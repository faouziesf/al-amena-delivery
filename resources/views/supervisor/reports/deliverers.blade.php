@extends('layouts.supervisor')

@section('title', 'Rapport Livreurs')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rapport Livreurs</h1>
                    <p class="text-gray-600">Performance, statistiques et gestion des équipes de livraison</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <a href="{{ route('supervisor.reports.index') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour aux rapports
                    </a>
                    <button onclick="exportReport()" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Exporter PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Deliverer Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select id="period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500" onchange="updateReport()">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">3 derniers mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500" onchange="updateReport()">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Inactifs</option>
                        <option value="training">En formation</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone</label>
                    <select id="zone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500" onchange="updateReport()">
                        <option value="">Toutes les zones</option>
                        <option value="tunis">Grand Tunis</option>
                        <option value="sfax">Sfax</option>
                        <option value="sousse">Sousse</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Performance</label>
                    <select id="performance" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500" onchange="updateReport()">
                        <option value="">Toutes performances</option>
                        <option value="excellent">Excellence (95%+)</option>
                        <option value="good">Bon (85-95%)</option>
                        <option value="needs_improvement">À améliorer (&lt;85%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                    <input type="text" id="search" placeholder="Nom du livreur..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500" onchange="updateReport()">
                </div>
            </div>
        </div>

        <!-- Deliverer Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Livreurs actifs</h3>
                        <div class="text-2xl font-bold text-orange-600 mt-2">47</div>
                        <div class="text-xs text-orange-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +3 nouveaux ce mois
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-truck text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Performance moyenne</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">91.4%</div>
                        <div class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +2.3% vs mois précédent
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
                        <h3 class="text-sm font-medium text-gray-500">Colis/livreur/jour</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">26.5</div>
                        <div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +1.8 vs mois précédent
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-box text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Note satisfaction</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">4.4/5</div>
                        <div class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +0.2 vs mois précédent
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-star text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Performance Distribution -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Répartition des performances</h3>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                        <option>Ce mois</option>
                        <option>Mois dernier</option>
                        <option>3 derniers mois</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Daily Activity -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Activité quotidienne</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Livraisons</span>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Top 10 performers du mois</h3>
                <div class="flex space-x-2">
                    <button onclick="showAllDeliverers()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-list mr-2"></i>
                        Voir tous
                    </button>
                    <button onclick="exportPerformers()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-medal mr-2"></i>
                        Exporter top
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Rang</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Livreur</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Zone</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Livraisons</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Taux réussite</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Délai moyen</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Note client</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Récompense</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="bg-yellow-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-crown text-yellow-600"></i>
                                    </div>
                                    <span class="font-bold text-yellow-600">1</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-blue-600">AA</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Ahmed Ben Ali</div>
                                        <div class="text-xs text-gray-500">ID: #LIV001 • 3 ans d'ancienneté</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">Tunis Centre</td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">347 colis</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    97.1%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">1.2 jours</td>
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
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    Bonus 200 TND
                                </span>
                            </td>
                        </tr>

                        <tr class="bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-medal text-gray-500"></i>
                                    </div>
                                    <span class="font-bold text-gray-600">2</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-green-600">MT</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Mohamed Trabelsi</div>
                                        <div class="text-xs text-gray-500">ID: #LIV002 • 2 ans d'ancienneté</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">Sfax Ville</td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">312 colis</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    95.8%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">1.4 jours</td>
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
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Bonus 150 TND
                                </span>
                            </td>
                        </tr>

                        <tr class="bg-orange-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-award text-orange-500"></i>
                                    </div>
                                    <span class="font-bold text-orange-600">3</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-medium text-purple-600">KM</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Karim Mejri</div>
                                        <div class="text-xs text-gray-500">ID: #LIV003 • 1.5 ans d'ancienneté</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">Sousse</td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">298 colis</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    94.3%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">1.5 jours</td>
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
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    Bonus 100 TND
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Training & Development -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Formation et développement</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-blue-800 mb-2">Nouveaux livreurs</h4>
                        <div class="text-2xl font-bold text-blue-600 mb-1">8</div>
                        <p class="text-sm text-blue-700">En formation cette semaine</p>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-certificate text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-green-800 mb-2">Certifiés ce mois</h4>
                        <div class="text-2xl font-bold text-green-600 mb-1">12</div>
                        <p class="text-sm text-green-700">Prêts pour livraisons</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-amber-800 mb-2">Nécessitent formation</h4>
                        <div class="text-2xl font-bold text-amber-600 mb-1">5</div>
                        <p class="text-sm text-amber-700">Performance &lt; 85%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insights -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Analyses et recommandations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-thumbs-up text-green-600 mr-2"></i>
                        <h4 class="font-semibold text-green-800">Performance globale</h4>
                    </div>
                    <p class="text-sm text-green-700">91.4% de performance moyenne, soit +2.3% par rapport au mois précédent. Excellente progression de l'équipe.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                        <h4 class="font-semibold text-blue-800">Recrutement</h4>
                    </div>
                    <p class="text-sm text-blue-700">3 nouveaux livreurs ce mois. Taux de rétention de 94%. Maintenir le programme d'intégration.</p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-chalkboard-teacher text-amber-600 mr-2"></i>
                        <h4 class="font-semibold text-amber-800">Formation prioritaire</h4>
                    </div>
                    <p class="text-sm text-amber-700">5 livreurs nécessitent une formation complémentaire. Focus sur la gestion du temps et satisfaction client.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Distribution Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
new Chart(performanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['Excellence (95%+)', 'Bon (85-95%)', 'À améliorer (<85%)'],
        datasets: [{
            data: [23, 19, 5],
            backgroundColor: [
                'rgb(34, 197, 94)',
                'rgb(59, 130, 246)',
                'rgb(249, 115, 22)'
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

// Daily Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
            label: 'Livraisons',
            data: [245, 289, 267, 298, 312, 189, 156],
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
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

function updateReport() {
    console.log('Updating deliverer report...');
}

function exportReport() {
    alert('Export du rapport livreurs en PDF...');
}

function exportPerformers() {
    alert('Export du classement des top performers...');
}

function showAllDeliverers() {
    alert('Redirection vers la liste complète des livreurs...');
}
</script>
@endsection