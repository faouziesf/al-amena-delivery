<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\Request;

class EnhancedActionLogController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
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

        $logs = $this->actionLogService->getCriticalLogs($filters);

        // Données pour les filtres
        $users = User::select('id', 'name', 'email', 'role')
                    ->orderBy('name')
                    ->get();

        $targetTypes = ActionLog::select('target_type')
                                ->distinct()
                                ->whereNotNull('target_type')
                                ->orderBy('target_type')
                                ->pluck('target_type');

        return view('supervisor.action-logs.critical', compact('logs', 'users', 'targetTypes'));
    }

    /**
     * API: Récupère les logs récents (pour dashboard)
     */
    public function apiRecent(Request $request)
    {
        $limit = $request->get('limit', 10);

        $logs = ActionLog::with('user:id,name,role')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'action_type' => $log->action_type,
                    'user_name' => $log->user ? $log->user->name : 'N/A',
                    'user_role' => $log->user_role,
                    'target_type' => $log->target_type,
                    'created_at' => $log->created_at->diffForHumans(),
                    'url' => route('supervisor.action-logs.show', $log->id),
                ];
            });

        return response()->json($logs);
    }
}
