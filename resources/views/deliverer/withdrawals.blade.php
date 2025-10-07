@extends('layouts.deliverer-modern')

@section('title', 'Mes Retraits')

@section('content')
<div class="bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 px-4 pb-4">
    <div class="max-w-md mx-auto">
        <h5 class="text-white font-bold text-xl mb-4 px-2">💵 Mes Retraits Espèces</h5>

        @if($withdrawals->isEmpty())
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-12 text-center text-white">
                <div class="text-6xl mb-4">✅</div>
                <h4 class="text-xl font-bold mb-2">Aucun retrait assigné</h4>
                <p class="text-white/80">Vous n'avez pas de retraits en cours</p>
            </div>
        @else
            <div class="space-y-3 mb-4">
                @foreach($withdrawals as $withdrawal)
                    <div class="bg-white rounded-2xl shadow-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <span class="inline-block px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-xs font-semibold">
                                💵 Retrait
                            </span>
                            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                                {{ $withdrawal->status }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div>
                                <div class="text-xs text-gray-500">👤 Client</div>
                                <div class="font-semibold text-gray-800">{{ $withdrawal->client->name ?? 'N/A' }}</div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">💰 Montant</div>
                                <div class="text-2xl font-bold text-green-600">
                                    {{ number_format($withdrawal->amount, 3) }} DT
                                </div>
                            </div>

                            @if($withdrawal->delivery_address)
                                <div>
                                    <div class="text-xs text-gray-500">📍 Adresse</div>
                                    <div class="text-gray-700">{{ $withdrawal->delivery_address }}</div>
                                </div>
                            @endif
                        </div>

                        @if($withdrawal->status === 'READY_FOR_DELIVERY')
                            <form action="{{ route('deliverer.withdrawals.delivered', $withdrawal) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95">
                                    ✅ Marquer comme Livré
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('deliverer.menu') }}" 
               class="block w-full bg-white/20 backdrop-blur-lg text-white text-center py-4 rounded-2xl font-semibold hover:bg-white/30 transition-all">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>
@endsection
