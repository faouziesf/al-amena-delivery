@extends('layouts.depot-manager')

@section('title', 'Colis #' . $package->package_code)
@section('page-title', 'Détails du Colis')
@section('page-description', 'Informations complètes et historique')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('depot-manager.packages.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-bold text-gray-900">#{{ $package->package_code }}</h1>
                    @if($package->est_echange)
                        <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full font-medium">
                            🔄 Échange
                        </span>
                    @endif
                </div>
                <p class="text-gray-600">{{ $package->content_description ?? 'Description non disponible' }}</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Bouton Imprimer Bon de Livraison -->
            <a href="{{ route('depot-manager.packages.delivery-receipt', $package) }}" target="_blank"
               class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer Bon
            </a>

            <!-- Bouton Assigner Livreur -->
            @if($package->status === 'AVAILABLE' || !$package->assignedDeliverer)
            <button onclick="showAssignDelivererModal({{ $package->id }})"
                    class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Assigner Livreur
            </button>
            @endif

            <!-- Bouton Réassigner -->
            @if($package->assignedDeliverer && in_array($package->status, ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']))
            <button onclick="showAssignDelivererModal({{ $package->id }})"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Réassigner
            </button>
            @endif

            @if($package->est_echange && $package->status === 'DELIVERED')
            <button onclick="processExchangeReturn({{ $package->id }})"
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Traiter Retour
            </button>
            @endif
        </div>
    </div>

    <!-- Statut et informations principales -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                    @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                    @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                    @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ $package->status }}
                </span>
                @if($package->delivery_attempts >= 3)
                    <p class="text-sm text-red-600 mt-2 font-medium">⚠️ Urgent</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Montant COD</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($package->cod_amount, 3) }}</p>
                    <p class="text-sm text-gray-500">DT</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tentatives</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $package->delivery_attempts }}</p>
                    <p class="text-sm text-gray-500">Livraison</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Créé le</p>
                    <p class="text-lg font-bold text-gray-900">{{ $package->created_at ? $package->created_at->format('d/m/Y') : 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $package->created_at ? $package->created_at->format('H:i') : '' }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0a2 2 0 00-2 2v6m0 0a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2H8z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Expéditeur & Destinataire -->
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Expéditeur & Destinataire</h3>

            <div class="space-y-6">
                <!-- Expéditeur -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Expéditeur
                    </h4>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="font-medium text-gray-900">{{ $package->sender->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">{{ $package->sender->email ?? 'Email N/A' }}</p>
                        <p class="text-sm text-gray-600">{{ $package->sender->phone ?? 'Téléphone N/A' }}</p>
                        <p class="text-sm text-gray-600">{{ $package->delegationFrom->name ?? 'Délégation N/A' }}</p>
                    </div>
                </div>

                <!-- Destinataire -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Destinataire
                    </h4>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="font-medium text-gray-900">{{ $package->formatted_recipient ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">{{ $package->delegationTo->name ?? 'Délégation N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Livreur assigné -->
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Livreur Assigné</h3>

            @if($package->assignedDeliverer)
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-orange-600 font-bold text-lg">
                            {{ substr($package->assignedDeliverer->name, 0, 2) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $package->assignedDeliverer->phone }}</p>
                        <p class="text-sm text-gray-600">{{ $package->assignedDeliverer->assigned_delegation }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Statut compte :</span>
                        <span class="font-medium {{ $package->assignedDeliverer->account_status === 'ACTIVE' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $package->assignedDeliverer->account_status }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Type :</span>
                        <span class="font-medium text-gray-900">{{ $package->assignedDeliverer->deliverer_type ?? 'NORMAL' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Assigné le :</span>
                        <span class="font-medium text-gray-900">{{ $package->assigned_at ? $package->assigned_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>

                <div class="mt-6 flex space-x-3">
                    <a href="{{ route('depot-manager.deliverers.show', $package->assignedDeliverer) }}"
                       class="flex-1 text-center px-4 py-2 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors text-sm font-medium">
                        Voir Profil
                    </a>
                    @if(in_array($package->status, ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']))
                    <button onclick="reassignPackage({{ $package->id }})"
                            class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                        Réassigner
                    </button>
                    @endif
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="text-gray-500 mb-4">Aucun livreur assigné</p>
                    <button class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                        Assigner un livreur
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Informations Pickup -->
    @if($package->pickup_address || $package->pickup_phone || $package->pickup_notes || $package->pickupAddress || $package->pickupDelegation)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Adresse de Pickup
        </h3>

        <div class="bg-blue-50 rounded-lg p-4">
            @if($package->pickupAddress || $package->pickup_address)
                <div class="mb-3">
                    <span class="text-sm font-medium text-gray-700">Adresse:</span>
                    <p class="text-gray-900">
                        @if($package->pickupAddress)
                            {{ $package->pickupAddress->address }}
                        @else
                            {{ $package->pickup_address }}
                        @endif
                    </p>
                </div>
            @endif

            @if($package->pickup_phone || ($package->pickupAddress && $package->pickupAddress->phone))
                <div class="mb-3">
                    <span class="text-sm font-medium text-gray-700">Téléphone Contact:</span>
                    <p class="text-gray-900">
                        {{ $package->pickup_phone ?? $package->pickupAddress->phone }}
                    </p>
                </div>
            @endif

            @if($package->pickupDelegation)
                <div class="mb-3">
                    <span class="text-sm font-medium text-gray-700">Délégation Pickup:</span>
                    <p class="text-gray-900">{{ $package->pickupDelegation->name }}</p>
                </div>
            @endif

            @if($package->pickup_notes)
                <div>
                    <span class="text-sm font-medium text-gray-700">Notes Pickup:</span>
                    <p class="text-gray-900">{{ $package->pickup_notes }}</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Options spéciales et notes -->
    @if($package->hasSpecialRequirements() || $package->notes || $package->est_echange)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Options spéciales et notes</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Options spéciales -->
            @if($package->hasSpecialRequirements() || $package->est_echange)
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Options activées</h4>
                <div class="space-y-2">
                    @if($package->est_echange)
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                            <span class="text-sm text-orange-700">Colis d'échange - Retour requis</span>
                        </div>
                    @endif
                    @foreach($package->specialRequirementsList as $requirement)
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            <span class="text-sm text-gray-700">{{ $requirement }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($package->notes)
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Notes</h4>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-sm text-gray-700">{{ $package->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Historique des statuts -->
    @if($package->statusHistory && $package->statusHistory->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Historique des statuts</h3>

        <div class="space-y-4">
            @foreach($package->statusHistory as $history)
            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">
                            {{ $history->previous_status }} → {{ $history->new_status }}
                        </p>
                        <span class="text-sm text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($history->notes)
                        <p class="text-sm text-gray-600 mt-1">{{ $history->notes }}</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">Par {{ $history->changedBy->name ?? 'Système' }} ({{ $history->changed_by_role }})</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<!-- Modal d'assignation de livreur -->
<div id="assignDelivererModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAssignDelivererModal()"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Assigner un livreur
                        </h3>
                        <div class="mt-4">
                            <div class="mb-4">
                                <label for="deliverer_select" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sélectionner un livreur disponible
                                </label>
                                <select id="deliverer_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Chargement des livreurs...</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="assignment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (optionnel)
                                </label>
                                <textarea id="assignment_notes" rows="3"
                                         class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                         placeholder="Ajouter des notes pour l'assignation..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="confirmAssignDeliverer()" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        id="confirmAssignBtn">
                    Assigner
                </button>
                <button onclick="closeAssignDelivererModal()" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function reassignPackage(packageId) {
    if (confirm('Voulez-vous réassigner ce colis à un autre livreur ?')) {
        fetch(`/depot-manager/packages/${packageId}/reassign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Colis réassigné avec succès !');
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la réassignation');
        });
    }
}

function processExchangeReturn(packageId) {
    if (confirm('Voulez-vous marquer ce colis d\'échange comme retourné ?')) {
        fetch(`/depot-manager/packages/${packageId}/process-exchange-return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Colis d\'échange traité avec succès !');
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du traitement');
        });
    }
}

let currentPackageId = null;

function showAssignDelivererModal(packageId) {
    currentPackageId = packageId;
    const modal = document.getElementById('assignDelivererModal');
    const select = document.getElementById('deliverer_select');

    // Afficher le modal
    modal.classList.remove('hidden');

    // Charger la liste des livreurs
    loadDeliverers();
}

function closeAssignDelivererModal() {
    const modal = document.getElementById('assignDelivererModal');
    modal.classList.add('hidden');

    // Réinitialiser les champs
    document.getElementById('deliverer_select').innerHTML = '<option value="">Chargement des livreurs...</option>';
    document.getElementById('assignment_notes').value = '';
    currentPackageId = null;
}

async function loadDeliverers() {
    try {
        const response = await fetch('/depot-manager/api/deliverers/available', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        const select = document.getElementById('deliverer_select');

        if (data.success) {
            select.innerHTML = '<option value="">-- Sélectionner un livreur --</option>';
            data.deliverers.forEach(deliverer => {
                const option = document.createElement('option');
                option.value = deliverer.id;
                option.textContent = `${deliverer.name} (${deliverer.delegation_name || 'N/A'}) - ${deliverer.packages_count || 0} colis`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">Erreur de chargement</option>';
        }
    } catch (error) {
        console.error('Error loading deliverers:', error);
        const select = document.getElementById('deliverer_select');
        select.innerHTML = '<option value="">Erreur de chargement</option>';
    }
}

async function confirmAssignDeliverer() {
    const delivererId = document.getElementById('deliverer_select').value;
    const notes = document.getElementById('assignment_notes').value;
    const btn = document.getElementById('confirmAssignBtn');

    if (!delivererId) {
        alert('Veuillez sélectionner un livreur');
        return;
    }

    if (!currentPackageId) {
        alert('Erreur: ID du colis non trouvé');
        return;
    }

    // Désactiver le bouton
    btn.disabled = true;
    btn.textContent = 'Attribution...';

    try {
        const response = await fetch(`/depot-manager/packages/${currentPackageId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                deliverer_id: delivererId,
                notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Livreur assigné avec succès !');
            closeAssignDelivererModal();
            location.reload();
        } else {
            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur lors de l\'assignation');
    } finally {
        // Réactiver le bouton
        btn.disabled = false;
        btn.textContent = 'Assigner';
    }
}
</script>

@endsection