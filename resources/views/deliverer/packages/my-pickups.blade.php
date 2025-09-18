@extends('layouts.deliverer')

@section('title', 'Mes Pickups')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="myPickupsApp()">
    
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Mes Pickups
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${packages.length} colis acceptés`"></span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-purple-600">Mes colis</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="toggleBulkMode()" 
                            class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            :class="bulkMode ? 'bg-purple-100 text-purple-600' : 'bg-gray-100'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                        </svg>
                    </button>
                    <button @click="refreshData()" 
                            class="p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors"
                            :class="loading ? 'opacity-50' : ''">
                        <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div x-show="bulkMode && selectedPackages.length > 0" x-transition 
                 class="mt-4 p-3 bg-purple-50 rounded-xl border border-purple-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-purple-900" x-text="`${selectedPackages.length} colis sélectionnés`"></span>
                    <div class="flex space-x-2">
                        <button @click="bulkPickup()" 
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                            Marquer Collectés
                        </button>
                        <button @click="clearSelection()" 
                                class="text-gray-600 hover:text-gray-800 px-3 py-2 text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3" x-show="!bulkMode">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="applyFilters()"
                           placeholder="Rechercher par code, nom..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="packages.length === 0 && !loading" 
         class="text-center py-16 px-4">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun pickup accepté</h3>
        <p class="text-gray-500 text-sm mb-4">Rendez-vous dans "Pickups Disponibles" pour accepter des colis à collecter.</p>
        <a href="{{ route('deliverer.pickups.available') }}" 
           class="inline-block bg-purple-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-purple-700 transition-colors">
            Voir Pickups Disponibles
        </a>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-16">
        <div class="animate-spin w-8 h-8 border-4 border-purple-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des pickups...</p>
    </div>

    <!-- Packages List -->
    <div class="px-4 pb-6 space-y-4">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Bulk Selection Checkbox -->
                <div x-show="bulkMode" class="p-4 bg-purple-50 border-b border-purple-100">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" 
                               :value="package.id" 
                               x-model="selectedPackages"
                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm font-medium text-purple-900">Sélectionner ce colis</span>
                    </label>
                </div>

                <!-- Package Header -->
                <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-purple-900" x-text="package.package_code"></span>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-purple-600 font-medium">ACCEPTÉ</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-emerald-600" x-text="formatAmount(package.cod_amount)"></div>
                            <span class="text-xs text-gray-500">COD</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="formatDate(package.assigned_at)"></span>
                        </div>
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                            À collecter chez expéditeur
                        </span>
                    </div>
                </div>

                <!-- Package Details -->
                <div class="p-4 space-y-3">
                    
                    <!-- Pickup Location (Priorité) -->
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 p-4 rounded-xl border border-orange-200">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-bold text-orange-700 bg-orange-200 px-3 py-1 rounded-full">
                                        🎯 COLLECTE ICI
                                    </span>
                                    <span class="text-sm font-medium text-orange-900" x-text="package.delegation_from.name"></span>
                                </div>
                                <p class="text-lg font-bold text-gray-900 mb-1" x-text="package.sender.name"></p>
                                <p class="text-sm text-gray-700" x-text="package.pickup_address || package.sender_data.address"></p>
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex items-center space-x-1 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <a :href="`tel:${package.pickup_phone || package.sender_data.phone}`" 
                                           class="text-orange-600 font-medium hover:text-orange-700" 
                                           x-text="package.pickup_phone || package.sender_data.phone"></a>
                                    </div>
                                    <button @click="openMaps(package.pickup_address || package.sender_data.address)" 
                                            class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-sm font-medium hover:bg-orange-200 transition-colors">
                                        📍 GPS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info (Plus petit) -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-medium text-gray-500">LIVRAISON FINALE</span>
                                    <span class="text-sm text-gray-700" x-text="package.delegation_to.name"></span>
                                </div>
                                <p class="text-sm text-gray-800" x-text="package.recipient_data.name"></p>
                                <p class="text-xs text-gray-600" x-text="package.recipient_data.address"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Package Info -->
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <span class="text-gray-600">Contenu:</span>
                                <span class="font-medium text-gray-900 ml-1" x-text="package.content_description"></span>
                            </div>
                        </div>
                        <template x-if="package.pickup_notes">
                            <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-amber-800">Notes pickup importantes:</p>
                                        <p class="text-sm text-amber-700" x-text="package.pickup_notes"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-purple-200">
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                <span x-text="`Livraison: ${formatAmount(package.delivery_fee)}`"></span>
                                <span x-text="`Retour: ${formatAmount(package.return_fee)}`"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 bg-gray-50 border-t" x-show="!bulkMode">
                    <div class="flex space-x-3">
                        <button @click="markPickedUp(package)" 
                                :disabled="package.processing" 
                                class="flex-1 bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!package.processing" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Marquer Collecté</span>
                            </span>
                            <span x-show="package.processing" class="flex items-center justify-center space-x-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Traitement...</span>
                            </span>
                        </button>
                        
                        <button @click="viewPackageDetails(package)" 
                                class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>

                        <button @click="transferPackage(package)" 
                                class="px-4 py-3 bg-blue-100 text-blue-700 rounded-xl hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Pickup Notes Modal -->
    <div x-show="showPickupModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Marquer comme Collecté</h3>
                <button @click="showPickupModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Colis à marquer</label>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-bold text-purple-600" x-text="selectedPackageForPickup?.package_code"></p>
                        <p class="text-sm text-gray-600" x-text="selectedPackageForPickup?.sender?.name"></p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de collecte (optionnel)</label>
                    <textarea x-model="pickupNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                              rows="3"
                              placeholder="État du colis, commentaires..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmPickup()" 
                            :disabled="processingPickup"
                            class="flex-1 bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 disabled:opacity-50">
                        <span x-show="!processingPickup">Confirmer Collecte</span>
                        <span x-show="processingPickup" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showPickupModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Spacing -->
    <div class="h-20"></div>
</div>

<script>
function myPickupsApp() {
    return {
        packages: @json($packages->items()),
        loading: false,
        searchQuery: '',
        bulkMode: false,
        selectedPackages: [],
        showPickupModal: false,
        selectedPackageForPickup: null,
        pickupNotes: '',
        processingPickup: false,

        get filteredPackages() {
            let filtered = [...this.packages];
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pkg => 
                    pkg.package_code.toLowerCase().includes(query) ||
                    pkg.sender.name.toLowerCase().includes(query) ||
                    pkg.recipient_data.name.toLowerCase().includes(query)
                );
            }
            
            return filtered;
        },

        init() {
            // Auto-refresh toutes les 30 secondes
            setInterval(() => {
                this.refreshData();
            }, 30000);
        },

        toggleBulkMode() {
            this.bulkMode = !this.bulkMode;
            if (!this.bulkMode) {
                this.selectedPackages = [];
            }
        },

        clearSelection() {
            this.selectedPackages = [];
            this.bulkMode = false;
        },

        async refreshData() {
            this.loading = true;
            
            try {
                const response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Erreur refresh:', error);
                this.showToast('Erreur lors du rafraîchissement', 'error');
            }
            
            this.loading = false;
        },

        applyFilters() {
            // Les filtres se mettent à jour automatiquement via le getter
        },

        markPickedUp(package) {
            this.selectedPackageForPickup = package;
            this.pickupNotes = '';
            this.showPickupModal = true;
        },

        async confirmPickup() {
            if (!this.selectedPackageForPickup) return;
            
            this.processingPickup = true;
            
            try {
                const formData = new FormData();
                formData.append('pickup_notes', this.pickupNotes);
                
                const response = await fetch(`/deliverer/packages/${this.selectedPackageForPickup.id}/pickup`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showPickupModal = false;
                    
                    // Retirer le colis de la liste (maintenant dans "Livraisons")
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackageForPickup.id);
                    
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur pickup:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingPickup = false;
        },

        async bulkPickup() {
            if (this.selectedPackages.length === 0) return;
            
            this.loading = true;
            
            try {
                const response = await fetch('/deliverer/packages/bulk-pickup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        package_ids: this.selectedPackages
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    
                    // Retirer les colis traités de la liste
                    this.packages = this.packages.filter(p => !this.selectedPackages.includes(p.id));
                    this.clearSelection();
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur bulk pickup:', error);
                this.showToast('Erreur lors du traitement groupé', 'error');
            }
            
            this.loading = false;
        },

        viewPackageDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        transferPackage(package) {
            // TODO: Implement transfer functionality (scan other deliverer code)
            this.showToast('Fonction de transfert - À implémenter');
        },

        openMaps(address) {
            const encodedAddress = encodeURIComponent(address);
            // Try Google Maps first, fallback to generic maps
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                // On mobile, use native maps app
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_system');
            } else {
                // On desktop, open in new tab
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
            }
        },

        formatAmount(amount) {
            return parseFloat(amount).toFixed(3) + ' DT';
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffMinutes < 1) return 'À l\'instant';
            if (diffMinutes < 60) return `Il y a ${diffMinutes}min`;
            
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `Il y a ${diffHours}h`;
            
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
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
            }, 4000);
        }
    }
}
</script>
@endsection