@extends('layouts.client')

@section('title', 'Détail du Manifeste - ' . $manifest->manifest_number)

@section('content')
<div x-data="manifestShowApp()" class="container mx-auto px-4 py-6">
    <!-- En-tête avec breadcrumb -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('client.manifests.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Manifestes
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $manifest->manifest_number }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl font-bold text-gray-900">{{ $manifest->manifest_number }}</h1>
                <p class="text-gray-600 mt-2">Consultation détaillée du manifeste</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('client.manifests.print', $manifest->id) }}" target="_blank"
                   class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                    </svg>
                    Imprimer le Manifeste
                </a>
                <a href="{{ route('client.manifests.download-pdf', $manifest->id) }}" target="_blank"
                   class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Télécharger PDF
                </a>
                <button x-show="canDeleteManifest" @click="confirmDelete"
                        class="inline-flex items-center px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Supprimer le Manifeste
                </button>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Informations principales du manifeste -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Informations générales -->
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $manifest->manifest_number }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h8a2 2 0 012 2v4m-6 9l3-3-3-3"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Date de création</div>
                                    <div class="text-sm text-gray-900">{{ $manifest->generated_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Adresse de pickup</div>
                                    <div class="text-sm text-gray-900">{{ $manifest->pickup_address_name }}</div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-500">Contact</div>
                                    <div class="text-sm text-gray-900">{{ $manifest->pickup_phone }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-500">Statut:</div>
                                <span :class="getStatusBadgeClass(manifestData.status_badge)" class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" x-text="manifestData.status_badge.text"></span>
                            </div>
                            @if($manifest->pickupRequest)
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-500">Demande de pickup:</div>
                                <a href="{{ route('client.pickup-requests.show', $manifest->pickupRequest->id) }}"
                                   class="ml-2 text-indigo-600 hover:text-indigo-900 text-sm">
                                    Voir la demande
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h3>
                    <div class="space-y-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $manifest->total_packages }}</div>
                            <div class="text-sm text-gray-600">colis au total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ number_format($manifest->total_cod_amount, 3) }} DT</div>
                            <div class="text-sm text-gray-600">montant COD total</div>
                        </div>
                        @if($manifest->total_weight)
                        <div class="text-center">
                            <div class="text-xl font-bold text-gray-900">{{ number_format($manifest->total_weight, 1) }} kg</div>
                            <div class="text-sm text-gray-600">poids total</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Ajouter des colis (si manifeste modifiable) -->
    <div x-show="canModifyManifest" class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Ajouter des colis
            </h2>
        </div>
        <div class="p-6">
            <div class="flex gap-3">
                <select x-model="selectedPackageToAdd" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sélectionnez un colis à ajouter</option>
                    <template x-for="pkg in availablePackages" :key="pkg.id">
                        <option :value="pkg.id" x-text="`${pkg.package_code} - ${pkg.recipient_name} (${pkg.cod_amount} DT)`"></option>
                    </template>
                </select>
                <button @click="addPackageToManifest" :disabled="!selectedPackageToAdd || addingPackage"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="addingPackage" class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                        Ajout...
                    </span>
                    <span x-show="!addingPackage">Ajouter au manifeste</span>
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-2">
                Seuls les colis non collectés peuvent être ajoutés. Une fois qu'un colis est collecté, le manifeste ne peut plus être modifié.
            </p>
        </div>
    </div>

    <!-- Liste des colis -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Colis inclus dans le manifeste</h2>
                <div class="text-sm text-gray-600">
                    {{ $packages->count() }} colis •
                    {{ $packages->where('status', 'AVAILABLE')->count() }} disponibles •
                    {{ $packages->where('status', 'PICKED_UP')->count() }} ramassés
                </div>
            </div>
        </div>

        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="ml-2 text-gray-600">Chargement...</span>
        </div>

        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            N° de Suivi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Destinataire
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Délégation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contenu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Montant COD
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($packages as $package)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4-4-4m-6 4l4 4 4-4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $package->package_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ is_array($package->recipient_data) ? ($package->recipient_data['name'] ?? 'N/A') : 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ is_array($package->recipient_data) ? ($package->recipient_data['phone'] ?? 'N/A') : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ optional($package->delegationTo)->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $package->content_description }}">
                                    {{ $package->content_description }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($package->cod_amount, 3) }} DT</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="{{ $package->status === 'AVAILABLE' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }} inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    {{ $package->status === 'AVAILABLE' ? 'Disponible' : 'Ramassé' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <!-- Bouton Retirer (seulement si statut AVAILABLE) -->
                                @if($package->status === 'AVAILABLE')
                                    <div class="flex items-center justify-end">
                                        <button @click="removePackage({{ $package->id }}, '{{ $package->package_code }}')"
                                                class="text-red-600 hover:text-red-900 p-2 rounded-md hover:bg-red-50 transition-colors"
                                                title="Retirer du manifeste">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <!-- Cadenas si colis ramassé -->
                                    <div class="flex items-center justify-end text-gray-400" title="Ce colis a déjà été ramassé">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($packages->isEmpty())
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8l-4 4-4-4m-6 4l4 4 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Aucun colis dans ce manifeste</h3>
                <p class="text-gray-600">Tous les colis de ce manifeste ont été retirés</p>
            </div>
        @endif
    </div>

    <!-- Modal de confirmation de suppression (mobile-optimized) -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" @click.self="closeDeleteModal">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl transform transition-all">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Supprimer le manifeste</h3>
                <div class="mb-6">
                    <p class="text-sm text-gray-600">
                        Êtes-vous sûr de vouloir supprimer le manifeste <strong class="text-gray-900">{{ $manifest->manifest_number }}</strong> ?
                    </p>
                    <p class="text-sm text-red-600 mt-2">
                        ⚠️ Cette action est irréversible et annulera également la demande de pickup associée.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="closeDeleteModal" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors duration-200 font-medium">
                        Annuler
                    </button>
                    <button @click="deleteManifest" :disabled="deleting" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 font-medium">
                        <span x-show="deleting" class="inline-flex items-center justify-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                            Suppression...
                        </span>
                        <span x-show="!deleting">Supprimer définitivement</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] {
    display: none !important;
}
</style>

