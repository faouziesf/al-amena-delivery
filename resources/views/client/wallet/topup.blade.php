@extends('layouts.client')

@section('title', 'Recharger mon portefeuille')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header avec navigation -->
        <div class="flex items-center mb-6">
            <a href="{{ route('client.wallet.index') }}" 
               class="flex items-center text-purple-600 hover:text-purple-800 transition-all duration-300 mr-6 group">
                <div class="bg-purple-100 rounded-full p-2 mr-3 group-hover:bg-purple-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </div>
                <span class="font-medium">Retour au portefeuille</span>
            </a>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Titre principal -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent mb-3">
                    💳 Recharger mon portefeuille
                </h1>
                <p class="text-gray-600 text-lg">Ajoutez des fonds à votre portefeuille pour créer de nouveaux colis</p>
            </div>

            <!-- Alertes -->
            @if(session('success'))
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex">
                        <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Affichage du solde actuel -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl p-6 mb-8 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-emerald-100 mb-2">💰 Solde actuel</h3>
                        <p class="text-4xl font-bold" id="currentBalance">{{ number_format($user->wallet->balance, 3) }} DT</p>
                    </div>
                    <div class="text-right text-emerald-100">
                        @if($user->wallet->frozen_amount > 0)
                            <p class="text-sm">Montant gelé: {{ number_format($user->wallet->frozen_amount, 3) }} DT</p>
                        @endif
                        <p class="text-sm">Disponible: {{ number_format($user->wallet->balance - ($user->wallet->frozen_amount ?? 0), 3) }} DT</p>
                    </div>
                </div>
                
                <!-- Simulateur de nouveau solde -->
                <div id="newBalancePreview" class="hidden mt-4 p-3 bg-white/20 rounded-lg">
                    <p class="text-emerald-100 text-sm">Nouveau solde après rechargement:</p>
                    <p class="text-2xl font-bold" id="newBalanceAmount">0.000 DT</p>
                </div>
            </div>

            <!-- Formulaire de rechargement -->
            <form action="{{ route('client.wallet.process.topup') }}" method="POST" 
                  id="topupForm" class="space-y-8">
                @csrf

                <!-- Montant à recharger -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Montant à ajouter</h3>
                    </div>
                    
                    <div class="space-y-4">
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
                                       class="block w-full pl-4 pr-16 py-4 text-lg rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-50"
                                       placeholder="0.000" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-500 text-sm font-medium">DT</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Minimum: <span class="font-semibold text-emerald-600">10.000 DT</span> • 
                                Maximum: <span class="font-semibold text-emerald-600">1000.000 DT</span>
                            </p>
                        </div>

                        <!-- Boutons de montant rapide -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-3">Montants populaires:</p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach([50, 100, 200, 500] as $quickAmount)
                                    <button type="button" 
                                            onclick="setAmount({{ $quickAmount }})"
                                            class="px-4 py-3 text-sm bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-700 rounded-xl hover:from-emerald-200 hover:to-teal-200 transition-all duration-300 transform hover:scale-105 font-semibold border-2 border-transparent hover:border-emerald-300">
                                        {{ $quickAmount }} DT
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Aperçu des frais -->
                        <div id="feePreview" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-blue-800">Montant:</span>
                                <span id="previewAmount" class="font-bold text-blue-900">0.000 DT</span>
                            </div>
                            <div class="flex justify-between items-center text-sm mt-1">
                                <span class="text-blue-800">Frais:</span>
                                <span class="font-bold text-blue-900">Gratuit 🎉</span>
                            </div>
                            <hr class="my-2 border-blue-200">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-blue-800 font-semibold">Total à payer:</span>
                                <span id="totalAmount" class="font-bold text-blue-900 text-lg">0.000 DT</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Méthode de paiement -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Méthode de paiement</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Carte bancaire -->
                        <div class="relative">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="CARD" 
                                   id="card_payment"
                                   class="sr-only peer" 
                                   {{ old('payment_method', 'CARD') === 'CARD' ? 'checked' : '' }}>
                            <label for="card_payment" 
                                   class="flex items-start p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-gradient-to-br peer-checked:from-blue-50 peer-checked:to-indigo-50 transition-all duration-300">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 relative">
                                        <div class="w-2 h-2 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden peer-checked:block"></div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-900">Carte bancaire</span>
                                        <span class="ml-3 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Recommandé</span>
                                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">Instantané</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Paiement sécurisé par notre partenaire de confiance
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Instantané
                                        </span>
                                        <span class="flex items-center text-blue-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 10-9 9m-9-9a9 9 0 019 9 9 9 0 01-9-9"/>
                                            </svg>
                                            Sécurisé SSL
                                        </span>
                                        <span class="flex items-center text-purple-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Visa, MasterCard
                                        </span>
                                    </div>
                                    
                                    <!-- Logos des cartes acceptées -->
                                    <div class="mt-3 flex items-center space-x-2">
                                        <div class="bg-white rounded border px-2 py-1 text-xs font-bold text-blue-600">VISA</div>
                                        <div class="bg-white rounded border px-2 py-1 text-xs font-bold text-orange-600">MC</div>
                                        <div class="bg-white rounded border px-2 py-1 text-xs font-bold text-green-600">🔒 SSL</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Virement bancaire -->
                        <div class="relative">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="BANK_TRANSFER" 
                                   id="bank_transfer"
                                   class="sr-only peer"
                                   {{ old('payment_method') === 'BANK_TRANSFER' ? 'checked' : '' }}>
                            <label for="bank_transfer" 
                                   class="flex items-start p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-gradient-to-br peer-checked:from-green-50 peer-checked:to-emerald-50 transition-all duration-300">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 relative">
                                        <div class="w-2 h-2 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden peer-checked:block"></div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-900">Virement bancaire</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Virement direct depuis votre compte bancaire
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-amber-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            24-48h ouvrables
                                        </span>
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Frais: Gratuit
                                        </span>
                                        <span class="flex items-center text-blue-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            RIB fourni après validation
                        </span>
                    </div>
                </div>
            </label>
        </div>
    </div>
</div>

<!-- Informations importantes -->
<div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-l-4 border-amber-400 rounded-xl p-6 mb-8">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="ml-4">
            <h4 class="text-lg font-semibold text-amber-800 mb-3">💡 Informations importantes</h4>
            <ul class="text-amber-700 space-y-2">
                <li class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Les paiements par carte sont traités instantanément et les fonds sont immédiatement disponibles</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Les virements bancaires nécessitent une validation manuelle (24-48h ouvrables)</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Aucun frais supplémentaire n'est appliqué, quel que soit le montant ou la méthode</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 10-9 9m-9-9a9 9 0 019 9 9 9 0 01-9-9"/>
                    </svg>
                    <span>Toutes les transactions sont sécurisées et protégées par cryptage SSL</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                    </svg>
                    <span>Vous recevrez une confirmation par email dès que votre rechargement sera validé</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Boutons d'action -->
<div class="flex flex-col sm:flex-row gap-4 pt-6">
    <button type="submit" 
            id="submitBtn"
            class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-8 py-4 rounded-xl hover:from-emerald-600 hover:to-teal-700 focus:ring-4 focus:ring-emerald-200 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
        <span class="flex items-center justify-center">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Procéder au rechargement
        </span>
    </button>
    
    <a href="{{ route('client.wallet.index') }}" 
       class="flex-none sm:w-auto bg-white text-purple-600 border-2 border-purple-200 px-8 py-4 rounded-xl hover:bg-purple-50 transition-all duration-300 font-semibold text-lg text-center shadow-md hover:shadow-lg transform hover:scale-105">
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
    <div class="mt-12 bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
        <div class="flex items-center mb-6">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full p-3 mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800">Rechargements récents</h3>
        </div>
        
        <div class="space-y-3">
            @foreach($recentTopups as $topup)
                <div class="flex items-center justify-between py-3 px-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-emerald-100 to-green-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $topup->created_at->format('d/m/Y à H:i') }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $topup->description }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-600">
                            +{{ number_format($topup->amount, 3) }} DT
                        </p>
                        <p class="text-xs {{ $topup->status === 'COMPLETED' ? 'text-green-600' : 'text-amber-600' }}">
                            {{ $topup->status_display }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4 text-center">
            <a href="{{ route('client.wallet.transactions') }}" 
               class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                Voir tous mes rechargements →
            </a>
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
    const feePreview = document.getElementById('feePreview');
    const previewAmount = document.getElementById('previewAmount');
    const totalAmount = document.getElementById('totalAmount');
    const newBalancePreview = document.getElementById('newBalancePreview');
    const newBalanceAmount = document.getElementById('newBalanceAmount');
    const currentBalanceElement = document.getElementById('currentBalance');
    const methodRadios = document.querySelectorAll('input[name="payment_method"]');
    
    const currentBalance = {{ $user->wallet->balance }};
    
    // Fonction pour définir un montant
    window.setAmount = function(amount) {
        amountInput.value = amount.toFixed(3);
        updatePreviews();
        validateForm();
        
        // Animation du champ montant
        amountInput.style.transform = 'scale(1.05)';
        amountInput.style.borderColor = '#10b981';
        setTimeout(() => {
            amountInput.style.transform = 'scale(1)';
            amountInput.style.borderColor = '';
        }, 300);
    };
    
    // Mise à jour des aperçus
    function updatePreviews() {
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount > 0) {
            // Aperçu des frais
            previewAmount.textContent = amount.toFixed(3) + ' DT';
            totalAmount.textContent = amount.toFixed(3) + ' DT'; // Pas de frais
            feePreview.classList.remove('hidden');
            
            // Aperçu du nouveau solde
            const newBalance = currentBalance + amount;
            newBalanceAmount.textContent = newBalance.toFixed(3) + ' DT';
            newBalancePreview.classList.remove('hidden');
            
            // Animation d'apparition
            feePreview.style.opacity = '0';
            newBalancePreview.style.opacity = '0';
            setTimeout(() => {
                feePreview.style.transition = 'opacity 0.3s ease';
                newBalancePreview.style.transition = 'opacity 0.3s ease';
                feePreview.style.opacity = '1';
                newBalancePreview.style.opacity = '1';
            }, 100);
        } else {
            feePreview.classList.add('hidden');
            newBalancePreview.classList.add('hidden');
        }
    }
    
    // Validation du formulaire avec feedback visuel
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
        
        let isValid = true;
        let errorMessage = '';
        
        // Validation du montant
        if (amount <= 0) {
            isValid = false;
            errorMessage = 'Le montant doit être supérieur à zéro';
            amountInput.classList.add('border-red-500', 'bg-red-50');
        } else if (amount < 10) {
            isValid = false;
            errorMessage = 'Le montant minimum est de 10 DT';
            amountInput.classList.add('border-red-500', 'bg-red-50');
        } else if (amount > 1000) {
            isValid = false;
            errorMessage = 'Le montant maximum est de 1000 DT';
            amountInput.classList.add('border-red-500', 'bg-red-50');
        } else {
            amountInput.classList.remove('border-red-500', 'bg-red-50');
            amountInput.classList.add('border-emerald-500', 'bg-green-50');
        }
        
        // Validation de la méthode
        if (!selectedMethod) {
            isValid = false;
            errorMessage = errorMessage || 'Veuillez sélectionner une méthode de paiement';
        }
        
        // Mise à jour du bouton
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.add('hover:scale-105');
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Procéder au rechargement
                </span>
            `;
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.remove('hover:scale-105');
            submitBtn.title = errorMessage;
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${errorMessage}
                </span>
            `;
        }
        
        return isValid;
    }
    
    // Event listeners
    amountInput.addEventListener('input', function() {
        updatePreviews();
        validateForm();
    });
    
    methodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            validateForm();
            
            // Animation pour la méthode sélectionnée
            const label = this.nextElementSibling;
            label.style.transform = 'scale(1.02)';
            setTimeout(() => {
                label.style.transform = 'scale(1)';
            }, 200);
        });
    });
    
    // Formatage du montant avec animation
    amountInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value > 0) {
            this.value = value.toFixed(3);
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);
        }
        updatePreviews();
    });
    
    // Prévention de la soumission multiple avec feedback
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75');
        
        if (selectedMethod === 'CARD') {
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Redirection vers le paiement...
                </span>
            `;
        } else {
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Génération du RIB...
                </span>
            `;
        }
        
        // Permettre la soumission après un délai en cas d'erreur
        setTimeout(function() {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75');
            validateForm();
        }, 10000);
    });
    
    // Initialisation
    validateForm();
    updatePreviews();
    
    // Animation d'entrée des sections
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

    // Animation du solde actuel
    let balanceAnimationFrame = 0;
    function animateBalance() {
        balanceAnimationFrame++;
        if (balanceAnimationFrame % 60 === 0) { // Toutes les secondes à 60fps
            currentBalanceElement.style.transform = 'scale(1.02)';
            setTimeout(() => {
                currentBalanceElement.style.transform = 'scale(1)';
            }, 200);
        }
        requestAnimationFrame(animateBalance);
    }
    // animateBalance(); // Décommenter si vous voulez l'animation permanente
});

