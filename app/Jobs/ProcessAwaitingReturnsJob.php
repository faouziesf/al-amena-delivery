<?php

namespace App\Jobs;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAwaitingReturnsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * Ce job s'exécute toutes les heures et passe les colis de
     * AWAITING_RETURN → RETURN_IN_PROGRESS après 48 heures
     */
    public function handle(): void
    {
        // Récupérer les colis en attente de retour depuis plus de 48h
        $packages = Package::where('status', 'AWAITING_RETURN')
            ->where('awaiting_return_since', '<=', now()->subHours(48))
            ->get();

        $processedCount = 0;

        foreach ($packages as $package) {
            try {
                $package->update([
                    'status' => 'RETURN_IN_PROGRESS',
                    'return_in_progress_since' => now()
                ]);

                Log::info('Colis passé en RETURN_IN_PROGRESS', [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'awaiting_since' => $package->awaiting_return_since,
                    'return_reason' => $package->return_reason
                ]);

                $processedCount++;

                // TODO: Envoyer notification au commercial
                // event(new PackageStatusChanged($package, 'AWAITING_RETURN', 'RETURN_IN_PROGRESS'));

            } catch (\Exception $e) {
                Log::error('Erreur lors du traitement du retour en attente', [
                    'package_id' => $package->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($processedCount > 0) {
            Log::info("Job ProcessAwaitingReturnsJob terminé", [
                'processed_count' => $processedCount,
                'total_found' => $packages->count()
            ]);
        }
    }
}
