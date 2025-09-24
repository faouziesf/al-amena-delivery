@extends('layouts.client')

@section('title', 'Mes Réclamations')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Mes Réclamations</h1>
            <p class="mt-1 text-sm text-purple-600">
                Suivez le traitement de vos réclamations
            </p>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
        <form method="GET" action="{{ route('client.complaints.index') }}" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filtre par statut -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
                    <select name="status" 
                            id="status"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-xl">
                        <option value="">Tous les statuts</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>En attente</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Résolu</option>
                        <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Refusé</option>
                    </select>
                </div>

                <!-- Filtre par type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" 
                            id="type"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-xl">
                        <option value="">Tous les types</option>
                        <option value="CHANGE_COD" {{ request('type') === 'CHANGE_COD' ? 'selected' : '' }}>Changement COD</option>
                        <option value="DELIVERY_DELAY" {{ request('type') === 'DELIVERY_DELAY' ? 'selected' : '' }}>Retard livraison</option>
                        <option value="REQUEST_RETURN" {{ request('type') === 'REQUEST_RETURN' ? 'selected' : '' }}>Demande de retour</option>
                        <option value="RETURN_DELAY" {{ request('type') === 'RETURN_DELAY' ? 'selected' : '' }}>Retard retour</option>
                        <option value="RESCHEDULE_TODAY" {{ request('type') === 'RESCHEDULE_TODAY' ? 'selected' : '' }}>Report aujourd'hui</option>
                        <option value="FOURTH_ATTEMPT" {{ request('type') === 'FOURTH_ATTEMPT' ? 'selected' : '' }}>4ème tentative</option>
                        <option value="CUSTOM" {{ request('type') === 'CUSTOM' ? 'selected' : '' }}>Personnalisé</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Filtrer
                    </button>
                    
                    @if(request()->hasAny(['status', 'type']))
                        <a href="{{ route('client.complaints.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </div>

            @if(request()->hasAny(['status', 'type']))
                <div class="text-sm text-purple-600 bg-purple-50 p-3 rounded-lg">
                    <strong>Filtres actifs:</strong>
                    @if(request('status'))
                        Statut: {{ ucfirst(strtolower(request('status'))) }}
                    @endif
                    @if(request('type'))
                        @if(request('status')), @endif
                        Type: {{ request('type') }}
                    @endif
                    • {{ $complaints->total() }} réclamation(s) trouvée(s)
                </div>
            @endif
        </form>
    </div>

    @if($complaints->count() > 0)
        <!-- Liste des réclamations -->
        <div class="space-y-4">
            @foreach($complaints as $complaint)
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    
                    <!-- Header avec informations principales -->
                    <div class="px-6 py-4 {{ $complaint->priority === 'URGENT' ? 'bg-red-50 border-b border-red-100' : 'bg-purple-50 border-b border-purple-100' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Icône de priorité -->
                                <div class="flex-shrink-0">
                                    @if($complaint->priority === 'URGENT')
                                        <div class="h-10 w-10 rounded-xl bg-red-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                        </div>
                                    @elseif($complaint->priority === 'HIGH')
                                        <div class="h-10 w-10 rounded-xl bg-orange-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 5.25h.008v.008H12V18z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="h-10 w-10 rounded-xl bg-purple-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Informations principales -->
                                <div>
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-purple-900">
                                            {{ $complaint->complaint_code }}
                                        </h3>
                                        
                                        <!-- Badge de statut -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($complaint->status)
                                                @case('PENDING') bg-yellow-100 text-yellow-800 @break
                                                @case('IN_PROGRESS') bg-blue-100 text-blue-800 @break
                                                @case('RESOLVED') bg-green-100 text-green-800 @break
                                                @case('REJECTED') bg-red-100 text-red-800 @break
                                            @endswitch">
                                            @switch($complaint->status)
                                                @case('PENDING') En attente @break
                                                @case('IN_PROGRESS') En cours @break
                                                @case('RESOLVED') Résolu @break
                                                @case('REJECTED') Refusé @break
                                            @endswitch
                                        </span>
                                        
                                        <!-- Badge de priorité -->
                                        @if($complaint->priority === 'URGENT')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                🚨 Urgent
                                            </span>
                                        @elseif($complaint->priority === 'HIGH')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                ⚡ Haute
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-600">
                                        <span>Créé le {{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                                        
                                        @if($complaint->package)
                                            <span>Colis: {{ $complaint->package->package_code }}</span>
                                        @endif
                                        
                                        <span class="capitalize">
                                            @switch($complaint->type)
                                                @case('CHANGE_COD') Changement COD @break
                                                @case('DELIVERY_DELAY') Retard livraison @break
                                                @case('REQUEST_RETURN') Demande retour @break
                                                @case('RETURN_DELAY') Retard retour @break
                                                @case('RESCHEDULE_TODAY') Report aujourd'hui @break
                                                @case('FOURTH_ATTEMPT') 4ème tentative @break
                                                @case('CUSTOM') Personnalisé @break
                                                @default {{ $complaint->type }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Commercial assigné -->
                            @if($complaint->assignedCommercial)
                                <div class="flex items-center text-sm text-gray-600">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                        <span class="text-xs font-semibold text-gray-600">
                                            {{ substr($complaint->assignedCommercial->name, 0, 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $complaint->assignedCommercial->name }}</p>
                                        <p class="text-xs text-gray-500">Commercial assigné</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Corps de la réclamation -->
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <!-- Description -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Description</h4>
                                <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg">
                                    {{ $complaint->description }}
                                </p>
                            </div>

                            <!-- Données additionnelles -->
                            @if($complaint->additional_data && count($complaint->additional_data) > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Détails supplémentaires</h4>
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        @foreach($complaint->additional_data as $key => $value)
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-blue-700 font-medium">
                                                    @switch($key)
                                                        @case('current_cod') COD actuel: @break
                                                        @case('requested_cod') COD demandé: @break
                                                        @default {{ $key }}: @break
                                                    @endswitch
                                                </span>
                                                <span class="text-blue-900">
                                                    @if(is_numeric($value))
                                                        {{ number_format($value, 3) }} DT
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Résolution -->
                            @if($complaint->status === 'RESOLVED' && $complaint->resolution_notes)
                                <div>
                                    <h4 class="text-sm font-medium text-green-900 mb-2">
                                        ✅ Résolution
                                        @if($complaint->resolved_at)
                                            <span class="font-normal text-xs text-green-600 ml-2">
                                                ({{ $complaint->resolved_at->format('d/m/Y H:i') }})
                                            </span>
                                        @endif
                                    </h4>
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                                        <p class="text-sm text-green-800">{{ $complaint->resolution_notes }}</p>
                                        
                                        @if($complaint->resolution_data && count($complaint->resolution_data) > 0)
                                            <div class="mt-3 space-y-2">
                                                @foreach($complaint->resolution_data as $key => $value)
                                                    <div class="flex justify-between items-center text-xs">
                                                        <span class="text-green-700">{{ $key }}:</span>
                                                        <span class="text-green-900 font-medium">{{ $value }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Rejet -->
                            @if($complaint->status === 'REJECTED' && $complaint->resolution_notes)
                                <div>
                                    <h4 class="text-sm font-medium text-red-900 mb-2">
                                        ❌ Réclamation refusée
                                        @if($complaint->resolved_at)
                                            <span class="font-normal text-xs text-red-600 ml-2">
                                                ({{ $complaint->resolved_at->format('d/m/Y H:i') }})
                                            </span>
                                        @endif
                                    </h4>
                                    <div class="bg-red-50 border border-red-200 p-4 rounded-lg">
                                        <p class="text-sm text-red-800">{{ $complaint->resolution_notes }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Actions disponibles -->
                            <div class="flex items-center justify-between pt-4 border-t border-purple-100">
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>ID: {{ $complaint->id }}</span>
                                    @if($complaint->package)
                                        <a href="{{ route('client.packages.show', $complaint->package) }}" 
                                           class="text-purple-600 hover:text-purple-800">
                                            Voir le colis →
                                        </a>
                                    @endif
                                </div>
                                
                                @if($complaint->status === 'PENDING')
                                    <div class="text-right">
                                        <p class="text-xs text-yellow-600">En attente de prise en charge</p>
                                    </div>
                                @elseif($complaint->status === 'IN_PROGRESS')
                                    <div class="text-right">
                                        <p class="text-xs text-blue-600">En cours de traitement</p>
                                        @if($complaint->assignedCommercial)
                                            <p class="text-xs text-gray-500">par {{ $complaint->assignedCommercial->name }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($complaints->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white rounded-xl shadow-sm border border-purple-100 px-6 py-3">
                    {{ $complaints->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
        
    @else
        <!-- État vide -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-12 text-center">
            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                @if(request()->hasAny(['status', 'type']))
                    Aucune réclamation trouvée
                @else
                    Aucune réclamation
                @endif
            </h3>
            
            <p class="text-gray-500 mb-6">
                @if(request()->hasAny(['status', 'type']))
                    Aucune réclamation ne correspond à vos critères de recherche.
                    <a href="{{ route('client.complaints.index') }}" class="text-purple-600 hover:text-purple-800 font-medium">
                        Voir toutes les réclamations
                    </a>
                @else
                    Vous n'avez créé aucune réclamation. Les réclamations permettent de signaler des problèmes avec vos colis.
                @endif
            </p>

            @if(!request()->hasAny(['status', 'type']))
                <a href="{{ route('client.packages') }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-purple-700 transition ease-in-out duration-150">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    Voir mes colis
                </a>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Auto-submit form on select change
    document.getElementById('status').addEventListener('change', function() {
        this.form.submit();
    });
    
    document.getElementById('type').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush