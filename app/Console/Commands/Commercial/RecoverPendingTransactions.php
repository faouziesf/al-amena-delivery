<?php

namespace App\Console\Commands\Commercial;

use Illuminate\Console\Command;
use App\Services\FinancialTransactionService;

class RecoverPendingTransactions extends Command
{
    protected $signature = 'commercial:recover-transactions';
    protected $description = 'Récupérer les transactions financières bloquées en statut PENDING';

    public function handle(FinancialTransactionService $financialService)
    {
        $this->info("🔄 Récupération des transactions bloquées...");
        
        $recoveredCount = $financialService->recoverPendingTransactions();
        
        if ($recoveredCount > 0) {
            $this->warn("⚠️  {$recoveredCount} transactions récupérées automatiquement");
        } else {
            $this->info("✅ Aucune transaction bloquée détectée");
        }
        
        return Command::SUCCESS;
    }
}