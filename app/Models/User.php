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
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'id')
                   ->orderBy('created_at', 'desc');
    }

    public function recentTransactions($limit = 20)
    {
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'id')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit);
    }

    public function completedTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'user_id', 'id')
                   ->where('status', 'COMPLETED')
                   ->orderBy('created_at', 'desc');
    }

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

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

    public function scopeRecentlyCreated($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeWithWallet($query)
    {
        return $query->whereHas('wallet');
    }

    public function scopeWithProfile($query)
    {
        return $query->whereHas('clientProfile');
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

    public function getWalletBalance()
    {
        if (!$this->wallet) {
            return 0;
        }
        return $this->wallet->balance;
    }

    public function getPendingAmount()
    {
        if (!$this->wallet) {
            return 0;
        }
        return $this->wallet->pending_amount;
    }

    public function hasSufficientBalance($amount)
    {
        return $this->getWalletBalance() >= $amount;
    }

    public function getFormattedWalletBalance()
    {
        return number_format($this->getWalletBalance(), 3) . ' DT';
    }

    public function getTotalPackagesCount()
    {
        if (!$this->isClient()) {
            return 0;
        }
        return $this->packages()->count();
    }

    public function getDeliveredPackagesCount()
    {
        if (!$this->isClient()) {
            return 0;
        }
        return $this->packages()->delivered()->count();
    }

    public function getInProgressPackagesCount()
    {
        if (!$this->isClient()) {
            return 0;
        }
        return $this->packages()->inProgress()->count();
    }

    public function getPendingComplaintsCount()
    {
        if (!$this->isClient()) {
            return 0;
        }
        return $this->complaints()->where('status', 'PENDING')->count();
    }

    public function getSuccessDeliveryRate()
    {
        $total = $this->getTotalPackagesCount();
        if ($total === 0) {
            return 0;
        }
        return round(($this->getDeliveredPackagesCount() / $total) * 100, 1);
    }

    public function getAssignedPackagesCount()
    {
        if (!$this->isDeliverer()) {
            return 0;
        }
        return $this->assignedPackages()->count();
    }

    public function getCompletedDeliveriesCount()
    {
        if (!$this->isDeliverer()) {
            return 0;
        }
        return $this->assignedPackages()->whereIn('status', ['DELIVERED', 'RETURNED'])->count();
    }

    public function getDeliverySuccessRate()
    {
        $total = $this->getAssignedPackagesCount();
        if ($total === 0) {
            return 0;
        }
        $delivered = $this->assignedPackages()->where('status', 'DELIVERED')->count();
        return round(($delivered / $total) * 100, 1);
    }

    public function getCreatedClientsCount()
    {
        if (!$this->canManageClients()) {
            return 0;
        }
        return $this->createdClients()->count();
    }

    public function getActiveClientsCount()
    {
        if (!$this->canManageClients()) {
            return 0;
        }
        return $this->createdClients()->active()->count();
    }

    public function getAssignedComplaintsCount()
    {
        if (!$this->canManageClients()) {
            return 0;
        }
        return $this->assignedComplaints()->whereIn('status', ['PENDING', 'IN_PROGRESS'])->count();
    }

    public function getValidatedClientsThisMonth()
    {
        if (!$this->canManageClients()) {
            return 0;
        }
        return $this->validatedClients()
                   ->whereMonth('verified_at', now()->month)
                   ->whereYear('verified_at', now()->year)
                   ->count();
    }

    public function getTotalTransactionsCount()
    {
        return $this->transactions()->count();
    }

    public function getCompletedTransactionsCount()
    {
        return $this->transactions()->where('status', 'COMPLETED')->count();
    }

    public function getCreditTransactionsSum($days = null)
    {
        $query = $this->transactions()->where('type', 'CREDIT')->where('status', 'COMPLETED');
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        return $query->sum('amount');
    }

    public function getDebitTransactionsSum($days = null)
    {
        $query = $this->transactions()->where('type', 'DEBIT')->where('status', 'COMPLETED');
        
        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }
        
        return abs($query->sum('amount'));
    }

    public function getLastTransaction()
    {
        return $this->transactions()->first();
    }

    public function hasRecentTransactions($days = 30)
    {
        return $this->transactions()
                   ->where('created_at', '>=', now()->subDays($days))
                   ->exists();
    }

    // ==================== BUSINESS LOGIC METHODS ====================

    public function canValidateClients()
    {
        return $this->canManageClients() && $this->isActive();
    }

    public function canManageWallets()
    {
        return $this->canManageClients() && $this->isActive();
    }

    public function canCreatePackages()
    {
        return $this->isClient() && $this->isActive();
    }

    public function canReceivePackages()
    {
        return $this->isDeliverer() && $this->isActive();
    }

    public function hasCompletedProfile()
    {
        if (!$this->isClient()) {
            return true;
        }
        
        if (!$this->clientProfile) {
            return false;
        }
        
        return $this->clientProfile->hasCompletedProfile();
    }

    public function needsWalletSetup()
    {
        return in_array($this->role, ['CLIENT', 'DELIVERER']) && !$this->wallet;
    }

    public function createWalletIfNotExists()
    {
        if (!$this->wallet && in_array($this->role, ['CLIENT', 'DELIVERER'])) {
            UserWallet::create([
                'user_id' => $this->id,
                'balance' => 0,
                'pending_amount' => 0,
                'frozen_amount' => 0,
            ]);
            
            // Recharger la relation
            $this->load('wallet');
        }
        
        return $this->wallet;
    }

    // ==================== FORMATTING METHODS ====================

    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->email . ')';
    }

    public function getShortNameAttribute()
    {
        $parts = explode(' ', $this->name);
        if (count($parts) >= 2) {
            return $parts[0] . ' ' . substr($parts[1], 0, 1) . '.';
        }
        return $this->name;
    }

    public function getInitialsAttribute()
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach ($parts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    public function getAccountStatusDisplayAttribute()
    {
        return match($this->account_status) {
            'ACTIVE' => 'Actif',
            'PENDING' => 'En attente',
            'SUSPENDED' => 'Suspendu',
            default => $this->account_status
        };
    }

    public function getAccountStatusColorAttribute()
    {
        return match($this->account_status) {
            'ACTIVE' => 'text-green-600 bg-green-100',
            'PENDING' => 'text-orange-600 bg-orange-100',
            'SUSPENDED' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getRoleDisplayAttribute()
    {
        return match($this->role) {
            'CLIENT' => 'Client',
            'DELIVERER' => 'Livreur',
            'COMMERCIAL' => 'Commercial',
            'SUPERVISOR' => 'Superviseur',
            default => $this->role
        };
    }

    // ==================== VALIDATION METHODS ====================

    public function canBeValidated()
    {
        return $this->isClient() && $this->isPending();
    }

    public function canBeSuspended()
    {
        return $this->isActive();
    }

    public function canBeReactivated()
    {
        return $this->isSuspended();
    }

    public function hasValidEmail()
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function hasValidPhone()
    {
        return preg_match('/^[0-9+\-\s\(\)]{8,15}$/', $this->phone);
    }

    // ==================== ACTIVITY TRACKING ====================

    public function updateLastLogin()
    {
        $this->update(['last_login' => now()]);
        return $this;
    }

    public function getLastLoginFormatted()
    {
        if (!$this->last_login) {
            return 'Jamais connecté';
        }
        
        return $this->last_login->diffForHumans();
    }

    public function isRecentlyActive($days = 7)
    {
        if (!$this->last_login) {
            return false;
        }
        
        return $this->last_login >= now()->subDays($days);
    }

    // ==================== SEARCH METHODS ====================

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeByAccountStatus($query, $status)
    {
        return $query->where('account_status', $status);
    }

    public function scopeCreatedByCommercial($query, $commercialId)
    {
        return $query->where('created_by', $commercialId);
    }

    public function scopeValidatedByCommercial($query, $commercialId)
    {
        return $query->where('verified_by', $commercialId);
    }

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if (in_array($user->role, ['CLIENT', 'DELIVERER'])) {
                try {
                    UserWallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'pending_amount' => 0,
                        'frozen_amount' => 0,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Erreur création wallet pour user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        });

        static::updating(function ($user) {
            if (auth()->check() && auth()->id() === $user->id && !$user->isDirty('last_login')) {
                $user->last_login = now();
            }
        });

        static::updated(function ($user) {
            if ($user->isDirty('account_status') && app()->bound(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'USER_STATUS_CHANGED',
                    'User',
                    $user->id,
                    $user->getOriginal('account_status'),
                    $user->account_status,
                    [
                        'user_name' => $user->name,
                        'user_role' => $user->role
                    ]
                );
            }
        });
    }
}