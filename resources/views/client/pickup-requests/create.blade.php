@extends('layouts.client')

@section('title', 'Nouvelle Demande de Collecte')
@section('page-title', 'Créer une Demande de Collecte')
@section('page-description', 'Organisez la collecte à une adresse spécifique')

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

.address-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.15);
}

.address-card.selected {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px -5px rgba(139, 92, 246, 0.25), 0 0 0 1px rgba(139, 92, 246, 0.3);
    animation: pulse 2s infinite;
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

    .address-card {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
}
</style>

<div class="max-w-4xl mx-auto px-4 sm:px-4 lg:px-4 sm:px-4" x-data="pickupRequestForm()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center mb-3 sm:mb-2 sm:mb-3 sm:mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3">
        <a href="{{ route('client.pickup-requests.index') }}"
           class="mb-2 sm:mb-3 sm:mb-0 sm:mr-4 inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors duration-200">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg sm:text-xl sm:text-xl sm:text-lg sm:text-xl font-bold text-gray-900">Nouvelle Demande de Collecte</h1>
            <p class="text-gray-600 mt-1">Créez une demande de collecte pour une adresse spécifique</p>
        </div>
    </div>

    <!-- Message d'erreur global -->
    @if($errors->any())
        <div class="mb-3 sm:mb-2 sm:mb-3 bg-red-50 border border-red-200 rounded-lg p-2.5 sm:p-3">
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

    <form action="{{ route('client.pickup-requests.store') }}" method="POST" class="space-y-3 sm:space-y-2 sm:space-y-3 sm:space-y-2 sm:space-y-3 sm:space-y-3 sm:space-y-2 sm:space-y-3" @submit="submitForm()">
        @csrf

        <!-- Champs cachés pour la soumission -->
        <input type="hidden" name="pickup_address" id="hidden_pickup_address" value="{{ old('pickup_address', '') }}" />
        <input type="hidden" name="delegation_from" id="hidden_delegation_from" value="{{ old('delegation_from', '') }}" />

        <!-- Section Adresse de Collecte -->
        <div class="form-section bg-white rounded-lg shadow-sm border border-gray-200 mobile-responsive p-2.5 sm:p-3 sm:p-3 sm:p-2.5 sm:p-3">
            <div class="flex items-center mb-3 sm:mb-2 sm:mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Adresse de Collecte</h2>
            </div>

            <!-- Type d'adresse -->
            <div class="mb-3 sm:mb-2 sm:mb-3">
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
                 class="space-y-2 sm:space-y-3">
                @if($savedAddresses->count() > 0)
                    <div class="grid grid-cols-2 gap-2 sm:gap-3 grid-mobile">
                        @foreach($savedAddresses as $address)
                        <label class="address-card block p-2.5 sm:p-3 border-2 rounded-lg transition-all duration-300 cursor-pointer hover:border-purple-300"
                               :class="{
                                   'border-purple-500 bg-purple-50 selected': selectedAddressId == {{ $address->id }},
                                   'border-gray-200': selectedAddressId != {{ $address->id }}
                               }">
                            <input type="radio" name="saved_address_id" value="{{ $address->id }}"
                                   x-model="selectedAddressId"
                                   class="sr-only">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                                        <h3 class="font-medium text-gray-900 mb-2 sm:mb-0">{{ $address->name }}</h3>
                                        <div class="flex flex-wrap gap-2">
                                            @if($address->is_default)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Par défaut</span>
                                            @endif
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
                    <div class="text-center py-2 sm:py-3 sm:py-3 sm:py-2 sm:py-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 mb-2 sm:mb-3">Aucune adresse enregistrée</p>
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
                 class="space-y-2 sm:space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-3 grid-mobile">
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
            <div class="mt-6 space-y-2 sm:space-y-3">
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

        <!-- Informations sur le fonctionnement -->
        <div class="form-section bg-blue-50 rounded-lg shadow-sm border border-blue-200 mobile-responsive p-2.5 sm:p-3 sm:p-3 sm:p-2.5 sm:p-3">
            <div class="flex items-center mb-2 sm:mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-blue-900">Comment ça fonctionne</h2>
            </div>
            <div class="space-y-3 text-sm text-blue-800">
                <div class="flex items-start">
                    <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 mt-0.5 text-xs font-bold">1</div>
                    <p>Vous créez une demande de collecte pour une adresse spécifique</p>
                </div>
                <div class="flex items-start">
                    <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 mt-0.5 text-xs font-bold">2</div>
                    <p>Un livreur sera assigné à votre demande selon la disponibilité</p>
                </div>
                <div class="flex items-start">
                    <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 mt-0.5 text-xs font-bold">3</div>
                    <p>Le livreur se rendra à l'adresse indiquée pour collecter vos colis</p>
                </div>
                <div class="flex items-start">
                    <div class="w-5 h-5 bg-blue-500 text-white rounded-full flex items-center justify-center mr-3 mt-0.5 text-xs font-bold">4</div>
                    <p>Vous pouvez suivre le statut de votre demande en temps réel</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
            <a href="{{ route('client.pickup-requests.index') }}"
               class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Annuler
            </a>

            <button type="submit"
                    :disabled="!isFormValid()"
                    :class="isFormValid() ? 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-sm' : 'bg-gray-400 cursor-not-allowed'"
                    class="inline-flex items-center justify-center px-4 sm:px-3 sm:px-4 py-2 border border-transparent rounded-lg text-base font-medium text-white transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isFormValid() ? 'Créer la Demande' : 'Remplir le formulaire'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function pickupRequestForm() {
    return {
        addressType: 'saved',
        selectedAddressId: {{ old('saved_address_id', 'null') }},

        init() {
            // Initialize the form
        },

        isFormValid() {
            const hasValidAddress = this.addressType === 'custom' || this.selectedAddressId !== null;
            return hasValidAddress;
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