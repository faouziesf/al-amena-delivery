@extends('layouts.commercial')

@section('title', 'Demandes de Paiement')
@section('page-title', 'Demandes de Paiement')
@section('page-description', 'Gérez les demandes de retrait - virements bancaires et livraisons espèces')

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
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
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

    <!-- Quick Stats Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600" x-text="stats.pending_count || 0">{{ $stats['total_pending'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">En Attente</div>
                <div class="text-xs text-orange-500" x-text="(stats.pending_amount || 0).toFixed(3) + ' DT'">{{ number_format($stats['total_amount_pending'] ?? 0, 3) }} DT</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600" x-text="stats.bank_transfers_pending || 0">{{ $stats['bank_transfers_pending'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Virements</div>
                <div class="text-xs text-blue-500">À traiter</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600" x-text="stats.cash_deliveries_pending || 0">{{ $stats['cash_deliveries_pending'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Espèces</div>
                <div class="text-xs text-green-500">À assigner</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600" x-text="stats.in_progress_count || 0">{{ $stats['approved_awaiting_delivery'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">En Cours</div>
                <div class="text-xs text-purple-500">Chez livreurs</div>
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
                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs" x-text="totalWithdrawals || 0">{{ $withdrawals->total() ?? 0 }}</span>
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

        <!-- Advanced Filters -->
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
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 transition-colors">
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

            <!-- Enhanced Table -->
            <div class="overflow-x-auto">
                <div class="bg-gray-50 px-6 py-2 text-xs text-gray-500 uppercase tracking-wider font-medium border-b">
                    Affichage {{ $withdrawals->firstItem() ?? 0 }}-{{ $withdrawals->lastItem() ?? 0 }} sur {{ $withdrawals->total() ?? 0 }} demandes
                </div>
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
                            data-amount="{{ $withdrawal->amount }}"
                            data-method="{{ $withdrawal->method }}">
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
                                            📄 Code: {{ $withdrawal->delivery_receipt_code }}
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
                                            💰 Solde: {{ number_format($withdrawal->client->wallet->balance ?? 0, 3) }} DT
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
                                        @php
                                            $iban = $withdrawal->bank_details['iban'] ?? null;
                                        @endphp
                                        @if($iban)
                                            <div class="text-xs text-gray-500" title="{{ $iban }}">
                                                🏦 IBAN: {{ substr($iban, 0, 10) }}...
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $withdrawal->status_color }}">
                                    {{ $withdrawal->status_display }}
                                </span>
                                @if($withdrawal->status === 'IN_PROGRESS' && $withdrawal->assignedDeliverer)
                                    <div class="text-xs text-purple-600 mt-1">
                                        🚚 Assigné: {{ $withdrawal->assignedDeliverer->name }}
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
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination with Stats -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span>{{ $withdrawals->total() }} demande(s) au total</span>
                        @if($withdrawals->hasPages())
                            <span>•</span>
                            <span>Page {{ $withdrawals->currentPage() }} sur {{ $withdrawals->lastPage() }}</span>
                        @endif
                    </div>
                    <div>
                        {{ $withdrawals->appends(request()->query())->links() }}
                    </div>
                </div>
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

@include('components.commercial.withdrawal-modals', ['withdrawal' => null])
@endsection

