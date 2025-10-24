<?php

namespace App\Http\Controllers;

use App\Models\TarifClasse;
use App\Models\Classe;
use Illuminate\Http\Request;

class TarifClasseController extends Controller
{
    /**
     * Afficher la liste des tarifs
     */
    public function index(Request $request)
    {
       $query = TarifClasse::with('classe');

        // Filtres
        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        if ($request->filled('annee_scolaire')) {
            $query->where('annee_scolaire', $request->annee_scolaire);
        }

        if ($request->filled('actif')) {
            $query->where('actif', $request->actif);
        }

        $tarifs = $query->orderBy('classe_id')->orderBy('annee_scolaire', 'desc')->paginate(20);
        $classes = Classe::orderBy('nom')->get();
        
        // Années scolaires disponibles
        $anneesScolaires = TarifClasse::select('annee_scolaire')
            ->distinct()
            ->orderBy('annee_scolaire', 'desc')
            ->pluck('annee_scolaire');

        return view('tarifs.index', compact('tarifs', 'classes', 'anneesScolaires'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $classes = Classe::orderBy('nom')->get();
        return view('tarifs.create', compact('classes'));
    }

    /**
     * Enregistrer un nouveau tarif
     */
    public function store(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'annee_scolaire' => 'required|string|max:20',
            'frais_inscription' => 'nullable|numeric|min:0',
            'frais_reinscription' => 'nullable|numeric|min:0',
            'frais_scolarite_mensuel' => 'nullable|numeric|min:0',
            'frais_cantine_mensuel' => 'nullable|numeric|min:0',
            'frais_transport_mensuel' => 'nullable|numeric|min:0',
            'frais_uniforme' => 'nullable|numeric|min:0',
            'frais_livres' => 'nullable|numeric|min:0',
            'frais_autres' => 'nullable|numeric|min:0',
            'paiement_par_tranches' => 'boolean',
            'nombre_tranches' => 'required|integer|min:1|max:9',
            'periode_tranche' => 'required|in:mensuel,trimestriel,semestriel,annuel',
            'actif' => 'boolean',
            'description' => 'nullable|string'
        ]);

        // Vérifier qu'il n'y a pas déjà un tarif pour cette classe et année
        $existingTarif = TarifClasse::where('classe_id', $request->classe_id)
            ->where('annee_scolaire', $request->annee_scolaire)
            ->first();

        if ($existingTarif) {
            return back()->withInput()->with('error', 'Un tarif existe déjà pour cette classe et cette année scolaire.');
        }

        // Préparer les données en gérant les valeurs nulles
        $data = $request->all();
        
        // Convertir les chaînes vides en null pour les champs numériques
        $numericFields = [
            'frais_inscription', 'frais_reinscription', 'frais_scolarite_mensuel',
            'frais_cantine_mensuel', 'frais_transport_mensuel', 'frais_uniforme',
            'frais_livres', 'frais_autres'
        ];
        
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        $tarif = TarifClasse::create($data);

        return redirect()->route('tarifs.show', $tarif)
            ->with('success', 'Tarif créé avec succès.');
    }

    /**
     * Afficher les détails d'un tarif
     */
    public function show(TarifClasse $tarif)
    {
        $tarif->load('classe');
        return view('tarifs.show', compact('tarif'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(TarifClasse $tarif)
    {
        $classes = Classe::orderBy('nom')->get();
        return view('tarifs.edit', compact('tarif', 'classes'));
    }

    /**
     * Mettre à jour un tarif
     */
    public function update(Request $request, TarifClasse $tarif)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'annee_scolaire' => 'required|string|max:20',
            'frais_inscription' => 'nullable|numeric|min:0',
            'frais_reinscription' => 'nullable|numeric|min:0',
            'frais_scolarite_mensuel' => 'nullable|numeric|min:0',
            'frais_cantine_mensuel' => 'nullable|numeric|min:0',
            'frais_transport_mensuel' => 'nullable|numeric|min:0',
            'frais_uniforme' => 'nullable|numeric|min:0',
            'frais_livres' => 'nullable|numeric|min:0',
            'frais_autres' => 'nullable|numeric|min:0',
            'paiement_par_tranches' => 'boolean',
            'nombre_tranches' => 'required|integer|min:1|max:9',
            'periode_tranche' => 'required|in:mensuel,trimestriel,semestriel,annuel',
            'actif' => 'boolean',
            'description' => 'nullable|string'
        ]);

        $tarif->update($request->all());

        return redirect()->route('tarifs.show', $tarif)
            ->with('success', 'Tarif mis à jour avec succès.');
    }

    /**
     * Supprimer un tarif
     */
    public function destroy(TarifClasse $tarif)
    {
        $tarif->delete();

        return redirect()->route('tarifs.index')
            ->with('success', 'Tarif supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un tarif
     */
    public function toggleStatus(TarifClasse $tarif)
    {
        $tarif->update(['actif' => !$tarif->actif]);

        $status = $tarif->actif ? 'activé' : 'désactivé';
        return back()->with('success', "Tarif {$status} avec succès.");
    }

    /**
     * Dupliquer un tarif pour une nouvelle année
     */
    public function duplicate(TarifClasse $tarif, Request $request)
    {
        $request->validate([
            'nouvelle_annee' => 'required|string|max:20'
        ]);

        // Vérifier qu'il n'y a pas déjà un tarif pour la nouvelle année
        $existingTarif = TarifClasse::where('classe_id', $tarif->classe_id)
            ->where('annee_scolaire', $request->nouvelle_annee)
            ->first();

        if ($existingTarif) {
            return back()->with('error', 'Un tarif existe déjà pour cette classe et cette année scolaire.');
        }

        $nouveauTarif = $tarif->replicate();
        $nouveauTarif->annee_scolaire = $request->nouvelle_annee;
        $nouveauTarif->actif = false; // Nouveau tarif inactif par défaut
        $nouveauTarif->save();

        return redirect()->route('tarifs.show', $nouveauTarif)
            ->with('success', 'Tarif dupliqué avec succès pour l\'année ' . $request->nouvelle_annee);
    }

    /**
     * Afficher le tableau des tarifs
     */
    public function tableau(Request $request)
    {
        $anneeScolaire = $request->get('annee_scolaire', now()->year . '-' . (now()->year + 1));
        
        $tarifs = TarifClasse::with('classe')
            ->where('annee_scolaire', $anneeScolaire)
            ->orderBy('classe_id')
            ->get();

        $classes = Classe::orderBy('nom')->get();
        
        $anneesScolaires = TarifClasse::select('annee_scolaire')
            ->distinct()
            ->orderBy('annee_scolaire', 'desc')
            ->pluck('annee_scolaire');

        return view('tarifs.tableau', compact('tarifs', 'classes', 'anneesScolaires', 'anneeScolaire'));
    }

    /**
     * Récupérer les tarifs d'une classe pour l'auto-complétion
     */
    public function getTarifsByClasse($classeId)
    {
        $tarifs = TarifClasse::where('classe_id', $classeId)
            ->orderBy('annee_scolaire', 'desc')
            ->get([
                'id',
                'annee_scolaire',
                'frais_inscription',
                'frais_reinscription',
                'frais_scolarite_mensuel',
                'frais_cantine_mensuel',
                'frais_transport_mensuel',
                'frais_uniforme',
                'frais_livres',
                'frais_autres',
                'nombre_tranches',
                'periode_tranche'
            ]);

        return response()->json($tarifs);
    }
}
