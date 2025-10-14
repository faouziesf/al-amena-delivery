@extends('layouts.client-new')

@section('title', 'Mes Colis')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="packagesApp()">
    
    <!-- Header Section (Mobile & Desktop) -->
    <div class="bg-white border-b border-gray-200 sticky top-[60px] lg:top-0 z-20">
        <div class="px-4 lg:px-6 py-4">
            <!-- Title & Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-900">📦 Mes Colis</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $packages->total() }} colis au total</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.packages.create') }}" 
                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-sm touch-feedback">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau
                    </a>
                    <a href="{{ route('client.packages.create-fast') }}" 
                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all shadow-sm touch-feedback">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Rapide
                    </a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 overflow-x-auto hide-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
                <a href="{{ route('client.packages.index', ['tab' => 'all']) }}" 
                   class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors touch-feedback {{ $activeTab === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Tous ({{ $stats['total'] }})
                </a>
                <a href="{{ route('client.packages.index', ['tab' => 'pending']) }}" 
                   class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors touch-feedback {{ $activeTab === 'pending' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Disponibles ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('client.packages.index', ['tab' => 'in_progress']) }}" 
                   class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors touch-feedback {{ $activeTab === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    En cours ({{ $stats['in_progress'] }})
                </a>
                <a href="{{ route('client.packages.index', ['tab' => 'delivered']) }}" 
                   class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors touch-feedback {{ $activeTab === 'delivered' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Livrés ({{ $stats['delivered'] }})
                </a>
                <a href="{{ route('client.packages.index', ['tab' => 'returned']) }}" 
                   class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors touch-feedback {{ $activeTab === 'returned' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Retournés ({{ $stats['returned'] }})
                </a>
            </div>
        </div>
    </div>

    <!-- Filters (Collapsible) -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 lg:px-6">
            <button @click="filtersOpen = !filtersOpen" 
                    class="w-full flex items-center justify-between py-3 text-sm font-medium text-gray-700 touch-feedback">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtres
                </span>
                <svg class="w-5 h-5 transition-transform" :class="filtersOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="filtersOpen" 
                 x-collapse
                 class="pb-4">
                <form method="GET" action="{{ route('client.packages.index') }}" class="space-y-3">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Statut</label>
                            <select name="status" class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tous</option>
                                <option value="CREATED" {{ request('status') === 'CREATED' ? 'selected' : '' }}>Créé</option>
                                <option value="AVAILABLE" {{ request('status') === 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                                <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                                <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Délégation</label>
                            <select name="delegation" class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Toutes</option>
                                @foreach(\App\Models\Delegation::all() as $delegation)
                                    <option value="{{ $delegation->id }}" {{ request('delegation') == $delegation->id ? 'selected' : '' }}>
                                        {{ $delegation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Code colis..."
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors touch-feedback">
                                Appliquer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div x-show="selectedPackages.length > 0" 
         x-transition
         class="sticky top-[120px] lg:top-[60px] z-10 bg-indigo-600 text-white px-4 lg:px-6 py-3">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium" x-text="selectedPackages.length + ' colis sélectionné(s)'"></span>
            <div class="flex gap-2">
                <button @click="printSelected()" 
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors touch-feedback">
                    Imprimer
                </button>
                <button @click="selectedPackages = []" 
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors touch-feedback">
                    Annuler
                </button>
            </div>
        </div>
    </div>

    <!-- Packages List -->
    <div class="p-4 lg:p-6">
        @if($packages->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun colis</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore de colis dans cette catégorie.</p>
                <a href="{{ route('client.packages.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer mon premier colis
                </a>
            </div>
        @else
            <!-- Mobile: Cards -->
            <div class="lg:hidden space-y-3">
                @foreach($packages as $package)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden touch-feedback">
                    <div class="p-4">
                        <!-- Header: Checkbox + Status + Menu -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3">
                                <label class="flex items-center pt-1">
                                    <input type="checkbox" 
                                           x-model="selectedPackages" 
                                           value="{{ $package->id }}"
                                           class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </label>
                                <div>
                                    @include('client.packages.partials.status-badge', ['status' => $package->status])
                                </div>
                            </div>
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="p-2 hover:bg-gray-100 rounded-lg touch-feedback">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                                @include('client.packages.partials.actions-dropdown-mobile', ['package' => $package])
                            </div>
                        </div>

                        <!-- Code -->
                        <a href="{{ route('client.packages.show', $package) }}" 
                           class="block mb-3">
                            <h3 class="text-base font-bold text-indigo-600">{{ $package->package_code }}</h3>
                        </a>

                        <!-- Info Grid -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="font-medium truncate">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="truncate">{{ $package->recipient_data['phone'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ $package->delegationTo->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Footer: Date + COD -->
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-500">{{ $package->created_at->format('d/m/Y H:i') }}</span>
                            <span class="text-base font-bold text-green-600">{{ number_format($package->cod_amount, 2) }} DT</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Desktop: Table -->
            <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left w-12">
                                    <input type="checkbox" 
                                           @change="toggleAll($event)"
                                           class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Code</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Destinataire</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Délégation</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">COD</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($packages as $package)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" 
                                           x-model="selectedPackages" 
                                           value="{{ $package->id }}"
                                           class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
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
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <button @click="open = !open" class="p-2 hover:bg-gray-100 rounded-lg">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        @include('client.packages.partials.actions-dropdown-desktop', ['package' => $package])
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="mt-6">
            {{ $packages->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function packagesApp() {
    return {
        selectedPackages: [],
        filtersOpen: false,

        toggleAll(event) {
            if (event.target.checked) {
                this.selectedPackages = Array.from(document.querySelectorAll('input[type="checkbox"][value]'))
                    .map(input => input.value);
            } else {
                this.selectedPackages = [];
            }
        },

        printSelected() {
            if (this.selectedPackages.length === 0) {
                alert('Veuillez sélectionner au moins un colis.');
                return;
            }

            if (this.selectedPackages.length > 50) {
                alert('Vous ne pouvez imprimer que 50 colis maximum à la fois.');
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
        }
    }
}
</script>
@endpush
@endsection
