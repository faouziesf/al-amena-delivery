@extends('layouts.client')

@section('title', 'Créer un Manifeste')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header mobile-optimized -->
    <div class="bg-white shadow-sm sticky top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <!-- Breadcrumb compact -->
            <nav class="flex items-center space-x-2 text-sm mb-3" aria-label="Breadcrumb">
                <a href="{{ route('client.manifests.index') }}" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="hidden sm:inline">Manifestes</span>
                    <span class="sm:hidden">Retour</span>
                </a>
                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-900 font-medium">Nouveau Manifeste</span>
            </nav>

            <!-- Titre responsive -->
            <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Créer un nouveau Manifeste</h1>
                <p class="text-sm sm:text-base text-gray-600 mt-1">Sélectionnez vos colis et votre adresse de collecte</p>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Messages flash -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="font-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($availablePackages->count() == 0)
            <!-- Aucun colis disponible -->
            <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4-4-4m-6 4l4 4 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Aucun colis disponible</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-6">Tous vos colis sont soit déjà dans des manifestes, soit ne sont pas éligibles</p>
                <a href="{{ route('client.packages.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Créer des colis
                </a>
            </div>
        @else
        <form method="POST" action="{{ route('client.manifests.generate') }}" class="space-y-6">
            @csrf

            <!-- Étape 1: Sélection de l'adresse de collecte -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 px-4 sm:px-6 py-4 border-b border-indigo-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-sm">1</div>
                        <h2 class="ml-3 text-lg sm:text-xl font-semibold text-gray-900">Adresse de Collecte</h2>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    @if($clientPickupAddresses->count() > 0)
                        <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 sm:gap-4">
                            @foreach($clientPickupAddresses as $address)
                                <label class="relative block">
                                    <input type="radio" name="pickup_address_id" value="{{ $address->id }}"
                                           class="sr-only peer"
                                           @if($address->is_default) checked @endif
                                           required>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl cursor-pointer peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all duration-200 hover:shadow-sm">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $address->name }}</h3>
                                                    @if($address->is_default)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                            Par défaut
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $address->address }}</p>
                                                @if($address->phone)
                                                    <div class="flex items-center text-xs text-gray-500">
                                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                        </svg>
                                                        <span class="truncate">{{ $address->phone }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-shrink-0 ml-3">
                                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 flex items-center justify-center transition-colors">
                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Aucune adresse de collecte</h3>
                            <p class="text-sm sm:text-base text-gray-600 mb-6">Vous devez d'abord ajouter une adresse de collecte</p>
                            <a href="{{ route('client.pickup-addresses.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Ajouter une adresse
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Étape 2: Sélection des colis -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-green-50 to-green-100 px-4 sm:px-6 py-4 border-b border-green-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-sm">2</div>
                            <h2 class="ml-3 text-lg sm:text-xl font-semibold text-gray-900">Sélection des Colis</h2>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-white shadow-sm">
                                <span id="selected-count" class="font-bold text-green-600">0</span>
                                <span class="ml-1">/ {{ $availablePackages->count() }} colis</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="mb-6">
                        <label class="inline-flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                            <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-green-600 rounded border-gray-300 focus:ring-green-500">
                            <span class="ml-3 text-sm font-medium text-gray-700">Sélectionner tous les colis disponibles</span>
                        </label>
                    </div>

                    <!-- Version mobile: Cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach($availablePackages as $package)
                            <div class="bg-gray-50 rounded-xl p-4 package-row">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="package_ids[]" value="{{ $package->id }}"
                                           class="package-checkbox form-checkbox h-5 w-5 text-green-600 rounded border-gray-300 focus:ring-green-500 mt-1">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $package->package_code }}</p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $package->status === 'CREATED' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $package->status === 'CREATED' ? 'Créé' : 'Disponible' }}
                                            </span>
                                        </div>

                                        <div class="space-y-1">
                                            <p class="text-sm text-gray-900 font-medium">{{ is_array($package->recipient_data) ? ($package->recipient_data['name'] ?? 'N/A') : 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">📞 {{ is_array($package->recipient_data) ? ($package->recipient_data['phone'] ?? 'N/A') : 'N/A' }}</p>
                                            <p class="text-xs text-gray-500 truncate">📍 {{ is_array($package->recipient_data) ? ($package->recipient_data['address'] ?? 'N/A') : 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">📍 {{ optional($package->delegationTo)->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-600 truncate">📦 {{ $package->content_description }}</p>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="flex items-center space-x-1">
                                                @if($package->cod_amount > 0)
                                                    <span class="text-sm font-bold text-green-600">{{ number_format($package->cod_amount, 3) }} DT</span>
                                                    <span class="text-xs text-gray-500">COD</span>
                                                @else
                                                    <span class="text-sm text-gray-400">Aucun COD</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Version desktop: Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-12 px-6 py-3 text-left">
                                        <span class="sr-only">Sélectionner</span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Code Suivi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Destinataire
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Destination
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        COD
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statut
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($availablePackages as $package)
                                    <tr class="hover:bg-gray-50 transition-colors package-row">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="package_ids[]" value="{{ $package->id }}"
                                                   class="package-checkbox form-checkbox h-5 w-5 text-green-600 rounded border-gray-300 focus:ring-green-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $package->package_code }}</div>
                                            <div class="text-sm text-gray-500">{{ $package->content_description }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ is_array($package->recipient_data) ? ($package->recipient_data['name'] ?? 'N/A') : 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ is_array($package->recipient_data) ? ($package->recipient_data['phone'] ?? 'N/A') : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                                {{ is_array($package->recipient_data) ? ($package->recipient_data['address'] ?? 'N/A') : 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ optional($package->delegationTo)->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($package->cod_amount > 0)
                                                <span class="text-sm font-medium text-green-600">
                                                    {{ number_format($package->cod_amount, 3) }} DT
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">Aucun</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $package->status === 'CREATED' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $package->status === 'CREATED' ? 'Créé' : 'Disponible' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Résumé de sélection mobile-optimized -->
                    <div id="selection-summary" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl" style="display: none;">
                        <h3 class="text-sm font-medium text-green-900 mb-3">Résumé de la sélection</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div class="text-center p-2 bg-white rounded-lg">
                                <div class="text-lg font-bold text-green-600" id="summary-count">0</div>
                                <div class="text-xs text-gray-600">Colis sélectionnés</div>
                            </div>
                            <div class="text-center p-2 bg-white rounded-lg">
                                <div class="text-lg font-bold text-green-600" id="summary-weight">0 kg</div>
                                <div class="text-xs text-gray-600">Poids total</div>
                            </div>
                            <div class="text-center p-2 bg-white rounded-lg">
                                <div class="text-lg font-bold text-green-600" id="summary-value">0 DT</div>
                                <div class="text-xs text-gray-600">Valeur déclarée</div>
                            </div>
                            <div class="text-center p-2 bg-white rounded-lg">
                                <div class="text-lg font-bold text-green-600" id="summary-cod">0 DT</div>
                                <div class="text-xs text-gray-600">COD total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions (mobile-optimized) -->
            @if($clientPickupAddresses->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-between">
                        <a href="{{ route('client.manifests.index') }}"
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors duration-200 font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Annuler
                        </a>

                        <button type="submit" id="create-manifest-btn"
                                class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 shadow-lg font-medium"
                                disabled>
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="hidden sm:inline">Créer le Manifeste et Planifier le Ramassage</span>
                            <span class="sm:hidden">Créer le Manifeste</span>
                        </button>
                    </div>
                </div>
            @endif
        </form>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const packageCheckboxes = document.querySelectorAll('.package-checkbox');
    const selectedCountElement = document.getElementById('selected-count');
    const createBtn = document.getElementById('create-manifest-btn');
    const selectionSummary = document.getElementById('selection-summary');

    // Données des packages pour les calculs
    const packagesData = {!! json_encode($availablePackages->keyBy('id')->map(function($package) {
        return [
            'id' => $package->id,
            'weight' => $package->package_weight ?? 0,
            'value' => $package->package_value ?? 0,
            'cod' => $package->cod_amount ?? 0
        ];
    })) !!};

    function updateSelectionCount() {
        const selectedCount = document.querySelectorAll('.package-checkbox:checked').length;
        selectedCountElement.textContent = selectedCount;

        // Activer/désactiver le bouton
        if (createBtn) {
            createBtn.disabled = selectedCount === 0;
        }

        // Afficher/masquer le résumé
        if (selectedCount > 0) {
            selectionSummary.style.display = 'block';
            updateSelectionSummary();
        } else {
            selectionSummary.style.display = 'none';
        }

        // Mettre à jour l'état "Sélectionner tout"
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount === packageCheckboxes.length && selectedCount > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < packageCheckboxes.length;
        }
    }

    function updateSelectionSummary() {
        const selectedCheckboxes = document.querySelectorAll('.package-checkbox:checked');
        let totalWeight = 0;
        let totalValue = 0;
        let totalCod = 0;

        selectedCheckboxes.forEach(checkbox => {
            const packageId = parseInt(checkbox.value);
            const packageData = packagesData[packageId];
            if (packageData) {
                totalWeight += parseFloat(packageData.weight) || 0;
                totalValue += parseFloat(packageData.value) || 0;
                totalCod += parseFloat(packageData.cod) || 0;
            }
        });

        document.getElementById('summary-count').textContent = selectedCheckboxes.length;
        document.getElementById('summary-weight').textContent = totalWeight.toFixed(1) + ' kg';
        document.getElementById('summary-value').textContent = totalValue.toFixed(2) + ' DT';
        document.getElementById('summary-cod').textContent = totalCod.toFixed(3) + ' DT';
    }

    // Event listeners
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            packageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionCount();
        });
    }

    packageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionCount);
    });

    // Initialiser l'état
    updateSelectionCount();
});
</script>
@endsection