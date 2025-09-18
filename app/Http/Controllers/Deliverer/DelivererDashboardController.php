<?php

namespace App\Http\Controllers\Deliverer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;

class DelivererDashboardController
{
    /**
     * Dashboard livreur
     */
    public function index()
    {
        // Vérification manuelle simple
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return redirect()->route('login')->with('error', 'Accès réservé aux livreurs.');
        }

        $user = Auth::user();
        $user->ensureWallet();

        // Stats simplifiées
        $stats = [
            'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
            'my_pickups' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'ACCEPTED')->count(),
            'deliveries' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'PICKED_UP')->count(),
            'returns' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'VERIFIED')->count(),
            'payments' => 0,
            'deliveries_today' => 0,
            'cod_collected_today' => 0,
            'total_delivered_month' => 0,
            'earnings_month' => 0,
            'success_rate' => 95
        ];

        $recentActivity = [];
        $walletData = [
            'balance' => $user->wallet ? $user->wallet->balance : 0,
            'formatted_balance' => $user->wallet ? number_format($user->wallet->balance, 3) . ' DT' : '0.000 DT'
        ];

        return view('deliverer.dashboard', compact('stats', 'recentActivity', 'walletData'));
    }

    /**
     * API Stats
     */
    public function apiStats()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = Auth::user();

        return response()->json([
            'available_pickups' => Package::where('status', 'AVAILABLE')->count(),
            'my_pickups' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'ACCEPTED')->count(),
            'deliveries' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'PICKED_UP')->count(),
            'returns' => Package::where('assigned_deliverer_id', $user->id)->where('status', 'VERIFIED')->count(),
            'payments' => 0,
            'deliveries_today' => 0,
            'cod_collected_today' => 0
        ]);
    }

    /**
     * API Activité récente
     */
    public function apiRecentActivity()
    {
        if (!Auth::check() || Auth::user()->role !== 'DELIVERER') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([]);
    }
}