@extends('layouts.depot-manager')

@section('title', 'Gestionnaire de Boîtes de Transit')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- En-tête avec Onglets -->
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="px-8 pt-8 pb-4">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">
                            🏢 Gestionnaire de Boîtes - Dépôt Tunis
                        </h1>
                        <p class="text-gray-600">Interface complète de gestion des boîtes de transit</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</div>
                        <div class="text-lg font-semibold text-blue-600" id="current-time"></div>
                    </div>
                </div>

                <!-- Navigation par Onglets -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8">
                        <button id="tab-preparation" class="tab-button active py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                            📦 Préparation / Tri
                        </button>
                        <button id="tab-departures" class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                            🚛 Départs / Arrivées
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Contenu des Onglets -->
        <div id="content-preparation" class="tab-content">
            <!-- Scanner et Statistiques -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Zone Scanner -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            🔍 Scanner de Colis
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Code du Colis</label>
                                <input type="text" id="package-scanner"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Scannez ou saisissez le code..." autofocus>
                            </div>

                            <button id="scan-button"
                                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105">
                                📷 Scanner Maintenant
                            </button>
                        </div>

                        <!-- Feedback Zone -->
                        <div id="scan-feedback" class="mt-4 p-4 rounded-lg hidden">
                            <div id="feedback-content"></div>
                        </div>
                    </div>

                    <!-- Statistiques Temps Réel -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Statistiques du Jour</h3>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-blue-800">Colis Scannés</span>
                                <span id="scanned-count" class="text-lg font-bold text-blue-600">0</span>
                            </div>

                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-green-800">Boîtes Scellées</span>
                                <span id="sealed-count" class="text-lg font-bold text-green-600">0</span>
                            </div>

                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-purple-800">Gouvernorats Actifs</span>
                                <span id="active-governorates" class="text-lg font-bold text-purple-600">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grille des Gouvernorats -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center justify-between">
                            🗂️ Répartition par Gouvernorat (24)
                            <span class="text-sm text-gray-500">Cliquez pour sceller une boîte</span>
                        </h2>

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="governorates-grid">
                            @php
                                $governorates = [
                                    'TUNIS', 'SFAX', 'SOUSSE', 'KAIROUAN', 'BIZERTE', 'GABES',
                                    'ARIANA', 'MANOUBA', 'NABEUL', 'ZAGHOUAN', 'BEJA', 'JENDOUBA',
                                    'KASSERINE', 'SILIANA', 'KEBILI', 'TOZEUR', 'GAFSA', 'SIDI_BOUZID',
                                    'MEDENINE', 'TATAOUINE', 'MAHDIA', 'MONASTIR', 'KASER', 'BENARO'
                                ];
                            @endphp

                            @foreach($governorates as $gov)
                            <div class="governorate-box border-2 border-gray-200 rounded-lg p-4 text-center hover:border-blue-300 transition-all duration-300 cursor-pointer"
                                 data-governorate="{{ $gov }}">
                                <div class="text-sm font-medium text-gray-700 mb-2">{{ $gov }}</div>
                                <div class="text-2xl font-bold text-blue-600 package-count">0</div>
                                <div class="text-xs text-gray-500 mt-1">colis</div>

                                <button class="seal-button w-full mt-3 bg-green-500 text-white py-2 px-3 rounded-lg text-sm font-medium hover:bg-green-600 transition-colors duration-200 opacity-0 pointer-events-none">
                                    🔒 Sceller & Imprimer
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglet Départs/Arrivées -->
        <div id="content-departures" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Mode Réception -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        📥 Mode Réception
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scanner Bon de Boîte</label>
                            <input type="text" id="box-scanner"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Ex: SFAX-TUN-28092025-01">
                        </div>

                        <button id="receive-button"
                                class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-300">
                            ✅ Confirmer Réception
                        </button>
                    </div>

                    <!-- Liste des Boîtes Reçues -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Boîtes Reçues Aujourd'hui</h3>
                        <div class="space-y-3" id="received-boxes">
                            <!-- Sera rempli dynamiquement -->
                        </div>
                    </div>
                </div>

                <!-- Suivi des Expéditions -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        📤 Suivi des Expéditions
                    </h2>

                    <div class="space-y-4" id="expedition-tracking">
                        @php
                            $expeditions = [
                                ['code' => 'SFAX-TUN-28092025-03', 'destination' => 'SFAX', 'packages' => 24, 'status' => 'En Transit', 'time' => '14:32'],
                                ['code' => 'SOUSSE-TUN-28092025-04', 'destination' => 'SOUSSE', 'packages' => 18, 'status' => 'Scellée', 'time' => '14:15'],
                                ['code' => 'GABES-TUN-28092025-02', 'destination' => 'GABES', 'packages' => 31, 'status' => 'Livrée', 'time' => '13:58'],
                            ];
                        @endphp

                        @foreach($expeditions as $exp)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $exp['code'] }}</div>
                                    <div class="text-sm text-gray-600">{{ $exp['destination'] }} • {{ $exp['packages'] }} colis</div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($exp['status'] === 'En Transit') bg-yellow-100 text-yellow-800
                                    @elseif($exp['status'] === 'Scellée') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    {{ $exp['status'] }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">{{ $exp['time'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Historique des Activités -->
            <div class="bg-white rounded-xl shadow-lg p-6 mt-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">📋 Historique des Activités</h2>

                <div class="space-y-3" id="activity-history">
                    @php
                        $activities = [
                            ['time' => '14:45', 'action' => 'Boîte SFAX-TUN-28092025-03 expédiée', 'type' => 'expedition'],
                            ['time' => '14:32', 'action' => 'Réception boîte SOUSSE-TUN-27092025-02', 'type' => 'reception'],
                            ['time' => '14:15', 'action' => '24 colis scannés pour SFAX', 'type' => 'scan'],
                            ['time' => '13:58', 'action' => 'Boîte GABES-TUN-28092025-01 scellée', 'type' => 'seal'],
                            ['time' => '13:42', 'action' => 'Scanner colis pour boîte BIZERTE', 'type' => 'scan'],
                        ];
                    @endphp

                    @foreach($activities as $activity)
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 rounded-full
                            @if($activity['type'] === 'expedition') bg-yellow-500
                            @elseif($activity['type'] === 'reception') bg-green-500
                            @elseif($activity['type'] === 'scan') bg-blue-500
                            @else bg-purple-500
                            @endif
                        "></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800">{{ $activity['action'] }}</div>
                            <div class="text-xs text-gray-500">{{ $activity['time'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Scellage -->
<div id="seal-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🔒 Sceller la Boîte</h3>

            <div id="seal-details" class="mb-6">
                <!-- Sera rempli dynamiquement -->
            </div>

            <div class="flex space-x-3">
                <button id="confirm-seal" class="flex-1 bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors duration-200">
                    ✅ Confirmer & Imprimer
                </button>
                <button id="cancel-seal" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                    ❌ Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
let scannedPackages = {};
let scannedCount = 0;
let sealedCount = 0;

// Mise à jour de l'heure
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString('fr-FR');
}

// Gestion des onglets
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Retirer les classes actives
            tabButtons.forEach(tb => {
                tb.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tb.classList.add('border-transparent', 'text-gray-500');
            });
            tabContents.forEach(tc => tc.classList.add('hidden'));

            // Ajouter les classes actives
            button.classList.add('active', 'border-blue-500', 'text-blue-600');
            button.classList.remove('border-transparent', 'text-gray-500');

            // Afficher le contenu correspondant
            const targetId = button.id.replace('tab-', 'content-');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
}

// Simulation du scanner
function simulatePackageScan(packageCode) {
    return new Promise((resolve) => {
        setTimeout(() => {
            const governorates = ['SFAX', 'SOUSSE', 'GABES', 'BIZERTE', 'KAIROUAN', 'NABEUL', 'ARIANA'];
            const randomGov = governorates[Math.floor(Math.random() * governorates.length)];

            resolve({
                success: true,
                governorate: randomGov,
                packageCode: packageCode,
                timestamp: new Date().toISOString()
            });
        }, 1000);
    });
}

// Traitement du scan
async function processPackageScan() {
    const scanner = document.getElementById('package-scanner');
    const packageCode = scanner.value.trim();

    if (!packageCode) {
        showToast('Veuillez saisir un code de colis', 'error');
        return;
    }

    const feedback = document.getElementById('scan-feedback');
    const feedbackContent = document.getElementById('feedback-content');

    // Afficher le feedback de chargement
    feedback.className = 'mt-4 p-4 rounded-lg bg-blue-50 border border-blue-200';
    feedbackContent.innerHTML = '🔄 Scan en cours...';
    feedback.classList.remove('hidden');

    try {
        const result = await simulatePackageScan(packageCode);

        if (result.success) {
            // Mettre à jour les statistiques
            if (!scannedPackages[result.governorate]) {
                scannedPackages[result.governorate] = [];
            }
            scannedPackages[result.governorate].push(result);
            scannedCount++;

            // Mettre à jour l'interface
            updateGovernorateBox(result.governorate);
            updateStatistics();

            // Feedback de succès
            feedback.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200';
            feedbackContent.innerHTML = `✅ Colis scanné avec succès!<br><strong>${result.packageCode}</strong> → <strong>${result.governorate}</strong>`;

            showToast(`Colis ajouté à ${result.governorate}`, 'success');

            // Effacer le champ
            scanner.value = '';
            scanner.focus();

        } else {
            throw new Error('Erreur de scan');
        }
    } catch (error) {
        feedback.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
        feedbackContent.innerHTML = '❌ Erreur lors du scan. Veuillez réessayer.';
        showToast('Erreur lors du scan', 'error');
    }
}

// Mettre à jour l'affichage d'un gouvernorat
function updateGovernorateBox(governorate) {
    const box = document.querySelector(`[data-governorate="${governorate}"]`);
    if (!box) return;

    const count = scannedPackages[governorate] ? scannedPackages[governorate].length : 0;
    const countElement = box.querySelector('.package-count');
    const sealButton = box.querySelector('.seal-button');

    countElement.textContent = count;

    if (count > 0) {
        box.classList.add('border-blue-500', 'bg-blue-50');
        sealButton.classList.remove('opacity-0', 'pointer-events-none');
        sealButton.classList.add('opacity-100', 'pointer-events-auto');
    }

    // Animation flash
    box.classList.add('animate-pulse');
    setTimeout(() => box.classList.remove('animate-pulse'), 1000);
}

// Mettre à jour les statistiques
function updateStatistics() {
    document.getElementById('scanned-count').textContent = scannedCount;
    document.getElementById('sealed-count').textContent = sealedCount;
    document.getElementById('active-governorates').textContent = Object.keys(scannedPackages).length;
}

// Gestion du scellage
function initSealingSystem() {
    const modal = document.getElementById('seal-modal');
    const confirmButton = document.getElementById('confirm-seal');
    const cancelButton = document.getElementById('cancel-seal');
    let currentGovernorate = null;

    // Événements de scellage sur les boîtes
    document.querySelectorAll('.seal-button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const box = e.target.closest('.governorate-box');
            currentGovernorate = box.dataset.governorate;

            const packages = scannedPackages[currentGovernorate] || [];

            document.getElementById('seal-details').innerHTML = `
                <div class="text-center mb-4">
                    <div class="text-lg font-semibold text-gray-800">${currentGovernorate}</div>
                    <div class="text-3xl font-bold text-blue-600">${packages.length}</div>
                    <div class="text-sm text-gray-600">colis à sceller</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm text-gray-700">Code de boîte généré:</div>
                    <div class="font-mono text-sm font-semibold text-blue-600">${currentGovernorate}-TUN-${new Date().toISOString().slice(0,10).replace(/-/g, '')}-01</div>
                </div>
            `;

            modal.classList.remove('hidden');
        });
    });

    // Confirmer le scellage
    confirmButton.addEventListener('click', () => {
        if (currentGovernorate) {
            const packages = scannedPackages[currentGovernorate] || [];
            const boxCode = `${currentGovernorate}-TUN-${new Date().toISOString().slice(0,10).replace(/-/g, '')}-01`;

            // Simuler l'impression du bon de boîte
            showToast(`Impression du bon de boîte ${boxCode}...`, 'info');

            setTimeout(() => {
                // Réinitialiser la boîte
                delete scannedPackages[currentGovernorate];
                scannedCount -= packages.length;
                sealedCount++;

                const box = document.querySelector(`[data-governorate="${currentGovernorate}"]`);
                box.classList.remove('border-blue-500', 'bg-blue-50');
                box.querySelector('.package-count').textContent = '0';
                box.querySelector('.seal-button').classList.add('opacity-0', 'pointer-events-none');

                updateStatistics();

                showToast(`Boîte ${currentGovernorate} scellée et étiquetée!`, 'success');
                modal.classList.add('hidden');
                currentGovernorate = null;
            }, 2000);
        }
    });

    // Annuler le scellage
    cancelButton.addEventListener('click', () => {
        modal.classList.add('hidden');
        currentGovernorate = null;
    });
}

// Système de toast
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    toast.textContent = message;

    container.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);

    // Suppression après 3 secondes
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => container.removeChild(toast), 300);
    }, 3000);
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    updateTime();
    setInterval(updateTime, 1000);
    initTabs();
    initSealingSystem();

    // Événements scanner
    document.getElementById('scan-button').addEventListener('click', processPackageScan);
    document.getElementById('package-scanner').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') processPackageScan();
    });

    // Initialiser les classes des onglets
    document.getElementById('tab-preparation').classList.add('border-blue-500', 'text-blue-600');
    document.getElementById('tab-preparation').classList.remove('border-transparent', 'text-gray-500');
    document.getElementById('tab-departures').classList.add('border-transparent', 'text-gray-500');
});
</script>

<style>
.tab-button.active {
    border-color: #3b82f6;
    color: #2563eb;
}

.tab-button:not(.active) {
    border-color: transparent;
    color: #6b7280;
}

.tab-button:hover:not(.active) {
    color: #374151;
    border-color: #d1d5db;
}

.governorate-box {
    transition: all 0.3s ease;
}

.governorate-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.animate-pulse {
    animation: pulse 1s ease-in-out;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
@endsection