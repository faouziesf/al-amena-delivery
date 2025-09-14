@extends('layouts.client')

@section('title', 'Dashboard Client')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">
                Bonjour, {{ auth()->user()->name }} 👋
            </h1>
            <p class="mt-1 text-sm text-purple-600">
                Voici un aperçu de votre activité et de vos colis
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.packages.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nouveau Colis
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Solde Wallet -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-xl bg-purple-gradient flex items-center justify-center shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Solde Wallet</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($stats['wallet_balance'], 3) }} DT</p>
                    @if($stats['wallet_pending'] > 0)
                        <p class="text-xs text-orange-600">{{ number_format($stats['wallet_pending'], 3) }} DT en attente</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colis en cours -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-xl bg-orange-500 flex items-center justify-center shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Colis en cours</p>
                    <p class="text-2xl font-bold text-orange-900">{{ $stats['in_progress_packages'] }}</p>
                    <p class="text-xs text-gray-600">{{ $stats['monthly_packages'] }} ce mois</p>
                </div>
            </div>
        </div>

        <!-- Colis livrés -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-xl bg-green-500 flex items-center justify-center shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Colis livrés</p>
                    <p class="text-2xl font-bold text-green-900">{{ $stats['delivered_packages'] }}</p>
                    <p class="text-xs text-gray-600">{{ $stats['monthly_delivered'] }} ce mois</p>
                </div>
            </div>
        </div>

        <!-- Réclamations -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-xl bg-red-500 flex items-center justify-center shadow-md">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Réclamations</p>
                    <p class="text-2xl font-bold text-red-900">{{ $stats['pending_complaints'] }}</p>
                    <p class="text-xs text-gray-600">En attente de traitement</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Colis récents -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100">
                <div class="px-6 py-4 border-b border-purple-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-purple-900">Colis récents</h3>
                        <a href="{{ route('client.packages.index') }}" 
                           class="text-sm font-medium text-purple-600 hover:text-purple-700">
                            Voir tout →
                        </a>
                    </div>
                </div>
                
                <div class="divide-y divide-purple-100">
                    @forelse($recentPackages as $package)
                        <div class="px-6 py-4 hover:bg-purple-50 transition-colors duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-purple-900">
                                            {{ $package->package_code }}
                                        </p>
                                        @if(isset($package->status))
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($package->status === 'CREATED') bg-blue-100 text-blue-800
                                                @elseif($package->status === 'AVAILABLE') bg-yellow-100 text-yellow-800
                                                @elseif($package->status === 'ACCEPTED') bg-purple-100 text-purple-800
                                                @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                                                @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                                                @elseif($package->status === 'PAID') bg-green-100 text-green-800
                                                @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $package->status }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $package->recipient_data['name'] ?? 'N/A' }} • {{ $package->delegationTo->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $package->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-sm font-medium text-purple-900">
                                        {{ number_format($package->cod_amount, 3) }} DT
                                    </p>
                                    <a href="{{ route('client.packages.show', $package) }}" 
                                       class="text-xs text-purple-600 hover:text-purple-700">
                                        Voir détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="h-12 w-12 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Aucun colis récent</p>
                            <a href="{{ route('client.packages.create') }}" 
                               class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg text-xs font-medium text-white hover:bg-purple-700">
                                Créer votre premier colis
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Wallet Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Wallet</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Solde disponible</span>
                        <span class="font-semibold text-purple-900">{{ number_format($user->wallet_balance ?? 0, 3) }} DT</span>
                    </div>
                    
                    @if(($user->wallet_pending ?? 0) > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">En attente</span>
                            <span class="font-medium text-orange-600">{{ number_format($user->wallet_pending, 3) }} DT</span>
                        </div>
                    @endif
                    
                    <div class="pt-4 border-t border-purple-100">
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('client.wallet.index') }}" 
                               class="inline-flex justify-center items-center px-3 py-2 bg-purple-100 border border-transparent rounded-lg text-xs font-medium text-purple-700 hover:bg-purple-200">
                                Historique
                            </a>
                            <a href="{{ route('client.wallet.withdrawal') }}" 
                               class="inline-flex justify-center items-center px-3 py-2 bg-purple-600 border border-transparent rounded-lg text-xs font-medium text-white hover:bg-purple-700">
                                Retrait
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Actions rapides</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('client.packages.create') }}" 
                       class="flex items-center p-3 rounded-xl hover:bg-purple-50 transition-colors duration-200 group">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200">
                            <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Créer un colis</span>
                    </a>
                    
                    <a href="{{ route('client.packages.index', ['status' => 'in_progress']) }}" 
                       class="flex items-center p-3 rounded-xl hover:bg-purple-50 transition-colors duration-200 group">
                        <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center group-hover:bg-orange-200">
                            <svg class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0V8.25a1.5 1.5 0 013 0v10.5zM12 7.5V21M15.75 18.75a1.5 1.5 0 01-3 0V8.25a1.5 1.5 0 013 0v10.5z" />
                            </svg>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900">Suivre mes colis</span>
                    </a>
                    
                    @if($stats['pending_complaints'] > 0)
                        <a href="{{ route('client.complaints.index') }}" 
                           class="flex items-center p-3 rounded-xl hover:bg-purple-50 transition-colors duration-200 group">
                            <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center group-hover:bg-red-200">
                                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <span class="text-sm font-medium text-gray-900">Mes réclamations</span>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $stats['pending_complaints'] }}
                                </span>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Transactions récentes -->
            @if($recentTransactions->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Transactions récentes</h3>
                    
                    <div class="space-y-3">
                        @foreach($recentTransactions->take(3) as $transaction)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $transaction->description ?? 'Transaction' }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->completed_at ? $transaction->completed_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-purple-100">
                        <a href="{{ route('client.wallet.index') }}" 
                           class="text-sm font-medium text-purple-600 hover:text-purple-700">
                            Voir toutes les transactions →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alertes et notifications -->
    @if(auth()->user()->account_status !== 'ACTIVE')
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Compte en attente de validation.</strong>
                        Votre compte est en cours de vérification par notre équipe. Vous pourrez créer des colis une fois la validation terminée.
                    </p>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Actualisation automatique des stats
    setInterval(() => {
        fetch('/client/api/dashboard-stats', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Mise à jour des éléments de stats si nécessaire
            console.log('Stats updated:', data);
        })
        .catch(error => console.log('Erreur stats:', error));
    }, 60000); // Toutes les minutes
</script>
@endpush