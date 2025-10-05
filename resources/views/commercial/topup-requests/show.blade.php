@extends('layouts.commercial')

@section('title', 'Demande de Recharge #' . $topupRequest->id)
@section('page-title', 'Demande de Recharge #' . $topupRequest->id)
@section('page-description', 'Détails de la demande de recharge')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('commercial.topup-requests.index') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Demande de Recharge #{{ $topupRequest->id }}</h1>
            <p class="text-gray-600">{{ $topupRequest->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Détails de la demande -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations principales -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Informations de la demande</h3>
                    @if($topupRequest->status === 'PENDING')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            En attente
                        </span>
                    @elseif($topupRequest->status === 'VALIDATED')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approuvé
                        </span>
                    @elseif($topupRequest->status === 'REJECTED')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeté
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            {{ $topupRequest->status }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de recharge</label>
                        <div class="flex items-center">
                            @if($topupRequest->type === 'BANK_TRANSFER')
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="text-gray-900">Virement bancaire</span>
                            @else
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-gray-900">Espèces</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant demandé</label>
                        <p class="text-2xl font-bold text-orange-600">{{ number_format($topupRequest->amount, 3) }} DT</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de demande</label>
                        <p class="text-gray-900">{{ $topupRequest->created_at->format('d/m/Y à H:i') }}</p>
                    </div>

                    @if($topupRequest->processed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de traitement</label>
                            <p class="text-gray-900">{{ $topupRequest->processed_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    @endif
                </div>

                @if($topupRequest->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes du client</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900">{{ $topupRequest->notes }}</p>
                        </div>
                    </div>
                @endif

                @if($topupRequest->processing_notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes de traitement</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900">{{ $topupRequest->processing_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($topupRequest->status === 'PENDING')
                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Actions</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Approuver -->
                        <form method="POST" action="{{ route('commercial.topup-requests.approve', $topupRequest) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="approval_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes d'approbation (optionnel)</label>
                                <textarea name="notes" id="approval_notes" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                          placeholder="Notes pour l'approbation..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full bg-green-500 text-white py-3 px-4 rounded-lg hover:bg-green-600 transition-colors font-medium"
                                    onclick="return confirm('Êtes-vous sûr de vouloir approuver cette demande ? Le wallet du client sera automatiquement crédité.')">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approuver la demande
                            </button>
                        </form>

                        <!-- Rejeter -->
                        <form method="POST" action="{{ route('commercial.topup-requests.reject', $topupRequest) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Raison du rejet <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                          placeholder="Raison du rejet..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full bg-red-500 text-white py-3 px-4 rounded-lg hover:bg-red-600 transition-colors font-medium"
                                    onclick="return confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rejeter la demande
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informations client -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Client</h3>

                @if($topupRequest->user)
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="text-orange-800 font-bold">{{ strtoupper(substr($topupRequest->user->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $topupRequest->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $topupRequest->user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Téléphone</span>
                            <span class="text-sm text-gray-900">{{ $topupRequest->user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Statut</span>
                            <span class="text-sm text-gray-900">{{ $topupRequest->user->account_status }}</span>
                        </div>
                        @if($topupRequest->user->userWallet)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Solde actuel</span>
                                <span class="text-sm font-medium text-orange-600">{{ number_format($topupRequest->user->userWallet->balance, 3) }} DT</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('commercial.clients.show', $topupRequest->user) }}"
                           class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                            Voir le profil client →
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-gray-500">Utilisateur supprimé</p>
                    </div>
                @endif
            </div>

            @if($topupRequest->processedBy)
                <!-- Traité par -->
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Traité par</h3>

                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-800 font-bold text-sm">{{ strtoupper(substr($topupRequest->processedBy->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $topupRequest->processedBy->name }}</p>
                            <p class="text-sm text-gray-500">{{ $topupRequest->processedBy->role }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Historique récent -->
            @if($recentTransactions->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recharges récentes</h3>

                    <div class="space-y-3">
                        @foreach($recentTransactions->take(5) as $transaction)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                <div>
                                    <p class="text-sm text-gray-900">{{ number_format($transaction->amount, 3) }} DT</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">{{ $transaction->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection