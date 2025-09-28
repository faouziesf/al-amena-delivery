@extends('layouts.depot-manager')

@section('title', 'Tableau de Bord - Actions Requises')

@section('content')
<style>
.action-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.action-card.exchange {
    border-left-color: #f59e0b;
    background: linear-gradient(135deg, #fef3c7 0%, #fff 100%);
}
.action-card.return {
    border-left-color: #ef4444;
    background: linear-gradient(135deg, #fee2e2 0%, #fff 100%);
}
.badge-urgent {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.search-input {
    transition: all 0.3s ease;
}
.search-input:focus {
    transform: scale(1.02);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-orange-50">
    <!-- Header Principal -->
    <div class="bg-white shadow-lg border-b border-slate-200 mb-8">
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Tableau de Bord - Actions Requises</h1>
                        <p class="text-slate-500">Gestion des échanges et retours - {{ auth()->user()->name }}</p>
                    </div>
                </div>
                <div class="text-right text-sm text-slate-500">
                    <p>{{ now()->format('d/m/Y') }}</p>
                    <p class="font-medium">{{ now()->format('H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 space-y-8">

        <!-- TÂCHE 1: Gestion des Échanges -->
        <div class="action-card exchange rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">🔄 Gestion des Échanges</h2>
                        <p class="text-slate-600 text-sm">Traiter les colis d'échange ramenés par les livreurs</p>
                    </div>
                </div>
                <div class="badge-urgent bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-medium">
                    Action Immédiate
                </div>
            </div>

            <!-- Zone de Recherche pour Échanges -->
            <div class="bg-white rounded-xl p-6 border border-amber-200">
                <form id="exchangeSearchForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            📦 Numéro de Suivi du Colis d'Origine
                        </label>
                        <div class="flex space-x-3">
                            <input type="text"
                                   id="exchangePackageCode"
                                   class="search-input flex-1 px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ex: PKG-2024-001234"
                                   autocomplete="off">
                            <button type="submit"
                                    class="px-6 py-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors font-medium">
                                🔍 Rechercher
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Résultats de Recherche -->
                <div id="exchangeResults" class="hidden mt-6 p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-slate-900">Commande Trouvée</h3>
                            <p class="text-sm text-slate-600" id="exchangePackageInfo"></p>
                        </div>
                        <button onclick="generateExchangeLabel()"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium">
                            🏷️ Générer Étiquette Retour
                        </button>
                    </div>
                </div>

                <!-- Message d'erreur -->
                <div id="exchangeError" class="hidden mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <p class="text-red-600 text-sm font-medium">❌ Commande non trouvée ou non éligible pour échange</p>
                </div>
            </div>
        </div>

        <!-- TÂCHE 2: Retours en Attente -->
        <div class="action-card return rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">📋 Retours à Traiter</h2>
                        <p class="text-slate-600 text-sm">Colis en attente de traitement retour</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ $pendingReturns->count() }} en attente
                    </span>
                    @if($pendingReturns->count() > 0)
                    <button onclick="processAllReturns()"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium">
                        Traiter Tout
                    </button>
                    @endif
                </div>
            </div>

            <!-- Liste des Retours -->
            <div class="bg-white rounded-xl border border-red-200">
                @forelse($pendingReturns as $package)
                <div class="border-b border-slate-100 last:border-b-0 p-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <div>
                                    <h3 class="font-medium text-slate-900">{{ $package->package_code }}</h3>
                                    <p class="text-sm text-slate-600">
                                        {{ $package->content_description ?? 'Contenu non spécifié' }} |
                                        COD: {{ number_format($package->cod_amount, 3) }} DT
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center space-x-4 text-xs text-slate-500">
                                <span>👤 {{ $package->recipient_data['name'] ?? 'N/A' }}</span>
                                <span>📱 {{ $package->recipient_data['phone'] ?? 'N/A' }}</span>
                                <span>📍 {{ $package->delegationTo->name ?? 'N/A' }}</span>
                                @if($package->delivery_attempts >= 3)
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full">3 tentatives échouées</span>
                                @else
                                    <span class="bg-orange-100 text-orange-600 px-2 py-1 rounded-full">Annulé par client</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="confirmReturn('{{ $package->id }}', '{{ $package->package_code }}')"
                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium">
                                🏷️ Traiter Retour
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">✅ Aucun retour en attente</h3>
                    <p class="text-slate-600">Tous les retours ont été traités</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Modal de Confirmation pour Retours -->
<div id="confirmReturnModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Confirmer le Traitement</h3>
            <p class="text-slate-600 mb-6">Êtes-vous sûr de vouloir traiter ce colis en retour ?</p>

            <div id="confirmPackageDetails" class="bg-slate-50 rounded-lg p-4 mb-6 text-left">
                <!-- Détails du colis injectés par JS -->
            </div>

            <div class="flex space-x-3">
                <button onclick="closeConfirmModal()"
                        class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    Annuler
                </button>
                <button id="confirmReturnBtn"
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium">
                    🏷️ Confirmer & Imprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion de la recherche d'échanges
document.getElementById('exchangeSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const packageCode = document.getElementById('exchangePackageCode').value.trim();

    if (!packageCode) {
        alert('Veuillez saisir un numéro de colis');
        return;
    }

    // Simulation de recherche (à remplacer par appel AJAX)
    searchExchangePackage(packageCode);
});

function searchExchangePackage(packageCode) {
    // Masquer les résultats précédents
    document.getElementById('exchangeResults').classList.add('hidden');
    document.getElementById('exchangeError').classList.add('hidden');

    // Appel AJAX vers le contrôleur
    fetch(`{{ route('depot-manager.packages.search-exchange') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ package_code: packageCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('exchangePackageInfo').textContent =
                `${data.package.package_code} - ${data.package.content_description} - ${data.package.cod_amount} DT`;
            document.getElementById('exchangeResults').classList.remove('hidden');

            // Stocker les données pour génération étiquette
            window.currentExchangePackage = data.package;
        } else {
            document.getElementById('exchangeError').classList.remove('hidden');
        }
    })
    .catch(() => {
        document.getElementById('exchangeError').classList.remove('hidden');
    });
}

function generateExchangeLabel() {
    if (!window.currentExchangePackage) return;

    // Rediriger vers génération d'étiquette
    const url = `{{ route('depot-manager.packages.generate-exchange-label', '__PACKAGE_ID__') }}`.replace('__PACKAGE_ID__', window.currentExchangePackage.id);
    window.open(url, '_blank');
}

// Gestion des confirmations de retour
function confirmReturn(packageId, packageCode) {
    // Récupérer les détails du colis et afficher modal
    fetch(`{{ route('depot-manager.packages.details', '__PACKAGE_ID__') }}`.replace('__PACKAGE_ID__', packageId))
    .then(response => response.json())
    .then(data => {
        document.getElementById('confirmPackageDetails').innerHTML = `
            <div class="space-y-2 text-sm">
                <p><strong>Code:</strong> ${data.package_code}</p>
                <p><strong>Contenu:</strong> ${data.content_description || 'N/A'}</p>
                <p><strong>Montant COD:</strong> ${parseFloat(data.cod_amount).toFixed(3)} DT</p>
                <p><strong>Destinataire:</strong> ${data.recipient_data.name || 'N/A'}</p>
                <p><strong>Téléphone:</strong> ${data.recipient_data.phone || 'N/A'}</p>
            </div>
        `;

        document.getElementById('confirmReturnBtn').onclick = () => processReturn(packageId);
        document.getElementById('confirmReturnModal').classList.remove('hidden');
    });
}

function closeConfirmModal() {
    document.getElementById('confirmReturnModal').classList.add('hidden');
}

function processReturn(packageId) {
    // Traiter le retour et générer l'étiquette
    fetch(`{{ route('depot-manager.packages.process-return-dashboard') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ package_id: packageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fermer modal et ouvrir impression
            closeConfirmModal();
            window.open(data.print_url, '_blank');

            // Recharger la page pour mettre à jour la liste
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Erreur lors du traitement: ' + data.message);
        }
    });
}

function processAllReturns() {
    if (confirm('Êtes-vous sûr de vouloir traiter tous les retours en attente ?')) {
        // Traitement en lot
        fetch(`{{ route('depot-manager.packages.process-all-returns') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(`${data.processed} retours traités avec succès`);
            location.reload();
        });
    }
}
</script>

@endsection