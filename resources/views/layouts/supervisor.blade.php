<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Al-Amena Delivery') }} - Superviseur</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.2/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --supervisor-primary: #DC2626; /* red-600 */
            --supervisor-secondary: #B91C1C; /* red-700 */
            --supervisor-light: #FEE2E2; /* red-100 */
            --supervisor-dark: #7F1D1D; /* red-900 */
            --supervisor-accent: #F87171; /* red-400 */
        }

        .bg-supervisor-primary { background-color: var(--supervisor-primary); }
        .bg-supervisor-secondary { background-color: var(--supervisor-secondary); }
        .bg-supervisor-light { background-color: var(--supervisor-light); }
        .bg-supervisor-dark { background-color: var(--supervisor-dark); }
        .text-supervisor-primary { color: var(--supervisor-primary); }
        .text-supervisor-secondary { color: var(--supervisor-secondary); }
        .border-supervisor-primary { border-color: var(--supervisor-primary); }

        .supervisor-gradient {
            background: linear-gradient(135deg, var(--supervisor-primary) 0%, var(--supervisor-secondary) 100%);
        }

        .sidebar-gradient {
            background: linear-gradient(180deg, var(--supervisor-dark) 0%, var(--supervisor-secondary) 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-item {
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-right: 4px solid #fff;
        }

        .notification-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50" x-data="supervisorApp()">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 sidebar-gradient shadow-2xl transform transition-transform duration-300 ease-in-out" :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 border-b border-white/20">
                <a href="{{ route('supervisor.dashboard') }}" class="flex items-center space-x-3">
                    <div class="glass-effect p-2 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-white">
                        <div class="font-bold text-lg">Al-Amena</div>
                        <div class="text-xs text-red-200">SUPERVISEUR</div>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 px-4">
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('supervisor.dashboard') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v1H8V5z"></path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Utilisateurs -->
                    <a href="{{ route('supervisor.users.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.users.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span class="font-medium">Utilisateurs</span>
                    </a>

                    <!-- Colis -->
                    <a href="{{ route('supervisor.packages.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.packages.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="font-medium">Colis</span>
                    </a>

                    <!-- Délégations -->
                    <a href="{{ route('supervisor.delegations.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.delegations.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium">Délégations</span>
                    </a>

                    <!-- Tickets Support -->
                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.tickets.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="font-medium">Tickets Support</span>
                        <span class="ml-auto notification-badge bg-yellow-500 text-white text-xs px-2 py-1 rounded-full" x-show="stats.urgent_tickets > 0" x-text="stats.urgent_tickets"></span>
                    </a>

                    <!-- Rapports -->
                    <a href="{{ route('supervisor.reports.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="font-medium">Rapports</span>
                    </a>

                    <!-- Système -->
                    <a href="{{ route('supervisor.system.overview') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.system.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium">Système</span>
                    </a>

                    <!-- Paramètres -->
                    <a href="{{ route('supervisor.settings.index') }}"
                       class="nav-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('supervisor.settings.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        <span class="font-medium">Paramètres</span>
                    </a>
                </div>
            </nav>

            <!-- User Info -->
            <div class="absolute bottom-0 w-full p-4 border-t border-white/20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full glass-effect flex items-center justify-center">
                        <span class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-red-200 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-0 lg:ml-64">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-6 py-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page title -->
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                        <p class="text-sm text-gray-500">@yield('subtitle', 'Vue d\'ensemble du système')</p>
                    </div>

                    <!-- Header actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                                </svg>
                                <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 rounded-full flex items-center justify-center text-xs text-white notification-badge" x-show="stats.total_notifications > 0" x-text="stats.total_notifications"></span>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div class="p-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-3">Notifications</h3>
                                    <div class="space-y-3 max-h-64 overflow-y-auto">
                                        <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                            <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">Tickets urgents</p>
                                                <p class="text-xs text-gray-500">3 tickets nécessitent votre attention</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">Backup complété</p>
                                                <p class="text-xs text-gray-500">Il y a 1 heure</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <a href="#" class="text-sm text-supervisor-primary hover:text-supervisor-secondary">Voir toutes les notifications</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 bg-supervisor-primary rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon Profil</a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Se déconnecter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 ml-0 lg:ml-64">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <p class="text-sm text-gray-500">&copy; {{ date('Y') }} Al-Amena Delivery. Tous droits réservés.</p>
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        <span class="text-xs text-gray-500">Système Opérationnel</span>
                    </div>
                </div>
                <div class="text-xs text-gray-400">
                    Version 1.0.0 | Mode Superviseur
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function supervisorApp() {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                stats: {
                    urgent_tickets: 0,
                    total_notifications: 0
                },

                init() {
                    this.loadStats();
                    setInterval(() => this.loadStats(), 30000); // Refresh every 30 seconds
                },

                async loadStats() {
                    try {
                        const response = await fetch('/supervisor/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = {
                                urgent_tickets: data.tickets?.urgent || 0,
                                total_notifications: data.system?.unread_notifications || 0
                            };
                        }
                    } catch (error) {
                        console.error('Failed to load stats:', error);
                    }
                }
            }
        }

        // Responsive sidebar handling
        window.addEventListener('resize', function() {
            if (window.innerWidth < 1024) {
                Alpine.store('sidebarOpen', false);
            }
        });

        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>