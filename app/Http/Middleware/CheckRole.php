<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
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
        
        // Si aucun rôle n'est spécifié, continuer
        if (empty($roles)) {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a l'un des rôles requis
        foreach ($roles as $role) {
            if ($user->role === $role || $user->hasRole($role)) {
                return $next($request);
            }
        }
        
        if ($request->expectsJson()) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.'
             ], 403);
         }
 
         return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
    }
}