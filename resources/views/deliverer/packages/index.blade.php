@extends('layouts.deliverer')

@section('title', 'Mes Colis')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">📦 Mes Colis</h1>
                    <p class="text-gray-600 mt-1">Vue d'ensemble de votre activité</p>
                </div>
                <button onclick="refreshStats()" class="bg-purple-300 hover:bg-purple-400 text-purple-800 px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- Pickups disponibles -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pickups</p>
                        <p class="text-2xl font-bold text-blue-600" id="stat-available">{{ $stats['available_pickups'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('deliverer.packages.available') }}" class="text-xs text-blue-600 hover:text-blue-800 mt-2 block">
                    → Voir disponibles
                </a>
            </div>

            <!-- Mes pickups -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Mes Pickups</p>
                        <p class="text-2xl font-bold text-orange-600" id="stat-pickups">{{ $stats['my_pickups'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('deliverer.packages.my-pickups') }}" class="text-xs text-orange-600 hover:text-orange-800 mt-2 block">
                    → Voir mes pickups
                </a>
            </div>

            <!-- Livraisons -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Livraisons</p>
                        <p class="text-2xl font-bold text-green-600" id="stat-deliveries">{{ $stats['deliveries'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('deliverer.packages.deliveries') }}" class="text-xs text-green-600 hover:text-green-800 mt-2 block">
                    → Voir livraisons
                </a>
            </div>

            <!-- Retours -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Retours</p>
                        <p class="text-2xl font-bold text-red-600" id="stat-returns">{{ $stats['returns'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-7.172a2 2 0 00-1.414.586L3 12z"></path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('deliverer.packages.returns') }}" class="text-xs text-red-600 hover:text-red-800 mt-2 block">
                    → Voir retours
                </a>
            </div>
        </div>

        <!-- Stats Today -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100">Livrés aujourd'hui</p>
                        <p class="text-3xl font-bold" id="stat-delivered-today">{{ $stats['deliveries_today'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100">COD collecté</p>
                        <p class="text-3xl font-bold" id="stat-cod-today">{{ number_format($stats['cod_collected_today'] ?? 0, 3) }} DT</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100">Urgents</p>
                        <p class="text-3xl font-bold" id="stat-urgent">{{ $stats['urgent_deliveries'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colis urgents -->
        @if($urgentPackages && $urgentPackages->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-red-200 mb-6">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <h2 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Colis Urgents (3ème tentative)
                </h2>
            </div>
            <div class="p-4">
                @foreach($urgentPackages as $package)
                <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg mb-3 last:mb-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm font-medium">{{ $package->package_code }}</span>
                            <span class="bg-red-600 text-white text-xs px-2 py-1 rounded-full">URGENT</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $package->recipient_data['name'] ?? 'N/A' }} •
                            {{ $package->delegationTo->name ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            COD: {{ number_format($package->cod_amount, 3) }} DT •
                            Tentatives: {{ $package->delivery_attempts }}/3
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('deliverer.packages.show', $package) }}"
                           class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm">
                            Traiter
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Activité récente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Activité Récente</h2>
            </div>
            <div class="p-4">
                @if($recentActivity && $recentActivity->count() > 0)
                    @foreach($recentActivity as $activity)
                    <div class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            @if($activity->status === 'DELIVERED') bg-green-100 text-green-600
                            @elseif($activity->status === 'PICKED_UP') bg-blue-100 text-blue-600
                            @elseif($activity->status === 'RETURNED') bg-red-100 text-red-600
                            @else bg-gray-100 text-gray-600
                            @endif">
                            @if($activity->status === 'DELIVERED')
                                ✅
                            @elseif($activity->status === 'PICKED_UP')
                                📦
                            @elseif($activity->status === 'RETURNED')
                                ↩️
                            @else
                                📋
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $activity->package_code }}</p>
                            <p class="text-sm text-gray-600">
                                @if($activity->status === 'DELIVERED')
                                    Livré • {{ number_format($activity->cod_amount, 3) }} DT
                                @elseif($activity->status === 'PICKED_UP')
                                    Collecté chez l'expéditeur
                                @elseif($activity->status === 'RETURNED')
                                    Retourné à l'expéditeur
                                @else
                                    {{ $activity->status }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">{{ $activity->updated_at ? $activity->updated_at->diffForHumans() : 'Date inconnue' }}</p>
                            <a href="{{ route('deliverer.packages.show', $activity) }}"
                               class="text-xs text-blue-600 hover:text-blue-800">
                                Voir détails
                            </a>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
                        </svg>
                        <p class="text-gray-500">Aucune activité récente</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <a href="{{ route('deliverer.packages.available') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-xl text-center transition-colors">
                <div class="w-8 h-8 mx-auto mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <p class="font-medium">Nouveaux Pickups</p>
            </a>

            <a href="{{ route('deliverer.deliveries.single') }}"
               class="bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white p-4 rounded-xl text-center transition-colors">
                <div class="w-8 h-8 mx-auto mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <p class="font-medium">⚡ Livraison Rapide</p>
            </a>

            <a href="{{ route('deliverer.packages.deliveries') }}"
               class="bg-green-600 hover:bg-green-700 text-white p-4 rounded-xl text-center transition-colors">
                <div class="w-8 h-8 mx-auto mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="font-medium">Mes Livraisons</p>
            </a>

            <a href="{{ route('deliverer.wallet.index') }}"
               class="bg-purple-300 hover:bg-purple-400 text-purple-800 p-4 rounded-xl text-center transition-colors">
                <div class="w-8 h-8 mx-auto mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <p class="font-medium">Mon Wallet</p>
            </a>

            <a href="{{ route('deliverer.client-topup.index') }}"
               class="bg-orange-600 hover:bg-orange-700 text-white p-4 rounded-xl text-center transition-colors">
                <div class="w-8 h-8 mx-auto mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <p class="font-medium">Recharge Client</p>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshStats() {
    // Refresh automatique des stats
    fetch('/api/deliverer/stats')
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById('stat-available').textContent = data.available_pickups || 0;
                document.getElementById('stat-pickups').textContent = data.my_pickups || 0;
                document.getElementById('stat-deliveries').textContent = data.deliveries || 0;
                document.getElementById('stat-returns').textContent = data.returns || 0;
                document.getElementById('stat-delivered-today').textContent = data.deliveries_today || 0;
                document.getElementById('stat-cod-today').textContent = (data.cod_collected_today || 0).toFixed(3) + ' DT';
                document.getElementById('stat-urgent').textContent = data.urgent_deliveries || 0;
            }
        })
        .catch(error => console.error('Erreur refresh stats:', error));
}

// Auto refresh toutes les 30 secondes
setInterval(refreshStats, 30000);
</script>
@endpush
@endsection