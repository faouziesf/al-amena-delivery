@extends('layouts.supervisor')

@section('title', 'Rapport Clients')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rapport Clients</h1>
                    <p class="text-gray-600">Analyse des comportements et satisfaction des clients</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <a href="{{ route('supervisor.reports.index') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour aux rapports
                    </a>
                    <button onclick="exportReport()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Exporter PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Client Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période d'analyse</label>
                    <select id="period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500" onchange="updateReport()">
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">3 derniers mois</option>
                        <option value="180">6 derniers mois</option>
                        <option value="365">12 derniers mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Segmentation</label>
                    <select id="segment" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500" onchange="updateReport()">
                        <option value="">Tous les segments</option>
                        <option value="new">Nouveaux clients</option>
                        <option value="regular">Clients réguliers</option>
                        <option value="vip">Clients VIP</option>
                        <option value="inactive">Clients inactifs</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Volume d'expédition</label>
                    <select id="volume" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500" onchange="updateReport()">
                        <option value="">Tous volumes</option>
                        <option value="low">1-10 colis/mois</option>
                        <option value="medium">11-50 colis/mois</option>
                        <option value="high">50+ colis/mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Région</label>
                    <select id="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500" onchange="updateReport()">
                        <option value="">Toutes les régions</option>
                        <option value="tunis">Grand Tunis</option>
                        <option value="sfax">Sfax</option>
                        <option value="sousse">Sousse-Monastir</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Client Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Clients actifs</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">1,247</div>
                        <div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +15.2% vs période précédente
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Nouveaux clients</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">187</div>
                        <div class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +28.4% vs période précédente
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-user-plus text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Satisfaction moyenne</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">4.6/5</div>
                        <div class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +0.3 vs période précédente
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-star text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Taux de rétention</h3>
                        <div class="text-2xl font-bold text-orange-600 mt-2">87.3%</div>
                        <div class="text-xs text-orange-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +4.2% vs période précédente
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-heart text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Client Growth Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Évolution de la clientèle</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Actifs</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Nouveaux</span>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="clientGrowthChart"></canvas>
                </div>
            </div>

            <!-- Client Satisfaction -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Répartition de la satisfaction</h3>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                        <option>30 derniers jours</option>
                        <option>3 derniers mois</option>
                        <option>6 derniers mois</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex mr-3">
                                @for($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                @endfor
                            </div>
                            <span class="text-sm font-medium text-gray-700">5 étoiles</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 68%"></div>
                            </div>
                            <span class="text-sm text-gray-600">68%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex mr-3">
                                @for($i = 0; $i < 4; $i++)
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                @endfor
                                <i class="far fa-star text-gray-300 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">4 étoiles</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 24%"></div>
                            </div>
                            <span class="text-sm text-gray-600">24%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex mr-3">
                                @for($i = 0; $i < 3; $i++)
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                @endfor
                                @for($i = 0; $i < 2; $i++)
                                    <i class="far fa-star text-gray-300 text-sm"></i>
                                @endfor
                            </div>
                            <span class="text-sm font-medium text-gray-700">3 étoiles</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 6%"></div>
                            </div>
                            <span class="text-sm text-gray-600">6%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex mr-3">
                                @for($i = 0; $i < 2; $i++)
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                @endfor
                                @for($i = 0; $i < 3; $i++)
                                    <i class="far fa-star text-gray-300 text-sm"></i>
                                @endfor
                            </div>
                            <span class="text-sm font-medium text-gray-700">2 étoiles</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: 2%"></div>
                            </div>
                            <span class="text-sm text-gray-600">2%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex mr-3">
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                @for($i = 0; $i < 4; $i++)
                                    <i class="far fa-star text-gray-300 text-sm"></i>
                                @endfor
                            </div>
                            <span class="text-sm font-medium text-gray-700">1 étoile</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: 0%"></div>
                            </div>
                            <span class="text-sm text-gray-600">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clients -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Top 10 clients - Volume d'expédition</h3>
                <div class="flex space-x-2">
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                        <option>Ce mois</option>
                        <option>3 derniers mois</option>
                        <option>Cette année</option>
                    </select>
                    <button onclick="exportTopClients()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-crown mr-2"></i>
                        Exporter VIP
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Rang</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Client</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Colis expédiés</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">CA généré</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Satisfaction</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Dernière commande</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-bold text-yellow-600">1</span>
                                    </div>
                                    <i class="fas fa-crown text-yellow-500 text-sm"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-blue-600">TC</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">TechnoCommerce SARL</div>
                                        <div class="text-xs text-gray-500">client@technocommerce.tn</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">1,247 colis</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">18,450 TND</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 5; $i++)
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">4.9</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">Il y a 2 heures</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    VIP
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-bold text-gray-600">2</span>
                                    </div>
                                    <i class="fas fa-medal text-gray-400 text-sm"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-green-600">FS</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Fashion Store</div>
                                        <div class="text-xs text-gray-500">orders@fashionstore.tn</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">892 colis</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">12,340 TND</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="flex">
                                        @for($i = 0; $i < 4; $i++)
                                            <i class="fas fa-star text-yellow-400 text-xs"></i>
                                        @endfor
                                        <i class="far fa-star text-gray-300 text-xs"></i>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">4.7</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">Il y a 1 jour</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Premium
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center mr-2">
                                        <span class="text-xs font-bold text-orange-600">3</span>
                                    </div>
                                    <i class="fas fa-award text-orange-500 text-sm"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-purple-600">EM</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">ElectroMax</div>
                                        <div class="text-xs text-gray-500">commandes@electromax.tn</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">654 colis</td>
                            <td class="py-3 px-4 text-sm font-semibold text-green-600">9,870 TND</td>
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
                            <td class="py-3 px-4 text-sm text-gray-600">Il y a 3 jours</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    Régulier
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Client Insights -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Analyses clients et recommandations</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-users text-green-600 mr-2"></i>
                        <h4 class="font-semibold text-green-800">Croissance clientèle</h4>
                    </div>
                    <p class="text-sm text-green-700">+187 nouveaux clients ce mois (+28.4%). La stratégie d'acquisition fonctionne très bien.</p>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-heart text-purple-600 mr-2"></i>
                        <h4 class="font-semibold text-purple-800">Satisfaction élevée</h4>
                    </div>
                    <p class="text-sm text-purple-700">Note moyenne de 4.6/5 et 92% de satisfaction. Maintenir cette qualité de service.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-bullseye text-blue-600 mr-2"></i>
                        <h4 class="font-semibold text-blue-800">Opportunité VIP</h4>
                    </div>
                    <p class="text-sm text-blue-700">23 clients proches du statut VIP. Programme de fidélisation à intensifier.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Client Growth Chart
const growthCtx = document.getElementById('clientGrowthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
        datasets: [{
            label: 'Clients actifs',
            data: [980, 1050, 1120, 1180, 1220, 1247],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Nouveaux clients',
            data: [45, 67, 78, 89, 124, 187],
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true
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

function updateReport() {
    console.log('Updating client report...');
}

function exportReport() {
    alert('Export du rapport clients en PDF...');
}

function exportTopClients() {
    alert('Export de la liste des clients VIP...');
}
</script>
@endsection