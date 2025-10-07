@extends('layouts.deliverer-modern')

@section('title', 'Recharge Client')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="rechargeApp()">
    
    <!-- Header -->
    <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white safe-top">
        <div class="px-6 py-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('deliverer.wallet') }}" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">💳 Recharge Client</h1>
                    <p class="text-purple-200 text-sm">Processus sécurisé en 3 étapes</p>
                </div>
            </div>

            <!-- Steps indicator -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                         :class="step >= 1 ? 'bg-white text-purple-600' : 'bg-white/20 text-white'">
                        1
                    </div>
                    <div class="flex-1 h-1 mx-2"
                         :class="step >= 2 ? 'bg-white' : 'bg-white/20'"></div>
                </div>
                <div class="flex-1 flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                         :class="step >= 2 ? 'bg-white text-purple-600' : 'bg-white/20 text-white'">
                        2
                    </div>
                    <div class="flex-1 h-1 mx-2"
                         :class="step >= 3 ? 'bg-white' : 'bg-white/20'"></div>
                </div>
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"
                     :class="step >= 3 ? 'bg-white text-purple-600' : 'bg-white/20 text-white'">
                    3
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 pb-32">
        
        <!-- Étape 1: Rechercher client -->
        <div x-show="step === 1" class="space-y-4 fade-in">
            <div class="card p-6">
                <h3 class="font-bold text-lg mb-2">Étape 1 : Rechercher le Client</h3>
                <p class="text-gray-600 text-sm mb-6">Entrez le numéro de téléphone du client</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de Téléphone</label>
                        <input type="tel" 
                               x-model="searchPhone"
                               @input="searchClient()"
                               placeholder="+216 XX XXX XXX"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               autofocus>
                    </div>

                    <!-- Résultats recherche -->
                    <div x-show="searchResults.length > 0" class="space-y-2">
                        <p class="text-sm text-gray-600">Résultats :</p>
                        <template x-for="client in searchResults" :key="client.id">
                            <button @click="selectClient(client)" 
                                    class="w-full text-left p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                                <div class="font-semibold text-gray-900" x-text="client.name"></div>
                                <div class="text-sm text-gray-600" x-text="client.phone"></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Solde actuel: <span class="font-semibold" x-text="formatMoney(client.balance)"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Aucun résultat -->
                    <div x-show="searchPhone.length >= 8 && searchResults.length === 0 && !searching" 
                         class="text-center py-6 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p>Aucun client trouvé</p>
                    </div>

                    <!-- Loading search -->
                    <div x-show="searching" class="text-center py-6">
                        <div class="spinner mx-auto mb-2"></div>
                        <p class="text-gray-600">Recherche...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 2: Saisir montant -->
        <div x-show="step === 2" class="space-y-4 fade-in">
            <div class="card p-6">
                <h3 class="font-bold text-lg mb-2">Étape 2 : Montant à Recharger</h3>
                <p class="text-gray-600 text-sm mb-6">Saisissez le montant en espèces</p>

                <!-- Client sélectionné -->
                <div class="bg-purple-50 p-4 rounded-xl mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900" x-text="selectedClient.name"></div>
                            <div class="text-sm text-gray-600" x-text="selectedClient.phone"></div>
                        </div>
                        <button @click="step = 1" class="text-purple-600 text-sm font-semibold">
                            Changer
                        </button>
                    </div>
                    <div class="mt-3 pt-3 border-t border-purple-200">
                        <div class="text-xs text-gray-600">Solde actuel</div>
                        <div class="text-lg font-bold text-purple-600" x-text="formatMoney(selectedClient.balance)"></div>
                    </div>
                </div>

                <!-- Saisie montant -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant (TND)</label>
                    <input type="number" 
                           x-model="amount"
                           placeholder="0.00"
                           step="0.01"
                           min="1"
                           class="w-full px-6 py-4 text-3xl font-bold border-2 border-purple-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center"
                           autofocus>
                </div>

                <!-- Montants rapides -->
                <div>
                    <p class="text-sm text-gray-600 mb-3">Montants rapides :</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="amount = 10" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            10 TND
                        </button>
                        <button @click="amount = 20" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            20 TND
                        </button>
                        <button @click="amount = 50" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            50 TND
                        </button>
                        <button @click="amount = 100" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            100 TND
                        </button>
                        <button @click="amount = 200" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            200 TND
                        </button>
                        <button @click="amount = 500" class="py-3 bg-gray-100 rounded-lg font-semibold hover:bg-purple-100 hover:text-purple-700">
                            500 TND
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 3: Confirmation et signature -->
        <div x-show="step === 3" class="space-y-4 fade-in">
            <div class="card p-6">
                <h3 class="font-bold text-lg mb-2">Étape 3 : Confirmation</h3>
                <p class="text-gray-600 text-sm mb-6">Signature du client requise</p>

                <!-- Récapitulatif -->
                <div class="bg-purple-50 p-4 rounded-xl mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-600">Client</div>
                            <div class="font-semibold" x-text="selectedClient.name"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Téléphone</div>
                            <div class="font-semibold" x-text="selectedClient.phone"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Montant</div>
                            <div class="text-2xl font-bold text-purple-600" x-text="formatMoney(amount)"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Nouveau solde</div>
                            <div class="text-lg font-bold text-green-600" x-text="formatMoney(selectedClient.balance + parseFloat(amount))"></div>
                        </div>
                    </div>
                </div>

                <!-- Zone de signature -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature du Client</label>
                    <div class="border-2 border-gray-300 rounded-xl bg-white relative" style="touch-action: none;">
                        <canvas id="signatureCanvas" 
                                class="w-full rounded-xl"
                                style="height: 200px;"
                                @touchstart="startDrawing($event)"
                                @touchmove="draw($event)"
                                @touchend="stopDrawing()"
                                @mousedown="startDrawing($event)"
                                @mousemove="draw($event)"
                                @mouseup="stopDrawing()"></canvas>
                        <button @click="clearSignature()" 
                                class="absolute top-2 right-2 px-3 py-1 bg-red-500 text-white rounded-lg text-xs">
                            Effacer
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Le client doit signer ici pour confirmer</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom z-20">
        <div class="flex space-x-3">
            <button x-show="step > 1" 
                    @click="step--" 
                    class="flex-1 btn bg-gray-200 text-gray-700 hover:bg-gray-300">
                ← Retour
            </button>
            
            <button x-show="step === 2" 
                    @click="goToConfirmation()"
                    :disabled="!amount || amount <= 0"
                    :class="!amount || amount <= 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700'"
                    class="flex-1 btn text-white">
                Suivant →
            </button>

            <button x-show="step === 3" 
                    @click="confirmRecharge()"
                    :disabled="processing || !hasSignature"
                    :class="processing || !hasSignature ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                    class="flex-1 btn text-white">
                <span x-show="!processing">✅ Confirmer la Recharge</span>
                <span x-show="processing">
                    <div class="spinner mx-auto" style="width: 20px; height: 20px; border-width: 2px;"></div>
                </span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function rechargeApp() {
    return {
        step: 1,
        searching: false,
        processing: false,
        searchPhone: '',
        searchResults: [],
        selectedClient: null,
        amount: null,
        hasSignature: false,
        canvas: null,
        ctx: null,
        drawing: false,

        init() {
            // Init canvas après le DOM
            setTimeout(() => {
                this.canvas = document.getElementById('signatureCanvas');
                if (this.canvas) {
                    this.ctx = this.canvas.getContext('2d');
                    this.canvas.width = this.canvas.offsetWidth;
                    this.canvas.height = 200;
                }
            }, 100);
        },

        async searchClient() {
            if (this.searchPhone.length < 8) {
                this.searchResults = [];
                return;
            }

            try {
                this.searching = true;
                
                const data = await apiRequest(`/deliverer/api/search/client?phone=${this.searchPhone}`);
                
                if (Array.isArray(data)) {
                    this.searchResults = data;
                } else if (data.client) {
                    this.searchResults = [data.client];
                }
            } catch (error) {
                console.error('Erreur recherche:', error);
                this.searchResults = [];
            } finally {
                this.searching = false;
            }
        },

        selectClient(client) {
            this.selectedClient = client;
            this.step = 2;
            vibrate([50]);
        },

        goToConfirmation() {
            if (!this.amount || this.amount <= 0) {
                showToast('Veuillez saisir un montant valide', 'warning');
                return;
            }

            this.step = 3;
            vibrate([50]);
        },

        startDrawing(e) {
            this.drawing = true;
            this.hasSignature = true;
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
            
            this.ctx.beginPath();
            this.ctx.moveTo(x, y);
        },

        draw(e) {
            if (!this.drawing) return;
            e.preventDefault();
            
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
            
            this.ctx.lineTo(x, y);
            this.ctx.strokeStyle = '#000';
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.stroke();
        },

        stopDrawing() {
            this.drawing = false;
        },

        clearSignature() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.hasSignature = false;
            vibrate([30]);
        },

        async confirmRecharge() {
            if (!this.hasSignature) {
                showToast('Signature du client requise', 'warning');
                return;
            }

            if (!confirm(`Confirmer la recharge de ${this.formatMoney(this.amount)} pour ${this.selectedClient.name} ?`)) {
                return;
            }

            try {
                this.processing = true;
                vibrate([50]);

                const signature = this.canvas.toDataURL();

                await apiRequest('/deliverer/api/recharge/client', {
                    method: 'POST',
                    body: {
                        client_id: this.selectedClient.id,
                        amount: this.amount,
                        signature: signature
                    }
                });

                showToast('Recharge effectuée avec succès !', 'success');
                vibrate([100, 50, 100]);

                setTimeout(() => {
                    window.location.href = '/deliverer/wallet';
                }, 2000);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de la recharge', 'error');
                this.processing = false;
            }
        },

        formatMoney(amount) {
            return (parseFloat(amount) || 0).toFixed(2) + ' TND';
        }
    }
}
</script>
@endpush

@endsection
