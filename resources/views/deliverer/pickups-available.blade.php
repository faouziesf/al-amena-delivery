@extends('layouts.deliverer-modern')

@section('title', 'Pickups Disponibles')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="pickupsApp()">
    
    <!-- Header -->
    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white safe-top">
        <div class="px-6 py-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Pickups Disponibles</h1>
                    <p class="text-blue-200 text-sm">Ramassages dans votre zone</p>
                </div>
                <button @click="refresh()" 
                        :class="loading ? 'animate-spin' : ''"
                        class="p-3 bg-white/20 rounded-xl hover:bg-white/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <!-- Compteur -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-3xl font-bold" x-text="pickups.length"></div>
                <div class="text-sm text-blue-200">Pickups disponibles</div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading && pickups.length === 0" class="p-8 text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des pickups...</p>
    </div>

    <!-- Liste des pickups -->
    <div x-show="!loading || pickups.length > 0" class="p-4 space-y-4 pb-24">
        
        <!-- Aucun pickup -->
        <div x-show="pickups.length === 0 && !loading" class="text-center py-12">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-gray-600 text-lg font-medium">Aucun pickup disponible</p>
            <p class="text-gray-500 text-sm mt-2">Revenez plus tard</p>
        </div>

        <!-- Cards pickups -->
        <template x-for="pickup in pickups" :key="pickup.id">
            <div class="card p-5 fade-in">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📦</span>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900" x-text="pickup.client_name || 'Client'"></div>
                            <div class="text-xs text-gray-500" x-text="pickup.delegation"></div>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                        Nouveau
                    </span>
                </div>

                <!-- Infos -->
                <div class="space-y-3 mb-4">
                    <div class="flex items-start text-gray-700">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <div class="font-medium" x-text="pickup.pickup_address"></div>
                            <div class="text-sm text-gray-500" x-text="pickup.delegation_from"></div>
                        </div>
                    </div>

                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-text="pickup.pickup_contact_name"></span>
                    </div>

                    <div class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a :href="`tel:${pickup.pickup_phone}`" class="text-indigo-600 font-medium" x-text="pickup.pickup_phone"></a>
                    </div>

                    <div x-show="pickup.pickup_notes" class="bg-yellow-50 p-3 rounded-lg">
                        <div class="text-xs text-gray-500 mb-1">📝 Notes</div>
                        <div class="text-sm text-gray-700" x-text="pickup.pickup_notes"></div>
                    </div>

                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm" x-text="pickup.requested_date"></span>
                    </div>
                </div>

                <!-- Action -->
                <button @click="acceptPickup(pickup.id)" 
                        :disabled="processing === pickup.id"
                        class="w-full btn btn-success">
                    <span x-show="processing !== pickup.id">✅ Accepter ce Pickup</span>
                    <span x-show="processing === pickup.id">
                        <div class="spinner mx-auto" style="width: 20px; height: 20px; border-width: 2px;"></div>
                    </span>
                </button>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function pickupsApp() {
    return {
        loading: true,
        pickups: [],
        processing: null,

        async init() {
            await this.loadPickups();
            
            // Auto-refresh toutes les 2 minutes
            setInterval(() => this.refresh(), 120000);
        },

        async loadPickups() {
            try {
                this.loading = true;
                
                const data = await apiRequest('/deliverer/api/pickups/available');
                
                if (Array.isArray(data)) {
                    this.pickups = data.map(pickup => ({
                        id: pickup.id,
                        client_name: pickup.client_name,
                        pickup_address: pickup.pickup_address,
                        pickup_contact_name: pickup.pickup_contact_name,
                        pickup_phone: pickup.pickup_phone,
                        pickup_notes: pickup.pickup_notes,
                        delegation_from: pickup.delegation_from,
                        delegation: pickup.delegation_from,
                        requested_date: this.formatDate(pickup.requested_pickup_date)
                    }));
                }
            } catch (error) {
                console.error('Erreur chargement pickups:', error);
                showToast('Erreur de chargement', 'error');
            } finally {
                this.loading = false;
            }
        },

        async refresh() {
            vibrate([30]);
            await this.loadPickups();
            showToast('Liste actualisée', 'success', 2000);
        },

        async acceptPickup(pickupId) {
            if (!confirm('Accepter ce pickup ?\nIl sera ajouté à votre tournée.')) {
                return;
            }

            try {
                this.processing = pickupId;
                vibrate([50]);

                await apiRequest(`/deliverer/api/pickups/${pickupId}/accept`, {
                    method: 'POST'
                });

                showToast('Pickup accepté et ajouté à votre tournée !', 'success');
                vibrate([100, 50, 100]);

                // Retirer de la liste
                this.pickups = this.pickups.filter(p => p.id !== pickupId);

                // Redirect vers tournée après 2s
                setTimeout(() => {
                    window.location.href = '/deliverer/tournee';
                }, 2000);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de l\'acceptation', 'error');
                this.processing = null;
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'Non spécifié';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', { 
                day: '2-digit', 
                month: 'long',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush

@endsection
