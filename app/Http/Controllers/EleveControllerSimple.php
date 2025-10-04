<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Utilisateur;
use App\Models\ParentModel;
use App\Models\Classe;
use App\Models\AnneeScolaire;
use App\Helpers\SchoolHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class EleveControllerSimple extends Controller
{
    protected $imageService;
    
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Version simplifiée de la mise à jour d'élève
     */
    public function updateSimple(Request $request, Eleve $eleve)
    {
        \Log::info('=== DÉBUT MISE À JOUR SIMPLIFIÉE ===');
        \Log::info('Données reçues:', $request->all());
        
        try {
            // Validation minimale
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'numero_etudiant' => 'required|string|max:50',
                'classe_id' => 'required|exists:classes,id',
            ]);
            
            \Log::info('Validation réussie');
            
            // Mise à jour simple sans transaction
            $eleve->load('utilisateur');
            
            \Log::info('Avant mise à jour:', [
                'ancien_nom' => $eleve->utilisateur->nom,
                'ancien_prenom' => $eleve->utilisateur->prenom
            ]);
            
            // Mettre à jour l'utilisateur
            $eleve->utilisateur->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
            ]);
            
            // Mettre à jour l'élève
            $eleve->update([
                'numero_etudiant' => $request->numero_etudiant,
                'classe_id' => $request->classe_id,
            ]);
            
            \Log::info('Après mise à jour:', [
                'nouveau_nom' => $eleve->utilisateur->fresh()->nom,
                'nouveau_prenom' => $eleve->utilisateur->fresh()->prenom
            ]);
            
            \Log::info('=== MISE À JOUR RÉUSSIE ===');
            
            return redirect()->route('eleves.index')
                ->with('success', 'Élève mis à jour avec succès (version simplifiée)');
                
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour simplifiée:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }
}
