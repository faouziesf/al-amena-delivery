<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Commercial - Al-Amena Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
</head>
<body class="bg-gradient-to-br from-purple-50 to-purple-100 min-h-screen">
    <div x-data="commercialDashboard()" class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <!-- Logo -->
            <div class="p-6 border-b border-purple-200">
                <h1 class="text-xl font-bold text-purple-600">Al-Amena Delivery</h1>
                <p class="text-sm text-gray-500">Espace Commercial</p>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="#" @click="activeSection = 'dashboard'" 
                   :class="activeSection === 'dashboard' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    Dashboard
                </a>

                <a href="#" @click="activeSection = 'clients'" 
                   :class="activeSection === 'clients' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    Clients
                </a>

                <a href="#" @click="activeSection = 'complaints'" 
                   :class="activeSection === 'complaints' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Réclamations
                    <span x-show="stats.pending_complaints > 0" 
                          x-text="stats.pending_complaints"
                          class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                    </span>
                </a>

                <a href="#" @click="activeSection = 'withdrawals'" 
                   :class="activeSection === 'withdrawals' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Retraits
                    <span x-show="stats.pending_withdrawals > 0" 
                          x-text="stats.pending_withdrawals"
                          class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
                    </span>
                </a>

                <a href="#" @click="activeSection = 'deliverers'" 
                   :class="activeSection === 'deliverers' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Livreurs
                    <span x-show="stats.high_balance_deliverers > 0" 
                          x-text="stats.high_balance_deliverers"
                          class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                    </span>
                </a>

                <a href="#" @click="activeSection = 'packages'" 
                   :class="activeSection === 'packages' ? 'bg-purple-100 text-purple-600' : 'text-gray-700 hover:bg-purple-50'"
                   class="flex items-center px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Colis
                </a>
            </nav>

            <!-- User Info -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-purple-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">Commercial</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-600 hover:text-red-800">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 x-text="getSectionTitle()" class="text-2xl font-bold text-gray-900"></h2>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                                </svg>
                                <span x-show="totalNotifications > 0" 
                                      x-text="totalNotifications"
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                </span>
                            </button>
                        </div>
                        
                        <!-- Quick Actions -->
                        <button @click="activeSection = 'clients'; showCreateClientModal = true" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Nouveau Client
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Dashboard Section -->
                <div x-show="activeSection === 'dashboard'">
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-sm p-6 border border-purple-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-red-100 rounded-lg">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">Réclamations Urgentes</p>
                                    <p x-text="stats.urgent_complaints" class="text-2xl font-bold text-gray-900">{{ $complaintsStats['urgent'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6 border border-purple-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-orange-100 rounded-lg">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">Retraits en Attente</p>
                                    <p x-text="stats.pending_withdrawals" class="text-2xl font-bold text-gray-900">{{ $stats['pending_withdrawals'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6 border border-purple-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">Wallets à Vider</p>
                                    <p x-text="stats.high_balance_deliverers" class="text-2xl font-bold text-gray-900">{{ $stats['high_balance_deliverers'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-6 border border-purple-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">Colis Aujourd'hui</p>
                                    <p x-text="stats.packages_today" class="text-2xl font-bold text-gray-900">{{ $stats['packages_today'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Complaints -->
                        <div class="bg-white rounded-lg shadow-sm border border-purple-100">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Réclamations Récentes</h3>
                            </div>
                            <div class="p-6">
                                @if($recentActivity['complaints']->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($recentActivity['complaints']->take(5) as $complaint)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 {{ $complaint->priority_color }} rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-bold">!</span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $complaint->type_display }}</p>
                                                <p class="text-sm text-gray-600">{{ $complaint->client->name }} - {{ $complaint->package->package_code }}</p>
                                                <p class="text-xs text-gray-500">{{ $complaint->created_at->diffForHumans() }}</p>
                                            </div>
                                            <button @click="viewComplaint({{ $complaint->id }})" 
                                                    class="text-purple-600 hover:text-purple-800 text-sm">
                                                Traiter
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-center py-4">Aucune réclamation récente</p>
                                @endif
                            </div>
                        </div>

                        <!-- Recent COD Modifications -->
                        <div class="bg-white rounded-lg shadow-sm border border-purple-100">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Modifications COD Récentes</h3>
                            </div>
                            <div class="p-6">
                                @if($recentActivity['cod_modifications']->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($recentActivity['cod_modifications']->take(5) as $mod)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs text-purple-600">€</span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $mod->package->package_code }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ number_format($mod->old_amount, 3) }} → {{ number_format($mod->new_amount, 3) }} DT
                                                    <span class="{{ $mod->change_color }}">{{ $mod->formatted_change }}</span>
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $mod->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-center py-4">Aucune modification récente</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clients Section -->
                <div x-show="activeSection === 'clients'">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Gestion des Clients</h3>
                                <button @click="showCreateClientModal = true" 
                                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Nouveau Client
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600">Interface de gestion des clients sera ici...</p>
                        </div>
                    </div>
                </div>

                <!-- Complaints Section -->
                <div x-show="activeSection === 'complaints'">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Réclamations en Attente</h3>
                                <div class="flex space-x-2">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                                        {{ $complaintsStats['urgent'] }} Urgentes
                                    </span>
                                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
                                        {{ $complaintsStats['cod_changes'] }} Changements COD
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600">Interface de gestion des réclamations sera ici...</p>
                        </div>
                    </div>
                </div>

                <!-- Autres sections... -->
                <div x-show="activeSection === 'withdrawals'">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Demandes de Retrait</h3>
                        <p class="text-gray-600">Interface de gestion des retraits sera ici...</p>
                    </div>
                </div>

                <div x-show="activeSection === 'deliverers'">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion des Livreurs</h3>
                        <p class="text-gray-600">Interface de gestion des livreurs sera ici...</p>
                    </div>
                </div>

                <div x-show="activeSection === 'packages'">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Suivi Global des Colis</h3>
                        <p class="text-gray-600">Interface de suivi des colis sera ici...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Client -->
    <div x-show="showCreateClientModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Nouveau Client</h3>
                <form @submit.prevent="createClient()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input x-model="newClient.name" type="text" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input x-model="newClient.email" type="email" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input x-model="newClient.phone" type="text" required 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prix Livraison (DT)</label>
                                <input x-model="newClient.delivery_price" type="number" step="0.001" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prix Retour (DT)</label>
                                <input x-model="newClient.return_price" type="number" step="0.001" required 
                                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex space-x-3">
                        <button type="submit" 
                                class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700">
                            Créer Client
                        </button>
                        <button type="button" @click="showCreateClientModal = false" 
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function commercialDashboard() {
            return {
                activeSection: 'dashboard',
                showCreateClientModal: false,
                showComplaintModal: false,
                showNotificationPanel: false,
                selectedComplaint: null,
                selectedPackage: null,
                loading: false,
                
                stats: {
                    pending_complaints: {{ $stats['pending_complaints'] }},
                    urgent_complaints: {{ $complaintsStats['urgent'] }},
                    pending_withdrawals: {{ $stats['pending_withdrawals'] }},
                    high_balance_deliverers: {{ $stats['high_balance_deliverers'] }},
                    packages_today: {{ $stats['packages_today'] }},
                    packages_in_progress: {{ $stats['packages_in_progress'] }},
                    cod_modifications_today: {{ $stats['cod_modifications_today'] }}
                },
                
                recentActivity: {
                    complaints: @json($recentActivity['complaints']->take(5)),
                    withdrawals: @json($recentActivity['withdrawals']->take(5)),
                    codModifications: @json($recentActivity['cod_modifications']->take(5))
                },
                
                notifications: [],
                unreadNotifications: 0,
                
                newClient: {
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    shop_name: '',
                    fiscal_number: '',
                    delivery_price: '',
                    return_price: '',
                    password: 'password123'
                },

                get totalNotifications() {
                    return this.unreadNotifications || (this.stats.pending_complaints + this.stats.pending_withdrawals);
                },

                getSectionTitle() {
                    const titles = {
                        'dashboard': 'Dashboard Commercial',
                        'clients': 'Gestion des Clients',
                        'complaints': 'Gestion des Réclamations',
                        'withdrawals': 'Demandes de Retrait',
                        'deliverers': 'Gestion des Livreurs',
                        'packages': 'Suivi Global des Colis',
                        'notifications': 'Notifications'
                    };
                    return titles[this.activeSection] || 'Dashboard';
                },

                async createClient() {
                    if (this.loading) return;
                    this.loading = true;
                    
                    try {
                        const response = await fetch('/commercial/clients', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            },
                            body: JSON.stringify(this.newClient)
                        });

                        const result = await response.json();
                        
                        if (response.ok) {
                            this.showCreateClientModal = false;
                            this.resetNewClientForm();
                            this.showSuccessMessage('Client créé avec succès !');
                            await this.refreshStats();
                        } else {
                            this.showErrorMessage(result.error || 'Erreur lors de la création du client');
                        }
                    } catch (error) {
                        this.showErrorMessage('Erreur réseau: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                resetNewClientForm() {
                    this.newClient = {
                        name: '', email: '', phone: '', address: '', shop_name: '',
                        fiscal_number: '', delivery_price: '', return_price: '', password: 'password123'
                    };
                },

                async refreshStats() {
                    try {
                        const [statsResponse, notifResponse] = await Promise.all([
                            fetch('/commercial/api/dashboard-stats'),
                            fetch('/commercial/notifications/api/unread-count')
                        ]);
                        
                        if (statsResponse.ok) {
                            const newStats = await statsResponse.json();
                            this.stats = { ...this.stats, ...newStats };
                        }
                        
                        if (notifResponse.ok) {
                            const notifData = await notifResponse.json();
                            this.unreadNotifications = notifData.unread_count;
                        }
                    } catch (error) {
                        console.error('Erreur refresh stats:', error);
                    }
                },

                async loadNotifications() {
                    try {
                        const response = await fetch('/commercial/notifications/api/recent');
                        if (response.ok) {
                            this.notifications = await response.json();
                        }
                    } catch (error) {
                        console.error('Erreur chargement notifications:', error);
                    }
                },

                async markNotificationAsRead(notificationId) {
                    try {
                        const response = await fetch(`/commercial/notifications/mark-read/${notificationId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Content-Type': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            await this.loadNotifications();
                            this.unreadNotifications = Math.max(0, this.unreadNotifications - 1);
                        }
                    } catch (error) {
                        console.error('Erreur marquage notification:', error);
                    }
                },

                async markAllNotificationsAsRead() {
                    try {
                        const response = await fetch('/commercial/notifications/api/mark-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ mark_all: true })
                        });
                        
                        if (response.ok) {
                            await this.loadNotifications();
                            this.unreadNotifications = 0;
                        }
                    } catch (error) {
                        console.error('Erreur marquage toutes notifications:', error);
                    }
                },

                async quickAction(actionType, itemId, additionalData = {}) {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        let url, method = 'POST', body = { ...additionalData };
                        
                        switch (actionType) {
                            case 'approve_withdrawal':
                                url = `/commercial/withdrawals/${itemId}/approve`;
                                break;
                            case 'assign_complaint':
                                url = `/commercial/complaints/${itemId}/assign`;
                                break;
                            case 'resolve_complaint':
                                url = `/commercial/complaints/${itemId}/resolve`;
                                break;
                            default:
                                throw new Error('Action non reconnue');
                        }

                        const response = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            },
                            body: JSON.stringify(body)
                        });

                        if (response.ok) {
                            this.showSuccessMessage('Action exécutée avec succès !');
                            await this.refreshStats();
                        } else {
                            const error = await response.json();
                            this.showErrorMessage(error.message || 'Erreur lors de l\'action');
                        }
                    } catch (error) {
                        this.showErrorMessage('Erreur: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                showSuccessMessage(message) {
                    // Simple toast notification
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                },

                showErrorMessage(message) {
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    toast.textContent = message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 5000);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-TN', {
                        style: 'currency',
                        currency: 'TND',
                        minimumFractionDigits: 3
                    }).format(amount);
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

                init() {
                    // Refresh stats every 30 seconds
                    setInterval(() => {
                        this.refreshStats();
                    }, 30000);

                    // Load initial notifications
                    this.loadNotifications();

                    // Handle outside clicks for modals
                    document.addEventListener('click', (e) => {
                        if (e.target.classList.contains('modal-backdrop')) {
                            this.showCreateClientModal = false;
                            this.showComplaintModal = false;
                            this.showNotificationPanel = false;
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>