<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\PickupRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

        $recentPickupRequests = PickupRequest::where('client_id', $user->id)
            ->with(['assignedDeliverer'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $notifications = $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $user->transactions()
            ->where('status', 'COMPLETED')
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        // Colis d'échange retournés récents
        $exchangeReturnedPackages = Package::where('sender_id', $user->id)
            ->where('est_echange', true)
            ->where('status', 'RETURNED')
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact(
            'user',
            'stats',
            'recentPackages',
            'recentPickupRequests',
            'notifications',
            'recentTransactions',
            'exchangeReturnedPackages'
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
        $pickupRequests = $user->pickupRequests();

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
            'pending_pickups' => $pickupRequests->where('status', 'pending')->count(),
            'assigned_pickups' => $pickupRequests->where('status', 'assigned')->count(),
            'completed_pickups' => $pickupRequests->where('status', 'picked_up')->count(),
            'total_pickups' => $pickupRequests->count(),
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

    /**
     * Afficher les colis retournés au client (nouveau système)
     */
    public function returns()
    {
        $user = Auth::user();

        // Récupérer les colis en RETURNED_TO_CLIENT (en attente de validation client)
        $packagesAwaitingConfirmation = Package::where('sender_id', $user->id)
            ->where('status', 'RETURNED_TO_CLIENT')
            ->with(['returnPackage', 'delegationFrom', 'delegationTo'])
            ->orderBy('returned_to_client_at', 'desc')
            ->get();

        // Récupérer les retours confirmés
        $confirmedReturns = Package::where('sender_id', $user->id)
            ->where('status', 'RETURN_CONFIRMED')
            ->with(['returnPackage', 'delegationFrom', 'delegationTo'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Récupérer les retours avec problème
        $issueReturns = Package::where('sender_id', $user->id)
            ->where('status', 'RETURN_ISSUE')
            ->with(['returnPackage', 'delegationFrom', 'delegationTo', 'complaints'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('client.returns', compact(
            'packagesAwaitingConfirmation',
            'confirmedReturns',
            'issueReturns'
        ));
    }

    /**
     * Confirmer la réception d'un colis retourné
     */
    public function confirmReturn(Package $package)
    {
        $user = Auth::user();

        // Vérifier que c'est bien le client propriétaire
        if ($package->sender_id !== $user->id) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à confirmer ce retour.');
        }

        // Vérifier le statut
        if ($package->status !== 'RETURNED_TO_CLIENT') {
            return back()->with('error', 'Ce colis ne peut pas être confirmé (statut: ' . $package->status . ').');
        }

        // Confirmer le retour
        $package->update(['status' => 'RETURN_CONFIRMED']);

        \Log::info('Retour confirmé par le client', [
            'package_id' => $package->id,
            'client_id' => $user->id,
        ]);

        return back()->with('success', 'Retour confirmé avec succès.');
    }

    /**
     * Signaler un problème sur un colis retourné
     */
    public function reportReturnIssue(Request $request, Package $package)
    {
        $request->validate([
            'issue_description' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Vérifier que c'est bien le client propriétaire
        if ($package->sender_id !== $user->id) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à signaler un problème pour ce colis.');
        }

        // Vérifier le statut
        if ($package->status !== 'RETURNED_TO_CLIENT') {
            return back()->with('error', 'Ce colis ne peut pas être signalé (statut: ' . $package->status . ').');
        }

        // Créer une réclamation
        Complaint::create([
            'package_id' => $package->id,
            'client_id' => $user->id,
            'type' => 'RETURN_ISSUE',
            'description' => $request->issue_description,
            'status' => 'PENDING',
            'priority' => 'HIGH',
        ]);

        // Changer le statut du colis
        $package->update(['status' => 'RETURN_ISSUE']);

        \Log::info('Problème signalé sur retour', [
            'package_id' => $package->id,
            'client_id' => $user->id,
            'issue' => $request->issue_description,
        ]);

        return back()->with('success', 'Problème signalé avec succès. Notre équipe va vous contacter.');
    }
}