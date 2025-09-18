<!-- Scanner QR/Code-barres Modal Component -->
<div x-show="scannerOpen" x-transition class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">
    <div class="bg-white rounded-3xl p-6 m-4 w-full max-w-md max-h-screen overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Scanner</h3>
                <p class="text-sm text-gray-600">QR Code ou Code-barres</p>
            </div>
            <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Scanner Tabs -->
        <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
            <button @click="scannerMode = 'camera'" 
                    :class="scannerMode === 'camera' ? 'bg-white shadow-sm' : ''"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                📷 Caméra
            </button>
            <button @click="scannerMode = 'manual'" 
                    :class="scannerMode === 'manual' ? 'bg-white shadow-sm' : ''"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                ⌨️ Manuel
            </button>
            <button @click="scannerMode = 'search'" 
                    :class="scannerMode === 'search' ? 'bg-white shadow-sm' : ''"
                    class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors">
                🔍 Recherche
            </button>
        </div>

        <!-- Camera Scanner -->
        <div x-show="scannerMode === 'camera'" class="space-y-4">
            <div class="relative">
                <!-- Video Element for Camera -->
                <div id="qr-reader" class="w-full aspect-square bg-gray-900 rounded-xl overflow-hidden relative">
                    <video x-ref="videoElement" 
                           class="w-full h-full object-cover" 
                           autoplay 
                           playsinline></video>
                    
                    <!-- Scan Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-48 h-48 border-4 border-emerald-500 rounded-2xl relative">
                            <!-- Corner indicators -->
                            <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-emerald-500 rounded-tl-lg"></div>
                            <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-emerald-500 rounded-tr-lg"></div>
                            <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-emerald-500 rounded-bl-lg"></div>
                            <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-emerald-500 rounded-br-lg"></div>
                            
                            <!-- Scanning Line Animation -->
                            <div class="absolute inset-0 flex items-center justify-center" x-show="cameraScanning">
                                <div class="w-full h-0.5 bg-emerald-500 animate-pulse shadow-lg"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Overlay -->
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <div class="bg-black bg-opacity-75 text-white px-4 py-2 rounded-xl">
                            <p class="text-sm" x-show="!cameraError && !cameraScanning">📱 Positionnez le code dans le cadre</p>
                            <p class="text-sm text-emerald-400" x-show="cameraScanning">🔍 Scan en cours...</p>
                            <p class="text-sm text-red-400" x-show="cameraError" x-text="cameraError"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Camera Controls -->
                <div class="flex justify-center space-x-4 mt-4">
                    <button @click="startCamera()" 
                            x-show="!cameraActive"
                            class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-emerald-700 transition-colors">
                        📷 Activer Caméra
                    </button>
                    <button @click="stopCamera()" 
                            x-show="cameraActive"
                            class="bg-red-600 text-white px-6 py-2 rounded-xl font-medium hover:bg-red-700 transition-colors">
                        ⏹️ Arrêter
                    </button>
                    <button @click="switchCamera()" 
                            x-show="cameraActive && availableCameras.length > 1"
                            class="bg-blue-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-blue-700 transition-colors">
                        🔄
                    </button>
                </div>
                
                <!-- Camera Selection -->
                <div x-show="availableCameras.length > 1" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner Caméra</label>
                    <select x-model="selectedCameraId" @change="switchToSelectedCamera()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <template x-for="camera in availableCameras" :key="camera.id">
                            <option :value="camera.id" x-text="camera.label || `Caméra ${camera.id}`"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- Manual Entry -->
        <div x-show="scannerMode === 'manual'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Saisir le code</label>
                <input type="text" 
                       x-model="manualCode" 
                       @keydown.enter="processManualCode()"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-lg"
                       placeholder="PKG_ABC123... ou code-barres"
                       autofocus>
                <p class="text-xs text-gray-500 mt-2">Formats acceptés: PKG_XXXXX, REF_XXXXX, codes-barres...</p>
            </div>
            
            <!-- Recent Scans -->
            <div x-show="recentScans.length > 0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Codes récents</label>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="scan in recentScans.slice(0, 5)" :key="scan.code">
                        <button @click="manualCode = scan.code; processManualCode()" 
                                class="w-full text-left p-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm" x-text="scan.code"></span>
                                <span class="text-xs text-gray-500" x-text="formatTime(scan.timestamp)"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            <button @click="processManualCode()" 
                    :disabled="!manualCode || processingCode"
                    class="w-full bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!processingCode">🔍 Rechercher</span>
                <span x-show="processingCode" class="flex items-center justify-center space-x-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Recherche...</span>
                </span>
            </button>
        </div>

        <!-- Advanced Search -->
        <div x-show="scannerMode === 'search'" class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom Client</label>
                    <input type="text" 
                           x-model="advancedSearch.clientName"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Nom...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" 
                           x-model="advancedSearch.phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           placeholder="Téléphone...">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <input type="text" 
                       x-model="advancedSearch.address"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                       placeholder="Rue, quartier...">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délégation</label>
                    <select x-model="advancedSearch.delegation" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Toutes</option>
                        <template x-for="delegation in delegations" :key="delegation.id">
                            <option :value="delegation.id" x-text="delegation.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select x-model="advancedSearch.status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">Tous</option>
                        <option value="AVAILABLE">Disponible</option>
                        <option value="ACCEPTED">Accepté</option>
                        <option value="PICKED_UP">Collecté</option>
                        <option value="DELIVERED">Livré</option>
                    </select>
                </div>
            </div>
            
            <button @click="performAdvancedSearch()" 
                    :disabled="processingSearch"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50">
                <span x-show="!processingSearch">🔍 Recherche Avancée</span>
                <span x-show="processingSearch" class="flex items-center justify-center space-x-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Recherche...</span>
                </span>
            </button>
            
            <!-- Search Results -->
            <div x-show="searchResults.length > 0" class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Résultats (<span x-text="searchResults.length"></span>)</label>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="result in searchResults" :key="result.id">
                        <button @click="selectSearchResult(result)" 
                                class="w-full text-left p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-blue-900" x-text="result.package_code"></p>
                                    <p class="text-sm text-blue-700" x-text="result.recipient_data.name"></p>
                                    <p class="text-xs text-blue-600" x-text="result.recipient_data.phone"></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full" 
                                          :class="getStatusColor(result.status)" 
                                          x-text="getStatusText(result.status)"></span>
                                    <p class="text-sm font-bold text-emerald-600 mt-1" x-text="formatAmount(result.cod_amount)"></p>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Scan History -->
        <div x-show="scanHistory.length > 0" class="mt-6 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Historique des scans</h4>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                <template x-for="(scan, index) in scanHistory.slice(0, 5)" :key="index">
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium" x-text="scan.code"></p>
                            <p class="text-xs text-gray-500" x-text="scan.result"></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-400" x-text="formatTime(scan.timestamp)"></span>
                            <span :class="scan.success ? 'text-green-500' : 'text-red-500'">
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

        <!-- Action Result Modal -->
        <div x-show="showActionModal" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-10 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full mb-4"
                         :class="scanResult.success ? 'bg-green-100' : 'bg-red-100'">
                        <svg class="w-6 h-6" :class="scanResult.success ? 'text-green-600' : 'text-red-600'" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  :d="scanResult.success ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2" :class="scanResult.success ? 'text-green-800' : 'text-red-800'" 
                        x-text="scanResult.title"></h3>
                    <p class="text-gray-600 mb-4" x-text="scanResult.message"></p>
                    
                    <div class="flex space-x-3">
                        <button @click="showActionModal = false" 
                                class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                            Fermer
                        </button>
                        <button x-show="scanResult.redirect" 
                                @click="window.location.href = scanResult.redirect" 
                                class="flex-1 py-2 px-4 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition-colors">
                            Voir Colis
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Scanner QR/Code-barres Logic
function scannerApp() {
    return {
        scannerOpen: false,
        scannerMode: 'camera', // camera, manual, search
        
        // Camera
        cameraActive: false,
        cameraScanning: false,
        cameraError: null,
        videoStream: null,
        qrScanner: null,
        availableCameras: [],
        selectedCameraId: null,
        
        // Manual entry
        manualCode: '',
        processingCode: false,
        recentScans: JSON.parse(localStorage.getItem('deliverer_recent_scans') || '[]'),
        
        // Advanced search
        advancedSearch: {
            clientName: '',
            phone: '',
            address: '',
            delegation: '',
            status: ''
        },
        processingSearch: false,
        searchResults: [],
        delegations: [], // To be loaded
        
        // Results and history
        scanResult: {},
        showActionModal: false,
        scanHistory: JSON.parse(localStorage.getItem('deliverer_scan_history') || '[]'),

        init() {
            // Load delegations for search
            this.loadDelegations();
            
            // Listen for scanner events from parent
            this.$watch('scannerOpen', (isOpen) => {
                if (isOpen) {
                    this.manualCode = '';
                    this.searchResults = [];
                    if (this.scannerMode === 'camera') {
                        setTimeout(() => this.startCamera(), 300);
                    }
                } else {
                    this.stopCamera();
                }
            });
        },

        // ==================== CAMERA METHODS ====================
        
        async startCamera() {
            try {
                this.cameraError = null;
                
                // Get available cameras
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.availableCameras = devices.filter(device => device.kind === 'videoinput');
                
                if (this.availableCameras.length === 0) {
                    throw new Error('Aucune caméra trouvée');
                }
                
                // Select back camera by default if available
                if (!this.selectedCameraId) {
                    const backCamera = this.availableCameras.find(camera => 
                        camera.label.toLowerCase().includes('back') || 
                        camera.label.toLowerCase().includes('rear')
                    );
                    this.selectedCameraId = backCamera ? backCamera.deviceId : this.availableCameras[0].deviceId;
                }
                
                // Start video stream
                const constraints = {
                    video: {
                        deviceId: this.selectedCameraId,
                        facingMode: 'environment', // Back camera
                        width: { ideal: 640 },
                        height: { ideal: 640 }
                    }
                };
                
                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$refs.videoElement.srcObject = this.videoStream;
                this.cameraActive = true;
                
                // Initialize QR Scanner
                this.initializeQRScanner();
                
            } catch (error) {
                console.error('Camera error:', error);
                this.cameraError = this.formatCameraError(error);
            }
        },

        stopCamera() {
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            
            if (this.qrScanner) {
                this.qrScanner = null;
            }
            
            this.cameraActive = false;
            this.cameraScanning = false;
            this.cameraError = null;
        },

        switchCamera() {
            if (this.availableCameras.length > 1) {
                const currentIndex = this.availableCameras.findIndex(c => c.deviceId === this.selectedCameraId);
                const nextIndex = (currentIndex + 1) % this.availableCameras.length;
                this.selectedCameraId = this.availableCameras[nextIndex].deviceId;
                
                this.stopCamera();
                setTimeout(() => this.startCamera(), 100);
            }
        },

        switchToSelectedCamera() {
            if (this.cameraActive) {
                this.stopCamera();
                setTimeout(() => this.startCamera(), 100);
            }
        },

        initializeQRScanner() {
            // Use a simple scanning approach with canvas analysis
            const video = this.$refs.videoElement;
            
            const scanFrame = () => {
                if (!this.cameraActive || !video.videoWidth) {
                    if (this.cameraActive) {
                        requestAnimationFrame(scanFrame);
                    }
                    return;
                }
                
                try {
                    // Create canvas for frame analysis
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Draw current frame
                    ctx.drawImage(video, 0, 0);
                    
                    // For production, you'd use a QR code library here
                    // For now, we'll simulate detection or use jsQR
                    this.analyzeFrame(canvas);
                    
                } catch (error) {
                    console.error('Frame analysis error:', error);
                }
                
                if (this.cameraActive) {
                    requestAnimationFrame(scanFrame);
                }
            };
            
            requestAnimationFrame(scanFrame);
        },

        analyzeFrame(canvas) {
            // Here you would integrate with a QR scanning library like jsQR
            // For demo purposes, we'll simulate detection
            
            // In production: 
            // const imageData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            // const code = jsQR(imageData.data, imageData.width, imageData.height);
            // if (code) {
            //     this.handleScannedCode(code.data);
            // }
            
            // Simulate random QR detection for demo
            if (Math.random() < 0.001) { // Very low chance to simulate real detection
                this.handleScannedCode('PKG_DEMO' + Date.now().toString().slice(-6));
            }
        },

        // ==================== CODE PROCESSING ====================

        async handleScannedCode(code) {
            this.cameraScanning = true;
            
            // Add to recent scans
            this.addToRecentScans(code);
            
            // Process the code
            await this.processCode(code);
            
            this.cameraScanning = false;
        },

        async processManualCode() {
            if (!this.manualCode.trim()) return;
            
            await this.processCode(this.manualCode.trim());
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
                
                // Add to scan history
                this.addToScanHistory(code, data.message, data.success);
                
                if (data.success) {
                    this.showScanResult({
                        success: true,
                        title: 'Code trouvé!',
                        message: data.message,
                        redirect: data.redirect
                    });
                    
                    if (data.action) {
                        // Handle specific actions
                        this.handleCodeAction(data.action, data.package);
                    }
                } else {
                    this.showScanResult({
                        success: false,
                        title: 'Code non trouvé',
                        message: data.message
                    });
                }
                
            } catch (error) {
                console.error('Code processing error:', error);
                this.addToScanHistory(code, 'Erreur de connexion', false);
                this.showScanResult({
                    success: false,
                    title: 'Erreur',
                    message: 'Erreur de connexion. Vérifiez votre réseau.'
                });
            }
            
            this.processingCode = false;
        },

        handleCodeAction(action, packageData) {
            switch(action) {
                case 'accept':
                    // Show accept confirmation for available packages
                    break;
                case 'pickup':
                    // Show pickup form for accepted packages
                    break;
                case 'deliver':
                    // Show delivery form for picked up packages
                    break;
                case 'view':
                    // Just redirect to package details
                    break;
            }
        },

        // ==================== ADVANCED SEARCH ====================

        async performAdvancedSearch() {
            this.processingSearch = true;
            
            try {
                const searchParams = new URLSearchParams();
                Object.keys(this.advancedSearch).forEach(key => {
                    if (this.advancedSearch[key]) {
                        searchParams.append(key, this.advancedSearch[key]);
                    }
                });
                
                const response = await fetch(`/deliverer/packages/search-advanced?${searchParams}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.searchResults = data.packages || [];
                } else {
                    this.showToast('Erreur lors de la recherche', 'error');
                }
                
            } catch (error) {
                console.error('Advanced search error:', error);
                this.showToast('Erreur de connexion', 'error');
            }
            
            this.processingSearch = false;
        },

        selectSearchResult(result) {
            // Process selected result
            this.processCode(result.package_code);
        },

        async loadDelegations() {
            try {
                const response = await fetch('/deliverer/api/delegations');
                if (response.ok) {
                    const data = await response.json();
                    this.delegations = data.delegations || [];
                }
            } catch (error) {
                console.error('Error loading delegations:', error);
            }
        },

        // ==================== UTILITY METHODS ====================

        closeScanner() {
            this.stopCamera();
            this.scannerOpen = false;
            this.manualCode = '';
            this.searchResults = [];
        },

        addToRecentScans(code) {
            const recent = {
                code: code,
                timestamp: Date.now()
            };
            
            this.recentScans.unshift(recent);
            this.recentScans = this.recentScans.slice(0, 10); // Keep last 10
            
            localStorage.setItem('deliverer_recent_scans', JSON.stringify(this.recentScans));
        },

        addToScanHistory(code, result, success) {
            const historyItem = {
                code: code,
                result: result,
                success: success,
                timestamp: Date.now()
            };
            
            this.scanHistory.unshift(historyItem);
            this.scanHistory = this.scanHistory.slice(0, 50); // Keep last 50
            
            localStorage.setItem('deliverer_scan_history', JSON.stringify(this.scanHistory));
        },

        showScanResult(result) {
            this.scanResult = result;
            this.showActionModal = true;
            
            // Auto-close if successful and has redirect
            if (result.success && result.redirect) {
                setTimeout(() => {
                    this.showActionModal = false;
                    this.closeScanner();
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                }, 2000);
            }
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

        getStatusColor(status) {
            const colors = {
                'AVAILABLE': 'bg-blue-100 text-blue-800',
                'ACCEPTED': 'bg-purple-100 text-purple-800',
                'PICKED_UP': 'bg-orange-100 text-orange-800',
                'DELIVERED': 'bg-green-100 text-green-800',
                'RETURNED': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },

        getStatusText(status) {
            const texts = {
                'AVAILABLE': 'Disponible',
                'ACCEPTED': 'Accepté',
                'PICKED_UP': 'Collecté',
                'DELIVERED': 'Livré',
                'RETURNED': 'Retourné'
            };
            return texts[status] || status;
        },

        showToast(message, type = 'success') {
            // Utility toast function
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
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
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}

// Global function to open scanner from parent components
function openScanner() {
    // This will be called from the main app
    const scannerComponent = document.querySelector('[x-data*="scannerApp"]');
    if (scannerComponent) {
        scannerComponent.__x.$data.scannerOpen = true;
    }
}
</script>

<!-- Add this script to load QR scanning library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<style>
/* Scanner specific styles */
#qr-reader video {
    transform: scaleX(-1); /* Mirror effect for front camera */
}

.scanner-overlay {
    background: linear-gradient(
        45deg,
        transparent 30%, 
        rgba(16, 185, 129, 0.1) 30%, 
        rgba(16, 185, 129, 0.1) 70%, 
        transparent 70%
    );
}

/* Scanning line animation */
@keyframes scan-line {
    0% { top: 0%; }
    100% { top: 100%; }
}

.scanning-line {
    animation: scan-line 2s linear infinite;
}
</style>