@extends('layouts.deliverer')

@section('title', 'Feuilles de Route')
@section('page-title', 'Feuilles de Route')
@section('page-description', 'Générer et gérer vos feuilles de route par délégation')

@section('content')
<div class="space-y-6" x-data="runSheetsManager()">
    
    <!-- Stats Dashboard -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="stats.total_sheets">{{ $todayStats['total_sheets'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Terminées</p>
                    <p class="text-2xl font-bold text-green-600" x-text="stats.completed_sheets">{{ $todayStats['completed_sheets'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Colis Pendants</p>
                    <p class="text-2xl font-bold text-orange-600" x-text="stats.pending_packages">{{ $todayStats['pending_packages'] }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Traités</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="stats.total_packages_today">{{ $todayStats['total_packages_today'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-xl p-6 shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Générer Nouvelle Feuille -->
            <div class="p-4 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-400 transition-colors">
                <button @click="showGenerateModal = true" 
                        class="w-full flex flex-col items-center text-center space-y-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Nouvelle Feuille de Route</h4>
                        <p class="text-sm text-gray-600">Générer une feuille par délégation</p>
                    </div>
                </button>
            </div>

            <!-- Scan Lot -->
            <div class="p-4 border-2 border-dashed border-green-300 rounded-lg hover:border-green-400 transition-colors">
                <button @click="$dispatch('open-scanner', { mode: 'batch' })" 
                        class="w-full flex flex-col items-center text-center space-y-3">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Scan par Lot</h4>
                        <p class="text-sm text-gray-600">Scanner plusieurs colis rapidement</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <form @submit.prevent="applyFilters()" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Délégation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délégation</label>
                    <select x-model="filters.delegation" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les délégations</option>
                        @foreach($delegations as $delegation)
                        <option value="{{ $delegation->id }}">{{ $delegation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select x-model="filters.status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="PENDING">En attente</option>
                        <option value="IN_PROGRESS">En cours</option>
                        <option value="COMPLETED">Terminée</option>
                    </select>
                </div>

                <!-- Date De -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de</label>
                    <input type="date" x-model="filters.date_from" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Date À -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date à</label>
                    <input type="date" x-model="filters.date_to" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    <button type="button" @click="resetFilters()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des Feuilles de Route -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Mes Feuilles de Route</h3>
            <span class="text-sm text-gray-500" x-text="`${filteredSheets.length} feuille(s)`"></span>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($runSheets as $runSheet)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- En-tête -->
                        <div class="flex items-center space-x-3 mb-3">
                            <h4 class="text-lg font-medium text-gray-900">#{{ $runSheet->sheet_code }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $runSheet->status === 'COMPLETED' ? 'bg-green-100 text-green-800' : 
                                   ($runSheet->status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $runSheet->status === 'COMPLETED' ? 'Terminée' : ($runSheet->status === 'IN_PROGRESS' ? 'En cours' : 'En attente') }}
                            </span>
                            @if($runSheet->printed_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Imprimée
                            </span>
                            @endif
                        </div>

                        <!-- Détails -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Délégation:</span>
                                <span class="block font-medium text-gray-900">{{ $runSheet->delegation->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Colis:</span>
                                <span class="block font-medium text-gray-900">{{ $runSheet->packages_count ?? 0 }} colis</span>
                            </div>
                            <div>
                                <span class="text-gray-500">COD Total:</span>
                                <span class="block font-medium text-gray-900">{{ number_format($runSheet->total_cod_amount ?? 0, 3) }} DT</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Date:</span>
                                <span class="block font-medium text-gray-900">{{ $runSheet->date ? $runSheet->date->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Types de colis -->
                        @if($runSheet->package_types && is_array($runSheet->package_types))
                        <div class="mt-3 flex flex-wrap gap-1">
                            @foreach($runSheet->package_types as $type)
                            <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">
                                {{ $type === 'pickups' ? 'Collectes' : ($type === 'deliveries' ? 'Livraisons' : 'Retours') }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        <!-- Progression -->
                        @if($runSheet->status === 'IN_PROGRESS' && $runSheet->completion_stats && is_array($runSheet->completion_stats))
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">Progression</span>
                                <span class="font-medium">{{ $runSheet->completion_stats['completion_rate'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full"
                                     style="width: {{ $runSheet->completion_stats['completion_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-2 ml-4">
                        <!-- Imprimer -->
                        <a href="{{ route('deliverer.runsheets.print', $runSheet) }}" 
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimer
                        </a>

                        @if($runSheet->status === 'IN_PROGRESS')
                        <!-- Marquer comme terminée -->
                        <button @click="markComplete({{ $runSheet->id }})" 
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Terminer
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <!-- État vide -->
            <div class="p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune feuille de route</h3>
                <p class="text-gray-600 mb-4">Générez votre première feuille de route pour organiser vos livraisons par zone.</p>
                <button @click="showGenerateModal = true" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle Feuille
                </button>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($runSheets->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $runSheets->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Génération Feuille -->
    <div x-show="showGenerateModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        
        <div class="bg-white rounded-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto"
             @click.away="showGenerateModal = false">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Générer Feuille de Route</h3>
                <button @click="showGenerateModal = false" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="generateRunSheet()" class="space-y-4">
                <!-- Délégation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Délégation *</label>
                    <select x-model="generateForm.delegation_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner une délégation</option>
                        @foreach($delegations as $delegation)
                        <option value="{{ $delegation->id }}">{{ $delegation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Types de colis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Types de colis *</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="generateForm.package_types" value="pickups"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Collectes (Pickups)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="generateForm.package_types" value="deliveries"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Livraisons</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="generateForm.package_types" value="returns"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Retours</span>
                        </label>
                    </div>
                </div>

                <!-- Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="generateForm.include_cod_summary"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Inclure résumé COD</span>
                        </label>
                    </div>
                </div>

                <!-- Tri -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                    <select x-model="generateForm.sort_by"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="address">Adresse</option>
                        <option value="cod_amount">Montant COD</option>
                        <option value="created_at">Date de création</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="showGenerateModal = false"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button type="submit" :disabled="generating"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        <span x-show="!generating">Générer</span>
                        <span x-show="generating" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Génération...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Complétion -->
    <div x-show="showCompleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        
        <div class="bg-white rounded-xl max-w-md w-full p-6"
             @click.away="showCompleteModal = false">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Marquer comme Terminée</h3>
                <button @click="showCompleteModal = false" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitComplete()" class="space-y-4">
                <!-- Statistiques -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Colis livrés</label>
                        <input type="number" x-model="completeForm.packages_delivered" min="0" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Colis retournés</label>
                        <input type="number" x-model="completeForm.packages_returned" min="0" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">COD total collecté (DT)</label>
                    <input type="number" x-model="completeForm.total_cod_collected" step="0.001" min="0" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes de fin (optionnel)</label>
                    <textarea x-model="completeForm.completion_notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Remarques, problèmes rencontrés, etc."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="showCompleteModal = false"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button type="submit" :disabled="completing"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors">
                        <span x-show="!completing">Confirmer</span>
                        <span x-show="completing">Traitement...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function runSheetsManager() {
    return {
        stats: {
            total_sheets: {{ $todayStats['total_sheets'] }},
            completed_sheets: {{ $todayStats['completed_sheets'] }},
            pending_packages: {{ $todayStats['pending_packages'] }},
            total_packages_today: {{ $todayStats['total_packages_today'] }}
        },
        
        showGenerateModal: false,
        showCompleteModal: false,
        generating: false,
        completing: false,
        
        filters: {
            delegation: '',
            status: '',
            date_from: '',
            date_to: ''
        },
        
        generateForm: {
            delegation_id: '',
            package_types: [],
            include_cod_summary: false,
            sort_by: 'address'
        },
        
        completeForm: {
            run_sheet_id: null,
            packages_delivered: 0,
            packages_returned: 0,
            total_cod_collected: 0,
            completion_notes: ''
        },
        
        filteredSheets: [],
        
        init() {
            this.loadStats();
        },
        
        async loadStats() {
            try {
                const response = await fetch('/deliverer/api/runsheets/stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data.today };
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },
        
        async generateRunSheet() {
            if (this.generateForm.package_types.length === 0) {
                alert('Veuillez sélectionner au moins un type de colis');
                return;
            }
            
            this.generating = true;
            
            try {
                const response = await fetch('/deliverer/runsheets/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.generateForm)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showToast(result.message, 'success');
                    this.showGenerateModal = false;
                    
                    // Rediriger vers l'impression
                    if (result.redirect) {
                        window.open(result.redirect, '_blank');
                    }
                    
                    // Recharger la page
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showToast(result.message, 'error');
                }
                
            } catch (error) {
                console.error('Erreur génération:', error);
                this.showToast('Erreur lors de la génération', 'error');
            } finally {
                this.generating = false;
            }
        },
        
        markComplete(runSheetId) {
            this.completeForm.run_sheet_id = runSheetId;
            this.showCompleteModal = true;
        },
        
        async submitComplete() {
            this.completing = true;
            
            try {
                const response = await fetch(`/deliverer/runsheets/${this.completeForm.run_sheet_id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.completeForm)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showToast(result.message, 'success');
                    this.showCompleteModal = false;
                    window.location.reload();
                } else {
                    this.showToast(result.message, 'error');
                }
                
            } catch (error) {
                console.error('Erreur complétion:', error);
                this.showToast('Erreur lors de la finalisation', 'error');
            } finally {
                this.completing = false;
            }
        },
        
        applyFilters() {
            // Recharger avec filtres
            const params = new URLSearchParams(this.filters);
            window.location.search = params.toString();
        },
        
        resetFilters() {
            this.filters = {
                delegation: '',
                status: '',
                date_from: '',
                date_to: ''
            };
            window.location.search = '';
        },
        
        showToast(message, type = 'success') {
            // Utiliser le système de toast global
            if (window.showToast) {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush
@endsection