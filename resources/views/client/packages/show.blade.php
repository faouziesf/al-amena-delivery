@extends('layouts.client')

@section('title', 'Colis ' . $package->package_code)
@section('page-title', 'Colis ' . $package->package_code)
@section('page-description', 'Détails et suivi de votre envoi')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>
    
    @if(in_array($package->status, ['DELIVERED', 'PICKED_UP', 'ACCEPTED', 'REFUSED']))
    <a href="{{ route('client.complaints.create', $package) }}" 
       class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        Créer une Réclamation
    </a>
    @endif

    <button @click="window.print()" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimer
    </button>
</div>
@endsection

@section('content')
<div x-data="packageDetailsData()" class="space-y-6">

    <!-- En-tête avec Statut -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-emerald-600 p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2">{{ $package->package_code }}</h1>
                    <p class="text-blue-100">Créé le {{ $package->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                        {{ $package->status === 'DELIVERED' || $package->status === 'PAID' ? 'bg-green-500 bg-opacity-20 text-green-100' : 
                           ($package->status === 'RETURNED' ? 'bg-red-500 bg-opacity-20 text-red-100' : 
                           'bg-orange-500 bg-opacity-20 text-orange-100') }}">
                        @switch($package->status)
                            @case('CREATED') Créé @break
                            @case('AVAILABLE') Disponible @break
                            @case('ACCEPTED') Accepté @break
                            @case('PICKED_UP') Collecté @break
                            @case('DELIVERED') Livré @break
                            @case('PAID') Payé @break
                            @case('RETURNED') Retourné @break
                            @case('REFUSED') Refusé @break
                            @default {{ $package->status }}
                        @endswitch
                    </div>
                    <p class="text-2xl font-bold mt-2">{{ number_format($package->cod_amount, 3) }} DT</p>
                    <p class="text-xs text-blue-100">Montant COD</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Colonne Principale -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Informations Itinéraire -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Itinéraire</h3>
                
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-900">{{ $package->delegationFrom->name }}</p>
                                <p class="text-sm text-gray-500">Délégation d'origine</p>
                                @if($package->delegationFrom->zone)
                                    <p class="text-xs text-gray-400">Zone: {{ $package->delegationFrom->zone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0 mx-4">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center justify-end">
                            <div class="mr-4 text-right">
                                <p class="font-medium text-gray-900">{{ $package->delegationTo->name }}</p>
                                <p class="text-sm text-gray-500">Délégation de destination</p>
                                @if($package->delegationTo->zone)
                                    <p class="text-xs text-gray-400">Zone: {{ $package->delegationTo->zone }}</p>
                                @endif
                            </div>
                            <div class="w-4 h-4 bg-emerald-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations Expéditeur et Destinataire -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Expéditeur -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Expéditeur</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Nom</p>
                            <p class="font-medium text-gray-900">{{ $package->sender_data['name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Téléphone</p>
                            <p class="font-medium text-gray-900">{{ $package->sender_data['phone'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Adresse</p>
                            <p class="font-medium text-gray-900">{{ $package->sender_data['address'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Destinataire -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Destinataire</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Nom</p>
                            <p class="font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Téléphone</p>
                            <p class="font-medium text-gray-900">{{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Adresse</p>
                            <p class="font-medium text-gray-900">{{ $package->recipient_data['address'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails du Colis -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails du Colis</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Description du contenu</p>
                            <p class="font-medium text-gray-900">{{ $package->content_description }}</p>
                        </div>
                        
                        @if($package->notes)
                        <div>
                            <p class="text-sm text-gray-500">Notes spéciales</p>
                            <p class="font-medium text-gray-900">{{ $package->notes }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Montant COD</p>
                            <p class="text-xl font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Frais de livraison</p>
                                <p class="font-medium">{{ number_format($package->delivery_fee, 3) }} DT</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Frais de retour</p>
                                <p class="font-medium">{{ number_format($package->return_fee, 3) }} DT</p>
                            </div>
                        </div>
                        
                        @if($package->amount_in_escrow > 0)
                        <div>
                            <p class="text-sm text-gray-500">Montant en escrow</p>
                            <p class="font-medium text-blue-600">{{ number_format($package->amount_in_escrow, 3) }} DT</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Livreur Assigné -->
            @if($package->assignedDeliverer)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Livreur Assigné</h3>
                
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr($package->assignedDeliverer->name, 0, 2) }}
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</p>
                        <p class="text-sm text-gray-500">{{ $package->assignedDeliverer->phone ?? 'Téléphone non disponible' }}</p>
                        @if($package->assigned_at)
                            <p class="text-xs text-gray-400">Assigné le {{ $package->assigned_at->format('d/m/Y à H:i') }}</p>
                        @endif
                    </div>
                </div>
                
                @if($package->delivery_attempts > 0)
                <div class="mt-4 p-3 bg-orange-50 rounded-lg">
                    <p class="text-sm text-orange-800">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $package->delivery_attempts }} tentative(s) de livraison
                    </p>
                </div>
                @endif
            </div>
            @endif

            <!-- Réclamations -->
            @if($package->complaints->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Réclamations</h3>
                
                <div class="space-y-4">
                    @foreach($package->complaints as $complaint)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $complaint->status === 'RESOLVED' ? 'bg-green-100 text-green-800' : 
                                       ($complaint->status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800') }}">
                                    {{ $complaint->getStatusDisplayAttribute() }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">{{ $complaint->getTypeDisplayAttribute() }}</span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-600">{{ $complaint->description }}</p>
                        
                        @if($complaint->resolution_notes)
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Résolution:</p>
                            <p class="text-sm text-gray-700">{{ $complaint->resolution_notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Modifications COD -->
            @if($package->codModifications->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Modifications COD</h3>
                
                <div class="space-y-3">
                    @foreach($package->codModifications as $modification)
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-900">
                                {{ number_format($modification->old_amount, 3) }} DT → {{ number_format($modification->new_amount, 3) }} DT
                            </p>
                            <p class="text-xs text-blue-700">{{ $modification->reason }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-blue-600">{{ $modification->created_at->format('d/m/Y H:i') }}</p>
                            @if($modification->modifiedByCommercial)
                                <p class="text-xs text-blue-500">Par: {{ $modification->modifiedByCommercial->name }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Suivi en Temps Réel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Suivi en Temps Réel</h3>
                
                <div class="space-y-4">
                    @php
                        $statuses = [
                            'CREATED' => ['Créé', 'Colis créé dans le système'],
                            'AVAILABLE' => ['Disponible', 'Prêt pour collecte'],
                            'ACCEPTED' => ['Accepté', 'Pris en charge par le livreur'],
                            'PICKED_UP' => ['Collecté', 'En cours de livraison'],
                            'DELIVERED' => ['Livré', 'Remis au destinataire'],
                            'PAID' => ['Payé', 'Transaction finalisée'],
                            'RETURNED' => ['Retourné', 'Retourné à l\'expéditeur']
                        ];
                        
                        $currentStatus = $package->status;
                        $statusKeys = array_keys($statuses);
                        $currentIndex = array_search($currentStatus, $statusKeys);
                    @endphp
                    
                    @foreach($statuses as $status => $details)
                        @php
                            $statusIndex = array_search($status, $statusKeys);
                            $isCompleted = $statusIndex <= $currentIndex;
                            $isCurrent = $status === $currentStatus;
                        @endphp
                        
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($isCompleted)
                                    <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                @elseif($isCurrent)
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center animate-pulse">
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                                @endif
                            </div>
                            
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium {{ $isCurrent ? 'text-blue-600' : ($isCompleted ? 'text-emerald-600' : 'text-gray-500') }}">
                                    {{ $details[0] }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $details[1] }}</p>
                            </div>
                        </div>
                        
                        @if(!$loop->last && $status !== 'RETURNED')
                            <div class="ml-4 w-px h-4 bg-gray-200"></div>
                        @endif
                    @endforeach
                </div>
                
                <!-- Actions Rapides -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Actions Rapides</h4>
                    <div class="space-y-2">
                        <button @click="refreshStatus()" 
                                class="w-full flex items-center justify-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Actualiser le statut
                        </button>
                        
                        @if(in_array($package->status, ['DELIVERED', 'PICKED_UP', 'ACCEPTED', 'REFUSED']))
                        <a href="{{ route('client.complaints.create', $package) }}" 
                           class="w-full flex items-center justify-center px-3 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            Créer une réclamation
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Historique Détaillé -->
            @if($package->statusHistory->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Historique Détaillé</h3>
                
                <div class="space-y-4">
                    @foreach($package->statusHistory->take(10) as $history)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $history->getPreviousStatusDisplayAttribute() }} → {{ $history->getNewStatusDisplayAttribute() }}
                            </p>
                            @if($history->notes)
                                <p class="text-xs text-gray-600 mt-1">{{ $history->notes }}</p>
                            @endif
                            <div class="flex items-center mt-1 text-xs text-gray-500">
                                <span>{{ $history->created_at->format('d/m/Y H:i') }}</span>
                                @if($history->changedByUser)
                                    <span class="ml-2">• Par: {{ $history->changedByUser->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($package->statusHistory->count() > 10)
                <div class="mt-4 text-center">
                    <button @click="showAllHistory = !showAllHistory" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        <span x-text="showAllHistory ? 'Voir moins' : 'Voir tout l\'historique'"></span>
                    </button>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
function packageDetailsData() {
    return {
        showAllHistory: false,
        
        async refreshStatus() {
            try {
                const response = await fetch(`/client/api/packages/{{ $package->id }}/status`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.status !== '{{ $package->status }}') {
                        showToast('Statut mis à jour! Rechargement de la page...', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast('Le statut est à jour', 'success');
                    }
                }
            } catch (error) {
                console.error('Erreur refresh statut:', error);
                showToast('Erreur lors de la mise à jour', 'error');
            }
        },
        
        init() {
            // Auto-refresh every 2 minutes for packages in progress
            @if(in_array($package->status, ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP']))
            setInterval(() => {
                this.refreshStatus();
            }, 120000);
            @endif
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .print-break { page-break-after: always; }
    body { font-size: 12px; }
}
</style>
@endpush
@endsection