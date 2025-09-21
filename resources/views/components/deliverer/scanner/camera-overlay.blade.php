@props([
    'showGuides' => true,
    'showLaser' => true,
    'aspectRatio' => '1:1' // 1:1, 4:3, 16:9
])

<div x-show="activeMode === 'camera'" class="space-y-4">
    <!-- Camera Permission Request -->
    <div x-show="!permissionAsked && !cameraActive"
         class="text-center p-6 bg-blue-50 rounded-xl border border-blue-200">
        <div class="mb-4">
            <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h4 class="text-lg font-semibold text-blue-900 mb-2">Activer la caméra</h4>
        <p class="text-sm text-blue-700 mb-4">
            Autorisez l'accès à la caméra pour scanner automatiquement les codes QR et codes-barres.
        </p>
        <button @click="requestCameraPermission()"
                class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Autoriser l'accès
        </button>
    </div>

    <!-- Camera Container -->
    <div x-show="permissionAsked"
         class="relative bg-black rounded-2xl overflow-hidden camera-container"
         :style="`aspect-ratio: ${getAspectRatio()}`">

        <!-- Video Element -->
        <video x-ref="videoElement"
               class="w-full h-full object-cover"
               autoplay playsinline muted
               x-show="cameraActive && !cameraErrorMsg"></video>

        <!-- Hidden Canvas for QR Analysis -->
        <canvas x-ref="canvasElement" class="hidden"></canvas>

        <!-- Loading State -->
        <div class="absolute inset-0 flex items-center justify-center">
            <div x-show="permissionAsked && !cameraActive && !cameraErrorMsg"
                 class="text-white text-center">
                <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-lg font-medium">Démarrage de la caméra...</p>
                <p class="text-sm opacity-75">Veuillez patienter</p>
            </div>

            <!-- Error State -->
            <div x-show="cameraErrorMsg"
                 class="bg-red-500 text-white p-6 rounded-xl text-center max-w-xs mx-4">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p class="text-sm mb-3 font-medium">Erreur Caméra</p>
                <p class="text-xs mb-4" x-text="cameraErrorMsg"></p>
                <button @click="retryCamera()"
                        class="w-full bg-white text-red-500 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réessayer
                </button>
            </div>
        </div>

        @if($showGuides)
        <!-- Scanning Overlay with Guides -->
        <div x-show="cameraActive && !cameraErrorMsg"
             class="absolute inset-0 pointer-events-none scanning-overlay"
             style="box-shadow: inset 0 0 0 9999px rgba(0,0,0,0.6);">

            <!-- Scan Area -->
            <div class="w-full h-full relative scan-area"
                 style="clip-path: polygon(15% 15%, 85% 15%, 85% 85%, 15% 85%);">

                @if($showLaser)
                <!-- Laser Line Animation -->
                <div class="laser-line"></div>
                @endif

                <!-- Corner Guides -->
                <div class="absolute top-0 left-0 w-8 h-8">
                    <div class="absolute top-0 left-0 w-full h-1 bg-emerald-400"></div>
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
                </div>
                <div class="absolute top-0 right-0 w-8 h-8">
                    <div class="absolute top-0 right-0 w-full h-1 bg-emerald-400"></div>
                    <div class="absolute top-0 right-0 w-1 h-full bg-emerald-400"></div>
                </div>
                <div class="absolute bottom-0 left-0 w-8 h-8">
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-400"></div>
                    <div class="absolute bottom-0 left-0 w-1 h-full bg-emerald-400"></div>
                </div>
                <div class="absolute bottom-0 right-0 w-8 h-8">
                    <div class="absolute bottom-0 right-0 w-full h-1 bg-emerald-400"></div>
                    <div class="absolute bottom-0 right-0 w-1 h-full bg-emerald-400"></div>
                </div>

                <!-- Center Crosshair -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <div class="w-6 h-0.5 bg-emerald-400 opacity-75"></div>
                    <div class="w-0.5 h-6 bg-emerald-400 opacity-75 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></div>
                </div>
            </div>

            <!-- Instructions Overlay -->
            <div class="absolute bottom-4 left-4 right-4">
                <div class="bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg text-center">
                    <p class="text-sm font-medium">Positionnez le code dans le cadre</p>
                    <p class="text-xs opacity-75">QR Code ou Code-barres</p>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="absolute top-4 right-4">
                <div :class="getStatusIndicatorClass()"
                     class="flex items-center space-x-2 px-3 py-2 rounded-lg">
                    <div class="w-2 h-2 rounded-full animate-pulse"
                         :class="cameraActive ? 'bg-emerald-400' : 'bg-red-400'"></div>
                    <span class="text-sm font-medium" x-text="getStatusText()"></span>
                </div>
            </div>

            <!-- Scan Mode Indicator -->
            <div class="absolute top-4 left-4">
                <div class="bg-black bg-opacity-50 text-white px-3 py-2 rounded-lg">
                    <span class="text-xs font-medium" x-text="getScanModeText()"></span>
                </div>
            </div>
        </div>
        @endif

        <!-- Flash/Torch Control (Mobile) -->
        <div x-show="cameraActive && isMobile" class="absolute bottom-4 right-4">
            <button @click="toggleTorch()"
                    :class="torchEnabled ? 'bg-yellow-500' : 'bg-black bg-opacity-50'"
                    class="text-white p-3 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Camera Controls -->
    <div x-show="permissionAsked" class="flex justify-center space-x-3">
        <!-- Start Camera -->
        <button @click="startCamera()"
                x-show="!cameraActive && !cameraErrorMsg"
                class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9 3h10a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Démarrer</span>
        </button>

        <!-- Stop Camera -->
        <button @click="stopCamera()"
                x-show="cameraActive"
                class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/>
            </svg>
            <span>Arrêter</span>
        </button>

        <!-- Switch Camera (Mobile) -->
        <button @click="switchCamera()"
                x-show="cameraActive && isMobile && hasMultipleCameras"
                class="bg-gray-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-700 transition-colors flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
            <span>Changer</span>
        </button>
    </div>

    <!-- Camera Quality Settings -->
    <div x-show="cameraActive" class="bg-gray-50 rounded-lg p-3">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Qualité de scan</span>
            <select x-model="scanQuality" @change="adjustScanQuality()"
                    class="text-sm border-gray-300 rounded">
                <option value="low">Économique</option>
                <option value="medium" selected>Standard</option>
                <option value="high">Haute qualité</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs text-gray-600">
            <div>
                <span class="font-medium">Résolution:</span>
                <span x-text="getCurrentResolution()"></span>
            </div>
            <div>
                <span class="font-medium">FPS:</span>
                <span x-text="getCurrentFPS()"></span>
            </div>
        </div>
    </div>

    <!-- Troubleshooting Tips -->
    <div x-show="cameraErrorMsg" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <div class="flex-1">
                <h5 class="text-sm font-medium text-yellow-800 mb-2">Conseils de dépannage</h5>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Vérifiez que la caméra n'est pas utilisée par une autre application</li>
                    <li>• Assurez-vous d'avoir autorisé l'accès à la caméra</li>
                    <li>• Essayez de recharger la page</li>
                    <li>• Utilisez le mode saisie manuelle en alternative</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Extend camera overlay functionality
