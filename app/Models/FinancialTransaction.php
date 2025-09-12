<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'user_id', 'type', 'amount', 'status',
        'package_id', 'description', 'sequence_number',
        'wallet_balance_before', 'wallet_balance_after',
        'checksum', 'metadata', 'completed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'wallet_balance_before' => 'decimal:3',
        'wallet_balance_after' => 'decimal:3',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function backup()
    {
        return $this->hasOne(WalletTransactionBackup::class, 'transaction_id', 'transaction_id');
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

    public function generateChecksum()
    {
        return hash('sha256', 
            $this->transaction_id . 
            $this->user_id . 
            $this->amount . 
            $this->wallet_balance_before . 
            $this->created_at
        );
    }

    public function verifyChecksum()
    {
        return $this->checksum === $this->generateChecksum();
    }

    // Boot method pour auto-générer checksum
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->checksum)) {
                $transaction->checksum = $transaction->generateChecksum();
            }
        });
    }
}