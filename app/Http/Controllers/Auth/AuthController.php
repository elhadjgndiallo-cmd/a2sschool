<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $credentials['actif'] = true; // S'assurer que l'utilisateur est actif

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Rediriger selon le rôle
            return $this->redirectBasedOnRole(Auth::user());
        }

        throw ValidationException::withMessages([
            'email' => ['Les informations de connexion sont incorrectes.'],
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        try {
            // Logger la tentative de déconnexion
            \Log::info('Tentative de déconnexion pour l\'utilisateur: ' . auth()->user()->email);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            \Log::info('Déconnexion réussie');
            return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la déconnexion: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la déconnexion. Veuillez réessayer.');
        }
    }

    /**
     * Rediriger l'utilisateur vers l'accueil pour tous les rôles
     */
private function redirectBasedOnRole($user)
    {
        // Tous les utilisateurs sont redirigés vers la page d'accueil
        return redirect()->route('dashboard');
    }
}
