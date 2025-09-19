@extends('layouts.deliverer')

@section('title', 'Mes Pickups')

@section('content')
<div class="p-4 space-y-4" x-data="myPickupsApp()" x-init="init()">
    
    <!-- Header -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Mes Pickups</h1>
                <p class="text-gray-600 text-sm" x-text="`${packages.length} colis acceptés à collecter`"></p>
            </div>
            <div class="flex space-x-2">
                <button @click="loadPackages()" :disabled="loading"
                        class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
                <button @click="openScanner()" 
                        class="p-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4.01M12 12V7.99"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Actions en lot (si sélection) -->
        <div x-show="selectedPackages.length > 0" x-transition
             class="bg-purple-50 border border-purple-200 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-purple-800 font-medium" x-text="`${selectedPackages.length} colis sélectionné(s)`"></span>
                    <button @click="selectedPackages = []" class="text-purple-600 hover:text-purple-800 text-sm">
                        Désélectionner
                    </button>
                </div>
                <button @click="pickupSelectedPackages()" :disabled="processing"
                        class="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 disabled:opacity-50">
                    Marquer comme Collectés
                </button>
            </div>
        </div>
    </div>

    <!-- Liste des colis -->
    <div class="space-y-3">
        <template x-for="package in packages" :key="package.id">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200"
                 :class="selectedPackages.includes(package.id) ? 'ring-2 ring-purple-500 bg-purple-50' : ''">
                
                <!-- Header du colis -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" :value="package.id" x-model="selectedPackages"
                               class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <div>
                            <h3 class="font-semibold text-gray-900" x-text="package.package_code"></h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    À Collecter
                                </span>
                                <span x-show="isUrgent(package.assigned_at)" 
                                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Urgent
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-600" x-text="formatAmount(package.cod_amount)"></p>
                        <p class="text-xs text-gray-500" x-text="`Accepté ${formatTimeAgo(package.assigned_at)}`"></p>
                    </div>
                </div>

                <!-- Informations Pickup (Focus principal) -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-semibold text-blue-800">📍 COLLECTE CHEZ</span>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-lg" x-text="package.sender_data.name"></p>
                        <div class="flex items-center space-x-4">
                            <a :href="`tel:${package.sender_data.phone}`" 
                               class="flex items-center space-x-1 text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span x-text="package.sender_data.phone"></span>
                            </a>
                            <button @click="openMap(package.sender_data.address)" 
                                    class="flex items-center space-x-1 text-green-600 hover:text-green-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
                                </svg>
                                <span>Navigation</span>
                            </button>
                        </div>
                        <p class="text-gray-700 font-medium" x-text="package.sender_data.address"></p>
                        <p class="text-sm text-gray-600" x-text="package.delegation_from_name"></p>
                    </div>
                </div>

                <!-- Informations Livraison (Aperçu) -->
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-green-600">Destination finale</span>
                    </div>
                    <p class="text-sm font-medium text-gray-900" x-text="package.recipient_data.name"></p>
                    <p class="text-sm text-gray-600" x-text="package.recipient_data.address"></p>
                    <p class="text-xs text-gray-500" x-text="package.delegation_to_name"></p>
                </div>

                <!-- Contenu -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-yellow-800">Contenu:</p>
                            <p class="text-sm text-yellow-700" x-text="package.content_description"></p>
                            <div x-show="package.notes" class="mt-2">
                                <p class="text-xs font-medium text-yellow-800">Instructions:</p>
                                <p class="text-xs text-yellow-600" x-text="package.notes"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions principales -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="`Accepté ${formatTimeAgo(package.assigned_at)}`"></span>
                        </div>
                        <div x-show="isUrgent(package.assigned_at)" class="flex items-center space-x-1 text-red-600 font-medium">
                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <span>Collecte urgente requise</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button @click="viewPackageDetails(package.id)" 
                                class="px-3 py-1 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                            Voir Tout
                        </button>
                        <button @click="markAsPickedUp(package.id)" :disabled="processing"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors text-sm font-medium">
                            <span x-show="!processing || processingPackageId !== package.id">✓ Collecté</span>
                            <span x-show="processing && processingPackageId === package.id" class="flex items-center space-x-1">
                                <div class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- État vide -->
    <div x-show="packages.length === 0 && !loading" 
         class="text-center py-12 bg-white rounded-xl border border-gray-200">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun pickup assigné</h3>
        <p class="mt-1 text-sm text-gray-500">Consultez la liste des pickups disponibles pour en accepter.</p>
        <div class="mt-6">
            <a href="{{ route('deliverer.pickups.available') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Voir Pickups Disponibles
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-8">
        <div class="flex items-center space-x-3">
            <div class="w-6 h-6 border-2 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-gray-600">Chargement de vos pickups...</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function myPickupsApp() {
    return {
        packages: @json($packages->items() ?? []),
        selectedPackages: [],
        loading: false,
        processing: false,
        processingPackageId: null,

        init() {
            // Auto refresh every 45 seconds
            setInterval(() => {
                this.loadPackages();
            }, 45000);
        },

        async loadPackages() {
            this.loading = true;
            try {
                const response = await fetch('/deliverer/pickups/mine?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                }
            } catch (error) {
                console.error('Error loading my pickups:', error);
                this.showToast('Erreur de chargement', 'error');
            }
            this.loading = false;
        },

        async markAsPickedUp(packageId) {
            this.processing = true;
            this.processingPackageId = packageId;
            
            try {
                const response = await fetch(`/deliverer/packages/${packageId}/pickup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    // Retirer le package de la liste (maintenant dans "Livraisons")
                    this.packages = this.packages.filter(p => p.id !== packageId);
                    this.selectedPackages = this.selectedPackages.filter(id => id !== packageId);
                } else {
                    this.showToast(data.message, 'error');
                }

            } catch (error) {
                console.error('Error marking as picked up:', error);
                this.showToast('Erreur lors du marquage', 'error');
            }

            this.processing = false;
            this.processingPackageId = null;
        },

        async pickupSelectedPackages() {
            if (this.selectedPackages.length === 0) return;

            this.processing = true;
            
            try {
                const response = await fetch('/deliverer/packages/bulk-pickup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        package_ids: this.selectedPackages
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(`${this.selectedPackages.length} colis collectés avec succès`, 'success');
                    // Retirer les packages collectés
                    this.packages = this.packages.filter(p => !this.selectedPackages.includes(p.id));
                    this.selectedPackages = [];
                } else {
                    this.showToast(data.message, 'error');
                }

            } catch (error) {
                console.error('Error bulk pickup:', error);
                this.showToast('Erreur lors de la collecte groupée', 'error');
            }

            this.processing = false;
        },

        viewPackageDetails(packageId) {
            window.location.href = `/deliverer/packages/${packageId}`;
        },

        isUrgent(assignedAt) {
            const assigned = new Date(assignedAt);
            const now = new Date();
            const hoursAgo = (now - assigned) / (1000 * 60 * 60);
            return hoursAgo > 2; // Urgent si accepté il y a plus de 2h
        },

        openMap(address) {
            const encodedAddress = encodeURIComponent(address);
            const mapUrl = `https://maps.google.com/maps?q=${encodedAddress}`;
            window.open(mapUrl, '_blank');
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        formatTimeAgo(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 60) {
                return `il y a ${diffInMinutes}min`;
            } else if (diffInMinutes < 1440) {
                return `il y a ${Math.floor(diffInMinutes / 60)}h`;
            } else {
                return date.toLocaleDateString('fr-FR');
            }
        },

        openScanner() {
            const scannerComponent = document.querySelector('[x-data*="scannerApp"]');
            if (scannerComponent) {
                scannerComponent.__x.$data.scannerOpen = true;
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            toast.className = `fixed top-20 left-4 right-4 ${bgColor} text-white px-4 py-3 rounded-xl shadow-lg z-50 mx-auto max-w-md transition-all duration-300`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endpush