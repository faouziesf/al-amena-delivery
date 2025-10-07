/**
 * Deliverer Enhancements - Corrections automatiques PWA
 * @version 1.0.0
 * S'applique automatiquement à toutes les pages livreur
 */

(function() {
    'use strict';

    // ==================== CONFIGURATION ====================
    
    const CONFIG = {
        apiTimeout: 30000,
        retryAttempts: 3,
        retryDelay: 1000,
        toastDuration: 5000,
        hapticEnabled: true,
        debugMode: localStorage.getItem('debug') === 'true'
    };

    // ==================== AMÉLIORATION DES FETCH ====================
    
    /**
     * Intercepter tous les fetch et ajouter gestion d'erreur
     */
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const [url, options = {}] = args;
        
        // Ajouter CSRF token automatiquement
        if (!options.headers) options.headers = {};
        if (typeof options.headers === 'object' && !options.headers['X-CSRF-TOKEN']) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            if (token) options.headers['X-CSRF-TOKEN'] = token;
        }
        
        // Log en mode debug
        if (CONFIG.debugMode) {
            console.log('🌐 Fetch:', url, options);
        }
        
        return originalFetch.apply(this, args)
            .then(response => {
                if (!response.ok && response.status !== 422) {
                    console.error('❌ Fetch Error:', response.status, url);
                }
                return response;
            })
            .catch(error => {
                console.error('❌ Fetch Failed:', url, error);
                if (!navigator.onLine) {
                    showToast('Mode hors ligne - Action mise en queue', 'warning');
                }
                throw error;
            });
    };

    // ==================== AMÉLIORATION DES FORMULAIRES ====================
    
    /**
     * Ajouter validation et feedback à tous les formulaires
     */
    function enhanceForms() {
        document.querySelectorAll('form').forEach(form => {
            // Skip si déjà amélioré
            if (form.dataset.enhanced) return;
            form.dataset.enhanced = 'true';
            
            form.addEventListener('submit', async function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                
                // Désactiver bouton pendant soumission
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <span class="ml-2">Traitement...</span>
                    `;
                    
                    // Restaurer après timeout
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, CONFIG.apiTimeout);
                }
                
                // Haptic feedback
                if (CONFIG.hapticEnabled) {
                    haptic('light');
                }
            });
        });
    }

    // ==================== AMÉLIORATION DES BOUTONS ====================
    
    /**
     * Ajouter haptic feedback aux boutons importants
     */
    function enhanceButtons() {
        const selectors = [
            'button[type="submit"]',
            '.btn-primary',
            '.btn-success',
            '.btn-danger',
            'button[onclick*="accept"]',
            'button[onclick*="deliver"]',
            'button[onclick*="return"]',
            'button[onclick*="unavailable"]'
        ];
        
        document.querySelectorAll(selectors.join(',')).forEach(btn => {
            if (btn.dataset.haptic) return;
            btn.dataset.haptic = 'true';
            
            btn.addEventListener('click', () => {
                const action = btn.textContent.toLowerCase();
                
                if (action.includes('livr') || action.includes('accept')) {
                    haptic('success');
                } else if (action.includes('refus') || action.includes('annul')) {
                    haptic('error');
                } else {
                    haptic('light');
                }
            });
        });
    }

    // ==================== LAZY LOADING IMAGES ====================
    
    /**
     * Lazy load pour toutes les images
     */
    function lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback pour navigateurs sans IntersectionObserver
            images.forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        }
    }

    // ==================== SKELETON LOADERS ====================
    
    /**
     * Ajouter skeleton loaders aux conteneurs avec loading
     */
    function addSkeletonLoaders() {
        const loadingContainers = document.querySelectorAll('[x-show*="loading"]');
        
        loadingContainers.forEach(container => {
            if (container.querySelector('.skeleton')) return;
            
            const skeleton = document.createElement('div');
            skeleton.className = 'skeleton animate-pulse space-y-3';
            skeleton.innerHTML = `
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            `;
            
            container.prepend(skeleton);
        });
    }

    // ==================== OPTIMISATION ALPINE.JS ====================
    
    /**
     * Ajouter x-cloak pour éviter flash de contenu
     */
    function optimizeAlpine() {
        // Ajouter styles x-cloak si pas présent
        if (!document.getElementById('alpine-cloak-styles')) {
            const style = document.createElement('style');
            style.id = 'alpine-cloak-styles';
            style.textContent = '[x-cloak] { display: none !important; }';
            document.head.appendChild(style);
        }
        
        // Ajouter x-cloak aux éléments Alpine non initialisés
        document.querySelectorAll('[x-data]').forEach(el => {
            if (!el.hasAttribute('x-cloak')) {
                el.setAttribute('x-cloak', '');
            }
        });
    }

    // ==================== AMÉLIORATION MODALES ====================
    
    /**
     * Améliorer les modales avec fermeture ESC et backdrop
     */
    function enhanceModals() {
        document.querySelectorAll('[x-show*="modal"], [id*="modal"]').forEach(modal => {
            if (modal.dataset.enhanced) return;
            modal.dataset.enhanced = 'true';
            
            // Fermer avec ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            
            // Fermer en cliquant backdrop
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    }

    // ==================== COPIE RAPIDE ====================
    
    /**
     * Ajouter copie rapide sur les codes (PKG_, tracking, etc.)
     */
    function addQuickCopy() {
        const codeElements = document.querySelectorAll('[class*="package-code"], [class*="tracking"], .font-mono');
        
        codeElements.forEach(el => {
            if (el.dataset.copyable) return;
            el.dataset.copyable = 'true';
            el.style.cursor = 'pointer';
            el.title = 'Cliquer pour copier';
            
            el.addEventListener('click', () => {
                const text = el.textContent.trim();
                copyText(text);
            });
        });
    }

    // ==================== SCROLL SMOOTH ====================
    
    /**
     * Améliorer le scroll
     */
    function enhanceScroll() {
        // Smooth scroll pour ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Bouton retour en haut
        const scrollBtn = document.createElement('button');
        scrollBtn.id = 'scroll-to-top';
        scrollBtn.className = 'fixed bottom-24 right-4 w-12 h-12 bg-blue-500 text-white rounded-full shadow-lg hidden transition-all hover:bg-blue-600 z-40';
        scrollBtn.innerHTML = `
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        `;
        scrollBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
        document.body.appendChild(scrollBtn);
        
        // Afficher/masquer selon scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.remove('hidden');
            } else {
                scrollBtn.classList.add('hidden');
            }
        });
    }

    // ==================== GESTION DES ERREURS GLOBALE ====================
    
    /**
     * Capturer et afficher erreurs JS
     */
    function setupErrorHandling() {
        window.addEventListener('error', (e) => {
            console.error('💥 Error:', e.message, e.filename, e.lineno);
            
            if (CONFIG.debugMode) {
                showToast(`Erreur: ${e.message}`, 'error');
            }
        });
        
        window.addEventListener('unhandledrejection', (e) => {
            console.error('💥 Unhandled Promise:', e.reason);
            
            if (CONFIG.debugMode) {
                showToast(`Promise rejetée: ${e.reason}`, 'error');
            }
        });
    }

    // ==================== MONITORING PERFORMANCE ====================
    
    /**
     * Monitorer performance de la page
     */
    function monitorPerformance() {
        if (!('PerformanceObserver' in window)) return;
        
        const observer = new PerformanceObserver((list) => {
            list.getEntries().forEach(entry => {
                if (CONFIG.debugMode) {
                    console.log(`⚡ ${entry.name}: ${entry.duration.toFixed(2)}ms`);
                }
                
                // Alerter si trop lent
                if (entry.duration > 5000) {
                    console.warn('⚠️ Performance lente:', entry.name);
                }
            });
        });
        
        try {
            observer.observe({ entryTypes: ['measure', 'navigation'] });
        } catch (e) {
            console.log('Performance Observer non supporté');
        }
    }

    // ==================== CACHE LOCAL SIMPLE ====================
    
    /**
     * Cache local simple pour données fréquentes
     */
    const LocalCache = {
        set(key, value, ttl = 3600000) { // 1 heure par défaut
            const item = {
                value,
                expiry: Date.now() + ttl
            };
            localStorage.setItem(`cache_${key}`, JSON.stringify(item));
        },
        
        get(key) {
            const itemStr = localStorage.getItem(`cache_${key}`);
            if (!itemStr) return null;
            
            const item = JSON.parse(itemStr);
            if (Date.now() > item.expiry) {
                localStorage.removeItem(`cache_${key}`);
                return null;
            }
            
            return item.value;
        },
        
        clear() {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('cache_')) {
                    localStorage.removeItem(key);
                }
            });
        }
    };
    
    window.LocalCache = LocalCache;

    // ==================== HELPERS GLOBAUX ====================
    
    /**
     * Formater montant
     */
    window.formatAmount = function(amount) {
        return new Intl.NumberFormat('fr-TN', {
            style: 'decimal',
            minimumFractionDigits: 3,
            maximumFractionDigits: 3
        }).format(amount) + ' DT';
    };

    /**
     * Formater date relative
     */
    window.formatRelativeDate = function(date) {
        const diff = Date.now() - new Date(date).getTime();
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'À l\'instant';
        if (minutes < 60) return `Il y a ${minutes} min`;
        if (hours < 24) return `Il y a ${hours}h`;
        if (days < 7) return `Il y a ${days}j`;
        
        return new Date(date).toLocaleDateString('fr-FR');
    };

    /**
     * Valider numéro de téléphone
     */
    window.validatePhone = function(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return /^(?:\+?216)?[0-9]{8}$/.test(cleaned);
    };

    /**
     * Valider montant
     */
    window.validateAmount = function(amount, min = 0, max = 999999) {
        const num = parseFloat(amount);
        return !isNaN(num) && num >= min && num <= max;
    };

    // ==================== INITIALISATION ====================
    
    function init() {
        console.log('🚀 Deliverer Enhancements v1.0.0');
        
        // Attendre que le DOM soit prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        // Appliquer toutes les améliorations
        enhanceForms();
        enhanceButtons();
        lazyLoadImages();
        addSkeletonLoaders();
        optimizeAlpine();
        enhanceModals();
        addQuickCopy();
        enhanceScroll();
        setupErrorHandling();
        monitorPerformance();
        
        // Réappliquer après changements DOM (Alpine.js, AJAX, etc.)
        const mutationObserver = new MutationObserver(() => {
            enhanceForms();
            enhanceButtons();
            lazyLoadImages();
            addQuickCopy();
        });
        
        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('✅ Deliverer Enhancements actif');
    }

    // Lancer l'initialisation
    init();

})();
