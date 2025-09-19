<!-- Scanner QR/Code-barres - SOLUTION DÉFINITIVE -->
<div x-show="scannerOpen" x-transition class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center">
    <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Scanner QR/Code</h3>
                <p class="text-sm text-gray-600" x-text="scannerMode === 'camera' ? 'Caméra active' : 'Saisie manuelle'"></p>
            </div>
            <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Mode Tabs -->
        <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
            <button @click="switchToCamera()" 
                    :class="scannerMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Caméra
            </button>
            <button @click="switchToManual()" 
                    :class="scannerMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002 2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Manuel
            </button>
        </div>

        <!-- Camera Mode -->
        <div x-show="scannerMode === 'camera'" class="space-y-4">
            <!-- Video Container -->
            <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 1;">
                <video x-ref="videoElement" 
                       class="w-full h-full object-cover" 
                       autoplay 
                       playsinline 
                       muted
                       x-show="cameraStarted && !cameraError"></video>
                
                <!-- Canvas pour capture -->
                <canvas x-ref="canvasElement" class="hidden"></canvas>
                
                <!-- Status Overlay -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div x-show="!cameraStarted && !cameraError" class="text-white text-center">
                        <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                        <p>Démarrage caméra...</p>
                    </div>
                    
                    <div x-show="cameraError" class="bg-red-500 text-white p-4 rounded-xl text-center max-w-xs">
                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm mb-2" x-text="cameraErrorMessage"></p>
                        <button @click="switchToManual()" class="bg-white text-red-500 px-3 py-1 rounded text-xs font-medium">
                            Mode Manuel
                        </button>
                    </div>
                </div>
                
                <!-- Scan Frame Overlay -->
                <div x-show="cameraStarted && !cameraError" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="relative">
                        <div class="w-64 h-64 border-4 border-emerald-500 rounded-2xl relative">
                            <!-- Corner Indicators -->
                            <div class="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                            <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                            
                            <!-- Scanning Line Animation -->
                            <div x-show="isScanning" class="absolute inset-0 flex items-center justify-center">
                                <div class="w-full h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Camera Controls -->
            <div class="flex justify-center space-x-3">
                <button @click="startCamera()" 
                        x-show="!cameraStarted"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Démarrer
                </button>
                
                <button @click="stopCamera()" 
                        x-show="cameraStarted"
                        class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l6 6m0-6l-6 6"/>
                    </svg>
                    Arrêter
                </button>
                
                <button @click="switchCamera()" 
                        x-show="cameraStarted && availableCameras.length > 1"
                        class="bg-blue-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                <!-- Capture Manual -->
                <button @click="captureAndAnalyze()" 
                        x-show="cameraStarted && !isScanning"
                        class="bg-purple-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>

            <!-- Status Info -->
            <div x-show="cameraStarted" class="text-center">
                <div class="bg-emerald-50 p-3 rounded-xl">
                    <p class="text-sm text-emerald-700">
                        <span x-show="!isScanning">Positionnez le QR dans le cadre vert</span>
                        <span x-show="isScanning" class="flex items-center justify-center">
                            <div class="w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mr-2"></div>
                            Analyse en cours...
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Manual Mode -->
        <div x-show="scannerMode === 'manual'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
                <input type="text" 
                       x-ref="manualInput"
                       x-model="manualCode" 
                       @keydown.enter="processManualCode()"
                       @input="validateManualCode()"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-lg font-mono uppercase"
                       placeholder="PKG_12345678_20251219"
                       autofocus>
                <div class="mt-2 text-sm">
                    <div x-show="manualCodeValid" class="text-emerald-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Format valide
                    </div>
                    <div x-show="manualCode && !manualCodeValid" class="text-red-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Format invalide
                    </div>
                </div>
            </div>
            
            <!-- Recent Codes -->
            <div x-show="recentCodes.length > 0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Codes récents</label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="code in recentCodes.slice(0, 5)" :key="code.value">
                        <button @click="selectRecentCode(code.value)" 
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
                    :disabled="!manualCodeValid || processingCode"
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
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Derniers scans</h4>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                <template x-for="(scan, index) in scanHistory.slice(0, 3)" :key="index">
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div class="flex-1">
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

<!-- Modal Résultat -->
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
            
            <!-- Title & Message -->
            <h3 class="text-lg font-bold mb-2" 
                :class="scanResult.success ? 'text-emerald-800' : 'text-red-800'" 
                x-text="scanResult.success ? 'Colis trouvé!' : 'Erreur'"></h3>
            
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
                <button @click="closeResultModal()" 
                        class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                    Fermer
                </button>
                <button x-show="scanResult.success && scanResult.redirect" 
                        @click="handleScanAction()" 
                        class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors"
                        x-text="getActionText()">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Scanner Définitif - FONCTIONNE SUR TOUS LES APPAREILS
