<?php

namespace App\Http\Controllers;

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
        $query = SalaireEnseignant::with(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);

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

        $salaires = $query->orderBy('periode_debut', 'desc')->paginate(20);
        $enseignants = Enseignant::with('utilisateur')
            ->where('actif', true)
            ->get()
            ->sortBy(function($enseignant) {
                return $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
            });

        return view('salaires.index', compact('salaires', 'enseignants'));
    }

    /**
     * Afficher le formulaire de création de salaire
     */
    public function create()
    {
        $enseignants = Enseignant::with('utilisateur')
            ->where('actif', true)
            ->get()
            ->sortBy(function($enseignant) {
                return $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
            });
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
            'nombre_heures' => 'required|integer|min:0',
            'taux_horaire' => 'required|numeric|min:0',
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
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);
        return view('salaires.show', compact('salaire'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(SalaireEnseignant $salaire)
    {
        $enseignants = Enseignant::with('utilisateur')
            ->where('actif', true)
            ->get()
            ->sortBy(function($enseignant) {
                return $enseignant->utilisateur->nom . ' ' . $enseignant->utilisateur->prenom;
            });
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
            'nombre_heures' => 'required|integer|min:0',
            'taux_horaire' => 'required|numeric|min:0',
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
            // Marquer le salaire comme payé
            $salaire->marquerCommePaye();

            // Créer une dépense correspondante
            Depense::create([
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
                'observations' => 'Paiement automatique depuis le système de salaires'
            ]);

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
        if ($salaire->statut === 'payé') {
            return back()->with('error', 'Impossible de supprimer un salaire déjà payé.');
        }

        $salaire->delete();

        return redirect()->route('salaires.index')
            ->with('success', 'Salaire supprimé avec succès.');
    }

    /**
     * Calculer automatiquement les salaires pour une période
     */
    public function calculerSalairesPeriode(Request $request)
    {
        $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after:periode_debut',
            'taux_horaire_defaut' => 'required|numeric|min:0',
            'salaire_base_defaut' => 'required|numeric|min:0'
        ]);

        $enseignants = Enseignant::all();
        $salairesCrees = 0;

        foreach ($enseignants as $enseignant) {
            // Vérifier qu'il n'y a pas déjà un salaire pour cette période
            $existingSalaire = SalaireEnseignant::where('enseignant_id', $enseignant->id)
                ->where('periode_debut', $request->periode_debut)
                ->where('periode_fin', $request->periode_fin)
                ->first();

            if (!$existingSalaire) {
                // Calculer le nombre d'heures (exemple: 20h par semaine × 4 semaines)
                $nombreHeures = 80; // À adapter selon vos besoins

                $salaire = SalaireEnseignant::create([
                    'enseignant_id' => $enseignant->id,
                    'periode_debut' => $request->periode_debut,
                    'periode_fin' => $request->periode_fin,
                    'nombre_heures' => $nombreHeures,
                    'taux_horaire' => $request->taux_horaire_defaut,
                    'salaire_base' => $request->salaire_base_defaut,
                    'prime_anciennete' => 0,
                    'prime_performance' => 0,
                    'prime_heures_supplementaires' => 0,
                    'deduction_absences' => 0,
                    'deduction_autres' => 0
                ]);

                $salaire->calculerSalaires();
                $salairesCrees++;
            }
        }

        return redirect()->route('salaires.index')
            ->with('success', "Calcul automatique terminé. {$salairesCrees} salaires créés.");
    }

    /**
     * Afficher les rapports de salaires
     */
    public function rapports(Request $request)
    {
        $dateDebut = $request->get('date_debut', now()->startOfMonth());
        $dateFin = $request->get('date_fin', now()->endOfMonth());

        $stats = [
            'total_salaires' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->count(),
            'salaires_payes' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->payes()->count(),
            'salaires_valides' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->valides()->count(),
            'salaires_calcules' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->calcules()->count(),
            'montant_total_brut' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->sum('salaire_brut'),
            'montant_total_net' => SalaireEnseignant::parPeriode($dateDebut, $dateFin)->sum('salaire_net')
        ];

        // Salaires par enseignant
        $salairesParEnseignant = SalaireEnseignant::parPeriode($dateDebut, $dateFin)
            ->with('enseignant.utilisateur')
            ->selectRaw('enseignant_id, COUNT(*) as count, SUM(salaire_net) as total_net')
            ->groupBy('enseignant_id')
            ->get();

        return view('salaires.rapports', compact('stats', 'salairesParEnseignant', 'dateDebut', 'dateFin'));
    }

    /**
     * Générer le bon de salaire PDF
     */
    public function genererBonSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::first();
        
        // Pour l'instant, retourner la vue HTML (temporaire)
        return view('salaires.bon-salaire-pdf', compact('salaire', 'etablissement'));
    }

    /**
     * Afficher le bon de salaire dans le navigateur
     */
    public function afficherBonSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::first();
        
        // Pour l'instant, retourner la vue HTML (temporaire)
        return view('salaires.bon-salaire-pdf', compact('salaire', 'etablissement'));
    }

    /**
     * Générer le bulletin de salaire PDF
     */
    public function genererBulletinSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::first();
        
        // Pour l'instant, retourner la vue HTML (temporaire)
        return view('salaires.bulletin-salaire-pdf', compact('salaire', 'etablissement'));
    }

    /**
     * Afficher le bulletin de salaire dans le navigateur
     */
    public function afficherBulletinSalaire(SalaireEnseignant $salaire)
    {
        $salaire->load(['enseignant.utilisateur', 'calculePar', 'validePar', 'payePar']);
        
        // Récupérer les informations de l'établissement
        $etablissement = \App\Models\Etablissement::first();
        
        // Pour l'instant, retourner la vue HTML (temporaire)
        return view('salaires.bulletin-salaire-pdf', compact('salaire', 'etablissement'));
    }
}
