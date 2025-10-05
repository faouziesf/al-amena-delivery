@extends('layouts.client')

@section('page-title', $page_title)
@section('page-description', $page_description)

@section('header-actions')
<div class="flex items-center space-x-3 flex-col sm:flex-row">
    @if($packages->count() > 0)
        <!-- Actions groupées selon le type de filtre -->
        @if($filter_type === 'pending')
            <button onclick="printSelected()" 
                    class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer Bons
            </button>
            
            <button onclick="deleteSelected()" 
                    class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
        @elseif($filter_type === 'delivered')
            <a href="{{ route('client.packages.export') }}?{{ request()->getQueryString() }}" 
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter CSV
            </a>
        @endif
        
        @if(in_array($filter_type, ['pending', 'in_progress', 'delivered']))
            <button onclick="printSelected()" 
                    class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer Sélection
            </button>
        @endif
    @endif
    
    <!-- Actions communes -->
    <a href="{{ route('client.packages.create') }}" 
       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouveau Colis
    </a>
</div>
@endsection

@section('content')
<div class="p-4 sm:p-5 lg:p-6" x-data="packagesFilter()">
    
    <!-- Filtres et Recherche -->
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border mb-6 p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
            <!-- Recherche -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Code colis, destinataire..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
            </div>

            <!-- Date début -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
            </div>

            <!-- Date fin -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
            </div>

            <!-- Bouton de recherche -->
            <div class="flex items-end flex-col sm:flex-row">
                <button type="submit" 
                        class="w-full px-4 sm:px-5 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                    🔍 Filtrer
                </button>
            </div>
        </form>
    </div>
    
    <!-- Statistiques rapides pour ce filtre -->
    <div class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center flex-col sm:flex-row">
                <div class="p-3 rounded-2xl 
                    {{ $filter_type === 'pending' ? 'bg-orange-100' : '' }}
                    {{ $filter_type === 'in_progress' ? 'bg-indigo-100' : '' }}
                    {{ $filter_type === 'delivered' ? 'bg-green-100' : '' }}
                    {{ $filter_type === 'returned' ? 'bg-red-100' : '' }}">
                    @if($filter_type === 'pending')
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($filter_type === 'in_progress')
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @elseif($filter_type === 'delivered')
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ $page_title }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $packages->total() }}</p>
                </div>
            </div>
        </div>

        <!-- Stats additionnelles selon le type -->
        @if($filter_type === 'delivered')
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center flex-col sm:flex-row">
                <div class="p-3 bg-emerald-100 rounded-2xl">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Revenus Total</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ number_format($packages->sum('cod_amount'), 3) }} DT</p>
                </div>
            </div>
        </div>
        @endif

        @if($filter_type === 'pending')
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center flex-col sm:flex-row">
                <div class="p-3 bg-yellow-100 rounded-2xl">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Attendent Pickup</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $packages->where('status', 'AVAILABLE')->count() }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats temporelles -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center flex-col sm:flex-row">
                <div class="p-3 bg-blue-100 rounded-2xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ce Mois</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $packages->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Montant moyen -->
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center flex-col sm:flex-row">
                <div class="p-3 bg-purple-100 rounded-2xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">COD Moyen</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ $packages->count() > 0 ? number_format($packages->avg('cod_amount'), 3) : '0' }} DT
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions en bloc (si applicable) -->
    <div x-show="selectedPackages.length > 0" x-transition 
         class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex items-center justify-between flex-col sm:flex-row">
        <div class="flex items-center space-x-4 flex-col sm:flex-row">
            <span class="text-blue-700 font-medium">
                <span x-text="selectedPackages.length"></span> colis sélectionné(s)
            </span>
            <button @click="clearSelection()" 
                    class="text-blue-600 hover:text-blue-800 text-sm underline">
                Désélectionner tout
            </button>
        </div>
        
        <div class="flex items-center space-x-3 flex-col sm:flex-row">
            <!-- Impression groupée -->
            <button @click="printSelected()" 
                    class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer bons
            </button>
            
            @if($filter_type === 'pending')
            <!-- Suppression groupée -->
            <button @click="deleteSelected()" 
                    class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer
            </button>
            @endif
        </div>
    </div>

    <!-- Liste des colis -->
    @if($packages->count() > 0)
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border overflow-hidden transition-all duration-300 hover:-translate-y-1">
        <!-- En-tête du tableau -->
        <div class="bg-gray-50 px-4 sm:px-5 lg:px-6 py-4 border-b flex items-center justify-between flex-col sm:flex-row">
            @if($filter_type === 'pending')
            <div class="flex items-center space-x-4 flex-col sm:flex-row">
                <label class="flex items-center flex-col sm:flex-row">
                    <input type="checkbox" @change="toggleSelectAll()" 
                           :checked="allSelected"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded min-h-[44px]">
                    <span class="ml-2 text-sm font-medium text-gray-700">Tout sélectionner</span>
                </label>
            </div>
            @endif
            
            <div class="text-sm text-gray-500 flex items-center space-x-4 flex-col sm:flex-row">
                <span>{{ $packages->total() }} colis - {{ $page_title }}</span>
                @if($filter_type === 'delivered')
                    <span class="text-emerald-600 font-medium">
                        Total COD: {{ number_format($packages->sum('cod_amount'), 3) }} DT
                    </span>
                @endif
            </div>
        </div>

        <!-- Corps du tableau -->
        <div class="divide-y divide-gray-200">
            @foreach($packages as $package)
            <div class="p-4 sm:p-5 lg:p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-4 flex-col sm:flex-row">
                    <!-- Checkbox (seulement pour les colis en attente) -->
                    @if($filter_type === 'pending')
                    <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}"
                           class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    @endif
                    
                    <!-- Informations principales -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-6 gap-3 sm:gap-2 sm:gap-3 lg:gap-4 lg:gap-6 items-center flex-col sm:flex-row">
                        <!-- Code et Date -->
                        <div>
                            <p class="font-bold text-lg text-gray-900">{{ $package->package_code }}</p>
                            <p class="text-sm text-gray-500">{{ $package->created_at->format('d/m/Y H:i') }}</p>
                            @if($package->isFromImport())
                                <span class="inline-block px-2 py-1 text-sm sm:text-xs bg-purple-100 text-purple-700 rounded-full mt-1">
                                    Import CSV
                                </span>
                            @endif
                        </div>

                        <!-- Pickup -->
                        <div>
                            @if($package->supplier_data && is_array($package->supplier_data))
                                <p class="text-sm font-medium text-gray-900">{{ $package->supplier_data['name'] ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $package->supplier_data['phone'] ?? 'N/A' }}</p>
                            @else
                                <p class="text-sm font-medium text-gray-900">{{ $package->sender_data['name'] ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $package->sender_data['phone'] ?? 'N/A' }}</p>
                            @endif
                            <p class="text-sm text-blue-600 font-medium">{{ $package->delegationFrom->name ?? 'N/A' }}</p>
                        </div>

                        <!-- Destinataire -->
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                            <p class="text-sm text-emerald-600 font-medium">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                        </div>

                        <!-- Contenu -->
                        <div>
                            <p class="text-sm text-gray-900 font-medium">{{ $package->content_description }}</p>
                            @if($package->hasSpecialRequirements())
                                <div class="flex items-center mt-2 space-x-2 flex-col sm:flex-row">
                                    @if($package->is_fragile)
                                        <span class="text-sm sm:text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full">🔸 Fragile</span>
                                    @endif
                                    @if($package->requires_signature)
                                        <span class="text-sm sm:text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">✍️ Signature</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- COD et Statut -->
                        <div class="text-center">
                            <p class="text-xl font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                            <span class="inline-block px-3 py-1 text-sm font-medium rounded-full mt-2
                                {{ $package->status === 'CREATED' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $package->status === 'AVAILABLE' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $package->status === 'ACCEPTED' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $package->status === 'PICKED_UP' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                {{ $package->status === 'DELIVERED' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $package->status === 'PAID' ? 'bg-green-200 text-green-900' : '' }}
                                {{ $package->status === 'RETURNED' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $package->status === 'REFUSED' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $package->status }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col space-y-2 flex-col sm:flex-row">
                            <!-- Voir détails -->
                            <a href="{{ route('client.packages.show', $package) }}" 
                               class="text-center px-3 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white text-sm font-medium rounded-2xl transition-colors">
                                Voir Détails
                            </a>

                            <!-- Imprimer bon -->
                            <a href="{{ route('client.packages.print', $package) }}" target="_blank"
                               class="text-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors">
                                Imprimer Bon
                            </a>

                            <!-- Actions secondaires -->
                            <div class="flex space-x-2 flex-col sm:flex-row">
                                <!-- Dupliquer -->
                                <form method="POST" action="{{ route('client.packages.duplicate', $package) }}" class="flex-1 flex-col sm:flex-row">
                                    @csrf
                                    <button type="submit" class="w-full text-orange-600 hover:text-orange-800 text-sm font-medium">
                                        Dupliquer
                                    </button>
                                </form>

                                <!-- Supprimer (si possible) -->
                                @if($package->canBeDeleted())
                                <form method="POST" action="{{ route('client.packages.destroy', $package) }}" class="flex-1 flex-col sm:flex-row"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce colis ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-red-600 hover:text-red-800 text-sm font-medium">
                                        Supprimer
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="px-4 sm:px-5 lg:px-6 py-4 border-t bg-gray-50">
            {{ $packages->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @else
    <!-- État vide -->
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border p-12 text-center transition-all duration-300 hover:-translate-y-1">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($filter_type === 'pending')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @elseif($filter_type === 'delivered')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            @endif
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun colis {{ strtolower($page_title) }}</h3>
        <p class="text-gray-500 mb-6">
            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                Aucun colis ne correspond à vos critères de recherche.
            @else
                Vous n'avez pas encore de colis avec ce statut.
            @endif
        </p>
        
        <div class="flex items-center justify-center space-x-4 flex-col sm:flex-row">
            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <a href="{{ request()->url() }}" 
                   class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-2xl text-sm font-medium transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 transition-all duration-300 hover:-translate-y-1 flex-col sm:flex-row">
                    Effacer les filtres
                </a>
            @endif
            
            <a href="{{ route('client.packages.create') }}" 
               class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-medium transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Créer un colis
            </a>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function packagesFilter() {
    return {
        selectedPackages: [],
        allSelected: false,
        
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedPackages = [];
                this.allSelected = false;
            } else {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]');
                this.selectedPackages = Array.from(checkboxes).map(cb => cb.value);
                this.allSelected = true;
            }
        },
        
        clearSelection() {
            this.selectedPackages = [];
            this.allSelected = false;
        },
        
        printSelected() {
            if (this.selectedPackages.length === 0) {
                alert('Aucun colis sélectionné');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("client.packages.print.multiple") }}';
            form.target = '_blank';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            this.selectedPackages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'package_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },
        
        async deleteSelected() {
            if (this.selectedPackages.length === 0) {
                alert('Aucun colis sélectionné');
                return;
            }
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${this.selectedPackages.length} colis ?`)) {
                return;
            }
            
            try {
                const response = await fetch('{{ route("client.packages.bulk.destroy") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        package_ids: this.selectedPackages
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                alert('Erreur de connexion');
            }
        }
    }
}

// Fonction globale pour l'impression
function printSelected() {
    const app = document.querySelector('[x-data*="packagesFilter"]').__x.$data;
    app.printSelected();
}

function deleteSelected() {
    const app = document.querySelector('[x-data*="packagesFilter"]').__x.$data;
    app.deleteSelected();
}
</script>
@endpush
@endsection