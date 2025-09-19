<?php

use App\Http\Controllers\Deliverer\DelivererDashboardController;
use App\Http\Controllers\Deliverer\DelivererPackageController;
use App\Http\Controllers\Deliverer\DelivererWalletController;
use App\Http\Controllers\Deliverer\DelivererWithdrawalController;
use App\Http\Controllers\Deliverer\DelivererPaymentController;
use App\Http\Controllers\Deliverer\DelivererRunSheetController;
use App\Http\Controllers\Deliverer\DelivererClientTopupController;
use App\Http\Controllers\Deliverer\DelivererLocationController;
use App\Http\Controllers\Deliverer\DelivererNotificationController;
use App\Http\Controllers\Deliverer\DelivererProfileController;
use App\Http\Controllers\Deliverer\DelivererHelpController;
use App\Http\Controllers\Deliverer\DelivererEmergencyController;
use App\Http\Controllers\Deliverer\DelivererReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DelivererDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        // Détails d'un colis
        Route::get('/{package}', [DelivererPackageController::class, 'show'])->name('show');
        
        // ACTIONS CRITIQUES
        Route::post('/{package}/accept', [DelivererPackageController::class, 'acceptPickup'])->name('accept');
        Route::post('/{package}/pickup', [DelivererPackageController::class, 'markPickedUp'])->name('pickup');
        Route::post('/{package}/deliver', [DelivererPackageController::class, 'deliverPackage'])->name('deliver');
        Route::post('/{package}/unavailable', [DelivererPackageController::class, 'markUnavailable'])->name('unavailable');
        Route::post('/{package}/return', [DelivererPackageController::class, 'returnToSender'])->name('return');
        Route::post('/{package}/attempt', [DelivererPackageController::class, 'recordAttempt'])->name('attempt');
        
        // SCAN QR/CODES-BARRES
        Route::post('/scan', [DelivererPackageController::class, 'scanPackage'])->name('scan');
        Route::get('/search-advanced', [DelivererPackageController::class, 'searchAdvanced'])->name('search.advanced');
        Route::post('/scan-batch', [DelivererPackageController::class, 'scanBatch'])->name('scan.batch');
        
        // Actions groupées
        Route::post('/bulk-accept', [DelivererPackageController::class, 'bulkAccept'])->name('bulk.accept');
        Route::post('/bulk-pickup', [DelivererPackageController::class, 'bulkPickup'])->name('bulk.pickup');
        Route::post('/bulk-deliver', [DelivererPackageController::class, 'bulkDeliver'])->name('bulk.deliver');
        Route::post('/bulk-return', [DelivererPackageController::class, 'bulkReturn'])->name('bulk.return');
        
        // Documents
        Route::get('/run-sheet', [DelivererPackageController::class, 'generateRunSheet'])->name('run.sheet');
        Route::get('/{package}/delivery-receipt', [DelivererPackageController::class, 'deliveryReceipt'])->name('delivery.receipt');
    });

    // ==================== LISTES SPÉCIFIQUES (5 LISTES) ====================
    // LISTE 1: Pickups Disponibles
    Route::get('/pickups/available', [DelivererPackageController::class, 'availablePickups'])->name('pickups.available');
    
    // LISTE 2: Mes Pickups (acceptés)
    Route::get('/pickups/mine', [DelivererPackageController::class, 'myPickups'])->name('pickups.mine');
    
    // LISTE 3: Livraisons (à livrer + 4ème tentatives)
    Route::get('/deliveries', [DelivererPackageController::class, 'deliveries'])->name('deliveries.index');
    
    // LISTE 4: Retours (à retourner expéditeur)
    Route::get('/returns', [DelivererPackageController::class, 'returns'])->name('returns.index');

    // LISTE 5: Paiements clients
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [DelivererPaymentController::class, 'index'])->name('index');
        Route::get('/{withdrawalRequest}', [DelivererPaymentController::class, 'show'])->name('show');
        Route::post('/{withdrawalRequest}/deliver', [DelivererPaymentController::class, 'markDelivered'])->name('deliver');
        Route::post('/{withdrawalRequest}/unavailable', [DelivererPaymentController::class, 'markUnavailable'])->name('unavailable');
        Route::get('/{withdrawalRequest}/receipt', [DelivererPaymentController::class, 'printDeliveryReceipt'])->name('receipt');
        Route::get('/history', [DelivererPaymentController::class, 'history'])->name('history');
    });

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'index'])->name('index');
        Route::get('/history', [DelivererWalletController::class, 'history'])->name('history');
        Route::get('/sources', [DelivererWalletController::class, 'sources'])->name('sources');
        Route::post('/request-emptying', [DelivererWalletController::class, 'requestEmptying'])->name('request.emptying');
        Route::get('/export', [DelivererWalletController::class, 'exportTransactions'])->name('export');
    });

    // ==================== DEMANDES DE VIDAGE ====================
    Route::prefix('emptyings')->name('emptyings.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'emptyings'])->name('index');
        Route::get('/{emptying}', [DelivererWalletController::class, 'showEmptying'])->name('show');
    });

    // ==================== RUN SHEETS (Feuilles de Route) - CORRECTION ERREUR ====================
    Route::prefix('runsheets')->name('runsheets.')->group(function () {
        Route::get('/', [DelivererRunSheetController::class, 'index'])->name('index');
        Route::post('/generate', [DelivererRunSheetController::class, 'generate'])->name('generate');
        Route::get('/{runSheet}/print', [DelivererRunSheetController::class, 'print'])->name('print');
        Route::post('/{runSheet}/complete', [DelivererRunSheetController::class, 'markComplete'])->name('complete');
        Route::get('/{runSheet}/download/{token}', [DelivererRunSheetController::class, 'downloadWithToken'])->name('download');
    });
    
    // ==================== RECHARGE CLIENT (Ajout Fonds) ====================
    Route::prefix('client-topup')->name('client-topup.')->group(function () {
        Route::get('/', [DelivererClientTopupController::class, 'index'])->name('index');
        Route::post('/process', [DelivererClientTopupController::class, 'processTopup'])->name('process');
        Route::get('/history', [DelivererClientTopupController::class, 'history'])->name('history');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [DelivererNotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [DelivererNotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/mark-all-read', [DelivererNotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/{notification}/mark-read', [DelivererNotificationController::class, 'markRead'])->name('mark-read');
    });

    // ==================== PROFIL & PARAMÈTRES ====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [DelivererProfileController::class, 'show'])->name('show');
        Route::put('/', [DelivererProfileController::class, 'update'])->name('update');
        Route::get('/statistics', [DelivererProfileController::class, 'statistics'])->name('statistics');
    });
    
    // ==================== SUPPORT & AIDE ====================
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', [DelivererHelpController::class, 'index'])->name('index');
        Route::get('/qr-scanner', [DelivererHelpController::class, 'qrScanner'])->name('qr-scanner');
        Route::get('/cod-process', [DelivererHelpController::class, 'codProcess'])->name('cod-process');
        Route::post('/contact', [DelivererHelpController::class, 'contactSupport'])->name('contact');
    });
    
    // ==================== URGENCES & CONTACT COMMERCIAL ====================
    Route::prefix('emergency')->name('emergency.')->group(function () {
        Route::post('/call-commercial', [DelivererEmergencyController::class, 'callCommercial'])->name('call-commercial');
        Route::post('/report-issue', [DelivererEmergencyController::class, 'reportIssue'])->name('report-issue');
    });

    // ==================== API ENDPOINTS LIVREUR ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/dashboard-stats', [DelivererPackageController::class, 'apiDashboardStats'])->name('dashboard.stats');
        
        // Package APIs
        Route::get('/available-count', function() {
            return response()->json(['count' => \App\Models\Package::where('status', 'AVAILABLE')->count()]);
        })->name('packages.available.count');
        
        Route::get('/my-pickups-count', function() {
            return response()->json(['count' => \App\Models\Package::where('assigned_deliverer_id', auth()->id())->where('status', 'ACCEPTED')->count()]);
        })->name('packages.my-pickups.count');
        
        Route::get('/deliveries-count', function() {
            return response()->json(['count' => \App\Models\Package::where('assigned_deliverer_id', auth()->id())->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->count()]);
        })->name('packages.deliveries.count');
        
        Route::get('/returns-count', function() {
            return response()->json(['count' => \App\Models\Package::where('assigned_deliverer_id', auth()->id())->where('status', 'VERIFIED')->count()]);
        })->name('packages.returns.count');
        
        Route::get('/payments-count', [DelivererPaymentController::class, 'apiPaymentsCount'])->name('payments.count');
        
        // Wallet APIs
        Route::get('/wallet/balance', [DelivererPackageController::class, 'apiWalletBalance'])->name('wallet.balance');
        Route::get('/wallet/recent-transactions', [DelivererWalletController::class, 'apiRecentTransactions'])->name('wallet.transactions');
        Route::get('/wallet/earnings-chart', [DelivererWalletController::class, 'apiEarningsChart'])->name('wallet.earnings');
        
        // Délégations pour le scanner
        Route::get('/delegations', [DelivererPackageController::class, 'apiDelegations'])->name('delegations');
        
        // Notifications
        Route::get('/notifications/unread-count', function() {
            return response()->json([
                'count' => auth()->user()->notifications()->where('read', false)->count()
            ]);
        })->name('notifications.unread.count');
        
        Route::get('/notifications/recent', function() {
            $notifications = auth()->user()->notifications()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            return response()->json(['notifications' => $notifications]);
        })->name('notifications.recent');
        
        // Location tracking
        Route::post('/location/update', [DelivererLocationController::class, 'updateLocation'])->name('location.update');
        Route::get('/location/current', [DelivererLocationController::class, 'currentLocation'])->name('location.current');
        Route::get('/location/history', [DelivererLocationController::class, 'locationHistory'])->name('location.history');
    });
});
