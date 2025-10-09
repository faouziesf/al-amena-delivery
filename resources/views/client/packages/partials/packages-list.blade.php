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
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-2xl mb-4">
        <div class="px-4 py-3 flex items-center justify-between flex-col sm:flex-row">
            <label class="flex items-center space-x-3 group cursor-pointer flex-col sm:flex-row">
                <input type="checkbox" @change="toggleSelectAll()" :checked="allSelected"
                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2 min-h-[44px]">
                <div class="flex items-center space-x-2 flex-col sm:flex-row">
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Tout sélectionner</span>
                    <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </label>
            <div class="flex items-center space-x-2 text-sm sm:text-xs text-gray-500 flex-col sm:flex-row">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-col sm:flex-row">
                    <span class="text-sm sm:text-xs font-bold text-blue-600">{{ $packagesCount }}</span>
                </div>
                <span class="font-medium text-gray-700">{{ Str::plural('colis', $packagesCount) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Packages Grid - Mobile Optimized Compact List -->
    <div class="space-y-2 sm:grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">@foreach($packages as $package)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg hover:shadow-2xl transition-all duration-200 overflow-hidden transition-all duration-300 hover:-translate-y-1">

            <!-- Mobile Optimized Package Row -->
            <div class="p-2 sm:p-3">
                <div class="flex items-start sm:items-center justify-between flex-col sm:flex-row">
                    <!-- Left Section: Checkbox + Package Info -->
                    <div class="flex items-start sm:items-center space-x-2 sm:space-x-3 flex-1 min-w-0 flex-col sm:flex-row">
                        @if($showBulkActions)
                            <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}"
                                   data-status="{{ $package->status }}"
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2 flex-shrink-0 flex-col sm:flex-row">
                        @endif

                        <!-- Enhanced Status Badge - Mobile Optimized -->
                        <div class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-1.5 text-sm sm:text-xs font-semibold rounded-2xl border-2 flex-shrink-0 min-w-[70px] sm:min-w-[90px] justify-center
                            {{ match($package->status) {
                                'CREATED' => 'bg-gray-100 text-gray-800 border-gray-300 shadow-md hover:shadow-xl',
                                'AVAILABLE' => 'bg-blue-100 text-blue-800 border-blue-300 shadow-md hover:shadow-xl',
                                'PICKED_UP' => 'bg-indigo-100 text-indigo-800 border-indigo-300 shadow-md hover:shadow-xl',
                                'AT_DEPOT' => 'bg-yellow-100 text-yellow-800 border-yellow-300 shadow-md hover:shadow-xl',
                                'IN_TRANSIT' => 'bg-purple-100 text-purple-800 border-purple-300 shadow-md hover:shadow-xl',
                                'DELIVERED' => 'bg-emerald-100 text-emerald-800 border-emerald-300 shadow-md hover:shadow-xl',
                                'RETURNED' => 'bg-amber-100 text-amber-800 border-amber-300 shadow-md hover:shadow-xl',
                                'REFUSED' => 'bg-red-100 text-red-800 border-red-300 shadow-md hover:shadow-xl',
                                default => 'bg-gray-100 text-gray-800 border-gray-300 shadow-md hover:shadow-xl',
                            } }}
                         transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                            <span class="mr-1 sm:mr-1.5 text-sm">
                                {{ match($package->status) {
                                    'CREATED' => '🆕',
                                    'AVAILABLE' => '📋',
                                    'PICKED_UP' => '🚚',
                                    'AT_DEPOT' => '🏭',
                                    'IN_TRANSIT' => '🚛',
                                    'DELIVERED' => '✅',
                                    'RETURNED' => '↩️',
                                    'REFUSED' => '❌',
                                    default => '📦',
                                } }}
                            </span>
                            <span class="uppercase tracking-wide text-xs sm:text-sm sm:text-xs">
                                {{ match($package->status) {
                                    'CREATED' => 'Créé',
                                    'AVAILABLE' => 'Dispo',
                                    'PICKED_UP' => 'Collecté',
                                    'AT_DEPOT' => 'Au Dépôt',
                                    'IN_TRANSIT' => 'En Livraison',
                                    'DELIVERED' => 'Livré',
                                    'RETURNED' => 'Retourné',
                                    'REFUSED' => 'Refusé',
                                    default => 'Inconnu',
                                } }}
                            </span>
                        </div>

                        <!-- Package Details - Mobile Optimized -->
                        <div class="flex-1 min-w-0 flex-col sm:flex-row">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 mb-1 flex-col sm:flex-row">
                                <a href="{{ route('client.packages.show', $package) }}"
                                   class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors mb-1 sm:mb-0">
                                    {{ $package->package_code }}
                                </a>
                                <div class="flex items-center text-sm sm:text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded self-start flex-col sm:flex-row">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    <span class="font-medium">{{ number_format($package->cod_amount, 2) }} DT</span>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 text-sm sm:text-xs text-gray-500 space-y-1 sm:space-y-0 flex-col sm:flex-row">
                                <span class="truncate max-w-[150px] sm:max-w-[120px]">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                                <span class="flex items-center space-x-1 flex-col sm:flex-row">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span class="truncate max-w-[80px] sm:max-w-[100px]">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                                </span>
                                <span>{{ $package->created_at->format('d/m') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Actions - Mobile Optimized -->
                    <div class="flex items-start sm:items-center space-x-1 ml-2 sm:ml-3 flex-col sm:flex-row">
                        <!-- Quick Action Buttons for CREATED/AVAILABLE -->
                        @if(in_array($package->status, ['CREATED', 'AVAILABLE']))
                            <a href="{{ route('client.packages.edit', $package) }}"
                               class="p-1.5 text-blue-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors touch-manipulation"
                               title="Modifier">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button onclick="deletePackage({{ $package->id }}, '{{ $package->package_code }}')"
                                    class="p-1.5 text-red-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors touch-manipulation"
                                    title="Supprimer">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif

                        <!-- More Actions Dropdown -->
                        <div class="relative">
                            <button onclick="toggleDropdown({{ $package->id }})"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors touch-manipulation"
                                    title="Plus d'actions"
                                    id="dropdown-button-{{ $package->id }}">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div id="dropdown-menu-{{ $package->id }}"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-gray-200 z-30 hidden transition-all duration-300 hover:-translate-y-1"
                                 style="display: none;">

                                <div class="py-1">
                                    <a href="{{ route('client.packages.show', $package) }}"
                                       class="flex items-center px-3 sm:px-4 sm:px-5 py-2.5 sm:py-3.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 touch-manipulation flex-col sm:flex-row">
                                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Voir détails
                                    </a>

                                    <a href="{{ route('public.track.package', $package->package_code) }}" target="_blank"
                                       class="flex items-center px-3 sm:px-4 py-2.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 touch-manipulation flex-col sm:flex-row">
                                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Suivre colis
                                    </a>

                                    <a href="{{ route('client.packages.print', $package) }}" target="_blank"
                                       class="flex items-center px-3 sm:px-4 sm:px-5 py-2.5 sm:py-3.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 touch-manipulation flex-col sm:flex-row">
                                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Imprimer étiquette
                                    </a>

                                    @if(!in_array($package->status, ['PAID', 'DELIVERED_PAID']))
                                        <div class="border-t border-gray-100"></div>
                                        <a href="{{ route('client.complaints.create', $package) }}"
                                           class="flex items-center px-3 sm:px-4 sm:px-5 py-2.5 sm:py-3.5 sm:py-2 text-sm text-amber-600 hover:bg-amber-50 touch-manipulation flex-col sm:flex-row">
                                            <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            Créer réclamation
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Enhanced Pagination -->
    @if($isPaginated && $packages->hasPages())
    <div class="bg-white border border-gray-200 rounded-2xl mt-6 overflow-hidden transition-all duration-300 hover:-translate-y-1">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between text-sm sm:text-xs text-gray-600 flex-col sm:flex-row">
                <span>
                    Affichage de <span class="font-medium text-gray-900">{{ $packages->firstItem() }}</span>
                    à <span class="font-medium text-gray-900">{{ $packages->lastItem() }}</span>
                    sur <span class="font-medium text-gray-900">{{ $packages->total() }}</span> colis
                </span>
                <div class="flex items-center space-x-2 flex-col sm:flex-row">
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
        <div class="mx-auto w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6 shadow-inner flex-col sm:flex-row">
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
                <div class="flex flex-wrap gap-2 sm:gap-3"><a href="{{ route('client.packages.index') }}"
                       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="flex flex-row flex-wrap sm:flex-row gap-3 justify-center flex-col sm:flex-row"><a href="{{ route('client.packages.create-fast') }}"
                       class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Créer rapidement
                    </a>
                    <a href="{{ route('client.packages.create') }}"
                       class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-2xl hover:bg-gray-50 transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer avec options
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif