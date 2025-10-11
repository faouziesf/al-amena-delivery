<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'original_package_id',
        'return_package_code',
        'cod',
        'status',
        'sender_info',
        'recipient_info',
        'return_reason',
        'comment',
        'created_by',
        'printed_at',
        'delivered_at',
        'assigned_deliverer_id',
    ];

    protected $casts = [
        'sender_info' => 'array',
        'recipient_info' => 'array',
        'printed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return $this->belongsTo(User::class, 'created_by');
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
