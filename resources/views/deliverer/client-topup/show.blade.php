@extends('layouts.deliverer')

@section('title', 'Détails Recharge - ' . $topupRequest->request_code)

@section('page-title', 'Détails de la Recharge')
@section('page-description', 'Recharge ' . $topupRequest->request_code)

@section('content')
<div class="space-y-6">
    <!-- Fil d'Ariane -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('deliverer.client-topup.index') }}" class="text-blue-600 hover:text-blue-800">
                    Recharges Client
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                    </svg>
                    <span class="ml-1 text-gray-500">{{ $topupRequest->request_code }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- En-tête avec statut -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $topupRequest->request_code }}</h1>
                    <p class="text-sm text-gray-500">
                        Créée le {{ $topupRequest->created_at->format('d/m/Y à H:i') }}
                        @if($topupRequest->processed_at)
                            • Traitée le {{ $topupRequest->processed_at->format('d/m/Y à H:i') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $topupRequest->status_color }}">
                        @if($topupRequest->status === 'VALIDATED')
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                        @elseif($topupRequest->status === 'PENDING')
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                            </svg>
                        @elseif($topupRequest->status === 'REJECTED')
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                            </svg>
                        @endif
                        {{ $topupRequest->status_display }}
                    </span>
                    
                    @if($topupRequest->status === 'VALIDATED')
                        <a href="{{ route('deliverer.client-topup.receipt', $topupRequest) }}" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimer reçu
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Détails de la recharge -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">💰 Détails de la recharge</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Montant</dt>
                            <dd class="mt-1 text-lg font-semibold text-green-600">{{ number_format($topupRequest->amount, 3) }} DT</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Méthode</dt>
                            <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $topupRequest->method_display }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $topupRequest->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        @if($topupRequest->processed_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date de traitement</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $topupRequest->processed_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Informations client -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">👤 Informations client</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-gray-900">{{ $topupRequest->client->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $topupRequest->client->phone }}</p>
                            @if($topupRequest->client->email)
                                <p class="text-sm text-gray-500">{{ $topupRequest->client->email }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Solde wallet</p>
                            <p class="text-lg font-semibold text-blue-600">
                                {{ number_format($topupRequest->client->wallet->balance ?? 0, 3) }} DT
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes et observations -->
            @if($topupRequest->notes || $topupRequest->validation_notes || $topupRequest->rejection_reason)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📝 Notes et observations</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    @if($topupRequest->notes)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Notes de la recharge</h4>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">{{ $topupRequest->notes }}</p>
                    </div>
                    @endif

                    @if($topupRequest->validation_notes)
                    <div>
                        <h4 class="text-sm font-medium text-green-700 mb-2">Notes de validation</h4>
                        <p class="text-sm text-green-600 bg-green-50 p-3 rounded-lg">{{ $topupRequest->validation_notes }}</p>
                    </div>
                    @endif

                    @if($topupRequest->rejection_reason)
                    <div>
                        <h4 class="text-sm font-medium text-red-700 mb-2">Raison du rejet</h4>
                        <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">{{ $topupRequest->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Métadonnées techniques -->
            @if($topupRequest->metadata && is_array($topupRequest->metadata))
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">🔧 Détails techniques</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        @foreach($topupRequest->metadata as $key => $value)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if(is_string($value))
                                    {{ $value }}
                                @elseif(is_bool($value))
                                    {{ $value ? 'Oui' : 'Non' }}
                                @else
                                    {{ json_encode($value) }}
                                @endif
                            </dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions rapides -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">⚡ Actions rapides</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    @if($topupRequest->status === 'VALIDATED')
                        <a href="{{ route('deliverer.client-topup.receipt', $topupRequest) }}" 
                           target="_blank"
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimer reçu
                        </a>
                    @endif

                    <a href="{{ route('deliverer.client-topup.index') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvelle recharge
                    </a>

                    <a href="{{ route('deliverer.client-topup.history') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Voir historique
                    </a>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">ℹ️ Informations</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-blue-800 mb-1">💡 Recharge espèces</h4>
                        <p class="text-xs text-blue-700">
                            Cette recharge a été effectuée en espèces sur le terrain par un livreur.
                        </p>
                    </div>

                    @if($topupRequest->status === 'VALIDATED')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-green-800 mb-1">✅ Fonds ajoutés</h4>
                        <p class="text-xs text-green-700">
                            Le montant a été ajouté au wallet du client et à votre wallet livreur.
                        </p>
                    </div>
                    @elseif($topupRequest->status === 'PENDING')
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-orange-800 mb-1">⏳ En attente</h4>
                        <p class="text-xs text-orange-700">
                            La recharge sera validée automatiquement prochainement.
                        </p>
                    </div>
                    @endif

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-gray-800 mb-1">🔒 Sécurité</h4>
                        <p class="text-xs text-gray-700">
                            Toutes les recharges sont tracées et sécurisées. Conservez les reçus.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Timeline ou historique des statuts -->
            @if($topupRequest->status !== 'PENDING')
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📅 Chronologie</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Recharge créée par le livreur</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $topupRequest->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            @if($topupRequest->processed_at)
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $topupRequest->status === 'VALIDATED' ? 'bg-green-500' : 'bg-red-500' }} flex items-center justify-center ring-8 ring-white">
                                                @if($topupRequest->status === 'VALIDATED')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    {{ $topupRequest->status === 'VALIDATED' ? 'Recharge validée' : 'Recharge rejetée' }}
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $topupRequest->processed_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection