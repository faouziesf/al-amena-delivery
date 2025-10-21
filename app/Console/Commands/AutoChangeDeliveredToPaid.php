<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use App\Models\ActionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoChangeDeliveredToPaid extends Command
{
    protected $signature = 'auto:delivered-to-paid {--dry-run : Tester sans modifier}';
    protected $description = 'Changer automatiquement les colis DELIVERED en PAID à 20:00';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 MODE TEST - Aucune modification ne sera effectuée');
            $this->newLine();
        }

        $this->info('=== CHANGEMENT AUTO DELIVERED → PAID ===');
        $this->info('⏰ Heure: ' . now()->format('H:i:s'));
        $this->newLine();

        // Trouver les colis DELIVERED depuis plus de 24h
        $packages = Package::where('status', 'DELIVERED')
            ->where('delivered_at', '<=', now()->subHours(24))
            ->with(['sender', 'assignedDeliverer'])
            ->get();

        $this->info("📦 Colis trouvés: {$packages->count()}");
        
        if ($packages->isEmpty()) {
            $this->info('✅ Aucun colis à traiter');
            return 0;
        }

        $this->newLine();
        $this->info('📋 Détails des colis:');
        
        $successCount = 0;
        $errorCount = 0;

        foreach ($packages as $package) {
            $age = now()->diffInHours($package->delivered_at);
            $this->line("  - {$package->package_code} | Livré il y a {$age}h | Client: {$package->sender->name}");

            if (!$dryRun) {
                try {
                    DB::beginTransaction();

                    // Mettre à jour le statut
                    $package->update(['status' => 'PAID']);

                    // Logger l'action
                    ActionLog::create([
                        'user_id' => null,
                        'user_role' => 'SYSTEM',
                        'action_type' => 'PACKAGE_AUTO_PAID',
                        'target_type' => 'Package',
                        'target_id' => $package->id,
                        'old_value' => json_encode(['status' => 'DELIVERED']),
                        'new_value' => json_encode(['status' => 'PAID']),
                        'additional_data' => json_encode([
                            'description' => "Changement automatique DELIVERED → PAID ({$package->package_code})",
                            'delivered_at' => $package->delivered_at,
                            'hours_elapsed' => $age
                        ]),
                        'ip_address' => request()->ip() ?? '127.0.0.1',
                        'user_agent' => 'System Cron Job',
                    ]);

                    DB::commit();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("    ❌ Erreur: {$e->getMessage()}");
                    Log::error('Erreur auto DELIVERED->PAID', [
                        'package_id' => $package->id,
                        'error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("✅ Succès: {$successCount}");
            if ($errorCount > 0) {
                $this->error("❌ Erreurs: {$errorCount}");
            }

            Log::info('Auto DELIVERED->PAID exécuté', [
                'total' => $packages->count(),
                'success' => $successCount,
                'errors' => $errorCount
            ]);
        } else {
            $this->newLine();
            $this->warn('🔍 MODE TEST - {$packages->count()} colis seraient traités');
        }

        $this->newLine();
        $this->info('=== TERMINÉ ===');

        return 0;
    }
}
