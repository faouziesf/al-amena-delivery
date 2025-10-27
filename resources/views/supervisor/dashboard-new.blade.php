<x-layouts.supervisor-new>
    <x-slot name="title">Tableau de Bord</x-slot>
    <x-slot name="subtitle">Vue d'ensemble de l'activité</x-slot>

    <div x-data="{
        loading: true,
        stats: {},
        recentLogs: [],
        alerts: [],
        
        async loadData() {
            this.loading = true;
            try {
                const [statsRes, logsRes] = await Promise.all([
                    fetch('/supervisor/api/financial/dashboard'),
                    fetch('/supervisor/api/action-logs/recent?limit=10')
                ]);
                
                this.stats = await statsRes.json();
                this.recentLogs = await logsRes.json();
            } catch (error) {
                console.error('Erreur chargement données:', error);
            } finally {
                this.loading = false;
            }
        }
    }" x-init="loadData()">

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-20">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-gray-600">Chargement des données...</p>
            </div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" x-cloak class="space-y-6">
            
            <!-- KPIs Row 1: Financial -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Revenus du Jour -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Revenus Aujourd'hui</p>
                            <p class="text-3xl font-bold mt-2" x-text="(stats.today?.total_revenue || 0).toFixed(3) + ' DT'"></p>
                            <p class="text-blue-100 text-xs mt-2">
                                <span x-text="stats.today?.packages_count || 0"></span> colis
                            </p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Charges du Jour -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium">Charges Aujourd'hui</p>
                            <p class="text-3xl font-bold mt-2" x-text="(stats.today?.total_charges || 0).toFixed(3) + ' DT'"></p>
                            <p class="text-red-100 text-xs mt-2">
                                Fixes: <span x-text="(stats.today?.fixed_charges || 0).toFixed(3)"></span> DT
                            </p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Bénéfice du Jour -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Bénéfice Aujourd'hui</p>
                            <p class="text-3xl font-bold mt-2" x-text="(stats.today?.profit || 0).toFixed(3) + ' DT'"></p>
                            <p class="text-green-100 text-xs mt-2">
                                Marge: <span x-text="(stats.today?.profit_margin || 0).toFixed(1)"></span>%
                            </p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Bénéfice du Mois -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Bénéfice ce Mois</p>
                            <p class="text-3xl font-bold mt-2" x-text="(stats.month?.profit || 0).toFixed(3) + ' DT'"></p>
                            <p class="text-purple-100 text-xs mt-2">
                                <span x-text="stats.month?.packages_count || 0"></span> colis traités
                            </p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Row 2: Operations -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Utilisateurs -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Utilisateurs Actifs</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1" x-data x-init="
                                fetch('/supervisor/api/users/stats')
                                    .then(r => r.json())
                                    .then(data => $el.textContent = data.active || 0)
                            ">-</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Véhicules -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Véhicules</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1" x-data="{count: 0, alerts: 0}" x-init="
                                fetch('/supervisor/api/vehicles/stats')
                                    .then(r => r.json())
                                    .then(data => {
                                        count = data.total || 0;
                                        alerts = data.alerts || 0;
                                    })
                            ">
                                <span x-text="count"></span>
                                <span x-show="alerts > 0" class="text-sm text-red-500 ml-2">
                                    (<span x-text="alerts"></span> alertes)
                                </span>
                            </p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Colis Aujourd'hui -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Colis Aujourd'hui</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.today?.packages_count || 0"></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Actions Critiques -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Actions Critiques</p>
                            <p class="text-2xl font-bold text-red-600 mt-1" x-data x-init="
                                fetch('/supervisor/api/action-logs/critical-count')
                                    .then(r => r.json())
                                    .then(data => $el.textContent = data.count || 0)
                                    .catch(() => $el.textContent = '0')
                            ">0</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Tendance Financière (7 derniers jours) -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tendance Financière (7 jours)</h3>
                    <canvas id="financialTrendChart" height="200"></canvas>
                </div>

                <!-- Activité Récente -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Activité Récente</h3>
                        <a href="{{ route('supervisor.action-logs.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            Voir tout →
                        </a>
                    </div>
                    
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <template x-for="log in recentLogs" :key="log.id">
                            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900" x-text="log.action_type"></p>
                                    <p class="text-xs text-gray-600" x-text="log.user_name"></p>
                                    <p class="text-xs text-gray-500" x-text="log.created_at"></p>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="recentLogs.length === 0" class="text-center py-8 text-gray-500">
                            Aucune activité récente
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a href="{{ route('supervisor.users.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Nouvel Utilisateur</span>
                    </a>

                    <a href="{{ route('supervisor.financial.charges.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Nouvelle Charge</span>
                    </a>

                    <a href="{{ route('supervisor.vehicles.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Nouveau Véhicule</span>
                    </a>

                    <a href="{{ route('supervisor.financial.reports.index') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Rapport Financier</span>
                    </a>

                    <a href="{{ route('supervisor.search.index') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Recherche</span>
                    </a>

                    <a href="{{ route('supervisor.action-logs.critical') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition">
                        <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-sm text-gray-700 text-center">Actions Critiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // Initialiser le graphique financier
            const ctx = document.getElementById('financialTrendChart');
            if (ctx) {
                fetch('/supervisor/api/financial/trends?days=7')
                    .then(r => r.json())
                    .then(data => {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels || [],
                                datasets: [
                                    {
                                        label: 'Revenus',
                                        data: data.revenue || [],
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Charges',
                                        data: data.charges || [],
                                        borderColor: 'rgb(239, 68, 68)',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Bénéfice',
                                        data: data.profit || [],
                                        borderColor: 'rgb(34, 197, 94)',
                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                        tension: 0.4
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    })
                    .catch(err => console.error('Erreur chargement graphique:', err));
            }
        });
    </script>
    @endpush
</x-layouts.supervisor-new>
