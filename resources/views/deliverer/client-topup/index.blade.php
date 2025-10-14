@extends('layouts.deliverer-modern')

@section('title', 'Recharge Client')
@section('content')
<div class="px-4 pb-4" x-data="clientTopup()">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <div class="text-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">💳 Recharge Client</h1>
                <p class="text-gray-500">Recherchez et rechargez le solde d'un client</p>
            </div>
        </div>

        <!-- Recherche Client -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">🔍 Rechercher un Client</h2>
            
            <form @submit.prevent="searchClient" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email, Téléphone ou Numéro de compte
                    </label>
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Entrez l'email, téléphone ou ID du client"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           required>
                </div>

                <button type="submit" 
                        :disabled="searching"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50">
                    <span x-show="!searching">🔍 Rechercher</span>
                    <span x-show="searching">⏳ Recherche...</span>
                </button>
            </form>

            <!-- Message d'erreur -->
            <div x-show="errorMessage" 
                 x-text="errorMessage"
                 class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            </div>
        </div>

        <!-- Informations Client -->
        <div x-show="client" 
             x-transition
             class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">👤 Informations Client</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                    <span class="text-gray-600">Nom:</span>
                    <span class="font-semibold" x-text="client?.name"></span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                    <span class="text-gray-600">Email:</span>
                    <span class="font-semibold" x-text="client?.email"></span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                    <span class="text-gray-600">Téléphone:</span>
                    <span class="font-semibold" x-text="client?.phone"></span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-xl">
                    <span class="text-gray-600">Solde actuel:</span>
                    <span class="font-bold text-green-600 text-lg" x-text="client?.balance_formatted"></span>
                </div>
            </div>
        </div>

        <!-- Formulaire de Recharge -->
        <div x-show="client" 
             x-transition
             class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">💰 Ajouter un Montant</h2>
            
            <form @submit.prevent="addTopup" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Montant à ajouter (DT)
                    </label>
                    <input type="number" 
                           x-model="topupAmount"
                           step="0.001"
                           min="1"
                           max="10000"
                           placeholder="Entrez le montant"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg font-semibold"
                           required>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-sm text-blue-800">
                        <strong>ℹ️ Information:</strong> Le montant sera ajouté au solde principal du client ET à votre wallet en tant que commission.
                    </p>
                </div>

                <button type="submit" 
                        :disabled="processing"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold py-3 rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all disabled:opacity-50 text-lg">
                    <span x-show="!processing">✅ Confirmer la Recharge</span>
                    <span x-show="processing">⏳ Traitement...</span>
                </button>
            </form>
        </div>

        <!-- Message de succès -->
        <div x-show="successMessage" 
             x-transition
             class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
            <div class="text-4xl mb-3">✅</div>
            <p class="text-green-800 font-semibold text-lg" x-text="successMessage"></p>
            <button @click="reset" 
                    class="mt-4 px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">
                Nouvelle Recharge
            </button>
        </div>

        <!-- Lien vers l'historique -->
        <div class="text-center mt-6">
            <a href="{{ route('deliverer.client-topup.history') }}" 
               class="text-indigo-600 hover:text-indigo-700 font-medium">
                📋 Voir l'historique des recharges
            </a>
        </div>
    </div>
</div>

<script>
function clientTopup() {
    return {
        searchQuery: '',
        searching: false,
        client: null,
        topupAmount: '',
        processing: false,
        errorMessage: '',
        successMessage: '',

        async searchClient() {
            this.searching = true;
            this.errorMessage = '';
            this.client = null;

            try {
                const response = await fetch('{{ route("deliverer.client-topup.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        search: this.searchQuery
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.client = data.client;
                } else {
                    this.errorMessage = data.message || 'Client non trouvé';
                }
            } catch (error) {
                this.errorMessage = 'Erreur de connexion';
                console.error(error);
            } finally {
                this.searching = false;
            }
        },

        async addTopup() {
            if (!this.topupAmount || this.topupAmount < 1) {
                alert('Veuillez entrer un montant valide');
                return;
            }

            if (!confirm(`Confirmer la recharge de ${this.topupAmount} DT pour ${this.client.name} ?`)) {
                return;
            }

            this.processing = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("deliverer.client-topup.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        client_id: this.client.id,
                        amount: this.topupAmount
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.successMessage = data.message;
                    this.client = null;
                    this.topupAmount = '';
                } else {
                    this.errorMessage = data.message || 'Erreur lors de la recharge';
                }
            } catch (error) {
                this.errorMessage = 'Erreur de connexion';
                console.error(error);
            } finally {
                this.processing = false;
            }
        },

        reset() {
            this.searchQuery = '';
            this.client = null;
            this.topupAmount = '';
            this.successMessage = '';
            this.errorMessage = '';
        }
    }
}
</script>
@endsection
