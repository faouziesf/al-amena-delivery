@extends('layouts.client')

@section('title', 'Mes Colis')
@section('page-title', 'Gestion des Colis')
@section('page-description', 'Gérez tous vos envois en un seul endroit')

@section('content')
<!-- Mobile-first Main container with optimized spacing -->
<div class="min-h-screen pt-2 sm:pt-4 lg:pt-6 pb-16 sm:pb-20 px-3 sm:px-4 lg:px-8" x-data="packagesManager()" x-init="init()">

    <!-- Mobile-optimized Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 mb-4 sm:mb-6 lg:mb-8">
        <!-- Total Packages -->
        <div class="glass-enhanced rounded-lg sm:rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] touch-manipulation">
            <div class="p-2 sm:p-3 lg:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-1 sm:space-x-2 mb-1">
                            <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-purple-500 rounded-full animate-pulse"></div>
                            <p class="text-xs sm:text-sm font-bold text-purple-700 uppercase tracking-wide truncate">Total</p>
                        </div>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-purple-900 leading-tight">{{ $stats['total'] ?? 0 }}</p>
                        <p class="text-xs text-purple-600 mt-0.5 hidden sm:block">Colis créés</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Packages -->
        <div class="bg-gradient-to-br from-pink-50 to-pink-100 border border-pink-200 rounded-lg sm:rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] touch-manipulation">
            <div class="p-2 sm:p-3 lg:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-1 sm:space-x-2 mb-1">
                            <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-pink-500 rounded-full animate-pulse"></div>
                            <p class="text-xs sm:text-sm font-bold text-pink-700 uppercase tracking-wide truncate">Attente</p>
                        </div>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-pink-900 leading-tight">{{ $stats['pending'] ?? 0 }}</p>
                        <p class="text-xs text-pink-600 mt-0.5 hidden sm:block">À collecter</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-lg sm:rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] touch-manipulation">
            <div class="p-2 sm:p-3 lg:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-1 sm:space-x-2 mb-1">
                            <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                            <p class="text-xs sm:text-sm font-bold text-indigo-700 uppercase tracking-wide truncate">Transit</p>
                        </div>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-indigo-900 leading-tight">{{ $stats['in_progress'] ?? 0 }}</p>
                        <p class="text-xs text-indigo-600 mt-0.5 hidden sm:block">En cours</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivered -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-lg sm:rounded-xl overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] touch-manipulation">
            <div class="p-2 sm:p-3 lg:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-1 sm:space-x-2 mb-1">
                            <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full"></div>
                            <p class="text-xs sm:text-sm font-bold text-emerald-700 uppercase tracking-wide truncate">Livrés</p>
                        </div>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-emerald-900 leading-tight">{{ $stats['delivered'] ?? 0 }}</p>
                        <p class="text-xs text-emerald-600 mt-0.5 hidden sm:block">Terminés</p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick Actions Bar -->
    <div class="glass-enhanced rounded-lg sm:rounded-xl p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6 lg:mb-8 shadow-lg">
        <div class="flex items-center justify-between mb-3 sm:mb-4 lg:mb-6">
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-purple-900 flex items-center">
                <div class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="truncate">Actions Rapides</span>
            </h3>
            <div class="text-xs sm:text-sm text-purple-600 hidden sm:block">
                <span class="bg-purple-100 px-2 py-1 rounded-full">3 actions</span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
            <a href="{{ route('client.packages.create-fast') }}"
               class="group relative flex items-center p-3 sm:p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg sm:rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-xl touch-manipulation active:scale-95">
                <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="relative flex-1 min-w-0">
                    <p class="font-semibold text-sm sm:text-base lg:text-lg truncate">Créer Rapide</p>
                    <p class="text-xs sm:text-sm text-purple-100 truncate">Nouveau colis express</p>
                </div>
                <div class="relative ml-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('client.packages.create') }}"
               class="group relative flex items-center p-3 sm:p-4 bg-gradient-to-r from-pink-500 to-pink-600 text-white rounded-lg sm:rounded-xl hover:from-pink-600 hover:to-pink-700 transition-all duration-300 transform hover:scale-105 hover:shadow-xl touch-manipulation active:scale-95">
                <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="relative flex-1 min-w-0">
                    <p class="font-semibold text-sm sm:text-base lg:text-lg truncate">Créer Détaillé</p>
                    <p class="text-xs sm:text-sm text-pink-100 truncate">Colis avec options</p>
                </div>
                <div class="relative ml-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('client.manifests.index') }}"
               class="group relative flex items-center p-3 sm:p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg sm:rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 hover:shadow-xl touch-manipulation active:scale-95 sm:col-span-2 lg:col-span-1">
                <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 lg:w-10 lg:h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="relative flex-1 min-w-0">
                    <p class="font-semibold text-sm sm:text-base lg:text-lg truncate">Manifestes</p>
                    <p class="text-xs sm:text-sm text-indigo-100 truncate">Lots de colis</p>
                </div>
                <div class="relative ml-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>

    <!-- Enhanced Mobile-First Tab Navigation -->
    <div class="glass-enhanced rounded-lg sm:rounded-xl shadow-lg border border-purple-200/30 overflow-hidden mb-4 sm:mb-6 lg:mb-8">
        <div class="border-b border-purple-200/30">
            <nav class="flex overflow-x-auto scrollbar-hide touch-manipulation" aria-label="Tabs">
                <button @click="setActiveTab('all')"
                        :class="activeTab === 'all' ? 'border-purple-500 text-purple-600 bg-purple-50' : 'border-transparent text-gray-500 hover:text-purple-600 hover:border-purple-300'"
                        class="flex-1 min-w-0 py-3 sm:py-4 px-2 sm:px-4 lg:px-6 text-xs sm:text-sm font-medium border-b-2 focus:outline-none transition-all duration-300 hover:bg-purple-50/50 touch-manipulation active:bg-purple-100">
                    <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="font-semibold text-xs sm:text-sm">Tous</span>
                        <span class="bg-purple-100 text-purple-700 text-xs px-1 sm:px-1.5 lg:px-2 py-0.5 sm:py-1 rounded-full font-medium" x-text="stats.total"></span>
                    </div>
                </button>

                <button @click="setActiveTab('pending')"
                        :class="activeTab === 'pending' ? 'border-pink-500 text-pink-600 bg-pink-50' : 'border-transparent text-gray-500 hover:text-pink-600 hover:border-pink-300'"
                        class="flex-1 min-w-0 py-3 sm:py-4 px-2 sm:px-4 lg:px-6 text-xs sm:text-sm font-medium border-b-2 focus:outline-none transition-all duration-300 hover:bg-pink-50/50 touch-manipulation active:bg-pink-100">
                    <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-xs sm:text-sm">Attente</span>
                        <span x-show="stats.pending > 0" class="bg-pink-100 text-pink-700 text-xs px-1 sm:px-1.5 lg:px-2 py-0.5 sm:py-1 rounded-full font-medium animate-pulse" x-text="stats.pending"></span>
                    </div>
                </button>

                <button @click="setActiveTab('delivered')"
                        :class="activeTab === 'delivered' ? 'border-emerald-500 text-emerald-600 bg-emerald-50' : 'border-transparent text-gray-500 hover:text-emerald-600 hover:border-emerald-300'"
                        class="flex-1 min-w-0 py-3 sm:py-4 px-2 sm:px-4 lg:px-6 text-xs sm:text-sm font-medium border-b-2 focus:outline-none transition-all duration-300 hover:bg-emerald-50/50 touch-manipulation active:bg-emerald-100">
                    <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold text-xs sm:text-sm">Livrés</span>
                        <span x-show="stats.delivered > 0" class="bg-emerald-100 text-emerald-700 text-xs px-1 sm:px-1.5 lg:px-2 py-0.5 sm:py-1 rounded-full font-medium" x-text="stats.delivered"></span>
                    </div>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-3 sm:p-4 lg:p-6">
            <!-- All Packages Tab -->
            <div x-show="activeTab === 'all'">
                <!-- Mobile-optimized Smart Filters -->
                <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="glass-enhanced rounded-lg border border-purple-200/30 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6 lg:mb-8">
                    <form method="GET" action="{{ route('client.packages.index') }}" class="space-y-3 sm:space-y-4">
                        <input type="hidden" name="tab" value="all">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                            <div class="sm:col-span-2 lg:col-span-1">
                                <label class="block text-sm font-semibold text-purple-700 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Recherche
                                </label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Code, destinataire, ville..."
                                       class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 bg-white/80 backdrop-filter backdrop-blur-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📊 Statut</label>
                                <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors text-sm">
                                    <option value="">Tous les statuts</option>
                                    <option value="CREATED" @selected(request('status') == 'CREATED')>🆕 Créés</option>
                                    <option value="AVAILABLE" @selected(request('status') == 'AVAILABLE')>📋 Disponibles</option>
                                    <option value="PICKED_UP" @selected(request('status') == 'PICKED_UP')>🚚 Collectés</option>
                                    <option value="DELIVERED" @selected(request('status') == 'DELIVERED')>✅ Livrés</option>
                                    <option value="RETURNED" @selected(request('status') == 'RETURNED')>↩️ Retournés</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📅 À partir du</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors text-sm">
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 touch-manipulation active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    <span>Filtrer</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Mobile-optimized Filter Toggle & View Controls -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <button @click="showFilters = !showFilters"
                            class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors touch-manipulation active:bg-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span x-text="showFilters ? 'Masquer filtres' : 'Afficher filtres'"></span>
                    </button>

                    <div class="flex items-center justify-between w-full sm:w-auto space-x-3">
                        <span class="text-sm text-gray-500 font-medium">{{ $packages->total() ?? $packages->count() }} colis</span>
                        <button @click="toggleView()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors touch-manipulation active:scale-95">
                            <svg x-show="viewMode === 'cards'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <svg x-show="viewMode === 'list'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis trouvé', 'emptyIcon' => 'package'])
            </div>

            <!-- Pending Tab -->
            <div x-show="activeTab === 'pending'">
                @if($activeTab === 'pending')
                    @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => true, 'emptyMessage' => 'Aucun colis en attente de collecte', 'emptyIcon' => 'clock'])
                @endif
            </div>

            <!-- Delivered Tab -->
            <div x-show="activeTab === 'delivered'">
                @if($activeTab === 'delivered')
                    @include('client.packages.partials.packages-list', ['packages' => $packages, 'showBulkActions' => false, 'emptyMessage' => 'Aucun colis livré', 'emptyIcon' => 'check'])
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Mobile-First Floating Actions for Selected Items -->
<div x-show="selectedPackages.length > 0"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-full"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-filter backdrop-blur-sm border-t border-gray-200 p-3 sm:p-4 z-50 safe-area-bottom shadow-2xl">

    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between space-x-3 sm:space-x-4">
            <div class="flex items-center space-x-2 sm:space-x-3">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-purple-600" x-text="selectedPackages.length"></span>
                </div>
                <span class="text-sm font-medium text-gray-700">sélectionné(s)</span>
            </div>

            <div class="flex items-center space-x-2">
                <button @click="printSelected()"
                        class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-lg transition-all duration-300 touch-manipulation active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span class="hidden sm:inline">Imprimer</span>
                </button>

                <button @click="deleteSelected()" :disabled="!canDeleteSelected"
                        :class="canDeleteSelected ? 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-4 py-2 font-medium rounded-lg transition-all duration-300 touch-manipulation active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span class="hidden sm:inline">Supprimer</span>
                </button>

                <button @click="clearSelection()"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors touch-manipulation active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function packagesManager() {
    return {
        activeTab: '{{ $activeTab ?? "all" }}',
        selectedPackages: [],
        allSelected: false,
        showFilters: false,
        viewMode: 'cards', // 'cards' or 'list'
        stats: {
            total: {{ $stats['total'] ?? 0 }},
            pending: {{ $stats['pending'] ?? 0 }},
            in_progress: {{ $stats['in_progress'] ?? 0 }},
            delivered: {{ $stats['delivered'] ?? 0 }},
        },

        init() {
            // Auto-show filters if there are filter parameters
            this.showFilters = {{ request()->hasAny(['search', 'status', 'date_from']) ? 'true' : 'false' }};

            // Load saved view mode
            const savedViewMode = localStorage.getItem('packages_view_mode');
            if (savedViewMode) {
                this.viewMode = savedViewMode;
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            this.selectedPackages = [];
            this.allSelected = false;

            const url = new URL(window.location);
            url.searchParams.set('tab', tab);

            // Handle specific tab redirections
            if (tab === 'pending') {
                url.searchParams.set('status', 'CREATED,AVAILABLE');
            } else if (tab === 'delivered') {
                url.searchParams.set('status', 'DELIVERED');
            } else {
                url.searchParams.delete('status');
            }

            history.pushState({}, '', url);

            // Reload for server-side filtering
            if (tab !== 'all' || this.shouldReload()) {
                window.location.href = url.toString();
            }
        },

        shouldReload() {
            return {{ !request()->has("search") && !request()->has("status") ? 'true' : 'false' }};
        },

        toggleView() {
            this.viewMode = this.viewMode === 'cards' ? 'list' : 'cards';
            localStorage.setItem('packages_view_mode', this.viewMode);
        },

        get canDeleteSelected() {
            if (this.selectedPackages.length === 0) return false;
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]:checked');
            return Array.from(checkboxes).every(cb => ['CREATED', 'AVAILABLE'].includes(cb.dataset.status));
        },

        toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]');
            this.allSelected = !this.allSelected;
            if (this.allSelected) {
                this.selectedPackages = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selectedPackages = [];
            }
        },

        clearSelection() {
            this.selectedPackages = [];
            this.allSelected = false;
        },

        printSelected() {
            if (this.selectedPackages.length === 0) return;

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
            if (this.selectedPackages.length === 0 || !this.canDeleteSelected) return;

            if (!confirm(`Confirmez-vous la suppression de ${this.selectedPackages.length} colis ?`)) return;

            try {
                const response = await fetch('{{ route("client.packages.bulk.destroy") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ package_ids: this.selectedPackages })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success notification
                    this.showNotification(`${data.deleted_count} colis supprimés avec succès!`, 'success');

                    // Reload page after short delay
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(`Erreur: ${data.message}`, 'error');
                }
            } catch (error) {
                this.showNotification('Erreur de connexion. Veuillez réessayer.', 'error');
            }
        },

        showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    }
}

