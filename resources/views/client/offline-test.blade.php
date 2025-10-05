@extends('layouts.client')

@section('title', 'Test Mode Offline')

@section('content')
<div class="min-h-screen pt-6 pb-20 px-4 sm:px-6 lg:px-8">

    <!-- En-tête avec indicateurs de statut -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-purple-50/80 to-pink-50/80 backdrop-blur-lg rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4 flex-col sm:flex-row">
                <h1 class="text-2xl font-bold gradient-text">Test Mode Offline</h1>
                <div class="flex items-center space-x-3 flex-col sm:flex-row">
                    <div class="flex items-center space-x-2 flex-col sm:flex-row">
                        <div data-network-status class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Statut réseau</span>
                    </div>
                </div>
            </div>
            <p class="text-gray-600">
                Testez les fonctionnalités offline de l'application. Vos données seront sauvegardées localement et synchronisées lors de la reconnexion.
            </p>
        </div>
    </div>

    <!-- Actions de test -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-5 lg:grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-2 sm:gap-3 lg:gap-4 lg:gap-6 mb-8">

        <!-- Test Colis Offline -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-all duration-300 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center mb-4 flex-col sm:flex-row">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mr-3 flex-col sm:flex-row">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Nouveau Colis</h3>
            </div>
            <p class="text-gray-600 mb-4">Créer un colis en mode offline</p>
            <button onclick="testCreatePackage()"
                    class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white py-2 px-4 rounded-2xl hover:from-purple-600 hover:to-purple-700 transition-all">
                Tester Création
            </button>
        </div>

        <!-- Test Manifeste Offline -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-all duration-300 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center mb-4 flex-col sm:flex-row">
                <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mr-3 flex-col sm:flex-row">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Manifeste</h3>
            </div>
            <p class="text-gray-600 mb-4">Créer un manifeste en mode offline</p>
            <button onclick="testCreateManifest()"
                    class="w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-2 px-4 rounded-2xl hover:from-indigo-600 hover:to-indigo-700 transition-all">
                Tester Manifeste
            </button>
        </div>

        <!-- Test Recharge Offline -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6 hover:shadow-lg transition-all duration-300 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center mb-4 flex-col sm:flex-row">
                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center mr-3 flex-col sm:flex-row">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Recharge</h3>
            </div>
            <p class="text-gray-600 mb-4">Demande de recharge offline</p>
            <button onclick="testTopupRequest()"
                    class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-2 px-4 rounded-2xl hover:from-emerald-600 hover:to-emerald-700 transition-all">
                Tester Recharge
            </button>
        </div>
    </div>

    <!-- Brouillons sauvegardés -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6 mb-8 transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between mb-6 flex-col sm:flex-row">
            <h2 class="text-xl font-bold text-gray-900">Brouillons Offline</h2>
            <div class="flex space-x-2 flex-col sm:flex-row">
                <button onclick="loadDrafts()"
                        class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    Actualiser
                </button>
                <button onclick="clearAllDrafts()"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Vider tout
                </button>
            </div>
        </div>

        <div id="drafts-container">
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p>Aucun brouillon pour le moment</p>
                <p class="text-sm mt-1">Créez des éléments en mode offline pour les voir ici</p>
            </div>
        </div>
    </div>

    <!-- Contrôles de test -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-purple-200/30 p-4 sm:p-5 lg:p-6 transition-all duration-300 hover:-translate-y-1">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Contrôles de Test</h2>

        <div class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 lg:gap-4">
            <button onclick="simulateOffline()"
                    class="bg-amber-500 hover:bg-amber-600 text-white py-3 px-4 rounded-2xl transition-colors">
                🔌 Simuler Mode Offline
            </button>

            <button onclick="simulateOnline()"
                    class="bg-green-500 hover:bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white py-3 px-4 rounded-2xl transition-colors">
                📶 Simuler Retour Online
            </button>

            <button onclick="triggerSync()"
                    data-requires-online="true"
                    class="bg-blue-500 hover:bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white py-3 px-4 rounded-2xl transition-colors">
                🔄 Forcer Synchronisation
            </button>

            <button onclick="showOfflineStats()"
                    class="bg-purple-500 hover:bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 text-white py-3 px-4 rounded-2xl transition-colors">
                📊 Statistiques Offline
            </button>
        </div>
    </div>
</div>

<script>
// ===== FONCTIONS DE TEST =====

async function testCreatePackage() {
    const packageData = {
        recipient_name: 'Test Destinataire',
        recipient_phone: '1234567890',
        recipient_address: 'Adresse de test',
        delivery_city: 'Tunis',
        package_description: 'Colis de test offline',
        declared_value: 100,
        cod_amount: 0,
        created_at: new Date().toISOString()
    };

    try {
        await window.savePackageOffline(packageData);
        await loadDrafts();
    } catch (error) {
        console.error('Erreur test package:', error);
    }
}

async function testCreateManifest() {
    const manifestData = {
        name: 'Manifeste Test Offline',
        description: 'Manifeste créé en mode offline',
        packages: [],
        created_at: new Date().toISOString()
    };

    try {
        await window.saveManifestOffline(manifestData);
        await loadDrafts();
    } catch (error) {
        console.error('Erreur test manifeste:', error);
    }
}

async function testTopupRequest() {
    const topupData = {
        amount: 50,
        method: 'bank_transfer',
        bank_account: 'Test Account',
        reference: 'TEST_' + Date.now(),
        created_at: new Date().toISOString()
    };

    try {
        await window.saveTopupOffline(topupData);
        await loadDrafts();
    } catch (error) {
        console.error('Erreur test topup:', error);
    }
}

async function loadDrafts() {
    if (window.clientOffline) {
        await window.clientOffline.loadDrafts();
    }
}

async function clearAllDrafts() {
    if (confirm('Supprimer tous les brouillons ?')) {
        if (window.clientOffline) {
            await window.clientOffline.clearDrafts();
        }
    }
}

// ===== CONTRÔLES DE TEST =====

function simulateOffline() {
    // Simulation du mode offline
    Object.defineProperty(navigator, 'onLine', {
        writable: true,
        value: false
    });

    window.dispatchEvent(new Event('offline'));
    showToast('Mode offline simulé', 'warning');
}

function simulateOnline() {
    // Simulation du retour online
    Object.defineProperty(navigator, 'onLine', {
        writable: true,
        value: true
    });

    window.dispatchEvent(new Event('online'));
    showToast('Mode online simulé', 'success');
}

async function triggerSync() {
    if (window.clientOffline) {
        await window.clientOffline.syncPendingData();
        showToast('Synchronisation déclenchée', 'info');
    }
}

async function showOfflineStats() {
    if (window.clientOffline) {
        const stats = await window.clientOffline.getOfflineStats();

        alert(`Statistiques Offline:
- En ligne: ${stats.isOnline ? 'Oui' : 'Non'}
- Brouillons en attente: ${stats.pendingDrafts}
- Dernière sync: ${stats.lastSync ? stats.lastSync.toLocaleString() : 'Jamais'}
- Peut fonctionner offline: ${stats.canWorkOffline ? 'Oui' : 'Non'}`);
    }
}

function showToast(message, type) {
    if (window.clientOffline) {
        window.clientOffline.showToast(message, type);
    }
}

// Initialisation de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page de test offline chargée');

    // Charger les brouillons existants
    setTimeout(() => {
        loadDrafts();
    }, 1000);
});
</script>
@endsection