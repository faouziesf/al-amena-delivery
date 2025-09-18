@extends('layouts.deliverer')

@section('title', 'Pickups Disponibles')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="availablePickupsApp()">
    
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-10">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Pickups Disponibles
                    </h1>
                    <div class="flex items-center space-x-4 mt-1">
                        <span class="text-sm text-gray-600">{{ $packages->count() }} colis disponibles</span>
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-blue-600">Temps réel</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button @click="refreshData()" 
                            class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    @if($packages->count() === 0)
        <div class="text-center py-16 px-4">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun pickup disponible</h3>
            <p class="text-gray-500 text-sm mb-4">Tous les colis ont été acceptés par d'autres livreurs ou il n'y a pas de nouveaux colis.</p>
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="inline-block bg-blue-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-blue-700 transition-colors">
                Actualiser
            </a>
        </div>
    @else
        <!-- Packages List -->
        <div class="px-4 pb-6 space-y-4">
            @foreach($packages as $package)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    
                    <!-- Package Header -->
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-blue-900">{{ $package->package_code }}</span>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="text-xs text-green-600 font-medium">DISPONIBLE</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</div>
                                <span class="text-xs text-gray-500">COD</span>
                            </div>
                        </div>

                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $package->created_at->diffForHumans() }}</span>
                            <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                Premier arrivé = premier servi
                            </span>
                        </div>
                    </div>

                    <!-- Package Details -->
                    <div class="p-4 space-y-3">
                        
                        <!-- Pickup Location -->
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-semibold text-purple-700 bg-purple-200 px-2 py-1 rounded-full">PICKUP</span>
                                        <span class="text-sm font-medium text-purple-900">
                                            {{ $package->delegationFrom->name ?? 'Zone non définie' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 font-medium">
                                        {{ $package->sender->name ?? ($package->sender_data['name'] ?? 'Expéditeur') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $package->pickup_address ?? ($package->sender_data['address'] ?? 'Adresse non disponible') }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span>{{ $package->pickup_phone ?? ($package->sender_data['phone'] ?? 'N/A') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Location -->
                        <div class="bg-orange-50 p-3 rounded-lg">
                            <div class="flex items-start space-x-3">
                                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-semibold text-orange-700 bg-orange-200 px-2 py-1 rounded-full">LIVRAISON</span>
                                        <span class="text-sm font-medium text-orange-900">
                                            {{ $package->delegationTo->name ?? 'Zone non définie' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 font-medium">
                                        {{ $package->recipient_data['name'] ?? 'Destinataire' }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $package->recipient_data['address'] ?? 'Adresse non disponible' }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span>{{ $package->recipient_data['phone'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Info -->
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <span class="text-gray-600">Contenu:</span>
                                    <span class="font-medium text-gray-900 ml-1">{{ $package->content_description }}</span>
                                </div>
                            </div>
                            @if($package->notes)
                                <div class="mt-2 text-sm">
                                    <span class="text-gray-600">Notes:</span>
                                    <span class="text-gray-900 ml-1">{{ $package->notes }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200">
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span>Livraison: {{ number_format($package->delivery_fee, 3) }} DT</span>
                                    <span>Retour: {{ number_format($package->return_fee, 3) }} DT</span>
                                </div>
                                @if($package->special_instructions || $package->is_fragile || $package->requires_signature)
                                    <div class="flex items-center space-x-1 text-xs">
                                        <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <span class="text-amber-600">Instructions spéciales</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-4 bg-gray-50 border-t">
                        <div class="flex space-x-3">
                            <form action="{{ route('deliverer.packages.accept', $package) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                                    <span class="flex items-center justify-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Accepter Pickup</span>
                                    </span>
                                </button>
                            </form>
                            
                            <a href="{{ route('deliverer.packages.show', $package) }}" 
                               class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
            <div class="px-4 pb-6">
                {{ $packages->links() }}
            </div>
        @endif
    @endif

    <!-- Bottom Spacing -->
    <div class="h-20"></div>
</div>

<script>
function availablePickupsApp() {
    return {
        init() {
            console.log('Pickups disponibles initialisé');
        },

        refreshData() {
            window.location.reload();
        },

        showToast(message, type = 'success') {
            // Create and show toast notification
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection