@props([
    'wallet' => null,
    'showActions' => true,
    'showHistory' => false,
    'compact' => false
])

@php
    $balance = $wallet?->balance ?? 0;
    $pendingAmount = $wallet?->pending_amount ?? 0;
    $frozenAmount = $wallet?->frozen_amount ?? 0;
    $totalAvailable = $balance - $frozenAmount;
@endphp

<div {{ $attributes->merge(['class' => 'bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl text-white']) }}
     x-data="walletSummary({
        initialBalance: {{ $balance }},
        initialPending: {{ $pendingAmount }},
        initialFrozen: {{ $frozenAmount }},
        showActions: {{ $showActions ? 'true' : 'false' }},
        showHistory: {{ $showHistory ? 'true' : 'false' }},
        compact: {{ $compact ? 'true' : 'false' }}
     })"
     x-init="loadWalletData()">

    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold">{{ $compact ? 'Portefeuille' : 'Mon Portefeuille' }}</h3>
                <p class="text-blue-100 text-sm">
                    @if($compact)
                        Solde disponible
                    @else
                        Gérez vos gains et transactions
                    @endif
                </p>
            </div>

            @if(!$compact)
            <div class="flex items-center space-x-2">
                <!-- Refresh Button -->
                <button @click="refreshWallet()"
                        :disabled="loading"
                        class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors disabled:opacity-50">
                    <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                <!-- Settings -->
                <button @click="$dispatch('wallet-settings')"
                        class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
            @endif
        </div>

        <!-- Balance Display -->
        <div class="space-y-4">
            <!-- Main Balance -->
            <div class="text-center">
                <p class="text-blue-100 text-sm mb-1">Solde disponible</p>
                <div class="text-4xl font-bold mb-2" x-text="formatMoney(balance - frozenAmount)">
                    {{ number_format($totalAvailable, 3) }} DT
                </div>

                @if(!$compact && ($pendingAmount > 0 || $frozenAmount > 0))
                <!-- Additional Balances -->
                <div class="flex justify-center space-x-6 text-sm">
                    @if($pendingAmount > 0)
                    <div class="text-center">
                        <p class="text-blue-200">En attente</p>
                        <p class="font-semibold" x-text="formatMoney(pendingAmount)">{{ number_format($pendingAmount, 3) }} DT</p>
                    </div>
                    @endif

                    @if($frozenAmount > 0)
                    <div class="text-center">
                        <p class="text-blue-200">Gelé</p>
                        <p class="font-semibold" x-text="formatMoney(frozenAmount)">{{ number_format($frozenAmount, 3) }} DT</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            @if(!$compact)
            <!-- Balance Breakdown -->
            <div class="grid grid-cols-3 gap-4">
                <!-- Total Balance -->
                <div class="bg-white/10 rounded-lg p-3 text-center">
                    <div class="text-xs text-blue-200 mb-1">Solde total</div>
                    <div class="font-bold" x-text="formatMoney(balance)">{{ number_format($balance, 3) }} DT</div>
                </div>

                <!-- Today's Earnings -->
                <div class="bg-white/10 rounded-lg p-3 text-center">
                    <div class="text-xs text-blue-200 mb-1">Gains du jour</div>
                    <div class="font-bold text-emerald-300" x-text="formatMoney(todayEarnings)">+ 0.000 DT</div>
                </div>

                <!-- This Week -->
                <div class="bg-white/10 rounded-lg p-3 text-center">
                    <div class="text-xs text-blue-200 mb-1">Cette semaine</div>
                    <div class="font-bold text-emerald-300" x-text="formatMoney(weekEarnings)">+ 0.000 DT</div>
                </div>
            </div>
            @endif

            <!-- Balance Status -->
            <div class="flex items-center justify-center space-x-2 text-sm" x-show="getBalanceStatus()">
                <div class="w-2 h-2 rounded-full" :class="getBalanceStatusColor()"></div>
                <span x-text="getBalanceStatus()"></span>
            </div>
        </div>

        @if($showActions && !$compact)
        <!-- Quick Actions -->
        <div class="mt-6 space-y-3">
            <!-- Primary Actions -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('deliverer.wallet.emptying.create') }}"
                   class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white py-3 px-4 rounded-xl font-semibold text-center transition-all flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Retirer</span>
                </a>

                <a href="{{ route('deliverer.wallet.history') }}"
                   class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white py-3 px-4 rounded-xl font-semibold text-center transition-all flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Historique</span>
                </a>
            </div>

            <!-- Secondary Actions -->
            <div class="flex space-x-2">
                <button @click="$dispatch('generate-report')"
                        class="flex-1 bg-white/10 hover:bg-white/20 border border-white/20 text-white py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Rapport</span>
                </button>

                <button @click="exportWalletData()"
                        class="flex-1 bg-white/10 hover:bg-white/20 border border-white/20 text-white py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Exporter</span>
                </button>
            </div>
        </div>
        @endif

        @if($showHistory && !$compact)
        <!-- Recent Transactions -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold">Transactions récentes</h4>
                <a href="{{ route('deliverer.wallet.history') }}" class="text-sm text-blue-200 hover:text-white">Voir tout</a>
            </div>

            <div class="space-y-2 max-h-32 overflow-y-auto">
                <template x-for="transaction in recentTransactions" :key="transaction.id">
                    <div class="flex items-center justify-between bg-white/10 rounded-lg p-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="transaction.type === 'credit' ? 'bg-emerald-500' : 'bg-red-500'">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          :d="transaction.type === 'credit' ? 'M12 6v6m0 0v6m0-6h6m-6 0H6' : 'M18 12H6'"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium" x-text="transaction.description"></p>
                                <p class="text-xs text-blue-200" x-text="formatDate(transaction.created_at)"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold"
                               :class="transaction.type === 'credit' ? 'text-emerald-300' : 'text-red-300'"
                               x-text="(transaction.type === 'credit' ? '+' : '-') + formatMoney(transaction.amount)">
                            </p>
                        </div>
                    </div>
                </template>

                <div x-show="recentTransactions.length === 0" class="text-center py-4 text-blue-200 text-sm">
                    Aucune transaction récente
                </div>
            </div>
        </div>
        @endif

        @if($compact && $showActions)
        <!-- Compact Actions -->
        <div class="mt-4 flex space-x-2">
            <a href="{{ route('deliverer.wallet.index') }}"
               class="flex-1 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white py-2 px-3 rounded-lg text-sm font-medium text-center transition-all">
                Gérer
            </a>
            <a href="{{ route('deliverer.wallet.emptying.create') }}"
               class="flex-1 bg-white text-purple-600 py-2 px-3 rounded-lg text-sm font-medium text-center hover:bg-gray-100 transition-all">
                Retirer
            </a>
        </div>
        @endif

        <!-- Last Update -->
        <div class="mt-4 text-center text-xs text-blue-200" x-show="lastUpdate">
            Mis à jour: <span x-text="formatLastUpdate()"></span>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-sm rounded-xl flex items-center justify-center">
        <div class="bg-white rounded-lg p-4 flex items-center space-x-3">
            <div class="w-5 h-5 border-2 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-gray-700 text-sm">Mise à jour...</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
