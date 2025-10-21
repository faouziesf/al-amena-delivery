<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la table return_packages existe
        if (!Schema::hasTable('return_packages')) {
            return; // Si elle n'existe pas, on passe
        }

        // Migrer les données de return_packages vers packages
        $returnPackages = DB::table('return_packages')->get();
        
        foreach ($returnPackages as $returnPackage) {
            // Récupérer le colis original pour copier les informations
            $originalPackage = DB::table('packages')->where('id', $returnPackage->original_package_id)->first();
            
            if (!$originalPackage) {
                continue; // Si le colis original n'existe pas, on saute
            }
            
            // Insérer dans packages avec type RETURN
            DB::table('packages')->insert([
                'package_code' => $returnPackage->return_package_code,
                'package_type' => 'RETURN',
                'return_package_code' => $returnPackage->return_package_code,
                'original_package_id' => $returnPackage->original_package_id,
                'return_reason' => $returnPackage->reason ?? 'Non spécifié',
                'return_notes' => $returnPackage->notes ?? null,
                'return_requested_at' => $returnPackage->requested_at ?? null,
                'return_accepted_at' => $returnPackage->accepted_at ?? null,
                
                // Copier les données du destinataire original (qui devient expéditeur pour le retour)
                'sender_id' => $originalPackage->sender_id,
                'sender_data' => $originalPackage->recipient_data, // Le destinataire devient expéditeur
                'delegation_from' => $originalPackage->delegation_to, // Inversion
                
                // Le destinataire du retour est l'expéditeur original
                'recipient_data' => $originalPackage->sender_data, // L'expéditeur devient destinataire
                'delegation_to' => $originalPackage->delegation_from, // Inversion
                
                'content_description' => 'Colis de retour - ' . ($originalPackage->content_description ?? ''),
                'status' => $returnPackage->status ?? 'CREATED',
                
                // Pas de COD pour les retours
                'cod_amount' => 0,
                'delivery_fee' => $returnPackage->return_fee ?? 0,
                'return_fee' => 0,
                
                // Livreur assigné
                'assigned_deliverer_id' => $returnPackage->assigned_deliverer_id ?? null,
                'assigned_at' => $returnPackage->assigned_at ?? null,
                
                // Dates
                'created_at' => $returnPackage->created_at ?? now(),
                'updated_at' => $returnPackage->updated_at ?? now(),
            ]);
        }
        
        // Supprimer la table return_packages
        Schema::dropIfExists('return_packages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer la table return_packages
        Schema::create('return_packages', function (Blueprint $table) {
            $table->id();
            $table->string('return_package_code', 50)->unique();
            $table->unsignedBigInteger('original_package_id');
            $table->string('reason', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('AWAITING_RETURN');
            $table->decimal('return_fee', 10, 3)->default(0);
            $table->unsignedBigInteger('assigned_deliverer_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('return_package_code');
            $table->index('original_package_id');
            $table->index('status');
        });
        
        // Restaurer les données depuis packages (type RETURN)
        $returnPackages = DB::table('packages')->where('package_type', 'RETURN')->get();
        
        foreach ($returnPackages as $package) {
            DB::table('return_packages')->insert([
                'return_package_code' => $package->return_package_code,
                'original_package_id' => $package->original_package_id,
                'reason' => $package->return_reason,
                'notes' => $package->return_notes,
                'status' => $package->status,
                'return_fee' => $package->delivery_fee,
                'assigned_deliverer_id' => $package->assigned_deliverer_id,
                'assigned_at' => $package->assigned_at,
                'requested_at' => $package->return_requested_at,
                'accepted_at' => $package->return_accepted_at,
                'created_at' => $package->created_at,
                'updated_at' => $package->updated_at,
                'deleted_at' => $package->deleted_at,
            ]);
        }
        
        // Supprimer les colis de type RETURN de packages
        DB::table('packages')->where('package_type', 'RETURN')->delete();
    }
};
