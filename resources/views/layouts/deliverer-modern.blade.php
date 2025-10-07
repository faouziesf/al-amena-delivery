<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <title>@yield('title', 'Al-Amena Delivery')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Variables couleurs modernes */
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #06B6D4;
        }

        /* Safe areas iPhone */
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            overscroll-behavior: none;
        }

        .safe-top {
            padding-top: max(1rem, env(safe-area-inset-top));
        }

        .safe-bottom {
            padding-bottom: max(env(1rem, env(safe-area-inset-bottom)));
        }

        /* Animations optimisées */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        /* Boutons modernes */
        .btn {
            @apply px-6 py-3 rounded-xl font-semibold transition-all active:scale-95;
        }

        .btn-primary {
            @apply bg-indigo-600 text-white hover:bg-indigo-700;
        }

        .btn-success {
            @apply bg-green-600 text-white hover:bg-green-700;
        }

        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700;
        }

        /* Cards modernes */
        .card {
            @apply bg-white rounded-2xl shadow-sm border border-gray-100;
        }

        /* Disable pull-to-refresh sur iOS */
        html, body {
            overscroll-behavior-y: contain;
        }

        /* Loading spinner optimisé */
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Optimisation des transitions */
        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased" x-data="{ loading: false }">

    <!-- Global Loading -->
    <div x-show="loading" 
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="spinner"></div>
    </div>

    <!-- Content -->
    <div class="min-h-screen pb-20">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40"
         style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <div class="flex items-center justify-around py-2">
            <!-- Ma Tournée -->
            <a href="{{ route('deliverer.tournee') }}"
               class="flex flex-col items-center py-2 px-4 transition-colors {{ request()->routeIs('deliverer.tournee') ? 'text-indigo-600' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs font-medium">Tournée</span>
            </a>

            <!-- Pickups -->
            <a href="{{ route('deliverer.pickups.available') }}"
               class="flex flex-col items-center py-2 px-4 transition-colors {{ request()->routeIs('deliverer.pickups.available') ? 'text-indigo-600' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-xs font-medium">Pickups</span>
            </a>

            <!-- Scanner -->
            <a href="{{ route('deliverer.scan.simple') }}"
               class="flex flex-col items-center -mt-6 mb-2">
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-1">Scanner</span>
            </a>

            <!-- Wallet -->
            <a href="{{ route('deliverer.wallet') }}"
               class="flex flex-col items-center py-2 px-4 transition-colors {{ request()->routeIs('deliverer.wallet') ? 'text-indigo-600' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="text-xs font-medium">Wallet</span>
            </a>

            <!-- Menu -->
            <a href="{{ route('deliverer.menu') }}"
               class="flex flex-col items-center py-2 px-4 transition-colors {{ request()->routeIs('deliverer.menu') ? 'text-indigo-600' : 'text-gray-500' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="text-xs font-medium">Menu</span>
            </a>
        </div>
    </nav>

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Global Scripts -->
    <script>
        // Configuration API pour ngrok/localhost
        window.API_CONFIG = {
            baseURL: window.location.origin,
            timeout: 15000, // 15 secondes pour ngrok
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Helper pour requêtes API optimisées pour ngrok
        window.apiRequest = async function(url, options = {}) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            const config = {
                method: options.method || 'GET',
                headers: {
                    ...window.API_CONFIG.headers,
                    'X-CSRF-TOKEN': csrfToken,
                    ...options.headers
                },
                credentials: 'same-origin', // Important pour ngrok
                ...options
            };

            // Ajouter body si nécessaire
            if (options.body && typeof options.body === 'object') {
                config.body = JSON.stringify(options.body);
            }

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), window.API_CONFIG.timeout);

                const response = await fetch(url, {
                    ...config,
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}`);
                }

                return data;
            } catch (error) {
                if (error.name === 'AbortError') {
                    throw new Error('Connexion trop lente. Vérifiez votre réseau.');
                }
                throw error;
            }
        };

        // Toast moderne
        window.showToast = function(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                warning: 'bg-yellow-600',
                info: 'bg-blue-600'
            };

            const icons = {
                success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
                error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
                warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            };

            toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3 fade-in`;
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icons[type]}
                </svg>
                <span class="font-medium">${message}</span>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };

        // Vibration helper
        window.vibrate = function(pattern = [50]) {
            if ('vibrate' in navigator) {
                navigator.vibrate(pattern);
            }
        };

        // Gérer la connexion réseau
        window.addEventListener('online', () => {
            showToast('Connexion rétablie', 'success');
        });

        window.addEventListener('offline', () => {
            showToast('Pas de connexion internet', 'error', 5000);
        });

        // Empêcher le double tap zoom sur iOS
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

    @stack('scripts')

    <!-- Service Worker pour Cache -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('✅ Service Worker enregistré'))
                .catch(err => console.log('❌ Service Worker erreur:', err));
        });
    }
    </script>
</body>
</html>
