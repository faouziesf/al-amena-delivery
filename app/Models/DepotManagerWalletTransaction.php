<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotManagerWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'depot_manager_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'related_deliverer_id',
        'related_withdrawal_id',
        'processed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'metadata' => 'array',
    ];

    /**
     * Relation avec le chef dépôt
     */
    public function depotManager()
    {
        return $this->belongsTo(User::class, 'depot_manager_id');
    }

    /**
     * Relation avec le livreur concerné
     */
    public function relatedDeliverer()
    {
        return $this->belongsTo(User::class, 'related_deliverer_id');
    }

    /**
     * Relation avec la demande de retrait concernée
     */
    public function relatedWithdrawal()
    {
        return $this->belongsTo(WithdrawalRequest::class, 'related_withdrawal_id');
    }

    /**
     * Relation avec l'utilisateur qui a traité (superviseur)
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Obtenir le libellé du type de transaction
     */
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'DELIVERER_EMPTYING' => 'Vidage wallet livreur',
            'CASH_PAYMENT' => 'Paiement espèce',
            'SUPERVISOR_ADJUSTMENT' => 'Ajustement superviseur',
            'SUPERVISOR_EMPTYING' => 'Vidage par superviseur',
            default => $this->type
        };
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par chef dépôt
     */
    public function scopeByDepotManager($query, $depotManagerId)
    {
        return $query->where('depot_manager_id', $depotManagerId);
    }
}
