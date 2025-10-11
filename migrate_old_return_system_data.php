<?php

/**
 * Script de Migration - Ancien Système de Retours vers Nouveau Système
 *
 * Ce script migre les données de l'ancien système de retours vers le nouveau système automatisé.
 *
 * IMPORTANT:
 * - Créer un backup de la base de données avant d'exécuter ce script
 * - Exécuter en environnement de test d'abord
 * - Vérifier les résultats avant de déployer en production
 *
 * Usage:
 *   php migrate_old_return_system_data.php
 *
 * Options:
 *   php migrate_old_return_system_data.php --dry-run    # Mode simulation (ne modifie pas la BD)
 *   php migrate_old_return_system_data.php --verbose    # Mode verbeux (détails)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Options de ligne de commande
$dryRun = in_array('--dry-run', $argv);
$verbose = in_array('--verbose', $argv);

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   MIGRATION ANCIEN SYSTÈME RETOURS → NOUVEAU SYSTÈME        ║\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "\n";

if ($dryRun) {
    echo "🔍 MODE SIMULATION (Dry Run) - Aucune modification ne sera effectuée\n\n";
}

// Statistiques
$stats = [
    'total_packages_analyzed' => 0,
    'old_returned_migrated' => 0,
    'accepted_removed' => 0,
    'cancelled_removed' => 0,
    'exchange_requested_found' => 0,
    'exchange_processed_found' => 0,
    'errors' => 0,
];

try {
    echo "📊 ÉTAPE 1: Analyse de la base de données actuelle\n";
    echo "------------------------------------------------------------\n";

    // Compter les colis par statut
    $statusCounts = DB::table('packages')
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    echo "\nDistribution actuelle des statuts:\n";
    foreach ($statusCounts as $statusCount) {
        echo "  - {$statusCount->status}: {$statusCount->count} colis\n";
        $stats['total_packages_analyzed'] += $statusCount->count;
    }

    echo "\n";
    echo "🔄 ÉTAPE 2: Migration des colis avec statut 'RETURNED'\n";
    echo "------------------------------------------------------------\n";

    // Trouver tous les colis avec statut RETURNED (ancien système)
    $returnedPackages = Package::where('status', 'RETURNED')->get();

    echo "Trouvé {$returnedPackages->count()} colis avec statut 'RETURNED'\n";

    foreach ($returnedPackages as $package) {
        if ($verbose) {
            echo "  - Traitement colis: {$package->package_code}\n";
        }

        try {
            if (!$dryRun) {
                DB::beginTransaction();

                // Déterminer le nouveau statut basé sur le contexte
                $newStatus = 'AWAITING_RETURN';

                // Si le colis a 3+ tentatives échouées, c'est logique de le mettre en AWAITING_RETURN
                if ($package->unavailable_attempts >= 3) {
                    $newStatus = 'AWAITING_RETURN';
                    $package->awaiting_return_since = $package->updated_at ?? now();
                } else {
                    // Sinon, le mettre directement en RETURN_IN_PROGRESS
                    $newStatus = 'RETURN_IN_PROGRESS';
                    $package->return_in_progress_since = $package->updated_at ?? now();
                }

                $package->status = $newStatus;
                $package->return_reason = $package->return_reason ?? 'Migré depuis ancien système RETURNED';
                $package->save();

                DB::commit();

                if ($verbose) {
                    echo "    ✅ Migré vers: {$newStatus}\n";
                }
            }

            $stats['old_returned_migrated']++;
        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            echo "    ❌ Erreur: {$e->getMessage()}\n";
            $stats['errors']++;
            Log::error('Migration error for package: ' . $package->id, ['error' => $e->getMessage()]);
        }
    }

    echo "\n";
    echo "🗑️  ÉTAPE 3: Nettoyage des statuts obsolètes\n";
    echo "------------------------------------------------------------\n";

    // Vérifier les colis avec statut ACCEPTED
    $acceptedCount = Package::where('status', 'ACCEPTED')->count();
    echo "Trouvé {$acceptedCount} colis avec statut 'ACCEPTED'\n";

    if ($acceptedCount > 0) {
        echo "⚠️  ATTENTION: Le statut 'ACCEPTED' doit être remplacé par 'PICKED_UP'\n";

        if (!$dryRun) {
            Package::where('status', 'ACCEPTED')->update([
                'status' => 'PICKED_UP',
                'updated_at' => now(),
            ]);
            echo "  ✅ {$acceptedCount} colis mis à jour vers 'PICKED_UP'\n";
        }

        $stats['accepted_removed'] = $acceptedCount;
    }

    // Vérifier les colis avec statut CANCELLED
    $cancelledCount = Package::where('status', 'CANCELLED')->count();
    echo "\nTrouvé {$cancelledCount} colis avec statut 'CANCELLED'\n";

    if ($cancelledCount > 0) {
        echo "⚠️  ATTENTION: Le statut 'CANCELLED' est conservé pour l'instant\n";
        echo "  Recommandation: Vérifier manuellement ces colis et les archiver si nécessaire\n";
        $stats['cancelled_removed'] = $cancelledCount;
    }

    // Vérifier les colis avec statuts EXCHANGE_*
    $exchangeRequestedCount = Package::where('status', 'EXCHANGE_REQUESTED')->count();
    $exchangeProcessedCount = Package::where('status', 'EXCHANGE_PROCESSED')->count();

    echo "\nTrouvé {$exchangeRequestedCount} colis avec statut 'EXCHANGE_REQUESTED'\n";
    echo "Trouvé {$exchangeProcessedCount} colis avec statut 'EXCHANGE_PROCESSED'\n";

    if ($exchangeRequestedCount > 0 || $exchangeProcessedCount > 0) {
        echo "⚠️  ATTENTION: Les statuts d'échange ne sont plus utilisés\n";
        echo "  Recommandation: Convertir vers AWAITING_RETURN ou archiver\n";

        if (!$dryRun) {
            // Convertir EXCHANGE_REQUESTED vers AWAITING_RETURN
            Package::where('status', 'EXCHANGE_REQUESTED')->update([
                'status' => 'AWAITING_RETURN',
                'awaiting_return_since' => now(),
                'return_reason' => 'Migré depuis EXCHANGE_REQUESTED',
                'updated_at' => now(),
            ]);

            // Convertir EXCHANGE_PROCESSED vers RETURN_CONFIRMED (assumant que l'échange était terminé)
            Package::where('status', 'EXCHANGE_PROCESSED')->update([
                'status' => 'RETURN_CONFIRMED',
                'updated_at' => now(),
            ]);

            echo "  ✅ Statuts d'échange migrés vers nouveaux statuts\n";
        }

        $stats['exchange_requested_found'] = $exchangeRequestedCount;
        $stats['exchange_processed_found'] = $exchangeProcessedCount;
    }

    echo "\n";
    echo "✅ ÉTAPE 4: Vérification de l'intégrité des données\n";
    echo "------------------------------------------------------------\n";

    // Vérifier que tous les colis avec nouveaux statuts ont les champs requis
    $awaitingReturnWithoutDate = Package::where('status', 'AWAITING_RETURN')
        ->whereNull('awaiting_return_since')
        ->count();

    if ($awaitingReturnWithoutDate > 0) {
        echo "⚠️  {$awaitingReturnWithoutDate} colis en AWAITING_RETURN sans date\n";

        if (!$dryRun) {
            Package::where('status', 'AWAITING_RETURN')
                ->whereNull('awaiting_return_since')
                ->update(['awaiting_return_since' => now()]);
            echo "  ✅ Dates ajoutées\n";
        }
    }

    $returnInProgressWithoutDate = Package::where('status', 'RETURN_IN_PROGRESS')
        ->whereNull('return_in_progress_since')
        ->count();

    if ($returnInProgressWithoutDate > 0) {
        echo "⚠️  {$returnInProgressWithoutDate} colis en RETURN_IN_PROGRESS sans date\n";

        if (!$dryRun) {
            Package::where('status', 'RETURN_IN_PROGRESS')
                ->whereNull('return_in_progress_since')
                ->update(['return_in_progress_since' => now()]);
            echo "  ✅ Dates ajoutées\n";
        }
    }

    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                    RÉSUMÉ DE LA MIGRATION                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";

    echo "📦 Colis analysés: " . number_format($stats['total_packages_analyzed']) . "\n";
    echo "✅ Colis RETURNED migrés: " . number_format($stats['old_returned_migrated']) . "\n";
    echo "✅ Colis ACCEPTED convertis: " . number_format($stats['accepted_removed']) . "\n";
    echo "⚠️  Colis CANCELLED trouvés: " . number_format($stats['cancelled_removed']) . "\n";
    echo "⚠️  Colis EXCHANGE_REQUESTED: " . number_format($stats['exchange_requested_found']) . "\n";
    echo "⚠️  Colis EXCHANGE_PROCESSED: " . number_format($stats['exchange_processed_found']) . "\n";
    echo "❌ Erreurs rencontrées: " . number_format($stats['errors']) . "\n";

    echo "\n";

    if ($dryRun) {
        echo "🔍 MODE SIMULATION - Aucune modification n'a été effectuée\n";
        echo "Exécutez sans --dry-run pour appliquer les changements\n";
    } else {
        echo "✅ MIGRATION TERMINÉE AVEC SUCCÈS\n";

        // Log de la migration
        Log::info('Migration système retours terminée', $stats);

        echo "\n";
        echo "📋 Prochaines étapes recommandées:\n";
        echo "  1. Vérifier les colis dans l'interface web\n";
        echo "  2. Tester le workflow complet de retours\n";
        echo "  3. Former les utilisateurs sur le nouveau système\n";
        echo "  4. Supprimer les anciennes vues et routes (voir MIGRATION_ANCIEN_VERS_NOUVEAU_SYSTEME_RETOURS.md)\n";
    }

    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                   MIGRATION TERMINÉE                          ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";

} catch (\Exception $e) {
    echo "\n";
    echo "❌ ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    Log::critical('Migration système retours échouée', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit(1);
}
