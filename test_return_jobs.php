<?php

/**
 * Script de test pour les Jobs de retours automatisés
 *
 * Usage: php test_return_jobs.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;
use App\Models\User;
use App\Jobs\ProcessAwaitingReturnsJob;
use App\Jobs\ProcessReturnedPackagesJob;
use Illuminate\Support\Facades\DB;

echo "🧪 TEST DES JOBS DE RETOURS AUTOMATISÉS\n";
echo str_repeat("=", 60) . "\n\n";

// ========================================
// PARTIE 1 : CRÉER DONNÉES DE TEST
// ========================================

echo "📦 PARTIE 1 : Création des données de test\n";
echo str_repeat("-", 60) . "\n";

// Trouver un client existant
$client = User::where('role', 'CLIENT')->first();

if (!$client) {
    echo "❌ Aucun client trouvé. Veuillez créer un client d'abord.\n";
    exit(1);
}

echo "✅ Client trouvé: {$client->name} (ID: {$client->id})\n";

// Créer un colis de test AWAITING_RETURN (expiré - 49h ago)
$testPackage1 = Package::create([
    'package_code' => 'TEST-AWAIT-' . time(),
    'sender_id' => $client->id,
    'status' => 'AWAITING_RETURN',
    'cod_amount' => 100.000,
    'sender_info' => json_encode([
        'name' => $client->name,
        'phone' => $client->phone ?? '12345678',
        'address' => 'Test Address',
        'city' => 'Tunis',
        'postal_code' => '1000'
    ]),
    'recipient_info' => json_encode([
        'name' => 'Client Test',
        'phone' => '98765432',
        'address' => 'Recipient Address',
        'city' => 'Sfax',
        'postal_code' => '3000'
    ]),
    'unavailable_attempts' => 3,
    'return_reason' => 'Client indisponible après 3 tentatives',
    'awaiting_return_since' => now()->subHours(49), // 49h ago (expiré)
]);

echo "✅ Colis 1 créé: {$testPackage1->package_code}\n";
echo "   → Statut: AWAITING_RETURN\n";
echo "   → Depuis: " . $testPackage1->awaiting_return_since->diffForHumans() . "\n";
echo "   → Devrait passer en RETURN_IN_PROGRESS\n\n";

// Créer un colis de test AWAITING_RETURN (NON expiré - 10h ago)
$testPackage2 = Package::create([
    'package_code' => 'TEST-AWAIT-RECENT-' . time(),
    'sender_id' => $client->id,
    'status' => 'AWAITING_RETURN',
    'cod_amount' => 150.000,
    'sender_info' => json_encode([
        'name' => $client->name,
        'phone' => $client->phone ?? '12345678',
        'address' => 'Test Address',
        'city' => 'Tunis',
        'postal_code' => '1000'
    ]),
    'recipient_info' => json_encode([
        'name' => 'Client Test 2',
        'phone' => '11223344',
        'address' => 'Recipient Address 2',
        'city' => 'Sousse',
        'postal_code' => '4000'
    ]),
    'unavailable_attempts' => 2,
    'return_reason' => 'Client indisponible - tentative 2',
    'awaiting_return_since' => now()->subHours(10), // 10h ago (NON expiré)
]);

echo "✅ Colis 2 créé: {$testPackage2->package_code}\n";
echo "   → Statut: AWAITING_RETURN\n";
echo "   → Depuis: " . $testPackage2->awaiting_return_since->diffForHumans() . "\n";
echo "   → NE devrait PAS changer (pas expiré)\n\n";

// Créer un colis de test RETURNED_TO_CLIENT (expiré - 50h ago)
$testPackage3 = Package::create([
    'package_code' => 'TEST-RETURNED-' . time(),
    'sender_id' => $client->id,
    'status' => 'RETURNED_TO_CLIENT',
    'cod_amount' => 200.000,
    'sender_info' => json_encode([
        'name' => $client->name,
        'phone' => $client->phone ?? '12345678',
        'address' => 'Test Address',
        'city' => 'Tunis',
        'postal_code' => '1000'
    ]),
    'recipient_info' => json_encode([
        'name' => 'Client Test 3',
        'phone' => '55667788',
        'address' => 'Recipient Address 3',
        'city' => 'Bizerte',
        'postal_code' => '7000'
    ]),
    'return_reason' => 'Refusé par le client',
    'returned_to_client_at' => now()->subHours(50), // 50h ago (expiré)
]);

echo "✅ Colis 3 créé: {$testPackage3->package_code}\n";
echo "   → Statut: RETURNED_TO_CLIENT\n";
echo "   → Depuis: " . $testPackage3->returned_to_client_at->diffForHumans() . "\n";
echo "   → Devrait passer en RETURN_CONFIRMED\n\n";

// ========================================
// PARTIE 2 : TESTER ProcessAwaitingReturnsJob
// ========================================

echo "\n📋 PARTIE 2 : Test ProcessAwaitingReturnsJob\n";
echo str_repeat("-", 60) . "\n";

echo "🚀 Exécution du job...\n";
$job1 = new ProcessAwaitingReturnsJob();
$job1->handle();

// Vérifier les résultats
$testPackage1->refresh();
$testPackage2->refresh();

echo "\n📊 Résultats:\n";
echo "   Colis 1: {$testPackage1->package_code}\n";
echo "   → Statut avant: AWAITING_RETURN\n";
echo "   → Statut après: {$testPackage1->status}\n";
echo "   → " . ($testPackage1->status === 'RETURN_IN_PROGRESS' ? '✅ SUCCÈS' : '❌ ÉCHEC') . "\n\n";

echo "   Colis 2: {$testPackage2->package_code}\n";
echo "   → Statut avant: AWAITING_RETURN\n";
echo "   → Statut après: {$testPackage2->status}\n";
echo "   → " . ($testPackage2->status === 'AWAITING_RETURN' ? '✅ SUCCÈS (non modifié)' : '❌ ÉCHEC (ne devrait pas changer)') . "\n\n";

// ========================================
// PARTIE 3 : TESTER ProcessReturnedPackagesJob
// ========================================

echo "\n📋 PARTIE 3 : Test ProcessReturnedPackagesJob\n";
echo str_repeat("-", 60) . "\n";

echo "🚀 Exécution du job...\n";
$job2 = new ProcessReturnedPackagesJob();
$job2->handle();

// Vérifier les résultats
$testPackage3->refresh();

echo "\n📊 Résultats:\n";
echo "   Colis 3: {$testPackage3->package_code}\n";
echo "   → Statut avant: RETURNED_TO_CLIENT\n";
echo "   → Statut après: {$testPackage3->status}\n";
echo "   → " . ($testPackage3->status === 'RETURN_CONFIRMED' ? '✅ SUCCÈS' : '❌ ÉCHEC') . "\n\n";

// ========================================
// PARTIE 4 : NETTOYAGE
// ========================================

echo "\n🧹 PARTIE 4 : Nettoyage\n";
echo str_repeat("-", 60) . "\n";

$delete = readline("Supprimer les colis de test ? (o/n): ");

if (strtolower($delete) === 'o') {
    $testPackage1->forceDelete();
    $testPackage2->forceDelete();
    $testPackage3->forceDelete();
    echo "✅ Colis de test supprimés\n";
} else {
    echo "ℹ️  Colis de test conservés:\n";
    echo "   - {$testPackage1->package_code} (statut: {$testPackage1->status})\n";
    echo "   - {$testPackage2->package_code} (statut: {$testPackage2->status})\n";
    echo "   - {$testPackage3->package_code} (statut: {$testPackage3->status})\n";
}

// ========================================
// RÉSUMÉ FINAL
// ========================================

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ TESTS TERMINÉS\n";
echo str_repeat("=", 60) . "\n";

echo "\n📝 Prochaines étapes:\n";
echo "1. Vérifier les logs: tail -f storage/logs/laravel.log\n";
echo "2. Vérifier le scheduler: php artisan schedule:list\n";
echo "3. Tester en production: php artisan schedule:work\n";
echo "\n";
