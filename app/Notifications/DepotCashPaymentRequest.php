<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DepotCashPaymentRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WithdrawalRequest $withdrawal)
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
            'withdrawal_id' => $this->withdrawal->id,
            'client_name' => $this->withdrawal->client->name ?? 'Client',
            'amount' => $this->withdrawal->amount,
            'request_code' => $this->withdrawal->request_code ?? '',
            'message' => "💰 Paiement espèce à préparer - " . number_format($this->withdrawal->amount, 3) . " DT",
            'url' => route('depot-manager.payments.details', $this->withdrawal),
            'icon' => '💰',
            'type' => 'cash_payment_request',
            'priority' => 'high',
        ];
    }
}
