<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClientPickupAddress;
use App\Models\Package;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Configuration des données de la Tunisie
        $gouvernorats = config('tunisia.gouvernorats');
        $delegations = config('tunisia.delegations');

        // Créer des utilisateurs clients de test
        $clients = [
            [
                'name' => 'Ahmed Ben Ali',
                'email' => 'ahmed.benali@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Fatma Trabelsi',
                'email' => 'fatma.trabelsi@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mohamed Sassi',
                'email' => 'mohamed.sassi@example.com',
                'password' => Hash::make('password'),
                'role' => 'CLIENT',
                'email_verified_at' => now(),
            ]
        ];

        foreach ($clients as $clientData) {
            $client = User::firstOrCreate(
                ['email' => $clientData['email']],
                $clientData
            );

            // Créer des adresses de pickup pour chaque client
            $this->createPickupAddresses($client);

            // Créer des colis de test pour chaque client
            $this->createTestPackages($client);
        }

        $this->command->info('Données de test créées avec succès!');
    }

    private function createPickupAddresses($client)
    {
        $pickupAddresses = [
            [
                'name' => 'Boutique Principale',
                'address' => '15 Avenue Habib Bourguiba, Centre Ville',
                'gouvernorat' => 'tunis',
                'delegation' => 'tunis_medina',
                'phone' => '+216 71 123 456',
                'tel2' => '+216 98 765 432',
                'contact_name' => 'Responsable Boutique',
                'notes' => 'Ouvert de 8h à 18h, entrée principale',
                'is_default' => true
            ],
            [
                'name' => 'Entrepôt Ariana',
                'address' => '25 Rue de la République, Zone Industrielle',
                'gouvernorat' => 'ariana',
                'delegation' => 'ariana_ville',
                'phone' => '+216 71 456 789',
                'contact_name' => 'Gardien Entrepôt',
                'notes' => 'Accès par l\'entrée arrière, code 1234',
                'is_default' => false
            ],
            [
                'name' => 'Magasin Sousse',
                'address' => '8 Boulevard Yahia Ibn Omar, Centre Commercial',
                'gouvernorat' => 'sousse',
                'delegation' => 'sousse_medina',
                'phone' => '+216 73 987 654',
                'tel2' => '+216 22 345 678',
                'contact_name' => 'Gérant Magasin',
                'notes' => 'Parking disponible, 2ème étage',
                'is_default' => false
            ]
        ];

        foreach ($pickupAddresses as $addressData) {
            $addressData['client_id'] = $client->id;
            ClientPickupAddress::create($addressData);
        }
    }

    private function createTestPackages($client)
    {
        $pickupAddresses = $client->pickupAddresses;

        if ($pickupAddresses->isEmpty()) {
            return;
        }

        $packages = [
            [
                'nom_complet' => 'Amira Ben Salem',
                'gouvernorat' => 'nabeul',
                'delegation' => 'hammamet',
                'telephone_1' => '+216 72 123 456',
                'telephone_2' => '+216 98 111 222',
                'adresse_complete' => '12 Rue des Jasmins, Résidence El Andalous, Hammamet',
                'contenu' => 'Vêtements d\'été',
                'prix' => 89.500,
                'commentaire' => 'Fragile - Contient des articles en soie',
                'fragile' => true,
                'signature_obligatoire' => false,
                'autorisation_ouverture' => true,
                'payment_method' => 'especes_seulement'
            ],
            [
                'nom_complet' => 'Karim Jlassi',
                'gouvernorat' => 'sfax',
                'delegation' => 'sfax_ville',
                'telephone_1' => '+216 74 987 654',
                'adresse_complete' => '45 Avenue Hedi Chaker, Sfax Centre',
                'contenu' => 'Matériel informatique',
                'prix' => 1250.000,
                'commentaire' => 'Ordinateur portable - Très fragile',
                'fragile' => true,
                'signature_obligatoire' => true,
                'autorisation_ouverture' => false,
                'payment_method' => 'cheque_seulement'
            ],
            [
                'nom_complet' => 'Sonia Mejri',
                'gouvernorat' => 'ben_arous',
                'delegation' => 'ezzahra',
                'telephone_1' => '+216 71 555 777',
                'telephone_2' => '+216 24 888 999',
                'adresse_complete' => '33 Rue de la Liberté, Cité El Ghazala, Ezzahra',
                'contenu' => 'Livres et documents',
                'prix' => 45.200,
                'fragile' => false,
                'signature_obligatoire' => false,
                'autorisation_ouverture' => true,
                'payment_method' => 'especes_et_cheques'
            ],
            [
                'nom_complet' => 'Hedi Bouazizi',
                'gouvernorat' => 'manouba',
                'delegation' => 'manouba',
                'telephone_1' => '+216 71 444 333',
                'adresse_complete' => '67 Route de Tunis, Manouba Ville',
                'contenu' => 'Produits artisanaux',
                'prix' => 120.750,
                'commentaire' => 'Produits en céramique - Manipulation délicate',
                'fragile' => true,
                'signature_obligatoire' => true,
                'autorisation_ouverture' => false,
                'payment_method' => 'especes_seulement'
            ],
            [
                'nom_complet' => 'Leila Khelifi',
                'gouvernorat' => 'ariana',
                'delegation' => 'la_soukra',
                'telephone_1' => '+216 71 666 555',
                'adresse_complete' => '19 Impasse des Roses, La Soukra',
                'contenu' => 'Produits cosmétiques',
                'prix' => 67.300,
                'fragile' => false,
                'signature_obligatoire' => false,
                'autorisation_ouverture' => true,
                'payment_method' => 'especes_et_cheques'
            ]
        ];

        foreach ($packages as $index => $packageData) {
            // Assigner une adresse de pickup aléatoire
            $pickupAddress = $pickupAddresses->random();

            $packageData = array_merge($packageData, [
                'sender_id' => $client->id,
                'pickup_address_id' => $pickupAddress->id,
                'package_code' => 'PKG' . str_pad($client->id, 3, '0', STR_PAD_LEFT) . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'status' => 'CREATED',
                'package_weight' => rand(500, 5000) / 1000, // Poids entre 0.5 et 5 kg
                'delivery_fee' => rand(500, 1500) / 100, // Frais entre 5 et 15 TND
            ]);

            Package::create($packageData);
        }
    }
}