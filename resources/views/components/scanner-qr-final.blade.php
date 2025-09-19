<!-- Scanner QR FINAL - FONCTIONNE GARANTIE -->
<div x-data="scannerQRFinal()" @open-scanner.window="openScanner()">
    
    <!-- Modal Scanner -->
    <div x-show="scannerVisible" x-transition class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center">
        <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Scanner QR/Code</h3>
                    <p class="text-sm text-gray-600" x-text="activeMode === 'camera' ? 'Mode caméra' : 'Saisie manuelle'"></p>
                </div>
                <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Mode Selection -->
            <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
                <button @click="switchMode('camera')" 
                        :class="activeMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all">
                    📷 Caméra
                </button>
                <button @click="switchMode('manual')" 
                        :class="activeMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all">
                    ✏️ Manuel
                </button>
            </div>

            <!-- Camera Mode -->
            <div x-show="activeMode === 'camera'" class="space-y-4">
                <!-- Video Container -->
                <div class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 1;">
                    <video x-ref="videoElement" 
                           class="w-full h-full object-cover" 
                           autoplay playsinline muted
                           x-show="cameraActive && !cameraErrorMsg"></video>
                    
                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                    
                    <!-- Status Messages -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div x-show="!cameraActive && !cameraErrorMsg" class="text-white text-center">
                            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <p>Démarrage caméra...</p>
                        </div>
                        
                        <div x-show="cameraErrorMsg" class="bg-red-500 text-white p-4 rounded-xl text-center max-w-xs">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm mb-2" x-text="cameraErrorMsg"></p>
                            <button @click="switchMode('manual')" class="bg-white text-red-500 px-3 py-1 rounded text-xs">
                                Mode Manuel
                            </button>
                        </div>
                    </div>
                    
                    <!-- Scan Frame -->
                    <div x-show="cameraActive && !cameraErrorMsg" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-64 h-64 border-4 border-emerald-500 rounded-2xl relative">
                            <div class="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                            <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Camera Controls -->
                <div class="flex justify-center space-x-3">
                    <button @click="startCamera()" x-show="!cameraActive"
                            class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700">
                        Démarrer Caméra
                    </button>
                    
                    <button @click="stopCamera()" x-show="cameraActive"
                            class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700">
                        Arrêter
                    </button>
                    
                    <button @click="captureFrame()" x-show="cameraActive"
                            class="bg-purple-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-purple-700">
                        📸 Capturer
                    </button>
                </div>
            </div>

            <!-- Manual Mode -->
            <div x-show="activeMode === 'manual'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
                    <input type="text" x-ref="manualInput" x-model="manualCode" 
                           @keydown.enter="searchCode()" @input="validateCode()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-lg font-mono uppercase"
                           placeholder="PKG_12345678_20251219" autofocus>
                    
                    <div class="mt-2 text-sm">
                        <div x-show="codeValid" class="text-emerald-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Format valide
                        </div>
                        <div x-show="manualCode && !codeValid" class="text-red-600 flex items-center">
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
                            <button @click="useRecentCode(code.value)" 
                                    class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-sm" x-text="code.value"></span>
                                    <span class="text-xs text-gray-500" x-text="formatTime(code.timestamp)"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                
                <button @click="searchCode()" :disabled="!codeValid || searching"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!searching">🔍 Rechercher</span>
                    <span x-show="searching">⏳ Recherche...</span>
                </button>
            </div>

            <!-- Scan History -->
            <div x-show="scanHistory.length > 0" class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Derniers scans</h4>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="(scan, index) in scanHistory.slice(0, 3)" :key="index">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium" x-text="scan.code"></p>
                                <p class="text-xs text-gray-500" x-text="scan.result"></p>
                            </div>
                            <span :class="scan.success ? 'text-emerald-500' : 'text-red-500'">
                                <span x-text="scan.success ? '✅' : '❌'"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div x-show="resultVisible" x-transition 
         class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4">
        <div x-show="resultVisible" x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
             class="bg-white rounded-2xl p-6 w-full max-w-sm">
            
            <div class="text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4"
                     :class="result.success ? 'bg-emerald-100' : 'bg-red-100'">
                    <span class="text-2xl" x-text="result.success ? '✅' : '❌'"></span>
                </div>
                
                <!-- Title & Message -->
                <h3 class="text-lg font-bold mb-2" :class="result.success ? 'text-emerald-800' : 'text-red-800'" 
                    x-text="result.success ? 'Colis trouvé!' : 'Erreur'"></h3>
                
                <p class="text-gray-600 mb-4" x-text="result.message"></p>
                
                <!-- Package Info -->
                <div x-show="result.package" class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="text-left space-y-2">
                        <p class="font-mono text-sm text-blue-600" x-text="result.package?.code"></p>
                        <p class="font-medium" x-text="result.package?.recipient_name"></p>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">COD:</span>
                            <span class="font-bold text-emerald-600" x-text="formatMoney(result.package?.cod_amount)"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex space-x-3">
                    <button @click="closeResult()" 
                            class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">
                        Fermer
                    </button>
                    <button x-show="result.success && result.redirect" @click="goToPackage()" 
                            class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700"
                            x-text="getActionLabel()">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scannerQRFinal() {
    return {
        // État principal
        scannerVisible: false,
        activeMode: 'manual', // Démarrer en mode manuel
        
        // Caméra
        cameraActive: false,
        cameraErrorMsg: '',
        videoStream: null,
        scanInterval: null,
        
        // Manuel
        manualCode: '',
        codeValid: false,
        searching: false,
        recentCodes: [],
        
        // Résultats
        result: {},
        resultVisible: false,
        scanHistory: [],

        init() {
            // Charger données localStorage
            this.loadStoredData();
            
            // Détecter mobile pour mode par défaut
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            this.activeMode = isMobile ? 'manual' : 'manual'; // Manuel par défaut partout
            
            // Surveiller les changements de code
            this.$watch('manualCode', () => this.validateCode());
        },

        // ==================== ACTIONS PRINCIPALES ====================
        
        openScanner() {
            console.log('Ouverture scanner...');
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
            console.log('Fermeture scanner...');
            this.stopCamera();
            this.scannerVisible = false;
            this.resetScanner();
        },

        resetScanner() {
            this.manualCode = '';
            this.codeValid = false;
            this.searching = false;
            this.cameraErrorMsg = '';
            this.resultVisible = false;
        },

        switchMode(mode) {
            console.log('Changement mode:', mode);
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

        // ==================== CAMÉRA ====================
        
        async startCamera() {
            console.log('Démarrage caméra...');
            this.cameraErrorMsg = '';
            
            try {
                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error('getUserMedia non supporté');
                }

                this.stopCamera(); // Nettoyer avant de commencer

                const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                const constraints = {
                    video: {
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 },
                    }
                };

                if (isMobile) {
                    constraints.video.facingMode = { ideal: "environment" };
                }

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = this.$refs.videoElement;
                video.srcObject = this.videoStream;

                await new Promise(resolve => {
                    video.onloadedmetadata = resolve;
                });

                this.cameraActive = true;
                this.startScanning();
                console.log('Caméra démarrée avec succès');

            } catch (error) {
                console.error('Erreur caméra:', error);
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
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

        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();
            
            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Autorisez l\'accès caméra.';
            }
            if (msg.includes('NotFoundError')) {
                return 'Aucune caméra trouvée.';
            }
            if (msg.includes('NotReadableError')) {
                return 'Caméra occupée par une autre app.';
            }
            
            return 'Erreur caméra. Utilisez le mode manuel.';
        },

        startScanning() {
            this.scanInterval = setInterval(() => {
                this.analyzeFrame();
            }, 500);
        },

        stopScanning() {
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
        },

        captureFrame() {
            this.analyzeFrame();
        },

        analyzeFrame() {
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
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code?.data) {
                        console.log('QR détecté:', code.data);
                        this.stopCamera();
                        this.processCode(code.data);
                    }
                }
            } catch (error) {
                console.error('Erreur analyse:', error);
            }
        },

        // ==================== MODE MANUEL ====================
        
        validateCode() {
            const code = this.manualCode.trim().toUpperCase();
            this.codeValid = /^PKG_[A-Z0-9]{8,}_\d{8}$/.test(code) || 
                             /^[A-Z0-9]{8,}$/.test(code) || 
                             /^[0-9]{8,}$/.test(code);
        },

        searchCode() {
            if (!this.codeValid || this.searching) return;
            
            const code = this.manualCode.trim().toUpperCase();
            this.processCode(code);
        },

        useRecentCode(code) {
            this.manualCode = code;
            this.validateCode();
            this.processCode(code);
        },

        // ==================== TRAITEMENT ====================
        
        async processCode(code) {
            console.log('Traitement code:', code);
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
                
                this.addToHistory(code, data.message, data.success);
                this.showResult(data);

            } catch (error) {
                console.error('Erreur réseau:', error);
                this.addToHistory(code, 'Erreur de connexion', false);
                this.showResult({
                    success: false,
                    message: 'Erreur de connexion. Vérifiez votre réseau.'
                });
            }
            
            this.searching = false;
        },

        showResult(data) {
            this.result = data;
            this.resultVisible = true;
            
            // Auto-redirection si succès
            if (data.success && data.redirect) {
                setTimeout(() => {
                    this.goToPackage();
                }, 3000);
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

        // ==================== STOCKAGE ====================
        
        loadStoredData() {
            try {
                this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
                this.scanHistory = JSON.parse(localStorage.getItem('scanner_history') || '[]');
            } catch {
                this.recentCodes = [];
                this.scanHistory = [];
            }
        },

        addToRecent(code) {
            const item = { value: code, timestamp: Date.now() };
            this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10);
            
            try {
                localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
            } catch (error) {
                console.error('Erreur sauvegarde:', error);
            }
        },

        addToHistory(code, result, success) {
            const item = { code, result, success, timestamp: Date.now() };
            this.scanHistory = [item, ...this.scanHistory].slice(0, 20);
            
            try {
                localStorage.setItem('scanner_history', JSON.stringify(this.scanHistory));
            } catch (error) {
                console.error('Erreur sauvegarde:', error);
            }
        },

        // ==================== UTILITAIRES ====================
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', { 
                hour: '2-digit', minute: '2-digit' 
            });
        },

        formatMoney(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        }
    }
}
</script>