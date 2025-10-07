@extends('layouts.deliverer-modern')

@section('title', 'Ma Tournée')

@section('content')
<div class="px-4 pb-4">
    <div class="max-w-md mx-auto">
        <!-- Stats Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <div class="grid grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-cyan-600">{{ $stats['livraisons'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Livraisons</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-amber-600">{{ $stats['pickups'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Ramassages</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Terminés</div>
                </div>
            </div>
        </div>

        <h5 class="text-white font-semibold mb-3 px-2">📦 Mes Tâches ({{ $tasks->count() }})</h5>

        @if($tasks->isEmpty())
            <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-12 text-center text-white">
                <div class="text-6xl mb-4">✅</div>
                <h4 class="text-xl font-bold mb-2">Aucune tâche en cours</h4>
                <p class="text-white/80">Vous avez terminé toutes vos livraisons !</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($tasks as $task)
                    <div class="bg-white rounded-2xl shadow-lg p-4 transition-all hover:shadow-xl">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white {{ $task['type'] === 'livraison' ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gradient-to-r from-pink-500 to-rose-500' }}">
                                    {{ $task['type'] === 'livraison' ? '📦 Livraison' : '🏪 Ramassage' }}
                                </span>
                                <div class="font-bold text-gray-800 mt-2">{{ $task['tracking_number'] }}</div>
                            </div>
                            @if($task['cod_amount'] > 0)
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">{{ number_format($task['cod_amount'], 3) }} DT</div>
                                    <div class="text-xs text-gray-500">COD</div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2 text-sm">
                            <div>
                                <div class="text-gray-500 text-xs">👤 Client</div>
                                <div class="font-semibold text-gray-800">{{ $task['recipient_name'] }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500 text-xs">📍 Adresse</div>
                                <div class="text-gray-700">{{ $task['recipient_address'] }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500 text-xs">📞 Téléphone</div>
                                <a href="tel:{{ $task['recipient_phone'] }}" class="text-indigo-600 font-medium">
                                    {{ $task['recipient_phone'] }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-4">
                            @if($task['type'] === 'livraison')
                                <a href="{{ route('deliverer.task.detail', $task['id']) }}" 
                                   class="block w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95">
                                    Voir Détails
                                </a>
                            @else
                                <a href="{{ route('deliverer.pickup.detail', $task['pickup_id']) }}" 
                                   class="block w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white text-center py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95">
                                    Voir Ramassage
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
