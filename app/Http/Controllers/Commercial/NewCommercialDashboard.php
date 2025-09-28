<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Complaint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NewCommercialDashboard extends Controller
{
    public function api_getDashboardStats()
    {
        try {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            // === MÉTRIQUES PRINCIPALES ===
            // Clients - compter exactement
            $totalClients = User::where('role', 'CLIENT')->count();
            $activeClients = User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->count();

            // Packages - compter exactement
            $totalPackages = Package::count();
            $packagesToday = Package::whereDate('created_at', $today)->count();
            $deliveredToday = Package::where('status', 'DELIVERED')->whereDate('updated_at', $today)->count();
            $packagesInProgress = Package::whereIn('status', ['AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count();

            // === MÉTRIQUES FINANCIÈRES ===
            $totalRevenue = Package::where('status', 'DELIVERED')->sum('delivery_fee') ?? 0;
            $revenueThisMonth = Package::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$thisMonth, now()])->sum('delivery_fee') ?? 0;
            $revenueLastMonth = Package::where('status', 'DELIVERED')
                ->whereBetween('updated_at', [$lastMonth, $thisMonth])->sum('delivery_fee') ?? 0;

            $totalCodCollected = Package::where('status', 'DELIVERED')->sum('cod_amount') ?? 0;
            $codCollectedToday = Package::where('status', 'DELIVERED')
                ->whereDate('updated_at', $today)->sum('cod_amount') ?? 0;

            // === MÉTRIQUES DE PERFORMANCE ===
            $deliveredCount = Package::where('status', 'DELIVERED')->count();
            $successRate = $totalPackages > 0 ? round(($deliveredCount / $totalPackages) * 100, 1) : 0;

            $averageOrderValue = $deliveredCount > 0 ? round($totalCodCollected / $deliveredCount, 2) : 0;

            // === MÉTRIQUES OPÉRATIONNELLES ===
            $totalComplaints = Complaint::count();
            $pendingComplaints = Complaint::where('status', 'PENDING')->count();
            $urgentComplaints = Complaint::where('priority', 'URGENT')->count();
            $resolvedComplaints = Complaint::where('status', 'RESOLVED')->count();

            $complaintsResolutionRate = $totalComplaints > 0 ?
                round(($resolvedComplaints / $totalComplaints) * 100, 1) : 100;

            // WithdrawalRequest peut ne pas exister
            $pendingWithdrawals = 0;
            $pendingWithdrawalAmount = 0;

            // === MÉTRIQUES DE CROISSANCE ===
            $newClientsThisMonth = User::where('role', 'CLIENT')
                ->whereBetween('created_at', [$thisMonth, now()])->count();
            $newClientsLastMonth = User::where('role', 'CLIENT')
                ->whereBetween('created_at', [$lastMonth, $thisMonth])->count();

            $clientGrowthRate = $newClientsLastMonth > 0 ?
                round((($newClientsThisMonth - $newClientsLastMonth) / $newClientsLastMonth) * 100, 1) : 0;

            $packagesThisMonth = Package::whereBetween('created_at', [$thisMonth, now()])->count();
            $packagesLastMonth = Package::whereBetween('created_at', [$lastMonth, $thisMonth])->count();

            $packageGrowthRate = $packagesLastMonth > 0 ?
                round((($packagesThisMonth - $packagesLastMonth) / $packagesLastMonth) * 100, 1) : 0;

            $revenueGrowthRate = $revenueLastMonth > 0 ?
                round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0;

            // === MÉTRIQUES LIVREURS ===
            $totalDeliverers = User::where('role', 'DELIVERER')->count();
            $activeDeliverers = User::where('role', 'DELIVERER')->where('account_status', 'ACTIVE')->count();

            // Temps de livraison moyen (en heures)
            $averageDeliveryTime = Package::where('status', 'DELIVERED')
                ->whereNotNull('delivered_at')
                ->whereNotNull('picked_up_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, picked_up_at, delivered_at)) as avg_time')
                ->value('avg_time');
            $averageDeliveryTime = $averageDeliveryTime ? round($averageDeliveryTime, 1) : 0;

            // Livraisons par livreur
            $deliveriesPerDeliverer = $activeDeliverers > 0 ? round($deliveredCount / $activeDeliverers, 1) : 0;

            // Score de satisfaction client simplifié
            $clientSatisfactionScore = $totalPackages > 0 ?
                max(0, 100 - ($totalComplaints / $totalPackages * 20)) : 100;
            $clientSatisfactionScore = round($clientSatisfactionScore, 1);

            // === TENDANCES (7 derniers jours) ===
            $dailyTrend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $packagesCreated = Package::whereDate('created_at', $date)->count();
                $packagesDelivered = Package::where('status', 'DELIVERED')->whereDate('updated_at', $date)->count();
                $revenue = Package::where('status', 'DELIVERED')->whereDate('updated_at', $date)->sum('delivery_fee') ?? 0;

                $dailyTrend[] = [
                    'date' => $date->format('d/m'),
                    'day' => $date->format('D'),
                    'packages_created' => $packagesCreated,
                    'packages_delivered' => $packagesDelivered,
                    'revenue' => round($revenue, 2)
                ];
            }

            // === TOP LIVREURS ===
            $topDeliverers = User::where('role', 'DELIVERER')
                ->withCount([
                    'assignedPackages as total_deliveries' => function ($query) {
                        $query->where('status', 'DELIVERED');
                    }
                ])
                ->having('total_deliveries', '>', 0)
                ->orderBy('total_deliveries', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($deliverer) {
                    return [
                        'id' => $deliverer->id,
                        'name' => $deliverer->name,
                        'total_deliveries' => $deliverer->total_deliveries,
                        'phone' => $deliverer->phone ?? 'N/A'
                    ];
                });

            // === ASSEMBLAGE DES STATISTIQUES ===
            $stats = [
                // Métriques principales
                'total_clients' => $totalClients,
                'active_clients' => $activeClients,
                'total_packages' => $totalPackages,
                'packages_today' => $packagesToday,
                'delivered_today' => $deliveredToday,
                'packages_in_progress' => $packagesInProgress,

                // Métriques financières
                'total_revenue' => round($totalRevenue, 2),
                'revenue_this_month' => round($revenueThisMonth, 2),
                'revenue_growth_rate' => $revenueGrowthRate,
                'total_cod_collected' => round($totalCodCollected, 2),
                'cod_collected_today' => round($codCollectedToday, 2),
                'average_order_value' => $averageOrderValue,

                // Métriques de performance
                'success_rate' => $successRate,
                'complaints_resolution_rate' => $complaintsResolutionRate,
                'average_delivery_time' => $averageDeliveryTime,
                'total_deliverers' => $totalDeliverers,
                'active_deliverers' => $activeDeliverers,
                'deliveries_per_deliverer' => $deliveriesPerDeliverer,

                // Métriques opérationnelles
                'total_complaints' => $totalComplaints,
                'pending_complaints' => $pendingComplaints,
                'urgent_complaints' => $urgentComplaints,
                'resolved_complaints' => $resolvedComplaints,
                'pending_withdrawals' => $pendingWithdrawals,
                'pending_withdrawal_amount' => round($pendingWithdrawalAmount, 2),

                // Métriques de croissance
                'new_clients_this_month' => $newClientsThisMonth,
                'client_growth_rate' => $clientGrowthRate,
                'packages_this_month' => $packagesThisMonth,
                'package_growth_rate' => $packageGrowthRate,

                // Tendances et top performers
                'daily_trend' => $dailyTrend,
                'top_deliverers' => $topDeliverers->toArray(),
                'client_satisfaction_score' => $clientSatisfactionScore,

                // Debug - données brutes pour vérification
                'debug_info' => [
                    'query_date' => now()->toDateTimeString(),
                    'total_users_all_roles' => User::count(),
                    'users_by_role' => User::groupBy('role')->selectRaw('role, count(*) as count')->pluck('count', 'role'),
                    'packages_by_status' => Package::groupBy('status')->selectRaw('status, count(*) as count')->pluck('count', 'status'),
                ]
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Erreur API dashboard stats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors du calcul des statistiques',
                'message' => $e->getMessage(),
                'debug' => [
                    'total_users' => User::count() ?? 'Error counting users',
                    'total_packages' => Package::count() ?? 'Error counting packages'
                ]
            ], 500);
        }
    }
}