<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'sender_type',
        'message',
        'attachments',
        'is_internal',
        'read_at',
        'metadata'
    ];

    protected $casts = [
        'attachments' => 'json',
        'metadata' => 'json',
        'read_at' => 'datetime',
        'is_internal' => 'boolean'
    ];

    // ==================== RELATIONS ====================

    /**
     * Ticket auquel appartient le message
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Utilisateur qui a envoyé le message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ==================== SCOPES ====================

    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    public function scopeFromClient($query)
    {
        return $query->where('sender_type', 'CLIENT');
    }

    public function scopeFromCommercial($query)
    {
        return $query->where('sender_type', 'COMMERCIAL');
    }

    public function scopeFromSupervisor($query)
    {
        return $query->where('sender_type', 'SUPERVISOR');
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // ==================== ACCESSORS ====================

    /**
     * Affichage du type d'expéditeur en français
     */
    public function getSenderTypeDisplayAttribute()
    {
        return match($this->sender_type) {
            'CLIENT' => 'Client',
            'COMMERCIAL' => 'Commercial',
            'SUPERVISOR' => 'Superviseur',
            default => $this->sender_type
        };
    }

    /**
     * Vérifier si le message a été lu
     */
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    /**
     * Obtenir le nom de l'expéditeur
     */
    public function getSenderNameAttribute()
    {
        return $this->sender ? $this->sender->name : 'Utilisateur supprimé';
    }


    // ==================== HELPER METHODS ====================

    /**
     * Vérifier si le message est du client
     */
    public function isFromClient()
    {
        return $this->sender_type === 'CLIENT';
    }

    /**
     * Vérifier si le message est du commercial
     */
    public function isFromCommercial()
    {
        return $this->sender_type === 'COMMERCIAL';
    }

    /**
     * Vérifier si le message est du superviseur
     */
    public function isFromSupervisor()
    {
        return $this->sender_type === 'SUPERVISOR';
    }

    /**
     * Vérifier si le message est interne
     */
    public function isInternal()
    {
        return $this->is_internal;
    }

    /**
     * Vérifier si le message est public (visible par le client)
     */
    public function isPublic()
    {
        return !$this->is_internal;
    }

    /**
     * Marquer le message comme lu
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['read_at' => now()]);
        }

        return $this;
    }

    /**
     * Obtenir le nombre de pièces jointes
     */
    public function getAttachmentsCount()
    {
        return $this->hasAttachments() ? count($this->attachments) : 0;
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            // Mettre à jour la dernière activité du ticket
            $message->ticket->updateLastActivity();

            // Marquer la première réponse si c'est la première réponse du commercial
            if ($message->isFromCommercial() && !$message->ticket->first_response_at) {
                $message->ticket->update(['first_response_at' => now()]);
            }

            // Log de création de message
            if (class_exists(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'TICKET_MESSAGE_CREATED',
                    'TicketMessage',
                    $message->id,
                    null,
                    'CREATED',
                    [
                        'ticket_id' => $message->ticket_id,
                        'ticket_number' => $message->ticket->ticket_number,
                        'sender_type' => $message->sender_type,
                        'is_internal' => $message->is_internal
                    ]
                );
            }
        });
    }

    // ==================== ATTACHMENT METHODS ====================

    /**
     * Vérifier si le message a des pièces jointes
     */
    public function hasAttachments()
    {
        return !empty($this->attachments) && is_array($this->attachments) && count($this->attachments) > 0;
    }

    /**
     * Obtenir les attachments formatés avec informations d'affichage
     */
    public function getFormattedAttachmentsAttribute()
    {
        if (!$this->hasAttachments()) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            $fileType = $this->getFileType($attachment);

            return [
                'name' => $attachment['name'] ?? 'Fichier',
                'url' => $attachment['url'] ?? '#',
                'type' => $attachment['type'] ?? 'unknown',
                'size' => $attachment['size'] ?? 0,
                'file_type' => $fileType,
                'is_image' => in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp']),
                'is_pdf' => $fileType === 'pdf',
                'is_document' => in_array($fileType, ['doc', 'docx', 'txt']),
                'icon' => $this->getFileIcon($fileType)
            ];
        });
    }

    /**
     * Obtenir le type de fichier à partir du nom ou du type MIME
     */
    private function getFileType($attachment)
    {
        if (isset($attachment['name'])) {
            return strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
        }

        if (isset($attachment['type'])) {
            $mimeType = $attachment['type'];
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'text/plain' => 'txt'
            ];

            return $extensions[$mimeType] ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * Obtenir l'icône pour le type de fichier
     */
    private function getFileIcon($fileType)
    {
        return match($fileType) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'txt' => '📄',
            'zip', 'rar' => '📦',
            default => '📎'
        };
    }
}