<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner Retours - {{ $depotManagerName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
        #reader {
            width: 100%;
        }
        #reader__scan_region {
            border: 2px solid #f97316 !important;
        }
        .scan-success {
            animation: flashGreen 0.5s ease-in-out;
        }
        @keyframes flashGreen {
            0%, 100% { background-color: white; }
            50% { background-color: #10b981; }
        }
    </style>
</head>
<body class="bg-orange-50">
    <div id="app" class="h-full flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white p-4 shadow-lg flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">📦 Scanner Retours</h1>
                    <p class="text-sm text-orange-100">Chef: {{ $depotManagerName }}</p>
                </div>
                <div id="scan-count" class="bg-white text-orange-600 px-3 py-1 rounded-full font-bold text-lg">
                    0
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div id="status-bar" class="bg-white border-b border-gray-200 p-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2">
                <div id="status-indicator" class="w-3 h-3 bg-green-500 rounded-full"></div>
                <span id="status-text" class="text-sm font-medium text-gray-700">Prêt à scanner</span>
            </div>
            <button onclick="toggleCamera()" id="camera-toggle" class="text-blue-600 font-medium text-sm">
                📷 Pause
            </button>
        </div>

        <!-- Scanner Section -->
        <div class="flex-1 relative overflow-hidden">
            <div id="reader" class="w-full h-full"></div>
            <div id="scanner-overlay" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="border-4 border-orange-400 rounded-lg w-72 h-72 relative">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white rounded-br-lg"></div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white border-t-2 border-orange-300 p-4 flex-shrink-0" style="max-height: 30vh; overflow-y: auto;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-800">Derniers scans:</h3>
                <button onclick="validateSession()" id="validate-btn" disabled
                    class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                    ✅ Valider
                </button>
            </div>
            <div id="scanned-list" class="space-y-2">
                <p class="text-center text-gray-400 text-sm py-2">Aucun colis scanné</p>
            </div>
        </div>
    </div>

    <!-- Session Terminated Popup -->
    <div id="session-popup" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Session Terminée</h3>
            <p id="popup-message" class="text-gray-600 mb-6">La session a été validée sur le PC</p>
            <a href="/depot/returns" class="block w-full bg-orange-600 text-white py-3 rounded-lg font-bold">
                Retour à l'accueil
            </a>
        </div>
    </div>

<script>
const sessionId = '{{ $sessionId }}';
let html5QrCode;
let isScanning = true;
let scannedPackages = [];
let sessionCheckInterval;

// Initialiser le scanner
document.addEventListener('DOMContentLoaded', async function() {
    initScanner();
    checkSessionActivity();
    sessionCheckInterval = setInterval(checkSessionActivity, 3000);
});

// Initialiser le scanner QR
async function initScanner() {
    html5QrCode = new Html5Qrcode("reader");

    try {
        const cameras = await Html5Qrcode.getCameras();
        if (cameras && cameras.length) {
            const cameraId = cameras[cameras.length - 1].id; // Caméra arrière

            html5QrCode.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                },
                onScanSuccess,
                onScanError
            ).catch(err => {
                console.error('Erreur démarrage scanner:', err);
                alert('Impossible d\'accéder à la caméra');
            });
        }
    } catch (err) {
        console.error('Erreur:', err);
    }
}

// Scan réussi
async function onScanSuccess(decodedText, decodedResult) {
    if (!isScanning) return;

    // Vibrer
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }

    // Scanner le colis
    await scanPackage(decodedText.trim());
}

function onScanError(error) {
    // Ignorer les erreurs normales de scan
}

