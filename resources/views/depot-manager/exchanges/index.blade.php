@extends('layouts.depot-manager')

@section('title', 'Colis Échanges à Traiter')

@section('page-title', '🔄 Colis Échanges')

@section('content')
<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-gray-600">Gérez les colis échanges livrés dans vos gouvernorats</p>
        </div>
        <a href="{{ route('depot-manager.exchanges.history') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Historique
        </a>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm animate-pulse">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-800 font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">À traiter</p>
                    <p class="text-4xl font-bold mt-2">{{ $exchanges->total() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Sur cette page</p>
                    <p class="text-4xl font-bold mt-2">{{ $exchanges->count() }}</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Colis Échanges -->
    <form action="{{ route('depot-manager.exchanges.create-returns') }}" method="POST" id="exchangeForm">
        @csrf
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-red-50 to-orange-50 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Liste des Échanges ({{ $exchanges->total() }})
                </h3>
                <button type="submit" id="createReturnsBtn" disabled class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors">
                    📦 Créer Retours (<span id="selectedCount">0</span>)
                </button>
            </div>
            
            @if($exchanges->isEmpty())
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun colis échange à traiter</h4>
            <p class="text-gray-500">Les colis échanges livrés apparaîtront ici</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataire</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Livré</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($exchanges as $package)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="package_ids[]" value="{{ $package->id }}" class="package-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 animate-pulse">
                                    🔄
                                </span>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $package->package_code }}</div>
                                    <div class="text-sm text-gray-500">COD: {{ number_format($package->cod_amount, 3) }} DT</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $package->sender->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $package->sender_data['phone'] ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $package->recipient_data['phone'] ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                {{ $package->delegationTo->governorate ?? 'N/A' }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ $package->assignedDeliverer->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $package->delivered_at ? $package->delivered_at->format('d/m/Y') : 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $package->delivered_at ? $package->delivered_at->format('H:i') : '' }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        @if($exchanges->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $exchanges->links() }}
        </div>
        @endif
    </div>
    </form>

    <!-- Info Box -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-lg font-semibold text-blue-900 mb-2">📖 Comment ça marche ?</h4>
                <div class="space-y-2 text-sm text-blue-800">
                    <p><span class="font-bold">1.</span> Sélectionnez un ou plusieurs colis échanges livrés</p>
                    <p><span class="font-bold">2.</span> Cliquez sur "Créer Retours" pour générer automatiquement les colis retours</p>
                    <p><span class="font-bold">3.</span> Les retours seront créés avec le statut "AT_DEPOT"</p>
                    <p><span class="font-bold">4.</span> Vous serez redirigé vers l'impression des bordereaux</p>
                    <p><span class="font-bold">⚠️ Important :</span> Le statut du colis original ne change PAS (reste DELIVERED car l'échange est réussi)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.package-checkbox');
    const submitBtn = document.getElementById('createReturnsBtn');
    const selectedCount = document.getElementById('selectedCount');
    const form = document.getElementById('exchangeForm');

    function updateCount() {
        const checked = document.querySelectorAll('.package-checkbox:checked').length;
        selectedCount.textContent = checked;
        submitBtn.disabled = checked === 0;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateCount();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });

    form.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.package-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('❌ Veuillez sélectionner au moins un colis');
            return false;
        }
        
        if (!confirm(`🔄 Créer ${checked} retour(s) d'échange ?\n\nLes colis retours seront créés et prêts à être imprimés.`)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
