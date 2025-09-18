@extends('layouts.deliverer')

@section('title', 'Mon Wallet')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="walletApp()">
    
    <!-- Header Section -->
    <div class="bg-gradient-to-br from-purple-600 to-indigo-700 text-white">
        <div class="px-4 py-6">
            <!-- Wallet Balance -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold mb-2">Mon Wallet</h1>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-3xl p-6">
                    <div class="mb-2">
                        <span class="text-purple-200 text-sm font-medium">💰 Caisse Physique</span>
                    </div>
                    <div class="text-4xl font-bold mb-2" x-text="formatAmount(walletBalance)"></div>
                    <div class="flex items-center justify-center space-x-4 text-sm text-purple-200">
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span x-text="`${activeSources} sources actives`"></span>
                        </div>
                        <span>•</span>
                        <span x-text="lastUpdate"></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3">
                <button @click="requestEmptying()" 
                        :disabled="walletBalance <= 0"
                        class="bg-white bg-opacity-10 hover:bg-opacity-20 backdrop-blur-sm p-4 rounded-xl transition-colors disabled:opacity-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-sm">Demander Vidange</p>
                            <p class="text-xs text-purple-200">Remettre au commercial</p>
                        </div>
                    </div>
                </button>

                <a href="{{ route('deliverer.wallet.history') }}" 
                   class="bg-white bg-opacity-10 hover:bg-opacity-20 backdrop-blur-sm p-4 rounded-xl transition-colors block">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-sm">Historique</p>
                            <p class="text-xs text-purple-200">Toutes transactions</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Sources Breakdown -->
    <div class="px-4 -mt-4 relative z-10 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">💰 Composition de ma caisse</h2>
                <span class="text-sm text-gray-500">Aujourd'hui</span>
            </div>
            
            <div class="space-y-4">
                <!-- COD Collecté -->
                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span class="text-lg">📦</span>
                        </div>
                        <div>
                            <p class="font-semibold text-emerald-800">COD Collecté</p>
                            <p class="text-sm text-emerald-600" x-text="`${todayStats.cod_count || 0} colis livrés`"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-700" x-text="formatAmount(todayStats.cod_collected || 0)"></p>
                        <p class="text-xs text-emerald-600">En espèces</p>
                    </div>
                </div>

                <!-- Recharges Clients -->
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-lg">💳</span>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-800">Recharges Clients</p>
                            <p class="text-sm text-blue-600" x-text="`${todayStats.topups_count || 0} recharges`"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-blue-700" x-text="formatAmount(todayStats.topups_amount || 0)"></p>
                        <p class="text-xs text-blue-600">Double wallet</p>
                    </div>
                </div>

                <!-- Paiements Livrés (Sortants) -->
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl border border-red-200" x-show="todayStats.payments_delivered > 0">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-lg">💸</span>
                        </div>
                        <div>
                            <p class="font-semibold text-red-800">Paiements Livrés</p>
                            <p class="text-sm text-red-600" x-text="`${todayStats.payments_count || 0} retraits clients`"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-700" x-text="'-' + formatAmount(todayStats.payments_delivered || 0)"></p>
                        <p class="text-xs text-red-600">Espèces remises</p>
                    </div>
                </div>
            </div>

            <!-- Total Today -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-gray-700">Net aujourd'hui:</p>
                    <p class="text-xl font-bold text-purple-600" x-text="formatAmount(getTodayNet())"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="px-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">📊 Mes Performances</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm text-center">
                <p class="text-2xl font-bold text-green-600" x-text="formatAmount(stats.week_earned || 0)"></p>
                <p class="text-xs text-gray-500">Cette semaine</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm text-center">
                <p class="text-2xl font-bold text-blue-600" x-text="formatAmount(stats.month_earned || 0)"></p>
                <p class="text-xs text-gray-500">Ce mois</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm text-center">
                <p class="text-2xl font-bold text-purple-600" x-text="stats.total_transactions || 0"></p>
                <p class="text-xs text-gray-500">Total transactions</p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="px-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">💱 Activité Récente</h2>
            <a href="{{ route('deliverer.wallet.history') }}" class="text-purple-600 text-sm font-medium hover:text-purple-800">
                Voir tout →
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-100">
            <template x-for="transaction in recentTransactions" :key="transaction.id">
                <div class="p-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                             :class="getTransactionColor(transaction.source_details.type)">
                            <span x-text="transaction.source_details.icon"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="transaction.description"></p>
                            <div class="flex items-center space-x-2 mt-1">
                                <p class="text-xs text-gray-500" x-text="formatDate(transaction.created_at)"></p>
                                <template x-if="transaction.source_details.package_code">
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded" x-text="transaction.source_details.package_code"></span>
                                </template>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold" :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'" 
                               x-text="transaction.formatted_amount"></p>
                            <p class="text-xs text-gray-500" x-text="formatAmount(transaction.wallet_balance_after)"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-show="recentTransactions.length === 0" class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                </svg>
                <p class="text-sm">Aucune transaction récente</p>
            </div>
        </div>
    </div>

    <!-- Recent Emptyings -->
    <div class="px-4 mb-6" x-show="recentEmptyings.length > 0">
        <h2 class="text-lg font-bold text-gray-900 mb-4">🏦 Dernières Vidanges</h2>
        
        <div class="bg-white rounded-2xl shadow-sm divide-y divide-gray-100">
            <template x-for="emptying in recentEmptyings" :key="emptying.id">
                <div class="p-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-lg">🏦</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Vidange wallet</p>
                            <p class="text-xs text-gray-500" x-text="`Remis à ${emptying.commercial.name} • ${formatDate(emptying.emptying_date)}`"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-purple-600" x-text="formatAmount(emptying.wallet_amount)"></p>
                            <template x-if="emptying.discrepancy_amount != 0">
                                <p class="text-xs" :class="emptying.discrepancy_amount > 0 ? 'text-orange-600' : 'text-red-600'" 
                                   x-text="(emptying.discrepancy_amount > 0 ? '+' : '') + formatAmount(emptying.discrepancy_amount)"></p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Emptying Request Modal -->
    <div x-show="showEmptyingModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Demander Vidange Wallet</h3>
                <button @click="showEmptyingModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Current Balance -->
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-200">
                    <p class="text-sm font-bold text-purple-800 mb-2">MONTANT À VIDER:</p>
                    <p class="text-3xl font-bold text-purple-900" x-text="formatAmount(walletBalance)"></p>
                    <p class="text-xs text-purple-600 mt-1">Caisse physique actuelle</p>
                </div>

                <!-- Sources Breakdown for Commercial -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-sm font-medium text-gray-700 mb-2">Composition actuelle:</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">📦 COD aujourd'hui:</span>
                            <span class="font-medium" x-text="formatAmount(todayStats.cod_collected || 0)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">💳 Recharges clients:</span>
                            <span class="font-medium" x-text="formatAmount(todayStats.topups_amount || 0)"></span>
                        </div>
                        <div class="flex justify-between" x-show="todayStats.payments_delivered > 0">
                            <span class="text-gray-600">💸 Paiements livrés:</span>
                            <span class="font-medium text-red-600" x-text="'-' + formatAmount(todayStats.payments_delivered || 0)"></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes pour le commercial (optionnel)</label>
                    <textarea x-model="emptyingNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              rows="3"
                              placeholder="Lieu de rendez-vous, heure préférée..."></textarea>
                </div>
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-bold text-amber-800">IMPORTANT:</p>
                            <p class="text-xs text-amber-700">Un commercial vous contactera pour organiser la vidange. Vous devez remettre EXACTEMENT le montant affiché en espèces.</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmEmptyingRequest()" 
                            :disabled="processingEmptying || walletBalance <= 0"
                            class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-50">
                        <span x-show="!processingEmptying">Envoyer Demande</span>
                        <span x-show="processingEmptying" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Envoi...</span>
                        </span>
                    </button>
                    <button @click="showEmptyingModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Spacing -->
    <div class="h-20"></div>
