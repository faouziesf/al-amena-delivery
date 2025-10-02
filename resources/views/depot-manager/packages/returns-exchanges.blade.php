@extends('layouts.depot-manager')

@section('title', 'Retours & Échanges')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-orange-50">
    <!-- Header moderne -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-600 to-red-700 rounded-2xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Retours & Échanges</h1>
                        <p class="text-slate-500 text-sm">Gestion des colis retournés et des demandes d'échange</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshPackages()"
                            class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                    <a href="{{ route('depot-manager.packages.all') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Tous les Colis
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-red-100 text-sm font-medium">Total Retours</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['total_returns']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-orange-100 text-sm font-medium">Total Échanges</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['total_exchanges']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-blue-100 text-sm font-medium">Retours Aujourd'hui</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['returns_today']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-yellow-100 text-sm font-medium">Échanges En Attente</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($stats['exchanges_pending']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 mb-8">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                    </svg>
                    <span class="text-slate-600 font-medium">Filtres :</span>
                </div>

                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <select name="type" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Tous les types</option>
                        <option value="returns" {{ request('type') == 'returns' ? 'selected' : '' }}>Retours seulement</option>
                        <option value="exchanges" {{ request('type') == 'exchanges' ? 'selected' : '' }}>Échanges seulement</option>
                    </select>

                    <select name="gouvernorat" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Tous les gouvernorats</option>
                        <option value="Tunis" {{ request('gouvernorat') == 'Tunis' ? 'selected' : '' }}>Tunis</option>
                        <option value="Ariana" {{ request('gouvernorat') == 'Ariana' ? 'selected' : '' }}>Ariana</option>
                        <option value="Ben Arous" {{ request('gouvernorat') == 'Ben Arous' ? 'selected' : '' }}>Ben Arous</option>
                        <option value="Manouba" {{ request('gouvernorat') == 'Manouba' ? 'selected' : '' }}>Manouba</option>
                        <option value="Nabeul" {{ request('gouvernorat') == 'Nabeul' ? 'selected' : '' }}>Nabeul</option>
                        <option value="Zaghouan" {{ request('gouvernorat') == 'Zaghouan' ? 'selected' : '' }}>Zaghouan</option>
                        <option value="Bizerte" {{ request('gouvernorat') == 'Bizerte' ? 'selected' : '' }}>Bizerte</option>
                        <option value="Béja" {{ request('gouvernorat') == 'Béja' ? 'selected' : '' }}>Béja</option>
                        <option value="Jendouba" {{ request('gouvernorat') == 'Jendouba' ? 'selected' : '' }}>Jendouba</option>
                        <option value="Kef" {{ request('gouvernorat') == 'Kef' ? 'selected' : '' }}>Kef</option>
                        <option value="Siliana" {{ request('gouvernorat') == 'Siliana' ? 'selected' : '' }}>Siliana</option>
                        <option value="Kairouan" {{ request('gouvernorat') == 'Kairouan' ? 'selected' : '' }}>Kairouan</option>
                        <option value="Kasserine" {{ request('gouvernorat') == 'Kasserine' ? 'selected' : '' }}>Kasserine</option>
                        <option value="Sidi Bouzid" {{ request('gouvernorat') == 'Sidi Bouzid' ? 'selected' : '' }}>Sidi Bouzid</option>
                        <option value="Sousse" {{ request('gouvernorat') == 'Sousse' ? 'selected' : '' }}>Sousse</option>
                        <option value="Monastir" {{ request('gouvernorat') == 'Monastir' ? 'selected' : '' }}>Monastir</option>
                        <option value="Mahdia" {{ request('gouvernorat') == 'Mahdia' ? 'selected' : '' }}>Mahdia</option>
                        <option value="Sfax" {{ request('gouvernorat') == 'Sfax' ? 'selected' : '' }}>Sfax</option>
                        <option value="Gafsa" {{ request('gouvernorat') == 'Gafsa' ? 'selected' : '' }}>Gafsa</option>
                        <option value="Tozeur" {{ request('gouvernorat') == 'Tozeur' ? 'selected' : '' }}>Tozeur</option>
                        <option value="Kebili" {{ request('gouvernorat') == 'Kebili' ? 'selected' : '' }}>Kebili</option>
                        <option value="Gabès" {{ request('gouvernorat') == 'Gabès' ? 'selected' : '' }}>Gabès</option>
                        <option value="Médenine" {{ request('gouvernorat') == 'Médenine' ? 'selected' : '' }}>Médenine</option>
                        <option value="Tataouine" {{ request('gouvernorat') == 'Tataouine' ? 'selected' : '' }}>Tataouine</option>
                    </select>

                    <input type="text" name="search" placeholder="Rechercher..."
                           value="{{ request('search') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">

                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">

                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">

                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>

                    @if(request()->hasAny(['type', 'gouvernorat', 'search', 'date_from', 'date_to']))
                    <a href="{{ route('depot-manager.packages.returns-exchanges') }}"
                       class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Effacer
                    </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Section Échanges à Traiter (nouveaux échanges livrés) -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-orange-900">🔄 Échanges à Traiter</h3>
                        <p class="text-orange-600 text-sm">Colis d'échange livrés nécessitant la création de colis de retour</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-200 text-orange-800">
                        3 en attente
                    </span>
                </div>
            </div>

            <!-- Liste des échanges à traiter -->
            <div class="divide-y divide-slate-200">
                <!-- Exemple d'échange à traiter -->
                <div class="p-6 hover:bg-orange-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Icon échange -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Informations du colis d'échange -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold text-slate-900">AL240001</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        LIVRÉ (ÉCHANGE)
                                    </span>
                                </div>

                                <div class="flex items-center text-sm text-slate-500 space-x-4 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Ahmed Sassi
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Mohamed Ben Ali
                                    </span>
                                </div>

                                <div class="flex items-center text-sm text-slate-500 space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Livré le 02/10/2025 14:30
                                    </span>
                                    <span class="flex items-center text-orange-600 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        Ancien article collecté
                                    </span>
                                </div>

                                <div class="mt-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-sm text-orange-700">
                                        <strong>⚠️ Action requise:</strong> Créer un colis de retour pour l'ancien article collecté lors de cet échange.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <button onclick="createReturnPackage('AL240001')"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Créer Colis de Retour
                            </button>

                            <a href="#"
                               class="inline-flex items-center px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Détails
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Message si aucun échange à traiter -->
                <div class="p-8 text-center text-slate-500 hidden" id="no-exchanges">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <p class="text-sm">Aucun échange à traiter pour le moment</p>
                </div>
            </div>
        </div>

        <!-- Liste des retours/échanges -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-900">Retours & Échanges</h3>
                <p class="text-slate-500 text-sm">{{ $packages->total() }} élément(s) au total</p>
            </div>

            @if($packages->count() > 0)
            <div class="divide-y divide-slate-200">
                @foreach($packages as $package)
                <div class="p-6 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Icon et statut -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white
                                    @if($package->status === 'RETURNED') bg-gradient-to-r from-red-500 to-red-600
                                    @elseif($package->status === 'EXCHANGE_REQUESTED') bg-gradient-to-r from-yellow-500 to-yellow-600
                                    @elseif($package->status === 'EXCHANGE_PROCESSED') bg-gradient-to-r from-green-500 to-green-600
                                    @else bg-gradient-to-r from-blue-500 to-blue-600
                                    @endif">
                                    @if($package->status === 'RETURNED')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Informations du colis -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-semibold text-slate-900">{{ $package->package_code }}</h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($package->status === 'RETURNED') bg-red-100 text-red-800
                                        @elseif($package->status === 'EXCHANGE_REQUESTED') bg-yellow-100 text-yellow-800
                                        @elseif($package->status === 'EXCHANGE_PROCESSED') bg-green-100 text-green-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        @if($package->status === 'RETURNED') RETOUR
                                        @elseif($package->status === 'EXCHANGE_REQUESTED') ÉCHANGE DEMANDÉ
                                        @elseif($package->status === 'EXCHANGE_PROCESSED') ÉCHANGE TRAITÉ
                                        @else {{ $package->status }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center text-sm text-slate-500 space-x-4 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $package->recipient_name }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $package->recipient_phone }}
                                    </span>
                                    @if($package->assignedDeliverer)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $package->assignedDeliverer->first_name }} {{ $package->assignedDeliverer->last_name }}
                                    </span>
                                    @endif
                                </div>

                                <div class="flex items-center text-sm text-slate-500 space-x-4">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $package->returned_at ? $package->returned_at->format('d/m/Y H:i') : $package->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    @if($package->cod_amount > 0)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                        {{ number_format($package->cod_amount, 3) }} DT
                                    </span>
                                    @endif
                                </div>

                                @if($package->return_reason)
                                <div class="mt-3 p-3 bg-slate-50 rounded-lg">
                                    <p class="text-sm text-slate-600"><strong>Raison:</strong> {{ $package->return_reason }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="{{ route('depot-manager.packages.show', $package) }}"
                               class="inline-flex items-center px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Détails
                            </a>

                            <a href="{{ route('depot-manager.packages.return-receipt', $package) }}"
                               class="inline-flex items-center px-3 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition-colors text-sm font-medium"
                               target="_blank">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Bon Retour
                            </a>

                            @if($package->status === 'EXCHANGE_REQUESTED')
                            <button onclick="processExchange({{ $package->id }})"
                                    class="inline-flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Traiter Échange
                            </button>
                            @endif

                            @if($package->status === 'RETURNED' && !$package->return_processed_at)
                            <button onclick="processReturn({{ $package->id }})"
                                    class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Traiter Retour
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                {{ $packages->withQueryString()->links() }}
            </div>
            @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">Aucun retour ou échange trouvé</h3>
                <p class="text-slate-500">Il n'y a pas de retours ou échanges correspondant à vos critères actuels.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals pour traitement -->
<!-- Modal Traiter Retour -->
<div id="processReturnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Traiter le Retour</h3>
            <form id="processReturnForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Raison du retour</label>
                    <textarea name="return_reason" required
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                              rows="3" placeholder="Expliquez la raison du retour..."></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Action à effectuer</label>
                    <select name="return_action" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="return_to_sender">Retourner à l'expéditeur</option>
                        <option value="process_exchange">Traiter comme échange</option>
                        <option value="dispose">Mettre au rebut</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeProcessReturnModal()"
                            class="px-4 py-2 text-slate-600 hover:text-slate-800">Annuler</button>
                    <button type="submit"
                            class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">Traiter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Traiter Échange -->
<div id="processExchangeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Traiter l'Échange</h3>
            <form id="processExchangeForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Notes sur l'échange</label>
                    <textarea name="exchange_notes"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              rows="3" placeholder="Notes sur le traitement de l'échange..."></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Code du nouveau colis (optionnel)</label>
                    <input type="text" name="new_package_code"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Ex: PKG123456">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeProcessExchangeModal()"
                            class="px-4 py-2 text-slate-600 hover:text-slate-800">Annuler</button>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Traiter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function refreshPackages() {
    window.location.reload();
}

function processReturn(packageId) {
    const form = document.getElementById('processReturnForm');
    form.action = `/depot-manager/packages/${packageId}/process-return`;
    document.getElementById('processReturnModal').classList.remove('hidden');
}

function closeProcessReturnModal() {
    document.getElementById('processReturnModal').classList.add('hidden');
}

function processExchange(packageId) {
    const form = document.getElementById('processExchangeForm');
    form.action = `/depot-manager/packages/${packageId}/process-exchange`;
    document.getElementById('processExchangeModal').classList.remove('hidden');
}

function closeProcessExchangeModal() {
    document.getElementById('processExchangeModal').classList.add('hidden');
}

function createReturnPackage(originalPackageCode) {
    // Créer un modal pour la création du colis de retour
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Créer Colis de Retour</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Colis d'origine</label>
                <input type="text" value="${originalPackageCode}" disabled
                       class="w-full px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Description de l'ancien article</label>
                <textarea placeholder="Décrivez l'ancien article collecté..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          rows="3"></textarea>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Notes supplémentaires</label>
                <textarea placeholder="Notes pour le retour..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          rows="2"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeReturnPackageModal()"
                        class="px-4 py-2 text-slate-600 hover:text-slate-800">Annuler</button>
                <button onclick="confirmCreateReturnPackage('${originalPackageCode}')"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Créer Colis de Retour</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    window.currentReturnModal = modal;
}

function closeReturnPackageModal() {
    if (window.currentReturnModal) {
        document.body.removeChild(window.currentReturnModal);
        window.currentReturnModal = null;
    }
}

function confirmCreateReturnPackage(originalPackageCode) {
    const modal = window.currentReturnModal;
    const description = modal.querySelector('textarea[placeholder*="Décrivez"]').value;
    const notes = modal.querySelector('textarea[placeholder*="Notes"]').value;

    if (!description.trim()) {
        showNotification('Veuillez décrire l\'ancien article collecté.', 'error');
        return;
    }

    // Appel AJAX pour créer le colis de retour
    fetch('/depot-manager/packages/create-return-package', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            original_package_code: originalPackageCode,
            description: description,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Colis de retour ${data.return_package_code} créé avec succès!`, 'success');
            closeReturnPackageModal();
            // Optionnel: rafraîchir la page ou mettre à jour l'interface
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification(data.error || 'Erreur lors de la création du colis de retour.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur de connexion. Veuillez réessayer.', 'error');
    });
}

// Notification de succès
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('success') }}", 'success');
    });
@endif

// Notification d'erreur
@if(session('error') || $errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('error') ?? $errors->first() }}", 'error');
    });
@endif

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}
</script>
@endsection