<?php
// app/Http/Controllers/Deliverer/DelivererDashboardController.php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\FinancialTransaction;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class DelivererDashboardController extends Controller
{
    /**
     * Dashboard livreur principal
     */
    public function index()
    {
        // Vérification sécurisée
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return redirect()->route('login')->with('error', 'Accès réservé aux livreurs.');
        }

        $user = Auth::user();
        $user->ensureWallet();

        try {
            // Stats principales optimisées
            $stats = $this->getOptimizedStats($user);
            
            // Données wallet
            $walletData = $this->getWalletData($user);
            
            // Activité récente
            $recentActivity = $this->getRecentActivity($user);
            
            // Notifications importantes
            $notifications = $this->getImportantNotifications($user);
            
            // Données pour graphiques
            $chartData = $this->getChartData($user);

            return view('deliverer.dashboard', compact(
                'stats', 
                'walletData', 
                'recentActivity', 
                'notifications',
                'chartData'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur dashboard livreur', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            // Données par défaut en cas d'erreur
            $stats = $this->getDefaultStats();
            $walletData = ['balance' => 0, 'formatted_balance' => '0.000 DT'];
            $recentActivity = [];
            $notifications = [];
            $chartData = [];

            return view('deliverer.dashboard', compact(
                'stats', 
                'walletData', 
                'recentActivity', 
                'notifications',
                'chartData'
            ));
        }
    }

    /**
     * API Stats dashboard optimisées
     */
    public function apiStats()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $user = Auth::user();
            $stats = $this->getOptimizedStats($user);
            
            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Erreur API stats dashboard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json($this->getDefaultStats());
        }
    }

    /**
     * API Wallet balance
     */
    public function apiWalletBalance()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $user = Auth::user();
            $user->ensureWallet();

            return response()->json([
                'balance' => $user->wallet->balance ?? 0,
                'formatted_balance' => number_format($user->wallet->balance ?? 0, 3) . ' DT',
                'pending_amount' => $user->wallet->pending_amount ?? 0,
                'frozen_amount' => $user->wallet->frozen_amount ?? 0,
                'available_balance' => $user->wallet->getAvailableBalanceAttribute() ?? 0
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur API wallet balance', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'balance' => 0,
                'formatted_balance' => '0.000 DT',
                'pending_amount' => 0,
                'frozen_amount' => 0,
                'available_balance' => 0
            ]);
        }
    }

    /**
     * API Activité récente
     */
    public function apiRecentActivity()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $user = Auth::user();
            $activity = $this->getRecentActivity($user);
            
            return response()->json([
                'success' => true,
                'activity' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'activity' => []
            ]);
        }
    }

    /**
     * API Notifications importantes
     */
    public function apiNotifications()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $user = Auth::user();
            $notifications = $this->getImportantNotifications($user);
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'notifications' => []
            ]);
        }
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Statistiques optimisées avec cache
     */
    private function getOptimizedStats(User $user)
    {
        $cacheKey = "deliverer_stats_{$user->id}";
        
        return Cache::remember($cacheKey, 30, function () use ($user) {
            $delivererId = $user->id;
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();

            // Statistiques des demandes de collecte
            $availablePickups = PickupRequest::where('status', 'pending')
                ->whereDate('requested_pickup_date', '>=', $today)
                ->count();

            $myPickups = PickupRequest::where('assigned_deliverer_id', $delivererId)
                ->whereIn('status', ['assigned', 'picked_up'])
                ->count();

            $completedToday = PickupRequest::where('assigned_deliverer_id', $delivererId)
                ->where('status', 'picked_up')
                ->whereDate('picked_up_at', $today)
                ->count();

            $pendingToday = PickupRequest::where('assigned_deliverer_id', $delivererId)
                ->where('status', 'assigned')
                ->whereDate('requested_pickup_date', $today)
                ->count();

            // Statistiques des packages livrés
            $deliveriesCount = Package::where('assigned_deliverer_id', $delivererId)
                ->whereIn('status', ['DELIVERED', 'PAID'])
                ->count();

            $deliveredToday = Package::where('assigned_deliverer_id', $delivererId)
                ->whereIn('status', ['DELIVERED', 'PAID'])
                ->whereDate('delivered_at', $today)
                ->count();

            $codCollectedToday = Package::where('assigned_deliverer_id', $delivererId)
                ->whereIn('status', ['DELIVERED', 'PAID'])
                ->whereDate('delivered_at', $today)
                ->sum('cod_amount');

            $returnsCount = Package::where('assigned_deliverer_id', $delivererId)
                ->where('status', 'RETURNED')
                ->count();

            // Stats additionnelles
            $urgentDeliveries = Package::where('assigned_deliverer_id', $delivererId)
                                     ->where('status', 'UNAVAILABLE')
                                     ->where('delivery_attempts', '>=', 3)
                                     ->count();

            $thisWeekDelivered = Package::where('assigned_deliverer_id', $delivererId)
                                       ->whereIn('status', ['DELIVERED', 'PAID'])
                                       ->where('delivered_at', '>=', $thisWeek)
                                       ->count();

            $thisMonthEarnings = FinancialTransaction::where('user_id', $delivererId)
                                                   ->where('type', 'COD_COLLECTION')
                                                   ->where('status', 'COMPLETED')
                                                   ->where('created_at', '>=', $thisMonth)
                                                   ->sum('amount');

            return [
                'available_pickups' => $availablePickups,
                'my_pickups' => $myPickups,
                'completed_today' => $completedToday,
                'pending_today' => $pendingToday,
                'deliveries' => $deliveriesCount,
                'returns' => $returnsCount,
                'deliveries_today' => $deliveredToday,
                'cod_collected_today' => $codCollectedToday,
                'urgent_deliveries' => $urgentDeliveries,
                'week_delivered' => $thisWeekDelivered,
                'month_earnings' => $thisMonthEarnings,
                'success_rate' => $this->calculateSuccessRate($delivererId)
            ];
        });
    }

    /**
     * Données wallet enrichies
     */
    private function getWalletData(User $user)
    {
        $wallet = $user->wallet;
        
        if (!$wallet) {
            return [
                'balance' => 0,
                'formatted_balance' => '0.000 DT',
                'pending_amount' => 0,
                'recent_transactions' => []
            ];
        }

        // Transactions récentes
        $recentTransactions = FinancialTransaction::where('user_id', $user->id)
                                                ->orderBy('created_at', 'desc')
                                                ->limit(5)
                                                ->get()
                                                ->map(function($transaction) {
                                                    return [
                                                        'id' => $transaction->id,
                                                        'type' => $transaction->type,
                                                        'amount' => $transaction->amount,
                                                        'formatted_amount' => $transaction->formatted_amount,
                                                        'description' => $transaction->description,
                                                        'created_at' => $transaction->created_at->format('d/m H:i'),
                                                        'status' => $transaction->status,
                                                        'package_code' => $transaction->package ? $transaction->package->package_code : null
                                                    ];
                                                });

        return [
            'balance' => $wallet->balance,
            'formatted_balance' => number_format($wallet->balance, 3) . ' DT',
            'pending_amount' => $wallet->pending_amount,
            'frozen_amount' => $wallet->frozen_amount,
            'available_balance' => $wallet->getAvailableBalanceAttribute(),
            'last_transaction_at' => $wallet->last_transaction_at?->diffForHumans(),
            'recent_transactions' => $recentTransactions
        ];
    }

    /**
     * Activité récente du livreur
     */
    private function getRecentActivity(User $user)
    {
        $activities = collect();

        // Colis livrés récemment
        $recentDeliveries = Package::where('assigned_deliverer_id', $user->id)
                                  ->whereIn('status', ['DELIVERED', 'PAID'])
                                  ->where('delivered_at', '>=', Carbon::now()->subDays(3))
                                  ->orderBy('delivered_at', 'desc')
                                  ->limit(3)
                                  ->get();

        foreach ($recentDeliveries as $package) {
            $activities->push([
                'type' => 'delivery',
                'icon' => '✅',
                'title' => 'Colis livré',
                'description' => "#{$package->package_code} - " . number_format($package->cod_amount, 3) . ' DT',
                'time' => $package->delivered_at ? $package->delivered_at->diffForHumans() : 'Récemment',
                'color' => 'text-green-600'
            ]);
        }

        // Colis collectés récemment
        $recentPickups = Package::where('assigned_deliverer_id', $user->id)
                               ->where('status', 'PICKED_UP')
                               ->where('picked_up_at', '>=', Carbon::now()->subDays(1))
                               ->orderBy('picked_up_at', 'desc')
                               ->limit(2)
                               ->get();

        foreach ($recentPickups as $package) {
            $activities->push([
                'type' => 'pickup',
                'icon' => '📦',
                'title' => 'Colis collecté',
                'description' => "#{$package->package_code}",
                'time' => $package->picked_up_at?->diffForHumans() ?? 'Récemment',
                'color' => 'text-blue-600'
            ]);
        }

        // Transactions récentes
        $recentTransactions = FinancialTransaction::where('user_id', $user->id)
                                                ->where('type', 'COD_COLLECTION')
                                                ->where('created_at', '>=', Carbon::now()->subHours(12))
                                                ->orderBy('created_at', 'desc')
                                                ->limit(2)
                                                ->get();

        foreach ($recentTransactions as $transaction) {
            $activities->push([
                'type' => 'transaction',
                'icon' => '💰',
                'title' => 'COD encaissé',
                'description' => number_format($transaction->amount, 3) . ' DT',
                'time' => $transaction->created_at ? $transaction->created_at->diffForHumans() : 'Récemment',
                'color' => 'text-emerald-600'
            ]);
        }

        return $activities->sortByDesc('time')->values()->take(8);
    }

    /**
     * Notifications importantes
     */
    private function getImportantNotifications(User $user)
    {
        return Notification::where('user_id', $user->id)
                          ->unread()
                          ->whereIn('priority', ['HIGH', 'URGENT'])
                          ->orderBy('priority', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get()
                          ->map(function($notification) {
                              return [
                                  'id' => $notification->id,
                                  'title' => $notification->title,
                                  'message' => $notification->message,
                                  'priority' => $notification->priority,
                                  'priority_color' => $notification->priority_color,
                                  'created_at' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Récemment',
                                  'action_url' => $notification->action_url
                              ];
                          });
    }

    /**
     * Données pour graphiques
     */
    private function getChartData(User $user)
    {
        // Livraisons des 7 derniers jours
        $deliveriesChart = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Package::where('assigned_deliverer_id', $user->id)
                           ->whereIn('status', ['DELIVERED', 'PAID'])
                           ->whereDate('delivered_at', $date)
                           ->count();
            
            $deliveriesChart->push([
                'date' => $date->format('d/m'),
                'count' => $count
            ]);
        }

        // COD collecté des 7 derniers jours
        $codChart = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $amount = FinancialTransaction::where('user_id', $user->id)
                                        ->where('type', 'COD_COLLECTION')
                                        ->whereDate('created_at', $date)
                                        ->sum('amount');
            
            $codChart->push([
                'date' => $date->format('d/m'),
                'amount' => $amount
            ]);
        }

        return [
            'deliveries_week' => $deliveriesChart,
            'cod_week' => $codChart
        ];
    }

    /**
     * Calculer le taux de succès
     */
    private function calculateSuccessRate($delivererId)
    {
        $totalAssigned = Package::where('assigned_deliverer_id', $delivererId)->count();
        
        if ($totalAssigned === 0) {
            return 100;
        }

        $delivered = Package::where('assigned_deliverer_id', $delivererId)
                           ->whereIn('status', ['DELIVERED', 'PAID'])
                           ->count();

        return round(($delivered / $totalAssigned) * 100, 1);
    }

    /**
     * Stats par défaut en cas d'erreur
     */
    private function getDefaultStats()
    {
        return [
            'available_pickups' => 0,
            'my_pickups' => 0,
            'completed_today' => 0,
            'pending_today' => 0,
            'deliveries' => 0,
            'returns' => 0,
            'deliveries_today' => 0,
            'cod_collected_today' => 0,
            'urgent_deliveries' => 0,
            'week_delivered' => 0,
            'month_earnings' => 0,
            'success_rate' => 100
        ];
    }
}