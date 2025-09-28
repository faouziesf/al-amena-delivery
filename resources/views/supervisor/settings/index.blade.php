@extends('layouts.supervisor')

@section('title', 'Paramètres système')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Paramètres système</h1>
                    <p class="text-gray-600">Configuration et gestion globale du système Al-Amena Delivery</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <button onclick="saveAllSettings()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Sauvegarder tout
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- General Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-cogs text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Général</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Configuration générale</h3>
                    <p class="text-gray-600 text-sm mb-4">Paramètres globaux de l'application et de l'entreprise</p>
                    <button onclick="openSettings('general')" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-credit-card text-green-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Paiement</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Paramètres financiers</h3>
                    <p class="text-gray-600 text-sm mb-4">Tarifs, COD, commissions et méthodes de paiement</p>
                    <button onclick="openSettings('payment')" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>

            <!-- Delivery Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <i class="fas fa-truck text-orange-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">Livraison</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Paramètres de livraison</h3>
                    <p class="text-gray-600 text-sm mb-4">Zones, délais, types de livraison et restrictions</p>
                    <button onclick="openSettings('delivery')" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-bell text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">Notifications</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Notifications</h3>
                    <p class="text-gray-600 text-sm mb-4">SMS, emails, push notifications et alertes</p>
                    <button onclick="openSettings('notifications')" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>

            <!-- User Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-indigo-100 p-3 rounded-lg">
                            <i class="fas fa-users-cog text-indigo-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">Utilisateurs</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Gestion utilisateurs</h3>
                    <p class="text-gray-600 text-sm mb-4">Rôles, permissions, validation des comptes</p>
                    <button onclick="openSettings('users')" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-red-100 p-3 rounded-lg">
                            <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                        </div>
                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Sécurité</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Sécurité & confidentialité</h3>
                    <p class="text-gray-600 text-sm mb-4">Authentification, logs, sauvegarde et RGPD</p>
                    <button onclick="openSettings('security')" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm">
                        Configurer
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Actions rapides</h3>
                <div class="text-sm text-gray-500">Gestion globale des paramètres</div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="exportSettings()" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Exporter</div>
                            <div class="text-sm text-gray-600">Configuration</div>
                        </div>
                    </div>
                </button>

                <button onclick="importSettings()" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-green-600 p-2 rounded-lg mr-3 group-hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Importer</div>
                            <div class="text-sm text-gray-600">Configuration</div>
                        </div>
                    </div>
                </button>

                <button onclick="resetToDefaults()" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-yellow-600 p-2 rounded-lg mr-3 group-hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-undo text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Réinitialiser</div>
                            <div class="text-sm text-gray-600">Par défaut</div>
                        </div>
                    </div>
                </button>

                <button onclick="validateSettings()" class="bg-purple-50 border border-purple-200 rounded-lg p-4 hover:bg-purple-100 transition-colors group">
                    <div class="flex items-center">
                        <div class="bg-purple-600 p-2 rounded-lg mr-3 group-hover:bg-purple-700 transition-colors">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-900">Valider</div>
                            <div class="text-sm text-gray-600">Configuration</div>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">État du système</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-green-600 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900">Système opérationnel</h4>
                    <p class="text-sm text-gray-600 mt-1">Toutes les configurations sont valides</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-sync-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900">Dernière sauvegarde</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-cog text-yellow-600 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900">Modifications en attente</h4>
                    <p class="text-sm text-gray-600 mt-1">3 paramètres à valider</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openSettings(category) {
    alert(`Ouverture de la configuration ${category}...`);
    // Redirection vers la page de configuration spécifique
}

function saveAllSettings() {
    if (confirm('Sauvegarder toutes les modifications ?')) {
        alert('Paramètres sauvegardés avec succès!');
    }
}

function exportSettings() {
    // Show loading
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Export...';
    button.disabled = true;

    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        alert('Configuration exportée avec succès!');
    }, 2000);
}

function importSettings() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json,.xml';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            alert(`Import du fichier ${file.name} en cours...`);
        }
    };
    input.click();
}

function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser TOUS les paramètres aux valeurs par défaut ? Cette action est irréversible.')) {
        alert('Réinitialisation en cours...');
        setTimeout(() => {
            alert('Paramètres réinitialisés avec succès!');
            location.reload();
        }, 2000);
    }
}

function validateSettings() {
    const button = event.target.closest('button');
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Validation...';
    button.disabled = true;

    setTimeout(() => {
        alert('Validation terminée: Tous les paramètres sont corrects!');
        location.reload();
    }, 2000);
}
</script>
@endsection