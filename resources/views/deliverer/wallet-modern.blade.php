@extends('layouts.deliverer-modern')

@section('title', 'Mon Wallet')

@section('content')
<div class="bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 px-4 pb-4">
    <div class="max-w-md mx-auto">
        <h4 class="text-white font-bold text-xl mb-4 px-2">💰 Mon Wallet</h4>

        <!-- Balance Card -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl shadow-2xl p-8 mb-4 text-white">
            <div class="text-center">
                <div class="text-white/70 text-sm mb-2">Solde Disponible</div>
                <div class="text-5xl font-bold mb-2">0.000 DT</div>
                <div class="text-white/60 text-xs">Mis à jour maintenant</div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-white rounded-2xl p-4 text-center shadow-lg">
                <div class="text-2xl font-bold text-green-600 mb-1">0.000 DT</div>
                <div class="text-xs text-gray-500">Collecté aujourd'hui</div>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center shadow-lg">
                <div class="text-2xl font-bold text-amber-600 mb-1">0.000 DT</div>
                <div class="text-xs text-gray-500">En attente</div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6">
            <h6 class="font-bold text-gray-800 mb-4">Transactions Récentes</h6>
            <div class="text-center py-8 text-gray-400">
                <div class="text-6xl mb-3">📊</div>
                <p class="text-sm">Aucune transaction récente</p>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('deliverer.menu') }}" 
               class="block w-full bg-white/20 backdrop-blur-lg text-white text-center py-4 rounded-2xl font-semibold hover:bg-white/30 transition-all">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>
@endsection
