<?php

use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\Http\Controllers\Supervisor\DelegationController;
use App\Http\Controllers\Supervisor\UserController;
use App\Http\Controllers\Supervisor\SystemController;
use App\Http\Controllers\Supervisor\ReportController;
use App\Http\Controllers\Supervisor\SettingsController;
use App\Http\Controllers\Supervisor\PackageController;
use App\Http\Controllers\Supervisor\SupervisorTicketController;
use App\Services\FinancialTransactionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Supervisor Routes
|--------------------------------------------------------------------------
|
| Routes spécifiques aux superviseurs (rôle SUPERVISOR)
| Préfixe: /supervisor
| Middleware: auth, verified, role:SUPERVISOR
|
*/

Route::middleware(['auth', 'verified', 'role:SUPERVISOR'])->prefix('supervisor')->name('supervisor.')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');

    // ==================== GESTION DÉLÉGATIONS ====================
    Route::prefix('delegations')->name('delegations.')->group(function () {
        Route::get('/', [DelegationController::class, 'index'])->name('index');
        Route::get('/create', [DelegationController::class, 'create'])->name('create');
        Route::post('/', [DelegationController::class, 'store'])->name('store');
        Route::get('/{delegation}', [DelegationController::class, 'show'])->name('show');
        Route::get('/{delegation}/edit', [DelegationController::class, 'edit'])->name('edit');
        Route::put('/{delegation}', [DelegationController::class, 'update'])->name('update');
        Route::delete('/{delegation}', [DelegationController::class, 'destroy'])->name('destroy');
        
        // Actions groupées
        Route::post('/bulk-activate', [DelegationController::class, 'bulkActivate'])->name('bulk.activate');
        Route::post('/bulk-deactivate', [DelegationController::class, 'bulkDeactivate'])->name('bulk.deactivate');
        
        // Import/Export
        Route::get('/export', [DelegationController::class, 'export'])->name('export');
        Route::get('/import/template', [DelegationController::class, 'importTemplate'])->name('import.template');
        Route::post('/import', [DelegationController::class, 'import'])->name('import');
    });

    // ==================== GESTION UTILISATEURS ====================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Actions spécifiques
        Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
        Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset.password');
        Route::post('/{user}/force-logout', [UserController::class, 'forceLogout'])->name('force.logout');

        // Gestion des rôles et permissions
        Route::get('/{user}/permissions', [UserController::class, 'permissions'])->name('permissions');
        Route::post('/{user}/permissions', [UserController::class, 'updatePermissions'])->name('permissions.update');

        // Actions groupées
        Route::post('/bulk-activate', [UserController::class, 'bulkActivate'])->name('bulk.activate');
        Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk.deactivate');
        Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk.delete');

        // Export
        Route::get('/export', [UserController::class, 'export'])->name('export');
    });

    // ==================== GESTION SYSTÈME ====================
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/overview', [SystemController::class, 'overview'])->name('overview');
        Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        Route::get('/maintenance', [SystemController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/enable', [SystemController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [SystemController::class, 'disableMaintenance'])->name('maintenance.disable');
        
        // Backup & Restore
        Route::get('/backup', [SystemController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [SystemController::class, 'createBackup'])->name('backup.create');
        Route::post('/backup/restore', [SystemController::class, 'restoreBackup'])->name('backup.restore');
        Route::delete('/backup/{backup}', [SystemController::class, 'deleteBackup'])->name('backup.delete');
        
        // Cache management
        Route::post('/cache/clear', [SystemController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/optimize', [SystemController::class, 'optimizeCache'])->name('cache.optimize');
        
        // Database maintenance
        Route::post('/database/optimize', [SystemController::class, 'optimizeDatabase'])->name('database.optimize');
        Route::post('/database/migrate', [SystemController::class, 'runMigrations'])->name('database.migrate');
    });

    // ==================== RAPPORTS ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        
        // Rapports financiers
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/financial/export', [ReportController::class, 'exportFinancial'])->name('financial.export');
        
        // Rapports opérationnels
        Route::get('/operational', [ReportController::class, 'operational'])->name('operational');
        Route::get('/operational/export', [ReportController::class, 'exportOperational'])->name('operational.export');
        
        // Rapports clients
        Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
        Route::get('/clients/export', [ReportController::class, 'exportClients'])->name('clients.export');
        
        // Rapports livreurs
        Route::get('/deliverers', [ReportController::class, 'deliverers'])->name('deliverers');
        Route::get('/deliverers/export', [ReportController::class, 'exportDeliverers'])->name('deliverers.export');
        
        // Rapports personnalisés
        Route::get('/custom', [ReportController::class, 'custom'])->name('custom');
        Route::post('/custom/generate', [ReportController::class, 'generateCustom'])->name('custom.generate');
        
        // API pour graphiques
        Route::get('/api/revenue-chart', [ReportController::class, 'apiRevenueChart'])->name('api.revenue.chart');
        Route::get('/api/packages-chart', [ReportController::class, 'apiPackagesChart'])->name('api.packages.chart');
        Route::get('/api/performance-chart', [ReportController::class, 'apiPerformanceChart'])->name('api.performance.chart');
    });

    // ==================== GESTION COLIS SUPERVISEUR ====================
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [PackageController::class, 'index'])->name('index');
        Route::get('/{package}', [PackageController::class, 'show'])->name('show');
        Route::get('/tracking/{code}', [PackageController::class, 'tracking'])->name('tracking');
        Route::post('/{package}/force-deliver', [PackageController::class, 'forceDeliver'])->name('force.deliver');
        Route::post('/{package}/cancel', [PackageController::class, 'cancel'])->name('cancel');
        Route::post('/bulk-update', [PackageController::class, 'bulkUpdate'])->name('bulk.update');
        Route::get('/export', [PackageController::class, 'export'])->name('export');
    });

    // ==================== GESTION TICKETS SUPERVISEUR ====================
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [SupervisorTicketController::class, 'index'])->name('index');
        Route::get('/overview', [SupervisorTicketController::class, 'overview'])->name('overview');
        Route::get('/{ticket}', [SupervisorTicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/escalate', [SupervisorTicketController::class, 'escalate'])->name('escalate');
        Route::post('/{ticket}/force-close', [SupervisorTicketController::class, 'forceClose'])->name('force.close');
        Route::post('/bulk-reassign', [SupervisorTicketController::class, 'bulkReassign'])->name('bulk.reassign');
        Route::get('/performance-report', [SupervisorTicketController::class, 'performanceReport'])->name('performance.report');
    });

    // ==================== PARAMÈTRES SYSTÈME ====================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Paramètres généraux
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
        
        // Paramètres financiers
        Route::get('/financial', [SettingsController::class, 'financial'])->name('financial');
        Route::post('/financial', [SettingsController::class, 'updateFinancial'])->name('financial.update');
        
        // Paramètres de livraison
        Route::get('/delivery', [SettingsController::class, 'delivery'])->name('delivery');
        Route::post('/delivery', [SettingsController::class, 'updateDelivery'])->name('delivery.update');
        
        // Paramètres de notifications
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        
        // Paramètres de sécurité
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security', [SettingsController::class, 'updateSecurity'])->name('security.update');
    });

    // ==================== AUDIT & LOGS ====================
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/activities', [SystemController::class, 'activities'])->name('activities');
        Route::get('/transactions', [SystemController::class, 'transactionLogs'])->name('transactions');
        Route::get('/logins', [SystemController::class, 'loginLogs'])->name('logins');
        Route::get('/errors', [SystemController::class, 'errorLogs'])->name('errors');
        
        // Export des logs
        Route::get('/activities/export', [SystemController::class, 'exportActivities'])->name('activities.export');
        Route::get('/transactions/export', [SystemController::class, 'exportTransactions'])->name('transactions.export');
    });

    // ==================== API ENDPOINTS SUPERVISEUR ====================
    Route::prefix('api')->name('api.')->group(function () {
        
        // Dashboard APIs
        Route::get('/dashboard-stats', [SupervisorDashboardController::class, 'apiStats'])->name('supervisor.dashboard.stats');
        Route::get('/system-status', [SupervisorDashboardController::class, 'apiSystemStatus'])->name('system.status');
        
        // User APIs
        Route::get('/users/search', [UserController::class, 'apiSearch'])->name('users.search');
        Route::get('/users/stats', [UserController::class, 'apiStats'])->name('users.stats');
        Route::get('/users/active-sessions', [UserController::class, 'apiActiveSessions'])->name('users.sessions');
        
        // Delegation APIs
        Route::get('/delegations/search', [DelegationController::class, 'apiSearch'])->name('delegations.search');
        Route::get('/delegations/stats', [DelegationController::class, 'apiStats'])->name('delegations.stats');
        
        // System APIs
        Route::get('/system/health', [SystemController::class, 'apiHealth'])->name('system.health');
        Route::get('/system/performance', [SystemController::class, 'apiPerformance'])->name('system.performance');
        Route::get('/system/storage', [SystemController::class, 'apiStorage'])->name('system.storage');
        
        // Financial APIs
        Route::get('/financial/summary', [ReportController::class, 'apiFinancialSummary'])->name('financial.summary');
        Route::get('/financial/trends', [ReportController::class, 'apiFinancialTrends'])->name('financial.trends');
    });
});

// ==================== ROUTES DE TEST FINANCIER ====================
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