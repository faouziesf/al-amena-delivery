@extends('layouts.deliverer')

@section('title', 'Pickups Disponibles')

@section('content')
<div x-data="availablePickupsApp()" x-init="init()">
    <!-- Header avec filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Pickups Disponibles</h1>
                    <p class="text-sm text-gray-600" x-text="`${packages.length} colis disponibles`"></p>
                </div>
                
                <!-- Scanner Rapide -->
                <button @click="openScanner()" 
                        class="bg-gradient-to-r from-emerald-500 to-green-500 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </button>
            </div>

            <!-- Filtres rapides -->
            <div class="flex items-center space-x-3 overflow-x-auto pb-2">
                <button @click="filterBy = ''; loadPackages()" 
                        :class="filterBy === '' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                    Tout
                </button>
                <button @click="filterBy = 'high_cod'; loadPackages()" 
                        :class="filterBy === 'high_cod' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                    COD Élevé (+50 DT)
                </button>
                <button @click="filterBy = 'nearby'; loadPackages()" 
                        :class="filterBy === 'nearby' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                    Proches
                </button>
                <button @click="filterBy = 'urgent'; loadPackages()" 
                        :class="filterBy === 'urgent' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                    Urgents
                </button>
            </div>

            <!-- Barre de recherche -->
            <div class="mt-4 relative">
                <input type="text" x-model="searchQuery" @input="searchPackages()" 
                       placeholder="Rechercher par code, nom client, téléphone..."
                       class="w-full pl-10 pr-4 py-3 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-colors">
                <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
    </div>

    <!-- Liste des colis -->
    <div x-show="!loading" class="space-y-3 px-4">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <!-- Header avec statut priorité -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <p class="text-xs text-gray-500" x-text="formatTimeAgo(package.created_at)"></p>
                        </div>
                    </div>
                    
                    <!-- COD Badge -->
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 text-sm font-bold rounded-full"
                              :class="getCodBadgeClass(package.cod_amount)"
                              x-text="formatAmount(package.cod_amount)">
                        </span>
                        <p class="text-xs text-gray-500 mt-1">COD</p>
                    </div>
                </div>

                <!-- Détails du colis -->
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Expéditeur -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">📦 Expéditeur</h4>
                            <p class="font-medium text-gray-900" x-text="package.sender_data?.name || package.sender?.name || 'N/A'"></p>
                            <p class="text-sm text-gray-600" x-text="package.sender_data?.phone || 'N/A'"></p>
                            <p class="text-xs text-blue-600" x-text="package.delegation_from?.name || 'N/A'"></p>
                        </div>
                        
                        <!-- Récepteur -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">🎯 Récepteur</h4>
                            <p class="font-medium text-gray-900" x-text="package.recipient_data?.name || 'N/A'"></p>
                            <p class="text-sm text-gray-600" x-text="package.recipient_data?.phone || 'N/A'"></p>
                            <p class="text-xs text-green-600" x-text="package.delegation_to?.name || 'N/A'"></p>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Contenu:</span> 
                            <span x-text="package.content_description || 'Non spécifié'"></span>
                        </p>
                        <div x-show="package.notes" class="mt-2">
                            <p class="text-xs text-gray-600">
                                <span class="font-medium">Notes:</span> 
                                <span x-text="package.notes"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3 mt-4">
                        <!-- Accepter Pickup -->
                        <button @click="acceptPickup(package)" 
                                :disabled="accepting === package.id"
                                class="flex-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white py-3 px-4 rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50">
                            <span x-show="accepting !== package.id" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Accepter
                            </span>
                            <span x-show="accepting === package.id" class="flex items-center justify-center">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                Acceptation...
                            </span>
                        </button>

                        <!-- Scanner ce colis -->
                        <button @click="scanSpecificPackage(package.package_code)" 
                                class="bg-blue-100 text-blue-600 p-3 rounded-xl hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                            </svg>
                        </button>

                        <!-- Plus d'infos -->
                        <button @click="viewPackageDetails(package)" 
                                class="bg-gray-100 text-gray-600 p-3 rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="filteredPackages.length === 0 && !loading" 
             class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun pickup disponible</h3>
            <p class="text-gray-600 mb-6">Tous les colis sont déjà assignés ou aucun nouveau colis n'est disponible.</p>
            <button @click="loadPackages()" 
                    class="bg-emerald-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-600 transition-colors">
                🔄 Actualiser
            </button>
        </div>
    </div>

    <!-- Pull to Refresh Indicator -->
    <div x-show="refreshing" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-40">
        <div class="bg-emerald-500 text-white px-4 py-2 rounded-full shadow-lg flex items-center space-x-2">
            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>Actualisation...</span>
        </div>
    </div>
</div>

