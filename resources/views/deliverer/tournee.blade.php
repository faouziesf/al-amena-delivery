@extends('layouts.deliverer-modern')

@section('title', 'Ma Tournée')

@section('content')
<div x-data="{ filter: 'all' }" class="px-4">
    <!-- Header avec nom livreur -->
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-white mb-1">📋 Run Sheet</h1>
        <p class="text-white/80 text-sm">{{ Auth::user()->name }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-4 gap-2 mb-4">
        <div class="bg-white/20 backdrop-blur-lg rounded-xl p-3 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-white/80 mt-1">Total</div>
        </div>
        <div class="bg-white/20 backdrop-blur-lg rounded-xl p-3 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['livraisons'] }}</div>
            <div class="text-xs text-white/80 mt-1">Livraisons</div>
        </div>
        <div class="bg-white/20 backdrop-blur-lg rounded-xl p-3 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['pickups'] }}</div>
            <div class="text-xs text-white/80 mt-1">Pickups</div>
        </div>
        <div class="bg-white/20 backdrop-blur-lg rounded-xl p-3 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['completed_today'] }}</div>
            <div class="text-xs text-white/80 mt-1">Complétés</div>
        </div>
    </div>

    <!-- Filtres Modernes -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
        <button @click="filter = 'all'" 
                :class="filter === 'all' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white'"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all">
            Tous
        </button>
        <button @click="filter = 'livraison'" 
                :class="filter === 'livraison' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white'"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all">
            🚚 Livraisons
        </button>
        <button @click="filter = 'pickup'" 
                :class="filter === 'pickup' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white'"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all">
            📦 Pickups
        </button>
        <button @click="filter = 'retour'" 
                :class="filter === 'retour' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white'"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all">
            ↩️ Retours
        </button>
        <button @click="filter = 'paiement'" 
                :class="filter === 'paiement' ? 'bg-white text-indigo-600' : 'bg-white/20 text-white'"
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all">
            💰 Paiements
        </button>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-500 text-white px-4 py-3 rounded-xl mb-4 flex items-center gap-2 fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-500 text-white px-4 py-3 rounded-xl mb-4 flex items-center gap-2 fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Liste des Tâches -->
    <div class="space-y-3 pb-4">
        @forelse($tasks as $task)
        <div x-show="filter === 'all' || filter === '{{ $task['type'] }}'" 
             x-transition
             class="card p-4 fade-in">
                <!-- Header Card -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold text-white
                            @if($task['type'] === 'livraison') bg-gradient-to-r from-indigo-600 to-purple-600
                            @elseif($task['type'] === 'pickup') bg-gradient-to-r from-cyan-600 to-blue-600
                            @elseif($task['type'] === 'retour') bg-gradient-to-r from-orange-600 to-red-600
                            @elseif($task['type'] === 'paiement') bg-gradient-to-r from-green-600 to-emerald-600
                            @endif">
                            {{ $task['icon'] }}
                            @if($task['type'] === 'livraison') Livraison
                            @elseif($task['type'] === 'pickup') Ramassage
                            @elseif($task['type'] === 'retour') Retour
                            @elseif($task['type'] === 'paiement') Paiement
                            @endif
                        </span>
                        <div class="font-bold text-gray-900 mt-2">{{ $task['package_code'] }}</div>
                    </div>
                    
                    @if($task['est_echange'])
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold animate-pulse">ÉCHANGE</span>
                    @elseif(isset($task['payment_amount']) && $task['payment_amount'] > 0)
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">{{ number_format($task['payment_amount'], 3) }} DT</span>
                    @elseif($task['cod_amount'] > 0)
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">{{ number_format($task['cod_amount'], 3) }} DT</span>
                    @endif
                </div>

                <!-- Infos Destinataire -->
                <div class="space-y-2 mb-3">
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 text-sm">👤</span>
                        <span class="text-gray-700 text-sm font-medium">{{ $task['recipient_name'] }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 text-sm">📞</span>
                        <a href="tel:{{ $task['recipient_phone'] }}" class="text-indigo-600 text-sm font-medium hover:underline">{{ $task['recipient_phone'] }}</a>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 text-sm">📍</span>
                        <span class="text-gray-600 text-sm">{{ $task['recipient_address'] }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 text-sm">🗺️</span>
                        <span class="text-gray-600 text-sm font-medium">{{ $task['delegation'] }}</span>
                    </div>
                </div>

                <!-- Badges Infos -->
                @if($task['requires_signature'])
                <div class="flex items-center gap-1 text-indigo-600 text-xs font-medium mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span>Signature obligatoire</span>
                </div>
                @endif

                @if($task['type'] === 'retour' && isset($task['return_reason']))
                <div class="bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 mb-3">
                    <p class="text-xs text-orange-800"><span class="font-semibold">Raison:</span> {{ $task['return_reason'] }}</p>
                </div>
                @endif

                <!-- Bouton Action -->
                <a href="{{ route('deliverer.task.detail', $task['id']) }}" 
                   class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-xl font-semibold transition-all active:scale-95">
                    Voir détails →
                </a>
            </div>
        @empty
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune tâche</h3>
            <p class="text-gray-500">Vous n'avez aucune tâche assignée pour le moment.</p>
        </div>
        @endforelse
    </div>

    <!-- Bouton Scanner Flottant -->
    <a href="{{ route('deliverer.scan.simple') }}" 
       class="fixed bottom-24 right-4 w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl hover:scale-110 transition-transform active:scale-95 z-30">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
        </svg>
    </a>
</div>

@push('styles')
<style>
    /* Hide scrollbar but keep functionality */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
@endsection
