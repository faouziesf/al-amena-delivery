@php
$showBulkActions = $showBulkActions ?? false;
$emptyMessage = $emptyMessage ?? 'Aucun colis trouvé';
$emptyIcon = $emptyIcon ?? 'package';
$isPaginated = method_exists($packages, 'total');
$packagesCount = $isPaginated ? $packages->total() : $packages->count();
@endphp

@if($packages->isNotEmpty())
    @if($showBulkActions)
    <div class="bg-gray-50/50 border-b border-gray-200">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 group cursor-pointer">
                    <input type="checkbox" @change="toggleSelectAll()" :checked="allSelected" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-gray-600 group-hover:text-blue-600">Tout sélectionner</span>
                </label>
            </div>
            <div class="flex items-center space-x-3 text-xs text-gray-500">
                <span class="font-semibold text-gray-700">{{ $packagesCount }}</span>
                <span>{{ Str::plural('colis', $packagesCount) }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="divide-y divide-gray-200 bg-white">
        @foreach($packages as $package)
        <div class="package-card group hover:bg-gray-50/50 transition-colors duration-150 relative">
            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r-full
                {{ match($package->status) {
                    'DELIVERED' => 'bg-green-500',
                    'PICKED_UP' => 'bg-indigo-500',
                    'RETURNED' => 'bg-orange-500',
                    'REFUSED' => 'bg-red-500',
                    default => 'bg-blue-500',
                } }}
            "></div>
            
            <div class="pl-5 pr-4 py-3 flex items-center flex-wrap justify-between gap-x-6 gap-y-3">

                <div class="flex items-center space-x-3 min-w-[220px]">
                    @if($showBulkActions)
                        <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}" data-status="{{ $package->status }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    @endif
                    <div>
                        <a href="{{ route('client.packages.show', $package) }}" class="font-bold text-gray-800 hover:text-blue-600 text-sm">{{ $package->package_code }}</a>
                        <p class="text-xs text-gray-500">{{ $package->created_at->format('d/m/y H:i') }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2 text-xs min-w-[250px] flex-1">
                    <div class="text-right">
                        <p class="font-medium text-gray-800 truncate">{{ $package->supplier_data['name'] ?? 'N/A' }}</p>
                        <p class="text-gray-500">{{ $package->delegationFrom->name ?? 'N/A' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    <div>
                        <p class="font-medium text-gray-800 truncate">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                        <p class="text-gray-500">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="min-w-[120px] text-right">
                    <p class="text-base font-bold text-emerald-600 whitespace-nowrap">{{ number_format($package->cod_amount, 2) }} DT</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                        {{ match($package->status) {
                            'CREATED' => 'bg-gray-100 text-gray-700',
                            'AVAILABLE' => 'bg-blue-100 text-blue-700',
                            'PICKED_UP' => 'bg-indigo-100 text-indigo-700',
                            'DELIVERED' => 'bg-green-100 text-green-700',
                            'RETURNED' => 'bg-orange-100 text-orange-700',
                            'REFUSED' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        } }}
                    ">{{ $package->status }}</span>

                    <div class="flex items-center space-x-1">
                        <a href="{{ route('client.packages.show', $package) }}" class="p-1.5 text-gray-500 hover:bg-gray-200 rounded" title="Détails"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                        
                        <a href="{{ route('public.track.package', $package->package_code) }}" target="_blank" class="p-1.5 text-gray-500 hover:bg-gray-200 rounded" title="Suivre le colis en ligne">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </a>
                        <a href="{{ route('client.packages.print', $package) }}" target="_blank" class="p-1.5 text-gray-500 hover:bg-gray-200 rounded" title="Imprimer bon"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                        
                        @if(in_array($package->status, ['CREATED', 'AVAILABLE']))
                        <button onclick="deletePackage({{ $package->id }}, '{{ $package->package_code }}')" class="p-1.5 text-red-500 hover:bg-red-100 rounded" title="Supprimer"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($isPaginated && $packages->hasPages())
    <div class="bg-gray-50/50 px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-xs text-gray-600">
                Affichage de <span class="font-medium">{{ $packages->firstItem() }}</span> à <span class="font-medium">{{ $packages->lastItem() }}</span> sur <span class="font-medium">{{ $packages->total() }}</span> colis
            </div>
            <div>
                {{ $packages->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
    @endif

@else
    <div class="text-center py-16 px-4 bg-white">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
             <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $emptyMessage }}</h3>
        <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto">
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                Aucun colis ne correspond à vos filtres. Essayez de les modifier.
            @else
                Commencez par créer votre premier envoi pour le voir apparaître ici.
            @endif
        </p>
    </div>
@endif