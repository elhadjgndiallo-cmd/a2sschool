<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authentifier un utilisateur et générer un token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Vérifier les identifiants
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $utilisateur = Utilisateur::where('email', $request->email)->first();
        
        // Vérifier si l'utilisateur est actif
        if ($utilisateur->statut !== 'actif') {
            return response()->json([
                'status' => 'error',
                'message' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.'
            ], 403);
        }

        // Générer un token
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        // Charger les relations en fonction du rôle
        switch ($utilisateur->role) {
            case 'enseignant':
                $utilisateur->load('enseignant.matieres', 'enseignant.classes');
                break;
            case 'eleve':
                $utilisateur->load('eleve.classe', 'eleve.parents');
                break;
            case 'parent':
                $utilisateur->load('parent.eleves');
                break;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'utilisateur' => $utilisateur,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Authentification réussie'
        ]);
    }

    /**
     * Déconnecter un utilisateur (révoquer le token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $utilisateur = $request->user();
        
        // Charger les relations en fonction du rôle
        switch ($utilisateur->role) {
            case 'enseignant':
                $utilisateur->load('enseignant.matieres', 'enseignant.classes');
                break;
            case 'eleve':
                $utilisateur->load('eleve.classe', 'eleve.parents');
                break;
            case 'parent':
                $utilisateur->load('parent.eleves');
                break;
        }

        return response()->json([
            'status' => 'success',
            'data' => $utilisateur,
            'message' => 'Informations de l\'utilisateur récupérées avec succès'
        ]);
    }

    /**
     * Changer le mot de passe de l'utilisateur connecté
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $utilisateur = $request->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $utilisateur->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Le mot de passe actuel est incorrect'
            ], 400);
        }

        // Mettre à jour le mot de passe
        $utilisateur->password = Hash::make($request->password);
        $utilisateur->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    }

    /**
     * Demander la réinitialisation du mot de passe
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:utilisateurs,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        $utilisateur = Utilisateur::where('email', $request->email)->first();
        
        // Générer un token de réinitialisation
        $token = Str::random(60);
        
        // Stocker le token dans la base de données
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $utilisateur->email],
            [
                'email' => $utilisateur->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );
        
        // Envoyer un email avec le lien de réinitialisation
        // Dans une application réelle, vous enverriez un email ici
        // Mail::to($utilisateur->email)->send(new ResetPasswordMail($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email'
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:utilisateurs,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation'
            ], 422);
        }

        // Vérifier le token
        $resetRecord = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide ou expiré'
            ], 400);
        }

        // Vérifier si le token n'est pas expiré (24 heures)
        if (now()->diffInHours(\Carbon\Carbon::parse($resetRecord->created_at)) > 24) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 400);
        }

        // Mettre à jour le mot de passe
        $utilisateur = Utilisateur::where('email', $request->email)->first();
        $utilisateur->password = Hash::make($request->password);
        $utilisateur->save();

        // Supprimer le token
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }
}