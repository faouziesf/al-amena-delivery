@extends('layouts.deliverer-modern')

@section('title', 'Ma Tournée')

@section('content')
<div class="min-h-screen bg-gray-50">
    
    <!-- Header avec stats -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white safe-top">
        <div class="px-6 py-6">
            <!-- Date et refresh -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Ma Tournée</h1>
                    <p class="text-indigo-200 text-sm">{{ now()->format('d/m/Y') }}</p>
                </div>
                <a href="{{ route('deliverer.tournee') }}" 
                   class="p-3 bg-white/20 rounded-xl hover:bg-white/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </a>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
                    <div class="text-xs text-indigo-200 mt-1">Total</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-blue-300">{{ $stats['livraisons'] }}</div>
                    <div class="text-xs text-indigo-200 mt-1">Livraisons</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-green-300">{{ $stats['pickups'] }}</div>
                    <div class="text-xs text-indigo-200 mt-1">Ramassages</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="p-4 space-y-3 pb-24">
        
        @if($tasks->isEmpty())
            <!-- Aucune tâche -->
            <div class="text-center py-12">
                <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-600 text-lg font-medium">Aucune tâche pour le moment</p>
                <p class="text-gray-500 text-sm mt-2">Vérifiez les pickups disponibles</p>
                <a href="{{ route('deliverer.pickups.available') }}" class="mt-4 inline-block px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700">
                    Voir Pickups Disponibles
                </a>
            </div>
        @else
            <!-- Cards des tâches -->
            @foreach($tasks as $task)
                <a href="@if($task['type'] === 'pickup')/deliverer/pickup/{{ $task['pickup_id'] }}@else/deliverer/task/{{ $task['id'] }}@endif" 
                   class="block card p-4 hover:shadow-md transition-all active:scale-98">
                    
                    <div class="flex items-start justify-between mb-3">
                        <!-- Type et statut -->
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">{{ $task['type'] === 'livraison' ? '🚚' : '📦' }}</span>
                            <div>
                                <div class="font-bold text-gray-900">{{ $task['type'] === 'livraison' ? 'LIVRAISON' : 'RAMASSAGE' }}</div>
                                <div class="text-xs text-gray-500">{{ $task['package_code'] }}</div>
                            </div>
                        </div>
                        
                        <!-- Badge statut -->
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($task['status'] === 'DELIVERED') bg-green-100 text-green-700
                            @elseif($task['status'] === 'PICKED_UP') bg-blue-100 text-blue-700
                            @elseif($task['status'] === 'assigned') bg-purple-100 text-purple-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">
                            {{ strtoupper($task['status']) }}
                        </span>
                    </div>

                    <!-- Info destinataire -->
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-gray-700 font-medium">{{ $task['recipient_name'] }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-gray-600 flex-1">{{ $task['recipient_address'] }}</span>
                        </div>

                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $task['recipient_phone'] }}" class="text-indigo-600 font-medium">{{ $task['recipient_phone'] }}</a>
                        </div>

                        @if($task['cod_amount'] > 0)
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">COD à collecter</span>
                                <span class="text-lg font-bold text-green-600">{{ number_format($task['cod_amount'], 2) }} TND</span>
                            </div>
                        @endif

                        @if($task['est_echange'])
                            <div class="mt-2 p-2 bg-orange-50 border border-orange-200 rounded-lg flex items-center text-xs text-orange-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="font-semibold">⚠️ ÉCHANGE - Reprendre ancien colis</span>
                            </div>
                        @endif
                    </div>

                    <!-- Actions rapides -->
                    <div class="mt-4 flex space-x-2">
                        <a href="tel:{{ $task['recipient_phone'] }}" 
                           class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg text-center text-sm font-semibold hover:bg-green-700"
                           onclick="event.stopPropagation()">
                            📞 Appeler
                        </a>
                        <a href="@if($task['type'] === 'pickup')/deliverer/pickup/{{ $task['pickup_id'] }}@else/deliverer/task/{{ $task['id'] }}@endif" 
                           class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-center text-sm font-semibold hover:bg-indigo-700">
                            👁️ Détails
                        </a>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh toutes les 2 minutes
setTimeout(() => {
    window.location.reload();
}, 120000);
</script>
@endpush

@endsection
