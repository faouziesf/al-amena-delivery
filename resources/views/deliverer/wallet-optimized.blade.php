@extends('layouts.deliverer-modern')

@section('title', 'Portefeuille')

@section('content')
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Mon Portefeuille</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="modern-card p-4">
            <h2 class="text-lg font-semibold mb-2">Solde Disponible</h2>
            <p class="text-3xl font-bold text-green-600" x-text="'{{ number_format($walletData['balance'], 3) }} TND'"></p>
        </div>
        
        <div class="modern-card p-4">
            <h2 class="text-lg font-semibold mb-2">Montant en Attente</h2>
            <p class="text-3xl font-bold text-blue-600" x-text="'{{ number_format($walletData['pending_amount'], 3) }} TND'"></p>
        </div>
        
        <div class="modern-card p-4">
            <h2 class="text-lg font-semibold mb-2">Montant Gelé</h2>
            <p class="text-3xl font-bold text-red-600" x-text="'{{ number_format($walletData['frozen_amount'], 3) }} TND'"></p>
        </div>
    </div>
    
    <div class="modern-card p-4">
        <h2 class="text-lg font-semibold mb-4">Dernières Transactions</h2>
        <div class="space-y-3">
            @foreach ($recentTransactions as $transaction)
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <p class="font-medium">{{ $transaction['description'] }}</p>
                    <p class="text-sm text-gray-500">{{ $transaction['date'] }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold {{ $transaction['amount'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $transaction['amount'] > 0 ? '+' : '' }}{{ number_format($transaction['amount'], 3) }} TND
                    </p>
                    <p class="text-sm">Solde: {{ number_format($transaction['balance'], 3) }} TND</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
