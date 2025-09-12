<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'priority',
        'read', 'read_at', 'expires_at', 'action_url',
        'related_type', 'related_id'
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'URGENT');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isUnread()
    {
        return !$this->read;
    }

    public function isUrgent()
    {
        return $this->priority === 'URGENT';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasAction()
    {
        return !empty($this->action_url);
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'LOW' => 'text-gray-600 bg-gray-100',
            'NORMAL' => 'text-blue-600 bg-blue-100',
            'HIGH' => 'text-orange-600 bg-orange-100',
            'URGENT' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getPriorityDisplayAttribute()
    {
        return match($this->priority) {
            'LOW' => 'Basse',
            'NORMAL' => 'Normale',
            'HIGH' => 'Haute',
            'URGENT' => 'Urgente',
            default => $this->priority
        };
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'COMPLAINT_NEW' => 'Nouvelle réclamation',
            'COMPLAINT_URGENT' => 'Réclamation urgente',
            'WITHDRAWAL_REQUEST' => 'Demande de retrait',
            'WALLET_HIGH_BALANCE' => 'Wallet élevé',
            'COD_MODIFICATION' => 'Modification COD',
            'PACKAGE_BLOCKED' => 'Colis bloqué',
            'CLIENT_ACCOUNT_PENDING' => 'Compte client en attente',
            'SYSTEM_ALERT' => 'Alerte système',
            default => $this->type
        };
    }

    // Actions
    public function markAsRead()
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now()
            ]);
        }
        
        return $this;
    }

    public function markAsUnread()
    {
        $this->update([
            'read' => false,
            'read_at' => null
        ]);
        
        return $this;
    }

    // Static methods pour créer des notifications
    public static function createComplaintNotification(Complaint $complaint, array $commercialIds = [])
    {
        $priority = $complaint->priority === 'URGENT' ? 'URGENT' : 'HIGH';
        $type = $complaint->priority === 'URGENT' ? 'COMPLAINT_URGENT' : 'COMPLAINT_NEW';
        
        $title = $complaint->priority === 'URGENT' ? 'Réclamation URGENTE' : 'Nouvelle réclamation';
        $message = "Réclamation {$complaint->type_display} pour le colis {$complaint->package->package_code}";

        // Si pas de commerciaux spécifiés, notifier tous les commerciaux actifs
        if (empty($commercialIds)) {
            $commercialIds = User::where('role', 'COMMERCIAL')
                                ->where('account_status', 'ACTIVE')
                                ->pluck('id')
                                ->toArray();
        }

        $notifications = [];
        foreach ($commercialIds as $commercialId) {
            $notifications[] = self::create([
                'user_id' => $commercialId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'priority' => $priority,
                'action_url' => route('commercial.complaints.show', $complaint->id),
                'related_type' => 'App\Models\Complaint',
                'related_id' => $complaint->id,
                'data' => [
                    'complaint_id' => $complaint->id,
                    'package_code' => $complaint->package->package_code,
                    'client_name' => $complaint->client->name,
                    'complaint_type' => $complaint->type
                ]
            ]);
        }

        return $notifications;
    }

    public static function createWithdrawalNotification(WithdrawalRequest $withdrawal, array $commercialIds = [])
    {
        if (empty($commercialIds)) {
            $commercialIds = User::where('role', 'COMMERCIAL')
                                ->where('account_status', 'ACTIVE')
                                ->pluck('id')
                                ->toArray();
        }

        $notifications = [];
        foreach ($commercialIds as $commercialId) {
            $notifications[] = self::create([
                'user_id' => $commercialId,
                'type' => 'WITHDRAWAL_REQUEST',
                'title' => 'Nouvelle demande de retrait',
                'message' => "Demande de retrait de {$withdrawal->amount} DT par {$withdrawal->client->name}",
                'priority' => 'NORMAL',
                'action_url' => route('commercial.withdrawals.show', $withdrawal->id),
                'related_type' => 'App\Models\WithdrawalRequest',
                'related_id' => $withdrawal->id,
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'method' => $withdrawal->method,
                    'client_name' => $withdrawal->client->name
                ]
            ]);
        }

        return $notifications;
    }

    public static function createHighWalletNotification($deliverer, array $commercialIds = [])
    {
        if (empty($commercialIds)) {
            $commercialIds = User::where('role', 'COMMERCIAL')
                                ->where('account_status', 'ACTIVE')
                                ->pluck('id')
                                ->toArray();
        }

        $walletBalance = $deliverer->wallet->balance;
        
        $notifications = [];
        foreach ($commercialIds as $commercialId) {
            $notifications[] = self::create([
                'user_id' => $commercialId,
                'type' => 'WALLET_HIGH_BALANCE',
                'title' => 'Wallet livreur élevé',
                'message' => "Le livreur {$deliverer->name} a un solde de {$walletBalance} DT à vider",
                'priority' => $walletBalance > 200 ? 'HIGH' : 'NORMAL',
                'action_url' => route('commercial.deliverers.wallet', $deliverer->id),
                'related_type' => 'App\Models\User',
                'related_id' => $deliverer->id,
                'data' => [
                    'deliverer_id' => $deliverer->id,
                    'deliverer_name' => $deliverer->name,
                    'wallet_balance' => $walletBalance
                ]
            ]);
        }

        return $notifications;
    }

    // Cleanup des notifications expirées
    public static function cleanupExpired()
    {
        return self::expired()->delete();
    }
}