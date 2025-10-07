@extends('layouts.deliverer-modern')

@section('title', 'Scanner Multiple')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    #camera-view {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    #camera-video {
        width: 100%;
        height: auto;
    }
    .scan-line {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #10B981, transparent);
        animation: scan 2s ease-in-out infinite;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.8);
    }
    @keyframes scan {
        0%, 100% { opacity: 0.3; transform: translateY(-30px); }
        50% { opacity: 1; transform: translateY(30px); }
    }
    .pulse-scan {
        animation: pulse 0.5s ease-in-out;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); box-shadow: 0 0 25px rgba(16, 185, 129, 0.6); }
    }
    .modern-card {
        background: white;
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }
    .action-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 1rem 2rem;
        border-radius: 1rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
    }
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="multiScannerApp()" x-init="init()">
    
    <!-- Header Moderne avec Gradient -->
    <div class="relative safe-top">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-600"></div>
        <div class="relative px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('deliverer.tournee') }}" class="p-2 bg-white/20 rounded-xl hover:bg-white/30 transition-all">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center">
                            <span class="mr-2">📦</span> Scanner Pro
                        </h1>
                        <p class="text-white/80 text-sm font-medium" x-text="statusText">Prêt à scanner</p>
                    </div>
                </div>
                <button @click="toggleCamera()" 
                        :class="cameraActive ? 'bg-green-500 shadow-lg shadow-green-500/50' : 'bg-white/20'"
                        class="p-4 rounded-2xl transition-all duration-300 transform hover:scale-110">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="p-4" style="padding-bottom: 180px;">
        
        <!-- Stats Modernes -->
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div class="stat-card text-center transform hover:scale-105 transition-all">
                <div class="text-4xl font-black mb-1" x-text="scannedCount">0</div>
                <div class="text-sm font-semibold opacity-90">📦 Scannés</div>
            </div>
            <div class="stat-card text-center transform hover:scale-105 transition-all">
                <div class="text-4xl font-black mb-1" x-text="totalProcessed">0</div>
                <div class="text-sm font-semibold opacity-90">✅ Traités</div>
            </div>
        </div>

        <!-- Caméra View -->
        <div x-show="cameraActive" class="mb-6">
            <div id="camera-view" class="relative">
                <video id="camera-video" autoplay playsinline></video>
                <canvas id="camera-canvas" style="display:none;"></canvas>
                <div class="scan-line"></div>
                <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-lg text-sm font-semibold">
                    🎥 Caméra Active
                </div>
            </div>
        </div>

        <!-- Sélecteur d'action Moderne -->
        <div class="mb-6 modern-card p-5">
            <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center">
                <span class="text-2xl mr-2">🎯</span>
                <span>Action à effectuer</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <button @click="scanAction = 'pickup'" 
                        :class="scanAction === 'pickup' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'bg-gray-100 text-gray-700'"
                        class="p-4 rounded-xl font-bold transition-all duration-300 transform hover:scale-105">
                    <div class="text-3xl mb-1">📦</div>
                    <div class="text-sm">Ramassage</div>
                </button>
                <button @click="scanAction = 'delivering'" 
                        :class="scanAction === 'delivering' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/50' : 'bg-gray-100 text-gray-700'"
                        class="p-4 rounded-xl font-bold transition-all duration-300 transform hover:scale-105">
                    <div class="text-3xl mb-1">🚚</div>
                    <div class="text-sm">En Livraison</div>
                </button>
            </div>
        </div>

        <!-- Zone de saisie manuelle Moderne -->
        <div class="mb-6 modern-card p-5">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <span class="text-2xl mr-2">📝</span>
                <span>Saisie Manuelle</span>
            </h3>
            <textarea 
                x-model="manualCodes"
                @keydown.enter.prevent="processManual()"
                placeholder="Saisissez vos codes ici...&#10;(séparés par virgule ou retour ligne)"
                class="w-full p-4 border-2 border-gray-200 rounded-xl h-24 focus:border-purple-500 focus:ring-4 focus:ring-purple-200 transition-all"
            ></textarea>
            <button @click="processManual()" class="w-full mt-3 action-button">
                <span class="text-lg">➕ Ajouter les Codes</span>
            </button>
        </div>

        <!-- Liste des colis scannés Moderne -->
        <div x-show="scannedPackages.length > 0" x-transition class="mb-6">
            <div class="modern-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <span class="text-2xl mr-2">📋</span>
                        <span>Colis Scannés</span>
                    </h3>
                    <button @click="clearAll()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition-all transform hover:scale-105">
                        🗑️ Effacer
                    </button>
                </div>
                
                <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                    <template x-for="(pkg, index) in scannedPackages" :key="index">
                        <div class="p-4 rounded-xl flex items-center justify-between pulse-scan transition-all hover:shadow-lg"
                             :class="pkg.status === 'success' ? 'bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-300' : pkg.status === 'error' ? 'bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-300' : 'bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300'">
                            <div class="flex items-center space-x-4 flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-md"
                                     :class="pkg.status === 'success' ? 'bg-green-500 text-white' : pkg.status === 'error' ? 'bg-red-500 text-white' : 'bg-gray-400 text-white'">
                                    <span x-text="index + 1"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900 text-lg" x-text="pkg.code"></div>
                                    <div class="text-sm font-medium" 
                                         :class="pkg.status === 'success' ? 'text-green-700' : pkg.status === 'error' ? 'text-red-700' : 'text-gray-600'" 
                                         x-text="pkg.message || 'En attente'"></div>
                                </div>
                            </div>
                            <button @click="removePackage(index)" class="ml-3 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-all transform hover:scale-110 shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Bouton Validation Moderne Flottant -->
        <div x-show="scannedPackages.length > 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="fixed left-0 right-0 p-4 glass-effect shadow-2xl" 
             style="bottom: 100px;">
            <button @click="validateAndSubmit()" 
                    :disabled="processing || scannedPackages.length === 0"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-green-500/50 hover:shadow-xl hover:shadow-green-500/60 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-300">
                <span x-show="!processing" class="text-lg flex items-center justify-center">
                    <span class="text-2xl mr-2">✅</span>
                    Valider (<span x-text="scannedCount"></span> colis)
                </span>
                <span x-show="processing" class="text-lg flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Traitement...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- ZXing pour scanner QR codes ET codes-barres -->
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js"></script>

