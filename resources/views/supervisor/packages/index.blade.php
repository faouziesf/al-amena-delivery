@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Gestion des Colis
                </h1>
                <p class="text-gray-600 mt-2">Supervision complète de tous les colis du système</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="generateRunSheet()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all flex items-center shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Feuille de Route
                </button>
                <a href="#" class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all flex items-center shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Rapport
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Total Colis</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_packages'] }}</p>
                    <div class="flex items-center mt-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                        <p class="text-sm text-gray-500">Tous statuts</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Livrés</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['delivered_packages'] }}</p>
                    <div class="flex items-center mt-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <p class="text-sm text-gray-500">Succès</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">En Transit</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['picked_up_packages'] }}</p>
                    <div class="flex items-center mt-2">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                        <p class="text-sm text-gray-500">En cours</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Problèmes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['returned_packages'] + $stats['cancelled_packages'] }}</p>
                    <div class="flex items-center mt-2">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                        <p class="text-sm text-gray-500">Nécessitent attention</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-xl p-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtres de Recherche
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Code colis, destinataire..."
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
                        <option value="">Tous les statuts</option>
                        <option value="CREATED" {{ request('status') == 'CREATED' ? 'selected' : '' }}>Créé</option>
                        <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                        <option value="OUT_FOR_DELIVERY" {{ request('status') == 'OUT_FOR_DELIVERY' ? 'selected' : '' }}>En livraison</option>
                        <option value="PICKED_UP" {{ request('status') == 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                        <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                        <option value="RETURNED" {{ request('status') == 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de Livraison</label>
                    <select name="delivery_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
                        <option value="">Tous les types</option>
                        <option value="fast" {{ request('delivery_type') == 'fast' ? 'selected' : '' }}>Rapide</option>
                        <option value="advanced" {{ request('delivery_type') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                    </select>
                </div>
                @if(isset($clients) && $clients->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                    <select name="client_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de Création</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions groupées -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" x-data="{ selectedPackages: [] }">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Actions Groupées
                </span>
                <div class="flex items-center space-x-3">
                    <button @click="bulkUpdateStatus('AVAILABLE')" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Rendre Disponible
                    </button>
                    <button @click="bulkUpdateStatus('CANCELLED')" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition-all text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Annuler
                    </button>
                    <button @click="bulkAssignDeliverer()" class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Assigner Livreur
                    </button>
                </div>
            </div>
            <div class="flex items-center">
                <span x-text="selectedPackages.length + ' colis sélectionné(s)'" class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full"></span>
            </div>
        </div>
    </div>

    <!-- Tableau des colis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Liste des Colis
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Colis</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Destinataire</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">COD</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Livreur</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($packages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" value="{{ $package->id }}" class="rounded border-gray-300">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $package->tracking_number }}</div>
                            <div class="text-sm text-gray-500">{{ $package->delivery_type === 'fast' ? 'Rapide' : 'Avancé' }}</div>
                            <div class="text-xs text-gray-400">{{ $package->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $package->client->name }}</div>
                            <div class="text-sm text-gray-500">{{ $package->client->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $package->recipient_name }}</div>
                            <div class="text-sm text-gray-500">{{ $package->recipient_phone }}</div>
                            <div class="text-xs text-gray-400">{{ Str::limit($package->recipient_address, 30) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($package->status === 'CREATED') bg-gray-100 text-gray-800
                                @elseif($package->status === 'AVAILABLE') bg-blue-100 text-blue-800
                                @elseif($package->status === 'OUT_FOR_DELIVERY') bg-yellow-100 text-yellow-800
                                @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                                @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                                @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $package->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($package->cod_amount, 3) }} TND
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($package->deliverer)
                            <div class="text-sm font-medium text-gray-900">{{ $package->deliverer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $package->deliverer->phone }}</div>
                            @else
                            <span class="text-sm text-gray-400">Non assigné</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('supervisor.packages.show', $package) }}"
                                   class="text-red-600 hover:text-red-900">Voir</a>

                                @if(!$package->deliverer_id)
                                <button onclick="showAssignModal({{ $package->id }})"
                                        class="text-blue-600 hover:text-blue-900">Assigner</button>
                                @endif

                                <button onclick="showStatusModal({{ $package->id }}, '{{ $package->status }}')"
                                        class="text-green-600 hover:text-green-900">Statut</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-lg font-medium">Aucun colis trouvé</p>
                                <p class="text-sm text-gray-400 mt-1">Essayez de modifier vos filtres</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="bg-white px-6 py-3 border-t border-gray-200">
            {{ $packages->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modals -->
<!-- Modal assignation livreur -->
<div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Assigner un Livreur</h3>
            <form id="assignForm">
                <div class="mb-4">
                    <select id="delivererSelect" class="w-full border-gray-300 rounded-lg">
                        <option value="">Sélectionner un livreur</option>
                        @foreach($deliverers as $deliverer)
                        <option value="{{ $deliverer->id }}">{{ $deliverer->name }} - {{ $deliverer->phone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('assignModal')" class="px-4 py-2 text-gray-600 border rounded-lg">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentPackageId = null;

function showAssignModal(packageId) {
    currentPackageId = packageId;
    document.getElementById('assignModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function generateRunSheet() {
    const selectedPackages = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value).filter(v => v);
    if (selectedPackages.length === 0) {
        alert('Veuillez sélectionner au moins un colis');
        return;
    }
    alert('Génération de la feuille de route pour ' + selectedPackages.length + ' colis...');
}
</script>
@endsection