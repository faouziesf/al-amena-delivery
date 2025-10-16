@extends('layouts.client')

@section('title', 'Création Rapide de Colis')
@section('page-title', 'Création Rapide')
@section('page-description', 'Créez plusieurs colis rapidement')

@section('content')
<!-- Main container with proper mobile spacing -->
<div x-data="fastCreateApp()" x-init="init()" class="pb-24 px-4 sm:px-6 lg:px-8">

    <!-- Progress Indicator -->
    <div class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">Mode Création Rapide</h1>
                        <p class="text-sm text-gray-500">Créez plusieurs colis efficacement</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="text-sm font-medium text-blue-700" x-text="packages.length || 0"></span>
                        <span class="text-xs text-blue-600 ml-1">colis</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pickup Address Section - Collapsible -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="p-4 cursor-pointer" @click="togglePickupSection()">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-white font-semibold">📍 Adresse de Collecte</h2>
                            <p x-show="!selectedPickupId" class="text-purple-100 text-sm">Touchez pour sélectionner</p>
                            <p x-show="selectedPickupId" class="text-purple-100 text-sm" x-text="selectedPickupSummary"></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div x-show="selectedPickupId" class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <svg class="w-5 h-5 text-white transition-transform duration-200"
                             :class="pickupExpanded ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Expandable Content -->
            <div x-show="pickupExpanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-screen"
                 class="border-t border-purple-500/30">

                @if($pickupAddresses->count() > 0)
                    <div class="p-4 space-y-3">
                        @foreach($pickupAddresses as $address)
                            <label class="block cursor-pointer">
                                <input type="radio" name="pickup_address_id" value="{{ $address->id }}"
                                       x-model="selectedPickupId" class="sr-only">
                                <div class="p-4 rounded-lg border-2 transition-all duration-200"
                                     :class="selectedPickupId == {{ $address->id }} ?
                                             'border-white bg-white/15 shadow-lg' :
                                             'border-white/30 bg-white/5 hover:bg-white/10'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <p class="font-semibold text-white">{{ $address->name }}</p>
                                                @if($address->is_default)
                                                    <span class="bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full font-medium">Défaut</span>
                                                @endif
                                            </div>
                                            <p class="text-purple-100 text-sm">📍 {{ $address->delegation }}, {{ $address->gouvernorat }}</p>
                                            @if($address->address)
                                                <p class="text-purple-200 text-xs mt-1">{{ Str::limit($address->address, 50) }}</p>
                                            @endif
                                        </div>
                                        <div x-show="selectedPickupId == {{ $address->id }}" class="ml-3">
                                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <p class="text-white mb-4">Aucune adresse de collecte enregistrée</p>
                        <a href="{{ route('client.pickup-addresses.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter une adresse
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Packages List -->
    <div class="space-y-4">
        <template x-for="(package, index) in packages" :key="package.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                <!-- Package Summary (Collapsed State) -->
                <div x-show="!package.expanded" @click="expandPackage(index)"
                     class="p-4 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <!-- Package Info -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span class="text-sm font-bold text-blue-600" x-text="index + 1"></span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">
                                        <span x-text="package.recipient_name || 'Nouveau colis'"></span>
                                    </h3>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span x-show="package.destination" x-text="package.destination"></span>
                                        <span x-show="package.prix > 0" class="text-green-600 font-medium" x-text="package.prix + ' DT'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions & Status -->
                        <div class="flex items-center space-x-2">
                            <!-- Quick Actions -->
                            <button @click.stop="duplicatePackage(index)"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Dupliquer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>

                            <button x-show="packages.length > 1" @click.stop="removePackage(index)"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>

                            <!-- Status Indicator -->
                            <div class="ml-2">
                                <div x-show="isPackageValid(package)"
                                     class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div x-show="!isPackageValid(package)"
                                     class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Expand Arrow -->
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Package Form (Expanded State) -->
                <div x-show="package.expanded"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 max-h-0"
                     x-transition:enter-end="opacity-100 max-h-screen"
                     class="border-t border-gray-200">

                    <!-- Form Header -->
                    <div class="p-4 bg-gray-50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <span class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2" x-text="index + 1"></span>
                            Configuration du colis
                        </h3>
                        <button @click="collapsePackage(index)"
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Form Content -->
                    <div class="p-4 space-y-6">
                        <!-- Recipient Section -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <h4 class="font-semibold text-blue-900">👤 Destinataire</h4>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">
                                        Nom complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="package.recipient_name"
                                           @input="updatePackageSummary(index)"
                                           class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                           placeholder="Nom du destinataire">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">
                                            📱 Téléphone principal <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel"
                                               x-model="package.telephone_1"
                                               class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="+216 XX XXX XXX">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">
                                            📞 Téléphone secondaire
                                        </label>
                                        <input type="tel"
                                               x-model="package.telephone_2"
                                               class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="+216 XX XXX XXX">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">
                                            🏛️ Gouvernorat <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="package.gouvernorat"
                                                @change="updateGouvernorat(index)"
                                                class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Sélectionner un gouvernorat</option>
                                            @foreach($gouvernorats as $key => $gouvernorat)
                                                <option value="{{ $key }}">{{ $gouvernorat }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">
                                            📍 Délégation <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="package.delegation"
                                                @change="updatePackageSummary(index)"
                                                class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Sélectionner une délégation</option>
                                            <template x-for="(delName, delKey) in package.availableDelegations" :key="delKey">
                                                <option :value="delKey" x-text="delName"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">
                                        🏠 Adresse complète <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="package.adresse_complete"
                                              rows="3"
                                              class="w-full px-4 py-3 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                              placeholder="Adresse détaillée avec points de repère..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Package Details Section -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <h4 class="font-semibold text-green-900">📦 Détails du Colis</h4>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-green-800 mb-2">
                                            📋 Contenu <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               x-model="package.contenu"
                                               class="w-full px-4 py-3 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                               placeholder="Vêtements, électronique...">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-green-800 mb-2">
                                            💰 Prix COD (TND) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number"
                                               x-model="package.prix"
                                               @input="updatePackageSummary(index)"
                                               min="0" step="0.001"
                                               class="w-full px-4 py-3 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                               placeholder="0.000">
                                    </div>
                                </div>


                                <!-- Options -->
                                <div>
                                    <label class="block text-sm font-medium text-green-800 mb-3">⚙️ Options</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="flex items-center p-3 border border-green-200 rounded-lg hover:bg-green-100 cursor-pointer transition-colors">
                                            <input type="checkbox" x-model="package.fragile" class="sr-only">
                                            <div class="w-5 h-5 border-2 border-green-400 rounded mr-3 flex items-center justify-center"
                                                 :class="package.fragile ? 'bg-green-500 border-green-500' : ''">
                                                <svg x-show="package.fragile" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-800">🔴 Fragile</span>
                                        </label>

                                        <label class="flex items-center p-3 border border-green-200 rounded-lg hover:bg-green-100 cursor-pointer transition-colors">
                                            <input type="checkbox" x-model="package.est_echange" class="sr-only">
                                            <div class="w-5 h-5 border-2 border-orange-400 rounded mr-3 flex items-center justify-center"
                                                 :class="package.est_echange ? 'bg-orange-500 border-orange-500' : ''">
                                                <svg x-show="package.est_echange" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-800">🔄 Échange</span>
                                        </label>

                                        <label class="flex items-center p-3 border border-green-200 rounded-lg hover:bg-green-100 cursor-pointer transition-colors">
                                            <input type="checkbox" x-model="package.requires_signature" class="sr-only">
                                            <div class="w-5 h-5 border-2 border-blue-400 rounded mr-3 flex items-center justify-center"
                                                 :class="package.requires_signature ? 'bg-blue-500 border-blue-500' : ''">
                                                <svg x-show="package.requires_signature" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-800">✍️ Signature</span>
                                        </label>

                                        <label class="flex items-center p-3 border border-green-200 rounded-lg hover:bg-green-100 cursor-pointer transition-colors">
                                            <input type="checkbox" x-model="package.allow_opening" class="sr-only">
                                            <div class="w-5 h-5 border-2 border-purple-400 rounded mr-3 flex items-center justify-center"
                                                 :class="package.allow_opening ? 'bg-purple-500 border-purple-500' : ''">
                                                <svg x-show="package.allow_opening" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-800">📂 Ouverture</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Additional Fields -->
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-green-800 mb-2">
                                            🗒️ Notes internes
                                        </label>
                                        <textarea x-model="package.notes"
                                                  rows="2"
                                                  class="w-full px-4 py-3 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                                  placeholder="Notes internes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Add Package Button -->
        <div class="text-center py-6">
            <button @click="addNewPackage()"
                    class="inline-flex items-center px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span x-text="packages.length === 0 ? '➕ Ajouter le premier colis' : '➕ Ajouter un autre colis'"></span>
            </button>
        </div>

        <!-- Validation Button -->
        <div x-show="packages.length > 0" class="text-center py-6 border-t border-gray-200">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-xl font-bold text-green-600" x-text="packages.length"></span>
                    </div>
                    <div class="ml-4 text-left">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="packages.length === 1 ? 'Colis préparé' : 'Colis préparés'"></h3>
                        <p class="text-sm text-gray-600" x-text="validPackagesCount() + '/' + packages.length + ' complets'"></p>
                    </div>
                </div>

                <button @click="submitAllPackages()"
                        :disabled="!canSubmit()"
                        :class="canSubmit() ?
                                'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 transform hover:scale-105' :
                                'bg-gray-400 cursor-not-allowed'"
                        class="w-full flex items-center justify-center space-x-2 px-8 py-4 text-white font-bold rounded-xl shadow-lg transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="'🚀 Créer ' + (packages.length === 1 ? 'le colis' : 'les ' + packages.length + ' colis')"></span>
                </button>

                <p x-show="!canSubmit()" class="text-xs text-gray-500 mt-2 text-center">
                    Assurez-vous que tous les champs obligatoires sont remplis et qu'une adresse de collecte est sélectionnée
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Fixed Bottom Action Bar -->
<div x-show="packages.length > 0"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-full"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 safe-area-bottom">

    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between space-x-4">
            <!-- Summary -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-bold text-blue-600" x-text="packages.length"></span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900" x-text="packages.length === 1 ? 'colis préparé' : 'colis préparés'"></p>
                    <p class="text-xs text-gray-500" x-text="validPackagesCount() + '/' + packages.length + ' complets'"></p>
                </div>
            </div>

            <!-- Action Button -->
            <button @click="submitAllPackages()"
                    :disabled="!canSubmit()"
                    :class="canSubmit() ?
                            'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 transform hover:scale-105' :
                            'bg-gray-400 cursor-not-allowed'"
                    class="flex items-center space-x-2 px-6 py-3 text-white font-bold rounded-xl shadow-lg transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="'🚀 Créer ' + (packages.length === 1 ? 'le colis' : 'les colis')"></span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fastCreateApp() {
    return {
        // State
        pickupExpanded: true,
        selectedPickupId: '',
        selectedPickupSummary: '',
        packages: [],
        nextPackageId: 1,
        initialized: false,

        // Data
        gouvernorats: @json($gouvernorats ?? []),
        delegations: @json($delegations ?? []),

        init() {
            if (this.initialized) return;
            this.initialized = true;

            // Start with one package
            this.addNewPackage();

            // Auto-select default pickup address
            const pickupAddresses = @json($pickupAddresses);
            const defaultAddress = pickupAddresses.find(addr => addr.is_default);
            if (defaultAddress) {
                this.selectedPickupId = defaultAddress.id;
                this.pickupExpanded = false;
                this.selectedPickupSummary = `${defaultAddress.name} - ${defaultAddress.delegation}, ${defaultAddress.gouvernorat}`;
            }

            // Watch for pickup selection changes
            this.$watch('selectedPickupId', () => {
                if (this.selectedPickupId) {
                    this.pickupExpanded = false;
                    const selected = pickupAddresses.find(addr => addr.id == this.selectedPickupId);
                    if (selected) {
                        this.selectedPickupSummary = `${selected.name} - ${selected.delegation}, ${selected.gouvernorat}`;
                    }
                }
            });
        },

        togglePickupSection() {
            this.pickupExpanded = !this.pickupExpanded;
        },

        addNewPackage() {
            // Collapse all existing packages
            this.packages.forEach(pkg => pkg.expanded = false);

            // Create new package
            const newPackage = {
                id: this.nextPackageId++,
                expanded: true,
                recipient_name: '',
                telephone_1: '',
                telephone_2: '',
                gouvernorat: '',
                delegation: '',
                adresse_complete: '',
                contenu: '',
                prix: 0,
                notes: '',
                fragile: false,
                est_echange: false,
                requires_signature: false,
                allow_opening: false,
                availableDelegations: {},
                destination: ''
            };

            this.packages.push(newPackage);
        },

        expandPackage(index) {
            this.packages.forEach((pkg, i) => {
                pkg.expanded = (i === index);
            });
        },

        collapsePackage(index) {
            this.packages[index].expanded = false;
        },

        duplicatePackage(index) {
            const original = this.packages[index];
            const duplicate = {
                id: this.nextPackageId++,
                expanded: false,
                recipient_name: original.recipient_name + ' (Copie)',
                telephone_1: original.telephone_1,
                telephone_2: original.telephone_2,
                gouvernorat: original.gouvernorat,
                delegation: original.delegation,
                adresse_complete: original.adresse_complete,
                contenu: original.contenu,
                prix: original.prix,
                notes: original.notes,
                fragile: original.fragile,
                est_echange: original.est_echange,
                requires_signature: original.requires_signature,
                allow_opening: original.allow_opening,
                availableDelegations: JSON.parse(JSON.stringify(original.availableDelegations || {})),
                destination: original.destination
            };

            this.packages.splice(index + 1, 0, duplicate);
        },

        removePackage(index) {
            if (this.packages.length > 1) {
                this.packages.splice(index, 1);
            } else {
                alert('Vous devez avoir au moins un colis');
            }
        },

        updateGouvernorat(index) {
            const package = this.packages[index];
            if (package.gouvernorat && this.delegations[package.gouvernorat]) {
                package.availableDelegations = this.delegations[package.gouvernorat];
                package.delegation = '';
            }
            this.updatePackageSummary(index);
        },

        updatePackageSummary(index) {
            const package = this.packages[index];
            if (package.delegation && package.gouvernorat) {
                const govName = this.gouvernorats[package.gouvernorat] || package.gouvernorat;
                const delName = package.availableDelegations[package.delegation] || package.delegation;
                package.destination = `${delName}, ${govName}`;
            }
        },

        isPackageValid(package) {
            return package.recipient_name &&
                   package.telephone_1 &&
                   package.gouvernorat &&
                   package.delegation &&
                   package.adresse_complete &&
                   package.contenu &&
                   package.prix >= 0;
        },

        validPackagesCount() {
            return this.packages.filter(pkg => this.isPackageValid(pkg)).length;
        },

        canSubmit() {
            return this.selectedPickupId &&
                   this.packages.length > 0 &&
                   this.packages.every(pkg => this.isPackageValid(pkg));
        },

        async submitAllPackages() {
            if (!this.selectedPickupId) {
                this.pickupExpanded = true;
                alert('Veuillez sélectionner une adresse de collecte');
                return;
            }

            if (!this.canSubmit()) return;

            const formData = {
                pickup_address_id: this.selectedPickupId,
                packages: this.packages.map(pkg => ({
                    recipient_name: pkg.recipient_name,
                    telephone_1: pkg.telephone_1,
                    telephone_2: pkg.telephone_2,
                    gouvernorat: pkg.gouvernorat,
                    delegation: pkg.delegation,
                    adresse_complete: pkg.adresse_complete,
                    contenu: pkg.contenu,
                    prix: pkg.prix,
                    notes: pkg.notes,
                    fragile: pkg.fragile,
                    est_echange: pkg.est_echange,
                    requires_signature: pkg.requires_signature,
                    allow_opening: pkg.allow_opening
                }))
            };

            try {
                const response = await fetch('{{ route("client.packages.store-multiple") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    // Show success notification
                    this.showNotification(`🎉 ${this.packages.length} colis créés avec succès!`, 'success');

                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = '{{ route("client.packages.index") }}?success=multiple_created';
                    }, 1500);
                } else {
                    this.showNotification(`❌ ${data.message || 'Erreur lors de la création des colis'}`, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showNotification('❌ Erreur de connexion', 'error');
            }
        },

        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    }
}
</script>
@endpush

<style>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

@media (max-width: 640px) {
    .pb-24 {
        padding-bottom: calc(6rem + env(safe-area-inset-bottom));
    }
}

[x-cloak] {
    display: none !important;
}
</style>
@endsection