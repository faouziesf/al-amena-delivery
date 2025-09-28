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
        'assigned_delegation',
        'deliverer_type',
        'delegation_latitude',
        'delegation_longitude',
        'delegation_radius_km',
        // Champs chef dépôt
        'assigned_gouvernorats',
        'depot_name',
        'depot_address',
        'is_depot_manager',
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
        'assigned_gouvernorats' => 'array',
        'is_depot_manager' => 'boolean',
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
     * Demandes de rechargement du client
     */
    public function topupRequests()
    {
        return $this->hasMany(TopupRequest::class, 'client_id');
    }

    /**
     * Demandes de rechargement traitées par ce commercial/livreur
     */
    public function processedTopupRequests()
    {
        return $this->hasMany(TopupRequest::class, 'processed_by_id');
    }

    /**
     * Demandes de collecte créées par ce client
     */
    public function pickupRequests()
    {
        return $this->hasMany(PickupRequest::class, 'client_id');
    }

    /**
     * Demandes de collecte assignées à ce livreur
     */
    public function assignedPickupRequests()
    {
        return $this->hasMany(PickupRequest::class, 'assigned_deliverer_id');
    }

    /**
     * Adresses de collecte de ce client
     */
    public function clientPickupAddresses()
    {
        return $this->hasMany(ClientPickupAddress::class, 'client_id');
    }

    /**
     * Comptes bancaires du client
     */
    public function bankAccounts()
    {
        return $this->hasMany(ClientBankAccount::class, 'client_id');
    }

    /**
     * Alias pour les adresses de pickup
     */
    public function pickupAddresses()
    {
        return $this->clientPickupAddresses();
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

    /**
     * Tickets créés par ce client
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'client_id');
    }

    /**
     * Tickets assignés à ce commercial/superviseur
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to_id');
    }

    /**
     * Messages de tickets envoyés par cet utilisateur
     */
    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class, 'sender_id');
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
            'DEPOT_MANAGER' => 'Chef Dépôt',
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
        // Utiliser une vérification plus sûre
        $wallet = $this->wallet()->first();

        if (!$wallet) {
            $wallet = $this->wallet()->create([
                'balance' => 0.000,
                'pending_amount' => 0.000,
                'frozen_amount' => 0.000
            ]);
            $this->setRelation('wallet', $wallet);
        }

        return $wallet;
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
     * Obtenir les statistiques des demandes de rechargement
     */
    public function getTopupRequestsStats($period = null)
    {
        if (!$this->isClient()) {
            return null;
        }

        $query = $this->topupRequests();
        
        if ($period) {
            $query->where('created_at', '>=', now()->sub($period));
        }

        $requests = $query->get();
        
        return [
            'total_requests' => $requests->count(),
            'pending_requests' => $requests->where('status', 'PENDING')->count(),
            'validated_requests' => $requests->where('status', 'VALIDATED')->count(),
            'rejected_requests' => $requests->where('status', 'REJECTED')->count(),
            'cancelled_requests' => $requests->where('status', 'CANCELLED')->count(),
            'total_amount_requested' => $requests->sum('amount'),
            'total_amount_validated' => $requests->where('status', 'VALIDATED')->sum('amount'),
            'total_amount_pending' => $requests->where('status', 'PENDING')->sum('amount'),
            'bank_transfers' => $requests->whereIn('method', ['BANK_TRANSFER', 'BANK_DEPOSIT'])->count(),
            'cash_payments' => $requests->where('method', 'CASH')->count(),
        ];
    }

    /**
     * Demandes de rechargement récentes
     */
    public function recentTopupRequests($limit = 5)
    {
        return $this->topupRequests()
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Vérifier si l'utilisateur a des demandes en attente
     */
    public function hasPendingTopupRequests()
    {
        return $this->topupRequests()->pending()->exists();
    }

    /**
     * Dernière demande de rechargement
     */
    public function lastTopupRequest()
    {
        return $this->topupRequests()
                    ->orderBy('created_at', 'desc')
                    ->first();
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
     * Vérifier si l'utilisateur peut traiter des demandes de rechargement bancaires
     */
    public function canProcessBankTopupRequests()
    {
        return $this->isCommercial() && $this->isActive();
    }

    /**
     * Vérifier si l'utilisateur peut traiter des demandes de rechargement espèces
     */
    public function canProcessCashTopupRequests()
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

    /**
     * Obtenir le résumé de l'activité récente
     */
    public function getRecentActivitySummary($days = 7)
    {
        $since = now()->subDays($days);
        
        return [
            'packages_created' => $this->sentPackages()->where('created_at', '>=', $since)->count(),
            'packages_delivered' => $this->isDeliverer() ? $this->assignedPackages()->whereIn('status', ['DELIVERED', 'PAID'])->where('updated_at', '>=', $since)->count() : 0,
            'transactions_count' => $this->transactions()->where('created_at', '>=', $since)->count(),
            'topup_requests' => $this->topupRequests()->where('created_at', '>=', $since)->count(),
            'complaints_created' => $this->complaints()->where('created_at', '>=', $since)->count(),
            'period_days' => $days
        ];
    }

    // ==================== BOOT METHOD ====================

    // ==================== DELEGATION METHODS ====================

    /**
     * Vérifier si le livreur est assigné à une délégation
     */
    public function hasAssignedDelegation()
    {
        return $this->role === 'DELIVERER' && !empty($this->assigned_delegation);
    }

    /**
     * Vérifier si le livreur est de type DELEGATION (délégation fixe)
     */
    public function isDelegationDeliverer()
    {
        return $this->role === 'DELIVERER' && $this->deliverer_type === 'DELEGATION';
    }

    /**
     * Vérifier si le livreur est de type JOKER (toutes délégations)
     */
    public function isJokerDeliverer()
    {
        return $this->role === 'DELIVERER' && $this->deliverer_type === 'JOKER';
    }

    /**
     * Vérifier si le livreur est de type TRANSIT (changement uniquement)
     */
    public function isTransitDeliverer()
    {
        return $this->role === 'DELIVERER' && $this->deliverer_type === 'TRANSIT';
    }

    /**
     * Vérifier si le livreur peut voir les pickup requests
     */
    public function canViewPickupRequests()
    {
        if ($this->role !== 'DELIVERER') {
            return false;
        }

        // Les livreurs TRANSIT ne peuvent pas voir les pickup requests
        return $this->deliverer_type !== 'TRANSIT';
    }

    /**
     * Vérifier si le livreur peut effectuer des changements de livreur
     */
    public function canReassignPackages()
    {
        return $this->role === 'DELIVERER' && $this->deliverer_type === 'TRANSIT';
    }

    /**
     * Obtenir les colis dans la délégation du livreur
     */
    public function getDelegationPackages()
    {
        if (!$this->hasAssignedDelegation()) {
            return collect();
        }

        return Package::where('delegation_to', $this->assigned_delegation)->get();
    }

    /**
     * Obtenir les pickup requests selon le type de livreur
     */
    public function getDelegationPickupRequests()
    {
        if (!$this->canViewPickupRequests()) {
            return PickupRequest::where('id', 0); // Query vide pour TRANSIT
        }

        if ($this->isJokerDeliverer()) {
            // Les livreurs JOKER voient tous les pickup requests
            return PickupRequest::query();
        }

        if ($this->isDelegationDeliverer() && $this->hasAssignedDelegation()) {
            // Les livreurs DELEGATION voient seulement leur délégation
            return PickupRequest::where('delegation', $this->assigned_delegation);
        }

        return PickupRequest::where('id', 0); // Query vide par défaut
    }

    /**
     * Vérifier si un colis peut être auto-assigné à ce livreur
     */
    public function canAutoAssignPackage(Package $package)
    {
        return $this->hasAssignedDelegation() &&
               $package->delegation_to === $this->assigned_delegation &&
               $package->status === 'PICKED_UP' &&
               !$package->assigned_deliverer_id;
    }

    /**
     * Obtenir tous les livreurs d'une délégation
     */
    public static function getDeliverersInDelegation($delegation)
    {
        return self::where('role', 'DELIVERER')
                   ->where('assigned_delegation', $delegation)
                   ->where('account_status', 'ACTIVE')
                   ->get();
    }

    /**
     * Obtenir la liste des délégations disponibles
     */
    public static function getAvailableDelegations()
    {
        return [
            'Tunis' => 'Tunis',
            'Ariana' => 'Ariana',
            'Ben Arous' => 'Ben Arous',
            'Manouba' => 'Manouba',
            'Nabeul' => 'Nabeul',
            'Zaghouan' => 'Zaghouan',
            'Bizerte' => 'Bizerte',
            'Béja' => 'Béja',
            'Jendouba' => 'Jendouba',
            'Kef' => 'Kef',
            'Siliana' => 'Siliana',
            'Sousse' => 'Sousse',
            'Monastir' => 'Monastir',
            'Mahdia' => 'Mahdia',
            'Sfax' => 'Sfax',
            'Kairouan' => 'Kairouan',
            'Kasserine' => 'Kasserine',
            'Sidi Bouzid' => 'Sidi Bouzid',
            'Gabès' => 'Gabès',
            'Medenine' => 'Medenine',
            'Tataouine' => 'Tataouine',
            'Gafsa' => 'Gafsa',
            'Tozeur' => 'Tozeur',
            'Kebili' => 'Kebili'
        ];
    }

    /**
     * Obtenir les types de livreurs disponibles
     */
    public static function getDelivererTypes()
    {
        return [
            'DELEGATION' => 'Délégation fixe',
            'JOKER' => 'Joker (toutes délégations)',
            'TRANSIT' => 'Transit (changement uniquement)'
        ];
    }

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
            
            // Marquer les demandes de rechargement comme annulées
            $user->topupRequests()->where('status', 'PENDING')->update([
                'status' => 'CANCELLED',
                'processed_at' => now(),
                'rejection_reason' => 'Compte utilisateur supprimé'
            ]);
        });
    }

    // ==================== MÉTHODES CHEF DÉPÔT ====================

    /**
     * Vérifier si c'est un chef dépôt
     */
    public function isDepotManager()
    {
        return $this->role === 'DEPOT_MANAGER' || $this->is_depot_manager;
    }

    /**
     * Obtenir les livreurs de ce chef dépôt
     */
    public function getManagedDeliverers()
    {
        if (!$this->isDepotManager() || empty($this->assigned_gouvernorats_array)) {
            return collect();
        }

        return User::where('role', 'DELIVERER')
                   ->where('account_status', 'ACTIVE')
                   ->whereIn('assigned_delegation', $this->assigned_gouvernorats_array)
                   ->with(['assignedPackages' => function($q) {
                       $q->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                         ->select('id', 'assigned_deliverer_id', 'status', 'cod_amount');
                   }])
                   ->get();
    }

    /**
     * Accesseur pour obtenir les gouvernorats assignés comme array
     */
    public function getAssignedGouvernoratsArrayAttribute()
    {
        if (is_string($this->assigned_gouvernorats)) {
            $decoded = json_decode($this->assigned_gouvernorats, true);
            return is_array($decoded) ? $decoded : [];
        }
        if (is_array($this->assigned_gouvernorats)) {
            return $this->assigned_gouvernorats;
        }
        return [];
    }

    /**
     * Vérifier si ce chef dépôt peut gérer un gouvernorat
     */
    public function canManageGouvernorat($gouvernorat)
    {
        if (!$this->isDepotManager()) {
            return false;
        }

        return in_array($gouvernorat, $this->assigned_gouvernorats_array);
    }

    /**
     * Obtenir les statistiques du dépôt
     */
    public function getDepotStats()
    {
        if (!$this->isDepotManager()) {
            return null;
        }

        $deliverers = $this->getManagedDeliverers();
        $delivererIds = $deliverers->pluck('id')->toArray();

        return [
            'total_deliverers' => $deliverers->count(),
            'active_deliverers' => $deliverers->where('account_status', 'ACTIVE')->count(),
            'packages_in_progress' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                           ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                                           ->count(),
            'delivered_today' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                      ->where('status', 'DELIVERED')
                                      ->whereDate('delivered_at', today())
                                      ->count(),
            'cod_collected_today' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                          ->where('status', 'DELIVERED')
                                          ->whereDate('delivered_at', today())
                                          ->sum('cod_amount'),
            'assigned_gouvernorats' => $this->assigned_gouvernorats_array,
        ];
    }

    /**
     * Obtenir les clients des gouvernorats gérés par ce chef dépôt
     */
    public function getManagedClients()
    {
        if (!$this->isDepotManager()) {
            return collect([]);
        }

        // Pour l'instant, retourner tous les clients
        // À adapter selon la logique métier (clients par gouvernorat)
        return User::where('role', 'CLIENT')
                  ->where('account_status', 'ACTIVE')
                  ->get();
    }

    /**
     * Vérifier si ce chef dépôt peut gérer un client spécifique
     */
    public function canManageClient($clientId)
    {
        if (!$this->isDepotManager()) {
            return false;
        }

        // Pour l'instant, autoriser tous les clients
        // À adapter selon la logique métier
        $client = User::find($clientId);
        return $client && $client->role === 'CLIENT';
    }
}