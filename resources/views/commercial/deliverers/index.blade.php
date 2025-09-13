@extends('layouts.commercial')

@section('title', 'Gestion des Livreurs')
@section('page-title', 'Gestion des Livreurs')
@section('page-description', 'Gérez les wallets des livreurs et les livraisons de paiements clients')

@section('header-actions')
<div class="flex items-center space-x-3">
    <div class="hidden lg:flex items-center space-x-4 text-sm">
        <div class="flex items-center space-x-2" x-show="stats.urgent_emptying_count > 0">
            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <span class="text-red-600 font-medium" x-text="stats.urgent_emptying_count + ' urgent(s)'"></span>
        </div>
        <div class="text-gray-500">
            <span x-text="(stats.total_wallet_amount || 0).toFixed(3)"></span> DT en circulation
        </div>
    </div>
    <button onclick="generateDeliverersReport()" 
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Rapport
    </button>
    <button onclick="openBulkEmptyModal()" 
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Vidage Groupé
    </button>
</div>
@endsection

@section('content')
<div x-data="deliverersApp()" x-init="init()">
    <!-- High Balance Alert Banner -->
    <div x-show="stats.urgent_emptying_count > 0" 
         class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl p-4 mb-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Wallets Nécessitent Vidage Urgent !</h3>
                    <p x-text="`${stats.urgent_emptying_count} livreurs ont des wallets élevés (>200 DT) nécessitant un vidage immédiat`"></p>
                </div>
            </div>
            <button onclick="showHighBalanceOnly()" 
                    class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg font-medium transition-colors">
                Vider Maintenant
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Livreurs Actifs</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.total_active || {{ $stats['total_deliverers'] ?? 0 }}"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Wallets à Vider</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.high_balance_count || {{ $stats['high_balance_count'] ?? 0 }}"></p>
                    <p class="text-xs text-orange-600 mt-1">+100 DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Vidage Urgent</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.urgent_emptying_count || {{ $stats['high_balance_count'] ?? 0 }}"></p>
                    <p class="text-xs text-red-600 mt-1">+200 DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total en Circulation</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="(stats.total_wallet_amount || 0).toFixed(3)"></p>
                    <p class="text-xs text-green-600 mt-1">DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Vidages Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.emptyings_today || {{ $stats['emptyings_today'] ?? 0 }}"></p>
                    <p class="text-xs text-purple-600 mt-1" x-text="(stats.amount_emptied_today || 0).toFixed(3) + ' DT'"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="setActiveTab('all')" 
                        :class="activeTab === 'all' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Tous les Livreurs
                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.total_active || 0"></span>
                </button>
                <button @click="setActiveTab('high_balance')" 
                        :class="activeTab === 'high_balance' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Wallets Élevés
                    <span class="ml-2 bg-orange-100 text-orange-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.high_balance_count || 0"></span>
                </button>
                <button @click="setActiveTab('urgent_emptying')" 
                        :class="activeTab === 'urgent_emptying' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Vidage Urgent
                    <span class="ml-2 bg-red-100 text-red-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.urgent_emptying_count || 0"></span>
                </button>
                <button @click="setActiveTab('cash_deliveries')" 
                        :class="activeTab === 'cash_deliveries' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Paiements Clients
                    <span class="ml-2 bg-green-100 text-green-900 py-0.5 px-2.5 rounded-full text-xs" x-text="awaitingDeliveries.length || 0"></span>
                </button>
                <button @click="setActiveTab('recent_emptyings')" 
                        :class="activeTab === 'recent_emptyings' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Historique
                    <span class="ml-2 bg-purple-100 text-purple-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.emptyings_today || 0"></span>
                </button>
            </nav>
        </div>

        <!-- Filters -->
        <div class="p-6">
            <form method="GET" action="{{ route('commercial.deliverers.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Nom, téléphone..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Solde minimum</label>
                        <input type="number" name="min_balance" value="{{ request('min_balance') }}" step="0.001"
                               placeholder="0.000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Actions Rapides</label>
                        <select onchange="handleQuickAction(this.value)" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sélectionner action...</option>
                            <option value="show_high_balance">Wallets > 100 DT</option>
                            <option value="show_urgent">Wallets > 200 DT</option>
                            <option value="show_empty">Wallets vides</option>
                            <option value="bulk_empty">Vidage groupé</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tri</label>
                        <select name="sort" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Par nom</option>
                            <option value="balance_desc" {{ request('sort') == 'balance_desc' ? 'selected' : '' }}>Solde décroissant</option>
                            <option value="balance_asc" {{ request('sort') == 'balance_asc' ? 'selected' : '' }}>Solde croissant</option>
                            <option value="last_emptying" {{ request('sort') == 'last_emptying' ? 'selected' : '' }}>Dernier vidage</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 transition-colors">
                            Filtrer
                        </button>
                        <a href="{{ route('commercial.deliverers.index') }}" 
                           class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    </div>
                </div>
                
                <!-- Quick Filters -->
                <div class="flex items-center space-x-6 pt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="high_balance_only" value="1" 
                               {{ request('high_balance_only') ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">Soldes élevés uniquement (+100 DT)</span>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <!-- Content Based on Active Tab -->
    <div class="space-y-6">
        <!-- Deliverers List (All, High Balance, Urgent) -->
        <div x-show="['all', 'high_balance', 'urgent_emptying'].includes(activeTab)" class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
            @if($deliverers->count() > 0)
                <!-- Selection Header -->
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200" 
                     x-show="selectedDeliverers.length > 0" x-transition>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-900" 
                                  x-text="`${selectedDeliverers.length} livreur(s) sélectionné(s)`"></span>
                            <span class="text-sm text-gray-600" 
                                  x-text="`(${calculateSelectedWalletAmount().toFixed(3)} DT au total)`"></span>
                            <button @click="selectedDeliverers = []" 
                                    class="text-sm text-gray-500 hover:text-gray-700">
                                Désélectionner tout
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="bulkEmptySelected()" 
                                    class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                Vider Wallets
                            </button>
                            <button onclick="bulkAssignPayments()" 
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                Assigner Paiements
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" @change="toggleAllDeliverers($event)" 
                                           class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Livreur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sources Wallet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernier Vidage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($deliverers as $deliverer)
                            <tr class="hover:bg-gray-50 transition-colors group
                                       {{ ($deliverer->wallet->balance ?? 0) > 200 ? 'bg-red-50 border-l-4 border-red-500' : 
                                          (($deliverer->wallet->balance ?? 0) > 100 ? 'bg-orange-50 border-l-4 border-orange-500' : '') }}"
                                :data-balance="{{ $deliverer->wallet->balance ?? 0 }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" :value="{{ $deliverer->id }}" 
                                           x-model="selectedDeliverers"
                                           class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center shadow-sm">
                                                <span class="text-sm font-bold text-white">{{ substr($deliverer->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $deliverer->name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $deliverer->id }}</div>
                                            @if(($deliverer->wallet->balance ?? 0) > 200)
                                                <div class="text-xs text-red-600 font-medium mt-1">
                                                    <svg class="w-3 h-3 inline animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Vidage urgent requis
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $deliverer->phone }}</div>
                                    <div class="text-sm text-gray-500">{{ $deliverer->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-lg font-bold 
                                                    {{ ($deliverer->wallet->balance ?? 0) > 200 ? 'text-red-600' : 
                                                       (($deliverer->wallet->balance ?? 0) > 100 ? 'text-orange-600' : 
                                                        (($deliverer->wallet->balance ?? 0) > 0 ? 'text-green-600' : 'text-gray-900')) }}">
                                            {{ number_format($deliverer->wallet->balance ?? 0, 3) }} DT
                                        </div>
                                        @if(($deliverer->wallet->balance ?? 0) > 100)
                                            <div class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                        {{ ($deliverer->wallet->balance ?? 0) > 200 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                                {{ ($deliverer->wallet->balance ?? 0) > 200 ? 'Urgent' : 'À vider' }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs space-y-1">
                                        @php
                                            $codAmount = $deliverer->transactions()
                                                          ->where('type', 'COD_COLLECTION')
                                                          ->where('created_at', '>=', now()->subDays(7))
                                                          ->sum('amount');
                                            $rechargeAmount = $deliverer->transactions()
                                                            ->where('type', 'WALLET_RECHARGE')
                                                            ->where('created_at', '>=', now()->subDays(7))
                                                            ->sum('amount');
                                        @endphp
                                        @if($codAmount > 0)
                                            <div class="text-green-600">COD: {{ number_format($codAmount, 1) }} DT</div>
                                        @endif
                                        @if($rechargeAmount > 0)
                                            <div class="text-blue-600">Recharge: {{ number_format($rechargeAmount, 1) }} DT</div>
                                        @endif
                                        @if($codAmount == 0 && $rechargeAmount == 0)
                                            <div class="text-gray-500">Aucune activité</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm space-y-1">
                                        <div class="text-gray-900">{{ $deliverer->packages_today ?? 0 }} colis aujourd'hui</div>
                                        @if(isset($deliverer->last_emptying))
                                            <div class="text-xs text-gray-500">
                                                Dernier vidage: {{ $deliverer->last_emptying->emptying_date->diffForHumans() }}
                                            </div>
                                        @else
                                            <div class="text-xs text-orange-600">Jamais vidé</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($deliverer->last_emptying))
                                        <div class="text-sm">
                                            <div class="text-gray-900">{{ $deliverer->last_emptying->emptying_date->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ number_format($deliverer->last_emptying->wallet_amount, 3) }} DT</div>
                                            @if($deliverer->last_emptying->hasDiscrepancy())
                                                <div class="text-xs {{ $deliverer->last_emptying->discrepancy_color }}">
                                                    {{ $deliverer->last_emptying->formatted_discrepancy }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">Jamais vidé</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Quick Empty Wallet -->
                                        @if(($deliverer->wallet->balance ?? 0) > 0)
                                            <button onclick="quickEmptyWallet({{ $deliverer->id }}, '{{ $deliverer->name }}', {{ $deliverer->wallet->balance ?? 0 }})" 
                                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                    title="Vider wallet">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        <!-- Assign Cash Delivery -->
                                        <button onclick="assignCashDelivery({{ $deliverer->id }}, '{{ $deliverer->name }}')" 
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-colors"
                                                title="Assigner paiement client">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                        </button>

                                        <!-- View Details -->
                                        <a href="{{ route('commercial.deliverers.show', $deliverer) }}" 
                                           class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                           title="Voir détails">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <!-- Wallet History -->
                                        <a href="{{ route('commercial.deliverers.wallet', $deliverer) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-100 transition-colors"
                                           title="Historique wallet">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                        </a>

                                        <!-- More Actions -->
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" 
                                                    class="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                                </svg>
                                            </button>
                                            
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                                <div class="py-1">
                                                    @if(isset($deliverer->last_emptying))
                                                        <a href="{{ route('commercial.deliverers.emptying.receipt', $deliverer->last_emptying) }}" target="_blank"
                                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            Reçu dernier vidage
                                                        </a>
                                                    @endif
                                                    <button onclick="exportDelivererData({{ $deliverer->id }})" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Exporter données
                                                    </button>
                                                    <hr class="my-1">
                                                    <button onclick="sendNotificationToDeliverer({{ $deliverer->id }})" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Envoyer notification
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $deliverers->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun livreur trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if(request()->hasAny(['search', 'min_balance', 'high_balance_only']))
                            Aucun livreur ne correspond à vos critères.
                        @else
                            Aucun livreur actif trouvé.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <!-- Cash Deliveries Tab -->
        <div x-show="activeTab === 'cash_deliveries'" class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-white">
                <h3 class="text-lg font-semibold text-gray-900">Paiements Clients en Attente de Livraison</h3>
                <p class="text-sm text-gray-600 mt-1">Retraits approuvés nécessitant assignation à un livreur</p>
            </div>
            
            <div x-show="awaitingDeliveries.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun paiement en attente</h3>
                <p class="mt-1 text-sm text-gray-500">Tous les paiements clients sont assignés ou livrés.</p>
            </div>
            
            <div x-show="awaitingDeliveries.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code Livraison</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigné à</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Traité le</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="delivery in awaitingDeliveries" :key="delivery.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono font-medium text-gray-900" x-text="delivery.delivery_receipt_code"></div>
                                    <div class="text-sm text-gray-500" x-text="delivery.request_code"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="delivery.client_name"></div>
                                    <div class="text-sm text-gray-500" x-text="delivery.client_phone"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-green-600" x-text="delivery.amount + ' DT'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div x-show="delivery.assigned_deliverer">
                                        <div class="text-sm font-medium text-gray-900" x-text="delivery.assigned_deliverer"></div>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Assigné
                                        </span>
                                    </div>
                                    <div x-show="!delivery.assigned_deliverer">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            Non assigné
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div x-text="delivery.processed_at"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button x-show="!delivery.assigned_deliverer" 
                                                @click="assignCashDeliveryFromList(delivery.id)"
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-colors"
                                                title="Assigner livreur">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </button>
                                        <button @click="generateDeliveryReceipt(delivery.id)"
                                                class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                                title="Bon de livraison">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Emptyings Tab -->
        <div x-show="activeTab === 'recent_emptyings'" class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white">
                <h3 class="text-lg font-semibold text-gray-900">Historique des Vidages</h3>
                <p class="text-sm text-gray-600 mt-1">Derniers vidages de wallets effectués</p>
            </div>
            
            <div x-show="recentEmptyings.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun vidage récent</h3>
                <p class="mt-1 text-sm text-gray-500">L'historique des vidages apparaîtra ici.</p>
            </div>
            
            <div x-show="recentEmptyings.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Livreur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montants</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Différence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="emptying in recentEmptyings" :key="emptying.id">
                            <tr class="hover:bg-gray-50" :class="emptying.has_discrepancy ? 'bg-yellow-50' : ''">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="emptying.deliverer_name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="emptying.commercial_name"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="text-gray-900">Wallet: <span class="font-medium" x-text="emptying.wallet_amount + ' DT'"></span></div>
                                        <div class="text-gray-600">Physique: <span class="font-medium" x-text="emptying.physical_amount + ' DT'"></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium" :class="emptying.discrepancy_color" x-text="emptying.discrepancy"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div x-text="emptying.emptying_date"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="viewEmptyingReceipt(emptying.id)"
                                            class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                            title="Voir reçu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Quick Empty Wallet Modal -->
<div id="quick-empty-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Vidage Wallet Rapide</h3>
                <button onclick="closeQuickEmptyModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="quick-empty-form" class="p-6 space-y-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">
                        <div>Livreur: <span id="empty-deliverer-name" class="font-medium"></span></div>
                        <div>Solde wallet: <span id="empty-wallet-balance" class="font-bold"></span> DT</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant physique reçu (DT)</label>
                    <input type="number" id="empty-physical-amount" step="0.001" min="0" 
                           placeholder="Montant en espèces que vous avez reçu..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Laissez vide si identique au solde wallet</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="empty-notes" rows="3"
                              placeholder="Notes sur le vidage..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="empty-force" 
                           class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label for="empty-force" class="text-sm text-gray-700">
                        Forcer le vidage (en cas d'écart important)
                    </label>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">Important:</p>
                            <p>Le livreur est responsable de toute différence entre le wallet et les espèces remises.</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                        Vider Wallet
                    </button>
                    <button type="button" onclick="closeQuickEmptyModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Cash Delivery Modal -->
<div id="assign-cash-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Assigner Paiement Client</h3>
                <button onclick="closeAssignCashModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="assign-cash-form" class="p-6 space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm text-blue-700">
                        <div>Livreur: <span id="cash-deliverer-name" class="font-medium"></span></div>
                        <div>Wallet actuel: <span id="cash-deliverer-balance" class="font-medium"></span> DT</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paiement à assigner</label>
                    <select id="cash-withdrawal-id" required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Sélectionner un paiement...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Retraits approuvés en attente de livraison</p>
                </div>
                
                <div id="cash-payment-details" class="hidden bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">
                        <div>Client: <span id="cash-client-name" class="font-medium"></span></div>
                        <div>Montant: <span id="cash-amount" class="font-bold"></span> DT</div>
                        <div>Code: <span id="cash-code" class="font-mono"></span></div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes d'assignation</label>
                    <textarea id="cash-assign-notes" rows="3"
                              placeholder="Instructions spéciales pour la livraison..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-orange-700">
                            <p class="font-medium">Rappel:</p>
                            <p>Vous devez remettre les espèces au livreur avant qu'il ne parte livrer le client.</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        Assigner Paiement
                    </button>
                    <button type="button" onclick="closeAssignCashModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function deliverersApp() {
    return {
        activeTab: 'all',
        selectedDeliverers: [],
        awaitingDeliveries: [],
        recentEmptyings: [],
        stats: {
            total_active: {{ $stats['total_deliverers'] ?? 0 }},
            high_balance_count: {{ $stats['high_balance_count'] ?? 0 }},
            urgent_emptying_count: {{ $stats['high_balance_count'] ?? 0 }},
            total_wallet_amount: {{ $stats['total_wallet_amount'] ?? 0 }},
            emptyings_today: {{ $stats['emptyings_today'] ?? 0 }},
            amount_emptied_today: {{ $stats['total_emptied_today'] ?? 0 }}
        },

        init() {
            this.loadStats();
            this.loadAwaitingDeliveries();
            this.loadRecentEmptyings();
            this.loadWithdrawalsForCash();
            
            // Auto-refresh every 45 seconds
            setInterval(() => {
                this.loadStats();
                this.loadAwaitingDeliveries();
                this.loadRecentEmptyings();
            }, 45000);
        },

        async loadStats() {
            try {
                const response = await fetch('/commercial/deliverers/api/stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                }
            } catch (error) {
                console.error('Erreur chargement stats livreurs:', error);
            }
        },

        async loadAwaitingDeliveries() {
            try {
                const response = await fetch('/commercial/withdrawals/api/awaiting-delivery');
                if (response.ok) {
                    this.awaitingDeliveries = await response.json();
                }
            } catch (error) {
                console.error('Erreur chargement livraisons en attente:', error);
            }
        },

        async loadRecentEmptyings() {
            try {
                const response = await fetch('/commercial/deliverers/api/recent-emptyings');
                if (response.ok) {
                    this.recentEmptyings = await response.json();
                }
            } catch (error) {
                console.error('Erreur chargement vidages récents:', error);
            }
        },

        async loadWithdrawalsForCash() {
            try {
                const response = await fetch('/commercial/withdrawals/api/awaiting-delivery');
                if (response.ok) {
                    const withdrawals = await response.json();
                    const select = document.getElementById('cash-withdrawal-id');
                    if (select) {
                        select.innerHTML = '<option value="">Sélectionner un paiement...</option>';
                        withdrawals.forEach(withdrawal => {
                            if (!withdrawal.assigned_deliverer) {
                                const option = document.createElement('option');
                                option.value = withdrawal.id;
                                option.textContent = `${withdrawal.request_code} - ${withdrawal.client_name} (${withdrawal.amount} DT)`;
                                option.dataset.client = withdrawal.client_name;
                                option.dataset.amount = withdrawal.amount;
                                option.dataset.code = withdrawal.delivery_receipt_code;
                                select.appendChild(option);
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Erreur chargement retraits pour assignation:', error);
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            const params = new URLSearchParams();
            
            switch (tab) {
                case 'high_balance':
                    params.set('high_balance_only', '1');
                    break;
                case 'urgent_emptying':
                    params.set('min_balance', '200');
                    break;
            }
            
            if (params.toString() && !['cash_deliveries', 'recent_emptyings'].includes(tab)) {
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            }
        },

        toggleAllDeliverers(event) {
            const delivererIds = Array.from(document.querySelectorAll('tbody input[type="checkbox"]'))
                                          .map(cb => parseInt(cb.value));
            
            if (event.target.checked) {
                this.selectedDeliverers = delivererIds;
            } else {
                this.selectedDeliverers = [];
            }
        },

        calculateSelectedWalletAmount() {
            return this.selectedDeliverers.reduce((total, id) => {
                const row = document.querySelector(`input[value="${id}"]`).closest('tr');
                const balance = parseFloat(row.getAttribute('data-balance') || 0);
                return total + balance;
            }, 0);
        },

        assignCashDeliveryFromList(withdrawalId) {
            // TODO: Implement assignment from the cash deliveries list
            showToast('Assignation depuis la liste à implémenter', 'info');
        },

        generateDeliveryReceipt(withdrawalId) {
            window.open(`/commercial/withdrawals/${withdrawalId}/receipt`, '_blank');
        },

        viewEmptyingReceipt(emptyingId) {
            window.open(`/commercial/deliverers/emptying/${emptyingId}/receipt`, '_blank');
        }
    }
}

// Global variables
let currentDelivererId = null;

// Quick Actions
function quickEmptyWallet(delivererId, delivererName, walletBalance) {
    currentDelivererId = delivererId;
    document.getElementById('empty-deliverer-name').textContent = delivererName;
    document.getElementById('empty-wallet-balance').textContent = parseFloat(walletBalance).toFixed(3);
    document.getElementById('empty-physical-amount').placeholder = `${parseFloat(walletBalance).toFixed(3)} DT (montant exact)`;
    document.getElementById('quick-empty-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeQuickEmptyModal() {
    document.getElementById('quick-empty-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('quick-empty-form').reset();
    currentDelivererId = null;
}

function assignCashDelivery(delivererId, delivererName) {
    currentDelivererId = delivererId;
    document.getElementById('cash-deliverer-name').textContent = delivererName;
    
    // Get deliverer balance from the table
    const row = document.querySelector(`input[value="${delivererId}"]`).closest('tr');
    const balance = row.getAttribute('data-balance') || '0';
    document.getElementById('cash-deliverer-balance').textContent = parseFloat(balance).toFixed(3);
    
    document.getElementById('assign-cash-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAssignCashModal() {
    document.getElementById('assign-cash-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('assign-cash-form').reset();
    document.getElementById('cash-payment-details').classList.add('hidden');
    currentDelivererId = null;
}

// Utility Functions
function handleQuickAction(action) {
    const params = new URLSearchParams();
    
    switch (action) {
        case 'show_high_balance':
            params.set('high_balance_only', '1');
            break;
        case 'show_urgent':
            params.set('min_balance', '200');
            break;
        case 'show_empty':
            params.set('min_balance', '0');
            params.set('max_balance', '0.001');
            break;
        case 'bulk_empty':
            openBulkEmptyModal();
            return;
    }
    
    if (params.toString()) {
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
}

function showHighBalanceOnly() {
    window.location.href = '{{ route("commercial.deliverers.index") }}?min_balance=200';
}

function generateDeliverersReport() {
    showToast('Génération du rapport en cours...', 'info');
    // TODO: Implement report generation
}

function openBulkEmptyModal() {
    showToast('Vidage groupé à implémenter', 'info');
    // TODO: Implement bulk empty modal
}

function bulkEmptySelected() {
    showToast('Vidage des wallets sélectionnés à implémenter', 'info');
}

function bulkAssignPayments() {
    showToast('Assignation groupée de paiements à implémenter', 'info');
}

function exportDelivererData(delivererId) {
    window.open(`/commercial/deliverers/${delivererId}/export`, '_blank');
}

function sendNotificationToDeliverer(delivererId) {
    showToast('Envoi de notification à implémenter', 'info');
}

// Form Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Quick Empty Form
    const emptyForm = document.getElementById('quick-empty-form');
    if (emptyForm) {
        emptyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const walletBalance = parseFloat(document.getElementById('empty-wallet-balance').textContent);
            const physicalAmount = document.getElementById('empty-physical-amount').value;
            
            const formData = {
                physical_amount: physicalAmount || walletBalance,
                notes: document.getElementById('empty-notes').value,
                force_empty: document.getElementById('empty-force').checked
            };
            
            try {
                const response = await fetch(`/commercial/deliverers/${currentDelivererId}/empty-wallet`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('Wallet vidé avec succès', 'success');
                    closeQuickEmptyModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors du vidage', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
    
    // Assign Cash Form
    const cashForm = document.getElementById('assign-cash-form');
    if (cashForm) {
        const withdrawalSelect = document.getElementById('cash-withdrawal-id');
        const detailsDiv = document.getElementById('cash-payment-details');
        
        withdrawalSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('cash-client-name').textContent = option.dataset.client;
                document.getElementById('cash-amount').textContent = option.dataset.amount;
                document.getElementById('cash-code').textContent = option.dataset.code;
                detailsDiv.classList.remove('hidden');
            } else {
                detailsDiv.classList.add('hidden');
            }
        });
        
        cashForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                withdrawal_id: document.getElementById('cash-withdrawal-id').value,
                notes: document.getElementById('cash-assign-notes').value
            };
            
            try {
                const response = await fetch(`/commercial/deliverers/${currentDelivererId}/assign-cash-delivery`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('Paiement assigné au livreur avec succès', 'success');
                    closeAssignCashModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de l\'assignation', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
});
</script>
@endpush