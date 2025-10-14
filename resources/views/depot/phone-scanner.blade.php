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
    
    <!-- Messages de succès/erreur -->
    @if(session('success'))
    <div class="bg-green-500 text-white p-4 text-center font-bold" id="success-message">
        ✅ {{ session('success') }}
    </div>
    <script>
        // Effacer la liste locale après validation réussie
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            if (successMsg) {
                successMsg.style.display = 'none';
            }
        }, 3000);
    </script>
    @endif

    @if(session('error'))
    <div class="bg-red-500 text-white p-4 text-center font-bold" id="error-message">
        ❌ {{ session('error') }}
    </div>
    <script>
        setTimeout(() => {
            const errorMsg = document.getElementById('error-message');
            if (errorMsg) {
                errorMsg.style.display = 'none';
            }
        }, 3000);
    </script>
    @endif

    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-4 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">🏭 Scanner Dépôt</h1>
                <p class="text-white/80 text-sm" x-text="statusText">Prêt</p>
                <p class="text-white/70 text-xs mt-1">👤 Chef: {{ $depotManagerName }}</p>
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
            <div class="text-sm font-semibold text-white/90"> Colis Scannés</div>
            <div class="text-xs text-white/75 mt-1">Arrivée au dépôt</div>
        </div>

        <!-- Vidéo caméra -->
        <div x-show="cameraActive" class="relative bg-black rounded-2xl overflow-hidden mb-4" style="min-height: 50vh;">
            <video id="scanner-video" 
                   class="w-full h-full object-cover"
                   style="max-height: 60vh;"
                   autoplay 
                   playsinline
                   muted>
            </video>
            <canvas id="scanner-canvas" class="hidden"></canvas>
            
            <!-- Messages Overlay sur Caméra -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="bg-black/80 p-6 rounded-2xl text-center min-w-[80%]">
                    <p x-show="cameraActive && statusText && !statusText.includes('Prêt')" 
                       x-text="statusText" 
                       class="font-black text-3xl leading-tight mb-2"
                       :class="{
                           'text-green-400': statusText.includes('✅') || statusText.includes('Déjà scanné'),
                           'text-red-400': statusText.includes('❌'),
                           'text-orange-400': statusText.includes('⚠️') && !statusText.includes('Déjà scanné'),
                           'text-white': statusText.includes('📷')
                       }">
                    </p>
                    <p x-show="cameraActive" class="text-white font-bold text-xl">
                        <span x-text="scannedCodes.length"></span> colis scanné(s)
                    </p>
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
            <div class="mt-4 text-center min-h-20 flex items-center justify-center">
                <div>
                    <span x-show="currentCode.length > 0 && codeStatus === 'checking'" class="text-blue-600 font-bold text-xl">
                        🔍 Vérification...
                    </span>
                    <span x-show="codeStatus === 'valid'" class="text-green-600 font-black text-2xl">
                        ✅ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'not_found'" class="text-red-600 font-black text-2xl">
                        ❌ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'wrong_status'" class="text-orange-600 font-black text-2xl" x-cloak>
                        ⚠️ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'invalid'" class="text-red-600 font-black text-2xl" x-cloak>
                        ❌ <span x-text="statusMessage"></span>
                    </span>
                    <span x-show="codeStatus === 'duplicate'" class="text-green-600 font-black text-2xl">
                        ✅ Déjà scanné
                    </span>
                </div>
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

        <!-- Info: Validation depuis PC uniquement -->
        <div x-show="scannedCodes.length > 0" 
             class="fixed left-0 right-0 bottom-0 p-4 bg-gradient-to-r from-blue-500 to-indigo-600 border-t-2 border-blue-700 shadow-2xl">
            <div class="text-center text-white">
                <div class="text-3xl font-black mb-2" x-text="scannedCodes.length"></div>
                <div class="text-sm font-semibold">📦 Colis scannés</div>
                <div class="text-xs mt-2 opacity-90">
                    💻 Validez depuis le PC pour terminer la session
                </div>
            </div>
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
console.log('📦 Colis chargés:', PACKAGES_DATA.length);
console.log('📦 Exemple de colis:', PACKAGES_DATA.slice(0, 3));

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

            // Vérifier immédiatement au démarrage
            this.checkSessionActivity();

            // Vérifier l'activité de la session toutes les 3 secondes (plus rapide)
            setInterval(() => this.checkSessionActivity(), 3000);

            // Mettre à jour l'activité toutes les 10 secondes
            setInterval(() => this.updateActivity(), 10000);

            // Créer Map pour recherche rapide
            this.packagesMap = new Map();
            
            PACKAGES_DATA.forEach(pkg => {
                const packageData = {
                    code: pkg.c,
                    status: pkg.s,
                    id: pkg.id,
                    d: pkg.d, // Nom du dépôt actuel (si AT_DEPOT)
                    current_depot: pkg.current_depot // Nom du dépôt qui scanne
                };
                
                // Ajouter avec code principal (exact)
                this.packagesMap.set(pkg.c, packageData);
                
                // Ajouter variantes (sans underscore, minuscules)
                const upperCode = pkg.c.toUpperCase();
                const noUnderscore = pkg.c.replace(/_/g, '');
                const noUnderscoreUpper = noUnderscore.toUpperCase();
                
                if (upperCode !== pkg.c) {
                    this.packagesMap.set(upperCode, packageData);
                }
                if (noUnderscore !== pkg.c) {
                    this.packagesMap.set(noUnderscore, packageData);
                }
                if (noUnderscoreUpper !== pkg.c && noUnderscoreUpper !== noUnderscore) {
                    this.packagesMap.set(noUnderscoreUpper, packageData);
                }
            });
            
            console.log(`📦 ${PACKAGES_DATA.length} colis chargés (${this.packagesMap.size} clés)`);
            
            // Si validation réussie, vider la liste locale
            @if(session('success'))
            this.scannedCodes = [];
            this.statusText = 'Validation réussie - Prêt pour nouveaux scans';
            @endif
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
            console.log('Nombre de colis en mémoire:', PACKAGES_DATA.length);
            
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
                // Colis pas dans la liste locale - Vérifier dans la base via API
                this.codeStatus = 'checking';
                this.statusMessage = 'Vérification...';
                
                // Requête API pour vérifier le statut réel
                fetch(`/depot/scan/check-package-status?code=${encodeURIComponent(code)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.found) {
                            // Colis existe mais statut invalide
                            this.codeStatus = 'wrong_status';
                            this.statusMessage = `Statut invalide: ${data.status}`;
                            console.log('❌ Statut invalide:', data.status);
                        } else {
                            // Colis vraiment introuvable
                            this.codeStatus = 'not_found';
                            this.statusMessage = 'Colis introuvable dans la base';
                            console.log('❌ Non trouvé:', code);
                        }
                        if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                    })
                    .catch(error => {
                        console.error('Erreur vérification:', error);
                        this.codeStatus = 'not_found';
                        this.statusMessage = 'Colis non trouvé';
                        if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                    });
                return;
            }
            
            console.log('✅ Colis trouvé:', packageData);
            console.log('Statut du colis:', packageData.status);

            // Cas spécial: AT_DEPOT - vérifier si même dépôt
            if (packageData.status === 'AT_DEPOT') {
                const depotName = packageData.d; // Nom du dépôt actuel du colis
                const currentDepot = packageData.current_depot; // Nom du dépôt qui scanne

                if (depotName === currentDepot) {
                    // Même dépôt - rejeter
                    this.codeStatus = 'wrong_status';
                    this.statusMessage = `Déjà au dépôt ${depotName}`;
                    console.log('❌ Même dépôt:', depotName);
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                    return;
                }
                // Dépôt différent - accepter (transfert)
                console.log('✅ Transfert dépôt:', depotName, '→', currentDepot);
            }

            // VALIDATION STRICTE DÉPÔT : Statuts acceptés uniquement
            // PICKED_UP, AVAILABLE, CREATED, OUT_FOR_DELIVERY, AT_DEPOT (si dépôt différent)
            const acceptedStatuses = ['CREATED', 'AVAILABLE', 'PICKED_UP', 'OUT_FOR_DELIVERY'];
            
            console.log('🔍 Vérification statut:', packageData.status, 'Acceptés:', acceptedStatuses);
            
            // Cas spécial AT_DEPOT : Accepter seulement si dépôt différent
            if (packageData.status === 'AT_DEPOT') {
                const depotName = packageData.d;
                const currentDepot = packageData.current_depot;
                
                if (depotName === currentDepot) {
                    this.codeStatus = 'wrong_status';
                    this.statusMessage = `Déjà au dépôt ${depotName}`;
                    console.log('❌ Même dépôt:', depotName);
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                    return;
                }
                // Dépôt différent - accepter (transfert)
                console.log('✅ Transfert dépôt:', depotName, '→', currentDepot);
            } else if (!acceptedStatuses.includes(packageData.status)) {
                this.codeStatus = 'wrong_status';
                this.statusMessage = `Statut invalide: ${packageData.status}`;
                console.log('❌ Statut rejeté:', packageData.status);
                if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                return;
            }

            // Valide - Statut accepté
            this.codeStatus = 'valid';
            this.statusMessage = `Colis valide (${packageData.status})`;
            console.log('✅ Statut accepté:', packageData.status);
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

        async addCodeManually() {
            const code = this.currentCode.trim().toUpperCase();
            
            if (!code || this.codeStatus !== 'valid') {
                return;
            }
            
            // Envoyer au serveur (mise à jour cache temps réel)
            try {
                const response = await fetch(`/depot/scan/{{ $sessionId }}/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ code: code })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Ajouter localement
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
                } else {
                    alert('Erreur: ' + data.message);
                }
            } catch (error) {
                console.error('Erreur ajout:', error);
                alert('Erreur de connexion');
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
                const video = document.getElementById('scanner-video');
                if (!video) {
                    console.error('Video element not found');
                    alert('Erreur: Élément vidéo introuvable');
                    return;
                }

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
                    
                    const video = document.getElementById('scanner-video');
                    if (!video) return;
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
                const videoElement = document.getElementById('scanner-video');
                if (!videoElement) return;
                
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: videoElement,
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
                const video = document.getElementById('scanner-video');
                const canvas = document.getElementById('scanner-canvas');
                
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
                this.statusText = '✅ Déjà scanné';
                this.showFlash('success');
                if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
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
            
            // Cas spécial: AT_DEPOT - vérifier si même dépôt (caméra)
            if (packageData.status === 'AT_DEPOT') {
                const depotName = packageData.d;
                const currentDepot = packageData.current_depot;

                if (depotName === currentDepot) {
                    // Même dépôt - rejeter
                    this.statusText = `⚠️ ${code} - Déjà au dépôt ${depotName}`;
                    this.showFlash('error');
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                    console.log('📷 Même dépôt:', depotName);
                    setTimeout(() => {
                        if (this.cameraActive) {
                            this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                        }
                    }, 2000);
                    return;
                }
                // Dépôt différent - accepter (transfert)
                console.log('📷 Transfert dépôt:', depotName, '→', currentDepot);
            }

            // VALIDATION STRICTE DÉPÔT : Statuts acceptés uniquement
            // PICKED_UP, AVAILABLE, CREATED, OUT_FOR_DELIVERY, AT_DEPOT (si dépôt différent)
            const acceptedStatuses = ['CREATED', 'AVAILABLE', 'PICKED_UP', 'OUT_FOR_DELIVERY'];
            
            // Cas spécial AT_DEPOT déjà géré ci-dessus
            if (packageData.status !== 'AT_DEPOT' && !acceptedStatuses.includes(packageData.status)) {
                this.statusText = `⚠️ ${code} - Statut invalide: ${packageData.status}`;
                this.showFlash('error');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                console.log('❌ Statut rejeté (caméra):', packageData.status, '(statuts acceptés:', acceptedStatuses.join(', ') + ')');
                setTimeout(() => {
                    if (this.cameraActive) {
                        this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                    }
                }, 2000);
                return;
            }
            
            console.log('📷 Statut accepté (caméra):', packageData.status);
            
            // Valide - Ajouter immédiatement en local (plus rapide)
            this.addCodeLocally(packageData.code, type, packageData.status);
        },
        
        // Ajout local immédiat (sans attendre le serveur)
        addCodeLocally(code, type, status) {
            // Ajouter immédiatement en local
            this.scannedCodes.push({
                code: code,
                message: `${type} - ${status}`,
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

            // Mettre à jour l'activité
            this.updateActivity();

            // Envoyer au serveur en arrière-plan (non bloquant)
            this.syncToServerAsync(code);
        },

        // Synchronisation serveur en arrière-plan
        async syncToServerAsync(code) {
            try {
                await fetch(`/depot/scan/{{ $sessionId }}/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ code: code })
                });
            } catch (error) {
                console.error('Erreur sync serveur:', error);
                // Ne pas bloquer l'utilisateur
            }
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
                
                const video = document.getElementById('scanner-video');
                if (video) {
                    video.srcObject = null;
                }
                
                this.cameraActive = false;
                this.statusText = 'Caméra arrêtée';
                
            } catch (error) {
                console.error('Erreur arrêt caméra:', error);
            }
        },
        
        // MÉTHODE SUPPRIMÉE : La validation se fait uniquement depuis le PC
        // Le téléphone sert uniquement à scanner, pas à valider
        
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
        },

        // Vérifier le heartbeat du PC (AMÉLIORÉ)
        async checkSessionActivity() {
            try {
                const response = await fetch(`/depot/api/session/{{ $sessionId }}/check-activity`);

                if (!response.ok) {
                    // Session n'existe plus
                    console.log('❌ Session expirée ou introuvable');
                    this.stopCamera();
                    this.showSessionTerminatedPopup('expired');
                    return;
                }

                const data = await response.json();

                if (!data.active) {
                    console.log('🛑 Session terminée par le Chef de Dépôt');
                    console.log('Raison:', data.reason || 'Validation effectuée');
                    
                    // Arrêter immédiatement la caméra
                    this.stopCamera();
                    
                    // Afficher popup plein écran
                    this.showSessionTerminatedPopup(data.reason || 'completed');

                    // Désactiver TOUS les contrôles
                    document.querySelectorAll('input, button').forEach(el => {
                        if (!el.closest('#session-popup')) {
                            el.disabled = true;
                        }
                    });
                }
            } catch (error) {
                console.error('Erreur vérification activité:', error);
            }
        },

        async updateActivity() {
            try {
                await fetch(`/depot/api/session/{{ $sessionId }}/update-activity`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            } catch (error) {
                console.error('Erreur mise à jour activité:', error);
            }
        },

        showSessionTerminatedPopup(reason) {
            // Éviter d'afficher plusieurs fois
            if (document.getElementById('session-popup')) {
                return;
            }

            const reasons = {
                'completed': '✅ Session Terminée par le Chef de Dépôt',
                'expired': '⏰ Session Expirée',
                'inactivity': '💤 Session Terminée (Inactivité)',
                'default': '🛑 Session Terminée'
            };

            const title = reasons[reason] || reasons['default'];
            const message = reason === 'completed' 
                ? '🏭 Les colis ont été validés et marqués AT_DEPOT par le Chef de Dépôt.'
                : 'La session de scan a été terminée.';

            const popup = document.createElement('div');
            popup.id = 'session-popup';
            popup.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px;">
                    <div style="background: white; border-radius: 24px; padding: 50px 30px; max-width: 450px; width: 100%; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
                        <div style="font-size: 100px; margin-bottom: 25px; animation: bounce 1s ease-in-out;">${reason === 'completed' ? '✅' : '🛑'}</div>
                        <h2 style="font-size: 28px; font-weight: 900; margin-bottom: 20px; color: #1f2937;">${title}</h2>
                        <p style="color: #4b5563; margin-bottom: 25px; font-size: 17px; line-height: 1.6;">${message}</p>
                        <div style="background: #f3f4f6; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                            <p style="color: #6b7280; font-size: 15px; margin-bottom: 10px;">💻 <strong>Validation depuis le PC</strong></p>
                            <p style="color: #9ca3af; font-size: 13px;">Seul le Chef de Dépôt peut valider les colis depuis l'interface PC.</p>
                        </div>
                        <button onclick="window.close()" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px 30px; border-radius: 14px; border: none; font-weight: bold; font-size: 18px; cursor: pointer; box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            ✖️ Fermer cette Page
                        </button>
                        <p style="color: #9ca3af; font-size: 12px; margin-top: 20px;">Vous pouvez fermer cet onglet en toute sécurité</p>
                    </div>
                </div>
                <style>
                    @keyframes bounce {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-20px); }
                    }
                </style>
            `;
            document.body.appendChild(popup);
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200, 100, 200]);
            }
        }
    }
}
    </script>
</body>
</html>