// Scanner un colis via API
async function scanPackage(packageCode) {
    try {
        const response = await fetch(`/depot/returns/api/session/${sessionId}/scan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ package_code: packageCode })
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data.message, data.package);
            scannedPackages.push(data.package);
            updateScannedList();
            updateCount(data.total_scanned);
        } else {
            showError(data.message);
        }
    } catch (error) {
        console.error('Erreur scan:', error);
        showError('Erreur de connexion');
    }
}

// Afficher succès
function showSuccess(message, pkg) {
    document.body.classList.add('scan-success');
    setTimeout(() => document.body.classList.remove('scan-success'), 500);

    document.getElementById('status-indicator').className = 'w-3 h-3 bg-green-500 rounded-full';
    document.getElementById('status-text').textContent = message;
    document.getElementById('status-text').className = 'text-sm font-medium text-green-600';

    setTimeout(() => {
        document.getElementById('status-text').textContent = 'Prêt à scanner';
        document.getElementById('status-text').className = 'text-sm font-medium text-gray-700';
    }, 2000);
}

// Afficher erreur
function showError(message) {
    if (navigator.vibrate) {
        navigator.vibrate([100, 50, 100]);
    }

    document.getElementById('status-indicator').className = 'w-3 h-3 bg-red-500 rounded-full';
    document.getElementById('status-text').textContent = message;
    document.getElementById('status-text').className = 'text-sm font-medium text-red-600';

    setTimeout(() => {
        document.getElementById('status-indicator').className = 'w-3 h-3 bg-green-500 rounded-full';
        document.getElementById('status-text').textContent = 'Prêt à scanner';
        document.getElementById('status-text').className = 'text-sm font-medium text-gray-700';
    }, 3000);
}

// Mettre à jour la liste
function updateScannedList() {
    const listContainer = document.getElementById('scanned-list');

    if (scannedPackages.length === 0) {
        listContainer.innerHTML = '<p class="text-center text-gray-400 text-sm py-2">Aucun colis scanné</p>';
        return;
    }

    listContainer.innerHTML = scannedPackages.slice(-5).reverse().map(pkg => `
        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-2">
            <div class="font-mono font-bold text-green-900 text-sm">${pkg.code}</div>
            <div class="text-xs text-gray-600">${pkg.sender} - ${pkg.cod} TND</div>
        </div>
    `).join('');
}

// Mettre à jour le compteur
function updateCount(count) {
    document.getElementById('scan-count').textContent = count;
    document.getElementById('validate-btn').disabled = count === 0;
}

// Toggle caméra
function toggleCamera() {
    if (isScanning) {
        html5QrCode.pause();
        isScanning = false;
        document.getElementById('camera-toggle').textContent = '📷 Reprendre';
    } else {
        html5QrCode.resume();
        isScanning = true;
        document.getElementById('camera-toggle').textContent = '📷 Pause';
    }
}

// Vérifier l'activité de la session
async function checkSessionActivity() {
    try {
        const response = await fetch(`/depot/returns/api/session/${sessionId}/check-activity`);
        const data = await response.json();

        if (!data.active) {
            stopCamera();
            showSessionTerminatedPopup(data.reason);
            clearInterval(sessionCheckInterval);
        }
    } catch (error) {
        console.error('Erreur vérification session:', error);
    }
}

// Arrêter la caméra
function stopCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            isScanning = false;
        }).catch(err => console.error('Erreur arrêt caméra:', err));
    }
}

// Afficher popup session terminée
function showSessionTerminatedPopup(reason) {
    document.getElementById('popup-message').textContent = reason || 'La session a été terminée';
    document.getElementById('session-popup').classList.remove('hidden');

    // Désactiver tous les boutons
    document.querySelectorAll('button').forEach(btn => btn.disabled = true);
}

// Valider la session
async function validateSession() {
    if (scannedPackages.length === 0) {
        alert('Aucun colis à valider');
        return;
    }

    if (!confirm(`Créer ${scannedPackages.length} colis retour(s) ?`)) {
        return;
    }

    try {
        const response = await fetch(`/depot/returns/${sessionId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (data.success) {
            stopCamera();
            showSessionTerminatedPopup(data.message);
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        console.error('Erreur validation:', error);
        alert('Erreur lors de la validation');
    }
}
</script>
</body>
</html>
