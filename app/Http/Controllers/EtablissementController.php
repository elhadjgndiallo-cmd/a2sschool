<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etablissement;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;
use App\Helpers\SchoolHelper;

class EtablissementController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Afficher les informations de l'établissement (consultation)
     */
    public function informations()
    {
        $etablissement = Etablissement::principal();
        
        return view('etablissement.informations', compact('etablissement'));
    }

    /**
     * Formulaire d'édition des informations de l'établissement
     */
    public function editInformations()
    {
        $etablissement = Etablissement::principal();
        
        // Si pas d'établissement, en créer un par défaut
        if (!$etablissement) {
            $etablissement = new Etablissement();
        }

        return view('etablissement.edit-informations', compact('etablissement'));
    }

    /**
     * Mettre à jour les informations de l'établissement
     */
    public function updateInformations(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'slogan' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cachet' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function() use ($request) {
                $etablissement = Etablissement::principal();
                
                if (!$etablissement) {
                    $etablissement = new Etablissement();
                }

                // Gestion du logo
                if ($request->hasFile('logo')) {
                    // Supprimer l'ancien logo
                    if ($etablissement->logo) {
                        $this->imageService->deleteImage($etablissement->logo);
                    }
                    
                    $logoPath = $this->imageService->resizeAndSaveImage(
                        $request->file('logo'),
                        'etablissement/logos',
                        200,
                        200
                    );
                    $etablissement->logo = $logoPath;
                }

                // Gestion du cachet
                if ($request->hasFile('cachet')) {
                    // Supprimer l'ancien cachet
                    if ($etablissement->cachet) {
                        $this->imageService->deleteImage($etablissement->cachet);
                    }
                    
                    $cachetPath = $this->imageService->resizeAndSaveImage(
                        $request->file('cachet'),
                        'etablissement/cachets',
                        150,
                        150
                    );
                    $etablissement->cachet = $cachetPath;
                }

                // Mettre à jour les autres informations
                $etablissement->fill($request->except(['logo', 'cachet']));
                $etablissement->actif = true;
                $etablissement->save();
                
                // Vider le cache après mise à jour
                SchoolHelper::clearCache();
            });

            return redirect()->route('etablissement.informations')
                ->with('success', 'Informations de l\'établissement mises à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les responsabilités de l'établissement (consultation)
     */
    public function responsabilites()
    {
        $etablissement = Etablissement::principal();
        
        return view('etablissement.responsabilites', compact('etablissement'));
    }

    /**
     * Formulaire d'édition des responsabilités de l'établissement
     */
    public function editResponsabilites()
    {
        $etablissement = Etablissement::principal();
        
        // Si pas d'établissement, en créer un par défaut
        if (!$etablissement) {
            $etablissement = new Etablissement();
        }

        return view('etablissement.edit-responsabilites', compact('etablissement'));
    }

    /**
     * Mettre à jour les responsabilités de l'établissement
     */
    public function updateResponsabilites(Request $request)
    {
        $request->validate([
            'dg' => 'nullable|string|max:255',
            'directeur_primaire' => 'nullable|string|max:255',
            'prefixe_matricule' => 'nullable|string|max:10',
            'suffixe_matricule' => 'nullable|string|max:10',
            'statut_etablissement' => 'required|in:prive,public,semi_prive',
        ]);

        try {
            $etablissement = Etablissement::principal();
            
            if (!$etablissement) {
                $etablissement = new Etablissement();
                $etablissement->nom = 'Établissement'; // Nom par défaut
                $etablissement->adresse = 'Adresse à définir'; // Adresse par défaut
            }

            $etablissement->fill($request->all());
            $etablissement->actif = true;
            $etablissement->save();
            
            // Vider le cache après mise à jour
            SchoolHelper::clearCache();

            return redirect()->route('etablissement.responsabilites')
                ->with('success', 'Responsabilités mises à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Réinitialiser les informations de l'établissement
     */
    public function reset()
    {
        try {
            $etablissement = Etablissement::principal();
            
            if ($etablissement) {
                // Supprimer les images
                if ($etablissement->logo) {
                    $this->imageService->deleteImage($etablissement->logo);
                }
                if ($etablissement->cachet) {
                    $this->imageService->deleteImage($etablissement->cachet);
                }
                
                // Supprimer l'établissement
                $etablissement->delete();
                
                // Vider le cache
                SchoolHelper::clearCache();
            }

            return redirect()->route('etablissement.informations')
                ->with('success', 'Informations de l\'établissement supprimées avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la réinitialisation: ' . $e->getMessage());
        }
    }
}
