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

class EnseignantController extends Controller
{
    /**
     * Service de gestion des images
     *
     * @var ImageService
     */
    protected $imageService;
    
    /**
     * Constructeur
     *
     * @param ImageService $imageService
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Afficher la liste des enseignants
     */
    public function index()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('enseignants.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        // Vider tous les caches pour forcer le rechargement des données
        \DB::flushQueryLog();
        \Cache::flush();
        
        $enseignants = Enseignant::with('utilisateur')->paginate(20);
        
        // S'assurer que les relations sont bien chargées et fraîches
        foreach ($enseignants as $enseignant) {
            // Forcer le rechargement de l'enseignant et de ses relations
            $enseignant->refresh();
            
            if (!$enseignant->relationLoaded('utilisateur')) {
                $enseignant->load('utilisateur');
            } else {
                // Recharger l'utilisateur même si la relation est chargée
                $enseignant->utilisateur->refresh();
            }
        }
        
        return view('enseignants.index', compact('enseignants'));
    }

    /**
     * Afficher le formulaire d'ajout d'enseignant
     */
    public function create()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('enseignants.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        $matieres = Matiere::actif()->get();
        return view('enseignants.create', compact('matieres'));
    }

    /**
     * Enregistrer un nouvel enseignant
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email|max:191',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            'numero_employe' => 'required|string|max:50|unique:enseignants,numero_employe',
            'specialite' => 'required|string|max:255',
            'date_embauche' => 'required|date',
            'statut' => 'required|in:titulaire,contractuel,vacataire',
            'matieres' => 'array',
            'matieres.*' => 'exists:matieres,id',
        ]);

        DB::transaction(function() use ($request) {
            // Créer l'utilisateur
            $utilisateur = Utilisateur::create([
                'name' => $request->prenom . ' ' . $request->nom, // Champ name requis
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Mot de passe par défaut
                'role' => 'teacher',
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'sexe' => $request->sexe,
                'actif' => true,
            ]);
            
            // Gérer l'upload de la photo de profil
            if ($request->hasFile('photo_profil')) {
                $photoPath = $this->imageService->resizeAndSaveImage(
                    $request->file('photo_profil'),
                    'profile_images',
                    300,
                    300
                );
                $utilisateur->photo_profil = $photoPath;
                $utilisateur->save();
            }

            // Créer l'enseignant
            $enseignant = Enseignant::create([
                'utilisateur_id' => $utilisateur->id,
                'numero_employe' => $request->numero_employe,
                'specialite' => $request->specialite,
                'date_embauche' => $request->date_embauche,
                'statut' => $request->statut,
                'actif' => true,
            ]);

            // Associer les matières si sélectionnées
            if ($request->has('matieres')) {
                $enseignant->matieres()->attach($request->matieres);
            }
        });

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant ajouté avec succès. Mot de passe par défaut: password123');
    }

    /**
     * Afficher les détails d'un enseignant
     */
    public function show($id)
    {
        try {
            $enseignant = Enseignant::with([
                'utilisateur', 
                'matieres' => function($query) {
                    $query->where('actif', true);
                },
                'notes' => function($query) {
                    $query->with(['eleve.utilisateur', 'matiere']);
                },
                'emploisTemps' => function($query) {
                    $query->with(['matiere', 'classe']);
                }
            ])->findOrFail($id);
            
            // Vérifier que l'enseignant a un utilisateur associé
            if (!$enseignant->utilisateur) {
                return redirect()->route('enseignants.index')
                    ->with('error', 'Aucun utilisateur associé à cet enseignant.');
            }
            
            return view('enseignants.show', compact('enseignant'));
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'affichage de l\'enseignant:', [
                'enseignant_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('enseignants.index')
                ->with('error', 'Erreur lors du chargement des détails de l\'enseignant.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Enseignant $enseignant)
    {
        $enseignant->load(['utilisateur', 'matieres']);
        $matieres = Matiere::actif()->get();
        return view('enseignants.edit', compact('enseignant', 'matieres'));
    }

    /**
     * Afficher le formulaire de test d'édition
     */
    public function testEdit(Enseignant $enseignant)
    {
        $enseignant->load(['utilisateur', 'matieres']);
        return view('enseignants.test-edit', compact('enseignant'));
    }

    /**
     * Supprimer la photo de profil d'un enseignant
     */
    public function deletePhoto(Enseignant $enseignant)
    {
        
        $utilisateur = $enseignant->utilisateur;
        
        if ($utilisateur && $utilisateur->photo_profil) {
            // Supprimer l'ancienne photo
            $this->imageService->deleteImage($utilisateur->photo_profil);
            
            // Mettre à jour l'utilisateur
            $utilisateur->photo_profil = null;
            $utilisateur->save();
            
            return redirect()->back()->with('success', 'Photo de profil supprimée avec succès');
        }
        
        return redirect()->back()->with('error', 'Aucune photo de profil à supprimer');
    }
    
    /**
     * Mettre à jour un enseignant
     */
    public function update(Request $request, Enseignant $enseignant)
    {
        // Debug: Log de la requête
        \Log::info('=== DÉBUT MISE À JOUR ENSEIGNANT ===', [
            'enseignant_id' => $enseignant->id,
            'user_id' => auth()->id(),
            'user_role' => auth()->user() ? auth()->user()->role : 'NULL',
            'user_email' => auth()->user() ? auth()->user()->email : 'NULL',
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'request_data' => $request->all(),
            'session_id' => session()->getId(),
            'auth_check' => auth()->check()
        ]);
        
        // Vérifier l'authentification
        if (!auth()->check()) {
            \Log::warning('Utilisateur non authentifié lors de la mise à jour enseignant', [
                'enseignant_id' => $enseignant->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId()
            ]);
            
            // Forcer la déconnexion
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            
            return redirect()->route('enseignants.index')->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }
        
        // Debug: Afficher un message dans la réponse
        session()->flash('debug', 'Méthode update appelée avec succès !');
        session()->flash('info', 'Contrôleur atteint - Utilisateur: ' . auth()->user()->email . ' - Rôle: ' . auth()->user()->role);
        
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('enseignants.edit')) {
            \Log::warning('Permission refusée pour la mise à jour enseignant', [
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role
            ]);
            return redirect()->route('enseignants.index')->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        $enseignant->load('utilisateur');
        
        $request->validate([
            'nom' => 'required|string|max:255|min:2',
            'prenom' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:191|unique:utilisateurs,email,' . $enseignant->utilisateur_id,
            'telephone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'required|in:M,F',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'numero_employe' => 'nullable|string|max:50|unique:enseignants,numero_employe,' . $enseignant->id,
            'specialite' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date|before_or_equal:today',
            'statut' => 'required|in:titulaire,contractuel,vacataire',
            'matieres' => 'nullable|array',
            'matieres.*' => 'exists:matieres,id',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'telephone.regex' => 'Le numéro de téléphone n\'est pas valide.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'sexe.in' => 'Le sexe doit être Masculin ou Féminin.',
            'photo_profil.image' => 'Le fichier doit être une image.',
            'photo_profil.mimes' => 'L\'image doit être au format jpeg, png, jpg, gif ou svg.',
            'photo_profil.max' => 'L\'image ne doit pas dépasser 2MB.',
            'numero_employe.unique' => 'Ce numéro d\'employé est déjà utilisé.',
            'date_embauche.date' => 'La date d\'embauche doit être valide.',
            'date_embauche.before_or_equal' => 'La date d\'embauche ne peut pas être future.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être titulaire, contractuel ou vacataire.',
            'matieres.array' => 'Les matières doivent être sélectionnées correctement.',
            'matieres.*.exists' => 'Une ou plusieurs matières sélectionnées n\'existent pas.',
        ]);

        try {
            DB::transaction(function() use ($request, $enseignant) {
                // Mettre à jour l'utilisateur
                $enseignant->utilisateur->update([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'name' => $request->prenom . ' ' . $request->nom,
                    'email' => $request->email,
                    'telephone' => $request->telephone,
                    'adresse' => $request->adresse,
                    'date_naissance' => $request->date_naissance,
                    'lieu_naissance' => $request->lieu_naissance,
                    'sexe' => $request->sexe,
                ]);

                // Mettre à jour l'enseignant
                $enseignant->update([
                    'numero_employe' => $request->numero_employe,
                    'specialite' => $request->specialite,
                    'date_embauche' => $request->date_embauche,
                    'statut' => $request->statut,
                ]);
                
                // Gérer l'upload de la photo de profil
                if ($request->hasFile('photo_profil')) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($enseignant->utilisateur->photo_profil) {
                        Storage::disk('public')->delete($enseignant->utilisateur->photo_profil);
                    }
                    
                    $photoPath = $request->file('photo_profil')->store('profile_images', 'public');
                    $enseignant->utilisateur->photo_profil = $photoPath;
                    $enseignant->utilisateur->save();
                    
                    // Synchroniser l'image pour XAMPP
                    \App\Helpers\ImageSyncHelper::syncImage($photoPath);
                }

                // Synchroniser les matières
                if ($request->has('matieres')) {
                    $enseignant->matieres()->sync($request->matieres);
                } else {
                    $enseignant->matieres()->detach();
                }
            });

            \Log::info('Enseignant mis à jour avec succès', [
                'enseignant_id' => $enseignant->id,
                'user_id' => auth()->id(),
                'nouveau_nom' => $enseignant->utilisateur->nom,
                'nouveau_prenom' => $enseignant->utilisateur->prenom
            ]);
            
            // Vider tous les caches pour s'assurer que les données sont fraîches
            \Cache::flush();
            \DB::flushQueryLog();
            
            // Recharger l'enseignant pour s'assurer que les données sont à jour
            $enseignant->refresh();
            $enseignant->load('utilisateur');
            
            \Log::info('Données après refresh', [
                'enseignant_id' => $enseignant->id,
                'nom_apres_refresh' => $enseignant->utilisateur->nom,
                'prenom_apres_refresh' => $enseignant->utilisateur->prenom
            ]);
            
            $response = redirect()->route('enseignants.show', $enseignant)
                ->with('success', 'Enseignant mis à jour avec succès');
            
            // Ajouter des headers pour empêcher la mise en cache
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            
            return $response;
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion spécifique des erreurs de validation
            \Log::warning('Erreur de validation lors de la mise à jour enseignant', [
                'enseignant_id' => $enseignant->id,
                'errors' => $e->errors()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            // Gestion des autres erreurs
            \Log::error('Erreur lors de la mise à jour enseignant', [
                'enseignant_id' => $enseignant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('enseignants.index')
                ->with('error', 'Erreur lors de la mise à jour de l\'enseignant. Veuillez réessayer.');
        }
    }

    /**
     * Test de modification d'enseignant
     */
    public function testUpdate(Request $request, Enseignant $enseignant)
    {
        \Log::info('=== TEST DE MODIFICATION ENSEIGNANT ===');
        \Log::info('Données reçues:', $request->all());
        \Log::info('Enseignant ID:', $enseignant->id);
        
        try {
            // Récupérer les données actuelles
            $enseignant->load('utilisateur');
            $ancienNom = $enseignant->utilisateur->nom;
            $ancienPrenom = $enseignant->utilisateur->prenom;
            
            \Log::info('Données actuelles:', [
                'ancien_nom' => $ancienNom,
                'ancien_prenom' => $ancienPrenom
            ]);
            
            // Créer des valeurs de test
            $timestamp = now()->format('His');
            $nouveauNom = $ancienNom . '_TEST_' . $timestamp;
            $nouveauPrenom = $ancienPrenom . '_TEST';
            
            \Log::info('Nouvelles valeurs de test:', [
                'nouveau_nom' => $nouveauNom,
                'nouveau_prenom' => $nouveauPrenom
            ]);
            
            // Effectuer la modification
            $enseignant->utilisateur->update([
                'nom' => $nouveauNom,
                'prenom' => $nouveauPrenom,
                'name' => $nouveauPrenom . ' ' . $nouveauNom
            ]);
            
            \Log::info('Modification effectuée avec succès');
            
            // Recharger pour vérifier
            $enseignant->refresh();
            $enseignant->load('utilisateur');
            
            \Log::info('Données après modification:', [
                'nom_apres' => $enseignant->utilisateur->nom,
                'prenom_apres' => $enseignant->utilisateur->prenom
            ]);
            
            // Restaurer les valeurs originales
            $enseignant->utilisateur->update([
                'nom' => $ancienNom,
                'prenom' => $ancienPrenom,
                'name' => $ancienPrenom . ' ' . $ancienNom
            ]);
            
            \Log::info('Valeurs restaurées');
            
            if ($request->wantsJson() || $request->has('test_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test de modification réussi !',
                    'ancien_nom' => $ancienNom,
                    'ancien_prenom' => $ancienPrenom,
                    'nouveau_nom' => $nouveauNom,
                    'nouveau_prenom' => $nouveauPrenom
                ]);
            }
            
            return redirect()->route('enseignants.edit', $enseignant)
                ->with('success', 'Test de modification réussi ! Vérifiez les logs pour plus de détails.');
                
        } catch (\Exception $e) {
            \Log::error('Erreur lors du test de modification:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('enseignants.edit', $enseignant)
                ->with('error', 'Erreur lors du test: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un enseignant
     */
    public function destroy($id)
    {
        
        $enseignant = Enseignant::findOrFail($id);
        
        DB::transaction(function() use ($enseignant) {
            // Détacher les matières
            $enseignant->matieres()->detach();
            
            // Désactiver au lieu de supprimer
            $enseignant->update(['actif' => false]);
            $enseignant->utilisateur->update(['actif' => false]);
        });

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant désactivé avec succès');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $newPassword = 'password123';
        
        $enseignant->utilisateur->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé: ' . $newPassword);
    }

    /**
     * Afficher la photo de l'enseignant
     */
    public function showPhoto(Enseignant $enseignant)
    {
        if (!$enseignant->utilisateur->photo_profil) {
            abort(404, 'Photo non trouvée');
        }

        $photoPath = storage_path('app/public/' . $enseignant->utilisateur->photo_profil);
        
        if (!file_exists($photoPath)) {
            abort(404, 'Fichier photo non trouvé');
        }

        return response()->file($photoPath);
    }

    /**
     * Réactiver un enseignant
     */
    public function reactivate($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        
        DB::transaction(function() use ($enseignant) {
            // Réactiver l'enseignant et l'utilisateur
            $enseignant->update(['actif' => true]);
            $enseignant->utilisateur->update(['actif' => true]);
        });

        return redirect()->back()->with('success', 'Enseignant réactivé avec succès');
    }

    /**
     * Supprimer définitivement un enseignant
     */
    public function deletePermanently($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        
        DB::transaction(function() use ($enseignant) {
            // Supprimer les salaires associés
            $enseignant->salaires()->delete();
            
            // Supprimer les cartes associées
            $enseignant->cartes()->delete();
            
            // Supprimer la photo de profil si elle existe
            if ($enseignant->utilisateur && $enseignant->utilisateur->photo_profil) {
                if (Storage::disk('public')->exists($enseignant->utilisateur->photo_profil)) {
                    Storage::disk('public')->delete($enseignant->utilisateur->photo_profil);
                }
            }
            
            // Supprimer l'utilisateur associé
            if ($enseignant->utilisateur) {
                $enseignant->utilisateur->delete();
            }
            
            // Supprimer l'enseignant
            $enseignant->delete();
        });

        return redirect()->route('enseignants.index')
            ->with('success', 'Enseignant supprimé définitivement avec succès');
    }
}
