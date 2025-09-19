<!-- Scanner QR/Code-barres Modal Component - FONCTIONNEL -->
<div x-show="scannerOpen" x-transition class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center">
    <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Scanner QR/Code</h3>
                <p class="text-sm text-gray-600" x-text="scannerMode === 'camera' ? 'Positionnez le code dans le cadre' : 'Saisissez manuellement'"></p>
            </div>
            <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Scanner Tabs -->
        <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
            <button @click="switchMode('camera')" 
                    :class="scannerMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Caméra
            </button>
            <button @click="switchMode('manual')" 
                    :class="scannerMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Manuel
            </button>
        </div>

        <!-- Camera Scanner -->
        <div x-show="scannerMode === 'camera'" class="space-y-4">
            <!-- Video Container -->
            <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 1;">
                <video x-ref="videoElement" 
                       class="w-full h-full object-cover" 
                       autoplay 
                       playsinline 
                       muted></video>
                
                <!-- Scan Overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative">
                        <!-- Scan Frame -->
                        <div class="w-56 h-56 border-4 border-emerald-500 rounded-2xl relative">
                            <!-- Corner Indicators -->
                            <div class="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                            <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                            
                            <!-- Scanning Animation -->
                            <div x-show="isScanning" class="absolute inset-0 flex items-center justify-center">
                                <div class="w-full h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-pulse"></div>
                            </div>
                        </div>
                        
                        <!-- Scan Status -->
                        <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2 w-64">
                            <div class="bg-black bg-opacity-75 text-white px-4 py-2 rounded-xl text-center">
                                <p class="text-sm" x-show="!cameraError && !isScanning">Positionnez le QR code dans le cadre</p>
                                <p class="text-sm text-emerald-400" x-show="isScanning">
                                    <svg class="w-4 h-4 inline animate-spin mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Scan en cours...
                                </p>
                                <p class="text-sm text-red-400" x-show="cameraError" x-text="cameraError"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Controls Overlay -->
                <div class="absolute top-4 right-4">
                    <button @click="switchCamera()" 
                            x-show="cameraActive && cameras.length > 1"
                            class="bg-black bg-opacity-50 text-white p-2 rounded-lg hover:bg-opacity-70 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Camera Controls -->
            <div class="flex justify-center space-x-3">
                <button @click="startCamera()" 
                        x-show="!cameraActive"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Activer Caméra
                </button>
                <button @click="stopCamera()" 
                        x-show="cameraActive"
                        class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l6 6m0-6l-6 6"/>
                    </svg>
                    Arrêter
                </button>
            </div>
        </div>

        <!-- Manual Entry -->
        <div x-show="scannerMode === 'manual'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
                <input type="text" 
                       x-model="manualCode" 
                       @keydown.enter="processManualCode()"
                       @input="validateCode()"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-lg font-mono uppercase"
                       placeholder="PKG_123456 ou scan..."
                       autofocus>
                <p class="text-xs text-gray-500 mt-2">Format: PKG_XXXXXX, REF_XXXXXX ou code-barres</p>
            </div>
            
            <!-- Code Validation -->
            <div x-show="manualCode" class="text-sm">
                <div x-show="isValidCode" class="text-emerald-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Format valide
                </div>
                <div x-show="!isValidCode && manualCode" class="text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Format non reconnu
                </div>
            </div>
            
            <!-- Recent Codes -->
            <div x-show="recentCodes.length > 0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Codes récents</label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="code in recentCodes.slice(0, 5)" :key="code.value">
                        <button @click="manualCode = code.value; processManualCode()" 
                                class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm" x-text="code.value"></span>
                                <span class="text-xs text-gray-500" x-text="formatTime(code.timestamp)"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            <button @click="processManualCode()" 
                    :disabled="!isValidCode || processingCode"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!processingCode" class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </span>
                <span x-show="processingCode" class="flex items-center justify-center">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    Recherche...
                </span>
            </button>
        </div>

        <!-- Scan History -->
        <div x-show="scanHistory.length > 0" class="mt-6 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Historique des scans</h4>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                <template x-for="(scan, index) in scanHistory.slice(0, 3)" :key="index">
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium" x-text="scan.code"></p>
                            <p class="text-xs text-gray-500" x-text="scan.result"></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-400" x-text="formatTime(scan.timestamp)"></span>
                            <span :class="scan.success ? 'text-emerald-500' : 'text-red-500'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          :d="scan.success ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Résultat du scan Modal -->
