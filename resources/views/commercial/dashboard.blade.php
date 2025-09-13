@extends('layouts.commercial')

@section('title', 'Dashboard Commercial')
@section('page-title', 'Dashboard Commercial')
@section('page-description', 'Vue d\'ensemble de votre activité commerciale')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="openQuickStats()" 
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Rapport
    </button>
    <button onclick="openCreateClientModal()" 
            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouveau Client
    </button>
</div>
@endsection

@section('content')
<div x-data="dashboardApp()">
    <!-- Stats Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Réclamations Urgentes -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg group-hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Réclamations Urgentes</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="dashboardStats.urgent_complaints || {{ $complaintsStats['urgent'] ?? 0 }}"></p>
                        <p class="text-xs text-red-600 mt-1" x-show="dashboardStats.urgent_complaints > 0">
                            <svg class="w-3 h-3 inline animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Action requise
                        </p>
                    </div>
                </div>
                <button onclick="window.location.href='{{ route('commercial.complaints.index') }}?priority=URGENT'" 
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Retraits en Attente -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg group-hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Retraits en Attente</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="dashboardStats.pending_withdrawals || {{ $stats['pending_withdrawals'] ?? 0 }}"></p>
                        <p class="text-xs text-blue-600 mt-1">
                            <span x-text="(dashboardStats.total_pending_amount || 0).toFixed(3)"></span> DT
                        </p>
                    </div>
                </div>
                <button onclick="window.location.href='{{ route('commercial.withdrawals.index') }}'" 
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Wallets à Vider -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg group-hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Wallets à Vider</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="dashboardStats.high_balance_deliverers || {{ $stats['high_balance_deliverers'] ?? 0 }}"></p>
                        <p class="text-xs text-green-600 mt-1">
                            +100 DT
                        </p>
                    </div>
                </div>
                <button onclick="window.location.href='{{ route('commercial.deliverers.index') }}?high_balance_only=1'" 
                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Colis Aujourd'hui -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg group-hover:shadow-xl transition-shadow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Colis Aujourd'hui</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="dashboardStats.packages_today || {{ $stats['packages_today'] ?? 0 }}"></p>
                        <p class="text-xs text-purple-600 mt-1">
                            +<span x-text="dashboardStats.packages_delivered_today || 0"></span> livrés
                        </p>
                    </div>
                </div>
                <button onclick="window.location.href='{{ route('commercial.packages.index') }}?date_from={{ today()->format('Y-m-d') }}'" 
                        class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Quick Client Creation -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Nouveau Client</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <p class="text-purple-100 text-sm mb-4">Créez rapidement un nouveau compte client avec tarification personnalisée.</p>
            <button onclick="openCreateClientModal()" 
                    class="w-full bg-white text-purple-600 py-2 px-4 rounded-lg font-medium hover:bg-purple-50 transition-colors">
                Créer un Client
            </button>
        </div>

        <!-- Quick COD Modification -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Modifier COD</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <p class="text-blue-100 text-sm mb-4">Modifiez rapidement le montant COD d'un colis pour résoudre les réclamations.</p>
            <button onclick="openCodModificationModal()" 
                    class="w-full bg-white text-blue-600 py-2 px-4 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                Modifier COD
            </button>
        </div>

        <!-- Quick Reports -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Rapports</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-green-100 text-sm mb-4">Générez des rapports détaillés sur l'activité commerciale.</p>
            <button onclick="generateDailyReport()" 
                    class="w-full bg-white text-green-600 py-2 px-4 rounded-lg font-medium hover:bg-green-50 transition-colors">
                Rapport Quotidien
            </button>
        </div>
    </div>

    <!-- Activity Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Complaints -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Réclamations Récentes</h3>
                    <a href="{{ route('commercial.complaints.index') }}" 
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Voir tout
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div x-show="recentComplaints.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune réclamation</h3>
                    <p class="mt-1 text-sm text-gray-500">Toutes les réclamations sont traitées !</p>
                </div>
                
                <div class="space-y-4" x-show="recentComplaints.length > 0">
                    <template x-for="complaint in recentComplaints" :key="complaint.id">
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                                     :class="complaint.priority === 'URGENT' ? 'bg-red-100 text-red-600' : 
                                            complaint.priority === 'HIGH' ? 'bg-orange-100 text-orange-600' : 
                                            'bg-blue-100 text-blue-600'">
                                    !
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900" x-text="complaint.type_display"></p>
                                <p class="text-sm text-gray-600">
                                    <span x-text="complaint.client_name"></span> - 
                                    <span x-text="complaint.package_code"></span>
                                </p>
                                <p class="text-xs text-gray-500" x-text="complaint.created_at_human"></p>
                            </div>
                            <button @click="window.location.href = complaint.show_url" 
                                    class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Traiter
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- High Balance Deliverers -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Livreurs - Wallets Élevés</h3>
                    <a href="{{ route('commercial.deliverers.index') }}?high_balance_only=1" 
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Voir tout
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div x-show="highBalanceDeliverers.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tous les wallets sont vidés</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucun livreur n'a un solde élevé.</p>
                </div>
                
                <div class="space-y-4" x-show="highBalanceDeliverers.length > 0">
                    <template x-for="deliverer in highBalanceDeliverers" :key="deliverer.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-green-600" x-text="deliverer.name.substring(0, 2)"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900" x-text="deliverer.name"></p>
                                    <p class="text-sm text-gray-600" x-text="deliverer.phone"></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-lg font-bold text-green-600" x-text="deliverer.wallet_balance + ' DT'"></span>
                                <button @click="emptyDelivererWallet(deliverer.id)" 
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

    <!-- Recent Activity Timeline -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Activité Récente</h3>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8" x-show="recentActivity.length > 0">
                    <template x-for="(activity, index) in recentActivity" :key="activity.id">
                        <li>
                            <div class="relative pb-8" x-show="index < recentActivity.length - 1">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                              :class="activity.type === 'complaint' ? 'bg-red-400' : 
                                                     activity.type === 'package' ? 'bg-blue-400' : 
                                                     activity.type === 'withdrawal' ? 'bg-green-400' : 'bg-purple-400'">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
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
                            <div class="relative flex space-x-3" x-show="index === recentActivity.length - 1">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                          :class="activity.type === 'complaint' ? 'bg-red-400' : 
                                                 activity.type === 'package' ? 'bg-blue-400' : 
                                                 activity.type === 'withdrawal' ? 'bg-green-400' : 'bg-purple-400'">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
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
@endsection

