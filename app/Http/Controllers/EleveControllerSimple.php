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
        \Log::info('Élève ID:', ['id' => $eleve->id]);
        
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.edit')) {
            \Log::error('Permission refusée pour la mise à jour');
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier les élèves.');
        }
        
        try {
            // Validation minimale
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'numero_etudiant' => 'required|string|max:50',
                'classe_id' => 'required|exists:classes,id',
                'exempte_frais' => 'nullable|boolean',
                'paiement_annuel' => 'nullable|boolean',
                'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            \Log::info('Validation réussie');
            
            // Vérifier que l'élève a un utilisateur
            if (!$eleve->utilisateur) {
                \Log::error('Élève sans utilisateur associé', ['eleve_id' => $eleve->id]);
                return redirect()->back()->with('error', 'Aucun utilisateur associé à cet élève.');
            }
            
            // Mise à jour simple sans transaction
            $eleve->load('utilisateur');
            
            \Log::info('Avant mise à jour:', [
                'ancien_nom' => $eleve->utilisateur->nom,
                'ancien_prenom' => $eleve->utilisateur->prenom,
                'ancien_matricule' => $eleve->numero_etudiant,
                'ancienne_classe' => $eleve->classe_id,
                'ancienne_photo' => $eleve->utilisateur->photo_profil
            ]);
            
            // Préparer les données de mise à jour de l'utilisateur
            $userData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
            ];
            
            // Ajouter les champs optionnels s'ils sont présents
            if ($request->has('sexe')) {
                $userData['sexe'] = $request->sexe;
            }
            if ($request->has('date_naissance')) {
                $userData['date_naissance'] = $request->date_naissance;
            }
            if ($request->has('lieu_naissance')) {
                $userData['lieu_naissance'] = $request->lieu_naissance;
            }
            if ($request->has('telephone')) {
                $userData['telephone'] = $request->telephone;
            }
            if ($request->has('adresse')) {
                $userData['adresse'] = $request->adresse;
            }
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }
            
            // Gérer l'upload de la photo de profil
            if ($request->hasFile('photo_profil')) {
                \Log::info('Upload de nouvelle photo détecté');
                
                // Supprimer l'ancienne photo si elle existe
                if ($eleve->utilisateur->photo_profil) {
                    \Log::info('Suppression de l\'ancienne photo:', ['path' => $eleve->utilisateur->photo_profil]);
                    $this->imageService->deleteImage($eleve->utilisateur->photo_profil);
                }
                
                // Uploader la nouvelle photo
                $photoPath = $this->imageService->resizeAndSaveImage(
                    $request->file('photo_profil'),
                    'profile_images',
                    300,
                    300
                );
                
                $userData['photo_profil'] = $photoPath;
                
                \Log::info('Nouvelle photo uploadée:', ['path' => $photoPath]);
                
                // Synchroniser l'image pour XAMPP
                \App\Helpers\ImageSyncHelper::syncImage($photoPath);
            }
            
            // Mettre à jour l'utilisateur
            $updated = $eleve->utilisateur->update($userData);
            
            \Log::info('Mise à jour utilisateur:', ['success' => $updated]);
            
            // Vérifier si l'élève devient exempté des frais
            $etaitExempte = $eleve->exempte_frais;
            $devientExempte = (bool) $request->exempte_frais;
            
            // Mettre à jour l'élève
            $updatedEleve = $eleve->update([
                'numero_etudiant' => $request->numero_etudiant,
                'classe_id' => $request->classe_id,
                'exempte_frais' => $devientExempte,
                'paiement_annuel' => (bool) $request->paiement_annuel,
            ]);
            
            // Si l'élève devient exempté et qu'il avait des frais, les annuler
            if (!$etaitExempte && $devientExempte) {
                \App\Models\FraisScolarite::where('eleve_id', $eleve->id)
                    ->where('statut', 'en_attente')
                    ->update(['statut' => 'annule']);
                    
                \Log::info('Frais de scolarité annulés pour élève exempté:', ['eleve_id' => $eleve->id]);
            }
            
            \Log::info('Mise à jour élève:', [
                'success' => $updatedEleve,
                'exempte_frais' => $request->exempte_frais,
                'paiement_annuel' => $request->paiement_annuel
            ]);
            
            // Recharger les données
            $eleve->refresh();
            $eleve->load('utilisateur');
            
            \Log::info('Après mise à jour:', [
                'nouveau_nom' => $eleve->utilisateur->nom,
                'nouveau_prenom' => $eleve->utilisateur->prenom,
                'nouveau_matricule' => $eleve->numero_etudiant,
                'nouvelle_classe' => $eleve->classe_id,
                'nouvelle_photo' => $eleve->utilisateur->photo_profil
            ]);
            
            \Log::info('=== MISE À JOUR RÉUSSIE ===');
            
            return redirect()->route('eleves.index')
                ->with('success', 'Élève mis à jour avec succès !');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation:', [
                'errors' => $e->errors()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour simplifiée:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }
}
