<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedCharge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'periodicity',
        'monthly_equivalent',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'monthly_equivalent' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Mutateurs
    protected static function boot()
    {
        parent::boot();

        // Calcul automatique de l'équivalent mensuel
        static::saving(function ($charge) {
            $charge->monthly_equivalent = $charge->calculateMonthlyEquivalent();
        });
    }

    /**
     * Calcule l'équivalent mensuel selon la périodicité
     */
    public function calculateMonthlyEquivalent(): float
    {
        return match($this->periodicity) {
            'DAILY' => $this->amount * 26, // 26 jours ouvrables par mois (6 jours/semaine)
            'WEEKLY' => $this->amount * 4.33, // 4.33 semaines par mois en moyenne
            'MONTHLY' => $this->amount,
            'YEARLY' => $this->amount / 12,
            default => 0,
        };
    }

    /**
     * Calcule le montant pour une période donnée
     * 
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function calculateForPeriod($startDate, $endDate): float
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        $days = $end->diffInDays($start);
        $workingDays = $this->calculateWorkingDays($start, $end);
        
        return match($this->periodicity) {
            'DAILY' => $this->amount * $workingDays,
            'WEEKLY' => $this->amount * ($workingDays / 6), // 6 jours ouvrables par semaine
            'MONTHLY' => $this->amount * ($days / 30),
            'YEARLY' => $this->amount * ($days / 365),
            default => 0,
        };
    }

    /**
     * Calcule le nombre de jours ouvrables (6 jours par semaine)
     */
    private function calculateWorkingDays($start, $end): int
    {
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPeriodicity($query, $periodicity)
    {
        return $query->where('periodicity', $periodicity);
    }
}
