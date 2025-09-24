@extends('layouts.supervisor')

@section('title', 'Dashboard')
@section('subtitle', 'Vue d\'ensemble du système')

@section('content')
<div class="space-y-6" x-data="dashboardData()">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_users'] ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Utilisateurs</p>
                    <p class="text-xs text-green-600 mt-1">{{ $stats['active_users'] ?? 0 }} actifs</p>
                </div>
            </div>
        </div>

        <!-- Total Packages -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_packages'] ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Colis</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $stats['pending_packages'] ?? 0 }} en cours</p>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_revenue'] ?? 0, 3) }} TND</h3>
                    <p class="text-sm text-gray-600">Chiffre d'affaires</p>
                    <p class="text-xs text-green-600 mt-1">+12% ce mois</p>
                </div>
            </div>
        </div>

        <!-- Tickets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total_tickets'] ?? 0 }}</h3>
                    <p class="text-sm text-gray-600">Tickets</p>
                    <p class="text-xs text-red-600 mt-1">{{ $stats['urgent_tickets'] ?? 0 }} urgents</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Revenus (7 derniers jours)</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">7J</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-full">30J</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Packages Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Colis créés (7 derniers jours)</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">7J</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-full">30J</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="packagesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Clients -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Top 5 Clients</h3>
            <div class="space-y-4">
                @forelse($topClients ?? [] as $client)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-supervisor-primary rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white">{{ substr($client->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $client->name }}</p>
                                <p class="text-sm text-gray-500">{{ $client->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">{{ $client->packages_count }}</p>
                            <p class="text-xs text-gray-500">colis</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <p>Aucun client trouvé</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Activité Récente</h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                @forelse($recentActivity ?? [] as $activity)
                    <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            @if($activity['type'] === 'ticket')
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            @elseif($activity['type'] === 'package')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                            <p class="text-xs text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                        @if(isset($activity['url']))
                            <a href="{{ $activity['url'] }}" class="text-supervisor-primary hover:text-supervisor-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p>Aucune activité récente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if(!empty($alerts))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Alertes Système</h3>
            <div class="space-y-4">
                @foreach($alerts as $alert)
                    <div class="flex items-center justify-between p-4 border-l-4 @if($alert['type'] === 'danger') border-red-500 bg-red-50 @elseif($alert['type'] === 'warning') border-yellow-500 bg-yellow-50 @else border-blue-500 bg-blue-50 @endif rounded-r-lg">
                        <div class="flex items-center space-x-3">
                            @if($alert['type'] === 'danger')
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @elseif($alert['type'] === 'warning')
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @endif
                            <p class="text-sm font-medium @if($alert['type'] === 'danger') text-red-800 @elseif($alert['type'] === 'warning') text-yellow-800 @else text-blue-800 @endif">
                                {{ $alert['message'] }}
                            </p>
                        </div>
                        @if(isset($alert['url']))
                            <a href="{{ $alert['url'] }}" class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md @if($alert['type'] === 'danger') bg-red-100 text-red-800 hover:bg-red-200 @elseif($alert['type'] === 'warning') bg-yellow-100 text-yellow-800 hover:bg-yellow-200 @else bg-blue-100 text-blue-800 hover:bg-blue-200 @endif">
                                Voir détails
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Actions Rapides</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('supervisor.users.create') }}" class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-supervisor-primary hover:bg-supervisor-light transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Nouvel Utilisateur</p>
                </div>
            </a>

            <a href="{{ route('supervisor.system.backup') }}" class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-supervisor-primary hover:bg-supervisor-light transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Backup Système</p>
                </div>
            </a>

            <a href="{{ route('supervisor.reports.index') }}" class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-supervisor-primary hover:bg-supervisor-light transition-colors">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-900">Générer Rapport</p>
                </div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dashboardData() {
    return {
        init() {
            this.initCharts();
        },

        initCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(collect($revenueChart ?? [])->pluck('date')->toArray()) !!},
                    datasets: [{
                        label: 'Revenus (TND)',
                        data: {!! json_encode(collect($revenueChart ?? [])->pluck('revenue')->toArray()) !!},
                        borderColor: '#DC2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Packages Chart
            const packagesCtx = document.getElementById('packagesChart').getContext('2d');
            new Chart(packagesCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(collect($packagesChart ?? [])->pluck('date')->toArray()) !!},
                    datasets: [{
                        label: 'Colis créés',
                        data: {!! json_encode(collect($packagesChart ?? [])->pluck('packages')->toArray()) !!},
                        backgroundColor: '#3B82F6',
                        borderColor: '#2563EB',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection