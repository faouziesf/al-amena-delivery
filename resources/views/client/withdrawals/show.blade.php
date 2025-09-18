@extends('layouts.client')

@section('title', "Demande de retrait #{$withdrawal->request_code}")

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header avec navigation -->
        <div class="flex items-center mb-6">
            <a href="{{ route('client.withdrawals') }}" 
               class="flex items-center text-purple-600 hover:text-purple-800 transition-all duration-300 mr-6 group">
                <div class="bg-purple-100 rounded-full p-2 mr-3 group-hover:bg-purple-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </div>
                <span class="font-medium">Retour aux demandes</span>
            </a>
            <div class="text-gray-400">•</div>
            <a href="{{ route('client.wallet.index') }}" 
               class="ml-4 text-gray-600 hover:text-gray-900 transition-colors font-medium">
                Portefeuille
            </a>
        </div>

        <!-- Titre principal -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                        💸 Demande #{{ $withdrawal->request_code }}
                    </h1>
                    <p class="text-gray-600">Détails et suivi de votre demande de retrait</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold {{ $withdrawal->status_color }} shadow-sm">
                        {{ $withdrawal->status_display }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Informations principales -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-purple-100">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Informations de la demande
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Montant demandé</p>
                                <p class="text-3xl font-bold text-purple-600">{{ number_format($withdrawal->amount, 3) }} DT</p>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Méthode de retrait</p>
                                <div class="flex items-center">
                                    @if($withdrawal->method === 'BANK_TRANSFER')
                                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                </div>

                <!-- Coordonnées bancaires (si applicable) -->
                @if($withdrawal->method === 'BANK_TRANSFER' && $withdrawal->bank_details)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                </svg>
                                Coordonnées bancaires
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <pre class="text-sm text-blue-900 whitespace-pre-wrap">{{ $withdrawal->bank_details }}</pre>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Informations de livraison (si applicable) -->
                @if($withdrawal->method === 'CASH_DELIVERY')
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-green-100">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 013-3h1m8-3a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Informations de livraison
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            @if($withdrawal->delivery_receipt_code)
                                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <p class="text-sm font-medium text-yellow-800 mb-2">🔑 Code de livraison</p>
                                    <p class="text-xl font-mono font-bold text-yellow-900">{{ $withdrawal->delivery_receipt_code }}</p>
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
                                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-green-800">✅ Livraison terminée</p>
                                            <p class="text-sm text-green-700">{{ $withdrawal->delivered_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Notes du commercial -->
                @if($withdrawal->processing_notes)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Note du commercial
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-xl">
                                <p class="text-blue-800">{{ $withdrawal->processing_notes }}</p>
                                @if($withdrawal->processedByCommercial)
                                    <p class="text-sm text-blue-600 mt-2 font-medium">
                                        — {{ $withdrawal->processedByCommercial->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Motif de rejet -->
                @if($withdrawal->rejection_reason)
                    <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-red-50 to-pink-50 px-6 py-4 border-b border-red-100">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Motif de rejet
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl">
                                <p class="text-red-800">{{ $withdrawal->rejection_reason }}</p>
                                @if($withdrawal->processedByCommercial)
                                    <p class="text-sm text-red-600 mt-2 font-medium">
                                        — {{ $withdrawal->processedByCommercial->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne latérale -->
            <div class="space-y-6">
                <!-- Timeline -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-purple-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Timeline
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Création -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Actions
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-3">
                        @if($withdrawal->canBeProcessed())
                            <form action="{{ route('client.wallet.withdrawal.cancel', $withdrawal) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ? Cette action est irréversible.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-red-500 to-pink-600 text-white px-4 py-3 rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300 text-sm font-medium transform hover:scale-105 shadow-lg">
                                    ❌ Annuler la demande
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('client.withdrawals') }}" 
                           class="block w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-xl hover:bg-gray-200 transition-all duration-300 text-sm font-medium text-center transform hover:scale-105">
                            📋 Toutes mes demandes
                        </a>

                        <a href="{{ route('client.wallet.withdrawal') }}" 
                           class="block w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-4 py-3 rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 text-sm font-medium text-center transform hover:scale-105 shadow-lg">
                            💸 Nouvelle demande
                        </a>
                    </div>
                </div>

                <!-- Informations utiles -->
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-amber-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Bon à savoir
                    </h3>
                    <div class="text-sm text-amber-800 space-y-2">
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