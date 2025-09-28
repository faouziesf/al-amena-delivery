@extends('layouts.commercial')

@section('title', 'Dashboard Commercial')
@section('page-title', 'Dashboard Commercial')
@section('page-description', 'Vue d\'ensemble de votre activité commerciale')

@section('content')
<div x-data="commercialDashboard()" x-init="init()">

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Chiffre d'Affaires -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-100">CA du Mois</p>
                    <p class="text-3xl font-bold" x-text="formatAmount(stats.revenue_this_month || 0)"></p>
                    <div class="flex items-center mt-2">
                        <div class="flex items-center text-xs">
                            <span :class="stats.revenue_growth_rate >= 0 ? 'text-green-200' : 'text-red-200'">
                                <svg class="w-3 h-3 mr-1" :class="stats.revenue_growth_rate >= 0 ? '' : 'transform rotate-180'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="Math.abs(stats.revenue_growth_rate || 0) + '%'"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- COD Collecté -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-100">COD Aujourd'hui</p>
                    <p class="text-3xl font-bold" x-text="formatAmount(stats.cod_collected_today || 0)"></p>
                    <p class="text-xs text-blue-200 mt-2">
                        Valeur moy. <span x-text="formatAmount(stats.average_order_value || 0)"></span>
                    </p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-30 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Performance Livraisons -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-purple-100">Taux de Réussite</p>
                    <p class="text-3xl font-bold" x-text="(stats.success_rate || 0) + '%'"></p>
                    <p class="text-xs text-purple-200 mt-2">
                        <span x-text="stats.delivered_today || 0"></span> livrés / <span x-text="stats.packages_today || 0"></span> créés
                    </p>
                </div>
                <div class="p-3 bg-purple-400 bg-opacity-30 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Satisfaction Client -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl shadow-sm p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-orange-100">Satisfaction Client</p>
                    <p class="text-3xl font-bold" x-text="(stats.client_satisfaction_score || 0) + '%'"></p>
                    <div class="flex items-center mt-2">
                        <span class="text-xs text-orange-200">
                            <span x-text="stats.pending_complaints || 0"></span> réclamations
                        </span>
                        <span class="mx-2 text-orange-300">•</span>
                        <span class="text-xs text-orange-200">
                            <span x-text="(stats.complaints_resolution_rate || 0) + '%'"></span> résolues
                        </span>
                    </div>
                </div>
                <div class="p-3 bg-orange-400 bg-opacity-30 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.01M15 10h1.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Secondaires -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- Croissance Clients -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Nouveaux Clients</p>
                    <p class="text-xl font-bold text-gray-900" x-text="stats.new_clients_this_month || 0"></p>
                </div>
                <div class="text-right">
                    <span class="text-xs px-2 py-1 rounded-full"
                          :class="(stats.client_growth_rate || 0) >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        <span x-text="(stats.client_growth_rate >= 0 ? '+' : '') + (stats.client_growth_rate || 0) + '%'"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Efficacité Livreurs -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Temps Moy. Livr.</p>
                    <p class="text-xl font-bold text-gray-900" x-text="(stats.average_delivery_time || 0) + 'h'"></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500" x-text="stats.deliveries_per_deliverer || 0"></p>
                    <p class="text-xs text-gray-400">par livreur</p>
                </div>
            </div>
        </div>

        <!-- Colis en Cours -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Transit</p>
                    <p class="text-xl font-bold text-gray-900" x-text="stats.packages_in_progress || 0"></p>
                </div>
                <div class="text-right">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Requises -->
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Actions</p>
                    <p class="text-xl font-bold text-gray-900" x-text="(stats.urgent_complaints || 0) + (stats.pending_withdrawals || 0)"></p>
                </div>
                <div class="text-right">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Nouveau Client -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Nouveau Client</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <p class="text-purple-100 text-sm mb-4">Créer rapidement un nouveau compte client avec tarification personnalisée.</p>
            <button @click="showCreateClientModal = true" class="w-full bg-white text-purple-600 py-2 px-4 rounded-lg font-medium hover:bg-purple-50 transition-colors">
                Créer un Client
            </button>
        </div>

        <!-- Modifier COD -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Modifier COD</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <p class="text-blue-100 text-sm mb-4">Modifier rapidement le montant COD d'un colis.</p>
            <button @click="showCodModal = true" class="w-full bg-white text-blue-600 py-2 px-4 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                Modifier COD
            </button>
        </div>

        <!-- Rapports -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Rapports</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-green-100 text-sm mb-4">Générer des rapports détaillés sur l'activité.</p>
            <button @click="generateReport()" class="w-full bg-white text-green-600 py-2 px-4 rounded-lg font-medium hover:bg-green-50 transition-colors">
                Rapport Quotidien
            </button>
        </div>
    </div>

    <!-- Graphiques de Tendances -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Tendance Colis (7 derniers jours) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tendance Colis (7 jours)</h3>
                <p class="text-sm text-gray-600">Créés vs Livrés par jour</p>
            </div>
            <div class="p-6">
                <div class="h-64 flex items-end space-x-2" x-show="stats.daily_packages_trend && stats.daily_packages_trend.length > 0">
                    <template x-for="day in stats.daily_packages_trend || []" :key="day.date">
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full flex flex-col items-center space-y-1">
                                <!-- Barre Créés -->
                                <div class="bg-blue-200 rounded-t-md w-full transition-all duration-300"
                                     :style="`height: ${Math.max((day.created || 0) * 4, 4)}px`"
                                     :title="`${day.created} créés`">
                                </div>
                                <!-- Barre Livrés -->
                                <div class="bg-green-400 rounded-b-md w-full transition-all duration-300"
                                     :style="`height: ${Math.max((day.delivered || 0) * 4, 4)}px`"
                                     :title="`${day.delivered} livrés`">
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2 text-center">
                                <div x-text="day.day"></div>
                                <div x-text="day.date" class="font-medium"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="!stats.daily_packages_trend || stats.daily_packages_trend.length === 0"
                     class="h-64 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="mt-2">Pas de données disponibles</p>
                    </div>
                </div>
                <!-- Légende -->
                <div class="flex justify-center space-x-6 mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-200 rounded"></div>
                        <span class="text-xs text-gray-600">Colis Créés</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-400 rounded"></div>
                        <span class="text-xs text-gray-600">Colis Livrés</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Livreurs Performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Top Livreurs</h3>
                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        Ce mois
                    </span>
                </div>
            </div>
            <div class="p-6">
                <div x-show="stats.top_performing_deliverers && stats.top_performing_deliverers.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun livreur actif</h3>
                    <p class="mt-1 text-sm text-gray-500">Les performances apparaîtront ici.</p>
                </div>

                <div class="space-y-4" x-show="stats.top_performing_deliverers && stats.top_performing_deliverers.length > 0">
                    <template x-for="(deliverer, index) in stats.top_performing_deliverers || []" :key="deliverer.id">
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                            <!-- Rang -->
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                     :class="index === 0 ? 'bg-yellow-100 text-yellow-800' :
                                            index === 1 ? 'bg-gray-100 text-gray-800' :
                                            index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'">
                                    <span x-text="index + 1"></span>
                                </div>
                            </div>

                            <!-- Info Livreur -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900" x-text="deliverer.name"></p>
                                <div class="flex items-center space-x-3 text-xs text-gray-500 mt-1">
                                    <span><span x-text="deliverer.deliveries_this_month"></span> livr./mois</span>
                                    <span>•</span>
                                    <span><span x-text="deliverer.avg_delivery_time"></span>h moy.</span>
                                    <span>•</span>
                                    <span><span x-text="formatAmount(deliverer.wallet_balance)"></span></span>
                                </div>
                            </div>

                            <!-- Score Performance -->
                            <div class="flex-shrink-0 text-right">
                                <div class="text-lg font-bold"
                                     :class="deliverer.performance_score >= 80 ? 'text-green-600' :
                                            deliverer.performance_score >= 60 ? 'text-yellow-600' : 'text-red-600'">
                                    <span x-text="deliverer.performance_score"></span>%
                                </div>
                                <div class="text-xs text-gray-500">score</div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Réclamations Urgentes & Livreurs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Réclamations Urgentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Réclamations Urgentes</h3>
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full" x-text="urgentComplaints.length"></span>
                </div>
            </div>
            <div class="p-6">
                <div x-show="urgentComplaints.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune réclamation urgente</h3>
                    <p class="mt-1 text-sm text-gray-500">Toutes les réclamations urgentes sont traitées !</p>
                </div>

                <div class="space-y-4" x-show="urgentComplaints.length > 0">
                    <template x-for="complaint in urgentComplaints.slice(0, 5)" :key="complaint.id">
                        <div class="flex items-center space-x-4 p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors cursor-pointer"
                             @click="window.location.href = `/commercial/complaints/${complaint.id}`">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900" x-text="complaint.type_display"></p>
                                <p class="text-sm text-gray-600">
                                    <span x-text="complaint.client_name"></span> -
                                    <span x-text="complaint.package_code"></span>
                                </p>
                                <p class="text-xs text-gray-500" x-text="complaint.created_at_human"></p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Livreurs avec Wallets Élevés -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Wallets à Vider</h3>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full" x-text="highBalanceDeliverers.length"></span>
                </div>
            </div>
            <div class="p-6">
                <div x-show="highBalanceDeliverers.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tous les wallets sont normaux</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucun livreur n'a un solde élevé.</p>
                </div>

                <div class="space-y-4" x-show="highBalanceDeliverers.length > 0">
                    <template x-for="deliverer in highBalanceDeliverers.slice(0, 5)" :key="deliverer.id">
                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-green-600" x-text="deliverer.name.substring(0, 2).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900" x-text="deliverer.name"></p>
                                    <p class="text-sm text-gray-600" x-text="deliverer.phone"></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-lg font-bold text-green-600" x-text="formatAmount(deliverer.wallet_balance)"></span>
                                <button @click="emptyWallet(deliverer.id)"
                                        class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition-colors">
                                    Vider
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité Récente -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Activité Récente</h3>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8" x-show="recentActivity.length > 0">
                    <template x-for="(activity, index) in recentActivity.slice(0, 10)" :key="activity.id">
                        <li>
                            <div class="relative pb-8" x-show="index < Math.min(recentActivity.length - 1, 9)">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                              :class="getActivityColor(activity.type)">
                                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500" x-html="activity.description"></p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time x-text="activity.created_at_human"></time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="relative flex space-x-3" x-show="index === Math.min(recentActivity.length - 1, 9)">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                          :class="getActivityColor(activity.type)">
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500" x-html="activity.description"></p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time x-text="activity.created_at_human"></time>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>

                <div x-show="recentActivity.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune activité récente</h3>
                    <p class="mt-1 text-sm text-gray-500">L'activité apparaîtra ici au fur et à mesure.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modals -->
