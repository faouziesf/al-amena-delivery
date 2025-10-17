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

// ==================== ICÔNES PWA (GÉNÉRATION DYNAMIQUE) ====================
Route::get('/icon-192.png', function () {
    $img = imagecreatetruecolor(192, 192);
    $bgColor = imagecolorallocate($img, 139, 92, 246);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, 192, 192, $bgColor);
    imagefilledrectangle($img, 40, 50, 55, 140, $white);
    imagefilledrectangle($img, 40, 50, 75, 65, $white);
    imagefilledrectangle($img, 60, 50, 75, 140, $white);
    imagefilledrectangle($img, 45, 85, 70, 100, $bgColor);
    imagefilledrectangle($img, 117, 50, 132, 140, $white);
    imagefilledrectangle($img, 117, 50, 152, 65, $white);
    imagefilledrectangle($img, 137, 50, 152, 140, $white);
    imagefilledrectangle($img, 122, 85, 147, 100, $bgColor);
    ob_start();
    imagepng($img);
    $imageData = ob_get_clean();
    imagedestroy($img);
    return response($imageData)->header('Content-Type', 'image/png')->header('Cache-Control', 'public, max-age=31536000');
});

Route::get('/icon-512.png', function () {
    $img = imagecreatetruecolor(512, 512);
    $bgColor = imagecolorallocate($img, 139, 92, 246);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, 512, 512, $bgColor);
    imagefilledrectangle($img, 100, 130, 130, 380, $white);
    imagefilledrectangle($img, 100, 130, 200, 170, $white);
    imagefilledrectangle($img, 170, 130, 200, 380, $white);
    imagefilledrectangle($img, 115, 230, 185, 280, $bgColor);
    imagefilledrectangle($img, 312, 130, 342, 380, $white);
    imagefilledrectangle($img, 312, 130, 412, 170, $white);
    imagefilledrectangle($img, 382, 130, 412, 380, $white);
    imagefilledrectangle($img, 327, 230, 397, 280, $bgColor);
    ob_start();
    imagepng($img);
    $imageData = ob_get_clean();
    imagedestroy($img);
    return response($imageData)->header('Content-Type', 'image/png')->header('Cache-Control', 'public, max-age=31536000');
});

// ==================== ROUTE TEMPORAIRE POUR TESTER LE REÇU ====================
Route::get('/test-receipt/{id}', function($id) {
    try {
        $package = \App\Models\Package::findOrFail($id);

        $recipientData = is_string($package->recipient_data)
            ? json_decode($package->recipient_data, true)
            : $package->recipient_data;

        $senderData = is_string($package->sender_data)
            ? json_decode($package->sender_data, true)
            : $package->sender_data;

        return view('deliverer.receipts.delivery-receipt', compact('package', 'recipientData', 'senderData'));
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
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
        case 'DEPOT_MANAGER':
            return redirect()->route('depot-manager.dashboard');
        case 'SUPERVISOR':
            return redirect()->route('supervisor.dashboard');
        default:
            return redirect()->route('login')->with('error', 'Type de compte non reconnu ou désactivé.');
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

// Routes spécifiques aux chefs dépôt
require __DIR__.'/depot-manager.php';

// Routes système de scan dépôt
require __DIR__.'/depot.php';

// TRANSIT_DRIVER routes removed - account type deprecated

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

        // Test scanner - PUBLIC pour debug
        Route::get('/test-scanner-debug', function() {
            return view('test-scanner');
        })->name('test.scanner.debug');

        // Test endpoint scan GET - PUBLIC pour debug (TEMPORAIRE)
        Route::get('/test-scan/{code}', function($code) {
            try {
                if (empty($code)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Code requis'
                    ], 400);
                }

                // Chercher le colis sans validation
                $package = \App\Models\Package::where('package_code', $code)->first();

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Colis non trouvé avec le code: ' . $code
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Colis trouvé!',
                    'package' => [
                        'code' => $package->package_code,
                        'status' => $package->status,
                        'cod_amount' => $package->cod_amount,
                        'formatted_cod' => $package->cod_amount . ' DA'
                    ],
                    'delivery_info' => [
                        'name' => $package->receiver_name ?? 'N/A',
                        'address' => $package->receiver_address ?? 'N/A'
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }
        })->name('test.scan.get');

        // Test endpoint scan - PUBLIC pour debug (TEMPORAIRE)
        Route::post('/test-scan', function(Illuminate\Http\Request $request) {
            try {
                $code = $request->input('code');

                if (empty($code)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Code requis'
                    ], 400);
                }

                // Chercher le colis sans validation
                $package = \App\Models\Package::where('package_code', $code)->first();

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Colis non trouvé avec le code: ' . $code
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Colis trouvé!',
                    'package' => [
                        'code' => $package->package_code,
                        'status' => $package->status,
                        'cod_amount' => $package->cod_amount,
                        'formatted_cod' => $package->cod_amount . ' DA'
                    ],
                    'delivery_info' => [
                        'name' => $package->receiver_name ?? 'N/A',
                        'address' => $package->receiver_address ?? 'N/A'
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }
        })->name('test.scan');
    });
}

// ==================== GESTION D'ERREURS PERSONNALISÉES ====================

// 404 personnalisé pour les routes inexistantes
Route::fallback(function () {
    // Check if user is authenticated
    if (auth()->check()) {
        // User is logged in but accessing invalid route - redirect to dashboard with error
        $errorMessage = 'La page demandée n\'existe pas.';
        
        // Add debug details if debug mode is enabled
        if (config('app.debug')) {
            $errorMessage .= ' (Route: ' . request()->path() . ')';
        }
        
        // Redirect to appropriate dashboard based on role
        $user = auth()->user();
        $dashboardRoute = match($user->role) {
            'CLIENT' => 'client.dashboard',
            'DELIVERER' => 'deliverer.dashboard',
            'COMMERCIAL' => 'commercial.dashboard',
            'SUPERVISOR' => 'supervisor.dashboard',
            'DEPOT_MANAGER' => 'depot-manager.dashboard',
            default => 'login'
        };
        
        return redirect()->route($dashboardRoute)->with('error', $errorMessage);
    }
    
    // User not authenticated - redirect to login
    if (!request()->expectsJson()) {
        return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
    }
    
    // For API requests, return JSON error
    return response()->json([
        'error' => 'Route non trouvée',
        'message' => 'L\'endpoint demandé n\'existe pas'
    ], 404);
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