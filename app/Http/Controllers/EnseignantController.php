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
        $enseignants = Enseignant::with('utilisateur')->paginate(20);
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
        $enseignant = Enseignant::with(['utilisateur', 'matieres', 'notes', 'emploisTemps'])->findOrFail($id);
        return view('enseignants.show', compact('enseignant'));
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
            'user_role' => auth()->user()->role,
            'user_email' => auth()->user()->email,
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'request_data' => $request->all()
        ]);
        
        // Debug: Afficher un message dans la réponse
        session()->flash('debug', 'Méthode update appelée avec succès !');
        session()->flash('info', 'Contrôleur atteint - Utilisateur: ' . auth()->user()->email . ' - Rôle: ' . auth()->user()->role);
        
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('enseignants.edit')) {
            \Log::warning('Permission refusée pour la mise à jour enseignant', [
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role
            ]);
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        
        $enseignant->load('utilisateur');
        
        $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:191|unique:utilisateurs,email,' . $enseignant->utilisateur_id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'numero_employe' => 'nullable|string|max:50|unique:enseignants,numero_employe,' . $enseignant->id,
            'specialite' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'statut' => 'nullable|in:titulaire,contractuel,vacataire',
            'matieres' => 'array',
            'matieres.*' => 'exists:matieres,id',
        ]);

        try {
            DB::transaction(function() use ($request, $enseignant) {
                // Préparer les données utilisateur à mettre à jour
                $userData = [];
                if ($request->filled('nom') || $request->filled('prenom')) {
                    $nom = $request->filled('nom') ? $request->nom : $enseignant->utilisateur->nom;
                    $prenom = $request->filled('prenom') ? $request->prenom : $enseignant->utilisateur->prenom;
                    $userData['name'] = $prenom . ' ' . $nom;
                }
                
                if ($request->filled('nom')) $userData['nom'] = $request->nom;
                if ($request->filled('prenom')) $userData['prenom'] = $request->prenom;
                if ($request->filled('email')) $userData['email'] = $request->email;
                if ($request->filled('telephone')) $userData['telephone'] = $request->telephone;
                if ($request->filled('adresse')) $userData['adresse'] = $request->adresse;
                if ($request->filled('date_naissance')) $userData['date_naissance'] = $request->date_naissance;
                if ($request->filled('lieu_naissance')) $userData['lieu_naissance'] = $request->lieu_naissance;
                if ($request->filled('sexe')) $userData['sexe'] = $request->sexe;
                
                // Mettre à jour l'utilisateur seulement si des données sont fournies
                if (!empty($userData)) {
                    $enseignant->utilisateur->update($userData);
                }

                // Préparer les données enseignant à mettre à jour
                $enseignantData = [];
                if ($request->filled('numero_employe')) $enseignantData['numero_employe'] = $request->numero_employe;
                if ($request->filled('specialite')) $enseignantData['specialite'] = $request->specialite;
                if ($request->filled('date_embauche')) $enseignantData['date_embauche'] = $request->date_embauche;
                if ($request->filled('statut')) $enseignantData['statut'] = $request->statut;
                
                // Mettre à jour l'enseignant seulement si des données sont fournies
                if (!empty($enseignantData)) {
                    $enseignant->update($enseignantData);
                }
                
                // Gérer l'upload de la photo de profil
                if ($request->hasFile('photo_profil')) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($enseignant->utilisateur->photo_profil) {
                        Storage::disk('public')->delete($enseignant->utilisateur->photo_profil);
                    }
                    
                    $photoPath = $request->file('photo_profil')->store('profile_images', 'public');
                    $enseignant->utilisateur->photo_profil = $photoPath;
                    $enseignant->utilisateur->save();
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
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('enseignants.index')
                ->with('success', 'Enseignant mis à jour avec succès');
                
        } catch (\Exception $e) {
            // Afficher l'erreur complète
            $errorMessage = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            $errorMessage .= '<br><strong>Fichier:</strong> ' . $e->getFile();
            $errorMessage .= '<br><strong>Ligne:</strong> ' . $e->getLine();
            $errorMessage .= '<br><strong>Trace:</strong><br><pre>' . $e->getTraceAsString() . '</pre>';
            
            \Log::error('Erreur mise à jour enseignant', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
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
}
