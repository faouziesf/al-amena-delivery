<?php

namespace App\Notifications;

use App\Models\PickupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DelivererNewPickup extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PickupRequest $pickup)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'pickup_id' => $this->pickup->id,
            'pickup_code' => $this->pickup->pickup_code ?? 'P-' . $this->pickup->id,
            'address' => $this->pickup->pickup_address,
            'governorate' => $this->pickup->delegation->governorate ?? '',
            'delegation' => $this->pickup->delegation->name ?? '',
            'message' => "📦 Nouveau ramassage disponible dans votre zone",
            'url' => '/deliverer/pickups/available',
            'icon' => '📦',
            'type' => 'new_pickup',
            'priority' => 'high',
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return [
            'title' => '📦 Nouveau Ramassage',
            'body' => "Ramassage disponible: {$this->pickup->pickup_address}",
            'icon' => '/images/icons/pickup.png',
            'url' => '/deliverer/pickups/available',
            'data' => [
                'pickup_id' => $this->pickup->id,
                'address' => $this->pickup->pickup_address,
            ],
        ];
    }
}
