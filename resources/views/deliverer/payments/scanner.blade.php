@extends('layouts.deliverer')

@section('title', 'Scanner Paiement Espèces')

@section('content')
<div x-data="paymentScannerApp()" class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-20">
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('deliverer.payments.index') }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Scanner Paiement</h1>
                        <p class="text-sm text-gray-600">Scannez le code de retrait</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 rounded-full" :class="isScanning ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"></div>
                        <span class="text-xs" :class="isScanning ? 'text-green-600' : 'text-gray-500'" x-text="isScanning ? 'Scanner actif' : 'Scanner arrêté'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Scanner -->
    <div class="relative">
        <div id="camera-container" class="relative bg-black" style="height: 300px;">
            <video id="camera-preview" class="w-full h-full object-cover" autoplay playsinline></video>

            <!-- Scanner Overlay -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative">
                    <!-- Scanning Frame -->
                    <div class="w-64 h-64 border-2 border-green-500 rounded-lg relative">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-white rounded-br-lg"></div>

                        <!-- Scanning Line -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-green-500 animate-pulse" x-show="isScanning"></div>
                    </div>

                    <!-- Instructions -->
                    <p class="text-white text-center mt-4 text-sm bg-black bg-opacity-50 px-4 py-2 rounded-lg">
                        Placez le code QR dans le cadre
                    </p>
                </div>
            </div>

            <!-- Camera Controls -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-4">
                <button @click="toggleScanner()"
                        class="bg-white bg-opacity-90 text-gray-800 p-3 rounded-full shadow-lg hover:bg-opacity-100 transition-all">
                    <svg class="w-6 h-6" x-show="!isScanning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10H6a2 2 0 00-2 2v3a2 2 0 002 2h12a2 2 0 002-2v-3a2 2 0 00-2-2h-3m-9 0V8a2 2 0 012-2h3m0 0V4a2 2 0 012-2h1a2 2 0 012 2v2m0 0h3a2 2 0 012 2v2"/>
                    </svg>
                    <svg class="w-6 h-6" x-show="isScanning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                </button>
                <button @click="switchCamera()"
                        class="bg-white bg-opacity-90 text-gray-800 p-3 rounded-full shadow-lg hover:bg-opacity-100 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Manual Input -->
    <div class="p-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Saisie Manuelle</h3>
            <div class="flex space-x-2">
                <input type="text"
                       x-model="manualCode"
                       placeholder="Code de retrait (ex: WDR-2024-001)"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <button @click="processManualCode()"
                        :disabled="!manualCode.trim()"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Valider
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Scans -->
    <div class="p-4" x-show="recentScans.length > 0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Derniers Scans</h3>
            <div class="space-y-2">
                <template x-for="scan in recentScans.slice(0, 3)" :key="scan.code">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800" x-text="scan.code"></p>
                            <p class="text-sm text-gray-600" x-text="scan.timestamp"></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm px-2 py-1 rounded-full"
                                  :class="scan.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                  x-text="scan.status === 'success' ? 'Trouvé' : 'Erreur'"></span>
                            <button @click="retryCode(scan.code)"
                                    x-show="scan.status !== 'success'"
                                    class="text-green-600 hover:text-green-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="p-4">
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-800">Comment scanner ?</h4>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1">
                        <li>• Pointez la caméra vers le code QR du bon de livraison</li>
                        <li>• Assurez-vous que le code est bien visible dans le cadre</li>
                        <li>• En cas d'échec, utilisez la saisie manuelle</li>
                        <li>• Le code de retrait commence par "WDR-"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div x-show="showPaymentModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
     @click="showPaymentModal = false">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6" x-show="currentPayment">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Paiement Trouvé</h3>
                    <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-800" x-text="formatAmount(currentPayment?.amount)"></p>
                            <p class="text-sm text-green-600">Montant à livrer</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Code:</span>
                            <span class="font-medium" x-text="currentPayment?.request_code"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Client:</span>
                            <span class="font-medium" x-text="currentPayment?.client_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Téléphone:</span>
                            <span class="font-medium" x-text="currentPayment?.client_phone"></span>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-800 font-medium">⚠️ Important</p>
                        <p class="text-sm text-yellow-700">Vérifiez l'identité du client avant de remettre les espèces</p>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button @click="confirmDelivery()"
                                class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 font-medium">
                            Confirmer Livraison
                        </button>
                        <button @click="showPaymentModal = false"
                                class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-400 font-medium">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentScannerApp() {
    return {
        isScanning: false,
        manualCode: '',
        currentPayment: null,
        showPaymentModal: false,
        recentScans: JSON.parse(localStorage.getItem('recentPaymentScans') || '[]'),
        cameraStream: null,
        currentCamera: 'environment', // 'user' for front camera

        init() {
            this.loadRecentScans();
        },

        async toggleScanner() {
            if (this.isScanning) {
                this.stopScanner();
            } else {
                await this.startScanner();
            }
        },

        async startScanner() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: this.currentCamera,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });

                const video = document.getElementById('camera-preview');
                video.srcObject = stream;
                this.cameraStream = stream;
                this.isScanning = true;

                // Start QR code detection
                this.startQRDetection(video);

            } catch (error) {
                console.error('Erreur accès caméra:', error);
                this.showToast('Impossible d\'accéder à la caméra', 'error');
            }
        },

        stopScanner() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
            this.isScanning = false;
        },

        async switchCamera() {
            this.stopScanner();
            this.currentCamera = this.currentCamera === 'environment' ? 'user' : 'environment';
            if (this.isScanning) {
                await this.startScanner();
            }
        },

        startQRDetection(video) {
            // Simplified QR detection - in real app, use a proper QR library
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            const detectQR = () => {
                if (!this.isScanning) return;

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);

                // Here you would use a QR code library to detect codes
                // For now, we'll simulate detection

                setTimeout(detectQR, 500);
            };

            video.addEventListener('loadedmetadata', detectQR);
        },

        async processCode(code) {
            try {
                this.addToRecentScans(code, 'processing');

                const response = await fetch(`/deliverer/payments/scan/${encodeURIComponent(code)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const payment = await response.json();
                    this.currentPayment = payment;
                    this.showPaymentModal = true;
                    this.addToRecentScans(code, 'success');
                    this.stopScanner();
                } else {
                    const error = await response.json();
                    this.showToast(error.message || 'Paiement non trouvé', 'error');
                    this.addToRecentScans(code, 'error');
                }
            } catch (error) {
                console.error('Erreur traitement code:', error);
                this.showToast('Erreur de connexion', 'error');
                this.addToRecentScans(code, 'error');
            }
        },

        processManualCode() {
            if (!this.manualCode.trim()) return;
            this.processCode(this.manualCode.trim());
            this.manualCode = '';
        },

        retryCode(code) {
            this.processCode(code);
        },

        async confirmDelivery() {
            if (!this.currentPayment) return;

            try {
                const response = await fetch(`/deliverer/payments/${this.currentPayment.id}/deliver`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        delivery_method: 'scanner',
                        delivery_notes: 'Livraison confirmée via scanner mobile'
                    })
                });

                if (response.ok) {
                    this.showToast('Paiement livré avec succès', 'success');
                    this.showPaymentModal = false;
                    this.currentPayment = null;

                    // Return to payments list
                    setTimeout(() => {
                        window.location.href = '{{ route("deliverer.payments.index") }}';
                    }, 1500);
                } else {
                    const error = await response.json();
                    this.showToast(error.message || 'Erreur lors de la confirmation', 'error');
                }
            } catch (error) {
                console.error('Erreur confirmation livraison:', error);
                this.showToast('Erreur de connexion', 'error');
            }
        },

        addToRecentScans(code, status) {
            const scan = {
                code,
                status,
                timestamp: new Date().toLocaleTimeString('fr-FR')
            };

            this.recentScans.unshift(scan);
            this.recentScans = this.recentScans.slice(0, 10); // Keep only last 10
            localStorage.setItem('recentPaymentScans', JSON.stringify(this.recentScans));
        },

        loadRecentScans() {
            this.recentScans = JSON.parse(localStorage.getItem('recentPaymentScans') || '[]');
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };

            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }
    }
}
</script>
@endpush