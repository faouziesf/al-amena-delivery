@extends('layouts.deliverer')

@section('title', 'Ma Tournée')

@section('content')
<div x-data="simpleDashboard()" x-init="init()">

    <!-- Statistiques rapides -->
    <div class="p-4">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Livraisons -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-700 text-sm font-medium">Livraisons</p>
                        <p class="text-green-800 text-2xl font-bold" x-text="deliveries.length"></p>
                    </div>
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Collectes -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-700 text-sm font-medium">Collectes</p>
                        <p class="text-blue-800 text-2xl font-bold" x-text="pickups.length"></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression du jour -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Progression du jour</h2>
                <span class="text-sm font-medium text-blue-600" x-text="`${completedTasks}/${totalTasks}`"></span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                     :style="`width: ${totalTasks > 0 ? (completedTasks/totalTasks)*100 : 0}%`"></div>
            </div>

            <p class="text-sm text-gray-600 mt-2" x-text="`${Math.round(totalTasks > 0 ? (completedTasks/totalTasks)*100 : 0)}% terminé`"></p>
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="px-4">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Mes tâches</h2>

        <div class="space-y-3 content-mobile-spacing">
            <!-- Collectes -->
            <template x-for="pickup in pickups" :key="`pickup-${pickup.id}`">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="w-3 h-3 rounded-full bg-orange-400"></div>
                                <span class="font-medium text-gray-900">COLLECTE</span>
                                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                                    #<span x-text="pickup.id"></span>
                                </span>
                                <div x-show="pickup.status === 'PICKED_UP'">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Terminé</span>
                                </div>
                            </div>

                            <p class="text-gray-800 font-medium mb-1" x-text="pickup.pickup_address"></p>
                            <p class="text-gray-600 text-sm" x-text="pickup.pickup_contact"></p>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-gray-500">Colis à collecter</span>
                                <span class="font-medium text-blue-600" x-text="pickup.packages_count"></span>
                            </div>
                        </div>

                        <div class="ml-3">
                            <button x-show="pickup.status !== 'PICKED_UP'"
                                    @click="markPickup(pickup.id)"
                                    class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700 active:scale-95 transition-all">
                                Collecter
                            </button>
                            <div x-show="pickup.status === 'PICKED_UP'" class="text-green-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Livraisons -->
            <template x-for="delivery in deliveries" :key="`delivery-${delivery.id}`">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                                <span class="font-medium text-gray-900">LIVRAISON</span>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    #<span x-text="delivery.tracking_number"></span>
                                </span>
                                <div x-show="delivery.status === 'DELIVERED'">
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Livré</span>
                                </div>
                                <div x-show="delivery.status === 'UNAVAILABLE'">
                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">Indisponible</span>
                                </div>
                                <div x-show="delivery.status === 'CANCELLED'">
                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Annulé</span>
                                </div>
                            </div>

                            <p class="text-gray-800 font-medium mb-1" x-text="delivery.recipient_name"></p>
                            <p class="text-gray-600 text-sm" x-text="delivery.recipient_address"></p>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-gray-500">COD</span>
                                <span class="font-medium text-green-600" x-text="formatAmount(delivery.cod_amount)"></span>
                            </div>
                        </div>

                        <div class="ml-3">
                            <div x-show="delivery.status === 'PICKED_UP' || delivery.status === 'ACCEPTED'" class="space-y-2">
                                <button @click="markDelivered(delivery.id)"
                                        class="block w-full bg-green-600 text-white px-3 py-2 rounded text-sm font-medium hover:bg-green-700 active:scale-95 transition-all">
                                    Livré
                                </button>
                                <button @click="markUnavailable(delivery.id)"
                                        class="block w-full bg-orange-600 text-white px-3 py-2 rounded text-sm font-medium hover:bg-orange-700 active:scale-95 transition-all">
                                    Indisponible
                                </button>
                            </div>
                            <div x-show="delivery.status === 'DELIVERED'" class="text-green-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- État vide -->
            <div x-show="totalTasks === 0" class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune tâche</h3>
                <p class="text-gray-600">Toutes vos tâches sont terminées pour aujourd'hui !</p>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700">Chargement...</p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function simpleDashboard() {
    return {
        loading: false,
        pickups: [],
        deliveries: [],
        walletBalance: 0,

        init() {
            this.loadData();
            setInterval(() => this.loadData(), 30000); // Refresh every 30 seconds
        },

        get totalTasks() {
            return this.pickups.length + this.deliveries.length;
        },

        get completedTasks() {
            const completedPickups = this.pickups.filter(p => p.status === 'PICKED_UP').length;
            const completedDeliveries = this.deliveries.filter(d => d.status === 'DELIVERED').length;
            return completedPickups + completedDeliveries;
        },

        async loadData() {
            try {
                const [pickupsRes, deliveriesRes, walletRes] = await Promise.all([
                    fetch('/deliverer/api/simple/pickups'),
                    fetch('/deliverer/api/simple/deliveries'),
                    fetch('/deliverer/api/simple/wallet/balance')
                ]);

                if (pickupsRes.ok) this.pickups = await pickupsRes.json();
                if (deliveriesRes.ok) this.deliveries = await deliveriesRes.json();
                if (walletRes.ok) {
                    const walletData = await walletRes.json();
                    this.walletBalance = walletData.balance || 0;
                }
            } catch (error) {
                console.error('Erreur chargement:', error);
            }
        },

        async markPickup(packageId) {
            this.loading = true;
            try {
                const response = await fetch(`/deliverer/pickup/${packageId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showToast('Colis collecté !', 'success');
                    this.loadData();
                } else {
                    showToast(data.message || 'Erreur lors de la collecte', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async markDelivered(packageId) {
            this.loading = true;
            try {
                const response = await fetch(`/deliverer/deliver/${packageId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showToast('Colis livré !', 'success');
                    this.loadData();
                } else {
                    showToast(data.message || 'Erreur lors de la livraison', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        async markUnavailable(packageId) {
            this.loading = true;
            try {
                const response = await fetch(`/deliverer/unavailable/${packageId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showToast('Colis marqué indisponible', 'success');
                    this.loadData();
                } else {
                    showToast(data.message || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            } finally {
                this.loading = false;
            }
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DA';
        }
    }
}
</script>
@endpush
@endsection