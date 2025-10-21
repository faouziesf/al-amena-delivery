@extends('layouts.deliverer-modern')

@section('title', 'Mon Wallet')

@section('content')
<div class="px-4 pb-4">
    <div class="max-w-md mx-auto">
        <!-- Header avec animation -->
        <div class="mb-6 text-center">
            <div class="inline-block">
                <h4 class="text-white font-black text-3xl mb-1 flex items-center gap-3">
                    <span class="text-4xl animate-bounce">💰</span>
                    <span>Mon Wallet</span>
                </h4>
                <div class="h-1 bg-gradient-to-r from-yellow-400 via-orange-500 to-yellow-400 rounded-full animate-pulse"></div>
            </div>
        </div>

        <!-- Balance Card avec Gradient Animé -->
        <div class="relative overflow-hidden bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 rounded-3xl shadow-2xl p-8 mb-6 text-white transform hover:scale-105 transition-transform">
            <!-- Blobs décoratifs -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 text-center">
                <div class="text-white/90 text-sm font-semibold mb-2 uppercase tracking-wide">💵 Solde Disponible</div>
                <div class="text-6xl font-black mb-3 drop-shadow-lg">{{ number_format($wallet->balance ?? 0, 3) }} <span class="text-3xl">DT</span></div>
                <div class="flex items-center justify-center gap-2 text-white/80 text-xs">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span>Mis à jour maintenant</span>
                </div>
            </div>
        </div>

        <!-- Quick Stats avec Gradients -->
        <div class="grid grid-cols-3 gap-3 mb-6">
            <!-- COD Collecté Aujourd'hui -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-4 text-center shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="text-sm font-medium mb-1 opacity-90">Aujourd'hui</div>
                <div class="text-2xl font-black">{{ number_format($todayCollected ?? 0, 3) }}</div>
                <div class="text-xs opacity-75 mt-1">DT</div>
            </div>
            
            <!-- En Attente -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-4 text-center shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="text-sm font-medium mb-1 opacity-90">En attente</div>
                <div class="text-2xl font-black">{{ number_format($wallet->pending_amount ?? 0, 3) }}</div>
                <div class="text-xs opacity-75 mt-1">DT</div>
            </div>
            
            <!-- Total Transactions -->
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-4 text-center shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="text-sm font-medium mb-1 opacity-90">Transactions</div>
                <div class="text-2xl font-black">{{ $transactionCount ?? 0 }}</div>
                <div class="text-xs opacity-75 mt-1">Total</div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <h6 class="font-black text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Transactions Récentes
                </h6>
            </div>
            
            @if(isset($transactions) && $transactions->count() > 0)
            @php
                $grouped = $transactions->groupBy(function($t) {
                    if ($t->created_at->isToday()) return 'Aujourd\'hui';
                    if ($t->created_at->isYesterday()) return 'Hier';
                    if ($t->created_at->isCurrentWeek()) return 'Cette semaine';
                    return 'Plus ancien';
                });
            @endphp
            
            <div class="divide-y divide-gray-100">
                @foreach($grouped as $period => $periodTransactions)
                    <div class="px-6 py-3 bg-gray-50">
                        <h3 class="text-xs font-bold text-gray-600 uppercase">{{ $period }}</h3>
                    </div>
                    @foreach($periodTransactions as $transaction)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center
                                    @if($transaction->type === 'CREDIT') bg-green-100
                                    @elseif($transaction->type === 'DEBIT') bg-red-100
                                    @else bg-blue-100
                                    @endif">
                                    <span class="text-xl">
                                        @if(str_contains(strtoupper($transaction->description ?? ''), 'LIVRAISON') || str_contains(strtoupper($transaction->description ?? ''), 'DELIVERY'))
                                            💰
                                        @elseif(str_contains(strtoupper($transaction->description ?? ''), 'RAMASSAGE') || str_contains(strtoupper($transaction->description ?? ''), 'PICKUP'))
                                            📦
                                        @elseif(str_contains(strtoupper($transaction->description ?? ''), 'RETRAIT') || str_contains(strtoupper($transaction->description ?? ''), 'WITHDRAWAL'))
                                            💸
                                        @elseif(str_contains(strtoupper($transaction->description ?? ''), 'PÉNALITÉ') || str_contains(strtoupper($transaction->description ?? ''), 'PENALTY'))
                                            ⚠️
                                        @elseif($transaction->type === 'CREDIT')
                                            ➕
                                        @else
                                            ➖
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 text-sm">
                                        @if($transaction->package_id && $transaction->package)
                                            💰 Livraison #{{ $transaction->package->package_code }}
                                            @if(isset($transaction->package->recipient_data['name']))
                                                <span class="text-xs text-gray-500">- {{ $transaction->package->recipient_data['name'] }}</span>
                                            @endif
                                        @elseif(str_contains(strtoupper($transaction->description ?? ''), 'PICKUP') || str_contains(strtoupper($transaction->description ?? ''), 'RAMASSAGE'))
                                            📦 {{ $transaction->description }}
                                        @else
                                            {{ $transaction->description }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center gap-2">
                                        <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                                        @if($transaction->type)
                                            <span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $transaction->type }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-lg
                                    @if($transaction->type === 'CREDIT') text-green-600
                                    @else text-red-600
                                    @endif">
                                    @if($transaction->type === 'CREDIT')+@else-@endif{{ number_format($transaction->amount, 3) }} DT
                                </div>
                                @if($transaction->balance_after)
                                <div class="text-xs text-gray-500">Solde: {{ number_format($transaction->balance_after, 3) }} DT</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="relative inline-block mb-4">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur-2xl opacity-20"></div>
                    <div class="relative text-7xl">📊</div>
                </div>
                <p class="text-gray-400 font-medium">Aucune transaction récente</p>
                <p class="text-gray-300 text-sm mt-1">Vos transactions apparaîtront ici</p>
            </div>
            @endif
        </div>

        <!-- Actions Rapides -->
        <div class="grid grid-cols-2 gap-3 mt-6">
            <a href="{{ route('deliverer.menu') }}" 
               class="flex items-center justify-center gap-2 bg-white/20 backdrop-blur-lg text-white text-center py-4 rounded-2xl font-bold hover:bg-white/30 transition-all shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Menu
            </a>
            
            <a href="{{ route('deliverer.tournee') }}" 
               class="flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-4 rounded-2xl font-bold hover:shadow-xl transition-all shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Tournée
            </a>
        </div>
    </div>
</div>
@endsection
