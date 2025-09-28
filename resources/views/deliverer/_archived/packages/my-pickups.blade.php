@extends('layouts.deliverer')

@section('title', 'Mes Pickups')

@section('content')
<div class="p-4 sm:p-6 lg:p-8" x-data="myPickupsApp()" x-init="init()">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Mes Pickups</h1>
                <p class="text-gray-500 mt-1" x-text="`${filteredPackages.length} colis acceptés à collecter`"></p>
            </div>
            <div class="flex items-center gap-2 mt-4 md:mt-0">
                <button @click="showFilters = !showFilters" class="action-button-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/></svg>
                    Filtres
                </button>
                <button @click="isSelectionMode = !isSelectionMode; selectedPackages = []" :class="isSelectionMode ? 'bg-purple-600 text-white' : ''" class="action-button-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sélection
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div x-show="showFilters" x-transition class="bg-white rounded-2xl p-6 mb-8 shadow-sm border border-purple-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" x-model="searchQuery" @input.debounce.300ms="applyFilters()" placeholder="Rechercher..." class="input-field md:col-span-3">
            <select x-model="filters.delegationFrom" @change="applyFilters()" class="input-field">
                <option value="">Toutes les délégations</option>
                <template x-for="delegation in delegations" :key="delegation.id">
                    <option :value="delegation.id" x-text="delegation.name"></option>
                </template>
            </select>
            <input type="number" x-model="filters.codMin" placeholder="COD Min" class="input-field">
            <input type="number" x-model="filters.codMax" placeholder="COD Max" class="input-field">
        </div>
        <div class="flex justify-end mt-4">
            <button @click="clearFilters()" class="action-button-secondary">Effacer les filtres</button>
        </div>
    </div>

    <!-- Selection Mode Bar -->
    <div x-show="isSelectionMode" x-transition class="bg-purple-100 border border-purple-200 rounded-2xl p-4 mb-8 flex items-center justify-between">
        <span class="text-sm font-medium text-purple-800" x-text="`${selectedPackages.length} colis sélectionné(s)`"></span>
        <div>
            <button @click="selectAll()" class="font-medium text-purple-600 text-sm mr-4">Tout sélectionner</button>
            <button @click="bulkMarkCollected()" :disabled="selectedPackages.length === 0" class="action-button-primary">Collecter la sélection</button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Chargement de vos pickups...</p>
    </div>

    <!-- Package List -->
    <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border hover:shadow-lg transition-shadow duration-300" :class="{'border-purple-400 ring-2 ring-purple-200': selectedPackages.includes(package.id), 'border-purple-100': !selectedPackages.includes(package.id)}" @click="isSelectionMode ? toggleSelection(package.id) : null">
                <div class="p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-800" x-text="package.package_code"></p>
                            <p class="text-sm text-gray-500" x-text="`Accepté ` + formatTimeAgo(package.assigned_at)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-purple-800" x-text="formatAmount(package.cod_amount)"></p>
                            <p class="text-xs text-gray-500">COD</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span x-show="isOldPackage(package)" class="badge bg-red-100 text-red-800">Ancien</span>
                        <span x-show="isHighCod(package)" class="badge bg-green-100 text-green-800">COD Élevé</span>
                        <span x-show="isAcceptedToday(package)" class="badge bg-blue-100 text-blue-800">Aujourd'hui</span>
                    </div>
                </div>
                <div class="bg-slate-50 p-5 border-t border-purple-100 space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Collecte</p>
                        <p class="text-sm font-medium text-gray-800" x-text="package.supplier_data?.name || 'N/A'"></p>
                        <p class="text-sm text-purple-700 font-semibold" x-text="package.delegation_from?.name || 'N/A'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Destination</p>
                        <p class="text-sm font-medium text-gray-800" x-text="package.recipient_data?.name || 'N/A'"></p>
                        <p class="text-sm text-purple-700 font-semibold" x-text="package.delegation_to?.name || 'N/A'"></p>
                    </div>
                </div>
                <div class="p-4 flex gap-2">
                    <button @click.stop="markAsCollected(package)" class="action-button-primary flex-1">Collecter</button>
                    <a :href="`/deliverer/packages/${package.id}`" @click.stop class="action-button-secondary">Détails</a>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && filteredPackages.length === 0" class="text-center py-16">
        <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun pickup pour le moment</h3>
        <p class="text-gray-500 mb-6">Les nouveaux colis que vous acceptez apparaîtront ici.</p>
        <a href="{{ route('deliverer.packages.available') }}" class="action-button-primary">Voir les pickups disponibles</a>
    </div>

