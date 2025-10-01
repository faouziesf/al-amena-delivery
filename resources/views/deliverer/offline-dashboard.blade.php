@extends('layouts.deliverer')

@section('title', 'Mode Offline - Dashboard Livreur')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-4">
    <!-- Offline Status Header -->
    <div class="mb-6">
        <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div id="network-status" class="w-4 h-4 bg-red-500 rounded-full"></div>
                    <h1 class="text-xl font-bold text-gray-900">Mode Offline</h1>
                </div>

                <div id="sync-indicator" class="hidden">
                    <!-- Contenu dynamique via JavaScript -->
                </div>
            </div>

            <p class="text-sm text-gray-600 mt-2">
                Vos actions sont sauvegardées localement et seront synchronisées automatiquement lors de la reconnexion.
            </p>
        </div>
    </div>

    <!-- Quick Actions - Offline Optimized -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Scanner QR -->
        <button onclick="openQRScanner()"
                class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <span class="font-semibold text-lg">Scanner</span>
                <span class="text-sm opacity-90">Fonctionnalité offline</span>
            </div>
        </button>

        <!-- Ma Tournée -->
        <button onclick="viewTournee()"
                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <span class="font-semibold text-lg">Ma Tournée</span>
                <span class="text-sm opacity-90">Données mises en cache</span>
            </div>
        </button>
    </div>

    <!-- Offline Package List -->
    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Colis en Attente</h2>
            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                Mode Cache
            </span>
        </div>

        <div id="offline-packages-list" class="space-y-3">
            <!-- Contenu dynamique via JavaScript -->
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>Chargement des données en cache...</p>
            </div>
        </div>
    </div>

    <!-- Offline Actions Log -->
    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Actions Récentes (Mode Offline)</h2>

        <div id="offline-actions-log" class="space-y-2">
            <!-- Actions sauvegardées localement -->
        </div>

        <!-- Clear Data Button -->
        <div class="mt-6 pt-4 border-t">
            <button onclick="clearOfflineData()"
                    class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-4 rounded-xl transition-colors duration-200">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Vider les Données Offline</span>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div id="qr-scanner-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Scanner QR (Mode Offline)</h3>
            <button onclick="closeQRScanner()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <p class="text-gray-600">Scanner disponible même hors ligne</p>
            </div>

            <input type="text"
                   id="manual-package-code"
                   placeholder="Ou saisir le code manuellement"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

            <button onclick="processPackageCode()"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                Traiter le Colis
            </button>
        </div>
    </div>
</div>

<script src="/js/deliverer-offline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser l'interface offline
    initOfflineInterface();

    // Charger les données en cache
    loadCachedPackages();

    // Mettre à jour le log des actions
    updateActionsLog();
});

function initOfflineInterface() {
    // Vérifier le statut de connexion
    updateNetworkStatus();

    // Configurez les listeners pour les changements de réseau
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
}

function updateNetworkStatus() {
    const networkStatus = document.getElementById('network-status');
    const isOnline = navigator.onLine;

    if (networkStatus) {
        networkStatus.className = isOnline ?
            'w-4 h-4 bg-green-500 rounded-full animate-pulse' :
            'w-4 h-4 bg-red-500 rounded-full';
    }
}

function openQRScanner() {
    document.getElementById('qr-scanner-modal').classList.remove('hidden');
}

function closeQRScanner() {
    document.getElementById('qr-scanner-modal').classList.add('hidden');
}

function processPackageCode() {
    const code = document.getElementById('manual-package-code').value.trim();
    if (!code) {
        alert('Veuillez saisir un code de colis');
        return;
    }

    // Traitement offline du colis
    processPackageOffline(code);
    closeQRScanner();
}

async function processPackageOffline(packageCode) {
    try {
        // Sauvegarder l'action offline
        const deliveryData = {
            package_code: packageCode,
            action: 'scanned',
            timestamp: new Date().toISOString(),
            location: await getCurrentLocation(),
            offline: true
        };

        await window.saveDeliveryOffline(packageCode, deliveryData);

        // Mettre à jour l'interface
        addToActionsLog(`Colis ${packageCode} scanné (offline)`, 'scan');

        showToast('Colis traité en mode offline', 'success');

    } catch (error) {
        console.error('Erreur traitement offline:', error);
        showToast('Erreur lors du traitement', 'error');
    }
}

async function getCurrentLocation() {
    return new Promise((resolve) => {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    });
                },
                () => {
                    resolve(null);
                },
                { timeout: 5000, enableHighAccuracy: true }
            );
        } else {
            resolve(null);
        }
    });
}

function loadCachedPackages() {
    const packagesList = document.getElementById('offline-packages-list');

    // Simuler le chargement des données en cache
    // En production, ceci utiliserait l'API IndexedDB
    setTimeout(() => {
        packagesList.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <p>Aucun colis en cache pour le moment</p>
                <p class="text-sm mt-1">Les données seront disponibles après la première synchronisation</p>
            </div>
        `;
    }, 1000);
}

function addToActionsLog(action, type) {
    const log = document.getElementById('offline-actions-log');
    const timestamp = new Date().toLocaleTimeString();

    const actionElement = document.createElement('div');
    actionElement.className = 'flex items-center space-x-3 p-3 bg-gray-50 rounded-lg';

    const iconClass = type === 'scan' ? 'text-blue-500' : 'text-green-500';

    actionElement.innerHTML = `
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">${action}</p>
            <p class="text-xs text-gray-500">${timestamp}</p>
        </div>
        <div class="flex-shrink-0">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                En attente de sync
            </span>
        </div>
    `;

    log.prepend(actionElement);
}

function updateActionsLog() {
    // Initialiser avec un message vide
    const log = document.getElementById('offline-actions-log');
    log.innerHTML = `
        <div class="text-center py-4 text-gray-500">
            <p>Aucune action récente</p>
        </div>
    `;
}

function viewTournee() {
    showToast('Fonctionnalité en développement', 'info');
}

async function clearOfflineData() {
    if (confirm('Êtes-vous sûr de vouloir supprimer toutes les données offline ?')) {
        try {
            await window.delivererOffline.clearOfflineData();

            // Réinitialiser l'interface
            loadCachedPackages();
            updateActionsLog();

        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            showToast('Erreur lors de la suppression', 'error');
        }
    }
}

function showToast(message, type) {
    if (window.delivererOffline) {
        window.delivererOffline.showToast(message, type);
    }
}
</script>
@endsection