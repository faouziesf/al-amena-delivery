<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NgrokCorsMiddleware
{
    /**
     * Handle an incoming request - Optimisé pour ngrok
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Gérer preflight OPTIONS AVANT traitement
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN, X-Requested-With, Authorization, Accept')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        // Traiter la requête
        $response = $next($request);

        // Headers CORS pour toutes les réponses
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN, X-Requested-With, Authorization, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}
