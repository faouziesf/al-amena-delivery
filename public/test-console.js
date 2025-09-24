// Test console pour le validateur AL-AMENA DELIVERY
// Copiez-collez ce code dans la console du navigateur à l'URL: http://127.0.0.1:8000/test-validator.html

console.log('🚀 Démarrage des tests AL-AMENA DELIVERY...\n');

// Activer le mode debug
if (window.packageValidator) {
    window.packageValidator.setDebugMode(true);
} else {
    console.error('❌ packageValidator non trouvé');
}

// Liste des codes à tester
const testCases = [
    { code: 'PKG_HNIZCWH4_20250921', description: 'Format principal spécifié' },
    { code: 'PKG_CLQVFCWP_20250921', description: 'Format principal 2' },
    { code: 'PKG_000038', description: 'Format seeder spécifié' },
    { code: 'PKG_000007', description: 'Format seeder 2' },
    { code: 'http://127.0.0.1:8000/track/PKG_HNIZCWH4_20250921', description: 'URL complète spécifiée' },
    { code: 'PKG_WRQFAGFY_20250918', description: 'Code ancien qui devrait marcher' },
    { code: 'PKG_ABC123', description: 'PKG simple' },
    { code: 'ABC123DEF890', description: 'Code alphanumérique' },
    { code: '123456789', description: 'Code numérique' },
    { code: 'ABC', description: 'Trop court (ECHEC attendu)' },
    { code: 'LIVRAISON', description: 'Mot exclus (ECHEC attendu)' },
    { code: '', description: 'Vide (ECHEC attendu)' }
];

// Exécuter les tests
testCases.forEach((testCase, index) => {
    console.log(`\n--- Test ${index + 1}: ${testCase.description} ---`);
    console.log(`Code: "${testCase.code}"`);

    if (window.packageValidator) {
        const result = window.packageValidator.validate(testCase.code);
        console.log(`✅ Résultat: ${result.isValid ? 'VALIDE' : 'INVALIDE'}`);
        console.log(`📝 Normalisé: "${result.normalizedCode}"`);
    } else {
        console.log('❌ Validateur non disponible');
    }
});

console.log('\n🎯 Tests terminés ! Vérifiez les résultats ci-dessus.');
console.log('\n💡 Pour tester un code manuellement:');
console.log('window.packageValidator.validate("VOTRE_CODE")');

// Test interactif
window.testCode = function(code) {
    if (!window.packageValidator) {
        console.error('❌ Validateur non disponible');
        return;
    }

    const result = window.packageValidator.validate(code);
    console.log(`\n🧪 Test de: "${code}"`);
    console.log(`✅ Résultat: ${result.isValid ? 'VALIDE' : 'INVALIDE'}`);
    console.log(`📝 Normalisé: "${result.normalizedCode}"`);
    return result;
};

console.log('\n🔧 Fonction testCode() créée. Utilisez: testCode("VOTRE_CODE")');