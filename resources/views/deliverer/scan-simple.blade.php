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

        <!-- Résultat -->
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

    // Fonction pour afficher le résultat
    function showResult(success, data) {
        resultBox.classList.remove('hidden');

        if (success && data.success && data.package) {
            resultContent.innerHTML = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-green-800 mb-4">✅ Colis trouvé</h3>
                </div>

                <div class="space-y-3">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Code</p>
                        <p class="font-mono text-lg font-bold">${data.package.code}</p>
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
                        <p class="text-lg font-bold text-green-600">${data.package.formatted_cod || data.package.cod_amount + ' DA'}</p>
                    </div>
                    ` : ''}

                    ${data.delivery_info && data.delivery_info.name !== 'N/A' ? `
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Destinataire</p>
                        <p class="font-medium">${data.delivery_info.name}</p>
                        ${data.delivery_info.address !== 'N/A' ? `<p class="text-sm text-gray-500">${data.delivery_info.address}</p>` : ''}
                    </div>
                    ` : ''}
                </div>

                <button onclick="resetForm()" class="mt-6 w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    ✅ Scanner un autre colis
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

    // Fonction pour scanner un colis
    async function scanPackage(code) {
        console.log(`🔍 Scan du code: ${code}`);
        updateStatus('🔄 Recherche en cours...', 'loading');

        // Désactiver le formulaire
        button.disabled = true;
        button.textContent = '🔄 Scanning...';

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
            // Réactiver le formulaire
            button.disabled = false;
            button.textContent = '🔍 Scanner';
        }
    }

    // Fonction pour reset le formulaire
    window.resetForm = function() {
        input.value = '';
        input.focus();
        resultBox.classList.add('hidden');
        updateStatus('Prêt à scanner', 'info');
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

        scanPackage(code);
    });

    // Scanner avec Enter
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            form.dispatchEvent(new Event('submit'));
        }
    });

    // Variables pour la caméra
    let cameraStream = null;
    let isScanning = false;

    // Éléments DOM caméra
    const cameraButton = document.getElementById('cameraButton');
    const cameraContainer = document.getElementById('cameraContainer');
    const closeCameraButton = document.getElementById('closeCameraButton');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraStatus = document.getElementById('cameraStatus');

    // Gérer l'ouverture de la caméra
    cameraButton.addEventListener('click', async function() {
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
            cameraContainer.classList.remove('hidden');

            // Arrêter la caméra existante si elle fonctionne
            if (cameraStream) {
                stopCamera();
            }

            // Demander accès à la caméra
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'environment'
                }
            });

            cameraVideo.srcObject = cameraStream;
            cameraVideo.play();

            // Attendre que la vidéo soit prête
            cameraVideo.addEventListener('loadeddata', function() {
                cameraStatus.textContent = 'Caméra prête - Pointez vers un code-barres';
                startQuaggaScanning();
            });

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
        if (window.ZXing && window.codeReader) {
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

            isScanning = true;
            cameraStatus.textContent = 'Scanner actif - Pointez vers un code';

            // Démarrer le scan continu
            codeReader.decodeFromVideoDevice(null, cameraVideo, (result, err) => {
                if (result) {
                    const code = result.getText();
                    console.log('✅ Code détecté:', code);

                    // Arrêter le scan
                    codeReader.reset();
                    cameraStatus.textContent = '🔍 Validation...';

                    // Valider et scanner le code
                    validateAndScanFromCamera(code);
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

    // Fonction pour valider et scanner un code depuis la caméra
    async function validateAndScanFromCamera(code) {
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
                // Code valide
                console.log('✅ Code valide:', code);
                playBeepSuccess();
                showCameraFeedback('✅ Code valide - Redirection...', 'success');

                // Arrêter la caméra
                stopCamera();

                // Rediriger vers la page show du colis après 1 seconde
                setTimeout(() => {
                    if (data.package && data.package.id) {
                        window.location.href = `/deliverer/packages/${data.package.id}`;
                    } else {
                        // Fallback: afficher le résultat
                        input.value = code;
                        updateStatus('✅ Colis trouvé avec succès!', 'success');
                        showResult(true, data);
                    }
                }, 1000);
            } else {
                // Code invalide
                console.log('❌ Code invalide:', code);
                playBeepError();
                showCameraFeedback('❌ Code invalide!', 'error');
                updateStatus(`❌ ${data.message || 'Colis non trouvé'}`, 'error');
                showResult(false, data);

                // Arrêter la caméra
                stopCamera();
            }
        } catch (error) {
            console.error('❌ Erreur validation:', error);
            playBeepError();
            showCameraFeedback('💥 Erreur réseau', 'error');
            updateStatus('💥 Erreur de connexion', 'error');
            showResult(false, { message: 'Erreur de connexion au serveur' });

            // Arrêter la caméra
            stopCamera();
        }
    }

    // Fonction pour afficher feedback visuel sur la caméra
    function showCameraFeedback(message, type) {
        const feedback = document.createElement('div');
        feedback.style.position = 'absolute';
        feedback.style.top = '50%';
        feedback.style.left = '50%';
        feedback.style.transform = 'translate(-50%, -50%)';
        feedback.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
        feedback.style.color = 'white';
        feedback.style.padding = '12px 24px';
        feedback.style.borderRadius = '8px';
        feedback.style.fontSize = '18px';
        feedback.style.fontWeight = 'bold';
        feedback.style.zIndex = '1000';
        feedback.style.textAlign = 'center';
        feedback.style.opacity = '0.95';
        feedback.textContent = message;

        cameraVideo.parentNode.appendChild(feedback);

        // Supprimer après 2 secondes
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 2000);
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

    console.log('✅ Page Scan Simple initialisée');
});
</script>

<!-- Charger ZXing (plus stable que QuaggaJS) -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

@endsection