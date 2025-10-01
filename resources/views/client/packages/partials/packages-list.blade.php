@php
$showBulkActions = $showBulkActions ?? false;
$emptyMessage = $emptyMessage ?? 'Aucun colis trouvé';
$emptyIcon = $emptyIcon ?? 'package';
$isPaginated = method_exists($packages, 'total');
$packagesCount = $isPaginated ? $packages->total() : $packages->count();
@endphp

@if($packages->isNotEmpty())
    @if($showBulkActions)
    <!-- Bulk Actions Header - Mobile Optimized -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-lg mb-4">
        <div class="px-4 py-3 flex items-center justify-between">
            <label class="flex items-center space-x-3 group cursor-pointer">
                <input type="checkbox" @change="toggleSelectAll()" :checked="allSelected"
                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Tout sélectionner</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </label>
            <div class="flex items-center space-x-2 text-xs text-gray-500">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-xs font-bold text-blue-600">{{ $packagesCount }}</span>
                </div>
                <span class="font-medium text-gray-700">{{ Str::plural('colis', $packagesCount) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Packages Grid - Responsive Cards -->
    <div class="space-y-3">
        @foreach($packages as $package)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

            <!-- Mobile-First Package Card -->
            <div class="p-4">
                <!-- Header Row: Checkbox + Status + Actions -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        @if($showBulkActions)
                            <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}"
                                   data-status="{{ $package->status }}"
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                        @endif

                        <!-- Status Badge - Improved Design -->
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border-2
                            {{ match($package->status) {
                                'CREATED' => 'bg-gray-50 text-gray-700 border-gray-200',
                                'AVAILABLE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'PICKED_UP' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'RETURNED' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'REFUSED' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                            } }}
                        ">
                            {{ match($package->status) {
                                'CREATED' => '🆕 Créé',
                                'AVAILABLE' => '📋 Disponible',
                                'PICKED_UP' => '🚚 Collecté',
                                'DELIVERED' => '✅ Livré',
                                'RETURNED' => '↩️ Retourné',
                                'REFUSED' => '❌ Refusé',
                                default => $package->status,
                            } }}
                        </span>
                    </div>

                    <!-- Quick Actions Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">

                            <div class="py-1">
                                <a href="{{ route('client.packages.show', $package) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Voir détails
                                </a>

                                <a href="{{ route('public.track.package', $package->package_code) }}" target="_blank"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    Suivre colis
                                </a>

                                <a href="{{ route('client.packages.print', $package) }}" target="_blank"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Imprimer étiquette
                                </a>

                                @if(!in_array($package->status, ['PAID', 'DELIVERED_PAID']))
                                    <div class="border-t border-gray-100"></div>
                                    <x-client.package-complaint-button :package="$package" class="flex items-center px-4 py-2 text-sm text-amber-600 hover:bg-amber-50" />
                                @endif

                                @if(in_array($package->status, ['CREATED', 'AVAILABLE']))
                                    <div class="border-t border-gray-100"></div>
                                    <button onclick="deletePackage({{ $package->id }}, '{{ $package->package_code }}')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Information -->
                <div class="space-y-3">
                    <!-- Package Code & COD Amount Row -->
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <a href="{{ route('client.packages.show', $package) }}"
                               class="text-lg font-bold text-gray-900 hover:text-blue-600 transition-colors">
                                {{ $package->package_code }}
                            </a>
                        </div>
                        <div class="text-right">
                            <div class="inline-flex items-center px-3 py-1 bg-green-50 border border-green-200 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                <span class="text-sm font-bold text-green-700">{{ number_format($package->cod_amount, 2) }} DT</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Information -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $package->recipient_data['name'] ?? 'N/A' }}
                                </p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500 truncate">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                                </div>
                                @if(isset($package->recipient_data['phone']))
                                <div class="flex items-center space-x-2 mt-1">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500">{{ $package->recipient_data['phone'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Date and Progress -->
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <div class="flex items-center space-x-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Créé {{ $package->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        @if($package->updated_at->gt($package->created_at))
                        <div class="flex items-center space-x-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <span>Maj {{ $package->updated_at->diffForHumans() }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Enhanced Pagination -->
    @if($isPaginated && $packages->hasPages())
    <div class="bg-white border border-gray-200 rounded-lg mt-6 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between text-xs text-gray-600">
                <span>
                    Affichage de <span class="font-medium text-gray-900">{{ $packages->firstItem() }}</span>
                    à <span class="font-medium text-gray-900">{{ $packages->lastItem() }}</span>
                    sur <span class="font-medium text-gray-900">{{ $packages->total() }}</span> colis
                </span>
                <div class="flex items-center space-x-2">
                    <span>Page {{ $packages->currentPage() }} / {{ $packages->lastPage() }}</span>
                </div>
            </div>
        </div>
        <div class="px-4 py-3">
            {{ $packages->appends(request()->query())->links('pagination::tailwind') }}
        </div>
    </div>
    @endif

@else
    <!-- Enhanced Empty State -->
    <div class="text-center py-12 px-4">
        <div class="mx-auto w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6 shadow-inner">
            @if($emptyIcon === 'clock')
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @elseif($emptyIcon === 'check')
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @else
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            @endif
        </div>

        <h3 class="text-xl font-semibold text-gray-800 mb-3">{{ $emptyMessage }}</h3>

        <div class="max-w-md mx-auto">
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <p class="text-gray-500 text-sm mb-6">
                    Aucun colis ne correspond à vos critères de recherche.
                    Essayez de modifier vos filtres ou de supprimer certains critères.
                </p>
                <div class="space-y-3">
                    <a href="{{ route('client.packages.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Voir tous les colis
                    </a>
                </div>
            @else
                <p class="text-gray-500 text-sm mb-6">
                    Vous n'avez pas encore créé de colis.
                    Commencez par créer votre premier envoi pour le voir apparaître ici.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('client.packages.create-fast') }}"
                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Créer rapidement
                    </a>
                    <a href="{{ route('client.packages.create') }}"
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer avec options
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif