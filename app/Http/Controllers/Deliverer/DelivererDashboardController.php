<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererDashboardController extends Controller
{
    /**
     * Dashboard principal du livreur
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'DELIVERER') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'delivererProfile']);

        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $stats = $this->getDashboardStats();
        
        // Colis récents assignés
        $recentPackages = Package::where('assigned_deliverer_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'sender'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Colis disponibles dans la zone
        $availablePackages = $this->getAvailablePackagesInZone();

        // Retraits clients assignés
        $assignedWithdrawals = WithdrawalRequest::where('assigned_deliverer_id', $user->id)
            ->where('status', 'ASSIGNED_TO_DELIVERER')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Notifications non lues
        $notifications = $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('deliverer.dashboard', compact(
            'user',
            'stats', 
            'recentPackages',
            'availablePackages',
            'assignedWithdrawals',
            'notifications'
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
     * Mise à jour de la localisation du livreur
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0'
        ]);

        $user = Auth::user();
        
        // Mettre à jour la position dans le profil livreur
        if ($user->delivererProfile) {
            $user->delivererProfile->update([
                'current_latitude' => $validated['latitude'],
                'current_longitude' => $validated['longitude'],
                'location_accuracy' => $validated['accuracy'] ?? null,
                'last_location_update' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Position mise à jour avec succès'
        ]);
    }

    /**
     * Obtenir la position actuelle du livreur
     */
    public function currentLocation()
    {
        $user = Auth::user();
        
        if (!$user->delivererProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil livreur non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'location' => [
                'latitude' => $user->delivererProfile->current_latitude,
                'longitude' => $user->delivererProfile->current_longitude,
                'accuracy' => $user->delivererProfile->location_accuracy,
                'last_update' => $user->delivererProfile->last_location_update
            ]
        ]);
    }

    /**
     * Calcul des statistiques du dashboard
     */
    private function getDashboardStats(): array
    {
        $user = Auth::user();
        
        $user->ensureWallet();
        $user->load('wallet');
        
        $assignedPackages = Package::where('assigned_deliverer_id', $user->id);
        $todayPackages = Package::where('assigned_deliverer_id', $user->id)
                               ->whereDate('updated_at', today());
        $monthlyPackages = Package::where('assigned_deliverer_id', $user->id)
                                 ->whereMonth('updated_at', now()->month)
                                 ->whereYear('updated_at', now()->year);

        return [
            // Portefeuille
            'wallet_balance' => (float) ($user->wallet->balance ?? 0),
            'wallet_pending' => (float) ($user->wallet->pending_amount ?? 0),
            'wallet_available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0)),
            
            // Colis
            'total_assigned' => $assignedPackages->count(),
            'pending_pickup' => $assignedPackages->where('status', 'ACCEPTED')->count(),
            'in_delivery' => $assignedPackages->where('status', 'PICKED_UP')->count(),
            'delivered_today' => $todayPackages->where('status', 'DELIVERED')->count(),
            'available_packages' => $this->countAvailablePackages(),
            
            // Performance mensuelle
            'monthly_delivered' => $monthlyPackages->where('status', 'DELIVERED')->count(),
            'monthly_returned' => $monthlyPackages->where('status', 'RETURNED')->count(),
            'monthly_earnings' => $this->calculateMonthlyEarnings(),
            
            // Retraits clients
            'assigned_withdrawals' => WithdrawalRequest::where('assigned_deliverer_id', $user->id)
                                                     ->where('status', 'ASSIGNED_TO_DELIVERER')
                                                     ->count(),
            
            // Taux de réussite
            'success_rate' => $this->calculateSuccessRate(),
            'average_delivery_time' => $this->calculateAverageDeliveryTime(),
            
            // Notifications
            'unread_notifications' => $user->notifications()->where('read', false)->count()
        ];
    }

    /**
     * Obtenir les colis disponibles dans la zone du livreur
     */
    private function getAvailablePackagesInZone()
    {
        $user = Auth::user();
        
        // Si le livreur a des zones assignées, filtrer par ces zones
        if ($user->delivererProfile && $user->delivererProfile->assigned_delegations) {
            $delegationIds = $user->delivererProfile->assigned_delegations;
            
            return Package::where('status', 'AVAILABLE')
                         ->whereIn('delegation_from', $delegationIds)
                         ->with(['delegationFrom', 'delegationTo', 'sender'])
                         ->orderBy('created_at', 'asc')
                         ->limit(10)
                         ->get();
        }
        
        // Sinon, retourner tous les colis disponibles
        return Package::where('status', 'AVAILABLE')
                     ->with(['delegationFrom', 'delegationTo', 'sender'])
                     ->orderBy('created_at', 'asc')
                     ->limit(10)
                     ->get();
    }

    /**
     * Compter les colis disponibles
     */
    private function countAvailablePackages()
    {
        $user = Auth::user();
        
        if ($user->delivererProfile && $user->delivererProfile->assigned_delegations) {
            $delegationIds = $user->delivererProfile->assigned_delegations;
            
            return Package::where('status', 'AVAILABLE')
                         ->whereIn('delegation_from', $delegationIds)
                         ->count();
        }
        
        return Package::where('status', 'AVAILABLE')->count();
    }

    /**
     * Calculer les gains du mois
     */
    private function calculateMonthlyEarnings()
    {
        $user = Auth::user();
        
        return $user->transactions()
                   ->where('type', 'DELIVERY_COMMISSION')
                   ->where('status', 'COMPLETED')
                   ->whereMonth('completed_at', now()->month)
                   ->whereYear('completed_at', now()->year)
                   ->sum('amount');
    }

    /**
     * Calculer le taux de réussite
     */
    private function calculateSuccessRate()
    {
        $user = Auth::user();
        
        $totalPackages = Package::where('assigned_deliverer_id', $user->id)
                               ->whereIn('status', ['DELIVERED', 'RETURNED'])
                               ->where('updated_at', '>=', now()->subDays(30))
                               ->count();

        if ($totalPackages === 0) {
            return 100;
        }

        $deliveredPackages = Package::where('assigned_deliverer_id', $user->id)
                                   ->where('status', 'DELIVERED')
                                   ->where('updated_at', '>=', now()->subDays(30))
                                   ->count();

        return round(($deliveredPackages / $totalPackages) * 100, 1);
    }

    /**
     * Calculer le temps moyen de livraison
     */
    private function calculateAverageDeliveryTime()
    {
        $user = Auth::user();
        
        $deliveredPackages = Package::where('assigned_deliverer_id', $user->id)
                                   ->where('status', 'DELIVERED')
                                   ->where('updated_at', '>=', now()->subDays(30))
                                   ->get();

        if ($deliveredPackages->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($deliveredPackages as $package) {
            $pickupHistory = $package->statusHistory()
                                   ->where('status', 'PICKED_UP')
                                   ->first();
            $deliveryHistory = $package->statusHistory()
                                     ->where('status', 'DELIVERED')
                                     ->first();

            if ($pickupHistory && $deliveryHistory) {
                $hours = $pickupHistory->created_at->diffInHours($deliveryHistory->created_at);
                $totalHours += $hours;
                $count++;
            }
        }

        return $count > 0 ? round($totalHours / $count, 1) : 0;
    }
}