<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'zone', 'active', 'created_by'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByZone($query, $zone)
    {
        return $query->where('zone', $zone);
    }

    // Accessors
    public function getGovernorateAttribute()
    {
        return $this->zone;
    }

    // Helper methods
    public function isActive()
    {
        return $this->active;
    }

    public function activate()
    {
        $this->update(['active' => true]);
        return $this;
    }

    public function deactivate()
    {
        $this->update(['active' => false]);
        return $this;
    }
}