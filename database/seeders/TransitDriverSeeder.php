<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TransitDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un compte de test pour livreur de transit
        User::updateOrCreate(
            ['email' => 'transit@al-amena.com'],
            [
                'name' => 'Livreur Transit',
                'email' => 'transit@al-amena.com',
                'password' => Hash::make('123456'),
                'role' => 'DELIVERER',
                'account_status' => 'ACTIVE',
                'phone' => '+216 12 345 678',
                'email_verified_at' => now(),
            ]
        );

        // Créer quelques autres livreurs de transit pour les tests
        User::updateOrCreate(
            ['email' => 'transit2@al-amena.com'],
            [
                'name' => 'Mohamed Ben Salem',
                'email' => 'transit2@al-amena.com',
                'password' => Hash::make('123456'),
                'role' => 'DELIVERER',
                'account_status' => 'ACTIVE',
                'phone' => '+216 22 345 678',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'transit3@al-amena.com'],
            [
                'name' => 'Ali Trabelsi',
                'email' => 'transit3@al-amena.com',
                'password' => Hash::make('123456'),
                'role' => 'DELIVERER',
                'account_status' => 'ACTIVE',
                'phone' => '+216 32 345 678',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Comptes livreurs de transit créés avec succès !');
        $this->command->info('📧 Identifiants de connexion :');
        $this->command->info('   - Email: transit@al-amena.com | Password: 123456');
        $this->command->info('   - Email: transit2@al-amena.com | Password: 123456');
        $this->command->info('   - Email: transit3@al-amena.com | Password: 123456');
    }
}