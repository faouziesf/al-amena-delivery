@extends('layouts.commercial')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête avec informations du colis -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Détails du Colis #{{ $package->tracking_number }}</h1>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @if($package->status === 'CREATED') bg-gray-100 text-gray-800
                    @elseif($package->status === 'AVAILABLE') bg-blue-100 text-blue-800
                    @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                    @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                    @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                    @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                    @elseif($package->status === 'CANCELLED') bg-gray-100 text-gray-800
                    @else bg-purple-100 text-purple-800
                    @endif">
                    {{ $package->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Informations de base -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Informations de base</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Numéro de suivi</label>
                    <p class="text-gray-900 font-mono">{{ $package->tracking_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Type de livraison</label>
                    <p class="text-gray-900">{{ $package->delivery_type === 'fast' ? 'Livraison Rapide' : 'Livraison Avancée' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Montant COD</label>
                    <p class="text-gray-900 font-semibold">{{ number_format($package->cod_amount, 3) }} TND</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Date de création</label>
                    <p class="text-gray-900">{{ $package->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Informations client -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Client</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Nom complet</label>
                    <p class="text-gray-900">{{ $package->client->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Email</label>
                    <p class="text-gray-900">{{ $package->client->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                    <p class="text-gray-900">{{ $package->client->phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Balance portefeuille</label>
                    <p class="text-gray-900 font-semibold">{{ number_format($package->client->wallet->balance ?? 0, 3) }} TND</p>
                </div>
            </div>

            <!-- Destinataire -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-700 border-b pb-2">Destinataire</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Nom</label>
                    <p class="text-gray-900">{{ $package->recipient_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                    <p class="text-gray-900">{{ $package->recipient_phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Adresse</label>
                    <p class="text-gray-900">{{ $package->recipient_address }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Ville</label>
                    <p class="text-gray-900">{{ $package->recipient_city }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de livraison -->
    @if($package->deliverer || $package->status !== 'CREATED')
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Informations de livraison</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($package->deliverer)
            <div>
                <label class="block text-sm font-medium text-gray-600">Livreur assigné</label>
                <p class="text-gray-900">{{ $package->deliverer->name }}</p>
                <p class="text-sm text-gray-600">{{ $package->deliverer->phone }}</p>
            </div>
            @endif
            @if($package->pickup_date)
            <div>
                <label class="block text-sm font-medium text-gray-600">Date de collecte</label>
                <p class="text-gray-900">{{ \Carbon\Carbon::parse($package->pickup_date)->format('d/m/Y H:i') }}</p>
            </div>
            @endif
            @if($package->delivery_date)
            <div>
                <label class="block text-sm font-medium text-gray-600">Date de livraison</label>
                <p class="text-gray-900">{{ \Carbon\Carbon::parse($package->delivery_date)->format('d/m/Y H:i') }}</p>
            </div>
            @endif
            @if($package->notes)
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600">Notes</label>
                <p class="text-gray-900">{{ $package->notes }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Actions commerciales -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Actions commerciales</h3>
        <div class="flex flex-wrap gap-3">
            @if($package->status === 'CREATED')
                <button type="button"
                        onclick="assignDeliverer({{ $package->id }})"
                        class="px-4 py-2 bg-purple-200 text-purple-800 rounded-lg hover:bg-purple-300 transition-colors">
                    Assigner un livreur
                </button>
            @endif

            @if(in_array($package->status, ['DELIVERED', 'RETURNED']))
                <button type="button"
                        onclick="markAsPaid({{ $package->id }})"
                        class="px-4 py-2 bg-green-200 text-green-800 rounded-lg hover:bg-green-300 transition-colors">
                    Marquer comme payé
                </button>
            @endif

            @if($package->status !== 'CANCELLED' && $package->status !== 'DELIVERED')
                <button type="button"
                        onclick="cancelPackage({{ $package->id }})"
                        class="px-4 py-2 bg-red-200 text-red-800 rounded-lg hover:bg-red-300 transition-colors">
                    Annuler le colis
                </button>
            @endif

            <button type="button"
                    onclick="editCodAmount({{ $package->id }}, {{ $package->cod_amount }})"
                    class="px-4 py-2 bg-yellow-200 text-yellow-800 rounded-lg hover:bg-yellow-300 transition-colors">
                Modifier le montant COD
            </button>

            <a href="{{ route('commercial.packages.index') }}"
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                Retour à la liste
            </a>
        </div>
    </div>

    <!-- Historique des transactions -->
    @if($package->transactions && $package->transactions->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Historique des transactions</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($package->transactions as $transaction)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($transaction->type === 'COD_PAYMENT') bg-green-100 text-green-800
                                @elseif($transaction->type === 'DELIVERY_FEE') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $transaction->type }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm font-semibold text-gray-900">{{ number_format($transaction->amount, 3) }} TND</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $transaction->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Réclamations liées -->
    @if($package->complaints && $package->complaints->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Réclamations liées</h3>
        <div class="space-y-3">
            @foreach($package->complaints as $complaint)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-gray-900">Réclamation #{{ $complaint->id }}</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($complaint->status === 'PENDING') bg-yellow-100 text-yellow-800
                        @elseif($complaint->status === 'RESOLVED') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ $complaint->status }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-2">{{ $complaint->description }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                    <a href="{{ route('commercial.complaints.show', $complaint->id) }}"
                       class="text-purple-600 hover:text-purple-800">
                        Voir détails
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Modales -->
<!-- Modal pour assigner un livreur -->
<div id="assignDelivererModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Assigner un livreur</h3>
            <form id="assignDelivererForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner un livreur</label>
                    <select id="delivererId" class="w-full border-gray-300 rounded-lg">
                        <option value="">Choisir un livreur...</option>
                        @foreach(\App\Models\User::where('role', 'DELIVERER')->where('status', 'ACTIVE')->get() as $deliverer)
                        <option value="{{ $deliverer->id }}">{{ $deliverer->name }} - {{ $deliverer->phone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('assignDelivererModal')"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-purple-200 text-purple-800 rounded-lg hover:bg-purple-300">
                        Assigner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier le montant COD -->
<div id="editCodModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Modifier le montant COD</h3>
            <form id="editCodForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau montant COD</label>
                    <input type="number" step="0.001" id="newCodAmount"
                           class="w-full border-gray-300 rounded-lg" placeholder="0.000">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison de la modification</label>
                    <textarea id="codChangeReason" rows="3"
                              class="w-full border-gray-300 rounded-lg"
                              placeholder="Expliquez la raison de cette modification..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editCodModal')"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-200 text-yellow-800 rounded-lg hover:bg-yellow-300">
                        Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentPackageId = null;

function assignDeliverer(packageId) {
    currentPackageId = packageId;
    document.getElementById('assignDelivererModal').classList.remove('hidden');
}

function editCodAmount(packageId, currentAmount) {
    currentPackageId = packageId;
    document.getElementById('newCodAmount').value = currentAmount;
    document.getElementById('editCodModal').classList.remove('hidden');
}

function markAsPaid(packageId) {
    if (confirm('Êtes-vous sûr de vouloir marquer ce colis comme payé ?')) {
        fetch(`/commercial/packages/${packageId}/mark-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function cancelPackage(packageId) {
    if (confirm('Êtes-vous sûr de vouloir annuler ce colis ?')) {
        fetch(`/commercial/packages/${packageId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Gestion du formulaire d'assignation de livreur
document.getElementById('assignDelivererForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const delivererId = document.getElementById('delivererId').value;

    if (!delivererId) {
        alert('Veuillez sélectionner un livreur');
        return;
    }

    fetch(`/commercial/packages/${currentPackageId}/assign-deliverer`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            deliverer_id: delivererId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
});

// Gestion du formulaire de modification COD
document.getElementById('editCodForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const newAmount = document.getElementById('newCodAmount').value;
    const reason = document.getElementById('codChangeReason').value;

    if (!newAmount || !reason) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    fetch(`/commercial/packages/${currentPackageId}/update-cod`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cod_amount: parseFloat(newAmount),
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
});

// Fermer les modales en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const modals = ['assignDelivererModal', 'editCodModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (e.target === modal) {
            closeModal(modalId);
        }
    });
});
</script>
@endsection