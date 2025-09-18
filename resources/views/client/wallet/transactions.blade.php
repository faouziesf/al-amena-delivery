@extends('layouts.client')

@section('title', 'Historique des transactions')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    📋 Historique des transactions
                </h1>
                <p class="text-gray-600">Consultez tous les mouvements de votre portefeuille</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('client.wallet.export') }}" 
                   class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-6 py-3 rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
                <a href="{{ route('client.wallet.index') }}" 
                   class="bg-white text-purple-600 border-2 border-purple-200 px-6 py-3 rounded-xl hover:bg-purple-50 transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour au portefeuille
                </a>
            </div>
        </div>

        <!-- Filtres avancés -->
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtres de recherche
            </h3>
            
            <form method="GET" action="{{ route('client.wallet.transactions') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Type de transaction -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type de transaction</label>
                        <select name="type" id="type" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-white">
                            <option value="">🔍 Tous les types</option>
                            <option value="CREDIT" {{ request('type') === 'CREDIT' ? 'selected' : '' }}>💰 Crédits</option>
                            <option value="DEBIT" {{ request('type') === 'DEBIT' ? 'selected' : '' }}>💸 Débits</option>
                            <option value="PACKAGE_PAYMENT" {{ request('type') === 'PACKAGE_PAYMENT' ? 'selected' : '' }}>📦 Paiement colis</option>
                            <option value="WITHDRAWAL" {{ request('type') === 'WITHDRAWAL' ? 'selected' : '' }}>🏧 Retraits</option>
                        </select>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select name="status" id="status" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-white">
                            <option value="">📊 Tous les statuts</option>
                            <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>✅ Terminé</option>
                            <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>⏳ En attente</option>
                            <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>❌ Échoué</option>
                        </select>
                    </div>

                    <!-- Date de début -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                        <input type="date" 
                               name="date_from" 
                               id="date_from" 
                               value="{{ request('date_from') }}"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                        <input type="date" 
                               name="date_to" 
                               id="date_to" 
                               value="{{ request('date_to') }}"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>
                </div>

                <!-- Recherche par mot-clé -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}"
                               placeholder="Rechercher par description ou ID de transaction..."
                               class="block w-full pl-12 rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-3 pt-4">
                    <button type="submit" 
                            class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>
                    <a href="{{ route('client.wallet.transactions') }}" 
                       class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistiques des résultats -->
        @php
            $filteredTransactions = $transactions->getCollection();
            $totalCredits = $filteredTransactions->where('amount', '>', 0)->sum('amount');
            $totalDebits = abs($filteredTransactions->where('amount', '<', 0)->sum('amount'));
            $netAmount = $totalCredits - $totalDebits;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-emerald-100 to-green-100 rounded-2xl p-6 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-700 text-sm font-medium">Total crédité</p>
                        <p class="text-2xl font-bold text-emerald-800">{{ number_format($totalCredits, 3) }} DT</p>
                    </div>
                    <div class="bg-emerald-200 rounded-full p-3">
                        <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-100 to-pink-100 rounded-2xl p-6 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-700 text-sm font-medium">Total débité</p>
                        <p class="text-2xl font-bold text-red-800">{{ number_format($totalDebits, 3) }} DT</p>
                    </div>
                    <div class="bg-red-200 rounded-full p-3">
                        <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-700 text-sm font-medium">Solde net</p>
                        <p class="text-2xl font-bold {{ $netAmount >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                            {{ number_format($netAmount, 3) }} DT
                        </p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-700 text-sm font-medium">Nb transactions</p>
                        <p class="text-2xl font-bold text-blue-800">{{ $transactions->total() }}</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des transactions -->
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2 sm:mb-0">
                        📊 Transactions 
                        <span class="text-sm font-normal text-gray-500">
                            ({{ $transactions->total() }} résultats)
                        </span>
                    </h3>
                    <div class="text-sm text-gray-500">
                        Page {{ $transactions->currentPage() }} sur {{ $transactions->lastPage() }}
                    </div>
                </div>
            </div>

            @if($transactions->count() > 0)
                <!-- Liste responsive des transactions -->
                <div class="divide-y divide-gray-100">
                    @foreach($transactions as $transaction)
                        <div class="p-6 hover:bg-gradient-to-r hover:from-purple-25 hover:to-indigo-25 transition-all duration-300">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                                <!-- Informations principales -->
                                <div class="flex items-start flex-1">
                                    <!-- Icône du type de transaction -->
                                    <div class="flex-shrink-0 mr-4">
                                        @if($transaction->amount > 0)
                                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-green-100 rounded-2xl flex items-center justify-center shadow-sm">
                                                @if($transaction->type === 'PACKAGE_PAYMENT')
                                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        @else
                                            <div class="w-12 h-12 bg-gradient-to-br from-red-100 to-pink-100 rounded-2xl flex items-center justify-center shadow-sm">
                                                @if($transaction->type === 'WITHDRAWAL')
                                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Détails de la transaction -->
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-3 mb-3">
                                            <h4 class="text-lg font-semibold text-gray-900">
                                                {{ $transaction->description }}
                                            </h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->status_color }}">
                                                {{ $transaction->status_display }}
                                            </span>
                                        </div>

                                        <!-- Informations détaillées -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                    {{ $transaction->transaction_id }}
                                                </span>
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                                </svg>
                                                <span>{{ $transaction->type_display }}</span>
                                            </div>

                                            @if($transaction->package_id)
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                    <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                                                       class="text-purple-600 hover:text-purple-800 font-medium">
                                                        {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>

                                        @if($transaction->wallet_balance_before !== null && $transaction->wallet_balance_after !== null)
                                            <div class="mt-3 flex items-center text-xs text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                                Solde: {{ number_format($transaction->wallet_balance_before, 3) }} DT 
                                                → {{ number_format($transaction->wallet_balance_after, 3) }} DT
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Montant et actions -->
                                <div class="flex items-center justify-between lg:justify-end gap-4 mt-4 lg:mt-0 lg:ml-6">
                                    <div class="text-right">
                                        <p class="text-xl font-bold {{ $transaction->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $transaction->formatted_amount }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    
                                    <a href="{{ route('client.wallet.transaction.show', $transaction) }}" 
                                       class="bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 px-4 py-2 rounded-xl hover:from-purple-200 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105 text-sm font-medium">
                                        Détails →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $transactions->appends(request()->query())->links('pagination::tailwind') }}
                        </div>
                    </div>
                @endif
            @else
                <!-- État vide -->
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Aucune transaction trouvée</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        @if(request()->hasAny(['type', 'status', 'date_from', 'date_to', 'search']))
                            Aucune transaction ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                        @else
                            Vous n'avez pas encore de transactions dans votre portefeuille. Commencez par créer votre premier colis !
                        @endif
                    </p>
                    
                    <div class="flex flex-wrap justify-center gap-3">
                        @if(request()->hasAny(['type', 'status', 'date_from', 'date_to', 'search']))
                            <a href="{{ route('client.wallet.transactions') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Supprimer les filtres
                            </a>
                        @else
                            <a href="{{ route('client.packages.create') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Créer mon premier colis
                            </a>
                        @endif
                        
                        <a href="{{ route('client.wallet.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-white text-purple-600 border-2 border-purple-200 rounded-xl hover:bg-purple-50 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Retour au portefeuille
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des transactions
    const transactions = document.querySelectorAll('[class*="hover:bg-gradient-to-r"]');
    transactions.forEach((transaction, index) => {
        transaction.style.opacity = '0';
        transaction.style.transform = 'translateX(20px)';
        setTimeout(() => {
            transaction.style.transition = 'all 0.5s ease';
            transaction.style.opacity = '1';
            transaction.style.transform = 'translateX(0)';
        }, index * 50);
    });

    // Auto-submit formulaire de recherche avec debounce
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    // this.closest('form').submit();
                }
            }, 500);
        });
    }

    // Amélioration des filtres avec URL params
    const form = document.querySelector('form[method="GET"]');
    if (form) {
        const inputs = form.querySelectorAll('select, input[type="date"]');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                // Auto-submit après sélection
                if (this.tagName === 'SELECT') {
                    setTimeout(() => form.submit(), 100);
                }
            });
        });
    }

    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Ctrl + F pour focus sur recherche
        if (e.ctrlKey && e.key === 'f' && searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape pour effacer recherche
        if (e.key === 'Escape' && searchInput) {
            searchInput.value = '';
            searchInput.blur();
        }
    });

    // Mise en surbrillance des nouvelles transactions (moins de 24h)
    const now = new Date();
    const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
    
    transactions.forEach(transaction => {
        const dateElements = transaction.querySelectorAll('time, [datetime]');
        dateElements.forEach(element => {
            const dateValue = element.dateTime || element.textContent;
            const transactionDate = new Date(dateValue);
            
            if (transactionDate > oneDayAgo) {
                const indicator = document.createElement('span');
                indicator.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2 animate-pulse';
                indicator.textContent = '🆕 Nouveau';
                element.parentNode.appendChild(indicator);
            }
        });
    });
});

// Lazy loading pour les images/icons si nécessaire
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
        }
    });
}, observerOptions);

// Observer les cartes de transactions
document.querySelectorAll('.bg-white').forEach(card => {
    observer.observe(card);
});
</script>
@endpush

@push('styles')
<style>
.purple-25 {
    background-color: rgba(139, 92, 246, 0.025);
}

.hover\:from-purple-25:hover {
    background-image: linear-gradient(to right, rgba(139, 92, 246, 0.025), rgba(99, 102, 241, 0.025));
}

/* Animation fade-in */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

/* Responsive design amélioré */
@media (max-width: 640px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

/* États de hover améliorés */
.transaction-card:hover {
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
}

/* Pagination custom */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.pagination a,
.pagination span {
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.pagination a {
    background: white;
    color: #7c3aed;
    border: 2px solid #e2e8f0;
}

.pagination a:hover {
    background: #f3f4f6;
    border-color: #7c3aed;
    transform: scale(1.05);
}

.pagination .current {
    background: linear-gradient(to right, #7c3aed, #6366f1);
    color: white;
    border: 2px solid transparent;
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
@endpush
@endsection