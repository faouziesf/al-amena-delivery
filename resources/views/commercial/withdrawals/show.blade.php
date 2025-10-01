@extends('layouts.commercial')

@section('title', 'Demande de Retrait #' . $withdrawal->request_code)
@section('page-title', 'Détails de la Demande de Retrait')
@section('page-description', 'Demande #' . $withdrawal->request_code . ' - ' . number_format($withdrawal->amount, 3) . ' DT')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.withdrawals.index') }}"
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>

    @if($withdrawal->status === 'PENDING')
    <button onclick="window.withdrawalApp?.approveWithdrawal()"
            class="px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Approuver
    </button>

    <button onclick="window.withdrawalApp?.rejectWithdrawal()"
            class="px-4 py-2 bg-red-300 text-red-800 rounded-lg hover:bg-red-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Rejeter
    </button>
    @endif

    @if($withdrawal->status === 'APPROVED' && $withdrawal->method === 'BANK_TRANSFER')
    <button onclick="window.withdrawalApp?.markAsProcessed()"
            class="px-4 py-2 bg-teal-300 text-teal-800 rounded-lg hover:bg-teal-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Marquer comme Traité
    </button>
    @endif

    @if($withdrawal->status === 'PROCESSED' && $withdrawal->method === 'BANK_TRANSFER')
    <button onclick="window.withdrawalApp?.markAsDelivered()"
            class="px-4 py-2 bg-emerald-300 text-emerald-800 rounded-lg hover:bg-emerald-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Marquer comme Livré
    </button>
    @endif

    @if($withdrawal->status === 'APPROVED' && $withdrawal->method === 'CASH_DELIVERY')
    <button onclick="window.withdrawalApp?.openAssignModal()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Assigner Livreur
    </button>
    @endif

    @if(in_array($withdrawal->status, ['READY_FOR_DELIVERY', 'IN_PROGRESS']) && $withdrawal->method === 'CASH_DELIVERY')
    <button onclick="window.withdrawalApp?.markAsDelivered()"
            class="px-4 py-2 bg-emerald-300 text-emerald-800 rounded-lg hover:bg-emerald-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Marquer comme Livré
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="withdrawalShowApp()" x-init="init()">

    <!-- En-tête avec informations principales -->
    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-xl shadow-lg text-purple-800 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $withdrawal->request_code }}</h1>
                <p class="text-purple-700">
                    Demande de {{ $withdrawal->client->name }}
                    - {{ number_format($withdrawal->amount, 3) }} DT
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $withdrawal->status_color }}">
                        {{ $withdrawal->status_display }}
                    </span>
                    <span class="text-purple-700 text-sm">
                        {{ $withdrawal->created_at->format('d/m/Y à H:i') }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-4xl font-bold text-purple-800">{{ number_format($withdrawal->amount, 3) }}</div>
                <div class="text-sm text-purple-700">Dinars Tunisiens</div>
                <div class="text-sm text-purple-600 mt-1">
                    {{ $withdrawal->method === 'BANK_TRANSFER' ? 'Virement bancaire' : 'Livraison cash' }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Informations du Client -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informations du Client
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $withdrawal->client->name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $withdrawal->client->email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $withdrawal->client->phone }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Solde actuel</label>
                        <div class="mt-1 text-sm font-semibold text-green-600">
                            {{ number_format($withdrawal->client->wallet->balance ?? 0, 3) }} DT
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('commercial.clients.show', $withdrawal->client) }}"
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        → Voir le profil complet du client
                    </a>
                </div>
            </div>

            <!-- Détails de la Demande -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Détails de la Demande
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Code de demande</label>
                            <div class="mt-1 text-sm font-mono text-gray-900">{{ $withdrawal->request_code }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant demandé</label>
                            <div class="mt-1 text-lg font-bold text-purple-600">{{ number_format($withdrawal->amount, 3) }} DT</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Méthode</label>
                            <div class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $withdrawal->method === 'BANK_TRANSFER' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $withdrawal->method === 'BANK_TRANSFER' ? 'Virement bancaire' : 'Livraison cash' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de création</label>
                            <div class="mt-1 text-sm text-gray-900">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>

                    @if($withdrawal->method === 'BANK_TRANSFER' && $withdrawal->bank_details)
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Détails bancaires</h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            @if(isset($withdrawal->bank_details['bank_name']))
                            <div>
                                <span class="text-sm font-medium text-gray-700">Banque:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $withdrawal->bank_details['bank_name'] }}</span>
                            </div>
                            @endif
                            @if(isset($withdrawal->bank_details['rib']))
                            <div>
                                <span class="text-sm font-medium text-gray-700">RIB:</span>
                                <span class="text-sm font-mono text-gray-900 ml-2">{{ $withdrawal->bank_details['rib'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historique des Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique des Actions
                </h3>

                <div class="space-y-4">
                    <!-- Action de création -->
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Demande créée</span> par {{ $withdrawal->client->name }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>

                    @if($withdrawal->processed_at)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Demande {{ $withdrawal->status === 'APPROVED' ? 'approuvée' : 'traitée' }}</span>
                                @if($withdrawal->processedBy)
                                par {{ $withdrawal->processedBy->name }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $withdrawal->processed_at->format('d/m/Y à H:i') }}</div>
                            @if($withdrawal->processing_notes)
                            <div class="text-xs text-gray-600 mt-1">{{ $withdrawal->processing_notes }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($withdrawal->delivered_at)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-2 h-2 bg-purple-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Livré avec succès</span>
                                @if($withdrawal->assignedDeliverer)
                                par {{ $withdrawal->assignedDeliverer->name }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $withdrawal->delivered_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne de droite -->
        <div class="space-y-6">

            <!-- Actions Rapides -->
            @if($withdrawal->status === 'PENDING')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button @click="approveWithdrawal()"
                            class="w-full px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approuver la demande
                    </button>

                    <button @click="rejectWithdrawal()"
                            class="w-full px-4 py-2 bg-red-300 text-red-800 rounded-lg hover:bg-red-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rejeter la demande
                    </button>
                </div>
            </div>
            @endif

            @if($withdrawal->status === 'APPROVED' && $withdrawal->method === 'CASH_DELIVERY' && !$withdrawal->assigned_deliverer_id)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Attribution</h3>

                <button @click="openAssignModal()"
                        class="w-full px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Assigner un livreur
                </button>
            </div>
            @endif

            <!-- Actions d'impression -->
            @if($withdrawal->status !== 'PENDING' && $withdrawal->delivery_receipt_code)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents</h3>

                <div class="space-y-3">
                    <button @click="printReceipt()"
                            class="w-full px-4 py-2 bg-blue-300 text-blue-800 rounded-lg hover:bg-blue-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimer le reçu
                    </button>

                    <a href="{{ route('commercial.withdrawals.delivery-receipt', $withdrawal) }}" target="_blank"
                       class="w-full px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Aperçu du reçu
                    </a>
                </div>
            </div>
            @endif

            <!-- Informations complémentaires -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>

                <div class="space-y-3 text-sm">
                    <!-- Informations toujours affichées -->
                    <div>
                        <span class="font-medium text-gray-700">Code de demande:</span>
                        <div class="font-mono text-gray-900">{{ $withdrawal->request_code }}</div>
                    </div>

                    <div>
                        <span class="font-medium text-gray-700">Date de création:</span>
                        <div class="text-gray-900">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    <div>
                        <span class="font-medium text-gray-700">Statut:</span>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $withdrawal->status_color }}">
                                {{ $withdrawal->status_display }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="font-medium text-gray-700">Méthode:</span>
                        <div class="text-gray-900">{{ $withdrawal->method_display }}</div>
                    </div>

                    @if($withdrawal->reason)
                    <div>
                        <span class="font-medium text-gray-700">Motif:</span>
                        <div class="text-gray-900">{{ $withdrawal->reason }}</div>
                    </div>
                    @endif

                    @if($withdrawal->delivery_receipt_code)
                    <div>
                        <span class="font-medium text-gray-700">Code de reçu:</span>
                        <div class="font-mono text-gray-900">{{ $withdrawal->delivery_receipt_code }}</div>
                    </div>
                    @endif

                    @if($withdrawal->assignedDeliverer)
                    <div>
                        <span class="font-medium text-gray-700">Livreur assigné:</span>
                        <div class="text-gray-900">{{ $withdrawal->assignedDeliverer->name }}</div>
                    </div>
                    @endif

                    @if($withdrawal->processed_at)
                    <div>
                        <span class="font-medium text-gray-700">Traité le:</span>
                        <div class="text-gray-900">{{ $withdrawal->processed_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @endif

                    @if($withdrawal->processing_notes)
                    <div>
                        <span class="font-medium text-gray-700">Notes de traitement:</span>
                        <div class="text-gray-900">{{ $withdrawal->processing_notes }}</div>
                    </div>
                    @endif

                    @if($withdrawal->delivered_at)
                    <div>
                        <span class="font-medium text-gray-700">Livré le:</span>
                        <div class="text-gray-900">{{ $withdrawal->delivered_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @endif

                    @if($withdrawal->rejection_reason)
                    <div>
                        <span class="font-medium text-gray-700">Raison du rejet:</span>
                        <div class="text-red-600">{{ $withdrawal->rejection_reason }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Récapitulatif -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Récapitulatif</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-purple-700">Montant demandé:</span>
                        <span class="font-semibold text-purple-900">{{ number_format($withdrawal->amount, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Méthode:</span>
                        <span class="text-purple-900">{{ $withdrawal->method === 'BANK_TRANSFER' ? 'Virement' : 'Cash' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Statut:</span>
                        <span class="font-semibold text-purple-900">
                            {{ $withdrawal->status_display }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'assignation de livreur -->
    <div x-show="showAssignModal" @click.away="showAssignModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Assigner un livreur
                            </h3>
                            <div class="mt-4">
                                <form @submit.prevent="assignDeliverer()">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Sélectionner un livreur
                                        </label>
                                        <select x-model="selectedDeliverer"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="">-- Choisir un livreur --</option>
                                            <template x-for="deliverer in deliverers" :key="deliverer.id">
                                                <option :value="deliverer.id" x-text="`${deliverer.name} - ${deliverer.phone}`"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Notes (optionnel)
                                        </label>
                                        <textarea x-model="assignNotes"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                  rows="3" placeholder="Notes pour le livreur..."></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="assignDeliverer()"
                            :disabled="!selectedDeliverer"
                            :class="!selectedDeliverer ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Assigner
                    </button>
                    <button @click="showAssignModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales existantes -->
    @include('components.commercial.withdrawal-modals', ['withdrawal' => $withdrawal])
</div>
@endsection

@push('scripts')
<script>
// Alpine.js data function
function withdrawalShowApp() {
    return {
        withdrawal: @json($withdrawal),
        showAssignModal: false,
        deliverers: [],
        selectedDeliverer: '',
        assignNotes: '',
        loading: false,

        async init() {
            await this.loadDeliverers();
            // Expose to window for header buttons
            window.withdrawalApp = this;
        },

        async loadDeliverers() {
            try {
                const response = await fetch('{{ route("commercial.api.deliverers.active") }}');
                if (response.ok) {
                    this.deliverers = await response.json();
                }
            } catch (error) {
                console.error('Erreur chargement livreurs:', error);
            }
        },

        openAssignModal() {
            this.showAssignModal = true;
        },

        async assignDeliverer() {
            if (!this.selectedDeliverer) {
                showToast('Veuillez sélectionner un livreur', 'warning');
                return;
            }

            if (this.loading) return;
            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('deliverer_id', this.selectedDeliverer);
                formData.append('notes', this.assignNotes);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(`/commercial/withdrawals/${this.withdrawal.id}/assign-deliverer`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message || 'Livreur assigné avec succès', 'success');
                    this.showAssignModal = false;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.error || data.message || 'Erreur lors de l\'assignation', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async approveWithdrawal() {
            if (confirm('Êtes-vous sûr de vouloir approuver cette demande de retrait ?')) {
                try {
                    const response = await fetch(`/commercial/withdrawals/${this.withdrawal.id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        showToast('Demande approuvée avec succès', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors de l\'approbation', 'error');
                    }
                } catch (error) {
                    showToast('Erreur de connexion', 'error');
                }
            }
        },

        async rejectWithdrawal() {
            const reason = prompt('Raison du rejet (optionnel):');
            if (reason !== null) {
                try {
                    const response = await fetch(`/commercial/withdrawals/${this.withdrawal.id}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ rejection_reason: reason })
                    });

                    const data = await response.json();
                    if (data.success) {
                        showToast('Demande rejetée', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors du rejet', 'error');
                    }
                } catch (error) {
                    showToast('Erreur de connexion', 'error');
                }
            }
        },

        printReceipt() {
            if (this.withdrawal.delivery_receipt_code) {
                window.open(`/commercial/withdrawals/${this.withdrawal.id}/delivery-receipt`, '_blank');
            } else {
                showToast('Aucun reçu disponible', 'warning');
            }
        },

        async markAsProcessed() {
            const notes = prompt('Notes de traitement (optionnel):');
            if (notes !== null) {
                try {
                    const response = await fetch(`/commercial/withdrawals/${this.withdrawal.id}/mark-processed`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ processing_notes: notes })
                    });

                    const data = await response.json();
                    if (data.success) {
                        showToast('Virement marqué comme traité', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors du traitement', 'error');
                    }
                } catch (error) {
                    showToast('Erreur de connexion', 'error');
                }
            }
        },

        async markAsDelivered() {
            const notes = prompt('Notes de livraison (optionnel):');
            const isBank = this.withdrawal.method === 'BANK_TRANSFER';
            const confirmMsg = isBank ?
                'Confirmer la finalisation de ce virement bancaire (montant sera débité du compte client) ?' :
                'Confirmer la livraison de ce retrait en espèces ?';

            if (notes !== null && confirm(confirmMsg)) {
                try {
                    const response = await fetch(`/commercial/withdrawals/${this.withdrawal.id}/delivered`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ delivery_notes: notes })
                    });

                    const data = await response.json();
                    if (data.success) {
                        const successMsg = isBank ? 'Virement finalisé avec succès' : 'Retrait marqué comme livré';
                        showToast(successMsg, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Erreur lors de la finalisation', 'error');
                    }
                } catch (error) {
                    showToast('Erreur de connexion', 'error');
                }
            }
        }
    };
}


// Fonction utilitaire pour les toasts
function showToast(message, type = 'info') {
    // Créer l'élément toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transform transition-all duration-300 translate-x-full opacity-0`;

    // Définir les couleurs selon le type
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    toast.classList.add(colors[type] || colors.info);
    toast.textContent = message;

    // Ajouter au DOM
    document.body.appendChild(toast);

    // Animer l'entrée
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Supprimer après 3 secondes
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}
</script>
@endpush