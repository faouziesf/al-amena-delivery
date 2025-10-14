<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#6366F1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Al-Amena') - Client</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* === MOBILE FIRST APPROACH === */
        
        /* Base Reset */
        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #F3F4F6;
            overscroll-behavior-y: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Mobile: Top header + Bottom nav spacing */
        body {
            padding-top: 56px; /* Mobile header height */
            padding-bottom: calc(64px + env(safe-area-inset-bottom)); /* Bottom nav + safe area */
        }

        /* Desktop: Sidebar spacing */
        @media (min-width: 1024px) {
            body {
                padding-top: 0;
                padding-left: 280px;
                padding-bottom: 0;
            }
        }

        /* Prevent zoom on input (iOS) */
        input, select, textarea {
            font-size: 16px !important;
        }

        /* Hide scrollbar but keep functionality */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* Safe areas for notched devices */
        .safe-top {
            padding-top: env(safe-area-inset-top);
        }
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Touch feedback */
        .touch-active:active {
            transform: scale(0.96);
            opacity: 0.7;
        }

        /* Smooth transitions */
        .transition-smooth {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animate-slide-up {
            animation: slideInUp 0.3s ease-out;
        }

        .animate-slide-left {
            animation: slideInLeft 0.3s ease-out;
        }

        .animate-fade {
            animation: fadeIn 0.2s ease-out;
        }

        /* Custom scrollbar for desktop */
        @media (min-width: 1024px) {
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #F3F4F6;
            }
            ::-webkit-scrollbar-thumb {
                background: #D1D5DB;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #9CA3AF;
            }
        }
    </style>
</head>
<body x-data="{ 
    sidebarOpen: false, 
    currentRoute: '{{ Route::currentRouteName() }}',
    userBalance: {{ Auth::user()->wallet->balance ?? 0 }},
    userName: '{{ Auth::user()->name }}',
    userEmail: '{{ Auth::user()->email }}',
    userInitial: '{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}'
}" x-cloak>

    <!-- ========== MOBILE: Top Header ========== -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 safe-top">
        <div class="flex items-center justify-between px-4 h-14">
            <!-- Menu Button + Logo -->
            <div class="flex items-center space-x-3">
                <button @click="sidebarOpen = true" 
                        class="p-2 -ml-2 text-gray-700 hover:bg-gray-100 rounded-lg touch-active transition-smooth">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-base font-bold text-gray-900">Al-Amena</h1>
                    <p class="text-xs text-gray-500" x-text="userName.split(' ')[0]"></p>
                </div>
            </div>

            <!-- Wallet Balance -->
            <a href="{{ route('client.wallet.index') }}" 
               class="flex items-center space-x-2 bg-gradient-to-r from-green-50 to-emerald-50 px-3 py-1.5 rounded-full border border-green-200 touch-active transition-smooth">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold text-green-700" x-text="userBalance.toFixed(3)"></span>
                <span class="text-xs text-green-600">DT</span>
            </a>
        </div>
    </header>

    <!-- ========== MOBILE: Sidebar Overlay ========== -->
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

    <!-- ========== MOBILE: Sidebar Drawer ========== -->
    <aside x-show="sidebarOpen"
           x-transition:enter="transition ease-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="lg:hidden fixed top-0 left-0 bottom-0 w-[280px] bg-white z-50 overflow-y-auto scrollbar-hide safe-top safe-bottom"
           style="display: none;">
        
        <!-- Sidebar Header -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Menu</h2>
                <button @click="sidebarOpen = false" 
                        class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg touch-active transition-smooth">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white text-xl font-bold">
                    <span x-text="userInitial"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-white truncate" x-text="userName"></p>
                    <p class="text-sm text-white/80 truncate" x-text="userEmail"></p>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="p-3">
            @include('layouts.partials.client-menu')
        </nav>

        <!-- Logout -->
        <div class="p-3 border-t border-gray-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl touch-active transition-smooth">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-medium">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========== DESKTOP: Sidebar ========== -->
    <aside class="hidden lg:block fixed top-0 left-0 bottom-0 w-[280px] bg-white border-r border-gray-200 overflow-y-auto scrollbar-hide">
        <!-- Desktop Header -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 p-6">
            <h1 class="text-2xl font-bold text-white mb-1">Al-Amena</h1>
            <p class="text-sm text-white/80">Espace Client</p>
        </div>

        <!-- User Info Card -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg">
                    <span x-text="userInitial"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate text-sm" x-text="userName"></p>
                    <p class="text-xs text-gray-500 truncate" x-text="userEmail"></p>
                </div>
            </div>
            <a href="{{ route('client.wallet.index') }}" 
               class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl hover:from-green-100 hover:to-emerald-100 border border-green-200 transition-smooth">
                <span class="text-sm font-medium text-gray-700">Solde</span>
                <div class="flex items-center space-x-1">
                    <span class="text-lg font-bold text-green-600" x-text="userBalance.toFixed(3)"></span>
                    <span class="text-xs text-green-600">DT</span>
                </div>
            </a>
        </div>

        <!-- Desktop Menu -->
        <nav class="p-3 flex-1">
            @include('layouts.partials.client-menu')
        </nav>

        <!-- Desktop Logout -->
        <div class="p-3 border-t border-gray-200 bg-gray-50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-smooth">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-medium">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========== Main Content ========== -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- ========== MOBILE: Bottom Navigation ========== -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 safe-bottom">
        <div class="grid grid-cols-5 h-16">
            <!-- Home -->
            <a href="{{ route('client.dashboard') }}" 
               class="flex flex-col items-center justify-center space-y-1 touch-active transition-smooth"
               :class="currentRoute === 'client.dashboard' ? 'text-indigo-600' : 'text-gray-500'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-xs font-medium">Accueil</span>
            </a>

            <!-- Packages -->
            <a href="{{ route('client.packages.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 touch-active transition-smooth"
               :class="currentRoute.includes('packages') ? 'text-indigo-600' : 'text-gray-500'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-xs font-medium">Colis</span>
            </a>

            <!-- Create (Center FAB) -->
            <a href="{{ route('client.packages.create') }}" 
               class="flex flex-col items-center justify-center -mt-6 touch-active transition-smooth">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-indigo-500/50">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-500 mt-1">Nouveau</span>
            </a>

            <!-- Pickups -->
            <a href="{{ route('client.pickup-requests.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 touch-active transition-smooth"
               :class="currentRoute.includes('pickup') ? 'text-indigo-600' : 'text-gray-500'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-medium">Collectes</span>
            </a>

            <!-- Wallet -->
            <a href="{{ route('client.wallet.index') }}" 
               class="flex flex-col items-center justify-center space-y-1 touch-active transition-smooth"
               :class="currentRoute.includes('wallet') ? 'text-indigo-600' : 'text-gray-500'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span class="text-xs font-medium">Wallet</span>
            </a>
        </div>
    </nav>

    <!-- ========== Toast Notifications ========== -->
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 4000)" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-20 lg:top-4 lg:bottom-auto right-4 left-4 lg:left-auto lg:w-96 bg-green-500 text-white px-4 py-3 rounded-xl shadow-2xl z-50"
         style="display: none;">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show = false" class="flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 4000)" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-20 lg:top-4 lg:bottom-auto right-4 left-4 lg:left-auto lg:w-96 bg-red-500 text-white px-4 py-3 rounded-xl shadow-2xl z-50"
         style="display: none;">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="show = false" class="flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @stack('scripts')
</body>
</html>