<script>
function manifestShowApp() {
    return {
        manifestData: @json($manifestData),
        packages: @json($packages),
        loading: false,
        showDeleteModal: false,
        deleting: false,
        availablePackages: [],
        selectedPackageToAdd: '',
        addingPackage: false,

        async init() {
            await this.loadAvailablePackages();
        },

        get canDeleteManifest() {
            return this.manifestData.status_badge.text === 'En préparation';
        },

        get canModifyManifest() {
            // Le manifeste peut être modifié si aucun colis n'a été collecté
            return !this.packages.some(pkg => pkg.status === 'PICKED_UP');
        },

        async loadAvailablePackages() {
            try {
                // Filtrer par l'adresse de pickup du manifeste
                const pickupAddressId = this.manifestData.pickup_address_id;
                let url = '{{ route("client.manifests.api.available-packages") }}';
                if (pickupAddressId) {
                    url += `?pickup_address_id=${pickupAddressId}`;
                }

                const response = await fetch(url);
                const data = await response.json();
                this.availablePackages = data.packages || [];
            } catch (error) {
                console.error('Erreur lors du chargement des colis disponibles:', error);
                this.availablePackages = [];
            }
        },

        async addPackageToManifest() {
            if (!this.selectedPackageToAdd) return;

            this.addingPackage = true;
            try {
                const response = await fetch(`{{ route('client.manifests.add-package', $manifest->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ package_id: this.selectedPackageToAdd })
                });

                const data = await response.json();
                if (data.success) {
                    // Recharger la page pour voir les changements
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout du colis');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout du colis au manifeste');
            } finally {
                this.addingPackage = false;
            }
        },

        async removePackage(packageId, packageCode) {
            if (!confirm(`Êtes-vous sûr de vouloir retirer le colis ${packageCode} du manifeste ?`)) {
                return;
            }

            try {
                const response = await fetch(`{{ route('client.manifests.remove-package', $manifest->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ package_id: packageId })
                });

                const data = await response.json();
                if (data.success) {
                    // Recharger la page pour voir les changements
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression du colis');
            }
        },

        confirmDelete() {
            this.showDeleteModal = true;
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
        },

        async deleteManifest() {
            this.deleting = true;
            try {
                const response = await fetch(`{{ route('client.manifests.destroy', $manifest->id) }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    // Afficher un message de succès avant redirection
                    if (window.showToast) {
                        window.showToast(data.message || 'Manifeste supprimé avec succès', 'success');
                    }
                    // Rediriger vers la liste des manifestes après un délai
                    setTimeout(() => {
                        window.location.href = '{{ route("client.manifests.index") }}';
                    }, 1500);
                } else {
                    if (window.showToast) {
                        window.showToast(data.message || 'Erreur lors de la suppression', 'error');
                    } else {
                        alert(data.message || 'Erreur lors de la suppression');
                    }
                    this.closeDeleteModal();
                }
            } catch (error) {
                console.error('Erreur:', error);
                if (window.showToast) {
                    window.showToast('Erreur lors de la suppression du manifeste', 'error');
                } else {
                    alert('Erreur lors de la suppression du manifeste');
                }
                this.closeDeleteModal();
            } finally {
                this.deleting = false;
            }
        },

        getStatusBadgeClass(statusBadge) {
            const colorMap = {
                'blue': 'bg-blue-100 text-blue-800',
                'orange': 'bg-orange-100 text-orange-800',
                'green': 'bg-green-100 text-green-800',
                'gray': 'bg-gray-100 text-gray-800'
            };
            return colorMap[statusBadge.color] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
@endsection