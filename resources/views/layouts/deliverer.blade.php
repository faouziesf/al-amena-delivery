<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Al-Amena Delivery') - Livreur</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#7c3aed">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Al-Amena Delivery">
    
    <!-- CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- jsQR Library UNIQUEMENT -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'purple': {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87'
                        },
                        'emerald': {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b'
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen overflow-x-hidden" x-data="delivererApp()">
    
    <!-- Fixed Top Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo & Status -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-emerald-600 to-green-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-emerald-700">Livreur</h1>
                        <div class="flex items-center space-x-1 text-xs">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" x-show="isOnline"></div>
                            <div class="w-2 h-2 bg-red-500 rounded-full" x-show="!isOnline"></div>
                            <span x-text="isOnline ? 'En ligne' : 'Hors ligne'" class="text-gray-600"></span>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-2">
                    <!-- Quick Scan Button -->
                    <button @click="$dispatch('open-scanner')" 
                            class="p-2 bg-emerald-100 text-emerald-600 rounded-lg hover:bg-emerald-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                    </button>

                    <!-- Wallet Balance -->
                    <div class="bg-purple-100 px-3 py-1 rounded-full">
                        <span class="text-sm font-semibold text-purple-700" x-text="formatAmount(walletBalance)"></span>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center p-1 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 bg-gradient-to-r from-emerald-600 to-green-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                            </div>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border z-50">
                            <div class="p-4 border-b bg-gradient-to-r from-emerald-50 to-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-600 to-green-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-sm text-gray-600">Livreur</p>
                                        <p class="text-xs text-emerald-600" x-text="'Solde: ' + formatAmount(walletBalance)"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-2">
                                <a href="{{ route('deliverer.profile.show') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon Profil
                                </a>
                            </div>
                            
                            <div class="p-2 border-t">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Se déconnecter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="pt-20 pb-20">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-5 h-16">
            <!-- Dashboard -->
            <a href="{{ route('deliverer.dashboard') }}" 
               class="nav-tab flex flex-col items-center justify-center space-y-1 {{ request()->routeIs('deliverer.dashboard') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-500 hover:text-emerald-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                </svg>
                <span class="text-xs font-medium">Accueil</span>
            </a>

            <!-- Pickups -->
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="nav-tab flex flex-col items-center justify-center space-y-1 relative {{ request()->routeIs('deliverer.pickups.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-500 hover:text-blue-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-xs font-medium">Pickups</span>
                <div class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                     x-show="stats.available_pickups > 0" x-text="stats.available_pickups"></div>
            </a>

            <!-- Scanner QR (Centre) -->
            <button @click="$dispatch('open-scanner')" 
                    class="nav-tab flex flex-col items-center justify-center space-y-1 text-gray-500 hover:text-emerald-600 relative">
                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </div>
                <span class="text-xs font-medium">Scanner</span>
            </button>

            <!-- Livraisons -->
            <a href="{{ route('deliverer.deliveries.index') }}" 
               class="nav-tab flex flex-col items-center justify-center space-y-1 relative {{ request()->routeIs('deliverer.deliveries.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-500 hover:text-orange-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span class="text-xs font-medium">Livraisons</span>
                <div class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                     x-show="stats.deliveries > 0" x-text="stats.deliveries"></div>
            </a>

            <!-- Plus -->
            <button @click="showMore = !showMore" 
                    class="nav-tab flex flex-col items-center justify-center space-y-1 text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5 transition-transform" :class="showMore ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-xs font-medium">Plus</span>
            </button>
        </div>
    </nav>

    <!-- Extended Menu Overlay -->
    <div x-show="showMore" @click.away="showMore = false" x-transition
         class="fixed inset-0 bg-black bg-opacity-50 z-40">
        <div x-show="showMore" x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
             class="fixed bottom-16 left-0 right-0 bg-white rounded-t-3xl shadow-xl max-h-96 overflow-y-auto">
            
            <div class="p-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Menu complet</h3>
                    <button @click="showMore = false" class="p-1 hover:bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('deliverer.pickups.mine') }}" 
                       class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors relative">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-700">Mes Pickups</span>
                        <div class="absolute -top-1 -right-1 bg-purple-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                             x-show="stats.my_pickups > 0" x-text="stats.my_pickups"></div>
                    </a>

                    <a href="{{ route('deliverer.returns.index') }}" 
                       class="flex flex-col items-center p-4 bg-red-50 rounded-xl hover:bg-red-100 transition-colors relative">
                        <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">Retours</span>
                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                             x-show="stats.returns > 0" x-text="stats.returns"></div>
                    </a>

                    <a href="{{ route('deliverer.wallet.index') }}" 
                       class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-700">Wallet</span>
                    </a>

                    <button @click="$dispatch('open-scanner')" 
                            class="flex flex-col items-center p-4 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                        <svg class="w-8 h-8 text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        <span class="text-sm font-medium text-emerald-700">Scanner QR</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner QR Component -->
    @include('components.scanner-qr-final')

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition class="fixed top-20 left-4 right-4 bg-emerald-500 text-white px-4 py-3 rounded-xl shadow-lg z-40 mx-auto max-w-md">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        function delivererApp() {
            return {
                showMore: false,
                isOnline: navigator.onLine,
                walletBalance: 0.000,
                stats: {
                    available_pickups: 0,
                    my_pickups: 0,
                    deliveries: 0,
                    returns: 0,
                    payments: 0
                },

                init() {
                    this.loadStats();
                    this.loadWallet();
                    
                    window.addEventListener('online', () => {
                        this.isOnline = true;
                    });
                    
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                    });

                    setInterval(() => {
                        if (this.isOnline) {
                            this.loadStats();
                            this.loadWallet();
                        }
                    }, 30000);
                },

                async loadStats() {
                    try {
                        const response = await fetch('/deliverer/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = {
                                available_pickups: data.available_pickups || 0,
                                my_pickups: data.my_pickups || 0,
                                deliveries: data.deliveries || 0,
                                returns: data.returns || 0,
                                payments: data.payments || 0
                            };
                        }
                    } catch (error) {
                        console.error('Erreur chargement stats:', error);
                    }
                },

                async loadWallet() {
                    try {
                        const response = await fetch('/deliverer/api/wallet/balance');
                        if (response.ok) {
                            const data = await response.json();
                            this.walletBalance = data.balance || 0;
                        }
                    } catch (error) {
                        console.error('Erreur chargement wallet:', error);
                    }
                },

                formatAmount(amount) {
                    return parseFloat(amount).toFixed(3) + ' DT';
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>