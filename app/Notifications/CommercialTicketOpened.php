<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommercialTicketOpened extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'client_name' => $this->ticket->client->name ?? 'Client',
            'priority' => $this->ticket->priority ?? 'normal',
            'message' => "🎫 Nouveau ticket de {$this->ticket->client->name}",
            'url' => route('commercial.tickets.show', $this->ticket),
            'icon' => '🎫',
            'type' => 'new_ticket',
        ];
    }
}
