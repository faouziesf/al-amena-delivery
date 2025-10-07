/**
 * PWA Manager - Gestion avancée PWA pour livreurs
 * @version 1.0.0
 */

class PWAManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.installPrompt = null;
        this.toasts = [];
        this.maxToasts = 3;
        
        this.init();
    }

    init() {
        this.setupNetworkListeners();
        this.setupServiceWorkerUpdate();
        this.setupInstallPrompt();
        this.setupToastContainer();
        this.setupHapticFeedback();
        this.checkStorageQuota();
    }

    // ==================== INDICATEUR RÉSEAU ====================
    
    setupNetworkListeners() {
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Vérification initiale
        this.updateNetworkStatus();
    }

    handleOnline() {
        this.isOnline = true;
        this.updateNetworkStatus();
        this.showToast('Connexion rétablie', 'success', 3000);
        this.syncOfflineActions();
    }

    handleOffline() {
        this.isOnline = false;
        this.updateNetworkStatus();
        this.showToast('Mode hors ligne', 'warning', null);
    }

    updateNetworkStatus() {
        const indicator = document.getElementById('network-indicator');
        if (!indicator) this.createNetworkIndicator();
        
        const ind = document.getElementById('network-indicator');
        if (this.isOnline) {
            ind.classList.remove('bg-red-500');
            ind.classList.add('bg-green-500');
            ind.textContent = '🟢 En ligne';
            setTimeout(() => ind.classList.add('hidden'), 3000);
        } else {
            ind.classList.remove('bg-green-500', 'hidden');
            ind.classList.add('bg-red-500');
            ind.textContent = '🔴 Hors ligne';
        }
    }

    createNetworkIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'network-indicator';
        indicator.className = 'fixed top-4 right-4 z-50 px-4 py-2 rounded-full text-white text-sm font-medium shadow-lg transition-all duration-300 hidden';
        document.body.appendChild(indicator);
    }

    // ==================== TOAST NOTIFICATIONS ====================
    
    setupToastContainer() {
        if (document.getElementById('toast-container')) return;
        
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-20 left-4 right-4 z-50 space-y-2 pointer-events-none';
        document.body.appendChild(container);
    }

    showToast(message, type = 'info', duration = 5000) {
        const toast = this.createToast(message, type);
        const container = document.getElementById('toast-container');
        
        // Limiter le nombre de toasts
        if (this.toasts.length >= this.maxToasts) {
            this.removeToast(this.toasts[0]);
        }
        
        container.appendChild(toast);
        this.toasts.push(toast);
        
        // Animation d'entrée
        setTimeout(() => toast.classList.add('translate-x-0', 'opacity-100'), 10);
        
        // Vibration si mobile
        if (type === 'success' || type === 'error') {
            this.hapticFeedback(type);
        }
        
        // Auto-remove
        if (duration) {
            setTimeout(() => this.removeToast(toast), duration);
        }
        
        return toast;
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `transform translate-x-full opacity-0 transition-all duration-300 ease-out pointer-events-auto ${this.getToastClasses(type)}`;
        
        const icon = this.getToastIcon(type);
        
        toast.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1 text-sm font-medium">
                    ${message}
                </div>
                <button onclick="pwaManager.removeToast(this.closest('.transform'))" class="flex-shrink-0 text-current opacity-70 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        return toast;
    }

    getToastClasses(type) {
        const classes = {
            success: 'bg-green-500 text-white shadow-lg rounded-xl p-4',
            error: 'bg-red-500 text-white shadow-lg rounded-xl p-4',
            warning: 'bg-orange-500 text-white shadow-lg rounded-xl p-4',
            info: 'bg-blue-500 text-white shadow-lg rounded-xl p-4'
        };
        return classes[type] || classes.info;
    }

    getToastIcon(type) {
        const icons = {
            success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
            warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };
        return icons[type] || icons.info;
    }

    removeToast(toast) {
        if (!toast) return;
        
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');
        
        setTimeout(() => {
            toast.remove();
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    // ==================== HAPTIC FEEDBACK ====================
    
    setupHapticFeedback() {
        this.hapticSupported = 'vibrate' in navigator;
    }

    hapticFeedback(type = 'light') {
        if (!this.hapticSupported) return;
        
        const patterns = {
            light: [10],
            medium: [20],
            heavy: [30],
            success: [10, 50, 10],
            error: [20, 50, 20, 50, 20],
            warning: [15, 30, 15]
        };
        
        const pattern = patterns[type] || patterns.light;
        navigator.vibrate(pattern);
    }

    // ==================== INSTALLATION PWA ====================
    
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.installPrompt = e;
            this.showInstallButton();
        });
        
        window.addEventListener('appinstalled', () => {
            this.showToast('Application installée avec succès !', 'success');
            this.installPrompt = null;
        });
    }

    showInstallButton() {
        const btn = document.getElementById('install-pwa-btn');
        if (btn) btn.classList.remove('hidden');
    }

    async promptInstall() {
        if (!this.installPrompt) {
            this.showToast('L\'application est déjà installée', 'info');
            return;
        }
        
        this.installPrompt.prompt();
        const { outcome } = await this.installPrompt.userChoice;
        
        if (outcome === 'accepted') {
            this.showToast('Installation en cours...', 'info');
        }
        
        this.installPrompt = null;
    }

    // ==================== SERVICE WORKER ====================
    
    setupServiceWorkerUpdate() {
        if (!('serviceWorker' in navigator)) return;
        
        navigator.serviceWorker.ready.then(registration => {
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        this.showUpdatePrompt();
                    }
                });
            });
        });
    }

    showUpdatePrompt() {
        const toast = this.showToast(
            'Nouvelle version disponible. Appuyez pour actualiser.',
            'info',
            null
        );
        
        toast.style.cursor = 'pointer';
        toast.addEventListener('click', () => {
            window.location.reload();
        });
    }

    // ==================== SYNCHRONISATION OFFLINE ====================
    
    async syncOfflineActions() {
        if (!this.isOnline) return;
        
        this.showToast('Synchronisation des actions...', 'info', 2000);
        
        try {
            // Demander au Service Worker de synchroniser
            if ('serviceWorker' in navigator && 'sync' in self.ServiceWorkerRegistration.prototype) {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('offline-actions-sync');
            }
            
            // Attendre un peu pour la sync
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            this.showToast('Synchronisation terminée', 'success');
        } catch (error) {
            console.error('Erreur sync:', error);
            this.showToast('Erreur de synchronisation', 'error');
        }
    }

    // ==================== GESTION DU STORAGE ====================
    
    async checkStorageQuota() {
        if (!('storage' in navigator && 'estimate' in navigator.storage)) return;
        
        try {
            const estimate = await navigator.storage.estimate();
            const percentUsed = (estimate.usage / estimate.quota) * 100;
            
            console.log(`Storage: ${(estimate.usage / 1024 / 1024).toFixed(2)} MB / ${(estimate.quota / 1024 / 1024).toFixed(2)} MB (${percentUsed.toFixed(1)}%)`);
            
            if (percentUsed > 80) {
                this.showToast('Espace de stockage faible. Pensez à vider le cache.', 'warning');
            }
        } catch (error) {
            console.error('Erreur vérification storage:', error);
        }
    }

    async clearCache() {
        try {
            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
            
            this.showToast('Cache vidé avec succès', 'success');
            
            // Recharger
            setTimeout(() => window.location.reload(), 1000);
        } catch (error) {
            console.error('Erreur vidage cache:', error);
            this.showToast('Erreur lors du vidage du cache', 'error');
        }
    }

    // ==================== PULL TO REFRESH ====================
    
    setupPullToRefresh(callback) {
        let startY = 0;
        let currentY = 0;
        let pulling = false;
        const threshold = 80;
        
        let refreshIndicator = document.getElementById('pull-refresh-indicator');
        if (!refreshIndicator) {
            refreshIndicator = document.createElement('div');
            refreshIndicator.id = 'pull-refresh-indicator';
            refreshIndicator.className = 'fixed top-0 left-0 right-0 h-16 bg-blue-500 text-white flex items-center justify-center transform -translate-y-full transition-transform duration-300 z-40';
            refreshIndicator.innerHTML = '<svg class="animate-spin w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>';
            document.body.insertBefore(refreshIndicator, document.body.firstChild);
        }
        
        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].pageY;
                pulling = true;
            }
        });
        
        document.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            
            currentY = e.touches[0].pageY;
            const diff = currentY - startY;
            
            if (diff > 0 && diff < threshold * 2) {
                e.preventDefault();
                refreshIndicator.style.transform = `translateY(${Math.min(diff - 64, 0)}px)`;
            }
        });
        
        document.addEventListener('touchend', async () => {
            if (!pulling) return;
            pulling = false;
            
            const diff = currentY - startY;
            
            if (diff >= threshold) {
                refreshIndicator.style.transform = 'translateY(0)';
                
                if (callback) await callback();
                else window.location.reload();
                
                setTimeout(() => {
                    refreshIndicator.style.transform = 'translateY(-100%)';
                }, 1000);
            } else {
                refreshIndicator.style.transform = 'translateY(-100%)';
            }
            
            startY = 0;
            currentY = 0;
        });
    }

    // ==================== UTILITAIRES ====================
    
    async shareContent(title, text, url) {
        if (!navigator.share) {
            this.showToast('Partage non supporté sur cet appareil', 'error');
            return;
        }
        
        try {
            await navigator.share({ title, text, url });
            this.showToast('Partagé avec succès', 'success');
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.showToast('Erreur de partage', 'error');
            }
        }
    }

    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('Copié dans le presse-papiers', 'success', 2000);
                this.hapticFeedback('light');
            });
        } else {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            this.showToast('Copié', 'success', 2000);
        }
    }
}

// Instance globale
window.pwaManager = new PWAManager();

// Helper functions globales
window.showToast = (message, type, duration) => pwaManager.showToast(message, type, duration);
window.haptic = (type) => pwaManager.hapticFeedback(type);
window.copyText = (text) => pwaManager.copyToClipboard(text);
window.shareContent = (title, text, url) => pwaManager.shareContent(title, text, url);

console.log('✅ PWA Manager initialisé');
