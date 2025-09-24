@props([
    'mode' => 'single', // single, batch
    'autoRedirect' => true,
    'showRecent' => true
])

<style>
    :root {
        --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        --color-primary: #3b82f6; --color-success: #10b981; --color-danger: #ef4444;
        --surface-background: #ffffff; --app-background: #f8fafc; --muted-background: #f1f5f9;
        --text-primary: #1e293b; --text-secondary: #64748b; --border-color: #e2e8f0;
        --border-radius-lg: 1.5rem; --border-radius-md: 1rem;
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }
    .scanner-container .bg-white {
        font-family: var(--font-sans);
        background-color: var(--surface-background);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 1.5rem;
    }
    .scanner-container .text-lg.font-bold { color: var(--text-primary); font-size: 1.25rem; }
    .scanner-container .text-sm.text-gray-600 { color: var(--text-secondary); }
    .scanner-container .flex.mb-4.bg-gray-100 { background-color: var(--muted-background); }
    .scanner-container .flex.mb-4.bg-gray-100 button { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .scanner-container .flex.mb-4.bg-gray-100 button.bg-white {
        color: var(--text-primary) !important;
        font-weight: 600;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }
    .scanner-container .flex.mb-4.bg-gray-100 button:not(.bg-white) {
        color: var(--text-secondary);
        background: transparent;
    }
    .scanner-container .flex.mb-4.bg-gray-100 button:hover {
        background-color: rgba(255, 255, 255, 0.8);
        transform: translateY(-1px);
    }

    .scanning-overlay {
        pointer-events: none;
        border: 2px solid var(--color-success);
        border-radius: 12px;
        position: relative;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.2), transparent);
        animation: pulse 2s infinite;
    }

    .scanning-overlay::before, .scanning-overlay::after {
        content: '';
        position: absolute;
        background: var(--color-success);
    }

    .scanning-overlay::before {
        top: 50%;
        left: 10%;
        right: 10%;
        height: 2px;
        animation: laser-scan 2.5s infinite alternate ease-in-out;
        box-shadow: 0 0 10px var(--color-success);
    }

    .scanning-overlay::after {
        top: 10%;
        bottom: 10%;
        left: 50%;
        width: 2px;
        animation: laser-scan 2.5s infinite alternate-reverse ease-in-out;
        box-shadow: 0 0 10px var(--color-success);
    }

    @keyframes laser-scan {
        0%, 100% { opacity: 0.3; transform: scale(0.8); }
        50% { opacity: 1; transform: scale(1.1); }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Toast Notifications */
    .result-modal-container .bg-white {
        max-height: 90vh;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--border-color) transparent;
    }

    .result-modal-container .bg-white::-webkit-scrollbar {
        width: 6px;
    }

    .result-modal-container .bg-white::-webkit-scrollbar-track {
        background: transparent;
    }

    .result-modal-container .bg-white::-webkit-scrollbar-thumb {
        background-color: var(--border-color);
        border-radius: 3px;
    }
</style>

