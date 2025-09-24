@extends('layouts.client')

@section('title', 'Mes Colis')
@section('page-title', 'Gestion des Colis')
@section('page-description', 'Interface centralisée pour tous vos envois')

@section('content')
<div class="max-w-7xl mx-auto" x-data="packagesTabsManager()" x-init="init()">

    <!-- Statistiques simples -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-purple-600">TOTAL COLIS</p>
                    <p class="text-xl md:text-2xl font-bold text-purple-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-400 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-purple-100 border border-purple-300 rounded-lg p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-purple-700">EN ATTENTE</p>
                    <p class="text-xl md:text-2xl font-bold text-purple-900">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-purple-200 border border-purple-400 rounded-lg p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-purple-800">EN COURS</p>
                    <p class="text-xl md:text-2xl font-bold text-purple-900">{{ $stats['in_progress'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-3 md:p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm font-medium text-green-600">LIVRÉS</p>
                    <p class="text-xl md:text-2xl font-bold text-green-900">{{ $stats['delivered'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 md:w-10 md:h-10 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation simplifiée -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex overflow-x-auto" aria-label="Tabs">
                <button @click="setActiveTab('create')"
                        :class="activeTab === 'create' ? 'bg-purple-50 text-purple-600 border-purple-500' : 'text-gray-500 hover:text-purple-600'"
                        class="flex-1 whitespace-nowrap py-3 px-3 md:px-4 text-xs md:text-sm font-medium border-b-2 border-transparent">
                    <div class="flex items-center justify-center space-x-1 md:space-x-2">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>Créer</span>
                    </div>
                </button>

                <button @click="setActiveTab('pending')"
                        :class="activeTab === 'pending' ? 'bg-purple-100 text-purple-700 border-purple-500' : 'text-gray-500 hover:text-purple-600'"
                        class="flex-1 whitespace-nowrap py-3 px-3 md:px-4 text-xs md:text-sm font-medium border-b-2 border-transparent">
                    <div class="flex items-center justify-center space-x-1 md:space-x-2">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="hidden sm:inline">En Attente</span>
                        <span class="sm:hidden">Attente</span>
                        <span x-show="stats.pending > 0" class="bg-purple-500 text-white text-xs px-1.5 py-0.5 rounded-full" x-text="stats.pending"></span>
                    </div>
                </button>

                <button @click="setActiveTab('all')"
                        :class="activeTab === 'all' ? 'bg-purple-200 text-purple-800 border-purple-600' : 'text-gray-500 hover:text-purple-600'"
                        class="flex-1 whitespace-nowrap py-3 px-3 md:px-4 text-xs md:text-sm font-medium border-b-2 border-transparent">
                    <div class="flex items-center justify-center space-x-1 md:space-x-2">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Tous</span>
                    </div>
                </button>
            </nav>
        </div>

        <!-- Contenu des onglets -->
        <div class="p-3 md:p-6">
            <!-- Onglet Créer -->
            <div x-show="activeTab === 'create'" class="text-center">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2">Créer un Nouveau Colis</h3>
                <p class="text-sm md:text-base text-gray-600 mb-6">Créez rapidement un nouveau colis pour expédition</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('client.packages.create') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 md:px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer Colis
                    </a>
                    <a href="{{ route('client.packages.create') }}?fast=true"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 md:px-6 py-3 border border-purple-600 text-purple-600 font-medium rounded-lg hover:bg-purple-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Mode Rapide
                    </a>
                </div>
            </div>

            <!-- Onglet En Attente -->
            <div x-show="activeTab === 'pending'">
                @if($activeTab === 'pending')
                    @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis en attente de pickup', 'emptyIcon' => 'clock'])
                @endif
            </div>

            <!-- Onglet Tous -->
            <div x-show="activeTab === 'all'">
                <!-- Filtres simples -->
                <div class="bg-gray-50 rounded-lg p-3 md:p-4 mb-6">
                    <form method="GET" action="{{ route('client.packages.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                        <input type="hidden" name="tab" value="all">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Code, destinataire..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous</option>
                                <option value="CREATED" @selected(request('status') == 'CREATED')>Créés</option>
                                <option value="PICKED_UP" @selected(request('status') == 'PICKED_UP')>Collectés</option>
                                <option value="DELIVERED" @selected(request('status') == 'DELIVERED')>Livrés</option>
                                <option value="RETURNED" @selected(request('status') == 'RETURNED')>Retournés</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end sm:col-span-2 md:col-span-1">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md transition-colors text-sm md:text-base">
                                Filtrer
                            </button>
                        </div>
                    </form>
                </div>

                @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis trouvé', 'emptyIcon' => 'package'])
            </div>
        </div>
    </div>

    <!-- Actions flottantes simplifiées -->
    <div x-show="selectedPackages.length > 0" x-transition
         class="fixed bottom-4 left-4 right-4 md:left-1/2 md:right-auto md:transform md:-translate-x-1/2 bg-white rounded-lg shadow-lg border border-gray-200 p-3 md:p-4 z-50">
        <div class="flex flex-col md:flex-row items-center md:space-x-4 space-y-2 md:space-y-0">
            <div class="flex items-center text-purple-600 font-medium text-sm">
                <span x-text="selectedPackages.length"></span>
                <span class="ml-1">sélectionné(s)</span>
            </div>
            <div class="flex items-center space-x-2 w-full md:w-auto">
                <button @click="printSelected()" class="flex-1 md:flex-none px-3 md:px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md text-xs md:text-sm">
                    <svg class="w-3 h-3 md:w-4 md:h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span class="hidden sm:inline">Imprimer</span>
                    <span class="sm:hidden">Print</span>
                </button>
                <button @click="deleteSelected()" :disabled="!canDeleteSelected"
                        :class="canDeleteSelected ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="flex-1 md:flex-none px-3 md:px-4 py-2 font-medium rounded-md text-xs md:text-sm">
                    <svg class="w-3 h-3 md:w-4 md:h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span class="hidden sm:inline">Supprimer</span>
                    <span class="sm:hidden">Del</span>
                </button>
                <button @click="clearSelection()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-md">
                    <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function packagesTabsManager() {
    return {
        activeTab: '{{ $activeTab ?? "all" }}',
        selectedPackages: [],
        allSelected: false,
        stats: {
            total: {{ $stats['total'] ?? 0 }},
            pending: {{ $stats['pending'] ?? 0 }},
            in_progress: {{ $stats['in_progress'] ?? 0 }},
            delivered: {{ $stats['delivered'] ?? 0 }},
        },

        init() {},

        setActiveTab(tab) {
            this.activeTab = tab;
            this.selectedPackages = [];
            this.allSelected = false;

            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.pushState({}, '', url);

            if (tab === 'pending' || (tab === 'all' && '{{ !request()->has("search") && !request()->has("status") }}')) {
                window.location.href = url.toString();
            }
        },

        get canDeleteSelected() {
            if (this.selectedPackages.length === 0) return false;
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]:checked');
            return Array.from(checkboxes).every(cb => ['CREATED', 'AVAILABLE'].includes(cb.dataset.status));
        },

        toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]');
            this.allSelected = !this.allSelected;
            if (this.allSelected) {
                this.selectedPackages = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selectedPackages = [];
            }
        },

        clearSelection() {
            this.selectedPackages = [];
            this.allSelected = false;
        },

        printSelected() {
            if (this.selectedPackages.length === 0) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("client.packages.print.multiple") }}';
            form.target = '_blank';
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            this.selectedPackages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'package_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        async deleteSelected() {
            if (this.selectedPackages.length === 0 || !this.canDeleteSelected) return;
            if (!confirm(`Confirmez-vous la suppression de ${this.selectedPackages.length} colis ?`)) return;
            try {
                const response = await fetch('{{ route("client.packages.bulk.destroy") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ package_ids: this.selectedPackages })
                });
                const data = await response.json();
                if (data.success) {
                    alert(`${data.deleted_count} colis supprimés !`);
                    window.location.reload();
                } else {
                    alert(`Erreur: ${data.message}`);
                }
            } catch (error) {
                alert('Erreur de connexion.');
            }
        },
    }
}
</script>
@endsection