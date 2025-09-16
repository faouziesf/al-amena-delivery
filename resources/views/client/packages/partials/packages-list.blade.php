@php
$showBulkActions = $showBulkActions ?? false;
$emptyMessage = $emptyMessage ?? 'Aucun colis trouvé';
$emptyIcon = $emptyIcon ?? 'package';
$isPaginated = method_exists($packages, 'total');
$packagesCount = $isPaginated ? $packages->total() : $packages->count();
@endphp

@if($packages->isNotEmpty())
    @if($showBulkActions)
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2 group cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" @change="toggleSelectAll()" 
                               :checked="allSelected"
                               class="sr-only">
                        <div :class="allSelected ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300 group-hover:border-blue-500'" 
                             class="w-4 h-4 border-2 rounded transition-all duration-200 flex items-center justify-center">
                            <svg x-show="allSelected" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-xs font-medium text-gray-600 group-hover:text-blue-600">Tout sélectionner</span>
                </label>
            </div>
            
            <div class="flex items-center space-x-3 text-xs text-gray-500">
                <span class="font-semibold text-gray-700">{{ $packagesCount }}</span>
                <span>{{ Str::plural('colis', $packagesCount) }}</span>
                <button class="text-gray-400 hover:text-gray-600 transition-colors" title="Actualiser" onclick="window.location.reload()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="divide-y divide-gray-200 bg-white">
        @foreach($packages as $package)
        <div class="group hover:bg-gray-50 transition-colors duration-150 relative">
            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r-full
                {{ match($package->status) {
                    'DELIVERED' => 'bg-green-500',
                    'AVAILABLE' => 'bg-blue-500',
                    'PICKED_UP' => 'bg-indigo-500',
                    'RETURNED' => 'bg-orange-500',
                    'REFUSED' => 'bg-red-500',
                    default => 'bg-gray-400',
                } }}
            "></div>
            
            <div class="pl-5 pr-4 py-3 flex items-center flex-wrap justify-between gap-x-6 gap-y-3">

                <div class="flex items-center space-x-3 min-w-[220px]">
                    @if($showBulkActions)
                    <div class="flex-shrink-0">
                        <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </div>
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

                <div class="flex items-center space-x-4 min-w-[180px]">
                    <div>
                        <p class="text-sm text-gray-800 font-medium truncate">{{ $package->content_description }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            @if($package->is_fragile) <span class="text-xs font-semibold text-orange-600">Fragile</span> @endif
                            @if($package->requires_signature) <span class="text-xs font-semibold text-purple-600">Signature</span> @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-base font-bold text-emerald-600 whitespace-nowrap">{{ number_format($package->cod_amount, 2) }} DT</p>
                    </div>
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
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-xs text-gray-600">
                Affichage de <span class="font-medium">{{ $packages->firstItem() }}</span> à <span class="font-medium">{{ $packages->lastItem() }}</span> sur <span class="font-medium">{{ $packages->total() }}</span> colis
            </div>
            <div class="pagination-modern">
                {{ $packages->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif

@else
    <div class="text-center py-12 px-4 bg-white">
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
        <div class="flex items-center justify-center gap-3">
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('client.packages.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors">
                    Réinitialiser
                </a>
            @endif
            <a href="{{ route('client.packages.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Créer un colis
            </a>
        </div>
    </div>
@endif

<style>
.pagination-modern .pagination { @apply flex items-center space-x-1; }
.pagination-modern .page-link { @apply px-3 py-1.5 text-xs bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-md transition-colors; }
.pagination-modern .page-item.active .page-link { @apply bg-blue-600 border-blue-600 text-white font-semibold; }
.pagination-modern .page-item.disabled .page-link { @apply text-gray-400 cursor-not-allowed bg-gray-50; }
</style>

@push('scripts')
<script>
// Fonction de suppression (inchangée mais fonctionnera avec le nouveau design)
window.deletePackage = async function(packageId, packageCode) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le colis ${packageCode} ?`)) {
        return;
    }
    try {
        const response = await fetch(`/client/packages/${packageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (response.ok && data.success) {
            const packageRow = event.target.closest('.group');
            if (packageRow) {
                packageRow.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                packageRow.style.opacity = '0';
                packageRow.style.transform = 'translateX(20px)';
                setTimeout(() => window.location.reload(), 300);
            } else {
                window.location.reload();
            }
        } else {
            alert(data.message || 'Une erreur est survenue lors de la suppression.');
        }
    } catch (error) {
        alert('Erreur de connexion. Veuillez réessayer.');
    }
}
// La fonction toggleSelectAll reste la même
window.toggleSelectAll = function() {
    const alpineComponent = document.querySelector('[x-data*="packagesTabsManager"]');
    if (!alpineComponent || !window.Alpine) return console.warn('Composant Alpine.js non trouvé');
    const component = alpineComponent._x_dataStack[0];
    if (!component) return console.warn('Données Alpine.js non trouvées');
    
    const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selectedPackages"]');
    component.allSelected = !component.allSelected;
    if (component.allSelected) {
        component.selectedPackages = Array.from(checkboxes).map(cb => cb.value);
    } else {
        component.selectedPackages = [];
    }
}
</script>
@endpush