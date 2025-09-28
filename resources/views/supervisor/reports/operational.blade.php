@extends('layouts.supervisor')

@section('title', 'Rapport Opérationnel')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rapport Opérationnel</h1>
                    <p class="text-gray-600">Analyse des performances de livraison et efficacité opérationnelle</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <a href="{{ route('supervisor.reports.index') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour aux rapports
                    </a>
                    <button onclick="exportReport()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Exporter PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Operational Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select id="period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">3 derniers mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Délégation</label>
                    <select id="delegation" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="">Toutes les délégations</option>
                        <option value="tunis">Tunis</option>
                        <option value="sfax">Sfax</option>
                        <option value="sousse">Sousse</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de service</label>
                    <select id="serviceType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="">Tous les services</option>
                        <option value="fast">Livraison rapide</option>
                        <option value="advanced">Livraison avancée</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Livreur</label>
                    <select id="deliverer" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="updateReport()">
                        <option value="">Tous les livreurs</option>
                        <option value="1">Ahmed Ben Ali</option>
                        <option value="2">Mohamed Trabelsi</option>
                        <option value="3">Fatma Bouazizi</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Key Operational Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Taux de livraison</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">94.2%</div>
                        <div class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +2.1% vs période précédente
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Délai moyen</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">1.8 jours</div>
                        <div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-arrow-down mr-1"></i>
                            -0.3j vs période précédente
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Colis livrés</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">1,247</div>
                        <div class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +18.5% vs période précédente
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-box text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Taux de retour</h3>
                        <div class="text-2xl font-bold text-orange-600 mt-2">3.4%</div>
                        <div class="text-xs text-red-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +0.8% vs période précédente
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-undo text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Delivery Performance Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Performance des livraisons</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Livrées</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Retours</span>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Delivery Status Distribution -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Répartition des statuts</h3>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                        <option>Aujourd'hui</option>
                        <option>Cette semaine</option>
                        <option>Ce mois</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Deliverer Performance -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Performance des livreurs</h3>
                <button onclick="exportDelivererReport()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-users mr-2"></i>
                    Rapport détaillé
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Livreur</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Colis attribués</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Colis livrés</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Taux de réussite</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Délai moyen</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Note</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-blue-600">AA</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Ahmed Ben Ali</div>
                                        <div class="text-xs text-gray-500">ID: #LIV001</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">156</td>
                            <td class="py-3 px-4 text-sm text-gray-600">149</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    95.5%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">1.4 jours</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 5; $i++)
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">4.8</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Actif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-green-600">MT</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Mohamed Trabelsi</div>
                                        <div class="text-xs text-gray-500">ID: #LIV002</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">143</td>
                            <td class="py-3 px-4 text-sm text-gray-600">134</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    93.7%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">1.6 jours</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 4; $i++)
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                        @endfor
                                        <i class="far fa-star text-gray-300 text-xs"></i>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">4.5</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Actif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-purple-600">FB</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Fatma Bouazizi</div>
                                        <div class="text-xs text-gray-500">ID: #LIV003</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">127</td>
                            <td class="py-3 px-4 text-sm text-gray-600">115</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    90.6%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">2.1 jours</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 4; $i++)
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                        @endfor
                                        <i class="far fa-star text-gray-300 text-xs"></i>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">4.2</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    Formation
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Operational Insights -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Analyses opérationnelles et recommandations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-trophy text-green-600 mr-2"></i>
                        <h4 class="font-semibold text-green-800">Performance excellente</h4>
                    </div>
                    <p class="text-sm text-green-700">Taux de livraison de 94.2% et délai moyen de 1.8 jours, performances supérieures aux objectifs fixés.</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-exclamation-triangle text-amber-600 mr-2"></i>
                        <h4 class="font-semibold text-amber-800">Point d'attention</h4>
                    </div>
                    <p class="text-sm text-amber-700">Le taux de retour a légèrement augmenté (+0.8%). Analyser les causes principales et former les livreurs.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-lightbulb text-blue-600 mr-2"></i>
                        <h4 class="font-semibold text-blue-800">Optimisation</h4>
                    </div>
                    <p class="text-sm text-blue-700">Redistribuer la charge de travail pour optimiser les performances et réduire le délai moyen à moins de 1.5 jour.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
new Chart(performanceCtx, {
    type: 'bar',
    data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
            label: 'Livrées',
            data: [89, 95, 87, 92, 98, 76, 82],
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }, {
            label: 'Retours',
            data: [4, 2, 6, 3, 1, 8, 5],
            backgroundColor: 'rgba(249, 115, 22, 0.8)',
            borderColor: 'rgb(249, 115, 22)',
            borderWidth: 1
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
                beginAtZero: true
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Livrés', 'En cours', 'Retours', 'Annulés'],
        datasets: [{
            data: [1247, 89, 45, 12],
            backgroundColor: [
                'rgb(34, 197, 94)',
                'rgb(59, 130, 246)',
                'rgb(249, 115, 22)',
                'rgb(239, 68, 68)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
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

function updateReport() {
    console.log('Updating operational report...');
}

function exportReport() {
    alert('Export du rapport opérationnel en PDF...');
}

function exportDelivererReport() {
    alert('Export du rapport détaillé des livreurs...');
}
</script>
@endsection