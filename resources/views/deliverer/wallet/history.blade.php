@extends('layouts.deliverer')

@section('title', 'Historique Wallet')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">💰 Historique Wallet</h1>
                    <p class="text-gray-600 mt-1">Toutes vos transactions détaillées</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="exportTransactions()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exporter CSV
                    </button>
                    <a href="{{ route('deliverer.wallet.index') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white border-b p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Tous types</option>
                    <option value="COD_COLLECTION" {{ request('type') === 'COD_COLLECTION' ? 'selected' : '' }}>COD Collecté</option>
                    <option value="CLIENT_FUND_ADD" {{ request('type') === 'CLIENT_FUND_ADD' ? 'selected' : '' }}>Recharge Client</option>
                    <option value="WALLET_TOPUP" {{ request('type') === 'WALLET_TOPUP' ? 'selected' : '' }}>Recharge Wallet</option>
                    <option value="WALLET_EMPTYING" {{ request('type') === 'WALLET_EMPTYING' ? 'selected' : '' }}>Vidange Wallet</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant min</label>
                <input type="number" step="0.001" name="amount_min" value="{{ request('amount_min') }}"
                       placeholder="0.000" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant max</label>
                <input type="number" step="0.001" name="amount_max" value="{{ request('amount_max') }}"
                       placeholder="1000.000" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Stats période filtrée -->
    @if($filteredStats)
    <div class="p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-600">Transactions</p>
                <p class="text-2xl font-bold text-blue-600">{{ $filteredStats['total_transactions'] }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-600">Crédits</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($filteredStats['total_credits'], 3) }} DT</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-600">Débits</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($filteredStats['total_debits'], 3) }} DT</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-600">Net</p>
                <p class="text-2xl font-bold {{ $filteredStats['net_amount'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $filteredStats['net_amount'] >= 0 ? '+' : '' }}{{ number_format($filteredStats['net_amount'], 3) }} DT
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste transactions -->
    <div class="p-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">
                                            @if($transaction->type === 'COD_COLLECTION')
                                                💰
                                            @elseif($transaction->type === 'CLIENT_FUND_ADD')
                                                🔄
                                            @elseif($transaction->type === 'WALLET_TOPUP')
                                                ⬆️
                                            @elseif($transaction->type === 'WALLET_EMPTYING')
                                                ⬇️
                                            @else
                                                📊
                                            @endif
                                        </span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                @switch($transaction->type)
                                                    @case('COD_COLLECTION')
                                                        COD Collecté
                                                        @break
                                                    @case('CLIENT_FUND_ADD')
                                                        Recharge Client
                                                        @break
                                                    @case('WALLET_TOPUP')
                                                        Recharge Wallet
                                                        @break
                                                    @case('WALLET_EMPTYING')
                                                        Vidange Wallet
                                                        @break
                                                    @default
                                                        {{ $transaction->type }}
                                                @endswitch
                                            </div>
                                            @if($transaction->package)
                                                <div class="text-xs text-gray-500">{{ $transaction->package->package_code }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $transaction->description }}</div>
                                    @if($transaction->reference)
                                        <div class="text-xs text-gray-500">Réf: {{ $transaction->reference }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($transaction->status === 'COMPLETED') bg-green-100 text-green-800
                                        @elseif($transaction->status === 'PENDING') bg-yellow-100 text-yellow-800
                                        @elseif($transaction->status === 'FAILED') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @switch($transaction->status)
                                            @case('COMPLETED')
                                                ✅ Complété
                                                @break
                                            @case('PENDING')
                                                ⏳ En attente
                                                @break
                                            @case('FAILED')
                                                ❌ Échoué
                                                @break
                                            @default
                                                {{ $transaction->status }}
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if($transaction->package)
                                            <a href="{{ route('deliverer.packages.show', $transaction->package) }}"
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                Voir colis
                                            </a>
                                        @endif
                                        <button onclick="showTransactionDetails({{ $transaction->id }})"
                                                class="text-gray-600 hover:text-gray-800 text-sm">
                                            Détails
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune transaction trouvée</h3>
                    <p class="text-gray-600">Essayez de modifier vos critères de recherche</p>
                    <a href="{{ route('deliverer.wallet.history') }}"
                       class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Voir toutes les transactions
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal détails transaction -->
<div id="transactionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Détails Transaction</h3>
                <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="transactionDetails" class="space-y-3">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportTransactions() {
    const params = new URLSearchParams(window.location.search);
    const url = '{{ route("deliverer.wallet.export") }}?' + params.toString();
    window.location.href = url;
}

function showTransactionDetails(transactionId) {
    // Récupérer les détails via API (à implémenter)
    const modal = document.getElementById('transactionModal');
    const details = document.getElementById('transactionDetails');

    details.innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-full"></div>
        </div>
    `;

    modal.classList.remove('hidden');

    // TODO: Appel API pour récupérer les détails
    setTimeout(() => {
        details.innerHTML = `
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Transaction:</span>
                    <span class="font-medium">#${transactionId}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Type:</span>
                    <span class="font-medium">COD Collecté</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Montant:</span>
                    <span class="font-medium text-green-600">+45.500 DT</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Statut:</span>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Complété</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Date:</span>
                    <span class="font-medium">${new Date().toLocaleDateString('fr-FR')}</span>
                </div>
            </div>
        `;
    }, 500);
}

function closeTransactionModal() {
    document.getElementById('transactionModal').classList.add('hidden');
}

// Fermer modal en cliquant à l'extérieur
document.getElementById('transactionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTransactionModal();
    }
});
</script>
@endpush
@endsection