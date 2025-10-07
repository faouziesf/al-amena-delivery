@extends('layouts.deliverer')

@section('title', 'Ma Caisse')

@section('content')
<div x-data="walletProductionApp()" x-init="init()" class="min-h-screen bg-gray-50">

    <!-- Header Caisse -->
    <div class="bg-gradient-to-br from-emerald-600 to-green-700 text-white px-6 py-8 safe-top">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('deliverer.run.sheet') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Ma Caisse</h1>
            <button @click="refreshData()" class="text-white">
                <svg class="w-6 h-6" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>

        <!-- Date -->
        <p class="text-emerald-100 text-center mb-6" x-text="formatDate()"></p>

        <!-- Montant Principal -->
        <div class="text-center">
            <div class="text-6xl font-black mb-2">
                <span x-text="formatAmount(totalCOD)"></span>
                <span class="text-3xl">DT</span>
            </div>
            <p class="text-emerald-200 text-lg">Espèces à remettre</p>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-3 gap-4 mt-8">
            <div class="text-center">
                <div class="text-2xl font-bold" x-text="deliveredCount"></div>
                <div class="text-emerald-200 text-sm">Livrés</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold" x-text="codCount"></div>
                <div class="text-emerald-200 text-sm">COD</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold" x-text="formatAmount(averageCOD)"></div>
                <div class="text-emerald-200 text-sm">Moyen</div>
            </div>
        </div>
    </div>

    <!-- État de chargement -->
    <div x-show="loading && transactions.length === 0" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-emerald-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement...</p>
    </div>

    <!-- Alerte si montant important -->
    <div x-show="totalCOD > 200 && !loading" class="mx-6 mt-6">
        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-orange-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-orange-800">Vidage de caisse recommandé</p>
                    <p class="text-sm text-orange-700">Vous détenez plus de 200 DT en espèces</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Transactions COD -->
    <div x-show="!loading" class="px-6 py-6 space-y-4 pb-24">

        <!-- Header Liste -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Transactions du Jour</h2>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm font-semibold" x-text="codCount + ' COD'"></span>
        </div>

        <!-- Message si aucune transaction -->
        <div x-show="transactions.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-500 font-medium">Aucune collecte COD aujourd'hui</p>
            <p class="text-gray-400 text-sm mt-2">Les espèces collectées apparaîtront ici</p>
        </div>

        <!-- Liste Transactions -->
        <template x-for="(transaction, index) in transactions" :key="transaction.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <!-- Code Colis -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 font-mono text-sm" x-text="transaction.package_code"></p>
                            <p class="text-xs text-gray-500" x-text="transaction.client_name || 'Client'"></p>
                        </div>
                    </div>

                    <!-- Montant -->
                    <div class="text-right">
                        <p class="text-2xl font-bold text-emerald-600" x-text="formatAmount(transaction.amount)"></p>
                        <p class="text-xs text-gray-500" x-text="formatTime(transaction.delivered_at)"></p>
                    </div>
                </div>

                <!-- Adresse (tronquée) -->
                <div x-show="transaction.delivery_address" class="text-xs text-gray-500 flex items-center space-x-2 mt-2 pt-2 border-t border-gray-100">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="line-clamp-1" x-text="transaction.delivery_address"></span>
                </div>
            </div>
        </template>

        <!-- Bouton Charger Plus -->
        <button x-show="hasMore && !loadingMore" @click="loadMore()" 
                class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
            Charger Plus
        </button>

        <div x-show="loadingMore" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom">
        <button @click="requestWithdrawal()" :disabled="totalCOD === 0"
                :class="totalCOD === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                class="w-full py-4 rounded-xl font-bold text-lg transition-colors">
            <span x-show="totalCOD > 0">Demander Vidage de Caisse</span>
            <span x-show="totalCOD === 0">Aucun espèce à remettre</span>
        </button>
        <p class="text-center text-sm text-gray-500 mt-2">
            Dernière mise à jour: <span x-text="lastUpdate"></span>
        </p>
    </div>

</div>

@push('scripts')
<script>
function walletProductionApp() {
    return {
        loading: true,
        loadingMore: false,
        transactions: [],
        totalCOD: 0,
        deliveredCount: 0,
        codCount: 0,
        averageCOD: 0,
        hasMore: false,
        page: 1,
        lastUpdate: '--:--',

        async init() {
            await this.loadWalletData();
            this.updateLastUpdate();
            
            // Auto-refresh toutes les 2 minutes
            setInterval(() => this.refreshData(), 120000);
            setInterval(() => this.updateLastUpdate(), 30000);
        },

        async loadWalletData() {
            this.loading = true;
            try {
                // Charger les vraies données depuis l'API
                const response = await fetch('/deliverer/api/wallet/cod-today');
                
                if (!response.ok) {
                    throw new Error('Erreur de chargement');
                }

                const data = await response.json();
                
                // Données réelles de la base de données
                this.totalCOD = parseFloat(data.total_cod || 0);
                this.deliveredCount = parseInt(data.delivered_count || 0);
                this.codCount = parseInt(data.cod_count || 0);
                this.averageCOD = this.codCount > 0 ? (this.totalCOD / this.codCount) : 0;
                this.transactions = data.transactions || [];
                this.hasMore = data.has_more || false;

                if (this.transactions.length === 0) {
                    showToast('Aucune collecte COD aujourd\'hui', 'info', 3000);
                }

            } catch (error) {
                console.error('Erreur chargement wallet:', error);
                showToast('Erreur de chargement', 'error');
                
                // En cas d'erreur, afficher vide plutôt que des fausses données
                this.totalCOD = 0;
                this.deliveredCount = 0;
                this.codCount = 0;
                this.transactions = [];
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMore) return;
            
            this.loadingMore = true;
            this.page++;

            try {
                const response = await fetch(`/deliverer/api/wallet/cod-today?page=${this.page}`);
                const data = await response.json();
                
                this.transactions = [...this.transactions, ...(data.transactions || [])];
                this.hasMore = data.has_more || false;

            } catch (error) {
                console.error('Erreur chargement plus:', error);
                showToast('Erreur de chargement', 'error');
            } finally {
                this.loadingMore = false;
            }
        },

        async refreshData() {
            this.page = 1;
            await this.loadWalletData();
            showToast('Données actualisées', 'success', 2000);
            haptic('light');
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3);
        },

        formatDate() {
            return new Date().toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatTime(dateString) {
            if (!dateString) return '--:--';
            const date = new Date(dateString);
            return date.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        updateLastUpdate() {
            this.lastUpdate = new Date().toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        requestWithdrawal() {
            if (this.totalCOD === 0) {
                showToast('Aucun espèce à remettre', 'warning');
                return;
            }

            if (confirm(`Demander un vidage de caisse de ${this.formatAmount(this.totalCOD)} DT ?`)) {
                window.location.href = '{{ route("deliverer.withdrawals.create") }}';
            }
        }
    }
}
</script>
@endpush
@endsection
