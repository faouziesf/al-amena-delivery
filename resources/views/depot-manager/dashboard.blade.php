@extends('layouts.depot-manager')

@section('title', 'Dashboard Chef Dépôt')
@section('page-title', 'Dashboard Chef Dépôt')
@section('page-description', 'Vue d\'ensemble de votre dépôt et gouvernorats gérés')

@section('content')
<div class="space-y-6">

    <!-- Informations du dépôt -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ auth()->user()->depot_name ?? 'Dépôt Principal' }}</h2>
                    <p class="text-sm text-gray-600">{{ auth()->user()->depot_address ?? 'Chef Dépôt' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Gouvernorats gérés</p>
                <p class="text-2xl font-bold text-orange-600">{{ count(auth()->user()->assigned_gouvernorats_array) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach(auth()->user()->assigned_gouvernorats_array as $gouvernorat)
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-orange-800">{{ $gouvernorat }}</h3>
                        <p class="text-sm text-orange-600">Gouvernorat géré</p>
                    </div>
                    <div class="text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Livreurs Total</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['total_deliverers'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ $stats['active_deliverers'] ?? 0 }} actifs</p>
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
                    <p class="text-sm font-medium text-gray-600">Colis en Cours</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['packages_in_progress'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">En traitement</p>
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
                    <p class="text-3xl font-bold text-green-600">{{ $stats['delivered_today'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Colis livrés</p>
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
                    <p class="text-sm font-medium text-gray-600">COD Collecté</p>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['cod_collected_today'] ?? 0, 3) }}</p>
                    <p class="text-sm text-gray-500">DT aujourd'hui</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('depot-manager.deliverers.index') }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Gérer les Livreurs</h3>
                    <p class="text-sm text-gray-600">Ajouter, modifier, superviser</p>
                </div>
            </div>
        </a>

        <a href="{{ route('depot-manager.packages.index') }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Suivre les Colis</h3>
                    <p class="text-sm text-gray-600">Réassigner, superviser</p>
                </div>
            </div>
        </a>

        <a href="{{ route('depot-manager.reports.index') }}"
           class="bg-white rounded-xl shadow-sm border border-orange-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Rapports & Analytics</h3>
                    <p class="text-sm text-gray-600">Performance, statistiques</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Performance des livreurs -->
    @if($delivererPerformance->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-900">Performance des Livreurs - Aujourd'hui</h2>
            <a href="{{ route('depot-manager.deliverers.index') }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                Voir tous →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($delivererPerformance->take(6) as $performance)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-medium text-gray-900">{{ $performance['deliverer']->name }}</h3>
                    <span class="text-xs text-gray-500">{{ $performance['deliverer']->assigned_delegation }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div class="text-center">
                        <p class="font-semibold text-green-600">{{ $performance['deliveries_today'] }}</p>
                        <p class="text-gray-500 text-xs">Livrés</p>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-purple-600">{{ number_format($performance['cod_collected_today'], 0) }}</p>
                        <p class="text-gray-500 text-xs">COD DT</p>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-blue-600">{{ $performance['packages_in_progress'] }}</p>
                        <p class="text-gray-500 text-xs">En cours</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Colis urgents -->
    @if($urgentPackages->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-2">
                <h2 class="text-lg font-bold text-gray-900">Colis Urgents</h2>
                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">{{ $urgentPackages->count() }}</span>
            </div>
            <a href="{{ route('depot-manager.packages.index', ['urgent' => 1]) }}" class="text-red-600 hover:text-red-800 text-sm font-medium">
                Voir tous →
            </a>
        </div>

        <div class="space-y-3">
            @foreach($urgentPackages->take(5) as $package)
            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $package->package_code }}</p>
                        <p class="text-sm text-gray-600">{{ $package->assignedDeliverer?->name }} - {{ $package->delegationTo?->name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-red-600">{{ $package->delivery_attempts }} tentatives</p>
                    <p class="text-xs text-gray-500">{{ $package->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Colis d'échange à retourner -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-2">
                <h2 class="text-lg font-bold text-gray-900">Colis d'Échange à Retourner</h2>
                <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">{{ $exchangePackages->count() ?? 0 }}</span>
            </div>
            <a href="{{ route('depot-manager.packages.index', ['exchange' => 1]) }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                Voir tous →
            </a>
        </div>

        @if(($exchangePackages ?? collect())->count() > 0)
        <div class="space-y-3">
            @foreach($exchangePackages->take(5) as $package)
            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $package->package_code }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $package->assignedDeliverer?->name ?? 'Non assigné' }} -
                            {{ $package->delegationTo?->name ?? 'Délégation inconnue' }}
                        </p>
                        <p class="text-xs text-orange-600">Status: {{ $package->status }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-orange-600">Échange</p>
                    <p class="text-xs text-gray-500">{{ $package->updated_at->diffForHumans() }}</p>
                    <button onclick="processExchangeReturn('{{ $package->id }}')"
                            class="mt-1 text-xs bg-orange-500 text-white px-2 py-1 rounded hover:bg-orange-600 transition-colors">
                        Traiter Retour
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-orange-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <p class="text-gray-500 text-sm">Aucun colis d'échange à retourner pour le moment</p>
        </div>
        @endif
    </div>

</div>

<script>
function processExchangeReturn(packageId) {
    if (confirm('Voulez-vous marquer ce colis d\'échange comme retourné ?')) {
        fetch(`/depot-manager/packages/${packageId}/process-exchange-return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Colis d\'échange traité avec succès !');
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du traitement');
        });
    }
}
</script>

@endsection