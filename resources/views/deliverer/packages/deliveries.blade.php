@extends('layouts.deliverer')

@section('title', 'Livraisons')

@section('content')
<div x-data="deliveriesApp()" x-init="init()">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Mes Livraisons</h1>
                    <p class="text-sm text-gray-600">
                        <span x-text="packages.length"></span> colis à livrer 
                        <span x-show="urgentCount > 0" class="text-red-600 font-medium">
                            (dont <span x-text="urgentCount"></span> urgent(s))
                        </span>
                    </p>
                </div>
                
                <!-- Actions rapides -->
                <div class="flex items-center space-x-2">
                    <!-- Scanner -->
                    <button @click="openScanner()" 
                            class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>
                    
                    <!-- Navigation -->
                    <button @click="showMap = !showMap"
                            class="bg-blue-500 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filtres et tri -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-x-auto">
                    <button @click="filterBy = ''; applyFilters()" 
                            :class="filterBy === '' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        Tous
                    </button>
                    <button @click="filterBy = 'urgent'; applyFilters()" 
                            :class="filterBy === 'urgent' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        ⚠️ Urgents
                    </button>
                    <button @click="filterBy = 'high_cod'; applyFilters()" 
                            :class="filterBy === 'high_cod' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        💰 COD Élevé
                    </button>
                    <button @click="filterBy = 'nearby'; applyFilters()" 
                            :class="filterBy === 'nearby' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        📍 Proches
                    </button>
                </div>
                
                <div class="text-sm text-gray-500">
                    Total COD: <span class="font-bold text-orange-600" x-text="totalCod.toFixed(3) + ' DT'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
    </div>

    <!-- Liste des livraisons -->
    <div x-show="!loading" class="space-y-3 px-4">
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
                 :class="getPackagePriorityClass(package)">
                
                <!-- Header avec priorité -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100"
                     :class="getHeaderBackgroundClass(package)">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="getPackageStatusText(package)"></span>
                                <!-- Compteur de tentatives -->
                                <span x-show="package.delivery_attempts > 0" 
                                      class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                    Tentative <span x-text="package.delivery_attempts + 1"></span>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- COD et priorité -->
                    <div class="text-right">
                        <!-- Badge de priorité -->
                        <div class="flex items-center space-x-2 mb-1">
                            <span x-show="isUrgentPackage(package)" 
                                  class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full animate-pulse">
                                URGENT
                            </span>
                            <span x-show="is4thAttempt(package)" 
                                  class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                4ème TENTATIVE
                            </span>
                        </div>
                        <!-- COD -->
                        <p class="text-lg font-bold text-orange-600" x-text="formatAmount(package.cod_amount)"></p>
                    </div>
                </div>

                <!-- Informations client -->
                <div class="p-4">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-xl border-l-4 border-green-400 mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-green-800">🎯 Client à livrer</h4>
                                    <span class="text-xs text-green-600" x-text="package.delegation_to?.name"></span>
                                </div>
                                <p class="font-bold text-gray-900 text-lg" x-text="package.recipient_data?.name"></p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <a :href="'tel:' + (package.recipient_data?.phone || '')" 
                                       class="flex items-center space-x-1 text-green-700 hover:text-green-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span x-text="package.recipient_data?.phone"></span>
                                    </a>
                                    <button @click="openMaps(package)" 
                                            class="flex items-center space-x-1 text-blue-700 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        <span class="text-xs">Navigation</span>
                                    </button>
                                </div>
                                <p class="text-sm text-gray-700 mt-2" x-text="package.recipient_data?.address"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du colis -->
                    <div class="bg-gray-50 p-3 rounded-xl mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Contenu</span>
                            <span class="text-xs text-gray-500">De: <span x-text="package.sender_data?.name || 'N/A'"></span></span>
                        </div>
                        <p class="text-sm text-gray-900" x-text="package.content_description"></p>
                        
                        <!-- Notes spéciales -->
                        <div x-show="package.notes" class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-600">
                                <span class="font-medium">📝 Notes:</span> 
                                <span x-text="package.notes"></span>
                            </p>
                        </div>
                        
                        <!-- Instructions spéciales -->
                        <div x-show="package.special_instructions" class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-amber-700 bg-amber-50 p-2 rounded">
                                <span class="font-medium">⚠️ Instructions:</span> 
                                <span x-text="package.special_instructions"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions principales -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Livrer avec COD -->
                        <button @click="startDelivery(package)" 
                                :disabled="processing === package.id"
                                class="flex flex-col items-center justify-center p-4 bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50">
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="processing !== package.id">Livrer</span>
                            <span x-show="processing === package.id" class="flex items-center">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-1"></div>
                                Livraison...
                            </span>
                        </button>

                        <!-- Client non disponible -->
                        <button @click="markUnavailable(package)" 
                                :disabled="processing === package.id"
                                class="flex flex-col items-center justify-center p-4 bg-yellow-500 text-white rounded-xl font-semibold hover:bg-yellow-600 transition-all disabled:opacity-50">
                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Non Dispo</span>
                        </button>

                        <!-- Scanner ce colis -->
                        <button @click="scanPackage(package.package_code)" 
                                class="flex items-center justify-center p-3 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                            </svg>
                            Scanner
                        </button>

                        <!-- Contact Commercial -->
                        <button @click="contactCommercial(package)" 
                                class="flex items-center justify-center p-3 bg-purple-100 text-purple-600 rounded-xl hover:bg-purple-200 transition-colors">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Commercial
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                <span x-show="filterBy === ''">Aucune livraison en attente</span>
                <span x-show="filterBy !== ''">Aucun résultat pour ce filtre</span>
            </h3>
            <p class="text-gray-600 mb-6">
                <span x-show="filterBy === ''">Toutes vos livraisons sont terminées !</span>
                <span x-show="filterBy !== ''">Essayez un autre filtre ou réinitialisez la recherche.</span>
            </p>
            <button @click="filterBy = ''; applyFilters()" x-show="filterBy !== ''"
                    class="bg-orange-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-orange-600 transition-colors">
                Voir toutes les livraisons
            </button>
        </div>
    </div>
