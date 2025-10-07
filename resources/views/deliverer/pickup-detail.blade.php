@extends('layouts.deliverer-modern')

@section('title', 'Détail Ramassage')

@section('content')
<div class="bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 px-4 pb-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-3xl shadow-xl p-6">
            <div class="text-center mb-6">
                <div class="text-6xl mb-3">🏪</div>
                <h4 class="text-xl font-bold text-gray-800">Ramassage #{{ $pickup->id }}</h4>
                <span class="inline-block mt-2 px-4 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-semibold">
                    {{ $pickup->status }}
                </span>
            </div>

            <div class="space-y-4 mb-6">
                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">📍 Adresse de ramassage</div>
                    <div class="font-semibold text-gray-800">{{ $pickup->pickup_address }}</div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">👤 Contact</div>
                    <div class="text-gray-700">{{ $pickup->pickup_contact_name ?? 'N/A' }}</div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-2">📞 Téléphone</div>
                    <a href="tel:{{ $pickup->pickup_phone }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-medium hover:bg-indigo-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $pickup->pickup_phone }}
                    </a>
                </div>

                @if($pickup->pickup_notes)
                    <div class="pb-4 border-b border-gray-100">
                        <div class="text-xs text-gray-500 mb-1">📝 Notes</div>
                        <div class="text-gray-700 bg-amber-50 p-3 rounded-xl">{{ $pickup->pickup_notes }}</div>
                    </div>
                @endif

                @if($pickup->requested_pickup_date)
                    <div class="pb-4">
                        <div class="text-xs text-gray-500 mb-1">📅 Date demandée</div>
                        <div class="text-gray-700">{{ $pickup->requested_pickup_date->format('d/m/Y H:i') }}</div>
                    </div>
                @endif
            </div>

            <div class="space-y-3">
                @if($pickup->status === 'assigned' || $pickup->status === 'pending')
                    <form action="{{ route('deliverer.pickup.collect', $pickup->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            ✅ Marquer comme Collecté
                        </button>
                    </form>
                @endif

                <a href="{{ route('deliverer.tournee') }}" 
                   class="block w-full bg-gray-100 text-gray-700 py-4 rounded-xl font-semibold text-center hover:bg-gray-200 transition-colors">
                    ← Retour à la tournée
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
