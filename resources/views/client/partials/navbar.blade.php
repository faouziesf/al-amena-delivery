<!-- Top Navigation Bar -->
<div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-purple-200 bg-white/80 backdrop-blur-lg px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    
    <!-- Mobile menu button -->
    <button type="button" 
            class="-m-2.5 p-2.5 text-purple-700 lg:hidden" 
            @click="sidebarOpen = true">
        <span class="sr-only">Ouvrir le menu</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-purple-200 lg:hidden" aria-hidden="true"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        
        <!-- Search bar (optionnel) -->
        <div class="relative flex flex-1 max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                </svg>
            </div>
            <input type="text" 
                   name="search" 
                   id="search" 
                   class="block w-full rounded-xl border-0 py-2 pl-10 pr-3 text-purple-900 ring-1 ring-inset ring-purple-200 placeholder:text-purple-400 focus:ring-2 focus:ring-inset focus:ring-purple-500 sm:text-sm sm:leading-6 bg-white/70"
                   placeholder="Rechercher un colis..." />
        </div>
        
        <div class="flex items-center gap-x-4 lg:gap-x-6">
            
            <!-- Quick Stats (Desktop only) -->
            <div class="hidden lg:flex items-center gap-x-4 text-sm">
                <div class="flex items-center gap-x-2 bg-purple-100 rounded-lg px-3 py-1.5">
                    <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5" />
                    </svg>
                    <span class="font-medium text-purple-900">
                        {{ auth()->user()->sentPackages()->inProgress()->count() }} en cours
                    </span>
                </div>
                
                <div class="flex items-center gap-x-2 bg-green-100 rounded-lg px-3 py-1.5">
                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12" />
                    </svg>
                    <span class="font-medium text-green-900" id="navbar-wallet-balance">
                        {{ number_format(auth()->user()->wallet_balance, 3) }} DT
                    </span>
                </div>
            </div>

            <!-- Notifications Dropdown -->
            <div x-data="{ open: false, notifications: [], loading: false }" class="relative">
                <button type="button" 
                        class="relative -m-2.5 p-2.5 text-purple-600 hover:text-purple-700 transition-colors duration-200"
                        @click="open = !open; if(open && notifications.length === 0) loadNotifications()">
                    <span class="sr-only">Voir les notifications</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    
                    <!-- Badge notification -->
                    @php
                        $unreadCount = auth()->user()->notifications()->where('read', false)->count();
                    @endphp
                    <span id="notifications-badge" 
                          class="absolute -top-0.5 -right-0.5 h-5 w-5 rounded-full bg-red-500 text-xs font-medium text-white flex items-center justify-center {{ $unreadCount > 0 ? '' : 'hidden' }}">
                        {{ $unreadCount }}
                    </span>
                </button>
                
                <!-- Dropdown notifications -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     @click.outside="open = false"
                     class="absolute right-0 z-10 mt-2.5 w-96 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-purple-200 focus:outline-none">
                    
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-purple-100 bg-purple-50 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-purple-900">Notifications</h3>
                            <button @click="markAllAsRead()" 
                                    class="text-xs text-purple-600 hover:text-purple-700 font-medium">
                                Tout marquer lu
                            </button>
                        </div>
                    </div>
                    
                    <!-- Contenu notifications -->
                    <div class="max-h-96 overflow-y-auto">
                        <div x-show="loading" class="px-4 py-6 text-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mx-auto"></div>
                            <p class="text-sm text-gray-500 mt-2">Chargement...</p>
                        </div>
                        
                        <template x-for="notification in notifications" :key="notification.id">
                            <div class="px-4 py-3 hover:bg-purple-50 cursor-pointer transition-colors duration-200"
                                 @click="markAsRead(notification.id)"
                                 :class="{ 'bg-purple-50': !notification.read }">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <div class="h-3 w-3 mt-1.5 rounded-full"
                                             :class="notification.read ? 'bg-gray-300' : 'bg-purple-500'"></div>
                                    </div>
                                    <div class="ml-3 w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                        <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                        <p class="text-xs text-gray-400 mt-2" x-text="formatRelativeTime(notification.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                            <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-5 5v-5zM4 19h5V5H4v14zm8-14v9.586l-2.293-2.293a1 1 0 00-1.414 1.414L12 17.414l3.707-3.707a1 1 0 00-1.414-1.414L12 14.586V5h-1z" />
                            </svg>
                            Aucune notification
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="border-t border-purple-100 px-4 py-2 bg-purple-50 rounded-b-xl">
                        <a href="{{ route('client.notifications') }}" 
                           class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                            Voir toutes les notifications →
                        </a>
                    </div>
                </div>

                <script>
                    function loadNotifications() {
                        this.loading = true;
                        fetch('/client/api/notifications/recent', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.notifications = data.notifications || [];
                        })
                        .catch(error => console.error('Erreur:', error))
                        .finally(() => this.loading = false);
                    }

                    function markAsRead(notificationId) {
                        fetch(`/client/notifications/${notificationId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(() => {
                            // Mettre à jour localement
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) notification.read = true;
                            
                            // Mettre à jour le badge
                            const badge = document.querySelector('#notifications-badge');
                            const currentCount = parseInt(badge.textContent) - 1;
                            if (currentCount <= 0) {
                                badge.classList.add('hidden');
                            } else {
                                badge.textContent = currentCount;
                            }
                        });
                    }

                    function markAllAsRead() {
                        fetch('/client/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(() => {
                            this.notifications.forEach(n => n.read = true);
                            document.querySelector('#notifications-badge').classList.add('hidden');
                        });
                    }

                    function formatRelativeTime(dateString) {
                        const date = new Date(dateString);
                        const now = new Date();
                        const diffInSeconds = Math.floor((now - date) / 1000);

                        if (diffInSeconds < 60) return 'À l\'instant';
                        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}min`;
                        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`;
                        return `${Math.floor(diffInSeconds / 86400)}j`;
                    }
                </script>
            </div>

            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-purple-200" aria-hidden="true"></div>

            <!-- Profile dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button type="button" 
                        class="relative -m-1.5 flex items-center p-1.5 hover:bg-purple-100 rounded-lg transition-colors duration-200" 
                        @click="open = !open">
                    <span class="sr-only">Ouvrir le menu utilisateur</span>
                    <div class="h-8 w-8 rounded-lg bg-purple-gradient flex items-center justify-center shadow-md">
                        <span class="text-sm font-bold text-white">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <span class="hidden lg:flex lg:items-center">
                        <span class="ml-4 text-sm font-semibold leading-6 text-purple-900">{{ auth()->user()->name }}</span>
                        <svg class="ml-2 h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     @click.outside="open = false"
                     class="absolute right-0 z-10 mt-2.5 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-purple-200 focus:outline-none">
                    
                    <div class="py-2">
                        <div class="px-3 py-2 border-b border-purple-100">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
                            <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Mon Profil
                        </a>
                        
                        <a href="{{ route('client.wallet') }}" 
                           class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-200">
                            <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                            </svg>
                            Mon Wallet
                        </a>
                        
                        <div class="border-t border-purple-100 my-1"></div>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="flex w-full items-center px-3 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-200">
                                <svg class="h-4 w-4 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
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