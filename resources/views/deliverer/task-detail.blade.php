@extends('layouts.deliverer-modern')

@section('title', 'Détail Colis')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 p-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-3xl shadow-xl p-6">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="text-6xl mb-3">📦</div>
                <h4 class="text-xl font-bold text-gray-800">{{ $package->tracking_number }}</h4>
                <span class="inline-block mt-2 px-4 py-1 bg-cyan-100 text-cyan-700 rounded-full text-sm font-semibold">
                    {{ $package->status }}
                </span>
            </div>

            <!-- COD Amount -->
            @if($package->cod_amount > 0)
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6 text-center mb-6">
                    <div class="text-4xl font-bold text-green-600 mb-1">{{ number_format($package->cod_amount, 3) }} DT</div>
                    <div class="text-sm text-green-700">Montant à collecter</div>
                </div>
            @endif

            <!-- Client Info -->
            <div class="space-y-4 mb-6">
                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">👤 Destinataire</div>
                    <div class="font-semibold text-gray-800">{{ $package->recipient_name }}</div>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-2">📞 Téléphone</div>
                    <a href="tel:{{ $package->recipient_phone }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-medium hover:bg-indigo-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $package->recipient_phone }}
                    </a>
                </div>

                <div class="pb-4 border-b border-gray-100">
                    <div class="text-xs text-gray-500 mb-1">📍 Adresse</div>
                    <div class="text-gray-700">{{ $package->recipient_address }}</div>
                </div>

                @if($package->recipient_city)
                    <div class="pb-4 border-b border-gray-100">
                        <div class="text-xs text-gray-500 mb-1">🏙️ Ville</div>
                        <div class="text-gray-700">{{ $package->recipient_city }}</div>
                    </div>
                @endif

                @if($package->delivery_notes)
                    <div class="pb-4">
                        <div class="text-xs text-gray-500 mb-1">📝 Notes</div>
                        <div class="text-gray-700 bg-amber-50 p-3 rounded-xl">{{ $package->delivery_notes }}</div>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                @if($package->status === 'AVAILABLE' || $package->status === 'ACCEPTED')
                    <form action="{{ route('deliverer.simple.pickup', $package) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            ✅ Marquer comme Collecté
                        </button>
                    </form>
                @endif

                @if($package->status === 'PICKED_UP')
                    <form action="{{ route('deliverer.simple.deliver', $package) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            🚚 Marquer comme Livré
                        </button>
                    </form>

                    <form action="{{ route('deliverer.simple.unavailable', $package) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            ❌ Client Indisponible
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
