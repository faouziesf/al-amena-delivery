@extends('layouts.commercial')

@section('title', 'Avances - ' . $client->name)
@section('page-title', 'Gestion Avance Client')
@section('page-description', 'Gérer l\'avance pour ' . $client->name)

@section('breadcrumbs')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('commercial.client-advances.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-emerald-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/>
                </svg>
                Avances Clients
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $client->name }}</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.clients.show', $client) }}"
       class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Profil Complet
    </a>
    <button @click="exportTransactions()"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exporter
    </button>
</div>
@endsection

@section('content')
<div x-data="clientAdvanceDetailApp()" x-init="init()">
    <!-- Client Info & Balance Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Client Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 flex items-center justify-center">
                        <span class="text-xl font-bold text-white">{{ substr($client->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $client->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $client->email }}</p>
                        <p class="text-sm text-gray-600">{{ $client->phone }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Statut compte:</span>
                        <span class="text-sm font-medium {{ $client->account_status === 'ACTIVE' ? 'text-green-600' : 'text-orange-600' }}">
                            {{ $client->account_status === 'ACTIVE' ? 'Actif' : 'En attente' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Créé le:</span>
                        <span class="text-sm text-gray-900">{{ $client->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Dernière connexion:</span>
                        <span class="text-sm text-gray-900">
                            {{ $client->last_login_at ? $client->last_login_at->format('d/m/Y H:i') : 'Jamais' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Cards -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Main Balance -->
                <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Solde Principal</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($client->wallet->balance ?? 0, 3) }} DT</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Disponible: {{ number_format(($client->wallet->balance ?? 0) - ($client->wallet->frozen_amount ?? 0), 3) }} DT
                            </p>
                        </div>
                        <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Advance Balance -->
                <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Avance Accordée</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ number_format($client->wallet->advance_balance ?? 0, 3) }} DT</p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if($client->wallet->advance_last_modified_at)
                                    Modifiée {{ $client->wallet->advance_last_modified_at->diffForHumans() }}
                                @else
                                    Jamais modifiée
                                @endif
                            </p>
                        </div>
                        <div class="p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-200 p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button @click="showAddAdvanceModal = true"
                            class="flex items-center justify-center px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter Avance
                    </button>
                    <button @click="showRemoveAdvanceModal = true"
                            :disabled="!hasAdvance"
                            :class="hasAdvance ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                            class="flex items-center justify-center px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
                        </svg>
                        Retirer Avance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Advance History -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Historique des Avances</h3>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600" x-text="`${advanceTransactions.length} transaction(s)`"></span>
                    <button @click="refreshTransactions()" class="p-1 text-gray-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loadingTransactions }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="transaction in advanceTransactions" :key="transaction.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span x-text="formatDate(transaction.created_at)"></span>
                                    <span class="text-xs text-gray-500" x-text="formatTime(transaction.created_at)"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getTransactionTypeBadgeClass(transaction.type)"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      x-text="getTransactionTypeLabel(transaction.type)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="transaction.amount >= 0 ? 'text-emerald-600' : 'text-red-600'"
                                      class="text-sm font-medium"
                                      x-text="formatCurrency(Math.abs(transaction.amount))"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate" x-text="transaction.description" :title="transaction.description"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="getTransactionUser(transaction)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded" x-text="transaction.transaction_id || transaction.reference"></code>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Empty state for advance transactions -->
            <div x-show="advanceTransactions.length === 0 && !loadingTransactions" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune transaction d'avance</h3>
                <p class="mt-1 text-sm text-gray-500">Ce client n'a pas encore d'historique d'avances.</p>
            </div>

            <!-- Loading state -->
            <div x-show="loadingTransactions" class="text-center py-12">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-emerald-500 bg-white">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Chargement des transactions...
                </div>
            </div>
        </div>
    </div>

    <!-- Add Advance Modal -->
    <div x-show="showAddAdvanceModal" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ajouter une Avance</h3>
                    <button @click="showAddAdvanceModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="addAdvance()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant (DT)</label>
                        <input type="number"
                               x-model="addAdvanceForm.amount"
                               step="0.001"
                               min="0.001"
                               max="1000"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="0.000">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
                        <textarea x-model="addAdvanceForm.description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                                  placeholder="Raison de l'avance..."></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <button type="button"
                                @click="showAddAdvanceModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                            Annuler
                        </button>
                        <button type="submit"
                                :disabled="addAdvanceForm.submitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-md hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                            <span x-show="!addAdvanceForm.submitting">Ajouter</span>
                            <span x-show="addAdvanceForm.submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Ajout...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Advance Modal -->
    <div x-show="showRemoveAdvanceModal" x-cloak class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Retirer l'Avance</h3>
                    <button @click="showRemoveAdvanceModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.5 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <p class="text-sm text-yellow-800">
                                Avance actuelle: <strong>{{ number_format($client->wallet->advance_balance ?? 0, 3) }} DT</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="removeAdvance()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant à retirer (DT)</label>
                        <input type="number"
                               x-model="removeAdvanceForm.amount"
                               step="0.001"
                               min="0.001"
                               :max="clientData.wallet.advance_balance"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500"
                               placeholder="0.000">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Raison du retrait</label>
                        <textarea x-model="removeAdvanceForm.description"
                                  rows="3"
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500"
                                  placeholder="Expliquez pourquoi vous retirez cette avance..."></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <button type="button"
                                @click="showRemoveAdvanceModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                            Annuler
                        </button>
                        <button type="submit"
                                :disabled="removeAdvanceForm.submitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50 transition-colors">
                            <span x-show="!removeAdvanceForm.submitting">Retirer</span>
                            <span x-show="removeAdvanceForm.submitting" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Retrait...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function clientAdvanceDetailApp() {
    return {
        clientData: @json($client->load(['wallet.advanceModifiedBy', 'wallet.transactions'])),
        advanceTransactions: [],
        loadingTransactions: false,

        // Modals
        showAddAdvanceModal: false,
        showRemoveAdvanceModal: false,

        // Forms
        addAdvanceForm: {
            amount: '',
            description: '',
            submitting: false
        },
        removeAdvanceForm: {
            amount: '',
            description: '',
            submitting: false
        },

        async init() {
            await this.loadAdvanceTransactions();
        },

        get hasAdvance() {
            return (this.clientData.wallet?.advance_balance || 0) > 0;
        },

        async loadAdvanceTransactions() {
            this.loadingTransactions = true;
            try {
                // Filter advance-related transactions
                this.advanceTransactions = this.clientData.wallet.transactions.filter(transaction =>
                    ['ADVANCE_CREDIT', 'ADVANCE_DEBIT', 'ADVANCE_USAGE'].includes(transaction.type)
                );
            } catch (error) {
                console.error('Erreur chargement transactions:', error);
            } finally {
                this.loadingTransactions = false;
            }
        },

        async addAdvance() {
            this.addAdvanceForm.submitting = true;
            try {
                const response = await fetch(`{{ route('commercial.client-advances.add', $client) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.addAdvanceForm.amount),
                        description: this.addAdvanceForm.description
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    alert(data.message);

                    // Refresh page to show updated data
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout de l\'avance');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout de l\'avance');
            } finally {
                this.addAdvanceForm.submitting = false;
            }
        },

        async removeAdvance() {
            this.removeAdvanceForm.submitting = true;
            try {
                const response = await fetch(`{{ route('commercial.client-advances.remove', $client) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(this.removeAdvanceForm.amount),
                        description: this.removeAdvanceForm.description
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    alert(data.message);

                    // Refresh page to show updated data
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors du retrait de l\'avance');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du retrait de l\'avance');
            } finally {
                this.removeAdvanceForm.submitting = false;
            }
        },

        async refreshTransactions() {
            await this.loadAdvanceTransactions();
        },

        // Utility methods
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            }).format(amount || 0) + ' DT';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR');
        },

        formatTime(dateString) {
            return new Date(dateString).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getTransactionTypeLabel(type) {
            const labels = {
                'ADVANCE_CREDIT': 'Avance ajoutée',
                'ADVANCE_DEBIT': 'Avance retirée',
                'ADVANCE_USAGE': 'Avance utilisée'
            };
            return labels[type] || type;
        },

        getTransactionTypeBadgeClass(type) {
            const classes = {
                'ADVANCE_CREDIT': 'bg-green-100 text-green-800',
                'ADVANCE_DEBIT': 'bg-red-100 text-red-800',
                'ADVANCE_USAGE': 'bg-blue-100 text-blue-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        },

        getTransactionUser(transaction) {
            // Extract user info from metadata if available
            try {
                const metadata = typeof transaction.metadata === 'string'
                    ? JSON.parse(transaction.metadata)
                    : transaction.metadata;

                if (metadata?.added_by_user_id || metadata?.removed_by_user_id) {
                    return 'Commercial'; // Could be enhanced to show actual user name
                }
            } catch (e) {
                // Ignore JSON parse errors
            }
            return transaction.created_by_name || 'Système';
        }
    };
}

// Global functions
function exportTransactions() {
    window.location.href = `{{ route('commercial.client-advances.show', $client) }}?export=transactions`;
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection