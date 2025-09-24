/**
 * Scanner Simple - Al-Amena Delivery
 * Système de scan basique avec vérification base de données
 */

// Fonction simple pour scanner un code
async function scanPackage(code) {
    console.log('🔍 Scan du code:', code);

    if (!code || code.trim().length === 0) {
        showResult(false, 'Veuillez saisir un code valide');
        return;
    }

    // Afficher loading
    showLoading(true);

    try {
        // Appel API simple
        const response = await fetch('/deliverer/packages/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ code: code.trim() })
        });

        const data = await response.json();
        console.log('📦 Résultat:', data);

        // Afficher le résultat
        if (data.success && data.package) {
            showResult(true, 'Colis trouvé !', data);
        } else {
            showResult(false, data.message || 'Colis non trouvé');
        }

    } catch (error) {
        console.error('❌ Erreur scan:', error);
        showResult(false, 'Erreur de connexion');
    } finally {
        showLoading(false);
    }
}

// Afficher le popup de résultat
function showResult(success, message, data = null) {
    // Supprimer ancien popup s'il existe
    const oldPopup = document.getElementById('scan-popup');
    if (oldPopup) oldPopup.remove();

    // Créer le popup
    const popup = document.createElement('div');
    popup.id = 'scan-popup';
    popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';

    const iconColor = success ? 'text-green-600' : 'text-red-600';
    const bgColor = success ? 'bg-green-50' : 'bg-red-50';
    const icon = success ? '✅' : '❌';

    let packageInfo = '';
    if (success && data && data.package) {
        packageInfo = `
            <div class="mt-4 p-3 bg-gray-50 rounded-lg text-left">
                <p class="font-bold text-blue-600 font-mono">${data.package.code}</p>
                ${data.delivery_info?.name ? `<p class="text-sm">👤 ${data.delivery_info.name}</p>` : ''}
                ${data.delivery_info?.address ? `<p class="text-sm">📍 ${data.delivery_info.address}</p>` : ''}
                ${data.package.cod_amount > 0 ? `<p class="text-sm font-bold text-green-600">💰 ${data.package.formatted_cod || data.package.cod_amount + ' DA'}</p>` : ''}
            </div>
        `;
    }

    popup.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full text-center">
            <div class="w-16 h-16 mx-auto ${bgColor} rounded-full flex items-center justify-center mb-4">
                <span class="text-3xl">${icon}</span>
            </div>
            <h3 class="text-xl font-bold ${iconColor} mb-2">
                ${success ? 'Colis Trouvé' : 'Erreur'}
            </h3>
            <p class="text-gray-700 mb-4">${message}</p>
            ${packageInfo}
            <button onclick="closePopup()"
                    class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Fermer
            </button>
        </div>
    `;

    document.body.appendChild(popup);

    // Fermer au clic sur le fond
    popup.addEventListener('click', (e) => {
        if (e.target === popup) closePopup();
    });

    // Fermer avec Escape
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closePopup();
            document.removeEventListener('keydown', escapeHandler);
        }
    });
}

// Fermer le popup
function closePopup() {
    const popup = document.getElementById('scan-popup');
    if (popup) {
        popup.remove();
    }
}

// Afficher/cacher loading
function showLoading(show) {
    const loadingElements = document.querySelectorAll('.scan-loading');
    loadingElements.forEach(el => {
        if (show) {
            el.innerHTML = '<span class="spinner"></span> Recherche...';
            el.disabled = true;
        } else {
            el.innerHTML = 'Scanner';
            el.disabled = false;
        }
    });
}

// Récupérer token CSRF
function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Scanner Simple initialisé');

    // Gestion des formulaires de scan
    const scanForms = document.querySelectorAll('.scan-form');
    scanForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = form.querySelector('input[name="code"]');
            if (input) {
                await scanPackage(input.value);
                input.value = ''; // Vider après scan
            }
        });
    });

    // Gestion des boutons de scan
    const scanButtons = document.querySelectorAll('.scan-button');
    scanButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const codeInput = document.querySelector('input[name="scan-code"]') ||
                             document.querySelector('input[type="text"]');
            if (codeInput) {
                await scanPackage(codeInput.value);
                codeInput.value = ''; // Vider après scan
            }
        });
    });
});

// Fonction globale pour utilisation inline
window.simpleScan = scanPackage;
window.closePopup = closePopup;