</div>

<!-- Modal Livraison avec COD -->
<div x-show="showDeliveryModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
    <div x-show="showDeliveryModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
         x-transition:enter-end="translate-y-0 sm:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0"
         class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-md max-h-96 overflow-y-auto">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Livraison avec COD</h3>
            <button @click="showDeliveryModal = false" 
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="confirmDelivery()">
            <!-- Package info -->
            <div class="bg-orange-50 p-4 rounded-xl mb-4">
                <p class="font-bold text-orange-900" x-text="selectedPackage?.package_code"></p>
                <p class="text-sm text-orange-700" x-text="selectedPackage?.recipient_data?.name"></p>
                <p class="text-lg font-bold text-emerald-600 mt-2">
                    COD à collecter: <span x-text="formatAmount(selectedPackage?.cod_amount)"></span>
                </p>
            </div>

            <!-- COD EXACT requis -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Montant COD collecté (DT) <span class="text-red-500">*</span>
                </label>
                <input type="number" x-model="deliveryForm.cod_collected" step="0.001" min="0"
                       :placeholder="selectedPackage?.cod_amount"
                       class="w-full border border-gray-300 rounded-xl px-3 py-3 text-lg font-bold text-center focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       required>
                <div class="flex justify-center mt-2">
                    <button type="button" @click="deliveryForm.cod_collected = selectedPackage?.cod_amount"
                            class="text-sm bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg hover:bg-emerald-200 transition-colors">
                        Utiliser le montant exact: <span x-text="formatAmount(selectedPackage?.cod_amount)"></span>
                    </button>
                </div>
                <p class="text-xs text-red-600 mt-1">⚠️ Le montant doit être EXACT. Contactez le commercial si problème.</p>
            </div>

            <!-- Nom du récepteur -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du récepteur <span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="deliveryForm.recipient_name"
                       :placeholder="selectedPackage?.recipient_data?.name"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       required>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison (optionnel)</label>
                <textarea x-model="deliveryForm.notes" rows="3" 
                          placeholder="État du colis, conditions de livraison..."
                          class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
            </div>

            <!-- Photo -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Photo de livraison (recommandé)</label>
                <input type="file" @change="handleDeliveryPhoto" accept="image/*" capture="environment"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-gray-500 mt-1">Preuve de livraison pour votre protection</p>
            </div>

            <!-- Warning COD -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                <div class="flex items-start space-x-2">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div class="text-sm text-red-700">
                        <p class="font-medium">Important :</p>
                        <p>Vous devez collecter EXACTEMENT <span class="font-bold" x-text="formatAmount(selectedPackage?.cod_amount)"></span> en espèces.</p>
                        <p>Si le client n'a pas le montant exact, contactez le commercial.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="submit" 
                        :disabled="processingDelivery"
                        class="flex-1 bg-emerald-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                    <span x-show="!processingDelivery">Confirmer Livraison</span>
                    <span x-show="processingDelivery" class="flex items-center justify-center">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        Livraison...
                    </span>
                </button>
                <button type="button" @click="showDeliveryModal = false" 
                        class="bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Client Non Disponible -->
