<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Eleve;
use App\Models\Enseignant;
use App\Models\ParentModel;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Paiement;
use App\Models\Absence;
use App\Models\Note;
use App\Models\Depense;
use App\Models\Entree;
use App\Models\SalaireEnseignant;

class StatistiqueController extends Controller
{
    /**
     * Afficher les statistiques générales
     */
    public function index()
    {
        // Statistiques de base
        $stats = [
            'eleves' => Eleve::count(),
            'enseignants' => Enseignant::count(),
            'parents' => ParentModel::count(),
            'classes' => Classe::count(),
            'matieres' => Matiere::count(),
        ];

        // Statistiques financières (année scolaire en cours)
        $dateDebutAnnee = now()->startOfYear();
        $dateFinAnnee = now()->endOfYear();
        
        $totalEntreesManuelles = Entree::where('source', '!=', 'Paiements scolaires')
            ->whereBetween('date_entree', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant');
        $totalPaiementsFrais = Paiement::whereHas('fraisScolarite')
            ->whereBetween('date_paiement', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant_paye');
        $totalEntrees = $totalEntreesManuelles + $totalPaiementsFrais;
        
        $totalSortiesManuelles = Depense::where('type_depense', '!=', 'salaire_enseignant')
            ->whereBetween('date_depense', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant');
        $totalSalairesEnseignants = SalaireEnseignant::where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('salaire_net');
        $totalSorties = $totalSortiesManuelles + $totalSalairesEnseignants;
        
        $solde = $totalEntrees - $totalSorties;
        
        $statsFinancieres = [
            'totalEntrees' => $totalEntrees,
            'totalSorties' => $totalSorties,
            'solde' => $solde,
            'totalPaiementsFrais' => $totalPaiementsFrais,
            'totalEntreesManuelles' => $totalEntreesManuelles,
            'totalSortiesManuelles' => $totalSortiesManuelles,
            'totalSalairesEnseignants' => $totalSalairesEnseignants,
        ];

        // Répartition des élèves par classe
        $elevesParClasse = DB::table('eleves')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->select('classes.nom', DB::raw('count(*) as total'))
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('classes.nom')
            ->get();

        // Répartition des élèves par sexe
        $elevesParSexe = DB::table('eleves')
            ->join('utilisateurs', 'eleves.utilisateur_id', '=', 'utilisateurs.id')
            ->select('utilisateurs.sexe', DB::raw('count(*) as total'))
            ->groupBy('utilisateurs.sexe')
            ->get();

        // Évolution des paiements par mois (12 derniers mois)
        $paiementsParMois = DB::table('paiements')
            ->select(
                DB::raw('YEAR(date_paiement) as annee'),
                DB::raw('MONTH(date_paiement) as mois'),
                DB::raw('SUM(montant_paye) as total')
            )
            ->where('date_paiement', '>=', now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();

        // Absences par mois (12 derniers mois)
        $absencesParMois = DB::table('absences')
            ->select(
                DB::raw('YEAR(date_absence) as annee'),
                DB::raw('MONTH(date_absence) as mois'),
                DB::raw('count(*) as total')
            )
            ->where('date_absence', '>=', now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();

        // Top 5 des classes avec le plus d'absences
        $topAbsencesParClasse = DB::table('absences')
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->select('classes.nom', DB::raw('count(*) as total'))
            ->where('absences.date_absence', '>=', now()->subMonths(6))
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Moyennes par matière (si des notes existent)
        $moyennesParMatiere = DB::table('notes')
            ->join('matieres', 'notes.matiere_id', '=', 'matieres.id')
            ->select('matieres.nom', DB::raw('AVG(notes.note) as moyenne'))
            ->groupBy('matieres.id', 'matieres.nom')
            ->orderBy('moyenne', 'desc')
            ->get();

        // Répartition des types d'absences
        $typesAbsences = DB::table('absences')
            ->select('type', DB::raw('count(*) as total'))
            ->where('date_absence', '>=', now()->subMonths(6))
            ->groupBy('type')
            ->get();

        // Évolution des dépenses par mois
        $depensesParMois = DB::table('depenses')
            ->select(
                DB::raw('YEAR(date_depense) as annee'),
                DB::raw('MONTH(date_depense) as mois'),
                DB::raw('SUM(montant) as total')
            )
            ->where('statut', 'paye')
            ->where('date_depense', '>=', now()->subMonths(12))
            ->groupBy('annee', 'mois')
            ->orderBy('annee', 'asc')
            ->orderBy('mois', 'asc')
            ->get();

        return view('statistiques.generales', compact(
            'stats',
            'statsFinancieres',
            'elevesParClasse',
            'elevesParSexe',
            'paiementsParMois',
            'absencesParMois',
            'topAbsencesParClasse',
            'moyennesParMatiere',
            'typesAbsences',
            'depensesParMois'
        ));
    }

    /**
     * Afficher les statistiques financières
     */
    public function financieres()
    {
        // Statistiques financières (année scolaire en cours)
        $dateDebutAnnee = now()->startOfYear();
        $dateFinAnnee = now()->endOfYear();
        
        $totalEntreesManuelles = Entree::where('source', '!=', 'Paiements scolaires')
            ->whereBetween('date_entree', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant');
        $totalPaiementsFrais = Paiement::whereHas('fraisScolarite')
            ->whereBetween('date_paiement', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant_paye');
        $totalEntrees = $totalEntreesManuelles + $totalPaiementsFrais;
        
        $totalSortiesManuelles = Depense::where('type_depense', '!=', 'salaire_enseignant')
            ->whereBetween('date_depense', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('montant');
        $totalSalairesEnseignants = SalaireEnseignant::where('statut', 'payé')
            ->whereBetween('date_paiement', [$dateDebutAnnee, $dateFinAnnee])
            ->sum('salaire_net');
        $totalSorties = $totalSortiesManuelles + $totalSalairesEnseignants;
        
        $solde = $totalEntrees - $totalSorties;
        
        $statsFinancieres = [
            'totalEntrees' => $totalEntrees,
            'totalSorties' => $totalSorties,
            'solde' => $solde,
            'totalPaiementsFrais' => $totalPaiementsFrais,
            'totalEntreesManuelles' => $totalEntreesManuelles,
            'totalSortiesManuelles' => $totalSortiesManuelles,
            'totalSalairesEnseignants' => $totalSalairesEnseignants,
        ];

        // Paiements par mois (12 derniers mois)
        $paiementsParMois = Paiement::selectRaw('MONTH(date_paiement) as mois, SUM(montant_paye) as total, COUNT(*) as count')
            ->where('date_paiement', '>=', now()->subMonths(12))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Derniers paiements
        $derniersPaiements = Paiement::with(['fraisScolarite.eleve.utilisateur'])
            ->orderBy('date_paiement', 'desc')
            ->limit(10)
            ->get();

        return view('statistiques.financieres', compact(
            'statsFinancieres',
            'paiementsParMois',
            'derniersPaiements'
        ));
    }

    /**
     * Afficher les statistiques d'absences
     */
    public function absences()
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::anneeActive();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée. Veuillez activer une année scolaire.');
        }
        
        // Statistiques d'absences (filtrées par année scolaire active)
        $statsAbsences = [
            'total' => Absence::whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })->count(),
            'justifiees' => Absence::whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })->where('statut', 'justifiee')->count(),
            'non_justifiees' => Absence::whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })->where('statut', 'non_justifiee')->count(),
            'en_attente' => Absence::whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })->where('statut', 'en_attente')->count(),
        ];

        // Taux d'absentéisme (filtré par année scolaire active)
        $totalEleves = \App\Models\Eleve::where('annee_scolaire_id', $anneeScolaireActive->id)->count();
        $statsAbsences['taux_absenteisme'] = $totalEleves > 0 ? 
            round(($statsAbsences['total'] / $totalEleves) * 100, 1) : 0;

        // Absences par mois (12 derniers mois, filtrées par année scolaire active)
        $absencesParMois = Absence::selectRaw('MONTH(date_absence) as mois, COUNT(*) as total')
            ->whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->where('date_absence', '>=', now()->subMonths(12))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Types d'absences (filtrées par année scolaire active)
        $typesAbsences = Absence::selectRaw('type, COUNT(*) as total')
            ->whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->where('date_absence', '>=', now()->subMonths(6))
            ->groupBy('type')
            ->get();

        // Dernières absences (filtrées par année scolaire active)
        $dernieresAbsences = Absence::whereHas('eleve', function($query) use ($anneeScolaireActive) {
                $query->where('annee_scolaire_id', $anneeScolaireActive->id);
            })
            ->with(['eleve.utilisateur', 'matiere'])
            ->orderBy('date_absence', 'desc')
            ->limit(10)
            ->get();

        // Top 5 des classes avec le plus d'absences (filtrées par année scolaire active)
        $topAbsencesParClasse = DB::table('absences')
            ->join('eleves', 'absences.eleve_id', '=', 'eleves.id')
            ->join('classes', 'eleves.classe_id', '=', 'classes.id')
            ->select(
                'classes.nom',
                DB::raw('count(*) as total'),
                DB::raw('count(*) * 100.0 / (SELECT COUNT(*) FROM eleves e2 WHERE e2.classe_id = classes.id AND e2.annee_scolaire_id = ' . $anneeScolaireActive->id . ') as taux_absenteisme')
            )
            ->where('eleves.annee_scolaire_id', $anneeScolaireActive->id)
            ->where('absences.date_absence', '>=', now()->subMonths(6))
            ->groupBy('classes.id', 'classes.nom')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('statistiques.absences', compact(
            'statsAbsences',
            'absencesParMois',
            'typesAbsences',
            'dernieresAbsences',
            'topAbsencesParClasse',
            'anneeScolaireActive'
        ));
    }

    /**
     * API pour récupérer les données des graphiques
     */
    public function apiData(Request $request)
    {
        $type = $request->get('type');

        switch ($type) {
            case 'eleves_par_classe':
                return response()->json(
                    DB::table('eleves')
                        ->join('classes', 'eleves.classe_id', '=', 'classes.id')
                        ->select('classes.nom as label', DB::raw('count(*) as value'))
                        ->groupBy('classes.id', 'classes.nom')
                        ->get()
                );

            case 'paiements_par_mois':
                return response()->json(
                    DB::table('paiements')
                        ->select(
                            DB::raw('CONCAT(YEAR(date_paiement), "-", LPAD(MONTH(date_paiement), 2, "0")) as label'),
                            DB::raw('SUM(montant_paye) as value')
                        )
                        ->where('date_paiement', '>=', now()->subMonths(12))
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get()
                );

            case 'absences_par_mois':
                return response()->json(
                    DB::table('absences')
                        ->select(
                            DB::raw('CONCAT(YEAR(date_absence), "-", LPAD(MONTH(date_absence), 2, "0")) as label'),
                            DB::raw('count(*) as value')
                        )
                        ->where('date_absence', '>=', now()->subMonths(12))
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get()
                );

            default:
                return response()->json(['error' => 'Type non reconnu'], 400);
        }
    }
}
