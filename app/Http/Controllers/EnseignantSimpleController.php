<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnseignantSimpleController extends Controller
{
    /**
     * Afficher le formulaire de modification simplifié
     */
    public function editSimple(Enseignant $enseignant)
    {
        try {
            $enseignant->load('utilisateur');
            
            if (!$enseignant->utilisateur) {
                return redirect()->route('enseignants.index')
                    ->with('error', 'Aucun utilisateur associé à cet enseignant.');
            }
            
            return view('enseignants.edit-simple', compact('enseignant'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans editSimple:', [
                'enseignant_id' => $enseignant->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('enseignants.index')
                ->with('error', 'Erreur lors du chargement de la page.');
        }
    }
    
    /**
     * Mise à jour simplifiée
     */
    public function updateSimple(Request $request, Enseignant $enseignant)
    {
        Log::info('=== MISE À JOUR SIMPLIFIÉE COMPLÈTE ===');
        Log::info('Données reçues:', $request->all());
        
        try {
            // Validation complète (même que le formulaire d'inscription)
            $request->validate([
                // Informations de base
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|max:191|unique:utilisateurs,email,' . $enseignant->utilisateur_id,
                'telephone' => 'required|string|max:20',
                
                // Informations personnelles
                'date_naissance' => 'required|date|before:today',
                'lieu_naissance' => 'required|string|max:255',
                'sexe' => 'required|in:M,F',
                'adresse' => 'required|string|max:500',
                
                // Informations professionnelles
                'numero_employe' => 'required|string|max:50',
                'specialite' => 'required|string|max:255',
                'date_embauche' => 'required|date',
                'statut' => 'required|in:titulaire,contractuel,vacataire',
                
                // Photo
                'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'nom.required' => 'Le nom est obligatoire.',
                'prenom.required' => 'Le prénom est obligatoire.',
                'email.required' => 'L\'email est obligatoire.',
                'email.unique' => 'Cet email est déjà utilisé.',
                'telephone.required' => 'Le téléphone est obligatoire.',
                'date_naissance.required' => 'La date de naissance est obligatoire.',
                'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
                'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
                'sexe.required' => 'Le sexe est obligatoire.',
                'adresse.required' => 'L\'adresse est obligatoire.',
                'numero_employe.required' => 'Le numéro d\'employé est obligatoire.',
                'specialite.required' => 'La spécialité est obligatoire.',
                'date_embauche.required' => 'La date d\'embauche est obligatoire.',
                'statut.required' => 'Le statut est obligatoire.',
                'photo_profil.image' => 'Le fichier doit être une image.',
                'photo_profil.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
                'photo_profil.max' => 'L\'image ne doit pas dépasser 2MB.',
            ]);
            
            Log::info('Validation réussie');
            
            // Charger les relations
            $enseignant->load('utilisateur');
            
            $ancienNom = $enseignant->utilisateur->nom;
            $ancienPrenom = $enseignant->utilisateur->prenom;
            
            Log::info('Avant modification:', [
                'ancien_nom' => $ancienNom,
                'ancien_prenom' => $ancienPrenom
            ]);
            
            // Gestion de la photo
            $photoPath = null;
            if ($request->hasFile('photo_profil')) {
                $file = $request->file('photo_profil');
                $fileName = 'img_' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $photoPath = 'profile_images/' . $fileName;
                
                // Supprimer l'ancienne photo si elle existe
                if ($enseignant->utilisateur->photo_profil && \Storage::disk('public')->exists($enseignant->utilisateur->photo_profil)) {
                    \Storage::disk('public')->delete($enseignant->utilisateur->photo_profil);
                }
                
                // Sauvegarder la nouvelle photo
                $stored = $file->storeAs('public/profile_images', $fileName);
                
                // Copier aussi dans public/storage pour Windows
                $publicPath = public_path('storage/profile_images/' . $fileName);
                $publicDir = dirname($publicPath);
                
                if (!is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }
                
                copy($file->getRealPath(), $publicPath);
                
                // Vérifier que le fichier a été sauvegardé
                $fullPath = storage_path('app/public/' . $photoPath);
                $fileExists = file_exists($fullPath);
                $publicExists = file_exists($publicPath);
                
                Log::info('Nouvelle photo uploadée:', [
                    'ancienne_photo' => $enseignant->utilisateur->photo_profil,
                    'nouvelle_photo' => $photoPath,
                    'stored_path' => $stored,
                    'full_path' => $fullPath,
                    'public_path' => $publicPath,
                    'fichier_existe_storage' => $fileExists,
                    'fichier_existe_public' => $publicExists,
                    'taille_fichier_storage' => $fileExists ? filesize($fullPath) : 0,
                    'taille_fichier_public' => $publicExists ? filesize($publicPath) : 0
                ]);
                
                if (!$fileExists && !$publicExists) {
                    Log::error('ERREUR: Le fichier n\'a pas été sauvegardé correctement');
                    throw new \Exception('Erreur lors de la sauvegarde de l\'image');
                }
            }
            
            // Mettre à jour l'utilisateur
            $utilisateurData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'sexe' => $request->sexe,
                'adresse' => $request->adresse,
            ];
            
            // Ajouter la photo si elle a été uploadée
            if ($photoPath) {
                $utilisateurData['photo_profil'] = $photoPath;
                Log::info('Photo ajoutée aux données utilisateur:', ['photo_path' => $photoPath]);
            }
            
            $enseignant->utilisateur->update($utilisateurData);
            
            // Forcer le rechargement de la relation
            $enseignant->utilisateur->refresh();
            
            // Mettre à jour l'enseignant
            $enseignant->update([
                'numero_employe' => $request->numero_employe,
                'specialite' => $request->specialite,
                'date_embauche' => $request->date_embauche,
                'statut' => $request->statut,
            ]);
            
            Log::info('Après modification:', [
                'nouveau_nom' => $enseignant->utilisateur->nom,
                'nouveau_prenom' => $enseignant->utilisateur->prenom,
                'specialite' => $enseignant->specialite,
                'statut' => $enseignant->statut,
                'photo_profil' => $enseignant->utilisateur->photo_profil,
                'photo_existe' => $enseignant->utilisateur->photo_profil ? \Storage::disk('public')->exists($enseignant->utilisateur->photo_profil) : false
            ]);
            
            // Recharger pour vérifier
            $enseignant->refresh();
            $enseignant->load('utilisateur');
            
            Log::info('Vérification après refresh:', [
                'nom_final' => $enseignant->utilisateur->nom,
                'prenom_final' => $enseignant->utilisateur->prenom
            ]);
            
            return redirect()->route('enseignants.index')
                ->with('success', 'Enseignant modifié avec succès !')
                ->with('message', 'Les modifications ont été sauvegardées.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Erreur de validation:', [
                'errors' => $e->errors()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }
}
