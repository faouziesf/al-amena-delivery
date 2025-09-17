@extends('layouts.client')

@section('title', 'Recharger mon portefeuille')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header avec navigation -->
    <div class="flex items-center mb-6">
        <a href="{{ route('client.wallet.index') }}" 
           class="flex items-center text-gray-600 hover:text-gray-900 transition-colors mr-4">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Retour au portefeuille
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <!-- Titre et description -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">💳 Recharger mon portefeuille</h1>
            <p class="text-gray-600">Ajoutez des fonds à votre portefeuille pour créer de nouveaux colis</p>
        </div>

        <!-- Alertes -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Affichage du solde actuel -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-blue-900">💰 Solde actuel</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($user->wallet->balance, 3) }} DT</p>
                </div>
                <div class="text-sm text-blue-700">
                    @if($user->wallet->frozen_amount > 0)
                        <p>Montant gelé: {{ number_format($user->wallet->frozen_amount, 3) }} DT</p>
                    @endif
                    <p>Disponible: {{ number_format($user->wallet->balance - ($user->wallet->frozen_amount ?? 0), 3) }} DT</p>
                </div>
            </div>
        </div>

        <!-- Formulaire de rechargement -->
        <form action="{{ route('client.wallet.process.topup') }}" method="POST" 
              id="topupForm" class="space-y-6">
            @csrf

            <!-- Montant à recharger -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💵 Montant à ajouter</h3>
                
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Montant (en DT) *
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               step="0.001" 
                               min="10" 
                               max="1000" 
                               value="{{ old('amount') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-12"
                               placeholder="0.000" required>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 text-sm">DT</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Minimum: 10.000 DT • Maximum: 1000.000 DT
                    </p>
                </div>

                <!-- Boutons de montant rapide -->
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Montants populaires:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach([50, 100, 200, 500] as $quickAmount)
                            <button type="button" 
                                    onclick="setAmount({{ $quickAmount }})"
                                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                {{ $quickAmount }} DT
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Aperçu du nouveau solde -->
                <div class="mt-4 p-3 bg-green-50 rounded-lg hidden" id="balancePreview">
                    <p class="text-sm text-green-800">
                        <span class="font-medium">Nouveau solde après rechargement:</span>
                        <span id="newBalance" class="font-bold">{{ number_format($user->wallet->balance, 3) }} DT</span>
                    </p>
                </div>
            </div>

            <!-- Méthode de paiement -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Méthode de paiement</h3>
                
                <div class="space-y-4">
                    <!-- Carte bancaire -->
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="CARD" 
                                   class="mt-1 text-blue-600" 
                                   {{ old('payment_method', 'CARD') === 'CARD' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">Carte bancaire</span>
                                    <span class="ml-2 text-xs bg-green-100 text-green-600 px-2 py-1 rounded">Recommandé</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    Paiement instantané • Sécurisé • Visa, MasterCard acceptées
                                </p>
                                <div class="flex items-center mt-2">
                                    <img src="/images/visa.svg" alt="Visa" class="h-6 mr-2">
                                    <img src="/images/mastercard.svg" alt="MasterCard" class="h-6 mr-2">
                                    <span class="text-xs text-gray-500">SSL 256-bit</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Virement bancaire -->
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="BANK_TRANSFER" 
                                   class="mt-1 text-green-600"
                                   {{ old('payment_method') === 'BANK_TRANSFER' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">Virement bancaire</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    Délai: 24-48h • Frais: Gratuit • RIB fourni après validation
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Informations importantes -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-2">Informations importantes :</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Les paiements par carte sont traités instantanément</li>
                            <li>Les virements bancaires nécessitent une validation manuelle</li>
                            <li>Montant minimum: 10 DT • Montant maximum: 1000 DT</li>
                            <li>Aucun frais supplémentaire n'est appliqué</li>
                            <li>Les fonds sont immédiatement disponibles après validation</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit" 
                        id="submitBtn"
                        class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors font-medium">
                    💳 Procéder au rechargement
                </button>
                
                <a href="{{ route('client.wallet.index') }}" 
                   class="flex-1 sm:flex-none bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition-colors font-medium text-center">
                    Annuler
                </a>
            </div>
        </form>

        <!-- Historique des rechargements récents -->
        @php
            $recentTopups = $user->transactions()
                ->where('type', 'CREDIT')
                ->where('description', 'like', '%Rechargement%')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        @endphp

        @if($recentTopups->count() > 0)
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Rechargements récents</h3>
                <div class="space-y-3">
                    @foreach($recentTopups as $topup)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $topup->created_at->format('d/m/Y à H:i') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $topup->description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-green-600">
                                    +{{ number_format($topup->amount, 3) }} DT
                                </p>
                                <p class="text-xs text-gray-500">{{ $topup->status_display }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('topupForm');
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('submitBtn');
    const balancePreview = document.getElementById('balancePreview');
    const newBalanceSpan = document.getElementById('newBalance');
    const currentBalance = {{ $user->wallet->balance }};
    
    // Fonction pour définir un montant
    window.setAmount = function(amount) {
        amountInput.value = amount.toFixed(3);
        updateBalancePreview();
        validateForm();
    };
    
    // Mise à jour de l'aperçu du nouveau solde
    function updateBalancePreview() {
        const amount = parseFloat(amountInput.value) || 0;
        if (amount > 0) {
            const newBalance = currentBalance + amount;
            newBalanceSpan.textContent = newBalance.toFixed(3) + ' DT';
            balancePreview.classList.remove('hidden');
        } else {
            balancePreview.classList.add('hidden');
        }
    }
    
    // Validation du formulaire
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        
        let isValid = true;
        let errorMessage = '';
        
        if (amount < 10) {
            isValid = false;
            errorMessage = 'Le montant minimum est de 10 DT';
        } else if (amount > 1000) {
            isValid = false;
            errorMessage = 'Le montant maximum est de 1000 DT';
        } else if (!paymentMethod) {
            isValid = false;
            errorMessage = 'Veuillez sélectionner une méthode de paiement';
        }
        
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.className = submitBtn.className.replace('bg-gray-400', 'bg-blue-600').replace('cursor-not-allowed', '');
            submitBtn.innerHTML = '💳 Procéder au rechargement';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = submitBtn.className.replace('bg-blue-600', 'bg-gray-400') + ' cursor-not-allowed';
            submitBtn.title = errorMessage;
        }
        
        return isValid;
    }
    
    // Event listeners
    amountInput.addEventListener('input', function() {
        updateBalancePreview();
        validateForm();
    });
    
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', validateForm);
    });
    
    // Formatage du montant en temps réel
    amountInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(3);
            updateBalancePreview();
        }
    });
    
    // Prévention de la soumission multiple
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        submitBtn.disabled = true;
        if (paymentMethod === 'CARD') {
            submitBtn.innerHTML = '🔄 Redirection vers le paiement...';
        } else {
            submitBtn.innerHTML = '⏳ Traitement en cours...';
        }
        
        // Permettre la soumission après un délai en cas d'erreur
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '💳 Procéder au rechargement';
        }, 10000);
    });
    
    // Initialisation
    validateForm();
    updateBalancePreview();
});

// Animation d'entrée
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.bg-white');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        setTimeout(() => {
            section.style.transition = 'all 0.5s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, index * 150);
    });
});
</script>
@endpush
@endsection