<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PickupRequest;
use App\Models\Delegation;
use App\Models\User;

class FixPickupsAndDelegations extends Command
{
    protected $signature = 'fix:pickups-complete';
    protected $description = 'Corriger complètement les pickups avec des délégations valides';

    public function handle()
    {
        $this->info('=== CORRECTION COMPLETE PICKUPS ===');
        $this->newLine();

        // 1. Trouver les gouvernorats des livreurs
        $deliverers = User::where('role', 'DELIVERER')
            ->whereNotNull('deliverer_gouvernorats')
            ->get();

        $availableGovernments = [];
        foreach ($deliverers as $deliverer) {
            $govs = $deliverer->deliverer_gouvernorats;
            if (is_string($govs)) {
                $govs = json_decode($govs, true) ?? [];
            }
            if (is_array($govs)) {
                $availableGovernments = array_merge($availableGovernments, $govs);
            }
        }
        $availableGovernments = array_unique($availableGovernments);

        $this->info('✅ Gouvernorats des livreurs: ' . implode(', ', $availableGovernments));
        $this->newLine();

        // 2. Trouver une délégation valide pour chaque gouvernorat
        foreach ($availableGovernments as $gov) {
            $delegation = Delegation::where('zone', $gov)
                ->where('active', true)
                ->first();
            
            if ($delegation) {
                $this->info("✅ {$gov}: Délégation '{$delegation->name}' (ID: {$delegation->id})");
            } else {
                $this->warn("⚠️  {$gov}: Aucune délégation trouvée");
            }
        }

        $this->newLine();

        // 3. Assigner une délégation valide à chaque pickup
        $pickups = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
            ->whereNull('assigned_deliverer_id')
            ->get();

        $this->info("📦 {$pickups->count()} pickup(s) à corriger");
        $this->newLine();

        foreach ($pickups as $pickup) {
            // Essayer de trouver une délégation dans les gouvernorats disponibles
            $delegation = null;
            foreach ($availableGovernments as $gov) {
                $delegation = Delegation::where('zone', $gov)
                    ->where('active', true)
                    ->first();
                if ($delegation) break;
            }

            if ($delegation) {
                $pickup->update(['delegation_id' => $delegation->id]);
                $this->line("  ✅ Pickup {$pickup->id} → Délégation {$delegation->name} ({$delegation->zone})");
            } else {
                // Fallback: première délégation active
                $fallback = Delegation::where('active', true)
                    ->whereNotNull('zone')
                    ->where('zone', '!=', '')
                    ->first();
                
                if ($fallback) {
                    $pickup->update(['delegation_id' => $fallback->id]);
                    $this->line("  ⚠️  Pickup {$pickup->id} → Fallback: {$fallback->name} ({$fallback->zone})");
                } else {
                    $this->error("  ❌ Pickup {$pickup->id} → Aucune délégation disponible");
                }
            }
        }

        // 4. Vérification finale
        $this->newLine();
        $available = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
            ->whereNull('assigned_deliverer_id')
            ->whereHas('delegation', function($q) {
                $q->whereNotNull('zone')->where('zone', '!=', '');
            })
            ->count();

        $this->info("✅ Pickups disponibles avec gouvernorat valide: {$available}");

        // 5. Test de matching
        $this->newLine();
        $this->info('🔍 Test de matching avec livreurs:');
        foreach ($deliverers as $deliverer) {
            $govs = $deliverer->deliverer_gouvernorats;
            if (is_string($govs)) {
                $govs = json_decode($govs, true) ?? [];
            }
            
            $count = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
                ->whereNull('assigned_deliverer_id')
                ->whereHas('delegation', function($q) use ($govs) {
                    $q->whereIn('zone', $govs);
                })
                ->count();

            $this->line("  - {$deliverer->name}: {$count} pickup(s) visibles");
        }

        $this->newLine();
        $this->info('=== CORRECTION TERMINÉE ===');

        return 0;
    }
}
