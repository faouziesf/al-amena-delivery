<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\WithdrawalRequest;
use App\Models\Complaint;
use App\Models\FinancialTransaction;
use App\Models\UserWallet;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Période par défaut : derniers 30 jours
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $period = Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');

        // KPIs principaux
        $kpis = $this->getMainKPIs($startDate, $endDate);

        // Données pour les graphiques
        $chartData = $this->getChartData($startDate, $endDate);

        // Top performers
        $topPerformers = $this->getTopPerformers($startDate, $endDate);

        // Statistiques de revenus
        $revenueStats = $this->getRevenueStatistics($startDate, $endDate);

        return view('commercial.analytics.index', compact(
            'kpis',
            'chartData',
            'topPerformers',
            'revenueStats',
            'period',
            'startDate',
            'endDate'
        ));
    }

    private function getMainKPIs($startDate, $endDate)
    {
        return [
            // Clients
            'total_clients' => User::where('role', 'CLIENT')->count(),
            'new_clients' => User::where('role', 'CLIENT')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'active_clients' => User::where('role', 'CLIENT')
                ->where('account_status', 'ACTIVE')
                ->count(),

            // Colis
            'total_packages' => Package::whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_packages' => Package::where('status', 'DELIVERED')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'packages_in_transit' => Package::whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),

            // Revenus
            'total_revenue' => Package::where('status', 'DELIVERED')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('delivery_fee'),
            'cod_collected' => Package::where('payment_method', 'COD')
                ->where('status', 'DELIVERED')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('cod_amount'),

            // Réclamations
            'total_complaints' => Complaint::whereBetween('created_at', [$startDate, $endDate])->count(),
            'resolved_complaints' => Complaint::where('status', 'RESOLVED')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),

            // Livreurs
            'active_deliverers' => User::where('role', 'DELIVERER')
                ->where('account_status', 'ACTIVE')
                ->count(),
            'deliverer_wallets_total' => UserWallet::whereHas('user', function($q) {
                $q->where('role', 'DELIVERER');
            })->sum('balance'),

            // Retraits
            'withdrawal_requests' => WithdrawalRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'approved_withdrawals' => WithdrawalRequest::where('status', 'COMPLETED')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),

            // Tickets
            'open_tickets' => Ticket::whereIn('status', ['OPEN', 'IN_PROGRESS'])->count(),
            'pending_tickets' => Ticket::where('status', 'PENDING')->count(),
            'urgent_tickets' => Ticket::where('priority', 'HIGH')->whereIn('status', ['OPEN', 'IN_PROGRESS'])->count(),
        ];
    }

    private function getChartData($startDate, $endDate)
    {
        // Évolution des colis par jour
        $packagesDaily = Package::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Évolution des revenus par jour
        $revenueDaily = Package::selectRaw('DATE(created_at) as date, SUM(delivery_fee) as total')
            ->where('status', 'DELIVERED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Répartition des statuts de colis
        $packageStatus = Package::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Répartition des méthodes de paiement
        $paymentMethods = Package::selectRaw('payment_method, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get()
            ->pluck('count', 'payment_method')
            ->toArray();

        return [
            'packages_daily' => $packagesDaily,
            'revenue_daily' => $revenueDaily,
            'package_status' => $packageStatus,
            'payment_methods' => $paymentMethods,
        ];
    }

    private function getTopPerformers($startDate, $endDate)
    {
        // Top clients par nombre de colis
        $topClientsByPackages = DB::table('packages')
            ->join('users', 'packages.sender_id', '=', 'users.id')
            ->select('users.name', 'users.email', DB::raw('COUNT(*) as package_count'))
            ->whereBetween('packages.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('package_count')
            ->limit(10)
            ->get();

        // Top livreurs par nombre de livraisons
        $topDeliverersByDeliveries = DB::table('packages')
            ->join('users', 'packages.assigned_deliverer_id', '=', 'users.id')
            ->select('users.name', 'users.phone', DB::raw('COUNT(*) as delivery_count'))
            ->where('packages.status', 'DELIVERED')
            ->whereBetween('packages.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name', 'users.phone')
            ->orderByDesc('delivery_count')
            ->limit(10)
            ->get();

        // Top délégations par activité
        $topDelegations = Package::selectRaw('delegation_to, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('delegation_to')
            ->groupBy('delegation_to')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'destination_delegation' => $item->delegation_to,
                    'count' => $item->count
                ];
            });

        return [
            'clients' => $topClientsByPackages,
            'deliverers' => $topDeliverersByDeliveries,
            'delegations' => $topDelegations,
        ];
    }

    private function getRevenueStatistics($startDate, $endDate)
    {
        // Revenus de livraison
        $deliveryRevenue = Package::where('status', 'DELIVERED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('delivery_fee');

        // Frais de retour
        $returnRevenue = Package::where('status', 'RETURNED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('return_fee');

        // COD collecté
        $codCollected = Package::where('payment_method', 'COD')
            ->where('status', 'DELIVERED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('cod_amount');

        // Évolution mensuelle des revenus (derniers 12 mois)
        $monthlyRevenue = Package::selectRaw("strftime('%Y', created_at) as year, strftime('%m', created_at) as month, SUM(delivery_fee) as total")
            ->where('status', 'DELIVERED')
            ->where('created_at', '>=', now()->subYear())
            ->groupByRaw("strftime('%Y', created_at), strftime('%m', created_at)")
            ->orderByRaw("strftime('%Y', created_at), strftime('%m', created_at)")
            ->get()
            ->map(function ($item) {
                return [
                    'period' => sprintf('%04d-%02d', $item->year, $item->month),
                    'total' => $item->total
                ];
            })
            ->pluck('total', 'period')
            ->toArray();

        return [
            'delivery_fees' => $deliveryRevenue,
            'return_fees' => $returnRevenue,
            'cod_collected' => $codCollected,
            'total_processed' => $deliveryRevenue + $returnRevenue,
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    public function apiKPIs(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        return response()->json($this->getMainKPIs($startDate, $endDate));
    }

    public function apiChartData(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        return response()->json($this->getChartData($startDate, $endDate));
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $data = [
            'kpis' => $this->getMainKPIs($startDate, $endDate),
            'revenue' => $this->getRevenueStatistics($startDate, $endDate),
            'performers' => $this->getTopPerformers($startDate, $endDate),
            'period' => $startDate . ' - ' . $endDate,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        return response()->json($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="analytics-report-' . now()->format('Y-m-d') . '.json"');
    }
}