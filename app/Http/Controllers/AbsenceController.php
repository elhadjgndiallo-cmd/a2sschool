<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absence;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Matiere;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    /**
     * Afficher la liste des classes pour gestion des absences
     */
    public function index()
    {
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
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
        return view('absences.index', compact('classes'));
    }

    /**
     * Afficher le formulaire de saisie des absences pour une classe
     */
    public function saisir($classeId)
    {
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        $classe = Classe::with(['eleves' => function($query) use ($anneeScolaireActive) {
            if ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        }, 'eleves.utilisateur'])->findOrFail($classeId);
        $matieres = Matiere::actif()->get();
        
        // Récupérer les absences du jour pour l'année active
        $absencesAujourdhui = Absence::whereHas('eleve', function($query) use ($classeId, $anneeScolaireActive) {
            $query->where('classe_id', $classeId);
            if ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            }
        })->whereDate('date_absence', today())->with(['eleve', 'matiere'])->get();

        return view('absences.saisir', compact('classe', 'matieres', 'absencesAujourdhui'));
    }

    /**
     * Enregistrer les absences
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'classe_id' => 'required|exists:classes,id',
                'absences' => 'required|array',
                'absences.*.eleve_id' => 'required|exists:eleves,id',
                'absences.*.date_absence' => 'required|date',
                'absences.*.type' => 'nullable|in:absence,retard,sortie_anticipee',
                'absences.*.matiere_id' => 'nullable|exists:matieres,id',
                'absences.*.heure_debut' => 'nullable|date_format:H:i',
                'absences.*.heure_fin' => 'nullable|date_format:H:i',
                'absences.*.motif' => 'nullable|string',
            ]);

            $absencesCreees = 0;

            foreach ($request->absences as $index => $absenceData) {
                // Vérifier si l'élève est absent
                $isAbsent = !isset($absenceData['present']) || $absenceData['present'] != '1';

                if ($isAbsent) {
                    try {
                        Absence::create([
                            'eleve_id' => $absenceData['eleve_id'],
                            'matiere_id' => $absenceData['matiere_id'] ?? null,
                            'date_absence' => $absenceData['date_absence'],
                            'heure_debut' => $absenceData['heure_debut'] ?? null,
                            'heure_fin' => $absenceData['heure_fin'] ?? null,
                            'type' => $absenceData['type'] ?? $absenceData['type_hidden'] ?? 'absence',
                            'statut' => 'non_justifiee',
                            'motif' => $absenceData['motif'] ?? null,
                            'saisi_par' => auth()->id(),
                        ]);
                        $absencesCreees++;
                    } catch (\Exception $e) {
                        \Log::error("Erreur création absence pour l'élève {$index}: " . $e->getMessage());
                    }
                }
            }

            return redirect()->back()->with('success', "{$absencesCreees} absence(s) enregistrée(s) avec succès");

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'enregistrement des absences: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement des absences: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les absences d'un élève
     */
    public function eleveAbsences($eleveId)
    {
        // Récupérer l'année scolaire active pour filtrer les données
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        $eleve = Eleve::with(['utilisateur', 'classe'])->findOrFail($eleveId);
        
        // Vérifier que l'élève appartient à l'année active
        if ($anneeScolaireActive && $eleve->annee_scolaire_id != $anneeScolaireActive->id) {
            abort(404, 'Élève non trouvé pour l\'année scolaire active.');
        }
        
        $absences = Absence::where('eleve_id', $eleveId)
            ->with(['matiere', 'saisiPar'])
            ->orderBy('date_absence', 'desc')
            ->paginate(20);

        // Statistiques
        $totalAbsences = Absence::where('eleve_id', $eleveId)->count();
        $absencesNonJustifiees = Absence::where('eleve_id', $eleveId)
            ->where('statut', 'non_justifiee')->count();
        $retards = Absence::where('eleve_id', $eleveId)
            ->where('type', 'retard')->count();

        return view('absences.eleve', compact(
            'eleve', 
            'absences', 
            'totalAbsences', 
            'absencesNonJustifiees', 
            'retards'
        ));
    }

    /**
     * Justifier une absence
     */
    public function justifier(Request $request, $absenceId)
    {
        $request->validate([
            'motif' => 'required|string',
            'document_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $absence = Absence::findOrFail($absenceId);
        
        $documentPath = null;
        if ($request->hasFile('document_justificatif')) {
            $documentPath = $request->file('document_justificatif')
                ->store('justificatifs', 'public');
        }

        $absence->update([
            'statut' => 'justifiee',
            'motif' => $request->motif,
            'document_justificatif' => $documentPath,
        ]);

        return redirect()->back()->with('success', 'Absence justifiée avec succès');
    }

    /**
     * Rapport des absences par classe
     */
    public function rapportClasse($classeId)
    {
        $classe = Classe::findOrFail($classeId);
        $dateDebut = request('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = request('date_fin', now()->endOfMonth()->format('Y-m-d'));

        // Récupérer tous les élèves de la classe
        $tousLesEleves = $classe->eleves()->with('utilisateur')->get();

        // Récupérer toutes les absences pour la période
        $absences = Absence::whereHas('eleve', function($query) use ($classeId) {
            $query->where('classe_id', $classeId);
        })
        ->whereBetween('date_absence', [$dateDebut, $dateFin])
        ->with(['eleve.utilisateur', 'matiere'])
        ->get();

        // Grouper les absences par élève
        $absencesParEleve = $absences->groupBy('eleve_id');

        // Créer un rapport complet avec tous les élèves (présents et absents)
        $rapportComplet = $tousLesEleves->map(function($eleve) use ($absencesParEleve) {
            $absencesEleve = $absencesParEleve->get($eleve->id, collect());
            
            return [
                'eleve' => $eleve,
                'total_absences' => $absencesEleve->count(),
                'non_justifiees' => $absencesEleve->where('statut', 'non_justifiee')->count(),
                'justifiees' => $absencesEleve->where('statut', 'justifiee')->count(),
                'retards' => $absencesEleve->where('type', 'retard')->count(),
                'absences_completes' => $absencesEleve->where('type', 'absence')->count(),
                'absences' => $absencesEleve,
                'statut' => $absencesEleve->count() > 0 ? 'avec_absences' : 'present'
            ];
        });

        // Statistiques générales
        $statistiques = [
            'total_eleves' => $tousLesEleves->count(),
            'eleves_avec_absences' => $rapportComplet->where('statut', 'avec_absences')->count(),
            'eleves_presents' => $rapportComplet->where('statut', 'present')->count(),
            'total_absences' => $absences->count(),
            'total_non_justifiees' => $absences->where('statut', 'non_justifiee')->count(),
            'total_retards' => $absences->where('type', 'retard')->count(),
        ];

        return view('absences.rapport', compact(
            'classe', 
            'rapportComplet', 
            'statistiques',
            'dateDebut', 
            'dateFin'
        ));
    }

    /**
     * Notification automatique aux parents
     */
    public function notifierParents($absenceId)
    {
        $absence = Absence::with(['eleve.parents.utilisateur'])->findOrFail($absenceId);
        
        // Marquer comme notifié
        $absence->update(['notifie_parents_at' => now()]);

        // Ici vous pouvez ajouter la logique d'envoi d'email/SMS
        // Mail::to($parents)->send(new AbsenceNotification($absence));

        return redirect()->back()->with('success', 'Parents notifiés avec succès');
    }

    /**
     * Statistiques globales des absences
     */
    public function statistiques()
    {
        $totalAbsences = Absence::count();
        $absencesAujourdhui = Absence::whereDate('date_absence', today())->count();
        $absencesNonJustifiees = Absence::where('statut', 'non_justifiee')->count();
        $retardsAujourdhui = Absence::whereDate('date_absence', today())
            ->where('type', 'retard')->count();

        // Absences par classe
        $absencesParClasse = Absence::selectRaw('COUNT(*) as total, classes.nom')
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->groupBy('classes.id', 'classes.nom')
            ->get();

        // Évolution des absences sur les 30 derniers jours
        $evolutionAbsences = Absence::selectRaw('DATE(date_absence) as date, COUNT(*) as total')
            ->where('date_absence', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('absences.statistiques', compact(
            'totalAbsences',
            'absencesAujourdhui',
            'absencesNonJustifiees',
            'retardsAujourdhui',
            'absencesParClasse',
            'evolutionAbsences'
        ));
    }
}