function walletSummary(options = {}) {
    return {
        // Configuration
        showActions: options.showActions !== false,
        showHistory: options.showHistory || false,
        compact: options.compact || false,

        // State
        loading: false,
        balance: options.initialBalance || 0,
        pendingAmount: options.initialPending || 0,
        frozenAmount: options.initialFrozen || 0,
        todayEarnings: 0,
        weekEarnings: 0,
        recentTransactions: [],
        lastUpdate: null,

        async init() {
            this.lastUpdate = new Date();

            if (this.showHistory) {
                await this.loadRecentTransactions();
            }

            // Auto-refresh every 2 minutes
            setInterval(() => {
                this.refreshWallet();
            }, 120000);
        },

        async loadWalletData() {
            try {
                const response = await fetch('/deliverer/api/wallet/summary', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error('Network error');

                const data = await response.json();

                if (data.success) {
                    this.balance = data.wallet.balance || 0;
                    this.pendingAmount = data.wallet.pending_amount || 0;
                    this.frozenAmount = data.wallet.frozen_amount || 0;
                    this.todayEarnings = data.earnings.today || 0;
                    this.weekEarnings = data.earnings.week || 0;
                    this.lastUpdate = new Date();
                }

            } catch (error) {
                console.error('Erreur chargement wallet:', error);
            }
        },

        async loadRecentTransactions() {
            try {
                const response = await fetch('/deliverer/api/wallet/transactions?limit=5', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error('Network error');

                const data = await response.json();

                if (data.success) {
                    this.recentTransactions = data.transactions || [];
                }

            } catch (error) {
                console.error('Erreur chargement transactions:', error);
            }
        },

        async refreshWallet() {
            this.loading = true;

            try {
                await this.loadWalletData();

                if (this.showHistory) {
                    await this.loadRecentTransactions();
                }

            } catch (error) {
                console.error('Erreur refresh wallet:', error);
            } finally {
                this.loading = false;
            }
        },

        getBalanceStatus() {
            const available = this.balance - this.frozenAmount;

            if (available <= 0) {
                return 'Solde insuffisant';
            } else if (available < 50) {
                return 'Solde faible';
            } else if (available > 500) {
                return 'Solde élevé - Envisagez un retrait';
            }

            return '';
        },

        getBalanceStatusColor() {
            const available = this.balance - this.frozenAmount;

            if (available <= 0) {
                return 'bg-red-400';
            } else if (available < 50) {
                return 'bg-yellow-400';
            } else if (available > 500) {
                return 'bg-blue-400';
            }

            return 'bg-emerald-400';
        },

        async exportWalletData() {
            try {
                const response = await fetch('/deliverer/wallet/export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        format: 'csv',
                        period: 'month'
                    })
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `wallet_export_${new Date().toISOString().split('T')[0]}.csv`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                }

            } catch (error) {
                console.error('Erreur export:', error);
            }
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('fr-TN', {
                style: 'decimal',
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }).format(amount);
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatLastUpdate() {
            if (!this.lastUpdate) return '';

            return this.lastUpdate.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush