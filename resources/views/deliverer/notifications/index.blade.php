@extends('layouts.deliverer')

@section('title', 'Notifications')
@section('page-title', 'Centre de Notifications')

@section('content')
<div class="bg-gradient-to-br from-purple-50 to-purple-100" x-data="notificationsApp()">
    
    <!-- Header avec stats -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                    </svg>
                    Notifications
                </h1>
                <p class="text-gray-600">Restez informé de vos activités de livraison</p>
            </div>
            
            <!-- Actions rapides -->
            <div class="flex space-x-2">
                <button @click="markAllAsRead()" 
                        :disabled="stats.unread === 0"
                        class="px-4 py-2 bg-purple-300 text-purple-800 rounded-lg hover:bg-purple-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tout marquer lu
                </button>
                
                <button @click="refreshNotifications()" 
                        :disabled="loading"
                        class="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Stats rapides -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Total non lues -->
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm">Non lues</p>
                        <p class="text-2xl font-bold" x-text="stats.unread"></p>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-lg p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Urgentes -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm">Urgentes</p>
                        <p class="text-2xl font-bold" x-text="stats.urgent"></p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-lg p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Aujourd'hui -->
            <div class="bg-gradient-to-r from-purple-300 to-purple-400 rounded-xl p-4 text-purple-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-600 text-sm">Aujourd'hui</p>
                        <p class="text-2xl font-bold" x-text="stats.today"></p>
                    </div>
                    <div class="bg-purple-200 bg-opacity-50 rounded-lg p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Cette semaine -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Cette semaine</p>
                        <p class="text-2xl font-bold" x-text="stats.this_week"></p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-lg p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white shadow-sm border-b px-4 py-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            
            <!-- Filtres rapides -->
            <div class="flex flex-wrap gap-2">
                <button @click="currentFilter = 'all'" 
                        :class="currentFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    Toutes (<span x-text="stats.total"></span>)
                </button>
                
                <button @click="currentFilter = 'unread'" 
                        :class="currentFilter === 'unread' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    Non lues (<span x-text="stats.unread"></span>)
                </button>
                
                <button @click="currentFilter = 'urgent'" 
                        :class="currentFilter === 'urgent' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    Urgentes (<span x-text="stats.urgent"></span>)
                </button>
                
                <button @click="currentFilter = 'today'" 
                        :class="currentFilter === 'today' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                    Aujourd'hui (<span x-text="stats.today"></span>)
                </button>
            </div>

            <!-- Recherche et filtres avancés -->
            <div class="flex items-center space-x-3">
                <!-- Recherche -->
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery"
                           @input.debounce.300ms="filterNotifications()"
                           placeholder="Rechercher..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Filtre par type -->
                <select x-model="typeFilter" @change="filterNotifications()" 
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous types</option>
                    <option value="NEW_PICKUP_AVAILABLE">Nouveaux pickups</option>
                    <option value="DELIVERY_URGENT">Livraisons urgentes</option>
                    <option value="PAYMENT_ASSIGNED">Paiements assignes</option>
                    <option value="WALLET_HIGH_BALANCE">Wallet eleve</option>
                    <option value="COD_MODIFICATION">COD modifie</option>
                    <option value="SYSTEM_UPDATE">Mises a jour</option>
                </select>

                <!-- Filtre par priorite -->
                <select x-model="priorityFilter" @change="filterNotifications()" 
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Toutes priorites</option>
                    <option value="URGENT">Urgente</option>
                    <option value="HIGH">Haute</option>
                    <option value="NORMAL">Normale</option>
                    <option value="LOW">Basse</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="p-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- Loading state -->
            <div x-show="loading" class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-3 text-gray-500">
                    <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"/>
                    </svg>
                    <span>Chargement des notifications...</span>
                </div>
            </div>

            <!-- Empty state -->
            <div x-show="!loading && filteredNotifications.length === 0" class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune notification</h3>
                <p class="text-gray-500">
                    <span x-show="currentFilter === 'all'">Vous n'avez aucune notification pour le moment.</span>
                    <span x-show="currentFilter === 'unread'">Toutes vos notifications sont déjà lues.</span>
                    <span x-show="currentFilter === 'urgent'">Aucune notification urgente.</span>
                    <span x-show="currentFilter === 'today'">Aucune notification aujourd'hui.</span>
                </p>
            </div>

            <!-- Liste des notifications -->
            <div x-show="!loading && filteredNotifications.length > 0" class="divide-y divide-gray-200">
                <template x-for="notification in filteredNotifications" :key="notification.id">
                    <div class="p-4 hover:bg-gray-50 transition-colors"
                         :class="!notification.read ? 'bg-blue-50 border-l-4 border-blue-500' : ''">
                        
                        <div class="flex items-start justify-between">
                            <!-- Contenu principal -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <!-- Icône du type -->
                                    <div class="flex-shrink-0">
                                        <div :class="getTypeIcon(notification.type).bg" 
                                             class="w-10 h-10 rounded-lg flex items-center justify-center">
                                            <span x-text="getTypeIcon(notification.type).icon" class="text-lg"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Titre et priorité -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-sm font-semibold text-gray-900 truncate" 
                                                x-text="notification.title"></h4>
                                            
                                            <!-- Badge priorité -->
                                            <span :class="getPriorityBadge(notification.priority).class" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                                <span x-text="getPriorityBadge(notification.priority).text"></span>
                                            </span>
                                            
                                            <!-- Badge non lu -->
                                            <span x-show="!notification.read" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Non lu
                                            </span>
                                        </div>
                                        
                                        <!-- Date -->
                                        <p class="text-xs text-gray-500 mt-1" x-text="formatDate(notification.created_at)"></p>
                                    </div>
                                </div>
                                
                                <!-- Message -->
                                <p class="text-sm text-gray-700 mb-3" x-text="notification.message"></p>
                                
                                <!-- Données supplémentaires -->
                                <div x-show="notification.data && Object.keys(notification.data).length > 0" 
                                     class="bg-gray-50 rounded-lg p-3 mb-3">
                                    <template x-if="notification.data.package_code">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-xs font-medium text-gray-500">Colis:</span>
                                            <span class="text-xs font-mono bg-white px-2 py-1 rounded border" 
                                                  x-text="notification.data.package_code"></span>
                                        </div>
                                    </template>
                                    
                                    <template x-if="notification.data.amount">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-xs font-medium text-gray-500">Montant:</span>
                                            <span class="text-xs font-semibold text-green-600" 
                                                  x-text="notification.data.amount + ' DT'"></span>
                                        </div>
                                    </template>
                                    
                                    <template x-if="notification.data.deliverer_name">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium text-gray-500">Livreur:</span>
                                            <span class="text-xs" x-text="notification.data.deliverer_name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex-shrink-0 ml-4">
                                <div class="flex items-center space-x-2">
                                    <!-- Action principale (si URL disponible) -->
                                    <template x-if="notification.action_url">
                                        <a :href="notification.action_url" 
                                           class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            Voir
                                        </a>
                                    </template>
                                    
                                    <!-- Marquer comme lu -->
                                    <template x-if="!notification.read">
                                        <button @click="markAsRead(notification.id)" 
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Lu
                                        </button>
                                    </template>
                                    
                                    <!-- Supprimer -->
                                    <button @click="deleteNotification(notification.id)" 
                                            class="inline-flex items-center px-3 py-1.5 border border-red-300 text-red-700 text-xs font-medium rounded-lg hover:bg-red-50 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pagination -->
            <div x-show="!loading && filteredNotifications.length > 0" class="border-t bg-gray-50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Affichage de <span x-text="(currentPage - 1) * perPage + 1"></span> à 
                        <span x-text="Math.min(currentPage * perPage, filteredNotifications.length)"></span> sur 
                        <span x-text="filteredNotifications.length"></span> notifications
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button @click="previousPage()" 
                                :disabled="currentPage === 1"
                                class="px-3 py-1 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Précédent
                        </button>
                        
                        <span class="px-3 py-1 text-sm font-medium">
                            Page <span x-text="currentPage"></span> sur <span x-text="totalPages"></span>
                        </span>
                        
                        <button @click="nextPage()" 
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Suivant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
</div>

<script>
function notificationsApp() {
    return {
        // État
        loading: true,
        notifications: {!! json_encode(isset($notifications) ? $notifications->items() : []) !!},
        filteredNotifications: [],
        
        // Filtres
        currentFilter: 'all',
        searchQuery: '',
        typeFilter: '',
        priorityFilter: '',
        
        // Pagination
        currentPage: 1,
        perPage: 20,
        totalPages: 1,
        
        // Stats
        stats: {!! json_encode($stats ?? ['total' => 0, 'unread' => 0, 'urgent' => 0, 'today' => 0, 'this_week' => 0]) !!},

        init() {
            this.filterNotifications();
            this.loading = false;
            
            // Auto-refresh toutes les 30 secondes
            setInterval(() => {
                this.refreshNotifications();
            }, 30000);
        },

        // Filtrage des notifications
        filterNotifications() {
            if (!Array.isArray(this.notifications)) {
                this.notifications = [];
            }
            let filtered = [...this.notifications];
            
            // Filtre principal
            switch (this.currentFilter) {
                case 'unread':
                    filtered = filtered.filter(n => !n.read);
                    break;
                case 'urgent':
                    filtered = filtered.filter(n => n.priority === 'URGENT');
                    break;
                case 'today':
                    const today = new Date().toDateString();
                    filtered = filtered.filter(n => new Date(n.created_at).toDateString() === today);
                    break;
            }
            
            // Filtre par recherche
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(n => 
                    n.title.toLowerCase().includes(query) ||
                    n.message.toLowerCase().includes(query) ||
                    (n.data && JSON.stringify(n.data).toLowerCase().includes(query))
                );
            }
            
            // Filtre par type
            if (this.typeFilter) {
                filtered = filtered.filter(n => n.type === this.typeFilter);
            }
            
            // Filtre par priorité
            if (this.priorityFilter) {
                filtered = filtered.filter(n => n.priority === this.priorityFilter);
            }
            
            this.filteredNotifications = filtered;
            this.totalPages = Math.ceil(filtered.length / this.perPage);
            this.currentPage = 1;
        },

        // Actions
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/deliverer/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    // Mettre à jour localement
                    if (Array.isArray(this.notifications)) {
                        const notification = this.notifications.find(n => n.id === notificationId);
                        if (notification) {
                            notification.read = true;
                            notification.read_at = new Date().toISOString();
                        }
                    }
                    
                    this.updateStats();
                    this.filterNotifications();
                    this.showToast('Notification marquée comme lue', 'success');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur lors de la mise à jour', 'error');
            }
        },

        async markAllAsRead() {
            if (this.stats.unread === 0) return;
            
            try {
                const response = await fetch('/deliverer/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    // Mettre à jour toutes les notifications
                    if (Array.isArray(this.notifications)) {
                        this.notifications.forEach(n => {
                            if (!n.read) {
                                n.read = true;
                                n.read_at = new Date().toISOString();
                            }
                        });
                    }
                    
                    this.updateStats();
                    this.filterNotifications();
                    this.showToast('Toutes les notifications marquées comme lues', 'success');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur lors de la mise à jour', 'error');
            }
        },

        async deleteNotification(notificationId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) return;
            
            try {
                const response = await fetch(`/deliverer/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Supprimer localement
                    if (Array.isArray(this.notifications)) {
                        this.notifications = this.notifications.filter(n => n.id !== notificationId);
                    }
                    this.updateStats();
                    this.filterNotifications();
                    this.showToast('Notification supprimée', 'success');
                }
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur lors de la suppression', 'error');
            }
        },

        async refreshNotifications() {
            this.loading = true;
            
            try {
                const response = await fetch('/deliverer/notifications?ajax=1');
                const data = await response.json();
                
                this.notifications = data.notifications || [];
                this.stats = data.stats || this.stats;
                this.filterNotifications();
                this.showToast('Notifications actualisées', 'success');
            } catch (error) {
                console.error('Erreur:', error);
                this.showToast('Erreur lors de l\'actualisation', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Pagination
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        // Utilitaires
        updateStats() {
            if (!Array.isArray(this.notifications)) {
                this.notifications = [];
            }
            this.stats.total = this.notifications.length;
            this.stats.unread = this.notifications.filter(n => !n.read).length;
            this.stats.urgent = this.notifications.filter(n => n.priority === 'URGENT').length;

            const today = new Date().toDateString();
            this.stats.today = this.notifications.filter(n => new Date(n.created_at).toDateString() === today).length;
        },

        getTypeIcon(type) {
            const icons = {
                'NEW_PICKUP_AVAILABLE': { icon: '📦', bg: 'bg-blue-100 text-blue-600' },
                'DELIVERY_URGENT': { icon: '🚨', bg: 'bg-red-100 text-red-600' },
                'PAYMENT_ASSIGNED': { icon: '💰', bg: 'bg-green-100 text-green-600' },
                'WALLET_HIGH_BALANCE': { icon: '💳', bg: 'bg-yellow-100 text-yellow-600' },
                'COD_MODIFICATION': { icon: '📝', bg: 'bg-purple-100 text-purple-600' },
                'SYSTEM_UPDATE': { icon: '🔄', bg: 'bg-indigo-100 text-indigo-600' },
                'PACKAGE_REASSIGNED': { icon: '🔄', bg: 'bg-orange-100 text-orange-600' }
            };
            return icons[type] || { icon: '📢', bg: 'bg-gray-100 text-gray-600' };
        },

        getPriorityBadge(priority) {
            const badges = {
                'URGENT': { text: 'Urgente', class: 'bg-red-100 text-red-800' },
                'HIGH': { text: 'Haute', class: 'bg-orange-100 text-orange-800' },
                'NORMAL': { text: 'Normale', class: 'bg-blue-100 text-blue-800' },
                'LOW': { text: 'Basse', class: 'bg-gray-100 text-gray-800' }
            };
            return badges[priority] || badges['NORMAL'];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'À l\'instant';
            if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)} min`;
            if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)} h`;
            if (diffInSeconds < 604800) return `Il y a ${Math.floor(diffInSeconds / 86400)} j`;
            
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            }[type] || 'bg-blue-500';
            
            toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
            toast.textContent = message;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Animation d'entrée
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            // Animation de sortie après 3 secondes
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection