@extends('layouts.deliverer')

@section('title', 'Recharge Client')

@section('content')
<div x-data="clientRechargeApp()" x-init="init()" class="h-full" style="background: var(--background)">

    <!-- Header avec Stepper -->
    <div class="relative overflow-hidden safe-top">
        <div class="absolute inset-0 bg-gradient-to-br from-green-600 via-green-700 to-emerald-800"></div>
        <div class="relative text-white px-6 py-8">
            <div class="fade-in">
                <!-- Header avec bouton retour -->
                <div class="flex items-center space-x-4 mb-6">
                    <button @click="goBack()" class="w-12 h-12 rounded-2xl glass flex items-center justify-center soft-button">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h1 class="text-headline font-bold">Recharge Client</h1>
                        <p class="text-body text-white/80">Créditer le compte prépayé d'un client</p>
                    </div>
                </div>

                <!-- Stepper Indicator -->
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                             :class="currentStep >= 1 ? 'bg-white text-green-600' : 'bg-white/20 text-white/60'">
                            1
                        </div>
                        <span class="text-sm font-medium" :class="currentStep >= 1 ? 'text-white' : 'text-white/60'">Client</span>
                    </div>

                    <div class="w-8 h-0.5 rounded-full" :class="currentStep >= 2 ? 'bg-white' : 'bg-white/20'"></div>

                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                             :class="currentStep >= 2 ? 'bg-white text-green-600' : 'bg-white/20 text-white/60'">
                            2
                        </div>
                        <span class="text-sm font-medium" :class="currentStep >= 2 ? 'text-white' : 'text-white/60'">Montant</span>
                    </div>

                    <div class="w-8 h-0.5 rounded-full" :class="currentStep >= 3 ? 'bg-white' : 'bg-white/20'"></div>

                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                             :class="currentStep >= 3 ? 'bg-white text-green-600' : 'bg-white/20 text-white/60'">
                            3
                        </div>
                        <span class="text-sm font-medium" :class="currentStep >= 3 ? 'text-white' : 'text-white/60'">Confirmation</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu des Étapes -->
    <div class="px-6 py-6 content-mobile-spacing">

        <!-- ÉTAPE 1: Identifier le Client -->
        <div x-show="currentStep === 1" class="space-y-6">
            <div class="soft-card p-8 fade-in">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-blue-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-title font-bold mb-3" style="color: var(--text-primary)">Identifier le Client</h2>
                    <p class="text-body" style="color: var(--text-secondary)">Recherchez le client par nom, téléphone ou code</p>
                </div>

                <!-- Barre de recherche -->
                <div class="mb-6">
                    <div class="relative">
                        <input x-model="searchQuery"
                               @input="searchClients()"
                               type="text"
                               placeholder="Nom, téléphone ou code client..."
                               class="w-full py-4 px-6 pl-14 rounded-2xl border-2 border-gray-200 focus:border-blue-500 focus:outline-none text-lg font-medium"
                               style="background: var(--surface);">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <svg class="w-6 h-6" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Résultats de recherche -->
                <div x-show="searchResults.length > 0" class="space-y-3">
                    <h3 class="text-body font-bold" style="color: var(--text-secondary)">Résultats de recherche</h3>
                    <template x-for="client in searchResults" :key="client.id">
                        <button @click="selectClient(client)"
                                class="w-full p-6 rounded-2xl border-2 border-gray-200 hover:border-blue-500 transition-all text-left soft-button interactive">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-blue-600" x-text="client.name.charAt(0)"></span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-title font-bold mb-1" style="color: var(--text-primary)" x-text="client.name"></h4>
                                    <p class="text-body" style="color: var(--text-secondary)" x-text="client.phone"></p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-caption px-3 py-1 rounded-full" style="background: var(--accent); color: white;" x-text="'Code: ' + client.code"></span>
                                        <span class="text-caption" style="color: var(--text-muted)" x-text="'Solde: ' + formatAmount(client.balance)"></span>
                                    </div>
                                </div>
                                <svg class="w-6 h-6" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- État vide -->
                <div x-show="searchQuery && searchResults.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-lg font-medium" style="color: var(--text-muted)">Aucun client trouvé</p>
                </div>
            </div>
        </div>

        <!-- ÉTAPE 2: Saisir le Montant -->
        <div x-show="currentStep === 2" class="space-y-6">
            <div class="soft-card p-8 fade-in">
                <!-- Client sélectionné -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-lg font-bold text-white" x-text="selectedClient?.name.charAt(0)"></span>
                        </div>
                        <div>
                            <h3 class="text-title font-bold text-blue-900" x-text="selectedClient?.name"></h3>
                            <p class="text-body text-blue-700" x-text="selectedClient?.phone"></p>
                            <p class="text-caption text-blue-600" x-text="'Solde actuel: ' + formatAmount(selectedClient?.balance)"></p>
                        </div>
                    </div>
                </div>

                <!-- Interface calculatrice -->
                <div class="text-center mb-8">
                    <h2 class="text-title font-bold mb-3" style="color: var(--text-primary)">Montant à créditer</h2>
                    <div class="text-6xl font-black mb-6" style="color: var(--success)" x-text="formatAmount(rechargeAmount)"></div>

                    <!-- Montants rapides -->
                    <div class="grid grid-cols-4 gap-3 mb-6">
                        <template x-for="amount in quickAmounts" :key="amount">
                            <button @click="setAmount(amount)"
                                    class="py-3 px-4 rounded-2xl font-bold text-lg soft-button interactive"
                                    style="background: rgba(46, 204, 113, 0.1); color: var(--success);">
                                <span x-text="amount + ' DT'"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Clavier numérique -->
                    <div class="grid grid-cols-3 gap-4">
                        <template x-for="num in [1,2,3,4,5,6,7,8,9]" :key="num">
                            <button @click="addDigit(num)"
                                    class="h-16 rounded-2xl font-bold text-2xl soft-button interactive"
                                    style="background: var(--surface); color: var(--text-primary); box-shadow: var(--shadow-soft);">
                                <span x-text="num"></span>
                            </button>
                        </template>

                        <button @click="clearAmount()"
                                class="h-16 rounded-2xl font-bold text-lg soft-button interactive"
                                style="background: var(--error); color: white;">
                            C
                        </button>

                        <button @click="addDigit(0)"
                                class="h-16 rounded-2xl font-bold text-2xl soft-button interactive"
                                style="background: var(--surface); color: var(--text-primary); box-shadow: var(--shadow-soft);">
                            0
                        </button>

                        <button @click="removeLastDigit()"
                                class="h-16 rounded-2xl font-bold text-lg soft-button interactive"
                                style="background: var(--warning); color: white;">
                            ⌫
                        </button>
                    </div>
                </div>

                <!-- Bouton Suivant -->
                <button @click="nextStep()"
                        :disabled="rechargeAmount <= 0"
                        :class="rechargeAmount > 0 ? 'opacity-100' : 'opacity-50'"
                        class="w-full py-6 px-8 rounded-2xl font-bold text-xl text-white soft-button interactive"
                        style="background: linear-gradient(135deg, var(--success), #27AE60);">
                    Continuer vers la confirmation
                </button>
            </div>
        </div>

        <!-- ÉTAPE 3: Confirmation & Signature -->
        <div x-show="currentStep === 3" class="space-y-6">
            <div class="soft-card p-8 fade-in">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-title font-bold mb-3" style="color: var(--text-primary)">Confirmation de Recharge</h2>
                    <p class="text-body" style="color: var(--text-secondary)">Vérifiez les détails avant de valider</p>
                </div>

                <!-- Récapitulatif -->
                <div class="space-y-4 mb-8">
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-body font-medium" style="color: var(--text-secondary)">Client</span>
                            <span class="text-body font-bold" style="color: var(--text-primary)" x-text="selectedClient?.name"></span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-body font-medium" style="color: var(--text-secondary)">Téléphone</span>
                            <span class="text-body font-bold" style="color: var(--text-primary)" x-text="selectedClient?.phone"></span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-body font-medium" style="color: var(--text-secondary)">Solde actuel</span>
                            <span class="text-body font-bold" style="color: var(--text-primary)" x-text="formatAmount(selectedClient?.balance)"></span>
                        </div>
                        <div class="flex justify-between items-center border-t pt-4">
                            <span class="text-title font-bold" style="color: var(--success)">Montant à créditer</span>
                            <span class="text-3xl font-black" style="color: var(--success)" x-text="formatAmount(rechargeAmount)"></span>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                        <div class="flex justify-between items-center">
                            <span class="text-title font-bold text-green-800">Nouveau solde</span>
                            <span class="text-3xl font-black text-green-600" x-text="formatAmount(selectedClient?.balance + rechargeAmount)"></span>
                        </div>
                    </div>
                </div>

                <!-- Zone de signature -->
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 mb-8 text-center">
                    <div class="mb-4">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <h3 class="text-body font-bold mb-2" style="color: var(--text-primary)">Signature du Client</h3>
                    <p class="text-caption" style="color: var(--text-muted)">Le client doit signer ici ou saisir son code PIN</p>

                    <div class="mt-4">
                        <input x-model="clientPin"
                               type="password"
                               placeholder="Code PIN du client (optionnel)"
                               class="w-full py-3 px-4 rounded-xl border border-gray-300 focus:border-blue-500 focus:outline-none text-center font-mono text-lg">
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="grid grid-cols-2 gap-4">
                    <button @click="previousStep()"
                            class="py-6 px-6 rounded-2xl font-bold text-lg soft-button interactive"
                            style="background: rgba(0,0,0,0.1); color: var(--text-secondary);">
                        Précédent
                    </button>
                    <button @click="confirmRecharge()"
                            :disabled="processing"
                            :class="processing ? 'opacity-50' : 'opacity-100'"
                            class="py-6 px-6 rounded-2xl font-bold text-lg text-white soft-button interactive"
                            style="background: linear-gradient(135deg, var(--success), #27AE60);">
                        <span x-show="!processing">Confirmer la Recharge</span>
                        <span x-show="processing">Traitement...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function clientRechargeApp() {
    return {
        currentStep: 1,
        searchQuery: '',
        searchResults: [],
        selectedClient: null,
        rechargeAmount: 0,
        clientPin: '',
        processing: false,
        quickAmounts: [10, 25, 50, 100],

        init() {
            // Initialisation
        },

        searchClients() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            // Simulation de recherche
            const mockClients = [
                { id: 1, name: 'Ahmed Ben Ali', phone: '+216 20 123 456', code: 'CLI001', balance: 45.750 },
                { id: 2, name: 'Fatma Sassi', phone: '+216 50 789 123', code: 'CLI002', balance: 123.250 },
                { id: 3, name: 'Mohamed Triki', phone: '+216 70 456 789', code: 'CLI003', balance: 0.000 },
                { id: 4, name: 'Leila Mansouri', phone: '+216 90 654 321', code: 'CLI004', balance: 75.500 }
            ];

            this.searchResults = mockClients.filter(client =>
                client.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                client.phone.includes(this.searchQuery) ||
                client.code.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectClient(client) {
            this.selectedClient = client;
            this.nextStep();
        },

        setAmount(amount) {
            this.rechargeAmount = amount;
        },

        addDigit(digit) {
            if (this.rechargeAmount === 0) {
                this.rechargeAmount = digit;
            } else {
                this.rechargeAmount = parseFloat(this.rechargeAmount.toString() + digit.toString());
            }
        },

        removeLastDigit() {
            const str = this.rechargeAmount.toString();
            if (str.length <= 1) {
                this.rechargeAmount = 0;
            } else {
                this.rechargeAmount = parseFloat(str.slice(0, -1));
            }
        },

        clearAmount() {
            this.rechargeAmount = 0;
        },

        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        async confirmRecharge() {
            this.processing = true;

            try {
                // Simulation de l'API call
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Succès
                showToast(`${this.formatAmount(this.rechargeAmount)} crédités sur le compte de ${this.selectedClient.name}`, 'success');

                // Retour à l'accueil après 1 seconde
                setTimeout(() => {
                    this.goBack();
                }, 1000);

            } catch (error) {
                showToast('Erreur lors de la recharge', 'error');
            } finally {
                this.processing = false;
            }
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        goBack() {
            window.history.back();
        }
    }
}
</script>
@endpush
@endsection