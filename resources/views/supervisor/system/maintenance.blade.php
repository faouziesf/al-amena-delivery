@extends('layouts.supervisor')

@section('title', 'Mode Maintenance')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Mode Maintenance</h1>
                    <p class="text-gray-600">Gestion du mode maintenance du système</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <a href="{{ route('supervisor.system.overview') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour à la vue d'ensemble
                    </a>
                </div>
            </div>
        </div>

        <!-- Current Status -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mr-6">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Système actif</h2>
                        <p class="text-gray-600">L'application est actuellement en ligne et accessible aux utilisateurs</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Statut depuis</div>
                    <div class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Enable Maintenance -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Activer le mode maintenance</h3>
                    <p class="text-gray-600 text-sm">Rend l'application inaccessible aux utilisateurs finaux</p>
                </div>

                <form action="{{ route('supervisor.system.maintenance.enable') }}" method="POST" onsubmit="return confirmMaintenance()">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                Message à afficher aux utilisateurs
                            </label>
                            <textarea name="message" id="message" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Le système est temporairement en maintenance...">Le système est temporairement en maintenance pour des améliorations. Merci de votre patience.</textarea>
                        </div>

                        <div>
                            <label for="secret" class="block text-sm font-medium text-gray-700 mb-1">
                                Code d'accès d'urgence (optionnel)
                            </label>
                            <input type="text" name="secret" id="secret"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Code pour accès d'urgence">
                        </div>

                        <div>
                            <label for="allowed_ips" class="block text-sm font-medium text-gray-700 mb-1">
                                IPs autorisées (séparées par des virgules)
                            </label>
                            <input type="text" name="allowed_ips" id="allowed_ips"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="192.168.1.1, 10.0.0.1"
                                value="{{ request()->ip() }}">
                            <p class="text-xs text-gray-500 mt-1">Votre IP actuelle: {{ request()->ip() }}</p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="retry_after" id="retry_after" value="3600"
                                class="mr-2 text-blue-600 focus:ring-blue-500">
                            <label for="retry_after" class="text-sm text-gray-700">
                                Ajouter un header Retry-After (1 heure)
                            </label>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium">
                            <i class="fas fa-tools mr-2"></i>
                            Activer le mode maintenance
                        </button>
                    </div>
                </form>
            </div>

            <!-- Maintenance Checklist -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Checklist de maintenance</h3>
                    <p class="text-gray-600 text-sm">Actions recommandées avant la maintenance</p>
                </div>

                <div class="space-y-4">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Sauvegarder la base de données</div>
                            <div class="text-xs text-gray-500">Créer une sauvegarde récente des données</div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Notifier les utilisateurs</div>
                            <div class="text-xs text-gray-500">Informer en avance de la maintenance</div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Vérifier les files d'attente</div>
                            <div class="text-xs text-gray-500">S'assurer qu'aucune tâche critique n'est en cours</div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Planifier la durée</div>
                            <div class="text-xs text-gray-500">Estimer le temps de maintenance nécessaire</div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Préparer un plan de rollback</div>
                            <div class="text-xs text-gray-500">En cas de problème durant la maintenance</div>
                        </div>
                    </label>
                </div>

                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
                        <div class="text-sm text-yellow-700">
                            <strong>Important:</strong> Une fois en mode maintenance, seuls les administrateurs avec les bonnes permissions pourront accéder au système.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Maintenance History -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Historique des maintenances</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Durée</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Raison</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Utilisateur</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-600">15/03/2024 02:00</td>
                            <td class="py-3 px-4 text-sm text-gray-600">45 min</td>
                            <td class="py-3 px-4 text-sm text-gray-600">Mise à jour système</td>
                            <td class="py-3 px-4 text-sm text-gray-600">Admin</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Terminée
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 text-sm text-gray-600">08/03/2024 01:30</td>
                            <td class="py-3 px-4 text-sm text-gray-600">1h 20min</td>
                            <td class="py-3 px-4 text-sm text-gray-600">Migration base de données</td>
                            <td class="py-3 px-4 text-sm text-gray-600">Admin</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Terminée
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmMaintenance() {
    return confirm('Êtes-vous sûr de vouloir activer le mode maintenance ? Cette action rendra le site inaccessible aux utilisateurs.');
}
</script>
@endsection