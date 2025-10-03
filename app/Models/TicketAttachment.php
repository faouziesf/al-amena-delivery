<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'filename',
        'original_filename',
        'path',
        'url',
        'size',
        'mime_type',
        'uploaded_by',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'size' => 'integer'
    ];

    /**
     * Relation vers le ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relation vers l'utilisateur qui a uploadé
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtenir la taille formatée
     */
    public function getFormattedSizeAttribute()
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        } elseif ($this->size < 1048576) {
            return round($this->size / 1024, 2) . ' KB';
        } else {
            return round($this->size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Vérifier si le fichier est une image
     */
    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Vérifier si le fichier est un PDF
     */
    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Obtenir l'icône selon le type de fichier
     */
    public function getFileIconAttribute()
    {
        if ($this->isImage()) {
            return '🖼️';
        } elseif ($this->isPdf()) {
            return '📄';
        } elseif (str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document')) {
            return '📝';
        } elseif (str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet')) {
            return '📊';
        } else {
            return '📎';
        }
    }
}