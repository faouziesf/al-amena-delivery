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
    ];

    protected function schedule(Schedule $schedule)
    {
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
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}