@extends('layouts.supervisor')

@section('title', 'Journaux système')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Journaux système</h1>
                    <p class="text-gray-600">Consultation et analyse des logs du système</p>
                </div>
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    <a href="{{ route('supervisor.system.overview') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour
                    </a>
                    <button onclick="refreshLogs()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Actualiser
                    </button>
                    <button onclick="clearLogs()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Vider les logs
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Niveau de log</label>
                    <select id="logLevel" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="filterLogs()">
                        <option value="">Tous les niveaux</option>
                        <option value="emergency">Emergency</option>
                        <option value="alert">Alert</option>
                        <option value="critical">Critical</option>
                        <option value="error" selected>Error</option>
                        <option value="warning">Warning</option>
                        <option value="notice">Notice</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Canal</label>
                    <select id="logChannel" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="filterLogs()">
                        <option value="">Tous les canaux</option>
                        <option value="single">Single</option>
                        <option value="daily">Daily</option>
                        <option value="stack">Stack</option>
                        <option value="stderr">Stderr</option>
                        <option value="database">Database</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <input type="datetime-local" id="dateFrom"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        value="{{ now()->subDay()->format('Y-m-d\TH:i') }}"
                        onchange="filterLogs()">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input type="datetime-local" id="dateTo"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        value="{{ now()->format('Y-m-d\TH:i') }}"
                        onchange="filterLogs()">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Recherche dans les messages</label>
                <div class="flex">
                    <input type="text" id="searchQuery" placeholder="Rechercher dans les logs..."
                        class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button onclick="filterLogs()" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Emergency</div>
                        <div class="text-xl font-bold text-red-600">3</div>
                    </div>
                    <div class="bg-red-100 p-2 rounded-lg">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Error</div>
                        <div class="text-xl font-bold text-red-500">47</div>
                    </div>
                    <div class="bg-red-100 p-2 rounded-lg">
                        <i class="fas fa-times-circle text-red-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Warning</div>
                        <div class="text-xl font-bold text-yellow-500">156</div>
                    </div>
                    <div class="bg-yellow-100 p-2 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Info</div>
                        <div class="text-xl font-bold text-blue-500">892</div>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Debug</div>
                        <div class="text-xl font-bold text-gray-500">2,341</div>
                    </div>
                    <div class="bg-gray-100 p-2 rounded-lg">
                        <i class="fas fa-bug text-gray-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Entries -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Entrées du journal</h3>
                    <div class="flex items-center space-x-3">
                        <label class="flex items-center">
                            <input type="checkbox" id="autoRefresh" class="mr-2 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Actualisation automatique</span>
                        </label>
                        <div class="text-sm text-gray-500">
                            <span id="logCount">1,234</span> entrées
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto" id="logContainer">
                <!-- Log Entry Example -->
                <div class="border-b border-gray-100 p-4 hover:bg-gray-50 log-entry" data-level="error">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ERROR
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-sm font-medium text-gray-900">
                                    Erreur de connexion à la base de données
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ now()->subMinutes(5)->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                SQLSTATE[HY000] [2002] Connection refused (SQL: select * from users where id = 1)
                            </div>
                            <div class="text-xs text-gray-500">
                                <strong>Context:</strong> local.ERROR: Exception in UserController@show:42
                            </div>
                            <details class="mt-2">
                                <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-700">
                                    Afficher la stack trace
                                </summary>
                                <pre class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-x-auto">
#0 /var/www/app/Http/Controllers/UserController.php(42): App\Models\User::find(1)
#1 /var/www/vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54): App\Http\Controllers\UserController->show(1)
#2 /var/www/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(45): Illuminate\Routing\Controller->callAction('show', Array)
                                </pre>
                            </details>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-100 p-4 hover:bg-gray-50 log-entry" data-level="warning">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                WARNING
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-sm font-medium text-gray-900">
                                    Tentative de connexion avec un mot de passe incorrect
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ now()->subMinutes(15)->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                User authentication failed for email: test@example.com from IP: 192.168.1.100
                            </div>
                            <div class="text-xs text-gray-500">
                                <strong>Context:</strong> local.WARNING: Failed login attempt in AuthController@login:28
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-100 p-4 hover:bg-gray-50 log-entry" data-level="info">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                INFO
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-sm font-medium text-gray-900">
                                    Nouveau package créé avec succès
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ now()->subMinutes(30)->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                Package #PKG123456 created by user ID: 42, assigned to deliverer ID: 15
                            </div>
                            <div class="text-xs text-gray-500">
                                <strong>Context:</strong> local.INFO: Package creation successful in PackageController@store:85
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-100 p-4 hover:bg-gray-50 log-entry" data-level="debug">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                DEBUG
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-sm font-medium text-gray-900">
                                    Cache hit pour la clé user.profile.42
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ now()->subHour()->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                Cache retrieved successfully, TTL: 3600 seconds
                            </div>
                            <div class="text-xs text-gray-500">
                                <strong>Context:</strong> local.DEBUG: Cache hit in CacheService@get:23
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Affichage de 1 à 50 sur 1,234 entrées
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            Précédent
                        </button>
                        <span class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">1</span>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">2</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">3</button>
                        <span class="px-2 text-gray-500">...</span>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                            Suivant
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;

function filterLogs() {
    const level = document.getElementById('logLevel').value;
    const channel = document.getElementById('logChannel').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const query = document.getElementById('searchQuery').value;

    // Here you would normally make an AJAX call to filter logs
    console.log('Filtering logs:', { level, channel, dateFrom, dateTo, query });

    // For demo purposes, hide/show log entries based on level
    const logEntries = document.querySelectorAll('.log-entry');
    logEntries.forEach(entry => {
        if (!level || entry.dataset.level === level) {
            entry.style.display = 'block';
        } else {
            entry.style.display = 'none';
        }
    });
}

function refreshLogs() {
    location.reload();
}

function clearLogs() {
    if (confirm('Êtes-vous sûr de vouloir vider tous les logs ? Cette action est irréversible.')) {
        // Make AJAX call to clear logs
        fetch('/supervisor/system/logs/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Logs vidés avec succès');
                location.reload();
            } else {
                alert('Erreur lors du vidage des logs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de connexion');
        });
    }
}

// Auto-refresh functionality
document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(refreshLogs, 10000); // Refresh every 10 seconds
    } else {
        clearInterval(autoRefreshInterval);
    }
});

// Auto-scroll to bottom when new logs arrive
function scrollToBottom() {
    const container = document.getElementById('logContainer');
    container.scrollTop = container.scrollHeight;
}

// Initialize filters
document.addEventListener('DOMContentLoaded', function() {
    filterLogs();
});
</script>
@endsection