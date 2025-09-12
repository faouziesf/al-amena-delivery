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
        'status', 'processed_by_commercial_id', 'assigned_deliverer_id',
        'delivery_receipt_code', 'delivered_at', 'delivery_proof',
        'processing_notes', 'rejection_reason', 'processed_at'
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
        return match($this->status) {
            'PENDING' => 'En attente',
            'APPROVED' => 'Approuvée',
            'IN_PROGRESS' => 'En cours',
            'COMPLETED' => 'Terminée',
            'REJECTED' => 'Rejetée',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PENDING' => 'text-orange-600 bg-orange-100',
            'APPROVED' => 'text-blue-600 bg-blue-100',
            'IN_PROGRESS' => 'text-purple-600 bg-purple-100',
            'COMPLETED' => 'text-green-600 bg-green-100',
            'REJECTED' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
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
        $this->update([
            'status' => 'REJECTED',
            'processed_by_commercial_id' => $commercial->id,
            'rejection_reason' => $reason,
            'processed_at' => now()
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'WITHDRAWAL_REJECTED',
            'WithdrawalRequest',
            $this->id,
            'PENDING',
            'REJECTED',
            ['request_code' => $this->request_code, 'reason' => $reason]
        );

        return $this;
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