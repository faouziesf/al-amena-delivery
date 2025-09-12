<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'balance', 'pending_amount', 'frozen_amount',
        'last_transaction_at', 'last_transaction_id'
    ];

    protected $casts = [
        'balance' => 'decimal:3',
        'pending_amount' => 'decimal:3',
        'frozen_amount' => 'decimal:3',
        'last_transaction_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'user_id');
    }

    // Helper methods
    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->frozen_amount;
    }

    public function getTotalBalanceAttribute()
    {
        return $this->balance + $this->pending_amount;
    }

    public function hasSufficientBalance($amount)
    {
        return $this->getAvailableBalanceAttribute() >= $amount;
    }

    public function addFunds($amount, $description = 'Ajout de fonds')
    {
        $this->increment('balance', $amount);
        
        // Log de l'opération
        app(ActionLogService::class)->logWalletOperation('CREDIT', $this->user, $amount, [
            'description' => $description
        ]);
        
        return $this;
    }

    public function deductFunds($amount, $description = 'Déduction de fonds')
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Solde insuffisant');
        }
        
        $this->decrement('balance', $amount);
        
        // Log de l'opération
        app(ActionLogService::class)->logWalletOperation('DEBIT', $this->user, $amount, [
            'description' => $description
        ]);
        
        return $this;
    }
}