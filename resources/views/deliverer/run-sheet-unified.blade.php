<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Run Sheet - {{ Auth::user()->name }}</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        .task-card {
            transition: all 0.3s ease;
        }
        
        .task-card:active {
            transform: scale(0.98);
        }
        
        .badge-priority {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <div x-data="runSheetApp()" x-init="init()" class="min-h-screen pb-20">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white sticky top-0 z-50 shadow-lg">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h1 class="text-xl font-bold">📋 Run Sheet</h1>
                        <p class="text-blue-100 text-sm">{{ Auth::user()->name }}</p>
                    </div>
                    <a href="{{ route('deliverer.menu') }}" class="bg-white/20 p-2 rounded-lg hover:bg-white/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div class="bg-white/20 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                        <div class="text-xs text-blue-100">Total</div>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{ $stats['livraisons'] }}</div>
                        <div class="text-xs text-blue-100">Livraisons</div>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{ $stats['pickups'] }}</div>
                        <div class="text-xs text-blue-100">Pickups</div>
                    </div>
                    <div class="bg-white/20 rounded-lg p-2">
                        <div class="text-2xl font-bold">{{ $stats['completed_today'] }}</div>
                        <div class="text-xs text-blue-100">Complétés</div>
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="px-4 pb-3 flex gap-2 overflow-x-auto">
                <button @click="filter = 'all'" 
                        :class="filter === 'all' ? 'bg-white text-blue-600' : 'bg-white/20 text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    Tous
                </button>
                <button @click="filter = 'livraison'" 
                        :class="filter === 'livraison' ? 'bg-white text-blue-600' : 'bg-white/20 text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    🚚 Livraisons
                </button>
                <button @click="filter = 'pickup'" 
                        :class="filter === 'pickup' ? 'bg-white text-blue-600' : 'bg-white/20 text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    📦 Pickups
                </button>
                <button @click="filter = 'retour'" 
                        :class="filter === 'retour' ? 'bg-white text-blue-600' : 'bg-white/20 text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    ↩️ Retours
                </button>
                <button @click="filter = 'paiement'" 
                        :class="filter === 'paiement' ? 'bg-white text-blue-600' : 'bg-white/20 text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    💰 Paiements
                </button>
            </div>
        </div>
        
        <!-- Messages -->
        @if(session('success'))
        <div class="mx-4 mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="mx-4 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
        @endif
        
        <!-- Liste des Tâches -->
        <div class="px-4 py-4 space-y-3">
            @forelse($tasks as $task)
            <div x-show="filter === 'all' || filter === '{{ $task['type'] }}'" 
                 x-transition
                 class="task-card">
                <a href="{{ route('deliverer.task.detail', $task['id']) }}" 
                   class="block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md">
                    
                    <!-- Header de la carte -->
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $task['icon'] }}</span>
                            <div>
                                <div class="font-bold text-gray-900">{{ $task['package_code'] }}</div>
                                <div class="text-xs text-gray-500">
                                    @if($task['type'] === 'livraison')
                                        Livraison Standard
                                    @elseif($task['type'] === 'pickup')
                                        Ramassage
                                    @elseif($task['type'] === 'retour')
                                        Retour Fournisseur
                                    @elseif($task['type'] === 'paiement')
                                        Paiement Espèce
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Badge priorité -->
                        @if($task['est_echange'])
                        <span class="badge-priority bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">
                            ÉCHANGE
                        </span>
                        @elseif($task['type'] === 'paiement')
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                            {{ number_format($task['payment_amount'], 3) }} DT
                        </span>
                        @elseif($task['cod_amount'] > 0)
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                            {{ number_format($task['cod_amount'], 3) }} DT
                        </span>
                        @endif
                    </div>
                    
                    <!-- Corps de la carte -->
                    <div class="px-4 py-3 space-y-2">
                        <!-- Destinataire -->
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $task['recipient_name'] }}</div>
                                <div class="text-sm text-gray-600">{{ $task['recipient_phone'] }}</div>
                            </div>
                        </div>
                        
                        <!-- Adresse -->
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-sm text-gray-700">{{ $task['recipient_address'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">📍 {{ $task['delegation'] }}</div>
                            </div>
                        </div>
                        
                        <!-- Infos spéciales -->
                        @if($task['type'] === 'retour' && isset($task['return_reason']))
                        <div class="bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-sm">
                            <span class="font-medium text-orange-800">Raison retour:</span>
                            <span class="text-orange-700">{{ $task['return_reason'] }}</span>
                        </div>
                        @endif
                        
                        @if($task['requires_signature'])
                        <div class="flex items-center gap-2 text-xs text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span class="font-medium">Signature obligatoire</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-4 py-2 bg-gray-50 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ \Carbon\Carbon::parse($task['date'])->format('d/m/Y H:i') }}</span>
                        <span class="text-blue-600 font-medium">Voir détails →</span>
                    </div>
                </a>
            </div>
            @empty
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune tâche</h3>
                <p class="text-gray-500">Vous n'avez aucune tâche assignée pour le moment.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Bouton Flottant Scanner -->
        <a href="{{ route('deliverer.scan.simple') }}" 
           class="fixed bottom-20 right-4 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 active:scale-95 transition-transform">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
        </a>
    </div>
    
    <script>
        function runSheetApp() {
            return {
                filter: 'all',
                tasks: @json($tasks),
                
                init() {
                    console.log('Run Sheet Unifié initialisé');
                    console.log('Total tâches:', this.tasks.length);
                }
            }
        }
    </script>
</body>
</html>
