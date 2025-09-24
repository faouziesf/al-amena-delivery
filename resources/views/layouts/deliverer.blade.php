<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Al-Amena Livreur</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    
    <link rel="icon" type="image/png" href="/images/icons/icon-32x32.png">
    
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <link rel="preload" href="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" as="script">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"
            onerror="console.log('Alpine.js CDN échoué, fallback activé')"></script>

    <!-- Fallbacks locaux en cas de problème de connexion -->
    <script src="{{ asset('js/libs/alpine-simple.js') }}"></script>
    <script src="{{ asset('js/libs/scanner-fallback.js') }}"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chargement prioritaire des bibliothèques de scan avec fallbacks -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js" crossorigin="anonymous"
            onerror="console.log('Quagga CDN échoué, fallback activé')"></script>
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js" crossorigin="anonymous"
            onerror="console.log('jsQR principal échoué, fallback activé')"></script>
    <!-- Backup QR scanner -->
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js" crossorigin="anonymous"
            onerror="console.log('ZXing backup échoué, fallback activé')"></script>

    <!-- CSS simple pour le scanner -->
    <style>
        /* Animation spinner simple */
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Masquer warnings Tailwind */
        [data-tailwind-warning] { display: none !important; }

        html {
            scroll-behavior: smooth;
            -webkit-tap-highlight-color: transparent;
        }

        /* Améliorations mobiles */
        @media (max-width: 768px) {
            .mobile-optimized {
                padding: 12px;
                border-radius: 16px;
            }

            .mobile-button {
                padding: 14px 20px;
                font-size: 16px;
                border-radius: 12px;
                min-height: 48px;
            }

            .mobile-card {
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }
        }

        /* Animations modernes */
        .bounce-in {
            animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Couleurs principales - Pale Purple */
        :root {
            --primary-purple: #E9D8FD;
            --primary-purple-light: #F7EEFF;
            --primary-purple-medium: #D6C2F0;
            --primary-purple-dark: #B794E6;
            --accent-purple: #A78BFA;
            --text-purple: #553C9A;
        }

        /* Dégradés personnalisés avec couleur pale purple */
        .purple-gradient {
            background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-purple-medium) 100%);
        }

        .pale-purple-bg {
            background-color: var(--primary-purple);
        }

        .pale-purple-light-bg {
            background-color: var(--primary-purple-light);
        }

        .text-purple-dark {
            color: var(--text-purple);
        }
    </style>

    <!-- Scanner Simple - Nouveau système -->
    <script src="{{ asset('js/simple-scanner.js') }}"></script>

    <!-- Package Validator FINAL - SIMPLE -->
    <script src="{{ asset('js/package-validator-no-validation.js') }}"></script>

    <!-- Scanner Component Function - DOIT être chargé avant Alpine.js -->
    <script defer>
    window.delivererQRScanner = function(options = {}) {
        return {
            // Configuration
            scanMode: options.mode || 'single',
            autoRedirect: options.autoRedirect !== false,
            showRecent: options.showRecent !== false,

            // UI State
            scannerVisible: false,
            resultVisible: false,
            showPackageList: false,
            activeMode: 'camera', // 'camera' or 'manual'

            // Camera State
            cameraActive: false,
            permissionAsked: false,
            cameraErrorMsg: '',
            stream: null,
            torchEnabled: false,
            hasMultipleCameras: false,
            scanQuality: 'medium',

            // Scanner State
            searching: false,
            processing: false,
            scanInterval: null,
            scanCycle: 0,
            scanType: 'barcode',
            lastDetection: null,
            lastDetectionTime: 0,
            scanEnabled: true,
            consecutiveFailures: 0,
            watchdogInterval: null,

            // Data
            currentCode: '', // Nouveau: code actuel dans le champ unique
            manualCode: '', // Garder pour compatibilité
            codeValid: false,
            recentCodes: [],
            scannedCodes: [],
            scanHistory: [],

            // Système de liste de colis
            packageList: [],
            currentPackage: null,

            // Statistiques en temps réel
            totalPackages: 0,
            totalCOD: 0,
            batchStartTime: null,

            // Modal state pour le résumé
            batchSummaryVisible: false,

            // Variables pour compatibilité template
            batchScanMode: false,
            totalScanned: 0,

            // Toast system
            toastMessage: '',
            toastType: 'success',

            // Additional variables required by template
            resultVisible: false,
            result: {
                success: false,
                message: '',
                package: null,
                delivery_info: null,
                redirect: false
            },

            // Variables pour batch scanner
            visible: false,
            maxItems: 50,
            allowDuplicates: false,
            autoProcess: false,
            validCodes: [],

            init() {
                window.scannerComponent = this;
                this.loadStoredData();
                this.initAudioContext();
                this.ensureLibrariesLoaded();
                this.$watch('currentCode', () => this.validateCode());
                this.$watch('manualCode', () => this.validateCode()); // Garder pour compatibilité

                // Démarrer automatiquement la caméra si HTTPS
                if (this.isHttps) {
                    setTimeout(() => this.startCamera(), 500);
                }
            },

            // Assurer que les bibliothèques sont chargées
            async ensureLibrariesLoaded() {
                // Vérifier jsQR
                if (typeof jsQR === 'undefined') {
                    console.log('jsQR non chargé, tentative de chargement...');
                    try {
                        await this.loadScript('https://unpkg.com/jsqr@1.4.0/dist/jsQR.js');
                        console.log('jsQR chargé avec succès');
                    } catch (error) {
                        console.error('Erreur chargement jsQR:', error);
                    }
                }

                // Vérifier Quagga
                if (typeof Quagga === 'undefined') {
                    console.log('Quagga non chargé, tentative de chargement...');
                    try {
                        await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js');
                        console.log('Quagga chargé avec succès');
                    } catch (error) {
                        console.error('Erreur chargement Quagga:', error);
                    }
                }
            },

            loadScript(src) {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = src;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            },

            // Système audio
            initAudioContext() {
                try {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                } catch (error) {
                    console.log('AudioContext non supporté');
                }
            },

            playBeep(frequency = 1000, duration = 200, type = 'success') {
                if (!this.audioContext) return;

                try {
                    const oscillator = this.audioContext.createOscillator();
                    const gainNode = this.audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(this.audioContext.destination);

                    // Configuration selon le type
                    switch (type) {
                        case 'success':
                            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime);
                            oscillator.frequency.setValueAtTime(1200, this.audioContext.currentTime + 0.1);
                            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
                            break;
                        case 'error':
                            oscillator.frequency.setValueAtTime(400, this.audioContext.currentTime);
                            oscillator.frequency.setValueAtTime(200, this.audioContext.currentTime + 0.15);
                            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
                            duration = 300;
                            break;
                        case 'scan':
                            oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime);
                            gainNode.gain.setValueAtTime(0.2, this.audioContext.currentTime);
                            duration = 100;
                            break;
                        case 'added':
                            // Son d'ajout à la liste
                            oscillator.frequency.setValueAtTime(500, this.audioContext.currentTime);
                            oscillator.frequency.setValueAtTime(700, this.audioContext.currentTime + 0.05);
                            gainNode.gain.setValueAtTime(0.25, this.audioContext.currentTime);
                            duration = 150;
                            break;
                        case 'batch-complete':
                            // Son de fin de batch
                            oscillator.frequency.setValueAtTime(600, this.audioContext.currentTime);
                            oscillator.frequency.setValueAtTime(800, this.audioContext.currentTime + 0.1);
                            oscillator.frequency.setValueAtTime(1000, this.audioContext.currentTime + 0.2);
                            gainNode.gain.setValueAtTime(0.4, this.audioContext.currentTime);
                            duration = 500;
                            break;
                        case 'camera-start':
                            // Son de démarrage caméra
                            oscillator.frequency.setValueAtTime(440, this.audioContext.currentTime);
                            oscillator.frequency.setValueAtTime(880, this.audioContext.currentTime + 0.1);
                            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
                            duration = 200;
                            break;
                        case 'scanning':
                            // Son continu de scan
                            oscillator.frequency.setValueAtTime(700, this.audioContext.currentTime);
                            gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
                            duration = 50;
                            break;
                    }

                    oscillator.type = 'sine';
                    gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + duration / 1000);

                    oscillator.start(this.audioContext.currentTime);
                    oscillator.stop(this.audioContext.currentTime + duration / 1000);
                } catch (error) {
                    console.error('Erreur audio:', error);
                }
            },

            // Scanner Management
            openScanner(params = {}) {
                this.scanMode = params.mode || this.scanMode;
                this.batchScanMode = this.scanMode === 'batch';
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
                this.scannerVisible = false;
                this.resultVisible = false;
                this.stopCamera();
                this.resetScanner();
            },

            resetScanner() {
                this.searching = false;
                this.processing = false;
                this.manualCode = '';
                this.currentCode = '';
                this.codeValid = false;
                this.result = {
                    success: false,
                    message: '',
                    package: null,
                    delivery_info: null,
                    redirect: false
                };
                this.resultVisible = false;
                this.currentPackage = null;
                this.scannedCodes = [];
            },

            // Méthodes essentielles pour Alpine.js
            get isMobile() {
                return /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            },

            get isHttps() {
                return location.protocol === 'https:';
            },

            validateCode() {
                const code = this.currentCode || this.manualCode;
                this.codeValid = code && code.trim().length > 0;
            },

            loadStoredData() {
                if (window.ScannerUtils && window.ScannerUtils.Storage) {
                    this.recentCodes = window.ScannerUtils.Storage.getRecentCodes();
                }
            },

            searchCode() {
                if (!this.currentCode.trim() || this.searching) return;

                this.searching = true;
                // Simulation de recherche
                setTimeout(() => {
                    this.searching = false;
                    this.result = {
                        success: true,
                        message: 'Colis trouvé avec succès'
                    };
                    this.resultVisible = true;
                }, 1000);
            },

            async startCamera() {
                try {
                    this.permissionAsked = true;
                    this.cameraErrorMsg = '';
                    this.cameraActive = false;

                    // Vérifier si getUserMedia est disponible
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        throw new Error('L\'accès à la caméra n\'est pas supporté par ce navigateur ou nécessite HTTPS');
                    }

                    // Attendre que le DOM soit mis à jour
                    await this.$nextTick();

                    // Attendre un peu plus pour l'élément vidéo
                    await new Promise(resolve => setTimeout(resolve, 200));

                    // Vérifier que l'élément vidéo existe
                    if (!this.$refs.videoElement) {
                        throw new Error('Élément vidéo non trouvé dans le DOM');
                    }

                    console.log('Demande d\'accès à la caméra...');

                    // Demander l'accès à la caméra
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: this.isMobile ? 'environment' : 'user',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });

                    console.log('Accès caméra obtenu, configuration de la vidéo...');
                    this.stream = stream;

                    // Configurer l'élément vidéo
                    this.$refs.videoElement.srcObject = stream;

                    // Événement pour détecter quand la vidéo est prête
                    this.$refs.videoElement.onloadedmetadata = () => {
                        console.log('Métadonnées vidéo chargées');
                        this.cameraActive = true;

                        // Son de confirmation caméra active
                        this.playBeep(440, 200, 'camera-start');

                        // Attendre que la vidéo soit vraiment prête avant de démarrer le scan
                        setTimeout(() => {
                            if (this.cameraActive && this.$refs.videoElement.readyState >= 2) {
                                this.startScanning();
                            }
                        }, 1500); // Délai plus long pour assurer la stabilité
                    };

                    // Démarrer la lecture
                    try {
                        await this.$refs.videoElement.play();
                        console.log('Lecture vidéo démarrée');
                        // Forcer l'activation si pas encore fait
                        setTimeout(() => {
                            if (this.stream && !this.cameraActive) {
                                this.cameraActive = true;
                            }
                        }, 500);
                    } catch (playError) {
                        console.error('Erreur lecture vidéo:', playError);
                        throw new Error('Impossible de démarrer la lecture vidéo');
                    }

                } catch (error) {
                    console.error('Erreur caméra:', error);
                    this.cameraErrorMsg = `Erreur d'accès à la caméra: ${error.message}`;
                    this.cameraActive = false;
                    this.stopCamera();
                }
            },

            stopCamera() {
                console.log('Arrêt de la caméra...');

                // Arrêter le scan
                this.stopScanning();

                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                    console.log('Stream caméra fermé');
                }

                if (this.$refs.videoElement) {
                    this.$refs.videoElement.srcObject = null;
                    this.$refs.videoElement.onloadedmetadata = null;
                    console.log('Élément vidéo réinitialisé');
                }

                this.cameraActive = false;
                this.cameraErrorMsg = '';
            },

            // Fonctions de scan
            startScanning() {
                console.log('Démarrage du scan...');
                this.stopScanning(); // Arrêter tout scan en cours
                this.scanEnabled = true;
                this.consecutiveFailures = 0;

                // Activer AudioContext au premier scan (interaction utilisateur)
                if (this.audioContext && this.audioContext.state === 'suspended') {
                    this.audioContext.resume();
                }

                // Initialiser Quagga pour les codes-barres avec délai
                setTimeout(() => {
                    this.initQuaggaScanner();
                }, 500);

                // Démarrer le scan continu plus rapide
                this.scanInterval = setInterval(() => {
                    if (!this.scanEnabled) {
                        return;
                    }

                    // Vérifier que la caméra est toujours active
                    if (!this.cameraActive || !this.$refs.videoElement) {
                        console.log('Caméra inactive, arrêt du scan');
                        this.stopScanning();
                        return;
                    }

                    this.scanCycle++;

                    // Alternance plus fréquente et équilibrée
                    if (this.scanCycle % 2 === 0) {
                        this.scanType = 'qr';
                        this.analyzeQRFrame();
                    } else {
                        this.scanType = 'barcode';
                        // Quagga fonctionne en continu, pas besoin d'action ici
                    }

                    // Debugging - afficher le status toutes les 20 itérations
                    if (this.scanCycle % 20 === 0) {
                        console.log(`Scan actif - Cycle: ${this.scanCycle}, Mode: ${this.scanMode}, Enabled: ${this.scanEnabled}`);
                    }

                    // Son de feedback scan toutes les 5 secondes (5000ms / 200ms = 25 cycles)
                    if (this.scanCycle % 25 === 0 && this.scanCycle > 0) {
                        this.playBeep(700, 50, 'scanning');
                    }
                }, 200); // Plus rapide pour une meilleure réactivité

                console.log('Scan démarré - QR et codes-barres actifs');

                // Démarrer le watchdog pour surveiller le scan
                this.startWatchdog();
            },

            stopScanning() {
                if (this.scanInterval) {
                    clearInterval(this.scanInterval);
                    this.scanInterval = null;
                    console.log('Scan arrêté');
                }
                this.stopQuaggaScanner();
                this.stopWatchdog();
                this.scanCycle = 0;
                this.scanType = 'barcode';
            },

            // Watchdog pour surveiller et relancer le scan si nécessaire
            startWatchdog() {
                this.stopWatchdog(); // Nettoyer l'ancien watchdog

                this.watchdogInterval = setInterval(() => {
                    // Vérifier si le scan devrait être actif mais ne l'est pas
                    if (this.cameraActive && this.activeMode === 'camera' && this.scanEnabled) {
                        if (!this.scanInterval) {
                            console.log('Watchdog: Scan perdu, redémarrage...');
                            this.startScanning();
                        }
                    }
                }, 3000); // Vérifier toutes les 3 secondes
            },

            stopWatchdog() {
                if (this.watchdogInterval) {
                    clearInterval(this.watchdogInterval);
                    this.watchdogInterval = null;
                }
            },

            initQuaggaScanner() {
                if (typeof Quagga === 'undefined') {
                    console.log('Quagga non disponible');
                    return;
                }

                try {
                    // Arrêter Quagga s'il est déjà en cours
                    this.stopQuaggaScanner();

                    const video = this.$refs.videoElement;
                    if (!video || !video.videoWidth) {
                        console.log('Vidéo non prête pour Quagga');
                        return;
                    }

                    const config = {
                        inputStream: {
                            type: "LiveStream",
                            target: video,
                            constraints: {
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                                facingMode: this.isMobile ? "environment" : "user"
                            }
                        },
                        decoder: {
                            readers: [
                                "code_128_reader",
                                "ean_reader",
                                "ean_8_reader",
                                "code_39_reader",
                                "code_39_vin_reader",
                                "codabar_reader",
                                "upc_reader",
                                "upc_e_reader"
                            ]
                        },
                        locate: true,
                        debug: false,
                        multiple: false
                    };

                    Quagga.init(config, (err) => {
                        if (err) {
                            console.error('Erreur init Quagga:', err);
                            return;
                        }

                        Quagga.start();
                        console.log('Quagga démarré avec succès');
                    });

                    // Gestionnaire de détection amélioré avec nettoyage
                    Quagga.offDetected(); // Nettoyer les anciens handlers
                    Quagga.onDetected((result) => {
                        if (!this.scanEnabled || this.scanType !== 'barcode') {
                            return;
                        }

                        if (result?.codeResult?.code) {
                            const code = result.codeResult.code.trim();
                            console.log('Code-barres détecté par Quagga:', code);

                            if (this.isValidPackageCode(code)) {
                                this.onCodeDetected(code, 'BARCODE');
                            }
                        }
                    });

                } catch (error) {
                    console.error('Erreur initialisation Quagga:', error);
                }
            },

            stopQuaggaScanner() {
                if (typeof Quagga !== 'undefined') {
                    try {
                        // Nettoyer les handlers et arrêter Quagga
                        Quagga.offDetected(); // Nettoyer les handlers
                        Quagga.stop();
                        console.log('Quagga arrêté');
                    } catch (error) {
                        console.error('Erreur arrêt Quagga:', error);
                        // Force cleanup
                        try {
                            Quagga.offDetected();
                        } catch (e) {
                            console.error('Erreur nettoyage Quagga:', e);
                        }
                    }
                }
            },

            analyzeQRFrame() {
                if (!this.scanEnabled) return;

                try {
                    const video = this.$refs.videoElement;
                    let canvas = this.$refs.canvasElement;

                    // Vérifications strictes
                    if (!video || !video.videoWidth || !video.videoHeight) {
                        this.consecutiveFailures++;
                        return;
                    }

                    if (!video.readyState || video.readyState < 2) {
                        return; // Vidéo pas encore prête
                    }

                    // Créer un canvas temporaire si pas de référence
                    if (!canvas) {
                        canvas = document.createElement('canvas');
                    }

                    const ctx = canvas.getContext('2d');

                    // Utiliser des dimensions optimales pour jsQR
                    const scale = 0.5; // Réduire pour améliorer les performances
                    canvas.width = video.videoWidth * scale;
                    canvas.height = video.videoHeight * scale;

                    // Dessiner la frame vidéo
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // Essayer jsQR en premier
                    if (typeof jsQR !== 'undefined') {
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                        // Options optimisées pour jsQR
                        const qrResult = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "attemptBoth" // Essayer l'inversion pour améliorer la détection
                        });

                        if (qrResult?.data) {
                            const code = qrResult.data.trim();
                            console.log('QR détecté (jsQR):', code);

                            if (this.isValidCode(code)) {
                                this.onCodeDetected(code, 'QR');
                                return;
                            }
                        }
                    }
                    // Fallback vers ZXing si jsQR n'est pas disponible ou échoue
                    else if (typeof ZXing !== 'undefined') {
                        try {
                            const codeReader = new ZXing.BrowserQRCodeReader();
                            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                            // Convertir ImageData en format ZXing
                            const source = new ZXing.RGBLuminanceSource(
                                new Uint8ClampedArray(imageData.data),
                                canvas.width,
                                canvas.height
                            );

                            const bitmap = new ZXing.BinaryBitmap(new ZXing.HybridBinarizer(source));
                            const qrResult = codeReader.decode(bitmap);

                            if (qrResult) {
                                const code = qrResult.getText().trim();
                                console.log('QR détecté (ZXing):', code);

                                if (this.isValidCode(code)) {
                                    this.onCodeDetected(code, 'QR');
                                    return;
                                }
                            }
                        } catch (e) {
                            // ZXing peut lancer des erreurs si pas de QR trouvé, c'est normal
                        }
                    } else {
                        // Avertissement silencieux après le premier cycle
                        if (this.scanCycle % 100 === 0) {
                            console.warn('Aucune bibliothèque QR disponible - jsQR et ZXing manquants');
                        }
                    }

                    // Reset des échecs consécutifs si on arrive ici
                    this.consecutiveFailures = 0;

                } catch (error) {
                    this.consecutiveFailures++;
                    console.error('Erreur analyse QR:', error);

                    // Arrêter temporairement si trop d'échecs
                    if (this.consecutiveFailures > 10) {
                        console.log('Trop d\'échecs QR, pause temporaire');
                        setTimeout(() => {
                            this.consecutiveFailures = 0;
                        }, 2000);
                    }
                }
            },

            onCodeDetected(code, type) {
                const now = Date.now();

                // Éviter les détections multiples du même code dans un court délai
                if (this.lastDetection === code && (now - this.lastDetectionTime) < 2000) {
                    console.log('Code ignoré (détection récente):', code);
                    return;
                }

                console.log(`Code ${type} détecté:`, code);
                this.lastDetection = code;
                this.lastDetectionTime = now;

                // Son de détection initiale
                this.playBeep(600, 100, 'scan');

                // Remplir le champ avec le code détecté
                this.currentCode = code;
                this.validateCode();

                // Pause temporaire du scan pour éviter les détections multiples
                this.scanEnabled = false;

                // Auto-validation du package si le code est valide
                if (this.codeValid) {
                    setTimeout(() => {
                        this.validateCurrentPackage();
                    }, 300);
                }

                // En mode batch, reprendre le scan immédiatement après traitement
                if (this.scanMode === 'batch') {
                    setTimeout(() => {
                        if (this.cameraActive) {
                            this.scanEnabled = true;
                            console.log('Scan repris en mode batch');
                        }
                    }, 1500); // Laisser du temps pour la validation
                } else {
                    // En mode normal, reprendre après affichage du résultat
                    setTimeout(() => {
                        if (this.cameraActive && !this.resultVisible) {
                            this.scanEnabled = true;
                            console.log('Scan repris en mode normal');
                        }
                    }, 1500);
                }

                // Reset de la dernière détection après un délai
                setTimeout(() => {
                    this.lastDetection = null;
                    this.lastDetectionTime = 0;
                }, 3000);
            },

            isValidCode(code) {
                // Utiliser la même validation que pour isValidPackageCode
                return this.isValidPackageCode(code);
            },

            // 🚀 LOGIQUE ULTRA SIMPLE QUI MARCHE TOUJOURS
            extractPackageCodeFromInput(input) {
                return window.extractCodeFromUrl ? window.extractCodeFromUrl(input) : (input || '').toString().trim().toUpperCase();
            },

            isValidPackageCode(input) {
                return window.isValidPackageCode ? window.isValidPackageCode(input) : true;
            },

            async processCode(input) {
                this.searching = true;

                // Envoyer directement l'input au backend sans validation frontend
                const rawInput = input.trim();
                if (!rawInput) {
                    this.searching = false;
                    this.currentPackage = {
                        success: false,
                        message: 'Veuillez saisir un code.'
                    };
                    this.result = this.currentPackage;
                    return;
                }

                try {
                    this.addToRecent(rawInput);

                    const response = await fetch('/deliverer/packages/scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ code: rawInput })
                    });

                    const data = await response.json();
                    this.currentPackage = data;
                    this.result = data; // Synchroniser result avec currentPackage

                    // Si le colis est valide, on le stocke temporairement pour validation
                    if (data.success && data.package) {
                        this.playBeep(800, 200, 'success');
                        console.log('Colis trouvé:', data.package);
                    } else {
                        this.playBeep(400, 300, 'error');
                        console.log('Code non valide:', code);
                    }

                } catch (error) {
                    console.error('Erreur traitement code:', error);
                    this.playBeep(400, 300, 'error');
                    this.currentPackage = {
                        success: false,
                        message: 'Erreur de connexion.'
                    };
                }

                this.searching = false;
            },

            showResult(data) {
                this.result = data;
                this.resultVisible = true;
                console.log('Résultat:', data);

                // Son en fonction du résultat
                if (data.success) {
                    this.playBeep(1000, 300, 'success'); // Son de succès
                } else {
                    this.playBeep(400, 300, 'error'); // Son d'erreur
                }
            },

            // Nouvelles fonctions pour la gestion des colis
            async validateCurrentPackage() {
                if (this.searching) return;

                const input = this.currentCode.trim();
                if (!input) return;

                this.searching = true;

                try {
                    // Envoyer directement l'input au backend
                    await this.processCode(input);

                    // Si le colis est valide et qu'on est en mode batch, l'ajouter à la liste
                    if (this.currentPackage && this.currentPackage.success && this.currentPackage.package) {
                        if (this.scanMode === 'batch') {
                            this.addPackageToList(this.currentPackage);
                            this.playBeep(600, 300, 'added');

                            // Reset pour le prochain scan
                            this.currentCode = '';
                            this.currentPackage = null;

                            // Reprendre le scan automatiquement
                            setTimeout(() => {
                                if (this.cameraActive) {
                                    this.scanEnabled = true;
                                }
                            }, 500);
                        } else {
                            // Mode single: afficher le résultat
                            this.showResult(this.currentPackage);
                        }
                    } else {
                        // Afficher l'erreur
                        this.showResult(this.currentPackage || {
                            success: false,
                            message: 'Code invalide ou colis non trouvé.'
                        });
                    }
                } catch (error) {
                    console.error('Erreur validation:', error);
                    this.showResult({
                        success: false,
                        message: 'Erreur de validation.'
                    });
                }

                this.searching = false;
            },

            addPackageToList(packageData) {
                const packageItem = {
                    id: Date.now() + Math.random(), // ID unique
                    code: packageData.package.code,
                    name: packageData.delivery_info?.name || 'Destinataire non spécifié',
                    address: packageData.delivery_info?.address || 'Adresse non spécifiée',
                    package: packageData.package,
                    delivery_info: packageData.delivery_info || null,
                    cod_amount: packageData.package.cod_amount || 0,
                    timestamp: Date.now(),
                    formatted_cod: packageData.package.formatted_cod || '0 DA'
                };

                this.packageList.push(packageItem);
                this.updateStatistics();

                console.log(`Colis ajouté: ${packageItem.code}`);
            },

            removePackageFromList(index) {
                if (index >= 0 && index < this.packageList.length) {
                    this.packageList.splice(index, 1);
                    this.updateStatistics();
                    this.playBeep(300, 100, 'scan');
                }
            },

            updateStatistics() {
                this.totalPackages = this.packageList.length;
                this.totalCOD = this.packageList.reduce((sum, item) => sum + (item.cod_amount || 0), 0);
                this.totalScanned = this.packageList.length;
                this.batchScanMode = this.scanMode === 'batch';
            },

            clearCurrentPackage() {
                this.currentPackage = null;
                this.manualCode = '';
            },

            finishBatch() {
                if (this.packageList.length === 0) {
                    this.playBeep(400, 200, 'error');
                    return;
                }

                this.showPackageList = true;
                this.playBeep(600, 500, 'batch-complete');
                this.stopCamera(); // Arrêter la caméra quand on affiche la liste
            },

            startNewBatch() {
                this.packageList = [];
                this.currentPackage = null;
                this.manualCode = '';
                this.totalPackages = 0;
                this.totalCOD = 0;
                this.batchStartTime = Date.now();
                this.showPackageList = false;

                // Redémarrer la caméra
                setTimeout(() => this.startCamera(), 300);
            },

            async submitBatch() {
                try {
                    const response = await fetch('/deliverer/packages/batch-submit', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            packages: this.packageList.map(item => ({
                                code: item.code,
                                timestamp: item.timestamp
                            })),
                            total_packages: this.totalPackages,
                            total_cod: this.totalCOD
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.playBeep(1000, 300, 'success');
                        alert(`Lot validé avec succès! ${this.totalPackages} colis traités.`);
                        this.startNewBatch();
                    } else {
                        this.playBeep(400, 300, 'error');
                        alert('Erreur lors de la validation du lot.');
                    }
                } catch (error) {
                    console.error('Erreur soumission lot:', error);
                    this.playBeep(400, 300, 'error');
                    alert('Erreur de connexion lors de la validation.');
                }
            },

            // Gestion de la liste de scan batch (legacy - à supprimer)
            addToScanList(code, result) {
                // Vérifier si le code n'est pas déjà dans la liste
                const existingIndex = this.scanList.findIndex(item => item.code === code);

                if (existingIndex !== -1) {
                    // Code déjà scanné, jouer un son d'avertissement
                    this.playBeep(300, 200, 'error');
                    console.log('Code déjà scanné:', code);
                    return;
                }

                // Un code est considéré comme valide s'il est lié à un colis (result.success ET result.package)
                const isValidPackage = result.success && result.package;

                const scanItem = {
                    code: code,
                    result: result,
                    timestamp: Date.now(),
                    success: isValidPackage, // Basé sur la présence d'un colis
                    package: result.package || null,
                    message: result.message || (isValidPackage ? 'Colis trouvé' : 'Code non lié à un colis')
                };

                this.scanList.push(scanItem);
                this.updateScanStats();

                // Son selon le résultat
                if (isValidPackage) {
                    this.playBeep(800, 150, 'added'); // Son de succès pour colis trouvé
                    console.log(`✅ Code valide ajouté: ${code} - Colis trouvé`);
                } else {
                    this.playBeep(400, 200, 'error'); // Son d'erreur pour code non lié
                    console.log(`❌ Code invalide ajouté: ${code} - Pas de colis associé`);
                }
            },

            updateScanStats() {
                this.totalScanned = this.scanList.length;
                this.validScanned = this.scanList.filter(item => item.success).length;
                this.invalidScanned = this.scanList.filter(item => !item.success).length;
            },

            toggleBatchMode() {
                this.batchScanMode = !this.batchScanMode;

                if (this.batchScanMode) {
                    console.log('Mode batch activé');
                    this.scanList = [];
                    this.updateScanStats();
                    this.playBeep(600, 200, 'success'); // Son d'activation
                } else {
                    console.log('Mode batch désactivé');
                    this.playBeep(400, 150, 'scan'); // Son de désactivation
                }
            },

            showBatchSummary() {
                if (this.packageList.length === 0) {
                    this.playBeep(400, 200, 'error');
                    return;
                }
                this.batchSummaryVisible = true;
                this.playBeep(600, 500, 'batch-complete');
            },

            closeBatchSummary() {
                this.batchSummaryVisible = false;
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('fr-DZ', {
                    style: 'currency',
                    currency: 'DZD',
                    minimumFractionDigits: 0
                }).format(amount || 0);
            },

            selectRecentCode(code) {
                this.currentCode = code;
                this.validateCode();
            },

            formatTime(timestamp) {
                return new Date(timestamp).toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            clearRecentCodes() {
                this.recentCodes = [];
                try {
                    localStorage.removeItem('scanner_recent_codes');
                } catch (error) {
                    console.error('Erreur suppression localStorage:', error);
                }
            },

            validateBatch() {
                // Traitement final de la liste
                console.log('Validation du batch:', this.scanList);

                // Ici vous pouvez envoyer la liste complète au serveur
                this.processBatchList();
            },

            async processBatchList() {
                try {
                    const response = await fetch('/deliverer/packages/batch-scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            codes: this.scanList.map(item => ({
                                code: item.code,
                                success: item.success,
                                timestamp: item.timestamp
                            }))
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.playBeep(1000, 300, 'success');
                        alert(`Batch validé avec succès! ${this.validScanned} codes valides traités.`);
                        this.resetBatch();
                    } else {
                        this.playBeep(400, 300, 'error');
                        alert('Erreur lors de la validation du batch.');
                    }
                } catch (error) {
                    console.error('Erreur validation batch:', error);
                    this.playBeep(400, 300, 'error');
                    alert('Erreur de connexion lors de la validation.');
                }
            },

            resetBatch() {
                this.scanList = [];
                this.updateScanStats();
                this.showScanList = false;
                this.batchScanMode = false;
            },

            removeScanItem(index) {
                this.scanList.splice(index, 1);
                this.updateScanStats();
                this.playBeep(300, 100, 'scan');
            },

            addToRecent(code) {
                const item = { value: code, timestamp: Date.now() };
                this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10);

                try {
                    localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
                } catch (error) {
                    console.error('Erreur sauvegarde localStorage:', error);
                }
            },

            switchMode(mode) {
                this.activeMode = mode;

                if (mode === 'manual') {
                    this.stopCamera();
                    // Focus sur le champ manuel après le changement
                    this.$nextTick(() => {
                        if (this.$refs.manualInput) {
                            this.$refs.manualInput.focus();
                        }
                    });
                }
            },

            // Validation et recherche manuelle
            validateCode() {
                this.codeValid = this.manualCode && this.manualCode.trim().length > 0;
            },

            async searchCode() {
                if (!this.manualCode.trim()) return;

                const code = this.manualCode.trim().toUpperCase();
                await this.processCode(code);
            },

            closeResult() {
                this.resultVisible = false;
                this.result = {
                    success: false,
                    message: '',
                    package: null,
                    delivery_info: null,
                    redirect: false
                };
                this.manualCode = ''; // Reset du code manuel
                this.currentCode = ''; // Reset du code actuel
                this.currentPackage = null;

                // Reprendre le scan si la caméra est active
                if (this.cameraActive) {
                    // Reset complet des variables de scan
                    this.lastDetection = null;
                    this.lastDetectionTime = 0;
                    this.consecutiveFailures = 0;

                    // Reprendre le scan immédiatement
                    this.scanEnabled = true;

                    // Redémarrer le scan s'il n'est pas actif
                    if (!this.scanInterval) {
                        setTimeout(() => {
                            this.startScanning();
                        }, 200);
                    }

                    console.log('Scan repris après fermeture du résultat');
                }
            },

            // Méthodes helper pour les templates
            getStatusText() {
                return this.cameraActive ? 'Caméra active' : 'Caméra inactive';
            },

            getScanModeText() {
                return this.scanMode === 'single' ? 'Scan unique' : 'Scan par lot';
            },

            getStatusIndicatorClass() {
                return this.cameraActive
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-red-100 text-red-800';
            },

            getAspectRatio() {
                return this.isMobile ? '4/3' : '16/9';
            },

            getCurrentResolution() {
                if (!this.$refs.videoElement || !this.$refs.videoElement.videoWidth) {
                    return 'N/A';
                }
                return `${this.$refs.videoElement.videoWidth}x${this.$refs.videoElement.videoHeight}`;
            },

            getCurrentFPS() {
                return '30'; // Approximation
            },

            getActionLabel() {
                return 'Voir Détails';
            },

            goToPackage() {
                if (this.result?.redirect) {
                    window.location.href = this.result.redirect;
                }
            },

            // Gestion des données stockées
            loadStoredData() {
                try {
                    this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
                    this.scanHistory = [];
                } catch {
                    this.recentCodes = [];
                }
            },

            // Fonctions de caméra avancées
            toggleTorch() {
                if (!this.stream || !this.isMobile) return;

                const track = this.stream.getVideoTracks()[0];
                if (track && track.getCapabilities && track.getCapabilities().torch) {
                    track.applyConstraints({
                        advanced: [{ torch: !this.torchEnabled }]
                    }).then(() => {
                        this.torchEnabled = !this.torchEnabled;
                    }).catch(console.error);
                }
            },

            switchCamera() {
                if (!this.isMobile) return;

                // Changer entre caméra avant et arrière
                const currentFacing = this.isMobile ? 'environment' : 'user';
                const newFacing = currentFacing === 'environment' ? 'user' : 'environment';

                this.stopCamera();
                setTimeout(() => {
                    this.startCamera();
                }, 500);
            },

            adjustScanQuality() {
                // Redémarrer la caméra avec la nouvelle qualité
                if (this.cameraActive) {
                    this.stopCamera();
                    setTimeout(() => {
                        this.startCamera();
                    }, 500);
                }
            },

            goToPackage() {
                // Placeholder pour redirection
            },

            processBatchCodes() {
                // Placeholder pour batch processing
            },

            // Fonction helper manquantes
            formatTime(timestamp) {
                return new Date(timestamp).toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            clearRecentCodes() {
                this.recentCodes = [];
                if (window.ScannerUtils && window.ScannerUtils.Storage) {
                    window.ScannerUtils.Storage.clearRecentCodes();
                }
            },

            removeRecentCode(code) {
                this.recentCodes = this.recentCodes.filter(item => item.value !== code);
                if (window.ScannerUtils && window.ScannerUtils.Storage) {
                    window.ScannerUtils.Storage.removeRecentCode(code);
                }
            },

            selectRecentCode(code) {
                this.manualCode = code;
                this.validateCode();
                if (this.$refs.manualInput) {
                    this.$refs.manualInput.focus();
                }
            },

            clearBatch() {
                this.scannedCodes = [];
            },

            removeBatchCode(index) {
                this.scannedCodes.splice(index, 1);
            },

            closeResult() {
                this.resultVisible = false;
                this.result = {};
            },

            // Fonctions pour batch scanner
            openBatchScanner(options = {}) {
                this.visible = true;
                this.scanMode = 'batch';
                this.batchScanMode = true;
            },

            closeBatchScanner() {
                this.visible = false;
                this.scanMode = 'single';
                this.batchScanMode = false;
            },

            // Fonctions pour code input
            useRecentCode(code) {
                this.manualCode = code;
                this.currentCode = code;
                this.validateCode();
            },

            getCodeSuggestions() {
                if (window.codeInputHelpers && window.codeInputHelpers.getCodeSuggestions) {
                    return window.codeInputHelpers.getCodeSuggestions(this.manualCode || this.currentCode);
                }
                return [];
            },

            clearRecentCodes() {
                if (window.codeInputHelpers && window.codeInputHelpers.clearRecentCodes) {
                    window.codeInputHelpers.clearRecentCodes.call(this);
                }
            },

            removeRecentCode(code) {
                if (window.codeInputHelpers && window.codeInputHelpers.removeRecentCode) {
                    window.codeInputHelpers.removeRecentCode.call(this, code);
                }
            },

            // Fonctions manquantes pour les templates
            formatCurrency(amount) {
                if (!amount || amount === 0) return '0 DA';
                return new Intl.NumberFormat('fr-DZ', {
                    style: 'currency',
                    currency: 'DZD',
                    minimumFractionDigits: 0
                }).format(amount);
            },

            getActionLabel() {
                if (this.result && this.result.redirect) {
                    return 'Continuer';
                }
                return 'Fermer';
            },

            showBatchSummary() {
                this.batchSummaryVisible = true;
            },

            hideBatchSummary() {
                this.batchSummaryVisible = false;
            },

            selectRecentCode(code) {
                this.currentCode = code;
                this.manualCode = code;
                this.validateCode();
            },

            clearRecentCodes() {
                this.recentCodes = [];
                localStorage.removeItem('scanner_recent_codes');
            },

            // Methods from working scanner for template compatibility
            useRecentCode(code) {
                this.currentCode = code;
                this.manualCode = code;
                this.validateCode();
            },

            removeRecentCode(code) {
                this.recentCodes = this.recentCodes.filter(item => item.value !== code);
                localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
            },

            formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            getCodeSuggestions() {
                if (!this.manualCode || this.manualCode.length < 3) return [];

                const suggestions = [];
                const upper = this.manualCode.toUpperCase();

                // Suggestion basée sur le pattern PKG_
                if (upper.startsWith('PKG') && !upper.includes('_')) {
                    const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                    suggestions.push(`PKG_${upper.substring(3).padEnd(8, '0').slice(0, 8)}_${today}`);
                }

                // Si commence par PKG_000
                if (upper.startsWith('PKG_000')) {
                    const remaining = upper.substring(7);
                    if (remaining.length <= 3) {
                        suggestions.push(`PKG_${('000' + remaining).slice(-3).padEnd(6, '0')}`);
                    }
                }

                return [...new Set(suggestions)];
            },

            // Additional helper methods for template compatibility
            closeResult() {
                this.resultVisible = false;
                this.result = {
                    success: false,
                    message: '',
                    package: null,
                    delivery_info: null,
                    redirect: false
                };
            },

            resetScanner() {
                this.currentCode = '';
                this.manualCode = '';
                this.currentPackage = null;
                this.resultVisible = false;
                this.searching = false;
            },

            // Mode switching functionality
            toggleMode(mode) {
                this.activeMode = mode;
                if (mode === 'camera') {
                    setTimeout(() => this.startCamera(), 100);
                } else {
                    this.stopCamera();
                    setTimeout(() => {
                        const input = this.$refs.manualInput;
                        if (input) input.focus();
                    }, 100);
                }
            }
        }
    };
    </script>
    
    <script defer>
        // Configuration étendue de Tailwind CSS
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#F7EEFF', 100: '#F0E7FF', 200: '#E9D8FD', 300: '#D6C2F0',
                            400: '#B794E6', 500: '#A78BFA', 600: '#8B5CF6', 700: '#6D28D9',
                            800: '#553C9A', 900: '#44337A'
                        },
                        neutral: {
                            50: '#F8F9FA', 100: '#F1F3F5', 200: '#E9ECEF', 300: '#DEE2E6',
                            400: '#CED4DA', 500: '#ADB5BD', 600: '#868E96', 700: '#495057',
                            800: '#343A40', 900: '#212529'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Variables CSS pour les zones de sécurité (safe area) sur iOS */
        :root {
            --safe-area-inset-top: env(safe-area-inset-top);
            --safe-area-inset-bottom: env(safe-area-inset-bottom);
        }
        
        /* Styles de base */
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .safe-top { padding-top: var(--safe-area-inset-top); }
        .safe-bottom { padding-bottom: var(--safe-area-inset-bottom); }
        .content-safe-top { margin-top: var(--safe-area-inset-top); }
        
        /* Amélioration de la barre de défilement */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--primary-purple-medium); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--primary-purple-dark); }
        
        /* Animation pour les notifications */
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        }
        .notification-badge-pulse { animation: pulse-badge 1.5s infinite; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-neutral-50 font-sans antialiased text-neutral-800" x-data="delivererApp()" @online.window="setOnlineStatus(true)" @offline.window="setOnlineStatus(false)">
    
    <div class="fixed top-0 left-0 right-0 z-50 safe-top space-y-2 p-3">
        <div x-show="!isOnline" x-transition class="flex items-center justify-center space-x-2 bg-neutral-800 text-white p-2 rounded-lg shadow-md">
            <svg class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-sm font-medium">Mode hors ligne actif</span>
        </div>
        
        <div x-show="showInstallBanner" x-transition class="bg-white p-3 rounded-lg shadow-lg border border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-primary-500 text-white w-10 h-10 flex items-center justify-center rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-gray-800">Installer l'application</p>
                        <p class="text-xs text-gray-500">Pour un accès rapide et hors ligne.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="installPWA()" class="bg-primary-500 text-white px-3 py-1 rounded-md text-sm font-semibold hover:bg-primary-600 transition-colors">Installer</button>
                    <button @click="showInstallBanner = false" class="text-neutral-400 p-1 hover:text-neutral-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <header class="sticky top-0 bg-gray-50/80 backdrop-blur-sm z-20 safe-top content-safe-top" :class="{ 'pt-24': showInstallBanner, 'pt-14': !isOnline && !showInstallBanner }">
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-neutral-500 hover:text-primary-600 transition-colors md:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-gray-500">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="hidden sm:flex items-center space-x-1 bg-green-100 text-green-800 px-3 py-1.5 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-sm font-semibold" x-text="walletBalance || '0.000 DT'"></span>
                    </div>
                    <button @click="showNotifications = !showNotifications" class="relative p-2 text-neutral-500 hover:text-primary-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                        <span x-show="notificationsCount > 0" class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-neutral-50"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <div x-show="sidebarOpen" @click.away="sidebarOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>
    <aside x-show="sidebarOpen" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed left-0 top-0 bottom-0 w-72 bg-white z-50 flex flex-col custom-scrollbar overflow-y-auto safe-top safe-bottom">
        <div class="p-5 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Al-Amena Livreur</h2>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-400 p-2 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        
        <nav class="flex-grow p-3 space-y-1">
            @php
                $navItems = [
                    ['route' => 'deliverer.dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>', 'stat' => null],

                    ['divider' => true, 'label' => 'Scanner'],
                    ['route' => 'deliverer.scan.simple', 'label' => '📱 Scan Simple', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2m-6 0h-2m0 0h-.01m0 0h.01m0 0h-.01"/>', 'color' => 'bg-purple-500'],
                    ['route' => 'deliverer.scan.lot', 'label' => '📦 Scan en Lot', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"/>', 'color' => 'bg-indigo-500'],

                    ['divider' => true, 'label' => 'Collectes & Livraisons'],
                    ['route' => 'deliverer.pickups.index', 'label' => 'Demandes Collecte', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'stat' => 'stats.available_pickups', 'color' => 'bg-blue-500'],
                    ['route' => 'deliverer.packages.my-pickups', 'label' => 'Mes Collectes', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'stat' => 'stats.my_pickups', 'color' => 'bg-green-500'],
                    ['route' => 'deliverer.packages.index', 'label' => 'Tous les Colis', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'color' => 'bg-gray-500'],
                    ['route' => 'deliverer.deliveries.index', 'label' => 'Mes Livraisons', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>', 'stat' => 'stats.deliveries', 'color' => 'bg-orange-500'],
                    ['route' => 'deliverer.deliveries.single', 'label' => '⚡ Livraison Rapide', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>', 'stat' => 'stats.deliveries', 'color' => 'bg-gradient-to-r from-red-500 to-pink-500'],
                    ['route' => 'deliverer.packages.returns', 'label' => 'Retours', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>', 'stat' => 'stats.returns', 'color' => 'bg-purple-500'],

                    ['divider' => true, 'label' => 'Finances'],
                    ['route' => 'deliverer.wallet.index', 'label' => 'Mon Wallet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                    ['route' => 'deliverer.wallet.history', 'label' => 'Historique Wallet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'color' => 'bg-indigo-500'],
                    ['route' => 'deliverer.payments.index', 'label' => 'Paiements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'stat' => 'stats.payments', 'color' => 'bg-teal-500'],
                    ['route' => 'deliverer.client-topup.index', 'label' => 'Recharge Client', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>', 'color' => 'bg-emerald-500'],

                    ['divider' => true, 'label' => 'Outils & Support'],
                    ['route' => 'deliverer.runsheets.index', 'label' => 'Feuilles de Route', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'color' => 'bg-cyan-500'],
                    ['route' => 'deliverer.profile.show', 'label' => 'Mon Profil', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>', 'color' => 'bg-violet-500'],
                    ['route' => 'deliverer.notifications.index', 'label' => 'Notifications', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 17V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h8z"/>', 'stat' => 'notificationsCount', 'color' => 'bg-red-500'],
                    ['route' => 'deliverer.help.index', 'label' => 'Aide & Support', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'bg-amber-500'],
                ];
            @endphp
            @foreach ($navItems as $item)
                @if (isset($item['divider']))
                    <div class="pt-3 pb-2 px-3">
                        <div class="border-t border-gray-200"></div>
                        @if (isset($item['label']))
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-3">{{ $item['label'] }}</div>
                        @endif
                    </div>
                @else
                    <a href="{{ route($item['route']) }}" class="flex items-center px-3 py-2.5 rounded-lg transition-colors group relative {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-primary-50 text-primary-600' : 'text-neutral-600 hover:bg-neutral-100' }}">
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-r-full {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-primary-500' : '' }}"></div>
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span class="font-medium text-sm">{{ $item['label'] }}</span>
                        @if (isset($item['stat']))
                            <span x-show="{{ $item['stat'] }} > 0" x-text="{{ $item['stat'] }}" class="ml-auto text-white text-xs font-semibold px-2 py-0.5 rounded-full {{ $item['color'] }}"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>
        
        <div class="p-3 mt-auto">
            <div class="p-3 bg-gray-100 rounded-lg">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Livreur</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('deliverer.profile.show') }}" class="flex items-center w-full px-3 py-2 text-sm text-neutral-600 hover:bg-white rounded-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Mon Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
    
    <main class="min-h-screen pb-24 md:ml-64">
        <div class="p-4 sm:p-6">
            @yield('content')
        </div>
    </main>
    
    <nav class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm border-t border-neutral-200 safe-bottom z-30">
        <div class="grid grid-cols-5 gap-1 max-w-md mx-auto px-2 py-1.5">
            @php
                $bottomNavItems = [
                    ['route' => 'deliverer.dashboard', 'match' => 'deliverer.dashboard', 'label' => 'Accueil', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>'],
                    ['route' => 'deliverer.pickups.index', 'match' => 'deliverer.pickups.*', 'label' => 'Collectes', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'stat' => 'stats.available_pickups'],
                    ['route' => 'deliverer.scan.simple', 'match' => 'deliverer.scan.*', 'label' => 'Scanner', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>'],
                    ['route' => 'deliverer.packages.index', 'match' => 'deliverer.packages.*|deliverer.deliveries.*', 'label' => 'Colis', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>', 'stat' => 'stats.deliveries'],
                    ['route' => 'deliverer.wallet.index', 'match' => 'deliverer.wallet.*|deliverer.payments.*', 'label' => 'Wallet', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>']
                ];
            @endphp
            @foreach ($bottomNavItems as $item)
                @if (isset($item['type']) && $item['type'] === 'button')
                    <div class="flex items-center justify-center">
                        <button @click="{{ $item['action'] }}" class="relative -top-4 flex flex-col items-center justify-center p-3 text-white bg-primary-600 rounded-full shadow-lg hover:bg-primary-700 transition-transform hover:scale-105">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        </button>
                    </div>
                @else
                    @php
                        $isActive = request()->routeIs($item['match'] ?? $item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}" class="flex flex-col items-center justify-center py-1 px-1 rounded-lg relative transition-colors {{ $isActive ? 'text-primary-600' : 'text-neutral-500 hover:bg-primary-50' }}">
                        <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span class="text-[11px] font-medium">{{ $item['label'] }}</span>
                        @if (isset($item['stat']))
                            <span x-show="{{ $item['stat'] }} > 0" class="absolute top-0 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                        @endif
                        @if ($isActive)
                            <div class="absolute bottom-0 h-0.5 w-4 bg-primary-600 rounded-full"></div>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
    
    
    <div x-show="showNotifications" @click.away="showNotifications = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-50">
        <div @click.stop class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-xl flex flex-col safe-top safe-bottom" x-show="showNotifications" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-lg">Notifications</h3>
                    <button @click="showNotifications = false" class="p-1 text-neutral-400 hover:text-neutral-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-grow p-2 custom-scrollbar overflow-y-auto" x-html="notificationsList">
                <div class="text-center text-neutral-500 py-16">
                    <svg class="w-12 h-12 mx-auto mb-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                    <p class="font-medium">Aucune notification pour le moment</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="fixed bottom-20 sm:bottom-4 right-4 z-50 space-y-2">
        @if(session('success'))
            <div x-data="{ show: false }" x-init="() => { show = true; setTimeout(() => show = false, 5000); }" x-show="show" x-transition class="flex items-start space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg max-w-xs">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
                <button @click="show = false" class="ml-auto -mr-1 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div x-data="{ show: false }" x-init="() => { show = true; setTimeout(() => show = false, 7000); }" x-show="show" x-transition class="flex items-start space-x-3 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg max-w-xs">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-medium">
                    @if(session('error')) {{ session('error') }} @endif
                    @if($errors->any()) @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach @endif
                </div>
                <button @click="show = false" class="ml-auto -mr-1 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
    </div>
    
    @stack('modals')
    
    <script defer>
        function delivererApp() {
            return {
                sidebarOpen: false, showNotifications: false, showInstallBanner: false,
                isOnline: navigator.onLine, deferredPrompt: null,
                stats: { available_pickups: 0, my_pickups: 0, deliveries: 0, returns: 0, payments: 0, urgent_deliveries: 0 },
                walletBalance: '0.000 DT', notificationsCount: 0, notificationsList: '',

                // Scanner variables for template compatibility
                packageList: [],
                totalPackages: 0,
                totalCOD: 0,
                resultVisible: false,
                result: {
                    success: false,
                    message: '',
                    package: null,
                    delivery_info: null,
                    redirect: false
                },
                batchSummaryVisible: false,
                autoRedirect: true,
                
                init() {
                    this.fetchData();
                    this.setupPWA();
                    setInterval(() => this.isOnline && this.fetchData(), 30000);
                    setInterval(() => navigator.serviceWorker?.controller?.postMessage({ type: 'CHECK_UPDATE' }), 300000);
                    window.addEventListener('online', () => this.setOnlineStatus(true));
                    window.addEventListener('offline', () => this.setOnlineStatus(false));
                },

                async fetchData() {
                    await Promise.all([
                        this.loadStats(),
                        this.loadNotifications(),
                        this.loadWalletBalance()
                    ]);
                },
                
                async apiCall(url, errorMsg) {
                    try {
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Network response was not ok');
                        return await response.json();
                    } catch (error) {
                        console.warn(`${errorMsg}:`, error);
                        return null;
                    }
                },

                async loadStats() {
                    const data = await this.apiCall('/deliverer/api/dashboard-stats', 'Stats offline');
                    if (data) this.stats = data;
                },
                
                async loadWalletBalance() {
                    const data = await this.apiCall('/deliverer/api/wallet/balance', 'Wallet offline');
                    if (data) this.walletBalance = data.formatted_balance;
                },
                
                async loadNotifications() {
                    const countData = await this.apiCall('/deliverer/api/notifications/unread-count', 'Notifications count offline');
                    if (countData) this.notificationsCount = countData.count;
                    
                    const recentData = await this.apiCall('/deliverer/api/notifications/recent', 'Recent notifications offline');
                    if (recentData) this.updateNotificationsList(recentData);
                },
                
                updateNotificationsList(notifications) {
                    // Ensure notifications is an array
                    if (!Array.isArray(notifications) || notifications.length === 0) {
                        this.notificationsList = `
                            <div class="text-center text-neutral-500 py-16 flex flex-col items-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5-5 5h5zm0-8h5l-5-5-5 5h5z"/></svg>
                                <p class="font-medium text-sm">Aucune notification pour le moment</p>
                            </div>`;
                        return;
                    }
                    this.notificationsList = notifications.map(n => `
                        <div class="p-3 mb-2 rounded-lg transition-colors ${!n.read ? 'bg-primary-50' : 'bg-gray-50'}">
                            <div class="flex items-start space-x-3">
                                <div class="w-1.5 h-1.5 rounded-full mt-1.5 ${n.priority_color || 'bg-gray-400'}"></div>
                                <div class="flex-1">
                                    <p class="font-semibold text-sm text-neutral-800">${n.title}</p>
                                    <p class="text-sm text-neutral-600 mt-1">${n.message}</p>
                                    <p class="text-xs text-neutral-400 mt-2">${n.created_at_human}</p>
                                </div>
                            </div>
                        </div>`).join('');
                },
                
                setOnlineStatus(status) {
                    this.isOnline = status;
                    if (status) {
                        this.fetchData();
                        navigator.serviceWorker?.ready.then(reg => reg.sync?.register('offline-actions-sync'));
                    }
                },
                
                setupPWA() {
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.showInstallBanner = true;
                    });
                    
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js', { scope: '/deliverer/' })
                            .then(reg => {
                                reg.addEventListener('updatefound', () => {
                                    const newWorker = reg.installing;
                                    newWorker.addEventListener('statechange', () => {
                                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                            this.showUpdateAvailable();
                                        }
                                    });
                                });
                            }).catch(error => console.log('SW registration failed', error));
                    }
                },
                
                async installPWA() {
                    if (!this.deferredPrompt) return;
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') console.log('PWA installed');
                    this.deferredPrompt = null;
                    this.showInstallBanner = false;
                },
                
                showUpdateAvailable() {
                    const updateBanner = document.createElement('div');
                    updateBanner.className = 'fixed bottom-24 sm:bottom-20 right-4 bg-blue-600 text-white p-3 rounded-lg shadow-lg z-50';
                    updateBanner.innerHTML = `<div class="flex items-center justify-between"><div class="mr-4"><p class="font-semibold text-sm">Mise à jour disponible</p><p class="text-xs opacity-90">Redémarrez pour l'installer.</p></div><button onclick="window.location.reload()" class="bg-white text-blue-600 px-3 py-1 rounded-md text-sm font-semibold">Redémarrer</button></div>`;
                    document.body.appendChild(updateBanner);
                },

                // Functions for template compatibility
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-DZ', {
                        style: 'currency',
                        currency: 'DZD',
                        minimumFractionDigits: 0
                    }).format(amount || 0);
                },

                getActionLabel() {
                    if (this.result && this.result.redirect) {
                        return 'Continuer';
                    }
                    return 'Fermer';
                },

                showBatchSummary() {
                    this.batchSummaryVisible = true;
                },

                hideBatchSummary() {
                    this.batchSummaryVisible = false;
                }
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                const shortcuts = { 's': 'open-scanner', 'd': '/deliverer/dashboard', 'w': '/deliverer/wallet', 'l': '/deliverer/deliveries' };
                if (shortcuts[e.key]) {
                    e.preventDefault();
                    if (e.key === 's') document.dispatchEvent(new CustomEvent('open-scanner', { detail: { mode: 'single' } }));
                    else window.location.href = shortcuts[e.key];
                }
            }
        });

        // Enregistrement du composant QR Scanner dans Alpine.js
        document.addEventListener('alpine:init', () => {
            Alpine.data('delivererQRScanner', delivererQRScanner);
        });
    </script>

    @stack('scripts')
</body>
</html>