<?php

namespace App\Notifications;

use App\Models\TopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommercialTopupRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TopupRequest $topup)
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
            'topup_id' => $this->topup->id,
            'client_name' => $this->topup->client->name ?? 'Client',
            'amount' => $this->topup->amount,
            'message' => "🔋 Nouvelle demande de recharge - " . number_format($this->topup->amount, 3) . " DT",
            'url' => route('commercial.topup-requests.show', $this->topup),
            'icon' => '🔋',
            'type' => 'topup_request',
        ];
    }
}
