@extends('layouts.deliverer')

@section('title', 'Mes Statistiques')

@section('content')
<div x-data="delivererStatistics({
    deliverer: {{ json_encode(auth()->user() ?? []) }},
    stats: {{ json_encode($stats ?? []) }}
})" class="max-w-6xl mx-auto p-4 space-y-6">

    <!-- Header with period selector -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">Mes Statistiques</h1>
                <p class="text-purple-100">Analysez vos performances et votre évolution</p>
            </div>
            <div class="mt-4 md:mt-0">
                <select x-model="selectedPeriod" @change="loadPeriodData"
                        class="bg-white text-gray-900 px-4 py-2 rounded-lg border-0 focus:ring-2 focus:ring-purple-300">
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                    <option value="quarter">Ce trimestre</option>
                    <option value="year">Cette année</option>
                    <option value="all">Tout temps</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Colis livrés</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="stats.deliveries_count || 0"></p>
                    <p class="text-sm" :class="stats.deliveries_trend >= 0 ? 'text-green-600' : 'text-red-600'">
                        <i :class="stats.deliveries_trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                        <span x-text="Math.abs(stats.deliveries_trend || 0) + '%'"></span>
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-box text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Taux de réussite</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="(stats.success_rate || 0).toFixed(1) + '%'"></p>
                    <p class="text-sm" :class="stats.success_trend >= 0 ? 'text-green-600' : 'text-red-600'">
                        <i :class="stats.success_trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                        <span x-text="Math.abs(stats.success_trend || 0) + '%'"></span>
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Note moyenne</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="(stats.average_rating || 0).toFixed(1)"></p>
                    <div class="flex items-center">
                        <template x-for="i in 5" :key="i">
                            <i class="fas fa-star text-sm"
                               :class="i <= Math.round(stats.average_rating || 0) ? 'text-yellow-400' : 'text-gray-300'"></i>
                        </template>
                    </div>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-star text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Revenus</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="formatAmount(stats.earnings || 0)"></p>
                    <p class="text-sm" :class="stats.earnings_trend >= 0 ? 'text-green-600' : 'text-red-600'">
                        <i :class="stats.earnings_trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                        <span x-text="Math.abs(stats.earnings_trend || 0) + '%'"></span>
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-coins text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Deliveries Chart -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Évolution des livraisons</h3>
                <div class="flex space-x-2">
                    <button @click="chartType = 'daily'"
                            :class="chartType === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded text-sm">Journalier</button>
                    <button @click="chartType = 'weekly'"
                            :class="chartType === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded text-sm">Hebdomadaire</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="deliveriesChart"></canvas>
            </div>
        </div>

        <!-- Success Rate Chart -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Taux de réussite par zone</h3>
            <div class="h-64">
                <canvas id="successRateChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Time Stats -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                Statistiques temps
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Temps moyen/livraison</span>
                    <span class="font-semibold text-gray-900" x-text="formatDuration(stats.avg_delivery_time || 0)"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Heures travaillées</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.hours_worked || 0) + 'h'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pause moyenne</span>
                    <span class="font-semibold text-gray-900" x-text="formatDuration(stats.avg_break_time || 0)"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Livraisons/heure</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.deliveries_per_hour || 0).toFixed(1)"></span>
                </div>
            </div>
        </div>

        <!-- Distance Stats -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-route text-green-600 mr-2"></i>
                Statistiques distance
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Distance totale</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.total_distance || 0) + ' km'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Distance moyenne/livraison</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.avg_distance_per_delivery || 0).toFixed(1) + ' km'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Carburant estimé</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.estimated_fuel || 0).toFixed(1) + ' L'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Zones couvertes</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.zones_covered || 0)"></span>
                </div>
            </div>
        </div>

        <!-- Customer Stats -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-users text-purple-600 mr-2"></i>
                Statistiques clients
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Clients uniques</span>
                    <span class="font-semibold text-gray-900" x-text="stats.unique_customers || 0"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Clients satisfaits</span>
                    <span class="font-semibold text-gray-900" x-text="(stats.satisfied_customers || 0) + '%'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Réclamations</span>
                    <span class="font-semibold text-gray-900" x-text="stats.complaints || 0"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Compliments</span>
                    <span class="font-semibold text-gray-900" x-text="stats.compliments || 0"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performing Days -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                Mes meilleures journées
            </h3>
            <div class="space-y-3">
                <template x-for="(day, index) in topDays" :key="index">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <span class="text-yellow-600 font-bold text-sm" x-text="index + 1"></span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="formatDate(day.date)"></p>
                                <p class="text-sm text-gray-600" x-text="day.deliveries + ' livraisons'"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600" x-text="formatAmount(day.earnings)"></p>
                            <p class="text-sm text-gray-600" x-text="day.success_rate + '% réussite'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-comments text-blue-600 mr-2"></i>
                Commentaires récents
            </h3>
            <div class="space-y-4">
                <template x-for="feedback in recentFeedback" :key="feedback.id">
                    <div class="border-l-4 border-blue-400 pl-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="flex items-center">
                                <template x-for="i in 5" :key="i">
                                    <i class="fas fa-star text-sm"
                                       :class="i <= feedback.rating ? 'text-yellow-400' : 'text-gray-300'"></i>
                                </template>
                            </div>
                            <span class="text-sm text-gray-500" x-text="formatDate(feedback.date)"></span>
                        </div>
                        <p class="text-gray-900 text-sm" x-text="feedback.comment"></p>
                        <p class="text-xs text-gray-500 mt-1">Client: <span x-text="feedback.customer_name"></span></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Goals and Achievements -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-target text-red-600 mr-2"></i>
            Objectifs et réalisations
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="goal in goals" :key="goal.id">
                <div class="text-center p-4 border rounded-lg">
                    <div class="mb-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i :class="goal.icon" class="text-white text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900" x-text="goal.title"></h4>
                        <p class="text-sm text-gray-600" x-text="goal.description"></p>
                    </div>

                    <div class="mb-3">
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="h-3 rounded-full bg-gradient-to-r from-green-400 to-blue-500 transition-all duration-300"
                                 :style="`width: ${Math.min(100, (goal.current / goal.target) * 100)}%`"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-1">
                            <span x-text="goal.current"></span>
                            <span x-text="goal.target"></span>
                        </div>
                    </div>

                    <div class="text-sm">
                        <span :class="goal.current >= goal.target ? 'text-green-600 font-semibold' : 'text-gray-600'">
                            <span x-text="Math.round((goal.current / goal.target) * 100)"></span>% complété
                        </span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-gray-50 rounded-lg border p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-download text-gray-600 mr-2"></i>
            Exporter mes statistiques
        </h3>
        <div class="flex flex-wrap gap-3">
            <button @click="exportStats('pdf')"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-file-pdf"></i>
                <span>PDF</span>
            </button>
            <button @click="exportStats('excel')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-file-excel"></i>
                <span>Excel</span>
            </button>
            <button @click="exportStats('csv')"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-file-csv"></i>
                <span>CSV</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('delivererStatistics', (data) => ({
        deliverer: data.deliverer || {},
        stats: data.stats || {},
        selectedPeriod: 'month',
        chartType: 'daily',
        deliveriesChart: null,
        successRateChart: null,

        topDays: [
            { date: '2024-01-15', deliveries: 28, earnings: 2400, success_rate: 96 },
            { date: '2024-01-12', deliveries: 25, earnings: 2100, success_rate: 92 },
            { date: '2024-01-08', deliveries: 24, earnings: 2000, success_rate: 95 }
        ],

        recentFeedback: [
            { id: 1, rating: 5, comment: 'Livreur très professionnel et ponctuel!', customer_name: 'Ahmed K.', date: '2024-01-16' },
            { id: 2, rating: 4, comment: 'Service rapide, merci!', customer_name: 'Fatima B.', date: '2024-01-15' },
            { id: 3, rating: 5, comment: 'Excellent service, je recommande.', customer_name: 'Mohamed S.', date: '2024-01-14' }
        ],

        goals: [
            {
                id: 1,
                title: 'Livraisons mensuelles',
                description: 'Objectif: 500 livraisons ce mois',
                icon: 'fas fa-box',
                current: 387,
                target: 500
            },
            {
                id: 2,
                title: 'Taux de satisfaction',
                description: 'Maintenir 95% de satisfaction',
                icon: 'fas fa-smile',
                current: 92,
                target: 95
            },
            {
                id: 3,
                title: 'Revenus mensuels',
                description: 'Objectif: 40000 DA ce mois',
                icon: 'fas fa-coins',
                current: 31200,
                target: 40000
            }
        ],

        init() {
            this.initCharts();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-DZ');
        },

        formatAmount(amount) {
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 0
            }).format(amount).replace('DZD', 'DA');
        },

        formatDuration(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h ${mins}min` : `${mins}min`;
        },

        async loadPeriodData() {
            try {
                const response = await fetch(`/deliverer/profile/statistics/period/${this.selectedPeriod}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.stats = data.stats;
                    this.updateCharts();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
            }
        },

        initCharts() {
            this.initDeliveriesChart();
            this.initSuccessRateChart();
        },

        initDeliveriesChart() {
            const ctx = document.getElementById('deliveriesChart').getContext('2d');
            this.deliveriesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                    datasets: [{
                        label: 'Livraisons',
                        data: [12, 19, 15, 22, 18, 25, 20],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        initSuccessRateChart() {
            const ctx = document.getElementById('successRateChart').getContext('2d');
            this.successRateChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Livré', 'Échec', 'En attente'],
                    datasets: [{
                        data: [85, 10, 5],
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(239, 68, 68)',
                            'rgb(251, 191, 36)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        updateCharts() {
            // Update charts with new data based on selected period
            if (this.deliveriesChart) {
                this.deliveriesChart.data.datasets[0].data = this.stats.deliveries_chart_data || [];
                this.deliveriesChart.update();
            }

            if (this.successRateChart) {
                this.successRateChart.data.datasets[0].data = [
                    this.stats.success_rate || 0,
                    this.stats.failure_rate || 0,
                    this.stats.pending_rate || 0
                ];
                this.successRateChart.update();
            }
        },

        async exportStats(format) {
            try {
                const response = await fetch(`/deliverer/profile/statistics/export/${format}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        period: this.selectedPeriod,
                        stats: this.stats
                    })
                });

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `statistiques-${this.selectedPeriod}-${Date.now()}.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);

                this.showToast(`Statistiques exportées en ${format.toUpperCase()}`, 'success');
            } catch (error) {
                this.showToast('Erreur lors de l\'export', 'error');
            }
        },

        showToast(message, type = 'success') {
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
    }));
});
</script>
@endpush