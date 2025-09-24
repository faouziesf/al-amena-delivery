@extends('layouts.commercial')

@section('title', 'Réclamation #' . $complaint->id)
@section('page-title', 'Détails de la Réclamation')
@section('page-description', 'Réclamation #' . $complaint->id . ' - ' . $complaint->type)

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.complaints.index') }}"
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>

    @if($complaint->status === 'PENDING')
    <button onclick="resolveComplaint()"
            class="px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Résoudre
    </button>

    <button onclick="rejectComplaint()"
            class="px-4 py-2 bg-red-300 text-red-800 rounded-lg hover:bg-red-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Rejeter
    </button>
    @endif

    @if($complaint->package && $complaint->type === 'COD_MODIFICATION')
    <button onclick="modifyCod()"
            class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Modifier COD
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="complaintShowApp()">

    <!-- En-tête avec informations principales -->
    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-xl shadow-lg text-purple-800 p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Réclamation #{{ $complaint->id }}</h1>
                <p class="text-purple-700">
                    {{ $complaint->client->name }}
                    @if($complaint->package)
                    - Colis {{ $complaint->package->tracking_number }}
                    @endif
                </p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                        {{ $complaint->status === 'PENDING' ? 'bg-yellow-500 text-white' :
                           ($complaint->status === 'RESOLVED' ? 'bg-green-500 text-white' : 'bg-red-500 text-white') }}">
                        {{ $complaint->status === 'PENDING' ? 'En attente' :
                           ($complaint->status === 'RESOLVED' ? 'Résolue' : 'Rejetée') }}
                    </span>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $complaint->type === 'COD_MODIFICATION' ? 'Modification COD' :
                           ($complaint->type === 'DELIVERY_DELAY' ? 'Retard de livraison' :
                           ($complaint->type === 'DAMAGED_PACKAGE' ? 'Colis endommagé' :
                           ($complaint->type === 'WRONG_ADDRESS' ? 'Mauvaise adresse' : 'Autre'))) }}
                    </span>
                    <span class="text-purple-700 text-sm">
                        {{ $complaint->created_at->format('d/m/Y à H:i') }}
                    </span>
                </div>
            </div>
            @if($complaint->priority === 'HIGH')
            <div class="text-right">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Priorité élevée
                </span>
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Description de la réclamation -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Description de la réclamation
                </h3>

                <div class="prose max-w-none text-gray-700">
                    {{ $complaint->description }}
                </div>

                @if($complaint->type === 'COD_MODIFICATION' && $complaint->details)
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-3">Détails de la modification COD</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        @if(isset($complaint->details['old_cod_amount']))
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Montant COD actuel:</span>
                            <span class="text-sm text-gray-900">{{ number_format($complaint->details['old_cod_amount'], 3) }} DT</span>
                        </div>
                        @endif
                        @if(isset($complaint->details['new_cod_amount']))
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Nouveau montant demandé:</span>
                            <span class="text-sm font-semibold text-purple-600">{{ number_format($complaint->details['new_cod_amount'], 3) }} DT</span>
                        </div>
                        @endif
                        @if(isset($complaint->details['reason']))
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Raison:</span>
                            <div class="text-sm text-gray-900 mt-1">{{ $complaint->details['reason'] }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Informations du Colis (si applicable) -->
            @if($complaint->package)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Informations du Colis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Numéro de suivi</label>
                        <div class="mt-1 text-sm font-mono text-gray-900">{{ $complaint->package->tracking_number }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Statut</label>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $complaint->package->status === 'DELIVERED' ? 'bg-green-100 text-green-800' :
                                   ($complaint->package->status === 'IN_TRANSIT' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $complaint->package->status }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Montant COD</label>
                        <div class="mt-1 text-sm font-semibold text-purple-600">
                            {{ number_format($complaint->package->cod_amount ?? 0, 3) }} DT
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Destinataire</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $complaint->package->receiver_name }}</div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('commercial.packages.show', $complaint->package) }}"
                       class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        → Voir les détails complets du colis
                    </a>
                </div>
            </div>
            @endif

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
                                <span class="font-medium">Réclamation créée</span> par {{ $complaint->client->name }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $complaint->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>

                    @if($complaint->assigned_to)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-2 h-2 bg-yellow-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Assignée</span> à {{ $complaint->assignedTo->name }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $complaint->assigned_at ? $complaint->assigned_at->format('d/m/Y à H:i') : 'Date non spécifiée' }}</div>
                        </div>
                    </div>
                    @endif

                    @if($complaint->resolved_at)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Réclamation {{ $complaint->status === 'RESOLVED' ? 'résolue' : 'traitée' }}</span>
                                @if($complaint->resolvedBy)
                                par {{ $complaint->resolvedBy->name }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $complaint->resolved_at->format('d/m/Y à H:i') }}</div>
                            @if($complaint->resolution_notes)
                            <div class="text-xs text-gray-600 mt-1">{{ $complaint->resolution_notes }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne de droite -->
        <div class="space-y-6">

            <!-- Informations du Client -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Client</h3>

                <div class="space-y-3">
                    <div>
                        <div class="font-medium text-gray-900">{{ $complaint->client->name }}</div>
                        <div class="text-sm text-gray-600">{{ $complaint->client->email }}</div>
                        <div class="text-sm text-gray-600">{{ $complaint->client->phone }}</div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <a href="{{ route('commercial.clients.show', $complaint->client) }}"
                           class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            → Voir le profil complet
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions Rapides -->
            @if($complaint->status === 'PENDING')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button onclick="resolveComplaint()"
                            class="w-full px-4 py-2 bg-green-300 text-green-800 rounded-lg hover:bg-green-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Résoudre
                    </button>

                    <button onclick="rejectComplaint()"
                            class="w-full px-4 py-2 bg-red-300 text-red-800 rounded-lg hover:bg-red-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rejeter
                    </button>

                    @if($complaint->type === 'COD_MODIFICATION' && $complaint->package)
                    <button onclick="modifyCod()"
                            class="w-full px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier COD
                    </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Récapitulatif -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Récapitulatif</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-purple-700">Type:</span>
                        <span class="text-purple-900">
                            {{ $complaint->type === 'COD_MODIFICATION' ? 'Modification COD' :
                               ($complaint->type === 'DELIVERY_DELAY' ? 'Retard' : 'Autre') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Statut:</span>
                        <span class="font-semibold text-purple-900">
                            {{ $complaint->status === 'PENDING' ? 'En attente' :
                               ($complaint->status === 'RESOLVED' ? 'Résolue' : 'Rejetée') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Priorité:</span>
                        <span class="text-purple-900">
                            {{ $complaint->priority === 'HIGH' ? 'Élevée' :
                               ($complaint->priority === 'MEDIUM' ? 'Moyenne' : 'Normale') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-purple-700">Créée le:</span>
                        <span class="text-purple-900">{{ $complaint->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function complaintShowApp() {
    return {
        complaint: @json($complaint),

        init() {
            // Initialisation si nécessaire
        }
    }
}

function resolveComplaint() {
    const notes = prompt('Notes de résolution (optionnel):');
    if (notes !== null) {
        fetch(`/commercial/complaints/${window.complaintShowApp().complaint.id}/resolve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ notes: notes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Réclamation résolue avec succès', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur lors de la résolution', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    }
}

function rejectComplaint() {
    const reason = prompt('Raison du rejet (optionnel):');
    if (reason !== null) {
        fetch(`/commercial/complaints/${window.complaintShowApp().complaint.id}/reject`, {
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
                showToast('Réclamation rejetée', 'success');
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

function modifyCod() {
    const complaint = window.complaintShowApp().complaint;
    const newAmount = prompt('Nouveau montant COD (DT):', complaint.details?.new_cod_amount || '');

    if (newAmount !== null && !isNaN(parseFloat(newAmount))) {
        fetch(`/commercial/complaints/packages/${complaint.package.id}/modify-cod`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                new_cod_amount: parseFloat(newAmount),
                complaint_id: complaint.id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('COD modifié avec succès', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Erreur lors de la modification', 'error');
            }
        })
        .catch(error => {
            showToast('Erreur de connexion', 'error');
        });
    }
}
</script>
@endpush