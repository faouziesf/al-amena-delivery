@extends('layouts.client')

@section('title', 'Détails de la Demande #' . $pickupRequest->id)
@section('page-title', 'Demande de Collecte #' . $pickupRequest->id)
@section('page-description', 'Détails et suivi de votre demande de collecte')

@section('content')
<style>
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
.detail-section { animation: slideInUp 0.4s ease-out; transition: all 0.3s ease; }
.detail-section:hover { transform: translateY(-2px); }
.package-card { transition: all 0.3s ease; }
.package-card:hover { transform: translateY(-2px); }
.timeline-item { transition: all 0.3s ease; }
.timeline-item:hover { transform: translateX(5px); }
</style>

<div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <a href="{{ route('client.pickup-requests.index') }}" class="mr-4 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">Demande #{{ $pickupRequest->id }}</h1>
                    @php
                        $statusConfig = [
                            'pending' => ['color' => 'orange', 'text' => 'En Attente', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'assigned' => ['color' => 'blue', 'text' => 'Assignée', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            'picked_up' => ['color' => 'green', 'text' => 'Collectée', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'cancelled' => ['color' => 'red', 'text' => 'Annulée', 'icon' => 'M6 18L18 6M6 6l12 12']
                        ];
                        $config = $statusConfig[$pickupRequest->status] ?? $statusConfig['pending'];
                    @endphp
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                        </svg>
                        {{ $config['text'] }}
                    </span>
                </div>
                <p class="text-gray-600">Créée le {{ $pickupRequest->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        @if(in_array($pickupRequest->status, ['pending', 'assigned']))
            <form action="{{ route('client.pickup-requests.cancel', $pickupRequest) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-red-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler la Demande
                </button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Informations de Collecte -->
            <div class="detail-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Informations de Collecte</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">📍 Adresse de collecte</h3>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $pickupRequest->pickup_address }}</p>
                        </div>
                        @if($pickupRequest->delegation_from)
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">🏢 Délégation</h3>
                            <p class="text-gray-700">{{ $pickupRequest->delegation_from }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">📅 Date demandée</h3>
                            <p class="text-gray-700">{{ $pickupRequest->requested_pickup_date->format('d/m/Y à H:i') }}</p>
                        </div>
                        @if($pickupRequest->pickup_contact_name)
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">👤 Contact</h3>
                            <p class="text-gray-700">{{ $pickupRequest->pickup_contact_name }}</p>
                            @if($pickupRequest->pickup_phone)
                                <p class="text-sm text-gray-600">📞 {{ $pickupRequest->pickup_phone }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                @if($pickupRequest->pickup_notes)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">📝 Notes</h3>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $pickupRequest->pickup_notes }}</p>
                </div>
                @endif
            </div>

            <!-- Liste des Colis -->
            <div class="detail-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Colis à Collecter</h2>
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                        {{ $packages->count() }} colis
                    </span>
                </div>

                @if($packages->count() > 0)
                    <div class="space-y-4">
                        @foreach($packages as $package)
                        <div class="package-card flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $package->package_code }}</h3>
                                    <p class="text-sm text-gray-600">{{ $package->recipient_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $package->recipient_city }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($package->cod_amount)
                                    <p class="text-sm font-medium text-gray-900">{{ number_format($package->cod_amount, 2) }} TND</p>
                                    <p class="text-xs text-gray-500">COD</p>
                                @else
                                    <p class="text-sm text-green-600">Livraison gratuite</p>
                                @endif
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mt-1">
                                    {{ ucfirst($package->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-gray-600">Aucun colis trouvé pour cette demande</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">

            <!-- Statut et Timeline -->
            <div class="detail-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Suivi</h2>
                </div>

                <div class="space-y-4">
                    <!-- Étape: Demande créée -->
                    <div class="timeline-item flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Demande créée</h3>
                            <p class="text-xs text-gray-500">{{ $pickupRequest->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Étape: Assignée (si applicable) -->
                    @if($pickupRequest->assigned_deliverer_id)
                    <div class="timeline-item flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Assignée à un livreur</h3>
                            <p class="text-xs text-gray-600">{{ $pickupRequest->assignedDeliverer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $pickupRequest->assigned_at ? $pickupRequest->assigned_at->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Étape: Collectée (si applicable) -->
                    @if($pickupRequest->status === 'picked_up')
                    <div class="timeline-item flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Colis collectés</h3>
                            <p class="text-xs text-gray-500">{{ $pickupRequest->picked_up_at ? $pickupRequest->picked_up_at->format('d/m/Y H:i') : '' }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Étape: Annulée (si applicable) -->
                    @if($pickupRequest->status === 'cancelled')
                    <div class="timeline-item flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Demande annulée</h3>
                            <p class="text-xs text-gray-500">{{ $pickupRequest->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations Livreur (si assigné) -->
            @if($pickupRequest->assignedDeliverer)
            <div class="detail-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Livreur Assigné</h2>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-xl font-bold text-indigo-600">
                            {{ strtoupper(substr($pickupRequest->assignedDeliverer->name, 0, 2)) }}
                        </span>
                    </div>
                    <h3 class="font-medium text-gray-900">{{ $pickupRequest->assignedDeliverer->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $pickupRequest->assignedDeliverer->email }}</p>
                    @if($pickupRequest->assignedDeliverer->phone)
                        <p class="text-sm text-gray-600">📞 {{ $pickupRequest->assignedDeliverer->phone }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions Rapides -->
            <div class="detail-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Actions Rapides</h2>
                <div class="space-y-3">
                    <a href="{{ route('client.pickup-requests.index') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Toutes les Demandes
                    </a>
                    @if($pickupRequest->status === 'pending')
                    <a href="{{ route('client.pickup-requests.create') }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-purple-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvelle Demande
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection