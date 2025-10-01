<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;

class ProcessDailyWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:process-daily {--force : Force execution even if not end of day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily withdrawals - Mark PROCESSED bank transfers as DELIVERED at end of day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Traitement des retraits quotidiens...');

        // Vérifier si c'est la fin de journée (après 18h) ou si forcé
        $currentHour = Carbon::now()->hour;
        if ($currentHour < 18 && !$this->option('force')) {
            $this->warn('⏰ Le traitement automatique se fait après 18h. Utilisez --force pour forcer l\'exécution.');
            return Command::FAILURE;
        }

        // Traiter les virements bancaires "PROCESSED" -> "DELIVERED"
        $processedBankTransfers = WithdrawalRequest::where('status', 'PROCESSED')
            ->where('method', 'BANK_TRANSFER')
            ->whereDate('processed_at', '<=', Carbon::today())
            ->get();

        $count = 0;
        foreach ($processedBankTransfers as $withdrawal) {
            try {
                $withdrawal->markAsDeliveredFinal([
                    'auto_processed' => true,
                    'processed_by_system' => true,
                    'processed_at' => now()
                ]);

                $this->info("✅ Virement #{$withdrawal->request_code} marqué comme livré");
                $count++;
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour #{$withdrawal->request_code}: " . $e->getMessage());
            }
        }

        // Résumé
        $this->info("📊 Résumé du traitement:");
        $this->info("   • Virements bancaires traités: {$count}");

        if ($count > 0) {
            $this->info("🎉 Traitement terminé avec succès !");
        } else {
            $this->info("ℹ️  Aucun virement à traiter aujourd'hui.");
        }

        return Command::SUCCESS;
    }
}
