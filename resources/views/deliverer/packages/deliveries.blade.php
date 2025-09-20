@extends('layouts.deliverer')

@section('title', 'Livraisons')

@section('content')
<div x-data="deliveriesApp()" x-init="init()">
    
    <!-- Header avec stats et actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4 sticky top-20 z-10">
        <div class="p-4">
            <!-- En-tête principal -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Livraisons
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${filteredPackages.length} livraison(s) à effectuer`"></span>
                        <div class="flex items-center space-x-1" x-show="urgentCount > 0">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-red-600" x-text="`${urgentCount} urgent(s) (3+ tentatives)`"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="flex items-center space-x-2">
                    <!-- Scanner QR -->
                    <button @click="$dispatch('open-scanner')" 
                            class="bg-gradient-to-r from-emerald-500 to-green-500 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>
                    
                    <!-- Actualiser -->
                    <button @click="refreshData()" 
                            :class="loading ? 'animate-spin' : ''"
                            class="bg-blue-100 text-blue-600 p-3 rounded-xl hover:bg-blue-200 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                    
                    <!-- Filtres mobile -->
                    <button @click="showFilters = !showFilters" 
                            class="bg-gray-100 text-gray-600 p-3 rounded-xl hover:bg-gray-200 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filtres rapides -->
            <div x-show="!showFilters" class="flex items-center space-x-3 overflow-x-auto pb-2">
                <button @click="activeFilter = ''; applyFilters()" 
                        :class="activeFilter === '' ? 'bg-orange-100 text-orange-700 ring-2 ring-orange-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        📦 Tous (<span x-text="packages.length"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'urgent'; applyFilters()" 
                        :class="activeFilter === 'urgent' ? 'bg-red-100 text-red-700 ring-2 ring-red-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        🚨 Urgents (<span x-text="urgentCount"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'high_cod'; applyFilters()" 
                        :class="activeFilter === 'high_cod' ? 'bg-green-100 text-green-700 ring-2 ring-green-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        💰 COD Élevé (<span x-text="highCodCount"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'same_delegation'; applyFilters()" 
                        :class="activeFilter === 'same_delegation' ? 'bg-purple-100 text-purple-700 ring-2 ring-purple-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    📍 Même Zone
                </button>
                
                <button @click="activeFilter = 'retry'; applyFilters()" 
                        :class="activeFilter === 'retry' ? 'bg-yellow-100 text-yellow-700 ring-2 ring-yellow-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        🔄 Nouvelles tentatives (<span x-text="retryCount"></span>)
                    </span>
                </button>
            </div>

            <!-- Statistiques rapides -->
            <div class="mt-4 grid grid-cols-4 gap-2 text-xs">
                <div class="text-center p-2 bg-orange-50 rounded-lg">
                    <div class="font-bold text-orange-600" x-text="packages.length"></div>
                    <div class="text-orange-500">Total</div>
                </div>
                <div class="text-center p-2 bg-red-50 rounded-lg">
                    <div class="font-bold text-red-600" x-text="urgentCount"></div>
                    <div class="text-red-500">Urgents</div>
                </div>
                <div class="text-center p-2 bg-green-50 rounded-lg">
                    <div class="font-bold text-green-600" x-text="highCodCount"></div>
                    <div class="text-green-500">COD +50</div>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <div class="font-bold text-blue-600" x-text="totalCod.toFixed(0)"></div>
                    <div class="text-blue-500">COD Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-orange-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Chargement des livraisons...</p>
    </div>

    <!-- Liste des colis -->
    <div x-show="!loading" class="space-y-4 px-4 pb-6">
        
        <!-- Indicateur de tri -->
        <div x-show="filteredPackages.length > 0" class="flex items-center justify-between text-sm text-gray-500 px-2">
            <span>Triés par priorité (urgents en premier)</span>
            <span x-text="`${filteredPackages.length} résultat(s)`"></span>
        </div>
        
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200"
                 :class="getPackagePriorityClass(package)">
                
                <!-- Header avec statut et priorité -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100"
                     :class="getHeaderBackgroundClass(package)">
                    <div class="flex items-center space-x-3">
                        <!-- Icône priorité -->
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                             :class="getIconBackgroundClass(package)">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500" x-text="getStatusLabel(package.status)"></span>
                                <!-- Badges priorité -->
                                <span x-show="isUrgent(package)" 
                                      class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full animate-pulse">
                                    🚨 URGENT (3+ tentatives)
                                </span>
                                <span x-show="isHighCod(package)" 
                                      class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    💰 COD+
                                </span>
                                <span x-show="package.delivery_attempts > 0 && package.delivery_attempts < 3" 
                                      class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                    🔄 Tentative <span x-text="package.delivery_attempts + 1"></span>/3
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- COD Amount -->
                    <div class="text-right">
                        <div class="text-2xl font-bold" :class="getCodColorClass(package.cod_amount)" 
                             x-text="formatAmount(package.cod_amount)"></div>
                        <span class="text-xs text-gray-500">COD à collecter</span>
                    </div>
                </div>

                <!-- Détails du colis -->
                <div class="p-4">
                    <!-- Informations destinataire -->
                    <div class="mb-4">
                        <div class="flex items-center mb-3">
                            <div class="text-sm font-medium text-green-600">🎯 DESTINATAIRE</div>
                        </div>
                        
                        <div class="bg-green-50 p-3 rounded-xl border-l-4 border-green-400">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm" x-text="package.recipient_data?.name || 'N/A'"></p>
                                    <p class="text-xs text-gray-600" x-text="package.recipient_data?.phone || 'N/A'"></p>
                                    <p class="text-xs text-gray-700 mt-1" x-text="package.recipient_data?.address || 'N/A'"></p>
                                    <p class="text-xs text-green-600 font-medium mt-1" x-text="package.delegation_to?.name || 'N/A'"></p>
                                    
                                    <!-- Historique tentatives -->
                                    <div x-show="package.delivery_attempts > 0" class="mt-2 pt-2 border-t border-green-200">
                                        <p class="text-xs font-medium text-green-700">📋 Historique:</p>
                                        <p class="text-xs text-green-800">
                                            <span x-text="package.delivery_attempts"></span> tentative(s) - 
                                            <span x-show="package.unavailable_reason" x-text="getReasonLabel(package.unavailable_reason)"></span>
                                        </p>
                                        <p x-show="package.unavailable_notes" class="text-xs text-green-700 mt-1" x-text="package.unavailable_notes"></p>
                                    </div>
                                </div>
                                
                                <!-- Distance estimation -->
                                <div class="text-right ml-3">
                                    <div class="text-xs text-gray-500">Distance</div>
                                    <div class="text-sm font-medium text-blue-600">~3.2km</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu et détails -->
                    <div class="bg-gray-50 p-3 rounded-xl mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">📋 Contenu</p>
                                <p class="text-sm text-gray-900 mt-1" x-text="package.content_description || 'Non spécifié'"></p>
                                
                                <!-- Attributs spéciaux -->
                                <div x-show="package.is_fragile || package.requires_signature" class="mt-2 pt-2 border-t border-gray-200">
                                    <div class="flex flex-wrap gap-1">
                                        <span x-show="package.is_fragile" 
                                              class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                            🔴 Fragile
                                        </span>
                                        <span x-show="package.requires_signature" 
                                              class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                            ✏️ Signature
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Timing -->
                            <div class="text-right ml-3">
                                <div class="text-xs text-gray-500">Collecté</div>
                                <div class="text-sm font-medium text-orange-600" x-text="formatTimeAgo(package.picked_up_at || package.updated_at)"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions principales -->
                    <div class="flex items-center space-x-3">
                        <!-- Livrer (Action principale) -->
                        <button @click="deliverPackage(package)" 
                                :disabled="processing === package.id"
                                class="flex-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white py-4 px-4 rounded-xl font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="processing !== package.id" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>LIVRER</span>
                            </span>
                            <span x-show="processing === package.id" class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Livraison...</span>
                            </span>
                        </button>

                        <!-- Marquer indisponible -->
                        <button @click="markUnavailable(package)" 
                                :disabled="processing === package.id"
                                class="bg-orange-500 text-white py-4 px-4 rounded-xl font-bold hover:bg-orange-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="processing !== package.id" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>INDISPONIBLE</span>
                            </span>
                            <span x-show="processing === package.id" class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Traitement...</span>
                            </span>
                        </button>

                        <!-- Actions secondaires -->
                        <div class="flex space-x-2">
                            <!-- Scanner ce colis -->
                            <button @click="scanSpecificPackage(package.package_code)" 
                                    class="bg-blue-100 text-blue-600 p-3 rounded-xl hover:bg-blue-200 transition-colors"
                                    title="Scanner ce colis">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                                </svg>
                            </button>

                            <!-- Voir détails -->
                            <button @click="viewPackageDetails(package)" 
                                    class="bg-gray-100 text-gray-600 p-3 rounded-xl hover:bg-gray-200 transition-colors"
                                    title="Voir détails">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <!-- Navigation GPS -->
                            <button @click="openNavigation(package)" 
                                    class="bg-green-100 text-green-600 p-3 rounded-xl hover:bg-green-200 transition-colors"
                                    title="Navigation GPS">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- État vide -->
        <div x-show="filteredPackages.length === 0 && !loading" 
             class="text-center py-16">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span x-show="activeFilter === ''">Aucune livraison en attente</span>
                <span x-show="activeFilter !== ''">Aucun résultat pour ce filtre</span>
            </h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                <span x-show="activeFilter === ''">Vous n'avez pas de colis à livrer pour le moment.</span>
                <span x-show="activeFilter !== ''">Essayez d'ajuster vos filtres ou de rafraîchir la liste.</span>
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('deliverer.pickups.mine') }}" 
                   class="inline-block bg-orange-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-orange-600 transition-colors">
                    📦 Voir Mes Pickups
                </a>
                <div x-show="activeFilter !== ''">
                    <button @click="clearFilters()" 
                            class="text-orange-600 hover:text-orange-800 underline">
                        Effacer les filtres
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Indisponible -->
    <div x-show="showUnavailableModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showUnavailableModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Client Indisponible</h3>
                <button @click="showUnavailableModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUnavailable()">
                <!-- Current Attempt Info -->
                <div class="mb-4 bg-orange-50 p-4 rounded-xl border border-orange-200" x-show="selectedPackage">
                    <h4 class="font-semibold text-orange-800 mb-2">📊 Tentative # <span x-text="(selectedPackage?.delivery_attempts || 0) + 1"></span>/3</h4>
                    <div x-show="(selectedPackage?.delivery_attempts || 0) >= 2">
                        <p class="text-sm text-red-700 font-medium">⚠️ Dernière tentative avant retour obligatoire</p>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-orange-700">
                            <strong>Colis:</strong> <span x-text="selectedPackage?.package_code"></span>
                        </p>
                        <p class="text-sm text-orange-700">
                            <strong>COD:</strong> <span x-text="formatAmount(selectedPackage?.cod_amount)"></span>
                        </p>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison d'indisponibilité *</label>
                    <select x-model="unavailableForm.reason" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Sélectionner une raison</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse le colis</option>
                        <option value="PHONE_OFF">Téléphone éteint/injoignable</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Détails de la tentative *</label>
                    <textarea x-model="unavailableForm.notes" required
                              placeholder="Décrivez ce qui s'est passé..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                              rows="3"></textarea>
                </div>

                <!-- Next Attempt Date -->
                <div class="mb-4" x-show="(selectedPackage?.delivery_attempts || 0) < 2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prochaine tentative prévue</label>
                    <input type="datetime-local" x-model="unavailableForm.nextAttempt"
                           :min="new Date(Date.now() + 3600000).toISOString().slice(0, -1)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de preuve (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handleUnavailablePhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="unavailablePhotoInput">
                        
                        <div x-show="!unavailableForm.photoPreview" @click="$refs.unavailablePhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Photo de preuve (adresse, boîte aux lettres...)</p>
                        </div>
                        
                        <div x-show="unavailableForm.photoPreview" class="relative">
                            <img :src="unavailableForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removeUnavailablePhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit" :disabled="processing"
                            class="flex-1 bg-orange-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-orange-600 transition-colors disabled:opacity-50">
                        <span x-show="!processing">⏰ Enregistrer Tentative</span>
                        <span x-show="processing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Enregistrement...
                        </span>
                    </button>
                    <button type="button" @click="showUnavailableModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deliveriesApp() {
    return {
        // Données principales
        packages: @json($packages->items()),
        filteredPackages: [],
        
        // États
        loading: false,
        processing: null,
        
        // Filtres
        showFilters: false,
        activeFilter: '',
        
        // Statistiques
        urgentCount: 0,
        highCodCount: 0,
        retryCount: 0,
        totalCod: 0,
        
        // Modal Unavailable
        showUnavailableModal: false,
        selectedPackage: null,
        unavailableForm: {
            reason: '',
            notes: '',
            nextAttempt: '',
            photo: null,
            photoPreview: null
        },

        init() {
            this.applyFilters();
            this.calculateStats();
            
            console.log('Deliveries App initialisé:', {
                packages: this.packages.length
            });
        },

        // ==================== FILTRES ====================
        
        applyFilters() {
            let filtered = [...this.packages];
            
            // Filtres rapides
            switch (this.activeFilter) {
                case 'urgent':
                    filtered = filtered.filter(pkg => this.isUrgent(pkg));
                    break;
                case 'high_cod':
                    filtered = filtered.filter(pkg => parseFloat(pkg.cod_amount) >= 50);
                    break;
                case 'retry':
                    filtered = filtered.filter(pkg => pkg.status === 'UNAVAILABLE');
                    break;
                case 'same_delegation':
                    // Grouper par délégation la plus fréquente
                    const delegationCounts = {};
                    this.packages.forEach(pkg => {
                        const delId = pkg.delegation_to?.id;
                        if (delId) {
                            delegationCounts[delId] = (delegationCounts[delId] || 0) + 1;
                        }
                    });
                    const mostCommonDelegation = Object.keys(delegationCounts).reduce((a, b) => 
                        delegationCounts[a] > delegationCounts[b] ? a : b, null);
                    if (mostCommonDelegation) {
                        filtered = filtered.filter(pkg => 
                            pkg.delegation_to?.id == mostCommonDelegation
                        );
                    }
                    break;
            }
            
            // Tri : urgents en premier, puis tentatives multiples, puis par date
            filtered.sort((a, b) => {
                const aUrgent = this.isUrgent(a);
                const bUrgent = this.isUrgent(b);
                
                // Urgents d'abord
                if (aUrgent && !bUrgent) return -1;
                if (!aUrgent && bUrgent) return 1;
                
                // Puis par nombre de tentatives (plus de tentatives = prioritaire)
                const aTentatives = a.delivery_attempts || 0;
                const bTentatives = b.delivery_attempts || 0;
                if (aTentatives !== bTentatives) return bTentatives - aTentatives;
                
                // Enfin par ancienneté
                return new Date(a.updated_at) - new Date(b.updated_at);
            });
            
            this.filteredPackages = filtered;
        },

        clearFilters() {
            this.activeFilter = '';
            this.applyFilters();
        },

        // ==================== STATISTIQUES ====================
        
        calculateStats() {
            this.urgentCount = this.packages.filter(pkg => this.isUrgent(pkg)).length;
            this.highCodCount = this.packages.filter(pkg => parseFloat(pkg.cod_amount) >= 50).length;
            this.retryCount = this.packages.filter(pkg => pkg.status === 'UNAVAILABLE').length;
            this.totalCod = this.packages.reduce((sum, pkg) => sum + parseFloat(pkg.cod_amount || 0), 0);
        },

        isUrgent(package) {
            return (package.delivery_attempts || 0) >= 3;
        },

        isHighCod(package) {
            return parseFloat(package.cod_amount) >= 50;
        },

        // ==================== ACTIONS PRINCIPALES ====================
        
        deliverPackage(package) {
            // Rediriger vers la page de détail pour la livraison complète
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        markUnavailable(package) {
            this.selectedPackage = package;
            this.showUnavailableModal = true;
        },

        async submitUnavailable() {
            if (this.processing || !this.selectedPackage) return;
            
            this.processing = true;
            
            try {
                const formData = new FormData();
                formData.append('reason', this.unavailableForm.reason);
                formData.append('attempt_notes', this.unavailableForm.notes);
                
                if (this.unavailableForm.nextAttempt) {
                    formData.append('next_attempt_date', this.unavailableForm.nextAttempt);
                }
                
                if (this.unavailableForm.photo) {
                    formData.append('attempt_photo', this.unavailableForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.selectedPackage.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showUnavailableModal = false;
                    
                    // Mettre à jour le package ou le retirer de la liste
                    if (data.data.is_final_attempt) {
                        // 3ème tentative -> retirer de la liste (va vers returns)
                        this.packages = this.packages.filter(p => p.id !== this.selectedPackage.id);
                    } else {
                        // Mettre à jour le package
                        const packageIndex = this.packages.findIndex(p => p.id === this.selectedPackage.id);
                        if (packageIndex > -1) {
                            this.packages[packageIndex].delivery_attempts = data.data.attempt_count;
                            this.packages[packageIndex].status = data.data.status;
                            this.packages[packageIndex].unavailable_reason = this.unavailableForm.reason;
                            this.packages[packageIndex].unavailable_notes = this.unavailableForm.notes;
                        }
                    }
                    
                    this.applyFilters();
                    this.calculateStats();
                    this.resetUnavailableForm();
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
                }
            } catch (error) {
                console.error('Erreur unavailable:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processing = false;
        },

        resetUnavailableForm() {
            this.unavailableForm = {
                reason: '',
                notes: '',
                nextAttempt: '',
                photo: null,
                photoPreview: null
            };
            this.selectedPackage = null;
        },

        // ==================== ACTIONS SECONDAIRES ====================
        
        async refreshData() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("deliverer.deliveries.index") }}?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applyFilters();
                    this.calculateStats();
                    this.showToast('Livraisons mises à jour', 'success');
                }
            } catch (error) {
                console.error('Erreur actualisation:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.loading = false;
        },

        scanSpecificPackage(packageCode) {
            window.dispatchEvent(new CustomEvent('open-scanner', {
                detail: { prefilledCode: packageCode }
            }));
        },

        viewPackageDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        openNavigation(package) {
            const address = package.recipient_data?.address || '';
            if (!address) {
                this.showToast('Adresse non disponible', 'error');
                return;
            }
            
            const encodedAddress = encodeURIComponent(address);
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_system');
            } else {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
            }
        },

        // ==================== GESTION PHOTOS ====================
        
        handleUnavailablePhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.unavailableForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.unavailableForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeUnavailablePhoto() {
            this.unavailableForm.photo = null;
            this.unavailableForm.photoPreview = null;
            this.$refs.unavailablePhotoInput.value = '';
        },

        // ==================== UI HELPERS ====================
        
        getPackagePriorityClass(package) {
            if (this.isUrgent(package)) return 'ring-2 ring-red-300 shadow-red-100';
            if (this.isHighCod(package)) return 'ring-1 ring-green-300 shadow-green-100';
            if (package.status === 'UNAVAILABLE') return 'ring-1 ring-yellow-300 shadow-yellow-100';
            return '';
        },

        getHeaderBackgroundClass(package) {
            if (this.isUrgent(package)) return 'bg-gradient-to-r from-red-50 to-pink-50';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-50 to-emerald-50';
            if (package.status === 'UNAVAILABLE') return 'bg-gradient-to-r from-yellow-50 to-orange-50';
            return 'bg-gradient-to-r from-orange-50 to-amber-50';
        },

        getIconBackgroundClass(package) {
            if (this.isUrgent(package)) return 'bg-gradient-to-r from-red-500 to-red-600';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-500 to-green-600';
            if (package.status === 'UNAVAILABLE') return 'bg-gradient-to-r from-yellow-500 to-orange-500';
            return 'bg-gradient-to-r from-orange-500 to-orange-600';
        },

        getCodColorClass(amount) {
            const cod = parseFloat(amount);
            if (cod >= 100) return 'text-red-600';
            if (cod >= 50) return 'text-green-600';
            return 'text-orange-600';
        },

        getStatusLabel(status) {
            const labels = {
                'PICKED_UP': 'Collecté - Prêt livraison',
                'UNAVAILABLE': 'Nouvelle tentative'
            };
            return labels[status] || status;
        },

        getReasonLabel(reason) {
            const labels = {
                'CLIENT_ABSENT': 'Client absent',
                'ADDRESS_NOT_FOUND': 'Adresse introuvable',
                'CLIENT_REFUSES': 'Client refuse',
                'PHONE_OFF': 'Téléphone éteint',
                'OTHER': 'Autre'
            };
            return labels[reason] || reason;
        },

        // ==================== FORMATAGE ====================
        
        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) return `il y a ${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `il y a ${Math.floor(diffInMinutes / 60)}h`;
            return `il y a ${Math.floor(diffInMinutes / 1440)}j`;
        },

        // ==================== NOTIFICATIONS ====================
        
        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-orange-500' : 'bg-red-500';
            const icon = type === 'success' ? 
                'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 
                'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
            
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}"/>
                    </svg>
                    <span class="font-medium">${message}</span>
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