/**
 * AL-AMENA DELIVERY - Package Code Validator
 * Logique de validation centralisée pour tous les codes colis
 * Version: 2.0 - Refonte complète
 */

class PackageValidator {
    constructor() {
        this.debugMode = false;
        this.log('PackageValidator initialisé');
    }

    log(message, data = null) {
        if (this.debugMode) {
            console.log(`[PackageValidator] ${message}`, data || '');
        }
    }

    /**
     * Normalise et extrait le code depuis n'importe quel input
     */
    normalizeCode(input) {
        if (!input || typeof input !== 'string') {
            this.log('Input invalide', input);
            return '';
        }

        let code = input.trim();
        this.log('Code original', code);

        // 1. Détecter et extraire depuis URL
        const urlPatterns = [
            /\/track\/([A-Z0-9_]+)/i,
            /\/packages\/([A-Z0-9_]+)/i,
            /code[=:]([A-Z0-9_]+)/i,
            /pkg[=:]([A-Z0-9_]+)/i
        ];

        for (const pattern of urlPatterns) {
            const match = code.match(pattern);
            if (match) {
                code = match[1];
                this.log('Code extrait depuis URL', code);
                break;
            }
        }

        // 2. Nettoyer le code
        code = code.toUpperCase().replace(/[^A-Z0-9_]/g, '');
        this.log('Code nettoyé', code);

        return code;
    }

    /**
     * Valide un code avec tous les formats supportés
     */
    isValid(input) {
        const code = this.normalizeCode(input);

        if (!code || code.length < 3) {
            this.log('Code trop court', code);
            return false;
        }

        // Liste des formats à tester (du plus spécifique au plus général)
        const validationRules = [
            // Format principal: PKG_HNIZCWH4_20250921
            {
                name: 'Format principal',
                pattern: /^PKG_[A-Z0-9]{8}_\d{8}$/,
                test: code => this.testPattern(code, /^PKG_[A-Z0-9]{8}_\d{8}$/, 'Format principal')
            },

            // Format seeder: PKG_000038, PKG_000007
            {
                name: 'Format seeder',
                pattern: /^PKG_\d{6}$/,
                test: code => this.testPattern(code, /^PKG_\d{6}$/, 'Format seeder')
            },

            // Format PKG général: PKG_XXXXX (avec ou sans date)
            {
                name: 'Format PKG général',
                pattern: /^PKG_[A-Z0-9]{1,20}(_\d{8})?$/,
                test: code => this.testPattern(code, /^PKG_[A-Z0-9]{1,20}(_\d{8})?$/, 'Format PKG général')
            },

            // Codes alphanumériques purs
            {
                name: 'Alphanumériques',
                pattern: /^[A-Z0-9]{6,20}$/,
                test: code => {
                    if (this.testPattern(code, /^[A-Z0-9]{6,20}$/, 'Alphanumériques')) {
                        // Exclure les mots évidents qui ne sont pas des codes
                        const excludedWords = ['LIVRAISON', 'DELIVERY', 'SERVICE', 'CONTACT', 'ALAMENA', 'TELEPHONE', 'ADRESSE'];
                        return !excludedWords.some(word => code.includes(word));
                    }
                    return false;
                }
            },

            // Codes numériques purs
            {
                name: 'Numériques',
                pattern: /^\d{6,20}$/,
                test: code => this.testPattern(code, /^\d{6,20}$/, 'Numériques')
            }
        ];

        // Tester chaque règle
        for (const rule of validationRules) {
            if (rule.test(code)) {
                this.log(`✅ Code valide: ${rule.name}`, code);
                return true;
            }
        }

        // Test spécial pour les URLs complètes
        if (this.isValidUrl(input)) {
            this.log('✅ URL valide détectée', input);
            return true;
        }

        this.log('❌ Code invalide', code);
        return false;
    }

    /**
     * Teste un pattern spécifique
     */
    testPattern(code, pattern, name) {
        const result = pattern.test(code);
        this.log(`Test ${name}: ${result}`, code);
        return result;
    }

    /**
     * Vérifie si c'est une URL de tracking valide
     */
    isValidUrl(input) {
        try {
            const url = new URL(input);
            return url.pathname.includes('/track/') || url.pathname.includes('/packages/');
        } catch {
            return false;
        }
    }

    /**
     * Obtient le code final à envoyer au serveur
     */
    getFinalCode(input) {
        return this.normalizeCode(input);
    }

    /**
     * Méthode de validation publique simple
     */
    validate(input) {
        return {
            isValid: this.isValid(input),
            normalizedCode: this.normalizeCode(input),
            originalInput: input
        };
    }

    /**
     * Active/désactive le mode debug
     */
    setDebugMode(enabled) {
        this.debugMode = enabled;
        this.log(`Debug mode ${enabled ? 'activé' : 'désactivé'}`);
    }
}

// Initialisation sécurisée
try {
    // Instance globale
    window.packageValidator = new PackageValidator();

    // Pour le debugging en mode développement
    if (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') {
        window.packageValidator.setDebugMode(true);
    }

    console.log('🚀 PackageValidator chargé et prêt', window.packageValidator);

    // Test rapide d'initialisation
    const testResult = window.packageValidator.validate('PKG_000001');
    console.log('✅ Test d\'initialisation:', testResult.isValid ? 'SUCCÈS' : 'ÉCHEC');

} catch (error) {
    console.error('❌ Erreur chargement PackageValidator:', error);
}

// Export pour les modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PackageValidator;
}

// Fonction de vérification disponible globalement
window.checkPackageValidator = function() {
    console.log('📊 État PackageValidator:');
    console.log('- Disponible:', !!window.packageValidator);
    if (window.packageValidator) {
        console.log('- Test PKG_000001:', window.packageValidator.validate('PKG_000001').isValid);
        console.log('- Test URL:', window.packageValidator.validate('http://test.com/track/PKG_ABC123').isValid);
    }
};