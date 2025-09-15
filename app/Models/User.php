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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'account_status',
        'verified_at',
        'verified_by',
        'created_by',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];

    // ==================== RELATIONS ====================

    /**
     * Colis envoyés par ce client
     */
    public function sentPackages()
    {
        return $this->hasMany(Package::class, 'sender_id');
    }

    /**
     * Alias pour sentPackages (pour compatibilité)
     */
    public function packages()
    {
        return $this->sentPackages();
    }

    /**
     * Colis assignés à ce livreur
     */
    public function assignedPackages()
    {
        return $this->hasMany(Package::class, 'assigned_deliverer_id');
    }

    /**
     * Relation avec le portefeuille
     */
    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    /**
     * Relation avec le profil client
     */
    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    /**
     * Adresses sauvegardées
     */
    public function savedAddresses()
    {
        return $this->hasMany(SavedAddress::class);
    }

    /**
     * Réclamations créées par ce client
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'client_id');
    }

    /**
     * Réclamations assignées à ce commercial
     */
    public function assignedComplaints()
    {
        return $this->hasMany(Complaint::class, 'assigned_commercial_id');
    }

    /**
     * Demandes de retrait
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'client_id');
    }

    /**
     * Demandes de retrait traitées par ce commercial
     */
    public function processedWithdrawals()
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by_commercial_id');
    }

    /**
     * Notifications de l'utilisateur
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Lots d'import CSV
     */
    public function importBatches()
    {
        return $this->hasMany(ImportBatch::class);
    }

    /**
     * Relation avec les transactions financières
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Utilisateur qui a vérifié ce compte
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Utilisateur qui a créé ce compte
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Comptes vérifiés par cet utilisateur
     */
    public function verifiedUsers()
    {
        return $this->hasMany(User::class, 'verified_by');
    }

    /**
     * Comptes créés par cet utilisateur
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Modifications COD effectuées par ce commercial
     */
    public function codModifications()
    {
        return $this->hasMany(CodModification::class, 'modified_by_commercial_id');
    }

    /**
     * Vidanges de portefeuille (pour les livreurs)
     */
    public function walletEmptyings()
    {
        return $this->hasMany(DelivererWalletEmptying::class, 'deliverer_id');
    }

    /**
     * Vidanges traitées par ce commercial
     */
    public function processedWalletEmptyings()
    {
        return $this->hasMany(DelivererWalletEmptying::class, 'commercial_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('account_status', 'ACTIVE');
    }

    /**
     * Scope pour les utilisateurs en attente
     */
    public function scopePending($query)
    {
        return $query->where('account_status', 'PENDING');
    }

    /**
     * Scope pour les utilisateurs suspendus
     */
    public function scopeSuspended($query)
    {
        return $query->where('account_status', 'SUSPENDED');
    }

    /**
     * Scope par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope pour les clients
     */
    public function scopeClients($query)
    {
        return $query->where('role', 'CLIENT');
    }

    /**
     * Scope pour les livreurs
     */
    public function scopeDeliverers($query)
    {
        return $query->where('role', 'DELIVERER');
    }

    /**
     * Scope pour les commerciaux
     */
    public function scopeCommercials($query)
    {
        return $query->where('role', 'COMMERCIAL');
    }

    /**
     * Scope pour les superviseurs
     */
    public function scopeSupervisors($query)
    {
        return $query->where('role', 'SUPERVISOR');
    }

    /**
     * Scope pour les utilisateurs vérifiés
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope pour la recherche
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Obtenir les initiales du nom
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return substr($initials, 0, 2); // Limiter à 2 caractères
    }

    /**
     * Obtenir le nom formaté avec le rôle
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->getRoleDisplayAttribute() . ')';
    }

    /**
     * Affichage du rôle en français
     */
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

    /**
     * Affichage du statut en français
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->account_status) {
            'PENDING' => 'En attente',
            'ACTIVE' => 'Actif',
            'SUSPENDED' => 'Suspendu',
            default => $this->account_status
        };
    }

    /**
     * Couleur du statut pour l'affichage
     */
    public function getStatusColorAttribute()
    {
        return match($this->account_status) {
            'PENDING' => 'text-yellow-600 bg-yellow-100',
            'ACTIVE' => 'text-green-600 bg-green-100',
            'SUSPENDED' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    // ==================== MÉTHODES DE VÉRIFICATION ====================

    /**
     * Vérifier si le compte est actif
     */
    public function isActive()
    {
        return $this->account_status === 'ACTIVE';
    }

    /**
     * Vérifier si le compte est en attente
     */
    public function isPending()
    {
        return $this->account_status === 'PENDING';
    }

    /**
     * Vérifier si le compte est suspendu
     */
    public function isSuspended()
    {
        return $this->account_status === 'SUSPENDED';
    }

    /**
     * Vérifier si c'est un client
     */
    public function isClient()
    {
        return $this->role === 'CLIENT';
    }

    /**
     * Vérifier si c'est un livreur
     */
    public function isDeliverer()
    {
        return $this->role === 'DELIVERER';
    }

    /**
     * Vérifier si c'est un commercial
     */
    public function isCommercial()
    {
        return $this->role === 'COMMERCIAL';
    }

    /**
     * Vérifier si c'est un superviseur
     */
    public function isSupervisor()
    {
        return $this->role === 'SUPERVISOR';
    }

    /**
     * Vérifier si l'utilisateur est staff (commercial ou superviseur)
     */
    public function isStaff()
    {
        return in_array($this->role, ['COMMERCIAL', 'SUPERVISOR']);
    }

    /**
     * Vérifier si le compte est vérifié
     */
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    // ==================== MÉTHODES DE GESTION ====================

    /**
     * Assurer qu'un portefeuille existe pour cet utilisateur
     */
    public function ensureWallet()
    {
        if (!$this->wallet) {
            $this->wallet()->create([
                'balance' => 0.000,
                'pending_amount' => 0.000,
                'frozen_amount' => 0.000
            ]);
            $this->load('wallet');
        }
        return $this->wallet;
    }

    /**
     * Activer le compte
     */
    public function activate($verifiedBy = null)
    {
        $this->update([
            'account_status' => 'ACTIVE',
            'verified_at' => now(),
            'verified_by' => $verifiedBy
        ]);

        // Créer le portefeuille si c'est un client ou livreur
        if (in_array($this->role, ['CLIENT', 'DELIVERER'])) {
            $this->ensureWallet();
        }

        return $this;
    }

    /**
     * Suspendre le compte
     */
    public function suspend()
    {
        $this->update(['account_status' => 'SUSPENDED']);
        return $this;
    }

    /**
     * Marquer la dernière connexion
     */
    public function markLastLogin()
    {
        $this->update(['last_login' => now()]);
        return $this;
    }

    // ==================== STATISTIQUES ====================

    /**
     * Obtenir les statistiques du client
     */
    public function getClientStats($period = null)
    {
        if (!$this->isClient()) {
            return null;
        }

        $packagesQuery = $this->sentPackages();
        
        if ($period) {
            $packagesQuery->where('created_at', '>=', now()->sub($period));
        }

        $packages = $packagesQuery->get();
        
        return [
            'total_packages' => $packages->count(),
            'in_progress_packages' => $packages->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count(),
            'delivered_packages' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned_packages' => $packages->where('status', 'RETURNED')->count(),
            'total_cod_amount' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->sum('cod_amount'),
            'pending_complaints' => $this->complaints()->where('status', 'PENDING')->count(),
            'pending_withdrawals' => $this->withdrawalRequests()->where('status', 'PENDING')->count(),
            'wallet_balance' => $this->wallet ? $this->wallet->balance : 0,
            'wallet_pending' => $this->wallet ? $this->wallet->pending_amount : 0,
        ];
    }

    /**
     * Obtenir les statistiques du livreur
     */
    public function getDelivererStats($period = null)
    {
        if (!$this->isDeliverer()) {
            return null;
        }

        $packagesQuery = $this->assignedPackages();
        
        if ($period) {
            $packagesQuery->where('assigned_at', '>=', now()->sub($period));
        }

        $packages = $packagesQuery->get();
        
        return [
            'total_assigned' => $packages->count(),
            'delivered_packages' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned_packages' => $packages->where('status', 'RETURNED')->count(),
            'refused_packages' => $packages->where('status', 'REFUSED')->count(),
            'pending_packages' => $packages->whereIn('status', ['ACCEPTED', 'PICKED_UP'])->count(),
            'total_earnings' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->sum('delivery_fee'),
            'wallet_balance' => $this->wallet ? $this->wallet->balance : 0,
        ];
    }

    /**
     * Packages créés aujourd'hui
     */
    public function todayPackages()
    {
        return $this->sentPackages()->whereDate('created_at', today());
    }

    /**
     * Revenus du jour (COD des colis livrés aujourd'hui)
     */
    public function todayRevenue()
    {
        return $this->sentPackages()
                    ->whereDate('updated_at', today())
                    ->whereIn('status', ['DELIVERED', 'PAID'])
                    ->sum('cod_amount');
    }

    /**
     * Packages livrés ce mois
     */
    public function thisMonthDelivered()
    {
        return $this->sentPackages()
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->whereIn('status', ['DELIVERED', 'PAID'])
                    ->count();
    }

    /**
     * Revenus du mois
     */
    public function thisMonthRevenue()
    {
        return $this->sentPackages()
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->whereIn('status', ['DELIVERED', 'PAID'])
                    ->sum('cod_amount');
    }

    // ==================== MÉTHODES POUR LES ADRESSES ====================

    /**
     * Obtenir les adresses fournisseurs sauvegardées
     */
    public function getSupplierAddresses($limit = null)
    {
        $query = $this->savedAddresses()
                      ->suppliers()
                      ->with('delegation')
                      ->orderBy('last_used_at', 'desc')
                      ->orderBy('use_count', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Obtenir les adresses clients sauvegardées
     */
    public function getClientAddresses($limit = null)
    {
        $query = $this->savedAddresses()
                      ->clients()
                      ->with('delegation')
                      ->orderBy('last_used_at', 'desc')
                      ->orderBy('use_count', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    // ==================== MÉTHODES POUR LES NOTIFICATIONS ====================

    /**
     * Notifications non lues
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('read', false);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllNotificationsAsRead()
    {
        $this->notifications()->where('read', false)->update([
            'read' => true,
            'read_at' => now()
        ]);
        
        return $this;
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Obtenir le portefeuille avec création automatique si nécessaire
     */
    public function getWalletAttribute()
    {
        // Charger la relation si elle n'est pas déjà chargée
        if (!$this->relationLoaded('wallet')) {
            $this->load('wallet');
        }
        
        // Si pas de portefeuille et que c'est un client/livreur, le créer
        if (!$this->getRelationValue('wallet') && in_array($this->role, ['CLIENT', 'DELIVERER'])) {
            $this->ensureWallet();
        }
        
        return $this->getRelationValue('wallet');
    }

    /**
     * Vérifier si l'utilisateur peut créer des colis
     */
    public function canCreatePackages()
    {
        return $this->isClient() && 
               $this->isActive() && 
               $this->clientProfile && 
               $this->wallet && 
               $this->wallet->balance > 0;
    }

    /**
     * Vérifier si l'utilisateur peut effectuer des livraisons
     */
    public function canDeliverPackages()
    {
        return $this->isDeliverer() && $this->isActive();
    }

    /**
     * Formater le numéro de téléphone
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        
        // Format tunisien basique
        $phone = preg_replace('/[^\d]/', '', $this->phone);
        
        if (strlen($phone) === 8) {
            return substr($phone, 0, 2) . ' ' . substr($phone, 2, 3) . ' ' . substr($phone, 5);
        }
        
        return $this->phone;
    }

    // ==================== BOOT METHOD ====================

    /**
     * Boot method pour les événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Créer automatiquement un portefeuille pour les clients et livreurs
        static::created(function ($user) {
            if (in_array($user->role, ['CLIENT', 'DELIVERER'])) {
                $user->ensureWallet();
            }
        });

        // Nettoyer les relations lors de la suppression
        static::deleting(function ($user) {
            // Supprimer le portefeuille
            if ($user->wallet) {
                $user->wallet->delete();
            }
            
            // Supprimer les adresses sauvegardées
            $user->savedAddresses()->delete();
            
            // Supprimer les notifications
            $user->notifications()->delete();
        });
    }
}