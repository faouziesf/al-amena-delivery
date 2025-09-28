@extends('layouts.supervisor')

@section('title', 'Rapports personnalisés')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100" x-data="customReportsData()">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rapports personnalisés</h1>
                    <p class="text-gray-600">Créez et gérez vos rapports sur mesure avec des filtres avancés</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <button @click="showTemplates = true" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>
                        Templates
                    </button>
                    <button @click="showCreateReport = true" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nouveau rapport
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select x-model="filters.period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="quarter">Ce trimestre</option>
                        <option value="year">Cette année</option>
                        <option value="custom">Personnalisé</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de données</label>
                    <select x-model="filters.dataType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">Toutes</option>
                        <option value="packages">Colis</option>
                        <option value="financial">Financier</option>
                        <option value="users">Utilisateurs</option>
                        <option value="performance">Performance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                    <select x-model="filters.format" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="table">Tableau</option>
                        <option value="chart">Graphique</option>
                        <option value="mixed">Mixte</option>
                        <option value="summary">Résumé</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="generateReport()" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Générer
                    </button>
                </div>
            </div>
        </div>

        <!-- Saved Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Rapports sauvegardés</h3>
                    </div>
                    <div class="p-4">
                        <template x-for="report in savedReports" :key="report.id">
                            <div class="p-3 border border-gray-200 rounded-lg mb-3 hover:bg-gray-50 transition-colors cursor-pointer" @click="loadReport(report)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900" x-text="report.name"></h4>
                                        <p class="text-sm text-gray-600" x-text="report.description"></p>
                                        <div class="flex items-center mt-2 text-xs text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <span x-text="report.lastGenerated"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button @click.stop="editReport(report)" class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click.stop="deleteReport(report.id)" class="text-red-600 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="savedReports.length === 0" class="text-center text-gray-500 py-8">
                            <i class="fas fa-file-alt text-4xl mb-4"></i>
                            <p>Aucun rapport sauvegardé</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Preview/Results -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="currentReport ? currentReport.name : 'Aperçu du rapport'"></h3>
                            <div class="flex space-x-2" x-show="currentReport">
                                <button @click="exportReport('pdf')" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button @click="exportReport('excel')" class="text-green-600 hover:text-green-700">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                                <button @click="saveReport()" class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div x-show="!currentReport" class="text-center text-gray-500 py-12">
                            <i class="fas fa-chart-line text-6xl mb-4"></i>
                            <h4 class="text-xl font-semibold mb-2">Créez votre premier rapport</h4>
                            <p class="mb-6">Utilisez les filtres ci-dessus ou choisissez un template pour commencer</p>
                            <button @click="showTemplates = true" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                Voir les templates
                            </button>
                        </div>

                        <!-- Report Results -->
                        <div x-show="currentReport" class="space-y-6">
                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <template x-for="metric in reportMetrics" :key="metric.label">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-4 text-white">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-blue-100 text-sm" x-text="metric.label"></p>
                                                <p class="text-2xl font-bold" x-text="metric.value"></p>
                                            </div>
                                            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                                                <i :class="metric.icon + ' text-xl'"></i>
                                            </div>
                                        </div>
                                        <div class="flex items-center mt-3 text-sm">
                                            <span :class="metric.trend > 0 ? 'text-green-200' : 'text-red-200'">
                                                <i :class="metric.trend > 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'" class="mr-1"></i>
                                                <span x-text="Math.abs(metric.trend) + '%'"></span>
                                            </span>
                                            <span class="text-blue-100 ml-2">vs période précédente</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Chart -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <canvas id="customChart" class="max-h-96"></canvas>
                            </div>

                            <!-- Data Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <template x-for="column in reportColumns" :key="column">
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" x-text="column"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="row in reportData" :key="row.id">
                                            <tr class="hover:bg-gray-50">
                                                <template x-for="(value, key) in row" :key="key">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="value"></td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates Modal -->
        <div x-show="showTemplates" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Templates de rapports</h3>
                        <button @click="showTemplates = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="template in reportTemplates" :key="template.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer" @click="useTemplate(template)">
                                <div class="flex items-center justify-between mb-3">
                                    <div :class="template.color + ' p-2 rounded-lg'">
                                        <i :class="template.icon + ' text-white'"></i>
                                    </div>
                                    <span :class="template.color.replace('bg-', 'bg-opacity-20 text-') + ' px-2 py-1 rounded-full text-xs font-medium'" x-text="template.category"></span>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-2" x-text="template.name"></h4>
                                <p class="text-sm text-gray-600 mb-3" x-text="template.description"></p>
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span x-text="template.fields + ' champs'"></span>
                                    <span x-text="template.charts + ' graphiques'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Report Modal -->
        <div x-show="showCreateReport" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Créer un nouveau rapport</h3>
                        <button @click="showCreateReport = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form @submit.prevent="createReport()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du rapport</label>
                                <input type="text" x-model="newReport.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea x-model="newReport.description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                                    <select x-model="newReport.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="operational">Opérationnel</option>
                                        <option value="financial">Financier</option>
                                        <option value="performance">Performance</option>
                                        <option value="analytics">Analytics</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fréquence</label>
                                    <select x-model="newReport.frequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="manual">Manuel</option>
                                        <option value="daily">Quotidien</option>
                                        <option value="weekly">Hebdomadaire</option>
                                        <option value="monthly">Mensuel</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Champs à inclure</label>
                                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3">
                                    <template x-for="field in availableFields" :key="field.id">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" :value="field.id" x-model="newReport.fields" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700" x-text="field.label"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" @click="showCreateReport = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Créer le rapport
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function customReportsData() {
    return {
        showTemplates: false,
        showCreateReport: false,
        currentReport: null,
        filters: {
            period: 'month',
            dataType: 'all',
            format: 'mixed'
        },
        newReport: {
            name: '',
            description: '',
            category: 'operational',
            frequency: 'manual',
            fields: []
        },
        savedReports: [
            {
                id: 1,
                name: 'Performance mensuelle',
                description: 'Analyse complète des performances du mois',
                lastGenerated: '24/09/2025 15:30',
                category: 'performance'
            },
            {
                id: 2,
                name: 'Revenus par zone',
                description: 'Répartition des revenus par zone géographique',
                lastGenerated: '23/09/2025 09:15',
                category: 'financial'
            },
            {
                id: 3,
                name: 'Top clients',
                description: 'Classement des clients les plus actifs',
                lastGenerated: '22/09/2025 18:45',
                category: 'analytics'
            }
        ],
        reportTemplates: [
            {
                id: 1,
                name: 'Rapport de performance',
                description: 'Analyse des KPIs et métriques de performance',
                category: 'Performance',
                fields: 12,
                charts: 4,
                icon: 'fas fa-tachometer-alt',
                color: 'bg-blue-600'
            },
            {
                id: 2,
                name: 'Analyse financière',
                description: 'Revenus, coûts et rentabilité détaillés',
                category: 'Financier',
                fields: 8,
                charts: 3,
                icon: 'fas fa-chart-line',
                color: 'bg-green-600'
            },
            {
                id: 3,
                name: 'Comportement clients',
                description: 'Patterns et tendances des clients',
                category: 'Analytics',
                fields: 15,
                charts: 5,
                icon: 'fas fa-users',
                color: 'bg-purple-600'
            },
            {
                id: 4,
                name: 'Opérations quotidiennes',
                description: 'Activité quotidienne et efficacité opérationnelle',
                category: 'Opérationnel',
                fields: 10,
                charts: 2,
                icon: 'fas fa-calendar-day',
                color: 'bg-orange-600'
            },
            {
                id: 5,
                name: 'Analyse géographique',
                description: 'Performance par zones et régions',
                category: 'Géographique',
                fields: 7,
                charts: 6,
                icon: 'fas fa-map',
                color: 'bg-indigo-600'
            },
            {
                id: 6,
                name: 'Satisfaction client',
                description: 'Enquêtes et feedback des clients',
                category: 'Qualité',
                fields: 9,
                charts: 3,
                icon: 'fas fa-smile',
                color: 'bg-yellow-600'
            }
        ],
        reportMetrics: [
            { label: 'Total des données', value: '2,847', trend: 12.5, icon: 'fas fa-database' },
            { label: 'Valeur moyenne', value: '€1,247', trend: -3.2, icon: 'fas fa-euro-sign' },
            { label: 'Performance', value: '94.2%', trend: 8.7, icon: 'fas fa-chart-line' }
        ],
        reportColumns: ['ID', 'Date', 'Valeur', 'Status', 'Actions'],
        reportData: [
            { id: '001', date: '24/09/2025', value: '€1,250', status: 'Terminé', actions: 'Voir' },
            { id: '002', date: '24/09/2025', value: '€950', status: 'En cours', actions: 'Modifier' },
            { id: '003', date: '23/09/2025', value: '€1,750', status: 'Terminé', actions: 'Voir' }
        ],
        availableFields: [
            { id: 'date', label: 'Date' },
            { id: 'revenue', label: 'Revenus' },
            { id: 'packages', label: 'Nombre de colis' },
            { id: 'clients', label: 'Clients' },
            { id: 'deliverers', label: 'Livreurs' },
            { id: 'zones', label: 'Zones' },
            { id: 'performance', label: 'Performance' },
            { id: 'satisfaction', label: 'Satisfaction' },
            { id: 'costs', label: 'Coûts' },
            { id: 'profit', label: 'Bénéfices' }
        ],

        generateReport() {
            // Simulate report generation
            this.currentReport = {
                name: `Rapport ${this.filters.dataType} - ${new Date().toLocaleDateString('fr-FR')}`,
                filters: { ...this.filters },
                generatedAt: new Date().toISOString()
            };

            // Initialize chart
            setTimeout(() => this.initChart(), 100);
        },

        useTemplate(template) {
            this.showTemplates = false;
            this.currentReport = {
                name: template.name,
                template: template,
                generatedAt: new Date().toISOString()
            };
            setTimeout(() => this.initChart(), 100);
        },

        createReport() {
            if (this.newReport.name) {
                this.savedReports.push({
                    id: this.savedReports.length + 1,
                    ...this.newReport,
                    lastGenerated: new Date().toLocaleDateString('fr-FR') + ' ' + new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
                });
                this.showCreateReport = false;
                this.newReport = { name: '', description: '', category: 'operational', frequency: 'manual', fields: [] };
                alert('Rapport créé avec succès!');
            }
        },

        loadReport(report) {
            this.currentReport = report;
            setTimeout(() => this.initChart(), 100);
        },

        editReport(report) {
            this.newReport = { ...report };
            this.showCreateReport = true;
        },

        deleteReport(reportId) {
            if (confirm('Supprimer ce rapport ?')) {
                this.savedReports = this.savedReports.filter(r => r.id !== reportId);
            }
        },

        saveReport() {
            if (this.currentReport && !this.savedReports.find(r => r.name === this.currentReport.name)) {
                this.savedReports.push({
                    id: this.savedReports.length + 1,
                    name: this.currentReport.name,
                    description: 'Rapport généré automatiquement',
                    lastGenerated: new Date().toLocaleDateString('fr-FR') + ' ' + new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
                    category: 'custom'
                });
                alert('Rapport sauvegardé!');
            }
        },

        exportReport(format) {
            alert(`Export ${format.toUpperCase()} en cours...`);
        },

        initChart() {
            const ctx = document.getElementById('customChart');
            if (ctx && typeof Chart !== 'undefined') {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'],
                        datasets: [{
                            label: 'Données du rapport',
                            data: [1200, 1900, 800, 1500, 2000, 1800],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>
@endsection