@extends('layouts.deliverer')

@section('title', 'Mon Profil')

@section('content')
<div x-data="delivererProfile({
    deliverer: {{ json_encode(auth()->user() ?? []) }},
    stats: {{ json_encode($stats ?? []) }}
})" class="max-w-4xl mx-auto p-4 space-y-6">

    <!-- Profile Header avec pale purple -->
    <div class="bg-gradient-to-r from-purple-200 to-purple-300 rounded-xl p-6 text-purple-800">
        <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Avatar -->
            <div class="relative">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <template x-if="deliverer.avatar">
                        <img :src="deliverer.avatar" :alt="deliverer.name" class="w-24 h-24 rounded-full object-cover">
                    </template>
                    <template x-if="!deliverer.avatar">
                        <i class="fas fa-user text-purple-600 text-3xl"></i>
                    </template>
                </div>
                <button @click="changeAvatar" class="absolute bottom-0 right-0 bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-yellow-600 transition-colors">
                    <i class="fas fa-camera text-sm"></i>
                </button>
            </div>

            <!-- Basic Info -->
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-2xl font-bold mb-2" x-text="deliverer.name || 'N/A'"></h1>
                <p class="text-purple-600 mb-1">
                    <i class="fas fa-id-badge mr-2"></i>
                    ID: <span x-text="deliverer.deliverer_id || deliverer.id || 'N/A'"></span>
                </p>
                <p class="text-purple-600 mb-1">
                    <i class="fas fa-phone mr-2"></i>
                    <span x-text="deliverer.phone || 'N/A'"></span>
                </p>
                <p class="text-purple-600">
                    <i class="fas fa-envelope mr-2"></i>
                    <span x-text="deliverer.email || 'N/A'"></span>
                </p>
            </div>

            <!-- Status Badge -->
            <div class="text-center">
                <div class="bg-green-500 text-white px-4 py-2 rounded-lg mb-2">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span x-text="getStatusText()"></span>
                </div>
                <p class="text-purple-600 text-sm">
                    Membre depuis <span x-text="formatDate(deliverer.created_at)"></span>
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-box text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Colis livrés</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.total_deliveries || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-star text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Note moyenne</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="(stats.average_rating || 0).toFixed(1)"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-wallet text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Solde</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="formatAmount(stats.wallet_balance || 0)"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-calendar text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Jours actifs</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="stats.active_days || 0"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-user mr-2 text-purple-600"></i>
                    Informations personnelles
                </h2>
                <button @click="editPersonalInfo = !editPersonalInfo"
                        class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    <i :class="editPersonalInfo ? 'fas fa-times' : 'fas fa-edit'" class="mr-1"></i>
                    <span x-text="editPersonalInfo ? 'Annuler' : 'Modifier'"></span>
                </button>
            </div>

            <form @submit.prevent="updatePersonalInfo" x-show="editPersonalInfo" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                        <input type="text" x-model="editData.name"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <input type="tel" x-model="editData.phone"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" x-model="editData.email"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                        <input type="date" x-model="editData.birth_date"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                    <textarea x-model="editData.address" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                            class="bg-purple-300 text-purple-800 px-4 py-2 rounded-lg hover:bg-purple-400 transition-colors">
                        Enregistrer
                    </button>
                    <button type="button" @click="editPersonalInfo = false"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>

            <div x-show="!editPersonalInfo" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Nom complet :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.name || 'Non renseigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Téléphone :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.phone || 'Non renseigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Email :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.email || 'Non renseigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Date de naissance :</span>
                        <p class="font-medium text-gray-900" x-text="formatDate(deliverer.birth_date) || 'Non renseigné'"></p>
                    </div>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Adresse :</span>
                    <p class="font-medium text-gray-900" x-text="deliverer.address || 'Non renseigné'"></p>
                </div>
            </div>
        </div>

        <!-- Work Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-briefcase mr-2 text-green-600"></i>
                Informations professionnelles
            </h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Statut :</span>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold"
                              :class="getStatusColor()">
                            <span x-text="getStatusText()"></span>
                        </span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Zone assignée :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.zone || 'Non assigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Véhicule :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.vehicle_type || 'Non renseigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Immatriculation :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.vehicle_plate || 'Non renseigné'"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Date d'embauche :</span>
                        <p class="font-medium text-gray-900" x-text="formatDate(deliverer.hired_at || deliverer.created_at)"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Superviseur :</span>
                        <p class="font-medium text-gray-900" x-text="deliverer.supervisor_name || 'Non assigné'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                Documents
            </h2>

            <div class="space-y-4">
                <template x-for="doc in documents" :key="doc.type">
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 rounded-lg" :class="doc.verified ? 'bg-green-100' : 'bg-yellow-100'">
                                <i :class="doc.icon + (doc.verified ? ' text-green-600' : ' text-yellow-600')"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="doc.name"></p>
                                <p class="text-sm text-gray-500" x-text="doc.status"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <template x-if="doc.verified">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </template>
                            <template x-if="!doc.verified">
                                <button @click="uploadDocument(doc.type)"
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    Télécharger
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Preferences -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">
                <i class="fas fa-cog mr-2 text-gray-600"></i>
                Préférences
            </h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Notifications push</p>
                        <p class="text-sm text-gray-500">Recevoir les notifications sur votre appareil</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.push_notifications" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Notifications email</p>
                        <p class="text-sm text-gray-500">Recevoir les résumés par email</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.email_notifications" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">Mode sombre</p>
                        <p class="text-sm text-gray-500">Interface en thème sombre</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.dark_mode" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <button @click="savePreferences"
                        class="w-full bg-purple-300 text-purple-800 px-4 py-2 rounded-lg hover:bg-purple-400 transition-colors">
                    Enregistrer les préférences
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-bolt mr-2 text-yellow-600"></i>
            Actions rapides
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('deliverer.profile.statistics') }}"
               class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-chart-bar text-blue-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Mes statistiques</p>
                    <p class="text-sm text-gray-500">Voir mes performances</p>
                </div>
            </a>

            <button @click="changePassword"
                    class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-lock text-red-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Changer mot de passe</p>
                    <p class="text-sm text-gray-500">Sécuriser mon compte</p>
                </div>
            </button>

            <button @click="downloadData"
                    class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-download text-green-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Exporter mes données</p>
                    <p class="text-sm text-gray-500">Télécharger mes infos</p>
                </div>
            </button>
        </div>
    </div>

    <!-- File Upload Modal -->
    <div x-show="showUploadModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
         @click.away="showUploadModal = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Télécharger un document</h3>
            <form @submit.prevent="submitDocument">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichier</label>
                    <input type="file" @change="selectedFile = $event.target.files[0]"
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Télécharger
                    </button>
                    <button type="button" @click="showUploadModal = false"
                            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('delivererProfile', (data) => ({
        deliverer: data.deliverer || {},
        stats: data.stats || {},
        editPersonalInfo: false,
        showUploadModal: false,
        selectedFile: null,
        documentType: '',

        editData: {},
        preferences: {
            push_notifications: true,
            email_notifications: true,
            dark_mode: false
        },

        documents: [
            {
                type: 'id_card',
                name: 'Carte d\'identité',
                icon: 'fas fa-id-card',
                verified: true,
                status: 'Vérifié'
            },
            {
                type: 'driving_license',
                name: 'Permis de conduire',
                icon: 'fas fa-id-badge',
                verified: false,
                status: 'En attente'
            },
            {
                type: 'vehicle_registration',
                name: 'Carte grise',
                icon: 'fas fa-car',
                verified: false,
                status: 'Non fourni'
            }
        ],

        init() {
            this.editData = { ...this.deliverer };
            this.loadPreferences();
        },

        formatDate(dateString) {
            if (!dateString) return 'Non renseigné';
            return new Date(dateString).toLocaleDateString('fr-DZ');
        },

        formatAmount(amount) {
            if (!amount) return '0 DA';
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 0
            }).format(amount).replace('DZD', 'DA');
        },

        getStatusText() {
            const status = this.deliverer.status || 'active';
            const statuses = {
                active: 'Actif',
                inactive: 'Inactif',
                suspended: 'Suspendu',
                pending: 'En attente'
            };
            return statuses[status] || 'Actif';
        },

        getStatusColor() {
            const status = this.deliverer.status || 'active';
            const colors = {
                active: 'bg-green-100 text-green-800',
                inactive: 'bg-gray-100 text-gray-800',
                suspended: 'bg-red-100 text-red-800',
                pending: 'bg-yellow-100 text-yellow-800'
            };
            return colors[status] || 'bg-green-100 text-green-800';
        },

        async updatePersonalInfo() {
            try {
                const response = await fetch('/deliverer/profile/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.editData)
                });

                const data = await response.json();

                if (data.success) {
                    this.deliverer = { ...this.deliverer, ...this.editData };
                    this.editPersonalInfo = false;
                    this.showToast('Profil mis à jour avec succès', 'success');
                } else {
                    this.showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                this.showToast('Erreur lors de la mise à jour', 'error');
            }
        },

        async savePreferences() {
            try {
                const response = await fetch('/deliverer/profile/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.preferences)
                });

                const data = await response.json();

                if (data.success) {
                    this.showToast('Préférences enregistrées', 'success');
                } else {
                    this.showToast('Erreur lors de l\'enregistrement', 'error');
                }
            } catch (error) {
                this.showToast('Erreur lors de l\'enregistrement', 'error');
            }
        },

        loadPreferences() {
            const saved = localStorage.getItem('deliverer-preferences');
            if (saved) {
                this.preferences = { ...this.preferences, ...JSON.parse(saved) };
            }
        },

        changeAvatar() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.uploadAvatar(file);
                }
            };
            input.click();
        },

        async uploadAvatar(file) {
            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await fetch('/deliverer/profile/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.deliverer.avatar = data.avatar_url;
                    this.showToast('Photo de profil mise à jour', 'success');
                } else {
                    this.showToast('Erreur lors du téléchargement', 'error');
                }
            } catch (error) {
                this.showToast('Erreur lors du téléchargement', 'error');
            }
        },

        uploadDocument(type) {
            this.documentType = type;
            this.showUploadModal = true;
        },

        async submitDocument() {
            if (!this.selectedFile) return;

            const formData = new FormData();
            formData.append('document', this.selectedFile);
            formData.append('type', this.documentType);

            try {
                const response = await fetch('/deliverer/profile/documents', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showUploadModal = false;
                    this.selectedFile = null;
                    this.showToast('Document téléchargé avec succès', 'success');

                    // Update document status
                    const doc = this.documents.find(d => d.type === this.documentType);
                    if (doc) {
                        doc.status = 'En cours de vérification';
                    }
                } else {
                    this.showToast('Erreur lors du téléchargement', 'error');
                }
            } catch (error) {
                this.showToast('Erreur lors du téléchargement', 'error');
            }
        },

        changePassword() {
            // Redirect to password change page or open modal
            window.location.href = '/deliverer/profile/password';
        },

        async downloadData() {
            try {
                const response = await fetch('/deliverer/profile/export', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `mes-donnees-${this.deliverer.name || 'livreur'}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);

                this.showToast('Données exportées avec succès', 'success');
            } catch (error) {
                this.showToast('Erreur lors de l\'export', 'error');
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }));
});
</script>
@endpush