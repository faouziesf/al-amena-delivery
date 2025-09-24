@extends('layouts.deliverer')

@section('title', 'Détails de la Collecte #' . $pickupRequest->id)
@section('page-title', 'Collecte #' . $pickupRequest->id)
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" x-data>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('deliverer.pickups.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Collecte #{{ $pickupRequest->id }}</h1>
                    @php
                        $statusConfig = [
                            'pending' => ['color' => 'orange', 'text' => 'En Attente', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'assigned' => ['color' => 'blue', 'text' => 'Assignée', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            'picked_up' => ['color' => 'green', 'text' => 'Collectée', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'cancelled' => ['color' => 'red', 'text' => 'Annulée', 'icon' => 'M6 18L18 6M6 6l12 12']
                        ];
                        $config = $statusConfig[$pickupRequest->status] ?? $statusConfig['pending'];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 dark:bg-{{ $config['color'] }}-900/50 dark:text-{{ $config['color'] }}-300">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                        {{ $config['text'] }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Créée le {{ $pickupRequest->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <div class="flex space-x-3 w-full sm:w-auto">
            @if($pickupRequest->status === 'pending' && $pickupRequest->assigned_deliverer_id !== Auth::id())
                <form action="{{ route('deliverer.pickups.assign', $pickupRequest) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" onclick="return confirm('Voulez-vous prendre en charge cette collecte ?')"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-purple-300 text-purple-800 border border-transparent rounded-lg shadow-sm text-sm font-medium hover:bg-purple-400 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Prendre en Charge
                    </button>
                </form>
            @endif

            @if($pickupRequest->status === 'assigned' && $pickupRequest->assigned_deliverer_id === Auth::id())
                <form action="{{ route('deliverer.pickups.complete', $pickupRequest) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirmer la collecte de tous les colis ?')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Marquer comme Collectée
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Informations de Collecte -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center mb-5">
                    <div class="w-10 h-10 bg-purple-200 dark:bg-purple-900/50 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Informations de Collecte</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">👤 Client</h3>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $pickupRequest->client->name }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $pickupRequest->client->email }}</p>
                            @if($pickupRequest->client->phone)
                                <p class="text-sm text-gray-600">📞 {{ $pickupRequest->client->phone }}</p>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">📍 Adresse de collecte</h3>
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg space-y-2">
                                <!-- Adresse depuis pickup_request -->
                                <p class="text-gray-800 dark:text-gray-200 font-medium">Adresse de collecte</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ $pickupRequest->pickup_address }}</p>
                                @if($pickupRequest->delegation_from)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pickupRequest->delegation_from }}</p>
                                @endif

                                @if($pickupRequest->pickup_contact_name)
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">👤 Contact sur place</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $pickupRequest->pickup_contact_name }}</p>
                                        @if($pickupRequest->pickup_phone)
                                            <p class="text-sm text-gray-600">📞 {{ $pickupRequest->pickup_phone }}</p>
                                        @endif
                                    </div>
                                @endif

                                @if($pickupRequest->pickup_notes)
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">📝 Notes</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $pickupRequest->pickup_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">📅 Date demandée</h3>
                            <p class="text-gray-700 dark:text-gray-300">{{ $pickupRequest->requested_pickup_date->format('d/m/Y à H:i') }}</p>
                        </div>

                        @if($pickupRequest->pickup_contact_name && !$pickupRequest->pickupAddress)
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">👤 Contact sur place</h3>
                            <p class="text-gray-700 dark:text-gray-300">{{ $pickupRequest->pickup_contact_name }}</p>
                            @if($pickupRequest->pickup_phone)
                                <p class="text-sm text-gray-600">📞 {{ $pickupRequest->pickup_phone }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                @if($pickupRequest->pickup_notes)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-200 mb-2">📝 Notes</h3>
                    <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">{{ $pickupRequest->pickup_notes }}</p>
                </div>
                @endif
            </div>

            <!-- Liste des Colis à Collecter -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-200 dark:bg-purple-900/50 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Colis à Collecter</h2>
                    </div>
                    <span class="px-3 py-1 bg-purple-200 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 text-sm font-medium rounded-full">
                        {{ $packages->count() }} colis
                    </span>
                </div>

                @if($packages->count() > 0)
                    <div class="space-y-4">
                        @foreach($packages as $package)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-purple-300 dark:hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-purple-200 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $package->package_code }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $package->recipient_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">{{ $package->recipient_city }} - {{ $package->recipient_delegation }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($package->cod_amount)
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($package->cod_amount, 3) }} TND</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">COD</p>
                                @else
                                    <p class="text-sm text-green-600">Livraison gratuite</p>
                                @endif
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300 mt-1">
                                    {{ ucfirst($package->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Résumé financier -->
                    <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-900 dark:text-gray-100">Total COD à collecter :</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($packages->sum('cod_amount'), 3) }} TND
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Aucun colis trouvé pour cette demande</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">

            <!-- Statut et Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <div class="flex items-center mb-5">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Historique</h2>
                </div>

                <div class="space-y-4">
                    <!-- Étape: Demande créée -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Demande créée</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pickupRequest->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($pickupRequest->assigned_deliverer_id)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Assignée à un livreur</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $pickupRequest->assignedDeliverer->name ?? 'Vous' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500">{{ $pickupRequest->assigned_at ? $pickupRequest->assigned_at->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($pickupRequest->status === 'picked_up')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Colis collectés</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-500">{{ $pickupRequest->picked_up_at ? $pickupRequest->picked_up_at->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions Rapides</h2>
                <div class="space-y-3">
                    <a href="{{ route('deliverer.pickups.index') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Toutes les Collectes
                    </a>

                    <a href="{{ route('deliverer.pickups.scan') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-300 text-purple-800 border border-transparent rounded-lg shadow-sm text-sm font-medium hover:bg-purple-400 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2 text-purple-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scanner QR
                    </a>

                    <!-- Bouton d'appel client -->
                    @if($pickupRequest->client->phone)
                        <a href="tel:{{ $pickupRequest->client->phone }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Appeler Client
                        </a>
                    @endif

                    <!-- Boutons d'appel contact pickup -->
                    @if($pickupRequest->pickupAddress && $pickupRequest->pickupAddress->phone)
                        <a href="tel:{{ $pickupRequest->pickupAddress->phone }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contact Pickup
                        </a>
                    @elseif($pickupRequest->pickup_phone && $pickupRequest->pickup_phone !== $pickupRequest->client->phone)
                        <a href="tel:{{ $pickupRequest->pickup_phone }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Contact Pickup
                        </a>
                    @endif

                    <!-- Bouton téléphone alternatif si disponible -->
                    @if($pickupRequest->pickupAddress && $pickupRequest->pickupAddress->tel2 && $pickupRequest->pickupAddress->tel2 !== $pickupRequest->pickupAddress->phone)
                        <a href="tel:{{ $pickupRequest->pickupAddress->tel2 }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-indigo-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Tel. Alternatif
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection