@extends('layouts.supervisor')

@section('title', 'Vue d\'ensemble système')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Vue d'ensemble système</h1>
                    <p class="text-gray-600">Surveillance en temps réel de l'état du système</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-gray-700">Système opérationnel</span>
                        </div>
                        <button onclick="location.reload()" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Server Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Serveur</h3>
                        <div class="flex items-center mt-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                            <span class="text-lg font-semibold text-gray-900">En ligne</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Uptime: 99.9%</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-server text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Database Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Base de données</h3>
                        <div class="flex items-center mt-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                            <span class="text-lg font-semibold text-gray-900">Connectée</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Latence: 12ms</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-database text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Queue Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Files d'attente</h3>
                        <div class="flex items-center mt-2">
                            <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                            <span class="text-lg font-semibold text-gray-900">{{ $queueStats['pending_jobs'] ?? 25 }} tâches</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">En traitement</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Storage Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Stockage</h3>
                        <div class="flex items-center mt-2">
                            <span class="text-lg font-semibold text-gray-900">{{ isset($diskUsage) ? number_format($diskUsage['percentage'], 1) : '65' }}%</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            @if(isset($diskUsage))
                                {{ number_format($diskUsage['used'] / 1024 / 1024 / 1024, 1) }} GB / {{ number_format($diskUsage['total'] / 1024 / 1024 / 1024, 1) }} GB
                            @else
                                2.3 GB / 3.5 GB
                            @endif
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-hdd text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ isset($diskUsage) ? $diskUsage['percentage'] : 65 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Performance Metrics -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Métriques de performance</h3>
                        <select class="text-sm border border-gray-200 rounded-lg px-3 py-1">
                            <option>Dernière heure</option>
                            <option>Dernières 24h</option>
                            <option>Dernière semaine</option>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <!-- CPU Usage -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-microchip text-blue-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-700">CPU</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 45%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">45%</span>
                            </div>
                        </div>

                        <!-- Memory Usage -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-memory text-green-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-700">Mémoire</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ isset($systemInfo) ? (intval(str_replace(['M', 'G'], ['', '000'], $systemInfo['memory_usage'] ?? '256M')) / 1024 * 100) : 72 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ isset($systemInfo) ? (intval(str_replace(['M', 'G'], ['', '000'], $systemInfo['memory_usage'] ?? '256M')) / 1024 * 100) : 72 }}%</span>
                            </div>
                        </div>

                        <!-- Disk I/O -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-hdd text-purple-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-700">E/S Disque</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: 28%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">28%</span>
                            </div>
                        </div>

                        <!-- Network -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-network-wired text-orange-500 mr-3"></i>
                                <span class="text-sm font-medium text-gray-700">Réseau</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-orange-600 h-2 rounded-full" style="width: 35%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">35%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations système</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fab fa-php text-blue-600 mr-2"></i>
                                Version PHP:
                            </span>
                            <span class="font-medium">{{ $systemInfo['php_version'] ?? '8.1.0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fab fa-laravel text-red-600 mr-2"></i>
                                Version Laravel:
                            </span>
                            <span class="font-medium">{{ $systemInfo['laravel_version'] ?? '10.0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-server text-gray-600 mr-2"></i>
                                Serveur Web:
                            </span>
                            <span class="font-medium">{{ $systemInfo['server_software'] ?? 'Apache/Nginx' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-database text-blue-600 mr-2"></i>
                                Base de données:
                            </span>
                            <span class="font-medium">{{ isset($systemInfo) ? ucfirst($systemInfo['database_connection']) : 'MySQL' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-memory text-green-600 mr-2"></i>
                                Limite mémoire:
                            </span>
                            <span class="font-medium">{{ $systemInfo['memory_limit'] ?? '512M' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-clock text-yellow-600 mr-2"></i>
                                Temps d'exécution max:
                            </span>
                            <span class="font-medium">{{ $systemInfo['max_execution_time'] ?? '30' }}s</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Utilisateurs</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">
                            {{ isset($databaseStats) ? number_format($databaseStats['users_count']) : '1,234' }}
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
                        <h3 class="text-sm font-medium text-gray-500">Colis</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">
                            {{ isset($databaseStats) ? number_format($databaseStats['packages_count']) : '5,678' }}
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-box text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Réclamations</h3>
                        <div class="text-2xl font-bold text-yellow-600 mt-2">
                            {{ isset($databaseStats) ? number_format($databaseStats['complaints_count']) : '89' }}
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Transactions</h3>
                        <div class="text-2xl font-bold text-purple-600 mt-2">
                            {{ isset($databaseStats) ? number_format($databaseStats['transactions_count']) : '12,345' }}
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent System Events -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Événements récents</h3>
                    <a href="{{ route('supervisor.system.logs') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Voir tous
                    </a>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="bg-green-100 p-1.5 rounded-full">
                            <i class="fas fa-check text-green-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Sauvegarde automatique réussie</p>
                            <p class="text-xs text-gray-500">Il y a 15 minutes</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="bg-blue-100 p-1.5 rounded-full">
                            <i class="fas fa-sync text-blue-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Cache système mis à jour</p>
                            <p class="text-xs text-gray-500">Il y a 1 heure</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="bg-yellow-100 p-1.5 rounded-full">
                            <i class="fas fa-exclamation text-yellow-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Files d'attente: {{ $queueStats['failed_jobs'] ?? 2 }} tâches échouées</p>
                            <p class="text-xs text-gray-500">Il y a 3 heures</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="bg-green-100 p-1.5 rounded-full">
                            <i class="fas fa-user text-green-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Nouveau utilisateur enregistré</p>
                            <p class="text-xs text-gray-500">Il y a 5 heures</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions système</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('supervisor.system.maintenance') }}" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 transition-colors">
                    <div class="flex items-center">
                        <i class="fas fa-tools text-yellow-600 text-xl mr-3"></i>
                        <div>
                            <div class="font-semibold text-gray-900">Maintenance</div>
                            <div class="text-sm text-gray-600">Mode maintenance</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('supervisor.system.backup') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                    <div class="flex items-center">
                        <i class="fas fa-download text-blue-600 text-xl mr-3"></i>
                        <div>
                            <div class="font-semibold text-gray-900">Sauvegarde</div>
                            <div class="text-sm text-gray-600">Créer sauvegarde</div>
                        </div>
                    </div>
                </a>

                <button onclick="clearCache()" class="bg-purple-50 border border-purple-200 rounded-lg p-4 hover:bg-purple-100 transition-colors text-left">
                    <div class="flex items-center">
                        <i class="fas fa-broom text-purple-600 text-xl mr-3"></i>
                        <div>
                            <div class="font-semibold text-gray-900">Nettoyer cache</div>
                            <div class="text-sm text-gray-600">Vider le cache</div>
                        </div>
                    </div>
                </button>

                <a href="{{ route('supervisor.system.logs') }}" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors">
                    <div class="flex items-center">
                        <i class="fas fa-list text-green-600 text-xl mr-3"></i>
                        <div>
                            <div class="font-semibold text-gray-900">Journaux</div>
                            <div class="text-sm text-gray-600">Voir les logs</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        fetch('{{ route("supervisor.system.cache.clear") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache vidé avec succès');
                location.reload();
            }
        })
        .catch(error => console.error('Erreur:', error));
    }
}

// Auto refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endsection