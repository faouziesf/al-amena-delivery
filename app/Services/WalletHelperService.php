<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;

class WalletHelperService
{
    /**
     * Formater un montant en dinars tunisiens
     */
    public static function formatAmount(float $amount, int $decimals = 3): string
    {
        return number_format($amount, $decimals, ',', ' ') . ' DT';
    }

    /**
     * Formater un montant avec signe (+ ou -)
     */
    public static function formatAmountWithSign(float $amount, int $decimals = 3): string
    {
        $sign = $amount >= 0 ? '+' : '';
        return $sign . self::formatAmount($amount, $decimals);
    }

    /**
     * Obtenir la couleur CSS selon le montant
     */
    public static function getAmountColor(float $amount): string
    {
        if ($amount > 0) {
            return 'text-green-600';
        } elseif ($amount < 0) {
            return 'text-red-600';
        }
        return 'text-gray-600';
    }

    /**
     * Obtenir l'icône selon le type de transaction
     */
    public static function getTransactionIcon(string $type, bool $isCredit = true): string
    {
        $icons = [
            'CREDIT' => '↗',
            'DEBIT' => '↙',
            'PACKAGE_PAYMENT' => '📦',
            'WITHDRAWAL' => '💸',
            'DELIVERY_FEE' => '🚚',
            'TOPUP' => '💳',
            'REFUND' => '↩️',
        ];

        return $icons[$type] ?? ($isCredit ? '⬆️' : '⬇️');
    }

    /**
     * Calculer le solde disponible pour un wallet
     */
    public static function getAvailableBalance(UserWallet $wallet): float
    {
        return max(0, $wallet->balance - ($wallet->frozen_amount ?? 0));
    }

    /**
     * Vérifier si un wallet a suffisamment de fonds
     */
    public static function hasSufficientBalance(UserWallet $wallet, float $amount): bool
    {
        return self::getAvailableBalance($wallet) >= $amount;
    }

    /**
     * Obtenir les statistiques d'un wallet pour une période
     */
    public static function getWalletStats(User $user, int $days = 30): array
    {
        $since = now()->subDays($days);
        
        $transactions = $user->transactions()
            ->where('created_at', '>=', $since)
            ->where('status', 'COMPLETED')
            ->get();

        return [
            'total_credits' => $transactions->where('amount', '>', 0)->sum('amount'),
            'total_debits' => abs($transactions->where('amount', '<', 0)->sum('amount')),
            'transaction_count' => $transactions->count(),
            'net_amount' => $transactions->sum('amount'),
            'period_days' => $days,
            'average_transaction' => $transactions->count() > 0 ? $transactions->avg('amount') : 0,
            'largest_credit' => $transactions->where('amount', '>', 0)->max('amount') ?? 0,
            'largest_debit' => abs($transactions->where('amount', '<', 0)->min('amount') ?? 0),
        ];
    }

    /**
     * Obtenir le statut d'un wallet (couleur et texte)
     */
    public static function getWalletStatus(UserWallet $wallet): array
    {
        $availableBalance = self::getAvailableBalance($wallet);
        
        if ($availableBalance <= 0) {
            return [
                'status' => 'empty',
                'text' => 'Vide',
                'color' => 'text-red-600 bg-red-100',
                'icon' => '⚠️'
            ];
        } elseif ($availableBalance < 50) {
            return [
                'status' => 'low',
                'text' => 'Solde faible',
                'color' => 'text-yellow-600 bg-yellow-100',
                'icon' => '⚡'
            ];
        } elseif ($availableBalance >= 500) {
            return [
                'status' => 'high',
                'text' => 'Solde élevé',
                'color' => 'text-green-600 bg-green-100',
                'icon' => '💰'
            ];
        }
        
        return [
            'status' => 'normal',
            'text' => 'Normal',
            'color' => 'text-blue-600 bg-blue-100',
            'icon' => '✓'
        ];
    }

    /**
     * Générer un résumé textuel d'un wallet
     */
    public static function getWalletSummary(UserWallet $wallet): string
    {
        $available = self::getAvailableBalance($wallet);
        $status = self::getWalletStatus($wallet);
        
        $summary = "Solde: " . self::formatAmount($wallet->balance);
        
        if ($wallet->frozen_amount > 0) {
            $summary .= " (dont " . self::formatAmount($wallet->frozen_amount) . " gelé)";
        }
        
        if ($wallet->pending_amount > 0) {
            $summary .= " • En attente: " . self::formatAmount($wallet->pending_amount);
        }
        
        $summary .= " • Disponible: " . self::formatAmount($available);
        $summary .= " • Statut: " . $status['text'];
        
        return $summary;
    }

