@extends('layouts.commercial')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header moderne avec navigation -->
    <div class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Colis {{ $package->package_code ?? $package->tracking_number }}</h1>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                @if($package->status === 'CREATED') bg-slate-100 text-slate-800
                                @elseif($package->status === 'AVAILABLE') bg-blue-100 text-blue-800
                                @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                                @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                                @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                                @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                @elseif($package->status === 'CANCELLED') bg-slate-100 text-slate-800
                                @else bg-purple-100 text-purple-800
                                @endif">
                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                                {{ $package->status }}
                            </span>
                            <span class="text-sm text-slate-500">Créé {{ $package->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshPage()" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser
                    </button>
                    <a href="{{ route('commercial.packages.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Section principale avec grille des informations en haut -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">

            <!-- Informations du client -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Client expéditeur
                    </h3>
                    <p class="text-emerald-100 text-sm mt-1">Informations de l'expéditeur</p>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($package->sender->first_name ?? $package->sender->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">{{ $package->sender->first_name ?? $package->sender->name }} {{ $package->sender->last_name ?? '' }}</h4>
                            <p class="text-slate-500">Client</p>
                            @if($package->sender->phone)
                                <p class="text-slate-600 text-sm">📞 {{ $package->sender->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        @if($package->sender->email)
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->sender->email }}</p>
                                <p class="text-xs text-slate-500">Email</p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->sender->account_status }}</p>
                                <p class="text-xs text-slate-500">Statut du compte</p>
                            </div>
                        </div>

                        <!-- Bouton d'appel client -->
                        @if($package->sender->phone)
                        <div class="flex items-center justify-between bg-emerald-50 rounded-lg p-3">
                            <span class="text-sm font-medium text-emerald-900">Contacter le client</span>
                            <button onclick="contactClient('{{ $package->sender->phone }}')" class="text-emerald-600 hover:text-emerald-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations du destinataire -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Destinataire
                    </h3>
                    <p class="text-indigo-100 text-sm mt-1">Informations du destinataire</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">Nom complet</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                                @if(isset($package->recipient_data['phone2']) && !empty($package->recipient_data['phone2']))
                                    <p class="text-sm text-slate-600">{{ $package->recipient_data['phone2'] }}</p>
                                @endif
                                <p class="text-xs text-slate-500">Téléphone{{ isset($package->recipient_data['phone2']) && !empty($package->recipient_data['phone2']) ? '(s)' : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['city'] ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $package->recipient_data['address'] ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">Adresse de livraison</p>
                            </div>
                        </div>
                        @if(isset($package->recipient_data['gouvernorat']))
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['gouvernorat'] }}</p>
                                <p class="text-xs text-slate-500">Gouvernorat</p>
                            </div>
                        </div>
                        @endif
                        @if(isset($package->recipient_data['postal_code']))
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['postal_code'] }}</p>
                                <p class="text-xs text-slate-500">Code postal</p>
                            </div>
                        </div>
                        @endif
                        @if(isset($package->recipient_data['notes']) && !empty($package->recipient_data['notes']))
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $package->recipient_data['notes'] }}</p>
                                <p class="text-xs text-slate-500">Notes de livraison</p>
                            </div>
                        </div>
                        @endif

                        <!-- Bouton d'appel si téléphone disponible -->
                        @if(isset($package->recipient_data['phone']) && !empty($package->recipient_data['phone']))
                        <div class="flex items-center justify-between bg-indigo-50 rounded-lg p-3">
                            <span class="text-sm font-medium text-indigo-900">Contacter le destinataire</span>
                            <button onclick="contactRecipient('{{ $package->recipient_data['phone'] }}')" class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations du livreur -->
            @if($package->assignedDeliverer)
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Livreur assigné
                    </h3>
                    <p class="text-purple-100 text-sm mt-1">Informations du livreur responsable</p>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($package->assignedDeliverer->first_name ?? $package->assignedDeliverer->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">{{ $package->assignedDeliverer->first_name ?? $package->assignedDeliverer->name }} {{ $package->assignedDeliverer->last_name ?? '' }}</h4>
                            <p class="text-slate-500">Livreur professionnel</p>
                            @if($package->assignedDeliverer->phone)
                                <p class="text-slate-600 text-sm">📞 {{ $package->assignedDeliverer->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        @if($package->assigned_at)
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($package->assigned_at)->format('d/m/Y H:i') }}</p>
                                <p class="text-xs text-slate-500">Assigné le</p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center justify-between bg-purple-50 rounded-lg p-3">
                            <span class="text-sm font-medium text-purple-900">Contacter le livreur</span>
                            <button onclick="contactDeliverer('{{ $package->assignedDeliverer->phone ?? '' }}')" class="text-purple-600 hover:text-purple-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Actions d'assignation si pas de livreur -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Actions requises
                    </h3>
                    <p class="text-orange-100 text-sm mt-1">Ce colis nécessite une intervention</p>
                </div>
                <div class="p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-2">Aucun livreur assigné</h4>
                        <p class="text-slate-500 text-sm mb-6">Ce colis doit être assigné à un livreur pour être traité.</p>
                        <button onclick="openAssignModal()" class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Assigner un livreur
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Détails du colis -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Détails du colis
                    </h3>
                    <p class="text-amber-100 text-sm mt-1">Informations techniques</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-200">
                            <div class="text-2xl font-bold text-amber-900">{{ number_format($package->cod_amount, 2) }}</div>
                            <div class="text-sm text-amber-700">TND (COD)</div>
                        </div>
                        <div class="text-center p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="text-2xl font-bold text-slate-900">{{ $package->delivery_attempts ?? 0 }}</div>
                            <div class="text-sm text-slate-700">Tentatives</div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">Type de livraison</span>
                            <span class="text-sm font-medium text-slate-900">{{ $package->delivery_type === 'fast' ? 'Rapide' : 'Avancée' }}</span>
                        </div>
                        @if($package->delegationFrom)
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">Délégation origine</span>
                            <span class="text-sm font-medium text-slate-900">{{ $package->delegationFrom->name }}</span>
                        </div>
                        @endif
                        @if($package->delegationTo)
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">Délégation destination</span>
                            <span class="text-sm font-medium text-slate-900">{{ $package->delegationTo->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions commerciales -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Actions commerciales
                    </h3>
                    <p class="text-red-100 text-sm mt-1">Opérations disponibles</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <!-- Bouton 4ème tentative (si applicable) -->
                        @if($package->status === 'AWAITING_RETURN' && $package->unavailable_attempts >= 3)
                        <form action="{{ route('commercial.packages.launch.fourth.attempt', $package) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir lancer une 4ème tentative de livraison ?');">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Lancer 4ème Tentative
                            </button>
                        </form>
                        @endif

                        <button onclick="openAssignModal()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $package->assignedDeliverer ? 'Réassigner' : 'Assigner' }} livreur
                        </button>
                        <button onclick="openCodModal()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Modifier COD
                        </button>
                        <button onclick="openManualStatusModal()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Changer statut
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Gestion des Retours (si applicable) -->
        @if(in_array($package->status, ['AWAITING_RETURN', 'RETURN_IN_PROGRESS', 'RETURNED_TO_CLIENT', 'RETURN_CONFIRMED', 'RETURN_ISSUE']) || $package->unavailable_attempts >= 3 || $package->return_reason)
        <div class="bg-white rounded-2xl shadow-xl border-2 border-orange-300 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                    </svg>
                    Gestion des Retours
                </h2>
                <p class="text-orange-100 text-sm mt-1">Suivi et actions de retour</p>
            </div>

            <div class="p-6">
                <!-- Informations de retour -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Statut du retour -->
                    <div class="bg-orange-50 rounded-xl p-4 border-2 border-orange-200">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-orange-900">Statut Actuel</h3>
                                <p class="text-sm text-orange-700">{{ $package->status }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-orange-700">Tentatives:</span>
                                <span class="font-semibold text-orange-900">{{ $package->unavailable_attempts ?? 0 }}/3</span>
                            </div>
                            @if($package->return_reason)
                            <div class="mt-2 p-2 bg-orange-100 rounded">
                                <span class="font-medium text-orange-900">Raison:</span>
                                <p class="text-orange-800 text-xs mt-1">{{ $package->return_reason }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dates importantes -->
                    <div class="bg-blue-50 rounded-xl p-4 border-2 border-blue-200">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-900">Chronologie</h3>
                                <p class="text-sm text-blue-700">Dates clés</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            @if($package->awaiting_return_since)
                            <div class="flex justify-between">
                                <span class="text-blue-700">En attente depuis:</span>
                                <span class="font-semibold text-blue-900">{{ $package->awaiting_return_since->diffForHumans() }}</span>
                            </div>
                            @endif
                            @if($package->return_in_progress_since)
                            <div class="flex justify-between">
                                <span class="text-blue-700">Retour depuis:</span>
                                <span class="font-semibold text-blue-900">{{ $package->return_in_progress_since->diffForHumans() }}</span>
                            </div>
                            @endif
                            @if($package->returned_to_client_at)
                            <div class="flex justify-between">
                                <span class="text-blue-700">Retourné le:</span>
                                <span class="font-semibold text-blue-900">{{ $package->returned_to_client_at->diffForHumans() }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Colis retour associé -->
                @if($package->returnPackage)
                <div class="border-t-2 border-orange-200 pt-6 mt-6">
                    <h3 class="font-bold text-orange-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Colis Retour Créé
                    </h3>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-slate-700">Code:</span>
                                <p class="text-slate-900 font-semibold">{{ $package->returnPackage->return_package_code }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-slate-700">Statut:</span>
                                <p class="text-slate-900 font-semibold">{{ $package->returnPackage->status }}</p>
                            </div>
                            @if($package->returnPackage->printed_at)
                            <div>
                                <span class="font-medium text-slate-700">Imprimé:</span>
                                <p class="text-slate-900">{{ $package->returnPackage->printed_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                            @if($package->returnPackage->delivered_at)
                            <div>
                                <span class="font-medium text-slate-700">Livré:</span>
                                <p class="text-slate-900">{{ $package->returnPackage->delivered_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Section historique en pleine largeur en bas -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique complet du colis
                </h2>
                <p class="text-blue-100 text-sm mt-1">Suivi détaillé de tous les événements</p>
            </div>

            <div class="p-6">
                <div class="relative">
                    <!-- Timeline -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 to-slate-200"></div>

                    <div class="space-y-6">
                        <!-- Événement: Création -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-slate-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-slate-900">Colis créé</h3>
                                    <span class="text-sm text-slate-500">{{ $package->created_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mb-2">Le colis a été créé par {{ $package->sender->first_name ?? $package->sender->name }}</p>
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div><span class="font-medium">COD:</span> {{ number_format($package->cod_amount, 2) }} TND</div>
                                    <div><span class="font-medium">Type:</span> {{ $package->delivery_type === 'fast' ? 'Rapide' : 'Avancée' }}</div>
                                </div>
                            </div>
                        </div>

                        @if($package->status !== 'CREATED')
                        <!-- Événement: Disponible -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-blue-50 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-blue-900">Colis disponible</h3>
                                    <span class="text-sm text-blue-600">{{ $package->updated_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                <p class="text-sm text-blue-700">Le colis est maintenant disponible pour les livreurs</p>
                            </div>
                        </div>
                        @endif

                        @if($package->assignedDeliverer && in_array($package->status, ['ACCEPTED', 'PICKED_UP', 'DELIVERED', 'RETURNED']))
                        <!-- Événement: Accepté par livreur -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-yellow-900">Accepté par le livreur</h3>
                                    <span class="text-sm text-yellow-600">{{ $package->assigned_at ? \Carbon\Carbon::parse($package->assigned_at)->format('d/m/Y à H:i') : 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-yellow-700 mb-2">Accepté par {{ $package->assignedDeliverer->first_name ?? $package->assignedDeliverer->name }}</p>
                                <div class="text-xs text-yellow-600">
                                    <span class="font-medium">Tél:</span> {{ $package->assignedDeliverer->phone ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(in_array($package->status, ['PICKED_UP', 'DELIVERED', 'RETURNED']))
                        <!-- Événement: Collecté -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-orange-50 rounded-xl p-4 border border-orange-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-orange-900">Colis collecté</h3>
                                    <span class="text-sm text-orange-600">{{ $package->pickup_date ? \Carbon\Carbon::parse($package->pickup_date)->format('d/m/Y à H:i') : 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-orange-700">Le colis a été récupéré par le livreur</p>
                            </div>
                        </div>
                        @endif

                        @if($package->status === 'DELIVERED')
                        <!-- Événement: Livré -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-green-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-green-900">Colis livré avec succès</h3>
                                    <span class="text-sm text-green-600">{{ $package->delivered_at ? \Carbon\Carbon::parse($package->delivered_at)->format('d/m/Y à H:i') : 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-green-700 mb-2">Livraison réussie au destinataire</p>
                                @if($package->delivery_notes)
                                <div class="text-xs text-green-600 bg-green-100 rounded p-2 mt-2">
                                    <span class="font-medium">Notes:</span> {{ $package->delivery_notes }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @elseif($package->status === 'RETURNED')
                        <!-- Événement: Retourné -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-red-50 rounded-xl p-4 border border-red-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-red-900">Colis retourné</h3>
                                    <span class="text-sm text-red-600">{{ $package->returned_at ? \Carbon\Carbon::parse($package->returned_at)->format('d/m/Y à H:i') : 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-red-700 mb-2">Le colis n'a pas pu être livré et a été retourné</p>
                                @if($package->return_reason)
                                <div class="text-xs text-red-600 bg-red-100 rounded p-2 mt-2">
                                    <span class="font-medium">Raison:</span> {{ $package->return_reason }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Affichage des modifications COD s'il y en a -->
                        @if($package->codModifications && $package->codModifications->count() > 0)
                        @foreach($package->codModifications->take(3) as $modification)
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-amber-50 rounded-xl p-4 border border-amber-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-amber-900">Modification COD</h3>
                                    <span class="text-sm text-amber-600">{{ $modification->created_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                <p class="text-sm text-amber-700 mb-2">COD modifié par {{ $modification->modifiedByCommercial->name ?? 'Commercial' }}</p>
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div><span class="font-medium">Ancien:</span> {{ number_format($modification->old_amount, 2) }} TND</div>
                                    <div><span class="font-medium">Nouveau:</span> {{ number_format($modification->new_amount, 2) }} TND</div>
                                </div>
                                @if($modification->reason)
                                <div class="text-xs text-amber-600 bg-amber-100 rounded p-2 mt-2">
                                    <span class="font-medium">Raison:</span> {{ $modification->reason }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @endif

                        <!-- Affichage des réclamations s'il y en a -->
                        @if($package->complaints && $package->complaints->count() > 0)
                        @foreach($package->complaints->take(3) as $complaint)
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-12 bg-rose-50 rounded-xl p-4 border border-rose-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-rose-900">Réclamation</h3>
                                    <span class="text-sm text-rose-600">{{ $complaint->created_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                <p class="text-sm text-rose-700 mb-2">{{ $complaint->type }} - {{ $complaint->status }}</p>
                                @if($complaint->description)
                                <div class="text-xs text-rose-600 bg-rose-100 rounded p-2 mt-2">
                                    {{ Str::limit($complaint->description, 100) }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les actions commerciales -->
@include('commercial.packages.modals.assign-deliverer', ['package' => $package])
@include('commercial.packages.modals.modify-cod', ['package' => $package])
@include('commercial.packages.modals.update-status', ['package' => $package])
@include('commercial.packages.modals.manual-status-change', ['package' => $package])

<!-- Notifications toast -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
// Fonctions utilitaires
function refreshPage() {
    window.location.reload();
}

function contactDeliverer(phone) {
    if (phone) {
        window.location.href = `tel:${phone}`;
    } else {
        showToast('Numéro de téléphone non disponible', 'warning');
    }
}

function contactRecipient(phone) {
    if (phone) {
        window.location.href = `tel:${phone}`;
    } else {
        showToast('Numéro de téléphone du destinataire non disponible', 'warning');
    }
}

function contactClient(phone) {
    if (phone) {
        window.location.href = `tel:${phone}`;
    } else {
        showToast('Numéro de téléphone du client non disponible', 'warning');
    }
}

// Modals
function openAssignModal() {
    document.getElementById('assignDelivererModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function openCodModal() {
    document.getElementById('modifyCodModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function openStatusModal() {
    document.getElementById('updateStatusModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Système de notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500';

    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>${message}</span>
        </div>
    `;

    document.getElementById('toast-container').appendChild(toast);

    // Animation d'entrée
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Suppression automatique
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Gestion des messages flash Laravel
@if(session('success'))
    showToast("{{ session('success') }}", 'success');
@endif

@if(session('error') || $errors->any())
    showToast("{{ session('error') ?? $errors->first() }}", 'error');
@endif

// Fermeture des modals avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.classList.add('hidden');
        });
        document.body.classList.remove('overflow-hidden');
    }
});
</script>
@endsection