<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\Commercial\CheckHighWalletBalances::class,
        Commands\Commercial\CleanupNotifications::class,
        Commands\Commercial\RecoverPendingTransactions::class,
        Commands\Commercial\GenerateDailyReport::class,
        Commands\FinancialAutomation::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // === TÂCHES COMMERCIALES ===

        // Vérifier les wallets élevés toutes les 2 heures
        $schedule->command('commercial:check-high-wallets')->everyTwoHours();

        // Récupérer les transactions bloquées toutes les 5 minutes
        $schedule->command('commercial:recover-transactions')->everyFiveMinutes();

        // Nettoyer les notifications tous les jours à 2h du matin
        $schedule->command('commercial:cleanup-notifications')->dailyAt('02:00');

        // Générer le rapport quotidien à 23h
        $schedule->command('commercial:daily-report')->dailyAt('23:00');

        // Traitement automatique des colis livrés à 22h
        $schedule->call(function () {
            app(\App\Services\PackageProcessingService::class)->processDeliveredPackages();
        })->dailyAt('22:00');

        // === AUTOMATISATION FINANCIÈRE ===

        // Auto-validation des demandes de rechargement - DÉSACTIVÉ
        // Les topups bancaires doivent être gérés manuellement par le commercial
        // $schedule->command('financial:automation topup')

        // Traitement automatique COD - toutes les 4 heures
        $schedule->command('financial:automation cod')
            ->cron('0 */4 * * *') // 00:00, 04:00, 08:00, 12:00, 16:00, 20:00
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Traitement COD automatique exécuté avec succès');
            });

        // Réconciliation des wallets - une fois par jour à 3h du matin
        $schedule->command('financial:automation reconcile')
            ->dailyAt('03:00')
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Réconciliation des wallets exécutée avec succès');
            });

        // Nettoyage des anciennes données - une fois par semaine (dimanche à 1h)
        $schedule->command('financial:automation cleanup')
            ->weeklyOn(0, '01:00') // Dimanche à 1h
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Nettoyage des données anciennes exécuté avec succès');
            });

        // Automatisation complète - MODIFIÉE pour exclure les topups
        // Exécute seulement COD et réconciliation
        $schedule->call(function () {
            // Traitement COD
            app(\App\Services\FinancialAutomationService::class)->processAutoCodPayments();
            // Réconciliation
            app(\App\Services\FinancialAutomationService::class)->reconcileWallets();
            \Log::info('Automatisation financière partielle exécutée (COD + réconciliation)');
        })
            ->dailyAt('04:00')
            ->runInBackground()
            ->skip(function () {
                return app()->environment('local');
            })
            ->onFailure(function () {
                \Log::critical('Échec de l\'automatisation financière partielle');
            });

        // Tâche de monitoring du système financier - toutes les 30 minutes
        $schedule->call(function () {
            $stats = app(\App\Services\FinancialAutomationService::class)->getFinancialSystemStats();

            // Log des métriques importantes
            \Log::info('Statistiques système financier', [
                'total_wallets' => $stats['wallets']['total'],
                'pending_transactions' => $stats['transactions']['pending'],
                'pending_topups' => $stats['topup_requests']['pending'],
                'auto_validation_eligible' => $stats['topup_requests']['auto_validation_eligible'],
                'total_system_balance' => $stats['total_system_balance']
            ]);

            // Alertes si nécessaire
            if ($stats['transactions']['failed'] > 10) {
                \Log::warning('Nombre élevé de transactions échouées', [
                    'failed_count' => $stats['transactions']['failed']
                ]);
            }

            if ($stats['topup_requests']['auto_validation_eligible'] > 20) {
                \Log::info('Nombre élevé de demandes éligibles à l\'auto-validation', [
                    'eligible_count' => $stats['topup_requests']['auto_validation_eligible']
                ]);
            }
        })
        ->everyThirtyMinutes()
        ->name('financial-monitoring')
        ->runInBackground();

        // === SYSTÈME DE RETOURS AUTOMATISÉ ===

        // Traiter les colis en attente de retour (48h) - toutes les heures
        $schedule->job(new \App\Jobs\ProcessAwaitingReturnsJob)
            ->hourly()
            ->name('process-awaiting-returns')
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Job ProcessAwaitingReturnsJob exécuté avec succès');
            })
            ->onFailure(function () {
                \Log::error('Échec du job ProcessAwaitingReturnsJob');
            });

        // Auto-confirmer les retours clients (48h sans action) - toutes les heures
        $schedule->job(new \App\Jobs\ProcessReturnedPackagesJob)
            ->hourly()
            ->name('process-returned-packages')
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Job ProcessReturnedPackagesJob exécuté avec succès');
            })
            ->onFailure(function () {
                \Log::error('Échec du job ProcessReturnedPackagesJob');
            });
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}