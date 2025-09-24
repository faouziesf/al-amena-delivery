@extends('layouts.client')

@section('title', 'Demandes de Collecte')
@section('page-title', 'Mes Demandes de Collecte')
@section('page-description', 'Gérez toutes vos demandes de collecte de colis')

@section('content')
<style>
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
.pickup-card { animation: slideInUp 0.4s ease-out; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); }
.pickup-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1); }
.status-badge { transition: all 0.3s ease; }
.status-badge:hover { transform: scale(1.05); }
</style>

<div class="max-w-7xl mx-auto">

    <!-- Header avec Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg" style="animation: float 3s ease-in-out infinite">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Demandes de Collecte</h2>
                <p class="text-gray-600">{{ $pickupRequests->total() }} demande(s) au total</p>
            </div>
        </div>

        <div class="flex space-x-3">
            <a href="{{ route('client.pickup-addresses.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Mes Adresses
            </a>
            <a href="{{ route('client.pickup-requests.create') }}" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle Demande
            </a>
        </div>
    </div>

    <!-- Liste des Demandes -->
    @if($pickupRequests->count() > 0)
        <div class="space-y-6">
            @foreach($pickupRequests as $request)
            <div class="pickup-card bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">

                    <!-- Informations principales -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <span class="text-lg font-bold text-gray-900">#{{ $request->id }}</span>
                                @php
                                    $statusConfig = [
                                        'pending' => ['color' => 'orange', 'text' => 'En Attente', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'assigned' => ['color' => 'blue', 'text' => 'Assignée', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                        'picked_up' => ['color' => 'green', 'text' => 'Collectée', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'cancelled' => ['color' => 'red', 'text' => 'Annulée', 'icon' => 'M6 18L18 6M6 6l12 12']
                                    ];
                                    $config = $statusConfig[$request->status] ?? $statusConfig['pending'];
                                @endphp
                                <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                                    </svg>
                                    {{ $config['text'] }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $request->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900 mb-1">📍 Adresse de collecte</p>
                                <p class="text-sm text-gray-600">{{ $request->pickup_address }}</p>
                                @if($request->delegation_from)
                                    <p class="text-xs text-gray-500 mt-1">{{ $request->delegation_from }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 mb-1">📅 Date demandée</p>
                                <p class="text-sm text-gray-600">{{ $request->requested_pickup_date->format('d/m/Y H:i') }}</p>
                                @if($request->assignedDeliverer)
                                    <p class="text-xs text-green-600 mt-1">👤 {{ $request->assignedDeliverer->name }}</p>
                                @endif
                            </div>
                        </div>

                        @if($request->packages)
                            <div class="mt-3">
                                <p class="text-sm font-medium text-gray-900 mb-1">📦 Colis concernés</p>
                                <p class="text-sm text-gray-600">{{ count($request->packages) }} colis sélectionné(s)</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('client.pickup-requests.show', $request) }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Détails
                        </a>

                        @if(in_array($request->status, ['pending', 'assigned']))
                            <form action="{{ route('client.pickup-requests.cancel', $request) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-red-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annuler
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $pickupRequests->links() }}
        </div>
    @else
        <!-- État vide -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune demande de collecte</h3>
            <p class="text-gray-600 mb-6">Vous n'avez pas encore créé de demande de collecte.</p>
            <a href="{{ route('client.pickup-requests.create') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-lg shadow-sm text-base font-medium text-white hover:from-purple-700 hover:to-indigo-700 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Créer ma première demande
            </a>
        </div>
    @endif
</div>
@endsection