document.addEventListener('alpine:init', () => {
    Alpine.data('delivererQRScanner', (options) => ({
        ...Alpine.$data.delivererQRScanner?.(options) || {},

        // Camera overlay specific properties
        torchEnabled: false,
        hasMultipleCameras: false,
        currentCameraIndex: 0,
        scanQuality: 'medium',
        availableCameras: [],

        async initCameraOverlay() {
            await this.detectAvailableCameras();
        },

        async detectAvailableCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.availableCameras = devices.filter(device => device.kind === 'videoinput');
                this.hasMultipleCameras = this.availableCameras.length > 1;
            } catch (error) {
                console.error('Erreur détection caméras:', error);
                this.hasMultipleCameras = false;
            }
        },

        async toggleTorch() {
            if (!this.videoStream) return;

            try {
                const track = this.videoStream.getVideoTracks()[0];
                const capabilities = track.getCapabilities();

                if (capabilities.torch) {
                    await track.applyConstraints({
                        advanced: [{ torch: !this.torchEnabled }]
                    });
                    this.torchEnabled = !this.torchEnabled;
                }
            } catch (error) {
                console.error('Erreur contrôle flash:', error);
            }
        },

        async switchCamera() {
            if (!this.hasMultipleCameras) return;

            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;

            if (this.cameraActive) {
                await this.startCamera();
            }
        },

        adjustScanQuality() {
            if (this.cameraActive) {
                // Restart camera with new quality settings
                this.startCamera();
            }
        },

        getAspectRatio() {
            return '{{ $aspectRatio }}' || '1';
        },

        getStatusIndicatorClass() {
            if (this.cameraActive) {
                return 'bg-emerald-500 bg-opacity-90 text-white';
            } else {
                return 'bg-red-500 bg-opacity-90 text-white';
            }
        },

        getStatusText() {
            if (this.cameraActive) {
                return 'En ligne';
            } else {
                return 'Hors ligne';
            }
        },

        getScanModeText() {
            if (this.currentScanMode === 'barcode') {
                return 'Code-barres';
            } else if (this.currentScanMode === 'qr') {
                return 'QR Code';
            } else {
                return 'Scan Auto';
            }
        },

        getCurrentResolution() {
            if (!this.videoStream) return 'N/A';

            const track = this.videoStream.getVideoTracks()[0];
            const settings = track.getSettings();
            return `${settings.width}x${settings.height}`;
        },

        getCurrentFPS() {
            if (!this.videoStream) return 'N/A';

            const track = this.videoStream.getVideoTracks()[0];
            const settings = track.getSettings();
            return `${settings.frameRate || 30} fps`;
        },

        getQualityConstraints() {
            const constraints = {
                low: { width: 640, height: 480, frameRate: 15 },
                medium: { width: 1280, height: 720, frameRate: 30 },
                high: { width: 1920, height: 1080, frameRate: 30 }
            };

            return constraints[this.scanQuality] || constraints.medium;
        },

        // Override startCamera to include quality settings
        async startCamera() {
            this.cameraErrorMsg = '';

            try {
                this.stopCamera();

                const qualityConstraints = this.getQualityConstraints();
                const constraints = {
                    video: {
                        width: { min: 640, ideal: qualityConstraints.width },
                        height: { min: 480, ideal: qualityConstraints.height },
                        frameRate: { min: 15, ideal: qualityConstraints.frameRate }
                    }
                };

                // Use specific camera if multiple available
                if (this.hasMultipleCameras && this.availableCameras[this.currentCameraIndex]) {
                    constraints.video.deviceId = { exact: this.availableCameras[this.currentCameraIndex].deviceId };
                } else if (this.isMobile) {
                    constraints.video.facingMode = { exact: "environment" };
                }

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = this.$refs.videoElement;

                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }

                video.srcObject = this.videoStream;

                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => resolve();
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000);
                });

                this.cameraActive = true;
                this.startScanning();

            } catch (error) {
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
                this.stopCamera();
            }
        }
    }));
});
</script>
@endpush

<style>
@keyframes laser-scan {
    0% { top: 0; opacity: 1; }
    50% { opacity: 0.7; }
    100% { top: 100%; opacity: 1; }
}

.laser-line {
    position: absolute;
    left: 5%;
    right: 5%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10b981, transparent);
    box-shadow: 0 0 10px #10b981;
    animation: laser-scan 2.5s infinite alternate ease-in-out;
}

.camera-container {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
}

.scanning-overlay {
    background: radial-gradient(ellipse at center, transparent 30%, rgba(0,0,0,0.6) 70%);
}

.scan-area {
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .camera-container {
        border-radius: 1rem;
    }

    .laser-line {
        height: 1px;
    }
}
</style>