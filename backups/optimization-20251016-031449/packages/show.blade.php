@extends('layouts.client')

@section('title', 'Colis ' . $package->package_code)
@section('page-title', 'Détails du Colis')
@section('page-description', 'Suivi et informations complètes')

@section('header-actions')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('client.packages.index') }}"
       class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="hidden sm:inline">Retour à la liste</span>
        <span class="sm:hidden">Retour</span>
    </a>

    @if($package->return_package_id)
    <a href="{{ route('client.returns.show-return-package', $package->return_package_id) }}"
       class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-lg">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="hidden sm:inline">📦 Suivre le Retour</span>
        <span class="sm:hidden">📦 Retour</span>
    </a>
    @endif

    @if(in_array($package->status, ['DELIVERED', 'PICKED_UP', 'ACCEPTED', 'REFUSED']))
    <a href="{{ route('client.complaints.create', $package) }}"
       class="inline-flex items-center px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <span class="hidden sm:inline">Réclamation</span>
        <span class="sm:hidden">📝</span>
    </a>
    @endif

    <button @click="window.print()"
            class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span class="hidden sm:inline">Imprimer</span>
        <span class="sm:hidden">🖨️</span>
    </button>
</div>
@endsection

@section('content')
<!-- Main container with proper mobile spacing -->
<div x-data="packageDetailsData()" x-init="init()" class="pb-6 px-4 sm:px-6 lg:px-8">

    <!-- Mobile Status Header -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-6">
        <div class="bg-gradient-to-br from-blue-600 via-purple-600 to-emerald-600 p-4 sm:p-6 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl font-bold mb-2 flex items-center">
                        📦 {{ $package->package_code }}
                    </h1>
                    <p class="text-blue-100 text-sm">Créé le {{ $package->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="flex flex-col sm:text-right space-y-2">
                    <div class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium self-start sm:self-end
                        {{ $package->status === 'DELIVERED' || $package->status === 'PAID' ? 'bg-green-500 bg-opacity-20 text-green-100 border border-green-300' :
                           ($package->status === 'RETURNED' ? 'bg-red-500 bg-opacity-20 text-red-100 border border-red-300' :
                           'bg-orange-500 bg-opacity-20 text-orange-100 border border-orange-300') }}">
                        @switch($package->status)
                            @case('CREATED') 🆕 Créé @break
                            @case('AVAILABLE') 📋 Disponible @break
                            @case('ACCEPTED') ✅ Accepté @break
                            @case('PICKED_UP') 🚚 Collecté @break
                            @case('AT_DEPOT') 🏭 Au Dépôt @break
                            @case('IN_TRANSIT') 🚛 En Cours de Livraison @break
                            @case('DELIVERED') 📦 Livré @break
                            @case('PAID') 💰 Payé @break
                            @case('RETURNED') ↩️ Retourné @break
                            @case('REFUSED') ❌ Refusé @break
                            @default {{ $package->status }}
                        @endswitch
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold">{{ number_format($package->cod_amount, 3) }} DT</p>
                        <p class="text-xs text-blue-100">Montant COD</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile-First Layout -->
    <div class="space-y-6">

        <!-- Tracking Progress - Always Visible on Mobile -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                🕒 Suivi en Temps Réel
            </h3>

            <div class="space-y-4">
                @php
                    $statuses = [
                        'CREATED' => ['🆕 Créé', 'Colis créé dans le système'],
                        'AVAILABLE' => ['📋 Disponible', 'Prêt pour collecte'],
                        'ACCEPTED' => ['✅ Accepté', 'Pris en charge par le livreur'],
                        'PICKED_UP' => ['🚚 Collecté', 'Colis récupéré'],
                        'AT_DEPOT' => ['🏭 Au Dépôt', 'Colis arrivé au dépôt'],
                        'IN_TRANSIT' => ['🚛 En Cours de Livraison', 'En route vers le destinataire'],
                        'DELIVERED' => ['📦 Livré', 'Remis au destinataire'],
                        'PAID' => ['💰 Payé', 'Transaction finalisée'],
                        'RETURNED' => ['↩️ Retourné', 'Retourné à l\'expéditeur']
                    ];

                    $currentStatus = $package->status;
                    $statusKeys = array_keys($statuses);
                    $currentIndex = array_search($currentStatus, $statusKeys);
                @endphp

                @foreach($statuses as $status => $details)
                    @php
                        $statusIndex = array_search($status, $statusKeys);
                        $isCompleted = $statusIndex <= $currentIndex;
                        $isCurrent = $status === $currentStatus;
                    @endphp

                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($isCompleted)
                                <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @elseif($isCurrent)
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center animate-pulse shadow-lg">
                                    <div class="w-3 h-3 bg-white rounded-full"></div>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                            @endif
                        </div>

                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium {{ $isCurrent ? 'text-blue-600' : ($isCompleted ? 'text-emerald-600' : 'text-gray-500') }}">
                                {{ $details[0] }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $details[1] }}</p>
                        </div>

                        @if($isCurrent)
                            <div class="ml-2 animate-ping w-2 h-2 bg-blue-500 rounded-full"></div>
                        @endif
                    </div>

                    @if(!$loop->last && $status !== 'RETURNED')
                        <div class="ml-4 w-px h-4 bg-gray-200"></div>
                    @endif
                @endforeach
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button @click="refreshStatus()"
                            class="flex items-center justify-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        🔄 Actualiser
                    </button>

                    @if(in_array($package->status, ['DELIVERED', 'PICKED_UP', 'ACCEPTED', 'REFUSED']))
                    <a href="{{ route('client.complaints.create', $package) }}"
                       class="flex items-center justify-center px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        📝 Réclamation
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Route Information - Mobile Optimized -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                🗺️ Itinéraire
            </h3>

            <div class="space-y-4">
                <!-- From -->
                <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                    <div class="w-4 h-4 bg-blue-500 rounded-full flex-shrink-0"></div>
                    <div class="ml-3 flex-1">
                        <p class="font-medium text-blue-900">{{ $package->delegationFrom->name }}</p>
                        <p class="text-sm text-blue-700">📍 Délégation d'origine</p>
                        @if($package->delegationFrom->zone)
                            <p class="text-xs text-blue-600">Zone: {{ $package->delegationFrom->zone }}</p>
                        @endif
                    </div>
                </div>

                <!-- Arrow -->
                <div class="flex justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>

                <!-- To -->
                <div class="flex items-center p-3 bg-emerald-50 rounded-lg">
                    <div class="w-4 h-4 bg-emerald-500 rounded-full flex-shrink-0"></div>
                    <div class="ml-3 flex-1">
                        <p class="font-medium text-emerald-900">{{ $package->delegationTo->name }}</p>
                        <p class="text-sm text-emerald-700">🎯 Délégation de destination</p>
                        @if($package->delegationTo->zone)
                            <p class="text-xs text-emerald-600">Zone: {{ $package->delegationTo->zone }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- People Information - Mobile Cards -->
        <div class="grid grid-cols-1 gap-4">
            <!-- Sender -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    👤 Expéditeur
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                            {{ substr($package->sender_data['name'] ?? 'E', 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="font-medium text-gray-900">{{ $package->sender_data['name'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">📱 {{ $package->sender_data['phone'] ?? 'N/A' }}</p>
                            @if(isset($package->sender_data['address']))
                                <p class="text-xs text-gray-500 mt-1">📍 {{ Str::limit($package->sender_data['address'], 50) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipient -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    🎯 Destinataire
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-emerald-50 rounded-lg">
                        <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white font-bold">
                            {{ substr($package->recipient_data['name'] ?? 'D', 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="font-medium text-gray-900">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">📱 {{ $package->recipient_data['phone'] ?? 'N/A' }}</p>
                            @if(isset($package->recipient_data['address']))
                                <p class="text-xs text-gray-500 mt-1">📍 {{ Str::limit($package->recipient_data['address'], 50) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Details - Mobile Optimized -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                📦 Détails du Colis
            </h3>

            <div class="space-y-4">
                <!-- Content Description -->
                <div class="p-3 bg-purple-50 rounded-lg">
                    <p class="text-sm font-medium text-purple-800 mb-1">📋 Contenu</p>
                    <p class="text-purple-900">{{ $package->content_description }}</p>
                </div>

                <!-- Financial Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-green-800 mb-1">💰 Montant COD</p>
                        <p class="text-xl font-bold text-green-600">{{ number_format($package->cod_amount, 3) }} DT</p>
                    </div>

                    <div class="p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-blue-800 mb-1">🚚 Frais de livraison</p>
                        <p class="text-lg font-semibold text-blue-600">{{ number_format($package->delivery_fee, 3) }} DT</p>
                    </div>
                </div>

                @if($package->notes)
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800 mb-1">📝 Notes spéciales</p>
                    <p class="text-yellow-900">{{ $package->notes }}</p>
                </div>
                @endif

                @if($package->amount_in_escrow > 0)
                <div class="p-3 bg-indigo-50 rounded-lg">
                    <p class="text-sm font-medium text-indigo-800 mb-1">🔒 Montant en escrow</p>
                    <p class="text-lg font-semibold text-indigo-600">{{ number_format($package->amount_in_escrow, 3) }} DT</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Assigned Deliverer -->
        @if($package->assignedDeliverer)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                🚛 Livreur Assigné
            </h3>

            <div class="flex items-center p-3 bg-gradient-to-r from-blue-50 to-emerald-50 rounded-lg">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    {{ substr($package->assignedDeliverer->name, 0, 2) }}
                </div>
                <div class="ml-4 flex-1">
                    <p class="font-medium text-gray-900">{{ $package->assignedDeliverer->name }}</p>
                    <p class="text-sm text-gray-600">📱 {{ $package->assignedDeliverer->phone ?? 'Téléphone non disponible' }}</p>
                    @if($package->assigned_at)
                        <p class="text-xs text-gray-500">Assigné le {{ $package->assigned_at->format('d/m/Y à H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($package->delivery_attempts > 0)
            <div class="mt-4 p-3 bg-orange-50 rounded-lg border border-orange-200">
                <p class="text-sm text-orange-800 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ⚠️ {{ $package->delivery_attempts }} tentative(s) de livraison
                </p>
            </div>
            @endif
        </div>
        @endif

        <!-- Complaints Section -->
        @if($package->complaints->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                📝 Réclamations
            </h3>

            <div class="space-y-4">
                @foreach($package->complaints as $complaint)
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 space-y-2 sm:space-y-0">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $complaint->status === 'RESOLVED' ? 'bg-green-100 text-green-800' :
                                   ($complaint->status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $complaint->getStatusDisplayAttribute() }}
                            </span>
                            <span class="text-sm font-medium text-gray-900">{{ $complaint->getTypeDisplayAttribute() }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mb-3">{{ $complaint->description }}</p>

                    @if($complaint->resolution_notes)
                    <div class="p-3 bg-white rounded-lg border border-green-200">
                        <p class="text-xs font-medium text-green-700 mb-1">✅ Résolution:</p>
                        <p class="text-sm text-green-800">{{ $complaint->resolution_notes }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- COD Modifications -->
        @if($package->codModifications->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                💰 Modifications COD
            </h3>

            <div class="space-y-3">
                @foreach($package->codModifications as $modification)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-blue-50 rounded-lg space-y-2 sm:space-y-0">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-blue-900">
                            💸 {{ number_format($modification->old_amount, 3) }} DT → {{ number_format($modification->new_amount, 3) }} DT
                        </p>
                        <p class="text-xs text-blue-700">{{ $modification->reason }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-600">{{ $modification->created_at->format('d/m/Y H:i') }}</p>
                        @if($modification->modifiedByCommercial)
                            <p class="text-xs text-blue-500">Par: {{ $modification->modifiedByCommercial->name }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Detailed History - Collapsible on Mobile -->
        @if($package->statusHistory->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    📚 Historique Détaillé
                </h3>
                <button @click="showAllHistory = !showAllHistory"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <span x-text="showAllHistory ? '🔼 Masquer' : '🔽 Voir plus'"></span>
                </button>
            </div>

            <div class="space-y-4" x-show="showAllHistory" x-transition>
                @foreach($package->statusHistory->take(10) as $history)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $history->getPreviousStatusDisplayAttribute() }} → {{ $history->getNewStatusDisplayAttribute() }}
                        </p>
                        @if($history->notes)
                            <p class="text-xs text-gray-600 mt-1">{{ $history->notes }}</p>
                        @endif
                        <div class="flex flex-col sm:flex-row sm:items-center mt-1 text-xs text-gray-500 space-y-1 sm:space-y-0">
                            <span>📅 {{ $history->created_at->format('d/m/Y H:i') }}</span>
                            @if($history->changedByUser)
                                <span class="sm:ml-2">👤 Par: {{ $history->changedByUser->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
function packageDetailsData() {
    return {
        showAllHistory: false,

        async refreshStatus() {
            try {
                const response = await fetch(`/client/api/packages/{{ $package->id }}/status`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.status !== '{{ $package->status }}') {
                        this.showNotification('🎉 Statut mis à jour! Rechargement...', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        this.showNotification('✅ Le statut est à jour', 'success');
                    }
                }
            } catch (error) {
                console.error('Erreur refresh statut:', error);
                this.showNotification('❌ Erreur lors de la mise à jour', 'error');
            }
        },

        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        },

        init() {
            // Auto-refresh every 2 minutes for packages in progress
            @if(in_array($package->status, ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP']))
            setInterval(() => {
                this.refreshStatus();
            }, 120000);
            @endif
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .print-break { page-break-after: always; }
    body { font-size: 12px; }
    .bg-gradient-to-br { background: #3B82F6 !important; }
}

@media (max-width: 640px) {
    .pb-6 {
        padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
    }
}

[x-cloak] {
    display: none !important;
}
</style>
@endpush
@endsection