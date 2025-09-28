<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Al-Amena Livreur</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta -->
    <meta name="theme-color" content="#2563EB">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest-deliverer.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" href="/images/icons/icon-32x32.png">

    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [data-tailwind-warning] { display: none !important; }
        html {
            scroll-behavior: smooth;
            -webkit-tap-highlight-color: transparent;
            font-family: system-ui, -apple-system, sans-serif;
        }

        /* Safe area support */
        :root {
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
        }

        .safe-top { padding-top: max(1rem, env(safe-area-inset-top)); }
        .safe-bottom { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }

        /* Mobile interactions */
        .active\\:scale-95:active { transform: scale(0.95); }
        .active\\:scale-98:active { transform: scale(0.98); }

        /* Smooth transitions */
        .transition-all { transition: all 0.2s ease; }

        /* Menu burger animation */
        .menu-line {
            transition: all 0.3s ease;
            transform-origin: center;
        }

        .menu-open .line1 { transform: rotate(45deg) translate(5px, 5px); }
        .menu-open .line2 { opacity: 0; }
        .menu-open .line3 { transform: rotate(-45deg) translate(7px, -6px); }

        /* Overlay */
        .menu-overlay {
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        /* Bottom navigation shadow */
        .bottom-nav-shadow {
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.1);
        }

        /* Safe mobile padding */
        .pb-safe-mobile {
            padding-bottom: max(5rem, calc(5rem + env(safe-area-inset-bottom)));
        }

        @media (min-width: 768px) {
            .pb-safe-mobile {
                padding-bottom: 1rem;
            }
        }

        /* Content spacing for mobile */
        .content-mobile-spacing {
            margin-bottom: max(6rem, calc(6rem + env(safe-area-inset-bottom)));
        }

        @media (min-width: 768px) {
            .content-mobile-spacing {
                margin-bottom: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ menuOpen: false, loading: false }">

    <!-- Header avec menu burger -->
    <header class="bg-blue-600 text-white sticky top-0 z-40 safe-top">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Menu Burger -->
            <button @click="menuOpen = !menuOpen"
                    class="p-2 rounded-lg hover:bg-blue-700 active:scale-95 transition-all"
                    :class="{ 'menu-open': menuOpen }">
                <div class="w-6 h-6 flex flex-col justify-center space-y-1">
                    <span class="menu-line line1 block h-0.5 w-6 bg-white rounded"></span>
                    <span class="menu-line line2 block h-0.5 w-6 bg-white rounded"></span>
                    <span class="menu-line line3 block h-0.5 w-6 bg-white rounded"></span>
                </div>
            </button>

            <!-- Titre -->
            <h1 class="text-lg font-semibold truncate mx-4">@yield('title', 'Al-Amena')</h1>

            <!-- Scanner rapide -->
            <a href="{{ route('deliverer.scan.simple') }}"
               class="p-2.5 bg-blue-700 rounded-lg hover:bg-blue-800 active:scale-95 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                </svg>
            </a>
        </div>
    </header>

    <!-- Menu overlay -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="menuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 menu-overlay z-50"
         style="display: none;">
    </div>

    <!-- Menu sidebar -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed top-0 left-0 h-full w-80 bg-white shadow-xl z-50 overflow-y-auto"
         style="display: none;">

        <!-- Menu header -->
        <div class="bg-blue-600 text-white p-4 safe-top">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">{{ auth()->user()->name }}</h2>
                    <p class="text-blue-200 text-sm">Livreur</p>
                </div>
                <button @click="menuOpen = false" class="p-2 hover:bg-blue-700 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation menu -->
        <nav class="p-4">
            <!-- Accès rapides -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Accès rapides</h3>
                <div class="space-y-2">
                    <a href="{{ route('deliverer.simple.dashboard') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-98"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        <span class="font-medium">Ma Tournée</span>
                    </a>

                    <a href="{{ route('deliverer.scan.simple') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-98"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        <span class="font-medium">Scanner QR</span>
                    </a>

                    <a href="{{ route('deliverer.wallet.index') }}"
                       class="flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-98"
                       @click="menuOpen = false">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="font-medium">Mon Wallet</span>
                    </a>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Actions rapides</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-700">Livraisons</span>
                        <div class="text-lg font-bold text-green-600" x-text="'12'">12</div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-700">Collectes</span>
                        <div class="text-lg font-bold text-blue-600" x-text="'5'">5</div>
                    </div>
                </div>
            </div>

            <!-- Wallet rapide -->
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Solde Wallet</p>
                        <p class="text-lg font-bold text-purple-600">2,450.00 DA</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Déconnexion -->
            <div class="border-t pt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center w-full p-3 text-red-600 rounded-lg hover:bg-red-50 transition-all active:scale-98">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Main content -->
    <main class="min-h-screen pb-safe-mobile">
        @yield('content')
    </main>

    <!-- Bottom navigation mobile -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 bottom-nav-shadow z-30 md:hidden" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <div class="flex items-center justify-around py-2">
            <a href="{{ route('deliverer.simple.dashboard') }}"
               class="flex flex-col items-center p-2 {{ request()->routeIs('deliverer.simple.dashboard') ? 'text-blue-600' : 'text-gray-500' }} active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                </svg>
                <span class="text-xs mt-1">Tournée</span>
            </a>

            <a href="{{ route('deliverer.scan.simple') }}"
               class="flex flex-col items-center p-2 {{ request()->routeIs('deliverer.scan.simple') ? 'text-blue-600' : 'text-gray-500' }} active:scale-95 transition-all">
                <div class="bg-blue-600 p-2 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </div>
                <span class="text-xs mt-1 text-blue-600 font-medium">Scanner</span>
            </a>

            <a href="{{ route('deliverer.wallet.index') }}"
               class="flex flex-col items-center p-2 {{ request()->routeIs('deliverer.wallet.index') ? 'text-blue-600' : 'text-gray-500' }} active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-xs mt-1">Wallet</span>
            </a>
        </div>
    </nav>

    <!-- Loading overlay -->
    <div x-show="loading"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         style="display: none;">
        <div class="bg-white rounded-lg p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700">Chargement...</p>
        </div>
    </div>

    <!-- Global Scripts -->
    <script>
        // PWA Installation
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        // Network status
        window.addEventListener('online', () => {
            document.body.classList.remove('offline');
        });

        window.addEventListener('offline', () => {
            document.body.classList.add('offline');
        });

        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Global toast function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }
    </script>

    @stack('scripts')
</body>
</html>