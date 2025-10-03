@extends('layouts.client')

@section('title', 'Modifier le Colis')
@section('page-title', 'Modification de Colis')
@section('page-description', 'Modifier les informations du colis {{ $package->package_code }}')

@section('content')
<div class="pb-24 px-4 sm:px-6 lg:px-8">

    <!-- Header avec informations du colis -->
    <div class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Modifier le Colis</h1>
                        <p class="text-sm text-gray-500">{{ $package->package_code }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border
                        {{ match($package->status) {
                            'CREATED' => 'bg-gray-50 text-gray-700 border-gray-200',
                            'AVAILABLE' => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-gray-50 text-gray-700 border-gray-200',
                        } }}
                    ">
                        {{ match($package->status) {
                            'CREATED' => '🆕 Créé',
                            'AVAILABLE' => '📋 Disponible',
                            default => $package->status,
                        } }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice d'information -->
    <div class="mb-6">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Information:</strong> Vous pouvez uniquement modifier les colis qui n'ont pas encore été pris en charge par un livreur.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('client.packages.update', $package) }}" x-data="editPackageApp()" x-init="init()">
        @csrf
        @method('PUT')

        <!-- Adresse de Collecte -->
        <div class="mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg overflow-hidden">
                <div class="p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-white font-semibold">📍 Adresse de Collecte</h2>
                            <p class="text-purple-100 text-sm">Sélectionnez l'adresse de collecte</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4">
                    <div class="space-y-4">
                        @if($pickupAddresses->isNotEmpty())
                            @foreach($pickupAddresses as $address)
                                <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" name="pickup_address_id" value="{{ $address->id }}"
                                           class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300"
                                           {{ old('pickup_address_id', $package->pickup_address_id) == $address->id ? 'checked' : '' }} required>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $address->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $address->address }}, {{ $address->city }}</p>
                                        <p class="text-xs text-gray-400">📞 {{ $address->phone }}</p>
                                        @if($address->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                Par défaut
                                            </span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <div class="text-center py-6">
                                <p class="text-gray-500">Aucune adresse de collecte configurée.</p>
                                <a href="{{ route('client.pickup-addresses.create') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                    Créer une adresse
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du Destinataire -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-white font-semibold">👤 Informations du Destinataire</h2>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <!-- Nom complet -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom Complet *</label>
                        <input type="text" name="nom_complet" value="{{ old('nom_complet', $package->recipient_data['name'] ?? '') }}"
                               required maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        @error('nom_complet')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphones -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone Principal *</label>
                            <input type="tel" name="telephone_1" value="{{ old('telephone_1', $package->recipient_data['phone'] ?? '') }}"
                                   required maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            @error('telephone_1')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone Secondaire</label>
                            <input type="tel" name="telephone_2" value="{{ old('telephone_2', $package->recipient_data['phone2'] ?? '') }}"
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        </div>
                    </div>

                    <!-- Adresse complète -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse Complète *</label>
                        <textarea name="adresse_complete" required maxlength="500" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">{{ old('adresse_complete', $package->recipient_data['address'] ?? '') }}</textarea>
                        @error('adresse_complete')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gouvernorat et Délégation -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gouvernorat *</label>
                            <select name="gouvernorat" x-model="selectedGouvernorat" @change="updateDelegations()" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                <option value="">Sélectionnez un gouvernorat</option>
                                @foreach($gouvernorats as $gov)
                                    <option value="{{ $gov['name'] }}"
                                            {{ old('gouvernorat', $package->recipient_data['gouvernorat'] ?? '') === $gov['name'] ? 'selected' : '' }}>
                                        {{ $gov['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gouvernorat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Délégation *</label>
                            <select name="delegation" x-model="selectedDelegation" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                <option value="">Sélectionnez une délégation</option>
                                <template x-for="delegation in availableDelegations" :key="delegation">
                                    <option :value="delegation" x-text="delegation"
                                            :selected="delegation === '{{ old('delegation', $package->recipient_data['delegation'] ?? '') }}'"></option>
                                </template>
                            </select>
                            @error('delegation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du Colis -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-white font-semibold">📦 Informations du Colis</h2>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <!-- Contenu et Prix -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contenu du Colis *</label>
                            <input type="text" name="contenu" value="{{ old('contenu', $package->content_description) }}"
                                   required maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @error('contenu')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prix (TND) *</label>
                            <input type="number" name="prix" value="{{ old('prix', $package->cod_amount) }}"
                                   step="0.001" min="0" max="9999.999" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @error('prix')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Commentaire -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                        <textarea name="commentaire" maxlength="1000" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">{{ old('commentaire', $package->notes) }}</textarea>
                    </div>

                    <!-- Options du Colis -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Options du Colis</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="fragile" {{ old('fragile', $package->is_fragile) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">🔻 Fragile</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="signature_obligatoire" {{ old('signature_obligatoire', $package->requires_signature) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">✍️ Signature obligatoire</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="autorisation_ouverture" {{ old('autorisation_ouverture', $package->allow_opening) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">📂 Autorisation d'ouverture</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="est_echange" {{ old('est_echange', $package->is_exchange) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">🔄 Échange</span>
                            </label>
                        </div>
                    </div>

                    <!-- Méthode de Paiement -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Méthode de Paiement *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="payment_method" value="especes_seulement"
                                       {{ old('payment_method', $package->payment_method) === 'especes_seulement' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="text-sm text-gray-700">💵 Espèces seulement</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="payment_method" value="cheque_seulement"
                                       {{ old('payment_method', $package->payment_method) === 'cheque_seulement' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="text-sm text-gray-700">💳 Chèque seulement</span>
                            </label>
                            <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="payment_method" value="especes_et_cheques"
                                       {{ old('payment_method', $package->payment_method) === 'especes_et_cheques' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="text-sm text-gray-700">💰 Espèces et chèques</span>
                            </label>
                        </div>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'Action -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('client.packages.index') }}"
               class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<script>
function editPackageApp() {
    return {
        selectedGouvernorat: '{{ old('gouvernorat', $package->recipient_data['gouvernorat'] ?? '') }}',
        selectedDelegation: '{{ old('delegation', $package->recipient_data['delegation'] ?? '') }}',
        availableDelegations: [],
        delegationsData: @json($delegations),

        init() {
            this.updateDelegations();
        },

        updateDelegations() {
            if (this.selectedGouvernorat && this.delegationsData[this.selectedGouvernorat]) {
                this.availableDelegations = this.delegationsData[this.selectedGouvernorat];
            } else {
                this.availableDelegations = [];
                this.selectedDelegation = '';
            }
        }
    }
}
</script>
@endsection