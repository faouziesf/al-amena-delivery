@extends('layouts.depot-manager')

@section('title', 'Scanner Code-Barres - Traitement en Lot')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header moderne -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Scanner Code-Barres</h1>
                        <p class="text-slate-500 text-sm">Traitement en lot des colis retournés</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('depot-manager.packages.supplier-returns') }}"
                       class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Retours Fournisseur
                    </a>
                    <button onclick="clearAll()"
                            class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Vider Tout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Zone de scan -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Scanner Code-Barres</h3>
                        <p class="text-slate-500 text-sm">Scannez ou saisissez les codes colis</p>
                    </div>
                </div>

                <!-- Zone de scan avec caméra -->
                <div class="mb-6">
                    <div class="border-2 border-dashed border-blue-300 rounded-xl p-6 text-center bg-blue-50">
                        <button id="startCamera" onclick="startBarcodeScanning()"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors font-medium mb-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Démarrer Scanner Caméra
                        </button>
                        <div id="scanner-container" style="display: none;">
                            <video id="scanner-video" width="100%" style="max-width: 400px; border-radius: 8px;"></video>
                            <button id="stopCamera" onclick="stopBarcodeScanning()"
                                    class="mt-3 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                Arrêter Scanner
                            </button>
                        </div>
                        <p class="text-blue-600 font-medium">Scanner automatique activé</p>
                        <p class="text-slate-500 text-sm mt-2">Positionnez le code-barres devant la caméra</p>
                    </div>
                </div>

                <!-- Saisie manuelle -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Saisie manuelle ou scan lecteur USB
                    </label>
                    <div class="flex space-x-2">
                        <input type="text" id="manualInput" placeholder="Scannez ou tapez le code colis..."
                               class="flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onkeypress="handleManualInput(event)">
                        <button onclick="addManualCode()"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Statistiques de scan en temps réel -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600" id="scannedCount">0</div>
                        <div class="text-sm text-green-700">Scannés</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="duplicateCount">0</div>
                        <div class="text-sm text-yellow-700">Doublons</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600" id="errorCount">0</div>
                        <div class="text-sm text-red-700">Erreurs</div>
                    </div>
                </div>
            </div>

            <!-- Liste des codes scannés -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Codes Scannés</h3>
                            <p class="text-slate-500 text-sm">Liste des colis à traiter</p>
                        </div>
                    </div>
                    <button onclick="exportList()"
                            class="inline-flex items-center px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exporter
                    </button>
                </div>

                <!-- Liste scrollable -->
                <div class="border border-slate-200 rounded-lg max-h-96 overflow-y-auto">
                    <div id="scannedList" class="divide-y divide-slate-200">
                        <div class="p-4 text-center text-slate-500 italic">
                            Aucun code scanné pour le moment...
                        </div>
                    </div>
                </div>

                <!-- Actions de traitement -->
                <div class="mt-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Action à effectuer</label>
                        <select id="batchAction" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="return_to_supplier">Retourner au fournisseur</option>
                            <option value="mark_processed">Marquer comme traité</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Notes (optionnel)</label>
                        <textarea id="batchNotes" rows="3" placeholder="Notes pour ce traitement en lot..."
                                  class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <button onclick="processBatch()" id="processBtn" disabled
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-lg transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Traiter les Colis (<span id="batchCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour le scanner -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<script>
let scannedCodes = new Set();
let scannedCount = 0;
let duplicateCount = 0;
let errorCount = 0;
let codeReader = null;

// Mise à jour des compteurs
function updateCounters() {
    document.getElementById('scannedCount').textContent = scannedCount;
    document.getElementById('duplicateCount').textContent = duplicateCount;
    document.getElementById('errorCount').textContent = errorCount;
    document.getElementById('batchCount').textContent = scannedCodes.size;

    const processBtn = document.getElementById('processBtn');
    processBtn.disabled = scannedCodes.size === 0;
}

// Démarrer le scanner caméra
async function startBarcodeScanning() {
    try {
        codeReader = new ZXing.BrowserBarcodeReader();
        const videoElement = document.getElementById('scanner-video');

        document.getElementById('startCamera').style.display = 'none';
        document.getElementById('scanner-container').style.display = 'block';

        const result = await codeReader.decodeFromVideoDevice(null, videoElement, (result, err) => {
            if (result) {
                addScannedCode(result.text);
            }
        });

        showNotification('Scanner caméra démarré avec succès', 'success');
    } catch (err) {
        console.error('Erreur scanner:', err);
        showNotification('Erreur: Impossible d\'accéder à la caméra', 'error');
        document.getElementById('startCamera').style.display = 'block';
        document.getElementById('scanner-container').style.display = 'none';
    }
}

// Arrêter le scanner caméra
function stopBarcodeScanning() {
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }

    document.getElementById('startCamera').style.display = 'block';
    document.getElementById('scanner-container').style.display = 'none';
    showNotification('Scanner caméra arrêté', 'info');
}

