@extends('layouts.client')

@section('title', 'Mes Colis')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Mes Colis</h1>
            <p class="mt-1 text-sm text-purple-600">
                Gérez et suivez tous vos envois
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.packages.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nouveau Colis
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filtres et Recherche -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
        <form method="GET" action="{{ route('client.packages') }}" class="space-y-4">
            
            <!-- Première ligne - Recherche et Statut -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Recherche par code -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Rechercher</label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               value="{{ request('search') }}"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Code colis...">
                    </div>
                </div>

                <!-- Filtre par statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                    <select name="status" 
                            id="status"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-xl">
                        <option value="">Tous les statuts</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="CREATED" {{ request('status') === 'CREATED' ? 'selected' : '' }}>Créé</option>
                        <option value="AVAILABLE" {{ request('status') === 'AVAILABLE' ? 'selected' : '' }}>Disponible</option>
                        <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Accepté</option>
                        <option value="PICKED_UP" {{ request('status') === 'PICKED_UP' ? 'selected' : '' }}>Collecté</option>
                        <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>Livré</option>
                        <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Payé</option>
                        <option value="RETURNED" {{ request('status') === 'RETURNED' ? 'selected' : '' }}>Retourné</option>
                    </select>
                </div>

                <!-- Filtre par date de début -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date de début</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from" 
                           value="{{ request('date_from') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <!-- Filtre par date de fin -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date de fin</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to" 
                           value="{{ request('date_to') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center justify-between">
                <div class="flex space-x-2">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Filtrer
                    </button>
                    
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('client.packages') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Réinitialiser
                        </a>
                    @endif
                </div>

                <div class="text-sm text-gray-500">
                    {{ $packages->total() }} colis au total
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des colis -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 overflow-hidden">
        @if($packages->count() > 0)
            <!-- En-tête du tableau -->
            <div class="px-6 py-4 border-b border-purple-100 bg-purple-50">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-purple-700 uppercase tracking-wider">
                    <div class="col-span-2">Code Colis</div>
                    <div class="col-span-3">Destinataire</div>
                    <div class="col-span-2">Délégations</div>
                    <div class="col-span-1">COD</div>
                    <div class="col-span-2">Statut</div>
                    <div class="col-span-1">Date</div>
                    <div class="col-span-1">Actions</div>
                </div>
            </div>

            <!-- Corps du tableau -->
            <div class="divide-y divide-purple-100">
                @foreach($packages as $package)
                    <div class="px-6 py-4 hover:bg-purple-50 transition-colors duration-200">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <!-- Code colis -->
                            <div class="col-span-2">
                                <div class="font-semibold text-purple-900">
                                    {{ $package->package_code }}
                                </div>
                                @if($package->notes)
                                    <div class="text-xs text-gray-500 mt-1" title="{{ $package->notes }}">
                                        {{ Str::limit($package->notes, 20) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Destinataire -->
                            <div class="col-span-3">
                                <div class="font-medium text-gray-900">
                                    {{ $package->recipient_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $package->recipient_phone }}
                                </div>
                                <div class="text-xs text-gray-400 truncate" title="{{ $package->recipient_address }}">
                                    {{ Str::limit($package->recipient_address, 40) }}
                                </div>
                            </div>

                            <!-- Délégations -->
                            <div class="col-span-2">
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-600">{{ $package->delegationFrom->name }}</span>
                                    <svg class="h-4 w-4 mx-1 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                    <span class="text-gray-600">{{ $package->delegationTo->name }}</span>
                                </div>
                            </div>

                            <!-- COD -->
                            <div class="col-span-1">
                                <div class="font-semibold text-gray-900">
                                    {{ number_format($package->cod_amount, 3) }} DT
                                </div>
                            </div>

                            <!-- Statut -->
                            <div class="col-span-2">
                                <x-client.package-status-badge :status="$package->status" />
                                
                                @if($package->delivery_attempts > 0)
                                    <div class="text-xs text-orange-600 mt-1">
                                        {{ $package->delivery_attempts }} tentative(s)
                                    </div>
                                @endif
                            </div>

                            <!-- Date -->
                            <div class="col-span-1">
                                <div class="text-sm text-gray-600">
                                    {{ $package->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $package->created_at->format('H:i') }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="col-span-1">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('client.packages.show', $package) }}" 
                                       class="text-purple-600 hover:text-purple-900 text-sm font-medium">
                                        Voir
                                    </a>
                                    
                                    @if($package->canBeComplained())
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('client.complaints.create', $package) }}" 
                                           class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                            Réclamer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- État vide -->
            <div class="px-6 py-12 text-center">
                <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun colis trouvé</h3>
                
                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <p class="text-gray-500 mb-4">
                        Essayez de modifier vos critères de recherche ou
                        <a href="{{ route('client.packages') }}" class="text-purple-600 hover:text-purple-800 font-medium">
                            réinitialisez les filtres
                        </a>
                    </p>
                @else
                    <p class="text-gray-500 mb-4">
                        Vous n'avez pas encore créé de colis.
                    </p>
                @endif

                <a href="{{ route('client.packages.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Créer votre premier colis
                </a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($packages->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="bg-white rounded-xl shadow-sm border border-purple-100 px-6 py-3">
                {{ $packages->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Auto-submit form on select change
    document.getElementById('status').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush