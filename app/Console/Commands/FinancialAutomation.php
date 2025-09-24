<?php

namespace App\Console\Commands;

use App\Services\FinancialAutomationService;
use App\Jobs\ProcessFinancialAutomation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class FinancialAutomation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:automation
                            {type=full : Type d\'automatisation (topup, cod, reconcile, cleanup, full)}
                            {--sync : Exécuter en mode synchrone au lieu d\'utiliser la queue}
                            {--force : Forcer l\'exécution même en production}
                            {--stats : Afficher les statistiques du système financier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les processus d\'automatisation financière (topup validation, COD payments, wallet reconciliation)';

    /**
     * Execute the console command.
     */
    public function handle(FinancialAutomationService $financialService)
    {
        $type = $this->argument('type');
        $sync = $this->option('sync');
        $force = $this->option('force');
        $showStats = $this->option('stats');

        // Afficher les statistiques si demandé
        if ($showStats) {
            $this->displayFinancialStats($financialService);
            return 0;
        }

        // Vérification environnement production
        if (app()->environment('production') && !$force) {
            if (!$this->confirm('Vous êtes en production. Voulez-vous vraiment exécuter l\'automatisation financière ?')) {
                $this->info('Automatisation annulée.');
                return 0;
            }
        }

        $this->info("🔄 Démarrage de l'automatisation financière: {$type}");
        $this->newLine();

        $validTypes = ['topup', 'cod', 'reconcile', 'cleanup', 'full'];
        if (!in_array($type, $validTypes)) {
            $this->error("Type d'automatisation invalide. Types disponibles: " . implode(', ', $validTypes));
            return 1;
        }

        if ($sync) {
            return $this->runSynchronously($type, $financialService);
        } else {
            return $this->runAsynchronously($type);
        }
    }

    /**
     * Exécuter de manière synchrone
     */
    protected function runSynchronously(string $type, FinancialAutomationService $financialService): int
    {
        $this->info("⚡ Exécution synchrone...");
        $startTime = microtime(true);

        try {
            switch ($type) {
                case 'topup':
                    $results = $financialService->processAutoValidationTopups();
                    $this->displayTopupResults($results);
                    break;

                case 'cod':
                    $results = $financialService->processAutoCodPayments();
                    $this->displayCodResults($results);
                    break;

                case 'reconcile':
                    $results = $financialService->reconcileWallets();
                    $this->displayReconciliationResults($results);
                    break;

                case 'cleanup':
                    $results = $financialService->cleanupOldData();
                    $this->displayCleanupResults($results);
                    break;

                case 'full':
                    $this->info("🎯 Exécution complète de toutes les automatisations...");
                    $this->newLine();

                    $topupResults = $financialService->processAutoValidationTopups();
                    $this->displayTopupResults($topupResults);

                    $codResults = $financialService->processAutoCodPayments();
                    $this->displayCodResults($codResults);

                    $reconcileResults = $financialService->reconcileWallets();
                    $this->displayReconciliationResults($reconcileResults);

                    $cleanupResults = $financialService->cleanupOldData();
                    $this->displayCleanupResults($cleanupResults);
                    break;
            }

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->newLine();
            $this->info("✅ Automatisation terminée avec succès en {$executionTime}ms");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'automatisation: " . $e->getMessage());
            $this->error("Ligne: " . $e->getLine() . ", Fichier: " . $e->getFile());
            return 1;
        }
    }

    /**
     * Exécuter de manière asynchrone via queue
     */
    protected function runAsynchronously(string $type): int
    {
        $this->info("📤 Ajout à la queue...");

        $typeMapping = [
            'topup' => 'topup_validation',
            'cod' => 'cod_payments',
            'reconcile' => 'wallet_reconciliation',
            'cleanup' => 'data_cleanup',
            'full' => 'full_automation'
        ];

        $jobType = $typeMapping[$type];

        $job = new ProcessFinancialAutomation($jobType, [
            'source' => 'artisan_command',
            'triggered_at' => now()->toISOString()
        ]);

        $jobId = Queue::push($job);

        $this->info("📋 Job ajouté à la queue avec l'ID: {$jobId}");
        $this->info("🕒 Le traitement sera effectué par un worker de queue");
        $this->info("📝 Consultez les logs pour suivre l'avancement");

        return 0;
    }

    /**
     * Afficher les statistiques financières
     */
    protected function displayFinancialStats(FinancialAutomationService $financialService)
    {
        $this->info("📊 Statistiques du système financier");
        $this->newLine();

        $stats = $financialService->getFinancialSystemStats();

        // Wallets
        $this->info("💳 WALLETS:");
        $this->line("  Total: {$stats['wallets']['total']}");
        $this->line("  Avec solde: {$stats['wallets']['with_balance']}");
        $this->line("  Avec montant en attente: {$stats['wallets']['with_pending']}");
        $this->line("  Avec montant gelé: {$stats['wallets']['with_frozen']}");

        $this->newLine();

        // Transactions
        $this->info("💰 TRANSACTIONS:");
        $this->line("  Total: {$stats['transactions']['total']}");
        $this->line("  Aujourd'hui: {$stats['transactions']['today']}");
        $this->line("  En attente: {$stats['transactions']['pending']}");
        $this->line("  Terminées: {$stats['transactions']['completed']}");
        $this->line("  Échouées: {$stats['transactions']['failed']}");

        $this->newLine();

        // Demandes de topup
        $this->info("📈 DEMANDES DE RECHARGEMENT:");
        $this->line("  Total: {$stats['topup_requests']['total']}");
        $this->line("  En attente: {$stats['topup_requests']['pending']}");
        $this->line("  Validées aujourd'hui: {$stats['topup_requests']['validated_today']}");
        $this->line("  Éligibles auto-validation: {$stats['topup_requests']['auto_validation_eligible']}");

        $this->newLine();

        // Totaux système
        $this->info("💎 SOLDES SYSTÈME:");
        $this->line("  Solde total: " . number_format($stats['total_system_balance'], 3) . " DT");
        $this->line("  Montants en attente: " . number_format($stats['total_pending_amount'], 3) . " DT");
        $this->line("  Montants gelés: " . number_format($stats['total_frozen_amount'], 3) . " DT");
    }

    /**
     * Afficher les résultats topup
     */
    protected function displayTopupResults(array $results)
    {
        $this->info("💳 Auto-validation des demandes de rechargement:");
        if (isset($results['message'])) {
            $this->warn("  " . $results['message']);
        } else {
            $this->line("  Traitées: {$results['processed']}");
            $this->line("  Validées: {$results['validated']}");
            $this->line("  Rejetées: {$results['rejected']}");
            if (!empty($results['errors'])) {
                $this->warn("  Erreurs: " . count($results['errors']));
            }
        }
        $this->newLine();
    }

    /**
     * Afficher les résultats COD
     */
    protected function displayCodResults(array $results)
    {
        $this->info("💰 Traitement automatique COD:");
        $this->line("  Traitées: {$results['processed']}");
        $this->line("  Succès: {$results['success']}");
        $this->line("  Échecs: {$results['failed']}");
        if (!empty($results['errors'])) {
            $this->warn("  Erreurs: " . count($results['errors']));
        }
        $this->newLine();
    }

    /**
     * Afficher les résultats de réconciliation
     */
    protected function displayReconciliationResults(array $results)
    {
        $this->info("🔄 Réconciliation des wallets:");
        $this->line("  Vérifiés: {$results['checked']}");
        $this->line("  Incohérences: {$results['inconsistencies']}");
        $this->line("  Corrigés: {$results['fixed']}");
        if (!empty($results['errors'])) {
            $this->warn("  Erreurs: " . count($results['errors']));
        }
        $this->newLine();
    }

    /**
     * Afficher les résultats de nettoyage
     */
    protected function displayCleanupResults(array $results)
    {
        $this->info("🧹 Nettoyage des données anciennes:");
        $this->line("  Transactions supprimées: {$results['old_transactions_cleaned']}");
        $this->line("  Demandes topup supprimées: {$results['old_topup_requests_cleaned']}");
        if (!empty($results['errors'])) {
            $this->warn("  Erreurs: " . count($results['errors']));
        }
        $this->newLine();
    }
}
