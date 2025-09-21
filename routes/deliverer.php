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
        // Liste principale des colis
        Route::get('/', [DelivererPackageController::class, 'index'])->name('index');

        // Détails d'un colis
        Route::get('/{package}', [DelivererPackageController::class, 'show'])->name('show');

        // ACTIONS CRITIQUES SCANNER - NOUVELLES/AMÉLIORÉES
        Route::post('/{package}/accept', [DelivererPackageController::class, 'acceptPickup'])->name('accept');
        Route::post('/{package}/pickup', [DelivererPackageController::class, 'markPickedUp'])->name('pickup');
        Route::post('/{package}/deliver', [DelivererPackageController::class, 'deliverPackage'])->name('deliver');
        Route::post('/{package}/unavailable', [DelivererPackageController::class, 'markUnavailable'])->name('unavailable');
        Route::post('/{package}/return', [DelivererPackageController::class, 'returnToSender'])->name('return');
        Route::post('/{package}/attempt', [DelivererPackageController::class, 'recordAttempt'])->name('attempt');

        // SCAN QR/CODES-BARRES - AMÉLIORÉ ET ROBUSTE
        Route::post('/scan', [DelivererPackageController::class, 'scanPackage'])->name('scan');
        Route::post('/scan-batch', [DelivererPackageController::class, 'scanBatch'])->name('scan.batch');
        Route::get('/search-advanced', [DelivererPackageController::class, 'searchAdvanced'])->name('search.advanced');

        // Actions groupées - NOUVELLES
        Route::post('/bulk-accept', [DelivererPackageController::class, 'bulkAccept'])->name('bulk.accept');
        Route::post('/bulk-pickup', [DelivererPackageController::class, 'bulkPickup'])->name('bulk.pickup');
        Route::post('/bulk-deliver', [DelivererPackageController::class, 'bulkDeliver'])->name('bulk.deliver');
        Route::post('/bulk-return', [DelivererPackageController::class, 'bulkReturn'])->name('bulk.return');

        // Documents et preuves
        Route::get('/run-sheet', [DelivererPackageController::class, 'generateRunSheet'])->name('run.sheet');
        Route::get('/{package}/delivery-receipt', [DelivererPackageController::class, 'deliveryReceipt'])->name('delivery.receipt');
        Route::get('/{package}/pickup-photo', [DelivererPackageController::class, 'showPickupPhoto'])->name('pickup.photo');
        Route::get('/{package}/delivery-photo', [DelivererPackageController::class, 'showDeliveryPhoto'])->name('delivery.photo');
    });

    // ==================== LISTES SPÉCIFIQUES (5 LISTES) ====================
    // LISTE 1: Pickups Disponibles
    Route::get('/pickups/available', [DelivererPackageController::class, 'availablePickups'])->name('pickups.available');
    
    // LISTE 2: Mes Pickups (acceptés) - AMÉLIORÉE
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
        Route::get('/topup', [DelivererWalletController::class, 'showTopupForm'])->name('topup');
        Route::post('/topup', [DelivererWalletController::class, 'processTopup'])->name('topup.process');
        Route::post('/request-emptying', [DelivererWalletController::class, 'requestEmptying'])->name('request.emptying');
        Route::get('/export', [DelivererWalletController::class, 'exportTransactions'])->name('export');
    });

    // ==================== DEMANDES DE VIDAGE ====================
    Route::prefix('emptyings')->name('emptyings.')->group(function () {
        Route::get('/', [DelivererWalletController::class, 'emptyings'])->name('index');
        Route::get('/{emptying}', [DelivererWalletController::class, 'showEmptying'])->name('show');
    });

    // ==================== RUN SHEETS (Feuilles de Route) ====================
    Route::prefix('runsheets')->name('runsheets.')->group(function () {
        Route::get('/', [DelivererRunSheetController::class, 'index'])->name('index');
        Route::post('/generate', [DelivererRunSheetController::class, 'generate'])->name('generate');
        Route::get('/{runSheet}/print', [DelivererRunSheetController::class, 'print'])->name('print');
        Route::post('/{runSheet}/complete', [DelivererRunSheetController::class, 'complete'])->name('complete');
        Route::get('/{runSheet}/download/{token}', [DelivererRunSheetController::class, 'downloadWithToken'])->name('download');
    });

    // ==================== API ROUTES ====================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/runsheets/stats', [DelivererRunSheetController::class, 'apiStats'])->name('runsheets.stats');
    });
    
    // ==================== RECHARGE CLIENT (Ajout Fonds) ====================
    Route::prefix('client-topup')->name('client-topup.')->group(function () {
        Route::get('/', [DelivererClientTopupController::class, 'index'])->name('index');
        Route::post('/search-client', [DelivererClientTopupController::class, 'searchClient'])->name('search-client');
        Route::post('/process', [DelivererClientTopupController::class, 'processTopup'])->name('process');
        Route::get('/history', [DelivererClientTopupController::class, 'history'])->name('history');
        Route::get('/{topup}', [DelivererClientTopupController::class, 'show'])->name('show');
        Route::get('/{topup}/receipt', [DelivererClientTopupController::class, 'receipt'])->name('receipt');
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
        Route::post('/update', [DelivererProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [DelivererProfileController::class, 'updateAvatar'])->name('avatar');
        Route::post('/preferences', [DelivererProfileController::class, 'updatePreferences'])->name('preferences');
        Route::post('/documents', [DelivererProfileController::class, 'uploadDocument'])->name('documents');
        Route::get('/export', [DelivererProfileController::class, 'exportData'])->name('export');
        Route::get('/password', [DelivererProfileController::class, 'showPasswordForm'])->name('password');
        Route::post('/password', [DelivererProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/statistics', [DelivererProfileController::class, 'statistics'])->name('statistics');
        Route::get('/statistics/period/{period}', [DelivererProfileController::class, 'statisticsByPeriod'])->name('statistics.period');
        Route::post('/statistics/export/{format}', [DelivererProfileController::class, 'exportStatistics'])->name('statistics.export');
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
        Route::post('/trigger', [DelivererEmergencyController::class, 'triggerEmergency'])->name('trigger');
        Route::post('/call-commercial', [DelivererEmergencyController::class, 'callCommercial'])->name('call-commercial');
        Route::post('/report-issue', [DelivererEmergencyController::class, 'reportIssue'])->name('report-issue');
    });

    // ==================== REÇUS ====================
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/package/{package}', [DelivererReceiptController::class, 'packageReceipt'])->name('package');
        Route::get('/payment/{payment}', [DelivererReceiptController::class, 'paymentReceipt'])->name('payment');
        Route::get('/topup/{topup}', [DelivererReceiptController::class, 'topupReceipt'])->name('topup');
    });

    // ==================== API ENDPOINTS LIVREUR - AMÉLIORÉES ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs - AMÉLIORÉES
        Route::get('/dashboard-stats', [DelivererPackageController::class, 'apiDashboardStats'])->name('dashboard.stats');
        
        // Package APIs - NOUVELLES/AMÉLIORÉES  
        Route::get('/available-count', function() {
            try {
                $count = \App\Models\Package::where('status', 'AVAILABLE')->count();
                return response()->json(['success' => true, 'count' => $count]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'count' => 0]);
            }
        })->name('packages.available.count');
        
        Route::get('/my-pickups-count', function() {
            try {
                $count = \App\Models\Package::where('assigned_deliverer_id', auth()->id())
                    ->where('status', 'ACCEPTED')->count();
                return response()->json(['success' => true, 'count' => $count]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'count' => 0]);
            }
        })->name('packages.my-pickups.count');
        
        Route::get('/deliveries-count', function() {
            try {
                $count = \App\Models\Package::where('assigned_deliverer_id', auth()->id())
                    ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->count();
                return response()->json(['success' => true, 'count' => $count]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'count' => 0]);
            }
        })->name('packages.deliveries.count');
        
        Route::get('/returns-count', function() {
            try {
                $count = \App\Models\Package::where('assigned_deliverer_id', auth()->id())
                    ->where('status', 'VERIFIED')->count();
                return response()->json(['success' => true, 'count' => $count]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'count' => 0]);
            }
        })->name('packages.returns.count');
        
        Route::get('/payments-count', [DelivererPaymentController::class, 'apiPaymentsCount'])->name('payments.count');
        
        // Wallet APIs - AMÉLIORÉES
        Route::get('/wallet/balance', [DelivererPackageController::class, 'apiWalletBalance'])->name('wallet.balance');
        Route::get('/wallet/recent-transactions', [DelivererWalletController::class, 'apiRecentTransactions'])->name('wallet.transactions');
        Route::get('/wallet/earnings-chart', [DelivererWalletController::class, 'apiEarningsChart'])->name('wallet.earnings');
        
        // Délégations pour le scanner - NOUVEAU
        Route::get('/delegations', [DelivererPackageController::class, 'apiDelegations'])->name('delegations');
        
        // Scanner APIs - NOUVEAUX
        Route::post('/scan/validate-code', function(\Illuminate\Http\Request $request) {
            $code = strtoupper(trim($request->input('code', '')));
            $isValid = preg_match('/^PKG_[A-Z0-9]{8,}_\d{8}$/', $code) || 
                      preg_match('/^[A-Z0-9]{8,}$/', $code) || 
                      preg_match('/^[0-9]{8,}$/', $code);
            
            return response()->json([
                'valid' => $isValid,
                'formatted' => $code
            ]);
        })->name('scan.validate');
        
        Route::get('/scan/recent-codes', function() {
            // Récupérer les codes récents depuis localStorage côté client
            // Cette route pourrait servir pour la synchronisation
            return response()->json(['codes' => []]);
        })->name('scan.recent');
        
        // Notifications - EXISTANTES
        Route::get('/notifications/unread-count', function() {
            try {
                $count = auth()->user()->notifications()->where('read', false)->count();
                return response()->json(['success' => true, 'count' => $count]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'count' => 0]);
            }
        })->name('notifications.unread.count');
        
        Route::get('/notifications/recent', function() {
            try {
                $notifications = auth()->user()->notifications()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                return response()->json(['success' => true, 'notifications' => $notifications]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'notifications' => []]);
            }
        })->name('notifications.recent');
        
        // Location tracking - EXISTANTES
        Route::post('/location/update', [DelivererLocationController::class, 'updateLocation'])->name('location.update');
        Route::get('/location/current', [DelivererLocationController::class, 'currentLocation'])->name('location.current');
        Route::get('/location/history', [DelivererLocationController::class, 'locationHistory'])->name('location.history');

        // Emergency API
        Route::post('/emergency', [DelivererEmergencyController::class, 'apiTriggerEmergency'])->name('emergency.trigger');

        // Verification APIs
        Route::get('/verify-receipt/{trackingNumber}', [DelivererReceiptController::class, 'verifyReceipt'])->name('verify.receipt');
        Route::get('/verify-payment/{paymentId}', [DelivererReceiptController::class, 'verifyPayment'])->name('verify.payment');
        Route::get('/verify-topup/{topupId}', [DelivererReceiptController::class, 'verifyTopup'])->name('verify.topup');
        
        // Health Check - NOUVEAU
        Route::get('/health', function() {
            return response()->json([
                'status' => 'OK',
                'timestamp' => now()->toISOString(),
                'user_id' => auth()->id(),
                'features' => [
                    'scanner' => true,
                    'camera' => true,
                    'batch_processing' => true,
                    'photos' => true,
                    'offline_sync' => true
                ]
            ]);
        })->name('health');
    });
    
    // ==================== ROUTES DE DÉVELOPPEMENT (à supprimer en prod) ====================
    Route::prefix('dev')->name('dev.')->middleware(['app.debug'])->group(function () {
        // Test scanner
        Route::get('/test-scanner', function() {
            return view('deliverer.dev.test-scanner');
        })->name('test.scanner');
        
        // Test génération de codes
        Route::get('/generate-test-codes', function() {
            $codes = [];
            for ($i = 0; $i < 10; $i++) {
                $codes[] = 'PKG_' . strtoupper(\Illuminate\Support\Str::random(8)) . '_' . date('Ymd');
            }
            return response()->json(['codes' => $codes]);
        })->name('generate.codes');
        
        // Reset localStorage (pour tests)
        Route::post('/reset-local-storage', function() {
            return response()->json(['message' => 'localStorage reset signal sent']);
        })->name('reset.storage');
    });
});

