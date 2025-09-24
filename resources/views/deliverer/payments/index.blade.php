@extends('layouts.deliverer')

@section('title', 'Paiements Clients')

@section('content')
<div class="bg-gray-50" x-data="paymentsApp()">
    
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Paiements Clients
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600" x-text="`${payments.length} paiements à livrer`"></span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-green-600">💵 Livraison espèces</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="openScanner()" 
                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>
                    <button @click="toggleBulkMode()" 
                            x-show="payments.length > 0"
                            class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                            :class="bulkMode ? 'bg-green-100 text-green-600' : 'bg-gray-100'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v1a2 2 0 002 2h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v.93m-6 2.427c0 .1.031.199.09.289l.06.17c.078.315.422.559.75.559h2.28c.328 0 .672-.244.75-.56l.06-.169a.75.75 0 00.09-.29v-2.926m0 0a24.704 24.704 0 00-.161-3.08.676.676 0 00-.088-.281 6.738 6.738 0 01-.748-.924.999.999 0 00-.799-.34h-2.48a.999.999 0 00-.799.34 6.738 6.738 0 01-.748.924.676.676 0 00-.088.281A24.707 24.707 0 009 10.73v2.926z"/>
                        </svg>
                    </button>
                    <a href="{{ route('deliverer.payments.history') }}"
                       class="p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </a>
                    <button @click="refreshData()"
                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                            :class="loading ? 'opacity-50' : ''">
                        <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div x-show="bulkMode && selectedPayments.length > 0" x-transition 
                 class="mt-4 p-3 bg-green-50 rounded-xl border border-green-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-900" x-text="`${selectedPayments.length} paiements sélectionnés`"></span>
                    <div class="flex space-x-2">
                        <button @click="bulkDeliver()" 
                                class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                            Marquer Livrés
                        </button>
                        <button @click="clearSelection()" 
                                class="text-gray-600 hover:text-gray-800 px-3 py-2 text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Security Banner -->
            <div class="mt-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">💵 PAIEMENTS SÉCURISÉS</p>
                        <p class="text-xs text-amber-700">Ces montants ont été remis par le commercial. Livrer EXACTEMENT le montant indiqué + demander signature.</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3" x-show="!bulkMode">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="applyFilters()"
                           placeholder="Rechercher par code, nom client..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="payments.length === 0 && !loading" 
         class="text-center py-16 px-4">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement en attente</h3>
        <p class="text-gray-500 text-sm mb-4">Aucune demande de retrait client ne vous a été assignée pour le moment.</p>
        <a href="{{ route('deliverer.payments.history') }}" 
           class="inline-block bg-green-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-700 transition-colors">
            Voir Historique
        </a>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-16">
        <div class="animate-spin w-8 h-8 border-4 border-green-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des paiements...</p>
    </div>

    <!-- Payments List -->
    <div class="px-4 pb-6 space-y-4">
        <template x-for="payment in filteredPayments" :key="payment.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Bulk Selection Checkbox -->
                <div x-show="bulkMode" class="p-4 bg-green-50 border-b border-green-100">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" 
                               :value="payment.id" 
                               x-model="selectedPayments"
                               class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="text-sm font-medium text-green-900">Sélectionner ce paiement</span>
                    </label>
                </div>

                <!-- Payment Header -->
                <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-100">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-green-900" x-text="payment.request_code"></span>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-green-600 font-medium">💵 À LIVRER</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-bold text-green-600" x-text="formatAmount(payment.amount)"></div>
                            <span class="text-xs text-green-800 font-medium">ESPÈCES</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="formatDate(payment.created_at)"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                Retrait espèces
                            </span>
                            <template x-if="payment.delivery_attempts > 0">
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full" 
                                      x-text="`${payment.delivery_attempts} tentative(s)`"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="p-4 space-y-3">
                    
                    <!-- Client Info (Priority) -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-2 border-blue-200">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-bold text-blue-700 bg-blue-200 px-3 py-1 rounded-full">
                                        💵 REMETTRE À
                                    </span>
                                </div>
                                <p class="text-lg font-bold text-gray-900 mb-1" x-text="payment.client.name"></p>
                                <p class="text-sm text-gray-700 mb-2" x-text="payment.client.address || 'Adresse dans les détails'"></p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-1 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <a :href="`tel:${payment.client.phone}`" 
                                           class="text-blue-600 font-medium hover:text-blue-700" 
                                           x-text="payment.client.phone"></a>
                                    </div>
                                    <button @click="contactClient(payment)" 
                                            class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors">
                                        📞 Appeler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Security Info -->
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-4 rounded-xl border border-amber-300">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-amber-800">MONTANT À REMETTRE</p>
                                <p class="text-2xl font-bold text-amber-900" x-text="formatAmount(payment.amount)"></p>
                            </div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-bold text-green-800">PROCÉDURE:</p>
                                    <p class="text-xs text-green-700">1. Vérifier identité client • 2. Remettre montant exact • 3. Demander signature • 4. Scanner bon de livraison</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Receipt Code -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <div>
                                <span class="text-gray-600">Code de livraison:</span>
                                <span class="font-bold text-gray-900 ml-1" x-text="payment.delivery_receipt_code"></span>
                            </div>
                            <button @click="copyCode(payment.delivery_receipt_code)" 
                                    class="text-blue-600 hover:text-blue-800 text-xs">
                                Copier
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-600">
                                <span>Traité par: </span>
                                <span class="text-gray-900" x-text="payment.processed_by_commercial?.name || 'Commercial'"></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="'Demande: ' + formatDate(payment.created_at)"></div>
                        </div>
                        <template x-if="payment.processing_notes">
                            <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs font-medium text-blue-800">Notes commercial:</p>
                                <p class="text-sm text-blue-700" x-text="payment.processing_notes"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 bg-gray-50 border-t" x-show="!bulkMode">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Primary Action: Deliver -->
                        <button @click="markDelivered(payment)" 
                                :disabled="payment.processing" 
                                class="col-span-2 bg-green-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!payment.processing" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Marquer Livré + Signature</span>
                            </span>
                            <span x-show="payment.processing" class="flex items-center justify-center space-x-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Traitement...</span>
                            </span>
                        </button>
                        
                        <!-- Secondary Actions -->
                        <button @click="markUnavailable(payment)" 
                                class="bg-yellow-100 text-yellow-700 py-2 px-3 rounded-xl font-medium hover:bg-yellow-200 transition-colors">
                            Client Non Dispo
                        </button>
                        
                        <button @click="viewPaymentDetails(payment)" 
                                class="bg-gray-200 text-gray-700 py-2 px-3 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Payment Delivery Modal -->
    <div x-show="showDeliveryModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Confirmer Livraison Paiement</h3>
                <button @click="showDeliveryModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Payment Info -->
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-bold text-green-600" x-text="selectedPaymentForDelivery?.request_code"></p>
                    <p class="text-sm text-gray-600" x-text="selectedPaymentForDelivery?.client.name"></p>
                    <p class="text-xs text-gray-500" x-text="selectedPaymentForDelivery?.delivery_receipt_code"></p>
                </div>

                <!-- Amount (Most Important) -->
                <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl">
                    <p class="text-sm font-bold text-green-800 mb-2">MONTANT À REMETTRE:</p>
                    <p class="text-3xl font-bold text-green-900" x-text="selectedPaymentForDelivery ? formatAmount(selectedPaymentForDelivery.amount) : ''"></p>
                    <p class="text-xs text-red-600 font-medium mt-1">💵 Espèces exactes</p>
                </div>
                
                <!-- Amount Confirmation -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Montant remis (DT) *
                        <span class="text-red-600">- DOIT ÊTRE EXACTEMENT LE MONTANT CI-DESSUS</span>
                    </label>
                    <input type="number" 
                           x-model="amountDelivered" 
                           step="0.001" 
                           min="0"
                           :placeholder="selectedPaymentForDelivery ? selectedPaymentForDelivery.amount : '0.000'"
                           class="w-full px-3 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                    <template x-if="amountDelivered && selectedPaymentForDelivery && Math.abs(parseFloat(amountDelivered) - parseFloat(selectedPaymentForDelivery.amount)) > 0.001">
                        <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm font-bold text-red-800">❌ ERREUR DE MONTANT</p>
                            <p class="text-xs text-red-700">
                                Attendu: <span x-text="formatAmount(selectedPaymentForDelivery.amount)"></span><br>
                                Saisi: <span x-text="formatAmount(amountDelivered || 0)"></span>
                            </p>
                        </div>
                    </template>
                </div>
                
                <!-- Delivery Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison</label>
                    <textarea x-model="deliveryNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              rows="3"
                              placeholder="Identité vérifiée, montant remis..."></textarea>
                </div>

                <!-- Signature -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature client *</label>
                    <div class="w-full h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-500 text-sm">
                        Signature pad (à implémenter)
                    </div>
                    <p class="text-xs text-red-600 mt-1">⚠️ Signature obligatoire pour tous les paiements</p>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmDelivery()" 
                            :disabled="processingDelivery || !amountDelivered || !selectedPaymentForDelivery || Math.abs(parseFloat(amountDelivered) - parseFloat(selectedPaymentForDelivery.amount)) > 0.001"
                            class="flex-1 bg-green-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processingDelivery">Confirmer Livraison</span>
                        <span x-show="processingDelivery" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showDeliveryModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unavailable Modal -->
    <div x-show="showUnavailableModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Client Non Disponible</h3>
                <button @click="showUnavailableModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-bold text-green-600" x-text="selectedPaymentForUnavailable?.request_code"></p>
                    <p class="text-sm text-gray-600" x-text="selectedPaymentForUnavailable?.client.name"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison *</label>
                    <select x-model="unavailableReason" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Sélectionner une raison</option>
                        <option value="CLIENT_UNAVAILABLE">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSED">Client refuse paiement</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Détails de la tentative *</label>
                    <textarea x-model="unavailableNotes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              rows="3"
                              placeholder="Détails de la situation..."
                              required></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="confirmUnavailable()" 
                            :disabled="processingUnavailable || !unavailableReason || !unavailableNotes"
                            class="flex-1 bg-yellow-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-yellow-700 disabled:opacity-50">
                        <span x-show="!processingUnavailable">Enregistrer Tentative</span>
                        <span x-show="processingUnavailable" class="flex items-center justify-center space-x-2">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>
                    <button @click="showUnavailableModal = false" 
                            class="px-4 py-3 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div x-show="showScannerModal" x-transition class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Scanner Code Paiement</h3>
                <button @click="showScannerModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Camera would go here -->
                <div class="aspect-square bg-gray-100 rounded-xl flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ou saisir le code</label>
                    <input type="text" x-model="manualCode" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Code de livraison...">
                </div>
                
                <div class="flex space-x-3">
                    <button @click="processPaymentCode(manualCode)" 
                            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700">
                        Valider
                    </button>
                    <button @click="showScannerModal = false" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Spacing -->
    <div class="h-20"></div>
