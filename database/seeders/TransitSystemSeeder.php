<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TransitRoute;
use App\Models\TransitBox;

class TransitSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le livreur de transit de test
        $transitDriver = User::where('email', 'transit@al-amena.com')->first();

        if (!$transitDriver) {
            $this->command->error('❌ Livreur de transit non trouvé. Veuillez d\'abord exécuter TransitDriverSeeder');
            return;
        }

        // Créer une tournée pour aujourd'hui
        $route = TransitRoute::create([
            'driver_id' => $transitDriver->id,
            'origin_depot' => 'TUNIS',
            'destination_depot' => 'SFAX',
            'date' => now()->toDateString(),
            'status' => 'ASSIGNED',
            'created_by' => $transitDriver->id
        ]);

        // Créer des boîtes de transit pour cette tournée
        $governorates = ['SFAX', 'SOUSSE', 'GABES', 'BIZERTE', 'KAIROUAN'];

        foreach ($governorates as $index => $governorate) {
            $boxCode = TransitBox::generateCode($governorate);

            TransitBox::create([
                'route_id' => $route->id,
                'code' => $boxCode,
                'destination_governorate' => $governorate,
                'packages_count' => rand(15, 35),
                'status' => 'PENDING',
                'package_ids' => $this->generateFakePackageIds(rand(15, 35)),
                'created_by' => $transitDriver->id
            ]);
        }

        // Créer une tournée historique (terminée hier)
        $historicalRoute = TransitRoute::create([
            'driver_id' => $transitDriver->id,
            'origin_depot' => 'TUNIS',
            'destination_depot' => 'SOUSSE',
            'date' => now()->subDay()->toDateString(),
            'status' => 'COMPLETED',
            'started_at' => now()->subDay()->setHour(8),
            'completed_at' => now()->subDay()->setHour(14),
            'created_by' => $transitDriver->id
        ]);

        // Créer des boîtes pour la tournée historique
        foreach (['SOUSSE', 'MONASTIR', 'MAHDIA'] as $governorate) {
            $boxCode = TransitBox::generateCode($governorate);

            TransitBox::create([
                'route_id' => $historicalRoute->id,
                'code' => str_replace(now()->format('dmY'), now()->subDay()->format('dmY'), $boxCode),
                'destination_governorate' => $governorate,
                'packages_count' => rand(10, 25),
                'status' => 'DELIVERED',
                'loaded_at' => now()->subDay()->setHour(8)->addMinutes(rand(10, 30)),
                'delivered_at' => now()->subDay()->setHour(13)->addMinutes(rand(10, 40)),
                'package_ids' => $this->generateFakePackageIds(rand(10, 25)),
                'created_by' => $transitDriver->id
            ]);
        }

        $this->command->info('✅ Système de transit initialisé avec succès !');
        $this->command->info("📦 Tournée créée: {$route->origin_depot} → {$route->destination_depot}");
        $this->command->info("🗳️ {$route->boxes()->count()} boîtes créées pour la tournée");
        $this->command->info("📊 1 tournée historique créée");
    }

    /**
     * Générer des IDs de colis factices
     */
    private function generateFakePackageIds(int $count): array
    {
        $packageIds = [];
        for ($i = 0; $i < $count; $i++) {
            $packageIds[] = rand(1000, 9999);
        }
        return $packageIds;
    }
}