// ==================== ROUTES PUBLIQUES LIÉES AU SCANNER ====================
// (Si nécessaire pour tracking ou webhooks)

Route::prefix('deliverer-public')->name('deliverer.public.')->group(function () {
    // Tracking public de colis (sans auth)
    Route::get('/track/{code}', function($code) {
        $package = \App\Models\Package::where('package_code', $code)
            ->select(['package_code', 'status', 'delivered_at', 'delegation_to'])
            ->first();

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json([
            'code' => $package->package_code,
            'status' => $package->status,
            'delivered_at' => $package->delivered_at,
            'destination' => $package->delegationTo->name ?? null
        ]);
    })->name('track');

    // Webhook pour notifications push (si implémenté)
    Route::post('/webhook/scanner-notification', function(\Illuminate\Http\Request $request) {
        // Traiter les notifications push pour le scanner
        \Log::info('Scanner webhook received', $request->all());
        return response()->json(['status' => 'received']);
    })->name('webhook.scanner');
});

// ==================== ROUTES PUBLIQUES DE VÉRIFICATION ====================
Route::prefix('verify')->name('verify.')->group(function () {
    Route::get('/receipt/{trackingNumber}', [DelivererReceiptController::class, 'publicVerifyReceipt'])->name('receipt');
    Route::get('/payment/{paymentId}', [DelivererReceiptController::class, 'publicVerifyPayment'])->name('payment');
    Route::get('/topup/{topupId}', [DelivererReceiptController::class, 'publicVerifyTopup'])->name('topup');
});