<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FixedCharge;
use App\Models\Vehicle;
use App\Models\DepreciableAsset;

class SupervisorTestDataSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Création des données de test pour Superviseur..." . PHP_EOL;

        // 1. Créer des Charges Fixes
        echo "💰 Création des charges fixes..." . PHP_EOL;
        
        FixedCharge::create([
            'name' => 'Loyer Bureau Principal',
            'description' => 'Loyer mensuel du local commercial principal',
            'amount' => 1500.000,
            'periodicity' => 'MONTHLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        FixedCharge::create([
            'name' => 'Électricité',
            'description' => 'Facture d\'électricité mensuelle',
            'amount' => 200.000,
            'periodicity' => 'MONTHLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        FixedCharge::create([
            'name' => 'Eau',
            'description' => 'Facture d\'eau bimestrielle',
            'amount' => 80.000,
            'periodicity' => 'MONTHLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        FixedCharge::create([
            'name' => 'Internet & Téléphone',
            'description' => 'Abonnement internet et téléphonie fixe',
            'amount' => 120.000,
            'periodicity' => 'MONTHLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        FixedCharge::create([
            'name' => 'Assurance Locale',
            'description' => 'Prime d\'assurance annuelle pour le local',
            'amount' => 1200.000,
            'periodicity' => 'YEARLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        FixedCharge::create([
            'name' => 'Maintenance Système',
            'description' => 'Contrat maintenance système informatique',
            'amount' => 500.000,
            'periodicity' => 'MONTHLY',
            'is_active' => true,
            'created_by' => 1,
        ]);

        echo "   ✅ 6 charges fixes créées" . PHP_EOL;

        // 2. Créer des Véhicules
        echo "🚗 Création des véhicules..." . PHP_EOL;

        Vehicle::create([
            'name' => 'Peugeot Partner',
            'registration_number' => '123TU1234',
            'purchase_price' => 25000.000,
            'max_depreciation_km' => 300000,
            'current_km' => 85000,
            'oil_change_cost' => 50.000,
            'oil_change_interval_km' => 10000,
            'last_oil_change_km' => 80000,
            'spark_plug_cost' => 80.000,
            'spark_plug_interval_km' => 30000,
            'last_spark_plug_change_km' => 60000,
            'tire_unit_cost' => 120.000,
            'tire_change_interval_km' => 50000,
            'last_tire_change_km' => 50000,
            'fuel_price_per_liter' => 2.150,
            'created_by' => 1,
        ]);

        Vehicle::create([
            'name' => 'Renault Kangoo',
            'registration_number' => '456TU5678',
            'purchase_price' => 22000.000,
            'max_depreciation_km' => 300000,
            'current_km' => 125000,
            'oil_change_cost' => 45.000,
            'oil_change_interval_km' => 10000,
            'last_oil_change_km' => 120000,
            'spark_plug_cost' => 75.000,
            'spark_plug_interval_km' => 30000,
            'last_spark_plug_change_km' => 120000,
            'tire_unit_cost' => 110.000,
            'tire_change_interval_km' => 50000,
            'last_tire_change_km' => 100000,
            'fuel_price_per_liter' => 2.150,
            'created_by' => 1,
        ]);

        Vehicle::create([
            'name' => 'Citroen Berlingo',
            'registration_number' => '789TU9012',
            'purchase_price' => 24500.000,
            'max_depreciation_km' => 300000,
            'current_km' => 45000,
            'oil_change_cost' => 48.000,
            'oil_change_interval_km' => 10000,
            'last_oil_change_km' => 40000,
            'spark_plug_cost' => 78.000,
            'spark_plug_interval_km' => 30000,
            'last_spark_plug_change_km' => 30000,
            'tire_unit_cost' => 115.000,
            'tire_change_interval_km' => 50000,
            'last_tire_change_km' => 0,
            'fuel_price_per_liter' => 2.150,
            'created_by' => 1,
        ]);

        echo "   ✅ 3 véhicules créés" . PHP_EOL;

        // 3. Créer quelques Actifs Amortissables
        echo "💻 Création des actifs amortissables..." . PHP_EOL;

        try {
            DepreciableAsset::create([
                'name' => 'Serveur Dell PowerEdge',
                'description' => 'Serveur principal pour l\'application',
                'cost' => 15000.000,
                'useful_life_months' => 60, // 5 ans
                'is_active' => true,
                'created_by' => 1,
            ]);

            DepreciableAsset::create([
                'name' => 'Équipement Bureau (Mobilier)',
                'description' => 'Bureaux, chaises, armoires de rangement',
                'cost' => 8000.000,
                'useful_life_months' => 120, // 10 ans
                'is_active' => true,
                'created_by' => 1,
            ]);

            echo "   ✅ 2 actifs amortissables créés" . PHP_EOL;
        } catch (\Exception $e) {
            echo "   ⚠️ Erreur actifs: " . $e->getMessage() . PHP_EOL;
        }

        echo PHP_EOL . "✅ Seeding terminé avec succès!" . PHP_EOL;
        echo "📊 Récapitulatif:" . PHP_EOL;
        echo "   - Charges fixes: " . FixedCharge::count() . PHP_EOL;
        echo "   - Véhicules: " . Vehicle::count() . PHP_EOL;
        echo "   - Actifs amortissables: " . DepreciableAsset::count() . PHP_EOL;
    }
}
