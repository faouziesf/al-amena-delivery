<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Créer les utilisateurs de test
        echo "🔄 Création des utilisateurs...\n";
        
        // Créer un superviseur
        $supervisorId = DB::table('users')->insertGetId([
            'name' => 'Admin Supervisor',
            'email' => 'supervisor@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'SUPERVISOR',
            'phone' => '+216 20 123 456',
            'address' => 'Tunis, Tunisie',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer des commerciaux
        $commercials = [];
        for ($i = 1; $i <= 3; $i++) {
            $commercialId = DB::table('users')->insertGetId([
                'name' => "Commercial $i",
                'email' => "commercial$i@test.com",
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'COMMERCIAL',
                'phone' => "+216 21 123 45$i",
                'address' => "Tunis, Bureau $i",
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $supervisorId,
                'created_by' => $supervisorId,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
            $commercials[] = $commercialId;
        }

        // Créer des livreurs
        $deliverers = [];
        for ($i = 1; $i <= 5; $i++) {
            $delivererId = DB::table('users')->insertGetId([
                'name' => "Livreur $i",
                'email' => "deliverer$i@test.com",
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'DELIVERER',
                'phone' => "+216 22 123 45$i",
                'address' => "Zone $i, Tunis",
                'account_status' => rand(0, 1) ? 'ACTIVE' : 'PENDING',
                'verified_at' => rand(0, 1) ? now() : null,
                'verified_by' => $commercials[array_rand($commercials)],
                'created_by' => $commercials[array_rand($commercials)],
                'created_at' => now()->subDays(rand(1, 45)),
                'updated_at' => now(),
            ]);
            $deliverers[] = $delivererId;
        }

        // Créer des clients
        $clients = [];
        for ($i = 1; $i <= 10; $i++) {
            $clientId = DB::table('users')->insertGetId([
                'name' => "Client $i",
                'email' => "client$i@test.com",
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'CLIENT',
                'phone' => "+216 23 123 45$i",
                'address' => "Adresse Client $i, Tunis",
                'account_status' => rand(0, 1) ? 'ACTIVE' : 'PENDING',
                'verified_at' => rand(0, 1) ? now() : null,
                'verified_by' => rand(0, 1) ? $commercials[array_rand($commercials)] : null,
                'created_by' => $commercials[array_rand($commercials)],
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
            $clients[] = $clientId;
        }

        // 2. Créer les délégations
        echo "🔄 Création des délégations...\n";
        
        $tunisianDelegations = [
            ['name' => 'Tunis', 'zone' => 'Grand Tunis'],
            ['name' => 'La Marsa', 'zone' => 'Grand Tunis'],
            ['name' => 'Carthage', 'zone' => 'Grand Tunis'],
            ['name' => 'Sidi Bou Said', 'zone' => 'Grand Tunis'],
            ['name' => 'Ariana', 'zone' => 'Grand Tunis'],
            ['name' => 'Ben Arous', 'zone' => 'Grand Tunis'],
            ['name' => 'Sfax', 'zone' => 'Centre'],
            ['name' => 'Sousse', 'zone' => 'Centre'],
            ['name' => 'Monastir', 'zone' => 'Centre'],
            ['name' => 'Mahdia', 'zone' => 'Centre'],
            ['name' => 'Bizerte', 'zone' => 'Nord'],
            ['name' => 'Nabeul', 'zone' => 'Nord'],
            ['name' => 'Zaghouan', 'zone' => 'Nord'],
            ['name' => 'Gabès', 'zone' => 'Sud'],
            ['name' => 'Médenine', 'zone' => 'Sud'],
            ['name' => 'Tataouine', 'zone' => 'Sud'],
        ];

        $delegations = [];
        foreach ($tunisianDelegations as $delegation) {
            $delegationId = DB::table('delegations')->insertGetId([
                'name' => $delegation['name'],
                'zone' => $delegation['zone'],
                'active' => true,
                'created_by' => $supervisorId,
                'created_at' => now()->subDays(rand(30, 90)),
                'updated_at' => now(),
            ]);
            $delegations[] = $delegationId;
        }

        // 3. Créer les profils clients
        echo "🔄 Création des profils clients...\n";
        
        $businessSectors = ['E-commerce', 'Textile', 'Électronique', 'Cosmétiques', 'Alimentaire', 'Librairie', 'Pharmacie'];
        
        foreach ($clients as $clientId) {
            DB::table('client_profiles')->insert([
                'user_id' => $clientId,
                'shop_name' => "Boutique Client " . array_search($clientId, $clients) + 1,
                'fiscal_number' => '1' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT) . 'R' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                'business_sector' => $businessSectors[array_rand($businessSectors)],
                'identity_document' => 'CIN_' . rand(10000000, 99999999) . '.pdf',
                'offer_delivery_price' => rand(3000, 8000) / 1000, // 3.000 à 8.000 DT
                'offer_return_price' => rand(2000, 6000) / 1000, // 2.000 à 6.000 DT
                'internal_notes' => rand(0, 1) ? 'Client fiable, paiements réguliers' : null,
                'created_at' => now()->subDays(rand(1, 45)),
                'updated_at' => now(),
            ]);
        }

        // 4. Créer les wallets et transactions financières
        echo "🔄 Création des wallets et transactions...\n";
        
        $allUserIds = array_merge($clients, $deliverers);
        
        foreach ($allUserIds as $userId) {
            $balance = rand(0, 500000) / 1000; // 0 à 500 DT
            $pending = rand(0, 100000) / 1000; // 0 à 100 DT
            $frozen = rand(0, 50000) / 1000; // 0 à 50 DT
            
            DB::table('user_wallets')->insert([
                'user_id' => $userId,
                'balance' => $balance,
                'pending_amount' => $pending,
                'frozen_amount' => $frozen,
                'last_transaction_at' => rand(0, 1) ? now()->subHours(rand(1, 72)) : null,
                'last_transaction_id' => rand(0, 1) ? 'TXN_' . strtoupper(Str::random(10)) : null,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);

            // Créer quelques transactions pour chaque wallet
            $transactionTypes = ['DEPOSIT', 'WITHDRAWAL', 'PAYMENT', 'REFUND', 'FEE'];
            for ($j = 0; $j < rand(2, 8); $j++) {
                $amount = rand(1000, 100000) / 1000;
                $type = $transactionTypes[array_rand($transactionTypes)];
                
                DB::table('financial_transactions')->insert([
                    'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                    'user_id' => $userId,
                    'type' => $type,
                    'amount' => $amount,
                    'status' => rand(0, 10) > 1 ? 'COMPLETED' : 'PENDING',
                    'description' => "Transaction $type - " . $amount . " DT",
                    'sequence_number' => $j + 1,
                    'wallet_balance_before' => rand(0, 1000000) / 1000,
                    'wallet_balance_after' => rand(0, 1000000) / 1000,
                    'checksum' => hash('sha256', $userId . $amount . $type),
                    'metadata' => json_encode(['source' => 'test_seeder']),
                    'reference' => 'REF_' . strtoupper(Str::random(8)),
                    'completed_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 45)),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Créer les adresses sauvegardées
        echo "🔄 Création des adresses sauvegardées...\n";
        
        foreach ($clients as $clientId) {
            // Adresses clients
            for ($i = 0; $i < rand(2, 5); $i++) {
                DB::table('saved_addresses')->insert([
                    'user_id' => $clientId,
                    'type' => 'CLIENT',
                    'name' => "Destinataire " . ($i + 1),
                    'label' => rand(0, 1) ? ['Domicile', 'Bureau', 'Magasin'][array_rand(['Domicile', 'Bureau', 'Magasin'])] : null,
                    'phone' => "+216 9" . rand(1, 9) . " " . rand(100, 999) . " " . rand(100, 999),
                    'address' => "Adresse " . ($i + 1) . ", Rue " . rand(1, 50) . ", " . $tunisianDelegations[array_rand($tunisianDelegations)]['name'],
                    'delegation_id' => $delegations[array_rand($delegations)],
                    'is_default' => $i === 0,
                    'usage_count' => rand(0, 20),
                    'last_used_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now(),
                ]);
            }

            // Adresses fournisseurs
            for ($i = 0; $i < rand(1, 3); $i++) {
                DB::table('saved_addresses')->insert([
                    'user_id' => $clientId,
                    'type' => 'SUPPLIER',
                    'name' => "Fournisseur " . ($i + 1),
                    'label' => rand(0, 1) ? ['Entrepôt', 'Magasin'][array_rand(['Entrepôt', 'Magasin'])] : null,
                    'phone' => "+216 7" . rand(1, 9) . " " . rand(100, 999) . " " . rand(100, 999),
                    'address' => "Zone Industrielle " . ($i + 1) . ", " . $tunisianDelegations[array_rand($tunisianDelegations)]['name'],
                    'delegation_id' => $delegations[array_rand($delegations)],
                    'is_default' => $i === 0,
                    'usage_count' => rand(0, 50),
                    'last_used_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Créer les lots d'importation
        echo "🔄 Création des lots d'importation...\n";
        
        $importBatches = [];
        foreach (array_slice($clients, 0, 5) as $clientId) { // Seulement 5 clients ont des imports
            $batchId = DB::table('import_batches')->insertGetId([
                'batch_code' => 'BATCH_' . strtoupper(Str::random(8)),
                'user_id' => $clientId,
                'filename' => 'import_packages_' . date('Y_m_d_H_i_s') . '.csv',
                'total_rows' => $totalRows = rand(10, 100),
                'processed_rows' => $processedRows = rand(5, $totalRows),
                'successful_rows' => $successfulRows = rand(3, $processedRows),
                'failed_rows' => $processedRows - $successfulRows,
                'status' => ['COMPLETED', 'PROCESSING', 'FAILED'][array_rand(['COMPLETED', 'PROCESSING', 'FAILED'])],
                'started_at' => now()->subHours(rand(1, 48)),
                'completed_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
                'errors' => json_encode(['Ligne 5: Délégation invalide', 'Ligne 12: Téléphone manquant']),
                'summary' => json_encode(['total_amount' => rand(1000, 10000), 'packages_created' => $successfulRows]),
                'file_path' => 'imports/' . date('Y/m/d') . '/import_packages_' . $clientId . '.csv',
                'created_at' => now()->subDays(rand(1, 15)),
                'updated_at' => now(),
            ]);
            $importBatches[] = $batchId;
        }

        // 7. Créer les packages
        echo "🔄 Création des packages...\n";
        
        $packageStatuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'DELIVERED', 'PAID', 'REFUSED', 'RETURNED', 'UNAVAILABLE', 'VERIFIED', 'CANCELLED'];
        $packages = [];
        
        for ($i = 1; $i <= 50; $i++) {
            $senderId = $clients[array_rand($clients)];
            $delegationFrom = $delegations[array_rand($delegations)];
            $delegationTo = $delegations[array_rand($delegations)];
            $status = $packageStatuses[array_rand($packageStatuses)];
            $assignedDeliverer = rand(0, 1) ? $deliverers[array_rand($deliverers)] : null;
            
            $packageId = DB::table('packages')->insertGetId([
                'package_code' => 'PKG_' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'sender_id' => $senderId,
                'sender_data' => json_encode([
                    'name' => "Expéditeur $i",
                    'phone' => "+216 20 123 $i",
                    'address' => "Adresse expéditeur $i"
                ]),
                'supplier_data' => rand(0, 1) ? json_encode([
                    'company' => "Fournisseur $i",
                    'contact' => "Contact $i",
                    'reference' => "SUP_$i"
                ]) : null,
                'delegation_from' => $delegationFrom,
                'pickup_delegation_id' => rand(0, 1) ? $delegations[array_rand($delegations)] : null,
                'pickup_address' => rand(0, 1) ? "Adresse de collecte $i" : null,
                'pickup_phone' => rand(0, 1) ? "+216 25 123 $i" : null,
                'pickup_notes' => rand(0, 1) ? "Notes de collecte pour le package $i" : null,
                'recipient_data' => json_encode([
                    'name' => "Destinataire $i",
                    'phone' => "+216 91 123 $i",
                    'address' => "Adresse destinataire $i",
                    'alternative_phone' => rand(0, 1) ? "+216 92 123 $i" : null
                ]),
                'delegation_to' => $delegationTo,
                'content_description' => ['Vêtements', 'Électronique', 'Cosmétiques', 'Livres', 'Accessoires'][array_rand(['Vêtements', 'Électronique', 'Cosmétiques', 'Livres', 'Accessoires'])],
                'notes' => rand(0, 1) ? "Notes spéciales pour le package $i" : null,
                'cod_amount' => rand(0, 200000) / 1000, // 0 à 200 DT
                'package_weight' => rand(100, 5000) / 1000, // 0.1 à 5 kg
                'package_dimensions' => json_encode([
                    'length' => rand(10, 50),
                    'width' => rand(10, 50),
                    'height' => rand(5, 30)
                ]),
                'package_value' => rand(10000, 500000) / 1000, // 10 à 500 DT
                'delivery_fee' => rand(3000, 12000) / 1000, // 3 à 12 DT
                'return_fee' => rand(2000, 8000) / 1000, // 2 à 8 DT
                'special_instructions' => rand(0, 1) ? "Instructions spéciales pour la livraison" : null,
                'is_fragile' => rand(0, 1),
                'requires_signature' => rand(0, 1),
                'status' => $status,
                'assigned_deliverer_id' => $assignedDeliverer,
                'assigned_at' => $assignedDeliverer ? now()->subHours(rand(1, 48)) : null,
                'delivery_attempts' => rand(0, 3),
                'cod_modifiable_by_commercial' => rand(0, 1),
                'amount_in_escrow' => rand(0, 1) ? rand(10000, 100000) / 1000 : 0,
                'import_batch_id' => rand(0, 1) && !empty($importBatches) ? $importBatches[array_rand($importBatches)] : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
            $packages[] = $packageId;
        }

        // 8. Créer l'historique des statuts des packages
        echo "🔄 Création de l'historique des statuts...\n";
        
        foreach (array_slice($packages, 0, 30) as $packageId) { // Historique pour 30 packages
            $statusHistory = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'];
            $previousStatus = '';
            
            foreach ($statusHistory as $index => $newStatus) {
                if ($index === 0) {
                    $previousStatus = 'DRAFT';
                } else {
                    $previousStatus = $statusHistory[$index - 1];
                }
                
                DB::table('package_status_histories')->insert([
                    'package_id' => $packageId,
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $newStatus === 'ACCEPTED' ? $deliverers[array_rand($deliverers)] : $commercials[array_rand($commercials)],
                    'changed_by_role' => $newStatus === 'ACCEPTED' ? 'DELIVERER' : 'COMMERCIAL',
                    'notes' => "Changement de statut vers $newStatus",
                    'additional_data' => json_encode(['automated' => false]),
                    'ip_address' => '192.168.1.' . rand(100, 200),
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'created_at' => now()->subDays(rand(1, 15))->subHours($index),
                    'updated_at' => now(),
                ]);
            }
        }

        // 9. Créer les réclamations
        echo "🔄 Création des réclamations...\n";
        
        $complaintTypes = ['CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN', 'RETURN_DELAY', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT', 'CUSTOM'];
        $complaints = [];
        
        for ($i = 1; $i <= 15; $i++) {
            $packageId = $packages[array_rand($packages)];
            $clientId = $clients[array_rand($clients)];
            $type = $complaintTypes[array_rand($complaintTypes)];
            
            $complaintId = DB::table('complaints')->insertGetId([
                'complaint_code' => 'COMP_' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'package_id' => $packageId,
                'client_id' => $clientId,
                'type' => $type,
                'description' => "Réclamation de type $type pour le package",
                'additional_data' => json_encode(['urgency' => rand(0, 1) ? 'high' : 'normal']),
                'status' => ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'REJECTED'][array_rand(['PENDING', 'IN_PROGRESS', 'RESOLVED', 'REJECTED'])],
                'priority' => ['LOW', 'NORMAL', 'HIGH', 'URGENT'][array_rand(['LOW', 'NORMAL', 'HIGH', 'URGENT'])],
                'assigned_commercial_id' => rand(0, 1) ? $commercials[array_rand($commercials)] : null,
                'resolution_notes' => rand(0, 1) ? "Résolution de la réclamation $i" : null,
                'resolution_data' => rand(0, 1) ? json_encode(['action' => 'resolved']) : null,
                'resolved_at' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                'created_at' => now()->subDays(rand(1, 20)),
                'updated_at' => now(),
            ]);
            $complaints[] = $complaintId;
        }

        // 10. Créer les notifications
        echo "🔄 Création des notifications...\n";
        
        $notificationTypes = ['PACKAGE_DELIVERED', 'PAYMENT_RECEIVED', 'COMPLAINT_CREATED', 'WALLET_LOW', 'SYSTEM_MAINTENANCE'];
        
        foreach (array_merge($clients, $deliverers, $commercials) as $userId) {
            for ($i = 0; $i < rand(2, 8); $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                
                DB::table('notifications')->insert([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => "Notification $type",
                    'message' => "Message de notification pour $type",
                    'data' => json_encode(['additional' => 'data']),
                    'priority' => ['LOW', 'NORMAL', 'HIGH', 'URGENT'][array_rand(['LOW', 'NORMAL', 'HIGH', 'URGENT'])],
                    'read' => rand(0, 1),
                    'read_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                    'expires_at' => rand(0, 1) ? now()->addDays(rand(7, 30)) : null,
                    'action_url' => rand(0, 1) ? '/packages/' . ($packages[array_rand($packages)] ?? 1) : null,
                    'related_type' => rand(0, 1) ? 'Package' : null,
                    'related_id' => rand(0, 1) ? $packages[array_rand($packages)] ?? 1 : null,
                    'created_at' => now()->subDays(rand(1, 15)),
                    'updated_at' => now(),
                ]);
            }
        }

        // 11. Créer les demandes de retrait
        echo "🔄 Création des demandes de retrait...\n";
        
        for ($i = 1; $i <= 8; $i++) {
            DB::table('withdrawal_requests')->insert([
                'request_code' => 'WD_' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients[array_rand($clients)],
                'amount' => rand(50000, 500000) / 1000, // 50 à 500 DT
                'method' => ['BANK_TRANSFER', 'CASH_DELIVERY'][array_rand(['BANK_TRANSFER', 'CASH_DELIVERY'])],
                'bank_details' => json_encode([
                    'bank_name' => 'Banque de Tunisie',
                    'rib' => '1234567890123456789012345'
                ]),
                'status' => ['PENDING', 'APPROVED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'][array_rand(['PENDING', 'APPROVED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'])],
                'processed_by_commercial_id' => rand(0, 1) ? $commercials[array_rand($commercials)] : null,
                'assigned_deliverer_id' => rand(0, 1) ? $deliverers[array_rand($deliverers)] : null,
                'delivery_receipt_code' => rand(0, 1) ? 'REC_' . strtoupper(Str::random(8)) : null,
                'delivered_at' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                'delivery_proof' => rand(0, 1) ? json_encode(['signature' => 'signature.jpg']) : null,
                'processing_notes' => rand(0, 1) ? "Notes de traitement pour la demande $i" : null,
                'rejection_reason' => rand(0, 1) ? "Raison de rejet" : null,
                'processed_at' => rand(0, 1) ? now()->subDays(rand(1, 15)) : null,
                'created_at' => now()->subDays(rand(1, 25)),
                'updated_at' => now(),
            ]);
        }

        // 12. Créer les modifications de COD
        echo "🔄 Création des modifications de COD...\n";
        
        for ($i = 1; $i <= 10; $i++) {
            $packageId = $packages[array_rand($packages)];
            $oldAmount = rand(50000, 200000) / 1000;
            $newAmount = rand(30000, 250000) / 1000;
            
            DB::table('cod_modifications')->insert([
                'package_id' => $packageId,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'modified_by_commercial_id' => $commercials[array_rand($commercials)],
                'reason' => ['Client Request', 'Error Correction', 'Price Change'][array_rand(['Client Request', 'Error Correction', 'Price Change'])],
                'client_complaint_id' => rand(0, 1) && !empty($complaints) ? $complaints[array_rand($complaints)] : null,
                'modification_notes' => "Modification du montant COD de {$oldAmount} vers {$newAmount} DT",
                'context_data' => json_encode(['previous_attempts' => rand(0, 3)]),
                'ip_address' => '192.168.1.' . rand(100, 200),
                'emergency_modification' => rand(0, 1),
                'created_at' => now()->subDays(rand(1, 20)),
                'updated_at' => now(),
            ]);
        }

        // 13. Créer les vidages de portefeuilles livreurs
        echo "🔄 Création des vidages de portefeuilles...\n";
        
        for ($i = 1; $i <= 6; $i++) {
            $walletAmount = rand(100000, 1000000) / 1000;
            $physicalAmount = $walletAmount + rand(-10000, 10000) / 1000; // Différence possible
            
            DB::table('deliverer_wallet_emptyings')->insert([
                'deliverer_id' => $deliverers[array_rand($deliverers)],
                'commercial_id' => $commercials[array_rand($commercials)],
                'wallet_amount' => $walletAmount,
                'physical_amount' => $physicalAmount,
                'discrepancy_amount' => abs($walletAmount - $physicalAmount),
                'emptying_date' => now()->subDays(rand(1, 30)),
                'notes' => rand(0, 1) ? "Notes pour le vidage $i" : null,
                'receipt_generated' => rand(0, 1),
                'receipt_path' => rand(0, 1) ? "receipts/emptying_$i.pdf" : null,
                'emptying_details' => json_encode([
                    'cash_breakdown' => ['50dt' => rand(0, 10), '20dt' => rand(0, 20), '10dt' => rand(0, 30)]
                ]),
                'deliverer_acknowledged' => rand(0, 1),
                'deliverer_acknowledged_at' => rand(0, 1) ? now()->subDays(rand(1, 25)) : null,
                'created_at' => now()->subDays(rand(1, 35)),
                'updated_at' => now(),
            ]);
        }

        // 14. Créer les demandes de recharge
        echo "🔄 Création des demandes de recharge...\n";
        
        for ($i = 1; $i <= 12; $i++) {
            DB::table('topup_requests')->insert([
                'request_code' => 'TOP_' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'client_id' => $clients[array_rand($clients)],
                'amount' => rand(50000, 1000000) / 1000, // 50 à 1000 DT
                'method' => ['BANK_TRANSFER', 'BANK_DEPOSIT', 'CASH'][array_rand(['BANK_TRANSFER', 'BANK_DEPOSIT', 'CASH'])],
                'bank_transfer_id' => rand(0, 1) ? 'TRF_' . strtoupper(Str::random(10)) : null,
                'proof_document' => rand(0, 1) ? "proofs/topup_proof_$i.pdf" : null,
                'notes' => rand(0, 1) ? "Notes pour la recharge $i" : null,
                'status' => ['PENDING', 'VALIDATED', 'REJECTED', 'CANCELLED'][array_rand(['PENDING', 'VALIDATED', 'REJECTED', 'CANCELLED'])],
                'processed_by_id' => rand(0, 1) ? $commercials[array_rand($commercials)] : null,
                'processed_at' => rand(0, 1) ? now()->subDays(rand(1, 15)) : null,
                'rejection_reason' => rand(0, 1) ? "Document illisible" : null,
                'validation_notes' => rand(0, 1) ? "Recharge validée et créditée" : null,
                'metadata' => json_encode(['source' => 'mobile_app', 'device' => 'android']),
                'created_at' => now()->subDays(rand(1, 20)),
                'updated_at' => now(),
            ]);
        }

        // 15. Créer les logs d'actions
        echo "🔄 Création des logs d'actions...\n";
        
        $actionTypes = ['CREATE_PACKAGE', 'UPDATE_STATUS', 'MODIFY_COD', 'ASSIGN_DELIVERER', 'EMPTY_WALLET', 'VALIDATE_TOPUP'];
        $targetTypes = ['Package', 'User', 'Wallet', 'TopupRequest'];
        
        foreach (array_merge($commercials, $deliverers) as $userId) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $actionType = $actionTypes[array_rand($actionTypes)];
                $targetType = $targetTypes[array_rand($targetTypes)];
                
                DB::table('action_logs')->insert([
                    'user_id' => $userId,
                    'user_role' => in_array($userId, $commercials) ? 'COMMERCIAL' : 'DELIVERER',
                    'action_type' => $actionType,
                    'target_type' => $targetType,
                    'target_id' => rand(1, 50),
                    'old_value' => rand(0, 1) ? json_encode(['status' => 'PENDING']) : null,
                    'new_value' => rand(0, 1) ? json_encode(['status' => 'COMPLETED']) : null,
                    'ip_address' => '192.168.1.' . rand(100, 200),
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'additional_data' => json_encode(['automated' => false, 'source' => 'web_interface']),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }

        // 16. Créer les sauvegardes de transactions
        echo "🔄 Création des sauvegardes de transactions...\n";
        
        for ($i = 1; $i <= 20; $i++) {
            DB::table('wallet_transaction_backups')->insert([
                'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
                'snapshot_data' => json_encode([
                    'wallet_state' => ['balance' => rand(0, 1000000) / 1000],
                    'transaction_data' => ['amount' => rand(1000, 100000) / 1000],
                    'metadata' => ['backup_reason' => 'scheduled_backup']
                ]),
                'backup_at' => now()->subDays(rand(1, 60)),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
        }

        echo "✅ Seeding terminé avec succès!\n";
        echo "📊 Données créées:\n";
        echo "   - " . (1 + 3 + 5 + 10) . " utilisateurs (1 superviseur, 3 commerciaux, 5 livreurs, 10 clients)\n";
        echo "   - " . count($delegations) . " délégations\n";
        echo "   - " . count($clients) . " profils clients\n";
        echo "   - " . count($allUserIds) . " wallets avec transactions\n";
        echo "   - " . count($packages) . " packages\n";
        echo "   - 15 réclamations\n";
        echo "   - Notifications, demandes de retrait, modifications COD, etc.\n";
        echo "🔐 Mot de passe pour tous les utilisateurs: password123\n";
    }
}