<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Deliverer\DelivererDashboardController;
use App\Http\Controllers\Commercial\CommercialDashboardController;
use App\Http\Controllers\Commercial\ClientController;
use App\Http\Controllers\Commercial\ComplaintController;
use App\Http\Controllers\Commercial\WithdrawalController;
use App\Http\Controllers\Commercial\DelivererController;
use App\Http\Controllers\Commercial\PackageController;
use App\Http\Controllers\Commercial\NotificationController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Services\FinancialTransactionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Redirection selon le rôle
    switch ($user->role) {
        case 'CLIENT':
            return redirect()->route('client.dashboard');
        case 'DELIVERER':
            return redirect()->route('deliverer.dashboard');
        case 'COMMERCIAL':
            return redirect()->route('commercial.dashboard');
        case 'SUPERVISOR':
            return redirect()->route('supervisor.dashboard');
        default:
            return redirect()->route('login');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// ==================== ROUTES SPÉCIFIQUES PAR RÔLE ====================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ==================== CLIENT ROUTES ====================
    Route::middleware(['role:CLIENT'])->prefix('client')->name('client.')->group(function () {
        
        // Dashboard Principal
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');

        // ==================== GESTION DES COLIS ====================
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'packages'])->name('index');
            Route::get('/create', [ClientDashboardController::class, 'createPackage'])->name('create');
            Route::post('/', [ClientDashboardController::class, 'storePackage'])->name('store');
            Route::get('/{package}', [ClientDashboardController::class, 'packageShow'])->name('show');
        });

        // ==================== GESTION WALLET ====================
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'wallet'])->name('index');
            Route::get('/withdrawal', [ClientDashboardController::class, 'createWithdrawal'])->name('withdrawal');
            Route::post('/withdrawal', [ClientDashboardController::class, 'storeWithdrawal'])->name('store-withdrawal');
        });

        // ==================== DEMANDES DE RETRAIT ====================
        Route::get('/withdrawals', [ClientDashboardController::class, 'withdrawals'])->name('withdrawals');

        // ==================== RÉCLAMATIONS ====================
        Route::prefix('complaints')->name('complaints.')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'complaints'])->name('index');
            Route::get('/create/{package}', [ClientDashboardController::class, 'createComplaint'])->name('create');
            Route::post('/{package}', [ClientDashboardController::class, 'storeComplaint'])->name('store');
        });

        // ==================== NOTIFICATIONS ====================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', function() {
                $user = auth()->user();
                $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
                return view('client.notifications.index', compact('notifications'));
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

        // ==================== API ENDPOINTS CLIENT ====================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard-stats', [ClientDashboardController::class, 'apiStats'])->name('dashboard.stats');
            Route::get('/wallet/balance', [ClientDashboardController::class, 'apiWalletBalance'])->name('wallet.balance');
            
            // Notifications API
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
            
            // Packages API
            Route::get('/packages/{package}/status', function($packageId) {
                $package = auth()->user()->sentPackages()->findOrFail($packageId);
                return response()->json(['status' => $package->status]);
            })->name('packages.status');
        });
    });

    // ==================== DELIVERER ROUTES ====================
    Route::middleware(['role:DELIVERER'])->prefix('deliverer')->name('deliverer.')->group(function () {
        Route::get('/dashboard', function () {
            return view('deliverer.dashboard', ['user' => auth()->user()]);
        })->name('dashboard');
    });

    // ==================== COMMERCIAL ROUTES ====================
    Route::middleware(['role:COMMERCIAL,SUPERVISOR'])->prefix('commercial')->name('commercial.')->group(function () {
        
        // Dashboard Principal
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
            
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/search', [ClientController::class, 'apiSearch'])->name('search');
                Route::get('/{client}/profile', [ClientController::class, 'apiClientProfile'])->name('profile');
                Route::get('/{client}/duplicate-data', [ClientController::class, 'apiClientForDuplication'])->name('duplicate.data');
                Route::get('/recent', [ClientController::class, 'apiRecentClients'])->name('recent');
                Route::get('/global-stats', [ClientController::class, 'apiGlobalStats'])->name('global.stats');
            });
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
            
            // API Endpoints
            Route::get('/api/stats', [ComplaintController::class, 'apiStats'])->name('api.stats');
            Route::get('/api/pending', [ComplaintController::class, 'apiPending'])->name('api.pending');
            Route::get('/api/recent-activity', [ComplaintController::class, 'apiRecentActivity'])->name('api.activity');
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
            
            // API Endpoints
            Route::get('/api/pending', [WithdrawalController::class, 'apiPending'])->name('api.pending');
            Route::get('/api/stats', [WithdrawalController::class, 'apiStats'])->name('api.stats');
            Route::get('/api/awaiting-delivery', [WithdrawalController::class, 'apiAwaitingDelivery'])->name('api.awaiting');
            Route::get('/api/search-clients', [WithdrawalController::class, 'apiSearchClients'])->name('api.search.clients');
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
            
            // API Endpoints
            Route::get('/api/search', [DelivererController::class, 'apiSearch'])->name('api.search');
            Route::get('/api/high-balance', [DelivererController::class, 'apiHighBalanceDeliverers'])->name('api.high.balance');
            Route::get('/api/stats', [DelivererController::class, 'apiStats'])->name('api.stats');
            Route::get('/api/recent-emptyings', [DelivererController::class, 'apiRecentEmptyings'])->name('api.emptyings');
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
            
            // API Endpoints
            Route::get('/api/search', [PackageController::class, 'apiSearch'])->name('api.search');
            Route::get('/api/stats', [PackageController::class, 'apiStats'])->name('api.stats');
            Route::get('/api/blocked', [PackageController::class, 'apiBlockedPackages'])->name('api.blocked');
            Route::get('/api/by-delegation', [PackageController::class, 'apiByDelegation'])->name('api.delegation');
            Route::get('/{package}/api/cod-history', [PackageController::class, 'codHistory'])->name('api.cod.history');
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
            
            // API Endpoints
            Route::get('/api/unread-count', [NotificationController::class, 'apiUnreadCount'])->name('api.unread.count');
            Route::get('/api/recent', [NotificationController::class, 'apiRecent'])->name('api.recent');
            Route::get('/api/all', [NotificationController::class, 'apiAll'])->name('api.all');
            Route::post('/api/mark-read', [NotificationController::class, 'apiMarkRead'])->name('api.mark.read');
            Route::get('/api/stats', [NotificationController::class, 'apiStats'])->name('api.stats');
            Route::get('/api/by-type/{type}', [NotificationController::class, 'apiByType'])->name('api.by.type');
            
            // Test (développement uniquement)
            Route::post('/api/test-notification', [NotificationController::class, 'createTestNotification'])->name('api.test');
        });

        // ==================== API ENDPOINTS GLOBAUX ====================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard-stats', [CommercialDashboardController::class, 'api_getDashboardStats'])->name('dashboard.stats');
            Route::get('/complaints-count', [CommercialDashboardController::class, 'api_getComplaintsCount'])->name('complaints.count');
            Route::get('/withdrawals-count', [CommercialDashboardController::class, 'api_getWithdrawalsCount'])->name('withdrawals.count');
            Route::get('/search-clients', [CommercialDashboardController::class, 'api_searchClients'])->name('search.clients');
            Route::get('/search-deliverers', [CommercialDashboardController::class, 'api_searchDeliverers'])->name('search.deliverers');
        });
    });

    // ==================== SUPERVISOR ROUTES ====================
    Route::middleware(['role:SUPERVISOR'])->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', function () {
            return view('supervisor.dashboard', ['user' => auth()->user()]);
        })->name('dashboard');

        // Le superviseur a accès à toutes les routes commercial
        // Plus des routes spécifiques de supervision (à implémenter)
        
        // ==================== GESTION DÉLÉGATIONS ====================
        Route::prefix('delegations')->name('delegations.')->group(function () {
            Route::get('/', function() {
                $delegations = \App\Models\Delegation::with('creator')->orderBy('name')->paginate(20);
                return view('supervisor.delegations.index', compact('delegations'));
            })->name('index');
            
            Route::get('/create', function() {
                return view('supervisor.delegations.create');
            })->name('create');
            
            Route::post('/', function(\Illuminate\Http\Request $request) {
                $validated = $request->validate([
                    'name' => 'required|string|max:255|unique:delegations',
                    'zone' => 'nullable|string|max:255',
                    'active' => 'boolean'
                ]);
                
                \App\Models\Delegation::create([
                    'name' => $validated['name'],
                    'zone' => $validated['zone'],
                    'active' => $validated['active'] ?? true,
                    'created_by' => auth()->id()
                ]);
                
                return redirect()->route('supervisor.delegations.index')
                    ->with('success', 'Délégation créée avec succès');
            })->name('store');
            
            Route::get('/{delegation}/edit', function(\App\Models\Delegation $delegation) {
                return view('supervisor.delegations.edit', compact('delegation'));
            })->name('edit');
            
            Route::put('/{delegation}', function(\App\Models\Delegation $delegation, \Illuminate\Http\Request $request) {
                $validated = $request->validate([
                    'name' => 'required|string|max:255|unique:delegations,name,' . $delegation->id,
                    'zone' => 'nullable|string|max:255',
                    'active' => 'boolean'
                ]);
                
                $delegation->update($validated);
                
                return redirect()->route('supervisor.delegations.index')
                    ->with('success', 'Délégation mise à jour avec succès');
            })->name('update');
            
            Route::delete('/{delegation}', function(\App\Models\Delegation $delegation) {
                if (!$delegation->canBeDeleted()) {
                    return redirect()->back()
                        ->with('error', 'Cette délégation ne peut pas être supprimée car elle est utilisée par des colis.');
                }
                
                $delegation->delete();
                
                return redirect()->route('supervisor.delegations.index')
                    ->with('success', 'Délégation supprimée avec succès');
            })->name('destroy');
        });

        // ==================== GESTION UTILISATEURS ====================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', function() {
                $users = \App\Models\User::with(['creator', 'verifier'])->orderBy('created_at', 'desc')->paginate(20);
                return view('supervisor.users.index', compact('users'));
            })->name('index');
            
            Route::get('/create', function() {
                return view('supervisor.users.create');
            })->name('create');
            
            Route::post('/', function(\Illuminate\Http\Request $request) {
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users',
                    'phone' => 'required|string|max:20',
                    'address' => 'required|string|max:500',
                    'role' => 'required|in:CLIENT,DELIVERER,COMMERCIAL,SUPERVISOR',
                    'password' => 'required|min:8|confirmed'
                ]);
                
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'role' => $validated['role'],
                    'password' => bcrypt($validated['password']),
                    'account_status' => 'ACTIVE',
                    'created_by' => auth()->id(),
                    'verified_at' => now(),
                    'verified_by' => auth()->id()
                ]);
                
                // Créer le wallet si nécessaire
                if (in_array($validated['role'], ['CLIENT', 'DELIVERER'])) {
                    $user->ensureWallet();
                }
                
                return redirect()->route('supervisor.users.index')
                    ->with('success', 'Utilisateur créé avec succès');
            })->name('store');
        });
    });
});

