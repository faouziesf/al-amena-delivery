@extends('layouts.deliverer')

@section('title', 'Scan Simple')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50">
    <div class="max-w-md mx-auto px-4 py-6">

        <!-- Header Modern -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Scanner QR</h1>
            <p class="text-gray-600">Scannez ou saisissez un code colis</p>
        </div>

        <!-- Formulaire de scan moderne -->
        <div class="bg-white rounded-2xl shadow-xl border-0 p-6 mb-6 backdrop-blur-sm">
            <form id="scanForm" class="space-y-6">
                <div>
                    <label for="codeInput" class="block text-sm font-semibold text-gray-800 mb-3">
                        Code du colis
                    </label>
                    <input type="text"
                           id="codeInput"
                           name="code"
                           class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 text-lg font-mono bg-gray-50 transition-all duration-200"
                           placeholder="PKG_XXXXXXX_YYYYMMDD"
                           autofocus
                           autocomplete="off">
                </div>

                <!-- Boutons modernes avec icônes -->
                <div class="space-y-3">
                    <button type="submit"
                            id="scanButton"
                            class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Scanner le Code</span>
                    </button>

                    <button type="button"
                            id="cameraButton"
                            class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Ouvrir Caméra</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Status moderne -->
        <div id="statusBox" class="bg-white/50 backdrop-blur-sm border-2 border-gray-200 rounded-xl p-4 mb-6 transition-all duration-300">
            <p class="text-gray-700 text-center font-medium">📱 Prêt à scanner</p>
        </div>

        <!-- Zone caméra -->
        <div id="cameraContainer" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="bg-black rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-white font-medium">Scanner avec la caméra</h4>
                    <button id="closeCameraButton" class="text-white hover:text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <video id="cameraVideo" width="100%" height="200" class="rounded"></video>
                <div id="cameraStatus" class="text-white text-sm mt-2 text-center">Initialisation...</div>
            </div>
        </div>

        <!-- Résultat avec actions -->
        <div id="resultBox" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div id="resultContent"></div>
        </div>

        <!-- Bouton retour -->
        <div class="text-center">
            <a href="{{ route('deliverer.dashboard') }}"
               class="inline-flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                ← Retour au dashboard
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Page Scan Simple chargée');

    const form = document.getElementById('scanForm');
    const input = document.getElementById('codeInput');
    const button = document.getElementById('scanButton');
    const statusBox = document.getElementById('statusBox');
    const resultBox = document.getElementById('resultBox');
    const resultContent = document.getElementById('resultContent');

    // Fonction pour mettre à jour le status
    function updateStatus(message, type = 'info') {
        const colors = {
            info: 'bg-gray-50 border-gray-200 text-gray-600',
            loading: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800'
        };

        statusBox.className = `border rounded-lg p-4 mb-6 ${colors[type]}`;
        statusBox.innerHTML = `<p class="text-center font-medium">${message}</p>`;
        console.log(`STATUS: ${message}`);
    }

    // Fonction pour générer les actions selon le statut
    function getActionsForStatus(package) {
        const status = package.status;
        const packageId = package.id;
        const canPerform = package.can_perform_action || {};

        let actionsHtml = '';

        // Actions selon le statut
        switch (status) {
            case 'AVAILABLE':
                if (canPerform.accept) {
                    actionsHtml += `
                        <button onclick="performAction('accept', ${packageId})"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg mb-2">
                            ✅ ACCEPTER LE PICKUP
                        </button>
                    `;
                }
                break;

            case 'ACCEPTED':
                if (canPerform.pickup) {
                    actionsHtml += `
                        <button onclick="performAction('pickup', ${packageId})"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg mb-2">
                            📦 MARQUER COLLECTÉ
                        </button>
                    `;
                }
                break;

            case 'PICKED_UP':
            case 'UNAVAILABLE':
                if (canPerform.deliver) {
                    // Pour les colis à 0 DT
                    if (parseFloat(package.cod_amount || 0) === 0) {
                        actionsHtml += `
                            <button onclick="performSimpleDelivery(${packageId}, '${package.recipient_data?.name || 'Destinataire'}')"
                                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg mb-2">
                                ✅ LIVRER (0 DT)
                            </button>
                        `;
                    } else {
                        actionsHtml += `
                            <a href="/deliverer/packages/${packageId}"
                               class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg mb-2 text-center">
                                ✅ LIVRER (${parseFloat(package.cod_amount).toFixed(3)} DT)
                            </a>
                        `;
                    }
                }

                if (canPerform.unavailable || status === 'PICKED_UP') {
                    actionsHtml += `
                        <button onclick="markUnavailable(${packageId})"
                                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg mb-2">
                            ⏰ MARQUER INDISPONIBLE
                        </button>
                    `;
                }
                break;

            case 'VERIFIED':
                if (canPerform.return) {
                    actionsHtml += `
                        <button onclick="performAction('return', ${packageId})"
                                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg mb-2">
                            ↩️ RETOURNER À L'EXPÉDITEUR
                        </button>
                    `;
                }
                break;

            case 'DELIVERED':
                actionsHtml += `
                    <div class="bg-green-100 text-green-800 p-3 rounded-lg text-center mb-2">
                        ✅ Colis déjà livré
                    </div>
                `;
                break;

            case 'RETURNED':
                actionsHtml += `
                    <div class="bg-gray-100 text-gray-800 p-3 rounded-lg text-center mb-2">
                        ↩️ Colis retourné à l'expéditeur
                    </div>
                `;
                break;

            default:
                actionsHtml += `
                    <div class="bg-yellow-100 text-yellow-800 p-3 rounded-lg text-center mb-2">
                        ⚠️ Aucune action disponible pour ce statut
                    </div>
                `;
        }

        // Toujours ajouter le bouton "Voir détails"
        actionsHtml += `
            <a href="/deliverer/packages/${packageId}"
               class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg text-center">
                👁️ VOIR DÉTAILS
            </a>
        `;

        return actionsHtml;
    }

    // Fonction pour afficher le résultat avec actions
    function showResult(success, data) {
        resultBox.classList.remove('hidden');

        if (success && data.success && data.package) {
            const package = data.package;
            const actionsHtml = getActionsForStatus(package);

            resultContent.innerHTML = `
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-green-800 mb-4">✅ Colis trouvé</h3>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Code</p>
                        <p class="font-mono text-lg font-bold">${package.package_code || package.code}</p>
                    </div>

                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Statut</p>
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                               ${getStatusBadgeClass(package.status)}">
                            ${getStatusLabel(package.status)}
                        </span>
                    </div>

                    ${package.cod_amount && parseFloat(package.cod_amount) > 0 ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Montant COD</p>
                        <p class="text-lg font-bold text-green-600">${parseFloat(package.cod_amount).toFixed(3)} DT</p>
                    </div>
                    ` : `
                    <div class="bg-blue-50 p-3 rounded">
                        <p class="text-sm text-blue-600">✅ Colis gratuit (0 DT)</p>
                    </div>
                    `}

                    ${package.recipient_data?.name ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Destinataire</p>
                        <p class="font-medium">${package.recipient_data.name}</p>
                        ${package.recipient_data.phone ? `<p class="text-sm text-gray-500">📞 ${package.recipient_data.phone}</p>` : ''}
                    </div>
                    ` : ''}
                </div>

                <!-- Actions selon le statut -->
                <div class="space-y-3 mb-4">
                    ${actionsHtml}
                </div>

                <button onclick="resetForm()" class="w-full bg-purple-100 hover:bg-purple-200 text-purple-700 font-semibold py-2 px-4 rounded-lg">
                    🔄 Scanner un autre colis
                </button>
            `;
        } else {
            resultContent.innerHTML = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-red-800 mb-4">❌ Colis non trouvé</h3>
                    <p class="text-red-600 mb-6">${data.message || 'Le code saisi n\'a pas été trouvé'}</p>

                    <button onclick="resetForm()" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                        🔄 Réessayer
                    </button>
                </div>
            `;
        }
    }

    // Fonctions helper pour l'affichage
    function getStatusBadgeClass(status) {
        const classes = {
            'AVAILABLE': 'bg-blue-100 text-blue-800',
            'ACCEPTED': 'bg-yellow-100 text-yellow-800',
            'PICKED_UP': 'bg-orange-100 text-orange-800',
            'UNAVAILABLE': 'bg-red-100 text-red-800',
            'DELIVERED': 'bg-green-100 text-green-800',
            'VERIFIED': 'bg-purple-100 text-purple-800',
            'RETURNED': 'bg-gray-100 text-gray-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusLabel(status) {
        const labels = {
            'AVAILABLE': 'Disponible',
            'ACCEPTED': 'Accepté',
            'PICKED_UP': 'Collecté',
            'UNAVAILABLE': 'Indisponible',
            'DELIVERED': 'Livré',
            'VERIFIED': 'À retourner',
            'RETURNED': 'Retourné'
        };
        return labels[status] || status;
    }

    // Fonction pour scanner un colis
    async function scanPackage(code) {
        console.log(`🔍 Scan du code: ${code}`);
        updateStatus('🔄 Recherche en cours...', 'loading');

        button.disabled = true;
        button.innerHTML = '<span>🔄 Scanning...</span>';

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

            const data = await response.json();

            if (response.ok && data.success) {
                updateStatus('✅ Colis trouvé avec succès!', 'success');
                showResult(true, data);
            } else {
                updateStatus(`❌ ${data.message || 'Colis non trouvé'}`, 'error');
                showResult(false, data);
            }

        } catch (error) {
            console.error('❌ Erreur:', error);
            updateStatus('💥 Erreur de connexion', 'error');
            showResult(false, { message: 'Erreur de connexion au serveur' });
        } finally {
            button.disabled = false;
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Scanner le Code</span>
            `;
        }
    }

    // Fonctions pour les actions
    window.performAction = async function(action, packageId) {
        try {
            updateStatus(`🔄 ${action === 'accept' ? 'Acceptation' : action === 'pickup' ? 'Collecte' : 'Action'} en cours...`, 'loading');

            const response = await fetch(`/deliverer/packages/${packageId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                updateStatus(`✅ ${data.message}`, 'success');
                showToast(data.message, 'success');

                // Relancer le scan pour mettre à jour l'affichage
                setTimeout(() => {
                    scanPackage(input.value.trim());
                }, 1000);
            } else {
                updateStatus(`❌ ${data.message}`, 'error');
                showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur action:', error);
            updateStatus('💥 Erreur lors de l\'action', 'error');
            showToast('Erreur de connexion', 'error');
        }
    };

    window.performSimpleDelivery = async function(packageId, recipientName) {
        if (!confirm('Confirmer la livraison de ce colis gratuit ?')) return;

        try {
            updateStatus('🔄 Livraison en cours...', 'loading');

            const response = await fetch(`/deliverer/packages/${packageId}/deliver`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    cod_collected: 0,
                    recipient_name: recipientName || 'Destinataire'
                })
            });

            const data = await response.json();

            if (data.success) {
                updateStatus('✅ Colis livré avec succès!', 'success');
                showToast('Colis livré avec succès!', 'success');

                // Relancer le scan pour mettre à jour
                setTimeout(() => {
                    scanPackage(input.value.trim());
                }, 1000);
            } else {
                updateStatus(`❌ ${data.message}`, 'error');
                showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Erreur livraison:', error);
            updateStatus('💥 Erreur lors de la livraison', 'error');
            showToast('Erreur de connexion', 'error');
        }
    };

    window.markUnavailable = function(packageId) {
        // Rediriger vers la page de détail pour plus d'options
        window.location.href = `/deliverer/packages/${packageId}`;
    };

    // Fonction pour reset le formulaire
    window.resetForm = function() {
        input.value = '';
        input.focus();
        resultBox.classList.add('hidden');
        updateStatus('📱 Prêt à scanner', 'info');
    };

    // Toast notifications
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 max-w-sm`;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Gérer la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const code = input.value.trim();
        if (!code) {
            updateStatus('⚠️ Veuillez saisir un code', 'error');
            input.focus();
            return;
        }

        scanPackage(code);
    });

    // Scanner avec Enter
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            form.dispatchEvent(new Event('submit'));
        }
    });

    // Variables et fonctions caméra (simplifiées)
    let cameraStream = null;
    const cameraButton = document.getElementById('cameraButton');
    const cameraContainer = document.getElementById('cameraContainer');
    const closeCameraButton = document.getElementById('closeCameraButton');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraStatus = document.getElementById('cameraStatus');

    cameraButton.addEventListener('click', async function() {
        if (cameraContainer.classList.contains('hidden')) {
            await startCamera();
        } else {
            stopCamera();
        }
    });

    closeCameraButton.addEventListener('click', stopCamera);

    async function startCamera() {
        try {
            cameraStatus.textContent = 'Démarrage de la caméra...';
            cameraContainer.classList.remove('hidden');

            if (cameraStream) stopCamera();

            // S'assurer que le conteneur vidéo a la bonne taille AVANT de l'afficher
            cameraVideo.style.width = '100%';
            cameraVideo.style.height = 'auto';
            cameraVideo.style.maxHeight = '200px';
            cameraVideo.style.objectFit = 'cover';
            cameraVideo.style.display = 'block';

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
                // Forcer la taille après chargement
                cameraVideo.style.width = '100%';
                cameraVideo.style.height = 'auto';
                cameraVideo.style.maxHeight = '200px';

                cameraStatus.textContent = 'Caméra prête - Pointez vers un code-barres';
                startZXingScanning();
            }, { once: true });

            // Empêcher le plein écran automatique
            cameraVideo.addEventListener('loadedmetadata', function() {
                // Forcer les dimensions dès que les métadonnées sont chargées
                cameraVideo.style.width = '100%';
                cameraVideo.style.height = 'auto';
                cameraVideo.style.maxHeight = '200px';
                cameraVideo.style.objectFit = 'cover';
            }, { once: true });

            await cameraVideo.play();

            cameraButton.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>Fermer Caméra</span>
            `;

        } catch (error) {
            console.error('Erreur caméra:', error);
            cameraStatus.textContent = 'Erreur: Impossible d\'accéder à la caméra';
            updateStatus('❌ Erreur caméra: ' + error.message, 'error');
        }
    }

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
        cameraStatus.textContent = 'Caméra fermée';

        cameraButton.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Ouvrir Caméra</span>
        `;
    }

    // Variables pour le scanning
    let isScanning = false;
    let scanCooldown = false;
    let lastScannedCode = null;

    // Démarrer le scanning avec ZXing
    function startZXingScanning() {
        if (!window.ZXing) {
            cameraStatus.textContent = 'Chargement du scanner...';
            setTimeout(startZXingScanning, 500);
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

                    // Marquer le code comme en cours de traitement
                    lastScannedCode = code;

                    // Pause pour validation
                    cameraStatus.textContent = '🔍 Validation en cours...';
                    playBeepSuccess();

                    // Démarrer cooldown de 2.5 secondes
                    startScanCooldown(2500);

                    // Remplir le champ input et scanner automatiquement
                    input.value = code;
                    scanPackage(code);
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

    console.log('✅ Page Scan Simple initialisée');
});
</script>

<!-- Charger ZXing pour scanning caméra -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

@endsection