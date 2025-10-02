@extends('layouts.depot-manager')

@section('title', 'Colis de Paiement')
@section('page-title', 'Colis de Paiement')
@section('page-description', 'Gestion des colis de paiement créés')

@section('content')
<div class="space-y-6">
    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Disponibles</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['available'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">En cours</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Livrés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['delivered'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Tous les statuts</option>
                        <option value="AVAILABLE" {{ request('status') === 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                        <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Accepté</option>
                        <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>Récupéré</option>
                        <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                        <option value="RETURNED" {{ request('status') === 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Code colis ou demande..."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Du</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrer
                    </button>
                    <button type="button" onclick="printSelected()" id="printSelectedBtn" disabled
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Imprimer Sélection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table des colis -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code Colis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Demande Paiement
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Client
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Montant COD
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Livreur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Créé le
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($packages as $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="package-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500"
                                       value="{{ $package->id }}" onchange="updatePrintButton()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-bold text-gray-900">{{ $package->package_code }}</div>
                                    <div class="ml-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            💰 Paiement
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $package->withdrawalRequest->request_code ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $package->content_description }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $package->withdrawalRequest->client->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $package->withdrawalRequest->client->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-lg font-bold text-green-600">{{ number_format($package->cod_amount, 3) }} DT</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'AVAILABLE' => 'bg-yellow-100 text-yellow-800',
                                        'ACCEPTED' => 'bg-blue-100 text-blue-800',
                                        'PICKED_UP' => 'bg-orange-100 text-orange-800',
                                        'DELIVERED' => 'bg-green-100 text-green-800',
                                        'RETURNED' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$package->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $package->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($package->assignedDeliverer)
                                    <div class="text-sm text-gray-900">{{ $package->assignedDeliverer->name }}</div>
                                @else
                                    <span class="text-sm text-gray-400">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $package->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('depot-manager.packages.delivery-receipt', $package) }}" target="_blank"
                                       class="text-blue-600 hover:text-blue-900 text-xs" title="Imprimer bon de livraison">
                                        🖨️
                                    </a>
                                    <a href="{{ route('depot-manager.packages.show', $package) }}"
                                       class="text-green-600 hover:text-green-900 text-xs">
                                        Détails
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4.5M20 7v10l-8 4M4 7v10l8 4m0-10L4 7"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun colis de paiement</h3>
                                <p class="text-gray-500">Il n'y a pas encore de colis de paiement créés ou correspondant aux filtres.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($packages->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $packages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.package-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updatePrintButton();
}

function updatePrintButton() {
    const checkboxes = document.querySelectorAll('.package-checkbox:checked');
    const printBtn = document.getElementById('printSelectedBtn');

    if (checkboxes.length > 0) {
        printBtn.disabled = false;
        printBtn.textContent = `Imprimer Sélection (${checkboxes.length})`;
    } else {
        printBtn.disabled = true;
        printBtn.innerHTML = `
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer Sélection
        `;
    }

    // Mettre à jour le checkbox "Tout sélectionner"
    const allCheckboxes = document.querySelectorAll('.package-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.package-checkbox:checked');
    const selectAll = document.getElementById('selectAll');

    if (checkedCheckboxes.length === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (checkedCheckboxes.length === allCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
        selectAll.checked = false;
    }
}

function printSelected() {
    const checkboxes = document.querySelectorAll('.package-checkbox:checked');

    if (checkboxes.length === 0) {
        alert('Veuillez sélectionner au moins un colis');
        return;
    }

    const packageIds = Array.from(checkboxes).map(cb => cb.value);

    // Construire l'URL pour l'impression en masse
    const params = new URLSearchParams();
    packageIds.forEach(id => params.append('packages[]', id));

    const printUrl = `/depot-manager/packages/bulk-delivery-receipts?${params.toString()}`;

    // Ouvrir dans un nouvel onglet pour impression
    window.open(printUrl, '_blank');
}

// Initialiser l'état du bouton au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updatePrintButton();
});
</script>

@endsection