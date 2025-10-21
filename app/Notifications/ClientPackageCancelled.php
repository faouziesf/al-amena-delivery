<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientPackageCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Package $package, public string $reason)
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
            'reason' => $this->reason,
            'message' => "❌ Votre colis {$this->package->package_code} a été annulé",
            'url' => route('client.packages.show', $this->package),
            'icon' => '❌',
            'type' => 'package_cancelled',
        ];
    }
}
