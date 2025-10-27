<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\User;
use App\Models\CriticalActionConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CriticalActionAlert;

class ActionLogService
{
    /**
     * Log une action utilisateur dans la base de données
     */
    public function log(string $action, string $targetType = null, int $targetId = null, array $oldValue = null, array $newValue = null, array $additionalData = null)
    {
        try {
            $user = auth()->user();
            
            $actionLog = ActionLog::create([
                'user_id' => $user ? $user->id : null,
                'user_role' => $user ? $user->role : null,
                'action_type' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'additional_data' => $additionalData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Vérifier si c'est une action critique
            $this->checkCriticalAction($actionLog, $oldValue, $newValue);
        } catch (\Exception $e) {
            // Log dans les fichiers si erreur BDD
            Log::error("Erreur enregistrement action log: " . $e->getMessage(), [
                'action' => $action,
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Vérifie si une action est critique et notifie les superviseurs
     */
    private function checkCriticalAction(ActionLog $actionLog, ?array $oldValue, ?array $newValue)
    {
        // Préparer les données pour évaluation
        $data = array_merge(
            $oldValue ?? [],
            $newValue ?? [],
            $actionLog->additional_data ?? []
        );

        // Vérifier si l'action est critique
        if (CriticalActionConfig::isActionCritical($actionLog->action_type, $actionLog->target_type, $data)) {
            // Notifier les superviseurs
            $this->notifySupervisors($actionLog);
        }
    }

    /**
     * Notifie tous les superviseurs actifs d'une action critique
     */
    private function notifySupervisors(ActionLog $actionLog)
    {
        try {
            $supervisors = User::where('role', 'SUPERVISOR')
                ->where('account_status', 'ACTIVE')
                ->get();

            foreach ($supervisors as $supervisor) {
                $supervisor->notify(new CriticalActionAlert($actionLog));
            }
        } catch (\Exception $e) {
            Log::error("Erreur notification superviseurs pour action critique: " . $e->getMessage());
        }
    }

    /**
     * Log une action avec un utilisateur spécifique
     */
    public function logForUser(int $userId, string $action, string $targetType = null, int $targetId = null, array $oldValue = null, array $newValue = null)
    {
        try {
            $user = User::find($userId);
            
            ActionLog::create([
                'user_id' => $userId,
                'user_role' => $user ? $user->role : null,
                'action_type' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur enregistrement action log: " . $e->getMessage());
        }
    }

    /**
     * Log une création d'entité
     */
    public function logCreated(string $entityType, int $entityId, array $data = [])
    {
        $this->log(
            "{$entityType}_CREATED",
            $entityType,
            $entityId,
            null,
            $data
        );
    }

    /**
     * Log une modification d'entité
     */
    public function logUpdated(string $entityType, int $entityId, array $oldData, array $newData)
    {
        $this->log(
            "{$entityType}_UPDATED",
            $entityType,
            $entityId,
            $oldData,
            $newData
        );
    }

    /**
     * Log une suppression d'entité
     */
    public function logDeleted(string $entityType, int $entityId, array $data = [])
    {
        $this->log(
            "{$entityType}_DELETED",
            $entityType,
            $entityId,
            $data,
            null
        );
    }

    /**
     * Log un changement de statut
     */
    public function logStatusChanged(string $entityType, int $entityId, string $oldStatus, string $newStatus, array $additionalData = [])
    {
        $this->log(
            "STATUS_CHANGED",
            $entityType,
            $entityId,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $additionalData
        );
    }

    /**
     * Log une assignation
     */
    public function logAssignment(string $entityType, int $entityId, int $oldUserId = null, int $newUserId = null)
    {
        $this->log(
            "ASSIGNMENT_CHANGED",
            $entityType,
            $entityId,
            ['assigned_to' => $oldUserId],
            ['assigned_to' => $newUserId]
        );
    }

    /**
     * Log une modification de package
     */
    public function logPackageModification(int $packageId, string $action, array $oldData = null, array $newData = null)
    {
        $this->log(
            "PACKAGE_" . strtoupper($action),
            'Package',
            $packageId,
            $oldData,
            $newData
        );
    }

    /**
     * Log une connexion utilisateur
     */
    public function logLogin(User $user)
    {
        $this->logForUser(
            $user->id,
            'USER_LOGIN',
            'User',
            $user->id,
            null,
            [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        );
    }

    /**
     * Log une déconnexion utilisateur
     */
    public function logLogout(User $user)
    {
        $this->logForUser(
            $user->id,
            'USER_LOGOUT',
            'User',
            $user->id,
            null,
            [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        );
    }

    /**
     * Log une transaction financière
     */
    public function logFinancialTransaction(string $type, float $amount, int $userId, array $details = [])
    {
        $this->log(
            "FINANCIAL_" . strtoupper($type),
            'Transaction',
            null,
            null,
            array_merge([
                'amount' => $amount,
                'user_id' => $userId
            ], $details)
        );
    }

    /**
     * Log un changement de rôle utilisateur (action critique)
     */
    public function logRoleChanged(int $userId, string $oldRole, string $newRole, array $additionalData = [])
    {
        $this->log(
            "USER_ROLE_CHANGED",
            'User',
            $userId,
            ['role' => $oldRole],
            ['role' => $newRole],
            $additionalData
        );
    }

    /**
     * Log une validation financière (action critique)
     */
    public function logFinancialValidation(string $entityType, int $entityId, array $oldData, array $newData)
    {
        $this->log(
            "FINANCIAL_VALIDATION",
            $entityType,
            $entityId,
            $oldData,
            $newData
        );
    }

    /**
     * Log une impersonation (superviseur se connectant en tant qu'utilisateur)
     */
    public function logImpersonation(int $supervisorId, int $targetUserId, string $action = 'START')
    {
        $targetUser = User::find($targetUserId);
        
        $this->logForUser(
            $supervisorId,
            "IMPERSONATION_" . strtoupper($action),
            'User',
            $targetUserId,
            null,
            [
                'target_user_name' => $targetUser->name ?? 'Unknown',
                'target_user_email' => $targetUser->email ?? 'Unknown',
                'target_user_role' => $targetUser->role ?? 'Unknown',
            ]
        );
    }

    /**
     * Log une modification de paramètre système (action critique)
     */
    public function logSystemSettingChanged(string $settingKey, $oldValue, $newValue)
    {
        $this->log(
            "SYSTEM_SETTING_CHANGED",
            'SystemSetting',
            null,
            ['key' => $settingKey, 'value' => $oldValue],
            ['key' => $settingKey, 'value' => $newValue]
        );
    }

    /**
     * Récupère les logs d'actions critiques
     */
    public function getCriticalLogs($filters = [])
    {
        $criticalActions = CriticalActionConfig::getAllCriticalActions();
        $actionTypes = collect($criticalActions)->pluck('action_type')->toArray();

        $query = ActionLog::whereIn('action_type', $actionTypes)
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Appliquer les filtres
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Récupère l'activité récente d'un utilisateur
     */
    public function getUserActivity(int $userId, int $limit = 20)
    {
        return ActionLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}