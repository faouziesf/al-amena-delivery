<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Delegation;
use Illuminate\Support\Facades\DB;

class CheckDelegations extends Command
{
    protected $signature = 'check:delegations';
    protected $description = 'Vérifier les délégations et leurs gouvernorats';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC DELEGATIONS ===');
        $this->newLine();

        // Total
        $total = Delegation::count();
        $this->info("📊 Total délégations: $total");

        // Avec governorate non null
        $withGov = Delegation::whereNotNull('governorate')->count();
        $this->info("✅ Avec governorate NOT NULL: $withGov");

        // Avec governorate vide
        $emptyGov = Delegation::where('governorate', '')->count();
        $this->info("⚠️  Avec governorate = '' (vide): $emptyGov");

        // NULL governorate
        $nullGov = Delegation::whereNull('governorate')->count();
        $this->info("❌ Avec governorate NULL: $nullGov");

        // Actives
        $active = Delegation::where('active', true)->count();
        $this->info("🟢 Délégations actives: $active");

        $this->newLine();
        $this->info('📋 Exemple de délégations actives:');
        
        $examples = Delegation::where('active', true)->limit(10)->get();
        foreach ($examples as $del) {
            $gov = $del->governorate ?? 'NULL';
            if ($gov === '') $gov = '(VIDE)';
            $this->line("  - {$del->name} | Gov: {$gov}");
        }

        $this->newLine();
        $this->info('🎯 Gouvernorats uniques:');
        $governorates = Delegation::whereNotNull('governorate')
            ->where('governorate', '!=', '')
            ->distinct()
            ->pluck('governorate')
            ->take(20);
        
        foreach ($governorates as $gov) {
            $count = Delegation::where('governorate', $gov)->count();
            $this->line("  - {$gov}: {$count} délégation(s)");
        }

        $this->newLine();
        $this->info('=== FIN DIAGNOSTIC ===');
    }
}
