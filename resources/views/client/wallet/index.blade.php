@extends('layouts.client')

@section('title', 'Mon Wallet')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Mon Wallet</h1>
            <p class="mt-1 text-sm text-purple-600">
                Gérez votre solde et consultez l'historique de vos transactions
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                </svg>
                Demander un retrait
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Résumé du Wallet -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Solde disponible -->
        <x-client.stats-card 
            title="Solde Disponible"
            :value="number_format($walletStats['balance'], 3) . ' DT'"
            :subtitle="$walletStats['pending_amount'] > 0 ? number_format($walletStats['pending_amount'], 3) . ' DT en attente' : null"
            color="purple"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3&quot; />' " />

        <!-- Entrées du mois -->
        <x-client.stats-card 
            title="Entrées ce mois"
            :value="number_format($walletStats['monthly_credits'], 3) . ' DT'"
            subtitle="Montants crédités"
            color="green"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941&quot; />' " />

        <!-- Sorties du mois -->
        <x-client.stats-card 
            title="Sorties ce mois"
            :value="number_format(abs($walletStats['monthly_debits']), 3) . ' DT'"
            subtitle="Montants débités"
            color="red"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941&quot; />' " />
    </div>

    <!-- Filtres et Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
        <form method="GET" action="{{ route('client.wallet') }}" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filtre par type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type de transaction</label>
                    <select name="type" 
                            id="type"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-xl">
                        <option value="">Toutes les transactions</option>
                        <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Entrées uniquement</option>
                        <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Sorties uniquement</option>
                    </select>
                </div>

                <!-- Filtre par mois -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Mois</label>
                    <input type="month" 
                           name="month" 
                           id="month" 
                           value="{{ request('month') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Filtrer
                    </button>
                    
                    @if(request()->hasAny(['type', 'month']))
                        <a href="{{ route('client.wallet') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </div>

            <!-- Résumé des filtres -->
            @if(request()->hasAny(['type', 'month']))
                <div class="text-sm text-purple-600 bg-purple-50 p-3 rounded-lg">
                    <strong>Filtres actifs:</strong>
                    @if(request('type'))
                        {{ request('type') === 'credit' ? 'Entrées uniquement' : 'Sorties uniquement' }}
                    @endif
                    @if(request('month'))
                        @if(request('type')), @endif
                        Mois: {{ \Carbon\Carbon::parse(request('month'))->format('F Y') }}
                    @endif
                    • {{ $transactions->total() }} transaction(s) trouvée(s)
                </div>
            @endif
        </form>
    </div>

    <!-- Historique des transactions -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 overflow-hidden">
        @if($transactions->count() > 0)
            <!-- En-tête -->
            <div class="px-6 py-4 border-b border-purple-100 bg-purple-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-purple-900">Historique des Transactions</h3>
                    <p class="text-sm text-purple-600">{{ $transactions->total() }} transaction(s)</p>
                </div>
            </div>

            <!-- Liste des transactions -->
            <div class="divide-y divide-purple-100">
                @foreach($transactions as $transaction)
                    @php
                        $isCredit = $transaction->amount > 0;
                        $amount = abs($transaction->amount);
                    @endphp
                    
                    <div class="px-6 py-4 hover:bg-purple-50 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0 flex-1">
                                <!-- Icône -->
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-xl {{ $isCredit ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                        @if($isCredit)
                                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Détails -->
                                <div class="ml-4 min-w-0 flex-1">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900">
                                            @switch($transaction->type)
                                                @case('PACKAGE_CREATION_DEBIT')
                                                    Création de colis
                                                    @break
                                                @case('PACKAGE_DELIVERY_CREDIT')
                                                    Livraison de colis
                                                    @break
                                                @case('WALLET_RECHARGE')
                                                    Recharge de wallet
                                                    @break
                                                @case('WALLET_WITHDRAWAL')
                                                    Retrait de wallet
                                                    @break
                                                @case('REFUND')
                                                    Remboursement
                                                    @break
                                                @case('ADJUSTMENT')
                                                    Ajustement
                                                    @break
                                                @default
                                                    {{ $transaction->type }}
                                            @endswitch
                                        </p>
                                        
                                        @if($transaction->package)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $transaction->package->package_code }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                        <span>{{ $transaction->completed_at->format('d/m/Y H:i') }}</span>
                                        
                                        @if($transaction->reference)
                                            <span>Réf: {{ $transaction->reference }}</span>
                                        @endif
                                        
                                        @if($transaction->description)
                                            <span class="truncate max-w-xs" title="{{ $transaction->description }}">
                                                {{ $transaction->description }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Montant et solde -->
                            <div class="flex-shrink-0 text-right">
                                <p class="text-lg font-semibold {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $isCredit ? '+' : '-' }}{{ number_format($amount, 3) }} DT
                                </p>
                                
                                @if($transaction->wallet_balance_after)
                                    <p class="text-sm text-gray-500">
                                        Solde: {{ number_format($transaction->wallet_balance_after, 3) }} DT
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Détails additionnels pour certains types -->
                        @if($transaction->metadata)
                            @php
                                $metadata = is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : $transaction->metadata;
                            @endphp
                            
                            @if(isset($metadata['escrow_type']) || isset($metadata['package_code']))
                                <div class="mt-3 px-14">
                                    <div class="bg-gray-50 p-3 rounded-lg text-xs text-gray-600">
                                        @if(isset($metadata['escrow_type']))
                                            <span class="font-medium">Type:</span> 
                                            {{ $metadata['escrow_type'] === 'return_fee' ? 'Frais de retour' : 'Frais de livraison' }}
                                        @endif
                                        
                                        @if(isset($metadata['package_code']))
                                            @if(isset($metadata['escrow_type']))<span class="mx-2">•</span>@endif
                                            <span class="font-medium">Colis:</span> {{ $metadata['package_code'] }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- État vide -->
            <div class="px-6 py-12 text-center">
                <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune transaction trouvée</h3>
                
                @if(request()->hasAny(['type', 'month']))
                    <p class="text-gray-500 mb-4">
                        Aucune transaction ne correspond à vos critères de recherche.
                        <a href="{{ route('client.wallet') }}" class="text-purple-600 hover:text-purple-800 font-medium">
                            Voir toutes les transactions
                        </a>
                    </p>
                @else
                    <p class="text-gray-500 mb-4">
                        Aucune transaction n'a encore été effectuée sur votre wallet.
                    </p>
                @endif

                <a href="{{ route('client.packages.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Créer votre premier colis
                </a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($transactions->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-xl shadow-sm border border-purple-100 px-6 py-3">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Auto-submit form on select change
    document.getElementById('type').addEventListener('change', function() {
        this.form.submit();
    });
    
    document.getElementById('month').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush