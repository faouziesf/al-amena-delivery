@extends('layouts.deliverer')

@section('title', 'Scan en Lot - Al-Amena')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-bold text-gray-900">📦 Scan en Lot</h1>
                <a href="{{ route('deliverer.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                    ← Retour
                </a>
            </div>
            <p class="text-gray-600 text-sm">Scannez plusieurs colis et effectuez des actions groupées</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Zone de scan -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Scanner</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Code du colis
                        </label>
                        <input type="text"
                               id="packageCode"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="PKG_XXXXXXX_YYYYMMDD"
                               autofocus>
                    </div>

                    <button id="addToListBtn"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 mb-4">
                        ➕ Ajouter à la liste
                    </button>

                    <!-- Status -->
                    <div id="status" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-blue-800 text-sm font-medium">Prêt à scanner</span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions Rapides</h3>

                    <div class="space-y-3">
                        <button onclick="clearAll()" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            🗑️ Vider la liste
                        </button>

                        <button onclick="exportList()" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            📄 Exporter la liste
                        </button>
                    </div>
                </div>
            </div>

            <!-- Liste des colis scannés -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">📋 Colis Scannés</h3>
                        <span id="packageCount" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">0 colis</span>
                    </div>

                    <!-- Filtres -->
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button onclick="filterPackages('all')" class="filter-btn active bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-sm">
                            Tous
                        </button>
                        <button onclick="filterPackages('valid')" class="filter-btn bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded text-sm">
                            Valides
                        </button>
                        <button onclick="filterPackages('invalid')" class="filter-btn bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded text-sm">
                            Erreurs
                        </button>
                    </div>

                    <!-- Liste -->
                    <div id="packageList" class="space-y-3 max-h-96 overflow-y-auto">
                        <div class="text-center text-gray-500 py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m-4 4v4m0-4h4m0 0v4"></path>
                            </svg>
                            <p>Aucun colis scanné</p>
                            <p class="text-sm">Scannez des codes pour commencer</p>
                        </div>
                    </div>

                    <!-- Actions groupées -->
                    <div id="batchActions" class="hidden mt-6 pt-4 border-t border-gray-200">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Actions Groupées</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button onclick="batchAction('accept')" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-3 rounded text-sm transition-colors">
                                ✅ Accepter
                            </button>
                            <button onclick="batchAction('pickup')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded text-sm transition-colors">
                                📦 Récupérer
                            </button>
                            <button onclick="batchAction('deliver')" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-3 rounded text-sm transition-colors">
                                🚚 Livrer
                            </button>
                            <button onclick="batchAction('return')" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-3 rounded text-sm transition-colors">
                                ↩️ Retourner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
console.log('🚀 Page Scan en Lot chargée');

// Variables globales
let scannedPackages = [];
let currentFilter = 'all';

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

    statusDiv.className = `border rounded-lg p-3 ${colors[type]}`;
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-4 h-4 mr-2 ${type === 'loading' ? 'animate-spin' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type]}"></path>
            </svg>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    log(`STATUS: ${message}`);
}

// Fonction de scan et ajout à la liste
async function scanAndAddPackage(code) {
    log(`🔍 Scan et ajout pour: "${code}"`);
    setStatus('Vérification du colis...', 'loading');

    // Vérifier si déjà dans la liste
    if (scannedPackages.some(pkg => pkg.code === code)) {
        setStatus('⚠️ Colis déjà dans la liste', 'error');
        return;
    }

    try {
        // Utilisation de l'endpoint officiel
        const url = '/deliverer/packages/scan';
        log(`📡 URL: ${window.location.origin}${url}`);

        const requestData = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ code: code.trim() })
        };

        log(`📤 Headers: ${JSON.stringify(requestData.headers, null, 2)}`);
        log(`📤 Body: ${requestData.body}`);

        const response = await fetch(url, requestData);

        const data = await response.json();
        log(`📋 Response data: ${JSON.stringify(data, null, 2)}`);

        // Créer l'objet package
        const packageItem = {
            code: code,
            timestamp: new Date().toLocaleTimeString(),
            valid: response.ok && data.success,
            status: data.package?.status || 'Erreur',
            cod_amount: data.package?.cod_amount || '0',
            formatted_cod: data.package?.formatted_cod || '0 DA',
            delivery_info: data.delivery_info || { name: 'N/A', address: 'N/A' },
            message: data.message || '',
            selected: false
        };

        // Ajouter à la liste
        scannedPackages.unshift(packageItem);
        updatePackageList();
        updatePackageCount();

        if (packageItem.valid) {
            setStatus(`✅ Colis ajouté: ${code}`, 'success');
        } else {
            setStatus(`❌ Colis ajouté (erreur): ${packageItem.message}`, 'error');
        }

    } catch (error) {
        log(`❌ ERREUR: ${error.message}`);

        // Ajouter même en cas d'erreur de connexion
        const packageItem = {
            code: code,
            timestamp: new Date().toLocaleTimeString(),
            valid: false,
            status: 'Erreur connexion',
            cod_amount: '0',
            formatted_cod: '0 DA',
            delivery_info: { name: 'N/A', address: 'N/A' },
            message: error.message,
            selected: false
        };

        scannedPackages.unshift(packageItem);
        updatePackageList();
        updatePackageCount();

        setStatus(`💥 Erreur ajouté: ${error.message}`, 'error');
    }

    // Reset input
    document.getElementById('packageCode').value = '';
    document.getElementById('packageCode').focus();
}

// Mettre à jour l'affichage de la liste
function updatePackageList() {
    const listDiv = document.getElementById('packageList');

    if (scannedPackages.length === 0) {
        listDiv.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m-4 4v4m0-4h4m0 0v4"></path>
                </svg>
                <p>Aucun colis scanné</p>
                <p class="text-sm">Scannez des codes pour commencer</p>
            </div>
        `;
        document.getElementById('batchActions').classList.add('hidden');
        return;
    }

    // Filtrer les packages
    let filteredPackages = scannedPackages;
    if (currentFilter === 'valid') {
        filteredPackages = scannedPackages.filter(pkg => pkg.valid);
    } else if (currentFilter === 'invalid') {
        filteredPackages = scannedPackages.filter(pkg => !pkg.valid);
    }

    listDiv.innerHTML = filteredPackages.map((pkg, index) => `
        <div class="border border-gray-200 rounded-lg p-4 ${pkg.valid ? 'bg-green-50' : 'bg-red-50'}">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <input type="checkbox"
                           class="mt-1 package-checkbox"
                           data-code="${pkg.code}"
                           ${pkg.selected ? 'checked' : ''}
                           onchange="togglePackageSelection('${pkg.code}')">

                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <p class="font-mono text-sm font-semibold">${pkg.code}</p>
                            <span class="text-xs px-2 py-1 rounded ${pkg.valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${pkg.status}
                            </span>
                        </div>

                        ${pkg.valid ? `
                            <div class="text-sm text-gray-600 space-y-1">
                                ${parseFloat(pkg.cod_amount) > 0 ? `<p><strong>COD:</strong> ${pkg.formatted_cod}</p>` : ''}
                                ${pkg.delivery_info.name !== 'N/A' ? `<p><strong>Destinataire:</strong> ${pkg.delivery_info.name}</p>` : ''}
                            </div>
                        ` : `
                            <p class="text-sm text-red-600">${pkg.message}</p>
                        `}

                        <p class="text-xs text-gray-400 mt-2">Scanné à ${pkg.timestamp}</p>
                    </div>
                </div>

                <button onclick="removePackage('${pkg.code}')"
                        class="text-red-600 hover:text-red-800 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `).join('');

    // Afficher les actions groupées si il y a des colis
    if (scannedPackages.length > 0) {
        document.getElementById('batchActions').classList.remove('hidden');
    }
}

// Mettre à jour le compteur
function updatePackageCount() {
    const validCount = scannedPackages.filter(pkg => pkg.valid).length;
    const totalCount = scannedPackages.length;

    document.getElementById('packageCount').textContent =
        `${totalCount} colis (${validCount} valides)`;
}

// Toggle sélection d'un package
function togglePackageSelection(code) {
    const pkg = scannedPackages.find(p => p.code === code);
    if (pkg) {
        pkg.selected = !pkg.selected;
        log(`📋 Package ${code} sélection: ${pkg.selected}`);
    }
}

// Supprimer un package de la liste
function removePackage(code) {
    scannedPackages = scannedPackages.filter(pkg => pkg.code !== code);
    updatePackageList();
    updatePackageCount();
    setStatus(`🗑️ Colis supprimé: ${code}`, 'info');
}

// Vider toute la liste
function clearAll() {
    if (scannedPackages.length === 0) return;

    if (confirm('Êtes-vous sûr de vouloir vider toute la liste ?')) {
        scannedPackages = [];
        updatePackageList();
        updatePackageCount();
        setStatus('🗑️ Liste vidée', 'info');
    }
}

// Filtrer les packages
function filterPackages(filter) {
    currentFilter = filter;

    // Mettre à jour les boutons de filtre
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-gray-200');
        btn.classList.add('bg-gray-100');
    });

    event.target.classList.add('active', 'bg-gray-200');
    event.target.classList.remove('bg-gray-100');

    updatePackageList();
    log(`🔍 Filtre appliqué: ${filter}`);
}

