@extends('layouts.commercial')

@section('title', 'Gestion des Clients')
@section('page-title', 'Gestion des Clients')
@section('page-description', 'Créez et gérez vos comptes clients avec tarification personnalisée')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="exportClients()" 
            class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exporter CSV
    </button>
    <a href="{{ route('commercial.clients.create') }}"
       class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouveau Client
    </a>
</div>
@endsection

@section('content')
<div x-data="clientsApp()" x-init="init()">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Clients</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Comptes Actifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">En Attente</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                    @if(($stats['pending'] ?? 0) > 0)
                        <button onclick="window.location.href='{{ route('commercial.clients.index') }}?status=PENDING'" 
                                class="text-xs text-orange-600 hover:text-orange-800 font-medium">
                            Voir les comptes →
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-purple-100 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Mes Créations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['created_by_me'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-6 mb-6">
        <form method="GET" action="{{ route('commercial.clients.index') }}" 
              x-data="{ showAdvanced: false }" class="space-y-4">
            
            <!-- Basic Filters Row -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Nom, email, téléphone, boutique..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Tous les statuts</option>
                        <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>En attente</option>
                        <option value="SUSPENDED" {{ request('status') == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filtres</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="created_by_me" value="1" 
                                   {{ request('created_by_me') ? 'checked' : '' }}
                                   class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Mes créations</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('commercial.clients.index') }}" 
                       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Réinitialiser
                    </a>
                    <button type="button" @click="showAdvanced = !showAdvanced"
                            class="px-3 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters (Hidden by default) -->
            <div x-show="showAdvanced" x-transition class="border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secteur d'activité</label>
                        <input type="text" name="business_sector" value="{{ request('business_sector') }}"
                               placeholder="Ex: Commerce, Textile..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Solde wallet minimum</label>
                        <input type="number" name="min_wallet" value="{{ request('min_wallet') }}" step="0.001"
                               placeholder="0.000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Créé depuis</label>
                        <input type="date" name="created_after" value="{{ request('created_after') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Liste des Clients</h3>
                <div class="flex items-center space-x-3 text-sm text-gray-600">
                    <span>{{ $clients->total() }} clients au total</span>
                    <button @click="toggleView = !toggleView" 
                            class="p-1 hover:bg-purple-100 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @if($clients->count() > 0)
            <!-- Table View -->
            <div x-show="!toggleView" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Boutique/Fiscal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarifs (DT)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($clients as $client)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center shadow-sm">
                                            <span class="text-sm font-bold text-white">{{ substr($client->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $client->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $client->email }}</div>
                                <div class="text-sm text-gray-500">{{ $client->phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $client->clientProfile->shop_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $client->clientProfile->fiscal_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-medium text-green-600">{{ number_format($client->clientProfile->offer_delivery_price ?? 0, 3) }}</span> 
                                    / <span class="font-medium text-red-600">{{ number_format($client->clientProfile->offer_return_price ?? 0, 3) }}</span>
                                </div>
                                <div class="text-xs text-gray-500">Livraison / Retour</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($client->wallet->balance ?? 0, 3) }} DT</div>
                                @if(($client->wallet->pending_amount ?? 0) > 0)
                                    <div class="text-xs text-orange-600">
                                        Attente: {{ number_format($client->wallet->pending_amount, 3) }} DT
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($client->account_status === 'ACTIVE')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Actif
                                    </span>
                                @elseif($client->account_status === 'PENDING')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        En attente
                                    </span>
                                @elseif($client->account_status === 'SUSPENDED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Suspendu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $client->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $client->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- View Button -->
                                    <button onclick="viewClient({{ $client->id }})" 
                                            class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-100 transition-colors"
                                            title="Voir détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    <!-- Wallet Management -->
                                    <button onclick="manageWallet({{ $client->id }}, '{{ $client->name }}', {{ $client->wallet->balance ?? 0 }})" 
                                            class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                            title="Gérer wallet">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <a href="{{ route('commercial.clients.edit', $client) }}" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition-colors"
                                       title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Status Actions -->
                                    @if($client->account_status === 'PENDING')
                                        <button onclick="validateClient({{ $client->id }}, '{{ $client->name }}')" 
                                                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                title="Valider le compte">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    @if($client->account_status === 'ACTIVE')
                                        <button onclick="suspendClient({{ $client->id }}, '{{ $client->name }}')" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-100 transition-colors"
                                                title="Suspendre le compte">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- More Actions Dropdown -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" 
                                                class="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                            </svg>
                                        </button>
                                        
                                        <div x-show="open" @click.away="open = false" x-transition
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                            <div class="py-1">
                                                <button onclick="duplicateClient({{ $client->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Dupliquer
                                                </button>
                                                <button onclick="exportClientData({{ $client->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Exporter données
                                                </button>
                                                <hr class="my-1">
                                                <button onclick="viewClientHistory({{ $client->id }})" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Historique
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Card View (Alternative) -->
            <div x-show="toggleView" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($clients as $client)
                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow border border-purple-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center">
                                <span class="text-sm font-bold text-white">{{ substr($client->name, 0, 2) }}</span>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $client->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $client->clientProfile->shop_name ?? 'Pas de boutique' }}</p>
                            </div>
                        </div>
                        @if($client->account_status === 'ACTIVE')
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Actif</span>
                        @elseif($client->account_status === 'PENDING')
                            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">En attente</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Suspendu</span>
                        @endif
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium">{{ $client->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Téléphone:</span>
                            <span class="font-medium">{{ $client->phone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Wallet:</span>
                            <span class="font-medium text-green-600">{{ number_format($client->wallet->balance ?? 0, 3) }} DT</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tarifs:</span>
                            <span class="font-medium">{{ number_format($client->clientProfile->offer_delivery_price ?? 0, 3) }}/{{ number_format($client->clientProfile->offer_return_price ?? 0, 3) }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex space-x-2">
                        <button onclick="viewClient({{ $client->id }})" 
                                class="flex-1 bg-purple-600 text-white text-sm py-2 px-3 rounded hover:bg-purple-700 transition-colors">
                            Voir
                        </button>
                        <button onclick="manageWallet({{ $client->id }}, '{{ $client->name }}', {{ $client->wallet->balance ?? 0 }})" 
                                class="flex-1 bg-green-600 text-white text-sm py-2 px-3 rounded hover:bg-green-700 transition-colors">
                            Wallet
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $clients->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun client trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status', 'created_by_me']))
                        Aucun client ne correspond à vos critères de recherche.
                    @else
                        Commencez par créer votre premier client.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status', 'created_by_me']))
                        <a href="{{ route('commercial.clients.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Voir tous les clients
                        </a>
                    @else
                        <a href="{{ route('commercial.clients.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nouveau Client
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
<!-- Wallet Management Modal -->
<div id="wallet-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900" id="wallet-title">Gérer Wallet</h3>
                <button onclick="closeWalletModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="p-6">
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-600">Solde actuel</div>
                    <div class="text-2xl font-bold text-gray-900" id="current-balance">0.000 DT</div>
                </div>
                
                <form id="wallet-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                        <select id="wallet-action" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="add">Ajouter des fonds</option>
                            <option value="deduct">Déduire des fonds</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant (DT)</label>
                        <input type="number" id="wallet-amount" step="0.001" min="0.001" required 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="wallet-description" rows="3" required
                                  placeholder="Motif de l'ajustement..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700">
                            Confirmer
                        </button>
                        <button type="button" onclick="closeWalletModal()" 
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function clientsApp() {
    return {
        toggleView: false,
        selectedClients: [],
        
        init() {
            // Initialize any client-specific functionality
        }
    }
}

// Global client management functions
let currentClientId = null;

function viewClient(clientId) {
    window.location.href = `/commercial/clients/${clientId}`;
}

function manageWallet(clientId, clientName, currentBalance) {
    currentClientId = clientId;
    document.getElementById('wallet-title').textContent = `Wallet - ${clientName}`;
    document.getElementById('current-balance').textContent = `${parseFloat(currentBalance).toFixed(3)} DT`;
    document.getElementById('wallet-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeWalletModal() {
    document.getElementById('wallet-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('wallet-form').reset();
    currentClientId = null;
}

async function validateClient(clientId, clientName) {
    if (!confirm(`Êtes-vous sûr de vouloir valider le compte de ${clientName} ?`)) {
        return;
    }

    try {
        const response = await fetch(`/commercial/clients/${clientId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notes: 'Compte validé via interface commercial'
            })
        });

        if (response.ok) {
            showToast('Compte client validé avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showToast(data.message || 'Erreur lors de la validation', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

async function suspendClient(clientId, clientName) {
    const reason = prompt(`Motif de suspension pour ${clientName}:`);
    if (!reason) return;

    try {
        const response = await fetch(`/commercial/clients/${clientId}/suspend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason })
        });

        if (response.ok) {
            showToast('Compte client suspendu avec succès', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const data = await response.json();
            showToast(data.message || 'Erreur lors de la suspension', 'error');
        }
    } catch (error) {
        showToast('Erreur de connexion', 'error');
    }
}

function duplicateClient(clientId) {
    if (confirm('Créer un nouveau client basé sur les informations de ce client ?')) {
        window.location.href = `/commercial/clients/create?duplicate=${clientId}`;
    }
}

function exportClientData(clientId) {
    window.open(`/commercial/clients/${clientId}/export`, '_blank');
}

function viewClientHistory(clientId) {
    window.location.href = `/commercial/clients/${clientId}?tab=history`;
}

function exportClients() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open(`${window.location.pathname}?${params.toString()}`, '_blank');
}

// Wallet form submission
document.addEventListener('DOMContentLoaded', function() {
    const walletForm = document.getElementById('wallet-form');
    if (walletForm) {
        walletForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!currentClientId) return;
            
            const action = document.getElementById('wallet-action').value;
            const amount = parseFloat(document.getElementById('wallet-amount').value);
            const description = document.getElementById('wallet-description').value;
            
            if (!amount || !description) {
                showToast('Tous les champs sont requis', 'error');
                return;
            }
            
            try {
                const endpoint = action === 'add' ? 'add' : 'deduct';
                const response = await fetch(`/commercial/clients/${currentClientId}/wallet/${endpoint}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ amount, description })
                });

                if (response.ok) {
                    showToast(`Wallet mis à jour avec succès`, 'success');
                    closeWalletModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        });
    }
});
</script>
@endpush