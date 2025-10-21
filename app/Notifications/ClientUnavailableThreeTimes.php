<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientUnavailableThreeTimes extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Package $package)
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
            'package_id' => $this->package->id,
            'package_code' => $this->package->package_code,
            'message' => "⚠️ 3ème tentative échouée pour le colis {$this->package->package_code}",
            'warning' => 'Le colis sera retourné si vous restez indisponible',
            'url' => route('client.packages.show', $this->package),
            'icon' => '⚠️',
            'type' => 'unavailable_warning',
            'priority' => 'high',
        ];
    }
}
