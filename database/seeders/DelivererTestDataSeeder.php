<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Package;
use App\Models\Delegation;
use Illuminate\Support\Facades\DB;

class DelivererTestDataSeeder extends Seeder
{
    public function run()
    {
        echo "🔄 Création de données de test pour les livreurs...\n";

        // Récupérer des livreurs existants
        $deliverers = User::where('role', 'DELIVERER')->where('account_status', 'ACTIVE')->get();

        if ($deliverers->isEmpty()) {
            echo "❌ Aucun livreur actif trouvé. Veuillez d'abord exécuter le seeder principal.\n";
            return;
        }

        // Récupérer des clients existants
        $clients = User::where('role', 'CLIENT')->get();
        if ($clients->isEmpty()) {
            echo "❌ Aucun client trouvé. Veuillez d'abord exécuter le seeder principal.\n";
            return;
        }

        // Récupérer des délégations existantes
        $delegations = Delegation::all();
        if ($delegations->isEmpty()) {
            echo "❌ Aucune délégation trouvée. Veuillez d'abord exécuter le seeder principal.\n";
            return;
        }

        $packagesCreated = 0;

        foreach ($deliverers as $deliverer) {
            echo "📦 Création de packages pour {$deliverer->name}...\n";

            // Créer des packages avec différents statuts pour ce livreur
            $statusData = [
                // Packages prêts pour pickup (acceptés)
                ['status' => 'ACCEPTED', 'count' => 3],
                // Packages prêts pour livraison (collectés)
                ['status' => 'PICKED_UP', 'count' => 5],
                // Packages en tentative de livraison (indisponibles)
                ['status' => 'UNAVAILABLE', 'count' => 3],
                // Packages à retourner (vérifiés après 3 tentatives)
                ['status' => 'VERIFIED', 'count' => 2],
            ];

            foreach ($statusData as $data) {
                for ($i = 1; $i <= $data['count']; $i++) {
                    $client = $clients->random();
                    $delegationFrom = $delegations->random();
                    $delegationTo = $delegations->random();

                    $packageCode = 'DEL_' . $deliverer->id . '_' . strtoupper($data['status']) . '_' . str_pad($i, 3, '0', STR_PAD_LEFT) . '_' . time() . rand(100, 999);

                    $packageData = [
                        'package_code' => $packageCode,
                        'sender_id' => $client->id,
                        'sender_data' => json_encode([
                            'name' => $client->name,
                            'phone' => $client->phone,
                            'address' => $client->address
                        ]),
                        'delegation_from' => $delegationFrom->id,
                        'pickup_address' => "Adresse de collecte pour {$packageCode}",
                        'pickup_phone' => "+216 20 " . rand(100000, 999999),
                        'recipient_data' => json_encode([
                            'name' => "Destinataire {$packageCode}",
                            'phone' => "+216 9" . rand(1, 9) . " " . rand(100, 999) . " " . rand(100, 999),
                            'address' => "Adresse destinataire {$packageCode}",
                        ]),
                        'delegation_to' => $delegationTo->id,
                        'content_description' => ['Vêtements', 'Électronique', 'Cosmétiques', 'Livres', 'Accessoires'][array_rand(['Vêtements', 'Électronique', 'Cosmétiques', 'Livres', 'Accessoires'])],
                        'cod_amount' => rand(20, 300),
                        'package_weight' => rand(0.5, 3.0),
                        'package_value' => rand(25, 400),
                        'delivery_fee' => rand(5, 15),
                        'return_fee' => rand(3, 10),
                        'is_fragile' => rand(0, 1),
                        'requires_signature' => rand(0, 1),
                        'status' => $data['status'],
                        'assigned_deliverer_id' => $deliverer->id,
                        'assigned_at' => now()->subHours(rand(1, 72)),
                        'created_at' => now()->subDays(rand(1, 7)),
                        'updated_at' => now(),
                    ];

                    // Ajuster les données selon le statut
                    switch ($data['status']) {
                        case 'UNAVAILABLE':
                            $packageData['delivery_attempts'] = rand(1, 2);
                            break;

                        case 'VERIFIED':
                            $packageData['delivery_attempts'] = 3;
                            break;
                    }

                    Package::create($packageData);
                    $packagesCreated++;
                }
            }
        }

        echo "✅ {$packagesCreated} packages de test créés pour les livreurs!\n";
        echo "📊 Répartition par livreur:\n";
        foreach ($deliverers as $deliverer) {
            $counts = [
                'ACCEPTED' => Package::where('assigned_deliverer_id', $deliverer->id)->where('status', 'ACCEPTED')->count(),
                'PICKED_UP' => Package::where('assigned_deliverer_id', $deliverer->id)->where('status', 'PICKED_UP')->count(),
                'UNAVAILABLE' => Package::where('assigned_deliverer_id', $deliverer->id)->where('status', 'UNAVAILABLE')->count(),
                'VERIFIED' => Package::where('assigned_deliverer_id', $deliverer->id)->where('status', 'VERIFIED')->count(),
            ];
            echo "   - {$deliverer->name}: " . json_encode($counts) . "\n";
        }
    }
}