</div>

<script>
function walletApp() {
    return {
        walletBalance: {{ $wallet->balance ?? 0 }},
        activeSources: 0,
        lastUpdate: 'À l\'instant',
        showEmptyingModal: false,
        emptyingNotes: '',
        processingEmptying: false,
        
        todayStats: {
            cod_collected: 0,
            cod_count: 0,
            topups_amount: 0,
            topups_count: 0,
            payments_delivered: 0,
            payments_count: 0
        },
        
        stats: {
            week_earned: 0,
            month_earned: 0,
            total_transactions: 0
        },
        
        recentTransactions: @json($recentTransactions ?? []),
        recentEmptyings: @json($recentEmptyings ?? []),

        init() {
            this.loadWalletData();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.loadWalletData();
            }, 30000);
        },

        async loadWalletData() {
            try {
                // Load balance
                const balanceResponse = await fetch('/deliverer/api/wallet/balance');
                if (balanceResponse.ok) {
                    const data = await balanceResponse.json();
                    this.walletBalance = data.balance;
                    this.activeSources = data.sources_count;
                    this.lastUpdate = 'Mis à jour';
                }

                // Load quick stats
                const statsResponse = await fetch('/deliverer/api/wallet/quick-stats');
                if (statsResponse.ok) {
                    const data = await statsResponse.json();
                    this.todayStats = {
                        cod_collected: data.today_earned || 0,
                        cod_count: 0, // TODO: Implement
                        topups_amount: 0, // TODO: Implement
                        topups_count: 0,
                        payments_delivered: data.total_payments_delivered || 0,
                        payments_count: 0
                    };
                    
                    this.stats = {
                        week_earned: data.week_earned || 0,
                        month_earned: data.month_earned || 0,
                        total_transactions: this.recentTransactions.length
                    };
                }
            } catch (error) {
                console.error('Erreur chargement wallet:', error);
            }
        },

        requestEmptying() {
            if (this.walletBalance <= 0) {
                this.showToast('Aucun montant à vider dans votre wallet', 'error');
                return;
            }
            this.showEmptyingModal = true;
        },

        async confirmEmptyingRequest() {
            this.processingEmptying = true;
            
            try {
                const response = await fetch('/deliverer/wallet/request-emptying', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notes: this.emptyingNotes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showEmptyingModal = false;
                    this.emptyingNotes = '';
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur demande vidange:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingEmptying = false;
        },

        getTodayNet() {
            return (this.todayStats.cod_collected + this.todayStats.topups_amount) - this.todayStats.payments_delivered;
        },

        getTransactionColor(type) {
            const colors = {
                'COD_COLLECTION': 'bg-emerald-100 text-emerald-600',
                'PAYMENT_DELIVERY': 'bg-red-100 text-red-600',
                'CLIENT_TOPUP': 'bg-blue-100 text-blue-600',
                'WALLET_EMPTYING': 'bg-purple-100 text-purple-600',
                'DELIVERY_COMMISSION': 'bg-green-100 text-green-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffMinutes < 1) return 'À l\'instant';
            if (diffMinutes < 60) return `Il y a ${diffMinutes}min`;
            
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `Il y a ${diffHours}h`;
            
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }
}
</script>
@endsection