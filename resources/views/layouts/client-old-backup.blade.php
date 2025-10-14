<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#6366F1">
    <title>@yield('title', 'Al-Amena') - Client</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Mobile-First Base Styles */
        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            padding-bottom: calc(70px + env(safe-area-inset-bottom));
            background: #F9FAFB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overscroll-behavior: none;
            -webkit-font-smoothing: antialiased;
        }

        /* Desktop: Sidebar padding */
        @media (min-width: 1024px) {
            body {
                padding-left: 280px;
                padding-bottom: 0;
            }
        }

        /* Safe Areas */
        .safe-top {
            padding-top: env(safe-area-inset-top);
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Prevent zoom on input focus (iOS) */
        input, select, textarea {
            font-size: 16px !important;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Hide scrollbar but keep functionality */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Animations */
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.2s ease-out;
        }

        /* Touch feedback */
        .touch-feedback:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        /* Bottom nav shadow */
        .bottom-nav-shadow {
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Card hover effect (desktop only) */
        @media (min-width: 1024px) {
            .card-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>
<body>
    <div x-data="{ 
        sidebarOpen: false,
        currentRoute: '{{ Route::currentRouteName() }}',
        userBalance: {{ Auth::user()->wallet->balance ?? 0 }}
    }">
        
        <!-- MOBILE: Top Header -->
        <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-gradient-to-r from-indigo-600 to-purple-600 text-white safe-top">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = true" class="p-2 -ml-2 touch-feedback">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold">Al-Amena</h1>
                        <p class="text-xs opacity-90">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <a href="{{ route('client.wallet.index') }}" class="flex items-center space-x-2 bg-white/20 px-3 py-1.5 rounded-full touch-feedback">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold" x-text="userBalance.toFixed(3) + ' DT'"></span>
                </a>
            </div>
        </header>

        <!-- MOBILE: Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="lg:hidden fixed inset-0 bg-black/50 z-50"
             style="display: none;">
        </div>

        <!-- MOBILE: Sidebar Drawer -->
        <aside x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               @click.away="sidebarOpen = false"
               class="lg:hidden fixed top-0 left-0 bottom-0 w-80 max-w-[85vw] bg-white z-50 overflow-y-auto safe-top safe-bottom"
               style="display: none;">
            
            <!-- Sidebar Header -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Menu</h2>
                    <button @click="sidebarOpen = false" class="p-2 hover:bg-white/20 rounded-lg touch-feedback">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-xl font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ Auth::user()->name }}</p>
                        <p class="text-sm opacity-90">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="p-4 space-y-1">
                @include('layouts.partials.client-menu-items')
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors touch-feedback">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- DESKTOP: Sidebar -->
        <aside class="hidden lg:block fixed top-0 left-0 bottom-0 w-[280px] bg-white border-r border-gray-200 overflow-y-auto">
            <!-- Desktop Header -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white p-6">
                <h1 class="text-2xl font-bold mb-1">Al-Amena</h1>
                <p class="text-sm opacity-90">Espace Client</p>
            </div>

            <!-- User Info -->
            <div class="p-4 border-b">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <a href="{{ route('client.wallet.index') }}" class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg hover:from-green-100 hover:to-emerald-100 transition-colors">
                    <span class="text-sm text-gray-600">Solde</span>
                    <span class="text-lg font-bold text-green-600" x-text="userBalance.toFixed(3) + ' DT'"></span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <nav class="p-4 space-y-1">
                @include('layouts.partials.client-menu-items')
            </nav>

            <!-- Desktop Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t bg-white">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="min-h-screen pt-[60px] lg:pt-0">
            @yield('content')
        </main>

        <!-- MOBILE: Bottom Navigation -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 bottom-nav-shadow safe-bottom z-30">
            <div class="grid grid-cols-5 h-[70px]">
                <a href="{{ route('client.dashboard') }}" 
                   class="flex flex-col items-center justify-center space-y-1 touch-feedback"
                   :class="currentRoute === 'client.dashboard' ? 'text-indigo-600' : 'text-gray-500'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-xs font-medium">Accueil</span>
                </a>

                <a href="{{ route('client.packages.index') }}" 
                   class="flex flex-col items-center justify-center space-y-1 touch-feedback"
                   :class="currentRoute.startsWith('client.packages') ? 'text-indigo-600' : 'text-gray-500'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-xs font-medium">Colis</span>
                </a>

                <a href="{{ route('client.packages.create') }}" 
                   class="flex flex-col items-center justify-center -mt-4 touch-feedback">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 mt-1">Nouveau</span>
                </a>

                <a href="{{ route('client.pickups.index') }}" 
                   class="flex flex-col items-center justify-center space-y-1 touch-feedback"
                   :class="currentRoute.startsWith('client.pickups') ? 'text-indigo-600' : 'text-gray-500'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-xs font-medium">Ramassages</span>
                </a>

                <a href="{{ route('client.wallet.index') }}" 
                   class="flex flex-col items-center justify-center space-y-1 touch-feedback"
                   :class="currentRoute.startsWith('client.wallet') ? 'text-indigo-600' : 'text-gray-500'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span class="text-xs font-medium">Wallet</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Toast Notifications -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="fixed top-20 lg:top-4 right-4 left-4 lg:left-auto lg:w-96 bg-green-500 text-white px-4 py-3 rounded-xl shadow-lg z-50 animate-slide-up">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="fixed top-20 lg:top-4 right-4 left-4 lg:left-auto lg:w-96 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg z-50 animate-slide-up">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @stack('scripts')
</body>
</html>
