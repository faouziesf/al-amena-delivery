<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoAssignPackagesToDeliverers extends Command
{
    protected $signature = 'auto:assign-packages {--dry-run : Tester sans modifier}';
    protected $description = 'Assigner automatiquement les colis AVAILABLE aux livreurs selon gouvernorat';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 MODE TEST - Aucune modification ne sera effectuée');
            $this->newLine();
        }

        $this->info('=== ASSIGNATION AUTO COLIS → LIVREURS ===');
        $this->info('⏰ Heure: ' . now()->format('H:i:s'));
        $this->newLine();

        // Trouver colis disponibles
        $packages = Package::where('status', 'AVAILABLE')
            ->whereNull('assigned_deliverer_id')
            ->with(['delegationTo'])
            ->get();

        $this->info("📦 Colis disponibles: {$packages->count()}");

        if ($packages->isEmpty()) {
            $this->info('✅ Aucun colis à assigner');
            return 0;
        }

        // Trouver livreurs actifs
        $deliverers = User::where('role', 'DELIVERER')
            ->where('status', 'active')
            ->get();

        $this->info("👷 Livreurs actifs: {$deliverers->count()}");
        $this->newLine();

        $assignedCount = 0;

        foreach ($packages as $package) {
            $packageGov = $package->delegationTo?->zone ?? null;
            
            if (!$packageGov) {
                $this->warn("  ⚠️  {$package->package_code}: Gouvernorat inconnu");
                continue;
            }

            // Trouver livreur correspondant
            $bestDeliverer = null;
            $minLoad = PHP_INT_MAX;

            foreach ($deliverers as $deliverer) {
                $govs = $deliverer->deliverer_gouvernorats ?? [];
                if (is_string($govs)) {
                    $govs = json_decode($govs, true) ?? [];
                }
                
                // Normaliser
                $govs = array_map(function($g) {
                    return strtoupper(str_replace(' ', '_', trim($g)));
                }, $govs);

                // Vérifier si livreur gère ce gouvernorat
                if (in_array($packageGov, $govs)) {
                    // Compter sa charge actuelle
                    $load = Package::where('assigned_deliverer_id', $deliverer->id)
                        ->whereIn('status', ['AVAILABLE', 'PICKED_UP', 'OUT_FOR_DELIVERY'])
                        ->count();

                    if ($load < $minLoad) {
                        $minLoad = $load;
                        $bestDeliverer = $deliverer;
                    }
                }
            }

            if ($bestDeliverer) {
                $this->line("  ✅ {$package->package_code} → {$bestDeliverer->name} (charge: {$minLoad})");
                
                if (!$dryRun) {
                    try {
                        $package->update([
                            'assigned_deliverer_id' => $bestDeliverer->id,
                            'assigned_at' => now(),
                            'status' => 'AVAILABLE'  // Reste AVAILABLE mais assigné
                        ]);
                        $assignedCount++;
                    } catch (\Exception $e) {
                        $this->error("    ❌ Erreur: {$e->getMessage()}");
                    }
                }
            } else {
                $this->warn("  ⚠️  {$package->package_code}: Aucun livreur disponible pour {$packageGov}");
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("✅ Colis assignés: {$assignedCount}");
            Log::info('Auto assignation colis', ['assigned' => $assignedCount]);
        } else {
            $this->newLine();
            $this->warn('🔍 MODE TEST terminé');
        }

        $this->newLine();
        $this->info('=== TERMINÉ ===');

        return 0;
    }
}
