@extends('layouts.deliverer')

@section('title', 'Mes Pickups')

@section('content')
<div x-data="myPickupsApp()" x-init="init()">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Mes Pickups</h1>
                    <p class="text-sm text-gray-600" x-text="`${packages.length} colis acceptés à collecter`"></p>
                </div>
                
                <!-- Actions rapides -->
                <div class="flex items-center space-x-2">
                    <!-- Scanner -->
                    <button @click="openScanner()" 
                            class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>
                    
                    <!-- Scan par lot -->
                    <button @click="openBatchScanModal()" 
                            x-show="packages.length > 1"
                            class="bg-blue-500 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filtres et tri -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-x-auto">
                    <button @click="sortBy = 'date'; applySorting()" 
                            :class="sortBy === 'date' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        📅 Par Date
                    </button>
                    <button @click="sortBy = 'delegation'; applySorting()" 
                            :class="sortBy === 'delegation' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        📍 Par Zone
                    </button>
                    <button @click="sortBy = 'cod'; applySorting()" 
                            :class="sortBy === 'cod' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
                            class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors">
                        💰 Par COD
                    </button>
                </div>
                
                <div class="text-sm text-gray-500" x-text="`Total COD: ${totalCod.toFixed(3)} DT`"></div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
    </div>

    <!-- Liste des pickups -->
    <div x-show="!loading" class="space-y-3 px-4">
        <template x-for="package in sortedPackages" :key="package.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
                 :class="selectedPackages.includes(package.id) ? 'ring-2 ring-purple-500 bg-purple-50' : ''">
                
                <!-- Header avec sélection -->
                <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                    <div class="flex items-center space-x-3">
                        <!-- Checkbox pour sélection multiple -->
                        <input type="checkbox" :value="package.id" x-model="selectedPackages"
                               class="h-5 w-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        
                        <div>
                            <p class="font-bold text-gray-900" x-text="package.package_code"></p>
                            <p class="text-xs text-gray-500">
                                Accepté <span x-text="formatTimeAgo(package.assigned_at)"></span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Statut urgence -->
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 text-sm font-bold rounded-full"
                              :class="getUrgencyBadgeClass(package.assigned_at)"
                              x-text="getUrgencyText(package.assigned_at)">
                        </span>
                        <p class="text-lg font-bold text-purple-600 mt-1" x-text="formatAmount(package.cod_amount)"></p>
                    </div>
                </div>

                <!-- Détails pickup -->
                <div class="p-4">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Informations pickup -->
                        <div class="bg-orange-50 p-3 rounded-xl border-l-4 border-orange-400">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-orange-800">📦 Point de Collecte</h4>
                                <span class="text-xs text-orange-600" x-text="package.delegation_from?.name"></span>
                            </div>
                            <p class="font-medium text-gray-900" x-text="package.sender_data?.name || package.sender?.name"></p>
                            <p class="text-sm text-gray-600" x-text="package.sender_data?.phone"></p>
                            <p class="text-sm text-gray-700 mt-1" x-text="package.sender_data?.address || 'Adresse non spécifiée'"></p>
                        </div>

                        <!-- Destination -->
                        <div class="bg-green-50 p-3 rounded-xl border-l-4 border-green-400">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-green-800">🎯 Destination</h4>
                                <span class="text-xs text-green-600" x-text="package.delegation_to?.name"></span>
                            </div>
                            <p class="font-medium text-gray-900" x-text="package.recipient_data?.name"></p>
                            <p class="text-sm text-gray-600" x-text="package.recipient_data?.phone"></p>
                        </div>
                    </div>

                    <!-- Contenu et notes -->
                    <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Contenu:</span> 
                            <span x-text="package.content_description"></span>
                        </p>
                        <div x-show="package.notes" class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-600">
                                <span class="font-medium">Notes:</span> 
                                <span x-text="package.notes"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Actions principales -->
                    <div class="flex items-center space-x-3 mt-4">
                        <!-- Collecter (Pickup) -->
                        <button @click="markAsPickedUp(package)" 
                                :disabled="processing === package.id"
                                class="flex-1 bg-gradient-to-r from-emerald-500 to-green-500 text-white py-3 px-4 rounded-xl font-semibold hover:shadow-lg transition-all disabled:opacity-50">
                            <span x-show="processing !== package.id" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Collecter
                            </span>
                            <span x-show="processing === package.id" class="flex items-center justify-center">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                                Collecte...
                            </span>
                        </button>

                        <!-- Scanner -->
                        <button @click="scanSpecificPackage(package.package_code)" 
                                class="bg-blue-100 text-blue-600 p-3 rounded-xl hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                            </svg>
                        </button>

                        <!-- Détails -->
                        <button @click="viewDetails(package)" 
                                class="bg-gray-100 text-gray-600 p-3 rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Actions groupées -->
        <div x-show="selectedPackages.length > 0" 
             class="fixed bottom-24 left-4 right-4 bg-white rounded-2xl shadow-xl border border-gray-200 p-4 z-40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-900" x-text="`${selectedPackages.length} colis sélectionnés`"></p>
                    <p class="text-sm text-gray-600">Actions groupées disponibles</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="bulkPickup()" 
                            :disabled="processing"
                            class="bg-emerald-500 text-white px-4 py-2 rounded-xl font-medium hover:bg-emerald-600 transition-colors disabled:opacity-50">
                        <span x-show="!processing">Collecter Tous</span>
                        <span x-show="processing" class="flex items-center">
                            <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Collecte...
                        </span>
                    </button>
                    <button @click="selectedPackages = []" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-xl font-medium hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="packages.length === 0 && !loading" 
             class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun pickup en attente</h3>
            <p class="text-gray-600 mb-6">Vous n'avez aucun colis accepté à collecter.</p>
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="bg-purple-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-purple-600 transition-colors inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Rechercher des Pickups
            </a>
        </div>
    </div>
</div>

<!-- Modal Collecte avec Photo -->
<div x-show="showPickupModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
    <div x-show="showPickupModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
         x-transition:enter-end="translate-y-0 sm:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 sm:scale-100"
         x-transition:leave-end="translate-y-full sm:scale-95 sm:translate-y-0"
         class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-md max-h-96 overflow-y-auto">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Confirmer la Collecte</h3>
            <button @click="closePickupModal()" 
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="confirmPickup()">
            <!-- Colis info -->
            <div class="bg-purple-50 p-4 rounded-xl mb-4">
                <p class="font-semibold text-purple-900" x-text="selectedPickupPackage?.package_code"></p>
                <p class="text-sm text-purple-700" x-text="selectedPickupPackage?.sender_data?.name || selectedPickupPackage?.sender?.name"></p>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes de collecte (optionnel)</label>
                <textarea x-model="pickupForm.notes" rows="3" 
                          placeholder="État du colis, conditions particulières..."
                          class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
            </div>

            <!-- Photo -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Photo de collecte (optionnel)</label>
                <input type="file" x-ref="photoInput" @change="handlePhotoUpload" accept="image/*" capture="environment"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-purple-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                <p class="text-xs text-gray-500 mt-1">Recommandé pour preuve de collecte</p>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="submit" 
                        :disabled="processingPickup"
                        class="flex-1 bg-emerald-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                    <span x-show="!processingPickup">Confirmer Collecte</span>
                    <span x-show="processingPickup" class="flex items-center justify-center">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        Collecte...
                    </span>
                </button>
                <button type="button" @click="closePickupModal()" 
                        class="bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Scan par Lot -->
