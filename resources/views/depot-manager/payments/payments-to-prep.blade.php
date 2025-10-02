@extends('layouts.depot-manager')

@section('title', 'Paiements en Espèce à Préparer')
@section('page-title', 'Paiements à Préparer')
@section('page-description', 'Transformez les paiements en espèces en colis de livraison')

@section('content')
<div class="max-w-7xl mx-auto" x-data="paymentsToPrep()" x-init="init()">

    <!-- En-tête avec statistiques -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Paiements en Espèce à Préparer</h1>
                <p class="text-gray-600">Transformez ces paiements en colis pour livraison</p>
            </div>
            <div class="text-right">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-green-600" x-text="payments.length || 0"></div>
                    <div class="text-sm text-gray-500">À préparer</div>
                    <div class="text-xs text-blue-600 mt-1" x-text="'Debug: ' + (loading ? 'Chargement...' : 'Chargé')"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Explication du processus -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Comment ça marche ?</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    En cliquant sur "📦 Créer le Colis de Paiement", le système génère automatiquement un colis
                    avec les informations du client pré-remplies. Le montant du paiement devient le montant COD
                    (Contre-Remboursement). Une fois le colis créé, il entre dans votre flux normal de dispatching.
                </p>
            </div>
        </div>
    </div>

    <!-- Liste des paiements à préparer -->
    <div class="bg-white rounded-xl shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tous les Paiements en Espèces
                    <span class="text-sm font-normal text-gray-500">
                        (Priorité: Mon Gouvernorat)
                    </span>
                </h3>
                <div class="flex items-center space-x-3">
                    <!-- Filtre par statut -->
                    <select x-model="statusFilter" @change="filterPayments()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="PENDING">En attente</option>
                        <option value="APPROVED">Approuvé</option>
                        <option value="READY_FOR_DELIVERY">Prêt livraison</option>
                        <option value="DELIVERED">Livré</option>
                        <option value="COMPLETED">Complété</option>
                    </select>

                    <!-- Toggle priorité gouvernorat -->
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" x-model="showMyGouvernoratOnly" @change="filterPayments()"
                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Mon gouvernorat uniquement
                    </label>

                    <button @click="loadData()"
                            :disabled="loading"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code Paiement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse de Livraison</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant à Payer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="payment in payments" :key="payment.id">
                        <tr class="hover:bg-gray-50" :class="{ 'opacity-50': payment.processing }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="payment.request_code"></div>
                                    <div class="text-xs text-gray-500" x-text="payment.delivery_code"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="payment.client_name"></div>
                                    <div class="text-sm text-gray-500" x-text="payment.client_phone"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-xs" x-text="payment.client_address"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-green-600" x-text="payment.amount + ' DT'"></div>
                                <div class="text-xs text-gray-500">COD à percevoir</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusColorClass(payment.status_color)"
                                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      x-text="payment.status_display"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="payment.created_at"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <!-- Actions pour PENDING - Approuver/Rejeter -->
                                <div x-show="payment.status === 'PENDING'" class="flex flex-col space-y-2">
                                    <button @click="approvePayment(payment)"
                                            :disabled="loading || payment.processing"
                                            class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        ✅ Approuver
                                    </button>
                                    <button @click="rejectPayment(payment)"
                                            :disabled="loading || payment.processing"
                                            class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        ❌ Rejeter
                                    </button>
                                </div>

                                <!-- Actions pour APPROVED - Créer colis -->
                                <div x-show="payment.status === 'APPROVED'" class="flex flex-col space-y-2">
                                    <button @click="createPaymentPackage(payment)"
                                            :disabled="loading || payment.processing"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        <span x-show="!payment.processing" class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                                            </svg>
                                            📦 Créer Colis
                                        </span>
                                        <span x-show="payment.processing" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Création...
                                        </span>
                                    </button>
                                </div>

                                <!-- Actions pour READY_FOR_DELIVERY - Créer colis -->
                                <div x-show="payment.can_create_package && !payment.package_code && (payment.status === 'READY_FOR_DELIVERY' || payment.status === 'DELIVERED')">
                                    <button @click="createPaymentPackage(payment)"
                                            :disabled="loading || payment.processing"
                                            class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        <span x-show="!payment.processing" class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                                            </svg>
                                            📦 Créer Colis
                                        </span>
                                        <span x-show="payment.processing" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Création...
                                        </span>
                                    </button>
                                </div>

                                <!-- Affichage du colis créé -->
                                <div x-show="payment.package_code" class="text-center">
                                    <div class="text-sm font-medium text-green-600" x-text="payment.package_code"></div>
                                    <div class="text-xs text-gray-500">Colis créé</div>
                                </div>

                                <!-- Statut non actionnable -->
                                <div x-show="!payment.can_create_package && !payment.package_code && payment.status !== 'PENDING' && payment.status !== 'APPROVED'" class="text-center">
                                    <span class="text-xs text-gray-400">Traité</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- État vide -->
                    <tr x-show="payments.length === 0 && !loading">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement à préparer</h3>
                            <p class="text-gray-500">Tous les paiements en espèces ont été traités ou il n'y en a pas encore.</p>
                        </td>
                    </tr>

                    <!-- État de chargement -->
                    <tr x-show="loading">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Chargement des paiements...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div x-show="showConfirmModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.away="showConfirmModal = false"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Créer un Colis de Paiement
                            </h3>
                            <div class="mt-4" x-show="selectedPayment">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Récapitulatif</h4>
                                    <div class="text-sm text-gray-600 space-y-2">
                                        <div><span class="font-medium">Client:</span> <span x-text="selectedPayment?.client_name"></span></div>
                                        <div><span class="font-medium">Téléphone:</span> <span x-text="selectedPayment?.client_phone"></span></div>
                                        <div><span class="font-medium">Adresse:</span> <span x-text="selectedPayment?.client_address"></span></div>
                                        <div><span class="font-medium">Montant COD:</span> <span class="font-bold text-green-600" x-text="selectedPayment?.amount + ' DT'"></span></div>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-800">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Le colis sera créé avec une signature obligatoire et un contenu "Paiement Fournisseur".
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmCreatePackage()"
                            :disabled="!selectedPayment || loading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Créer le Colis</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Création...
                        </span>
                    </button>
                    <button @click="showConfirmModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<script>
