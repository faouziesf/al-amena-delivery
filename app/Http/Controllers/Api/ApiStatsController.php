<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class ApiStatsController extends Controller
{
    /**
     * Obtenir les statistiques du client
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Total de colis
        $totalPackages = Package::where('sender_id', $userId)->count();
        
        // Répartition par statut
        $byStatus = Package::where('sender_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Statistiques du mois en cours
        $thisMonth = [
            'total' => Package::where('sender_id', $userId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'delivered' => Package::where('sender_id', $userId)
                ->where('status', 'DELIVERED')
                ->whereYear('delivered_at', now()->year)
                ->whereMonth('delivered_at', now()->month)
                ->count(),
            'pending' => Package::where('sender_id', $userId)
                ->whereIn('status', ['CREATED', 'ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY'])
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];
        
        // Revenus totaux (colis livrés)
        $totalRevenue = Package::where('sender_id', $userId)
            ->where('status', 'DELIVERED')
            ->sum('package_price');
        
        // Total COD collecté
        $totalCodCollected = Package::where('sender_id', $userId)
            ->where('status', 'DELIVERED')
            ->where('payment_type', 'COD')
            ->sum('cod_amount');
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_packages' => $totalPackages,
                'by_status' => $byStatus,
                'this_month' => $thisMonth,
                'total_revenue' => (float)$totalRevenue,
                'total_cod_collected' => (float)$totalCodCollected,
            ]
        ]);
    }
}
