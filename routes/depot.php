<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepotScanController;
use App\Http\Controllers\DepotScanDebugController;

/*
|--------------------------------------------------------------------------
| Depot Scan Routes - AVEC SUPPORT NGROK
|--------------------------------------------------------------------------
|
| Routes pour le système de scan dépôt PC/Téléphone
| Middleware ngrok.cors appliqué pour fonctionner avec ngrok
|
*/

// Appliquer le middleware ngrok.cors à TOUTES les routes depot
Route::middleware(['ngrok.cors'])->group(function () {

// Interface PC - Tableau de bord
Route::get('/depot/scan', [DepotScanController::class, 'dashboard'])
    ->name('depot.scan.dashboard');

// Page d'aide
Route::get('/depot/scan/help', function () {
    return view('depot.scan-help');
})->name('depot.scan.help');

// Interface Téléphone - Scanner
Route::get('/depot/scan/{sessionId}', [DepotScanController::class, 'scanner'])
    ->name('depot.scan.phone')
    ->where('sessionId', '[0-9a-f-]{36}');

// Ajouter un code scanné au cache (temps réel)
Route::post('/depot/scan/{sessionId}/add', [DepotScanController::class, 'addScannedCode'])
    ->name('depot.scan.add')
    ->where('sessionId', '[0-9a-f-]{36}');

// Valider tous les colis depuis PC Dashboard OU Téléphone
Route::post('/depot/scan/{sessionId}/validate-all', [DepotScanController::class, 'validateAllFromPC'])
    ->name('depot.scan.validate.all')
    ->where('sessionId', '[0-9a-f-]{36}');

// Terminer la session (quand PC rafraîchi ou quitté)
Route::post('/depot/scan/{sessionId}/terminate', [DepotScanController::class, 'terminateSession'])
    ->name('depot.scan.terminate')
    ->where('sessionId', '[0-9a-f-]{36}');

// API Routes (gardées pour compatibilité dashboard)
Route::prefix('depot/api')->group(function () {
    
    // Statut de la session
    Route::get('/session/{sessionId}/status', [DepotScanController::class, 'getSessionStatus'])
        ->name('depot.api.session.status');
    
    // Scanner un colis
    Route::post('/session/{sessionId}/scan', [DepotScanController::class, 'scanPackage'])
        ->name('depot.api.scan.package');
    
    // Liste des colis scannés
    Route::get('/session/{sessionId}/packages', [DepotScanController::class, 'getScannedPackages'])
        ->name('depot.api.scanned.packages');
    
});

// Export
Route::get('/depot/scan/{sessionId}/export', [DepotScanController::class, 'exportScan'])
    ->name('depot.scan.export');

// ==================== ROUTES DE DEBUG (À SUPPRIMER EN PRODUCTION) ====================
Route::prefix('depot/debug')->group(function () {
    // Voir les colis disponibles
    Route::get('/packages', [DepotScanDebugController::class, 'debugPackages'])
        ->name('depot.debug.packages');
    
    // Tester la recherche d'un code
    Route::get('/test-search', [DepotScanDebugController::class, 'testSearch'])
        ->name('depot.debug.search');
    
    // Créer des colis de test
    Route::post('/create-test-packages', [DepotScanDebugController::class, 'createTestPackages'])
        ->name('depot.debug.create');
});

}); // Fin du groupe middleware ngrok.cors
