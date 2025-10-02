@extends('layouts.deliverer')

@section('title', 'Détail de la Tâche')

@section('content')
<div x-data="taskDetailApp()" x-init="init()" class="h-full bg-gray-50">

    <!-- Header de la Tâche -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-8">
        <div class="flex items-center space-x-4 mb-4">
            <button @click="goBack()" class="text-white hover:text-blue-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="text-2xl" x-text="getTaskIcon()"></span>
                    <span class="text-lg font-bold" x-text="getTaskType()"></span>
                </div>
                <h1 class="text-xl font-bold" x-text="getTaskTitle()"></h1>
            </div>
        </div>
    </div>

    <!-- Informations de la Tâche -->
    <div class="px-6 py-6">

        <!-- Bloc d'Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">

            <!-- Adresse -->
            <div class="flex items-start space-x-4 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Adresse</h3>
                    <p class="text-gray-900 font-semibold leading-relaxed" x-text="task?.address"></p>
                </div>
            </div>

            <!-- Notes/Instructions -->
            <div x-show="task?.notes" class="flex items-start space-x-4 mb-6">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Instructions</h3>
                    <p class="text-gray-700 leading-relaxed" x-text="task?.notes"></p>
                </div>
            </div>

            <!-- COD pour livraisons -->
            <div x-show="task?.type === 'delivery' && task?.cod_amount > 0" class="flex items-start space-x-4 mb-6">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Montant à Collecter (COD)</h3>
                    <p class="text-2xl font-black text-green-600" x-text="formatAmount(task?.cod_amount)"></p>
                </div>
            </div>

            <!-- Nombre de colis pour collectes -->
            <div x-show="task?.type === 'pickup'" class="flex items-start space-x-4 mb-6">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Colis à Collecter</h3>
                    <p class="text-2xl font-black text-orange-600" x-text="task?.packages_count || 1"></p>
                </div>
            </div>

            <!-- Avertissement Échange -->
            <div x-show="task?.est_echange" class="flex items-start space-x-4 mb-6 p-4 bg-orange-50 border-2 border-orange-200 rounded-lg">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-orange-800 mb-2">⚠️ COLIS D'ÉCHANGE</h3>
                    <p class="text-orange-700 font-medium leading-relaxed">
                        Ce colis nécessite un échange. Vous devez <strong>récupérer l'ancien article</strong> lors de la livraison et le rapporter au dépôt.
                    </p>
                </div>
            </div>

            <!-- Bouton d'appel -->
            <div class="pt-4 border-t border-gray-200">
                <button @click="callClient()"
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-bold
                               hover:bg-blue-700 transition-colors
                               flex items-center justify-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>Appeler le client</span>
                </button>
            </div>
        </div>

        <!-- Portail d'Action Principal -->
        <div class="pb-24">

            <!-- Phase 1: Scanner Required -->
            <div x-show="!scanCompleted && !loading" class="bg-white rounded-xl shadow-sm border-2 border-blue-300 p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-3">Scanner Requis</h2>
                <p class="text-gray-600 mb-6">Scannez le QR code du colis pour débloquer les actions</p>

                <button @click="startScanner()"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 px-6 rounded-lg font-bold
                               hover:from-blue-700 hover:to-blue-800 transition-all
                               flex items-center justify-center space-x-3 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Scanner le colis</span>
                </button>
            </div>

            <!-- Phase 2: Actions Disponibles -->
            <div x-show="scanCompleted && !loading" class="space-y-4">

                <!-- Actions pour Livraison avec workflow COD -->
                <div x-show="task?.type === 'delivery'" class="space-y-6">
                    <!-- Bouton Principal LIVRÉ -->
                    <button @click="confirmDelivery()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-6 px-6 rounded-lg font-bold text-xl
                                   flex items-center justify-center space-x-3 relative transition-colors shadow-lg">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span>COLIS LIVRÉ</span>
                        <div x-show="task?.cod_amount > 0" class="absolute top-2 right-2 w-5 h-5 bg-yellow-400 rounded-full flex items-center justify-center">
                            <span class="text-xs text-black font-bold">!</span>
                        </div>
                    </button>

                    <!-- Actions Secondaires avec motifs -->
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="showUnavailableReasons()"
                                class="soft-button interactive py-6 px-6 text-white font-bold text-lg
                                       flex items-center justify-center space-x-3"
                                style="background: linear-gradient(135deg, var(--warning), #E67E22);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Indisponible</span>
                        </button>

                        <button @click="showCancelReasons()"
                                class="soft-button interactive py-6 px-6 text-white font-bold text-lg
                                       flex items-center justify-center space-x-3"
                                style="background: linear-gradient(135deg, var(--error), #C0392B);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Annuler</span>
                        </button>
                    </div>
                </div>

                <!-- Actions pour Pickup avec scan multiple -->
                <div x-show="task?.type === 'pickup'" class="space-y-6">
                    <!-- Zone de scan multiple -->
                    <div class="soft-card p-6">
                        <h3 class="text-title font-bold mb-4" style="color: var(--text-primary)">Scanner les colis à collecter</h3>

                        <!-- Bouton Scanner un colis -->
                        <button @click="scanPackage()"
                                class="w-full soft-button interactive py-6 px-6 text-white font-bold text-lg mb-6
                                       flex items-center justify-center space-x-3"
                                style="background: linear-gradient(135deg, var(--accent), var(--accent-light));">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>➕ SCANNER UN COLIS</span>
                        </button>

                        <!-- Liste des colis scannés -->
                        <div x-show="scannedPackages.length > 0">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-body font-bold" style="color: var(--text-secondary)">Colis scannés</h4>
                                <div class="px-3 py-1 rounded-full" style="background: var(--accent); color: white;">
                                    <span class="text-sm font-bold" x-text="scannedPackages.length + ' colis'"></span>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6 max-h-40 overflow-y-auto">
                                <template x-for="(pkg, index) in scannedPackages" :key="pkg.code">
                                    <div class="flex items-center justify-between p-3 rounded-xl" style="background: rgba(0, 171, 228, 0.1);">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center text-sm font-bold" x-text="index + 1"></div>
                                            <span class="font-mono font-bold" x-text="pkg.code"></span>
                                        </div>
                                        <button @click="removeScannedPackage(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton Valider (apparaît après premier scan) -->
                    <button x-show="scannedPackages.length > 0"
                            @click="validatePickup()"
                            class="w-full soft-button interactive py-8 px-8 text-white font-black text-2xl
                                   flex items-center justify-center space-x-4"
                            style="background: linear-gradient(135deg, #8E44AD, #9B59B6); box-shadow: var(--shadow-strong);">
                        <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span>VALIDER LE RAMASSAGE</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- État de chargement modernisé -->
        <div x-show="loading" class="soft-card p-12 text-center content-mobile-spacing">
            <div class="relative mx-auto mb-8 w-20 h-20">
                <div class="absolute inset-0 border-4 rounded-full animate-spin" style="border-color: var(--accent) transparent var(--accent) transparent;"></div>
                <div class="absolute inset-2 border-4 border-transparent rounded-full animate-spin" style="border-top-color: var(--accent-light); animation-direction: reverse; animation-duration: 1.5s;"></div>
            </div>
            <div class="space-y-2">
                <h3 class="text-title font-bold" style="color: var(--text-primary)">Traitement en cours</h3>
                <p class="text-body" style="color: var(--text-secondary)">Veuillez patienter...</p>
            </div>
        </div>

        <!-- Modal de Confirmation COD -->
        <div x-show="showCodConfirmation"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5); backdrop-filter: blur(8px);">

            <div class="soft-card p-8 max-w-md w-full scale-in">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Confirmation COD</h3>
                    <p class="text-body" style="color: var(--text-secondary)">Confirmer la réception des espèces ?</p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-6 text-center">
                    <div class="text-3xl font-black text-green-600 mb-2" x-text="formatAmount(task?.cod_amount)"></div>
                    <div class="text-sm font-medium text-green-700">Montant reçu en espèces</div>
                </div>

                <div class="flex space-x-4">
                    <button @click="showCodConfirmation = false"
                            class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg"
                            style="background: rgba(0,0,0,0.1); color: var(--text-secondary);">
                        Annuler
                    </button>
                    <button @click="confirmCodAndDeliver()"
                            class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg text-white"
                            style="background: var(--success);">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Sélection de Motifs -->
        <div x-show="showReasonModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-full"
             class="fixed inset-0 z-50 flex items-end justify-center p-4"
             style="background: rgba(0,0,0,0.5); backdrop-filter: blur(8px);">

            <div class="soft-card w-full max-w-md slide-up">
                <div class="p-6">
                    <div class="text-center mb-6">
                        <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Motif de l'échec</h3>
                        <p class="text-body" style="color: var(--text-secondary)">Sélectionnez le motif approprié</p>
                    </div>

                    <div class="space-y-3 mb-6">
                        <template x-for="reason in getReasonOptions()" :key="reason.value">
                            <button @click="selectedReason = reason.value"
                                    :class="selectedReason === reason.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                                    class="w-full p-4 rounded-2xl border-2 text-left transition-all">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                         :class="selectedReason === reason.value ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                        <div x-show="selectedReason === reason.value" class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                    <span class="font-medium" x-text="reason.label"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div class="flex space-x-4">
                        <button @click="closeReasonModal()"
                                class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg"
                                style="background: rgba(0,0,0,0.1); color: var(--text-secondary);">
                            Annuler
                        </button>
                        <button @click="confirmWithReason()"
                                :disabled="!selectedReason"
                                :class="selectedReason ? 'opacity-100' : 'opacity-50'"
                                class="flex-1 py-4 px-6 rounded-2xl font-bold text-lg text-white"
                                :style="reasonType === 'unavailable' ? 'background: var(--warning)' : 'background: var(--error)'">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function taskDetailApp() {
    return {
        task: null,
        loading: false,
        scanCompleted: false,
        scannedPackages: [],
        showCodConfirmation: false,
        showReasonModal: false,
        reasonType: '', // 'unavailable' ou 'cancelled'
        selectedReason: '',

        init() {
            this.loadTask();

            // Simule le délai du bottom sheet qui glisse depuis le bas
            setTimeout(() => {
                this.showBottomSheet = true;
            }, 800);
        },

        async loadTask() {
            const taskId = window.location.pathname.split('/').pop();

            try {
                // Simuler le chargement des données de la tâche
                // En réalité, vous feriez un appel API ici
                this.task = {
                    id: taskId,
                    type: 'delivery', // ou 'pickup'
                    client_name: 'Mohamed Salah',
                    address: '15 Rue Habib Bourguiba, Tunis Centre, 1001\nAppartement 4, 2ème étage',
                    notes: 'Appartement 4, 2ème étage. Sonnette de droite.',
                    cod_amount: 45.500,
                    packages_count: 1,
                    phone: '+216 20 123 456',
                    est_echange: true // Pour tester l'affichage de l'avertissement d'échange
                };

                // Simuler le scan déjà effectué pour les tests
                setTimeout(() => {
                    this.scanCompleted = true;
                }, 1000);
            } catch (error) {
                console.error('Erreur chargement tâche:', error);
            }
        },

        startScanner() {
            // Redirection vers le scanner
            window.location.href = '/deliverer/scan';
        },

        callClient() {
            if (this.task?.phone) {
                window.location.href = `tel:${this.task.phone}`;
            }
        },

        sendMessage() {
            if (this.task?.phone) {
                window.location.href = `sms:${this.task.phone}`;
            }
        },

        openMaps() {
            if (this.task?.address) {
                const address = encodeURIComponent(this.task.address);
                // Détection du type d'appareil pour ouvrir la bonne app
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                const isAndroid = /Android/.test(navigator.userAgent);

                if (isIOS) {
                    window.location.href = `maps://maps.google.com/maps?q=${address}`;
                } else if (isAndroid) {
                    window.location.href = `geo:0,0?q=${address}`;
                } else {
                    window.open(`https://maps.google.com?q=${address}`, '_blank');
                }
            }
        },

        getTaskStatus() {
            if (!this.task) return '';

            const statusMap = {
                'AVAILABLE': 'Disponible',
                'ACCEPTED': 'Acceptée',
                'PICKED_UP': this.task.type === 'pickup' ? 'Collectée' : 'Ramassée',
                'DELIVERED': 'Livrée',
                'UNAVAILABLE': 'Indisponible',
                'CANCELLED': 'Annulée',
                'REFUSED': 'Refusée'
            };
            return statusMap[this.task.status] || this.task.status;
        },

        async markDelivered() {
            this.loading = true;
            try {
                const response = await fetch(`/deliverer/deliver/${this.task.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    // Proposer l'impression du reçu après livraison
                    if (confirm('Colis livré avec succès! Voulez-vous imprimer le reçu de livraison?')) {
                        window.open(`/deliverer/print/receipt/${this.task.id}?autoprint=1`, '_blank');
                    }
                    // Redirection vers capture de signature
                    window.location.href = `/deliverer/signature/${this.task.id}`;
                } else {
                    alert(data.message || 'Erreur lors de la livraison');
                }
            } catch (error) {
                alert('Erreur de connexion');
            } finally {
                this.loading = false;
            }
        },

        async markUnavailable() {
            await this.updateTaskStatus('unavailable');
        },

        async markCancelled() {
            await this.updateTaskStatus('cancelled');
        },

        async markPickedUp() {
            await this.updateTaskStatus('pickup');
        },

        async updateTaskStatus(action) {
            this.loading = true;
            try {
                const response = await fetch(`/deliverer/${action}/${this.task.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.goBack();
                } else {
                    alert(data.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                alert('Erreur de connexion');
            } finally {
                this.loading = false;
            }
        },

        goBack() {
            window.history.back();
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        getTaskIcon() {
            return this.task?.type === 'delivery' ? '🚚' : '📦';
        },

        getTaskType() {
            return this.task?.type === 'delivery' ? 'LIVRAISON' : 'RAMASSAGE';
        },

        getTaskTitle() {
            const type = this.task?.type === 'delivery' ? 'Livraison chez' : 'Collecte chez';
            return `${type} ${this.task?.client_name || '...'}`;
        },

        // Nouveaux workflows
        confirmDelivery() {
            if (this.task?.cod_amount > 0) {
                // Si COD > 0, demander confirmation du montant
                this.showCodConfirmation = true;
            } else {
                // Si pas de COD, livrer directement
                this.markDelivered();
            }
        },

        confirmCodAndDeliver() {
            this.showCodConfirmation = false;
            this.markDelivered();
        },

        showUnavailableReasons() {
            this.reasonType = 'unavailable';
            this.selectedReason = '';
            this.showReasonModal = true;
        },

        showCancelReasons() {
            this.reasonType = 'cancelled';
            this.selectedReason = '';
            this.showReasonModal = true;
        },

        closeReasonModal() {
            this.showReasonModal = false;
            this.selectedReason = '';
            this.reasonType = '';
        },

        confirmWithReason() {
            if (!this.selectedReason) return;

            this.loading = true;
            this.showReasonModal = false;

            const action = this.reasonType === 'unavailable' ? 'unavailable' : 'cancelled';
            const reason = this.selectedReason;

            // Simuler l'API call avec le motif
            setTimeout(() => {
                showToast(`Tâche marquée comme ${action} : ${reason}`, this.reasonType === 'unavailable' ? 'success' : 'error');
                this.goBack();
            }, 1000);
        },

        getReasonOptions() {
            if (this.reasonType === 'unavailable') {
                return [
                    { value: 'client_absent', label: 'Client absent' },
                    { value: 'reporte_client', label: 'Reporté par le client' },
                    { value: 'adresse_incorrecte', label: 'Adresse incorrecte' },
                    { value: 'acces_impossible', label: 'Accès impossible' },
                    { value: 'autre', label: 'Autre motif' }
                ];
            } else {
                return [
                    { value: 'refuse_client', label: 'Refusé par le client' },
                    { value: 'colis_endommage', label: 'Colis endommagé' },
                    { value: 'erreur_commande', label: 'Erreur de commande' },
                    { value: 'probleme_paiement', label: 'Problème de paiement' },
                    { value: 'autre', label: 'Autre motif' }
                ];
            }
        },

        // Workflow pickup avec scan multiple
        scanPackage() {
            // Simulation du scan d'un nouveau colis
            const packageCode = 'AL' + new Date().getTime().toString().slice(-6);
            this.scannedPackages.push({
                code: packageCode,
                scannedAt: new Date()
            });

            showToast(`Colis ${packageCode} scanné !`, 'success');
        },

        removeScannedPackage(index) {
            this.scannedPackages.splice(index, 1);
            showToast('Colis retiré de la liste', 'success');
        },

        validatePickup() {
            if (this.scannedPackages.length === 0) {
                showToast('Aucun colis scanné', 'error');
                return;
            }

            this.loading = true;

            // Simuler la validation du pickup
            setTimeout(() => {
                showToast(`${this.scannedPackages.length} colis collectés avec succès !`, 'success');
                this.goBack();
            }, 1500);
        }
    }
}
</script>
@endpush
@endsection