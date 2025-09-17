@extends('layouts.client')

@section('title', "Demande de retrait #{$withdrawal->request_code}")

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header avec navigation -->
    <div class="flex items-center mb-6">
        <a href="{{ route('client.withdrawals') }}" 
           class="flex items-center text-gray-600 hover:text-gray-900 transition-colors mr-4">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Retour aux demandes
        </a>
        <div class="text-gray-400">•</div>
        <a href="{{ route('client.wallet.index') }}" 
           class="ml-4 text-gray-600 hover:text-gray-900 transition-colors">
            Portefeuille
        </a>
    </div>

    <!-- Titre principal -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    💸 Demande #{{ $withdrawal->request_code }}
                </h1>
                <p class="text-gray-600">Détails et suivi de votre demande de retrait</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $withdrawal->status_color }}">
                    {{ $withdrawal->status_display }}
                </span>
            </div>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Informations principales -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">📋 Informations de la demande</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Montant demandé</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($withdrawal->amount, 3) }} DT</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Méthode de retrait</p>
                        <div class="flex items-center">
                            @if($withdrawal->method === 'BANK_TRANSFER')
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            @endif
                            <span class="font-medium text-gray-900">{{ $withdrawal->method_display }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Date de création</p>
                        <p class="text-lg font-medium text-gray-900">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</p>
                        <p class="text-sm text-gray-500">{{ $withdrawal->created_at->diffForHumans() }}</p>
                    </div>

                    @if($withdrawal->processed_at)
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Date de traitement</p>
                            <p class="text-lg font-medium text-gray-900">{{ $withdrawal->processed_at->format('d/m/Y à H:i') }}</p>
                            <p class="text-sm text-gray-500">{{ $withdrawal->processed_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>

                @if($withdrawal->reason)
                    <div class="mt-6">
                        <p class="text-sm font-medium text-gray-600 mb-2">Motif de la demande</p>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $withdrawal->reason }}</p>
                    </div>
                @endif
            </div>

            <!-- Coordonnées bancaires (si applicable) -->
            @if($withdrawal->method === 'BANK_TRANSFER' && $withdrawal->bank_details)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🏛️ Coordonnées bancaires</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <pre class="text-sm text-blue-900 whitespace-pre-wrap">{{ $withdrawal->bank_details }}</pre>
                    </div>
                </div>
            @endif

            <!-- Informations de livraison (si applicable) -->
            @if($withdrawal->method === 'CASH_DELIVERY')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🚚 Informations de livraison</h3>
                    
                    @if($withdrawal->delivery_receipt_code)
                        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm font-medium text-yellow-800 mb-2">Code de livraison</p>
                            <p class="text-lg font-mono font-bold text-yellow-900">{{ $withdrawal->delivery_receipt_code }}</p>
                            <p class="text-sm text-yellow-700 mt-1">Communiquez ce code au livreur lors de la remise</p>
                        </div>
                    @endif

                    @if($withdrawal->assignedDeliverer)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Livreur assigné</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $withdrawal->assignedDeliverer->name }}</p>
                            </div>
                            @if($withdrawal->assignedDeliverer->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-600 mb-1">Téléphone</p>
                                    <p class="text-lg font-medium text-blue-600">{{ $withdrawal->assignedDeliverer->formatted_phone }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($withdrawal->delivered_at)
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-green-800">Livraison terminée</p>
                                    <p class="text-sm text-green-700">{{ $withdrawal->delivered_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Notes du commercial -->
            @if($withdrawal->processing_notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">💬 Note du commercial</h3>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <p class="text-blue-800">{{ $withdrawal->processing_notes }}</p>
                        @if($withdrawal->processedByCommercial)
                            <p class="text-sm text-blue-600 mt-2">
                                — {{ $withdrawal->processedByCommercial->name }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Motif de rejet -->
            @if($withdrawal->rejection_reason)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">❌ Motif de rejet</h3>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                        <p class="text-red-800">{{ $withdrawal->rejection_reason }}</p>
                        @if($withdrawal->processedByCommercial)
                            <p class="text-sm text-red-600 mt-2">
                                — {{ $withdrawal->processedByCommercial->name }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Timeline</h3>
                
                <div class="space-y-4">
                    <!-- Création -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Demande créée</p>
                            <p class="text-xs text-gray-500">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($withdrawal->processed_at)
                        <!-- Traitement -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 {{ $withdrawal->status === 'REJECTED' ? 'bg-red-100' : 'bg-green-100' }} rounded-full flex items-center justify-center">
                                @if($withdrawal->status === 'REJECTED')
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $withdrawal->status === 'REJECTED' ? 'Demande rejetée' : 'Demande approuvée' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $withdrawal->processed_at->format('d/m/Y H:i') }}</p>
                                @if($withdrawal->processedByCommercial)
                                    <p class="text-xs text-gray-400">Par {{ $withdrawal->processedByCommercial->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($withdrawal->assignedDeliverer && $withdrawal->status !== 'REJECTED')
                        <!-- Assignment livreur -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Livreur assigné</p>
                                <p class="text-xs text-gray-500">{{ $withdrawal->assignedDeliverer->name }}</p>
                            </div>
                        </div>
                    @endif

                    @if($withdrawal->delivered_at)
                        <!-- Livraison terminée -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Livraison terminée</p>
                                <p class="text-xs text-gray-500">{{ $withdrawal->delivered_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($withdrawal->status === 'PENDING')
                        <!-- En attente -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <div class="w-2 h-2 bg-orange-400 rounded-full animate-pulse"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-orange-600">En attente de traitement</p>
                                <p class="text-xs text-gray-500">Sera traitée prochainement</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions</h3>
                
                <div class="space-y-3">
                    @if($withdrawal->canBeProcessed())
                        <form action="{{ route('client.wallet.withdrawal.cancel', $withdrawal) }}" 
                              method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ? Cette action est irréversible.')">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                                ❌ Annuler la demande
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('client.withdrawals') }}" 
                       class="block w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium text-center">
                        📋 Toutes mes demandes
                    </a>

                    <a href="{{ route('client.wallet.withdrawal') }}" 
                       class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium text-center">
                        💸 Nouvelle demande
                    </a>
                </div>
            </div>

            <!-- Informations utiles -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">💡 Bon à savoir</h3>
                <div class="text-sm text-blue-800 space-y-2">
                    @if($withdrawal->method === 'BANK_TRANSFER')
                        <p>• Les virements bancaires sont traités sous 24-48h ouvrables</p>
                        <p>• Vous recevrez une confirmation par email</p>
                        <p>• Vérifiez vos coordonnées bancaires</p>
                    @else
                        <p>• Le livreur vous contactera avant la livraison</p>
                        <p>• Ayez votre pièce d'identité à portée de main</p>
                        <p>• Le code de livraison est obligatoire</p>
                    @endif
                    <p>• En cas de problème, contactez le support</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-actualisation pour les demandes en cours
@if(in_array($withdrawal->status, ['PENDING', 'APPROVED', 'IN_PROGRESS']))
    setInterval(function() {
        location.reload();
    }, 60000); // 1 minute
@endif

// Animation d'entrée
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bg-white');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
@endsection