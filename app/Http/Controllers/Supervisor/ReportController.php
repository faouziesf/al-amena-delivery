<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $availableReports = [
            'financial' => [
                'title' => 'Rapports Financiers',
                'description' => 'Revenus, transactions, portefeuilles',
                'icon' => 'chart-line',
                'url' => route('supervisor.reports.financial'),
            ],
            'operational' => [
                'title' => 'Rapports Opérationnels',
                'description' => 'Livraisons, performances, KPIs',
                'icon' => 'truck',
                'url' => route('supervisor.reports.operational'),
            ],
            'clients' => [
                'title' => 'Rapports Clients',
                'description' => 'Activité clients, satisfaction',
                'icon' => 'users',
                'url' => route('supervisor.reports.clients'),
            ],
            'deliverers' => [
                'title' => 'Rapports Livreurs',
                'description' => 'Performance, revenus livreurs',
                'icon' => 'user-check',
                'url' => route('supervisor.reports.deliverers'),
            ],
            'custom' => [
                'title' => 'Rapports Personnalisés',
                'description' => 'Créez vos propres rapports',
                'icon' => 'settings',
                'url' => route('supervisor.reports.custom'),
            ],
        ];

        $quickStats = [
            'total_revenue_today' => Transaction::whereDate('created_at', today())
                                              ->where('type', 'COD_PAYMENT')
                                              ->sum('amount'),
            'packages_today' => Package::whereDate('created_at', today())->count(),
            'deliveries_today' => Package::whereDate('delivery_date', today())->count(),
            'active_clients' => User::where('role', 'CLIENT')
                                  ->where('status', 'ACTIVE')
                                  ->count(),
        ];

        return view('supervisor.reports.index', compact('availableReports', 'quickStats'));
    }

    public function financial(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Revenus par jour
        $dailyRevenue = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('type', 'COD_PAYMENT')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Résumé financier
        $financialSummary = [
            'total_revenue' => Transaction::where('type', 'COD_PAYMENT')
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->sum('amount'),
            'delivery_fees' => Transaction::where('type', 'DELIVERY_FEE')
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->sum('amount'),
            'pending_payments' => Package::where('status', 'DELIVERED')
                                       ->whereNull('payment_date')
                                       ->sum('cod_amount'),
            'wallet_balances' => DB::table('user_wallets')->sum('balance'),
        ];

        // Top clients par revenus
        $topClients = User::select('users.*', DB::raw('SUM(transactions.amount) as total_revenue'))
                         ->join('transactions', 'users.id', '=', 'transactions.user_id')
                         ->where('users.role', 'CLIENT')
                         ->where('transactions.type', 'COD_PAYMENT')
                         ->whereBetween('transactions.created_at', [$startDate, $endDate])
                         ->groupBy('users.id')
                         ->orderBy('total_revenue', 'desc')
                         ->limit(10)
                         ->get();

        // Transactions par type
        $transactionTypes = Transaction::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                                     ->whereBetween('created_at', [$startDate, $endDate])
                                     ->groupBy('type')
                                     ->get();

        return view('supervisor.reports.financial', compact(
            'dailyRevenue',
            'financialSummary',
            'topClients',
            'transactionTypes',
            'period',
            'startDate',
            'endDate'
        ));
    }

    public function operational(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Statistiques de livraison
        $deliveryStats = [
            'total_packages' => Package::whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_packages' => Package::where('status', 'DELIVERED')
                                          ->whereBetween('delivery_date', [$startDate, $endDate])
                                          ->count(),
            'returned_packages' => Package::where('status', 'RETURNED')
                                         ->whereBetween('updated_at', [$startDate, $endDate])
                                         ->count(),
            'cancelled_packages' => Package::where('status', 'CANCELLED')
                                          ->whereBetween('updated_at', [$startDate, $endDate])
                                          ->count(),
        ];

        $deliveryStats['success_rate'] = $deliveryStats['total_packages'] > 0
            ? ($deliveryStats['delivered_packages'] / $deliveryStats['total_packages']) * 100
            : 0;

        // Packages par jour
        $dailyPackages = Package::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "DELIVERED" THEN 1 ELSE 0 END) as delivered')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Performance par délégation
        $delegationPerformance = Package::select(
                'delegations.name as delegation_name',
                DB::raw('COUNT(packages.id) as total_packages'),
                DB::raw('SUM(CASE WHEN packages.status = "DELIVERED" THEN 1 ELSE 0 END) as delivered_packages'),
                DB::raw('AVG(julianday(packages.delivery_date) - julianday(packages.created_at)) as avg_delivery_time')
            )
            ->join('users', 'packages.client_id', '=', 'users.id')
            ->join('delegations', 'users.delegation_id', '=', 'delegations.id')
            ->whereBetween('packages.created_at', [$startDate, $endDate])
            ->groupBy('delegations.id', 'delegations.name')
            ->get();

        // Top livreurs
        $topDeliverers = User::select(
                'users.*',
                DB::raw('COUNT(packages.id) as total_deliveries'),
                DB::raw('AVG(julianday(packages.delivery_date) - julianday(packages.pickup_date)) as avg_delivery_time')
            )
            ->join('packages', 'users.id', '=', 'packages.deliverer_id')
            ->where('users.role', 'DELIVERER')
            ->where('packages.status', 'DELIVERED')
            ->whereBetween('packages.delivery_date', [$startDate, $endDate])
            ->groupBy('users.id')
            ->orderBy('total_deliveries', 'desc')
            ->limit(10)
            ->get();

        return view('supervisor.reports.operational', compact(
            'deliveryStats',
            'dailyPackages',
            'delegationPerformance',
            'topDeliverers',
            'period',
            'startDate',
            'endDate'
        ));
    }

    public function clients(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Statistiques clients
        $clientStats = [
            'total_clients' => User::where('role', 'CLIENT')->count(),
            'active_clients' => User::where('role', 'CLIENT')
                                  ->where('status', 'ACTIVE')
                                  ->count(),
            'new_clients' => User::where('role', 'CLIENT')
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->count(),
            'clients_with_packages' => User::where('role', 'CLIENT')
                                         ->whereHas('packages', function($q) use ($startDate, $endDate) {
                                             $q->whereBetween('created_at', [$startDate, $endDate]);
                                         })
                                         ->count(),
        ];

        // Top clients par nombre de colis
        $topClientsByPackages = User::select(
                'users.*',
                DB::raw('COUNT(packages.id) as total_packages'),
                DB::raw('SUM(packages.cod_amount) as total_cod')
            )
            ->join('packages', 'users.id', '=', 'packages.client_id')
            ->where('users.role', 'CLIENT')
            ->whereBetween('packages.created_at', [$startDate, $endDate])
            ->groupBy('users.id')
            ->orderBy('total_packages', 'desc')
            ->limit(10)
            ->get();

        // Nouveaux clients par jour
        $newClientsDaily = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('role', 'CLIENT')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Répartition par délégation
        $clientsByDelegation = User::select(
                'delegations.name as delegation_name',
                DB::raw('COUNT(users.id) as clients_count')
            )
            ->join('delegations', 'users.delegation_id', '=', 'delegations.id')
            ->where('users.role', 'CLIENT')
            ->groupBy('delegations.id', 'delegations.name')
            ->orderBy('clients_count', 'desc')
            ->get();

        return view('supervisor.reports.clients', compact(
            'clientStats',
            'topClientsByPackages',
            'newClientsDaily',
            'clientsByDelegation',
            'period',
            'startDate',
            'endDate'
        ));
    }

    public function deliverers(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();

        // Statistiques livreurs
        $delivererStats = [
            'total_deliverers' => User::where('role', 'DELIVERER')->count(),
            'active_deliverers' => User::where('role', 'DELIVERER')
                                     ->where('status', 'ACTIVE')
                                     ->count(),
            'deliverers_with_deliveries' => User::where('role', 'DELIVERER')
                                               ->whereHas('deliveredPackages', function($q) use ($startDate, $endDate) {
                                                   $q->whereBetween('delivery_date', [$startDate, $endDate]);
                                               })
                                               ->count(),
            'total_deliveries' => Package::where('status', 'DELIVERED')
                                        ->whereBetween('delivery_date', [$startDate, $endDate])
                                        ->count(),
        ];

        // Performance des livreurs
        $delivererPerformance = User::select(
                'users.*',
                DB::raw('COUNT(packages.id) as total_deliveries'),
                DB::raw('SUM(packages.cod_amount) as total_cod_handled'),
                DB::raw('AVG(julianday(packages.delivery_date) - julianday(packages.pickup_date)) as avg_delivery_time'),
                'user_wallets.balance as wallet_balance'
            )
            ->join('packages', 'users.id', '=', 'packages.deliverer_id')
            ->leftJoin('user_wallets', 'users.id', '=', 'user_wallets.user_id')
            ->where('users.role', 'DELIVERER')
            ->where('packages.status', 'DELIVERED')
            ->whereBetween('packages.delivery_date', [$startDate, $endDate])
            ->groupBy('users.id', 'user_wallets.balance')
            ->orderBy('total_deliveries', 'desc')
            ->get();

        // Livreurs par délégation
        $deliverersByDelegation = User::select(
                'delegations.name as delegation_name',
                DB::raw('COUNT(users.id) as deliverers_count'),
                DB::raw('SUM(CASE WHEN users.status = "ACTIVE" THEN 1 ELSE 0 END) as active_count')
            )
            ->join('delegations', 'users.delegation_id', '=', 'delegations.id')
            ->where('users.role', 'DELIVERER')
            ->groupBy('delegations.id', 'delegations.name')
            ->orderBy('deliverers_count', 'desc')
            ->get();

        // Livraisons par jour
        $dailyDeliveries = Package::select(
                DB::raw('DATE(delivery_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT deliverer_id) as unique_deliverers')
            )
            ->where('status', 'DELIVERED')
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('supervisor.reports.deliverers', compact(
            'delivererStats',
            'delivererPerformance',
            'deliverersByDelegation',
            'dailyDeliveries',
            'period',
            'startDate',
            'endDate'
        ));
    }

    public function custom()
    {
        $availableMetrics = [
            'packages' => [
                'total_packages' => 'Nombre total de colis',
                'delivered_packages' => 'Colis livrés',
                'returned_packages' => 'Colis retournés',
                'cancelled_packages' => 'Colis annulés',
                'avg_delivery_time' => 'Temps moyen de livraison',
            ],
            'financial' => [
                'total_revenue' => 'Chiffre d\'affaires total',
                'delivery_fees' => 'Frais de livraison',
                'cod_amount' => 'Montant COD',
                'wallet_balances' => 'Soldes portefeuilles',
            ],
            'users' => [
                'total_clients' => 'Nombre de clients',
                'active_clients' => 'Clients actifs',
                'total_deliverers' => 'Nombre de livreurs',
                'active_deliverers' => 'Livreurs actifs',
            ]
        ];

        $dimensions = [
            'time' => [
                'daily' => 'Par jour',
                'weekly' => 'Par semaine',
                'monthly' => 'Par mois',
            ],
            'location' => [
                'delegation' => 'Par délégation',
                'city' => 'Par ville',
            ],
            'user' => [
                'client' => 'Par client',
                'deliverer' => 'Par livreur',
            ]
        ];

        return view('supervisor.reports.custom', compact('availableMetrics', 'dimensions'));
    }

    public function generateCustom(Request $request)
    {
        $request->validate([
            'metrics' => 'required|array',
            'dimensions' => 'required|array',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        // Cette méthode génèrerait un rapport personnalisé basé sur les paramètres
        // Pour l'instant, on retourne un exemple de données

        $results = [
            'metrics' => $request->metrics,
            'dimensions' => $request->dimensions,
            'date_range' => [
                'from' => $request->date_from,
                'to' => $request->date_to,
            ],
            'data' => [], // Les données seraient générées ici
        ];

        return view('supervisor.reports.custom-results', compact('results'));
    }

    // Export methods
    public function exportFinancial(Request $request)
    {
        return back()->with('info', 'Export financier en cours de développement.');
    }

    public function exportOperational(Request $request)
    {
        return back()->with('info', 'Export opérationnel en cours de développement.');
    }

    public function exportClients(Request $request)
    {
        return back()->with('info', 'Export clients en cours de développement.');
    }

    public function exportDeliverers(Request $request)
    {
        return back()->with('info', 'Export livreurs en cours de développement.');
    }

    // API Methods for charts
    public function apiRevenueChart(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $data = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue')
            )
            ->where('type', 'COD_PAYMENT')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $data->pluck('date'),
            'data' => $data->pluck('revenue'),
        ]);
    }

    public function apiPackagesChart(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $data = Package::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "DELIVERED" THEN 1 ELSE 0 END) as delivered')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'labels' => $data->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $data->pluck('total'),
                ],
                [
                    'label' => 'Livrés',
                    'data' => $data->pluck('delivered'),
                ]
            ]
        ]);
    }

    public function apiPerformanceChart(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $data = Package::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "DELIVERED" THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN status = "RETURNED" THEN 1 ELSE 0 END) as returned')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total > 0 ? ($item->delivered / $item->total) * 100 : 0;
                return $item;
            });

        return response()->json([
            'labels' => $data->pluck('date'),
            'data' => $data->pluck('success_rate'),
        ]);
    }

    public function apiFinancialSummary()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'today' => [
                'revenue' => Transaction::where('type', 'COD_PAYMENT')
                                      ->whereDate('created_at', $today)
                                      ->sum('amount'),
                'packages' => Package::whereDate('created_at', $today)->count(),
            ],
            'this_month' => [
                'revenue' => Transaction::where('type', 'COD_PAYMENT')
                                      ->where('created_at', '>=', $thisMonth)
                                      ->sum('amount'),
                'packages' => Package::where('created_at', '>=', $thisMonth)->count(),
            ]
        ]);
    }

    public function apiFinancialTrends()
    {
        $last30Days = Carbon::now()->subDays(30);
        $last60Days = Carbon::now()->subDays(60);

        $current = Transaction::where('type', 'COD_PAYMENT')
                            ->where('created_at', '>=', $last30Days)
                            ->sum('amount');

        $previous = Transaction::where('type', 'COD_PAYMENT')
                             ->whereBetween('created_at', [$last60Days, $last30Days])
                             ->sum('amount');

        $trend = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;

        return response()->json([
            'current_period' => $current,
            'previous_period' => $previous,
            'trend_percentage' => $trend,
            'trend_direction' => $trend >= 0 ? 'up' : 'down',
        ]);
    }
}