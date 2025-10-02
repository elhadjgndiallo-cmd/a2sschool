<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Absence;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Afficher la liste des élèves de l'enseignant
     */
    public function mesEleves()
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        // Récupérer les classes où l'enseignant enseigne
        $classes = Classe::actif()
            ->whereHas('emploisTemps', function($query) use ($enseignant) {
                $query->where('enseignant_id', $enseignant->id);
            })
            ->with(['eleves.utilisateur'])
            ->get();

        // Récupérer tous les élèves de ces classes
        $eleves = collect();
        foreach ($classes as $classe) {
            $eleves = $eleves->merge($classe->eleves);
        }

        // Supprimer les doublons
        $eleves = $eleves->unique('id');

        return view('teacher.mes-eleves', compact('eleves', 'classes'));
    }

    /**
     * Afficher les classes de l'enseignant
     */
    public function classes()
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        // Récupérer les classes où l'enseignant enseigne
        $classes = Classe::actif()
            ->whereHas('emploisTemps', function($query) use ($enseignant) {
                $query->where('enseignant_id', $enseignant->id);
            })
            ->with(['eleves.utilisateur'])
            ->get();

        return view('teacher.classes', compact('classes'));
    }

    /**
     * Afficher les élèves d'une classe spécifique
     */
    public function elevesClasse($classeId)
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        $classe = Classe::with(['eleves.utilisateur'])->findOrFail($classeId);
        
        // Vérifier que l'enseignant enseigne dans cette classe
        $hasAccess = $classe->emploisTemps()
            ->where('enseignant_id', $enseignant->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à cette classe.');
        }

        return view('teacher.eleves-classe', compact('classe'));
    }

    /**
     * Afficher l'emploi du temps de l'enseignant
     */
    public function emploiTemps()
    {
        try {
            $user = Auth::user();
            $enseignant = $user->enseignant;
            
            if (!$enseignant) {
                \Log::error('Profil enseignant non trouvé', ['user_id' => $user->id]);
                abort(403, 'Profil enseignant non trouvé');
            }

            // Récupérer l'emploi du temps de l'enseignant
            $emploisTemps = $enseignant->emploisTemps()
                ->with(['classe', 'matiere'])
                ->actif()
                ->orderBy('jour_semaine')
                ->orderBy('heure_debut')
                ->get();

            \Log::info('Emploi du temps enseignant chargé', [
                'enseignant_id' => $enseignant->id,
                'emplois_count' => $emploisTemps->count()
            ]);

            return view('teacher.emploi-temps', compact('emploisTemps'));
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors du chargement de l\'emploi du temps enseignant', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('teacher.emploi-temps', ['emploisTemps' => collect()])
                ->with('error', 'Erreur lors du chargement de l\'emploi du temps. Veuillez contacter l\'administrateur.');
        }
    }

    /**
     * Afficher le formulaire de saisie des absences
     */
    public function saisirAbsences()
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        // Récupérer les classes où l'enseignant enseigne
        $classes = Classe::actif()
            ->whereHas('emploisTemps', function($query) use ($enseignant) {
                $query->where('enseignant_id', $enseignant->id);
            })
            ->with(['eleves.utilisateur'])
            ->get();

        return view('teacher.saisir-absences', compact('classes'));
    }

    /**
     * Afficher le formulaire de saisie des absences pour une classe spécifique
     */
    public function saisirAbsencesClasse($classeId)
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        $classe = Classe::with(['eleves.utilisateur'])->findOrFail($classeId);
        
        // Vérifier que l'enseignant enseigne dans cette classe
        $hasAccess = $classe->emploisTemps()
            ->where('enseignant_id', $enseignant->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à cette classe.');
        }

        return view('teacher.saisir-absences-classe', compact('classe'));
    }

    /**
     * Enregistrer les absences
     */
    public function storeAbsences(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'date_absence' => 'required|date',
            'absences' => 'required|array',
            'absences.*.eleve_id' => 'required|exists:eleves,id',
            'absences.*.present' => 'required|in:0,1',
            'absences.*.justifie' => 'nullable|in:1',
            'absences.*.motif' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        try {
            DB::transaction(function() use ($request, $enseignant) {
                $absencesEnregistrees = 0;
                
                foreach ($request->absences as $index => $absenceData) {
                    // Vérifier si l'élève est présent (radio button sélectionné)
                    $isPresent = isset($absenceData['present']) && $absenceData['present'] == '1';
                    
                    // Debug: logger les valeurs reçues
                    \Log::info("Élève {$index}: present=" . ($absenceData['present'] ?? 'null') . ", isPresent=" . ($isPresent ? 'true' : 'false'));
                    
                    if (!$isPresent) {
                        Absence::create([
                            'eleve_id' => $absenceData['eleve_id'],
                            'matiere_id' => null, // Pas de matière spécifique pour l'appel général
                            'date_absence' => $request->date_absence,
                            'motif' => $absenceData['motif'] ?? null,
                            'statut' => isset($absenceData['justifie']) && $absenceData['justifie'] == '1' ? 'justifiee' : 'non_justifiee',
                            'saisi_par' => $enseignant->utilisateur_id
                        ]);
                        $absencesEnregistrees++;
                    }
                }
                
                \Log::info("Total absences enregistrées: {$absencesEnregistrees}");
            });

            return redirect()->route('teacher.absences')
                ->with('success', 'Absences enregistrées avec succès');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement des absences: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher le formulaire de saisie des notes pour une classe
     */
    public function saisirNotes($classeId)
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        $classe = Classe::with(['eleves.utilisateur'])->findOrFail($classeId);
        
        // Vérifier que l'enseignant enseigne dans cette classe
        $hasAccess = $classe->emploisTemps()
            ->where('enseignant_id', $enseignant->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à cette classe.');
        }
        
        // Récupérer toutes les matières actives (temporaire pour résoudre le problème)
        $matieres = \App\Models\Matiere::actif()->get();
        
        // Debug: vérifier les matières
        \Log::info('Matières disponibles pour enseignant ID: ' . $enseignant->id, [
            'matieres_count' => $matieres->count(),
            'matieres' => $matieres->pluck('nom', 'id')->toArray()
        ]);
            
        $enseignants = collect([$enseignant]);

        return view('teacher.saisir-notes', compact('classe', 'matieres', 'enseignants'));
    }

    /**
     * Enregistrer les notes
     */
    public function storeNotes(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'notes' => 'required|array',
            'notes.*.eleve_id' => 'required|exists:eleves,id',
            'notes.*.matiere_id' => 'required|exists:matieres,id',
            'notes.*.note_cours' => 'nullable|numeric|min:0|max:20',
            'notes.*.note_composition' => 'nullable|numeric|min:0|max:20',
            'notes.*.coefficient' => 'required|numeric|min:1|max:10',
            'notes.*.commentaire' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        try {
            DB::transaction(function() use ($request, $enseignant) {
                foreach ($request->notes as $noteData) {
                    if ($noteData['note_cours'] || $noteData['note_composition']) {
                        Note::create([
                            'eleve_id' => $noteData['eleve_id'],
                            'matiere_id' => $noteData['matiere_id'],
                            'enseignant_id' => $enseignant->id,
                            'note_cours' => $noteData['note_cours'],
                            'note_composition' => $noteData['note_composition'],
                            'coefficient' => $noteData['coefficient'],
                            'commentaire' => $noteData['commentaire'],
                            'date_evaluation' => now()->toDateString(),
                            'type_evaluation' => 'devoir',
                            'periode' => 'trimestre1'
                        ]);
                    }
                }
            });

            return redirect()->route('teacher.mes-eleves')
                ->with('success', 'Notes enregistrées avec succès');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement des notes: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher le profil d'un élève (pour modal AJAX)
     */
    public function profilEleve($eleveId)
    {
        $user = Auth::user();
        $enseignant = $user->enseignant;
        
        if (!$enseignant) {
            abort(403, 'Profil enseignant non trouvé');
        }

        // Récupérer l'élève avec ses informations
        $eleve = Eleve::with(['utilisateur', 'classe', 'notes.matiere', 'absences'])
            ->findOrFail($eleveId);

        // Vérifier que l'enseignant a accès à cet élève
        $hasAccess = Classe::whereHas('emploisTemps', function($query) use ($enseignant) {
            $query->where('enseignant_id', $enseignant->id);
        })->whereHas('eleves', function($query) use ($eleveId) {
            $query->where('id', $eleveId);
        })->exists();

        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à ce profil d\'élève');
        }

        // Récupérer les statistiques de l'élève
        $statistiques = [
            'notes_count' => $eleve->notes->count(),
            'absences_count' => $eleve->absences->count(),
            'moyenne_generale' => $eleve->notes->avg('note') ?? 0,
            'derniere_absence' => $eleve->absences->sortByDesc('date_absence')->first()
        ];

        if (request()->ajax()) {
            return view('teacher.partials.profil-eleve', compact('eleve', 'statistiques'));
        }

        return view('teacher.profil-eleve', compact('eleve', 'statistiques'));
    }
}