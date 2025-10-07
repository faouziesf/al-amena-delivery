@extends('layouts.deliverer-modern')

@section('title', 'Détail Tâche')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="taskDetailApp({{ $package->id }})">
    
    <!-- Header avec retour -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white safe-top">
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('deliverer.tournee') }}" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-xl font-bold" x-text="task.type === 'pickup' ? '📦 RAMASSAGE' : '🚚 LIVRAISON'"></h1>
                    <p class="text-indigo-200 text-sm" x-text="task.package_code"></p>
                </div>
                <span class="px-3 py-1 bg-white/20 rounded-lg text-xs font-semibold" x-text="getStatusLabel(task.status)"></span>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="p-8 text-center">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement...</p>
    </div>

    <!-- Content -->
    <div x-show="!loading" class="p-4 space-y-4 pb-32">

        <!-- Alert ÉCHANGE si applicable -->
        <div x-show="task.est_echange" class="card p-4 bg-orange-50 border-orange-200">
            <div class="flex items-center text-orange-700">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <div class="font-bold">⚠️ ATTENTION : ÉCHANGE</div>
                    <div class="text-sm mt-1">N'oubliez pas de récupérer l'ancien colis !</div>
                </div>
            </div>
        </div>

        <!-- Infos Client/Destinataire -->
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations
            </h3>
            
            <div class="space-y-3">
                <div>
                    <div class="text-xs text-gray-500 mb-1">Nom</div>
                    <div class="font-semibold text-gray-900" x-text="task.recipient_name"></div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 mb-1">Téléphone</div>
                    <a :href="`tel:${task.recipient_phone}`" 
                       class="font-semibold text-indigo-600 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span x-text="task.recipient_phone"></span>
                    </a>
                </div>

                <div>
                    <div class="text-xs text-gray-500 mb-1">Adresse</div>
                    <div class="text-gray-700" x-text="task.recipient_address"></div>
                </div>

                <div x-show="task.notes">
                    <div class="text-xs text-gray-500 mb-1">Notes</div>
                    <div class="text-gray-700 text-sm bg-yellow-50 p-3 rounded-lg" x-text="task.notes"></div>
                </div>
            </div>
        </div>

        <!-- COD à collecter (si livraison) -->
        <div x-show="task.type === 'livraison' && task.cod_amount > 0" class="card p-5 bg-green-50 border-green-200">
            <h3 class="font-bold text-green-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Montant à Collecter
            </h3>
            <div class="text-4xl font-bold text-green-700" x-text="formatMoney(task.cod_amount)"></div>
        </div>

        <!-- Scanner le colis (scan-to-act) -->
        <div x-show="!scanned && task.status !== 'DELIVERED'" class="card p-6 bg-indigo-50 border-indigo-200">
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Scanner le Colis</h3>
                <p class="text-gray-600 text-sm mb-4">Obligatoire avant toute action</p>
                <a href="{{ route('deliverer.scan.simple') }}" 
                   class="inline-block btn btn-primary">
                    📷 Ouvrir le Scanner
                </a>
            </div>
        </div>

        <!-- Colis scannés (pour pickup) -->
        <div x-show="task.type === 'pickup'" class="card p-5">
            <h3 class="font-bold text-gray-900 mb-4">Colis Scannés</h3>
            
            <div x-show="scannedPackages.length === 0" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm">Aucun colis scanné</p>
            </div>

            <div class="space-y-2 mb-4">
                <template x-for="(pkg, index) in scannedPackages" :key="pkg.id">
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <span class="font-bold text-indigo-600" x-text="index + 1"></span>
                            </div>
                            <div>
                                <div class="font-mono text-sm font-semibold" x-text="pkg.code"></div>
                                <div class="text-xs text-gray-500" x-text="pkg.recipient"></div>
                            </div>
                        </div>
                        <button @click="removeScannedPackage(index)" class="text-red-500 p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <a href="{{ route('deliverer.scan.simple') }}" 
               class="block text-center btn bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                ➕ Scanner un Colis
            </a>
        </div>
    </div>

    <!-- Actions footer (après scan) -->
    <div x-show="scanned || task.status === 'DELIVERED'" 
         class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 space-y-3 safe-bottom z-20">
        
        <!-- Actions pour LIVRAISON -->
        <template x-if="task.type === 'livraison'">
            <div class="space-y-3">
                <button @click="markDelivered()" 
                        :disabled="processing"
                        class="w-full btn btn-success">
                    ✅ Livré
                </button>
                
                <div class="grid grid-cols-2 gap-3">
                    <button @click="showUnavailableReasons()" 
                            :disabled="processing"
                            class="btn bg-yellow-600 text-white hover:bg-yellow-700">
                        ⏳ Indisponible
                    </button>
                    <button @click="showCancelReasons()" 
                            :disabled="processing"
                            class="btn btn-danger">
                        🚫 Annulé
                    </button>
                </div>
            </div>
        </template>

        <!-- Actions pour PICKUP -->
        <template x-if="task.type === 'pickup'">
            <button @click="validatePickup()" 
                    :disabled="processing || scannedPackages.length === 0"
                    class="w-full btn btn-success">
                ✅ Valider le Ramassage (<span x-text="scannedPackages.length"></span> colis)
            </button>
        </template>
    </div>

    <!-- Modal raisons indisponibilité -->
    <div x-show="showReasons" 
         x-transition
         @click="showReasons = false"
         class="fixed inset-0 bg-black/50 z-50 flex items-end">
        <div @click.stop class="bg-white rounded-t-3xl w-full p-6 slide-up">
            <h3 class="font-bold text-lg mb-4">Raison de l'indisponibilité</h3>
            <div class="space-y-2">
                <button @click="setUnavailable('Client absent')" 
                        class="w-full text-left p-4 bg-gray-50 rounded-xl hover:bg-gray-100">
                    Client absent
                </button>
                <button @click="setUnavailable('Adresse incorrecte')" 
                        class="w-full text-left p-4 bg-gray-50 rounded-xl hover:bg-gray-100">
                    Adresse incorrecte
                </button>
                <button @click="setUnavailable('Reporté par client')" 
                        class="w-full text-left p-4 bg-gray-50 rounded-xl hover:bg-gray-100">
                    Reporté par client
                </button>
                <button @click="setUnavailable('Autre')" 
                        class="w-full text-left p-4 bg-gray-50 rounded-xl hover:bg-gray-100">
                    Autre raison
                </button>
            </div>
            <button @click="showReasons = false" 
                    class="w-full mt-4 p-4 text-gray-600 hover:bg-gray-50 rounded-xl">
                Annuler
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function taskDetailApp(packageId) {
    return {
        loading: true,
        processing: false,
        task: {},
        scanned: false,
        scannedPackages: [],
        showReasons: false,

        async init() {
            await this.loadTask();
            
            // Vérifier si retour du scanner
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('scanned') === '1') {
                this.scanned = true;
                showToast('Colis scanné avec succès', 'success');
            }
        },

        async loadTask() {
            try {
                this.loading = true;
                
                const data = await apiRequest(`/deliverer/api/task/${packageId}`);
                
                this.task = {
                    id: data.id,
                    type: data.type || 'livraison',
                    package_code: data.tracking_number || data.package_code,
                    recipient_name: data.recipient_name,
                    recipient_phone: data.recipient_phone,
                    recipient_address: data.recipient_address,
                    cod_amount: data.cod_amount || 0,
                    status: data.status,
                    est_echange: data.est_echange || false,
                    notes: data.delivery_notes || data.notes
                };
            } catch (error) {
                console.error('Erreur chargement tâche:', error);
                showToast('Erreur de chargement', 'error');
            } finally {
                this.loading = false;
            }
        },

        async markDelivered() {
            if (!confirm(`Confirmer la livraison${this.task.cod_amount > 0 ? ` et la collecte de ${this.formatMoney(this.task.cod_amount)}` : ''} ?`)) {
                return;
            }

            try {
                this.processing = true;
                vibrate([50]);

                await apiRequest(`/deliverer/deliver/${this.task.id}`, {
                    method: 'POST',
                    body: { collected_amount: this.task.cod_amount }
                });

                showToast('Colis marqué comme livré !', 'success');
                vibrate([100, 50, 100]);
                
                // Redirect vers signature si COD ou échange
                if (this.task.cod_amount > 0 || this.task.est_echange) {
                    setTimeout(() => {
                        window.location.href = `/deliverer/signature/${this.task.id}`;
                    }, 1000);
                } else {
                    setTimeout(() => {
                        window.location.href = '/deliverer/tournee';
                    }, 1500);
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de la livraison', 'error');
                this.processing = false;
            }
        },

        showUnavailableReasons() {
            this.showReasons = true;
        },

        showCancelReasons() {
            const reason = prompt('Raison de l\'annulation :');
            if (reason) {
                this.setUnavailable(reason);
            }
        },

        async setUnavailable(reason) {
            try {
                this.showReasons = false;
                this.processing = true;
                vibrate([50]);

                await apiRequest(`/deliverer/unavailable/${this.task.id}`, {
                    method: 'POST',
                    body: { reason }
                });

                showToast('Statut mis à jour', 'warning');
                
                setTimeout(() => {
                    window.location.href = '/deliverer/tournee';
                }, 1500);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de la mise à jour', 'error');
                this.processing = false;
            }
        },

        async validatePickup() {
            if (this.scannedPackages.length === 0) {
                showToast('Veuillez scanner au moins un colis', 'warning');
                return;
            }

            if (!confirm(`Valider le ramassage de ${this.scannedPackages.length} colis ?`)) {
                return;
            }

            try {
                this.processing = true;
                vibrate([50]);

                await apiRequest(`/deliverer/pickup/${this.task.id}`, {
                    method: 'POST',
                    body: { 
                        packages: this.scannedPackages.map(p => p.id)
                    }
                });

                showToast('Ramassage validé !', 'success');
                vibrate([100, 50, 100]);
                
                setTimeout(() => {
                    window.location.href = '/deliverer/tournee';
                }, 1500);
            } catch (error) {
                console.error('Erreur:', error);
                showToast(error.message || 'Erreur lors de la validation', 'error');
                this.processing = false;
            }
        },

        removeScannedPackage(index) {
            this.scannedPackages.splice(index, 1);
            vibrate([30]);
        },

        getStatusLabel(status) {
            const labels = {
                'AVAILABLE': 'Disponible',
                'PICKED_UP': 'En cours',
                'DELIVERED': 'Livré',
                'UNAVAILABLE': 'Indisponible'
            };
            return labels[status] || status;
        },

        formatMoney(amount) {
            return (amount || 0).toFixed(2) + ' TND';
        }
    }
}
</script>
@endpush

@endsection
