@extends('layouts.deliverer-modern')

@section('title', 'Scanner Colis')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    #camera-container {
        position: relative;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        background: #000;
    }
    #camera-video {
        width: 100%;
        height: auto;
        display: block;
    }
    #qr-canvas {
        display: none;
    }
    .scan-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
    }
    .scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        height: 60%;
        border: 3px solid #10B981;
        border-radius: 1rem;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
    }
    .scan-line {
        position: absolute;
        top: 50%;
        left: 10%;
        right: 10%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #10B981, #10B981, transparent);
        animation: scan 2s ease-in-out infinite;
        box-shadow: 0 0 10px #10B981;
    }
    @keyframes scan {
        0% { transform: translateY(-150px); }
        100% { transform: translateY(150px); }
    }
    .scan-mode-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(16, 185, 129, 0.9);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: pulse-badge 2s ease-in-out infinite;
    }
    @keyframes pulse-badge {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-slideDown {
        animation: slideDown 0.4s ease-out;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="singleScannerApp()" x-init="init()">
    
    <!-- Header avec safe-area iOS -->
    <div class="relative" style="padding-top: env(safe-area-inset-top, 0px);">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-600"></div>
        <div class="relative px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('deliverer.menu') }}" class="p-2 bg-white/20 rounded-xl hover:bg-white/30 transition-all">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white">📦 Scanner</h1>
                        <p class="text-white/80 text-sm font-medium" x-text="statusText">Prêt</p>
                    </div>
                </div>
                <button @click="toggleCamera()" 
                        :class="cameraActive ? 'bg-green-500' : 'bg-white/20'"
                        class="p-4 rounded-2xl transition-all">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Messages session avec safe-area iOS -->
    @if(session('success'))
    <div class="mx-4 mt-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-4 rounded-2xl shadow-lg animate-slideDown">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mx-4 mt-4 bg-gradient-to-r from-red-500 to-red-600 text-white px-5 py-4 rounded-2xl shadow-lg animate-slideDown">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="p-4 pb-32">

        <!-- Caméra Améliorée -->
        <div x-show="cameraActive" class="mb-6" x-transition>
            <div id="camera-container">
                <video id="camera-video" autoplay playsinline></video>
                <canvas id="qr-canvas"></canvas>
                <div class="scan-overlay">
                    <div class="scan-frame"></div>
                    <div class="scan-line"></div>
                </div>
                <div class="scan-mode-badge">
                    <span x-show="scanMode === 'qr'">📱 QR Code</span>
                    <span x-show="scanMode === 'barcode'">📊 Code-Barres</span>
                </div>
                <div class="absolute top-4 left-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                        <span>🎥 Caméra Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saisie manuelle -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="font-bold text-gray-800 mb-3">📝 Saisir un Code</h3>
            <form id="scan-form" action="{{ route('deliverer.scan.submit') }}" method="POST">
                @csrf
                <input 
                    type="text"
                    name="code"
                    x-model="currentCode"
                    @input="validateCode()"
                    @keydown.enter.prevent="submitCode()"
                    placeholder="CODE DU COLIS"
                    class="w-full text-2xl font-bold text-center tracking-wider px-6 py-4 border-3 rounded-2xl focus:ring-4 focus:border-indigo-600 outline-none transition-all mb-3"
                    :class="getInputClass()"
                    autofocus
                >
                <div class="text-center min-h-6 mb-3">
                    <span x-show="codeStatus === 'valid'" class="text-green-600 font-bold text-sm">
                        ✅ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'not_found'" class="text-red-600 font-bold text-sm">
                        ❌ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'invalid'" class="text-orange-600 font-bold text-sm">
                        ⚠️ <span x-text="statusMessage"></span>
                    </span>
                </div>
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-95">
                    🔍 Rechercher
                </button>
            </form>
        </div>

        <!-- Boutons -->
        <div class="space-y-2">
            <a href="{{ route('deliverer.scan.multi') }}" 
               class="block w-full bg-indigo-50 text-indigo-700 text-center py-3 rounded-xl font-semibold hover:bg-indigo-100 transition-colors">
                📸 Scanner Multiple
            </a>
            <a href="{{ route('deliverer.menu') }}" 
               class="block w-full bg-gray-100 text-gray-700 text-center py-3 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                ← Retour au menu
            </a>
        </div>
    </div>
</div>

<!-- Quagga pour codes-barres -->
<script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
<!-- jsQR pour QR codes -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<!-- Sons -->
<audio id="scan-success-sound" src="/sounds/success.mp3" preload="auto"></audio>
<audio id="scan-error-sound" src="/sounds/error.mp3" preload="auto"></audio>

<script>
const PACKAGES_DATA = @json($packages ?? []);

function singleScannerApp() {
    return {
        cameraActive: false,
        currentCode: '',
        statusText: 'Prêt',
        packagesMap: null,
        codeStatus: '',
        statusMessage: '',
        validationTimeout: null,
        videoStream: null,
        scanInterval: null,
        lastScanTime: 0,
        lastScannedCode: '',
        scanMode: 'barcode',
        scanCycle: 0,

        init() {
            console.log('✅ Scanner simple initialisé');
            this.packagesMap = new Map();
            
            PACKAGES_DATA.forEach(pkg => {
                const packageData = {
                    code: pkg.c,
                    status: pkg.s,
                    can_pickup: pkg.p === 1,
                    can_deliver: pkg.d === 1,
                    id: pkg.id,
                    assigned: pkg.assigned === 1
                };
                
                this.packagesMap.set(pkg.c, packageData);
                if (pkg.c2 && pkg.c2 !== pkg.c) {
                    this.packagesMap.set(pkg.c2, packageData);
                }
                const noUnderscore = pkg.c.replace(/_/g, '');
                if (noUnderscore !== pkg.c) {
                    this.packagesMap.set(noUnderscore, packageData);
                }
            });
            
            console.log(`📦 ${PACKAGES_DATA.length} colis chargés`);
        },

        validateCode() {
            const code = this.currentCode.trim().toUpperCase();
            
            if (!code) {
                this.codeStatus = '';
                this.statusMessage = '';
                return;
            }
            
            if (this.validationTimeout) {
                clearTimeout(this.validationTimeout);
            }
            
            this.validationTimeout = setTimeout(() => {
                this.checkCode(code);
            }, 300);
        },

        checkCode(code) {
            if (code.length < 3) {
                this.codeStatus = 'invalid';
                this.statusMessage = 'Code trop court';
                return;
            }
            
            let packageData = this.packagesMap.get(code);
            
            if (!packageData) {
                const noUnderscore = code.replace(/_/g, '');
                packageData = this.packagesMap.get(noUnderscore);
            }
            
            if (!packageData) {
                const cleaned = code.replace(/[_\-\s]/g, '');
                packageData = this.packagesMap.get(cleaned);
            }
            
            if (!packageData) {
                this.codeStatus = 'not_found';
                this.statusMessage = 'Colis non trouvé';
                return;
            }
            
            this.codeStatus = 'valid';
            const assignInfo = packageData.assigned ? 'Assigné' : 'Non assigné';
            this.statusMessage = `Colis valide (${packageData.status}) - ${assignInfo}`;
        },

        getInputClass() {
            if (!this.currentCode) return 'border-gray-300';
            switch(this.codeStatus) {
                case 'valid':
                    return 'border-green-500 ring-4 ring-green-100';
                case 'invalid':
                case 'not_found':
                    return 'border-red-500 ring-4 ring-red-100';
                default:
                    return 'border-gray-300';
            }
        },

        submitCode() {
            if (this.codeStatus === 'valid') {
                document.getElementById('scan-form').submit();
            }
        },

        async toggleCamera() {
            if (this.cameraActive) {
                this.stopCamera();
            } else {
                await this.startCamera();
            }
        },

        async startCamera() {
            try {
                const video = document.getElementById('camera-video');
                const canvas = document.getElementById('qr-canvas');
                
                if (!video || !canvas) {
                    console.error('Élément vidéo ou canvas non trouvé');
                    return;
                }

                this.statusText = 'Démarrage caméra...';
                showToast('🎥 Démarrage de la caméra...', 'info');

                this.videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920, max: 1920 },
                        height: { ideal: 1080, max: 1080 }
                    }
                });
                
                video.srcObject = this.videoStream;
                
                // Attendre que la vidéo soit prête
                await new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        video.play();
                        resolve();
                    };
                });

                this.cameraActive = true;
                this.statusText = '📷 Scan actif';
                
                this.startHybridScanning();
                showToast('✅ Caméra prête !', 'success');
                console.log('✅ Caméra démarrée avec succès');
                
            } catch (error) {
                console.error('Erreur caméra:', error);
                showToast('❌ Erreur caméra: ' + error.message, 'error');
                this.statusText = 'Erreur caméra';
            }
        },

        startHybridScanning() {
            console.log('🔄 Démarrage scan hybride QR + Barcode');
            
            // QR Code prioritaire (plus rapide)
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                if (this.scanCycle % 3 !== 2) {
                    this.scanMode = 'qr';
                    this.scanQRCode();
                } else {
                    this.scanMode = 'barcode';
                }
            }, 300);
            
            // Démarrer Quagga en parallèle
            setTimeout(() => {
                this.initQuagga();
            }, 500);
        },

        initQuagga() {
            if (typeof Quagga === 'undefined') {
                console.warn('⚠️ Quagga non chargé - scan code-barres désactivé');
                return;
            }

            try {
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: document.getElementById('camera-video'),
                        constraints: {
                            width: { ideal: 1920 },
                            height: { ideal: 1080 },
                            facingMode: "environment"
                        }
                    },
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader",
                            "upc_reader"
                        ],
                        multiple: false
                    },
                    locate: true,
                    locator: {
                        patchSize: "large",
                        halfSample: false
                    },
                    numOfWorkers: 2,
                    frequency: 5
                }, (err) => {
                    if (err) {
                        console.error('❌ Erreur Quagga:', err);
                        return;
                    }
                    Quagga.start();
                    console.log('✅ Quagga (code-barres) démarré');
                });

                Quagga.onDetected((result) => {
                    if (this.scanMode === 'barcode' && result?.codeResult?.code) {
                        const code = result.codeResult.code.trim();
                        
                        // Vérifier qualité
                        const errors = result.codeResult.decodedCodes
                            .filter(x => x.error !== undefined)
                            .map(x => x.error);
                        const avgError = errors.length > 0 
                            ? errors.reduce((a, b) => a + b, 0) / errors.length 
                            : 0;
                        
                        if (avgError > 0.2) {
                            console.log('⚠️ Code-barres ignoré (qualité: ' + avgError.toFixed(3) + ')');
                            return;
                        }
                        
                        if (code.length >= 4) {
                            console.log('✅ Code-barres détecté:', code, '(qualité:', avgError.toFixed(3), ')');
                            this.handleScannedCode(code);
                        }
                    }
                });
            } catch (error) {
                console.error('❌ Erreur init Quagga:', error);
            }
        },

        scanQRCode() {
            if (typeof jsQR === 'undefined') return;
            
            try {
                const video = document.getElementById('camera-video');
                const canvas = document.getElementById('qr-canvas');
                
                if (!video || !canvas || !video.videoWidth) return;

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const qrCode = jsQR(imageData.data, imageData.width, imageData.height);

                if (qrCode?.data) {
                    const code = qrCode.data.trim();
                    if (code) {
                        this.handleScannedCode(code);
                    }
                }
            } catch (error) {
                // Ignorer
            }
        },

        handleScannedCode(rawCode) {
            const now = Date.now();
            let code = this.extractCleanCode(rawCode);
            
            if (!code || code.length < 3) return;
            
            if (code === this.lastScannedCode && (now - this.lastScanTime) < 2000) {
                return;
            }
            
            this.lastScannedCode = code;
            this.lastScanTime = now;
            
            console.log('📦 Scanné:', code);
            
            // Vérifier le code
            let packageData = this.packagesMap.get(code);
            
            if (!packageData) {
                const noUnderscore = code.replace(/_/g, '');
                packageData = this.packagesMap.get(noUnderscore);
            }
            
            if (!packageData) {
                const cleaned = code.replace(/[_\-\s]/g, '');
                packageData = this.packagesMap.get(cleaned);
            }
            
            if (packageData) {
                this.currentCode = packageData.code;
                this.statusText = `✅ ${code} scanné`;
                showToast(`✅ ${code}`, 'success');
                this.playSound('success');
                if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
                
                // Soumettre automatiquement
                setTimeout(() => {
                    document.getElementById('scan-form').submit();
                }, 500);
            } else {
                showToast(`❌ ${code} - Non trouvé`, 'error');
                this.playSound('error');
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
            }
        },

        extractCleanCode(rawCode) {
            let code = rawCode.trim();
            
            if (code.includes('/track/')) {
                const match = code.match(/\/track\/([A-Za-z0-9_-]+)/);
                if (match && match[1]) {
                    code = match[1];
                }
            }
            
            if (code.includes('?')) {
                code = code.split('?')[0];
            }
            
            code = code.toUpperCase().replace(/\s+/g, '');
            
            return code;
        },

        stopCamera() {
            try {
                if (this.scanInterval) {
                    clearInterval(this.scanInterval);
                    this.scanInterval = null;
                }
                
                if (typeof Quagga !== 'undefined') {
                    try { Quagga.stop(); } catch(e) {}
                }
                
                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(track => track.stop());
                    this.videoStream = null;
                }
                
                const video = document.getElementById('camera-video');
                if (video) {
                    video.srcObject = null;
                }
                
                this.cameraActive = false;
                this.statusText = 'Caméra arrêtée';
                
            } catch (error) {
                console.error('Erreur arrêt caméra:', error);
            }
        },

        playSound(type) {
            try {
                const sound = document.getElementById(`scan-${type}-sound`);
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(() => {});
                }
            } catch (e) {}
        }
    }
}
</script>
@endsection
