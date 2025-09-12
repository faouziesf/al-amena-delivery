<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransactionBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'snapshot_data',
        'backup_at'
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'backup_at' => 'datetime',
    ];

    // Relations
    public function transaction()
    {
        return $this->belongsTo(FinancialTransaction::class, 'transaction_id', 'transaction_id');
    }
}