<div x-show="showUnavailableModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
    <div x-show="showUnavailableModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
         x-transition:enter-end="translate-y-0 sm:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0"
         class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-md">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Client Non Disponible</h3>
            <button @click="showUnavailableModal = false" 
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="confirmUnavailable()">
            <!-- Package info -->
            <div class="bg-yellow-50 p-4 rounded-xl mb-4">
                <p class="font-bold text-yellow-900" x-text="selectedPackage?.package_code"></p>
                <p class="text-sm text-yellow-700" x-text="selectedPackage?.recipient_data?.name"></p>
                <p class="text-xs text-yellow-600 mt-1">
                    Tentative <span x-text="(selectedPackage?.delivery_attempts || 0) + 1"></span>/3
                </p>
            </div>

            <!-- Raison -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Raison <span class="text-red-500">*</span></label>
                <select x-model="unavailableForm.reason" required 
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    <option value="">Sélectionner une raison...</option>
                    <option value="CLIENT_ABSENT">Client absent</option>
                    <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                    <option value="CLIENT_REFUSES">Client refuse la livraison</option>
                    <option value="PHONE_OFF">Téléphone éteint</option>
                    <option value="OTHER">Autre</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Détails de la tentative <span class="text-red-500">*</span>
                </label>
                <textarea x-model="unavailableForm.notes" rows="3" required
                          placeholder="Décrivez ce qui s'est passé lors de cette tentative..."
                          class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="submit" 
                        :disabled="processingUnavailable"
                        class="flex-1 bg-yellow-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-yellow-600 transition-colors disabled:opacity-50">
                    <span x-show="!processingUnavailable">Confirmer</span>
                    <span x-show="processingUnavailable" class="flex items-center justify-center">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        Enregistrement...
                    </span>
                </button>
                <button type="button" @click="showUnavailableModal = false" 
                        class="bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function deliveriesApp() {
    return {
        packages: @json($packages->items()),
        filteredPackages: [],
        loading: false,
        processing: null,
        filterBy: '',
        totalCod: 0,
        urgentCount: 0,
        
        // Modals
        showDeliveryModal: false,
        showUnavailableModal: false,
        selectedPackage: null,
        processingDelivery: false,
        processingUnavailable: false,
        
        // Forms
        deliveryForm: {
            cod_collected: '',
            recipient_name: '',
            notes: '',
            photo: null
        },
        unavailableForm: {
            reason: '',
            notes: ''
        },

        init() {
            this.applyFilters();
            this.calculateStats();
            this.startAutoRefresh();
        },

        applyFilters() {
            let filtered = [...this.packages];
            
            switch (this.filterBy) {
                case 'urgent':
                    filtered = filtered.filter(p => this.isUrgentPackage(p) || this.is4thAttempt(p));
                    break;
                case 'high_cod':
                    filtered = filtered.filter(p => p.cod_amount >= 50);
                    break;
                case 'nearby':
                    // Implémentation future avec géolocalisation
                    break;
            }
            
            // Trier par priorité : urgents en premier
            filtered.sort((a, b) => {
                const aUrgent = this.isUrgentPackage(a) || this.is4thAttempt(a);
                const bUrgent = this.isUrgentPackage(b) || this.is4thAttempt(b);
                
                if (aUrgent && !bUrgent) return -1;
                if (!aUrgent && bUrgent) return 1;
                
                // Si même priorité, trier par COD décroissant
                return b.cod_amount - a.cod_amount;
            });
            
            this.filteredPackages = filtered;
        },

        calculateStats() {
            this.totalCod = this.packages.reduce((sum, pkg) => sum + parseFloat(pkg.cod_amount || 0), 0);
            this.urgentCount = this.packages.filter(p => this.isUrgentPackage(p) || this.is4thAttempt(p)).length;
        },

        async startDelivery(package) {
            this.selectedPackage = package;
            this.deliveryForm = {
                cod_collected: package.cod_amount,
                recipient_name: package.recipient_data?.name || '',
                notes: '',
                photo: null
            };
            this.showDeliveryModal = true;
        },

        async confirmDelivery() {
            if (!this.selectedPackage) return;
            
            // Vérifier COD exact
            const expectedCod = parseFloat(this.selectedPackage.cod_amount);
            const collectedCod = parseFloat(this.deliveryForm.cod_collected);
            
            if (Math.abs(expectedCod - collectedCod) > 0.001) {
                this.showToast(`COD incorrect ! Attendu: ${expectedCod.toFixed(3)} DT, Saisi: ${collectedCod.toFixed(3)} DT`, 'error');
                return;
            }
            
            this.processingDelivery = true;
            try {
                const formData = new FormData();
                formData.append('cod_collected', this.deliveryForm.cod_collected);
                formData.append('recipient_name', this.deliveryForm.recipient_name);
                formData.append('delivery_notes', this.deliveryForm.notes);
                if (this.deliveryForm.photo) {
                    formData.append('delivery_photo', this.deliveryForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.selectedPackage.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackage.id);
                    this.applyFilters();
                    this.calculateStats();
                    this.showDeliveryModal = false;
                    this.resetDeliveryForm();
                } else {
                    this.showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            } catch (error) {
                console.error('Erreur livraison:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.processingDelivery = false;
        },

        async markUnavailable(package) {
            this.selectedPackage = package;
            this.unavailableForm = {
                reason: '',
                notes: ''
            };
            this.showUnavailableModal = true;
        },

        async confirmUnavailable() {
            if (!this.selectedPackage) return;
            
            this.processingUnavailable = true;
            try {
                const response = await fetch(`/deliverer/packages/${this.selectedPackage.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        reason: this.unavailableForm.reason,
                        attempt_notes: this.unavailableForm.notes
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    
                    // Mettre à jour le package ou le retirer s'il passe en VERIFIED
                    if (data.status === 'VERIFIED') {
                        this.packages = this.packages.filter(p => p.id !== this.selectedPackage.id);
                    } else {
                        // Mettre à jour le compteur de tentatives
                        const pkgIndex = this.packages.findIndex(p => p.id === this.selectedPackage.id);
                        if (pkgIndex !== -1) {
                            this.packages[pkgIndex].delivery_attempts = data.attempt_count;
                            this.packages[pkgIndex].status = data.status;
                        }
                    }
                    
                    this.applyFilters();
                    this.calculateStats();
                    this.showUnavailableModal = false;
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
                }
            } catch (error) {
                console.error('Erreur tentative:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.processingUnavailable = false;
        },

        scanPackage(packageCode) {
            openScanner();
        },

        contactCommercial(package) {
            // Implémentation contact commercial
            this.showToast('Fonction contact commercial à implémenter', 'info');
        },

        openMaps(package) {
            const address = encodeURIComponent(package.recipient_data?.address || '');
            const url = `https://www.google.com/maps/search/?api=1&query=${address}`;
            window.open(url, '_blank');
        },

        handleDeliveryPhoto(event) {
            this.deliveryForm.photo = event.target.files[0];
        },

        resetDeliveryForm() {
            this.deliveryForm = {
                cod_collected: '',
                recipient_name: '',
                notes: '',
                photo: null
            };
            this.selectedPackage = null;
        },

        startAutoRefresh() {
            setInterval(() => {
                if (!this.loading && !this.processingDelivery && !this.processingUnavailable) {
                    this.loadPackages();
                }
            }, 120000); // 2 minutes
        },

        async loadPackages() {
            try {
                const response = await fetch('{{ route("deliverer.deliveries.index") }}?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applyFilters();
                    this.calculateStats();
                }
            } catch (error) {
                console.error('Erreur actualisation:', error);
            }
        },

        // Utility methods
        isUrgentPackage(package) {
            const hoursAgo = (new Date() - new Date(package.updated_at)) / (1000 * 60 * 60);
            return hoursAgo > 24 || package.delivery_attempts >= 2;
        },

        is4thAttempt(package) {
            return package.delivery_attempts >= 3;
        },

        getPackagePriorityClass(package) {
            if (this.is4thAttempt(package)) return 'ring-2 ring-purple-500';
            if (this.isUrgentPackage(package)) return 'ring-2 ring-red-500';
            return '';
        },

        getHeaderBackgroundClass(package) {
            if (this.is4thAttempt(package)) return 'bg-gradient-to-r from-purple-50 to-white';
            if (this.isUrgentPackage(package)) return 'bg-gradient-to-r from-red-50 to-white';
            return 'bg-gradient-to-r from-orange-50 to-white';
        },

        getPackageStatusText(package) {
            if (package.status === 'PICKED_UP') return 'À livrer';
            if (package.status === 'UNAVAILABLE') return 'Nouvelle tentative nécessaire';
            return package.status;
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : (type === 'error' ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z')}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }
}
</script>
@endsection