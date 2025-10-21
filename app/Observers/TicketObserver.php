<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\ActionLogService;
use App\Services\NotificationService;

class TicketObserver
{
    protected $actionLogService;
    protected $notificationService;

    public function __construct(ActionLogService $actionLogService, NotificationService $notificationService)
    {
        $this->actionLogService = $actionLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // Logger la création du ticket
        $this->actionLogService->logCreated('Ticket', $ticket->id, [
            'subject' => $ticket->subject,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'user_id' => $ticket->user_id
        ]);

        // Notifier l'utilisateur que son ticket a été créé
        $this->notificationService->notifyNewTicket($ticket->id, $ticket->user_id);

        // Notifier tous les commerciaux qu'un nouveau ticket a été créé
        $commercials = User::where('role', 'COMMERCIAL')
            ->where('status', 'ACTIVE')
            ->pluck('id')
            ->toArray();

        if (!empty($commercials)) {
            $priorityEmoji = match($ticket->priority) {
                'URGENT' => '🔴',
                'HIGH' => '🟠',
                'NORMAL' => '🟡',
                'LOW' => '🟢',
                default => '⚪'
            };

            $this->notificationService->createForUsers(
                $commercials,
                'TICKET_CREATED',
                "{$priorityEmoji} Nouveau Ticket",
                "Ticket #{$ticket->id}: {$ticket->subject}",
                $ticket->priority === 'URGENT' ? 'URGENT' : 'HIGH',
                ['ticket_id' => $ticket->id, 'priority' => $ticket->priority]
            );
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();
        
        if (empty($dirty)) {
            return;
        }

        $original = [];
        foreach (array_keys($dirty) as $key) {
            $original[$key] = $ticket->getOriginal($key);
        }

        // Logger la modification
        $this->actionLogService->logUpdated('Ticket', $ticket->id, $original, $dirty);

        // Notifier selon les changements
        if ($ticket->isDirty('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $newStatus = $ticket->status;

            // Notifier l'utilisateur du changement de statut
            if ($newStatus === 'RESOLVED') {
                $this->notificationService->create(
                    $ticket->user_id,
                    'TICKET_RESOLVED',
                    '✅ Ticket Résolu',
                    "Votre ticket #{$ticket->id} a été résolu",
                    'NORMAL',
                    ['ticket_id' => $ticket->id]
                );
            } elseif ($newStatus === 'IN_PROGRESS') {
                $this->notificationService->create(
                    $ticket->user_id,
                    'TICKET_IN_PROGRESS',
                    '🔄 Ticket en Traitement',
                    "Votre ticket #{$ticket->id} est en cours de traitement",
                    'NORMAL',
                    ['ticket_id' => $ticket->id]
                );
            }
        }

        if ($ticket->isDirty('priority')) {
            $oldPriority = $ticket->getOriginal('priority');
            $newPriority = $ticket->priority;

            // Si la priorité devient URGENT, notifier tous les commerciaux
            if ($newPriority === 'URGENT') {
                $commercials = User::where('role', 'COMMERCIAL')
                    ->where('status', 'ACTIVE')
                    ->pluck('id')
                    ->toArray();

                if (!empty($commercials)) {
                    $this->notificationService->createForUsers(
                        $commercials,
                        'TICKET_PRIORITY_CHANGED',
                        '🔴 Ticket URGENT',
                        "Le ticket #{$ticket->id} est maintenant URGENT",
                        'URGENT',
                        ['ticket_id' => $ticket->id, 'old_priority' => $oldPriority]
                    );
                }
            }
        }

        if ($ticket->isDirty('assigned_to')) {
            $oldAssigned = $ticket->getOriginal('assigned_to');
            $newAssigned = $ticket->assigned_to;

            // Notifier le nouveau responsable
            if ($newAssigned) {
                $this->notificationService->create(
                    $newAssigned,
                    'TICKET_ASSIGNED',
                    '📌 Ticket Assigné',
                    "Le ticket #{$ticket->id} vous a été assigné",
                    'HIGH',
                    ['ticket_id' => $ticket->id]
                );
            }

            // Notifier l'ancien responsable
            if ($oldAssigned && !$newAssigned) {
                $this->notificationService->create(
                    $oldAssigned,
                    'TICKET_UNASSIGNED',
                    '📌 Ticket Désassigné',
                    "Le ticket #{$ticket->id} ne vous est plus assigné",
                    'NORMAL',
                    ['ticket_id' => $ticket->id]
                );
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        $this->actionLogService->logDeleted('Ticket', $ticket->id, [
            'subject' => $ticket->subject,
            'status' => $ticket->status
        ]);
    }
}
