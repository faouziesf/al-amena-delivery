@extends('layouts.client')

@section('title', 'Colis ' . $package->package_code)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    
    <!-- Entête avec Gradient -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl">📦</span>
                    <h1 class="text-2xl font-bold">{{ $package->package_code }}</h1>
                </div>
                <p class="text-blue-100 text-sm">Créé le {{ $package->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <span class="px-4 py-2 bg-white/20 backdrop-blur rounded-full text-sm font-semibold">
                {{ $package->status }}
            </span>
        </div>
        
        <!-- Actions Rapides -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('client.packages.index') }}"
               class="inline-flex items-center px-3 py-2 bg-white/10 hover:bg-white/20 backdrop-blur text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Retour</span>
            </a>
            
            @if(isset($package->recipient_data['phone']))
            <a href="tel:{{ $package->recipient_data['phone'] }}"
               class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>Appeler</span>
            </a>
            @endif
            
            @php
                // Chercher les colis de retour associés
                $returnPackages = \App\Models\Package::where('original_package_id', $package->id)
                    ->where('package_type', 'RETURN')
                    ->get();
            @endphp
            
            @if($returnPackages->count() > 0)
            <a href="{{ route('client.returns.show-return-package', $returnPackages->first()->id) }}"
               class="inline-flex items-center px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span>↩️ Suivre Retour</span>
            </a>
            @endif

            @if(in_array($package->status, ['DELIVERED', 'PICKED_UP', 'ACCEPTED', 'REFUSED']))
            <a href="{{ route('client.complaints.create', $package) }}"
               class="inline-flex items-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span>Réclamation</span>
            </a>
            @endif
            
            <button onclick="window.print()" class="inline-flex items-center px-3 py-2 bg-white/10 hover:bg-white/20 backdrop-blur text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimer</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne Gauche - Détails -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Informations Destinataire -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Destinataire
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Nom</label>
                        <p class="font-semibold">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Téléphone</label>
                        <p class="font-semibold">{{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-500">Adresse</label>
                        <p class="font-semibold">{{ $package->recipient_data['address'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Ville</label>
                        <p class="font-semibold">{{ $package->recipient_data['city'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Gouvernorat</label>
                        <p class="font-semibold">{{ $package->delegationTo->governorate ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Informations Colis -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Détails du Colis
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Montant COD</label>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                    </div>
                    @if($package->assignedDeliverer)
                    <div>
                        <label class="text-xs text-gray-500">Livreur Assigné</label>
                        <p class="font-semibold">{{ $package->assignedDeliverer->name }}</p>
                    </div>
                    @endif
                    @if($package->est_echange)
                    <div class="col-span-2">
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">
                            🔄 Colis Échange
                        </span>
                    </div>
                    @endif
                    @if($package->notes)
                    <div class="col-span-2">
                        <label class="text-xs text-gray-500">Notes</label>
                        <p class="text-sm">{{ $package->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historique Complet -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique
                </h2>
                <div class="relative border-l-2 border-gray-200 ml-3 space-y-4">
                    @forelse($package->statusHistories()->orderBy('created_at', 'desc')->get() as $history)
                    <div class="ml-6 relative">
                        <span class="absolute -left-9 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs">
                            ✓
                        </span>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex justify-between items-start mb-1">
                                <p class="font-semibold text-sm">{{ $history->notes ?? 'Changement de statut' }}</p>
                                <span class="text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="px-2 py-0.5 bg-gray-200 rounded">{{ $history->previous_status }}</span>
                                →
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $history->new_status }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm ml-6">Aucun historique disponible</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Colonne Droite - Carte Statut -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6 sticky top-6">
                <h3 class="font-bold mb-4">Progression</h3>
                <div class="space-y-3">
                    @php
                        $statuses = [
                            'CREATED' => ['label' => 'Créé', 'icon' => '📝'],
                            'AVAILABLE' => ['label' => 'Disponible', 'icon' => '📦'],
                            'PICKED_UP' => ['label' => 'Collecté', 'icon' => '🚚'],
                            'OUT_FOR_DELIVERY' => ['label' => 'En livraison', 'icon' => '🛵'],
                            'DELIVERED' => ['label' => 'Livré', 'icon' => '✅'],
                            'PAID' => ['label' => 'Payé', 'icon' => '💰'],
                        ];
                        
                        $currentIndex = array_search($package->status, array_keys($statuses));
                    @endphp
                    
                    @foreach($statuses as $statusKey => $statusInfo)
                        @php
                            $index = array_search($statusKey, array_keys($statuses));
                            $isCompleted = $currentIndex !== false && $index <= $currentIndex;
                            $isCurrent = $statusKey === $package->status;
                        @endphp
                        <div class="flex items-center gap-3 {{ $isCompleted ? 'opacity-100' : 'opacity-30' }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center 
                                {{ $isCurrent ? 'bg-blue-600 ring-4 ring-blue-100' : ($isCompleted ? 'bg-green-500' : 'bg-gray-300') }}">
                                <span class="text-white text-sm">{{ $isCompleted ? '✓' : $statusInfo['icon'] }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">{{ $statusInfo['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
