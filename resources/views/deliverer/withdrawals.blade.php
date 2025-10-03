@extends('layouts.deliverer')

@section('title', 'Retraits Espèces')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white">
        <div class="px-6 py-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('deliverer.run.sheet') }}" class="text-white hover:text-orange-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold">Retraits Espèces</h1>
                    <p class="text-orange-100 mt-1">Livraisons d'espèces assignées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="px-6 py-4">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                <div class="text-2xl font-bold text-orange-600">{{ $withdrawals->count() }}</div>
                <div class="text-sm text-gray-600">Retraits assignés</div>
            </div>
            <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                <div class="text-2xl font-bold text-emerald-600">{{ $withdrawals->where('status', 'READY_FOR_DELIVERY')->count() }}</div>
                <div class="text-sm text-gray-600">Prêts</div>
            </div>
            <div class="bg-white rounded-xl p-4 text-center shadow-sm col-span-2 md:col-span-1">
                <div class="text-2xl font-bold text-blue-600">{{ $withdrawals->where('status', 'IN_PROGRESS')->count() }}</div>
                <div class="text-sm text-gray-600">En cours</div>
            </div>
        </div>
    </div>

    <!-- Liste des retraits -->
    <div class="px-6 pb-6">
        @if($withdrawals->count() > 0)
            <div class="space-y-4">
                @foreach($withdrawals as $withdrawal)
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <!-- En-tête du retrait -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Retrait #{{ $withdrawal->id }}</h3>
                                <p class="text-sm text-gray-600">Client: {{ $withdrawal->client->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $withdrawal->status === 'READY_FOR_DELIVERY' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $withdrawal->status === 'READY_FOR_DELIVERY' ? 'Prêt' : 'En cours' }}
                                </span>
                            </div>
                        </div>

                        <!-- Informations du retrait -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Montant</dt>
                                <dd class="text-lg font-bold text-emerald-600">{{ number_format($withdrawal->amount, 3) }} TND</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Date de demande</dt>
                                <dd class="text-sm text-gray-900">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if($withdrawal->delivery_address)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-600">Adresse de livraison</dt>
                                    <dd class="text-sm text-gray-900">{{ $withdrawal->delivery_address }}</dd>
                                </div>
                            @endif
                            @if($withdrawal->client_phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Téléphone client</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="tel:{{ $withdrawal->client_phone }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $withdrawal->client_phone }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if($withdrawal->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-600">Notes</dt>
                                    <dd class="text-sm text-gray-900">{{ $withdrawal->notes }}</dd>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-3">
                            @if($withdrawal->client_phone)
                                <a href="tel:{{ $withdrawal->client_phone }}"
                                   class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Appeler
                                </a>
                            @endif

                            @if($withdrawal->status === 'READY_FOR_DELIVERY')
                                <form action="{{ route('deliverer.withdrawals.delivered', $withdrawal) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Confirmer la livraison de ce retrait ?')"
                                            class="w-full bg-emerald-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Marquer livré
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- État vide -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun retrait assigné</h3>
                <p class="text-gray-600 mb-6">Vous n'avez actuellement aucune livraison d'espèces assignée.</p>
                <a href="{{ route('deliverer.run.sheet') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour au Run Sheet
                </a>
            </div>
        @endif
    </div>
</div>
@endsection