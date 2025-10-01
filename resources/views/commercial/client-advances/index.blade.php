@extends('layouts.commercial')

@section('title', 'Gestion des Avances Clients')
@section('page-title', 'Gestion des Avances Clients')
@section('page-description', 'Accordez et gérez les avances pour les frais de retour des clients')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="exportAdvances()"
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exporter Rapport
    </button>
    <button onclick="openQuickAddModal()"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Ajouter Avance
    </button>
</div>
@endsection

@section('content')
<div x-data="clientAdvancesApp()" x-init="init()">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Clients avec Avance</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.clients_with_advance">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Avances</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.formatted_total_advance_amount">-- DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Avance Moyenne</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="formatCurrency(stats.average_advance)">-- DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Transactions (30j)</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.recent_transactions">--</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 mb-8">
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text"
                               x-model="search"
                               @input="debouncedSearch()"
                               placeholder="Rechercher un client..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Filters -->
                    <select x-model="statusFilter" @change="applyFilters()" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Tous les statuts</option>
                        <option value="with_advance">Avec avance</option>
                        <option value="without_advance">Sans avance</option>
                    </select>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600" x-text="`${filteredClients.length} client(s) trouvé(s)`"></span>
                    <button @click="refresh()" class="p-2 text-gray-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Principal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière Modif.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modifié par</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="client in paginatedClients" :key="client.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="client.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="client.name"></div>
                                        <div class="text-sm text-gray-500" x-text="client.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" x-text="formatCurrency(client.wallet?.balance || 0)"></div>
                                <div class="text-xs text-gray-500">Solde disponible</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-bold"
                                          :class="client.wallet?.advance_balance > 0 ? 'text-emerald-600' : 'text-gray-400'"
                                          x-text="formatCurrency(client.wallet?.advance_balance || 0)"></span>
                                    <span x-show="client.wallet?.advance_balance > 0"
                                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Avance active
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="client.wallet?.advance_last_modified_at ? formatDate(client.wallet.advance_last_modified_at) : 'Jamais'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="client.wallet?.advance_modified_by?.name || 'N/A'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="viewClientDetails(client)"
                                            class="text-emerald-600 hover:text-emerald-900 p-2 rounded-md hover:bg-emerald-50"
                                            title="Voir détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button @click="addAdvance(client)"
                                            class="text-blue-600 hover:text-blue-900 p-2 rounded-md hover:bg-blue-50"
                                            title="Ajouter avance">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                    <button @click="removeAdvance(client)"
                                            x-show="client.wallet?.advance_balance > 0"
                                            class="text-red-600 hover:text-red-900 p-2 rounded-md hover:bg-red-50"
                                            title="Retirer avance">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Empty State -->
            <div x-show="filteredClients.length === 0 && !loading" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun client trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">Essayez de modifier vos critères de recherche.</p>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="text-center py-12">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-emerald-500 bg-white">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Chargement...
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="totalPages > 1" class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Précédent
                </button>
                <button @click="nextPage()" :disabled="currentPage === totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Suivant
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Affichage de <span class="font-medium" x-text="((currentPage - 1) * itemsPerPage) + 1"></span> à
                        <span class="font-medium" x-text="Math.min(currentPage * itemsPerPage, filteredClients.length)"></span> sur
                        <span class="font-medium" x-text="filteredClients.length"></span> résultats
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button @click="previousPage()" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <template x-for="page in visiblePages" :key="page">
                            <button @click="currentPage = page"
                                    :class="currentPage === page ? 'bg-emerald-50 border-emerald-500 text-emerald-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium" x-text="page">
                            </button>
                        </template>
                        <button @click="nextPage()" :disabled="currentPage === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add/Remove Advance Modals -->
    @include('commercial.client-advances.partials.add-advance-modal')
    @include('commercial.client-advances.partials.remove-advance-modal')
    @include('commercial.client-advances.partials.quick-add-modal')
</div>

