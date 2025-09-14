@extends('layouts.client')

@section('title', 'Mes Demandes de Retrait')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Mes Demandes de Retrait</h1>
            <p class="mt-1 text-sm text-purple-600">
                Suivez le statut de vos demandes de retrait
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nouvelle Demande
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if($withdrawals->count() > 0)
        <!-- Liste des demandes -->
        <div class="space-y-4">
            @foreach($withdrawals as $withdrawal)
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <!-- Icône de statut -->
                                <div class="flex-shrink-0">
                                    @switch($withdrawal->status)
                                        @case('PENDING')
                                            <div class="h-12 w-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            @break
                                        @case('APPROVED')
                                        @case('IN_PROGRESS')
                                            <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            @break
                                        @case('COMPLETED')
                                            <div class="h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            @break
                                        @case('REJECTED')
                                            <div class="h-12 w-12 rounded-xl bg-red-100 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            @break
                                    @endswitch
                                </div>

                                <!-- Informations principales -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-semibold text-purple-900">
                                            {{ $withdrawal->request_code }}
                                        </h3>
                                        
                                        <!-- Badge de statut -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($withdrawal->status)
                                                @case('PENDING') bg-yellow-100 text-yellow-800 @break
                                                @case('APPROVED') bg-blue-100 text-blue-800 @break
                                                @case('IN_PROGRESS') bg-purple-100 text-purple-800 @break
                                                @case('COMPLETED') bg-green-100 text-green-800 @break
                                                @case('REJECTED') bg-red-100 text-red-800 @break
                                            @endswitch">
                                            @switch($withdrawal->status)
                                                @case('PENDING') En attente @break
                                                @case('APPROVED') Approuvé @break
                                                @case('IN_PROGRESS') En traitement @break
                                                @case('COMPLETED') Terminé @break
                                                @case('REJECTED') Refusé @break
                                            @endswitch
                                        </span>
                                        
                                        <!-- Badge méthode -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            @if($withdrawal->method === 'BANK_TRANSFER')
                                                🏦 Virement bancaire
                                            @else
                                                💵 Livraison espèces
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="mt-2 flex items-center space-x-6 text-sm text-gray-500">
                                        <span>Demandé le {{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                                        
                                        @if($withdrawal->processed_at)
                                            <span>Traité le {{ $withdrawal->processed_at->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Montant -->
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-purple-900">
                                        {{ number_format($withdrawal->amount, 3) }} DT
                                    </p>
                                    
                                    @if($withdrawal->status === 'PENDING')
                                        <p class="text-sm text-yellow-600">En attente de validation</p>
                                    @elseif($withdrawal->status === 'IN_PROGRESS' && $withdrawal->method === 'CASH_DELIVERY')
                                        <p class="text-sm text-blue-600">En cours de livraison</p>
                                    @elseif($withdrawal->status === 'COMPLETED')
                                        <p class="text-sm text-green-600">
                                            @if($withdrawal->method === 'BANK_TRANSFER')
                                                Virement effectué
                                            @else
                                                Livré avec succès
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Détails additionnels selon le statut et la méthode -->
                            @if($withdrawal->method === 'BANK_TRANSFER' && $withdrawal->bank_details)
                                <div class="mt-4 pt-4 border-t border-purple-100">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Détails bancaires</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">IBAN:</span>
                                            <span class="ml-2 font-mono">{{ $withdrawal->bank_details['iban'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Banque:</span>
                                            <span class="ml-2">{{ $withdrawal->bank_details['bank_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Bénéficiaire:</span>
                                            <span class="ml-2">{{ $withdrawal->bank_details['beneficiary'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($withdrawal->method === 'CASH_DELIVERY' && $withdrawal->assignedDeliverer)
                                <div class="mt-4 pt-4 border-t border-purple-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                <span class="text-xs font-semibold text-purple-600">
                                                    {{ substr($withdrawal->assignedDeliverer->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    Livreur: {{ $withdrawal->assignedDeliverer->name }}
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $withdrawal->assignedDeliverer->phone }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($withdrawal->delivery_receipt_code)
                                            <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">
                                                Code: {{ $withdrawal->delivery_receipt_code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Notes de traitement ou de rejet -->
                            @if($withdrawal->processing_notes)
                                <div class="mt-4 pt-4 border-t border-purple-100">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Notes de traitement</h4>
                                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                        {{ $withdrawal->processing_notes }}
                                    </p>
                                </div>
                            @endif

                            @if($withdrawal->rejection_reason)
                                <div class="mt-4 pt-4 border-t border-red-100">
                                    <h4 class="text-sm font-medium text-red-900 mb-2">Motif de refus</h4>
                                    <p class="text-sm text-red-700 bg-red-50 p-3 rounded-lg border border-red-200">
                                        {{ $withdrawal->rejection_reason }}
                                    </p>
                                </div>
                            @endif

                            <!-- Proof de livraison -->
                            @if($withdrawal->delivered_at && $withdrawal->delivery_proof)
                                <div class="mt-4 pt-4 border-t border-green-100">
                                    <h4 class="text-sm font-medium text-green-900 mb-2">Livraison confirmée</h4>
                                    <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                        <p class="text-sm text-green-700">
                                            <strong>Livré le:</strong> {{ $withdrawal->delivered_at->format('d/m/Y H:i') }}
                                        </p>
                                        @if(isset($withdrawal->delivery_proof['signature']))
                                            <p class="text-xs text-green-600 mt-1">
                                                ✅ Signature client confirmée
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Timeline simple pour les demandes en cours -->
                            @if(in_array($withdrawal->status, ['APPROVED', 'IN_PROGRESS']))
                                <div class="mt-4 pt-4 border-t border-purple-100">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Progression</h4>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center text-xs">
                                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                            <span class="ml-2 text-gray-600">Demande créée</span>
                                        </div>
                                        
                                        <div class="flex-1 h-0.5 bg-green-200"></div>
                                        
                                        <div class="flex items-center text-xs">
                                            <div class="h-2 w-2 rounded-full {{ $withdrawal->status === 'APPROVED' || $withdrawal->status === 'IN_PROGRESS' ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                            <span class="ml-2 text-gray-600">Approuvée</span>
                                        </div>
                                        
                                        <div class="flex-1 h-0.5 {{ $withdrawal->status === 'IN_PROGRESS' ? 'bg-green-200' : 'bg-gray-200' }}"></div>
                                        
                                        <div class="flex items-center text-xs">
                                            <div class="h-2 w-2 rounded-full {{ $withdrawal->status === 'IN_PROGRESS' ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
                                            <span class="ml-2 text-gray-600">En traitement</span>
                                        </div>
                                        
                                        <div class="flex-1 h-0.5 bg-gray-200"></div>
                                        
                                        <div class="flex items-center text-xs">
                                            <div class="h-2 w-2 rounded-full bg-gray-300"></div>
                                            <span class="ml-2 text-gray-600">Terminé</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($withdrawals->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white rounded-xl shadow-sm border border-purple-100 px-6 py-3">
                    {{ $withdrawals->links() }}
                </div>
            </div>
        @endif
        
    @else
        <!-- État vide -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-12 text-center">
            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.5 12h-15" />
            </svg>
            
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune demande de retrait</h3>
            <p class="text-gray-500 mb-6">
                Vous n'avez pas encore effectué de demande de retrait de fonds.
            </p>

            <a href="{{ route('client.wallet.withdrawal') }}" 
               class="inline-flex items-center px-6 py-3 bg-purple-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-purple-700 transition ease-in-out duration-150">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Faire ma première demande
            </a>
        </div>
    @endif
@endsection