@extends('layouts.client')

@section('title', 'Nouveau Colis')
@section('page-title', 'Créer un Nouveau Colis')
@section('page-description', 'Créez votre colis en sélectionnant une adresse de pickup et en saisissant les informations')

@section('content')
<style>
@keyframes slideInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
.form-section {
    animation: slideInUp 0.4s ease-out;
    transition: all 0.3s ease;
}
.form-section:hover {
    transform: translateY(-2px);
}
.address-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.address-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
}
.address-card.selected {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px -5px rgba(139, 92, 246, 0.25), 0 0 0 1px rgba(139, 92, 246, 0.3);
    animation: pulse 2s infinite;
}
.mobile-responsive {
    @media (max-width: 640px) {
        padding: 0.75rem;
    }
}
@media (max-width: 640px) {
    .form-section {
        margin: 0 -0.5rem;
        border-radius: 1rem;
    }
    .grid-mobile {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" x-data="packageCreateForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center mb-6 sm:mb-8">
        <a href="{{ route('client.packages.index') }}"
           class="mb-4 sm:mb-0 sm:mr-4 inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors duration-200">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Nouveau Colis</h1>
            <p class="text-gray-600 mt-1">Créez votre colis étape par étape</p>
        </div>
    </div>

    <!-- Message d'erreur global -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Erreurs de validation</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('client.packages.store') }}" method="POST" class="space-y-6 sm:space-y-8">
        @csrf

        <!-- SECTION 1: ADRESSE DE PICKUP -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 mobile-responsive p-4 sm:p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">1. Adresse de Pickup</h2>
                <span class="ml-3 text-sm text-red-500">*</span>
            </div>

            @if($pickupAddresses->count() > 0)
                <div>
                    <label for="pickup_address_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner une adresse de pickup <span class="text-red-500">*</span>
                    </label>
                    <select id="pickup_address_id" name="pickup_address_id" required
                            x-model="selectedPickupAddressId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('pickup_address_id') border-red-500 @enderror">
                        <option value="">Choisir une adresse de pickup...</option>
                        @foreach($pickupAddresses as $address)
                            <option value="{{ $address->id }}" {{ old('pickup_address_id') == $address->id ? 'selected' : '' }}>
                                {{ $address->name }} - {{ $address->delegation }}, {{ $address->gouvernorat }}
                                @if($address->is_default) (Par défaut) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('pickup_address_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('client.pickup-addresses.create') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter une nouvelle adresse de pickup
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-600 mb-4">Aucune adresse de pickup enregistrée</p>
                    <a href="{{ route('client.pickup-addresses.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer une adresse de pickup
                    </a>
                </div>
            @endif
        </div>

        <!-- SECTION 2: INFORMATIONS DU DESTINATAIRE -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 mobile-responsive p-4 sm:p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">2. Informations du Destinataire</h2>
                <span class="ml-3 text-sm text-red-500">*</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 grid-mobile">
                <!-- Nom complet -->
                <div class="md:col-span-2">
                    <label for="nom_complet" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet du destinataire <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nom_complet" name="nom_complet" required
                           value="{{ old('nom_complet') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('nom_complet') border-red-500 @enderror"
                           placeholder="Nom et prénom du destinataire">
                    @error('nom_complet')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gouvernorat -->
                <div>
                    <label for="gouvernorat" class="block text-sm font-medium text-gray-700 mb-2">
                        Gouvernorat <span class="text-red-500">*</span>
                    </label>
                    <select id="gouvernorat" name="gouvernorat" required
                            @change="updateDelegations()"
                            x-model="selectedGouvernorat"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('gouvernorat') border-red-500 @enderror">
                        <option value="">Sélectionner un gouvernorat</option>
                        @foreach($gouvernorats as $key => $name)
                            <option value="{{ $key }}" {{ old('gouvernorat') === $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('gouvernorat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Délégation -->
                <div>
                    <label for="delegation" class="block text-sm font-medium text-gray-700 mb-2">
                        Délégation <span class="text-red-500">*</span>
                    </label>
                    <select id="delegation" name="delegation" required
                            x-model="selectedDelegation"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('delegation') border-red-500 @enderror">
                        <option value="">Sélectionner une délégation</option>
                        <template x-for="(delegationName, delegationKey) in availableDelegations" :key="delegationKey">
                            <option :value="delegationKey" x-text="delegationName" :selected="selectedDelegation === delegationKey"></option>
                        </template>
                    </select>
                    @error('delegation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone 1 -->
                <div>
                    <label for="telephone_1" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone 1 <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="telephone_1" name="telephone_1" required
                           value="{{ old('telephone_1') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('telephone_1') border-red-500 @enderror"
                           placeholder="Ex: +216 XX XXX XXX">
                    @error('telephone_1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone 2 (optionnel) -->
                <div>
                    <label for="telephone_2" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone 2 <span class="text-gray-400 text-xs">(optionnel)</span>
                    </label>
                    <input type="tel" id="telephone_2" name="telephone_2"
                           value="{{ old('telephone_2') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('telephone_2') border-red-500 @enderror"
                           placeholder="Ex: +216 XX XXX XXX">
                    @error('telephone_2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse complète -->
                <div class="md:col-span-2">
                    <label for="adresse_complete" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse complète de livraison <span class="text-red-500">*</span>
                    </label>
                    <textarea id="adresse_complete" name="adresse_complete" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('adresse_complete') border-red-500 @enderror"
                              placeholder="Adresse détaillée de livraison (rue, numéro, quartier, points de repère...)">{{ old('adresse_complete') }}</textarea>
                    @error('adresse_complete')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 3: DÉTAILS DU COLIS -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 mobile-responsive p-4 sm:p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-300 to-purple-500 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">3. Détails du Colis</h2>
                <span class="ml-3 text-sm text-red-500">*</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 grid-mobile">
                <!-- Contenu -->
                <div>
                    <label for="contenu" class="block text-sm font-medium text-gray-700 mb-2">
                        Contenu du colis <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="contenu" name="contenu" required
                           value="{{ old('contenu') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('contenu') border-red-500 @enderror"
                           placeholder="Ex: Vêtements, électronique, documents...">
                    @error('contenu')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix -->
                <div>
                    <label for="prix" class="block text-sm font-medium text-gray-700 mb-2">
                        Prix du colis (TND) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="prix" name="prix" min="0" step="0.001" required
                           value="{{ old('prix') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('prix') border-red-500 @enderror"
                           placeholder="0.000">
                    @error('prix')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Commentaire optionnel -->
                <div class="md:col-span-2">
                    <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-2">
                        Commentaire <span class="text-gray-400 text-xs">(optionnel)</span>
                    </label>
                    <textarea id="commentaire" name="commentaire" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('commentaire') border-red-500 @enderror"
                              placeholder="Instructions spéciales, informations complémentaires...">{{ old('commentaire') }}</textarea>
                    @error('commentaire')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Options du colis -->
            <div class="mt-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Options de traitement</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Fragile -->
                    <label class="flex items-center cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-purple-300 transition-all duration-200"
                           :class="isFragile ? 'border-purple-400 bg-purple-50' : ''">
                        <input type="checkbox" name="fragile" value="1" x-model="isFragile" class="sr-only">
                        <div class="flex items-center">
                            <div class="w-5 h-5 border-2 border-purple-300 rounded mr-3 flex items-center justify-center transition-all duration-200"
                                 :class="isFragile ? 'bg-purple-500 border-purple-500' : ''">
                                <svg x-show="isFragile" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Fragile</span>
                        </div>
                    </label>

                    <!-- Signature obligatoire -->
                    <label class="flex items-center cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-purple-300 transition-all duration-200"
                           :class="requiresSignature ? 'border-purple-500 bg-purple-50' : ''">
                        <input type="checkbox" name="signature_obligatoire" value="1" x-model="requiresSignature" class="sr-only">
                        <div class="flex items-center">
                            <div class="w-5 h-5 border-2 border-purple-300 rounded mr-3 flex items-center justify-center transition-all duration-200"
                                 :class="requiresSignature ? 'bg-purple-600 border-purple-600' : ''">
                                <svg x-show="requiresSignature" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Signature obligatoire</span>
                        </div>
                    </label>

                    <!-- Autorisation d'ouvrir -->
                    <label class="flex items-center cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-purple-300 transition-all duration-200"
                           :class="allowOpening ? 'border-purple-600 bg-purple-50' : ''">
                        <input type="checkbox" name="autorisation_ouverture" value="1" x-model="allowOpening" class="sr-only">
                        <div class="flex items-center">
                            <div class="w-5 h-5 border-2 border-purple-300 rounded mr-3 flex items-center justify-center transition-all duration-200"
                                 :class="allowOpening ? 'bg-purple-700 border-purple-700' : ''">
                                <svg x-show="allowOpening" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Autoriser l'ouverture</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Mode de paiement -->
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Mode de paiement accepté</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Espèces seulement (par défaut) -->
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="especes_seulement" x-model="paymentMethod" class="sr-only" checked>
                        <div class="p-4 border-2 rounded-lg transition-all duration-200"
                             :class="paymentMethod === 'especes_seulement' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300'">
                            <div class="flex items-center">
                                <div class="w-4 h-4 border-2 border-green-300 rounded-full mr-3"
                                     :class="paymentMethod === 'especes_seulement' ? 'bg-green-600 border-green-600' : ''">
                                    <div x-show="paymentMethod === 'especes_seulement'" class="w-full h-full flex items-center justify-center">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">Espèces seulement</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Paiement uniquement en liquide</p>
                        </div>
                    </label>

                    <!-- Chèque seulement -->
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="cheque_seulement" x-model="paymentMethod" class="sr-only">
                        <div class="p-4 border-2 rounded-lg transition-all duration-200"
                             :class="paymentMethod === 'cheque_seulement' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                            <div class="flex items-center">
                                <div class="w-4 h-4 border-2 border-blue-300 rounded-full mr-3"
                                     :class="paymentMethod === 'cheque_seulement' ? 'bg-blue-600 border-blue-600' : ''">
                                    <div x-show="paymentMethod === 'cheque_seulement'" class="w-full h-full flex items-center justify-center">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">Chèque seulement</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Paiement uniquement par chèque</p>
                        </div>
                    </label>

                    <!-- Espèces et Chèque -->
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="especes_et_cheques" x-model="paymentMethod" class="sr-only">
                        <div class="p-4 border-2 rounded-lg transition-all duration-200"
                             :class="paymentMethod === 'especes_et_cheques' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                            <div class="flex items-center">
                                <div class="w-4 h-4 border-2 border-purple-300 rounded-full mr-3"
                                     :class="paymentMethod === 'especes_et_cheques' ? 'bg-purple-600 border-purple-600' : ''">
                                    <div x-show="paymentMethod === 'especes_et_cheques'" class="w-full h-full flex items-center justify-center">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-900">Espèces et Chèque</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Paiement en liquide ou par chèque</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('client.packages.index') }}"
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Annuler
            </a>

            <button type="submit"
                    :disabled="!isFormValid()"
                    :class="isFormValid() ? 'bg-gradient-to-r from-purple-500 to-purple-700 hover:from-purple-600 hover:to-purple-800 shadow-lg' : 'bg-gray-400 cursor-not-allowed'"
                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-lg text-base font-medium text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isFormValid() ? 'Créer le Colis' : 'Remplir les champs obligatoires'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function packageCreateForm() {
    return {
        selectedPickupAddressId: {{ old('pickup_address_id', 'null') }},
        selectedGouvernorat: '{{ old('gouvernorat', '') }}',
        selectedDelegation: '{{ old('delegation', '') }}',
        isFragile: {{ old('fragile') ? 'true' : 'false' }},
        requiresSignature: {{ old('signature_obligatoire') ? 'true' : 'false' }},
        allowOpening: {{ old('autorisation_ouverture') ? 'true' : 'false' }},
        paymentMethod: '{{ old('payment_method', 'especes_seulement') }}',
        delegationsData: @json($delegationsData ?? []),
        availableDelegations: {},

        init() {
            this.updateDelegations();
            if (this.selectedDelegation) {
                this.$nextTick(() => {
                    this.updateDelegations();
                });
            }
        },

        updateDelegations() {
            if (this.selectedGouvernorat && this.delegationsData[this.selectedGouvernorat]) {
                this.availableDelegations = this.delegationsData[this.selectedGouvernorat];
            } else {
                this.availableDelegations = {};
                this.selectedDelegation = '';
            }
        },

        isFormValid() {
            return this.selectedPickupAddressId !== null;
        }
    }
}
</script>
@endsection