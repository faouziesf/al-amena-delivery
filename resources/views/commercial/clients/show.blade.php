@extends('layouts.commercial')

@section('title', 'Profil Client - ' . $client->name)
@section('page-title', $client->name)
@section('page-description', 'Profil détaillé et gestion du compte client')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.clients.index') }}" 
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>
    
    @if($client->account_status === 'PENDING')
        <button onclick="validateClient()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Valider le Compte
        </button>
    @endif
    
    <a href="{{ route('commercial.clients.edit', $client) }}" 
       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Modifier
    </a>
</div>
@endsection

@section('content')
<div x-data="clientProfileApp()" x-init="init()" class="space-y-6">
    
    <!-- Client Header Card -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg text-white p-8">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-6">
                <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <span class="text-3xl font-bold text-white">{{ substr($client->name, 0, 2) }}</span>
                </div>
                
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold">{{ $client->name }}</h1>
                    @if($client->clientProfile && $client->clientProfile->shop_name)
                        <p class="text-xl text-purple-100">{{ $client->clientProfile->shop_name }}</p>
                    @endif
                    <div class="flex items-center space-x-4 text-purple-100">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $client->email }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $client->phone }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10h6V11M6 7h12l-1 10H7L6 7z"/>
                            </svg>
                            Client #{{ $client->id }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="text-right">
                @if($client->account_status === 'ACTIVE')
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-green-500 text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Compte Actif
                    </span>
                @elseif($client->account_status === 'PENDING')
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-orange-500 text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        En Attente de Validation
                    </span>
                @else
                    <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-red-500 text-white">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                        </svg>
                        Compte Suspendu
                    </span>
                @endif
                
                <div class="mt-2 text-purple-100 text-sm">
                    Créé le {{ $client->created_at->format('d/m/Y') }}
                    @if($client->createdBy)
                        <br>par {{ $client->createdBy->name }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Solde Wallet</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="formatAmount(stats.wallet_balance)"></p>
                    <template x-if="stats.pending_amount > 0">
                        <p class="text-xs text-orange-600" x-text="'En attente: ' + formatAmount(stats.pending_amount)"></p>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Colis</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.total_packages"></p>
                    <p class="text-xs text-blue-600" x-text="stats.packages_in_progress + ' en cours'"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Livrés</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.packages_delivered"></p>
                    <p class="text-xs text-green-600" x-text="getSuccessRate() + '% succès'"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Réclamations</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.complaints"></p>
                    <template x-if="stats.pending_complaints > 0">
                        <p class="text-xs text-red-600" x-text="stats.pending_complaints + ' en attente'"></p>
                    </template>
                    <template x-if="stats.pending_complaints === 0">
                        <p class="text-xs text-green-600">Aucune en attente</p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
                <button @click="setActiveTab('overview')" 
                        :class="activeTab === 'overview' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Vue d'ensemble
                </button>
                <button @click="setActiveTab('wallet')" 
                        :class="activeTab === 'wallet' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Wallet & Transactions
                </button>
                <button @click="setActiveTab('packages')" 
                        :class="activeTab === 'packages' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Colis (<span x-text="stats.total_packages"></span>)
                </button>
                <button @click="setActiveTab('history')" 
                        :class="activeTab === 'history' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" x-transition class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Client Information -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Client</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Nom complet:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Email:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->email }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Téléphone:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->phone }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Adresse:</dt>
                                    <dd class="text-sm font-medium text-gray-900 text-right max-w-xs">{{ $client->address }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Business Information -->
                        @if($client->clientProfile && ($client->clientProfile->shop_name || $client->clientProfile->fiscal_number || $client->clientProfile->business_sector))
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Professionnelles</h3>
                            <dl class="space-y-3">
                                @if($client->clientProfile->shop_name)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Boutique/Entreprise:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->clientProfile->shop_name }}</dd>
                                </div>
                                @endif
                                @if($client->clientProfile->fiscal_number)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Matricule fiscal:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->clientProfile->fiscal_number }}</dd>
                                </div>
                                @endif
                                @if($client->clientProfile->business_sector)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Secteur d'activité:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->clientProfile->business_sector }}</dd>
                                </div>
                                @endif
                                @if($client->clientProfile->identity_document)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Document d'identité:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->clientProfile->identity_document }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        @endif
                    </div>

                    <!-- Pricing & Account Info -->
                    <div class="space-y-6">
                        <!-- Pricing Configuration -->
                        <div class="bg-purple-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration Tarifaire</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-white rounded-lg border border-green-200">
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ number_format($client->clientProfile->offer_delivery_price ?? 0, 3) }} DT
                                    </div>
                                    <div class="text-sm text-gray-600">Livraison réussie</div>
                                </div>
                                <div class="text-center p-4 bg-white rounded-lg border border-red-200">
                                    <div class="text-2xl font-bold text-red-600">
                                        {{ number_format($client->clientProfile->offer_return_price ?? 0, 3) }} DT
                                    </div>
                                    <div class="text-sm text-gray-600">Retour échec</div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Compte</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Date de création:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                @if($client->createdBy)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Créé par:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->createdBy->name }}</dd>
                                </div>
                                @endif
                                @if($client->verified_at)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Validé le:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->verified_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                @endif
                                @if($client->verifiedBy)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Validé par:</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $client->verifiedBy->name }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Dernière connexion:</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        {{ $client->last_login ? $client->last_login->diffForHumans() : 'Jamais connecté' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white border-2 border-purple-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="openWalletModal()" 
                                        class="flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Gérer Wallet
                                </button>
                                
                                <button @click="createPackage()" 
                                        class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nouveau Colis
                                </button>
                                
                                <button @click="duplicateClient()" 
                                        class="flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Dupliquer
                                </button>
                                
                                @if($client->account_status === 'ACTIVE')
                                <button @click="suspendClient()" 
                                        class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636"/>
                                    </svg>
                                    Suspendre
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Tab -->
            <div x-show="activeTab === 'wallet'" x-transition class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Wallet Summary -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                            <h3 class="text-lg font-semibold mb-2">Solde Actuel</h3>
                            <div class="text-3xl font-bold" x-text="formatAmount(stats.wallet_balance)"></div>
                            <template x-if="stats.pending_amount > 0">
                                <div class="mt-2 text-green-100" x-text="'En attente: ' + formatAmount(stats.pending_amount)"></div>
                            </template>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h4 class="font-semibold text-gray-900 mb-4">Actions Wallet</h4>
                            <div class="space-y-3">
                                <button @click="openAddFundsModal()" 
                                        class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Ajouter des fonds
                                </button>
                                <button @click="openDeductFundsModal()" 
                                        class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                    Déduire des fonds
                                </button>
                                <button @click="exportWalletHistory()" 
                                        class="w-full flex items-center justify-center px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Exporter historique
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border border-gray-200 rounded-lg">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Historique des Transactions</h3>
                            </div>
                            
                            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                                @forelse($client->transactions->take(20) as $transaction)
                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                                {{ $transaction->amount > 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                                @if($transaction->amount > 0)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $transaction->description }}</p>
                                                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                                            </div>
                                            <div class="text-xs text-gray-500">{{ ucfirst($transaction->status) }}</div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="p-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune transaction</h3>
                                    <p class="mt-1 text-sm text-gray-500">L'historique des transactions apparaîtra ici.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packages Tab -->
            <div x-show="activeTab === 'packages'" x-transition class="space-y-6">
                <!-- Package Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600" x-text="stats.total_packages"></div>
                        <div class="text-sm text-blue-600">Total Colis</div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-orange-600" x-text="stats.packages_in_progress"></div>
                        <div class="text-sm text-orange-600">En cours</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600" x-text="stats.packages_delivered"></div>
                        <div class="text-sm text-green-600">Livrés</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600" x-text="stats.complaints"></div>
                        <div class="text-sm text-red-600">Réclamations</div>
                    </div>
                </div>

                <!-- Recent Packages -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Colis Récents</h3>
                            <a href="{{ route('commercial.packages.index') }}?sender_id={{ $client->id }}" 
                               class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Voir tous les colis →
                            </a>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        @forelse($packages->take(10) as $package)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $package->package_code }}</p>
                                        <p class="text-xs text-gray-500">
                                            Vers {{ $package->recipient_data['name'] ?? 'N/A' }} - 
                                            {{ $package->delegationTo->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $package->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                                        @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                        @elseif(in_array($package->status, ['PICKED_UP', 'ACCEPTED'])) bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $package->status }}
                                    </span>
                                    <div class="text-sm font-medium text-gray-900 mt-1">
                                        {{ number_format($package->cod_amount, 3) }} DT
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun colis</h3>
                            <p class="mt-1 text-sm text-gray-500">Ce client n'a pas encore envoyé de colis.</p>
                            <div class="mt-6">
                                <button @click="createPackage()" 
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Créer le premier colis
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div x-show="activeTab === 'history'" x-transition class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Journal d'Activité</h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    Compte client <span class="font-medium text-gray-900">créé</span>
                                                    @if($client->createdBy)
                                                        par <span class="font-medium text-gray-900">{{ $client->createdBy->name }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time>{{ $client->created_at->format('d/m/Y H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                @if($client->verified_at)
                                <li class="relative pb-8">
                                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    Compte <span class="font-medium text-gray-900">validé</span>
                                                    @if($client->verifiedBy)
                                                        par <span class="font-medium text-gray-900">{{ $client->verifiedBy->name }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time>{{ $client->verified_at->format('d/m/Y H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Management Modal -->
    <div x-show="isWalletModalOpen" 
         @keydown.escape.window="closeWalletModal()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-bold text-gray-900">Gérer le Wallet</h3>
                    <button @click="closeWalletModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitWalletForm()" class="p-6 space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">Solde actuel</div>
                        <div class="text-2xl font-bold text-gray-900" x-text="formatAmount(stats.wallet_balance)"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                        <select x-model="walletForm.action" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="add">Ajouter des fonds</option>
                            <option value="deduct">Déduire des fonds</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant (DT)</label>
                        <input type="number" x-model="walletForm.amount" step="0.001" min="0.001" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea x-model="walletForm.description" rows="3" required
                                  placeholder="Motif de l'ajustement..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                            <span x-show="!isSubmitting">Confirmer</span>
                            <span x-show="isSubmitting">Traitement...</span>
                        </button>
                        <button type="button" @click="closeWalletModal()" 
                                class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div x-data="toastManager()" class="fixed top-4 right-4 z-50 space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-full"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-full"
             :class="toast.type === 'success' ? 'bg-green-500' : toast.type === 'error' ? 'bg-red-500' : 'bg-blue-500'"
             class="text-white px-6 py-4 rounded-lg shadow-lg max-w-sm">
            <div class="flex items-center justify-between">
                <p x-text="toast.message" class="text-sm font-medium"></p>
                <button @click="removeToast(toast.id)" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
// Configuration globale
const CLIENT_ID = {{ $client->id }};
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Alpine.js Components
function clientProfileApp() {
    return {
        activeTab: '{{ request("tab", "overview") }}',
        isWalletModalOpen: false,
        isSubmitting: false,
        stats: {
            wallet_balance: {{ $stats['wallet_balance'] ?? 0 }},
            pending_amount: {{ $stats['pending_amount'] ?? 0 }},
            total_packages: {{ $stats['total_packages'] ?? 0 }},
            packages_in_progress: {{ $stats['packages_in_progress'] ?? 0 }},
            packages_delivered: {{ $stats['packages_delivered'] ?? 0 }},
            complaints: {{ $stats['complaints'] ?? 0 }},
            pending_complaints: {{ $stats['pending_complaints'] ?? 0 }}
        },
        walletForm: {
            action: 'add',
            amount: '',
            description: ''
        },

        init() {
            this.loadStats();
            setInterval(() => this.loadStats(), 60000);
        },

        async loadStats() {
            try {
                const response = await fetch(`/commercial/clients/${CLIENT_ID}/api/stats`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                
                if (response.ok) {
                    const newStats = await response.json();
                    this.stats = { ...this.stats, ...newStats };
                }
            } catch (error) {
                console.error('Erreur chargement stats:', error);
            }
        },

        setActiveTab(tab) {
            this.activeTab = tab;
            this.updateUrl(tab);
        },

        updateUrl(tab) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        getSuccessRate() {
            if (this.stats.total_packages === 0) return 0;
            return Math.round((this.stats.packages_delivered / this.stats.total_packages) * 100);
        },

        // Modal Management
        openWalletModal() {
            this.isWalletModalOpen = true;
            document.body.style.overflow = 'hidden';
        },

        openAddFundsModal() {
            this.walletForm.action = 'add';
            this.openWalletModal();
        },

        openDeductFundsModal() {
            this.walletForm.action = 'deduct';
            this.openWalletModal();
        },

        closeWalletModal() {
            this.isWalletModalOpen = false;
            document.body.style.overflow = 'auto';
            this.resetWalletForm();
        },

        resetWalletForm() {
            this.walletForm = {
                action: 'add',
                amount: '',
                description: ''
            };
        },

        // SOLUTION DÉFINITIVE - TRANSACTION WALLET
        async submitWalletForm() {
            // Validation des champs
            if (!this.walletForm.amount || !this.walletForm.description) {
                this.showToast('Tous les champs sont requis', 'error');
                return;
            }

            // Validation du montant
            const amount = parseFloat(this.walletForm.amount);
            if (amount <= 0) {
                this.showToast('Le montant doit être positif', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const endpoint = this.walletForm.action === 'add' ? 'add' : 'deduct';
                
                console.log('Envoi de la requête:', {
                    url: `/commercial/clients/${CLIENT_ID}/wallet/${endpoint}`,
                    amount: this.walletForm.amount,
                    description: this.walletForm.description
                });

                const response = await fetch(`/commercial/clients/${CLIENT_ID}/wallet/${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: this.walletForm.amount,
                        description: this.walletForm.description
                    })
                });

                console.log('Réponse reçue:', response.status);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Données reçues:', data);
                    
                    // Fermer le modal
                    this.closeWalletModal();
                    
                    // Afficher le message de succès
                    this.showToast('Transaction effectuée avec succès', 'success');
                    
                    // Recharger les stats
                    await this.loadStats();
                    
                } else {
                    // Erreur HTTP
                    let errorMessage = 'Erreur lors de la transaction';
                    
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        console.error('Erreur parsing JSON:', e);
                    }
                    
                    console.error('Erreur HTTP:', response.status, errorMessage);
                    this.showToast(errorMessage, 'error');
                }

            } catch (error) {
                console.error('Erreur réseau ou JavaScript:', error);
                this.showToast('Erreur de connexion: ' + error.message, 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Actions
        createPackage() {
            window.location.href = `/commercial/packages/create?client_id=${CLIENT_ID}`;
        },

        duplicateClient() {
            if (confirm('Créer un nouveau client basé sur les informations de ce client ?')) {
                window.location.href = `/commercial/clients/create?duplicate=${CLIENT_ID}`;
            }
        },

        exportWalletHistory() {
            window.open(`/commercial/clients/${CLIENT_ID}/wallet/export`, '_blank');
        },

        async suspendClient() {
            const reason = prompt('Motif de suspension:');
            if (!reason) return;

            try {
                const response = await fetch(`/commercial/clients/${CLIENT_ID}/suspend`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ reason })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showToast('Compte client suspendu avec succès', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.showToast(data.message || 'Erreur lors de la suspension', 'error');
                }
            } catch (error) {
                this.showToast('Erreur de connexion', 'error');
            }
        },

        showToast(message, type = 'info') {
            if (window.showToast) {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    };
}

// Toast Manager Component
function toastManager() {
    return {
        toasts: [],
        nextId: 1,

        init() {
            window.showToast = (message, type = 'info') => {
                this.addToast(message, type);
            };
        },

        addToast(message, type = 'info') {
            const toast = {
                id: this.nextId++,
                message,
                type,
                show: true
            };

            this.toasts.push(toast);

            setTimeout(() => {
                this.removeToast(toast.id);
            }, 5000);
        },

        removeToast(id) {
            const index = this.toasts.findIndex(toast => toast.id === id);
            if (index > -1) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 300);
            }
        }
    };
}

// Global Functions
async function validateClient() {
    if (!confirm('Êtes-vous sûr de vouloir valider ce compte client ?')) {
        return;
    }

    try {
        const response = await fetch(`/commercial/clients/${CLIENT_ID}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                notes: 'Compte validé depuis le profil client'
            })
        });

        const data = await response.json();

        if (response.ok) {
            window.showToast('Compte client validé avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast(data.message || 'Erreur lors de la validation', 'error');
        }
    } catch (error) {
        window.showToast('Erreur de connexion', 'error');
    }
}

// Laravel Success/Error Messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        setTimeout(() => {
            if (window.showToast) {
                window.showToast('{{ session('success') }}', 'success');
            }
        }, 100);
    @endif

    @if(session('error'))
        setTimeout(() => {
            if (window.showToast) {
                window.showToast('{{ session('error') }}', 'error');
            }
        }, 100);
    @endif

    @if($errors->any())
        setTimeout(() => {
            if (window.showToast) {
                window.showToast('{{ $errors->first() }}', 'error');
            }
        }, 100);
    @endif
});
</script>
@endpush