    /**
     * Valider qu'un montant est acceptable pour une transaction
     */
    public static function validateTransactionAmount(float $amount, string $type = 'DEBIT'): array
    {
        $errors = [];
        
        // Montant doit être positif
        if ($amount <= 0) {
            $errors[] = 'Le montant doit être supérieur à zéro';
        }
        
        // Limites par type
        $limits = [
            'WITHDRAWAL' => ['min' => 1, 'max' => 10000],
            'TOPUP' => ['min' => 10, 'max' => 1000],
            'DEBIT' => ['min' => 0.001, 'max' => 50000],
            'CREDIT' => ['min' => 0.001, 'max' => 50000],
        ];
        
        if (isset($limits[$type])) {
            $min = $limits[$type]['min'];
            $max = $limits[$type]['max'];
            
            if ($amount < $min) {
                $errors[] = "Montant minimum: " . self::formatAmount($min);
            }
            
            if ($amount > $max) {
                $errors[] = "Montant maximum: " . self::formatAmount($max);
            }
        }
        
        // Vérification précision (maximum 3 décimales)
        if (round($amount, 3) !== $amount) {
            $errors[] = 'Maximum 3 décimales autorisées';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'formatted_amount' => self::formatAmount($amount)
        ];
    }

    /**
     * Calculer les frais pour un type d'opération
     */
    public static function calculateFees(string $operation, float $amount): array
    {
        $fees = [
            'WITHDRAWAL_BANK' => ['rate' => 0, 'fixed' => 0, 'min' => 0, 'max' => 0],
            'WITHDRAWAL_CASH' => ['rate' => 0, 'fixed' => 0, 'min' => 0, 'max' => 0],
            'TOPUP_CARD' => ['rate' => 0, 'fixed' => 0, 'min' => 0, 'max' => 0],
            'TOPUP_BANK' => ['rate' => 0, 'fixed' => 0, 'min' => 0, 'max' => 0],
        ];
        
        if (!isset($fees[$operation])) {
            return ['fee' => 0, 'total' => $amount, 'description' => 'Aucun frais'];
        }
        
        $config = $fees[$operation];
        $calculatedFee = ($amount * $config['rate'] / 100) + $config['fixed'];
        
        // Appliquer min/max
        if ($config['min'] > 0) {
            $calculatedFee = max($calculatedFee, $config['min']);
        }
        if ($config['max'] > 0) {
            $calculatedFee = min($calculatedFee, $config['max']);
        }
        
        return [
            'fee' => $calculatedFee,
            'total' => $amount + $calculatedFee,
            'description' => $calculatedFee > 0 ? 
                "Frais: " . self::formatAmount($calculatedFee) : 
                'Aucun frais'
        ];
    }

    /**
     * Générer des suggestions de montants pour les retraits/recharges
     */
    public static function getSuggestedAmounts(UserWallet $wallet, string $type = 'WITHDRAWAL'): array
    {
        $available = self::getAvailableBalance($wallet);
        
        if ($type === 'WITHDRAWAL') {
            $base = [50, 100, 200, 500, 1000];
            return array_filter($base, fn($amount) => $amount <= $available);
        }
        
        if ($type === 'TOPUP') {
            return [50, 100, 200, 500];
        }
        
        return [];
    }

    /**
     * Vérifier la santé d'un wallet (anomalies, incohérences)
     */
    public static function checkWalletHealth(UserWallet $wallet): array
    {
        $issues = [];
        $warnings = [];
        
        // Vérifier solde négatif
        if ($wallet->balance < 0) {
            $issues[] = 'Solde négatif détecté';
        }
        
        // Vérifier montants gelés > solde
        if (($wallet->frozen_amount ?? 0) > $wallet->balance) {
            $issues[] = 'Montant gelé supérieur au solde';
        }
        
        // Vérifier dernière activité
        $lastActivity = $wallet->last_transaction_at;
        if ($lastActivity && $lastActivity->diffInDays() > 90) {
            $warnings[] = 'Aucune activité depuis plus de 90 jours';
        }
        
        // Vérifier solde élevé sans activité récente
        if ($wallet->balance > 1000 && $lastActivity && $lastActivity->diffInDays() > 30) {
            $warnings[] = 'Solde élevé sans activité récente';
        }
        
        return [
            'healthy' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
            'score' => empty($issues) ? (empty($warnings) ? 100 : 75) : 25
        ];
    }
}