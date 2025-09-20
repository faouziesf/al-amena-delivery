<!-- Scanner QR FINAL - VERSION MOBILE AMÉLIORÉE -->
<div x-data="scannerQRFinal()" @open-scanner.window="openScanner()">
    
    <!-- Modal Scanner -->
    <div x-show="scannerVisible" x-transition class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center">
        <div class="bg-white rounded-3xl p-4 m-4 w-full max-w-md max-h-screen overflow-y-auto">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Scanner QR/Code</h3>
                    <p class="text-sm text-gray-600" x-text="activeMode === 'camera' ? 'Mode caméra' : 'Saisie manuelle'"></p>
                </div>
                <button @click="closeScanner()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- HTTPS Warning pour mobile -->
            <div x-show="!isHttps && isMobile" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Caméra nécessite HTTPS</p>
                        <p class="text-xs text-amber-700 mt-1">Sur mobile, utilisez le mode manuel ou accédez via HTTPS.</p>
                    </div>
                </div>
            </div>
            
            <!-- Mode Selection -->
            <div class="flex mb-4 bg-gray-100 rounded-xl p-1">
                <button @click="switchMode('camera')" 
                        :class="activeMode === 'camera' ? 'bg-white shadow-sm text-emerald-600' : 'text-gray-600'"
                        :disabled="!isHttps && isMobile"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all disabled:opacity-50">
                    📷 Caméra
                </button>
                <button @click="switchMode('manual')" 
                        :class="activeMode === 'manual' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-all">
                    ✏️ Manuel
                </button>
            </div>

            <!-- Camera Mode -->
            <div x-show="activeMode === 'camera'" class="space-y-4">
                
                <!-- Permission Request -->
                <div x-show="!permissionAsked && !cameraActive" class="text-center p-6 bg-blue-50 rounded-xl">
                    <svg class="w-16 h-16 text-blue-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Autorisation Caméra</h4>
                    <p class="text-sm text-blue-700 mb-4">Autorisez l'accès à la caméra pour scanner les codes QR automatiquement.</p>
                    <button @click="requestCameraPermission()" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700">
                        Autoriser la Caméra
                    </button>
                </div>

                <!-- Video Container -->
                <div x-show="permissionAsked" class="relative bg-black rounded-2xl overflow-hidden" style="aspect-ratio: 1;">
                    <video x-ref="videoElement" 
                           class="w-full h-full object-cover" 
                           autoplay playsinline muted
                           x-show="cameraActive && !cameraErrorMsg"></video>
                    
                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                    
                    <!-- Status Messages -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div x-show="permissionAsked && !cameraActive && !cameraErrorMsg" class="text-white text-center">
                            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <p>Démarrage caméra...</p>
                        </div>
                        
                        <div x-show="cameraErrorMsg" class="bg-red-500 text-white p-4 rounded-xl text-center max-w-xs mx-4">
                            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm mb-3 font-medium">Erreur Caméra</p>
                            <p class="text-xs mb-3" x-text="cameraErrorMsg"></p>
                            <div class="space-y-2">
                                <button @click="retryCamera()" 
                                        class="w-full bg-white text-red-500 px-3 py-2 rounded text-sm font-medium">
                                    Réessayer
                                </button>
                                <button @click="switchMode('manual')" 
                                        class="w-full bg-red-400 text-white px-3 py-2 rounded text-sm">
                                    Mode Manuel
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scan Frame avec Zones Spécialisées -->
                    <div x-show="cameraActive && !cameraErrorMsg" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="relative w-80 h-80">
                            <!-- Cadre principal -->
                            <div class="w-full h-full border-4 border-emerald-500 rounded-2xl relative">
                                <!-- Coins du cadre -->
                                <div class="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                                <div class="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                                <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                                <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                                
                                <!-- Zone QR (bas-droite) avec indicateur actif -->
                                <div class="absolute bottom-8 right-8 w-20 h-20 border-2 rounded-lg transition-all duration-300"
                                     :class="scanMode === 'qr' ? 'border-blue-500 bg-blue-500 bg-opacity-20 shadow-lg' : 'border-blue-300 bg-blue-300 bg-opacity-10'">
                                    <div class="absolute top-1 left-1 text-xs font-bold px-1 rounded transition-colors"
                                         :class="scanMode === 'qr' ? 'text-blue-700 bg-white' : 'text-blue-500 bg-blue-100'">
                                        📱 QR
                                    </div>
                                    <!-- Grille QR animée -->
                                    <div class="absolute inset-2 grid grid-cols-4 gap-0.5">
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div></div>
                                        <div></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div></div>
                                        <div></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                        <div :class="scanMode === 'qr' ? 'bg-blue-500 opacity-50' : 'bg-blue-400 opacity-20'"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indicateur de mode actif en bas -->
                            <div class="absolute -bottom-8 left-0 right-0 text-center">
                                <div class="bg-black bg-opacity-70 text-white px-3 py-1 rounded-full text-xs">
                                    <span x-show="scanMode === 'barcode'">📊 Scan Codes-barres...</span>
                                    <span x-show="scanMode === 'qr'">📱 Scan QR Codes...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scan Status avec Alternance -->
                    <div x-show="cameraActive && !cameraErrorMsg" class="absolute bottom-4 left-0 right-0 text-center">
                        <div class="bg-black bg-opacity-60 text-white px-4 py-2 rounded-full inline-block">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full animate-pulse" 
                                     :class="scanMode === 'barcode' ? 'bg-red-400' : 'bg-blue-400'"></div>
                                <span class="text-sm">
                                    <span x-show="scanMode === 'barcode'">📊 Scan codes-barres...</span>
                                    <span x-show="scanMode === 'qr'">📱 Scan QR (zone bleue)...</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Camera Controls -->
                <div x-show="permissionAsked" class="flex justify-center space-x-3">
                    <button @click="startCamera()" x-show="!cameraActive && !cameraErrorMsg"
                            class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-emerald-700">
                        Démarrer
                    </button>
                    
                    <button @click="stopCamera()" x-show="cameraActive"
                            class="bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700">
                        Arrêter
                    </button>
                    
                    <button @click="captureFrame()" x-show="cameraActive"
                            class="bg-purple-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-purple-700">
                        📸 Capturer
                    </button>
                </div>

                <!-- Instructions avec alternance -->
                <div x-show="isMobile && cameraActive" class="bg-blue-50 rounded-xl p-3">
                    <p class="text-sm text-blue-800 text-center">
                        💡 <strong>Scan en alternance :</strong> 
                        <span x-show="scanMode === 'barcode'">Pointez vers les codes-barres (scan global)</span>
                        <span x-show="scanMode === 'qr'">Placez QR codes dans la zone bleue</span>
                        - Change automatiquement !
                    </p>
                </div>
            </div>

            <!-- Manual Mode -->
            <div x-show="activeMode === 'manual'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
                    <input type="text" x-ref="manualInput" x-model="manualCode" 
                           @keydown.enter="searchCode()" @input="validateCode()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-lg font-mono uppercase"
                           placeholder="PKG_12345678_20251219" autofocus>
                    
                    <div class="mt-2 text-sm">
                        <div x-show="codeValid" class="text-emerald-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Format valide
                        </div>
                        <div x-show="manualCode && !codeValid" class="text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Format invalide
                        </div>
                    </div>
                </div>
                
                <!-- Recent Codes -->
                <div x-show="recentCodes.length > 0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Codes récents</label>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        <template x-for="code in recentCodes.slice(0, 5)" :key="code.value">
                            <button @click="useRecentCode(code.value)" 
                                    class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-sm" x-text="code.value"></span>
                                    <span class="text-xs text-gray-500" x-text="formatTime(code.timestamp)"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                
                <button @click="searchCode()" :disabled="!codeValid || searching"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!searching">🔍 Rechercher</span>
                    <span x-show="searching">⏳ Recherche...</span>
                </button>
            </div>

            <!-- Scan History -->
            <div x-show="scanHistory.length > 0" class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Derniers scans</h4>
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="(scan, index) in scanHistory.slice(0, 3)" :key="index">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium" x-text="scan.code"></p>
                                <p class="text-xs text-gray-500" x-text="scan.result"></p>
                            </div>
                            <span :class="scan.success ? 'text-emerald-500' : 'text-red-500'">
                                <span x-text="scan.success ? '✅' : '❌'"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div x-show="resultVisible" x-transition 
         class="fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center p-4">
        <div x-show="resultVisible" x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
             class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            
            <div class="text-center">
                <!-- Icon & Status -->
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full mb-4"
                     :class="result.success ? 'bg-emerald-100' : 'bg-red-100'">
                    <span class="text-2xl" x-text="result.success ? '✅' : '❌'"></span>
                </div>
                
                <!-- Title & Message -->
                <h3 class="text-lg font-bold mb-2" :class="result.success ? 'text-emerald-800' : 'text-red-800'" 
                    x-text="result.success ? 'Colis Détecté!' : 'Erreur'"></h3>
                
                <p class="text-gray-700 mb-4 text-sm leading-relaxed" x-text="result.message"></p>
                
                <!-- Instructions -->
                <div x-show="result.instructions" class="bg-blue-50 rounded-xl p-3 mb-4">
                    <p class="text-sm text-blue-800 font-medium" x-text="result.instructions"></p>
                </div>

                <!-- Warning COD -->
                <div x-show="result.cod_warning" class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4">
                    <p class="text-sm text-amber-800 font-bold" x-text="result.cod_warning"></p>
                </div>

                <!-- Urgent Badge -->
                <div x-show="result.is_urgent" class="bg-red-100 border border-red-200 rounded-xl p-2 mb-4">
                    <span class="text-red-700 font-bold text-sm">🚨 LIVRAISON URGENTE - 3ème tentative</span>
                </div>
                
                <!-- Package Info Card -->
                <div x-show="result.package" class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                    <!-- Package Header -->
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                        <span class="font-mono text-sm text-blue-600 font-bold" x-text="result.package?.code"></span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium" 
                              x-text="result.package?.status_label"></span>
                    </div>
                    
                    <!-- COD Amount (if relevant) -->
                    <div x-show="result.package?.cod_amount > 0" class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg p-3 mb-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">💰 Montant COD:</span>
                            <span class="font-bold text-lg text-emerald-600" x-text="result.package?.formatted_cod"></span>
                        </div>
                    </div>

                    <!-- Pickup Info (for ACCEPTED status) -->
                    <div x-show="result.pickup_info" class="mb-3">
                        <h4 class="font-semibold text-amber-700 text-sm mb-2">📦 Informations Collecte</h4>
                        <div class="bg-amber-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Nom:</strong> <span x-text="result.pickup_info?.name"></span></div>
                            <div><strong>Tél:</strong> <span x-text="result.pickup_info?.phone"></span></div>
                            <div><strong>Lieu:</strong> <span x-text="result.pickup_info?.delegation"></span></div>
                            <div x-show="result.pickup_info?.notes"><strong>Notes:</strong> <span x-text="result.pickup_info?.notes"></span></div>
                        </div>
                    </div>

                    <!-- Delivery Info (for PICKED_UP/UNAVAILABLE status) -->
                    <div x-show="result.delivery_info" class="mb-3">
                        <h4 class="font-semibold text-green-700 text-sm mb-2">🎯 Informations Livraison</h4>
                        <div class="bg-green-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Destinataire:</strong> <span x-text="result.delivery_info?.name"></span></div>
                            <div><strong>Tél:</strong> <span x-text="result.delivery_info?.phone"></span></div>
                            <div><strong>Adresse:</strong> <span x-text="result.delivery_info?.address"></span></div>
                            <div><strong>Délégation:</strong> <span x-text="result.delivery_info?.delegation"></span></div>
                            <div x-show="result.delivery_info?.attempts > 0">
                                <strong>Tentatives:</strong> <span x-text="result.delivery_info?.attempts"></span>/3
                            </div>
                            <div x-show="result.delivery_info?.special_instructions" class="pt-1 border-t border-green-200">
                                <strong>Instructions:</strong> <span x-text="result.delivery_info?.special_instructions"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Return Info (for VERIFIED status) -->
                    <div x-show="result.return_info" class="mb-3">
                        <h4 class="font-semibold text-red-700 text-sm mb-2">↩️ Informations Retour</h4>
                        <div class="bg-red-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Retourner à:</strong> <span x-text="result.return_info?.name"></span></div>
                            <div><strong>Tél:</strong> <span x-text="result.return_info?.phone"></span></div>
                            <div><strong>Lieu:</strong> <span x-text="result.return_info?.delegation"></span></div>
                            <div><strong>Tentatives échouées:</strong> <span x-text="result.return_info?.attempts_made"></span></div>
                        </div>
                    </div>

                    <!-- Previous Attempt (for UNAVAILABLE) -->
                    <div x-show="result.previous_attempt" class="mb-3">
                        <h4 class="font-semibold text-orange-700 text-sm mb-2">🔄 Tentative Précédente</h4>
                        <div class="bg-orange-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Tentative #:</strong> <span x-text="result.previous_attempt?.attempt_number"></span></div>
                            <div><strong>Raison:</strong> <span x-text="result.previous_attempt?.reason"></span></div>
                            <div x-show="result.previous_attempt?.notes"><strong>Notes:</strong> <span x-text="result.previous_attempt?.notes"></span></div>
                            <div><strong>Date:</strong> <span x-text="result.previous_attempt?.last_attempt"></span></div>
                        </div>
                    </div>

                    <!-- Delivery/Return Details (for completed statuses) -->
                    <div x-show="result.delivery_details" class="mb-3">
                        <h4 class="font-semibold text-green-700 text-sm mb-2">✅ Détails Livraison</h4>
                        <div class="bg-green-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Livré le:</strong> <span x-text="result.delivery_details?.delivered_at"></span></div>
                            <div><strong>COD encaissé:</strong> <span x-text="result.delivery_details?.cod_amount"></span></div>
                        </div>
                    </div>

                    <div x-show="result.return_details" class="mb-3">
                        <h4 class="font-semibold text-red-700 text-sm mb-2">↩️ Détails Retour</h4>
                        <div class="bg-red-50 rounded-lg p-2 text-xs space-y-1">
                            <div><strong>Retourné le:</strong> <span x-text="result.return_details?.returned_at"></span></div>
                            <div x-show="result.return_details?.return_reason"><strong>Raison:</strong> <span x-text="result.return_details?.return_reason"></span></div>
                        </div>
                    </div>

                    <!-- Basic Package Info -->
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Contenu:</span>
                            <span class="font-medium text-gray-800" x-text="result.package?.content_description || 'N/A'"></span>
                        </div>
                        <div x-show="result.package?.package_weight" class="flex justify-between">
                            <span>Poids:</span>
                            <span class="font-medium text-gray-800" x-text="result.package?.package_weight + 'kg'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>De:</span>
                            <span class="font-medium text-gray-800" x-text="result.package?.delegation_from"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Vers:</span>
                            <span class="font-medium text-gray-800" x-text="result.package?.delegation_to"></span>
                        </div>
                    </div>

                    <!-- Special Attributes -->
                    <div x-show="result.package?.is_fragile || result.package?.requires_signature" 
                         class="mt-3 pt-2 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <span x-show="result.package?.is_fragile" 
                                  class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                🔴 Fragile
                            </span>
                            <span x-show="result.package?.requires_signature" 
                                  class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                ✍️ Signature
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Assigned to other deliverer -->
                <div x-show="result.assigned_to" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <h4 class="font-semibold text-amber-800 text-sm mb-1">🔒 Colis Assigné</h4>
                    <p class="text-amber-700 text-sm">
                        Ce colis est assigné à: <strong x-text="result.assigned_to"></strong>
                    </p>
                </div>
                
                <!-- Actions -->
                <div class="flex space-x-3">
                    <button @click="closeResult()" 
                            class="flex-1 py-3 px-4 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300">
                        Fermer
                    </button>
                    <button x-show="result.success && result.redirect" @click="goToPackage()" 
                            class="flex-1 py-3 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700"
                            x-text="getActionLabel()">
                    </button>
                </div>

                <!-- Auto redirect countdown -->
                <div x-show="result.success && result.redirect" class="mt-3">
                    <p class="text-xs text-gray-500">Redirection automatique dans 5 secondes...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scannerQRFinal() {
    return {
        // État principal
        scannerVisible: false,
        activeMode: 'manual', // Démarrer en mode manuel
        
        // Détection environnement
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        isHttps: location.protocol === 'https:',
        
        // Caméra et scan avec alternance
        cameraActive: false,
        cameraErrorMsg: '',
        videoStream: null,
        scanInterval: null,
        lastDetection: null,
        permissionAsked: false,
        
        // Alternance des modes de scan
        scanMode: 'barcode', // 'barcode' ou 'qr'
        scanCycle: 0,
        
        // Manuel
        manualCode: '',
        codeValid: false,
        searching: false,
        recentCodes: [],
        
        // Résultats
        result: {},
        resultVisible: false,
        scanHistory: [],

        init() {
            console.log('Init scanner - Mobile:', this.isMobile, 'HTTPS:', this.isHttps);
            
            // Charger données localStorage
            this.loadStoredData();
            
            // Mode par défaut selon l'environnement
            if (this.isMobile && !this.isHttps) {
                this.activeMode = 'manual';
            }
            
            // Surveiller les changements de code
            this.$watch('manualCode', () => this.validateCode());
        },

        // ==================== ACTIONS PRINCIPALES ====================
        
        openScanner() {
            console.log('Ouverture scanner...');
            this.scannerVisible = true;
            this.resetScanner();
            
            if (this.activeMode === 'manual') {
                setTimeout(() => {
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                }, 100);
            }
        },

        closeScanner() {
            console.log('Fermeture scanner...');
            this.stopCamera();
            this.scannerVisible = false;
            this.resetScanner();
        },

        resetScanner() {
            this.manualCode = '';
            this.codeValid = false;
            this.searching = false;
            this.cameraErrorMsg = '';
            this.resultVisible = false;
            this.permissionAsked = false;
        },

        switchMode(mode) {
            console.log('Changement mode:', mode);
            
            // Vérifier HTTPS sur mobile pour caméra
            if (mode === 'camera' && this.isMobile && !this.isHttps) {
                this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile';
                return;
            }
            
            this.activeMode = mode;
            
            if (mode === 'manual') {
                this.stopCamera();
                setTimeout(() => {
                    if (this.$refs.manualInput) {
                        this.$refs.manualInput.focus();
                    }
                }, 100);
            }
        },

        // ==================== CAMÉRA AVEC GESTION PERMISSIONS ====================
        
        async requestCameraPermission() {
            console.log('Demande permission caméra...');
            this.permissionAsked = true;
            this.cameraErrorMsg = '';
            
            // Vérifier HTTPS d'abord
            if (this.isMobile && !this.isHttps) {
                this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile. Utilisez le mode manuel.';
                return;
            }
            
            try {
                // Vérifier support getUserMedia
                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error('getUserMedia non supporté par ce navigateur');
                }

                // Démarrer caméra
                await this.startCamera();
                
            } catch (error) {
                console.error('Erreur permission caméra:', error);
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
            }
        },
        
        async startCamera() {
            console.log('Démarrage caméra...');
            this.cameraErrorMsg = '';
            
            try {
                this.stopCamera(); // Nettoyer avant de commencer

                // Contraintes optimisées pour mobile
                const constraints = {
                    video: {
                        width: { min: 640, ideal: this.isMobile ? 1280 : 1920 },
                        height: { min: 480, ideal: this.isMobile ? 720 : 1080 },
                        frameRate: { min: 15, ideal: 30 }
                    }
                };

                // Caméra arrière sur mobile
                if (this.isMobile) {
                    constraints.video.facingMode = { exact: "environment" };
                }

                console.log('Contraintes caméra:', constraints);

                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                const video = this.$refs.videoElement;
                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }
                
                video.srcObject = this.videoStream;

                // Attendre que la vidéo soit prête
                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => {
                        console.log('Vidéo prête - Résolution:', video.videoWidth, 'x', video.videoHeight);
                        resolve();
                    };
                    video.onerror = reject;
                    
                    // Timeout de sécurité
                    setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000);
                });

                this.cameraActive = true;
                this.startScanning();
                console.log('Caméra démarrée avec succès');

            } catch (error) {
                console.error('Erreur démarrage caméra:', error);
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
                this.stopCamera();
            }
        },

        stopCamera() {
            this.stopScanning();
            
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => {
                    console.log('Arrêt track caméra:', track.kind);
                    track.stop();
                });
                this.videoStream = null;
            }
            
            if (this.$refs.videoElement) {
                this.$refs.videoElement.srcObject = null;
            }
            
            this.cameraActive = false;
        },

        retryCamera() {
            console.log('Nouvelle tentative caméra...');
            this.cameraErrorMsg = '';
            this.requestCameraPermission();
        },

        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();
            console.log('Erreur caméra détaillée:', msg, error);
            
            if (msg.includes('Permission denied') || msg.includes('NotAllowedError') || error.name === 'NotAllowedError') {
                return 'Permission refusée. Autorisez l\'accès caméra dans les paramètres de votre navigateur.';
            }
            if (msg.includes('NotFoundError') || error.name === 'NotFoundError') {
                return 'Aucune caméra trouvée sur cet appareil.';
            }
            if (msg.includes('NotReadableError') || error.name === 'NotReadableError') {
                return 'Caméra occupée par une autre application. Fermez les autres apps utilisant la caméra.';
            }
            if (msg.includes('OverconstrainedError') || error.name === 'OverconstrainedError') {
                return 'Contraintes caméra non supportées. Essayez de redémarrer l\'application.';
            }
            if (msg.includes('SecurityError') || error.name === 'SecurityError') {
                return 'Erreur de sécurité. HTTPS requis pour la caméra.';
            }
            if (msg.includes('AbortError') || error.name === 'AbortError') {
                return 'Accès caméra interrompu. Réessayez.';
            }
            if (msg.includes('getUserMedia non supporté')) {
                return 'Votre navigateur ne supporte pas la caméra. Utilisez le mode manuel.';
            }
            
            return 'Erreur caméra. Vérifiez les permissions et réessayez ou utilisez le mode manuel.';
        },

        startScanning() {
            console.log('Démarrage scan en alternance...');
            this.initQuaggaScanner(); // Initialiser Quagga pour codes-barres
            this.startAlternatingScans(); // Démarrer l'alternance
        },

        startAlternatingScans() {
            // Alternance toutes les 750ms : un cycle codes-barres, un cycle QR
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                
                // Alterner le mode : paire = codes-barres, impaire = QR
                if (this.scanCycle % 2 === 0) {
                    this.scanMode = 'barcode';
                    this.scanBarcodes();
                } else {
                    this.scanMode = 'qr';
                    this.scanQRCodes();
                }
                
                console.log(`🔄 Cycle ${this.scanCycle} - Mode: ${this.scanMode}`);
                
            }, 750); // 750ms pour laisser le temps à chaque méthode
        },

        scanBarcodes() {
            // Les codes-barres sont gérés automatiquement par Quagga
            // On ne fait rien ici, Quagga tourne en arrière-plan
            console.log('📊 Cycle codes-barres actif (Quagga en arrière-plan)');
        },

        scanQRCodes() {
            // Scanner QR codes uniquement pendant ce cycle
            console.log('📱 Cycle QR codes actif');
            this.analyzeQRFrame();
        },

        stopScanning() {
            console.log('Arrêt scan en alternance...');
            
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
            
            this.stopQuaggaScanner();
            this.scanCycle = 0;
            this.scanMode = 'barcode';
        },

        // ==================== QUAGGA SCANNER (CODES-BARRES 1D) ====================
        
        initQuaggaScanner() {
            if (typeof Quagga === 'undefined') {
                console.warn('QuaggaJS non disponible');
                return;
            }

            try {
                const video = this.$refs.videoElement;
                if (!video) return;

                // Configuration simple pour codes-barres (sans zones restrictives)
                Quagga.init({
                    inputStream: {
                        type: "LiveStream",
                        target: video,
                        constraints: {
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                            facingMode: this.isMobile ? "environment" : "user"
                        }
                        // PAS de zone area - scan complet pour codes-barres
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "code_39_reader"]
                    },
                    locate: true,
                    debug: false
                }, (err) => {
                    if (err) {
                        console.error('Erreur Quagga:', err);
                        return;
                    }
                    console.log('📊 Quagga initialisé pour codes-barres (sans zones)');
                    Quagga.start();
                });

                // Écouter détections codes-barres - seulement pendant cycles appropriés
                Quagga.onDetected((result) => {
                    if (result?.codeResult?.code && this.scanMode === 'barcode') {
                        const code = result.codeResult.code.trim();
                        console.log('📊 Code-barres détecté pendant cycle barcode:', code);
                        
                        if (this.isValidPackageCode(code)) {
                            console.log('📊 Code-barres validé:', code);
                            this.onCodeDetected(code, 'BARCODE');
                        }
                    }
                });

            } catch (error) {
                console.error('Erreur QuaggaJS:', error);
            }
        },

        stopQuaggaScanner() {
            try {
                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                    console.log('📊 Quagga arrêté');
                }
            } catch (error) {
                console.error('Erreur arrêt Quagga:', error);
            }
        },

        // ==================== QR CODE SCANNER ====================
        
        startQRScanning() {
            this.qrScanInterval = setInterval(() => {
                this.analyzeQRFrame();
            }, 300); // Scan plus rapide pour temps réel
        },

        stopQRScanning() {
            if (this.qrScanInterval) {
                clearInterval(this.qrScanInterval);
                this.qrScanInterval = null;
                console.log('QR scan arrêté');
            }
        },

        analyzeQRFrame() {
            try {
                const video = this.$refs.videoElement;
                const canvas = this.$refs.canvasElement;
                
                if (!video || !canvas || !video.videoWidth || !video.videoHeight) {
                    return;
                }

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);

                // Scan QR codes avec jsQR
                if (typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert"
                    });

                    if (qrResult?.data) {
                        const code = qrResult.data.trim();
                        console.log('📱 QR Code détecté:', code);
                        
                        if (this.isValidCode(code)) {
                            this.onCodeDetected(code, 'QR');
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur analyse QR:', error);
            }
        },

        captureFrame() {
            console.log('📸 Capture manuelle...');
            this.analyzeQRFrame();
        },

        // ==================== TRAITEMENT UNIFIÉ ====================
        
        onCodeDetected(code, type) {
            // Éviter les doublons de détection rapprochés (réduit à 2 secondes)
            const now = Date.now();
            if (this.lastDetection && (now - this.lastDetection.time < 2000) && this.lastDetection.code === code) {
                console.log('⏭️ Détection ignorée (doublon récent)', code);
                return;
            }
            
            this.lastDetection = { code, time: now, type };
            
            console.log(`🎉 Code ${type} DÉTECTÉ ET VALIDÉ:`, code);
            console.log('📊 Arrêt du scanner et traitement...');
            
            // Arrêter scan et traiter
            this.stopCamera();
            this.showDetectionFeedback(code, type);
            this.processCode(code);
        },

        isValidPackageCode(code) {
            if (!code || code.length < 6) {
                console.log('❌ Code trop court:', code);
                return false;
            }
            
            // Nettoyer le code
            const cleanCode = code.trim().toUpperCase();
            
            // 1. URL de tracking (QR codes du bon de livraison)
            if (/^https?:\/\/.*\/track\//.test(cleanCode)) {
                console.log('✅ URL de tracking valide');
                return true;
            }
            
            // 2. Validation simplifiée - accepter plus de formats
            const validPatterns = [
                /^PKG_[A-Z0-9]{6,}_\d{6,}$/,     // PKG_VRQFAGFY_20250918 (format complet)
                /^[A-Z0-9]{6,20}$/,              // Codes alphanumériques (6-20 chars)
                /^\d{6,}$/,                      // Codes numériques purs (6+ chiffres)
                /^[A-Z0-9_]{6,}$/,              // Codes avec underscores (min 6 chars)
                /^[A-Z]{4,}[0-9]{4,}$/,         // Format mixte lettres+chiffres
                /^[A-Z0-9]{8}_\d{8}$/           // Format XXXXXXXX_YYYYMMDD
            ];
            
            // Rejeter seulement les mots français très évidents
            if (this.isObviousText(cleanCode)) {
                console.log('❌ Texte évident rejeté:', cleanCode);
                return false;
            }
            
            const isValid = validPatterns.some(pattern => pattern.test(cleanCode));
            
            if (isValid) {
                console.log('✅ Code valide selon pattern:', cleanCode);
            } else {
                console.log('⚠️ Code format non reconnu (mais autorisé):', cleanCode);
                // Autoriser quand même si ce n'est pas du texte évident
                return cleanCode.length >= 6 && !this.isObviousText(cleanCode);
            }
            
            return isValid;
        },

        isObviousText(code) {
            // Rejeter seulement les mots français/anglais très évidents (raccourcie la liste)
            const obviousWords = [
                'LIVRAISON', 'DELIVERY', 'BON', 'ALAMENA', 'SERVICE',
                'CONTACT', 'TELEPHONE', 'ADRESSE', 'CLIENT', 'DATE'
            ];
            
            const upperCode = code.toUpperCase();
            return obviousWords.some(word => upperCode === word || upperCode.includes(word) && word.length > 4);
        },

        // Fonction de compatibilité simplifiée
        looksLikeText(code) {
            return this.isObviousText(code);
        },

        isValidCode(code) {
            // Fonction de compatibilité - utilise la validation stricte
            return this.isValidPackageCode(code);
        },

        showDetectionFeedback(code, type) {
            // Feedback visuel rapide
            const icon = type === 'QR' ? '📱' : '📊';
            console.log(`${icon} DÉTECTION ${type}:`, code);
            
            // Vibration mobile si disponible
            if (navigator.vibrate && this.isMobile) {
                navigator.vibrate([100, 50, 100]);
            }
            
            // Son de confirmation si possible
            this.playDetectionSound();
        },

        playDetectionSound() {
            try {
                // Son de bip simple
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (error) {
                // Ignore si pas de support audio
            }
        },

        // ==================== MODE MANUEL ====================
        
        validateCode() {
            const code = this.manualCode.trim().toUpperCase();
            this.codeValid = this.isValidPackageCode(code);
            
            // Debug pour mode manuel
            if (this.codeValid) {
                const format = this.detectCodeFormat(code);
                console.log(`✅ Code manuel VALIDE - Format: ${format}, Code: ${code}`);
            } else if (code.length > 0) {
                console.log(`❌ Code manuel INVALIDE: ${code}, Longueur: ${code.length}`);
                if (this.isObviousText(code)) {
                    console.log('💡 Conseil: Ce code ressemble à du texte, utilisez le code/QR du colis');
                } else {
                    console.log('💡 Conseil: Vérifiez le format du code (min 6 caractères)');
                }
            }
        },

        detectCodeFormat(code) {
            // URL de tracking (QR code)
            if (/^https?:\/\/.*\/track\//.test(code)) {
                return 'QR_TRACKING_URL';
            }
            
            // Package code complet
            if (/^PKG_[A-Z0-9]{8,}_\d{8}$/.test(code)) {
                return 'FULL_PACKAGE_CODE';
            }
            
            // Code court (code-barres)
            if (/^[A-Z0-9]{8,16}$/.test(code)) {
                return 'SHORT_BARCODE';
            }
            
            // Code numérique pur
            if (/^\d{8,}$/.test(code)) {
                return 'NUMERIC_CODE';
            }
            
            return 'UNKNOWN_FORMAT';
        },

        searchCode() {
            if (!this.codeValid || this.searching) return;
            
            const code = this.manualCode.trim().toUpperCase();
            this.processCode(code);
        },

        useRecentCode(code) {
            this.manualCode = code;
            this.validateCode();
            this.processCode(code);
        },

        // ==================== TRAITEMENT ====================
        
        async processCode(code) {
            console.log('🔍 DÉBUT TRAITEMENT CODE:', code);
            this.searching = true;
            
            try {
                this.addToRecent(code);

                console.log('📡 Envoi requête au serveur...');
                const response = await fetch('/deliverer/packages/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });

                console.log('📥 Réponse serveur reçue:', response.status);
                const data = await response.json();
                console.log('📋 Données reçues:', data);
                
                this.addToHistory(code, data.message, data.success);
                this.showResult(data);

            } catch (error) {
                console.error('❌ ERREUR RÉSEAU:', error);
                this.addToHistory(code, 'Erreur de connexion', false);
                this.showResult({
                    success: false,
                    message: 'Erreur de connexion. Vérifiez votre réseau.'
                });
            }
            
            this.searching = false;
            console.log('✅ FIN TRAITEMENT CODE');
        },

        showResult(data) {
            this.result = data;
            this.resultVisible = true;
            
            // Auto-redirection si succès (avec délai plus long pour lire les infos)
            if (data.success && data.redirect) {
                setTimeout(() => {
                    this.goToPackage();
                }, 5000); // 5 secondes pour lire les détails
            }
        },

        closeResult() {
            this.resultVisible = false;
        },

        goToPackage() {
            if (this.result.redirect) {
                this.closeScanner();
                window.location.href = this.result.redirect;
            }
        },

        getActionLabel() {
            const action = this.result.action;
            switch (action) {
                case 'accept': return 'Accepter';
                case 'pickup': return 'Collecter';
                case 'deliver': return 'Livrer';
                case 'return': return 'Retourner';
                default: return 'Voir';
            }
        },

        // ==================== STOCKAGE ====================
        
        loadStoredData() {
            try {
                this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
                this.scanHistory = JSON.parse(localStorage.getItem('scanner_history') || '[]');
            } catch {
                this.recentCodes = [];
                this.scanHistory = [];
            }
        },

        addToRecent(code) {
            const item = { value: code, timestamp: Date.now() };
            this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10);
            
            try {
                localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
            } catch (error) {
                console.error('Erreur sauvegarde:', error);
            }
        },

        addToHistory(code, result, success) {
            const item = { code, result, success, timestamp: Date.now() };
            this.scanHistory = [item, ...this.scanHistory].slice(0, 20);
            
            try {
                localStorage.setItem('scanner_history', JSON.stringify(this.scanHistory));
            } catch (error) {
                console.error('Erreur sauvegarde:', error);
            }
        },

        // ==================== UTILITAIRES ====================
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', { 
                hour: '2-digit', minute: '2-digit' 
            });
        },

        formatMoney(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        }
    }
}
</script>