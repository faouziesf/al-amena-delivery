<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Al-Amena Delivery') }} - Superviseur</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Fix scroll issues */
        html, body {
            height: 100%;
            overflow-x: hidden;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #667eea, #764ba2);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #764ba2, #667eea);
        }

        /* Modern Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* Nav Items */
        .nav-item {
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.3);
            border-left: 4px solid #fff;
        }

        /* Dropdown Animation */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .dropdown-content.open {
            max-height: 500px;
        }

        /* Modern Button */
        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        /* Stats Card Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>

    @stack('styles')
</head>
<body class="h-full bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-600" 
      x-data="supervisorApp()" 
      x-init="init()">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen && isMobile"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 sidebar transform transition-transform duration-300 ease-in-out lg:translate-x-0 overflow-y-auto"
           :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">
        
        <!-- Logo -->
        <div class="flex items-center justify-center h-20 border-b border-white/20">
            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-white">
                    <div class="font-bold text-lg">Al-Amena</div>
                    <div class="text-xs opacity-75">Superviseur</div>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="px-4 py-6 space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('supervisor.dashboard') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Utilisateurs -->
            <div x-data="{ open: {{ request()->routeIs('supervisor.users.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg text-white">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="font-medium">Utilisateurs</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="dropdown-content ml-11 mt-2 space-y-1" :class="{ 'open': open }">
                    <a href="{{ route('supervisor.users.index') }}" class="block px-4 py-2 text-sm text-white/80 hover:text-white rounded-lg hover:bg-white/10 {{ request()->routeIs('supervisor.users.index') ? 'bg-white/20' : '' }}">
                        Liste des utilisateurs
                    </a>
                </div>
            </div>

            <!-- Colis -->
            <div x-data="{ open: {{ request()->routeIs('supervisor.packages.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="nav-item w-full flex items-center justify-between px-4 py-3 rounded-lg text-white">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="font-medium">Colis</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="dropdown-content ml-11 mt-2 space-y-1" :class="{ 'open': open }">
                    <a href="{{ route('supervisor.packages.index') }}" class="block px-4 py-2 text-sm text-white/80 hover:text-white rounded-lg hover:bg-white/10 {{ request()->routeIs('supervisor.packages.index') ? 'bg-white/20' : '' }}">
                        Tous les colis
                    </a>
                </div>
            </div>

            <!-- Délégations -->
            <a href="{{ route('supervisor.delegations.index') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.delegations.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="font-medium">Délégations</span>
            </a>

            <!-- Tickets -->
            <a href="{{ route('supervisor.tickets.index') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.tickets.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="font-medium">Support</span>
            </a>

            <!-- Rapports -->
            <a href="{{ route('supervisor.reports.index') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="font-medium">Rapports</span>
            </a>

            <!-- Système -->
            <a href="{{ route('supervisor.system.overview') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.system.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="font-medium">Système</span>
            </a>

            <!-- Paramètres -->
            <a href="{{ route('supervisor.settings.index') }}" 
               class="nav-item flex items-center px-4 py-3 rounded-lg text-white {{ request()->routeIs('supervisor.settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110 4m0-4v2m0-6V4"/>
                </svg>
                <span class="font-medium">Paramètres</span>
            </a>
        </nav>

        <!-- User Info -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/20">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <span class="text-purple-600 font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-white/70 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Header -->
        <header class="glass-card sticky top-0 z-30">
            <div class="flex items-center justify-between h-16 px-4 lg:px-8">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 rounded-lg text-white hover:bg-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <h1 class="text-lg lg:text-2xl font-bold text-white">@yield('title', 'Dashboard')</h1>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Time -->
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-1.5 glass-card rounded-lg">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-white" x-text="currentTime">--:--</span>
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-white hover:bg-white/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 lg:p-8 overflow-y-auto">
            <!-- Flash Messages -->
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 glass-card bg-green-500/20 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-white font-medium">{{ session('success') }}</p>
                        <button @click="show = false" class="ml-auto text-white/70 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mb-6 glass-card bg-red-500/20 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-white font-medium">{{ session('error') }}</p>
                        <button @click="show = false" class="ml-auto text-white/70 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="glass-card mt-auto">
            <div class="px-4 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <p class="text-sm text-white/70">&copy; {{ date('Y') }} Al-Amena Delivery. Tous droits réservés.</p>
                    <p class="text-sm text-white/70">Version 3.0.0</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function supervisorApp() {
            return {
                sidebarOpen: false,
                isMobile: window.innerWidth < 1024,
                currentTime: '',

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                    });
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
