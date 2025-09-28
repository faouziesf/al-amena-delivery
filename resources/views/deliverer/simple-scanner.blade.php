@extends('layouts.deliverer')

@section('content')
<div class="bg-gray-100" x-data="qrScanner()">

    <!-- Scanner View -->
    <div class="relative" x-show="!result">
        <div class="aspect-square bg-black relative">
            <video id="scanner-video" class="w-full h-full object-cover" autoplay playsinline></video>

            <!-- Overlay de visée -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative">
                    <!-- Carré de visée -->
                    <div class="w-64 h-64 border-4 border-white border-opacity-50 relative">
                        <!-- Coins -->
                        <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-blue-400"></div>
                        <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-blue-400"></div>
                        <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-blue-400"></div>
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-blue-400"></div>

                        <!-- Ligne de scan animée -->
                        <div class="absolute inset-x-0 h-0.5 bg-blue-400 animate-pulse"
                             style="top: 50%; animation: scanLine 2s linear infinite;"></div>
                    </div>

                    <!-- Instructions -->
                    <p class="text-white text-center mt-4 bg-black bg-opacity-50 px-4 py-2 rounded-lg">
                        Centrez le QR code dans le carré
                    </p>
                </div>
            </div>
        </div>

        <!-- Boutons de contrôle -->
        <div class="p-4 bg-white content-mobile-spacing">
            <div class="flex gap-4 justify-center">
                <button @click="toggleFlash()" class="flex-1 max-w-32 bg-gray-600 text-white py-3 px-4 rounded-lg active:scale-95">
                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="block text-sm mt-1">Flash</span>
                </button>

                <a href="{{ route('deliverer.simple.dashboard') }}" class="flex-1 max-w-32 bg-blue-600 text-white py-3 px-4 rounded-lg active:scale-95 text-center">
                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    <span class="block text-sm mt-1">Tournée</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Résultat du scan -->
    <div x-show="result" class="p-4" x-transition>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">QR Code Scanné</h2>
            </div>

            <!-- Détails du colis/collecte -->
            <div x-show="result?.type === 'package'" class="space-y-4">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="font-semibold text-gray-800">Colis</h3>
                    <p class="text-gray-600" x-text="result?.data?.tracking_number"></p>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <span class="text-sm text-gray-500">Destinataire</span>
                        <p class="font-medium" x-text="result?.data?.recipient_name"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Adresse</span>
                        <p class="text-sm" x-text="result?.data?.recipient_address"></p>
                    </div>
                    <div x-show="result?.data?.cod_amount > 0">
                        <span class="text-sm text-gray-500">COD</span>
                        <p class="font-bold text-green-600" x-text="result?.data?.cod_amount + ' DA'"></p>
                    </div>
                </div>

                <!-- Actions pour colis -->
                <div class="grid grid-cols-3 gap-3 mt-6">
                    <button @click="markDelivered()" class="bg-green-600 text-white py-3 px-4 rounded-lg text-sm font-medium active:scale-95">
                        Livré
                    </button>
                    <button @click="markUnavailable()" class="bg-orange-600 text-white py-3 px-4 rounded-lg text-sm font-medium active:scale-95">
                        Indisponible
                    </button>
                    <button @click="markCancelled()" class="bg-red-600 text-white py-3 px-4 rounded-lg text-sm font-medium active:scale-95">
                        Annulé
                    </button>
                </div>
            </div>

            <!-- Détails de la collecte -->
            <div x-show="result?.type === 'pickup'" class="space-y-4">
                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="font-semibold text-gray-800">Collecte</h3>
                    <p class="text-gray-600" x-text="result?.data?.packages_count + ' colis'"></p>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <span class="text-sm text-gray-500">Contact</span>
                        <p class="font-medium" x-text="result?.data?.pickup_contact"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Adresse</span>
                        <p class="text-sm" x-text="result?.data?.pickup_address"></p>
                    </div>
                </div>

                <!-- Action pour collecte -->
                <div class="mt-6">
                    <button @click="markPickup()" class="w-full bg-purple-600 text-white py-4 px-4 rounded-lg font-medium active:scale-95">
                        Colis Collectés
                    </button>
                </div>
            </div>

            <!-- Boutons généraux -->
            <div class="flex gap-3 mt-6 content-mobile-spacing">
                <button @click="resetScanner()" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium active:scale-95">
                    Scanner Autre
                </button>
                <a href="{{ route('deliverer.simple.dashboard') }}" class="flex-1 bg-gray-600 text-white py-3 px-4 rounded-lg font-medium active:scale-95 text-center">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700">Traitement...</p>
        </div>
    </div>

