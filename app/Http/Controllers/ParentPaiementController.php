<?php

namespace App\Http\Controllers;

use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use Illuminate\Http\Request;

class ParentPaiementController extends Controller
{
    /**
     * Afficher les paiements pour un parent
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }
        
        // Récupérer les enfants du parent connecté
        $enfants = $parent->eleves()->with(['classe', 'fraisScolarite.tranchesPaiement', 'fraisScolarite.paiements'])->get();
        
        if ($enfants->isEmpty()) {
            return view('parent.paiements.index', compact('enfants'))
                ->with('message', 'Aucun enfant trouvé pour ce compte parent.');
        }

        // Récupérer tous les frais de scolarité des enfants
        $fraisScolarite = FraisScolarite::whereIn('eleve_id', $enfants->pluck('id'))
            ->with(['eleve', 'eleve.classe', 'tranchesPaiement', 'paiements'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Filtres
        if ($request->filled('eleve_id')) {
            $fraisScolarite = $fraisScolarite->where('eleve_id', $request->eleve_id);
        }

        if ($request->filled('type_frais')) {
            $fraisScolarite = $fraisScolarite->where('type_frais', $request->type_frais);
        }

        if ($request->filled('statut')) {
            $fraisScolarite = $fraisScolarite->where('statut', $request->statut);
        }

        // Statistiques
        $stats = [
            'total_frais' => $fraisScolarite->count(),
            'frais_payes' => $fraisScolarite->where('statut', 'paye')->count(),
            'frais_en_attente' => $fraisScolarite->where('statut', 'en_attente')->count(),
            'montant_total' => $fraisScolarite->sum('montant'),
            'montant_paye' => $fraisScolarite->sum(function($frais) {
                return $frais->paiements->sum('montant_paye');
            }),
            'montant_restant' => $fraisScolarite->sum('montant_restant')
        ];

        return view('parent.paiements.index', compact('enfants', 'fraisScolarite', 'stats'));
    }

    /**
     * Afficher les détails d'un frais de scolarité
     */
    public function show(FraisScolarite $frais)
    {
        // Vérifier que le parent a accès à ce frais
        $user = auth()->user();
        $parent = $user->parent;
        
        if (!$parent || !$parent->eleves()->where('id', $frais->eleve_id)->exists()) {
            abort(403, 'Accès non autorisé.');
        }

        $frais->load(['eleve', 'eleve.classe', 'tranchesPaiement', 'paiements.encaissePar']);

        return view('parent.paiements.show', compact('frais'));
    }

    /**
     * Afficher l'historique des paiements
     */
    public function historique(Request $request)
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }
        
        $enfants = $parent->eleves()->pluck('eleves.id');

        $query = Paiement::whereHas('fraisScolarite', function($q) use ($enfants) {
            $q->whereIn('eleve_id', $enfants);
        })->with(['fraisScolarite.eleve', 'fraisScolarite.eleve.classe', 'tranchePaiement', 'encaissePar']);

        // Filtres
        if ($request->filled('eleve_id')) {
            $query->whereHas('fraisScolarite', function($q) use ($request) {
                $q->where('eleve_id', $request->eleve_id);
            });
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        }

        $paiements = $query->orderBy('date_paiement', 'desc')->paginate(20);

        $enfantsList = $parent->eleves()->with('utilisateur')->get()->sortBy('utilisateur.nom');

        return view('parent.paiements.historique', compact('paiements', 'enfantsList'));
    }

    /**
     * Afficher les échéances à venir
     */
    public function echeances()
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }
        
        $enfants = $parent->eleves()->pluck('eleves.id');

        // Récupérer les tranches en attente
        $echeances = TranchePaiement::whereHas('fraisScolarite', function($q) use ($enfants) {
            $q->whereIn('eleve_id', $enfants);
        })
        ->where('statut', 'en_attente')
        ->where('date_echeance', '>=', now()->toDateString())
        ->with(['fraisScolarite.eleve', 'fraisScolarite.eleve.classe'])
        ->orderBy('date_echeance')
        ->get();

        // Grouper par mois
        $echeancesParMois = $echeances->groupBy(function($echeance) {
            return $echeance->date_echeance->format('Y-m');
        });

        return view('parent.paiements.echeances', compact('echeances', 'echeancesParMois'));
    }

    /**
     * Afficher le récapitulatif des paiements
     */
    public function recapitulatif(Request $request)
    {
        $user = auth()->user();
        $parent = $user->parent;
        
        if (!$parent) {
            abort(403, 'Profil parent non trouvé');
        }
        
        $enfants = $parent->eleves()->with('classe')->get();

        $anneeScolaire = $request->get('annee_scolaire', now()->year . '-' . (now()->year + 1));

        $recapitulatif = [];

        foreach ($enfants as $enfant) {
            $frais = FraisScolarite::where('eleve_id', $enfant->id)
                ->whereHas('eleve.anneeScolaire', function($q) use ($anneeScolaire) {
                    $q->where('libelle', $anneeScolaire);
                })
                ->with(['paiements', 'tranchesPaiement'])
                ->get();

            $recapitulatif[$enfant->id] = [
                'enfant' => $enfant,
                'frais' => $frais,
                'total_frais' => $frais->sum('montant'),
                'total_paye' => $frais->sum(function($f) {
                    return $f->paiements->sum('montant_paye');
                }),
                'total_restant' => $frais->sum('montant_restant'),
                'taux_paiement' => $frais->sum('montant') > 0 ? 
                    round(($frais->sum(function($f) {
                        return $f->paiements->sum('montant_paye');
                    }) / $frais->sum('montant')) * 100, 1) : 0
            ];
        }

        return view('parent.paiements.recapitulatif', compact('recapitulatif', 'anneeScolaire'));
    }
}
