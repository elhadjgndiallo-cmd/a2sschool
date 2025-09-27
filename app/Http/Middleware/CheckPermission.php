<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non authentifié'
                ], 401);
            }
            return redirect('login');
        }

        $user = Auth::user();
        
        // L'administrateur principal a toutes les permissions
        if ($user->role === 'admin' && $user->email === 'admin@gmail.com') {
            return $next($request);
        }
        
        // Les administrateurs ont toutes les permissions
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a la permission requise
        if ($user->role === 'personnel_admin' && $user->personnelAdministration) {
            if ($user->personnelAdministration->hasPermission($permission)) {
                return $next($request);
            }
        }
        
        // Debug: Log pour voir qui essaie d'accéder
        \Log::info('Permission refusée', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_email' => $user->email,
            'permission_required' => $permission,
            'has_permission' => $user->hasPermission($permission)
        ]);
        
        // Debug: Afficher un message d'erreur détaillé
        $errorMessage = 'PERMISSION REFUSÉE - Debug Info:<br>';
        $errorMessage .= '<strong>Utilisateur:</strong> ' . $user->email . '<br>';
        $errorMessage .= '<strong>Rôle:</strong> ' . $user->role . '<br>';
        $errorMessage .= '<strong>Permission requise:</strong> ' . $permission . '<br>';
        $errorMessage .= '<strong>A la permission:</strong> ' . ($user->hasPermission($permission) ? 'OUI' : 'NON') . '<br>';
        $errorMessage .= '<strong>Est admin:</strong> ' . ($user->role === 'admin' ? 'OUI' : 'NON') . '<br>';
        $errorMessage .= '<strong>Est personnel_admin:</strong> ' . ($user->role === 'personnel_admin' ? 'OUI' : 'NON');
        
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.'
            ], 403);
        }

        return redirect()->back()->with('error', $errorMessage);
    }
}
