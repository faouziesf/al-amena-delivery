/**
 * Scanner Fallback - Fallback pour jsQR et ZXing
 * Permet au scanner de fonctionner même sans les bibliothèques externes
 */

// Fallback jsQR simple
if (typeof jsQR === 'undefined') {
    window.jsQR = function(data, width, height, options) {
        // Mock function - ne peut pas vraiment décoder QR sans bibliothèque
        console.log('jsQR fallback - décoder QR non disponible');
        return null;
    };
    console.log('⚠️ jsQR fallback activé - scan QR limité');
}

// Fallback ZXing simple
if (typeof ZXing === 'undefined') {
    window.ZXing = {
        BrowserQRCodeReader: function() {
            return {
                decode: function() {
                    console.log('ZXing fallback - décoder QR non disponible');
                    return null;
                }
            };
        },
        RGBLuminanceSource: function() { return {}; },
        BinaryBitmap: function() { return {}; },
        HybridBinarizer: function() { return {}; }
    };
    console.log('⚠️ ZXing fallback activé - scan QR limité');
}

// Fallback Quagga simple
if (typeof Quagga === 'undefined') {
    window.Quagga = {
        init: function(config, callback) {
            console.log('Quagga fallback - scan barcode non disponible');
            if (callback) callback(new Error('Quagga fallback - pas de scan disponible'));
        },
        start: function() {
            console.log('Quagga fallback - démarrage simulé');
        },
        stop: function() {
            console.log('Quagga fallback - arrêt simulé');
        },
        offDetected: function() {},
        offProcessed: function() {},
        onDetected: function() {},
        onProcessed: function() {}
    };
    console.log('⚠️ Quagga fallback activé - scan barcode limité');
}

console.log('✅ Scanner Fallback chargé - Mode dégradé disponible');