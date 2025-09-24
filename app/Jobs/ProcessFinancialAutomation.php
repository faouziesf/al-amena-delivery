<?php

namespace App\Jobs;

use App\Services\FinancialAutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFinancialAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Type d'automatisation à exécuter
     */
    protected string $automationType;

    /**
     * Configuration supplémentaire
     */
    protected array $config;

    /**
     * Nombre maximum de tentatives
     */
    public int $tries = 3;

    /**
     * Timeout en secondes
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(string $automationType, array $config = [])
    {
        $this->automationType = $automationType;
        $this->config = $config;

        // Queue spécialisée pour les tâches financières
        $this->onQueue('financial');
    }

    /**
     * Execute the job.
     */
    public function handle(FinancialAutomationService $financialService): void
    {
        Log::info("Début d'automatisation financière", [
            'type' => $this->automationType,
            'config' => $this->config
        ]);

        $startTime = microtime(true);
        $results = [];

        try {
            switch ($this->automationType) {
                case 'topup_validation':
                    $results = $financialService->processAutoValidationTopups();
                    break;

                case 'cod_payments':
                    $results = $financialService->processAutoCodPayments();
                    break;

                case 'wallet_reconciliation':
                    $results = $financialService->reconcileWallets();
                    break;

                case 'data_cleanup':
                    $results = $financialService->cleanupOldData();
                    break;

                case 'full_automation':
                    // Exécuter toutes les automatisations dans l'ordre
                    $results = $this->runFullAutomation($financialService);
                    break;

                default:
                    throw new \InvalidArgumentException("Type d'automatisation inconnu: {$this->automationType}");
            }

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("Automatisation financière terminée avec succès", [
                'type' => $this->automationType,
                'results' => $results,
                'execution_time_ms' => $executionTime
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error("Erreur lors de l'automatisation financière", [
                'type' => $this->automationType,
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            // Re-lancer l'exception pour déclencher les tentatives de retry
            throw $e;
        }
    }

    /**
     * Exécuter toutes les automatisations
     */
    protected function runFullAutomation(FinancialAutomationService $financialService): array
    {
        $results = [
            'topup_validation' => [],
            'cod_payments' => [],
            'wallet_reconciliation' => [],
            'data_cleanup' => [],
            'total_execution_time_ms' => 0
        ];

        $overallStartTime = microtime(true);

        // 1. Auto-validation des topups
        try {
            $startTime = microtime(true);
            $results['topup_validation'] = $financialService->processAutoValidationTopups();
            $results['topup_validation']['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        } catch (\Exception $e) {
            $results['topup_validation']['error'] = $e->getMessage();
            Log::error('Erreur topup validation dans full automation', ['error' => $e->getMessage()]);
        }

        // 2. Traitement paiements COD
        try {
            $startTime = microtime(true);
            $results['cod_payments'] = $financialService->processAutoCodPayments();
            $results['cod_payments']['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        } catch (\Exception $e) {
            $results['cod_payments']['error'] = $e->getMessage();
            Log::error('Erreur COD payments dans full automation', ['error' => $e->getMessage()]);
        }

        // 3. Réconciliation des wallets (seulement si configuré)
        if ($this->config['include_reconciliation'] ?? true) {
            try {
                $startTime = microtime(true);
                $results['wallet_reconciliation'] = $financialService->reconcileWallets();
                $results['wallet_reconciliation']['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            } catch (\Exception $e) {
                $results['wallet_reconciliation']['error'] = $e->getMessage();
                Log::error('Erreur wallet reconciliation dans full automation', ['error' => $e->getMessage()]);
            }
        }

        // 4. Nettoyage des anciennes données (seulement le weekend)
        if ($this->config['include_cleanup'] ?? (now()->isWeekend())) {
            try {
                $startTime = microtime(true);
                $results['data_cleanup'] = $financialService->cleanupOldData();
                $results['data_cleanup']['execution_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            } catch (\Exception $e) {
                $results['data_cleanup']['error'] = $e->getMessage();
                Log::error('Erreur data cleanup dans full automation', ['error' => $e->getMessage()]);
            }
        }

        $results['total_execution_time_ms'] = round((microtime(true) - $overallStartTime) * 1000, 2);

        return $results;
    }

    /**
     * Détermine le délai avant nouvelle tentative
     */
    public function backoff(): array
    {
        // Délais progressifs: 30s, 2min, 5min
        return [30, 120, 300];
    }

    /**
     * Gestion des échecs après toutes les tentatives
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("Échec définitif de l'automatisation financière", [
            'type' => $this->automationType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'config' => $this->config
        ]);

        // Optionnel: Envoyer une notification aux administrateurs
        // Notification::route('mail', config('app.admin_email'))
        //     ->notify(new FinancialAutomationFailedNotification($this->automationType, $exception));
    }

    /**
     * Tags pour identifier le job dans la queue
     */
    public function tags(): array
    {
        return [
            'financial',
            'automation',
            $this->automationType,
        ];
    }
}