<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'registration_number',
        'purchase_price',
        'purchase_date',
        'max_depreciation_km',
        'current_km',
        'oil_change_cost',
        'oil_change_interval_km',
        'last_oil_change_km',
        'spark_plug_cost',
        'spark_plug_interval_km',
        'last_spark_plug_change_km',
        'tire_unit_cost',
        'tire_change_interval_km',
        'last_tire_change_km',
        'fuel_price_per_liter',
        'average_consumption_per_100km',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:3',
        'purchase_date' => 'date',
        'max_depreciation_km' => 'integer',
        'current_km' => 'integer',
        'oil_change_cost' => 'decimal:3',
        'oil_change_interval_km' => 'integer',
        'last_oil_change_km' => 'integer',
        'spark_plug_cost' => 'decimal:3',
        'spark_plug_interval_km' => 'integer',
        'last_spark_plug_change_km' => 'integer',
        'tire_unit_cost' => 'decimal:3',
        'tire_change_interval_km' => 'integer',
        'last_tire_change_km' => 'integer',
        'fuel_price_per_liter' => 'decimal:3',
        'average_consumption_per_100km' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mileageReadings()
    {
        return $this->hasMany(VehicleMileageReading::class)->orderBy('reading_date', 'desc');
    }

    public function maintenanceAlerts()
    {
        return $this->hasMany(VehicleMaintenanceAlert::class)->orderBy('created_at', 'desc');
    }

    /**
     * Calcule le coût d'amortissement par kilomètre
     */
    public function getDepreciationCostPerKmAttribute(): float
    {
        if ($this->max_depreciation_km <= 0) {
            return 0;
        }
        
        return $this->purchase_price / $this->max_depreciation_km;
    }

    /**
     * Calcule le coût de vidange par kilomètre
     */
    public function getOilChangeCostPerKmAttribute(): float
    {
        if ($this->oil_change_interval_km <= 0) {
            return 0;
        }
        
        return $this->oil_change_cost / $this->oil_change_interval_km;
    }

    /**
     * Calcule le coût de bougies par kilomètre
     */
    public function getSparkPlugCostPerKmAttribute(): float
    {
        if ($this->spark_plug_interval_km <= 0) {
            return 0;
        }
        
        return $this->spark_plug_cost / $this->spark_plug_interval_km;
    }

    /**
     * Calcule le coût de pneus par kilomètre (4 pneus)
     */
    public function getTireCostPerKmAttribute(): float
    {
        if ($this->tire_change_interval_km <= 0) {
            return 0;
        }
        
        return ($this->tire_unit_cost * 4) / $this->tire_change_interval_km;
    }

    /**
     * Calcule le coût de carburant par kilomètre
     */
    public function getFuelCostPerKmAttribute(): float
    {
        if (!$this->average_consumption_per_100km || $this->average_consumption_per_100km <= 0) {
            return 0;
        }
        
        return ($this->fuel_price_per_liter * $this->average_consumption_per_100km) / 100;
    }

    /**
     * Calcule le coût total par kilomètre
     */
    public function getTotalCostPerKmAttribute(): float
    {
        return $this->depreciation_cost_per_km
            + $this->oil_change_cost_per_km
            + $this->spark_plug_cost_per_km
            + $this->tire_cost_per_km
            + $this->fuel_cost_per_km;
    }

    /**
     * Calcule le kilométrage restant avant vidange
     */
    public function getKmUntilOilChangeAttribute(): int
    {
        return max(0, ($this->last_oil_change_km + $this->oil_change_interval_km) - $this->current_km);
    }

    /**
     * Calcule le kilométrage restant avant changement de bougies
     */
    public function getKmUntilSparkPlugChangeAttribute(): int
    {
        return max(0, ($this->last_spark_plug_change_km + $this->spark_plug_interval_km) - $this->current_km);
    }

    /**
     * Calcule le kilométrage restant avant changement de pneus
     */
    public function getKmUntilTireChangeAttribute(): int
    {
        return max(0, ($this->last_tire_change_km + $this->tire_change_interval_km) - $this->current_km);
    }

    /**
     * Vérifie si une maintenance est proche
     * 
     * @param string $type (oil, spark_plug, tire)
     * @param int $threshold Seuil en km pour considérer la maintenance proche
     * @return bool
     */
    public function isMaintenanceDue($type, $threshold = 500): bool
    {
        return match($type) {
            'oil' => $this->km_until_oil_change <= $threshold,
            'spark_plug' => $this->km_until_spark_plug_change <= $threshold,
            'tire' => $this->km_until_tire_change <= $threshold,
            default => false,
        };
    }

    /**
     * Calcule le coût variable total pour une période basée sur le kilométrage moyen journalier
     * 
     * @param int $workingDays Nombre de jours ouvrables
     * @param float $avgDailyKm Kilométrage moyen journalier
     * @return float
     */
    public function calculateVariableCostForPeriod($workingDays, $avgDailyKm = null): float
    {
        if (is_null($avgDailyKm)) {
            $avgDailyKm = $this->calculateAverageDailyKm();
        }
        
        $totalKm = $workingDays * $avgDailyKm;
        
        return $totalKm * $this->total_cost_per_km;
    }

    /**
     * Calcule le kilométrage moyen journalier basé sur les derniers relevés
     */
    public function calculateAverageDailyKm(): float
    {
        $lastReading = $this->mileageReadings()->first();
        
        if (!$lastReading || !$lastReading->avg_daily_km) {
            return 0;
        }
        
        return $lastReading->avg_daily_km;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsMaintenance($query, $type = null, $threshold = 500)
    {
        if ($type === 'oil') {
            return $query->whereRaw('(last_oil_change_km + oil_change_interval_km - current_km) <= ?', [$threshold]);
        } elseif ($type === 'spark_plug') {
            return $query->whereRaw('(last_spark_plug_change_km + spark_plug_interval_km - current_km) <= ?', [$threshold]);
        } elseif ($type === 'tire') {
            return $query->whereRaw('(last_tire_change_km + tire_change_interval_km - current_km) <= ?', [$threshold]);
        }
        
        // Retourne tous les véhicules nécessitant une maintenance
        return $query->where(function($q) use ($threshold) {
            $q->whereRaw('(last_oil_change_km + oil_change_interval_km - current_km) <= ?', [$threshold])
              ->orWhereRaw('(last_spark_plug_change_km + spark_plug_interval_km - current_km) <= ?', [$threshold])
              ->orWhereRaw('(last_tire_change_km + tire_change_interval_km - current_km) <= ?', [$threshold]);
        });
    }
}
