<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <title>@yield('title', 'Al-Amena Delivery') - Livreur</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Al-Amena Delivery">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- Permissions Meta Tags -->
    <meta http-equiv="Feature-Policy" content="camera 'self'; microphone 'none'; geolocation 'self'">
    
    <!-- CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- jsQR Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js" 
            onerror="console.warn('jsQR failed to load')"></script>
    
    <!-- QuaggaJS pour codes-barres -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"
            onerror="console.warn('QuaggaJS failed to load')"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'emerald': {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b'
                        },
                        'purple': {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-soft': 'bounce 2s infinite',
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'wiggle': 'wiggle 1s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(100%)' },
                            '100%': { transform: 'translateY(0)' }
                        },
                        wiggle: {
                            '0%, 100%': { transform: 'rotate(-3deg)' },
                            '50%': { transform: 'rotate(3deg)' }
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen overflow-x-hidden" x-data="delivererApp()" x-init="init()">
    
    <!-- PWA Install Prompt -->
    <div x-show="showInstallPrompt && !installPromptDismissed" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0"
         class="fixed bottom-20 left-4 right-4 bg-gradient-to-r from-emerald-500 to-green-500 text-white p-4 rounded-2xl shadow-xl z-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold">Installer l'App</p>
                    <p class="text-sm text-emerald-100">Pour une meilleure expérience</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="installPWA()" 
                        class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-30 transition-colors">
                    Installer
                </button>
                <button @click="dismissInstallPrompt()" 
                        class="text-white hover:text-emerald-200 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Connection Status Banner -->
    <div x-show="!isOnline" 
         class="fixed top-0 left-0 right-0 bg-red-500 text-white text-center py-2 text-sm font-medium z-60">
        <div class="flex items-center justify-center space-x-2">
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Mode hors ligne - Synchronisation en attente</span>
        </div>
    </div>

    <!-- HTTPS Warning for Mobile -->
    <div x-show="showHttpsWarning && !httpsWarningDismissed" 
         class="fixed top-0 left-0 right-0 bg-amber-500 text-white px-4 py-2 text-center text-sm z-60">
        <div class="flex items-center justify-between max-w-md mx-auto">
            <span>⚠️ Caméra limitée sans HTTPS</span>
            <button @click="dismissHttpsWarning()" 
                    class="text-white hover:text-amber-200 ml-2">✕</button>
        </div>
    </div>
    
    <!-- Fixed Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50"
         :class="{ 'mt-8': !isOnline || (showHttpsWarning && !httpsWarningDismissed) }">
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
                        <h1 class="text-lg font-bold text-emerald-700">{{ auth()->user()->name }}</h1>
                        <div class="flex items-center space-x-2 text-xs">
                            <!-- Status Connection -->
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 rounded-full" 
                                     :class="isOnline ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></div>
                                <span x-text="isOnline ? 'En ligne' : 'Hors ligne'" 
                                      :class="isOnline ? 'text-green-600' : 'text-red-600'"></span>
                            </div>
                            <!-- Camera Status -->
                            <div x-show="isMobile" class="flex items-center space-x-1">
                                <span x-text="isHttps ? '📷' : '📷❌'" 
                                      :class="isHttps ? 'text-green-600' : 'text-amber-600'"></span>
                                <span class="text-gray-500" 
                                      x-text="isHttps ? 'Caméra OK' : 'Manuel only'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-2">
                    <!-- Urgent Notifications -->
                    <div x-show="urgentNotifications > 0" 
                         class="relative animate-bounce-soft">
                        <button @click="openNotifications()" 
                                class="relative p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-all animate-wiggle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"
                                 x-text="urgentNotifications"></div>
                        </button>
                    </div>

                    <!-- Quick Scanner -->
                    <button @click="$dispatch('open-scanner')" 
                            class="relative p-2 bg-emerald-100 text-emerald-600 rounded-lg hover:bg-emerald-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        <div x-show="!isHttps && isMobile" 
                             class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full text-white text-xs flex items-center justify-center">!</div>
                    </button>

                    <!-- Notifications Center -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open; loadNotifications()" 
                                class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <div x-show="unreadNotifications > 0" 
                                 class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                                 x-text="unreadNotifications"></div>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border z-50 max-h-96 overflow-hidden">
                            <div class="p-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-blue-600" x-text="`${unreadNotifications} non lues`"></span>
                                        <button @click="markAllNotificationsRead()" 
                                                x-show="unreadNotifications > 0"
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                            Tout lire
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="notification in notifications.slice(0, 8)" :key="notification.id">
                                    <div class="p-3 border-b hover:bg-gray-50 transition-colors cursor-pointer"
                                         :class="!notification.read ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''"
                                         @click="markNotificationRead(notification.id)">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                                 :class="getNotificationIconClass(notification.priority)">
                                                <span x-text="getNotificationIcon(notification.type)"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 text-sm" x-text="notification.title"></p>
                                                <p class="text-sm text-gray-600 line-clamp-2" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-500 mt-1" x-text="formatTimeAgo(notification.created_at)"></p>
                                            </div>
                                            <div x-show="!notification.read" 
                                                 class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                        </div>
                                    </div>
                                </template>
                                
                                <div x-show="notifications.length === 0" 
                                     class="p-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <p>Aucune notification</p>
                                </div>
                            </div>
                            
                            <div x-show="notifications.length > 8" 
                                 class="p-3 bg-gray-50 text-center">
                                <a href="{{ route('deliverer.notifications.index') }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800">
                                    Voir toutes les notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Wallet Balance -->
                    <div class="bg-purple-100 px-3 py-1 rounded-full cursor-pointer hover:bg-purple-200 transition-colors"
                         @click="openWalletModal()">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="text-sm font-semibold text-purple-700" x-text="formatAmount(walletBalance)"></span>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center p-1 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 bg-gradient-to-r from-emerald-600 to-green-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                            </div>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border z-50">
                            <!-- Profile Header -->
                            <div class="p-4 border-b bg-gradient-to-r from-emerald-50 to-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-emerald-600 to-green-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-sm text-gray-600">Livreur</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-xs text-emerald-600 font-medium" x-text="'Solde: ' + formatAmount(walletBalance)"></span>
                                            <div class="flex items-center space-x-1">
                                                <span x-show="isMobile" class="text-xs bg-blue-100 text-blue-600 px-1 rounded">📱</span>
                                                <span x-show="isHttps" class="text-xs bg-green-100 text-green-600 px-1 rounded">🔒</span>
                                                <span x-show="!isHttps" class="text-xs bg-amber-100 text-amber-600 px-1 rounded">⚠️</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="p-2">
                                <a href="{{ route('deliverer.profile.show') }}" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon Profil
                                </a>
                                <a href="{{ route('deliverer.wallet.index') }}" 
                                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Mon Wallet
                                </a>
                                <button @click="checkForUpdates()" 
                                        class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Vérifier mises à jour
                                </button>
                            </div>
                            
                            <!-- Logout -->
                            <div class="p-2 border-t">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
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

    <!-- Quick Stats Bar (collapsible) -->
    <div class="fixed top-16 left-0 right-0 z-40 bg-white border-b shadow-sm transition-all duration-300"
         :class="showQuickStats ? 'translate-y-0' : '-translate-y-full'">
        <div class="px-4 py-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 overflow-x-auto">
                    <!-- Stats rapides -->
                    <div class="flex items-center space-x-1 text-xs whitespace-nowrap">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-gray-600">Disponibles:</span>
                        <span class="font-bold text-blue-600" x-text="stats.available_pickups || 0"></span>
                    </div>
                    <div class="flex items-center space-x-1 text-xs whitespace-nowrap">
                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                        <span class="text-gray-600">Mes Pickups:</span>
                        <span class="font-bold text-purple-600" x-text="stats.my_pickups || 0"></span>
                    </div>
                    <div class="flex items-center space-x-1 text-xs whitespace-nowrap">
                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                        <span class="text-gray-600">Livraisons:</span>
                        <span class="font-bold text-orange-600" x-text="stats.deliveries || 0"></span>
                    </div>
                    <div class="flex items-center space-x-1 text-xs whitespace-nowrap">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-gray-600">Retours:</span>
                        <span class="font-bold text-red-600" x-text="stats.returns || 0"></span>
                    </div>
                </div>
                <button @click="toggleQuickStats()" 
                        class="p-1 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4 transform transition-transform" 
                         :class="showQuickStats ? 'rotate-180' : 'rotate-0'" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="transition-all duration-300"
         :class="showQuickStats ? 'pt-28 pb-20' : 'pt-20 pb-20'">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-5 h-16">
            <!-- Dashboard -->
            <a href="{{ route('deliverer.dashboard') }}" 
               class="nav-tab flex flex-col items-center justify-center space-y-1 relative {{ request()->routeIs('deliverer.dashboard') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-500 hover:text-emerald-600' }}">
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
                <div class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center transition-all" 
                     x-show="stats.available_pickups > 0" 
                     x-text="stats.available_pickups"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="scale-0 opacity-0"
                     x-transition:enter-end="scale-100 opacity-100"></div>
            </a>

            <!-- Scanner QR (Centre) -->
            <button @click="$dispatch('open-scanner')" 
                    class="nav-tab flex flex-col items-center justify-center space-y-1 text-gray-500 hover:text-emerald-600 relative">
                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-green-500 rounded-full flex items-center justify-center relative hover:shadow-lg transition-all">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                    <div x-show="!isHttps && isMobile" 
                         class="absolute -top-1 -right-1 w-4 h-4 bg-amber-500 text-white rounded-full text-xs flex items-center justify-center">!</div>
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
                     x-show="stats.deliveries > 0" 
                     x-text="stats.deliveries"></div>
            </a>

            <!-- Plus -->
            <button @click="showMore = !showMore" 
                    class="nav-tab flex flex-col items-center justify-center space-y-1 text-gray-500 hover:text-gray-700 relative">
                <svg class="w-5 h-5 transition-transform" :class="showMore ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-xs font-medium">Plus</span>
                <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                     x-show="(stats.returns + stats.payments) > 0" 
                     x-text="stats.returns + stats.payments"></div>
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
                    <!-- Mes Pickups -->
                    <a href="{{ route('deliverer.pickups.mine') }}" 
                       class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors relative">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-700">Mes Pickups</span>
                        <div class="absolute -top-1 -right-1 bg-purple-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                             x-show="stats.my_pickups > 0" x-text="stats.my_pickups"></div>
                    </a>

                    <!-- Retours -->
                    <a href="{{ route('deliverer.returns.index') }}" 
                       class="flex flex-col items-center p-4 bg-red-50 rounded-xl hover:bg-red-100 transition-colors relative">
                        <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">Retours</span>
                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                             x-show="stats.returns > 0" x-text="stats.returns"></div>
                    </a>

                    <!-- Wallet -->
                    <a href="{{ route('deliverer.wallet.index') }}" 
                       class="flex flex-col items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-700">Wallet</span>
                        <div class="text-xs text-purple-600 mt-1" x-text="formatAmount(walletBalance)"></div>
                    </a>

                    <!-- Paiements -->
                    <a href="{{ route('deliverer.payments.index') }}" 
                       class="flex flex-col items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors relative">
                        <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-green-700">Paiements</span>
                        <div class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" 
                             x-show="stats.payments > 0" x-text="stats.payments"></div>
                    </a>

                    <!-- Scanner -->
                    <button @click="$dispatch('open-scanner'); showMore = false" 
                            class="flex flex-col items-center p-4 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors relative">
                        <svg class="w-8 h-8 text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                        </svg>
                        <span class="text-sm font-medium text-emerald-700">Scanner QR</span>
                        <div class="text-xs text-emerald-600 mt-1 text-center">
                            <div x-show="isHttps">📱 QR + 📊 Codes</div>
                            <div x-show="!isHttps && isMobile">Mode manuel</div>
                        </div>
                    </button>

                    <!-- Recharge Client -->
                    <a href="{{ route('deliverer.client-topup.index') }}" 
                       class="flex flex-col items-center p-4 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition-colors">
                        <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.467-.22-2.121-.659-1.172-.879-1.172-2.303 0-3.182s3.07-.879 4.242 0L15 9m-3 9v1"/>
                        </svg>
                        <span class="text-sm font-medium text-yellow-700">Recharge</span>
                    </a>
                </div>

                <!-- System Info -->
                <div x-show="!isHttps && isMobile" class="mt-4 p-3 bg-amber-50 rounded-xl">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800">Mode Caméra Limité</p>
                            <p class="text-xs text-amber-700 mt-1">
                                Pour utiliser la caméra, l'application nécessite HTTPS. Utilisez le mode manuel ou demandez l'activation HTTPS.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Modal -->
    <div x-show="showWalletModal" x-transition 
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div x-show="showWalletModal" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Mon Wallet</h3>
                <button @click="showWalletModal = false" 
                        class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="text-center mb-6">
                <div class="text-3xl font-bold text-purple-600 mb-2" x-text="formatAmount(walletBalance)"></div>
                <p class="text-sm text-gray-600">Solde disponible</p>
                <p class="text-xs text-gray-500 mt-1">Wallet = Espèces physiques</p>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <span class="text-sm text-green-700">Dernière collecte COD</span>
                    <span class="font-medium text-green-600" x-text="formatAmount(lastCodAmount)"></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <span class="text-sm text-blue-700">Transactions aujourd'hui</span>
                    <span class="font-medium text-blue-600" x-text="todayTransactions"></span>
                </div>
            </div>
            
            <div class="flex space-x-3 mt-6">
                <a href="{{ route('deliverer.wallet.index') }}" 
                   class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-xl text-center font-medium hover:bg-purple-700 transition-colors">
                    Voir Détails
                </a>
                <button @click="showWalletModal = false" 
                        class="px-4 py-3 text-gray-600 hover:text-gray-800">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Scanner QR Component -->
    @include('components.scanner-qr-final')

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition class="fixed top-24 left-4 right-4 bg-emerald-500 text-white px-4 py-3 rounded-xl shadow-lg z-40 mx-auto max-w-md">
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

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition class="fixed top-24 left-4 right-4 bg-red-500 text-white px-4 py-3 rounded-xl shadow-lg z-40 mx-auto max-w-md">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
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
                // État principal
                showMore: false,
                showQuickStats: true,
                showWalletModal: false,
                showInstallPrompt: false,
                installPromptDismissed: localStorage.getItem('install_prompt_dismissed') === 'true',
                httpsWarningDismissed: localStorage.getItem('https_warning_dismissed') === 'true',
                
                // Status système
                isOnline: navigator.onLine,
                isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
                isHttps: location.protocol === 'https:',
                
                // PWA
                pwaInstallPrompt: null,
                
                // Données
                walletBalance: 0.000,
                lastCodAmount: 0.000,
                todayTransactions: 0,
                notifications: [],
                unreadNotifications: 0,
                urgentNotifications: 0,
                
                // Stats temps réel
                stats: {
                    available_pickups: 0,
                    my_pickups: 0,
                    deliveries: 0,
                    returns: 0,
                    payments: 0,
                    deliveries_today: 0,
                    cod_collected_today: 0
                },

                // Variables pour notifications
                showHttpsWarning: false,

                init() {
                    console.log('DelivererApp Init - Mobile:', this.isMobile, 'HTTPS:', this.isHttps);
                    
                    // Initialize states
                    this.showHttpsWarning = this.isMobile && !this.isHttps && !this.httpsWarningDismissed;
                    
                    // Charger données initiales
                    this.loadInitialData();
                    
                    // Event listeners
                    this.setupEventListeners();
                    
                    // PWA setup
                    this.setupPWA();
                    
                    // Auto refresh
                    this.startAutoRefresh();
                    
                    // Quick stats toggle state
                    this.showQuickStats = localStorage.getItem('quick_stats_visible') !== 'false';
                },

                async loadInitialData() {
                    await Promise.all([
                        this.loadStats(),
                        this.loadWallet(),
                        this.loadNotifications()
                    ]);
                },

                setupEventListeners() {
                    // Connection status
                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        console.log('Connexion rétablie');
                        this.syncOfflineData();
                    });
                    
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                        console.log('Connexion perdue');
                    });

                    // PWA install prompt
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.pwaInstallPrompt = e;
                        this.showInstallPrompt = !this.installPromptDismissed;
                    });

                    // Page visibility for refresh
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden && this.isOnline) {
                            this.loadInitialData();
                        }
                    });
                },

                setupPWA() {
                    // Register service worker
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js')
                            .then(registration => {
                                console.log('SW registered:', registration);
                            })
                            .catch(error => {
                                console.log('SW registration failed:', error);
                            });
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/deliverer/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats = { ...this.stats, ...data };
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

                async loadNotifications() {
                    try {
                        const response = await fetch('/deliverer/api/notifications/recent');
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications = data.notifications || [];
                            this.updateNotificationCounts();
                        }
                    } catch (error) {
                        console.error('Erreur chargement notifications:', error);
                    }
                },

                updateNotificationCounts() {
                    this.unreadNotifications = this.notifications.filter(n => !n.read).length;
                    this.urgentNotifications = this.notifications.filter(n => !n.read && n.priority === 'URGENT').length;
                },

                async markNotificationRead(notificationId) {
                    try {
                        const response = await fetch(`/deliverer/notifications/${notificationId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) {
                                notification.read = true;
                                notification.read_at = new Date().toISOString();
                                this.updateNotificationCounts();
                            }
                        }
                    } catch (error) {
                        console.error('Erreur mark notification:', error);
                    }
                },

                async markAllNotificationsRead() {
                    try {
                        const response = await fetch('/deliverer/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            this.notifications.forEach(n => {
                                n.read = true;
                                n.read_at = new Date().toISOString();
                            });
                            this.updateNotificationCounts();
                        }
                    } catch (error) {
                        console.error('Erreur mark all notifications:', error);
                    }
                },

                startAutoRefresh() {
                    // Refresh stats toutes les 30 secondes
                    setInterval(() => {
                        if (this.isOnline && !document.hidden) {
                            this.loadStats();
                        }
                    }, 30000);

                    // Refresh wallet et notifications toutes les 60 secondes
                    setInterval(() => {
                        if (this.isOnline && !document.hidden) {
                            this.loadWallet();
                            this.loadNotifications();
                        }
                    }, 60000);
                },

                // PWA Actions
                async installPWA() {
                    if (this.pwaInstallPrompt) {
                        this.pwaInstallPrompt.prompt();
                        const { outcome } = await this.pwaInstallPrompt.userChoice;
                        console.log('PWA install outcome:', outcome);
                        this.pwaInstallPrompt = null;
                        this.showInstallPrompt = false;
                    }
                },

                dismissInstallPrompt() {
                    this.showInstallPrompt = false;
                    this.installPromptDismissed = true;
                    localStorage.setItem('install_prompt_dismissed', 'true');
                },

                dismissHttpsWarning() {
                    this.showHttpsWarning = false;
                    this.httpsWarningDismissed = true;
                    localStorage.setItem('https_warning_dismissed', 'true');
                },

                // UI Actions
                toggleQuickStats() {
                    this.showQuickStats = !this.showQuickStats;
                    localStorage.setItem('quick_stats_visible', this.showQuickStats);
                },

                openWalletModal() {
                    this.showWalletModal = true;
                    // Charger données récentes du wallet
                    this.loadWalletDetails();
                },

                async loadWalletDetails() {
                    try {
                        const response = await fetch('/deliverer/api/wallet/recent-transactions');
                        if (response.ok) {
                            const data = await response.json();
                            this.lastCodAmount = data.last_cod_amount || 0;
                            this.todayTransactions = data.today_count || 0;
                        }
                    } catch (error) {
                        console.error('Erreur wallet details:', error);
                    }
                },

                openNotifications() {
                    // Fonction pour ouvrir le centre de notifications
                    window.location.href = '/deliverer/notifications';
                },

                async checkForUpdates() {
                    if ('serviceWorker' in navigator) {
                        const registration = await navigator.serviceWorker.getRegistration();
                        if (registration) {
                            registration.update();
                            this.showToast('Vérification des mises à jour...', 'info');
                        }
                    }
                },

                async syncOfflineData() {
                    // Synchroniser les données offline quand connexion revient
                    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
                        const registration = await navigator.serviceWorker.ready;
                        await registration.sync.register('sync-offline-data');
                    }
                },

                // Notification helpers
                getNotificationIcon(type) {
                    const icons = {
                        'COMPLAINT_NEW': '📝',
                        'COMPLAINT_URGENT': '🚨',
                        'WITHDRAWAL_REQUEST': '💰',
                        'WALLET_HIGH_BALANCE': '💰',
                        'COD_MODIFICATION': '✏️',
                        'PACKAGE_BLOCKED': '📦',
                        'SYSTEM_ALERT': '⚠️',
                        default: '🔔'
                    };
                    return icons[type] || icons.default;
                },

                getNotificationIconClass(priority) {
                    const classes = {
                        'LOW': 'bg-gray-100 text-gray-600',
                        'NORMAL': 'bg-blue-100 text-blue-600',
                        'HIGH': 'bg-orange-100 text-orange-600',
                        'URGENT': 'bg-red-100 text-red-600'
                    };
                    return classes[priority] || classes.NORMAL;
                },

                // Utility methods
                formatAmount(amount) {
                    return parseFloat(amount || 0).toFixed(3) + ' DT';
                },

                formatTimeAgo(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
                    
                    if (diffInMinutes < 1) return 'À l\'instant';
                    if (diffInMinutes < 60) return `Il y a ${diffInMinutes}min`;
                    if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)}h`;
                    return `Il y a ${Math.floor(diffInMinutes / 1440)}j`;
                },

                showToast(message, type = 'success') {
                    const toast = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-emerald-500' : 
                                   type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                    toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
                    toast.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : type === 'error' ? 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                            </svg>
                            <span>${message}</span>
                        </div>
                    `;
                    
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateY(-20px)';
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>