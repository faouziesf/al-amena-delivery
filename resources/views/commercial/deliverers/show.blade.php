@extends('layouts.commercial')

@section('title', 'Livreur - ' . $deliverer->name)
@section('page-title', 'Profil du Livreur')
@section('page-description', $deliverer->name . ' - ' . $deliverer->email)

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.deliverers.index') }}"
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>

    @if($deliverer->wallet && $deliverer->wallet->balance > 0)
    <button onclick="emptyWallet()"
            class="px-4 py-2 bg-orange-300 text-orange-800 rounded-lg hover:bg-orange-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Vider Portefeuille
    </button>
    @endif

    <button onclick="assignCashDelivery()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
        </svg>
        Assigner Livraison Cash
    </button>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="delivererShowApp()">

    <!-- En-tête avec informations principales -->
    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-xl shadow-lg text-purple-800 p-6 mb-8">
        <div class="flex items-center space-x-6">
            <!-- Avatar -->
            <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <span class="text-3xl font-bold text-purple-800">{{ substr($deliverer->name, 0, 2) }}</span>
            </div>

            <div class="flex-1">
                <h1 class="text-2xl font-bold">{{ $deliverer->name }}</h1>
                <p class="text-purple-700">{{ $deliverer->email }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                        {{ $deliverer->account_status === 'ACTIVE' ? 'bg-green-500 text-white' :
                           ($deliverer->account_status === 'PENDING' ? 'bg-orange-500 text-white' : 'bg-red-500 text-white') }}">
                        {{ $deliverer->account_status === 'ACTIVE' ? 'Actif' :
                           ($deliverer->account_status === 'PENDING' ? 'En attente' : 'Suspendu') }}
                    </span>
                    <span class="text-purple-700 text-sm">
                        Inscrit le {{ $deliverer->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            <!-- Solde du portefeuille -->
            <div class="text-right">
                <div class="text-3xl font-bold text-purple-800">
                    {{ number_format($deliverer->wallet->balance ?? 0, 3) }}
                </div>
                <div class="text-sm text-purple-700">Dinars Tunisiens</div>
                <div class="text-xs text-purple-600 mt-1">Solde portefeuille</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Informations personnelles -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informations Personnelles
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $deliverer->name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $deliverer->email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $deliverer->phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut du compte</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $deliverer->account_status === 'ACTIVE' ? 'bg-green-100 text-green-800' :
                                   ($deliverer->account_status === 'PENDING' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800') }}">
                                {{ $deliverer->account_status === 'ACTIVE' ? 'Actif' :
                                   ($deliverer->account_status === 'PENDING' ? 'En attente' : 'Suspendu') }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($deliverer->address)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700">Adresse</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $deliverer->address }}</div>
                </div>
                @endif
            </div>

            <!-- Statistiques de livraison -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Statistiques de Performance
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            {{ $stats['total_packages'] ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">Total colis</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ $stats['delivered_packages'] ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">Livrés</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">
                            {{ $stats['returned_packages'] ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-600">Retournés</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ $stats['success_rate'] ?? 0 }}%
                        </div>
                        <div class="text-sm text-gray-600">Taux de réussite</div>
                    </div>
                </div>
            </div>

            <!-- Colis récents -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Colis Récents
                </h3>

                @if($recentPackages && $recentPackages->count() > 0)
                <div class="space-y-3">
                    @foreach($recentPackages as $package)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ $package->tracking_number }}</div>
                            <div class="text-sm text-gray-600">
                                {{ $package->receiver_name }} - {{ number_format($package->cod_amount ?? 0, 3) }} DT
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $package->status === 'DELIVERED' ? 'bg-green-100 text-green-800' :
                                   ($package->status === 'RETURNED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $package->status }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $package->updated_at->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293L18 15H6l-2.707-1.707A1 1 0 002.586 13H0"/>
                    </svg>
                    Aucun colis récent
                </div>
                @endif
            </div>

            <!-- Historique des vidages de portefeuille -->
            @if($walletEmptyings && $walletEmptyings->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Historique des Vidages
                </h3>

                <div class="space-y-3">
                    @foreach($walletEmptyings as $emptying)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ number_format($emptying->amount, 3) }} DT</div>
                            <div class="text-sm text-gray-600">
                                {{ $emptying->created_at->format('d/m/Y à H:i') }}
                                @if($emptying->processedBy)
                                - par {{ $emptying->processedBy->name }}
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $emptying->status === 'COMPLETED' ? 'bg-green-100 text-green-800' :
                                   ($emptying->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $emptying->status === 'COMPLETED' ? 'Terminé' :
                                   ($emptying->status === 'PENDING' ? 'En attente' : 'Annulé') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Colonne de droite -->
        <div class="space-y-6">

            <!-- Informations du portefeuille -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Portefeuille</h3>

                @if($deliverer->wallet)
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Solde disponible:</span>
                        <span class="font-semibold text-green-600">{{ number_format($deliverer->wallet->balance, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Montant gelé:</span>
                        <span class="font-semibold text-orange-600">{{ number_format($deliverer->wallet->frozen_amount, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">En attente:</span>
                        <span class="font-semibold text-blue-600">{{ number_format($deliverer->wallet->pending_amount, 3) }} DT</span>
                    </div>

                    @if($deliverer->wallet->balance > 0)
                    <div class="pt-3 border-t border-gray-200">
                        <button onclick="emptyWallet()"
                                class="w-full px-4 py-2 bg-orange-300 text-orange-800 rounded-lg hover:bg-orange-400 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Vider le portefeuille
                        </button>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-4 text-gray-500">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Aucun portefeuille configuré
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    @if($deliverer->wallet && $deliverer->wallet->balance > 0)
                    <button onclick="emptyWallet()"
                            class="w-full px-4 py-2 bg-orange-300 text-orange-800 rounded-lg hover:bg-orange-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Vider Portefeuille
                    </button>
                    @endif

                    <button onclick="assignCashDelivery()"
                            class="w-full px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        Assigner Livraison Cash
                    </button>
                </div>
            </div>

            <!-- Récapitulatif -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Récapitulatif</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-purple-700">Statut:</span>
                        <span class="font-semibold text-purple-900">
                            {{ $deliverer->account_status === 'ACTIVE' ? 'Actif' :
                               ($deliverer->account_status === 'PENDING' ? 'En attente' : 'Suspendu') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Solde:</span>
                        <span class="font-semibold text-purple-900">{{ number_format($deliverer->wallet->balance ?? 0, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Colis livrés:</span>
                        <span class="text-purple-900">{{ $stats['delivered_packages'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Membre depuis:</span>
                        <span class="text-purple-900">{{ $deliverer->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function delivererShowApp() {
    return {
        deliverer: @json($deliverer),

        init() {
            // Initialisation si nécessaire
        }
    }
}

function emptyWallet() {
    const deliverer = window.delivererShowApp().deliverer;

    if (confirm(`Êtes-vous sûr de vouloir vider le portefeuille de ${deliverer.name} (${deliverer.wallet.balance} DT) ?`)) {
        fetch(`/commercial/deliverers/${deliverer.id}/empty-wallet`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Portefeuille vidé avec succès', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur lors du vidage', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    }
}

function assignCashDelivery() {
    // TODO: Implémenter l'assignment de livraison cash
    showToast('Fonctionnalité d\'assignment en cours de développement', 'info');
}
</script>
@endpush