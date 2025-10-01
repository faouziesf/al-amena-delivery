@extends('layouts.depot-manager')

@section('title', 'Gestion des Livreurs')
@section('page-title', 'Gestion des Livreurs')
@section('page-description', 'Livreurs de vos gouvernorats assignés')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Livreurs</h1>
            <p class="text-gray-600 mt-1">{{ $deliverers->total() }} livreurs dans vos gouvernorats</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('depot-manager.deliverers.create') }}"
               class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau Livreur
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gouvernorat</label>
                <select name="gouvernorat" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les gouvernorats</option>
                    @foreach(auth()->user()->assigned_gouvernorats_array as $gov)
                        <option value="{{ $gov }}" {{ request('gouvernorat') == $gov ? 'selected' : '' }}>
                            {{ $gov }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les statuts</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                    <option value="SUSPENDED" {{ request('status') == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>En attente</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nom, téléphone, email..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des livreurs -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
        <div class="p-6">
            @if($deliverers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Livreur</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Contact</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Localisation</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Performance</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Wallet</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Statut</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($deliverers as $deliverer)
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <span class="text-orange-600 font-semibold text-sm">
                                                {{ substr($deliverer->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $deliverer->name }}</p>
                                            <p class="text-sm text-gray-500">ID: {{ $deliverer->id }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <p class="text-sm text-gray-900">{{ $deliverer->phone }}</p>
                                    <p class="text-sm text-gray-500">{{ $deliverer->email }}</p>
                                </td>

                                <td class="py-4 px-4">
                                    <p class="text-sm text-gray-900">{{ $deliverer->assigned_delegation ?? 'Non assigné' }}</p>
                                    <p class="text-sm text-gray-500">{{ $deliverer->address ?? 'Adresse non renseignée' }}</p>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div>
                                            <p class="font-semibold text-blue-600">{{ $deliverer->assignedPackages->count() }}</p>
                                            <p class="text-gray-500">En cours</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-green-600">0</p>
                                            <p class="text-gray-500">Livrés</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-purple-600">0</p>
                                            <p class="text-gray-500">COD</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <div class="text-xs">
                                        <div>
                                            <p class="font-semibold text-blue-600">{{ number_format($deliverer->wallet->balance ?? 0, 3) }} DT</p>
                                            <p class="text-gray-500">Solde</p>
                                        </div>
                                        @if(($deliverer->wallet->advance_balance ?? 0) > 0)
                                        <div class="mt-1">
                                            <p class="font-semibold text-emerald-600">{{ number_format($deliverer->wallet->advance_balance, 3) }} DT</p>
                                            <p class="text-gray-500">Avances</p>
                                        </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($deliverer->account_status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($deliverer->account_status === 'SUSPENDED') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $deliverer->account_status }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('depot-manager.deliverers.show', $deliverer) }}"
                                           class="text-orange-600 hover:text-orange-800 transition-colors"
                                           title="Voir détails">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <!-- Wallet Management -->
                                        <button onclick="manageDelivererWallet({{ $deliverer->id }}, '{{ $deliverer->name }}', {{ $deliverer->wallet->balance ?? 0 }}, {{ $deliverer->wallet->advance_balance ?? 0 }})"
                                                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                title="Gérer wallet et avances">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </button>

                                        <a href="{{ route('depot-manager.deliverers.edit', $deliverer) }}"
                                           class="text-blue-600 hover:text-blue-800 transition-colors"
                                           title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <button onclick="toggleDelivererStatus({{ $deliverer->id }})"
                                                class="text-gray-600 hover:text-gray-800 transition-colors"
                                                title="Changer statut">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $deliverers->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 mb-4">Aucun livreur trouvé</p>
                    <a href="{{ route('depot-manager.deliverers.create') }}"
                       class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                        Ajouter le premier livreur
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Wallet Management Modal -->
<div id="deliverer-wallet-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900" id="deliverer-wallet-title">Gérer Wallet & Avances</h3>
                <button onclick="closeDelivererWalletModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Deliverer Info Section -->
                <div class="mb-6 p-4 bg-gradient-to-r from-orange-50 to-green-50 rounded-lg border">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-orange-500 to-green-500 flex items-center justify-center">
                            <span class="text-white font-bold text-lg" id="deliverer-wallet-initial">L</span>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900" id="deliverer-wallet-name">Livreur</div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <div class="text-xs text-gray-600">Solde Principal</div>
                                    <div class="text-lg font-bold text-blue-600" id="deliverer-current-balance">0.000 DT</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-600">Avances</div>
                                    <div class="text-lg font-bold text-green-600" id="deliverer-current-advance-balance">0.000 DT</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="deliverer-wallet-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de Transaction</label>
                        <select id="deliverer-wallet-action" onchange="updateDelivererWalletActionUI()" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-orange-500 focus:border-orange-500">
                            <optgroup label="💰 Gestion du Solde Principal">
                                <option value="add">Ajouter des fonds au solde</option>
                                <option value="deduct">Déduire des fonds du solde</option>
                            </optgroup>
                            <optgroup label="💎 Gestion des Avances">
                                <option value="add_advance">Ajouter une avance</option>
                                <option value="remove_advance">Retirer une avance</option>
                            </optgroup>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant (DT)</label>
                        <input type="number" id="deliverer-wallet-amount" step="0.001" min="0.001" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-orange-500 focus:border-orange-500">
                        <div class="text-xs text-gray-500 mt-1" id="deliverer-wallet-amount-help">
                            Montant entre 0.001 DT et 10000 DT
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="deliverer-wallet-description" rows="3" required
                                  placeholder="Motif de l'opération..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" id="deliverer-wallet-submit-btn"
                                class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 transition-colors">
                            Confirmer l'Opération
                        </button>
                        <button type="button" onclick="closeDelivererWalletModal()"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDelivererStatus(delivererId) {
    if (confirm('Voulez-vous changer le statut de ce livreur ?')) {
        fetch(`/depot-manager/deliverers/${delivererId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du changement de statut');
        });
    }
}

// Global variables
let currentDelivererId = null;

// Deliverer Wallet Management Functions
function manageDelivererWallet(delivererId, delivererName, currentBalance, currentAdvanceBalance = 0) {
    currentDelivererId = delivererId;
    document.getElementById('deliverer-wallet-title').textContent = `Wallet & Avances - ${delivererName}`;
    document.getElementById('deliverer-wallet-name').textContent = delivererName;
    document.getElementById('deliverer-wallet-initial').textContent = delivererName.charAt(0).toUpperCase();
    document.getElementById('deliverer-current-balance').textContent = `${parseFloat(currentBalance).toFixed(3)} DT`;
    document.getElementById('deliverer-current-advance-balance').textContent = `${parseFloat(currentAdvanceBalance).toFixed(3)} DT`;

    // Reset form
    document.getElementById('deliverer-wallet-action').value = 'add';
    updateDelivererWalletActionUI();

    document.getElementById('deliverer-wallet-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDelivererWalletModal() {
    document.getElementById('deliverer-wallet-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('deliverer-wallet-form').reset();
    currentDelivererId = null;
}

function updateDelivererWalletActionUI() {
    const action = document.getElementById('deliverer-wallet-action').value;
    const submitBtn = document.getElementById('deliverer-wallet-submit-btn');
    const amountHelp = document.getElementById('deliverer-wallet-amount-help');
    const amountInput = document.getElementById('deliverer-wallet-amount');

    switch(action) {
        case 'add':
            submitBtn.textContent = 'Ajouter des Fonds';
            submitBtn.className = 'flex-1 bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 transition-colors';
            amountHelp.textContent = 'Montant entre 0.001 DT et 10000 DT';
            amountInput.setAttribute('max', '10000');
            break;
        case 'deduct':
            submitBtn.textContent = 'Déduire des Fonds';
            submitBtn.className = 'flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors';
            const currentBalance = parseFloat(document.getElementById('deliverer-current-balance').textContent);
            amountHelp.textContent = `Maximum disponible: ${currentBalance.toFixed(3)} DT`;
            amountInput.setAttribute('max', currentBalance);
            break;
        case 'add_advance':
            submitBtn.textContent = 'Ajouter une Avance';
            submitBtn.className = 'flex-1 bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700 transition-colors';
            amountHelp.textContent = 'Montant entre 0.001 DT et 1000 DT (pour avances)';
            amountInput.setAttribute('max', '1000');
            break;
        case 'remove_advance':
            submitBtn.textContent = 'Retirer une Avance';
            submitBtn.className = 'flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors';
            const currentAdvanceBalance = parseFloat(document.getElementById('deliverer-current-advance-balance').textContent);
            amountHelp.textContent = `Maximum disponible: ${currentAdvanceBalance.toFixed(3)} DT`;
            amountInput.setAttribute('max', currentAdvanceBalance);
            break;
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const delivererWalletForm = document.getElementById('deliverer-wallet-form');
    if (delivererWalletForm) {
        delivererWalletForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!currentDelivererId) return;

            const action = document.getElementById('deliverer-wallet-action').value;
            const amount = parseFloat(document.getElementById('deliverer-wallet-amount').value);
            const description = document.getElementById('deliverer-wallet-description').value;

            if (!amount || !description) {
                alert('Tous les champs sont requis');
                return;
            }

            try {
                let endpoint, url;

                if (action === 'add' || action === 'deduct') {
                    // Deliverer wallet operations
                    endpoint = action === 'add' ? 'add' : 'deduct';
                    url = `/depot-manager/deliverers/${currentDelivererId}/wallet/${endpoint}`;
                } else {
                    // Advance operations
                    endpoint = action === 'add_advance' ? 'add' : 'remove';
                    url = `/depot-manager/deliverers/${currentDelivererId}/advance/${endpoint}`;
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ amount, description })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert(data.message || 'Opération réalisée avec succès');
                    closeDelivererWalletModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.message || 'Erreur lors de l\'opération');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
            }
        });
    }
});
</script>

@endsection