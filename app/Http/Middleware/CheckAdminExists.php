<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Utilisateur;

class CheckAdminExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si au moins un administrateur existe
        $adminExists = Utilisateur::where('role', 'admin')
            ->orWhere('role', 'personnel_admin')
            ->exists();

        // Si aucun admin n'existe et qu'on n'est pas déjà sur la page de setup
        if (!$adminExists && !$request->is('setup*') && !$request->is('admin/setup*')) {
            return redirect()->route('admin.setup');
        }

        return $next($request);
    }
}