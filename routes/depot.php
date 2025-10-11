<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepotScanController;
use App\Http\Controllers\DepotScanDebugController;
use App\Http\Controllers\Depot\DepotReturnScanController;

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

// Interface Téléphone - Saisie manuelle du code de session
Route::get('/depot/enter-code', [DepotScanController::class, 'enterCode'])
    ->name('depot.enter.code');

// Validation du code de session de 8 chiffres (GET pour éviter CSRF avec ngrok)
Route::get('/depot/validate-code/{code}', [DepotScanController::class, 'validateCodeGet'])
    ->name('depot.validate.code.get')
    ->where('code', '[0-9]{8}');

// Validation POST (fallback)
Route::post('/depot/validate-code', [DepotScanController::class, 'validateCode'])
    ->name('depot.validate.code');

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

    // Heartbeat du PC
    Route::post('/session/{sessionId}/heartbeat', [DepotScanController::class, 'heartbeat'])
        ->name('depot.api.session.heartbeat');

    // Vérifier l'activité de la session
    Route::get('/session/{sessionId}/check-activity', [DepotScanController::class, 'checkActivity'])
        ->name('depot.api.session.check-activity');

    // Mettre à jour l'activité de la session
    Route::post('/session/{sessionId}/update-activity', [DepotScanController::class, 'updateActivity'])
        ->name('depot.api.session.update-activity');

});

// Export
Route::get('/depot/scan/{sessionId}/export', [DepotScanController::class, 'exportScan'])
    ->name('depot.scan.export');

// ==================== SYSTÈME DE SCAN RETOURS ====================
// Routes pour le scan et la gestion des colis retours au dépôt

// Interface PC - Dashboard scan retours
Route::get('/depot/returns', [DepotReturnScanController::class, 'dashboard'])
    ->name('depot.returns.dashboard');

// Saisie du nom du gestionnaire
Route::get('/depot/returns/enter-name', [DepotReturnScanController::class, 'enterManagerName'])
    ->name('depot.returns.enter-manager-name');

// Interface Mobile - Scanner retours
Route::get('/depot/returns/phone/{sessionId}', [DepotReturnScanController::class, 'phoneScanner'])
    ->name('depot.returns.phone-scanner');

// Valider et créer les colis retours
Route::post('/depot/returns/{sessionId}/validate', [DepotReturnScanController::class, 'validateAndCreate'])
    ->name('depot.returns.validate')
    ->where('sessionId', '[0-9a-f-]{36}');

// Démarrer une nouvelle session
Route::post('/depot/returns/new-session', [DepotReturnScanController::class, 'startNewSession'])
    ->name('depot.returns.new-session');

// Gestion des colis retours créés
Route::get('/depot/returns/manage', [DepotReturnScanController::class, 'manageReturns'])
    ->name('depot.returns.manage');

// Détails d'un colis retour
Route::get('/depot/returns/package/{returnPackage}', [DepotReturnScanController::class, 'showReturnPackage'])
    ->name('depot.returns.show');

// Imprimer bordereau colis retour
Route::get('/depot/returns/package/{returnPackage}/print', [DepotReturnScanController::class, 'printReturnLabel'])
    ->name('depot.returns.print');

// API Routes pour retours
Route::prefix('depot/returns/api')->name('depot.returns.api.')->group(function () {
    // Scanner un colis retour
    Route::post('/session/{sessionId}/scan', [DepotReturnScanController::class, 'scanPackage'])
        ->name('scan')
        ->where('sessionId', '[0-9a-f-]{36}');

    // Statut de la session
    Route::get('/session/{sessionId}/status', [DepotReturnScanController::class, 'getSessionStatus'])
        ->name('status')
        ->where('sessionId', '[0-9a-f-]{36}');

    // Vérifier l'activité de la session
    Route::get('/session/{sessionId}/check-activity', [DepotReturnScanController::class, 'checkSessionActivity'])
        ->name('check-activity')
        ->where('sessionId', '[0-9a-f-]{36}');
});

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
