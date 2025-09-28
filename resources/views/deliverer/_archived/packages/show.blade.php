<!-- Final Version: {{ now() }} -->
@extends('layouts.deliverer')

@section('title', 'Colis ' . $package->package_code)

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Main Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">{{ $package->package_code }}</h1>
                    <div class="flex items-center gap-2 mt-2">
                        @php
                            $statusColors = [
                                'AVAILABLE' => 'bg-blue-100 text-blue-800',
                                'ACCEPTED' => 'bg-purple-100 text-purple-800',
                                'PICKED_UP' => 'bg-orange-100 text-orange-800',
                                'DELIVERED' => 'bg-green-100 text-green-800',
                                'VERIFIED' => 'bg-teal-100 text-teal-800',
                                'RETURNED' => 'bg-gray-100 text-gray-800',
                                'UNAVAILABLE' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $statusColor = $statusColors[$package->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                            {{ $package->status_message }}
                        </span>
                        @if($stats['is_urgent'])
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold animate-pulse">URGENT</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-xs text-gray-500">COD</p>
                <p class="text-2xl font-bold text-purple-800">{{ number_format($package->cod_amount, 3) }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-4">
        <div class="flex flex-col gap-4">
            @if($package->status === 'PICKED_UP' || $package->status === 'UNAVAILABLE')
                <button onclick="executeDelivery({{ $package->id }})" class="pkg-show-btn pkg-show-btn-primary">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Livrer le Colis</span>
                </button>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="executeAction('unavailable', {{ $package->id }}, 'Marquer comme indisponible ?')" class="pkg-show-btn pkg-show-btn-secondary-yellow">
                        <span>⏰ Indisponible</span>
                    </button>
                    @if($package->delivery_attempts >= 2)
                        <button onclick="executeAction('return', {{ $package->id }}, 'Retourner ce colis ?')" class="pkg-show-btn pkg-show-btn-secondary-red">
                            <span>↩️ Retour</span>
                        </button>
                    @endif
                </div>
            @elseif($package->status === 'ACCEPTED')
                <button onclick="executeAction('pickup', {{ $package->id }}, 'Confirmer la collecte ?')" class="pkg-show-btn pkg-show-btn-primary">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>Confirmer la Collecte</span>
                </button>
            @elseif($package->status === 'AVAILABLE')
                <button onclick="executeAction('accept', {{ $package->id }}, 'Accepter ce colis ?')" class="pkg-show-btn pkg-show-btn-primary">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 18.734V6.266L10.06 3.203A2 2 0 0112.143 3h.057a2 2 0 012 2v4zm-4-2h4M7 16H6a2 2 0 01-2-2V6a2 2 0 012-2h2.5"></path></svg>
                    <span>Accepter ce Colis</span>
                </button>
            @endif
            <a href="/test-receipt/{{ $package->id }}?print=true" class="pkg-show-btn pkg-show-btn-secondary text-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Voir le Reçu</span>
            </a>
        </div>
    </div>

    <!-- Pickup Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Point de Collecte
        </h2>
        <div class="space-y-3 text-sm">
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Nom:</strong> <span>{{ $package->supplier_data['name'] ?? $package->sender_data['name'] ?? 'N/A' }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Tél:</strong> <span>{{ $package->supplier_data['phone'] ?? $package->sender_data['phone'] ?? 'N/A' }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Adresse:</strong> <span>{{ $package->pickup_address ?? ($package->sender_data['address'] ?? 'N/A') }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Zone:</strong> <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-md text-xs">{{ $package->delegationFrom->name ?? 'N/A' }}</span></p>
        </div>
    </div>

    <!-- Delivery Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Point de Livraison
        </h2>
        <div class="space-y-3 text-sm">
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Nom:</strong> <span>{{ $package->recipient_data['name'] ?? 'N/A' }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Tél:</strong> <span>{{ $package->recipient_data['phone'] ?? 'N/A' }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Adresse:</strong> <span>{{ $package->recipient_data['address'] ?? 'N/A' }}</span></p>
            <p class="flex items-start"><strong class="font-medium text-gray-500 w-20">Zone:</strong> <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-md text-xs">{{ $package->delegationTo->name ?? 'N/A' }}</span></p>
            @if($package->delivery_attempts > 0)
                <p class="flex items-start text-red-600"><strong class="font-medium text-red-500 w-20">Tentatives:</strong> <span class="font-semibold">{{ $package->delivery_attempts }}/3</span></p>
            @endif
        </div>
    </div>

    <!-- Package Details Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-3">Détails du Colis</h2>
        <div class="space-y-2 text-sm">
            <p class="flex justify-between"><strong class="font-medium text-gray-500">Contenu:</strong> <span>{{ $package->content_description ?? 'N/A' }}</span></p>
            @if($package->package_weight)<p class="flex justify-between"><strong class="font-medium text-gray-500">Poids:</strong> <span>{{ number_format($package->package_weight, 3) }} kg</span></p>@endif
            @if($package->package_value)<p class="flex justify-between"><strong class="font-medium text-gray-500">Valeur:</strong> <span>{{ number_format($package->package_value, 3) }} DT</span></p>@endif
            <p class="flex justify-between"><strong class="font-medium text-gray-500">Frais de livraison:</strong> <span>{{ number_format($package->delivery_fee, 3) }} DT</span></p>
        </div>
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-purple-100">
            @if($package->is_fragile)<span class="badge-purple">Fragile</span>@endif
            @if($package->requires_signature)<span class="badge-purple">Signature Requise</span>@endif
            @if($package->special_instructions)<span class="badge-purple">Instructions Spéciales</span>@endif
            @if($package->notes)<span class="badge-purple">Notes</span>@endif
        </div>
    </div>

</div>



@push('scripts')
<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

function executeDelivery(packageId) {
    if (!confirm('Confirmer la livraison de ce colis ?')) return;

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('cod_collected', {{ $package->cod_amount }});
    formData.append('signature_required', 'false');
    formData.append('delivery_notes', '');

    fetch(`/deliverer/packages/${packageId}/deliver`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = `/test-receipt/${packageId}?print=true`;
            }, 1000);
        } else {
            showToast(data.message || 'Erreur lors de la livraison', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur de connexion', 'error');
    });
}

function executeAction(action, packageId, confirmMessage) {
    if (!confirm(confirmMessage)) return;

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');

    let url = '';
    switch(action) {
        case 'accept':
            url = `/deliverer/packages/${packageId}/accept`;
            break;
        case 'pickup':
            url = `/deliverer/packages/${packageId}/pickup`;
            break;
        case 'unavailable':
            url = `/deliverer/packages/${packageId}/unavailable`;
            break;
        case 'return':
            url = `/deliverer/packages/${packageId}/return`;
            break;
        default:
            showToast('Action non reconnue', 'error');
            return;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Erreur lors de l\'action', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur de connexion', 'error');
    });
}
</script>
@endpush
@endsection