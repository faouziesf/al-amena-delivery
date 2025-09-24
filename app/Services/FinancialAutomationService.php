<?php

namespace App\Services;

use App\Models\TopupRequest;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FinancialAutomationService
{
    /**
     * Service d'automatisation financière
     * Gère les processus automatiques de validation et traitement financier
     */

    // ==================== AUTO-VALIDATION TOPUP ====================

    /**
     * Auto-valider les demandes de rechargement éligibles
     * DÉSACTIVÉ : Les topups bancaires doivent toujours être gérés par le commercial
     */
    public function processAutoValidationTopups()
    {
        $results = [
            'processed' => 0,
            'validated' => 0,
            'rejected' => 0,
            'errors' => [],
            'message' => 'Auto-validation des topups désactivée - gestion manuelle requise par le commercial'
        ];

        Log::info('Auto-validation topups désactivée - gestion manuelle par commercial requise');

        return $results;
    }

    /**
     * Vérifier si un topup doit être auto-validé
     */
    protected function shouldAutoValidateTopup(TopupRequest $topup): bool
    {
        // Règles d'auto-validation
        $rules = [
            // Montant raisonnable (déjà filtré dans la requête)
            $topup->amount <= 500,

            // Client avec historique positif
            $this->hasPositiveClientHistory($topup->client),

            // Identifiant bancaire unique et valide
            $topup->bank_transfer_id && TopupRequest::isBankTransferIdUnique($topup->bank_transfer_id, $topup->id),

            // Pas plus de 3 demandes par jour pour ce client
            $this->isWithinDailyTopupLimit($topup->client),

            // Délai minimum respecté depuis dernière demande
            $this->isWithinTopupCooldown($topup->client),

            // Pattern de l'identifiant bancaire valide
            $this->isValidBankTransferId($topup->bank_transfer_id)
        ];

        return !in_array(false, $rules, true);
    }

    /**
     * Auto-valider un topup
     */
    protected function autoValidateTopup(TopupRequest $topup)
    {
        DB::transaction(function () use ($topup) {
            // Mettre à jour le statut
            $topup->update([
                'status' => 'VALIDATED',
                'processed_at' => now(),
                'processed_by_id' => 1, // ID système ou utilisateur automatique
                'validation_notes' => 'Validation automatique - Critères d\'éligibilité respectés'
            ]);

            // Ajouter les fonds au wallet
            $wallet = $topup->client->wallet;
            if (!$wallet) {
                $wallet = UserWallet::create(['user_id' => $topup->client_id, 'balance' => 0]);
            }

            $wallet->addFunds(
                $topup->amount,
                "Rechargement automatique - {$topup->request_code}",
                $topup->request_code
            );
        });
    }

    // ==================== VÉRIFICATIONS CLIENT ====================

    /**
     * Vérifier l'historique positif du client
     */
    protected function hasPositiveClientHistory(User $client): bool
    {
        if (!$client->wallet) return true; // Nouveau client = OK

        // Vérifier les transactions des 30 derniers jours
        $recentTransactions = $client->wallet->transactions()
            ->where('created_at', '>=', now()->subDays(30))
            ->completed()
            ->count();

        // Vérifier les packages récents
        $recentPackages = Package::where('client_id', $client->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Vérifier les échecs de paiement
        $failedTransactions = $client->wallet->transactions()
            ->where('created_at', '>=', now()->subDays(30))
            ->failed()
            ->count();

        // OK si: activité récente ET peu d'échecs
        return ($recentTransactions >= 1 || $recentPackages >= 1) && $failedTransactions <= 2;
    }

    /**
     * Vérifier la limite quotidienne de topups
     */
    protected function isWithinDailyTopupLimit(User $client): bool
    {
        $todayTopups = TopupRequest::where('client_id', $client->id)
            ->whereDate('created_at', today())
            ->count();

        return $todayTopups <= 3;
    }

    /**
     * Vérifier le délai minimum entre topups
     */
    protected function isWithinTopupCooldown(User $client): bool
    {
        $lastTopup = TopupRequest::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastTopup) return true;

        // Minimum 30 minutes entre les demandes
        return $lastTopup->created_at <= now()->subMinutes(30);
    }

    /**
     * Valider le format de l'identifiant bancaire
     */
    protected function isValidBankTransferId(string $bankTransferId): bool
    {
        // Patterns acceptables
        $patterns = [
            '/^TX[0-9]{8,12}$/i',           // TX12345678
            '/^REF[0-9]{6,10}$/i',          // REF123456
            '/^VIR[0-9]{8,12}$/i',          // VIR12345678
            '/^[0-9]{8,15}$/i',             // 123456789012345
            '/^[A-Z0-9]{6,20}$/i'           // Alphanumerique générique
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $bankTransferId)) {
                return true;
            }
        }

        return false;
    }

    // ==================== AUTO-TRAITEMENT COD ====================

    /**
     * Traiter automatiquement les paiements COD
     */
    public function processAutoCodPayments()
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // Packages COD livrés mais pas encore payés
            $codPackages = Package::where('payment_method', 'COD')
                ->where('status', 'DELIVERED')
                ->where('financial_status', 'PENDING')
                ->where('delivered_at', '<=', now()->subHours(24)) // Au moins 24h après livraison
                ->with(['client', 'client.wallet', 'deliverer', 'deliverer.wallet'])
                ->get();

            foreach ($codPackages as $package) {
                try {
                    $results['processed']++;

                    if ($this->processPackageCodPayment($package)) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                    }

                } catch (Exception $e) {
                    $results['errors'][] = [
                        'package_id' => $package->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

        } catch (Exception $e) {
            Log::error('Erreur auto-traitement COD', ['error' => $e->getMessage()]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Traiter le paiement COD d'un package
     */
    protected function processPackageCodPayment(Package $package): bool
    {
        try {
            DB::transaction(function () use ($package) {
                $clientWallet = $package->client->wallet;
                $delivererWallet = $package->deliverer->wallet;

                // Calculer les montants
                $deliveryFee = $package->delivery_price;
                $codAmount = $package->cod_amount ?? 0;
                $totalToClient = $codAmount - $deliveryFee;

                // Débiter les frais de livraison du client
                if ($deliveryFee > 0) {
                    $clientWallet->deductFunds(
                        $deliveryFee,
                        "Frais de livraison - Package #{$package->tracking_number}",
                        $package->tracking_number
                    );
                }

                // Créditer le livreur avec les frais
                if ($deliveryFee > 0) {
                    $delivererWallet->addFunds(
                        $deliveryFee,
                        "Frais de livraison - Package #{$package->tracking_number}",
                        $package->tracking_number
                    );
                }

                // Si COD > frais, créditer le client du reste
                if ($totalToClient > 0) {
                    $clientWallet->addFunds(
                        $totalToClient,
                        "COD restant - Package #{$package->tracking_number}",
                        $package->tracking_number
                    );
                }

                // Marquer le package comme payé
                $package->update([
                    'financial_status' => 'PAID',
                    'paid_at' => now()
                ]);
            });

            Log::info('Paiement COD automatique réussi', [
                'package_id' => $package->id,
                'tracking_number' => $package->tracking_number,
                'delivery_fee' => $package->delivery_price,
                'cod_amount' => $package->cod_amount
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Erreur paiement COD automatique', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ==================== RÉCONCILIATION ====================

    /**
     * Réconcilier automatiquement les wallets
     */
    public function reconcileWallets()
    {
        $results = [
            'checked' => 0,
            'inconsistencies' => 0,
            'fixed' => 0,
            'errors' => []
        ];

        try {
            $wallets = UserWallet::with(['user', 'transactions' => function($query) {
                $query->completed()->orderBy('created_at', 'desc');
            }])->get();

            foreach ($wallets as $wallet) {
                $results['checked']++;

                // Calculer le solde théorique basé sur les transactions
                $calculatedBalance = $wallet->transactions->sum('amount');

                // Comparer avec le solde enregistré
                if (abs($wallet->balance - $calculatedBalance) > 0.001) {
                    $results['inconsistencies']++;

                    Log::warning('Incohérence wallet détectée', [
                        'wallet_id' => $wallet->id,
                        'user_id' => $wallet->user_id,
                        'recorded_balance' => $wallet->balance,
                        'calculated_balance' => $calculatedBalance,
                        'difference' => $wallet->balance - $calculatedBalance
                    ]);

                    // Auto-correction si différence minime
                    if (abs($wallet->balance - $calculatedBalance) <= 1.000) {
                        $this->fixWalletBalance($wallet, $calculatedBalance);
                        $results['fixed']++;
                    }
                }
            }

        } catch (Exception $e) {
            Log::error('Erreur réconciliation wallets', ['error' => $e->getMessage()]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Corriger le solde d'un wallet
     */
    protected function fixWalletBalance(UserWallet $wallet, float $correctBalance)
    {
        $difference = $correctBalance - $wallet->balance;

        $wallet->update(['balance' => $correctBalance]);

        // Créer une transaction de correction
        FinancialTransaction::create([
            'transaction_id' => FinancialTransaction::generateTransactionId('COR'),
            'user_id' => $wallet->user_id,
            'type' => $difference >= 0 ? 'CREDIT' : 'DEBIT',
            'amount' => $difference,
            'status' => 'COMPLETED',
            'description' => 'Correction automatique du solde',
            'wallet_balance_before' => $wallet->balance - $difference,
            'wallet_balance_after' => $correctBalance,
            'completed_at' => now(),
            'metadata' => json_encode([
                'operation' => 'auto_correction',
                'difference' => $difference,
                'timestamp' => now()->toISOString()
            ])
        ]);
    }

    // ==================== NETTOYAGE ====================

    /**
     * Nettoyer les anciennes transactions et demandes
     */
    public function cleanupOldData()
    {
        $results = [
            'old_transactions_cleaned' => 0,
            'old_topup_requests_cleaned' => 0,
            'errors' => []
        ];

        try {
            // Nettoyer les transactions anciennes (garder 1 an)
            $cutoffDate = now()->subYear();

            $oldTransactionsCount = FinancialTransaction::where('created_at', '<', $cutoffDate)
                ->whereIn('status', ['COMPLETED', 'FAILED'])
                ->count();

            if ($oldTransactionsCount > 0) {
                // Archiver avant de supprimer (optionnel)
                FinancialTransaction::where('created_at', '<', $cutoffDate)
                    ->whereIn('status', ['COMPLETED', 'FAILED'])
                    ->delete();

                $results['old_transactions_cleaned'] = $oldTransactionsCount;
            }

            // Nettoyer les demandes de topup anciennes (garder 6 mois)
            $topupCutoffDate = now()->subMonths(6);

            $oldTopupsCount = TopupRequest::where('created_at', '<', $topupCutoffDate)
                ->whereIn('status', ['VALIDATED', 'REJECTED', 'CANCELLED'])
                ->count();

            if ($oldTopupsCount > 0) {
                TopupRequest::where('created_at', '<', $topupCutoffDate)
                    ->whereIn('status', ['VALIDATED', 'REJECTED', 'CANCELLED'])
                    ->delete();

                $results['old_topup_requests_cleaned'] = $oldTopupsCount;
            }

        } catch (Exception $e) {
            Log::error('Erreur nettoyage données anciennes', ['error' => $e->getMessage()]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Obtenir les statistiques du système financier
     */
    public function getFinancialSystemStats()
    {
        return [
            'wallets' => [
                'total' => UserWallet::count(),
                'with_balance' => UserWallet::withBalance('>', 0)->count(),
                'with_pending' => UserWallet::withPendingAmount()->count(),
                'with_frozen' => UserWallet::withFrozenAmount()->count(),
            ],
            'transactions' => [
                'total' => FinancialTransaction::count(),
                'today' => FinancialTransaction::today()->count(),
                'pending' => FinancialTransaction::pending()->count(),
                'completed' => FinancialTransaction::completed()->count(),
                'failed' => FinancialTransaction::failed()->count(),
            ],
            'topup_requests' => [
                'total' => TopupRequest::count(),
                'pending' => TopupRequest::pending()->count(),
                'validated_today' => TopupRequest::validated()->today()->count(),
                'auto_validation_eligible' => TopupRequest::pending()
                    ->bankTransfers()
                    ->where('created_at', '<=', now()->subHours(2))
                    ->where('amount', '<=', 500)
                    ->whereNotNull('bank_transfer_id')
                    ->count(),
            ],
            'total_system_balance' => UserWallet::sum('balance'),
            'total_pending_amount' => UserWallet::sum('pending_amount'),
            'total_frozen_amount' => UserWallet::sum('frozen_amount'),
        ];
    }
}