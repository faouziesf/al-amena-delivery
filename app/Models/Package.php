<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_code', 'sender_id', 'sender_data', 'delegation_from',
        'recipient_data', 'delegation_to', 'content_description', 'notes',
        'cod_amount', 'delivery_fee', 'return_fee', 'status',
        'assigned_deliverer_id', 'assigned_at', 'delivery_attempts',
        'cod_modifiable_by_commercial', 'amount_in_escrow'
    ];

    protected $casts = [
        'sender_data' => 'array',
        'recipient_data' => 'array',
        'cod_amount' => 'decimal:3',
        'delivery_fee' => 'decimal:3',
        'return_fee' => 'decimal:3',
        'amount_in_escrow' => 'decimal:3',
        'assigned_at' => 'datetime',
        'cod_modifiable_by_commercial' => 'boolean',
    ];

    // Relations
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function delegationFrom()
    {
        return $this->belongsTo(Delegation::class, 'delegation_from');
    }

    public function delegationTo()
    {
        return $this->belongsTo(Delegation::class, 'delegation_to');
    }

    public function assignedDeliverer()
    {
        return $this->belongsTo(User::class, 'assigned_deliverer_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(PackageStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function codModifications()
    {
        return $this->hasMany(CodModification::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('sender_id', $clientId);
    }

    public function scopeByDeliverer($query, $delivererId)
    {
        return $query->where('assigned_deliverer_id', $delivererId);
    }

    public function scopeWithPendingComplaints($query)
    {
        return $query->whereHas('complaints', function ($q) {
            $q->where('status', 'PENDING');
        });
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP']);
    }

    public function scopeDelivered($query)
    {
        return $query->whereIn('status', ['DELIVERED', 'PAID']);
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'RETURNED');
    }

    // Helper methods
    public function isInProgress()
    {
        return in_array($this->status, ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP']);
    }

    public function isDelivered()
    {
        return in_array($this->status, ['DELIVERED', 'PAID']);
    }

    public function canBeModified()
    {
        return $this->cod_modifiable_by_commercial && $this->status !== 'PAID';
    }

    public function hasPendingComplaints()
    {
        return $this->complaints()->where('status', 'PENDING')->exists();
    }

    public function getFormattedRecipientAttribute()
    {
        $data = $this->recipient_data;
        return $data['name'] . ' - ' . $data['phone'] . ' - ' . $data['address'];
    }

    public function getFormattedSenderAttribute()
    {
        $data = $this->sender_data;
        return $data['name'] . ' - ' . $data['phone'] . ' - ' . $data['address'];
    }

    // Méthodes de gestion des statuts
    public function updateStatus($newStatus, $user, $notes = null, $additionalData = [])
    {
        $oldStatus = $this->status;
        
        // Enregistrer dans l'historique
        PackageStatusHistory::create([
            'package_id' => $this->id,
            'previous_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $user->id,
            'changed_by_role' => $user->role,
            'notes' => $notes,
            'additional_data' => $additionalData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Mettre à jour le statut
        $this->update(['status' => $newStatus]);

        // Log de l'action
        app(\App\Services\ActionLogService::class)->log(
            'PACKAGE_STATUS_CHANGE',
            'Package',
            $this->id,
            $oldStatus,
            $newStatus,
            array_merge([
                'package_code' => $this->package_code,
                'notes' => $notes
            ], $additionalData)
        );

        return $this;
    }

    // Boot method pour générer le code automatiquement
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            if (empty($package->package_code)) {
                $package->package_code = 'PKG_' . strtoupper(Str::random(8)) . '_' . date('Ymd');
            }
        });
    }
}