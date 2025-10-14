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
     * RETURNED → RETURN_CONFIRMED après 48 heures sans action du client
     * 
     * REFACTORISATION: Utilise maintenant le statut RETURNED au lieu de RETURNED_TO_CLIENT
     */
    public function handle(): void
    {
        // Récupérer les colis retournés depuis plus de 48h sans action
        $packages = Package::where('status', 'RETURNED')
            ->where('returned_to_client_at', '<=', now()->subHours(48))
            ->whereNull('cancelled_at') // Ne pas traiter les colis annulés
            ->get();

        $confirmedCount = 0;

        foreach ($packages as $package) {
            try {
                // Mettre à jour le statut vers RETURN_CONFIRMED
                $package->update([
                    'status' => 'RETURN_CONFIRMED',
                    'updated_at' => now()
                ]);

                // Enregistrer dans l'historique
                $package->statusHistory()->create([
                    'status' => 'RETURN_CONFIRMED',
                    'changed_by' => null, // Changement automatique
                    'notes' => 'Confirmation automatique après 48 heures sans réclamation',
                    'created_at' => now()
                ]);

                Log::info('Retour auto-confirmé après 48h', [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'returned_to_client_at' => $package->returned_to_client_at,
                    'client_id' => $package->sender_id,
                    'hours_elapsed' => now()->diffInHours($package->returned_to_client_at)
                ]);

                $confirmedCount++;

            } catch (\Exception $e) {
                Log::error('Erreur lors de la confirmation automatique du retour', [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if ($confirmedCount > 0) {
            Log::info("Job ProcessReturnedPackagesJob terminé", [
                'confirmed_count' => $confirmedCount,
                'total_found' => $packages->count(),
                'execution_time' => now()
            ]);
        }
    }
}