<!-- Détails Colis Modal -->
<div x-show="showDetailsModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
    <div x-show="showDetailsModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
         x-transition:enter-end="translate-y-0 sm:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0"
         class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-md max-h-96 overflow-y-auto">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Détails du Colis</h3>
            <button @click="showDetailsModal = false" 
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="selectedPackage" class="space-y-4">
            <!-- Code Package -->
            <div class="bg-blue-50 p-4 rounded-xl">
                <p class="text-sm text-blue-600 font-medium">Code Colis</p>
                <p class="text-xl font-bold text-blue-900 font-mono" x-text="selectedPackage?.package_code"></p>
            </div>

            <!-- COD -->
            <div class="bg-green-50 p-4 rounded-xl">
                <p class="text-sm text-green-600 font-medium">Montant COD</p>
                <p class="text-2xl font-bold text-green-900" x-text="formatAmount(selectedPackage?.cod_amount)"></p>
            </div>

            <!-- Adresses complètes -->
            <div class="space-y-3">
                <div class="border-l-4 border-orange-400 pl-4">
                    <p class="font-semibold text-gray-900">📍 Adresse Pickup</p>
                    <p class="text-sm text-gray-600" x-text="selectedPackage?.sender_data?.address || 'Non spécifiée'"></p>
                </div>
                
                <div class="border-l-4 border-green-400 pl-4">
                    <p class="font-semibold text-gray-900">🎯 Adresse Livraison</p>
                    <p class="text-sm text-gray-600" x-text="selectedPackage?.recipient_data?.address || 'Non spécifiée'"></p>
                </div>
            </div>

            <!-- Actions Modal -->
            <div class="flex space-x-3 pt-4">
                <button @click="acceptPickup(selectedPackage); showDetailsModal = false" 
                        class="flex-1 bg-emerald-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors">
                    Accepter Pickup
                </button>
                <button @click="showDetailsModal = false" 
                        class="bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function availablePickupsApp() {
    return {
        packages: @json($packages->items()),
        filteredPackages: [],
        loading: false,
        refreshing: false,
        accepting: null,
        searchQuery: '',
        filterBy: '',
        showDetailsModal: false,
        selectedPackage: null,

        init() {
            this.filteredPackages = this.packages;
            this.setupPullToRefresh();
            this.startAutoRefresh();
        },

        async loadPackages() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterBy === 'high_cod') params.set('cod_min', '50');
                if (this.filterBy === 'urgent') params.set('urgent', '1');
                if (this.searchQuery) params.set('search', this.searchQuery);

                const response = await fetch(`{{ route('deliverer.pickups.available') }}?${params}&ajax=1`);
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applyFilters();
                }
            } catch (error) {
                console.error('Erreur chargement pickups:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.loading = false;
            this.refreshing = false;
        },

        async acceptPickup(package) {
            if (this.accepting) return;
            
            this.accepting = package.id;
            try {
                const response = await fetch(`/deliverer/packages/${package.id}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== package.id);
                    this.applyFilters();
                    
                    // Rediriger vers la page du colis si fournie
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1000);
                    }
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'acceptation', 'error');
                }
            } catch (error) {
                console.error('Erreur acceptation:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.accepting = null;
        },

        searchPackages() {
            this.applyFilters();
        },

        applyFilters() {
            let filtered = [...this.packages];

            // Filtre recherche
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(p => 
                    p.package_code.toLowerCase().includes(query) ||
                    (p.recipient_data?.name || '').toLowerCase().includes(query) ||
                    (p.recipient_data?.phone || '').toLowerCase().includes(query) ||
                    (p.sender_data?.name || '').toLowerCase().includes(query)
                );
            }

            // Filtres spéciaux
            if (this.filterBy === 'high_cod') {
                filtered = filtered.filter(p => p.cod_amount >= 50);
            } else if (this.filterBy === 'urgent') {
                const twoDaysAgo = new Date();
                twoDaysAgo.setDate(twoDaysAgo.getDate() - 2);
                filtered = filtered.filter(p => new Date(p.created_at) <= twoDaysAgo);
            }

            this.filteredPackages = filtered;
        },

        viewPackageDetails(package) {
            this.selectedPackage = package;
            this.showDetailsModal = true;
        },

        scanSpecificPackage(packageCode) {
            // Ouvrir le scanner avec le code pré-rempli
            openScanner();
            // Ou implémenter le scan direct
        },

        setupPullToRefresh() {
            let startY = 0;
            let pullDistance = 0;
            const threshold = 100;

            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0) {
                    startY = e.touches[0].clientY;
                }
            });

            document.addEventListener('touchmove', (e) => {
                if (window.scrollY === 0 && startY > 0) {
                    pullDistance = e.touches[0].clientY - startY;
                    if (pullDistance > threshold) {
                        this.refreshing = true;
                    }
                }
            });

            document.addEventListener('touchend', () => {
                if (pullDistance > threshold) {
                    this.loadPackages();
                }
                startY = 0;
                pullDistance = 0;
            });
        },

        startAutoRefresh() {
            setInterval(() => {
                if (!this.loading && !this.refreshing) {
                    this.loadPackages();
                }
            }, 60000); // Actualisation toutes les minutes
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) return `Il y a ${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)}h`;
            return `Il y a ${Math.floor(diffInMinutes / 1440)}j`;
        },

        getCodBadgeClass(amount) {
            if (amount >= 100) return 'bg-red-100 text-red-700';
            if (amount >= 50) return 'bg-orange-100 text-orange-700';
            return 'bg-green-100 text-green-700';
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
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
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection