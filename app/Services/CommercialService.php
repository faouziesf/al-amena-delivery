<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\ClientProfile;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\CodModification;
use App\Models\Delegation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CommercialService
{
    protected $financialService;
    protected $actionLogService;

    public function __construct(FinancialTransactionService $financialService, ActionLogService $actionLogService)
    {
        $this->financialService = $financialService;
        $this->actionLogService = $actionLogService;
    }

    // ==================== GESTION CLIENTS ====================
    
    public function createClientAccount(array $data, User $createdBy)
    {
        return DB::transaction(function () use ($data, $createdBy) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'CLIENT',
                'phone' => $data['phone'],
                'address' => $data['address'],
                'account_status' => 'PENDING', // En attente de validation
                'created_by' => $createdBy->id,
            ]);

            // Créer le profil client
            ClientProfile::create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'] ?? null,
                'fiscal_number' => $data['fiscal_number'] ?? null,
                'business_sector' => $data['business_sector'] ?? null,
                'identity_document' => $data['identity_document'] ?? null,
                'offer_delivery_price' => $data['delivery_price'],
                'offer_return_price' => $data['return_price'],
            ]);

            // Log de création
            $this->actionLogService->log(
                'CLIENT_ACCOUNT_CREATED',
                'User',
                $user->id,
                null,
                $user->toArray(),
                [
                    'created_by' => $createdBy->name,
                    'shop_name' => $data['shop_name'] ?? null,
                    'fiscal_number' => $data['fiscal_number'] ?? null
                ]
            );

            Log::info('Nouveau compte client créé par commercial', [
                'client_id' => $user->id,
                'client_email' => $user->email,
                'created_by' => $createdBy->id,
                'commercial_name' => $createdBy->name
            ]);

            return $user->load('clientProfile');
        });
    }

    public function validateClientAccount(User $client, User $validator, array $validationData = [])
    {
        $client->update([
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'verified_by' => $validator->id,
        ]);

        // Log de validation
        $this->actionLogService->log(
            'CLIENT_ACCOUNT_VALIDATED',
            'User',
            $client->id,
            'PENDING',
            'ACTIVE',
            array_merge([
                'validated_by' => $validator->name,
                'validation_date' => now()->toISOString()
            ], $validationData)
        );

        return $client;
    }

    // ==================== GESTION WALLET ====================

    public function addFundsToWallet(User $client, $amount, $description = 'Ajout de fonds par commercial', User $addedBy = null)
    {
        return $this->financialService->processTransaction([
            'user_id' => $client->id,
            'type' => 'WALLET_RECHARGE',
            'amount' => $amount,
            'description' => $description,
            'metadata' => [
                'added_by_commercial' => $addedBy?->id,
                'commercial_name' => $addedBy?->name,
                'manual_addition' => true
            ]
        ]);
    }

    public function deductFundsFromWallet(User $client, $amount, $description = 'Déduction par commercial', User $deductedBy = null)
    {
        return $this->financialService->processTransaction([
            'user_id' => $client->id,
            'type' => 'COMMERCIAL_DEDUCTION',
            'amount' => -$amount,
            'description' => $description,
            'metadata' => [
                'deducted_by_commercial' => $deductedBy?->id,
                'commercial_name' => $deductedBy?->name,
                'manual_deduction' => true
            ]
        ]);
    }

    // ==================== GESTION COD ====================

    public function modifyCodAmount(Package $package, $newAmount, $reason, User $commercial, $complaintId = null, $emergency = false)
    {
        if (!$package->canBeModified()) {
            throw new \Exception('Ce colis ne peut plus être modifié');
        }

        $modification = CodModification::createModification(
            $package->id,
            $newAmount,
            $commercial->id,
            $reason,
            $complaintId,
            null,
            $emergency
        );

        Log::info('COD modifié par commercial', [
            'package_id' => $package->id,
            'package_code' => $package->package_code,
            'old_amount' => $modification->old_amount,
            'new_amount' => $modification->new_amount,
            'commercial_id' => $commercial->id,
            'reason' => $reason,
            'emergency' => $emergency
        ]);

        return $modification;
    }

    // ==================== GESTION RÉCLAMATIONS ====================

    public function getPendingComplaints(User $commercial = null)
    {
        $query = Complaint::with(['package', 'client'])
            ->pending()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        if ($commercial) {
            $query->where(function ($q) use ($commercial) {
                $q->where('assigned_commercial_id', $commercial->id)
                  ->orWhereNull('assigned_commercial_id');
            });
        }

        return $query->get();
    }

    public function getComplaintsSummary()
    {
        return [
            'total_pending' => Complaint::pending()->count(),
            'urgent' => Complaint::pending()->where('priority', 'URGENT')->count(),
            'cod_changes' => Complaint::pending()->where('type', 'CHANGE_COD')->count(),
            'delivery_delays' => Complaint::pending()->where('type', 'DELIVERY_DELAY')->count(),
            'today_resolved' => Complaint::where('status', 'RESOLVED')->whereDate('resolved_at', today())->count(),
        ];
    }

    public function processComplaint(Complaint $complaint, $action, $data, User $commercial)
    {
        return DB::transaction(function () use ($complaint, $action, $data, $commercial) {
            switch ($action) {
                case 'resolve_with_cod_change':
                    // Modifier le COD et résoudre la réclamation
                    $this->modifyCodAmount(
                        $complaint->package,
                        $data['new_cod_amount'],
                        'Suite à réclamation client',
                        $commercial,
                        $complaint->id
                    );
                    
                    $complaint->resolve(
                        'COD modifié de ' . $complaint->package->cod_amount . ' à ' . $data['new_cod_amount'] . ' DT',
                        ['cod_changed' => true, 'new_amount' => $data['new_cod_amount']]
                    );
                    break;

                case 'resolve_with_reschedule':
                    // Programmer une nouvelle tentative
                    $complaint->package->update(['delivery_attempts' => 0]);
                    $complaint->resolve(
                        'Reprogrammé pour livraison - ' . ($data['notes'] ?? ''),
                        ['rescheduled' => true, 'reschedule_date' => $data['reschedule_date'] ?? null]
                    );
                    break;

                case 'resolve_with_return':
                    // Marquer pour retour
                    $complaint->package->updateStatus('RETURNED', $commercial, 'Suite à réclamation client');
                    $complaint->resolve(
                        'Colis marqué pour retour - ' . ($data['notes'] ?? ''),
                        ['returned' => true]
                    );
                    break;

                case 'reject':
                    $complaint->reject($data['rejection_reason']);
                    break;

                default:
                    $complaint->resolve($data['resolution_notes'] ?? 'Réclamation traitée');
            }

            // Assigner au commercial si pas déjà fait
            if (!$complaint->assigned_commercial_id) {
                $complaint->assignTo($commercial);
            }

            return $complaint;
        });
    }

    // ==================== GESTION RETRAITS ====================

    public function getPendingWithdrawals()
    {
        return WithdrawalRequest::with(['client'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function processWithdrawalRequest(WithdrawalRequest $request, $action, $data, User $commercial)
    {
        return DB::transaction(function () use ($request, $action, $data, $commercial) {
            switch ($action) {
                case 'approve_bank_transfer':
                    $request->approve($commercial, $data['notes'] ?? 'Virement bancaire approuvé');
                    // Déduire du wallet client
                    $this->deductFundsFromWallet(
                        $request->client,
                        $request->amount,
                        'Retrait approuvé - Virement bancaire',
                        $commercial
                    );
                    break;

                case 'approve_cash_delivery':
                    $request->approve($commercial, $data['notes'] ?? 'Livraison espèces approuvée');
                    // Le wallet sera débité lors de la livraison effective
                    break;

                case 'reject':
                    $request->reject($commercial, $data['rejection_reason']);
                    break;
            }

            return $request;
        });
    }

    public function assignWithdrawalToDeliverer(WithdrawalRequest $request, User $deliverer)
    {
        $request->assignToDeliverer($deliverer);

        // Déduire du wallet client maintenant
        $this->deductFundsFromWallet(
            $request->client,
            $request->amount,
            'Retrait assigné au livreur - ' . $deliverer->name
        );

        return $request;
    }

    // ==================== GESTION LIVREURS ====================

    public function getDeliverersWithWallets()
    {
        return User::with('wallet')
            ->where('role', 'DELIVERER')
            ->where('account_status', 'ACTIVE')
            ->get()
            ->map(function ($deliverer) {
                return [
                    'id' => $deliverer->id,
                    'name' => $deliverer->name,
                    'phone' => $deliverer->phone,
                    'wallet_balance' => $deliverer->wallet->balance ?? 0,
                    'last_transaction' => $deliverer->wallet->last_transaction_at ?? null,
                    'needs_emptying' => ($deliverer->wallet->balance ?? 0) > 100, // Seuil configurable
                ];
            });
    }

    public function emptyDelivererWallet(User $deliverer, User $commercial, $physicalAmount = null)
    {
        return DB::transaction(function () use ($deliverer, $commercial, $physicalAmount) {
            $wallet = $deliverer->wallet;
            $walletBalance = $wallet->balance;
            
            $physicalAmount = $physicalAmount ?? $walletBalance;
            $discrepancy = $walletBalance - $physicalAmount;

            // Créer l'enregistrement de vidage
            $emptying = \App\Models\DelivererWalletEmptying::create([
                'deliverer_id' => $deliverer->id,
                'commercial_id' => $commercial->id,
                'wallet_amount' => $walletBalance,
                'physical_amount' => $physicalAmount,
                'discrepancy_amount' => $discrepancy,
                'emptying_date' => now(),
                'notes' => $discrepancy != 0 ? 'Différence détectée: ' . $discrepancy . ' DT' : null,
                'receipt_generated' => true,
            ]);

            // Vider le wallet
            $this->financialService->processTransaction([
                'user_id' => $deliverer->id,
                'type' => 'COMMERCIAL_EMPTYING',
                'amount' => -$walletBalance,
                'description' => 'Vidage wallet par commercial',
                'metadata' => [
                    'commercial_id' => $commercial->id,
                    'physical_amount' => $physicalAmount,
                    'discrepancy' => $discrepancy,
                    'emptying_id' => $emptying->id
                ]
            ]);

            Log::info('Wallet livreur vidé par commercial', [
                'deliverer_id' => $deliverer->id,
                'commercial_id' => $commercial->id,
                'wallet_amount' => $walletBalance,
                'physical_amount' => $physicalAmount,
                'discrepancy' => $discrepancy
            ]);

            return $emptying;
        });
    }

    // ==================== STATISTIQUES & RAPPORTS ====================

    public function getDashboardStats()
    {
        return [
            'pending_complaints' => Complaint::pending()->count(),
            'urgent_complaints' => Complaint::pending()->where('priority', 'URGENT')->count(),
            'pending_withdrawals' => WithdrawalRequest::pending()->count(),
            'packages_today' => Package::whereDate('created_at', today())->count(),
            'packages_in_progress' => Package::inProgress()->count(),
            'high_balance_deliverers' => User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                ->where('users.role', 'DELIVERER')
                ->where('user_wallets.balance', '>', 100)
                ->count(),
            'cod_modifications_today' => CodModification::whereDate('created_at', today())->count(),
        ];
    }

    public function getRecentActivity($days = 7)
    {
        return [
            'complaints' => Complaint::with(['package', 'client'])
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'withdrawals' => WithdrawalRequest::with(['client'])
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'cod_modifications' => CodModification::with(['package', 'modifiedByCommercial'])
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
    }
}