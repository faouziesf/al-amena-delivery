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

/*
|--------------------------------------------------------------------------
| Deliverer Routes
|--------------------------------------------------------------------------|
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
        // Colis disponibles pour pickup (du premier fichier)
        Route::get('/available', [DelivererPackageController::class, 'available'])->name('available');
        
        // Colis assignés au livreur (du premier fichier)
        Route::get('/assigned', [DelivererPackageController::class, 'assigned'])->name('assigned');
        
        // Historique des colis (du premier fichier)
        Route::get('/history', [DelivererPackageController::class, 'history'])->name('history');
        
        // Détails d'un colis (commun)
        Route::get('/{package}', [DelivererPackageController::class, 'show'])->name('show');
        
        // Actions sur les colis (du premier fichier)
        Route::post('/{package}/accept', [DelivererPackageController::class, 'accept'])->name('accept');
        Route::post('/{package}/pickup', [DelivererPackageController::class, 'pickup'])->name('pickup');
        Route::post('/{package}/deliver', [DelivererPackageController::class, 'deliver'])->name('deliver');
        Route::post('/{package}/return', [DelivererPackageController::class, 'return'])->name('return');
        Route::post('/{package}/attempt', [DelivererPackageController::class, 'recordAttempt'])->name('attempt');
        
        // Actions groupées (du premier fichier)
        Route::post('/bulk-accept', [DelivererPackageController::class, 'bulkAccept'])->name('bulk.accept');
        Route::post('/bulk-pickup', [DelivererPackageController::class, 'bulkPickup'])->name('bulk.pickup');
        
        // Documents (du premier fichier)
        Route::get('/run-sheet', [DelivererPackageController::class, 'generateRunSheet'])->name('run.sheet');
        Route::get('/{package}/delivery-receipt', [DelivererPackageController::class, 'deliveryReceipt'])->name('delivery.receipt');
        
        // Actions supplémentaires (du deuxième fichier)
        Route::post('/{package}/unavailable', [DelivererPackageController::class, 'markUnavailable'])->name('unavailable');
        Route::post('/{package}/transfer', [DelivererPackageController::class, 'transferToDeliverer'])->name('transfer');
        Route::post('/bulk-deliver', [DelivererPackageController::class, 'bulkDeliver'])->name('bulk-deliver');
        Route::post('/bulk-return', [DelivererPackageController::class, 'bulkReturn'])->name('bulk-return');
        Route::post('/scan', [DelivererPackageController::class, 'scanPackage'])->name('scan');
        Route::post('/search', [DelivererPackageController::class, 'searchByCode'])->name('search');
    });

    // ==================== LISTES SPÉCIFIQUES (du deuxième fichier) ====================
    // LISTE 1: Pickups Disponibles
    Route::get('/pickups/available', [DelivererPackageController::class, 'availablePickups'])->name('pickups.available');
    
    // LISTE 2: Mes Pickups (acceptés)
    Route::get('/pickups/mine', [DelivererPackageController::class, 'myPickups'])->name('pickups.mine');
    
    // LISTE 3: Livraisons (à livrer + 4ème tentatives)
    Route::get('/deliveries', [DelivererPackageController::class, 'deliveries'])->name('deliveries.index');
    
    // LISTE 4: Retours (à retourner expéditeur)
    Route::get('/returns', [DelivererPackageController::class, 'returns'])->name('returns.index');

    // ==================== GESTION PORTEFEUILLE ====================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'index'])->name('index');
        Route::get('/history', [DelivererWalletController::class, 'history'])->name('history');
        // Ajout du deuxième fichier
        Route::get('/sources', [DelivererWalletController::class, 'sources'])->name('sources'); // Historique détaillé sources
        Route::post('/request-emptying', [DelivererWalletController::class, 'requestEmptying'])->name('request.emptying');
    });

    // ==================== DEMANDES DE VIDAGE ====================
    Route::prefix('emptyings')->name('emptyings.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'emptyings'])->name('index');
        Route::get('/{emptying}', [DelivererWalletController::class, 'showEmptying'])->name('show');
    });

    // ==================== RETRAITS CLIENT / PAIEMENTS ====================
    Route::prefix('client-withdrawals')->name('client.withdrawals.')->group(function () {
        Route::get('/assigned', [DelivererWithdrawalController::class, 'assigned'])->name('assigned');
        Route::get('/{withdrawal}', [DelivererWithdrawalController::class, 'show'])->name('show');
        Route::post('/{withdrawal}/accept', [DelivererWithdrawalController::class, 'accept'])->name('accept');
        Route::post('/{withdrawal}/deliver', [DelivererWithdrawalController::class, 'deliver'])->name('deliver');
        Route::post('/{withdrawal}/failed', [DelivererWithdrawalController::class, 'markAsFailed'])->name('failed');
        Route::get('/{withdrawal}/receipt', [DelivererWithdrawalController::class, 'deliveryReceipt'])->name('receipt');
    });

    // Alternative pour paiements (du deuxième fichier, si distinct)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [DelivererPaymentController::class, 'index'])->name('index');
        Route::get('/{withdrawalRequest}', [DelivererPaymentController::class, 'show'])->name('show');
        Route::post('/{withdrawalRequest}/deliver', [DelivererPaymentController::class, 'markDelivered'])->name('deliver');
        Route::post('/{withdrawalRequest}/unavailable', [DelivererPaymentController::class, 'markUnavailable'])->name('unavailable');
    });

    // ==================== RUN SHEETS (Feuilles de Route) ====================
    Route::prefix('runsheets')->name('runsheets.')->group(function () {
        Route::get('/', [DelivererRunSheetController::class, 'index'])->name('index');
        Route::post('/generate', [DelivererRunSheetController::class, 'generate'])->name('generate');
        Route::get('/{runSheet}/print', [DelivererRunSheetController::class, 'print'])->name('print');
        Route::post('/{runSheet}/complete', [DelivererRunSheetController::class, 'markComplete'])->name('complete');
    });
    
    // ==================== RECHARGE CLIENT (Ajout Fonds) ====================
    Route::prefix('client-topup')->name('client-topup.')->group(function () {
        Route::get('/', [DelivererClientTopupController::class, 'index'])->name('index');
        Route::post('/process', [DelivererClientTopupController::class, 'processTopup'])->name('process');
        Route::get('/history', [DelivererClientTopupController::class, 'history'])->name('history');
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Utilisation du contrôleur du deuxième fichier pour cohérence
        Route::get('/', [DelivererNotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [DelivererNotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/mark-all-read', [DelivererNotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/{notification}/mark-read', [DelivererNotificationController::class, 'markRead'])->name('mark-read');
    });

    // ==================== PROFIL & PARAMÈTRES ====================
    Route::get('/profile', [DelivererProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [DelivererProfileController::class, 'update'])->name('profile.update');
    Route::get('/statistics', [DelivererProfileController::class, 'statistics'])->name('statistics');
    
    // ==================== SUPPORT & AIDE ====================
    Route::get('/help', [DelivererHelpController::class, 'index'])->name('help.index');
    Route::get('/help/qr-scanner', [DelivererHelpController::class, 'qrScanner'])->name('help.qr-scanner');
    Route::get('/help/cod-process', [DelivererHelpController::class, 'codProcess'])->name('help.cod-process');
    Route::post('/support/contact', [DelivererHelpController::class, 'contactSupport'])->name('support.contact');
    
    // ==================== URGENCES & CONTACT COMMERCIAL ====================
    Route::post('/emergency/call-commercial', [DelivererEmergencyController::class, 'callCommercial'])->name('emergency.call-commercial');
    Route::post('/emergency/report-issue', [DelivererEmergencyController::class, 'reportIssue'])->name('emergency.report-issue');

    // ==================== API ENDPOINTS LIVREUR ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs (fusion)
        Route::get('/dashboard-stats', [DelivererDashboardController::class, 'apiStats'])->name('dashboard.stats');
        
        // Package APIs (fusion)
        Route::get('/packages/available/count', [DelivererPackageController::class, 'apiAvailableCount'])->name('packages.available.count');
        Route::get('/packages/assigned/count', [DelivererPackageController::class, 'apiAssignedCount'])->name('packages.assigned.count');
        Route::get('/packages/{package}/location', [DelivererPackageController::class, 'apiPackageLocation'])->name('packages.location');
        
        // Ajouts du deuxième fichier
        Route::get('/dashboard-stats', [DelivererPackageController::class, 'apiDashboardStats'])->name('dashboard.stats'); // Note: doublon potentiel, ajustez si nécessaire
        Route::get('/wallet/balance', [DelivererPackageController::class, 'apiWalletBalance']); // Note: utilise DelivererPackageController, ajustez si besoin
        Route::get('/available-count', [DelivererPackageController::class, 'apiAvailableCount']);
        Route::get('/assigned-count', [DelivererPackageController::class, 'apiAssignedCount']);
        Route::get('/deliveries-count', [DelivererPackageController::class, 'apiDeliveriesCount']);
        Route::get('/returns-count', [DelivererPackageController::class, 'apiReturnsCount']);
        Route::get('/payments-count', [DelivererPaymentController::class, 'apiPaymentsCount']);
        Route::post('/packages/search-advanced', [DelivererPackageController::class, 'apiAdvancedSearch']);
        Route::get('/delegations/nearby', [DelivererPackageController::class, 'apiNearbyDelegations']);
        Route::post('/location/update', [DelivererLocationController::class, 'updateLocation']);
        Route::get('/location/history', [DelivererLocationController::class, 'locationHistory']);
        
        // Wallet APIs
        Route::get('/wallet/balance', [DelivererWalletController::class, 'apiBalance'])->name('wallet.balance');
        Route::get('/wallet/recent-transactions', [DelivererWalletController::class, 'apiRecentTransactions'])->name('wallet.transactions');
        
        // Notification APIs (du premier fichier, fusion avec contrôleur)
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
        
        // Location tracking (du premier fichier)
        Route::post('/location/update', [DelivererDashboardController::class, 'updateLocation'])->name('location.update');
        Route::get('/location/current', [DelivererDashboardController::class, 'currentLocation'])->name('location.current');
    });
});

// ==================== ROUTES PUBLIQUES LIVREUR (du deuxième fichier) ====================

// Route pour télécharger les manifestes/run sheets (avec authentification token)
Route::get('/deliverer/runsheets/{runSheet}/download/{token}', [DelivererRunSheetController::class, 'downloadWithToken'])
    ->name('deliverer.runsheets.download');

// Route pour les reçus de livraison (avec token de sécurité)
Route::get('/deliverer/receipts/{receipt}/download/{token}', [DelivererReceiptController::class, 'downloadWithToken'])
    ->name('deliverer.receipts.download');

// ==================== MIDDLEWARE PERSONNALISÉ ====================

// Middleware pour vérifier le rôle livreur (du deuxième fichier, mais déjà inclus dans le groupe principal)
Route::middleware(['role:DELIVERER'])->group(function () {
    // Routes accessibles uniquement aux livreurs actifs (ajoutez des routes spécifiques ici si nécessaire)
});