function createScannerDefinitif() {
    return {
        // État principal
        scannerOpen: false,
        scannerMode: 'manual', // Démarrer en mode manuel par défaut
        
        // Caméra
        cameraStarted: false,
        cameraError: false,
        cameraErrorMessage: '',
        videoStream: null,
        availableCameras: [],
        currentCameraIndex: 0,
        isScanning: false,
        scanInterval: null,
        
        // Manuel
        manualCode: '',
        manualCodeValid: false,
        processingCode: false,
        recentCodes: [],
        
        // Résultats
        scanResult: {
            success: false,
            message: '',
            package: null,
            action: null,
            redirect: null
        },
        showResultModal: false,
        scanHistory: [],

        init() {
            // Charger les données du localStorage
            this.recentCodes = this.loadFromStorage('deliverer_recent_codes', []);
            this.scanHistory = this.loadFromStorage('deliverer_scan_history', []);
            
            // Détecter si mobile pour mode par défaut
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            if (isMobile) {
                this.scannerMode = 'manual'; // Mode manuel par défaut sur mobile
            }
            
            // Événements
            window.addEventListener('open-scanner', () => {
                this.openScanner();
            });

            this.$watch('scannerOpen', (isOpen) => {
                if (isOpen) {
                    this.onScannerOpen();
                } else {
                    this.onScannerClose();
                }
            });

            this.$watch('manualCode', () => {
                this.validateManualCode();
            });

            // Charger les caméras disponibles
            this.loadAvailableCameras();
        },

        // ==================== OUVERTURE/FERMETURE ====================
        
        openScanner() {
            this.scannerOpen = true;
        },

        closeScanner() {
            this.scannerOpen = false;
        },

        onScannerOpen() {
            this.resetState();
            // Focus automatique sur le champ manuel
            if (this.scannerMode === 'manual') {
                setTimeout(() => {
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                }, 100);
            }
        },

        onScannerClose() {
            this.stopCamera();
            this.resetState();
        },

        resetState() {
            this.manualCode = '';
            this.manualCodeValid = false;
            this.processingCode = false;
            this.showResultModal = false;
            this.cameraError = false;
            this.cameraErrorMessage = '';
            this.isScanning = false;
        },

        // ==================== GESTION DES MODES ====================
        
        switchToCamera() {
            this.scannerMode = 'camera';
            // Ne pas auto-démarrer, laisser l'utilisateur cliquer
        },

        switchToManual() {
            this.scannerMode = 'manual';
            this.stopCamera();
            setTimeout(() => {
                if (this.$refs.manualInput) {
                    this.$refs.manualInput.focus();
                }
            }, 100);
        },

        // ==================== CAMÉRA NATIVE ====================
        
        async loadAvailableCameras() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                    console.warn('enumerateDevices non supporté');
                    return;
                }

                const devices = await navigator.mediaDevices.enumerateDevices();
                this.availableCameras = devices.filter(device => device.kind === 'videoinput');
                console.log('Caméras détectées:', this.availableCameras.length);
            } catch (error) {
                console.error('Erreur chargement caméras:', error);
                this.availableCameras = [];
            }
        },

        async startCamera() {
            try {
                this.cameraError = false;
                this.cameraErrorMessage = '';

                console.log('Démarrage caméra native...');

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('getUserMedia non supporté par ce navigateur');
                }

                // Arrêter l'ancien stream s'il existe
                this.stopCamera();

                // Contraintes selon l'appareil
                const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                
                let constraints = {
                    video: {
                        width: { min: 640, ideal: 1280, max: 1920 },
                        height: { min: 480, ideal: 720, max: 1080 },
                    }
                };

                if (isMobile) {
                    // Sur mobile, privilégier la caméra arrière
                    constraints.video.facingMode = { ideal: "environment" };
                } else {
                    // Sur desktop, utiliser la caméra sélectionnée
                    if (this.availableCameras.length > 0) {
                        constraints.video.deviceId = this.availableCameras[this.currentCameraIndex].deviceId;
                    }
                }

                console.log('Contraintes:', constraints);

                // Démarrer le stream
                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                // Attacher au video element
                const video = this.$refs.videoElement;
                video.srcObject = this.videoStream;
                
                // Attendre que la vidéo soit prête
                await new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        console.log('Vidéo prête, dimensions:', video.videoWidth, 'x', video.videoHeight);
                        resolve();
                    };
                });

                this.cameraStarted = true;
                console.log('Caméra démarrée avec succès');

                // Démarrer l'analyse automatique
                this.startAutoScan();

            } catch (error) {
                console.error('Erreur caméra:', error);
                this.cameraError = true;
                this.cameraErrorMessage = this.formatCameraError(error);
                this.cameraStarted = false;
            }
        },

        stopCamera() {
            // Arrêter l'analyse
            this.stopAutoScan();
            
            // Arrêter le stream
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            
            // Reset l'élément vidéo
            if (this.$refs.videoElement) {
                this.$refs.videoElement.srcObject = null;
            }
            
            this.cameraStarted = false;
            this.isScanning = false;
        },

        switchCamera() {
            if (this.availableCameras.length > 1) {
                this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
                if (this.cameraStarted) {
                    this.stopCamera();
                    setTimeout(() => this.startCamera(), 500);
                }
            }
        },

        formatCameraError(error) {
            const message = error.message || error.toString();
            
            console.error('Erreur caméra:', error);
            
            if (message.includes('Permission denied') || message.includes('NotAllowedError')) {
                return 'Accès caméra refusé. Vérifiez les permissions dans les paramètres du navigateur.';
            }
            if (message.includes('NotFoundError') || message.includes('no camera')) {
                return 'Aucune caméra trouvée. Vérifiez qu\'une caméra est disponible.';
            }
            if (message.includes('NotReadableError')) {
                return 'Caméra en cours d\'utilisation. Fermez les autres applications.';
            }
            if (message.includes('OverconstrainedError')) {
                return 'Caméra incompatible. Essayez de changer de caméra.';
            }
            
            return 'Erreur caméra. Utilisez le mode manuel.';
        },

        // ==================== ANALYSE QR NATIVE ====================
        
        startAutoScan() {
            this.isScanning = true;
            this.scanInterval = setInterval(() => {
                this.analyzeFrame();
            }, 500); // Analyser toutes les 500ms
        },

        stopAutoScan() {
            this.isScanning = false;
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
        },

        captureAndAnalyze() {
            this.analyzeFrame();
        },

        analyzeFrame() {
            try {
                const video = this.$refs.videoElement;
                const canvas = this.$refs.canvasElement;
                
                if (!video || !canvas || !video.videoWidth) return;

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Capturer l'image actuelle
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Analyser avec jsQR si disponible
                if (typeof jsQR !== 'undefined') {
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert"
                    });

                    if (code && code.data) {
                        console.log('QR détecté:', code.data);
                        this.onCodeScanned(code.data);
                    }
                } else {
                    console.warn('jsQR non disponible');
                }
                
            } catch (error) {
                console.error('Erreur analyse frame:', error);
            }
        },

        onCodeScanned(code) {
            console.log('Code scanné:', code);
            this.addToRecentCodes(code);
            this.stopCamera();
            this.processCode(code);
        },

        // ==================== MODE MANUEL ====================
        
        validateManualCode() {
            const code = this.manualCode.trim().toUpperCase();
            this.manualCodeValid = /^PKG_[A-Z0-9]{8,}_\d{8}$/.test(code) || 
                                  /^[A-Z0-9]{8,}$/.test(code) || 
                                  /^[0-9]{8,}$/.test(code);
        },

        processManualCode() {
            if (!this.manualCodeValid || this.processingCode) return;
            
            const code = this.manualCode.trim().toUpperCase();
            this.addToRecentCodes(code);
            this.processCode(code);
        },

        selectRecentCode(code) {
            this.manualCode = code;
            this.validateManualCode();
            this.processCode(code);
        },

        // ==================== TRAITEMENT DES CODES ====================
        
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
                this.showScanResult(data);

            } catch (error) {
                console.error('Erreur traitement code:', error);
                this.addToScanHistory(code, 'Erreur de connexion', false);
                this.showScanResult({
                    success: false,
                    message: 'Erreur de connexion. Vérifiez votre réseau.',
                    code_scanned: code
                });
            }
            
            this.processingCode = false;
        },

        // ==================== RÉSULTATS ====================
        
        showScanResult(result) {
            this.scanResult = {
                success: result.success || false,
                message: result.message || '',
                package: result.package || null,
                action: result.action || null,
                redirect: result.redirect || null
            };
            this.showResultModal = true;
            
            // Auto-redirection si succès
            if (result.success && result.redirect) {
                setTimeout(() => {
                    this.handleScanAction();
                }, 2000);
            }
        },

        closeResultModal() {
            this.showResultModal = false;
        },

        handleScanAction() {
            if (this.scanResult.redirect) {
                this.closeScanner();
                window.location.href = this.scanResult.redirect;
            }
            this.showResultModal = false;
        },

        getActionText() {
            const action = this.scanResult.action;
            switch (action) {
                case 'accept': return 'Accepter';
                case 'pickup': return 'Collecter';
                case 'deliver': return 'Livrer';
                case 'return': return 'Retourner';
                default: return 'Voir';
            }
        },

        // ==================== UTILITAIRES ====================
        
        addToRecentCodes(code) {
            const recent = { value: code, timestamp: Date.now() };
            this.recentCodes = [recent, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10);
            this.saveToStorage('deliverer_recent_codes', this.recentCodes);
        },

        addToScanHistory(code, result, success) {
            const historyItem = { code, result, success, timestamp: Date.now() };
            this.scanHistory = [historyItem, ...this.scanHistory].slice(0, 20);
            this.saveToStorage('deliverer_scan_history', this.scanHistory);
        },

        loadFromStorage(key, defaultValue = []) {
            try {
                const stored = localStorage.getItem(key);
                return stored ? JSON.parse(stored) : defaultValue;
            } catch {
                return defaultValue;
            }
        },

        saveToStorage(key, data) {
            try {
                localStorage.setItem(key, JSON.stringify(data));
            } catch (error) {
                console.error('Erreur sauvegarde localStorage:', error);
            }
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        }
    }
}

// Fonction globale pour compatibilité
window.openScanner = function() {
    window.dispatchEvent(new Event('open-scanner'));
};
</script>

<!-- jsQR Library pour analyse QR -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>