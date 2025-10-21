<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActionLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    protected $actionLogService;
    protected $notificationService;

    public function __construct(ActionLogService $actionLogService, NotificationService $notificationService)
    {
        $this->actionLogService = $actionLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Logger la création d'utilisateur
        $this->actionLogService->logCreated('User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ]);

        // Notifier l'utilisateur de la création de son compte
        $this->notificationService->create(
            $user->id,
            'USER_CREATED',
            '👋 Bienvenue !',
            "Votre compte a été créé avec succès. Bienvenue sur Al-Amena Delivery !",
            'HIGH',
            ['user_id' => $user->id]
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $dirty = $user->getDirty();
        
        if (empty($dirty)) {
            return;
        }

        $original = [];
        foreach (array_keys($dirty) as $key) {
            $original[$key] = $user->getOriginal($key);
        }

        // Logger la modification
        $this->actionLogService->logUpdated('User', $user->id, $original, $dirty);

        // Notifier selon les changements
        if ($user->isDirty('status')) {
            $oldStatus = $user->getOriginal('status');
            $newStatus = $user->status;

            if ($newStatus === 'ACTIVE' && $oldStatus !== 'ACTIVE') {
                $this->notificationService->create(
                    $user->id,
                    'USER_ACTIVATED',
                    '✅ Compte Activé',
                    'Votre compte a été activé. Vous pouvez maintenant vous connecter.',
                    'HIGH',
                    ['old_status' => $oldStatus, 'new_status' => $newStatus]
                );
            } elseif ($newStatus === 'SUSPENDED') {
                $this->notificationService->create(
                    $user->id,
                    'USER_SUSPENDED',
                    '⚠️ Compte Suspendu',
                    'Votre compte a été suspendu. Veuillez contacter l\'administration.',
                    'URGENT',
                    ['old_status' => $oldStatus, 'new_status' => $newStatus]
                );
            }
        }

        if ($user->isDirty('role')) {
            $oldRole = $user->getOriginal('role');
            $newRole = $user->role;

            $this->notificationService->create(
                $user->id,
                'USER_ROLE_CHANGED',
                '🔄 Rôle Modifié',
                "Votre rôle a été modifié de {$oldRole} à {$newRole}",
                'HIGH',
                ['old_role' => $oldRole, 'new_role' => $newRole]
            );
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->actionLogService->logDeleted('User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);
    }
}
