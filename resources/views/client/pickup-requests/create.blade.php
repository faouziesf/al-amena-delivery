@extends('layouts.client')

@section('title', 'Nouvelle Demande de Collecte')
@section('page-title', 'Créer une Demande de Collecte')
@section('page-description', 'Organisez la collecte de vos colis en un seul endroit')

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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.form-section {
    animation: slideInUp 0.4s ease-out;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.form-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.1);
}

.address-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(5px);
}

.address-card:not(.cursor-not-allowed):hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
}

.address-card.selected {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px -5px rgba(139, 92, 246, 0.25), 0 0 0 1px rgba(139, 92, 246, 0.3);
    animation: pulse 2s infinite;
}

.package-item {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(5px);
}

.package-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.1);
}

.package-item.selected {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.25);
    animation: scaleIn 0.3s ease-out;
}

.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.mobile-responsive {
    @media (max-width: 640px) {
        padding: 0.75rem;
    }
}

/* Enhanced mobile styles */
@media (max-width: 640px) {
    .form-section {
        margin: 0;
        border-radius: 1rem;
        padding: 1rem;
    }

    .grid-mobile {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .address-card {
        padding: 1rem;
    }

    .package-item {
        padding: 0.75rem;
    }
}

@media (max-width: 480px) {
    .form-section {
        padding: 0.75rem;
        border-radius: 0.75rem;
    }

    h1 {
        font-size: 1.5rem;
    }

    h2 {
        font-size: 1.125rem;
    }

    .address-card, .package-item {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" x-data="pickupRequestForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center mb-6 sm:mb-8">
        <a href="{{ route('client.pickup-requests.index') }}"
           class="mb-4 sm:mb-0 sm:mr-4 inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors duration-200">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Nouvelle Demande de Collecte</h1>
            <p class="text-gray-600 mt-1">Sélectionnez vos colis et organisez leur collecte</p>
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

    <form action="{{ route('client.pickup-requests.store') }}" method="POST" class="space-y-6 sm:space-y-8" @submit="submitForm()">
        @csrf

        <!-- Champs cachés pour la soumission -->
        <input type="hidden" name="pickup_address" id="hidden_pickup_address" value="{{ old('pickup_address', '') }}" />
        <input type="hidden" name="delegation_from" id="hidden_delegation_from" value="{{ old('delegation_from', '') }}" />

        <!-- Section Adresse de Collecte -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 mobile-responsive p-4 sm:p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Adresse de Collecte</h2>
            </div>

            <!-- Type d'adresse -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:space-x-6 space-y-3 sm:space-y-0">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="address_type" value="saved" x-model="addressType" class="sr-only">
                        <div class="relative mr-3">
                            <div class="w-4 h-4 border-2 border-purple-300 rounded-full transition-all duration-200"
                                 :class="addressType === 'saved' ? 'bg-purple-600 border-purple-600' : ''"></div>
                            <div x-show="addressType === 'saved'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-75"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Utiliser une adresse enregistrée</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="address_type" value="custom" x-model="addressType" class="sr-only">
                        <div class="relative mr-3">
                            <div class="w-4 h-4 border-2 border-purple-300 rounded-full transition-all duration-200"
                                 :class="addressType === 'custom' ? 'bg-purple-600 border-purple-600' : ''"></div>
                            <div x-show="addressType === 'custom'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-75"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Saisir une nouvelle adresse</span>
                    </label>
                </div>
            </div>

            <!-- Adresses enregistrées -->
            <div x-show="addressType === 'saved'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="space-y-4">
                @if($savedAddresses->count() > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 grid-mobile">
                        @foreach($savedAddresses as $address)
                        <label class="address-card block p-4 border-2 rounded-xl transition-all duration-300"
                               :class="{
                                   'cursor-pointer hover:border-purple-300': getPackagesForAddress({{ $address->id }}) > 0,
                                   'cursor-not-allowed border-red-200 bg-red-50 opacity-75': getPackagesForAddress({{ $address->id }}) === 0,
                                   'border-purple-500 bg-purple-50 selected': selectedAddressId == {{ $address->id }},
                                   'border-gray-200': selectedAddressId != {{ $address->id }} && getPackagesForAddress({{ $address->id }}) > 0
                               }">
                            <input type="radio" name="saved_address_id" value="{{ $address->id }}"
                                   x-model="selectedAddressId"
                                   @change="filterPackagesByAddress({{ $address->id }})"
                                   :disabled="getPackagesForAddress({{ $address->id }}) === 0"
                                   class="sr-only">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                        <h3 class="font-medium text-gray-900 mb-2 sm:mb-0">{{ $address->name }}</h3>
                                        <div class="flex flex-wrap gap-2">
                                            @if($address->is_default)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Par défaut</span>
                                            @endif
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                  :class="getPackagesForAddress({{ $address->id }}) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  x-text="`${getPackagesForAddress({{ $address->id }})} colis`"></span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1">{{ $address->address }}</p>
                                    <p class="text-xs text-gray-500 mb-2">{{ $address->delegation }}, {{ $address->gouvernorat }}</p>
                                    @if($address->contact_name || $address->phone)
                                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                                            @if($address->contact_name)
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $address->contact_name }}
                                                </span>
                                            @endif
                                            @if($address->phone)
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    {{ $address->phone }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    <div x-show="getPackagesForAddress({{ $address->id }}) === 0"
                                         class="mt-2 p-2 bg-red-50 border border-red-200 rounded-md">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-xs text-red-600">Aucun colis disponible pour cette adresse</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-4 h-4 border-2 border-purple-300 rounded-full ml-3 flex-shrink-0"
                                     :class="selectedAddressId == {{ $address->id }} ? 'bg-purple-600 border-purple-600' : ''">
                                    <div x-show="selectedAddressId == {{ $address->id }}" class="w-full h-full flex items-center justify-center">
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="text-center pt-4">
                        <a href="{{ route('client.pickup-addresses.create') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter une nouvelle adresse
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 mb-4">Aucune adresse enregistrée</p>
                        <a href="{{ route('client.pickup-addresses.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Créer une adresse
                        </a>
                    </div>
                @endif
            </div>

            <!-- Nouvelle adresse -->
            <div x-show="addressType === 'custom'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 grid-mobile">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse complète *</label>
                        <textarea id="pickup_address_visible" name="pickup_address_visible" rows="3"
                                  x-on:input="document.getElementById('hidden_pickup_address').value = $event.target.value"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('pickup_address') border-red-500 @enderror"
                                  placeholder="Adresse complète de collecte...">{{ old('pickup_address') }}</textarea>
                        @error('pickup_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Délégation *</label>
                        <input type="text" id="delegation_from_visible" name="delegation_from_visible"
                               value="{{ old('delegation_from') }}"
                               x-on:input="document.getElementById('hidden_delegation_from').value = $event.target.value || 'Non spécifié'"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('delegation_from') border-red-500 @enderror"
                               placeholder="Nom de la délégation">
                        @error('delegation_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du contact</label>
                        <input type="text" name="pickup_contact_name"
                               value="{{ old('pickup_contact_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('pickup_contact_name') border-red-500 @enderror"
                               placeholder="Nom de la personne à contacter">
                        @error('pickup_contact_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <input type="tel" name="pickup_phone"
                               value="{{ old('pickup_phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('pickup_phone') border-red-500 @enderror"
                               placeholder="Numéro de téléphone">
                        @error('pickup_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Notes et Date -->
            <div class="mt-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de collecte</label>
                    <textarea name="pickup_notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('pickup_notes') border-red-500 @enderror"
                              placeholder="Instructions spéciales pour la collecte...">{{ old('pickup_notes') }}</textarea>
                    @error('pickup_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date et heure souhaitées *</label>
                    <input type="datetime-local" name="requested_pickup_date"
                           value="{{ old('requested_pickup_date', now()->addHour()->format('Y-m-d\TH:i')) }}"
                           min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('requested_pickup_date') border-red-500 @enderror">
                    @error('requested_pickup_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section Colis -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 mobile-responsive p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="flex items-center mb-3 sm:mb-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">Sélection des Colis</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600" x-text="`${selectedPackages.length} colis sélectionné(s)`"></span>
                    <span class="text-xs text-gray-500" x-text="`sur ${filteredPackages.length} disponibles`"></span>
                </div>
            </div>

            <div x-show="filteredPackages.length > 0" class="space-y-3">
                <template x-for="packageData in filteredPackages" :key="packageData.id">
                    <label class="package-item cursor-pointer flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-all duration-200"
                           :class="selectedPackages.includes(packageData.id) ? 'border-blue-500 bg-blue-50 selected' : ''">
                        <input type="checkbox" name="package_ids[]" :value="packageData.id"
                               x-model="selectedPackages" class="sr-only">
                        <div class="w-5 h-5 border-2 border-blue-300 rounded mr-4 flex items-center justify-center transition-all duration-200"
                             :class="selectedPackages.includes(packageData.id) ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedPackages.includes(packageData.id)"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-50"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="mb-2 sm:mb-0">
                                    <p class="font-medium text-gray-900" x-text="packageData.package_code"></p>
                                    <p class="text-sm text-gray-600" x-text="`${packageData.recipient_name} - ${packageData.recipient_city || 'N/A'}`"></p>
                                    <p class="text-xs text-gray-500" x-text="packageData.cod_amount ? `${packageData.cod_amount} TND` : 'Livraison gratuite'"></p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        En attente
                                    </span>
                                </div>
                            </div>
                        </div>
                    </label>
                </template>

                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <button type="button" @click="selectAllFilteredPackages()"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200"
                            x-show="filteredPackages.length > 0">
                        Sélectionner tous les colis visibles
                    </button>
                    <button type="button" @click="clearSelection()"
                            class="text-sm font-medium text-gray-600 hover:text-gray-700 transition-colors duration-200"
                            x-show="selectedPackages.length > 0">
                        Désélectionner tout
                    </button>
                </div>
            </div>

            <div x-show="filteredPackages.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div x-show="allPackages.length === 0">
                    <p class="text-gray-600 mb-2">Aucun colis disponible pour la collecte</p>
                    <p class="text-sm text-gray-500 mb-4">Vous devez créer des colis avant de pouvoir faire une demande de collecte</p>
                </div>
                <div x-show="allPackages.length > 0 && addressType === 'saved' && selectedAddressId">
                    <p class="text-gray-600 mb-2">Aucun colis disponible pour cette adresse</p>
                    <p class="text-sm text-gray-500 mb-4">Sélectionnez une autre adresse ou créez des colis pour cette adresse</p>
                </div>
                <div x-show="allPackages.length > 0 && addressType === 'saved' && !selectedAddressId">
                    <p class="text-gray-600 mb-2">Sélectionnez une adresse de collecte</p>
                    <p class="text-sm text-gray-500 mb-4">Choisissez une adresse qui contient des colis disponibles</p>
                </div>
                <div x-show="allPackages.length > 0 && addressType === 'custom'">
                    <p class="text-gray-600 mb-2">Mode nouvelle adresse</p>
                    <p class="text-sm text-gray-500 mb-4">Complétez les informations d'adresse ci-dessus</p>
                </div>
                <a href="{{ route('client.packages.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer un colis
                </a>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('client.pickup-requests.index') }}"
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Annuler
            </a>

            <button type="submit"
                    :disabled="!isFormValid()"
                    :class="isFormValid() ? 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-lg' : 'bg-gray-400 cursor-not-allowed'"
                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-lg text-base font-medium text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isFormValid() ? 'Créer la Demande' : 'Sélectionner des colis'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function pickupRequestForm() {
    return {
        addressType: 'saved',
        selectedAddressId: {{ old('saved_address_id', 'null') }},
        selectedPackages: @json(old('package_ids', [])),
        allPackages: @json($availablePackages ?? []),
        filteredPackages: [],

        init() {
            this.filterPackages();
        },

        filterPackages() {
            if (this.addressType === 'saved' && this.selectedAddressId) {
                this.filteredPackages = this.allPackages.filter(pkg => pkg.pickup_address_id == this.selectedAddressId);
            } else {
                this.filteredPackages = this.allPackages;
            }

            // Remove selected packages that are no longer visible
            this.selectedPackages = this.selectedPackages.filter(id =>
                this.filteredPackages.some(pkg => pkg.id === id)
            );
        },

        filterPackagesByAddress(addressId) {
            this.selectedAddressId = addressId;
            this.filterPackages();
        },

        getPackagesForAddress(addressId) {
            return this.allPackages.filter(pkg => pkg.pickup_address_id == addressId).length;
        },

        isFormValid() {
            const hasPackages = this.selectedPackages.length > 0;
            const hasValidAddress = this.addressType === 'custom' ||
                (this.selectedAddressId !== null && this.getPackagesForAddress(this.selectedAddressId) > 0);
            return hasPackages && hasValidAddress;
        },

        hasValidAddresses() {
            return this.allPackages.length > 0;
        },

        selectAllFilteredPackages() {
            const allFilteredIds = this.filteredPackages.map(pkg => pkg.id);
            this.selectedPackages = [...new Set([...this.selectedPackages, ...allFilteredIds])];
        },

        clearSelection() {
            this.selectedPackages = [];
        },

        submitForm() {
            // S'assurer que les champs cachés sont correctement remplis
            const hiddenPickupAddress = document.getElementById('hidden_pickup_address');
            const hiddenDelegationFrom = document.getElementById('hidden_delegation_from');

            if (this.addressType === 'saved') {
                // Pour une adresse sauvegardée, on met des chaînes vides pour pickup_address
                // mais pour delegation_from, on laisse le serveur gérer
                hiddenPickupAddress.value = '';
                hiddenDelegationFrom.value = 'saved_address'; // Indicateur pour le serveur
            } else {
                // Pour une adresse personnalisée, on prend les valeurs des champs visibles
                const visiblePickupAddress = document.getElementById('pickup_address_visible');
                const visibleDelegationFrom = document.getElementById('delegation_from_visible');

                if (visiblePickupAddress) {
                    hiddenPickupAddress.value = visiblePickupAddress.value || '';
                }
                if (visibleDelegationFrom) {
                    hiddenDelegationFrom.value = visibleDelegationFrom.value || 'Non spécifié';
                }
            }

            return true; // Permettre la soumission
        }
    }
}
</script>
@endsection