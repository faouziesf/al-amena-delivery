<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMileageReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'mileage',
        'reading_date',
        'fuel_cost',
        'fuel_liters',
        'km_since_last_reading',
        'working_days_since_last',
        'avg_daily_km',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'mileage' => 'integer',
        'reading_date' => 'datetime',
        'fuel_cost' => 'decimal:3',
        'fuel_liters' => 'decimal:2',
        'km_since_last_reading' => 'integer',
        'working_days_since_last' => 'integer',
        'avg_daily_km' => 'decimal:2',
    ];

    // Relations
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Calculs automatiques lors de la création
        static::creating(function ($reading) {
            $reading->calculateMetrics();
            $reading->updateVehicleData();
        });

        static::created(function ($reading) {
            // Créer des alertes de maintenance si nécessaire
            $reading->checkMaintenanceAlerts();
        });
    }

    /**
     * Calcule les métriques du relevé (km depuis dernier, jours, moyenne)
     */
    public function calculateMetrics()
    {
        $previousReading = VehicleMileageReading::where('vehicle_id', $this->vehicle_id)
            ->where('reading_date', '<', $this->reading_date)
            ->orderBy('reading_date', 'desc')
            ->first();

        if ($previousReading) {
            // Calcul du kilométrage parcouru
            $this->km_since_last_reading = $this->mileage - $previousReading->mileage;

            // Calcul des jours ouvrables entre les deux relevés (6 jours/semaine)
            $this->working_days_since_last = $this->calculateWorkingDays(
                $previousReading->reading_date,
                $this->reading_date
            );

            // Calcul de la moyenne journalière
            if ($this->working_days_since_last > 0) {
                $this->avg_daily_km = $this->km_since_last_reading / $this->working_days_since_last;
            }

            // Si fuel_cost est fourni, calcul des litres
            if ($this->fuel_cost && $this->vehicle->fuel_price_per_liter > 0) {
                $this->fuel_liters = $this->fuel_cost / $this->vehicle->fuel_price_per_liter;
                
                // Mise à jour de la consommation moyenne du véhicule
                if ($this->km_since_last_reading > 0) {
                    $consumption = ($this->fuel_liters / $this->km_since_last_reading) * 100;
                    $this->updateVehicleConsumption($consumption);
                }
            }
        } else {
            // Premier relevé
            $this->km_since_last_reading = 0;
            $this->working_days_since_last = 0;
            $this->avg_daily_km = 0;
        }
    }

    /**
     * Met à jour les données du véhicule
     */
    public function updateVehicleData()
    {
        $this->vehicle->update([
            'current_km' => $this->mileage,
        ]);
    }

    /**
     * Met à jour la consommation moyenne du véhicule
     */
    private function updateVehicleConsumption($newConsumption)
    {
        // Calcul de la moyenne mobile avec les 5 derniers relevés
        $recentReadings = VehicleMileageReading::where('vehicle_id', $this->vehicle_id)
            ->whereNotNull('fuel_liters')
            ->where('km_since_last_reading', '>', 0)
            ->orderBy('reading_date', 'desc')
            ->take(5)
            ->get();

        if ($recentReadings->count() > 0) {
            $totalConsumption = $recentReadings->sum(function($reading) {
                return ($reading->fuel_liters / $reading->km_since_last_reading) * 100;
            });
            
            $avgConsumption = $totalConsumption / $recentReadings->count();
            
            $this->vehicle->update([
                'average_consumption_per_100km' => round($avgConsumption, 2),
            ]);
        }
    }

    /**
     * Calcule le nombre de jours ouvrables entre deux dates (6 jours/semaine, excluant dimanche)
     */
    private function calculateWorkingDays($startDate, $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $workingDays = 0;
        
        $current = $start->copy();
        while ($current <= $end) {
            // Exclure le dimanche (0)
            if ($current->dayOfWeek !== 0) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Vérifie et crée des alertes de maintenance si nécessaire
     */
    public function checkMaintenanceAlerts()
    {
        $vehicle = $this->vehicle;
        $threshold = 500; // Seuil d'alerte en km

        // Vérification vidange
        if ($vehicle->isMaintenanceDue('oil', $threshold)) {
            VehicleMaintenanceAlert::createAlert(
                $vehicle,
                'OIL_CHANGE',
                'Vidange nécessaire',
                "Le véhicule {$vehicle->name} nécessite une vidange. Kilométrage restant: {$vehicle->km_until_oil_change} km",
                $vehicle->km_until_oil_change <= 100 ? 'CRITICAL' : 'WARNING'
            );
        }

        // Vérification bougies
        if ($vehicle->isMaintenanceDue('spark_plug', $threshold)) {
            VehicleMaintenanceAlert::createAlert(
                $vehicle,
                'SPARK_PLUGS',
                'Changement de bougies nécessaire',
                "Le véhicule {$vehicle->name} nécessite un changement de bougies. Kilométrage restant: {$vehicle->km_until_spark_plug_change} km",
                $vehicle->km_until_spark_plug_change <= 100 ? 'CRITICAL' : 'WARNING'
            );
        }

        // Vérification pneus
        if ($vehicle->isMaintenanceDue('tire', $threshold)) {
            VehicleMaintenanceAlert::createAlert(
                $vehicle,
                'TIRES',
                'Changement de pneus nécessaire',
                "Le véhicule {$vehicle->name} nécessite un changement de pneus. Kilométrage restant: {$vehicle->km_until_tire_change} km",
                $vehicle->km_until_tire_change <= 100 ? 'CRITICAL' : 'WARNING'
            );
        }
    }

    // Scopes
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeWithFuelData($query)
    {
        return $query->whereNotNull('fuel_cost')->whereNotNull('fuel_liters');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('reading_date', '>=', now()->subDays($days));
    }
}