</div>

<script>
function paymentsApp() {
    return {
        payments: @json($payments ?? []),
        loading: false,
        searchQuery: '',
        bulkMode: false,
        selectedPayments: [],
        
        // Delivery modal
        showDeliveryModal: false,
        selectedPaymentForDelivery: null,
        amountDelivered: '',
        deliveryNotes: '',
        processingDelivery: false,
        
        // Unavailable modal
        showUnavailableModal: false,
        selectedPaymentForUnavailable: null,
        unavailableReason: '',
        unavailableNotes: '',
        processingUnavailable: false,

        // Scanner modal
        showScannerModal: false,
        manualCode: '',

        get filteredPayments() {
            let filtered = [...this.payments];
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(payment => 
                    payment.request_code.toLowerCase().includes(query) ||
                    payment.delivery_receipt_code.toLowerCase().includes(query) ||
                    payment.client.name.toLowerCase().includes(query) ||
                    payment.client.phone.includes(query)
                );
            }
            
            return filtered;
        },

        init() {
            // Auto-refresh every 45 seconds
            setInterval(() => {
                this.refreshData();
            }, 45000);
        },

        toggleBulkMode() {
            this.bulkMode = !this.bulkMode;
            if (!this.bulkMode) {
                this.selectedPayments = [];
            }
        },

        clearSelection() {
            this.selectedPayments = [];
            this.bulkMode = false;
        },

        async refreshData() {
            this.loading = true;
            
            try {
                const response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Erreur refresh:', error);
                this.showToast('Erreur lors du rafraîchissement', 'error');
            }
            
            this.loading = false;
        },

        applyFilters() {
            // Auto-applied via getter
        },

        markDelivered(payment) {
            this.selectedPaymentForDelivery = payment;
            this.amountDelivered = payment.amount; // Pre-fill
            this.deliveryNotes = '';
            this.showDeliveryModal = true;
        },

        async confirmDelivery() {
            if (!this.selectedPaymentForDelivery || !this.amountDelivered) return;
            
            // Vérification montant exact
            const expectedAmount = parseFloat(this.selectedPaymentForDelivery.amount);
            const deliveredAmount = parseFloat(this.amountDelivered);
            
            if (Math.abs(deliveredAmount - expectedAmount) > 0.001) {
                this.showToast(`Erreur montant: Attendu ${this.formatAmount(expectedAmount)}, saisi ${this.formatAmount(deliveredAmount)}`, 'error');
                return;
            }
            
            this.processingDelivery = true;
            
            try {
                const formData = new FormData();
                formData.append('amount_delivered', this.amountDelivered);
                formData.append('delivery_notes', this.deliveryNotes);
                formData.append('client_signature', 'digital_signature'); // TODO: Real signature
                
                const response = await fetch(`/deliverer/payments/${this.selectedPaymentForDelivery.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showDeliveryModal = false;
                    
                    // Retirer le paiement de la liste
                    this.payments = this.payments.filter(p => p.id !== this.selectedPaymentForDelivery.id);
                    
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur livraison paiement:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingDelivery = false;
        },

        markUnavailable(payment) {
            this.selectedPaymentForUnavailable = payment;
            this.unavailableReason = '';
            this.unavailableNotes = '';
            this.showUnavailableModal = true;
        },

        async confirmUnavailable() {
            if (!this.selectedPaymentForUnavailable || !this.unavailableReason || !this.unavailableNotes) return;
            
            this.processingUnavailable = true;
            
            try {
                const response = await fetch(`/deliverer/payments/${this.selectedPaymentForUnavailable.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        unavailable_reason: this.unavailableReason,
                        attempt_notes: this.unavailableNotes
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showUnavailableModal = false;
                    
                    // Update attempt count or remove if returned to commercial
                    const paymentIndex = this.payments.findIndex(p => p.id === this.selectedPaymentForUnavailable.id);
                    if (paymentIndex !== -1) {
                        if (data.message.includes('retourné au commercial')) {
                            // Remove from list
                            this.payments = this.payments.filter(p => p.id !== this.selectedPaymentForUnavailable.id);
                        } else {
                            // Update attempts
                            this.payments[paymentIndex].delivery_attempts++;
                        }
                    }
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur tentative paiement:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingUnavailable = false;
        },

        openScanner() {
            this.showScannerModal = true;
            this.manualCode = '';
        },

        async processPaymentCode(code) {
            if (!code || code.trim().length < 3) {
                this.showToast('Code invalide', 'error');
                return;
            }

            try {
                const response = await fetch('/deliverer/payments/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code.trim() })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showScannerModal = false;
                    this.showToast(data.message);
                    
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur scan paiement:', error);
                this.showToast('Erreur de connexion', 'error');
            }
        },

        viewPaymentDetails(payment) {
            window.location.href = `/deliverer/payments/${payment.id}`;
        },

        contactClient(payment) {
            window.location.href = `tel:${payment.client.phone}`;
        },

        copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                this.showToast('Code copié!');
            });
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }
}
</script>
@endsection