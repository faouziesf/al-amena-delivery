@extends('layouts.deliverer-modern')

@section('title', 'Scanner Multiple')

@section('content')
<style>
.camera-container {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
}

.scan-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-center;
    z-index: 10;
}

.scan-box {
    width: 200px;
    height: 200px;
    border: 3px solid #fff;
    border-radius: 12px;
    position: relative;
    animation: scan-pulse 2s infinite;
}

.scanning-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10B981, transparent);
    animation: scanning 2s linear infinite;
}

@keyframes scan-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes scanning {
    0% { transform: translateY(0); }
    100% { transform: translateY(200px); }
}
</style>

<div class="min-h-screen bg-gray-50 safe-top safe-bottom" x-data="multiScannerApp()">

    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-20">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('deliverer.run.sheet') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">Scanner Multiple</h1>
                    <p class="text-sm text-gray-600">Scannez plusieurs colis</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-green-600" x-text="scannedPackages.length"></div>
                <div class="text-xs text-gray-500">Colis</div>
            </div>
        </div>
    </div>

    <!-- Scanner Section -->
    <div class="p-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-6">
            
            <!-- Camera -->
            <div class="camera-container mb-6">
                <video x-ref="video" autoplay playsinline class="w-full h-auto rounded-lg"></video>
                <div class="scan-overlay">
                    <div class="scan-box">
                        <div class="scanning-line" x-show="scanning"></div>
                    </div>
                </div>
            </div>

            <!-- Instructions Scan -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Scannez les Colis</h3>
                <p class="text-gray-600">Ajoutez plusieurs colis à la liste</p>
            </div>

            <!-- Saisie manuelle -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-4 text-sm text-gray-500">ou</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <form @submit.prevent="manualScan()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Saisir le code manuellement
                        </label>
                        <input type="text"
                               x-model="manualCode"
                               x-ref="manualInput"
                               @input="manualCode = manualCode.toUpperCase()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-mono"
                               placeholder="PKG_XXXXX">
                    </div>
                    <button type="submit"
                            :disabled="!manualCode || processing"
                            :class="!manualCode || processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                            class="w-full py-3 text-white rounded-xl font-semibold transition-colors">
                        <span x-show="!processing">Ajouter</span>
                        <span x-show="processing">Ajout...</span>
                    </button>
                </form>
            </div>

            <!-- Message erreur -->
            <div x-show="error" x-transition class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-700" x-text="errorMessage"></p>
                </div>
            </div>
        </div>

        <!-- Liste des colis scannés -->
        <div x-show="scannedPackages.length > 0" class="mb-24">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Colis Scannés</h2>
                <button @click="resetAll()" class="text-sm text-red-600 font-semibold hover:text-red-700">
                    Tout effacer
                </button>
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
                                <div class="font-mono text-sm font-bold text-green-600 mb-1" x-text="pkg.package_code"></div>
                                <div class="text-xs text-gray-600 mb-1" x-text="pkg.recipient_name || 'Destinataire'"></div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-md font-medium" x-text="pkg.status"></span>
                                    <span x-show="pkg.cod_amount > 0" class="text-xs font-semibold text-green-600" x-text="pkg.cod_amount + ' DT'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-24">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-green-900 mb-2">Instructions</h3>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>• Scannez les colis un par un</li>
                        <li>• Les colis s'ajoutent automatiquement</li>
                        <li>• Retirez un colis en cliquant sur ✕</li>
                        <li>• Validez quand tous les colis sont scannés</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action fixés en bas -->
    <div x-show="scannedPackages.length > 0" 
         class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 safe-bottom z-30">
        <button @click="validateAll()" 
                :disabled="validating"
                :class="validating ? 'opacity-50 cursor-not-allowed' : ''"
                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-4 rounded-xl font-bold hover:from-green-600 hover:to-green-700 transition-all">
            <span x-show="!validating" x-text="'Valider (' + scannedPackages.length + ' colis)'"></span>
            <span x-show="validating">Validation...</span>
        </button>
    </div>
</div>

<!-- QR Scanner Library -->
<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>

<script>
function multiScannerApp() {
    return {
        scanner: null,
        scanning: true,
        scannedPackages: [],
        error: false,
        errorMessage: '',
        manualCode: '',
        processing: false,
        validating: false,
        lastScanTime: 0,

        init() {
            this.startScanner();
        },

        async startScanner() {
            try {
                const video = this.$refs.video;
                
                this.scanner = new QrScanner(video, result => {
                    this.processScan(result.data);
                }, {
                    highlightScanRegion: false,
                    highlightCodeOutline: false,
                    preferredCamera: 'environment'
                });

                await this.scanner.start();
                this.scanning = true;
                this.error = false;
            } catch (err) {
                this.showError('Impossible d\'accéder à la caméra. Utilisez la saisie manuelle.');
                console.error('Erreur caméra:', err);
            }
        },

        async processScan(code) {
            const now = Date.now();
            if (now - this.lastScanTime < 1000) return;
            this.lastScanTime = now;

            this.processing = true;

            try {
                // Vibration
                if (navigator.vibrate) {
                    navigator.vibrate(200);
                }

                const response = await fetch('{{ route("deliverer.scan.multi.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ qr_code: code })
                });

                const data = await response.json();

                if (data.success && data.package) {
                    // Vérifier si déjà scanné
                    if (this.scannedPackages.some(p => p.id === data.package.id)) {
                        this.showError('Colis déjà scanné');
                    } else {
                        this.scannedPackages.push(data.package);
                        if (typeof showToast !== 'undefined') {
                            showToast('Colis ajouté !', 'success', 2000);
                        }
                    }
                    this.manualCode = '';
                } else {
                    this.showError(data.message || 'Code non reconnu');
                }
            } catch (err) {
                console.error('Erreur:', err);
                this.showError('Erreur de connexion au serveur');
            } finally {
                this.processing = false;
            }
        },

        async manualScan() {
            if (!this.manualCode) return;
            await this.processScan(this.manualCode);
        },

        removePackage(index) {
            this.scannedPackages.splice(index, 1);
            if (typeof showToast !== 'undefined') {
                showToast('Colis retiré', 'info', 2000);
            }
        },

        resetAll() {
            if (confirm('Effacer tous les colis scannés ?')) {
                this.scannedPackages = [];
                this.manualCode = '';
            }
        },

        async validateAll() {
            if (this.scannedPackages.length === 0) return;

            this.validating = true;

            try {
                const response = await fetch('{{ route("deliverer.scan.multi.validate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        packages: this.scannedPackages.map(p => p.id)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    if (typeof showToast !== 'undefined') {
                        showToast('Colis validés avec succès !', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = '{{ route("deliverer.run.sheet") }}';
                    }, 1500);
                } else {
                    this.showError(data.message || 'Erreur de validation');
                }
            } catch (err) {
                console.error('Erreur:', err);
                this.showError('Erreur de connexion au serveur');
            } finally {
                this.validating = false;
            }
        },

        showError(message) {
            this.error = true;
            this.errorMessage = message;
            setTimeout(() => {
                this.error = false;
            }, 3000);
        },

        destroy() {
            if (this.scanner) {
                this.scanner.stop();
                this.scanner.destroy();
            }
        }
    }
}
</script>

@endsection
