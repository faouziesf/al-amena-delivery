@extends('layouts.client')

@section('title', 'Colis ' . $package->package_code)

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-purple-900">{{ $package->package_code }}</h1>
                <x-client.package-status-badge :status="$package->status" size="md" />
            </div>
            <p class="mt-1 text-sm text-purple-600">
                Créé le {{ $package->created_at->format('d/m/Y à H:i') }}
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            @if($package->canBeComplained())
                <a href="{{ route('client.complaints.create', $package) }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Faire une réclamation
                </a>
            @endif
            
            <a href="{{ route('client.packages') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Retour à la liste
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Contenu principal -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Informations générales -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Informations Générales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Code du colis</dt>
                        <dd class="mt-1 text-lg font-semibold text-purple-900">{{ $package->package_code }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut actuel</dt>
                        <dd class="mt-1">
                            <x-client.package-status-badge :status="$package->status" size="md" />
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Montant COD</dt>
                        <dd class="mt-1 text-lg font-semibold text-green-600">{{ number_format($package->cod_amount, 3) }} DT</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tentatives de livraison</dt>
                        <dd class="mt-1">
                            <span class="text-lg font-semibold {{ $package->delivery_attempts > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                                {{ $package->delivery_attempts }}/3
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Contenu</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $package->content_description }}</dd>
                    </div>
                    
                    @if($package->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Commentaires</dt>
                            <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $package->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Expéditeur et Destinataire -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Expéditeur -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <h4 class="ml-3 font-semibold text-purple-900">Expéditeur</h4>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $package->sender_data['name'] }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $package->sender_data['phone'] }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Délégation</dt>
                            <dd class="mt-1 text-sm font-medium text-purple-600">{{ $package->delegationFrom->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $package->sender_data['address'] }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Destinataire -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <h4 class="ml-3 font-semibold text-purple-900">Destinataire</h4>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $package->recipient_data['name'] }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $package->recipient_data['phone'] }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Délégation</dt>
                            <dd class="mt-1 text-sm font-medium text-green-600">{{ $package->delegationTo->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $package->recipient_data['address'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des statuts -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Historique du Colis</h3>
                
                @if($package->statusHistory->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($package->statusHistory as $index => $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-purple-200" aria-hidden="true"></span>
                                        @endif
                                        
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full {{ $history->new_status === 'PAID' ? 'bg-green-500' : ($history->new_status === 'RETURNED' ? 'bg-red-500' : 'bg-purple-500') }} flex items-center justify-center ring-8 ring-white">
                                                    @if($history->new_status === 'PAID')
                                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @elseif($history->new_status === 'RETURNED')
                                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                        </svg>
                                                    @else
                                                        <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 6 6" aria-hidden="true">
                                                            <circle cx="3" cy="3" r="3" />
                                                        </svg>
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        Statut changé vers 
                                                        <x-client.package-status-badge :status="$history->new_status" size="xs" class="ml-1" />
                                                    </p>
                                                    
                                                    @if($history->notes)
                                                        <p class="mt-1 text-sm text-gray-500">{{ $history->notes }}</p>
                                                    @endif
                                                    
                                                    @if($history->changedByUser && $history->changedByUser->id !== auth()->id())
                                                        <p class="mt-1 text-xs text-gray-400">
                                                            Par {{ $history->changedByUser->name }} ({{ ucfirst(strtolower($history->changed_by_role)) }})
                                                        </p>
                                                    @endif
                                                </div>
                                                
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                    <time datetime="{{ $history->created_at->toISOString() }}">
                                                        {{ $history->created_at->format('d/m/Y H:i') }}
                                                    </time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="h-12 w-12 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Aucun historique disponible</p>
                    </div>
                @endif
            </div>

            <!-- Réclamations associées -->
            @if($package->complaints->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Réclamations</h3>
                    
                    <div class="space-y-4">
                        @foreach($package->complaints as $complaint)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $complaint->complaint_code }}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $complaint->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $complaint->status === 'RESOLVED' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $complaint->status === 'REJECTED' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst(strtolower($complaint->status)) }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-2">{{ $complaint->description }}</p>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                                    @if($complaint->assignedCommercial)
                                        <span>Assigné à {{ $complaint->assignedCommercial->name }}</span>
                                    @endif
                                </div>
                                
                                @if($complaint->resolution_notes)
                                    <div class="mt-3 p-3 bg-green-50 rounded-lg">
                                        <p class="text-sm text-green-800"><strong>Résolution:</strong> {{ $complaint->resolution_notes }}</p>
                                        @if($complaint->resolved_at)
                                            <p class="text-xs text-green-600 mt-1">Résolu le {{ $complaint->resolved_at->format('d/m/Y H:i') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Informations financières -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Informations Financières</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Montant COD</span>
                        <span class="font-semibold text-gray-900">{{ number_format($package->cod_amount, 3) }} DT</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Frais livraison</span>
                        <span class="font-medium text-gray-700">{{ number_format($package->delivery_fee, 3) }} DT</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Frais retour</span>
                        <span class="font-medium text-gray-700">{{ number_format($package->return_fee, 3) }} DT</span>
                    </div>
                    
                    <hr class="border-purple-100">
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Montant en escrow</span>
                        <span class="font-semibold text-red-600">{{ number_format($package->amount_in_escrow, 3) }} DT</span>
                    </div>
                    
                    @php
                        $deliveryCredit = $package->calculateDeliveryCredit();
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Si livré, vous recevez</span>
                        <span class="font-semibold text-green-600">{{ number_format($deliveryCredit, 3) }} DT</span>
                    </div>
                </div>
            </div>

            <!-- Livreur assigné -->
            @if($package->assignedDeliverer)
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Livreur Assigné</h3>
                    
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <span class="text-sm font-semibold text-purple-600">
                                {{ substr($package->assignedDeliverer->name, 0, 2) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $package->assignedDeliverer->phone }}</p>
                            @if($package->assigned_at)
                                <p class="text-xs text-purple-600">Assigné le {{ $package->assigned_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions rapides -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h3 class="text-lg font-semibold text-purple-900 mb-4">Actions</h3>
                
                <div class="space-y-3">
                    @if($package->canBeComplained())
                        <a href="{{ route('client.complaints.create', $package) }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-100 border border-orange-300 rounded-xl text-sm font-medium text-orange-700 hover:bg-orange-200 transition-colors">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            Faire une réclamation
                        </a>
                    @endif
                    
                    <button onclick="window.print()" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-purple-100 border border-purple-300 rounded-xl text-sm font-medium text-purple-700 hover:bg-purple-200 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.32 0H6.34m11.32 0l1.836-1.836a.75.75 0 011.061 1.061l-1.837 1.837" />
                        </svg>
                        Imprimer les détails
                    </button>
                    
                    <a href="{{ route('client.packages') }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25" />
                        </svg>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Actualisation automatique du statut toutes les 30 secondes
    setInterval(() => {
        fetch('/client/packages/{{ $package->id }}/status', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== '{{ $package->status }}') {
                // Recharger la page si le statut a changé
                window.location.reload();
            }
        })
        .catch(error => console.log('Erreur actualisation:', error));
    }, 30000);
</script>
@endpush