<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Al-Amena Delivery') - Espace Client</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="Al-Amena Client">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Al-Amena Client">
    <meta name="description" content="Application client Al-Amena Delivery - Envoyer, Suivre, Gérer vos colis">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#059669">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#059669">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest-client.json">

    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/client-icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/client-icon-16x16.png">
    <link rel="shortcut icon" href="/images/icons/client-icon-32x32.png">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/images/icons/client-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/client-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/images/icons/client-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/images/icons/client-icon-120x120.png">

    <!-- Splash Screens for iOS -->
    <link rel="apple-touch-startup-image" href="/images/splash/launch-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="/images/splash/launch-750x1334.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="/images/splash/launch-1242x2208.png" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="/images/splash/launch-1125x2436.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Security -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'purple': {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'slide-in': 'slideIn 0.5s ease-out',
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'bounce-soft': 'bounceSoft 2s infinite'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(139, 92, 246, 0.3)' },
                            '100%': { boxShadow: '0 0 30px rgba(139, 92, 246, 0.6)' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(-10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        bounceSoft: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        }
                    }
                }
            }
        }
    </script>

    <!-- PWA Service Worker Registration -->
    <script>
        // Check if the browser supports service workers
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw-client.js')
                    .then(function(registration) {
                        console.log('[PWA] Service Worker enregistré avec succès:', registration.scope);

                        // Écouter les mises à jour du SW
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Nouvelle version disponible
                                    if (confirm('Une nouvelle version de l\'application est disponible. Souhaitez-vous la recharger ?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('[PWA] Échec de l\'enregistrement du Service Worker:', error);
                    });

                // Écouter les messages du service worker
                navigator.serviceWorker.addEventListener('message', function(event) {
                    const { type, data } = event.data;

                    switch (type) {
                        case 'NAVIGATE_TO':
                            window.location.href = data.url;
                            break;
                        case 'SYNC_COMPLETE':
                            // Refresh des données après synchronisation
                            if (window.clientAppInstance) {
                                window.clientAppInstance.loadStats();
                                window.clientAppInstance.loadNotifications();
                                window.clientAppInstance.loadWalletBalance();
                            }
                            break;
                    }
                });
            });
        }

        // Gestion de l'installation PWA
        let deferredPrompt;
        let pwaInstalled = false;

        // Détecter si l'app est déjà installée
        window.addEventListener('appinstalled', function() {
            pwaInstalled = true;
            console.log('[PWA] Application installée');
        });

        // Capturer l'événement beforeinstallprompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;

            // Afficher le bouton d'installation après 30 secondes si l'utilisateur utilise mobile
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    if (!pwaInstalled && deferredPrompt) {
                        showInstallPrompt();
                    }
                }, 30000);
            }
        });

        function showInstallPrompt() {
            if (!deferredPrompt) return;

            const installBanner = document.createElement('div');
            installBanner.className = 'fixed bottom-4 left-4 right-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 rounded-xl shadow-lg z-50 animate-slide-up';
            installBanner.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-sm">Installer l'application</h3>
                        <p class="text-xs opacity-90 mt-1">Ajoutez Al-Amena Client à votre écran d'accueil pour un accès rapide</p>
                    </div>
                    <div class="flex space-x-2 ml-3">
                        <button onclick="installPWA()" class="bg-white text-green-600 px-3 py-1 rounded-lg text-sm font-medium">
                            Installer
                        </button>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                            ✕
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(installBanner);

            // Auto-hide after 10 seconds
            setTimeout(() => {
                if (installBanner.parentNode) {
                    installBanner.remove();
                }
            }, 10000);
        }

        function installPWA() {
            if (!deferredPrompt) return;

            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[PWA] Utilisateur a accepté l\'installation');
                } else {
                    console.log('[PWA] Utilisateur a refusé l\'installation');
                }
                deferredPrompt = null;
            });

            // Remove install banner
            const banner = document.querySelector('.animate-slide-up');
            if (banner) banner.remove();
        }

        // Gestion hors ligne
        window.addEventListener('online', function() {
            document.body.classList.remove('offline');
            if (window.clientAppInstance) {
                window.clientAppInstance.loadStats();
                window.clientAppInstance.loadNotifications();
            }
        });

        window.addEventListener('offline', function() {
            document.body.classList.add('offline');
        });
    </script>

    <!-- CSS pour PWA -->
    <style>
        :root {
            --primary: #8B5CF6;
            --secondary: #EC4899;
            --accent: #A855F7;
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;
            --dark: #1F2937;
            --light: #F9FAFB;
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .interactive-element {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .interactive-element::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .interactive-element:hover::before {
            left: 100%;
        }

        .nav-dropdown {
            position: relative;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.1), 0 10px 10px -5px rgba(139, 92, 246, 0.04);
            border: 1px solid rgba(139, 92, 246, 0.1);
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
        }

        .dropdown-content.open {
            max-height: 400px;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 12px 16px;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 4px;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
            transform: translateX(4px);
        }

        .modern-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(139, 92, 246, 0.1);
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
        }

        .notification-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(139, 92, 246, 0.1);
        }

        .sidebar-glass {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8));
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(139, 92, 246, 0.1);
        }

        .nav-item-modern {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .nav-item-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-item-modern.active::before,
        .nav-item-modern:hover::before {
            transform: scaleY(1);
        }

        .nav-item-modern.active {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
            color: var(--primary);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.1);
        }

        .nav-item-modern:hover {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(236, 72, 153, 0.05));
            transform: translateX(8px);
        }

        .floating-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .floating-button:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.4);
        }

        .wallet-modern {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));
            border: 1px solid rgba(139, 92, 246, 0.2);
            backdrop-filter: blur(10px);
        }

        .dropdown-arrow {
            transition: transform 0.3s ease;
        }

        .dropdown-open .dropdown-arrow {
            transform: rotate(180deg);
        }
        .offline {
            filter: grayscale(0.3);
        }

        .offline::before {
            content: "Mode hors ligne";
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: #f59e0b;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 9999;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateX(-50%) translateY(-100%); }
            to { transform: translateX(-50%) translateY(0); }
        }

        @keyframes slide-up {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }

        /* Adaptations pour écrans très petits */
        @media (max-width: 380px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        /* Style pour les écrans avec notch */
        @supports (padding: max(0px)) {
            body {
                padding-left: max(12px, env(safe-area-inset-left));
                padding-right: max(12px, env(safe-area-inset-right));
            }
        }

        /* Pull-to-refresh style */
        body {
            overscroll-behavior-y: contain;
        }

        /* Amélioration de la performance de scroll sur mobile */
        * {
            -webkit-overflow-scrolling: touch;
        }

        /* Optimisation des animations pour mobile */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Mobile optimizations */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        .safe-area-top {
            padding-top: env(safe-area-inset-top);
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Amélioration du contraste pour mobile */
        @media (max-width: 768px) {
            .nav-item {
                font-size: 0.9rem;
                padding: 0.875rem 1rem;
            }

            .mobile-layout .sidebar-compact {
                width: 280px;
            }

            .mobile-layout .main-content {
                padding: 0;
            }

            /* Ensure proper padding for mobile content */
            .mobile-layout .main-content > main {
                padding: 1rem 0.75rem;
            }

            /* Fix top spacing issues on mobile */
            body.mobile-layout {
                padding-top: 0;
            }

            /* Ensure content doesn't go under navbar */
            .main-content {
                min-height: calc(100vh - 56px);
            }
        }

        /* Optimisation pour écrans très petits */
        @media (max-width: 480px) {
            .nav-item {
                font-size: 0.85rem;
                padding: 0.75rem 0.875rem;
            }

            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            /* More aggressive padding fixes for very small screens */
            .mobile-layout .main-content > main {
                padding: 0.75rem 0.5rem;
            }

            /* Better spacing for mobile cards and containers */
            .mobile-layout .space-y-6 > * + * {
                margin-top: 1rem;
            }

            .mobile-layout .space-y-4 > * + * {
                margin-top: 0.75rem;
            }
        }

        /* Fix for content padding issues */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .main-content > main {
                padding-top: 1.5rem;
            }
        }

        /* Better responsive container handling */
        .responsive-container {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (max-width: 640px) {
            .responsive-container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .responsive-container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }

        /* Style pour les boutons flottants */
        .fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 40;
            transition: all 0.2s ease;
        }

        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 24px rgba(16, 185, 129, 0.6);
        }

        @media (max-width: 768px) {
            .fab {
                bottom: 20px;
                right: 20px;
                width: 52px;
                height: 52px;
            }
        }

        /* Wallet Balance Styles */
        .wallet-balance-container {
            transition: all 0.3s ease;
        }

        .wallet-balance-container:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        .wallet-balance-loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading-shimmer 1.5s infinite;
        }

        @keyframes loading-shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .wallet-balance-amount {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Pulse animation for low balance */
        .wallet-balance-low {
            animation: balance-pulse 2s infinite;
        }

        @keyframes balance-pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 min-h-screen overflow-x-hidden" x-data="clientApp()"
      :class="{ 'mobile-layout': window.innerWidth <= 768 }"
      x-init="window.clientAppInstance = $data">
    
    <!-- Top Navigation Bar -->
    <nav class="navbar-glass shadow-lg fixed top-0 left-0 right-0 z-50 safe-area-top">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-14 md:h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 md:p-2 rounded-lg hover:bg-purple-50 transition-all duration-300 touch-target interactive-element">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="flex items-center space-x-2 md:space-x-3">
                        <div class="w-7 h-7 md:w-8 md:h-8 lg:w-10 lg:h-10 gradient-bg rounded-xl flex items-center justify-center hover-lift">
                            <svg class="w-3.5 h-3.5 md:w-4 md:h-4 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-base md:text-lg lg:text-xl font-bold gradient-text">Al-Amena</h1>
                            <p class="text-xs text-gray-500">Espace Client</p>
                        </div>
                    </div>
                </div>

                <!-- Right side - User info and actions -->
                <div class="flex items-center space-x-2 lg:space-x-4">
                    <!-- Wallet Balance Display -->
                    <div class="hidden sm:flex items-center space-x-3 wallet-modern rounded-xl px-3 py-2 lg:px-4 lg:py-2.5 wallet-balance-container hover-lift" :class="{ 'wallet-balance-low': wallet.balance !== null && wallet.balance < 50 }">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-purple-600 font-medium">Solde Actuel</p>
                                <p class="text-sm lg:text-base font-bold text-purple-700 wallet-balance-amount" x-text="formatBalance(wallet.balance)" x-show="wallet.balance !== null">
                                    ---
                                </p>
                                <div x-show="wallet.advance_balance > 0 && wallet.advance_balance !== null" class="mt-0.5">
                                    <p class="text-xs text-blue-600 font-medium">+ Avance: <span x-text="formatBalance(wallet.advance_balance)">---</span></p>
                                </div>
                                <div x-show="wallet.balance === null" class="flex items-center space-x-1">
                                    <div class="w-3 h-3 bg-emerald-300 rounded-full animate-pulse"></div>
                                    <div class="w-8 h-3 bg-emerald-200 rounded animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Quick Actions Dropdown -->
                        <div class="relative" x-data="{ walletOpen: false }">
                            <button @click="walletOpen = !walletOpen" class="p-1 text-emerald-600 hover:text-emerald-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="walletOpen" @click.away="walletOpen = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border z-50">
                                <div class="p-2">
                                    <a href="{{ route('client.wallet.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Détails du portefeuille
                                    </a>
                                    <a href="{{ route('client.wallet.topup') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Recharger le compte
                                    </a>
                                    <a href="{{ route('client.wallet.transactions') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Historique
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Wallet Balance (Compact) -->
                    <div class="sm:hidden" x-data="{ mobileWalletOpen: false }">
                        <button @click="mobileWalletOpen = !mobileWalletOpen" class="flex items-center space-x-2 bg-emerald-50 border border-emerald-200 rounded-lg px-2 py-1.5">
                            <div class="w-6 h-6 bg-gradient-to-r from-emerald-500 to-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-emerald-700" x-text="formatBalanceShort(wallet.balance)" x-show="wallet.balance !== null">--</span>
                            <div x-show="wallet.balance === null" class="w-6 h-2 bg-emerald-200 rounded animate-pulse"></div>
                        </button>

                        <div x-show="mobileWalletOpen" @click.away="mobileWalletOpen = false" x-transition
                             class="absolute right-4 mt-2 w-56 bg-white rounded-xl shadow-xl border z-50">
                            <div class="p-4 border-b bg-emerald-50">
                                <div class="text-center">
                                    <p class="text-xs text-emerald-600 font-medium">Solde Actuel</p>
                                    <p class="text-lg font-bold text-emerald-700" x-text="formatBalance(wallet.balance)">---</p>
                                </div>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('client.wallet.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Portefeuille
                                </a>
                                <a href="{{ route('client.wallet.topup') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Recharger
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors relative">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                            </svg>
                            <span x-show="notifications.unread_count > 0" 
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 lg:w-5 lg:h-5 flex items-center justify-center animate-pulse"
                                  x-text="notifications.unread_count"></span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-72 lg:w-80 bg-white rounded-xl shadow-xl border z-50">
                            <div class="p-4 border-b">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                                    <button @click="markAllAsRead()" class="text-sm text-purple-600 hover:text-purple-800">
                                        Tout marquer lu
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-80 lg:max-h-96 overflow-y-auto" x-html="notificationsList">
                                <p class="p-4 text-gray-500 text-center">Chargement...</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center space-x-2 lg:space-x-3 p-1 lg:p-2 rounded-xl hover:bg-purple-50 transition-colors">
                            <div class="w-7 h-7 lg:w-8 lg:h-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs lg:text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900 truncate max-w-24 lg:max-w-32">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">Client</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-56 lg:w-64 bg-white rounded-xl shadow-xl border z-50">
                            <div class="p-4 border-b bg-gradient-to-r from-purple-50 to-indigo-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-sm lg:text-base">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm lg:text-base">{{ auth()->user()->name }}</p>
                                        <p class="text-xs lg:text-sm text-gray-600">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-2">
                                <a href="{{ route('client.profile.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon Profil
                                </a>
                                <a href="{{ route('client.bank-accounts.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Comptes Bancaires
                                </a>
                                <a href="{{ route('client.notifications.settings') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Paramètres
                                </a>
                            </div>
                            
                            <div class="p-2 border-t">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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

    <!-- Main Layout with fixed sidebar -->
    <div class="flex pt-14 md:pt-16">
        <!-- Fixed Sidebar -->
        <div class="fixed inset-y-0 left-0 z-30 w-72 md:w-64 sidebar-glass shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 pt-14 md:pt-16 sidebar-compact"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
             @click.away="if (window.innerWidth < 1024) sidebarOpen = false">
            
            <!-- Navigation Menu -->
            <nav class="p-4 space-y-1 h-full overflow-y-auto pb-20" x-data="navigationData()">
                <!-- Dashboard -->
                <a href="{{ route('client.dashboard') }}"
                   class="nav-item-modern interactive-element {{ request()->routeIs('client.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <!-- Gestion des Colis -->
                <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('client.packages.*') ? 'true' : 'false' }} }">
                    <div class="nav-item-modern interactive-element {{ request()->routeIs('client.packages.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700 cursor-pointer" @click="open = !open">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="font-medium flex-1">Gestion des Colis</span>
                        <div class="flex items-center space-x-2">
                            <span x-show="stats.in_progress_packages > 0" class="notification-badge bg-purple-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.in_progress_packages"></span>
                            <svg class="w-4 h-4 dropdown-arrow transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="dropdown-content" :class="{ 'open': open }">
                        <a href="{{ route('client.packages.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span>Mes Colis</span>
                        </a>
                        <a href="{{ route('client.packages.create') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Nouveau Colis</span>
                        </a>
                    </div>
                </div>

                <!-- Demandes de Collecte -->
                <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('client.pickup-requests.*') ? 'true' : 'false' }} }">
                    <div class="nav-item-modern interactive-element {{ request()->routeIs('client.pickup-requests.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700 cursor-pointer" @click="open = !open">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span class="font-medium flex-1">Demandes de Collecte</span>
                        <div class="flex items-center space-x-2">
                            <span x-show="stats.pending_pickups > 0" class="notification-badge bg-blue-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_pickups"></span>
                            <svg class="w-4 h-4 dropdown-arrow transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="dropdown-content" :class="{ 'open': open }">
                        <a href="{{ route('client.pickup-requests.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span>Mes Collectes</span>
                        </a>
                        <a href="{{ route('client.pickup-requests.create') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Nouvelle Collecte</span>
                        </a>
                    </div>
                </div>

                <!-- Manifestes & Adresses -->
                <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('client.manifests.*', 'client.pickup-addresses.*') ? 'true' : 'false' }} }">
                    <div class="nav-item-modern interactive-element {{ request()->routeIs('client.manifests.*', 'client.pickup-addresses.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700 cursor-pointer" @click="open = !open">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="font-medium flex-1">Documents & Adresses</span>
                        <svg class="w-4 h-4 dropdown-arrow transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="dropdown-content" :class="{ 'open': open }">
                        <a href="{{ route('client.manifests.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Manifestes</span>
                        </a>
                        <a href="{{ route('client.pickup-addresses.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span>Adresses de Collecte</span>
                        </a>
                    </div>
                </div>

                <!-- Finances -->
                <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('client.wallet.*', 'client.bank-accounts.*', 'client.withdrawals*') ? 'true' : 'false' }} }">
                    <div class="nav-item-modern interactive-element {{ request()->routeIs('client.wallet.*', 'client.bank-accounts.*', 'client.withdrawals*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700 cursor-pointer" @click="open = !open">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="font-medium flex-1">Finances</span>
                        <div class="flex items-center space-x-2">
                            <span x-show="stats.pending_withdrawals > 0" class="notification-badge bg-yellow-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_withdrawals"></span>
                            <svg class="w-4 h-4 dropdown-arrow transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="dropdown-content" :class="{ 'open': open }">
                        <a href="{{ route('client.wallet.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span>Mon Portefeuille</span>
                        </a>
                        <a href="{{ route('client.bank-accounts.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Comptes Bancaires</span>
                        </a>
                        <a href="{{ route('client.withdrawals') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span>Mes Retraits</span>
                        </a>
                    </div>
                </div>

                <!-- Support -->
                <div class="nav-dropdown" x-data="{ open: {{ request()->routeIs('client.tickets.*', 'client.notifications.*') ? 'true' : 'false' }} }">
                    <div class="nav-item-modern interactive-element {{ request()->routeIs('client.tickets.*', 'client.notifications.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-700 cursor-pointer" @click="open = !open">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="font-medium flex-1">Support & Notifications</span>
                        <div class="flex items-center space-x-2">
                            <span x-show="notifications.unread_count > 0" class="notification-badge bg-red-500 text-white text-xs px-2 py-1 rounded-full" x-text="notifications.unread_count"></span>
                            <svg class="w-4 h-4 dropdown-arrow transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="dropdown-content" :class="{ 'open': open }">
                        <a href="{{ route('client.tickets.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Support Tickets</span>
                        </a>
                        <a href="{{ route('client.notifications.index') }}" class="dropdown-item flex items-center text-gray-600 hover:text-purple-600">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                            </svg>
                            <span>Notifications</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
             class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Main Content Area -->
        <div class="flex-1 lg:ml-64 main-content">
            <!-- Page Content -->
            <main class="min-h-screen pb-20 safe-area-bottom">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Floating Action Button (Mobile Only) -->
    <div x-show="window.innerWidth <= 768" class="lg:hidden">
        <button onclick="window.location.href='{{ route('client.packages.create') }}'"
                class="floating-button rounded-full w-14 h-14 flex items-center justify-center fixed bottom-6 right-6 z-40"
                x-data="{ showTooltip: false }"
                @mouseenter="showTooltip = true"
                @mouseleave="showTooltip = false"
                title="Créer un nouveau colis">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>

            <!-- Tooltip -->
            <div x-show="showTooltip"
                 x-transition
                 class="absolute bottom-full right-0 mb-2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                Nouveau colis
            </div>
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="fixed top-20 right-4 bg-emerald-500 text-white px-4 lg:px-6 py-3 rounded-xl shadow-lg z-50 max-w-xs lg:max-w-sm">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm lg:text-base">{{ session('success') }}</span>
                <button @click="show = false" class="ml-auto flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="fixed top-20 right-4 bg-red-500 text-white px-4 lg:px-6 py-3 rounded-xl shadow-lg z-50 max-w-xs lg:max-w-sm">
            <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1 text-sm lg:text-base">
                    @if(session('error'))
                        <span>{{ session('error') }}</span>
                    @endif
                    @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    @endif
                </div>
                <button @click="show = false" class="ml-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Scripts -->
    <script>
        function clientApp() {
            return {
                sidebarOpen: false,
                isOnline: navigator.onLine,
                isMobile: window.innerWidth <= 768,
                stats: {
                    in_progress_packages: 0,
                    pending_withdrawals: 0,
                    pending_pickups: 0
                },
                notifications: {
                    unread_count: 0
                },
                wallet: {
                    balance: null,
                    advance_balance: null,
                    total_available_for_return_fees: null,
                    currency: 'DT'
                },
                notificationsList: '<p class="p-4 text-gray-500 text-center">Chargement...</p>',

                init() {
                    this.loadStats();
                    this.loadNotifications();
                    this.loadWalletBalance();

                    // Auto-refresh every 60 seconds (only when online)
                    setInterval(() => {
                        if (this.isOnline) {
                            this.loadStats();
                            this.loadNotifications();
                            this.loadWalletBalance();
                        }
                    }, 60000);

                    // Handle responsive sidebar
                    this.handleResize();
                    window.addEventListener('resize', () => this.handleResize());

                    // Listen for online/offline events
                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        this.loadStats();
                        this.loadNotifications();
                        this.loadWalletBalance();
                    });

                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                    });

                    // PWA features
                    this.initPWAFeatures();

                    // Enable pull-to-refresh on mobile
                    if (this.isMobile) {
                        this.initPullToRefresh();
                    }

                    // Haptic feedback for touch interactions
                    if ('vibrate' in navigator && this.isMobile) {
                        this.enableHapticFeedback();
                    }
                },

                handleResize() {
                    this.isMobile = window.innerWidth <= 768;
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = false;
                    }
                },

                initPWAFeatures() {
                    // Register for background sync if available
                    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                        navigator.serviceWorker.ready.then(registration => {
                            registration.sync.register('client-background-sync');
                        });
                    }

                    // Handle notification permission
                    if ('Notification' in window && 'serviceWorker' in navigator) {
                        if (Notification.permission === 'default') {
                            setTimeout(() => {
                                this.requestNotificationPermission();
                            }, 10000); // Ask after 10 seconds
                        }
                    }
                },

                async requestNotificationPermission() {
                    if (Notification.permission === 'default') {
                        const permission = await Notification.requestPermission();
                        if (permission === 'granted') {
                            this.showToast('Notifications activées avec succès!', 'success');
                        }
                    }
                },

                initPullToRefresh() {
                    let startY = 0;
                    let isPulling = false;

                    document.addEventListener('touchstart', (e) => {
                        startY = e.touches[0].pageY;
                        isPulling = window.pageYOffset === 0;
                    });

                    document.addEventListener('touchmove', (e) => {
                        if (!isPulling) return;

                        const currentY = e.touches[0].pageY;
                        const pullDistance = currentY - startY;

                        if (pullDistance > 100) {
                            // Visual feedback for pull to refresh
                            document.body.style.paddingTop = '20px';
                        }
                    });

                    document.addEventListener('touchend', (e) => {
                        if (!isPulling) return;

                        const currentY = e.changedTouches[0].pageY;
                        const pullDistance = currentY - startY;

                        document.body.style.paddingTop = '';

                        if (pullDistance > 100) {
                            this.refresh();
                        }

                        isPulling = false;
                    });
                },

                enableHapticFeedback() {
                    // Add haptic feedback to buttons
                    document.addEventListener('click', (e) => {
                        if (e.target.matches('button, .nav-item, .fab')) {
                            navigator.vibrate(50);
                        }
                    });
                },

                refresh() {
                    this.showToast('Actualisation...', 'info');
                    this.loadStats();
                    this.loadNotifications();
                    this.loadWalletBalance();

                    // Refresh the current page data
                    if (typeof window.refreshPageData === 'function') {
                        window.refreshPageData();
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/client/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats.in_progress_packages = data.in_progress_packages || 0;
                            this.stats.pending_withdrawals = data.pending_withdrawals || 0;
                        }
                    } catch (error) {
                        console.error('Erreur chargement stats:', error);
                    }
                },

                async loadWalletBalance() {
                    const previousBalance = this.wallet.balance;
                    const previousAdvanceBalance = this.wallet.advance_balance;
                    try {
                        const response = await fetch('/client/api/wallet-balance');
                        if (response.ok) {
                            const data = await response.json();
                            this.wallet.balance = data.balance;
                            this.wallet.advance_balance = data.advance_balance || 0;
                            this.wallet.total_available_for_return_fees = data.total_available_for_return_fees || 0;
                            this.wallet.currency = data.currency || 'DT';

                            // Vérifier si le solde est faible et afficher une notification
                            this.checkLowBalance();

                            // Vérifier si le solde a changé et notifier l'utilisateur
                            this.checkBalanceChange(previousBalance);
                            this.checkAdvanceBalanceChange(previousAdvanceBalance);
                        }
                    } catch (error) {
                        console.error('Erreur chargement solde:', error);
                        // Fallback - essayer d'obtenir le solde via l'API dashboard
                        try {
                            const fallbackResponse = await fetch('/client/api/dashboard-stats');
                            if (fallbackResponse.ok) {
                                const fallbackData = await fallbackResponse.json();
                                this.wallet.balance = fallbackData.wallet_balance || 0;
                                this.wallet.advance_balance = 0; // Pas disponible dans l'API dashboard
                                this.checkLowBalance();
                                this.checkBalanceChange(previousBalance);
                            }
                        } catch (fallbackError) {
                            console.error('Erreur fallback solde:', fallbackError);
                            this.wallet.balance = 0;
                            this.wallet.advance_balance = 0;
                        }
                    }
                },

                checkLowBalance() {
                    if (this.wallet.balance !== null && this.wallet.balance < 50 && this.wallet.balance >= 0) {
                        // Ne pas afficher la notification plus d'une fois par session
                        if (!sessionStorage.getItem('lowBalanceWarningShown')) {
                            this.showToast(`Attention: Votre solde est faible (${this.formatBalance(this.wallet.balance)}). Pensez à recharger votre compte.`, 'warning');
                            sessionStorage.setItem('lowBalanceWarningShown', 'true');
                        }
                    }
                },

                checkBalanceChange(previousBalance) {
                    if (previousBalance !== null && this.wallet.balance !== null && previousBalance !== this.wallet.balance) {
                        const difference = this.wallet.balance - previousBalance;
                        if (Math.abs(difference) >= 5) { // Seulement si la différence est significative
                            const changeText = difference > 0 ? 'augmenté' : 'diminué';
                            const changeAmount = this.formatBalance(Math.abs(difference));
                            this.showToast(`Votre solde a ${changeText} de ${changeAmount}`, difference > 0 ? 'success' : 'info');
                        }
                    }
                },

                checkAdvanceBalanceChange(previousAdvanceBalance) {
                    if (previousAdvanceBalance !== null && this.wallet.advance_balance !== null && previousAdvanceBalance !== this.wallet.advance_balance) {
                        const difference = this.wallet.advance_balance - previousAdvanceBalance;
                        if (Math.abs(difference) >= 1) { // Seulement si la différence est significative
                            const changeText = difference > 0 ? 'ajoutée' : 'utilisée/retirée';
                            const changeAmount = this.formatBalance(Math.abs(difference));
                            const messageType = difference > 0 ? 'success' : 'info';
                            this.showToast(`Avance ${changeText}: ${changeAmount}`, messageType);
                        }
                    }
                },

                async loadNotifications() {
                    try {
                        const response = await fetch('/client/api/notifications/unread-count');
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications.unread_count = data.count;
                        }

                        // Charger la liste des notifications récentes
                        const recentResponse = await fetch('/client/api/notifications/recent');
                        if (recentResponse.ok) {
                            const data = await recentResponse.json();
                            this.updateNotificationsList(data.notifications);
                        }
                    } catch (error) {
                        console.error('Erreur chargement notifications:', error);
                    }
                },

                updateNotificationsList(notifications) {
                    if (notifications.length === 0) {
                        this.notificationsList = '<p class="p-4 text-gray-500 text-center">Aucune notification</p>';
                        return;
                    }

                    this.notificationsList = notifications.map(notification => `
                        <div class="p-4 border-b hover:bg-gray-50 ${notification.read ? 'opacity-75' : ''}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">${notification.title}</p>
                                    <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                                    <p class="text-xs text-gray-500 mt-2">${this.formatDate(notification.created_at)}</p>
                                </div>
                                <div class="ml-2 flex flex-col items-end space-y-1">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                        ${notification.type}
                                    </span>
                                    ${!notification.read ? `
                                        <button onclick="markNotificationRead(${notification.id})" 
                                                class="text-xs text-purple-600 hover:text-purple-800">
                                            Marquer lu
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('');
                },

                async markAllAsRead() {
                    try {
                        const response = await fetch('/client/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            this.loadNotifications();
                        }
                    } catch (error) {
                        console.error('Erreur marquage notifications:', error);
                    }
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                formatBalance(amount) {
                    if (amount === null || amount === undefined) {
                        return '---';
                    }
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 3,
                        maximumFractionDigits: 3
                    }).format(amount) + ' ' + this.wallet.currency;
                },

                formatBalanceShort(amount) {
                    if (amount === null || amount === undefined) {
                        return '--';
                    }
                    if (amount >= 1000) {
                        return (amount / 1000).toFixed(1) + 'K ' + this.wallet.currency;
                    }
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 1
                    }).format(amount) + ' ' + this.wallet.currency;
                }
            }
        }

        // Global functions
        async function markNotificationRead(notificationId) {
            try {
                const response = await fetch(`/client/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    // Trigger notification reload from Alpine component
                    document.querySelector('[x-data*="clientApp"]').__x.$data.loadNotifications();
                }
            } catch (error) {
                console.error('Erreur marquage notification:', error);
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            let bgColor;
            switch (type) {
                case 'success':
                    bgColor = 'bg-emerald-500';
                    break;
                case 'error':
                    bgColor = 'bg-red-500';
                    break;
                case 'info':
                    bgColor = 'bg-blue-500';
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-500';
                    break;
                default:
                    bgColor = 'bg-gray-500';
            }

            toast.className = `fixed top-20 right-4 ${bgColor} text-white px-4 md:px-6 py-2 md:py-3 rounded-xl shadow-lg z-50 transform transition-all duration-300 text-sm md:text-base max-w-xs md:max-w-sm`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Haptic feedback for mobile
            if ('vibrate' in navigator && window.innerWidth <= 768) {
                navigator.vibrate(100);
            }

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, type === 'info' ? 2000 : 3000);
        }

        // Expose showToast globally for PWA features
        window.showToast = showToast;

        // Navigation data function for dropdown menus
        function navigationData() {
            return {
                // Navigation state management
            }
        }
    </script>

    <!-- Client Offline Manager -->
    <script src="/js/client-offline.js"></script>

    @stack('scripts')
</body>
</html>