<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SimpleTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔄 Création de données de test simples avec délégations...');

        // Configuration des données de la Tunisie
        $gouvernorats = config('tunisia.gouvernorats');
        $delegations = config('tunisia.delegations');

        // Créer des utilisateurs clients de test
        $clients = [
            [
                'name' => 'Ahmed Ben Ali',
                'email' => 'test.ahmed@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'phone' => '+216 71 123 456',
                'address' => '15 Avenue Bourguiba, Tunis',
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fatma Trabelsi',
                'email' => 'test.fatma@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'phone' => '+216 71 456 789',
                'address' => '25 Rue de la République, Ariana',
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mohamed Sassi',
                'email' => 'test.mohamed@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'phone' => '+216 73 987 654',
                'address' => '8 Boulevard Yahia Ibn Omar, Sousse',
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($clients as $clientData) {
            // Insérer le client s'il n'existe pas déjà
            $existingClient = DB::table('users')->where('email', $clientData['email'])->first();
            if (!$existingClient) {
                $clientId = DB::table('users')->insertGetId($clientData);

                // Créer des adresses de pickup pour ce client
                $this->createPickupAddressesForClient($clientId);

                // Créer des colis de test pour ce client
                $this->createTestPackagesForClient($clientId);
            }
        }

        $this->command->info('✅ Données de test créées avec succès!');
        $this->command->info('📧 Emails de test: test.ahmed@example.com, test.fatma@example.com, test.mohamed@example.com');
        $this->command->info('🔐 Mot de passe: password');
    }

    private function createPickupAddressesForClient($clientId)
    {
        $pickupAddresses = [
            [
                'client_id' => $clientId,
                'name' => 'Boutique Principale',
                'address' => '15 Avenue Habib Bourguiba, Centre Ville',
                'gouvernorat' => 'tunis',
                'delegation' => 'tunis_medina',
                'phone' => '+216 71 123 456',
                'tel2' => '+216 98 765 432',
                'contact_name' => 'Responsable Boutique',
                'notes' => 'Ouvert de 8h à 18h, entrée principale',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Entrepôt Ariana',
                'address' => '25 Rue de la République, Zone Industrielle',
                'gouvernorat' => 'ariana',
                'delegation' => 'ariana_ville',
                'phone' => '+216 71 456 789',
                'contact_name' => 'Gardien Entrepôt',
                'notes' => 'Accès par l\'entrée arrière, code 1234',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clientId,
                'name' => 'Magasin Sousse',
                'address' => '8 Boulevard Yahia Ibn Omar, Centre Commercial',
                'gouvernorat' => 'sousse',
                'delegation' => 'sousse_medina',
                'phone' => '+216 73 987 654',
                'tel2' => '+216 22 345 678',
                'contact_name' => 'Gérant Magasin',
                'notes' => 'Parking disponible, 2ème étage',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($pickupAddresses as $addressData) {
            DB::table('client_pickup_addresses')->insert($addressData);
        }
    }

    private function createTestPackagesForClient($clientId)
    {
        // Récupérer les adresses de pickup pour ce client
        $pickupAddresses = DB::table('client_pickup_addresses')
            ->where('client_id', $clientId)
            ->get();

        if ($pickupAddresses->isEmpty()) {
            return;
        }

        // Récupérer les IDs des délégations pour les relations
        $delegationIds = DB::table('delegations')->pluck('id', 'name');

        $packages = [
            [
                'recipient_name' => 'Amira Ben Salem',
                'recipient_phone' => '+216 72 123 456',
                'recipient_phone2' => '+216 98 111 222',
                'recipient_address' => '12 Rue des Jasmins, Résidence El Andalous, Hammamet',
                'content' => 'Vêtements d\'été',
                'cod_amount' => 89.500,
                'notes' => 'Fragile - Contient des articles en soie',
                'is_fragile' => 1,
                'requires_signature' => 0,
                'allow_opening' => 1,
                'payment_method' => 'cash_only',
                'delegation_to_name' => 'Nabeul'
            ],
            [
                'recipient_name' => 'Karim Jlassi',
                'recipient_phone' => '+216 74 987 654',
                'recipient_address' => '45 Avenue Hedi Chaker, Sfax Centre',
                'content' => 'Matériel informatique',
                'cod_amount' => 1250.000,
                'notes' => 'Ordinateur portable - Très fragile',
                'is_fragile' => 1,
                'requires_signature' => 1,
                'allow_opening' => 0,
                'payment_method' => 'check_only',
                'delegation_to_name' => 'Sfax'
            ],
            [
                'recipient_name' => 'Sonia Mejri',
                'recipient_phone' => '+216 71 555 777',
                'recipient_phone2' => '+216 24 888 999',
                'recipient_address' => '33 Rue de la Liberté, Cité El Ghazala, Ezzahra',
                'content' => 'Livres et documents',
                'cod_amount' => 45.200,
                'is_fragile' => 0,
                'requires_signature' => 0,
                'allow_opening' => 1,
                'payment_method' => 'both',
                'delegation_to_name' => 'Ben Arous'
            ],
            [
                'recipient_name' => 'Hedi Bouazizi',
                'recipient_phone' => '+216 71 444 333',
                'recipient_address' => '67 Route de Tunis, Manouba Ville',
                'content' => 'Produits artisanaux',
                'cod_amount' => 120.750,
                'notes' => 'Produits en céramique - Manipulation délicate',
                'is_fragile' => 1,
                'requires_signature' => 1,
                'allow_opening' => 0,
                'payment_method' => 'cash_only',
                'delegation_to_name' => 'Tunis'
            ],
            [
                'recipient_name' => 'Leila Khelifi',
                'recipient_phone' => '+216 71 666 555',
                'recipient_address' => '19 Impasse des Roses, La Soukra',
                'content' => 'Produits cosmétiques',
                'cod_amount' => 67.300,
                'is_fragile' => 0,
                'requires_signature' => 0,
                'allow_opening' => 1,
                'payment_method' => 'both',
                'delegation_to_name' => 'Ariana'
            ]
        ];

        foreach ($packages as $index => $packageData) {
            // Assigner une adresse de pickup aléatoire
            $pickupAddress = $pickupAddresses->random();

            // Trouver l'ID de la délégation destination
            $delegationToId = $delegationIds->get($packageData['delegation_to_name'], 1);
            $delegationFromId = $delegationIds->get('Tunis', 1); // Délégation d'origine par défaut

            // Extraire le nom du destinataire pour les autres champs
            $delegationToName = $packageData['delegation_to_name'];
            unset($packageData['delegation_to_name']);

            // Préparer les données selon la structure de la table packages
            $finalPackageData = [
                'package_code' => 'TEST' . str_pad($clientId, 3, '0', STR_PAD_LEFT) . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'sender_id' => $clientId,
                'sender_data' => json_encode([
                    'name' => "Client Test $clientId",
                    'phone' => '+216 71 123 456',
                    'address' => 'Adresse expéditeur test'
                ]),
                'delegation_from' => $delegationFromId,
                'pickup_delegation_id' => $delegationFromId,
                'pickup_address' => $pickupAddress->address,
                'pickup_phone' => $pickupAddress->phone,
                'pickup_notes' => 'Collecte via adresse sauvegardée: ' . $pickupAddress->name,
                'recipient_data' => json_encode([
                    'name' => $packageData['recipient_name'],
                    'phone' => $packageData['recipient_phone'],
                    'alternative_phone' => $packageData['recipient_phone2'] ?? null,
                    'address' => $packageData['recipient_address']
                ]),
                'delegation_to' => $delegationToId,
                'content_description' => $packageData['content'],
                'notes' => $packageData['notes'] ?? null,
                'cod_amount' => $packageData['cod_amount'],
                'package_weight' => rand(500, 5000) / 1000,
                'delivery_fee' => rand(500, 1500) / 100,
                'return_fee' => rand(300, 800) / 100,
                'is_fragile' => $packageData['is_fragile'],
                'requires_signature' => $packageData['requires_signature'],
                'allow_opening' => $packageData['allow_opening'],
                'payment_method' => $packageData['payment_method'],
                'status' => 'CREATED',
                'pickup_address_id' => $pickupAddress->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('packages')->insert($finalPackageData);
        }
    }
}