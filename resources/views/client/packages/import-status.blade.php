@extends('layouts.client')

@section('title', 'Statut Import #' . $batch->batch_code)
@section('page-title', 'Import ' . $batch->batch_code)
@section('page-description', 'Suivi et résultats de l\'import en cours')

@section('header-actions')
<div class="flex items-center space-x-3 flex-col sm:flex-row">
    @if($batch->isCompleted() && $packages->count() > 0)
        <a href="{{ route('client.packages.print.batch', $batch->id) }}" 
           class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
            <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer tous les bons
        </a>
    @endif
    
    <a href="{{ route('client.packages.import.csv') }}" 
       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nouvel import
    </a>
    
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Liste des colis
    </a>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="importStatus()" x-init="init()">
    
    <!-- Résumé de l'import -->
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border mb-6 transition-all duration-300 hover:-translate-y-1">
        <div class="p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4 flex-col sm:flex-row">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $batch->filename }}</h2>
                    <p class="text-sm text-gray-500">Créé le {{ $batch->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-4 py-2 text-sm font-medium rounded-full {{ $batch->getStatusColorAttribute() }}">
                        {{ $batch->getStatusDisplayAttribute() }}
                    </span>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-2 md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
                <div class="bg-blue-50 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-blue-900">{{ $batch->total_rows }}</div>
                    <div class="text-sm text-blue-700">Total lignes</div>
                </div>
                
                <div class="bg-green-50 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-green-900">{{ $batch->successful_rows }}</div>
                    <div class="text-sm text-green-700">Créés avec succès</div>
                </div>
                
                <div class="bg-red-50 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-red-900">{{ $batch->failed_rows }}</div>
                    <div class="text-sm text-red-700">Échecs</div>
                </div>
                
                <div class="bg-purple-50 rounded-2xl p-4">
                    <div class="text-2xl font-bold text-purple-900">{{ $batch->getSuccessRateAttribute() }}%</div>
                    <div class="text-sm text-purple-700">Taux de réussite</div>
                </div>
            </div>

            <!-- Barre de progression -->
            @if($batch->isProcessing())
            <div class="mt-6">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-2 flex-col sm:flex-row">
                    <span>Progression</span>
                    <span>{{ $batch->processed_rows }} / {{ $batch->total_rows }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $batch->total_rows > 0 ? ($batch->processed_rows / $batch->total_rows) * 100 : 0 }}%"></div>
                </div>
            </div>
            @endif

            <!-- Temps de traitement -->
            @if($batch->isCompleted() || $batch->isFailed())
            <div class="mt-4 text-sm text-gray-600">
                <span class="font-medium">Temps de traitement:</span> {{ $batch->getFormattedProcessingTimeAttribute() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Erreurs d'import -->
    @if($batch->hasErrors())
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border mb-6 transition-all duration-300 hover:-translate-y-1">
        <div class="bg-red-50 px-4 sm:px-5 lg:px-6 py-4 border-b">
            <h3 class="font-semibold text-red-900">⚠️ Erreurs détectées ({{ $batch->failed_rows }})</h3>
        </div>
        
        <div class="p-4 sm:p-5 lg:p-6">
            <div class="space-y-4">
                @foreach($batch->getTopErrors(10) as $error)
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
                    <div class="flex items-start justify-between flex-col sm:flex-row">
                        <div class="flex-1 flex-col sm:flex-row">
                            <p class="font-medium text-red-900">{{ $error['error'] }}</p>
                            <p class="text-sm text-red-700 mt-1">
                                Occurrences: {{ $error['count'] }} • 
                                Lignes: {{ implode(', ', array_slice($error['rows'], 0, 5)) }}
                                @if(count($error['rows']) > 5) et {{ count($error['rows']) - 5 }} autres @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des colis créés -->
    @if($packages->count() > 0)
    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl border transition-all duration-300 hover:-translate-y-1">
        <div class="bg-gray-50 px-4 sm:px-5 lg:px-6 py-4 border-b flex items-center justify-between flex-col sm:flex-row">
            <h3 class="font-semibold text-gray-900">📦 Colis créés ({{ $packages->total() }})</h3>
            <div class="flex items-center space-x-2 text-sm text-gray-600 flex-col sm:flex-row">
                <button @click="toggleSelectAll()" 
                        class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition-colors">
                    <span x-show="!allSelected">Tout sélectionner</span>
                    <span x-show="allSelected">Tout désélectionner</span>
                </button>
                <button @click="printSelected()" :disabled="selectedPackages.length === 0"
                        :class="selectedPackages.length > 0 ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700' : 'bg-gray-100 text-gray-400'"
                        class="px-3 py-1 rounded transition-colors">
                    Imprimer sélectionnés (<span x-text="selectedPackages.length"></span>)
                </button>
            </div>
        </div>
        
        <div class="divide-y divide-gray-200">
            @foreach($packages as $package)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-center space-x-4 flex-col sm:flex-row">
                    <input type="checkbox" x-model="selectedPackages" value="{{ $package->id }}"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 flex-col sm:flex-row">
                        <!-- Code colis -->
                        <div>
                            <p class="font-medium text-gray-900">{{ $package->package_code }}</p>
                            <p class="text-sm text-gray-500">{{ $package->created_at->format('H:i') }}</p>
                        </div>
                        
                        <!-- Fournisseur -->
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $package->getFormattedSupplierAttribute() }}</p>
                            <p class="text-sm sm:text-xs text-gray-500">{{ $package->delegationFrom->name }}</p>
                        </div>
                        
                        <!-- Destinataire -->
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $package->recipient_data['name'] }}</p>
                            <p class="text-sm sm:text-xs text-gray-500">{{ $package->delegationTo->name }}</p>
                        </div>
                        
                        <!-- COD et Actions -->
                        <div class="flex items-center justify-between flex-col sm:flex-row">
                            <div>
                                <p class="text-sm font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                                <p class="text-sm sm:text-xs text-gray-500">{{ $package->content_description }}</p>
                            </div>
                            
                            <div class="flex items-center space-x-2 flex-col sm:flex-row">
                                <a href="{{ route('client.packages.show', $package) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm sm:text-xs">
                                    Voir
                                </a>
                                <a href="{{ route('client.packages.print', $package) }}" 
                                   class="text-emerald-600 hover:text-emerald-800 text-sm sm:text-xs">
                                    Bon
                                </a>
                                @if($package->status === 'CREATED' || $package->status === 'AVAILABLE')
                                <button @click="deletePackage({{ $package->id }}, '{{ $package->package_code }}')"
                                        class="text-red-600 hover:text-red-800 text-sm sm:text-xs">
                                    Suppr.
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="px-4 sm:px-5 lg:px-6 py-4 border-t">
            {{ $packages->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 sm:p-5 lg:p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <h3 class="text-lg font-medium text-yellow-800 mb-2">Aucun colis créé</h3>
        <p class="text-yellow-700">L'import n'a créé aucun colis avec succès.</p>
    </div>
    @endif

</div>

@push('scripts')
<script>
function importStatus() {
    return {
        selectedPackages: [],
        allSelected: false,
        refreshInterval: null,
        
        init() {
            // Auto-refresh si en cours de traitement
            @if($batch->isProcessing())
            this.refreshInterval = setInterval(() => {
                window.location.reload();
            }, 5000);
            @endif
        },
        
        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedPackages = [];
                this.allSelected = false;
            } else {
                this.selectedPackages = @json($packages->pluck('id')->toArray());
                this.allSelected = true;
            }
        },
        
        printSelected() {
            if (this.selectedPackages.length === 0) {
                alert('Aucun colis sélectionné');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("client.packages.print.multiple") }}';
            form.target = '_blank';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            this.selectedPackages.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'package_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },
        
        async deletePackage(packageId, packageCode) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer le colis ${packageCode} ?`)) {
                return;
            }
            
            try {
                const response = await fetch(`/client/packages/${packageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                alert('Erreur de connexion');
            }
        }
    }
}
</script>
@endpush
@endsection