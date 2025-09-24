@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Vue d'Ensemble du Système</h1>
        <p class="text-gray-600">Monitoring et informations système en temps réel</p>
    </div>

    <!-- État global du système -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Système Opérationnel</h2>
                    <p class="text-green-100">Tous les services fonctionnent normalement</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">99.9%</div>
                <div class="text-green-100 text-sm">Uptime</div>
            </div>
        </div>
    </div>

    <!-- Informations système -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Informations Système</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Version PHP:</span>
                    <span class="font-medium">{{ $systemInfo['php_version'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Version Laravel:</span>
                    <span class="font-medium">{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Serveur Web:</span>
                    <span class="font-medium">{{ $systemInfo['server_software'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Base de données:</span>
                    <span class="font-medium">{{ ucfirst($systemInfo['database_connection']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Limite mémoire:</span>
                    <span class="font-medium">{{ $systemInfo['memory_limit'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Temps d'exécution max:</span>
                    <span class="font-medium">{{ $systemInfo['max_execution_time'] }}s</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Taille upload max:</span>
                    <span class="font-medium">{{ $systemInfo['upload_max_filesize'] }}</span>
                </div>
            </div>
        </div>

        <!-- Utilisation du disque -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Utilisation du Disque</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Espace utilisé:</span>
                        <span class="font-medium">{{ number_format($diskUsage['percentage'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r
                            @if($diskUsage['percentage'] < 70) from-green-500 to-green-600
                            @elseif($diskUsage['percentage'] < 90) from-yellow-500 to-yellow-600
                            @else from-red-500 to-red-600
                            @endif
                            h-3 rounded-full transition-all duration-300"
                            style="width: {{ $diskUsage['percentage'] }}%"></div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-medium">{{ number_format($diskUsage['total'] / 1024 / 1024 / 1024, 2) }} GB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Utilisé:</span>
                        <span class="font-medium">{{ number_format($diskUsage['used'] / 1024 / 1024 / 1024, 2) }} GB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Libre:</span>
                        <span class="font-medium">{{ number_format($diskUsage['free'] / 1024 / 1024 / 1024, 2) }} GB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques de la base de données -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-blue-600">
            <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($databaseStats['users_count']) }}</div>
            <div class="text-sm text-gray-600">Utilisateurs</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-green-600">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($databaseStats['packages_count']) }}</div>
            <div class="text-sm text-gray-600">Colis</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-yellow-600">
            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ number_format($databaseStats['complaints_count']) }}</div>
            <div class="text-sm text-gray-600">Réclamations</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-purple-600">
            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($databaseStats['transactions_count']) }}</div>
            <div class="text-sm text-gray-600">Transactions</div>
        </div>
    </div>

    <!-- État des services -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">État des Files d'Attente</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-gray-600">Jobs en attente:</span>
                    </div>
                    <span class="font-medium">{{ $queueStats['pending_jobs'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <span class="text-gray-600">Jobs échoués:</span>
                    </div>
                    <span class="font-medium">{{ $queueStats['failed_jobs'] }}</span>
                </div>
                @if($queueStats['failed_jobs'] > 0)
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">⚠ Il y a des jobs qui ont échoué. Vérifiez les logs.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Utilisation mémoire -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Utilisation Mémoire</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Mémoire utilisée:</span>
                    <span class="font-medium">{{ number_format($systemInfo['memory_usage'] / 1024 / 1024, 2) }} MB</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Limite configurée:</span>
                    <span class="font-medium">{{ $systemInfo['memory_limit'] }}</span>
                </div>

                <!-- Graphique de mémoire simulé -->
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full" style="width: 35%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">35% de la mémoire utilisée</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Actions Système</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('supervisor.system.logs') }}"
               class="flex items-center justify-center p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Voir Logs
            </a>

            <a href="{{ route('supervisor.system.maintenance') }}"
               class="flex items-center justify-center p-4 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Maintenance
            </a>

            <a href="{{ route('supervisor.system.backup') }}"
               class="flex items-center justify-center p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Backup
            </a>

            <button onclick="clearCache()"
                    class="flex items-center justify-center p-4 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Vider Cache
            </button>
        </div>
    </div>
</div>

<script>
// Auto-refresh des données
let refreshInterval;

function startAutoRefresh() {
    refreshInterval = setInterval(function() {
        window.location.reload();
    }, 60000); // Refresh toutes les minutes
}

function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache? Cela peut temporairement ralentir le système.')) {
        fetch('{{ route("supervisor.system.clear-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache vidé avec succès!');
            } else {
                alert('Erreur lors du vidage du cache: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur de connexion');
        });
    }
}

// Démarrer l'auto-refresh
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});

// Arrêter l'auto-refresh quand on quitte la page
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
@endsection