<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Al-Amena Delivery') - Espace Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-50 min-h-screen" x-data="clientApp()">
    
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-purple-100 sticky top-0 z-40">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-purple-50 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">Al-Amena</h1>
                            <p class="text-xs text-gray-500">Espace Client</p>
                        </div>
                    </div>
                </div>

                <!-- Right side - User info and actions -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                            </svg>
                            <span x-show="notifications.unread_count > 0" 
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse"
                                  x-text="notifications.unread_count"></span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border z-50">
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

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center space-x-3 p-2 rounded-xl hover:bg-purple-50 transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900 truncate max-w-32">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">Client</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border z-50">
                            <div class="p-4 border-b bg-gradient-to-r from-purple-50 to-indigo-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-2">
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon Profil
                                </a>
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 rounded-lg transition-colors">
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

    <!-- Main Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-xl border-r border-purple-100 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 mt-16 lg:mt-0" 
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Navigation Menu -->
            <nav class="p-4 space-y-2 h-full overflow-y-auto">
                <a href="{{ route('client.dashboard') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.dashboard') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('client.packages.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.packages.*') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="font-medium">Mes Colis</span>
                    <div class="ml-auto" x-show="stats.in_progress_packages > 0">
                        <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.in_progress_packages"></span>
                    </div>
                </a>

                <a href="{{ route('client.packages.create') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.packages.create') ? 'bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-700 shadow-sm' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="font-medium">Nouveau Colis</span>
                </a>

                <a href="{{ route('client.wallet.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.wallet.*') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span class="font-medium">Mon Portefeuille</span>
                </a>

                <a href="{{ route('client.withdrawals') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.withdrawals*') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-medium">Mes Retraits</span>
                    <div class="ml-auto" x-show="stats.pending_withdrawals > 0">
                        <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_withdrawals"></span>
                    </div>
                </a>

                <a href="{{ route('client.complaints.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.complaints.*') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <span class="font-medium">Réclamations</span>
                    <div class="ml-auto" x-show="stats.pending_complaints > 0">
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full" x-text="stats.pending_complaints"></span>
                    </div>
                </a>

                <a href="{{ route('client.notifications.index') }}" 
                   class="nav-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('client.notifications.*') ? 'bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 shadow-sm' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                    </svg>
                    <span class="font-medium">Notifications</span>
                    <div class="ml-auto" x-show="notifications.unread_count > 0">
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full animate-bounce" x-text="notifications.unread_count"></span>
                    </div>
                </a>
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
        <div class="flex-1 lg:ml-0">
            <!-- Page Content -->
            <main class="min-h-screen">
                @yield('content')
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
             class="fixed top-20 right-4 bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-lg z-50 max-w-sm">
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
             class="fixed top-20 right-4 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-50 max-w-sm">
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

    <!-- Scripts -->
    <script>
        function clientApp() {
            return {
                sidebarOpen: false,
                stats: {
                    in_progress_packages: 0,
                    pending_complaints: 0,
                    pending_withdrawals: 0
                },
                notifications: {
                    unread_count: 0
                },
                notificationsList: '<p class="p-4 text-gray-500 text-center">Chargement...</p>',

                init() {
                    this.loadStats();
                    this.loadNotifications();
                    
                    // Auto-refresh every 60 seconds
                    setInterval(() => {
                        this.loadStats();
                        this.loadNotifications();
                    }, 60000);

                    // Handle responsive sidebar
                    this.handleResize();
                    window.addEventListener('resize', () => this.handleResize());
                },

                handleResize() {
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = false;
                    }
                },

                async loadStats() {
                    try {
                        const response = await fetch('/client/api/dashboard-stats');
                        if (response.ok) {
                            const data = await response.json();
                            this.stats.in_progress_packages = data.in_progress_packages || 0;
                            this.stats.pending_complaints = data.pending_complaints || 0;
                            this.stats.pending_withdrawals = data.pending_withdrawals || 0;
                        }
                    } catch (error) {
                        console.error('Erreur chargement stats:', error);
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
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 right-4 ${bgColor} text-white px-6 py-3 rounded-xl shadow-lg z-50 transform transition-all duration-300`;
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