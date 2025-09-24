/**
 * Alpine.js Simple - Fallback léger pour Al-Amena Delivery
 * Ne remplace pas Alpine.js si il se charge correctement
 */

// Vérifier si Alpine.js n'existe pas après 2 secondes
setTimeout(() => {
    if (typeof Alpine === 'undefined') {
        console.log('⚠️ Alpine.js CDN non disponible - fallback minimal activé');

        // Créer un Alpine.js minimal
        window.Alpine = {
            data: function(name, callback) {
                // Stocker les composants pour usage futur si besoin
                if (!window._alpineComponents) {
                    window._alpineComponents = {};
                }
                window._alpineComponents[name] = callback;
                console.log(`Alpine fallback: composant ${name} enregistré`);
            },
            start: function() {
                console.log('Alpine.js fallback démarré (mode minimal)');
                document.body.setAttribute('data-alpine-initialized', 'fallback');
            }
        };

        // Démarrer le fallback
        if (window.Alpine.start) {
            window.Alpine.start();
        }
    }
}, 2000);

console.log('✅ Alpine.js fallback chargé - Prêt si CDN échoue');