@extends('layouts.depot-manager')

@section('title', 'Détails Livreur - ' . $deliverer->name)
@section('page-title', 'Détails du Livreur')
@section('page-description', 'Informations complètes et performance')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('depot-manager.deliverers.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $deliverer->name }}</h1>
                <p class="text-gray-600">Livreur ID: {{ $deliverer->id }} • {{ $deliverer->assigned_delegation }}</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('depot-manager.deliverers.edit', $deliverer) }}"
               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>

            <button onclick="toggleDelivererStatus({{ $deliverer->id }})"
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                </svg>
                Changer Statut
            </button>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Profil -->
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <div class="text-center">
                <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-orange-600 font-bold text-2xl">
                        {{ substr($deliverer->name, 0, 2) }}
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $deliverer->name }}</h3>
                <p class="text-gray-600 text-sm">{{ $deliverer->deliverer_type ?? 'NORMAL' }}</p>

                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($deliverer->account_status === 'ACTIVE') bg-green-100 text-green-800
                        @elseif($deliverer->account_status === 'SUSPENDED') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $deliverer->account_status }}
                    </span>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-gray-900">{{ $deliverer->email }}</span>
                </div>

                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-gray-900">{{ $deliverer->phone }}</span>
                </div>

                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-gray-900">{{ $deliverer->assigned_delegation ?? 'Non assigné' }}</span>
                </div>

                @if($deliverer->address)
                <div class="flex items-start text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-gray-900">{{ $deliverer->address }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Statistiques de performance -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Colis En Cours</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['packages_in_progress'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Livrés Aujourd'hui</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['delivered_today'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">COD Collecté</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['cod_collected_today'] ?? 0, 0) }}</p>
                        <p class="text-xs text-gray-500">DT aujourd'hui</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de gestion -->
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Actions de gestion</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button onclick="reassignPackages({{ $deliverer->id }})"
                    class="flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Réassigner les Colis
            </button>

            <a href="{{ route('depot-manager.packages.index', ['deliverer' => $deliverer->id]) }}"
               class="flex items-center justify-center px-4 py-3 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Voir ses Colis
            </a>

            <button onclick="viewPerformanceReport({{ $deliverer->id }})"
                    class="flex items-center justify-center px-4 py-3 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Rapport Performance
            </button>
        </div>
    </div>

    <!-- Colis récents -->
    @if($recentHistory->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-orange-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Colis Récents</h3>
                <a href="{{ route('depot-manager.packages.index', ['deliverer' => $deliverer->id]) }}"
                   class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                    Voir tous →
                </a>
            </div>
        </div>

        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentHistory as $package)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">#{{ $package->package_code }}</p>
                            <p class="text-sm text-gray-500">{{ $package->formatted_recipient ?? 'Destinataire N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($package->status === 'DELIVERED') bg-green-100 text-green-800
                            @elseif($package->status === 'PICKED_UP') bg-blue-100 text-blue-800
                            @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $package->status }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">{{ number_format($package->cod_amount, 3) }} DT</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

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

function reassignPackages(delivererId) {
    if (confirm('Voulez-vous réassigner tous les colis de ce livreur ?')) {
        fetch(`/depot-manager/deliverers/${delivererId}/reassign-packages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.reassigned_count} colis ont été réassignés avec succès !`);
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

function viewPerformanceReport(delivererId) {
    window.open(`/depot-manager/reports?deliverer=${delivererId}`, '_blank');
}
</script>

@endsection