<div x-data="delivererQRScanner({
        mode: '{{ $mode }}',
        autoRedirect: {{ $autoRedirect ? 'true' : 'false' }},
        showRecent: {{ $showRecent ? 'true' : 'false' }}
    })"
    @open-scanner.window="openScanner($event.detail)"
    @scan-package.window="openScanner($event.detail)">

    <!-- Scanner Modal -->
    <div x-show="scannerVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center scanner-container">

        <div class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto mx-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        <span x-show="scanMode === 'single'">Scanner le Colis</span>
                        <span x-show="scanMode === 'batch'">Scanner par Lot</span>
                    </h2>
                    <p class="text-sm text-gray-600">Caméra + Saisie manuelle</p>
                </div>
                <button @click="closeScanner()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Interface unique avec caméra en haut -->
            <div class="space-y-4">
                <!-- Camera Permission Request -->
                <div x-show="!permissionAsked && !cameraActive" class="text-center p-6 bg-blue-50 rounded-xl border border-blue-200">
                    <svg class="w-12 h-12 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="font-semibold text-blue-900 mb-2">Accès Caméra Requis</h3>
                    <p class="text-sm text-blue-700 mb-4">Autorisez l'accès à votre caméra pour scanner les codes-barres</p>
                    <button @click="startCamera()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Activer la Caméra
                    </button>
                </div>

                <!-- Camera View (En haut) -->
                <div x-show="permissionAsked" class="relative bg-black rounded-2xl overflow-hidden camera-container" :style="`aspect-ratio: ${getAspectRatio()}`">
                    <video x-ref="videoElement" class="w-full h-full object-cover" autoplay playsinline muted x-show="permissionAsked && !cameraErrorMsg"></video>
                    <canvas x-ref="canvasElement" style="display: none;"></canvas>

                    <!-- Scanning Overlay -->
                    <div x-show="cameraActive && !searching" class="absolute inset-4 scanning-overlay">
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-center">
                            <div class="bg-black bg-opacity-50 px-3 py-2 rounded-lg">
                                <p class="text-sm font-medium" x-text="scanType === 'qr' ? 'Scan QR Code...' : 'Scan Code-Barres...'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Searching Overlay -->
                    <div x-show="searching" class="absolute inset-0 bg-black bg-opacity-75 flex items-center justify-center text-white">
                        <div class="text-center">
                            <div class="animate-spin w-8 h-8 border-4 border-white border-t-transparent rounded-full mx-auto mb-2"></div>
                            <p class="text-sm">Recherche en cours...</p>
                        </div>
                    </div>

                    <div x-show="permissionAsked && !cameraActive && !cameraErrorMsg" class="text-white text-center">
                        <div class="animate-spin w-8 h-8 border-4 border-white border-t-transparent rounded-full mx-auto mb-2"></div>
                        <p class="text-sm">Démarrage de la caméra...</p>
                    </div>

                    <!-- Error Message -->
                    <div x-show="cameraErrorMsg" class="bg-red-500 text-white p-6 rounded-xl text-center max-w-xs mx-4">
                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="font-semibold mb-2">Erreur Caméra</p>
                        <p class="text-xs mb-4" x-text="cameraErrorMsg"></p>
                        <button @click="startCamera()" class="bg-white text-red-600 px-4 py-2 rounded-lg text-sm font-medium">
                            Réessayer
                        </button>
                    </div>

                    <!-- Scanning Overlay -->
                    <div x-show="cameraActive && !cameraErrorMsg" class="absolute inset-0 pointer-events-none scanning-overlay" style="box-shadow: rgba(0, 0, 0, 0.6) 0px 0px 0px 9999px inset;">
                        <div class="absolute inset-4 border-2 border-emerald-400 rounded-2xl"></div>
                    </div>

                    <!-- Status Indicator -->
                    <div class="absolute top-4 left-4 right-4">
                        <div :class="getStatusIndicatorClass()" class="flex items-center space-x-2 px-3 py-2 rounded-lg">
                            <div class="w-2 h-2 rounded-full animate-pulse" :class="cameraActive ? 'bg-emerald-400' : 'bg-red-400'"></div>
                            <span class="text-sm font-medium" x-text="getStatusText()"></span>
                            <div class="ml-auto flex items-center space-x-2">
                                <div x-show="batchScanMode" class="text-xs bg-blue-600 text-white px-2 py-1 rounded-full">
                                    <span x-text="totalScanned"></span> scannés
                                </div>
                                <span class="text-xs font-medium" x-text="getScanModeText()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Torch Button (Mobile) -->
                    <div x-show="cameraActive && isMobile" class="absolute bottom-4 right-4">
                        <button @click="toggleTorch()" :class="torchEnabled ? 'bg-yellow-500' : 'bg-black bg-opacity-50'" class="text-white p-3 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Camera Controls -->
                <div x-show="permissionAsked" class="flex justify-center space-x-3">
                    <button @click="startCamera()" x-show="!cameraActive && !cameraErrorMsg" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-emerald-700 transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Démarrer</span>
                    </button>

                    <button @click="stopCamera()" x-show="cameraActive" class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/>
                        </svg>
                        <span>Arrêter</span>
                    </button>

                    <button @click="switchCamera()" x-show="cameraActive && isMobile && hasMultipleCameras" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <span>Changer</span>
                    </button>
                </div>

                <!-- Camera Settings -->
                <div x-show="cameraActive" class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between text-sm">
                        <label class="font-medium text-gray-700">Qualité:</label>
                        <select x-model="scanQuality" @change="adjustScanQuality()" class="text-sm border-gray-300 rounded">
                            <option value="low">Basse</option>
                            <option value="medium">Moyenne</option>
                            <option value="high">Haute</option>
                        </select>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                        <span>Résolution: <span x-text="getCurrentResolution()"></span></span>
                        <span>FPS: <span x-text="getCurrentFPS()"></span></span>
                    </div>
                </div>

                <!-- Champ de saisie manuelle (En dessous) -->
                <div class="space-y-3">
                    <div class="relative">
                        <input type="text"
                               x-ref="manualInput"
                               x-model="currentCode"
                               @keydown.enter="validateCurrentPackage()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:border-blue-500 focus:ring-blue-200 text-lg font-mono uppercase transition-colors"
                               placeholder="Scanner, saisir le code ou coller l'URL de suivi..."
                               autofocus>

                    </div>

                    <div x-show="currentCode.trim().length > 0" class="text-sm text-blue-600">
                        Prêt à rechercher : <span x-text="currentCode.trim()"></span>
                    </div>

                    <!-- Aide pour les formats -->
                    <div x-show="currentCode.length >= 3" class="mt-2 text-xs text-gray-500 space-y-1">
                        <p class="font-medium">Le backend déterminera si le code est valide</p>
                        <div>• PKG_CLQVFCWP_20250921</div>
                        <div>• PKG_000038</div>
                        <div>• PKG_000007</div>
                        <div>• http://127.0.0.1:8000/track/PKG_HNIZCWH4_20250921</div>
                    </div>

                    <!-- Bouton de validation -->
                    <button @click="validateCurrentPackage()"
                            :disabled="!codeValid || searching"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <span x-show="!searching" class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Valider le Colis</span>
                        </span>
                        <span x-show="searching" class="flex items-center justify-center space-x-2">
                            <div class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></div>
                            <span>Validation...</span>
                        </span>
                    </button>
                </div>

                <!-- Camera Error -->
                <div x-show="cameraErrorMsg" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="text-center text-yellow-800">
                        <p class="font-medium">Problème avec la caméra</p>
                        <p class="text-sm mt-1">Utilisez la saisie manuelle ci-dessous</p>
                    </div>
                </div>
            </div>

                <!-- Liste des colis (pour le mode batch) -->
                <div x-show="scanMode === 'batch' && packageList.length > 0" class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-700">Colis Scannés
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2" x-text="totalPackages"></span>
                        </h4>
                        <div class="text-xs text-gray-600">
                            COD Total: <span class="font-semibold text-emerald-600" x-text="formatCurrency(totalCOD)"></span>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-48 overflow-y-auto mb-4">
                        <template x-for="(pkg, index) in packageList" :key="index">
                            <div class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-mono text-sm text-emerald-800" x-text="pkg.code"></div>
                                    <div class="text-xs text-gray-600" x-text="pkg.name || 'Destinataire non spécifié'"></div>
                                    <div class="text-xs text-emerald-600 font-semibold" x-show="pkg.cod_amount > 0" x-text="'COD: ' + pkg.formatted_cod"></div>
                                </div>
                                <button @click="removePackageFromList(index)" class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Bouton Terminer (pour le mode batch) -->
                    <button @click="showBatchSummary()"
                            class="w-full bg-emerald-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Terminer le Lot (<span x-text="totalPackages"></span> colis)</span>
                    </button>
                </div>

                <!-- Codes récents (pour mode single) -->
                <div x-show="scanMode === 'single' && recentCodes.length > 0" class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-700">Derniers codes scannés</h4>
                        <button @click="clearRecentCodes()" class="text-xs text-red-600 hover:text-red-700">Effacer</button>
                    </div>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        <template x-for="item in recentCodes.slice(0, 3)" :key="item.value">
                            <button @click="selectRecentCode(item.value)" class="w-full text-left p-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <div class="font-mono text-sm text-gray-700" x-text="item.value"></div>
                                <div class="text-xs text-gray-500" x-text="formatTime(item.timestamp)"></div>
                            </button>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div x-show="resultVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4 result-modal-container">

        <div class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4"
                     :class="result.success ? 'bg-emerald-100' : 'bg-red-100'">
                    <span class="text-3xl" x-text="result.success ? '✅' : '❌'"></span>
                </div>

                <h3 class="text-xl font-bold mb-2"
                    :class="result.success ? 'text-emerald-800' : 'text-red-800'"
                    x-text="result.success ? 'Colis Trouvé' : 'Erreur'"></h3>

                <p class="text-gray-700 mb-4 text-sm" x-text="result.message"></p>

                <!-- Package Details -->
                <div x-show="result.package" class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-4">
                    <div class="text-center border-b pb-4">
                        <p class="font-mono text-lg text-blue-600 font-bold" x-text="result.package?.code"></p>
                    </div>

                    <dl class="space-y-3">
                        <div x-show="result.delivery_info?.name">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Destinataire</dt>
                            <dd class="text-gray-900" x-text="result.delivery_info?.name"></dd>
                        </div>
                        <div x-show="result.delivery_info?.address">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Adresse</dt>
                            <dd class="text-gray-900" x-text="result.delivery_info?.address"></dd>
                        </div>
                        <div x-show="result.package?.cod_amount > 0">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Montant COD</dt>
                            <dd class="text-emerald-600 font-bold text-lg" x-text="result.package?.formatted_cod"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button @click="closeResult()" class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">
                        Fermer
                    </button>
                    <button x-show="result.success && result.redirect && autoRedirect"
                            @click="goToPackage()"
                            class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700"
                            x-text="getActionLabel()"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé du Lot Modal -->
    <div x-show="batchSummaryVisible" x-transition class="fixed inset-0 bg-black bg-opacity-75 z-60 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Résumé du Lot</h3>
                <button @click="closeBatchSummary()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-xl text-center">
                    <div class="text-3xl font-bold text-blue-600" x-text="totalPackages"></div>
                    <div class="text-sm text-blue-700">Nombre de Colis</div>
                </div>
                <div class="bg-emerald-50 p-4 rounded-xl text-center">
                    <div class="text-3xl font-bold text-emerald-600" x-text="formatCurrency(totalCOD)"></div>
                    <div class="text-sm text-emerald-700">COD Total</div>
                </div>
            </div>

            <!-- Liste des colis -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Détail des Colis</h4>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <template x-for="(pkg, index) in packageList" :key="index">
                        <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="font-mono text-lg font-semibold text-blue-600" x-text="pkg.code"></div>
                                    <div class="text-gray-700 mt-1" x-text="pkg.name || 'Destinataire non spécifié'"></div>
                                    <div class="text-sm text-gray-600 mt-1" x-text="pkg.address || 'Adresse non spécifiée'"></div>
                                    <div class="mt-2">
                                        <span class="text-sm text-emerald-600 font-semibold" x-show="pkg.cod_amount > 0" x-text="'COD: ' + pkg.formatted_cod"></span>
                                        <span class="text-sm text-gray-500" x-show="!pkg.cod_amount || pkg.cod_amount === 0">Aucun COD</span>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    #<span x-text="index + 1"></span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="packageList.length === 0" class="text-center py-8 text-gray-500">
                        Aucun colis dans le lot
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex space-x-3">
                <button @click="closeBatchSummary()" class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">
                    Continuer le Scan
                </button>
                <button @click="submitBatch()" class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700">
                    Valider le Lot (<span x-text="totalPackages"></span> colis)
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="fixed bottom-20 sm:bottom-4 right-4 z-50 space-y-2">
        <!-- Toasts will be dynamically inserted here -->
    </div>
</div>

<!--
SCRIPT DÉPLACÉ DANS LAYOUT PRINCIPAL
Le script delivererQRScanner a été déplacé dans layouts/deliverer.blade.php
pour s'assurer qu'il se charge avant Alpine.js
-->