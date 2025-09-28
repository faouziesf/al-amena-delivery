@extends('layouts.supervisor')

@section('title', 'Gestion des sauvegardes')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestion des sauvegardes</h1>
                    <p class="text-gray-600">Créer, gérer et restaurer les sauvegardes du système</p>
                </div>
                <div class="mt-4 lg:mt-0">
                    <a href="{{ route('supervisor.system.overview') }}" class="bg-white px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors mr-3">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour
                    </a>
                    <button onclick="createBackup()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nouvelle sauvegarde
                    </button>
                </div>
            </div>
        </div>

        <!-- Backup Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Sauvegardes totales</h3>
                        <div class="text-2xl font-bold text-blue-600 mt-2">24</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-archive text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Taille totale</h3>
                        <div class="text-2xl font-bold text-green-600 mt-2">2.4 GB</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-hdd text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Dernière sauvegarde</h3>
                        <div class="text-lg font-bold text-gray-900 mt-2">{{ now()->subHours(6)->format('H:i') }}</div>
                        <div class="text-xs text-gray-500">{{ now()->subHours(6)->format('d/m/Y') }}</div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Sauvegarde auto</h3>
                        <div class="flex items-center mt-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                            <span class="text-lg font-semibold text-gray-900">Activée</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-sync text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Create New Backup -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Créer une nouvelle sauvegarde</h3>

                <form action="{{ route('supervisor.system.backup.create') }}" method="POST" id="backupForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type de sauvegarde</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="backup_types[]" value="database" checked class="mr-3 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Base de données</div>
                                        <div class="text-xs text-gray-500">Sauvegarde complète de la DB</div>
                                    </div>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="backup_types[]" value="files" class="mr-3 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Fichiers système</div>
                                        <div class="text-xs text-gray-500">Code source et configurations</div>
                                    </div>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="backup_types[]" value="uploads" class="mr-3 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Fichiers uploadés</div>
                                        <div class="text-xs text-gray-500">Documents et images des utilisateurs</div>
                                    </div>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="backup_types[]" value="logs" class="mr-3 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Logs système</div>
                                        <div class="text-xs text-gray-500">Journaux d'événements</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="backup_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la sauvegarde (optionnel)
                            </label>
                            <input type="text" name="backup_name" id="backup_name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="backup_{{ now()->format('Y_m_d_H_i') }}">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Sauvegarde manuelle créée le {{ now()->format('d/m/Y') }}"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="compress" id="compress" checked class="mr-2 text-blue-600 focus:ring-blue-500">
                            <label for="compress" class="text-sm text-gray-700">
                                Compresser la sauvegarde (recommandé)
                            </label>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <i class="fas fa-play mr-2"></i>
                            Créer la sauvegarde
                        </button>
                    </div>
                </form>
            </div>

            <!-- Backup Settings -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Paramètres automatiques</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Sauvegarde automatique</div>
                            <div class="text-xs text-gray-500">Exécutée quotidiennement à 2h00</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Nettoyage automatique</div>
                            <div class="text-xs text-gray-500">Supprime les sauvegardes > 30 jours</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Notification par email</div>
                            <div class="text-xs text-gray-500">En cas d'échec de sauvegarde</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <hr class="my-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fréquence</label>
                        <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="daily" selected>Quotidienne</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly">Mensuelle</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Heure d'exécution</label>
                        <input type="time" value="02:00"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Conservation (jours)</label>
                        <input type="number" value="30" min="1" max="365"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <button class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Sauvegarder les paramètres
                    </button>
                </div>
            </div>
        </div>

        <!-- Backup List -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Sauvegardes disponibles</h3>
                <button onclick="refreshBackups()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    <i class="fas fa-sync mr-1"></i>
                    Actualiser
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Nom</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Type</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Taille</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Statut</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 px-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">backup_2024_03_15_02_00</div>
                                    <div class="text-xs text-gray-500">Sauvegarde automatique quotidienne</div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    Complète
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">15/03/2024 02:00</td>
                            <td class="py-3 px-4 text-sm text-gray-600">125 MB</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Réussie
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="downloadBackup('backup_2024_03_15_02_00')"
                                        class="text-blue-600 hover:text-blue-700" title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button onclick="restoreBackup('backup_2024_03_15_02_00')"
                                        class="text-green-600 hover:text-green-700" title="Restaurer">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button onclick="deleteBackup('backup_2024_03_15_02_00')"
                                        class="text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">backup_2024_03_14_02_00</div>
                                    <div class="text-xs text-gray-500">Sauvegarde automatique quotidienne</div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    Complète
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">14/03/2024 02:00</td>
                            <td class="py-3 px-4 text-sm text-gray-600">120 MB</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Réussie
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="downloadBackup('backup_2024_03_14_02_00')"
                                        class="text-blue-600 hover:text-blue-700" title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button onclick="restoreBackup('backup_2024_03_14_02_00')"
                                        class="text-green-600 hover:text-green-700" title="Restaurer">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button onclick="deleteBackup('backup_2024_03_14_02_00')"
                                        class="text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #3b82f6;
}

input:checked + .slider:before {
    transform: translateX(20px);
}

.slider.round {
    border-radius: 20px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>

<script>
function createBackup() {
    document.getElementById('backupForm').submit();
}

function refreshBackups() {
    location.reload();
}

function downloadBackup(name) {
    window.location.href = `/supervisor/system/backup/download/${name}`;
}

function restoreBackup(name) {
    if (confirm(`Êtes-vous sûr de vouloir restaurer la sauvegarde "${name}" ? Cette action peut prendre du temps et le site sera temporairement indisponible.`)) {
        fetch(`{{ route('supervisor.system.backup.restore', '') }}/${name}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Restauration initiée avec succès');
                location.reload();
            } else {
                alert('Erreur lors de la restauration: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de connexion');
        });
    }
}

function deleteBackup(name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la sauvegarde "${name}" ? Cette action est irréversible.`)) {
        fetch(`{{ route('supervisor.system.backup.delete', '') }}/${name}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sauvegarde supprimée avec succès');
                location.reload();
            } else {
                alert('Erreur lors de la suppression: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de connexion');
        });
    }
}
</script>
@endsection