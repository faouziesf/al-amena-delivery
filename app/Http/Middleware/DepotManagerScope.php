<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DepotManagerScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est un chef dépôt
        if ($user && $user->isDepotManager()) {
            // Ajouter les gouvernorats gérés dans la requête pour filtrage
            $request->merge([
                'depot_gouvernorats' => $user->assigned_gouvernorats_array,
                'depot_manager_id' => $user->id
            ]);
        }

        return $next($request);
    }
}
