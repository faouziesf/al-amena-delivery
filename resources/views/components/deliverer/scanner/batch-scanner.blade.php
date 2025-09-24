@props([
    'maxItems' => 50,
    'allowDuplicates' => false,
    'autoProcess' => false
])

<div x-data="batchScanner({
        maxItems: {{ $maxItems }},
        allowDuplicates: {{ $allowDuplicates ? 'true' : 'false' }},
        autoProcess: {{ $autoProcess ? 'true' : 'false' }}
    })"
    @open-batch-scanner.window="openBatchScanner($event.detail)"
    class="batch-scanner-component">

    <!-- Batch Scanner Modal -->
    <div x-show="visible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">

        <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-2xl max-h-screen overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Scanner par Lot</h3>
                    <p class="text-sm text-gray-600">
                        Scanner plusieurs colis rapidement
                        <span x-show="maxItems > 0">(max {{ $maxItems }})</span>
                    </p>
                </div>
                <button @click="closeBatchScanner()"
                        class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="scannedCodes.length"></div>
                    <div class="text-sm text-blue-700">Scannés</div>
                </div>
                <div class="bg-emerald-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-emerald-600" x-text="validCodes.length"></div>
                    <div class="text-sm text-emerald-700">Valides</div>
                </div>
                <div class="bg-red-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-red-600" x-text="invalidCodes.length"></div>
                    <div class="text-sm text-red-700">Invalides</div>
                </div>
            </div>

            <!-- Scanner Mode Toggle -->
            <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
                <button @click="setScannerMode('camera')"
                        :class="scannerMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                        :disabled="!isHttps && isMobile"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all disabled:opacity-50">
                    <span>Caméra Continue</span>
                </button>
                <button @click="setScannerMode('manual')"
                        :class="scannerMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all">
                    <span>Saisie Multiple</span>
                </button>
            </div>

            <!-- Camera Mode -->
            <div x-show="scannerMode === 'camera'" class="space-y-4">
                <!-- Camera Container -->
                <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 4/3;">
                    <video x-ref="batchVideoElement"
                           class="w-full h-full object-cover"
                           autoplay playsinline muted
                           x-show="cameraActive && !cameraError"></video>
                    <canvas x-ref="batchCanvasElement" class="hidden"></canvas>

                    <!-- Camera Status Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div x-show="!cameraActive && !cameraError" class="text-white text-center">
                            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <p>Démarrage caméra...</p>
                        </div>

                        <div x-show="cameraError" class="bg-red-500 text-white p-4 rounded-xl text-center max-w-xs mx-4">
                            <p class="text-sm mb-3 font-medium">Erreur Caméra</p>
                            <p class="text-xs mb-3" x-text="cameraError"></p>
                            <button @click="startBatchCamera()"
                                    class="w-full bg-white text-red-500 px-3 py-2 rounded text-sm font-medium">
                                Réessayer
                            </button>
                        </div>
                    </div>

                    <!-- Scanning Overlay -->
                    <div x-show="cameraActive && isScanning" class="absolute inset-0 pointer-events-none">
                        <div class="w-full h-full relative bg-black bg-opacity-40">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="border-2 border-emerald-400 rounded-lg" style="width: 80%; height: 60%;">
                                    <div class="laser-line"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Scanned Indicator -->
                    <div x-show="lastScannedCode" class="absolute top-4 left-4 right-4">
                        <div class="bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="lastScannedCode"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls -->
                <div class="flex justify-center space-x-3">
                    <button @click="startBatchCamera()"
                            x-show="!cameraActive"
                            class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-emerald-700">
                        Démarrer Caméra
                    </button>

                    <button @click="toggleScanning()"
                            x-show="cameraActive"
                            :class="isScanning ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="text-white px-6 py-2 rounded-xl font-semibold">
                        <span x-show="isScanning">Arrêter Scanner</span>
                        <span x-show="!isScanning">Commencer Scanner</span>
                    </button>

                    <button @click="stopBatchCamera()"
                            x-show="cameraActive"
                            class="bg-gray-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-gray-700">
                        Arrêter Caméra
                    </button>
                </div>
            </div>

            <!-- Manual Mode -->
            <div x-show="scannerMode === 'manual'" class="space-y-4">
                <!-- Bulk Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Saisir plusieurs codes (un par ligne)
                    </label>
                    <textarea x-model="bulkInput"
                              @input="processBulkInput()"
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                              placeholder="PKG_12345678&#10;PKG_87654321&#10;..."></textarea>

                    <div class="mt-2 text-sm text-gray-600">
                        <span x-text="bulkInput.split('\n').filter(line => line.trim()).length"></span> lignes saisies
                    </div>
                </div>

                <!-- Quick Add Single Code -->
                <div class="flex space-x-2">
                    <input type="text"
                           x-model="quickCode"
                           @keydown.enter="addQuickCode()"
                           placeholder="Code rapide..."
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                    <button @click="addQuickCode()"
                            :disabled="!quickCode.trim()"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Ajouter
                    </button>
                </div>
            </div>

            <!-- Scanned Codes List -->
            <div class="flex-1 min-h-0 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-gray-900">Codes Collectés</h4>
                    <div class="flex space-x-2">
                        <button @click="exportCodes()"
                                :disabled="scannedCodes.length === 0"
                                class="text-sm bg-gray-100 text-gray-700 px-3 py-1 rounded-lg hover:bg-gray-200 disabled:opacity-50">
                            Exporter
                        </button>
                        <button @click="clearAllCodes()"
                                :disabled="scannedCodes.length === 0"
                                class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded-lg hover:bg-red-200 disabled:opacity-50">
                            Vider
                        </button>
                    </div>
                </div>

                <div class="border rounded-lg max-h-48 overflow-y-auto">
                    <template x-for="(item, index) in scannedCodes" :key="index">
                        <div class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50">
                            <div class="flex items-center space-x-3">
                                <div :class="item.valid ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                     class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                                    <span x-show="item.valid">✓</span>
                                    <span x-show="!item.valid">✗</span>
                                </div>
                                <div>
                                    <div class="font-mono text-sm" x-text="item.code"></div>
                                    <div class="text-xs text-gray-500" x-text="new Date(item.timestamp).toLocaleTimeString()"></div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <span x-show="item.method"
                                      :class="item.method === 'camera' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                                      class="px-2 py-1 rounded text-xs font-medium"
                                      x-text="item.method === 'camera' ? 'CAM' : 'MAN'">
                                </span>

                                <button @click="removeCode(index)"
                                        class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="scannedCodes.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p>Aucun code scanné</p>
                        <p class="text-sm">Utilisez la caméra ou la saisie manuelle</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3 pt-4 border-t">
                <button @click="processBatch()"
                        :disabled="validCodes.length === 0 || processing"
                        class="flex-1 bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 disabled:opacity-50">
                    <span x-show="!processing">
                        Traiter le Lot (<span x-text="validCodes.length"></span>)
                    </span>
                    <span x-show="processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Traitement...
                    </span>
                </button>

                <button @click="closeBatchScanner()"
                        class="bg-gray-500 text-white py-3 px-4 rounded-xl font-semibold hover:bg-gray-600">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function batchScanner(options = {}) {
    return {
        // Configuration
        maxItems: options.maxItems || 50,
        allowDuplicates: options.allowDuplicates || false,
        autoProcess: options.autoProcess || false,

        // UI State
        visible: false,
        scannerMode: 'camera', // camera, manual
        processing: false,

        // Camera State
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        isHttps: location.protocol === 'https:',
        cameraActive: false,
        cameraError: '',
        videoStream: null,
        isScanning: false,
        scanInterval: null,
        lastScannedCode: '',

        // Data
        scannedCodes: [],
        bulkInput: '',
        quickCode: '',

        // Computed properties
        get validCodes() {
            return this.scannedCodes.filter(item => item.valid);
        },

        get invalidCodes() {
            return this.scannedCodes.filter(item => !item.valid);
        },

        init() {
            // Load saved batch if any
            this.loadBatchData();
        },

        // Modal Management
        openBatchScanner(params = {}) {
            this.visible = true;
            this.scannerMode = params.mode || 'camera';
            this.resetBatchScanner();

            if (this.scannerMode === 'camera') {
                setTimeout(() => this.startBatchCamera(), 100);
            }
        },

        closeBatchScanner() {
            this.stopBatchCamera();
            this.visible = false;
            this.saveBatchData();
        },

        resetBatchScanner() {
            this.bulkInput = '';
            this.quickCode = '';
            this.lastScannedCode = '';
            this.processing = false;

            if (!this.allowDuplicates) {
                // Keep existing codes
            } else {
                this.scannedCodes = [];
            }
        },

        setScannerMode(mode) {
            if (mode === 'camera' && this.isMobile && !this.isHttps) {
                return;
            }

            this.scannerMode = mode;

            if (mode === 'manual') {
                this.stopBatchCamera();
            }
        },

        // Camera Functions
        async startBatchCamera() {
            this.cameraError = '';

            if (this.isMobile && !this.isHttps) {
                this.cameraError = 'HTTPS requis pour la caméra sur mobile.';
                return;
            }

            try {
                this.stopBatchCamera();

                const constraints = {
                    video: {
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 },
                        frameRate: { min: 15, ideal: 30 }
                    }
                };

                if (this.isMobile) {
                    constraints.video.facingMode = { exact: "environment" };
                }

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = this.$refs.batchVideoElement;

                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }

                video.srcObject = this.videoStream;

                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => resolve();
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout')), 10000);
                });

                this.cameraActive = true;
                this.isScanning = true;
                this.startBatchScanning();

            } catch (error) {
                this.cameraError = this.getCameraErrorMessage(error);
                this.stopBatchCamera();
            }
        },

        stopBatchCamera() {
            this.stopBatchScanning();

            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }

            if (this.$refs.batchVideoElement) {
                this.$refs.batchVideoElement.srcObject = null;
            }

            this.cameraActive = false;
            this.isScanning = false;
        },

        toggleScanning() {
            if (this.isScanning) {
                this.stopBatchScanning();
            } else {
                this.startBatchScanning();
            }
        },

        startBatchScanning() {
            if (!this.cameraActive) return;

            this.isScanning = true;
            this.initBatchQuagga();

            // Start continuous QR scanning
            this.scanInterval = setInterval(() => {
                this.analyzeBatchQRFrame();
            }, 1000);
        },

        stopBatchScanning() {
            this.isScanning = false;

            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }

            this.stopBatchQuagga();
        },

        initBatchQuagga() {
            if (typeof Quagga === 'undefined') return;

            try {
                const video = this.$refs.batchVideoElement;
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
                    if (result?.codeResult?.code) {
                        const code = result.codeResult.code.trim();
                        this.addCodeToBatch(code, 'camera', 'BARCODE');
                    }
                });

            } catch (error) {
                console.error('Erreur Quagga batch:', error);
            }
        },

        stopBatchQuagga() {
            try {
                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                }
            } catch (error) {
                console.error('Erreur arrêt Quagga batch:', error);
            }
        },

        analyzeBatchQRFrame() {
            try {
                const video = this.$refs.batchVideoElement;
                const canvas = this.$refs.batchCanvasElement;

                if (!video || !canvas || !video.videoWidth) return;

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);

                if (typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height);

                    if (qrResult?.data) {
                        const code = qrResult.data.trim();
                        this.addCodeToBatch(code, 'camera', 'QR');
                    }
                }
            } catch (error) {
                console.error('Erreur analyse QR batch:', error);
            }
        },

        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();

            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Autorisez la caméra.';
            }
            if (msg.includes('NotFoundError')) {
                return 'Aucune caméra trouvée.';
            }
            if (msg.includes('NotReadableError')) {
                return 'Caméra déjà utilisée.';
            }

            return 'Erreur caméra.';
        },

        // Code Management
        addCodeToBatch(code, method = 'manual', type = 'MANUAL') {
            const cleanCode = this.normalizePackageCode(code);

            // Check if code already exists (if duplicates not allowed)
            if (!this.allowDuplicates && this.scannedCodes.some(item => item.code === cleanCode)) {
                return;
            }

            // Check max items
            if (this.maxItems > 0 && this.scannedCodes.length >= this.maxItems) {
                return;
            }

            // Validate code
            const isValid = this.isValidPackageCode(code);

            const item = {
                code: cleanCode,
                valid: isValid,
                method: method,
                type: type,
                timestamp: Date.now()
            };

            this.scannedCodes.push(item);
            this.lastScannedCode = cleanCode;

            // Visual feedback
            if (navigator.vibrate) {
                navigator.vibrate(isValid ? 100 : [100, 100, 100]);
            }

            // Auto-hide last scanned after 3 seconds
            setTimeout(() => {
                if (this.lastScannedCode === cleanCode) {
                    this.lastScannedCode = '';
                }
            }, 3000);

            // Auto process if enabled
            if (this.autoProcess && isValid) {
                setTimeout(() => this.processBatch(), 1000);
            }
        },

        removeCode(index) {
            this.scannedCodes.splice(index, 1);
        },

        clearAllCodes() {
            this.scannedCodes = [];
            this.lastScannedCode = '';
        },

        // Manual Input
        processBulkInput() {
            const lines = this.bulkInput.split('\n')
                .map(line => line.trim())
                .filter(line => line.length > 0);

            // Clear existing manual codes if replacing
            this.scannedCodes = this.scannedCodes.filter(item => item.method !== 'manual');

            lines.forEach(code => {
                this.addCodeToBatch(code, 'manual', 'MANUAL');
            });
        },

        addQuickCode() {
            if (!this.quickCode.trim()) return;

            this.addCodeToBatch(this.quickCode.trim(), 'manual', 'MANUAL');
            this.quickCode = '';
        },

        // 🚀 LOGIQUE ULTRA SIMPLE - ACCEPTE TOUT
        normalizePackageCode(input) {
            return window.extractCodeFromUrl ? window.extractCodeFromUrl(input) : (input || '').toString().trim().toUpperCase();
        },

        isValidPackageCode(input) {
            return window.isValidPackageCode ? window.isValidPackageCode(input) : true;
        },

        // Export
        exportCodes() {
            const codes = this.validCodes.map(item => item.code);
            const text = codes.join('\n');

            // Create download
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `batch_codes_${new Date().toISOString().split('T')[0]}.txt`;
            a.click();
            URL.revokeObjectURL(url);
        },

        // Processing
        async processBatch() {
            if (this.validCodes.length === 0 || this.processing) return;

            this.processing = true;

            try {
                const codes = this.validCodes.map(item => item.code);

                const response = await fetch('/deliverer/packages/scan-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ codes: codes })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success notification
                    this.showNotification('Lot traité avec succès!', 'success');

                    // Clear processed codes
                    this.scannedCodes = [];

                    // Redirect if needed
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    }
                } else {
                    this.showNotification(data.message || 'Erreur lors du traitement', 'error');
                }

            } catch (error) {
                console.error('Erreur traitement batch:', error);
                this.showNotification('Erreur de connexion', 'error');
            }

            this.processing = false;
        },

        showNotification(message, type = 'success') {
            // Emit custom event for notifications
            this.$dispatch('show-notification', { message, type });
        },

        // Storage
        saveBatchData() {
            try {
                const data = {
                    codes: this.scannedCodes,
                    timestamp: Date.now()
                };
                localStorage.setItem('batch_scanner_data', JSON.stringify(data));
            } catch (error) {
                console.error('Erreur sauvegarde batch:', error);
            }
        },

        loadBatchData() {
            try {
                const data = JSON.parse(localStorage.getItem('batch_scanner_data') || '{}');

                // Load codes if less than 1 hour old
                if (data.codes && data.timestamp && (Date.now() - data.timestamp < 3600000)) {
                    this.scannedCodes = data.codes;
                }
            } catch (error) {
                console.error('Erreur chargement batch:', error);
            }
        }
    }
}
</script>
@endpush