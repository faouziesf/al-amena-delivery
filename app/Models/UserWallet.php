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
        'advance_balance',
        'advance_last_modified_at',
        'advance_last_modified_by',
        'last_transaction_at',
        'last_transaction_id'
    ];

    protected $casts = [
        'balance' => 'decimal:3',
        'pending_amount' => 'decimal:3',
        'frozen_amount' => 'decimal:3',
        'advance_balance' => 'decimal:3',
        'advance_last_modified_at' => 'datetime',
        'last_transaction_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advanceModifiedBy()
    {
        return $this->belongsTo(User::class, 'advance_last_modified_by');
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

    public function getFormattedAdvanceBalanceAttribute()
    {
        return number_format($this->advance_balance, 3) . ' DT';
    }

    public function getTotalAvailableForReturnFeesAttribute()
    {
        // Solde disponible + avance pour les frais de retour uniquement
        return $this->getAvailableBalanceAttribute() + $this->advance_balance;
    }

    // Helper methods
    public function hasSufficientBalance($amount)
    {
        return $this->getAvailableBalanceAttribute() >= $amount;
    }

    public function hasSufficientBalanceForReturnFees($amount)
    {
        // Vérifie si le solde + avance couvre les frais de retour
        return $this->getTotalAvailableForReturnFeesAttribute() >= $amount;
    }

    public function canUseAdvanceForReturnFees($amount)
    {
        // Vérifie si l'avance peut couvrir le montant demandé
        return $this->advance_balance >= $amount;
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
        return $this->balance + $this->pending_amount + $this->frozen_amount + $this->advance_balance;
    }

    // ================== MÉTHODES DE GESTION DES AVANCES ==================

    /**
     * Ajouter une avance au compte client (réservé COMMERCIAL/SUPERVISOR)
     */
    public function addAdvance($amount, $addedByUserId, $description = 'Avance accordée')
    {
        if ($amount <= 0) {
            throw new \Exception('Le montant de l\'avance doit être positif.');
        }

        $oldAdvanceBalance = $this->advance_balance;
        $newAdvanceBalance = $oldAdvanceBalance + $amount;

        $this->update([
            'advance_balance' => $newAdvanceBalance,
            'advance_last_modified_at' => now(),
            'advance_last_modified_by' => $addedByUserId,
            'last_transaction_at' => now()
        ]);

        // Créer la transaction d'avance
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('ADV'),
            'user_id' => $this->user_id,
            'type' => 'ADVANCE_CREDIT',
            'amount' => $amount,
            'status' => 'COMPLETED',
            'description' => $description,
            'reference' => 'ADVANCE_ADD_' . $addedByUserId,
            'wallet_balance_before' => $this->balance,
            'wallet_balance_after' => $this->balance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'add_advance',
                'advance_balance_before' => $oldAdvanceBalance,
                'advance_balance_after' => $newAdvanceBalance,
                'added_by_user_id' => $addedByUserId,
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    /**
     * Retirer une avance du compte client (réservé COMMERCIAL/SUPERVISOR)
     */
    public function removeAdvance($amount, $removedByUserId, $description = 'Avance retirée')
    {
        if ($amount <= 0) {
            throw new \Exception('Le montant à retirer doit être positif.');
        }

        if ($this->advance_balance < $amount) {
            throw new \Exception('Avance insuffisante. Disponible: ' . $this->formatted_advance_balance);
        }

        $oldAdvanceBalance = $this->advance_balance;
        $newAdvanceBalance = $oldAdvanceBalance - $amount;

        $this->update([
            'advance_balance' => $newAdvanceBalance,
            'advance_last_modified_at' => now(),
            'advance_last_modified_by' => $removedByUserId,
            'last_transaction_at' => now()
        ]);

        // Créer la transaction de retrait d'avance
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('RAV'),
            'user_id' => $this->user_id,
            'type' => 'ADVANCE_DEBIT',
            'amount' => -$amount,
            'status' => 'COMPLETED',
            'description' => $description,
            'reference' => 'ADVANCE_REMOVE_' . $removedByUserId,
            'wallet_balance_before' => $this->balance,
            'wallet_balance_after' => $this->balance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'remove_advance',
                'advance_balance_before' => $oldAdvanceBalance,
                'advance_balance_after' => $newAdvanceBalance,
                'removed_by_user_id' => $removedByUserId,
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    /**
     * Utiliser l'avance pour payer les frais de retour (UNIQUEMENT)
     */
    public function useAdvanceForReturnFees($amount, $packageCode = null, $description = 'Utilisation avance - Frais de retour')
    {
        if ($amount <= 0) {
            throw new \Exception('Le montant doit être positif.');
        }

        if (!$this->canUseAdvanceForReturnFees($amount)) {
            throw new \Exception('Avance insuffisante pour couvrir les frais de retour. Disponible: ' . $this->formatted_advance_balance);
        }

        $oldAdvanceBalance = $this->advance_balance;
        $newAdvanceBalance = $oldAdvanceBalance - $amount;

        $this->update([
            'advance_balance' => $newAdvanceBalance,
            'advance_last_modified_at' => now(),
            'last_transaction_at' => now()
        ]);

        // Créer la transaction d'utilisation d'avance
        $transaction = FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('UAV'),
            'user_id' => $this->user_id,
            'type' => 'ADVANCE_USAGE',
            'amount' => -$amount,
            'status' => 'COMPLETED',
            'description' => $description . ($packageCode ? " - Colis: {$packageCode}" : ''),
            'reference' => $packageCode ? "RETURN_FEES_{$packageCode}" : 'RETURN_FEES',
            'wallet_balance_before' => $this->balance,
            'wallet_balance_after' => $this->balance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'use_advance_return_fees',
                'advance_balance_before' => $oldAdvanceBalance,
                'advance_balance_after' => $newAdvanceBalance,
                'package_code' => $packageCode,
                'usage_type' => 'return_fees',
                'timestamp' => now()->toISOString()
            ])
        ]);

        $this->update(['last_transaction_id' => $transaction->transaction_id]);

        return $this;
    }

    /**
     * Déduire les frais en utilisant d'abord l'avance puis le solde normal
     * (Méthode spéciale pour les frais de retour)
     */
    public function deductReturnFees($amount, $packageCode = null, $description = 'Frais de retour')
    {
        if ($amount <= 0) {
            throw new \Exception('Le montant des frais doit être positif.');
        }

        if (!$this->hasSufficientBalanceForReturnFees($amount)) {
            throw new \Exception('Solde insuffisant (normal + avance) pour couvrir les frais de retour.');
        }

        $remainingAmount = $amount;

        // Utiliser d'abord l'avance si disponible
        if ($this->advance_balance > 0 && $remainingAmount > 0) {
            $advanceToUse = min($this->advance_balance, $remainingAmount);
            $this->useAdvanceForReturnFees($advanceToUse, $packageCode, $description);
            $remainingAmount -= $advanceToUse;
        }

        // Utiliser le solde normal si nécessaire
        if ($remainingAmount > 0) {
            $this->deductFunds($remainingAmount, $description . ($packageCode ? " - Colis: {$packageCode}" : ''), $packageCode);
        }

        return $this;
    }

    /**
     * Rembourser les frais de retour en restituant d'abord à l'avance puis au solde normal
     * (Inverse de deductReturnFees)
     */
    public function refundReturnFees($amount, $packageCode = null, $description = 'Remboursement frais de retour')
    {
        if ($amount <= 0) {
            throw new \Exception('Le montant du remboursement doit être positif.');
        }

        // Pour déterminer la répartition du remboursement, on pourrait vérifier l'historique
        // Mais pour simplifier, on va rembourser d'abord à l'avance (dans la limite de la capacité d'avance)
        // puis le reste au solde normal

        $remainingAmount = $amount;

        // Rechercher les transactions de déduction d'avance pour ce colis pour déterminer combien rembourser à l'avance
        $advanceTransactions = WalletTransaction::where('user_id', $this->user_id)
            ->where('type', 'ADVANCE_USE_RETURN_FEE')
            ->where('description', 'like', "%{$packageCode}%")
            ->where('amount', '<', 0)
            ->get();

        $totalAdvanceUsed = $advanceTransactions->sum(function($transaction) {
            return abs($transaction->amount);
        });

        // Rembourser à l'avance le montant qui avait été déduit de l'avance
        if ($totalAdvanceUsed > 0 && $remainingAmount > 0) {
            $advanceRefund = min($totalAdvanceUsed, $remainingAmount);
            $this->addAdvanceFunds($advanceRefund, $description . ($packageCode ? " - Colis: {$packageCode}" : '') . " (avance)", $packageCode);
            $remainingAmount -= $advanceRefund;
        }

        // Rembourser le reste au solde normal
        if ($remainingAmount > 0) {
            $this->addFunds($remainingAmount, $description . ($packageCode ? " - Colis: {$packageCode}" : '') . " (solde normal)", $packageCode);
        }

        return $this;
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

    /**
     * Get combined balance display for UI
     */
    public function getBalanceDisplayAttribute()
    {
        return [
            'balance' => $this->balance,
            'balance_formatted' => $this->formatted_balance,
            'advance' => $this->advance_balance,
            'advance_formatted' => $this->formatted_advance_balance,
            'total' => $this->balance + $this->advance_balance,
            'total_formatted' => number_format($this->balance + $this->advance_balance, 3) . ' DT'
        ];
    }
}