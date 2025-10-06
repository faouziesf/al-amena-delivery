<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 🔐 Mot de passe pour TOUS les utilisateurs: 12345678
     */
    public function run(): void
    {
        $password = Hash::make('12345678');
        
        echo "🔄 Démarrage du seeding...\n";
        echo "🔐 Mot de passe pour TOUS les utilisateurs: 12345678\n\n";

        // Charger les données exportées si disponibles
        $exportFile = base_path('database_export.json');
        $hasExport = file_exists($exportFile);
        
        if ($hasExport) {
            $data = json_decode(file_get_contents($exportFile), true);
            echo "📦 Utilisation des données exportées\n\n";
        } else {
            $data = ['users' => [], 'delegations' => [], 'client_profiles' => []];
            echo "📦 Création de données par défaut\n\n";
        }

        // 1. Créer les délégations
        if (!empty($data['delegations'])) {
            echo "🔄 Création des délégations (" . count($data['delegations']) . ")...\n";
            foreach ($data['delegations'] as $delegation) {
                DB::table('delegations')->insert([
                    'name' => $delegation['name'],
                    'zone' => $delegation['gouvernorat'] ?? $delegation['zone'] ?? 'Grand Tunis',
                    'active' => $delegation['is_active'] ?? $delegation['active'] ?? true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            // Créer des délégations par défaut si pas d'export
            echo "🔄 Création des délégations par défaut...\n";
            $defaultDelegations = [
                ['name' => 'Tunis', 'zone' => 'Grand Tunis'],
                ['name' => 'Ariana', 'zone' => 'Grand Tunis'],
                ['name' => 'Ben Arous', 'zone' => 'Grand Tunis'],
                ['name' => 'Manouba', 'zone' => 'Grand Tunis'],
                ['name' => 'Sfax', 'zone' => 'Centre'],
                ['name' => 'Sousse', 'zone' => 'Centre'],
                ['name' => 'Monastir', 'zone' => 'Centre'],
                ['name' => 'Nabeul', 'zone' => 'Nord'],
                ['name' => 'Bizerte', 'zone' => 'Nord'],
                ['name' => 'Gabès', 'zone' => 'Sud'],
            ];
            
            foreach ($defaultDelegations as $del) {
                DB::table('delegations')->insert([
                    'name' => $del['name'],
                    'zone' => $del['zone'],
                    'active' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Créer les utilisateurs
        $userIds = [];
        if (!empty($data['users'])) {
            echo "🔄 Création des utilisateurs (" . count($data['users']) . ")...\n";
            
            foreach ($data['users'] as $user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => now(),
                    'password' => $password, // 12345678 pour tous
                    'role' => $user['role'],
                    'phone' => $user['phone'] ?? null,
                    'address' => $user['address'] ?? null,
                    'account_status' => 'ACTIVE',
                    'verified_at' => now(),
                    'verified_by' => null,
                    'created_by' => null,
                    'last_login' => null,
                    'assigned_delegation' => $user['delegation'] ?? null,
                    'delegation_latitude' => null,
                    'delegation_longitude' => null,
                    'delegation_radius_km' => 10,
                    'deliverer_type' => $user['deliverer_type'] ?? 'DELEGATION',
                    'assigned_gouvernorats' => null,
                    'depot_name' => null,
                    'depot_address' => null,
                    'is_depot_manager' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $userIds[$user['id']] = $userId;
            }
        } else {
            // Créer des utilisateurs par défaut
            echo "🔄 Création des utilisateurs par défaut...\n";
            
            // Admin
            DB::table('users')->insert([
                'name' => 'Admin Principal',
                'email' => 'admin@alamena.com',
                'email_verified_at' => now(),
                'password' => $password,
                'role' => 'ADMIN',
                'phone' => '+216 20 000 001',
                'address' => 'Adresse Admin',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => null,
                'created_by' => null,
                'last_login' => null,
                'assigned_delegation' => null,
                'delegation_latitude' => null,
                'delegation_longitude' => null,
                'delegation_radius_km' => 10,
                'deliverer_type' => 'DELEGATION',
                'assigned_gouvernorats' => null,
                'depot_name' => null,
                'depot_address' => null,
                'is_depot_manager' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Commercial
            DB::table('users')->insert([
                'name' => 'Commercial Test',
                'email' => 'commercial@alamena.com',
                'email_verified_at' => now(),
                'password' => $password,
                'role' => 'COMMERCIAL',
                'phone' => '+216 20 000 002',
                'address' => 'Adresse Commercial',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => null,
                'created_by' => null,
                'last_login' => null,
                'assigned_delegation' => null,
                'delegation_latitude' => null,
                'delegation_longitude' => null,
                'delegation_radius_km' => 10,
                'deliverer_type' => 'DELEGATION',
                'assigned_gouvernorats' => null,
                'depot_name' => null,
                'depot_address' => null,
                'is_depot_manager' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Client
            $clientId = DB::table('users')->insertGetId([
                'name' => 'Client Test',
                'email' => 'client@alamena.com',
                'email_verified_at' => now(),
                'password' => $password,
                'role' => 'CLIENT',
                'phone' => '+216 20 000 003',
                'address' => 'Adresse Client',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => null,
                'created_by' => null,
                'last_login' => null,
                'assigned_delegation' => null,
                'delegation_latitude' => null,
                'delegation_longitude' => null,
                'delegation_radius_km' => 10,
                'deliverer_type' => 'DELEGATION',
                'assigned_gouvernorats' => null,
                'depot_name' => null,
                'depot_address' => null,
                'is_depot_manager' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer profil client
            DB::table('client_profiles')->insert([
                'user_id' => $clientId,
                'shop_name' => 'Boutique Test',
                'fiscal_number' => '1234567890123',
                'business_sector' => 'E-commerce',
                'identity_document' => 'CIN_12345678.pdf',
                'offer_delivery_price' => 7.000,
                'offer_return_price' => 5.000,
                'internal_notes' => 'Client test par défaut',
                'validation_status' => 'APPROVED',
                'validated_by' => null,
                'validated_at' => now(),
                'validation_notes' => 'Approuvé automatiquement',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Livreur
            DB::table('users')->insert([
                'name' => 'Livreur Test',
                'email' => 'deliverer@alamena.com',
                'email_verified_at' => now(),
                'password' => $password,
                'role' => 'DELIVERER',
                'phone' => '+216 20 000 004',
                'address' => 'Adresse Livreur',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => null,
                'created_by' => null,
                'last_login' => null,
                'assigned_delegation' => 'Tunis',
                'delegation_latitude' => null,
                'delegation_longitude' => null,
                'delegation_radius_km' => 10,
                'deliverer_type' => 'INTERNAL',
                'assigned_gouvernorats' => null,
                'depot_name' => null,
                'depot_address' => null,
                'is_depot_manager' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Depot Manager
            DB::table('users')->insert([
                'name' => 'Depot Manager Test',
                'email' => 'depot@alamena.com',
                'email_verified_at' => now(),
                'password' => $password,
                'role' => 'DEPOT_MANAGER',
                'phone' => '+216 20 000 005',
                'address' => 'Adresse Depot',
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => null,
                'created_by' => null,
                'last_login' => null,
                'assigned_delegation' => null,
                'delegation_latitude' => null,
                'delegation_longitude' => null,
                'delegation_radius_km' => 10,
                'deliverer_type' => 'DELEGATION',
                'assigned_gouvernorats' => null,
                'depot_name' => 'Depot Test',
                'depot_address' => 'Adresse Depot Principal',
                'is_depot_manager' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Créer les profils clients (si données exportées)
        if (!empty($data['client_profiles'])) {
            echo "🔄 Création des profils clients (" . count($data['client_profiles']) . ")...\n";
            foreach ($data['client_profiles'] as $profile) {
                if (isset($userIds[$profile['user_id']])) {
                    DB::table('client_profiles')->insert([
                        'user_id' => $userIds[$profile['user_id']],
                        'shop_name' => $profile['company_name'] ?? $profile['shop_name'] ?? null,
                        'fiscal_number' => $profile['registration_number'] ?? $profile['fiscal_number'] ?? null,
                        'business_sector' => $profile['business_sector'] ?? 'E-commerce',
                        'identity_document' => $profile['identity_document'] ?? null,
                        'offer_delivery_price' => $profile['delivery_fee_rate'] ?? $profile['offer_delivery_price'] ?? 7.000,
                        'offer_return_price' => $profile['offer_return_price'] ?? 5.000,
                        'internal_notes' => $profile['business_description'] ?? $profile['internal_notes'] ?? null,
                        'validation_status' => $profile['status'] ?? $profile['validation_status'] ?? 'PENDING',
                        'validated_by' => $profile['validated_by'] ?? null,
                        'validated_at' => $profile['validated_at'] ?? null,
                        'validation_notes' => $profile['validation_notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        echo "\n✅ Seeding terminé avec succès!\n";
        echo "📊 Résumé:\n";
        echo "   - Délégations: " . DB::table('delegations')->count() . "\n";
        echo "   - Utilisateurs: " . DB::table('users')->count() . "\n";
        echo "   - Profils clients: " . DB::table('client_profiles')->count() . "\n";
        echo "\n🔐 IMPORTANT: Tous les mots de passe sont: 12345678\n";
        echo "\n📧 Comptes créés:\n";
        
        $users = DB::table('users')->select('email', 'role')->get();
        foreach ($users as $user) {
            echo "   - {$user->email} ({$user->role})\n";
        }
    }
}
