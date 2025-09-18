<?php

namespace App\Http\Controllers\Deliverer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\Delegation;

class DelivererPackageController
{
    /**
     * PAS de __construct pour éviter les erreurs
     */

    /**
     * Pickups disponibles
     */
    public function availablePickups(Request $request)
    {
        // Vérifier manuellement l'authentification et le rôle
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('status', 'AVAILABLE')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('created_at', 'asc')
                          ->paginate(20);
                          
        $delegations = Delegation::where('active', true)->orderBy('name')->get();

        return view('deliverer.packages.available', compact('packages', 'delegations'));
    }

    /**
     * Mes pickups acceptés
     */
    public function myPickups(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                          ->where('status', 'ACCEPTED')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('assigned_at', 'asc')
                          ->paginate(20);

        return view('deliverer.packages.my-pickups', compact('packages'));
    }

    /**
     * Livraisons à effectuer
     */
    public function deliveries(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                          ->where('status', 'PICKED_UP')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('updated_at', 'asc')
                          ->paginate(20);

        return view('deliverer.packages.deliveries', compact('packages'));
    }

    /**
     * Retours à effectuer
     */
    public function returns(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        $packages = Package::where('assigned_deliverer_id', Auth::id())
                          ->where('status', 'VERIFIED')
                          ->with(['sender', 'delegationFrom', 'delegationTo'])
                          ->orderBy('updated_at', 'asc')
                          ->paginate(20);

        return view('deliverer.packages.returns', compact('packages'));
    }

    /**
     * Accepter un pickup
     */
    public function acceptPickup(Package $package)
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        if ($package->status !== 'AVAILABLE') {
            return response()->json(['success' => false, 'message' => 'Pickup non disponible.']);
        }

        $package->update([
            'assigned_deliverer_id' => Auth::id(),
            'assigned_at' => now(),
            'status' => 'ACCEPTED'
        ]);

        return response()->json(['success' => true, 'message' => 'Pickup accepté!']);
    }

    /**
     * API Stats pour le dashboard
     */
    public function apiDashboardStats()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
            'my_pickups' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'ACCEPTED')->count(),
            'deliveries' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'PICKED_UP')->count(),
            'returns' => Package::where('assigned_deliverer_id', Auth::id())->where('status', 'VERIFIED')->count(),
            'payments' => 0
        ]);
    }

    /**
     * API Wallet Balance
     */
    public function apiWalletBalance()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = Auth::user();
        $user->ensureWallet();

        return response()->json([
            'balance' => $user->wallet->balance ?? 0,
            'formatted_balance' => number_format($user->wallet->balance ?? 0, 3) . ' DT'
        ]);
    }
}