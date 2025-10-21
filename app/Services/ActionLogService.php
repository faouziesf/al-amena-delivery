<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActionLogService
{
    /**
     * Log une action utilisateur dans la base de données
     */
    public function log(string $action, string $targetType = null, int $targetId = null, array $oldValue = null, array $newValue = null, array $additionalData = null)
    {
        try {
            $user = auth()->user();
            
            ActionLog::create([
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
        } catch (\Exception $e) {
            // Log dans les fichiers si erreur BDD
            Log::error("Erreur enregistrement action log: " . $e->getMessage(), [
                'action' => $action,
                'user_id' => auth()->id(),
            ]);
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
}