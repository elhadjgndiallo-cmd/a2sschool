<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\BonSalaireEnseignant;
use App\Models\SalaireEnseignant;
use App\Models\Enseignant;
use App\Models\Depense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Barryvdh\DomPDF\Facade\Pdf;

class SalaireEnseignantController extends Controller
{
    /**
     * Afficher la liste des salaires
     */
    public function index(Request $request)
    {
        $anneeScolaire = AnneeScolaire::anneeActive();

        $query = SalaireEnseignant::with(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);

        if ($anneeScolaire) {
            $query->whereHas('enseignant', fn ($q) => $q->where('annee_scolaire_id', $anneeScolaire->id));
        }

        // Filtres
        if ($request->filled('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('periode_debut') && $request->filled('periode_fin')) {
            $query->whereBetween('periode_debut', [$request->periode_debut, $request->periode_fin]);
        }

        $salaires = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(20);
        $enseignants = Enseignant::listeDeroulante($anneeScolaire?->id);

        return view('salaires.index', compact('salaires', 'enseignants', 'anneeScolaire'));
    }

    /**
     * Afficher le formulaire de création de salaire
     */
    public function create()
    {
        $enseignants = Enseignant::listeDeroulante();
        return view('salaires.create', compact('enseignants'));
    }

    /**
     * Enregistrer un nouveau salaire
     */
    public function store(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after:periode_debut',
            'nombre_heures' => 'nullable|integer|min:0',
            'taux_horaire' => 'nullable|numeric|min:0',
            'salaire_base' => 'nullable|numeric|min:0',
            'prime_anciennete' => 'nullable|numeric|min:0',
            'prime_performance' => 'nullable|numeric|min:0',
            'prime_heures_supplementaires' => 'nullable|numeric|min:0',
            'deduction_absences' => 'nullable|numeric|min:0',
            'deduction_autres' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string'
        ]);

        // Vérifier qu'il n'y a pas déjà un salaire pour cette période
        $existingSalaire = SalaireEnseignant::where('enseignant_id', $request->enseignant_id)
            ->where('periode_debut', $request->periode_debut)
            ->where('periode_fin', $request->periode_fin)
            ->first();

        if ($existingSalaire) {
            return back()->withInput()->with('error', 'Un salaire existe déjà pour cette période.');
        }

        $salaire = SalaireEnseignant::create($request->all());
        $salaire->calculerSalaires();

