@extends('layouts.client')

@section('title', 'Mes demandes de retrait')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">📤 Mes demandes de retrait</h1>
            <p class="text-gray-600">Suivez l'état de vos demandes de retrait</p>
        </div>
        <div class="flex space-x-3 mt-4 md:mt-0">
            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                💸 Nouvelle demande
            </a>
            <a href="{{ route('client.wallet.index') }}" 
               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                ← Retour au portefeuille
            </a>
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

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-orange-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">En attente</p>
                    <p class="text-lg font-bold text-orange-600">
                        {{ $withdrawals->where('status', 'PENDING')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Approuvées</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ $withdrawals->whereIn('status', ['APPROVED', 'IN_PROGRESS'])->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Terminées</p>
                    <p class="text-lg font-bold text-green-600">
                        {{ $withdrawals->where('status', 'COMPLETED')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="bg-gray-100 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Montant total</p>
                    <p class="text-lg font-bold text-gray-900">
                        {{ number_format($withdrawals->sum('amount'), 3) }} DT
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes de retrait -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">📋 Historique des demandes</h3>
        </div>

        @if($withdrawals->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($withdrawals as $withdrawal)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <!-- Informations principales -->
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900 mr-3">
                                        #{{ $withdrawal->request_code }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $withdrawal->status_color }}">
                                        {{ $withdrawal->status_display }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600">Montant</p>
                                        <p class="font-semibold text-gray-900">{{ number_format($withdrawal->amount, 3) }} DT</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-gray-600">Méthode</p>
                                        <p class="font-semibold text-gray-900">{{ $withdrawal->method_display }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-gray-600">Date de demande</p>
                                        <p class="font-semibold text-gray-900">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>

                                @if($withdrawal->processing_notes)
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                        <p class="text-sm text-blue-800">
                                            <span class="font-medium">Note du commercial:</span>
                                            {{ $withdrawal->processing_notes }}
                                        </p>
                                    </div>
                                @endif

                                @if($withdrawal->rejection_reason)
                                    <div class="mt-3 p-3 bg-red-50 rounded-lg">
                                        <p class="text-sm text-red-800">
                                            <span class="font-medium">Motif de rejet:</span>
                                            {{ $withdrawal->rejection_reason }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Timeline et actions -->
                            <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col items-end">
                                <!-- Timeline simple -->
                                <div class="text-right text-sm space-y-1 mb-4">
                                    <div class="flex items-center justify-end">
                                        <span class="text-gray-600 mr-2">Créée</span>
                                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    
                                    @if($withdrawal->processed_at)
                                        <div class="flex items-center justify-end">
                                            <span class="text-gray-600 mr-2">
                                                {{ $withdrawal->status === 'APPROVED' ? 'Approuvée' : 'Traitée' }}
                                            </span>
                                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $withdrawal->processed_at->format('d/m/Y H:i') }}</p>
                                    @endif

                                    @if($withdrawal->delivered_at)
                                        <div class="flex items-center justify-end">
                                            <span class="text-gray-600 mr-2">Livrée</span>
                                            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $withdrawal->delivered_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    @if($withdrawal->method === 'CASH_DELIVERY' && $withdrawal->delivery_receipt_code)
                                        <div class="text-center">
                                            <p class="text-xs text-gray-600 mb-1">Code de livraison:</p>
                                            <p class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                                                {{ $withdrawal->delivery_receipt_code }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($withdrawal->assignedDeliverer)
                                        <div class="text-center">
                                            <p class="text-xs text-gray-600 mb-1">Livreur assigné:</p>
                                            <p class="text-sm font-semibold text-blue-600">
                                                {{ $withdrawal->assignedDeliverer->name }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($withdrawal->canBeProcessed())
                                        <button onclick="cancelWithdrawal('{{ $withdrawal->id }}')"
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Annuler
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Détails supplémentaires selon le statut -->
                        @if($withdrawal->status === 'IN_PROGRESS' && $withdrawal->method === 'CASH_DELIVERY')
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-yellow-800">
                                        <p class="font-semibold">Livraison en cours</p>
                                        <p>Un livreur vous contactera prochainement pour organiser la remise des espèces.</p>
                                        @if($withdrawal->assignedDeliverer)
                                            <p class="mt-1">
                                                <span class="font-medium">Livreur:</span> {{ $withdrawal->assignedDeliverer->name }}
                                                @if($withdrawal->assignedDeliverer->phone)
                                                    <span class="ml-2">📞 {{ $withdrawal->assignedDeliverer->formatted_phone }}</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($withdrawal->status === 'COMPLETED')
                            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div class="text-sm text-green-800">
                                        <p class="font-semibold">Retrait terminé avec succès</p>
                                        <p>Votre demande de retrait a été traitée le {{ $withdrawal->delivered_at?->format('d/m/Y à H:i') ?? $withdrawal->processed_at?->format('d/m/Y à H:i') }}.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($withdrawals->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        @else
            <!-- État vide -->
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune demande de retrait</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore fait de demande de retrait.</p>
                <a href="{{ route('client.wallet.withdrawal') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    💸 Faire une demande
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Fonction pour annuler une demande de retrait
function cancelWithdrawal(withdrawalId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette demande de retrait ? Cette action est irréversible.')) {
        // Créer un formulaire temporaire pour la requête POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/client/withdrawals/${withdrawalId}/cancel`;
        
        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Animation d'entrée des cartes
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('[class*="bg-white"]');
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

// Actualisation automatique du statut toutes les 2 minutes
setInterval(function() {
    const pendingWithdrawals = document.querySelectorAll('[data-status="PENDING"], [data-status="APPROVED"], [data-status="IN_PROGRESS"]');
    if (pendingWithdrawals.length > 0) {
        // Recharger la page si il y a des demandes en cours
        location.reload();
    }
}, 120000); // 2 minutes
</script>
@endpush
@endsection