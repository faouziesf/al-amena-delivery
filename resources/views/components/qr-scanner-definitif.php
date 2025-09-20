<style>
    :root {
        --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        --color-primary: #3b82f6;
        --color-success: #10b981;
        --color-danger: #ef4444;
        --color-warning: #f59e0b;
        --surface-background: #ffffff;
        --app-background: #f8fafc;
        --muted-background: #f1f5f9;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --border-radius-lg: 1.5rem;
        --border-radius-md: 1rem;
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .scanner-container .bg-white { font-family: var(--font-sans); background-color: var(--surface-background); border-radius: var(--border-radius-lg); box-shadow: var(--shadow-lg); padding: 1.5rem; }
    .scanner-container .text-lg.font-bold { color: var(--text-primary); font-size: 1.25rem; }
    .scanner-container .text-sm.text-gray-600 { color: var(--text-secondary); }
    .scanner-container .bg-amber-50 { background-color: #fffbeb; border-color: #fde68a; }
    .scanner-container .text-amber-800 { color: #92400e; }
    .scanner-container .text-amber-700 { color: #b45309; }
    .scanner-container .flex.mb-4.bg-gray-100 { background-color: var(--muted-background); }
    .scanner-container .flex.mb-4.bg-gray-100 button { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .scanner-container .flex.mb-4.bg-gray-100 button.bg-white { color: var(--text-primary) !important; font-weight: 600; border-radius: 0.75rem; }
    .scanner-container .text-emerald-600 { color: var(--color-success) !important; }
    .scanner-container .text-blue-600 { color: var(--color-primary) !important; }
    .scanner-container .bg-blue-50 { background-color: #eff6ff; }
    .scanner-container .text-blue-900 { color: #1e3a8a; }
    .scanner-container .text-blue-700 { color: #1d4ed8; }
    .scanner-container input[type="text"] { background-color: var(--surface-background); border-color: var(--border-color); border-radius: var(--border-radius-md); transition: all 0.2s ease-in-out; }
    .scanner-container input[type="text"]:focus { --tw-ring-color: var(--color-primary); border-color: var(--color-primary); }
    .scanner-container .bg-gray-50.hover\:bg-gray-100 { background-color: var(--muted-background); transition: background-color 0.2s; }
    .scanner-container .bg-gray-50.hover\:bg-gray-100:hover { background-color: #e2e8f0; }
    .scanner-container button { border-radius: var(--border-radius-md); font-weight: 600; transition: all 0.2s ease-in-out; }
    .scanner-container button:hover { filter: brightness(1.1); }
    .scanner-container .bg-blue-600 { background-color: var(--color-primary); }
    .scanner-container .bg-emerald-600 { background-color: var(--color-success); }
    .scanner-container .bg-red-600 { background-color: var(--color-danger); }
    .result-modal-container { backdrop-filter: blur(4px); }
    .result-modal-container .bg-white { border-radius: var(--border-radius-lg); box-shadow: var(--shadow-lg); padding: 1.5rem 2rem; }
    .result-modal-container .bg-emerald-100 { background-color: #d1fae5; }
    .result-modal-container .text-emerald-800 { color: #065f46; }
    .result-modal-container .bg-red-100 { background-color: #fee2e2; }
    .result-modal-container .text-red-800 { color: #991b1b; }
    .result-modal-container .bg-gray-50 { background-color: var(--surface-background); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); }
    .result-modal-container .bg-gray-200 { background-color: #e2e8f0; color: #334155; }
    .result-modal-container .bg-gray-200:hover { background-color: #cbd5e1; }
    @keyframes laser-scan { 0% { top: 0; } 100% { top: 100%; } }
    .laser-line { position: absolute; left: 0; right: 0; height: 2px; background: var(--color-success); box-shadow: 0 0 10px var(--color-success); animation: laser-scan 2.5s infinite alternate ease-in-out; }
</style>

<div x-data="scannerQRFinal()" @open-scanner.window="openScanner()">
    
    <div x-show="scannerVisible" x-transition class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center scanner-container">
        <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">
            
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Scanner le Colis</h3>
                    <p class="text-sm text-gray-600" x-text="activeMode === 'camera' ? 'Mode caméra' : 'Saisie manuelle'"></p>
                </div>
                <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-show="!isHttps && isMobile" class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg></div>
                    <div class="ml-3"><p class="text-sm text-amber-800">La caméra nécessite une connexion sécurisée (HTTPS).</p></div>
                </div>
            </div>
            
            <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
                <button @click="switchMode('camera')" :class="activeMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'" :disabled="!isHttps && isMobile" class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all disabled:opacity-50 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Caméra</span>
                </button>
                <button @click="switchMode('manual')" :class="activeMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'" class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center space-x-2">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Manuel</span>
                </button>
            </div>

            <div x-show="activeMode === 'camera'" class="space-y-4">
                <div x-show="!permissionAsked && !cameraActive" class="text-center p-6 bg-blue-50 rounded-xl">
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Activer la caméra</h4>
                    <p class="text-sm text-blue-700 mb-4">Autorisez l'accès pour scanner automatiquement.</p>
                    <button @click="requestCameraPermission()" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700">Autoriser</button>
                </div>
                <div x-show="permissionAsked" class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 1;">
                    <video x-ref="videoElement" class="w-full h-full object-cover" autoplay playsinline muted x-show="cameraActive && !cameraErrorMsg"></video>
                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div x-show="permissionAsked && !cameraActive && !cameraErrorMsg" class="text-white text-center">
                            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <p>Démarrage...</p>
                        </div>
                        <div x-show="cameraErrorMsg" class="bg-red-500 text-white p-4 rounded-xl text-center max-w-xs mx-4">
                            <p class="text-sm mb-3 font-medium">Erreur Caméra</p>
                            <p class="text-xs mb-3" x-text="cameraErrorMsg"></p>
                            <button @click="retryCamera()" class="w-full bg-white text-red-500 px-3 py-2 rounded text-sm font-medium">Réessayer</button>
                        </div>
                    </div>
                    <div x-show="cameraActive && !cameraErrorMsg" class="absolute inset-0 pointer-events-none" style="box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.6);">
                         <div class="w-full h-full relative" style="clip-path: polygon(15% 15%, 85% 15%, 85% 85%, 15% 85%);">
                            <div class="laser-line"></div>
                         </div>
                    </div>
                </div>
                <div x-show="permissionAsked" class="flex justify-center space-x-3">
                    <button @click="startCamera()" x-show="!cameraActive && !cameraErrorMsg" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700">Démarrer</button>
                    <button @click="stopCamera()" x-show="cameraActive" class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700">Arrêter</button>
                </div>
            </div>

            <div x-show="activeMode === 'manual'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
                    <input type="text" x-ref="manualInput" x-model="manualCode" @keydown.enter="searchCode()" @input="validateCode()" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-lg font-mono uppercase" placeholder="PKG_1234..." autofocus>
                </div>
                <button @click="searchCode()" :disabled="!codeValid || searching" class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!searching">Rechercher</span>
                    <span x-show="searching">Recherche...</span>
                </button>
            </div>

            <div x-show="scanHistory.length > 0" class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Historique Récent</h4>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="(scan, index) in scanHistory.slice(0, 3)" :key="index">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-mono" x-text="scan.code"></p>
                                <p class="text-xs text-gray-500" x-text="scan.result"></p>
                            </div>
                            <span class="text-lg" x-text="scan.success ? '✅' : '❌'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div x-show="resultVisible" x-transition class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4 result-modal-container">
        <div x-show="resultVisible" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100" class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4" :class="result.success ? 'bg-emerald-100' : 'bg-red-100'">
                    <span class="text-3xl" x-text="result.success ? '✅' : '❌'"></span>
                </div>
                <h3 class="text-xl font-bold mb-2" :class="result.success ? 'text-emerald-800' : 'text-red-800'" x-text="result.success ? 'Colis Trouvé' : 'Erreur'"></h3>
                <p class="text-gray-700 mb-4 text-sm" x-text="result.message"></p>
                <div x-show="result.package" class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-4">
                    <div class="pb-3 border-b border-gray-200">
                        <p class="text-sm text-gray-500">Code Colis</p>
                        <p class="font-mono text-lg text-blue-600 font-bold" x-text="result.package?.code"></p>
                    </div>
                    <dl class="text-sm space-y-3">
                        <div x-show="result.delivery_info?.name"><dt class="font-semibold text-gray-600">Destinataire</dt><dd class="text-gray-900" x-text="result.delivery_info?.name"></dd></div>
                        <div x-show="result.delivery_info?.address"><dt class="font-semibold text-gray-600">Adresse</dt><dd class="text-gray-900" x-text="result.delivery_info?.address"></dd></div>
                        <div x-show="result.package?.cod_amount > 0"><dt class="font-semibold text-gray-600">Montant à encaisser</dt><dd class="text-emerald-600 font-bold text-lg" x-text="result.package?.formatted_cod"></dd></div>
                    </dl>
                </div>
                <div class="flex space-x-3">
                    <button @click="closeResult()" class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">Fermer</button>
                    <button x-show="result.success && result.redirect" @click="goToPackage()" class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700" x-text="getActionLabel()"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Le code JavaScript original est conservé ici sans modification
function scannerQRFinal() {
    return {
        scannerVisible: false, activeMode: 'manual', isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent), isHttps: location.protocol === 'https:', cameraActive: false, cameraErrorMsg: '', videoStream: null, scanInterval: null, lastDetection: null, permissionAsked: false, scanMode: 'barcode', scanCycle: 0, manualCode: '', codeValid: false, searching: false, recentCodes: [], result: {}, resultVisible: false, scanHistory: [],
        init() { console.log('Init scanner - Mobile:', this.isMobile, 'HTTPS:', this.isHttps); this.loadStoredData(); if (this.isMobile && !this.isHttps) { this.activeMode = 'manual'; } this.$watch('manualCode', () => this.validateCode()); },
        openScanner() { console.log('Ouverture scanner...'); this.scannerVisible = true; this.resetScanner(); if (this.activeMode === 'manual') { setTimeout(() => { if (this.$refs.manualInput) { this.$refs.manualInput.focus(); } }, 100); } },
        closeScanner() { console.log('Fermeture scanner...'); this.stopCamera(); this.scannerVisible = false; this.resetScanner(); },
        resetScanner() { this.manualCode = ''; this.codeValid = false; this.searching = false; this.cameraErrorMsg = ''; this.resultVisible = false; this.permissionAsked = false; },
        switchMode(mode) { console.log('Changement mode:', mode); if (mode === 'camera' && this.isMobile && !this.isHttps) { this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile'; return; } this.activeMode = mode; if (mode === 'manual') { this.stopCamera(); setTimeout(() => { if (this.$refs.manualInput) { this.$refs.manualInput.focus(); } }, 100); } },
        async requestCameraPermission() { console.log('Demande permission caméra...'); this.permissionAsked = true; this.cameraErrorMsg = ''; if (this.isMobile && !this.isHttps) { this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile. Utilisez le mode manuel.'; return; } try { if (!navigator.mediaDevices?.getUserMedia) { throw new Error('getUserMedia non supporté par ce navigateur'); } await this.startCamera(); } catch (error) { console.error('Erreur permission caméra:', error); this.cameraErrorMsg = this.getCameraErrorMessage(error); } },
        async startCamera() { console.log('Démarrage caméra...'); this.cameraErrorMsg = ''; try { this.stopCamera(); const constraints = { video: { width: { min: 640, ideal: this.isMobile ? 1280 : 1920 }, height: { min: 480, ideal: this.isMobile ? 720 : 1080 }, frameRate: { min: 15, ideal: 30 } } }; if (this.isMobile) { constraints.video.facingMode = { exact: "environment" }; } console.log('Contraintes caméra:', constraints); this.videoStream = await navigator.mediaDevices.getUserMedia(constraints); const video = this.$refs.videoElement; if (!video) { throw new Error('Élément vidéo non trouvé'); } video.srcObject = this.videoStream; await new Promise((resolve, reject) => { video.onloadedmetadata = () => { console.log('Vidéo prête - Résolution:', video.videoWidth, 'x', video.videoHeight); resolve(); }; video.onerror = reject; setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000); }); this.cameraActive = true; this.startScanning(); console.log('Caméra démarrée avec succès'); } catch (error) { console.error('Erreur démarrage caméra:', error); this.cameraErrorMsg = this.getCameraErrorMessage(error); this.stopCamera(); } },
        stopCamera() { this.stopScanning(); if (this.videoStream) { this.videoStream.getTracks().forEach(track => { console.log('Arrêt track caméra:', track.kind); track.stop(); }); this.videoStream = null; } if (this.$refs.videoElement) { this.$refs.videoElement.srcObject = null; } this.cameraActive = false; },
        retryCamera() { console.log('Nouvelle tentative caméra...'); this.cameraErrorMsg = ''; this.requestCameraPermission(); },
        getCameraErrorMessage(error) { const msg = error.message || error.toString(); console.log('Erreur caméra détaillée:', msg, error); if (msg.includes('Permission denied') || msg.includes('NotAllowedError') || error.name === 'NotAllowedError') { return 'Permission refusée. Autorisez l\'accès caméra dans les paramètres de votre navigateur.'; } if (msg.includes('NotFoundError') || error.name === 'NotFoundError') { return 'Aucune caméra trouvée sur cet appareil.'; } if (msg.includes('NotReadableError') || error.name === 'NotReadableError') { return 'Caméra occupée par une autre application. Fermez les autres apps utilisant la caméra.'; } if (msg.includes('OverconstrainedError') || error.name === 'OverconstrainedError') { return 'Contraintes caméra non supportées. Essayez de redémarrer l\'application.'; } if (msg.includes('SecurityError') || error.name === 'SecurityError') { return 'Erreur de sécurité. HTTPS requis pour la caméra.'; } if (msg.includes('AbortError') || error.name === 'AbortError') { return 'Accès caméra interrompu. Réessayez.'; } if (msg.includes('getUserMedia non supporté')) { return 'Votre navigateur ne supporte pas la caméra. Utilisez le mode manuel.'; } return 'Erreur caméra. Vérifiez les permissions et réessayez ou utilisez le mode manuel.'; },
        startScanning() { console.log('Démarrage scan en alternance...'); this.initQuaggaScanner(); this.startAlternatingScans(); },
        startAlternatingScans() { this.scanInterval = setInterval(() => { this.scanCycle++; if (this.scanCycle % 2 === 0) { this.scanMode = 'barcode'; this.scanBarcodes(); } else { this.scanMode = 'qr'; this.scanQRCodes(); } console.log(`🔄 Cycle ${this.scanCycle} - Mode: ${this.scanMode}`); }, 750); },
        scanBarcodes() { console.log('📊 Cycle codes-barres actif (Quagga en arrière-plan)'); },
        scanQRCodes() { console.log('📱 Cycle QR codes actif'); this.analyzeQRFrame(); },
        stopScanning() { console.log('Arrêt scan en alternance...'); if (this.scanInterval) { clearInterval(this.scanInterval); this.scanInterval = null; } this.stopQuaggaScanner(); this.scanCycle = 0; this.scanMode = 'barcode'; },
        initQuaggaScanner() { if (typeof Quagga === 'undefined') { console.warn('QuaggaJS non disponible'); return; } try { const video = this.$refs.videoElement; if (!video) return; Quagga.init({ inputStream: { type: "LiveStream", target: video, constraints: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: this.isMobile ? "environment" : "user" } }, decoder: { readers: ["code_128_reader", "ean_reader", "code_39_reader"] }, locate: true, debug: false }, (err) => { if (err) { console.error('Erreur Quagga:', err); return; } console.log('📊 Quagga initialisé pour codes-barres (sans zones)'); Quagga.start(); }); Quagga.onDetected((result) => { if (result?.codeResult?.code && this.scanMode === 'barcode') { const code = result.codeResult.code.trim(); console.log('📊 Code-barres détecté pendant cycle barcode:', code); if (this.isValidPackageCode(code)) { console.log('📊 Code-barres validé:', code); this.onCodeDetected(code, 'BARCODE'); } } }); } catch (error) { console.error('Erreur QuaggaJS:', error); } },
        stopQuaggaScanner() { try { if (typeof Quagga !== 'undefined') { Quagga.stop(); console.log('📊 Quagga arrêté'); } } catch (error) { console.error('Erreur arrêt Quagga:', error); } },
        startQRScanning() { this.qrScanInterval = setInterval(() => { this.analyzeQRFrame(); }, 300); },
        stopQRScanning() { if (this.qrScanInterval) { clearInterval(this.qrScanInterval); this.qrScanInterval = null; console.log('QR scan arrêté'); } },
        analyzeQRFrame() { try { const video = this.$refs.videoElement; const canvas = this.$refs.canvasElement; if (!video || !canvas || !video.videoWidth || !video.videoHeight) { return; } const ctx = canvas.getContext('2d'); canvas.width = video.videoWidth; canvas.height = video.videoHeight; ctx.drawImage(video, 0, 0); if (typeof jsQR !== 'undefined') { const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height); const qrResult = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: "dontInvert" }); if (qrResult?.data) { const code = qrResult.data.trim(); console.log('📱 QR Code détecté:', code); if (this.isValidCode(code)) { this.onCodeDetected(code, 'QR'); } } } } catch (error) { console.error('Erreur analyse QR:', error); } },
        captureFrame() { console.log('📸 Capture manuelle...'); this.analyzeQRFrame(); },
        onCodeDetected(code, type) { const now = Date.now(); if (this.lastDetection && (now - this.lastDetection.time < 2000) && this.lastDetection.code === code) { console.log('⏭️ Détection ignorée (doublon récent)', code); return; } this.lastDetection = { code, time: now, type }; console.log(`🎉 Code ${type} DÉTECTÉ ET VALIDÉ:`, code); console.log('📊 Arrêt du scanner et traitement...'); this.stopCamera(); this.showDetectionFeedback(code, type); this.processCode(code); },
        isValidPackageCode(code) { if (!code || code.length < 6) { return false; } const cleanCode = code.trim().toUpperCase(); if (/^https?:\/\/.*\/track\//.test(cleanCode)) { return true; } const validPatterns = [/^PKG_[A-Z0-9]{6,}_\d{6,}$/, /^[A-Z0-9]{6,20}$/, /^\d{6,}$/, /^[A-Z0-9_]{6,}$/, /^[A-Z]{4,}[0-9]{4,}$/, /^[A-Z0-9]{8}_\d{8}$/]; if (this.isObviousText(cleanCode)) { return false; } return validPatterns.some(pattern => pattern.test(cleanCode)); },
        isObviousText(code) { const obviousWords = ['LIVRAISON', 'DELIVERY', 'BON', 'ALAMENA', 'SERVICE', 'CONTACT', 'TELEPHONE', 'ADRESSE', 'CLIENT', 'DATE']; const upperCode = code.toUpperCase(); return obviousWords.some(word => upperCode === word || upperCode.includes(word) && word.length > 4); },
        looksLikeText(code) { return this.isObviousText(code); },
        isValidCode(code) { return this.isValidPackageCode(code); },
        showDetectionFeedback(code, type) { console.log(`${type === 'QR' ? '📱' : '📊'} DÉTECTION ${type}:`, code); if (navigator.vibrate && this.isMobile) { navigator.vibrate([100, 50, 100]); } this.playDetectionSound(); },
        playDetectionSound() { try { const audioContext = new (window.AudioContext || window.webkitAudioContext)(); const oscillator = audioContext.createOscillator(); const gainNode = audioContext.createGain(); oscillator.connect(gainNode); gainNode.connect(audioContext.destination); oscillator.frequency.setValueAtTime(800, audioContext.currentTime); gainNode.gain.setValueAtTime(0.3, audioContext.currentTime); gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2); oscillator.start(audioContext.currentTime); oscillator.stop(audioContext.currentTime + 0.2); } catch (error) {} },
        validateCode() { const code = this.manualCode.trim().toUpperCase(); this.codeValid = this.isValidPackageCode(code); },
        detectCodeFormat(code) { if (/^https?:\/\/.*\/track\//.test(code)) { return 'QR_TRACKING_URL'; } if (/^PKG_[A-Z0-9]{8,}_\d{8}$/.test(code)) { return 'FULL_PACKAGE_CODE'; } if (/^[A-Z0-9]{8,16}$/.test(code)) { return 'SHORT_BARCODE'; } if (/^\d{8,}$/.test(code)) { return 'NUMERIC_CODE'; } return 'UNKNOWN_FORMAT'; },
        searchCode() { if (!this.codeValid || this.searching) return; const code = this.manualCode.trim().toUpperCase(); this.processCode(code); },
        useRecentCode(code) { this.manualCode = code; this.validateCode(); this.processCode(code); },
        async processCode(code) { console.log('🔍 DÉBUT TRAITEMENT CODE:', code); this.searching = true; try { this.addToRecent(code); console.log('📡 Envoi requête au serveur...'); const response = await fetch('/deliverer/packages/scan', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ code: code }) }); console.log('📥 Réponse serveur reçue:', response.status); const data = await response.json(); console.log('📋 Données reçues:', data); this.addToHistory(code, data.message, data.success); this.showResult(data); } catch (error) { console.error('❌ ERREUR RÉSEAU:', error); this.addToHistory(code, 'Erreur de connexion', false); this.showResult({ success: false, message: 'Erreur de connexion. Vérifiez votre réseau.' }); } this.searching = false; console.log('✅ FIN TRAITEMENT CODE'); },
        showResult(data) { this.result = data; this.resultVisible = true; if (data.success && data.redirect) { setTimeout(() => { this.goToPackage(); }, 5000); } },
        closeResult() { this.resultVisible = false; },
        goToPackage() { if (this.result.redirect) { this.closeScanner(); window.location.href = this.result.redirect; } },
        getActionLabel() { const action = this.result.action; switch (action) { case 'accept': return 'Accepter'; case 'pickup': return 'Collecter'; case 'deliver': return 'Livrer'; case 'return': return 'Retourner'; default: return 'Voir'; } },
        loadStoredData() { try { this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]'); this.scanHistory = JSON.parse(localStorage.getItem('scanner_history') || '[]'); } catch { this.recentCodes = []; this.scanHistory = []; } },
        addToRecent(code) { const item = { value: code, timestamp: Date.now() }; this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10); try { localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes)); } catch (error) { console.error('Erreur sauvegarde:', error); } },
        addToHistory(code, result, success) { const item = { code, result, success, timestamp: Date.now() }; this.scanHistory = [item, ...this.scanHistory].slice(0, 20); try { localStorage.setItem('scanner_history', JSON.stringify(this.scanHistory)); } catch (error) { console.error('Erreur sauvegarde:', error); } },
        formatTime(timestamp) { return new Date(timestamp).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); },
        formatMoney(amount) { return parseFloat(amount || 0).toFixed(3) + ' DT'; }
    }
}
</script>