<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DepotExchangeToProcess extends Notification implements ShouldQueue
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
            'client_name' => $this->package->sender->name ?? 'Client',
            'message' => "🔄 Échange livré à traiter - {$this->package->package_code}",
            'url' => route('depot-manager.exchanges.index'),
            'icon' => '🔄',
            'type' => 'exchange_to_process',
            'priority' => 'medium',
        ];
    }
}
