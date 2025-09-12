<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Commercial - Al-Amena Delivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body class="bg-gradient-to-br from-purple-50 to-purple-100 min-h-screen">
    <div id="app" class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <!-- Logo -->
            <div class="p-6 border-b border-purple-200">
                <h1 class="text-xl font-bold text-purple-600">Al-Amena Delivery</h1>
                <p class="text-sm text-gray-500">Espace Commercial</p>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <button onclick="showSection('dashboard')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    Dashboard
                </button>

                <button onclick="showSection('clients')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    Clients
                </button>

                <button onclick="showSection('complaints')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    Réclamations
                    <span id="complaints-badge" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full hidden">0</span>
                </button>

                <button onclick="showSection('withdrawals')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Retraits
                    <span id="withdrawals-badge" class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full hidden">0</span>
                </button>

                <button onclick="showSection('deliverers')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Livreurs
                    <span id="deliverers-badge" class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full hidden">0</span>
                </button>

                <button onclick="showSection('packages')" 
                        class="nav-item w-full flex items-center px-4 py-3 rounded-lg transition-colors text-left">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Colis
                </button>
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
                    <h2 id="section-title" class="text-2xl font-bold text-gray-900">Dashboard Commercial</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button onclick="toggleNotifications()" 
                                    class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/>
                                </svg>
                                <span id="notifications-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                            </button>
                            
                            <!-- Notifications Panel -->
                            <div id="notifications-panel" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50 hidden">
                                <div class="p-4 border-b">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                                        <button onclick="markAllNotificationsRead()" class="text-sm text-purple-600 hover:text-purple-800">
                                            Marquer tout lu
                                        </button>
                                    </div>
                                </div>
                                <div id="notifications-list" class="max-h-96 overflow-y-auto">
                                    <p class="p-4 text-gray-500 text-center">Aucune notification</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <button onclick="openCreateClientModal()" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Nouveau Client
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
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
                                    <p id="urgent-complaints-count" class="text-2xl font-bold text-gray-900">{{ $complaintsStats['urgent'] ?? 0 }}</p>
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
                                    <p id="pending-withdrawals-count" class="text-2xl font-bold text-gray-900">{{ $stats['pending_withdrawals'] ?? 0 }}</p>
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
                                    <p id="high-balance-deliverers-count" class="text-2xl font-bold text-gray-900">{{ $stats['high_balance_deliverers'] ?? 0 }}</p>
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
                                    <p id="packages-today-count" class="text-2xl font-bold text-gray-900">{{ $stats['packages_today'] ?? 0 }}</p>
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
                            <div class="p-6" id="recent-complaints">
                                @if(isset($recentActivity['complaints']) && $recentActivity['complaints']->count() > 0)
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
                                            <button onclick="quickAction('complaint', {{ $complaint->id }})" 
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
                            <div class="p-6" id="recent-cod-modifications">
                                @if(isset($recentActivity['cod_modifications']) && $recentActivity['cod_modifications']->count() > 0)
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

                <!-- Other Sections -->
                <div id="clients-section" class="content-section hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion des Clients</h3>
                        <p class="text-gray-600">Interface de gestion des clients sera ici...</p>
                    </div>
                </div>

                <div id="complaints-section" class="content-section hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion des Réclamations</h3>
                        <p class="text-gray-600">Interface de gestion des réclamations sera ici...</p>
                    </div>
                </div>

                <div id="withdrawals-section" class="content-section hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Demandes de Retrait</h3>
                        <p class="text-gray-600">Interface de gestion des retraits sera ici...</p>
                    </div>
                </div>

                <div id="deliverers-section" class="content-section hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion des Livreurs</h3>
                        <p class="text-gray-600">Interface de gestion des livreurs sera ici...</p>
                    </div>
                </div>

                <div id="packages-section" class="content-section hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-purple-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Suivi Global des Colis</h3>
                        <p class="text-gray-600">Interface de suivi des colis sera ici...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Client -->
    <div id="create-client-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-bold text-gray-900">Nouveau Client</h3>
                    <button onclick="closeCreateClientModal()" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="create-client-form" onsubmit="createClient(event)">
                    <div class="p-6 space-y-4">
                        <!-- Informations de base -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                                <input id="client-name" type="text" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input id="client-email" type="email" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span class="text-red-500">*</span></label>
                                <input id="client-phone" type="text" required 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom Boutique</label>
                                <input id="client-shop" type="text" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse <span class="text-red-500">*</span></label>
                            <input id="client-address" type="text" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Mots de passe -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                                <input id="client-password" type="password" required minlength="6" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer mot de passe <span class="text-red-500">*</span></label>
                                <input id="client-password-confirmation" type="password" required minlength="6" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <!-- Informations professionnelles -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Matricule Fiscal</label>
                                <input id="client-fiscal" type="text" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secteur d'Activité</label>
                                <input id="client-sector" type="text" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Document d'Identité</label>
                            <input id="client-identity" type="text" 
                                   placeholder="CIN, Passeport, Registre de commerce..."
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Tarification -->
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-3">Offre Tarifaire</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix Livraison (DT) <span class="text-red-500">*</span></label>
                                    <input id="delivery-price" type="number" step="0.001" required min="0" 
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">Frais de livraison en cas de succès</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix Retour (DT) <span class="text-red-500">*</span></label>
                                    <input id="return-price" type="number" step="0.001" required min="0" 
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-purple-500 focus:border-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">Frais de retour en cas d'échec</p>
                                </div>
                            </div>
                        </div>

                        <!-- Messages d'erreur -->
                        <div id="form-errors" class="hidden bg-red-50 border border-red-200 rounded-md p-3">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-2">
                                    <p class="text-sm text-red-800" id="error-message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-3 p-6 border-t bg-gray-50">
                        <button type="submit" 
                                class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="create-btn-text">Créer Client</span>
                        </button>
                        <button type="button" onclick="closeCreateClientModal()" 
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentSection = 'dashboard';
        let isLoading = false;

        // Navigation
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show target section
            document.getElementById(sectionName + '-section').classList.remove('hidden');
            
            // Update nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-purple-100', 'text-purple-600');
                item.classList.add('text-gray-700', 'hover:bg-purple-50');
            });
            
            // Highlight active nav item
            event.target.classList.add('bg-purple-100', 'text-purple-600');
            event.target.classList.remove('text-gray-700', 'hover:bg-purple-50');
            
            // Update title
            const titles = {
                'dashboard': 'Dashboard Commercial',
                'clients': 'Gestion des Clients',
                'complaints': 'Gestion des Réclamations',
                'withdrawals': 'Demandes de Retrait',
                'deliverers': 'Gestion des Livreurs',
                'packages': 'Suivi Global des Colis'
            };
            document.getElementById('section-title').textContent = titles[sectionName] || 'Dashboard';
            currentSection = sectionName;

            // Charger les données spécifiques à la section
            if (sectionName === 'clients') {
                loadClients();
            }
        }

        // Modal Management
        function openCreateClientModal() {
            document.getElementById('create-client-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            hideFormErrors();
        }

        function closeCreateClientModal() {
            document.getElementById('create-client-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            resetCreateClientForm();
            hideFormErrors();
        }

        function resetCreateClientForm() {
            document.getElementById('create-client-form').reset();
            document.getElementById('create-btn-text').textContent = 'Créer Client';
        }

        function showFormErrors(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('form-errors').classList.remove('hidden');
        }

        function hideFormErrors() {
            document.getElementById('form-errors').classList.add('hidden');
        }

        // Validation des mots de passe
        function validatePasswords() {
            const password = document.getElementById('client-password').value;
            const confirmation = document.getElementById('client-password-confirmation').value;
            
            if (password.length < 6) {
                showFormErrors('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }
            
            if (password !== confirmation) {
                showFormErrors('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            return true;
        }

        // Create Client
        async function createClient(event) {
            event.preventDefault();
            if (isLoading) return;
            
            hideFormErrors();
            
            // Validation côté client
            if (!validatePasswords()) {
                return;
            }
            
            isLoading = true;
            document.getElementById('create-btn-text').textContent = 'Création en cours...';
            
            const formData = {
                name: document.getElementById('client-name').value.trim(),
                email: document.getElementById('client-email').value.trim(),
                phone: document.getElementById('client-phone').value.trim(),
                address: document.getElementById('client-address').value.trim(),
                shop_name: document.getElementById('client-shop').value.trim(),
                fiscal_number: document.getElementById('client-fiscal').value.trim(),
                business_sector: document.getElementById('client-sector').value.trim(),
                identity_document: document.getElementById('client-identity').value.trim(),
                delivery_price: parseFloat(document.getElementById('delivery-price').value),
                return_price: parseFloat(document.getElementById('return-price').value),
                password: document.getElementById('client-password').value,
                password_confirmation: document.getElementById('client-password-confirmation').value
            };

            try {
                const response = await fetch('/commercial/clients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok) {
                    showSuccessMessage(`Client créé avec succès ! Email: ${formData.email}`);
                    closeCreateClientModal();
                    refreshStats();
                } else {
                    // Gestion des erreurs de validation Laravel
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat();
                        showFormErrors(errorMessages.join(' '));
                    } else {
                        showFormErrors(result.message || 'Erreur lors de la création du client.');
                    }
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
                showFormErrors('Erreur de connexion. Veuillez réessayer.');
            } finally {
                isLoading = false;
                document.getElementById('create-btn-text').textContent = 'Créer Client';
            }
        }

        // Notifications
        function toggleNotifications() {
            const panel = document.getElementById('notifications-panel');
            if (panel.classList.contains('hidden')) {
                loadNotifications();
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        }

        async function loadNotifications() {
            try {
                const response = await fetch('/commercial/notifications/api/recent');
                if (response.ok) {
                    const notifications = await response.json();
                    displayNotifications(notifications);
                    updateNotificationCount(notifications.filter(n => !n.read).length);
                }
            } catch (error) {
                console.error('Erreur chargement notifications:', error);
            }
        }

        function displayNotifications(notifications) {
            const list = document.getElementById('notifications-list');
            
            if (notifications.length === 0) {
                list.innerHTML = '<p class="p-4 text-gray-500 text-center">Aucune notification</p>';
                return;
            }

            list.innerHTML = notifications.map(notification => `
                <div class="p-4 border-b hover:bg-gray-50 ${notification.read ? 'opacity-75' : ''}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-sm text-gray-900">${notification.title}</p>
                            <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                            <p class="text-xs text-gray-500 mt-2">${notification.created_at_human}</p>
                        </div>
                        <div class="ml-2">
                            <span class="inline-block px-2 py-1 text-xs rounded-full ${notification.priority_color}">
                                ${notification.priority_display}
                            </span>
                            ${!notification.read ? `
                                <button onclick="markNotificationRead(${notification.id})" 
                                        class="ml-2 text-xs text-purple-600 hover:text-purple-800">
                                    Marquer lu
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function markNotificationRead(notificationId) {
            try {
                const response = await fetch(`/commercial/notifications/mark-read/${notificationId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (response.ok) {
                    loadNotifications();
                }
            } catch (error) {
                console.error('Erreur marquage notification:', error);
            }
        }

        async function markAllNotificationsRead() {
            try {
                const response = await fetch('/commercial/notifications/api/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ mark_all: true })
                });
                if (response.ok) {
                    loadNotifications();
                }
            } catch (error) {
                console.error('Erreur marquage toutes notifications:', error);
            }
        }

        function updateNotificationCount(count) {
            const badge = document.getElementById('notifications-count');
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Stats Management
        async function refreshStats() {
            try {
                // Update notification count
                const notifResponse = await fetch('/commercial/notifications/api/unread-count');
                if (notifResponse.ok) {
                    const data = await notifResponse.json();
                    updateNotificationCount(data.unread_count);
                    updateBadges(data);
                }
            } catch (error) {
                console.error('Erreur refresh stats:', error);
            }
        }

        function updateBadges(data) {
            const complaintsCount = data.urgent_count || 0;
            const withdrawalsCount = data.pending_withdrawals || 0;
            const deliverersCount = data.high_balance_deliverers || 0;

            updateBadge('complaints-badge', complaintsCount);
            updateBadge('withdrawals-badge', withdrawalsCount);
            updateBadge('deliverers-badge', deliverersCount);
        }

        function updateBadge(badgeId, count) {
            const badge = document.getElementById(badgeId);
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Messages
        function showSuccessMessage(message) {
            showMessage(message, 'bg-green-500');
        }

        function showErrorMessage(message) {
            showMessage(message, 'bg-red-500');
        }

        function showMessage(message, colorClass) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 ${colorClass} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Animate in
            setTimeout(() => toast.classList.add('translate-x-0'), 10);
            
            // Remove after delay
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Quick Actions
        function quickAction(type, id) {
            showSuccessMessage('Action rapide: ' + type + ' #' + id);
        }

        // Event Listeners
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateClientModal();
                document.getElementById('notifications-panel').classList.add('hidden');
            }
        });

        document.addEventListener('click', function(e) {
            const notifPanel = document.getElementById('notifications-panel');
            const notifButton = e.target.closest('[onclick="toggleNotifications()"]');
            
            if (!notifPanel.contains(e.target) && !notifButton && !notifPanel.classList.contains('hidden')) {
                notifPanel.classList.add('hidden');
            }
        });

        // Validation temps réel des mots de passe
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('client-password');
            const confirmationField = document.getElementById('client-password-confirmation');
            
            function validatePasswordsRealTime() {
                const password = passwordField.value;
                const confirmation = confirmationField.value;
                
                if (confirmation && password !== confirmation) {
                    confirmationField.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmationField.setCustomValidity('');
                }
            }
            
            passwordField.addEventListener('input', validatePasswordsRealTime);
            confirmationField.addEventListener('input', validatePasswordsRealTime);
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial active nav item
            document.querySelector('[onclick="showSection(\'dashboard\')"]').classList.add('bg-purple-100', 'text-purple-600');
            
            // Load initial data
            refreshStats();
            
            // Auto refresh every 30 seconds
            setInterval(refreshStats, 30000);
        });
    </script>
</body>
</html>