<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id', 'previous_status', 'new_status', 'changed_by',
        'changed_by_role', 'notes', 'additional_data', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    // Relations
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes
    public function scopeByPackage($query, $packageId)
    {
        return $query->where('package_id', $packageId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('changed_by_role', $role);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function getStatusDisplayAttribute($status)
    {
        return match($status) {
            'CREATED' => 'Créé',
            'AVAILABLE' => 'Disponible',
            'OUT_FOR_DELIVERY' => 'En livraison',
            'PICKED_UP' => 'Collecté',
            'DELIVERED' => 'Livré',
            'PAID' => 'Payé',
            'REFUSED' => 'Refusé',
            'RETURNED' => 'Retourné',
            'UNAVAILABLE' => 'Non disponible',
            'VERIFIED' => 'Vérifié',
            'CANCELLED' => 'Annulé',
            default => $status
        };
    }

    public function getPreviousStatusDisplayAttribute()
    {
        return $this->getStatusDisplayAttribute($this->previous_status);
    }

    public function getNewStatusDisplayAttribute()
    {
        return $this->getStatusDisplayAttribute($this->new_status);
    }

    public function getFormattedChangeAttribute()
    {
        return $this->getPreviousStatusDisplayAttribute() . ' → ' . $this->getNewStatusDisplayAttribute();
    }

    public function getStatusColorAttribute()
    {
        return match($this->new_status) {
            'CREATED' => 'text-gray-600 bg-gray-100',
            'AVAILABLE' => 'text-blue-600 bg-blue-100',
            'OUT_FOR_DELIVERY' => 'text-purple-600 bg-purple-100',
            'PICKED_UP' => 'text-indigo-600 bg-indigo-100',
            'DELIVERED' => 'text-green-600 bg-green-100',
            'PAID' => 'text-green-800 bg-green-200',
            'REFUSED' => 'text-red-600 bg-red-100',
            'RETURNED' => 'text-orange-600 bg-orange-100',
            'UNAVAILABLE' => 'text-yellow-600 bg-yellow-100',
            'CANCELLED' => 'text-red-800 bg-red-200',
            default => 'text-gray-600 bg-gray-100'
        };
    }
}