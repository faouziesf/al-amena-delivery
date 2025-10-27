<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    /**
     * Affiche tous les logs d'actions
     */
    public function index(Request $request)
    {
        $query = ActionLog::with('user');

        // Filtres
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->has('target_type') && $request->target_type) {
            $query->where('target_type', $request->target_type);
        }

        if ($request->has('user_role') && $request->user_role) {
            $query->where('user_role', $request->user_role);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtre par période prédéfinie
        if ($request->has('period') && $request->period) {
            $period = $request->period;
            match($period) {
                'today' => $query->whereDate('created_at', today()),
                'yesterday' => $query->whereDate('created_at', now()->subDay()),
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month),
                '7days' => $query->where('created_at', '>=', now()->subDays(7)),
                '30days' => $query->where('created_at', '>=', now()->subDays(30)),
                default => null,
            };
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $logs = $query->paginate($request->get('per_page', 50));

        // Données pour les filtres
        $users = User::select('id', 'name', 'email', 'role')
                    ->orderBy('name')
                    ->get();

        $actionTypes = ActionLog::select('action_type')
                                ->distinct()
                                ->orderBy('action_type')
                                ->pluck('action_type');

        $targetTypes = ActionLog::select('target_type')
                                ->distinct()
                                ->whereNotNull('target_type')
                                ->orderBy('target_type')
                                ->pluck('target_type');

        $roles = ['CLIENT', 'DELIVERER', 'COMMERCIAL', 'SUPERVISOR', 'DEPOT_MANAGER'];

        // Alias pour compatibilité avec la vue
        $actions = $actionTypes;
        $entityTypes = $targetTypes;

        return view('supervisor.action-logs.index', compact(
            'logs',
            'users',
            'actionTypes',
            'actions',
            'targetTypes',
            'entityTypes',
            'roles'
        ));
    }

    /**
     * Affiche les détails d'un log
     */
    public function show(ActionLog $log)
    {
        $log->load('user');
        // Alias pour compatibilité avec la vue
        $actionLog = $log;
        return view('supervisor.action-logs.show', compact('log', 'actionLog'));
    }

    /**
     * Affiche uniquement les logs critiques
     */
    public function critical(Request $request)
    {
        $filters = [
            'user_id' => $request->get('user_id'),
            'target_type' => $request->get('target_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'per_page' => $request->get('per_page', 50),
        ];

        // Appliquer mêmes filtres
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('role')) {
            $query->where('user_role', $request->role);
        }

        if ($request->filled('action')) {
            $query->where('action_type', 'LIKE', "%{$request->action}%");
        }

        if ($request->filled('entity_type')) {
            $query->where('target_type', $request->entity_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->get();

        $csv = "Date,Heure,Utilisateur,Rôle,Action,Entité,ID Entité,IP\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $log->created_at->format('Y-m-d'),
                $log->created_at->format('H:i:s'),
                str_replace(',', ' ', $log->user->name ?? 'N/A'),
                $log->user_role ?? 'N/A',
                $log->action_type ?? 'N/A',
                $log->target_type ?? 'N/A',
                $log->target_id ?? 'N/A',
                $log->ip_address ?? 'N/A'
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="action_logs_' . date('Y-m-d_His') . '.csv"');
    }

    /**
     * Statistiques des logs
     */
    public function stats()
    {
        $stats = [
            'total' => ActionLog::count(),
            'today' => ActionLog::whereDate('created_at', today())->count(),
            'this_week' => ActionLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActionLog::whereMonth('created_at', now()->month)->count(),
            'by_action' => ActionLog::selectRaw('action_type, COUNT(*) as count')
                ->whereNotNull('action_type')
                ->groupBy('action_type')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'by_user' => ActionLog::selectRaw('user_id, user_role, COUNT(*) as count')
                ->with('user:id,name')
                ->whereNotNull('user_id')
                ->groupBy('user_id', 'user_role')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return view('supervisor.action-logs.stats', compact('stats'));
    }
}
