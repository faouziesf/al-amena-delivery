@extends('layouts.deliverer')

@section('title', 'Pickups Disponibles')

@section('content')
<div x-data="availablePickupsApp()" x-init="init()">
    
    <!-- Header avec stats et actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4 sticky top-20 z-10">
        <div class="p-4">
            <!-- En-tête principal -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Pickups Disponibles
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${filteredPackages.length} colis disponibles`"></span>
                        <div class="flex items-center space-x-1" x-show="urgentCount > 0">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-red-600" x-text="`${urgentCount} urgent(s)`"></span>
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
                            class="bg-purple-100 text-purple-600 p-3 rounded-xl hover:bg-purple-200 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filtres rapides -->
            <div x-show="!showFilters" class="flex items-center space-x-3 overflow-x-auto pb-2">
                <button @click="activeFilter = ''; applyFilters()" 
                        :class="activeFilter === '' ? 'bg-blue-100 text-blue-700 ring-2 ring-blue-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        📦 Tous (<span x-text="packages.length"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'high_cod'; applyFilters()" 
                        :class="activeFilter === 'high_cod' ? 'bg-green-100 text-green-700 ring-2 ring-green-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        💰 COD Élevé (<span x-text="highCodCount"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'urgent'; applyFilters()" 
                        :class="activeFilter === 'urgent' ? 'bg-red-100 text-red-700 ring-2 ring-red-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        🚨 Urgents (<span x-text="urgentCount"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'nearby'; applyFilters()" 
                        :class="activeFilter === 'nearby' ? 'bg-purple-100 text-purple-700 ring-2 ring-purple-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    📍 Proches
                </button>
            </div>

            <!-- Filtres avancés (cachés par défaut) -->
            <div x-show="showFilters" x-transition class="mt-4 p-4 bg-gray-50 rounded-xl space-y-4">
                <!-- Recherche -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Recherche</label>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="applyFilters()"
                           placeholder="Code, nom client, téléphone..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Délégations -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📍 Zone Départ</label>
                        <select x-model="filters.delegationFrom" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <option value="">Toutes</option>
                            <template x-for="delegation in delegations" :key="delegation.id">
                                <option :value="delegation.id" x-text="delegation.name"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🎯 Zone Arrivée</label>
                        <select x-model="filters.delegationTo" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <option value="">Toutes</option>
                            <template x-for="delegation in delegations" :key="delegation.id">
                                <option :value="delegation.id" x-text="delegation.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                
                <!-- COD Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">💰 COD Min (DT)</label>
                        <input type="number" x-model="filters.codMin" @input.debounce.500ms="applyFilters()" step="0.001" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">💰 COD Max (DT)</label>
                        <input type="number" x-model="filters.codMax" @input.debounce.500ms="applyFilters()" step="0.001" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <!-- Actions filtres -->
                <div class="flex justify-between items-center pt-2">
                    <button @click="clearFilters()" 
                            class="text-gray-600 hover:text-gray-800 text-sm underline">
                        🗑️ Effacer filtres
                    </button>
                    <button @click="showFilters = false" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        ✅ Appliquer
                    </button>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="mt-4 grid grid-cols-4 gap-2 text-xs">
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <div class="font-bold text-blue-600" x-text="packages.length"></div>
                    <div class="text-blue-500">Total</div>
                </div>
                <div class="text-center p-2 bg-green-50 rounded-lg">
                    <div class="font-bold text-green-600" x-text="highCodCount"></div>
                    <div class="text-green-500">COD +50</div>
                </div>
                <div class="text-center p-2 bg-red-50 rounded-lg">
                    <div class="font-bold text-red-600" x-text="urgentCount"></div>
                    <div class="text-red-500">Urgents</div>
                </div>
                <div class="text-center p-2 bg-purple-50 rounded-lg">
                    <div class="font-bold text-purple-600" x-text="totalCod.toFixed(0)"></div>
                    <div class="text-purple-500">COD Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Chargement des pickups...</p>
        <p class="text-sm text-gray-500">Recherche des colis disponibles</p>
    </div>

    <!-- Liste des colis -->
    <div x-show="!loading" class="space-y-4 px-4 pb-6">
        
        <!-- Indicateur de tri -->
        <div x-show="filteredPackages.length > 0" class="flex items-center justify-between text-sm text-gray-500 px-2">
            <span>Triés par ancienneté</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500" x-text="formatTimeAgo(package.created_at)"></span>
                                <!-- Badges priorité -->
                                <span x-show="isUrgent(package)" 
                                      class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full animate-pulse">
                                    🚨 URGENT
                                </span>
                                <span x-show="isHighCod(package)" 
                                      class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    💰 COD+
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
                    <!-- Route expéditeur → destinataire -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-sm font-medium text-orange-600">📦 COLLECTE CHEZ</div>
                            <div class="text-sm font-medium text-green-600">🎯 LIVRER À</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Expéditeur -->
                            <div class="bg-orange-50 p-3 rounded-xl border-l-4 border-orange-400">
                                <p class="font-semibold text-gray-900 text-sm" x-text="package.sender_data?.name || 'N/A'"></p>
                                <p class="text-xs text-gray-600" x-text="package.sender_data?.phone || 'N/A'"></p>
                                <p class="text-xs text-orange-600 font-medium mt-1" x-text="package.delegation_from?.name || 'N/A'"></p>
                            </div>
                            
                            <!-- Destinataire -->
                            <div class="bg-green-50 p-3 rounded-xl border-l-4 border-green-400">
                                <p class="font-semibold text-gray-900 text-sm" x-text="package.recipient_data?.name || 'N/A'"></p>
                                <p class="text-xs text-gray-600" x-text="package.recipient_data?.phone || 'N/A'"></p>
                                <p class="text-xs text-green-600 font-medium mt-1" x-text="package.delegation_to?.name || 'N/A'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu et détails -->
                    <div class="bg-gray-50 p-3 rounded-xl mb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">📋 Contenu</p>
                                <p class="text-sm text-gray-900 mt-1" x-text="package.content_description || 'Non spécifié'"></p>
                                
                                <!-- Notes spéciales -->
                                <div x-show="package.notes" class="mt-2 pt-2 border-t border-gray-200">
                                    <p class="text-xs font-medium text-amber-600">📝 Instructions</p>
                                    <p class="text-xs text-amber-700" x-text="package.notes"></p>
                                </div>
                                
                                <!-- Attributs spéciaux -->
                                <div x-show="package.is_fragile || package.requires_signature" class="mt-2 pt-2 border-t border-gray-200">
                                    <div class="flex flex-wrap gap-1">
                                        <span x-show="package.is_fragile" 
                                              class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                            🔴 Fragile
                                        </span>
                                        <span x-show="package.requires_signature" 
                                              class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                            ✍️ Signature
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Distance/proximité (si géolocalisation disponible) -->
                            <div class="text-right ml-3">
                                <div class="text-xs text-gray-500">Distance</div>
                                <div class="text-sm font-medium text-blue-600">~2.5km</div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions principales -->
                    <div class="flex items-center space-x-3">
                        <!-- Accepter (Action principale) -->
                        <button @click="acceptPickup(package)" 
                                :disabled="accepting === package.id"
                                class="flex-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white py-4 px-4 rounded-xl font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="accepting !== package.id" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>ACCEPTER PICKUP</span>
                            </span>
                            <span x-show="accepting === package.id" class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Acceptation...</span>
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

                            <!-- Plus d'infos -->
                            <button @click="viewPackageDetails(package)" 
                                    class="bg-gray-100 text-gray-600 p-3 rounded-xl hover:bg-gray-200 transition-colors"
                                    title="Voir détails">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <!-- Navigation GPS -->
                            <button @click="openNavigation(package)" 
                                    class="bg-purple-100 text-purple-600 p-3 rounded-xl hover:bg-purple-200 transition-colors"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span x-show="activeFilter === ''">Aucun pickup disponible</span>
                <span x-show="activeFilter !== ''">Aucun résultat pour ce filtre</span>
            </h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                <span x-show="activeFilter === ''">Tous les colis sont déjà assignés ou aucun nouveau colis n'est disponible pour le moment.</span>
                <span x-show="activeFilter !== ''">Essayez d'ajuster vos filtres ou de rafraîchir la liste.</span>
            </p>
            
            <div class="space-y-3">
                <button @click="refreshData()" 
                        class="bg-emerald-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-emerald-600 transition-colors">
                    🔄 Actualiser
                </button>
                <div x-show="activeFilter !== ''">
                    <button @click="clearFilters()" 
                            class="text-blue-600 hover:text-blue-800 underline">
                        Effacer les filtres
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pull to Refresh Indicator -->
    <div x-show="refreshing" class="fixed top-24 left-1/2 transform -translate-x-1/2 z-40">
        <div class="bg-emerald-500 text-white px-6 py-3 rounded-full shadow-xl flex items-center space-x-3">
            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span class="font-medium">Actualisation...</span>
        </div>
    </div>
</div>

<!-- Modal Détails Package -->
<div x-show="showDetailsModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
    <div x-show="showDetailsModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
         x-transition:enter-end="translate-y-0 sm:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0"
         class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Détails du Colis</h3>
            <button @click="showDetailsModal = false" 
                    class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="selectedPackage" class="space-y-6">
            <!-- Code Package -->
            <div class="bg-blue-50 p-4 rounded-xl text-center">
                <p class="text-sm text-blue-600 font-medium">Code Colis</p>
                <p class="text-2xl font-bold text-blue-900 font-mono" x-text="selectedPackage?.package_code"></p>
            </div>

            <!-- COD -->
            <div class="bg-green-50 p-4 rounded-xl text-center">
                <p class="text-sm text-green-600 font-medium">Montant COD</p>
                <p class="text-3xl font-bold text-green-900" x-text="formatAmount(selectedPackage?.cod_amount)"></p>
                <p class="text-xs text-green-600 mt-1">À collecter lors de la livraison</p>
            </div>

            <!-- Itinéraire complet -->
            <div class="space-y-4">
                <!-- Collecte -->
                <div class="border-l-4 border-orange-400 pl-4">
                    <h4 class="font-semibold text-orange-800 mb-2">📦 Point de Collecte</h4>
                    <div class="space-y-1 text-sm">
                        <p class="font-medium text-gray-900" x-text="selectedPackage?.sender_data?.name"></p>
                        <p class="text-gray-600" x-text="selectedPackage?.sender_data?.phone"></p>
                        <p class="text-gray-700" x-text="selectedPackage?.sender_data?.address || 'Adresse non spécifiée'"></p>
                        <p class="text-orange-600 font-medium" x-text="selectedPackage?.delegation_from?.name"></p>
                    </div>
                </div>
                
                <!-- Flèche -->
                <div class="text-center">
                    <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                
                <!-- Livraison -->
                <div class="border-l-4 border-green-400 pl-4">
                    <h4 class="font-semibold text-green-800 mb-2">🎯 Point de Livraison</h4>
                    <div class="space-y-1 text-sm">
                        <p class="font-medium text-gray-900" x-text="selectedPackage?.recipient_data?.name"></p>
                        <p class="text-gray-600" x-text="selectedPackage?.recipient_data?.phone"></p>
                        <p class="text-gray-700" x-text="selectedPackage?.recipient_data?.address || 'Adresse non spécifiée'"></p>
                        <p class="text-green-600 font-medium" x-text="selectedPackage?.delegation_to?.name"></p>
                    </div>
                </div>
            </div>

            <!-- Détails additionnels -->
            <div class="bg-gray-50 p-4 rounded-xl">
                <h4 class="font-semibold text-gray-800 mb-3">📋 Informations Complémentaires</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Contenu:</span>
                        <span class="font-medium text-gray-900" x-text="selectedPackage?.content_description"></span>
                    </div>
                    <div x-show="selectedPackage?.package_weight" class="flex justify-between">
                        <span class="text-gray-600">Poids:</span>
                        <span class="font-medium text-gray-900" x-text="selectedPackage?.package_weight + ' kg'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Créé le:</span>
                        <span class="font-medium text-gray-900" x-text="formatDateTime(selectedPackage?.created_at)"></span>
                    </div>
                </div>
                
                <!-- Attributs spéciaux -->
                <div x-show="selectedPackage?.is_fragile || selectedPackage?.requires_signature" 
                     class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-sm font-medium text-gray-700 mb-2">Exigences spéciales:</p>
                    <div class="flex flex-wrap gap-2">
                        <span x-show="selectedPackage?.is_fragile" 
                              class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">
                            🔴 Fragile
                        </span>
                        <span x-show="selectedPackage?.requires_signature" 
                              class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                            ✍️ Signature requise
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions Modal -->
            <div class="flex space-x-3 pt-4">
                <button @click="acceptPickup(selectedPackage); showDetailsModal = false" 
                        :disabled="accepting === selectedPackage?.id"
                        class="flex-1 bg-emerald-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                    <span x-show="accepting !== selectedPackage?.id">✅ Accepter ce Pickup</span>
                    <span x-show="accepting === selectedPackage?.id">⏳ Acceptation...</span>
                </button>
                <button @click="showDetailsModal = false" 
                        class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function availablePickupsApp() {
    return {
        // Données principales
        packages: @json($packages->items()),
        delegations: @json($delegations),
        filteredPackages: [],
        
        // États
        loading: false,
        refreshing: false,
        accepting: null,
        
        // Filtres
        showFilters: false,
        activeFilter: '',
        searchQuery: '',
        filters: {
            delegationFrom: '',
            delegationTo: '',
            codMin: '',
            codMax: ''
        },
        
        // Statistiques
        urgentCount: 0,
        highCodCount: 0,
        totalCod: 0,
        
        // Modal
        showDetailsModal: false,
        selectedPackage: null,

        init() {
            this.applyFilters();
            this.calculateStats();
            this.setupPullToRefresh();
            this.startAutoRefresh();
            
            // Log d'initialisation
            console.log('💼 Available Pickups App initialisé:', {
                packages: this.packages.length,
                delegations: this.delegations.length
            });
        },

        // ==================== FILTRES ====================
        
        applyFilters() {
            let filtered = [...this.packages];
            
            // Filtre de recherche
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(pkg => 
                    pkg.package_code.toLowerCase().includes(query) ||
                    (pkg.recipient_data?.name || '').toLowerCase().includes(query) ||
                    (pkg.recipient_data?.phone || '').toLowerCase().includes(query) ||
                    (pkg.sender_data?.name || '').toLowerCase().includes(query) ||
                    (pkg.content_description || '').toLowerCase().includes(query)
                );
            }
            
            // Filtres par délégation
            if (this.filters.delegationFrom) {
                filtered = filtered.filter(pkg => 
                    pkg.delegation_from?.id == this.filters.delegationFrom
                );
            }
            
            if (this.filters.delegationTo) {
                filtered = filtered.filter(pkg => 
                    pkg.delegation_to?.id == this.filters.delegationTo
                );
            }
            
            // Filtres COD
            if (this.filters.codMin) {
                filtered = filtered.filter(pkg => 
                    parseFloat(pkg.cod_amount) >= parseFloat(this.filters.codMin)
                );
            }
            
            if (this.filters.codMax) {
                filtered = filtered.filter(pkg => 
                    parseFloat(pkg.cod_amount) <= parseFloat(this.filters.codMax)
                );
            }
            
            // Filtres rapides
            switch (this.activeFilter) {
                case 'high_cod':
                    filtered = filtered.filter(pkg => parseFloat(pkg.cod_amount) >= 50);
                    break;
                case 'urgent':
                    filtered = filtered.filter(pkg => this.isUrgent(pkg));
                    break;
                case 'nearby':
                    // À implémenter avec géolocalisation
                    break;
            }
            
            // Tri : urgents en premier, puis par ancienneté
            filtered.sort((a, b) => {
                const aUrgent = this.isUrgent(a);
                const bUrgent = this.isUrgent(b);
                
                if (aUrgent && !bUrgent) return -1;
                if (!aUrgent && bUrgent) return 1;
                
                // Si même priorité, trier par ancienneté
                return new Date(a.created_at) - new Date(b.created_at);
            });
            
            this.filteredPackages = filtered;
        },

        clearFilters() {
            this.activeFilter = '';
            this.searchQuery = '';
            this.filters = {
                delegationFrom: '',
                delegationTo: '',
                codMin: '',
                codMax: ''
            };
            this.applyFilters();
        },

        // ==================== STATISTIQUES ====================
        
        calculateStats() {
            this.urgentCount = this.packages.filter(pkg => this.isUrgent(pkg)).length;
            this.highCodCount = this.packages.filter(pkg => parseFloat(pkg.cod_amount) >= 50).length;
            this.totalCod = this.packages.reduce((sum, pkg) => sum + parseFloat(pkg.cod_amount || 0), 0);
        },

        isUrgent(package) {
            const createdAt = new Date(package.created_at);
            const hoursAgo = (new Date() - createdAt) / (1000 * 60 * 60);
            return hoursAgo > 24; // Urgent si créé il y a plus de 24h
        },

        isHighCod(package) {
            return parseFloat(package.cod_amount) >= 50;
        },

        // ==================== ACTIONS ====================
        
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
                    this.calculateStats();
                    
                    // Rediriger si fourni
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    }
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'acceptation', 'error');
                }
            } catch (error) {
                console.error('Erreur acceptation pickup:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.accepting = null;
        },

        async refreshData() {
            this.refreshing = true;
            
            try {
                const response = await fetch('{{ route("deliverer.pickups.available") }}?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applyFilters();
                    this.calculateStats();
                    this.showToast('📦 Pickups mis à jour', 'success');
                }
            } catch (error) {
                console.error('Erreur actualisation:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.refreshing = false;
        },

        scanSpecificPackage(packageCode) {
            window.dispatchEvent(new CustomEvent('open-scanner', {
                detail: { prefilledCode: packageCode }
            }));
        },

        viewPackageDetails(package) {
            this.selectedPackage = package;
            this.showDetailsModal = true;
        },

        openNavigation(package) {
            const address = encodeURIComponent(package.sender_data?.address || '');
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                window.open(`https://maps.google.com/maps?q=${address}`, '_system');
            } else {
                window.open(`https://maps.google.com/maps?q=${address}`, '_blank');
            }
        },

        // ==================== UI HELPERS ====================
        
        getPackagePriorityClass(package) {
            if (this.isUrgent(package)) return 'ring-2 ring-red-300 shadow-red-100';
            if (this.isHighCod(package)) return 'ring-1 ring-green-300 shadow-green-100';
            return '';
        },

        getHeaderBackgroundClass(package) {
            if (this.isUrgent(package)) return 'bg-gradient-to-r from-red-50 to-pink-50';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-50 to-emerald-50';
            return 'bg-gradient-to-r from-blue-50 to-indigo-50';
        },

        getIconBackgroundClass(package) {
            if (this.isUrgent(package)) return 'bg-gradient-to-r from-red-500 to-red-600';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-500 to-green-600';
            return 'bg-gradient-to-r from-blue-500 to-blue-600';
        },

        getCodColorClass(amount) {
            const cod = parseFloat(amount);
            if (cod >= 100) return 'text-red-600';
            if (cod >= 50) return 'text-green-600';
            return 'text-blue-600';
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

        formatDateTime(timestamp) {
            return new Date(timestamp).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        // ==================== FONCTIONNALITÉS MOBILES ====================
        
        setupPullToRefresh() {
            let startY = 0;
            let pullDistance = 0;
            const threshold = 100;

            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0) {
                    startY = e.touches[0].clientY;
                }
            }, { passive: true });

            document.addEventListener('touchmove', (e) => {
                if (window.scrollY === 0 && startY > 0) {
                    pullDistance = e.touches[0].clientY - startY;
                    if (pullDistance > threshold && !this.refreshing) {
                        this.refreshing = true;
                    }
                }
            }, { passive: true });

            document.addEventListener('touchend', () => {
                if (pullDistance > threshold) {
                    this.refreshData();
                }
                startY = 0;
                pullDistance = 0;
            }, { passive: true });
        },

        startAutoRefresh() {
            setInterval(() => {
                if (!this.refreshing && !this.accepting) {
                    this.refreshData();
                }
            }, 60000); // Actualisation auto toutes les minutes
        },

        // ==================== NOTIFICATIONS ====================
        
        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
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
            
            // Animation d'entrée
            setTimeout(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            }, 100);
            
            // Suppression automatique
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