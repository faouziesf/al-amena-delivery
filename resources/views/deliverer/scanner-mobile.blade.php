@extends('layouts.deliverer')

@section('title', 'Scanner Mobile')

@section('content')
<div class="min-h-screen bg-gray-900" x-data="mobileScannerApp()" x-init="init()">
    
    <!-- Header -->
    <div class="bg-blue-600 text-white px-4 py-4 safe-top">
        <div class="flex items-center justify-between">
            <a href="{{ route('deliverer.run.sheet') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-xl font-bold">Scanner Colis</h1>
            <button @click="switchMode()" class="text-sm bg-blue-500 px-3 py-2 rounded-lg">
                <span x-show="mode === 'camera'">Manuel</span>
                <span x-show="mode === 'manual'">Caméra</span>
            </button>
        </div>
    </div>

    <!-- Mode Caméra -->
    <div x-show="mode === 'camera'" class="relative" style="height: 70vh;">
        
        <!-- Video Element -->
        <video x-ref="video" 
               class="w-full h-full object-cover" 
               autoplay 
               playsinline 
               muted
               x-show="cameraActive"></video>
        
        <!-- Canvas caché pour capture -->
        <canvas x-ref="canvas" class="hidden"></canvas>
        
        <!-- Overlay Scanning -->
        <div x-show="cameraActive" class="absolute inset-0 pointer-events-none">
            <!-- Zone de scan -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative w-72 h-72">
                    <!-- Coins -->
                    <div class="absolute top-0 left-0 w-12 h-12 border-t-4 border-l-4 border-white rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-12 h-12 border-t-4 border-r-4 border-white rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-12 h-12 border-b-4 border-l-4 border-white rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-12 h-12 border-b-4 border-r-4 border-white rounded-br-lg"></div>
                    
                    <!-- Ligne de scan animée -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-green-400 shadow-lg animate-scan"></div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="absolute top-4 left-0 right-0 text-center">
                <div class="inline-block bg-black/70 px-4 py-2 rounded-full">
                    <p class="text-white text-sm font-medium">Placez le QR code dans le cadre</p>
                </div>
            </div>
        </div>
        
        <!-- Bouton Start/Stop Camera -->
        <div class="absolute bottom-6 left-0 right-0 flex justify-center space-x-3">
            <button @click="startCamera()" 
                    x-show="!cameraActive"
                    class="px-8 py-4 bg-green-600 text-white rounded-2xl font-bold text-lg shadow-2xl">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Activer Caméra
            </button>
            
            <button @click="stopCamera()" 
                    x-show="cameraActive"
                    class="px-8 py-4 bg-red-600 text-white rounded-2xl font-bold text-lg shadow-2xl">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Arrêter
            </button>

            <button @click="switchCamera()" 
                    x-show="cameraActive && cameras.length > 1"
                    class="px-4 py-4 bg-blue-600 text-white rounded-2xl font-bold shadow-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
        
        <!-- Message d'erreur -->
        <div x-show="error" class="absolute inset-0 flex items-center justify-center bg-gray-900">
            <div class="text-center p-6 max-w-md">
                <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-white font-bold mb-2">Erreur Caméra</p>
                <p class="text-gray-400 text-sm mb-4" x-text="errorMsg"></p>
                <div class="space-y-2">
                    <button @click="requestPermissions()" class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold">
                        Autoriser la Caméra
                    </button>
                    <button @click="mode = 'manual'; error = false" class="w-full px-6 py-3 bg-gray-700 text-white rounded-xl font-semibold">
                        Passer en Mode Manuel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode Manuel -->
    <div x-show="mode === 'manual'" class="p-6" style="min-height: 70vh;">
        <div class="max-w-md mx-auto">
            <div class="bg-gray-800 rounded-2xl p-8 mb-6">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Saisie Manuelle</h2>
                    <p class="text-gray-400">Entrez le code du colis</p>
                </div>

                <div class="space-y-4">
                    <input type="text" 
                           x-model="manualCode" 
                           @keydown.enter="scanManual()"
                           placeholder="PKG_XXXXX ou code-barres"
                           class="w-full px-6 py-4 bg-gray-700 border-2 border-gray-600 text-white text-center text-xl font-mono rounded-xl focus:border-blue-500 focus:outline-none"
                           autofocus>
                    
                    <button @click="scanManual()" 
                            :disabled="!manualCode || scanning"
                            :class="!manualCode || scanning ? 'bg-gray-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                            class="w-full px-6 py-4 text-white rounded-xl font-bold text-lg transition-colors">
                        <span x-show="!scanning">Valider</span>
                        <span x-show="scanning">
                            <svg class="animate-spin w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Vérification...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-gray-800 rounded-xl p-4">
                <h3 class="text-white font-semibold mb-3 flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Formats acceptés
                </h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Code QR: PKG_XXXXX
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        Code-barres: 123456789
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Résultat du Scan -->
    <div x-show="result" 
         class="fixed inset-x-0 bottom-0 bg-white rounded-t-3xl shadow-2xl p-6 safe-bottom transform transition-transform duration-300"
         :class="result ? 'translate-y-0' : 'translate-y-full'">
        
        <div class="text-center mb-4">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full flex items-center justify-center"
                 :class="result?.success ? 'bg-green-100' : 'bg-red-100'">
                <svg class="w-8 h-8" :class="result?.success ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="result?.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    <path x-show="!result?.success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2" :class="result?.success ? 'text-green-600' : 'text-red-600'" x-text="result?.message"></h3>
            <p x-show="result?.package_code" class="text-gray-600 font-mono" x-text="result?.package_code"></p>
        </div>

        <div class="space-y-3">
            <button x-show="result?.success" 
                    @click="goToPackage()"
                    class="w-full py-4 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700">
                Voir le Colis
            </button>
            
            <button @click="resetScan()" 
                    class="w-full py-4 bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300">
                Scanner un Autre Colis
            </button>
        </div>
    </div>

