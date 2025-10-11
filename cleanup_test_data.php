<?php

/**
 * Script de Nettoyage - Données de Test et Démonstration
 *
 * Supprime les données créées pour les tests et démos
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;
use App\Models\ReturnPackage;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   NETTOYAGE DES DONNÉES DE TEST ET DÉMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Compter les données à supprimer
$demoPackages = Package::where('package_code', 'LIKE', 'DEMO-%')->count();
$testPackages = Package::where('package_code', 'LIKE', 'TEST-%')->count();
$demoReturnPackages = ReturnPackage::where('comment', 'LIKE', '%DEMO%')->count();
$testReturnPackages = ReturnPackage::where('comment', 'LIKE', '%test%')->count();

echo "📊 Données détectées:\n";
echo str_repeat("-", 60) . "\n";
echo "  • Colis DEMO-*:         {$demoPackages}\n";
echo "  • Colis TEST-*:         {$testPackages}\n";
echo "  • Retours DEMO:         {$demoReturnPackages}\n";
echo "  • Retours Test:         {$testReturnPackages}\n";
echo "\n";

$totalToDelete = $demoPackages + $testPackages + $demoReturnPackages + $testReturnPackages;

if ($totalToDelete === 0) {
    echo "✅ Aucune donnée de test à nettoyer.\n";
    echo "   La base de données est propre!\n\n";
    exit(0);
}

echo "⚠️  ATTENTION: Vous allez supprimer {$totalToDelete} enregistrements!\n";
echo "\n";
echo "Options de nettoyage:\n";
echo "  1. Supprimer UNIQUEMENT les données DEMO\n";
echo "  2. Supprimer UNIQUEMENT les données TEST\n";
echo "  3. Supprimer TOUT (DEMO + TEST)\n";
echo "  4. Annuler\n";
echo "\n";
echo "Votre choix (1-4): ";

$handle = fopen("php://stdin", "r");
$choice = trim(fgets($handle));
fclose($handle);

if ($choice === '4' || empty($choice)) {
    echo "\n❌ Nettoyage annulé.\n\n";
    exit(0);
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   SUPPRESSION EN COURS...\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$deleted = [
    'packages' => 0,
    'return_packages' => 0,
];

DB::beginTransaction();

try {
    switch ($choice) {
        case '1': // DEMO uniquement
            echo "🗑️  Suppression des données DEMO...\n";

            // Supprimer les colis retours DEMO
            $deletedReturnPackages = ReturnPackage::where('comment', 'LIKE', '%DEMO%')->delete();
            $deleted['return_packages'] += $deletedReturnPackages;
            echo "  ✅ {$deletedReturnPackages} colis retours DEMO supprimés\n";

            // Supprimer les colis DEMO
            $deletedPackages = Package::where('package_code', 'LIKE', 'DEMO-%')->delete();
            $deleted['packages'] += $deletedPackages;
            echo "  ✅ {$deletedPackages} colis DEMO supprimés\n";
            break;

        case '2': // TEST uniquement
            echo "🗑️  Suppression des données TEST...\n";

            // Supprimer les colis retours TEST
            $deletedReturnPackages = ReturnPackage::where('comment', 'LIKE', '%test%')->delete();
            $deleted['return_packages'] += $deletedReturnPackages;
            echo "  ✅ {$deletedReturnPackages} colis retours TEST supprimés\n";

            // Supprimer les colis TEST
            $deletedPackages = Package::where('package_code', 'LIKE', 'TEST-%')->delete();
            $deleted['packages'] += $deletedPackages;
            echo "  ✅ {$deletedPackages} colis TEST supprimés\n";
            break;

        case '3': // TOUT
            echo "🗑️  Suppression de TOUTES les données de test...\n";

            // Supprimer tous les colis retours de test
            $deletedReturnPackages = ReturnPackage::where(function($query) {
                $query->where('comment', 'LIKE', '%DEMO%')
                      ->orWhere('comment', 'LIKE', '%test%');
            })->delete();
            $deleted['return_packages'] += $deletedReturnPackages;
            echo "  ✅ {$deletedReturnPackages} colis retours supprimés\n";

            // Supprimer tous les colis de test
            $deletedPackages = Package::where(function($query) {
                $query->where('package_code', 'LIKE', 'DEMO-%')
                      ->orWhere('package_code', 'LIKE', 'TEST-%');
            })->delete();
            $deleted['packages'] += $deletedPackages;
            echo "  ✅ {$deletedPackages} colis supprimés\n";
            break;

        default:
            echo "❌ Choix invalide.\n\n";
            DB::rollBack();
            exit(1);
    }

    DB::commit();

    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "   NETTOYAGE TERMINÉ AVEC SUCCÈS\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    echo "📊 Résumé:\n";
    echo "  • Colis supprimés:         {$deleted['packages']}\n";
    echo "  • Colis retours supprimés: {$deleted['return_packages']}\n";
    echo "  • Total:                   " . array_sum($deleted) . "\n";
    echo "\n";

    // Vérifier qu'il ne reste rien
    $remainingDemo = Package::where('package_code', 'LIKE', 'DEMO-%')->count();
    $remainingTest = Package::where('package_code', 'LIKE', 'TEST-%')->count();

    if ($remainingDemo + $remainingTest > 0) {
        echo "⚠️  Il reste encore:\n";
        if ($remainingDemo > 0) echo "  • {$remainingDemo} colis DEMO\n";
        if ($remainingTest > 0) echo "  • {$remainingTest} colis TEST\n";
    } else {
        echo "✅ Base de données nettoyée!\n";
    }

    echo "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Erreur lors du nettoyage: {$e->getMessage()}\n";
    echo "   Aucune donnée n'a été supprimée.\n\n";
    exit(1);
}

echo "✨ Nettoyage terminé!\n\n";
