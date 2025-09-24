@extends('layouts.client')

@section('title', 'Demande de rechargement #' . $topupRequest->request_code)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header avec navigation -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('client.wallet.topup.requests') }}" 
                   class="flex items-center text-purple-600 hover:text-purple-800 transition-all duration-300 mr-6 group">
                    <div class="bg-purple-100 rounded-full p-2 mr-3 group-hover:bg-purple-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </div>
                    <span class="font-medium">Retour aux demandes</span>
                </a>
            </div>
            
            @if($topupRequest->canBeCancelled())
                <form action="{{ route('client.wallet.topup.request.cancel', $topupRequest) }}" 
                      method="POST" class="inline"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                    @csrf
                    <button type="submit" 
                            class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-3 rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler la demande
                    </button>
                </form>
            @endif
        </div>

        <!-- Titre principal -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-purple-800 bg-clip-text text-transparent mb-3">
                💳 Demande #{{ $topupRequest->request_code }}
            </h1>
            <p class="text-gray-600 text-lg">Détails de votre demande de rechargement</p>
        </div>

        <!-- Alertes -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 border-l-4 border-purple-500 text-purple-800 px-6 py-4 rounded-xl mb-6 shadow-sm">
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

        <div class="max-w-4xl mx-auto">
            <!-- Informations principales -->
            <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-8 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Statut et montant -->
                    <div class="lg:col-span-1">
                        <div class="text-center">
                            <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-r {{ $topupRequest->status === 'PENDING' ? 'from-orange-100 to-yellow-100' : ($topupRequest->status === 'VALIDATED' ? 'from-green-100 to-emerald-100' : ($topupRequest->status === 'REJECTED' ? 'from-red-100 to-pink-100' : 'from-gray-100 to-slate-100')) }} rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 {{ $topupRequest->status === 'PENDING' ? 'text-orange-600' : ($topupRequest->status === 'VALIDATED' ? 'text-green-600' : ($topupRequest->status === 'REJECTED' ? 'text-red-600' : 'text-gray-600')) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($topupRequest->status === 'PENDING')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @elseif($topupRequest->status === 'VALIDATED')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @elseif($topupRequest->status === 'REJECTED')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                                    @endif
                                </svg>
                            </div>
                            
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-semibold {{ $topupRequest->status_color }} mb-4">
                                {{ $topupRequest->status_display }}
                            </span>
                            
                            <div class="text-4xl font-bold text-purple-600 mb-2">
                                {{ $topupRequest->formatted_amount }}
                            </div>
                            
                            <p class="text-gray-600">Montant demandé</p>
                        </div>
                    </div>

                    <!-- Détails de la demande -->
                    <div class="lg:col-span-2">
                        <h3 class="text-xl font-semibold text-gray-800 mb-6">Informations de la demande</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Code de demande</span>
                                <span class="font-semibold text-gray-900">{{ $topupRequest->request_code }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Méthode de paiement</span>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($topupRequest->method === 'BANK_TRANSFER')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        @elseif($topupRequest->method === 'BANK_DEPOSIT')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        @endif
                                    </svg>
                                    <span class="font-semibold text-gray-900">{{ $topupRequest->method_display }}</span>
                                </div>
                            </div>

                            @if($topupRequest->bank_transfer_id)
                                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Identifiant de {{ $topupRequest->method === 'BANK_TRANSFER' ? 'virement' : 'versement' }}</span>
                                    <span class="font-mono text-sm bg-gray-100 px-3 py-1 rounded-lg">{{ $topupRequest->bank_transfer_id }}</span>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600">Date de création</span>
                                <span class="font-semibold text-gray-900">{{ $topupRequest->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            
                            @if($topupRequest->processed_at)
                                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Date de traitement</span>
                                    <span class="font-semibold text-gray-900">{{ $topupRequest->processed_at->format('d/m/Y à H:i') }}</span>
                                </div>
                            @endif

                            @if($topupRequest->processedBy)
                                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Traité par</span>
                                    <span class="font-semibold text-gray-900">{{ $topupRequest->processedBy->name }} ({{ $topupRequest->processedBy->role_display }})</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between py-3">
                                <span class="text-gray-600">Délai de traitement estimé</span>
                                <span class="font-semibold text-gray-900">{{ $topupRequest->estimated_processing_time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes et justificatifs -->
            @if($topupRequest->notes || $topupRequest->proof_document)
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Informations complémentaires</h3>
                    
                    @if($topupRequest->notes)
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Vos notes</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-purple-500">
                                <p class="text-gray-700">{{ $topupRequest->notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($topupRequest->proof_document)
                        <div>
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Justificatif de paiement</h4>
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 border">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Justificatif uploadé</p>
                                        <p class="text-sm text-gray-500">{{ pathinfo($topupRequest->proof_document, PATHINFO_EXTENSION) === 'pdf' ? 'Document PDF' : 'Image' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('client.wallet.topup.request.download.proof', $topupRequest) }}" 
                                   class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Validation/Rejet -->
            @if($topupRequest->status === 'VALIDATED' || $topupRequest->status === 'REJECTED')
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">
                        @if($topupRequest->status === 'VALIDATED')
                            ✅ Demande validée
                        @else
                            ❌ Demande rejetée
                        @endif
                    </h3>
                    
                    @if($topupRequest->status === 'VALIDATED')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-green-800 mb-2">Votre demande a été acceptée</h4>
                                    <p class="text-green-700 mb-4">
                                        Le montant de <strong>{{ $topupRequest->formatted_amount }}</strong> a été ajouté à votre portefeuille.
                                    </p>
                                    @if($topupRequest->validation_notes)
                                        <div class="bg-white rounded-lg p-4 border-l-4 border-green-500">
                                            <p class="text-sm font-medium text-gray-900 mb-1">Notes de validation :</p>
                                            <p class="text-gray-700">{{ $topupRequest->validation_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-red-800 mb-2">Votre demande a été rejetée</h4>
                                    <p class="text-red-700 mb-4">
                                        Malheureusement, votre demande de rechargement n'a pas pu être acceptée.
                                    </p>
                                    @if($topupRequest->rejection_reason)
                                        <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
                                            <p class="text-sm font-medium text-gray-900 mb-1">Raison du rejet :</p>
                                            <p class="text-gray-700">{{ $topupRequest->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Timeline et actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Timeline -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Chronologie</h3>
                    
                    <div class="space-y-6">
                        <!-- Création -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Demande créée</p>
                                <p class="text-sm text-gray-500">{{ $topupRequest->created_at->format('d/m/Y à H:i') }}</p>
                                <p class="text-xs text-gray-400">{{ $topupRequest->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if($topupRequest->processed_at)
                            <!-- Traitement -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 {{ $topupRequest->status === 'VALIDATED' ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $topupRequest->status === 'VALIDATED' ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($topupRequest->status === 'VALIDATED')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        @endif
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $topupRequest->status === 'VALIDATED' ? 'Demande validée' : 'Demande rejetée' }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ $topupRequest->processed_at->format('d/m/Y à H:i') }}</p>
                                    <p class="text-xs text-gray-400">{{ $topupRequest->processed_at->diffForHumans() }}</p>
                                    @if($topupRequest->processedBy)
                                        <p class="text-xs text-gray-400">par {{ $topupRequest->processedBy->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- En attente -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">En attente de validation</p>
                                    <p class="text-sm text-gray-500">
                                        @if($topupRequest->requiresBankValidation())
                                            Votre demande sera traitée par notre équipe commerciale dans les {{ $topupRequest->estimated_processing_time }}.
                                        @else
                                            Un livreur vous contactera pour organiser la collecte.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-2xl shadow-lg border border-purple-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Actions</h3>
                    
                    <div class="space-y-4">
                        <a href="{{ route('client.wallet.index') }}" 
                           class="w-full bg-gradient-to-r from-purple-500 to-purple-700 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-purple-800 transition-all duration-300 font-semibold text-center block">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Voir mon portefeuille
                        </a>
                        
                        <a href="{{ route('client.wallet.topup.requests') }}" 
                           class="w-full bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition-all duration-300 font-semibold text-center block">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Toutes mes demandes
                        </a>
                        
                        @if($topupRequest->status !== 'PENDING')
                            <a href="{{ route('client.wallet.topup') }}" 
                               class="w-full bg-gradient-to-r from-purple-500 to-purple-700 text-white px-6 py-3 rounded-xl hover:from-purple-600 hover:to-purple-800 transition-all duration-300 font-semibold text-center block">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Nouvelle demande
                            </a>
                        @endif
                    </div>

                    <!-- Informations d'aide -->
                    <div class="mt-8 p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg border border-purple-200">
                        <h4 class="text-sm font-semibold text-purple-800 mb-2">💡 Besoin d'aide ?</h4>
                        <ul class="text-sm text-purple-700 space-y-1">
                            @if($topupRequest->isPending())
                                @if($topupRequest->requiresBankValidation())
                                    <li>• Assurez-vous que l'identifiant de virement est correct</li>
                                    <li>• Gardez votre justificatif bancaire</li>
                                @else
                                    <li>• Préparez le montant exact en espèces</li>
                                    <li>• Attendez le contact de notre livreur</li>
                                @endif
                            @elseif($topupRequest->isRejected())
                                <li>• Vérifiez la raison du rejet ci-dessus</li>
                                <li>• Vous pouvez créer une nouvelle demande</li>
                            @else
                                <li>• Votre solde a été mis à jour</li>
                                <li>• Vous pouvez maintenant créer des colis</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection