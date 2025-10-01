@extends('layouts.commercial')

@section('title', 'Tableau de Bord des Paiements')
@section('page-title', 'Gestion des Paiements')
@section('page-description', 'Interface simplifiée pour traiter les demandes de paiement')

@section('content')
<div class="max-w-7xl mx-auto" x-data="paymentDashboard()" x-init="init()">

    <!-- En-tête avec statistiques -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-lg p-6 mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Tableau de Bord des Paiements</h1>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4" x-show="stats">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">En attente</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="stats.pending_count || 0"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">File d'attente</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="stats.queue_count || 0"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Virements</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="stats.bank_transfers_queue || 0"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Espèces</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="stats.cash_deliveries_queue || 0"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Système d'onglets -->
    <div class="bg-white rounded-xl shadow-lg">
        <!-- En-têtes des onglets -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="currentTab = 'pending'"
                        :class="currentTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Demandes en Attente
                        <span x-show="stats.pending_count > 0" x-text="`(${stats.pending_count})`" class="ml-1 text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full"></span>
                    </span>
                </button>

                <button @click="currentTab = 'queue'"
                        :class="currentTab === 'queue' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2"/>
                        </svg>
                        File d'Attente
                        <span x-show="stats.queue_count > 0" x-text="`(${stats.queue_count})`" class="ml-1 text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full"></span>
                    </span>
                </button>

                <button @click="currentTab = 'history'"
                        :class="currentTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Historique
                    </span>
                </button>
            </nav>
        </div>

        <!-- Contenu des onglets -->
        <div class="p-6">

            <!-- Onglet Demandes en Attente -->
            <div x-show="currentTab === 'pending'" x-transition>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nouvelles Demandes de Paiement</h3>
                    <button @click="loadData()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                </div>

                <!-- Table des demandes en attente -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Client</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in pendingPayments" :key="item.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="item.client_name"></div>
                                            <div class="text-sm text-gray-500" x-text="item.client_phone"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-purple-600" x-text="item.amount + ' DT'"></div>
                                        <div class="text-xs text-gray-500" x-text="item.request_code"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="item.method === 'BANK_TRANSFER' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" x-text="item.method_display">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.created_at"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-green-600" x-text="item.wallet_balance + ' DT'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button @click="approvePayment(item.id)"
                                                :disabled="loading"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 disabled:opacity-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approuver
                                        </button>
                                        <button @click="rejectPayment(item.id)"
                                                :disabled="loading"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 disabled:opacity-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Refuser
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="pendingPayments.length === 0 && !loading">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    Aucune demande en attente
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet File d'Attente -->
            <div x-show="currentTab === 'queue'" x-transition>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Paiements à Traiter</h3>
                    <div class="text-sm text-gray-600">
                        <span x-text="queuePayments.filter(p => p.method === 'BANK_TRANSFER').length"></span> virements •
                        <span x-text="queuePayments.filter(p => p.method === 'CASH_DELIVERY').length"></span> espèces
                    </div>
                </div>

                <!-- Table de la file d'attente -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approuvé le</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in queuePayments" :key="item.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="item.client_name"></div>
                                            <div class="text-sm text-gray-500" x-text="item.client_phone"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-purple-600" x-text="item.amount + ' DT'"></div>
                                        <div class="text-xs text-gray-500" x-text="item.request_code"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="item.method === 'BANK_TRANSFER' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" x-text="item.method_display">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.approved_at"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <!-- Bouton pour Virement Bancaire -->
                                        <button x-show="item.method === 'BANK_TRANSFER'"
                                                @click="processBankTransfer(item)"
                                                :disabled="loading"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            📝 Traiter le Virement
                                        </button>

                                        <!-- Bouton pour Paiement Espèce -->
                                        <button x-show="item.method === 'CASH_DELIVERY'"
                                                @click="assignToDepot(item.id)"
                                                :disabled="loading"
                                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            🚚 Assigner au Dépôt
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="queuePayments.length === 0 && !loading">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2"/>
                                    </svg>
                                    Aucun paiement en file d'attente
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Historique -->
            <div x-show="currentTab === 'history'" x-transition>
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Historique des Paiements</h3>
                    <div class="text-sm text-gray-600">50 derniers paiements traités</div>
                </div>

                <!-- Table de l'historique -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence/Colis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dépôt Assigné</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in historyPayments" :key="item.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900" x-text="item.client_name"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-purple-600" x-text="item.amount + ' DT'"></div>
                                        <div class="text-xs text-gray-500" x-text="item.request_code"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="item.method_display === 'Virement bancaire' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" x-text="item.method_display">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="item.status_color" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" x-text="item.status_display"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div x-show="item.virement_reference">
                                            <span class="text-xs font-mono text-blue-600" x-text="item.virement_reference"></span>
                                            <div class="text-xs text-gray-500">Virement</div>
                                        </div>
                                        <div x-show="item.package_code">
                                            <span class="text-xs font-mono text-green-600" x-text="item.package_code"></span>
                                            <div class="text-xs text-gray-500">Colis créé</div>
                                        </div>
                                        <span x-show="!item.virement_reference && !item.package_code" class="text-xs text-gray-400">—</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div x-show="item.depot_manager">
                                            <div class="text-sm font-medium text-gray-900" x-text="item.depot_manager.name"></div>
                                            <div class="text-xs text-gray-500" x-text="item.depot_manager.depot_name"></div>
                                        </div>
                                        <span x-show="!item.depot_manager" class="text-xs text-gray-400">Non assigné</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.updated_at"></td>
                                </tr>
                            </template>
                            <tr x-show="historyPayments.length === 0 && !loading">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Aucun historique disponible
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal de traitement virement bancaire -->
    <div x-show="showBankTransferModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.away="showBankTransferModal = false"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Traiter le Virement Bancaire
                            </h3>
                            <div class="mt-4" x-show="selectedPayment">
                                <!-- Informations de rappel -->
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Détails du Paiement</h4>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div><span class="font-medium">Client:</span> <span x-text="selectedPayment?.client_name"></span></div>
                                        <div><span class="font-medium">Téléphone:</span> <span x-text="selectedPayment?.client_phone"></span></div>
                                        <div><span class="font-medium">Montant:</span> <span x-text="selectedPayment?.amount + ' DT'"></span></div>
                                        <div x-show="selectedPayment?.bank_details">
                                            <span class="font-medium">Banque:</span> <span x-text="selectedPayment?.bank_details?.bank_name"></span>
                                        </div>
                                        <div x-show="selectedPayment?.bank_details">
                                            <span class="font-medium">RIB:</span> <span x-text="selectedPayment?.bank_details?.rib"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Champ référence virement -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Numéro de Référence du Virement*
                                    </label>
                                    <input type="text"
                                           x-model="virementReference"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Ex: VIR2024001234 ou REF-BANK-12345"
                                           :class="virementReferenceError ? 'border-red-300' : ''"
                                           required>
                                    <p x-show="virementReferenceError" class="mt-1 text-sm text-red-600" x-text="virementReferenceError"></p>
                                    <p class="mt-1 text-xs text-gray-500">Cette référence doit être unique et provenir de votre banque</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmBankTransfer()"
                            :disabled="!virementReference || virementReference.length < 3 || loading"
                            :class="(!virementReference || virementReference.length < 3 || loading) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <span x-show="!loading">Valider le Paiement</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                    <button @click="showBankTransferModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div x-show="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-40 flex items-center justify-center" style="display: none;">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Traitement en cours...
            </div>
        </div>
    </div>

