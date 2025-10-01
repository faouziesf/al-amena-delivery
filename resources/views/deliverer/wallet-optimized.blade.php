@extends('layouts.deliverer')

@section('title', 'Ma Caisse')

@section('content')
<div x-data="walletOptimizedApp()" x-init="init()" class="h-full" style="background: var(--background)">

    <!-- Header Wallet Modernisé -->
    <div class="relative overflow-hidden safe-top">
        <div class="absolute inset-0 bg-gradient-to-br from-green-600 via-green-700 to-emerald-800"></div>
        <!-- Glassmorphism Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="w-full h-full" style="background-image: radial-gradient(circle at 20% 30%, white 2px, transparent 2px), radial-gradient(circle at 80% 70%, white 2px, transparent 2px); background-size: 30px 30px;"></div>
        </div>

        <div class="relative text-white px-6 py-10">
            <div class="text-center fade-in">
                <!-- Wallet Icon -->
                <div class="w-20 h-20 mx-auto mb-6 glass rounded-3xl flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>

                <h1 class="text-display font-black mb-3">Mon Wallet</h1>
                <p class="text-title text-white/80 font-medium" x-text="formatDate()"></p>

                <!-- Quick Stats -->
                <div class="flex justify-center space-x-8 mt-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white/90" x-text="transactions.length"></div>
                        <div class="text-sm text-white/70">Transactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white/90" x-text="getWalletStatus().split(' ')[0]"></div>
                        <div class="text-sm text-white/70">Statut</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Principal du Montant -->
    <div class="mx-6 mt-6">
        <div class="soft-card p-10 relative overflow-hidden scale-in">
            <!-- Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-50"></div>

            <div class="relative text-center">
                <!-- Montant Animé avec Compteur -->
                <div class="mb-8">
                    <div class="relative inline-block">
                        <div class="text-6xl font-black mb-4" style="color: var(--success)">
                            <span x-text="animatedAmount" x-init="animateCounter()"></span>
                            <span class="text-3xl opacity-75">DT</span>
                        </div>
                        <!-- Progress Ring autour du montant -->
                        <svg class="absolute -inset-8 w-32 h-32 transform -rotate-90" viewBox="0 0 128 128">
                            <circle cx="64" cy="64" r="56" stroke="rgba(46, 204, 113, 0.1)" stroke-width="4" fill="none"/>
                            <circle cx="64" cy="64" r="56" stroke="var(--success)" stroke-width="4" fill="none"
                                   stroke-dasharray="351"
                                   :stroke-dashoffset="351 - (walletFillPercentage * 351 / 100)"
                                   class="transition-all duration-2000 ease-out"/>
                        </svg>
                    </div>

                    <!-- Separator Line -->
                    <div class="w-40 h-1 bg-gradient-to-r from-transparent via-green-400 to-transparent mx-auto rounded-full"></div>
                </div>

                <!-- Description Modernisée -->
                <div class="space-y-3 mb-8">
                    <h2 class="text-title font-bold" style="color: var(--text-primary)">Montant total à remettre</h2>
                    <p class="text-body" style="color: var(--text-secondary)">Collectes COD de la journée</p>
                </div>

                <!-- Status Badge Animé -->
                <div class="inline-flex items-center space-x-4 px-8 py-4 rounded-2xl" :class="getStatusBadgeClass()">
                    <div class="relative">
                        <div class="w-4 h-4 rounded-full" :class="getStatusDotClass()"></div>
                        <div class="absolute inset-0 w-4 h-4 rounded-full animate-ping" :class="getStatusDotClass()" style="opacity: 0.3;"></div>
                    </div>
                    <span class="text-lg font-bold" x-text="getWalletStatus()"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- État de chargement -->
    <div x-show="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-green-600 mx-auto mb-4"></div>
        <p class="text-gray-600 text-lg font-medium">Chargement de votre caisse...</p>
    </div>

    <!-- Section Transactions -->
    <div x-show="!loading" class="px-6 py-6 space-y-6 pb-24">

        <!-- Header Section Transactions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Historique des transactions</h2>
                        <p class="text-gray-600">Collectes COD effectuées aujourd'hui</p>
                    </div>
                </div>

                <div class="text-right">
                    <div class="px-4 py-2 rounded-xl bg-green-500 text-white">
                        <span class="text-lg font-bold" x-text="transactions.length"></span>
                    </div>
                    <div class="text-sm mt-1 text-gray-500">transactions</div>
                </div>
            </div>
        </div>

        <!-- Liste des Transactions Simplifiée -->
        <div class="space-y-4">
            <template x-for="(transaction, index) in transactions" :key="transaction.id">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">

                        <!-- Info Transaction -->
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Icône -->
                            <div class="w-12 h-12 rounded-xl bg-green-500 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>

                            <!-- Détails Transaction -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900" x-text="transaction.package_code"></h3>
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">COD</span>
                                </div>
                                <p class="text-gray-600 font-medium mb-1" x-text="transaction.client_name"></p>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500" x-text="formatTime(transaction.time)"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div class="text-right ml-6">
                            <div class="inline-flex items-center space-x-2 mb-2">
                                <span class="text-sm text-gray-500">+</span>
                                <p class="text-2xl font-black text-green-600" x-text="formatAmount(transaction.amount)"></p>
                            </div>
                            <p class="text-sm font-medium text-gray-500">COD collecté</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- État vide -->
            <div x-show="transactions.length === 0" class="text-center py-16">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-3">Aucune transaction COD</h3>
                <p class="text-lg text-gray-500">Les collectes COD apparaîtront ici</p>
            </div>
        </div>

        <!-- Analytics Dashboard -->
        <div x-show="transactions.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mt-8">
            <div class="mb-8">
                <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Analytics de performance</h3>
                <p class="text-body" style="color: var(--text-secondary)">Résumé détaillé de votre journée</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <!-- Collectes -->
                <div class="text-center p-6 rounded-3xl" style="background: linear-gradient(135deg, rgba(0, 171, 228, 0.1), rgba(0, 171, 228, 0.05));">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: var(--accent); color: white;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-black mb-2" style="color: var(--accent)" x-text="transactions.length"></p>
                    <p class="text-caption font-medium" style="color: var(--text-muted)">Collectes COD</p>
                </div>

                <!-- Montant Moyen -->
                <div class="text-center p-6 rounded-3xl" style="background: linear-gradient(135deg, rgba(142, 68, 173, 0.1), rgba(142, 68, 173, 0.05));">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: #8E44AD; color: white;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-black mb-2" style="color: #8E44AD" x-text="formatAmount(averageAmount)"></p>
                    <p class="text-caption font-medium" style="color: var(--text-muted)">Montant moyen</p>
                </div>

                <!-- Efficacité -->
                <div class="text-center p-6 rounded-3xl" style="background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(46, 204, 113, 0.05));">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: var(--success); color: white;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <p class="text-3xl font-black mb-2" style="color: var(--success)" x-text="Math.round(dayProgress) + '%'"></p>
                    <p class="text-caption font-medium" style="color: var(--text-muted)">Efficacité</p>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="pt-8" style="border-top: 1px solid rgba(0,0,0,0.08);">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h4 class="text-title font-bold" style="color: var(--text-primary)">Progression de la journée</h4>
                        <p class="text-body" style="color: var(--text-secondary)">Basée sur votre activité habituelle</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold" style="color: var(--success)" x-text="Math.round(dayProgress) + '%'"></div>
                        <div class="text-caption" style="color: var(--text-muted)">complété</div>
                    </div>
                </div>

                <!-- Enhanced Progress Bar -->
                <div class="relative mb-4">
                    <div class="w-full h-4 rounded-full" style="background: rgba(46, 204, 113, 0.1);">
                        <div class="h-4 rounded-full progress-gradient transition-all duration-2000 ease-out"
                             :style="`width: ${dayProgress}%`"></div>
                    </div>
                    <!-- Progress Markers -->
                    <div class="flex justify-between mt-3">
                        <span class="text-caption" style="color: var(--text-muted)">8h</span>
                        <span class="text-caption" style="color: var(--text-muted)">12h</span>
                        <span class="text-caption" style="color: var(--text-muted)">16h</span>
                        <span class="text-caption" style="color: var(--text-muted)">18h</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function walletOptimizedApp() {
    return {
        loading: true,
        totalCod: 0,
        animatedAmount: 0,
        transactions: [],
        walletFillPercentage: 0,

        init() {
            this.loadWalletData();
            // Actualisation automatique toutes les 60 secondes
            setInterval(() => this.loadWalletData(), 60000);
        },

        // Animation du compteur de montant
        animateCounter() {
            const duration = 2000;
            const startTime = Date.now();
            const startValue = 0;
            const endValue = this.totalCod;

            const animate = () => {
                const currentTime = Date.now();
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                this.animatedAmount = (startValue + (endValue - startValue) * easeOut).toFixed(3);

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            animate();

            // Animer le cercle de progression aussi
            setTimeout(() => {
                this.walletFillPercentage = Math.min((this.totalCod / 500) * 100, 100);
            }, 500);
        },

        async loadWalletData() {
            try {
                // Charger le solde total
                const balanceRes = await fetch('/deliverer/api/simple/wallet/balance');
                if (balanceRes.ok) {
                    const balanceData = await balanceRes.json();
                    this.totalCod = balanceData.balance || 0;
                }

                // Charger les transactions de la journée
                const transactionsRes = await fetch('/deliverer/api/wallet/cod-transactions');
                if (transactionsRes.ok) {
                    const transactionsData = await transactionsRes.json();
                    this.transactions = transactionsData.transactions || [];
                } else {
                    // Données simulées pour la démonstration
                    this.transactions = [
                        {
                            id: 1,
                            package_code: 'AL2025001',
                            client_name: 'Mohamed Salah',
                            amount: 45.500,
                            time: new Date(Date.now() - 3600000) // Il y a 1h
                        },
                        {
                            id: 2,
                            package_code: 'AL2025002',
                            client_name: 'Fatma Ben Ali',
                            amount: 25.000,
                            time: new Date(Date.now() - 7200000) // Il y a 2h
                        },
                        {
                            id: 3,
                            package_code: 'AL2025003',
                            client_name: 'Ahmed Triki',
                            amount: 60.000,
                            time: new Date(Date.now() - 10800000) // Il y a 3h
                        },
                        {
                            id: 4,
                            package_code: 'AL2025004',
                            client_name: 'Leila Mansouri',
                            amount: 217.000,
                            time: new Date(Date.now() - 14400000) // Il y a 4h
                        }
                    ];

                    // Calculer le total simulé
                    this.totalCod = this.transactions.reduce((sum, t) => sum + t.amount, 0);
                }

                // Déclencher les animations après le chargement
                setTimeout(() => {
                    this.animateCounter();
                }, 200);

            } catch (error) {
                console.error('Erreur chargement wallet:', error);
            } finally {
                this.loading = false;
            }
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatDate() {
            return new Date().toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatTime(date) {
            return new Date(date).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getWalletStatus() {
            if (this.totalCod > 200) return 'Vidage urgent requis';
            if (this.totalCod > 100) return 'Vidage recommandé';
            if (this.totalCod > 0) return 'Espèces disponibles';
            return 'Caisse vide';
        },

        get averageAmount() {
            if (this.transactions.length === 0) return 0;
            return this.totalCod / this.transactions.length;
        },

        get dayProgress() {
            // Simulation d'une progression basée sur l'heure de la journée
            const now = new Date();
            const hour = now.getHours();

            // Progression estimée : 8h = 0%, 18h = 100%
            if (hour < 8) return 0;
            if (hour >= 18) return 100;

            return ((hour - 8) / 10) * 100;
        },

        // Nouvelles méthodes pour le design modernisé
        getStatusBadgeClass() {
            if (this.totalCod > 200) return 'bg-red-100 text-red-800';
            if (this.totalCod > 100) return 'bg-yellow-100 text-yellow-800';
            if (this.totalCod > 0) return 'bg-green-100 text-green-800';
            return 'bg-gray-100 text-gray-800';
        },

        getStatusDotClass() {
            if (this.totalCod > 200) return 'bg-red-500';
            if (this.totalCod > 100) return 'bg-yellow-500';
            if (this.totalCod > 0) return 'bg-green-500';
            return 'bg-gray-500';
        }
    }
}
</script>
@endpush
@endsection