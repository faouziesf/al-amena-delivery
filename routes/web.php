<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Fichier principal des routes web avec redirection selon les rôles
| et inclusion des fichiers de routes spécialisés
|
*/

// ==================== ROUTE RACINE ====================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==================== DASHBOARD PRINCIPAL AVEC REDIRECTION ====================
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Redirection selon le rôle
    switch ($user->role) {
        case 'CLIENT':
            return redirect()->route('client.dashboard');
        case 'DELIVERER':
            return redirect()->route('deliverer.dashboard');
        case 'COMMERCIAL':
            return redirect()->route('commercial.dashboard');
        case 'SUPERVISOR':
            return redirect()->route('supervisor.dashboard');
        default:
            return redirect()->route('login');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// ==================== ROUTES PROFIL UTILISATEUR ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== INCLUSION DES ROUTES SPÉCIALISÉES ====================

// Routes spécifiques aux clients
require __DIR__.'/client.php';

// Routes spécifiques aux livreurs  
require __DIR__.'/deliverer.php';

// Routes spécifiques aux commerciaux (inclut aussi SUPERVISOR pour ces routes)
require __DIR__.'/commercial.php';

// Routes spécifiques aux superviseurs
require __DIR__.'/supervisor.php';

// Routes d'authentification
require __DIR__.'/auth.php';

// ==================== API PUBLIQUE & WEBHOOKS ====================
Route::prefix('api/public')->name('api.public.')->group(function () {
    // Routes API publiques pour intégrations externes (à sécuriser avec API keys)
    
    // Exemple: tracking public d'un colis
    Route::get('/track/{package_code}', function($packageCode) {
        $package = \App\Models\Package::where('package_code', $packageCode)->first();
        
        if (!$package) {
            return response()->json(['error' => 'Colis non trouvé'], 404);
        }
        
        return response()->json([
            'package_code' => $package->package_code,
            'status' => $package->status,
            'current_location' => $package->delegationTo->name ?? null,
            'estimated_delivery' => $package->estimated_delivery_date ?? null,
            'last_update' => $package->updated_at->format('d/m/Y H:i')
        ]);
    })->name('track');
    
    // Webhook pour services externes (à implémenter selon besoins)
    // Route::post('/webhook/payment', [PaymentWebhookController::class, 'handle'])->name('webhook.payment');
});

// ==================== ROUTES DE DÉVELOPPEMENT ====================
if (app()->environment(['local', 'staging'])) {
    
    Route::prefix('dev')->name('dev.')->group(function () {
        
        // Génération de données de test
        Route::get('/seed-demo-data', function() {
            if (!auth()->check() || auth()->user()->role !== 'SUPERVISOR') {
                abort(403, 'Accès non autorisé');
            }
            
            Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
            
            return response()->json([
                'message' => 'Données de démonstration générées avec succès',
                'output' => Artisan::output()
            ]);
        })->name('seed.demo');
        
        // Test d'envoi d'emails
        Route::get('/test-email', function() {
            if (!auth()->check() || auth()->user()->role !== 'SUPERVISOR') {
                abort(403, 'Accès non autorisé');
            }
            
            Mail::raw('Test email depuis l\'application de livraison', function ($message) {
                $message->to(auth()->user()->email)
                        ->subject('Test Email - ' . config('app.name'));
            });
            
            return response()->json(['message' => 'Email de test envoyé']);
        })->name('test.email');
        
        // Informations système
        Route::get('/system-info', function() {
            if (!auth()->check() || auth()->user()->role !== 'SUPERVISOR') {
                abort(403, 'Accès non autorisé');
            }
            
            return response()->json([
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'database' => [
                    'driver' => config('database.default'),
                    'connection' => DB::connection()->getDatabaseName()
                ],
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'mail_driver' => config('mail.default'),
                'timezone' => config('app.timezone'),
                'url' => config('app.url')
            ]);
        })->name('system.info');
    });
}

// ==================== GESTION D'ERREURS PERSONNALISÉES ====================

// 404 personnalisé pour les routes inexistantes
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'error' => 'Route non trouvée',
            'message' => 'L\'endpoint demandé n\'existe pas'
        ], 404);
    }
    
    return response()->view('errors.404', [], 404);
});

/*
|--------------------------------------------------------------------------
| Middleware personnalisés disponibles
|--------------------------------------------------------------------------
|
| 'role:CLIENT' - Accès limité aux clients
| 'role:DELIVERER' - Accès limité aux livreurs  
| 'role:COMMERCIAL' - Accès limité aux commerciaux
| 'role:SUPERVISOR' - Accès limité aux superviseurs
| 'role:COMMERCIAL,SUPERVISOR' - Accès aux commerciaux ET superviseurs
|
| Ces middlewares sont définis dans app/Http/Middleware/RoleMiddleware.php
|
*/