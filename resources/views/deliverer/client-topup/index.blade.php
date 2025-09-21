@extends('layouts.deliverer')

@section('title', 'Recharge Client')

@section('page-title', 'Recharge Client')
@section('page-description', 'Ajouter des fonds aux wallets clients via paiement espèces')

@section('content')
<div x-data="clientTopupApp()" class="space-y-6">
    <!-- Stats du jour -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Recharges aujourd'hui</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_topups_today'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Montant aujourd'hui</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_amount_today'], 3) }} DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">En attente validation</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['pending_validation'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Clients aidés</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_clients_helped'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interface de recharge -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">💰 Nouvelle Recharge Client</h3>
            <p class="text-sm text-gray-500 mt-1">Recherchez le client et effectuez la recharge en espèces</p>
        </div>

        <div class="p-6">
            <form @submit.prevent="processTopup()">
                <!-- Étape 1: Recherche client -->
                <div class="space-y-4" x-show="step === 1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            📞 Numéro de téléphone du client
                        </label>
                        <div class="flex space-x-3">
                            <input 
                                type="tel" 
                                x-model="clientPhone"
                                @input="validatePhone()"
                                placeholder="Ex: 20123456"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                                maxlength="20"
                            >
                            <button 
                                type="button"
                                @click="searchClient()"
                                :disabled="!clientPhone || searching"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                            >
                                <svg x-show="!searching" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <svg x-show="searching" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                                </svg>
                                <span x-text="searching ? 'Recherche...' : 'Rechercher'"></span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Saisissez le numéro de téléphone du client</p>
                    </div>

                    <!-- Résultat de recherche -->
                    <div x-show="client" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-green-800" x-text="client?.name"></h4>
                                <p class="text-sm text-green-600">
                                    📞 <span x-text="client?.phone"></span> | 
                                    📧 <span x-text="client?.email"></span>
                                </p>
                                <p class="text-sm text-green-600">
                                    💰 Solde actuel: <span class="font-semibold" x-text="client?.wallet_balance + ' DT'"></span>
                                </p>
                                <p class="text-xs text-green-500 mt-1" x-show="client?.last_topup">
                                    Dernière recharge: <span x-text="client?.last_topup"></span>
                                </p>
                            </div>
                            <button 
                                type="button"
                                @click="step = 2"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                            >
                                Continuer →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Étape 2: Détails de recharge -->
                <div class="space-y-4" x-show="step === 2">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-blue-800" x-text="client?.name"></h4>
                                <p class="text-sm text-blue-600" x-text="client?.phone"></p>
                            </div>
                            <button 
                                type="button"
                                @click="step = 1; client = null"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                ← Changer client
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Montant -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                💰 Montant à recharger (DT)
                            </label>
                            <input 
                                type="number" 
                                x-model="amount"
                                step="0.001"
                                min="1"
                                max="1000"
                                placeholder="Ex: 50.000"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                            <p class="text-xs text-gray-500 mt-1">Minimum: 1 DT | Maximum: 1000 DT</p>
                        </div>

                        <!-- Nom pour vérification -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                👤 Nom du client (vérification)
                            </label>
                            <input 
                                type="text" 
                                x-model="clientName"
                                placeholder="Nom complet ou partiel"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                                maxlength="100"
                            >
                            <p class="text-xs text-gray-500 mt-1">Pour vérifier l'identité du client</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            📝 Notes (optionnel)
                        </label>
                        <textarea 
                            x-model="notes"
                            rows="3"
                            placeholder="Notes sur la recharge..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            maxlength="500"
                        ></textarea>
                    </div>

                    <!-- Photo du reçu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            📸 Photo du reçu (optionnel)
                        </label>
                        <input 
                            type="file" 
                            @change="receiptPhoto = $event.target.files[0]"
                            accept="image/*"
                            capture="environment"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Photographiez le reçu de paiement du client</p>
                    </div>

                    <!-- Résumé -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">📋 Résumé de la recharge</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span>Client:</span>
                                <span class="font-medium" x-text="client?.name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Montant:</span>
                                <span class="font-medium text-green-600" x-text="amount + ' DT'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Solde actuel:</span>
                                <span x-text="client?.wallet_balance + ' DT'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Nouveau solde:</span>
                                <span class="font-semibold text-green-600" x-text="(parseFloat(client?.wallet_balance || 0) + parseFloat(amount || 0)).toFixed(3) + ' DT'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex space-x-3">
                        <button 
                            type="button"
                            @click="step = 1"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                        >
                            ← Retour
                        </button>
                        <button 
                            type="submit"
                            :disabled="processing || !amount || !clientName"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                        >
                            <svg x-show="!processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            <svg x-show="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                            </svg>
                            <span x-text="processing ? 'Traitement...' : 'Effectuer la recharge'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historique récent -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">📋 Recharges récentes</h3>
                <p class="text-sm text-gray-500">Dernières recharges effectuées</p>
            </div>
            <a href="{{ route('deliverer.client-topup.history') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Voir tout →
            </a>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($recentTopups as $topup)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $topup->client->name }}</p>
                                <p class="text-sm text-gray-500">{{ $topup->client->phone }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-green-600">{{ number_format($topup->amount, 3) }} DT</p>
                            <p class="text-xs text-gray-500">{{ $topup->processed_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    <p class="text-lg font-medium">Aucune recharge effectuée</p>
                    <p class="text-sm">Effectuez votre première recharge client ci-dessus</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    function clientTopupApp() {
        return {
            step: 1,
            client: null,
            clientPhone: '',
            clientName: '',
            amount: '',
            notes: '',
            receiptPhoto: null,
            searching: false,
            processing: false,

            validatePhone() {
                // Nettoyage du numéro
                this.clientPhone = this.clientPhone.replace(/[^\d]/g, '');
            },

            async searchClient() {
                if (!this.clientPhone || this.searching) return;

                this.searching = true;
                this.client = null;

                try {
                    const response = await fetch('{{ route("deliverer.client-topup.search-client") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            phone: this.clientPhone
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.client = data.client;
                        this.showToast('Client trouvé !', 'success');
                    } else {
                        this.showToast(data.message || 'Client non trouvé', 'error');
                    }
                } catch (error) {
                    this.showToast('Erreur lors de la recherche', 'error');
                } finally {
                    this.searching = false;
                }
            },

            async processTopup() {
                if (this.processing || !this.client || !this.amount || !this.clientName) return;

                this.processing = true;

                try {
                    const formData = new FormData();
                    formData.append('client_phone', this.client.phone);
                    formData.append('amount', this.amount);
                    formData.append('payment_method', 'CASH');
                    formData.append('client_name', this.clientName);
                    formData.append('notes', this.notes);
                    
                    if (this.receiptPhoto) {
                        formData.append('receipt_photo', this.receiptPhoto);
                    }

                    const response = await fetch('{{ route("deliverer.client-topup.process") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.resetForm();
                        // Optionnel: Recharger la page ou mettre à jour les stats
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        this.showToast(data.message || 'Erreur lors de la recharge', 'error');
                    }
                } catch (error) {
                    this.showToast('Erreur lors de la recharge', 'error');
                } finally {
                    this.processing = false;
                }
            },

            resetForm() {
                this.step = 1;
                this.client = null;
                this.clientPhone = '';
                this.clientName = '';
                this.amount = '';
                this.notes = '';
                this.receiptPhoto = null;
            },

            showToast(message, type = 'success') {
                // Implementation simple de toast
                const toast = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }
        }
    }
</script>
@endpush