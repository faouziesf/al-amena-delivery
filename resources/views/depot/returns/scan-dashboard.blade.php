@extends('layouts.depot-manager')

@section('title', 'Scan Retours Dépôt')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-red-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-orange-900">📦 Scan Colis Retours</h1>
                    <p class="text-orange-700 mt-1">Session: {{ substr($sessionId, 7) }} - Chef: <strong>{{ $depotManagerName }}</strong></p>
                </div>
                <div id="connection-status" class="flex items-center space-x-2">
                    <div id="status-indicator" class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                    <span id="status-text" class="text-sm font-medium">En attente de scan</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- QR Code Section -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">📱 Connexion Mobile</h2>
                <div class="bg-gray-50 rounded-lg p-8 mb-6 flex justify-center">
                    <div class="bg-white p-4 rounded-lg border-2 border-orange-300">
                        <div id="qrcode"></div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-gray-700 font-medium mb-2">Scannez ce code avec votre téléphone</p>
                    <p class="text-sm text-gray-500">Le téléphone sera connecté à cette session</p>
                </div>
            </div>

            <!-- Liste des colis scannés -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">📋 Colis Scannés</h2>
                    <span id="package-count" class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full font-bold">0</span>
                </div>

                <div id="packages-list" class="space-y-3 max-h-96 overflow-y-auto">
                    <div class="text-center text-gray-400 py-8">
                        <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p>Aucun colis scanné</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton de validation -->
        <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total de colis retours scannés</p>
                    <p id="total-count" class="text-2xl font-bold text-orange-900">0</p>
                </div>
                <div class="flex space-x-3">
                    <form action="{{ route('depot.returns.new-session') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-all">
                            🔄 Nouvelle Session
                        </button>
                    </form>
                    <button onclick="validateFromPC()" id="validate-btn" disabled
                        class="px-8 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg font-bold transition-all shadow-lg">
                        ✅ Valider et Créer Colis Retours
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popup de succès -->
<div id="success-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">✅ Validation Réussie!</h3>
            <p id="success-message" class="text-gray-600 mb-6"></p>
            <div class="flex space-x-3">
                <form action="{{ route('depot.returns.new-session') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">
                        Démarrer Nouvelle Session
                    </button>
                </form>
                <a href="{{ route('depot.returns.manage') }}" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-center">
                    Voir les Retours
                </a>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
const sessionId = '{{ $sessionId }}';
const qrCodeUrl = '{{ $qrCodeUrl }}';
let pollInterval;
let packages = [];

// Démarrer le polling et générer QR code
document.addEventListener('DOMContentLoaded', function() {
    // Générer le QR code
    new QRCode(document.getElementById('qrcode'), {
        text: qrCodeUrl,
        width: 200,
        height: 200,
        colorDark: '#ea580c',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    pollSessionStatus();
    pollInterval = setInterval(pollSessionStatus, 2000);
});

// Récupérer le statut de la session
async function pollSessionStatus() {
    try {
        const response = await fetch(`/depot/returns/api/session/${sessionId}/status`);
        const data = await response.json();

        if (!data.exists || !data.active) {
            document.getElementById('status-indicator').className = 'w-3 h-3 bg-red-500 rounded-full';
            document.getElementById('status-text').textContent = 'Session terminée';
            clearInterval(pollInterval);
            return;
        }

        // Mettre à jour la liste des colis
        packages = data.packages || [];
        updatePackagesList(packages);
        updateCounts(packages.length);

        if (packages.length > 0) {
            document.getElementById('status-indicator').className = 'w-3 h-3 bg-green-500 rounded-full';
            document.getElementById('status-text').textContent = 'Connecté - ' + packages.length + ' colis';
            document.getElementById('validate-btn').disabled = false;
        } else {
            document.getElementById('status-indicator').className = 'w-3 h-3 bg-yellow-500 rounded-full animate-pulse';
            document.getElementById('status-text').textContent = 'En attente de scan';
        }
    } catch (error) {
        console.error('Erreur lors du polling:', error);
    }
}

// Mettre à jour la liste visuelle
function updatePackagesList(packages) {
    const listContainer = document.getElementById('packages-list');

    if (packages.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center text-gray-400 py-8">
                <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p>Aucun colis scanné</p>
            </div>
        `;
        return;
    }

    listContainer.innerHTML = packages.map((pkg, index) => `
        <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="font-mono font-bold text-orange-900">${pkg.code}</div>
                    <div class="text-sm text-gray-600">${pkg.sender} - ${pkg.cod} TND</div>
                    <div class="text-xs text-gray-500 mt-1">${pkg.reason || 'Aucune raison'}</div>
                </div>
                <div class="text-2xl">📦</div>
            </div>
        </div>
    `).join('');
}

// Mettre à jour les compteurs
function updateCounts(count) {
    document.getElementById('package-count').textContent = count;
    document.getElementById('total-count').textContent = count;
}

// Valider depuis le PC
async function validateFromPC() {
    if (packages.length === 0) {
        alert('Aucun colis à valider');
        return;
    }

    if (!confirm(`Créer ${packages.length} colis retour(s) ?`)) {
        return;
    }

    try {
        const response = await fetch(`/depot/returns/${sessionId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (data.success) {
            showSuccessPopup(data.message, data.created_count);
            clearInterval(pollInterval);
        } else {
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la validation');
    }
}

// Afficher popup de succès
function showSuccessPopup(message, count) {
    document.getElementById('success-message').textContent = message;
    document.getElementById('success-popup').classList.remove('hidden');
}
</script>
@endsection
