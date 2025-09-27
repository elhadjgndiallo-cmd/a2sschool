<?php

namespace App\Http\Controllers;

use App\Models\CarteEnseignant;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarteEnseignantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CarteEnseignant::with(['enseignant.utilisateur', 'emisePar', 'valideePar']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_carte')) {
            $query->where('type_carte', $request->type_carte);
        }

        if ($request->filled('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }

        if ($request->filled('numero_carte')) {
            $query->where('numero_carte', 'like', '%' . $request->numero_carte . '%');
        }

        $cartes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Pour les filtres
        $enseignants = Enseignant::with('utilisateur')->where('actif', true)->get();
        
        return view('cartes-enseignants.index', compact('cartes', 'enseignants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $enseignants = Enseignant::with('utilisateur')
            ->where('actif', true)
            ->whereDoesntHave('cartesEnseignants', function($query) {
                $query->where('statut', 'active');
            })
            ->get();

        return view('cartes-enseignants.create', compact('enseignants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|exists:enseignants,id',
            'type_carte' => 'required|in:standard,temporaire,remplacement',
            'date_emission' => 'required|date',
            'date_expiration' => 'required|date|after:date_emission',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request) {
                $enseignant = Enseignant::with('utilisateur')->findOrFail($request->enseignant_id);
                
                // Générer le numéro de carte
                $numeroCarte = CarteEnseignant::genererNumeroCarte();
                
                // Générer le QR code
                $qrCodeData = [
                    'numero_carte' => $numeroCarte,
                    'enseignant_nom' => $enseignant->utilisateur->nom,
                    'enseignant_prenom' => $enseignant->utilisateur->prenom,
                    'matiere' => $enseignant->matiere_principale ?? 'Non assigné',
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration
                ];
                
                // QR Code généré via API en ligne
                $qrCodeDataString = json_encode($qrCodeData);
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrCodeDataString);
                $qrCode = '<img src="' . $qrCodeUrl . '" alt="QR Code" style="width: 100%; height: 100%;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                $qrCode .= '<div style="width: 100%; height: 100%; border: 1px solid #d4af37; display: none; align-items: center; justify-content: center; background: #f8f9fa; text-align: center; padding: 2px; font-size: 8px; border-radius: 2px;">QR<br/>Code<br/><small>' . substr($numeroCarte, -4) . '</small></div>';

                // Créer la carte enseignant
                $carte = CarteEnseignant::create([
                    'enseignant_id' => $request->enseignant_id,
                    'numero_carte' => $numeroCarte,
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => $request->type_carte,
                    'qr_code' => $qrCode,
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);

                // Si c'est une carte standard, désactiver les autres cartes de l'enseignant
                if ($request->type_carte === 'standard') {
                    CarteEnseignant::where('enseignant_id', $request->enseignant_id)
                        ->where('id', '!=', $carte->id)
                        ->where('statut', 'active')
                        ->update(['statut' => 'annulee']);
                }
            });

            return redirect()->route('cartes-enseignants.index')
                ->with('success', 'Carte enseignant créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la carte : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CarteEnseignant $cartes_enseignant)
    {
        $cartes_enseignant->load(['enseignant.utilisateur', 'emisePar', 'valideePar']);
        
        return view('cartes-enseignants.show', compact('cartes_enseignant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CarteEnseignant $cartes_enseignant)
    {
        $cartes_enseignant->load(['enseignant.utilisateur']);
        
        return view('cartes-enseignants.edit', compact('cartes_enseignant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarteEnseignant $cartes_enseignant)
    {
        $request->validate([
            'type_carte' => 'required|in:standard,temporaire,remplacement',
            'date_emission' => 'required|date',
            'date_expiration' => 'required|date|after:date_emission',
            'statut' => 'required|in:active,expiree,suspendue,annulee',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            $cartes_enseignant->update($request->only([
                'type_carte', 'date_emission', 'date_expiration', 
                'statut', 'observations'
            ]));

            return redirect()->route('cartes-enseignants.index')
                ->with('success', 'Carte enseignant mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CarteEnseignant $cartes_enseignant)
    {
        try {
            $cartes_enseignant->delete();
            
            return redirect()->route('cartes-enseignants.index')
                ->with('success', 'Carte enseignant supprimée avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Imprimer une carte enseignant
     */
    public function imprimer(CarteEnseignant $cartes_enseignant)
    {
        $cartes_enseignant->load(['enseignant.utilisateur', 'emisePar']);
        
        return view('cartes-enseignants.imprimer', compact('cartes_enseignant'));
    }

    /**
     * Renouveler une carte enseignant
     */
    public function renouveler(CarteEnseignant $cartes_enseignant)
    {
        $cartes_enseignant->load(['enseignant.utilisateur']);
        
        return view('cartes-enseignants.renouveler', compact('cartes_enseignant'));
    }

    /**
     * Traiter le renouvellement d'une carte
     */
    public function traiterRenouvellement(Request $request, CarteEnseignant $cartes_enseignant)
    {
        $request->validate([
            'date_expiration' => 'required|date|after:today',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request, $cartes_enseignant) {
                // Désactiver l'ancienne carte
                $cartes_enseignant->update(['statut' => 'annulee']);

                // Créer une nouvelle carte
                $nouvelleCarte = CarteEnseignant::create([
                    'enseignant_id' => $cartes_enseignant->enseignant_id,
                    'numero_carte' => CarteEnseignant::genererNumeroCarte(),
                    'date_emission' => now()->toDateString(),
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => 'standard',
                    'qr_code' => $cartes_enseignant->qr_code, // Réutiliser le QR code
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);
            });

            return redirect()->route('cartes-enseignants.index')
                ->with('success', 'Carte enseignant renouvelée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du renouvellement : ' . $e->getMessage());
        }
    }
}