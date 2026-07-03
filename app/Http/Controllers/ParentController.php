<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    /**
     * Afficher la liste de tous les parents
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        // Charger les relations avec filtrage des élèves par année active
        $query = ParentModel::with(['utilisateur']);
        
        // Charger uniquement les élèves de l'année scolaire active
        if ($anneeScolaireActive) {
            $query->with(['eleves' => function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id)
                  ->with(['utilisateur', 'classe']);
            }]);
        }

        // Filtrer uniquement les parents ayant des élèves dans l'année scolaire active
        if ($anneeScolaireActive) {
            $query->whereHas('eleves', function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id);
            });
        }

        // Recherche par nom, prénom, téléphone ou email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('utilisateur', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par profession
        if ($request->filled('profession')) {
            $query->where('profession', 'like', "%{$request->profession}%");
        }

        // Filtre par lien de parenté
        if ($request->filled('lien_parente')) {
            $query->where('lien_parente', $request->lien_parente);
        }

        // Filtre par statut actif
        if ($request->filled('actif')) {
            $query->where('actif', $request->actif == '1');
        }

        // Tri par défaut par date de création
        $query->orderBy('created_at', 'desc');

        // Pagination
        $parents = $query->paginate(20)->withQueryString();

        // Statistiques - uniquement pour l'année active
        if ($anneeScolaireActive) {
            $stats = [
                'total' => ParentModel::whereHas('eleves', function($q) use ($anneeScolaireActive) {
                    $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                })->count(),
                'actifs' => ParentModel::where('actif', true)
                    ->whereHas('eleves', function($q) use ($anneeScolaireActive) {
                        $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                    })->count(),
                'inactifs' => ParentModel::where('actif', false)
                    ->whereHas('eleves', function($q) use ($anneeScolaireActive) {
                        $q->where('annee_scolaire_id', $anneeScolaireActive->id);
                    })->count(),
            ];
        } else {
            $stats = [
                'total' => 0,
                'actifs' => 0,
                'inactifs' => 0,
            ];
        }

        return view('parents.index', compact('parents', 'stats'));
    }

    /**
     * Afficher le formulaire de création d'un nouveau parent
     */
    public function create()
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        // Récupérer les élèves de l'année active pour la sélection
        $eleves = \App\Models\Eleve::with(['utilisateur', 'classe'])
            ->where('annee_scolaire_id', $anneeScolaireActive->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('parents.create', compact('eleves'));
    }

    /**
     * Enregistrer un nouveau parent
     */
    public function store(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.create')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:utilisateurs,email',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'sexe' => 'required|in:M,F',
            'eleves_ids' => 'nullable|array',
            'eleves_ids.*' => 'exists:eleves,id',
        ]);

        try {
            \DB::beginTransaction();

            // Générer un email unique si non fourni
            $email = $request->email;
            if (empty($email)) {
                $email = $this->generateUniqueEmail($request->prenom, $request->nom);
            }

            // Générer un mot de passe temporaire
            $password = 'parent' . rand(1000, 9999);

            // Créer l'utilisateur
            $utilisateur = \App\Models\Utilisateur::create([
                'name' => $request->prenom . ' ' . $request->nom,
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'email' => $email,
                'password' => \Hash::make($password),
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'date_naissance' => $request->date_naissance,
                'sexe' => $request->sexe,
                'role' => 'parent',
                'actif' => true,
            ]);

            // Créer le parent
            $parent = ParentModel::create([
                'utilisateur_id' => $utilisateur->id,
                'profession' => null,
                'employeur' => null,
                'telephone_travail' => null,
                'lien_parente' => 'tuteur', // Valeur par défaut
                'autre_lien_parente' => null,
                'contact_urgence' => false,
                'actif' => true,
            ]);

            // Lier le parent aux élèves sélectionnés
            if ($request->has('eleves_ids') && !empty($request->eleves_ids)) {
                foreach ($request->eleves_ids as $eleveId) {
                    $parent->eleves()->attach($eleveId, [
                        'lien_parente' => 'tuteur', // Valeur par défaut
                        'autre_lien_parente' => null,
                        'responsable_legal' => true,
                        'contact_urgence' => true,
                        'autorise_sortie' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            \DB::commit();

            return redirect()->route('parents.show', $parent->id)
                ->with('success', "Parent créé avec succès. Email: {$email}, Mot de passe temporaire: {$password}");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du parent : ' . $e->getMessage());
        }
    }

    /**
     * Générer un email unique pour le parent
     */
    private function generateUniqueEmail($prenom, $nom)
    {
        $base = strtolower(substr($prenom, 0, 1) . $nom);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        $counter = 1;
        $email = $base . '@parent.com';
        
        while (\App\Models\Utilisateur::where('email', $email)->exists()) {
            $email = $base . $counter . '@parent.com';
            $counter++;
        }
        
        return $email;
    }

    /**
     * Afficher les détails d'un parent
     */
    public function show($id)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.view')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        // Récupérer l'année scolaire active
        $anneeScolaireActive = \App\Models\AnneeScolaire::where('active', true)->first();
        
        // Charger le parent avec uniquement les élèves de l'année active
        $parent = ParentModel::with(['utilisateur']);
        
        if ($anneeScolaireActive) {
            $parent->with(['eleves' => function($q) use ($anneeScolaireActive) {
                $q->where('annee_scolaire_id', $anneeScolaireActive->id)
                  ->with(['utilisateur', 'classe']);
            }]);
        }
        
        $parent = $parent->findOrFail($id);

        return view('parents.show', compact('parent'));
    }

    /**
     * Afficher le formulaire de modification d'un parent
     */
    public function edit($id)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $parent = ParentModel::with(['utilisateur', 'eleves'])->findOrFail($id);

        return view('parents.edit', compact('parent'));
    }

    /**
     * Mettre à jour les informations d'un parent
     */
    public function update(Request $request, $id)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasPermission('eleves.edit')) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
        }

        $parent = ParentModel::with('utilisateur')->findOrFail($id);

        $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
            'profession' => 'nullable|string|max:255',
            'employeur' => 'nullable|string|max:255',
            'telephone_travail' => 'nullable|string|max:20',
            'lien_parente' => 'required|in:pere,mere,tuteur,autre',
            'contact_urgence' => 'boolean',
            'actif' => 'boolean',
        ]);

        // Mettre à jour les informations de l'utilisateur
        $parent->utilisateur->update([
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'date_naissance' => $request->date_naissance,
            'sexe' => $request->sexe,
        ]);

        // Mettre à jour les informations du parent
        $parent->update([
            'profession' => $request->profession,
            'employeur' => $request->employeur,
            'telephone_travail' => $request->telephone_travail,
            'lien_parente' => $request->lien_parente,
            'contact_urgence' => $request->has('contact_urgence') ? true : false,
            'actif' => $request->has('actif') ? true : false,
        ]);

        return redirect()->route('parents.show', $parent->id)
            ->with('success', 'Informations du parent mises à jour avec succès.');
    }
}

