@extends('layouts.deliverer')

@section('title', 'Demandes de Collecte')
@section('page-title', 'Demandes de Collecte')

@section('content')
<style>
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
.pickup-card { animation: slideInUp 0.4s ease-out; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.pickup-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1); }
.tab-button { transition: all 0.3s ease; }
.tab-button.active { transform: translateY(-2px); }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="pickupManager()">

    <!-- Header avec Statistiques -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-200 to-purple-300 rounded-xl flex items-center justify-center shadow-lg" style="animation: float 3s ease-in-out infinite">
                    <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Demandes de Collecte</h1>
                    <p class="text-gray-600">Gérez vos collectes de colis</p>
                </div>
            </div>

            <a href="{{ route('deliverer.pickups.scan') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-200 to-purple-300 border border-transparent rounded-xl shadow-sm text-base font-medium text-purple-800 hover:from-purple-300 hover:to-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scanner
            </a>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl shadow-lg border border-orange-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-orange-700 mb-1 tracking-wide">DISPONIBLES</p>
                        <p class="text-3xl font-extrabold text-orange-900">{{ $availablePickups->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-lg border border-blue-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-700 mb-1 tracking-wide">MES COLLECTES</p>
                        <p class="text-3xl font-extrabold text-blue-900">{{ $myPickups->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-lg border border-green-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-green-700 mb-1 tracking-wide">COMPLÉTÉES</p>
                        <p class="text-3xl font-extrabold text-green-900">{{ $myPickups->where('status', 'picked_up')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl shadow-lg border border-purple-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-purple-700 mb-1 tracking-wide">EN COURS</p>
                        <p class="text-3xl font-extrabold text-purple-900">{{ $myPickups->where('status', 'assigned')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <div class="mb-6">
        <div class="flex space-x-4 bg-gray-100 rounded-2xl p-2">
            <button @click="activeTab = 'available'"
                    :class="activeTab === 'available' ? 'bg-white shadow-lg text-purple-600 active' : 'text-gray-600 hover:text-gray-800'"
                    class="tab-button flex-1 py-3 px-4 rounded-xl font-medium transition-all duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Disponibles ({{ $availablePickups->total() }})
            </button>
            <button @click="activeTab = 'mine'"
                    :class="activeTab === 'mine' ? 'bg-white shadow-lg text-purple-600 active' : 'text-gray-600 hover:text-gray-800'"
                    class="tab-button flex-1 py-3 px-4 rounded-xl font-medium transition-all duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Mes Collectes ({{ $myPickups->total() }})
            </button>
        </div>
    </div>

    <!-- Contenu des onglets -->
    <div x-show="activeTab === 'available'">
        @if($availablePickups->count() > 0)
            <div class="space-y-6">
                @foreach($availablePickups as $pickup)
                <div class="pickup-card bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">

                        <!-- Informations principales -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-bold text-gray-900">#{{ $pickup->id }}</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Disponible
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $pickup->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">👤 Client</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->client->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📅 Date demandée</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->requested_pickup_date->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📍 Adresse</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->pickup_address }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $pickup->delegation_from }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📦 Colis</p>
                                    <p class="text-sm text-gray-600">{{ count($pickup->packages ?? []) }} colis à collecter</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('deliverer.pickups.show', $pickup) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Détails
                            </a>

                            <form action="{{ route('deliverer.pickups.assign', $pickup) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" onclick="return confirm('Voulez-vous prendre en charge cette collecte ?')"
                                        class="inline-flex items-center px-4 py-2 bg-purple-300 text-purple-800 border border-transparent rounded-lg shadow-sm text-sm font-medium hover:bg-purple-400 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Prendre
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $availablePickups->links() }}
            </div>
        @else
            <!-- État vide -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune collecte disponible</h3>
                <p class="text-gray-600">Il n'y a pas de demandes de collecte disponibles pour le moment.</p>
            </div>
        @endif
    </div>

    <div x-show="activeTab === 'mine'">
        @if($myPickups->count() > 0)
            <div class="space-y-6">
                @foreach($myPickups as $pickup)
                <div class="pickup-card bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hover:shadow-xl">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">

                        <!-- Informations principales -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-bold text-gray-900">#{{ $pickup->id }}</span>
                                    @php
                                        $statusConfig = [
                                            'assigned' => ['color' => 'blue', 'text' => 'Assignée', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                                            'picked_up' => ['color' => 'green', 'text' => 'Collectée', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z']
                                        ];
                                        $config = $statusConfig[$pickup->status] ?? $statusConfig['assigned'];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                                        </svg>
                                        {{ $config['text'] }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $pickup->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">👤 Client</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->client->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📅 Date demandée</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->requested_pickup_date->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📍 Adresse</p>
                                    <p class="text-sm text-gray-600">{{ $pickup->pickup_address }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $pickup->delegation_from }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 mb-1">📦 Colis</p>
                                    <p class="text-sm text-gray-600">{{ count($pickup->packages ?? []) }} colis</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('deliverer.pickups.show', $pickup) }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Détails
                            </a>

                            @if($pickup->status === 'assigned')
                                <form action="{{ route('deliverer.pickups.complete', $pickup) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Confirmer la collecte de tous les colis ?')"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Compléter
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
                {{ $myPickups->links() }}
            </div>
        @else
            <!-- État vide -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune collecte assignée</h3>
                <p class="text-gray-600">Vous n'avez pas encore de collectes assignées.</p>
            </div>
        @endif
    </div>
</div>

<script>
function pickupManager() {
    return {
        activeTab: 'available'
    }
}
</script>
@endsection