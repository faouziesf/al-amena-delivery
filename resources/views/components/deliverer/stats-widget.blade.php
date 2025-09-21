@props([
    'title' => 'Statistiques',
    'period' => 'today', // today, week, month
    'showChart' => false,
    'refreshable' => true,
    'stats' => null
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200']) }}
     x-data="statsWidget({
        period: '{{ $period }}',
        showChart: {{ $showChart ? 'true' : 'false' }},
        refreshable: {{ $refreshable ? 'true' : 'false' }},
        initialStats: {{ json_encode($stats) }}
     })"
     x-init="loadStats()">

    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            <p class="text-sm text-gray-600" x-text="getPeriodLabel()"></p>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Period Selector -->
            <select x-model="period" @change="loadStats()"
                    class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
            </select>

            @if($refreshable)
            <!-- Refresh Button -->
            <button @click="loadStats()"
                    :disabled="loading"
                    class="p-2 text-gray-400 hover:text-gray-600 disabled:opacity-50 transition-colors">
                <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            @endif
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-sm text-gray-600">Chargement des statistiques...</p>
    </div>

    <!-- Stats Content -->
    <div x-show="!loading" class="p-4">

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Packages -->
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-blue-600" x-text="stats.total_packages || 0"></div>
                <div class="text-sm text-blue-700">Total Colis</div>
            </div>

            <!-- Delivered -->
            <div class="text-center p-4 bg-emerald-50 rounded-lg">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-emerald-600" x-text="stats.delivered_packages || 0"></div>
                <div class="text-sm text-emerald-700">Livrés</div>
            </div>

            <!-- Pending -->
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-yellow-600" x-text="stats.pending_packages || 0"></div>
                <div class="text-sm text-yellow-700">En attente</div>
            </div>

            <!-- Earnings -->
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-purple-600" x-text="formatMoney(stats.total_earnings || 0)"></div>
                <div class="text-sm text-purple-700">Gains</div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Success Rate -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Taux de réussite</span>
                    <span class="text-sm font-bold text-gray-900" x-text="getSuccessRate() + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-emerald-600 h-2 rounded-full transition-all duration-300"
                         :style="`width: ${getSuccessRate()}%`"></div>
                </div>
            </div>

            <!-- Average per Day -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Moyenne/jour</span>
                    <span class="text-lg font-bold text-gray-900" x-text="getAveragePerDay()"></span>
                </div>
                <p class="text-xs text-gray-600 mt-1">Colis traités</p>
            </div>

            <!-- COD Collected -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">COD collecté</span>
                    <span class="text-lg font-bold text-emerald-600" x-text="formatMoney(stats.total_cod_collected || 0)"></span>
                </div>
                <p class="text-xs text-gray-600 mt-1">Montant total</p>
            </div>
        </div>

        @if($showChart)
        <!-- Chart Section -->
        <div class="border-t pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Évolution</h4>
            <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center" x-ref="chartContainer">
                <div x-show="!chartData || chartData.length === 0" class="text-gray-500 text-sm">
                    Aucune donnée pour le graphique
                </div>
                <!-- Chart will be rendered here -->
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="border-t pt-4 mt-4">
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-scanner')"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                    </svg>
                    Scanner
                </button>

                <a href="{{ route('deliverer.packages.index') }}"
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Voir tous les colis
                </a>

                <a href="{{ route('deliverer.wallet.index') }}"
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Portefeuille
                </a>
            </div>
        </div>

        <!-- Last Update -->
        <div class="text-xs text-gray-500 text-center mt-4" x-show="lastUpdate">
            Dernière mise à jour: <span x-text="formatLastUpdate()"></span>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="error" class="p-6 text-center">
        <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Erreur de chargement</h3>
        <p class="text-sm text-gray-600 mb-4" x-text="error"></p>
        <button @click="loadStats()"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            Réessayer
        </button>
    </div>
</div>

