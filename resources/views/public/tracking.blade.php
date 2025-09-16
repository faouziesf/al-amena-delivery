<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi Colis {{ $package->package_code ?? '' }} - Al-Amena Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="description" content="Suivez votre colis Al-Amena Delivery en temps réel">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'/></svg>">
    
    <style>
        .tracking-timeline {
            position: relative;
        }
        
        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #3b82f6, #10b981);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 50px;
            margin-bottom: 30px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: white;
            border: 3px solid #d1d5db;
            z-index: 2;
        }
        
        .timeline-item.active::before {
            border-color: #3b82f6;
            background: #3b82f6;
        }
        
        .timeline-item.completed::before {
            border-color: #10b981;
            background: #10b981;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.5); }
            50% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.8); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-created { background: #f3f4f6; color: #374151; }
        .status-available { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #fce7f3; color: #be185d; }
        .status-picked_up { background: #e0e7ff; color: #3730a3; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-returned { background: #fed7aa; color: #c2410c; }
        .status-refused { background: #fecaca; color: #dc2626; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-emerald-50 min-h-screen" x-data="trackingApp()">
    
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Al-Amena Delivery</h1>
                        <p class="text-sm text-gray-600">Suivi de colis en temps réel</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-sm text-gray-500">Dernière mise à jour</div>
                    <div class="text-lg font-semibold text-gray-900" x-text="lastUpdated"></div>
                </div>
            </div>
        </div>
    </header>

    @if(isset($package))
    <!-- Contenu principal -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Informations du colis -->
        <div class="bg-white rounded-2xl shadow-lg border p-8 mb-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $package->package_code }}</h2>
                <div class="status-badge status-{{ strtolower($package->status) }} pulse-glow">
                    {{ $package->status }}
                </div>
                <p class="text-gray-600 mt-4">Colis créé le {{ $package->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            
            <!-- Informations de livraison -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Expédition -->
                <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        Expédition
                    </h3>
                    <div class="space-y-3">
                        @if($package->supplier_data && is_array($package->supplier_data))
                        <div>
                            <span class="text-sm text-gray-500">Expéditeur:</span>
                            <p class="font-medium">{{ $package->supplier_data['name'] ?? 'N/A' }}</p>
                        </div>
                        @endif
                        <div>
                            <span class="text-sm text-gray-500">Zone de départ:</span>
                            <p class="font-medium">{{ $package->delegationFrom->name ?? 'N/A' }}</p>
                        </div>
                        @if($package->pickup_address)
                        <div>
                            <span class="text-sm text-gray-500">Adresse de collecte:</span>
                            <p class="font-medium">{{ Str::limit($package->pickup_address, 50) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Livraison -->
                <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Livraison
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Destinataire:</span>
                            <p class="font-medium">{{ substr($package->recipient_data['name'] ?? 'N/A', 0, 1) }}***</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Zone de livraison:</span>
                            <p class="font-medium">{{ $package->delegationTo->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Adresse:</span>
                            <p class="font-medium">{{ Str::limit($package->recipient_data['address'] ?? 'N/A', 30) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informations supplémentaires -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <div class="text-2xl font-bold text-blue-600">{{ $package->delivery_attempts }}</div>
                    <div class="text-sm text-gray-600">Tentatives de livraison</div>
                </div>
                
                @if($package->package_weight)
                <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($package->package_weight, 1) }}kg</div>
                    <div class="text-sm text-gray-600">Poids du colis</div>
                </div>
                @endif
                
                <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-200">
                    <div class="text-2xl font-bold text-indigo-600">{{ $package->updated_at->diffForHumans() }}</div>
                    <div class="text-sm text-gray-600">Dernière mise à jour</div>
                </div>
            </div>
        </div>
        
        <!-- Timeline de suivi -->
        <div class="bg-white rounded-2xl shadow-lg border p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">Historique de Livraison</h3>
            
            <div class="tracking-timeline">
                @if($package->statusHistory && $package->statusHistory->count() > 0)
                    @foreach($package->statusHistory->reverse() as $index => $history)
                    <div class="timeline-item {{ $loop->first ? 'active' : 'completed' }}">
                        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                        @switch($history->new_status)
                                            @case('CREATED')
                                                📦 Colis créé
                                                @break
                                            @case('AVAILABLE')
                                                🔄 Disponible pour collecte
                                                @break
                                            @case('ACCEPTED')
                                                ✅ Accepté par le livreur
                                                @break
                                            @case('PICKED_UP')
                                                🚚 Colis collecté
                                                @break
                                            @case('DELIVERED')
                                                🎯 Colis livré
                                                @break
                                            @case('PAID')
                                                💰 Paiement effectué
                                                @break
                                            @case('RETURNED')
                                                ↩️ Colis retourné
                                                @break
                                            @case('REFUSED')
                                                ❌ Livraison refusée
                                                @break
                                            @default
                                                📋 {{ $history->new_status }}
                                        @endswitch
                                    </h4>
                                    <p class="text-gray-600 mb-3">{{ $history->created_at->format('d/m/Y à H:i') }}</p>
                                    @if($history->notes)
                                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">
                                            {{ Str::limit($history->notes, 150) }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="ml-4">
                                    <span class="status-badge status-{{ strtolower($history->new_status) }}">
                                        {{ $history->new_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="timeline-item active">
                        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">📦 Colis créé</h4>
                            <p class="text-gray-600">{{ $package->created_at->format('d/m/Y à H:i') }}</p>
                            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3 mt-3">
                                Votre colis a été créé et est en cours de traitement.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Instructions spéciales -->
        @if($package->hasSpecialRequirements())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mt-8">
            <h3 class="text-lg font-semibold text-amber-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Instructions Spéciales
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($package->getSpecialRequirementsListAttribute() as $requirement)
                    <div class="flex items-center space-x-3">
                        @if(str_contains($requirement, 'Fragile'))
                            <span class="text-2xl">🔸</span>
                        @elseif(str_contains($requirement, 'Signature'))
                            <span class="text-2xl">✍️</span>
                        @else
                            <span class="text-2xl">⚠️</span>
                        @endif
                        <span class="text-amber-700 font-medium">{{ $requirement }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Contact et aide -->
        <div class="bg-gray-50 rounded-2xl p-8 mt-8 text-center">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Besoin d'aide ?</h3>
            <p class="text-gray-600 mb-6">Notre équipe est là pour vous accompagner</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 border">
                    <div class="text-blue-600 text-3xl mb-3">📞</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Hotline</h4>
                    <p class="text-gray-600 text-sm">+216 XX XXX XXX</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 border">
                    <div class="text-green-600 text-3xl mb-3">💬</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Chat en ligne</h4>
                    <p class="text-gray-600 text-sm">Assistance immédiate</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 border">
                    <div class="text-purple-600 text-3xl mb-3">📧</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Email</h4>
                    <p class="text-gray-600 text-sm">support@al-amena-delivery.tn</p>
                </div>
            </div>
        </div>
        
    </main>
    
    @else
    
    <!-- Page de recherche -->
    <main class="max-w-2xl mx-auto px-4 py-16">
        <div class="bg-white rounded-2xl shadow-lg border p-8 text-center">
            <div class="w-20 h-20 bg-gradient-to-r from-blue-600 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Suivez votre colis</h2>
            <p class="text-gray-600 mb-8">Entrez votre code de suivi pour voir l'état de votre livraison</p>
            
            <form method="GET" action="{{ route('public.track.check') }}" class="space-y-6">
                <div>
                    <input type="text" name="package_code" value="{{ request('package_code') }}" 
                           placeholder="Ex: PKG_ABC12345_20240916"
                           class="w-full px-6 py-4 text-lg border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                
                <button type="submit" 
                        class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-emerald-600 hover:from-blue-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                    🔍 Rechercher mon colis
                </button>
            </form>
            
            @if(request('package_code') && !isset($package))
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-red-600">❌ Aucun colis trouvé avec ce code. Vérifiez votre saisie.</p>
                </div>
            @endif
            
            <div class="mt-8 text-sm text-gray-500">
                <p>💡 <strong>Astuce:</strong> Vous pouvez aussi scanner le QR code sur votre bon de livraison</p>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="mt-8 bg-blue-50 rounded-2xl p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Comment utiliser le suivi ?</h3>
            <div class="space-y-3 text-sm text-blue-800">
                <div class="flex items-start space-x-3">
                    <span class="text-blue-600">1️⃣</span>
                    <span>Récupérez votre code de suivi sur le bon de livraison</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-blue-600">2️⃣</span>
                    <span>Saisissez le code complet (ex: PKG_ABC12345_20240916)</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-blue-600">3️⃣</span>
                    <span>Consultez l'état de votre livraison en temps réel</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-blue-600">📱</span>
                    <span>Ou scannez directement le QR code avec votre téléphone</span>
                </div>
            </div>
        </div>
    </main>
    
    @endif
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Al-Amena Delivery</span>
                </div>
                <p class="text-gray-600 text-sm mb-4">Service de livraison professionnel en Tunisie</p>
                <div class="flex items-center justify-center space-x-6 text-sm text-gray-500">
                    <span>© 2024 Al-Amena Delivery</span>
                    <span>•</span>
                    <span>Suivi sécurisé</span>
                    <span>•</span>
                    <span>Support 24h/7j</span>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        function trackingApp() {
            return {
                lastUpdated: '',
                
                init() {
                    this.updateLastUpdated();
                    
                    // Mise à jour automatique de l'heure
                    setInterval(() => {
                        this.updateLastUpdated();
                    }, 60000); // Chaque minute
                    
                    // Auto-refresh de la page toutes les 5 minutes si on suit un colis
                    @if(isset($package))
                    setInterval(() => {
                        window.location.reload();
                    }, 300000); // 5 minutes
                    @endif
                },
                
                updateLastUpdated() {
                    const now = new Date();
                    this.lastUpdated = now.toLocaleString('fr-FR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>
</body>
</html>