@push('scripts')
<script>
function withdrawalsApp() {
    return {
        activeTab: '{{ request("status") ? "pending" : (request("method") === "BANK_TRANSFER" ? "bank_transfers" : (request("method") === "CASH_DELIVERY" ? "cash_deliveries" : "all")) }}',
        selectedWithdrawals: [],
        totalWithdrawals: {{ $withdrawals->total() ?? 0 }},
        availableDeliverers: [],
        stats: {
            pending_count: {{ $stats['total_pending'] ?? 0 }},
            pending_amount: {{ $stats['total_amount_pending'] ?? 0 }},
            bank_transfers_pending: {{ $stats['bank_transfers_pending'] ?? 0 }},
            cash_deliveries_pending: {{ $stats['cash_deliveries_pending'] ?? 0 }},
            in_progress_count: {{ $stats['approved_awaiting_delivery'] ?? 0 }},
            completed_today: {{ $stats['processed_by_me_today'] ?? 0 }}
        },

        async init() {
            await this.loadStats();
            await this.loadDeliverers();

            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.loadStats();
            }, 30000);
        },

        async loadStats() {
            try {
                const response = await fetch('/commercial/api/withdrawals/stats');
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
                const response = await fetch('/commercial/api/deliverers/available');
                if (response.ok) {
                    this.availableDeliverers = await response.json();
                    this.updateDeliverersDropdown();
                }
            } catch (error) {
                console.error('Erreur chargement livreurs:', error);
            }
        },

        updateDeliverersDropdown() {
            const select = document.getElementById('assign-deliverer-id');
            if (select) {
                select.innerHTML = '<option value="">Sélectionner un livreur...</option>';
                this.availableDeliverers.forEach(deliverer => {
                    const option = document.createElement('option');
                    option.value = deliverer.id;
                    option.textContent = `${deliverer.name} (${deliverer.wallet_balance} DT)`;
                    select.appendChild(option);
                });
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
            showNotification('Demande de retrait approuvée avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showNotification(data.message || 'Erreur lors de l\'approbation', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
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
            showNotification('Demande de retrait rejetée', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showNotification(data.message || 'Erreur lors du rejet', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Modal functions
async function assignToDeliverer(withdrawalId) {
    try {
        currentWithdrawalId = withdrawalId;

        // Load withdrawal details
        const response = await fetch(`/commercial/withdrawals/${withdrawalId}`);
        if (response.ok) {
            // Show modal
            document.getElementById('assign-deliverer-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        showNotification('Erreur lors du chargement des détails', 'error');
    }
}

function closeAssignDelivererModal() {
    document.getElementById('assign-deliverer-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('assign-deliverer-form').reset();
    currentWithdrawalId = null;
}

async function markAsDelivered(withdrawalId) {
    try {
        currentWithdrawalId = withdrawalId;

        // Load withdrawal details
        const response = await fetch(`/commercial/withdrawals/${withdrawalId}`);
        if (response.ok) {
            // Show modal
            document.getElementById('mark-delivered-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    } catch (error) {
        showNotification('Erreur lors du chargement des détails', 'error');
    }
}

function closeMarkDeliveredModal() {
    document.getElementById('mark-delivered-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('mark-delivered-form').reset();
    currentWithdrawalId = null;
}

// Bulk operations
async function bulkApprove(method) {
    const app = window.withdrawalsApp;
    if (!app || app.selectedWithdrawals.length === 0) {
        showNotification('Veuillez sélectionner au moins une demande', 'warning');
        return;
    }

    const methodText = method === 'BANK_TRANSFER' ? 'virements bancaires' : 'livraisons espèces';
    if (!confirm(`Approuver ${app.selectedWithdrawals.length} demandes de ${methodText} ?`)) {
        return;
    }

    try {
        const response = await fetch('/commercial/withdrawals/bulk-approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                withdrawal_ids: app.selectedWithdrawals,
                bulk_notes: `Approbation groupée ${methodText} via interface commercial`
            })
        });

        if (response.ok) {
            showNotification('Demandes approuvées avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showNotification(data.message || 'Erreur lors de l\'approbation groupée', 'error');
        }
    } catch (error) {
        showNotification('Erreur de connexion', 'error');
    }
}

// Utility functions
function showPendingOnly() {
    window.location.href = '{{ route("commercial.withdrawals.index") }}?status=PENDING';
}

function generateWithdrawalsReport() {
    showNotification('Génération du rapport en cours...', 'info');
    // TODO: Implement report generation
}

function openBulkApprovalModal() {
    showNotification('Sélectionnez des demandes pour effectuer une approbation groupée', 'info');
}

// Notification system
function showNotification(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm transform transition-all duration-300`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 3000);
}

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Store the app instance globally
    window.withdrawalsApp = Alpine.$data(document.querySelector('[x-data="withdrawalsApp()"]'));

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
                    showNotification('Retrait assigné au livreur avec succès', 'success');
                    closeAssignDelivererModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showNotification(data.message || 'Erreur lors de l\'assignation', 'error');
                }
            } catch (error) {
                showNotification('Erreur de connexion', 'error');
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
                client_signature: null
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
                    showNotification('Livraison confirmée avec succès', 'success');
                    closeMarkDeliveredModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showNotification(data.message || 'Erreur lors de la confirmation', 'error');
                }
            } catch (error) {
                showNotification('Erreur de connexion', 'error');
            }
        });
    }
});
</script>
@endpush