<div x-show="showBatchModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div x-show="showBatchModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 max-h-96 overflow-y-auto">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Scan par Lot</h3>
            <button @click="closeBatchModal()" 
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            <p class="text-sm text-gray-600">
                Collecte automatique de plusieurs colis en une fois. Scannez ou saisissez les codes un par un.
            </p>

            <!-- Zone de saisie -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Code colis</label>
                <div class="flex space-x-2">
                    <input type="text" x-model="batchCode" @keydown.enter="addToBatch()"
                           placeholder="PKG_12345678_20251219"
                           class="flex-1 border border-gray-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm uppercase">
                    <button @click="addToBatch()" :disabled="!batchCode.trim()"
                            class="bg-blue-500 text-white px-4 py-2 rounded-xl font-medium hover:bg-blue-600 transition-colors disabled:opacity-50">
                        Ajouter
                    </button>
                </div>
            </div>

            <!-- Liste des codes ajoutés -->
            <div x-show="batchCodes.length > 0" class="max-h-40 overflow-y-auto">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Codes ajoutés (<span x-text="batchCodes.length"></span>)
                </label>
                <div class="space-y-2">
                    <template x-for="(code, index) in batchCodes" :key="index">
                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                            <span class="font-mono text-sm" x-text="code"></span>
                            <button @click="removeFromBatch(index)" 
                                    class="text-red-500 hover:text-red-700 p-1 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button @click="processBatch()" 
                        :disabled="batchCodes.length === 0 || processingBatch"
                        class="flex-1 bg-emerald-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                    <span x-show="!processingBatch">Collecter Tous</span>
                    <span x-show="processingBatch" class="flex items-center justify-center">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                        Traitement...
                    </span>
                </button>
                <button @click="closeBatchModal()" 
                        class="bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function myPickupsApp() {
    return {
        packages: @json($packages->items()),
        sortedPackages: [],
        selectedPackages: [],
        loading: false,
        processing: null,
        sortBy: 'date',
        totalCod: 0,
        
        // Modal collecte - AJOUTÉ
        showPickupModal: false,
        selectedPickupPackage: null,
        processingPickup: false,
        pickupForm: {
            notes: '',
            photo: null
        },

        // Modal scan par lot - AJOUTÉ
        showBatchModal: false,
        batchCodes: [],
        batchCode: '',
        processingBatch: false,

        init() {
            this.applySorting();
            this.calculateTotalCod();
            this.startAutoRefresh();
        },

        applySorting() {
            let sorted = [...this.packages];
            
            switch (this.sortBy) {
                case 'delegation':
                    sorted.sort((a, b) => {
                        const delA = a.delegation_from?.name || '';
                        const delB = b.delegation_from?.name || '';
                        return delA.localeCompare(delB);
                    });
                    break;
                case 'cod':
                    sorted.sort((a, b) => b.cod_amount - a.cod_amount);
                    break;
                case 'date':
                default:
                    sorted.sort((a, b) => new Date(a.assigned_at) - new Date(b.assigned_at));
                    break;
            }
            
            this.sortedPackages = sorted;
        },

        calculateTotalCod() {
            this.totalCod = this.packages.reduce((sum, pkg) => sum + parseFloat(pkg.cod_amount || 0), 0);
        },

        // ==================== COLLECTE INDIVIDUELLE ====================

        async markAsPickedUp(package) {
            this.selectedPickupPackage = package;
            this.showPickupModal = true;
        },

        closePickupModal() {
            this.showPickupModal = false;
            this.resetPickupForm();
        },

        async confirmPickup() {
            if (!this.selectedPickupPackage) return;
            
            this.processingPickup = true;
            try {
                const formData = new FormData();
                formData.append('pickup_notes', this.pickupForm.notes);
                if (this.pickupForm.photo) {
                    formData.append('pickup_photo', this.pickupForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.selectedPickupPackage.id}/pickup`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    // Retirer le colis de la liste
                    this.packages = this.packages.filter(p => p.id !== this.selectedPickupPackage.id);
                    this.applySorting();
                    this.calculateTotalCod();
                    this.closePickupModal();
                    
                    // Rediriger si fourni
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    }
                } else {
                    this.showToast(data.message || 'Erreur lors de la collecte', 'error');
                }
            } catch (error) {
                console.error('Erreur collecte:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.processingPickup = false;
        },

        handlePhotoUpload(event) {
            this.pickupForm.photo = event.target.files[0];
        },

        resetPickupForm() {
            this.pickupForm = {
                notes: '',
                photo: null
            };
            this.selectedPickupPackage = null;
            // Reset file input
            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
        },

        // ==================== COLLECTE GROUPÉE ====================

        async bulkPickup() {
            if (this.selectedPackages.length === 0) return;
            
            this.processing = true;
            try {
                const codes = this.selectedPackages.map(id => {
                    const pkg = this.packages.find(p => p.id === id);
                    return pkg?.package_code;
                }).filter(Boolean);

                const response = await fetch('/deliverer/packages/scan-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        codes: codes,
                        action: 'pickup'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(`${data.summary.success} colis collectés avec succès`, 'success');
                    // Recharger la page pour actualiser
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors de la collecte groupée', 'error');
                }
            } catch (error) {
                console.error('Erreur collecte groupée:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.processing = false;
            this.selectedPackages = [];
        },

        // ==================== SCAN PAR LOT ====================

        openBatchScanModal() {
            this.showBatchModal = true;
            this.batchCodes = [];
            this.batchCode = '';
        },

        closeBatchModal() {
            this.showBatchModal = false;
            this.batchCodes = [];
            this.batchCode = '';
        },

        addToBatch() {
            const code = this.batchCode.trim().toUpperCase();
            if (code && !this.batchCodes.includes(code)) {
                this.batchCodes.push(code);
                this.batchCode = '';
            }
        },

        removeFromBatch(index) {
            this.batchCodes.splice(index, 1);
        },

        async processBatch() {
            if (this.batchCodes.length === 0) return;
            
            this.processingBatch = true;
            try {
                const response = await fetch('/deliverer/packages/scan-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        codes: this.batchCodes,
                        action: 'pickup'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(`${data.summary.success} colis traités avec succès`, 'success');
                    this.closeBatchModal();
                    // Recharger après un court délai
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors du traitement par lot', 'error');
                    
                    // Afficher les détails des échecs si disponibles
                    if (data.results) {
                        const failures = data.results.filter(r => !r.success);
                        failures.forEach(f => {
                            this.showToast(`${f.code}: ${f.message}`, 'error');
                        });
                    }
                }
            } catch (error) {
                console.error('Erreur traitement par lot:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            this.processingBatch = false;
        },

        // ==================== AUTRES ACTIONS ====================

        scanSpecificPackage(packageCode) {
            // Déclencher le scanner global avec pré-remplissage du code
            window.dispatchEvent(new CustomEvent('open-scanner', {
                detail: { prefilledCode: packageCode }
            }));
        },

        openScanner() {
            window.dispatchEvent(new Event('open-scanner'));
        },

        viewDetails(package) {
            window.location.href = `/deliverer/packages/${package.id}`;
        },

        // ==================== UTILITAIRES ====================

        startAutoRefresh() {
            setInterval(() => {
                if (!this.loading && !this.processingPickup && !this.showPickupModal) {
                    this.loadPackages();
                }
            }, 120000); // Actualisation toutes les 2 minutes
        },

        async loadPackages() {
            try {
                const response = await fetch('{{ route("deliverer.pickups.mine") }}?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                    this.applySorting();
                    this.calculateTotalCod();
                }
            } catch (error) {
                console.error('Erreur actualisation:', error);
            }
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) return `il y a ${diffInMinutes}min`;
            if (diffInMinutes < 1440) return `il y a ${Math.floor(diffInMinutes / 60)}h`;
            return `il y a ${Math.floor(diffInMinutes / 1440)}j`;
        },

        getUrgencyBadgeClass(assignedAt) {
            const hoursAgo = (new Date() - new Date(assignedAt)) / (1000 * 60 * 60);
            if (hoursAgo > 24) return 'bg-red-100 text-red-700';
            if (hoursAgo > 4) return 'bg-orange-100 text-orange-700';
            return 'bg-green-100 text-green-700';
        },

        getUrgencyText(assignedAt) {
            const hoursAgo = (new Date() - new Date(assignedAt)) / (1000 * 60 * 60);
            if (hoursAgo > 24) return 'Urgent';
            if (hoursAgo > 4) return 'À collecter';
            return 'Récent';
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
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
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection