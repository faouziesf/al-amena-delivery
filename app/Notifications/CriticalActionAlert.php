<?php

namespace App\Notifications;

use App\Models\ActionLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalActionAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $actionLog;

    /**
     * Create a new notification instance.
     */
    public function __construct(ActionLog $actionLog)
    {
        $this->actionLog = $actionLog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionUser = $this->actionLog->user;
        $userName = $actionUser ? $actionUser->name : 'Utilisateur inconnu';
        
        return (new MailMessage)
            ->subject('🚨 Action Critique Détectée')
            ->line("Une action critique a été effectuée par {$userName}.")
            ->line("Type d'action: {$this->actionLog->action_type}")
            ->line("Entité ciblée: " . ($this->actionLog->target_type ?? 'N/A'))
            ->line("Date: " . $this->actionLog->created_at->format('d/m/Y à H:i'))
            ->action('Voir les détails', route('supervisor.action-logs.show', $this->actionLog->id))
            ->line('Veuillez vérifier cette action pour assurer la sécurité du système.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $actionUser = $this->actionLog->user;
        
        return [
            'action_log_id' => $this->actionLog->id,
            'action_type' => $this->actionLog->action_type,
            'target_type' => $this->actionLog->target_type,
            'target_id' => $this->actionLog->target_id,
            'user_id' => $this->actionLog->user_id,
            'user_name' => $actionUser ? $actionUser->name : 'Utilisateur inconnu',
            'user_role' => $this->actionLog->user_role,
            'message' => $this->formatMessage(),
            'url' => route('supervisor.action-logs.show', $this->actionLog->id),
        ];
    }

    /**
     * Formate le message de notification
     */
    private function formatMessage(): string
    {
        $actionUser = $this->actionLog->user;
        $userName = $actionUser ? $actionUser->name : 'Utilisateur inconnu';
        
        $actionType = str_replace('_', ' ', $this->actionLog->action_type);
        
        return "Action critique: {$actionType} effectuée par {$userName}";
    }
}
