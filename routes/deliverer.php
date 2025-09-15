<?php

use App\Http\Controllers\Deliverer\DelivererDashboardController;
use App\Http\Controllers\Deliverer\DelivererPackageController;
use App\Http\Controllers\Deliverer\DelivererWalletController;
use App\Http\Controllers\Deliverer\DelivererWithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Deliverer Routes
|--------------------------------------------------------------------------
|
| Routes spécifiques aux livreurs (rôle DELIVERER)
| Préfixe: /deliverer
| Middleware: auth, verified, role:DELIVERER
|
*/

Route::middleware(['auth', 'verified', 'role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DelivererDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        // Colis disponibles pour pickup
        Route::get('/available', [DelivererPackageController::class, 'available'])->name('available');
        
        // Colis assignés au livreur
        Route::get('/assigned', [DelivererPackageController::class, 'assigned'])->name('assigned');
        
        // Historique des colis
        Route::get('/history', [DelivererPackageController::class, 'history'])->name('history');
        
        // Détails d'un colis
        Route::get('/{package}', [DelivererPackageController::class, 'show'])->name('show');
        
        // Actions sur les colis
        Route::post('/{package}/accept', [DelivererPackageController::class, 'accept'])->name('accept');
        Route::post('/{package}/pickup', [DelivererPackageController::class, 'pickup'])->name('pickup');
        Route::post('/{package}/deliver', [DelivererPackageController::class, 'deliver'])->name('deliver');
        Route::post('/{package}/return', [DelivererPackageController::class, 'return'])->name('return');
        Route::post('/{package}/attempt', [DelivererPackageController::class, 'recordAttempt'])->name('attempt');
        
        // Actions groupées
        Route::post('/bulk-accept', [DelivererPackageController::class, 'bulkAccept'])->name('bulk.accept');
        Route::post('/bulk-pickup', [DelivererPackageController::class, 'bulkPickup'])->name('bulk.pickup');
        
        // Documents
        Route::get('/run-sheet', [DelivererPackageController::class, 'generateRunSheet'])->name('run.sheet');
        Route::get('/{package}/delivery-receipt', [DelivererPackageController::class, 'deliveryReceipt'])->name('delivery.receipt');
    });

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'index'])->name('index');
        Route::get('/history', [DelivererWalletController::class, 'history'])->name('history');
        Route::post('/request-emptying', [DelivererWalletController::class, 'requestEmptying'])->name('request.emptying');
    });

    // ==================== DEMANDES DE VIDAGE ====================
    Route::prefix('emptyings')->name('emptyings.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'emptyings'])->name('index');
        Route::get('/{emptying}', [DelivererWalletController::class, 'showEmptying'])->name('show');
    });

    // ==================== RETRAITS CLIENT ====================
    Route::prefix('client-withdrawals')->name('client.withdrawals.')->group(function () {
        Route::get('/assigned', [DelivererWithdrawalController::class, 'assigned'])->name('assigned');
        Route::get('/{withdrawal}', [DelivererWithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/accept', [DelivererWithdrawalController::class, 'accept'])->name('accept');
        Route::post('/{withdrawal}/deliver', [DelivererWithdrawalController::class, 'deliver'])->name('deliver');
        Route::post('/{withdrawal}/failed', [DelivererWithdrawalController::class, 'markAsFailed'])->name('failed');
        Route::get('/{withdrawal}/receipt', [DelivererWithdrawalController::class, 'deliveryReceipt'])->name('receipt');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', function() {
            $user = auth()->user();
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
            return view('deliverer.notifications.index', compact('notifications'));
        })->name('index');
        
        Route::post('/{notification}/mark-read', function($notificationId) {
            $notification = auth()->user()->notifications()->findOrFail($notificationId);
            $notification->update(['read' => true, 'read_at' => now()]);
            return response()->json(['success' => true]);
        })->name('mark-read');
        
        Route::post('/mark-all-read', function() {
            auth()->user()->notifications()->where('read', false)->update([
                'read' => true, 
                'read_at' => now()
            ]);
            return response()->json(['success' => true]);
        })->name('mark-all-read');
    });

    // ==================== API ENDPOINTS LIVREUR ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/dashboard-stats', [DelivererDashboardController::class, 'apiStats'])->name('dashboard.stats');
        
        // Package APIs
        Route::get('/packages/available/count', [DelivererPackageController::class, 'apiAvailableCount'])->name('packages.available.count');
        Route::get('/packages/assigned/count', [DelivererPackageController::class, 'apiAssignedCount'])->name('packages.assigned.count');
        Route::get('/packages/{package}/location', [DelivererPackageController::class, 'apiPackageLocation'])->name('packages.location');
        
        // Wallet APIs
        Route::get('/wallet/balance', [DelivererWalletController::class, 'apiBalance'])->name('wallet.balance');
        Route::get('/wallet/recent-transactions', [DelivererWalletController::class, 'apiRecentTransactions'])->name('wallet.transactions');
        
        // Notification APIs
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
        
        // Location tracking (si implémenté)
        Route::post('/location/update', [DelivererDashboardController::class, 'updateLocation'])->name('location.update');
        Route::get('/location/current', [DelivererDashboardController::class, 'currentLocation'])->name('location.current');
    });
});