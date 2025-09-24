/**
 * AL-AMENA DELIVERY - Validateur ULTRA SIMPLE
 * PAS DE VALIDATION - ENVOIE TOUT AU SERVEUR
 */

// FONCTION QUI ACCEPTE ABSOLUMENT TOUT
function isValidPackageCode(input) {
    // Si il y a quelque chose écrit, c'est valide
    return input && input.toString().trim().length > 0;
}

// FONCTION POUR NETTOYER LE CODE (BASIQUE)
function extractCodeFromUrl(input) {
    if (!input) return '';
    return input.toString().trim().toUpperCase();
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

console.log('✅ Validateur SIMPLE chargé - ACCEPTE ABSOLUMENT TOUT');