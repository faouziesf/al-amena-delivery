@extends('layouts.deliverer')

@section('title', 'Sources Wallet')

@section('content')
<div class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">🔍 Sources du Wallet</h1>
                    <p class="text-gray-600 mt-1">Détail des montants dans votre caisse</p>
                </div>
                <a href="{{ route('deliverer.wallet.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="p-4">
        <!-- Résumé par type -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($sourcesByType as $type => $sources)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center
                            @if($type === 'COD') bg-green-100 text-green-600
                            @elseif($type === 'CLIENT_FUND') bg-blue-100 text-blue-600
                            @else bg-gray-100 text-gray-600
                            @endif">
                            @if($type === 'COD')
                                💰
                            @elseif($type === 'CLIENT_FUND')
                                🔄
                            @else
                                📊
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">
                                @if($type === 'COD')
                                    COD Collectés
                                @elseif($type === 'CLIENT_FUND')
                                    Recharges Clients
                                @else
                                    {{ $type }}
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600">{{ $sources->count() }} transaction(s)</p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-2xl font-bold
                            @if($type === 'COD') text-green-600
                            @elseif($type === 'CLIENT_FUND') text-blue-600
                            @else text-gray-600
                            @endif">
                            {{ number_format($sources->sum('amount'), 3) }} DT
                        </p>
                        <p class="text-sm text-gray-500">
                            Moyenne: {{ number_format($sources->avg('amount'), 3) }} DT
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Détail par type -->
        @foreach($sourcesByType as $type => $sources)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        @if($type === 'COD')
                            💰 COD Collectés ({{ $sources->count() }})
                        @elseif($type === 'CLIENT_FUND')
                            🔄 Recharges Clients ({{ $sources->count() }})
                        @else
                            📊 {{ $type }} ({{ $sources->count() }})
                        @endif
                        <span class="text-sm font-normal text-gray-600">
                            - Total: {{ number_format($sources->sum('amount'), 3) }} DT
                        </span>
                    </h2>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($sources as $source)
                        <div class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gray-100">
                                            {{ $source['icon'] ?? '📊' }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $source['description'] }}</p>
                                            @if(isset($source['package_code']))
                                                <p class="text-sm text-gray-600">Colis: {{ $source['package_code'] }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500">{{ $source['date']->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold
                                        @if($type === 'COD') text-green-600
                                        @elseif($type === 'CLIENT_FUND') text-blue-600
                                        @else text-gray-600
                                        @endif">
                                        +{{ number_format($source['amount'], 3) }} DT
                                    </p>
                                    @if(isset($source['source_id']))
                                        <p class="text-xs text-gray-500">ID: {{ $source['source_id'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($sources->isEmpty())
                        <div class="px-6 py-8 text-center">
                            <p class="text-gray-500">Aucune transaction de ce type</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if($sourcesByType->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Wallet vide</h3>
                <p class="text-gray-600 mb-6">Votre wallet ne contient aucun montant actuellement</p>
                <div class="flex gap-4 justify-center">
                    <a href="{{ route('deliverer.packages.available') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Voir pickups disponibles
                    </a>
                    <a href="{{ route('deliverer.client-topup.index') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                        Recharger des clients
                    </a>
                </div>
            </div>
        @endif

        <!-- Info importantes -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-6">
            <div class="flex items-start gap-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-blue-900 mb-2">Principe Wallet = Caisse Physique</h4>
                    <div class="text-sm text-blue-800 space-y-1">
                        <p>• Le montant affiché dans votre wallet correspond exactement aux espèces que vous devez avoir physiquement</p>
                        <p>• Les COD collectés s'ajoutent immédiatement à votre wallet</p>
                        <p>• Les recharges clients sont ajoutées en double (client + votre wallet)</p>
                        <p>• Vous êtes responsable de la différence entre wallet digital et espèces physiques</p>
                        <p>• Le commercial videra périodiquement votre wallet contre remise des espèces</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection