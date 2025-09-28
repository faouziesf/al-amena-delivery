@extends('layouts.deliverer')

@section('title', 'Mon Wallet')

@section('content')
<div class="p-4 sm:p-6 space-y-6 content-mobile-spacing" x-data="walletApp()" x-init="init()">

    <!-- Header Balance Card -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-200 text-sm">Mon Solde Actuel</p>
                <p class="text-4xl font-bold" x-text="formatAmount(walletBalance)"></p>
            </div>
            <div class="text-right">
                <button @click="requestEmptying()" class="bg-white/20 hover:bg-white/30 font-semibold px-4 py-2 rounded-full transition-colors">
                    Demander un Vidage
                </button>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-white/20 text-sm text-purple-200" x-text="getWalletStatusText()"></div>
    </div>

    <!-- Stats Wallet -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100">
            <p class="text-sm text-gray-500">Collecté Aujourd'hui</p>
            <p class="text-2xl font-bold text-purple-800" x-text="formatAmount(stats.collected_today || 0)"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100">
            <p class="text-sm text-gray-500">COD en Attente</p>
            <p class="text-2xl font-bold text-purple-800" x-text="formatAmount(stats.pending_cod || 0)"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100">
            <p class="text-sm text-gray-500">Transactions (Mois)</p>
            <p class="text-2xl font-bold text-purple-800" x-text="stats.transactions_count || 0"></p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100">
            <p class="text-sm text-gray-500">Dernier Vidage</p>
            <p class="text-lg font-bold text-purple-800" x-text="lastEmptying ? formatTimeAgo(lastEmptying.date) : 'Jamais'"></p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('deliverer.wallet.history') }}" class="btn-secondary text-center">Historique</a>
            <button @click="requestEmptying()" class="btn-secondary">Demander Vidage</button>
            <a href="{{ route('deliverer.client-topup.index') }}" class="btn-secondary text-center">Recharge Client</a>
            <button @click="exportTransactions()" class="btn-secondary">Export CSV</button>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100">
        <div class="p-5 flex items-center justify-between border-b border-purple-100">
            <h3 class="text-lg font-semibold text-gray-800">Transactions Récentes</h3>
            <a href="{{ route('deliverer.wallet.history') }}" class="text-sm font-medium text-purple-600 hover:text-purple-800">Voir Tout →</a>
        </div>
        <div class="p-4 space-y-3">
            <template x-for="transaction in recentTransactions" :key="transaction.id">
                <div class="flex items-center justify-between p-3 rounded-xl" :class="transaction.is_credit ? 'bg-green-50' : 'bg-red-50'">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="transaction.is_credit ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="transaction.is_credit ? 'M12 6v6m0 0v6m0-6h6m-6 0H6' : 'M20 12H4'"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800" x-text="transaction.description"></p>
                            <p class="text-xs text-gray-500" x-text="formatTimeAgo(transaction.date)"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold" :class="transaction.is_credit ? 'text-green-700' : 'text-red-700'" x-text="(transaction.is_credit ? '+' : '-') + formatAmount(Math.abs(transaction.amount))"></p>
                        <p class="text-xs text-gray-500" x-text="transaction.status_display"></p>
                    </div>
                </div>
            </template>
            <div x-show="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
                <p class="text-sm">Aucune transaction récente</p>
            </div>
        </div>
    </div>

</div>

<!-- Modal -->
<div x-show="emptyingModal.show" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div @click.away="closeEmptyingModal()" class="bg-white rounded-2xl p-6 w-full max-w-md">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Demander Vidage Wallet</h3>
        <div class="space-y-4">
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <p class="font-medium text-purple-800">Solde à vider</p>
                <p class="text-2xl font-bold text-purple-900" x-text="formatAmount(walletBalance)"></p>
            </div>
            <textarea x-model="emptyingModal.notes" rows="3" placeholder="Notes (optionnel)..." class="input-field"></textarea>
            <div class="flex justify-end gap-3 pt-4">
                <button @click="closeEmptyingModal()" class="btn-secondary">Annuler</button>
                <button @click="submitEmptyingRequest()" :disabled="emptyingModal.processing" class="btn-primary">
                    <span x-show="!emptyingModal.processing">Envoyer Demande</span>
                    <span x-show="emptyingModal.processing">Envoi...</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .btn-primary { @apply bg-purple-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-purple-700 transition-colors disabled:opacity-50; }
    .btn-secondary { @apply bg-slate-100 text-slate-700 py-2 px-4 rounded-lg font-semibold hover:bg-slate-200 transition-colors; }
    .input-field { @apply w-full px-4 py-3 border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent; }
</style>
@endpush

@push('scripts')
<script>
function walletApp() {
    return {
        walletBalance: @json($walletData['balance'] ?? 0),
        stats: @json($walletStats ?? []),
        recentTransactions: @json($recentTransactions ?? []),
        lastEmptying: @json($lastEmptying ?? null),
        emptyingModal: { show: false, notes: '', urgent: false, processing: false },

        init() {
            this.loadWalletData();
            setInterval(() => this.loadWalletData(), 120000);
        },

        async loadWalletData() {
            try {
                const balanceResponse = await fetch('/deliverer/api/wallet/balance');
                if (balanceResponse.ok) {
                    const data = await balanceResponse.json();
                    this.walletBalance = data.balance;
                }
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
            this.emptyingModal.show = false;
            this.emptyingModal.notes = '';
            this.emptyingModal.urgent = false;
            this.emptyingModal.processing = false;
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
            if (this.walletBalance > 200) return '🚨 Vidage urgent requis - Montant très élevé';
            if (this.walletBalance > 100) return '⚠️ Vidage recommandé - Montant élevé';
            if (this.walletBalance > 0) return '✅ Wallet normal - Espèces disponibles';
            return '💸 Wallet vide - Aucune espèce';
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            if (diffInMinutes < 60) return `Il y a ${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)}h`;
            return date.toLocaleDateString('fr-FR');
        },

        showToast(message, type = 'success') {
            // Implementation for toast notification
        }
    }
}
</script>
@endpush
@endsection