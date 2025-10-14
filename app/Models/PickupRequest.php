<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PickupRequest extends Model
{
    protected $fillable = [
        'client_id',
        'pickup_address',
        'pickup_phone',
        'pickup_contact_name',
        'pickup_notes',
        'delegation_from',
        'requested_pickup_date',
        'status',
        'assigned_deliverer_id',
        'assigned_at',
        'picked_up_at'
    ];

    protected $casts = [
        'requested_pickup_date' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function assignedDeliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_deliverer_id');
    }

    // Note: pickup_address est un champ texte libre, pas une relation
    // Si on veut une relation vers ClientPickupAddress, il faudra ajouter pickup_address_id à la table

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopePickedUp($query)
    {
        return $query->where('status', 'picked_up');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->where('created_at', '>=', now()->startOfWeek());
    }

    /**
     * Filtrer les pickups par gouvernorat/délégation du livreur
     * Si le livreur a un gouvernorat assigné, ne montrer que les pickups de ce gouvernorat
     */
    public function scopeForDelivererGovernorate($query, $deliverer)
    {
        // Si le livreur a un gouvernorat/délégation assigné, filtrer
        if (isset($deliverer->governorate) && !empty($deliverer->governorate)) {
            return $query->where('delegation_from', $deliverer->governorate);
        }
        
        // Sinon, retourner tous les pickups (pas de restriction)
        return $query;
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function isPickedUp()
    {
        return $this->status === 'picked_up';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'assigned']);
    }

    // Note: Les méthodes de packages ont été supprimées car les pickup requests
    // ne contiennent plus de packages directement

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'assigned' => 'Assignée',
            'picked_up' => 'Collectée',
            'cancelled' => 'Annulée',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'picked_up' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Marquer ce pickup comme collecté
     */
    public function markAsPickedUp($delivererId = null)
    {
        if ($this->status !== 'assigned') {
            throw new \Exception('Ce pickup ne peut pas être marqué comme collecté car il n\'est pas assigné');
        }

        $this->update([
            'status' => 'picked_up',
            'picked_up_at' => now()
        ]);

        return $this->fresh();
    }
}