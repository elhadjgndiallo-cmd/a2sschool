<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            \Log::warning('Utilisateur non authentifié', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expirée. Veuillez vous reconnecter.'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }
        
        // Vérifier que l'utilisateur est toujours valide
        $user = Auth::user();
        if (!$user || !$user->exists) {
            \Log::warning('Utilisateur invalide', [
                'user_id' => $user ? $user->id : 'NULL',
                'url' => $request->url()
            ]);
            
            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur invalide. Veuillez vous reconnecter.'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Utilisateur invalide. Veuillez vous reconnecter.');
        }
        
        return $next($request);
    }
}
