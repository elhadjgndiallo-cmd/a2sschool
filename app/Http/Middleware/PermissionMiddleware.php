<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Les administrateurs ont toutes les permissions
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Vérifier la permission pour le personnel d'administration
        if ($user->role === 'personnel_admin') {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }
        
        // Permission refusée
        return redirect()->back()->with('error', 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
    }
}
