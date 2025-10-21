<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alias/Wrapper pour les colis de retour
 * Pointe vers la table packages avec package_type = 'RETURN'
 * Pour compatibilité avec l'ancien code
 */
class ReturnPackage extends Package
{
    use HasFactory;
    
    // Utiliser la même table que Package
    protected $table = 'packages';
    
    /**
     * Scope global pour filtrer uniquement les retours
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('return_only', function ($builder) {
            $builder->where('package_type', Package::TYPE_RETURN);
        });
        
        // Lors de la création, définir automatiquement le type
        static::creating(function ($model) {
            $model->package_type = Package::TYPE_RETURN;
        });
    }
    
    // Mapping des anciens champs vers les nouveaux
    protected $fillable = [
        'package_code',
        'return_package_code',
        'original_package_id',
        'sender_id',
        'sender_data', // Remplace sender_info
        'recipient_data', // Remplace recipient_info
        'delegation_from',
        'delegation_to',
        'return_reason',
        'notes', // Remplace comment
        'cod_amount', // Remplace cod
        'status',
        'assigned_deliverer_id',
        'delivered_at',
    ];

    /**
     * Relations
     */
    public function originalPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'original_package_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id'); // sender_id = created_by
    }

    public function assignedDeliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_deliverer_id');
    }

    /**
     * Scopes
     */

    public function scopeNotPrinted($query)
    {
        return $query->whereNull('printed_at');
    }

    public function scopePrinted($query)
    {
        return $query->whereNotNull('printed_at');
    }

    public function scopeAtDepot($query)
    {
        return $query->where('status', 'AT_DEPOT');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'DELIVERED');
    }

    /**
     * Helpers
     */

    public function isPrinted(): bool
    {
        return !is_null($this->printed_at);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }

    public function markAsPrinted(): void
    {
        $this->update(['printed_at' => now()]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'DELIVERED',
            'delivered_at' => now()
        ]);

        // Mettre à jour le colis original
        $this->originalPackage->update([
            'status' => 'RETURNED_TO_CLIENT',
            'returned_to_client_at' => now()
        ]);
    }

    /**
     * Générer le code du colis retour
     */
    public static function generateReturnCode(): string
    {
        do {
            $code = 'RET-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('return_package_code', $code)->exists());

        return $code;
    }

    /**
     * Obtenir les informations de la société (expéditeur)
     */
    public static function getCompanyInfo(): array
    {
        return [
            'name' => config('app.company_name', 'AL-AMENA Delivery'),
            'phone' => config('app.company_phone', '+216 XX XXX XXX'),
            'address' => config('app.company_address', 'Adresse Société'),
            'city' => config('app.company_city', 'Ville'),
            'postal_code' => config('app.company_postal', '0000'),
        ];
    }
}