        return redirect()->route('salaires.show', $salaire)
            ->with('success', 'Salaire créé et calculé avec succès.');
    }

    /**
     * Afficher les détails d'un salaire
     */
    public function show(SalaireEnseignant $salaire)
    {
        try {
            $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar', 'bonsAvance']);
            
            // Vérifier que l'enseignant a un utilisateur associé
            if (!$salaire->enseignant || !$salaire->enseignant->utilisateur) {
                return redirect()->route('salaires.index')
                    ->with('error', 'Aucun utilisateur associé à cet enseignant.');
            }
            
            return view('salaires.show', compact('salaire'));
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'affichage du salaire:', [
                'salaire_id' => $salaire->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('salaires.index')
                ->with('error', 'Erreur lors du chargement des détails du salaire.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(SalaireEnseignant $salaire)
    {
        $enseignants = Enseignant::listeDeroulante();
        return view('salaires.edit', compact('salaire', 'enseignants'));
    }

    /**
     * Mettre à jour un salaire
     */
    public function update(Request $request, SalaireEnseignant $salaire)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after:periode_debut',
            'nombre_heures' => 'nullable|integer|min:0',
            'taux_horaire' => 'nullable|numeric|min:0',
            'salaire_base' => 'nullable|numeric|min:0',
            'prime_anciennete' => 'nullable|numeric|min:0',
            'prime_performance' => 'nullable|numeric|min:0',
            'prime_heures_supplementaires' => 'nullable|numeric|min:0',
            'deduction_absences' => 'nullable|numeric|min:0',
            'deduction_autres' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string'
        ]);

        $salaire->update($request->all());
        $salaire->calculerSalaires();

        return redirect()->route('salaires.show', $salaire)
            ->with('success', 'Salaire mis à jour avec succès.');
    }

    /**
     * Valider un salaire
     */
    public function valider(SalaireEnseignant $salaire)
    {
        $salaire->valider();

        return redirect()->route('salaires.show', $salaire)
            ->with('success', 'Salaire validé avec succès.');
    }

    /**
     * Afficher le formulaire de paiement
     */
    public function payerForm(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur']);
        return view('salaires.payer', compact('salaire'));
    }

    /**
     * Payer un salaire
     */
    public function payer(Request $request, SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur']);
        
        $request->validate([
            'mode_paiement' => 'required|in:especes,cheque,virement,carte',
            'reference_paiement' => 'nullable|string|max:255',
            'date_paiement' => 'required|date'
        ]);

        DB::beginTransaction();
        try {
            // Marquer le salaire comme payé avec la date du formulaire
            $salaire->marquerCommePaye($request->date_paiement);

            // Créer une dépense correspondante avec la même date de paiement
            $depensePayload = [
                'libelle' => 'Salaire ' . $salaire->enseignant->utilisateur->nom . ' ' . $salaire->enseignant->utilisateur->prenom . ' - ' . $salaire->periode_formatee,
                'montant' => $salaire->salaire_net,
                'date_depense' => $request->date_paiement,
                'type_depense' => 'salaire_enseignant',
                'description' => 'Paiement de salaire pour la période ' . $salaire->periode_formatee,
                'beneficiaire' => $salaire->enseignant->utilisateur->nom . ' ' . $salaire->enseignant->utilisateur->prenom,
                'statut' => 'paye',
                'mode_paiement' => $request->mode_paiement,
                'reference_paiement' => $request->reference_paiement,
                'paye_par' => auth()->id(),
                'date_paiement' => $request->date_paiement,
                'observations' => 'Paiement automatique depuis le système de salaires',
                'annee_scolaire_id' => \App\Models\AnneeScolaire::anneeActive()?->id,
            ];

            if (Depense::hasSalaireEnseignantLinkColumn()) {
                $depensePayload['salaire_enseignant_id'] = $salaire->id;
            }

            Depense::create($depensePayload);

            DB::commit();
            return redirect()->route('salaires.show', $salaire)
                ->with('success', 'Salaire payé et dépense créée avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un salaire
     */
    public function destroy(SalaireEnseignant $salaire)
    {
        DB::beginTransaction();
        try {
            // Si le salaire est payé, supprimer aussi la dépense associée
            if ($salaire->statut === 'payé') {
                $salaire->load(['enseignant.utilisateur']);
                
                // Trouver la dépense associée au salaire
                $depense = Depense::hasSalaireEnseignantLinkColumn()
                    ? Depense::where('salaire_enseignant_id', $salaire->id)->first()
                    : null;

                $depense = $depense ?? Depense::where('type_depense', 'salaire_enseignant')
                        ->where('montant', $salaire->salaire_net)
                        ->where('date_depense', $salaire->date_paiement)
                        ->where('beneficiaire', $salaire->enseignant->utilisateur->nom . ' ' . $salaire->enseignant->utilisateur->prenom)
                        ->where('observations', 'like', '%Paiement automatique depuis le système de salaires%')
                        ->first();
                
                if ($depense) {
                    $depense->delete();
                }
                
                // Remettre le salaire au statut validé (ou calculé si pas validé)
                $nouveauStatut = $salaire->date_validation ? 'validé' : 'calculé';
                $salaire->update([
                    'statut' => $nouveauStatut,
                    'date_paiement' => null,
                    'paye_par' => null
                ]);
            }
            
            // Supprimer le salaire
            $salaire->libererAvancesLiees();
            $salaire->delete();

            DB::commit();
            
            // Rediriger vers la page d'origine si c'est depuis sorties, sinon vers l'index
            $redirectRoute = request()->headers->get('referer') && str_contains(request()->headers->get('referer'), 'sorties') 
                ? route('comptabilite.sorties')
                : route('salaires.index');
            
            return redirect($redirectRoute)
                ->with('success', 'Paiement de salaire supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les rapports de salaires
     */
    public function rapports(Request $request)
    {
        $anneeScolaire = AnneeScolaire::anneeActive();
        $anneesScolaires = AnneeScolaire::orderByDesc('date_debut')->get();

        $mode = $request->get('mode', 'mois');
        $mois = $request->get('mois', now()->format('Y-m'));
        $anneeScolaireId = $request->get('annee_scolaire_id', $anneeScolaire?->id);

        if ($mode === 'annee' && $anneeScolaireId) {
            $annee = AnneeScolaire::find($anneeScolaireId);
            $dateDebut = $annee?->date_debut?->format('Y-m-d') ?? now()->startOfYear()->format('Y-m-d');
            $dateFin = $annee?->date_fin?->format('Y-m-d') ?? now()->endOfYear()->format('Y-m-d');
            $periodeLibelle = $annee?->nom ?? 'Année scolaire';
        } else {
            $carbonMois = \Carbon\Carbon::createFromFormat('Y-m', $mois)->startOfMonth();
            $dateDebut = $carbonMois->copy()->startOfMonth()->format('Y-m-d');
            $dateFin = $carbonMois->copy()->endOfMonth()->format('Y-m-d');
            $periodeLibelle = $carbonMois->translatedFormat('F Y');
            $mode = 'mois';
        }

        $baseQuery = SalaireEnseignant::query()
            ->whereBetween('periode_debut', [$dateDebut, $dateFin]);

        if ($anneeScolaireId) {
            $baseQuery->whereHas('enseignant', fn ($q) => $q->where('annee_scolaire_id', $anneeScolaireId));
        }

        $stats = [
            'total_salaires' => (clone $baseQuery)->count(),
            'salaires_payes' => (clone $baseQuery)->payes()->count(),
            'salaires_valides' => (clone $baseQuery)->valides()->count(),
            'salaires_calcules' => (clone $baseQuery)->calcules()->count(),
            'montant_total_brut' => (clone $baseQuery)->sum('salaire_brut'),
            'montant_total_net' => (clone $baseQuery)->sum('salaire_net'),
            'montant_total_avances' => (clone $baseQuery)->sum('deduction_avances'),
        ];

        $salairesParEnseignant = (clone $baseQuery)
            ->with('enseignant.utilisateur')
            ->selectRaw('enseignant_id, COUNT(*) as count, SUM(salaire_net) as total_net, SUM(salaire_brut) as total_brut, SUM(deduction_avances) as total_avances')
            ->groupBy('enseignant_id')
            ->get();

        $salairesListe = (clone $baseQuery)
            ->with(['enseignant.utilisateur'])
            ->orderBy('periode_debut')
            ->orderBy('id')
            ->get();

        $statsAvances = [
            'total_bons' => BonSalaireEnseignant::query()
                ->when($anneeScolaireId, fn ($q) => $q->where('annee_scolaire_id', $anneeScolaireId))
                ->whereBetween('date_bon', [$dateDebut, $dateFin])
                ->whereIn('statut', ['actif', 'deduit'])
                ->count(),
            'montant_bons' => BonSalaireEnseignant::query()
                ->when($anneeScolaireId, fn ($q) => $q->where('annee_scolaire_id', $anneeScolaireId))
                ->whereBetween('date_bon', [$dateDebut, $dateFin])
                ->whereIn('statut', ['actif', 'deduit'])
                ->sum('montant'),
        ];

        return view('salaires.rapports', compact(
            'stats',
            'statsAvances',
            'salairesParEnseignant',
            'salairesListe',
            'dateDebut',
            'dateFin',
            'periodeLibelle',
            'mode',
            'mois',
            'anneeScolaireId',
            'anneesScolaires',
            'anneeScolaire'
        ));
    }

    /**
     * Générer le bulletin de salaire PDF
     */
    public function genererBulletinSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar', 'bonsAvance']);
        
        $etablissement = \App\Models\Etablissement::first();
        
        return view('salaires.bulletin-salaire-pdf', compact('salaire', 'etablissement'));
    }

    /**
     * Afficher le bulletin de salaire dans le navigateur
     */
    public function afficherBulletinSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar', 'bonsAvance']);
        
        $etablissement = \App\Models\Etablissement::first();
        
        return view('salaires.bulletin-salaire-pdf', compact('salaire', 'etablissement'));
    }
}
