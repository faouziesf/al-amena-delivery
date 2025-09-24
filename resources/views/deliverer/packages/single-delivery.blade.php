@extends('layouts.deliverer')

@section('title', 'Livraison Rapide')

@section('content')
<div x-data="singleDeliveryApp()" x-init="init()">

    <!-- Header fixe avec navigation -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="p-4">
            <!-- Titre et navigation -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <button @click="window.history.back()"
                            class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Livraison Rapide
                        </h1>
                        <div x-show="packages.length > 0" class="text-sm text-gray-600">
                            <span x-text="currentIndex + 1"></span> / <span x-text="packages.length"></span> -
                            <span x-text="formatAmount(currentPackage?.cod_amount || 0)"></span> COD
                        </div>
                    </div>
                </div>

                <!-- Boutons de navigation -->
                <div class="flex items-center space-x-2">
                    <button @click="previousPackage()"
                            :disabled="currentIndex === 0"
                            class="p-3 bg-gray-100 text-gray-600 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-200 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="nextPackage()"
                            :disabled="currentIndex >= packages.length - 1"
                            class="p-3 bg-gray-100 text-gray-600 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-200 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Progress bar -->
            <div x-show="packages.length > 0" class="w-full bg-gray-200 rounded-full h-2 mb-3">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300"
                     :style="`width: ${((currentIndex + 1) / packages.length) * 100}%`"></div>
            </div>

            <!-- Stats rapides -->
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="text-center p-2 bg-orange-50 rounded-lg">
                    <div class="font-bold text-orange-600" x-text="packages.length"></div>
                    <div class="text-orange-500">Total</div>
                </div>
                <div class="text-center p-2 bg-red-50 rounded-lg">
                    <div class="font-bold text-red-600" x-text="stats.urgent"></div>
                    <div class="text-red-500">Urgents</div>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <div class="font-bold text-blue-600" x-text="stats.retry"></div>
                    <div class="text-blue-500">Tentatives</div>
                </div>
                <div class="text-center p-2 bg-green-50 rounded-lg">
                    <div class="font-bold text-green-600" x-text="formatCodShort(stats.total_cod)"></div>
                    <div class="text-green-500">COD Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- État vide -->
    <div x-show="packages.length === 0" class="text-center py-16 px-4">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">Toutes les livraisons sont terminées !</h3>
        <p class="text-gray-600 mb-6">Excellent travail ! Vous n'avez plus de colis à livrer.</p>
        <a href="{{ route('deliverer.dashboard') }}"
           class="inline-block bg-orange-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-orange-600 transition-colors">
            🏠 Retour au Dashboard
        </a>
    </div>

    <!-- Colis actuel -->
    <div x-show="packages.length > 0 && currentPackage" class="p-4">

        <!-- Card du colis avec priorité visuelle -->
        <div class="bg-white rounded-2xl shadow-lg border overflow-hidden"
             :class="getPriorityBorderClass(currentPackage)">

            <!-- Header avec badge de priorité -->
            <div class="p-6 border-b border-gray-100"
                 :class="getPriorityBackgroundClass(currentPackage)">

                <!-- Code et badges -->
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="currentPackage.package_code"></h2>
                        <div class="flex flex-wrap gap-2">
                            <span x-show="isUrgent(currentPackage)"
                                  class="px-3 py-1 bg-red-100 text-red-800 text-sm font-bold rounded-full animate-pulse">
                                🚨 URGENT - Tentative <span x-text="currentPackage.delivery_attempts + 1"></span>/3
                            </span>
                            <span x-show="currentPackage.delivery_attempts > 0 && !isUrgent(currentPackage)"
                                  class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">
                                🔄 Tentative <span x-text="currentPackage.delivery_attempts + 1"></span>/3
                            </span>
                            <span x-show="isHighValue(currentPackage)"
                                  class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                💰 Valeur élevée
                            </span>
                        </div>
                    </div>

                    <!-- COD montant -->
                    <div class="text-right">
                        <div class="text-3xl font-bold text-orange-600" x-text="formatAmount(currentPackage.cod_amount)"></div>
                        <div class="text-sm text-gray-500">COD à collecter</div>
                    </div>
                </div>
            </div>

            <!-- Informations destinataire avec actions rapides -->
            <div class="p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <span class="text-green-600 mr-2">🎯</span> DESTINATAIRE
                        </h3>

                        <div class="space-y-4">
                            <!-- Nom du destinataire -->
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <div>
                                    <div class="font-bold text-lg text-gray-900" x-text="getRecipientName(currentPackage)"></div>
                                    <div class="text-xs text-gray-500">Destinataire</div>
                                </div>
                            </div>

                            <!-- Téléphone avec bouton d'appel -->
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900" x-text="getRecipientPhone(currentPackage)"></div>
                                    <div class="text-xs text-gray-500">Téléphone</div>
                                </div>
                                <!-- Bouton d'appel intégré -->
                                <button @click="callClient(currentPackage)"
                                        class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-600 transition-colors inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    📞 Appeler
                                </button>
                            </div>

                            <!-- Adresse avec bouton GPS -->
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-gray-900 font-medium" x-text="getRecipientAddress(currentPackage)"></div>
                                    <div class="text-xs text-gray-500 mt-1" x-text="currentPackage.delegation_to?.name || 'Zone non spécifiée'"></div>
                                </div>
                                <!-- Bouton navigation GPS -->
                                <button @click="openNavigation(currentPackage)"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
                                    </svg>
                                    🗺️ GPS
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails du colis -->
                <div class="bg-gray-50 p-4 rounded-xl mb-6">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        Détails du Colis
                    </h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Expéditeur:</span>
                            <div class="font-medium" x-text="getSenderName(currentPackage)"></div>
                        </div>
                        <div>
                            <span class="text-gray-500">Contenu:</span>
                            <div class="font-medium" x-text="currentPackage.content_description || 'Non spécifié'"></div>
                        </div>
                        <div>
                            <span class="text-gray-500">Valeur:</span>
                            <div class="font-medium" x-text="formatAmount(currentPackage.package_value || 0)"></div>
                        </div>
                        <div>
                            <span class="text-gray-500">Poids:</span>
                            <div class="font-medium" x-text="(currentPackage.package_weight || 0) + ' kg'"></div>
                        </div>
                        <div>
                            <span class="text-gray-500">Statut:</span>
                            <div class="font-medium" x-text="getStatusLabel(currentPackage.status)"></div>
                        </div>
                        <div>
                            <span class="text-gray-500">Zone livraison:</span>
                            <div class="font-medium text-orange-600" x-text="currentPackage.delegation_to?.name || 'Non spécifiée'"></div>
                        </div>
                    </div>

                    <!-- Attributs spéciaux -->
                    <div x-show="currentPackage.is_fragile || currentPackage.requires_signature" class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <span x-show="currentPackage.is_fragile"
                                  class="px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                🔴 Fragile
                            </span>
                            <span x-show="currentPackage.requires_signature"
                                  class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                ✏️ Signature requise
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Historique des tentatives -->
                <div x-show="currentPackage.delivery_attempts > 0" class="bg-yellow-50 p-4 rounded-xl mb-6 border border-yellow-200">
                    <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Historique des Tentatives
                    </h4>
                    <div class="text-sm text-yellow-800">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium">Tentatives précédentes:</span>
                            <span x-text="currentPackage.delivery_attempts"></span>
                        </div>
                        <div x-show="currentPackage.unavailable_reason" class="mt-1">
                            <span class="font-medium">Dernière raison:</span>
                            <span x-text="getReasonLabel(currentPackage.unavailable_reason)"></span>
                        </div>
                        <div x-show="currentPackage.unavailable_notes" class="mt-1">
                            <span class="font-medium">Notes:</span>
                            <span x-text="currentPackage.unavailable_notes"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions principales - GRANDES ET RAPIDES -->
                <div class="space-y-3">
                    <!-- Action principale: Livrer -->
                    <button @click="showDeliveryOptions(currentPackage)"
                            :disabled="processing === currentPackage.id"
                            class="w-full bg-gradient-to-r from-emerald-500 to-green-500 text-white py-6 px-6 rounded-2xl font-bold text-lg hover:shadow-lg transform hover:scale-[1.02] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="processing !== currentPackage.id" class="flex items-center justify-center space-x-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>LIVRÉ - COLLECTER COD</span>
                        </span>
                        <span x-show="processing === currentPackage.id" class="flex items-center justify-center space-x-3">
                            <div class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span>Traitement...</span>
                        </span>
                    </button>

                    <!-- Actions secondaires -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Client indisponible -->
                        <button @click="markUnavailable(currentPackage)"
                                :disabled="processing === currentPackage.id"
                                class="bg-orange-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-orange-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="processing !== currentPackage.id" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>INDISPONIBLE</span>
                            </span>
                            <span x-show="processing === currentPackage.id" class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>...</span>
                            </span>
                        </button>

                        <!-- Voir détails complets -->
                        <button @click="viewDetails(currentPackage)"
                                class="bg-gray-600 text-white py-4 px-4 rounded-xl font-semibold hover:bg-gray-700 transition-all">
                            <span class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>DÉTAILS</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Indisponible (copié et adapté) -->
    <div x-show="showUnavailableModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showUnavailableModal"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Client Indisponible</h3>
                <button @click="showUnavailableModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUnavailable()">
                <!-- Info tentative -->
                <div class="mb-4 bg-orange-50 p-4 rounded-xl border border-orange-200" x-show="selectedPackage">
                    <h4 class="font-semibold text-orange-800 mb-2">
                        📊 Tentative # <span x-text="(selectedPackage?.delivery_attempts || 0) + 1"></span>/3
                    </h4>
                    <div x-show="(selectedPackage?.delivery_attempts || 0) >= 2">
                        <p class="text-sm text-red-700 font-medium">⚠️ Dernière tentative avant retour obligatoire</p>
                    </div>
                </div>

                <!-- Raison -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison d'indisponibilité *</label>
                    <select x-model="unavailableForm.reason" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Sélectionner une raison</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse le colis</option>
                        <option value="PHONE_OFF">Téléphone éteint/injoignable</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Détails de la tentative *
                        <span class="text-xs text-gray-500">
                            (minimum 10 caractères - <span x-text="unavailableForm.notes.length"></span>/10)
                        </span>
                    </label>
                    <textarea x-model="unavailableForm.notes" required
                              placeholder="Décrivez ce qui s'est passé (ex: personne n'a répondu, adresse fermée, client pas disponible...)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                              :class="unavailableForm.notes.length < 10 && unavailableForm.notes.length > 0 ? 'border-red-300' : ''"
                              rows="3"
                              minlength="10"></textarea>
                    <div x-show="unavailableForm.notes.length < 10 && unavailableForm.notes.length > 0"
                         class="text-red-600 text-xs mt-1">
                        Veuillez entrer au moins 10 caractères
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit"
                            :disabled="processing || !unavailableForm.reason || unavailableForm.notes.length < 10"
                            class="flex-1 bg-orange-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processing">⏰ Enregistrer Tentative</span>
                        <span x-show="processing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Enregistrement...
                        </span>
                    </button>
                    <button type="button" @click="showUnavailableModal = false"
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Options de Livraison -->
    <div x-show="showDeliveryModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showDeliveryModal"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Options de Livraison</h3>
                <button @click="showDeliveryModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Info du colis -->
            <div class="mb-6 bg-green-50 p-4 rounded-xl border border-green-200" x-show="selectedPackage">
                <h4 class="font-semibold text-green-800 mb-2">
                    📦 Livraison de <span x-text="selectedPackage?.package_code"></span>
                </h4>
                <div class="text-sm text-green-700">
                    <div><strong>Destinataire:</strong> <span x-text="getRecipientName(selectedPackage)"></span></div>
                    <div><strong>COD à collecter:</strong> <span x-text="formatAmount(selectedPackage?.cod_amount || 0)"></span></div>
                </div>
            </div>

            <!-- Option reçu de livraison -->
            <div class="mb-6">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H9.5a2 2 0 01-2-2V5a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2.5a2 2 0 012 2v2a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <div class="font-semibold text-blue-800">Imprimer reçu de livraison</div>
                            <div class="text-sm text-blue-600">Pour signature du destinataire</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="deliveryForm.printReceipt" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- COD à collecter -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">COD collecté *</label>
                <div class="relative">
                    <input type="number"
                           x-model="deliveryForm.codCollected"
                           step="0.001"
                           required
                           :placeholder="formatAmount(selectedPackage?.cod_amount || 0)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 pr-12">
                    <span class="absolute right-3 top-2 text-gray-500 text-sm">DT</span>
                </div>
                <div class="text-xs text-gray-600 mt-1">
                    Montant attendu: <span class="font-medium" x-text="formatAmount(selectedPackage?.cod_amount || 0)"></span>
                </div>
            </div>

            <!-- Nom du destinataire -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du destinataire *</label>
                <input type="text"
                       x-model="deliveryForm.recipientName"
                       required
                       :placeholder="getRecipientName(selectedPackage)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Notes de livraison (optionnel) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison (optionnel)</label>
                <textarea x-model="deliveryForm.notes"
                          placeholder="Commentaires sur la livraison, état du colis, etc."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                          rows="3"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button @click="processDelivery()"
                        :disabled="processing || !deliveryForm.codCollected || deliveryForm.codCollected <= 0 || !deliveryForm.recipientName || deliveryForm.recipientName.trim() === ''"
                        class="flex-1 bg-green-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!processing" class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>✅ Confirmer Livraison</span>
                    </span>
                    <span x-show="processing" class="flex items-center justify-center">
                        <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        Traitement...
                    </span>
                </button>
                <button @click="showDeliveryModal = false"
                        class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function singleDeliveryApp() {
    return {
        // Données
        packages: @json($packages),
        stats: @json($stats),
        currentIndex: 0,
        currentPackage: null,

        // États
        processing: null,
        showUnavailableModal: false,
        showDeliveryModal: false,
        selectedPackage: null,
        unavailableForm: {
            reason: '',
            notes: ''
        },
        deliveryForm: {
            printReceipt: false,
            notes: '',
            codCollected: 0,
            recipientName: ''
        },

        init() {
            if (this.packages.length > 0) {
                this.currentPackage = this.packages[0];

                // Debug: afficher les données du premier package pour vérifier la structure
                console.log('Premier package - recipient_data:', this.packages[0].recipient_data);
                console.log('Premier package - sender_data:', this.packages[0].sender_data);
            }
            console.log('Single Delivery App initialized:', {
                packages: this.packages.length,
                stats: this.stats,
                firstPackage: this.packages[0] || null
            });
        },

        // ==================== NAVIGATION ====================

        nextPackage() {
            if (this.currentIndex < this.packages.length - 1) {
                this.currentIndex++;
                this.currentPackage = this.packages[this.currentIndex];
            }
        },

        previousPackage() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.currentPackage = this.packages[this.currentIndex];
            }
        },

        // ==================== ACTIONS COLIS ====================

        showDeliveryOptions(package) {
            this.selectedPackage = package;
            this.showDeliveryModal = true;
            this.deliveryForm = {
                printReceipt: false,
                notes: '',
                codCollected: parseFloat(package.cod_amount || 0),
                recipientName: this.getRecipientName(package)
            };
        },

        async processDelivery() {
            if (this.processing || !this.selectedPackage) return;

            this.processing = true;

            // Validation côté client
            if (!this.deliveryForm.codCollected || this.deliveryForm.codCollected <= 0) {
                this.showToast('Veuillez entrer le montant COD collecté', 'error');
                this.processing = false;
                return;
            }

            if (!this.deliveryForm.recipientName || this.deliveryForm.recipientName.trim() === '') {
                this.showToast('Veuillez entrer le nom du destinataire', 'error');
                this.processing = false;
                return;
            }

            try {
                const formData = new FormData();
                formData.append('cod_collected', this.deliveryForm.codCollected);
                formData.append('recipient_name', this.deliveryForm.recipientName.trim());
                formData.append('delivery_notes', this.deliveryForm.notes);
                formData.append('print_receipt', this.deliveryForm.printReceipt ? '1' : '0');

                // CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    this.showToast('Token CSRF manquant - Rechargez la page', 'error');
                    this.processing = false;
                    return;
                }
                formData.append('_token', csrfToken);

                const response = await fetch(`/deliverer/packages/${this.selectedPackage.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    let errorMessage = `Erreur ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.text();
                        try {
                            const jsonError = JSON.parse(errorData);
                            if (jsonError.message) {
                                errorMessage = jsonError.message;
                            }
                        } catch (e) {
                            // Ignore JSON parse error
                        }
                    } catch (e) {
                        console.error('Could not read error response:', e);
                    }

                    this.showToast(errorMessage, 'error');
                    this.processing = false;
                    return;
                }

                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON, response was:', responseText.substring(0, 500));
                    this.showToast('Le serveur a retourné une réponse HTML au lieu de JSON', 'error');
                    this.processing = false;
                    return;
                }

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showDeliveryModal = false;

                    // Si un reçu doit être imprimé, l'ouvrir dans un nouvel onglet
                    if (this.deliveryForm.printReceipt && data.receipt_url) {
                        window.open(data.receipt_url, '_blank');
                    }

                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== this.selectedPackage.id);
                    this.stats.total--;

                    // Ajuster l'index si nécessaire
                    if (this.currentIndex >= this.packages.length && this.packages.length > 0) {
                        this.currentIndex = this.packages.length - 1;
                    }

                    if (this.packages.length > 0) {
                        this.currentPackage = this.packages[this.currentIndex];
                    } else {
                        this.currentPackage = null;
                    }

                    this.resetDeliveryForm();
                } else {
                    this.showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            } catch (error) {
                console.error('Erreur delivery:', error);
                let errorMessage = 'Erreur de connexion';
                if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                    errorMessage = 'Erreur réseau - Vérifiez votre connexion internet';
                } else if (error.message) {
                    errorMessage = `Erreur: ${error.message}`;
                }
                this.showToast(errorMessage, 'error');
            }

            this.processing = false;
        },

        resetDeliveryForm() {
            this.deliveryForm = {
                printReceipt: false,
                notes: '',
                codCollected: 0,
                recipientName: ''
            };
            this.selectedPackage = null;
        },

        markUnavailable(package) {
            this.selectedPackage = package;
            this.showUnavailableModal = true;
        },

        async submitUnavailable() {
            if (this.processing || !this.selectedPackage) return;

            // Validation côté client
            if (!this.unavailableForm.reason) {
                this.showToast('Veuillez sélectionner une raison', 'error');
                return;
            }

            if (this.unavailableForm.notes.length < 10) {
                this.showToast('Les détails doivent contenir au moins 10 caractères', 'error');
                return;
            }

            this.processing = true;

            try {
                const formData = new FormData();
                formData.append('reason', this.unavailableForm.reason);
                formData.append('attempt_notes', this.unavailableForm.notes);

                // Vérifier la présence du token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    this.showToast('Token CSRF manquant - Rechargez la page', 'error');
                    this.processing = false;
                    return;
                }

                // Ajouter le token CSRF aux données du formulaire
                formData.append('_token', csrfToken);

                const response = await fetch(`/deliverer/packages/${this.selectedPackage.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    let errorMessage = `Erreur ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.text();
                        try {
                            const jsonError = JSON.parse(errorData);
                            if (jsonError.message) {
                                errorMessage = jsonError.message;
                            } else if (jsonError.errors) {
                                errorMessage = Object.values(jsonError.errors).flat().join(', ');
                            }
                        } catch (e) {
                            if (errorData.length > 0 && errorData.length < 200) {
                                errorMessage += ': ' + errorData.substring(0, 100);
                            }
                        }
                    } catch (e) {
                        console.error('Could not read error response:', e);
                    }

                    this.showToast(errorMessage, 'error');
                    this.processing = false;
                    return;
                }

                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON, response was:', responseText.substring(0, 500));
                    this.showToast('Le serveur a retourné une réponse HTML au lieu de JSON', 'error');
                    this.processing = false;
                    return;
                }

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showUnavailableModal = false;

                    // Si c'était la dernière tentative, retirer de la liste
                    if (data.data.is_final_attempt) {
                        this.packages = this.packages.filter(p => p.id !== this.selectedPackage.id);
                        this.stats.total--;

                        // Ajuster l'index si nécessaire
                        if (this.currentIndex >= this.packages.length && this.packages.length > 0) {
                            this.currentIndex = this.packages.length - 1;
                        }

                        if (this.packages.length > 0) {
                            this.currentPackage = this.packages[this.currentIndex];
                        } else {
                            this.currentPackage = null;
                        }
                    } else {
                        // Mettre à jour le package
                        const packageIndex = this.packages.findIndex(p => p.id === this.selectedPackage.id);
                        if (packageIndex > -1) {
                            this.packages[packageIndex].delivery_attempts = data.data.attempt_count;
                            this.packages[packageIndex].status = data.data.status;
                            this.packages[packageIndex].unavailable_reason = this.unavailableForm.reason;
                            this.packages[packageIndex].unavailable_notes = this.unavailableForm.notes;

                            if (packageIndex === this.currentIndex) {
                                this.currentPackage = this.packages[packageIndex];
                            }
                        }
                    }

                    this.resetUnavailableForm();
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
                }
            } catch (error) {
                console.error('Erreur unavailable:', error);

                let errorMessage = 'Erreur de connexion';
                if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                    errorMessage = 'Erreur réseau - Vérifiez votre connexion internet';
                } else if (error.name === 'SyntaxError') {
                    errorMessage = 'Erreur de format de réponse du serveur';
                } else if (error.message) {
                    errorMessage = `Erreur: ${error.message}`;
                }

                this.showToast(errorMessage, 'error');
            }

            this.processing = false;
        },

        resetUnavailableForm() {
            this.unavailableForm = {
                reason: '',
                notes: ''
            };
            this.selectedPackage = null;
        },

        viewDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        // ==================== ACTIONS RAPIDES ====================

        callClient(package) {
            const phone = this.getRecipientPhone(package);
            if (phone && phone !== 'N/A') {
                // Nettoyer le numéro de téléphone pour l'appel
                const cleanPhone = phone.replace(/[^\d+]/g, '');
                window.location.href = `tel:${cleanPhone}`;
            } else {
                this.showToast('Numéro de téléphone non disponible', 'error');
            }
        },

        openNavigation(package) {
            const address = this.getRecipientAddress(package);
            if (!address || address === 'N/A') {
                this.showToast('Adresse non disponible', 'error');
                return;
            }

            // Combiner avec la délégation pour une recherche plus précise
            const delegation = package.delegation_to?.name || '';
            const fullAddress = delegation ? `${address}, ${delegation}, Tunisie` : `${address}, Tunisie`;

            const encodedAddress = encodeURIComponent(fullAddress);
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            if (isMobile) {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_system');
            } else {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
            }
        },

        // ==================== HELPERS ====================

        isUrgent(package) {
            return (package.delivery_attempts || 0) >= 3;
        },

        isHighValue(package) {
            return parseFloat(package.cod_amount) >= 100;
        },

        getPriorityBorderClass(package) {
            if (this.isUrgent(package)) return 'border-l-4 border-red-500 shadow-red-100';
            if (package.delivery_attempts > 0) return 'border-l-4 border-yellow-500 shadow-yellow-100';
            if (this.isHighValue(package)) return 'border-l-4 border-green-500 shadow-green-100';
            return 'border-l-4 border-orange-500';
        },

        getPriorityBackgroundClass(package) {
            if (this.isUrgent(package)) return 'bg-gradient-to-r from-red-50 to-pink-50';
            if (package.delivery_attempts > 0) return 'bg-gradient-to-r from-yellow-50 to-orange-50';
            if (this.isHighValue(package)) return 'bg-gradient-to-r from-green-50 to-emerald-50';
            return 'bg-gradient-to-r from-orange-50 to-amber-50';
        },

        getRecipientName(package) {
            if (typeof package.recipient_data === 'string') {
                try {
                    const data = JSON.parse(package.recipient_data);
                    return data.name || 'N/A';
                } catch (e) {
                    return 'N/A';
                }
            }
            return package.recipient_data?.name || 'N/A';
        },

        getRecipientPhone(package) {
            if (typeof package.recipient_data === 'string') {
                try {
                    const data = JSON.parse(package.recipient_data);
                    return data.phone || 'N/A';
                } catch (e) {
                    return 'N/A';
                }
            }
            return package.recipient_data?.phone || 'N/A';
        },

        getRecipientAddress(package) {
            if (typeof package.recipient_data === 'string') {
                try {
                    const data = JSON.parse(package.recipient_data);
                    return data.address || 'N/A';
                } catch (e) {
                    return 'N/A';
                }
            }
            return package.recipient_data?.address || 'N/A';
        },

        getSenderName(package) {
            if (typeof package.sender_data === 'string') {
                try {
                    const data = JSON.parse(package.sender_data);
                    return data.name || 'N/A';
                } catch (e) {
                    return 'N/A';
                }
            }
            return package.sender_data?.name || 'N/A';
        },

        getStatusLabel(status) {
            const labels = {
                'PICKED_UP': 'Collecté - Prêt livraison',
                'UNAVAILABLE': 'Nouvelle tentative'
            };
            return labels[status] || status;
        },

        getReasonLabel(reason) {
            const labels = {
                'CLIENT_ABSENT': 'Client absent du domicile',
                'ADDRESS_NOT_FOUND': 'Adresse introuvable',
                'CLIENT_REFUSES': 'Client refuse le colis',
                'PHONE_OFF': 'Téléphone éteint/injoignable',
                'OTHER': 'Autre raison'
            };
            return labels[reason] || reason;
        },

        // ==================== FORMATAGE ====================

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatCodShort(amount) {
            const cod = parseFloat(amount || 0);
            if (cod >= 1000) return Math.round(cod / 1000) + 'k';
            return Math.round(cod) + '';
        },

        // ==================== NOTIFICATIONS ====================

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ?
                'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' :
                'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';

            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}"/>
                    </svg>
                    <span class="font-medium">${message}</span>
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

<!-- Support pour les gestes de swipe (optionnel) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let startX = null;
    let startY = null;

    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });

    document.addEventListener('touchend', function(e) {
        if (!startX || !startY) return;

        let endX = e.changedTouches[0].clientX;
        let endY = e.changedTouches[0].clientY;

        let diffX = startX - endX;
        let diffY = startY - endY;

        // Vérifier que c'est un swipe horizontal et non vertical
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            const app = Alpine.$data(document.querySelector('[x-data]'));

            if (diffX > 0) {
                // Swipe vers la gauche = package suivant
                app.nextPackage();
            } else {
                // Swipe vers la droite = package précédent
                app.previousPackage();
            }
        }

        startX = null;
        startY = null;
    });
});
</script>

@endsection