@push('scripts')
<script>
function statsWidget(options = {}) {
    return {
        // Configuration
        period: options.period || 'today',
        showChart: options.showChart || false,
        refreshable: options.refreshable !== false,

        // State
        loading: false,
        error: null,
        stats: options.initialStats || {},
        chartData: [],
        lastUpdate: null,

        async init() {
            if (!options.initialStats) {
                await this.loadStats();
            } else {
                this.lastUpdate = new Date();
            }

            // Auto-refresh every 5 minutes
            if (this.refreshable) {
                setInterval(() => {
                    this.loadStats();
                }, 300000);
            }
        },

        async loadStats() {
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(`/deliverer/api/stats?period=${this.period}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.stats = data.stats;
                    this.chartData = data.chart_data || [];
                    this.lastUpdate = new Date();

                    if (this.showChart && this.chartData.length > 0) {
                        this.renderChart();
                    }
                } else {
                    throw new Error(data.message || 'Erreur lors du chargement');
                }

            } catch (error) {
                console.error('Erreur stats:', error);
                this.error = 'Impossible de charger les statistiques';
            } finally {
                this.loading = false;
            }
        },

        renderChart() {
            // Simple chart rendering with canvas
            if (!this.$refs.chartContainer || !this.chartData.length) return;

            const canvas = document.createElement('canvas');
            canvas.width = this.$refs.chartContainer.offsetWidth;
            canvas.height = 128;

            this.$refs.chartContainer.innerHTML = '';
            this.$refs.chartContainer.appendChild(canvas);

            const ctx = canvas.getContext('2d');
            this.drawSimpleChart(ctx, canvas.width, canvas.height);
        },

        drawSimpleChart(ctx, width, height) {
            const padding = 20;
            const chartWidth = width - 2 * padding;
            const chartHeight = height - 2 * padding;

            // Clear canvas
            ctx.clearRect(0, 0, width, height);

            if (!this.chartData.length) return;

            // Find max value
            const maxValue = Math.max(...this.chartData.map(d => d.value));
            const stepX = chartWidth / (this.chartData.length - 1);

            // Draw grid lines
            ctx.strokeStyle = '#e5e7eb';
            ctx.lineWidth = 1;

            for (let i = 0; i <= 4; i++) {
                const y = padding + (chartHeight / 4) * i;
                ctx.beginPath();
                ctx.moveTo(padding, y);
                ctx.lineTo(width - padding, y);
                ctx.stroke();
            }

            // Draw line
            ctx.strokeStyle = '#3b82f6';
            ctx.lineWidth = 2;
            ctx.beginPath();

            this.chartData.forEach((point, index) => {
                const x = padding + stepX * index;
                const y = padding + chartHeight - (point.value / maxValue) * chartHeight;

                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });

            ctx.stroke();

            // Draw points
            ctx.fillStyle = '#3b82f6';
            this.chartData.forEach((point, index) => {
                const x = padding + stepX * index;
                const y = padding + chartHeight - (point.value / maxValue) * chartHeight;

                ctx.beginPath();
                ctx.arc(x, y, 3, 0, 2 * Math.PI);
                ctx.fill();
            });
        },

        getPeriodLabel() {
            const labels = {
                'today': "Statistiques d'aujourd'hui",
                'week': 'Statistiques de cette semaine',
                'month': 'Statistiques de ce mois'
            };
            return labels[this.period] || labels.today;
        },

        getSuccessRate() {
            const total = this.stats.total_packages || 0;
            const delivered = this.stats.delivered_packages || 0;

            if (total === 0) return 0;
            return Math.round((delivered / total) * 100);
        },

        getAveragePerDay() {
            const total = this.stats.total_packages || 0;
            let days = 1;

            if (this.period === 'week') {
                days = 7;
            } else if (this.period === 'month') {
                days = 30;
            }

            return Math.round(total / days);
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('fr-TN', {
                style: 'currency',
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount);
        },

        formatLastUpdate() {
            if (!this.lastUpdate) return '';

            return this.lastUpdate.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush