<?php

namespace App\Http\Controllers;

use App\Models\CarteScolaire;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CarteScolaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CarteScolaire::with(['eleve.utilisateur', 'emisePar', 'valideePar']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_carte')) {
            $query->where('type_carte', $request->type_carte);
        }

        if ($request->filled('eleve_id')) {
            $query->where('eleve_id', $request->eleve_id);
        }

        if ($request->filled('numero_carte')) {
            $query->where('numero_carte', 'like', '%' . $request->numero_carte . '%');
        }

        $cartes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Pour les filtres
        $eleves = Eleve::with('utilisateur')->where('actif', true)->get();
        
        return view('cartes-scolaires.index', compact('cartes', 'eleves'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Récupérer tous les élèves actifs (pas seulement ceux sans carte)
        // pour permettre la création même si une carte existe déjà (remplacement)
        $eleves = Eleve::with(['utilisateur', 'classe'])
            ->where('actif', true)
            ->get();

        // Si un eleve_id est passé, pré-sélectionner cet élève
        $selectedEleveId = $request->get('eleve_id');

        return view('cartes-scolaires.create', compact('eleves', 'selectedEleveId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'type_carte' => 'required|in:standard,temporaire,remplacement',
            'date_emission' => 'required|date',
            'date_expiration' => 'required|date|after:date_emission',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request) {
                $eleve = Eleve::with('utilisateur')->findOrFail($request->eleve_id);
                
                // Générer le numéro de carte
                $numeroCarte = CarteScolaire::genererNumeroCarte();
                
                // Générer le QR code (temporairement désactivé)
                $qrCodeData = [
                    'numero_carte' => $numeroCarte,
                    'eleve_nom' => $eleve->utilisateur->nom,
                    'eleve_prenom' => $eleve->utilisateur->prenom,
                    'classe' => $eleve->classe->nom ?? 'Non assigné',
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration
                ];
                
                // QR Code généré via API en ligne (optimisé pour carte 86x54mm)
                $qrCodeDataString = json_encode($qrCodeData);
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrCodeDataString);
                $qrCode = '<img src="' . $qrCodeUrl . '" alt="QR Code" style="width: 100%; height: 100%;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                $qrCode .= '<div style="width: 100%; height: 100%; border: 1px solid #d4af37; display: none; align-items: center; justify-content: center; background: #f8f9fa; text-align: center; padding: 2px; font-size: 8px; border-radius: 2px;">QR<br/>Code<br/><small>' . substr($numeroCarte, -4) . '</small></div>';

                // Créer la carte scolaire
                $carte = CarteScolaire::create([
                    'eleve_id' => $request->eleve_id,
                    'numero_carte' => $numeroCarte,
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => $request->type_carte,
                    'qr_code' => $qrCode,
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);

                // Si c'est une carte standard, désactiver les autres cartes de l'élève
                if ($request->type_carte === 'standard') {
                    CarteScolaire::where('eleve_id', $request->eleve_id)
                        ->where('id', '!=', $carte->id)
                        ->where('statut', 'active')
                        ->update(['statut' => 'annulee']);
                }
            });

            return redirect()->route('cartes-scolaires.index')
                ->with('success', 'Carte scolaire créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la carte : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CarteScolaire $cartes_scolaire)
    {
        $cartes_scolaire->load(['eleve.utilisateur', 'eleve.classe', 'emisePar', 'valideePar']);
        
        return view('cartes-scolaires.show', compact('cartes_scolaire'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CarteScolaire $cartes_scolaire)
    {
        $cartes_scolaire->load(['eleve.utilisateur']);
        
        return view('cartes-scolaires.edit', compact('cartes_scolaire'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarteScolaire $cartes_scolaire)
    {
        $request->validate([
            'statut' => 'required|in:active,expiree,suspendue,annulee',
            'date_expiration' => 'required|date|after:date_emission',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            $cartes_scolaire->update([
                'statut' => $request->statut,
                'date_expiration' => $request->date_expiration,
                'observations' => $request->observations,
                'validee_par' => auth()->id()
            ]);

            return redirect()->route('cartes-scolaires.show', $cartes_scolaire)
                ->with('success', 'Carte scolaire mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarteScolaire $cartes_scolaire)
    {
        try {
            $cartes_scolaire->delete();
            
            return redirect()->route('cartes-scolaires.index')
                ->with('success', 'Carte scolaire supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Imprimer une carte scolaire
     */
    public function imprimer(CarteScolaire $cartes_scolaire)
    {
        $cartes_scolaire->load(['eleve.utilisateur', 'eleve.classe', 'emisePar']);
        
        return view('cartes-scolaires.imprimer', compact('cartes_scolaire'));
    }

    /**
     * Imprimer plusieurs cartes scolaires (8 par page A4)
     */
    public function imprimerPlusieurs(Request $request)
    {
        // Récupérer les IDs depuis les paramètres GET ou POST
        $carteIds = $request->input('cartes', []);
        
        // Si c'est une chaîne séparée par des virgules, la convertir en tableau
        if (is_string($carteIds)) {
            $carteIds = explode(',', $carteIds);
            $carteIds = array_filter($carteIds, function($id) {
                return !empty(trim($id));
            });
        }
        
        if (empty($carteIds)) {
            return redirect()->back()->with('error', 'Veuillez sélectionner au moins une carte.');
        }

        // Charger les cartes avec leurs relations
        $cartes = CarteScolaire::whereIn('id', $carteIds)
            ->with(['eleve.utilisateur', 'eleve.classe', 'emisePar'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Diviser en pages de 8 cartes
        $cartesParPage = $cartes->chunk(8);

        return view('cartes-scolaires.imprimer-plusieurs', compact('cartesParPage', 'cartes'));
    }


    /**
     * Renouveler une carte scolaire
     */
    public function renouveler(CarteScolaire $cartes_scolaire)
    {
        $cartes_scolaire->load(['eleve.utilisateur']);
        
        return view('cartes-scolaires.renouveler', compact('cartes_scolaire'));
    }

    /**
     * Traiter le renouvellement d'une carte
     */
    public function traiterRenouvellement(Request $request, CarteScolaire $cartes_scolaire)
    {
        $request->validate([
            'date_expiration' => 'required|date|after:today',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request, $cartes_scolaire) {
                // Désactiver l'ancienne carte
                $cartes_scolaire->update([
                    'statut' => 'annulee',
                    'observations' => $cartes_scolaire->observations . "\nRenouvelée le " . now()->format('d/m/Y')
                ]);

                // Créer une nouvelle carte
                $nouvelleCarte = CarteScolaire::create([
                    'eleve_id' => $cartes_scolaire->eleve_id,
                    'numero_carte' => CarteScolaire::genererNumeroCarte(),
                    'date_emission' => now()->toDateString(),
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => 'remplacement',
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);
            });

            return redirect()->route('cartes-scolaires.index')
                ->with('success', 'Carte scolaire renouvelée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du renouvellement : ' . $e->getMessage());
        }
    }
}
