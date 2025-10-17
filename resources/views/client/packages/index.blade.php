@extends('layouts.client')

@section('title', 'Mes Colis')

@section('content')
<div class="min-h-screen bg-gray-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6" x-data="packagesApp()">
    <!-- Mobile Header Actions -->
    <div class="lg:hidden bg-white border-b border-gray-200 px-4 py-2.5 space-y-2.5">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900">📦 Mes Colis</h2>
            <button @click="showFilters = !showFilters" 
                    class="flex items-center space-x-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg touch-active transition-smooth">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-sm font-medium">Filtres</span>
            </button>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex space-x-2">
            <a href="{{ route('client.packages.create') }}" 
               class="flex-1 flex items-center justify-center space-x-1.5 px-3 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg touch-active transition-smooth shadow-md text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="font-medium">Nouveau</span>
            </a>
            <a href="{{ route('client.packages.create-fast') }}" 
               class="flex-1 flex items-center justify-center space-x-1.5 px-3 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg touch-active transition-smooth shadow-md text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="font-medium">Rapide</span>
            </a>
            <a href="{{ route('client.packages.import.csv') }}" 
               class="flex-1 flex items-center justify-center space-x-1.5 px-3 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg touch-active transition-smooth shadow-md text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="font-medium">Import CSV</span>
            </a>
        </div>
        
        <!-- Template Download Button -->
        <a href="{{ route('client.packages.import.template') }}" 
           class="flex items-center justify-center space-x-2 px-4 py-2 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 border-2 border-purple-300 rounded-lg hover:from-purple-200 hover:to-pink-200 touch-active transition-smooth text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>📥 Télécharger le Template CSV</span>
        </a>
    </div>

    <!-- Desktop Header -->
    <div class="hidden lg:block bg-white border-b border-gray-200 px-6 py-3">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">📦 Mes Colis</h1>
                    <p class="text-sm text-gray-600 mt-0.5">Gérez et suivez tous vos envois</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('client.packages.create') }}" 
                       class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-smooth shadow-md text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="font-medium">Nouveau Colis</span>
                    </a>
                    <a href="{{ route('client.packages.create-fast') }}" 
                       class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-smooth shadow-md text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="font-medium">Création Rapide</span>
                    </a>
                    <a href="{{ route('client.packages.import.csv') }}" 
                       class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition-smooth shadow-md text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="font-medium">Import CSV</span>
                    </a>
                    <a href="{{ route('client.packages.import.template') }}" 
                       class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-smooth shadow-md text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span class="font-medium">Template CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-white border-b border-gray-200"
         style="display: none;">
        <div class="px-4 lg:px-6 py-4 max-w-7xl lg:mx-auto">
            <form method="GET" action="{{ route('client.packages.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Statut</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Tous les statuts</option>
                            <option value="CREATED" {{ request('status') === 'CREATED' ? 'selected' : '' }}>🆕 Créé</option>
                            <option value="AVAILABLE" {{ request('status') === 'AVAILABLE' ? 'selected' : '' }}>📋 Disponible</option>
                            <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>🚚 Collecté</option>
                            <option value="AT_DEPOT" {{ request('status') === 'AT_DEPOT' ? 'selected' : '' }}>🏭 Au Dépôt</option>
                            <option value="IN_TRANSIT" {{ request('status') === 'IN_TRANSIT' ? 'selected' : '' }}>🚛 En Transit</option>
                            <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>✅ Livré</option>
                            <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>💰 Payé</option>
                            <option value="RETURNED" {{ request('status') === 'RETURNED' ? 'selected' : '' }}>↩️ Retourné</option>
                        </select>
                    </div>

                    <!-- Delegation Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Délégation</label>
                        <select name="delegation" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Toutes</option>
                            @foreach(\App\Models\Delegation::all() as $delegation)
                                <option value="{{ $delegation->id }}" {{ request('delegation') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Recherche</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Code colis..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-smooth">
                            🔍 Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions (Toujours visible) -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 lg:px-6 py-3 max-w-7xl lg:mx-auto">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               x-model="allChecked" 
                               @change="toggleSelectAll()"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Tout sélectionner</span>
                    </label>
                    <span class="text-sm text-gray-500" x-text="`${selectedPackages.length} sélectionné(s)`"></span>
                </div>

                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <button type="button"
                            @click="bulkPrint()"
                            :disabled="selectedPackages.length === 0"
                            class="flex-1 sm:flex-none flex items-center justify-center space-x-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-smooth shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Imprimer BL</span>
                    </button>
                    <button type="button"
                            @click="bulkExport()"
                            :disabled="selectedPackages.length === 0"
                            class="flex-1 sm:flex-none flex items-center justify-center space-x-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-smooth shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Exporter</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Container -->
    <div class="max-w-7xl lg:mx-auto px-4 lg:px-6 py-4">
        <!-- Mobile: Card List -->
        <div class="lg:hidden space-y-3">
            @forelse($packages as $package)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden touch-active transition-smooth">
                    <div class="p-3">
                        <!-- Header Row -->
                        <div class="flex items-start space-x-2.5 mb-2.5">
                            <!-- Checkbox -->
                            <input type="checkbox" 
                                   x-model="selectedPackages" 
                                   value="{{ $package->id }}"
                                   class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 flex-shrink-0">
                            
                            <!-- Package Info -->
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('client.packages.show', $package) }}" 
                                   class="block text-sm font-bold text-indigo-600 hover:text-indigo-800 mb-1.5 truncate">
                                    {{ $package->package_code }}
                                </a>
                                <div class="flex items-center">
                                    @include('client.packages.partials.status-badge', ['status' => $package->status])
                                </div>
                            </div>
                        </div>

                        <!-- Package Details -->
                        <div class="space-y-1.5 text-sm ml-6">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="font-medium truncate">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1.5 border-t border-gray-100">
                                <span class="text-xs text-gray-500">{{ $package->created_at->format('d/m/Y H:i') }}</span>
                                <span class="text-base font-bold text-green-600">{{ number_format($package->cod_amount, 2) }} DT</span>
                            </div>
                        </div>

                        <!-- Actions Menu (en dessous) -->
                        <div class="mt-2.5 pt-2.5 border-t border-gray-100">
                            @include('client.packages.partials.actions-menu-mobile', ['package' => $package])
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun colis trouvé</h3>
                    <p class="text-gray-600 mb-4">Commencez par créer votre premier colis</p>
                    <a href="{{ route('client.packages.create') }}" 
                       class="inline-flex items-center space-x-2 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-smooth">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Créer un colis</span>
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Desktop: Table -->
        <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left w-12">
                                <input type="checkbox" 
                                       x-model="allChecked" 
                                       @change="toggleSelectAll()"
                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Destinataire</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Délégation</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">COD</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($packages as $package)
                        <tr class="hover:bg-gray-50 transition-smooth">
                            <td class="px-4 py-3">
                                <input type="checkbox" 
                                       x-model="selectedPackages" 
                                       value="{{ $package->id }}"
                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('client.packages.show', $package) }}"
                                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                    {{ $package->package_code }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $package->recipient_data['phone'] ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $package->delegationTo->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-green-600">
                                {{ number_format($package->cod_amount, 2) }} DT
                            </td>
                            <td class="px-4 py-3">
                                @include('client.packages.partials.status-badge', ['status' => $package->status])
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $package->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @include('client.packages.partials.actions-menu', ['package' => $package])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="text-6xl mb-4">📭</div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun colis trouvé</h3>
                                <p class="text-gray-600 mb-4">Commencez par créer votre premier colis</p>
                                <a href="{{ route('client.packages.create') }}" 
                                   class="inline-flex items-center space-x-2 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-smooth">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Créer un colis</span>
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="mt-4">
            {{ $packages->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function packagesApp() {
    return {
        showFilters: false,
        selectedPackages: [],
        allChecked: false,

        toggleSelectAll() {
            if (this.allChecked) {
                this.selectedPackages = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(input => input.value);
            } else {
                this.selectedPackages = [];
            }
        },

        bulkPrint() {
            if (this.selectedPackages.length === 0) {
                alert('Veuillez sélectionner au moins un colis.');
                return;
            }

            if (this.selectedPackages.length > 50) {
                alert('Vous ne pouvez imprimer que 50 bons maximum à la fois.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("client.packages.print.multiple") }}';
            form.target = '_blank';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name=csrf-token]').content;
            form.appendChild(csrfInput);

            this.selectedPackages.forEach(packageId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'package_ids[]';
                input.value = packageId;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        bulkExport() {
            if (this.selectedPackages.length === 0) {
                alert('Veuillez sélectionner au moins un colis.');
                return;
            }

            // Construire l'URL avec les IDs sélectionnés
            const packageIds = this.selectedPackages.join(',');
            const exportUrl = '{{ route("client.packages.export") }}' + '?package_ids=' + packageIds;
            
            // Ouvrir dans un nouvel onglet pour téléchargement
            window.open(exportUrl, '_blank');
        }
    }
}
</script>
@endsection
