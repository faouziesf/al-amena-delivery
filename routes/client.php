<?php

use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientPackageController;
use App\Http\Controllers\Client\ClientPackageImportController;
use App\Http\Controllers\Client\ClientWalletController;
use App\Http\Controllers\Client\ClientComplaintController;
use App\Http\Controllers\Client\ClientNotificationController;
use App\Http\Controllers\Client\ClientPickupRequestController;
use App\Http\Controllers\Client\ClientPickupAddressController;
use App\Http\Controllers\Client\ClientBankAccountController;
use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\Client\ClientTicketController;
use App\Http\Controllers\Client\ClientManifestController;
use App\Http\Controllers\Client\ClientReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes - UNIQUEMENT pour les clients (PROPRE)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\CheckRole::class . ':CLIENT'])->prefix('client')->name('client.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api/stats', [ClientDashboardController::class, 'apiStats'])->name('dashboard.api.stats');

    // ==================== GESTION RETOURS CLIENT (REFACTORISÉ) ====================
    Route::prefix('returns')->name('returns.')->group(function () {
        // Liste des retours en attente de traitement (48h)
        Route::get('/pending', [ClientReturnController::class, 'pending'])->name('pending');
        
        // Détails d'un colis retourné
        Route::get('/{id}', [ClientReturnController::class, 'show'])->name('show');
        
        // Détails du colis retour associé (ReturnPackage)
        Route::get('/return-package/{returnPackageId}', [ClientReturnController::class, 'showReturnPackage'])->name('show-return-package');
        
        // Valider la réception d'un colis retourné
        Route::post('/{id}/validate-reception', [ClientReturnController::class, 'validateReception'])->name('validate-reception');
        
        // Réclamer un problème pour un colis retourné
        Route::post('/{id}/report-problem', [ClientReturnController::class, 'reportProblem'])->name('report-problem');
        
        // API: Obtenir le nombre de retours en attente (pour badge notification)
        Route::get('/api/pending-count', [ClientReturnController::class, 'getPendingCount'])->name('api.pending-count');
    });

    // ==================== API ROUTES ====================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard-stats', [ClientDashboardController::class, 'apiStats'])->name('client.dashboard.stats');
    });

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [ClientPackageController::class, 'index'])->name('index');
        Route::get('/pending', [ClientPackageController::class, 'pending'])->name('pending');
        Route::get('/in-progress', [ClientPackageController::class, 'inProgress'])->name('in-progress');
        Route::get('/delivered', [ClientPackageController::class, 'delivered'])->name('delivered');
        Route::get('/returned', [ClientPackageController::class, 'returned'])->name('returned');
        Route::get('/create', [ClientPackageController::class, 'create'])->name('create');
        Route::get('/create-fast', [ClientPackageController::class, 'createFast'])->name('create-fast');
        Route::post('/', [ClientPackageController::class, 'store'])->name('store');
        Route::post('/store-multiple', [ClientPackageController::class, 'storeMultiple'])->name('store-multiple');
        Route::get('/{package}', [ClientPackageController::class, 'show'])->name('show');
        Route::get('/{package}/edit', [ClientPackageController::class, 'edit'])->name('edit');
        Route::put('/{package}', [ClientPackageController::class, 'update'])->name('update');
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

    // ==================== GESTION DES DEMANDES DE COLLECTE ====================
    Route::prefix('pickup-requests')->name('pickup-requests.')->group(function () {
        Route::get('/', [ClientPickupRequestController::class, 'index'])->name('index');
        Route::get('/create', [ClientPickupRequestController::class, 'create'])->name('create');
        Route::post('/', [ClientPickupRequestController::class, 'store'])->name('store');
        Route::get('/{pickupRequest}', [ClientPickupRequestController::class, 'show'])->name('show');
        Route::post('/{pickupRequest}/cancel', [ClientPickupRequestController::class, 'cancel'])->name('cancel');

        // API Endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/stats', [ClientPickupRequestController::class, 'apiStats'])->name('stats');
            Route::get('/recent', [ClientPickupRequestController::class, 'apiRecent'])->name('recent');
        });
    });

    // ==================== GESTION DES ADRESSES DE COLLECTE ====================
    Route::prefix('pickup-addresses')->name('pickup-addresses.')->group(function () {
        Route::get('/', [ClientPickupAddressController::class, 'index'])->name('index');
        Route::get('/create', [ClientPickupAddressController::class, 'create'])->name('create');
        Route::post('/', [ClientPickupAddressController::class, 'store'])->name('store');
        Route::get('/{pickupAddress}/edit', [ClientPickupAddressController::class, 'edit'])->name('edit');
        Route::put('/{pickupAddress}', [ClientPickupAddressController::class, 'update'])->name('update');
        Route::delete('/{pickupAddress}', [ClientPickupAddressController::class, 'destroy'])->name('destroy');
        Route::post('/{pickupAddress}/set-default', [ClientPickupAddressController::class, 'setDefault'])->name('set-default');
    });

    // ==================== GESTION PORTEFEUILLE CLIENT ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [ClientWalletController::class, 'index'])->name('index');
        Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions');
        Route::get('/transaction/{transaction}', [ClientWalletController::class, 'showTransaction'])->name('transaction.show');
        Route::get('/statement', [ClientWalletController::class, 'downloadStatement'])->name('statement');
        Route::get('/export', [ClientWalletController::class, 'exportTransactions'])->name('export');
        
        // DEMANDES DE RECHARGEMENT CLIENT
        Route::get('/topup', [ClientWalletController::class, 'showTopupForm'])->name('topup');
        Route::post('/topup', [ClientWalletController::class, 'processTopup'])->name('process.topup');
        
        // Gestion des demandes de rechargement
        Route::prefix('topup')->name('topup.')->group(function () {
            Route::get('/requests', [ClientWalletController::class, 'topupRequests'])->name('requests');
            Route::get('/request/{topupRequest}', [ClientWalletController::class, 'showTopupRequest'])->name('request.show');
            Route::post('/request/{topupRequest}/cancel', [ClientWalletController::class, 'cancelTopupRequest'])->name('request.cancel');
            Route::get('/request/{topupRequest}/download-proof', [ClientWalletController::class, 'downloadTopupProof'])->name('request.download.proof');
        });

        // DEMANDES DE RETRAIT CLIENT
        Route::get('/withdrawal', [ClientWalletController::class, 'createWithdrawal'])->name('withdrawal');
        Route::post('/withdrawal', [ClientWalletController::class, 'storeWithdrawal'])->name('store.withdrawal');
        Route::get('/withdrawal/{withdrawal}', [ClientWalletController::class, 'showWithdrawal'])->name('withdrawal.show');
        Route::post('/withdrawal/{withdrawal}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdrawal.cancel');
    });

    // LISTE DES DEMANDES DE RETRAIT CLIENT
    Route::get('/withdrawals', [ClientWalletController::class, 'withdrawals'])->name('withdrawals');
    Route::post('/withdrawals/{withdrawal}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdrawals.cancel');

    // ==================== RÉCLAMATIONS CLIENT ====================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ClientComplaintController::class, 'index'])->name('index');
        Route::get('/create/{package}', [ClientComplaintController::class, 'create'])->name('create');
        Route::post('/{package}', [ClientComplaintController::class, 'store'])->name('store');
        Route::get('/{complaint}', [ClientComplaintController::class, 'show'])->name('show');
        Route::post('/{complaint}/respond', [ClientComplaintController::class, 'respond'])->name('respond');
        Route::post('/{complaint}/close', [ClientComplaintController::class, 'close'])->name('close');
        Route::post('/{complaint}/reopen', [ClientComplaintController::class, 'reopen'])->name('reopen');
        Route::post('/{complaint}/accept-resolution', [ClientComplaintController::class, 'acceptResolution'])->name('accept-resolution');
        Route::post('/{package}/change-cod', [ClientComplaintController::class, 'requestCodChange'])->name('change.cod');
        Route::post('/{package}/request-return', [ClientComplaintController::class, 'requestReturn'])->name('request.return');
        Route::post('/{package}/reschedule', [ClientComplaintController::class, 'requestReschedule'])->name('reschedule');
        Route::get('/{complaint}/timeline', [ClientComplaintController::class, 'showTimeline'])->name('timeline');
        Route::post('/{complaint}/mark-resolved', [ClientComplaintController::class, 'markResolved'])->name('mark.resolved');
    });

    // ==================== TICKETS CLIENT ====================
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [ClientTicketController::class, 'index'])->name('index');
        Route::get('/create', [ClientTicketController::class, 'create'])->name('create');
        Route::post('/', [ClientTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [ClientTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/messages', [ClientTicketController::class, 'addMessage'])->name('add.message');
        Route::post('/{ticket}/reply', [ClientTicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/mark-resolved', [ClientTicketController::class, 'markResolved'])->name('mark.resolved');
        Route::get('/from-complaint/{complaint}', [ClientTicketController::class, 'createFromComplaint'])->name('from.complaint');
    });

    // ==================== PROFIL CLIENT ====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ClientProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ClientProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ClientProfileController::class, 'update'])->name('update');
        Route::get('/download-identity', [ClientProfileController::class, 'downloadIdentityDocument'])->name('download-identity');
        Route::delete('/delete-identity', [ClientProfileController::class, 'deleteIdentityDocument'])->name('delete-identity');
        Route::post('/validate-fiscal', [ClientProfileController::class, 'validateFiscalNumber'])->name('validate-fiscal');
    });

    // ==================== MANIFESTES CLIENT ====================
    Route::prefix('manifests')->name('manifests.')->group(function () {
        Route::get('/', [ClientManifestController::class, 'index'])->name('index');
        Route::get('/create', [ClientManifestController::class, 'create'])->name('create');
        Route::post('/generate', [ClientManifestController::class, 'generate'])->name('generate');
        Route::get('/{manifest}', [ClientManifestController::class, 'show'])->name('show');
        Route::delete('/{manifest}', [ClientManifestController::class, 'destroy'])->name('destroy');
        Route::post('/{manifest}/remove-package', [ClientManifestController::class, 'removePackage'])->name('remove-package');
        Route::post('/{manifest}/add-package', [ClientManifestController::class, 'addPackage'])->name('add-package');
        Route::get('/{manifest}/details', [ClientManifestController::class, 'getDetails'])->name('details');
        Route::get('/{manifest}/pdf', [ClientManifestController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{manifest}/print', [ClientManifestController::class, 'printView'])->name('print');
        Route::post('/preview', [ClientManifestController::class, 'preview'])->name('preview');
        Route::get('/packages-by-pickup', [ClientManifestController::class, 'getPackagesByPickup'])->name('packages-by-pickup');
        Route::get('/api/available-packages', [ClientManifestController::class, 'getAvailablePackages'])->name('api.available-packages');
    });

    // ==================== COMPTES BANCAIRES CLIENT ====================
    Route::prefix('bank-accounts')->name('bank-accounts.')->group(function () {
        Route::get('/', [ClientBankAccountController::class, 'index'])->name('index');
        Route::get('/create', [ClientBankAccountController::class, 'create'])->name('create');
        Route::post('/', [ClientBankAccountController::class, 'store'])->name('store');
        Route::get('/{bankAccount}', [ClientBankAccountController::class, 'show'])->name('show');
        Route::get('/{bankAccount}/edit', [ClientBankAccountController::class, 'edit'])->name('edit');
        Route::put('/{bankAccount}', [ClientBankAccountController::class, 'update'])->name('update');
        Route::delete('/{bankAccount}', [ClientBankAccountController::class, 'destroy'])->name('destroy');
        Route::post('/{bankAccount}/set-default', [ClientBankAccountController::class, 'setDefault'])->name('set-default');
        Route::post('/validate-iban', [ClientBankAccountController::class, 'validateIban'])->name('validate-iban');
    });

    // ==================== NOTIFICATIONS CLIENT ====================
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

    // ==================== API ENDPOINTS CLIENT SEULEMENT ====================
    Route::prefix('api')->name('api.')->group(function () {
        // Dashboard APIs
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/stats', [ClientDashboardController::class, 'apiStats'])->name('stats');
            Route::get('/today-stats', [ClientDashboardController::class, 'apiTodayStats'])->name('today.stats');
            Route::get('/chart-data', [ClientDashboardController::class, 'apiChartData'])->name('chart.data');
            Route::get('/recent-activity', [ClientDashboardController::class, 'apiRecentActivity'])->name('recent.activity');
        });
        
        // Wallet APIs Client
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/balance', [ClientWalletController::class, 'apiBalance'])->name('balance');
            Route::get('/transactions', [ClientWalletController::class, 'apiTransactions'])->name('transactions');
            Route::get('/summary', [ClientWalletController::class, 'apiSummary'])->name('summary');
            Route::get('/check-balance', [ClientWalletController::class, 'apiCheckBalance'])->name('check.balance');
        });

        // APIs pour les demandes de rechargement CLIENT
        Route::prefix('topup')->name('topup.')->group(function () {
            Route::post('/check-bank-transfer-id', [ClientWalletController::class, 'apiCheckBankTransferId'])->name('check.bank.transfer.id');
            Route::get('/recent-requests', [ClientWalletController::class, 'apiRecentTopupRequests'])->name('recent.requests');
            Route::get('/stats', [ClientWalletController::class, 'apiTopupStats'])->name('stats');
            Route::get('/request/{topupRequest}/status', [ClientWalletController::class, 'apiTopupRequestStatus'])->name('request.status');
            Route::post('/create', [ClientWalletController::class, 'apiCreateTopupRequest'])->name('create');
        });
        
        // Package APIs Client
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/{package}/status', [ClientPackageController::class, 'apiStatus'])->name('status');
            Route::get('/summary', [ClientPackageController::class, 'apiSummary'])->name('summary');
            Route::get('/last-supplier-data', [ClientPackageController::class, 'apiLastSupplierData'])->name('last.supplier');
            Route::get('/session-data', [ClientPackageController::class, 'apiSessionData'])->name('session.data');
            Route::get('/today-stats', [ClientPackageController::class, 'apiTodayStats'])->name('today.stats');
        });
        
        // Notification APIs Client
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/unread-count', [ClientNotificationController::class, 'apiUnreadCount'])->name('unread.count');
            Route::get('/recent', [ClientNotificationController::class, 'apiRecent'])->name('recent');
            Route::post('/mark-read', [ClientNotificationController::class, 'apiMarkRead'])->name('mark.read');
            Route::get('/poll', [ClientNotificationController::class, 'apiPollNotifications'])->name('poll');
        });
    });

    // ==================== WEBHOOKS CLIENT SEULEMENT ====================
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('/package-status-changed', [ClientPackageController::class, 'webhookPackageStatusChanged'])->name('package.status.changed');
        Route::post('/payment-received', [ClientWalletController::class, 'webhookPaymentReceived'])->name('payment.received');
        Route::post('/withdrawal-processed', [ClientWalletController::class, 'webhookWithdrawalProcessed'])->name('withdrawal.processed');
    });

    // ==================== GESTION TOKEN API ====================
    Route::prefix('settings/api')->name('api.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\ClientApiTokenController::class, 'index'])->name('index');
        Route::post('/token/generate', [\App\Http\Controllers\Client\ClientApiTokenController::class, 'generate'])->name('token.generate');
        Route::post('/token/regenerate', [\App\Http\Controllers\Client\ClientApiTokenController::class, 'regenerate'])->name('token.regenerate');
        Route::delete('/token', [\App\Http\Controllers\Client\ClientApiTokenController::class, 'delete'])->name('token.delete');
        Route::get('/history', [\App\Http\Controllers\Client\ClientApiTokenController::class, 'history'])->name('history');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Publiques pour Tracking SEULEMENT
|--------------------------------------------------------------------------
*/
Route::prefix('track')->name('public.track.')->group(function () {
    Route::get('/{package_code}', [ClientPackageController::class, 'publicTracking'])->name('package');
    Route::post('/check', [ClientPackageController::class, 'publicTrackingCheck'])->name('check');
    Route::get('/qr/{package_code}', [ClientPackageController::class, 'qrTracking'])->name('qr');
});
