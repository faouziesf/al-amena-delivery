<?php

use App\Http\Controllers\DepotManager\DepotManagerDashboardController;
use App\Http\Controllers\DepotManager\DepotManagerDelivererController;
use App\Http\Controllers\DepotManager\DepotManagerPackageController;
use App\Http\Controllers\DepotManager\ExchangePackageController;
use App\Http\Controllers\Api\PaymentDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Depot Manager Routes - Routes pour les chefs dépôt
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:DEPOT_MANAGER'])->prefix('depot-manager')->name('depot-manager.')->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DepotManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api/stats', [DepotManagerDashboardController::class, 'apiStats'])->name('dashboard.api.stats');
    Route::get('/gouvernorat/{gouvernorat}', [DepotManagerDashboardController::class, 'showGouvernorat'])->name('gouvernorat.show');

    // ==================== GESTION DES PAIEMENTS ====================
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/to-prep', function () {
            return view('depot-manager.payments.payments-to-prep');
        })->name('to-prep');
    });

    // ==================== GESTION DES LIVREURS ====================
    Route::prefix('deliverers')->name('deliverers.')->group(function () {
        Route::get('/', [DepotManagerDelivererController::class, 'index'])->name('index');
        Route::get('/stats', [DepotManagerDelivererController::class, 'stats'])->name('stats');
        Route::get('/create', [DepotManagerDelivererController::class, 'create'])->name('create');
        Route::post('/', [DepotManagerDelivererController::class, 'store'])->name('store');
        Route::get('/{deliverer}', [DepotManagerDelivererController::class, 'show'])->name('show');
        Route::get('/{deliverer}/edit', [DepotManagerDelivererController::class, 'edit'])->name('edit');
        Route::put('/{deliverer}', [DepotManagerDelivererController::class, 'update'])->name('update');
        Route::post('/{deliverer}/toggle-status', [DepotManagerDelivererController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{deliverer}/reassign-packages', [DepotManagerDelivererController::class, 'reassignPackages'])->name('reassign-packages');

        // Wallet Management Routes - Chef dépôt peut uniquement vider le wallet complet
        Route::post('/{deliverer}/wallet/empty', [DepotManagerDelivererController::class, 'emptyDelivererWallet'])->name('wallet.empty');
    });

    // ==================== GESTION DES COLIS ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [DepotManagerPackageController::class, 'index'])->name('index');
        Route::get('/all', [DepotManagerPackageController::class, 'allPackages'])->name('all');
        Route::get('/payment-packages', [DepotManagerPackageController::class, 'paymentPackages'])->name('payment-packages');
        Route::get('/returns-exchanges', [DepotManagerPackageController::class, 'returnsExchanges'])->name('returns-exchanges');
        Route::get('/supplier-returns', [DepotManagerPackageController::class, 'supplierReturns'])->name('supplier-returns');
        Route::get('/batch-scanner', [DepotManagerPackageController::class, 'batchScanner'])->name('batch-scanner');
        Route::post('/batch-scan', [DepotManagerPackageController::class, 'processBatchScan'])->name('batch-scan');
        Route::post('/print-batch-returns', [DepotManagerPackageController::class, 'printBatchReturns'])->name('print-batch-returns');

        // ==================== ACTIONS REQUISES DASHBOARD ====================
        Route::get('/dashboard-actions', [DepotManagerPackageController::class, 'dashboardActions'])->name('dashboard-actions');
        Route::get('/{package}/details', [DepotManagerPackageController::class, 'packageDetails'])->name('details');

        Route::get('/{package}', [DepotManagerPackageController::class, 'show'])->name('show');
        Route::post('/{package}/reassign', [DepotManagerPackageController::class, 'reassign'])->name('reassign');
        
        // ROUTES SUPPRIMÉES (anciennes routes de retour obsolètes):
        // - process-all-returns
        // - process-return
        // - process-return-dashboard
        // Ces fonctionnalités sont maintenant gérées par le nouveau système de retours
        
        Route::get('/{package}/return-receipt', [DepotManagerPackageController::class, 'printReturnReceipt'])->name('return-receipt');
        Route::post('/create-return-package', [DepotManagerPackageController::class, 'createReturnPackage'])->name('create-return-package');
        Route::get('/{package}/delivery-receipt', [DepotManagerPackageController::class, 'deliveryReceipt'])->name('delivery-receipt');
        Route::get('/bulk-delivery-receipts', [DepotManagerPackageController::class, 'bulkDeliveryReceipts'])->name('bulk-delivery-receipts');
    });

    // ==================== SYSTÈME BOÎTES DE TRANSIT ====================
    Route::prefix('crates')->name('crates.')->group(function () {
        Route::get('/', [DepotManagerPackageController::class, 'cratesIndex'])->name('index');
        Route::get('/box-manager', [DepotManagerPackageController::class, 'boxManager'])->name('box-manager');
        Route::post('/scan-package', [DepotManagerPackageController::class, 'scanPackageForBox'])->name('scan-package');
        Route::post('/seal-box', [DepotManagerPackageController::class, 'sealBox'])->name('seal-box');
        Route::get('/box-receipt/{boxCode}', [DepotManagerPackageController::class, 'generateBoxReceipt'])->name('box-receipt');
        Route::post('/receive-box', [DepotManagerPackageController::class, 'receiveBox'])->name('receive-box');
        Route::get('/box-details/{boxId}', [DepotManagerPackageController::class, 'getBoxDetails'])->name('box-details');
    });

    // ==================== RAPPORTS ET ANALYSES ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [DepotManagerPackageController::class, 'reports'])->name('index');
    });

    // ==================== ACCÈS COMMERCIAL (HÉRITAGE) ====================
    // Le chef dépôt a aussi accès aux fonctionnalités commerciales pour ses gouvernorats
    Route::prefix('commercial')->name('commercial.')->group(function () {

        // Réutiliser les contrôleurs commerciaux avec middleware supplémentaire
        Route::get('/dashboard', function() {
            return redirect()->route('depot-manager.dashboard');
        })->name('dashboard');

        // Gestion des demandes de rechargement pour les clients de ses gouvernorats
        Route::prefix('topup-requests')->name('topup-requests.')->group(function () {
            Route::get('/', function(\Illuminate\Http\Request $request) {
                return app(\App\Http\Controllers\Commercial\CommercialTopupRequestController::class)->index($request);
            })->name('index');

            Route::get('/{topupRequest}', function(\App\Models\TopupRequest $topupRequest) {
                return app(\App\Http\Controllers\Commercial\CommercialTopupRequestController::class)->show($topupRequest);
            })->name('show');

            Route::post('/{topupRequest}/approve', function(\Illuminate\Http\Request $request, \App\Models\TopupRequest $topupRequest) {
                return app(\App\Http\Controllers\Commercial\CommercialTopupRequestController::class)->approve($request, $topupRequest);
            })->name('approve');

            Route::post('/{topupRequest}/reject', function(\Illuminate\Http\Request $request, \App\Models\TopupRequest $topupRequest) {
                return app(\App\Http\Controllers\Commercial\CommercialTopupRequestController::class)->reject($request, $topupRequest);
            })->name('reject');
        });

        // Gestion des demandes de retrait
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', function(\Illuminate\Http\Request $request) {
                return app(\App\Http\Controllers\Commercial\WithdrawalController::class)->index($request);
            })->name('index');

            Route::get('/{withdrawal}', function(\App\Models\WithdrawalRequest $withdrawal) {
                return app(\App\Http\Controllers\Commercial\WithdrawalController::class)->show($withdrawal);
            })->name('show');

            Route::get('/{withdrawal}/receipt', function(\App\Models\WithdrawalRequest $withdrawal) {
                return app(\App\Http\Controllers\Commercial\WithdrawalController::class)->receipt($withdrawal);
            })->name('receipt');

            Route::post('/{withdrawal}/approve', function(\Illuminate\Http\Request $request, \App\Models\WithdrawalRequest $withdrawal) {
                return app(\App\Http\Controllers\Commercial\WithdrawalController::class)->approve($request, $withdrawal);
            })->name('approve');

            Route::post('/{withdrawal}/reject', function(\Illuminate\Http\Request $request, \App\Models\WithdrawalRequest $withdrawal) {
                return app(\App\Http\Controllers\Commercial\WithdrawalController::class)->reject($request, $withdrawal);
            })->name('reject');
        });

        // Gestion des tickets (consultation et réponse uniquement)
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', function(\Illuminate\Http\Request $request) {
                return app(\App\Http\Controllers\Commercial\CommercialTicketController::class)->index($request);
            })->name('index');

            Route::get('/{ticket}', function(\App\Models\Ticket $ticket) {
                return app(\App\Http\Controllers\Commercial\CommercialTicketController::class)->show($ticket);
            })->name('show');

            Route::post('/{ticket}/respond', function(\Illuminate\Http\Request $request, \App\Models\Ticket $ticket) {
                return app(\App\Http\Controllers\Commercial\CommercialTicketController::class)->reply($request, $ticket);
            })->name('respond');

            Route::post('/{ticket}/update-status', function(\Illuminate\Http\Request $request, \App\Models\Ticket $ticket) {
                return app(\App\Http\Controllers\Commercial\CommercialTicketController::class)->updateStatus($request, $ticket);
            })->name('update-status');

            Route::post('/{ticket}/update-priority', function(\Illuminate\Http\Request $request, \App\Models\Ticket $ticket) {
                return app(\App\Http\Controllers\Commercial\CommercialTicketController::class)->updatePriority($request, $ticket);
            })->name('update-priority');
        });
    });

    // ==================== API ROUTES PAIEMENTS ====================
    Route::prefix('api/payments')->name('api.payments.')->group(function () {
        Route::get('/dashboard', [PaymentDashboardController::class, 'depotDashboard'])->name('dashboard');
        Route::post('/{withdrawal}/create-package', [PaymentDashboardController::class, 'createPaymentPackage'])->name('create.package');
        Route::post('/{withdrawal}/approve', [PaymentDashboardController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [PaymentDashboardController::class, 'reject'])->name('reject');
    });

    // ==================== API ROUTES LIVREURS ====================
    Route::prefix('api/deliverers')->name('api.deliverers.')->group(function () {
        Route::get('/available', [DepotManagerDelivererController::class, 'apiAvailable'])->name('available');
    });

    // ==================== API ROUTES COLIS ====================
    Route::prefix('api/packages')->name('api.packages.')->group(function () {
        Route::post('/{package}/assign', [DepotManagerPackageController::class, 'assignDeliverer'])->name('assign');
    });

    // ==================== GESTION COLIS ÉCHANGES ====================
    Route::prefix('exchanges')->name('exchanges.')->group(function() {
        Route::get('/', [ExchangePackageController::class, 'index'])->name('index');
        Route::post('/{package}/process', [ExchangePackageController::class, 'processExchange'])->name('process');
        Route::get('/history', [ExchangePackageController::class, 'history'])->name('history');
        Route::get('/{returnPackage}/print', [ExchangePackageController::class, 'printReturnReceipt'])->name('print');
    });

});