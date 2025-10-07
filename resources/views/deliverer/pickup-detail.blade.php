@extends('layouts.deliverer-modern')

@section('title', 'Détail Ramassage')

@section('content')
<div class="min-h-screen bg-gray-50">
    
    <!-- Header -->
    <div class="bg-green-600 text-white safe-top px-6 py-4">
        <div class="flex items-center">
            <a href="{{ route('deliverer.tournee') }}" class="mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">📦 Ramassage</h1>
                <p class="text-green-200 text-sm">{{ $pickup->pickup_code ?? 'PICKUP-' . $pickup->id }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 pb-24">
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl">
                ❌ {{ session('error') }}
            </div>
        @endif

        <!-- Statut -->
        <div class="card p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Statut</h2>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($pickup->status === 'picked_up') bg-green-100 text-green-700
                    @elseif($pickup->status === 'assigned') bg-blue-100 text-blue-700
                    @else bg-yellow-100 text-yellow-700
                    @endif">
                    {{ strtoupper($pickup->status) }}
                </span>
            </div>
        </div>

        <!-- Informations Client -->
        <div class="card p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-900 mb-4">👤 Informations Contact</h2>
            
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Nom du contact</div>
                    <div class="font-semibold text-gray-900">{{ $pickup->pickup_contact_name ?? 'Non spécifié' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-600 mb-1">Téléphone</div>
                    <a href="tel:{{ $pickup->pickup_phone }}" class="font-semibold text-indigo-600">
                        {{ $pickup->pickup_phone }}
                    </a>
                </div>

                <div>
                    <div class="text-sm text-gray-600 mb-1">Adresse de ramassage</div>
                    <div class="font-medium text-gray-900">{{ $pickup->pickup_address }}</div>
                </div>

                @if($pickup->delegation_from)
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Délégation</div>
                        <div class="font-medium text-gray-900">{{ $pickup->delegation_from }}</div>
                    </div>
                @endif

                @if($pickup->pickup_notes)
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Notes</div>
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm">
                            {{ $pickup->pickup_notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Date -->
        @if($pickup->requested_pickup_date)
            <div class="card p-6 mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-2">📅 Date Demandée</h2>
                <div class="text-gray-700 font-medium">
                    {{ $pickup->requested_pickup_date->format('d/m/Y à H:i') }}
                </div>
            </div>
        @endif

        <!-- Client -->
        @if($pickup->client)
            <div class="card p-6 mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-2">🏢 Client</h2>
                <div class="text-gray-700 font-medium">
                    {{ $pickup->client->name }}
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom">
            <div class="grid grid-cols-2 gap-3">
                <a href="tel:{{ $pickup->pickup_phone }}" 
                   class="btn bg-green-600 text-white hover:bg-green-700 text-center">
                    📞 Appeler
                </a>

                @if($pickup->status === 'assigned')
                    <form action="{{ route('deliverer.pickup.collect', $pickup->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Confirmer le ramassage de ce colis ?')"
                                class="w-full btn bg-indigo-600 text-white hover:bg-indigo-700">
                            ✅ Marquer Ramassé
                        </button>
                    </form>
                @elseif($pickup->status === 'picked_up')
                    <div class="flex-1 btn bg-gray-300 text-gray-600 cursor-not-allowed text-center">
                        ✅ Déjà Ramassé
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
