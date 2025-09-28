@extends('layouts.deliverer')

@section('title', 'Historique Wallet')

@section('content')
<div class="p-4 sm:p-6 pb-24 space-y-6" x-data="{ showFilters: false }">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Historique du Wallet</h1>
            <p class="text-gray-500 mt-1">Toutes vos transactions détaillées</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3">
            <button @click="showFilters = !showFilters" class="btn-primary flex items-center justify-center w-full md:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path></svg>
                <span x-text="showFilters ? 'Cacher les Filtres' : 'Afficher les Filtres'"></span>
            </button>
            <button onclick="exportTransactions()" class="btn-primary flex items-center justify-center w-full md:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Exporter</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div x-show="showFilters" x-transition class="bg-purple-50 rounded-2xl shadow-sm border border-purple-200 p-5">
        <form method="GET" class="grid grid-cols-1 gap-4">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field" placeholder="Date début">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field" placeholder="Date fin">
            <select name="type" class="input-field">
                <option value="">Tous les types</option>
                <option value="COD_COLLECTION" @if(request('type') == 'COD_COLLECTION') selected @endif>COD Collecté</option>
                <option value="WALLET_EMPTYING" @if(request('type') == 'WALLET_EMPTYING') selected @endif>Vidage Wallet</option>
            </select>
            <button type="submit" class="btn-primary w-full">Appliquer les Filtres</button>
        </form>
    </div>

    <!-- Stats for filtered period -->
    @if($filteredStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
                <p class="text-sm font-medium text-blue-700">Transactions</p>
                <p class="text-2xl font-bold text-blue-900">{{ $filteredStats['total_transactions'] }}</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <p class="text-sm font-medium text-green-700">Total Crédits</p>
                <p class="text-2xl font-bold text-green-900">{{ number_format($filteredStats['total_credits'], 3) }} DT</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 text-center">
                <p class="text-sm font-medium text-red-700">Total Débits</p>
                <p class="text-2xl font-bold text-red-900">{{ number_format($filteredStats['total_debits'], 3) }} DT</p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 text-center">
                <p class="text-sm font-medium text-purple-700">Montant Net</p>
                <p class="text-2xl font-bold {{ $filteredStats['net_amount'] >= 0 ? 'text-purple-900' : 'text-red-900' }}">{{ number_format($filteredStats['net_amount'], 3) }} DT</p>
            </div>
        </div>
    @endif

    <!-- Transaction List -->
    <div class="space-y-3">
        @forelse($transactions as $transaction)
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 {{ $transaction->amount >= 0 ? 'border-green-500' : 'border-red-500' }}">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-center">
                    <div class="md:hidden col-span-1 text-right">
                        <p class="text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                        </p>
                    </div>
                    <div class="col-span-1 md:col-span-1">
                        <p class="text-sm font-medium text-gray-800">{{ $transaction->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</p>
                    </div>
                    <div class="col-span-2 md:col-span-2">
                        <p class="text-sm font-medium text-gray-800">{{ $transaction->description }}</p>
                        @if($transaction->package)
                            <p class="text-xs text-purple-600 font-mono">{{ $transaction->package->package_code }}</p>
                        @endif
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 3) }} DT
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $transaction->status === 'COMPLETED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $transaction->status }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl">
                <p class="text-gray-500">Aucune transaction trouvée pour cette période.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $transactions->appends(request()->query())->links() }}
    </div>
</div>



@push('scripts')
<script>
function exportTransactions() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route("deliverer.wallet.export") }}?${params.toString()}`;
}
</script>
@endpush
@endsection