<!-- Sons de feedback -->
<audio id="scan-success-sound" src="/sounds/success.mp3" preload="auto"></audio>
<audio id="scan-error-sound" src="/sounds/error.mp3" preload="auto"></audio>
<audio id="scan-invalid-sound" src="/sounds/error.mp3" preload="auto"></audio>
<audio id="scan-duplicate-sound" src="/sounds/error.mp3" preload="auto"></audio>
<script>
function multiScannerApp() {
    return {
        cameraActive: false,
        manualCodes: '',
        scannedPackages: [],
        scannedCount: 0,
        totalProcessed: 0,
        processing: false,
        statusText: 'Prêt à scanner',
        scanAction: 'pickup', // Par défaut: ramassage
        codeReader: null,
        videoInputDevices: [],
        selectedDeviceId: null,
        lastScannedCode: null,
        lastScanTime: 0,

        async init() {
            console.log('✅ Multi-scanner initialisé');
            this.updateCounts();
            
            // Initialiser ZXing avec support multi-formats (QR + Codes-barres)
            if (typeof ZXing !== 'undefined') {
                this.codeReader = new ZXing.BrowserMultiFormatReader();
                console.log('✅ ZXing initialisé - Support QR + Codes-barres');
            } else {
                console.error('❌ ZXing non chargé');
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
                if (!video || !this.codeReader) {
                    console.error('Éléments manquants pour démarrer la caméra');
                    return;
                }

                this.statusText = 'Démarrage caméra...';

                // Utiliser ZXing pour décoder depuis la caméra
                this.codeReader.decodeFromVideoDevice(
                    undefined, // undefined = caméra par défaut
                    video,
                    (result, error) => {
                        if (result) {
                            this.handleScannedCode(result.getText());
                        }
                        // Les erreurs NotFoundException sont normales (pas de code détecté)
                        if (error && !(error instanceof ZXing.NotFoundException)) {
                            console.error('Erreur scan:', error);
                        }
                    }
                );

                this.cameraActive = true;
                this.statusText = '📷 Caméra active - QR + Codes-barres';
                
                showToast('Caméra activée', 'success');
                this.playSound('success');
                vibrate([50]);

            } catch (error) {
                console.error('Erreur caméra:', error);
                showToast('Impossible d\'accéder à la caméra', 'error');
                this.playSound('error');
                this.statusText = 'Erreur caméra';
                this.cameraActive = false;
            }
        },

        stopCamera() {
            if (this.codeReader) {
                this.codeReader.reset();
            }
            this.cameraActive = false;
            this.statusText = 'Caméra arrêtée';
            showToast('Caméra désactivée', 'info');
        },

        playSound(type) {
            try {
                const sound = document.getElementById(`scan-${type}-sound`);
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(e => console.log('Son désactivé'));
                }
            } catch (e) {
                console.log('Lecture son échouée');
            }
        },

        playSoundByType(errorType) {
            switch(errorType) {
                case 'not_found':
                    this.playSound('error');
                    break;
                case 'invalid_status':
                    this.playSound('invalid');
                    break;
                case 'wrong_deliverer':
                    this.playSound('error');
                    break;
                case 'duplicate':
                    this.playSound('duplicate');
                    break;
                default:
                    this.playSound('error');
            }
        },

        handleScannedCode(code) {
            const now = Date.now();
            
            // Éviter les doublons (même code dans les 2 secondes)
            if (this.lastScannedCode === code && (now - this.lastScanTime) < 2000) {
                return;
            }

            this.lastScannedCode = code;
            this.lastScanTime = now;

            // Vérifier si déjà scanné
            if (this.scannedPackages.find(pkg => pkg.code === code)) {
                showToast('⚠️ Colis déjà scanné', 'warning');
                this.playSoundByType('duplicate');
                vibrate([100, 50, 100]);
                return;
            }

            // Ajouter le colis
            this.scannedPackages.push({
                code: code,
                status: 'pending',
                message: 'Scanné avec succès',
                timestamp: new Date().toISOString()
            });

            this.updateCounts();
            this.statusText = `✅ ${code} scanné !`;
            
            showToast(`Colis ${code} ajouté`, 'success');
            this.playSound('success');
            vibrate([50, 30, 50]);

            // Réinitialiser le message après 2s
            setTimeout(() => {
                this.statusText = '📷 Caméra active - Scannez vos colis';
            }, 2000);
        },

        processManual() {
            const codes = this.manualCodes
                .split(/[,\n]+/)
                .map(code => code.trim())
                .filter(code => code.length > 0);
            
            if (codes.length === 0) {
                showToast('Veuillez saisir au moins un code', 'warning');
                return;
            }

            let added = 0;
            let duplicates = 0;

            codes.forEach(code => {
                if (!this.scannedPackages.find(pkg => pkg.code === code)) {
                    this.scannedPackages.push({
                        code: code,
                        status: 'pending',
                        message: 'Ajouté manuellement',
                        timestamp: new Date().toISOString()
                    });
                    added++;
                } else {
                    duplicates++;
                }
            });
            
            this.updateCounts();
            this.manualCodes = '';
            
            if (added > 0) {
                showToast(`✅ ${added} colis ajouté(s)`, 'success');
                vibrate([50]);
            }
            if (duplicates > 0) {
                showToast(`⚠️ ${duplicates} doublon(s) ignoré(s)`, 'warning');
            }
        },

        async validateAndSubmit() {
            if (this.scannedPackages.length === 0) {
                showToast('Aucun colis à valider', 'warning');
                return;
            }

            const actionLabel = this.scanAction === 'pickup' ? 'ramasser' : 'mettre en livraison';
            if (!confirm(`Voulez-vous ${actionLabel} ${this.scannedCount} colis ?`)) {
                return;
            }

            this.processing = true;
            this.statusText = 'Traitement en cours...';

            try {
                const url = '{{ route("deliverer.scan.submit") }}';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                console.log('=== DÉMARRAGE VALIDATION ===');
                console.log('URL:', url);
                console.log('CSRF Token:', csrfToken ? 'Présent' : 'MANQUANT');
                console.log('Action:', this.scanAction);
                console.log('Codes:', this.scannedPackages.map(pkg => pkg.code));

                if (!csrfToken) {
                    throw new Error('Token CSRF manquant. Rechargez la page.');
                }

                const requestData = {
                    codes: this.scannedPackages.map(pkg => pkg.code),
                    batch: true,
                    action: this.scanAction
                };

                console.log('Données envoyées:', requestData);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData),
                    credentials: 'same-origin'
                });

                console.log('Réponse reçue - Statut:', response.status, response.statusText);

                const data = await response.json();
                console.log('Données reçues:', data);

                if (response.ok && data.success) {
                    // Afficher les résultats détaillés
                    const summary = data.summary;
                    
                    if (summary.success > 0) {
                        showToast(`✅ ${summary.success} colis traités avec succès`, 'success');
                        this.playSound('success');
                    }
                    
                    if (summary.invalid_status > 0) {
                        showToast(`⚠️ ${summary.invalid_status} colis avec statut invalide`, 'warning');
                        this.playSound('invalid');
                    }
                    
                    if (summary.errors > 0) {
                        showToast(`❌ ${summary.errors} erreurs`, 'error');
                        this.playSound('error');
                    }
                    
                    this.totalProcessed += summary.success;
                    vibrate([100, 50, 100, 50, 100]);
                    
                    // Réinitialiser seulement les colis réussis
                    this.scannedPackages = [];
                    this.updateCounts();
                    this.statusText = 'Validation terminée !';
                    
                    setTimeout(() => {
                        this.statusText = 'Prêt à scanner';
                    }, 3000);
                } else {
                    throw new Error(data.message || 'Erreur de validation');
                }

            } catch (error) {
                console.error('=== ERREUR VALIDATION ===');
                console.error('Type:', error.name);
                console.error('Message:', error.message);
                console.error('Stack:', error.stack);
                
                let errorMessage = 'Erreur inconnue';
                if (error.message.includes('Failed to fetch') || error.message.includes('load failed')) {
                    errorMessage = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
                } else if (error.message.includes('NetworkError')) {
                    errorMessage = 'Erreur réseau. Vérifiez votre connexion Internet.';
                } else {
                    errorMessage = error.message;
                }
                
                showToast(errorMessage, 'error');
                this.playSound('error');
                this.statusText = 'Erreur: ' + errorMessage;
            } finally {
                this.processing = false;
            }
        },

        removePackage(index) {
            const removed = this.scannedPackages.splice(index, 1);
            this.updateCounts();
            showToast(`Colis ${removed[0].code} retiré`, 'info');
            vibrate([30]);
        },

        clearAll() {
            if (confirm('Effacer tous les colis scannés ?')) {
                this.scannedPackages = [];
                this.updateCounts();
                showToast('Liste effacée', 'info');
            }
        },

        updateCounts() {
            this.scannedCount = this.scannedPackages.length;
        }
    }
}
</script>

@endsection
