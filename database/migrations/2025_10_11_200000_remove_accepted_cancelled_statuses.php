<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * REFACTORISATION MAJEURE:
     * - Suppression du statut ACCEPTED (remplacé par OUT_FOR_DELIVERY direct)
     * - Suppression du statut CANCELLED pour les packages (conservé pour TopupRequest/WithdrawalRequest)
     * - Migration des données existantes vers les nouveaux statuts
     */
    public function up(): void
    {
        // 1. Migrer les colis ACCEPTED vers OUT_FOR_DELIVERY
        DB::table('packages')
            ->where('status', 'ACCEPTED')
            ->update([
                'status' => 'OUT_FOR_DELIVERY',
                'updated_at' => now()
            ]);

        \Log::info('Migration: Colis ACCEPTED migrés vers OUT_FOR_DELIVERY', [
            'count' => DB::table('packages')->where('status', 'OUT_FOR_DELIVERY')->count()
        ]);

        // 2. Migrer les colis CANCELLED vers PROBLEM (à analyser au cas par cas)
        // On garde une trace de l'annulation dans les notes
        $cancelledPackages = DB::table('packages')
            ->where('status', 'CANCELLED')
            ->get();

        foreach ($cancelledPackages as $package) {
            DB::table('packages')
                ->where('id', $package->id)
                ->update([
                    'status' => 'PROBLEM',
                    'notes' => ($package->notes ? $package->notes . "\n\n" : '') . 
                               "[MIGRATION] Anciennement CANCELLED - Raison: " . 
                               ($package->cancellation_reason ?? 'Non spécifiée'),
                    'updated_at' => now()
                ]);
        }

        \Log::info('Migration: Colis CANCELLED migrés vers PROBLEM', [
            'count' => count($cancelledPackages)
        ]);

        // 3. Mettre à jour l'historique des statuts (previous_status et new_status)
        // Compatible SQLite et MySQL
        $concatOperator = DB::connection()->getDriverName() === 'sqlite' ? '||' : 'CONCAT';
        
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite utilise || pour la concaténation
            DB::table('package_status_histories')
                ->where('previous_status', 'ACCEPTED')
                ->update([
                    'previous_status' => 'OUT_FOR_DELIVERY',
                    'notes' => DB::raw("COALESCE(notes, '') || ' [Migré de ACCEPTED]'")
                ]);

            DB::table('package_status_histories')
                ->where('new_status', 'ACCEPTED')
                ->update([
                    'new_status' => 'OUT_FOR_DELIVERY',
                    'notes' => DB::raw("COALESCE(notes, '') || ' [Migré de ACCEPTED]'")
                ]);

            DB::table('package_status_histories')
                ->where('previous_status', 'CANCELLED')
                ->update([
                    'previous_status' => 'PROBLEM',
                    'notes' => DB::raw("COALESCE(notes, '') || ' [Migré de CANCELLED]'")
                ]);

            DB::table('package_status_histories')
                ->where('new_status', 'CANCELLED')
                ->update([
                    'new_status' => 'PROBLEM',
                    'notes' => DB::raw("COALESCE(notes, '') || ' [Migré de CANCELLED]'")
                ]);
        } else {
            // MySQL utilise CONCAT
            DB::table('package_status_histories')
                ->where('previous_status', 'ACCEPTED')
                ->update([
                    'previous_status' => 'OUT_FOR_DELIVERY',
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [Migré de ACCEPTED]')")
                ]);

            DB::table('package_status_histories')
                ->where('new_status', 'ACCEPTED')
                ->update([
                    'new_status' => 'OUT_FOR_DELIVERY',
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [Migré de ACCEPTED]')")
                ]);

            DB::table('package_status_histories')
                ->where('previous_status', 'CANCELLED')
                ->update([
                    'previous_status' => 'PROBLEM',
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [Migré de CANCELLED]')")
                ]);

            DB::table('package_status_histories')
                ->where('new_status', 'CANCELLED')
                ->update([
                    'new_status' => 'PROBLEM',
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' [Migré de CANCELLED]')")
                ]);
        }

        \Log::info('Migration: Historique des statuts mis à jour');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: La migration inverse n'est pas recommandée car elle pourrait causer des incohérences
        // Si vraiment nécessaire, il faudrait analyser les notes pour restaurer les anciens statuts
        
        \Log::warning('Migration inverse non implémentée pour éviter les incohérences de données');
    }
};
