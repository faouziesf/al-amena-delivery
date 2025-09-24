@extends('layouts.client')

@section('title', 'Modifier l\'Adresse de Collecte')
@section('page-title', 'Modifier l\'Adresse de Collecte')
@section('page-description', 'Mettez à jour les informations de votre adresse de collecte')

@section('content')
<style>
@keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.form-section { animation: slideInUp 0.4s ease-out; transition: all 0.3s ease; }
.form-section:hover { transform: translateY(-2px); }
</style>

<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center mb-8">
        <a href="{{ route('client.pickup-addresses.index') }}" class="mr-4 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier l'Adresse de Collecte</h1>
            <p class="text-gray-600">{{ $pickupAddress->name }}</p>
        </div>
    </div>

    <form action="{{ route('client.pickup-addresses.update', $pickupAddress) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section Informations Principales -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Informations de l'Adresse</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom de l'adresse -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'adresse <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                           value="{{ old('name', $pickupAddress->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 @error('name') border-red-500 @enderror"
                           placeholder="Ex: Boutique principale, Entrepôt Tunis...">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse complète -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse complète <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 @error('address') border-red-500 @enderror"
                              placeholder="Adresse complète avec rue, numéro, quartier...">{{ old('address', $pickupAddress->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gouvernorat -->
                <div>
                    <label for="gouvernorat" class="block text-sm font-medium text-gray-700 mb-2">
                        Gouvernorat <span class="text-red-500">*</span>
                    </label>
                    <select id="gouvernorat" name="gouvernorat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 @error('gouvernorat') border-red-500 @enderror">
                        <option value="">Sélectionner un gouvernorat</option>
                        @if(isset($gouvernorats) && $gouvernorats)
                            @foreach($gouvernorats as $key => $name)
                                <option value="{{ $key }}" {{ (old('gouvernorat', $pickupAddress->gouvernorat) === $key) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        @endif
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 @error('delegation') border-red-500 @enderror">
                        <option value="">Sélectionner une délégation</option>
                    </select>
                    @error('delegation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse par défaut -->
                <div class="md:col-span-2 flex items-center justify-end">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $pickupAddress->is_default) ? 'checked' : '' }}
                               class="sr-only">
                        <div class="relative">
                            <div class="w-6 h-6 border-2 border-emerald-300 rounded bg-white transition-all duration-200 checkbox-unchecked"></div>
                            <div class="absolute inset-0 w-6 h-6 bg-emerald-600 border-2 border-emerald-600 rounded flex items-center justify-center opacity-0 transition-all duration-200 checkbox-checked">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Définir comme adresse par défaut</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Section Informations de Contact -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Informations de Contact</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Nom du contact -->
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du contact
                    </label>
                    <input type="text" id="contact_name" name="contact_name"
                           value="{{ old('contact_name', $pickupAddress->contact_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('contact_name') border-red-500 @enderror"
                           placeholder="Nom de la personne à contacter">
                    @error('contact_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone principal -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone principal
                    </label>
                    <input type="tel" id="phone" name="phone"
                           value="{{ old('phone', $pickupAddress->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('phone') border-red-500 @enderror"
                           placeholder="Ex: +216 XX XXX XXX">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone secondaire -->
                <div>
                    <label for="tel2" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone secondaire <span class="text-gray-400 text-xs">(optionnel)</span>
                    </label>
                    <input type="tel" id="tel2" name="tel2"
                           value="{{ old('tel2', $pickupAddress->tel2) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 @error('tel2') border-red-500 @enderror"
                           placeholder="Ex: +216 XX XXX XXX">
                    @error('tel2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section Notes -->
        <div class="form-section bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Notes Additionnelles</h2>
                <span class="ml-3 text-sm text-gray-500">(Optionnel)</span>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Instructions spéciales
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 @error('notes') border-red-500 @enderror"
                          placeholder="Instructions spéciales pour cette adresse (heures d'ouverture, étage, code d'accès...)">{{ old('notes', $pickupAddress->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('client.pickup-addresses.index') }}"
               class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Annuler
            </a>

            <button type="submit"
                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg shadow-sm text-base font-medium text-white hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mettre à Jour
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gouvernoratSelect = document.getElementById('gouvernorat');
    const delegationSelect = document.getElementById('delegation');

    // Données des délégations par gouvernorat
    const delegationsData = @json($delegationsData ?? []);

    function updateDelegations() {
        const selectedGouvernorat = gouvernoratSelect.value;

        // Vider la liste des délégations
        delegationSelect.innerHTML = '<option value="">Sélectionner une délégation</option>';

        // Remplir avec les délégations du gouvernorat sélectionné
        if (selectedGouvernorat && delegationsData[selectedGouvernorat]) {
            Object.entries(delegationsData[selectedGouvernorat]).forEach(([key, name]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = name;
                delegationSelect.appendChild(option);
            });
        }
    }

    gouvernoratSelect.addEventListener('change', updateDelegations);

    // Initialiser les délégations au chargement
    updateDelegations();

    // Sélectionner la délégation actuelle si elle existe
    @if(old('delegation', $pickupAddress->delegation))
        setTimeout(() => {
            delegationSelect.value = '{{ old('delegation', $pickupAddress->delegation) }}';
        }, 100);
    @endif
});
</script>

<style>
/* Styles pour la checkbox personnalisée */
input[type="checkbox"]:checked + div .checkbox-unchecked {
    opacity: 0;
}

input[type="checkbox"]:checked + div .checkbox-checked {
    opacity: 1;
}

input[type="checkbox"]:focus + div {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>
@endsection