</div>

<style>
@keyframes scan {
    0% { top: 0; }
    50% { top: calc(100% - 4px); }
    100% { top: 0; }
}
.animate-scan {
    animation: scan 2s ease-in-out infinite;
}
</style>

<script>
function mobileScannerApp() {
    return {
        mode: 'camera', // 'camera' ou 'manual'
        cameraActive: false,
        scanning: false,
        error: false,
        errorMsg: '',
        manualCode: '',
        result: null,
        stream: null,
        cameras: [],
        currentCameraIndex: 0,
        scanInterval: null,

        async init() {
            // Détecter les caméras disponibles
            await this.detectCameras();
            
            // Si mobile, démarrer caméra automatiquement
            if (this.isMobile()) {
                setTimeout(() => this.startCamera(), 500);
            }
        },

        isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },

        async detectCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.cameras = devices.filter(device => device.kind === 'videoinput');
                console.log('Caméras détectées:', this.cameras.length);
            } catch (error) {
                console.error('Erreur détection caméras:', error);
            }
        },

        async requestPermissions() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                stream.getTracks().forEach(track => track.stop());
                this.error = false;
                this.startCamera();
            } catch (error) {
                console.error('Permission refusée:', error);
                showToast('Autorisation caméra refusée', 'error');
            }
        },

        async startCamera() {
            try {
                this.error = false;
                this.errorMsg = '';

                // Arrêter stream existant
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }

                // Configuration pour mobile (caméra arrière)
                const constraints = {
                    video: {
                        facingMode: this.cameras.length > 1 ? 
                            (this.currentCameraIndex === 0 ? 'environment' : 'user') : 
                            'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };

                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;

                // Démarrer le scan automatique
                this.startScanning();

                showToast('Caméra activée', 'success', 2000);
                haptic('light');

            } catch (error) {
                console.error('Erreur caméra:', error);
                this.error = true;
                
                if (error.name === 'NotAllowedError') {
                    this.errorMsg = 'Autorisation caméra refusée. Veuillez autoriser l\'accès dans les paramètres.';
                } else if (error.name === 'NotFoundError') {
                    this.errorMsg = 'Aucune caméra trouvée sur cet appareil.';
                } else {
                    this.errorMsg = 'Impossible d\'accéder à la caméra. ' + error.message;
                }
                
                showToast(this.errorMsg, 'error');
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
            this.cameraActive = false;
            haptic('light');
        },

        async switchCamera() {
            if (this.cameras.length > 1) {
                this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
                await this.startCamera();
            }
        },

        startScanning() {
            // Scanner toutes les 500ms
            this.scanInterval = setInterval(() => {
                this.captureAndScan();
            }, 500);
        },

        async captureAndScan() {
            if (!this.cameraActive || this.scanning) return;

            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            
            if (!video || !canvas || video.readyState !== video.HAVE_ENOUGH_DATA) return;

            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            try {
                // Essayer de détecter QR code avec jsQR
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code && code.data) {
                    this.scanning = true;
                    await this.processCode(code.data);
                }
            } catch (error) {
                console.error('Erreur scan:', error);
            }
        },

        async scanManual() {
            if (!this.manualCode || this.scanning) return;
            
            this.scanning = true;
            await this.processCode(this.manualCode);
        },

        async processCode(code) {
            try {
                haptic('medium');
                
                // Envoyer à l'API
                const response = await fetch('/deliverer/api/scan/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await response.json();

                this.result = data;

                if (data.success) {
                    showToast('Colis trouvé !', 'success');
                    haptic('success');
                    this.stopCamera();
                } else {
                    showToast(data.message || 'Colis non trouvé', 'error');
                    haptic('error');
                    
                    // Réessayer après 2 secondes
                    setTimeout(() => {
                        this.result = null;
                        this.scanning = false;
                    }, 2000);
                }

            } catch (error) {
                console.error('Erreur traitement:', error);
                showToast('Erreur de vérification', 'error');
                this.scanning = false;
            }
        },

        resetScan() {
            this.result = null;
            this.scanning = false;
            this.manualCode = '';
            
            if (this.mode === 'camera') {
                this.startCamera();
            }
        },

        goToPackage() {
            if (this.result && this.result.package_id) {
                window.location.href = `/deliverer/packages/${this.result.package_id}`;
            }
        },

        switchMode() {
            if (this.mode === 'camera') {
                this.stopCamera();
                this.mode = 'manual';
            } else {
                this.mode = 'camera';
                this.startCamera();
            }
        }
    }
}
</script>

<!-- jsQR pour scanner QR codes -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

@endsection
