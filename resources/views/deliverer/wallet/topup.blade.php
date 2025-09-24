@extends('layouts.deliverer')

@section('title', 'Recharge Wallet')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">⬆️ Recharge Wallet</h1>
                    <p class="text-gray-600 mt-1">Ajouter des fonds à votre wallet personnel</p>
                </div>
                <a href="{{ route('deliverer.wallet.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="p-4 max-w-2xl mx-auto">
        <!-- Solde actuel -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Solde actuel</p>
                    <p class="text-3xl font-bold">{{ number_format($wallet->balance, 3) }} DT</p>
                </div>
                <div class="bg-white/20 p-3 rounded-lg">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stats recharges personnelles -->
        @if($personalTopupStats['total_personal_topups'] > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Vos recharges personnelles</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Total recharges</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $personalTopupStats['total_personal_topups'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Montant total</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($personalTopupStats['total_amount_topped_up'], 3) }} DT</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Formulaire de recharge -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold mb-6">Nouvelle recharge</h3>

            <form id="topupForm" onsubmit="submitTopup(event)">
                @csrf

                <!-- Montant -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant à recharger</label>
                    <div class="relative">
                        <input type="number" name="amount" id="amount" step="0.001" min="1" max="500"
                               placeholder="0.000" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <span class="absolute right-3 top-3 text-gray-500 font-medium">DT</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum: 1.000 DT • Maximum: 500.000 DT</p>

                    <!-- Montants suggérés -->
                    <div class="grid grid-cols-4 gap-2 mt-3">
                        <button type="button" onclick="setAmount(50)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium">
                            50 DT
                        </button>
                        <button type="button" onclick="setAmount(100)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium">
                            100 DT
                        </button>
                        <button type="button" onclick="setAmount(200)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium">
                            200 DT
                        </button>
                        <button type="button" onclick="setAmount(500)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium">
                            500 DT
                        </button>
                    </div>
                </div>

                <!-- Méthode de paiement -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Méthode de paiement</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="CASH" checked
                                   class="sr-only peer">
                            <div class="border-2 border-gray-200 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="flex items-center gap-3">
                                    <div class="bg-green-100 p-2 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium">Espèces</p>
                                        <p class="text-sm text-gray-600">Recharge immédiate</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="BANK_TRANSFER"
                                   class="sr-only peer">
                            <div class="border-2 border-gray-200 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium">Virement bancaire</p>
                                        <p class="text-sm text-gray-600">Validation required</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Référence (pour virement) -->
                <div class="mb-6" id="referenceField" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Référence virement</label>
                    <input type="text" name="reference" id="reference" maxlength="100"
                           placeholder="Référence ou numéro de transaction"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Numéro de transaction ou référence bancaire</p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                    <textarea name="notes" rows="3" maxlength="500"
                              placeholder="Raison de la recharge, commentaires..."
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Récapitulatif -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-medium mb-3">Récapitulatif</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Montant à recharger:</span>
                            <span id="recap-amount" class="font-medium">0.000 DT</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Frais:</span>
                            <span class="font-medium text-green-600">Gratuit</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-medium">
                            <span>Total:</span>
                            <span id="recap-total" class="text-lg">0.000 DT</span>
                        </div>
                        <div class="flex justify-between text-blue-600">
                            <span>Nouveau solde:</span>
                            <span id="recap-new-balance" class="font-medium">{{ number_format($wallet->balance, 3) }} DT</span>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex gap-4">
                    <button type="submit" id="submitBtn"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium disabled:opacity-50">
                        <span id="submitText">Recharger le wallet</span>
                        <span id="submitLoader" class="hidden">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>
                    <a href="{{ route('deliverer.wallet.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-6 rounded-lg font-medium">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Info importante -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-6">
            <div class="flex items-start gap-3">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="text-sm text-amber-800">
                    <p class="font-medium mb-1">Important :</p>
                    <ul class="space-y-1">
                        <li>• Les recharges en espèces sont créditées immédiatement</li>
                        <li>• Les virements bancaires nécessitent une validation (24-48h)</li>
                        <li>• Votre wallet doit correspondre aux espèces physiques que vous possédez</li>
                        <li>• Maximum 500 DT par recharge pour des raisons de sécurité</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const currentBalance = {{ $wallet->balance }};

// Gestion méthode de paiement
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const referenceField = document.getElementById('referenceField');
        if (this.value === 'BANK_TRANSFER') {
            referenceField.style.display = 'block';
            document.getElementById('reference').required = true;
        } else {
            referenceField.style.display = 'none';
            document.getElementById('reference').required = false;
        }
    });
});

// Mettre à jour le récapitulatif
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value) || 0;
    document.getElementById('recap-amount').textContent = amount.toFixed(3) + ' DT';
    document.getElementById('recap-total').textContent = amount.toFixed(3) + ' DT';
    document.getElementById('recap-new-balance').textContent = (currentBalance + amount).toFixed(3) + ' DT';
});

function setAmount(amount) {
    const input = document.getElementById('amount');
    input.value = amount;
    input.dispatchEvent(new Event('input'));
}

function submitTopup(event) {
    event.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    // Désactiver le bouton
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    submitLoader.classList.remove('hidden');

    const formData = new FormData(event.target);

    fetch('{{ route("deliverer.wallet.topup.process") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Succès
            showNotification('success', data.message);

            // Rediriger vers wallet index après 2 secondes
            setTimeout(() => {
                window.location.href = '{{ route("deliverer.wallet.index") }}';
            }, 2000);
        } else {
            // Erreur
            showNotification('error', data.message || 'Erreur lors de la recharge');

            // Réactiver le bouton
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoader.classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('error', 'Erreur de connexion. Veuillez réessayer.');

        // Réactiver le bouton
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        submitLoader.classList.add('hidden');
    });
}

function showNotification(type, message) {
    // Créer notification toast
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success'
            ? 'bg-green-500 text-white'
            : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                }
            </svg>
            <p class="font-medium">${message}</p>
        </div>
    `;

    document.body.appendChild(notification);

    // Supprimer après 5 secondes
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
@endpush
@endsection