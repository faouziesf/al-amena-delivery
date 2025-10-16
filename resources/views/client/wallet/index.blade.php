@extends('layouts.client')

@section('title', 'Mon Portefeuille')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6 px-4 py-4 lg:px-6 lg:py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col space-y-3 lg:flex-row lg:justify-between lg:items-start lg:space-y-0 mb-4 sm:mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                    💰 Mon Portefeuille
                </h1>
                <p class="text-gray-600 text-sm">Gérez votre solde et vos transactions</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('client/wallet/topup/requests') }}" 
                   class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:from-emerald-600 hover:to-teal-700 transition-all shadow-md flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="hidden sm:inline">Demande de</span> Recharge
                </a>
                <a href="{{ route('client.wallet.withdrawal') }}" 
                   class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all shadow-md flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Retirer
                </a>
                <a href="{{ route('client.wallet.statement') }}" 
                   class="bg-white text-purple-600 border border-purple-200 px-3 sm:px-4 py-2 rounded-lg hover:bg-purple-50 transition-all shadow-sm flex items-center gap-1.5 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a4 4 0 01-4 4z"/>
                    </svg>
                    <span class="hidden sm:inline">Relevé</span> PDF
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 text-emerald-800 px-3 sm:px-4 py-2.5 rounded-lg mb-3 sm:mb-4 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-800 px-3 sm:px-4 py-2.5 rounded-lg mb-3 sm:mb-4 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm lg:text-base">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Balance Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <!-- Solde disponible -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-xl p-3 sm:p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-xs font-medium">Disponible</p>
                        <p class="text-lg sm:text-xl font-bold mt-1" id="available-balance">
                            {{ number_format($user->wallet->balance - ($user->wallet->frozen_amount ?? 0), 3) }} DT
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Montant en attente -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-xl p-3 sm:p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-xs font-medium">Attente</p>
                        <p class="text-lg sm:text-xl font-bold mt-1">
                            {{ number_format($user->wallet->pending_amount ?? 0, 3) }} DT
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Montant gelé -->
            <div class="bg-gradient-to-br from-red-500 to-pink-600 text-white rounded-xl p-3 sm:p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-xs font-medium">Gelé</p>
                        <p class="text-lg sm:text-xl font-bold mt-1">
                            {{ number_format($user->wallet->frozen_amount ?? 0, 3) }} DT
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Solde total -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl p-3 sm:p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-medium">Total</p>
                        <p class="text-lg sm:text-xl font-bold mt-1">
                            {{ number_format($user->wallet->balance, 3) }} DT
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-purple-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-800 mb-1">💳 Crédité</h3>
                        <p class="text-lg sm:text-xl font-bold text-emerald-600">
                            {{ number_format($stats['total_credited'], 3) }} DT
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">Revenus</p>
                    </div>
                    <div class="bg-emerald-100 rounded-full p-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-3 sm:p-4 shadow-sm border border-purple-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-800 mb-1">💸 Débité</h3>
                        <p class="text-lg sm:text-xl font-bold text-red-600">
                            {{ number_format($stats['total_debited'], 3) }} DT
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">Frais</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 lg:p-6 shadow-lg border border-purple-100 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-2">📊 Solde net</h3>
                        <p class="text-xl lg:text-3xl font-bold {{ ($stats['total_credited'] - $stats['total_debited']) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($stats['total_credited'] - $stats['total_debited'], 3) }} DT
                        </p>
                        <p class="text-xs lg:text-sm text-gray-500 mt-1">Résultat global</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3 lg:p-4">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-4 lg:p-6 mb-6 lg:mb-8">
            <h3 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 lg:mb-6 flex items-center">
                <span class="mr-2 lg:mr-3">⚡</span> Actions rapides
            </h3>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <!-- Nouveau retrait -->
                <a href="{{ route('client.wallet.withdrawal') }}" 
                   class="group flex flex-col items-center p-4 lg:p-6 bg-gradient-to-br from-purple-50 to-indigo-50 border-2 border-purple-200 rounded-xl hover:from-purple-100 hover:to-indigo-100 hover:border-purple-300 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full p-3 lg:p-4 mb-2 lg:mb-4 group-hover:shadow-lg transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1 lg:mb-2 text-sm lg:text-base text-center">Demander un retrait</h4>
                    <p class="text-xs lg:text-sm text-gray-600 text-center">Virement ou espèces</p>
                </a>

                <!-- Mes demandes -->
                <a href="{{ route('client.withdrawals') }}" 
                   class="group flex flex-col items-center p-4 lg:p-6 bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-200 rounded-xl hover:from-emerald-100 hover:to-teal-100 hover:border-emerald-300 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full p-3 lg:p-4 mb-2 lg:mb-4 group-hover:shadow-lg transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1 lg:mb-2 text-sm lg:text-base text-center">Mes demandes</h4>
                    <p class="text-xs lg:text-sm text-gray-600 text-center">Suivi des retraits</p>
                </a>

                <!-- Demande de recharge -->
                <a href="{{ url('client/wallet/topup/requests') }}" 
                   class="group flex flex-col items-center p-4 lg:p-6 bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl hover:from-amber-100 hover:to-orange-100 hover:border-amber-300 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-full p-3 lg:p-4 mb-2 lg:mb-4 group-hover:shadow-lg transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1 lg:mb-2 text-sm lg:text-base text-center">Demande de recharge</h4>
                    <p class="text-xs lg:text-sm text-gray-600 text-center">Ajouter des fonds</p>
                </a>

                <!-- Relevé PDF -->
                <a href="{{ route('client.wallet.statement') }}" 
                   class="group flex flex-col items-center p-4 lg:p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl hover:from-blue-100 hover:to-indigo-100 hover:border-blue-300 transition-all duration-300 transform hover:scale-105">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full p-3 lg:p-4 mb-2 lg:mb-4 group-hover:shadow-lg transition-all duration-300">
                        <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a4 4 0 01-4 4z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1 lg:mb-2 text-sm lg:text-base text-center">Télécharger relevé</h4>
                    <p class="text-xs lg:text-sm text-gray-600 text-center">Export PDF</p>
                </a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
            <div class="px-4 lg:px-6 py-4 lg:py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800 flex items-center">
                        <span class="mr-2 lg:mr-3">📋</span> Transactions récentes
                    </h3>
                    <div class="flex flex-wrap gap-2 lg:gap-3">
                        <a href="{{ route('client.wallet.transactions') }}" 
                           class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-3 lg:px-4 py-2 rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 text-sm font-medium">
                            Voir tout →
                        </a>
                        <a href="{{ route('client.wallet.export') }}" 
                           class="bg-white text-purple-600 border border-purple-200 px-3 lg:px-4 py-2 rounded-lg hover:bg-purple-50 transition-all duration-300 transform hover:scale-105 text-sm font-medium">
                            📊 Exporter
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="divide-y divide-gray-100">
                @forelse($transactions->take(10) as $transaction)
                    <div class="p-4 lg:p-6 hover:bg-gradient-to-r hover:from-purple-25 hover:to-indigo-25 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1">
                                <!-- Transaction Icon -->
                                <div class="flex-shrink-0 mr-3 lg:mr-4">
                                    @if($transaction->amount > 0)
                                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-emerald-100 to-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-red-100 to-pink-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Transaction Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center flex-wrap gap-2 mb-1 lg:mb-2">
                                        <h4 class="font-semibold text-gray-900 text-sm lg:text-base truncate">{{ $transaction->description }}</h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $transaction->status_color }}">
                                            {{ $transaction->status_display }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-2 lg:gap-4 text-xs lg:text-sm text-gray-500">
                                        <span class="flex items-center">
                                            <svg class="w-3 h-3 lg:w-4 lg:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        
                                        @if($transaction->package_id)
                                            <span class="hidden lg:flex items-center">
                                                <svg class="w-3 h-3 lg:w-4 lg:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                            </span>
                                        @endif
                                        
                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded hidden lg:inline">
                                            ID: {{ $transaction->transaction_id }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Amount and Action -->
                            <div class="flex items-center gap-2 lg:gap-4 ml-2">
                                <div class="text-right">
                                    <p class="text-base lg:text-lg font-bold {{ $transaction->amount > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $transaction->formatted_amount }}
                                    </p>
                                    <p class="text-xs lg:text-sm text-gray-500">
                                        {{ $transaction->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <a href="{{ route('client.wallet.transaction.show', $transaction) }}" 
                                   class="bg-purple-100 text-purple-600 px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg hover:bg-purple-200 transition-colors text-xs lg:text-sm font-medium">
                                    →
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 lg:p-12 text-center">
                        <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-r from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-2">Aucune transaction</h3>
                        <p class="text-gray-600 mb-4 lg:mb-6 text-sm lg:text-base">Vous n'avez pas encore de transactions dans votre portefeuille.</p>
                        <a href="{{ url('client/wallet/topup/requests') }}" 
                           class="inline-flex items-center px-4 lg:px-6 py-2.5 lg:py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 text-sm lg:text-base">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Première recharge
                        </a>
                    </div>
                @endforelse
            </div>

            @if($transactions->count() > 10)
                <div class="px-4 lg:px-6 py-3 lg:py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-gray-200">
                    <div class="flex justify-center">
                        <a href="{{ route('client.wallet.transactions') }}" 
                           class="bg-white text-purple-600 px-4 lg:px-6 py-2.5 lg:py-3 rounded-xl hover:bg-purple-50 transition-all duration-300 transform hover:scale-105 shadow-md font-medium text-sm lg:text-base">
                            Voir les {{ $transactions->count() - 10 }} autres transactions →
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
    // Animation d'entrée des cartes
    const cards = document.querySelectorAll('[class*="transform hover:scale-105"]');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Actualisation automatique du solde toutes les 30 secondes
    setInterval(function() {
        fetch('/client/api/wallet/balance')
            .then(response => response.json())
            .then(data => {
                if (data.available !== undefined) {
                    const balanceElement = document.getElementById('available-balance');
                    if (balanceElement) {
                        const currentBalance = parseFloat(balanceElement.textContent.replace(/[^\d.,]/g, '').replace(',', '.'));
                        const newBalance = data.available;
                        
                        if (currentBalance !== newBalance) {
                            balanceElement.style.transition = 'all 0.3s ease';
                            balanceElement.style.transform = 'scale(1.1)';
                            balanceElement.textContent = new Intl.NumberFormat('fr-TN', {
                                minimumFractionDigits: 3,
                                maximumFractionDigits: 3
                            }).format(newBalance) + ' DT';
                            
                            setTimeout(() => {
                                balanceElement.style.transform = 'scale(1)';
                            }, 300);
                        }
                    }
                }
            })
            .catch(error => console.error('Erreur actualisation solde:', error));
    }, 30000);

    // Smooth scroll pour les liens internes
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Effet de survol amélioré pour les boutons
    const buttons = document.querySelectorAll('.transform.hover\\:scale-105');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 10px 25px rgba(139, 92, 246, 0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
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
    const elementsToObserve = document.querySelectorAll('.bg-white, .bg-gradient-to-br');
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
.purple-25 {
    background-color: rgba(139, 92, 246, 0.025);
}

.hover\:from-purple-25:hover {
    background-image: linear-gradient(to right, rgba(139, 92, 246, 0.025), rgba(99, 102, 241, 0.025));
}

/* Animations personnalisées */
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
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    /* Mobile optimizations */
    .grid-cols-2 {
        gap: 0.75rem;
    }
    
    .text-xl {
        font-size: 1.125rem;
    }
}

@media (max-width: 480px) {
    .grid-cols-2 > * {
        min-width: 0;
    }
    
    .truncate {
        max-width: 120px;
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

/* Touch improvements */
@media (hover: none) and (pointer: coarse) {
    .hover\:scale-105:hover {
        transform: none;
    }
    
    .hover\:shadow-xl:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
}
</style>
@endpush
@endsection