@extends('layouts.deliverer')

@section('title', 'Scan en Lot')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50 py-4">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Header Modern -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Scan en Lot</h1>
            <p class="text-gray-600">Scannez plusieurs colis en une fois</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Zone de saisie -->
            <div class="bg-white rounded-2xl shadow-xl border-0 p-6 backdrop-blur-sm">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                    Scanner des colis
                </h3>

                <!-- Sélection d'action OBLIGATOIRE -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-3">⚡ Choisir l'action (obligatoire)</h4>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="action" value="pickup" class="mr-3 text-blue-600">
                            <div>
                                <div class="font-medium text-gray-900">📦 Pickup Colis</div>
                                <div class="text-sm text-gray-600">Available/Accepted → Picked Up</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="action" value="change_deliverer" class="mr-3 text-green-600">
                            <div>
                                <div class="font-medium text-gray-900">👤 Changer Livreur</div>
                                <div class="text-sm text-gray-600">Available/Picked Up/Accepted seulement</div>
                            </div>
                        </label>
                    </div>
                    <div id="actionStatus" class="mt-3 text-sm text-red-600 hidden">
                        ⚠️ Veuillez sélectionner une action avant de scanner
                    </div>
                </div>

                <form id="addForm" class="space-y-4">
                    <div>
                        <label for="codeInput" class="block text-sm font-medium text-gray-700 mb-2">
                            Code du colis
                        </label>
                        <input type="text"
                               id="codeInput"
                               name="code"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-mono"
                               placeholder="PKG_XXXXXXX_YYYYMMDD"
                               autofocus
                               autocomplete="off">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="submit"
                                id="addButton"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            ➕ Ajouter
                        </button>
                        <button type="button"
                                id="cameraButton"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            📷 Caméra
                        </button>
                    </div>
                </form>

                <!-- Zone caméra AMÉLIORÉE -->
                <div id="cameraContainer" class="hidden mt-4">
                    <div class="bg-black rounded-lg overflow-hidden shadow-2xl border-2 border-green-500">
                        <!-- Header compact -->
                        <div class="bg-gradient-to-r from-green-600 to-blue-600 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-white text-lg">📷</span>
                                <h4 class="text-white font-semibold">Scanner Actif</h4>
                                <span id="scanCounter" class="bg-white bg-opacity-20 text-white px-2 py-1 rounded text-sm">0 scannés</span>
                            </div>
                            <button id="closeCameraButton"
                                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded
                                           border-2 border-white transition-all flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Fermer</span>
                            </button>
                        </div>

                        <!-- Zone vidéo avec overlay simplifié -->
                        <div class="relative">
                            <video id="cameraVideo" class="w-full h-80 md:h-96 object-cover"></video>

                            <!-- Messages temporaires overlay -->
                            <div id="tempMessage" class="hidden absolute top-4 left-1/2 transform -translate-x-1/2
                                                          bg-black bg-opacity-90 text-white px-4 py-3 rounded-lg text-center
                                                          border border-white shadow-lg max-w-xs z-10">
                                <div id="tempMessageIcon" class="text-2xl mb-1"></div>
                                <div id="tempMessageText" class="font-bold"></div>
                                <div id="tempMessageSubtext" class="text-sm opacity-80 mt-1"></div>
                            </div>

                            <!-- Zone de scan simple -->
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="border-2 border-green-400 w-64 h-64 rounded-lg animate-pulse opacity-50"></div>
                            </div>
                        </div>

                        <!-- Footer status -->
                        <div class="bg-gray-900 px-4 py-3">
                            <div id="cameraStatus" class="text-white text-center font-medium">Initialisation...</div>
                            <div id="lastScanInfo" class="text-gray-300 text-sm text-center mt-1 hidden">
                                Dernier: <span id="lastScanCode" class="font-mono"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Styles CSS simplifiés -->
                <style>
                    .temp-message-enter {
                        animation: tempMessageEnter 0.3s ease-out;
                    }

                    .temp-message-exit {
                        animation: tempMessageExit 0.3s ease-in;
                    }

                    @keyframes tempMessageEnter {
                        from {
                            opacity: 0;
                            transform: translate(-50%, -20px) scale(0.9);
                        }
                        to {
                            opacity: 1;
                            transform: translate(-50%, 0) scale(1);
                        }
                    }

                    @keyframes tempMessageExit {
                        from {
                            opacity: 1;
                            transform: translate(-50%, 0) scale(1);
                        }
                        to {
                            opacity: 0;
                            transform: translate(-50%, -20px) scale(0.9);
                        }
                    }

                    /* Empêcher le plein écran automatique de la vidéo */
                    #cameraVideo {
                        width: 100% !important;
                        height: auto !important;
                        max-height: 384px !important; /* md:h-96 */
                        object-fit: cover !important;
                        display: block !important;
                        position: relative !important; /* Empêcher position fixed/absolute */
                    }

                    /* S'assurer que le conteneur caméra reste dans sa zone */
                    #cameraContainer {
                        position: relative !important;
                        z-index: 1 !important;
                        max-width: 100% !important;
                        overflow: hidden !important;
                    }
                </style>

                <!-- Status -->
                <div id="statusBox" class="bg-gray-50 border border-gray-200 rounded-lg p-3 mt-4">
                    <p class="text-gray-600 text-center text-sm">Prêt à scanner</p>
                </div>

                <!-- Boutons de validation des actions -->
                <div id="validateActions" class="mt-6 space-y-3 hidden">
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800 font-medium">
                            💼 <span id="actionSummary">Action prête</span>
                        </p>
                    </div>
                    <button id="validatePickup" onclick="validatePickupAction()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg hidden">
                        📦 Valider Pickup (<span id="pickupCount">0</span> colis)
                    </button>
                    <button id="validateChangeDeliverer" onclick="validateChangeDelivererAction()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg hidden">
                        👤 Valider Changement Livreur (<span id="changeCount">0</span> colis)
                    </button>
                </div>

                <!-- Actions rapides -->
                <div class="mt-6 space-y-2">
                    <button onclick="clearAll()" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                        🗑️ Vider la liste
                    </button>
                    <button onclick="exportList()" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg text-sm">
                        📄 Exporter CSV
                    </button>
                </div>
            </div>

            <!-- Liste des colis -->
            <div class="bg-white rounded-2xl shadow-xl border-0 p-6 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Liste des colis
                    </h3>
                    <span id="countBadge" class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">0 colis</span>
                </div>

                <div id="packageList" class="space-y-3 max-h-96 overflow-y-auto">
                    <div class="text-center text-gray-500 py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m-4 4v4m0-4h4m0 0v4"></path>
                        </svg>
                        <p>Aucun colis dans la liste</p>
                        <p class="text-sm">Ajoutez des codes pour commencer</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bouton retour -->
        <div class="text-center mt-6">
            <a href="{{ route('deliverer.dashboard') }}"
               class="inline-flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                ← Retour au dashboard
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page Scan en Lot chargée');

    // Variables
    let packageList = [];
    let scanCounter = 0; // Compteur de scans
    let selectedAction = null; // Action sélectionnée

    // Éléments DOM
    const form = document.getElementById('addForm');
    const input = document.getElementById('codeInput');
    const button = document.getElementById('addButton');
    const statusBox = document.getElementById('statusBox');
    const listContainer = document.getElementById('packageList');
    const countBadge = document.getElementById('countBadge');

    // Éléments DOM nouveaux
    const scanCounterElement = document.getElementById('scanCounter');
    const tempMessage = document.getElementById('tempMessage');
    const tempMessageIcon = document.getElementById('tempMessageIcon');
    const tempMessageText = document.getElementById('tempMessageText');
    const tempMessageSubtext = document.getElementById('tempMessageSubtext');
    const lastScanInfo = document.getElementById('lastScanInfo');
    const lastScanCode = document.getElementById('lastScanCode');

    // Éléments DOM pour actions
    const actionInputs = document.querySelectorAll('input[name="action"]');
    const actionStatus = document.getElementById('actionStatus');
    const validateActions = document.getElementById('validateActions');
    const validatePickup = document.getElementById('validatePickup');
    const validateChangeDeliverer = document.getElementById('validateChangeDeliverer');
    const actionSummary = document.getElementById('actionSummary');
    const pickupCount = document.getElementById('pickupCount');
    const changeCount = document.getElementById('changeCount');

    // Fonction pour mettre à jour le status
    function updateStatus(message, type = 'info') {
        const colors = {
            info: 'bg-gray-50 border-gray-200 text-gray-600',
            loading: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800'
        };

        statusBox.className = `border rounded-lg p-3 mt-4 ${colors[type]}`;
        statusBox.innerHTML = `<p class="text-center text-sm font-medium">${message}</p>`;
        console.log(`STATUS: ${message}`);
    }

    // Fonction pour mettre à jour le compteur
    function updateCount() {
        const validCount = packageList.filter(pkg => pkg.valid).length;
        const totalCount = packageList.length;
        countBadge.textContent = `${totalCount} colis (${validCount} valides)`;

        // Mettre à jour le compteur de scan en mode caméra
        if (scanCounterElement) {
            scanCounterElement.textContent = `${scanCounter} scannés`;
        }
    }

    // Fonction pour afficher message temporaire dans l'overlay caméra
    function showTempMessage(icon, mainText, subText = '', type = 'success', duration = 3000) {
        if (!tempMessage) return;

        // Configurer le contenu
        tempMessageIcon.textContent = icon;
        tempMessageText.textContent = mainText;
        tempMessageSubtext.textContent = subText;

        // Configurer les couleurs selon le type
        tempMessage.className = tempMessage.className.replace(/bg-\w+-\d+/, '');
        if (type === 'success') {
            tempMessage.classList.add('bg-green-600');
        } else if (type === 'error') {
            tempMessage.classList.add('bg-red-600');
        } else if (type === 'warning') {
            tempMessage.classList.add('bg-yellow-600');
        }

        // Afficher avec animation
        tempMessage.classList.remove('hidden');
        tempMessage.classList.add('temp-message-enter');

        // Masquer après la durée
        setTimeout(() => {
            tempMessage.classList.add('temp-message-exit');
            setTimeout(() => {
                tempMessage.classList.add('hidden');
                tempMessage.classList.remove('temp-message-enter', 'temp-message-exit');
            }, 300);
        }, duration);
    }

    // Fonction pour mettre à jour l'info du dernier scan
    function updateLastScanInfo(code) {
        if (lastScanInfo && lastScanCode) {
            lastScanCode.textContent = code;
            lastScanInfo.classList.remove('hidden');
        }
    }

    // ==================== GESTION DES ACTIONS ====================

    // Fonction pour valider le statut selon l'action
    function isValidStatusForAction(status, action) {
        const statusLower = status.toLowerCase();

        if (action === 'pickup') {
            // Pickup: seulement Available ou Accepted
            return statusLower === 'available' || statusLower === 'accepted';
        } else if (action === 'change_deliverer') {
            // Changer livreur: Available, Picked Up ou Accepted
            return statusLower === 'available' || statusLower === 'picked_up' || statusLower === 'accepted';
        }

        return false;
    }

    // Fonction pour mettre à jour l'interface des actions
    function updateActionInterface() {
        if (!selectedAction || packageList.length === 0) {
            validateActions.classList.add('hidden');
            return;
        }

        const validPackages = packageList.filter(pkg =>
            pkg.valid && pkg.actionValid === true
        );

        validateActions.classList.remove('hidden');

        if (selectedAction === 'pickup') {
            actionSummary.textContent = `Pickup de ${validPackages.length} colis prêts`;
            pickupCount.textContent = validPackages.length;
            validatePickup.classList.remove('hidden');
            validateChangeDeliverer.classList.add('hidden');
        } else if (selectedAction === 'change_deliverer') {
            actionSummary.textContent = `Changement livreur pour ${validPackages.length} colis`;
            changeCount.textContent = validPackages.length;
            validateChangeDeliverer.classList.remove('hidden');
            validatePickup.classList.add('hidden');
        }
    }

    // Gestionnaire de changement d'action
    actionInputs.forEach(input => {
        input.addEventListener('change', function() {
            selectedAction = this.value;
            console.log('Action sélectionnée:', selectedAction);

            // Cacher le message d'erreur
            actionStatus.classList.add('hidden');

            // Re-valider tous les colis existants pour la nouvelle action
            packageList.forEach(pkg => {
                if (pkg.valid && pkg.data?.package?.status) {
                    pkg.actionValid = isValidStatusForAction(pkg.data.package.status, selectedAction);
                }
            });

            updateList();
            updateActionInterface();
        });
    });

    // Fonction pour mettre à jour la liste
    function updateList() {
        if (packageList.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m0 0V9a2 2 0 012-2h2m-4 4v4m0-4h4m0 0v4"></path>
                    </svg>
                    <p>Aucun colis dans la liste</p>
                    <p class="text-sm">Ajoutez des codes pour commencer</p>
                </div>
            `;
            return;
        }

        listContainer.innerHTML = packageList.map((pkg, index) => {
            const actionValidClass = pkg.actionValid === true ? 'bg-green-50 border-green-200' :
                                   pkg.actionValid === false ? 'bg-red-50 border-red-200' :
                                   'bg-gray-50 border-gray-200';

            const actionIcon = pkg.actionValid === true ? '✅' : pkg.actionValid === false ? '❌' : '⚪';

            return `
                <div class="border rounded-lg p-4 ${actionValidClass}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <p class="font-mono text-sm font-bold">${pkg.code}</p>
                                <span class="text-xs px-2 py-1 rounded ${pkg.valid ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'}">
                                    ${pkg.status}
                                </span>
                                ${pkg.action ? `
                                    <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-800">
                                        ${getActionLabel(pkg.action)}
                                    </span>
                                ` : ''}
                                <span class="text-sm">${actionIcon}</span>
                            </div>

                            ${pkg.valid && pkg.data ? `
                                <div class="text-sm text-gray-600 space-y-1">
                                    ${pkg.data.package.cod_amount && parseFloat(pkg.data.package.cod_amount) > 0 ? `
                                        <p><strong>COD:</strong> ${pkg.data.package.formatted_cod || pkg.data.package.cod_amount + ' DA'}</p>
                                    ` : ''}
                                    ${pkg.data.delivery_info && pkg.data.delivery_info.name !== 'N/A' ? `
                                        <p><strong>Destinataire:</strong> ${pkg.data.delivery_info.name}</p>
                                    ` : ''}
                                    ${pkg.actionValid === false ? `
                                        <p class="text-red-600"><strong>⚠️ Statut non autorisé pour cette action</strong></p>
                                    ` : ''}
                                </div>
                            ` : `
                                <p class="text-sm text-red-600">${pkg.message}</p>
                            `}

                            <p class="text-xs text-gray-400 mt-2">Ajouté à ${pkg.time}</p>
                        </div>

                        <button onclick="removePackage(${index})" class="text-red-600 hover:text-red-800 p-1 ml-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        updateCount();
    }

    // Fonction pour ajouter un colis avec vérifications renforcées
    async function addPackage(code, fromCamera = false) {
        console.log(`📦 Tentative ajout du code: ${code}`);

        // VÉRIFIER QU'UNE ACTION EST SÉLECTIONNÉE
        if (!selectedAction) {
            const message = '⚠️ Veuillez sélectionner une action avant de scanner';
            updateStatus(message, 'error');
            actionStatus.classList.remove('hidden');

            if (fromCamera) {
                playBeepError();
                showTempMessage('⚠️', 'ACTION MANQUANTE', 'Sélectionnez une action', 'error', 2000);
            }
            return;
        }

        updateStatus('🔄 Vérification...', 'loading');
        if (!fromCamera) {
            button.disabled = true;
        }

        try {
            const response = await fetch('/deliverer/packages/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: code.trim() })
            });

            console.log(`📥 Response status: ${response.status}`);
            const data = await response.json();
            console.log(`📋 Response data:`, data);

            if (response.ok && data.success) {
                // Colis valide trouvé en base
                const packageId = data.package?.id || data.package?.tracking_number || code;

                // VÉRIFIER SI LE COLIS (même ID) EXISTE DÉJÀ DANS LA LISTE
                const existingPackage = packageList.find(pkg =>
                    pkg.packageId === packageId ||
                    pkg.code === code ||
                    (pkg.data?.package?.id && pkg.data.package.id === data.package?.id)
                );

                if (existingPackage) {
                    // Colis déjà dans la liste - Son de succès mais pas d'ajout
                    console.log(`✅ Colis déjà dans la liste: ${packageId}`);

                    if (fromCamera) {
                        playBeepSuccess(); // Son de succès
                        showTempMessage('✅', 'DÉJÀ AJOUTÉ', `Colis: ${packageId}`, 'success', 2000);
                    }

                    updateStatus(`✅ Colis déjà dans la liste: ${packageId}`, 'success');
                    return; // NE PAS AJOUTER
                }

                // VALIDER LE STATUT POUR L'ACTION SÉLECTIONNÉE
                const packageStatus = data.package?.status;
                const actionValid = isValidStatusForAction(packageStatus, selectedAction);

                if (!actionValid) {
                    // Statut invalide pour cette action - son d'erreur et ne pas ajouter
                    console.log(`❌ Statut invalide pour action ${selectedAction}: ${packageStatus}`);

                    const message = `❌ Statut invalide: ${packageStatus} (requis: ${getRequiredStatusText(selectedAction)})`;
                    updateStatus(message, 'error');

                    if (fromCamera) {
                        playBeepError();
                        showTempMessage('❌', 'STATUT INVALIDE', `${packageStatus} non autorisé`, 'error', 3000);
                    }

                    // NE PAS AJOUTER à la liste
                    return;
                }

                // Nouveau colis valide avec statut correct - Ajouter à la liste
                const packageItem = {
                    code: code,
                    packageId: packageId, // ID unique du colis
                    time: new Date().toLocaleTimeString(),
                    valid: true,
                    actionValid: true, // Valide pour l'action sélectionnée
                    status: packageStatus || 'Valide',
                    action: selectedAction, // Action pour laquelle ce colis est scanné
                    message: data.message || '',
                    data: data
                };

                packageList.unshift(packageItem);
                scannedCodes.add(packageId); // Utiliser l'ID du colis, pas le code
                updateList();
                updateActionInterface(); // Mettre à jour l'interface des actions

                scanCounter++;
                updateLastScanInfo(code);

                const message = `✅ Colis ajouté pour ${selectedAction}: ${packageId}`;
                updateStatus(message, 'success');

                if (fromCamera) {
                    playBeepSuccess();
                    const packageInfo = data.package?.recipient?.name || packageId;
                    showTempMessage('✅', 'COLIS AJOUTÉ', `${getActionLabel(selectedAction)}: ${packageInfo}`, 'success', 3000);
                }

            } else {
                // Colis INVALIDE - NE PAS AJOUTER À LA LISTE
                console.log(`❌ Colis non trouvé: ${code}`);

                const message = `❌ Colis non trouvé: ${data.message || 'Introuvable en base'}`;
                updateStatus(message, 'error');

                if (fromCamera) {
                    playBeepError();
                    showTempMessage('❌', 'NON TROUVÉ', data.message || 'Colis introuvable', 'error', 3000);
                }

                // NE PAS AJOUTER à la liste ni au Set
            }

        } catch (error) {
            console.error('❌ Erreur:', error);

            // ERREUR RÉSEAU - NE PAS AJOUTER À LA LISTE
            updateStatus('💥 Erreur de connexion - Colis non ajouté', 'error');

            if (fromCamera) {
                playBeepError();
                showTempMessage('💥', 'ERREUR RÉSEAU', 'Vérifiez la connexion', 'error', 3000);
            }
        } finally {
            button.disabled = false;
            input.value = '';
            input.focus();
        }
    }

    // Fonction pour supprimer un colis
    window.removePackage = function(index) {
        const pkg = packageList[index];
        packageList.splice(index, 1);
        updateList();
        updateStatus(`🗑️ Supprimé: ${pkg.code}`, 'info');
    };

    // ==================== FONCTIONS UTILITAIRES ====================

    // Fonction pour obtenir le texte des statuts requis
    function getRequiredStatusText(action) {
        if (action === 'pickup') {
            return 'Available ou Accepted';
        } else if (action === 'change_deliverer') {
            return 'Available, Picked Up ou Accepted';
        }
        return 'Statut non défini';
    }

    // Fonction pour obtenir le label de l'action
    function getActionLabel(action) {
        if (action === 'pickup') {
            return 'PICKUP';
        } else if (action === 'change_deliverer') {
            return 'CHANGER LIVREUR';
        }
        return action.toUpperCase();
    }

    // ==================== VALIDATION FINALE DES ACTIONS ====================

    // Fonction pour valider l'action Pickup
    window.validatePickupAction = async function() {
        const validPackages = packageList.filter(pkg =>
            pkg.valid && pkg.actionValid === true && pkg.action === 'pickup'
        );

        if (validPackages.length === 0) {
            updateStatus('⚠️ Aucun colis valide pour Pickup', 'error');
            return;
        }

        if (!confirm(`Confirmer le Pickup de ${validPackages.length} colis ?`)) {
            return;
        }

        updateStatus('🔄 Validation Pickup en cours...', 'loading');
        validatePickup.disabled = true;

        try {
            const packageIds = validPackages.map(pkg => pkg.packageId);

            const response = await fetch('/deliverer/packages/bulk-pickup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ package_ids: packageIds })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                updateStatus(`✅ Pickup validé pour ${validPackages.length} colis`, 'success');

                // Vider la liste après succès
                packageList = [];
                scannedCodes.clear();
                scanCounter = 0;
                updateList();
                updateActionInterface();

                alert(`✅ Pickup réussi!\n${validPackages.length} colis ont été marqués comme "Picked Up"`);
            } else {
                updateStatus(`❌ Erreur Pickup: ${data.message || 'Erreur inconnue'}`, 'error');
                alert(`❌ Erreur lors du Pickup:\n${data.message || 'Erreur inconnue'}`);
            }
        } catch (error) {
            console.error('Erreur Pickup:', error);
            updateStatus('💥 Erreur réseau lors du Pickup', 'error');
            alert('💥 Erreur de connexion lors du Pickup');
        } finally {
            validatePickup.disabled = false;
        }
    };

    // Fonction pour valider le changement de livreur
    window.validateChangeDelivererAction = async function() {
        const validPackages = packageList.filter(pkg =>
            pkg.valid && pkg.actionValid === true && pkg.action === 'change_deliverer'
        );

        if (validPackages.length === 0) {
            updateStatus('⚠️ Aucun colis valide pour changement de livreur', 'error');
            return;
        }

        if (!confirm(`Confirmer le changement de livreur pour ${validPackages.length} colis ?`)) {
            return;
        }

        updateStatus('🔄 Changement livreur en cours...', 'loading');
        validateChangeDeliverer.disabled = true;

        try {
            const packageIds = validPackages.map(pkg => pkg.packageId);

            const response = await fetch('/deliverer/packages/bulk-change-deliverer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ package_ids: packageIds })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                updateStatus(`✅ Changement livreur validé pour ${validPackages.length} colis`, 'success');

                // Vider la liste après succès
                packageList = [];
                scannedCodes.clear();
                scanCounter = 0;
                updateList();
                updateActionInterface();

                alert(`✅ Changement de livreur réussi!\n${validPackages.length} colis ont été transférés`);
            } else {
                updateStatus(`❌ Erreur changement: ${data.message || 'Erreur inconnue'}`, 'error');
                alert(`❌ Erreur lors du changement de livreur:\n${data.message || 'Erreur inconnue'}`);
            }
        } catch (error) {
            console.error('Erreur changement livreur:', error);
            updateStatus('💥 Erreur réseau lors du changement', 'error');
            alert('💥 Erreur de connexion lors du changement de livreur');
        } finally {
            validateChangeDeliverer.disabled = false;
        }
    };

    // Fonction pour vider la liste
    window.clearAll = function() {
        if (packageList.length === 0) return;

        if (confirm('Êtes-vous sûr de vouloir vider toute la liste ?')) {
            packageList = [];
            scannedCodes.clear(); // Nettoyer aussi le tracking des IDs
            lastScannedCode = null;
            scanCounter = 0; // Reset compteur
            updateList();
            updateActionInterface(); // Mettre à jour l'interface des actions
            updateStatus('🗑️ Liste vidée', 'info');
        }
    };

    // Fonction pour exporter
    window.exportList = function() {
        if (packageList.length === 0) {
            updateStatus('⚠️ Aucun colis à exporter', 'error');
            return;
        }

        const csvContent = [
            ['Code', 'Statut', 'Valide', 'Heure', 'Message'].join(','),
            ...packageList.map(pkg => [
                pkg.code,
                pkg.status,
                pkg.valid ? 'Oui' : 'Non',
                pkg.time,
                pkg.message || ''
            ].join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `scan-lot-${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);

        updateStatus(`📄 Exporté: ${packageList.length} colis`, 'success');
    };

    // Gérer la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const code = input.value.trim();
        if (!code) {
            updateStatus('⚠️ Veuillez saisir un code', 'error');
            input.focus();
            return;
        }

        addPackage(code);
    });

    // Variables pour la caméra
    let cameraStream = null;
    let isScanning = false;
    let scanCooldown = false; // Cooldown pour éviter les scans trop rapides
    let lastScannedCode = null; // Dernier code scanné pour éviter les doublons
    let scannedCodes = new Set(); // Set pour tracking des codes déjà scannés

    // Éléments DOM caméra
    const cameraButton = document.getElementById('cameraButton');
    const cameraContainer = document.getElementById('cameraContainer');
    const closeCameraButton = document.getElementById('closeCameraButton');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraStatus = document.getElementById('cameraStatus');

    // Gérer l'ouverture de la caméra
    cameraButton.addEventListener('click', async function() {
        // VÉRIFIER QU'UNE ACTION EST SÉLECTIONNÉE AVANT D'OUVRIR LA CAMÉRA
        if (!selectedAction && cameraContainer.classList.contains('hidden')) {
            // Empêcher l'ouverture de la caméra si aucune action n'est sélectionnée
            updateStatus('⚠️ Veuillez sélectionner une action avant d\'ouvrir la caméra', 'error');
            actionStatus.classList.remove('hidden');

            // Flash visuel sur le bouton pour attirer l'attention
            cameraButton.classList.add('bg-red-600', 'animate-pulse');
            setTimeout(() => {
                cameraButton.classList.remove('bg-red-600', 'animate-pulse');
            }, 2000);

            return; // Bloquer l'ouverture
        }

        if (cameraContainer.classList.contains('hidden')) {
            await startCamera();
        } else {
            stopCamera();
        }
    });

    // Gérer la fermeture de la caméra
    closeCameraButton.addEventListener('click', function() {
        stopCamera();
    });

    // Démarrer la caméra
    async function startCamera() {
        try {
            cameraStatus.textContent = 'Démarrage de la caméra...';

            // Arrêter la caméra existante si elle fonctionne
            if (cameraStream) {
                stopCamera();
            }

            // S'assurer que le conteneur vidéo a la bonne taille AVANT de l'afficher
            cameraVideo.style.width = '100%';
            cameraVideo.style.height = 'auto';
            cameraVideo.style.maxHeight = '384px'; // md:h-96 = 24rem = 384px
            cameraVideo.style.objectFit = 'cover';
            cameraVideo.style.display = 'block';

            // Afficher le conteneur APRÈS avoir configuré les styles
            cameraContainer.classList.remove('hidden');

            // Demander accès à la caméra avec contraintes spécifiques
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 },
                    facingMode: 'environment'
                }
            });

            cameraVideo.srcObject = cameraStream;

            // Configurer les événements AVANT de lancer la vidéo
            cameraVideo.addEventListener('loadeddata', function() {
                // Forcer la taille après chargement pour éviter le plein écran
                cameraVideo.style.width = '100%';
                cameraVideo.style.height = 'auto';
                cameraVideo.style.maxHeight = '384px';

                cameraStatus.textContent = 'Caméra prête - Pointez vers un code-barres';
                startQuaggaScanning();
            }, { once: true });

            // Empêcher le plein écran automatique
            cameraVideo.addEventListener('loadedmetadata', function() {
                // Forcer les dimensions dès que les métadonnées sont chargées
                cameraVideo.style.width = '100%';
                cameraVideo.style.height = 'auto';
                cameraVideo.style.maxHeight = '384px';
                cameraVideo.style.objectFit = 'cover';
            }, { once: true });

            await cameraVideo.play();

            cameraButton.textContent = '📷 Fermer';
            cameraButton.classList.remove('bg-green-600', 'hover:bg-green-700');
            cameraButton.classList.add('bg-red-600', 'hover:bg-red-700');

        } catch (error) {
            console.error('Erreur caméra:', error);
            cameraStatus.textContent = 'Erreur: Impossible d\'accéder à la caméra';
            updateStatus('❌ Erreur caméra: ' + error.message, 'error');
        }
    }

    // Arrêter la caméra
    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        // Arrêter ZXing si actif
        if (window.codeReader) {
            try {
                window.codeReader.reset();
            } catch (e) {
                console.log('Erreur reset scanner:', e);
            }
        }

        isScanning = false;
        cameraContainer.classList.add('hidden');
        cameraButton.textContent = '📷 Caméra';
        cameraButton.classList.remove('bg-red-600', 'hover:bg-red-700');
        cameraButton.classList.add('bg-green-600', 'hover:bg-green-700');
        cameraStatus.textContent = 'Caméra fermée';
    }

    // Démarrer le scanning avec ZXing
    function startQuaggaScanning() {
        if (!window.ZXing) {
            cameraStatus.textContent = 'Chargement du scanner...';
            setTimeout(startQuaggaScanning, 500);
            return;
        }
        initZXing();
    }

    // Initialiser ZXing
    function initZXing() {
        if (isScanning) return;

        try {
            console.log("Initialisation ZXing...");

            // Créer les readers ZXing
            const codeReader = new ZXing.BrowserMultiFormatReader();
            window.codeReader = codeReader; // Stocker pour pouvoir l'arrêter

            isScanning = true;
            cameraStatus.textContent = 'Scanner actif - Pointez vers un code';

            // Démarrer le scan continu avec throttling
            codeReader.decodeFromVideoDevice(null, cameraVideo, (result, err) => {
                if (result && !scanCooldown) {
                    const code = result.getText();

                    // Éviter le même code que celui scanné il y a moins de 3 secondes
                    if (code === lastScannedCode) {
                        return;
                    }

                    console.log('📷 Code détecté:', code);

                    // Éviter de re-scanner le même code immédiatement
                    if (code === lastScannedCode) {
                        return;
                    }

                    // Marquer le code comme en cours de traitement
                    lastScannedCode = code;

                    // Pause pour validation
                    cameraStatus.textContent = '🔍 Validation en cours...';
                    showTempMessage('🔍', 'VALIDATION', 'Vérification en base...', 'success', 1000);

                    // Démarrer cooldown de 2.5 secondes
                    startScanCooldown(2500);

                    // Valider le code - la logique anti-doublon est dans addPackage
                    addPackage(code, true);
                }

                if (err && !(err instanceof ZXing.NotFoundException)) {
                    console.log('Erreur de scan:', err);
                }
            }).catch(err => {
                console.error('Erreur ZXing:', err);
                cameraStatus.textContent = 'Erreur de scanner: ' + err.message;
            });

        } catch (error) {
            console.error('Erreur init ZXing:', error);
            cameraStatus.textContent = 'Erreur scanner non disponible';
        }
    }

    // Fonction pour démarrer le cooldown de scan
    function startScanCooldown(duration) {
        scanCooldown = true;

        // Countdown visuel
        let remaining = Math.ceil(duration / 1000);
        cameraStatus.textContent = `⏳ Attente ${remaining}s avant prochain scan`;

        const countdown = setInterval(() => {
            remaining--;
            if (remaining > 0) {
                cameraStatus.textContent = `⏳ Attente ${remaining}s avant prochain scan`;
            } else {
                clearInterval(countdown);
                scanCooldown = false;
                cameraStatus.textContent = 'Scanner actif - Pointez vers un code';

                // Reset lastScannedCode après le cooldown
                lastScannedCode = null;
            }
        }, 1000);
    }



    // Fonction pour jouer un son de succès
    function playBeepSuccess() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 1000;
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (error) {
            console.log('Son non disponible:', error);
        }
    }

    // Fonction pour jouer un son d'erreur
    function playBeepError() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();

            // Son d'erreur en deux tons
            [400, 300].forEach((freq, index) => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = freq;
                gainNode.gain.setValueAtTime(0.2, audioContext.currentTime + (index * 0.15));
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + (index * 0.15) + 0.1);

                oscillator.start(audioContext.currentTime + (index * 0.15));
                oscillator.stop(audioContext.currentTime + (index * 0.15) + 0.1);
            });
        } catch (error) {
            console.log('Son non disponible:', error);
        }
    }

    // Fonction pour jouer un son de détection (legacy)
    function playBeep() {
        playBeepSuccess();
    }

    console.log('✅ Page Scan en Lot initialisée');
});
</script>

<!-- Charger ZXing (plus stable que QuaggaJS) -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

@endsection