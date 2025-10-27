<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'alert_type',
        'title',
        'message',
        'current_km',
        'threshold_km',
        'severity',
        'is_read',
        'read_at',
        'read_by',
    ];

    protected $casts = [
        'current_km' => 'integer',
        'threshold_km' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relations
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    /**
     * Crée une alerte de maintenance (évite les doublons)
     */
    public static function createAlert($vehicle, $type, $title, $message, $severity = 'INFO')
    {
        // Vérifier si une alerte similaire non lue existe déjà
        $existingAlert = self::where('vehicle_id', $vehicle->id)
            ->where('alert_type', $type)
            ->where('is_read', false)
            ->first();

        if ($existingAlert) {
            // Mettre à jour l'alerte existante
            $existingAlert->update([
                'message' => $message,
                'current_km' => $vehicle->current_km,
                'severity' => $severity,
            ]);
            
            return $existingAlert;
        }

        // Créer une nouvelle alerte
        return self::create([
            'vehicle_id' => $vehicle->id,
            'alert_type' => $type,
            'title' => $title,
            'message' => $message,
            'current_km' => $vehicle->current_km,
            'threshold_km' => match($type) {
                'OIL_CHANGE' => $vehicle->km_until_oil_change,
                'SPARK_PLUGS' => $vehicle->km_until_spark_plug_change,
                'TIRES' => $vehicle->km_until_tire_change,
                default => 0,
            },
            'severity' => $severity,
        ]);
    }

    /**
     * Marque l'alerte comme lue
     */
    public function markAsRead($userId = null)
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
            'read_by' => $userId ?? auth()->id(),
        ]);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'CRITICAL');
    }

    public function scopeWarning($query)
    {
        return $query->where('severity', 'WARNING');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
