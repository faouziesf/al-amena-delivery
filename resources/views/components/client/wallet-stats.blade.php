@props([
    'wallet',
    'stats' => null,
    'showActions' => true,
    'compact' => false
])

@php
    $balanceColor = $wallet->balance > 0 ? 'text-green-600' : ($wallet->balance < 0 ? 'text-red-600' : 'text-gray-600');
    $availableBalance = $wallet->balance - ($wallet->frozen_amount ?? 0);
@endphp

<div class="grid grid-cols-1 {{ $compact ? 'md:grid-cols-2 lg:grid-cols-4' : 'md:grid-cols-2 lg:grid-cols-4' }} gap-{{ $compact ? '4' : '6' }}">
    <!-- Solde disponible -->
    <div class="bg-white rounded-{{ $compact ? 'lg' : 'xl' }} shadow-sm border border-gray-200 p-{{ $compact ? '4' : '6' }}">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Solde disponible</p>
                <p class="text-{{ $compact ? 'xl' : '2xl' }} font-bold {{ $availableBalance > 0 ? 'text-green-600' : 'text-gray-600' }}">
                    {{ number_format($availableBalance, 3) }} DT
                </p>
                @if(!$compact)
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $availableBalance > 0 ? 'Utilisable immédiatement' : 'Aucun fonds disponible' }}
                    </p>
                @endif
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-{{ $compact ? '5' : '6' }} h-{{ $compact ? '5' : '6' }} text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Montant en attente -->
    @if($wallet->pending_amount > 0 || !$compact)
        <div class="bg-white rounded-{{ $compact ? 'lg' : 'xl' }} shadow-sm border border-gray-200 p-{{ $compact ? '4' : '6' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">En attente</p>
                    <p class="text-{{ $compact ? 'xl' : '2xl' }} font-bold text-orange-600">
                        {{ number_format($wallet->pending_amount ?? 0, 3) }} DT
                    </p>
                    @if(!$compact)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $wallet->pending_amount > 0 ? 'Colis en cours de livraison' : 'Aucun montant en attente' }}
                        </p>
                    @endif
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <svg class="w-{{ $compact ? '5' : '6' }} h-{{ $compact ? '5' : '6' }} text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    <!-- Montant gelé -->
    @if($wallet->frozen_amount > 0 || !$compact)
        <div class="bg-white rounded-{{ $compact ? 'lg' : 'xl' }} shadow-sm border border-gray-200 p-{{ $compact ? '4' : '6' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Gelé (retraits)</p>
                    <p class="text-{{ $compact ? 'xl' : '2xl' }} font-bold text-red-600">
                        {{ number_format($wallet->frozen_amount ?? 0, 3) }} DT
                    </p>
                    @if(!$compact)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $wallet->frozen_amount > 0 ? 'Demandes de retrait en cours' : 'Aucun montant gelé' }}
                        </p>
                    @endif
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-{{ $compact ? '5' : '6' }} h-{{ $compact ? '5' : '6' }} text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endif

    <!-- Solde total -->
    <div class="bg-white rounded-{{ $compact ? 'lg' : 'xl' }} shadow-sm border border-gray-200 p-{{ $compact ? '4' : '6' }}">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Solde total</p>
                <p class="text-{{ $compact ? 'xl' : '2xl' }} font-bold {{ $balanceColor }}">
                    {{ number_format($wallet->balance, 3) }} DT
                </p>
                @if(!$compact)
                    <p class="text-xs text-gray-500 mt-1">
                        Incluant montants gelés
                    </p>
                @endif
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-{{ $compact ? '5' : '6' }} h-{{ $compact ? '5' : '6' }} text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

@if($stats && !$compact)
    <!-- Statistiques supplémentaires -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Total crédité</h3>
            <p class="text-3xl font-bold text-green-600">
                {{ number_format($stats['total_credited'] ?? 0, 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Revenus accumulés</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">💸 Total débité</h3>
            <p class="text-3xl font-bold text-red-600">
                {{ number_format($stats['total_debited'] ?? 0, 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Frais et retraits</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Solde net</h3>
            @php
                $netBalance = ($stats['total_credited'] ?? 0) - ($stats['total_debited'] ?? 0);
            @endphp
            <p class="text-3xl font-bold {{ $netBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($netBalance, 3) }} DT
            </p>
            <p class="text-sm text-gray-600 mt-1">Résultat global</p>
        </div>
    </div>
@endif

@if($showActions && !$compact)
    <!-- Actions rapides -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions rapides</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($availableBalance > 0)
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
            @endif

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

            @if(Route::has('client.wallet.topup'))
                <a href="{{ route('client.wallet.topup') }}" 
                   class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                    <div class="bg-yellow-600 rounded-full p-2 mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Recharger</p>
                        <p class="text-sm text-gray-600">Ajouter des fonds</p>
                    </div>
                </a>
            @endif
        </div>
    </div>
@endif