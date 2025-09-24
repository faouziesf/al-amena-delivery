/**
 * AL-AMENA DELIVERY - Validateur FINAL
 * LOGIQUE SIMPLE QUI MARCHE TOUJOURS
 */

// FONCTION SIMPLE QUI ACCEPTE TOUT CE QUI RESSEMBLE À UN CODE
function isValidPackageCode(input) {
    if (!input || typeof input !== 'string') return false;

    const code = input.trim().toUpperCase();
    if (code.length < 3) return false;

    // ACCEPTER ABSOLUMENT TOUS CES FORMATS:

    // 1. Tout ce qui commence par PKG_
    if (code.startsWith('PKG_')) return true;

    // 2. Tout code alphanumérique de 6+ caractères
    if (/^[A-Z0-9]{6,}$/.test(code)) return true;

    // 3. Tout code numérique de 6+ chiffres
    if (/^\d{6,}$/.test(code)) return true;

    // 4. URL de tracking
    if (code.includes('/TRACK/') || code.includes('TRACK')) return true;

    return false;
}

// FONCTION POUR EXTRAIRE LE CODE DEPUIS UNE URL
function extractCodeFromUrl(input) {
    if (!input) return '';

    const str = input.toString().trim().toUpperCase();

    // Chercher /track/CODE dans l'URL
    const urlMatch = str.match(/\/TRACK\/([A-Z0-9_]+)/i);
    if (urlMatch) return urlMatch[1];

    return str;
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

console.log('✅ Validateur FINAL chargé - ACCEPTE TOUT');