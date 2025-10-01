<?php

use App\Http\Controllers\Deliverer\SimpleDelivererController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {

    // ==================== DASHBOARD - REDIRECT TO RUN SHEET ====================
    Route::get('/dashboard', function() {
        return redirect()->route('deliverer.run.sheet');
    })->name('dashboard');


    // ==================== NOUVELLE PWA OPTIMISÉE ====================
    Route::get('/run-sheet', [SimpleDelivererController::class, 'runSheet'])->name('run.sheet');
    Route::get('/task/{package}', [SimpleDelivererController::class, 'taskDetail'])->name('task.detail')->where('package', '[0-9]+');
    Route::get('/task/{taskId}', [SimpleDelivererController::class, 'taskByCustomId'])->name('task.custom')->where('taskId', '[a-zA-Z0-9_]+');
    Route::get('/signature/{package}', [SimpleDelivererController::class, 'signatureCapture'])->name('signature.capture');
    Route::get('/wallet-optimized', [SimpleDelivererController::class, 'walletOptimized'])->name('wallet.optimized');
    Route::get('/client-recharge', [SimpleDelivererController::class, 'clientRecharge'])->name('client.recharge');

    // ==================== SIMPLE SCANNER QR ====================
    Route::get('/scan', [SimpleDelivererController::class, 'scanner'])->name('scan.simple');
    Route::post('/scan/process', [SimpleDelivererController::class, 'processScan'])->name('scan.process');

    // ==================== SIMPLE ACTIONS ====================
    Route::post('/pickup/{package}', [SimpleDelivererController::class, 'markPickup'])->name('simple.pickup');
    Route::post('/deliver/{package}', [SimpleDelivererController::class, 'markDelivered'])->name('simple.deliver');
    Route::post('/unavailable/{package}', [SimpleDelivererController::class, 'markUnavailable'])->name('simple.unavailable');
    Route::post('/cancelled/{package}', [SimpleDelivererController::class, 'markCancelled'])->name('simple.cancelled');
    Route::post('/signature/{package}', [SimpleDelivererController::class, 'saveSignature'])->name('simple.signature');

    // ==================== IMPRESSION ====================
    Route::get('/print/run-sheet', [SimpleDelivererController::class, 'printRunSheet'])->name('print.run.sheet');
    Route::get('/print/receipt/{package}', [SimpleDelivererController::class, 'printDeliveryReceipt'])->name('print.receipt');



    // ==================== PICKUP REQUESTS ====================
    Route::post('/pickup-requests/{pickupRequest}/accept', [SimpleDelivererController::class, 'acceptPickup'])->name('pickup.accept');
    Route::post('/pickup-requests/{pickupRequest}/collected', [SimpleDelivererController::class, 'markPickupCollected'])->name('pickup.collected');

    // ==================== RETRAITS ESPÈCES ====================
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        // Vue des retraits assignés
        Route::get('/', [SimpleDelivererController::class, 'myWithdrawals'])->name('index');
        // Utilise le même contrôleur commercial mais avec middleware livreur
        Route::post('/{withdrawal}/delivered', [\App\Http\Controllers\Commercial\WithdrawalController::class, 'markAsDelivered'])->name('delivered');
    });

    // ==================== SIMPLE API ROUTES ====================
    Route::prefix('api/simple')->name('api.simple.')->group(function () {
        Route::get('/pickups', [SimpleDelivererController::class, 'apiPickups'])->name('pickups');
        Route::get('/deliveries', [SimpleDelivererController::class, 'apiDeliveries'])->name('deliveries');
        Route::get('/wallet/balance', [SimpleDelivererController::class, 'apiWalletBalance'])->name('wallet.balance');
        Route::get('/available-pickups', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('available.pickups.api');
    });

});