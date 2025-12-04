<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;
use App\Models\TestMensuel;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NoteController extends Controller
{
    /**
     * Afficher la liste des classes pour sélection
     */
    public function index()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $user = auth()->user();
        
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        if ($user->isAdmin() || $user->role === 'personnel_admin') {
            // Admin et Personnel Admin voient toutes les classes de l'année active
            $classes = Classe::actif()
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                    }
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                    }
                }])
                ->get();
        } else if ($user->isTeacher()) {
            // Enseignant voit seulement ses classes de l'année active (via emplois du temps)
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
                })
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                    }
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                    }
                }])
                ->get();
        } else {
            $classes = collect();
        }
        
        return view('notes.index', compact('classes'));
    }

    /**
     * Afficher le formulaire de saisie des notes pour une classe
     */
    public function saisir($classeId)
    {
        $user = auth()->user();
        
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        $classe = Classe::findOrFail($classeId);
        
        // Récupérer les élèves triés par prénom puis nom
        $elevesQuery = \App\Models\Eleve::where('eleves.classe_id', $classe->id)
            ->where('eleves.actif', true);
            
        if ($anneeScolaireActive) {
            $elevesQuery->where('eleves.annee_scolaire_id', $anneeScolaireActive->id);
        }
        
        $eleves = $elevesQuery->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.prenom', 'asc')
            ->orderBy('utilisateurs.nom', 'asc')
            ->select('eleves.*')
            ->with('utilisateur')
            ->get();
        
        // Ajouter l'attribut nom_complet pour compatibilité
        $eleves->each(function($eleve) {
            $eleve->nom_complet = $eleve->utilisateur->prenom . ' ' . $eleve->utilisateur->nom;
        });
        
        // Assigner les élèves triés à la classe
        $classe->setRelation('eleves', $eleves);
        
        // Vérifier les permissions
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            
            // Vérifier que l'enseignant enseigne dans cette classe
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                abort(403, 'Vous n\'avez pas accès à cette classe.');
            }
            
            // Enseignant voit seulement ses matières assignées
            $matieres = $enseignant->matieres()->actif()->get();
                
            $enseignants = collect([$enseignant])->map(function($enseignant) {
                $enseignant->nom_complet = $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
                return $enseignant;
            });
        } elseif ($user->isAdmin() || $user->role === 'personnel_admin') {
            // Admin et Personnel Admin voient seulement les enseignants qui enseignent dans cette classe
            $enseignants = \App\Models\Enseignant::with(['utilisateur', 'emploisTemps.matiere'])
                ->whereHas('emploisTemps', function($query) use ($classe) {
                    $query->where('classe_id', $classe->id)
                          ->where('actif', true);
                })
                ->get()
                ->map(function($enseignant) use ($classe) {
                    $enseignant->nom_complet = $enseignant->utilisateur->prenom . ' ' . $enseignant->utilisateur->nom;
                    // Ajouter les matières enseignées par cet enseignant dans cette classe
                    $enseignant->matieres_classe = $enseignant->emploisTemps
                        ->where('classe_id', $classe->id)
                        ->where('actif', true)
                        ->pluck('matiere_id')
                        ->unique()
                        ->toArray();
                    return $enseignant;
                });
            
            // Récupérer toutes les matières actives pour le filtrage JavaScript
            $matieres = Matiere::actif()->get();
        } else {
            abort(403, 'Vous n\'avez pas accès à cette fonctionnalité.');
        }
        
        // Récupérer les notes existantes pour cette classe de l'année active
        $notesExistantes = Note::whereHas('eleve', function($query) use ($classeId, $anneeScolaireActive) {
            $query->where('classe_id', $classeId);
            if ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        })->with(['matiere', 'enseignant'])->get()->groupBy(['eleve_id', 'matiere_id']);

        return view('notes.saisir', compact('classe', 'matieres', 'enseignants', 'notesExistantes'));
    }

    /**
     * Afficher le formulaire de saisie des notes pour les enseignants (vue simplifiée)
     */
    public function teacherSaisir($classeId)
    {
        $user = auth()->user();
        
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        $classe = Classe::findOrFail($classeId);
        
        // Récupérer les élèves triés par prénom puis nom
        $elevesQuery = \App\Models\Eleve::where('eleves.classe_id', $classe->id)
            ->where('eleves.actif', true);
            
        if ($anneeScolaireActive) {
            $elevesQuery->where('eleves.annee_scolaire_id', $anneeScolaireActive->id);
        }
        
        $eleves = $elevesQuery->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.prenom', 'asc')
            ->orderBy('utilisateurs.nom', 'asc')
            ->select('eleves.*')
            ->with('utilisateur')
            ->get();
        
        // Assigner les élèves triés à la classe
        $classe->setRelation('eleves', $eleves);
        
        // Vérifier que l'enseignant enseigne dans cette classe
        $enseignant = $user->enseignant;
        $hasAccess = $classe->emploisTemps()
            ->where('enseignant_id', $enseignant->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à cette classe.');
        }
        
        // Enseignant voit seulement ses matières assignées
        $matieres = $enseignant->matieres()->actif()->get();
            
        $enseignants = collect([$enseignant])->map(function($enseignant) {
            $enseignant->nom_complet = $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
            return $enseignant;
        });
        
        return view('notes.teacher-saisir', compact('classe', 'matieres', 'enseignants'));
    }

    /**
     * Enregistrer les notes saisies
     */
    public function store(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'notes' => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.matiere_id' => 'required|exists:matieres,id',
            'notes.*.enseignant_id' => 'required|exists:enseignants,id',
            'notes.*.note_cours' => 'nullable|numeric|min:0|max:20',
            'notes.*.note_composition' => 'nullable|numeric|min:0|max:20',
            'notes.*.coefficient' => 'required|numeric|min:1|max:10',
            'notes.*.type_evaluation' => 'required|in:devoir,controle,examen,oral,tp',
            'notes.*.periode' => 'required|in:trimestre1,trimestre2',
        ]);

        $matieresAvecNotes = collect();
        
        DB::transaction(function() use ($request, &$matieresAvecNotes) {
            $classeId = $request->classe_id;
            
            foreach ($request->notes as $noteData) {
                // Vérifier qu'une matière et un coefficient sont sélectionnés
                if (!empty($noteData['matiere_id']) && !empty($noteData['coefficient'])) {
                    // Créer l'emploi du temps si nécessaire pour assurer que la matière apparaît dans le tableau complet
                    $emploiExistant = \App\Models\EmploiTemps::where('classe_id', $classeId)
                        ->where('matiere_id', $noteData['matiere_id'])
                        ->first();
                    
                    if (!$emploiExistant) {
                        \App\Models\EmploiTemps::create([
                            'classe_id' => $classeId,
                            'matiere_id' => $noteData['matiere_id'],
                            'enseignant_id' => $noteData['enseignant_id'],
                            'jour_semaine' => 'lundi',
                            'heure_debut' => '08:00:00',
                            'heure_fin' => '09:00:00',
                            'salle' => 'Salle 1',
                            'date_debut' => now()->startOfYear(),
                            'date_fin' => now()->endOfYear(),
                            'actif' => true
                        ]);
                    }
                    
                    // Déterminer les notes (par défaut 2/20 si aucune note saisie)
                    $noteCours = !empty($noteData['note_cours']) ? $noteData['note_cours'] : 2.0;
                    $noteComposition = !empty($noteData['note_composition']) ? $noteData['note_composition'] : 2.0;
                    
                    // Si aucune note n'est saisie, utiliser la note par défaut
                    if (empty($noteData['note_cours']) && empty($noteData['note_composition'])) {
                        $noteCours = 2.0;
                        $noteComposition = 2.0;
                    }
                    
                    // Calculer la note finale (moyenne pondérée)
                    $noteFinale = ($noteCours + $noteComposition) / 2;
                    
                    // Créer la note avec les nouveaux champs
                    Note::create([
                        'eleve_id' => $noteData['eleve_id'],
                        'matiere_id' => $noteData['matiere_id'],
                        'enseignant_id' => $noteData['enseignant_id'],
                        'note_cours' => $noteCours,
                        'note_composition' => $noteComposition,
                        'note_finale' => $noteFinale,
                        'note_sur' => $noteFinale,
                        'type_evaluation' => $noteData['type_evaluation'],
                        'titre' => $noteData['titre'] ?? null,
                        'commentaire' => $noteData['commentaire'] ?? null,
                        'date_evaluation' => $noteData['date_evaluation'] ?? now(),
                        'periode' => $noteData['periode'],
                        'coefficient' => $noteData['coefficient'],
                    ]);
                    
                    $matieresAvecNotes->push($noteData['matiere_id']);
                }
            }
        });

        $matieresUniques = $matieresAvecNotes->unique();
        
        if ($matieresUniques->count() > 0) {
            $message = 'Notes enregistrées avec succès';
            $nomsMatieres = $matieresUniques->map(function($matiereId) {
                return \App\Models\Matiere::find($matiereId)->nom ?? 'Matière inconnue';
            })->implode(', ');
            
            $message .= '. Les colonnes pour les matières suivantes ont été automatiquement créées dans le tableau complet : ' . $nomsMatieres;
            $message .= '. Note : Les élèves sans notes saisies ont reçu une note par défaut de 2/20.';
            
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Aucune note n\'a été enregistrée. Veuillez sélectionner au moins une matière pour au moins un élève.');
        }
    }

    /**
     * Afficher les notes d'un élève (vue interactive)
     */
    public function eleveNotes(Request $request, $eleveId)
    {
        $periode = $request->input('periode', 'trimestre1');
        $eleve = Eleve::with(['utilisateur', 'classe'])->findOrFail($eleveId);
        
        // Récupérer les notes de l'élève pour la période
        $notes = Note::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with(['matiere', 'enseignant'])
            ->get()
            ->groupBy('matiere_id');

        // Calculer les moyennes par matière
        $moyennesParMatiere = [];
        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            
            // Calculer la moyenne de la matière en utilisant les notes finales
            $sommeNotesFinales = 0;
            $nombreNotes = 0;
            
            foreach ($notesMatiere as $note) {
                $noteFinale = $note->calculerNoteFinale();
                if ($noteFinale !== null) {
                    $sommeNotesFinales += $noteFinale;
                    $nombreNotes++;
                }
            }
            
            $moyenneMatiere = $nombreNotes > 0 ? $sommeNotesFinales / $nombreNotes : 0;
            
            $moyennesParMatiere[$matiereId] = [
                'matiere' => $matiere,
                'notes' => $notesMatiere,
                'moyenne' => $moyenneMatiere,
                'coefficient' => $matiere->coefficient,
                'points' => $moyenneMatiere * $matiere->coefficient
            ];

            $totalPoints += $moyenneMatiere * $matiere->coefficient;
            $totalCoefficients += $matiere->coefficient;
        }

        // Moyenne générale
        $moyenneGenerale = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : 0;

        // Calculer l'appréciation générale
        $appreciationGenerale = $eleve->classe->getAppreciation($moyenneGenerale);

        // Calculer le rang dans la classe
        $rang = $this->calculerRang($eleve->classe_id, $periode, $moyenneGenerale);

        return view('notes.eleve', compact(
            'eleve', 
            'periode', 
            'moyennesParMatiere', 
            'moyenneGenerale', 
            'appreciationGenerale',
            'rang'
        ));
    }

    /**
     * Afficher le bulletin de notes d'un élève
     */
    public function bulletin(Request $request, $eleveId, $periode = 'trimestre1')
    {
        $periode = $request->input('periode', $periode);
        $eleve = Eleve::with(['utilisateur', 'classe'])->findOrFail($eleveId);
        
        // Récupérer les notes de l'élève pour la période
        $notes = Note::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with(['matiere', 'enseignant'])
            ->get()
            ->groupBy('matiere_id');

        // Calculer les moyennes par matière
        $moyennesParMatiere = [];
        $totalPoints = 0;
        $totalCoefficients = 0;

        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            
            // Calculer la moyenne de la matière en utilisant les notes finales
            $sommeNotesFinales = 0;
            $nombreNotes = 0;
            
            foreach ($notesMatiere as $note) {
                $noteFinale = $note->calculerNoteFinale();
                if ($noteFinale !== null) {
                    $sommeNotesFinales += $noteFinale;
                    $nombreNotes++;
                }
            }
            
            $moyenneMatiere = $nombreNotes > 0 ? $sommeNotesFinales / $nombreNotes : 0;
            
            $moyennesParMatiere[$matiereId] = [
                'matiere' => $matiere,
                'notes' => $notesMatiere,
                'moyenne' => $moyenneMatiere,
                'coefficient' => $matiere->coefficient,
                'points' => $moyenneMatiere * $matiere->coefficient
            ];

            $totalPoints += $moyenneMatiere * $matiere->coefficient;
            $totalCoefficients += $matiere->coefficient;
        }

        // Moyenne générale
        $moyenneGenerale = $totalCoefficients > 0 ? $totalPoints / $totalCoefficients : 0;

        // Calculer l'appréciation générale
        $appreciationGenerale = $eleve->classe->getAppreciation($moyenneGenerale);

        // Calculer le rang dans la classe
        $rang = $this->calculerRang($eleve->classe_id, $periode, $moyenneGenerale);

        return view('notes.bulletin', compact(
            'eleve', 
            'periode', 
            'moyennesParMatiere', 
            'moyenneGenerale', 
            'appreciationGenerale',
            'rang'
        ));
    }

    /**
     * Calculer le rang d'un élève dans sa classe
     */
    private function calculerRang($classeId, $periode, $moyenneEleve)
    {
        $eleves = Eleve::where('classe_id', $classeId)->get();
        $moyennes = [];

        foreach ($eleves as $eleve) {
            $moyenneGenerale = Note::calculerMoyenneGenerale($eleve->id, $periode);
            $moyennes[] = $moyenneGenerale;
        }

        // Trier les moyennes par ordre décroissant
        rsort($moyennes);
        
        // Trouver la position de la moyenne de l'élève
        $rang = array_search($moyenneEleve, $moyennes);
        return $rang !== false ? $rang + 1 : count($moyennes);
    }

    /**
     * Récupérer le coefficient d'une matière (API)
     */
    public function getCoefficientMatiere($matiereId)
    {
        $matiere = Matiere::findOrFail($matiereId);
        return response()->json(['coefficient' => $matiere->coefficient]);
    }

    /**
     * Afficher le formulaire de sélection de classe pour les bulletins
     */
    public function bulletins()
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Filtrer les classes pour ne montrer que celles qui ont des élèves de l'année scolaire active
        $classes = Classe::whereHas('eleves', function($query) use ($anneeScolaireActive) {
            $query->where('annee_scolaire_id', $anneeScolaireActive->id);
        })->with(['eleves' => function($query) use ($anneeScolaireActive) {
            $query->where('annee_scolaire_id', $anneeScolaireActive->id);
        }])->get();
        
        return view('notes.bulletins', compact('classes', 'anneeScolaireActive'));
    }

    /**
     * Générer les bulletins de notes pour une classe
     */
    public function genererBulletins(Request $request, $classeId)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $periode = $request->input('periode', 'trimestre1');
        
        // Filtrer les élèves par année scolaire active
        $classe = Classe::with(['eleves' => function($query) use ($anneeScolaireActive) {
            $query->where('annee_scolaire_id', $anneeScolaireActive->id);
        }, 'eleves.utilisateur', 'eleves.notes' => function($q) use ($periode) {
            $q->where('periode', $periode)->with('matiere');
        }])->findOrFail($classeId);
        
        // Filtrer les élèves pour ne garder que ceux de l'année scolaire active
        $elevesActifs = $classe->eleves->filter(function($eleve) use ($anneeScolaireActive) {
            return $eleve->annee_scolaire_id == $anneeScolaireActive->id;
        });
        
        // Logique de génération des bulletins
        $bulletins = [];
        foreach ($elevesActifs as $eleve) {
            $notesDetaillees = $this->getNotesDetailleesElevePeriode($eleve->id, $periode);
            $moyenneGenerale = $this->calculerMoyenneGeneralePeriode($eleve->id, $periode);
            $rang = $this->calculerRangEleve($eleve->id, $classeId, $anneeScolaireActive->id);
            
            // Générer un hash unique pour sécuriser le bulletin
            // Le hash est basé sur les données du bulletin + la clé secrète de l'application
            $dataToHash = $eleve->id . '|' . $classeId . '|' . $periode . '|' . $anneeScolaireActive->id . '|' . number_format($moyenneGenerale, 2);
            $verificationHash = hash_hmac('sha256', $dataToHash, config('app.key'));
            
            // Créer le token de vérification (format base64 encodé)
            $token = base64_encode(json_encode([
                'e' => $eleve->id,
                'c' => $classeId,
                'p' => $periode,
                'a' => $anneeScolaireActive->id,
                'h' => $verificationHash
            ]));
            
            // URL de vérification pour le QR code
            $verificationUrl = url('/notes/bulletin/verifier/' . $token);
            
            $bulletins[] = [
                'eleve' => $eleve,
                'notes' => $notesDetaillees,
                'moyenne_generale' => $moyenneGenerale,
                'rang' => $rang,
                'verification_token' => $token,
                'verification_url' => $verificationUrl
            ];
        }
        
        // Trier les bulletins par ordre de mérite (par rang, puis par moyenne générale)
        usort($bulletins, function($a, $b) {
            // D'abord par rang (1er avant 2ème)
            if ($a['rang'] != $b['rang']) {
                return $a['rang'] <=> $b['rang'];
            }
            // En cas d'égalité de rang, trier par moyenne générale décroissante
            return $b['moyenne_generale'] <=> $a['moyenne_generale'];
        });
        
        return view('notes.bulletins-classe', compact('classe', 'bulletins', 'periode', 'anneeScolaireActive'));
    }

    /**
     * Rapport global des notes
     */
    public function rapportGlobal()
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $classes = Classe::actif()
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                }, 'eleves.notes'])
                ->get();
        } else {
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
                })
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                }, 'eleves.notes'])
                ->get();
        }

        // Filtrer les notes par élèves de l'année scolaire active
        $elevesIds = \App\Models\Eleve::where('annee_scolaire_id', $anneeScolaireActive->id)->pluck('id');
        
        $statistiques = [
            'total_notes' => Note::whereIn('eleve_id', $elevesIds)->count(),
            'moyenne_generale' => Note::whereIn('eleve_id', $elevesIds)->whereNotNull('note_finale')->avg('note_finale'),
            'notes_ce_mois' => Note::whereIn('eleve_id', $elevesIds)->whereMonth('created_at', now()->month)->count(),
            'classes_actives' => $classes->count()
        ];

        return view('notes.rapport-global', compact('classes', 'statistiques', 'anneeScolaireActive'));
    }

    /**
     * Exporter les notes au format CSV
     */
    public function exporterNotes(Request $request)
    {
        $classeId = $request->get('classe_id');
        $periode = $request->get('periode');
        
        $query = Note::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur']);
        
        if ($classeId) {
            $query->whereHas('eleve', function($q) use ($classeId) {
                $q->where('classe_id', $classeId);
            });
        }
        
        if ($periode) {
            $query->where('periode', $periode);
        }
        
        $notes = $query->get();
        
        $filename = 'notes_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];
        
        $callback = function() use ($notes) {
            $file = fopen('php://output', 'w');
            
            // Ajouter le BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes CSV
            fputcsv($file, [
                'Élève',
                'Classe', 
                'Matière',
                'Note Finale',
                'Note Cours',
                'Note Composition',
                'Coefficient',
                'Type Évaluation',
                'Période',
                'Date Évaluation',
                'Enseignant',
                'Commentaire'
            ]);
            
            // Données
            foreach ($notes as $note) {
                fputcsv($file, [
                    $note->eleve->nom_complet ?? 'N/A',
                    $note->eleve->classe->nom ?? 'N/A',
                    $note->matiere->nom ?? 'N/A',
                    $note->note_finale ?? 'N/A',
                    $note->note_cours ?? 'N/A',
                    $note->note_composition ?? 'N/A',
                    $note->coefficient ?? 1,
                    $note->type_evaluation ?? 'N/A',
                    $note->periode ?? 'N/A',
                    $note->date_evaluation ? $note->date_evaluation->format('d/m/Y') : 'N/A',
                    $note->enseignant->nom_complet ?? 'N/A',
                    $note->commentaire ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Paramètres de notation
     */
    public function parametres()
    {
        $matieres = Matiere::actif()->get();
        $classes = Classe::actif()->get();
        $periodesScolaires = \App\Models\PeriodeScolaire::getPeriodesActives();
        
        return view('notes.parametres', compact('matieres', 'classes', 'periodesScolaires'));
    }

    /**
     * Récupérer les notes détaillées d'un élève par matière
     */
    private function getNotesDetailleesElevePeriode($eleveId, $periode)
    {
        $notes = Note::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');
            
        $notesParMatiere = [];
        
        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            
            // Calculer les moyennes des notes cours et composition
            $sommeNoteCours = 0;
            $sommeNoteComposition = 0;
            $nombreNotesCours = 0;
            $nombreNotesComposition = 0;
            
            foreach ($notesMatiere as $note) {
                if ($note->note_cours !== null) {
                    $sommeNoteCours += $note->note_cours;
                    $nombreNotesCours++;
                }
                if ($note->note_composition !== null) {
                    $sommeNoteComposition += $note->note_composition;
                    $nombreNotesComposition++;
                }
            }
            
            $moyenneNoteCours = $nombreNotesCours > 0 ? $sommeNoteCours / $nombreNotesCours : 0;
            $moyenneNoteComposition = $nombreNotesComposition > 0 ? $sommeNoteComposition / $nombreNotesComposition : 0;
            
            // Calculer la note finale selon la formule : (NOTE DE COURS + NOTES DE COMPO * 2) / 3
            $noteFinale = 0;
            if ($moyenneNoteCours > 0 && $moyenneNoteComposition > 0) {
                $noteFinale = ($moyenneNoteCours + ($moyenneNoteComposition * 2)) / 3;
            } elseif ($moyenneNoteCours > 0) {
                $noteFinale = $moyenneNoteCours;
            } elseif ($moyenneNoteComposition > 0) {
                $noteFinale = $moyenneNoteComposition;
            }
            
            // Calculer les points : Note finale * Coefficient
            $points = $noteFinale * $matiere->coefficient;
            
            $notesParMatiere[$matiere->nom] = [
                'matiere' => $matiere,
                'coefficient' => $matiere->coefficient,
                'note_cours' => round($moyenneNoteCours, 2),
                'note_composition' => round($moyenneNoteComposition, 2),
                'note_finale' => round($noteFinale, 2),
                'points' => round($points, 2)
            ];
        }
        
        return $notesParMatiere;
    }

    /**
     * Calculer la moyenne générale d'un élève
     */
    private function calculerMoyenneGeneralePeriode($eleveId, $periode)
    {
        $notes = Note::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');
            
        $totalPoints = 0;
        $totalCoefficients = 0;
        
        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            
            // Calculer la moyenne de la matière
            $sommeNotesFinales = 0;
            $nombreNotes = 0;
            
            foreach ($notesMatiere as $note) {
                $noteFinale = $note->calculerNoteFinale();
                if ($noteFinale !== null) {
                    $sommeNotesFinales += $noteFinale;
                    $nombreNotes++;
                }
            }
            
            if ($nombreNotes > 0) {
                $moyenne = $sommeNotesFinales / $nombreNotes;
                $totalPoints += $moyenne * $matiere->coefficient;
                $totalCoefficients += $matiere->coefficient;
            }
        }
        
        return $totalCoefficients > 0 ? round($totalPoints / $totalCoefficients, 2) : 0;
    }

    /**
     * Calculer les moyennes d'un élève
     */
    private function calculerMoyennesEleve($eleveId)
    {
        $notes = Note::where('eleve_id', $eleveId)
            ->with('matiere')
            ->get()
            ->groupBy('matiere_id');
            
        $moyennes = [];
        $totalPoints = 0;
        $totalCoefficients = 0;
        
        foreach ($notes as $matiereId => $notesMatiere) {
            $matiere = $notesMatiere->first()->matiere;
            
            // Calculer la moyenne de la matière en utilisant les notes finales
            $sommeNotesFinales = 0;
            $nombreNotes = 0;
            
            foreach ($notesMatiere as $note) {
                $noteFinale = $note->calculerNoteFinale();
                if ($noteFinale !== null) {
                    $sommeNotesFinales += $noteFinale;
                    $nombreNotes++;
                }
            }
            
            if ($nombreNotes > 0) {
                $moyenne = $sommeNotesFinales / $nombreNotes;
                $moyennes[$matiere->nom] = [
                    'moyenne' => round($moyenne, 2),
                    'coefficient' => $matiere->coefficient
                ];
                
                $totalPoints += $moyenne * $matiere->coefficient;
                $totalCoefficients += $matiere->coefficient;
            }
        }
        
        $moyennes['generale'] = $totalCoefficients > 0 ? 
            round($totalPoints / $totalCoefficients, 2) : 0;
            
        return $moyennes;
    }

    /**
     * Calculer le rang d'un élève dans sa classe
     */
    private function calculerRangEleve($eleveId, $classeId, $anneeScolaireId = null)
    {
        $query = Eleve::where('classe_id', $classeId);
        
        // Filtrer par année scolaire si fournie
        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }
        
        $eleves = $query->get();
        $moyennes = [];
        
        foreach ($eleves as $eleve) {
            $moyennesEleve = $this->calculerMoyennesEleve($eleve->id);
            $moyennes[$eleve->id] = $moyennesEleve['generale'] ?? 0;
        }
        
        arsort($moyennes);
        $rang = 1;
        
        foreach ($moyennes as $id => $moyenne) {
            if ($id == $eleveId) {
                return $rang;
            }
            $rang++;
        }
        
        return $rang;
    }



    /**
     * Afficher les statistiques des notes par classe
     */
    public function statistiques()
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Filtrer les classes pour ne montrer que celles qui ont des élèves de l'année scolaire active
        $classes = Classe::whereHas('eleves', function($query) use ($anneeScolaireActive) {
            $query->where('annee_scolaire_id', $anneeScolaireActive->id);
        })->with(['eleves' => function($query) use ($anneeScolaireActive) {
            $query->where('annee_scolaire_id', $anneeScolaireActive->id);
        }])->get();
        
        return view('notes.statistiques', compact('classes', 'anneeScolaireActive'));
    }

    public function statistiquesClasse(Request $request, $classeId)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $classe = Classe::findOrFail($classeId);
        $periode = $request->input('periode', 'trimestre1');
        
        // Filtrer les élèves par année scolaire active
        $eleves = Eleve::where('classe_id', $classeId)
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->with('utilisateur')
            ->get();
        
        $statistiques = [];

        foreach ($eleves as $eleve) {
            $moyenneGenerale = Note::calculerMoyenneGenerale($eleve->id, $periode);
            
            $statistiques[] = [
                'eleve' => $eleve,
                'moyenne' => $moyenneGenerale
            ];
        }

        // Trier par moyenne décroissante pour le classement
        usort($statistiques, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });

        // Ajouter le rang
        foreach ($statistiques as $index => &$stat) {
            $stat['rang'] = $index + 1;
        }

        // Convertir en collection pour utiliser les méthodes Laravel
        $statistiques = collect($statistiques);

        return view('notes.statistiques-classe', compact('classe', 'periode', 'statistiques', 'anneeScolaireActive'));
    }

    /**
     * Afficher les statistiques des notes par classe (version imprimable)
     */
    public function statistiquesClasseImprimable($classeId, $periode = 'trimestre1')
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $classe = Classe::findOrFail($classeId);
        
        // Filtrer les élèves par année scolaire active
        $eleves = Eleve::where('classe_id', $classeId)
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->with('utilisateur')
            ->get();
        
        $statistiques = [];

        foreach ($eleves as $eleve) {
            $moyenneGenerale = Note::calculerMoyenneGenerale($eleve->id, $periode);
            
            $statistiques[] = [
                'eleve' => $eleve,
                'moyenne' => $moyenneGenerale
            ];
        }

        // Trier par moyenne décroissante pour le classement
        usort($statistiques, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });

        // Ajouter le rang
        foreach ($statistiques as $index => &$stat) {
            $stat['rang'] = $index + 1;
        }

        // Convertir en collection pour utiliser les méthodes Laravel
        $statistiques = collect($statistiques);

        return view('notes.statistiques-classe-imprimable', compact('classe', 'periode', 'statistiques', 'anneeScolaireActive'));
    }

    /**
     * Afficher le formulaire d'édition d'une note
     */
    public function edit(Note $note)
    {
        $note->load(['eleve', 'matiere', 'enseignant']);
        
        return view('notes.edit', compact('note'));
    }

    /**
     * Mettre à jour une note
     */
    public function update(Request $request, Note $note)
    {
        $request->validate([
            'note_cours' => 'nullable|numeric|min:0|max:20',
            'note_composition' => 'nullable|numeric|min:0|max:20',
            'coefficient' => 'required|numeric|min:0.1|max:10',
            'type_evaluation' => 'required|string|in:devoir,controle,examen,oral,tp',
            'titre' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:1000',
            'date_evaluation' => 'required|date',
            'periode' => 'required|string|in:trimestre1,trimestre2',
        ]);

        // Déterminer les notes (par défaut 2/20 si aucune note saisie)
        $noteCours = !empty($request->note_cours) ? $request->note_cours : 2.0;
        $noteComposition = !empty($request->note_composition) ? $request->note_composition : 2.0;
        
        // Si aucune note n'est saisie, utiliser la note par défaut
        if (empty($request->note_cours) && empty($request->note_composition)) {
            $noteCours = 2.0;
            $noteComposition = 2.0;
        }

        $note->update([
            'note_cours' => $noteCours,
            'note_composition' => $noteComposition,
            'coefficient' => $request->coefficient,
            'type_evaluation' => $request->type_evaluation,
            'titre' => $request->titre,
            'commentaire' => $request->commentaire,
            'date_evaluation' => $request->date_evaluation,
            'periode' => $request->periode,
        ]);

        return redirect()->route('notes.eleve', $note->eleve_id)
            ->with('success', 'Note mise à jour avec succès.');
    }

    /**
     * Supprimer une note
     */
    public function destroy(Note $note)
    {
        $eleveId = $note->eleve_id;
        $note->delete();

        return redirect()->route('notes.eleve', $eleveId)
            ->with('success', 'Note supprimée avec succès.');
    }

    /**
     * Mettre à jour une période scolaire
     */
    public function updatePeriodeScolaire(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'date_conseil' => 'required|date|after:date_fin',
            'couleur' => 'required|string|in:primary,success,warning,danger,info,secondary',
            'actif' => 'boolean',
            'ordre' => 'required|integer|min:1|max:10',
        ]);

        $periode = \App\Models\PeriodeScolaire::findOrFail($id);
        $periode->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Période scolaire mise à jour avec succès'
        ]);
    }

    /**
     * Créer une nouvelle période scolaire
     */
    public function createPeriodeScolaire(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'date_conseil' => 'required|date|after:date_fin',
            'couleur' => 'required|string|in:primary,success,warning,danger,info,secondary',
            'actif' => 'boolean',
            'ordre' => 'required|integer|min:1|max:10',
        ]);

        $periode = \App\Models\PeriodeScolaire::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Période scolaire créée avec succès',
            'periode' => $periode
        ]);
    }


    /**
     * Supprimer une période scolaire
     */
    public function deletePeriodeScolaire($id)
    {
        $periode = \App\Models\PeriodeScolaire::findOrFail($id);
        $periode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Période scolaire supprimée avec succès'
        ]);
    }

    /**
     * Afficher la page d'accueil des tests mensuels
     */
    public function mensuelIndex()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $user = auth()->user();
        
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }
        
        if ($user->isAdmin() || $user->role === 'personnel_admin') {
            // Admin et Personnel Admin voient toutes les classes avec élèves de l'année active
            $classes = Classe::actif()
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id)
                          ->where('actif', true);
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id)
                          ->where('actif', true)
                          ->with('utilisateur')
                          ->orderBy('id', 'asc');
                }])
                ->orderBy('id', 'asc')
                ->get();
        } else if ($user->isTeacher()) {
            // Enseignant voit seulement ses classes avec élèves de l'année active
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
                })
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id)
                          ->where('actif', true);
                })
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id)
                          ->where('actif', true)
                          ->with('utilisateur')
                          ->orderBy('id', 'asc');
                }])
                ->orderBy('id', 'asc')
                ->get();
        } else {
            $classes = collect();
        }

        // Mettre à jour l'effectif actuel de chaque classe
        foreach ($classes as $classe) {
            $classe->updateEffectifActuel();
        }

        // Vider le cache pour s'assurer d'avoir les données les plus récentes
        \Cache::forget('classes_with_students_' . $anneeScolaireActive->id);

        return view('notes.mensuel.index', compact('classes', 'anneeScolaireActive'));
    }

    /**
     * Afficher les tests mensuels d'une classe
     */
    public function mensuelClasse(Request $request, Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Vérifier l'accès à la classe pour les enseignants
        $user = auth()->user();
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }
        }

        $mois = $request->get('mois', date('n'));
        $annee = $request->get('annee', date('Y'));

        // Vider le cache pour s'assurer que les données sont à jour
        \Cache::forget('tests_mensuels_' . $classe->id . '_' . $mois . '_' . $annee);

        // Récupérer les tests mensuels de la classe pour le mois/année sélectionnés
        $tests = TestMensuel::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur'])
            ->parClasse($classe->id)
            ->parPeriode($mois, $annee)
            ->orderBy('eleve_id')
            ->orderBy('matiere_id')
            ->get();

        // Récupérer les élèves de la classe pour l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $eleves = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->where('actif', true)
            ->with('utilisateur')
            ->orderBy('id', 'asc')
            ->get();
            
        // Mettre à jour l'effectif actuel de la classe
        $classe->updateEffectifActuel();

        // Récupérer les matières de la classe
        $matieres = $classe->matieres()->get();

        // Créer un tableau des mois
        $moisListe = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return view('notes.mensuel.classe', compact('classe', 'tests', 'eleves', 'matieres', 'mois', 'annee', 'moisListe'));
    }

    /**
     * Afficher le formulaire de saisie des tests mensuels
     */
    public function mensuelSaisir(Request $request, Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Vérifier l'accès à la classe pour les enseignants
        $user = auth()->user();
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }
        }

        $mois = $request->get('mois', date('n'));
        $annee = $request->get('annee', date('Y'));

        // Récupérer les élèves de la classe pour l'année scolaire active, triés par prénom
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $elevesQuery = \App\Models\Eleve::where('eleves.classe_id', $classe->id)
            ->where('eleves.annee_scolaire_id', $anneeScolaireActive->id)
            ->where('eleves.actif', true);
        
        $eleves = $elevesQuery->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.prenom', 'asc')
            ->orderBy('utilisateurs.nom', 'asc')
            ->select('eleves.*')
            ->with('utilisateur')
            ->get();
        
        // Ajouter les attributs matricule, nom, prenom pour compatibilité avec la vue
        $eleves->each(function($eleve) {
            $eleve->matricule = $eleve->numero_etudiant;
            $eleve->nom = $eleve->utilisateur->nom;
            $eleve->prenom = $eleve->utilisateur->prenom;
        });
            
        // Mettre à jour l'effectif actuel de la classe
        $classe->updateEffectifActuel();

        // Récupérer les enseignants selon le rôle de l'utilisateur
        if ($user->isTeacher()) {
            // Enseignant voit seulement lui-même
            $enseignants = collect([$user->enseignant])->filter();
        } else {
            // Admin et Personnel Admin voient tous les enseignants de la classe
            $enseignants = \App\Models\Enseignant::with(['utilisateur', 'emploisTemps.matiere'])
                ->whereHas('emploisTemps', function($query) use ($classe) {
                    $query->where('classe_id', $classe->id)
                          ->where('actif', true);
                })
                ->get();
        }

        // Récupérer toutes les matières actives (pas seulement celles de la classe)
        $matieres = \App\Models\Matiere::where('actif', true)->orderBy('nom')->get();

        // Récupérer les tests existants pour éviter les doublons
        $testsExistants = TestMensuel::parClasse($classe->id)
            ->parPeriode($mois, $annee)
            ->get()
            ->keyBy(function($test) {
                return $test->eleve_id . '_' . $test->matiere_id;
            });

        // Créer un tableau des mois
        $moisListe = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return view('notes.mensuel.saisir', compact('classe', 'eleves', 'matieres', 'enseignants', 'mois', 'annee', 'moisListe', 'testsExistants'));
    }

    /**
     * Enregistrer les tests mensuels
     */
    public function mensuelStore(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'mois' => 'required|integer|between:1,12',
            'annee' => 'required|integer|min:2020|max:2030',
            'enseignant_id' => 'required|exists:enseignants,id',
            'matiere_id' => 'required|exists:matieres,id',
            'notes' => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.note' => 'nullable|numeric|min:0|max:20',
            'notes.*.coefficient' => 'required|integer|min:1|max:10'
        ]);

        $classeId = $request->classe_id;
        $enseignantId = $request->enseignant_id;
        $matiereId = $request->matiere_id;
        $mois = $request->mois;
        $annee = $request->annee;
        $createdBy = auth()->id();

        // Récupérer tous les élèves de la classe pour l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $tousLesEleves = \App\Models\Eleve::where('eleves.classe_id', $classeId)
            ->where('eleves.annee_scolaire_id', $anneeScolaireActive->id)
            ->where('eleves.actif', true)
            ->pluck('id')
            ->toArray();

        // Créer un tableau des notes saisies indexé par eleve_id
        $notesSaisies = [];
        foreach ($request->notes as $noteData) {
            if (!empty($noteData['note']) || $noteData['note'] === '0' || $noteData['note'] === 0) {
                $notesSaisies[$noteData['eleve_id']] = [
                    'note' => floatval($noteData['note']),
                    'coefficient' => intval($noteData['coefficient'])
                ];
            }
        }

        DB::beginTransaction();
        try {
            // Traiter tous les élèves de la classe
            foreach ($tousLesEleves as $eleveId) {
                // Vérifier si un test existe déjà
                $testExistant = TestMensuel::where('eleve_id', $eleveId)
                    ->where('matiere_id', $matiereId)
                    ->where('mois', $mois)
                    ->where('annee', $annee)
                    ->first();

                // Déterminer la note et le coefficient
                if (isset($notesSaisies[$eleveId])) {
                    // L'élève a une note saisie
                    $note = $notesSaisies[$eleveId]['note'];
                    $coefficient = $notesSaisies[$eleveId]['coefficient'];
                } else {
                    // L'élève n'a pas de note, attribuer 0.00
                    $note = 0.00;
                    // Utiliser le coefficient par défaut (1) ou celui de la matière
                    $matiere = \App\Models\Matiere::find($matiereId);
                    $coefficient = $matiere ? $matiere->coefficient : 1;
                }

                if ($testExistant) {
                    // Mettre à jour le test existant
                    $testExistant->update([
                        'enseignant_id' => $enseignantId,
                        'note' => $note,
                        'coefficient' => $coefficient
                    ]);
                } else {
                    // Créer un nouveau test
                    TestMensuel::create([
                        'eleve_id' => $eleveId,
                        'classe_id' => $classeId,
                        'matiere_id' => $matiereId,
                        'enseignant_id' => $enseignantId,
                        'mois' => $mois,
                        'annee' => $annee,
                        'note' => $note,
                        'coefficient' => $coefficient,
                        'created_by' => $createdBy
                    ]);
                }
            }

            DB::commit();

            // Redirection différente selon le rôle
            if (auth()->user()->isTeacher()) {
                return redirect()->route('teacher.classes')
                    ->with('success', 'Tests mensuels enregistrés avec succès');
            } else {
                return redirect()->route('notes.mensuel.classe', $classeId)
                    ->with('success', 'Tests mensuels enregistrés avec succès')
                    ->with('mois', $mois)
                    ->with('annee', $annee);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement des tests mensuels: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les résultats des tests mensuels
     */
    public function mensuelResultats(Request $request, Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Vérifier l'accès à la classe pour les enseignants
        $user = auth()->user();
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }
        }

        $mois = $request->get('mois', date('n'));
        $annee = $request->get('annee', date('Y'));

        // Vider le cache pour s'assurer que les données sont à jour
        \Cache::forget('tests_mensuels_' . $classe->id . '_' . $mois . '_' . $annee);

        // Récupérer les tests mensuels de la classe
        $tests = TestMensuel::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur'])
            ->parClasse($classe->id)
            ->parPeriode($mois, $annee)
            ->get();

        // Récupérer les élèves de la classe pour l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $eleves = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->with('utilisateur')
            ->get();
            
        // Mettre à jour l'effectif actuel de la classe
        $classe->updateEffectifActuel();

        // Calculer les moyennes et rangs - inclure tous les élèves même sans note
        $resultats = [];
        foreach ($eleves as $eleve) {
            $moyenne = TestMensuel::calculerMoyenneMensuelle($eleve->id, $mois, $annee);
            // Si l'élève n'a pas de note, lui attribuer 0.00
            if ($moyenne === null) {
                $moyenne = 0.00;
            }
            $resultats[] = [
                'eleve' => $eleve,
                'moyenne' => $moyenne
            ];
        }

        // Trier par moyenne décroissante et calculer les rangs
        usort($resultats, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });

        $rang = 1;
        foreach ($resultats as &$resultat) {
            $resultat['rang'] = $rang++;
        }

        // Créer un tableau des mois
        $moisListe = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return view('notes.mensuel.resultats', compact('classe', 'tests', 'resultats', 'mois', 'annee', 'moisListe'));
    }

    /**
     * Afficher la version imprimable des résultats
     */
    public function mensuelResultatsImprimer(Request $request, Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Vérifier l'accès à la classe pour les enseignants
        $user = auth()->user();
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }
        }

        $mois = $request->get('mois', date('n'));
        $annee = $request->get('annee', date('Y'));

        // Vider le cache pour s'assurer que les données sont à jour
        \Cache::forget('tests_mensuels_' . $classe->id . '_' . $mois . '_' . $annee);

        // Récupérer les tests mensuels de la classe
        $tests = TestMensuel::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur'])
            ->parClasse($classe->id)
            ->parPeriode($mois, $annee)
            ->get();

        // Récupérer les élèves de la classe pour l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $eleves = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->with('utilisateur')
            ->get();
            
        // Mettre à jour l'effectif actuel de la classe
        $classe->updateEffectifActuel();

        // Calculer les moyennes et rangs - inclure tous les élèves même sans note
        $resultats = [];
        foreach ($eleves as $eleve) {
            $moyenne = TestMensuel::calculerMoyenneMensuelle($eleve->id, $mois, $annee);
            // Si l'élève n'a pas de note, lui attribuer 0.00
            if ($moyenne === null) {
                $moyenne = 0.00;
            }
            $resultats[] = [
                'eleve' => $eleve,
                'moyenne' => $moyenne
            ];
        }

        // Trier par moyenne décroissante et calculer les rangs
        usort($resultats, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });

        $rang = 1;
        foreach ($resultats as &$resultat) {
            $resultat['rang'] = $rang++;
        }

        // Créer un tableau des mois
        $moisListe = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return view('notes.mensuel.resultats-imprimer', compact('classe', 'tests', 'resultats', 'mois', 'annee', 'moisListe'));
    }

    /**
     * Afficher le formulaire de modification des tests mensuels
     */
    public function mensuelModifier(Request $request, Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Vérifier l'accès à la classe pour les enseignants
        $user = auth()->user();
        if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $hasAccess = $classe->emploisTemps()
                ->where('enseignant_id', $enseignant->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }
        }

        $mois = $request->get('mois', date('n'));
        $annee = $request->get('annee', date('Y'));

        // Vider le cache pour s'assurer que les données sont à jour
        \Cache::forget('tests_mensuels_' . $classe->id . '_' . $mois . '_' . $annee);

        // Récupérer les tests mensuels de la classe pour le mois/année sélectionnés
        $tests = TestMensuel::with(['eleve.utilisateur', 'matiere', 'enseignant.utilisateur'])
            ->parClasse($classe->id)
            ->parPeriode($mois, $annee)
            ->orderBy('eleve_id')
            ->orderBy('matiere_id')
            ->get();

        // Créer un tableau des mois
        $moisListe = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return view('notes.mensuel.modifier', compact('classe', 'tests', 'mois', 'annee', 'moisListe'));
    }

    /**
     * Mettre à jour un test mensuel
     */
    public function mensuelUpdate(Request $request, TestMensuel $test)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $request->validate([
            'note' => 'required|numeric|min:0|max:20',
            'coefficient' => 'required|integer|min:1|max:10'
        ]);

        $test->update([
            'note' => $request->note,
            'coefficient' => $request->coefficient
        ]);

        // Rafraîchir le modèle pour s'assurer que les données sont à jour
        $test->refresh();

        return redirect()->route('notes.mensuel.modifier', [
            'classe' => $test->classe_id,
            'mois' => $test->mois,
            'annee' => $test->annee
        ])
            ->with('success', 'Note modifiée avec succès');
    }

    /**
     * Supprimer un test mensuel
     */
    public function mensuelDestroy(TestMensuel $test)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $classeId = $test->classe_id;
        $mois = $test->mois;
        $annee = $test->annee;

        $test->delete();

        return redirect()->route('notes.mensuel.modifier', [
            'classe' => $classeId,
            'mois' => $mois,
            'annee' => $annee
        ])
            ->with('success', 'Note supprimée avec succès');
    }

    /**
     * Afficher le formulaire de sélection pour la fiche de notes
     */
    public function ficheNotesSelection()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('notes.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        $user = auth()->user();
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }
        
        // Récupérer les classes selon le rôle
        if ($user->isAdmin() || $user->role === 'personnel_admin') {
            $classes = Classe::actif()
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                })
                ->get();
            $enseignants = Enseignant::with('utilisateur')->actif()->get();
        } else if ($user->isTeacher()) {
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
                })
                ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                })
                ->get();
            $enseignants = collect([$enseignant]);
        } else {
            $classes = collect();
            $enseignants = collect();
        }
        
        $matieres = Matiere::actif()->orderBy('nom')->get();
        
        return view('notes.fiche-selection', compact('classes', 'enseignants', 'matieres', 'anneeScolaireActive'));
    }

    /**
     * Générer la fiche de notes pour l'enseignant (format A4 paysage)
     */
    public function ficheNotes(Request $request, $classeId, $enseignantId, $matiereId)
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Récupérer les mois sélectionnés
        $moisSelectionnes = $request->input('mois', []);
        
        if (empty($moisSelectionnes)) {
            return redirect()->back()->with('error', 'Veuillez sélectionner au moins un mois.');
        }
        
        // Convertir les mois en entiers et les trier
        $moisSelectionnes = array_map('intval', $moisSelectionnes);
        sort($moisSelectionnes);
        
        // Récupérer la classe
        $classe = Classe::findOrFail($classeId);
        
        // Récupérer l'enseignant
        $enseignant = Enseignant::with('utilisateur')->findOrFail($enseignantId);
        
        // Récupérer la matière
        $matiere = Matiere::findOrFail($matiereId);
        
        // Récupérer les élèves de la classe pour l'année scolaire active, triés par prénom puis nom
        $eleves = Eleve::where('eleves.classe_id', $classeId)
            ->where('eleves.annee_scolaire_id', $anneeScolaireActive->id)
            ->where('eleves.actif', true)
            ->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->select('eleves.*')
            ->orderBy('utilisateurs.prenom', 'asc')
            ->orderBy('utilisateurs.nom', 'asc')
            ->with('utilisateur')
            ->get();
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::principal();
        
        // Noms des mois en français
        $nomsMois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        // Préparer les données des élèves avec leurs notes
        $elevesAvecNotes = [];
        $annee = $anneeScolaireActive->date_debut->year;
        
        foreach ($eleves as $eleve) {
            // Récupérer les notes de l'élève pour cette matière
            $notes = Note::where('eleve_id', $eleve->id)
                ->where('matiere_id', $matiereId)
                ->where('enseignant_id', $enseignantId)
                ->get();
            
            // Calculer les moyennes des notes cours et composition
            $sommeNoteCours = 0;
            $sommeNoteComposition = 0;
            $nombreNotesCours = 0;
            $nombreNotesComposition = 0;
            
            foreach ($notes as $note) {
                if ($note->note_cours !== null) {
                    $sommeNoteCours += $note->note_cours;
                    $nombreNotesCours++;
                }
                if ($note->note_composition !== null) {
                    $sommeNoteComposition += $note->note_composition;
                    $nombreNotesComposition++;
                }
            }
            
            $moyenneCours = $nombreNotesCours > 0 ? round($sommeNoteCours / $nombreNotesCours, 2) : null;
            $moyenneComposition = $nombreNotesComposition > 0 ? round($sommeNoteComposition / $nombreNotesComposition, 2) : null;
            
            // Récupérer les tests mensuels pour les mois sélectionnés
            $notesMensuelles = [];
            foreach ($moisSelectionnes as $mois) {
                $testMensuel = TestMensuel::where('eleve_id', $eleve->id)
                    ->where('matiere_id', $matiereId)
                    ->where('enseignant_id', $enseignantId)
                    ->where('mois', $mois)
                    ->where('annee', $annee)
                    ->first();
                
                $notesMensuelles[$mois] = $testMensuel ? $testMensuel->note : null;
            }
            
            $elevesAvecNotes[] = [
                'eleve' => $eleve,
                'notes_mensuelles' => $notesMensuelles,
                'moyenne_cours' => $moyenneCours,
                'moyenne_composition' => $moyenneComposition
            ];
        }
        
        // Diviser les élèves en groupes de 30 pour la pagination
        $elevesParPage = collect($elevesAvecNotes)->chunk(30);
        
        return view('notes.fiche-notes', compact(
            'classe',
            'enseignant',
            'matiere',
            'elevesAvecNotes',
            'elevesParPage',
            'anneeScolaireActive',
            'etablissement',
            'moisSelectionnes',
            'nomsMois'
        ));
    }

    /**
     * Vérifier l'authenticité d'un bulletin via le token QR code
     */
    public function verifierBulletin(Request $request, $token)
    {
        try {
            // Décoder le token
            $decoded = json_decode(base64_decode($token), true);
            
            if (!$decoded || !isset($decoded['e']) || !isset($decoded['c']) || !isset($decoded['p']) || !isset($decoded['a']) || !isset($decoded['h'])) {
                return view('notes.bulletin-verify', [
                    'valid' => false,
                    'message' => 'Token invalide'
                ]);
            }
            
            // Récupérer les données
            $eleveId = $decoded['e'];
            $classeId = $decoded['c'];
            $periode = $decoded['p'];
            $anneeScolaireId = $decoded['a'];
            $hash = $decoded['h'];
            
            // Récupérer l'élève
            $eleve = Eleve::with(['utilisateur', 'classe'])->find($eleveId);
            if (!$eleve) {
                return view('notes.bulletin-verify', [
                    'valid' => false,
                    'message' => 'Élève non trouvé'
                ]);
            }
            
            // Calculer la moyenne générale
            $moyenneGenerale = $this->calculerMoyenneGeneralePeriode($eleveId, $periode);
            
            // Vérifier le hash
            $dataToHash = $eleveId . '|' . $classeId . '|' . $periode . '|' . $anneeScolaireId . '|' . number_format($moyenneGenerale, 2);
            $expectedHash = hash_hmac('sha256', $dataToHash, config('app.key'));
            
            if ($hash !== $expectedHash) {
                return view('notes.bulletin-verify', [
                    'valid' => false,
                    'message' => 'Le bulletin a été modifié ou n\'est pas authentique',
                    'eleve' => $eleve,
                    'periode' => $periode
                ]);
            }
            
            // Récupérer l'année scolaire
            $anneeScolaire = \App\Models\AnneeScolaire::find($anneeScolaireId);
            
            // Récupérer les notes détaillées
            $notesDetaillees = $this->getNotesDetailleesElevePeriode($eleveId, $periode);
            
            return view('notes.bulletin-verify', [
                'valid' => true,
                'message' => 'Ce bulletin est authentique et vérifié',
                'eleve' => $eleve,
                'classe' => $eleve->classe,
                'periode' => $periode,
                'anneeScolaire' => $anneeScolaire,
                'moyenneGenerale' => $moyenneGenerale,
                'notes' => $notesDetaillees
            ]);
            
        } catch (\Exception $e) {
            return view('notes.bulletin-verify', [
                'valid' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ]);
        }
    }
}
