@extends('layouts.deliverer-modern')

@section('title', 'Détail Colis')

@section('content')
<div class="px-4 pb-4">
    <div class="max-w-md mx-auto">
        <!-- Messages -->
        @if(session('success'))
        <div class="bg-green-500 text-white px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500 text-white px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-amber-500 text-white px-4 py-3 rounded-xl mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ session('warning') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-3xl shadow-xl p-6">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="text-6xl mb-3">📦</div>
                <h4 class="text-xl font-bold text-gray-800">{{ $package->package_code }}</h4>
                <span class="inline-block mt-2 px-4 py-1 
                    @if($package->status === 'DELIVERED') bg-green-100 text-green-700
                    @elseif($package->status === 'OUT_FOR_DELIVERY') bg-blue-100 text-blue-700
                    @elseif($package->status === 'PICKED_UP') bg-cyan-100 text-cyan-700
                    @elseif($package->status === 'UNAVAILABLE') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700
                    @endif
                    rounded-full text-sm font-semibold">
                    {{ $package->status }}
                </span>
                
                @if($package->est_echange)
                <span class="inline-block mt-2 px-4 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold animate-pulse">
                    🔄 ÉCHANGE
                </span>
                @endif
            </div>

            <!-- COD Amount -->
            @if($package->cod_amount > 0)
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6 text-center mb-6">
                    <div class="text-4xl font-bold text-green-600 mb-1">{{ number_format($package->cod_amount, 3) }} DT</div>
                    <div class="text-sm text-green-700">Montant à collecter (COD)</div>
                </div>
            @endif

            <!-- Sender Info (Fournisseur) -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-4 mb-4">
                <h5 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="text-2xl">🏭</span>
                    <span>Fournisseur / Expéditeur</span>
                </h5>
                
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">Nom:</span>
                        <span class="font-semibold text-gray-800 flex-1">{{ $package->sender_data['name'] ?? $package->sender->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">📞</span>
                        <a href="tel:{{ $package->sender_data['phone'] ?? '' }}" 
                           class="font-semibold text-green-600 hover:underline flex-1">
                            {{ $package->sender_data['phone'] ?? 'N/A' }}
                        </a>
                    </div>

                    @if(isset($package->sender_data['address']) && $package->sender_data['address'])
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">📍</span>
                        <span class="text-gray-700 flex-1">{{ $package->sender_data['address'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recipient Info -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-4 mb-6">
                <h5 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="text-2xl">👤</span>
                    <span>Destinataire</span>
                </h5>
                
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">Nom:</span>
                        <span class="font-semibold text-gray-800 flex-1">{{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">📞</span>
                        <a href="tel:{{ $package->recipient_data['phone'] ?? '' }}" 
                           class="font-semibold text-indigo-600 hover:underline flex-1">
                            {{ $package->recipient_data['phone'] ?? 'N/A' }}
                        </a>
                    </div>

                    @if(isset($package->recipient_data['phone2']) && $package->recipient_data['phone2'])
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">📱</span>
                        <a href="tel:{{ $package->recipient_data['phone2'] }}" 
                           class="font-semibold text-indigo-600 hover:underline flex-1">
                            {{ $package->recipient_data['phone2'] }}
                        </a>
                    </div>
                    @endif

                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">📍</span>
                        <span class="text-gray-700 flex-1">{{ $package->recipient_data['address'] ?? 'N/A' }}</span>
                    </div>

                    @if(isset($package->recipient_data['city']))
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">🏙️</span>
                        <span class="text-gray-700 flex-1">{{ $package->recipient_data['city'] ?? 'N/A' }}</span>
                    </div>
                    @endif

                    @if(isset($package->recipient_data['gouvernorat']))
                    <div class="flex items-start gap-3">
                        <span class="text-gray-400">🗺️</span>
                        <span class="text-gray-700 flex-1">{{ $package->recipient_data['gouvernorat'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Package Info -->
            <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                <h5 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="text-2xl">📋</span>
                    <span>Informations Colis</span>
                </h5>
                
                <div class="space-y-2 text-sm">
                    @if($package->content_description)
                    <div class="flex items-start gap-3">
                        <span class="text-gray-500">Contenu:</span>
                        <span class="text-gray-800 flex-1">{{ $package->content_description }}</span>
                    </div>
                    @endif

                    @if($package->notes)
                    <div class="flex items-start gap-3">
                        <span class="text-gray-500">Notes:</span>
                        <span class="text-gray-800 flex-1">{{ $package->notes }}</span>
                    </div>
                    @endif

                    @if($package->is_fragile)
                    <div class="flex items-center gap-2 text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="font-semibold">FRAGILE</span>
                    </div>
                    @endif

                    @if($package->requires_signature)
                    <div class="flex items-center gap-2 text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span class="font-semibold">Signature requise</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                @if($package->status === 'AVAILABLE' || $package->status === 'ACCEPTED' || $package->status === 'CREATED')
                    <form action="{{ route('deliverer.simple.pickup', $package) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            📦 Marquer comme Ramassé
                        </button>
                    </form>
                @endif

                @if($package->status === 'PICKED_UP' || $package->status === 'OUT_FOR_DELIVERY')
                    <form action="{{ route('deliverer.simple.deliver', $package) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                            ✅ Marquer comme Livré
                        </button>
                    </form>

                    <button @click="$dispatch('open-modal', 'unavailable-modal')" 
                            class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                        ⚠️ Client Indisponible
                    </button>

                    <button @click="$dispatch('open-modal', 'refused-modal')" 
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                        ❌ Refusé par le Client
                    </button>

                    <button @click="$dispatch('open-modal', 'scheduled-modal')" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                        📅 Reporter la Livraison
                    </button>
                @endif

                <!-- Appeler le client -->
                <a href="tel:{{ $package->recipient_data['phone'] ?? '' }}" 
                   class="flex items-center justify-center gap-2 w-full bg-blue-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Appeler le client
                </a>

                <a href="{{ route('deliverer.tournee') }}" 
                   class="block w-full bg-gray-100 text-gray-700 py-4 rounded-xl font-semibold text-center hover:bg-gray-200 transition-colors">
                    ← Retour à la tournée
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Client Indisponible -->
    <div x-data="{ open: false }" 
         @open-modal.window="if ($event.detail === 'unavailable-modal') open = true"
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 slide-up">
            <form action="{{ route('deliverer.simple.unavailable', $package) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Client Indisponible</h3>
                    </div>
                    
                    <p class="text-gray-600 mb-4">Veuillez indiquer la raison de l'indisponibilité du client.</p>
                    
                    <textarea name="comment" 
                              required
                              rows="4"
                              placeholder="Ex: Client absent, portes fermées, pas de réponse..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
                </div>
                
                <div class="flex gap-3 p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" 
                            @click="open = false"
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Refusé -->
    <div x-data="{ open: false }" 
         @open-modal.window="if ($event.detail === 'refused-modal') open = true"
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 slide-up">
            <form action="{{ route('deliverer.simple.refused', $package) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">❌</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Colis Refusé</h3>
                    </div>
                    
                    <p class="text-gray-600 mb-4">Veuillez indiquer la raison du refus du client.</p>
                    
                    <textarea name="comment" 
                              required
                              rows="4"
                              placeholder="Ex: Produit non conforme, client a changé d'avis..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"></textarea>
                </div>
                
                <div class="flex gap-3 p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" 
                            @click="open = false"
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reporter la Livraison -->
    <div x-data="{ open: false }" 
         @open-modal.window="if ($event.detail === 'scheduled-modal') open = true"
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 slide-up">
            <form action="{{ route('deliverer.simple.scheduled', $package) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">📅</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Reporter la Livraison</h3>
                    </div>
                    
                    <p class="text-gray-600 mb-4">Choisissez une nouvelle date de livraison (dans les 7 prochains jours).</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date de livraison</label>
                        <input type="date" 
                               name="scheduled_date" 
                               required
                               min="{{ date('Y-m-d', strtotime('tomorrow')) }}"
                               max="{{ date('Y-m-d', strtotime('+7 days')) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <textarea name="comment" 
                              required
                              rows="3"
                              placeholder="Raison du report (Ex: Client demande une livraison ultérieure)..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>
                
                <div class="flex gap-3 p-4 bg-gray-50 rounded-b-2xl">
                    <button type="button" 
                            @click="open = false"
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
@endsection
