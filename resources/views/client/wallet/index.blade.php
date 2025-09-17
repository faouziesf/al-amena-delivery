@extends('layouts.client')

@section('title', 'Mon Portefeuille')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header avec titre et actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">💰 Mon Portefeuille</h1>
            <p class="text-gray-600">Gérez votre solde et vos transactions</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                📤 Demander un retrait
            </a>
            <a href="{{ route('client.wallet.statement') }}" 
               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                📄 Relevé
            </a>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Cartes de statistiques du wallet -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Solde disponible -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Solde disponible</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ number_format($user->wallet->balance - ($user->wallet->frozen_amount ?? 0), 3) }} DT
                    </p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Montant en attente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">En attente</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ number_format($user->wallet->pending_amount ?? 0, 3) }} DT
                    </p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Montant gelé -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Gelé (retraits)</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ number_format($user->wallet->frozen_amount ?? 0, 3) }} DT
                    </p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Solde total -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Solde total</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ number_format($user->wallet->balance, 3) }} DT
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques supplémentaires -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Total crédité</h3>
            <p class="text-3xl font-bold text-green-600">
                {{ number_format($stats['total_credited'], 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Revenus accumulés</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💸 Total débité</h3>
            <p class="text-3xl font-bold text-red-600">
                {{ number_format($stats['total_debited'], 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Frais et retraits</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Solde net</h3>
            <p class="text-3xl font-bold {{ ($stats['total_credited'] - $stats['total_debited']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($stats['total_credited'] - $stats['total_debited'], 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Résultat global</p>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions rapides</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="bg-blue-600 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Demander un retrait</p>
                    <p class="text-sm text-gray-600">Virement ou espèces</p>
                </div>
            </a>

            <a href="{{ route('client.withdrawals') }}" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="bg-green-600 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Mes demandes</p>
                    <p class="text-sm text-gray-600">Suivi des retraits</p>
                </div>
            </a>

            <a href="{{ route('client.wallet.statement') }}" 
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="bg-purple-600 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a4 4 0 01-4 4z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Télécharger relevé</p>
                    <p class="text-sm text-gray-600">Export PDF</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Historique des transactions récentes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">📋 Transactions récentes</h3>
                <a href="{{ route('client.wallet.transactions') }}" 
                   class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Voir tout →
                </a>
            </div>
        </div>
        
        <div class="divide-y divide-gray-200">
            @forelse($transactions->take(10) as $transaction)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($transaction->amount > 0)
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $transaction->description }}
                                </p>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs px-2 py-1 rounded {{ $transaction->status_color }}">
                                        {{ $transaction->status_display }}
                                    </span>
                                    @if($transaction->package_id)
                                        <span class="text-xs text-gray-500 ml-2">
                                            Colis: {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->formatted_amount }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Aucune transaction pour le moment</p>
                </div>
            @endforelse
        </div>

        @if($transactions->count() > 10)
            <div class="px-6 py-4 bg-gray-50 rounded-b-xl">
                <a href="{{ route('client.wallet.transactions') }}" 
                   class="block text-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Voir les {{ $transactions->count() - 10 }} autres transactions →
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Actualisation automatique du solde toutes les 30 secondes
setInterval(function() {
    fetch('/client/api/wallet/balance')
        .then(response => response.json())
        .then(data => {
            if (data.balance !== undefined) {
                // Mettre à jour l'affichage du solde
                console.log('Solde mis à jour:', data.balance);
            }
        })
        .catch(error => console.error('Erreur actualisation solde:', error));
}, 30000);

// Animation des cartes au chargement
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('[class*="rounded-xl"]');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
@endsection