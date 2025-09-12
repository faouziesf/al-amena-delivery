<?php

namespace App\Console\Commands\Commercial;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CleanupNotifications extends Command
{
    protected $signature = 'commercial:cleanup-notifications {--days=30 : Supprimer les notifications lues plus anciennes que X jours}';
    protected $description = 'Nettoyer les anciennes notifications lues';

    public function handle(NotificationService $notificationService)
    {
        $days = $this->option('days');
        
        $this->info("🧹 Nettoyage des notifications lues > {$days} jours...");
        
        $expiredCount = $notificationService->cleanupExpiredNotifications();
        $oldCount = $notificationService->cleanupOldReadNotifications($days);
        
        $this->info("✅ {$expiredCount} notifications expirées supprimées");
        $this->info("✅ {$oldCount} anciennes notifications lues supprimées");
        
        return Command::SUCCESS;
    }
}