</div>

<style>
@keyframes scanLine {
    0% { top: 0%; }
    50% { top: 50%; }
    100% { top: 100%; }
}
</style>

<script>
function qrScanner() {
    return {
        scanner: null,
        stream: null,
        result: null,
        loading: false,
        flashOn: false,

        init() {
            this.startScanner();
        },

        async startScanner() {
            try {
                const video = document.getElementById('scanner-video');

                // Demander l'accès à la caméra
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment', // Caméra arrière
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });

                video.srcObject = this.stream;

                // Initialiser le scanner QR
                if (typeof QrScanner !== 'undefined') {
                    this.scanner = new QrScanner(video,
                        result => this.onScanSuccess(result),
                        {
                            highlightScanRegion: false,
                            highlightCodeOutline: false,
                        }
                    );
                    await this.scanner.start();
                } else {
                    // Fallback manuel si QrScanner n'est pas disponible
                    this.startManualScan(video);
                }

            } catch (error) {
                console.error('Erreur scanner:', error);
                alert('Impossible d\'accéder à la caméra');
            }
        },

        onScanSuccess(result) {
            this.processScan(result);
        },

        async processScan(qrCode) {
            this.loading = true;

            try {
                const response = await fetch('/deliverer/scan/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ qr_code: qrCode })
                });

                const data = await response.json();

                if (data.success) {
                    this.result = data;
                    this.stopScanner();
                } else {
                    alert(data.message || 'QR code non reconnu');
                }

            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de traitement');
            } finally {
                this.loading = false;
            }
        },

        async markPickup() {
            if (!this.result?.data?.id) return;

            this.loading = true;
            try {
                const response = await fetch(`/deliverer/pickup/${this.result.data.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                alert(data.message);

                if (data.success) {
                    window.history.back();
                }
            } catch (error) {
                alert('Erreur de traitement');
            } finally {
                this.loading = false;
            }
        },

        async markDelivered() {
            if (!this.result?.data?.id) return;

            this.loading = true;
            try {
                const response = await fetch(`/deliverer/deliver/${this.result.data.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                alert(data.message);

                if (data.success) {
                    window.history.back();
                }
            } catch (error) {
                alert('Erreur de traitement');
            } finally {
                this.loading = false;
            }
        },

        async markUnavailable() {
            if (!this.result?.data?.id) return;

            this.loading = true;
            try {
                const response = await fetch(`/deliverer/unavailable/${this.result.data.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                alert(data.message);

                if (data.success) {
                    window.history.back();
                }
            } catch (error) {
                alert('Erreur de traitement');
            } finally {
                this.loading = false;
            }
        },

        async markCancelled() {
            if (!this.result?.data?.id) return;

            this.loading = true;
            try {
                const response = await fetch(`/deliverer/cancelled/${this.result.data.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                alert(data.message);

                if (data.success) {
                    window.history.back();
                }
            } catch (error) {
                alert('Erreur de traitement');
            } finally {
                this.loading = false;
            }
        },

        async toggleFlash() {
            if (this.stream) {
                const track = this.stream.getVideoTracks()[0];
                const capabilities = track.getCapabilities();

                if (capabilities.torch) {
                    try {
                        await track.applyConstraints({
                            advanced: [{ torch: !this.flashOn }]
                        });
                        this.flashOn = !this.flashOn;
                    } catch (error) {
                        console.error('Flash non supporté:', error);
                    }
                }
            }
        },

        resetScanner() {
            this.result = null;
            this.startScanner();
        },

        stopScanner() {
            if (this.scanner) {
                this.scanner.stop();
                this.scanner.destroy();
                this.scanner = null;
            }

            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
        },

        // Fallback pour scan manuel
        startManualScan(video) {
            console.log('Scanner manuel activé');
            // Ici on pourrait implémenter une détection manuelle ou utiliser une autre librairie
        }
    }
}
</script>

<!-- QR Scanner Library -->
<script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>

@endsection