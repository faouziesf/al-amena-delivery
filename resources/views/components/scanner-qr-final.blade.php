<style>
    :root {
        --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        --color-primary: #3b82f6; --color-success: #10b981; --color-danger: #ef4444;
        --surface-background: #ffffff; --app-background: #f8fafc; --muted-background: #f1f5f9;
        --text-primary: #1e293b; --text-secondary: #64748b; --border-color: #e2e8f0;
        --border-radius-lg: 1.5rem; --border-radius-md: 1rem;
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .scanner-container .bg-white { font-family: var(--font-sans); background-color: var(--surface-background); border-radius: var(--border-radius-lg); box-shadow: var(--shadow-lg); padding: 1.5rem; }
    .scanner-container .text-lg.font-bold { color: var(--text-primary); font-size: 1.25rem; }
    .scanner-container .text-sm.text-gray-600 { color: var(--text-secondary); }
    .scanner-container .flex.mb-4.bg-gray-100 { background-color: var(--muted-background); }
    .scanner-container .flex.mb-4.bg-gray-100 button { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .scanner-container .flex.mb-4.bg-gray-100 button.bg-white { color: var(--text-primary) !important; font-weight: 600; border-radius: 0.75rem; }
    .scanner-container .text-emerald-600 { color: var(--color-success) !important; }
    .scanner-container .text-blue-600 { color: var(--color-primary) !important; }
    .scanner-container .bg-blue-50 { background-color: #eff6ff; }
    .scanner-container .text-blue-900 { color: #1e3a8a; }
    .scanner-container .text-blue-700 { color: #1d4ed8; }
    .scanner-container input[type="text"] { background-color: var(--surface-background); border-radius: var(--border-radius-md); transition: all 0.2s ease-in-out; }
    .scanner-container .border-gray-300 { border-color: var(--border-color); }
    .scanner-container input[type="text"]:focus { --tw-ring-opacity: 0.5; }
    .scanner-container .border-emerald-500 { --tw-border-opacity: 1; border-color: rgb(16 185 129 / var(--tw-border-opacity)); }
    .scanner-container .focus\:ring-emerald-200:focus { --tw-ring-color: #a7f3d0; }
    .scanner-container .border-red-500 { --tw-border-opacity: 1; border-color: rgb(239 68 68 / var(--tw-border-opacity)); }
    .scanner-container .focus\:ring-red-200:focus { --tw-ring-color: #fecaca; }
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
    .laser-line { position: absolute; left: 5%; right: 5%; height: 2px; background: var(--color-success); box-shadow: 0 0 10px var(--color-success); animation: laser-scan 2.5s infinite alternate ease-in-out; }
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
                    <div class="relative">
                        <input type="text" x-ref="manualInput" x-model="manualCode" 
                               @keydown.enter="searchCode()" @input="validateCode()"
                               class="w-full px-4 py-3 border rounded-xl focus:ring-2 text-lg font-mono uppercase transition-colors"
                               :class="{
                                'border-gray-300 focus:border-primary-500 focus:ring-primary-200': !manualCode,
                                'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-200': codeValid,
                                'border-red-500 focus:border-red-500 focus:ring-red-200': manualCode && !codeValid
                               }"
                               placeholder="PKG_1234..." autofocus>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg x-show="codeValid" class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <svg x-show="manualCode && !codeValid" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <button @click="searchCode()" :disabled="!codeValid || searching" class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!searching">Rechercher</span>
                    <span x-show="searching">Recherche...</span>
                </button>
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
        init() { this.loadStoredData(); if (this.isMobile && !this.isHttps) { this.activeMode = 'manual'; } this.$watch('manualCode', () => this.validateCode()); },
        openScanner() { this.scannerVisible = true; this.resetScanner(); if (this.activeMode === 'manual') { setTimeout(() => { if (this.$refs.manualInput) { this.$refs.manualInput.focus(); } }, 100); } },
        closeScanner() { this.stopCamera(); this.scannerVisible = false; this.resetScanner(); },
        resetScanner() { this.manualCode = ''; this.codeValid = false; this.searching = false; this.cameraErrorMsg = ''; this.resultVisible = false; this.permissionAsked = false; },
        switchMode(mode) { if (mode === 'camera' && this.isMobile && !this.isHttps) { return; } this.activeMode = mode; if (mode === 'manual') { this.stopCamera(); setTimeout(() => { if (this.$refs.manualInput) { this.$refs.manualInput.focus(); } }, 100); } },
        async requestCameraPermission() { this.permissionAsked = true; this.cameraErrorMsg = ''; if (this.isMobile && !this.isHttps) { this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile.'; return; } try { if (!navigator.mediaDevices?.getUserMedia) { throw new Error('getUserMedia non supporté'); } await this.startCamera(); } catch (error) { this.cameraErrorMsg = this.getCameraErrorMessage(error); } },
        async startCamera() { this.cameraErrorMsg = ''; try { this.stopCamera(); const constraints = { video: { width: { min: 640, ideal: this.isMobile ? 1280 : 1920 }, height: { min: 480, ideal: this.isMobile ? 720 : 1080 }, frameRate: { min: 15, ideal: 30 } } }; if (this.isMobile) { constraints.video.facingMode = { exact: "environment" }; } this.videoStream = await navigator.mediaDevices.getUserMedia(constraints); const video = this.$refs.videoElement; if (!video) { throw new Error('Élément vidéo non trouvé'); } video.srcObject = this.videoStream; await new Promise((resolve, reject) => { video.onloadedmetadata = () => { resolve(); }; video.onerror = reject; setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000); }); this.cameraActive = true; this.startScanning(); } catch (error) { this.cameraErrorMsg = this.getCameraErrorMessage(error); this.stopCamera(); } },
        stopCamera() { this.stopScanning(); if (this.videoStream) { this.videoStream.getTracks().forEach(track => track.stop()); this.videoStream = null; } if (this.$refs.videoElement) { this.$refs.videoElement.srcObject = null; } this.cameraActive = false; },
        retryCamera() { this.cameraErrorMsg = ''; this.requestCameraPermission(); },
        getCameraErrorMessage(error) { const msg = error.message || error.toString(); if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) { return 'Permission refusée. Autorisez la caméra dans les paramètres.'; } if (msg.includes('NotFoundError')) { return 'Aucune caméra trouvée.'; } if (msg.includes('NotReadableError')) { return 'Caméra déjà utilisée.'; } return 'Erreur caméra. Réessayez ou utilisez le mode manuel.'; },
        startScanning() { this.initQuaggaScanner(); this.startAlternatingScans(); },
        startAlternatingScans() { this.scanInterval = setInterval(() => { this.scanCycle++; if (this.scanCycle % 3 === 0) { this.scanMode = 'qr'; this.analyzeQRFrame(); } else { this.scanMode = 'barcode'; } }, 500); },
        stopScanning() { if (this.scanInterval) { clearInterval(this.scanInterval); this.scanInterval = null; } this.stopQuaggaScanner(); this.scanCycle = 0; this.scanMode = 'barcode'; },
        initQuaggaScanner() { if (typeof Quagga === 'undefined') { return; } try { const video = this.$refs.videoElement; if (!video) return; Quagga.init({ inputStream: { type: "LiveStream", target: video, constraints: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: this.isMobile ? "environment" : "user" } }, decoder: { readers: ["code_128_reader", "ean_reader", "code_39_reader"] }, locate: true, debug: false }, (err) => { if (err) { return; } Quagga.start(); }); Quagga.onDetected((result) => { if (result?.codeResult?.code && this.scanMode === 'barcode') { const code = result.codeResult.code.trim(); if (this.isValidPackageCode(code)) { this.onCodeDetected(code, 'BARCODE'); } } }); } catch (error) {} },
        stopQuaggaScanner() { try { if (typeof Quagga !== 'undefined') { Quagga.stop(); } } catch (error) {} },
        analyzeQRFrame() { try { const video = this.$refs.videoElement; const canvas = this.$refs.canvasElement; if (!video || !canvas || !video.videoWidth) { return; } const ctx = canvas.getContext('2d'); canvas.width = video.videoWidth; canvas.height = video.videoHeight; ctx.drawImage(video, 0, 0); if (typeof jsQR !== 'undefined') { const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height); const qrResult = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: "dontInvert" }); if (qrResult?.data) { const code = qrResult.data.trim(); if (this.isValidCode(code)) { this.onCodeDetected(code, 'QR'); } } } } catch (error) {} },
        onCodeDetected(code, type) { const now = Date.now(); const normalizedCode = this.normalizePackageCode(code); if (this.lastDetection && (now - this.lastDetection.time < 2000) && this.lastDetection.code === normalizedCode) { return; } this.lastDetection = { code: normalizedCode, time: now, type }; this.stopCamera(); if (navigator.vibrate) { navigator.vibrate(100); } this.processCode(normalizedCode); },
        // 🚀 LOGIQUE ULTRA SIMPLE - ACCEPTE TOUT
        normalizePackageCode(input) {
            return window.extractCodeFromUrl ? window.extractCodeFromUrl(input) : (input || '').toString().trim().toUpperCase();
        },
        isValidPackageCode(input) {
            return window.isValidPackageCode ? window.isValidPackageCode(input) : true;
        },
        isValidCode(code) { return this.isValidPackageCode(code); },
        validateCode() { this.codeValid = this.isValidPackageCode(this.manualCode); },
        searchCode() { if (!this.codeValid || this.searching) return; this.processCode(this.normalizePackageCode(this.manualCode)); },
        useRecentCode(code) { this.manualCode = code; this.validateCode(); this.processCode(code); },
        async processCode(code) { this.searching = true; try { this.addToRecent(code); const response = await fetch('/deliverer/scan/process', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ qr_code: code }) }); const data = await response.json(); this.showResult(data); } catch (error) { this.showResult({ success: false, message: 'Erreur de connexion.' }); } this.searching = false; },
        showResult(data) { this.result = data; this.resultVisible = true; if (data.success && data.redirect) { setTimeout(() => { this.goToPackage(); }, 5000); } },
        closeResult() { this.resultVisible = false; },
        goToPackage() { if (this.result.redirect) { this.closeScanner(); window.location.href = this.result.redirect; } },
        getActionLabel() { const action = this.result.action; switch (action) { case 'accept': return 'Accepter'; case 'pickup': return 'Collecter'; case 'deliver': return 'Livrer'; case 'return': return 'Retourner'; default: return 'Voir'; } },
        loadStoredData() { try { this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]'); this.scanHistory = []; } catch { this.recentCodes = []; } },
        addToRecent(code) { const item = { value: code, timestamp: Date.now() }; this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10); try { localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes)); } catch (error) {} },
        formatTime(timestamp) { return new Date(timestamp).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); },
    }
}
</script>