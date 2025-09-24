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
    <button onclick="approveWithdrawal()"
            class="px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Approuver
    </button>

    <button onclick="rejectWithdrawal()"
            class="px-4 py-2 bg-red-300 text-red-800 rounded-lg hover:bg-red-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Rejeter
    </button>
    @endif

    @if($withdrawal->status === 'APPROVED' && $withdrawal->method === 'CASH_DELIVERY')
    <button onclick="assignDeliverer()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Assigner Livreur
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="withdrawalShowApp()">

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
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                        {{ $withdrawal->status === 'PENDING' ? 'bg-yellow-500 text-white' :
                           ($withdrawal->status === 'APPROVED' ? 'bg-green-500 text-white' :
                           ($withdrawal->status === 'COMPLETED' ? 'bg-blue-500 text-white' : 'bg-red-500 text-white')) }}">
                        {{ $withdrawal->status === 'PENDING' ? 'En attente' :
                           ($withdrawal->status === 'APPROVED' ? 'Approuvé' :
                           ($withdrawal->status === 'COMPLETED' ? 'Terminé' : 'Rejeté')) }}
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
                    <button onclick="approveWithdrawal()"
                            class="w-full px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approuver la demande
                    </button>

                    <button onclick="rejectWithdrawal()"
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

                <button onclick="assignDeliverer()"
                        class="w-full px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Assigner un livreur
                </button>
            </div>
            @endif

            <!-- Informations complémentaires -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>

                <div class="space-y-3 text-sm">
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
                            {{ $withdrawal->status === 'PENDING' ? 'En attente' :
                               ($withdrawal->status === 'APPROVED' ? 'Approuvé' :
                               ($withdrawal->status === 'COMPLETED' ? 'Terminé' : 'Rejeté')) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('components.commercial.withdrawal-modals', ['withdrawal' => $withdrawal])
</div>
@endsection

@push('scripts')
<script>
function withdrawalShowApp() {
    return {
        withdrawal: @json($withdrawal),

        init() {
            // Initialisation si nécessaire
        }
    }
}

function approveWithdrawal() {
    if (confirm('Êtes-vous sûr de vouloir approuver cette demande de retrait ?')) {
        fetch(`/commercial/withdrawals/${window.withdrawalShowApp().withdrawal.id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Demande approuvée avec succès', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur lors de l\'approbation', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    }
}

function rejectWithdrawal() {
    const reason = prompt('Raison du rejet (optionnel):');
    if (reason !== null) {
        fetch(`/commercial/withdrawals/${window.withdrawalShowApp().withdrawal.id}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Demande rejetée', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur lors du rejet', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    }
}

function assignDeliverer() {
    // TODO: Implémenter l'assignment de livreur
    showToast('Fonctionnalité d\'assignment en cours de développement', 'info');
}
</script>
@endpush