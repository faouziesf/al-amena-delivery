@extends('layouts.deliverer')

@section('title', 'Ma Tournée')

@section('content')
<div x-data="runSheetApp()" x-init="init()" class="h-full bg-gray-50">

    <!-- Header Principal avec Nom du Livreur -->
    <div class="relative overflow-hidden">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700">
            <div class="relative text-white px-6 py-8">
                <div>
                    <!-- Header avec nom et bouton print -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h1>
                                <p class="text-sm text-white/70">Livreur · Al-Amena</p>
                            </div>
                        </div>

                        <!-- Bouton Imprimer Run Sheet -->
                        <button @click="printRunSheet()" class="w-12 h-12 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Stats centraux -->
                    <div class="text-center">
                        <h2 class="text-2xl font-black mb-2" x-text="getTabTitle()"></h2>
                        <p class="text-white/80 font-medium mb-6" x-text="formatDate()"></p>

                        <!-- Progress Ring pour Ma Tournée uniquement -->
                        <div x-show="activeTab === 'mytasks'" class="relative inline-flex items-center justify-center">
                            <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center">
                                <div class="text-center">
                                    <span class="text-3xl font-black text-white" x-text="pendingTasksCount"></span>
                                    <div class="text-sm font-medium text-white/80">restantes</div>
                                </div>
                            </div>
                            <!-- Circular Progress Indicator -->
                            <svg class="absolute inset-0 w-24 h-24 transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="44" stroke="rgba(255,255,255,0.2)" stroke-width="3" fill="none"/>
                                <circle cx="48" cy="48" r="44" stroke="#06B6D4" stroke-width="3" fill="none"
                                       stroke-dasharray="276"
                                       :stroke-dashoffset="276 - (progressPercentage * 276 / 100)"
                                       class="transition-all duration-1000 ease-out"/>
                            </svg>
                        </div>

                        <!-- Stats pour les autres onglets -->
                        <div x-show="activeTab === 'available'" class="grid grid-cols-2 gap-6">
                            <div class="text-center bg-white/20 p-4 rounded-xl">
                                <div class="text-2xl font-bold mb-1" x-text="availablePickups.length"></div>
                                <div class="text-sm text-white/70">Pickups disponibles</div>
                            </div>
                            <div class="text-center bg-white/20 p-4 rounded-xl">
                                <div class="text-2xl font-bold mb-1" x-text="getZoneInfo()"></div>
                                <div class="text-sm text-white/70">Dans ma zone</div>
                            </div>
                        </div>

                        <div x-show="activeTab === 'actions'" class="flex items-center justify-center space-x-6">
                            <div class="text-center bg-white/20 p-4 rounded-xl">
                                <div class="text-2xl font-bold mb-1" x-text="formatAmount(currentBalance)"></div>
                                <div class="text-sm text-white/70">Solde actuel</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Principale -->
    <div class="bg-white shadow-sm">
        <div class="px-6 py-4">
            <div class="relative flex space-x-4">
                <button @click="activeTab = 'mytasks'"
                        :class="activeTab === 'mytasks' ? 'text-blue-600 font-bold' : 'text-gray-500'"
                        class="flex-1 py-3 relative transition-all">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-lg font-semibold">Ma Tournée</span>
                    </div>
                    <div x-show="activeTab === 'mytasks'" class="absolute bottom-0 left-2 right-2 h-1 bg-blue-500 rounded-full"></div>
                </button>

                <button @click="activeTab = 'available'"
                        :class="activeTab === 'available' ? 'text-orange-600 font-bold' : 'text-gray-500'"
                        class="flex-1 py-3 relative transition-all">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-lg font-semibold">Pickups</span>
                    </div>
                    <div x-show="activeTab === 'available'" class="absolute bottom-0 left-2 right-2 h-1 bg-orange-500 rounded-full"></div>
                </button>

                <button @click="activeTab = 'actions'"
                        :class="activeTab === 'actions' ? 'text-green-600 font-bold' : 'text-gray-500'"
                        class="flex-1 py-3 relative transition-all">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-lg font-semibold">Actions</span>
                    </div>
                    <div x-show="activeTab === 'actions'" class="absolute bottom-0 left-2 right-2 h-1 bg-green-500 rounded-full"></div>
                </button>
            </div>
        </div>

        <!-- Sub-navigation pour Ma Tournée -->
        <div x-show="activeTab === 'mytasks'" class="px-6 pb-4 border-t border-gray-100">
            <div class="flex space-x-6 pt-3">
                <button @click="currentTab = 'all'" :class="currentTab === 'all' ? 'text-blue-600 font-bold' : 'text-gray-400'" class="pb-2 relative transition-all">
                    <span>Toutes</span>
                    <div x-show="currentTab === 'all'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                </button>
                <button @click="currentTab = 'pending'" :class="currentTab === 'pending' ? 'text-blue-600 font-bold' : 'text-gray-400'" class="pb-2 relative transition-all">
                    <span>En cours</span>
                    <div x-show="currentTab === 'pending'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                </button>
                <button @click="currentTab = 'completed'" :class="currentTab === 'completed' ? 'text-blue-600 font-bold' : 'text-gray-400'" class="pb-2 relative transition-all">
                    <span class="flex items-center space-x-1">
                        <span>Terminées</span>
                        <div class="w-5 h-5 bg-green-500 text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="completedTasksCount"></div>
                    </span>
                    <div x-show="currentTab === 'completed'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- Contenu Dynamique selon l'Onglet -->
    <div class="px-6 py-6 space-y-6 pb-24">

        <!-- État de chargement modernisé -->
        <div x-show="loading" class="text-center py-16">
            <div class="relative mx-auto mb-8">
                <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full mx-auto animate-spin"></div>
            </div>
            <div class="space-y-2">
                <p class="text-xl font-bold text-gray-900">Chargement de votre tournée</p>
                <p class="text-gray-600">Synchronisation en cours...</p>
            </div>
        </div>

        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>

        <!-- Liste des tâches modernisée -->
        <template x-for="(task, index) in filteredTasks" :key="task.id">
            <div @click="openTaskDetail(task)"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 cursor-pointer p-6 hover:shadow-md transition-shadow">

                <!-- Header avec Icône et Statut -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center space-x-4 flex-1">
                        <!-- Icône avec fond coloré -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="getTaskIconBg(task)">
                            <span class="text-xl" x-text="getTaskIcon(task)"></span>
                        </div>
                        <!-- Info Type -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-lg font-bold text-gray-900" x-text="getTaskType(task)"></span>
                                <div class="px-3 py-1 rounded-full text-sm font-bold" :class="getTaskStatusBadge(task)" x-text="getTaskStatus(task)"></div>
                            </div>
                            <!-- Indicateur de priorité -->
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full" :class="getTaskPriorityColor(task)"></div>
                                <span class="text-sm text-gray-500" x-text="getTaskPriority(task)"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Check Icon pour tâches terminées -->
                    <div x-show="task.status === 'DELIVERED' || task.status === 'PICKED_UP'"
                         class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Informations Client Modernisées -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold mb-3 text-gray-900" x-text="getClientName(task)"></h3>
                    <div class="flex items-start space-x-3">
                        <div class="w-5 h-5 mt-1 flex-shrink-0 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 leading-relaxed" x-text="getTaskAddress(task)"></p>
                    </div>
                </div>

                <!-- Footer avec Métriques -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                    <!-- COD Amount pour livraisons -->
                    <div x-show="task.type === 'delivery'" class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-green-500 text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Montant COD</p>
                            <p class="text-lg font-bold text-green-600" x-text="formatAmount(task.cod_amount)"></p>
                        </div>
                    </div>

                    <!-- Package Count pour collectes -->
                    <div x-show="task.type === 'pickup'" class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-500 text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Colis à collecter</p>
                            <p class="text-lg font-bold text-orange-600" x-text="task.packages_count || 1"></p>
                        </div>
                    </div>

                    <!-- Time Info -->
                    <div class="text-right">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-500" x-text="getTaskTime(task)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- CONTENU ONGLET 1: MA TOURNÉE -->
        <div x-show="activeTab === 'mytasks'">
            <!-- État vide -->
            <div x-show="!loading && filteredTasks.length === 0" class="text-center py-16">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Excellente nouvelle !</h3>
                <p class="text-lg text-gray-600">Toutes vos tâches sont terminées pour aujourd'hui.</p>
            </div>
        </div>

        <!-- CONTENU ONGLET 2: PICKUPS DISPONIBLES -->
        <div x-show="activeTab === 'available'">
            <div class="space-y-4">
                <template x-for="(pickup, index) in availablePickups" :key="pickup.id">
                    <div class="soft-card interactive p-6 fade-in" :style="'animation-delay: ' + (index * 0.1) + 's'">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- En-tête pickup -->
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: var(--warning); color: white;">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-title font-bold" style="color: var(--text-primary)" x-text="pickup.pickup_contact_name || pickup.client_name"></h3>
                                        <div class="px-3 py-1 rounded-full text-sm font-bold" style="background: rgba(243, 156, 18, 0.1); color: var(--warning);">Pickup Disponible</div>
                                    </div>
                                </div>

                                <!-- Informations -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 mt-1" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        <div>
                                            <p class="text-body" style="color: var(--text-secondary)" x-text="pickup.address"></p>
                                            <p class="text-sm" style="color: var(--text-muted)" x-text="pickup.delegation_from"></p>
                                        </div>
                                    </div>

                                    <div x-show="pickup.pickup_phone" class="flex items-center space-x-3">
                                        <svg class="w-5 h-5" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <span class="text-sm" style="color: var(--text-secondary)" x-text="pickup.pickup_phone"></span>
                                    </div>

                                    <div x-show="pickup.pickup_notes" class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 mt-1" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <p class="text-sm" style="color: var(--text-secondary)" x-text="pickup.pickup_notes"></p>
                                    </div>

                                    <div class="flex items-center space-x-6">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <span class="text-caption" style="color: var(--text-muted)" x-text="pickup.estimated_packages + ' colis estimés'"></span>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-caption" style="color: var(--text-muted)" x-text="pickup.requested_pickup_date || pickup.estimated_time"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton Accepter -->
                                <button @click="acceptPickup(pickup)"
                                        class="w-full soft-button interactive py-4 px-6 text-white font-bold text-lg flex items-center justify-center space-x-3"
                                        style="background: linear-gradient(135deg, var(--success), #27AE60);">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>ACCEPTER CE PICKUP</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- État vide pour pickups -->
                <div x-show="availablePickups.length === 0" class="text-center py-16">
                    <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucun pickup disponible</h3>
                    <p class="text-lg text-gray-600">Revenez plus tard pour voir de nouvelles demandes de ramassage.</p>
                </div>
            </div>
        </div>

        <!-- CONTENU ONGLET 3: ACTIONS RAPIDES -->
        <div x-show="activeTab === 'actions'">
            <div class="space-y-4">
                <!-- Recharger un Compte Client -->
                <div class="soft-card interactive p-8" @click="openClientRecharge()">
                    <div class="flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--success), #27AE60); color: white;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Recharger un Compte Client</h3>
                            <p class="text-body" style="color: var(--text-secondary)">Ajouter des fonds sur le compte prépayé d'un client</p>
                        </div>
                        <svg class="w-6 h-6" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Voir mon Wallet -->
                <div class="soft-card interactive p-8" @click="openWallet()">
                    <div class="flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), var(--accent-light)); color: white;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Mon Wallet / Ma Caisse</h3>
                            <p class="text-body" style="color: var(--text-secondary)">Consulter mon solde et l'historique des transactions</p>
                            <div class="mt-2">
                                <span class="text-title font-bold" style="color: var(--accent)" x-text="formatAmount(currentBalance)"></span>
                            </div>
                        </div>
                        <svg class="w-6 h-6" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Contacter le Support -->
                <div class="soft-card interactive p-8" @click="contactSupport()">
                    <div class="flex items-center space-x-6">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center" style="background: linear-gradient(135deg, #8E44AD, #9B59B6); color: white;">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Contacter le Support</h3>
                            <p class="text-body" style="color: var(--text-secondary)">Aide et assistance pour toutes vos questions</p>
                        </div>
                        <svg class="w-6 h-6" style="color: var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé de progression modernisé -->
        <div x-show="tasks.length > 0" class="soft-card p-8 mt-8 fade-in">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-title font-bold mb-2" style="color: var(--text-primary)">Progression de la journée</h3>
                    <p class="text-body" style="color: var(--text-secondary)">Suivez vos performances en temps réel</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-black mb-1" style="color: var(--accent)" x-text="Math.round(progressPercentage) + '%'"></div>
                    <div class="text-caption" style="color: var(--text-muted)" x-text="`${completedTasksCount}/${totalTasksCount} terminées`"></div>
                </div>
            </div>

            <!-- Progress Bar Modernisée -->
            <div class="relative mb-6">
                <div class="w-full h-3 rounded-full" style="background: rgba(0,0,0,0.06);">
                    <div class="h-3 rounded-full progress-gradient transition-all duration-1000 ease-out"
                         :style="`width: ${progressPercentage}%`"></div>
                </div>
                <!-- Progress Indicators -->
                <div class="flex justify-between mt-2">
                    <span class="text-caption" style="color: var(--text-muted)">0%</span>
                    <span class="text-caption" style="color: var(--text-muted)">50%</span>
                    <span class="text-caption" style="color: var(--text-muted)">100%</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 rounded-2xl" style="background: rgba(46, 204, 113, 0.1);">
                    <div class="text-2xl font-bold mb-1" style="color: var(--success)" x-text="completedTasksCount"></div>
                    <div class="text-caption" style="color: var(--text-muted)">Terminées</div>
                </div>
                <div class="text-center p-4 rounded-2xl" style="background: rgba(0, 171, 228, 0.1);">
                    <div class="text-2xl font-bold mb-1" style="color: var(--accent)" x-text="pendingTasksCount"></div>
                    <div class="text-caption" style="color: var(--text-muted)">En cours</div>
                </div>
                <div class="text-center p-4 rounded-2xl" style="background: rgba(243, 156, 18, 0.1);">
                    <div class="text-2xl font-bold mb-1" style="color: var(--warning)" x-text="totalTasksCount"></div>
                    <div class="text-caption" style="color: var(--text-muted)">Total</div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function runSheetApp() {
    return {
        loading: true,
        tasks: [],
        availablePickups: [],
        currentBalance: 347.250,
        activeTab: 'mytasks', // mytasks, available, actions
        currentTab: 'all', // all, pending, completed (sous-navigation pour mytasks)

        init() {
            this.loadTasks();
            this.loadAvailablePickups();
            // Actualisation automatique toutes les 30 secondes
            setInterval(() => {
                this.loadTasks();
                this.loadAvailablePickups();
            }, 30000);

            // Animation d'entrée différée pour les cartes
            setTimeout(() => {
                this.animateCards();
            }, 500);
        },

        animateCards() {
            const cards = document.querySelectorAll('.soft-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        },

        async loadAvailablePickups() {
            try {
                const response = await fetch('/deliverer/api/simple/available-pickups');
                if (response.ok) {
                    const pickups = await response.json();
                    this.availablePickups = pickups.map(pickup => ({
                        id: pickup.id,
                        client_name: pickup.client_name || 'Client non spécifié',
                        address: pickup.pickup_address,
                        estimated_packages: '1-3', // Estimation basique
                        estimated_time: '10-15 min',
                        priority: 'normale',
                        pickup_contact_name: pickup.pickup_contact_name,
                        pickup_phone: pickup.pickup_phone,
                        pickup_notes: pickup.pickup_notes,
                        delegation_from: pickup.delegation_from,
                        requested_pickup_date: pickup.requested_pickup_date
                    }));
                }
            } catch (error) {
                console.error('Erreur chargement pickups:', error);
                this.availablePickups = [];
            }
        },

        async acceptPickup(pickup) {
            try {
                showToast('Acceptation de la demande de collecte...', 'info');

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    showToast('Erreur: Token CSRF non trouvé', 'error');
                    return;
                }

                const response = await fetch(`/deliverer/pickup-requests/${pickup.id}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Retirer de la liste des pickups disponibles
                    this.availablePickups = this.availablePickups.filter(p => p.id !== pickup.id);

                    // Recharger les tâches pour inclure le nouveau pickup accepté
                    await this.loadTasks();

                    // Basculer sur l'onglet Ma Tournée
                    this.activeTab = 'mytasks';
                    this.currentTab = 'pending';

                    // Notification de succès
                    showToast('Pickup accepté et ajouté à votre tournée !', 'success');
                } else {
                    showToast('Erreur: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur lors de l\'acceptation:', error);
                showToast('Erreur lors de l\'acceptation de la demande de collecte', 'error');
            }
        },

        printRunSheet() {
            // Fonction d'impression du run sheet
            showToast('Génération du run sheet en cours...', 'success');

            // Ouvrir la page d'impression dans une nouvelle fenêtre
            const printUrl = '/deliverer/print/run-sheet?autoprint=1';
            const printWindow = window.open(printUrl, '_blank', 'width=800,height=600');

            // Focus sur la nouvelle fenêtre
            if (printWindow) {
                printWindow.focus();
            }
        },

        openClientRecharge() {
            // Navigation vers la page de recharge client
            window.location.href = '/deliverer/client-recharge';
        },

        openWallet() {
            // Navigation vers le wallet
            window.location.href = '/deliverer/wallet-optimized';
        },

        contactSupport() {
            // Ouvrir un dialogue de support ou rediriger
            const supportNumber = '+216 70 123 456';
            window.location.href = `tel:${supportNumber}`;
        },

        getTabTitle() {
            switch(this.activeTab) {
                case 'mytasks': return 'Ma Tournée Active';
                case 'available': return 'Pickups Disponibles';
                case 'actions': return 'Actions Rapides';
                default: return 'Tableau de Bord';
            }
        },

        getZoneInfo() {
            return this.availablePickups.filter(p => p.priority === 'haute').length + '/' + this.availablePickups.length;
        },

        async loadTasks() {
            try {
                const [pickupsRes, deliveriesRes] = await Promise.all([
                    fetch('/deliverer/api/simple/pickups'),
                    fetch('/deliverer/api/simple/deliveries')
                ]);

                let allTasks = [];

                if (pickupsRes.ok) {
                    const pickups = await pickupsRes.json();
                    allTasks = allTasks.concat(pickups.map(p => ({...p, type: 'pickup'})));
                }

                if (deliveriesRes.ok) {
                    const deliveries = await deliveriesRes.json();
                    allTasks = allTasks.concat(deliveries.map(d => ({...d, type: 'delivery'})));
                }

                // Trier : les tâches en cours d'abord, puis par priorité
                this.tasks = allTasks.sort((a, b) => {
                    const statusPriority = {
                        'ACCEPTED': 1, 'PICKED_UP': 1, 'AVAILABLE': 2,
                        'DELIVERED': 3, 'UNAVAILABLE': 3, 'CANCELLED': 3
                    };
                    return (statusPriority[a.status] || 2) - (statusPriority[b.status] || 2);
                });

            } catch (error) {
                console.error('Erreur chargement:', error);
            } finally {
                this.loading = false;
            }
        },

        openTaskDetail(task) {
            // Navigation vers la vue détail
            window.location.href = `/deliverer/task/${task.id}`;
        },

        formatDate() {
            return new Date().toLocaleDateString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3) + ' DT';
        },

        getTaskIcon(task) {
            return task.type === 'delivery' ? '🚚' : '📦';
        },

        getTaskType(task) {
            return task.type === 'delivery' ? 'LIVRAISON' : 'RAMASSAGE';
        },

        getClientName(task) {
            return task.type === 'delivery' ? task.recipient_name : task.pickup_contact;
        },

        getTaskAddress(task) {
            if (task.type === 'delivery') {
                return task.recipient_address;
            }
            return task.pickup_address;
        },

        getTaskBorderColor(task) {
            const completed = ['DELIVERED', 'PICKED_UP'].includes(task.status);
            const failed = ['UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status);

            if (completed) return 'border-green-300 bg-green-50';
            if (failed) return 'border-red-300 bg-red-50';
            return 'border-blue-300 bg-blue-50';
        },

        getTaskStatusColor(task) {
            const completed = ['DELIVERED', 'PICKED_UP'].includes(task.status);
            const failed = ['UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status);

            if (completed) return 'bg-green-500';
            if (failed) return 'bg-red-500';
            return 'bg-blue-500';
        },

        getTaskStatus(task) {
            const statusMap = {
                'AVAILABLE': 'À faire',
                'ACCEPTED': 'En cours',
                'PICKED_UP': task.type === 'pickup' ? 'Terminé' : 'Collecté',
                'DELIVERED': 'Livré',
                'UNAVAILABLE': 'Indisponible',
                'CANCELLED': 'Annulé',
                'REFUSED': 'Refusé'
            };
            return statusMap[task.status] || task.status;
        },

        getTaskTime(task) {
            if (task.updated_at) {
                return new Date(task.updated_at).toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            return '';
        },

        get pendingTasksCount() {
            return this.tasks.filter(task =>
                !['DELIVERED', 'PICKED_UP', 'UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status)
            ).length;
        },

        get completedTasksCount() {
            return this.tasks.filter(task =>
                ['DELIVERED', 'PICKED_UP'].includes(task.status)
            ).length;
        },

        get totalTasksCount() {
            return this.tasks.length;
        },

        get progressPercentage() {
            if (this.totalTasksCount === 0) return 0;
            return (this.completedTasksCount / this.totalTasksCount) * 100;
        },

        get filteredTasks() {
            if (this.activeTab !== 'mytasks') return [];

            switch (this.currentTab) {
                case 'pending':
                    return this.tasks.filter(task =>
                        !['DELIVERED', 'PICKED_UP', 'UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status)
                    );
                case 'completed':
                    return this.tasks.filter(task =>
                        ['DELIVERED', 'PICKED_UP'].includes(task.status)
                    );
                default:
                    return this.tasks;
            }
        },

        // Nouvelles méthodes pour le design modernisé
        getTaskIconBg(task) {
            const completed = ['DELIVERED', 'PICKED_UP'].includes(task.status);
            const failed = ['UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status);

            if (completed) return 'bg-green-100';
            if (failed) return 'bg-red-100';
            return task.type === 'delivery' ? 'bg-blue-100' : 'bg-orange-100';
        },

        getTaskStatusBadge(task) {
            const completed = ['DELIVERED', 'PICKED_UP'].includes(task.status);
            const failed = ['UNAVAILABLE', 'CANCELLED', 'REFUSED'].includes(task.status);

            if (completed) return 'bg-green-100 text-green-800';
            if (failed) return 'bg-red-100 text-red-800';
            return 'bg-blue-100 text-blue-800';
        },

        getTaskPriorityColor(task) {
            // Simuler une priorité basée sur l'heure
            const hour = new Date().getHours();
            if (hour < 12) return 'bg-red-500'; // Haute priorité le matin
            if (hour < 16) return 'bg-yellow-500'; // Moyenne priorité après-midi
            return 'bg-green-500'; // Basse priorité en soirée
        },

        getTaskPriority(task) {
            const hour = new Date().getHours();
            if (hour < 12) return 'Priorité haute';
            if (hour < 16) return 'Priorité moyenne';
            return 'Priorité normale';
        }
    }
}
</script>
@endpush
@endsection