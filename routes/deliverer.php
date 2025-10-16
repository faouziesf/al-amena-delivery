<?php

use App\Http\Controllers\Deliverer\DelivererController;
use App\Http\Controllers\Deliverer\DelivererActionsController;
use App\Http\Controllers\Deliverer\DelivererClientTopupController;
use App\Http\Controllers\Deliverer\SimpleDelivererController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Deliverer Routes - PWA Refonte Complète
|--------------------------------------------------------------------------
| Routes consolidées pour l'application livreur PWA
| Toutes les routes sont protégées par auth + role:DELIVERER
| Filtrage automatique par gouvernorats assignés au livreur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {

    // ==================== DASHBOARD - REDIRECT TO RUN SHEET ====================
    Route::get('/dashboard', function() {
        return redirect()->route('deliverer.tournee');
    })->name('dashboard');

    // ==================== RUN SHEET UNIFIÉ (Interface Principale) ====================
    Route::get('/tournee', [DelivererController::class, 'runSheetUnified'])->name('tournee');
    
    // ==================== DÉTAIL TÂCHE (Unifié pour tous types) ====================
    Route::get('/task/{package}', [DelivererController::class, 'taskDetail'])->name('task.detail');
    
    // ==================== MENU & NAVIGATION ====================
    Route::get('/menu', [DelivererController::class, 'menu'])->name('menu');
    Route::get('/wallet', [DelivererController::class, 'wallet'])->name('wallet');
    
    // ==================== SCANNER OPTIMISÉ ====================
    Route::get('/scan', [SimpleDelivererController::class, 'scanSimple'])->name('scan.simple');
    Route::post('/scan/submit', [SimpleDelivererController::class, 'processScan'])->name('scan.submit');
    Route::post('/scan/process', [SimpleDelivererController::class, 'processScan'])->name('scan.process');
    Route::get('/scan/multi', [SimpleDelivererController::class, 'scanMulti'])->name('scan.multi');
    Route::post('/scan/multi/process', [SimpleDelivererController::class, 'processMultiScan'])->name('scan.multi.process');
    Route::post('/scan/multi/validate', [SimpleDelivererController::class, 'validateMultiScan'])->name('scan.multi.validate');

    // ==================== SIGNATURE ====================
    Route::get('/signature/{package}', [DelivererActionsController::class, 'signatureCapture'])->name('signature.capture');
    Route::post('/signature/{package}', [DelivererActionsController::class, 'saveSignature'])->name('signature.save');
    
    // ==================== ACTIONS COLIS ====================
    Route::post('/pickup/{package}', [DelivererActionsController::class, 'markPickup'])->name('pickup');
    Route::post('/deliver/{package}', [DelivererActionsController::class, 'markDelivered'])->name('deliver');
    Route::post('/unavailable/{package}', [DelivererActionsController::class, 'markUnavailable'])->name('unavailable');

    // ==================== PICKUPS (Ramassages) ====================
    Route::get('/pickups/available', [SimpleDelivererController::class, 'availablePickups'])->name('pickups.available');
    Route::get('/pickup/{id}', [SimpleDelivererController::class, 'pickupDetail'])->name('pickup.detail');
    Route::post('/pickup/{id}/collect', [DelivererActionsController::class, 'markPickupCollected'])->name('pickup.collect');

    // ==================== RECHARGE CLIENT (CLIENT TOP-UP) ====================
    Route::get('/client-topup', [DelivererClientTopupController::class, 'index'])->name('client-topup.index');
    Route::post('/client-topup/search', [DelivererClientTopupController::class, 'searchClient'])->name('client-topup.search');
    Route::post('/client-topup/add', [DelivererClientTopupController::class, 'addTopup'])->name('client-topup.add');
    Route::get('/client-topup/history', [DelivererClientTopupController::class, 'history'])->name('client-topup.history');

    // ==================== IMPRESSION ====================
    Route::get('/print/run-sheet', [SimpleDelivererController::class, 'printRunSheet'])->name('print.run.sheet');
    Route::get('/print/receipt/{package}', [SimpleDelivererController::class, 'printDeliveryReceipt'])->name('print.receipt');

    // ==================== API ENDPOINTS PWA ====================
    Route::prefix('api')->name('api.')->group(function() {
        
        // Run Sheet & Tasks
        Route::get('/run-sheet', [DelivererController::class, 'apiRunSheet'])->name('run.sheet');
        Route::get('/task/{id}', [DelivererController::class, 'apiTaskDetail'])->name('task.detail');
        Route::get('/tasks', [DelivererController::class, 'apiRunSheet'])->name('tasks');
        
        // Packages
        Route::get('/packages', [SimpleDelivererController::class, 'apiActivePackages'])->name('packages');
        Route::get('/packages/active', [SimpleDelivererController::class, 'apiActivePackages'])->name('packages.active');
        Route::get('/packages/delivered', [SimpleDelivererController::class, 'apiDeliveredPackages'])->name('packages.delivered');
        Route::get('/packages/pending', [SimpleDelivererController::class, 'apiActivePackages'])->name('packages.pending');
        
        // Pickups (avec alias pour compatibilité)
        Route::get('/pickups/available', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('pickups.available');
        Route::get('/available/pickups', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('available.pickups');
        Route::post('/pickups/{pickupRequest}/accept', [SimpleDelivererController::class, 'acceptPickup'])->name('pickups.accept');
        Route::post('/pickups/{pickupRequest}/collected', [DelivererActionsController::class, 'markPickupCollected'])->name('pickups.collected');
        Route::get('/pickups', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('pickups');
        
        // Returns (Retours)
        Route::get('/returns', [DelivererController::class, 'apiReturns'])->name('returns');
        Route::get('/returns/pending', [DelivererController::class, 'apiReturns'])->name('returns.pending');
        
        // Payments (Paiements)
        Route::get('/payments', [DelivererController::class, 'apiPayments'])->name('payments');
        Route::get('/payments/pending', [DelivererController::class, 'apiPayments'])->name('payments.pending');
        
        // Wallet
        Route::get('/wallet', [DelivererController::class, 'apiWalletBalance'])->name('wallet');
        Route::get('/wallet/balance', [DelivererController::class, 'apiWalletBalance'])->name('wallet.balance');
        Route::get('/wallet/transactions', [DelivererController::class, 'apiWalletTransactions'])->name('wallet.transactions');
        
        // Stats
        Route::get('/stats', [DelivererController::class, 'apiStats'])->name('stats');
        Route::get('/stats/today', [DelivererController::class, 'apiStatsToday'])->name('stats.today');
        
        // Client Search & Recharge
        Route::get('/search/client', [DelivererClientTopupController::class, 'searchClient'])->name('search.client');
        Route::post('/recharge/client', [DelivererClientTopupController::class, 'addTopup'])->name('recharge.client');
        Route::get('/clients/search', [DelivererClientTopupController::class, 'searchClient'])->name('clients.search');
        
        // Scan & Verify
        Route::post('/scan/verify', [SimpleDelivererController::class, 'processScan'])->name('scan.verify');
        Route::post('/verify/package', [SimpleDelivererController::class, 'processScan'])->name('verify.package');
    });

    // ==================== FALLBACK - GESTION ERREURS ====================
    Route::fallback(function () {
        return redirect()->route('deliverer.tournee')->with('error', 'Page non trouvée. Redirection vers votre Run Sheet.');
    });

});