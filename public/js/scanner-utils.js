/**
 * Utilitaires pour le scanner QR/Code-barres
 * Fonctions communes utilisées dans toute l'application
 */

window.ScannerUtils = {
    /**
     * Valide un code de colis
     */
    isValidPackageCode(code) {
        if (!code || code.length < 3) return false;

        const cleanCode = code.trim().toUpperCase();

        // URLs de tracking
        if (/^https?:\/\/.*\/track\//.test(cleanCode)) return true;
        if (/^https?:\/\/.*packages\//.test(cleanCode)) return true;

        // Codes PKG_
        if (/^PKG_/.test(cleanCode)) return true;

        // Codes alphanumériques
        if (/^[A-Z0-9]{6,}$/.test(cleanCode)) return true;

        // Codes numériques longs
        if (/\d{8,}/.test(cleanCode)) return true;

        // Exclure les mots évidents qui ne sont pas des codes
        const obviousWords = ['LIVRAISON', 'DELIVERY', 'BON DE', 'SERVICE', 'CONTACT', 'TELEPHONE', 'ADRESSE', 'DATE', 'HEURE'];
        if (obviousWords.some(word => cleanCode.includes(word) && word.length > 4)) return false;

        return cleanCode.length >= 6;
    },

    /**
     * Normalise un code scanné
     */
    normalizeCode(code) {
        if (!code) return '';

        let normalized = code.trim().toUpperCase();

        // Extraire le code depuis une URL
        const urlMatch = normalized.match(/\/track\/([A-Z0-9_]+)/i);
        if (urlMatch) {
            normalized = urlMatch[1];
        }

        const packageMatch = normalized.match(/packages\/([A-Z0-9_]+)/i);
        if (packageMatch) {
            normalized = packageMatch[1];
        }

        return normalized;
    },

    /**
     * Format le temps depuis un timestamp
     */
    formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Format une date
     */
    formatDate(date) {
        return new Date(date).toLocaleDateString('fr-FR');
    },

    /**
     * Format un montant en dinars tunisiens
     */
    formatAmount(amount) {
        return new Intl.NumberFormat('fr-TN', {
            style: 'currency',
            currency: 'TND',
            minimumFractionDigits: 3
        }).format(amount);
    },

    /**
     * Gestion du stockage local pour les codes récents
     */
    Storage: {
        getRecentCodes() {
            try {
                return JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
            } catch {
                return [];
            }
        },

        addRecentCode(code) {
            const recentCodes = this.getRecentCodes();
            const item = { value: code, timestamp: Date.now() };

            const updated = [
                item,
                ...recentCodes.filter(c => c.value !== code)
            ].slice(0, 10);

            try {
                localStorage.setItem('scanner_recent_codes', JSON.stringify(updated));
            } catch (error) {
                console.error('Erreur sauvegarde localStorage:', error);
            }

            return updated;
        },

        clearRecentCodes() {
            localStorage.removeItem('scanner_recent_codes');
        },

        removeRecentCode(code) {
            const recentCodes = this.getRecentCodes();
            const updated = recentCodes.filter(item => item.value !== code);
            localStorage.setItem('scanner_recent_codes', JSON.stringify(updated));
            return updated;
        }
    },

    /**
     * Feedback haptique et visuel
     */
    Feedback: {
        vibrate(pattern = [100]) {
            if (navigator.vibrate) {
                navigator.vibrate(pattern);
            }
        },

        success() {
            this.vibrate([100]);
        },

        error() {
            this.vibrate([100, 50, 100]);
        },

        showToast(message, type = 'success', duration = 3000) {
            // Crée un toast notification
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm z-50 transition-all transform`;

            const iconClass = type === 'success' ? 'text-green-600' : 'text-red-600';
            const iconPath = type === 'success'
                ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z';

            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="${iconClass}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
                        </svg>
                    </div>
                    <p class="text-gray-900">${message}</p>
                </div>
            `;

            document.body.appendChild(toast);

            // Animation d'entrée
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            }, 10);

            // Suppression automatique
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, duration);
        }
    },

    /**
     * Utilitaires de caméra
     */
    Camera: {
        async getAvailableCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                return devices.filter(device => device.kind === 'videoinput');
            } catch (error) {
                console.error('Erreur énumération caméras:', error);
                return [];
            }
        },

        async hasMultipleCameras() {
            const cameras = await this.getAvailableCameras();
            return cameras.length > 1;
        },

        getConstraints(quality = 'medium', cameraId = null) {
            const qualities = {
                low: { width: 640, height: 480, frameRate: 15 },
                medium: { width: 1280, height: 720, frameRate: 30 },
                high: { width: 1920, height: 1080, frameRate: 30 }
            };

            const constraints = {
                video: {
                    width: { min: 640, ideal: qualities[quality].width },
                    height: { min: 480, ideal: qualities[quality].height },
                    frameRate: { min: 15, ideal: qualities[quality].frameRate }
                }
            };

            if (cameraId) {
                constraints.video.deviceId = { exact: cameraId };
            } else if (this.isMobile()) {
                constraints.video.facingMode = { exact: "environment" };
            }

            return constraints;
        },

        isMobile() {
            return /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        },

        isHttps() {
            return location.protocol === 'https:';
        }
    },

    /**
     * Générateur de code QR simple
     */
    QRCode: {
        generateSimplePattern() {
            return `
                ████ ██ ████
                █  █ ██ █  █
                █ ██ ██ ██ █
                ████ ██ ████
                ██ █ ██ █ ██
                █  █ ██ █  █
                ████ ██ ████
            `.trim();
        }
    },

    /**
     * Utilitaires d'API
     */
    API: {
        async scanPackage(code) {
            const response = await fetch('/deliverer/packages/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: code })
            });

            return await response.json();
        },

        async batchScan(codes) {
            const response = await fetch('/deliverer/packages/scan-batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ codes: codes })
            });

            return await response.json();
        },

        async checkPickup(code) {
            const response = await fetch('/deliverer/packages/check-pickup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: code })
            });

            return await response.json();
        }
    }
};

// Export global pour compatibilité
window.scannerUtils = window.ScannerUtils;