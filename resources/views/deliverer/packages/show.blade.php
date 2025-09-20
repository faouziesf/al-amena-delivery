@extends('layouts.deliverer')

@section('title', 'Colis ' . $package->package_code)

@section('content')
<div x-data="packageShowApp()" x-init="init()">
    
    <!-- Header with Package Info -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mx-4 mb-4 sticky top-20 z-10">
        <div class="p-4">
            <!-- Package Code & Status -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        {{ $package->package_code }}
                    </h1>
                    <div class="flex items-center space-x-3 mt-1">
                        <!-- Status Badge -->
                        @php
                            $statusColors = [
                                'AVAILABLE' => 'bg-blue-100 text-blue-700',
                                'ACCEPTED' => 'bg-purple-100 text-purple-700',
                                'PICKED_UP' => 'bg-orange-100 text-orange-700',
                                'DELIVERED' => 'bg-green-100 text-green-700',
                                'VERIFIED' => 'bg-red-100 text-red-700',
                                'RETURNED' => 'bg-gray-100 text-gray-700',
                                'UNAVAILABLE' => 'bg-yellow-100 text-yellow-700',
                            ];
                            $statusColor = $statusColors[$package->status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                            {{ $package->status_message }}
                        </span>
                        
                        <!-- Urgency Badge -->
                        @if($stats['is_urgent'])
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold animate-pulse">
                                🚨 URGENT
                            </span>
                        @endif
                        
                        <!-- High COD Badge -->
                        @if($package->cod_amount >= 50)
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                💰 COD Élevé
                            </span>
                        @endif
                        
                        <!-- Days Since Created -->
                        <span class="text-sm text-gray-500">
                            Il y a {{ $stats['days_since_created'] }} jour(s)
                        </span>
                    </div>
                </div>
                
                <!-- COD Amount -->
                <div class="text-right">
                    <div class="text-3xl font-bold {{ $package->cod_amount >= 100 ? 'text-red-600' : ($package->cod_amount >= 50 ? 'text-green-600' : 'text-blue-600') }}">
                        {{ number_format($package->cod_amount, 3) }} DT
                    </div>
                    <span class="text-sm text-gray-500">COD à collecter</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                @foreach($availableActions as $action)
                    @if($action['primary'])
                        <button @click="handleAction('{{ $action['key'] }}')"
                                :disabled="isProcessing"
                                class="flex-shrink-0 bg-{{ $action['color'] }}-500 text-white px-4 py-2 rounded-xl font-medium hover:bg-{{ $action['color'] }}-600 transition-colors flex items-center space-x-2 disabled:opacity-50">
                            <div x-show="isProcessing && currentAction === '{{ $action['key'] }}'" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <svg x-show="!(isProcessing && currentAction === '{{ $action['key'] }}')" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($action['icon'] === 'check-circle')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif($action['icon'] === 'truck')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0M19 17a2 2 0 104 0"/>
                                @elseif($action['icon'] === 'check')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                @elseif($action['icon'] === 'clock')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif($action['icon'] === 'arrow-left')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                @endif
                            </svg>
                            <span>{{ $action['label'] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Package Details -->
    <div class="space-y-4 px-4 pb-6">
        
        <!-- Route: From → To -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Itinéraire
                </h2>
                
                <div class="space-y-4">
                    <!-- Collection Point -->
                    <div class="bg-orange-50 p-4 rounded-xl border-l-4 border-orange-400">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-orange-800 mb-2 flex items-center">
                                    📦 Point de Collecte
                                </h3>
                                <div class="space-y-1 text-sm">
                                    <p class="font-medium text-gray-900">
                                        {{ $package->supplier_data['name'] ?? $package->sender_data['name'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-600">
                                        📞 {{ $package->supplier_data['phone'] ?? $package->sender_data['phone'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-700">
                                        📍 {{ $package->pickup_address ?? ($package->sender_data['address'] ?? 'Adresse non spécifiée') }}
                                    </p>
                                    <p class="text-orange-600 font-medium">
                                        🏛️ {{ $package->delegationFrom->name ?? 'N/A' }}
                                    </p>
                                    @if($package->pickup_notes)
                                        <div class="mt-2 pt-2 border-t border-orange-200">
                                            <p class="text-xs font-medium text-orange-700">📝 Notes pickup:</p>
                                            <p class="text-sm text-orange-800">{{ $package->pickup_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Pickup Actions -->
                            @if(in_array($package->status, ['ACCEPTED']))
                            <div class="ml-4">
                                <button @click="navigateToAddress('{{ $package->pickup_address ?? ($package->sender_data['address'] ?? '') }}')"
                                        class="bg-orange-100 text-orange-600 p-2 rounded-lg hover:bg-orange-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Route Arrow -->
                    <div class="flex justify-center">
                        <div class="bg-gray-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Delivery Point -->
                    <div class="bg-green-50 p-4 rounded-xl border-l-4 border-green-400">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-green-800 mb-2 flex items-center">
                                    🎯 Point de Livraison
                                </h3>
                                <div class="space-y-1 text-sm">
                                    <p class="font-medium text-gray-900">
                                        {{ $package->recipient_data['name'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-600">
                                        📞 {{ $package->recipient_data['phone'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-700">
                                        📍 {{ $package->recipient_data['address'] ?? 'N/A' }}
                                    </p>
                                    <p class="text-green-600 font-medium">
                                        🏛️ {{ $package->delegationTo->name ?? 'N/A' }}
                                    </p>
                                    
                                    <!-- Delivery attempts info -->
                                    @if($package->delivery_attempts > 0)
                                        <div class="mt-2 pt-2 border-t border-green-200">
                                            <p class="text-xs font-medium text-green-700">
                                                🔄 Tentatives de livraison: {{ $package->delivery_attempts }}/3
                                            </p>
                                            @if($package->unavailable_reason)
                                                @php
                                                    $reasonLabels = [
                                                        'CLIENT_ABSENT' => 'Client absent',
                                                        'ADDRESS_NOT_FOUND' => 'Adresse introuvable',
                                                        'CLIENT_REFUSES' => 'Client refuse',
                                                        'PHONE_OFF' => 'Téléphone éteint',
                                                        'OTHER' => 'Autre'
                                                    ];
                                                    $reasonLabel = $reasonLabels[$package->unavailable_reason] ?? $package->unavailable_reason;
                                                @endphp
                                                <p class="text-sm text-green-800">
                                                    Dernière raison: {{ $reasonLabel }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Delivery Actions -->
                            @if(in_array($package->status, ['PICKED_UP', 'UNAVAILABLE']))
                            <div class="ml-4">
                                <button @click="navigateToAddress('{{ $package->recipient_data['address'] ?? '' }}')"
                                        class="bg-green-100 text-green-600 p-2 rounded-lg hover:bg-green-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Content & Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Détails du Colis
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Info -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Contenu:</span>
                            <span class="font-medium text-gray-900">{{ $package->content_description }}</span>
                        </div>
                        
                        @if($package->package_weight)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Poids:</span>
                            <span class="font-medium text-gray-900">{{ number_format($package->package_weight, 3) }} kg</span>
                        </div>
                        @endif
                        
                        @if($package->package_value)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Valeur déclarée:</span>
                            <span class="font-medium text-gray-900">{{ number_format($package->package_value, 3) }} DT</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Frais de livraison:</span>
                            <span class="font-medium text-gray-900">{{ number_format($package->delivery_fee, 3) }} DT</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Frais de retour:</span>
                            <span class="font-medium text-gray-900">{{ number_format($package->return_fee, 3) }} DT</span>
                        </div>
                    </div>
                    
                    <!-- Special Requirements -->
                    <div class="space-y-3">
                        @if($package->is_fragile || $package->requires_signature || $package->special_instructions)
                            <div class="bg-amber-50 p-3 rounded-lg">
                                <h4 class="font-medium text-amber-800 mb-2">Exigences Spéciales</h4>
                                <div class="space-y-1">
                                    @if($package->is_fragile)
                                        <span class="inline-block bg-amber-200 text-amber-800 text-xs px-2 py-1 rounded-full mr-1">
                                            🔴 Fragile
                                        </span>
                                    @endif
                                    @if($package->requires_signature)
                                        <span class="inline-block bg-amber-200 text-amber-800 text-xs px-2 py-1 rounded-full mr-1">
                                            ✏️ Signature requise
                                        </span>
                                    @endif
                                </div>
                                
                                @if($package->special_instructions)
                                    <div class="mt-2 pt-2 border-t border-amber-200">
                                        <p class="text-sm text-amber-700">
                                            <strong>Instructions:</strong> {{ $package->special_instructions }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        @if($package->notes)
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <h4 class="font-medium text-blue-800 mb-2">Notes</h4>
                                <p class="text-sm text-blue-700">{{ $package->notes }}</p>
                            </div>
                        @endif
                        
                        <!-- Timeline estimate -->
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <h4 class="font-medium text-purple-800 mb-2">Estimation</h4>
                            <div class="text-sm text-purple-700">
                                <p>Temps de livraison estimé: <strong>{{ $stats['estimated_delivery_time'] }}</strong></p>
                                <p>Dernière mise à jour: {{ $stats['time_since_last_update'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status History -->
        @if(count($deliveryHistory) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique
                </h2>
                
                <div class="space-y-3">
                    @foreach($deliveryHistory as $index => $history)
                        <div class="flex items-start space-x-3 {{ $index === 0 ? 'pb-3' : 'py-3' }} {{ $index < count($deliveryHistory) - 1 ? 'border-b border-gray-100' : '' }}">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 text-sm font-medium">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        @php
                                            $statusLabels = [
                                                'CREATED' => 'Créé',
                                                'AVAILABLE' => 'Disponible',
                                                'ACCEPTED' => 'Accepté',
                                                'PICKED_UP' => 'Collecté',
                                                'DELIVERED' => 'Livré',
                                                'VERIFIED' => 'Vérifié',
                                                'RETURNED' => 'Retourné',
                                                'UNAVAILABLE' => 'Indisponible',
                                                'CANCELLED' => 'Annulé'
                                            ];
                                        @endphp
                                        {{ $statusLabels[$history['status']] ?? $history['status'] }}
                                    </p>
                                    <span class="text-xs text-gray-500">{{ $history['formatted_date'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600">Par {{ $history['user'] }}</p>
                                @if($history['notes'])
                                    <p class="text-sm text-gray-700 mt-1">{{ $history['notes'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Secondary Actions -->
        <div class="grid grid-cols-2 gap-3">
            @foreach($availableActions as $action)
                @if(!$action['primary'])
                    <button @click="handleSecondaryAction('{{ $action['key'] }}')"
                            class="flex items-center justify-center space-x-2 bg-{{ $action['color'] }}-100 text-{{ $action['color'] }}-700 py-3 px-4 rounded-xl font-medium hover:bg-{{ $action['color'] }}-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($action['icon'] === 'qrcode')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                            @elseif($action['icon'] === 'map')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            @endif
                        </svg>
                        <span>{{ $action['label'] }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <!-- MODALS -->

    <!-- Modal Pickup -->
    <div x-show="showPickupModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showPickupModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Marquer comme Collecté</h3>
                <button @click="showPickupModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitPickup()">
                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de collecte</label>
                    <textarea x-model="pickupForm.notes" 
                              placeholder="Notes optionnelles sur la collecte..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              rows="3"></textarea>
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de collecte (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handlePickupPhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="pickupPhotoInput">
                        
                        <div x-show="!pickupForm.photoPreview" @click="$refs.pickupPhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Appuyer pour prendre une photo</p>
                        </div>
                        
                        <div x-show="pickupForm.photoPreview" class="relative">
                            <img :src="pickupForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removePickupPhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit" :disabled="isProcessing"
                            class="flex-1 bg-emerald-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-emerald-600 transition-colors disabled:opacity-50">
                        <span x-show="!isProcessing">✅ Confirmer Collecte</span>
                        <span x-show="isProcessing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Collecte en cours...
                        </span>
                    </button>
                    <button type="button" @click="showPickupModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delivery -->
    <div x-show="showDeliveryModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showDeliveryModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Livrer le Colis</h3>
                <button @click="showDeliveryModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitDelivery()">
                <!-- COD Amount Verification -->
                <div class="mb-4 bg-green-50 p-4 rounded-xl border border-green-200">
                    <h4 class="font-semibold text-green-800 mb-2">💰 Montant COD à collecter</h4>
                    <div class="text-2xl font-bold text-green-700 mb-2">{{ number_format($package->cod_amount, 3) }} DT</div>
                    
                    <label class="block text-sm font-medium text-green-700 mb-2">Montant collecté (DT) *</label>
                    <input type="number" x-model="deliveryForm.codCollected" step="0.001" min="0" required
                           :max="{{ $package->cod_amount + 0.010 }}"
                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="{{ number_format($package->cod_amount, 3) }}">
                    
                    <div x-show="Math.abs(parseFloat(deliveryForm.codCollected || 0) - {{ $package->cod_amount }}) > 0.001" 
                         class="mt-2 p-2 bg-red-100 border border-red-300 rounded text-sm text-red-700">
                        ⚠️ Le montant doit être exactement {{ number_format($package->cod_amount, 3) }} DT
                    </div>
                </div>

                <!-- Recipient Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du destinataire *</label>
                    <input type="text" x-model="deliveryForm.recipientName" required
                           value="{{ $package->recipient_data['name'] ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Delivery Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes de livraison</label>
                    <textarea x-model="deliveryForm.notes" 
                              placeholder="Notes optionnelles..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              rows="3"></textarea>
                </div>

                <!-- Signature -->
                @if($package->requires_signature)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature numérique</label>
                    <div class="border border-gray-300 rounded-lg p-4">
                        <canvas x-ref="signatureCanvas" 
                                width="400" height="150" 
                                @mousedown="startSignature($event)"
                                @mousemove="drawSignature($event)"
                                @mouseup="endSignature()"
                                @touchstart.prevent="startSignature($event.touches[0])"
                                @touchmove.prevent="drawSignature($event.touches[0])"
                                @touchend.prevent="endSignature()"
                                class="w-full border border-gray-200 rounded cursor-crosshair">
                        </canvas>
                        <div class="flex justify-between mt-2">
                            <button type="button" @click="clearSignature()" 
                                    class="text-sm text-gray-600 hover:text-gray-800">Effacer</button>
                            <span class="text-xs text-gray-500">Signez ici</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de livraison (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handleDeliveryPhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="deliveryPhotoInput">
                        
                        <div x-show="!deliveryForm.photoPreview" @click="$refs.deliveryPhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Appuyer pour prendre une photo</p>
                        </div>
                        
                        <div x-show="deliveryForm.photoPreview" class="relative">
                            <img :src="deliveryForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removeDeliveryPhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit" :disabled="isProcessing || !isDeliveryFormValid()"
                            class="flex-1 bg-green-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-green-600 transition-colors disabled:opacity-50">
                        <span x-show="!isProcessing">✅ Confirmer Livraison</span>
                        <span x-show="isProcessing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Livraison en cours...
                        </span>
                    </button>
                    <button type="button" @click="showDeliveryModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Unavailable -->
    <div x-show="showUnavailableModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showUnavailableModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Client Indisponible</h3>
                <button @click="showUnavailableModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitUnavailable()">
                <!-- Current Attempt Info -->
                <div class="mb-4 bg-orange-50 p-4 rounded-xl border border-orange-200">
                    <h4 class="font-semibold text-orange-800 mb-2">📊 Tentative #{{ $package->delivery_attempts + 1 }}/3</h4>
                    @if($package->delivery_attempts >= 2)
                        <p class="text-sm text-red-700 font-medium">⚠️ Dernière tentative avant retour obligatoire</p>
                    @endif
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison d'indisponibilité *</label>
                    <select x-model="unavailableForm.reason" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Sélectionner une raison</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse le colis</option>
                        <option value="PHONE_OFF">Téléphone éteint/injoignable</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Détails de la tentative *</label>
                    <textarea x-model="unavailableForm.notes" required
                              placeholder="Décrivez ce qui s'est passé..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                              rows="3"></textarea>
                </div>

                <!-- Next Attempt Date -->
                @if($package->delivery_attempts < 2)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prochaine tentative prévue</label>
                    <input type="datetime-local" x-model="unavailableForm.nextAttempt"
                           :min="new Date(Date.now() + 3600000).toISOString().slice(0, -1)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                @endif

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de preuve (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handleUnavailablePhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="unavailablePhotoInput">
                        
                        <div x-show="!unavailableForm.photoPreview" @click="$refs.unavailablePhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Photo de preuve (adresse, boîte aux lettres...)</p>
                        </div>
                        
                        <div x-show="unavailableForm.photoPreview" class="relative">
                            <img :src="unavailableForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removeUnavailablePhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit" :disabled="isProcessing"
                            class="flex-1 bg-orange-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-orange-600 transition-colors disabled:opacity-50">
                        <span x-show="!isProcessing">⏰ Enregistrer Tentative</span>
                        <span x-show="isProcessing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Enregistrement...
                        </span>
                    </button>
                    <button type="button" @click="showUnavailableModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Return -->
    <div x-show="showReturnModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center">
        <div x-show="showReturnModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full sm:scale-95 sm:translate-y-0"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             class="bg-white rounded-t-3xl sm:rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Retourner à l'Expéditeur</h3>
                <button @click="showReturnModal = false" class="p-2 hover:bg-gray-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitReturn()">
                <!-- Info -->
                <div class="mb-4 bg-red-50 p-4 rounded-xl border border-red-200">
                    <h4 class="font-semibold text-red-800 mb-2">📋 Retour après 3 tentatives</h4>
                    <p class="text-sm text-red-700">Le colis sera retourné à {{ $package->supplier_data['name'] ?? $package->sender_data['name'] ?? 'l\'expéditeur' }}</p>
                </div>

                <!-- Return Reason -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motif de retour *</label>
                    <textarea x-model="returnForm.reason" required
                              placeholder="Motif du retour (3 tentatives échouées, refus client, etc.)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              rows="3"></textarea>
                </div>

                <!-- Return Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes additionnelles</label>
                    <textarea x-model="returnForm.notes"
                              placeholder="Notes optionnelles..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              rows="2"></textarea>
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo de retour (optionnel)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <input type="file" @change="handleReturnPhoto($event)" 
                               accept="image/*" capture="environment"
                               class="hidden" x-ref="returnPhotoInput">
                        
                        <div x-show="!returnForm.photoPreview" @click="$refs.returnPhotoInput.click()" 
                             class="text-center cursor-pointer hover:bg-gray-50 py-4 rounded">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm text-gray-600">Photo de remise à l'expéditeur</p>
                        </div>
                        
                        <div x-show="returnForm.photoPreview" class="relative">
                            <img :src="returnForm.photoPreview" class="w-full h-48 object-cover rounded">
                            <button type="button" @click="removeReturnPhoto()" 
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit" :disabled="isProcessing"
                            class="flex-1 bg-red-500 text-white py-4 px-4 rounded-xl font-semibold hover:bg-red-600 transition-colors disabled:opacity-50">
                        <span x-show="!isProcessing">↩️ Confirmer Retour</span>
                        <span x-show="isProcessing" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Retour en cours...
                        </span>
                    </button>
                    <button type="button" @click="showReturnModal = false" 
                            class="bg-gray-200 text-gray-700 py-4 px-6 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function packageShowApp() {
    return {
        package: @json($package),
        isProcessing: false,
        currentAction: '',
        
        // Modal states
        showPickupModal: false,
        showDeliveryModal: false,
        showUnavailableModal: false,
        showReturnModal: false,
        
        // Form data
        pickupForm: {
            notes: '',
            photo: null,
            photoPreview: null
        },
        
        deliveryForm: {
            codCollected: '{{ $package->cod_amount }}',
            recipientName: '{{ $package->recipient_data["name"] ?? "" }}',
            notes: '',
            signature: null,
            photo: null,
            photoPreview: null
        },
        
        unavailableForm: {
            reason: '',
            notes: '',
            nextAttempt: '',
            photo: null,
            photoPreview: null
        },
        
        returnForm: {
            reason: '',
            notes: '',
            photo: null,
            photoPreview: null
        },

        // Signature state
        isDrawing: false,
        
        init() {
            console.log('Package Show App initialized', this.package);
            // Initialize signature canvas if needed
            if (this.package.requires_signature) {
                this.$nextTick(() => {
                    const canvas = this.$refs.signatureCanvas;
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        ctx.strokeStyle = '#000';
                        ctx.lineWidth = 2;
                        ctx.lineCap = 'round';
                    }
                });
            }
        },
        
        async handleAction(actionKey) {
            this.currentAction = actionKey;
            
            switch(actionKey) {
                case 'accept':
                    await this.acceptPackage();
                    break;
                case 'pickup':
                    this.showPickupModal = true;
                    break;
                case 'deliver':
                    this.showDeliveryModal = true;
                    break;
                case 'unavailable':
                    this.showUnavailableModal = true;
                    break;
                case 'return':
                    this.showReturnModal = true;
                    break;
            }
        },
        
        handleSecondaryAction(actionKey) {
            switch(actionKey) {
                case 'scan':
                    this.$dispatch('open-scanner', { prefilledCode: this.package.package_code });
                    break;
                case 'navigate':
                    this.navigateToAddress(this.package.recipient_data?.address || '');
                    break;
            }
        },
        
        async acceptPackage() {
            if (this.isProcessing) return;
            
            this.isProcessing = true;
            
            try {
                const response = await fetch(`/deliverer/packages/${this.package.id}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'acceptation', 'error');
                }
            } catch (error) {
                console.error('Erreur acceptation:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.isProcessing = false;
        },

        async submitPickup() {
            if (this.isProcessing) return;
            this.isProcessing = true;

            try {
                const formData = new FormData();
                formData.append('pickup_notes', this.pickupForm.notes);
                if (this.pickupForm.photo) {
                    formData.append('pickup_photo', this.pickupForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.package.id}/pickup`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showPickupModal = false;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors de la collecte', 'error');
                }
            } catch (error) {
                console.error('Erreur pickup:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.isProcessing = false;
        },

        async submitDelivery() {
            if (this.isProcessing || !this.isDeliveryFormValid()) return;
            this.isProcessing = true;

            try {
                const formData = new FormData();
                formData.append('cod_collected', this.deliveryForm.codCollected);
                formData.append('recipient_name', this.deliveryForm.recipientName);
                formData.append('delivery_notes', this.deliveryForm.notes);
                
                if (this.deliveryForm.signature) {
                    formData.append('recipient_signature', this.deliveryForm.signature);
                }
                
                if (this.deliveryForm.photo) {
                    formData.append('delivery_photo', this.deliveryForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.package.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showDeliveryModal = false;
                    setTimeout(() => window.location.href = '/deliverer/deliveries', 2000);
                } else {
                    this.showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            } catch (error) {
                console.error('Erreur delivery:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.isProcessing = false;
        },

        async submitUnavailable() {
            if (this.isProcessing) return;
            this.isProcessing = true;

            try {
                const formData = new FormData();
                formData.append('reason', this.unavailableForm.reason);
                formData.append('attempt_notes', this.unavailableForm.notes);
                
                if (this.unavailableForm.nextAttempt) {
                    formData.append('next_attempt_date', this.unavailableForm.nextAttempt);
                }
                
                if (this.unavailableForm.photo) {
                    formData.append('attempt_photo', this.unavailableForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.package.id}/unavailable`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showUnavailableModal = false;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors de l\'enregistrement', 'error');
                }
            } catch (error) {
                console.error('Erreur unavailable:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.isProcessing = false;
        },

        async submitReturn() {
            if (this.isProcessing) return;
            this.isProcessing = true;

            try {
                const formData = new FormData();
                formData.append('return_reason', this.returnForm.reason);
                formData.append('return_notes', this.returnForm.notes);
                
                if (this.returnForm.photo) {
                    formData.append('return_photo', this.returnForm.photo);
                }

                const response = await fetch(`/deliverer/packages/${this.package.id}/return`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.showReturnModal = false;
                    setTimeout(() => window.location.href = '/deliverer/returns', 1500);
                } else {
                    this.showToast(data.message || 'Erreur lors du retour', 'error');
                }
            } catch (error) {
                console.error('Erreur return:', error);
                this.showToast('Erreur de connexion', 'error');
            }

            this.isProcessing = false;
        },

        // Photo handlers
        handlePickupPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.pickupForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.pickupForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removePickupPhoto() {
            this.pickupForm.photo = null;
            this.pickupForm.photoPreview = null;
            this.$refs.pickupPhotoInput.value = '';
        },

        handleDeliveryPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.deliveryForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.deliveryForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeDeliveryPhoto() {
            this.deliveryForm.photo = null;
            this.deliveryForm.photoPreview = null;
            this.$refs.deliveryPhotoInput.value = '';
        },

        handleUnavailablePhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.unavailableForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.unavailableForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeUnavailablePhoto() {
            this.unavailableForm.photo = null;
            this.unavailableForm.photoPreview = null;
            this.$refs.unavailablePhotoInput.value = '';
        },

        handleReturnPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.returnForm.photo = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.returnForm.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeReturnPhoto() {
            this.returnForm.photo = null;
            this.returnForm.photoPreview = null;
            this.$refs.returnPhotoInput.value = '';
        },

        // Signature methods
        startSignature(event) {
            this.isDrawing = true;
            const canvas = this.$refs.signatureCanvas;
            const rect = canvas.getBoundingClientRect();
            const ctx = canvas.getContext('2d');
            
            ctx.beginPath();
            ctx.moveTo(event.clientX - rect.left, event.clientY - rect.top);
        },

        drawSignature(event) {
            if (!this.isDrawing) return;
            
            const canvas = this.$refs.signatureCanvas;
            const rect = canvas.getBoundingClientRect();
            const ctx = canvas.getContext('2d');
            
            ctx.lineTo(event.clientX - rect.left, event.clientY - rect.top);
            ctx.stroke();
        },

        endSignature() {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            
            // Convert canvas to base64
            const canvas = this.$refs.signatureCanvas;
            this.deliveryForm.signature = canvas.toDataURL();
        },

        clearSignature() {
            const canvas = this.$refs.signatureCanvas;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.deliveryForm.signature = null;
        },

        // Validation
        isDeliveryFormValid() {
            const codValid = Math.abs(parseFloat(this.deliveryForm.codCollected || 0) - {{ $package->cod_amount }}) <= 0.001;
            const nameValid = this.deliveryForm.recipientName.trim().length > 0;
            return codValid && nameValid;
        },
        
        navigateToAddress(address) {
            if (!address) {
                this.showToast('Adresse non disponible', 'error');
                return;
            }
            
            const encodedAddress = encodeURIComponent(address);
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            
            if (isMobile) {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_system');
            } else {
                window.open(`https://maps.google.com/maps?q=${encodedAddress}`, '_blank');
            }
        },
        
        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 
                           type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? 
                'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 
                type === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' :
                'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
            
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}"/>
                    </svg>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    }
}
</script>
@endsection