<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    /**
     * Afficher la liste des action logs avec filtres
     */
    public function index(Request $request)
    {
        $query = ActionLog::query()->with('user');

        // Filtres
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

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('user_name', 'LIKE', "%{$request->search}%");
            });
        }

        $logs = $query->latest()->paginate(50);

        // Pour les filtres
        $users = User::select('id', 'name', 'role')->orderBy('name')->get();
        $roles = User::select('role')->distinct()->pluck('role');
        $actions = ActionLog::select('action_type')->distinct()->whereNotNull('action_type')->orderBy('action_type')->pluck('action_type');
        $entityTypes = ActionLog::select('target_type')->distinct()->whereNotNull('target_type')->orderBy('target_type')->pluck('target_type');

        return view('supervisor.action-logs.index', compact('logs', 'users', 'roles', 'actions', 'entityTypes'));
    }

    /**
     * Afficher le détail d'un log
     */
    public function show(ActionLog $actionLog)
    {
        $actionLog->load('user');
        return view('supervisor.action-logs.show', compact('actionLog'));
    }

    /**
     * Export CSV des logs
     */
    public function export(Request $request)
    {
        $query = ActionLog::query()->with('user');

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