<div x-show="showResultModal" x-transition 
     class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4">
    <div x-show="showResultModal" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-95 opacity-0"
         class="bg-white rounded-2xl p-6 w-full max-w-sm">
        
        <div class="text-center">
            <!-- Success/Error Icon -->
            <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 :class="scanResult.success ? 'bg-emerald-100' : 'bg-red-100'">
                <svg class="w-8 h-8" :class="scanResult.success ? 'text-emerald-600' : 'text-red-600'" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          :d="scanResult.success ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-bold mb-2" 
                :class="scanResult.success ? 'text-emerald-800' : 'text-red-800'" 
                x-text="scanResult.title"></h3>
            
            <!-- Message -->
            <p class="text-gray-600 mb-4" x-text="scanResult.message"></p>
            
            <!-- Package Info -->
            <div x-show="scanResult.package" class="bg-gray-50 rounded-xl p-4 mb-6">
                <div class="text-left space-y-2">
                    <p class="font-mono text-sm text-blue-600" x-text="scanResult.package?.code"></p>
                    <p class="font-medium" x-text="scanResult.package?.recipient_name"></p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">COD:</span>
                        <span class="font-bold text-emerald-600" x-text="formatAmount(scanResult.package?.cod_amount)"></span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button @click="showResultModal = false" 
                        class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Fermer
                </button>
                <button x-show="scanResult.action && scanResult.success" 
                        @click="executeAction()" 
                        class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors"
                        x-text="getActionButtonText()">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global Scanner JavaScript
