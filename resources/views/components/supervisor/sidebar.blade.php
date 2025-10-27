<nav x-data="{ 
    activeSection: '{{ request()->segment(2) ?? 'dashboard' }}',
    financialOpen: {{ request()->segment(2) === 'financial' ? 'true' : 'false' }},
    usersOpen: {{ request()->segment(2) === 'users' ? 'true' : 'false' }},
    vehiclesOpen: {{ request()->segment(2) === 'vehicles' ? 'true' : 'false' }}
}" 
class="w-64 bg-gradient-to-b from-gray-900 to-gray-800 h-screen fixed left-0 top-0 overflow-y-auto shadow-2xl">
    
    <!-- Logo -->
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-white font-bold text-lg">Al-Amena</h2>
                <p class="text-gray-400 text-xs">Superviseur</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ auth()->user()->name }}</p>
                <p class="text-gray-400 text-xs">{{ auth()->user()->email }}</p>
            </div>
        </div>

        @if(session()->has('impersonating'))
        <div class="mt-3 p-2 bg-yellow-500/20 border border-yellow-500/50 rounded-lg">
            <p class="text-yellow-400 text-xs font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Mode Impersonation
            </p>
            <form action="{{ route('supervisor.users.stop-impersonation') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded w-full">
                    Retour Superviseur
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Navigation Menu -->
    <div class="p-4 space-y-2">
        
        <!-- Dashboard -->
        <a href="{{ route('supervisor.dashboard') }}" 
           :class="activeSection === 'dashboard' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="font-medium">Tableau de Bord</span>
        </a>

        <!-- Gestion Utilisateurs -->
        <div>
            <button @click="usersOpen = !usersOpen"
                    :class="activeSection === 'users' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium">Utilisateurs</span>
                </div>
                <svg :class="usersOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="usersOpen" x-collapse class="ml-4 mt-2 space-y-1">
                <a href="{{ route('supervisor.users.index') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Tous les utilisateurs
                </a>
                <a href="{{ route('supervisor.users.by-role', 'CLIENT') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Clients
                </a>
                <a href="{{ route('supervisor.users.by-role', 'DELIVERER') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Livreurs
                </a>
                <a href="{{ route('supervisor.users.by-role', 'COMMERCIAL') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Commerciaux
                </a>
                <a href="{{ route('supervisor.users.by-role', 'DEPOT_MANAGER') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Chefs Dépôt
                </a>
                <a href="{{ route('supervisor.users.create') }}" class="block px-4 py-2 text-sm text-green-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    + Créer utilisateur
                </a>
            </div>
        </div>

        <!-- Gestion Financière -->
        <div>
            <button @click="financialOpen = !financialOpen"
                    :class="activeSection === 'financial' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Gestion Financière</span>
                </div>
                <svg :class="financialOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="financialOpen" x-collapse class="ml-4 mt-2 space-y-1">
                <a href="{{ route('supervisor.financial.reports.index') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Dashboard Financier
                </a>
                <a href="{{ route('supervisor.financial.charges.index') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Charges Fixes
                </a>
                <a href="{{ route('supervisor.financial.assets.index') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Actifs Amortissables
                </a>
            </div>
        </div>

        <!-- Gestion Véhicules -->
        <div>
            <button @click="vehiclesOpen = !vehiclesOpen"
                    :class="activeSection === 'vehicles' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-medium">Véhicules</span>
                </div>
                <svg :class="vehiclesOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="vehiclesOpen" x-collapse class="ml-4 mt-2 space-y-1">
                <a href="{{ route('supervisor.vehicles.index') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    Liste Véhicules
                </a>
                <a href="{{ route('supervisor.vehicles.alerts') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg flex items-center justify-between">
                    <span>Alertes Maintenance</span>
                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full" x-data x-init="
                        fetch('/supervisor/api/vehicles/alerts-count')
                            .then(r => r.json())
                            .then(data => $el.textContent = data.count || 0)
                    ">0</span>
                </a>
                <a href="{{ route('supervisor.vehicles.create') }}" class="block px-4 py-2 text-sm text-green-400 hover:text-white hover:bg-gray-700 rounded-lg">
                    + Ajouter véhicule
                </a>
            </div>
        </div>

        <!-- Gestion Colis -->
        <a href="{{ route('supervisor.packages.index') }}" 
           :class="activeSection === 'packages' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="font-medium">Gestion Colis</span>
        </a>

        <!-- Gestion Tickets -->
        <a href="{{ route('supervisor.tickets.index') }}" 
           :class="activeSection === 'tickets' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
            <span class="font-medium">Tickets Support</span>
        </a>

        <!-- Logs & Actions -->
        <div class="pt-2 border-t border-gray-700">
            <a href="{{ route('supervisor.action-logs.index') }}" 
               :class="activeSection === 'action-logs' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-medium">Suivi & Logs</span>
            </a>

            <a href="{{ route('supervisor.action-logs.critical') }}" 
               class="flex items-center space-x-3 px-4 py-2 text-red-400 hover:bg-gray-700 rounded-lg transition-all duration-200 mt-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-medium text-sm">Actions Critiques</span>
            </a>
        </div>

        <!-- Recherche Intelligente -->
        <a href="{{ route('supervisor.search.index') }}" 
           :class="activeSection === 'search' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="font-medium">Recherche</span>
        </a>

        <!-- Paramètres -->
        <a href="{{ route('supervisor.settings.index') }}" 
           :class="activeSection === 'settings' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="font-medium">Paramètres</span>
        </a>
    </div>

    <!-- Déconnexion -->
    <div class="p-4 border-t border-gray-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-400 hover:bg-red-500/20 rounded-lg transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="font-medium">Déconnexion</span>
            </button>
        </form>
    </div>
</nav>
