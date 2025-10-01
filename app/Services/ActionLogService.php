<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActionLogService
{
    /**
     * Log une action utilisateur
     */
    public function log($action, $description = null, $data = null)
    {
        // Pour le moment, on log simplement dans les logs Laravel
        Log::info("Action: {$action}", [
            'description' => $description,
            'data' => $data,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
    }

    /**
     * Log une action avec un utilisateur spécifique
     */
    public function logForUser($userId, $action, $description = null, $data = null)
    {
        Log::info("Action: {$action}", [
            'description' => $description,
            'data' => $data,
            'user_id' => $userId,
            'timestamp' => now()
        ]);
    }

    /**
     * Log une modification de package
     */
    public function logPackageModification($packageId, $action, $oldData = null, $newData = null)
    {
        $this->log("package_modification", "Package {$packageId}: {$action}", [
            'package_id' => $packageId,
            'old_data' => $oldData,
            'new_data' => $newData
        ]);
    }

    /**
     * Log une connexion utilisateur
     */
    public function logLogin($user)
    {
        $this->logForUser($user->id, 'login', "User {$user->name} ({$user->email}) logged in", [
            'user_role' => $user->role,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Log une déconnexion utilisateur
     */
    public function logLogout($user)
    {
        $this->logForUser($user->id, 'logout', "User {$user->name} ({$user->email}) logged out", [
            'user_role' => $user->role,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}