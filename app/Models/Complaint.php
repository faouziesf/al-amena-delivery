<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_code', 'package_id', 'client_id', 'type', 'description',
        'additional_data', 'status', 'priority', 'assigned_commercial_id',
        'resolution_notes', 'resolution_data', 'resolved_at', 'ticket_id'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'resolution_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function assignedCommercial()
    {
        return $this->belongsTo(User::class, 'assigned_commercial_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'URGENT');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCommercial($query, $commercialId)
    {
        return $query->where('assigned_commercial_id', $commercialId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_commercial_id');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    public function isUrgent()
    {
        return $this->priority === 'URGENT';
    }

    public function requiresCodChange()
    {
        return $this->type === 'CHANGE_COD';
    }

    public function canBeResolved()
    {
        return in_array($this->status, ['PENDING', 'IN_PROGRESS']);
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'CHANGE_COD' => 'Changement COD',
            'DELIVERY_DELAY' => 'Retard de livraison',
            'REQUEST_RETURN' => 'Demande de retour',
            'RETURN_DELAY' => 'Retard de retour',
            'RESCHEDULE_TODAY' => 'Report aujourd\'hui',
            'FOURTH_ATTEMPT' => '4ème tentative',
            'CUSTOM' => 'Personnalisé',
            default => $this->type
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'IN_PROGRESS' => 'En cours',
            'RESOLVED' => 'Résolue',
            'REJECTED' => 'Rejetée',
            default => $this->status
        };
    }

    public function getPriorityDisplayAttribute()
    {
        return match($this->priority) {
            'LOW' => 'Basse',
            'NORMAL' => 'Normale',
            'HIGH' => 'Haute',
            'URGENT' => 'Urgente',
            default => $this->priority
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'LOW' => 'text-gray-600 bg-gray-100',
            'NORMAL' => 'text-blue-600 bg-blue-100',
            'HIGH' => 'text-orange-600 bg-orange-100',
            'URGENT' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    // Méthodes de gestion
    public function assignTo(User $commercial)
    {
        $this->update([
            'assigned_commercial_id' => $commercial->id,
            'status' => 'IN_PROGRESS'
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'COMPLAINT_ASSIGNED',
            'Complaint',
            $this->id,
            null,
            $commercial->id,
            ['complaint_code' => $this->complaint_code]
        );

        return $this;
    }

    public function resolve($notes, $resolutionData = [])
    {
        $this->update([
            'status' => 'RESOLVED',
            'resolution_notes' => $notes,
            'resolution_data' => $resolutionData,
            'resolved_at' => now()
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'COMPLAINT_RESOLVED',
            'Complaint',
            $this->id,
            'PENDING/IN_PROGRESS',
            'RESOLVED',
            ['complaint_code' => $this->complaint_code, 'resolution' => $notes]
        );

        return $this;
    }

    public function reject($reason)
    {
        $this->update([
            'status' => 'REJECTED',
            'resolution_notes' => $reason,
            'resolved_at' => now()
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'COMPLAINT_REJECTED',
            'Complaint',
            $this->id,
            'PENDING/IN_PROGRESS',
            'REJECTED',
            ['complaint_code' => $this->complaint_code, 'reason' => $reason]
        );

        return $this;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->complaint_code)) {
                $complaint->complaint_code = 'CPL_' . strtoupper(Str::random(8)) . '_' . date('Ymd');
            }
        });

        static::created(function ($complaint) {
            // Créer automatiquement un ticket depuis la réclamation
            if (class_exists(\App\Services\TicketIntegrationService::class)) {
                try {
                    $ticketService = app(\App\Services\TicketIntegrationService::class);
                    $ticketService->createTicketFromComplaint($complaint);
                } catch (\Exception $e) {
                    // Log l'erreur mais ne bloque pas la création de la réclamation
                    \Log::error('Erreur création ticket depuis réclamation', [
                        'complaint_id' => $complaint->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        static::updated(function ($complaint) {
            // Synchroniser le statut avec le ticket associé
            if ($complaint->isDirty('status') && class_exists(\App\Services\TicketIntegrationService::class)) {
                try {
                    $ticketService = app(\App\Services\TicketIntegrationService::class);
                    $ticketService->syncComplaintTicketStatus($complaint);
                } catch (\Exception $e) {
                    \Log::error('Erreur synchronisation statut réclamation-ticket', [
                        'complaint_id' => $complaint->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }
}