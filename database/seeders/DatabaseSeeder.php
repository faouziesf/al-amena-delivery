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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $financialService = app(FinancialTransactionService::class);
        
        $this->command->info('🚀 Création des données de test Al-Amena Delivery...');

        // ==================== CRÉER LES DÉLÉGATIONS ====================
        $this->command->info('📍 Création des délégations...');
        $delegations = [
            ['name' => 'Tunis Centre', 'zone' => 'Tunis', 'active' => true],
            ['name' => 'Tunis Nord (Ariana)', 'zone' => 'Tunis', 'active' => true],
            ['name' => 'Tunis Sud (Ben Arous)', 'zone' => 'Tunis', 'active' => true],
            ['name' => 'Sfax Centre', 'zone' => 'Sfax', 'active' => true],
            ['name' => 'Sfax Sud', 'zone' => 'Sfax', 'active' => true],
            ['name' => 'Sousse', 'zone' => 'Sousse', 'active' => true],
            ['name' => 'Monastir', 'zone' => 'Monastir', 'active' => true],
            ['name' => 'Gabès', 'zone' => 'Gabès', 'active' => true],
            ['name' => 'Kairouan', 'zone' => 'Kairouan', 'active' => true],
            ['name' => 'Bizerte', 'zone' => 'Bizerte', 'active' => true],
        ];

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

        // Créer les délégations avec le superviseur
        foreach ($delegations as $delegation) {
            Delegation::create(array_merge($delegation, [
                'created_by' => $supervisor->id
            ]));
        }

        // ==================== CRÉER LES COMMERCIAUX ====================
        $this->command->info('🏢 Création des commerciaux...');
        $commercial1 = User::create([
            'name' => 'Ahmed Commercial',
            'email' => 'commercial@alamena.tn',
            'password' => Hash::make('password'),
            'role' => 'COMMERCIAL',
            'phone' => '+216 20 200 001',
            'address' => 'Centre ville, Tunis, Tunisie',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'verified_by' => $supervisor->id,
            'created_by' => $supervisor->id,
        ]);

        $commercial2 = User::create([
            'name' => 'Fatima Commercial',
            'email' => 'commercial2@alamena.tn',
            'password' => Hash::make('password'),
            'role' => 'COMMERCIAL',
            'phone' => '+216 20 200 002',
            'address' => 'Sfax Centre, Sfax, Tunisie',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'verified_by' => $supervisor->id,
            'created_by' => $supervisor->id,
        ]);

        // ==================== CRÉER LES LIVREURS ====================
        $this->command->info('🚚 Création des livreurs...');
        $deliverers = [];
        
        $deliverersData = [
            ['name' => 'Karim Livreur', 'email' => 'livreur1@alamena.tn', 'phone' => '+216 20 300 001', 'balance' => 250.750],
            ['name' => 'Sami Transport', 'email' => 'livreur2@alamena.tn', 'phone' => '+216 20 300 002', 'balance' => 180.500],
            ['name' => 'Hedi Express', 'email' => 'livreur3@alamena.tn', 'phone' => '+216 20 300 003', 'balance' => 95.250],
            ['name' => 'Nizar Rapid', 'email' => 'livreur4@alamena.tn', 'phone' => '+216 20 300 004', 'balance' => 320.000],
            ['name' => 'Tarek Speed', 'email' => 'livreur5@alamena.tn', 'phone' => '+216 20 300 005', 'balance' => 45.500],
        ];

        foreach ($deliverersData as $data) {
            $deliverer = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'DELIVERER',
                'phone' => $data['phone'],
                'address' => 'Tunis, Tunisie',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $commercial1->id,
                'created_by' => $commercial1->id,
            ]);

            // Ajouter des fonds au wallet pour simulation
            $deliverer->wallet->update(['balance' => $data['balance']]);
            $deliverers[] = $deliverer;
        }

        // ==================== CRÉER LES CLIENTS ====================
        $this->command->info('👥 Création des clients...');
        $clients = [];
        
        $clientsData = [
            [
                'name' => 'Boutique Sarra',
                'email' => 'sarra@boutique.tn',
                'phone' => '+216 20 400 001',
                'shop_name' => 'Boutique Sarra Fashion',
                'fiscal_number' => '1234567A',
                'business_sector' => 'Textile',
                'delivery_price' => 7.500,
                'return_price' => 5.000,
                'wallet_balance' => 150.250
            ],
            [
                'name' => 'Tech Store Pro',
                'email' => 'contact@techstore.tn',
                'phone' => '+216 20 400 002', 
                'shop_name' => 'Tech Store Pro',
                'fiscal_number' => '2345678B',
                'business_sector' => 'Électronique',
                'delivery_price' => 8.000,
                'return_price' => 6.000,
                'wallet_balance' => 89.750
            ],
            [
                'name' => 'Parfumerie Jasmin',
                'email' => 'jasmin@parfum.tn',
                'phone' => '+216 20 400 003',
                'shop_name' => 'Parfumerie Jasmin',
                'fiscal_number' => '3456789C',
                'business_sector' => 'Cosmétiques',
                'delivery_price' => 6.500,
                'return_price' => 4.500,
                'wallet_balance' => 245.000
            ],
            [
                'name' => 'Librairie Moderne',
                'email' => 'moderne@livres.tn',
                'phone' => '+216 20 400 004',
                'shop_name' => 'Librairie Moderne',
                'fiscal_number' => '4567890D',
                'business_sector' => 'Éducation',
                'delivery_price' => 5.500,
                'return_price' => 3.500,
                'wallet_balance' => 67.500
            ],
            [
                'name' => 'Sport Zone',
                'email' => 'contact@sportzone.tn',
                'phone' => '+216 20 400 005',
                'shop_name' => 'Sport Zone Equipment',
                'fiscal_number' => '5678901E',
                'business_sector' => 'Sport',
                'delivery_price' => 9.000,
                'return_price' => 7.000,
                'wallet_balance' => 198.750
            ],
        ];

        foreach ($clientsData as $data) {
            $client = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'phone' => $data['phone'],
                'address' => 'Tunis, Tunisie',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $commercial1->id,
                'created_by' => $commercial1->id,
            ]);

            // Créer le profil client
            ClientProfile::create([
                'user_id' => $client->id,
                'shop_name' => $data['shop_name'],
                'fiscal_number' => $data['fiscal_number'],
                'business_sector' => $data['business_sector'],
                'offer_delivery_price' => $data['delivery_price'],
                'offer_return_price' => $data['return_price'],
            ]);

            // Ajouter des fonds au wallet
            $client->wallet->update(['balance' => $data['wallet_balance']]);
            $clients[] = $client;
        }

        // ==================== CRÉER DES COLIS ====================
        $this->command->info('📦 Création des colis...');
        $packages = [];
        $delegationIds = Delegation::pluck('id')->toArray();
        
        $packageStatuses = ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'DELIVERED', 'RETURNED'];
        $recipientNames = ['Mohamed Ali', 'Fatma Ben Salem', 'Ahmed Kallel', 'Sonia Trabelsi', 'Hedi Mammou', 'Nour Gharbi'];
        $contents = ['Vêtements', 'Téléphone', 'Parfum', 'Livres', 'Équipement sport', 'Accessoires', 'Chaussures', 'Sac à main'];

        for ($i = 1; $i <= 50; $i++) {
            $client = $clients[array_rand($clients)];
            $deliverer = $deliverers[array_rand($deliverers)];
            $status = $packageStatuses[array_rand($packageStatuses)];
            
            $package = Package::create([
                'sender_id' => $client->id,
                'sender_data' => [
                    'name' => $client->name,
                    'phone' => $client->phone,
                    'address' => $client->address
                ],
                'delegation_from' => $delegationIds[array_rand($delegationIds)],
                'recipient_data' => [
                    'name' => $recipientNames[array_rand($recipientNames)],
                    'phone' => '+216 ' . rand(20000000, 99999999),
                    'address' => 'Avenue ' . ['Bourguiba', 'République', 'Liberté'][array_rand(['Bourguiba', 'République', 'Liberté'])] . ', Tunis'
                ],
                'delegation_to' => $delegationIds[array_rand($delegationIds)],
                'content_description' => $contents[array_rand($contents)],
                'cod_amount' => rand(15, 300) + (rand(0, 999) / 1000),
                'delivery_fee' => $client->clientProfile->offer_delivery_price,
                'return_fee' => $client->clientProfile->offer_return_price,
                'status' => $status,
                'assigned_deliverer_id' => in_array($status, ['ACCEPTED', 'PICKED_UP', 'DELIVERED']) ? $deliverer->id : null,
                'assigned_at' => in_array($status, ['ACCEPTED', 'PICKED_UP', 'DELIVERED']) ? now()->subDays(rand(0, 5)) : null,
                'delivery_attempts' => $status === 'UNAVAILABLE' ? rand(1, 3) : 0,
                'created_at' => now()->subDays(rand(0, 10)),
            ]);

            $packages[] = $package;
        }

        // ==================== CRÉER DES RÉCLAMATIONS ====================
        $this->command->info('🚨 Création des réclamations...');
        $complaintTypes = ['CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT'];
        $priorities = ['LOW', 'NORMAL', 'HIGH', 'URGENT'];
        
        foreach (array_slice($packages, 0, 15) as $package) {
            if (rand(0, 100) < 40) { // 40% chance de réclamation
                $type = $complaintTypes[array_rand($complaintTypes)];
                $priority = $priorities[array_rand($priorities)];
                
                Complaint::create([
                    'package_id' => $package->id,
                    'client_id' => $package->sender_id,
                    'type' => $type,
                    'description' => $this->getComplaintDescription($type),
                    'priority' => $priority,
                    'status' => rand(0, 100) < 70 ? 'PENDING' : 'RESOLVED',
                    'assigned_commercial_id' => rand(0, 100) < 60 ? $commercial1->id : null,
                    'created_at' => now()->subDays(rand(0, 7)),
                ]);
            }
        }

        // ==================== CRÉER DES DEMANDES DE RETRAIT ====================
        $this->command->info('💰 Création des demandes de retrait...');
        foreach (array_slice($clients, 0, 8) as $client) {
            if (rand(0, 100) < 50) { // 50% chance de demande retrait
                $amount = rand(20, min(200, $client->wallet->balance));
                $method = rand(0, 100) < 60 ? 'BANK_TRANSFER' : 'CASH_DELIVERY';
                $status = ['PENDING', 'APPROVED', 'COMPLETED'][array_rand(['PENDING', 'APPROVED', 'COMPLETED'])];
                
                WithdrawalRequest::create([
                    'client_id' => $client->id,
                    'amount' => $amount,
                    'method' => $method,
                    'bank_details' => $method === 'BANK_TRANSFER' ? [
                        'iban' => 'TN59' . rand(1000, 9999) . rand(1000000000, 9999999999),
                        'bank_name' => 'Banque de Tunisie'
                    ] : null,
                    'status' => $status,
                    'processed_by_commercial_id' => $status !== 'PENDING' ? $commercial1->id : null,
                    'processed_at' => $status !== 'PENDING' ? now()->subDays(rand(0, 3)) : null,
                    'created_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }

        // ==================== CRÉER DES MODIFICATIONS COD ====================
        $this->command->info('💱 Création des modifications COD...');
        foreach (array_slice($packages, 0, 10) as $package) {
            if (rand(0, 100) < 30) { // 30% chance de modification COD
                $oldAmount = $package->cod_amount;
                $newAmount = $oldAmount + (rand(-50, 50) + (rand(0, 999) / 1000));
                $newAmount = max(0, $newAmount); // Pas de COD négatif
                
                CodModification::create([
                    'package_id' => $package->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'modified_by_commercial_id' => $commercial1->id,
                    'reason' => 'Ajustement suite à négociation client',
                    'modification_notes' => 'Modification effectuée pour résoudre une réclamation',
                    'ip_address' => '127.0.0.1',
                    'created_at' => now()->subDays(rand(0, 7)),
                ]);
                
                // Mettre à jour le COD du package
                $package->update(['cod_amount' => $newAmount]);
            }
        }

        // ==================== CRÉER DES VIDAGES WALLET LIVREURS ====================
        $this->command->info('🏦 Création des vidages wallet...');
        foreach ($deliverers as $deliverer) {
            if (rand(0, 100) < 60) { // 60% chance de vidage récent
                $walletAmount = rand(50, 300) + (rand(0, 999) / 1000);
                $physicalAmount = $walletAmount + (rand(-10, 10) / 1000); // Petite différence possible
                
                DelivererWalletEmptying::create([
                    'deliverer_id' => $deliverer->id,
                    'commercial_id' => $commercial1->id,
                    'wallet_amount' => $walletAmount,
                    'physical_amount' => $physicalAmount,
                    'discrepancy_amount' => $walletAmount - $physicalAmount,
                    'emptying_date' => now()->subDays(rand(0, 10)),
                    'receipt_generated' => true,
                    'deliverer_acknowledged' => true,
                    'deliverer_acknowledged_at' => now()->subDays(rand(0, 10)),
                ]);
            }
        }

        // ==================== CRÉER DES NOTIFICATIONS ====================
        $this->command->info('🔔 Création des notifications...');
        $notificationTypes = ['COMPLAINT_NEW', 'COMPLAINT_URGENT', 'WITHDRAWAL_REQUEST', 'WALLET_HIGH_BALANCE', 'PACKAGE_BLOCKED'];
        $commercials = [$commercial1, $commercial2];
        
        foreach ($commercials as $commercial) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $priority = ['LOW', 'NORMAL', 'HIGH', 'URGENT'][array_rand(['LOW', 'NORMAL', 'HIGH', 'URGENT'])];
                
                Notification::create([
                    'user_id' => $commercial->id,
                    'type' => $type,
                    'title' => $this->getNotificationTitle($type),
                    'message' => $this->getNotificationMessage($type),
                    'priority' => $priority,
                    'read' => rand(0, 100) < 40, // 40% lues
                    'read_at' => rand(0, 100) < 40 ? now()->subDays(rand(0, 5)) : null,
                    'created_at' => now()->subDays(rand(0, 7)),
                ]);
            }
        }

        // ==================== RÉSUMÉ FINAL ====================
        $this->command->info('✅ Données de test créées avec succès !');
        $this->command->info('');
        $this->command->info('📧 COMPTES CRÉÉS :');
        $this->command->info('   👑 Superviseur : supervisor@alamena.tn / password');
        $this->command->info('   🏢 Commercial 1 : commercial@alamena.tn / password');
        $this->command->info('   🏢 Commercial 2 : commercial2@alamena.tn / password');
        $this->command->info('   🚚 Livreurs : livreur1@alamena.tn à livreur5@alamena.tn / password');
        $this->command->info('   👥 Clients : sarra@boutique.tn, contact@techstore.tn, etc. / password');
        $this->command->info('');
        $this->command->info('📊 DONNÉES GÉNÉRÉES :');
        $this->command->info('   📍 ' . count($delegations) . ' délégations');
        $this->command->info('   👥 ' . count($clients) . ' clients avec profils');
        $this->command->info('   🚚 ' . count($deliverers) . ' livreurs avec wallets');
        $this->command->info('   📦 ' . count($packages) . ' colis (différents statuts)');
        $this->command->info('   🚨 ~' . Complaint::count() . ' réclamations');
        $this->command->info('   💰 ~' . WithdrawalRequest::count() . ' demandes de retrait');
        $this->command->info('   💱 ~' . CodModification::count() . ' modifications COD');
        $this->command->info('   🔔 ~' . Notification::count() . ' notifications');
        $this->command->info('   🏦 ~' . DelivererWalletEmptying::count() . ' vidages wallet');
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