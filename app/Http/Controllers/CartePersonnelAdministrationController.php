<?php

namespace App\Http\Controllers;

use App\Models\CartePersonnelAdministration;
use App\Models\PersonnelAdministration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartePersonnelAdministrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CartePersonnelAdministration::with(['personnelAdministration.utilisateur', 'emisePar', 'valideePar']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_carte')) {
            $query->where('type_carte', $request->type_carte);
        }

        if ($request->filled('personnel_id')) {
            $query->where('personnel_administration_id', $request->personnel_id);
        }

        if ($request->filled('numero_carte')) {
            $query->where('numero_carte', 'like', '%' . $request->numero_carte . '%');
        }

        $cartes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Pour les filtres
        $personnel = PersonnelAdministration::with('utilisateur')->get();
        
        return view('cartes-personnel-administration.index', compact('cartes', 'personnel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $personnel = PersonnelAdministration::with('utilisateur')
            ->whereDoesntHave('cartesPersonnelAdministration', function($query) {
                $query->where('statut', 'active');
            })
            ->get();

        // Si un personnel_id est fourni dans la requête, pré-sélectionner
        $selectedPersonnelId = $request->get('personnel_administration_id');

        return view('cartes-personnel-administration.create', compact('personnel', 'selectedPersonnelId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'personnel_administration_id' => 'required|exists:personnel_administration,id',
            'type_carte' => 'required|in:standard,temporaire,remplacement',
            'date_emission' => 'required|date',
            'date_expiration' => 'required|date|after:date_emission',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request) {
                $personnel = PersonnelAdministration::with('utilisateur')->findOrFail($request->personnel_administration_id);
                
                // Générer le numéro de carte
                $numeroCarte = CartePersonnelAdministration::genererNumeroCarte();
                
                // Générer le QR code
                $qrCodeData = [
                    'numero_carte' => $numeroCarte,
                    'personnel_nom' => $personnel->utilisateur->nom,
                    'personnel_prenom' => $personnel->utilisateur->prenom,
                    'poste' => $personnel->poste ?? 'Non assigné',
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration
                ];
                
                // QR Code généré via API en ligne
                $qrCodeDataString = json_encode($qrCodeData);
                $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrCodeDataString);
                $qrCode = '<img src="' . $qrCodeUrl . '" alt="QR Code" style="width: 100%; height: 100%;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                $qrCode .= '<div style="width: 100%; height: 100%; border: 1px solid #d4af37; display: none; align-items: center; justify-content: center; background: #f8f9fa; text-align: center; padding: 2px; font-size: 8px; border-radius: 2px;">QR<br/>Code<br/><small>' . substr($numeroCarte, -4) . '</small></div>';

                // Créer la carte
                $carte = CartePersonnelAdministration::create([
                    'personnel_administration_id' => $request->personnel_administration_id,
                    'numero_carte' => $numeroCarte,
                    'date_emission' => $request->date_emission,
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => $request->type_carte,
                    'qr_code' => $qrCode,
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);

                // Si c'est une carte standard, désactiver les autres cartes du personnel
                if ($request->type_carte === 'standard') {
                    CartePersonnelAdministration::where('personnel_administration_id', $request->personnel_administration_id)
                        ->where('id', '!=', $carte->id)
                        ->where('statut', 'active')
                        ->update(['statut' => 'annulee']);
                }
            });

            return redirect()->route('cartes-personnel-administration.index')
                ->with('success', 'Carte personnel d\'administration créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la carte : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CartePersonnelAdministration $cartes_personnel_administration)
    {
        $cartes_personnel_administration->load(['personnelAdministration.utilisateur', 'emisePar', 'valideePar']);
        
        return view('cartes-personnel-administration.show', compact('cartes_personnel_administration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CartePersonnelAdministration $cartes_personnel_administration)
    {
        $cartes_personnel_administration->load(['personnelAdministration.utilisateur']);
        
        return view('cartes-personnel-administration.edit', compact('cartes_personnel_administration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CartePersonnelAdministration $cartes_personnel_administration)
    {
        $request->validate([
            'type_carte' => 'required|in:standard,temporaire,remplacement',
            'date_emission' => 'required|date',
            'date_expiration' => 'required|date|after:date_emission',
            'statut' => 'required|in:active,expiree,suspendue,annulee',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            $cartes_personnel_administration->update($request->only([
                'type_carte', 'date_emission', 'date_expiration', 
                'statut', 'observations'
            ]));

            return redirect()->route('cartes-personnel-administration.index')
                ->with('success', 'Carte personnel d\'administration mise à jour avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartePersonnelAdministration $cartes_personnel_administration)
    {
        try {
            $cartes_personnel_administration->delete();
            
            return redirect()->route('cartes-personnel-administration.index')
                ->with('success', 'Carte personnel d\'administration supprimée avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Imprimer une carte personnel d'administration
     */
    public function imprimer(CartePersonnelAdministration $cartes_personnel_administration)
    {
        $cartes_personnel_administration->load(['personnelAdministration.utilisateur', 'emisePar']);
        
        return view('cartes-personnel-administration.imprimer', compact('cartes_personnel_administration'));
    }

    /**
     * Renouveler une carte personnel d'administration
     */
    public function renouveler(CartePersonnelAdministration $cartes_personnel_administration)
    {
        $cartes_personnel_administration->load(['personnelAdministration.utilisateur']);
        
        return view('cartes-personnel-administration.renouveler', compact('cartes_personnel_administration'));
    }

    /**
     * Traiter le renouvellement d'une carte
     */
    public function traiterRenouvellement(Request $request, CartePersonnelAdministration $cartes_personnel_administration)
    {
        $request->validate([
            'date_expiration' => 'required|date|after:today',
            'observations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request, $cartes_personnel_administration) {
                // Désactiver l'ancienne carte
                $cartes_personnel_administration->update(['statut' => 'annulee']);

                // Créer une nouvelle carte
                $nouvelleCarte = CartePersonnelAdministration::create([
                    'personnel_administration_id' => $cartes_personnel_administration->personnel_administration_id,
                    'numero_carte' => CartePersonnelAdministration::genererNumeroCarte(),
                    'date_emission' => now()->toDateString(),
                    'date_expiration' => $request->date_expiration,
                    'statut' => 'active',
                    'type_carte' => 'standard',
                    'qr_code' => $cartes_personnel_administration->qr_code, // Réutiliser le QR code
                    'observations' => $request->observations,
                    'emise_par' => auth()->id()
                ]);
            });

            return redirect()->route('cartes-personnel-administration.index')
                ->with('success', 'Carte personnel d\'administration renouvelée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du renouvellement : ' . $e->getMessage());
        }
    }
}
