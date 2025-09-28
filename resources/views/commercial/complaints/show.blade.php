@extends('layouts.commercial')

@section('title', 'Réclamation #' . $complaint->id)
@section('page-title', 'Réclamation #' . $complaint->id)
@section('page-description', 'Détails de la réclamation')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('commercial.complaints.index') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Réclamation #{{ $complaint->id }}</h1>
            <p class="text-gray-600">{{ $complaint->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Détails de la réclamation -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations principales -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $complaint->subject }}</h3>
                    <div class="flex items-center space-x-2">
                        @if($complaint->priority === 'URGENT')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                Urgente
                            </span>
                        @elseif($complaint->priority === 'HIGH')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                Élevée
                            </span>
                        @elseif($complaint->priority === 'MEDIUM')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Moyenne
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Faible
                            </span>
                        @endif

                        @if($complaint->status === 'PENDING')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                En attente
                            </span>
                        @elseif($complaint->status === 'IN_PROGRESS')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                En cours
                            </span>
                        @elseif($complaint->status === 'RESOLVED')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Résolue
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Rejetée
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de réclamation</label>
                        <p class="text-gray-900">
                            @switch($complaint->type)
                                @case('DELIVERY_ISSUE')
                                    Problème de livraison
                                    @break
                                @case('PACKAGE_DAMAGED')
                                    Colis endommagé
                                    @break
                                @case('PACKAGE_LOST')
                                    Colis perdu
                                    @break
                                @case('WRONG_ADDRESS')
                                    Mauvaise adresse
                                    @break
                                @case('PAYMENT_ISSUE')
                                    Problème de paiement
                                    @break
                                @default
                                    Autre
                            @endswitch
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $complaint->description }}</p>
                        </div>
                    </div>

                    @if($complaint->proposed_solution)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Solution proposée par le client</label>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $complaint->proposed_solution }}</p>
                            </div>
                        </div>
                    @endif

                    @if($complaint->admin_notes)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes administratives</label>
                            <div class="bg-orange-50 rounded-lg p-4">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $complaint->admin_notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($complaint->status === 'PENDING' || $complaint->status === 'IN_PROGRESS')
                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Actions disponibles</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Assigner -->
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('commercial.complaints.assign', $complaint) }}">
                                @csrf
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigner à</label>
                                <select name="assigned_to" id="assigned_to"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 mb-3">
                                    <option value="">Sélectionner un agent</option>
                                    <!-- Les options seront chargées via JavaScript -->
                                </select>
                                <button type="submit"
                                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors">
                                    Assigner
                                </button>
                            </form>
                        </div>

                        <!-- Marquer comme urgent -->
                        @if($complaint->priority !== 'URGENT')
                            <div class="space-y-3">
                                <form method="POST" action="{{ route('commercial.complaints.urgent', $complaint) }}">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                                    <p class="text-sm text-gray-600 mb-3">Marquer cette réclamation comme urgente</p>
                                    <button type="submit"
                                            class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors"
                                            onclick="return confirm('Marquer cette réclamation comme urgente ?')">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        Marquer urgent
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Résoudre -->
                        <form method="POST" action="{{ route('commercial.complaints.resolve', $complaint) }}" class="space-y-3">
                            @csrf
                            <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Notes de résolution</label>
                            <textarea name="resolution_notes" id="resolution_notes" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Décrivez la solution apportée..."></textarea>
                            <button type="submit"
                                    class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors"
                                    onclick="return confirm('Marquer cette réclamation comme résolue ?')">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Résoudre
                            </button>
                        </form>

                        <!-- Rejeter -->
                        <form method="POST" action="{{ route('commercial.complaints.reject', $complaint) }}" class="space-y-3">
                            @csrf
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Raison du rejet</label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                      placeholder="Expliquez pourquoi cette réclamation est rejetée..."></textarea>
                            <button type="submit"
                                    class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors"
                                    onclick="return confirm('Rejeter cette réclamation ?')">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rejeter
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informations colis -->
            @if($complaint->package)
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Colis concerné</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Code</span>
                            <span class="text-sm font-medium text-gray-900">{{ $complaint->package->package_code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Statut</span>
                            <span class="text-sm font-medium text-gray-900">{{ $complaint->package->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Montant COD</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($complaint->package->cod_amount, 3) }} DT</span>
                        </div>
                        @if($complaint->package->assignedDeliverer)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Livreur</span>
                                <span class="text-sm font-medium text-gray-900">{{ $complaint->package->assignedDeliverer->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('commercial.packages.show', $complaint->package) }}"
                           class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                            Voir le colis →
                        </a>
                    </div>
                </div>
            @endif

            <!-- Informations client -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Client</h3>

                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-orange-800 font-bold">{{ strtoupper(substr($complaint->user->name, 0, 2)) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $complaint->user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $complaint->user->email }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Téléphone</span>
                        <span class="text-sm text-gray-900">{{ $complaint->user->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Statut</span>
                        <span class="text-sm text-gray-900">{{ $complaint->user->account_status }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('commercial.clients.show', $complaint->user) }}"
                       class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                        Voir le profil client →
                    </a>
                </div>
            </div>

            @if($complaint->assignedTo)
                <!-- Assigné à -->
                <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigné à</h3>

                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-800 font-bold text-sm">{{ strtoupper(substr($complaint->assignedTo->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $complaint->assignedTo->name }}</p>
                            <p class="text-sm text-gray-500">{{ $complaint->assignedTo->role }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Historique -->
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Historique</h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <div>
                            <p class="text-sm text-gray-900">Réclamation créée</p>
                            <p class="text-xs text-gray-500">{{ $complaint->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Créée</span>
                    </div>

                    @if($complaint->assigned_at)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm text-gray-900">Assignée</p>
                                <p class="text-xs text-gray-500">{{ $complaint->assigned_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Assignée</span>
                        </div>
                    @endif

                    @if($complaint->resolved_at)
                        <div class="flex justify-between items-center py-2">
                            <div>
                                <p class="text-sm text-gray-900">Résolue</p>
                                <p class="text-xs text-gray-500">{{ $complaint->resolved_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Résolue</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection