@extends('layouts.client')

@section('title', 'Demande de rechargement')

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
                    💳 Demande de rechargement
                </h1>
                <p class="text-gray-600 text-lg">Faites une demande de rechargement qui sera validée par notre équipe</p>
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

            <!-- Formulaire de demande -->
            <form action="{{ route('client.wallet.process.topup') }}" method="POST" 
                  id="topupForm" class="space-y-8" enctype="multipart/form-data">
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
                                       max="10000" 
                                       value="{{ old('amount') }}"
                                       class="block w-full pl-4 pr-16 py-4 text-lg rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-50"
                                       placeholder="0.000" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-500 text-sm font-medium">DT</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Minimum: <span class="font-semibold text-emerald-600">10.000 DT</span> • 
                                Maximum: <span class="font-semibold text-emerald-600">10,000.000 DT</span>
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
                        <!-- Virement bancaire -->
                        <div class="relative">
                            <input type="radio" 
                                   name="method" 
                                   value="BANK_TRANSFER" 
                                   id="bank_transfer"
                                   class="sr-only peer" 
                                   {{ old('method', 'BANK_TRANSFER') === 'BANK_TRANSFER' ? 'checked' : '' }}>
                            <label for="bank_transfer" 
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
                                        <span class="font-semibold text-gray-900">Virement bancaire</span>
                                        <span class="ml-3 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">24-48h</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Effectuez un virement depuis votre compte bancaire
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Sécurisé
                                        </span>
                                        <span class="flex items-center text-blue-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Identifiant requis
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Versement bancaire -->
                        <div class="relative">
                            <input type="radio" 
                                   name="method" 
                                   value="BANK_DEPOSIT" 
                                   id="bank_deposit"
                                   class="sr-only peer"
                                   {{ old('method') === 'BANK_DEPOSIT' ? 'checked' : '' }}>
                            <label for="bank_deposit" 
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
                                        <span class="font-semibold text-gray-900">Versement bancaire</span>
                                        <span class="ml-3 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">24-48h</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Déposez de l'argent directement en agence bancaire
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            En espèces
                                        </span>
                                        <span class="flex items-center text-blue-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Identifiant requis
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Paiement espèces -->
                        <div class="relative">
                            <input type="radio" 
                                   name="method" 
                                   value="CASH" 
                                   id="cash_payment"
                                   class="sr-only peer"
                                   {{ old('method') === 'CASH' ? 'checked' : '' }}>
                            <label for="cash_payment" 
                                   class="flex items-start p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-purple-50 hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 transition-all duration-300">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-purple-500 peer-checked:bg-purple-500 relative">
                                        <div class="w-2 h-2 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden peer-checked:block"></div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-900">Paiement espèces</span>
                                        <span class="ml-3 text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full font-medium">Variable</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Remise d'espèces à notre livreur lors de sa visite
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Pratique
                                        </span>
                                        <span class="flex items-center text-amber-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Selon disponibilité
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Informations supplémentaires (conditionnelles) -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6" id="additional-info">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Informations complémentaires</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Identifiant bancaire (pour virement/versement) -->
                        <div id="bank-transfer-fields" style="display: none;">
                            <label for="bank_transfer_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Identifiant de virement/versement *
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="bank_transfer_id" 
                                       name="bank_transfer_id" 
                                       value="{{ old('bank_transfer_id') }}"
                                       class="block w-full pl-4 pr-12 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50"
                                       placeholder="Ex: TX123456789, REF2024001, etc.">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <div id="uniqueness-indicator" class="hidden">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Cet identifiant permet de vérifier l'unicité de votre paiement
                            </p>
                            <div id="uniqueness-message" class="mt-2 text-sm hidden"></div>
                        </div>

                        <!-- Upload du justificatif -->
                        <div>
                            <label for="proof_document" class="block text-sm font-medium text-gray-700 mb-2">
                                Justificatif de paiement (facultatif)
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       id="proof_document" 
                                       name="proof_document" 
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       class="block w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Formats acceptés: JPG, PNG, PDF (max 5 MB)
                            </p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes supplémentaires (facultatif)
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      class="block w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 resize-none"
                                      placeholder="Ajoutez des informations supplémentaires si nécessaire...">{{ old('notes') }}</textarea>
                            <p class="mt-2 text-sm text-gray-500">Maximum 500 caractères</p>
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
                            Soumettre la demande
                        </span>
                    </button>
                    
                    <a href="{{ route('client.wallet.index') }}" 
                       class="flex-none sm:w-auto bg-white text-purple-600 border-2 border-purple-200 px-8 py-4 rounded-xl hover:bg-purple-50 transition-all duration-300 font-semibold text-lg text-center shadow-md hover:shadow-lg transform hover:scale-105">
                        Annuler
                    </a>
                </div>
            </form>

            <!-- Demandes récentes -->
            @if($recentRequests && $recentRequests->count() > 0)
                <div class="mt-12 bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Demandes récentes</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($recentRequests as $request)
                            <div class="flex items-center justify-between py-3 px-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg hover:from-blue-50 hover:to-indigo-50 transition-all duration-300">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $request->request_code }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ $request->method_display }} • {{ $request->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600">
                                        {{ $request->formatted_amount }}
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->status_color }}">
                                        {{ $request->status_display }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('client.wallet.index') }}" 
                           class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Voir mon portefeuille →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('topupForm');
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('submitBtn');
    const methodRadios = document.querySelectorAll('input[name="method"]');
    const bankTransferFields = document.getElementById('bank-transfer-fields');
    const bankTransferIdInput = document.getElementById('bank_transfer_id');
    const uniquenessIndicator = document.getElementById('uniqueness-indicator');
    const uniquenessMessage = document.getElementById('uniqueness-message');
    const feePreview = document.getElementById('feePreview');
    const previewAmount = document.getElementById('previewAmount');
    const totalAmount = document.getElementById('totalAmount');
    const newBalancePreview = document.getElementById('newBalancePreview');
    const newBalanceAmount = document.getElementById('newBalanceAmount');
    const currentBalanceElement = document.getElementById('currentBalance');
    
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

    // Gestion de l'affichage conditionnel des champs
    function toggleFields() {
        const selectedMethod = document.querySelector('input[name="method"]:checked')?.value;
        
        if (selectedMethod === 'BANK_TRANSFER' || selectedMethod === 'BANK_DEPOSIT') {
            bankTransferFields.style.display = 'block';
            bankTransferIdInput.required = true;
        } else {
            bankTransferFields.style.display = 'none';
            bankTransferIdInput.required = false;
            bankTransferIdInput.value = '';
        }
    }

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

    // Validation du formulaire
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const selectedMethod = document.querySelector('input[name="method"]:checked')?.value;
        const bankTransferId = bankTransferIdInput.value.trim();
        
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
        } else if (amount > 10000) {
            isValid = false;
            errorMessage = 'Le montant maximum est de 10 000 DT';
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
        
        // Validation de l'identifiant bancaire
        if ((selectedMethod === 'BANK_TRANSFER' || selectedMethod === 'BANK_DEPOSIT') && !bankTransferId) {
            isValid = false;
            errorMessage = errorMessage || 'L\'identifiant de virement/versement est requis';
        }
        
        // Vérifier l'unicité si l'identifiant est présent
        if (bankTransferId && bankTransferIdInput.classList.contains('border-red-500')) {
            isValid = false;
            errorMessage = errorMessage || 'L\'identifiant de virement/versement n\'est pas unique';
        }
        
        // Mise à jour du bouton
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Soumettre la demande
                </span>
            `;
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.title = errorMessage;
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
            toggleFields();
            validateForm();
        });
    });
    
    // Formatage du montant avec animation
    amountInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value > 0) {
            this.value = value.toFixed(3);
        }
        updatePreviews();
    });
    
    // Prévention de la soumission multiple
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75');
        submitBtn.innerHTML = `
            <span class="flex items-center justify-center">
                <svg class="animate-spin w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Traitement en cours...
            </span>
        `;
    });
    
    // Initialisation
    toggleFields();
    validateForm();
    updatePreviews();
});
</script>
@endpush
@endsection