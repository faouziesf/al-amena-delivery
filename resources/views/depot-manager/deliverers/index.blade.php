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
                                    <div class="text-sm">
                                        <div class="flex flex-wrap gap-1 mb-1">
                                            @foreach($deliverer->getDelivererGouvernorats() as $gov)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $gov }}
                                            </span>
                                            @endforeach
                                        </div>
                                        <p class="text-gray-500 text-xs">
                                            @if($deliverer->deliverer_type === 'DELEGATION')
                                                Délégation fixe
                                            @elseif($deliverer->deliverer_type === 'JOKER')
                                                Joker (toutes délégations)
                                            @elseif($deliverer->deliverer_type === 'TRANSIT')
                                                Transit uniquement
                                            @else
                                                {{ $deliverer->deliverer_type }}
                                            @endif
                                        </p>
                                    </div>
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

                                        <!-- Vider Wallet -->
                                        @if(($deliverer->wallet->balance ?? 0) > 0)
                                        <button onclick="emptyDelivererWallet({{ $deliverer->id }}, '{{ $deliverer->name }}', {{ $deliverer->wallet->balance }})"
                                                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition-colors"
                                                title="Vider le wallet (ajouter à ma caisse)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </button>
                                        @else
                                        <span class="text-gray-400 p-1" title="Wallet vide">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </span>
                                        @endif

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

function emptyDelivererWallet(delivererId, delivererName, currentBalance) {
    if (currentBalance <= 0) {
        alert('Le wallet du livreur est déjà vide.');
        return;
    }

    const notes = prompt(`Vider le wallet de ${delivererName} (${currentBalance.toFixed(3)} DT)\n\nNotes (optionnel):`);
    
    if (notes === null) return; // User cancelled

    if (confirm(`Confirmer le vidage de ${currentBalance.toFixed(3)} DT du wallet de ${delivererName} vers votre caisse ?`)) {
        fetch(`/depot-manager/deliverers/${delivererId}/wallet/empty`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: currentBalance,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du vidage du wallet');
        });
    }
}
</script>

@endsection