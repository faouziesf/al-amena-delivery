<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
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
     * Créer un compte client complet - VERSION CORRIGÉE
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

            // CORRIGÉ : Utiliser ensureWallet() au lieu de create() direct
            $wallet = $client->ensureWallet();
            
            // Si ensureWallet() échoue, essayer firstOrCreate comme fallback
            if (!$wallet) {
                $wallet = UserWallet::firstOrCreate(
                    ['user_id' => $client->id],
                    [
                        'balance' => 0.000,
                        'pending_amount' => 0.000,
                        'frozen_amount' => 0.000,
                    ]
                );
            }

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
                        'account_status' => 'PENDING',
                        'wallet_created' => $wallet ? 'SUCCESS' : 'FAILED'
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
     * Ajouter des fonds au wallet - VERSION CORRIGÉE
     */
    public function addFundsToWallet(User $client, float $amount, string $description, User $commercial)
    {
        return DB::transaction(function () use ($client, $amount, $description, $commercial) {
            // CORRIGÉ : Utiliser ensureWallet() au lieu de create() direct
            $wallet = $client->ensureWallet();
            
            if (!$wallet) {
                throw new \Exception('Impossible de créer ou récupérer le wallet du client.');
            }

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
     * Déduire des fonds du wallet - VERSION CORRIGÉE
     */
    public function deductFundsFromWallet(User $client, float $amount, string $description, User $commercial)
    {
        return DB::transaction(function () use ($client, $amount, $description, $commercial) {
            // CORRIGÉ : Utiliser ensureWallet() pour s'assurer que le wallet existe
            $wallet = $client->ensureWallet();
            
            if (!$wallet) {
                throw new \Exception('Le client n\'a pas de wallet.');
            }

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
     * NOUVELLE MÉTHODE - Obtenir les statistiques du dashboard
     */
    public function getDashboardStats()
    {
        try {
            return [
                'total_clients' => User::where('role', 'CLIENT')->count(),
                'active_clients' => User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->count(),
                'pending_clients' => User::where('role', 'CLIENT')->where('account_status', 'PENDING')->count(),
                'suspended_clients' => User::where('role', 'CLIENT')->where('account_status', 'SUSPENDED')->count(),
                'total_packages' => Package::count(),
                'packages_in_progress' => Package::whereIn('status', ['ACCEPTED', 'PICKED_UP', 'OUT_FOR_DELIVERY'])->count(),
                'delivered_today' => Package::where('status', 'DELIVERED')->whereDate('updated_at', today())->count(),
                'pending_complaints' => Complaint::where('status', 'PENDING')->count(),
                'pending_withdrawals' => WithdrawalRequest::where('status', 'PENDING')->count(),
                'clients_this_month' => User::where('role', 'CLIENT')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'validated_this_week' => User::where('role', 'CLIENT')
                    ->where('account_status', 'ACTIVE')
                    ->whereBetween('verified_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur getDashboardStats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retourner des stats par défaut en cas d'erreur
            return [
                'total_clients' => 0,
                'active_clients' => 0,
                'pending_clients' => 0,
                'suspended_clients' => 0,
                'total_packages' => 0,
                'packages_in_progress' => 0,
                'delivered_today' => 0,
                'pending_complaints' => 0,
                'pending_withdrawals' => 0,
                'clients_this_month' => 0,
                'validated_this_week' => 0,
            ];
        }
    }

    /**
     * NOUVELLE MÉTHODE - Résumé des réclamations
     */
    public function getComplaintsSummary()
    {
        try {
            return [
                'total' => Complaint::count(),
                'pending' => Complaint::where('status', 'PENDING')->count(),
                'urgent' => Complaint::where('priority', 'URGENT')->count(),
                'resolved_today' => Complaint::where('status', 'RESOLVED')->whereDate('updated_at', today())->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur getComplaintsSummary:', ['error' => $e->getMessage()]);
            return ['total' => 0, 'pending' => 0, 'urgent' => 0, 'resolved_today' => 0];
        }
    }

    /**
     * NOUVELLE MÉTHODE - Activité récente
     */
    public function getRecentActivity()
    {
        try {
            return [
                'recent_clients' => User::where('role', 'CLIENT')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'created_at', 'account_status']),
                'recent_packages' => Package::with(['sender:id,name'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'package_code', 'sender_id', 'status', 'created_at']),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur getRecentActivity:', ['error' => $e->getMessage()]);
            return ['recent_clients' => [], 'recent_packages' => []];
        }
    }

    /**
     * NOUVELLE MÉTHODE - Réclamations en attente
     */
    public function getPendingComplaints(User $commercial = null)
    {
        try {
            $query = Complaint::with(['package', 'client'])->where('status', 'PENDING');
            
            if ($commercial) {
                // Filtrer par commercial si nécessaire
                $query->where('assigned_to', $commercial->id);
            }
            
            return $query->orderBy('created_at', 'desc')->limit(20)->get();
        } catch (\Exception $e) {
            Log::error('Erreur getPendingComplaints:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * NOUVELLE MÉTHODE - Demandes de retrait en attente
     */
    public function getPendingWithdrawals()
    {
        try {
            return WithdrawalRequest::with(['client'])
                ->where('status', 'PENDING')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur getPendingWithdrawals:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * NOUVELLE MÉTHODE - Livreurs avec wallets
     */
    public function getDeliverersWithWallets()
    {
        try {
            return User::with(['wallet'])
                ->where('role', 'DELIVERER')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erreur getDeliverersWithWallets:', ['error' => $e->getMessage()]);
            return collect([]);
        }
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

    /**
     * NOUVELLE MÉTHODE - Créer un wallet de manière sécurisée
     */
    public function ensureClientWallet(User $client)
    {
        if ($client->role !== 'CLIENT') {
            throw new \Exception('Cette méthode est réservée aux clients.');
        }

        return $client->ensureWallet();
    }

    /**
     * NOUVELLE MÉTHODE - Vérifier l'intégrité des wallets d'un client
     */
    public function checkClientWalletIntegrity(User $client)
    {
        $wallet = $client->wallet;
        
        if (!$wallet) {
            return [
                'status' => 'missing',
                'message' => 'Wallet manquant',
                'action_needed' => 'create_wallet'
            ];
        }

        $validation = $this->validateWallet($wallet);
        
        if (!$validation['is_valid']) {
            return [
                'status' => 'invalid',
                'message' => 'Wallet invalide: ' . implode(', ', $validation['errors']),
                'errors' => $validation['errors'],
                'action_needed' => 'fix_wallet'
            ];
        }

        return [
            'status' => 'valid',
            'message' => 'Wallet valide',
            'balance' => $wallet->balance,
            'action_needed' => 'none'
        ];
    }
}