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
        
        $classe = Classe::with(['eleves' => function($query) use ($anneeScolaireActive) {
            if ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        }, 'eleves.utilisateur'])->findOrFail($classeId);
        
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
            // Admin et Personnel Admin voient toutes les matières et enseignants
            $matieres = Matiere::actif()->get();
            $enseignants = Enseignant::with('utilisateur')->get()->map(function($enseignant) {
                $enseignant->nom_complet = $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
                return $enseignant;
            });
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
        
        $classe = Classe::with(['eleves' => function($query) use ($anneeScolaireActive) {
            if ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        }, 'eleves.utilisateur'])->findOrFail($classeId);
        
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
     * Afficher les notes d'un élève (alias pour bulletin)
     */
    public function eleveNotes($eleveId, $periode = 'trimestre1')
    {
        return $this->bulletin($eleveId, $periode);
    }

    /**
     * Afficher le bulletin de notes d'un élève
     */
    public function bulletin($eleveId, $periode = 'trimestre1')
    {
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

        // Calculer le rang dans la classe
        $rang = $this->calculerRang($eleve->classe_id, $periode, $moyenneGenerale);

        return view('notes.bulletin', compact(
            'eleve', 
            'periode', 
            'moyennesParMatiere', 
            'moyenneGenerale', 
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
        $classes = Classe::with('eleves')->get();
        return view('notes.bulletins', compact('classes'));
    }

    /**
     * Générer les bulletins de notes pour une classe
     */
    public function genererBulletins(Request $request, $classeId)
    {
        $periode = $request->input('periode', 'trimestre1');
        $classe = Classe::with(['eleves.utilisateur', 'eleves.notes' => function($q) use ($periode) {
            $q->where('periode', $periode)->with('matiere');
        }])->findOrFail($classeId);
        
        // Logique de génération des bulletins
        $bulletins = [];
        foreach ($classe->eleves as $eleve) {
            $notesDetaillees = $this->getNotesDetailleesElevePeriode($eleve->id, $periode);
            $moyenneGenerale = $this->calculerMoyenneGeneralePeriode($eleve->id, $periode);
            $bulletins[] = [
                'eleve' => $eleve,
                'notes' => $notesDetaillees,
                'moyenne_generale' => $moyenneGenerale,
                'rang' => $this->calculerRangEleve($eleve->id, $classeId)
            ];
        }
        
        return view('notes.bulletins-classe', compact('classe', 'bulletins', 'periode'));
    }

    /**
     * Rapport global des notes
     */
    public function rapportGlobal()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $classes = Classe::actif()->with(['eleves.notes'])->get();
        } else {
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
                })
                ->with(['eleves.notes'])
                ->get();
        }

        $statistiques = [
            'total_notes' => Note::count(),
            'moyenne_generale' => Note::whereNotNull('note_finale')->avg('note_finale'),
            'notes_ce_mois' => Note::whereMonth('created_at', now()->month)->count(),
            'classes_actives' => $classes->count()
        ];

        return view('notes.rapport-global', compact('classes', 'statistiques'));
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
            
            // Calculer la note finale (moyenne des deux si les deux existent, sinon prendre celle qui existe)
            $noteFinale = 0;
            if ($moyenneNoteCours > 0 && $moyenneNoteComposition > 0) {
                $noteFinale = ($moyenneNoteCours + $moyenneNoteComposition) / 2;
            } elseif ($moyenneNoteCours > 0) {
                $noteFinale = $moyenneNoteCours;
            } elseif ($moyenneNoteComposition > 0) {
                $noteFinale = $moyenneNoteComposition;
            }
            
            $notesParMatiere[$matiere->nom] = [
                'matiere' => $matiere,
                'coefficient' => $matiere->coefficient,
                'note_cours' => round($moyenneNoteCours, 2),
                'note_composition' => round($moyenneNoteComposition, 2),
                'note_finale' => round($noteFinale, 2),
                'points' => round($noteFinale * $matiere->coefficient, 2)
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
    private function calculerRangEleve($eleveId, $classeId)
    {
        $eleves = Eleve::where('classe_id', $classeId)->get();
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
        $classes = Classe::with('eleves')->get();
        return view('notes.statistiques', compact('classes'));
    }

    public function statistiquesClasse(Request $request, $classeId)
    {
        $classe = Classe::findOrFail($classeId);
        $periode = $request->input('periode', 'trimestre1');
        
        // Récupérer toutes les moyennes des élèves de la classe
        $eleves = Eleve::where('classe_id', $classeId)->with('utilisateur')->get();
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

        return view('notes.statistiques-classe', compact('classe', 'periode', 'statistiques'));
    }

    /**
     * Afficher les statistiques des notes par classe (version imprimable)
     */
    public function statistiquesClasseImprimable($classeId, $periode = 'trimestre1')
    {
        $classe = Classe::findOrFail($classeId);
        
        // Récupérer toutes les moyennes des élèves de la classe
        $eleves = Eleve::where('classe_id', $classeId)->with('utilisateur')->get();
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

        return view('notes.statistiques-classe-imprimable', compact('classe', 'periode', 'statistiques'));
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
        
        if ($user->isAdmin() || $user->role === 'personnel_admin') {
            // Admin et Personnel Admin voient toutes les classes
            $classes = Classe::actif()
                ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                    if ($anneeScolaireActive) {
                        $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                    }
                }])
                ->get();
        } else if ($user->isTeacher()) {
            // Enseignant voit seulement ses classes
            $enseignant = $user->enseignant;
            $classes = Classe::actif()
                ->whereHas('emploisTemps', function($query) use ($enseignant) {
                    $query->where('enseignant_id', $enseignant->id);
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

        return view('notes.mensuel.index', compact('classes'));
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
            ->with('utilisateur')
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

        // Récupérer les élèves de la classe pour l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        $eleves = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->with('utilisateur')
            ->get();
            
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
            'notes.*.note' => 'required|numeric|min:0|max:20',
            'notes.*.coefficient' => 'required|integer|min:1|max:10'
        ]);

        $classeId = $request->classe_id;
        $enseignantId = $request->enseignant_id;
        $matiereId = $request->matiere_id;
        $mois = $request->mois;
        $annee = $request->annee;
        $createdBy = auth()->id();

        DB::beginTransaction();
        try {
            foreach ($request->notes as $noteData) {
                // Vérifier si un test existe déjà
                $testExistant = TestMensuel::where('eleve_id', $noteData['eleve_id'])
                    ->where('matiere_id', $matiereId)
                    ->where('mois', $mois)
                    ->where('annee', $annee)
                    ->first();

                if ($testExistant) {
                    // Mettre à jour le test existant
                    $testExistant->update([
                        'enseignant_id' => $enseignantId,
                        'note' => $noteData['note'],
                        'coefficient' => $noteData['coefficient']
                    ]);
                } else {
                    // Créer un nouveau test
                    TestMensuel::create([
                        'eleve_id' => $noteData['eleve_id'],
                        'classe_id' => $classeId,
                        'matiere_id' => $matiereId,
                        'enseignant_id' => $enseignantId,
                        'mois' => $mois,
                        'annee' => $annee,
                        'note' => $noteData['note'],
                        'coefficient' => $noteData['coefficient'],
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

        // Calculer les moyennes et rangs
        $resultats = [];
        foreach ($eleves as $eleve) {
            $moyenne = TestMensuel::calculerMoyenneMensuelle($eleve->id, $mois, $annee);
            if ($moyenne !== null) {
                $resultats[] = [
                    'eleve' => $eleve,
                    'moyenne' => $moyenne
                ];
            }
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

        // Calculer les moyennes et rangs
        $resultats = [];
        foreach ($eleves as $eleve) {
            $moyenne = TestMensuel::calculerMoyenneMensuelle($eleve->id, $mois, $annee);
            if ($moyenne !== null) {
                $resultats[] = [
                    'eleve' => $eleve,
                    'moyenne' => $moyenne
                ];
            }
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
}
