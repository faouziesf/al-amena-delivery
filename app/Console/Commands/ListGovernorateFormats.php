<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Delegation;
use Illuminate\Support\Facades\DB;

class ListGovernorateFormats extends Command
{
    protected $signature = 'list:governorate-formats';
    protected $description = 'Lister les formats de gouvernorats dans la BDD';

    public function handle()
    {
        $this->info('=== FORMATS GOUVERNORATS ===');
        $this->newLine();

        // Obtenir toutes les zones uniques
        $zones = DB::table('delegations')
            ->whereNotNull('zone')
            ->where('zone', '!=', '')
            ->distinct()
            ->pluck('zone')
            ->sort()
            ->values();

        $this->info("📊 Total zones uniques: {$zones->count()}");
        $this->newLine();

        $this->info('📋 Liste des zones:');
        foreach ($zones as $zone) {
            $count = Delegation::where('zone', $zone)->count();
            $this->line("  - {$zone} ({$count} délégation(s))");
        }

        $this->newLine();
        $this->info('=== FIN LISTE ===');
    }
}
