@extends('layouts.deliverer-modern')

@section('title', 'Ma Tournée')

@section('content')
<div x-data="{ filter: 'all', sortBy: 'priority' }" class="px-4">
    <!-- Header Moderne avec Illustration -->
    <div class="mb-6 relative overflow-hidden bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-3xl p-6 border border-white/20 shadow-2xl">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-yellow-400/20 to-orange-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-br from-purple-400/20 to-pink-500/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-3xl font-black text-white mb-1 flex items-center gap-2">
                        <span class="text-4xl">🚚</span>
                        <span>Ma Tournée</span>
                    </h1>
                    <p class="text-white/90 font-medium flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        {{ Auth::user()->name }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-white/70 text-xs mb-1">{{ date('l') }}</div>
                    <div class="text-white font-bold text-lg">{{ date('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Améliorées -->
    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 shadow-lg transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white/80 text-xs font-medium mb-1">Total</div>
                    <div class="text-3xl font-black text-white">{{ $stats['total'] }}</div>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">📦</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 shadow-lg transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white/80 text-xs font-medium mb-1">Livraisons</div>
                    <div class="text-3xl font-black text-white">{{ $stats['livraisons'] }}</div>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">🚚</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-4 shadow-lg transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white/80 text-xs font-medium mb-1">Pickups</div>
                    <div class="text-3xl font-black text-white">{{ $stats['pickups'] }}</div>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">📥</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-4 shadow-lg transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white/80 text-xs font-medium mb-1">Terminés</div>
                    <div class="text-3xl font-black text-white">{{ $stats['completed_today'] }}</div>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-2xl">✅</span>
                </div>
            </div>
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

    <!-- Liste des Tâches Modernisée -->
    <div class="space-y-4 pb-4">
        @forelse($tasks as $task)
        <div x-show="filter === 'all' || filter === '{{ $task['type'] }}'" 
             x-transition
             class="relative group">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-r 
                @if($task['type'] === 'livraison') from-indigo-500/20 to-purple-500/20
                @elseif($task['type'] === 'pickup') from-cyan-500/20 to-blue-500/20
                @elseif($task['type'] === 'retour') from-orange-500/20 to-red-500/20
                @elseif($task['type'] === 'paiement') from-green-500/20 to-emerald-500/20
                @endif rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <!-- Card Content -->
            <div class="relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all p-5 border-2 border-gray-100 hover:border-gray-200">
                <!-- Header avec Type et Badge -->\n                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-md
                            @if($task['type'] === 'livraison') bg-gradient-to-br from-indigo-500 to-purple-600
                            @elseif($task['type'] === 'pickup') bg-gradient-to-br from-cyan-500 to-blue-600
                            @elseif($task['type'] === 'retour') bg-gradient-to-br from-orange-500 to-red-600
                            @elseif($task['type'] === 'paiement') bg-gradient-to-br from-green-500 to-emerald-600
                            @endif">
                            <span class="text-2xl">{{ $task['icon'] }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                @if($task['type'] === 'livraison') Livraison
                                @elseif($task['type'] === 'pickup') Ramassage
                                @elseif($task['type'] === 'retour') Retour
                                @elseif($task['type'] === 'paiement') Paiement
                                @endif
                            </span>
                            <div class="font-black text-gray-900 text-lg">{{ $task['package_code'] }}</div>
                        </div>
                    </div>
                    
                    @if($task['est_echange'])
                    <span class="px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl text-xs font-bold shadow-lg animate-pulse">
                        🔄 ÉCHANGE
                    </span>
                    @elseif(isset($task['payment_amount']) && $task['payment_amount'] > 0)
                    <span class="px-3 py-1.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl text-xs font-bold shadow-md">
                        💰 {{ number_format($task['payment_amount'], 3) }} DT
                    </span>
                    @elseif($task['cod_amount'] > 0)
                    <span class="px-3 py-1.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-xs font-bold shadow-md">
                        💵 {{ number_format($task['cod_amount'], 3) }} DT
                    </span>
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

                <!-- Bouton Action avec Icône -->
                <a href="{{ route('deliverer.task.detail', $task['id']) }}" 
                   class="flex items-center justify-center gap-3 w-full bg-gradient-to-r 
                          @if($task['type'] === 'livraison') from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700
                          @elseif($task['type'] === 'pickup') from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700
                          @elseif($task['type'] === 'retour') from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700
                          @elseif($task['type'] === 'paiement') from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700
                          @endif 
                          text-white text-center py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95 group">
                    <span>Voir les détails</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <!-- Empty State Moderne -->
        <div class="text-center py-16">
            <div class="relative inline-block mb-6">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur-2xl opacity-20"></div>
                <div class="relative text-8xl">📭</div>
            </div>
            <h3 class="text-2xl font-black text-white mb-2">Aucune tâche</h3>
            <p class="text-white/70 max-w-sm mx-auto">Vous n'avez aucune tâche assignée pour le moment. Profitez de votre pause ! ☕</p>
        </div>
        @endforelse
    </div>
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
