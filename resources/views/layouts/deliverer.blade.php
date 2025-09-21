<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Al-Amena Livreur</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    
    <link rel="icon" type="image/png" href="/images/icons/icon-32x32.png">
    
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preload" href="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" as="script">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
    
    <script>
        // Configuration étendue de Tailwind CSS
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#F5F3FF', 100: '#EDE9FE', 200: '#DDD6FE', 300: '#C4B5FD',
                            400: '#A78BFA', 500: '#8B5CF6', 600: '#7C3AED', 700: '#6D28D9',
                            800: '#5B21B6', 900: '#4C1D95'
                        },
                        neutral: {
                            50: '#F8F9FA', 100: '#F1F3F5', 200: '#E9ECEF', 300: '#DEE2E6',
                            400: '#CED4DA', 500: '#ADB5BD', 600: '#868E96', 700: '#495057',
                            800: '#343A40', 900: '#212529'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Variables CSS pour les zones de sécurité (safe area) sur iOS */
        :root {
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
        }
        
        /* Styles de base */
        body {
            -webkit-tap-highlight-color: transparent;
        }
        .safe-top { padding-top: var(--safe-area-inset-top); }
        .safe-bottom { padding-bottom: var(--safe-area-inset-bottom); }
        .content-safe-top { margin-top: var(--safe-area-inset-top); }
        
        /* Amélioration de la barre de défilement */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #DDD6FE; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #C4B5FD; }
        
        /* Animation pour les notifications */
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }
        .notification-badge-pulse { animation: pulse-badge 1.5s infinite; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-neutral-50 font-sans antialiased text-neutral-800" x-data="delivererApp()" @online.window="setOnlineStatus(true)" @offline.window="setOnlineStatus(false)">
    
    <div class="fixed top-0 left-0 right-0 z-50 safe-top space-y-2 p-3">
        <div x-show="!isOnline" x-transition class="flex items-center justify-center space-x-2 bg-neutral-800 text-white p-2 rounded-lg shadow-md">
            <svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-sm font-medium">Mode hors ligne actif</span>
        </div>
        
        <div x-show="showInstallBanner" x-transition class="bg-white p-3 rounded-lg shadow-lg border border-neutral-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-primary-500 text-white w-10 h-10 flex items-center justify-center rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-neutral-800">Installer l'application</p>
                        <p class="text-xs text-neutral-500">Pour un accès rapide et hors ligne.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="installPWA()" class="bg-primary-500 text-white px-3 py-1 rounded-md text-sm font-semibold hover:bg-primary-600 transition-colors">Installer</button>
                    <button @click="showInstallBanner = false" class="text-neutral-400 p-1 hover:text-neutral-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <header class="sticky top-0 bg-neutral-50/80 backdrop-blur-sm z-20 safe-top content-safe-top" :class="{ 'pt-24': showInstallBanner, 'pt-14': !isOnline && !showInstallBanner }">
        <div class="px-4 py-3 border-b border-neutral-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-neutral-500 hover:text-primary-600 transition-colors md:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-neutral-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-neutral-500">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="hidden sm:flex items-center space-x-1 bg-green-100 text-green-800 px-3 py-1.5 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-sm font-semibold" x-text="walletBalance || '0.000 DT'"></span>
                    </div>
                    <button @click="showNotifications = !showNotifications" class="relative p-2 text-neutral-500 hover:text-primary-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                        <span x-show="notificationsCount > 0" class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-neutral-50"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <div x-show="sidebarOpen" @click.away="sidebarOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>
    <aside x-show="sidebarOpen" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed left-0 top-0 bottom-0 w-72 bg-white z-50 flex flex-col custom-scrollbar overflow-y-auto safe-top safe-bottom">
        <div class="p-5 border-b border-neutral-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-neutral-900">Al-Amena Livreur</h2>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-neutral-400 p-2 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        
        <nav class="flex-grow p-3 space-y-1">
            @php
                $navItems = [
                    ['route' => 'deliverer.dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>', 'stat' => null],
                    ['route' => 'deliverer.pickups.available', 'label' => 'Pickups Disponibles', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'stat' => 'stats.available_pickups', 'color' => 'bg-blue-500'],
                    ['route' => 'deliverer.pickups.mine', 'label' => 'Mes Pickups', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'stat' => 'stats.my_pickups', 'color' => 'bg-green-500'],
                    ['route' => 'deliverer.deliveries.index', 'label' => 'Livraisons', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>', 'stat' => 'stats.deliveries', 'color' => 'bg-orange-500'],
                    ['route' => 'deliverer.returns.index', 'label' => 'Retours', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>', 'stat' => 'stats.returns', 'color' => 'bg-purple-500'],
                    ['route' => 'deliverer.payments.index', 'label' => 'Paiements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'stat' => 'stats.payments', 'color' => 'bg-teal-500'],
                    ['divider' => true],
                    ['route' => 'deliverer.wallet.index', 'label' => 'Mon Wallet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                    ['route' => 'deliverer.client-topup.index', 'label' => 'Recharge Client', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'],
                    ['route' => 'deliverer.runsheets.index', 'label' => 'Feuilles de Route', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
                    ['route' => 'deliverer.notifications.index', 'label' => 'Notifications', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>', 'stat' => 'notificationsCount', 'color' => 'bg-red-500'],
                    ['route' => 'deliverer.help.index', 'label' => 'Aide', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                ];
            @endphp
            @foreach ($navItems as $item)
                @if (isset($item['divider']))
                    <div class="pt-2 pb-1 px-3"><div class="border-t border-neutral-200"></div></div>
                @else
                    <a href="{{ route($item['route']) }}" class="flex items-center px-3 py-2.5 rounded-lg transition-colors group relative {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-primary-50 text-primary-600' : 'text-neutral-600 hover:bg-neutral-100' }}">
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r-full {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-primary-500' : '' }}"></div>
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span class="font-medium text-sm">{{ $item['label'] }}</span>
                        @if (isset($item['stat']))
                            <span x-show="{{ $item['stat'] }} > 0" x-text="{{ $item['stat'] }}" class="ml-auto text-white text-xs font-semibold px-2 py-0.5 rounded-full {{ $item['color'] }}"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>
        
        <div class="p-3 mt-auto">
            <div class="p-3 bg-neutral-100 rounded-lg">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-neutral-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-neutral-500">Livreur</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('deliverer.profile.show') }}" class="flex items-center w-full px-3 py-2 text-sm text-neutral-600 hover:bg-white rounded-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Mon Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
    
    <main class="min-h-screen pb-24">
        <div class="p-4 sm:p-6">
            @yield('content')
        </div>
    </main>
    
    <nav class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm border-t border-neutral-200 safe-bottom z-30">
        <div class="grid grid-cols-5 gap-1 max-w-md mx-auto px-2 py-1.5">
            @php
                $bottomNavItems = [
                    ['route' => 'deliverer.dashboard', 'label' => 'Accueil', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>'],
                    ['route' => 'deliverer.pickups.available', 'match' => 'deliverer.pickups.*', 'label' => 'Pickups', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'stat' => 'stats.available_pickups'],
                    ['type' => 'button', 'action' => '$dispatch(\'open-scanner\')', 'label' => 'Scanner', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>'],
                    ['route' => 'deliverer.deliveries.index', 'match' => 'deliverer.deliveries.*', 'label' => 'Courses', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>', 'stat' => 'stats.deliveries'],
                    ['route' => 'deliverer.wallet.index', 'match' => 'deliverer.wallet.*', 'label' => 'Wallet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>']
                ];
            @endphp
            @foreach ($bottomNavItems as $item)
                @if (isset($item['type']) && $item['type'] === 'button')
                    <div class="flex items-center justify-center">
                        <button @click="{{ $item['action'] }}" class="relative -top-4 flex flex-col items-center justify-center p-3 text-white bg-primary-600 rounded-full shadow-lg hover:bg-primary-700 transition-transform hover:scale-105">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        </button>
                    </div>
                @else
                    @php 
                        $isActive = request()->routeIs($item['match'] ?? $item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}" class="flex flex-col items-center justify-center py-1 px-1 rounded-lg relative transition-colors {{ $isActive ? 'text-primary-600' : 'text-neutral-500 hover:bg-primary-50' }}">
                        <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span class="text-[11px] font-medium">{{ $item['label'] }}</span>
                        @if (isset($item['stat']))
                            <span x-show="{{ $item['stat'] }} > 0" class="absolute top-0 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                        @endif
                        @if ($isActive)
                            <div class="absolute bottom-0 h-0.5 w-4 bg-primary-600 rounded-full"></div>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
    
    @include('components.scanner-qr-final')
    
    <div x-show="showNotifications" @click.away="showNotifications = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-50">
        <div @click.stop class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-xl flex flex-col safe-top safe-bottom" x-show="showNotifications" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="p-4 border-b border-neutral-200">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-lg">Notifications</h3>
                    <button @click="showNotifications = false" class="p-1 text-neutral-400 hover:text-neutral-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow p-2 custom-scrollbar overflow-y-auto" x-html="notificationsList">
                <div class="text-center text-neutral-500 py-16">
                    <svg class="w-12 h-12 mx-auto mb-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                    <p class="font-medium">Aucune notification pour le moment</p>
                </div>
            </div>
        </div>
    </div>
    
    <div x-data="toast" class="fixed bottom-20 sm:bottom-4 right-4 z-50 space-y-2">
        @if(session('success'))
            <div x-data="{ show: false }" x-init="() => { show = true; setTimeout(() => show = false, 5000); }" x-show="show" x-transition class="flex items-start space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg max-w-xs">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
                <button @click="show = false" class="ml-auto -mr-1 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div x-data="{ show: false }" x-init="() => { show = true; setTimeout(() => show = false, 7000); }" x-show="show" x-transition class="flex items-start space-x-3 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg max-w-xs">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-medium">
                    @if(session('error')) {{ session('error') }} @endif
                    @if($errors->any()) @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach @endif
                </div>
                <button @click="show = false" class="ml-auto -mr-1 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
    </div>
    
    @stack('modals')
    
    <script>
        function delivererApp() {
            return {
                sidebarOpen: false, showNotifications: false, showInstallBanner: false,
                isOnline: navigator.onLine, deferredPrompt: null,
                stats: { available_pickups: 0, my_pickups: 0, deliveries: 0, returns: 0, payments: 0, urgent_deliveries: 0 },
                walletBalance: '0.000 DT', notificationsCount: 0, notificationsList: '',
                
                init() {
                    this.fetchData();
                    this.setupPWA();
                    setInterval(() => this.isOnline && this.fetchData(), 30000);
                    setInterval(() => navigator.serviceWorker?.controller?.postMessage({ type: 'CHECK_UPDATE' }), 300000);
                    window.addEventListener('online', () => this.setOnlineStatus(true));
                    window.addEventListener('offline', () => this.setOnlineStatus(false));
                },

                async fetchData() {
                    await Promise.all([
                        this.loadStats(),
                        this.loadNotifications(),
                        this.loadWalletBalance()
                    ]);
                },
                
                async apiCall(url, errorMsg) {
                    try {
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Network response was not ok');
                        return await response.json();
                    } catch (error) {
                        console.warn(`${errorMsg}:`, error);
                        return null;
                    }
                },

                async loadStats() {
                    const data = await this.apiCall('/deliverer/api/dashboard-stats', 'Stats offline');
                    if (data) this.stats = data;
                },
                
                async loadWalletBalance() {
                    const data = await this.apiCall('/deliverer/api/wallet/balance', 'Wallet offline');
                    if (data) this.walletBalance = data.formatted_balance;
                },
                
                async loadNotifications() {
                    const countData = await this.apiCall('/deliverer/api/notifications/unread-count', 'Notifications count offline');
                    if (countData) this.notificationsCount = countData.count;
                    
                    const recentData = await this.apiCall('/deliverer/api/notifications/recent', 'Recent notifications offline');
                    if (recentData) this.updateNotificationsList(recentData);
                },
                
                updateNotificationsList(notifications) {
                    if (notifications.length === 0) {
                        this.notificationsList = `
                            <div class="text-center text-neutral-500 py-16 flex flex-col items-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                                <p class="font-medium text-sm">Aucune notification pour le moment</p>
                            </div>`;
                        return;
                    }
                    this.notificationsList = notifications.map(n => `
                        <div class="p-3 mb-2 rounded-lg transition-colors ${!n.read ? 'bg-primary-50' : 'bg-neutral-50'}">
                            <div class="flex items-start space-x-3">
                                <div class="w-1.5 h-1.5 rounded-full mt-1.5 ${n.priority_color || 'bg-gray-400'}"></div>
                                <div class="flex-1">
                                    <p class="font-semibold text-sm text-neutral-800">${n.title}</p>
                                    <p class="text-sm text-neutral-600 mt-1">${n.message}</p>
                                    <p class="text-xs text-neutral-400 mt-2">${n.created_at_human}</p>
                                </div>
                            </div>
                        </div>`).join('');
                },
                
                setOnlineStatus(status) {
                    this.isOnline = status;
                    if (status) {
                        this.fetchData();
                        navigator.serviceWorker?.ready.then(reg => reg.sync?.register('offline-actions-sync'));
                    }
                },
                
                setupPWA() {
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.showInstallBanner = true;
                    });
                    
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js', { scope: '/deliverer/' })
                            .then(reg => {
                                reg.addEventListener('updatefound', () => {
                                    const newWorker = reg.installing;
                                    newWorker.addEventListener('statechange', () => {
                                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                            this.showUpdateAvailable();
                                        }
                                    });
                                });
                            }).catch(error => console.log('SW registration failed', error));
                    }
                },
                
                async installPWA() {
                    if (!this.deferredPrompt) return;
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') console.log('PWA installed');
                    this.deferredPrompt = null;
                    this.showInstallBanner = false;
                },
                
                showUpdateAvailable() {
                    const updateBanner = document.createElement('div');
                    updateBanner.className = 'fixed bottom-24 sm:bottom-20 right-4 bg-blue-600 text-white p-3 rounded-lg shadow-lg z-50';
                    updateBanner.innerHTML = `<div class="flex items-center justify-between"><div class="mr-4"><p class="font-semibold text-sm">Mise à jour disponible</p><p class="text-xs opacity-90">Redémarrez pour l'installer.</p></div><button onclick="window.location.reload()" class="bg-white text-blue-600 px-3 py-1 rounded-md text-sm font-semibold">Redémarrer</button></div>`;
                    document.body.appendChild(updateBanner);
                }
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                const shortcuts = { 's': 'open-scanner', 'd': '/deliverer/dashboard', 'w': '/deliverer/wallet', 'l': '/deliverer/deliveries' };
                if (shortcuts[e.key]) {
                    e.preventDefault();
                    if (e.key === 's') document.dispatchEvent(new CustomEvent('open-scanner'));
                    else window.location.href = shortcuts[e.key];
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>