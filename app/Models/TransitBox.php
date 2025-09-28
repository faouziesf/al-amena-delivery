<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransitBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'code',
        'destination_governorate',
        'packages_count',
        'status',
        'loaded_at',
        'delivered_at',
        'package_ids',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'loaded_at' => 'datetime',
        'delivered_at' => 'datetime',
        'package_ids' => 'array'
    ];

    /**
     * La tournée de cette boîte
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(TransitRoute::class, 'route_id');
    }

    /**
     * L'utilisateur qui a créé cette boîte
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeLoaded($query)
    {
        return $query->where('status', 'LOADED');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'DELIVERED');
    }

    /**
     * Générer un code unique pour la boîte
     */
    public static function generateCode(string $destinationGov, string $originDepot = 'TUN'): string
    {
        $date = now()->format('dmY');
        $sequence = static::where('destination_governorate', $destinationGov)
                         ->whereDate('created_at', now()->toDateString())
                         ->count() + 1;

        return sprintf('%s-%s-%s-%02d', $destinationGov, $originDepot, $date, $sequence);
    }

    /**
     * Vérifier si la boîte peut être chargée
     */
    public function canBeLoaded(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Vérifier si la boîte peut être déchargée
     */
    public function canBeUnloaded(): bool
    {
        return $this->status === 'LOADED';
    }

    /**
     * Charger la boîte dans le camion
     */
    public function loadIntoTruck(): bool
    {
        if (!$this->canBeLoaded()) {
            return false;
        }

        $this->update([
            'status' => 'LOADED',
            'loaded_at' => now()
        ]);

        return true;
    }

    /**
     * Décharger la boîte du camion
     */
    public function unloadFromTruck(): bool
    {
        if (!$this->canBeUnloaded()) {
            return false;
        }

        $this->update([
            'status' => 'DELIVERED',
            'delivered_at' => now()
        ]);

        return true;
    }

    /**
     * Ajouter des colis à la boîte
     */
    public function addPackages(array $packageIds): void
    {
        $currentPackages = $this->package_ids ?? [];
        $newPackages = array_unique(array_merge($currentPackages, $packageIds));

        $this->update([
            'package_ids' => $newPackages,
            'packages_count' => count($newPackages)
        ]);
    }

    /**
     * Obtenir les informations formattées de la boîte
     */
    public function getFormattedInfo(): array
    {
        return [
            'code' => $this->code,
            'destination' => $this->destination_governorate,
            'packages_count' => $this->packages_count,
            'status' => $this->status,
            'loaded_at' => $this->loaded_at?->format('H:i'),
            'delivered_at' => $this->delivered_at?->format('H:i'),
        ];
    }
}