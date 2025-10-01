<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code', 'client_id', 'amount', 'method', 'bank_details',
        'status', 'processed_by_commercial_id', 'assigned_deliverer_id', 'assigned_depot_manager_id',
        'delivery_receipt_code', 'delivered_at', 'delivery_proof',
        'processing_notes', 'rejection_reason', 'processed_at', 'assigned_package_id'
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'bank_details' => 'array',
        'delivery_proof' => 'array',
        'delivered_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function processedByCommercial()
    {
        return $this->belongsTo(User::class, 'processed_by_commercial_id');
    }

    public function assignedDeliverer()
    {
        return $this->belongsTo(User::class, 'assigned_deliverer_id');
    }

    public function assignedDepotManager()
    {
        return $this->belongsTo(User::class, 'assigned_depot_manager_id');
    }

    public function assignedPackage()
    {
        return $this->belongsTo(Package::class, 'assigned_package_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByCommercial($query, $commercialId)
    {
        return $query->where('processed_by_commercial_id', $commercialId);
    }

    public function scopeForCashDelivery($query)
    {
        return $query->where('method', 'CASH_DELIVERY')
                    ->where('status', 'APPROVED');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    public function isApproved()
    {
        return $this->status === 'APPROVED';
    }

    public function requiresCashDelivery()
    {
        return $this->method === 'CASH_DELIVERY';
    }

    public function canBeProcessed()
    {
        return $this->status === 'PENDING';
    }

    public function getMethodDisplayAttribute()
    {
        return match($this->method) {
            'BANK_TRANSFER' => 'Virement bancaire',
            'CASH_DELIVERY' => 'Livraison espèces',
            default => $this->method
        };
    }

    public function getStatusDisplayAttribute()
    {
        switch($this->status) {
            case 'PENDING': return 'En attente';
            case 'APPROVED': return 'Approuvée';
            case 'PROCESSED': return 'Traitée'; // Nouveau statut pour virements bancaires
            case 'READY_FOR_DELIVERY': return 'Prêt à livrer'; // Pour espèces assignées au livreur
            case 'IN_PROGRESS': return 'En cours de livraison';
            case 'DELIVERED': return 'Livrée'; // Nouveau statut final
            case 'COMPLETED': return 'Terminée';
            case 'REJECTED': return 'Rejetée';
            case 'CANCELLED': return 'Annulée';
            default: return $this->status;
        }
    }

    public function getStatusColorAttribute()
    {
        switch($this->status) {
            case 'PENDING': return 'text-orange-600 bg-orange-100';
            case 'APPROVED': return 'text-blue-600 bg-blue-100';
            case 'PROCESSED': return 'text-teal-600 bg-teal-100'; // Nouveau
            case 'READY_FOR_DELIVERY': return 'text-indigo-600 bg-indigo-100'; // Nouveau
            case 'IN_PROGRESS': return 'text-purple-600 bg-purple-100';
            case 'DELIVERED': return 'text-emerald-600 bg-emerald-100'; // Nouveau
            case 'COMPLETED': return 'text-green-600 bg-green-100';
            case 'REJECTED': return 'text-red-600 bg-red-100';
            case 'CANCELLED': return 'text-gray-600 bg-gray-100';
            default: return 'text-gray-600 bg-gray-100';
        }
    }

    // Relations
    public function transaction()
    {
        return $this->hasOne(FinancialTransaction::class, 'reference', 'request_code');
    }

    // Méthodes de gestion
    public function approve(User $commercial, $notes = null)
    {
        $this->update([
            'status' => 'APPROVED',
            'processed_by_commercial_id' => $commercial->id,
            'processing_notes' => $notes,
            'processed_at' => now()
        ]);

        // Mettre à jour le statut de la transaction correspondante
        if ($this->transaction) {
            $this->transaction->update(['status' => 'COMPLETED']);
        }

        // Si c'est une livraison espèces, générer le code de livraison
        if ($this->method === 'CASH_DELIVERY') {
            $this->update([
                'delivery_receipt_code' => 'DLV_' . strtoupper(Str::random(6)) . '_' . $this->id
            ]);
        }

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_APPROVED',
            'WithdrawalRequest',
            $this->id,
            'PENDING',
            'APPROVED',
            ['request_code' => $this->request_code, 'amount' => $this->amount]
        );

        return $this;
    }

    public function reject(User $commercial, $reason)
    {
        \DB::transaction(function () use ($commercial, $reason) {
            $this->update([
                'status' => 'REJECTED',
                'processed_by_commercial_id' => $commercial->id,
                'rejection_reason' => $reason,
                'processed_at' => now()
            ]);

            // Libérer le montant gelé (pas de remboursement car jamais débité)
            $this->client->ensureWallet();
            $this->client->wallet->frozen_amount = max(0, ($this->client->wallet->frozen_amount ?? 0) - $this->amount);
            $this->client->wallet->save();

            // Mettre à jour le statut de la transaction correspondante
            if ($this->transaction) {
                $this->transaction->update(['status' => 'CANCELLED']);
            }

            // Créer une transaction de libération
            app(\App\Services\FinancialTransactionService::class)->processTransaction([
                'user_id' => $this->client_id,
                'type' => 'WITHDRAWAL_RELEASE',
                'amount' => 0, // Pas de changement du solde, juste libération
                'status' => 'COMPLETED',
                'description' => "Libération de réservation pour demande de retrait rejetée #{$this->request_code}",
                'reference' => $this->request_code . '_RELEASE',
                'metadata' => [
                    'withdrawal_id' => $this->id,
                    'original_request' => $this->request_code,
                    'released_amount' => $this->amount,
                    'rejection_reason' => $reason,
                    'processed_by' => $commercial->id
                ]
            ]);

            app(\App\Services\ActionLogService::class)->log(
                'WITHDRAWAL_REJECTED',
                'WithdrawalRequest',
                $this->id,
                'PENDING',
                'REJECTED',
                ['request_code' => $this->request_code, 'reason' => $reason, 'refunded_amount' => $this->amount]
            );
        });

        return $this;
    }

    public function cancel($reason = null)
    {
        if (!$this->canBeCancelled()) {
            throw new \Exception('Cette demande ne peut plus être annulée.');
        }

        \DB::transaction(function () use ($reason) {
            $this->update([
                'status' => 'CANCELLED',
                'rejection_reason' => $reason ?? 'Annulée par le client',
                'processed_at' => now()
            ]);

            // Libérer le montant gelé (pas de remboursement car jamais débité)
            $this->client->ensureWallet();
            $this->client->wallet->frozen_amount = max(0, ($this->client->wallet->frozen_amount ?? 0) - $this->amount);
            $this->client->wallet->save();

            // Mettre à jour le statut de la transaction correspondante
            if ($this->transaction) {
                $this->transaction->update(['status' => 'CANCELLED']);
            }

            // Créer une transaction de libération
            app(\App\Services\FinancialTransactionService::class)->processTransaction([
                'user_id' => $this->client_id,
                'type' => 'WITHDRAWAL_RELEASE',
                'amount' => 0, // Pas de changement du solde, juste libération
                'status' => 'COMPLETED',
                'description' => "Libération de réservation pour demande de retrait annulée #{$this->request_code}",
                'reference' => $this->request_code . '_CANCEL_RELEASE',
                'metadata' => [
                    'withdrawal_id' => $this->id,
                    'original_request' => $this->request_code,
                    'cancellation_reason' => $reason,
                    'cancelled_by' => 'CLIENT'
                ]
            ]);

            app(\App\Services\ActionLogService::class)->log(
                'WITHDRAWAL_CANCELLED',
                'WithdrawalRequest',
                $this->id,
                'PENDING',
                'CANCELLED',
                ['request_code' => $this->request_code, 'reason' => $reason, 'refunded_amount' => $this->amount]
            );
        });

        return $this;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['PENDING']);
    }

    public function assignToDeliverer(User $deliverer)
    {
        $this->update([
            'assigned_deliverer_id' => $deliverer->id,
            'status' => 'IN_PROGRESS'
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_ASSIGNED_DELIVERER',
            'WithdrawalRequest',
            $this->id,
            'APPROVED',
            'IN_PROGRESS',
            ['deliverer_id' => $deliverer->id, 'deliverer_name' => $deliverer->name]
        );

        return $this;
    }

    public function markAsDelivered($deliveryProof = [])
    {
        $this->update([
            'status' => 'COMPLETED',
            'delivered_at' => now(),
            'delivery_proof' => $deliveryProof
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_DELIVERED',
            'WithdrawalRequest',
            $this->id,
            'IN_PROGRESS',
            'COMPLETED',
            ['request_code' => $this->request_code, 'delivered_at' => now()]
        );

        return $this;
    }

    /**
     * Marquer comme traité (pour virements bancaires)
     */
    public function markAsProcessed($notes = null)
    {
        $this->update([
            'status' => 'PROCESSED',
            'processing_notes' => $notes,
            'processed_at' => now()
        ]);

        // Mettre à jour le statut de la transaction correspondante
        if ($this->transaction) {
            $this->transaction->update(['status' => 'COMPLETED']);
        }

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_PROCESSED',
            'WithdrawalRequest',
            $this->id,
            'APPROVED',
            'PROCESSED',
            ['request_code' => $this->request_code, 'notes' => $notes]
        );

        return $this;
    }

    /**
     * Marquer comme livré (nouveau statut final)
     */
    public function markAsDeliveredFinal($deliveryProof = [])
    {
        \DB::transaction(function () use ($deliveryProof) {
            $this->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'delivery_proof' => $deliveryProof
            ]);

            // Maintenant débiter réellement le solde du client et libérer le gel
            $this->client->ensureWallet();

            // Débiter le solde
            $this->client->wallet->balance -= $this->amount;

            // Libérer le montant gelé
            $this->client->wallet->frozen_amount = max(0, ($this->client->wallet->frozen_amount ?? 0) - $this->amount);

            $this->client->wallet->save();

            // Créer la transaction finale de débit
            app(\App\Services\FinancialTransactionService::class)->processTransaction([
                'user_id' => $this->client_id,
                'type' => 'WITHDRAWAL',
                'amount' => -$this->amount, // Montant négatif pour débit
                'status' => 'COMPLETED',
                'description' => "Retrait livré #{$this->request_code}",
                'reference' => $this->request_code . '_FINAL',
                'metadata' => [
                    'withdrawal_id' => $this->id,
                    'original_request' => $this->request_code,
                    'delivery_proof' => $deliveryProof,
                    'delivered_at' => now()
                ]
            ]);

            // Mettre à jour la transaction de réservation
            if ($this->transaction) {
                $this->transaction->update(['status' => 'COMPLETED']);
            }

            app(\App\Services\ActionLogService::class)->log(
                'WITHDRAWAL_FINAL_DELIVERY',
                'WithdrawalRequest',
                $this->id,
                $this->getOriginal('status'),
                'DELIVERED',
                ['request_code' => $this->request_code, 'delivered_at' => now(), 'amount_debited' => $this->amount]
            );
        });

        return $this;
    }

    /**
     * Préparer pour livraison (espèces assignées au livreur)
     */
    public function prepareForDelivery()
    {
        $this->update([
            'status' => 'READY_FOR_DELIVERY'
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_READY_FOR_DELIVERY',
            'WithdrawalRequest',
            $this->id,
            'APPROVED',
            'READY_FOR_DELIVERY',
            ['request_code' => $this->request_code, 'deliverer_id' => $this->assigned_deliverer_id]
        );

        return $this;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_code)) {
                $request->request_code = 'WDR_' . strtoupper(Str::random(8)) . '_' . date('Ymd');
            }
        });
    }
}