// Gestion saisie manuelle
function handleManualInput(event) {
    if (event.key === 'Enter') {
        addManualCode();
    }
}

function addManualCode() {
    const input = document.getElementById('manualInput');
    const code = input.value.trim();

    if (code) {
        addScannedCode(code);
        input.value = '';
        input.focus();
    }
}

// Ajouter un code scanné
function addScannedCode(code) {
    const cleanCode = code.trim().toUpperCase();

    if (!cleanCode) return;

    if (scannedCodes.has(cleanCode)) {
        duplicateCount++;
        showNotification(`Code déjà scanné: ${cleanCode}`, 'warning');
    } else {
        scannedCodes.add(cleanCode);
        scannedCount++;
        addCodeToList(cleanCode);
        playBeepSound();
        showNotification(`Code ajouté: ${cleanCode}`, 'success');
    }

    updateCounters();
}

// Ajouter à la liste visuelle
function addCodeToList(code) {
    const listContainer = document.getElementById('scannedList');

    // Supprimer le message "Aucun code"
    if (listContainer.children.length === 1 && listContainer.children[0].textContent.includes('Aucun code')) {
        listContainer.innerHTML = '';
    }

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-3 hover:bg-slate-50';
    item.innerHTML = `
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="font-medium text-slate-900">${code}</div>
                <div class="text-xs text-slate-500">${new Date().toLocaleTimeString()}</div>
            </div>
        </div>
        <button onclick="removeCode('${code}')" class="text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    listContainer.insertBefore(item, listContainer.firstChild);
}

// Supprimer un code
function removeCode(code) {
    scannedCodes.delete(code);
    scannedCount--;
    updateCounters();

    // Reconstruire la liste
    const listContainer = document.getElementById('scannedList');
    listContainer.innerHTML = '';

    if (scannedCodes.size === 0) {
        listContainer.innerHTML = '<div class="p-4 text-center text-slate-500 italic">Aucun code scanné pour le moment...</div>';
    } else {
        scannedCodes.forEach(code => addCodeToList(code));
    }
}

// Vider tout
function clearAll() {
    if (confirm('Vider tous les codes scannés ?')) {
        scannedCodes.clear();
        scannedCount = 0;
        duplicateCount = 0;
        errorCount = 0;
        updateCounters();

        document.getElementById('scannedList').innerHTML = '<div class="p-4 text-center text-slate-500 italic">Aucun code scanné pour le moment...</div>';
        showNotification('Liste vidée', 'info');
    }
}

// Exporter la liste
function exportList() {
    if (scannedCodes.size === 0) {
        alert('Aucun code à exporter');
        return;
    }

    const data = Array.from(scannedCodes).join('\n');
    const blob = new Blob([data], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `codes-scannés-${new Date().toISOString().split('T')[0]}.txt`;
    a.click();

    URL.revokeObjectURL(url);
}

// Traiter le lot
async function processBatch() {
    if (scannedCodes.size === 0) {
        alert('Aucun code à traiter');
        return;
    }

    const action = document.getElementById('batchAction').value;
    const notes = document.getElementById('batchNotes').value;

    if (!confirm(`Traiter ${scannedCodes.size} colis ?`)) {
        return;
    }

    const processBtn = document.getElementById('processBtn');
    processBtn.disabled = true;
    processBtn.innerHTML = '<svg class="w-5 h-5 mr-2 inline animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Traitement en cours...';

    try {
        const response = await fetch('{{ route("depot-manager.packages.batch-scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                package_codes: Array.from(scannedCodes),
                action: action,
                batch_notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification(`${data.processed} colis traités avec succès`, 'success');
            if (data.errors && data.errors.length > 0) {
                showNotification(`Erreurs: ${data.errors.join(', ')}`, 'warning');
            }

            // Vider la liste après traitement réussi
            setTimeout(() => {
                clearAll();
            }, 2000);
        } else {
            throw new Error(data.message || 'Erreur lors du traitement');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors du traitement des colis', 'error');
    }

    processBtn.disabled = false;
    processBtn.innerHTML = '<svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Traiter les Colis (<span id="batchCount">' + scannedCodes.size + '</span>)';
}

// Son de bip
function playBeepSound() {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.frequency.value = 800;
    oscillator.type = 'square';

    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
}

// Focus automatique sur l'input manuel
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('manualInput').focus();
    updateCounters();
});

// Notifications
function showNotification(message, type) {
    const notification = document.createElement('div');
    let bgColor = 'bg-blue-500';

    switch(type) {
        case 'success': bgColor = 'bg-green-500'; break;
        case 'error': bgColor = 'bg-red-500'; break;
        case 'warning': bgColor = 'bg-yellow-500'; break;
        case 'info': bgColor = 'bg-blue-500'; break;
    }

    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${bgColor} text-white`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
</script>
@endsection