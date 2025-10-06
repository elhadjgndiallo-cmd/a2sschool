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
use App\Http\Controllers\PaiementController;

class EleveController extends Controller
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
     * Afficher la liste des élèves
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }
        // Vider tous les caches pour forcer le rechargement des données
        \DB::flushQueryLog();
        \Cache::flush();
        
        // Construction de la requête avec filtres
        $query = Eleve::select('*')->with([
            'utilisateur', 
            'classe', 
            'anneeScolaire',
            'fraisScolarite' => function($query) {
                $query->where('type_frais', 'scolarite');
            },
            'parents' => function($query) {
                $query->with('utilisateur');
            }
        ]);

        // Filtre par année scolaire
        if ($request->filled('annee_scolaire_id')) {
            $query->where('annee_scolaire_id', $request->annee_scolaire_id);
        }

        // Filtre par classe
        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par statut actif/inactif
        if ($request->filled('actif')) {
            $query->where('actif', $request->actif === '1');
        }

        // Filtre par matricule
        if ($request->filled('matricule')) {
            $query->where('numero_etudiant', 'LIKE', '%' . $request->matricule . '%');
        }

        // Filtre par nom complet (recherche dans nom et prénom)
        if ($request->filled('nom_complet')) {
            $searchTerm = $request->nom_complet;
            $query->whereHas('utilisateur', function($q) use ($searchTerm) {
                $q->where(function($subQuery) use ($searchTerm) {
                    $subQuery->where('nom', 'LIKE', '%' . $searchTerm . '%')
                             ->orWhere('prenom', 'LIKE', '%' . $searchTerm . '%')
                             ->orWhere('name', 'LIKE', '%' . $searchTerm . '%');
                });
            });
        }

        // Nombre d'éléments par page (par défaut 20)
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $eleves = $query->orderBy('updated_at', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->paginate($perPage)
                       ->appends($request->query());
        
        // S'assurer que les relations sont bien chargées et fraîches
        foreach ($eleves as $eleve) {
            // Forcer le rechargement de l'élève et de ses relations
            $eleve->refresh();
            
            if (!$eleve->relationLoaded('utilisateur')) {
                $eleve->load('utilisateur');
            } else {
                // Recharger l'utilisateur même si la relation est chargée
                $eleve->utilisateur->refresh();
            }
            
            if (!$eleve->relationLoaded('parents')) {
                $eleve->load('parents.utilisateur');
            } else {
                // Recharger les parents même si la relation est chargée
                $eleve->load('parents.utilisateur');
            }
            
            // Forcer le rechargement des données parent
            foreach ($eleve->parents as $parent) {
                if ($parent->utilisateur) {
                    $parent->utilisateur->refresh();
                }
            }
        }

        // Données pour les filtres
        $anneesScolarires = \App\Models\AnneeScolaire::orderBy('date_debut', 'desc')->get();
        $classes = \App\Models\Classe::actif()->orderBy('nom')->get();
        $statutsEleves = ['inscrit', 'en_cours', 'diplome', 'abandonne'];

        return view('eleves.index', compact(
            'eleves', 
            'anneesScolarires', 
            'classes', 
            'statutsEleves'
        ));
    }

    /**
     * Afficher le formulaire d'ajout d'élève
     */
    public function create()
    {
        // Réinitialiser la session pour une nouvelle inscription
        session()->forget(['current_step', 'student_data']);
        session(['current_step' => 1]);
        
        // Récupérer les données nécessaires
        $classes = Classe::actif()->get();
        $anneesScolarites = AnneeScolaire::orderBy('active', 'desc')->orderBy('date_debut', 'desc')->get();
        $parents = ParentModel::with('utilisateur')->get();
        
        // Debug pour voir les parents disponibles
        \Log::info('Parents disponibles:', [
            'count' => $parents->count(),
            'parents' => $parents->pluck('utilisateur.nom_complet', 'id')->toArray()
        ]);
        
        return view('eleves.create', compact('classes', 'anneesScolarites', 'parents'));
    }

    /**
     * Enregistrer un nouvel élève (redirection vers le système d'étapes)
     */
    public function store(Request $request)
    {
        // Validation personnalisée pour les emails
        $rules = [
            // Données élève
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|max:191',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'required|string',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            'classe_id' => 'required|exists:classes,id',
            'numero_etudiant' => 'required|string|max:50|unique:eleves,numero_etudiant',
            
            // Parent obligatoire - soit existant soit nouveau
            'parent_type' => 'required|in:existing,new',
            'parent_id' => 'required_if:parent_type,existing|nullable|exists:parents,id',
            
            // Données parent si nouveau
            'parent_nom' => 'required_if:parent_type,new|string|max:255',
            'parent_prenom' => 'required_if:parent_type,new|string|max:255',
            'parent_email' => 'nullable|email|max:191',
            'parent_telephone' => 'nullable|string|max:20',
            'parent_whatsapp' => 'nullable|string|max:20',
            'parent_adresse' => 'nullable|string',
            'parent_sexe' => 'required_if:parent_type,new|in:M,F',
            
            // Relation parent-élève
            'lien_parente' => 'required|in:pere,mere,tuteur,tutrice,grand_pere,grand_mere,oncle,tante,autre',
            'autre_lien_parente' => 'required_if:lien_parente,autre|nullable|string|max:255',
            'responsable_legal' => 'nullable|boolean',
            'contact_urgence' => 'nullable|boolean',
            'autorise_sortie' => 'nullable|boolean',
        ];

        // Ajouter la validation unique seulement si l'email est fourni
        if ($request->filled('email')) {
            $rules['email'] .= '|unique:utilisateurs,email';
        }
        
        if ($request->filled('parent_email')) {
            $rules['parent_email'] .= '|unique:utilisateurs,email';
        }

        $request->validate($rules);

        DB::transaction(function() use ($request) {
            // Générer un email pour l'élève si non fourni
            $eleveEmail = $request->email;
            if (empty($eleveEmail)) {
                $baseEmail = strtolower($request->prenom . '.' . $request->nom);
                $baseEmail = preg_replace('/[^a-z0-9.]/', '', $baseEmail);
                $counter = 1;
                do {
                    $eleveEmail = $baseEmail . ($counter > 1 ? $counter : '') . '@student.local';
                    $counter++;
                } while (Utilisateur::where('email', $eleveEmail)->exists());
            }
            
            // Créer l'utilisateur élève
            $utilisateurEleve = Utilisateur::create([
                'name' => $request->prenom . ' ' . $request->nom,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $eleveEmail,
                'password' => Hash::make('student123'), // Mot de passe par défaut
                'role' => 'student',
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
                $utilisateurEleve->photo_profil = $photoPath;
                $utilisateurEleve->save();
            }

            // Récupérer l'année scolaire active
            $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
            
            // Créer l'élève
            $eleve = Eleve::create([
                'utilisateur_id' => $utilisateurEleve->id,
                'classe_id' => $request->classe_id,
                'numero_etudiant' => $request->numero_etudiant,
                'date_inscription' => now(),
                'type_inscription' => $request->type_inscription ?? 'nouvelle',
                'ecole_origine' => $request->ecole_origine,
                'situation_matrimoniale' => $request->situation_matrimoniale,
                'statut' => 'inscrit', // Valeur ENUM valide
                'annee_scolaire_id' => $anneeScolaireActive ? $anneeScolaireActive->id : null,
                'exempte_frais' => $request->boolean('exempte_frais'),
                'paiement_annuel' => $request->boolean('paiement_annuel'),
                'actif' => true,
            ]);

            // Gérer le parent
            if ($request->parent_type === 'new') {
                // Créer un nouveau parent
                // Générer un email unique si non fourni
                $parentEmail = $request->parent_email;
                if (empty($parentEmail)) {
                    $baseEmail = strtolower($request->parent_prenom . '.' . $request->parent_nom);
                    $baseEmail = preg_replace('/[^a-z0-9.]/', '', $baseEmail); // Nettoyer l'email
                    $counter = 1;
                    do {
                        $parentEmail = $baseEmail . ($counter > 1 ? $counter : '') . '@parent.local';
                        $counter++;
                    } while (Utilisateur::where('email', $parentEmail)->exists());
                }
                
                $utilisateurParent = Utilisateur::create([
                    'name' => $request->parent_prenom . ' ' . $request->parent_nom,
                    'nom' => $request->parent_nom,
                    'prenom' => $request->parent_prenom,
                    'email' => $parentEmail,
                    'password' => Hash::make('parent123'), // Mot de passe par défaut
                    'role' => 'parent',
                    'telephone' => $request->parent_telephone,
                    'adresse' => $request->parent_adresse,
                    'date_naissance' => null, // Champ supprimé
                    'lieu_naissance' => null, // Champ supprimé
                    'sexe' => $request->parent_sexe,
                    'actif' => true,
                ]);

                $parent = ParentModel::create([
                    'utilisateur_id' => $utilisateurParent->id,
                    'numero_cni' => null, // Champ supprimé
                    'profession' => null, // Champ supprimé
                    'actif' => true,
                ]);

                $parentId = $parent->id;
            } else {
                // Utiliser un parent existant
                $parentId = $request->parent_id;
            }

            // Créer la relation parent-élève
            $eleve->parents()->attach($parentId, [
                'lien_parente' => $request->lien_parente === 'autre' ? $request->autre_lien_parente : $request->lien_parente,
                'responsable_legal' => (bool) $request->responsable_legal,
                'contact_urgence' => (bool) $request->contact_urgence,
                'autorise_sortie' => (bool) $request->autorise_sortie,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // Si c'est une requête d'étape, la rediriger vers storeStep
        if ($request->has('step')) {
            return $this->storeStep($request);
        }
        
        // Pour l'ancien système, on redirige vers les étapes maintenant
        return redirect()->route('eleves.create')
            ->with('info', 'Veuillez utiliser le nouveau formulaire d\'inscription par étapes.');
    }

    /**
     * Afficher les détails d'un élève
     */
    public function show($id)
    {
        $eleve = Eleve::with([
            'utilisateur', 
            'classe', 
            'parents.utilisateur', 
            'notes.matiere', 
            'absences'
        ])->findOrFail($id);
        
        return view('eleves.show', compact('eleve'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        // Charger l'élève avec toutes ses relations
        $eleve = Eleve::with([
            'utilisateur', 
            'classe', 
            'anneeScolaire',
            'parents' => function($query) {
                $query->with('utilisateur');
            }
        ])->findOrFail($id);
        
        // S'assurer que toutes les relations sont chargées
        if (!$eleve->relationLoaded('utilisateur')) {
            $eleve->load('utilisateur');
        }
        
        if (!$eleve->relationLoaded('classe')) {
            $eleve->load('classe');
        }
        
        if (!$eleve->relationLoaded('parents')) {
            $eleve->load('parents.utilisateur');
        }
        
        // Vérification supplémentaire pour les parents
        foreach ($eleve->parents as $parent) {
            if (!$parent->relationLoaded('utilisateur')) {
                $parent->load('utilisateur');
            }
        }
        
        $classes = Classe::actif()->get();
        $anneesScolarites = AnneeScolaire::orderBy('active', 'desc')->orderBy('date_debut', 'desc')->get();
        $parents = ParentModel::with('utilisateur')->get();
        
        
        return view('eleves.edit', compact('eleve', 'classes', 'anneesScolarites', 'parents'));
    }

    /**
     * Supprimer la photo de profil d'un élève
     */
    public function deletePhoto(Eleve $eleve)
    {
        // Charger la relation utilisateur si elle n'est pas déjà chargée
        $eleve->load('utilisateur');
        
        $utilisateur = $eleve->utilisateur;
        
        if (!$utilisateur) {
            return redirect()->back()->with('error', 'Aucun utilisateur associé à cet élève.');
        }
        
        if ($utilisateur->photo_profil) {
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
     * Mettre à jour un élève
     */
    public function update(Request $request, Eleve $eleve)
    {
        // Charger les relations nécessaires
        $eleve->load(['utilisateur', 'parents' => function($query) {
            $query->with('utilisateur');
        }]);
        
        // Vérifier que l'élève a un utilisateur associé
        if (!$eleve->utilisateur) {
            \Log::error("Élève sans utilisateur", [
                'eleve_id' => $eleve->id,
                'utilisateur_id' => $eleve->utilisateur_id
            ]);
            return redirect()->back()->with('error', 'Aucun utilisateur associé à cet élève.');
        }

        // Validation des données
        $rules = [
            // Informations élève
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|max:191|unique:utilisateurs,email,' . $eleve->utilisateur_id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'situation_matrimoniale' => 'nullable|in:celibataire,marie,divorce,veuf',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Informations inscription
            'numero_etudiant' => 'required|string|max:50|unique:eleves,numero_etudiant,' . $eleve->id,
            'date_inscription' => 'required|date',
            'type_inscription' => 'required|in:nouvelle,reinscription,transfert',
            'ecole_origine' => 'nullable|string|max:255',
            'statut' => 'required|in:actif,inactif,suspendu,diplome,abandonne',
            'classe_id' => 'required|exists:classes,id',
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id',
            'exempte_frais' => 'boolean',
            'paiement_annuel' => 'boolean',
            
            // Informations parent
            'parent_prenom' => 'nullable|string|max:255',
            'parent_nom' => 'nullable|string|max:255',
            'parent_telephone' => 'nullable|string|max:20',
            'parent_email' => 'nullable|email|max:191',
            'parent_adresse' => 'nullable|string',
            'lien_parente' => 'required|in:pere,mere,tuteur,tutrice,grand_pere,grand_mere,oncle,tante,autre',
            'autre_lien_parente' => 'nullable|string|max:255',
            'responsable_legal' => 'boolean',
            'contact_urgence' => 'boolean',
            'autorise_sortie' => 'boolean',
        ];

        // Validation conditionnelle pour "autre" lien de parenté
        if ($request->input('lien_parente') === 'autre') {
            $rules['autre_lien_parente'] = 'required|string|max:255';
        }

        // Validation conditionnelle pour l'email parent si fourni
        if ($request->filled('parent_email')) {
            $parentId = $eleve->parents->first()->id ?? null;
            $parentUtilisateurId = $eleve->parents->first()->utilisateur->id ?? null;
            if ($parentUtilisateurId) {
                $rules['parent_email'] .= '|unique:utilisateurs,email,' . $parentUtilisateurId;
            }
        }

        \Log::info('Validation des données de l\'élève...', [
            'eleve_id' => $eleve->id,
            'rules_count' => count($rules),
            'request_data' => $request->all()
        ]);

        try {
            $request->validate($rules);
            \Log::info('Validation réussie, début de la mise à jour...');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            DB::transaction(function() use ($request, $eleve) {
                \Log::info('Début de la transaction de mise à jour...');
                
                // Mettre à jour l'utilisateur élève
                \Log::info('Mise à jour de l\'utilisateur...', [
                    'utilisateur_id' => $eleve->utilisateur->id,
                    'ancien_nom' => $eleve->utilisateur->nom,
                    'nouveau_nom' => $request->nom
                ]);
                
                $eleve->utilisateur->update([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'name' => $request->prenom . ' ' . $request->nom, // Mettre à jour le champ 'name' aussi
                    'email' => $request->email,
                    'telephone' => $request->telephone,
                    'adresse' => $request->adresse,
                    'date_naissance' => $request->date_naissance,
                    'lieu_naissance' => $request->lieu_naissance,
                    'sexe' => $request->sexe,
                ]);
                
                \Log::info('Utilisateur mis à jour avec succès');

                // Gérer l'upload de la photo de profil
                if ($request->hasFile('photo_profil')) {
                    // Supprimer l'ancienne photo si elle existe
                    if ($eleve->utilisateur->photo_profil) {
                        $this->imageService->deleteImage($eleve->utilisateur->photo_profil);
                    }
                    
                    // Uploader la nouvelle photo
                    $photoPath = $this->imageService->resizeAndSaveImage(
                        $request->file('photo_profil'),
                        'profile_images',
                        300,
                        300
                    );
                    
                    $eleve->utilisateur->photo_profil = $photoPath;
                    $eleve->utilisateur->save();
                    
                    // Synchroniser l'image pour XAMPP
                    \App\Helpers\ImageSyncHelper::syncImage($photoPath);
                }

                // Mettre à jour l'élève
                $eleve->update([
                    'numero_etudiant' => $request->numero_etudiant,
                    'date_inscription' => $request->date_inscription,
                    'type_inscription' => $request->type_inscription,
                    'ecole_origine' => $request->ecole_origine,
                    'statut' => $request->statut,
                    'classe_id' => $request->classe_id,
                    'annee_scolaire_id' => $request->annee_scolaire_id,
                    'situation_matrimoniale' => $request->situation_matrimoniale,
                    'exempte_frais' => (bool) $request->exempte_frais,
                    'paiement_annuel' => (bool) $request->paiement_annuel,
                ]);

                // Gérer les informations du parent
                \Log::info('Mise à jour parent - données reçues:', [
                    'parent_prenom' => $request->parent_prenom,
                    'parent_nom' => $request->parent_nom,
                    'parent_telephone' => $request->parent_telephone,
                    'parent_email' => $request->parent_email,
                    'lien_parente' => $request->lien_parente,
                    'responsable_legal' => $request->responsable_legal,
                    'contact_urgence' => $request->contact_urgence,
                    'autorise_sortie' => $request->autorise_sortie,
                ]);
                
                $parent = $eleve->parents->first();
                
                if ($parent) {
                    
                    // Mettre à jour le parent existant
                    $parentData = [];
                    
                    // Récupérer les nouvelles valeurs ou conserver les anciennes
                    $nouveauPrenom = $request->filled('parent_prenom') ? $request->parent_prenom : $parent->utilisateur->prenom;
                    $nouveauNom = $request->filled('parent_nom') ? $request->parent_nom : $parent->utilisateur->nom;
                    
                    if ($request->filled('parent_nom')) {
                        $parentData['nom'] = $request->parent_nom;
                    }
                    if ($request->filled('parent_prenom')) {
                        $parentData['prenom'] = $request->parent_prenom;
                    }
                    
                    // Toujours mettre à jour le champ 'name' si nom ou prénom change
                    if ($request->filled('parent_nom') || $request->filled('parent_prenom')) {
                        $parentData['name'] = $nouveauPrenom . ' ' . $nouveauNom;
                    }
                    
                    if ($request->filled('parent_telephone')) {
                        $parentData['telephone'] = $request->parent_telephone;
                    }
                    if ($request->filled('parent_email')) {
                        $parentData['email'] = $request->parent_email;
                    }
                    if ($request->filled('parent_adresse')) {
                        $parentData['adresse'] = $request->parent_adresse;
                    }
                    
                    if (!empty($parentData)) {
                        $parent->utilisateur->update($parentData);
                        // Forcer la sauvegarde
                        $parent->utilisateur->save();
                    }

                    // Mettre à jour la relation pivot
                    $pivotData = [
                        'lien_parente' => $request->lien_parente === 'autre' ? $request->autre_lien_parente : $request->lien_parente,
                        'autre_lien_parente' => $request->lien_parente === 'autre' ? $request->autre_lien_parente : null,
                        'responsable_legal' => (bool) $request->responsable_legal,
                        'contact_urgence' => (bool) $request->contact_urgence,
                        'autorise_sortie' => (bool) $request->autorise_sortie,
                        'updated_at' => now(),
                    ];
                    
                    $eleve->parents()->updateExistingPivot($parent->id, $pivotData);
                    
                    // Forcer la mise à jour du timestamp de l'élève
                    $eleve->touch();
                    
                } elseif ($request->filled('parent_prenom') || $request->filled('parent_nom')) {
                    // Créer un nouveau parent si aucun n'existe
                    $parentEmail = $request->parent_email;
                    if (empty($parentEmail)) {
                        $baseEmail = strtolower($request->parent_prenom . '.' . $request->parent_nom);
                        $baseEmail = preg_replace('/[^a-z0-9.]/', '', $baseEmail);
                        $counter = 1;
                        do {
                            $parentEmail = $baseEmail . ($counter > 1 ? $counter : '') . '@parent.local';
                            $counter++;
                        } while (Utilisateur::where('email', $parentEmail)->exists());
                    }

                    $utilisateurParent = Utilisateur::create([
                        'nom' => $request->parent_nom,
                        'prenom' => $request->parent_prenom,
                        'name' => $request->parent_prenom . ' ' . $request->parent_nom,
                        'email' => $parentEmail,
                        'password' => Hash::make('parent123'),
                        'role' => 'parent',
                        'telephone' => $request->parent_telephone,
                        'adresse' => $request->parent_adresse,
                        'actif' => true,
                    ]);

                    $parent = ParentModel::create([
                        'utilisateur_id' => $utilisateurParent->id,
                    ]);

                    // Associer le parent à l'élève
                    $eleve->parents()->attach($parent->id, [
                        'lien_parente' => $request->lien_parente === 'autre' ? $request->autre_lien_parente : $request->lien_parente,
                        'autre_lien_parente' => $request->lien_parente === 'autre' ? $request->autre_lien_parente : null,
                        'responsable_legal' => (bool) $request->responsable_legal,
                        'contact_urgence' => (bool) $request->contact_urgence,
                        'autorise_sortie' => (bool) $request->autorise_sortie,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Forcer le rafraîchissement des relations
                $eleve->refresh();
                $eleve->load(['utilisateur', 'parents.utilisateur', 'classe']);
                
                // Vider tous les caches potentiels
                \Cache::flush();
                
                \Log::info('Transaction terminée avec succès', [
                    'eleve_id' => $eleve->id,
                    'nouveau_nom' => $eleve->utilisateur->nom
                ]);
            });

            // S'assurer que la transaction est bien terminée
            if (\DB::transactionLevel() > 0) {
                \DB::commit();
            }

            // Vider tous les caches pour s'assurer que les données sont fraîches
            \Cache::flush();
            \DB::flushQueryLog();
            
            // Redirection avec plusieurs options de fallback
            try {
                return redirect()->route('eleves.index')
                    ->with('success', 'Élève mis à jour avec succès');
            } catch (\Exception $redirectException) {
                // Fallback si la route ne fonctionne pas
                return redirect('/eleves')
                    ->with('success', 'Élève mis à jour avec succès');
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de l\'élève:', [
                'eleve_id' => $eleve->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un élève
     */
    public function destroy($id)
    {
        $eleve = Eleve::findOrFail($id);
        
        DB::transaction(function() use ($eleve) {
            // Désactiver au lieu de supprimer
            $eleve->update(['actif' => false, 'statut' => 'abandonne']);
            $eleve->utilisateur->update(['actif' => false]);
        });

        return redirect()->route('eleves.index')
            ->with('success', 'Élève désactivé avec succès');
    }

    /**
     * Réactiver un élève désactivé
     */
    public function reactivate($id)
    {
        $eleve = Eleve::findOrFail($id);
        
        DB::transaction(function() use ($eleve) {
            // Réactiver l'élève
            $eleve->update(['actif' => true, 'statut' => 'inscrit']);
            $eleve->utilisateur->update(['actif' => true]);
        });

        return redirect()->route('eleves.index')
            ->with('success', 'Élève réactivé avec succès');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($id)
    {
        $eleve = Eleve::findOrFail($id);
        $newPassword = 'student123';
        
        $eleve->utilisateur->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé: ' . $newPassword);
    }

    /**
     * Ajouter un parent à un élève
     */
    public function addParent(Request $request, $id)
    {
        $eleve = Eleve::findOrFail($id);
        
        $request->validate([
            'parent_id' => 'required|exists:parents,id',
            'lien_parente' => 'required|in:pere,mere,tuteur,grand_parent,autre',
            'responsable_legal' => 'boolean',
            'contact_urgence' => 'boolean',
        ]);

        $eleve->parents()->attach($request->parent_id, [
            'lien_parente' => $request->lien_parente,
            'responsable_legal' => $request->has('responsable_legal'),
            'contact_urgence' => $request->has('contact_urgence'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Parent ajouté avec succès');
    }


    /**
     * Traiter une étape du formulaire
     */
    public function storeStep(Request $request)
    {
        $step = $request->input('step');
        
        if ($step === 'final') {
            return $this->handleFinalForm($request);
        }
        
        $studentData = session('student_data', []);

        switch($step) {
            case 1:
                return $this->handleStep1($request, $studentData);
            case 2:
                return $this->handleStep2($request, $studentData);
            case 3:
                return $this->handleStep3($request, $studentData);
            case 4:
                return $this->handleStep4($request, $studentData);
            default:
                return redirect()->route('eleves.create');
        }
    }

    /**
     * Gérer l'étape 1 - Photo
     */
    private function handleStep1(Request $request, array $studentData)
    {
        // La photo est optionnelle à cette étape
        if ($request->hasFile('photo_profil')) {
            $request->validate([
                'photo_profil' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Sauvegarder temporairement la photo
            $photo = $request->file('photo_profil');
            $photoPath = $photo->store('temp_photos', 'public');
            $studentData['photo_profil_temp'] = $photoPath;
            $studentData['photo_profil_preview'] = asset('storage/' . $photoPath);
        }

        session(['student_data' => $studentData, 'current_step' => 2]);
        return redirect()->route('eleves.create-multi-step');
    }

    /**
     * Gérer l'étape 2 - Informations élève
     */
    private function handleStep2(Request $request, array $studentData)
    {
        $request->validate([
            'numero_etudiant' => 'required|string|unique:eleves,numero_etudiant',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'nullable|string|max:20',
            'situation_matrimoniale' => 'nullable|in:celibataire,marie,divorce,veuf',
        ]);

        // Sauvegarder les données de l'étape
        $studentData = array_merge($studentData, $request->only([
            'numero_etudiant', 'prenom', 'nom', 'sexe', 'date_naissance', 
            'lieu_naissance', 'adresse', 'telephone', 'situation_matrimoniale'
        ]));

        session(['student_data' => $studentData, 'current_step' => 3]);
        return redirect()->route('eleves.create-multi-step');
    }

    /**
     * Gérer l'étape 3 - Informations parent
     */
    private function handleStep3(Request $request, array $studentData)
    {
        $rules = [
            'parent_type' => 'required|in:existing,new',
            'lien_parente' => 'required|in:pere,mere,tuteur,tutrice,grand_pere,grand_mere,oncle,tante,autre',
            'responsable_legal' => 'boolean',
            'contact_urgence' => 'boolean',
            'autorise_sortie' => 'boolean',
        ];

        if ($request->input('parent_type') === 'existing') {
            $rules['parent_id'] = 'required|exists:parents,id';
        } else {
            $rules['parent_prenom'] = 'required|string|max:255';
            $rules['parent_nom'] = 'required|string|max:255';
            $rules['parent_telephone'] = 'nullable|string|max:20';
            $rules['parent_email'] = 'nullable|email|unique:utilisateurs,email';
            $rules['parent_adresse'] = 'nullable|string';
        }

        if ($request->input('lien_parente') === 'autre') {
            $rules['autre_lien_parente'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // Sauvegarder les données de l'étape
        $studentData = array_merge($studentData, $request->only([
            'parent_type', 'parent_id', 'parent_prenom', 'parent_nom', 
            'parent_telephone', 'parent_email', 'parent_adresse',
            'lien_parente', 'autre_lien_parente', 'responsable_legal', 
            'contact_urgence', 'autorise_sortie'
        ]));

        session(['student_data' => $studentData, 'current_step' => 4]);
        return redirect()->route('eleves.create-multi-step');
    }

    /**
     * Gérer l'étape 4 - Finaliser l'inscription
     */
    private function handleStep4(Request $request, array $studentData)
    {
        $request->validate([
            'date_inscription' => 'required|date',
            'type_inscription' => 'required|in:nouvelle,reinscription,transfert',
            'ecole_origine' => 'nullable|string|max:255',
            'statut' => 'required|in:inscrit,en_cours',
            'classe_id' => 'required|exists:classes,id',
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id',
            'exempte_frais' => 'boolean',
            'paiement_annuel' => 'boolean',
        ]);

        // Sauvegarder les données finales
        $studentData = array_merge($studentData, $request->only([
            'date_inscription', 'type_inscription', 'ecole_origine', 'statut',
            'classe_id', 'annee_scolaire_id', 'exempte_frais', 'paiement_annuel'
        ]));

        try {
            DB::transaction(function() use ($studentData) {
                // Créer l'utilisateur de l'élève
                $utilisateur = Utilisateur::create([
                    'nom' => $studentData['nom'],
                    'prenom' => $studentData['prenom'],
                    'email' => $this->generateStudentEmail($studentData['prenom'], $studentData['nom']),
                    'password' => Hash::make('student123'),
                    'telephone' => $studentData['telephone'] ?? null,
                    'adresse' => $studentData['adresse'],
                    'sexe' => $studentData['sexe'],
                    'date_naissance' => $studentData['date_naissance'],
                    'lieu_naissance' => $studentData['lieu_naissance'],
                    'role' => 'student',
                    'actif' => true,
                ]);

                // Traiter la photo si elle existe
                if (isset($studentData['photo_profil_temp'])) {
                    // Déplacer la photo temporaire vers le dossier final
                    $tempPath = 'temp_photos/' . basename($studentData['photo_profil_temp']);
                    if (Storage::disk('public')->exists($tempPath)) {
                        $finalPath = $this->imageService->resizeAndSaveImage(
                            Storage::disk('public')->path($tempPath),
                            'profile_images',
                            300,
                            300
                        );
                        $utilisateur->update(['photo_profil' => $finalPath]);
                        
                        // Supprimer le fichier temporaire
                        Storage::disk('public')->delete($tempPath);
                    }
                }

                // Récupérer l'année scolaire active
                $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
                
                // Créer l'élève
                $eleve = Eleve::create([
                    'utilisateur_id' => $utilisateur->id,
                    'classe_id' => $studentData['classe_id'],
                    'numero_etudiant' => $studentData['numero_etudiant'],
                    'date_inscription' => $studentData['date_inscription'],
                    'type_inscription' => $studentData['type_inscription'] ?? 'nouvelle',
                    'ecole_origine' => $studentData['ecole_origine'] ?? null,
                    'situation_matrimoniale' => $studentData['situation_matrimoniale'] ?? null,
                    'statut' => $studentData['statut'],
                    'annee_scolaire_id' => $anneeScolaireActive ? $anneeScolaireActive->id : null,
                    'exempte_frais' => $studentData['exempte_frais'] ?? false,
                    'paiement_annuel' => $studentData['paiement_annuel'] ?? false,
                    'actif' => true,
                ]);

                // Gérer le parent
                $parentId = null;
                if ($studentData['parent_type'] === 'existing') {
                    $parentId = $studentData['parent_id'];
                } else {
                    // Créer un nouveau parent
                    $parentUtilisateur = Utilisateur::create([
                        'nom' => $studentData['parent_nom'],
                        'prenom' => $studentData['parent_prenom'],
                        'email' => $studentData['parent_email'] ?: $this->generateParentEmail($studentData['parent_prenom'], $studentData['parent_nom']),
                        'password' => Hash::make('parent123'),
                        'telephone' => $studentData['parent_telephone'] ?? null,
                        'adresse' => $studentData['parent_adresse'] ?? $studentData['adresse'],
                        'role' => 'parent',
                        'actif' => true,
                    ]);

                    $parent = ParentModel::create([
                        'utilisateur_id' => $parentUtilisateur->id,
                    ]);

                    $parentId = $parent->id;
                }

                // Associer le parent à l'élève
                $eleve->parents()->attach($parentId, [
                    'lien_parente' => $studentData['lien_parente'],
                    'autre_lien_parente' => $studentData['autre_lien_parente'] ?? null,
                    'responsable_legal' => $studentData['responsable_legal'] ?? false,
                    'contact_urgence' => $studentData['contact_urgence'] ?? false,
                    'autorise_sortie' => $studentData['autorise_sortie'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Créer automatiquement les frais d'inscription et de scolarité
                $paiementController = new PaiementController();
                $paiementController->creerFraisAutomatiques($eleve);
            });

            // Nettoyer la session
            session()->forget(['current_step', 'student_data']);

            return redirect()->route('eleves.index')
                ->with('success', 'Élève inscrit avec succès ! Matricule: ' . $studentData['numero_etudiant']);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'inscription de l\'élève: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'inscription: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Revenir à l'étape précédente
     */
    public function previousStep()
    {
        $currentStep = session('current_step', 1);
        
        if ($currentStep > 1) {
            session(['current_step' => $currentStep - 1]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Générer un matricule automatiquement
     */
    public function generateMatricule()
    {
        try {
            $matricule = $this->generateNewMatricule();
            return response()->json(['matricule' => $matricule]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la génération du matricule'], 500);
        }
    }

    /**
     * Générer un email pour l'élève
     */
    private function generateStudentEmail($prenom, $nom)
    {
        $base = strtolower($prenom . '.' . $nom);
        $base = str_replace(' ', '.', $base);
        $counter = 1;
        
        do {
            $email = $base . ($counter > 1 ? $counter : '') . '@student.local';
            $counter++;
        } while (Utilisateur::where('email', $email)->exists());
        
        return $email;
    }

    /**
     * Générer un email pour le parent
     */
    private function generateParentEmail($prenom, $nom)
    {
        $base = strtolower($prenom . '.' . $nom);
        $base = str_replace(' ', '.', $base);
        $counter = 1;
        
        do {
            $email = $base . ($counter > 1 ? $counter : '') . '@parent.local';
            $counter++;
        } while (Utilisateur::where('email', $email)->exists());
        
        return $email;
    }

    /**
     * Afficher la page de réinscription
     */
    public function showReinscription()
    {
        // Récupérer l'année scolaire active
        $anneeScolaireActive = AnneeScolaire::where('active', true)->first();
        
        // Récupérer toutes les années scolaires sauf l'active pour le filtre
        $anneesPassees = AnneeScolaire::where('active', false)
            ->orderBy('date_debut', 'desc')
            ->get();
        
        // Récupérer les élèves des années passées (non inscrits cette année)
        $elevesPassees = $this->getElevesDesAnneesPassees($anneeScolaireActive);
        
        // Récupérer les classes actives pour la réinscription
        $classes = Classe::actif()->get();
        
        return view('eleves.reinscription', compact(
            'elevesPassees', 
            'anneesPassees', 
            'anneeScolaireActive',
            'classes'
        ));
    }
    
    /**
     * Traiter la réinscription des élèves sélectionnés
     */
    public function processReinscription(Request $request)
    {
        $request->validate([
            'eleves_ids' => 'required|array|min:1',
            'eleves_ids.*' => 'exists:eleves,id',
            'nouvelle_classe' => 'nullable|exists:classes,id',
        ]);
        
        $anneeScolaireActive = AnneeScolaire::where('active', true)->first();
        
        if (!$anneeScolaireActive) {
            return redirect()->back()->with('error', 'Aucune année scolaire active trouvée.');
        }
        
        $elevesReinscris = 0;
        $erreurs = [];
        
        DB::transaction(function() use ($request, $anneeScolaireActive, &$elevesReinscris, &$erreurs) {
            foreach ($request->eleves_ids as $eleveId) {
                try {
                    $ancienEleve = Eleve::with(['utilisateur', 'parents.utilisateur', 'classe'])
                        ->findOrFail($eleveId);
                    
                    // Vérifier si l'élève n'est pas déjà inscrit cette année
                    $dejaInscrit = Eleve::where('utilisateur_id', $ancienEleve->utilisateur_id)
                        ->where('annee_scolaire_id', $anneeScolaireActive->id)
                        ->exists();
                    
                    if ($dejaInscrit) {
                        $erreurs[] = "L'élève {$ancienEleve->utilisateur->nom} {$ancienEleve->utilisateur->prenom} est déjà inscrit cette année.";
                        continue;
                    }
                    
                    // Déterminer la nouvelle classe
                    $nouvelleClasseId = $request->nouvelle_classe ?: $ancienEleve->classe_id;
                    
                    // Créer la nouvelle inscription
                    $nouvelEleve = Eleve::create([
                        'utilisateur_id' => $ancienEleve->utilisateur_id,
                        'classe_id' => $nouvelleClasseId,
                        'numero_etudiant' => $this->generateNewMatricule(),
                        'date_inscription' => now(),
                        'type_inscription' => 'reinscription',
                        'ecole_origine' => null,
                        'statut' => 'en_cours', // Statut actif pour les élèves réinscrits 2024-2025
                        'annee_scolaire_id' => $anneeScolaireActive->id,
                        'situation_matrimoniale' => $ancienEleve->situation_matrimoniale,
                        'exempte_frais' => $ancienEleve->exempte_frais,
                        'paiement_annuel' => $ancienEleve->paiement_annuel,
                        'actif' => true,
                    ]);
                    
                    // Copier les relations parent-élève
                    foreach ($ancienEleve->parents as $parent) {
                        $nouvelEleve->parents()->attach($parent->id, [
                            'lien_parente' => $parent->pivot->lien_parente,
                            'autre_lien_parente' => $parent->pivot->autre_lien_parente,
                            'responsable_legal' => $parent->pivot->responsable_legal,
                            'contact_urgence' => $parent->pivot->contact_urgence,
                            'autorise_sortie' => $parent->pivot->autorise_sortie,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    
                    // Créer automatiquement les frais de réinscription
                    $paiementController = new PaiementController();
                    $paiementController->creerFraisAutomatiques($nouvelEleve);
                    
                    $elevesReinscris++;
                    
                } catch (\Exception $e) {
                    $erreurs[] = "Erreur lors de la réinscription de l'élève ID {$eleveId}: " . $e->getMessage();
                }
            }
        });
        
        $message = "{$elevesReinscris} élève(s) réinscrit(s) avec succès.";
        if (!empty($erreurs)) {
            $message .= " Erreurs: " . implode(', ', $erreurs);
        }
        
        return redirect()->route('eleves.reinscription')
            ->with($elevesReinscris > 0 ? 'success' : 'error', $message);
    }
    
    /**
     * Récupérer les élèves des années passées non inscrits cette année
     */
    private function getElevesDesAnneesPassees($anneeScolaireActive)
    {
        if (!$anneeScolaireActive) {
            return collect();
        }
        
        // Récupérer tous les utilisateurs d'élèves inscrits cette année
        $utilisateursInscritsThisYear = Eleve::where('annee_scolaire_id', $anneeScolaireActive->id)
            ->pluck('utilisateur_id')
            ->toArray();
        
        // Récupérer SEULEMENT les élèves des années passées qui ne sont pas inscrits cette année
        // Exclure les élèves sans année scolaire (ils ne sont pas des anciens élèves)
        return Eleve::with([
                'utilisateur', 
                'classe', 
                'anneeScolaire',
                'parents' => function($query) {
                    $query->with('utilisateur');
                }
            ])
            ->where('annee_scolaire_id', '!=', $anneeScolaireActive->id)
            ->whereNotNull('annee_scolaire_id') // Seulement les élèves avec une année scolaire assignée
            ->whereNotIn('utilisateur_id', $utilisateursInscritsThisYear)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy('utilisateur_id')
            ->map(function($eleves) {
                // Prendre l'inscription la plus récente pour chaque utilisateur
                return $eleves->sortByDesc('updated_at')->first();
            })
            ->values();
    }
    
    /**
     * Générer un nouveau matricule pour la réinscription
     */
    private function generateNewMatricule()
    {
        $school = \App\Helpers\SchoolHelper::getSchoolInfo();
        
        if (!$school) {
            $prefixe = date('Y');
            $suffixe = '';
        } else {
            $prefixe = $school->prefixe_matricule ?: date('Y');
            $suffixe = $school->suffixe_matricule ?: ''; // Pas de suffixe par défaut
        }
        
        // Trouver le prochain numéro disponible
        // Chercher les matricules avec le préfixe actuel (nouveau format)
        $pattern = $prefixe . '%';
        $lastMatricule = \App\Models\Eleve::where('numero_etudiant', 'LIKE', $pattern)
            ->where('numero_etudiant', 'NOT LIKE', '%STD%') // Exclure l'ancien format
            ->orderBy('numero_etudiant', 'desc')
            ->first();
        
        if ($lastMatricule) {
            // Extraire le numéro du dernier matricule
            $matriculeWithoutSuffix = $suffixe ? str_replace($suffixe, '', $lastMatricule->numero_etudiant) : $lastMatricule->numero_etudiant;
            $lastNumber = (int) str_replace($prefixe, '', $matriculeWithoutSuffix);
            $nextNumber = $lastNumber + 1;
        } else {
            // Commencer à 1 si aucun matricule n'existe avec ce format
            $nextNumber = 1;
        }
        
        // Générer un matricule unique
        $attempts = 0;
        do {
            $numero = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $matricule = $prefixe . $numero . $suffixe;
            
            if (!\App\Models\Eleve::where('numero_etudiant', $matricule)->exists()) {
                return $matricule;
            }
            
            $nextNumber++;
            $attempts++;
            
            // Sécurité pour éviter une boucle infinie
            if ($attempts > 1000) {
                $matricule = $prefixe . $numero . uniqid();
                break;
            }
            
        } while (true);
        
        return $matricule;
    }

    /**
     * Gérer le formulaire final unifié
     */
    private function handleFinalForm(Request $request)
    {
        // Validation complète du formulaire
        $rules = [
            // Données élève
            'numero_etudiant' => 'required|string|unique:eleves,numero_etudiant',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'nullable|string|max:20',
            'situation_matrimoniale' => 'nullable|in:celibataire,marie,divorce,veuf',
            'photo_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Données inscription
            'date_inscription' => 'required|date',
            'type_inscription' => 'required|in:nouvelle,reinscription,transfert',
            'ecole_origine' => 'nullable|string|max:255',
            'statut' => 'required|in:inscrit,en_cours',
            'classe_id' => 'required|exists:classes,id',
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id',
            'exempte_frais' => 'boolean',
            'paiement_annuel' => 'boolean',
            
            // Données parent
            'parent_type' => 'required|in:existing,new',
            'lien_parente' => 'required|in:pere,mere,tuteur,tutrice,grand_pere,grand_mere,oncle,tante,autre',
            'responsable_legal' => 'boolean',
            'contact_urgence' => 'boolean',
            'autorise_sortie' => 'boolean',
        ];

        if ($request->input('parent_type') === 'existing') {
            $rules['parent_id'] = 'required|exists:parents,id';
            // Pour un parent existant, les autres champs parent ne sont pas requis
        } else {
            $rules['parent_prenom'] = 'required|string|max:255';
            $rules['parent_nom'] = 'required|string|max:255';
            $rules['parent_telephone'] = 'nullable|string|max:20';
            $rules['parent_email'] = 'nullable|email|unique:utilisateurs,email';
            $rules['parent_adresse'] = 'nullable|string';
        }

        if ($request->input('lien_parente') === 'autre') {
            $rules['autre_lien_parente'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // Debug pour voir les données reçues
        \Log::info('Données reçues pour inscription:', [
            'parent_type' => $request->input('parent_type'),
            'parent_id' => $request->input('parent_id'),
            'lien_parente' => $request->input('lien_parente'),
            'parent_prenom' => $request->input('parent_prenom'),
            'parent_nom' => $request->input('parent_nom'),
        ]);

        try {
            DB::transaction(function() use ($request) {
                // Créer l'utilisateur de l'élève
                $utilisateur = Utilisateur::create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'email' => $this->generateStudentEmail($request->prenom, $request->nom),
                    'password' => Hash::make('student123'),
                    'telephone' => $request->telephone,
                    'adresse' => $request->adresse,
                    'sexe' => $request->sexe,
                    'date_naissance' => $request->date_naissance,
                    'lieu_naissance' => $request->lieu_naissance,
                    'role' => 'student',
                    'actif' => true,
                ]);

                // Traiter la photo si elle existe
                if ($request->hasFile('photo_profil')) {
                    $photoPath = $this->imageService->resizeAndSaveImage(
                        $request->file('photo_profil'),
                        'profile_images',
                        300,
                        300
                    );
                    $utilisateur->update(['photo_profil' => $photoPath]);
                }

                // Récupérer l'année scolaire active
                $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
                
                // Créer l'élève
                $eleve = Eleve::create([
                    'utilisateur_id' => $utilisateur->id,
                    'classe_id' => $request->classe_id,
                    'numero_etudiant' => $request->numero_etudiant,
                    'date_inscription' => $request->date_inscription,
                    'type_inscription' => $request->type_inscription,
                    'ecole_origine' => $request->ecole_origine,
                    'situation_matrimoniale' => $request->situation_matrimoniale,
                    'statut' => $request->statut,
                    'annee_scolaire_id' => $anneeScolaireActive ? $anneeScolaireActive->id : null,
                    'exempte_frais' => $request->boolean('exempte_frais'),
                    'paiement_annuel' => $request->boolean('paiement_annuel'),
                    'actif' => true,
                ]);

                // Gérer le parent
                $parentId = null;
                if ($request->input('parent_type') === 'existing') {
                    $parentId = $request->parent_id;
                } else {
                    // Créer un nouveau parent
                    $parentUtilisateur = Utilisateur::create([
                        'nom' => $request->parent_nom,
                        'prenom' => $request->parent_prenom,
                        'email' => $request->parent_email ?: $this->generateParentEmail($request->parent_prenom, $request->parent_nom),
                        'password' => Hash::make('parent123'),
                        'telephone' => $request->parent_telephone,
                        'adresse' => $request->parent_adresse ?: $request->adresse,
                        'role' => 'parent',
                        'actif' => true,
                    ]);

                    $parent = ParentModel::create([
                        'utilisateur_id' => $parentUtilisateur->id,
                    ]);

                    $parentId = $parent->id;
                }

                // Associer le parent à l'élève
                $eleve->parents()->attach($parentId, [
                    'lien_parente' => $request->lien_parente,
                    'autre_lien_parente' => $request->autre_lien_parente ?? null,
                    'responsable_legal' => $request->responsable_legal ?? false,
                    'contact_urgence' => $request->contact_urgence ?? false,
                    'autorise_sortie' => $request->autorise_sortie ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Créer automatiquement les frais d'inscription et de scolarité
                $paiementController = new PaiementController();
                $paiementController->creerFraisAutomatiques($eleve);
            });

            // Nettoyer la session
            session()->forget(['current_step', 'student_data']);

            return redirect()->route('eleves.index')
                ->with('success', 'Élève inscrit avec succès ! Matricule: ' . $request->numero_etudiant);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'inscription de l\'élève: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'inscription: ' . $e->getMessage())
                ->withInput();
        }
    }
}
