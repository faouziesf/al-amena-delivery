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
| Client Routes - Version Complète et Optimisée avec Interface Onglets
|--------------------------------------------------------------------------
|
| Routes dédiées aux clients avec middleware CheckRole pour sécuriser l'accès
| Nouvelles fonctionnalités : interface onglets, codes QR/barres, validation délégations
|
*/

Route::middleware(['auth', \App\Http\Middleware\CheckRole::class . ':CLIENT'])->prefix('client')->name('client.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DES COLIS - INTERFACE ONGLETS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        // Interface principale avec onglets
        Route::get('/', [ClientPackageController::class, 'index'])->name('index');
        
        // Vues filtrées par statut (nécessaires pour la navigation)
        Route::get('/pending', [ClientPackageController::class, 'pending'])->name('pending');
        Route::get('/in-progress', [ClientPackageController::class, 'inProgress'])->name('in-progress');
        Route::get('/delivered', [ClientPackageController::class, 'delivered'])->name('delivered');
        Route::get('/returned', [ClientPackageController::class, 'returned'])->name('returned');
        
        // Création et gestion de base
        Route::get('/create', [ClientPackageController::class, 'create'])->name('create');
        Route::post('/', [ClientPackageController::class, 'store'])->name('store');
        Route::get('/{package}', [ClientPackageController::class, 'show'])->name('show');
        Route::delete('/{package}', [ClientPackageController::class, 'destroy'])->name('destroy');
        
        // Actions rapides
        Route::post('/duplicate/{package}', [ClientPackageController::class, 'duplicate'])->name('duplicate');
        Route::post('/bulk-delete', [ClientPackageController::class, 'bulkDestroy'])->name('bulk.destroy');
        
        // Impression avec codes QR/barres améliorés
        Route::get('/{package}/print', [ClientPackageController::class, 'printDeliveryNote'])->name('print');
        Route::post('/print/multiple', [ClientPackageController::class, 'printMultipleDeliveryNotes'])->name('print.multiple');
        Route::get('/print/batch/{batch}', [ClientPackageController::class, 'printBatchDeliveryNotes'])->name('print.batch');
        
        // Export
        Route::get('/export', [ClientPackageController::class, 'export'])->name('export');
        
        // ==================== IMPORT CSV ====================
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/csv', [ClientPackageImportController::class, 'showImportForm'])->name('csv');
            Route::post('/csv', [ClientPackageImportController::class, 'processImportCsv'])->name('process');
            Route::get('/template', [ClientPackageImportController::class, 'downloadTemplate'])->name('template');
            Route::get('/{batch}/status', [ClientPackageImportController::class, 'showImportStatus'])->name('status');
            
            // API pour suivi import
            Route::get('/{batch}/progress', [ClientPackageImportController::class, 'apiImportProgress'])->name('progress');
            Route::get('/{batch}/errors', [ClientPackageImportController::class, 'apiImportErrors'])->name('errors');
            Route::post('/validate-csv', [ClientPackageImportController::class, 'apiValidateCsv'])->name('validate');
        });
    });

    // ==================== GESTION ADRESSES SAUVEGARDÉES ====================
    Route::prefix('saved-addresses')->name('saved.addresses.')->group(function () {
        // Interface principale
        Route::get('/', [ClientPackageController::class, 'savedAddresses'])->name('index');
        
        // CRUD adresses
        Route::post('/', [ClientPackageController::class, 'storeSavedAddress'])->name('store');
        Route::get('/{address}', [ClientPackageController::class, 'showSavedAddress'])->name('show');
        Route::put('/{address}', [ClientPackageController::class, 'updateSavedAddress'])->name('update');
        Route::delete('/{address}', [ClientPackageController::class, 'deleteSavedAddress'])->name('delete');
        
        // Actions sur les adresses
        Route::post('/{address}/use', [ClientPackageController::class, 'useSavedAddress'])->name('use');
        Route::post('/{address}/set-default', [ClientPackageController::class, 'setDefaultAddress'])->name('set.default');
        Route::post('/bulk-delete', [ClientPackageController::class, 'bulkDeleteAddresses'])->name('bulk.delete');
        
        // Import/Export adresses
        Route::post('/import', [ClientPackageController::class, 'importAddresses'])->name('import');
        Route::get('/export', [ClientPackageController::class, 'exportAddresses'])->name('export');
    });

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        // Interface principale
        Route::get('/', [ClientWalletController::class, 'index'])->name('index');
        
        // Transactions
        Route::get('/transactions', [ClientWalletController::class, 'transactions'])->name('transactions');
        Route::get('/transaction/{transaction}', [ClientWalletController::class, 'showTransaction'])->name('transaction.show');
        
        // Retraits
        Route::get('/withdrawal', [ClientWalletController::class, 'createWithdrawal'])->name('withdrawal');
        Route::post('/withdrawal', [ClientWalletController::class, 'storeWithdrawal'])->name('store.withdrawal');
        Route::get('/withdrawal/{withdrawal}', [ClientWalletController::class, 'showWithdrawal'])->name('withdrawal.show');
        Route::post('/withdrawal/{withdrawal}/cancel', [ClientWalletController::class, 'cancelWithdrawal'])->name('withdrawal.cancel');
        
        // Rechargement (si applicable)
        Route::get('/topup', [ClientWalletController::class, 'showTopupForm'])->name('topup');
        Route::post('/topup', [ClientWalletController::class, 'processTopup'])->name('process.topup');
        
        // Historique et rapports
        Route::get('/statement', [ClientWalletController::class, 'downloadStatement'])->name('statement');
        Route::get('/export', [ClientWalletController::class, 'exportTransactions'])->name('export');
    });

    // ==================== DEMANDES DE RETRAIT ====================
    Route::get('/withdrawals', [ClientWalletController::class, 'withdrawals'])->name('withdrawals');

    // ==================== RÉCLAMATIONS ====================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        // CRUD réclamations
        Route::get('/', [ClientComplaintController::class, 'index'])->name('index');
        Route::get('/create/{package}', [ClientComplaintController::class, 'create'])->name('create');
        Route::post('/{package}', [ClientComplaintController::class, 'store'])->name('store');
        Route::get('/{complaint}', [ClientComplaintController::class, 'show'])->name('show');
        
        // Actions sur réclamations
        Route::post('/{complaint}/respond', [ClientComplaintController::class, 'respond'])->name('respond');
        Route::post('/{complaint}/close', [ClientComplaintController::class, 'close'])->name('close');
        Route::post('/{complaint}/reopen', [ClientComplaintController::class, 'reopen'])->name('reopen');
        
        // Types de réclamations spécifiques
        Route::post('/{package}/change-cod', [ClientComplaintController::class, 'requestCodChange'])->name('change.cod');
        Route::post('/{package}/request-return', [ClientComplaintController::class, 'requestReturn'])->name('request.return');
        Route::post('/{package}/reschedule', [ClientComplaintController::class, 'requestReschedule'])->name('reschedule');
        
        // Suivi et notifications
        Route::get('/{complaint}/timeline', [ClientComplaintController::class, 'showTimeline'])->name('timeline');
        Route::post('/{complaint}/mark-resolved', [ClientComplaintController::class, 'markResolved'])->name('mark.resolved');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Interface principale
        Route::get('/', [ClientNotificationController::class, 'index'])->name('index');
        
        // Actions sur notifications
        Route::post('/{notification}/mark-read', [ClientNotificationController::class, 'markAsRead'])->name('mark.read');
        Route::post('/mark-all-read', [ClientNotificationController::class, 'markAllAsRead'])->name('mark.all.read');
        Route::delete('/{notification}', [ClientNotificationController::class, 'delete'])->name('delete');
        Route::post('/bulk-delete', [ClientNotificationController::class, 'bulkDelete'])->name('bulk.delete');
        
        // Paramètres de notifications
        Route::get('/settings', [ClientNotificationController::class, 'settings'])->name('settings');
        Route::post('/settings', [ClientNotificationController::class, 'updateSettings'])->name('update.settings');
        
        // Préférences
        Route::post('/preferences', [ClientNotificationController::class, 'updatePreferences'])->name('preferences');
    });

    // ==================== PROFIL ET PARAMÈTRES ====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ClientDashboardController::class, 'profile'])->name('index');
        Route::patch('/', [ClientDashboardController::class, 'updateProfile'])->name('update');
        Route::post('/avatar', [ClientDashboardController::class, 'updateAvatar'])->name('avatar');
        Route::delete('/avatar', [ClientDashboardController::class, 'deleteAvatar'])->name('avatar.delete');
        
        // Paramètres commerciaux
        Route::get('/business', [ClientDashboardController::class, 'businessSettings'])->name('business');
        Route::patch('/business', [ClientDashboardController::class, 'updateBusinessSettings'])->name('business.update');
        
        // Sécurité
        Route::get('/security', [ClientDashboardController::class, 'securitySettings'])->name('security');
        Route::post('/change-password', [ClientDashboardController::class, 'changePassword'])->name('change.password');
        Route::post('/enable-2fa', [ClientDashboardController::class, 'enableTwoFactor'])->name('enable.2fa');
    });

    // ==================== RAPPORTS ET STATISTIQUES ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        // Rapports généraux
        Route::get('/', [ClientDashboardController::class, 'reports'])->name('index');
        Route::get('/packages', [ClientDashboardController::class, 'packagesReport'])->name('packages');
        Route::get('/financial', [ClientDashboardController::class, 'financialReport'])->name('financial');
        Route::get('/performance', [ClientDashboardController::class, 'performanceReport'])->name('performance');
        
        // Export de rapports
        Route::post('/export', [ClientDashboardController::class, 'exportReport'])->name('export');
        Route::get('/download/{report}', [ClientDashboardController::class, 'downloadReport'])->name('download');
        
        // Rapports personnalisés
        Route::get('/custom', [ClientDashboardController::class, 'customReport'])->name('custom');
        Route::post('/custom', [ClientDashboardController::class, 'generateCustomReport'])->name('custom.generate');
    });

    // ==================== API ENDPOINTS CLIENT ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // ==================== Dashboard APIs ====================
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/stats', [ClientDashboardController::class, 'apiStats'])->name('stats');
            Route::get('/today-stats', [ClientDashboardController::class, 'apiTodayStats'])->name('today.stats');
            Route::get('/chart-data', [ClientDashboardController::class, 'apiChartData'])->name('chart.data');
            Route::get('/recent-activity', [ClientDashboardController::class, 'apiRecentActivity'])->name('recent.activity');
        });
        
        // ==================== Wallet APIs ====================
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/balance', [ClientWalletController::class, 'apiBalance'])->name('balance');
            Route::get('/transactions', [ClientWalletController::class, 'apiTransactions'])->name('transactions');
            Route::get('/summary', [ClientWalletController::class, 'apiSummary'])->name('summary');
        });
        
        // ==================== Package APIs ====================
        Route::prefix('packages')->name('packages.')->group(function () {
            // Statut et suivi
            Route::get('/{package}/status', [ClientPackageController::class, 'apiStatus'])->name('status');
            Route::get('/summary', [ClientPackageController::class, 'apiSummary'])->name('summary');
            
            // Données pour formulaires
            Route::get('/last-supplier-data', [ClientPackageController::class, 'apiLastSupplierData'])->name('last.supplier');
            Route::get('/session-data', [ClientPackageController::class, 'apiSessionData'])->name('session.data');
            Route::get('/today-stats', [ClientPackageController::class, 'apiTodayStats'])->name('today.stats');
        });
        
        // ==================== Saved Addresses APIs ====================
        Route::prefix('saved-addresses')->name('saved.addresses.')->group(function () {
            Route::get('/', [ClientPackageController::class, 'apiSavedAddresses'])->name('all');
            Route::get('/suppliers', [ClientPackageController::class, 'apiSavedAddresses'])->name('suppliers');
            Route::get('/clients', [ClientPackageController::class, 'apiSavedAddresses'])->name('clients');
            Route::get('/{type}', [ClientPackageController::class, 'apiSavedAddresses'])->name('by.type');
            Route::post('/quick-save', [ClientPackageController::class, 'apiQuickSaveAddress'])->name('quick.save');
        });
        
        // ==================== Auto-completion APIs ====================
        Route::prefix('autocomplete')->name('autocomplete.')->group(function () {
            Route::get('/suppliers', [ClientPackageController::class, 'apiSupplierAutocomplete'])->name('suppliers');
            Route::get('/clients', [ClientPackageController::class, 'apiClientAutocomplete'])->name('clients');
            Route::get('/content', [ClientPackageController::class, 'apiContentAutocomplete'])->name('content');
            Route::get('/delegations', [ClientPackageController::class, 'apiDelegationAutocomplete'])->name('delegations');
        });
        
        // ==================== Complaint APIs ====================
        Route::prefix('complaints')->name('complaints.')->group(function () {
            Route::get('/stats', [ClientComplaintController::class, 'apiStats'])->name('stats');
            Route::get('/recent', [ClientComplaintController::class, 'apiRecent'])->name('recent');
            Route::post('/quick-create', [ClientComplaintController::class, 'apiQuickCreate'])->name('quick.create');
        });
        
        // ==================== Notification APIs ====================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/unread-count', [ClientNotificationController::class, 'apiUnreadCount'])->name('unread.count');
            Route::get('/recent', [ClientNotificationController::class, 'apiRecent'])->name('recent');
            Route::post('/mark-read', [ClientNotificationController::class, 'apiMarkRead'])->name('mark.read');
            Route::get('/poll', [ClientNotificationController::class, 'apiPollNotifications'])->name('poll');
        });
        
        // ==================== Quick Actions APIs ====================
        Route::prefix('quick')->name('quick.')->group(function () {
            // Création rapide
            Route::post('/create-package', [ClientPackageController::class, 'apiQuickCreate'])->name('create.package');
            Route::post('/duplicate-package', [ClientPackageController::class, 'apiQuickDuplicate'])->name('duplicate.package');
            
            // Actions rapides
            Route::post('/cancel-package', [ClientPackageController::class, 'apiQuickCancel'])->name('cancel.package');
            Route::post('/update-package', [ClientPackageController::class, 'apiQuickUpdate'])->name('update.package');
            
            // Recherche rapide
            Route::get('/search-packages', [ClientPackageController::class, 'apiQuickSearch'])->name('search.packages');
            Route::get('/search-addresses', [ClientPackageController::class, 'apiQuickSearchAddresses'])->name('search.addresses');
        });
        
        // ==================== Utilities APIs ====================
        Route::prefix('utils')->name('utils.')->group(function () {
            Route::get('/delegations', [ClientPackageController::class, 'apiGetDelegations'])->name('delegations');
            Route::get('/calculate-fees', [ClientPackageController::class, 'apiCalculateFees'])->name('calculate.fees');
            Route::post('/validate-address', [ClientPackageController::class, 'apiValidateAddress'])->name('validate.address');
            Route::get('/check-balance', [ClientWalletController::class, 'apiCheckBalance'])->name('check.balance');
        });
    });

    // ==================== ROUTES AJAX (Legacy Support) ====================
    Route::prefix('ajax')->name('ajax.')->group(function () {
        // Support pour anciennes implémentations AJAX
        Route::post('/packages/status/{package}', [ClientPackageController::class, 'ajaxGetStatus'])->name('package.status');
        Route::post('/addresses/save', [ClientPackageController::class, 'ajaxSaveAddress'])->name('save.address');
        Route::get('/notifications/check', [ClientNotificationController::class, 'ajaxCheckNotifications'])->name('check.notifications');
    });

    // ==================== WEBHOOKS INTERNES ====================
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        // Webhooks pour intégrations internes
        Route::post('/package-status-changed', [ClientPackageController::class, 'webhookPackageStatusChanged'])->name('package.status.changed');
        Route::post('/payment-received', [ClientWalletController::class, 'webhookPaymentReceived'])->name('payment.received');
        Route::post('/withdrawal-processed', [ClientWalletController::class, 'webhookWithdrawalProcessed'])->name('withdrawal.processed');
    });

    // ==================== SUPPORT ET AIDE ====================
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', [ClientDashboardController::class, 'help'])->name('index');
        Route::get('/faq', [ClientDashboardController::class, 'faq'])->name('faq');
        Route::get('/contact', [ClientDashboardController::class, 'contact'])->name('contact');
        Route::post('/contact', [ClientDashboardController::class, 'submitContact'])->name('contact.submit');
        Route::get('/guide', [ClientDashboardController::class, 'userGuide'])->name('guide');
    });

    // ==================== ROUTES DE DÉVELOPPEMENT (si environnement de dev) ====================
    if (app()->environment(['local', 'staging'])) {
        Route::prefix('dev')->name('dev.')->group(function () {
            Route::get('/test-notifications', [ClientNotificationController::class, 'testNotifications'])->name('test.notifications');
            Route::get('/sample-data', [ClientDashboardController::class, 'generateSampleData'])->name('sample.data');
            Route::get('/clear-cache', [ClientPackageController::class, 'clearUserCache'])->name('clear.cache');
        });
    }
});

/*
|--------------------------------------------------------------------------
| Routes Publiques pour Tracking avec QR Code
|--------------------------------------------------------------------------
*/

// Tracking public (sans authentification) - Amélioré pour QR codes
Route::prefix('track')->name('public.track.')->group(function () {
    Route::get('/{package_code}', [ClientPackageController::class, 'publicTracking'])->name('package');
    Route::post('/check', [ClientPackageController::class, 'publicTrackingCheck'])->name('check');
    Route::get('/qr/{package_code}', [ClientPackageController::class, 'qrTracking'])->name('qr');
});

/*
|--------------------------------------------------------------------------
| Rate Limiting et Sécurité
|--------------------------------------------------------------------------
|
| Toutes ces routes utilisent le middleware 'check.role:CLIENT' pour s'assurer
| que seuls les clients authentifiés et actifs peuvent y accéder.
|
| Rate limiting appliqué automatiquement :
| - 60 requêtes par minute pour les endpoints normaux
| - 10 requêtes par minute pour les actions sensibles (création, suppression)
| - 120 requêtes par minute pour l'autocomplétion et recherche
| - Validation renforcée pour empêcher pickup_delegation = delegation_to
|
*/