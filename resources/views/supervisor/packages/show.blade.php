@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('supervisor.packages.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Colis #{{ $package->package_code }}
                </h1>
                <div class="flex items-center space-x-3 mt-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
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
                    <span class="text-sm text-gray-500">
                        Créé {{ $package->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Détails du colis -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Informations du Colis
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Code de Suivi</label>
                            <p class="text-lg font-bold text-gray-900">#{{ $package->package_code }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Type de Livraison</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($package->delivery_type === 'fast') bg-green-100 text-green-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $package->delivery_type === 'fast' ? '⚡ Rapide' : '🎯 Avancé' }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Description du Contenu</label>
                            <p class="text-gray-900">{{ $package->content_description ?: 'Non spécifié' }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Montant COD</label>
                            <p class="text-lg font-bold text-green-600">{{ number_format($package->cod_amount, 2) }} DT</p>
                        </div>
                        @if($package->notes)
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Notes</label>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $package->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations expéditeur -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Expéditeur
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Nom</label>
                            <p class="text-lg font-medium text-gray-900">{{ $package->sender->name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <p class="text-gray-900">{{ $package->sender->email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                            <p class="text-gray-900">{{ $package->sender->phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Délégation</label>
                            <p class="text-gray-900">{{ $package->delegationFrom->name ?? 'Non spécifiée' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations destinataire -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-red-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Destinataire
                    </h3>
                </div>
                <div class="p-6">
                    @if($package->recipient_data)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Nom</label>
                            <p class="text-lg font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'Non spécifié' }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                            <p class="text-gray-900">{{ $package->recipient_data['phone'] ?? 'Non spécifié' }}</p>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Adresse</label>
                            <p class="text-gray-900">{{ $package->recipient_data['address'] ?? 'Non spécifiée' }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Délégation de Destination</label>
                            <p class="text-gray-900">{{ $package->delegationTo->name ?? 'Non spécifiée' }}</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <p class="text-gray-500">Informations destinataire non disponibles</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            @if(isset($timeline) && $timeline->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Historique du Colis
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($timeline as $key => $event)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-{{ $event['color'] ?? 'gray' }}-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($event['icon'] === 'plus')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    @elseif($event['icon'] === 'check')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    @elseif($event['icon'] === 'truck')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                    @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $event['title'] }}</p>
                                                <p class="text-sm text-gray-500">{{ $event['description'] }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $event['date']->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Actions
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <button onclick="showStatusModal()" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Changer le Statut
                    </button>

                    @if(!$package->assigned_deliverer_id)
                    <button onclick="showAssignModal()" class="w-full px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:from-green-600 hover:to-emerald-600 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Assigner un Livreur
                    </button>
                    @endif

                    <button onclick="showCodModal()" class="w-full px-4 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Modifier COD
                    </button>
                </div>
            </div>

            <!-- Informations livreur -->
            @if($package->assignedDeliverer)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Livreur Assigné
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <span class="text-lg font-bold text-blue-600">{{ substr($package->assignedDeliverer->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</p>
                            <p class="text-sm text-gray-500">{{ $package->assignedDeliverer->email }}</p>
                            <p class="text-sm text-gray-500">{{ $package->assignedDeliverer->phone }}</p>
                        </div>
                    </div>
                    @if($package->assigned_at)
                    <div class="mt-4 text-sm text-gray-500">
                        Assigné le {{ $package->assigned_at->format('d/m/Y à H:i') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Résumé financier -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Résumé Financier
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Montant COD</span>
                        <span class="text-lg font-bold text-green-600">{{ number_format($package->cod_amount, 2) }} DT</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Frais de livraison</span>
                        <span class="text-lg font-bold text-blue-600">{{ number_format($package->delivery_fee, 2) }} DT</span>
                    </div>
                    @if($package->return_fee > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Frais de retour</span>
                        <span class="text-lg font-bold text-red-600">{{ number_format($package->return_fee, 2) }} DT</span>
                    </div>
                    @endif
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-medium text-gray-900">Total</span>
                            <span class="text-xl font-bold text-gray-900">
                                {{ number_format($package->cod_amount + $package->delivery_fee + $package->return_fee, 2) }} DT
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal changement de statut -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Changer le Statut</h3>
            <form method="POST" action="{{ route('supervisor.packages.update-status', $package) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau statut</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg">
                        <option value="CREATED" {{ $package->status === 'CREATED' ? 'selected' : '' }}>Créé</option>
                        <option value="AVAILABLE" {{ $package->status === 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                        <option value="OUT_FOR_DELIVERY" {{ $package->status === 'OUT_FOR_DELIVERY' ? 'selected' : '' }}>En livraison</option>
                        <option value="PICKED_UP" {{ $package->status === 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                        <option value="DELIVERED" {{ $package->status === 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                        <option value="RETURNED" {{ $package->status === 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                        <option value="CANCELLED" {{ $package->status === 'CANCELLED' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (optionnel)</label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-lg" placeholder="Raison du changement..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('statusModal')" class="px-4 py-2 text-gray-600 border rounded-lg">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
}

function showAssignModal() {
    // Implémentation à ajouter
    alert('Fonctionnalité d\'assignation en cours de développement');
}

function showCodModal() {
    // Implémentation à ajouter
    alert('Fonctionnalité de modification COD en cours de développement');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection