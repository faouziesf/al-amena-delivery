<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'type',
        'subject',
        'description',
        'status',
        'priority',
        'client_id',
        'assigned_to_id',
        'complaint_id',
        'package_id',
        'metadata',
        'first_response_at',
        'last_activity_at',
        'resolved_at',
        'closed_at'
    ];

    protected $casts = [
        'metadata' => 'json',
        'first_response_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    /**
     * Client qui a créé le ticket
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Commercial assigné au ticket
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Réclamation liée (si applicable)
     */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Package lié (si applicable)
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Messages du ticket
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Messages récents
     */
    public function recentMessages($limit = 5)
    {
        return $this->hasMany(TicketMessage::class)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit);
    }

    /**
     * Messages non lus par le client
     */
    public function unreadMessagesForClient()
    {
        return $this->hasMany(TicketMessage::class)
                   ->where('sender_type', '!=', 'CLIENT')
                   ->whereNull('read_at');
    }

    /**
     * Messages non lus par le commercial
     */
    public function unreadMessagesForCommercial()
    {
        return $this->hasMany(TicketMessage::class)
                   ->where('sender_type', 'CLIENT')
                   ->whereNull('read_at');
    }

    // ==================== SCOPES ====================

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'IN_PROGRESS');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'RESOLVED');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'CLOSED');
    }

    public function scopeUrgent($query)
    {
        return $query->where('status', 'URGENT');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('status', ['OPEN', 'URGENT'])
                    ->orWhere(function($q) {
                        $q->where('status', 'IN_PROGRESS')
                          ->where('last_activity_at', '<', now()->subDays(2));
                    });
    }

    // ==================== ACCESSORS ====================

    /**
     * Affichage du type en français
     */
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'COMPLAINT' => 'Réclamation',
            'QUESTION' => 'Question',
            'SUPPORT' => 'Support',
            'OTHER' => 'Autre',
            default => $this->type
        };
    }

    /**
     * Affichage du statut en français
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'OPEN' => 'Ouvert',
            'IN_PROGRESS' => 'En cours',
            'RESOLVED' => 'Résolu',
            'CLOSED' => 'Fermé',
            'URGENT' => 'Urgent',
            default => $this->status
        };
    }

    /**
     * Couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'OPEN' => 'text-blue-600 bg-blue-100',
            'IN_PROGRESS' => 'text-orange-600 bg-orange-100',
            'RESOLVED' => 'text-green-600 bg-green-100',
            'CLOSED' => 'text-gray-600 bg-gray-100',
            'URGENT' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Affichage de la priorité en français
     */
    public function getPriorityDisplayAttribute()
    {
        return match($this->priority) {
            'LOW' => 'Faible',
            'NORMAL' => 'Normale',
            'HIGH' => 'Élevée',
            'URGENT' => 'Urgente',
            default => $this->priority
        };
    }

    /**
     * Couleur de la priorité
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'LOW' => 'text-gray-600',
            'NORMAL' => 'text-blue-600',
            'HIGH' => 'text-orange-600',
            'URGENT' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    // ==================== HELPER METHODS ====================

    /**
     * Vérifier si le ticket est ouvert
     */
    public function isOpen()
    {
        return $this->status === 'OPEN';
    }

    /**
     * Vérifier si le ticket est en cours
     */
    public function isInProgress()
    {
        return $this->status === 'IN_PROGRESS';
    }

    /**
     * Vérifier si le ticket est résolu
     */
    public function isResolved()
    {
        return $this->status === 'RESOLVED';
    }

    /**
     * Vérifier si le ticket est fermé
     */
    public function isClosed()
    {
        return $this->status === 'CLOSED';
    }

    /**
     * Vérifier si le ticket est urgent
     */
    public function isUrgent()
    {
        return $this->status === 'URGENT';
    }

    /**
     * Vérifier si des messages peuvent être ajoutés
     */
    public function canAddMessages()
    {
        return !$this->isClosed();
    }

    /**
     * Marquer le ticket comme urgent
     */
    public function markAsUrgent($reason = null)
    {
        $this->update([
            'status' => 'URGENT',
            'priority' => 'URGENT',
            'last_activity_at' => now()
        ]);

        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['urgent_reason'] = $reason;
            $metadata['marked_urgent_at'] = now()->toISOString();
            $this->update(['metadata' => $metadata]);
        }

        return $this;
    }

    /**
     * Marquer le ticket comme résolu
     */
    public function markAsResolved($resolvedBy = null)
    {
        $this->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
            'last_activity_at' => now()
        ]);

        if ($resolvedBy) {
            $metadata = $this->metadata ?? [];
            $metadata['resolved_by'] = $resolvedBy;
            $this->update(['metadata' => $metadata]);
        }

        return $this;
    }

    /**
     * Fermer le ticket
     */
    public function close($closedBy = null)
    {
        $this->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
            'last_activity_at' => now()
        ]);

        if ($closedBy) {
            $metadata = $this->metadata ?? [];
            $metadata['closed_by'] = $closedBy;
            $this->update(['metadata' => $metadata]);
        }

        return $this;
    }

    /**
     * Assigner le ticket à un commercial
     */
    public function assignTo($commercialId)
    {
        $this->update([
            'assigned_to_id' => $commercialId,
            'status' => $this->isOpen() ? 'IN_PROGRESS' : $this->status,
            'last_activity_at' => now()
        ]);

        return $this;
    }

    /**
     * Obtenir le nombre de messages non lus pour le client
     */
    public function getUnreadCountForClient()
    {
        return $this->unreadMessagesForClient()->count();
    }

    /**
     * Obtenir le nombre de messages non lus pour le commercial
     */
    public function getUnreadCountForCommercial()
    {
        return $this->unreadMessagesForCommercial()->count();
    }

    /**
     * Mettre à jour la dernière activité
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
        return $this;
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Auto-générer le numéro de ticket
            if (empty($ticket->ticket_number)) {
                $date = now()->format('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $ticket->ticket_number = 'TKT_' . $date . '_' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            // Définir la dernière activité
            $ticket->last_activity_at = now();
        });

        static::created(function ($ticket) {
            // Log de création
            if (class_exists(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'TICKET_CREATED',
                    'Ticket',
                    $ticket->id,
                    null,
                    'OPEN',
                    [
                        'ticket_number' => $ticket->ticket_number,
                        'type' => $ticket->type,
                        'subject' => $ticket->subject
                    ]
                );
            }
        });

        static::updated(function ($ticket) {
            // Log des changements de statut
            if ($ticket->isDirty('status') && class_exists(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'TICKET_STATUS_CHANGED',
                    'Ticket',
                    $ticket->id,
                    $ticket->getOriginal('status'),
                    $ticket->status,
                    [
                        'ticket_number' => $ticket->ticket_number,
                        'assigned_to' => $ticket->assigned_to_id
                    ]
                );
            }
        });
    }
}