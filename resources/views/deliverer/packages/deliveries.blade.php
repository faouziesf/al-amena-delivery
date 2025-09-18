@extends('layouts.deliverer')

@section('title', 'Livraisons')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="deliveriesApp()">
    
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Livraisons
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${packages.length} colis à livrer`"></span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-orange-600">COD obligatoire</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="toggleBulkMode()" 
                            class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            :class="bulkMode ? 'bg-orange-100 text-orange-600' : 'bg-gray-100'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                        </svg>
                    </button>
                    <button @click="refreshData()" 
                            class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-200 transition-colors"
                            :class="loading ? 'opacity-50' : ''">
                        <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- COD Warning Banner -->
            <div class="mt-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">⚠️ COD OBLIGATOIRE ET EXACT</p>
                        <p class="text-xs text-amber-700">Le montant COD doit être collecté EXACTEMENT comme affiché. Si problème → Appeler Commercial</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="applyFilters()"
                           placeholder="Rechercher par code, nom, téléphone..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune livraison en cours</h3>
        <p class="text-gray-500 text-sm mb-4">Tous les colis ont été livrés ou vous n'avez pas de colis collectés.</p>
        <a href="{{ route('deliverer.pickups.mine') }}" 
           class="inline-block bg-orange-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-orange-700 transition-colors">
            Voir Mes Pickups
        </a>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-16">
        <div class="animate-spin w-8 h-8 border-4 border-orange-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des livraisons...</p>
    </div>

    <!-- Packages List -->
    <div class="px-4 pb-6 space-y-4">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Package Header -->
                <div class="p-4 bg-gradient-to-r from-orange-50 to-red-50 border-b border-orange-100">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-orange-900" x-text="package.package_code"></span>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-orange-600 font-medium">À LIVRER</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-bold text-emerald-600 flex items-center">
                                <span x-text="formatAmount(package.cod_amount)"></span>
                                <svg class="w-5 h-5 ml-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-red-600 font-medium">COD EXACT</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="formatDate(package.picked_up_at)"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                Collecté - À livrer
                            </span>
                            <template x-if="package.delivery_attempts > 0">
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full" 
                                      x-text="`${package.delivery_attempts} tentative(s)`"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Package Details -->
                <div class="p-4 space-y-3">
                    
                    <!-- Delivery Address (Priorité) -->
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-4 rounded-xl border-2 border-emerald-200">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-bold text-emerald-700 bg-emerald-200 px-3 py-1 rounded-full">
                                        🚚 LIVRER ICI
                                    </span>
                                    <span class="text-sm font-medium text-emerald-900" x-text="package.delegation_to.name"></span>
                                </div>
                                <p class="text-lg font-bold text-gray-900 mb-1" x-text="package.recipient_data.name"></p>
                                <p class="text-sm text-gray-700 mb-2" x-text="package.recipient_data.address"></p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-1 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <a :href="`tel:${package.recipient_data.phone}`" 
                                           class="text-emerald-600 font-medium hover:text-emerald-700" 
                                           x-text="package.recipient_data.phone"></a>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="openMaps(package.recipient_data.address)" 
                                                class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-sm font-medium hover:bg-emerald-200 transition-colors">
                                            📍 GPS
                                        </button>
                                        <button @click="callCommercial(package)" 
                                                class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors">
                                            📞 Commercial
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COD Collection Info -->
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-4 rounded-xl border-2 border-amber-300">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-amber-800">MONTANT À COLLECTER</p>
                                <p class="text-2xl font-bold text-amber-900" x-text="formatAmount(package.cod_amount)"></p>
                            </div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-bold text-red-800">IMPORTANT:</p>
                                    <p class="text-xs text-red-700">Vous DEVEZ collecter EXACTEMENT ce montant. Aucune modification autorisée. Si problème → Appeler Commercial immédiatement.</p>
                                </div>
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
                            <div class="text-gray-600">
                                <span>Expéditeur: </span>
                                <span class="text-gray-900" x-text="package.sender.name"></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="`${package.delegation_from.name} → ${package.delegation_to.name}`"></div>
                        </div>
                        <template x-if="package.special_instructions || package.is_fragile || package.requires_signature">
                            <div class="mt-2 p-2 bg-purple-50 border border-purple-200 rounded-lg">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-purple-800">Instructions spéciales:</p>
                                        <template x-if="package.is_fragile">
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded mr-1">Fragile</span>
                                        </template>
                                        <template x-if="package.requires_signature">
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1">Signature requise</span>
                                        </template>
                                        <template x-if="package.special_instructions">
                                            <p class="text-xs text-purple-700 mt-1" x-text="package.special_instructions"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 bg-gray-50 border-t">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Primary Action: Deliver -->
                        <button @click="markDelivered(package)" 
                                :disabled="package.processing" 
                                class="col-span-2 bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!package.processing" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Marquer Livré + Collecter COD</span>
                            </span>
                            <span x-show="package.processing" class="flex items-center justify-center space-x-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Traitement...</span>
                            </span>
                        </button>
                        
                        <!-- Secondary Actions -->
                        <button @click="markUnavailable(package)" 
                                class="bg-yellow-100 text-yellow-700 py-2 px-3 rounded-xl font-medium hover:bg-yellow-200 transition-colors">
                            Client Non Dispo
                        </button>
                        
                        <button @click="viewPackageDetails(package)" 
                                class="bg-gray-200 text-gray-700 py-2 px-3 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Delivery Modal with COD Collection -->
    <div x-show="showDeliveryModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Confirmer Livraison + COD</h3>
                <button @click="showDeliveryModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Package Info -->
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-bold text-orange-600" x-text="selectedPackageForDelivery?.package_code"></p>
                    <p class="text-sm text-gray-600" x-text="selectedPackageForDelivery?.recipient_data.name"></p>
                </div>

                <!-- COD Amount (Most Important) -->
                <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 border-2 border-emerald-300 rounded-xl">
                    <p class="text-sm font-bold text-emerald-800 mb-2">MONTANT COD À COLLECTER:</p>
                    <p class="text-3xl font-bold text-emerald-900" x-text="selectedPackageForDelivery ? formatAmount(selectedPackageForDelivery.cod_amount) : ''"></p>
                    <p class="text-xs text-red-600 font-medium mt-1">⚠️ Montant EXACT obligatoire</p>
                </div>
                
                <!-- COD Collection Input -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Montant COD collecté (DT) *
                        <span class="text-red-600">- DOIT ÊTRE EXACTEMENT LE MONTANT CI-DESSUS</span>
                    </label>
                    <input type="number" 
                           x-model="codCollected" 
                           step="0.001" 
                           min="0"
                           :placeholder="selectedPackageForDelivery ? selectedPackageForDelivery.cod_amount : '0.000'"
                           class="w-full px-3 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           required>
                    <template x-if="codCollected && selectedPackageForDelivery && Math.abs(parseFloat(codCollected) - parseFloat(selectedPackageForDelivery.cod_amount)) > 0.001">
                        <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm font-bold text-red-800">❌ ERREUR DE MONTANT</p>
                            <p class="text-xs text-red-700">
                                Attendu: <span x-text="formatAmount(selectedPackageForDelivery.cod_amount)"></span><br>
                                Saisi: <span x-text="formatAmount(codCollected || 0)"></span>
                            </p>
                        </div>
                    </template>
                </div>
                
                <!-- Delivery Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison (optionnel)</label>
                    <textarea x-model="deliveryNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                              rows="3"
                              placeholder="État de remise, commentaires client..."></textarea>
                </div>

                <!-- Signature -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature récepteur</label>
                    <div class="w-full h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-500 text-sm">
                        Signature pad (à implémenter)
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmDelivery()" 
                            :disabled="processingDelivery || !codCollected || !selectedPackageForDelivery || Math.abs(parseFloat(codCollected) - parseFloat(selectedPackageForDelivery.cod_amount)) > 0.001"
                            class="flex-1 bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processingDelivery">Confirmer Livraison</span>
                        <span x-show="processingDelivery" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showDeliveryModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unavailable Modal -->
    <div x-show="showUnavailableModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Client Non Disponible</h3>
                <button @click="showUnavailableModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-bold text-orange-600" x-text="selectedPackageForUnavailable?.package_code"></p>
                    <p class="text-sm text-gray-600" x-text="selectedPackageForUnavailable?.recipient_data.name"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison *</label>
                    <select x-model="unavailableReason" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Sélectionner une raison</option>
                        <option value="RECIPIENT_UNAVAILABLE">Destinataire absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="RECIPIENT_REFUSED">Destinataire refuse</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Détails de la tentative *</label>
                    <textarea x-model="unavailableNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                              rows="3"
                              placeholder="Détails de la situation..."
                              required></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmUnavailable()" 
                            :disabled="processingUnavailable || !unavailableReason || !unavailableNotes"
                            class="flex-1 bg-yellow-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-yellow-700 disabled:opacity-50">
                        <span x-show="!processingUnavailable">Enregistrer Tentative</span>
                        <span x-show="processingUnavailable" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showUnavailableModal = false" 
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
function deliveriesApp() {
    return {
        packages: @json($packages->items()),
        loading: false,
        searchQuery: '',
        bulkMode: false,
        
        // Delivery modal
        showDeliveryModal: false,
        selectedPackageForDelivery: null,
        codCollected: '',
        deliveryNotes: '',
        processingDelivery: false,
        
        // Unavailable modal
        showUnavailableModal: false,
        selectedPackageForUnavailable: null,
        unavailableReason: '',
        unavailableNotes: '',
        processingUnavailable: false,

        get filteredPackages() {
            let filtered = [...this.packages];
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pkg => 
                    pkg.package_code.toLowerCase().includes(query) ||
                    pkg.recipient_data.name.toLowerCase().includes(query) ||
                    pkg.recipient_data.phone.includes(query) ||
                    pkg.sender.name.toLowerCase().includes(query)
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

        markDelivered(package) {
            this.selectedPackageForDelivery = package;
            this.codCollected = package.cod_amount; // Pre-fill with expected amount
            this.deliveryNotes = '';
            this.showDeliveryModal = true;
        },

        async confirmDelivery() {
            if (!this.selectedPackageForDelivery || !this.codCollected) return;
            
            // Vérification COD exact
            const expectedCod = parseFloat(this.selectedPackageForDelivery.cod_amount);
            const collectedCod = parseFloat(this.codCollected);
            
            if (Math.abs(collectedCod - expectedCod) > 0.001) {
                this.showToast(`Erreur COD: Attendu ${this.formatAmount(expectedCod)}, saisi ${this.formatAmount(collectedCod)}`, 'error');
                return;
            }
            
            this.processingDelivery = true;
            
            try {
                const formData = new FormData();
                formData.append('cod_collected', this.codCollected);
                formData.append('delivery_notes', this.deliveryNotes);
                
                const response = await fetch(`/deliverer/packages/${this.selectedPackageForDelivery.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showDeliveryModal = false;
                    
                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackageForDelivery.id);
                    
                    // Show COD added message
                    setTimeout(() => {
                        this.showToast(`COD ${this.formatAmount(collectedCod)} ajouté à votre wallet!`, 'success');
                    }, 1500);
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur livraison:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingDelivery = false;
        },

        markUnavailable(package) {
            this.selectedPackageForUnavailable = package;
            this.unavailableReason = '';
            this.unavailableNotes = '';
            this.showUnavailableModal = true;
        },

        async confirmUnavailable() {
            if (!this.selectedPackageForUnavailable || !this.unavailableReason || !this.unavailableNotes) return;
            
            this.processingUnavailable = true;
            
            try {
                const response = await fetch(`/deliverer/packages/${this.selectedPackageForUnavailable.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        unavailable_reason: this.unavailableReason,
                        attempt_notes: this.unavailableNotes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showUnavailableModal = false;
                    
                    // Update package attempts count
                    const packageIndex = this.packages.findIndex(p => p.id === this.selectedPackageForUnavailable.id);
                    if (packageIndex !== -1) {
                        this.packages[packageIndex].delivery_attempts++;
                    }
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur tentative:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingUnavailable = false;
        },

        viewPackageDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        callCommercial(package) {
            // Call commercial for this package - could open phone or special contact form
            this.showToast(`Contacter commercial pour colis ${package.package_code}`, 'info');
            // window.location.href = 'tel:+21670123456'; // Replace with actual commercial number
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

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            
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
            const bgColor = type === 'success' ? 'bg-emerald-500' : 
                            type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : type === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, type === 'success' ? 4000 : 6000);
        }
    }
}
</script>
@endsection