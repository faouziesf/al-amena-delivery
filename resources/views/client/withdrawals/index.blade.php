@extends('layouts.client')

@section('title', 'Mes demandes de retrait')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    📤 Mes demandes de retrait
                </h1>
                <p class="text-gray-600">Suivez l'état de vos demandes de retrait en temps réel</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('client.wallet.withdrawal') }}" 
                   class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle demande
                </a>
                <a href="{{ route('client.wallet.index') }}" 
                   class="bg-white text-purple-600 border-2 border-purple-200 px-6 py-3 rounded-xl hover:bg-purple-50 transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour au portefeuille
                </a>
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

        <!-- Statistiques des demandes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- En attente -->
            <div class="bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl p-6 shadow-lg border border-amber-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-700 text-sm font-medium">En attente</p>
                        <p class="text-3xl font-bold text-amber-800">
                            {{ $withdrawals->where('status', 'PENDING')->count() }}
                        </p>
                        <p class="text-amber-600 text-sm mt-1">À traiter</p>
                    </div>
                    <div class="bg-amber-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @if($withdrawals->where('status', 'PENDING')->count() > 0)
                    <div class="mt-3 bg-amber-200/50 rounded-lg p-2">
                        <p class="text-xs text-amber-800 font-medium">⏳ Traitement en cours...</p>
                    </div>
                @endif
            </div>

            <!-- Approuvées/En cours -->
            <div class="bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl p-6 shadow-lg border border-blue-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-700 text-sm font-medium">En cours</p>
                        <p class="text-3xl font-bold text-blue-800">
                            {{ $withdrawals->whereIn('status', ['APPROVED', 'IN_PROGRESS'])->count() }}
                        </p>
                        <p class="text-blue-600 text-sm mt-1">Approuvées</p>
                    </div>
                    <div class="bg-blue-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @if($withdrawals->whereIn('status', ['APPROVED', 'IN_PROGRESS'])->count() > 0)
                    <div class="mt-3 bg-blue-200/50 rounded-lg p-2">
                        <p class="text-xs text-blue-800 font-medium">🚀 En traitement...</p>
                    </div>
                @endif
            </div>

            <!-- Terminées -->
            <div class="bg-gradient-to-br from-emerald-100 to-green-100 rounded-2xl p-6 shadow-lg border border-emerald-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-700 text-sm font-medium">Terminées</p>
                        <p class="text-3xl font-bold text-emerald-800">
                            {{ $withdrawals->where('status', 'COMPLETED')->count() }}
                        </p>
                        <p class="text-emerald-600 text-sm mt-1">Réussies</p>
                    </div>
                    <div class="bg-emerald-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Montant total -->
            <div class="bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl p-6 shadow-lg border border-purple-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-700 text-sm font-medium">Montant total</p>
                        <p class="text-2xl font-bold text-purple-800">
                            {{ number_format($withdrawals->sum('amount'), 3) }} DT
                        </p>
                        <p class="text-purple-600 text-sm mt-1">Toutes demandes</p>
                    </div>
                    <div class="bg-purple-200 rounded-full p-3">
                        <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des demandes de retrait -->
        <div class="bg-white rounded-2xl shadow-lg border border-purple-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2 sm:mb-0 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Historique des demandes
                    </h3>
                    @if($withdrawals->count() > 0)
                        <p class="text-sm text-gray-500">{{ $withdrawals->count() }} demande{{ $withdrawals->count() > 1 ? 's' : '' }}</p>
                    @endif
                </div>
            </div>

            @if($withdrawals->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($withdrawals as $withdrawal)
                        <div class="p-6 hover:bg-gradient-to-r hover:from-purple-25 hover:to-indigo-25 transition-all duration-300" 
                             data-status="{{ $withdrawal->status }}">
                            <div class="flex flex-col xl:flex-row xl:items-center justify-between">
                                <!-- Informations principales -->
                                <div class="flex-1">
                                    <!-- Header de la demande -->
                                    <div class="flex flex-wrap items-center gap-3 mb-4">
                                        <h4 class="text-xl font-bold text-gray-900 flex items-center">
                                            <span class="mr-2">{{ $withdrawal->method === 'BANK_TRANSFER' ? '🏦' : '💵' }}</span>
                                            #{{ $withdrawal->request_code }}
                                        </h4>
                                        
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $withdrawal->status_color }}">
                                            {{ $withdrawal->status_display }}
                                        </span>

                                        <div class="text-2xl font-bold {{ $withdrawal->status === 'COMPLETED' ? 'text-emerald-600' : ($withdrawal->status === 'REJECTED' ? 'text-red-600' : 'text-purple-600') }}">
                                            {{ number_format($withdrawal->amount, 3) }} DT
                                        </div>
                                    </div>
                                    
                                    <!-- Détails de la demande -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                            </svg>
                                            <span class="font-medium">{{ $withdrawal->method_display }}</span>
                                        </div>
                                        
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>

                                        @if($withdrawal->processed_at)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Traité le {{ $withdrawal->processed_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Timeline visuelle simple -->
                                    <div class="flex items-center space-x-2 mb-4">
                                        <!-- Étape 1: Créée -->
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-blue-500 rounded-full flex-shrink-0"></div>
                                            <span class="text-xs text-gray-500 ml-1 mr-3">Créée</span>
                                        </div>
                                        
                                        <!-- Ligne de progression -->
                                        <div class="flex-1 h-0.5 bg-gray-200 relative">
                                            @if($withdrawal->processed_at)
                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 {{ $withdrawal->status === 'COMPLETED' ? 'to-green-500' : ($withdrawal->status === 'REJECTED' ? 'to-red-500' : 'to-amber-500') }}"></div>
                                            @endif
                                        </div>

                                        @if($withdrawal->processed_at)
                                            <!-- Étape 2: Traitée -->
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 {{ $withdrawal->status === 'COMPLETED' ? 'bg-green-500' : ($withdrawal->status === 'REJECTED' ? 'bg-red-500' : 'bg-amber-500') }} rounded-full flex-shrink-0"></div>
                                                <span class="text-xs text-gray-500 ml-1">
                                                    {{ $withdrawal->status === 'COMPLETED' ? 'Terminée' : ($withdrawal->status === 'REJECTED' ? 'Rejetée' : 'Approuvée') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-gray-300 rounded-full flex-shrink-0 animate-pulse"></div>
                                                <span class="text-xs text-gray-400 ml-1">En attente</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Informations contextuelles -->
                                    @if($withdrawal->processing_notes)
                                        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg mb-3">
                                            <p class="text-sm text-blue-800">
                                                <span class="font-semibold">💬 Note du commercial:</span>
                                                {{ $withdrawal->processing_notes }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($withdrawal->rejection_reason)
                                        <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded-r-lg mb-3">
                                            <p class="text-sm text-red-800">
                                                <span class="font-semibold">❌ Motif de rejet:</span>
                                                {{ $withdrawal->rejection_reason }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($withdrawal->assignedDeliverer)
                                        <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded-r-lg mb-3">
                                            <p class="text-sm text-purple-800">
                                                <span class="font-semibold">🚚 Livreur assigné:</span>
                                                {{ $withdrawal->assignedDeliverer->name }}
                                                @if($withdrawal->assignedDeliverer->phone)
                                                    <span class="ml-2">📞 {{ $withdrawal->assignedDeliverer->formatted_phone }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif

                                    @if($withdrawal->delivery_receipt_code)
                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg mb-3">
                                            <p class="text-sm text-yellow-800">
                                                <span class="font-semibold">🔑 Code de livraison:</span>
                                                <span class="font-mono font-bold ml-2 bg-yellow-100 px-2 py-1 rounded">{{ $withdrawal->delivery_receipt_code }}</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions et statut spécial -->
                                <div class="flex flex-col xl:items-end gap-4 mt-4 xl:mt-0 xl:ml-6">
                                    <!-- Statuts spéciaux avec actions -->
                                    @if($withdrawal->status === 'IN_PROGRESS' && $withdrawal->method === 'CASH_DELIVERY')
                                        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-4 max-w-md">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <div class="text-sm text-amber-800">
                                                    <p class="font-semibold">🚚 Livraison en cours</p>
                                                    <p class="mt-1">Un livreur vous contactera prochainement pour organiser la remise des espèces.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($withdrawal->status === 'COMPLETED')
                                        <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-xl p-4 max-w-md">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <div class="text-sm text-emerald-800">
                                                    <p class="font-semibold">✅ Retrait terminé avec succès</p>
                                                    <p class="text-xs mt-1">{{ $withdrawal->delivered_at?->format('d/m/Y à H:i') ?? $withdrawal->processed_at?->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Boutons d'action -->
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('client.wallet.withdrawal.show', $withdrawal) }}" 
                                           class="bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 px-4 py-2 rounded-lg hover:from-purple-200 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105 text-sm font-medium">
                                            📄 Voir détails
                                        </a>

                                        @if($withdrawal->canBeProcessed())
                                            <button onclick="cancelWithdrawal('{{ $withdrawal->id }}')"
                                                    class="bg-gradient-to-r from-red-100 to-pink-100 text-red-700 px-4 py-2 rounded-lg hover:from-red-200 hover:to-pink-200 transition-all duration-300 transform hover:scale-105 text-sm font-medium">
                                                ❌ Annuler
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Temps relatif -->
                                    <p class="text-xs text-gray-500 text-right">
                                        {{ $withdrawal->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($withdrawals->hasPages())
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-gray-200">
                        <div class="flex justify-center">
                            {{ $withdrawals->links('pagination::tailwind') }}
                        </div>
                    </div>
                @endif
            @else
                <!-- État vide -->
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Aucune demande de retrait</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        Vous n'avez pas encore fait de demande de retrait. Commencez par faire votre première demande pour retirer vos fonds.
                    </p>
                    
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ route('client.wallet.withdrawal') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Faire ma première demande
                        </a>
                        
                        <a href="{{ route('client.wallet.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-white text-purple-600 border-2 border-purple-200 rounded-xl hover:bg-purple-50 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Voir mon portefeuille
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour annuler une demande de retrait
function cancelWithdrawal(withdrawalId) {
    if (confirm('⚠️ Êtes-vous sûr de vouloir annuler cette demande de retrait ?\n\nCette action est irréversible et le montant sera dégelé dans votre portefeuille.')) {
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

document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des cartes
    const cards = document.querySelectorAll('[class*="transform hover:scale-105"]');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Mise en surbrillance des demandes récentes (moins de 1 heure)
    const now = new Date();
    const oneHourAgo = new Date(now.getTime() - 60 * 60 * 1000);
    
    const withdrawalCards = document.querySelectorAll('[data-status]');
    withdrawalCards.forEach(card => {
        const timeElements = card.querySelectorAll('time, [datetime]');
        timeElements.forEach(element => {
            const dateValue = element.dateTime || element.textContent;
            const cardDate = new Date(dateValue);
            
            if (cardDate > oneHourAgo) {
                const indicator = document.createElement('span');
                indicator.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2 animate-bounce';
                indicator.innerHTML = '🆕 Nouvelle';
                card.querySelector('h4').appendChild(indicator);
            }
        });
    });

    // Auto-refresh pour les demandes en cours
    const pendingWithdrawals = document.querySelectorAll('[data-status="PENDING"], [data-status="APPROVED"], [data-status="IN_PROGRESS"]');
    if (pendingWithdrawals.length > 0) {
        // Actualisation automatique toutes les 2 minutes
        setInterval(function() {
            // Ajouter un indicateur de rafraîchissement
            const indicator = document.createElement('div');
            indicator.className = 'fixed top-4 right-4 bg-blue-500 text-white px-3 py-2 rounded-lg shadow-lg text-sm z-50';
            indicator.innerHTML = '🔄 Actualisation...';
            document.body.appendChild(indicator);
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        }, 120000); // 2 minutes
    }

    // Animation des barres de progression
    const progressBars = document.querySelectorAll('[class*="bg-gradient-to-r"]');
    progressBars.forEach(bar => {
        if (bar.classList.contains('absolute')) {
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = '100%';
            }, 500);
        }
    });

    // Amélioration des tooltips pour les codes de livraison
    const deliveryCodes = document.querySelectorAll('.font-mono.font-bold');
    deliveryCodes.forEach(code => {
        code.addEventListener('click', function() {
            // Copier le code dans le presse-papiers
            if (navigator.clipboard) {
                navigator.clipboard.writeText(this.textContent).then(() => {
                    // Feedback visuel
                    const original = this.textContent;
                    this.textContent = '✅ Copié!';
                    this.classList.add('bg-green-100', 'text-green-800');
                    
                    setTimeout(() => {
                        this.textContent = original;
                        this.classList.remove('bg-green-100', 'text-green-800');
                    }, 2000);
                });
            }
        });
        
        // Ajouter un curseur pointer et un titre
        code.style.cursor = 'pointer';
        code.title = 'Cliquer pour copier le code';
    });

    // Gestion des touches clavier
    document.addEventListener('keydown', function(e) {
        // 'N' pour nouvelle demande
        if (e.key === 'n' || e.key === 'N') {
            if (!e.ctrlKey && !e.metaKey && !e.target.matches('input, textarea')) {
                window.location.href = '{{ route("client.wallet.withdrawal") }}';
            }
        }
        
        // 'R' pour rafraîchir
        if (e.key === 'r' || e.key === 'R') {
            if (!e.ctrlKey && !e.metaKey && !e.target.matches('input, textarea')) {
                location.reload();
            }
        }
    });
});

// Lazy loading pour les images si nécessaire
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
        }
    });
}, observerOptions);

// Observer les cartes de retraits
document.querySelectorAll('.bg-white').forEach(card => {
    observer.observe(card);
});
</script>
@endpush

@push('styles')
<style>
.purple-25 {
    background-color: rgba(139, 92, 246, 0.025);
}

.hover\:from-purple-25:hover {
    background-image: linear-gradient(to right, rgba(139, 92, 246, 0.025), rgba(99, 102, 241, 0.025));
}

/* Animation fade-in */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

/* Progress bar animation */
.progress-bar {
    transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* États de hover améliorés */
.withdrawal-card:hover {
    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
}

/* Timeline améliorée */
.timeline-step {
    position: relative;
}

.timeline-step::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 100%;
    width: 20px;
    height: 2px;
    background: linear-gradient(to right, currentColor, transparent);
    transform: translateY(-50%);
}

/* Responsive design */
@media (max-width: 640px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .text-3xl {
        font-size: 1.875rem;
        line-height: 2.25rem;
    }
}

/* Loading state pour les boutons */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Améliorations des modales */
.modal-backdrop {
    backdrop-filter: blur(4px);
    background-color: rgba(0, 0, 0, 0.5);
}

/* États d'erreur et de succès */
.error-state {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.success-state {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
@endpush
@endsection