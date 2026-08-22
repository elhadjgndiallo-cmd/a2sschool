<?php

namespace App\Http\Controllers;

use App\Models\EmploiTemps;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class EmploiTempsController extends Controller
{
    /**
     * Afficher la gestion générale des emplois du temps
     */
    public function index()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les emplois du temps.');
        }
        
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        $classes = Classe::actif()
            ->where(function ($q) use ($anneeScolaireActive) {
                $q->whereHas('eleves', function ($query) use ($anneeScolaireActive) {
                    $query->where('annee_scolaire_id', $anneeScolaireActive->id);
                })->orWhereHas('emploisTemps', function ($query) use ($anneeScolaireActive) {
                    $query->where('actif', true)
                        ->where('annee_scolaire_id', $anneeScolaireActive->id);
                });
            })
            ->with(['eleves' => function ($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }])
            ->withCount([
                'emploisTemps as creneaux_annee' => function ($query) use ($anneeScolaireActive) {
                    $query->where('actif', true)
                        ->where('annee_scolaire_id', $anneeScolaireActive->id);
                },
            ])
            ->orderBy('nom')
            ->get();
        // Catalogue complet pour pouvoir créer de nouveaux créneaux
        $matieres = Matiere::actif()->orderBy('nom')->get();
        $enseignants = Enseignant::listeDeroulante($anneeScolaireActive?->id);
        
        return view('emplois-temps.index', compact('classes', 'matieres', 'enseignants', 'anneeScolaireActive'));
    }

    /**
     * Afficher l'emploi du temps d'une classe
     */
    public function show(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à voir les emplois du temps.');
        }
        
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Toujours afficher la grille de la classe (même vide) pour pouvoir créer l'EDT
        $emploisTemps = EmploiTemps::where('classe_id', $classe->id)
            ->actif()
            ->pourAnneeScolaire($anneeScolaireActive->id)
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get();
            
        // Pour le primaire, inclure samedi
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        
        $isPrimaire = $classe->isPrimaire();
        
        // Organiser les emplois par jour
        $emploisParJour = [];
        foreach ($jours as $jour) {
            $emploisParJour[$jour] = $emploisTemps->filter(function ($emploi) use ($jour) {
                return $emploi->jour_semaine === $jour;
            })->sortBy('heure_debut')->values();
        }
        
        $enseignants = Enseignant::listeDeroulante($anneeScolaireActive?->id);

        if ($isPrimaire) {
            $cfgPrimaire = config('emploi_temps.primaire');
            $dureesAutorisees = $cfgPrimaire['durees_autorisees'] ?? [30, 45, 60];
            $dureeDefaut = $cfgPrimaire['duree_defaut'] ?? 45;
            $recre = $cfgPrimaire['recre'] ?? null;
            $journee = $cfgPrimaire['journee'] ?? ['debut' => '08:00', 'fin' => '15:00'];

            // Grille dynamique : uniquement les plages des créneaux déjà ajoutés
            $plagesHoraires = $emploisTemps
                ->map(function ($emploi) {
                    return [
                        'debut' => \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i'),
                        'fin' => \Carbon\Carbon::parse($emploi->heure_fin)->format('H:i'),
                        'recre' => false,
                    ];
                })
                ->unique(fn ($p) => $p['debut'] . '-' . $p['fin'])
                ->sortBy('debut')
                ->values()
                ->all();

            // Afficher la récré seulement s'il y a déjà des cours
            if ($recre && count($plagesHoraires) > 0) {
                $plagesHoraires[] = [
                    'debut' => $recre['debut'],
                    'fin' => $recre['fin'],
                    'recre' => true,
                    'label' => $recre['label'] ?? 'RÉCRÉATION',
                ];
                usort($plagesHoraires, fn ($a, $b) => strcmp($a['debut'], $b['debut']));
            }

            $maxCreneauxParJour = $cfgPrimaire['max_creneaux_par_jour'] ?? 12;

            return view('emplois-temps.show-primaire', compact(
                'classe',
                'emploisTemps',
                'jours',
                'plagesHoraires',
                'emploisParJour',
                'anneeScolaireActive',
                'enseignants',
                'dureesAutorisees',
                'dureeDefaut',
                'recre',
                'journee',
                'maxCreneauxParJour'
            ));
        }

        $dureeDefautSecondaire = (int) config('emploi_temps.secondaire.duree_defaut_minutes', 120);

        // Grille dynamique secondaire : grandit avec les créneaux ajoutés (durée 2 h)
        $heures = $emploisTemps
            ->map(fn ($emploi) => \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('emplois-temps.show', compact(
            'classe',
            'emploisTemps',
            'jours',
            'heures',
            'emploisParJour',
            'anneeScolaireActive',
            'enseignants',
            'dureeDefautSecondaire'
        ));
    }

    /**
     * Créer ou modifier un créneau d'emploi du temps
     */
    public function store(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.create')) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à créer des emplois du temps.'], 403);
        }
        $validator = Validator::make($request->all(), [
            'classe_id' => 'required|exists:classes,id',
            'matiere_id' => 'required|exists:matieres,id',
            'enseignant_id' => 'required|exists:enseignants,id',
            'jour' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,lundi,mardi,mercredi,jeudi,vendredi,samedi',
            'heure_debut' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'heure_fin' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'salle' => 'nullable|string|max:50'
        ]);

        // Debug pour voir les données reçues et les erreurs
        \Log::info('=== DEBUG EMPLOI TEMPS STORE ===');
        \Log::info('Données reçues:', $request->all());
        
        if ($validator->fails()) {
            \Log::error('Erreurs de validation:', $validator->errors()->toArray());
            \Log::error('=== FIN DEBUG EMPLOI TEMPS ===');
            return response()->json([
                'success' => false,
                'message' => collect($validator->errors()->all())->implode(' '),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Normaliser HH:MM(:SS) → HH:MM
        $heureDebut = substr(str_pad($request->heure_debut, 5, '0', STR_PAD_LEFT), 0, 5);
        $heureFin = substr(str_pad($request->heure_fin, 5, '0', STR_PAD_LEFT), 0, 5);
        if (strlen($request->heure_debut) >= 5) {
            $heureDebut = substr($request->heure_debut, 0, 5);
        }
        if (strlen($request->heure_fin) >= 5) {
            $heureFin = substr($request->heure_fin, 0, 5);
        }
        // Forcer format 08:30 (pas 8:30)
        $partsD = explode(':', $heureDebut);
        $partsF = explode(':', $heureFin);
        $heureDebut = sprintf('%02d:%02d', (int) $partsD[0], (int) ($partsD[1] ?? 0));
        $heureFin = sprintf('%02d:%02d', (int) $partsF[0], (int) ($partsF[1] ?? 0));
        $request->merge(['heure_debut' => $heureDebut, 'heure_fin' => $heureFin]);

        if ($heureFin <= $heureDebut) {
            return response()->json([
                'success' => false,
                'message' => 'L\'heure de fin doit être après l\'heure de début.',
            ], 422);
        }
        
        \Log::info('Validation réussie', ['heure_debut' => $heureDebut, 'heure_fin' => $heureFin]);
        \Log::info('=== FIN DEBUG EMPLOI TEMPS ===');

        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        if (!$anneeScolaireActive) {
            return response()->json(['success' => false, 'message' => 'Aucune année scolaire active.'], 422);
        }

        $classe = Classe::findOrFail($request->classe_id);
        if ($classe->isPrimaire()) {
            $debut = \Carbon\Carbon::createFromFormat('H:i', $heureDebut);
            $fin = \Carbon\Carbon::createFromFormat('H:i', $heureFin);
            $dureeMinutes = (int) round(($fin->getTimestamp() - $debut->getTimestamp()) / 60);
            $dureesAutorisees = config('emploi_temps.primaire.durees_autorisees', [30, 45, 60]);

            if (!in_array($dureeMinutes, array_map('intval', $dureesAutorisees), true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au primaire, la durée du cours doit être de '
                        . implode(', ', $dureesAutorisees) . ' minutes (durée saisie : ' . $dureeMinutes . ' min).',
                ], 422);
            }

            $maxParJour = (int) config('emploi_temps.primaire.max_creneaux_par_jour', 12);
            $nbJour = EmploiTemps::where('classe_id', $classe->id)
                ->pourAnneeScolaire($anneeScolaireActive->id)
                ->where('jour_semaine', strtolower($request->jour))
                ->actif()
                ->count();
            if ($nbJour >= $maxParJour) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum {$maxParJour} créneaux par jour atteint pour cette classe.",
                ], 422);
            }
        }

        // Vérifier les conflits d'horaires (sauf si on force) — uniquement sur l'année active
        if (!$request->has('force') || !$request->force) {
            // D'abord, vérifier s'il y a un conflit avec la même matière (même horaire exact)
            $memeMatiereConflit = EmploiTemps::where('classe_id', $request->classe_id)
                ->pourAnneeScolaire($anneeScolaireActive->id)
                ->where('jour_semaine', strtolower($request->jour))
                ->where('matiere_id', $request->matiere_id)
                ->where('heure_debut', $request->heure_debut)
                ->where('heure_fin', $request->heure_fin)
                ->exists();
            
            if ($memeMatiereConflit) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ce créneau existe déjà pour cette matière à cet horaire exact'
                ], 422);
            }
            
            // Vérifier s'il y a un conflit d'horaire avec une matière différente
            $conflit = EmploiTemps::where('classe_id', $request->classe_id)
                ->pourAnneeScolaire($anneeScolaireActive->id)
                ->where('jour_semaine', strtolower($request->jour))
                ->where('matiere_id', '!=', $request->matiere_id) // Exclure la même matière
                ->where(function($query) use ($request) {
                    // Vérifier si le nouveau créneau chevauche avec un créneau existant
                    $query->where(function($q) use ($request) {
                        // Le nouveau créneau commence pendant un créneau existant
                        $q->where('heure_debut', '<=', $request->heure_debut)
                          ->where('heure_fin', '>', $request->heure_debut);
                    })->orWhere(function($q) use ($request) {
                        // Le nouveau créneau se termine pendant un créneau existant
                        $q->where('heure_debut', '<', $request->heure_fin)
                          ->where('heure_fin', '>=', $request->heure_fin);
                    })->orWhere(function($q) use ($request) {
                        // Le nouveau créneau englobe complètement un créneau existant
                        $q->where('heure_debut', '>=', $request->heure_debut)
                          ->where('heure_fin', '<=', $request->heure_fin);
                    });
                })
                ->exists();

            if ($conflit) {
            // Récupérer les créneaux en conflit pour donner plus d'informations
            $creneauxConflits = EmploiTemps::where('classe_id', $request->classe_id)
                ->pourAnneeScolaire($anneeScolaireActive->id)
                ->where('jour_semaine', strtolower($request->jour))
                ->where('matiere_id', '!=', $request->matiere_id) // Exclure la même matière
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('heure_debut', '<=', $request->heure_debut)
                          ->where('heure_fin', '>', $request->heure_debut);
                    })->orWhere(function($q) use ($request) {
                        $q->where('heure_debut', '<', $request->heure_fin)
                          ->where('heure_fin', '>=', $request->heure_fin);
                    })->orWhere(function($q) use ($request) {
                        $q->where('heure_debut', '>=', $request->heure_debut)
                          ->where('heure_fin', '<=', $request->heure_fin);
                    });
                })
                ->with(['matiere', 'enseignant.utilisateur'])
                ->get();
            
            $message = 'Conflit d\'horaire : ce créneau chevauche un cours déjà planifié. ';
            if ($creneauxConflits->count() > 0) {
                $details = [];
                $suggestion = null;
                foreach ($creneauxConflits as $creneau) {
                    $d = \Carbon\Carbon::parse($creneau->heure_debut)->format('H:i');
                    $f = \Carbon\Carbon::parse($creneau->heure_fin)->format('H:i');
                    $details[] = ($creneau->matiere->nom ?? 'Cours') . " ({$d}-{$f})";
                    if ($suggestion === null || $f > $suggestion) {
                        $suggestion = $f;
                    }
                }
                $message .= 'Occupé par : ' . implode(', ', $details) . '.';
                if ($suggestion) {
                    $message .= " Choisissez une heure de début à partir de {$suggestion} (ou cochez « Forcer » uniquement si nécessaire).";
                }
            }
            
                return response()->json([
                    'success' => false, 
                    'message' => $message
                ], 422);
            }
        }

        // Vérifier la disponibilité de l'enseignant (sauf si on force)
        if (!$request->has('force') || !$request->force) {
            // Vérifier si l'enseignant a déjà un cours avec une matière différente pendant ce créneau
            $enseignantOccupe = EmploiTemps::where('enseignant_id', $request->enseignant_id)
                ->pourAnneeScolaire($anneeScolaireActive->id)
                ->where('jour_semaine', strtolower($request->jour))
                ->where('matiere_id', '!=', $request->matiere_id) // Exclure la même matière
                ->where(function($query) use ($request) {
                    // Vérifier si l'enseignant a déjà un cours pendant ce créneau
                    $query->where(function($q) use ($request) {
                        // Le nouveau créneau commence pendant un créneau existant
                        $q->where('heure_debut', '<=', $request->heure_debut)
                          ->where('heure_fin', '>', $request->heure_debut);
                    })->orWhere(function($q) use ($request) {
                        // Le nouveau créneau se termine pendant un créneau existant
                        $q->where('heure_debut', '<', $request->heure_fin)
                          ->where('heure_fin', '>=', $request->heure_fin);
                    })->orWhere(function($q) use ($request) {
                        // Le nouveau créneau englobe complètement un créneau existant
                        $q->where('heure_debut', '>=', $request->heure_debut)
                          ->where('heure_fin', '<=', $request->heure_fin);
                    });
                })
                ->exists();

            if ($enseignantOccupe) {
                return response()->json([
                    'success' => false, 
                    'message' => 'L\'enseignant a déjà un cours d\'une autre matière à cet horaire'
                ], 422);
            }
        }

        try {
            // Préparer les données avec les champs requis
            $data = [
                'classe_id' => $request->classe_id,
                'matiere_id' => $request->matiere_id,
                'enseignant_id' => $request->enseignant_id,
                'annee_scolaire_id' => $anneeScolaireActive->id,
                'jour_semaine' => strtolower($request->jour), // Convertir 'jour' en 'jour_semaine' en minuscules
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'salle' => $request->salle,
                'type_cours' => 'cours', // Valeur par défaut
                'date_debut' => $anneeScolaireActive->date_debut ?? now()->startOfYear(),
                'date_fin' => $anneeScolaireActive->date_fin ?? now()->endOfYear(),
                'actif' => true
            ];

            $emploiTemps = EmploiTemps::create($data);
            $emploiTemps->enseignant?->synchroniserMatieresDepuisEmploiTemps();

            return response()->json([
                'success' => true, 
                'message' => 'Créneau ajouté avec succès',
                'emploi' => $emploiTemps->load(['matiere', 'enseignant.utilisateur'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création d\'un emploi du temps: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Erreur lors de la création du créneau: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un créneau
     */
    public function destroy(EmploiTemps $emploiTemps)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.delete')) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à supprimer des emplois du temps.'], 403);
        }
        
        $enseignant = $emploiTemps->enseignant;
        $emploiTemps->delete();
        $enseignant?->synchroniserMatieresDepuisEmploiTemps();
        
        return response()->json(['success' => true, 'message' => 'Créneau supprimé']);
    }

    /**
     * Dupliquer l'emploi du temps d'une classe vers une autre
     */
    public function duplicate(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à dupliquer des emplois du temps.');
        }
        
        $validator = Validator::make($request->all(), [
            'source_classe_id' => 'required|exists:classes,id',
            'target_classe_id' => 'required|exists:classes,id|different:source_classe_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        if (!$anneeScolaireActive) {
            return response()->json(['success' => false, 'message' => 'Aucune année scolaire active.'], 422);
        }

        $sourceEmplois = EmploiTemps::where('classe_id', $request->source_classe_id)
            ->pourAnneeScolaire($anneeScolaireActive->id)
            ->get();

        // Supprimer uniquement l'EDT de l'année active sur la classe cible
        EmploiTemps::where('classe_id', $request->target_classe_id)
            ->pourAnneeScolaire($anneeScolaireActive->id)
            ->delete();

        // Dupliquer les créneaux
        $enseignantsIds = [];
        foreach ($sourceEmplois as $emploi) {
            EmploiTemps::create([
                'classe_id' => $request->target_classe_id,
                'matiere_id' => $emploi->matiere_id,
                'enseignant_id' => $emploi->enseignant_id,
                'annee_scolaire_id' => $anneeScolaireActive->id,
                'jour_semaine' => $emploi->jour_semaine,
                'heure_debut' => $emploi->heure_debut,
                'heure_fin' => $emploi->heure_fin,
                'salle' => $emploi->salle,
                'type_cours' => $emploi->type_cours ?? 'cours',
                'date_debut' => $anneeScolaireActive->date_debut ?? $emploi->date_debut ?? now()->startOfYear(),
                'date_fin' => $anneeScolaireActive->date_fin ?? $emploi->date_fin ?? now()->endOfYear(),
                'actif' => $emploi->actif ?? true,
            ]);
            if ($emploi->enseignant_id) {
                $enseignantsIds[$emploi->enseignant_id] = true;
            }
        }

        foreach (array_keys($enseignantsIds) as $enseignantId) {
            \App\Models\Enseignant::find($enseignantId)?->synchroniserMatieresDepuisEmploiTemps();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Emploi du temps dupliqué avec succès'
        ]);
    }

    /**
     * Exporter / télécharger l'emploi du temps d'une classe (PDF ou CSV)
     */
    public function export(Classe $classe)
    {
        if (!auth()->user()->hasPermission('emplois-temps.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à exporter les emplois du temps.');
        }

        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();

        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }

        $emploisTemps = EmploiTemps::where('classe_id', $classe->id)
            ->actif()
            ->pourAnneeScolaire($anneeScolaireActive->id)
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get();

        $format = strtolower((string) request('format', 'pdf'));
        $safeNom = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classe->nom) ?: 'classe';
        $filenameBase = 'emploi_temps_' . $safeNom . '_' . date('Y-m-d');

        if ($format === 'csv') {
            return $this->exportCsv($classe, $emploisTemps, $filenameBase . '.csv');
        }

        return $this->exportPdf($classe, $emploisTemps, $anneeScolaireActive, $filenameBase . '.pdf');
    }

    /**
     * Export PDF (grille emploi du temps)
     */
    private function exportPdf(Classe $classe, $emploisTemps, $anneeScolaireActive, string $filename)
    {
        $isPrimaire = $classe->isPrimaire();
        $dureeDefautSecondaire = (int) config('emploi_temps.secondaire.duree_defaut_minutes', 120);
        $plages = [];

        if ($isPrimaire) {
            $plages = $emploisTemps
                ->map(function ($emploi) {
                    return [
                        'debut' => \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i'),
                        'fin' => \Carbon\Carbon::parse($emploi->heure_fin)->format('H:i'),
                        'recre' => false,
                    ];
                })
                ->unique(fn ($p) => $p['debut'] . '-' . $p['fin'])
                ->sortBy('debut')
                ->values()
                ->all();

            $recre = config('emploi_temps.primaire.recre');
            if ($recre && count($plages) > 0) {
                $plages[] = [
                    'debut' => $recre['debut'],
                    'fin' => $recre['fin'],
                    'recre' => true,
                    'label' => $recre['label'] ?? 'RÉCRÉATION',
                ];
                usort($plages, fn ($a, $b) => strcmp($a['debut'], $b['debut']));
            }
        } else {
            $plages = $emploisTemps
                ->map(function ($emploi) use ($dureeDefautSecondaire) {
                    $debut = \Carbon\Carbon::parse($emploi->heure_debut)->format('H:i');
                    $fin = \Carbon\Carbon::parse($emploi->heure_fin)->format('H:i');
                    if (!$fin) {
                        $parts = explode(':', $debut);
                        $startMin = ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
                        $endMin = $startMin + $dureeDefautSecondaire;
                        $fin = sprintf('%02d:%02d', intdiv($endMin, 60) % 24, $endMin % 60);
                    }
                    return [
                        'debut' => $debut,
                        'fin' => $fin,
                        'recre' => false,
                    ];
                })
                ->unique(fn ($p) => $p['debut'])
                ->sortBy('debut')
                ->values()
                ->all();
        }

        $schoolInfo = \App\Helpers\SchoolHelper::getSchoolInfo();
        $logoDataUri = null;
        if ($schoolInfo && !empty($schoolInfo->logo)) {
            $logoPath = storage_path('app/public/' . $schoolInfo->logo);
            if (is_file($logoPath)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $logoPath) ?: 'image/png';
                finfo_close($finfo);
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = Pdf::loadView('emplois-temps.export-pdf', compact(
            'classe',
            'emploisTemps',
            'plages',
            'anneeScolaireActive',
            'dureeDefautSecondaire',
            'logoDataUri'
        ));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download($filename);
    }

    /**
     * Export CSV
     */
    private function exportCsv(Classe $classe, $emploisTemps, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, must-revalidate',
        ];

        $callback = function () use ($emploisTemps, $classe) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Classe',
                'Jour',
                'Heure Début',
                'Heure Fin',
                'Matière',
                'Code Matière',
                'Enseignant',
                'Salle',
            ], ';');

            foreach ($emploisTemps as $emploi) {
                $heureDebut = date('H:i', strtotime($emploi->heure_debut));
                $heureFin = date('H:i', strtotime($emploi->heure_fin));

                $enseignantNom = '';
                if ($emploi->enseignant && $emploi->enseignant->utilisateur) {
                    $enseignantNom = $emploi->enseignant->utilisateur->nom . ' ' . $emploi->enseignant->utilisateur->prenom;
                }

                fputcsv($file, [
                    $classe->nom,
                    ucfirst($emploi->jour_semaine),
                    $heureDebut,
                    $heureFin,
                    $emploi->matiere->nom ?? '',
                    $emploi->matiere->code ?? '',
                    $enseignantNom,
                    $emploi->salle ?? '',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Effacer l'emploi du temps de l'année scolaire active uniquement
     */
    public function deleteAll()
    {
        if (!auth()->user()->hasPermission('emplois-temps.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer tous les emplois du temps.');
        }

        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        $deleted = EmploiTemps::pourAnneeScolaire($anneeScolaireActive->id)->delete();

        return redirect()->route('emplois-temps.index')
            ->with('success', "{$deleted} créneau(x) de l'année {$anneeScolaireActive->nom} ont été supprimés");
    }

}
