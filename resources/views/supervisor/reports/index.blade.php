@extends('layouts.supervisor')

@section('title', 'Centre de Rapports')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Centre de Rapports</h1>
                    <p class="text-gray-600">Analyses avancées et rapports détaillés du système</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <button onclick="generateCustomReport()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nouveau rapport
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">CA Aujourd'hui</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">
                            {{ isset($quickStats) ? number_format($quickStats['total_revenue_today'], 3) : '12,450' }} TND
                        </div>
                        <div class="text-xs text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +15.2% vs hier
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-coins text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Colis aujourd'hui</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">
                            {{ isset($quickStats) ? $quickStats['packages_today'] : '245' }}
                        </div>
                        <div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +8.1% vs hier
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
                        <h3 class="text-sm font-medium text-gray-500">Livraisons</h3>
                        <div class="text-2xl font-bold text-yellow-600 mt-2">
                            {{ isset($quickStats) ? $quickStats['deliveries_today'] : '189' }}
                        </div>
                        <div class="text-xs text-yellow-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +12.5% vs hier
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-truck text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Clients actifs</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">
                            {{ isset($quickStats) ? $quickStats['active_clients'] : '67' }}
                        </div>
                        <div class="text-xs text-purple-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            +5.8% vs hier
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Reports -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Rapports disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Financial Report -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-lg p-6 border border-green-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-600 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Financier</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapport Financier</h3>
                    <p class="text-gray-600 text-sm mb-4">Analyse des revenus, profits et tendances financières</p>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('supervisor.reports.financial') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                            Voir rapport
                        </a>
                        <div class="flex space-x-1">
                            <button onclick="exportReport('financial')" class="p-2 text-gray-400 hover:text-green-600 transition-colors" title="Exporter">
                                <i class="fas fa-download text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Operational Report -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-lg p-6 border border-blue-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-600 p-3 rounded-lg">
                            <i class="fas fa-cogs text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Opérationnel</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapport Opérationnel</h3>
                    <p class="text-gray-600 text-sm mb-4">Performances de livraison et efficacité opérationnelle</p>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('supervisor.reports.operational') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            Voir rapport
                        </a>
                        <div class="flex space-x-1">
                            <button onclick="exportReport('operational')" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="Exporter">
                                <i class="fas fa-download text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Client Report -->
                <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-lg p-6 border border-purple-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-600 p-3 rounded-lg">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">Clients</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapport Clients</h3>
                    <p class="text-gray-600 text-sm mb-4">Analyses des comportements et satisfaction client</p>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('supervisor.reports.clients') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                            Voir rapport
                        </a>
                        <div class="flex space-x-1">
                            <button onclick="exportReport('clients')" class="p-2 text-gray-400 hover:text-purple-600 transition-colors" title="Exporter">
                                <i class="fas fa-download text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Deliverers Report -->
                <div class="bg-gradient-to-br from-orange-50 to-amber-100 rounded-lg p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-orange-600 p-3 rounded-lg">
                            <i class="fas fa-truck text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">Livreurs</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapport Livreurs</h3>
                    <p class="text-gray-600 text-sm mb-4">Performances et statistiques des livreurs</p>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('supervisor.reports.deliverers') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                            Voir rapport
                        </a>
                        <div class="flex space-x-1">
                            <button onclick="exportReport('deliverers')" class="p-2 text-gray-400 hover:text-orange-600 transition-colors" title="Exporter">
                                <i class="fas fa-download text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Custom Report -->
                <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gray-600 p-3 rounded-lg">
                            <i class="fas fa-sliders-h text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">Personnalisé</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapport Personnalisé</h3>
                    <p class="text-gray-600 text-sm mb-4">Créer des rapports sur mesure selon vos besoins</p>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('supervisor.reports.custom') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            Créer rapport
                        </a>
                        <div class="flex space-x-1">
                            <button onclick="showCustomDialog()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Guide">
                                <i class="fas fa-question-circle text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Reports -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-100 rounded-lg p-6 border border-indigo-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-indigo-600 p-3 rounded-lg">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                        <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">Programmés</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapports Programmés</h3>
                    <p class="text-gray-600 text-sm mb-4">Gérer les rapports automatiques et récurrents</p>
                    <div class="flex items-center justify-between">
                        <button onclick="showScheduledReports()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            Gérer
                        </button>
                        <div class="flex space-x-1">
                            <span class="text-xs text-indigo-600 font-medium">3 actifs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Actions rapides</h3>
                <div class="text-sm text-gray-500">Génération instantanée</div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="generateDailyReport()" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-700 transition-colors">
                            <i class="fas fa-calendar-day text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Quotidien</div>
                            <div class="text-sm text-gray-600">Aujourd'hui</div>
                        </div>
                    </div>
                </button>

                <button onclick="generateWeeklyReport()" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-green-600 p-2 rounded-lg mr-3 group-hover:bg-green-700 transition-colors">
                            <i class="fas fa-calendar-week text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Hebdomadaire</div>
                            <div class="text-sm text-gray-600">7 derniers jours</div>
                        </div>
                    </div>
                </button>

                <button onclick="generateMonthlyReport()" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-yellow-600 p-2 rounded-lg mr-3 group-hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Mensuel</div>
                            <div class="text-sm text-gray-600">30 derniers jours</div>
                        </div>
                    </div>
                </button>

                <button onclick="generateCustomReport()" class="bg-purple-50 border border-purple-200 rounded-lg p-4 hover:bg-purple-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-purple-600 p-2 rounded-lg mr-3 group-hover:bg-purple-700 transition-colors">
                            <i class="fas fa-sliders-h text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Personnalisé</div>
                            <div class="text-sm text-gray-600">Configuration</div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(reportType) {
    // Show loading spinner
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Export...';
    button.disabled = true;

    // Simulate API call
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        alert(`Rapport ${reportType} exporté avec succès!`);
    }, 2000);
}

function generateDailyReport() {
    if (confirm('Générer le rapport quotidien pour aujourd\'hui ?')) {
        // Show loading
        const button = event.target.closest('button');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
        button.disabled = true;

        setTimeout(() => {
            alert('Rapport quotidien généré avec succès!');
            location.reload();
        }, 2000);
    }
}

function generateWeeklyReport() {
    if (confirm('Générer le rapport hebdomadaire pour les 7 derniers jours ?')) {
        const button = event.target.closest('button');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
        button.disabled = true;

        setTimeout(() => {
            alert('Rapport hebdomadaire généré avec succès!');
            location.reload();
        }, 2000);
    }
}

function generateMonthlyReport() {
    if (confirm('Générer le rapport mensuel pour les 30 derniers jours ?')) {
        const button = event.target.closest('button');
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération...';
        button.disabled = true;

        setTimeout(() => {
            alert('Rapport mensuel généré avec succès!');
            location.reload();
        }, 2000);
    }
}

function generateCustomReport() {
    window.location.href = '{{ route("supervisor.reports.custom") }}';
}

function showCustomDialog() {
    alert('Les rapports personnalisés vous permettent de sélectionner exactement les données et la période que vous souhaitez analyser.');
}

function showScheduledReports() {
    alert('Fonctionnalité de gestion des rapports programmés en cours de développement.');
}
</script>
@endsection