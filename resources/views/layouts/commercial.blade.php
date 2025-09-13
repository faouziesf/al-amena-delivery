<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Al-Amena Delivery') - Espace Commercial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pale-purple': '#E0D4FF',
                        'purple': {
                            50: '#F5F3FF', 100: '#EDE9FE', 200: '#DDD6FE', 300: '#C4B5FD',
                            400: '#A78BFA', 500: '#8B5CF6', 600: '#7C3AED', 700: '#6D28D9',
                            800: '#5B21B6', 900: '#4C1D95'
                        }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-purple-50 to-purple-100 min-h-screen" x-data="commercialApp()">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-purple-200">
            <!-- Logo & Brand -->
            <div class="p-6 border-b border-purple-200 bg-gradient-to-r from-purple-600 to-purple-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Al-Amena</h1>
                        <p class="text-xs text-purple-200">Espace Commercial</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
                <a href="{{ route('commercial.dashboard') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.dashboard') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('commercial.clients.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.clients.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    <span class="font-medium">Clients</span>
                    <div class="ml-auto" x-show="stats.pending_clients > 0">
                        <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_clients"></span>
                    </div>
                </a>

                <a href="{{ route('commercial.complaints.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.complaints.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <span class="font-medium">Réclamations</span>
                    <div class="ml-auto flex space-x-1">
                        <span x-show="stats.urgent_complaints > 0" class="bg-red-500 text-white text-xs px-2 py-1 rounded-full animate-pulse" x-text="stats.urgent_complaints"></span>
                        <span x-show="stats.pending_complaints > 0 && stats.urgent_complaints == 0" class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_complaints"></span>
                    </div>
                </a>

                <a href="{{ route('commercial.withdrawals.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.withdrawals.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-medium">Retraits</span>
                    <div class="ml-auto" x-show="stats.pending_withdrawals > 0">
                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_withdrawals"></span>
                    </div>
                </a>

                <a href="{{ route('commercial.deliverers.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.deliverers.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="font-medium">Livreurs</span>
                    <div class="ml-auto" x-show="stats.high_balance_deliverers > 0">
                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.high_balance_deliverers"></span>
                    </div>
                </a>

                <a href="{{ route('commercial.packages.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.packages.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="font-medium">Colis</span>
                    <div class="ml-auto" x-show="stats.blocked_packages > 0">
                        <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.blocked_packages"></span>
                    </div>
                </a>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-4"></div>

                <a href="{{ route('commercial.notifications.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-lg transition-all duration-200 group {{ request()->routeIs('commercial.notifications.*') ? 'bg-purple-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                    </svg>
                    <span class="font-medium">Notifications</span>
                    <div class="ml-auto" x-show="notifications.unread_count > 0">
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full animate-bounce" x-text="notifications.unread_count"></span>
                    </div>
                </a>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-purple-200 bg-gray-50">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-purple-700 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Commercial</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <button @click="showUserMenu = !showUserMenu" 
                            class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-white rounded-lg transition-colors">
                        <span>Mon Compte</span>
                        <svg class="w-4 h-4 transition-transform" :class="showUserMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="showUserMenu" x-transition class="space-y-1 pl-3">
                        <a href="#" class="block px-3 py-1 text-xs text-gray-600 hover:text-purple-600">Profil</a>
                        <a href="#" class="block px-3 py-1 text-xs text-gray-600 hover:text-purple-600">Paramètres</a>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-gray-600">@yield('page-description', 'Gestion commerciale Al-Amena Delivery')</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Quick Stats -->
                        <div class="hidden lg:flex items-center space-x-6 text-sm">
                            <div class="flex items-center space-x-2" x-show="stats.urgent_complaints > 0">
                                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                <span class="text-red-600 font-medium" x-text="stats.urgent_complaints + ' urgentes'"></span>
                            </div>
                            <div class="text-gray-500">
                                <span x-text="stats.packages_today"></span> colis aujourd'hui
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="relative p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-100 rounded-lg transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                                </svg>
                                <span x-show="notifications.unread_count > 0" 
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse"
                                      x-text="notifications.unread_count"></span>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
                                <div class="p-4 border-b">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                                        <button @click="markAllAsRead()" class="text-sm text-purple-600 hover:text-purple-800">
                                            Tout marquer lu
                                        </button>
                                    </div>
                                </div>
                                <div class="max-h-96 overflow-y-auto" x-html="notificationsList">
                                    <p class="p-4 text-gray-500 text-center">Chargement...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto">
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
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
             class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm">
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

    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm">
            <div class="flex items-start space-x-2">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
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

    <!-- Modals & Components -->
    @stack('modals')

    <!-- Scripts -->
    <script>
        function commercialApp() {
            return {
                showUserMenu: false,
                stats: {
                    total_clients: 0,
                    pending_clients: 0,
                    pending_complaints: 0,
                    urgent_complaints: 0,
                    pending_withdrawals: 0,
                    high_balance_deliverers: 0,
                    packages_today: 0,
                    blocked_packages: 0
                },
                notifications: {
                    unread_count: 0,
                    urgent_count: 0
                },
                notificationsList: '<p class="p-4 text-gray-500 text-center">Chargement...</p>',

                init() {
                    this.loadStats();
                    this.loadNotifications();
                    
                    // Auto-refresh every 30 seconds
                    setInterval(() => {
                        this.loadStats();
                        this.loadNotifications();
                    }, 30000);
                },

                async loadStats() {
                    try {
                        const response = await fetch('/commercial/api/dashboard-stats');
                        if (response.ok) {
                            this.stats = await response.json();
                        }
                    } catch (error) {
                        console.error('Erreur chargement stats:', error);
                    }
                },

                async loadNotifications() {
                    try {
                        const response = await fetch('/commercial/notifications/api/unread-count');
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications.unread_count = data.unread_count;
                            this.notifications.urgent_count = data.urgent_count;
                        }

                        // Charger la liste des notifications récentes
                        const recentResponse = await fetch('/commercial/notifications/api/recent');
                        if (recentResponse.ok) {
                            const notifications = await recentResponse.json();
                            this.updateNotificationsList(notifications);
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
                        <div class="p-4 border-b hover:bg-gray-50 ${notification.read_at ? 'opacity-75' : ''}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900">${notification.title}</p>
                                    <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                                    <p class="text-xs text-gray-500 mt-2">${notification.created_at_human}</p>
                                </div>
                                <div class="ml-2 flex flex-col items-end space-y-1">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full ${notification.priority_color}">
                                        ${notification.priority_display}
                                    </span>
                                    ${!notification.read_at ? `
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
                        const response = await fetch('/commercial/notifications/api/mark-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ mark_all: true })
                        });
                        
                        if (response.ok) {
                            this.loadNotifications();
                        }
                    } catch (error) {
                        console.error('Erreur marquage notifications:', error);
                    }
                }
            }
        }

        // Global functions
        async function markNotificationRead(notificationId) {
            try {
                const response = await fetch(`/commercial/notifications/mark-read/${notificationId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    // Trigger notification reload from Alpine component
                    document.querySelector('[x-data*="commercialApp"]').__x.$data.loadNotifications();
                }
            } catch (error) {
                console.error('Erreur marquage notification:', error);
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>

    @stack('scripts')
</body>
</html>