@extends('layouts.client')

@section('title', 'Mes demandes de retrait')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6 px-4 py-2 sm:py-3 lg:px-4 lg:py-3 sm:py-2 sm:py-3">
    <div class="max-w-7xl mx-auto">
        <!-- Header Mobile-Optimized -->
        <div class="flex flex-col space-y-2 sm:space-y-3 mb-3 sm:mb-2 sm:mb-3 sm:mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3 flex-col sm:flex-row">
            <div class="flex flex-col space-y-3 flex-col sm:flex-row">
                <h1 class="text-lg sm:text-xl sm:text-xl sm:text-lg sm:text-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    📤 Mes retraits
                </h1>
                <p class="text-sm sm:text-sm sm:text-base text-gray-600 leading-relaxed">
                    Suivez l'état de vos demandes de retrait en temps réel
                </p>
            </div>

            <!-- Actions Mobile-First -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 flex-col sm:flex-row">
                <a href="{{ route('client.wallet.withdrawal') }}"
                   class="w-full sm:w-auto bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-4 sm:px-4 sm:px-5 lg:px-3 sm:px-4 py-2 rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center justify-center gap-2 text-sm sm:text-sm sm:text-base font-medium flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle demande
                </a>
                <a href="{{ route('client.wallet.index') }}"
                   class="w-full sm:w-auto bg-white text-purple-600 border-2 border-purple-200 px-4 sm:px-4 sm:px-5 lg:px-3 sm:px-4 py-2 rounded-lg hover:bg-purple-50 transition-all duration-300 shadow-md hover:shadow-sm transform hover:scale-105 flex items-center justify-center gap-2 text-sm sm:text-sm sm:text-base font-medium transition-all duration-300 hover:-translate-y-1 flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline">Retour au</span> Portefeuille
                </a>
            </div>
        </div>

        <!-- Alertes Mobile-Optimized -->
        @if(session('success'))
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-500 text-emerald-800 px-4 py-3 rounded-lg mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3 shadow-md hover:shadow-md">
                <div class="flex items-start flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-3 mt-0.5 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm leading-relaxed">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3 shadow-md hover:shadow-md">
                <div class="flex items-start flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-3 mt-0.5 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm leading-relaxed">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Statistiques Mobile-Responsive -->
        <div class="grid grid-cols-2 lg:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-2 sm:gap-3 lg:gap-2 sm:gap-3 lg:gap-3 sm:gap-2 sm:gap-3 lg:gap-2 sm:gap-3 lg:gap-3 sm:gap-2 sm:gap-3 mb-3 sm:mb-2 sm:mb-3 sm:mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3">
            <!-- En attente -->
            <div class="bg-gradient-to-br from-amber-100 to-orange-100 rounded-lg sm:rounded-lg p-3 sm:p-2.5 sm:p-3 lg:p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 shadow-sm border border-amber-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex flex-col space-y-2 flex-col sm:flex-row">
                    <div class="flex items-center justify-between flex-col sm:flex-row">
                        <div class="bg-amber-200 rounded-full p-2 sm:p-3">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 lg:w-8 lg:h-8 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-amber-700 text-xs sm:text-sm font-medium">En attente</p>
                        <p class="text-xl sm:text-lg sm:text-xl lg:text-xl sm:text-lg sm:text-xl font-bold text-amber-800">
                            {{ $withdrawals->where('status', 'PENDING')->count() }}
                        </p>
                        <p class="text-amber-600 text-xs sm:text-sm">À traiter</p>
                    </div>
                </div>
                @if($withdrawals->where('status', 'PENDING')->count() > 0)
                    <div class="mt-2 sm:mt-3 bg-amber-200/50 rounded-lg p-2">
                        <p class="text-sm sm:text-xs text-amber-800 font-medium">⏳ Traitement...</p>
                    </div>
                @endif
            </div>

            <!-- En cours -->
            <div class="bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg sm:rounded-lg p-3 sm:p-2.5 sm:p-3 lg:p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 shadow-sm border border-blue-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex flex-col space-y-2 flex-col sm:flex-row">
                    <div class="flex items-center justify-between flex-col sm:flex-row">
                        <div class="bg-blue-200 rounded-full p-2 sm:p-3">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 lg:w-8 lg:h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-blue-700 text-xs sm:text-sm font-medium">En cours</p>
                        <p class="text-xl sm:text-lg sm:text-xl lg:text-xl sm:text-lg sm:text-xl font-bold text-blue-800">
                            {{ $withdrawals->whereIn('status', ['APPROVED', 'IN_PROGRESS'])->count() }}
                        </p>
                        <p class="text-blue-600 text-xs sm:text-sm">Approuvées</p>
                    </div>
                </div>
                @if($withdrawals->whereIn('status', ['APPROVED', 'IN_PROGRESS'])->count() > 0)
                    <div class="mt-2 sm:mt-3 bg-blue-200/50 rounded-lg p-2">
                        <p class="text-sm sm:text-xs text-blue-800 font-medium">🚀 En traitement...</p>
                    </div>
                @endif
            </div>

            <!-- Terminées -->
            <div class="bg-gradient-to-br from-emerald-100 to-green-100 rounded-lg sm:rounded-lg p-3 sm:p-2.5 sm:p-3 lg:p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 shadow-sm border border-emerald-200 transform hover:scale-105 transition-all duration-300">
                <div class="flex flex-col space-y-2 flex-col sm:flex-row">
                    <div class="flex items-center justify-between flex-col sm:flex-row">
                        <div class="bg-emerald-200 rounded-full p-2 sm:p-3">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 lg:w-8 lg:h-8 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-emerald-700 text-xs sm:text-sm font-medium">Terminées</p>
                        <p class="text-xl sm:text-lg sm:text-xl lg:text-xl sm:text-lg sm:text-xl font-bold text-emerald-800">
                            {{ $withdrawals->where('status', 'COMPLETED')->count() }}
                        </p>
                        <p class="text-emerald-600 text-xs sm:text-sm">Réussies</p>
                    </div>
                </div>
            </div>

            <!-- Montant total -->
            <div class="bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg sm:rounded-lg p-3 sm:p-2.5 sm:p-3 lg:p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 shadow-sm border border-purple-200 transform hover:scale-105 transition-all duration-300 col-span-2 lg:col-span-1">
                <div class="flex flex-col space-y-2 flex-col sm:flex-row">
                    <div class="flex items-center justify-between flex-col sm:flex-row">
                        <div class="bg-purple-200 rounded-full p-2 sm:p-3">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 lg:w-8 lg:h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-purple-700 text-xs sm:text-sm font-medium">Montant total</p>
                        <p class="text-lg sm:text-xl lg:text-lg sm:text-xl font-bold text-purple-800">
                            {{ number_format($withdrawals->sum('amount'), 3) }} DT
                        </p>
                        <p class="text-purple-600 text-xs sm:text-sm">Toutes demandes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des demandes - Mobile-Optimized -->
        <div class="bg-white rounded-lg sm:rounded-lg shadow-sm border border-purple-100 overflow-hidden transition-all duration-300 hover:-translate-y-1">
            <div class="px-4 sm:px-4 sm:px-5 lg:px-4 py-2 sm:py-3 sm:py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 flex-col sm:flex-row">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Historique des demandes
                    </h3>
                    @if($withdrawals->count() > 0)
                        <p class="text-sm text-gray-500">{{ $withdrawals->count() }} demande{{ $withdrawals->count() > 1 ? 's' : '' }}</p>
                    @endif
                </div>
            </div>

            @if($withdrawals->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($withdrawals as $withdrawal)
                        <div class="p-2.5 sm:p-3 sm:p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 hover:bg-gradient-to-r hover:from-purple-25 hover:to-indigo-25 transition-all duration-300"
                             data-status="{{ $withdrawal->status }}">

                            <!-- Mobile Card Layout -->
                            <div class="space-y-2 sm:space-y-3">
                                <!-- Header avec montant et statut -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 flex-col sm:flex-row">
                                    <div class="flex items-center space-x-3 flex-col sm:flex-row">
                                        <div class="flex-shrink-0 flex-col sm:flex-row">
                                            <span class="text-lg sm:text-xl">{{ $withdrawal->method === 'BANK_TRANSFER' ? '🏦' : '💵' }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-lg sm:text-xl font-bold text-gray-900">
                                                #{{ $withdrawal->request_code }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                {{ $withdrawal->created_at->format('d/m/Y à H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:items-end space-y-2 flex-col sm:flex-row">
                                        <div class="text-xl sm:text-lg sm:text-xl font-bold {{ $withdrawal->status === 'COMPLETED' ? 'text-emerald-600' : ($withdrawal->status === 'REJECTED' ? 'text-red-600' : 'text-purple-600') }}">
                                            {{ number_format($withdrawal->amount, 3) }} DT
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm sm:text-xs font-medium {{ $withdrawal->status_color }} w-fit flex-col sm:flex-row shadow-sm font-medium">
                                            {{ $withdrawal->status_display }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Informations détaillées -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-2 sm:gap-3 lg:gap-2 sm:gap-3">
                                    <div class="flex items-center text-sm text-gray-600 flex-col sm:flex-row">
                                        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2 text-gray-400 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                        <span class="font-medium">{{ $withdrawal->method_display }}</span>
                                    </div>

                                    @if($withdrawal->processed_at)
                                        <div class="flex items-center text-sm text-gray-600 flex-col sm:flex-row">
                                            <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2 text-gray-400 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Traité le {{ $withdrawal->processed_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Timeline Mobile -->
                                <div class="flex items-center space-x-2 flex-col sm:flex-row">
                                    <!-- Étape 1: Créée -->
                                    <div class="flex items-center flex-col sm:flex-row">
                                        <div class="w-3 h-3 sm:w-4 sm:h-4 bg-blue-500 rounded-full flex-shrink-0 flex-col sm:flex-row"></div>
                                        <span class="text-sm sm:text-xs text-gray-500 ml-1 sm:ml-2">Créée</span>
                                    </div>

                                    <!-- Ligne de progression -->
                                    <div class="flex-1 h-0.5 bg-gray-200 relative mx-2 flex-col sm:flex-row">
                                        @if($withdrawal->processed_at)
                                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 {{ $withdrawal->status === 'COMPLETED' ? 'to-green-500' : ($withdrawal->status === 'REJECTED' ? 'to-red-500' : 'to-amber-500') }}"></div>
                                        @endif
                                    </div>

                                    @if($withdrawal->processed_at)
                                        <!-- Étape 2: Traitée -->
                                        <div class="flex items-center flex-col sm:flex-row">
                                            <div class="w-3 h-3 sm:w-4 sm:h-4 {{ $withdrawal->status === 'COMPLETED' ? 'bg-green-500' : ($withdrawal->status === 'REJECTED' ? 'bg-red-500' : 'bg-amber-500') }} rounded-full flex-shrink-0 flex-col sm:flex-row"></div>
                                            <span class="text-sm sm:text-xs text-gray-500 ml-1 sm:ml-2">
                                                {{ $withdrawal->status === 'COMPLETED' ? 'Terminée' : ($withdrawal->status === 'REJECTED' ? 'Rejetée' : 'Approuvée') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center flex-col sm:flex-row">
                                            <div class="w-3 h-3 sm:w-4 sm:h-4 bg-gray-300 rounded-full flex-shrink-0 animate-pulse flex-col sm:flex-row"></div>
                                            <span class="text-sm sm:text-xs text-gray-400 ml-1 sm:ml-2">En attente</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Notes et informations contextuelles -->
                                @if($withdrawal->processing_notes)
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg">
                                        <p class="text-sm text-blue-800">
                                            <span class="font-semibold">💬 Note du commercial:</span>
                                            <span class="block mt-1">{{ $withdrawal->processing_notes }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($withdrawal->rejection_reason)
                                    <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded-r-lg">
                                        <p class="text-sm text-red-800">
                                            <span class="font-semibold">❌ Motif de rejet:</span>
                                            <span class="block mt-1">{{ $withdrawal->rejection_reason }}</span>
                                        </p>
                                    </div>
                                @endif

                                @if($withdrawal->assignedDeliverer)
                                    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded-r-lg">
                                        <p class="text-sm text-purple-800">
                                            <span class="font-semibold">🚚 Livreur assigné:</span>
                                            <span class="block mt-1">
                                                {{ $withdrawal->assignedDeliverer->name }}
                                                @if($withdrawal->assignedDeliverer->phone)
                                                    <span class="block sm:inline sm:ml-2">📞 {{ $withdrawal->assignedDeliverer->formatted_phone }}</span>
                                                @endif
                                            </span>
                                        </p>
                                    </div>
                                @endif

                                @if($withdrawal->delivery_receipt_code)
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-r-lg">
                                        <p class="text-sm text-yellow-800">
                                            <span class="font-semibold">🔑 Code de livraison:</span>
                                            <span class="font-mono font-bold ml-2 bg-yellow-100 px-2 py-1 rounded text-lg cursor-pointer hover:bg-yellow-200 transition-colors"
                                                  onclick="copyCode(this)"
                                                  title="Cliquer pour copier">
                                                {{ $withdrawal->delivery_receipt_code }}
                                            </span>
                                        </p>
                                    </div>
                                @endif

                                <!-- Statuts spéciaux -->
                                @if($withdrawal->status === 'IN_PROGRESS' && $withdrawal->method === 'CASH_DELIVERY')
                                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-lg p-2.5 sm:p-3">
                                        <div class="flex items-start flex-col sm:flex-row">
                                            <svg class="w-5 h-5 sm:w-5 sm:h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="text-sm text-amber-800">
                                                <p class="font-semibold">🚚 Livraison en cours</p>
                                                <p class="mt-1">Un livreur vous contactera prochainement pour organiser la remise des espèces.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($withdrawal->status === 'COMPLETED')
                                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-lg p-2.5 sm:p-3">
                                        <div class="flex items-center flex-col sm:flex-row">
                                            <svg class="w-5 h-5 sm:w-5 sm:h-5 text-emerald-600 mr-2 flex-shrink-0 flex-col sm:flex-row" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <div class="text-sm text-emerald-800">
                                                <p class="font-semibold">✅ Retrait terminé avec succès</p>
                                                <p class="text-sm sm:text-xs mt-1">{{ $withdrawal->delivered_at?->format('d/m/Y à H:i') ?? $withdrawal->processed_at?->format('d/m/Y à H:i') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions et temps -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 pt-2 border-t border-gray-100 flex-col sm:flex-row">
                                    <!-- Boutons d'action -->
                                    <div class="flex flex-wrap gap-2 flex-col sm:flex-row">
                                        <a href="{{ route('client.wallet.withdrawal.show', $withdrawal) }}"
                                           class="bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-700 px-3 py-2 rounded-lg hover:from-purple-200 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105 text-sm font-medium flex items-center flex-col sm:flex-row">
                                            <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Détails
                                        </a>

                                        @if($withdrawal->canBeProcessed())
                                            <button onclick="cancelWithdrawal('{{ $withdrawal->id }}')"
                                                    class="bg-gradient-to-r from-red-100 to-pink-100 text-red-700 px-3 py-2 rounded-lg hover:from-red-200 hover:to-pink-200 transition-all duration-300 transform hover:scale-105 text-sm font-medium flex items-center flex-col sm:flex-row">
                                                <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Annuler
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Temps relatif -->
                                    <p class="text-sm sm:text-xs text-gray-500">
                                        {{ $withdrawal->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Mobile-Optimized -->
                @if($withdrawals->hasPages())
                    <div class="px-4 sm:px-4 sm:px-5 lg:px-4 py-2 sm:py-3 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-gray-200">
                        <div class="flex justify-center flex-col sm:flex-row">
                            {{ $withdrawals->links('pagination::tailwind') }}
                        </div>
                    </div>
                @endif
            @else
                <!-- État vide Mobile-Optimized -->
                <div class="p-2.5 sm:p-3 sm:p-3 sm:p-2.5 sm:p-3 sm:p-12 text-center max-w-md mx-auto"><div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-r from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3 flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-10 sm:h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3">Aucune demande de retrait</h3>
                    <p class="text-gray-600 mb-3 sm:mb-2 sm:mb-3 sm:mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3 max-w-md mx-auto text-sm sm:text-sm sm:text-base leading-relaxed">
                        Vous n'avez pas encore fait de demande de retrait. Commencez par faire votre première demande pour retirer vos fonds.
                    </p>

                    <div class="flex flex-row flex-wrap sm:flex-row justify-center gap-3 flex-col sm:flex-row"><a href="{{ route('client.wallet.withdrawal') }}"
                           class="inline-flex items-center justify-center px-4 sm:px-5 lg:px-3 sm:px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 text-sm sm:text-sm sm:text-base font-medium transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Faire ma première demande
                        </a>

                        <a href="{{ route('client.wallet.index') }}"
                           class="inline-flex items-center justify-center px-4 sm:px-5 lg:px-3 sm:px-4 py-2 bg-white text-purple-600 border-2 border-purple-200 rounded-lg hover:bg-purple-50 transition-all duration-300 transform hover:scale-105 text-sm sm:text-sm sm:text-base font-medium transform hover:scale-105 active:scale-95 transition-all duration-200 transition-all duration-300 hover:-translate-y-1 flex-col sm:flex-row">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Voir mon portefeuille
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bottom Spacing for Mobile FAB -->
<div class="h-20 sm:h-0"></div>

@push('scripts')
<script>
// Fonction pour annuler une demande de retrait
function cancelWithdrawal(withdrawalId) {
    if (confirm('⚠️ Êtes-vous sûr de vouloir annuler cette demande de retrait ?\n\nCette action est irréversible et le montant sera dégelé dans votre portefeuille.')) {
        // Créer un formulaire temporaire pour la requête POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/client/withdrawals/${withdrawalId}/cancel`;

        // Ajouter le token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// Fonction pour copier un code
function copyCode(element) {
    const text = element.textContent.trim();

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            // Feedback visuel
            const original = element.textContent;
            element.textContent = '✅ Copié!';
            element.classList.add('bg-green-100', 'text-green-800');

            setTimeout(() => {
                element.textContent = original;
                element.classList.remove('bg-green-100', 'text-green-800');
                element.classList.add('bg-yellow-100');
            }, 2000);
        });
    } else {
        // Fallback pour les navigateurs plus anciens
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);

        // Feedback visuel
        const original = element.textContent;
        element.textContent = '✅ Copié!';
        setTimeout(() => {
            element.textContent = original;
        }, 2000);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des cartes
    const cards = document.querySelectorAll('[class*="transform hover:scale-105"]');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Mise en surbrillance des demandes récentes (moins de 1 heure)
    const now = new Date();
    const oneHourAgo = new Date(now.getTime() - 60 * 60 * 1000);

    const withdrawalCards = document.querySelectorAll('[data-status]');
    withdrawalCards.forEach(card => {
        const timeElements = card.querySelectorAll('time, [datetime]');
        timeElements.forEach(element => {
            const dateValue = element.dateTime || element.textContent;
            const cardDate = new Date(dateValue);

            if (cardDate > oneHourAgo) {
                const indicator = document.createElement('span');
                indicator.className = 'inline-flex items-center px-2 py-1 rounded-full text-sm sm:text-xs font-medium bg-yellow-100 text-yellow-800 ml-2 animate-bounce';
                indicator.innerHTML = '🆕 Nouvelle';
                const header = card.querySelector('h4');
                if (header) header.appendChild(indicator);
            }
        });
    });

    // Auto-refresh pour les demandes en cours
    const pendingWithdrawals = document.querySelectorAll('[data-status="PENDING"], [data-status="APPROVED"], [data-status="IN_PROGRESS"]');
    if (pendingWithdrawals.length > 0) {
        // Actualisation automatique toutes les 2 minutes
        setInterval(function() {
            // Ajouter un indicateur de rafraîchissement
            const indicator = document.createElement('div');
            indicator.className = 'fixed top-4 right-4 bg-blue-500 text-white px-3 py-2 rounded-lg shadow-sm text-sm z-50';
            indicator.innerHTML = '🔄 Actualisation...';
            document.body.appendChild(indicator);

            setTimeout(() => {
                location.reload();
            }, 1000);
        }, 120000); // 2 minutes
    }

    // Animation des barres de progression
    const progressBars = document.querySelectorAll('[class*="bg-gradient-to-r"]');
    progressBars.forEach(bar => {
        if (bar.classList.contains('absolute')) {
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = '100%';
            }, 500);
        }
    });

    // Gestion des touches clavier
    document.addEventListener('keydown', function(e) {
        // 'N' pour nouvelle demande
        if (e.key === 'n' || e.key === 'N') {
            if (!e.ctrlKey && !e.metaKey && !e.target.matches('input, textarea')) {
                window.location.href = '{{ route("client.wallet.withdrawal") }}';
            }
        }

        // 'R' pour rafraîchir
        if (e.key === 'r' || e.key === 'R') {
            if (!e.ctrlKey && !e.metaKey && !e.target.matches('input, textarea')) {
                location.reload();
            }
        }
    });

    // Gestion des gestes tactiles pour mobile
    let touchStartY = 0;
    let touchEndY = 0;

    document.addEventListener('touchstart', e => {
        touchStartY = e.changedTouches[0].screenY;
    });

    document.addEventListener('touchend', e => {
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeDistance = touchEndY - touchStartY;
        const minSwipeDistance = 50;

        if (Math.abs(swipeDistance) > minSwipeDistance) {
            // Swipe vers le bas = rafraîchir
            if (swipeDistance > 0 && touchStartY < 100) {
                location.reload();
            }
        }
    }

    // Améliorer l'expérience mobile avec touch feedback
    const touchElements = document.querySelectorAll('button, a, [onclick], [class*="cursor-pointer"]');
    touchElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });

        element.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
});

// Lazy loading pour les images si nécessaire
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
        }
    });
}, observerOptions);

// Observer les cartes de retraits
document.querySelectorAll('.bg-white').forEach(card => {
    observer.observe(card);
});
</script>
@endpush

@push('styles')
<style>
.purple-25 {
    background-color: rgba(139, 92, 246, 0.025);
}

.hover\:from-purple-25:hover {
    background-image: linear-gradient(to right, rgba(139, 92, 246, 0.025), rgba(99, 102, 241, 0.025));
}

/* Animation fade-in */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

/* Progress bar animation */
.progress-bar {
    transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile-specific improvements */
@media (max-width: 640px) {
    /* Améliorer l'espacement sur mobile */
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    /* Optimiser les cartes statistiques pour mobile */
    .grid-cols-2 > * {
        min-height: 120px;
    }

    /* Améliorer la lisibilité des montants */
    .text-xl {
        font-size: 1.5rem;
    }

    /* Optimiser les boutons pour le tactile */
    button, a {
        min-height: 44px;
        min-width: 44px;
    }

    /* Améliorer les zones tactiles */
    [onclick], [class*="cursor-pointer"] {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    /* Très petits écrans */
    .text-lg sm:text-xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }

    .text-lg {
        font-size: 1rem;
        line-height: 1.5rem;
    }

    /* Espacement réduit */
    .space-y-2 sm:space-y-3 > * + * {
        margin-top: 0.75rem;
    }

    .space-y-3 > * + * {
        margin-top: 0.5rem;
    }
}

/* Touch feedback */
.touch-feedback:active {
    transform: scale(0.95);
    transition: transform 0.1s ease;
}

/* États de hover améliorés pour desktop */
@media (min-width: 1024px) {
    .withdrawal-card:hover {
        box-shadow: 0 10px 25px rgba(139, 92, 246, 0.15);
    }
}

/* Loading state pour les boutons */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Amélioration de l'accessibilité */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Améliorations pour le mode sombre (si supporté) */
@media (prefers-color-scheme: dark) {
    .bg-gradient-to-br {
        background-image: linear-gradient(to bottom right, rgb(88, 28, 135), rgb(0, 0, 0), rgb(55, 48, 163));
    }
}

/* Pull to refresh indicator */
.pull-to-refresh {
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #8B5CF6, #EC4899);
    color: white;
    padding: 8px 20px;
    border-radius: 0 0 20px 20px;
    font-size: 12px;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.pull-to-refresh.show {
    opacity: 1;
}
</style>
@endpush
@endsection