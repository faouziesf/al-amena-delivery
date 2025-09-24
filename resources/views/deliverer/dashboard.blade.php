{{-- resources/views/deliverer/dashboard.blade.php --}}
@extends('layouts.deliverer')

@section('title', 'Dashboard Livreur')

@section('content')
<div x-data="dashboardApp()" x-init="init()">
    <!-- Header de bienvenue modernisé avec pale purple -->
    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-2xl mx-4 mb-6 p-6 text-purple-800 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Bonjour, {{ auth()->user()->name }} !</h1>
                <p class="text-purple-600 mt-1">
                    <span x-text="getGreetingMessage()"></span>
                </p>
                <div class="flex items-center mt-2 space-x-3">
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 rounded-full animate-pulse" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
                        <span class="text-purple-700 text-sm" x-text="isOnline ? 'En ligne' : 'Hors ligne'"></span>
                    </div>
                    <span class="text-purple-600 text-sm">•</span>
                    <span class="text-purple-600 text-sm" x-text="currentTime"></span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold" x-text="formatAmount(walletData.balance)"></div>
                <p class="text-purple-600 text-sm">Solde Wallet</p>
                <div x-show="walletData.pending_amount > 0" class="text-purple-700 text-xs mt-1">
                    + <span x-text="formatAmount(walletData.pending_amount)"></span> en attente
                </div>
            </div>
        </div>
    </div>

    <!-- Stats rapides modernisées avec animations -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mx-4 mb-6">
        <!-- Pickups Disponibles -->
        <a href="{{ route('deliverer.packages.available') }}" class="stats-card group">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Disponibles</p>
                        <p class="text-2xl font-bold text-blue-600 transition-all duration-300" x-text="stats.available_pickups || 0"></p>
                        <p class="text-xs text-blue-500 mt-1">Nouveaux pickups</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div x-show="stats.available_pickups > 0" class="mt-2 w-full bg-blue-200 rounded-full h-1">
                    <div class="bg-blue-600 h-1 rounded-full animate-pulse" :style="`width: ${Math.min(stats.available_pickups * 10, 100)}%`"></div>
                </div>
            </div>
        </a>

        <!-- Mes Pickups -->
        <a href="{{ route('deliverer.packages.my-pickups') }}" class="stats-card group">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Mes Pickups</p>
                        <p class="text-2xl font-bold text-purple-600 transition-all duration-300" x-text="stats.my_pickups || 0"></p>
                        <p class="text-xs text-purple-500 mt-1">À collecter</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
                <div x-show="stats.my_pickups > 0" class="mt-2 w-full bg-purple-200 rounded-full h-1">
                    <div class="bg-purple-600 h-1 rounded-full animate-pulse" :style="`width: ${Math.min(stats.my_pickups * 20, 100)}%`"></div>
                </div>
            </div>
        </a>

        <!-- Livraisons -->
        <a href="{{ route('deliverer.deliveries.index') }}" class="stats-card group">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group-hover:scale-105 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Livraisons</p>
                        <p class="text-2xl font-bold text-orange-600 transition-all duration-300" x-text="stats.deliveries || 0"></p>
                        <p class="text-xs text-orange-500 mt-1">En cours</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                </div>
                <!-- Badge urgent -->
                <div x-show="stats.urgent_deliveries > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                    <span x-text="stats.urgent_deliveries"></span>
                </div>
                <div x-show="stats.deliveries > 0" class="mt-2 w-full bg-orange-200 rounded-full h-1">
                    <div class="bg-orange-600 h-1 rounded-full" :style="`width: ${Math.min(stats.deliveries * 15, 100)}%`"></div>
                </div>
            </div>
        </a>

        <!-- Retours -->
        <a href="{{ route('deliverer.returns.index') }}" class="stats-card group">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group-hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Retours</p>
                        <p class="text-2xl font-bold text-red-600 transition-all duration-300" x-text="stats.returns || 0"></p>
                        <p class="text-xs text-red-500 mt-1">À traiter</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                </div>
                <div x-show="stats.returns > 0" class="mt-2 w-full bg-red-200 rounded-full h-1">
                    <div class="bg-red-600 h-1 rounded-full" :style="`width: ${Math.min(stats.returns * 25, 100)}%`"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Scanner Central "Hero Section" -->
    <div class="mx-4 mb-8">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Scanner QR/Code-barres</h2>
            <p class="text-gray-600 text-sm">Choisissez votre mode de scan</p>
        </div>

        <!-- Bouton Scanner Central avec animation -->
        <div class="flex justify-center mb-6">
            <a href="{{ route('deliverer.scan.simple') }}"
               class="group relative">
                <!-- Bouton principal rond avec pale purple -->
                <div class="w-32 h-32 bg-gradient-to-br from-purple-200 via-purple-300 to-purple-400
                           rounded-full shadow-2xl hover:shadow-purple-300/25
                           flex items-center justify-center
                           transition-all duration-300 ease-out
                           group-hover:scale-110 group-hover:rotate-3
                           border-4 border-white">

                    <!-- Icône Scanner -->
                    <svg class="w-12 h-12 text-purple-800 transform transition-transform group-hover:scale-110"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>

                    <!-- Animation Ring -->
                    <div class="absolute inset-0 rounded-full border-4 border-purple-200 animate-ping opacity-20"></div>
                    <div class="absolute inset-0 rounded-full border-2 border-purple-300 animate-pulse opacity-30"></div>
                </div>

                <!-- Label -->
                <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-center">
                    <p class="text-purple-700 font-bold text-lg">Scanner</p>
                    <p class="text-purple-500 text-xs">Scan Simple</p>
                </div>
            </a>
        </div>

        <!-- Options secondaires modernisées -->
        <div class="grid grid-cols-3 gap-3 mt-12">
            <!-- Scan en Lot -->
            <a href="{{ route('deliverer.scan.lot') }}"
               class="group bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg hover:border-indigo-200 transition-all duration-300">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 mx-auto group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 text-center">Scan Lot</p>
                <p class="text-xs text-gray-500 text-center mt-1">Multiple</p>
            </a>

            <!-- Wallet -->
            <a href="{{ route('deliverer.wallet.index') }}"
               class="group bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-3 mx-auto group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 text-center">Wallet</p>
                <p class="text-xs text-emerald-600 text-center mt-1 font-medium" x-text="formatAmountShort(walletData.balance)"></p>
            </a>

            <!-- Livraison Rapide -->
            <a href="{{ route('deliverer.deliveries.single') }}"
               class="group bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl p-4 shadow-sm hover:shadow-lg hover:from-red-600 hover:to-pink-600 transition-all duration-300 relative">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-3 mx-auto group-hover:bg-white/30 transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white text-center">⚡ Livraison Rapide</p>
                <p class="text-xs text-white/80 text-center mt-1 font-medium">Colis par colis</p>
                <!-- Badge urgent -->
                <div x-show="stats.urgent_deliveries > 0"
                     class="absolute -top-1 -right-1 w-5 h-5 bg-yellow-400 rounded-full flex items-center justify-center">
                    <span class="text-xs text-red-600 font-bold" x-text="stats.urgent_deliveries"></span>
                </div>
            </a>

            <!-- Mes Livraisons -->
            <a href="{{ route('deliverer.deliveries.index') }}"
               class="group bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg hover:border-orange-200 transition-all duration-300 relative">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3 mx-auto group-hover:bg-orange-200 transition-colors">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 text-center">Mes Livraisons</p>
                <p class="text-xs text-orange-600 text-center mt-1 font-medium" x-text="(stats.deliveries || 0) + ' colis'"></p>
            </a>
        </div>
    </div>

    <!-- Performance aujourd'hui avec graphique modernisé -->
    <div class="mx-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                </svg>
                Performance Aujourd'hui
            </h2>
            
            <div class="grid grid-cols-3 gap-6 text-center mb-6">
                <div class="stat-item">
                    <div class="text-3xl font-bold text-green-600 mb-1" x-text="stats.deliveries_today || 0"></div>
                    <p class="text-sm text-gray-600">Colis Livrés</p>
                    <div class="w-full bg-green-200 rounded-full h-2 mt-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                             :style="`width: ${Math.min((stats.deliveries_today || 0) * 10, 100)}%`"></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="text-3xl font-bold text-emerald-600 mb-1" x-text="formatAmountShort(stats.cod_collected_today || 0)"></div>
                    <p class="text-sm text-gray-600">COD Collecté</p>
                    <div class="w-full bg-emerald-200 rounded-full h-2 mt-2">
                        <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" 
                             :style="`width: ${Math.min((stats.cod_collected_today || 0) / 10, 100)}%`"></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="text-3xl font-bold text-blue-600 mb-1" x-text="(stats.success_rate || 100) + '%'"></div>
                    <p class="text-sm text-gray-600">Taux Succès</p>
                    <div class="w-full bg-blue-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                             :style="`width: ${stats.success_rate || 100}%`"></div>
                    </div>
                </div>
            </div>

            <!-- Mini graphique des livraisons de la semaine -->
            <div class="border-t pt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Livraisons - 7 derniers jours</h3>
                <div class="flex items-end space-x-2 h-16">
                    <template x-for="(day, index) in chartData.deliveries_week || []" :key="index">
                        <div class="flex-1 flex flex-col items-center">
                            <div class="bg-blue-500 rounded-t transition-all duration-500 hover:bg-blue-600 w-full min-h-1" 
                                 :style="`height: ${Math.max((day.count || 0) * 8, 4)}px`"
                                 :title="`${day.date}: ${day.count} livraisons`"></div>
                            <span class="text-xs text-gray-500 mt-1" x-text="day.date"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet détaillé avec pale purple -->
    <div class="mx-4 mb-6" x-show="walletData.balance > 0 || (walletData.recent_transactions && walletData.recent_transactions.length > 0)">
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl border border-purple-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Mon Portefeuille
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Soldes -->
                <div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-medium text-gray-900">Soldes</h3>
                            <span class="text-sm text-gray-500" x-text="walletData.last_transaction_at || 'Aucune transaction'"></span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Disponible</span>
                                <span class="font-semibold text-green-600" x-text="formatAmount(walletData.available_balance || walletData.balance)"></span>
                            </div>
                            
                            <div x-show="walletData.pending_amount > 0" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">En attente</span>
                                <span class="font-semibold text-orange-600" x-text="formatAmount(walletData.pending_amount)"></span>
                            </div>
                            
                            <div x-show="walletData.frozen_amount > 0" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Gelé</span>
                                <span class="font-semibold text-red-600" x-text="formatAmount(walletData.frozen_amount)"></span>
                            </div>
                            
                            <div class="border-t pt-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">Total</span>
                                    <span class="font-bold text-xl text-purple-600" x-text="formatAmount(walletData.balance)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions récentes -->
                <div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-medium text-gray-900 mb-4">Transactions Récentes</h3>
                        
                        <div class="space-y-3 max-h-48 overflow-y-auto">
                            <template x-for="transaction in (walletData.recent_transactions || [])" :key="transaction.id">
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900" x-text="transaction.description"></p>
                                        <p class="text-xs text-gray-500" x-text="transaction.created_at"></p>
                                        <span x-show="transaction.package_code" 
                                              class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full" 
                                              x-text="transaction.package_code"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold" 
                                              :class="transaction.amount >= 0 ? 'text-green-600' : 'text-red-600'" 
                                              x-text="transaction.formatted_amount"></span>
                                        <p class="text-xs" 
                                           :class="transaction.status === 'COMPLETED' ? 'text-green-500' : 'text-orange-500'" 
                                           x-text="transaction.status"></p>
                                    </div>
                                </div>
                            </template>
                            
                            <div x-show="!walletData.recent_transactions || walletData.recent_transactions.length === 0" 
                                 class="text-center py-4 text-gray-500 text-sm">
                                Aucune transaction récente
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-3 border-t">
                            <a href="{{ route('deliverer.wallet.index') }}" 
                               class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                                Voir tout l'historique →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente modernisée -->
    <div class="mx-4 mb-6" x-show="recentActivity.length > 0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Activité Récente
            </h2>
            
            <div class="flow-root">
                <ul class="-mb-8">
                    <template x-for="(activity, index) in recentActivity.slice(0, 6)" :key="index">
                        <li>
                            <div class="relative pb-8" x-show="index < recentActivity.length - 1">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                            </div>
                            
                            <div class="relative flex space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white"
                                     :class="getActivityBgColor(activity.type)">
                                    <span class="text-sm" x-text="activity.icon"></span>
                                </div>
                                
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                                        <p class="text-sm text-gray-600" x-text="activity.description"></p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        <span x-text="activity.time"></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <!-- Notifications importantes modernisées -->
    <div class="mx-4 mb-6" x-show="notifications.length > 0">
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 3v18m0-18l-9 9h5v5l4-4"/>
                </svg>
                Notifications Importantes
            </h3>
            
            <div class="space-y-3">
                <template x-for="notification in notifications.slice(0, 4)" :key="notification.id">
                    <div class="bg-white rounded-lg p-4 shadow-sm border-l-4" 
                         :class="getNotificationBorderColor(notification.priority)">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="font-medium text-gray-900" x-text="notification.title"></h4>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                          :class="notification.priority_color"
                                          x-text="notification.priority"></span>
                                </div>
                                <p class="text-sm text-gray-600" x-text="notification.message"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="notification.created_at"></p>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                <button x-show="notification.action_url" 
                                        @click="window.open(notification.action_url, '_blank')"
                                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Voir
                                </button>
                                <button @click="markNotificationAsRead(notification.id)"
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('deliverer.notifications.index') }}" 
                   class="text-yellow-700 hover:text-yellow-800 font-medium text-sm">
                    Voir toutes les notifications →
                </a>
            </div>
        </div>
    </div>

    <!-- Menu Fonctionnalités rapides modernisé -->
    <div class="mx-4 mb-20">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Autres Fonctionnalités
        </h2>
        <div class="grid grid-cols-2 gap-4">
            <!-- Paiements Clients -->
            <a href="{{ route('deliverer.payments.index') }}" 
               class="bg-white border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-all hover:shadow-md">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Paiements</p>
                        <p class="text-xs text-gray-500">Livraisons espèces</p>
                    </div>
                </div>
            </a>

            <!-- Recharge Client -->
            <a href="{{ route('deliverer.client-topup.index') }}" 
               class="bg-white border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-all hover:shadow-md">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.467-.22-2.121-.659-1.172-.879-1.172-2.303 0-3.182s3.07-.879 4.242 0L15 9m-3 9v1"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Recharge</p>
                        <p class="text-xs text-gray-500">Ajouter fonds</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script defer>
