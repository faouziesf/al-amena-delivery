@extends('layouts.depot-manager')

@section('title', 'Gestion des Colis')
@section('page-title', 'Gestion des Colis')
@section('page-description', 'Suivi et gestion des colis dans vos gouvernorats')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Colis</h1>
            <p class="text-gray-600 mt-1">{{ $packages->total() }} colis dans vos gouvernorats</p>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Colis</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">En Cours</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Livrés</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['delivered'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Urgents</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['urgent'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les statuts</option>
                    <option value="PAYMENT" {{ request('status') == 'PAYMENT' ? 'selected' : '' }}>💰 Colis de Paiement</option>
                    <option value="CREATED" {{ request('status') == 'CREATED' ? 'selected' : '' }}>Créé</option>
                    <option value="AVAILABLE" {{ request('status') == 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                    <option value="ACCEPTED" {{ request('status') == 'ACCEPTED' ? 'selected' : '' }}>Accepté</option>
                    <option value="PICKED_UP" {{ request('status') == 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                    <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                    <option value="RETURNED" {{ request('status') == 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                </select>
            </div>

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
                <label class="block text-sm font-medium text-gray-700 mb-2">Livreur</label>
                <select name="deliverer" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Tous les livreurs</option>
                    @foreach($deliverers as $deliverer)
                        <option value="{{ $deliverer->id }}" {{ request('deliverer') == $deliverer->id ? 'selected' : '' }}>
                            {{ $deliverer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Code colis, client..."
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

    <!-- Liste des colis -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
        <div class="p-6">
            @if($packages->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Colis</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Client/Destinataire</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Livreur</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">COD</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Statut</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($packages as $package)
                            <tr class="hover:bg-gray-50 {{ $package->payment_withdrawal_id ? 'bg-green-25' : '' }}">
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 {{ $package->payment_withdrawal_id ? 'bg-green-100' : 'bg-orange-100' }} rounded-lg flex items-center justify-center relative">
                                            @if($package->payment_withdrawal_id)
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            @endif
                                            @if($package->payment_withdrawal_id)
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs">💰</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">#{{ $package->package_code }}</p>
                                            <p class="text-sm text-gray-500">{{ $package->created_at ? $package->created_at->format('d/m/Y') : 'N/A' }}</p>
                                            @if($package->payment_withdrawal_id)
                                                <p class="text-xs text-green-600 font-medium">💰 Colis de Paiement</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $package->sender->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">{{ $package->formatted_recipient ?? 'Destinataire N/A' }}</p>
                                        <p class="text-xs text-gray-400">{{ $package->delegationTo->name ?? 'Délégation N/A' }}</p>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    @if($package->assignedDeliverer)
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                                                <span class="text-orange-600 font-semibold text-xs">
                                                    {{ substr($package->assignedDeliverer->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $package->assignedDeliverer->phone }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">Non assigné</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <p class="text-sm font-semibold text-gray-900">{{ number_format($package->cod_amount, 3) }} DT</p>
                                    @if($package->delivery_attempts > 0)
                                        <p class="text-xs text-gray-500">{{ $package->delivery_attempts }} tentative(s)</p>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                                        @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                                        @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                                        @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $package->status }}
                                    </span>
                                    @if($package->delivery_attempts >= 3)
                                        <p class="text-xs text-red-600 mt-1">Urgent</p>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('depot-manager.packages.show', $package) }}"
                                           class="text-orange-600 hover:text-orange-800 transition-colors"
                                           title="Voir détails">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        @if($package->assignedDeliverer && in_array($package->status, ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']))
                                        <button onclick="reassignPackage({{ $package->id }})"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="Réassigner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </button>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $packages->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-gray-500">Aucun colis trouvé avec ces filtres</p>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
function reassignPackage(packageId) {
    if (confirm('Voulez-vous réassigner ce colis à un autre livreur ?')) {
        fetch(`/depot-manager/packages/${packageId}/reassign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Colis réassigné avec succès !');
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la réassignation');
        });
    }
}

function processExchangeReturn(packageId) {
    if (confirm('Voulez-vous marquer ce colis d\'échange comme retourné ?')) {
        fetch(`/depot-manager/packages/${packageId}/process-exchange-return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Colis d\'échange traité avec succès !');
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du traitement');
        });
    }
}
</script>

@endsection