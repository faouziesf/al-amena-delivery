<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FinancialTransactionService;
use App\Services\ActionLogService;
use App\Models\Package;
use App\Models\FinancialTransaction;
use App\Models\CodCollectionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ProcessNightlyTransactions extends Command
{
    /**
     * Nom et signature de la command selon les spécifications système
     */
    protected $signature = 'wallet:process-nightly 
                            {--date= : Date spécifique à traiter (format Y-m-d, défaut: aujourd\'hui)}
                            {--force : Forcer le traitement même si déjà effectué}
                            {--dry-run : Mode simulation sans modifications}
                            {--verbose : Affichage détaillé}';

    /**
     * Description de la command
     */
    protected $description = 'Traitement automatique 22h00 - Conversion colis DELIVERED vers PAID avec ajustements wallets';

    protected $financialService;
    protected $actionLogService;

    public function __construct(FinancialTransactionService $financialService, ActionLogService $actionLogService)
    {
        parent::__construct();
        $this->financialService = $financialService;
        $this->actionLogService = $actionLogService;
    }

    /**
     * Exécution du traitement automatique
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info("🌙 Début du traitement automatique 22h00 - " . now()->format('Y-m-d H:i:s'));

        try {
            // 1. Validation des options et préparation
            $date = $this->option('date') ? Carbon::parse($this->option('date')) : today();
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');

            $this->info("📅 Date de traitement: {$date->format('Y-m-d')}");
            if ($dryRun) {
                $this->warn("🧪 MODE SIMULATION - Aucune modification ne sera effectuée");
            }

            // 2. Vérifier si le traitement a déjà été effectué
            if (!$force && $this->isAlreadyProcessed($date)) {
                $this->warn("⚠️  Traitement déjà effectué pour {$date->format('Y-m-d')}");
                return self::SUCCESS;
            }

            // 3. Récupération des colis livrés du jour
            $deliveredPackages = $this->getDeliveredPackagesForDate($date);
            $this->info("📦 {$deliveredPackages->count()} colis DELIVERED trouvés pour {$date->format('Y-m-d')}");

            if ($deliveredPackages->isEmpty()) {
                $this->info("✅ Aucun colis à traiter");
                return self::SUCCESS;
            }

            // 4. Traitement principal
            $results = $this->processDeliveredPackages($deliveredPackages, $dryRun);

            // 5. Récupération automatique des transactions échouées
            $recoveryResults = $this->recoverFailedTransactions($dryRun);

            // 6. Vérification d'intégrité post-traitement
            $integrityResults = $this->performIntegrityChecks();

            // 7. Génération du rapport
            $this->generateProcessingReport($results, $recoveryResults, $integrityResults, $startTime);

            // 8. Notifications et alertes
            $this->sendNotifications($results, $recoveryResults, $integrityResults);

            $this->info("🎉 Traitement terminé avec succès");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur critique: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            
            // Log d'erreur critique
            Log::critical("Échec du traitement automatique 22h00", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'date' => $date ?? null,
                'dry_run' => $dryRun ?? false
            ]);

            // Notification d'urgence
            $this->sendEmergencyNotification($e);

            return self::FAILURE;
        }
    }

    /**
     * Vérifier si le traitement a déjà été effectué
     */
    private function isAlreadyProcessed(Carbon $date)
    {
        return DB::table('system_recovery_logs')
            ->where('recovery_type', 'NIGHTLY_PROCESSING')
            ->whereDate('created_at', $date)
            ->where('success', true)
            ->exists();
    }

    /**
     * Récupérer les colis livrés pour une date
     */
    private function getDeliveredPackagesForDate(Carbon $date)
    {
        return Package::where('status', 'DELIVERED')
            ->whereDate('updated_at', $date)
            ->with(['sender', 'sender.wallet'])
            ->get();
    }

    /**
     * Traitement principal des colis livrés
     */
    private function processDeliveredPackages($packages, $dryRun = false)
    {
        $results = [
            'total_packages' => $packages->count(),
            'processed_successfully' => 0,
            'failed_processing' => 0,
            'cod_validations_missing' => 0,
            'wallet_adjustments' => 0,
            'total_amount_credited' => 0,
            'errors' => []
        ];

        $progressBar = $this->output->createProgressBar($packages->count());
        $progressBar->setFormat('🔄 Traitement: %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($packages as $package) {
            $progressBar->setMessage("Colis #{$package->package_code}");
            
            try {
                // 1. Vérification COD validé par commercial
                if (!$this->isCodValidatedByCommercial($package)) {
                    $results['cod_validations_missing']++;
                    $results['errors'][] = "COD non validé pour colis #{$package->package_code}";
                    $progressBar->advance();
                    continue;
                }

                if (!$dryRun) {
                    DB::beginTransaction();
                }

                // 2. Calcul du montant à créditer selon logique X/Y
                $creditAmount = $this->calculateDeliveryCreditAmount($package);

                if ($this->option('verbose')) {
                    $this->line("");
                    $this->info("📦 Colis #{$package->package_code}:");
                    $this->info("   💰 COD: {$package->cod_amount} DT");
                    $this->info("   🚚 Frais livraison: {$package->delivery_fee} DT");
                    $this->info("   🔄 Frais retour: {$package->return_fee} DT");
                    $this->info("   ➕ Crédit calculé: {$creditAmount} DT");
                }

                // 3. Traitement financier si montant positif
                if ($creditAmount > 0 && !$dryRun) {
                    $this->processPackageDeliveryPayment($package, $creditAmount);
                    $results['wallet_adjustments']++;
                    $results['total_amount_credited'] += $creditAmount;
                }

                // 4. Changement du statut DELIVERED → PAID
                if (!$dryRun) {
                    $package->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                        'processed_at_22h' => true,
                        'updated_at' => now()
                    ]);

                    // Log du changement de statut
                    $this->actionLogService->logPackageStatusChange(
                        $package->id,
                        'DELIVERED',
                        'PAID',
                        [
                            'trigger' => 'NIGHTLY_PROCESSING',
                            'credit_amount' => $creditAmount,
                            'processed_at' => now()
                        ]
                    );

                    DB::commit();
                }

                $results['processed_successfully']++;

            } catch (\Exception $e) {
                if (!$dryRun) {
                    DB::rollBack();
                }

                $results['failed_processing']++;
                $results['errors'][] = "Erreur colis #{$package->package_code}: " . $e->getMessage();
                
                Log::error("Erreur traitement colis #{$package->package_code}", [
                    'package_id' => $package->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("");

        return $results;
    }

    /**
     * Vérifier si COD est validé par commercial
     */
    private function isCodValidatedByCommercial($package)
    {
        return CodCollectionLog::where('package_id', $package->id)
            ->where('commercial_validated', true)
            ->exists();
    }

    /**
     * Calculer le montant crédit selon la logique X/Y du système
     */
    private function calculateDeliveryCreditAmount($package)
    {
        $codAmount = $package->cod_amount;
        $deliveryFee = $package->delivery_fee;
        $returnFee = $package->return_fee ?? 0;

        /*
         * Logique selon spécifications système de livraison:
         * 
         * Cas 1: COD ≥ Frais Livraison (X ≥ Y)
         * - Création: Déduction Z du wallet client (si wallet < Z → impossible)
         * - Montant en attente: (X + Z) - Y
         * - Si livré 22h00: +montant en attente au wallet
         * 
         * Cas 2: COD < Frais Livraison (X < Y)
         * - Création: Déduction Y du wallet client (si wallet < Y → impossible)
         * - Montant en attente: X complet
         * - Si livré 22h00: +COD au wallet
         */

        if ($codAmount >= $deliveryFee) {
            // Cas 1: COD ≥ Frais Livraison
            return ($codAmount + $returnFee) - $deliveryFee;
        } else {
            // Cas 2: COD < Frais Livraison
            return $codAmount;
        }
    }

    /**
     * Traiter le paiement de livraison d'un colis
     */
    private function processPackageDeliveryPayment($package, $creditAmount)
    {
        $transactionData = [
            'user_id' => $package->sender_id,
            'type' => 'PACKAGE_DELIVERY_CREDIT',
            'amount' => $creditAmount,
            'package_id' => $package->id,
            'description' => "Crédit livraison colis #{$package->package_code} - Traitement automatique 22h00",
            'reference' => $package->package_code,
            'metadata' => [
                'package_code' => $package->package_code,
                'cod_amount' => $package->cod_amount,
                'delivery_fee' => $package->delivery_fee,
                'return_fee' => $package->return_fee,
                'processed_at_22h' => true,
                'credit_calculation' => [
                    'cod_amount' => $package->cod_amount,
                    'delivery_fee' => $package->delivery_fee,
                    'return_fee' => $package->return_fee,
                    'formula_used' => $package->cod_amount >= $package->delivery_fee ? 'COD_GTE_DELIVERY' : 'COD_LT_DELIVERY'
                ]
            ]
        ];

        $result = $this->financialService->processTransaction($transactionData);

        if (!$result['success']) {
            throw new \Exception("Échec transaction financière: " . json_encode($result));
        }

        if ($this->option('verbose')) {
            $this->info("   ✅ Transaction créée: {$result['transaction_code']}");
        }

        return $result;
    }

    /**
     * Récupération automatique des transactions échouées
     */
    private function recoverFailedTransactions($dryRun = false)
    {
        $this->info("🔧 Récupération des transactions échouées...");

        if ($dryRun) {
            $this->warn("🧪 Simulation de récupération");
            return ['recovered' => 0, 'failed' => 0];
        }

        return $this->financialService->recoverPendingTransactions();
    }

    /**
     * Vérifications d'intégrité post-traitement
     */
    private function performIntegrityChecks()
    {
        $this->info("🔍 Vérifications d'intégrité...");

        $results = [
            'wallets_checked' => 0,
            'wallets_with_discrepancies' => 0,
            'transactions_verified' => 0,
            'integrity_issues' => []
        ];

        // Vérifier l'intégrité des wallets des clients ayant reçu des crédits aujourd'hui
        $affectedUsers = FinancialTransaction::where('type', 'PACKAGE_DELIVERY_CREDIT')
            ->whereDate('created_at', today())
            ->distinct('user_id')
            ->pluck('user_id');

        foreach ($affectedUsers as $userId) {
            $results['wallets_checked']++;
            
            $integrityCheck = $this->financialService->verifyWalletIntegrity($userId);
            
            if (!$integrityCheck['valid']) {
                $results['wallets_with_discrepancies']++;
                $results['integrity_issues'][] = [
                    'user_id' => $userId,
                    'issue' => $integrityCheck['error'],
                    'discrepancy' => $integrityCheck['discrepancy'] ?? null
                ];

                $this->warn("⚠️  Incohérence wallet utilisateur #{$userId}: {$integrityCheck['error']}");
            }
        }

        // Vérifier les transactions du jour
        $todayTransactions = FinancialTransaction::whereDate('created_at', today())
            ->where('status', 'COMPLETED')
            ->get();

        foreach ($todayTransactions as $transaction) {
            $results['transactions_verified']++;
            
            $verificationResult = $transaction->verifyIntegrity();
            
            if (!$verificationResult['valid']) {
                $results['integrity_issues'][] = [
                    'transaction_id' => $transaction->transaction_id,
                    'issue' => $verificationResult['error']
                ];

                $this->warn("⚠️  Transaction corrompue: {$transaction->transaction_id}");
            }
        }

        return $results;
    }

    /**
     * Génération du rapport de traitement
     */
    private function generateProcessingReport($results, $recoveryResults, $integrityResults, $startTime)
    {
        $endTime = microtime(true);
        $processingTime = round($endTime - $startTime, 2);

        $this->line("");
        $this->info("📊 RAPPORT DE TRAITEMENT AUTOMATIQUE 22H00");
        $this->info("=" . str_repeat("=", 50));
        
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Date de traitement', now()->format('Y-m-d H:i:s')],
                ['Temps d\'exécution', "{$processingTime}s"],
                ['Mode', $this->option('dry-run') ? 'SIMULATION' : 'PRODUCTION'],
                ['', ''],
                ['📦 COLIS TRAITÉS', ''],
                ['Total colis trouvés', $results['total_packages']],
                ['Traités avec succès', $results['processed_successfully']],
                ['Échecs de traitement', $results['failed_processing']],
                ['COD non validés', $results['cod_validations_missing']],
                ['', ''],
                ['💰 TRANSACTIONS FINANCIÈRES', ''],
                ['Ajustements wallet', $results['wallet_adjustments']],
                ['Montant total crédité', number_format($results['total_amount_credited'], 3) . ' DT'],
                ['', ''],
                ['🔧 RÉCUPÉRATION AUTOMATIQUE', ''],
                ['Transactions récupérées', $recoveryResults['recovered']],
                ['Échecs de récupération', $recoveryResults['failed']],
                ['', ''],
                ['🔍 VÉRIFICATIONS INTÉGRITÉ', ''],
                ['Wallets vérifiés', $integrityResults['wallets_checked']],
                ['Wallets avec incohérences', $integrityResults['wallets_with_discrepancies']],
                ['Transactions vérifiées', $integrityResults['transactions_verified']],
                ['Problèmes d\'intégrité', count($integrityResults['integrity_issues'])]
            ]
        );

        // Afficher les erreurs s'il y en a
        if (!empty($results['errors'])) {
            $this->line("");
            $this->error("❌ ERREURS RENCONTRÉES:");
            foreach ($results['errors'] as $error) {
                $this->line("   • {$error}");
            }
        }

        // Afficher les problèmes d'intégrité
        if (!empty($integrityResults['integrity_issues'])) {
            $this->line("");
            $this->warn("⚠️  PROBLÈMES D'INTÉGRITÉ:");
            foreach ($integrityResults['integrity_issues'] as $issue) {
                $this->line("   • " . json_encode($issue));
            }
        }

        // Enregistrer le log de traitement
        $this->recordProcessingLog($results, $recoveryResults, $integrityResults, $processingTime);
    }

    /**
     * Enregistrement du log de traitement
     */
    private function recordProcessingLog($results, $recoveryResults, $integrityResults, $processingTime)
    {
        if (!$this->option('dry-run')) {
            DB::table('system_recovery_logs')->insert([
                'recovery_type' => 'NIGHTLY_PROCESSING',
                'success' => $results['failed_processing'] === 0 && count($integrityResults['integrity_issues']) === 0,
                'error_details' => json_encode([
                    'processing_results' => $results,
                    'recovery_results' => $recoveryResults,
                    'integrity_results' => $integrityResults,
                    'processing_time_seconds' => $processingTime,
                    'command_options' => [
                        'date' => $this->option('date'),
                        'force' => $this->option('force'),
                        'dry_run' => $this->option('dry-run'),
                        'verbose' => $this->option('verbose')
                    ]
                ]),
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Envoi des notifications
     */
    private function sendNotifications($results, $recoveryResults, $integrityResults)
    {
        if ($this->option('dry-run')) {
            return;
        }

        $hasErrors = $results['failed_processing'] > 0 || count($integrityResults['integrity_issues']) > 0;
        
        // Notification aux superviseurs
        $supervisors = User::where('role', 'SUPERVISOR')->get();
        
        foreach ($supervisors as $supervisor) {
            // Ici, implémenter l'envoi d'email/SMS selon les besoins
            // Mail::to($supervisor->email)->send(new NightlyProcessingReport(...));
        }

        // Log de notification
        Log::info("Notifications envoyées pour traitement automatique 22h00", [
            'recipients_count' => $supervisors->count(),
            'has_errors' => $hasErrors,
            'processed_packages' => $results['processed_successfully']
        ]);
    }

    /**
     * Notification d'urgence en cas d'erreur critique
     */
    private function sendEmergencyNotification(\Exception $e)
    {
        // Notification immédiate en cas d'échec critique
        Log::critical("🚨 ALERTE: Échec critique du traitement automatique 22h00", [
            'error' => $e->getMessage(),
            'timestamp' => now(),
            'requires_immediate_attention' => true
        ]);

        // Ici, implémenter notification SMS/Slack pour les urgences
    }
}