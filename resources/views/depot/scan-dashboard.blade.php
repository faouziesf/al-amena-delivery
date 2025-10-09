@extends('layouts.depot-manager')

@section('title', 'Scan Dépôt')
@section('page-title', '🏭 Scan Dépôt PC/Téléphone')
@section('page-description', 'Système de scan en temps réel pour réception des colis au dépôt')

@push('styles')
<!-- QRCode Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

<style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        
        /* Better button hover effects */
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@section('content')
    <!-- Statut de connexion en haut -->
    <div class="mb-6 flex items-center justify-between bg-white rounded-xl shadow-lg p-4">
        <div class="flex items-center space-x-4">
            <div id="connection-status" class="flex items-center space-x-2">
                <div id="status-indicator" class="w-3 h-3 bg-red-500 rounded-full"></div>
                <span id="status-text" class="text-sm font-medium text-red-600">En attente</span>
            </div>
            <div class="text-sm text-gray-500">Session : <span class="font-mono text-gray-700">{{ substr($sessionId, 0, 8) }}...</span></div>
        </div>
        <div>
            <a href="{{ route('depot.scan.help') }}" 
               class="text-blue-600 hover:text-blue-700 text-sm font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Guide d'utilisation
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- QR Code Section -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">📱 Connexion Téléphone</h2>
                    
                    <!-- QR Code Container -->
                    <div class="bg-gray-50 rounded-lg p-8 mb-6 flex justify-center items-center min-h-[320px]" id="qr-container">
                        <div id="qrcode" class="mx-auto" style="width: 300px; height: 300px; display: flex; justify-content: center; align-items: center; border: 2px solid #e5e7eb; border-radius: 8px; background: white;"></div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-lg font-medium text-gray-700 mb-2">
                            Scannez ce code avec votre téléphone
                        </p>
                        <p class="text-sm text-gray-500">
                            Utilisez l'appareil photo de votre téléphone pour scanner le QR code
                        </p>
                    </div>

                    <!-- URL Alternative -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-xs text-blue-600 mb-2">Ou ouvrez directement :</p>
                        <div class="flex items-center justify-center space-x-2">
                            <input type="text" 
                                   id="scanner-url" 
                                   value="{{ route('depot.scan.phone', $sessionId) }}"
                                   class="text-xs bg-white border rounded px-2 py-1 flex-1 max-w-xs"
                                   readonly>
                            <button onclick="copyUrl()" 
                                    class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">📊 Statistiques</h2>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div id="total-scanned" class="text-3xl font-bold text-blue-600">0</div>
                        <div class="text-sm text-blue-600">Colis Scannés</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div id="scan-rate" class="text-3xl font-bold text-green-600">0</div>
                        <div class="text-sm text-green-600">Par Minute</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Session démarrée :</span>
                        <span id="session-start" class="font-medium">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Dernier scan :</span>
                        <span id="last-scan" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Durée session :</span>
                        <span id="session-duration" class="font-medium">00:00:00</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 space-y-2">
                    <form id="validate-form" method="POST" action="{{ route('depot.scan.validate.all', $sessionId) }}" onsubmit="return confirmValidation()">
                        @csrf
                        <button type="submit" 
                                id="validate-btn"
                                class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            ✅ Valider Réception au Dépôt
                        </button>
                    </form>
                    <button onclick="exportData()" 
                            id="export-btn"
                            class="w-full bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        📄 Exporter CSV
                    </button>
                    <button onclick="resetSession()" 
                            class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                        🔄 Nouvelle Session
                    </button>
                </div>
            </div>
        </div>

        <!-- Scanned Packages List -->
        <div class="mt-8 bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">📦 Colis Scannés</h2>
                <p class="text-gray-600">Liste en temps réel des colis traités</p>
            </div>
            
            <div class="p-6">
                <div id="packages-container">
                    <div class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-4">📱</div>
                        <p class="text-lg">Connectez votre téléphone pour commencer</p>
                        <p class="text-sm">Les colis scannés apparaîtront ici automatiquement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const sessionId = '{{ $sessionId }}';
        const scannerUrl = '{{ route("depot.scan.phone", $sessionId) }}';
        let sessionStartTime = new Date();
        let totalScanned = 0;
        let scanTimes = [];

        // Générer le QR Code - Méthode du compte client
        function generateQRCode() {
            try {
                const qrContainer = document.getElementById('qrcode');
                if (!qrContainer) {
                    console.error('QR Container not found');
                    return;
                }

                // Vérifier que la bibliothèque est chargée
                if (typeof qrcode === 'undefined') {
                    console.warn('qrcode library not loaded yet, retrying...');
                    qrContainer.innerHTML = '<div style="text-align: center; color: #6b7280;"><p>Chargement...</p></div>';
                    setTimeout(generateQRCode, 100);
                    return;
                }

                const containerSize = 280; // Taille du QR code
                const qr = qrcode(0, 'M'); // Type 0, niveau de correction M
                qr.addData(scannerUrl);
                qr.make();

                // Calculer la taille optimale
                const moduleCount = qr.getModuleCount();
                const moduleSize = Math.max(1, Math.floor(containerSize / moduleCount));
                
                // Créer le canvas
                const qrCanvas = document.createElement('canvas');
                qrCanvas.width = containerSize;
                qrCanvas.height = containerSize;
                const ctx = qrCanvas.getContext('2d');

                // Centrer le QR code
                const actualQrSize = moduleCount * moduleSize;
                const offset = (containerSize - actualQrSize) / 2;

                // Dessiner le fond blanc
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, containerSize, containerSize);
                
                // Dessiner le QR code
                ctx.save();
                ctx.translate(offset, offset);
                qr.renderTo2dContext(ctx, moduleSize);
                ctx.restore();
                
                // Ajouter au DOM
                qrContainer.innerHTML = '';
                qrContainer.appendChild(qrCanvas);
                
                console.log('✅ QR Code généré avec succès');

            } catch (error) {
                console.error('Erreur génération QR Code:', error);
                const qrContainer = document.getElementById('qrcode');
                if (qrContainer) {
                    qrContainer.innerHTML = `
                        <div style="text-align: center; color: #ef4444; padding: 20px;">
                            <p style="font-weight: bold; margin-bottom: 10px;">❌ Erreur</p>
                            <p style="font-size: 14px;">Utilisez le lien ci-dessous</p>
                        </div>
                    `;
                }
            }
        }

        // Copier l'URL
        function copyUrl() {
            const urlInput = document.getElementById('scanner-url');
            urlInput.select();
            document.execCommand('copy');
            
            // Feedback visuel
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = 'Copié !';
            btn.classList.add('bg-green-600');
            btn.classList.remove('bg-blue-600');
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-blue-600');
            }, 2000);
        }

        // Mettre à jour le statut de connexion
        function updateConnectionStatus(status) {
            const indicator = document.getElementById('status-indicator');
            const text = document.getElementById('status-text');
            
            if (status === 'connected') {
                indicator.className = 'w-3 h-3 bg-green-500 rounded-full animate-pulse';
                text.textContent = 'Connecté';
                text.className = 'text-sm font-medium text-green-600';
            } else {
                indicator.className = 'w-3 h-3 bg-red-500 rounded-full';
                text.textContent = 'En attente';
                text.className = 'text-sm font-medium text-red-600';
            }
        }

        // Mettre à jour la liste des colis
        function updatePackagesList(packages) {
            const container = document.getElementById('packages-container');
            
            if (packages.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-4">📱</div>
                        <p class="text-lg">Connectez votre téléphone pour commencer</p>
                        <p class="text-sm">Les colis scannés apparaîtront ici automatiquement</p>
                    </div>
                `;
                return;
            }

            const packagesHtml = packages.reverse().map((pkg, index) => `
                <div class="flex items-center justify-between p-4 border-b hover:bg-gray-50 ${index === 0 ? 'bg-green-50 border-green-200' : ''}">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">
                            ${packages.length - index}
                        </div>
                        <div>
                            <div class="font-mono font-bold text-gray-900">${pkg.tracking_number}</div>
                            <div class="text-sm text-gray-500">Code: ${pkg.code}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900">${pkg.scanned_time}</div>
                        <div class="text-xs text-gray-500">${pkg.status}</div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = `
                <div class="space-y-0">
                    ${packagesHtml}
                </div>
            `;
        }

        // Calculer le taux de scan
        function updateScanRate() {
            const now = new Date();
            scanTimes = scanTimes.filter(time => now - time < 60000); // Garder seulement la dernière minute
            document.getElementById('scan-rate').textContent = scanTimes.length;
        }

        // Mettre à jour la durée de session
        function updateSessionDuration() {
            const now = new Date();
            const diff = now - sessionStartTime;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            
            document.getElementById('session-duration').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        // Polling pour les mises à jour
        function pollUpdates() {
            fetch(`/depot/api/session/${sessionId}/status`)
                .then(response => response.json())
                .then(data => {
                    updateConnectionStatus(data.status);
                    
                    if (data.scanned_packages !== undefined) {
                        const newTotal = data.scanned_packages.length;
                        
                        // Si le total change (augmente ou diminue à 0 après validation)
                        if (newTotal !== totalScanned) {
                            if (newTotal > totalScanned) {
                                scanTimes.push(new Date());
                                document.getElementById('last-scan').textContent = new Date().toLocaleTimeString();
                            }
                            
                            totalScanned = newTotal;
                            
                            // Mettre à jour l'affichage
                            document.getElementById('total-scanned').textContent = totalScanned;
                            document.getElementById('export-btn').disabled = totalScanned === 0;
                            document.getElementById('validate-btn').disabled = totalScanned === 0;
                            
                            updateScanRate();
                        }
                        
                        updatePackagesList(data.scanned_packages);
                    }
                })
                .catch(error => {
                    console.error('Erreur polling:', error);
                });
        }

        // Exporter les données
        function exportData() {
            if (totalScanned === 0) return;
            window.open(`/depot/scan/${sessionId}/export`, '_blank');
        }

        // Confirmer la validation
        function confirmValidation() {
            if (totalScanned === 0) {
                alert('Aucun colis à valider');
                return false;
            }
            return confirm(`Confirmer la réception de ${totalScanned} colis au dépôt ?\n\nTous les colis seront marqués comme "AVAILABLE" (disponibles pour livraison).`);
        }

        // Nouvelle session
        function resetSession() {
            if (totalScanned > 0 && !confirm('Êtes-vous sûr de vouloir créer une nouvelle session ? Les données actuelles seront perdues.')) {
                return;
            }
            window.location.href = '/depot/scan';
        }

        // Terminer la session quand la page est quittée ou rafraîchie
        function terminateSession() {
            // Envoyer requête synchrone pour terminer la session
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `/depot/scan/${sessionId}/terminate`, false); // false = synchrone
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send();
        }

        // Événements de fermeture de page
        window.addEventListener('beforeunload', function(e) {
            terminateSession();
        });

        window.addEventListener('unload', function(e) {
            terminateSession();
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            generateQRCode();
            
            // Démarrer le polling
            setInterval(pollUpdates, 1000);
            setInterval(updateSessionDuration, 1000);
            setInterval(updateScanRate, 5000);
            
            // Premier appel
            pollUpdates();
        });
    </script>
@endpush
