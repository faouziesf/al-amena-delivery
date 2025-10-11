<?php

/**
 * Script de Test Complet - Système de Retours
 *
 * Ce script teste l'ensemble du workflow:
 * 1. Migrations et modèles
 * 2. Création de données de test
 * 3. Jobs automatiques (48h)
 * 4. Workflow complet du retour
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Package;
use App\Models\ReturnPackage;
use App\Models\Delegation;
use App\Jobs\ProcessAwaitingReturnsJob;
use App\Jobs\ProcessReturnedPackagesJob;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   TEST COMPLET - SYSTÈME DE RETOURS AL-AMENA DELIVERY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// ÉTAPE 1: Vérification des migrations
// ============================================================================
echo "📋 ÉTAPE 1: Vérification des migrations...\n";
echo str_repeat("-", 60) . "\n";

try {
    // Vérifier la table return_packages
    $returnPackagesExists = DB::table('return_packages')->count() >= 0;
    echo "✅ Table 'return_packages' existe\n";

    // Vérifier les colonnes du package
    $packageColumns = DB::select("PRAGMA table_info(packages)");
    $returnFieldsExists = false;
    foreach ($packageColumns as $col) {
        if ($col->name === 'return_package_id') {
            $returnFieldsExists = true;
            break;
        }
    }

    if ($returnFieldsExists) {
        echo "✅ Colonnes de retour ajoutées à la table 'packages'\n";
    } else {
        echo "❌ Colonnes de retour manquantes dans 'packages'\n";
    }

} catch (\Exception $e) {
    echo "❌ Erreur migrations: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// ============================================================================
// ÉTAPE 2: Création de données de test
// ============================================================================
echo "🔧 ÉTAPE 2: Création de données de test...\n";
echo str_repeat("-", 60) . "\n";

// Créer un client de test
$client = User::where('role', 'CLIENT')->first();
if (!$client) {
    $client = User::create([
        'name' => 'Client Test Retours',
        'email' => 'client_retour_test_' . uniqid() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'CLIENT',
        'account_status' => 'ACTIVE',
        'phone' => '50123456',
    ]);
    echo "✅ Client de test créé: {$client->name} (ID: {$client->id})\n";
} else {
    echo "✅ Utilisation du client existant: {$client->name} (ID: {$client->id})\n";
}

// Créer délégation si nécessaire
$delegation = Delegation::first();
if (!$delegation) {
    $delegation = Delegation::create([
        'name' => 'Tunis Centre',
        'code' => 'TUN-CTR',
        'is_active' => true,
    ]);
    echo "✅ Délégation créée: {$delegation->name}\n";
} else {
    echo "✅ Utilisation délégation: {$delegation->name}\n";
}

echo "\n";

// ============================================================================
// ÉTAPE 3: Test du workflow complet
// ============================================================================
echo "🔄 ÉTAPE 3: Test du workflow complet...\n";
echo str_repeat("-", 60) . "\n";

// 3.1 Créer un colis
echo "\n3.1 - Création d'un colis...\n";
$package = Package::create([
    'sender_id' => $client->id,
    'package_code' => 'TEST-RET-' . strtoupper(substr(md5(uniqid()), 0, 6)),
    'tracking_number' => 'TRK-' . time(),
    'status' => 'AT_DEPOT',
    'cod_amount' => 150.00,
    'delivery_type' => 'standard',
    'recipient_data' => [
        'name' => 'Destinataire Test',
        'phone' => '20123456',
        'address' => '123 Rue Test',
        'city' => 'Tunis',
    ],
    'delegation_from_id' => $delegation->id,
    'delegation_to_id' => $delegation->id,
    'unavailable_attempts' => 0,
]);

echo "   ✅ Colis créé: {$package->package_code} (Statut: {$package->status})\n";

// 3.2 Simuler 3 tentatives échouées
echo "\n3.2 - Simulation de 3 tentatives de livraison échouées...\n";
for ($i = 1; $i <= 3; $i++) {
    $package->update([
        'status' => 'UNAVAILABLE',
        'unavailable_attempts' => $i,
    ]);
    echo "   ⏳ Tentative {$i}/3 échouée\n";
}

// Passer en AWAITING_RETURN
$package->update([
    'status' => 'AWAITING_RETURN',
    'awaiting_return_since' => now()->subHours(50), // 50h dans le passé pour test
    'return_reason' => 'Destinataire injoignable après 3 tentatives',
]);

echo "   ✅ Colis passé en AWAITING_RETURN (depuis 50h)\n";

// 3.3 Test du job ProcessAwaitingReturnsJob
echo "\n3.3 - Test du job ProcessAwaitingReturnsJob (48h auto)...\n";
$job1 = new ProcessAwaitingReturnsJob();
$job1->handle();

$package->refresh();
echo "   ✅ Job exécuté - Statut actuel: {$package->status}\n";

if ($package->status === 'RETURN_IN_PROGRESS') {
    echo "   ✅ SUCCÈS: Le colis est passé en RETURN_IN_PROGRESS automatiquement\n";
} else {
    echo "   ⚠️  Le colis est resté en {$package->status}\n";
}

// 3.4 Simuler scan dépôt et création colis retour
echo "\n3.4 - Simulation scan dépôt et création colis retour...\n";

if ($package->status === 'RETURN_IN_PROGRESS') {
    $returnPackage = ReturnPackage::create([
        'original_package_id' => $package->id,
        'return_package_code' => ReturnPackage::generateReturnCode(),
        'cod' => 0,
        'status' => 'AT_DEPOT',
        'sender_info' => ReturnPackage::getCompanyInfo(),
        'recipient_info' => [
            'name' => $client->name,
            'phone' => $client->phone,
            'address' => $client->address ?? 'Adresse client',
            'city' => 'Tunis',
        ],
        'return_reason' => $package->return_reason,
        'comment' => 'Colis retour créé par test automatique',
        'created_by' => null,
    ]);

    $package->update([
        'return_package_id' => $returnPackage->id,
    ]);

    echo "   ✅ Colis retour créé: {$returnPackage->return_package_code}\n";

    // Simuler livraison du colis retour au client
    echo "\n3.5 - Simulation livraison du colis retour...\n";
    $returnPackage->markAsDelivered();
    $package->refresh();

    echo "   ✅ Colis retour livré - Statut package: {$package->status}\n";
    echo "   ✅ Date retour client: {$package->returned_to_client_at}\n";

    // Modifier la date pour test auto-confirmation
    $package->update([
        'returned_to_client_at' => now()->subHours(50), // 50h dans le passé
    ]);

    // 3.6 Test du job ProcessReturnedPackagesJob
    echo "\n3.6 - Test du job ProcessReturnedPackagesJob (auto-confirmation 48h)...\n";
    $job2 = new ProcessReturnedPackagesJob();
    $job2->handle();

    $package->refresh();
    echo "   ✅ Job exécuté - Statut final: {$package->status}\n";

    if ($package->status === 'RETURN_CONFIRMED') {
        echo "   ✅ SUCCÈS: Le retour a été auto-confirmé après 48h\n";
    } else {
        echo "   ⚠️  Le colis est en {$package->status}\n";
    }
}

echo "\n";

// ============================================================================
// ÉTAPE 4: Résumé des tests
// ============================================================================
echo "📊 ÉTAPE 4: Résumé des résultats...\n";
echo str_repeat("-", 60) . "\n\n";

$package->refresh();
$returnPackage->refresh();

echo "📦 COLIS ORIGINAL:\n";
echo "   - Code: {$package->package_code}\n";
echo "   - Statut final: {$package->status}\n";
echo "   - Tentatives: {$package->unavailable_attempts}\n";
echo "   - En attente retour depuis: " . ($package->awaiting_return_since ? $package->awaiting_return_since->diffForHumans() : 'N/A') . "\n";
echo "   - Retourné au client: " . ($package->returned_to_client_at ? $package->returned_to_client_at->diffForHumans() : 'N/A') . "\n\n";

echo "📮 COLIS RETOUR:\n";
echo "   - Code: {$returnPackage->return_package_code}\n";
echo "   - Statut: {$returnPackage->status}\n";
echo "   - Imprimé: " . ($returnPackage->printed_at ? 'Oui' : 'Non') . "\n";
echo "   - Livré: " . ($returnPackage->delivered_at ? 'Oui - ' . $returnPackage->delivered_at->diffForHumans() : 'Non') . "\n\n";

// ============================================================================
// ÉTAPE 5: Tests des méthodes du modèle
// ============================================================================
echo "🧪 ÉTAPE 5: Tests des méthodes du modèle ReturnPackage...\n";
echo str_repeat("-", 60) . "\n\n";

echo "   - isPrinted(): " . ($returnPackage->isPrinted() ? 'true' : 'false') . "\n";
echo "   - Génération code unique: " . ReturnPackage::generateReturnCode() . "\n";
echo "   - Relation originalPackage: " . ($returnPackage->originalPackage ? '✅ OK' : '❌ Erreur') . "\n";

echo "\n";

// ============================================================================
// RÉSULTAT FINAL
// ============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "   RÉSULTAT FINAL DU TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$allTestsPassed = (
    $returnPackagesExists &&
    $returnFieldsExists &&
    $package->status === 'RETURN_CONFIRMED' &&
    $returnPackage->status === 'DELIVERED'
);

if ($allTestsPassed) {
    echo "✅✅✅ TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS ! ✅✅✅\n\n";
    echo "Le système de retours fonctionne correctement:\n";
    echo "  1. ✅ Migrations OK\n";
    echo "  2. ✅ Workflow AWAITING_RETURN → RETURN_IN_PROGRESS (48h)\n";
    echo "  3. ✅ Création colis retour OK\n";
    echo "  4. ✅ Livraison retour → RETURNED_TO_CLIENT\n";
    echo "  5. ✅ Auto-confirmation après 48h → RETURN_CONFIRMED\n";
} else {
    echo "⚠️  CERTAINS TESTS ONT ÉCHOUÉ\n\n";
    echo "Vérifiez les détails ci-dessus pour identifier les problèmes.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   Voulez-vous nettoyer les données de test ? (O/N)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim(strtoupper($line)) === 'O') {
    echo "\n🧹 Nettoyage des données de test...\n";

    if (isset($returnPackage)) {
        $returnPackage->delete();
        echo "   ✅ Colis retour supprimé\n";
    }

    if (isset($package)) {
        $package->delete();
        echo "   ✅ Colis original supprimé\n";
    }

    echo "   ✅ Nettoyage terminé\n";
} else {
    echo "\n📝 Les données de test ont été conservées pour inspection.\n";
}

fclose($handle);

echo "\n✨ Test terminé !\n\n";