// Action groupée
function batchAction(action) {
    const selectedPackages = scannedPackages.filter(pkg => pkg.selected && pkg.valid);

    if (selectedPackages.length === 0) {
        setStatus('⚠️ Aucun colis valide sélectionné', 'error');
        return;
    }

    const actionNames = {
        accept: 'accepter',
        pickup: 'récupérer',
        deliver: 'livrer',
        return: 'retourner'
    };

    const actionName = actionNames[action] || action;

    if (confirm(`Êtes-vous sûr de vouloir ${actionName} ${selectedPackages.length} colis sélectionnés ?`)) {
        setStatus(`⚡ Action "${actionName}" sur ${selectedPackages.length} colis...`, 'loading');

        // Simulation d'action groupée
        setTimeout(() => {
            // Ici vous pouvez ajouter l'appel API réel
            selectedPackages.forEach(pkg => {
                pkg.selected = false;
                pkg.status = `${action.toUpperCase()}ED`;
            });

            updatePackageList();
            setStatus(`✅ Action "${actionName}" effectuée sur ${selectedPackages.length} colis`, 'success');
            log(`⚡ Action groupée ${action} sur ${selectedPackages.length} colis`);
        }, 1500);
    }
}

// Exporter la liste
function exportList() {
    if (scannedPackages.length === 0) {
        setStatus('⚠️ Aucun colis à exporter', 'error');
        return;
    }

    const csvContent = [
        ['Code', 'Statut', 'Valide', 'COD', 'Destinataire', 'Heure Scan'].join(','),
        ...scannedPackages.map(pkg => [
            pkg.code,
            pkg.status,
            pkg.valid ? 'Oui' : 'Non',
            pkg.cod_amount,
            pkg.delivery_info.name,
            pkg.timestamp
        ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `scan-batch-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);

    setStatus(`📄 Liste exportée (${scannedPackages.length} colis)`, 'success');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    log('📄 DOM chargé et prêt');

    const input = document.getElementById('packageCode');
    const addBtn = document.getElementById('addToListBtn');

    // Ajouter au clic du bouton
    addBtn.addEventListener('click', function() {
        const code = input.value.trim();
        if (code) {
            scanAndAddPackage(code);
        } else {
            setStatus('⚠️ Veuillez saisir un code', 'error');
            input.focus();
        }
    });

    // Ajouter avec Entrée
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const code = this.value.trim();
            if (code) {
                scanAndAddPackage(code);
            } else {
                setStatus('⚠️ Veuillez saisir un code', 'error');
            }
        }
    });

    // Focus automatique
    input.focus();

    setStatus('Prêt à scanner en lot', 'info');
    log('🎯 Page Scan en Lot initialisée avec succès');
});
</script>
@endsection