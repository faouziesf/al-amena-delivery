<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_code', 'sender_id', 'sender_data', 'delegation_from',
        'recipient_data', 'delegation_to', 'content_description', 'notes',
        'cod_amount', 'delivery_fee', 'return_fee', 'status',
        'assigned_deliverer_id', 'assigned_at', 'delivery_attempts',
        'cod_modifiable_by_commercial', 'amount_in_escrow',
        'supplier_data',           // JSON des données fournisseur
        'pickup_delegation_id',    // Délégation de pickup
        'pickup_address',          // Adresse complète de pickup
        'pickup_phone',            // Téléphone de contact pickup
        'pickup_notes',            // Notes spéciales pour le pickup
        'package_weight',          // Poids du colis (optionnel)
        'package_dimensions',      // Dimensions JSON (optionnel)
        'package_value',           // Valeur déclarée (optionnel)
        'special_instructions',    // Instructions spéciales
        'is_fragile',             // Colis fragile
        'requires_signature',     // Signature requise
        'allow_opening',          // Autorisation d'ouvrir le colis
        'payment_method',         // Mode de paiement accepté
        'pickup_address_id',      // ID de l'adresse de pickup
        'import_batch_id',        // ID du lot d'import (pour CSV)
        'reassigned_at',         // Date de réassignation
        'reassigned_by',         // ID du livreur TRANSIT qui a fait la réassignation
        'reassignment_reason',   // Raison de la réassignation
        'cancelled_by_client',   // Indicateur d'annulation par le client
        'cancellation_reason',   // Raison de l'annulation
        'cancelled_at',          // Date d'annulation
        'cancelled_by',          // ID de l'utilisateur qui a annulé
        'auto_return_reason',    // Raison automatique du retour
        'delivered_at',         // Date et heure de livraison
        'est_echange',          // Indique si ce colis nécessite un échange
        'payment_withdrawal_id', // ID de la demande de paiement liée
    ];

    protected $casts = [
        'sender_data' => 'array',
        'recipient_data' => 'array',
        'supplier_data' => 'array',
        'package_dimensions' => 'array',
        'cod_amount' => 'decimal:3',
        'delivery_fee' => 'decimal:3',
        'return_fee' => 'decimal:3',
        'amount_in_escrow' => 'decimal:3',
        'package_weight' => 'decimal:3',
        'package_value' => 'decimal:3',
        'assigned_at' => 'datetime',
        'reassigned_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cod_modifiable_by_commercial' => 'boolean',
        'is_fragile' => 'boolean',
        'requires_signature' => 'boolean',
        'allow_opening' => 'boolean',
        'cancelled_by_client' => 'boolean',
        'est_echange' => 'boolean',
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

    // Alias relationships for compatibility
    public function client()
    {
        return $this->sender();
    }

    public function deliverer()
    {
        return $this->assignedDeliverer();
    }

    // Legacy delegation relationship - using delegationTo as default
    public function delegation()
    {
        return $this->delegationTo();
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(PackageStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function withdrawalRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class, 'payment_withdrawal_id');
    }

    public function codModifications()
    {
        return $this->hasMany(CodModification::class)->orderBy('created_at', 'desc');
    }

    public function pickupDelegation()
    {
        return $this->belongsTo(Delegation::class, 'pickup_delegation_id');
    }

    public function importBatch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function pickupAddress()
    {
        return $this->belongsTo(ClientPickupAddress::class, 'pickup_address_id');
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
        if (!$data || !is_array($data)) {
            return 'N/A';
        }
        return ($data['name'] ?? 'N/A') . ' - ' . ($data['phone'] ?? 'N/A') . ' - ' . ($data['address'] ?? 'N/A');
    }

    public function getFormattedSenderAttribute()
    {
        $data = $this->sender_data;
        if (!$data || !is_array($data)) {
            return 'N/A';
        }
        return ($data['name'] ?? 'N/A') . ' - ' . ($data['phone'] ?? 'N/A') . ' - ' . ($data['address'] ?? 'N/A');
    }

    public function getFormattedSupplierAttribute()
    {
        $data = $this->supplier_data;
        if (!$data || !is_array($data)) {
            return 'N/A';
        }
        return ($data['name'] ?? 'N/A') . ' - ' . ($data['phone'] ?? 'N/A');
    }

    public function getPickupLocationAttribute()
    {
        // Priorité aux nouvelles données de pickup depuis ClientPickupAddress
        if ($this->pickupAddress) {
            return $this->pickupAddress->name . ' - ' . $this->pickupAddress->address;
        }

        // Fallback vers les anciennes données
        if ($this->pickupDelegation) {
            return $this->pickupDelegation->name . ' - ' . ($this->pickup_address ?? 'Adresse non spécifiée');
        }
        return $this->pickup_address ?? 'Adresse de pickup non spécifiée';
    }

    /**
     * Obtenir les données complètes de pickup depuis ClientPickupAddress
     */
    public function getPickupDataAttribute()
    {
        if ($this->pickupAddress) {
            return [
                'name' => $this->pickupAddress->name,
                'address' => $this->pickupAddress->address,
                'phone' => $this->pickupAddress->phone,
                'tel2' => $this->pickupAddress->tel2,
                'contact_name' => $this->pickupAddress->contact_name,
                'gouvernorat' => $this->pickupAddress->gouvernorat,
                'delegation' => $this->pickupAddress->delegation,
                'notes' => $this->pickupAddress->notes,
                'is_default' => $this->pickupAddress->is_default,
            ];
        }

        // Fallback vers les anciennes données dans sender_data
        return $this->sender_data ?? [];
    }

    /**
     * Obtenir le nom formaté de l'adresse de pickup
     */
    public function getFormattedPickupAttribute()
    {
        if ($this->pickupAddress) {
            $pickup = $this->pickupAddress;
            return $pickup->name . ' - ' . $pickup->contact_name . ' - ' . $pickup->phone . ' - ' . $pickup->address;
        }

        // Fallback vers les anciennes données
        $data = $this->sender_data;
        if (!$data || !is_array($data)) {
            return 'N/A';
        }
        return ($data['name'] ?? 'N/A') . ' - ' . ($data['phone'] ?? 'N/A') . ' - ' . ($data['address'] ?? 'N/A');
    }

    public function getFormattedWeightAttribute()
    {
        if (!$this->package_weight) return null;
        return number_format($this->package_weight, 3) . ' kg';
    }

    public function getFormattedValueAttribute()
    {
        if (!$this->package_value) return null;
        return number_format($this->package_value, 3) . ' DT';
    }

    public function getFormattedDimensionsAttribute()
    {
        if (!$this->package_dimensions || !is_array($this->package_dimensions)) {
            return null;
        }
        
        $dims = $this->package_dimensions;
        if (isset($dims['length'], $dims['width'], $dims['height'])) {
            return $dims['length'] . ' x ' . $dims['width'] . ' x ' . $dims['height'] . ' cm';
        }
        
        return null;
    }

    public function hasSpecialRequirements()
    {
        return $this->is_fragile || 
               $this->requires_signature || 
               !empty($this->special_instructions) ||
               !empty($this->pickup_notes);
    }

    public function getSpecialRequirementsListAttribute()
    {
        $requirements = [];
        
        if ($this->is_fragile) {
            $requirements[] = 'Fragile';
        }
        
        if ($this->requires_signature) {
            $requirements[] = 'Signature requise';
        }
        
        if (!empty($this->special_instructions)) {
            $requirements[] = 'Instructions spéciales';
        }
        
        if (!empty($this->pickup_notes)) {
            $requirements[] = 'Notes pickup';
        }
        
        return $requirements;
    }

    public function isFromImport()
    {
        return !is_null($this->import_batch_id);
    }

    /**
     * Vérifier si le colis a été annulé par le client
     */
    public function isCancelledByClient()
    {
        return $this->status === 'CANCELLED' ||
               (!empty($this->cancellation_reason) && $this->cancelled_by_client);
    }

    /**
     * Vérifier si le colis peut être retourné au fournisseur
     */
    public function canBeReturnedToSender()
    {
        return $this->delivery_attempts >= 3 ||
               $this->isCancelledByClient() ||
               $this->status === 'VERIFIED';
    }

    /**
     * Obtenir la raison pour laquelle le colis peut être retourné
     */
    public function getReturnReason()
    {
        if ($this->isCancelledByClient()) {
            return 'CANCELLED_BY_CLIENT';
        }

        if ($this->delivery_attempts >= 3) {
            return 'MAX_ATTEMPTS_REACHED';
        }

        if ($this->status === 'VERIFIED') {
            return 'VERIFIED_FOR_RETURN';
        }

        return null;
    }

    // Méthodes de gestion des statuts
    public function updateStatus($newStatus, $user, $notes = null, $additionalData = [])
    {
        $oldStatus = $this->status;
        
        // Enregistrer dans l'historique si le modèle existe
        if (class_exists(PackageStatusHistory::class)) {
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
        }

        // Mettre à jour le statut
        $this->update(['status' => $newStatus]);

        // Log de l'action si le service existe
        if (app()->bound(\App\Services\ActionLogService::class)) {
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
        }

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

        // Auto-assignation quand un colis passe au statut PICKED_UP
        static::updated(function ($package) {
            if ($package->isDirty('status') && $package->status === 'PICKED_UP') {
                // Lancer l'auto-assignation en arrière-plan
                \App\Jobs\AutoAssignPackageJob::dispatch($package->id)->delay(now()->addSeconds(5));
            }
        });
    }

    /**
     * Vérifier si peut être scanné par ce livreur
     */
    public function canBeScanBy(User $deliverer): bool
    {
        // Disponible pour tous
        if ($this->status === 'AVAILABLE') {
            return true;
        }
        
        // Assigné à ce livreur
        return $this->assigned_deliverer_id === $deliverer->id;
    }

    /**
     * Obtenir l'action possible pour ce livreur
     */
    public function getActionFor(User $deliverer): ?string
    {
        if ($this->status === 'AVAILABLE') {
            return 'accept';
        }
        
        if ($this->assigned_deliverer_id === $deliverer->id) {
            return match($this->status) {
                'ACCEPTED' => 'pickup',
                'PICKED_UP', 'UNAVAILABLE' => 'deliver', 
                'VERIFIED' => 'return',
                default => 'view'
            };
        }
        
        return null;
    }

    /**
     * Messages de statut
     */
    public function getStatusMessageAttribute(): string
    {
        return match($this->status) {
            'AVAILABLE' => '📦 Disponible',
            'ACCEPTED' => '✅ Accepté',
            'PICKED_UP' => '🚚 Collecté',
            'DELIVERED' => '✅ Livré',
            'VERIFIED' => '↩️ À retourner',
            'RETURNED' => '↩️ Retourné',
            default => $this->status
        };
    }

    /**
     * Obtenir les statistiques par délégation
     */
    public static function getStatsByDelegation()
    {
        return self::select('delegation_to', DB::raw('count(*) as total'))
                  ->groupBy('delegation_to')
                  ->orderBy('total', 'desc')
                  ->get();
    }
}