@extends('layouts.deliverer-modern')

@section('title', 'Menu Principal')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 p-4">
    <div class="max-w-md mx-auto">
        <!-- Header Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">🚚 Menu Livreur</h1>
                <p class="text-gray-500">Bienvenue {{ Auth::user()->name }}</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $activeCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Actifs</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $todayCount }}</div>
                    <div class="text-xs text-gray-500 mt-1">Livrés</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-cyan-600">{{ number_format($balance, 3) }}</div>
                    <div class="text-xs text-gray-500 mt-1">DT</div>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="space-y-3">
            <!-- Ma Tournée -->
            <a href="{{ route('deliverer.tournee') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">📦</span>
                        <div>
                            <div class="font-semibold">Ma Tournée</div>
                            <div class="text-xs opacity-90">Voir mes livraisons</div>
                        </div>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-bold">{{ $activeCount }}</span>
                </div>
            </a>

            <!-- Scanner -->
            <a href="{{ route('deliverer.scan.simple') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">📷</span>
                        <div>
                            <div class="font-semibold">Scanner</div>
                            <div class="text-xs opacity-90">Scanner un colis</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Scanner Multiple -->
            <a href="{{ route('deliverer.scan.multi') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">📸</span>
                        <div>
                            <div class="font-semibold">Scanner Multiple</div>
                            <div class="text-xs opacity-90">Scanner plusieurs colis</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Ramassages -->
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">🏪</span>
                        <div>
                            <div class="font-semibold">Ramassages</div>
                            <div class="text-xs opacity-90">Collectes disponibles</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <!-- Wallet -->
            <a href="{{ route('deliverer.wallet') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">💰</span>
                        <div>
                            <div class="font-semibold">Mon Wallet</div>
                            <div class="text-xs opacity-90">Solde et transactions</div>
                        </div>
                    </div>
                    <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-bold">{{ number_format($balance, 3) }} DT</span>
                </div>
            </a>

            <!-- Retraits -->
            <a href="{{ route('deliverer.withdrawals.index') }}" 
               class="block bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4 shadow-lg hover:shadow-xl transition-all active:scale-95">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">💵</span>
                        <div>
                            <div class="font-semibold">Retraits Espèces</div>
                            <div class="text-xs opacity-90">Mes retraits assignés</div>
                        </div>
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
