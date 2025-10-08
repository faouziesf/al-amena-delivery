<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepotScanController;

/*
|--------------------------------------------------------------------------
| Depot Scan Routes
|--------------------------------------------------------------------------
|
| Routes pour le système de scan dépôt PC/Téléphone
|
*/

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

// Soumettre les scans - MÉTHODE DIRECTE
Route::post('/depot/scan/{sessionId}/submit', [DepotScanController::class, 'submitScans'])
    ->name('depot.scan.submit')
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
