<?php

namespace App\Jobs;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReturnedPackagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * Ce job s'exécute toutes les heures et passe les colis de
     * RETURNED_TO_CLIENT → RETURN_CONFIRMED après 48 heures sans action du client
     */
    public function handle(): void
    {
        // Récupérer les colis retournés au client depuis plus de 48h sans action
        $packages = Package::where('status', 'RETURNED_TO_CLIENT')
            ->where('returned_to_client_at', '<=', now()->subHours(48))
            ->get();

        $confirmedCount = 0;

        foreach ($packages as $package) {
            try {
                $package->update([
                    'status' => 'RETURN_CONFIRMED'
                ]);

                Log::info('Retour auto-confirmé après 48h', [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'returned_to_client_at' => $package->returned_to_client_at,
                    'client_id' => $package->sender_id
                ]);

                $confirmedCount++;

                // TODO: Envoyer notification au client
                // event(new ReturnAutoConfirmed($package));

            } catch (\Exception $e) {
                Log::error('Erreur lors de la confirmation automatique du retour', [
                    'package_id' => $package->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($confirmedCount > 0) {
            Log::info("Job ProcessReturnedPackagesJob terminé", [
                'confirmed_count' => $confirmedCount,
                'total_found' => $packages->count()
            ]);
        }
    }
}