// Delete single package function
function deletePackage(packageId, packageCode) {
    if (!confirm(`Confirmez-vous la suppression du colis ${packageCode} ?`)) return;

    fetch(`/client/packages/${packageId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erreur lors de la suppression: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion.');
    });
}
</script>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Enhanced glass morphism for better mobile display */
.glass-enhanced {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px) saturate(180%);
    -webkit-backdrop-filter: blur(25px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow:
        0 8px 32px rgba(139, 92, 246, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

/* Mobile-first responsive enhancements */
@media (max-width: 640px) {
    .pb-20 {
        padding-bottom: calc(5rem + env(safe-area-inset-bottom));
    }

    /* Better touch targets */
    .touch-manipulation {
        touch-action: manipulation;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        user-select: none;
        min-height: 44px; /* iOS touch target recommendation */
    }

    /* Optimize text readability on mobile */
    .mobile-text-optimize {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }

    /* Better focus visibility on mobile */
    button:focus-visible,
    a:focus-visible {
        outline: 2px solid #8B5CF6;
        outline-offset: 2px;
        border-radius: 6px;
    }

    /* Improved scroll performance */
    .scrollbar-hide {
        -webkit-overflow-scrolling: touch;
    }
}

/* Enhanced hover states for non-touch devices */
@media (hover: hover) and (pointer: fine) {
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.1),
                    0 10px 10px -5px rgba(139, 92, 246, 0.04);
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .hover-lift:hover {
        transform: none;
    }

    .hover-lift:active {
        transform: scale(0.98);
    }

    /* Larger tap targets for mobile */
    button, a, [role="button"] {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Smooth loading animation */
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.loading-shimmer {
    background: linear-gradient(90deg,
        transparent,
        rgba(139, 92, 246, 0.1),
        transparent
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

/* Performance optimizations */
.will-change-transform {
    will-change: transform;
}

.will-change-opacity {
    will-change: opacity;
}

/* Safe area support */
@supports (padding: max(0px)) {
    .safe-area-bottom {
        padding-bottom: max(1rem, env(safe-area-inset-bottom));
    }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
</style>
@endsection