</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script>
function paymentDashboard() {
    return {
        // État
        currentTab: 'pending',
        loading: false,
        stats: {},
        pendingPayments: [],
        queuePayments: [],
        historyPayments: [],

        // Modal virement bancaire
        showBankTransferModal: false,
        selectedPayment: null,
        virementReference: '',
        virementReferenceError: '',

        async init() {
            await this.loadData();
        },

        async loadData() {
            if (this.loading) return;

            this.loading = true;
            try {
                const response = await fetch('/commercial/api/payment-dashboard', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!response.ok) {
                    throw new Error('Erreur de chargement');
                }

                const data = await response.json();

                if (data.success) {
                    this.stats = data.data.stats;
                    this.pendingPayments = data.data.pending;
                    this.queuePayments = data.data.queue;
                    this.historyPayments = data.data.history;
                } else {
                    this.showToast(data.message || 'Erreur de chargement', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async approvePayment(id) {
            if (this.loading) return;

            if (!confirm('Êtes-vous sûr de vouloir approuver cette demande de paiement ?')) {
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`/commercial/api/payments/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notes: 'Approuvé via tableau de bord'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    await this.loadData(); // Recharger les données
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'approbation', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async rejectPayment(id) {
            if (this.loading) return;

            const reason = prompt('Raison du refus (obligatoire):');
            if (!reason || reason.trim() === '') {
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`/commercial/api/payments/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        reason: reason.trim()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    await this.loadData(); // Recharger les données
                } else {
                    this.showToast(data.message || 'Erreur lors du refus', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        processBankTransfer(payment) {
            this.selectedPayment = payment;
            this.virementReference = '';
            this.virementReferenceError = '';
            this.showBankTransferModal = true;
        },

        async confirmBankTransfer() {
            if (this.loading) return;

            // Validation
            if (!this.virementReference || this.virementReference.length < 3) {
                this.virementReferenceError = 'La référence doit contenir au moins 3 caractères';
                return;
            }

            this.virementReferenceError = '';
            this.loading = true;

            try {
                const response = await fetch(`/commercial/api/payments/${this.selectedPayment.id}/process-bank-transfer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        virement_reference: this.virementReference.trim()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showBankTransferModal = false;
                    await this.loadData(); // Recharger les données
                } else {
                    if (data.message.includes('référence')) {
                        this.virementReferenceError = data.message;
                    } else {
                        this.showToast(data.message || 'Erreur lors du traitement', 'error');
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async assignToDepot(id) {
            if (this.loading) return;

            if (!confirm('Êtes-vous sûr de vouloir assigner ce paiement au dépôt correspondant ?')) {
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`/commercial/api/payments/${id}/assign-to-depot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    await this.loadData(); // Recharger les données
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'assignation', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full opacity-0`;

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            toast.classList.add(colors[type] || colors.info);
            toast.textContent = message;

            const container = document.getElementById('toast-container');
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    if (toast.parentNode) {
                        container.removeChild(toast);
                    }
                }, 300);
            }, 4000);
        }
    };
}
</script>
@endpush