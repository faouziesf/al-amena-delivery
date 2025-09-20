@extends('layouts.deliverer')

@section('title', 'Mes Pickups')

@section('content')
<div x-data="myPickupsApp()" x-init="init()">
    
    <!-- Header avec stats et actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4 sticky top-20 z-10">
        <div class="p-4">
            <!-- En-tête principal -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Mes Pickups
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${filteredPackages.length} pickup(s) à collecter`"></span>
                        <div class="flex items-center space-x-1" x-show="oldestPackage">
                            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-orange-600" x-text="oldestPackage ? `Le plus ancien: ${oldestPackage.days_old} jour(s)` : ''"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="flex items-center space-x-2">
                    <!-- Tout collecter -->
                    <button @click="showBulkCollectModal = true" 
                            x-show="filteredPackages.length > 1"
                            class="bg-gradient-to-r from-emerald-500 to-green-500 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    
                    <!-- Scanner QR -->
                    <button @click="$dispatch('open-scanner')" 
                            class="bg-purple-100 text-purple-600 p-3 rounded-xl hover:bg-purple-200 transition-all">
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
                        :class="activeFilter === '' ? 'bg-purple-100 text-purple-700 ring-2 ring-purple-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        📦 Tous (<span x-text="packages.length"></span>)
                    </span>
                </button>
                
                <button @click="activeFilter = 'today'; applyFilters()" 
                        :class="activeFilter === 'today' ? 'bg-blue-100 text-blue-700 ring-2 ring-blue-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        🕐 Aujourd'hui (<span x-text="todayCount"></span>)
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
                        :class="activeFilter === 'same_delegation' ? 'bg-orange-100 text-orange-700 ring-2 ring-orange-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    📍 Même Zone
                </button>
                
                <button @click="activeFilter = 'old'; applyFilters()" 
                        :class="activeFilter === 'old' ? 'bg-red-100 text-red-700 ring-2 ring-red-300' : 'bg-gray-100 text-gray-600'"
                        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    <span class="flex items-center">
                        ⏰ Anciens (<span x-text="oldCount"></span>)
                    </span>
                </button>
            </div>

            <!-- Filtres avancés -->
            <div x-show="showFilters" x-transition class="mt-4 p-4 bg-gray-50 rounded-xl space-y-4">
                <!-- Recherche -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Recherche</label>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="applyFilters()"
                           placeholder="Code, nom expéditeur, délégation..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <!-- Délégations -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📍 Délégation de Collecte</label>
                        <select x-model="filters.delegationFrom" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
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
                
                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Accepté Après</label>
                        <input type="date" x-model="filters.dateFrom" @change="applyFilters()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Accepté Avant</label>
                        <input type="date" x-model="filters.dateTo" @change="applyFilters()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- Actions filtres -->
                <div class="flex justify-between items-center pt-2">
                    <button @click="clearFilters()" 
                            class="text-gray-600 hover:text-gray-800 text-sm underline">
                        🗑️ Effacer filtres
                    </button>
                    <button @click="showFilters = false" 
                            class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                        ✅ Appliquer
                    </button>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="mt-4 grid grid-cols-4 gap-2 text-xs">
                <div class="text-center p-2 bg-purple-50 rounded-lg">
                    <div class="font-bold text-purple-600" x-text="packages.length"></div>
                    <div class="text-purple-500">Total</div>
                </div>
                <div class="text-center p-2 bg-green-50 rounded-lg">
                    <div class="font-bold text-green-600" x-text="highCodCount"></div>
                    <div class="text-green-500">COD +50</div>
                </div>
                <div class="text-center p-2 bg-orange-50 rounded-lg">
                    <div class="font-bold text-orange-600" x-text="todayCount"></div>
                    <div class="text-orange-500">Aujourd'hui</div>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <div class="font-bold text-blue-600" x-text="totalCod.toFixed(0)"></div>
                    <div class="text-blue-500">COD Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode sélection multiple -->
    <div x-show="isSelectionMode" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-purple-100 border-b border-purple-200 mx-4 mb-4 p-3 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-purple-800" x-text="`${selectedPackages.length} colis sélectionné(s)`"></span>
                <button @click="selectAll()" class="text-sm text-purple-600 hover:text-purple-800 underline">
                    Tout sélectionner
                </button>
                <button @click="clearSelection()" class="text-sm text-purple-600 hover:text-purple-800 underline">
                    Tout désélectionner
                </button>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="bulkMarkCollected()" 
                        :disabled="selectedPackages.length === 0"
                        class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Collecter sélectionnés
                </button>
                <button @click="exitSelectionMode()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Chargement de vos pickups...</p>
        <p class="text-sm text-gray-500">Récupération des colis acceptés</p>
    </div>

    <!-- Liste des colis -->
    <div x-show="!loading" class="space-y-4 px-4 pb-6">
        
        <!-- Indicateur de tri -->
        <div x-show="filteredPackages.length > 0" class="flex items-center justify-between text-sm text-gray-500 px-2">
            <span>Triés par ancienneté d'acceptation</span>
            <span x-text="`${filteredPackages.length} résultat(s)`"></span>
        </div>
        
        <template x-for="package in filteredPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200"
                 :class="getPackagePriorityClass(package)"
                 @click="isSelectionMode ? toggleSelection(package.id) : null">
                
                <!-- Header avec sélection et priorité -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100"
                     :class="getHeaderBackgroundClass(package)">
                    <div class="flex items-center space-x-3">
                        <!-- Checkbox pour sélection multiple -->
                        <div x-show="isSelectionMode" class="flex-shrink-0">
                            <input type="checkbox" 
                                   :checked="selectedPackages.includes(package.id)"
                                   @change="toggleSelection(package.id)"
                                   class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                        </div>
                        
                        <!-- Icône priorité -->
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                             :class="getIconBackgroundClass(package)">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500" x-text="formatTimeAgo(package.assigned_at)"></span>
                                <!-- Badges priorité -->
                                <span x-show="isOldPackage(package)" 
                                      class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full animate-pulse">
                                    ⏰ ANCIEN
                                </span>
                                <span x-show="isHighCod(package)" 
                                      class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    💰 COD+
                                </span>
                                <span x-show="isAcceptedToday(package)" 
                                      class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    🕐 Nouveau
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

                <!-- Détails du pickup -->
                <div class="p-4">
                    <!-- Informations expéditeur/fournisseur -->
                    <div class="mb-4">
                        <div class="flex items-center mb-3">
                            <div class="text-sm font-medium text-orange-600">📦 POINT DE COLLECTE</div>
                        </div>
                        
                        <div class="bg-orange-50 p-3 rounded-xl border-l-4 border-orange-400">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm" x-text="package.supplier_data?.name || package.sender_data?.name || 'N/A'"></p>
                                    <p class="text-xs text-gray-600" x-text="package.supplier_data?.phone || package.sender_data?.phone || 'N/A'"></p>
                                    <p class="text-xs text-orange-600 font-medium mt-1" x-text="package.delegation_from?.name || 'N/A'"></p>
                                    <p class="text-xs text-gray-700 mt-1" x-text="package.pickup_address || package.sender_data?.address || 'Adresse non spécifiée'"></p>
                                    
                                    <!-- Notes pickup si disponibles -->
                                    <div x-show="package.pickup_notes" class="mt-2 pt-2 border-t border-orange-200">
                                        <p class="text-xs font-medium text-orange-700">📝 Notes:</p>
                                        <p class="text-xs text-orange-800" x-text="package.pickup_notes"></p>
                                    </div>
                                </div>
                                
                                <!-- Distance estimation si géolocalisation disponible -->
                                <div class="text-right ml-3">
                                    <div class="text-xs text-gray-500">Distance</div>
                                    <div class="text-sm font-medium text-blue-600">~2.1km</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations destinataire (aperçu) -->
                    <div class="mb-4">
                        <div class="text-sm font-medium text-green-600 mb-2">🎯 DESTINATION</div>
                        <div class="bg-green-50 p-3 rounded-xl border-l-4 border-green-400">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900" x-text="package.recipient_data?.name || 'N/A'"></p>
                                <p class="text-xs text-gray-600" x-text="package.recipient_data?.phone || 'N/A'"></p>
                                <p class="text-xs text-green-600 font-medium" x-text="package.delegation_to?.name || 'N/A'"></p>
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
                            
                            <!-- Heure d'acceptation -->
                            <div class="text-right ml-3">
                                <div class="text-xs text-gray-500">Accepté</div>
                                <div class="text-sm font-medium text-purple-600" x-text="formatAcceptedTime(package.assigned_at)"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions principales -->
                    <div class="flex items-center space-x-3">
                        <!-- Marquer comme collecté (Action principale) -->
                        <button @click="markAsCollected(package)" 
                                :disabled="processing === package.id"
                                class="flex-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white py-4 px-4 rounded-xl font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="processing !== package.id" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>MARQUER COLLECTÉ</span>
                            </span>
                            <span x-show="processing === package.id" class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Collecte...</span>
                            </span>
                        </button>

                        <!-- Actions secondaires -->
                        <div class="flex space-x-2">
                            <!-- Scanner ce colis -->
                            <button @click="scanSpecificPackage(package.package_code)" 
                                    class="bg-purple-100 text-purple-600 p-3 rounded-xl hover:bg-purple-200 transition-colors"
                                    title="Scanner ce colis">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                                </svg>
                            </button>

                            <!-- Voir détails -->
                            <button @click="viewPackageDetails(package)" 
                                    class="bg-blue-100 text-blue-600 p-3 rounded-xl hover:bg-blue-200 transition-colors"
                                    title="Voir détails">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <!-- Navigation GPS -->
                            <button @click="openNavigation(package)" 
                                    class="bg-orange-100 text-orange-600 p-3 rounded-xl hover:bg-orange-200 transition-colors"
                                    title="Navigation GPS">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                            
                            <!-- Toggle sélection -->
                            <button @click="toggleSelectionMode(package.id)" 
                                    :class="isSelectionMode ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'"
                                    class="p-3 rounded-xl hover:bg-opacity-80 transition-colors"
                                    title="Mode sélection">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span x-show="activeFilter === ''">Aucun pickup accepté</span>
                <span x-show="activeFilter !== ''">Aucun résultat pour ce filtre</span>
            </h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                <span x-show="activeFilter === ''">Vous n'avez pas encore accepté de pickup, ou vous les avez tous collectés.</span>
                <span x-show="activeFilter !== ''">Essayez d'ajuster vos filtres ou de rafraîchir la liste.</span>
            </p>
            
            <div class="space-y-3">
                <a href="{{ route('deliverer.pickups.available') }}" 
                   class="inline-block bg-purple-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-purple-600 transition-colors">
                    🔍 Voir Pickups Disponibles
                </a>
                <div x-show="activeFilter !== ''">
                    <button @click="clearFilters()" 
                            class="text-purple-600 hover:text-purple-800 underline">
                        Effacer les filtres
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Collecte Simple -->
    <div x-show="showCollectModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showCollectModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Marquer comme Collecté</h3>
                <button @click="showCollectModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div x-show="selectedPackageForCollect" class="space-y-4">
                <!-- Package Info -->
                <div class="bg-purple-50 p-4 rounded-xl text-center">
                    <p class="text-sm text-purple-600 font-medium">Colis</p>
                    <p class="text-xl font-bold text-purple-900" x-text="selectedPackageForCollect?.package_code"></p>
                    <p class="text-lg font-semibold text-purple-700" x-text="formatAmount(selectedPackageForCollect?.cod_amount)"></p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de collecte (optionnel)</label>
                    <textarea x-model="collectForm.notes" 
                              placeholder="Notes sur la collecte..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                              rows="3"></textarea>
                </div>

                <!-- Photo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de collecte (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handleCollectPhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="collectPhotoInput">
                        
                        <div x-show="!collectForm.photoPreview" @click="$refs.collectPhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Appuyer pour prendre une photo</p>
                        </div>
                        
                        <div x-show="collectForm.photoPreview" class="relative">
                            <img :src="collectForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removeCollectPhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 pt-4">
                    <button @click="submitCollect()" :disabled="processing"
                            class="flex-1 bg-emerald-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                        <span x-show="!processing">✅ Confirmer Collecte</span>
                        <span x-show="processing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Collecte en cours...
                        </span>
                    </button>
                    <button @click="showCollectModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Collecte en Lot -->
    <div x-show="showBulkCollectModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showBulkCollectModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Collecte en Lot</h3>
                <button @click="showBulkCollectModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Sélection automatique -->
                <div class="bg-blue-50 p-4 rounded-xl">
                    <h4 class="font-semibold text-blue-800 mb-2">Sélection automatique</h4>
                    <div class="space-y-2">
                        <button @click="autoSelectByDelegation()" 
                                class="w-full bg-blue-100 text-blue-700 py-2 px-3 rounded-lg hover:bg-blue-200 transition-colors text-left">
                            📍 Même délégation (optimisation de route)
                        </button>
                        <button @click="autoSelectByTime()" 
                                class="w-full bg-orange-100 text-orange-700 py-2 px-3 rounded-lg hover:bg-orange-200 transition-colors text-left">
                            ⏰ Les plus anciens (priorité)
                        </button>
                        <button @click="autoSelectByCod()" 
                                class="w-full bg-green-100 text-green-700 py-2 px-3 rounded-lg hover:bg-green-200 transition-colors text-left">
                            💰 COD élevé (priorité financière)
                        </button>
                    </div>
                </div>

                <!-- Liste des colis sélectionnés -->
                <div x-show="selectedPackages.length > 0">
                    <h4 class="font-medium text-gray-700 mb-2" x-text="`Colis sélectionnés (${selectedPackages.length})`"></h4>
                    <div class="max-h-40 overflow-y-auto space-y-2">
                        <template x-for="packageId in selectedPackages" :key="packageId">
                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                <div class="flex-1">
                                    <span class="text-sm font-medium" x-text="getPackageById(packageId)?.package_code"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="formatAmount(getPackageById(packageId)?.cod_amount)"></span>
                                </div>
                                <button @click="removeFromSelection(packageId)" 
                                        class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Notes pour le lot -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes pour ce lot</label>
                    <textarea x-model="bulkCollectForm.notes" 
                              placeholder="Notes générales pour cette collecte en lot..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                              rows="3"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 pt-4">
                    <button @click="submitBulkCollect()" 
                            :disabled="selectedPackages.length === 0 || processing"
                            class="flex-1 bg-emerald-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                        <span x-show="!processing" x-text="`Collecter ${selectedPackages.length} colis`"></span>
                        <span x-show="processing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Collecte en cours...
                        </span>
                    </button>
                    <button @click="showBulkCollectModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pull to Refresh Indicator -->
    <div x-show="refreshing" class="fixed top-24 left-1/2 transform -translate-x-1/2 z-40">
        <div class="bg-purple-500 text-white px-6 py-3 rounded-full shadow-xl flex items-center space-x-3">
            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span class="font-medium">Actualisation...</span>
        </div>
    </div>
</div>

<script>
function myPickupsApp() {
    return {
        // Données principales
        packages: @json($packages->items()),
        delegations: @json($delegations ?? []),
        filteredPackages: [],
        
        // États
        loading: false,
        refreshing: false,
        processing: null,
        
        // Mode sélection
        isSelectionMode: false,
        selectedPackages: [],
        
        // Filtres
        showFilters: false,
        activeFilter: '',
        searchQuery: '',
        filters: {
            delegationFrom: '',
            codMin: '',
            codMax: '',
            dateFrom: '',
            dateTo: ''
        },
        
        // Statistiques
        todayCount: 0,
        highCodCount: 0,
        oldCount: 0,
        totalCod: 0,
        oldestPackage: null,
        
        // Modals
        showCollectModal: false,
        showBulkCollectModal: false,
        selectedPackageForCollect: null,
        
        // Forms
        collectForm: {
            notes: '',
            photo: null,
            photoPreview: null
        },
        
        bulkCollectForm: {
            notes: ''
        },

        init() {
            this.applyFilters();
            this.calculateStats();
            this.setupPullToRefresh();
            this.startAutoRefresh();
            
            console.log('My Pickups App initialisé:', {
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
                    (pkg.supplier_data?.name || pkg.sender_data?.name || '').toLowerCase().includes(query) ||
                    (pkg.supplier_data?.phone || pkg.sender_data?.phone || '').toLowerCase().includes(query) ||
                    (pkg.delegation_from?.name || '').toLowerCase().includes(query) ||
                    (pkg.content_description || '').toLowerCase().includes(query)
                );
            }
            
            // Filtres par délégation
            if (this.filters.delegationFrom) {
                filtered = filtered.filter(pkg => 
                    pkg.delegation_from?.id == this.filters.delegationFrom
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
            
            // Filtres par date
            if (this.filters.dateFrom) {
                filtered = filtered.filter(pkg => 
                    new Date(pkg.assigned_at) >= new Date(this.filters.dateFrom)
                );
            }
            
            if (this.filters.dateTo) {
                filtered = filtered.filter(pkg => 
                    new Date(pkg.assigned_at) <= new Date(this.filters.dateTo + 'T23:59:59')
                );
            }
            
            // Filtres rapides
            switch (this.activeFilter) {
                case 'today':
                    const today = new Date().toDateString();
                    filtered = filtered.filter(pkg => 
                        new Date(pkg.assigned_at).toDateString() === today
                    );
                    break;
                case 'high_cod':
                    filtered = filtered.filter(pkg => parseFloat(pkg.cod_amount) >= 50);
                    break;
                case 'same_delegation':
                    // Grouper par délégation la plus fréquente
                    const delegationCounts = {};
                    this.packages.forEach(pkg => {
                        const delId = pkg.delegation_from?.id;
                        if (delId) {
                            delegationCounts[delId] = (delegationCounts[delId] || 0) + 1;
                        }
                    });
                    const mostCommonDelegation = Object.keys(delegationCounts).reduce((a, b) => 
                        delegationCounts[a] > delegationCounts[b] ? a : b, null);
                    if (mostCommonDelegation) {
                        filtered = filtered.filter(pkg => 
                            pkg.delegation_from?.id == mostCommonDelegation
                        );
                    }
                    break;
                case 'old':
                    filtered = filtered.filter(pkg => this.isOldPackage(pkg));
                    break;
            }
            
            // Tri : anciens en premier, puis par heure d'acceptation
            filtered.sort((a, b) => {
                const aOld = this.isOldPackage(a);
                const bOld = this.isOldPackage(b);
                
                if (aOld && !bOld) return -1;
                if (!aOld && bOld) return 1;
                
                // Si même priorité, trier par ancienneté d'acceptation
                return new Date(a.assigned_at) - new Date(b.assigned_at);
            });
            
            this.filteredPackages = filtered;
        },

        clearFilters() {
            this.activeFilter = '';
            this.searchQuery = '';
            this.filters = {
                delegationFrom: '',
                codMin: '',
                codMax: '',
                dateFrom: '',
                dateTo: ''
            };
            this.applyFilters();
        },

        // ==================== STATISTIQUES ====================
        
        calculateStats() {
            const today = new Date().toDateString();
            
            this.todayCount = this.packages.filter(pkg => 
                new Date(pkg.assigned_at).toDateString() === today
            ).length;
            
            this.highCodCount = this.packages.filter(pkg => 
                parseFloat(pkg.cod_amount) >= 50
            ).length;
            
            this.oldCount = this.packages.filter(pkg => 
                this.isOldPackage(pkg)
            ).length;
            
            this.totalCod = this.packages.reduce((sum, pkg) => 
                sum + parseFloat(pkg.cod_amount || 0), 0
            );
            
            // Trouver le plus ancien
            if (this.packages.length > 0) {
                const oldest = this.packages.reduce((oldest, pkg) => 
                    new Date(pkg.assigned_at) < new Date(oldest.assigned_at) ? pkg : oldest
                );
                this.oldestPackage = {
                    ...oldest,
                    days_old: Math.floor((new Date() - new Date(oldest.assigned_at)) / (1000 * 60 * 60 * 24))
                };
            }
        },

        isOldPackage(package) {
            const assignedAt = new Date(package.assigned_at);
            const hoursAgo = (new Date() - assignedAt) / (1000 * 60 * 60);
            return hoursAgo > 24; // Ancien si accepté il y a plus de 24h
        },

        isHighCod(package) {
            return parseFloat(package.cod_amount) >= 50;
        },

        isAcceptedToday(package) {
            const today = new Date().toDateString();
            return new Date(package.assigned_at).toDateString() === today;
        },

        // ==================== ACTIONS PRINCIPALES ====================
        
        async markAsCollected(package) {
            this.selectedPackageForCollect = package;
            this.showCollectModal = true;
        },

        async submitCollect() {
            if (this.processing || !this.selectedPackageForCollect) return;
            
            this.processing = this.selectedPackageForCollect.id;
            
            try {
                const formData = new FormData();
                formData.append('pickup_notes', this.collectForm.notes);
                if (this.collectForm.photo) {
                    formData.append('pickup_photo', this.collectForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.selectedPackageForCollect.id}/pickup`, {
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
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackageForCollect.id);
                    this.applyFilters();
                    this.calculateStats();
                    
                    // Fermer modal et réinitialiser
                    this.showCollectModal = false;
                    this.resetCollectForm();
                } else {
                    this.showToast(data.message || 'Erreur lors de la collecte', 'error');
                }
            } catch (error) {
                console.error('Erreur collecte:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processing = null;
        },

        resetCollectForm() {
            this.collectForm = {
                notes: '',
                photo: null,
                photoPreview: null
            };
            this.selectedPackageForCollect = null;
        },

        // ==================== SÉLECTION MULTIPLE ====================
        
        toggleSelectionMode(packageId = null) {
            this.isSelectionMode = !this.isSelectionMode;
            if (!this.isSelectionMode) {
                this.selectedPackages = [];
            } else if (packageId) {
                this.selectedPackages = [packageId];
            }
        },

        exitSelectionMode() {
            this.isSelectionMode = false;
            this.selectedPackages = [];
        },

        toggleSelection(packageId) {
            const index = this.selectedPackages.indexOf(packageId);
            if (index > -1) {
                this.selectedPackages.splice(index, 1);
            } else {
                this.selectedPackages.push(packageId);
            }
        },

        selectAll() {
            this.selectedPackages = this.filteredPackages.map(pkg => pkg.id);
        },

        clearSelection() {
            this.selectedPackages = [];
        },

        removeFromSelection(packageId) {
            const index = this.selectedPackages.indexOf(packageId);
            if (index > -1) {
                this.selectedPackages.splice(index, 1);
            }
        },

        getPackageById(packageId) {
            return this.packages.find(pkg => pkg.id === packageId);
        },

        // ==================== SÉLECTION AUTOMATIQUE ====================
        
        autoSelectByDelegation() {
            // Sélectionner tous les colis de la délégation la plus fréquente
            const delegationCounts = {};
            this.filteredPackages.forEach(pkg => {
                const delId = pkg.delegation_from?.id;
                if (delId) {
                    delegationCounts[delId] = (delegationCounts[delId] || 0) + 1;
                }
            });
            
            const mostCommonDelegation = Object.keys(delegationCounts).reduce((a, b) => 
                delegationCounts[a] > delegationCounts[b] ? a : b, null);
            
            if (mostCommonDelegation) {
                this.selectedPackages = this.filteredPackages
                    .filter(pkg => pkg.delegation_from?.id == mostCommonDelegation)
                    .map(pkg => pkg.id);
            }
        },

        autoSelectByTime() {
            // Sélectionner les 5 plus anciens
            const sortedByTime = [...this.filteredPackages].sort((a, b) => 
                new Date(a.assigned_at) - new Date(b.assigned_at)
            );
            this.selectedPackages = sortedByTime.slice(0, 5).map(pkg => pkg.id);
        },

        autoSelectByCod() {
            // Sélectionner les colis avec COD >= 50 DT
            this.selectedPackages = this.filteredPackages
                .filter(pkg => parseFloat(pkg.cod_amount) >= 50)
                .map(pkg => pkg.id);
        },

        async submitBulkCollect() {
            if (this.selectedPackages.length === 0 || this.processing) return;
            
            this.processing = true;
            
            try {
                const packageCodes = this.selectedPackages.map(id => 
                    this.getPackageById(id)?.package_code
                ).filter(Boolean);

                const response = await fetch('/deliverer/packages/scan-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        codes: packageCodes,
                        action: 'pickup',
                        notes: this.bulkCollectForm.notes
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const successCount = data.summary?.success || 0;
                    this.showToast(`${successCount} colis collectés avec succès!`, 'success');
                    
                    // Retirer les colis collectés de la liste
                    this.packages = this.packages.filter(p => 
                        !this.selectedPackages.includes(p.id)
                    );
                    this.applyFilters();
                    this.calculateStats();
                    
                    // Fermer modal et réinitialiser
                    this.showBulkCollectModal = false;
                    this.exitSelectionMode();
                    this.bulkCollectForm.notes = '';
                } else {
                    this.showToast(data.message || 'Erreur lors de la collecte en lot', 'error');
                }
            } catch (error) {
                console.error('Erreur collecte en lot:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processing = false;
        },

        // ==================== ACTIONS SECONDAIRES ====================
        
        async refreshData() {
            this.refreshing = true;
            
            try {
                const response = await fetch('{{ route("deliverer.pickups.mine") }}?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applyFilters();
                    this.calculateStats();
                    this.showToast('Pickups mis à jour', 'success');
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
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        openNavigation(package) {
            const address = package.pickup_address || package.sender_data?.address || '';
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
        
        handleCollectPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.collectForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.collectForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeCollectPhoto() {
            this.collectForm.photo = null;
            this.collectForm.photoPreview = null;
            this.$refs.collectPhotoInput.value = '';
        },

        // ==================== UI HELPERS ====================
        
        getPackagePriorityClass(package) {
            if (this.isOldPackage(package)) return 'ring-2 ring-red-300 shadow-red-100';
            if (this.isHighCod(package)) return 'ring-1 ring-green-300 shadow-green-100';
            if (this.isAcceptedToday(package)) return 'ring-1 ring-blue-300 shadow-blue-100';
            return '';
        },

        getHeaderBackgroundClass(package) {
            if (this.isOldPackage(package)) return 'bg-gradient-to-r from-red-50 to-pink-50';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-50 to-emerald-50';
            if (this.isAcceptedToday(package)) return 'bg-gradient-to-r from-blue-50 to-indigo-50';
            return 'bg-gradient-to-r from-purple-50 to-indigo-50';
        },

        getIconBackgroundClass(package) {
            if (this.isOldPackage(package)) return 'bg-gradient-to-r from-red-500 to-red-600';
            if (this.isHighCod(package)) return 'bg-gradient-to-r from-green-500 to-green-600';
            if (this.isAcceptedToday(package)) return 'bg-gradient-to-r from-blue-500 to-blue-600';
            return 'bg-gradient-to-r from-purple-500 to-purple-600';
        },

        getCodColorClass(amount) {
            const cod = parseFloat(amount);
            if (cod >= 100) return 'text-red-600';
            if (cod >= 50) return 'text-green-600';
            return 'text-purple-600';
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

        formatAcceptedTime(timestamp) {
            const date = new Date(timestamp);
            const today = new Date().toDateString();
            
            if (date.toDateString() === today) {
                return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
            }
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
                if (!this.refreshing && !this.processing) {
                    this.refreshData();
                }
            }, 60000); // Actualisation auto toutes les minutes
        },

        // ==================== NOTIFICATIONS ====================
        
        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-purple-500' : 'bg-red-500';
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