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

        // Charger les données exportées
        $exportFile = base_path('database_export.json');
        if (file_exists($exportFile)) {
            $data = json_decode(file_get_contents($exportFile), true);
        } else {
            $data = ['users' => [], 'delegations' => [], 'client_profiles' => []];
        }

        // 1. Créer les délégations
        if (!empty($data['delegations'])) {
            echo "🔄 Création des délégations (" . count($data['delegations']) . ")...\n";
            foreach ($data['delegations'] as $delegation) {
                DB::table('delegations')->insert([
                    'name' => $delegation['name'],
                    'gouvernorat' => $delegation['gouvernorat'],
                    'is_active' => $delegation['is_active'] ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            // Créer des délégations par défaut si pas d'export
            echo "🔄 Création des délégations par défaut...\n";
            $defaultDelegations = [
                ['name' => 'Tunis', 'gouvernorat' => 'Tunis'],
                ['name' => 'Ariana', 'gouvernorat' => 'Ariana'],
                ['name' => 'Ben Arous', 'gouvernorat' => 'Ben Arous'],
                ['name' => 'Manouba', 'gouvernorat' => 'Manouba'],
                ['name' => 'Sfax', 'gouvernorat' => 'Sfax'],
                ['name' => 'Sousse', 'gouvernorat' => 'Sousse'],
                ['name' => 'Monastir', 'gouvernorat' => 'Monastir'],
                ['name' => 'Nabeul', 'gouvernorat' => 'Nabeul'],
                ['name' => 'Bizerte', 'gouvernorat' => 'Bizerte'],
                ['name' => 'Gabès', 'gouvernorat' => 'Gabès'],
            ];
            
            foreach ($defaultDelegations as $del) {
                DB::table('delegations')->insert([
                    'name' => $del['name'],
                    'gouvernorat' => $del['gouvernorat'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Créer les utilisateurs
        if (!empty($data['users'])) {
            echo "🔄 Création des utilisateurs (" . count($data['users']) . ")...\n";
            
            $userIds = [];
            foreach ($data['users'] as $user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => now(),
                    'password' => $password, // 12345678 pour tous
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'address' => $user['address'],
                    'city' => $user['city'],
                    'delegation' => $user['delegation'],
                    'delegation_from' => $user['delegation_from'],
                    'delegation_to' => $user['delegation_to'],
                    'deliverer_type' => $user['deliverer_type'],
                    'assigned_depot_manager_id' => $user['assigned_depot_manager_id'],
                    'is_active' => $user['is_active'] ?? true,
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
                'is_active' => true,
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
                'is_active' => true,
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
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer profil client
            DB::table('client_profiles')->insert([
                'user_id' => $clientId,
                'company_name' => 'Boutique Test',
                'status' => 'APPROVED',
                'delivery_fee_rate' => 7.000,
                'cod_fee_percentage' => 2.00,
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
                'delegation' => 'Tunis',
                'deliverer_type' => 'INTERNAL',
                'is_active' => true,
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
                'is_active' => true,
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
                        'company_name' => $profile['company_name'],
                        'company_type' => $profile['company_type'],
                        'registration_number' => $profile['registration_number'],
                        'tax_id' => $profile['tax_id'],
                        'business_address' => $profile['business_address'],
                        'business_phone' => $profile['business_phone'],
                        'website' => $profile['website'],
                        'business_description' => $profile['business_description'],
                        'delivery_fee_rate' => $profile['delivery_fee_rate'] ?? 7.000,
                        'cod_fee_percentage' => $profile['cod_fee_percentage'] ?? 2.00,
                        'status' => $profile['status'] ?? 'APPROVED',
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
