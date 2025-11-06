<?php

namespace App\Http\Controllers;

use App\Models\EmploiTemps;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            ->whereHas('eleves', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->with(['eleves' => function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }])
            ->orderBy('nom')
            ->get();
        $matieres = Matiere::actif()->orderBy('nom')->get();
        $enseignants = Enseignant::where('enseignants.actif', true)
            ->with('utilisateur')
            ->join('utilisateurs', 'enseignants.utilisateur_id', '=', 'utilisateurs.id')
            ->orderBy('utilisateurs.name')
            ->select('enseignants.*')
            ->get();
        
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
        
        // Vérifier que la classe a des élèves de l'année active
        $hasElevesActiveYear = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->exists();
            
        if (!$hasElevesActiveYear) {
            return redirect()->back()->with('error', 'Cette classe n\'a pas d\'élèves pour l\'année scolaire active.');
        }
        
        $emploisTemps = EmploiTemps::where('classe_id', $classe->id)
            ->actif()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get();
            
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $heures = ['08:00', '10:00', '10:10', '12:10', '14:00', '14:30', '16:00', '16:30'];
        
        return view('emplois-temps.show', compact('classe', 'emploisTemps', 'jours', 'heures', 'anneeScolaireActive'));
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
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'salle' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Vérifier les conflits d'horaires (sauf si on force)
        if (!$request->has('force') || !$request->force) {
            // D'abord, vérifier s'il y a un conflit avec la même matière (même horaire exact)
            $memeMatiereConflit = EmploiTemps::where('classe_id', $request->classe_id)
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
            
            $message = 'Conflit d\'horaire détecté pour cette classe. ';
            if ($creneauxConflits->count() > 0) {
                $message .= 'Créneaux existants: ';
                foreach ($creneauxConflits as $creneau) {
                    $message .= $creneau->matiere->nom . ' (' . $creneau->heure_debut . '-' . $creneau->heure_fin . '), ';
                }
                $message = rtrim($message, ', ');
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
                'jour_semaine' => strtolower($request->jour), // Convertir 'jour' en 'jour_semaine' en minuscules
                'heure_debut' => $request->heure_debut,
                'heure_fin' => $request->heure_fin,
                'salle' => $request->salle,
                'type_cours' => 'cours', // Valeur par défaut
                'date_debut' => now()->startOfYear(), // Date de début de l'année
                'date_fin' => now()->endOfYear(), // Date de fin de l'année
                'actif' => true
            ];

            $emploiTemps = EmploiTemps::create($data);

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
        
        $emploiTemps->delete();
        
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

        $sourceEmplois = EmploiTemps::where('classe_id', $request->source_classe_id)->get();
        
        // Supprimer l'emploi du temps existant de la classe cible
        EmploiTemps::where('classe_id', $request->target_classe_id)->delete();
        
        // Dupliquer les créneaux
        foreach ($sourceEmplois as $emploi) {
            EmploiTemps::create([
                'classe_id' => $request->target_classe_id,
                'matiere_id' => $emploi->matiere_id,
                'enseignant_id' => $emploi->enseignant_id,
                'jour_semaine' => $emploi->jour_semaine,
                'heure_debut' => $emploi->heure_debut,
                'heure_fin' => $emploi->heure_fin,
                'salle' => $emploi->salle,
                'type_cours' => $emploi->type_cours ?? 'cours',
                'date_debut' => $emploi->date_debut ?? now()->startOfYear(),
                'date_fin' => $emploi->date_fin ?? now()->endOfYear(),
                'actif' => $emploi->actif ?? true,
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Emploi du temps dupliqué avec succès'
        ]);
    }

    /**
     * Exporter l'emploi du temps d'une classe
     */
    public function export(Classe $classe)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à exporter les emplois du temps.');
        }
        
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Vérifier que la classe a des élèves de l'année active
        $hasElevesActiveYear = $classe->eleves()
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->exists();
            
        if (!$hasElevesActiveYear) {
            return redirect()->back()->with('error', 'Cette classe n\'a pas d\'élèves pour l\'année scolaire active.');
        }
        
        $emploisTemps = EmploiTemps::where('classe_id', $classe->id)
            ->actif()
            ->with(['matiere', 'enseignant.utilisateur'])
            ->orderBy('jour_semaine')
            ->orderBy('heure_debut')
            ->get();

        $filename = 'emploi_temps_' . str_replace(' ', '_', $classe->nom) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT'
        ];
        
        $callback = function() use ($emploisTemps, $classe) {
            $file = fopen('php://output', 'w');
            
            // Ajouter le BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes CSV avec point-virgule pour Excel français
            fputcsv($file, [
                'Classe',
                'Jour',
                'Heure Début',
                'Heure Fin',
                'Matière',
                'Code Matière',
                'Enseignant',
                'Salle'
            ], ';');
            
            // Données
            foreach ($emploisTemps as $emploi) {
                // Formater les heures (HH:MM seulement)
                $heureDebut = date('H:i', strtotime($emploi->heure_debut));
                $heureFin = date('H:i', strtotime($emploi->heure_fin));
                
                // Nom de l'enseignant
                $enseignantNom = '';
                if ($emploi->enseignant && $emploi->enseignant->utilisateur) {
                    $enseignantNom = $emploi->enseignant->utilisateur->nom . ' ' . $emploi->enseignant->utilisateur->prenom;
                }
                
                fputcsv($file, [
                    $classe->nom,
                    ucfirst($emploi->jour_semaine),
                    $heureDebut,
                    $heureFin,
                    $emploi->matiere->nom,
                    $emploi->matiere->code,
                    $enseignantNom,
                    $emploi->salle
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Effacer tout l'emploi du temps
     */
    public function deleteAll()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('emplois-temps.delete')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer tous les emplois du temps.');
        }
        
        EmploiTemps::truncate();
        
        return redirect()->route('emplois-temps.index')
            ->with('success', 'Tous les emplois du temps ont été supprimés');
    }
}