// Animation au scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
        }
    });
}, observerOptions);

// Appliquer l'observer aux éléments
document.addEventListener('DOMContentLoaded', function() {
    const elementsToObserve = document.querySelectorAll('.bg-white, .bg-gradient-to-br');
    elementsToObserve.forEach(el => {
        observer.observe(el);
    });
});
</script>
@endpush

@push('styles')
<style>
/* Amélioration des radio buttons */
input[type="radio"]:checked ~ label {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Animation pour les labels des radio */
input[type="radio"] ~ label {
    transition: all 0.3s ease;
}

input[type="radio"]:checked ~ label {
    transform: scale(1.02);
}

/* Animation fade-in */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

/* États de validation visuels */
.field-valid {
    border-color: #10b981 !important;
    background-color: #f0fdf4 !important;
}

.field-invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
}

/* Animation pour les montants rapides */
.quick-amount-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.quick-amount-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

/* Animation de pulsation pour les éléments importants */
.pulse-highlight {
    animation: pulseHighlight 2s infinite;
}

@keyframes pulseHighlight {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
    }
    50% {
        transform: scale(1.02);
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
}

/* Amélioration des gradients */
.bg-gradient-to-br {
    background-attachment: fixed;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .text-4xl {
        font-size: 2.5rem;
        line-height: 1.1;
    }
    
    .grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Améliorations des cartes */
.payment-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Animation pour les badges */
.badge {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endpush
@endsection