@extends('layouts.client')

@section('content')
<div class="flex h-screen bg-gray-100" x-data="packagesApp()">
    <!-- Sidebar des Colis -->
    <div class="w-64 bg-white shadow-lg border-r border-gray-200 flex flex-col">
        <!-- Header de la sidebar -->
        <div class="p-6 bg-gradient-to-r from-blue-600 to-emerald-600 text-white">
            <h2 class="text-xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Gestion Colis
            </h2>
            <p class="text-blue-100 text-sm mt-1">Toutes vos expéditions</p>
        </div>

        <!-- Navigation de la sidebar -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <!-- Actions Rapides -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Actions Rapides</h3>
                
                <!-- Créer Colis -->
                <a href="{{ route('client.packages.create') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.create') ? 'bg-emerald-100 text-emerald-700 shadow-sm' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="font-medium">Créer Colis</span>
                </a>

                <!-- Import CSV -->
                <a href="{{ route('client.packages.import.csv') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.import.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    <span class="font-medium">Importer en Gros</span>
                    <span class="ml-auto text-xs bg-purple-200 text-purple-700 px-2 py-1 rounded-full">CSV</span>
                </a>
            </div>

            <!-- Filtres par Statut -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Filtres</h3>
                
                <!-- Tous les Colis -->
                <a href="{{ route('client.packages.index') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ !request()->has('filter') && request()->routeIs('client.packages.index') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="font-medium">Tous les Colis</span>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded-full" x-text="stats.total_packages || '0'"></span>
                </a>

                <!-- Colis en Attente -->
                <a href="{{ route('client.packages.pending') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.pending') ? 'bg-orange-100 text-orange-700 shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">En Attente</span>
                    <span class="ml-auto text-xs bg-orange-200 text-orange-700 px-2 py-1 rounded-full" x-text="stats.pending_packages || '0'"></span>
                </a>

                <!-- Colis en Cours -->
                <a href="{{ route('client.packages.in-progress') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.in-progress') ? 'bg-indigo-100 text-indigo-700 shadow-sm' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="font-medium">En Cours</span>
                    <span class="ml-auto text-xs bg-indigo-200 text-indigo-700 px-2 py-1 rounded-full" x-text="stats.in_progress_packages || '0'"></span>
                </a>

                <!-- Colis Livrés -->
                <a href="{{ route('client.packages.delivered') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.delivered') ? 'bg-green-100 text-green-700 shadow-sm' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Livrés</span>
                    <span class="ml-auto text-xs bg-green-200 text-green-700 px-2 py-1 rounded-full" x-text="stats.delivered_packages || '0'"></span>
                </a>

                <!-- Colis Retournés -->
                <a href="{{ route('client.packages.returned') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.packages.returned') ? 'bg-red-100 text-red-700 shadow-sm' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/>
                    </svg>
                    <span class="font-medium">Retournés</span>
                    <span class="ml-auto text-xs bg-red-200 text-red-700 px-2 py-1 rounded-full" x-text="stats.returned_packages || '0'"></span>
                </a>
            </div>

            <!-- Outils et Rapports -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Outils</h3>
                
                <!-- Recherche Avancée -->
                <button @click="showAdvancedSearch = !showAdvancedSearch" 
                        class="sidebar-item w-full flex items-center px-3 py-2 rounded-lg transition-all duration-200 group text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="font-medium">Recherche</span>
                    <svg class="w-4 h-4 ml-auto transition-transform" :class="showAdvancedSearch ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Export/Rapports -->
                <a href="{{ route('client.packages.export') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">Exporter</span>
                </a>

                <!-- Adresses Sauvegardées -->
                <a href="{{ route('client.saved.addresses.index') }}" 
                   class="sidebar-item flex items-center px-3 py-2 rounded-lg transition-all duration-200 group {{ request()->routeIs('client.saved.addresses.*') ? 'bg-yellow-100 text-yellow-700 shadow-sm' : 'text-gray-700 hover:bg-yellow-50 hover:text-yellow-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-medium">Adresses</span>
                </a>
            </div>
        </nav>

        <!-- Stats résumées en bas -->
        <div class="p-4 bg-gray-50 border-t border-gray-200">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900" x-text="stats.total_packages || '0'"></div>
                <div class="text-xs text-gray-500">Total Colis</div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                    <div class="text-center">
                        <div class="font-semibold text-green-600" x-text="stats.delivered_packages || '0'"></div>
                        <div class="text-gray-500">Livrés</div>
                    </div>
                    <div class="text-center">
                        <div class="font-semibold text-orange-600" x-text="stats.pending_packages || '0'"></div>
                        <div class="text-gray-500">En attente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header avec fil d'Ariane -->
        <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <!-- Fil d'Ariane -->
                    <nav class="text-sm text-gray-500 mb-2">
                        <a href="{{ route('client.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">›</span>
                        <span class="text-gray-900 font-medium">@yield('breadcrumb', 'Colis')</span>
                    </nav>
                    
                    <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Gestion des Colis')</h1>
                    <p class="text-gray-600">@yield('page-description', 'Gérez vos expéditions Al-Amena Delivery')</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    @yield('header-actions')
                </div>
            </div>
        </div>

        <!-- Zone de recherche avancée (collapsible) -->
        <div x-show="showAdvancedSearch" x-transition 
             class="bg-blue-50 border-b border-blue-200 p-4">
            <form method="GET" action="{{ route('client.packages.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Code colis, destinataire..."
                       class="px-3 py-2 border border-blue-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                
                <select name="status" class="px-3 py-2 border border-blue-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="CREATED">Créés</option>
                    <option value="AVAILABLE">Disponibles</option>
                    <option value="ACCEPTED">Acceptés</option>
                    <option value="PICKED_UP">Collectés</option>
                    <option value="DELIVERED">Livrés</option>
                    <option value="RETURNED">Retournés</option>
                </select>
                
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-3 py-2 border border-blue-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                    🔍 Rechercher
                </button>
            </form>
        </div>

        <!-- Contenu de la page -->
        <main class="flex-1 overflow-auto bg-gray-50">
            <div class="p-6">
                @yield('packages-content')
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
function packagesApp() {
    return {
        showAdvancedSearch: false,
        stats: {
            total_packages: 0,
            pending_packages: 0,
            in_progress_packages: 0,
            delivered_packages: 0,
            returned_packages: 0
        },
        
        init() {
            this.loadStats();
            
            // Auto-refresh stats every 30 seconds
            setInterval(() => {
                this.loadStats();
            }, 30000);
        },
        
        async loadStats() {
            try {
                const response = await fetch('/client/api/packages/summary');
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        }
    }
}
</script>
@endpush
@endsection