<script>
function clientAdvancesApp() {
    return {
        clients: @json($clients->items()),
        stats: {
            total_clients: 0,
            clients_with_advance: 0,
            total_advance_amount: 0,
            average_advance: 0,
            recent_transactions: 0,
            formatted_total_advance_amount: '0.000 DT'
        },
        loading: false,
        search: '{{ request('search') }}',
        statusFilter: '',
        filteredClients: [],

        // Pagination
        currentPage: 1,
        itemsPerPage: 15,

        // Modals
        showAddModal: false,
        showRemoveModal: false,
        showQuickAddModal: false,
        selectedClient: null,

        // Modal forms
        addAdvanceForm: {
            amount: '',
            description: '',
            loading: false
        },
        removeAdvanceForm: {
            amount: '',
            description: '',
            loading: false
        },
        quickAddForm: {
            clientSearch: '',
            selectedClient: null,
            searchResults: [],
            amount: '',
            description: '',
            loading: false,
            searchTimeout: null
        },

        async init() {
            this.applyFilters();
            await this.loadStats();
        },

        async loadStats() {
            try {
                const response = await fetch('{{ route("commercial.client-advances.api.statistics") }}');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Erreur chargement statistiques:', error);
            }
        },

        applyFilters() {
            let filtered = [...this.clients];

            // Search filter
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                filtered = filtered.filter(client =>
                    client.name.toLowerCase().includes(searchLower) ||
                    client.email.toLowerCase().includes(searchLower) ||
                    client.phone?.toLowerCase().includes(searchLower)
                );
            }

            // Status filter
            if (this.statusFilter === 'with_advance') {
                filtered = filtered.filter(client => (client.wallet?.advance_balance || 0) > 0);
            } else if (this.statusFilter === 'without_advance') {
                filtered = filtered.filter(client => (client.wallet?.advance_balance || 0) === 0);
            }

            this.filteredClients = filtered;
            this.currentPage = 1; // Reset to first page
        },

        debouncedSearch: debounce(function() { this.applyFilters(); }, 300),

        async refresh() {
            this.loading = true;
            try {
                const response = await fetch(window.location.href);
                const text = await response.text();
                // Simple refresh - reload page
                window.location.reload();
            } catch (error) {
                console.error('Erreur refresh:', error);
            } finally {
                this.loading = false;
            }
        },

        // Pagination methods
        get totalPages() {
            return Math.ceil(this.filteredClients.length / this.itemsPerPage);
        },

        get paginatedClients() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredClients.slice(start, end);
        },

        get visiblePages() {
            const total = this.totalPages;
            const current = this.currentPage;
            const delta = 2;

            let range = [];
            for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                range.push(i);
            }

            if (current - delta > 2) {
                range.unshift('...');
            }
            if (current + delta < total - 1) {
                range.push('...');
            }

            range.unshift(1);
            if (total > 1) {
                range.push(total);
            }

            return range;
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        // Client actions
        viewClientDetails(client) {
            window.location.href = `{{ route('commercial.client-advances.show', '') }}/${client.id}`;
        },

        addAdvance(client) {
            this.selectedClient = client;
            this.showAddModal = true;
        },

        removeAdvance(client) {
            this.selectedClient = client;
            this.showRemoveModal = true;
        },

        // Utility methods
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }).format(amount || 0) + ' DT';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // Modal methods
        async submitAddAdvance() {
            if (!this.selectedClient || !this.addAdvanceForm.amount) {
                showToast('Veuillez remplir tous les champs requis', 'error');
                return;
            }

            this.addAdvanceForm.loading = true;

            try {
                const response = await fetch(`/commercial/client-advances/${this.selectedClient.id}/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.addAdvanceForm.amount),
                        description: this.addAdvanceForm.description
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message, 'success');

                    // Update client data
                    const clientIndex = this.clients.findIndex(c => c.id === this.selectedClient.id);
                    if (clientIndex !== -1) {
                        this.clients[clientIndex].wallet.advance_balance = data.advance_balance;
                    }

                    // Reset form and close modal
                    this.addAdvanceForm = { amount: '', description: '', loading: false };
                    this.showAddModal = false;
                    this.selectedClient = null;

                    // Refresh stats
                    await this.loadStats();
                    this.applyFilters();

                } else {
                    showToast(data.message || 'Erreur lors de l\'ajout de l\'avance', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur de connexion', 'error');
            } finally {
                this.addAdvanceForm.loading = false;
            }
        },

        async submitRemoveAdvance() {
            if (!this.selectedClient || !this.removeAdvanceForm.amount || !this.removeAdvanceForm.description) {
                showToast('Veuillez remplir tous les champs requis', 'error');
                return;
            }

            const amount = parseFloat(this.removeAdvanceForm.amount);
            const availableAdvance = this.selectedClient.wallet?.advance_balance || 0;

            if (amount > availableAdvance) {
                showToast(`Le montant ne peut pas dépasser l'avance disponible (${this.formatCurrency(availableAdvance)})`, 'error');
                return;
            }

            this.removeAdvanceForm.loading = true;

            try {
                const response = await fetch(`/commercial/client-advances/${this.selectedClient.id}/remove`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        amount: amount,
                        description: this.removeAdvanceForm.description
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message, 'success');

                    // Update client data
                    const clientIndex = this.clients.findIndex(c => c.id === this.selectedClient.id);
                    if (clientIndex !== -1) {
                        this.clients[clientIndex].wallet.advance_balance = data.advance_balance;
                    }

                    // Reset form and close modal
                    this.removeAdvanceForm = { amount: '', description: '', loading: false };
                    this.showRemoveModal = false;
                    this.selectedClient = null;

                    // Refresh stats
                    await this.loadStats();
                    this.applyFilters();

                } else {
                    showToast(data.message || 'Erreur lors du retrait de l\'avance', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur de connexion', 'error');
            } finally {
                this.removeAdvanceForm.loading = false;
            }
        },

        async searchClientsForQuickAdd() {
            const query = this.quickAddForm.clientSearch.trim();

            if (query.length < 2) {
                this.quickAddForm.searchResults = [];
                return;
            }

            // Debounce the search
            clearTimeout(this.quickAddForm.searchTimeout);
            this.quickAddForm.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/commercial/client-advances/api/search-clients?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    this.quickAddForm.searchResults = data.clients || [];
                } catch (error) {
                    console.error('Erreur recherche clients:', error);
                    this.quickAddForm.searchResults = [];
                }
            }, 300);
        },

        selectClientForQuickAdd(client) {
            this.quickAddForm.selectedClient = client;
            this.quickAddForm.clientSearch = client.name;
            this.quickAddForm.searchResults = [];
        },

        clearSelectedClient() {
            this.quickAddForm.selectedClient = null;
            this.quickAddForm.clientSearch = '';
            this.quickAddForm.searchResults = [];
        },

        async submitQuickAdd() {
            if (!this.quickAddForm.selectedClient || !this.quickAddForm.amount) {
                showToast('Veuillez sélectionner un client et saisir un montant', 'error');
                return;
            }

            this.quickAddForm.loading = true;

            try {
                const response = await fetch(`/commercial/client-advances/${this.quickAddForm.selectedClient.id}/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.quickAddForm.amount),
                        description: this.quickAddForm.description || `Avance accordée via ajout rapide`
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message, 'success');

                    // Update client data if in current list
                    const clientIndex = this.clients.findIndex(c => c.id === this.quickAddForm.selectedClient.id);
                    if (clientIndex !== -1) {
                        this.clients[clientIndex].wallet.advance_balance = data.advance_balance;
                    }

                    // Reset form and close modal
                    this.quickAddForm = {
                        clientSearch: '',
                        selectedClient: null,
                        searchResults: [],
                        amount: '',
                        description: '',
                        loading: false,
                        searchTimeout: null
                    };
                    this.showQuickAddModal = false;

                    // Refresh stats
                    await this.loadStats();
                    this.applyFilters();

                } else {
                    showToast(data.message || 'Erreur lors de l\'ajout de l\'avance', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur de connexion', 'error');
            } finally {
                this.quickAddForm.loading = false;
            }
        }
    };
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Global functions
function openQuickAddModal() {
    // Find Alpine component instance and set showQuickAddModal to true
    const element = document.querySelector('[x-data*="clientAdvancesApp"]');
    if (element && element._x_dataStack && element._x_dataStack[0]) {
        element._x_dataStack[0].showQuickAddModal = true;
    }
}

function exportAdvances() {
    window.location.href = '{{ route("commercial.client-advances.index") }}?export=true';
}
</script>
@endsection