function dashboardApp() {
    return {
        // État principal
        stats: {
            available_pickups: {{ $stats['available_pickups'] ?? 0 }},
            my_pickups: {{ $stats['my_pickups'] ?? 0 }},
            deliveries: {{ $stats['deliveries'] ?? 0 }},
            returns: {{ $stats['returns'] ?? 0 }},
            payments: {{ $stats['payments'] ?? 0 }},
            deliveries_today: {{ $stats['deliveries_today'] ?? 0 }},
            cod_collected_today: {{ $stats['cod_collected_today'] ?? 0 }},
            urgent_deliveries: {{ $stats['urgent_deliveries'] ?? 0 }},
            week_delivered: {{ $stats['week_delivered'] ?? 0 }},
            month_earnings: {{ $stats['month_earnings'] ?? 0 }},
            success_rate: {{ $stats['success_rate'] ?? 100 }}
        },
        walletData: @json($walletData ?? []),
        recentActivity: @json($recentActivity ?? []),
        notifications: @json($notifications ?? []),
        chartData: @json($chartData ?? []),
        
        // État interface
        isOnline: navigator.onLine,
        currentTime: '',
        
        init() {
            console.log('Dashboard App initialisé');
            this.updateTime();
            this.loadStats();
            
            // Auto-refresh toutes les 30 secondes
            setInterval(() => {
                this.loadStats();
                this.updateTime();
            }, 30000);
            
            // Mise à jour de l'heure chaque seconde
            setInterval(() => {
                this.updateTime();
            }, 1000);
            
            // Écouter les changements de connexion
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.loadStats();
            });
            
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });
        },

        async loadStats() {
            if (!this.isOnline) return;
            
            try {
                const response = await fetch('/deliverer/api/dashboard-stats');
                if (response.ok) {
                    const data = await response.json();
                    this.stats = { ...this.stats, ...data };
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
            
            // Charger aussi le wallet
            try {
                const walletResponse = await fetch('/deliverer/api/wallet/balance');
                if (walletResponse.ok) {
                    const walletData = await walletResponse.json();
                    this.walletData = { ...this.walletData, ...walletData };
                }
            } catch (error) {
                console.error('Erreur chargement wallet:', error);
            }
        },

        async markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`/deliverer/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.notifications = this.notifications.filter(n => n.id !== notificationId);
                }
            } catch (error) {
                console.error('Erreur marquage notification:', error);
            }
        },

        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit'
            });
        },

        getGreetingMessage() {
            const hour = new Date().getHours();
            if (hour < 12) return 'Bonne matinée ! Prêt pour vos livraisons ?';
            if (hour < 18) return 'Bon après-midi ! Comment se passent vos livraisons ?';
            return 'Bonne soirée ! Finissons en beauté !';
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatAmountShort(amount) {
            const num = parseFloat(amount || 0);
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'k DT';
            }
            return num.toFixed(0) + ' DT';
        },

        getActivityBgColor(type) {
            switch (type) {
                case 'delivery': return 'bg-green-500';
                case 'pickup': return 'bg-blue-500';
                case 'transaction': return 'bg-emerald-500';
                default: return 'bg-gray-500';
            }
        },

        getNotificationBorderColor(priority) {
            switch (priority) {
                case 'URGENT': return 'border-red-500';
                case 'HIGH': return 'border-orange-500';
                case 'NORMAL': return 'border-blue-500';
                default: return 'border-gray-500';
            }
        },

        openScanner() {
            // Test direct en modifiant une variable visible
            if (window.scannerComponent) {
                window.scannerComponent.openScanner({ mode: 'single' });
            } else {
                window.dispatchEvent(new CustomEvent('open-scanner', {
                    detail: { mode: 'single' }
                }));
            }
        },

        openBatchScanner(action) {
            // Redirection vers les différentes pages avec mode scanner activé
            const routes = {
                'pickup': '/deliverer/packages/batch-pickup',
                'delivery': '/deliverer/packages/deliveries?scanner=true',
                'return': '/deliverer/packages/returns?scanner=true',
                'validate': '/deliverer/payments?scanner=true'
            };

            if (routes[action]) {
                window.location.href = routes[action];
            }
        }
    }
}
</script>

<style>
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stat-item {
    position: relative;
    overflow: hidden;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
}

.stat-item:hover::before {
    left: 100%;
}

/* Animations personnalisées */
@keyframes pulse-soft {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.animate-pulse-soft {
    animation: pulse-soft 2s infinite;
}

/* Scrollbar personnalisée */
.max-h-48::-webkit-scrollbar {
    width: 4px;
}

.max-h-48::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.max-h-48::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.max-h-48::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
// Scanner Simple - Fonction pour ouvrir le popup de scan
function openSimpleScanner() {
    // Supprimer ancien popup s'il existe
    const oldPopup = document.getElementById('simple-scanner-popup');
    if (oldPopup) oldPopup.remove();

    // Créer le popup
    const popup = document.createElement('div');
    popup.id = 'simple-scanner-popup';
    popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';

    popup.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold text-center mb-4">🔍 Scanner un Colis</h3>

            <form class="scan-form space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Code du colis
                    </label>
                    <input type="text" name="code"
                           placeholder="PKG_CLQVFCWP_20250921"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           autofocus>
                </div>

                <button type="submit"
                        class="scan-loading w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Scanner
                </button>
            </form>

            <div class="mt-4 space-y-2">
                <button onclick="simpleScan('PKG_CLQVFCWP_20250921')"
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 text-sm">
                    Test Code Valide
                </button>
                <button onclick="simpleScan('CODE_INVALIDE')"
                        class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 text-sm">
                    Test Code Invalide
                </button>
            </div>

            <button onclick="closeSimpleScanner()"
                    class="mt-4 w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Fermer
            </button>
        </div>
    `;

    document.body.appendChild(popup);

    // Fermer au clic sur le fond
    popup.addEventListener('click', (e) => {
        if (e.target === popup) closeSimpleScanner();
    });

    // Fermer avec Escape
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeSimpleScanner();
            document.removeEventListener('keydown', escapeHandler);
        }
    });
}

// Fermer le scanner
function closeSimpleScanner() {
    const popup = document.getElementById('simple-scanner-popup');
    if (popup) {
        popup.remove();
    }
}

console.log('✅ Scanner Simple Dashboard chargé');
</script>

@endsection