<?php

use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientPackageController;
use App\Http\Controllers\Client\ClientPackageImportController;
use App\Http\Controllers\Client\ClientWalletController;
use App\Http\Controllers\Client\ClientComplaintController;
use App\Http\Controllers\Client\ClientNotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes - Version Complète et Optimisée
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\CheckRole::class . ':CLIENT'])->prefix('client')->name('client.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [ClientPackageController::class, 'index'])->name('index');
        Route::get('/pending', [ClientPackageController::class, 'pending'])->name('pending');
        Route::get('/in-progress', [ClientPackageController::class, 'inProgress'])->name('in-progress');
        Route::get('/delivered', [ClientPackageController::class, 'delivered'])->name('delivered');
        Route::get('/returned', [ClientPackageController::class, 'returned'])->name('returned');
        Route::get('/create', [ClientPackageController::class, 'create'])->name('create');
        Route::post('/', [ClientPackageController::class, 'store'])->name('store');
        Route::get('/{package}', [ClientPackageController::class, 'show'])->name('show');
        Route::delete('/{package}', [ClientPackageController::class, 'destroy'])->name('destroy');
        Route::post('/duplicate/{package}', [ClientPackageController::class, 'duplicate'])->name('duplicate');
        Route::post('/bulk-delete', [ClientPackageController::class, 'bulkDestroy'])->name('bulk.destroy');
        Route::get('/{package}/print', [ClientPackageController::class, 'printDeliveryNote'])->name('print');
        Route::post('/print/multiple', [ClientPackageController::class, 'printMultipleDeliveryNotes'])->name('print.multiple');
        Route::get('/print/batch/{batch}', [ClientPackageController::class, 'printBatchDeliveryNotes'])->name('print.batch');
        Route::get('/export', [ClientPackageController::class, 'export'])->name('export');
        
        // Import CSV
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/csv', [ClientPackageImportController::class, 'showImportForm'])->name('csv');
            Route::post('/csv', [ClientPackageImportController::class, 'processImportCsv'])->name('process');
            Route::get('/template', [ClientPackageImportController::class, 'downloadTemplate'])->name('template');
            Route::get('/{batch}/status', [ClientPackageImportController::class, 'showImportStatus'])->name('status');
            Route::get('/{batch}/progress', [ClientPackageImportController::class, 'apiImportProgress'])->name('progress');
            Route::get('/{batch}/errors', [ClientPackageImportController::class, 'apiImportErrors'])->name('errors');
            Route::post('/validate-csv', [ClientPackageImportController::class, 'apiValidateCsv'])->name('validate');
        });
    });

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [ClientWalletController::class, 'index'])->name('index');
        Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions');
        Route::get('/transaction/{transaction}', [ClientWalletController::class, 'showTransaction'])->name('transaction.show');
        Route::get('/withdrawal', [ClientWalletController::class, 'createWithdrawal'])->name('withdrawal');
        Route::post('/withdrawal', [ClientWalletController::class, 'storeWithdrawal'])->name('store.withdrawal');
        Route::get('/withdrawal/{withdrawal}', [ClientWalletController::class, 'showWithdrawal'])->name('withdrawal.show');
        Route::post('/withdrawal/{withdrawal}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdrawal.cancel');
        Route::get('/topup', [ClientWalletController::class, 'showTopupForm'])->name('topup');
        Route::post('/topup', [ClientWalletController::class, 'processTopup'])->name('process.topup');
        Route::get('/statement', [ClientWalletController::class, 'downloadStatement'])->name('statement');
        Route::get('/export', [ClientWalletController::class, 'exportTransactions'])->name('export');
    });

    // ==================== DEMANDES DE RETRAIT (Liste uniquement) ====================
    Route::get('/withdrawals', [ClientWalletController::class, 'withdrawals'])->name('withdrawals');

    // ==================== RÉCLAMATIONS ====================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ClientComplaintController::class, 'index'])->name('index');
        Route::get('/create/{package}', [ClientComplaintController::class, 'create'])->name('create');
        Route::post('/{package}', [ClientComplaintController::class, 'store'])->name('store');
        Route::get('/{complaint}', [ClientComplaintController::class, 'show'])->name('show');
        Route::post('/{complaint}/respond', [ClientComplaintController::class, 'respond'])->name('respond');
        Route::post('/{complaint}/close', [ClientComplaintController::class, 'close'])->name('close');
        Route::post('/{complaint}/reopen', [ClientComplaintController::class, 'reopen'])->name('reopen');
        Route::post('/{package}/change-cod', [ClientComplaintController::class, 'requestCodChange'])->name('change.cod');
        Route::post('/{package}/request-return', [ClientComplaintController::class, 'requestReturn'])->name('request.return');
        Route::post('/{package}/reschedule', [ClientComplaintController::class, 'requestReschedule'])->name('reschedule');
        Route::get('/{complaint}/timeline', [ClientComplaintController::class, 'showTimeline'])->name('timeline');
        Route::post('/{complaint}/mark-resolved', [ClientComplaintController::class, 'markResolved'])->name('mark.resolved');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [ClientNotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-read', [ClientNotificationController::class, 'markAsRead'])->name('mark.read');
        Route::post('/mark-all-read', [ClientNotificationController::class, 'markAllAsRead'])->name('mark.all.read');
        Route::delete('/{notification}', [ClientNotificationController::class, 'delete'])->name('delete');
        Route::post('/bulk-delete', [ClientNotificationController::class, 'bulkDelete'])->name('bulk.delete');
        Route::get('/settings', [ClientNotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [ClientNotificationController::class, 'updateSettings'])->name('update.settings');
        Route::post('/preferences', [ClientNotificationController::class, 'updatePreferences'])->name('preferences');
    });

    // ==================== API ENDPOINTS ====================
    Route::prefix('api')->name('api.')->group(function () {
        // Dashboard APIs
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/stats', [ClientDashboardController::class, 'apiStats'])->name('stats');
            Route::get('/today-stats', [ClientDashboardController::class, 'apiTodayStats'])->name('today.stats');
            Route::get('/chart-data', [ClientDashboardController::class, 'apiChartData'])->name('chart.data');
            Route::get('/recent-activity', [ClientDashboardController::class, 'apiRecentActivity'])->name('recent.activity');
        });
        
        // Wallet APIs
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/balance', [ClientWalletController::class, 'apiBalance'])->name('balance');
            Route::get('/transactions', [ClientWalletController::class, 'apiTransactions'])->name('transactions');
            Route::get('/summary', [ClientWalletController::class, 'apiSummary'])->name('summary');
            Route::get('/check-balance', [ClientWalletController::class, 'apiCheckBalance'])->name('check.balance');
        });
        
        // Package APIs
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/{package}/status', [ClientPackageController::class, 'apiStatus'])->name('status');
            Route::get('/summary', [ClientPackageController::class, 'apiSummary'])->name('summary');
            Route::get('/last-supplier-data', [ClientPackageController::class, 'apiLastSupplierData'])->name('last.supplier');
            Route::get('/session-data', [ClientPackageController::class, 'apiSessionData'])->name('session.data');
            Route::get('/today-stats', [ClientPackageController::class, 'apiTodayStats'])->name('today.stats');
        });
        
        // Notification APIs
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/unread-count', [ClientNotificationController::class, 'apiUnreadCount'])->name('unread.count');
            Route::get('/recent', [ClientNotificationController::class, 'apiRecent'])->name('recent');
            Route::post('/mark-read', [ClientNotificationController::class, 'apiMarkRead'])->name('mark.read');
            Route::get('/poll', [ClientNotificationController::class, 'apiPollNotifications'])->name('poll');
        });
    });

    // ==================== WEBHOOKS ====================
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('/package-status-changed', [ClientPackageController::class, 'webhookPackageStatusChanged'])->name('package.status.changed');
        Route::post('/payment-received', [ClientWalletController::class, 'webhookPaymentReceived'])->name('payment.received');
        Route::post('/withdrawal-processed', [ClientWalletController::class, 'webhookWithdrawalProcessed'])->name('withdrawal.processed');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Publiques pour Tracking
|--------------------------------------------------------------------------
*/
Route::prefix('track')->name('public.track.')->group(function () {
    Route::get('/{package_code}', [ClientPackageController::class, 'publicTracking'])->name('package');
    Route::post('/check', [ClientPackageController::class, 'publicTrackingCheck'])->name('check');
    Route::get('/qr/{package_code}', [ClientPackageController::class, 'qrTracking'])->name('qr');
});