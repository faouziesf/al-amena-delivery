@extends('layouts.deliverer-modern')

@section('title', 'Historique Recharges')
@section('content')
<div class="px-4 pb-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">📋 Historique des Recharges</h1>
                    <p class="text-gray-500">Toutes vos recharges effectuées</p>
                </div>
                <a href="{{ route('deliverer.client-topup.index') }}" 
                   class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Liste des recharges -->
        @if($topups->isEmpty())
            <div class="bg-white rounded-3xl shadow-xl p-12 text-center">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Aucune recharge</h3>
                <p class="text-gray-600">Vous n'avez pas encore effectué de recharge client.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($topups as $topup)
                    <div class="bg-white rounded-2xl shadow-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800">
                                    {{ $topup->description }}
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $topup->created_at->format('d/m/Y H:i') }}
                                </div>
                                @if($topup->reference)
                                    <div class="text-xs text-gray-400 mt-1">
                                        Réf: {{ $topup->reference }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right ml-4">
                                <div class="text-lg font-bold text-green-600">
                                    +{{ number_format($topup->amount, 3) }} DT
                                </div>
                                <div class="text-xs text-gray-500">
                                    Commission
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $topups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