function createScannerApp() {
    return {
        scannerOpen: false,
        scannerMode: 'camera',
        
        // Camera
        cameraActive: false,
        isScanning: false,
        cameraError: null,
        videoStream: null,
        qrScanner: null,
        cameras: [],
        currentCameraIndex: 0,
        
        // Manual
        manualCode: '',
        isValidCode: false,
        processingCode: false,
        recentCodes: JSON.parse(localStorage.getItem('deliverer_recent_codes') || '[]'),
        
        // Results
        scanResult: {},
        showResultModal: false,
        scanHistory: JSON.parse(localStorage.getItem('deliverer_scan_history') || '[]'),

        init() {
            // Charger les caméras disponibles
            this.loadCameras();
            
            // Écouter l'ouverture du scanner
            this.$watch('scannerOpen', (isOpen) => {
                if (isOpen) {
                    this.resetScanner();
                    if (this.scannerMode === 'camera') {
                        setTimeout(() => this.startCamera(), 300);
                    }
                } else {
                    this.stopCamera();
                }
            });

            // Validation en temps réel du code manuel
            this.$watch('manualCode', (code) => {
                this.validateCode();
            });
        },

        // ==================== CAMERA METHODS ====================
        
        async loadCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.cameras = devices.filter(device => device.kind === 'videoinput');
            } catch (error) {
                console.error('Erreur chargement caméras:', error);
            }
        },

        async startCamera() {
            try {
                this.cameraError = null;
                this.isScanning = true;
                
                if (this.cameras.length === 0) {
                    await this.loadCameras();
                }
                
                if (this.cameras.length === 0) {
                    throw new Error('Aucune caméra disponible');
                }

                const constraints = {
                    video: {
                        deviceId: this.cameras[this.currentCameraIndex]?.deviceId,
                        facingMode: 'environment',
                        width: { ideal: 640 },
                        height: { ideal: 640 }
                    }
                };

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$refs.videoElement.srcObject = this.videoStream;
                this.cameraActive = true;

                // Démarrer le scan QR
                this.initQRScanner();

            } catch (error) {
                console.error('Erreur caméra:', error);
                this.cameraError = this.formatCameraError(error);
                this.isScanning = false;
            }
        },

        stopCamera() {
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            
            if (this.qrScanner) {
                clearInterval(this.qrScanner);
                this.qrScanner = null;
            }
            
            this.cameraActive = false;
            this.isScanning = false;
            this.cameraError = null;
        },

        switchCamera() {
            if (this.cameras.length > 1) {
                this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
                this.stopCamera();
                setTimeout(() => this.startCamera(), 100);
            }
        },

        initQRScanner() {
            const video = this.$refs.videoElement;
            
            const scan = () => {
                if (!this.cameraActive || !video.videoWidth || video.paused) {
                    return;
                }

                try {
                    // Créer canvas pour analyser l'image
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;

                    // Dessiner l'image actuelle
                    ctx.drawImage(video, 0, 0);
                    
                    // Analyser avec jsQR
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    
                    if (typeof jsQR !== 'undefined') {
                        const code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "dontInvert",
                        });

                        if (code && code.data) {
                            this.handleScannedCode(code.data);
                            return; // Stop scanning after successful scan
                        }
                    }

                } catch (error) {
                    console.error('Erreur analyse image:', error);
                }
            };

            // Scanner toutes les 200ms
            this.qrScanner = setInterval(scan, 200);
        },

        // ==================== CODE PROCESSING ====================

        async handleScannedCode(code) {
            this.addToRecentCodes(code);
            this.stopCamera(); // Arrêter le scan après détection
            await this.processCode(code);
        },

        async processManualCode() {
            if (!this.manualCode.trim() || !this.isValidCode) return;
            await this.processCode(this.manualCode.trim().toUpperCase());
        },

        async processCode(code) {
            this.processingCode = true;
            
            try {
                const response = await fetch('/deliverer/packages/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();
                
                // Ajouter à l'historique
                this.addToScanHistory(code, data.message, data.success);
                
                // Afficher le résultat
                this.showScanResult({
                    success: data.success,
                    title: data.success ? 'Colis trouvé!' : 'Erreur',
                    message: data.message,
                    action: data.action,
                    redirect: data.redirect,
                    package: data.package
                });

            } catch (error) {
                console.error('Erreur traitement code:', error);
                this.addToScanHistory(code, 'Erreur de connexion', false);
                this.showScanResult({
                    success: false,
                    title: 'Erreur de connexion',
                    message: 'Impossible de traiter le code. Vérifiez votre connexion.'
                });
            }
            
            this.processingCode = false;
        },

        // ==================== UI METHODS ====================

        switchMode(mode) {
            this.scannerMode = mode;
            if (mode === 'camera') {
                setTimeout(() => this.startCamera(), 100);
            } else {
                this.stopCamera();
            }
        },

        closeScanner() {
            this.stopCamera();
            this.scannerOpen = false;
            this.resetScanner();
        },

        resetScanner() {
            this.manualCode = '';
            this.isValidCode = false;
            this.processingCode = false;
            this.showResultModal = false;
        },

        validateCode() {
            const code = this.manualCode.toUpperCase();
            // Valider les formats courants
            this.isValidCode = /^(PKG_|REF_|COL_|LIV_)[A-Z0-9]{6,}$/.test(code) || 
                              /^[0-9]{8,}$/.test(code) || // Code-barres numérique
                              /^[A-Z0-9]{8,}$/.test(code); // Code alphanumérique
        },

        showScanResult(result) {
            this.scanResult = result;
            this.showResultModal = true;
            
            // Auto-fermer et exécuter l'action si succès
            if (result.success && result.redirect) {
                setTimeout(() => {
                    this.showResultModal = false;
                    this.closeScanner();
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                }, 2500);
            }
        },

        executeAction() {
            if (this.scanResult.redirect) {
                window.location.href = this.scanResult.redirect;
            } else if (this.scanResult.action) {
                // Exécuter l'action spécifique
                this.handleAction(this.scanResult.action, this.scanResult.package);
            }
            this.showResultModal = false;
            this.closeScanner();
        },

        async handleAction(action, packageData) {
            switch(action) {
                case 'accept':
                    await this.acceptPackage(packageData.id);
                    break;
                case 'pickup':
                    window.location.href = `/deliverer/packages/${packageData.id}`;
                    break;
                case 'deliver':
                    window.location.href = `/deliverer/packages/${packageData.id}`;
                    break;
                default:
                    console.log('Action non gérée:', action);
            }
        },

        async acceptPackage(packageId) {
            try {
                const response = await fetch(`/deliverer/packages/${packageId}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1000);
                    }
                } else {
                    this.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Erreur acceptation:', error);
                this.showToast('Erreur de connexion', 'error');
            }
        },

        // ==================== UTILITY METHODS ====================

        addToRecentCodes(code) {
            const recent = {
                value: code,
                timestamp: Date.now()
            };
            
            this.recentCodes.unshift(recent);
            this.recentCodes = this.recentCodes.slice(0, 10);
            
            localStorage.setItem('deliverer_recent_codes', JSON.stringify(this.recentCodes));
        },

        addToScanHistory(code, result, success) {
            const historyItem = {
                code: code,
                result: result,
                success: success,
                timestamp: Date.now()
            };
            
            this.scanHistory.unshift(historyItem);
            this.scanHistory = this.scanHistory.slice(0, 20);
            
            localStorage.setItem('deliverer_scan_history', JSON.stringify(this.scanHistory));
        },

        formatCameraError(error) {
            if (error.name === 'NotAllowedError') {
                return 'Accès caméra refusé. Autorisez l\'accès dans les paramètres.';
            }
            if (error.name === 'NotFoundError') {
                return 'Aucune caméra trouvée sur cet appareil.';
            }
            if (error.name === 'NotReadableError') {
                return 'Caméra utilisée par une autre application.';
            }
            return `Erreur caméra: ${error.message}`;
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        getActionButtonText() {
            switch(this.scanResult.action) {
                case 'accept': return 'Accepter';
                case 'pickup': return 'Collecter';
                case 'deliver': return 'Livrer';
                case 'return': return 'Retourner';
                default: return 'Voir';
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-24 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}

// Fonction globale pour ouvrir le scanner
function openScanner() {
    window.dispatchEvent(new CustomEvent('open-scanner'));
}
</script>

<!-- Charger jsQR depuis CDN -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>