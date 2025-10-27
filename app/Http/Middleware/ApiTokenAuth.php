<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token API manquant. Veuillez inclure le header Authorization: Bearer YOUR_TOKEN',
                'error_code' => 'TOKEN_MISSING'
            ], 401);
        }
        
        $apiToken = ApiToken::verify($token);
        
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token API invalide ou expiré',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
        }
        
        // Vérifier expiration
        if ($apiToken->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Token API expiré. Veuillez le régénérer depuis votre espace client',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        }
        
        // Vérifier que l'utilisateur est actif et est un client
        if (!$apiToken->user || $apiToken->user->role !== 'CLIENT') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Ce token n\'appartient pas à un compte client',
                'error_code' => 'UNAUTHORIZED'
            ], 403);
        }
        
        if ($apiToken->user->status !== 'VERIFIED') {
            return response()->json([
                'success' => false,
                'message' => 'Compte client non vérifié. Veuillez contacter le support',
                'error_code' => 'ACCOUNT_NOT_VERIFIED'
            ], 403);
        }
        
        // Authentifier l'utilisateur
        Auth::setUser($apiToken->user);
        
        // Stocker le token dans la requête pour logging
        $request->attributes->set('api_token', $apiToken);
        
        // Mettre à jour la dernière utilisation (de manière asynchrone)
        dispatch(function () use ($apiToken) {
            $apiToken->updateLastUsed();
        })->afterResponse();
        
        return $next($request);
    }
}
