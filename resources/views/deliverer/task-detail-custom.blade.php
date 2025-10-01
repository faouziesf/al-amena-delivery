@extends('layouts.deliverer')

@section('title', 'Détail Tâche')

@section('content')
<div class="h-full bg-gray-50" x-data="taskDetailApp()" x-init="init()">

    <!-- Header avec infos de la tâche -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-6">
        <div class="flex items-center space-x-4 mb-4">
            <a href="{{ route('deliverer.run.sheet') }}" class="text-white hover:text-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold">{{ $mockTask->code }}</h1>
                <p class="text-blue-200 text-sm">
                    @if($mockTask->type === 'pickup')
                        📦 Tâche de Collecte
                    @else
                        🚚 Tâche de Livraison
                    @endif
                </p>
            </div>
            <div class="text-right">
                @if($mockTask->type === 'pickup')
                    <div class="bg-orange-500 px-3 py-1 rounded-full text-xs font-bold">
                        {{ $mockTask->packages_count }} Colis
                    </div>
                @else
                    @if($mockTask->cod_amount > 0)
                        <div class="bg-green-500 px-3 py-1 rounded-full text-xs font-bold">
                            {{ number_format($mockTask->cod_amount, 3) }} DT
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Statut -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if($mockTask->status === 'AVAILABLE')
                    <div class="w-3 h-3 bg-orange-400 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Disponible pour collecte</span>
                @elseif($mockTask->status === 'PICKED_UP')
                    <div class="w-3 h-3 bg-blue-400 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium">Prêt pour livraison</span>
                @endif
            </div>
            <span class="text-xs text-blue-200">{{ $mockTask->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="px-6 py-6 space-y-6">

        @if($mockTask->type === 'pickup')
            <!-- INFORMATIONS DE COLLECTE -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Informations de Collecte</h2>
                        <p class="text-sm text-gray-500">{{ $mockTask->client_name }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Adresse de Collecte</p>
                            <p class="text-gray-600">{{ $mockTask->pickup_address }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Détails</p>
                            <p class="text-gray-600">{{ $mockTask->packages_count }} colis • {{ number_format($mockTask->total_value, 3) }} DT</p>
                        </div>
                    </div>

                    @if($mockTask->pickup_notes)
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Instructions</p>
                            <p class="text-gray-600">{{ $mockTask->pickup_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- ACTIONS DE COLLECTE -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button @click="markAsPickedUp()"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white py-4 px-6 rounded-xl font-bold text-lg transition-all flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Marquer comme Collecté</span>
                    </button>

                    <button @click="markAsUnavailable()"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-3 px-6 rounded-xl font-medium transition-all">
                        Signaler un Problème
                    </button>
                </div>
            </div>

        @else
            <!-- INFORMATIONS DE LIVRAISON -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Destinataire</h2>
                        <p class="text-sm text-gray-500">Livraison à effectuer</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 text-lg">{{ $mockTask->recipient_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $mockTask->recipient_phone }}</p>
                            <a href="tel:{{ $mockTask->recipient_phone }}" class="text-blue-600 text-sm">Appeler</a>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Adresse</p>
                            <p class="text-gray-600">{{ $mockTask->recipient_address }}</p>
                            @if($mockTask->recipient_city)
                                <p class="text-gray-500 text-sm">{{ $mockTask->recipient_city }}</p>
                            @endif
                        </div>
                    </div>

                    @if($mockTask->delivery_notes)
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Instructions</p>
                            <p class="text-gray-600">{{ $mockTask->delivery_notes }}</p>
                        </div>
                    </div>
                    @endif

                    @if($mockTask->cod_amount > 0)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-green-800">COD à collecter</p>
                                <p class="text-2xl font-black text-green-600">{{ number_format($mockTask->cod_amount, 3) }} DT</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- ACTIONS DE LIVRAISON -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Actions</h3>

                <div class="space-y-3">
                    <button @click="markAsDelivered()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-4 px-6 rounded-xl font-bold text-lg transition-all flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Marquer comme Livré</span>
                    </button>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="markAsUnavailable()"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white py-3 px-4 rounded-xl font-medium transition-all text-sm">
                            Destinataire Absent
                        </button>
                        <button @click="markAsCancelled()"
                                class="bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-xl font-medium transition-all text-sm">
                            Refus de Livraison
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function taskDetailApp() {
    return {
        task: @json($mockTask),
        loading: false,

        init() {
            console.log('Task Detail Custom initialized:', this.task);
        },

        async markAsPickedUp() {
            this.loading = true;
            try {
                // Simuler l'API call
                await new Promise(resolve => setTimeout(resolve, 1000));

                alert('Collecte marquée comme effectuée ! (Démonstration)');
                window.location.href = '/deliverer/run-sheet';
            } catch (error) {
                alert('Erreur lors de la collecte');
            } finally {
                this.loading = false;
            }
        },

        async markAsDelivered() {
            this.loading = true;
            try {
                // Simuler l'API call
                await new Promise(resolve => setTimeout(resolve, 1000));

                if (confirm('Livraison effectuée ! Voulez-vous capturer une signature ?')) {
                    alert('Redirection vers capture de signature (Démonstration)');
                } else {
                    alert('Livraison confirmée ! (Démonstration)');
                }
                window.location.href = '/deliverer/run-sheet';
            } catch (error) {
                alert('Erreur lors de la livraison');
            } finally {
                this.loading = false;
            }
        },

        async markAsUnavailable() {
            this.loading = true;
            try {
                const reason = prompt('Raison de l\'indisponibilité:');
                if (reason) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    alert('Tâche marquée comme indisponible (Démonstration)');
                    window.location.href = '/deliverer/run-sheet';
                }
            } catch (error) {
                alert('Erreur lors de la mise à jour');
            } finally {
                this.loading = false;
            }
        },

        async markAsCancelled() {
            this.loading = true;
            try {
                const reason = prompt('Raison du refus:');
                if (reason) {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    alert('Livraison annulée (Démonstration)');
                    window.location.href = '/deliverer/run-sheet';
                }
            } catch (error) {
                alert('Erreur lors de l\'annulation');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush

@endsection