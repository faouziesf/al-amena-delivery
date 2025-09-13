<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Delegation;
use App\Models\ClientProfile;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\CodModification;
use App\Models\Notification;
use App\Models\DelivererWalletEmptying;
use App\Services\FinancialTransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $financialService = app(FinancialTransactionService::class);
        
        $this->command->info('🚀 Création des données de test Al-Amena Delivery...');

        // ==================== CRÉER LE SUPERVISEUR ====================
        $this->command->info('👑 Création du superviseur...');
        $supervisor = User::create([
            'name' => 'Mohamed Superviseur',
            'email' => 'supervisor@alamena.tn',
            'password' => Hash::make('password'),
            'role' => 'SUPERVISOR',
            'phone' => '+216 20 100 001',
            'address' => 'Avenue Habib Bourguiba, Tunis, Tunisie',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
        ]);

        // ==================== CRÉER LES DÉLÉGATIONS ====================
        $this->command->info('📍 Création des délégations...');
        $delegationsData = [
            ['name' => 'Tunis Centre', 'zone' => 'Tunis', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Tunis Nord (Ariana)', 'zone' => 'Tunis', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Tunis Sud (Ben Arous)', 'zone' => 'Tunis', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Sfax Centre', 'zone' => 'Sfax', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Sfax Sud', 'zone' => 'Sfax', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Sousse', 'zone' => 'Sousse', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Monastir', 'zone' => 'Monastir', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Gabès', 'zone' => 'Gabès', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Kairouan', 'zone' => 'Kairouan', 'active' => true, 'created_by' => $supervisor->id],
            ['name' => 'Bizerte', 'zone' => 'Bizerte', 'active' => true, 'created_by' => $supervisor->id],
        ];
        $delegations = [];
        foreach ($delegationsData as $data) {
            $delegations[] = Delegation::create($data);
        }

        // ==================== CRÉER LES COMMERCIAUX ====================
        $this->command->info('💼 Création des commerciaux...');
        $commercials = [];
        for ($i = 1; $i <= 5; $i++) {
            $commercial = User::create([
                'name' => "Commercial $i",
                'email' => "commercial$i@alamena.tn",
                'password' => Hash::make('password'),
                'role' => 'COMMERCIAL',
                'phone' => "+216 20 100 00$i",
                'address' => "Adresse Commercial $i, Tunisie",
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $supervisor->id,
                'created_by' => $supervisor->id,
            ]);
            $commercials[] = $commercial;
        }

        // ==================== CRÉER LES CLIENTS ====================
        $this->command->info('👥 Création des clients...');
        $clients = [];
        for ($i = 1; $i <= 10; $i++) {
            $client = User::create([
                'name' => "Client $i",
                'email' => "client$i@alamena.tn",
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'phone' => "+216 50 100 00$i",
                'address' => "Adresse Client $i, Tunisie",
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $commercials[array_rand($commercials)]->id,
                'created_by' => $commercials[array_rand($commercials)]->id,
            ]);

            ClientProfile::create([
                'user_id' => $client->id,
                'shop_name' => "Boutique Client $i",
                'fiscal_number' => "FIS00$i",
                'business_sector' => 'E-commerce',
                'identity_document' => "ID_DOC_00$i",
                'offer_delivery_price' => 5.000 + $i,
                'offer_return_price' => 3.000 + $i,
            ]);

            $clients[] = $client;
        }

        // ==================== CRÉER LES LIVREURS ====================
        $this->command->info('🚚 Création des livreurs...');
        $deliverers = [];
        for ($i = 1; $i <= 15; $i++) {
            $deliverer = User::create([
                'name' => "Livreur $i",
                'email' => "deliverer$i@alamena.tn",
                'password' => Hash::make('password'),
                'role' => 'DELIVERER',
                'phone' => "+216 30 100 00$i",
                'address' => "Adresse Livreur $i, Tunisie",
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $supervisor->id,
                'created_by' => $supervisor->id,
            ]);
            $deliverers[] = $deliverer;
        }

        // ==================== CRÉER LES WALLETS POUR UTILISATEURS EXISTANTS ====================
        $this->command->info('💼 Création des wallets pour utilisateurs existants...');
        DB::statement("
            INSERT INTO user_wallets (user_id, balance, pending_amount, frozen_amount, created_at, updated_at)
            SELECT id, 0.000, 0.000, 0.000, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM users 
            WHERE role IN ('CLIENT', 'DELIVERER') 
            AND id NOT IN (SELECT user_id FROM user_wallets)
        ");

        // ==================== MISE À JOUR DES SOLDES WALLETS (SIMULATION) ====================
        $this->command->info('🔄 Mise à jour des soldes wallets...');
        foreach ($clients as $client) {
            $balance = rand(50, 500) + (rand(0, 99) / 100);
            DB::table('user_wallets')
                ->where('user_id', $client->id)
                'update' => ['balance' => $balance];
        }
        foreach ($deliverers as $deliverer) {
            $balance = rand(100, 800) + (rand(0, 99) / 100);
            DB::table('user_wallets')
                ->where('user_id', $deliverer->id)
                ->update(['balance' => $balance]);
        }

        // ==================== CRÉER LES COLIS ====================
        $this->command->info('📦 Création des colis...');
        $packages = [];
        $statuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'DELIVERED', 'PAID', 'REFUSED', 'RETURNED', 'UNAVAILABLE', 'VERIFIED', 'CANCELLED'];
        for ($i = 1; $i <= 50; $i++) {
            $sender = $clients[array_rand($clients)];
            $delegationFrom = $delegations[array_rand($delegations)];
            $delegationTo = $delegations[array_rand($delegations)];
            $status = $statuses[array_rand($statuses)];
            $assignedDeliverer = $status !== 'CREATED' ? $deliverers[array_rand($deliverers)] : null;

            $package = Package::create([
                'package_code' => 'PKG-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'sender_id' => $sender->id,
                'sender_data' => json_encode([
                    'name' => $sender->name,
                    'phone' => $sender->phone,
                    'address' => $sender->address,
                ]),
                'delegation_from' => $delegationFrom->id,
                'recipient_data' => json_encode([
                    'name' => "Destinataire $i",
                    'phone' => "+216 70 100 00$i",
                    'address' => "Adresse Destinataire $i, Tunisie",
                ]),
                'delegation_to' => $delegationTo->id,
                'content_description' => "Contenu colis $i",
                'notes' => "Notes pour colis $i",
                'cod_amount' => rand(10, 500) + (rand(0, 99) / 100),
                'delivery_fee' => 5.000 + rand(0, 5),
                'return_fee' => 3.000 + rand(0, 3),
                'status' => $status,
                'assigned_deliverer_id' => $assignedDeliverer ? $assignedDeliverer->id : null,
                'assigned_at' => $assignedDeliverer ? now()->subDays(rand(1, 10)) : null,
                'delivery_attempts' => rand(0, 3),
                'cod_modifiable_by_commercial' => (bool) rand(0, 1),
                'amount_in_escrow' => rand(0, 100) + (rand(0, 99) / 100),
            ]);

            // Simuler une transaction financière pour le colis
            if ($status === 'CREATED') {
                $transactionData = [
                    'user_id' => $sender->id,
                    'type' => 'PACKAGE_CREATION_DEBIT',
                    'amount' => -$package->delivery_fee, // Debit, so negative amount
                    'description' => "Débit pour création colis {$package->package_code}",
                    'package_id' => $package->id,
                    'metadata' => json_encode(['package_code' => $package->package_code])
                ];
                $financialService->processTransaction($transactionData);
            }

            $packages[] = $package;
        }

        // ==================== CRÉER LES RÉCLAMATIONS ====================
        $this->command->info('⚠️ Création des réclamations...');
        $complaintTypes = ['CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT', 'CUSTOM'];
        $complaintStatuses = ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'REJECTED'];
        $priorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        for ($i = 1; $i <= 20; $i++) {
            $package = $packages[array_rand($packages)];
            $client = $package->sender;
            $type = $complaintTypes[array_rand($complaintTypes)];
            $status = $complaintStatuses[array_rand($complaintStatuses)];
            $priority = $priorities[array_rand($priorities)];
            $assignedCommercial = $status !== 'PENDING' ? $commercials[array_rand($commercials)] : null;

            Complaint::create([
                'complaint_code' => 'CMP-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'package_id' => $package->id,
                'client_id' => $client->id,
                'type' => $type,
                'description' => $this->getComplaintDescription($type),
                'additional_data' => json_encode(['detail' => "Détail supplémentaire pour $type"]),
                'status' => $status,
                'priority' => $priority,
                'assigned_commercial_id' => $assignedCommercial ? $assignedCommercial->id : null,
                'resolution_notes' => $status === 'RESOLVED' ? 'Résolu avec succès.' : null,
                'resolution_data' => $status === 'RESOLVED' ? json_encode(['action' => 'Modification effectuée']) : null,
                'resolved_at' => $status === 'RESOLVED' ? now()->subDays(rand(1, 5)) : null,
            ]);
        }

        // ==================== CRÉER LES DEMANDES DE RETRAIT ====================
        $this->command->info('💸 Création des demandes de retrait...');
        $withdrawalMethods = ['BANK_TRANSFER', 'CASH_DELIVERY'];
        $withdrawalStatuses = ['PENDING', 'APPROVED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'];
        for ($i = 1; $i <= 15; $i++) {
            $client = $clients[array_rand($clients)];
            $method = $withdrawalMethods[array_rand($withdrawalMethods)];
            $status = $withdrawalStatuses[array_rand($withdrawalStatuses)];
            $processedBy = $status !== 'PENDING' ? $commercials[array_rand($commercials)] : null;
            $assignedDeliverer = ($method === 'CASH_DELIVERY' && $status === 'IN_PROGRESS') ? $deliverers[array_rand($deliverers)] : null;

            WithdrawalRequest::create([
                'request_code' => 'WDR-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'amount' => rand(50, 500) + (rand(0, 99) / 100),
                'method' => $method,
                'bank_details' => $method === 'BANK_TRANSFER' ? json_encode(['iban' => 'TN' . rand(1000000000000000, 9999999999999999)]) : null,
                'status' => $status,
                'processed_by_commercial_id' => $processedBy ? $processedBy->id : null,
                'assigned_deliverer_id' => $assignedDeliverer ? $assignedDeliverer->id : null,
                'delivery_receipt_code' => $method === 'CASH_DELIVERY' && $status === 'COMPLETED' ? 'REC-' . rand(1000, 9999) : null,
                'delivered_at' => $status === 'COMPLETED' ? now()->subDays(rand(1, 7)) : null,
                'delivery_proof' => $status === 'COMPLETED' ? json_encode(['signature' => 'signed']) : null,
                'processing_notes' => $status !== 'PENDING' ? 'Notes de traitement.' : null,
                'rejection_reason' => $status === 'REJECTED' ? 'Raison de rejet.' : null,
                'processed_at' => $status !== 'PENDING' ? now()->subDays(rand(1, 10)) : null,
            ]);
        }

        // ==================== CRÉER LES MODIFICATIONS COD ====================
        $this->command->info('🔄 Création des modifications COD...');
        for ($i = 1; $i <= 10; $i++) {
            $package = $packages[array_rand($packages)];
            $oldAmount = $package->cod_amount;
            $newAmount = $oldAmount + rand(-50, 50) + (rand(-99, 99) / 100);

            CodModification::create([
                'package_id' => $package->id,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'modified_by_commercial_id' => $commercials[array_rand($commercials)]->id,
                'reason' => 'Demande client via réclamation',
                'client_complaint_id' => rand(1, 20), // Assumer IDs de plaintes
                'modification_notes' => 'Modification approuvée.',
                'context_data' => json_encode(['before' => $oldAmount, 'after' => $newAmount]),
                'ip_address' => '192.168.0.' . rand(1, 255),
                'emergency_modification' => (bool) rand(0, 1),
            ]);

            // Mettre à jour le montant COD du colis
            $package->update(['cod_amount' => $newAmount]);
        }

        // ==================== CRÉER LES NOTIFICATIONS ====================
        $this->command->info('🔔 Création des notifications...');
        $notificationTypes = ['COMPLAINT_NEW', 'COMPLAINT_URGENT', 'WITHDRAWAL_REQUEST', 'WALLET_HIGH_BALANCE', 'PACKAGE_BLOCKED'];
        $priorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        for ($i = 1; $i <= 30; $i++) {
            $user = rand(0, 1) ? $commercials[array_rand($commercials)] : $supervisor;
            $type = $notificationTypes[array_rand($notificationTypes)];
            $priority = $priorities[array_rand($priorities)];

            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $this->getNotificationTitle($type),
                'message' => $this->getNotificationMessage($type),
                'data' => json_encode(['related_id' => rand(1, 50)]),
                'priority' => $priority,
                'read' => (bool) rand(0, 1),
                'read_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                'expires_at' => now()->addDays(rand(1, 7)),
                'action_url' => '/dashboard/notifications/' . rand(1, 50),
                'related_type' => 'Package',
                'related_id' => rand(1, 50),
            ]);
        }

        // ==================== CRÉER LES VIDAGES WALLET LIVREURS ====================
        $this->command->info('🏦 Création des vidages wallet livreurs...');
        for ($i = 1; $i <= 10; $i++) {
            $deliverer = $deliverers[array_rand($deliverers)];
            $commercial = $commercials[array_rand($commercials)];
            $walletAmount = rand(100, 1000) + (rand(0, 99) / 100);
            $physicalAmount = $walletAmount - rand(0, 50);

            DelivererWalletEmptying::create([
                'deliverer_id' => $deliverer->id,
                'commercial_id' => $commercial->id,
                'wallet_amount' => $walletAmount,
                'physical_amount' => $physicalAmount,
                'discrepancy_amount' => $walletAmount - $physicalAmount,
                'emptying_date' => now()->subDays(rand(1, 30)),
                'notes' => 'Vidage régulier.',
                'receipt_generated' => true,
                'receipt_path' => '/receipts/emptying_' . $i . '.pdf',
                'emptying_details' => json_encode(['sources' => 'COD colis']),
                'deliverer_acknowledged' => (bool) rand(0, 1),
                'deliverer_acknowledged_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
            ]);
        }

        // ==================== STATISTIQUES FINALES ====================
        $this->command->info('');
        $this->command->info('📊 Statistiques des données créées :');
        $this->command->info('   👑 1 superviseur');
        $this->command->info('   💼 ' . count($commercials) . ' commerciaux');
        $this->command->info('   👥 ' . count($clients) . ' clients');
        $this->command->info('   🚚 ' . count($deliverers) . ' livreurs');
        $this->command->info('   📍 ' . count($delegations) . ' délégations');
        $this->command->info('   📦 ' . Package::count() . ' colis');
        $this->command->info('   ⚠️ ' . Complaint::count() . ' réclamations');
        $this->command->info('   💸 ' . WithdrawalRequest::count() . ' demandes de retrait');
        $this->command->info('   🔄 ' . CodModification::count() . ' modifications COD');
        $this->command->info('   🔔 ' . Notification::count() . ' notifications');
        $this->command->info('   🏦 ' . DelivererWalletEmptying::count() . ' vidages wallet');
        $this->command->info('');
        $this->command->info('🎯 READY TO GO! Le système commercial complet est prêt à tester !');
    }

    private function getComplaintDescription($type)
    {
        $descriptions = [
            'CHANGE_COD' => 'Le client souhaite modifier le montant COD de sa commande.',
            'DELIVERY_DELAY' => 'Retard dans la livraison, client mécontent du délai.',
            'REQUEST_RETURN' => 'Le client demande le retour de son colis.',
            'RESCHEDULE_TODAY' => 'Demande de reprogrammation de livraison pour aujourd\'hui.',
            'FOURTH_ATTEMPT' => 'Demande de 4ème tentative après 3 échecs de livraison.',
            'RETURN_DELAY' => 'Retard dans le retour du colis.',
            'CUSTOM' => 'Réclamation personnalisée.',
        ];
        
        return $descriptions[$type] ?? 'Réclamation générique.';
    }

    private function getNotificationTitle($type)
    {
        $titles = [
            'COMPLAINT_NEW' => 'Nouvelle réclamation',
            'COMPLAINT_URGENT' => 'Réclamation URGENTE',
            'WITHDRAWAL_REQUEST' => 'Demande de retrait',
            'WALLET_HIGH_BALANCE' => 'Wallet livreur élevé',
            'PACKAGE_BLOCKED' => 'Colis bloqué',
        ];
        
        return $titles[$type] ?? 'Notification';
    }

    private function getNotificationMessage($type)
    {
        $messages = [
            'COMPLAINT_NEW' => 'Une nouvelle réclamation nécessite votre attention.',
            'COMPLAINT_URGENT' => 'Réclamation marquée comme URGENTE à traiter immédiatement.',
            'WITHDRAWAL_REQUEST' => 'Nouvelle demande de retrait à approuver.',
            'WALLET_HIGH_BALANCE' => 'Un livreur a un solde wallet élevé à vider.',
            'PACKAGE_BLOCKED' => 'Un colis est bloqué depuis plusieurs jours.',
        ];
        
        return $messages[$type] ?? 'Message de notification.';
    }
}