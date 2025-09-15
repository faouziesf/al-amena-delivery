<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'SUPPLIER' ou 'CLIENT'
        'name',
        'label', // Ex: "Fournisseur principal", "Entrepôt Tunis"
        'phone',
        'address',
        'delegation_id',
        'is_default',
        'usage_count',
        'last_used_at'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('type', 'SUPPLIER');
    }

    public function scopeClients($query)
    {
        return $query->where('type', 'CLIENT');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeRecentlyUsed($query)
    {
        return $query->orderBy('last_used_at', 'desc');
    }

    public function scopeMostUsed($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    // Helper methods
    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->delegation) $parts[] = $this->delegation->name;
        
        return implode(', ', $parts);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->label) {
            return $this->label . ' (' . $this->name . ')';
        }
        return $this->name;
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'SUPPLIER' => 'Fournisseur',
            'CLIENT' => 'Client',
            default => $this->type
        };
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
        return $this;
    }

    public function markAsDefault()
    {
        // Retirer le statut par défaut des autres adresses du même type
        static::where('user_id', $this->user_id)
              ->where('type', $this->type)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);
              
        $this->update(['is_default' => true]);
        return $this;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['delegation_name'] = $this->delegation?->name;
        $array['full_address'] = $this->getFullAddressAttribute();
        $array['display_name'] = $this->getDisplayNameAttribute();
        
        return $array;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::created(function ($address) {
            // Si c'est la première adresse de ce type, la marquer comme par défaut
            $count = static::where('user_id', $address->user_id)
                          ->where('type', $address->type)
                          ->count();
                          
            if ($count === 1) {
                $address->update(['is_default' => true]);
            }
        });

        static::deleting(function ($address) {
            // Si on supprime l'adresse par défaut et qu'il y en a d'autres, 
            // marquer la plus récente comme par défaut
            if ($address->is_default) {
                $newDefault = static::where('user_id', $address->user_id)
                                   ->where('type', $address->type)
                                   ->where('id', '!=', $address->id)
                                   ->orderBy('last_used_at', 'desc')
                                   ->first();
                                   
                if ($newDefault) {
                    $newDefault->markAsDefault();
                }
            }
        });
    }
}