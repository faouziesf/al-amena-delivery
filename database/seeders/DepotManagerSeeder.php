<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DepotManagerSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run()
    {
        // Créer un chef dépôt d'exemple
        User::create([
            'name' => 'Ahmed Ben Salem',
            'email' => 'chef.depot@alamena.tn',
            'password' => Hash::make('password123'),
            'phone' => '+216 98 123 456',
            'role' => 'DEPOT_MANAGER',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'depot_name' => 'Dépôt Central Tunis',
            'depot_address' => 'Zone Industrielle, Route de Bizerte, Tunis',
            'assigned_gouvernorats' => json_encode(['Tunis', 'Ariana', 'Ben Arous', 'Manouba']),
            'is_depot_manager' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un autre chef dépôt pour le Sud
        User::create([
            'name' => 'Fatma Jlassi',
            'email' => 'chef.depot.sud@alamena.tn',
            'password' => Hash::make('password123'),
            'phone' => '+216 97 456 789',
            'role' => 'DEPOT_MANAGER',
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'depot_name' => 'Dépôt Sud Sfax',
            'depot_address' => 'Zone Industrielle Sfax Sud, Sfax',
            'assigned_gouvernorats' => json_encode(['Sfax', 'Gabes', 'Medenine', 'Tataouine']),
            'is_depot_manager' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ Chefs dépôt créés avec succès!\n";
        echo "📧 Emails: chef.depot@alamena.tn et chef.depot.sud@alamena.tn\n";
        echo "🔑 Mot de passe: password123\n";
    }
}