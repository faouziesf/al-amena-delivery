<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use App\Models\WalletTransactionBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FinancialTransactionService
{
    public function processTransaction(array $transactionData)
    {
        return DB::transaction(function () use ($transactionData) {
            try {
                // 1. Créer l'enregistrement transaction PENDING
                $pendingTransaction = $this->createPendingTransaction($transactionData);
                
                // 2. Backup avant transaction
                $this->createTransactionBackup($pendingTransaction);
                
                // 3. Effectuer modifications wallet avec vérifications
                $wallet = $this->updateWalletBalance($pendingTransaction);
                
                // 4. Marquer transaction COMPLETED
                $completedTransaction = $this->completeTransaction($pendingTransaction, $wallet);
                
                Log::info('Transaction financière réussie', [
                    'transaction_id' => $completedTransaction->transaction_id,
                    'user_id' => $completedTransaction->user_id,
                    'amount' => $completedTransaction->amount,
                    'type' => $completedTransaction->type
                ]);
                
                return [
                    'success' => true,
                    'transaction' => $completedTransaction,
                    'wallet_balance' => $wallet->balance
                ];
                
            } catch (\Exception $error) {
                Log::error('Échec transaction financière', [
                    'error' => $error->getMessage(),
                    'transaction_data' => $transactionData,
                    'trace' => $error->getTraceAsString()
                ]);
                
                throw new \Exception("Transaction failed: {$error->getMessage()}");
            }
        });
    }

    private function createPendingTransaction(array $data)
    {
        $user = User::findOrFail($data['user_id']);
        $wallet = $this->ensureWalletExists($user);
        
        $transaction = FinancialTransaction::create([
            'transaction_id' => $this->generateTransactionId(),
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'status' => 'PENDING',
            'package_id' => $data['package_id'] ?? null,
            'description' => $data['description'],
            'sequence_number' => $this->getNextSequenceNumber(),
            'wallet_balance_before' => $wallet->balance,
            'metadata' => $data['metadata'] ?? null,
        ]);
        
        $transaction->checksum = $transaction->generateChecksum();
        $transaction->save();
        
        return $transaction;
    }

    private function createTransactionBackup(FinancialTransaction $transaction)
    {
        WalletTransactionBackup::create([
            'transaction_id' => $transaction->transaction_id,
            'snapshot_data' => [
                'transaction' => $transaction->toArray(),
                'wallet_before' => $transaction->user->wallet->toArray(),
                'timestamp' => now()->toISOString()
            ],
            'backup_at' => now()
        ]);
    }

    private function updateWalletBalance(FinancialTransaction $transaction)
    {
        $wallet = $transaction->user->wallet;
        
        // Vérifications de sécurité
        if ($transaction->amount < 0 && !$wallet->hasSufficientBalance(abs($transaction->amount))) {
            throw new \Exception('Solde insuffisant pour cette transaction');
        }
        
        $newBalance = $wallet->balance + $transaction->amount;
        
        if ($newBalance < 0) {
            throw new \Exception('Le solde ne peut pas être négatif');
        }
        
        $wallet->update([
            'balance' => $newBalance,
            'last_transaction_at' => now(),
            'last_transaction_id' => $transaction->transaction_id
        ]);
        
        return $wallet->fresh();
    }

    private function completeTransaction(FinancialTransaction $transaction, UserWallet $wallet)
    {
        $transaction->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
            'wallet_balance_after' => $wallet->balance
        ]);
        
        return $transaction;
    }

    private function ensureWalletExists(User $user)
    {
        return UserWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0]
        );
    }

    private function generateTransactionId()
    {
        return 'TXN_' . strtoupper(Str::random(10)) . '_' . time();
    }

    private function getNextSequenceNumber()
    {
        $lastTransaction = FinancialTransaction::orderBy('sequence_number', 'desc')->first();
        return $lastTransaction ? $lastTransaction->sequence_number + 1 : 1000000;
    }

    // Récupération automatique des transactions bloquées
    public function recoverPendingTransactions()
    {
        $stuckTransactions = FinancialTransaction::where('status', 'PENDING')
            ->where('created_at', '<', now()->subMinutes(5))
            ->get();

        $recoveredCount = 0;
        
        foreach ($stuckTransactions as $transaction) {
            try {
                $systemState = $this->analyzeTransactionState($transaction);
                
                if ($systemState['can_auto_recover']) {
                    $wallet = $transaction->user->wallet;
                    $this->completeTransaction($transaction, $wallet);
                    $recoveredCount++;
                } else {
                    $this->markForManualReview($transaction, $systemState['issues']);
                }
            } catch (\Exception $error) {
                Log::error('Échec récupération transaction', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $error->getMessage()
                ]);
            }
        }
        
        return $recoveredCount;
    }

    private function analyzeTransactionState(FinancialTransaction $transaction)
    {
        $wallet = $transaction->user->wallet;
        $expectedBalance = $transaction->wallet_balance_before + $transaction->amount;
        
        return [
            'can_auto_recover' => $wallet->balance == $expectedBalance,
            'issues' => $wallet->balance != $expectedBalance ? ['Balance mismatch'] : []
        ];
    }

    private function markForManualReview(FinancialTransaction $transaction, array $issues)
    {
        $transaction->update([
            'status' => 'FAILED',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'manual_review_required' => true,
                'issues' => $issues,
                'marked_at' => now()->toISOString()
            ])
        ]);
    }
}