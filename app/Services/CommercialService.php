<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CommercialService
{
    // ==================== GESTION CLIENTS ====================

    public function createClientAccount(array $data, User $commercial)
    {
        DB::beginTransaction();
        
        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'address' => $data['address'],
                'role' => 'CLIENT',
                'account_status' => 'PENDING',
                'created_by' => $commercial->id,
            ]);

            // Créer le profil client avec les bons noms de colonnes
            ClientProfile::create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'] ?? null,
                'fiscal_number' => $data['fiscal_number'] ?? null,
                'business_sector' => $data['business_sector'] ?? null,
                'identity_document' => $data['identity_document'] ?? null,
                'offer_delivery_price' => $data['delivery_price'],
                'offer_return_price' => $data['return_price'],
            ]);

            // Log simple
            Log::info('Client créé avec succès', [
                'client_id' => $user->id,
                'client_name' => $user->name,
                'created_by' => $commercial->name
            ]);

            DB::commit();
            
            return $user->load(['clientProfile', 'wallet']);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur création client:', [
                'error' => $e->getMessage(),
                'commercial_id' => $commercial->id,
                'line' => $e->getLine()
            ]);
            
            throw new \Exception("Erreur lors de la création du compte client: " . $e->getMessage());
        }
    }

    public function validateClientAccount(User $client, User $commercial, array $options = [])
    {
        try {
            $client->update([
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $commercial->id,
            ]);

            Log::info('Client validé', [
                'client_id' => $client->id,
                'validated_by' => $commercial->name
            ]);

            return $client;
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la validation: " . $e->getMessage());
        }
    }

    // ==================== GESTION WALLET ====================

    public function addFundsToWallet(User $user, $amount, $description, User $commercial)
    {
        try {
            $wallet = $user->wallet;
            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance + $amount;
            
            $wallet->update(['balance' => $newBalance]);

            Log::info('Fonds ajoutés', [
                'user_id' => $user->id,
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'added_by' => $commercial->name
            ]);

            return [
                'wallet_balance' => $newBalance,
                'transaction_id' => null
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'ajout de fonds: " . $e->getMessage());
        }
    }

    public function deductFundsFromWallet(User $user, $amount, $description, User $commercial)
    {
        try {
            $wallet = $user->wallet;
            $oldBalance = $wallet->balance;
            
            if ($oldBalance < $amount) {
                throw new \Exception("Solde insuffisant");
            }
            
            $newBalance = $oldBalance - $amount;
            $wallet->update(['balance' => $newBalance]);

            Log::info('Fonds déduits', [
                'user_id' => $user->id,
                'amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'deducted_by' => $commercial->name
            ]);

            return [
                'wallet_balance' => $newBalance,
                'transaction_id' => null
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la déduction: " . $e->getMessage());
        }
    }

    // ==================== STATISTIQUES ====================

    public function getDashboardStats()
    {
        try {
            return [
                'total_clients' => User::where('role', 'CLIENT')->count(),
                'active_clients' => User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->count(),
                'pending_clients' => User::where('role', 'CLIENT')->where('account_status', 'PENDING')->count(),
                'total_packages' => Package::count(),
                'packages_today' => Package::whereDate('created_at', today())->count(),
                'pending_complaints' => Complaint::where('status', 'PENDING')->count(),
                'urgent_complaints' => Complaint::where('status', 'PENDING')->where('priority', 'URGENT')->count(),
                'pending_withdrawals' => WithdrawalRequest::where('status', 'PENDING')->count(),
                'high_balance_deliverers' => 0, // Simplifié pour éviter les erreurs
            ];
        } catch (\Exception $e) {
            Log::error('Erreur stats dashboard:', ['error' => $e->getMessage()]);
            return [
                'total_clients' => 0,
                'active_clients' => 0,
                'pending_clients' => 0,
                'total_packages' => 0,
                'packages_today' => 0,
                'pending_complaints' => 0,
                'urgent_complaints' => 0,
                'pending_withdrawals' => 0,
                'high_balance_deliverers' => 0,
            ];
        }
    }

    public function getComplaintsSummary()
    {
        try {
            return [
                'total' => Complaint::count(),
                'pending' => Complaint::where('status', 'PENDING')->count(),
                'urgent' => Complaint::where('status', 'PENDING')->where('priority', 'URGENT')->count(),
                'in_progress' => Complaint::where('status', 'IN_PROGRESS')->count(),
                'resolved_today' => Complaint::where('status', 'RESOLVED')->whereDate('resolved_at', today())->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur stats réclamations:', ['error' => $e->getMessage()]);
            return [
                'total' => 0,
                'pending' => 0,
                'urgent' => 0,
                'in_progress' => 0,
                'resolved_today' => 0,
            ];
        }
    }

    public function getPendingComplaints(User $commercial)
    {
        try {
            return Complaint::with(['package', 'client'])
                           ->where('status', 'PENDING')
                           ->orderBy('created_at', 'desc')
                           ->get();
        } catch (\Exception $e) {
            Log::error('Erreur réclamations en attente:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function getPendingWithdrawals()
    {
        try {
            return WithdrawalRequest::with(['client'])
                                   ->where('status', 'PENDING')
                                   ->orderBy('created_at', 'asc')
                                   ->get();
        } catch (\Exception $e) {
            Log::error('Erreur retraits en attente:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function getDeliverersWithWallets()
    {
        try {
            return User::with(['wallet'])
                      ->where('role', 'DELIVERER')
                      ->where('account_status', 'ACTIVE')
                      ->orderBy('name')
                      ->get();
        } catch (\Exception $e) {
            Log::error('Erreur livreurs avec wallets:', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    public function getRecentActivity()
    {
        try {
            return [
                'complaints' => Complaint::with(['package', 'client'])
                                       ->where('created_at', '>=', now()->subDays(7))
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get(),
                'cod_modifications' => collect([]), // Simplifié
            ];
        } catch (\Exception $e) {
            Log::error('Erreur activité récente:', ['error' => $e->getMessage()]);
            return [
                'complaints' => collect([]),
                'cod_modifications' => collect([]),
            ];
        }
    }
}