@push('modals')
<!-- Create Client Modal -->
<div id="create-client-modal" 
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden"
     x-data="createClientModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900">Nouveau Client</h3>
                <button onclick="closeCreateClientModal()" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="createClient">
                <div class="p-6 space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Informations de Base</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                                <input x-model="form.name" type="text" required 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input x-model="form.email" type="email" required 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span class="text-red-500">*</span></label>
                                <input x-model="form.phone" type="text" required 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom Boutique</label>
                                <input x-model="form.shop_name" type="text" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse <span class="text-red-500">*</span></label>
                            <input x-model="form.address" type="text" required 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Authentication -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Authentification</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                                <input x-model="form.password" type="password" required minlength="6" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer <span class="text-red-500">*</span></label>
                                <input x-model="form.password_confirmation" type="password" required minlength="6" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Informations Professionnelles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Matricule Fiscal</label>
                                <input x-model="form.fiscal_number" type="text" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secteur d'Activité</label>
                                <input x-model="form.business_sector" type="text" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Document d'Identité</label>
                            <input x-model="form.identity_document" type="text" 
                                   placeholder="CIN, Passeport, Registre de commerce..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Tarification</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prix Livraison (DT) <span class="text-red-500">*</span></label>
                                <input x-model="form.delivery_price" type="number" step="0.001" required min="0" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">Montant facturé en cas de livraison réussie</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prix Retour (DT) <span class="text-red-500">*</span></label>
                                <input x-model="form.return_price" type="number" step="0.001" required min="0" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">Montant facturé en cas de retour</p>
                            </div>
                        </div>
                    </div>

                    <!-- Error Display -->
                    <div x-show="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Erreurs de validation :</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <template x-for="error in errors" :key="error">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 p-6 border-t bg-gray-50 rounded-b-xl">
                    <button type="submit" :disabled="loading"
                            class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Créer Client</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Création...
                        </span>
                    </button>
                    <button type="button" onclick="closeCreateClientModal()" 
                            class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-400">
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
function dashboardApp() {
    return {
        dashboardStats: {},
        recentComplaints: [],
        highBalanceDeliverers: [],
        recentActivity: [],

        init() {
            this.loadDashboardData();
            
            // Auto-refresh every 60 seconds
            setInterval(() => {
                this.loadDashboardData();
            }, 60000);
        },

        async loadDashboardData() {
            try {
                // Load dashboard stats
                const statsResponse = await fetch('/commercial/api/dashboard-stats');
                if (statsResponse.ok) {
                    this.dashboardStats = await statsResponse.json();
                }

                // Load recent complaints
                const complaintsResponse = await fetch('/commercial/complaints/api/pending');
                if (complaintsResponse.ok) {
                    this.recentComplaints = (await complaintsResponse.json()).slice(0, 5);
                }

                // Load high balance deliverers
                const deliverersResponse = await fetch('/commercial/deliverers/api/high-balance');
                if (deliverersResponse.ok) {
                    this.highBalanceDeliverers = (await deliverersResponse.json()).slice(0, 5);
                }

                // Load recent activity
                const activityResponse = await fetch('/commercial/complaints/api/recent-activity');
                if (activityResponse.ok) {
                    this.recentActivity = (await activityResponse.json()).slice(0, 10);
                }
            } catch (error) {
                console.error('Erreur chargement données dashboard:', error);
            }
        },

        async emptyDelivererWallet(delivererId) {
            if (!confirm('Êtes-vous sûr de vouloir vider ce wallet ?')) {
                return;
            }

            try {
                const response = await fetch(`/commercial/deliverers/${delivererId}/empty-wallet`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
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
        }
    }
}

function createClientModal() {
    return {
        form: {
            name: '',
            email: '',
            phone: '',
            address: '',
            shop_name: '',
            fiscal_number: '',
            business_sector: '',
            identity_document: '',
            delivery_price: '',
            return_price: '',
            password: '',
            password_confirmation: ''
        },
        loading: false,
        errors: [],

        async createClient() {
            this.loading = true;
            this.errors = [];

            // Client-side validation
            if (this.form.password !== this.form.password_confirmation) {
                this.errors.push('Les mots de passe ne correspondent pas');
                this.loading = false;
                return;
            }

            if (this.form.password.length < 6) {
                this.errors.push('Le mot de passe doit contenir au moins 6 caractères');
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('/commercial/clients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (response.ok) {
                    showToast(`Client créé avec succès ! Email: ${this.form.email}`, 'success');
                    closeCreateClientModal();
                    this.resetForm();
                    
                    // Trigger refresh of dashboard data
                    document.querySelector('[x-data*="dashboardApp"]').__x.$data.loadDashboardData();
                } else {
                    if (result.errors) {
                        this.errors = Object.values(result.errors).flat();
                    } else {
                        this.errors = [result.message || 'Erreur lors de la création du client'];
                    }
                }
            } catch (error) {
                this.errors = ['Erreur de connexion. Veuillez réessayer.'];
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.form = {
                name: '',
                email: '',
                phone: '',
                address: '',
                shop_name: '',
                fiscal_number: '',
                business_sector: '',
                identity_document: '',
                delivery_price: '',
                return_price: '',
                password: '',
                password_confirmation: ''
            };
            this.errors = [];
        }
    }
}

// Global functions
function openCreateClientModal() {
    document.getElementById('create-client-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateClientModal() {
    document.getElementById('create-client-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openCodModificationModal() {
    // TODO: Implement COD modification modal
    showToast('Fonctionnalité de modification COD à implémenter', 'info');
}

function openQuickStats() {
    // TODO: Implement quick stats modal
    showToast('Fonctionnalité de rapport rapide à implémenter', 'info');
}

function generateDailyReport() {
    // TODO: Implement daily report generation
    showToast('Génération du rapport quotidien...', 'info');
}
</script>
@endpush