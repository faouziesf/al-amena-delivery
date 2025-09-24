@extends('layouts.deliverer')

@section('title', 'Historique des Paiements')

@section('content')
<div class="bg-gray-50" x-data="paymentsHistoryApp()">

    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Historique des Paiements
                    </h1>
                    <p class="text-gray-600 mt-1">Tous vos paiements collectés</p>
                </div>
                <div class="flex gap-3">
                    <button @click="exportPayments()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exporter
                    </button>
                    <a href="{{ route('deliverer.payments.index') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="p-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Collecté</p>
                    <p class="text-2xl font-bold text-green-600" x-text="formatAmount(stats.total_collected)"></p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Paiements</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="stats.total_payments"></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="formatAmount(stats.today_total)"></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Cette Semaine</p>
                    <p class="text-2xl font-bold text-orange-600" x-text="formatAmount(stats.week_total)"></p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="px-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                    <input type="date" x-model="filters.date_from" @change="applyFilters()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                    <input type="date" x-model="filters.date_to" @change="applyFilters()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant minimum</label>
                    <input type="number" x-model="filters.min_amount" @input="applyFilters()"
                           placeholder="0.000" step="0.001"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="flex justify-between mt-4">
                <button @click="resetFilters()"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Réinitialiser les filtres
                </button>
                <div class="text-sm text-gray-600">
                    <span x-text="filteredPayments.length"></span> paiements trouvés
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des paiements -->
    <div class="px-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div x-show="loading" class="p-8 text-center">
                <div class="animate-spin w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full mx-auto mb-4"></div>
                <p class="text-gray-600">Chargement des paiements...</p>
            </div>

            <div x-show="!loading && filteredPayments.length === 0" class="p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement trouvé</h3>
                <p class="text-gray-600">Aucun paiement ne correspond aux critères sélectionnés.</p>
            </div>

            <div x-show="!loading && filteredPayments.length > 0">
                <template x-for="payment in paginatedPayments" :key="payment.id">
                    <div class="border-b border-gray-100 p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-green-100 p-2 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="payment.package_code"></p>
                                        <p class="text-sm text-gray-600" x-text="payment.client_name"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600" x-text="formatAmount(payment.amount)"></p>
                                <p class="text-sm text-gray-500" x-text="formatDate(payment.collected_at)"></p>
                            </div>
                            <div class="ml-4">
                                <button @click="showPaymentDetails(payment)"
                                        class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Pagination -->
                <div x-show="totalPages > 1" class="p-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Page <span x-text="currentPage"></span> sur <span x-text="totalPages"></span>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="currentPage > 1 && (currentPage--)"
                                    :disabled="currentPage === 1"
                                    class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50">
                                Précédent
                            </button>
                            <button @click="currentPage < totalPages && (currentPage++)"
                                    :disabled="currentPage === totalPages"
                                    class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50">
                                Suivant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal détails paiement -->
    <div x-show="showModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-white rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Détails du Paiement</h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="selectedPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Code Colis</label>
                        <p class="text-lg font-bold text-gray-900" x-text="selectedPayment?.package_code"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Client</label>
                        <p class="text-gray-900" x-text="selectedPayment?.client_name"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Montant</label>
                        <p class="text-2xl font-bold text-green-600" x-text="formatAmount(selectedPayment?.amount)"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Date de collecte</label>
                        <p class="text-gray-900" x-text="formatDateTime(selectedPayment?.collected_at)"></p>
                    </div>

                    <div x-show="selectedPayment?.notes">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="text-gray-900" x-text="selectedPayment?.notes"></p>
                    </div>

                    <div class="pt-4 flex space-x-3">
                        <a :href="`/deliverer/packages/${selectedPayment?.package_id}`"
                           class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg text-center hover:bg-blue-700">
                            Voir le Colis
                        </a>
                        <button @click="showModal = false"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paymentsHistoryApp() {
    return {
        loading: true,
        payments: @json($payments ?? []),
        filteredPayments: [],
        showModal: false,
        selectedPayment: null,

        // Pagination
        currentPage: 1,
        itemsPerPage: 10,

        // Filters
        filters: {
            date_from: '',
            date_to: '',
            min_amount: ''
        },

        // Stats
        stats: @json($stats ?? [
            'total_collected' => 0,
            'total_payments' => 0,
            'today_total' => 0,
            'week_total' => 0
        ]),

        init() {
            this.filteredPayments = [...this.payments];
            this.calculateStats();
            this.loading = false;
        },

        get paginatedPayments() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPayments.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredPayments.length / this.itemsPerPage);
        },

        applyFilters() {
            this.filteredPayments = this.payments.filter(payment => {
                if (this.filters.date_from && payment.collected_at < this.filters.date_from) return false;
                if (this.filters.date_to && payment.collected_at > this.filters.date_to) return false;
                if (this.filters.min_amount && payment.amount < parseFloat(this.filters.min_amount)) return false;
                return true;
            });
            this.currentPage = 1;
        },

        resetFilters() {
            this.filters = { date_from: '', date_to: '', min_amount: '' };
            this.filteredPayments = [...this.payments];
            this.currentPage = 1;
        },

        calculateStats() {
            const today = new Date().toISOString().split('T')[0];
            const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            this.stats = {
                total_collected: this.payments.reduce((sum, p) => sum + p.amount, 0),
                total_payments: this.payments.length,
                today_total: this.payments.filter(p => p.collected_at.startsWith(today)).reduce((sum, p) => sum + p.amount, 0),
                week_total: this.payments.filter(p => p.collected_at >= weekAgo).reduce((sum, p) => sum + p.amount, 0)
            };
        },

        showPaymentDetails(payment) {
            this.selectedPayment = payment;
            this.showModal = true;
        },

        exportPayments() {
            const csvData = this.filteredPayments.map(payment => ({
                'Code Colis': payment.package_code,
                'Client': payment.client_name,
                'Montant': payment.amount,
                'Date': payment.collected_at,
                'Notes': payment.notes || ''
            }));

            this.downloadCSV(csvData, 'historique_paiements.csv');
        },

        downloadCSV(data, filename) {
            const csv = this.convertToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        },

        convertToCSV(data) {
            if (!data.length) return '';

            const headers = Object.keys(data[0]);
            const csvContent = [
                headers.join(','),
                ...data.map(row => headers.map(header => `"${row[header]}"`).join(','))
            ].join('\n');

            return csvContent;
        },

        formatAmount(amount) {
            return new Intl.NumberFormat('fr-TN', {
                style: 'currency',
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('fr-FR');
        },

        formatDateTime(date) {
            return new Date(date).toLocaleString('fr-FR');
        }
    }
}
</script>
@endsection