@include('commercial.partials.create-client-modal')
@include('commercial.partials.cod-modification-modal')

@endsection

@push('scripts')
<script>
function commercialDashboard() {
    return {
        stats: @json($stats ?? []),
        urgentComplaints: [],
        highBalanceDeliverers: [],
        recentActivity: @json($recentActivity ?? []),
        showCreateClientModal: false,
        showCodModal: false,
        loading: false,

        init() {
            this.loadDashboardData();
            // Refresh data every 60 seconds
            setInterval(() => {
                this.loadDashboardData();
            }, 60000);
        },

        async loadDashboardData() {
            try {
                this.loading = true;

                // Load main dashboard stats
                const statsResponse = await fetch('/commercial/api/dashboard-stats');
                if (statsResponse.ok) {
                    this.stats = await statsResponse.json();
                }

                // Load urgent complaints
                const complaintsResponse = await fetch('/commercial/api/complaints/urgent');
                if (complaintsResponse.ok) {
                    this.urgentComplaints = await complaintsResponse.json();
                }

                // Load high balance deliverers
                const deliverersResponse = await fetch('/commercial/api/deliverers/high-balance');
                if (deliverersResponse.ok) {
                    this.highBalanceDeliverers = await deliverersResponse.json();
                }

                // Load recent activity
                const activityResponse = await fetch('/commercial/api/recent-activity');
                if (activityResponse.ok) {
                    this.recentActivity = await activityResponse.json();
                }

            } catch (error) {
                console.error('Erreur chargement données dashboard:', error);
            } finally {
                this.loading = false;
            }
        },

        async emptyWallet(delivererId) {
            if (!confirm('Êtes-vous sûr de vouloir vider ce wallet ?')) {
                return;
            }

            try {
                const response = await fetch(`/commercial/deliverers/${delivererId}/empty-wallet`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    showToast('Wallet vidé avec succès', 'success');
                    this.loadDashboardData();
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors du vidage', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        },

        generateReport() {
            showToast('Génération du rapport en cours...', 'info');
            // TODO: Implement report generation
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        getActivityColor(type) {
            const colors = {
                complaint: 'bg-red-500',
                client: 'bg-green-500',
                package: 'bg-blue-500',
                withdrawal: 'bg-purple-500',
                default: 'bg-gray-500'
            };
            return colors[type] || colors.default;
        }
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : 'bg-red-500';
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform transition-all duration-300 ${bgColor}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}
</script>
@endpush