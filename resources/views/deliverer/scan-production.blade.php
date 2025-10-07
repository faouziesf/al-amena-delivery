@extends('layouts.deliverer-modern')

@section('title', 'Scanner')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="scannerProduction()">
    
    <!-- Header -->
    <div class="bg-indigo-600 text-white safe-top px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('deliverer.tournee') }}" class="mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold">Scanner Colis</h1>
                    <p class="text-indigo-200 text-sm" x-text="cameraActive ? 'Caméra active' : 'Caméra désactivée'"></p>
                </div>
            </div>
            <button @click="toggleCamera()" 
                    :class="cameraActive ? 'bg-green-500' : 'bg-white/20'"
                    class="p-2 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="p-4 pb-24">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl fade-in">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl fade-in">
                ❌ {{ session('error') }}
            </div>
        @endif

        <!-- Caméra -->
        <div x-show="cameraActive" x-transition class="card mb-4 overflow-hidden">
            <video x-ref="video" autoplay playsinline class="w-full rounded-lg bg-black"></video>
            <div class="p-4 text-center">
                <p class="text-sm text-gray-600">Positionnez le QR code dans le cadre</p>
            </div>
        </div>

        <!-- Formulaire saisie manuelle -->
        <div class="card p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Saisie Manuelle
            </h2>
            
            <form action="{{ route('deliverer.scan.submit') }}" method="POST" id="scanForm">
                @csrf
                <input type="hidden" name="code" x-model="scannedCode">
                
                <div class="mb-4">
                    <input 
                        type="text" 
                        x-model="manualCode"
                        @keyup.enter="submitManual()"
                        placeholder="Scannez ou saisissez le code..."
                        class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        autofocus>
                </div>
                
                <button type="button" 
                        @click="submitManual()"
                        class="w-full btn bg-indigo-600 text-white hover:bg-indigo-700 text-lg py-4">
                    🔍 Rechercher Colis
                </button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Instructions
            </h3>
            <ul class="text-sm text-blue-700 space-y-2">
                <li class="flex items-start">
                    <span class="mr-2">📷</span>
                    <span><strong>Caméra</strong>: Cliquez sur l'icône caméra en haut</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">⌨️</span>
                    <span><strong>Saisie</strong>: Tapez le code et validez</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">📱</span>
                    <span><strong>Lecteur</strong>: Utilisez un lecteur USB/Bluetooth</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">✨</span>
                    <span>Le colis sera automatiquement assigné</span>
                </li>
            </ul>
        </div>

        <!-- Historique -->
        @if(session('last_scans'))
            <div class="mt-6">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Derniers scans
                </h3>
                <div class="space-y-2">
                    @foreach(session('last_scans') as $scan)
                        <a href="/deliverer/task/{{ $scan['package_id'] }}" 
                           class="card p-3 flex items-center justify-between hover:shadow-md transition-shadow">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $scan['code'] }}</div>
                                <div class="text-xs text-gray-500">{{ $scan['time'] }}</div>
                            </div>
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- QR Scanner Library (Léger) -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
function scannerProduction() {
    return {
        cameraActive: false,
        manualCode: '',
        scannedCode: '',
        stream: null,
        scanning: false,
        animationFrame: null,

        init() {
            // Auto-focus sur input
            this.$nextTick(() => {
                const input = this.$el.querySelector('input[type="text"]');
                if (input) input.focus();
            });
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
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });
                
                this.$refs.video.srcObject = this.stream;
                this.cameraActive = true;
                this.scanning = true;
                
                // Attendre que vidéo soit prête
                await new Promise(resolve => {
                    this.$refs.video.onloadedmetadata = resolve;
                });
                
                this.scanFrame();
            } catch (err) {
                console.error('Erreur caméra:', err);
                alert('Impossible d\'accéder à la caméra. Utilisez la saisie manuelle.');
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
            }
            this.cameraActive = false;
            this.scanning = false;
        },

        scanFrame() {
            if (!this.scanning || !this.cameraActive) return;

            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            if (canvas.width > 0 && canvas.height > 0) {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code && code.data) {
                    // Code détecté !
                    this.scannedCode = code.data;
                    this.stopCamera();
                    
                    // Vibration
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                    
                    // Soumettre formulaire
                    document.getElementById('scanForm').submit();
                    return;
                }
            }

            this.animationFrame = requestAnimationFrame(() => this.scanFrame());
        },

        submitManual() {
            if (this.manualCode.trim()) {
                this.scannedCode = this.manualCode.trim();
                document.getElementById('scanForm').submit();
            }
        },

        destroy() {
            this.stopCamera();
        }
    }
}
</script>

@endsection
