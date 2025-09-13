<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address',
        'account_status', 'verified_at', 'verified_by', 'created_by', 'last_login'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];

    // ==================== RELATIONS ====================

    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Relations pour CLIENT
    public function packages()
    {
        return $this->hasMany(Package::class, 'sender_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'client_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'client_id');
    }

    // Relations pour DELIVERER
    public function assignedPackages()
    {
        return $this->hasMany(Package::class, 'assigned_deliverer_id');
    }

    public function delivererWalletEmptyings()
    {
        return $this->hasMany(DelivererWalletEmptying::class, 'deliverer_id');
    }

    public function assignedWithdrawals()
    {
        return $this->hasMany(WithdrawalRequest::class, 'assigned_deliverer_id');
    }

    // Relations pour COMMERCIAL
    public function createdClients()
    {
        return $this->hasMany(User::class, 'created_by')->where('role', 'CLIENT');
    }

    public function validatedClients()
    {
        return $this->hasMany(User::class, 'verified_by')->where('role', 'CLIENT');
    }

    public function assignedComplaints()
    {
        return $this->hasMany(Complaint::class, 'assigned_commercial_id');
    }

    public function processedWithdrawals()
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by_commercial_id');
    }

    public function walletEmptyings()
    {
        return $this->hasMany(DelivererWalletEmptying::class, 'commercial_id');
    }

    public function codModifications()
    {
        return $this->hasMany(CodModification::class, 'modified_by_commercial_id');
    }

    // Relations hiérarchiques
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->where('account_status', 'ACTIVE');
    }

    public function scopePending($query)
    {
        return $query->where('account_status', 'PENDING');
    }

    public function scopeSuspended($query)
    {
        return $query->where('account_status', 'SUSPENDED');
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'CLIENT');
    }

    public function scopeDeliverers($query)
    {
        return $query->where('role', 'DELIVERER');
    }

    public function scopeCommercials($query)
    {
        return $query->where('role', 'COMMERCIAL');
    }

    // ==================== HELPER METHODS ====================

    public function isClient()
    {
        return $this->role === 'CLIENT';
    }

    public function isDeliverer()
    {
        return $this->role === 'DELIVERER';
    }

    public function isCommercial()
    {
        return $this->role === 'COMMERCIAL';
    }

    public function isSupervisor()
    {
        return $this->role === 'SUPERVISOR';
    }

    public function canManageClients()
    {
        return in_array($this->role, ['COMMERCIAL', 'SUPERVISOR']);
    }

    public function isActive()
    {
        return $this->account_status === 'ACTIVE';
    }

    public function isPending()
    {
        return $this->account_status === 'PENDING';
    }

    public function isSuspended()
    {
        return $this->account_status === 'SUSPENDED';
    }

    public function hasValidatedAccount()
    {
        return $this->verified_at !== null;
    }

    // Wallet helpers for clients and deliverers
    public function getWalletBalance()
    {
        return $this->wallet ? $this->wallet->balance : 0;
    }

    public function getPendingAmount()
    {
        return $this->wallet ? $this->wallet->pending_amount : 0;
    }

    public function hasSufficientBalance($amount)
    {
        return $this->getWalletBalance() >= $amount;
    }

    // Package statistics for clients
    public function getTotalPackagesCount()
    {
        return $this->packages()->count();
    }

    public function getDeliveredPackagesCount()
    {
        return $this->packages()->whereIn('status', ['DELIVERED', 'PAID'])->count();
    }

    public function getInProgressPackagesCount()
    {
        return $this->packages()->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count();
    }

    public function getPendingComplaintsCount()
    {
        return $this->complaints()->where('status', 'PENDING')->count();
    }

    // Deliverer statistics
    public function getAssignedPackagesCount()
    {
        return $this->assignedPackages()->count();
    }

    public function getCompletedDeliveriesCount()
    {
        return $this->assignedPackages()->whereIn('status', ['DELIVERED', 'RETURNED'])->count();
    }

    // Commercial statistics
    public function getCreatedClientsCount()
    {
        return $this->createdClients()->count();
    }

    public function getActiveClientsCount()
    {
        return $this->createdClients()->active()->count();
    }

    public function getAssignedComplaintsCount()
    {
        return $this->assignedComplaints()->whereIn('status', ['PENDING', 'IN_PROGRESS'])->count();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        // Créer automatiquement un wallet lors de la création d'un utilisateur
        static::created(function ($user) {
            if (in_array($user->role, ['CLIENT', 'DELIVERER'])) {
                UserWallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'pending_amount' => 0,
                    'frozen_amount' => 0,
                ]);
            }
        });

        // Mettre à jour last_login automatiquement
        static::updating(function ($user) {
            if (auth()->check() && auth()->id() === $user->id && !$user->isDirty('last_login')) {
                $user->last_login = now();
            }
        });
    }
}