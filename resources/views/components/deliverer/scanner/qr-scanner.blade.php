@props([
    'mode' => 'single', // single, batch
    'autoRedirect' => true,
    'showRecent' => true
])

<style>
    :root {
        --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        --color-primary: #3b82f6; --color-success: #10b981; --color-danger: #ef4444;
        --surface-background: #ffffff; --app-background: #f8fafc; --muted-background: #f1f5f9;
        --text-primary: #1e293b; --text-secondary: #64748b; --border-color: #e2e8f0;
        --border-radius-lg: 1.5rem; --border-radius-md: 1rem;
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .scanner-container .bg-white {
        font-family: var(--font-sans);
        background-color: var(--surface-background);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 1.5rem;
    }
    .scanner-container .text-lg.font-bold { color: var(--text-primary); font-size: 1.25rem; }
    .scanner-container .text-sm.text-gray-600 { color: var(--text-secondary); }
    .scanner-container .flex.mb-4.bg-gray-100 { background-color: var(--muted-background); }
    .scanner-container .flex.mb-4.bg-gray-100 button { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .scanner-container .flex.mb-4.bg-gray-100 button.bg-white {
        color: var(--text-primary) !important;
        font-weight: 600;
        border-radius: 0.75rem;
    }
    .scanner-container .text-emerald-600 { color: var(--color-success) !important; }
    .scanner-container .text-blue-600 { color: var(--color-primary) !important; }
    .scanner-container .bg-blue-50 { background-color: #eff6ff; }
    .scanner-container .text-blue-900 { color: #1e3a8a; }
    .scanner-container .text-blue-700 { color: #1d4ed8; }
    .scanner-container input[type="text"] {
        background-color: var(--surface-background);
        border-radius: var(--border-radius-md);
        transition: all 0.2s ease-in-out;
    }
    .scanner-container .border-gray-300 { border-color: var(--border-color); }
    .scanner-container input[type="text"]:focus { --tw-ring-opacity: 0.5; }
    .scanner-container .border-emerald-500 { --tw-border-opacity: 1; border-color: rgb(16 185 129 / var(--tw-border-opacity)); }
    .scanner-container .focus\:ring-emerald-200:focus { --tw-ring-color: #a7f3d0; }
    .scanner-container .border-red-500 { --tw-border-opacity: 1; border-color: rgb(239 68 68 / var(--tw-border-opacity)); }
    .scanner-container .focus\:ring-red-200:focus { --tw-ring-color: #fecaca; }
    .scanner-container button {
        border-radius: var(--border-radius-md);
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }
    .scanner-container button:hover { filter: brightness(1.1); }
    .scanner-container .bg-blue-600 { background-color: var(--color-primary); }
    .scanner-container .bg-emerald-600 { background-color: var(--color-success); }
    .scanner-container .bg-red-600 { background-color: var(--color-danger); }
    .result-modal-container { backdrop-filter: blur(4px); }
    .result-modal-container .bg-white {
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 1.5rem 2rem;
    }
    .result-modal-container .bg-emerald-100 { background-color: #d1fae5; }
    .result-modal-container .text-emerald-800 { color: #065f46; }
    .result-modal-container .bg-red-100 { background-color: #fee2e2; }
    .result-modal-container .text-red-800 { color: #991b1b; }
    .result-modal-container .bg-gray-50 {
        background-color: var(--surface-background);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
    }
    .result-modal-container .bg-gray-200 { background-color: #e2e8f0; color: #334155; }
    .result-modal-container .bg-gray-200:hover { background-color: #cbd5e1; }
    @keyframes laser-scan { 0% { top: 0; } 100% { top: 100%; } }
    .laser-line {
        position: absolute;
        left: 5%;
        right: 5%;
        height: 2px;
        background: var(--color-success);
        box-shadow: 0 0 10px var(--color-success);
        animation: laser-scan 2.5s infinite alternate ease-in-out;
    }
</style>

<div x-data="delivererQRScanner({
        mode: '{{ $mode }}',
        autoRedirect: {{ $autoRedirect ? 'true' : 'false' }},
        showRecent: {{ $showRecent ? 'true' : 'false' }}
    })"
    @open-scanner.window="openScanner($event.detail)"
    @scan-package.window="openScanner($event.detail)">

    <!-- Scanner Modal -->
    <div x-show="scannerVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center scanner-container">

        <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        <span x-show="scanMode === 'single'">Scanner le Colis</span>
                        <span x-show="scanMode === 'batch'">Scanner par Lot</span>
                    </h3>
                    <p class="text-sm text-gray-600" x-text="activeMode === 'camera' ? 'Mode caméra' : 'Saisie manuelle'"></p>
                </div>
                <button @click="closeScanner()"
                        class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mode Toggle -->
            <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
                <button @click="switchMode('camera')"
                        :class="activeMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                        :disabled="!isHttps && isMobile"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all disabled:opacity-50 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Caméra</span>
                </button>
                <button @click="switchMode('manual')"
                        :class="activeMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Manuel</span>
                </button>
            </div>

            <!-- Camera Mode -->
            <x-deliverer.scanner.camera-overlay />

            <!-- Manual Mode -->
            <x-deliverer.scanner.code-input />

            <!-- Recent Codes (if enabled) -->
            <div x-show="showRecent && recentCodes.length > 0 && activeMode === 'manual'" class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Codes récents</h4>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    <template x-for="item in recentCodes.slice(0, 5)" :key="item.value">
                        <button @click="useRecentCode(item.value)"
                                class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors flex justify-between items-center">
                            <span class="font-mono text-xs" x-text="item.value"></span>
                            <span class="text-xs text-gray-500" x-text="formatTime(item.timestamp)"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Batch Mode Results -->
            <div x-show="scanMode === 'batch' && scannedCodes.length > 0" class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-700">Codes scannés</h4>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full" x-text="scannedCodes.length"></span>
                </div>
                <div class="space-y-1 max-h-32 overflow-y-auto border rounded-lg p-2">
                    <template x-for="(code, index) in scannedCodes" :key="index">
                        <div class="flex items-center justify-between text-xs bg-gray-50 px-2 py-1 rounded">
                            <span class="font-mono" x-text="code"></span>
                            <button @click="removeBatchCode(index)" class="text-red-500 hover:text-red-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <div class="flex space-x-2 mt-2">
                    <button @click="processBatchCodes()"
                            :disabled="scannedCodes.length === 0 || processing"
                            class="flex-1 bg-emerald-600 text-white py-2 px-3 rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                        <span x-show="!processing">Traiter (<span x-text="scannedCodes.length"></span>)</span>
                        <span x-show="processing">Traitement...</span>
                    </button>
                    <button @click="clearBatchCodes()"
                            class="bg-gray-500 text-white py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-600">
                        Vider
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div x-show="resultVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4 result-modal-container">

        <div class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4"
                     :class="result.success ? 'bg-emerald-100' : 'bg-red-100'">
                    <span class="text-3xl" x-text="result.success ? '✅' : '❌'"></span>
                </div>

                <h3 class="text-xl font-bold mb-2"
                    :class="result.success ? 'text-emerald-800' : 'text-red-800'"
                    x-text="result.success ? 'Colis Trouvé' : 'Erreur'"></h3>

                <p class="text-gray-700 mb-4 text-sm" x-text="result.message"></p>

                <!-- Package Details -->
                <div x-show="result.package" class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-4">
                    <div class="pb-3 border-b border-gray-200">
                        <p class="text-sm text-gray-500">Code Colis</p>
                        <p class="font-mono text-lg text-blue-600 font-bold" x-text="result.package?.code"></p>
                    </div>
                    <dl class="text-sm space-y-3">
                        <div x-show="result.delivery_info?.name">
                            <dt class="font-semibold text-gray-600">Destinataire</dt>
                            <dd class="text-gray-900" x-text="result.delivery_info?.name"></dd>
                        </div>
                        <div x-show="result.delivery_info?.address">
                            <dt class="font-semibold text-gray-600">Adresse</dt>
                            <dd class="text-gray-900" x-text="result.delivery_info?.address"></dd>
                        </div>
                        <div x-show="result.package?.cod_amount > 0">
                            <dt class="font-semibold text-gray-600">Montant à encaisser</dt>
                            <dd class="text-emerald-600 font-bold text-lg" x-text="result.package?.formatted_cod"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="flex space-x-3">
                    <button @click="closeResult()"
                            class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">
                        Fermer
                    </button>
                    <button x-show="result.success && result.redirect && autoRedirect"
                            @click="goToPackage()"
                            class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700"
                            x-text="getActionLabel()">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function delivererQRScanner(options = {}) {
    return {
        // Configuration
        scanMode: options.mode || 'single',
        autoRedirect: options.autoRedirect !== false,
        showRecent: options.showRecent !== false,

        // UI State
        scannerVisible: false,
        activeMode: 'manual',
        resultVisible: false,

        // Camera State
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        isHttps: location.protocol === 'https:',
        cameraActive: false,
        cameraErrorMsg: '',
        videoStream: null,
        scanInterval: null,
        lastDetection: null,
        permissionAsked: false,
        scanCycle: 0,
        currentScanMode: 'barcode',

        // Manual Input
        manualCode: '',
        codeValid: false,
        searching: false,
        processing: false,

        // Data
        recentCodes: [],
        scannedCodes: [],
        result: {},
        scanHistory: [],

        init() {
            this.loadStoredData();
            if (this.isMobile && !this.isHttps) {
                this.activeMode = 'manual';
            }
            this.$watch('manualCode', () => this.validateCode());
        },

        // Scanner Management
        openScanner(params = {}) {
            this.scanMode = params.mode || this.scanMode;
            this.scannerVisible = true;
            this.resetScanner();

            if (this.activeMode === 'manual') {
                setTimeout(() => {
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                }, 100);
            }
        },

        closeScanner() {
            this.stopCamera();
            this.scannerVisible = false;
            this.resetScanner();
        },

        resetScanner() {
            this.manualCode = '';
            this.codeValid = false;
            this.searching = false;
            this.processing = false;
            this.cameraErrorMsg = '';
            this.resultVisible = false;
            this.permissionAsked = false;

            if (this.scanMode === 'single') {
                this.scannedCodes = [];
            }
        },

        switchMode(mode) {
            if (mode === 'camera' && this.isMobile && !this.isHttps) {
                return;
            }

            this.activeMode = mode;

            if (mode === 'manual') {
                this.stopCamera();
                setTimeout(() => {
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                }, 100);
            }
        },

        // Camera Functions (imported from original)
        async requestCameraPermission() {
            this.permissionAsked = true;
            this.cameraErrorMsg = '';

            if (this.isMobile && !this.isHttps) {
                this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile.';
                return;
            }

            try {
                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error('getUserMedia non supporté');
                }
                await this.startCamera();
            } catch (error) {
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
            }
        },

        async startCamera() {
            this.cameraErrorMsg = '';

            try {
                this.stopCamera();

                const constraints = {
                    video: {
                        width: { min: 640, ideal: this.isMobile ? 1280 : 1920 },
                        height: { min: 480, ideal: this.isMobile ? 720 : 1080 },
                        frameRate: { min: 15, ideal: 30 }
                    }
                };

                if (this.isMobile) {
                    constraints.video.facingMode = { exact: "environment" };
                }

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = this.$refs.videoElement;

                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }

                video.srcObject = this.videoStream;

                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => resolve();
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000);
                });

                this.cameraActive = true;
                this.startScanning();

            } catch (error) {
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
                this.stopCamera();
            }
        },

        stopCamera() {
            this.stopScanning();

            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }

            if (this.$refs.videoElement) {
                this.$refs.videoElement.srcObject = null;
            }

            this.cameraActive = false;
        },

        retryCamera() {
            this.cameraErrorMsg = '';
            this.requestCameraPermission();
        },

        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();

            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Autorisez la caméra dans les paramètres.';
            }
            if (msg.includes('NotFoundError')) {
                return 'Aucune caméra trouvée.';
            }
            if (msg.includes('NotReadableError')) {
                return 'Caméra déjà utilisée.';
            }

            return 'Erreur caméra. Réessayez ou utilisez le mode manuel.';
        },

        // Scanning Functions (imported from original)
        startScanning() {
            this.initQuaggaScanner();
            this.startAlternatingScans();
        },

        startAlternatingScans() {
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                if (this.scanCycle % 2 === 0) {
                    this.currentScanMode = 'barcode';
                } else {
                    this.currentScanMode = 'qr';
                    this.analyzeQRFrame();
                }
            }, 750);
        },

        stopScanning() {
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
            this.stopQuaggaScanner();
            this.scanCycle = 0;
            this.currentScanMode = 'barcode';
        },

        initQuaggaScanner() {
            if (typeof Quagga === 'undefined') return;

            try {
                const video = this.$refs.videoElement;
                if (!video) return;

                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: video,
                        constraints: {
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                            facingMode: this.isMobile ? "environment" : "user"
                        }
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "code_39_reader"]
                    },
                    locate: true,
                    debug: false
                }, (err) => {
                    if (err) return;
                    Quagga.start();
                });

                Quagga.onDetected((result) => {
                    if (result?.codeResult?.code && this.currentScanMode === 'barcode') {
                        const code = result.codeResult.code.trim();
                        if (this.isValidPackageCode(code)) {
                            this.onCodeDetected(code, 'BARCODE');
                        }
                    }
                });

            } catch (error) {
                console.error('Erreur Quagga:', error);
            }
        },

        stopQuaggaScanner() {
            try {
                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                }
            } catch (error) {
                console.error('Erreur arrêt Quagga:', error);
            }
        },

        analyzeQRFrame() {
            try {
                const video = this.$refs.videoElement;
                const canvas = this.$refs.canvasElement;

                if (!video || !canvas || !video.videoWidth) return;

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);

                if (typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert"
                    });

                    if (qrResult?.data) {
                        const code = qrResult.data.trim();
                        if (this.isValidCode(code)) {
                            this.onCodeDetected(code, 'QR');
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur analyse QR:', error);
            }
        },

        onCodeDetected(code, type) {
            const now = Date.now();

            if (this.lastDetection &&
                (now - this.lastDetection.time < 2000) &&
                this.lastDetection.code === code) {
                return;
            }

            this.lastDetection = { code, time: now, type };

            if (navigator.vibrate) {
                navigator.vibrate(100);
            }

            if (this.scanMode === 'batch') {
                this.addToBatch(code);
            } else {
                this.stopCamera();
                this.processCode(code);
            }
        },

        // Code Validation (imported from original)
        isValidPackageCode(code) {
            if (!code || code.length < 6) return false;

            const cleanCode = code.trim().toUpperCase();

            if (/^https?:\/\/.*\/track\//.test(cleanCode)) return true;

            const obviousWords = ['LIVRAISON', 'DELIVERY', 'BON', 'ALAMENA', 'SERVICE', 'CONTACT', 'TELEPHONE', 'ADRESSE', 'CLIENT', 'DATE'];
            if (obviousWords.some(word => cleanCode.includes(word) && word.length > 4)) return false;

            return true;
        },

        isValidCode(code) {
            return this.isValidPackageCode(code);
        },

        validateCode() {
            this.codeValid = this.isValidPackageCode(this.manualCode);
        },

        // Manual Input
        searchCode() {
            if (!this.codeValid || this.searching) return;
            this.processCode(this.manualCode.trim().toUpperCase());
        },

        useRecentCode(code) {
            this.manualCode = code;
            this.validateCode();
            this.processCode(code);
        },

        // Batch Mode
        addToBatch(code) {
            if (!this.scannedCodes.includes(code)) {
                this.scannedCodes.push(code);
                this.addToRecent(code);
            }
        },

        removeBatchCode(index) {
            this.scannedCodes.splice(index, 1);
        },

        clearBatchCodes() {
            this.scannedCodes = [];
        },

        async processBatchCodes() {
            if (this.scannedCodes.length === 0 || this.processing) return;

            this.processing = true;

            try {
                const response = await fetch('/deliverer/packages/scan-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ codes: this.scannedCodes })
                });

                const data = await response.json();
                this.showResult(data);

                if (data.success) {
                    this.clearBatchCodes();
                }

            } catch (error) {
                this.showResult({
                    success: false,
                    message: 'Erreur lors du traitement par lot.'
                });
            }

            this.processing = false;
        },

        // Code Processing (imported from original)
        async processCode(code) {
            this.searching = true;

            try {
                this.addToRecent(code);

                const response = await fetch('/deliverer/packages/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();
                this.showResult(data);

            } catch (error) {
                this.showResult({
                    success: false,
                    message: 'Erreur de connexion.'
                });
            }

            this.searching = false;
        },

        // Result Handling
        showResult(data) {
            this.result = data;
            this.resultVisible = true;

            if (data.success && data.redirect && this.autoRedirect) {
                setTimeout(() => {
                    this.goToPackage();
                }, 5000);
            }
        },

        closeResult() {
            this.resultVisible = false;
        },

        goToPackage() {
            if (this.result.redirect) {
                this.closeScanner();
                window.location.href = this.result.redirect;
            }
        },

        getActionLabel() {
            const action = this.result.action;
            switch (action) {
                case 'accept': return 'Accepter';
                case 'pickup': return 'Collecter';
                case 'deliver': return 'Livrer';
                case 'return': return 'Retourner';
                default: return 'Voir';
            }
        },

        // Storage
        loadStoredData() {
            try {
                this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
                this.scanHistory = [];
            } catch {
                this.recentCodes = [];
            }
        },

        addToRecent(code) {
            const item = { value: code, timestamp: Date.now() };
            this.recentCodes = [
                item,
                ...this.recentCodes.filter(c => c.value !== code)
            ].slice(0, 10);

            try {
                localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
            } catch (error) {
                console.error('Erreur sauvegarde localStorage:', error);
            }
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush