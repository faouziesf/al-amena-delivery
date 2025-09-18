<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDelivererRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            return redirect()->route('login');
        }

        // Vérifier le rôle livreur
        if ($user->role !== 'DELIVERER') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Accès réservé aux livreurs'], 403);
            }
            
            abort(403, 'Accès réservé aux livreurs.');
        }

        // Vérifier que le compte est actif
        if ($user->account_status !== 'ACTIVE') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Compte livreur inactif',
                    'status' => $user->account_status
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Votre compte livreur est ' . strtolower($user->getStatusDisplayAttribute()) . '. Contactez votre superviseur.');
        }

        // S'assurer que le wallet existe
        $user->ensureWallet();

        // Log de l'accès si nécessaire (pour audit)
        if (config('app.log_deliverer_access', false)) {
            \Log::info('Accès livreur', [
                'user_id' => $user->id,
                'name' => $user->name,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $next($request);
    }
}

// ==================== MIDDLEWARE POUR GÉOLOCALISATION ====================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackDelivererLocation
{
    /**
     * Track deliverer location for certain routes
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Enregistrer la position si fournie (pour le suivi temps réel)
        if ($request->has(['latitude', 'longitude']) && Auth::check() && Auth::user()->role === 'DELIVERER') {
            $this->updateDelivererLocation($request);
        }

        return $response;
    }

    private function updateDelivererLocation(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Valider les coordonnées
            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            
            if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
                
                // Enregistrer dans cache (Redis recommandé)
                cache()->put(
                    "deliverer_location_{$user->id}",
                    [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'updated_at' => now(),
                        'accuracy' => $request->input('accuracy', null)
                    ],
                    now()->addMinutes(30) // Expire après 30 minutes
                );

                // Optionnel: Enregistrer en DB pour historique
                if (config('app.track_deliverer_location_history', false)) {
                    \App\Models\DelivererLocation::create([
                        'deliverer_id' => $user->id,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'accuracy' => $request->input('accuracy'),
                        'recorded_at' => now()
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ne pas faire planter la requête si géolocalisation échoue
            \Log::error('Erreur enregistrement géolocalisation livreur', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
        }
    }
}

// ==================== MIDDLEWARE POUR VÉRIFICATIONS PACKAGE ====================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;

class EnsurePackageAccess
{
    /**
     * Vérifier l'accès à un package spécifique
     */
    public function handle(Request $request, Closure $next, $accessType = 'view')
    {
        $package = $request->route('package');
        
        if (!$package instanceof Package) {
            abort(404, 'Colis introuvable');
        }

        $user = Auth::user();
        
        // Vérifications selon le type d'accès
        switch ($accessType) {
            case 'view':
                // Peut voir : colis assigné OU colis disponible
                if ($package->assigned_deliverer_id !== $user->id && $package->status !== 'AVAILABLE') {
                    abort(403, 'Accès non autorisé à ce colis');
                }
                break;
                
            case 'modify':
                // Peut modifier : uniquement colis assigné
                if ($package->assigned_deliverer_id !== $user->id) {
                    abort(403, 'Ce colis ne vous est pas assigné');
                }
                break;
                
            case 'accept':
                // Peut accepter : uniquement colis disponible
                if ($package->status !== 'AVAILABLE') {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ce colis n\'est plus disponible'
                        ], 400);
                    }
                    abort(400, 'Ce colis n\'est plus disponible');
                }
                break;
        }

        return $next($request);
    }
}

// ==================== MIDDLEWARE POUR RATE LIMITING LIVREUR ====================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class DelivererRateLimit
{
    /**
     * Rate limiting spécifique aux livreurs (plus souple que client/commercial)
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 100, $decayMinutes = 1)
    {
        $user = Auth::user();
        $key = 'deliverer_' . $user->id . '_' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Trop de requêtes. Réessayez dans ' . $seconds . ' secondes.',
                    'retry_after' => $seconds
                ], 429);
            }
            
            abort(429, 'Trop de requêtes. Réessayez dans quelques secondes.');
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}

// ==================== ENREGISTREMENT DES MIDDLEWARES ====================

// ==================== UTILISATION DANS LES ROUTES ====================

/*
// Exemples d'utilisation dans les routes

// Route avec middleware de base
Route::middleware(['auth', 'deliverer'])->group(function () {
    Route::get('/deliverer/dashboard', [DelivererDashboardController::class, 'index']);
});

// Route avec suivi géolocalisation
Route::middleware(['auth', 'deliverer', 'track_location'])->group(function () {
    Route::post('/deliverer/packages/{package}/deliver', [DelivererPackageController::class, 'markDelivered']);
});

// Route avec vérification accès package
Route::middleware(['auth', 'deliverer', 'package_access:modify'])->group(function () {
    Route::post('/deliverer/packages/{package}/pickup', [DelivererPackageController::class, 'markPickedUp']);
});

// Route avec rate limiting
Route::middleware(['auth', 'deliverer', 'deliverer_throttle:200,5'])->group(function () {
    Route::post('/deliverer/packages/scan', [DelivererPackageController::class, 'scanPackage']);
});
*/