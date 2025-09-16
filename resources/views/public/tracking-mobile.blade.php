<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi Colis - {{ $package->package_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media (max-width: 640px) {
            .mobile-optimized {
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-emerald-600 text-white p-6 text-center">
            <h1 class="text-xl font-bold">Al-Amena Delivery</h1>
            <p class="text-blue-100 text-sm">Suivi de Colis</p>
        </div>

        <!-- Code du colis -->
        <div class="p-6 bg-white border-b">
            <div class="text-center">
                <p class="text-sm text-gray-600">Code du Colis</p>
                <p class="text-2xl font-bold text-gray-900">{{ $package->package_code }}</p>
            </div>
        </div>

        <!-- Statut actuel -->
        <div class="p-6 bg-white border-b">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center
                    {{ $package->status === 'DELIVERED' ? 'bg-green-100' : '' }}
                    {{ $package->status === 'PICKED_UP' ? 'bg-indigo-100' : '' }}
                    {{ $package->status === 'AVAILABLE' ? 'bg-blue-100' : '' }}
                    {{ $package->status === 'RETURNED' ? 'bg-orange-100' : '' }}">
                    
                    @if($package->status === 'DELIVERED')
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($package->status === 'PICKED_UP')
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @elseif($package->status === 'AVAILABLE')
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>
                    @endif
                </div>
                
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    @if($package->status === 'DELIVERED')
                        Colis Livré ✅
                    @elseif($package->status === 'PICKED_UP')
                        En Cours de Livraison 🚚
                    @elseif($package->status === 'AVAILABLE')
                        En Attente de Collecte 📦
                    @elseif($package->status === 'RETURNED')
                        Colis Retourné 📮
                    @else
                        {{ $package->status }}
                    @endif
                </h2>
                
                <p class="text-sm text-gray-600">
                    Dernière mise à jour : {{ $package->updated_at->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>

        <!-- Détails du colis -->
        <div class="p-6 bg-white border-b">
            <h3 class="font-semibold text-gray-900 mb-4">Détails du Colis</h3>
            
            <!-- Pickup -->
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">📍 Collecte</p>
                <p class="text-gray-900">{{ $package->delegationFrom->name ?? 'N/A' }}</p>
                @if($package->supplier_data && is_array($package->supplier_data))
                    <p class="text-sm text-gray-600">{{ $package->supplier_data['name'] ?? '' }}</p>
                @endif
            </div>

            <!-- Livraison -->
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">🎯 Livraison</p>
                <p class="text-gray-900">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
            </div>

            <!-- COD -->
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-600">💰 Montant COD</p>
                <p class="text-lg font-bold text-emerald-600">{{ number_format($package->cod_amount, 3) }} DT</p>
            </div>

            <!-- Contenu -->
            <div>
                <p class="text-sm font-medium text-gray-600">📋 Contenu</p>
                <p class="text-gray-900">{{ $package->content_description }}</p>
            </div>
        </div>

        <!-- Historique -->
        @if($package->statusHistory && $package->statusHistory->count() > 0)
        <div class="p-6 bg-white">
            <h3 class="font-semibold text-gray-900 mb-4">Historique</h3>
            
            <div class="space-y-4">
                @foreach($package->statusHistory->take(5) as $history)
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $history->new_status }}
                        </p>
                        <p class="text-xs text-gray-600">
                            {{ $history->created_at->format('d/m/Y à H:i') }}
                        </p>
                        @if($history->notes)
                        <p class="text-xs text-gray-500 mt-1">{{ $history->notes }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="p-6 bg-gray-50 text-center">
            <p class="text-xs text-gray-500">
                Al-Amena Delivery - Service de livraison professionnel
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Pour toute question, contactez notre service client
            </p>
        </div>
    </div>

    <!-- Bouton de rafraîchissement -->
    <div class="fixed bottom-6 right-6">
        <button onclick="window.location.reload()" 
                class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>
</body>
</html>