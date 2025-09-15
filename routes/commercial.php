<?php

use App\Http\Controllers\Commercial\CommercialDashboardController;
use App\Http\Controllers\Commercial\ClientController;
use App\Http\Controllers\Commercial\ComplaintController;
use App\Http\Controllers\Commercial\WithdrawalController;
use App\Http\Controllers\Commercial\DelivererController;
use App\Http\Controllers\Commercial\PackageController;
use App\Http\Controllers\Commercial\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Commercial Routes
|--------------------------------------------------------------------------
|
| Routes spécifiques aux commerciaux (rôles COMMERCIAL et SUPERVISOR)
| Préfixe: /commercial
| Middleware: auth, verified, role:COMMERCIAL,SUPERVISOR
|
*/

Route::middleware(['auth', 'verified', 'role:COMMERCIAL,SUPERVISOR'])->prefix('commercial')->name('commercial.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [CommercialDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION CLIENTS ====================
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('update');
        
        // Actions spécifiques
        Route::post('/{client}/validate', [ClientController::class, 'validateAccount'])->name('validate');
        Route::post('/{client}/suspend', [ClientController::class, 'suspendAccount'])->name('suspend');
        
        // Gestion Wallet
        Route::get('/{client}/wallet', [ClientController::class, 'walletHistory'])->name('wallet.history');
        Route::post('/{client}/wallet/add', [ClientController::class, 'addFunds'])->name('wallet.add');
        Route::post('/{client}/wallet/deduct', [ClientController::class, 'deductFunds'])->name('wallet.deduct');
        Route::get('/{client}/wallet/export', [ClientController::class, 'exportWalletHistory'])->name('wallet.export');
        
        // Export
        Route::get('/{client}/export', [ClientController::class, 'exportClientData'])->name('export.data');

        // Opérations groupées
        Route::post('/bulk/validate', [ClientController::class, 'bulkValidate'])->name('bulk.validate');
        
        // API Endpoints
        Route::get('/{client}/api/stats', [ClientController::class, 'apiStats'])->name('api.stats');
    });

    // ==================== GESTION RÉCLAMATIONS ====================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ComplaintController::class, 'index'])->name('index');
        Route::get('/{complaint}', [ComplaintController::class, 'show'])->name('show');
        
        // Actions sur réclamations
        Route::post('/{complaint}/assign', [ComplaintController::class, 'assign'])->name('assign');
        Route::post('/{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('resolve');
        Route::post('/{complaint}/reject', [ComplaintController::class, 'reject'])->name('reject');
        Route::post('/{complaint}/urgent', [ComplaintController::class, 'markAsUrgent'])->name('urgent');
        Route::post('/bulk-assign', [ComplaintController::class, 'bulkAssign'])->name('bulk.assign');
        
        // Modification COD
        Route::post('/packages/{package}/modify-cod', [ComplaintController::class, 'modifyCod'])->name('modify.cod');
    });

    // ==================== GESTION RETRAITS ====================
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::get('/{withdrawal}', [WithdrawalController::class, 'show'])->name('show');
        
        // Actions sur retraits
        Route::post('/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('reject');
        Route::post('/{withdrawal}/assign', [WithdrawalController::class, 'assignToDeliverer'])->name('assign');
        Route::post('/{withdrawal}/delivered', [WithdrawalController::class, 'markAsDelivered'])->name('delivered');
        Route::post('/bulk-approve', [WithdrawalController::class, 'bulkApprove'])->name('bulk.approve');
        
        // Documents
        Route::get('/{withdrawal}/receipt', [WithdrawalController::class, 'generateDeliveryReceipt'])->name('receipt');
    });

    // ==================== GESTION LIVREURS ====================
    Route::prefix('deliverers')->name('deliverers.')->group(function () {
        Route::get('/', [DelivererController::class, 'index'])->name('index');
        Route::get('/{deliverer}', [DelivererController::class, 'show'])->name('show');
        Route::get('/{deliverer}/wallet', [DelivererController::class, 'walletDetails'])->name('wallet');
        
        // Actions wallet
        Route::post('/{deliverer}/empty-wallet', [DelivererController::class, 'emptyWallet'])->name('empty.wallet');
        Route::post('/bulk-empty', [DelivererController::class, 'bulkEmpty'])->name('bulk.empty');
        Route::post('/{deliverer}/assign-cash-delivery', [DelivererController::class, 'assignCashDelivery'])->name('assign.cash');
        
        // Reçus
        Route::get('/emptying/{emptying}/receipt', [DelivererController::class, 'generateEmptyingReceipt'])->name('emptying.receipt');
    });

    // ==================== GESTION COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::get('/{package}', [PackageController::class, 'show'])->name('show');
        
        // Actions colis
        Route::post('/{package}/update-status', [PackageController::class, 'updateStatus'])->name('update.status');
        Route::post('/{package}/assign-deliverer', [PackageController::class, 'assignDeliverer'])->name('assign.deliverer');
        Route::post('/{package}/modify-cod', [PackageController::class, 'modifyCod'])->name('modify.cod');
        Route::post('/{package}/reset-attempts', [PackageController::class, 'resetDeliveryAttempts'])->name('reset.attempts');
        
        // Actions groupées
        Route::post('/bulk-update-status', [PackageController::class, 'bulkUpdateStatus'])->name('bulk.status');
        Route::post('/bulk-assign-deliverer', [PackageController::class, 'bulkAssignDeliverer'])->name('bulk.assign');
        
        // Documents
        Route::post('/run-sheet', [PackageController::class, 'generateRunSheet'])->name('run.sheet');
    });

    // ==================== GESTION NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        
        // Actions notifications
        Route::post('/mark-read/{notification?}', [NotificationController::class, 'markAsRead'])->name('mark.read');
        Route::post('/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('mark.unread');
        Route::delete('/{notification}', [NotificationController::class, 'delete'])->name('delete');
        Route::post('/bulk-action', [NotificationController::class, 'bulkAction'])->name('bulk.action');
        Route::delete('/delete-old', [NotificationController::class, 'deleteOld'])->name('delete.old');
        
        // Test (développement uniquement)
        Route::post('/api/test-notification', [NotificationController::class, 'createTestNotification'])->name('api.test');
    });

    // ==================== API ENDPOINTS COMMERCIAUX ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/dashboard-stats', [CommercialDashboardController::class, 'api_getDashboardStats'])->name('dashboard.stats');
        
        // Client APIs
        Route::get('/clients/search', [ClientController::class, 'apiSearch'])->name('clients.search');
        Route::get('/clients/{client}/profile', [ClientController::class, 'apiClientProfile'])->name('clients.profile');
        Route::get('/clients/{client}/duplicate-data', [ClientController::class, 'apiClientForDuplication'])->name('clients.duplicate.data');
        Route::get('/clients/recent', [ClientController::class, 'apiRecentClients'])->name('clients.recent');
        Route::get('/clients/global-stats', [ClientController::class, 'apiGlobalStats'])->name('clients.global.stats');
        
        // Complaint APIs
        Route::get('/complaints/stats', [ComplaintController::class, 'apiStats'])->name('complaints.stats');
        Route::get('/complaints/pending', [ComplaintController::class, 'apiPending'])->name('complaints.pending');
        Route::get('/complaints/recent-activity', [ComplaintController::class, 'apiRecentActivity'])->name('complaints.activity');
        
        // Withdrawal APIs
        Route::get('/withdrawals/pending', [WithdrawalController::class, 'apiPending'])->name('withdrawals.pending');
        Route::get('/withdrawals/stats', [WithdrawalController::class, 'apiStats'])->name('withdrawals.stats');
        Route::get('/withdrawals/awaiting-delivery', [WithdrawalController::class, 'apiAwaitingDelivery'])->name('withdrawals.awaiting');
        Route::get('/withdrawals/search-clients', [WithdrawalController::class, 'apiSearchClients'])->name('withdrawals.search.clients');
        
        // Deliverer APIs
        Route::get('/deliverers/search', [DelivererController::class, 'apiSearch'])->name('deliverers.search');
        Route::get('/deliverers/high-balance', [DelivererController::class, 'apiHighBalanceDeliverers'])->name('deliverers.high.balance');
        Route::get('/deliverers/stats', [DelivererController::class, 'apiStats'])->name('deliverers.stats');
        Route::get('/deliverers/recent-emptyings', [DelivererController::class, 'apiRecentEmptyings'])->name('deliverers.emptyings');
        
        // Package APIs
        Route::get('/packages/search', [PackageController::class, 'apiSearch'])->name('packages.search');
        Route::get('/packages/stats', [PackageController::class, 'apiStats'])->name('packages.stats');
        Route::get('/packages/blocked', [PackageController::class, 'apiBlockedPackages'])->name('packages.blocked');
        Route::get('/packages/by-delegation', [PackageController::class, 'apiByDelegation'])->name('packages.delegation');
        Route::get('/packages/{package}/cod-history', [PackageController::class, 'codHistory'])->name('packages.cod.history');
        
        // Notification APIs
        Route::get('/notifications/unread-count', [NotificationController::class, 'apiUnreadCount'])->name('notifications.unread.count');
        Route::get('/notifications/recent', [NotificationController::class, 'apiRecent'])->name('notifications.recent');
        Route::get('/notifications/all', [NotificationController::class, 'apiAll'])->name('notifications.all');
        Route::post('/notifications/mark-read', [NotificationController::class, 'apiMarkRead'])->name('notifications.mark.read');
        Route::get('/notifications/stats', [NotificationController::class, 'apiStats'])->name('notifications.stats');
        Route::get('/notifications/by-type/{type}', [NotificationController::class, 'apiByType'])->name('notifications.by.type');
        
        // Global APIs
        Route::get('/complaints-count', [CommercialDashboardController::class, 'api_getComplaintsCount'])->name('complaints.count');
        Route::get('/withdrawals-count', [CommercialDashboardController::class, 'api_getWithdrawalsCount'])->name('withdrawals.count');
        Route::get('/search-clients', [CommercialDashboardController::class, 'api_searchClients'])->name('search.clients');
        Route::get('/search-deliverers', [CommercialDashboardController::class, 'api_searchDeliverers'])->name('search.deliverers');
    });
});