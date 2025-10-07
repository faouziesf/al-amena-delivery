@extends('layouts.deliverer')

@section('title', 'Scanner Collecte')

@section('content')
<style>
.camera-container {
    position: relative;
    max-width: 400px;
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
    justify-content: center;
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

.scan-box::before {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    border: 3px solid #A78BFA;
    border-radius: 12px;
    animation: scan-glow 2s infinite;
}

@keyframes scan-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes scan-glow {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.scanning-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #A78BFA, transparent);
    animation: scanning 2s linear infinite;
}

@keyframes scanning {
    0% { transform: translateY(0); }
    100% { transform: translateY(200px); }
}
</style>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8" x-data="scannerApp()">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <a href="{{ route('deliverer.tournee') }}" class="mr-4 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Scanner une Collecte</h1>
                <p class="text-gray-600">Scannez le QR code d'une demande de collecte</p>
            </div>
        </div>
    </div>

    <!-- Scanner Container -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-8">

        <!-- Instructions -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-200 to-purple-300 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Scanner le QR Code</h3>
            <p class="text-gray-600">Positionnez le QR code dans le cadre pour l'analyser</p>
        </div>

        <!-- Camera Section -->
        <div x-show="!scanned" class="camera-container" x-transition>
            <video x-ref="video" autoplay playsinline class="w-full h-auto rounded-lg"></video>
            <div class="scan-overlay">
                <div class="scan-box">
                    <div class="scanning-line" x-show="scanning"></div>
                </div>
            </div>
        </div>

        <!-- Manual Input Section -->
        <div class="mt-6">
            <div class="flex items-center justify-center mb-4">
                <div class="flex-1 border-t border-gray-300"></div>
                <span class="px-4 text-sm text-gray-500">ou</span>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            <form @submit.prevent="manualScan()" class="space-y-4">
                <div>
                    <label for="manual_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Saisir manuellement
                    </label>
                    <input type="text"
                           x-model="manualCode"
                           id="manual_code"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-300 focus:border-purple-300 transition-all duration-200"
                           placeholder="ID de la demande de collecte">
                </div>
                <button type="submit"
                        :disabled="!manualCode"
                        :class="manualCode ? 'bg-purple-600 hover:bg-purple-700 text-white' : 'bg-gray-400 cursor-not-allowed text-white'"
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Rechercher
                </button>
            </form>
        </div>

        <!-- Error Messages -->
        <div x-show="error" x-transition class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-700" x-text="errorMessage"></p>
            </div>
        </div>
    </div>

    <!-- Instructions d'utilisation -->
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-purple-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-purple-900 mb-2">Instructions</h3>
                <ul class="text-sm text-purple-700 space-y-1">
                    <li>• Positionnez le QR code dans le cadre de scan</li>
                    <li>• Maintenez l'appareil stable pour une meilleure lecture</li>
                    <li>• Assurez-vous d'avoir un bon éclairage</li>
                    <li>• Vous pouvez également saisir l'ID manuellement</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Inclure la bibliothèque QR Scanner -->
<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>

<script>
function scannerApp() {
    return {
        scanner: null,
        scanning: true,
        scanned: false,
        error: false,
        errorMessage: '',
        manualCode: '',
        lastScanTime: 0,

        init() {
            this.startScanner();
        },

        async startScanner() {
            try {
                const video = this.$refs.video;

                this.scanner = new QrScanner(video, result => {
                    this.processScan(result);
                }, {
                    highlightScanRegion: false,
                    highlightCodeOutline: false,
                });

                await this.scanner.start();
                this.scanning = true;
                this.error = false;
            } catch (err) {
                this.showError('Impossible d\'accéder à la caméra. Veuillez utiliser la saisie manuelle.');
                console.error('Erreur caméra:', err);
            }
        },

        async processScan(code) {
            // Éviter les scans multiples rapides
            const now = Date.now();
            if (now - this.lastScanTime < 2000) {
                return;
            }
            this.lastScanTime = now;

            this.scanning = false;

            try {
                const response = await fetch('{{ route("deliverer.pickups.scan.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        qr_code: code
                    })
                });

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    // Vibration si disponible
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                    
                    // Redirection immédiate
                    window.location.href = data.redirect_url;
                } else {
                    this.showError(data.message || 'Code non reconnu');
                    setTimeout(() => {
                        this.resetScanner();
                    }, 2000);
                }
            } catch (err) {
                this.showError('Erreur de connexion');
                setTimeout(() => {
                    this.resetScanner();
                }, 2000);
            }
        },

        async manualScan() {
            if (!this.manualCode) return;
            await this.processScan(this.manualCode);
        },

        showError(message) {
            this.error = true;
            this.errorMessage = message;
            this.scanning = false;
        },

        resetScanner() {
            this.scanned = false;
            this.error = false;
            this.manualCode = '';
            this.scanning = true;
            this.lastScanTime = 0;

            setTimeout(() => {
                this.startScanner();
            }, 100);
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
