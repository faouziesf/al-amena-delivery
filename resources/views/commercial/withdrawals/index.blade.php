@extends('layouts.commercial')

@section('title', 'Gestion des Retraits')
@section('page-title', 'Demandes de Retrait')
@section('page-description', 'Approuvez et gérez les demandes de retrait des clients (virements et espèces)')

@section('header-actions')
<div class="flex items-center space-x-3">
    <div class="hidden lg:flex items-center space-x-4 text-sm">
        <div class="flex items-center space-x-2" x-show="stats.pending_count > 0">
            <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
            <span class="text-orange-600 font-medium" x-text="stats.pending_count + ' en attente'"></span>
        </div>
        <div class="text-gray-500">
            <span x-text="(stats.pending_amount || 0).toFixed(3)"></span> DT à traiter
        </div>
    </div>
    <button onclick="generateWithdrawalsReport()" 
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Rapport
    </button>
    <button onclick="openBulkApprovalModal()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Approbation Groupée
    </button>
</div>
@endsection

@section('content')
<div x-data="withdrawalsApp()" x-init="init()">
    <!-- Priority Alert Banner -->
    <div x-show="stats.pending_count > 10" 
         class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl p-4 mb-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0H8"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Nombreuses Demandes en Attente</h3>
                    <p x-text="`${stats.pending_count} demandes de retrait nécessitent votre traitement (${(stats.pending_amount || 0).toFixed(3)} DT au total)`"></p>
                </div>
            </div>
            <button onclick="showPendingOnly()" 
                    class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg font-medium transition-colors">
                Traiter Maintenant
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">En Attente</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.pending_count || {{ $stats['total_pending'] ?? 0 }}"></p>
                    <p class="text-xs text-orange-600 mt-1" x-text="(stats.pending_amount || 0).toFixed(3) + ' DT'"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Virements Bancaires</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.bank_transfers_pending || {{ $stats['bank_transfers_pending'] ?? 0 }}"></p>
                    <p class="text-xs text-blue-600 mt-1">À traiter</p>
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
                    <p class="text-sm text-gray-600">Livraisons Espèces</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.cash_deliveries_pending || {{ $stats['cash_deliveries_pending'] ?? 0 }}"></p>
                    <p class="text-xs text-green-600 mt-1">À assigner</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">En Livraison</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.in_progress_count || {{ $stats['approved_awaiting_delivery'] ?? 0 }}"></p>
                    <p class="text-xs text-purple-600 mt-1">Chez livreurs</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Terminés Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.completed_today || {{ $stats['processed_by_me_today'] ?? 0 }}"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="setActiveTab('all')" 
                        :class="activeTab === 'all' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Toutes les Demandes
                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.total || 0"></span>
                </button>
                <button @click="setActiveTab('pending')" 
                        :class="activeTab === 'pending' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    En Attente
                    <span class="ml-2 bg-orange-100 text-orange-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.pending_count || 0"></span>
                </button>
                <button @click="setActiveTab('bank_transfers')" 
                        :class="activeTab === 'bank_transfers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Virements Bancaires
                    <span class="ml-2 bg-blue-100 text-blue-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.bank_transfers_pending || 0"></span>
                </button>
                <button @click="setActiveTab('cash_deliveries')" 
                        :class="activeTab === 'cash_deliveries' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Livraisons Espèces
                    <span class="ml-2 bg-green-100 text-green-900 py-0.5 px-2.5 rounded-full text-xs" x-text="(stats.cash_deliveries_pending || 0) + (stats.in_progress_count || 0)"></span>
                </button>
                <button @click="setActiveTab('completed')" 
                        :class="activeTab === 'completed' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Terminées
                    <span class="ml-2 bg-emerald-100 text-emerald-900 py-0.5 px-2.5 rounded-full text-xs" x-text="stats.completed_today || 0"></span>
                </button>
            </nav>
        </div>

        <!-- Filters -->
        <div class="p-6">
            <form method="GET" action="{{ route('commercial.withdrawals.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Code demande, client..."
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Méthode</label>
                        <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Toutes méthodes</option>
                            <option value="BANK_TRANSFER" {{ request('method') == 'BANK_TRANSFER' ? 'selected' : '' }}>Virement bancaire</option>
                            <option value="CASH_DELIVERY" {{ request('method') == 'CASH_DELIVERY' ? 'selected' : '' }}>Livraison espèces</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant minimum</label>
                        <input type="number" name="min_amount" value="{{ request('min_amount') }}" step="0.001"
                               placeholder="0.000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant maximum</label>
                        <input type="number" name="max_amount" value="{{ request('max_amount') }}" step="0.001"
                               placeholder="9999.999"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 focus:ring-2 focus:ring-purple-500 transition-colors">
                            Filtrer
                        </button>
                        <a href="{{ route('commercial.withdrawals.index') }}" 
                           class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                    </div>
                </div>
                
                <!-- Quick Filters -->
                <div class="flex items-center space-x-6 pt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="processed_by_me" value="1" 
                               {{ request('processed_by_me') ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">Traitées par moi</span>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdrawals List -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
        @if($withdrawals->count() > 0)
            <!-- Selection Header -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200" 
                 x-show="selectedWithdrawals.length > 0" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-900" 
                              x-text="`${selectedWithdrawals.length} demande(s) sélectionnée(s)`"></span>
                        <span class="text-sm text-gray-600" 
                              x-text="`(${calculateSelectedAmount().toFixed(3)} DT au total)`"></span>
                        <button @click="selectedWithdrawals = []" 
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Désélectionner tout
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="bulkApprove('BANK_TRANSFER')" 
                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                            Approuver Virements
                        </button>
                        <button onclick="bulkApprove('CASH_DELIVERY')" 
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                            Approuver Espèces
                        </button>
                        <button onclick="bulkReject()" 
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                            Rejeter
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
                                <input type="checkbox" @change="toggleAllWithdrawals($event)" 
                                       class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Demande</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant & Méthode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Traité par</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créée</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50 transition-colors group" 
                            :data-amount="{{ $withdrawal->amount }}"
                            :data-method="{{ $withdrawal->method }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" :value="{{ $withdrawal->id }}" 
                                       x-model="selectedWithdrawals"
                                       class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $withdrawal->request_code }}</div>
                                    <div class="text-sm text-gray-500">{{ $withdrawal->created_at->diffForHumans() }}</div>
                                    @if($withdrawal->delivery_receipt_code)
                                        <div class="text-xs text-green-600 font-medium mt-1">
                                            Code: {{ $withdrawal->delivery_receipt_code }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center">
                                            <span class="text-sm font-bold text-white">{{ substr($withdrawal->client->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $withdrawal->client->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $withdrawal->client->email }}</div>
                                        <div class="text-xs text-gray-500">
                                            Solde: {{ number_format($withdrawal->client->wallet->balance ?? 0, 3) }} DT
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-lg font-bold text-gray-900">{{ number_format($withdrawal->amount, 3) }} DT</div>
                                    <div>
                                        @if($withdrawal->method === 'BANK_TRANSFER')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                                Virement bancaire
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                Livraison espèces
                                            </span>
                                        @endif
                                    </div>
                                    @if($withdrawal->bank_details && $withdrawal->method === 'BANK_TRANSFER')
                                        <div class="text-xs text-gray-500">
                                            IBAN: {{ substr($withdrawal->bank_details['iban'] ?? 'N/A', 0, 10) }}...
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $withdrawal->status_color }}">
                                    {{ $withdrawal->status_display }}
                                </span>
                                @if($withdrawal->status === 'IN_PROGRESS' && $withdrawal->assignedDeliverer)
                                    <div class="text-xs text-purple-600 mt-1">
                                        Assigné: {{ $withdrawal->assignedDeliverer->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($withdrawal->processedByCommercial)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-purple-600">
                                                {{ substr($withdrawal->processedByCommercial->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-900">{{ $withdrawal->processedByCommercial->name }}</div>
                                            @if($withdrawal->processed_at)
                                                <div class="text-xs text-gray-500">{{ $withdrawal->processed_at->diffForHumans() }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">Non traité</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $withdrawal->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Quick Actions based on status -->
                                    @if($withdrawal->canBeProcessed())
                                        <!-- Quick Approve -->
                                        <button onclick="quickApprove({{ $withdrawal->id }}, '{{ $withdrawal->method }}', {{ $withdrawal->amount }})" 
                                                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                title="Approbation rapide">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>

                                        <!-- Quick Reject -->
                                        <button onclick="quickReject({{ $withdrawal->id }})" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-100 transition-colors"
                                                title="Rejeter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif

                                    @if($withdrawal->status === 'APPROVED' && $withdrawal->method === 'CASH_DELIVERY')
                                        <!-- Assign to Deliverer -->
                                        <button onclick="assignToDeliverer({{ $withdrawal->id }})" 
                                                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-colors"
                                                title="Assigner livreur">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Generate Receipt -->
                                        <a href="{{ route('commercial.withdrawals.receipt', $withdrawal) }}" target="_blank"
                                           class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                           title="Générer bon de livraison">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if($withdrawal->status === 'IN_PROGRESS')
                                        <!-- Mark as Delivered -->
                                        <button onclick="markAsDelivered({{ $withdrawal->id }})" 
                                                class="text-emerald-600 hover:text-emerald-900 p-1 rounded hover:bg-emerald-100 transition-colors"
                                                title="Marquer comme livré">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- View Details -->
                                    <a href="{{ route('commercial.withdrawals.show', $withdrawal) }}" 
                                       class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                       title="Voir détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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
                                                @if($withdrawal->bank_details)
                                                    <button onclick="copyBankDetails({{ $withdrawal->id }})" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        Copier IBAN
                                                    </button>
                                                @endif
                                                <button onclick="duplicateWithdrawal({{ $withdrawal->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Dupliquer demande
                                                </button>
                                                <button onclick="exportWithdrawalData({{ $withdrawal->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Exporter données
                                                </button>
                                                <hr class="my-1">
                                                <button onclick="viewClientHistory({{ $withdrawal->client->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Historique client
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
                {{ $withdrawals->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune demande de retrait trouvée</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'method', 'status']))
                        Aucune demande ne correspond à vos critères.
                    @else
                        Aucune demande de retrait en cours.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'method', 'status']))
                        <a href="{{ route('commercial.withdrawals.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Voir toutes les demandes
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
<!-- Assign to Deliverer Modal -->
<div id="assign-deliverer-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Assigner à un Livreur</h3>
                <button onclick="closeAssignDelivererModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="assign-deliverer-form" class="p-6 space-y-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm text-green-700">
                        <div>Demande: <span id="assign-request-code" class="font-medium"></span></div>
                        <div>Montant: <span id="assign-amount" class="font-bold"></span> DT</div>
                        <div>Code livraison: <span id="assign-delivery-code" class="font-mono text-xs"></span></div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Livreur</label>
                    <select id="assign-deliverer-id" required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Sélectionner un livreur...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Le livreur recevra les espèces de votre part</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                    <textarea id="assign-notes" rows="3"
                              placeholder="Instructions spéciales pour la livraison..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">Important:</p>
                            <p>Vous devez remettre les espèces au livreur avant qu'il ne parte en livraison.</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                        Assigner
                    </button>
                    <button type="button" onclick="closeAssignDelivererModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Delivered Modal -->
<div id="mark-delivered-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Confirmer Livraison</h3>
                <button onclick="closeMarkDeliveredModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="mark-delivered-form" class="p-6 space-y-4">
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="text-sm text-emerald-700">
                        <div>Demande: <span id="delivered-request-code" class="font-medium"></span></div>
                        <div>Montant livré: <span id="delivered-amount" class="font-bold"></span> DT</div>
                        <div>Livreur: <span id="delivered-by" class="font-medium"></span></div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes de livraison</label>
                    <textarea id="delivery-notes" rows="3"
                              placeholder="Détails sur la livraison, signature du client..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="confirm-delivery" required
                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label for="confirm-delivery" class="text-sm text-gray-700">
                        Je confirme que les espèces ont été livrées au client
                    </label>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700">
                        Confirmer Livraison
                    </button>
                    <button type="button" onclick="closeMarkDeliveredModal()" 
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
function withdrawalsApp() {
    return {
        activeTab: 'all',
        selectedWithdrawals: [],
        stats: {
            pending_count: {{ $stats['total_pending'] ?? 0 }},
            pending_amount: {{ $stats['total_amount_pending'] ?? 0 }},
            bank_transfers_pending: {{ $stats['bank_transfers_pending'] ?? 0 }},
            cash_deliveries_pending: {{ $stats['cash_deliveries_pending'] ?? 0 }},
            in_progress_count: {{ $stats['approved_awaiting_delivery'] ?? 0 }},
            completed_today: {{ $stats['processed_by_me_today'] ?? 0 }}
        },

        init() {
            this.loadStats();
            this.loadDeliverers();
            
            // Auto-refresh every 45 seconds
            setInterval(() => {
                this.loadStats();
            }, 45000);
        },

        async loadStats() {
            try {
                const response = await fetch('/commercial/withdrawals/api/stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                }
            } catch (error) {
                console.error('Erreur chargement stats retraits:', error);
            }
        },

        async loadDeliverers() {
            try {
                const response = await fetch('/commercial/deliverers/api/search?q=');
                if (response.ok) {
                    const deliverers = await response.json();
                    const select = document.getElementById('assign-deliverer-id');
                    if (select) {
                        select.innerHTML = '<option value="">Sélectionner un livreur...</option>';
                        deliverers.forEach(deliverer => {
                            const option = document.createElement('option');
                            option.value = deliverer.id;
                            option.textContent = `${deliverer.name} (${deliverer.wallet_balance} DT)`;
                            select.appendChild(option);
                        });
                    }
                }
            } catch (error) {
                console.error('Erreur chargement livreurs:', error);
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            const params = new URLSearchParams();
            
            switch (tab) {
                case 'pending':
                    params.set('status', 'PENDING');
                    break;
                case 'bank_transfers':
                    params.set('method', 'BANK_TRANSFER');
                    break;
                case 'cash_deliveries':
                    params.set('method', 'CASH_DELIVERY');
                    break;
                case 'completed':
                    params.set('status', 'COMPLETED');
                    break;
            }
            
            if (params.toString()) {
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            } else {
                window.location.href = window.location.pathname;
            }
        },

        toggleAllWithdrawals(event) {
            const withdrawalIds = Array.from(document.querySelectorAll('tbody input[type="checkbox"]'))
                                           .map(cb => parseInt(cb.value));
            
            if (event.target.checked) {
                this.selectedWithdrawals = withdrawalIds;
            } else {
                this.selectedWithdrawals = [];
            }
        },

        calculateSelectedAmount() {
            return this.selectedWithdrawals.reduce((total, id) => {
                const row = document.querySelector(`input[value="${id}"]`).closest('tr');
                const amount = parseFloat(row.getAttribute('data-amount') || 0);
                return total + amount;
            }, 0);
        }
    }
}

// Global variables
let currentWithdrawalId = null;

// Quick Actions
async function quickApprove(withdrawalId, method, amount) {
    if (!confirm(`Approuver cette demande de retrait de ${amount.toFixed(3)} DT ?`)) {
        return;
    }

    try {
        const response = await fetch(`/commercial/withdrawals/${withdrawalId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notes: 'Approbation rapide via interface commercial'
            })
        });

        if (response.ok) {
            showToast('Demande de retrait approuvée avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showToast(data.message || 'Erreur lors de l\'approbation', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

async function quickReject(withdrawalId) {
    const reason = prompt('Motif du rejet:');
    if (!reason) return;

    try {
        const response = await fetch(`/commercial/withdrawals/${withdrawalId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                rejection_reason: reason
            })
        });

        if (response.ok) {
            showToast('Demande de retrait rejetée', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showToast(data.message || 'Erreur lors du rejet', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

// Modal functions
function assignToDeliverer(withdrawalId) {
    // TODO: Load withdrawal details and populate modal
    currentWithdrawalId = withdrawalId;
    document.getElementById('assign-deliverer-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAssignDelivererModal() {
    document.getElementById('assign-deliverer-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('assign-deliverer-form').reset();
    currentWithdrawalId = null;
}

function markAsDelivered(withdrawalId) {
    // TODO: Load withdrawal details and populate modal
    currentWithdrawalId = withdrawalId;
    document.getElementById('mark-delivered-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeMarkDeliveredModal() {
    document.getElementById('mark-delivered-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('mark-delivered-form').reset();
    currentWithdrawalId = null;
}

// Utility functions
function showPendingOnly() {
    window.location.href = '{{ route("commercial.withdrawals.index") }}?status=PENDING';
}

function generateWithdrawalsReport() {
    showToast('Génération du rapport en cours...', 'info');
    // TODO: Implement report generation
}

function openBulkApprovalModal() {
    showToast('Approbation groupée à implémenter', 'info');
    // TODO: Implement bulk approval modal
}

function copyBankDetails(withdrawalId) {
    // TODO: Implement copy bank details functionality
    showToast('Détails bancaires copiés', 'success');
}

function duplicateWithdrawal(withdrawalId) {
    showToast('Duplication de demande à implémenter', 'info');
}

function exportWithdrawalData(withdrawalId) {
    window.open(`/commercial/withdrawals/${withdrawalId}/export`, '_blank');
}

function viewClientHistory(clientId) {
    window.location.href = `/commercial/clients/${clientId}?tab=withdrawals`;
}

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Assign Deliverer Form
    const assignForm = document.getElementById('assign-deliverer-form');
    if (assignForm) {
        assignForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                deliverer_id: document.getElementById('assign-deliverer-id').value,
                notes: document.getElementById('assign-notes').value
            };
            
            try {
                const response = await fetch(`/commercial/withdrawals/${currentWithdrawalId}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('Retrait assigné au livreur avec succès', 'success');
                    closeAssignDelivererModal();
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
    
    // Mark as Delivered Form
    const deliveredForm = document.getElementById('mark-delivered-form');
    if (deliveredForm) {
        deliveredForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                delivery_notes: document.getElementById('delivery-notes').value,
                client_signature: null // TODO: Implement signature capture if needed
            };
            
            try {
                const response = await fetch(`/commercial/withdrawals/${currentWithdrawalId}/delivered`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    showToast('Livraison confirmée avec succès', 'success');
                    closeMarkDeliveredModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la confirmation', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
});
</script>
@endpush