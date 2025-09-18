@extends('layouts.deliverer')

@section('title', 'Dashboard Livreur')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="delivererDashboard()">
    
    <!-- Hero Section with Wallet -->
    <div class="bg-gradient-to-br from-emerald-600 to-green-700 text-white">
        <div class="px-4 py-6">
            <!-- Greeting & Status -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Bonjour, {{ explode(' ', auth()->user()->name)[0] }} !</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        <div class="w-2 h-2 bg-green-300 rounded-full animate-pulse" x-show="isOnline"></div>
                        <div class="w-2 h-2 bg-red-400 rounded-full" x-show="!isOnline"></div>
                        <span class="text-emerald-100 text-sm" x-text="isOnline ? 'Connecté' : 'Hors ligne'"></span>
                        <span class="text-emerald-200 text-xs">•</span>
                        <span class="text-emerald-100 text-sm" x-text="currentTime"></span>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex space-x-2">
                    <button @click="openScanner()" 
                            class="p-2 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Wallet Card -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium">Solde Wallet</p>
                        <p class="text-2xl font-bold" x-text="formatAmount(wallet.balance)"></p>
                        <div class="flex items-center space-x-4 mt-2 text-sm">
                            <span class="text-emerald-200">
                                <span x-text="stats.deliveries_today"></span> livrés aujourd'hui
                            </span>
                            <span class="text-emerald-200">•</span>
                            <span class="text-emerald-200">
                                <span x-text="formatAmount(stats.cod_collected_today)"></span> COD
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <button @click="$dispatch('open-wallet')" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </button>
                        <p class="text-xs text-emerald-200 mt-1">Détails</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="px-4 -mt-8 relative z-10 mb-6">
        <div class="grid grid-cols-2 gap-4">
            <!-- Pickups Disponibles -->
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-600" x-text="stats.available_pickups"></p>
                        <p class="text-xs text-gray-500">Disponibles</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm font-medium text-gray-900">Pickups</p>
                    <p class="text-xs text-gray-500">Premier arrivé = premier servi</p>
                </div>
            </a>

            <!-- Mes Pickups -->
            <a href="{{ route('deliverer.pickups.mine') }}" 
               class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-600" x-text="stats.my_pickups"></p>
                        <p class="text-xs text-gray-500">À collecter</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm font-medium text-gray-900">Mes Pickups</p>
                    <p class="text-xs text-gray-500">Colis acceptés</p>
                </div>
            </a>

            <!-- Livraisons -->
            <a href="{{ route('deliverer.deliveries.index') }}" 
               class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-orange-600" x-text="stats.deliveries"></p>
                        <p class="text-xs text-gray-500">À livrer</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm font-medium text-gray-900">Livraisons</p>
                    <p class="text-xs text-gray-500">Colis collectés</p>
                </div>
            </a>

            <!-- Retours -->
            <a href="{{ route('deliverer.returns.index') }}" 
               class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-red-600" x-text="stats.returns"></p>
                        <p class="text-xs text-gray-500">À retourner</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm font-medium text-gray-900">Retours</p>
                    <p class="text-xs text-gray-500">Vers expéditeurs</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Actions Rapides</h2>
        <div class="grid grid-cols-3 gap-3">
            <!-- Scanner QR -->
            <button @click="openScanner()" 
                    class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900">Scanner</p>
                <p class="text-xs text-gray-500">QR/Code</p>
            </button>

            <!-- Feuille de Route -->
            <a href="{{ route('deliverer.runsheets.index') }}" 
               class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900">Feuille</p>
                <p class="text-xs text-gray-500">Route</p>
            </a>

            <!-- Recharge Client -->
            <a href="{{ route('deliverer.client-topup.index') }}" 
               class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.467-.22-2.121-.659-1.172-.879-1.172-2.303 0-3.182s3.07-.879 4.242 0L15 9m-3 9v1"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-900">Recharge</p>
                <p class="text-xs text-gray-500">Client</p>
            </a>
        </div>
    </div>

    <!-- Activité Récente -->
    <div class="px-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Activité Récente</h2>
            <button @click="refreshActivity()" 
                    class="text-emerald-600 text-sm font-medium hover:text-emerald-700">
                Actualiser
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm" x-show="recentActivity.length > 0">
            <template x-for="(activity, index) in recentActivity" :key="activity.id">
                <div class="p-4 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                             :class="getActivityColor(activity.type)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="getActivityIcon(activity.type)"/>
                            </svg>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                            <p class="text-sm text-gray-600" x-text="activity.description"></p>
                            <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
                                <span x-text="formatActivityDate(activity.created_at)"></span>
                                <template x-if="activity.amount">
                                    <span class="text-emerald-600 font-medium" x-text="'+' + formatAmount(activity.amount)"></span>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Badge -->
                        <div class="flex-shrink-0">
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                  :class="getActivityBadgeColor(activity.type)"
                                  x-text="getActivityBadgeText(activity.type)"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="recentActivity.length === 0" class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-gray-500 text-sm">Aucune activité récente</p>
        </div>
    </div>

    <!-- Statistiques Personnelles -->
    <div class="px-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Mes Performances</h2>
        
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.total_delivered_month"></p>
                    <p class="text-xs text-gray-500">Livrés ce mois</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-emerald-600" x-text="formatAmount(stats.earnings_month)"></p>
                    <p class="text-xs text-gray-500">Revenus mois</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600" x-text="stats.success_rate + '%'"></p>
                    <p class="text-xs text-gray-500">Taux réussite</p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('deliverer.statistics') }}" 
                   class="block text-center text-emerald-600 text-sm font-medium hover:text-emerald-700">
                    Voir statistiques complètes →
                </a>
            </div>
        </div>
    </div>

    <!-- Support & Aide -->
    <div class="px-4 mb-20">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-2xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold mb-1">Besoin d'aide ?</h3>
                    <p class="text-gray-300 text-sm">Support disponible 24/7</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('deliverer.help.index') }}" 
                       class="bg-white bg-opacity-10 hover:bg-opacity-20 p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </a>
                    <button @click="contactSupport()" 
                            class="bg-white bg-opacity-10 hover:bg-opacity-20 p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function delivererDashboard() {
    return {
        isOnline: navigator.onLine,
        currentTime: '',
        wallet: {
            balance: 0
        },
        stats: {
            available_pickups: 0,
            my_pickups: 0,
            deliveries: 0,
            returns: 0,
            payments: 0,
            deliveries_today: 0,
            cod_collected_today: 0,
            total_delivered_month: 0,
            earnings_month: 0,
            success_rate: 0
        },
        recentActivity: [],
        loading: false,

        init() {
            this.updateTime();
            this.loadDashboardData();
            
            // Update time every minute
            setInterval(() => {
                this.updateTime();
            }, 60000);
            
            // Auto-refresh data every 30 seconds
            setInterval(() => {
                this.loadDashboardData();
            }, 30000);

            // Network status
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.loadDashboardData(); // Sync when back online
            });
            
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });
        },

        updateTime() {
            this.currentTime = new Date().toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        async loadDashboardData() {
            if (!this.isOnline) return;

            try {
                // Load stats
                const statsResponse = await fetch('/deliverer/api/dashboard-stats');
                if (statsResponse.ok) {
                    this.stats = { ...this.stats, ...await statsResponse.json() };
                }

                // Load wallet
                const walletResponse = await fetch('/deliverer/api/wallet/balance');
                if (walletResponse.ok) {
                    this.wallet = { ...this.wallet, ...await walletResponse.json() };
                }

                // Load recent activity
                const activityResponse = await fetch('/deliverer/api/recent-activity');
                if (activityResponse.ok) {
                    this.recentActivity = await activityResponse.json();
                }

            } catch (error) {
                console.error('Erreur chargement dashboard:', error);
            }
        },

        async refreshActivity() {
            this.loading = true;
            await this.loadDashboardData();
            this.loading = false;
            this.showToast('Données actualisées');
        },

        openScanner() {
            // Trigger scanner from parent layout
            this.$dispatch('open-scanner');
        },

        contactSupport() {
            // Contact support functionality
            window.location.href = 'tel:+21670123456'; // Replace with actual support number
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatActivityDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffMinutes < 1) return 'À l\'instant';
            if (diffMinutes < 60) return `Il y a ${diffMinutes}min`;
            
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `Il y a ${diffHours}h`;
            
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit'
            });
        },

        getActivityColor(type) {
            const colors = {
                'pickup_accepted': 'bg-blue-100 text-blue-600',
                'package_delivered': 'bg-green-100 text-green-600',
                'package_returned': 'bg-red-100 text-red-600',
                'cod_collected': 'bg-emerald-100 text-emerald-600',
                'payment_delivered': 'bg-purple-100 text-purple-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },

        getActivityIcon(type) {
            const icons = {
                'pickup_accepted': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'package_delivered': 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                'package_returned': 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
                'cod_collected': 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                'payment_delivered': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
            };
            return icons[type] || 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        },

        getActivityBadgeColor(type) {
            const colors = {
                'pickup_accepted': 'bg-blue-100 text-blue-800',
                'package_delivered': 'bg-green-100 text-green-800',
                'package_returned': 'bg-red-100 text-red-800',
                'cod_collected': 'bg-emerald-100 text-emerald-800',
                'payment_delivered': 'bg-purple-100 text-purple-800'
            };
            return colors[type] || 'bg-gray-100 text-gray-800';
        },

        getActivityBadgeText(type) {
            const texts = {
                'pickup_accepted': 'Accepté',
                'package_delivered': 'Livré',
                'package_returned': 'Retourné',
                'cod_collected': 'COD',
                'payment_delivered': 'Paiement'
            };
            return texts[type] || 'Info';
        },

        showToast(message, type = 'success') {
            // Create and show toast notification
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection