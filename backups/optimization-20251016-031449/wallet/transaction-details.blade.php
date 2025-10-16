@extends('layouts.client')

@section('title', "Transaction #{$transaction->transaction_id}")

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6 px-4 py-4 lg:px-6 lg:py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header avec navigation -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 flex-col sm:flex-row">
            <div class="flex items-center mb-4 sm:mb-0 flex-col sm:flex-row">
                <a href="{{ route('client.wallet.transactions') }}" 
                   class="flex items-center text-purple-600 hover:text-purple-800 transition-all duration-300 mr-6 group flex-col sm:flex-row">
                    <div class="bg-purple-100 rounded-full p-2 mr-3 group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </div>
                    <span class="font-medium">Retour aux transactions</span>
                </a>
                <div class="text-gray-400 mx-2">•</div>
                <a href="{{ route('client.wallet.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors font-medium">
                    Portefeuille
                </a>
            </div>
            
            <!-- Badge de statut -->
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold {{ $transaction->status_color }} shadow-md hover:shadow-xl flex-col sm:flex-row">
                {{ $transaction->status_display }}
            </span>
        </div>

        <!-- Titre principal -->
        <div class="mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                📊 Transaction
            </h1>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-col sm:flex-row">
                <div class="font-mono text-lg text-gray-700 bg-gray-100 px-4 py-2 rounded-2xl">
                    #{{ Str::limit($transaction->transaction_id, 20) }}
                </div>
                <div class="flex items-center text-gray-600 flex-col sm:flex-row">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $transaction->created_at->format('d/m/Y à H:i:s') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Informations principales -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-4 sm:px-5 lg:px-6 py-4 border-b border-purple-100">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Détails de la transaction
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-5 lg:p-6">
                        <!-- Montant principal avec icône -->
                        <div class="flex items-center justify-center mb-8 flex-col sm:flex-row">
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-4 flex-col sm:flex-row">
                                    @if($transaction->amount > 0)
                                        <div class="w-16 h-16 bg-gradient-to-r from-purple-100 to-purple-200 rounded-full flex items-center justify-center shadow-lg flex-col sm:flex-row">
                                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-16 h-16 bg-gradient-to-r from-purple-200 to-purple-300 rounded-full flex items-center justify-center shadow-lg flex-col sm:flex-row">
                                            <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-4xl font-bold {{ $transaction->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }} mb-2">
                                    {{ $transaction->formatted_amount }}
                                </p>
                                <p class="text-gray-600 font-medium">{{ $transaction->type_display }}</p>
                            </div>
                        </div>

                        <!-- Grille d'informations -->
                        <div class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-2 sm:gap-3 lg:gap-4 lg:gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">ID de transaction</p>
                                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-2xl flex-col sm:flex-row">
                                        <span class="font-mono text-sm text-gray-900">{{ $transaction->transaction_id }}</span>
                                        <button onclick="copyToClipboard('{{ $transaction->transaction_id }}')" 
                                                class="text-purple-600 hover:text-purple-800 transition-colors" 
                                                title="Copier l'ID">
                                            <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">Statut</p>
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $transaction->status_color }} flex-col sm:flex-row">
                                        <div class="w-2 h-2 rounded-full mr-2 {{ $transaction->status === 'COMPLETED' ? 'bg-green-400' : ($transaction->status === 'PENDING' ? 'bg-amber-400 animate-pulse' : 'bg-red-400') }}"></div>
                                        {{ $transaction->status_display }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">Date de création</p>
                                    <div class="bg-gray-50 p-3 rounded-2xl">
                                        <p class="font-semibold text-gray-900">{{ $transaction->created_at->format('d/m/Y à H:i:s') }}</p>
                                        <p class="text-sm text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                @if($transaction->completed_at)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 mb-1">Date de traitement</p>
                                        <div class="bg-green-50 p-3 rounded-2xl border border-green-200">
                                            <p class="font-semibold text-green-900">{{ $transaction->completed_at->format('d/m/Y à H:i:s') }}</p>
                                            <p class="text-sm text-green-600">{{ $transaction->completed_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-600 mb-2">Description</p>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4">
                                <p class="text-gray-900 font-medium">{{ $transaction->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Impact sur le solde -->
                @if($transaction->wallet_balance_before !== null && $transaction->wallet_balance_after !== null)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-4 sm:px-5 lg:px-6 py-4 border-b border-purple-200">
                            <h3 class="text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                Impact sur le solde
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-5 lg:p-6">
                            <!-- Visualisation du changement de solde -->
                            <div class="flex items-center justify-between space-x-4 flex-col sm:flex-row">
                                <!-- Solde avant -->
                                <div class="text-center flex-1 flex-col sm:flex-row">
                                    <div class="bg-gradient-to-r from-purple-100 to-purple-200 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-md hover:shadow-xl">
                                        <p class="text-sm font-medium text-purple-700 mb-2">Solde avant</p>
                                        <p class="text-2xl font-bold text-purple-900">{{ number_format($transaction->wallet_balance_before, 3) }} DT</p>
                                    </div>
                                </div>
                                
                                <!-- Flèche et variation -->
                                <div class="flex flex-col items-center flex-col sm:flex-row">
                                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-full p-3 shadow-md hover:shadow-xl mb-2">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-600">Variation</p>
                                        <p class="font-bold {{ $transaction->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Solde après -->
                                <div class="text-center flex-1 flex-col sm:flex-row">
                                    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-md hover:shadow-xl">
                                        <p class="text-sm font-medium text-purple-800 mb-2">Solde après</p>
                                        <p class="text-2xl font-bold text-purple-900">{{ number_format($transaction->wallet_balance_after, 3) }} DT</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Barre de progression visuelle -->
                            <div class="mt-6 bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-full rounded-full transition-all duration-1000 ease-out"
                                     style="width: {{ min(100, ($transaction->wallet_balance_after / max($transaction->wallet_balance_before, $transaction->wallet_balance_after)) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Informations de colis (si applicable) -->
                @if($transaction->package_id && $transaction->package)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-4 sm:px-5 lg:px-6 py-4 border-b border-purple-200">
                            <h3 class="text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Colis associé
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-5 lg:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-2xl p-4 sm:p-5 lg:p-6 flex-col sm:flex-row">
                                <div class="mb-4 sm:mb-0">
                                    <h4 class="text-xl font-bold text-purple-900 mb-2">{{ $transaction->package->package_code }}</h4>
                                    <p class="text-purple-700 mb-1">
                                        <span class="font-medium">Destinataire:</span>
                                        {{ $transaction->package->recipient_name ?? 'Non spécifié' }}
                                    </p>
                                    <p class="text-purple-700">
                                        <span class="font-medium">Statut du colis:</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm sm:text-xs font-medium bg-purple-100 text-purple-800 transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row shadow-sm font-medium">
                                            {{ $transaction->package->status }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    @if(Route::has('client.packages.show'))
                                        <a href="{{ route('client.packages.show', $transaction->package_id) }}"
                                           class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-2xl hover:from-purple-600 hover:to-purple-800 transition-all duration-300 transform hover:scale-105 shadow-lg transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                                            <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            Voir le colis
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Métadonnées techniques -->
                @if($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-4 sm:px-5 lg:px-6 py-4 border-b border-gray-100">
                            <h3 class="text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                                <svg class="w-6 h-6 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Métadonnées techniques
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-5 lg:p-6">
                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">@foreach($transaction->metadata as $key => $value)
                                        <div class="flex justify-between items-start border-b border-gray-200 pb-2 last:border-b-0 last:pb-0 flex-col sm:flex-row">
                                            <span class="text-sm font-semibold text-gray-700 capitalize min-w-0 mr-4">
                                                {{ str_replace('_', ' ', $key) }}:
                                            </span>
                                            <span class="text-sm text-gray-900 text-right min-w-0">
                                                @if(is_array($value))
                                                    <details class="cursor-pointer">
                                                        <summary class="text-purple-600 hover:text-purple-800">Voir détails</summary>
                                                        <pre class="mt-2 text-sm sm:text-xs bg-white p-2 rounded border overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    </details>
                                                @else
                                                    <span class="font-mono text-sm sm:text-xs bg-white px-2 py-1 rounded border">{{ $value }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Transactions liées -->
                @if($relatedTransactions->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-4 sm:px-5 lg:px-6 py-4 border-b border-purple-200">
                            <h3 class="text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                                <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Transactions liées
                            </h3>
                        </div>
                        
                        <div class="p-4 sm:p-5 lg:p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">@foreach($relatedTransactions as $related)
                                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl hover:from-purple-100 hover:to-purple-200 transition-all duration-300 border border-purple-200 hover:border-purple-300 flex-col sm:flex-row">
                                        <div class="flex items-center flex-col sm:flex-row">
                                            <div class="w-8 h-8 {{ $related->amount >= 0 ? 'bg-purple-100 text-purple-600' : 'bg-purple-200 text-purple-700' }} rounded-full flex items-center justify-center mr-3 shadow-md hover:shadow-xl flex-col sm:flex-row">
                                                <div class="w-3 h-3 rounded-full {{ $related->amount >= 0 ? 'bg-purple-500' : 'bg-purple-700' }}"></div>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ Str::limit($related->description, 40) }}</p>
                                                <p class="text-sm sm:text-xs text-gray-500 flex items-center flex-col sm:flex-row">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $related->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right flex items-center gap-3 flex-col sm:flex-row">
                                            <div>
                                                <p class="font-bold {{ $related->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                                    {{ $related->formatted_amount }}
                                                </p>
                                                <span class="text-sm sm:text-xs px-2 py-1 rounded-full {{ $related->status_color }}">
                                                    {{ $related->status_display }}
                                                </span>
                                            </div>
                                            <a href="{{ route('client.wallet.transaction.show', $related) }}" 
                                               class="bg-purple-100 text-purple-600 px-3 py-2 rounded-2xl hover:bg-purple-200 transition-colors text-sm font-medium">
                                                Voir →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne latérale -->
            <div class="space-y-6">
                <!-- Informations techniques -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-4 sm:px-5 lg:px-6 py-4 border-b border-purple-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                            <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Détails techniques
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-5 lg:p-6 space-y-4">
                        @if($transaction->sequence_number)
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Numéro de séquence</p>
                                <p class="font-mono text-sm bg-gray-50 px-3 py-2 rounded-2xl">{{ $transaction->sequence_number }}</p>
                            </div>
                        @endif

                        @if($transaction->checksum)
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Checksum</p>
                                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-2xl flex-col sm:flex-row">
                                    <span class="font-mono text-sm sm:text-xs text-gray-900">{{ Str::limit($transaction->checksum, 20) }}...</span>
                                    <button onclick="copyToClipboard('{{ $transaction->checksum }}')" 
                                            class="text-purple-600 hover:text-purple-800 transition-colors" 
                                            title="Copier le checksum">
                                        <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($transaction->reference)
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Référence</p>
                                <p class="font-mono text-sm bg-gray-50 px-3 py-2 rounded-2xl">{{ $transaction->reference }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Vérification intégrité</p>
                            @if($transaction->checksum && $transaction->verifyChecksum())
                                <div class="flex items-center text-green-600 bg-green-50 px-3 py-2 rounded-2xl flex-col sm:flex-row">
                                    <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Validé ✓</span>
                                </div>
                            @else
                                <div class="flex items-center text-gray-500 bg-gray-50 px-3 py-2 rounded-2xl flex-col sm:flex-row">
                                    <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm">Non disponible</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 sm:px-5 lg:px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                            <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Actions
                        </h3>
                    </div>
                    
                    <div class="p-4 sm:p-5 lg:p-6 space-y-3">
                        @if($transaction->package_id && Route::has('client.packages.show'))
                            <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                               class="block w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-4 py-3 rounded-2xl hover:from-blue-700 hover:to-indigo-800 transition-all duration-300 text-sm font-medium text-center transform hover:scale-105 shadow-lg">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Voir le colis
                            </a>
                        @endif

                        <a href="{{ route('client.wallet.transactions') }}" 
                           class="block w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-2xl hover:bg-gray-200 transition-all duration-300 text-sm font-medium text-center transform hover:scale-105">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Toutes les transactions
                        </a>

                        <a href="{{ route('client.wallet.index') }}" 
                           class="block w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white px-4 py-3 rounded-2xl hover:from-emerald-600 hover:to-green-700 transition-all duration-300 text-sm font-medium text-center transform hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Retour au portefeuille
                        </a>
                    </div>
                </div>

                <!-- Support et aide -->
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-4 sm:p-5 lg:p-6">
                    <h3 class="text-lg font-semibold text-amber-900 mb-3 flex items-center flex-col sm:flex-row">
                        <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Besoin d'aide ?
                    </h3>
                    <div class="text-sm text-amber-800 space-y-2">
                        <p>• Conservez cet ID de transaction pour toute réclamation</p>
                        <p>• Les transactions complétées ne peuvent être modifiées</p>
                        <p>• Contactez le support en cas de problème</p>
                        <p>• Temps de réponse: 24-48h ouvrables</p>
                    </div>
                    <div class="mt-4">
                        <button onclick="copyTransactionInfo()" 
                                class="w-full bg-amber-200 text-amber-800 px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl hover:bg-amber-300 transition-colors text-sm font-medium">
                            📋 Copier info transaction
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour copier dans le presse-papiers
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('✅ Copié dans le presse-papiers!', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

// Fonction de fallback pour copier
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('✅ Copié dans le presse-papiers!', 'success');
    } catch (err) {
        showToast('❌ Impossible de copier', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Fonction pour copier les informations de la transaction
function copyTransactionInfo() {
    const info = `Transaction #{{ $transaction->transaction_id }}
Montant: {{ $transaction->formatted_amount }}
Type: {{ $transaction->type_display }}
Statut: {{ $transaction->status_display }}
Date: {{ $transaction->created_at->format('d/m/Y à H:i:s') }}
Description: {{ $transaction->description }}`;
    
    copyToClipboard(info);
}

// Fonction pour afficher les toasts
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 sm:px-5 lg:px-6 py-3 rounded-2xl shadow-lg z-50 transform transition-all duration-300`;
    toast.textContent = message;
    
    // Animation d'entrée
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Animation de sortie
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des cartes
    const cards = document.querySelectorAll('.bg-white');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Animation de la barre de progression
    const progressBar = document.querySelector('[style*="width:"]');
    if (progressBar) {
        const originalWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.transition = 'width 2s ease-out';
            progressBar.style.width = originalWidth;
        }, 1000);
    }

    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + C pour copier l'ID de transaction
        if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            copyToClipboard('{{ $transaction->transaction_id }}');
        }
        
        // Escape pour retourner à la liste
        if (e.key === 'Escape') {
            window.location.href = '{{ route("client.wallet.transactions") }}';
        }
        
        // 'P' pour aller au colis (si applicable)
        @if($transaction->package_id && Route::has('client.packages.show'))
            if (e.key === 'p' || e.key === 'P') {
                if (!e.target.matches('input, textarea')) {
                    window.location.href = '{{ route("client.packages.show", $transaction->package_id) }}';
                }
            }
        @endif
    });

    // Effet de hover amélioré pour les boutons
    const buttons = document.querySelectorAll('.transform.hover\\:scale-105');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 10px 25px rgba(139, 92, 246, 0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
        });
    });

    // Animation pour les détails pliables
    const detailsElements = document.querySelectorAll('details');
    detailsElements.forEach(details => {
        details.addEventListener('toggle', function() {
            if (this.open) {
                const summary = this.querySelector('summary');
                summary.style.color = '#7c3aed';
            }
        });
    });
});

// Animation au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Appliquer l'observer aux éléments
document.addEventListener('DOMContentLoaded', function() {
    const elementsToObserve = document.querySelectorAll('.bg-gradient-to-r, .bg-gradient-to-br');
    elementsToObserve.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
});
</script>
@endpush

@push('styles')
<style>
/* Animation personnalisées */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in-up {
    animation: slideInUp 0.6s ease-out;
}

/* Amélioration des gradients */
.bg-gradient-to-br {
    background-attachment: fixed;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .grid-cols-1 sm:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Améliorations des cartes */
.card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Animation des icônes */
.icon-bounce {
    animation: iconBounce 2s ease-in-out infinite;
}

@keyframes iconBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* États des boutons */
.btn-copying {
    animation: btnCopying 0.3s ease-out;
}

@keyframes btnCopying {
    0% { transform: scale(1); }
    50% { transform: scale(0.95); }
    100% { transform: scale(1); }
}

/* Amélioration des tooltips */
[title] {
    position: relative;
}

/* Progress bar améliorée */
.progress-animated {
    background-size: 200% 100%;
    animation: progressShine 2s infinite linear;
}

@keyframes progressShine {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* États de focus améliorés */
.focus-ring:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.3);
}

/* Animation pour les métadonnées */
details[open] summary ~ * {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
@endsection