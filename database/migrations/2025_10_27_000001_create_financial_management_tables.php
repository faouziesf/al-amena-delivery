<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table des charges fixes
        Schema::create('fixed_charges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 16, 3); // Montant de la charge
            $table->enum('periodicity', ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']); // Périodicité
            $table->decimal('monthly_equivalent', 16, 3); // Équivalent mensuel calculé automatiquement
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            $table->index('periodicity');
        });

        // Table des actifs amortissables (équipements, mobilier, etc.)
        Schema::create('depreciable_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_cost', 16, 3); // Coût d'achat
            $table->integer('depreciation_years'); // Durée d'amortissement en années
            $table->decimal('monthly_cost', 16, 3); // Coût mensuel calculé (linéaire)
            $table->date('purchase_date'); // Date d'achat
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            $table->index('purchase_date');
        });

        // Table des véhicules
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom/Modèle du véhicule
            $table->string('registration_number')->nullable(); // Immatriculation
            $table->decimal('purchase_price', 16, 3); // Prix d'achat
            $table->date('purchase_date')->nullable(); // Date d'achat
            
            // Paramètres d'amortissement
            $table->integer('max_depreciation_km'); // Kilométrage max pour amortissement
            $table->integer('current_km')->default(0); // Kilométrage actuel
            
            // Paramètres de maintenance
            $table->decimal('oil_change_cost', 16, 3)->default(0); // Coût vidange
            $table->integer('oil_change_interval_km')->default(5000); // Intervalle vidange en km
            $table->integer('last_oil_change_km')->default(0); // Dernier kilométrage vidange
            
            $table->decimal('spark_plug_cost', 16, 3)->default(0); // Coût bougies
            $table->integer('spark_plug_interval_km')->default(20000); // Intervalle bougies en km
            $table->integer('last_spark_plug_change_km')->default(0); // Dernier kilométrage bougies
            
            $table->decimal('tire_unit_cost', 16, 3)->default(0); // Coût unitaire pneu
            $table->integer('tire_change_interval_km')->default(40000); // Intervalle pneus en km
            $table->integer('last_tire_change_km')->default(0); // Dernier kilométrage pneus
            
            // Paramètres carburant
            $table->decimal('fuel_price_per_liter', 16, 3)->default(0); // Prix carburant par litre
            $table->decimal('average_consumption_per_100km', 8, 2)->nullable(); // Consommation moyenne L/100km (calculée)
            
            // Statut
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            $table->index('registration_number');
        });

        // Table des relevés kilométriques
        Schema::create('vehicle_mileage_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->integer('mileage'); // Kilométrage relevé
            $table->dateTime('reading_date'); // Date et heure du relevé
            $table->decimal('fuel_cost', 16, 3)->nullable(); // Coût carburant dépensé (optionnel)
            $table->decimal('fuel_liters', 8, 2)->nullable(); // Litres de carburant (calculé si cost fourni)
            $table->integer('km_since_last_reading')->nullable(); // KM depuis dernier relevé (calculé)
            $table->integer('working_days_since_last')->nullable(); // Jours ouvrables depuis dernier relevé
            $table->decimal('avg_daily_km', 8, 2)->nullable(); // KM moyen journalier (calculé)
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('vehicle_id');
            $table->index('reading_date');
        });

        // Table pour les alertes de maintenance véhicules
        Schema::create('vehicle_maintenance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->enum('alert_type', ['OIL_CHANGE', 'SPARK_PLUGS', 'TIRES', 'GENERAL']); // Type d'alerte
            $table->string('title');
            $table->text('message');
            $table->integer('current_km'); // KM actuel au moment de l'alerte
            $table->integer('threshold_km'); // KM seuil pour cette alerte
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('read_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['vehicle_id', 'is_read']);
            $table->index('alert_type');
        });

        // Table de configuration pour les actions critiques à logger
        Schema::create('critical_action_config', function (Blueprint $table) {
            $table->id();
            $table->string('action_type'); // Type d'action (ex: 'user_role_changed', 'package_status_changed')
            $table->string('target_type')->nullable(); // Type de cible (ex: 'User', 'Package')
            $table->text('description');
            $table->boolean('is_critical')->default(true);
            $table->json('conditions')->nullable(); // Conditions JSON pour définir quand c'est critique
            $table->timestamps();
            
            $table->unique(['action_type', 'target_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('critical_action_config');
        Schema::dropIfExists('vehicle_maintenance_alerts');
        Schema::dropIfExists('vehicle_mileage_readings');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('depreciable_assets');
        Schema::dropIfExists('fixed_charges');
    }
};
