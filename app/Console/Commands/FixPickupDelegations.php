<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PickupRequest;
use App\Models\Delegation;

class FixPickupDelegations extends Command
{
    protected $signature = 'fix:pickup-delegations';
    protected $description = 'Corriger les pickups avec delegation_id NULL ou invalide';

    public function handle()
    {
        $this->info('=== CORRECTION PICKUPS DELEGATIONS ===');
        $this->newLine();

        // 1. Trouver une délégation valide par défaut
        $defaultDelegation = Delegation::where('active', true)
            ->whereNotNull('governorate')
            ->where('governorate', '!=', '')
            ->first();

        if (!$defaultDelegation) {
            $this->error('❌ Aucune délégation valide trouvée !');
            return 1;
        }

        $this->info("✅ Délégation par défaut: {$defaultDelegation->name} ({$defaultDelegation->governorate})");
        $this->newLine();

        // 2. Corriger les pickups avec delegation_id NULL
        $nullDelegations = PickupRequest::whereNull('delegation_id')->count();
        if ($nullDelegations > 0) {
            $this->warn("⚠️  {$nullDelegations} pickup(s) avec delegation_id NULL");
            PickupRequest::whereNull('delegation_id')
                ->update(['delegation_id' => $defaultDelegation->id]);
            $this->info("✅ Corrigé : delegation_id mis à {$defaultDelegation->id}");
        }

        // 3. Corriger les pickups avec delegation invalide (governorate NULL)
        $invalidDelegations = PickupRequest::whereHas('delegation', function($q) {
            $q->whereNull('governorate');
        })->count();

        if ($invalidDelegations > 0) {
            $this->warn("⚠️  {$invalidDelegations} pickup(s) avec delegation invalide");
            PickupRequest::whereHas('delegation', function($q) {
                $q->whereNull('governorate');
            })->update(['delegation_id' => $defaultDelegation->id]);
            $this->info("✅ Corrigé : delegation_id mis à {$defaultDelegation->id}");
        }

        // 4. Vérification finale
        $this->newLine();
        $available = PickupRequest::whereIn('status', ['pending', 'awaiting_assignment'])
            ->whereNull('assigned_deliverer_id')
            ->whereHas('delegation', function($q) {
                $q->whereNotNull('governorate');
            })
            ->count();

        $this->info("✅ Pickups disponibles avec gouvernorat valide: {$available}");
        $this->newLine();
        $this->info('=== CORRECTION TERMINÉE ===');

        return 0;
    }
}
