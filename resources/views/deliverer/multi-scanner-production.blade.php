@extends('layouts.deliverer-modern')

@section('title', 'Scanner Multiple Simple')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .modern-card {
        background: white;
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .modern-card:hover {
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
    }
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
    .code-input {
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
        letter-spacing: 3px;
        border: 3px solid #667eea;
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    .code-input:focus {
        border-color: #10B981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        transform: scale(1.02);
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
        animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .scanned-item {
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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
    .active:scale-98 {
        transform: scale(0.98);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="simpleScannerApp()" x-init="init()">
    
    <!-- Header avec safe-area iOS -->
    <div class="relative" style="padding-top: env(safe-area-inset-top, 0px);">
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
                        <h1 class="text-2xl font-bold text-white">📦 Scanner Multiple</h1>
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

    <div class="p-4" style="padding-bottom: 180px;">
        
        <!-- Stats -->
        <div class="mb-6">
            <div class="stat-card text-center">
                <div class="text-4xl font-black mb-1" x-text="scannedCodes.length">0</div>
                <div class="text-sm font-semibold opacity-90">📦 Codes Scannés</div>
                <div class="text-xs opacity-75 mt-1">Vérification lors de la validation</div>
            </div>
        </div>

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

        <!-- Action -->
        <div class="mb-6 modern-card p-5">
            <label class="block text-sm font-bold text-gray-800 mb-3">🎯 Action</label>
            <div class="grid grid-cols-2 gap-3">
                <button @click="scanAction = 'pickup'" 
                        :class="scanAction === 'pickup' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700'"
                        class="py-4 rounded-xl font-bold transition-all active:scale-95">
                    📦 Ramassage
                </button>
                <button @click="scanAction = 'delivery'" 
                        :class="scanAction === 'delivery' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700'"
                        class="py-4 rounded-xl font-bold transition-all active:scale-95">
                    🚚 Livraison
                </button>
            </div>
        </div>

        <!-- CHAMP PRINCIPAL: Saisie manuelle avec validation locale -->
        <div class="mb-6 modern-card p-5">
            <h3 class="font-bold text-gray-800 mb-3">📝 Saisir un Code Manuellement</h3>
            <input 
                type="text"
                x-model="currentCode"
                @input="validateCodeFormat()"
                @keydown.enter="addCodeManually()"
                placeholder="Tapez un code puis ENTRÉE..."
                class="w-full code-input transition-all"
                :class="getInputClass()"
            >
            <div class="mt-2 text-center min-h-6">
                <span x-show="currentCode.length > 0 && codeStatus === 'checking'" class="text-blue-600 font-semibold text-sm">
                    🔍 Vérification dans la base...
                </span>
                <span x-show="codeStatus === 'valid'" class="text-green-600 font-bold text-sm">
                    ✅ <span x-text="statusMessage"></span> - Appuyez ENTRÉE
                </span>
                <span x-show="codeStatus === 'not_found'" class="text-red-600 font-bold text-sm">
                    ❌ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'wrong_status'" class="text-orange-600 font-bold text-sm">
                    ⚠️ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'duplicate'" class="text-orange-600 font-bold text-sm">
                    ⚠️ <span x-text="statusMessage"></span>
                </span>
                <span x-show="codeStatus === 'invalid'" class="text-red-600 font-bold text-sm">
                    ❌ <span x-text="statusMessage"></span>
                </span>
            </div>
            <button @click="addCodeManually()" 
                    :disabled="codeStatus !== 'valid'"
                    class="w-full mt-3 font-bold py-3 rounded-xl transition-all"
                    :class="codeStatus === 'valid' ? 'bg-green-500 text-white hover:bg-green-600' : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                <span x-show="codeStatus === 'valid'">✅ Ajouter le Code</span>
                <span x-show="codeStatus !== 'valid'">➕ Ajouter le Code</span>
            </button>
            <p class="text-xs text-gray-500 mt-2 text-center">
                <span x-show="packagesMap && packagesMap.size > 0">
                    📦 <span x-text="packagesMap ? packagesMap.size : 0"></span> colis chargés (optimisé)
                </span>
            </p>
        </div>

        <!-- Liste des codes scannés -->
        <div x-show="scannedCodes.length > 0" x-transition class="mb-6">
            <div class="modern-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">📋 Codes Scannés (<span x-text="scannedCodes.length"></span>)</h3>
                    <button @click="clearAll()" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold">
                        🗑️ Effacer
                    </button>
                </div>
                
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <template x-for="(item, index) in scannedCodes" :key="index">
                        <div class="scanned-item p-4 rounded-xl flex items-center justify-between shadow-sm hover:shadow-md transition-all"
                             :class="item.assigned ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300' : 'bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300'">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shadow-md"
                                     :class="item.assigned ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-amber-500 to-orange-600'">
                                    <span x-text="index + 1"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-gray-900 text-base" x-text="item.code"></span>
                                        <span x-show="item.assigned" class="text-xs px-2 py-1 bg-green-500 text-white rounded-lg font-semibold shadow-sm">
                                            ✓ Assigné
                                        </span>
                                        <span x-show="!item.assigned" class="text-xs px-2 py-1 bg-amber-500 text-white rounded-lg font-semibold shadow-sm">
                                            ℹ️ Non assigné
                                        </span>
                                    </div>
                                    <div class="text-xs font-medium" 
                                         :class="item.assigned ? 'text-blue-700' : 'text-amber-700'" 
                                         x-text="item.message"></div>
                                </div>
                            </div>
                            <button @click="removeCode(index)" class="p-2.5 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all active:scale-95 shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Bouton Validation FIXE EN BAS -->
        <div x-show="scannedCodes.length > 0" 
             class="fixed left-0 right-0 p-4 z-50" 
             style="bottom: 80px; background: linear-gradient(to top, rgba(255,255,255,1) 80%, rgba(255,255,255,0.95) 100%); padding-bottom: env(safe-area-inset-bottom, 1rem);">
            <form id="validation-form" method="POST" action="{{ route('deliverer.scan.multi.validate') }}">
                @csrf
                <input type="hidden" name="action" x-model="scanAction">
                <template x-for="(item, index) in scannedCodes" :key="index">
                    <input type="hidden" :name="'codes[' + index + ']'" :value="item.code">
                </template>
                
                <button type="button" @click="submitForm()" 
                        :disabled="processing"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-5 rounded-2xl disabled:opacity-50 shadow-2xl transition-all active:scale-98">
                    <span x-show="!processing" class="text-lg">
                        ✅ Valider <span x-text="scannedCodes.length"></span> colis (<span x-text="scanAction === 'pickup' ? 'Ramassage' : 'Livraison'"></span>)
                    </span>
                    <span x-show="processing" class="text-lg">
                        ⏳ Traitement en cours...
                    </span>
                </button>
            </form>
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
// DONNÉES DES COLIS CHARGÉES DU SERVEUR (une seule fois)
const PACKAGES_DATA = @json($packages ?? []);

function simpleScannerApp() {
    return {
        // États
        cameraActive: false,
        currentCode: '',
        scannedCodes: [],
        processing: false,
        statusText: 'Prêt',
        scanAction: 'pickup',
        
        // Base de données locale (Map pour recherche O(1))
        packagesMap: null,
        
        // Validation DB locale (SANS API temps réel)
        codeStatus: '', // 'checking', 'valid', 'invalid', 'not_found', 'duplicate', 'wrong_status'
        statusMessage: '',
        validationTimeout: null,
        
        // Scan alternatif
        scanMode: 'barcode', // 'barcode' ou 'qr'
        scanCycle: 0,
        
        // Caméra
        videoStream: null,
        scanInterval: null,
        lastScanTime: 0,
        lastScannedCode: '',
        
        // ANTI-SPAM pour erreurs
        errorBuffer: new Map(), // Pour éviter messages répétitifs
        lastErrorTime: 0,

        init() {
            console.log('✅ Scanner avec validation DB locale initialisé');
            
            // CORRECTION: Créer Map avec PLUSIEURS clés par colis
            // pour gérer différentes variantes du code
            this.packagesMap = new Map();
            
            PACKAGES_DATA.forEach(pkg => {
                const packageData = {
                    code: pkg.c,
                    status: pkg.s,
                    can_pickup: pkg.p === 1,
                    can_deliver: pkg.d === 1,
                    id: pkg.id,
                    assigned: pkg.assigned === 1 // Info: colis assigné au livreur ou non
                };
                
                // Ajouter avec le code original
                this.packagesMap.set(pkg.c, packageData);
                
                // CORRECTION: Ajouter aussi avec le code nettoyé (sans _, -, espaces)
                if (pkg.c2 && pkg.c2 !== pkg.c) {
                    this.packagesMap.set(pkg.c2, packageData);
                }
                
                // CORRECTION: Ajouter variantes courantes
                // Ex: PKG_ABC_123 → aussi accessible par PKGABC123
                const noUnderscore = pkg.c.replace(/_/g, '');
                if (noUnderscore !== pkg.c) {
                    this.packagesMap.set(noUnderscore, packageData);
                }
            });
            
            console.log(`📦 ${PACKAGES_DATA.length} colis chargés (${this.packagesMap.size} clés de recherche)`);
            console.log(`💾 Taille mémoire estimée: ${Math.round(this.packagesMap.size * 0.1)}KB`);
            
            // DEBUG: Afficher quelques codes pour vérification
            if (PACKAGES_DATA.length > 0) {
                console.log('📋 Exemples de codes chargés:');
                PACKAGES_DATA.slice(0, 3).forEach(pkg => {
                    console.log(`  - ${pkg.c} (ID: ${pkg.id}, Statut: ${pkg.s})`);
                });
            }
        },
        
        // VALIDATION TEMPS RÉEL avec DB locale (SANS API)
        validateCodeFormat() {
            const code = this.currentCode.trim().toUpperCase();
            
            // Reset si vide
            if (!code) {
                this.codeStatus = '';
                this.statusMessage = '';
                return;
            }
            
            // Afficher "Vérification..." brièvement
            this.codeStatus = 'checking';
            
            // Annuler timeout précédent
            if (this.validationTimeout) {
                clearTimeout(this.validationTimeout);
            }
            
            // Valider après 300ms (débounce)
            this.validationTimeout = setTimeout(() => {
                this.checkCodeInDB(code);
            }, 300);
        },
        
        // VÉRIFIER CODE DANS DB LOCALE (SANS API) - OPTIMISÉ avec Map
        checkCodeInDB(code) {
            console.log('🔍 Vérification:', code);
            
            // 1. Vérifier format basique
            if (code.length < 3) {
                this.codeStatus = 'invalid';
                this.statusMessage = 'Code trop court';
                this.playSound('error');
                if (navigator.vibrate) navigator.vibrate([100]);
                return;
            }
            
            // 2. CORRECTION: Vérifier si déjà scanné (avec variantes)
            const isDuplicate = this.scannedCodes.find(item => {
                return item.code === code || 
                       item.code.replace(/[_\-\s]/g, '') === code.replace(/[_\-\s]/g, '');
            });
            
            if (isDuplicate) {
                this.codeStatus = 'duplicate';
                this.statusMessage = 'Déjà scanné';
                this.playSound('error');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                return;
            }
            
            // 3. CORRECTION: Rechercher avec PLUSIEURS variantes du code
            let packageData = this.packagesMap.get(code);
            
            // Si pas trouvé, essayer sans underscores
            if (!packageData) {
                const noUnderscore = code.replace(/_/g, '');
                packageData = this.packagesMap.get(noUnderscore);
                if (packageData) {
                    console.log('✅ Trouvé avec variante sans underscore:', noUnderscore);
                }
            }
            
            // Si pas trouvé, essayer sans tirets
            if (!packageData) {
                const noDash = code.replace(/-/g, '');
                packageData = this.packagesMap.get(noDash);
                if (packageData) {
                    console.log('✅ Trouvé avec variante sans tiret:', noDash);
                }
            }
            
            // Si pas trouvé, essayer version complètement nettoyée
            if (!packageData) {
                const cleaned = code.replace(/[_\-\s]/g, '');
                packageData = this.packagesMap.get(cleaned);
                if (packageData) {
                    console.log('✅ Trouvé avec variante nettoyée:', cleaned);
                }
            }
            
            if (!packageData) {
                // ❌ COLIS NON TROUVÉ
                this.codeStatus = 'not_found';
                this.statusMessage = 'Colis non trouvé dans vos assignations';
                this.playSound('error');
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                console.log('❌ Non trouvé:', code);
                console.log('📋 Nombre de colis chargés:', PACKAGES_DATA.length);
                return;
            }
            
            console.log('✅ Colis trouvé:', packageData);
            
            // 4. Vérifier le statut selon l'action
            const isValidForAction = this.scanAction === 'pickup' 
                ? packageData.can_pickup 
                : packageData.can_deliver;
            
            if (!isValidForAction) {
                // ⚠️ STATUT NON VALIDE
                this.codeStatus = 'wrong_status';
                this.statusMessage = `Statut invalide pour ${this.scanAction === 'pickup' ? 'ramassage' : 'livraison'} (${packageData.status})`;
                this.playSound('error');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100, 50, 100]);
                console.log('⚠️ Statut invalide:', packageData);
                return;
            }
            
            // ✅ TOUT EST OK
            this.codeStatus = 'valid';
            // Afficher info d'assignation
            const assignInfo = packageData.assigned ? '✓ Assigné' : 'ℹ️ Non assigné';
            this.statusMessage = `Colis valide (${packageData.status}) - ${assignInfo}`;
            this.playSound('success');
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
            console.log('✅ Valide:', packageData);
        },
        
        // CLASS CSS DYNAMIQUE
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

        // AJOUTER CODE MANUELLEMENT
        addCodeManually() {
            const code = this.currentCode.trim().toUpperCase();
            
            if (!code) {
                showToast('⚠️ Veuillez saisir un code', 'warning');
                return;
            }
            
            // Vérifier le statut de validation
            if (this.codeStatus !== 'valid') {
                showToast('❌ ' + this.statusMessage, 'error');
                this.playSound('error');
                return;
            }
            
            // Ajouter le code valide
            this.scannedCodes.push({
                code: code,
                message: 'Saisie manuelle - ' + this.statusMessage,
                timestamp: new Date().toISOString()
            });
            
            // Reset complet
            this.currentCode = '';
            this.codeStatus = '';
            this.statusMessage = '';
            
            showToast('✅ Code ajouté', 'success');
            this.playSound('success');
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
            
            this.statusText = `${this.scannedCodes.length} code(s) scanné(s)`;
        },
        
        // CAMÉRA: Toggle
        async toggleCamera() {
            if (this.cameraActive) {
                this.stopCamera();
            } else {
                await this.startCamera();
            }
        },
        
        // DÉMARRER CAMÉRA - CORRIGÉ
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

                // Démarrer flux vidéo SANS Quagga d'abord
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
                
                // Démarrer le scan HYBRIDE (QR + Barcode)
                this.startHybridScanning();
                
                showToast('✅ Caméra prête !', 'success');
                console.log('✅ Caméra démarrée avec succès');
                
            } catch (error) {
                console.error('Erreur caméra:', error);
                showToast('❌ Erreur caméra: ' + error.message, 'error');
                this.statusText = 'Erreur caméra';
            }
        },
        
        // SCAN HYBRIDE: QR prioritaire + Barcode en backup
        startHybridScanning() {
            console.log('🔄 Démarrage scan hybride QR + Barcode');
            
            // PRIORITÉ 1: Scanner QR en continu (plus rapide)
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                
                // QR Code 2x sur 3 (prioritaire)
                if (this.scanCycle % 3 !== 2) {
                    this.scanMode = 'qr';
                    this.scanQRCode();
                } else {
                    this.scanMode = 'barcode';
                }
            }, 300); // 300ms pour meilleure réactivité
            
            // PRIORITÉ 2: Démarrer Quagga en parallèle (codes-barres)
            setTimeout(() => {
                this.initQuagga();
            }, 500); // Délai pour laisser la caméra se stabiliser
        },
        
        // INITIALISER QUAGGA (Codes-barres) - NE REMPLACE PAS LA VIDÉO
        initQuagga() {
            if (typeof Quagga === 'undefined') {
                console.warn('⚠️ Quagga non chargé - scan code-barres désactivé');
                return;
            }

            try {
                // Configuration Quagga pour utiliser le flux existant
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

                // Callback détection code-barres
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
                            this.handleScannedCode(code, 'BARCODE');
                        }
                    }
                });
            } catch (error) {
                console.error('❌ Erreur init Quagga:', error);
            }
        },
        
        // SCAN QR CODE avec jsQR
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
                        this.handleScannedCode(code, 'QR');
                    }
                }
            } catch (error) {
                // Ignorer erreurs de scan
            }
        },
        
        // EXTRAIRE CODE PROPRE (URL, espaces, etc.)
        extractCleanCode(rawCode) {
            let code = rawCode.trim();
            
            // 1. Extraire de l'URL de tracking
            // Formats supportés:
            //   - http://127.0.0.1:8000/track/PKG_VIHQA1_1006
            //   - https://al-amena.com/track/PKG_VIHQA1_1006
            //   - /track/PKG_VIHQA1_1006
            if (code.includes('/track/')) {
                const match = code.match(/\/track\/([A-Za-z0-9_-]+)/);
                if (match && match[1]) {
                    code = match[1];
                    console.log('📦 Code extrait de l\'URL:', code);
                }
            }
            
            // 2. Supprimer paramètres URL si présents
            if (code.includes('?')) {
                code = code.split('?')[0];
            }
            
            // 3. Supprimer espaces et convertir en majuscules
            code = code.toUpperCase().replace(/\s+/g, '');
            
            return code;
        },
        
        // AFFICHER ERREUR avec ANTI-SPAM
        showScanError(code, message, vibratePattern) {
            const now = Date.now();
            const errorKey = `${code}_${message}`;
            
            // Vérifier si cette erreur a été affichée récemment (< 3 secondes)
            const lastTime = this.errorBuffer.get(errorKey) || 0;
            if (now - lastTime < 3000) {
                console.log('🔇 Erreur ignorée (anti-spam):', message);
                return; // Ignorer (anti-spam)
            }
            
            // Enregistrer cette erreur
            this.errorBuffer.set(errorKey, now);
            
            // Afficher
            this.statusText = message;
            showToast(message, 'error');
            this.playSound('error');
            if (navigator.vibrate && vibratePattern) {
                navigator.vibrate(vibratePattern);
            }
        },
        
        // GÉRER CODE SCANNÉ avec vérification DB locale (AMÉLIORÉ)
        handleScannedCode(rawCode, type) {
            const now = Date.now();
            
            // AMÉLIORATION 1: Extraire code propre (URLs, espaces, etc.)
            let code = this.extractCleanCode(rawCode);
            
            // Validation basique
            if (!code || code.length < 3) {
                console.log('❌ Code trop court ignoré:', rawCode);
                return;
            }
            
            console.log(`🔍 ${type} scanné:`, code);
            
            // Anti-doublon temps (500ms au lieu de 2000ms pour plus de réactivité)
            if (code === this.lastScannedCode && (now - this.lastScanTime) < 500) {
                return;
            }
            
            // CORRECTION: Anti-doublon - vérifier avec variantes
            const isDuplicate = this.scannedCodes.find(item => {
                // Comparer le code scanné avec le code et toutes les variantes
                return item.code === code || 
                       item.code.replace(/[_\-\s]/g, '') === code.replace(/[_\-\s]/g, '');
            });
            
            if (isDuplicate) {
                // AMÉLIORATION 2: Anti-spam pour erreur "déjà scanné"
                this.showScanError(code, '⚠️ Déjà scanné', [100, 50, 100]);
                return;
            }
            
            this.lastScannedCode = code;
            this.lastScanTime = now;
            
            // CORRECTION: VÉRIFIER avec PLUSIEURS variantes du code
            let packageData = this.packagesMap.get(code);
            let searchVariant = code;
            
            // Si pas trouvé, essayer sans underscores
            if (!packageData) {
                const noUnderscore = code.replace(/_/g, '');
                packageData = this.packagesMap.get(noUnderscore);
                if (packageData) {
                    searchVariant = noUnderscore;
                    console.log('✅ Trouvé avec variante sans underscore:', noUnderscore);
                }
            }
            
            // Si pas trouvé, essayer sans tirets
            if (!packageData) {
                const noDash = code.replace(/-/g, '');
                packageData = this.packagesMap.get(noDash);
                if (packageData) {
                    searchVariant = noDash;
                    console.log('✅ Trouvé avec variante sans tiret:', noDash);
                }
            }
            
            // Si pas trouvé, essayer version complètement nettoyée
            if (!packageData) {
                const cleaned = code.replace(/[_\-\s]/g, '');
                packageData = this.packagesMap.get(cleaned);
                if (packageData) {
                    searchVariant = cleaned;
                    console.log('✅ Trouvé avec variante nettoyée:', cleaned);
                }
            }
            
            if (!packageData) {
                // ❌ COLIS NON TROUVÉ - AMÉLIORATION 3: Anti-spam
                console.log('❌ Non trouvé:', code);
                console.log('📋 Colis chargés:', PACKAGES_DATA.length);
                this.showScanError(code, `❌ ${code} - Non trouvé`, [200, 100, 200]);
                
                setTimeout(() => {
                    if (this.cameraActive) {
                        this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                    }
                }, 1500);
                return;
            }
            
            console.log('✅ Colis trouvé:', packageData);
            
            // Vérifier le statut selon l'action
            const isValidForAction = this.scanAction === 'pickup' 
                ? packageData.can_pickup 
                : packageData.can_deliver;
            
            if (!isValidForAction) {
                // ⚠️ STATUT NON VALIDE - AMÉLIORATION 3: Anti-spam
                console.log('⚠️ Statut invalide:', packageData);
                this.showScanError(code, `⚠️ ${code} - Statut invalide (${packageData.status})`, [100, 50, 100, 50, 100]);
                
                setTimeout(() => {
                    if (this.cameraActive) {
                        this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                    }
                }, 1500);
                return;
            }
            
            // ✅ TOUT EST OK - AJOUTER (utiliser le code original du colis)
            const assignBadge = packageData.assigned ? '✓' : 'ℹ️';
            this.scannedCodes.push({
                code: packageData.code, // Code original du colis
                scannedAs: code, // Code qui a été scanné
                message: `${type} - ${packageData.status} ${assignBadge}`,
                assigned: packageData.assigned,
                timestamp: new Date().toISOString()
            });
            
            console.log(`✅ ${type}: ${code}`, packageData.assigned ? '(Assigné)' : '(Non assigné)');
            
            const assignInfo = packageData.assigned ? '' : ' (Non assigné)';
            this.statusText = `✅ ${code} scanné${assignInfo}`;
            showToast(`✅ ${code}${assignInfo}`, 'success');
            this.playSound('success');
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);
            
            setTimeout(() => {
                if (this.cameraActive) {
                    this.statusText = `📷 ${this.scannedCodes.length} code(s)`;
                }
            }, 1500);
        },
        
        // ARRÊTER CAMÉRA
        stopCamera() {
            try {
                // Arrêter interval
                if (this.scanInterval) {
                    clearInterval(this.scanInterval);
                    this.scanInterval = null;
                }
                
                // Arrêter Quagga
                if (typeof Quagga !== 'undefined') {
                    try { 
                        Quagga.stop(); 
                        console.log('🛑 Quagga arrêté');
                    } catch(e) {}
                }
                
                // Arrêter flux vidéo
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
                console.log('🛑 Caméra arrêtée');
                
            } catch (error) {
                console.error('Erreur arrêt caméra:', error);
            }
        },
        
        // VALIDATION FINALE DIRECTE (SANS API - Formulaire classique)
        submitForm() {
            if (this.scannedCodes.length === 0) {
                showToast('⚠️ Aucun code à valider', 'warning');
                return;
            }

            const actionLabel = this.scanAction === 'pickup' ? 'ramasser' : 'mettre en livraison';
            if (!confirm(`Confirmer ${actionLabel} pour ${this.scannedCodes.length} colis ?`)) {
                return;
            }

            this.processing = true;
            this.statusText = 'Traitement en cours...';
            
            // Soumettre le formulaire directement (pas d'API)
            document.getElementById('validation-form').submit();
        },
        
        removeCode(index) {
            this.scannedCodes.splice(index, 1);
            showToast('Code retiré', 'info');
            this.statusText = `${this.scannedCodes.length} code(s)`;
        },
        
        clearAll() {
            if (confirm('Effacer tous les codes ?')) {
                this.scannedCodes = [];
                showToast('Liste effacée', 'info');
                this.statusText = 'Prêt';
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
