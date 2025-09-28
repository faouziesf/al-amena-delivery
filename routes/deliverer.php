<?php

use App\Http\Controllers\Deliverer\DelivererDashboardController;
use App\Http\Controllers\Deliverer\DelivererPackageController;
use App\Http\Controllers\Deliverer\DelivererWalletController;
use App\Http\Controllers\Deliverer\SimpleDelivererController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {

    // ==================== DASHBOARD - REDIRECT TO SIMPLE ====================
    Route::get('/dashboard', function() {
        return redirect()->route('deliverer.simple.dashboard');
    })->name('dashboard');

    // ==================== SIMPLE PWA DASHBOARD ====================
    Route::get('/simple', [SimpleDelivererController::class, 'dashboard'])->name('simple.dashboard');

    // ==================== SIMPLE SCANNER QR ====================
    Route::get('/scan', [SimpleDelivererController::class, 'scanner'])->name('scan.simple');
    Route::post('/scan/process', [SimpleDelivererController::class, 'processScan'])->name('scan.process');

    // ==================== SIMPLE ACTIONS ====================
    Route::post('/pickup/{package}', [SimpleDelivererController::class, 'markPickup'])->name('simple.pickup');
    Route::post('/deliver/{package}', [SimpleDelivererController::class, 'markDelivered'])->name('simple.deliver');
    Route::post('/unavailable/{package}', [SimpleDelivererController::class, 'markUnavailable'])->name('simple.unavailable');
    Route::post('/cancelled/{package}', [SimpleDelivererController::class, 'markCancelled'])->name('simple.cancelled');
    Route::post('/signature/{package}', [SimpleDelivererController::class, 'saveSignature'])->name('simple.signature');

    // ==================== PACKAGES - ESSENTIAL ONLY ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/{package}', [DelivererPackageController::class, 'show'])->name('show');
    });


    // ==================== WALLET - ESSENTIAL ONLY ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'index'])->name('index');
    });


    // ==================== SIMPLE API ROUTES ====================
    Route::prefix('api/simple')->name('api.simple.')->group(function () {
        Route::get('/pickups', [SimpleDelivererController::class, 'apiPickups'])->name('pickups');
        Route::get('/deliveries', [SimpleDelivererController::class, 'apiDeliveries'])->name('deliveries');
        Route::get('/wallet/balance', [SimpleDelivererController::class, 'apiWalletBalance'])->name('wallet.balance');
    });

});