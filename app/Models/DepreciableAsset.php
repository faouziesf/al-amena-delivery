<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepreciableAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'purchase_cost',
        'depreciation_years',
        'monthly_cost',
        'purchase_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:3',
        'monthly_cost' => 'decimal:3',
        'depreciation_years' => 'integer',
        'purchase_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        // Calcul automatique du coût mensuel
        static::saving(function ($asset) {
            $asset->monthly_cost = $asset->calculateMonthlyCost();
        });
    }

    /**
     * Calcule le coût mensuel d'amortissement linéaire
     */
    public function calculateMonthlyCost(): float
    {
        if ($this->depreciation_years <= 0) {
            return 0;
        }
        
        return $this->purchase_cost / ($this->depreciation_years * 12);
    }

    /**
     * Calcule le montant amorti à ce jour
     */
    public function getDepreciatedAmountAttribute(): float
    {
        $monthsSincePurchase = \Carbon\Carbon::parse($this->purchase_date)
            ->diffInMonths(now());
        
        $totalMonths = $this->depreciation_years * 12;
        
        if ($monthsSincePurchase >= $totalMonths) {
            return $this->purchase_cost;
        }
        
        return $this->monthly_cost * $monthsSincePurchase;
    }

    /**
     * Calcule la valeur résiduelle actuelle
     */
    public function getResidualValueAttribute(): float
    {
        return max(0, $this->purchase_cost - $this->depreciated_amount);
    }

    /**
     * Vérifie si l'amortissement est terminé
     */
    public function getIsFullyDepreciatedAttribute(): bool
    {
        return $this->depreciated_amount >= $this->purchase_cost;
    }

    /**
     * Calcule le coût pour une période donnée
     */
    public function calculateForPeriod($startDate, $endDate): float
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        $days = $end->diffInDays($start);
        
        // Coût par jour = coût mensuel / 30
        return ($this->monthly_cost / 30) * $days;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFullyDepreciated($query)
    {
        return $query->whereRaw('TIMESTAMPDIFF(MONTH, purchase_date, NOW()) >= depreciation_years * 12');
    }

    public function scopeNotFullyDepreciated($query)
    {
        return $query->whereRaw('TIMESTAMPDIFF(MONTH, purchase_date, NOW()) < depreciation_years * 12');
    }
}
