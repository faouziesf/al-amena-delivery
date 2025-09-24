@extends('layouts.deliverer')

@section('title', 'Historique Recharges Client')

@section('page-title', 'Historique Recharges Client')
@section('page-description', 'Toutes les recharges clients effectuées')

@section('content')
<div x-data="topupHistoryApp()" class="space-y-6">
    <!-- Actions et stats -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <a href="{{ route('deliverer.client-topup.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                Nouvelle recharge
            </a>
        </div>

        <!-- Stats période -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center space-x-6 text-sm">
                <div class="text-center">
                    <p class="text-gray-500">Total recharges</p>
                    <p class="font-semibold text-gray-900">{{ $stats['total_topups'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-500">Montant total</p>
                    <p class="font-semibold text-green-600">{{ number_format($stats['total_amount'], 3) }} DT</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-500">Clients uniques</p>
                    <p class="font-semibold text-purple-600">{{ $stats['unique_clients'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-500">Montant moyen</p>
                    <p class="font-semibold text-blue-600">{{ number_format($stats['average_amount'], 3) }} DT</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">🔍 Filtres</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('deliverer.client-topup.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Statut -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>En attente</option>
                        <option value="VALIDATED" {{ request('status') === 'VALIDATED' ? 'selected' : '' }}>Validé</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>

                <!-- Date de début -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Date de fin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Recherche client -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recherche client</label>
                    <input 
                        type="text" 
                        name="client_search" 
                        value="{{ request('client_search') }}"
                        placeholder="Nom ou téléphone..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Boutons -->
                <div class="md:col-span-4 flex space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Filtrer</span>
                    </button>
                    <a href="{{ route('deliverer.client-topup.history') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des recharges -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">📋 Recharges effectuées</h3>
            <p class="text-sm text-gray-500">{{ $topups->total() }} recharges au total</p>
        </div>

        @if($topups->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topups as $topup)
                            <tr class="hover:bg-gray-50">
                                <!-- Client -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $topup->client->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $topup->client->phone }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Montant -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-green-600">{{ number_format($topup->amount, 3) }} DT</div>
                                    <div class="text-xs text-gray-500">{{ $topup->method_display }}</div>
                                </td>

                                <!-- Statut -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $topup->status_color }}">
                                        @if($topup->status === 'VALIDATED')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                            </svg>
                                        @elseif($topup->status === 'PENDING')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                            </svg>
                                        @elseif($topup->status === 'REJECTED')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                            </svg>
                                        @endif
                                        {{ $topup->status_display }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $topup->processed_at?->format('d/m/Y') ?? $topup->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $topup->processed_at?->format('H:i') ?? $topup->created_at->format('H:i') }}</div>
                                </td>

                                <!-- Code -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $topup->request_code }}</code>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @if($topup->status === 'VALIDATED')
                                            <a href="{{ route('deliverer.client-topup.receipt', $topup) }}" 
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-900 p-1 rounded" 
                                               title="Imprimer reçu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        
                                        <button @click="showDetails('{{ $topup->id }}')" 
                                                class="text-gray-600 hover:text-gray-900 p-1 rounded" 
                                                title="Voir détails">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $topups->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune recharge trouvée</h3>
                <p class="text-gray-500 mb-4">
                    @if(request()->hasAny(['status', 'date_from', 'date_to', 'client_search']))
                        Aucune recharge ne correspond à vos critères de recherche.
                    @else
                        Vous n'avez pas encore effectué de recharge client.
                    @endif
                </p>
                <a href="{{ route('deliverer.client-topup.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Effectuer une recharge
                </a>
            </div>
        @endif
    </div>

    <!-- Modal détails (si nécessaire) -->
    <div x-show="showModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-end justify-center pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Détails de la recharge</h3>
                    <!-- Contenu des détails -->
                    <div x-html="modalContent"></div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="showModal = false" 
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function topupHistoryApp() {
        return {
            showModal: false,
            modalContent: '',

            async showDetails(topupId) {
                try {
                    const response = await fetch(`/deliverer/client-topup/${topupId}`);
                    const html = await response.text();
                    this.modalContent = html;
                    this.showModal = true;
                } catch (error) {
                    console.error('Erreur lors du chargement des détails:', error);
                }
            }
        }
    }
</script>
@endpush