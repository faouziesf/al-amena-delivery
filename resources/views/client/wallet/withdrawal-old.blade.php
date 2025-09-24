@extends('layouts.client')

@section('title', 'Demander un retrait')

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
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                    💸 Demander un retrait
                </h1>
                <p class="text-gray-600 text-lg">Retirez vos fonds par virement bancaire ou livraison espèces</p>
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

            <!-- Affichage du solde disponible -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-2xl p-6 mb-8 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-purple-100 mb-2">💰 Solde disponible pour retrait</h3>
                        <p class="text-4xl font-bold">{{ number_format($availableBalance, 3) }} DT</p>
                    </div>
                    <div class="text-right text-purple-100">
                        <p class="text-sm">Solde total: {{ number_format($user->wallet->balance, 3) }} DT</p>
                        @if($user->wallet->frozen_amount > 0)
                            <p class="text-sm">Montant gelé: {{ number_format($user->wallet->frozen_amount, 3) }} DT</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Formulaire de demande de retrait -->
            <form action="{{ route('client.wallet.store.withdrawal') }}" method="POST" 
                  id="withdrawalForm" class="space-y-8">
                @csrf

                <!-- Montant à retirer -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Montant à retirer</h3>
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
                                       min="1" 
                                       max="{{ $availableBalance }}" 
                                       value="{{ old('amount') }}"
                                       class="block w-full pl-4 pr-16 py-4 text-lg rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-gray-50"
                                       placeholder="0.000" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-500 text-sm font-medium">DT</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Maximum disponible: <span class="font-semibold text-purple-600">{{ number_format($availableBalance, 3) }} DT</span>
                            </p>
                        </div>

                        <!-- Boutons de montant rapide -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-3">Montants populaires:</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach([50, 100, 200, 500] as $quickAmount)
                                    @if($quickAmount <= $availableBalance)
                                        <button type="button" 
                                                onclick="setAmount({{ $quickAmount }})"
                                                class="px-4 py-2 text-sm bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 rounded-lg hover:from-purple-200 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105 font-medium">
                                            {{ $quickAmount }} DT
                                        </button>
                                    @endif
                                @endforeach
                                @if($availableBalance > 0)
                                    <button type="button" 
                                            onclick="setAmount({{ $availableBalance }})"
                                            class="px-4 py-2 text-sm bg-gradient-to-r from-emerald-100 to-teal-100 text-emerald-700 rounded-lg hover:from-emerald-200 hover:to-teal-200 transition-all duration-300 transform hover:scale-105 font-medium">
                                        Tout ({{ number_format($availableBalance, 3) }} DT)
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Aperçu du montant -->
                        <div id="amountPreview" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                            <p class="text-blue-800 font-medium">
                                <span>Montant à retirer:</span>
                                <span id="previewAmount" class="text-xl font-bold ml-2">0.000 DT</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Méthode de retrait -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Méthode de retrait</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Virement bancaire -->
                        <div class="relative">
                            <input type="radio" 
                                   name="preferred_method" 
                                   value="BANK_TRANSFER" 
                                   id="bank_transfer"
                                   class="sr-only peer" 
                                   {{ old('preferred_method', 'BANK_TRANSFER') === 'BANK_TRANSFER' ? 'checked' : '' }}>
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
                                        <span class="ml-3 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-medium">Recommandé</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Recevez vos fonds directement sur votre compte bancaire
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-green-600">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 10-9 9m-9-9a9 9 0 019 9 9 9 0 01-9-9"/>
                                            </svg>
                                            Sécurisé
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Livraison espèces -->
                        <div class="relative">
                            <input type="radio" 
                                   name="preferred_method" 
                                   value="CASH_DELIVERY" 
                                   id="cash_delivery"
                                   class="sr-only peer"
                                   {{ old('preferred_method') === 'CASH_DELIVERY' ? 'checked' : '' }}>
                            <label for="cash_delivery" 
                                   class="flex items-start p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-gradient-to-br peer-checked:from-green-50 peer-checked:to-emerald-50 transition-all duration-300">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-6 h-6 border-2 border-gray-300 rounded-full peer-checked:border-green-500 peer-checked:bg-green-500 relative">
                                        <div class="w-2 h-2 bg-white rounded-full absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden peer-checked:block"></div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-900">Livraison espèces</span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        Un livreur vous apporte les espèces à votre adresse
                                    </p>
                                    <div class="flex flex-wrap items-center gap-4 text-sm">
                                        <span class="flex items-center text-amber-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            2-5 jours
                                        </span>
                                        <span class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Frais: Gratuit
                                        </span>
                                        <span class="flex items-center text-indigo-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            À votre adresse
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Détails bancaires (affiché conditionnellement) -->
                <div id="bankDetailsSection" class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Coordonnées bancaires</h3>
                        <span class="ml-3 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Requis</span>
                    </div>
                    
                    <div>
                        <label for="bank_details" class="block text-sm font-medium text-gray-700 mb-3">
                            Détails bancaires complets *
                        </label>
                        <textarea id="bank_details" 
                                  name="bank_details" 
                                  rows="6"
                                  class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-gray-50"
                                  placeholder="Veuillez fournir vos coordonnées bancaires complètes :&#10;&#10;• Nom de la banque&#10;• IBAN ou RIB complet&#10;• Nom du bénéficiaire (tel qu'il apparaît sur le compte)&#10;• Agence bancaire (si nécessaire)&#10;• Toute autre information nécessaire au virement"
                                  >{{ old('bank_details') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            ⚠️ Vérifiez attentivement vos coordonnées bancaires pour éviter tout retard ou erreur de virement
                        </p>
                    </div>
                </div>

                <!-- Motif du retrait (optionnel) -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-full p-3 mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Motif du retrait</h3>
                        <span class="ml-3 text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full font-medium">Optionnel</span>
                    </div>
                    
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-3">
                            Expliquez brièvement le motif de votre retrait
                        </label>
                        <textarea id="reason" 
                                  name="reason" 
                                  rows="4"
                                  class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 bg-gray-50"
                                  placeholder="Ex: Paiement fournisseur, besoin de liquidités, investissement, etc."
                                  >{{ old('reason') }}</textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            Cette information peut nous aider à traiter votre demande plus rapidement
                        </p>
                    </div>
                </div>

                <!-- Informations importantes -->
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-l-4 border-amber-400 rounded-xl p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-amber-800 mb-3">Informations importantes</h4>
                            <ul class="text-amber-700 space-y-2">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Le montant sera gelé dans votre portefeuille jusqu'au traitement de votre demande</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Les virements bancaires sont traités sous 24-48h ouvrables (lundi-vendredi)</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Pour les livraisons espèces, un livreur vous contactera pour coordonner la remise</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                                    </svg>
                                    <span>Vous recevrez une notification à chaque étape du traitement</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 10-9 9m-9-9a9 9 0 019 9 9 9 0 01-9-9"/>
                                    </svg>
                                    <span>Aucun frais n'est appliqué sur les retraits, quelle que soit la méthode choisie</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit" 
                            id="submitBtn"
                            class="flex-1 bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-8 py-4 rounded-xl hover:from-purple-600 hover:to-indigo-700 focus:ring-4 focus:ring-purple-200 transition-all duration-300 font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="flex items-center justify-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Confirmer la demande de retrait
                        </span>
                    </button>
                    
                    <a href="{{ route('client.wallet.index') }}" 
                       class="flex-none sm:w-auto bg-white text-purple-600 border-2 border-purple-200 px-8 py-4 rounded-xl hover:bg-purple-50 transition-all duration-300 font-semibold text-lg text-center shadow-md hover:shadow-lg transform hover:scale-105">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('withdrawalForm');
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('submitBtn');
    const bankDetailsSection = document.getElementById('bankDetailsSection');
    const bankDetailsTextarea = document.getElementById('bank_details');
    const methodRadios = document.querySelectorAll('input[name="preferred_method"]');
    const amountPreview = document.getElementById('amountPreview');
    const previewAmount = document.getElementById('previewAmount');
    
    // Fonction pour définir un montant
    window.setAmount = function(amount) {
        amountInput.value = amount.toFixed(3);
        updatePreview();
        validateForm();
        
        // Animation du champ montant
        amountInput.style.transform = 'scale(1.05)';
        amountInput.style.borderColor = '#8b5cf6';
        setTimeout(() => {
            amountInput.style.transform = 'scale(1)';
            amountInput.style.borderColor = '';
        }, 300);
    };
    
    // Mise à jour de l'aperçu du montant
    function updatePreview() {
        const amount = parseFloat(amountInput.value) || 0;
        if (amount > 0) {
            previewAmount.textContent = amount.toFixed(3) + ' DT';
            amountPreview.classList.remove('hidden');
            amountPreview.style.opacity = '0';
            setTimeout(() => {
                amountPreview.style.transition = 'opacity 0.3s ease';
                amountPreview.style.opacity = '1';
            }, 100);
        } else {
            amountPreview.classList.add('hidden');
        }
    }
    
    // Gestion de l'affichage des détails bancaires
    function toggleBankDetails() {
        const selectedMethod = document.querySelector('input[name="preferred_method"]:checked');
        if (selectedMethod && selectedMethod.value === 'BANK_TRANSFER') {
            bankDetailsSection.style.display = 'block';
            bankDetailsTextarea.required = true;
            // Animation d'apparition
            bankDetailsSection.style.opacity = '0';
            bankDetailsSection.style.transform = 'translateY(20px)';
            setTimeout(() => {
                bankDetailsSection.style.transition = 'all 0.5s ease';
                bankDetailsSection.style.opacity = '1';
                bankDetailsSection.style.transform = 'translateY(0)';
            }, 100);
        } else {
            bankDetailsSection.style.display = 'none';
            bankDetailsTextarea.required = false;
            bankDetailsTextarea.value = '';
        }
    }
    
    // Validation du formulaire avec feedback visuel
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const maxAmount = {{ $availableBalance }};
        const selectedMethod = document.querySelector('input[name="preferred_method"]:checked')?.value;
        
        let isValid = true;
        let errorMessage = '';
        
        // Validation du montant
        if (amount <= 0) {
            isValid = false;
            errorMessage = 'Le montant doit être supérieur à zéro';
            amountInput.classList.add('border-red-500', 'bg-red-50');
        } else if (amount > maxAmount) {
            isValid = false;
            errorMessage = `Le montant ne peut pas dépasser ${maxAmount.toFixed(3)} DT`;
            amountInput.classList.add('border-red-500', 'bg-red-50');
        } else {
            amountInput.classList.remove('border-red-500', 'bg-red-50');
            amountInput.classList.add('border-green-500', 'bg-green-50');
        }
        
        // Validation de la méthode
        if (!selectedMethod) {
            isValid = false;
            errorMessage = errorMessage || 'Veuillez sélectionner une méthode de retrait';
        }
        
        // Validation des détails bancaires si nécessaire
        if (selectedMethod === 'BANK_TRANSFER' && !bankDetailsTextarea.value.trim()) {
            isValid = false;
            errorMessage = errorMessage || 'Les coordonnées bancaires sont requises';
            bankDetailsTextarea.classList.add('border-red-500', 'bg-red-50');
        } else if (selectedMethod === 'BANK_TRANSFER' && bankDetailsTextarea.value.trim()) {
            bankDetailsTextarea.classList.remove('border-red-500', 'bg-red-50');
            bankDetailsTextarea.classList.add('border-green-500', 'bg-green-50');
        }
        
        // Mise à jour du bouton
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.add('hover:scale-105');
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Confirmer la demande de retrait
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
    methodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleBankDetails();
            validateForm();
            
            // Animation pour la méthode sélectionnée
            const label = this.nextElementSibling;
            label.style.transform = 'scale(1.02)';
            setTimeout(() => {
                label.style.transform = 'scale(1)';
            }, 200);
        });
    });
    
    amountInput.addEventListener('input', function() {
        updatePreview();
        validateForm();
    });
    
    bankDetailsTextarea.addEventListener('input', validateForm);
    
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
        updatePreview();
    });
    
    // Prévention de la soumission multiple avec feedback
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        const selectedMethod = document.querySelector('input[name="preferred_method"]:checked').value;
        
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75');
        
        if (selectedMethod === 'BANK_TRANSFER') {
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Traitement de la demande...
                </span>
            `;
        } else {
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center">
                    <svg class="animate-spin w-6 h-6 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Assignment du livreur...
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
    toggleBankDetails();
    validateForm();
    
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
});
</script>
@endpush

@push('styles')
<style>
/* Amélioration des radio buttons */
input[type="radio"]:checked ~ label {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Animation pour les labels des radio */
input[type="radio"] ~ label {
    transition: all 0.3s ease;
}

input[type="radio"]:checked ~ label {
    border-color: #8b5cf6 !important;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(99, 102, 241, 0.05)) !important;
}

/* Amélioration des tooltips */
[title] {
    position: relative;
}

/* Animation de pulsation pour les éléments importants */
.pulse-highlight {
    animation: pulseHighlight 2s infinite;
}

@keyframes pulseHighlight {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4);
    }
    50% {
        transform: scale(1.02);
        box-shadow: 0 0 0 10px rgba(139, 92, 246, 0);
    }
}

/* Indicateur de chargement personnalisé */
.loading-dots::after {
    content: '';
    animation: loadingDots 1.5s infinite;
}

@keyframes loadingDots {
    0%, 20% { content: ''; }
    40% { content: '.'; }
    60% { content: '..'; }
    80%, 100% { content: '...'; }
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

/* Animation personnalisée pour les boutons */
.btn-pulse {
    animation: btnPulse 2s infinite;
}

@keyframes btnPulse {
    0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(139, 92, 246, 0); }
    100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
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
}
</style>
@endpush
@endsection