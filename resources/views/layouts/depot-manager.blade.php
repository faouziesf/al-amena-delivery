<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Al-Amena Delivery') - Chef Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pale-orange': '#FED7AA',
                        'orange': {
                            50: '#FFF7ED', 100: '#FFEDD5', 200: '#FED7AA', 300: '#FDBA74',
                            400: '#FB923C', 500: '#F97316', 600: '#EA580C', 700: '#C2410C',
                            800: '#9A3412', 900: '#7C2D12'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Scrollbar Custom Styles */
        .scrollbar-thin {
            scrollbar-width: thin;
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #FFEDD5;
            border-radius: 2px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #FDBA74;
            border-radius: 2px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #FB923C;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar-hidden {
                transform: translateX(-100%);
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-orange-50 to-orange-100 min-h-screen" x-data="depotManagerApp()">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-orange-200 flex flex-col">
            <!-- Logo & Brand -->
            <div class="p-6 border-b border-orange-200 bg-gradient-to-r from-orange-200 to-orange-300 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-orange-800">Al-Amena</h1>
                        <p class="text-xs text-orange-600">Chef Dépôt</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-orange-300 scrollbar-track-orange-100">
                <!-- Dashboard -->
                <a href="{{ route('depot-manager.dashboard') }}"
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.dashboard') ? 'bg-orange-100 text-orange-700 shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- Gestion des Livreurs (déroulant) -->
                <div x-data="{ open: {{ request()->routeIs('depot-manager.deliverers.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.deliverers.*') ? 'bg-orange-100 text-orange-700 shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="font-medium">Livreurs</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('depot-manager.deliverers.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-orange-600 hover:bg-orange-50 rounded">Liste des livreurs</a>
                        <a href="{{ route('depot-manager.deliverers.create') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded">Nouveau livreur</a>
                        <a href="{{ route('depot-manager.deliverers.stats') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded">📊 Statistiques</a>
                    </div>
                </div>

                <!-- Gestion des Colis (déroulant) -->
                <div x-data="{ open: {{ request()->routeIs('depot-manager.packages.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.packages.*') ? 'bg-orange-100 text-orange-700 shadow-sm' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-600' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="font-medium">Colis</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('depot-manager.packages.all') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded">Tous les Colis</a>
                        <a href="{{ route('depot-manager.packages.payment-packages') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded">💰 Colis de Paiement</a>
                        <a href="{{ route('depot.returns.manage') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded">📦 Colis Retours</a>
                        <a href="{{ route('depot.scan.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded">🏭 Scan Dépôt</a>
                        <a href="{{ route('depot.returns.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-orange-600 hover:bg-orange-50 rounded">🔄 Scanner Retours</a>
                    </div>
                </div>

                <!-- Boîtes de Transit (déroulant) -->
                <div x-data="{ open: {{ request()->routeIs('depot-manager.crates.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.crates.*') ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="font-medium">Boîtes de Transit</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('depot-manager.crates.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded">Vue d'ensemble</a>
                        <a href="{{ route('depot-manager.crates.box-manager') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded">Gestionnaire de Boîtes</a>
                    </div>
                </div>

                <!-- Gestion des Paiements (déroulant) -->
                <div x-data="{ open: {{ request()->routeIs('depot-manager.payments.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.payments.*') ? 'bg-green-100 text-green-700 shadow-sm' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            <span class="font-medium">Paiements</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('depot-manager.payments.to-prep') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded">Demandes Espèce à Traiter</a>
                        <a href="{{ route('depot-manager.packages.payment-packages') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-green-600 hover:bg-green-50 rounded">Colis de Paiement Créés</a>
                    </div>
                </div>

                <!-- Colis Échanges (déroulant) -->
                <div x-data="{ open: {{ request()->routeIs('depot-manager.exchanges.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('depot-manager.exchanges.*') ? 'bg-red-100 text-red-700 shadow-sm' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="font-medium">🔄 Colis Échanges</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                        <a href="{{ route('depot-manager.exchanges.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            À traiter
                        </a>
                        <a href="{{ route('depot-manager.exchanges.history') }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded">📜 Historique</a>
                    </div>
                </div>

            </nav>

            <!-- User Profile -->
            <div class="p-4 border-t border-orange-200 bg-gradient-to-r from-orange-50 to-orange-100 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-orange-200 rounded-full flex items-center justify-center">
                        <span class="text-orange-800 font-bold text-sm">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'UD' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-orange-800 truncate">{{ auth()->user() ? auth()->user()->name : 'Utilisateur' }}</p>
                        <p class="text-xs text-orange-600 truncate">{{ (auth()->user() && auth()->user()->depot_name) ? auth()->user()->depot_name : 'Chef Dépôt' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-orange-600 hover:text-orange-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-orange-200 px-4 lg:px-6 py-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 truncate">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-gray-600 text-sm hidden sm:block">@yield('page-description', 'Gestion du dépôt')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Wallet Balance -->
                        <div class="hidden md:flex items-center space-x-4 text-sm">
                            <div class="flex items-center space-x-2 px-3 py-2 bg-green-100 text-green-700 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="font-semibold">Caisse: <span x-text="formatAmount(stats.depot_wallet_balance || 0) + ' DT'"></span></span>
                            </div>
                        </div>


                        <!-- Menu mobile et heure -->
                        <div class="flex items-center space-x-3">
                            <div class="lg:hidden">
                                <button @click="showMobileActions = !showMobileActions"
                                        class="inline-flex items-center px-3 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition-colors"
                                        title="Actions rapides">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-sm text-gray-500" x-text="currentTime"></div>
                        </div>
                    </div>
                </div>

            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-orange-50 to-orange-100 p-4 lg:p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function depotManagerApp() {
            return {
                stats: {
                    total_deliverers: 0,
                    delivered_today: 0,
                    packages_in_progress: 0,
                    cod_collected_today: 0,
                    urgent_packages: 0,
                    depot_wallet_balance: 0
                },
                currentTime: '',
                showMobileActions: false,

                init() {
                    this.loadStats();
                    this.updateTime();
                    setInterval(() => {
                        this.updateTime();
                        this.loadStats();
                    }, 30000); // Refresh every 30 seconds
                },

                async loadStats() {
                    try {
                        const response = await fetch('{{ route("depot-manager.dashboard.api.stats") }}');
                        const data = await response.json();
                        if (data.success) {
                            this.stats = data.stats;
                        }
                    } catch (error) {
                        console.error('Error loading stats:', error);
                    }
                },

                updateTime() {
                    this.currentTime = new Date().toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                formatAmount(amount) {
                    return new Intl.NumberFormat('fr-TN', {
                        minimumFractionDigits: 3,
                        maximumFractionDigits: 3
                    }).format(amount || 0);
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>