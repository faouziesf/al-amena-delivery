<?php

use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\ClientPackageController;
use App\Http\Controllers\Client\ClientWalletController;
use App\Http\Controllers\Client\ClientComplaintController;
use App\Http\Controllers\Client\ClientNotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client Routes - Version Optimisée
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:CLIENT'])->prefix('client')->name('client.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [ClientPackageController::class, 'index'])->name('index');
        Route::get('/create', [ClientPackageController::class, 'create'])->name('create');
        Route::post('/', [ClientPackageController::class, 'store'])->name('store');
        Route::get('/{package}', [ClientPackageController::class, 'show'])->name('show');
        
        // Import CSV (si disponible)
        Route::get('/import/csv', [ClientPackageController::class, 'importCsvForm'])->name('import.csv');
        Route::post('/import/csv', [ClientPackageController::class, 'processImportCsv'])->name('process.import.csv');
        
        // Actions rapides
        Route::post('/duplicate/{package}', [ClientPackageController::class, 'duplicate'])->name('duplicate');
        Route::post('/create-similar/{package}', [ClientPackageController::class, 'createSimilar'])->name('create.similar');
    });

    // ==================== GESTION ADRESSES SAUVEGARDÉES ====================
    Route::prefix('saved-addresses')->name('saved.addresses.')->group(function () {
        Route::get('/', [ClientPackageController::class, 'savedAddresses'])->name('index');
        Route::post('/', [ClientPackageController::class, 'storeSavedAddress'])->name('store');
        Route::put('/{address}', [ClientPackageController::class, 'updateSavedAddress'])->name('update');
        Route::delete('/{address}', [ClientPackageController::class, 'deleteSavedAddress'])->name('delete');
        Route::post('/{address}/use', [ClientPackageController::class, 'useSavedAddress'])->name('use');
    });

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [ClientWalletController::class, 'index'])->name('index');
        Route::get('/withdrawal', [ClientWalletController::class, 'createWithdrawal'])->name('withdrawal');
        Route::post('/withdrawal', [ClientWalletController::class, 'storeWithdrawal'])->name('store-withdrawal');
    });

    // ==================== DEMANDES DE RETRAIT ====================
    Route::get('/withdrawals', [ClientWalletController::class, 'withdrawals'])->name('withdrawals');

    // ==================== RÉCLAMATIONS ====================
    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ClientComplaintController::class, 'index'])->name('index');
        Route::get('/create/{package}', [ClientComplaintController::class, 'create'])->name('create');
        Route::post('/{package}', [ClientComplaintController::class, 'store'])->name('store');
        Route::get('/{complaint}', [ClientComplaintController::class, 'show'])->name('show');
        Route::post('/{complaint}/respond', [ClientComplaintController::class, 'respond'])->name('respond');
        Route::post('/{complaint}/close', [ClientComplaintController::class, 'close'])->name('close');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [ClientNotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/mark-read', [ClientNotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [ClientNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [ClientNotificationController::class, 'delete'])->name('delete');
    });

    // ==================== API ENDPOINTS CLIENT ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/dashboard-stats', [ClientDashboardController::class, 'apiStats'])->name('dashboard.stats');
        Route::get('/today-stats', [ClientDashboardController::class, 'apiTodayStats'])->name('today.stats');
        
        // Wallet APIs
        Route::get('/wallet/balance', [ClientWalletController::class, 'apiBalance'])->name('wallet.balance');
        Route::get('/wallet/transactions', [ClientWalletController::class, 'apiTransactions'])->name('wallet.transactions');
        
        // Package APIs
        Route::get('/packages/{package}/status', [ClientPackageController::class, 'apiStatus'])->name('packages.status');
        Route::get('/packages/last-supplier-data', [ClientPackageController::class, 'apiLastSupplierData'])->name('packages.last.supplier');
        
        // Saved Addresses APIs
        Route::get('/saved-addresses', [ClientPackageController::class, 'apiSavedAddresses'])->name('saved.addresses.all');
        Route::get('/saved-addresses/suppliers', [ClientPackageController::class, 'apiSavedAddresses'])->name('saved.addresses.suppliers');
        Route::get('/saved-addresses/clients', [ClientPackageController::class, 'apiSavedAddresses'])->name('saved.addresses.clients');
        Route::get('/saved-addresses/{type}', [ClientPackageController::class, 'apiSavedAddresses'])->name('saved.addresses.by.type');
        
        // Auto-completion APIs
        Route::get('/autocomplete/suppliers', [ClientPackageController::class, 'apiSupplierAutocomplete'])->name('autocomplete.suppliers');
        Route::get('/autocomplete/clients', [ClientPackageController::class, 'apiClientAutocomplete'])->name('autocomplete.clients');
        Route::get('/autocomplete/content', [ClientPackageController::class, 'apiContentAutocomplete'])->name('autocomplete.content');
        
        // Complaint APIs
        Route::get('/complaints/stats', [ClientComplaintController::class, 'apiStats'])->name('complaints.stats');
        
        // Notification APIs
        Route::get('/notifications/unread-count', [ClientNotificationController::class, 'apiUnreadCount'])->name('notifications.unread.count');
        Route::get('/notifications/recent', [ClientNotificationController::class, 'apiRecent'])->name('notifications.recent');
        Route::post('/notifications/mark-read', [ClientNotificationController::class, 'apiMarkRead'])->name('notifications.mark.read');
        
        // Quick Actions APIs
        Route::post('/quick-create-package', [ClientPackageController::class, 'apiQuickCreate'])->name('quick.create.package');
        Route::get('/session-data', [ClientPackageController::class, 'apiSessionData'])->name('session.data');
        Route::post('/save-session-data', [ClientPackageController::class, 'apiSaveSessionData'])->name('save.session.data');
    });
});