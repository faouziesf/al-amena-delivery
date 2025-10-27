<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Ticket;
use App\Models\FinancialTransaction;
use App\Models\UserWallet;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('account_status', 'ACTIVE')->count(),
            'total_packages' => Package::count(),
            'pending_packages' => Package::whereIn('status', ['CREATED', 'AVAILABLE'])->count(),
            'total_tickets' => Ticket::count(),
            'urgent_tickets' => Ticket::where('status', 'URGENT')->count(),
            'total_revenue' => FinancialTransaction::where('type', 'CREDIT')->sum('amount'),
            'pending_complaints' => Complaint::where('status', 'OPEN')->count(),
        ];

        // Statistiques par rôle
        $usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Packages par statut
        $packagesByStatus = Package::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Revenue des 7 derniers jours
        $revenueChart = collect(range(0, 6))->map(function($days) {
            $date = now()->subDays($days);
            return [
                'date' => $date->format('d/m'),
                'revenue' => FinancialTransaction::whereDate('created_at', $date)
                    ->where('type', 'CREDIT')
                    ->sum('amount')
            ];
        })->reverse();

        // Packages créés ces 7 derniers jours
        $packagesChart = collect(range(0, 6))->map(function($days) {
            $date = now()->subDays($days);
            return [
                'date' => $date->format('d/m'),
                'packages' => Package::whereDate('created_at', $date)->count()
            ];
        })->reverse();

        // Top 5 clients par volume
        $topClients = User::where('role', 'CLIENT')
            ->withCount('packages')
            ->orderByDesc('packages_count')
            ->take(5)
            ->get();

        // Alertes système
        $alerts = [];

        if ($stats['urgent_tickets'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => $stats['urgent_tickets'] . ' ticket(s) urgent(s) nécessitent votre attention',
                'url' => route('supervisor.tickets.index', ['status' => 'URGENT'])
            ];
        }

        if ($stats['pending_complaints'] > 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => $stats['pending_complaints'] . ' réclamations en attente de traitement',
                'url' => '#'
            ];
        }

        // Activité récente
        $recentActivity = collect([
            // Tickets créés aujourd'hui
            ...Ticket::whereDate('created_at', today())
                ->with('client')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($ticket) => [
                    'type' => 'ticket',
                    'message' => "Nouveau ticket #{$ticket->ticket_number} créé par {$ticket->client->name}",
                    'time' => $ticket->created_at,
                    'url' => route('supervisor.tickets.show', $ticket)
                ]),

            // Packages créés aujourd'hui
            ...Package::whereDate('created_at', today())
                ->with('sender')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($package) => [
                    'type' => 'package',
                    'message' => "Nouveau colis {$package->package_code} créé par {$package->sender->name}",
                    'time' => $package->created_at,
                    'url' => route('supervisor.packages.show', $package)
                ]),
        ])->sortByDesc('time')->take(10);

        return view('supervisor.dashboard-new', compact(
            'stats',
            'usersByRole',
            'packagesByStatus',
            'revenueChart',
            'packagesChart',
            'topClients',
            'alerts',
            'recentActivity'
        ));
    }

    public function apiStats(Request $request)
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('account_status', 'ACTIVE')->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->pluck('count', 'role'),
            ],
            'packages' => [
                'total' => Package::count(),
                'today' => Package::whereDate('created_at', today())->count(),
                'by_status' => Package::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ],
            'tickets' => [
                'total' => Ticket::count(),
                'urgent' => Ticket::where('status', 'URGENT')->count(),
                'open' => Ticket::where('status', 'OPEN')->count(),
            ],
            'financial' => [
                'total_revenue' => FinancialTransaction::where('type', 'CREDIT')->sum('amount'),
                'today_revenue' => FinancialTransaction::where('type', 'CREDIT')
                    ->whereDate('created_at', today())
                    ->sum('amount'),
            ],
            'system' => [
                'uptime' => now()->diffInDays(Carbon::createFromTimestamp(filectime(base_path()))),
                'last_backup' => 'N/A', // Placeholder
                'storage_used' => round(disk_total_space('.') - disk_free_space('.')) / 1024 / 1024 / 1024,
            ]
        ];

        return response()->json($stats);
    }

    public function apiSystemStatus()
    {
        $status = [
            'database' => $this->checkDatabaseStatus(),
            'storage' => $this->checkStorageStatus(),
            'cache' => $this->checkCacheStatus(),
            'queue' => $this->checkQueueStatus(),
        ];

        $overall = collect($status)->every(fn($service) => $service['status'] === 'healthy')
            ? 'healthy'
            : 'warning';

        return response()->json([
            'overall_status' => $overall,
            'services' => $status,
            'last_check' => now()->toISOString(),
        ]);
    }

    private function checkDatabaseStatus()
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkStorageStatus()
    {
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usedPercent = (1 - ($freeSpace / $totalSpace)) * 100;

        if ($usedPercent > 90) {
            return ['status' => 'error', 'message' => 'Storage critically low (' . round($usedPercent, 1) . '% used)'];
        } elseif ($usedPercent > 80) {
            return ['status' => 'warning', 'message' => 'Storage running low (' . round($usedPercent, 1) . '% used)'];
        } else {
            return ['status' => 'healthy', 'message' => 'Storage OK (' . round($usedPercent, 1) . '% used)'];
        }
    }

    private function checkCacheStatus()
    {
        try {
            cache(['health_check' => 'ok'], 1);
            $test = cache('health_check');
            return $test === 'ok'
                ? ['status' => 'healthy', 'message' => 'Cache working']
                : ['status' => 'warning', 'message' => 'Cache not responding correctly'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache error: ' . $e->getMessage()];
        }
    }

    private function checkQueueStatus()
    {
        // Placeholder - would need queue monitoring
        return ['status' => 'healthy', 'message' => 'Queue status unknown'];
    }
}
