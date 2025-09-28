@extends('layouts.deliverer')

@section('title', 'Réassignation de livreurs')

@section('content')
<div class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Réassignation de livreurs
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    @if(Auth::user()->canReassignPackages())
                        Changez les livreurs assignés aux colis en cours de livraison
                    @else
                        Votre type de compte ne permet pas de changer les livreurs.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(isset($error))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Accès non autorisé
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        {{ $error }}
                        @if(isset($deliverer_type))
                            <br>Votre type de compte : {{ $deliverer_type }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Filtres -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtres</h3>
            </div>
            <div class="px-6 py-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">
                            Recherche
                        </label>
                        <input type="text"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Code colis, livreur..."
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="delegation_to" class="block text-sm font-medium text-gray-700">
                            Délégation
                        </label>
                        <select id="delegation_to"
                                name="delegation_to"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Toutes les délégations</option>
                            @if(isset($delegations))
                                @foreach($delegations as $key => $name)
                                    <option value="{{ $key }}" {{ request('delegation_to') == $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Statut
                        </label>
                        <select id="status"
                                name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Tous les statuts</option>
                            <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>Accepté</option>
                            <option value="PICKED_UP" {{ request('status') == 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                            <option value="OUT_FOR_DELIVERY" {{ request('status') == 'OUT_FOR_DELIVERY' ? 'selected' : '' }}>En livraison</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des colis -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    Colis à réassigner ({{ $packages->total() }})
                </h3>
            </div>

            @if($packages->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Colis
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Livreur actuel
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Délégation
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nouveau livreur
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($packages as $package)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $package->package_code }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $package->sender ? $package->sender->name : 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $package->assignedDeliverer ? $package->assignedDeliverer->name : 'Non assigné' }}
                                        </div>
                                        @if($package->assignedDeliverer)
                                            <div class="text-sm text-gray-500">
                                                {{ $package->assignedDeliverer->deliverer_type }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $package->delegation_to }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                                            @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                                            @elseif($package->status === 'OUT_FOR_DELIVERY') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $package->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form id="reassign-form-{{ $package->id }}"
                                              method="POST"
                                              action="{{ route('deliverer.reassignments.reassign', $package) }}"
                                              class="inline-block">
                                            @csrf
                                            <select name="new_deliverer_id"
                                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                                    required>
                                                <option value="">Sélectionner...</option>
                                                @if(isset($availableDeliverers))
                                                    @foreach($availableDeliverers as $deliverer)
                                                        <!-- Filtrer selon la délégation du colis -->
                                                        @if($deliverer->deliverer_type === 'JOKER' ||
                                                            ($deliverer->deliverer_type === 'DELEGATION' && $deliverer->assigned_delegation === $package->delegation_to))
                                                            <option value="{{ $deliverer->id }}">
                                                                {{ $deliverer->name }}
                                                                ({{ $deliverer->deliverer_type === 'JOKER' ? 'Joker' : $deliverer->assigned_delegation }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button type="button"
                                                    onclick="showReasonModal({{ $package->id }})"
                                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                Réassigner
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $packages->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m8-8V4a1 1 0 011-1h2a1 1 0 011 1v1M4 13v-1a1 1 0 011-1h2a1 1 0 011 1v1" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun colis à réassigner</h3>
                    <p class="mt-1 text-sm text-gray-500">Tous les colis sont correctement assignés.</p>
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Modal pour la raison de réassignation -->
<div id="reason-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div class="mt-5 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Raison de la réassignation</h3>
                <div class="mt-4">
                    <textarea id="reason-textarea"
                              placeholder="Expliquez pourquoi vous réassignez ce colis..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              rows="4"></textarea>
                </div>
                <div class="mt-5 flex justify-center space-x-3">
                    <button id="cancel-reassign"
                            type="button"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Annuler
                    </button>
                    <button id="confirm-reassign"
                            type="button"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPackageId = null;

function showReasonModal(packageId) {
    const form = document.getElementById(`reassign-form-${packageId}`);
    const select = form.querySelector('select[name="new_deliverer_id"]');

    if (!select.value) {
        alert('Veuillez d\'abord sélectionner un nouveau livreur.');
        return;
    }

    currentPackageId = packageId;
    document.getElementById('reason-modal').classList.remove('hidden');
    document.getElementById('reason-textarea').value = '';
    document.getElementById('reason-textarea').focus();
}

document.getElementById('cancel-reassign').addEventListener('click', function() {
    document.getElementById('reason-modal').classList.add('hidden');
    currentPackageId = null;
});

document.getElementById('confirm-reassign').addEventListener('click', function() {
    if (currentPackageId) {
        const form = document.getElementById(`reassign-form-${currentPackageId}`);
        const reason = document.getElementById('reason-textarea').value;

        // Ajouter la raison au formulaire
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);

        // Soumettre le formulaire
        form.submit();
    }
});

// Fermer le modal en cliquant à l'extérieur
document.getElementById('reason-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
        currentPackageId = null;
    }
});
</script>
@endpush
@endsection