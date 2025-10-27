<x-layouts.supervisor-new>
    <x-slot name="title">Rapports Financiers</x-slot>
    <x-slot name="subtitle">Analyse et visualisation des performances financières</x-slot>

    <div x-data="{
        period: 'month',
        dateFrom: '',
        dateTo: '',
        loading: false,
        reportData: null,
        
        async generateReport() {
            this.loading = true;
            try {
                const response = await fetch('/supervisor/financial/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        period: this.period,
                        date_from: this.dateFrom,
                        date_to: this.dateTo
                    })
                });
                this.reportData = await response.json();
            } catch (error) {
                console.error('Erreur génération rapport:', error);
            } finally {
                this.loading = false;
            }
        }
    }" class="space-y-6">

        <!-- Period Selector -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sélectionner une Période</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <button @click="period = 'today'; generateReport()" 
                        :class="period === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-3 rounded-lg font-medium transition hover:shadow">
                    Aujourd'hui
                </button>
                <button @click="period = 'week'; generateReport()" 
                        :class="period === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-3 rounded-lg font-medium transition hover:shadow">
                    Cette Semaine
                </button>
                <button @click="period = 'month'; generateReport()" 
                        :class="period === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-3 rounded-lg font-medium transition hover:shadow">
                    Ce Mois
                </button>
                <button @click="period = 'year'; generateReport()" 
                        :class="period === 'year' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="px-4 py-3 rounded-lg font-medium transition hover:shadow">
                    Cette Année
                </button>
            </div>

            <div class="flex items-end space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période Personnalisée</label>
                    <div class="flex space-x-2">
                        <input type="date" x-model="dateFrom" class="flex-1 px-4 py-2 border rounded-lg">
                        <input type="date" x-model="dateTo" class="flex-1 px-4 py-2 border rounded-lg">
                    </div>
                </div>
                <button @click="period = 'custom'; generateReport()" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Générer
                </button>
            </div>
        </div>

        <!-- Report Content -->
        <template x-if="reportData">
            <div class="space-y-6">
                <!-- Main KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-blue-100 text-sm">Revenus</p>
                        <p class="text-3xl font-bold mt-2" x-text="(reportData.total_revenue || 0).toFixed(3) + ' DT'"></p>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-red-100 text-sm">Charges</p>
                        <p class="text-3xl font-bold mt-2" x-text="(reportData.total_charges || 0).toFixed(3) + ' DT'"></p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-green-100 text-sm">Bénéfice</p>
                        <p class="text-3xl font-bold mt-2" x-text="(reportData.profit || 0).toFixed(3) + ' DT'"></p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <p class="text-purple-100 text-sm">Marge</p>
                        <p class="text-3xl font-bold mt-2" x-text="(reportData.profit_margin || 0).toFixed(1) + '%'"></p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Répartition des Charges</h3>
                        <canvas id="chargesChart" height="200"></canvas>
                    </div>
                    <div class="bg-white rounded-xl shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Évolution</h3>
                        <canvas id="evolutionChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="flex justify-end">
                    <a :href="'/supervisor/financial/reports/export?period=' + period" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                        📥 Exporter en CSV
                    </a>
                </div>
            </div>
        </template>

        <!-- Loading -->
        <div x-show="loading" class="text-center py-12">
            <div class="animate-spin h-12 w-12 border-4 border-blue-600 border-t-transparent rounded-full mx-auto"></div>
            <p class="mt-4 text-gray-600">Génération du rapport...</p>
        </div>
    </div>
</x-layouts.supervisor-new>
