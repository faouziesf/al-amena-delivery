@extends('layouts.deliverer')

@section('title', 'Scan Unique - Al-Amena')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-bold text-gray-900">🔍 Scan Unique</h1>
                <a href="{{ route('deliverer.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                    ← Retour
                </a>
            </div>
            <p class="text-gray-600 text-sm">Scannez un code colis pour voir ses détails instantanément</p>
        </div>

        <!-- Zone de scan -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Code du colis
                </label>
                <div class="relative">
                    <input type="text"
                           id="packageCode"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           placeholder="PKG_XXXXXXX_YYYYMMDD"
                           autofocus>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 16h4.01"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <button id="scanBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                🔍 Scanner maintenant
            </button>
        </div>

        <!-- Status -->
        <div id="status" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-blue-800 font-medium">Prêt à scanner</span>
            </div>
        </div>

        <!-- Résultat -->
        <div id="result" class="hidden bg-white rounded-lg shadow-md p-6">
            <div id="resultContent"></div>
        </div>

        <!-- Historique rapide -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Derniers scans</h3>
            <div id="scanHistory" class="space-y-3">
                <p class="text-gray-500 text-sm">Aucun scan récent</p>
            </div>
        </div>
    </div>
</div>

<script>
console.log('🚀 Page Scan Unique chargée');

// Variables globales
let scanHistory = [];

// Fonction pour logger avec timestamp
function log(message) {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`[${timestamp}] ${message}`);
}

// Fonction pour mettre à jour le status
function setStatus(message, type = 'info') {
    const statusDiv = document.getElementById('status');
    const icons = {
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        loading: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
    };

    const colors = {
        info: 'bg-blue-50 border-blue-200 text-blue-800',
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        loading: 'bg-yellow-50 border-yellow-200 text-yellow-800'
    };

    statusDiv.className = `border rounded-lg p-4 mb-6 ${colors[type]}`;
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 ${type === 'loading' ? 'animate-spin' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type]}"></path>
            </svg>
            <span class="font-medium">${message}</span>
        </div>
    `;
    log(`STATUS: ${message}`);
}

// Fonction de scan
async function scanPackage(code) {
    log(`🔍 Début du scan pour: "${code}"`);
    setStatus('Scan en cours...', 'loading');

    try {
        // Utilisation de l'endpoint officiel (même que scan en lot)
        const url = '/deliverer/packages/scan';
        log(`📡 URL: ${window.location.origin}${url}`);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ code: code.trim() })
        });

        log(`📥 Response status: ${response.status}`);

        const data = await response.json();
        log(`📋 Response data: ${JSON.stringify(data, null, 2)}`);

        if (response.ok && data.success) {
            showResult(true, data);
            setStatus('✅ Colis trouvé avec succès!', 'success');
            addToHistory(code, data);
        } else {
            showResult(false, data);
            setStatus(`❌ ${data.message || 'Colis non trouvé ou erreur'}`, 'error');
        }

    } catch (error) {
        log(`❌ ERREUR: ${error.message}`);
        setStatus(`💥 Erreur de connexion: ${error.message}`, 'error');
        showResult(false, { message: error.message });
    }
}

// Afficher le résultat
function showResult(success, data) {
    const resultDiv = document.getElementById('result');
    const contentDiv = document.getElementById('resultContent');

    resultDiv.classList.remove('hidden');

    if (success && data.package) {
        contentDiv.innerHTML = `
            <div class="border-l-4 border-green-500 pl-4">
                <h3 class="text-lg font-semibold text-green-800 mb-3">📦 Colis trouvé</h3>

                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Code</p>
                        <p class="font-mono text-lg font-semibold">${data.package.code}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Statut</p>
                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium">
                            ${data.package.status}
                        </span>
                    </div>

                    ${data.package.cod_amount && parseFloat(data.package.cod_amount) > 0 ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Montant COD</p>
                        <p class="text-lg font-semibold text-green-600">${data.package.formatted_cod || data.package.cod_amount + ' DA'}</p>
                    </div>
                    ` : ''}

                    ${data.delivery_info && (data.delivery_info.name !== 'N/A' || data.delivery_info.address !== 'N/A') ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Destinataire</p>
                        <p class="font-medium">${data.delivery_info.name || 'Non spécifié'}</p>
                        ${data.delivery_info.address !== 'N/A' ? `<p class="text-sm text-gray-600 mt-1">${data.delivery_info.address}</p>` : ''}
                    </div>
                    ` : ''}
                </div>

                <button onclick="resetScan()" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                    ✅ Scanner un autre colis
                </button>
            </div>
        `;
    } else {
        contentDiv.innerHTML = `
            <div class="border-l-4 border-red-500 pl-4">
                <h3 class="text-lg font-semibold text-red-800 mb-3">❌ Erreur</h3>
                <p class="text-red-700 mb-4">${data.message || 'Colis non trouvé'}</p>
                <button onclick="resetScan()" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                    🔄 Réessayer
                </button>
            </div>
        `;
    }
}

// Ajouter à l'historique
function addToHistory(code, data) {
    const historyItem = {
        code: code,
        timestamp: new Date().toLocaleTimeString(),
        success: data.success,
        status: data.package?.status || 'Erreur'
    };

    scanHistory.unshift(historyItem);
    if (scanHistory.length > 5) scanHistory.pop();

    updateHistoryDisplay();
}

// Mettre à jour l'affichage de l'historique
function updateHistoryDisplay() {
    const historyDiv = document.getElementById('scanHistory');

    if (scanHistory.length === 0) {
        historyDiv.innerHTML = '<p class="text-gray-500 text-sm">Aucun scan récent</p>';
        return;
    }

    historyDiv.innerHTML = scanHistory.map(item => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded cursor-pointer hover:bg-gray-100"
             onclick="document.getElementById('packageCode').value='${item.code}'; document.getElementById('packageCode').focus();">
            <div>
                <p class="font-mono text-sm font-medium">${item.code}</p>
                <p class="text-xs text-gray-500">${item.timestamp}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded ${item.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                ${item.status}
            </span>
        </div>
    `).join('');
}

// Reset pour nouveau scan
function resetScan() {
    document.getElementById('result').classList.add('hidden');
    document.getElementById('packageCode').value = '';
    document.getElementById('packageCode').focus();
    setStatus('Prêt à scanner', 'info');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    log('📄 DOM chargé et prêt');

    const input = document.getElementById('packageCode');
    const scanBtn = document.getElementById('scanBtn');

    // Scan au clic du bouton
    scanBtn.addEventListener('click', function() {
        const code = input.value.trim();
        if (code) {
            scanPackage(code);
        } else {
            setStatus('⚠️ Veuillez saisir un code', 'error');
            input.focus();
        }
    });

    // Scan avec Entrée
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const code = this.value.trim();
            if (code) {
                scanPackage(code);
            } else {
                setStatus('⚠️ Veuillez saisir un code', 'error');
            }
        }
    });

    // Focus automatique
    input.focus();

    setStatus('Prêt à scanner', 'info');
    log('🎯 Page initialisée avec succès');
});
</script>
@endsection