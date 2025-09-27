<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Etablissement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSetupController extends Controller
{
    /**
     * Afficher la page de configuration initiale
     */
    public function index()
    {
        // Vérifier si un admin existe déjà
        $adminExists = Utilisateur::where('role', 'admin')
            ->orWhere('role', 'personnel_admin')
            ->exists();

        if ($adminExists) {
            return redirect()->route('login')->with('info', 'L\'administrateur principal existe déjà.');
        }

        return view('admin.setup');
    }

    /**
     * Créer le compte administrateur principal
     */
    public function store(Request $request)
    {
        // Vérifier si un admin existe déjà
        $adminExists = Utilisateur::where('role', 'admin')
            ->orWhere('role', 'personnel_admin')
            ->exists();

        if ($adminExists) {
            return redirect()->route('login')->with('error', 'L\'administrateur principal existe déjà.');
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'nom_etablissement' => 'required|string|max:255',
            'adresse_etablissement' => 'required|string|max:500',
            'telephone_etablissement' => 'nullable|string|max:20',
            'email_etablissement' => 'nullable|email|max:255',
            'slogan_etablissement' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Créer l'établissement
            $etablissement = Etablissement::create([
                'nom' => $request->nom_etablissement,
                'adresse' => $request->adresse_etablissement,
                'telephone' => $request->telephone_etablissement,
                'email' => $request->email_etablissement,
                'slogan' => $request->slogan_etablissement,
                'statut_etablissement' => 'prive',
                'actif' => true,
            ]);

            // Créer l'utilisateur administrateur
            $utilisateur = Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'actif' => true,
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Compte administrateur principal créé avec succès. Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création du compte administrateur: ' . $e->getMessage())->withInput();
        }
    }
}