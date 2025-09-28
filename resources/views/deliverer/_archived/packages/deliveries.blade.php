@extends('layouts.deliverer')

@section('title', 'Livraisons')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header simple -->
    <div class="bg-white shadow-sm sticky top-0 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        🚚 Mes Livraisons
                    </h1>
                    <p class="text-sm text-gray-600">{{ $packages->total() }} colis à livrer</p>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('deliverer.packages.index') }}"
                       class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                        🏠
                    </a>
                    <button onclick="window.location.reload()"
                            class="bg-green-100 text-green-600 p-2 rounded-lg">
                        🔄
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste simple des colis -->
    <div class="p-4 space-y-3">
        @forelse($packages as $package)
        <div class="bg-white rounded-lg shadow-sm border p-4
                    @if($package->delivery_attempts >= 3) border-l-4 border-l-red-500 @endif">

            <!-- En-tête du colis -->
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-bold text-gray-900">{{ $package->package_code }}</h3>
                    <div class="flex items-center space-x-2 mt-1">
                        @if($package->delivery_attempts >= 3)
                            <span class="bg-red-100 text-red-700 px-2 py-1 text-xs rounded-full">
                                🚨 URGENT - Tentative {{ $package->delivery_attempts }}/3
                            </span>
                        @elseif($package->delivery_attempts > 0)
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 text-xs rounded-full">
                                🔄 Tentative {{ $package->delivery_attempts + 1 }}/3
                            </span>
                        @endif

                        @if($package->cod_amount >= 50)
                            <span class="bg-green-100 text-green-700 px-2 py-1 text-xs rounded-full">
                                💰 COD Élevé
                            </span>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-lg font-bold
                               @if($package->cod_amount >= 100) text-red-600
                               @elseif($package->cod_amount >= 50) text-green-600
                               @else text-orange-600 @endif">
                        {{ number_format($package->cod_amount, 3) }} DT
                    </div>
                    <div class="text-xs text-gray-500">COD à collecter</div>
                </div>
            </div>

            <!-- Infos destinataire -->
            <div class="bg-green-50 p-3 rounded-lg mb-3">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">📞 {{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-700 mt-1">📍 {{ $package->recipient_data['address'] ?? 'N/A' }}</p>
                        <p class="text-sm text-green-600 font-medium">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                    </div>

                    @if($package->recipient_data['phone'] ?? null)
                    <a href="tel:{{ $package->recipient_data['phone'] }}"
                       class="bg-green-500 text-white p-2 rounded-lg ml-2">
                        📞
                    </a>
                    @endif
                </div>

                @if($package->delivery_attempts > 0 && $package->unavailable_notes)
                <div class="mt-2 pt-2 border-t border-green-200">
                    <p class="text-xs text-green-800">
                        <strong>Dernière tentative:</strong> {{ $package->unavailable_notes }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Contenu -->
            <div class="bg-gray-50 p-3 rounded-lg mb-3">
                <p class="text-sm text-gray-700">
                    <strong>Contenu:</strong> {{ $package->content_description ?? 'Non spécifié' }}
                </p>
            </div>

            <!-- Actions principales -->
            <div class="flex space-x-2">
                <!-- Livrer -->
                <form method="POST" action="{{ route('deliverer.packages.deliver', $package) }}"
                      class="flex-1">
                    @csrf
                    <!-- Champs cachés pour colis à 0 DT -->
                    @if($package->cod_amount == 0)
                        <input type="hidden" name="cod_collected" value="0">
                        <input type="hidden" name="recipient_name" value="{{ $package->recipient_data['name'] ?? 'Destinataire' }}">
                    @endif

                    <button type="submit"
                            onclick="return confirm('Confirmer la livraison de ce colis ?')"
                            class="w-full bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg font-bold transition-colors">
                        ✅ LIVRER
                    </button>
                </form>

                <!-- Indisponible -->
                <button onclick="markUnavailable('{{ $package->id }}', '{{ $package->package_code }}')"
                        class="bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg font-bold transition-colors">
                    ⏰
                </button>

                <!-- Voir détails -->
                <a href="{{ route('deliverer.packages.show', $package) }}"
                   class="bg-blue-100 text-blue-600 py-3 px-4 rounded-lg hover:bg-blue-200 transition-colors">
                    👁️
                </a>
            </div>
        </div>
        @empty
        <!-- État vide -->
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune livraison</h3>
            <p class="text-gray-600 mb-6">Vous n'avez pas de colis à livrer pour le moment.</p>
            <a href="{{ route('deliverer.packages.my-pickups') }}"
               class="bg-orange-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-600 transition-colors">
                📦 Voir Mes Pickups
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($packages->hasPages())
    <div class="px-4 pb-6">
        <div class="bg-white rounded-lg p-4">
            {{ $packages->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Modal simple pour marquer indisponible -->
<div id="unavailableModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4">Client Indisponible</h3>

            <form id="unavailableForm" method="POST">
                @csrf
                <input type="hidden" id="packageId" name="package_id">

                <!-- Raison -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Raison *</label>
                    <select name="reason" required class="w-full p-2 border rounded-lg">
                        <option value="">Sélectionner...</option>
                        <option value="CLIENT_ABSENT">Client absent</option>
                        <option value="ADDRESS_NOT_FOUND">Adresse introuvable</option>
                        <option value="CLIENT_REFUSES">Client refuse</option>
                        <option value="PHONE_OFF">Téléphone éteint</option>
                        <option value="OTHER">Autre</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Détails *</label>
                    <textarea name="attempt_notes" required
                              placeholder="Décrivez ce qui s'est passé..."
                              class="w-full p-2 border rounded-lg" rows="3"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button type="submit"
                            class="flex-1 bg-orange-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-orange-600">
                        Enregistrer
                    </button>
                    <button type="button" onclick="closeUnavailableModal()"
                            class="bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function markUnavailable(packageId, packageCode) {
    document.getElementById('packageId').value = packageId;
    document.getElementById('unavailableForm').action = `/deliverer/packages/${packageId}/unavailable`;
    document.getElementById('unavailableModal').classList.remove('hidden');
}

function closeUnavailableModal() {
    document.getElementById('unavailableModal').classList.add('hidden');
    document.getElementById('unavailableForm').reset();
}

// Auto-fermeture du modal en cliquant à l'extérieur
document.getElementById('unavailableModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUnavailableModal();
    }
});

// Toast simple pour les notifications
@if(session('success'))
    showToast("{{ session('success') }}", 'success');
@endif

@if(session('error'))
    showToast("{{ session('error') }}", 'error');
@endif

function showToast(message, type) {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 max-w-sm`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection