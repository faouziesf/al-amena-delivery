<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Scanner Dépôt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Plein écran sans marges */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #000;
        }
        
        /* Caméra plein écran */
        #video {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
        }
        
        /* Overlay pour le cadre de scan */
        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        /* Cadre de scan */
        .scan-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 3px solid #fff;
            border-radius: 20px;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
        }
        
        /* Coins du cadre */
        .scan-frame::before,
        .scan-frame::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            border: 4px solid #10b981;
        }
        
        .scan-frame::before {
            top: -4px;
            left: -4px;
            border-right: none;
            border-bottom: none;
            border-radius: 20px 0 0 0;
        }
        
        .scan-frame::after {
            bottom: -4px;
            right: -4px;
            border-left: none;
            border-top: none;
            border-radius: 0 0 20px 0;
        }
        
        /* Animation de scan */
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: scan-animation 2s linear infinite;
        }
        
        @keyframes scan-animation {
            0% { top: 0; }
            100% { top: 100%; }
        }
        
        /* Feedback visuel */
        .feedback {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            text-align: center;
        }
        
        /* Flash effects */
        .flash-success {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(16, 185, 129, 0.3);
            animation: flash 0.3s ease-out;
        }
        
        .flash-error {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(239, 68, 68, 0.3);
            animation: flash 0.5s ease-out;
        }
        
        @keyframes flash {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        /* Safe areas iPhone */
        .safe-top {
            padding-top: max(20px, env(safe-area-inset-top));
        }
        
        .safe-bottom {
            padding-bottom: max(20px, env(safe-area-inset-bottom));
        }
    </style>
</head>
<body>
    
    <!-- Caméra Video -->
    <video id="video" autoplay muted playsinline></video>
    
    <!-- Overlay de scan -->
    <div class="scan-overlay">
        <div class="scan-frame">
            <div class="scan-line"></div>
        </div>
    </div>
    
    <!-- Feedback Zone -->
    <div class="feedback safe-top">
        <div id="status-message" class="bg-black/70 text-white px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm">
            📷 Positionnez le code dans le cadre
        </div>
    </div>
    
    <!-- Bottom Info -->
    <div class="absolute bottom-0 left-0 right-0 safe-bottom">
        <div class="text-center p-4">
            <div class="bg-black/70 text-white px-4 py-2 rounded-full text-xs backdrop-blur-sm inline-block">
                <span id="scan-count">0</span> colis scannés
            </div>
        </div>
    </div>
    
    <!-- Flash Overlay Container -->
    <div id="flash-container"></div>

    <script>
        const sessionId = '{{ $sessionId }}';
        let codeReader = null;
        let isScanning = false;
        let scanCount = 0;
        let lastScanTime = 0;
        const SCAN_COOLDOWN = 1000; // 1 seconde entre les scans

        // Initialiser la caméra et le scanner
        async function initializeScanner() {
            try {
                // Initialiser ZXing
                codeReader = new ZXing.BrowserMultiFormatReader();
                
                // Obtenir les caméras disponibles
                const videoInputDevices = await codeReader.listVideoInputDevices();
                
                if (videoInputDevices.length === 0) {
                    showMessage('❌ Aucune caméra détectée', 'error');
                    return;
                }
                
                // Utiliser la caméra arrière si disponible
                let selectedDeviceId = videoInputDevices[0].deviceId;
                for (const device of videoInputDevices) {
                    if (device.label.toLowerCase().includes('back') || 
                        device.label.toLowerCase().includes('rear') ||
                        device.label.toLowerCase().includes('environment')) {
                        selectedDeviceId = device.deviceId;
                        break;
                    }
                }
                
                // Démarrer le scan
                showMessage('📷 Démarrage de la caméra...', 'info');
                
                await codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
                    if (result) {
                        handleScanResult(result.getText());
                    }
                });
                
                showMessage('✅ Prêt à scanner', 'success');
                isScanning = true;
                
            } catch (error) {
                console.error('Erreur initialisation scanner:', error);
                showMessage('❌ Erreur caméra: ' + error.message, 'error');
            }
        }

        // Gérer le résultat du scan
        function handleScanResult(code) {
            const now = Date.now();
            
            // Éviter les scans trop rapprochés
            if (now - lastScanTime < SCAN_COOLDOWN) {
                return;
            }
            
            lastScanTime = now;
            
            // Vibration si disponible
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
            
            // Envoyer le code au serveur
            scanPackage(code);
        }

        // Scanner un colis via API
        async function scanPackage(code) {
            try {
                showMessage('🔍 Vérification...', 'info');
                
                const response = await fetch(`/depot/api/session/${sessionId}/scan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Succès
                    scanCount++;
                    document.getElementById('scan-count').textContent = scanCount;
                    
                    showFlash('success');
                    showMessage(`✅ ${data.tracking_number}`, 'success');
                    
                    // Vibration succès
                    if (navigator.vibrate) {
                        navigator.vibrate([100, 50, 100]);
                    }
                    
                } else {
                    // Erreur
                    showFlash('error');
                    showMessage(`❌ ${data.message}`, 'error');
                    
                    // Vibration erreur
                    if (navigator.vibrate) {
                        navigator.vibrate([200, 100, 200, 100, 200]);
                    }
                }
                
            } catch (error) {
                console.error('Erreur scan:', error);
                showFlash('error');
                showMessage('❌ Erreur connexion', 'error');
                
                if (navigator.vibrate) {
                    navigator.vibrate([300, 100, 300]);
                }
            }
        }

        // Afficher un message
        function showMessage(text, type = 'info') {
            const messageEl = document.getElementById('status-message');
            messageEl.textContent = text;
            
            // Couleurs selon le type
            messageEl.className = 'px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm transition-colors';
            
            switch (type) {
                case 'success':
                    messageEl.classList.add('bg-green-600/90', 'text-white');
                    break;
                case 'error':
                    messageEl.classList.add('bg-red-600/90', 'text-white');
                    break;
                case 'info':
                    messageEl.classList.add('bg-blue-600/90', 'text-white');
                    break;
                default:
                    messageEl.classList.add('bg-black/70', 'text-white');
            }
            
            // Retour au message par défaut après 3 secondes
            if (type !== 'info') {
                setTimeout(() => {
                    showMessage('📷 Positionnez le code dans le cadre', 'info');
                }, 3000);
            }
        }

        // Effet flash
        function showFlash(type) {
            const flashContainer = document.getElementById('flash-container');
            const flash = document.createElement('div');
            flash.className = type === 'success' ? 'flash-success' : 'flash-error';
            
            flashContainer.appendChild(flash);
            
            // Supprimer après l'animation
            setTimeout(() => {
                flashContainer.removeChild(flash);
            }, 500);
        }

        // Gestion des erreurs de caméra
        function handleCameraError(error) {
            console.error('Erreur caméra:', error);
            
            let message = '❌ Erreur caméra';
            if (error.name === 'NotAllowedError') {
                message = '❌ Accès caméra refusé';
            } else if (error.name === 'NotFoundError') {
                message = '❌ Caméra non trouvée';
            }
            
            showMessage(message, 'error');
        }

        // Empêcher le zoom et la rotation
        document.addEventListener('touchstart', function(e) {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        });

        document.addEventListener('touchmove', function(e) {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        });

        // Empêcher le menu contextuel
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Garder l'écran allumé si possible
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(err => {
                console.log('Wake lock non supporté:', err);
            });
        }

        // Initialisation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            initializeScanner();
        });

        // Nettoyage à la fermeture
        window.addEventListener('beforeunload', function() {
            if (codeReader) {
                codeReader.reset();
            }
        });
    </script>
</body>
</html>
