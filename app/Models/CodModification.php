<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodModification extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id', 'old_amount', 'new_amount', 'modified_by_commercial_id',
        'reason', 'client_complaint_id', 'modification_notes', 'context_data',
        'ip_address', 'emergency_modification'
    ];

    protected $casts = [
        'old_amount' => 'decimal:3',
        'new_amount' => 'decimal:3',
        'context_data' => 'array',
        'emergency_modification' => 'boolean',
    ];

    // Relations
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function modifiedByCommercial()
    {
        return $this->belongsTo(User::class, 'modified_by_commercial_id');
    }

    public function clientComplaint()
    {
        return $this->belongsTo(Complaint::class, 'client_complaint_id');
    }

    // Scopes
    public function scopeByPackage($query, $packageId)
    {
        return $query->where('package_id', $packageId);
    }

    public function scopeByCommercial($query, $commercialId)
    {
        return $query->where('modified_by_commercial_id', $commercialId);
    }

    public function scopeEmergency($query)
    {
        return $query->where('emergency_modification', true);
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
    public function getDifferenceAttribute()
    {
        return $this->new_amount - $this->old_amount;
    }

    public function isIncrease()
    {
        return $this->new_amount > $this->old_amount;
    }

    public function isDecrease()
    {
        return $this->new_amount < $this->old_amount;
    }

    public function isEmergency()
    {
        return $this->emergency_modification;
    }

    public function getFormattedChangeAttribute()
    {
        $diff = $this->getDifferenceAttribute();
        $sign = $diff >= 0 ? '+' : '';
        return "{$sign}{$diff} DT";
    }

    public function getChangeColorAttribute()
    {
        if ($this->isIncrease()) {
            return 'text-green-600';
        } elseif ($this->isDecrease()) {
            return 'text-red-600';
        }
        return 'text-gray-600';
    }

    // Méthode statique pour créer une modification COD
    public static function createModification($packageId, $newAmount, $commercialId, $reason, $complaintId = null, $notes = null, $emergency = false)
    {
        $package = Package::findOrFail($packageId);
        
        $modification = self::create([
            'package_id' => $packageId,
            'old_amount' => $package->cod_amount,
            'new_amount' => $newAmount,
            'modified_by_commercial_id' => $commercialId,
            'reason' => $reason,
            'client_complaint_id' => $complaintId,
            'modification_notes' => $notes,
            'ip_address' => request()->ip(),
            'emergency_modification' => $emergency,
            'context_data' => [
                'package_status' => $package->status,
                'previous_modifications_count' => $package->codModifications()->count(),
                'modification_timestamp' => now()->toISOString()
            ]
        ]);

        // Mettre à jour le COD du colis
        $package->update(['cod_amount' => $newAmount]);

        // Log de l'action
        app(\App\Services\ActionLogService::class)->log(
            'COD_MODIFICATION',
            'Package',
            $packageId,
            $package->cod_amount,
            $newAmount,
            [
                'modification_id' => $modification->id,
                'reason' => $reason,
                'complaint_id' => $complaintId,
                'emergency' => $emergency
            ]
        );

        return $modification;
    }
}