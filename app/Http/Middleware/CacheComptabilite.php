<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheComptabilite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si c'est une requête GET vers la comptabilité et pas de filtres
        if ($request->isMethod('get') && !$request->hasAny(['date_debut', 'date_fin', 'source', 'type_depense'])) {
            $cacheKey = 'comptabilite_' . $request->path() . '_' . auth()->id();
            
            // Mettre en cache pendant 5 minutes
            return Cache::remember($cacheKey, 300, function () use ($request, $next) {
                return $next($request);
            });
        }
        
        return $next($request);
    }
}
