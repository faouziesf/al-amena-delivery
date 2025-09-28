@extends('layouts.commercial')

@section('title', 'Dashboard Analytics')
@section('page-title', 'Dashboard Analytics')
@section('page-description', 'Aperçu des performances et statistiques - ' . $period)

@section('header-actions')
<div class="flex items-center space-x-3">
    <form method="GET" action="{{ route('commercial.analytics.index') }}" class="flex items-center space-x-2">
        <input type="date" name="start_date" value="{{ $startDate }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        <span class="text-gray-500">à</span>
        <input type="date" name="end_date" value="{{ $endDate }}"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Actualiser
        </button>
    </form>

    <a href="{{ route('commercial.analytics.export', request()->query()) }}"
       class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exporter
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Clients -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Total Clients</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($kpis['total_clients']) }}</p>
                    <p class="text-blue-700 text-xs mt-1">+{{ $kpis['new_clients'] }} nouveaux</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Colis -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">Colis Total</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($kpis['total_packages']) }}</p>
                    <p class="text-green-700 text-xs mt-1">{{ $kpis['delivered_packages'] }} livrés</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revenus -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-600 text-sm font-medium">Revenus</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($kpis['total_revenue'], 3) }} DT</p>
                    <p class="text-purple-700 text-xs mt-1">COD: {{ number_format($kpis['cod_collected'], 3) }} DT</p>
                </div>
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Livreurs -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-600 text-sm font-medium">Livreurs Actifs</p>
                    <p class="text-2xl font-bold text-orange-900">{{ number_format($kpis['active_deliverers']) }}</p>
                    <p class="text-orange-700 text-xs mt-1">Wallet: {{ number_format($kpis['deliverer_wallets_total'], 3) }} DT</p>
                </div>
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

            <!-- Créer Client -->
            <a href="{{ route('commercial.clients.create') }}"
               class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-600">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-blue-700">Créer Client</span>
            </a>

            <!-- Tickets Ouverts -->
            <a href="{{ route('commercial.tickets.index') }}?status=OPEN"
               class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors group relative">
                <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-orange-600">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-orange-700">Tickets Ouverts</span>
                @if($kpis['open_tickets'] > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $kpis['open_tickets'] }}</span>
                @endif
            </a>

            <!-- Tickets Urgents -->
            <a href="{{ route('commercial.tickets.index') }}?priority=HIGH"
               class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group relative">
                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-red-600">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-red-700">Urgents</span>
                @if($kpis['urgent_tickets'] > 0)
                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $kpis['urgent_tickets'] }}</span>
                @endif
            </a>

            <!-- Retraits Pending -->
            <a href="{{ route('commercial.withdrawals.index') }}?status=PENDING"
               class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group relative">
                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-purple-600">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-purple-700">Retraits</span>
                @if($kpis['withdrawal_requests'] > 0)
                <span class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $kpis['withdrawal_requests'] }}</span>
                @endif
            </a>


            <!-- Gérer Livreurs -->
            <a href="{{ route('commercial.deliverers.index') }}"
               class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-green-600">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-green-700">Livreurs</span>
            </a>

        </div>
    </div>

    <!-- Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Clients -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Clients</h3>
            <div class="space-y-3">
                @forelse($topPerformers['clients'] as $client)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $client->name }}</p>
                        <p class="text-sm text-gray-600">{{ $client->email }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        {{ $client->package_count }} colis
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Top Livreurs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Livreurs</h3>
            <div class="space-y-3">
                @forelse($topPerformers['deliverers'] as $deliverer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $deliverer->name }}</p>
                        <p class="text-sm text-gray-600">{{ $deliverer->phone }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        {{ $deliverer->delivery_count }} livraisons
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Top Délégations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Délégations</h3>
            <div class="space-y-3">
                @forelse($topPerformers['delegations'] as $delegation)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $delegation->destination_delegation }}</p>
                    </div>
                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        {{ $delegation->count }} colis
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Statistiques additionnelles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <h4 class="text-sm font-medium text-gray-600 mb-2">Réclamations</h4>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['total_complaints']) }}</p>
            <p class="text-sm text-green-600">{{ $kpis['resolved_complaints'] }} résolues</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <h4 class="text-sm font-medium text-gray-600 mb-2">Colis en Transit</h4>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['packages_in_transit']) }}</p>
            <p class="text-sm text-blue-600">En cours de livraison</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <h4 class="text-sm font-medium text-gray-600 mb-2">Demandes de Retrait</h4>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['withdrawal_requests']) }}</p>
            <p class="text-sm text-purple-600">{{ number_format($kpis['approved_withdrawals'], 3) }} DT approuvé</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <h4 class="text-sm font-medium text-gray-600 mb-2">Clients Actifs</h4>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($kpis['active_clients']) }}</p>
            <p class="text-sm text-green-600">Comptes validés</p>
        </div>
    </div>
</div>
@endsection

