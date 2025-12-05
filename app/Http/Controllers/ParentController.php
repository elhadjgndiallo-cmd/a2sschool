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

        $query = ParentModel::with(['utilisateur', 'eleves.utilisateur', 'eleves.classe']);

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

        // Statistiques
        $stats = [
            'total' => ParentModel::count(),
            'actifs' => ParentModel::where('actif', true)->count(),
            'inactifs' => ParentModel::where('actif', false)->count(),
        ];

        return view('parents.index', compact('parents', 'stats'));
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

        $parent = ParentModel::with(['utilisateur', 'eleves.utilisateur', 'eleves.classe'])->findOrFail($id);

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

