@extends('layouts.deliverer')

@section('title', 'Scanner')

@section('content')
<style>
    .camera-preview {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .camera-preview video {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .scan-line {
        position: absolute;
        left: 10%;
        right: 10%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #3B82F6, transparent);
        animation: scan 2s ease-in-out infinite;
        z-index: 10;
    }
    
    @keyframes scan {
        0%, 100% { top: 20%; opacity: 0; }
        50% { top: 80%; opacity: 1; }
    }
    
    .scan-frame {
        position: absolute;
        top: 15%;
        left: 15%;
        right: 15%;
        bottom: 15%;
        border: 3px solid #3B82F6;
        border-radius: 12px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        z-index: 5;
    }
    
    .scan-corners {
        position: absolute;
        inset: 0;
    }
    
    .corner {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 4px solid #3B82F6;
    }
    
    .corner.tl { top: 0; left: 0; border-right: none; border-bottom: none; }
    .corner.tr { top: 0; right: 0; border-left: none; border-bottom: none; }
    .corner.bl { bottom: 0; left: 0; border-right: none; border-top: none; }
    .corner.br { bottom: 0; right: 0; border-left: none; border-top: none; }
</style>

<div class="max-w-4xl mx-auto px-4 py-8" x-data="scannerApp()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('deliverer.run.sheet') }}" class="mr-4 p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Scanner un Colis</h1>
            <p class="text-gray-600 text-sm">Scannez le QR code ou code-barres</p>
        </div>
    </div>

    <!-- Camera Preview -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div x-show="!cameraActive && !error" class="text-center py-12">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <button @click="startCamera()" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700">
                Activer la Caméra
            </button>
        </div>

        <div x-show="cameraActive" class="camera-preview">
            <video x-ref="video" autoplay playsinline muted></video>
            <canvas x-ref="canvas" style="display:none;"></canvas>
            <div class="scan-frame">
                <div class="scan-corners">
                    <div class="corner tl"></div>
                    <div class="corner tr"></div>
                    <div class="corner bl"></div>
                    <div class="corner br"></div>
                </div>
            </div>
            <div class="scan-line" x-show="scanning"></div>
        </div>

        <div x-show="error" class="text-center py-12">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-red-600 mb-4" x-text="errorMessage"></p>
            <button @click="startCamera()" class="bg-red-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-red-700">
                Réessayer
            </button>
        </div>
    </div>

    <!-- Manual Input -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center mb-4">
            <div class="flex-1 border-t"></div>
            <span class="px-4 text-gray-500 text-sm">ou saisir manuellement</span>
            <div class="flex-1 border-t"></div>
        </div>
        <form @submit.prevent="manualScan()" class="flex gap-3">
            <input type="text" 
                   x-model="manualCode"
                   class="flex-1 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"
                   placeholder="PKG_12345 ou code du colis">
            <button type="submit" 
                    :disabled="!manualCode"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 disabled:bg-gray-400">
                Rechercher
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

<script>
function scannerApp() {
    return {
        cameraActive: false,
        scanning: false,
        error: false,
        errorMessage: '',
        manualCode: '',
        videoStream: null,
        scanInterval: null,
        lastScanTime: 0,
        quaggaInitialized: false,

        init() {
            // Auto-start camera
            setTimeout(() => this.startCamera(), 500);
        },

        async startCamera() {
            this.error = false;
            this.errorMessage = '';
            
            try {
                this.stopCamera();
                
                const constraints = {
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                const video = this.$refs.video;
                video.srcObject = this.videoStream;
                
                await video.play();
                
                this.cameraActive = true;
                this.scanning = true;
                
                // Start scanning
                this.startScanning();
                
            } catch (err) {
                console.error('Camera error:', err);
                this.error = true;
                this.errorMessage = this.getCameraError(err);
            }
        },

        stopCamera() {
            this.stopScanning();
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            this.cameraActive = false;
            this.scanning = false;
        },

        startScanning() {
            // Initialize Quagga for barcodes
            this.initQuagga();
            
            // Start QR code scanning
            this.scanInterval = setInterval(() => {
                this.scanQRCode();
            }, 500);
        },

        stopScanning() {
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
            
            if (this.quaggaInitialized && typeof Quagga !== 'undefined') {
                try {
                    Quagga.stop();
                    this.quaggaInitialized = false;
                } catch (e) {
                    console.error('Error stopping Quagga:', e);
                }
            }
        },

        initQuagga() {
            if (typeof Quagga === 'undefined' || this.quaggaInitialized) return;

            const video = this.$refs.video;
            
            try {
                Quagga.init({
                    inputStream: {
                        type: 'LiveStream',
                        target: video,
                        constraints: {
                            facingMode: 'environment'
                        }
                    },
                    decoder: {
                        readers: ['code_128_reader', 'ean_reader', 'ean_8_reader', 'code_39_reader', 'upc_reader']
                    },
                    locate: true
                }, (err) => {
                    if (!err) {
                        Quagga.start();
                        this.quaggaInitialized = true;
                    }
                });

                Quagga.onDetected((result) => {
                    if (result && result.codeResult && result.codeResult.code) {
                        this.onCodeDetected(result.codeResult.code);
                    }
                });
            } catch (e) {
                console.error('Quagga init error:', e);
            }
        },

        scanQRCode() {
            if (typeof jsQR === 'undefined') return;
            
            try {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                
                if (!video.videoWidth || !video.videoHeight) return;
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code && code.data) {
                    this.onCodeDetected(code.data);
                }
            } catch (e) {
                console.error('QR scan error:', e);
            }
        },

        onCodeDetected(code) {
            const now = Date.now();
            if (now - this.lastScanTime < 2000) return;
            
            this.lastScanTime = now;
            this.processScan(code);
        },

        async processScan(code) {
            this.scanning = false;
            this.stopCamera();

            try {
                const response = await fetch('{{ route("deliverer.scan.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ qr_code: code })
                });

                const data = await response.json();

                if (data.success && data.redirect) {
                    if (navigator.vibrate) navigator.vibrate(200);
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Code non reconnu');
                    this.startCamera();
                }
            } catch (err) {
                alert('Erreur de connexion');
                this.startCamera();
            }
        },

        async manualScan() {
            if (!this.manualCode) return;
            await this.processScan(this.manualCode);
        },

        getCameraError(err) {
            const msg = err.message || err.toString();
            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Veuillez autoriser l\'accès à la caméra.';
            }
            if (msg.includes('NotFoundError')) {
                return 'Aucune caméra trouvée sur cet appareil.';
            }
            return 'Erreur caméra. Veuillez utiliser la saisie manuelle.';
        }
    }
}
</script>
@endsection
