<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransitRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'origin_depot',
        'destination_depot',
        'date',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Le livreur assigné à cette tournée
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Les boîtes de transit de cette tournée
     */
    public function boxes(): HasMany
    {
        return $this->hasMany(TransitBox::class, 'route_id');
    }

    /**
     * L'utilisateur qui a créé cette tournée
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['ASSIGNED', 'IN_PROGRESS']);
    }

    /**
     * Vérifier si la tournée peut être démarrée
     */
    public function canBeStarted(): bool
    {
        return $this->status === 'ASSIGNED';
    }

    /**
     * Vérifier si la tournée peut être terminée
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'IN_PROGRESS' &&
               $this->boxes()->where('status', 'LOADED')->count() === 0;
    }

    /**
     * Démarrer la tournée
     */
    public function start(): bool
    {
        if (!$this->canBeStarted()) {
            return false;
        }

        $this->update([
            'status' => 'IN_PROGRESS',
            'started_at' => now()
        ]);

        return true;
    }

    /**
     * Terminer la tournée
     */
    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now()
        ]);

        return true;
    }
}