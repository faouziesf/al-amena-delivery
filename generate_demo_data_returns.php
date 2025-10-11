<?php

/**
 * Générateur de Données de Démonstration - Système de Retours
 *
 * Crée des données réalistes pour démonstration et tests
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\ReturnPackage;
use App\Models\Delegation;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   GÉNÉRATEUR DE DONNÉES DE DÉMONSTRATION - SYSTÈME RETOURS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Vérifier qu'on a les données de base
$client = User::where('role', 'CLIENT')->first();
if (!$client) {
    echo "❌ Erreur: Aucun client trouvé. Créez d'abord un utilisateur CLIENT.\n";
    exit(1);
}

$delegation = Delegation::first();
if (!$delegation) {
    echo "❌ Erreur: Aucune délégation trouvée. Créez d'abord une délégation.\n";
    exit(1);
}

echo "✅ Client trouvé: {$client->name} (ID: {$client->id})\n";
echo "✅ Délégation trouvée: {$delegation->name}\n\n";

// Demander combien de colis créer
echo "Combien de colis de démo voulez-vous créer? (défaut: 10): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
$count = empty($line) ? 10 : (int)$line;
fclose($handle);

if ($count < 1 || $count > 100) {
    echo "❌ Nombre invalide. Utilisez entre 1 et 100.\n";
    exit(1);
}

echo "\n📦 Création de {$count} colis de démonstration...\n";
echo str_repeat("-", 60) . "\n\n";

$created = [
    'AWAITING_RETURN' => 0,
    'RETURN_IN_PROGRESS' => 0,
    'RETURNED_TO_CLIENT' => 0,
    'RETURN_CONFIRMED' => 0,
    'RETURN_ISSUE' => 0,
    'RETURN_PACKAGES' => 0,
];

// Raisons de retour réalistes
$returnReasons = [
    'Destinataire injoignable après 3 tentatives',
    'Adresse incorrecte ou introuvable',
    'Client refuse de payer le COD',
    'Destinataire absent à chaque passage',
    'Numéro de téléphone incorrect',
    'Zone dangereuse - impossible de livrer',
    'Client demande annulation',
    'Colis endommagé avant livraison',
];

// Noms de clients réalistes
$clientNames = [
    'Ahmed Ben Ali', 'Fatma Gharbi', 'Mohamed Trabelsi', 'Leila Mansouri',
    'Karim Boussetta', 'Sana Hamdi', 'Youssef Nejjar', 'Amira Chakroun',
    'Mehdi Jlassi', 'Nadia Bouzid', 'Rami Khelifi', 'Ines Ferchichi',
];

// Villes réalistes
$cities = ['Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabès', 'Ariana', 'Gafsa'];

// Distribution des statuts (pourcentages réalistes)
$statusDistribution = [
    'AWAITING_RETURN' => 30,        // 30%
    'RETURN_IN_PROGRESS' => 25,     // 25%
    'RETURNED_TO_CLIENT' => 20,     // 20%
    'RETURN_CONFIRMED' => 15,       // 15%
    'RETURN_ISSUE' => 10,           // 10%
];

DB::beginTransaction();

try {
    for ($i = 1; $i <= $count; $i++) {
        // Sélectionner un statut aléatoire selon la distribution
        $rand = rand(1, 100);
        $cumulative = 0;
        $selectedStatus = 'AWAITING_RETURN';

        foreach ($statusDistribution as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                $selectedStatus = $status;
                break;
            }
        }

        // Données aléatoires réalistes
        $clientName = $clientNames[array_rand($clientNames)];
        $city = $cities[array_rand($cities)];
        $reason = $returnReasons[array_rand($returnReasons)];
        $cod = rand(50, 500);
        $phone = '2' . rand(0, 9) . rand(100000, 999999);

        // Créer le colis
        $package = Package::create([
            'sender_id' => $client->id,
            'package_code' => 'DEMO-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'tracking_number' => 'TRK-DEMO-' . str_pad($i, 6, '0', STR_PAD_LEFT),
            'status' => $selectedStatus,
            'cod_amount' => $cod,
            'delivery_type' => rand(0, 1) ? 'standard' : 'express',
            'recipient_data' => [
                'name' => $clientName,
                'phone' => $phone,
                'address' => rand(1, 999) . ' Avenue de la République',
                'city' => $city,
            ],
            'delegation_from_id' => $delegation->id,
            'delegation_to_id' => $delegation->id,
            'unavailable_attempts' => 3,
            'return_reason' => $reason,
        ]);

        // Ajouter des dates selon le statut
        switch ($selectedStatus) {
            case 'AWAITING_RETURN':
                $package->update([
                    'awaiting_return_since' => now()->subHours(rand(1, 47)),
                ]);
                break;

            case 'RETURN_IN_PROGRESS':
                $package->update([
                    'awaiting_return_since' => now()->subHours(rand(48, 96)),
                    'return_in_progress_since' => now()->subHours(rand(1, 24)),
                ]);

                // Créer un colis retour
                $returnPackage = ReturnPackage::create([
                    'original_package_id' => $package->id,
                    'return_package_code' => ReturnPackage::generateReturnCode(),
                    'cod' => 0,
                    'status' => 'AT_DEPOT',
                    'sender_info' => ReturnPackage::getCompanyInfo(),
                    'recipient_info' => [
                        'name' => $client->name,
                        'phone' => $client->phone ?? '70123456',
                        'address' => 'Adresse entreprise',
                        'city' => 'Tunis',
                    ],
                    'return_reason' => $reason,
                    'comment' => 'Colis retour généré automatiquement - DEMO',
                    'created_by' => null,
                ]);

                $package->update(['return_package_id' => $returnPackage->id]);
                $created['RETURN_PACKAGES']++;
                break;

            case 'RETURNED_TO_CLIENT':
                $package->update([
                    'awaiting_return_since' => now()->subHours(rand(48, 96)),
                    'return_in_progress_since' => now()->subHours(rand(24, 48)),
                    'returned_to_client_at' => now()->subHours(rand(1, 47)),
                ]);

                // Créer un colis retour livré
                $returnPackage = ReturnPackage::create([
                    'original_package_id' => $package->id,
                    'return_package_code' => ReturnPackage::generateReturnCode(),
                    'cod' => 0,
                    'status' => 'DELIVERED',
                    'sender_info' => ReturnPackage::getCompanyInfo(),
                    'recipient_info' => [
                        'name' => $client->name,
                        'phone' => $client->phone ?? '70123456',
                        'address' => 'Adresse entreprise',
                        'city' => 'Tunis',
                    ],
                    'return_reason' => $reason,
                    'comment' => 'Colis retour généré automatiquement - DEMO',
                    'created_by' => null,
                    'printed_at' => now()->subHours(rand(24, 48)),
                    'delivered_at' => now()->subHours(rand(1, 23)),
                ]);

                $package->update(['return_package_id' => $returnPackage->id]);
                $created['RETURN_PACKAGES']++;
                break;

            case 'RETURN_CONFIRMED':
            case 'RETURN_ISSUE':
                $package->update([
                    'awaiting_return_since' => now()->subHours(rand(48, 120)),
                    'return_in_progress_since' => now()->subHours(rand(24, 96)),
                    'returned_to_client_at' => now()->subHours(rand(48, 72)),
                ]);

                // Créer un colis retour livré
                $returnPackage = ReturnPackage::create([
                    'original_package_id' => $package->id,
                    'return_package_code' => ReturnPackage::generateReturnCode(),
                    'cod' => 0,
                    'status' => 'DELIVERED',
                    'sender_info' => ReturnPackage::getCompanyInfo(),
                    'recipient_info' => [
                        'name' => $client->name,
                        'phone' => $client->phone ?? '70123456',
                        'address' => 'Adresse entreprise',
                        'city' => 'Tunis',
                    ],
                    'return_reason' => $reason,
                    'comment' => 'Colis retour généré automatiquement - DEMO',
                    'created_by' => null,
                    'printed_at' => now()->subHours(rand(48, 96)),
                    'delivered_at' => now()->subHours(rand(48, 72)),
                ]);

                $package->update(['return_package_id' => $returnPackage->id]);
                $created['RETURN_PACKAGES']++;
                break;
        }

        $created[$selectedStatus]++;

        // Afficher la progression
        if ($i % 10 == 0 || $i == $count) {
            echo "  ✅ {$i}/{$count} colis créés...\n";
        }
    }

    DB::commit();

    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "   RÉSUMÉ DE LA CRÉATION\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    echo "📦 Colis créés par statut:\n";
    echo "   • AWAITING_RETURN:      {$created['AWAITING_RETURN']}\n";
    echo "   • RETURN_IN_PROGRESS:   {$created['RETURN_IN_PROGRESS']}\n";
    echo "   • RETURNED_TO_CLIENT:   {$created['RETURNED_TO_CLIENT']}\n";
    echo "   • RETURN_CONFIRMED:     {$created['RETURN_CONFIRMED']}\n";
    echo "   • RETURN_ISSUE:         {$created['RETURN_ISSUE']}\n";
    echo "\n";
    echo "📮 Colis retours créés:    {$created['RETURN_PACKAGES']}\n";
    echo "\n";
    echo "✅ Total: {$count} colis de démonstration créés avec succès!\n\n";

    echo "🌐 Accès aux interfaces:\n";
    echo "   • Dépôt:     http://localhost:8000/depot/returns\n";
    echo "   • Client:    http://localhost:8000/client/returns\n";
    echo "   • Commercial: http://localhost:8000/commercial/packages/{id}\n";
    echo "\n";

    echo "🧹 Pour nettoyer les données de démo plus tard:\n";
    echo "   php artisan tinker\n";
    echo "   Package::where('package_code', 'LIKE', 'DEMO-%')->delete();\n";
    echo "   ReturnPackage::where('comment', 'LIKE', '%DEMO%')->delete();\n";
    echo "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Erreur lors de la création: {$e->getMessage()}\n";
    echo "   Aucune donnée n'a été créée.\n\n";
    exit(1);
}

echo "✨ Génération terminée!\n\n";
