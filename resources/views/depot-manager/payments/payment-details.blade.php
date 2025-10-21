@extends('layouts.depot-manager')

@section('title', 'Détails Paiement')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Détails du Paiement</h1>
                <p class="text-sm text-gray-600 mt-1">Code: {{ $withdrawal->request_code }}</p>
            </div>
            <a href="{{ route('depot-manager.payments.to-prep') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
        
        <!-- Status Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
            @if($withdrawal->status === 'PENDING') bg-yellow-100 text-yellow-800
            @elseif($withdrawal->status === 'APPROVED') bg-blue-100 text-blue-800
            @elseif($withdrawal->status === 'READY_FOR_DELIVERY') bg-purple-100 text-purple-800
            @elseif($withdrawal->status === 'IN_PROGRESS') bg-indigo-100 text-indigo-800
            @elseif($withdrawal->status === 'DELIVERED' || $withdrawal->status === 'COMPLETED') bg-green-100 text-green-800
            @elseif($withdrawal->status === 'REJECTED') bg-red-100 text-red-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ $withdrawal->method_display }} - {{ $withdrawal->status }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Colonne Principale -->
        <div class="md:col-span-2 space-y-6">
            <!-- Informations Client -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informations Client
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Nom:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->client->name }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Téléphone:</span>
                        <a href="tel:{{ $withdrawal->client->phone }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                            {{ $withdrawal->client->phone }}
                        </a>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Email:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->client->email }}</span>
                    </div>
                    @if($withdrawal->client->wallet)
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Solde Wallet:</span>
                        <span class="text-sm font-medium text-emerald-600">{{ number_format($withdrawal->client->wallet->balance, 3) }} DT</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Détails du Paiement -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Détails du Paiement
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Montant:</span>
                        <span class="text-lg font-bold text-green-600">{{ number_format($withdrawal->amount, 3) }} DT</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Méthode:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->method_display }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Demandé le:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    @if($withdrawal->approved_at)
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Approuvé le:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->approved_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations de Livraison -->
            @if($withdrawal->method === 'CASH_DELIVERY')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Informations de Livraison
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Adresse:</span>
                        <span class="text-sm font-medium text-gray-900 text-right">
                            {{ $withdrawal->delivery_address ?? $withdrawal->client->address ?? 'Non spécifié' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Ville:</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $withdrawal->delivery_city ?? $withdrawal->client->city ?? 'Non spécifié' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Téléphone:</span>
                        <a href="tel:{{ $withdrawal->delivery_phone ?? $withdrawal->client->phone }}" 
                           class="text-sm font-medium text-blue-600 hover:text-blue-800">
                            {{ $withdrawal->delivery_phone ?? $withdrawal->client->phone }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($withdrawal->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-yellow-900 mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Notes du Client
                </h3>
                <p class="text-sm text-gray-700">{{ $withdrawal->notes }}</p>
            </div>
            @endif

            <!-- Colis Associé -->
            @if($withdrawal->assignedPackage)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Colis de Paiement
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Code Colis:</span>
                        <a href="{{ route('depot-manager.packages.show', $withdrawal->assignedPackage) }}" 
                           class="text-sm font-medium text-blue-600 hover:text-blue-800">
                            {{ $withdrawal->assignedPackage->package_code }}
                        </a>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Statut:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->assignedPackage->status }}</span>
                    </div>
                    @if($withdrawal->assignedPackage->assignedDeliverer)
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-600">Livreur:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $withdrawal->assignedPackage->assignedDeliverer->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Colonne Latérale - Actions -->
        <div class="space-y-4">
            <!-- Actions Rapides -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Actions</h3>
                <div class="space-y-2">
                    @if($withdrawal->status === 'PENDING')
                    <button onclick="approvePayment({{ $withdrawal->id }})"
                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        ✅ Approuver
                    </button>
                    <button onclick="rejectPayment({{ $withdrawal->id }})"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        ❌ Rejeter
                    </button>
                    @endif
                    
                    @if($withdrawal->status === 'APPROVED' && !$withdrawal->assignedPackage)
                    <button onclick="createPackage({{ $withdrawal->id }})"
                            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        📦 Créer Colis
                    </button>
                    @endif
                    
                    @if($withdrawal->assignedPackage)
                    <a href="{{ route('depot-manager.packages.show', $withdrawal->assignedPackage) }}"
                       class="block w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                        📋 Voir le Colis
                    </a>
                    @endif
                </div>
            </div>

            <!-- Informations Complémentaires -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Informations</h3>
                <div class="space-y-2 text-xs text-gray-600">
                    <p><strong>ID:</strong> {{ $withdrawal->id }}</p>
                    <p><strong>Créé:</strong> {{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Mis à jour:</strong> {{ $withdrawal->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function approvePayment(id) {
    if (!confirm('Approuver ce paiement ?')) return;
    
    try {
        const response = await fetch(`/depot-manager/api/payments/${id}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        if (data.success) {
            alert('✅ Paiement approuvé');
            location.reload();
        } else {
            alert('❌ Erreur: ' + data.message);
        }
    } catch (error) {
        alert('❌ Erreur: ' + error.message);
    }
}

async function rejectPayment(id) {
    const reason = prompt('Raison du refus:');
    if (!reason) return;
    
    try {
        const response = await fetch(`/depot-manager/api/payments/${id}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ reason })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('✅ Paiement rejeté');
            location.reload();
        } else {
            alert('❌ Erreur: ' + data.message);
        }
    } catch (error) {
        alert('❌ Erreur: ' + error.message);
    }
}

async function createPackage(id) {
    if (!confirm('Créer un colis de paiement ?')) return;
    
    try {
        const response = await fetch(`/depot-manager/api/payments/${id}/create-package`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        if (data.success) {
            alert('✅ Colis créé: ' + data.package_code);
            location.reload();
        } else {
            alert('❌ Erreur: ' + data.message);
        }
    } catch (error) {
        alert('❌ Erreur: ' + error.message);
    }
}
</script>
@endsection
