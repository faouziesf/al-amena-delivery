<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelivererWalletEmptying extends Model
{
    use HasFactory;

    protected $fillable = [
        'deliverer_id', 'commercial_id', 'wallet_amount', 'physical_amount',
        'discrepancy_amount', 'emptying_date', 'notes', 'receipt_generated',
        'receipt_path', 'emptying_details', 'deliverer_acknowledged',
        'deliverer_acknowledged_at'
    ];

    protected $casts = [
        'wallet_amount' => 'decimal:3',
        'physical_amount' => 'decimal:3',
        'discrepancy_amount' => 'decimal:3',
        'emptying_date' => 'datetime',
        'emptying_details' => 'array',
        'receipt_generated' => 'boolean',
        'deliverer_acknowledged' => 'boolean',
        'deliverer_acknowledged_at' => 'datetime',
    ];

    // Relations
    public function deliverer()
    {
        return $this->belongsTo(User::class, 'deliverer_id');
    }

    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('emptying_date', today());
    }

    public function scopeByDeliverer($query, $delivererId)
    {
        return $query->where('deliverer_id', $delivererId);
    }

    public function scopeByCommercial($query, $commercialId)
    {
        return $query->where('commercial_id', $commercialId);
    }

    public function scopeWithDiscrepancy($query)
    {
        return $query->where('discrepancy_amount', '!=', 0);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('emptying_date', '>=', now()->subDays($days));
    }

    // Helper methods
    public function hasDiscrepancy()
    {
        return $this->discrepancy_amount != 0;
    }

    public function isPositiveDiscrepancy()
    {
        return $this->discrepancy_amount > 0;
    }

    public function isNegativeDiscrepancy()
    {
        return $this->discrepancy_amount < 0;
    }

    public function getDiscrepancyTypeAttribute()
    {
        if ($this->discrepancy_amount > 0) {
            return 'excess'; // Livreur a plus que prévu
        } elseif ($this->discrepancy_amount < 0) {
            return 'shortage'; // Livreur a moins que prévu
        }
        return 'exact';
    }

    public function getFormattedDiscrepancyAttribute()
    {
        if ($this->discrepancy_amount == 0) {
            return 'Exact';
        }
        
        $sign = $this->discrepancy_amount > 0 ? '+' : '';
        return $sign . number_format($this->discrepancy_amount, 3) . ' DT';
    }

    public function getDiscrepancyColorAttribute()
    {
        if ($this->discrepancy_amount > 0) {
            return 'text-orange-600 bg-orange-100'; // Surplus
        } elseif ($this->discrepancy_amount < 0) {
            return 'text-red-600 bg-red-100'; // Manque
        }
        return 'text-green-600 bg-green-100'; // Exact
    }

    public function acknowledgeByDeliverer()
    {
        $this->update([
            'deliverer_acknowledged' => true,
            'deliverer_acknowledged_at' => now()
        ]);

        app(\App\Services\ActionLogService::class)->log(
            'WALLET_EMPTYING_ACKNOWLEDGED',
            'DelivererWalletEmptying',
            $this->id,
            false,
            true,
            [
                'deliverer_id' => $this->deliverer_id,
                'commercial_id' => $this->commercial_id,
                'discrepancy' => $this->discrepancy_amount
            ]
        );

        return $this;
    }

    public function generateReceipt()
    {
        // Logic pour générer le PDF du reçu
        $receiptPath = 'receipts/emptying_' . $this->id . '_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        
        // TODO: Implémenter la génération PDF avec DomPDF
        // $pdf = PDF::loadView('commercial.receipts.wallet_emptying', ['emptying' => $this]);
        // $pdf->save(storage_path('app/public/' . $receiptPath));

        $this->update([
            'receipt_generated' => true,
            'receipt_path' => $receiptPath
        ]);

        return $receiptPath;
    }

    public function getReceiptUrl()
    {
        if (!$this->receipt_path) {
            return null;
        }
        
        return asset('storage/' . $this->receipt_path);
    }
}