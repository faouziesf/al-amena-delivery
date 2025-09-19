@extends('layouts.deliverer')

@section('title', 'Retours')

@section('content')
<div class="p-4 space-y-4" x-data="returnsApp()" x-init="init()">
    
    <!-- Header -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Retours Expéditeur</h1>
                <p class="text-gray-600 text-sm" x-text="`${packages.length} colis à retourner`"></p>
            </div>
            <div class="flex space-x-2">
                <button @click="loadPackages()" :disabled="loading"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <p class="text-2xl font-bold text-red-600" x-text="packages.length"></p>
                <p class="text-red-700 text-sm">À Retourner</p>
            </div>
            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600" x-text="getUrgentReturns()"></p>
                <p class="text-yellow-700 text-sm">Urgents</p>
            </div>
            <div class="text-center p-3 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600" x-text="selectedPackages.length"></p>
                <p class="text-purple-700 text-sm">Sélectionnés</p>
            </div>
        </div>
    </div>

    <!-- Actions groupées -->
    <div x-show="selectedPackages.length > 0" x-transition
         class="bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-red-800 font-medium" x-text="`${selectedPackages.length} colis sélectionné(s) pour retour`"></span>
                <button @click="selectedPackages = []" class="text-red-600 hover:text-red-800 text-sm">
                    Désélectionner tout
                </button>
            </div>
            <button @click="returnSelectedPackages()" :disabled="processing"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                <span x-show="!processing">Retourner Sélectionnés</span>
                <span x-show="processing" class="flex items-center space-x-2">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Traitement...</span>
                </span>
            </button>
        </div>
    </div>

    <!-- Liste des retours -->
    <div class="space-y-3">
        <template x-for="package in packages" :key="package.id">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200"
                 :class="selectedPackages.includes(package.id) ? 'ring-2 ring-red-500 bg-red-50' : ''">
                
                <!-- Header avec urgence -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" :value="package.id" x-model="selectedPackages"
                               class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <div>
                            <h3 class="font-semibold text-gray-900" x-text="package.package_code"></h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    À Retourner
                                </span>
                                <span x-show="isUrgentReturn(package)" 
                                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 animate-pulse">
                                    🚨 URGENT
                                </span>
                                <span x-show="package.delivery_attempts > 2" 
                                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800"
                                      x-text="`${package.delivery_attempts} tentatives`">
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Échec livraison</p>
                        <p class="text-xs text-gray-400" x-text="getReturnReason(package)"></p>
                    </div>
                </div>

                <!-- Informations Expéditeur (Retour) -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        <span class="font-semibold text-orange-800">🔄 RETOURNER CHEZ</span>
                    </div>
                    
                    <div class="space-y-3">
                        <p class="font-bold text-gray-900 text-lg" x-text="package.sender_data.name"></p>
                        
                        <!-- Actions de contact -->
                        <div class="flex items-center space-x-4">
                            <a :href="`tel:${package.sender_data.phone}`" 
                               class="flex items-center space-x-2 px-3 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span x-text="package.sender_data.phone"></span>
                            </a>
                            
                            <button @click="openMap(package.sender_data.address)" 
                                    class="flex items-center space-x-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
                                </svg>
                                <span>Navigation</span>
                            </button>
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-gray-700 font-medium" x-text="package.sender_data.address"></p>
                            <p class="text-sm text-gray-600" x-text="package.delegation_from_name"></p>
                        </div>
                    </div>
                </div>

                <!-- Informations sur l'échec de livraison -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800">Livraison échouée</p>
                            <p class="text-sm text-red-700">
                                <span x-text="package.delivery_attempts"></span> tentative(s) -
                                <span x-text="getReturnReason(package)"></span>
                            </p>
                            <div class="mt-2">
                                <p class="text-xs font-medium text-red-800">Client concerné:</p>
                                <p class="text-xs text-red-700" x-text="package.recipient_data.name + ' - ' + package.recipient_data.phone"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu du colis -->
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Contenu:</p>
                            <p class="text-sm text-gray-600" x-text="package.content_description"></p>
                            <div x-show="package.cod_amount > 0" class="mt-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    COD: <span x-text="formatAmount(package.cod_amount)"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="formatTimeAgo(package.updated_at)"></span>
                        </div>
                        
                        <div x-show="package.delivery_attempts > 2" class="flex items-center space-x-1 text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <span>Multiples échecs</span>
                        </div>
                        
                        <div x-show="isUrgentReturn(package)" class="flex items-center space-x-1 text-yellow-600 font-medium">
                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <span>Retour urgent</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button @click="viewPackageDetails(package.id)" 
                                class="px-3 py-1 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                            Détails
                        </button>
                        
                        <button @click="confirmReturn(package.id)" :disabled="processing"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors text-sm font-medium">
                            <span x-show="!processing || processingPackageId !== package.id">✓ Retourné</span>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun retour en attente</h3>
        <p class="mt-1 text-sm text-gray-500">Tous vos colis ont été livrés avec succès ou retournés.</p>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-8">
        <div class="flex items-center space-x-3">
            <div class="w-6 h-6 border-2 border-red-600 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-gray-600">Chargement des retours...</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function returnsApp() {
    return {
        packages: @json($packages->items() ?? []),
        selectedPackages: [],
        loading: false,
        processing: false,
        processingPackageId: null,

        init() {
            // Auto refresh every 60 seconds
            setInterval(() => {
                this.loadPackages();
            }, 60000);
        },

        async loadPackages() {
            this.loading = true;
            try {
                const response = await fetch('/deliverer/returns?ajax=1');
                if (response.ok) {
                    const data = await response.json();
                    this.packages = data.packages || [];
                }
            } catch (error) {
                console.error('Error loading returns:', error);
            }
            this.loading = false;
        },

        async confirmReturn(packageId) {
            if (!confirm('Confirmer le retour de ce colis à l\'expéditeur ?')) return;

            this.processing = true;
            this.processingPackageId = packageId;
            
            try {
                const response = await fetch(`/deliverer/packages/${packageId}/return`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        return_reason: 'DELIVERY_FAILED',
                        return_notes: 'Retourné expéditeur après échec(s) de livraison'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    // Retirer de la liste
                    this.packages = this.packages.filter(p => p.id !== packageId);
                    this.selectedPackages = this.selectedPackages.filter(id => id !== packageId);
                } else {
                    this.showToast(data.message, 'error');
                }

            } catch (error) {
                console.error('Error confirming return:', error);
                this.showToast('Erreur lors du retour', 'error');
            }

            this.processing = false;
            this.processingPackageId = null;
        },

        async returnSelectedPackages() {
            if (this.selectedPackages.length === 0) return;
            
            if (!confirm(`Confirmer le retour de ${this.selectedPackages.length} colis ?`)) return;

            this.processing = true;
            
            try {
                const response = await fetch('/deliverer/packages/bulk-return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        package_ids: this.selectedPackages,
                        return_reason: 'DELIVERY_FAILED',
                        return_notes: 'Retour groupé après échecs de livraison'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(`${this.selectedPackages.length} colis retournés avec succès`, 'success');
                    // Retirer les packages retournés
                    this.packages = this.packages.filter(p => !this.selectedPackages.includes(p.id));
                    this.selectedPackages = [];
                } else {
                    this.showToast(data.message, 'error');
                }

            } catch (error) {
                console.error('Error bulk return:', error);
                this.showToast('Erreur lors du retour groupé', 'error');
            }

            this.processing = false;
        },

        getUrgentReturns() {
            return this.packages.filter(p => this.isUrgentReturn(p)).length;
        },

        isUrgentReturn(package) {
            // Urgent si échec depuis plus de 24h ou tentatives > 3
            const failedTime = new Date(package.updated_at);
            const hoursAgo = (new Date() - failedTime) / (1000 * 60 * 60);
            return hoursAgo > 24 || package.delivery_attempts > 3;
        },

        getReturnReason(package) {
            const reasons = {
                'CLIENT_ABSENT': 'Client absent',
                'ADDRESS_NOT_FOUND': 'Adresse introuvable', 
                'CLIENT_REFUSES': 'Client refuse',
                'DELIVERY_ATTEMPTS_EXCEEDED': 'Tentatives épuisées'
            };
            
            // Determine reason based on attempts
            if (package.delivery_attempts > 3) {
                return 'Tentatives épuisées';
            } else if (package.delivery_attempts > 1) {
                return 'Multiples échecs';
            }
            return 'Échec de livraison';
        },

        viewPackageDetails(packageId) {
            window.location.href = `/deliverer/packages/${packageId}`;
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
                return `Il y a ${diffInMinutes}min`;
            } else if (diffInMinutes < 1440) {
                return `Il y a ${Math.floor(diffInMinutes / 60)}h`;
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