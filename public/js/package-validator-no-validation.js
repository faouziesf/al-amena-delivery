/**
 * AL-AMENA DELIVERY - Validateur SANS VALIDATION
 * Accepte ABSOLUMENT TOUT - Le backend gère la validation
 */

// FONCTION QUI ACCEPTE ABSOLUMENT TOUT - AUCUNE VALIDATION
function isValidPackageCode(input) {
    // Retourne true si il y a quelque chose d'écrit
    return input && input.toString().trim().length > 0;
}

// FONCTION POUR NETTOYER LE CODE BASIQUE (JUSTE TRIM)
function extractCodeFromUrl(input) {
    if (!input) return '';
    return input.toString().trim();
}

// FONCTION DE VALIDATION FINALE - ULTRA PERMISSIVE
function validatePackageCode(input) {
    const code = extractCodeFromUrl(input);
    return {
        isValid: isValidPackageCode(code),
        normalizedCode: code,
        originalInput: input
    };
}

// DISPONIBLE GLOBALEMENT
window.validatePackageCode = validatePackageCode;
window.isValidPackageCode = isValidPackageCode;
window.extractCodeFromUrl = extractCodeFromUrl;

console.log('✅ Validateur SANS VALIDATION chargé - Backend gère la validation');