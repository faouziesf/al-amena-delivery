<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    /**
     * Dashboard principal du client
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'clientProfile']);

        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $stats = $this->getDashboardStats();
        
        $recentPackages = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $notifications = $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $user->transactions()
            ->where('status', 'COMPLETED')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact(
            'user',
            'stats', 
            'recentPackages',
            'notifications',
            'recentTransactions'
        ));
    }

    /**
     * API - Statistiques dashboard
     */
    public function apiStats()
    {
        return response()->json($this->getDashboardStats());
    }

    /**
     * Calcul des statistiques du dashboard
     */
    private function getDashboardStats(): array
    {
        $user = Auth::user();
        
        $user->ensureWallet();
        $user->load('wallet');
        
        $packages = $user->packages();
        
        return [
            'wallet_balance' => (float) ($user->wallet->balance ?? 0),
            'wallet_pending' => (float) ($user->wallet->pending_amount ?? 0),
            'wallet_available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0)),
            'total_packages' => $packages->count(),
            'in_progress_packages' => $packages->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count(),
            'delivered_packages' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned_packages' => $packages->where('status', 'RETURNED')->count(),
            'pending_complaints' => $user->complaints()->where('status', 'PENDING')->count(),
            'pending_withdrawals' => $user->withdrawalRequests()->where('status', 'PENDING')->count(),
            'unread_notifications' => $user->notifications()->where('read', false)->count(),
            'monthly_packages' => $packages->whereMonth('created_at', now()->month)->count(),
            'monthly_delivered' => $packages->whereIn('status', ['DELIVERED', 'PAID'])
                                           ->whereMonth('updated_at', now()->month)->count(),
            'monthly_revenue' => $packages->whereIn('status', ['DELIVERED', 'PAID'])
                                         ->whereMonth('updated_at', now()->month)
                                         ->sum('cod_amount'),
            'average_delivery_time' => $this->calculateAverageDeliveryTime(),
            'success_rate' => $this->calculateSuccessRate()
        ];
    }

    /**
     * Calcul du temps moyen de livraison
     */
    private function calculateAverageDeliveryTime()
    {
        $deliveredPackages = Auth::user()->packages()
            ->whereIn('status', ['DELIVERED', 'PAID'])
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        if ($deliveredPackages->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($deliveredPackages as $package) {
            $deliveryHistory = $package->statusHistory()
                ->where('status', 'DELIVERED')
                ->first();

            if ($deliveryHistory) {
                $hours = $package->created_at->diffInHours($deliveryHistory->created_at);
                $totalHours += $hours;
                $count++;
            }
        }

        return $count > 0 ? round($totalHours / $count, 1) : 0;
    }

    /**
     * Calcul du taux de réussite
     */
    private function calculateSuccessRate()
    {
        $totalPackages = Auth::user()->packages()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($totalPackages === 0) {
            return 100;
        }

        $deliveredPackages = Auth::user()->packages()
            ->whereIn('status', ['DELIVERED', 'PAID'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return round(($deliveredPackages / $totalPackages) * 100, 1);
    }
}