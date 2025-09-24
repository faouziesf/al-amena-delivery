<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPickupAddress extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'address',
        'phone',
        'tel2',
        'contact_name',
        'delegation',
        'gouvernorat',
        'notes',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'pickup_address_id');
    }

    public function hasPackages()
    {
        return $this->packages()->where('status', 'CREATED')->exists();
    }

    public function getAvailablePackagesCount()
    {
        return $this->packages()->where('status', 'CREATED')->count();
    }
}