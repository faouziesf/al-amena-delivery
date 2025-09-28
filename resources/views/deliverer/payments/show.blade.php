@extends('layouts.deliverer')

@section('title', 'Détails Paiement')

@section('content')
<div class="bg-gray-50 min-h-screen" x-data="paymentDetailsApp()">

    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('deliverer.payments.index') }}"
                       class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $withdrawalRequest->request_code }}</h1>
                        <p class="text-sm text-gray-600">Détails du paiement client</p>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button @click="generateReceipt()"
                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </button>
                    <button @click="refreshData()"
                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 py-6 space-y-6">

        <!-- Payment Status -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-green-900">{{ number_format($withdrawalRequest->amount, 3) }} DT</h2>
                        <p class="text-sm text-green-700">À remettre en espèces</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center space-x-2 bg-green-100 px-4 py-2 rounded-full">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-bold text-green-800">{{ $withdrawalRequest->status_display }}</span>
                    </div>
                    <p class="text-xs text-green-600 mt-1">Paiement espèces</p>
                </div>
            </div>

            @if($withdrawalRequest->delivery_attempts > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-yellow-800">
                        {{ $withdrawalRequest->delivery_attempts }} tentative(s) de livraison
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- Client Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                <h3 class="text-lg font-semibold text-blue-900 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Informations Client</span>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nom</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $withdrawalRequest->client->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Téléphone</p>
                        <a href="tel:{{ $withdrawalRequest->client->phone }}"
                           class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                            {{ $withdrawalRequest->client->phone }}
                        </a>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-base text-gray-900">{{ $withdrawalRequest->client->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-amber-100">
                <h3 class="text-lg font-semibold text-amber-900 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Code de Livraison</span>
                </h3>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Code de réception</p>
                            <p class="text-2xl font-bold text-gray-900 font-mono">{{ $withdrawalRequest->delivery_receipt_code }}</p>
                            <p class="text-xs text-gray-500 mt-1">À scanner ou saisir lors de la livraison</p>
                        </div>
                        <button @click="copyCode('{{ $withdrawalRequest->delivery_receipt_code }}')"
                                class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors">
                            Copier
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-purple-100">
                <h3 class="text-lg font-semibold text-purple-900 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Traitement</span>
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Traité par</p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ $withdrawalRequest->processedByCommercial->name ?? 'Commercial' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date de traitement</p>
                        <p class="text-base text-gray-900">
                            {{ $withdrawalRequest->processed_at ? $withdrawalRequest->processed_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>

                @if($withdrawalRequest->processing_notes)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-blue-800">Notes du commercial:</p>
                    <p class="text-sm text-blue-700 mt-1">{{ $withdrawalRequest->processing_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Security Instructions -->
        <div class="bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-200 rounded-2xl p-6">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-red-900 mb-2">⚠️ Instructions de Sécurité</h4>
                    <ul class="text-sm text-red-800 space-y-2">
                        <li class="flex items-start space-x-2">
                            <span class="text-red-600 mt-1">•</span>
                            <span>Vérifier l'identité du client (CIN ou document officiel)</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-600 mt-1">•</span>
                            <span>Remettre exactement le montant de <strong>{{ number_format($withdrawalRequest->amount, 3) }} DT</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-600 mt-1">•</span>
                            <span>Demander la signature du client</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-600 mt-1">•</span>
                            <span>Prendre une photo de la remise (preuve de livraison)</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-600 mt-1">•</span>
                            <span>Scanner le code <strong>{{ $withdrawalRequest->delivery_receipt_code }}</strong> pour valider</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        @if(in_array($withdrawalRequest->status, ['IN_PROGRESS', 'APPROVED']))
        <!-- Action Buttons -->
        <div class="space-y-3">
            <button @click="markDelivered()"
                    :disabled="processing"
                    class="w-full bg-green-600 text-white py-4 px-6 rounded-xl font-semibold text-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!processing" class="flex items-center justify-center space-x-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Marquer comme Livré</span>
                </span>
                <span x-show="processing" class="flex items-center justify-center space-x-2">
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Traitement...</span>
                </span>
            </button>

            <button @click="markUnavailable()"
                    class="w-full bg-yellow-100 text-yellow-700 py-3 px-6 rounded-xl font-medium hover:bg-yellow-200 transition-colors">
                Client Non Disponible
            </button>
        </div>
        @endif
    </div>

    <!-- Delivery Modal -->
    <div x-show="showDeliveryModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Confirmer Livraison</h3>
                <button @click="showDeliveryModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Amount Confirmation -->
                <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl">
                    <p class="text-sm font-bold text-green-800 mb-2">MONTANT À REMETTRE:</p>
                    <p class="text-3xl font-bold text-green-900">{{ number_format($withdrawalRequest->amount, 3) }} DT</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Code de livraison *
                    </label>
                    <input type="text"
                           x-model="deliveryCode"
                           placeholder="{{ $withdrawalRequest->delivery_receipt_code }}"
                           class="w-full px-3 py-3 text-lg font-mono border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Scanner ou saisir le code du bon de livraison</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison</label>
                    <textarea x-model="deliveryNotes"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              rows="3"
                              placeholder="Identité vérifiée, montant remis..."></textarea>
                </div>

                <div class="flex space-x-3">
                    <button @click="confirmDelivery()"
                            :disabled="processingDelivery || !deliveryCode"
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison *</label>
                    <select x-model="unavailableReason"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Sélectionner une raison</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse le paiement</option>
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
</div>

<script>
function paymentDetailsApp() {
    return {
        processing: false,

        // Delivery modal
        showDeliveryModal: false,
        deliveryCode: '',
        deliveryNotes: '',
        processingDelivery: false,

        // Unavailable modal
        showUnavailableModal: false,
        unavailableReason: '',
        unavailableNotes: '',
        processingUnavailable: false,

        refreshData() {
            window.location.reload();
        },

        generateReceipt() {
            window.open('/deliverer/payments/{{ $withdrawalRequest->id }}/receipt', '_blank');
        },

        markDelivered() {
            this.deliveryCode = '{{ $withdrawalRequest->delivery_receipt_code }}';
            this.deliveryNotes = '';
            this.showDeliveryModal = true;
        },

        async confirmDelivery() {
            if (!this.deliveryCode) return;

            if (this.deliveryCode !== '{{ $withdrawalRequest->delivery_receipt_code }}') {
                this.showToast('Code de livraison incorrect', 'error');
                return;
            }

            this.processingDelivery = true;

            try {
                const formData = new FormData();
                formData.append('delivery_confirmation_code', this.deliveryCode);
                formData.append('delivery_notes', this.deliveryNotes);
                formData.append('client_signature', 'digital_signature');

                const response = await fetch('/deliverer/payments/{{ $withdrawalRequest->id }}/deliver', {
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

                    setTimeout(() => {
                        window.location.href = '/deliverer/payments';
                    }, 2000);
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur livraison paiement:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.processingDelivery = false;
        },

        markUnavailable() {
            this.unavailableReason = '';
            this.unavailableNotes = '';
            this.showUnavailableModal = true;
        },

        async confirmUnavailable() {
            if (!this.unavailableReason || !this.unavailableNotes) return;

            this.processingUnavailable = true;

            try {
                const response = await fetch('/deliverer/payments/{{ $withdrawalRequest->id }}/unavailable', {
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

                    setTimeout(() => {
                        window.location.href = '/deliverer/payments';
                    }, 2000);
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur tentative paiement:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.processingUnavailable = false;
        },

        copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                this.showToast('Code copié!');
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