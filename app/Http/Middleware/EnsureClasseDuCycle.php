<?php

namespace App\Http\Middleware;

use App\Models\Classe;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClasseDuCycle
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user?->estLimiteParCycle()) {
            return $next($request);
        }

        $classe = $request->route('classe');
        if (!$classe instanceof Classe) {
            $id = $classe ?? $request->route('classeId') ?? $request->input('classe_id');
            if ($id) {
                $classe = Classe::find($id);
            }
        }

        if ($classe instanceof Classe) {
            $user->abortSiHorsCycle($classe);
        }

        return $next($request);
    }
}
