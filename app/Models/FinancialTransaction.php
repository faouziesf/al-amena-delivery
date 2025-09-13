<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 
        'user_id', 
        'type', 
        'amount', 
        'status',
        'package_id', 
        'description', 
        'sequence_number',
        'wallet_balance_before', 
        'wallet_balance_after',
        'checksum', 
        'metadata', 
        'completed_at',
        'reference'
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'wallet_balance_before' => 'decimal:3',
        'wallet_balance_after' => 'decimal:3',
        'metadata' => 'json', // Utiliser json au lieu de array pour plus de compatibilité
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'CREDIT');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'DEBIT');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    public function isCompleted()
    {
        return $this->status === 'COMPLETED';
    }

    public function isFailed()
    {
        return $this->status === 'FAILED';
    }

    public function isCredit()
    {
        return $this->type === 'CREDIT';
    }

    public function isDebit()
    {
        return $this->type === 'DEBIT';
    }

    // Formatage pour affichage
    public function getFormattedAmountAttribute()
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format($this->amount, 3) . ' DT';
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'CREDIT' => 'Crédit',
            'DEBIT' => 'Débit',
            'PACKAGE_PAYMENT' => 'Paiement colis',
            'PACKAGE_REFUND' => 'Remboursement colis',
            'WITHDRAWAL' => 'Retrait',
            'DELIVERY_FEE' => 'Frais livraison',
            default => $this->type
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'COMPLETED' => 'Terminé',
            'FAILED' => 'Échoué',
            'CANCELLED' => 'Annulé',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PENDING' => 'text-orange-600 bg-orange-100',
            'COMPLETED' => 'text-green-600 bg-green-100',
            'FAILED' => 'text-red-600 bg-red-100',
            'CANCELLED' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    // Méthodes de génération
    public static function generateTransactionId($prefix = 'TXN')
    {
        return $prefix . '_' . strtoupper(uniqid()) . '_' . date('Ymd');
    }

    public function generateChecksum()
    {
        return hash('sha256', 
            $this->transaction_id . 
            $this->user_id . 
            $this->amount . 
            ($this->wallet_balance_before ?? 0) . 
            $this->created_at
        );
    }

    public function verifyChecksum()
    {
        if (!$this->checksum) {
            return false;
        }
        return $this->checksum === $this->generateChecksum();
    }

    // Boot method pour auto-générer certaines valeurs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Auto-générer transaction_id si vide
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = static::generateTransactionId();
            }

            // Auto-générer checksum si vide
            if (empty($transaction->checksum)) {
                // On génère le checksum après avoir défini les autres valeurs
                $transaction->checksum = hash('sha256', 
                    $transaction->transaction_id . 
                    $transaction->user_id . 
                    $transaction->amount . 
                    ($transaction->wallet_balance_before ?? 0) . 
                    now()
                );
            }

            // Auto-générer sequence_number
            if (is_null($transaction->sequence_number)) {
                $lastSequence = static::where('user_id', $transaction->user_id)
                                    ->max('sequence_number') ?? 0;
                $transaction->sequence_number = $lastSequence + 1;
            }
        });
    }
}