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

    // Relations
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

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->where('account_status', 'ACTIVE');
    }

    // Helper methods
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

    // Créer automatiquement un wallet lors de la création d'un utilisateur
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            UserWallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_amount' => 0,
                'frozen_amount' => 0,
            ]);
        });
    }
}