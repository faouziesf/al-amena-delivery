<?php

use App\Http\Controllers\Deliverer\SimpleDelivererController;
use App\Models\Package;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {

    // ==================== DASHBOARD - REDIRECT TO TOURNÉE ====================
    Route::get('/dashboard', function() {
        return redirect()->route('deliverer.tournee');
    })->name('dashboard');

    // ==================== PAGES PRINCIPALES MODERNES ====================
    Route::get('/tournee', function() {
        return view('deliverer.tournee');
    })->name('tournee');

    Route::get('/pickups/available', function() {
        return view('deliverer.pickups-available');
    })->name('pickups.available');

    Route::get('/wallet', function() {
        return view('deliverer.wallet-modern');
    })->name('wallet');

    Route::get('/recharge', function() {
        return view('deliverer.recharge-client');
    })->name('recharge');

    Route::get('/menu', function() {
        return view('deliverer.menu');
    })->name('menu');

    // ==================== DÉTAIL TÂCHE ====================
    Route::get('/task/{package}', function(Package $package) {
        return view('deliverer.task-detail-modern', compact('package'));
    })->name('task.detail');

    // ==================== SIGNATURE ====================
    Route::get('/signature/{package}', function(Package $package) {
        return view('deliverer.signature-modern', compact('package'));
    })->name('signature.capture');

    Route::post('/signature/{package}', [SimpleDelivererController::class, 'saveSignature'])->name('signature.save');

    // ==================== SCANNER (NE PAS MODIFIER) ====================
    Route::get('/scan', function() { 
        return view('deliverer.simple-scanner-optimized'); 
    })->name('scan.simple');
    
    Route::post('/scan/process', [SimpleDelivererController::class, 'processScan'])->name('scan.process');
    
    Route::get('/scan/multi', function() { 
        return view('deliverer.multi-scanner-optimized'); 
    })->name('scan.multi');
    
    Route::post('/scan/multi/process', [SimpleDelivererController::class, 'processMultiScan'])->name('scan.multi.process');
    Route::post('/scan/multi/validate', [SimpleDelivererController::class, 'validateMultiScan'])->name('scan.multi.validate');

    // ==================== ACTIONS COLIS ====================
    Route::post('/pickup/{package}', [SimpleDelivererController::class, 'markPickup'])->name('pickup');
    Route::post('/deliver/{package}', [SimpleDelivererController::class, 'markDelivered'])->name('deliver');
    Route::post('/unavailable/{package}', [SimpleDelivererController::class, 'markUnavailable'])->name('unavailable');

    // ==================== IMPRESSION ====================
    Route::get('/print/run-sheet', [SimpleDelivererController::class, 'printRunSheet'])->name('print.run.sheet');
    Route::get('/print/receipt/{package}', [SimpleDelivererController::class, 'printDeliveryReceipt'])->name('print.receipt');

    // ==================== API ENDPOINTS ====================
    Route::prefix('api')->name('api.')->group(function() {
        
        // Packages
        Route::get('/packages/active', [SimpleDelivererController::class, 'apiActivePackages'])->name('packages.active');
        Route::get('/packages/delivered', [SimpleDelivererController::class, 'apiDeliveredPackages'])->name('packages.delivered');
        Route::get('/task/{id}', [SimpleDelivererController::class, 'apiTaskDetail'])->name('task.detail');
        
        // Pickups
        Route::get('/pickups/available', [SimpleDelivererController::class, 'apiAvailablePickups'])->name('pickups.available');
        Route::post('/pickups/{pickupRequest}/accept', [SimpleDelivererController::class, 'acceptPickup'])->name('pickups.accept');
        
        // Wallet
        Route::get('/wallet/balance', [SimpleDelivererController::class, 'apiWalletBalance'])->name('wallet.balance');
        
        // Recharge Client
        Route::get('/search/client', [SimpleDelivererController::class, 'searchClient'])->name('search.client');
        Route::post('/recharge/client', [SimpleDelivererController::class, 'rechargeClient'])->name('recharge.client');
    });

});
