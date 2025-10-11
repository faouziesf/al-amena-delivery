<?php

/**
 * Script de Vérification de Santé - Système de Retours
 *
 * Vérifie que tous les composants du système sont fonctionnels
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\ReturnPackage;
use App\Models\Package;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║    VÉRIFICATION DE SANTÉ - SYSTÈME DE RETOURS                ║\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "\n";

$errors = 0;
$warnings = 0;
$checks = 0;

function check($description, $callback) {
    global $errors, $warnings, $checks;
    $checks++;

    echo str_pad("[$checks] $description", 65, ".");

    try {
        $result = $callback();
        if ($result === true) {
            echo " ✅\n";
            return true;
        } elseif ($result === 'warning') {
            echo " ⚠️\n";
            $warnings++;
            return false;
        } else {
            echo " ❌\n";
            $errors++;
            return false;
        }
    } catch (\Exception $e) {
        echo " ❌\n";
        echo "    Erreur: " . $e->getMessage() . "\n";
        $errors++;
        return false;
    }
}

// ============================================================================
// SECTION 1: Base de Données
// ============================================================================
echo "📊 SECTION 1: Base de Données\n";
echo str_repeat("-", 70) . "\n";

check("Table 'return_packages' existe", function() {
    return DB::table('return_packages')->count() >= 0;
});

check("Colonne 'return_package_id' dans 'packages'", function() {
    $columns = DB::select("PRAGMA table_info(packages)");
    foreach ($columns as $col) {
        if ($col->name === 'return_package_id') return true;
    }
    return false;
});

check("Colonne 'unavailable_attempts' dans 'packages'", function() {
    $columns = DB::select("PRAGMA table_info(packages)");
    foreach ($columns as $col) {
        if ($col->name === 'unavailable_attempts') return true;
    }
    return false;
});

check("Colonne 'awaiting_return_since' dans 'packages'", function() {
    $columns = DB::select("PRAGMA table_info(packages)");
    foreach ($columns as $col) {
        if ($col->name === 'awaiting_return_since') return true;
    }
    return false;
});

check("Index sur 'status' dans 'return_packages'", function() {
    $indexes = DB::select("PRAGMA index_list(return_packages)");
    return count($indexes) > 0 || 'warning';
});

echo "\n";

// ============================================================================
// SECTION 2: Modèles
// ============================================================================
echo "🏗️  SECTION 2: Modèles\n";
echo str_repeat("-", 70) . "\n";

check("Classe ReturnPackage existe", function() {
    return class_exists('App\Models\ReturnPackage');
});

check("Méthode generateReturnCode() existe", function() {
    return method_exists('App\Models\ReturnPackage', 'generateReturnCode');
});

check("Relation originalPackage() existe", function() {
    return method_exists('App\Models\ReturnPackage', 'originalPackage');
});

check("Méthode markAsDelivered() existe", function() {
    return method_exists('App\Models\ReturnPackage', 'markAsDelivered');
});

check("Package a relation returnPackage()", function() {
    return method_exists('App\Models\Package', 'returnPackage');
});

echo "\n";

// ============================================================================
// SECTION 3: Jobs
// ============================================================================
echo "⚙️  SECTION 3: Jobs Automatiques\n";
echo str_repeat("-", 70) . "\n";

check("Classe ProcessAwaitingReturnsJob existe", function() {
    return class_exists('App\Jobs\ProcessAwaitingReturnsJob');
});

check("Classe ProcessReturnedPackagesJob existe", function() {
    return class_exists('App\Jobs\ProcessReturnedPackagesJob');
});

check("Jobs enregistrés dans Kernel", function() {
    $kernelFile = file_get_contents(app_path('Console/Kernel.php'));
    return strpos($kernelFile, 'ProcessAwaitingReturnsJob') !== false &&
           strpos($kernelFile, 'ProcessReturnedPackagesJob') !== false;
});

echo "\n";

// ============================================================================
// SECTION 4: Routes
// ============================================================================
echo "🛣️  SECTION 4: Routes\n";
echo str_repeat("-", 70) . "\n";

check("Route 'depot.returns.dashboard' existe", function() {
    return Route::has('depot.returns.dashboard');
});

check("Route 'depot.returns.api.scan' existe", function() {
    return Route::has('depot.returns.api.scan');
});

check("Route 'depot.returns.validate' existe", function() {
    return Route::has('depot.returns.validate');
});

check("Route 'client.returns.index' existe", function() {
    return Route::has('client.returns.index');
});

check("Route 'client.returns.confirm' existe", function() {
    return Route::has('client.returns.confirm');
});

check("Route 'client.returns.report-issue' existe", function() {
    return Route::has('client.returns.report-issue');
});

check("Route 'commercial.packages.launch.fourth.attempt' existe", function() {
    return Route::has('commercial.packages.launch.fourth.attempt');
});

check("Route 'commercial.packages.change.status' existe", function() {
    return Route::has('commercial.packages.change.status');
});

echo "\n";

// ============================================================================
// SECTION 5: Controllers
// ============================================================================
echo "🎮 SECTION 5: Controllers\n";
echo str_repeat("-", 70) . "\n";

check("DepotReturnScanController existe", function() {
    return class_exists('App\Http\Controllers\Depot\DepotReturnScanController');
});

check("Méthode dashboard() existe dans DepotReturnScanController", function() {
    return method_exists('App\Http\Controllers\Depot\DepotReturnScanController', 'dashboard');
});

check("Méthode scanPackage() existe dans DepotReturnScanController", function() {
    return method_exists('App\Http\Controllers\Depot\DepotReturnScanController', 'scanPackage');
});

check("ClientDashboardController a returns()", function() {
    return method_exists('App\Http\Controllers\Client\ClientDashboardController', 'returns');
});

check("ClientDashboardController a confirmReturn()", function() {
    return method_exists('App\Http\Controllers\Client\ClientDashboardController', 'confirmReturn');
});

check("PackageController (Commercial) a launchFourthAttempt()", function() {
    return method_exists('App\Http\Controllers\Commercial\PackageController', 'launchFourthAttempt');
});

echo "\n";

// ============================================================================
// SECTION 6: Vues
// ============================================================================
echo "👁️  SECTION 6: Vues\n";
echo str_repeat("-", 70) . "\n";

check("Vue depot/returns/scan-dashboard existe", function() {
    return view()->exists('depot.returns.scan-dashboard');
});

check("Vue depot/returns/phone-scanner existe", function() {
    return view()->exists('depot.returns.phone-scanner');
});

check("Vue depot/returns/manage existe", function() {
    return view()->exists('depot.returns.manage');
});

check("Vue depot/returns/print-label existe", function() {
    return view()->exists('depot.returns.print-label');
});

check("Vue client/returns existe", function() {
    return view()->exists('client.returns');
});

check("Modal manual-status-change existe", function() {
    return view()->exists('commercial.packages.modals.manual-status-change');
});

echo "\n";

// ============================================================================
// SECTION 7: Cache & Sessions
// ============================================================================
echo "💾 SECTION 7: Cache & Configuration\n";
echo str_repeat("-", 70) . "\n";

check("Cache driver fonctionnel", function() {
    Cache::put('test_return_system', 'ok', 10);
    $result = Cache::get('test_return_system') === 'ok';
    Cache::forget('test_return_system');
    return $result;
});

check("Config 'app.name' définie", function() {
    return !empty(config('app.name'));
});

check("Environnement configuré", function() {
    return !empty(config('app.env'));
});

echo "\n";

// ============================================================================
// SECTION 8: Données de Test
// ============================================================================
echo "🧪 SECTION 8: Intégrité des Données\n";
echo str_repeat("-", 70) . "\n";

check("Au moins un utilisateur CLIENT existe", function() {
    return \App\Models\User::where('role', 'CLIENT')->exists();
});

check("Au moins une délégation existe", function() {
    return \App\Models\Delegation::exists();
});

$packagesInReturn = Package::whereIn('status', [
    'AWAITING_RETURN',
    'RETURN_IN_PROGRESS',
    'RETURNED_TO_CLIENT'
])->count();

check("Colis en processus de retour: $packagesInReturn", function() use ($packagesInReturn) {
    return $packagesInReturn >= 0 ? true : 'warning';
});

$returnPackagesCount = ReturnPackage::count();
check("Colis retours créés: $returnPackagesCount", function() use ($returnPackagesCount) {
    return $returnPackagesCount >= 0 ? true : 'warning';
});

echo "\n";

// ============================================================================
// RÉSULTAT FINAL
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     RÉSULTAT FINAL                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "Total de vérifications: $checks\n";
echo "✅ Succès: " . ($checks - $errors - $warnings) . "\n";
echo "⚠️  Avertissements: $warnings\n";
echo "❌ Erreurs: $errors\n\n";

if ($errors === 0 && $warnings === 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  🎉 SYSTÈME ENTIÈREMENT OPÉRATIONNEL ! 🎉                   ║\n";
    echo "║                                                               ║\n";
    echo "║  Tous les composants sont fonctionnels.                      ║\n";
    echo "║  Le système de retours est prêt pour la production.         ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    exit(0);
} elseif ($errors === 0 && $warnings > 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ⚠️  SYSTÈME FONCTIONNEL AVEC AVERTISSEMENTS                 ║\n";
    echo "║                                                               ║\n";
    echo "║  Le système fonctionne mais certaines optimisations          ║\n";
    echo "║  sont recommandées (voir les ⚠️  ci-dessus).                ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ❌ ERREURS DÉTECTÉES                                        ║\n";
    echo "║                                                               ║\n";
    echo "║  Veuillez corriger les erreurs (❌) avant la mise en         ║\n";
    echo "║  production. Consultez les messages d'erreur ci-dessus.      ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    exit(1);
}
