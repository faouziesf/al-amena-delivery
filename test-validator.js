// Test simple du validateur en Node.js
const PackageValidator = require('./public/js/package-validator.js');

const validator = new PackageValidator();
validator.setDebugMode(true);

// Tests des codes réels
const testCodes = [
    'PKG_HNIZCWH4_20250921',
    'PKG_CLQVFCWP_20250921',
    'PKG_000038',
    'PKG_000007',
    'http://127.0.0.1:8000/track/PKG_HNIZCWH4_20250921',
    'PKG_WRQFAGFY_20250918', // Ancien code qui devrait marcher
    'INVALID',
    'ABC'
];

console.log('🧪 Test des codes AL-AMENA DELIVERY\n');

testCodes.forEach((code, index) => {
    const result = validator.validate(code);
    console.log(`Test ${index + 1}: ${code}`);
    console.log(`  ✅ Valide: ${result.isValid}`);
    console.log(`  📝 Normalisé: ${result.normalizedCode}`);
    console.log('');
});

console.log('✅ Tests terminés');