// ==================== ROUTES DE TEST FINANCIER (SUPERVISEUR UNIQUEMENT) ====================
Route::middleware(['auth', 'role:SUPERVISOR'])->group(function () {
    Route::post('/test/financial-transaction', function (Request $request, FinancialTransactionService $financialService) {
        try {
            $result = $financialService->processTransaction([
                'user_id' => $request->user_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'metadata' => ['test' => true, 'ip' => $request->ip()]
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('test.transaction');
    
    Route::post('/test/recover-transactions', function (FinancialTransactionService $financialService) {
        $recovered = $financialService->recoverPendingTransactions();
        return response()->json([
            'success' => true,
            'recovered_count' => $recovered
        ]);
    })->name('test.recover');

    // Route pour tester les notifications
    Route::post('/test/create-notification', function (Request $request) {
        $notification = \App\Models\Notification::create([
            'user_id' => auth()->id(),
            'type' => 'TEST_NOTIFICATION',
            'title' => 'Notification de Test',
            'message' => 'Ceci est une notification de test créée le ' . now()->format('d/m/Y H:i'),
            'priority' => 'NORMAL',
            'data' => ['test' => true, 'created_by' => auth()->user()->name]
        ]);

        return response()->json([
            'success' => true,
            'notification_id' => $notification->id,
            'message' => 'Notification de test créée'
        ]);
    })->name('test.notification');
});

// ==================== ROUTES PROFIL ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== WEBHOOKS & API PUBLIQUE ====================
Route::prefix('api/public')->name('api.public.')->group(function () {
    // Routes API publiques pour intégrations externes (à sécuriser avec API keys)
    // TODO: Implémenter selon besoins
});

require __DIR__.'/auth.php';