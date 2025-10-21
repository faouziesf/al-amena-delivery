@extends('layouts.depot-manager')

@section('title', 'Paiements à Préparer')
@section('page-title', 'Paiements à Préparer')
@section('page-description', 'Transformez les paiements en colis')

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-4 py-4" x-data="paymentsToPrep()" x-init="init()">

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="text-sm opacity-90 mb-1">À Préparer</div>
            <div class="text-3xl font-black" x-text="filteredPayments.length"></div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-4 text-white shadow-lg">
            <div class="text-sm opacity-90 mb-1">Total Montant</div>
            <div class="text-2xl font-black" x-text="totalAmount.toFixed(3) + ' DT'"></div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-3 mb-4">
        <div class="flex flex-col sm:flex-row gap-2">
            <select x-model="statusFilter" @change="filterPayments()"
                    class="flex-1 px-3 py-2 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Tous les statuts</option>
                <option value="PENDING">⏳ En attente</option>
                <option value="APPROVED">✅ Approuvé</option>
                <option value="READY_FOR_DELIVERY">🚚 Prêt livraison</option>
            </select>

            <button @click="loadData()"
                    :disabled="loading"
                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold transition-colors disabled:opacity-50 text-sm">
                <svg class="w-4 h-4 inline mr-1" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Liste des Paiements -->
    <div class="space-y-3">
        <template x-for="payment in filteredPayments" :key="payment.id">
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all">
                <div class="p-4">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="font-mono font-bold text-gray-900 mb-1" x-text="payment.request_code"></div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                      :class="{
                                          'bg-yellow-100 text-yellow-700': payment.status === 'PENDING',
                                          'bg-green-100 text-green-700': payment.status === 'APPROVED',
                                          'bg-blue-100 text-blue-700': payment.status === 'READY_FOR_DELIVERY',
                                          'bg-purple-100 text-purple-700': payment.status === 'DELIVERED',
                                          'bg-gray-100 text-gray-700': !['PENDING', 'APPROVED', 'READY_FOR_DELIVERY', 'DELIVERED'].includes(payment.status)
                                      }">
                                    <span x-show="payment.status === 'PENDING'">⏳ En attente</span>
                                    <span x-show="payment.status === 'APPROVED'">✅ Approuvé</span>
                                    <span x-show="payment.status === 'READY_FOR_DELIVERY'">🚚 Prêt</span>
                                    <span x-show="payment.status === 'DELIVERED'">📦 Livré</span>
                                    <span x-show="!['PENDING', 'APPROVED', 'READY_FOR_DELIVERY', 'DELIVERED'].includes(payment.status)" x-text="payment.status"></span>
                                </span>
                                
                                <span class="text-xs text-gray-500" x-text="payment.created_at_formatted"></span>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-2xl font-black text-green-600" x-text="parseFloat(payment.amount).toFixed(3)"></div>
                            <div class="text-xs text-gray-500">DT</div>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="bg-gray-50 rounded-xl p-3 mb-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-gray-900 mb-1" x-text="payment.client?.name || 'N/A'"></div>
                                <div class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span x-text="payment.client?.phone || 'N/A'"></span>
                                </div>
                                <div class="text-sm text-gray-600 flex items-start gap-2 mt-1">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="line-clamp-2" x-text="payment.client?.address || 'Adresse non spécifiée'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <!-- Boutons Approuver/Rejeter (si statut PENDING) -->
                        <div x-show="payment.status === 'PENDING'" class="flex gap-2">
                            <button @click="approvePayment(payment.id)" 
                                    :disabled="payment.loading"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Approuver</span>
                            </button>

                            <button @click="rejectPayment(payment.id)" 
                                    :disabled="payment.loading"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Rejeter</span>
                            </button>
                        </div>

                        <!-- Boutons Créer Colis / Voir Détails -->
                        <div class="flex gap-2">
                            <!-- Créer Colis (si approuvé ou prêt) -->
                            <button x-show="!payment.assigned_package && ['APPROVED', 'READY_FOR_DELIVERY'].includes(payment.status)"
                                    @click="createPackage(payment.id)" 
                                    :disabled="payment.loading"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" :class="{'animate-spin': payment.loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span x-show="!payment.loading">Créer Colis</span>
                                <span x-show="payment.loading">Création...</span>
                            </button>

                            <!-- Voir Détails (toujours affiché) -->
                            <a :href="'/depot-manager/payments/' + payment.id + '/details'"
                               class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold transition-colors flex items-center justify-center"
                               :class="payment.assigned_package || payment.status === 'PENDING' ? '' : 'flex-1'"
                               title="Voir les détails complets">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            <!-- Voir Colis (si colis créé) -->
                            <template x-if="payment.assigned_package">
                                <a :href="'/depot-manager/packages/' + payment.assigned_package.package_code"
                                   class="flex-1 px-4 py-3 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span>Voir Colis</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="filteredPayments.length === 0 && !loading" 
             class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Aucun paiement à préparer</h3>
            <p class="text-gray-600 text-sm">Les paiements en espèce apparaîtront ici</p>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="space-y-3">
            <div class="bg-white rounded-2xl p-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/4 mb-3"></div>
                <div class="h-16 bg-gray-200 rounded mb-3"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
            </div>
        </div>
    </div>

</div>

<script>
function paymentsToPrep() {
    return {
        payments: [],
        filteredPayments: [],
        statusFilter: '',
        loading: false,
        totalAmount: 0,
        
        async init() {
            await this.loadData();
        },
        
        async loadData() {
            this.loading = true;
            try {
                // Utiliser URL relative pour respecter le protocole de la page
                const url = '{{ route("depot-manager.dashboard.api.stats") }}';
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    }
                });
                
                if (!response.ok) throw new Error('Erreur réseau');
                
                const data = await response.json();
                this.payments = data.payments_to_prep || [];
                
                // Ajouter loading state à chaque paiement
                this.payments.forEach(p => p.loading = false);
                
                this.filterPayments();
            } catch (error) {
                console.error('Error loading payments:', error);
                this.payments = [];
                this.filteredPayments = [];
            } finally {
                this.loading = false;
            }
        },
        
        filterPayments() {
            this.filteredPayments = this.payments.filter(payment => {
                if (this.statusFilter && payment.status !== this.statusFilter) {
                    return false;
                }
                return true;
            });
            
            this.totalAmount = this.filteredPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
        },
        
        async approvePayment(paymentId) {
            const payment = this.payments.find(p => p.id === paymentId);
            if (!payment) return;
            
            if (!confirm(`Approuver le paiement de ${payment.client?.name} (${parseFloat(payment.amount).toFixed(3)} DT) ?`)) {
                return;
            }
            
            payment.loading = true;
            
            try {
                const url = '/depot-manager/api/payments/' + paymentId + '/approve';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Paiement approuvé avec succès');
                    await this.loadData();
                } else {
                    alert('❌ Erreur : ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('❌ Erreur lors de l\'approbation: ' + error.message);
            } finally {
                payment.loading = false;
            }
        },
        
        async rejectPayment(paymentId) {
            const payment = this.payments.find(p => p.id === paymentId);
            if (!payment) return;
            
            const reason = prompt(`Rejeter le paiement de ${payment.client?.name}\n\nRaison du rejet :`);
            if (!reason) return;
            
            payment.loading = true;
            
            try {
                const url = '/depot-manager/api/payments/' + paymentId + '/reject';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ reason: reason })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Paiement rejeté avec succès');
                    await this.loadData();
                } else {
                    alert('❌ Erreur : ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('❌ Erreur lors du rejet: ' + error.message);
            } finally {
                payment.loading = false;
            }
        },
        
        async createPackage(paymentId) {
            const payment = this.payments.find(p => p.id === paymentId);
            if (!payment) return;
            
            if (!confirm(`Créer un colis de paiement pour ${payment.client?.name} (${parseFloat(payment.amount).toFixed(3)} DT) ?`)) {
                return;
            }
            
            payment.loading = true;
            
            try {
                // Construire l'URL avec la route Laravel (respecte automatiquement HTTPS)
                const url = '/depot-manager/api/payments/' + paymentId + '/create-package';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Colis créé avec succès : ' + (data.package_code || 'Code généré'));
                    await this.loadData();
                } else {
                    alert('❌ Erreur : ' + data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('❌ Erreur lors de la création du colis: ' + error.message);
            } finally {
                payment.loading = false;
            }
        }
    }
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
