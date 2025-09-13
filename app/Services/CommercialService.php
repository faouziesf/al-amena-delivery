<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CommercialService
{
    protected $actionLogService;

    public function __construct()
    {
        // Injection optionnelle du service de log
        if (app()->bound(ActionLogService::class)) {
            $this->actionLogService = app(ActionLogService::class);
        }
    }

    /**
     * Créer un compte client complet
     */
    public function createClientAccount(array $data, User $commercial)
    {
        return DB::transaction(function () use ($data, $commercial) {
            // Créer l'utilisateur
            $client = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'address' => $data['address'],
                'role' => 'CLIENT',
                'account_status' => 'PENDING',
                'created_by' => $commercial->id,
            ]);

            // Créer le profil client
            ClientProfile::create([
                'user_id' => $client->id,
                'shop_name' => $data['shop_name'] ?? null,
                'fiscal_number' => $data['fiscal_number'] ?? null,
                'business_sector' => $data['business_sector'] ?? null,
                'identity_document' => $data['identity_document'] ?? null,
                'offer_delivery_price' => $data['delivery_price'],
                'offer_return_price' => $data['return_price'],
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            // Créer le wallet
            UserWallet::create([
                'user_id' => $client->id,
                'balance' => 0,
                'pending_amount' => 0,
                'frozen_amount' => 0,
            ]);

            // Log de l'action si le service existe
            if ($this->actionLogService) {
                $this->actionLogService->log(
                    'CLIENT_ACCOUNT_CREATED',
                    'User',
                    $client->id,
                    null,
                    $data,
                    [
                        'created_by' => $commercial->name,
                        'account_status' => 'PENDING'
                    ]
                );
            }

            return $client->load(['clientProfile', 'wallet']);
        });
    }

    /**
     * Valider un compte client
     */
    public function validateClientAccount(User $client, User $commercial, array $options = [])
    {
        return DB::transaction(function () use ($client, $commercial, $options) {
            $client->update([
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $commercial->id,
            ]);

            // Log de l'action si le service existe
            if ($this->actionLogService) {
                $this->actionLogService->log(
                    'CLIENT_ACCOUNT_VALIDATED',
                    'User',
                    $client->id,
                    'PENDING',
                    'ACTIVE',
                    array_merge([
                        'validated_by' => $commercial->name,
                        'validation_date' => now()->toISOString()
                    ], $options)
                );
            }

            return $client;
        });
    }

    /**
     * Ajouter des fonds au wallet - VERSION SIMPLE
     */
    public function addFundsToWallet(User $client, float $amount, string $description, User $commercial)
    {
        return DB::transaction(function () use ($client, $amount, $description, $commercial) {
            // S'assurer que le client a un wallet
            if (!$client->wallet) {
                UserWallet::create([
                    'user_id' => $client->id,
                    'balance' => 0,
                    'pending_amount' => 0,
                    'frozen_amount' => 0,
                ]);
                $client->load('wallet');
            }

            $wallet = $client->wallet;
            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance + $amount;

            // Mettre à jour le wallet
            $wallet->update([
                'balance' => $newBalance,
                'last_transaction_at' => now()
            ]);

            // Créer la transaction
            FinancialTransaction::create([
                'transaction_id' => 'TXN_ADD_' . strtoupper(uniqid()),
                'user_id' => $client->id,
                'type' => 'CREDIT',
                'amount' => $amount,
                'status' => 'COMPLETED',
                'description' => $description,
                'wallet_balance_before' => $oldBalance,
                'wallet_balance_after' => $newBalance,
                'metadata' => json_encode([
                    'added_by_commercial' => $commercial->id,
                    'commercial_name' => $commercial->name,
                    'ip_address' => request()->ip()
                ]),
                'completed_at' => now()
            ]);

            // Log de l'action si le service existe
            if ($this->actionLogService) {
                $this->actionLogService->log(
                    'WALLET_FUNDS_ADDED',
                    'UserWallet',
                    $wallet->id,
                    $oldBalance,
                    $newBalance,
                    [
                        'amount' => $amount,
                        'description' => $description,
                        'added_by' => $commercial->name
                    ]
                );
            }

            return [
                'wallet_balance' => $newBalance,
                'transaction_amount' => $amount,
                'description' => $description
            ];
        });
    }

    /**
     * Déduire des fonds du wallet - VERSION SIMPLE
     */
    public function deductFundsFromWallet(User $client, float $amount, string $description, User $commercial)
    {
        return DB::transaction(function () use ($client, $amount, $description, $commercial) {
            if (!$client->wallet) {
                throw new \Exception('Le client n\'a pas de wallet.');
            }

            $wallet = $client->wallet;

            if ($wallet->balance < $amount) {
                throw new \Exception('Solde insuffisant.');
            }

            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance - $amount;

            // Mettre à jour le wallet
            $wallet->update([
                'balance' => $newBalance,
                'last_transaction_at' => now()
            ]);

            // Créer la transaction
            FinancialTransaction::create([
                'transaction_id' => 'TXN_DED_' . strtoupper(uniqid()),
                'user_id' => $client->id,
                'type' => 'DEBIT',
                'amount' => -$amount, // Négatif pour les débits
                'status' => 'COMPLETED',
                'description' => $description,
                'wallet_balance_before' => $oldBalance,
                'wallet_balance_after' => $newBalance,
                'metadata' => json_encode([
                    'deducted_by_commercial' => $commercial->id,
                    'commercial_name' => $commercial->name,
                    'ip_address' => request()->ip()
                ]),
                'completed_at' => now()
            ]);

            // Log de l'action si le service existe
            if ($this->actionLogService) {
                $this->actionLogService->log(
                    'WALLET_FUNDS_DEDUCTED',
                    'UserWallet',
                    $wallet->id,
                    $oldBalance,
                    $newBalance,
                    [
                        'amount' => $amount,
                        'description' => $description,
                        'deducted_by' => $commercial->name
                    ]
                );
            }

            return [
                'wallet_balance' => $newBalance,
                'transaction_amount' => $amount,
                'description' => $description
            ];
        });
    }

    /**
     * Suspendre un compte client
     */
    public function suspendClientAccount(User $client, User $commercial, string $reason)
    {
        return DB::transaction(function () use ($client, $commercial, $reason) {
            $client->update([
                'account_status' => 'SUSPENDED',
                'verified_at' => null,
                'verified_by' => $commercial->id,
            ]);

            // Log de l'action si le service existe
            if ($this->actionLogService) {
                $this->actionLogService->log(
                    'CLIENT_ACCOUNT_SUSPENDED',
                    'User',
                    $client->id,
                    'ACTIVE',
                    'SUSPENDED',
                    [
                        'suspended_by' => $commercial->name,
                        'reason' => $reason,
                        'suspension_date' => now()->toISOString()
                    ]
                );
            }

            return $client;
        });
    }

    /**
     * Obtenir les statistiques d'un commercial
     */
    public function getCommercialStats(User $commercial)
    {
        $stats = [
            'total_clients_created' => $commercial->createdClients()->count(),
            'active_clients' => $commercial->createdClients()->active()->count(),
            'pending_clients' => $commercial->createdClients()->pending()->count(),
        ];

        // Ajouter des stats sur le mois en cours
        $thisMonth = now()->startOfMonth();
        $stats['clients_created_this_month'] = $commercial->createdClients()
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $stats['clients_validated_this_month'] = $commercial->validatedClients()
            ->where('verified_at', '>=', $thisMonth)
            ->count();

        return $stats;
    }

    /**
     * Méthode utilitaire pour formater un montant
     */
    public function formatAmount($amount)
    {
        return number_format($amount, 3) . ' DT';
    }

    /**
     * Vérifier la validité d'un wallet
     */
    public function validateWallet(UserWallet $wallet)
    {
        $errors = [];

        if ($wallet->balance < 0) {
            $errors[] = 'Le solde ne peut pas être négatif';
        }

        if ($wallet->pending_amount < 0) {
            $errors[] = 'Le montant en attente ne peut pas être négatif';
        }

        if ($wallet->frozen_amount < 0) {
            $errors[] = 'Le montant gelé ne peut pas être négatif';
        }

        if ($wallet->balance < $wallet->frozen_amount) {
            $errors[] = 'Le solde ne peut pas être inférieur au montant gelé';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }
}