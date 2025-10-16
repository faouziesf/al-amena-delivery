@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- En-tête avec salutation -->
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-3 sm:mb-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                    Bonjour, {{ $user->name }} 👋
                </h1>
                <p class="text-gray-600 text-sm">Voici un aperçu de votre activité aujourd'hui</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xs text-gray-500">{{ now()->format('l j F Y') }}</p>
                <p class="text-base font-semibold text-purple-600">{{ now()->format('H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">

        <!-- Solde Portefeuille -->
        <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl p-3 sm:p-4 text-white">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-sm sm:text-base font-bold">{{ number_format($stats['wallet_balance'], 3) }} DT</p>
                    <p class="text-xs opacity-80">Solde</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-between text-xs opacity-80 space-y-1 sm:space-y-0">
                <span>En attente: {{ number_format($stats['wallet_pending'], 3) }} DT</span>
                @if($stats['wallet_pending'] > 0)
                    <span class="bg-yellow-400/20 px-2 py-1 rounded-full text-xs self-start sm:self-center">{{ $stats['wallet_pending'] }}</span>
                @endif
            </div>
        </div>

        <!-- Colis en cours -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['in_progress_packages'] }}</p>
                    <p class="text-xs text-gray-500">En cours</p>
                </div>
            </div>
            <div class="text-xs text-gray-600">
                Total: {{ $stats['total_packages'] }} colis
            </div>
        </div>

        <!-- Demandes de collecte -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['pending_pickups'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Attente</p>
                </div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>Assignées: {{ $stats['assigned_pickups'] ?? 0 }}</span>
                <span>Terminées: {{ $stats['completed_pickups'] ?? 0 }}</span>
            </div>
        </div>

        <!-- Taux de réussite -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['success_rate'] }}%</p>
                    <p class="text-xs text-gray-500">Réussite</p>
                </div>
            </div>
            <div class="text-xs text-gray-600">
                {{ $stats['delivered_packages'] }}/{{ $stats['total_packages'] }} livrés
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        <!-- Activité récente -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">

            <!-- Colis récents -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Colis récents</h3>
                        <a href="{{ route('client.packages.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                            Voir tout →
                        </a>
                    </div>
                </div>
                <div class="p-3 sm:p-4">
                    @if($recentPackages->count() > 0)
                        <div class="space-y-2 sm:space-y-3">
                            @foreach($recentPackages as $package)
                                <div class="flex items-center justify-between p-2.5 sm:p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <div class="p-1.5 bg-purple-100 rounded-lg">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">#{{ $package->package_code }}</p>
                                            <p class="text-xs text-gray-500">{{ $package->recipient_name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                                            @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                                            @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                                        </span>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $package->created_at ? $package->created_at->diffForHumans() : 'N/A' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                            <p class="text-gray-500">Aucun colis pour le moment</p>
                            <a href="{{ route('client.packages.create') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                                Créer votre premier colis →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Demandes de collecte récentes -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Demandes de collecte</h3>
                        <a href="{{ route('client.pickup-requests.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Voir tout →
                        </a>
                    </div>
                </div>
                <div class="p-3 sm:p-4">
                    @if($recentPickupRequests->count() > 0)
                        <div class="space-y-2 sm:space-y-3">
                            @foreach($recentPickupRequests as $pickup)
                                <div class="flex items-center justify-between p-2.5 sm:p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <div class="p-1.5 bg-blue-100 rounded-lg">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">#{{ $pickup->id }}</p>
                                            <p class="text-xs text-gray-500">{{ $pickup->packages_count ?? 0 }} colis</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($pickup->status === 'picked_up') bg-green-100 text-green-800
                                            @elseif($pickup->status === 'assigned') bg-blue-100 text-blue-800
                                            @elseif($pickup->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            @if($pickup->status === 'pending') En attente
                                            @elseif($pickup->status === 'assigned') Assignée
                                            @elseif($pickup->status === 'picked_up') Collectée
                                            @elseif($pickup->status === 'cancelled') Annulée
                                            @else {{ $pickup->status }} @endif
                                        </span>
                                        <p class="text-sm text-gray-500 mt-1">{{ $pickup->created_at ? $pickup->created_at->diffForHumans() : 'Date non disponible' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <p class="text-gray-500">Aucune demande de collecte</p>
                            <a href="{{ route('client.pickup-requests.create') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                Créer votre première demande →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Colis d'échange retournés -->
            @if(($exchangeReturnedPackages ?? collect())->count() > 0)
            <div class="bg-white rounded-2xl border border-orange-200 shadow-sm">
                <div class="p-6 border-b border-orange-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-lg font-semibold text-gray-900">Colis d'Échange Retournés</h3>
                            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">{{ $exchangeReturnedPackages->count() }}</span>
                        </div>
                        <a href="{{ route('client.packages.index', ['exchange_returned' => 1]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                            Voir tout →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($exchangeReturnedPackages as $package)
                            <div class="flex items-center justify-between p-4 bg-orange-50 rounded-xl border border-orange-200">
                                <div class="flex items-center space-x-4">
                                    <div class="p-2 bg-orange-100 rounded-lg">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">#{{ $package->package_code }}</p>
                                        <p class="text-sm text-gray-500">{{ $package->formatted_recipient ?? 'Destinataire non spécifié' }}</p>
                                        <p class="text-xs text-orange-600 font-medium">🔄 Échange retourné</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Retourné
                                    </span>
                                    <p class="text-sm text-gray-500 mt-1">{{ $package->updated_at->diffForHumans() }}</p>
                                    <p class="text-xs text-gray-400">COD: {{ number_format($package->cod_amount, 3) }} DT</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 p-3 bg-orange-50 rounded-lg border border-orange-200">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-orange-800">Information sur les échanges</p>
                                <p class="text-xs text-orange-600 mt-1">
                                    Ces colis d'échange ont été livrés puis retournés selon vos instructions.
                                    Le montant COD sera traité selon votre contrat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar droite -->
        <div class="space-y-6">

            <!-- Actions rapides -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
                <div class="space-y-3">
                    <a href="{{ route('client.packages.create') }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouveau Colis
                    </a>
                    <a href="{{ route('client.pickup-requests.create') }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Demande Collecte
                    </a>
                    <a href="{{ route('client.wallet.index') }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Mon Portefeuille
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            @if($notifications->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                    <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">{{ $notifications->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($notifications->take(3) as $notification)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at ? $notification->created_at->diffForHumans() : 'Date non disponible' }}</p>
                        </div>
                    @endforeach
                </div>
                @if($notifications->count() > 3)
                    <div class="mt-4 text-center">
                        <a href="{{ route('client.notifications.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                            Voir toutes les notifications →
                        </a>
                    </div>
                @endif
            </div>
            @endif

            <!-- Transactions récentes -->
            @if($recentTransactions->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Transactions récentes</h3>
                    <a href="{{ route('client.wallet.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        Voir tout →
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($recentTransactions->take(3) as $transaction)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $transaction->description }}</p>
                                <p class="text-xs text-gray-500">{{ $transaction->completed_at ? $transaction->completed_at->diffForHumans() : $transaction->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-{{ $transaction->amount > 0 ? 'green' : 'red' }}-600">
                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh des statistiques toutes les 30 secondes
    setInterval(() => {
        fetch('{{ route('client.dashboard.api.stats') }}')
            .then(response => response.json())
            .then(data => {
                // Mettre à jour les statistiques dans Alpine.js si nécessaire
                if (window.Alpine && window.Alpine.store) {
                    window.Alpine.store('stats', data);
                }
            })
            .catch(error => console.log('Erreur lors du refresh des stats:', error));
    }, 30000);
</script>
@endpush