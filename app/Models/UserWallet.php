<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'balance', 
        'pending_amount', 
        'frozen_amount',
        'last_transaction_at', 
        'last_transaction_id'
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
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'user_id')
                   ->orderBy('created_at', 'desc');
    }

    public function recentTransactions($limit = 10)
    {
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'user_id')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit);
    }

    // Accessors
    public function getAvailableBalanceAttribute()
    {
        return max(0, $this->balance - $this->frozen_amount);
    }

    public function getTotalBalanceAttribute()
    {
        return $this->balance + $this->pending_amount;
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 3) . ' DT';
    }

    public function getFormattedPendingAmountAttribute()
    {
        return number_format($this->pending_amount, 3) . ' DT';
    }

    public function getFormattedFrozenAmountAttribute()
    {
        return number_format($this->frozen_amount, 3) . ' DT';
    }

    // Helper methods
    public function hasSufficientBalance($amount)
    {
        return $this->getAvailableBalanceAttribute() >= $amount;
    }

    public function addFunds($amount, $description = 'Ajout de fonds', $reference = null)
    {
        $oldBalance = $this->balance;
        $newBalance = $oldBalance + $amount;
        
        $this->update([
            'balance' => $newBalance,
            'last_transaction_at' => now()
        ]);

        // Créer la transaction
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('ADD'),
            'user_id' => $this->user_id,
            'type' => 'CREDIT',
            'amount' => $amount,
            'status' => 'COMPLETED',
            'description' => $description,
            'reference' => $reference,
            'wallet_balance_before' => $oldBalance,
            'wallet_balance_after' => $newBalance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'add_funds',
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    public function deductFunds($amount, $description = 'Déduction de fonds', $reference = null)
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Solde insuffisant pour cette opération.');
        }

        $oldBalance = $this->balance;
        $newBalance = $oldBalance - $amount;
        
        $this->update([
            'balance' => $newBalance,
            'last_transaction_at' => now()
        ]);

        // Créer la transaction
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('DED'),
            'user_id' => $this->user_id,
            'type' => 'DEBIT',
            'amount' => -$amount, // Négatif pour les débits
            'status' => 'COMPLETED',
            'description' => $description,
            'reference' => $reference,
            'wallet_balance_before' => $oldBalance,
            'wallet_balance_after' => $newBalance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'deduct_funds',
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    public function freezeAmount($amount, $description = 'Montant gelé')
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Solde insuffisant pour geler ce montant.');
        }

        $this->increment('frozen_amount', $amount);
        $this->update(['last_transaction_at' => now()]);

        return $this;
    }

    public function unfreezeAmount($amount, $description = 'Montant dégelé')
    {
        if ($this->frozen_amount < $amount) {
            throw new \Exception('Montant gelé insuffisant.');
        }

        $this->decrement('frozen_amount', $amount);
        $this->update(['last_transaction_at' => now()]);

        return $this;
    }

    public function addPendingAmount($amount, $description = 'Montant en attente')
    {
        $this->increment('pending_amount', $amount);
        $this->update(['last_transaction_at' => now()]);

        return $this;
    }

    public function confirmPendingAmount($amount, $description = 'Confirmation montant en attente')
    {
        if ($this->pending_amount < $amount) {
            throw new \Exception('Montant en attente insuffisant.');
        }

        $oldBalance = $this->balance;
        $newBalance = $oldBalance + $amount;

        $this->update([
            'balance' => $newBalance,
            'pending_amount' => $this->pending_amount - $amount,
            'last_transaction_at' => now()
        ]);

        // Créer la transaction de confirmation
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('CNF'),
            'user_id' => $this->user_id,
            'type' => 'CREDIT',
            'amount' => $amount,
            'status' => 'COMPLETED',
            'description' => $description,
            'wallet_balance_before' => $oldBalance,
            'wallet_balance_after' => $newBalance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'confirm_pending',
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    // Méthodes de vérification
    public function isValid()
    {
        return $this->balance >= 0 && 
               $this->pending_amount >= 0 && 
               $this->frozen_amount >= 0 &&
               $this->balance >= $this->frozen_amount;
    }

    public function getTotalAmount()
    {
        return $this->balance + $this->pending_amount + $this->frozen_amount;
    }

    // Statistiques
    public function getTransactionsSummary($days = 30)
    {
        $since = now()->subDays($days);
        
        $transactions = FinancialTransaction::where('user_id', $this->user_id)
                                           ->where('created_at', '>=', $since)
                                           ->where('status', 'COMPLETED')
                                           ->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_credits' => $transactions->where('type', 'CREDIT')->sum('amount'),
            'total_debits' => abs($transactions->where('type', 'DEBIT')->sum('amount')),
            'net_amount' => $transactions->sum('amount'),
            'period_days' => $days
        ];
    }

    // Scopes
    public function scopeWithBalance($query, $operator = '>', $amount = 0)
    {
        return $query->where('balance', $operator, $amount);
    }

    public function scopeWithPendingAmount($query)
    {
        return $query->where('pending_amount', '>', 0);
    }

    public function scopeWithFrozenAmount($query)
    {
        return $query->where('frozen_amount', '>', 0);
    }

    public function scopeRecentlyActive($query, $days = 7)
    {
        return $query->where('last_transaction_at', '>=', now()->subDays($days));
    }
}