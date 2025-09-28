@extends('layouts.depot-manager')

@section('title', 'Gouvernorat ' . $gouvernorat)
@section('page-title', 'Gouvernorat de ' . $gouvernorat)
@section('page-description', 'Gestion des livreurs et colis du gouvernorat')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center space-x-4 mb-6">
        <a href="{{ route('depot-manager.dashboard') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $gouvernorat }}</h1>
            <p class="text-gray-600">{{ $stats['total_deliverers'] }} livreurs • {{ $stats['active_packages'] }} colis actifs</p>
        </div>
    </div>

    <!-- Statistiques du gouvernorat -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Livreurs Total</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['total_deliverers'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Colis Actifs</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['active_packages'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Livrés Aujourd'hui</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['delivered_today'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Colis Urgents</p>
                    <p class="text-3xl font-bold text-red-600">{{ $stats['urgent_packages'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('depot-manager.deliverers.create') }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Ajouter un Livreur</h3>
                    <p class="text-sm text-gray-600">Nouveau livreur pour {{ $gouvernorat }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('depot-manager.packages.index', ['gouvernorat' => $gouvernorat]) }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Voir les Colis</h3>
                    <p class="text-sm text-gray-600">Colis de {{ $gouvernorat }}</p>
                </div>
            </div>
        </a>

        <a href="{{ route('depot-manager.reports.index', ['gouvernorat' => $gouvernorat]) }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Rapports</h3>
                    <p class="text-sm text-gray-600">Analytics {{ $gouvernorat }}</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Liste des livreurs -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Livreurs de {{ $gouvernorat }}</h2>
                <a href="{{ route('depot-manager.deliverers.create') }}"
                   class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter
                </a>
            </div>
        </div>

        <div class="p-6">
            @if($deliverers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($deliverers as $deliverer)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-semibold text-sm">
                                        {{ substr($deliverer->name, 0, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $deliverer->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $deliverer->phone }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($deliverer->account_status === 'ACTIVE') bg-green-100 text-green-800
                                @elseif($deliverer->account_status === 'SUSPENDED') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $deliverer->account_status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-sm text-center mb-3">
                            <div>
                                <p class="font-semibold text-blue-600">{{ $deliverer->assignedPackages->count() }}</p>
                                <p class="text-gray-500 text-xs">En cours</p>
                            </div>
                            <div>
                                <p class="font-semibold text-green-600">0</p>
                                <p class="text-gray-500 text-xs">Livrés</p>
                            </div>
                            <div>
                                <p class="font-semibold text-purple-600">0</p>
                                <p class="text-gray-500 text-xs">COD DT</p>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('depot-manager.deliverers.show', $deliverer) }}"
                               class="flex-1 text-center px-3 py-2 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors text-sm font-medium">
                                Voir
                            </a>
                            <a href="{{ route('depot-manager.deliverers.edit', $deliverer) }}"
                               class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                                Modifier
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 mb-4">Aucun livreur pour le gouvernorat {{ $gouvernorat }}</p>
                    <a href="{{ route('depot-manager.deliverers.create') }}"
                       class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                        Ajouter le premier livreur
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection