<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TopupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'client_id',
        'amount',
        'method',
        'bank_transfer_id',
        'proof_document',
        'notes',
        'status',
        'processed_by_id',
        'processed_at',
        'rejection_reason',
        'validation_notes',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'processed_at' => 'datetime',
        'metadata' => 'json',
    ];

    // ==================== RELATIONS ====================

    /**
     * Client qui a fait la demande
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Utilisateur qui a traité la demande (Commercial ou Livreur)
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'VALIDATED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'CANCELLED');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeBankTransfers($query)
    {
        return $query->whereIn('method', ['BANK_TRANSFER', 'BANK_DEPOSIT']);
    }

    public function scopeCashPayments($query)
    {
        return $query->where('method', 'CASH');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== ACCESSORS ====================

    /**
     * Affichage de la méthode en français
     */
    public function getMethodDisplayAttribute()
    {
        return match($this->method) {
            'BANK_TRANSFER' => 'Virement bancaire',
            'BANK_DEPOSIT' => 'Versement bancaire',
            'CASH' => 'Paiement espèces',
            default => $this->method
        };
    }

    /**
     * Affichage du statut en français
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'PENDING' => 'En attente',
            'VALIDATED' => 'Validée',
            'REJECTED' => 'Rejetée',
            'CANCELLED' => 'Annulée',
            default => $this->status
        };
    }

    /**
     * Couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PENDING' => 'text-orange-600 bg-orange-100',
            'VALIDATED' => 'text-green-600 bg-green-100',
            'REJECTED' => 'text-red-600 bg-red-100',
            'CANCELLED' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Icône du statut
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'PENDING' => 'clock',
            'VALIDATED' => 'check-circle',
            'REJECTED' => 'x-circle',
            'CANCELLED' => 'ban',
            default => 'help-circle'
        };
    }

    /**
     * Icône de la méthode
     */
    public function getMethodIconAttribute()
    {
        return match($this->method) {
            'BANK_TRANSFER' => 'credit-card',
            'BANK_DEPOSIT' => 'building',
            'CASH' => 'banknote',
            default => 'help-circle'
        };
    }

    /**
     * Montant formaté
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 3) . ' DT';
    }

    // ==================== HELPER METHODS ====================

    /**
     * Vérifier si la demande est en attente
     */
    public function isPending()
    {
        return $this->status === 'PENDING';
    }

    /**
     * Vérifier si la demande est validée
     */
    public function isValidated()
    {
        return $this->status === 'VALIDATED';
    }

    /**
     * Vérifier si la demande est rejetée
     */
    public function isRejected()
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Vérifier si la demande est annulée
     */
    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Vérifier si la demande peut être annulée par le client
     */
    public function canBeCancelled()
    {
        return $this->status === 'PENDING';
    }

    /**
     * Vérifier si la demande nécessite une validation bancaire
     */
    public function requiresBankValidation()
    {
        return in_array($this->method, ['BANK_TRANSFER', 'BANK_DEPOSIT']);
    }

    /**
     * Vérifier si la demande nécessite une validation cash
     */
    public function requiresCashValidation()
    {
        return $this->method === 'CASH';
    }

    /**
     * Obtenir le délai d'attente estimé
     */
    public function getEstimatedProcessingTimeAttribute()
    {
        return match($this->method) {
            'BANK_TRANSFER', 'BANK_DEPOSIT' => '24-48h ouvrables',
            'CASH' => 'Selon disponibilité du livreur',
            default => 'Non défini'
        };
    }

    /**
     * Vérifier si l'identifiant bancaire est unique
     */
    public static function isBankTransferIdUnique($bankTransferId, $excludeId = null)
    {
        $query = static::where('bank_transfer_id', $bankTransferId)
                      ->whereNotNull('bank_transfer_id');
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->doesntExist();
    }

    /**
     * Annuler la demande
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'CANCELLED',
            'processed_at' => now(),
            'rejection_reason' => $reason
        ]);

        return $this;
    }

    /**
     * Obtenir les demandes en attente pour validation bancaire
     */
    public static function getPendingBankValidations()
    {
        return static::pending()
                    ->bankTransfers()
                    ->with(['client'])
                    ->orderBy('created_at', 'asc')
                    ->get();
    }

    /**
     * Obtenir les demandes en attente pour validation cash
     */
    public static function getPendingCashValidations()
    {
        return static::pending()
                    ->cashPayments()
                    ->with(['client'])
                    ->orderBy('created_at', 'asc')
                    ->get();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            // Auto-générer le code de demande
            if (empty($request->request_code)) {
                $request->request_code = 'TOP_' . strtoupper(Str::random(8)) . '_' . date('Ymd');
            }
        });

        static::created(function ($request) {
            // Log de création
            if (class_exists(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'TOPUP_REQUEST_CREATED',
                    'TopupRequest',
                    $request->id,
                    null,
                    'PENDING',
                    [
                        'request_code' => $request->request_code,
                        'amount' => $request->amount,
                        'method' => $request->method
                    ]
                );
            }
        });

        static::updated(function ($request) {
            // Log des changements de statut
            if ($request->isDirty('status') && class_exists(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'TOPUP_REQUEST_STATUS_CHANGED',
                    'TopupRequest',
                    $request->id,
                    $request->getOriginal('status'),
                    $request->status,
                    [
                        'request_code' => $request->request_code,
                        'processed_by' => $request->processed_by_id
                    ]
                );
            }
        });
    }
}