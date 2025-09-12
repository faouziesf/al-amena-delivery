<?php

namespace App\Console\Commands\Commercial;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\User;

class CheckHighWalletBalances extends Command
{
    protected $signature = 'commercial:check-high-wallets {--threshold=100 : Seuil minimum pour notification}';
    protected $description = 'Vérifier les wallets livreurs avec soldes élevés et créer des notifications';

    public function handle(NotificationService $notificationService)
    {
        $threshold = $this->option('threshold');
        
        $this->info("🔍 Vérification des wallets > {$threshold} DT...");
        
        $notifications = $notificationService->checkAndNotifyHighWalletBalances($threshold);
        
        $this->info("✅ {$notifications->count()} notifications créées pour wallets élevés.");
        
        return Command::SUCCESS;
    }
}