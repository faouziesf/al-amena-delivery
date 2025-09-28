@extends('layouts.deliverer')

@section('title', 'Retours')

@section('content')
<div class="bg-gray-50" x-data="returnsApp()">
    
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Retours
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${packages.length} colis à retourner`"></span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-red-600">Vers expéditeurs</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="generateManifest()" 
                            x-show="packages.length > 0"
                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </button>
                    <button @click="toggleBulkMode()" 
                            x-show="packages.length > 0"
                            class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            :class="bulkMode ? 'bg-red-100 text-red-600' : 'bg-gray-100'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                        </svg>
                    </button>
                    <button @click="refreshData()" 
                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                            :class="loading ? 'opacity-50' : ''">
                        <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div x-show="bulkMode && selectedPackages.length > 0" x-transition 
                 class="mt-4 p-3 bg-red-50 rounded-xl border border-red-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-red-900" x-text="`${selectedPackages.length} colis sélectionnés`"></span>
                    <div class="flex space-x-2">
                        <button @click="bulkReturn()" 
                                class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                            Marquer Retournés
                        </button>
                        <button @click="bulkManifest()" 
                                class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors">
                            Générer Manifeste
                        </button>
                        <button @click="clearSelection()" 
                                class="text-gray-600 hover:text-gray-800 px-3 py-2 text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">📋 Retours Expéditeurs</p>
                        <p class="text-xs text-red-700">Ces colis doivent être retournés vers leurs expéditeurs. Générez un manifeste pour le traitement groupé.</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3" x-show="!bulkMode">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="applyFilters()"
                           placeholder="Rechercher par code, expéditeur..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun retour en attente</h3>
        <p class="text-gray-500 text-sm mb-4">Tous les colis ont été livrés avec succès ou aucun retour n'est nécessaire pour le moment.</p>
        <a href="{{ route('deliverer.deliveries.index') }}" 
           class="inline-block bg-red-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-red-700 transition-colors">
            Voir Livraisons
        </a>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-16">
        <div class="animate-spin w-8 h-8 border-4 border-red-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des retours...</p>
    </div>

    <!-- Packages List -->
    <div class="px-4 pb-6 space-y-4">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Bulk Selection Checkbox -->
                <div x-show="bulkMode" class="p-4 bg-red-50 border-b border-red-100">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" 
                               :value="package.id" 
                               x-model="selectedPackages"
                               class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <span class="text-sm font-medium text-red-900">Sélectionner ce retour</span>
                    </label>
                </div>

                <!-- Package Header -->
                <div class="p-4 bg-gradient-to-r from-red-50 to-pink-50 border-b border-red-100">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-red-900" x-text="package.package_code"></span>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-red-600 font-medium">À RETOURNER</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-600" x-text="formatAmount(package.cod_amount)"></div>
                            <span class="text-xs text-gray-500">COD (non collecté)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="getReturnReasonDate(package)"></span>
                        </div>
                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full" x-text="getReturnReason(package)">
                            3 tentatives échouées
                        </span>
                    </div>
                </div>

                <!-- Package Details -->
                <div class="p-4 space-y-3">
                    
                    <!-- Return Destination (Expéditeur) -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-bold text-blue-700 bg-blue-200 px-3 py-1 rounded-full">
                                        📦 RETOURNER VERS
                                    </span>
                                    <span class="text-sm font-medium text-blue-900" x-text="package.delegation_from.name"></span>
                                </div>
                                <p class="text-lg font-bold text-gray-900 mb-1" x-text="package.sender.name"></p>
                                <p class="text-sm text-gray-700 mb-2" x-text="package.sender_data.address"></p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-1 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <a :href="`tel:${package.sender_data.phone}`" 
                                           class="text-blue-600 font-medium hover:text-blue-700" 
                                           x-text="package.sender_data.phone"></a>
                                    </div>
                                    <button @click="openMaps(package.sender_data.address)" 
                                            class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors">
                                        📍 GPS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Delivery Info -->
                    <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 mb-1">Livraison échouée vers:</p>
                                <p class="text-sm text-red-700" x-text="package.recipient_data.name"></p>
                                <p class="text-xs text-red-600" x-text="package.recipient_data.address"></p>
                                <p class="text-xs text-red-600 mt-1" x-text="`Délégation: ${package.delegation_to.name}`"></p>
                                
                                <template x-if="package.delivery_attempts > 0">
                                    <div class="mt-2 flex items-center space-x-2">
                                        <span class="bg-red-200 text-red-800 text-xs px-2 py-1 rounded" x-text="`${package.delivery_attempts} tentatives`"></span>
                                        <span class="text-xs text-red-600">• Échec définitif</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Package Info -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <div>
                                <span class="text-gray-600">Contenu:</span>
                                <span class="font-medium text-gray-900 ml-1" x-text="package.content_description"></span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-500">
                                <span x-text="`Créé le: ${formatDate(package.created_at)}`"></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="`Frais retour: ${formatAmount(package.return_fee)}`"></div>
                        </div>
                        <template x-if="package.notes">
                            <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-xs font-medium text-amber-800">Notes originales:</p>
                                <p class="text-sm text-amber-700" x-text="package.notes"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 bg-gray-50 border-t" x-show="!bulkMode">
                    <div class="flex space-x-3">
                        <button @click="markReturned(package)" 
                                :disabled="package.processing" 
                                class="flex-1 bg-red-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!package.processing" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Marquer Retourné</span>
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
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Return Modal -->
    <div x-show="showReturnModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Confirmer Retour</h3>
                <button @click="showReturnModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-bold text-red-600" x-text="selectedPackageForReturn?.package_code"></p>
                    <p class="text-sm text-gray-600" x-text="'Retour vers: ' + selectedPackageForReturn?.sender?.name"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison du retour *</label>
                    <textarea x-model="returnReason" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              rows="3"
                              placeholder="3 tentatives échouées, client refuse, adresse introuvable..."
                              required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes supplémentaires</label>
                    <textarea x-model="returnNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              rows="2"
                              placeholder="État du colis, observations..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmReturn()" 
                            :disabled="processingReturn || !returnReason"
                            class="flex-1 bg-red-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-red-700 disabled:opacity-50">
                        <span x-show="!processingReturn">Confirmer Retour</span>
                        <span x-show="processingReturn" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showReturnModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manifest Modal -->
    <div x-show="showManifestModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Manifeste de Retours</h3>
                <button @click="showManifestModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-semibold text-red-800">Manifeste de Retours</span>
                    </div>
                    <p class="text-sm text-red-700" x-text="`${manifestPackages.length} colis à retourner`"></p>
                    <p class="text-xs text-red-600 mt-1">Date: <span x-text="new Date().toLocaleDateString('fr-FR')"></span></p>
                </div>

                <div class="max-h-60 overflow-y-auto space-y-2">
                    <template x-for="package in manifestPackages" :key="package.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="package.package_code"></p>
                                <p class="text-xs text-gray-600" x-text="package.sender.name"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500" x-text="package.delegation_from.name"></p>
                                <p class="text-xs text-green-600" x-text="formatAmount(package.return_fee)"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex space-x-3">
                    <button @click="downloadManifest()" 
                            class="flex-1 bg-red-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-red-700">
                        📄 Télécharger PDF
                    </button>
                    <button @click="printManifest()" 
                            class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300">
                        🖨️ Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Spacing -->
    <div class="h-20"></div>
</div>

<script>
function returnsApp() {
    return {
        packages: @json($packages->items()),
        loading: false,
        searchQuery: '',
        bulkMode: false,
        selectedPackages: [],
        
        // Return modal
        showReturnModal: false,
        selectedPackageForReturn: null,
        returnReason: '',
        returnNotes: '',
        processingReturn: false,
        
        // Manifest modal
        showManifestModal: false,
        manifestPackages: [],

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
            // Auto-refresh toutes les 60 secondes (moins critique que livraisons)
            setInterval(() => {
                this.refreshData();
            }, 60000);
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
            // Auto-applied via getter
        },

        markReturned(package) {
            this.selectedPackageForReturn = package;
            this.returnReason = this.getAutoReturnReason(package);
            this.returnNotes = '';
            this.showReturnModal = true;
        },

        getAutoReturnReason(package) {
            if (package.delivery_attempts >= 3) {
                return '3 tentatives de livraison échouées - Retour automatique';
            }
            return 'Retour demandé';
        },

        async confirmReturn() {
            if (!this.selectedPackageForReturn || !this.returnReason) return;
            
            this.processingReturn = true;
            
            try {
                const response = await fetch(`/deliverer/packages/${this.selectedPackageForReturn.id}/return`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        return_reason: this.returnReason,
                        return_notes: this.returnNotes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showReturnModal = false;
                    
                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackageForReturn.id);
                    
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur retour:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingReturn = false;
        },

        async bulkReturn() {
            if (this.selectedPackages.length === 0) return;
            
            this.loading = true;
            
            try {
                const response = await fetch('/deliverer/packages/bulk-return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        package_ids: this.selectedPackages,
                        return_reason: 'Retour groupé - Traitement par lot'
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
                console.error('Erreur bulk return:', error);
                this.showToast('Erreur lors du traitement groupé', 'error');
            }
            
            this.loading = false;
        },

        generateManifest() {
            this.manifestPackages = [...this.packages];
            this.showManifestModal = true;
        },

        bulkManifest() {
            if (this.selectedPackages.length === 0) return;
            
            this.manifestPackages = this.packages.filter(p => this.selectedPackages.includes(p.id));
            this.showManifestModal = true;
        },

        async downloadManifest() {
            try {
                const packageIds = this.manifestPackages.map(p => p.id);
                
                const response = await fetch('/deliverer/manifests/returns', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        package_ids: packageIds,
                        type: 'returns'
                    })
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `manifeste_retours_${new Date().toISOString().split('T')[0]}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    
                    this.showToast('Manifeste téléchargé avec succès');
                    this.showManifestModal = false;
                } else {
                    this.showToast('Erreur lors de la génération du manifeste', 'error');
                }
            } catch (error) {
                console.error('Erreur téléchargement manifeste:', error);
                this.showToast('Erreur de connexion', 'error');
            }
        },

        printManifest() {
            // Generate and print manifest
            const packageIds = this.manifestPackages.map(p => p.id);
            const printUrl = `/deliverer/manifests/returns/print?package_ids=${packageIds.join(',')}`;
            window.open(printUrl, '_blank');
            
            this.showManifestModal = false;
            this.showToast('Manifeste envoyé vers l\'imprimante');
        },

        viewPackageDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        openMaps(address) {
            const encodedAddress = encodeURIComponent(address);
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_system');
            } else {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
            }
        },

        getReturnReason(package) {
            if (package.delivery_attempts >= 3) {
                return '3 tentatives échouées';
            }
            if (package.status === 'VERIFIED') {
                return 'À vérifier → Retour';
            }
            return 'Retour demandé';
        },

        getReturnReasonDate(package) {
            // Show when package became eligible for return
            if (package.last_attempt_date) {
                return 'Dernière tentative: ' + this.formatDate(package.last_attempt_date);
            }
            return 'Mis en retour: ' + this.formatDate(package.updated_at);
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
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