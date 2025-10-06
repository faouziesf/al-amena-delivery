@extends('layouts.deliverer')

@section('title', 'Scanner')

@section('content')
<div class="min-h-screen bg-gray-900" x-data="cameraScannerApp()" x-init="init()">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <a href="{{ route('deliverer.run.sheet') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-bold">Scanner</h1>
            <a href="{{ route('deliverer.scan.multi') }}" class="text-sm bg-blue-500 px-3 py-1 rounded-lg">Multiple</a>
        </div>
    </div>

    <!-- Camera View -->
    <div class="relative bg-black" style="height: 60vh;">
        <video x-ref="videoElement" class="w-full h-full object-cover" autoplay playsinline muted x-show="cameraActive"></video>
        <canvas x-ref="canvasElement" class="hidden"></canvas>
        
        <!-- Scanning Overlay -->
        <div x-show="cameraActive" class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 m-auto w-64 h-64 border-4 border-blue-500 rounded-lg">
                <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white"></div>
                <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white"></div>
                <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white"></div>
                <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white"></div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-3">
            <button @click="startCamera()" x-show="!cameraActive"
                    class="px-6 py-3 bg-green-600 text-white rounded-xl font-semibold">
                Activer Caméra
            </button>
            <button @click="stopCamera()" x-show="cameraActive"
                    class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold">
                Arrêter
            </button>
        </div>
        
        <!-- Error -->
        <div x-show="cameraError" class="absolute inset-0 flex items-center justify-center bg-gray-900">
            <div class="text-center p-6">
                <p class="text-white mb-2">Erreur Caméra</p>
                <p class="text-gray-400 text-sm mb-4" x-text="cameraErrorMsg"></p>
                <button @click="retryCamera()" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Réessayer</button>
            </div>
        </div>
    </div>

    <!-- Manual Input & Result -->
    <div class="p-4 bg-gray-800">
        <div class="mb-4">
            <label class="block text-white font-semibold mb-2">Saisie Manuelle</label>
            <div class="flex space-x-2">
                <input type="text" x-model="manualCode" @keydown.enter="processManualCode()"
                       class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-xl font-mono"
                       placeholder="PKG_12345...">
                <button @click="processManualCode()" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold">
                    Scanner
                </button>
            </div>
        </div>

        <!-- Last Result -->
        <div x-show="lastResult" class="bg-gray-700 rounded-xl p-4" :class="lastResult?.success ? 'border-2 border-green-500' : 'border-2 border-red-500'">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="lastResult?.success ? 'bg-green-500' : 'bg-red-500'">
                    <span class="text-white text-xl" x-text="lastResult?.success ? '✓' : '✕'"></span>
                </div>
                <div class="flex-1">
                    <p class="text-white font-semibold" x-text="lastResult?.message"></p>
                    <p x-show="lastResult?.package" class="text-gray-300 text-sm font-mono" x-text="lastResult?.package?.tracking_number"></p>
                </div>
            </div>
            <button x-show="lastResult?.success && lastResult?.redirect" @click="goToPackage()" 
                    class="w-full mt-3 bg-blue-600 text-white py-2 rounded-lg font-semibold">
                Voir le Colis
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
function cameraScannerApp() {
    return {
        cameraActive: false,
        cameraError: false,
        cameraErrorMsg: '',
        videoStream: null,
        scanInterval: null,
        manualCode: '',
        lastResult: null,
        lastDetection: null,

        init() {
            // Auto-start camera après chargement complet de la page
            this.$nextTick(() => {
                setTimeout(() => this.startCamera(), 500);
            });
        },

        async startCamera() {
            this.cameraError = false;
            this.cameraErrorMsg = '';
            
            // Vérifier si mediaDevices est disponible
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.cameraError = true;
                this.cameraErrorMsg = 'Votre navigateur ne supporte pas l\'accès à la caméra. Utilisez la saisie manuelle.';
                return;
            }
            
            try {
                this.stopCamera();
                
                const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                const constraints = {
                    video: {
                        facingMode: isMobile ? "environment" : "user",
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 }
                    }
                };
                
                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                const video = this.$refs.videoElement;
                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }
                
                video.srcObject = this.videoStream;
                
                // Attendre que la vidéo soit prête
                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => {
                        video.play().then(resolve).catch(reject);
                    };
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout')), 10000);
                });
                
                this.cameraActive = true;
                this.startScanning();
            } catch (error) {
                console.error('Camera error:', error);
                this.cameraError = true;
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
            }
        },
        
        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();
            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Autorisez l\'accès à la caméra dans les paramètres de votre navigateur.';
            }
            if (msg.includes('NotFoundError') || msg.includes('No camera')) {
                return 'Aucune caméra trouvée sur cet appareil.';
            }
            if (msg.includes('NotReadableError')) {
                return 'La caméra est déjà utilisée par une autre application.';
            }
            if (msg.includes('NotSupported') || msg.includes('not supported')) {
                return 'Votre navigateur ne supporte pas l\'accès à la caméra.';
            }
            return 'Erreur d\'accès à la caméra. Utilisez la saisie manuelle.';
        },

        stopCamera() {
            this.stopScanning();
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            this.cameraActive = false;
        },

        retryCamera() {
            this.startCamera();
        },

        startScanning() {
            this.initQuagga();
            this.scanInterval = setInterval(() => this.scanQRFrame(), 500);
        },

        stopScanning() {
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
            }
            if (typeof Quagga !== 'undefined') {
                Quagga.stop();
            }
        },

        initQuagga() {
            if (typeof Quagga === 'undefined') {
                console.warn('Quagga non chargé, scan code-barres désactivé');
                return;
            }
            
            try {
                const video = this.$refs.videoElement;
                if (!video) return;
                
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: video,
                        constraints: {
                            facingMode: "environment"
                        }
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "code_39_reader", "upc_reader", "ean_8_reader"]
                    },
                    locate: true,
                    locator: {
                        halfSample: true,
                        patchSize: "medium"
                    }
                }, (err) => {
                    if (err) {
                        console.error('Erreur Quagga:', err);
                        return;
                    }
                    Quagga.start();
                });
                
                Quagga.onDetected((result) => {
                    if (result?.codeResult?.code) {
                        this.onCodeDetected(result.codeResult.code.trim());
                    }
                });
            } catch (error) {
                console.error('Erreur initialisation Quagga:', error);
            }
        },

        scanQRFrame() {
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
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height);
                    if (qrResult?.data) {
                        this.onCodeDetected(qrResult.data.trim());
                    }
                }
            } catch (error) {}
        },

        onCodeDetected(code) {
            const now = Date.now();
            if (this.lastDetection && (now - this.lastDetection.time < 3000) && this.lastDetection.code === code) {
                return;
            }
            this.lastDetection = { code, time: now };
            this.processCode(code);
        },

        processManualCode() {
            if (!this.manualCode) return;
            this.processCode(this.manualCode);
            this.manualCode = '';
        },

        async processCode(code) {
            try {
                const response = await fetch('{{ route("deliverer.scan.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ qr_code: code })
                });
                const data = await response.json();
                this.lastResult = data;
                
                if (data.success && data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 2000);
                }
            } catch (error) {
                this.lastResult = { success: false, message: 'Erreur de connexion' };
            }
        },

        goToPackage() {
            if (this.lastResult?.redirect) {
                window.location.href = this.lastResult.redirect;
            }
        }
    }
}
</script>
@endsection
