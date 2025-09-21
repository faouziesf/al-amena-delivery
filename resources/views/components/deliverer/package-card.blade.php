@props([
    'package',
    'showActions' => true,
    'showDetails' => true,
    'compact' => false,
    'interactive' => true
])

@php
    $statusColors = [
        'CREATED' => 'bg-gray-100 text-gray-800',
        'AVAILABLE' => 'bg-blue-100 text-blue-800',
        'ACCEPTED' => 'bg-yellow-100 text-yellow-800',
        'PICKED_UP' => 'bg-purple-100 text-purple-800',
        'DELIVERED' => 'bg-green-100 text-green-800',
        'RETURNED' => 'bg-red-100 text-red-800',
        'REFUSED' => 'bg-red-100 text-red-800',
        'PAID' => 'bg-emerald-100 text-emerald-800',
        'CANCELLED' => 'bg-gray-100 text-gray-800'
    ];

    $statusIcons = [
        'CREATED' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        'AVAILABLE' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'ACCEPTED' => 'M5 13l4 4L19 7',
        'PICKED_UP' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        'DELIVERED' => 'M5 13l4 4L19 7',
        'RETURNED' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
        'REFUSED' => 'M6 18L18 6M6 6l12 12',
        'PAID' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
        'CANCELLED' => 'M6 18L18 6M6 6l12 12'
    ];

    $statusText = [
        'CREATED' => 'Créé',
        'AVAILABLE' => 'Disponible',
        'ACCEPTED' => 'Accepté',
        'PICKED_UP' => 'Collecté',
        'DELIVERED' => 'Livré',
        'RETURNED' => 'Retourné',
        'REFUSED' => 'Refusé',
        'PAID' => 'Payé',
        'CANCELLED' => 'Annulé'
    ];

    $cardClass = $compact ? 'p-3' : 'p-4';
    $cardClass .= $interactive ? ' hover:shadow-md transition-shadow cursor-pointer' : '';
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-xl border border-gray-200 $cardClass"]) }}
     @if($interactive)
     @click="$dispatch('package-selected', { package: {{ json_encode($package) }} })"
     @endif>

    <!-- Header -->
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            <!-- Package Code -->
            <div class="flex items-center space-x-2 mb-1">
                <h3 class="font-bold text-gray-900 font-mono text-sm">
                    {{ $package->package_code }}
                </h3>

                @if($package->urgent ?? false)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Urgent
                </span>
                @endif
            </div>

            <!-- Status Badge -->
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$package->status] ?? 'bg-gray-100 text-gray-800' }}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcons[$package->status] ?? $statusIcons['CREATED'] }}"/>
                    </svg>
                    {{ $statusText[$package->status] ?? $package->status }}
                </span>

                @if($package->cod_amount > 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    COD
                </span>
                @endif
            </div>
        </div>

        <!-- Action Menu -->
        @if($showActions)
        <div class="relative" x-data="{ open: false }">
            <button @click.stop="open = !open"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                </svg>
            </button>

            <div x-show="open"
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">

                <div class="py-1">
                    <a href="{{ route('deliverer.packages.show', $package) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Voir détails
                    </a>

                    @if($package->status === 'AVAILABLE')
                    <button @click="$dispatch('package-action', { action: 'accept', package: {{ $package->id }} })"
                            class="block w-full text-left px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Accepter
                    </button>
                    @endif

                    @if($package->status === 'ACCEPTED')
                    <button @click="$dispatch('package-action', { action: 'pickup', package: {{ $package->id }} })"
                            class="block w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Marquer collecté
                    </button>
                    @endif

                    @if($package->status === 'PICKED_UP')
                    <button @click="$dispatch('package-action', { action: 'deliver', package: {{ $package->id }} })"
                            class="block w-full text-left px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Marquer livré
                    </button>
                    @endif

                    <button @click="$dispatch('scan-package', { code: '{{ $package->package_code }}' })"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                        </svg>
                        Scanner
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($showDetails)
    <!-- Details -->
    <div class="space-y-3">
        <!-- Recipient/Sender Info -->
        @if($package->status === 'ACCEPTED' || $package->status === 'PICKED_UP')
        <!-- Delivery Info -->
        <div class="bg-blue-50 rounded-lg p-3">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-blue-900">Destinataire</p>
                    <p class="text-sm text-blue-800 font-medium">
                        {{ $package->recipient_data['name'] ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-blue-700 mt-1">
                        {{ $package->recipient_data['phone'] ?? '' }}
                    </p>
                    <p class="text-xs text-blue-700">
                        {{ $package->recipient_data['address'] ?? '' }}
                    </p>
                    @if($package->delegationTo)
                    <p class="text-xs text-blue-600 font-medium mt-1">
                        📍 {{ $package->delegationTo->name }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @else
        <!-- Pickup Info -->
        <div class="bg-purple-50 rounded-lg p-3">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-purple-900">Expéditeur</p>
                    <p class="text-sm text-purple-800 font-medium">
                        {{ $package->sender->name ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-purple-700 mt-1">
                        {{ $package->sender->phone ?? '' }}
                    </p>
                    <p class="text-xs text-purple-700">
                        {{ $package->sender_data['address'] ?? '' }}
                    </p>
                    @if($package->delegationFrom)
                    <p class="text-xs text-purple-600 font-medium mt-1">
                        📍 {{ $package->delegationFrom->name }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Package Details -->
        <div class="grid grid-cols-2 gap-4 text-sm">
            @if($package->cod_amount > 0)
            <div class="bg-emerald-50 rounded-lg p-2">
                <p class="text-xs text-emerald-600 font-medium">Montant COD</p>
                <p class="text-lg font-bold text-emerald-800">
                    {{ number_format($package->cod_amount, 3) }} DT
                </p>
            </div>
            @endif

            @if($package->delivery_fee > 0)
            <div class="bg-blue-50 rounded-lg p-2">
                <p class="text-xs text-blue-600 font-medium">Frais de livraison</p>
                <p class="text-sm font-bold text-blue-800">
                    {{ number_format($package->delivery_fee, 3) }} DT
                </p>
            </div>
            @endif

            @if($package->weight)
            <div class="bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-600 font-medium">Poids</p>
                <p class="text-sm font-bold text-gray-800">
                    {{ $package->weight }} kg
                </p>
            </div>
            @endif

            @if($package->attempts > 0)
            <div class="bg-yellow-50 rounded-lg p-2">
                <p class="text-xs text-yellow-600 font-medium">Tentatives</p>
                <p class="text-sm font-bold text-yellow-800">
                    {{ $package->attempts }}/3
                </p>
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($package->notes || $package->delivery_notes)
        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-xs text-gray-600 font-medium mb-1">Notes</p>
            <p class="text-sm text-gray-800">
                {{ $package->notes ?? $package->delivery_notes }}
            </p>
        </div>
        @endif

        <!-- Timeline (if not compact) -->
        @if(!$compact && $package->statusHistory)
        <div class="border-t pt-3">
            <p class="text-xs text-gray-600 font-medium mb-2">Historique</p>
            <div class="space-y-1">
                @foreach($package->statusHistory->take(3) as $history)
                <div class="flex items-center space-x-2 text-xs">
                    <div class="w-2 h-2 rounded-full {{ $statusColors[$history->status] ?? 'bg-gray-200' }}"></div>
                    <span class="text-gray-600">{{ $history->created_at->format('d/m H:i') }}</span>
                    <span class="text-gray-800">{{ $statusText[$history->status] ?? $history->status }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Quick Actions (Compact Mode) -->
    @if($compact && $showActions)
    <div class="flex space-x-2 mt-3 pt-3 border-t">
        @if($package->status === 'AVAILABLE')
        <button @click="$dispatch('package-action', { action: 'accept', package: {{ $package->id }} })"
                class="flex-1 bg-emerald-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:bg-emerald-700 transition-colors">
            Accepter
        </button>
        @endif

        @if($package->status === 'ACCEPTED')
        <button @click="$dispatch('package-action', { action: 'pickup', package: {{ $package->id }} })"
                class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
            Collecter
        </button>
        @endif

        @if($package->status === 'PICKED_UP')
        <button @click="$dispatch('package-action', { action: 'deliver', package: {{ $package->id }} })"
                class="flex-1 bg-emerald-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:bg-emerald-700 transition-colors">
            Livrer
        </button>
        @endif

        <button @click="$dispatch('scan-package', { code: '{{ $package->package_code }}' })"
                class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
            </svg>
        </button>
    </div>
    @endif
</div>