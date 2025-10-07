<?php

use App\Http\Controllers\Deliverer\SimpleDelivererController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:DELIVERER', 'ngrok.cors'])->prefix('deliverer')->name('deliverer.')->group(function () {

    // ==================== DASHBOARD - REDIRECT TO TOURNÉE ====================
    Route::get('/dashboard', function() {
        return redirect()->route('deliverer.tournee');
    })->name('dashboard');


    // ==================== ROUTES PRODUCTION ====================
    Route::get('/task/{package}', [SimpleDelivererController::class, 'taskDetail'])->name('task.detail')->where('package', '[0-9]+');
    Route::get('/task/{taskId}', [SimpleDelivererController::class, 'taskByCustomId'])->name('task.custom')->where('taskId', '[a-zA-Z0-9_]+');
    Route::get('/signature/{package}', [SimpleDelivererController::class, 'signatureCapture'])->name('signature.capture');
    Route::get('/wallet', function() { return view('deliverer.wallet-modern'); })->name('wallet');
    Route::get('/client-recharge', [SimpleDelivererController::class, 'clientRecharge'])->name('client.recharge');
    Route::get('/recharge', function() { return view('deliverer.recharge-client'); })->name('recharge');
    Route::get('/tournee', [SimpleDelivererController::class, 'tournee'])->name('tournee');
    Route::get('/pickups/available', function() { return view('deliverer.pickups-available'); })->name('pickups.available');
    Route::get('/pickup/{id}', [SimpleDelivererController::class, 'pickupDetail'])->name('pickup.detail');
    Route::post('/pickup/{id}/collect', [SimpleDelivererController::class, 'markPickupCollect'])->name('pickup.collect');
    Route::get('/menu', [SimpleDelivererController::class, 'menu'])->name('menu');

    // ==================== SCANNER PRODUCTION ====================
    Route::get('/scan', function() { return view('deliverer.scan-production'); })->name('scan.simple');
    Route::post('/scan/submit', [SimpleDelivererController::class, 'scanSubmit'])->name('scan.submit');
    Route::get('/scan/multi', [SimpleDelivererController::class, 'multiScanner'])->name('scan.multi');

    // ==================== SIMPLE ACTIONS ====================
    Route::post('/pickup/{package}', [SimpleDelivererController::class, 'markPickup'])->name('simple.pickup');
    Route::post('/deliver/{package}', [SimpleDelivererController::class, 'markDelivered'])->name('simple.deliver');
    Route::post('/unavailable/{package}', [SimpleDelivererController::class, 'markUnavailable'])->name('simple.unavailable');
    Route::post('/signature/{package}', [SimpleDelivererController::class, 'saveSignature'])->name('simple.signature');

    Route::get('/print/run-sheet', [SimpleDelivererController::class, 'printRunSheet'])->name('print.run.sheet');
    Route::get('/print/receipt/{package}', [SimpleDelivererController::class, 'printDeliveryReceipt'])->name('print.receipt');



    // ==================== API Routes ====================
    Route::prefix('api')->group(function() {
        Route::get('/packages/active', [SimpleDelivererController::class, 'apiActivePackages'])->name('api.active.packages');
        Route::get('/packages/delivered', [SimpleDelivererController::class, 'apiDeliveredPackages'])->name('api.delivered.packages');
        Route::get('/pickups/available', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('api.available.pickups');
        Route::post('/pickups/{pickupRequest}/accept', [SimpleDelivererController::class, 'acceptPickup'])->name('api.accept.pickup');
        Route::post('/pickups/{pickupRequest}/collected', [SimpleDelivererController::class, 'markPickupCollected'])->name('api.pickup.collected');
        Route::get('/wallet/balance', [SimpleDelivererController::class, 'apiWalletBalance'])->name('api.wallet.balance');
    });

    // ==================== RETRAITS ESPÈCES ====================
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [SimpleDelivererController::class, 'myWithdrawals'])->name('index');
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