</div>

@push('styles')
<style>
    .action-button-primary {
        @apply w-full text-center bg-purple-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-purple-700 transition-colors disabled:opacity-50;
    }
    .action-button-secondary {
        @apply w-full text-center bg-purple-100 text-purple-700 py-3 px-4 rounded-xl font-semibold hover:bg-purple-200 transition-colors;
    }
    .input-field {
        @apply w-full px-4 py-3 border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent;
    }
    .badge {
        @apply text-xs font-semibold px-2.5 py-1 rounded-full;
    }
</style>
@endpush

@push('scripts')
<script>
function myPickupsApp() {
    return {
        packages: @json($packages->items() ?? []),
        delegations: @json($delegations ?? []),
        filteredPackages: [],
        loading: true, isSelectionMode: false, selectedPackages: [],
        showFilters: false, searchQuery: '',
        filters: { delegationFrom: '', codMin: '', codMax: '' },

        init() {
            this.applyFilters();
            this.loading = false;
        },

        applyFilters() {
            let filtered = [...this.packages];
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pkg => 
                    Object.values(pkg).some(val => String(val).toLowerCase().includes(query)) ||
                    Object.values(pkg.supplier_data || {}).some(val => String(val).toLowerCase().includes(query)) ||
                    Object.values(pkg.recipient_data || {}).some(val => String(val).toLowerCase().includes(query))
                );
            }
            if (this.filters.delegationFrom) {
                filtered = filtered.filter(pkg => pkg.delegation_from?.id == this.filters.delegationFrom);
            }
            if (this.filters.codMin) {
                filtered = filtered.filter(pkg => parseFloat(pkg.cod_amount) >= parseFloat(this.filters.codMin));
            }
            if (this.filters.codMax) {
                filtered = filtered.filter(pkg => parseFloat(pkg.cod_amount) <= parseFloat(this.filters.codMax));
            }
            this.filteredPackages = filtered;
        },

        clearFilters() {
            this.searchQuery = '';
            this.filters = { delegationFrom: '', codMin: '', codMax: '' };
            this.applyFilters();
        },

        isOldPackage(pkg) { return (new Date() - new Date(pkg.assigned_at)) / (1000 * 3600) > 24; },
        isHighCod(pkg) { return parseFloat(pkg.cod_amount) >= 50; },
        isAcceptedToday(pkg) { return new Date(pkg.assigned_at).toDateString() === new Date().toDateString(); },

        toggleSelection(packageId) {
            const index = this.selectedPackages.indexOf(packageId);
            if (index > -1) this.selectedPackages.splice(index, 1);
            else this.selectedPackages.push(packageId);
        },

        selectAll() {
            this.selectedPackages = this.filteredPackages.map(p => p.id);
        },

        markAsCollected(package) {
            if (!confirm(`Confirmer la collecte du colis ${package.package_code} ?`)) return;
            this.executeAction('pickup', [package.id]);
        },

        bulkMarkCollected() {
            if (!confirm(`Confirmer la collecte de ${this.selectedPackages.length} colis ?`)) return;
            this.executeAction('pickup', this.selectedPackages);
        },

        async executeAction(action, packageIds) {
            this.loading = true;
            try {
                const response = await fetch('/deliverer/packages/bulk-action', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({ action, package_ids: packageIds })
                });
                const data = await response.json();
                if (data.success) {
                    this.packages = this.packages.filter(p => !packageIds.includes(p.id));
                    this.applyFilters();
                    this.isSelectionMode = false;
                    this.selectedPackages = [];
                }
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },

        formatAmount(amount) { return parseFloat(amount || 0).toFixed(3) + ' DT'; },
        formatTimeAgo(timestamp) {
            const diff = (new Date() - new Date(timestamp)) / 1000;
            if (diff < 3600) return `il y a ${Math.round(diff / 60)} min`;
            if (diff < 86400) return `il y a ${Math.round(diff / 3600)} h`;
            return `il y a ${Math.round(diff / 86400)} j`;
        }
    }
}
</script>
@endpush
@endsection