function paymentsToPrep() {
    return {
        // État
        loading: false,
        payments: [],
        allPayments: [], // Stockage de tous les paiements

        // Filtres
        statusFilter: '',
        showMyGouvernoratOnly: false,

        // Modal
        showConfirmModal: false,
        selectedPayment: null,

        async init() {
            await this.loadData();
        },

        async loadData() {
            if (this.loading) return;

            this.loading = true;
            try {
                const response = await fetch('/depot-manager/api/payments/dashboard', {
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
                    this.allPayments = data.data.map(payment => ({
                        ...payment,
                        processing: false
                    }));
                    console.log('DEBUG: Paiements chargés:', this.allPayments.length, this.allPayments);
                    this.filterPayments(); // Appliquer les filtres
                    this.showToast(`${this.allPayments.length} paiement(s) chargé(s)`, 'info');
                } else {
                    this.showToast(data.message || 'Erreur de chargement', 'error');
                    console.error('DEBUG: Erreur API:', data);
                }
            } catch (error) {
                console.error('DEBUG: Erreur fetch loadData:', error);
                this.showToast('Erreur de connexion: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        filterPayments() {
            let filtered = [...this.allPayments];

            // Filtrer par statut
            if (this.statusFilter) {
                filtered = filtered.filter(payment => payment.status === this.statusFilter);
            }

            // Filtrer par gouvernorat (logique simplifiée côté client)
            if (this.showMyGouvernoratOnly) {
                // Les paiements prioritaires sont en début de liste, on prend les 20 premiers
                // Cette logique peut être améliorée selon les besoins spécifiques
                filtered = filtered.slice(0, Math.min(20, filtered.length));
            }

            this.payments = filtered;
        },

        createPaymentPackage(payment) {
            this.selectedPayment = payment;
            this.showConfirmModal = true;
        },

        async confirmCreatePackage() {
            if (this.loading || !this.selectedPayment) return;

            this.loading = true;
            this.selectedPayment.processing = true;

            try {
                const response = await fetch(`/depot-manager/api/payments/${this.selectedPayment.id}/create-package`, {
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
                    this.showToast(data.message + (data.package_code ? ` Code: ${data.package_code}` : ''), 'success');
                    this.showConfirmModal = false;
                    await this.loadData(); // Recharger pour enlever le paiement traité
                } else {
                    this.showToast(data.message || 'Erreur lors de la création du colis', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
                if (this.selectedPayment) {
                    this.selectedPayment.processing = false;
                }
            }
        },

        async approvePayment(payment) {
            if (this.loading || payment.processing) return;

            payment.processing = true;

            try {
                const response = await fetch(`/depot-manager/api/payments/${payment.id}/approve`, {
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
                    this.showToast(data.message || 'Paiement approuvé avec succès', 'success');
                    await this.loadData(); // Recharger les données
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'approbation', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                payment.processing = false;
            }
        },

        async rejectPayment(payment) {
            if (this.loading || payment.processing) return;

            // Demander la raison du rejet
            const reason = prompt('Veuillez indiquer la raison du rejet de cette demande de paiement :');
            if (!reason || reason.trim() === '') {
                return; // Annulé par l'utilisateur ou raison vide
            }

            payment.processing = true;

            try {
                const response = await fetch(`/depot-manager/api/payments/${payment.id}/reject`, {
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
                    this.showToast(data.message || 'Paiement rejeté avec succès', 'success');
                    await this.loadData(); // Recharger les données
                } else {
                    this.showToast(data.message || 'Erreur lors du rejet', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur de connexion', 'error');
            } finally {
                payment.processing = false;
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
        },

        getStatusColorClass(status_color) {
            const colorMap = {
                'yellow': 'bg-yellow-100 text-yellow-800',
                'blue': 'bg-blue-100 text-blue-800',
                'green': 'bg-green-100 text-green-800',
                'purple': 'bg-purple-100 text-purple-800',
                'indigo': 'bg-indigo-100 text-indigo-800',
                'red': 'bg-red-100 text-red-800',
                'gray': 'bg-gray-100 text-gray-800'
            };
            return colorMap[status_color] || 'bg-gray-100 text-gray-800';
        }
    };
}
</script>
@endpush