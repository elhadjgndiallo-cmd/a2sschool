<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Utilisateur;
use App\Models\Matiere;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class EnseignantControllerSimple extends Controller
{
    protected $imageService;
    
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Version simplifiée de la mise à jour d'enseignant
     */
    public function updateSimple(Request $request, Enseignant $enseignant)
    {
        \Log::info('=== DÉBUT MISE À JOUR ENSEIGNANT SIMPLIFIÉE ===');
        \Log::info('Données reçues:', $request->all());
        
        try {
            // Validation minimale
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'numero_employe' => 'required|string|max:50',
            ]);
            
            \Log::info('Validation réussie');
            
            // Mise à jour simple sans transaction
            $enseignant->load('utilisateur');
            
            \Log::info('Avant mise à jour:', [
                'ancien_nom' => $enseignant->utilisateur->nom,
                'ancien_prenom' => $enseignant->utilisateur->prenom
            ]);
            
            // Mettre à jour l'utilisateur
            $enseignant->utilisateur->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
            ]);
            
            // Mettre à jour l'enseignant
            $enseignant->update([
                'numero_employe' => $request->numero_employe,
            ]);
            
            \Log::info('Après mise à jour:', [
                'nouveau_nom' => $enseignant->utilisateur->fresh()->nom,
                'nouveau_prenom' => $enseignant->utilisateur->fresh()->prenom
            ]);
            
            \Log::info('=== MISE À JOUR ENSEIGNANT RÉUSSIE ===');
            
            return redirect()->route('enseignants.index')
                ->with('success', 'Enseignant mis à jour avec succès (version simplifiée)');
                
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour enseignant simplifiée:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }
}
