@extends('layouts.supervisor')

@section('title', 'Rapport Financier')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rapport Financier</h1>
                    <p class="text-gray-600">Analyse détaillée des revenus, profits et tendances financières</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <a href="{{ route('supervisor.reports.index') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour aux rapports
                    </a>
                    <button onclick="exportReport()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Exporter PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Financial Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select id="period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">3 derniers mois</option>
                        <option value="365">12 derniers mois</option>
                        <option value="custom">Période personnalisée</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <input type="date" id="dateFrom" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        value="{{ now()->subDays(30)->format('Y-m-d') }}" onchange="updateReport()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input type="date" id="dateTo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        value="{{ now()->format('Y-m-d') }}" onchange="updateReport()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'analyse</label>
                    <select id="analysisType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="revenue">Revenus</option>
                        <option value="profit">Profits</option>
                        <option value="cod">COD</option>
                        <option value="commission">Commissions</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Financial Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Chiffre d'affaires</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">45,250 TND</div>
                        <div class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +12.5% vs période précédente
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Profit net</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">18,750 TND</div>
                        <div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +8.3% vs période précédente
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-coins text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">COD collecté</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">32,100 TND</div>
                        <div class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +15.7% vs période précédente
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-hand-holding-usd text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Marge bénéficiaire</h3>
                        <div class="text-2xl font-bold text-orange-600 mt-2">41.4%</div>
                        <div class="text-xs text-orange-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +2.1% vs période précédente
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-percentage text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Évolution du chiffre d'affaires</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Revenus</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Profits</span>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Revenue by Service Type -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Revenus par type de service</h3>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                        <option>Ce mois</option>
                        <option>Mois dernier</option>
                        <option>3 derniers mois</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">Livraison rapide</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">25,400 TND</div>
                            <div class="text-xs text-gray-500">56.2%</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 56.2%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">Livraison avancée</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">19,850 TND</div>
                            <div class="text-xs text-gray-500">43.8%</div>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 43.8%"></div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">45,250 TND</div>
                        <div class="text-sm text-gray-600">Total des revenus</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Détail financier par période</h3>
                <button onclick="exportTable()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    <i class="fas fa-table mr-2"></i>
                    Exporter tableau
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Période</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Colis livrés</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">CA total</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">COD collecté</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Commissions</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Profit net</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Marge %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-900">Semaine 1</td>
                            <td class="py-3 px-4 text-sm text-gray-600">245</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">12,450 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">8,200 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">1,850 TND</td>
                            <td class="py-3 px-4 text-sm font-semibold text-blue-600">4,950 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">39.8%</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-900">Semaine 2</td>
                            <td class="py-3 px-4 text-sm text-gray-600">312</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">15,600 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">10,800 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">2,340 TND</td>
                            <td class="py-3 px-4 text-sm font-semibold text-blue-600">6,240 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">40.0%</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-900">Semaine 3</td>
                            <td class="py-3 px-4 text-sm text-gray-600">189</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">9,450 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">6,300 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">1,420 TND</td>
                            <td class="py-3 px-4 text-sm font-semibold text-blue-600">3,780 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">40.0%</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-900">Semaine 4</td>
                            <td class="py-3 px-4 text-sm text-gray-600">278</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">13,900 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">9,600 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">2,085 TND</td>
                            <td class="py-3 px-4 text-sm font-semibold text-blue-600">5,565 TND</td>
                            <td class="py-3 px-4 text-sm text-gray-600">40.0%</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 bg-gray-50">
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">TOTAL</td>
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">1,024</td>
                            <td class="py-3 px-4 text-sm font-bold text-green-600">51,400 TND</td>
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">34,900 TND</td>
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">7,695 TND</td>
                            <td class="py-3 px-4 text-sm font-bold text-blue-600">20,535 TND</td>
                            <td class="py-3 px-4 text-sm font-bold text-gray-900">39.9%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Financial Insights -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Analyses et recommandations</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-chart-line text-green-600 mr-2"></i>
                        <h4 class="font-semibold text-green-800">Tendance positive</h4>
                    </div>
                    <p class="text-sm text-green-700">Le chiffre d'affaires est en hausse de 12.5% par rapport au mois précédent, avec une croissance constante.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-bullseye text-blue-600 mr-2"></i>
                        <h4 class="font-semibold text-blue-800">Objectif atteint</h4>
                    </div>
                    <p class="text-sm text-blue-700">La marge bénéficiaire de 41.4% dépasse l'objectif fixé à 38%. Excellente performance financière.</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-lightbulb text-amber-600 mr-2"></i>
                        <h4 class="font-semibold text-amber-800">Recommandation</h4>
                    </div>
                    <p class="text-sm text-amber-700">Optimiser la collecte COD pour améliorer le flux de trésorerie et réduire les créances en attente.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'],
        datasets: [{
            label: 'Revenus',
            data: [12450, 15600, 9450, 13900, 16200],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }, {
            label: 'Profits',
            data: [4950, 6240, 3780, 5565, 6480],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' TND';
                    }
                }
            }
        }
    }
});

function updateReport() {
    // Simulate report update
    console.log('Updating financial report...');
}

function exportReport() {
    alert('Export du rapport financier en PDF...');
}

function exportTable() {
    alert('Export du tableau financier en Excel...');
}
</script>
@endsection