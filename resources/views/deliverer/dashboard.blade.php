@extends('layouts.deliverer')

@section('title', 'Dashboard Livreur')

@section('content')
<div x-data="dashboardApp()" x-init="init()">
    <!-- Header de bienvenue -->
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl mx-4 mb-6 p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Bonjour, {{ auth()->user()->name }}</h1>
                <p class="text-emerald-100 mt-1">Prêt pour vos livraisons aujourd'hui?</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold" x-text="formatAmount(walletBalance)"></div>
                <p class="text-emerald-100 text-sm">Solde Wallet</p>
            </div>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mx-4 mb-6">
        <!-- Pickups Disponibles -->
        <a href="{{ route('deliverer.pickups.available') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Disponibles</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="stats.available_pickups || 0"></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Mes Pickups -->
        <a href="{{ route('deliverer.pickups.mine') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Mes Pickups</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="stats.my_pickups || 0"></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Livraisons -->
        <a href="{{ route('deliverer.deliveries.index') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Livraisons</p>
                    <p class="text-2xl font-bold text-orange-600" x-text="stats.deliveries || 0"></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Retours -->
        <a href="{{ route('deliverer.returns.index') }}" class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Retours</p>
                    <p class="text-2xl font-bold text-red-600" x-text="stats.returns || 0"></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Actions rapides -->
    <div class="mx-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h2>
        <div class="grid grid-cols-2 gap-4">
            <!-- Scanner QR -->
            <button @click="openScanner()" 
                    class="bg-gradient-to-r from-emerald-500 to-green-500 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                </svg>
                <p class="font-semibold text-center">Scanner QR</p>
                <p class="text-xs text-emerald-100 text-center mt-1">Scanner un code colis</p>
            </button>

            <!-- Mon Wallet -->
            <a href="{{ route('deliverer.wallet.index') }}" 
               class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="font-semibold text-center">Mon Wallet</p>
                <p class="text-xs text-purple-100 text-center mt-1" x-text="formatAmount(walletBalance)"></p>
            </a>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="mx-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Activité Aujourd'hui</h2>
            
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-green-600" x-text="stats.deliveries_today || 0"></p>
                    <p class="text-sm text-gray-600">Livrés</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600" x-text="formatAmount(stats.cod_collected_today || 0)"></p>
                    <p class="text-sm text-gray-600">COD Collecté</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['success_rate'] ?? 95 }}%</p>
                    <p class="text-sm text-gray-600">Taux Succès</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications importantes -->
    <div x-show="notifications.length > 0" class="mx-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-yellow-800 mb-2">Notifications Importantes</h3>
            <template x-for="notification in notifications.slice(0, 3)" :key="notification.id">
                <div class="text-sm text-yellow-700" x-text="notification.message"></div>
            </template>
        </div>
    </div>

    <!-- Menu Fonctionnalités -->
    <div class="mx-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Fonctionnalités</h2>
        <div class="grid grid-cols-2 gap-4">
            <!-- Paiements Clients -->
            <a href="{{ route('deliverer.payments.index') }}" 
               class="bg-white border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Paiements</p>
                        <p class="text-xs text-gray-500">Livraisons espèces</p>
                    </div>
                </div>
            </a>

            <!-- Recharge Client -->
            <a href="{{ route('deliverer.client-topup.index') }}" 
               class="bg-white border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.467-.22-2.121-.659-1.172-.879-1.172-2.303 0-3.182s3.07-.879 4.242 0L15 9m-3 9v1"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Recharge</p>
                        <p class="text-xs text-gray-500">Ajouter fonds</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
function dashboardApp() {
    return {
        stats: {
            available_pickups: {{ $stats['available_pickups'] ?? 0 }},
            my_pickups: {{ $stats['my_pickups'] ?? 0 }},
            deliveries: {{ $stats['deliveries'] ?? 0 }},
            returns: {{ $stats['returns'] ?? 0 }},
            payments: {{ $stats['payments'] ?? 0 }},
            deliveries_today: {{ $stats['deliveries_today'] ?? 0 }},
            cod_collected_today: {{ $stats['cod_collected_today'] ?? 0 }}
        },
        walletBalance: {{ $walletData['balance'] ?? 0 }},
        notifications: [],

        init() {
            this.loadStats();
            this.loadNotifications();
            
            // Auto-refresh toutes les 30 secondes
            setInterval(() => {
                this.loadStats();
            }, 30000);
        },

        async loadStats() {
            try {
                const response = await fetch('/deliverer/api/dashboard-stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },

        async loadNotifications() {
            try {
                const response = await fetch('/deliverer/api/notifications/recent');
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                }
            } catch (error) {
                console.error('Erreur chargement notifications:', error);
            }
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        }
    }
}
</script>
@endsection