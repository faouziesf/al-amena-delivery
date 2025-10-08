<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Scanner Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js pour réactivité -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        #camera-video {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
        }
        
        .scan-line {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10B981, transparent);
            animation: scan 2s ease-in-out infinite;
        }
        
        @keyframes scan {
            0%, 100% { transform: translateY(-20px); }
            50% { transform: translateY(20px); }
        }
        
        .code-input {
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            border: 3px solid #667eea;
            border-radius: 0.75rem;
            padding: 1rem;
        }
        
        .code-input:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }
        
        @keyframes flash {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        .flash-success {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(16, 185, 129, 0.3);
            animation: flash 0.3s ease-out;
            pointer-events: none;
            z-index: 9999;
        }
        
        .flash-error {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(239, 68, 68, 0.3);
            animation: flash 0.5s ease-out;
            pointer-events: none;
            z-index: 9999;
        }
    </style>
</head>
<body class="min-h-screen" x-data="depotScannerApp()" x-init="init()">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-4 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">🏭 Scanner Dépôt</h1>
                <p class="text-white/80 text-sm" x-text="statusText">Prêt</p>
            </div>
            <button @click="toggleCamera()" 
                    :class="cameraActive ? 'bg-green-500' : 'bg-white/20'"
                    class="p-3 rounded-xl transition-all">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="p-4 pb-32">
        
        <!-- Stats -->
        <div class="mb-4 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl p-6 text-center shadow-lg">
            <div class="text-4xl font-black text-white mb-1" x-text="scannedCodes.length">0</div>
            <div class="text-sm font-semibold text-white/90">📦 Colis Scannés</div>
            <div class="text-xs text-white/75 mt-1">Arrivée au dépôt</div>
        </div>

        <!-- Caméra -->
        <div x-show="cameraActive" class="mb-4 rounded-2xl overflow-hidden shadow-xl" x-transition>
            <div class="relative bg-black">
                <video id="camera-video" autoplay playsinline></video>
                <canvas id="qr-canvas" style="display:none;"></canvas>
                <div class="scan-line"></div>
                <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-lg text-sm font-bold">
                    🎥 Caméra Active
                </div>
            </div>
        </div>

        <!-- Saisie manuelle -->
        <div class="mb-4 bg-white rounded-2xl p-5 shadow-lg">
            <h3 class="font-bold text-gray-800 mb-3">📝 Saisir un Code</h3>
            <input 
                type="text"
                x-model="currentCode"
                @input="validateCodeFormat()"
                @keydown.enter="addCodeManually()"
                placeholder="Code ou scan..."
                class="w-full code-input transition-all"
                :class="getInputClass()"
            >
            <div class="mt-2 text-center min-h-6">
                <span x-show="currentCode.length > 0 && codeStatus === 'checking'" class="text-blue-600 font-semibold text-sm">
                    🔍 Vérification...
                </span>
                <span x-show="codeStatus === 'valid'" class="text-green-600 font-bold text-sm">
                    ✅ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'not_found'" class="text-red-600 font-bold text-sm">
                    ❌ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'wrong_status'" class="text-orange-600 font-bold text-sm">
                    ⚠️ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'duplicate'" class="text-orange-600 font-bold text-sm">
                    ⚠️ Déjà scanné
                </span>
            </div>
            <button @click="addCodeManually()" 
                    :disabled="codeStatus !== 'valid'"
                    class="w-full mt-3 font-bold py-3 rounded-xl transition-all"
                    :class="codeStatus === 'valid' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500'">
                ➕ Ajouter
            </button>
        </div>

        <!-- Liste des codes -->
        <div x-show="scannedCodes.length > 0" x-transition class="mb-4">
            <div class="bg-white rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">📋 Codes (<span x-text="scannedCodes.length"></span>)</h3>
                    <button @click="clearAll()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-bold">
                        🗑️
                    </button>
                </div>
                
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="(item, index) in scannedCodes" :key="index">
                        <div class="p-3 rounded-lg bg-green-50 border-2 border-green-300 flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center font-bold text-white text-sm">
                                    <span x-text="index + 1"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900" x-text="item.code"></div>
                                    <div class="text-xs text-green-700" x-text="item.message"></div>
                                </div>
                            </div>
                            <button @click="removeCode(index)" class="p-2 bg-red-500 text-white rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Validation -->
        <div x-show="scannedCodes.length > 0" 
             class="fixed left-0 right-0 bottom-0 p-4 bg-white/95 border-t-2 border-purple-200">
            <form id="validation-form" method="POST" action="{{ route('depot.scan.submit', $sessionId) }}">
                @csrf
                <input type="hidden" name="codes" x-bind:value="JSON.stringify(scannedCodes.map(item => item.code))">
                
                <button type="button" @click="submitForm()" 
                        :disabled="processing"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-4 rounded-2xl disabled:opacity-50 shadow-lg">
                    <span x-show="!processing" class="text-lg">
                        ✅ Valider <span x-text="scannedCodes.length"></span> colis
                    </span>
                    <span x-show="processing" class="text-lg">
                        ⏳ Traitement...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <!-- Flash Container -->
    <div id="flash-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    
    <script>
// Données des colis chargées du serveur
const PACKAGES_DATA = @json($packages ?? []);

function depotScannerApp() {
    return {
        // États
        cameraActive: false,
        currentCode: '',
        scannedCodes: [],
        processing: false,
        statusText: 'Prêt',
        
        // Validation locale
        packagesMap: null,
        codeStatus: '',
        statusMessage: '',
        validationTimeout: null,
        
        // Caméra
        videoStream: null,
        scanInterval: null,
        lastScanTime: 0,
        lastScannedCode: '',
        scanMode: 'barcode',
        scanCycle: 0,

        init() {
            console.log('✅ Scanner dépôt initialisé');
            
            // Créer Map pour recherche rapide
            this.packagesMap = new Map();
            
            PACKAGES_DATA.forEach(pkg => {
                const packageData = {
                    code: pkg.c,
                    status: pkg.s,
                    id: pkg.id
                };
                
                // Ajouter avec code principal
                this.packagesMap.set(pkg.c, packageData);
                
                // Ajouter avec code alternatif
                if (pkg.c2 && pkg.c2 !== pkg.c) {
                    this.packagesMap.set(pkg.c2, packageData);
                }
                
                // Ajouter variantes
                const noUnderscore = pkg.c.replace(/_/g, '');
                if (noUnderscore !== pkg.c) {
                    this.packagesMap.set(noUnderscore, packageData);
                }
            });
            
            console.log(`📦 ${PACKAGES_DATA.length} colis chargés (${this.packagesMap.size} clés)`);
        },
        
        // Validation temps réel
        validateCodeFormat() {
            const code = this.currentCode.trim().toUpperCase();
            
            if (!code) {
                this.codeStatus = '';
                this.statusMessage = '';
                return;
            }
            
            this.codeStatus = 'checking';
            
            if (this.validationTimeout) {
                clearTimeout(this.validationTimeout);
            }
            
            this.validationTimeout = setTimeout(() => {
                this.checkCodeInDB(code);
            }, 300);
        },
        
        // Vérifier code dans DB locale
        checkCodeInDB(code) {
            console.log('🔍 Vérification:', code);
            
            if (code.length < 3) {
                this.codeStatus = 'invalid';
                this.statusMessage = 'Code trop court';
                return;
            }
            
            // Vérifier duplicat
            const isDuplicate = this.scannedCodes.find(item => {
                return item.code === code || 
                       item.code.replace(/[_\-\s]/g, '') === code.replace(/[_\-\s]/g, '');
            });
            
            if (isDuplicate) {
                this.codeStatus = 'duplicate';
                this.statusMessage = 'Déjà scanné';
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                return;
            }
            
            // Rechercher avec variantes
            let packageData = this.packagesMap.get(code);
            
            if (!packageData) {
                const noUnderscore = code.replace(/_/g, '');
                packageData = this.packagesMap.get(noUnderscore);
            }
            
            if (!packageData) {
                const noDash = code.replace(/-/g, '');
                packageData = this.packagesMap.get(noDash);
            }
            
            if (!packageData) {
                const cleaned = code.replace(/[_\-\s]/g, '');
                packageData = this.packagesMap.get(cleaned);
            }
            
            if (!packageData) {
                this.codeStatus = 'not_found';
                this.statusMessage = 'Colis non trouvé';
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                console.log('❌ Non trouvé:', code);
                return;
            }
            
            console.log('✅ Colis trouvé:', packageData);
            
            // Vérifier statut (COLLECTED, IN_TRANSIT, PENDING)
            if (!['COLLECTED', 'IN_TRANSIT', 'PENDING'].includes(packageData.status)) {
                this.codeStatus = 'wrong_status';
                this.statusMessage = `Statut invalide: ${packageData.status}`;
                if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                return;
            }
            
            // Valide
            this.codeStatus = 'valid';
            this.statusMessage = `Colis valide (${packageData.status})`;
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
        },
        
        getInputClass() {
            if (!this.currentCode) return 'border-gray-300';
            
            switch(this.codeStatus) {
                case 'checking':
                    return 'border-blue-400 ring-4 ring-blue-100';
                case 'valid':
                    return 'border-green-500 ring-4 ring-green-100';
                case 'invalid':
                case 'not_found':
                case 'duplicate':
                case 'wrong_status':
                    return 'border-red-500 ring-4 ring-red-100';
                default:
                    return 'border-gray-300';
            }
        },

        addCodeManually() {
            const code = this.currentCode.trim().toUpperCase();
            
            if (!code || this.codeStatus !== 'valid') {
                return;
            }
            
            this.scannedCodes.push({
                code: code,
                message: this.statusMessage,
                timestamp: new Date().toISOString()
            });
            
            this.currentCode = '';
            this.codeStatus = '';
            this.statusMessage = '';
            this.showFlash('success');
            this.statusText = `${this.scannedCodes.length} code(s) scanné(s)`;
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
                if (!video) return;

                this.statusText = 'Démarrage caméra...';

                // CORRECTION: Forcer caméra arrière
                this.videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: { exact: 'environment' }, // Force caméra arrière
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });
                
                video.srcObject = this.videoStream;
                await video.play();

                this.cameraActive = true;
                this.statusText = '📷 Scan actif';
                
                this.startAlternateScanning();
                
            } catch (error) {
                console.error('Erreur caméra:', error);
                this.statusText = 'Erreur caméra';
                
                // Fallback: essayer sans forcer environment
                try {
                    this.videoStream = await navigator.mediaDevices.getUserMedia({
                        video: { 
                            facingMode: 'environment', // Sans "exact"
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });
                    
                    const video = document.getElementById('camera-video');
                    video.srcObject = this.videoStream;
                    await video.play();
                    
                    this.cameraActive = true;
                    this.statusText = '📷 Scan actif';
                    this.startAlternateScanning();
                } catch (fallbackError) {
                    alert('Impossible d\'accéder à la caméra: ' + fallbackError.message);
                }
            }
        },
        
        startAlternateScanning() {
            this.initQuagga();
            
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                
                if (this.scanCycle % 3 === 0) {
                    this.scanMode = 'qr';
                    this.scanQRCode();
                } else {
                    this.scanMode = 'barcode';
                }
            }, 600);
        },
        
        initQuagga() {
            if (typeof Quagga === 'undefined') return;

            try {
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: document.getElementById('camera-video'),
                        constraints: {
                            width: { min: 640, ideal: 1280 },
                            height: { min: 480, ideal: 720 },
                            facingMode: "environment"
                        }
                    },
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader"
                        ],
                        multiple: false
                    },
                    locate: true,
                    numOfWorkers: navigator.hardwareConcurrency || 4,
                    frequency: 10
                }, (err) => {
                    if (err) {
                        console.error('Erreur Quagga:', err);
                        return;
                    }
                    Quagga.start();
                });

                Quagga.onDetected((result) => {
                    if (this.scanMode === 'barcode' && result?.codeResult?.code) {
                        const code = result.codeResult.code.trim();
                        if (code.length >= 4) {
                            this.handleScannedCode(code, 'BARCODE');
                        }
                    }
                });
            } catch (error) {
                console.error('Erreur init Quagga:', error);
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
                    const code = this.extractCleanCode(qrCode.data);
                    if (code) {
                        this.handleScannedCode(code, 'QR');
                    }
                }
            } catch (error) {
                // Ignorer
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
        
        handleScannedCode(rawCode, type) {
            const now = Date.now();
            const code = this.extractCleanCode(rawCode);
            
            if (!code || code.length < 3) return;
            
            console.log(`🔍 ${type} scanné:`, code);
            
            if (code === this.lastScannedCode && (now - this.lastScanTime) < 500) {
                return;
            }
            
            const isDuplicate = this.scannedCodes.find(item => {
                return item.code === code || 
                       item.code.replace(/[_\-\s]/g, '') === code.replace(/[_\-\s]/g, '');
            });
            
            if (isDuplicate) {
                this.statusText = '⚠️ Déjà scanné';
                this.showFlash('error');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                return;
            }
            
            this.lastScannedCode = code;
            this.lastScanTime = now;
            
            // Vérifier dans la base
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
                this.statusText = `❌ ${code} - Non trouvé`;
                this.showFlash('error');
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                setTimeout(() => {
                    if (this.cameraActive) {
                        this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                    }
                }, 1500);
                return;
            }
            
            if (!['COLLECTED', 'IN_TRANSIT', 'PENDING'].includes(packageData.status)) {
                this.statusText = `⚠️ ${code} - Statut invalide`;
                this.showFlash('error');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                setTimeout(() => {
                    if (this.cameraActive) {
                        this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                    }
                }, 1500);
                return;
            }
            
            // Valide - Ajouter
            this.scannedCodes.push({
                code: packageData.code,
                scannedAs: code,
                message: `${type} - ${packageData.status}`,
                timestamp: new Date().toISOString()
            });
            
            this.statusText = `✅ ${code} scanné`;
            this.showFlash('success');
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
            
            setTimeout(() => {
                if (this.cameraActive) {
                    this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                }
            }, 1500);
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
        
        submitForm() {
            if (this.scannedCodes.length === 0) {
                alert('Aucun code à valider');
                return;
            }

            if (!confirm(`Confirmer l'arrivée de ${this.scannedCodes.length} colis au dépôt ?`)) {
                return;
            }

            this.processing = true;
            this.statusText = 'Traitement en cours...';
            
            document.getElementById('validation-form').submit();
        },
        
        removeCode(index) {
            this.scannedCodes.splice(index, 1);
            this.statusText = `${this.scannedCodes.length} code(s)`;
        },
        
        clearAll() {
            if (confirm('Effacer tous les codes ?')) {
                this.scannedCodes = [];
                this.statusText = 'Prêt';
            }
        },
        
        showFlash(type) {
            const flashContainer = document.getElementById('flash-container');
            const flash = document.createElement('div');
            flash.className = type === 'success' ? 'flash-success' : 'flash-error';
            
            flashContainer.appendChild(flash);
            
            setTimeout(() => {
                flashContainer.removeChild(flash);
            }, 500);
        }
    }
}
    </script>
</body>
</html>
