@extends('layouts.deliverer')

@section('title', 'Scanner Multiple')

@section('content')
<div class="h-full bg-gray-50" x-data="multiScannerApp()" x-init="init()">

    <!-- Header avec retour -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 sticky top-0 z-10 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('deliverer.run.sheet') }}" class="text-white hover:text-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold">Scanner Multiple</h1>
                    <p class="text-blue-200 text-sm" x-text="getActionLabel()"></p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold" x-text="scannedPackages.length"></div>
                <div class="text-xs text-blue-200">Colis</div>
            </div>
        </div>
    </div>

    <!-- Sélection de l'action (si pas encore sélectionnée) -->
    <div x-show="!actionSelected" class="p-6 space-y-4">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Choisissez l'action</h2>
            <p class="text-sm text-gray-600 mb-4">Sélectionnez l'action à effectuer sur les colis scannés</p>
        </div>

        <button @click="selectAction('pickup')" 
                class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 rounded-2xl shadow-lg hover:from-orange-600 hover:to-orange-700 transition-all transform hover:scale-[1.02]">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1 text-left">
                    <h3 class="text-xl font-bold mb-1">Pickup chez Fournisseur</h3>
                    <p class="text-orange-100 text-sm">Collecter les colis chez le fournisseur</p>
                    <p class="text-orange-100 text-xs mt-1">Statuts acceptés: AVAILABLE, CREATED</p>
                </div>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </button>

        <button @click="selectAction('delivery')" 
                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg hover:from-green-600 hover:to-green-700 transition-all transform hover:scale-[1.02]">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
                <div class="flex-1 text-left">
                    <h3 class="text-xl font-bold mb-1">Prêt pour Livraison</h3>
                    <p class="text-green-100 text-sm">Préparer les colis pour la livraison</p>
                    <p class="text-green-100 text-xs mt-1">Tous statuts sauf DELIVERED, PAID</p>
                </div>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </button>
    </div>

    <!-- Interface de scan (après sélection de l'action) -->
    <div x-show="actionSelected" class="flex flex-col h-[calc(100vh-80px)]">
        
        <!-- Bouton de scan -->
        <div class="p-6">
            <button @click="openScanner()" 
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-6 px-6 rounded-2xl shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-[1.02]">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="text-xl font-bold">Scanner un Colis</span>
                </div>
            </button>
        </div>

        <!-- Liste des colis scannés -->
        <div class="flex-1 overflow-y-auto px-6 pb-6">
            <div x-show="scannedPackages.length === 0" class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Aucun colis scanné</h3>
                <p class="text-gray-600 text-sm">Scannez des colis pour les ajouter à la liste</p>
            </div>

            <div class="space-y-3">
                <template x-for="(pkg, index) in scannedPackages" :key="pkg.id">
                    <div class="bg-white rounded-xl p-4 shadow-sm border-2 border-green-200 relative">
                        <div class="absolute top-2 right-2">
                            <button @click="removePackage(index)" class="p-2 bg-red-100 hover:bg-red-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-start space-x-3 pr-10">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-green-600 font-bold" x-text="index + 1"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-mono text-sm font-bold text-blue-600 mb-1" x-text="pkg.tracking_number"></div>
                                <div class="text-xs text-gray-600 mb-1" x-text="pkg.recipient_name"></div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-md font-medium" x-text="pkg.status"></span>
                                    <span x-show="pkg.cod_amount > 0" class="text-xs font-semibold text-green-600" x-text="formatCurrency(pkg.cod_amount)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div x-show="scannedPackages.length > 0" class="sticky bottom-0 bg-white border-t border-gray-200 p-6 space-y-3 shadow-lg">
            <button @click="resetAll()" 
                    class="w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-xl font-semibold hover:bg-gray-300 transition-colors">
                Annuler et Recommencer
            </button>
            <button @click="validateScan()" 
                    :disabled="validating || scannedPackages.length === 0"
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-4 px-4 rounded-xl font-bold hover:from-green-600 hover:to-green-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!validating" x-text="'Valider (' + scannedPackages.length + ' colis)'"></span>
                <span x-show="validating" class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Validation en cours...
                </span>
            </button>
        </div>
    </div>

    <!-- Scanner Component with Camera -->
    <div x-show="scannerOpen" x-transition class="fixed inset-0 bg-black z-50">
        <div class="h-full flex flex-col">
            <!-- Header -->
            <div class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between">
                <h3 class="text-lg font-bold">Scanner le Code</h3>
                <button @click="closeScanner()" class="p-2 hover:bg-gray-800 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Camera View -->
            <div class="relative flex-1 bg-black">
                <video x-ref="scanVideoElement" class="w-full h-full object-cover" autoplay playsinline muted x-show="scanCameraActive"></video>
                <canvas x-ref="scanCanvasElement" class="hidden"></canvas>
                
                <!-- Scanning Overlay -->
                <div x-show="scanCameraActive" class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 m-auto w-64 h-64 border-4 border-blue-500 rounded-lg">
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white"></div>
                    </div>
                </div>
                
                <!-- Camera Controls -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-3">
                    <button @click="startScanCamera()" x-show="!scanCameraActive && !scanCameraError"
                            class="px-6 py-3 bg-green-600 text-white rounded-xl font-semibold">
                        Activer Caméra
                    </button>
                    <button @click="stopScanCamera()" x-show="scanCameraActive"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold">
                        Arrêter
                    </button>
                </div>
                
                <!-- Error Message -->
                <div x-show="scanCameraError" class="absolute inset-0 flex items-center justify-center bg-gray-900">
                    <div class="text-center p-6">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-white font-semibold mb-2">Erreur Caméra</p>
                        <p class="text-gray-400 text-sm mb-4" x-text="scanCameraErrorMsg"></p>
                        <button @click="retryScanCamera()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Réessayer</button>
                    </div>
                </div>
            </div>

            <!-- Manual Input -->
            <div class="bg-gray-900 p-4">
                <div class="space-y-3">
                    <label class="block text-white font-semibold text-sm">Saisie Manuelle</label>
                    <div class="flex space-x-2">
                        <input type="text" 
                               x-ref="scanInput"
                               x-model="scanCode" 
                               @keydown.enter="processScan()"
                               @input="scanCode = scanCode.toUpperCase()"
                               class="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl font-mono text-lg"
                               placeholder="PKG_1234...">
                        <button @click="processScan()" 
                                :disabled="!scanCode || scanning"
                                class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!scanning">OK</span>
                            <span x-show="scanning">...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast de notification -->
    <div x-show="toast.show" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-6 left-6 right-6 z-50">
        <div class="rounded-xl shadow-2xl p-4 flex items-center space-x-3"
             :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <span class="text-2xl" x-text="toast.type === 'success' ? '✓' : '✕'"></span>
            </div>
            <div class="flex-1">
                <p class="text-white font-semibold" x-text="toast.message"></p>
                <p x-show="toast.detail" class="text-white/90 text-sm" x-text="toast.detail"></p>
            </div>
        </div>
    </div>
</div>

<!-- Inclure les bibliothèques QR Scanner et Quagga -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

<script>
function multiScannerApp() {
    return {
        actionSelected: false,
        selectedAction: null,
        scannedPackages: [],
        scannerOpen: false,
        scanCode: '',
        scanning: false,
        validating: false,
        toast: {
            show: false,
            type: 'success',
            message: '',
            detail: ''
        },
        successAudio: null,
        errorAudio: null,

        init() {
            // Créer les sons
            this.successAudio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjWO1/LSeS0GKHzM8N6OPwoUXrTp6qhVFApGn+DyvmwhBjWO1/LSeS0GKHzM8N6OPwoUXrTp6qhVFA==');
            this.errorAudio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIA==');
        },

        selectAction(action) {
            this.selectedAction = action;
            this.actionSelected = true;
        },

        getActionLabel() {
            if (!this.actionSelected) return 'Sélectionnez une action';
            return this.selectedAction === 'pickup' ? 'Pickup chez Fournisseur' : 'Prêt pour Livraison';
        },

        openScanner() {
            this.scannerOpen = true;
            this.scanCode = '';
            setTimeout(() => {
                this.startScanCamera();
            }, 300);
        },

        closeScanner() {
            this.stopScanCamera();
            this.scannerOpen = false;
            this.scanCode = '';
            this.scanning = false;
        },
        
        async startScanCamera() {
            this.scanCameraError = false;
            this.scanCameraErrorMsg = '';
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.scanCameraError = true;
                this.scanCameraErrorMsg = 'Caméra non disponible. Utilisez la saisie manuelle.';
                return;
            }
            
            try {
                this.stopScanCamera();
                
                const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                const constraints = {
                    video: {
                        facingMode: isMobile ? "environment" : "user",
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 }
                    }
                };
                
                this.scanVideoStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                const video = this.$refs.scanVideoElement;
                if (!video) throw new Error('Élément vidéo non trouvé');
                
                video.srcObject = this.scanVideoStream;
                
                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => {
                        video.play().then(resolve).catch(reject);
                    };
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout')), 10000);
                });
                
                this.scanCameraActive = true;
                this.startScanScanning();
            } catch (error) {
                console.error('Camera error:', error);
                this.scanCameraError = true;
                const msg = error.message || '';
                if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                    this.scanCameraErrorMsg = 'Permission refusée. Autorisez la caméra.';
                } else if (msg.includes('NotFoundError')) {
                    this.scanCameraErrorMsg = 'Aucune caméra trouvée.';
                } else {
                    this.scanCameraErrorMsg = 'Erreur caméra. Utilisez la saisie manuelle.';
                }
            }
        },
        
        stopScanCamera() {
            this.stopScanScanning();
            if (this.scanVideoStream) {
                this.scanVideoStream.getTracks().forEach(track => track.stop());
                this.scanVideoStream = null;
            }
            this.scanCameraActive = false;
        },
        
        retryScanCamera() {
            this.startScanCamera();
        },
        
        startScanScanning() {
            this.initScanQuagga();
            this.scanScanInterval = setInterval(() => this.scanQRFrameMulti(), 500);
        },
        
        stopScanScanning() {
            if (this.scanScanInterval) {
                clearInterval(this.scanScanInterval);
                this.scanScanInterval = null;
            }
            if (typeof Quagga !== 'undefined') {
                try { Quagga.stop(); } catch(e) {}
            }
        },
        
        initScanQuagga() {
            if (typeof Quagga === 'undefined') return;
            
            try {
                const video = this.$refs.scanVideoElement;
                if (!video) return;
                
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: video,
                        constraints: { facingMode: "environment" }
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "code_39_reader", "upc_reader", "ean_8_reader"]
                    },
                    locate: true
                }, (err) => {
                    if (!err) Quagga.start();
                });
                
                Quagga.onDetected((result) => {
                    if (result?.codeResult?.code) {
                        this.onScanCodeDetected(result.codeResult.code.trim());
                    }
                });
            } catch (error) {
                console.error('Erreur Quagga:', error);
            }
        },
        
        scanQRFrameMulti() {
            try {
                const video = this.$refs.scanVideoElement;
                const canvas = this.$refs.scanCanvasElement;
                if (!video || !canvas || !video.videoWidth) return;
                
                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);
                
                if (typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height);
                    if (qrResult?.data) {
                        this.onScanCodeDetected(qrResult.data.trim());
                    }
                }
            } catch (error) {}
        },
        
        onScanCodeDetected(code) {
            const now = Date.now();
            if (this.scanLastDetection && (now - this.scanLastDetection.time < 2000) && this.scanLastDetection.code === code) {
                return;
            }
            this.scanLastDetection = { code, time: now };
            
            this.scanCode = code;
            this.processScan();
        },

        async processScan() {
            if (!this.scanCode || this.scanning) return;

            this.scanning = true;

            try {
                const response = await fetch('{{ route("deliverer.scan.multi.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        code: this.scanCode,
                        action: this.selectedAction,
                        scanned_ids: this.scannedPackages.map(p => p.id)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Ajouter le colis à la liste
                    this.scannedPackages.push(data.package);
                    this.showToast('success', 'Colis ajouté !', data.message);
                    this.playSuccessSound();
                    this.scanCode = '';
                    
                    // Refocus sur l'input
                    setTimeout(() => {
                        if (this.$refs.scanInput) {
                            this.$refs.scanInput.focus();
                        }
                    }, 100);
                } else {
                    this.showToast('error', 'Erreur', data.message);
                    this.playErrorSound();
                }
            } catch (error) {
                this.showToast('error', 'Erreur', 'Erreur de connexion au serveur');
                this.playErrorSound();
            }

            this.scanning = false;
        },

        removePackage(index) {
            this.scannedPackages.splice(index, 1);
            this.showToast('success', 'Colis retiré', 'Le colis a été retiré de la liste');
        },

        async validateScan() {
            if (this.scannedPackages.length === 0 || this.validating) return;

            if (!confirm(`Confirmer la validation de ${this.scannedPackages.length} colis ?`)) {
                return;
            }

            this.validating = true;

            try {
                const response = await fetch('{{ route("deliverer.scan.multi.validate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: this.selectedAction,
                        package_ids: this.scannedPackages.map(p => p.id)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('success', 'Validé !', data.message);
                    this.playSuccessSound();
                    
                    // Rediriger après 2 secondes
                    setTimeout(() => {
                        window.location.href = '{{ route("deliverer.run.sheet") }}';
                    }, 2000);
                } else {
                    this.showToast('error', 'Erreur', data.message);
                    this.playErrorSound();
                }
            } catch (error) {
                this.showToast('error', 'Erreur', 'Erreur de connexion au serveur');
                this.playErrorSound();
            }

            this.validating = false;
        },

        resetAll() {
            if (this.scannedPackages.length > 0) {
                if (!confirm('Êtes-vous sûr de vouloir tout réinitialiser ?')) {
                    return;
                }
            }
            this.actionSelected = false;
            this.selectedAction = null;
            this.scannedPackages = [];
        },

        showToast(type, message, detail = '') {
            this.toast = { show: true, type, message, detail };
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        playSuccessSound() {
            try {
                this.successAudio.play();
            } catch (e) {}
        },

        playErrorSound() {
            try {
                this.errorAudio.play();
            } catch (e) {}
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-TN', { 
                style: 'currency', 
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount);
        }
    }
}
</script>
@endsection
