<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Package;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Hash;

class AdminTestDataSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run()
    {
        echo "🔄 Création de données de test pour l'admin...\n";

        // 1. Créer des utilisateurs de test pour chaque rôle
        $this->createTestUsers();

        // 2. Créer des colis de test
        $this->createTestPackages();

        // 3. Créer des tickets de test
        $this->createTestTickets();

        // 4. Créer des demandes de retrait
        $this->createTestWithdrawals();

        echo "✅ Données de test créées avec succès!\n";
        echo "📧 Comptes de test créés:\n";
        echo "  - admin@test.com (SUPERVISOR) - 123456\n";
        echo "  - client@test.com (CLIENT) - 123456\n";
        echo "  - livreur@test.com (DELIVERER) - 123456\n";
        echo "  - commercial@test.com (COMMERCIAL) - 123456\n";
        echo "  - depot@test.com (DEPOT_MANAGER) - 123456\n";
        echo "🎯 Compte superviseur créé: admin@test.com\n";
    }

    private function createTestUsers()
    {
        // Client de test
        $client = User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Ahmed Ben Ali',
                'password' => Hash::make('123456'),
                'phone' => '+216 98 123 456',
                'role' => 'CLIENT',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'address' => '123 Rue de la République, Tunis'
            ]
        );

        // Créer wallet pour client et mettre à jour le solde
        $client->ensureWallet();
        $client->wallet->update([
            'balance' => 250.750,
            'last_transaction_at' => now()
        ]);

        // Livreur de test
        $deliverer = User::firstOrCreate(
            ['email' => 'livreur@test.com'],
            [
                'name' => 'Sami Trabelsi',
                'password' => Hash::make('123456'),
                'phone' => '+216 97 456 789',
                'role' => 'DELIVERER',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'deliverer_type' => 'DELEGATION',
                'assigned_delegation' => 'Tunis',
                'delegation_latitude' => 36.8065,
                'delegation_longitude' => 10.1815,
                'delegation_radius_km' => 15
            ]
        );

        // Créer wallet pour livreur et mettre à jour le solde
        $deliverer->ensureWallet();
        $deliverer->wallet->update([
            'balance' => 125.500,
            'last_transaction_at' => now()
        ]);

        // Commercial de test
        $commercial = User::firstOrCreate(
            ['email' => 'commercial@test.com'],
            [
                'name' => 'Leila Mansouri',
                'password' => Hash::make('123456'),
                'phone' => '+216 71 234 567',
                'role' => 'COMMERCIAL',
                'account_status' => 'ACTIVE',
                'verified_at' => now()
            ]
        );

        // Chef dépôt de test
        $depotManager = User::firstOrCreate(
            ['email' => 'depot@test.com'],
            [
                'name' => 'Mohamed Chakroun',
                'password' => Hash::make('123456'),
                'phone' => '+216 70 987 654',
                'role' => 'DEPOT_MANAGER',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'assigned_gouvernorats' => json_encode(['Tunis', 'Ariana', 'Ben Arous'])
            ]
        );

        // Superviseur admin de test
        $supervisor = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('123456'),
                'phone' => '+216 71 111 222',
                'role' => 'SUPERVISOR',
                'account_status' => 'ACTIVE',
                'verified_at' => now()
            ]
        );

        // Ajouter quelques transactions de test
        FinancialTransaction::create([
            'user_id' => $client->id,
            'type' => 'COD_RECEIVED',
            'amount' => 50.750,
            'description' => 'COD reçu pour colis PKG001',
            'metadata' => ['package_id' => 1, 'test' => true]
        ]);

        FinancialTransaction::create([
            'user_id' => $deliverer->id,
            'type' => 'DELIVERY_FEE',
            'amount' => 8.500,
            'description' => 'Frais de livraison PKG001',
            'metadata' => ['package_id' => 1, 'test' => true]
        ]);
    }

    private function createTestPackages()
    {
        echo "⚠️ Création de packages simplifiée - utiliser l'interface pour créer des vrais packages\n";
        // Les packages ont une structure complexe avec JSON - mieux vaut les créer via l'interface
    }

    private function createTestTickets()
    {
        $client = User::where('email', 'client@test.com')->first();
        $commercial = User::where('email', 'commercial@test.com')->first();

        // Tickets de test
        $tickets = [
            [
                'client_id' => $client->id,
                'type' => 'COMPLAINT',
                'subject' => 'Colis endommagé à la livraison',
                'description' => 'Mon colis est arrivé endommagé avec l\'emballage déchiré. Je souhaite un remboursement ou un échange.',
                'status' => 'OPEN',
                'priority' => 'HIGH',
                'assigned_to_id' => $commercial->id
            ],
            [
                'client_id' => $client->id,
                'type' => 'QUESTION',
                'subject' => 'Délai de livraison pour Ben Arous',
                'description' => 'Bonjour, quel est le délai habituel de livraison pour la zone de Ben Arous ? Mon colis urgent doit arriver rapidement.',
                'status' => 'IN_PROGRESS',
                'priority' => 'NORMAL',
                'assigned_to_id' => $commercial->id
            ],
            [
                'client_id' => $client->id,
                'type' => 'SUPPORT',
                'subject' => 'Problème de connexion à l\'application',
                'description' => 'Je n\'arrive pas à me connecter à l\'application mobile. Le message d\'erreur dit "identifiants incorrects" mais je suis sûr de mon mot de passe.',
                'status' => 'RESOLVED',
                'priority' => 'LOW',
                'assigned_to_id' => $commercial->id,
                'resolved_at' => now()->subDays(1)
            ]
        ];

        foreach ($tickets as $ticketData) {
            $ticket = Ticket::create($ticketData);

            // Ajouter quelques messages de test
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $client->id,
                'sender_type' => 'CLIENT',
                'message' => 'Bonjour, j\'ai un problème avec mon colis ' . $ticket->subject,
                'is_internal' => false
            ]);

            if ($ticket->status !== 'OPEN') {
                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'sender_id' => $commercial->id,
                    'sender_type' => 'COMMERCIAL',
                    'message' => 'Merci pour votre message. Nous allons traiter votre demande dans les plus brefs délais.',
                    'is_internal' => false,
                    'read_at' => now()
                ]);
            }
        }
    }

    private function createTestWithdrawals()
    {
        $client = User::where('email', 'client@test.com')->first();
        $commercial = User::where('email', 'commercial@test.com')->first();

        // Demandes de retrait de test
        WithdrawalRequest::create([
            'client_id' => $client->id,
            'amount' => 100.000,
            'method' => 'BANK_TRANSFER',
            'bank_details' => json_encode([
                'account_number' => '12345678901234567890',
                'bank_name' => 'Banque de Tunisie',
                'account_holder' => 'Ahmed Ben Ali'
            ]),
            'status' => 'PENDING',
            'request_code' => 'WD001TEST'
        ]);

        WithdrawalRequest::create([
            'client_id' => $client->id,
            'amount' => 50.000,
            'method' => 'CASH_DELIVERY',
            'status' => 'APPROVED',
            'request_code' => 'WD002TEST',
            'processed_by_commercial_id' => $commercial->id,
            'processed_at' => now()->subHours(2),
            'delivery_receipt_code' => 'DR002TEST'
        ]);
    }
}