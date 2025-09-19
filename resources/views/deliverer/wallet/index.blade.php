@extends('layouts.deliverer')

@section('title', 'Mon Wallet')

@section('content')
<div class="p-4 space-y-4" x-data="walletApp()" x-init="init()">
    
    <!-- Header avec solde principal -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">Mon Solde Wallet</p>
                <p class="text-4xl font-bold" x-text="formatAmount(walletBalance)"></p>
                <p class="text-purple-200 text-sm mt-1" x-text="getWalletStatusText()"></p>
            </div>
            <div class="text-right">
                <div x-show="walletBalance > 100" class="bg-yellow-500 bg-opacity-20 px-3 py-1 rounded-full mb-2">
                    <span class="text-yellow-100 text-sm font-medium">📢 Vidage recommandé</span>
                </div>
                <div x-show="walletBalance > 200" class="bg-red-500 bg-opacity-30 px-3 py-1 rounded-full animate-pulse">
                    <span class="text-red-100 text-sm font-bold">🚨 VIDAGE URGENT</span>
                </div>
                <button @click="requestEmptying()" 
                        class="mt-3 bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Demander Vidage
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Wallet -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Collecté Aujourd'hui -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-600 text-sm font-medium">Collecté Aujourd'hui</p>
                    <p class="text-xl font-bold text-gray-900" x-text="formatAmount(stats.collected_today || 0)"></p>
                </div>
            </div>
        </div>

        <!-- COD en Attente -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-orange-600 text-sm font-medium">COD en Attente</p>
                    <p class="text-xl font-bold text-gray-900" x-text="formatAmount(stats.pending_cod || 0)"></p>
                </div>
            </div>
        </div>

        <!-- Transactions ce Mois -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-blue-600 text-sm font-medium">Transactions</p>
                    <p class="text-xl font-bold text-gray-900" x-text="stats.transactions_count || 0"></p>
                </div>
            </div>
        </div>

        <!-- Dernier Vidage -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-purple-600 text-sm font-medium">Dernier Vidage</p>
                    <p class="text-sm font-bold text-gray-900" x-text="lastEmptying ? formatTimeAgo(lastEmptying.date) : 'Jamais'"></p>
                    <p x-show="lastEmptying" class="text-xs text-gray-500" x-text="lastEmptying ? formatAmount(lastEmptying.amount) : ''"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sources du Wallet -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Sources du Wallet</h3>
            <a href="{{ route('deliverer.wallet.sources') }}" 
               class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                Voir Détails →
            </a>
        </div>
        
        <div class="space-y-3">
            <template x-for="source in walletSources.slice(0, 5)" :key="source.id">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                             :class="getSourceColor(source.type)">
                            <span x-text="getSourceIcon(source.type)"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900" x-text="source.description"></p>
                            <p class="text-xs text-gray-500" x-text="formatTimeAgo(source.date)"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-emerald-600" x-text="'+' + formatAmount(source.amount)"></p>
                    </div>
                </div>
            </template>
            
            <div x-show="walletSources.length === 0" class="text-center py-4 text-gray-500">
                <p class="text-sm">Aucune transaction récente</p>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
        
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('deliverer.wallet.history') }}" 
               class="flex items-center justify-center space-x-2 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="font-medium text-gray-700">Historique</span>
            </a>
            
            <button @click="requestEmptying()" 
                    class="flex items-center justify-center space-x-2 p-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="font-medium">Demander Vidage</span>
            </button>
            
            <a href="{{ route('deliverer.client-topup.index') }}" 
               class="flex items-center justify-center space-x-2 p-3 border border-yellow-500 text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.467-.22-2.121-.659-1.172-.879-1.172-2.303 0-3.182s3.07-.879 4.242 0L15 9m-3 9v1"/>
                </svg>
                <span class="font-medium">Recharge Client</span>
            </a>
            
            <button @click="exportTransactions()" 
                    class="flex items-center justify-center space-x-2 p-3 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-medium">Export CSV</span>
            </button>
        </div>
    </div>

    <!-- Transactions Récentes -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transactions Récentes</h3>
            <a href="{{ route('deliverer.wallet.history') }}" 
               class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                Voir Tout →
            </a>
        </div>
        
        <div class="space-y-3">
            <template x-for="transaction in recentTransactions" :key="transaction.id">
                <div class="flex items-center justify-between p-3 rounded-lg"
                     :class="transaction.is_credit ? 'bg-emerald-50' : 'bg-red-50'">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                             :class="transaction.is_credit ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      :d="transaction.is_credit ? 'M12 6v6m0 0v6m0-6h6m-6 0H6' : 'M20 12H4'"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900" x-text="transaction.description"></p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <span x-text="formatTimeAgo(transaction.date)"></span>
                                <span x-show="transaction.package_code" 
                                      class="px-1 py-0.5 bg-gray-200 rounded text-gray-700"
                                      x-text="transaction.package_code"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold"
                           :class="transaction.is_credit ? 'text-emerald-600' : 'text-red-600'"
                           x-text="(transaction.is_credit ? '+' : '-') + formatAmount(Math.abs(transaction.amount))"></p>
                        <p class="text-xs text-gray-500" x-text="transaction.status_display"></p>
                    </div>
                </div>
            </template>
            
            <div x-show="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">Aucune transaction récente</p>
            </div>
        </div>
    </div>

    <!-- Informations importantes -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <div class="flex items-start space-x-3">
            <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h4 class="font-semibold text-yellow-800">💡 Rappel Important</h4>
                <p class="text-yellow-700 text-sm mt-1">
                    Votre wallet représente l'argent physique que vous devez avoir sur vous. 
                    Assurez-vous que le montant affiché correspond exactement aux espèces en votre possession.
                </p>
                <ul class="text-yellow-600 text-xs mt-2 space-y-1">
                    <li>• COD collectés = Argent reçu des clients</li>
                    <li>• Recharges clients = Argent reçu pour recharger leurs comptes</li>
                    <li>• Vous êtes responsable de toute différence</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<!-- Modal de demande de vidage -->
