<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PickupRequest;
use App\Models\User;

class DiagnosePickups extends Command
{
    protected $signature = 'diagnose:pickups';
    protected $description = 'Diagnostiquer pourquoi les pickups ne s\'affichent pas';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC PICKUPS ===');
        $this->newLine();

        // 1. Total pickups
        $total = PickupRequest::count();
        $this->info("📦 Total pickups en BDD: $total");

        // 2. Pickups disponibles
        $available = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
            ->whereNull('assigned_deliverer_id')
            ->count();
        $this->info("✅ Pickups disponibles: $available");

        // 3. Détails des pickups disponibles
        $this->newLine();
        $this->info("📋 Détails des pickups disponibles:");
        $pickups = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
            ->whereNull('assigned_deliverer_id')
            ->with('delegation')
            ->get();

        foreach ($pickups as $pickup) {
            $gov = $pickup->delegation ? $pickup->delegation->governorate : 'NULL';
            $this->line("  - ID: {$pickup->id} | Status: {$pickup->status} | Gouvernorat: {$gov}");
        }

        // 4. Livreurs et leurs gouvernorats
        $this->newLine();
        $this->info("👷 Livreurs et leurs gouvernorats:");
        $deliverers = User::where('role', 'DELIVERER')->get();

        foreach ($deliverers as $deliverer) {
            $govs = $deliverer->deliverer_gouvernorats;
            if (is_string($govs)) {
                $govs = json_decode($govs, true) ?? [];
            }
            $govList = is_array($govs) ? implode(', ', $govs) : 'AUCUN';
            $this->line("  - {$deliverer->name} (ID: {$deliverer->id}): {$govList}");
        }

        // 5. Test de matching
        $this->newLine();
        $this->info("🔍 Test de matching pickups <-> livreurs:");
        
        foreach ($deliverers as $deliverer) {
            $govs = $deliverer->deliverer_gouvernorats;
            if (is_string($govs)) {
                $govs = json_decode($govs, true) ?? [];
            }
            
            if (empty($govs)) {
                $this->warn("  ⚠️  {$deliverer->name}: Aucun gouvernorat configuré!");
                continue;
            }

            $matchingPickups = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
                ->whereNull('assigned_deliverer_id')
                ->whereHas('delegation', function($q) use ($govs) {
                    $q->whereIn('governorate', $govs);
                })
                ->count();

            $this->line("  - {$deliverer->name}: {$matchingPickups} pickup(s) dans ses gouvernorats");
        }

        $this->newLine();
        $this->info('=== FIN DIAGNOSTIC ===');
    }
}
