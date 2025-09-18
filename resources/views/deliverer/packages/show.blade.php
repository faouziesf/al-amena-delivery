@extends('layouts.deliverer')

@section('title', 'Détails Colis #' . $package->package_code)

@section('content')
<div class="min-h-screen bg-gray-50">
    
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="javascript:history.back()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">{{ $package->package_code }}</h1>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $package->status === 'AVAILABLE' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst(strtolower($package->status)) }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $package->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-xl font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</div>
                    <span class="text-xs text-gray-500">COD</span>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 py-6 space-y-6">
        
        <!-- Pickup Location -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-purple-50 px-4 py-3 border-b">
                <h2 class="font-semibold text-purple-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Lieu de Collecte (PICKUP)
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Délégation</label>
                    <p class="font-medium text-gray-900">{{ $package->delegationFrom->name ?? 'Non définie' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expéditeur</label>
                    <p class="font-medium text-gray-900">{{ $package->sender->name ?? ($package->sender_data['name'] ?? 'N/A') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                    <p class="text-gray-700">{{ $package->pickup_address ?? ($package->sender_data['address'] ?? 'Adresse non disponible') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                    <p class="text-gray-700">{{ $package->pickup_phone ?? ($package->sender_data['phone'] ?? 'N/A') }}</p>
                </div>
            </div>
        </div>

        <!-- Delivery Location -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-orange-50 px-4 py-3 border-b">
                <h2 class="font-semibold text-orange-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Lieu de Livraison
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Délégation</label>
                    <p class="font-medium text-gray-900">{{ $package->delegationTo->name ?? 'Non définie' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Destinataire</label>
                    <p class="font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Adresse</label>
                    <p class="text-gray-700">{{ $package->recipient_data['address'] ?? 'Adresse non disponible' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                    <p class="text-gray-700">{{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Package Info -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b">
                <h2 class="font-semibold text-gray-900">Informations Colis</h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contenu</label>
                    <p class="font-medium text-gray-900">{{ $package->content_description }}</p>
                </div>
                @if($package->notes)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <p class="text-gray-700">{{ $package->notes }}</p>
                    </div>
                @endif
                <div class="grid grid-cols-3 gap-4 pt-3 border-t">
                    <div class="text-center">
                        <p class="text-xs text-gray-600">Montant COD</p>
                        <p class="font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600">Frais Livraison</p>
                        <p class="font-semibold text-gray-900">{{ number_format($package->delivery_fee, 3) }} DT</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-600">Frais Retour</p>
                        <p class="font-semibold text-gray-900">{{ number_format($package->return_fee, 3) }} DT</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($package->status === 'AVAILABLE')
            <div class="bg-white rounded-xl shadow-sm p-4">
                <form action="{{ route('deliverer.packages.accept', $package) }}" method="POST" id="acceptForm">
                    @csrf
                    <button type="submit" 
                            class="w-full bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                        <span class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Accepter ce Pickup</span>
                        </span>
                    </button>
                </form>
            </div>
        @elseif($package->assigned_deliverer_id === auth()->id())
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-center text-gray-600 mb-3">Ce colis vous est assigné</p>
                
                @if($package->status === 'ACCEPTED')
                    <button class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 transition-colors mb-3">
                        Marquer comme Collecté
                    </button>
                @elseif($package->status === 'PICKED_UP')
                    <button class="w-full bg-orange-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-orange-700 transition-colors mb-3">
                        Marquer comme Livré
                    </button>
                @endif
                
                <p class="text-xs text-gray-500 text-center">
                    Statut actuel: {{ ucfirst(strtolower($package->status)) }}
                </p>
            </div>
        @endif

        <!-- Bottom spacing -->
        <div class="h-20"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const acceptForm = document.getElementById('acceptForm');
    if (acceptForm) {
        acceptForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = `
                <div class="flex items-center justify-center space-x-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Acceptation...</span>
                </div>
            `;
            button.disabled = true;
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Rediriger vers "Mes Pickups"
                    window.location.href = '{{ route("deliverer.pickups.mine") }}';
                } else {
                    alert(data.message || 'Erreur lors de l\'acceptation');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }
});
</script>
@endsection