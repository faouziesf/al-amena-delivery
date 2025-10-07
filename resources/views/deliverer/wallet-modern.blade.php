@extends('layouts.deliverer-modern')

@section('title', 'Mon Wallet')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="walletApp()">
    
    <!-- Header avec solde principal -->
    <div class="bg-gradient-to-br from-green-600 to-emerald-600 text-white safe-top">
        <div class="px-6 py-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-xl font-bold">💵 Mon Wallet</h1>
                <button @click="refresh()" 
                        :class="loading ? 'animate-spin' : ''"
                        class="p-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <!-- Solde total -->
            <div class="text-center mb-6">
                <p class="text-green-200 text-sm mb-2">À Remettre au Dépôt</p>
                <h2 class="text-6xl font-bold mb-2" x-text="formatMoney(totalCash)"></h2>
                <p class="text-green-200 text-xs">Dernière maj: <span x-text="lastUpdate"></span></p>
            </div>

            <!-- Stats détaillées -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="text-green-200 text-xs mb-1">COD Collectés</div>
                    <div class="text-2xl font-bold" x-text="formatMoney(collectedCOD)"></div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
                    <div class="text-green-200 text-xs mb-1">Recharges Clients</div>
                    <div class="text-2xl font-bold" x-text="formatMoney(clientRecharges)"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading && transactions.length === 0" class="p-8 text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement...</p>
    </div>

    <!-- Transactions du jour -->
    <div x-show="!loading || transactions.length > 0" class="p-4 space-y-4 pb-24">
        
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Transactions d'Aujourd'hui</h3>
            <span class="text-sm text-gray-500" x-text="transactions.length + ' transaction(s)'"></span>
        </div>

        <!-- Aucune transaction -->
        <div x-show="transactions.length === 0 && !loading" class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-600">Aucune transaction aujourd'hui</p>
        </div>

        <!-- Liste transactions -->
        <template x-for="transaction in transactions" :key="transaction.id">
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-xl" x-text="transaction.icon"></span>
                            <div>
                                <div class="font-semibold text-gray-900" x-text="transaction.title"></div>
                                <div class="text-xs text-gray-500" x-text="transaction.time"></div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600" x-text="transaction.details"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600" x-text="formatMoney(transaction.amount)"></div>
                        <div class="text-xs text-gray-500" x-text="transaction.type"></div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Bouton voir historique -->
        <button x-show="hasMoreHistory" 
                @click="loadHistory()"
                class="w-full py-3 text-indigo-600 font-semibold hover:bg-indigo-50 rounded-xl transition-colors">
            Voir l'historique complet
        </button>
    </div>

    <!-- Actions footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom z-20">
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('deliverer.recharge') }}" 
               class="btn bg-purple-600 text-white hover:bg-purple-700 text-center">
                💳 Recharger Client
            </a>
            <button @click="requestWithdrawal()" 
                    :disabled="totalCash <= 0"
                    :class="totalCash <= 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700'"
                    class="btn text-white">
                📤 Demander Vidage
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function walletApp() {
    return {
        loading: true,
        totalCash: 0,
        collectedCOD: 0,
        clientRecharges: 0,
        transactions: [],
        hasMoreHistory: false,
        lastUpdate: '--:--',

        async init() {
            await this.loadData();
            this.updateLastUpdate();
            
            // Auto-refresh toutes les 2 minutes
            setInterval(() => this.refresh(), 120000);
            setInterval(() => this.updateLastUpdate(), 30000);
        },

        async loadData() {
            try {
                this.loading = true;
                
                // Charger solde wallet
                const balanceData = await apiRequest('/deliverer/api/wallet/balance');
                this.totalCash = balanceData.balance || 0;
                this.collectedCOD = balanceData.available_balance || 0;
                this.clientRecharges = balanceData.pending_amount || 0;

                // Charger transactions du jour
                const packagesData = await apiRequest('/deliverer/api/packages/delivered');
                
                if (Array.isArray(packagesData)) {
                    this.transactions = packagesData
                        .filter(pkg => pkg.cod_amount > 0 && this.isToday(pkg.delivered_at))
                        .map(pkg => ({
                            id: pkg.id,
                            icon: '💵',
                            title: 'COD Collecté',
                            details: `${pkg.recipient_name} - ${pkg.tracking_number}`,
                            amount: pkg.cod_amount,
                            time: this.formatTime(pkg.delivered_at),
                            type: 'Livraison'
                        }));
                }

                this.hasMoreHistory = this.transactions.length >= 20;
            } catch (error) {
                console.error('Erreur chargement wallet:', error);
                showToast('Erreur de chargement', 'error');
            } finally {
                this.loading = false;
            }
        },

        async refresh() {
            vibrate([30]);
            await this.loadData();
            showToast('Données actualisées', 'success', 2000);
        },

        async requestWithdrawal() {
            if (this.totalCash <= 0) {
                showToast('Pas de montant à vider', 'warning');
                return;
            }

            if (!confirm(`Demander un vidage de ${this.formatMoney(this.totalCash)} ?\n\nVous devrez remettre ce montant au dépôt.`)) {
                return;
            }

            try {
                // À implémenter: API pour créer demande de vidage
                showToast('Demande de vidage envoyée', 'success');
                vibrate([100, 50, 100]);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de la demande', 'error');
            }
        },

        loadHistory() {
            // À implémenter: Charger historique complet
            showToast('Fonctionnalité à venir', 'info');
        },

        isToday(dateString) {
            if (!dateString) return false;
            const date = new Date(dateString);
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },

        formatMoney(amount) {
            const num = parseFloat(amount) || 0;
            return num.toFixed(2) + ' TND';
        },

        formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        updateLastUpdate() {
            const now = new Date();
            this.lastUpdate = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    }
}
</script>
@endpush

@endsection