<div x-show="emptyingModal.show" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Demander Vidage Wallet</h3>
            <button @click="closeEmptyingModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <!-- Solde actuel -->
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-purple-800">Solde à vider</span>
                </div>
                <p class="text-2xl font-bold text-purple-900" x-text="formatAmount(walletBalance)"></p>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                <textarea x-model="emptyingModal.notes" rows="3"
                          placeholder="Informations particulières pour le commercial..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>

            <!-- Urgence -->
            <label class="flex items-center">
                <input type="checkbox" x-model="emptyingModal.urgent"
                       class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Demande urgente (montant élevé)</span>
            </label>

            <!-- Warning -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <div class="flex items-start space-x-2">
                    <svg class="w-4 h-4 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-yellow-700 text-xs">
                        Un commercial vous contactera pour organiser la remise des espèces. 
                        Assurez-vous d'avoir le montant exact en votre possession.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3 pt-4">
                <button @click="closeEmptyingModal()" 
                        class="flex-1 py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Annuler
                </button>
                <button @click="submitEmptyingRequest()" :disabled="emptyingModal.processing"
                        class="flex-1 py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50">
                    <span x-show="!emptyingModal.processing">Envoyer Demande</span>
                    <span x-show="emptyingModal.processing" class="flex items-center justify-center space-x-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <span>Envoi...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function walletApp() {
    return {
        walletBalance: @json($walletData['balance'] ?? 0),
        stats: @json($walletStats ?? []),
        walletSources: @json($walletSources ?? []),
        recentTransactions: @json($recentTransactions ?? []),
        lastEmptying: @json($lastEmptying ?? null),
        
        emptyingModal: {
            show: false,
            notes: '',
            urgent: false,
            processing: false
        },

        init() {
            this.loadWalletData();
            
            // Auto refresh every 2 minutes
            setInterval(() => {
                this.loadWalletData();
            }, 120000);
        },

        async loadWalletData() {
            try {
                // Load wallet balance
                const balanceResponse = await fetch('/deliverer/api/wallet/balance');
                if (balanceResponse.ok) {
                    const data = await balanceResponse.json();
                    this.walletBalance = data.balance;
                }

                // Load recent transactions
                const transactionsResponse = await fetch('/deliverer/api/wallet/recent-transactions');
                if (transactionsResponse.ok) {
                    const data = await transactionsResponse.json();
                    this.recentTransactions = data.transactions || [];
                }
            } catch (error) {
                console.error('Error loading wallet data:', error);
            }
        },

        requestEmptying() {
            if (this.walletBalance <= 0) {
                this.showToast('Votre wallet est vide', 'error');
                return;
            }
            this.emptyingModal.show = true;
            this.emptyingModal.urgent = this.walletBalance > 200;
        },

        closeEmptyingModal() {
            this.emptyingModal = {
                show: false,
                notes: '',
                urgent: false,
                processing: false
            };
        },

        async submitEmptyingRequest() {
            this.emptyingModal.processing = true;
            
            try {
                const response = await fetch('/deliverer/wallet/request-emptying', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notes: this.emptyingModal.notes,
                        urgent: this.emptyingModal.urgent
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.closeEmptyingModal();
                } else {
                    this.showToast(data.message, 'error');
                }

            } catch (error) {
                console.error('Error requesting emptying:', error);
                this.showToast('Erreur lors de la demande', 'error');
            }
            
            this.emptyingModal.processing = false;
        },

        exportTransactions() {
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - 1);
            const endDate = new Date();
            
            const params = new URLSearchParams({
                start_date: startDate.toISOString().split('T')[0],
                end_date: endDate.toISOString().split('T')[0]
            });
            
            window.open(`/deliverer/wallet/export-transactions?${params}`, '_blank');
        },

        getWalletStatusText() {
            if (this.walletBalance > 200) {
                return '🚨 Vidage urgent requis - Montant très élevé';
            } else if (this.walletBalance > 100) {
                return '⚠️ Vidage recommandé - Montant élevé';
            } else if (this.walletBalance > 0) {
                return '✅ Wallet normal - Espèces disponibles';
            }
            return '💸 Wallet vide - Aucune espèce';
        },

        getSourceColor(type) {
            const colors = {
                'COD': 'bg-emerald-100 text-emerald-600',
                'CLIENT_FUND': 'bg-blue-100 text-blue-600',
                'COMMISSION': 'bg-purple-100 text-purple-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },

        getSourceIcon(type) {
            const icons = {
                'COD': '💰',
                'CLIENT_FUND': '🔄',
                'COMMISSION': '💼'
            };
            return icons[type] || '📊';
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) {
                return `Il y a ${diffInMinutes}min`;
            } else if (diffInMinutes < 1440) {
                return `Il y a ${Math.floor(diffInMinutes / 60)}h`;
            } else {
                return date.toLocaleDateString('fr-FR');
            }
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
            }, 3000);
        }
    }
}
</script>
@endpush