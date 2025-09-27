<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PersonnelPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();
        
        // Si l'utilisateur est admin, il a toutes les permissions
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }
        
        // Si l'utilisateur est un personnel d'administration
        if ($user && $user->hasRole('personnel_admin') && $user->personnelAdministration) {
            if ($user->personnelAdministration->hasPermission($permission)) {
                return $next($request);
            }
        }
        
        // Si l'utilisateur n'a pas la permission
        return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé, veuillez contacter l\'administrateur.');
    }
}
