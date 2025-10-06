function scannerQRFinal() {
    return {
        scannerVisible: false,
        activeMode: 'manual',
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        isHttps: location.protocol === 'https:',
        cameraActive: false,
        cameraErrorMsg: '',
        videoStream: null,
        scanInterval: null,
        lastDetection: null,
        permissionAsked: false,
        scanMode: 'barcode',
        scanCycle: 0,
        manualCode: '',
        codeValid: false,
        searching: false,
        recentCodes: [],
        result: {},
        resultVisible: false,
        scanHistory: [],
        
        init() {
            this.loadStoredData();
            if (this.isMobile && !this.isHttps) {
                this.activeMode = 'manual';
            }
            this.$watch('manualCode', () => this.validateCode());
        },
        
        openScanner() {
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
            if (mode === 'camera' && this.isMobile && !this.isHttps) {
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
        
        async requestCameraPermission() {
            this.permissionAsked = true;
            this.cameraErrorMsg = '';
            if (this.isMobile && !this.isHttps) {
                this.cameraErrorMsg = 'HTTPS requis pour la caméra sur mobile.';
                return;
            }
            try {
                if (!navigator.mediaDevices?.getUserMedia) {
                    throw new Error('getUserMedia non supporté');
                }
                await this.startCamera();
            } catch (error) {
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
            }
        },
        
        async startCamera() {
            this.cameraErrorMsg = '';
            try {
                this.stopCamera();
                const constraints = {
                    video: {
                        width: { min: 640, ideal: this.isMobile ? 1280 : 1920 },
                        height: { min: 480, ideal: this.isMobile ? 720 : 1080 },
                        frameRate: { min: 15, ideal: 30 }
                    }
                };
                if (this.isMobile) {
                    constraints.video.facingMode = { exact: "environment" };
                }
                this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = this.$refs.videoElement;
                if (!video) {
                    throw new Error('Élément vidéo non trouvé');
                }
                video.srcObject = this.videoStream;
                await new Promise((resolve, reject) => {
                    video.onloadedmetadata = () => {
                        resolve();
                    };
                    video.onerror = reject;
                    setTimeout(() => reject(new Error('Timeout chargement vidéo')), 10000);
                });
                this.cameraActive = true;
                this.startScanning();
            } catch (error) {
                this.cameraErrorMsg = this.getCameraErrorMessage(error);
                this.stopCamera();
            }
        },
        
        stopCamera() {
            this.stopScanning();
            if (this.videoStream) {
                this.videoStream.getTracks().forEach(track => track.stop());
                this.videoStream = null;
            }
            if (this.$refs.videoElement) {
                this.$refs.videoElement.srcObject = null;
            }
            this.cameraActive = false;
        },
        
        retryCamera() {
            this.cameraErrorMsg = '';
            this.requestCameraPermission();
        },
        
        getCameraErrorMessage(error) {
            const msg = error.message || error.toString();
            if (msg.includes('Permission denied') || msg.includes('NotAllowedError')) {
                return 'Permission refusée. Autorisez la caméra dans les paramètres.';
            }
            if (msg.includes('NotFoundError')) {
                return 'Aucune caméra trouvée.';
            }
            if (msg.includes('NotReadableError')) {
                return 'Caméra déjà utilisée.';
            }
            return 'Erreur caméra. Réessayez ou utilisez le mode manuel.';
        },
        
        startScanning() {
            this.initQuaggaScanner();
            this.startAlternatingScans();
        },
        
        startAlternatingScans() {
            this.scanInterval = setInterval(() => {
                this.scanCycle++;
                if (this.scanCycle % 3 === 0) {
                    this.scanMode = 'qr';
                    this.analyzeQRFrame();
                } else {
                    this.scanMode = 'barcode';
                }
            }, 500);
        },
        
        stopScanning() {
            if (this.scanInterval) {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
            }
            this.stopQuaggaScanner();
            this.scanCycle = 0;
            this.scanMode = 'barcode';
        },
        
        initQuaggaScanner() {
            if (typeof Quagga === 'undefined') {
                return;
            }
            try {
                const video = this.$refs.videoElement;
                if (!video) return;
                
                Quagga.init({
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
                        readers: ["code_128_reader", "ean_reader", "code_39_reader"]
                    },
                    locate: true,
                    debug: false
                }, (err) => {
                    if (err) {
                        return;
                    }
                    Quagga.start();
                });
                
                Quagga.onDetected((result) => {
                    if (result?.codeResult?.code && this.scanMode === 'barcode') {
                        const code = result.codeResult.code.trim();
                        if (this.isValidPackageCode(code)) {
                            this.onCodeDetected(code, 'BARCODE');
                        }
                    }
                });
            } catch (error) {}
        },
        
        stopQuaggaScanner() {
            try {
                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                }
            } catch (error) {}
        },
        
        analyzeQRFrame() {
            try {
                const video = this.$refs.videoElement;
                const canvas = this.$refs.canvasElement;
                if (!video || !canvas || !video.videoWidth) {
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                ctx.drawImage(video, 0, 0);
                
                if (typeof jsQR !== 'undefined') {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const qrResult = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert"
                    });
                    
                    if (qrResult?.data) {
                        const code = qrResult.data.trim();
                        if (this.isValidCode(code)) {
                            this.onCodeDetected(code, 'QR');
                        }
                    }
                }
            } catch (error) {}
        },
        
        onCodeDetected(code, type) {
            const now = Date.now();
            const normalizedCode = this.normalizePackageCode(code);
            
            if (this.lastDetection && (now - this.lastDetection.time < 2000) && 
                this.lastDetection.code === normalizedCode) {
                return;
            }
            
            this.lastDetection = { code: normalizedCode, time: now, type };
            this.stopCamera();
            
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
            
            this.processCode(normalizedCode);
        },
        
        normalizePackageCode(input) {
            return window.extractCodeFromUrl ? 
                window.extractCodeFromUrl(input) : 
                (input || '').toString().trim().toUpperCase();
        },
        
        isValidPackageCode(input) {
            return window.isValidPackageCode ? 
                window.isValidPackageCode(input) : 
                true;
        },
        
        isValidCode(code) {
            return this.isValidPackageCode(code);
        },
        
        validateCode() {
            this.codeValid = this.isValidPackageCode(this.manualCode);
        },
        
        searchCode() {
            if (!this.codeValid || this.searching) return;
            this.processCode(this.normalizePackageCode(this.manualCode));
        },
        
        useRecentCode(code) {
            this.manualCode = code;
            this.validateCode();
            this.processCode(code);
        },
        
        async processCode(code) {
            this.searching = true;
            try {
                this.addToRecent(code);
                
                const response = await fetch('/deliverer/packages/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                });
                
                const data = await response.json();
                this.showResult(data);
            } catch (error) {
                this.showResult({ success: false, message: 'Erreur de connexion.' });
            }
            this.searching = false;
        },
        
        showResult(data) {
            this.result = data;
            this.resultVisible = true;
            
            if (data.success && data.redirect) {
                setTimeout(() => {
                    this.goToPackage();
                }, 5000);
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
        
        loadStoredData() {
            try {
                this.recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
                this.scanHistory = [];
            } catch {
                this.recentCodes = [];
            }
        },
        
        addToRecent(code) {
            const item = { value: code, timestamp: Date.now() };
            this.recentCodes = [item, ...this.recentCodes.filter(c => c.value !== code)].slice(0, 10);
            try {
                localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
            } catch (error) {}
        },
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },
    }
}
