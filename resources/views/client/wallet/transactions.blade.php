@extends('layouts.client')

@section('title', 'Historique des transactions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">📋 Historique des transactions</h1>
            <p class="text-gray-600">Consultez tous les mouvements de votre portefeuille</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <a href="{{ route('client.wallet.export') }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                📊 Exporter
            </a>
            <a href="{{ route('client.wallet.index') }}" 
               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                ← Retour au portefeuille
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtres</h3>
        <form method="GET" action="{{ route('client.wallet.transactions') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Type de transaction -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" id="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tous les types</option>
                    <option value="CREDIT" {{ request('type') === 'CREDIT' ? 'selected' : '' }}>Crédits</option>
                    <option value="DEBIT" {{ request('type') === 'DEBIT' ? 'selected' : '' }}>Débits</option>
                    <option value="PACKAGE_PAYMENT" {{ request('type') === 'PACKAGE_PAYMENT' ? 'selected' : '' }}>Paiement colis</option>
                    <option value="WITHDRAWAL" {{ request('type') === 'WITHDRAWAL' ? 'selected' : '' }}>Retraits</option>
                </select>
            </div>

            <!-- Statut -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" id="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Terminé</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>En attente</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>

            <!-- Date de début -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Du</label>
                <input type="date" 
                       name="date_from" 
                       id="date_from" 
                       value="{{ request('date_from') }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Date de fin -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Au</label>
                <input type="date" 
                       name="date_to" 
                       id="date_to" 
                       value="{{ request('date_to') }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Boutons -->
            <div class="md:col-span-4 flex space-x-3">
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    🔍 Filtrer
                </button>
                <a href="{{ route('client.wallet.transactions') }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                    🗑️ Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Statistiques des transactions filtrées -->
    @php
        $filteredTransactions = $transactions->getCollection();
        $totalCredits = $filteredTransactions->where('amount', '>', 0)->sum('amount');
        $totalDebits = abs($filteredTransactions->where('amount', '<', 0)->sum('amount'));
        $netAmount = $totalCredits - $totalDebits;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total crédité</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($totalCredits, 3) }} DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-red-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total débité</p>
                    <p class="text-lg font-bold text-red-600">{{ number_format($totalDebits, 3) }} DT</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Solde net</p>
                    <p class="text-lg font-bold {{ $netAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($netAmount, 3) }} DT
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des transactions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    📊 Transactions 
                    <span class="text-sm font-normal text-gray-500">
                        ({{ $transactions->total() }} résultats)
                    </span>
                </h3>
                <div class="text-sm text-gray-500">
                    Page {{ $transactions->currentPage() }} sur {{ $transactions->lastPage() }}
                </div>
            </div>
        </div>

        @if($transactions->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($transactions as $transaction)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <!-- Icône et détails -->
                            <div class="flex items-start">
                                <!-- Icône du type de transaction -->
                                <div class="flex-shrink-0 mr-4">
                                    @if($transaction->amount > 0)
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            @if($transaction->type === 'PACKAGE_PAYMENT')
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    @else
                                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                            @if($transaction->type === 'WITHDRAWAL')
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Détails de la transaction -->
                                <div class="flex-1">
                                    <div class="flex items-center mb-1">
                                        <h4 class="text-lg font-semibold text-gray-900 mr-3">
                                            {{ $transaction->description }}
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $transaction->status_color }}">
                                            {{ $transaction->status_display }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 text-sm text-gray-600">
                                        <div>
                                            <span class="font-medium">ID:</span>
                                            <span class="font-mono">{{ $transaction->transaction_id }}</span>
                                        </div>

                                        <div>
                                            <span class="font-medium">Type:</span>
                                            {{ $transaction->type_display }}
                                        </div>

                                        @if($transaction->package_id)
                                            <div>
                                                <span class="font-medium">Colis:</span>
                                                <a href="{{ route('client.packages.show', $transaction->package_id) }}" 
                                                   class="text-blue-600 hover:text-blue-800">
                                                    {{ $transaction->package->package_code ?? '#'.$transaction->package_id }}
                                                </a>
                                            </div>
                                        @endif

                                        @if($transaction->completed_at)
                                            <div>
                                                <span class="font-medium">Traité le:</span>
                                                {{ $transaction->completed_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </div>

                                    @if($transaction->wallet_balance_before !== null && $transaction->wallet_balance_after !== null)
                                        <div class="mt-2 text-xs text-gray-500">
                                            Solde: {{ number_format($transaction->wallet_balance_before, 3) }} DT 
                                            → {{ number_format($transaction->wallet_balance_after, 3) }} DT
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Montant et date -->
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-xl font-bold {{ $transaction->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->formatted_amount }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $transaction->created_at->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $transaction->created_at->format('H:i:s') }}
                                </p>
                            </div>
                        </div>

                        <!-- Métadonnées supplémentaires si présentes -->
                        @if($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs font-medium text-gray-700 mb-2">Détails supplémentaires:</p>
                                <div class="text-xs text-gray-600">
                                    @foreach($transaction->metadata as $key => $value)
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <!-- État vide -->
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune transaction trouvée</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['type', 'status', 'date_from', 'date_to']))
                        Aucune transaction ne correspond à vos critères de recherche.
                    @else
                        Vous n'avez pas encore de transactions dans votre portefeuille.
                    @endif
                </p>
                @if(request()->hasAny(['type', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('client.wallet.transactions') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        🗑️ Supprimer les filtres
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des transactions
    const transactions = document.querySelectorAll('[class*="hover:bg-gray-50"]');
    transactions.forEach((transaction, index) => {
        transaction.style.opacity = '0';
        transaction.style.transform = 'translateX(20px)';
        setTimeout(() => {
            transaction.style.transition = 'all 0.5s ease';
            transaction.style.opacity = '1';
            transaction.style.transform = 'translateX(0)';
        }, index * 50);
    });

    // Mise en surbrillance des nouvelles transactions (moins de 24h)
    const now = new Date();
    transactions.forEach(transaction => {
        const dateElements = transaction.querySelectorAll('[class*="text-gray-400"], [class*="text-gray-500"]');
        dateElements.forEach(element => {
            const dateText = element.textContent.trim();
            // Si la transaction date d'aujourd'hui, ajouter un indicateur
            if (dateText.includes(now.toLocaleDateString('fr-FR'))) {
                const indicator = document.createElement('span');
                indicator.className = 'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2';
                indicator.textContent = 'Nouveau';
                element.appendChild(indicator);
            }
        });
    });
});
</script>
@endpush
@endsection