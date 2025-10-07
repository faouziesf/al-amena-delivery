@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center space-x-3 mb-4">
            <a href="{{ route('supervisor.users.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center mr-4">
                                <span class="text-xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            {{ $user->name }}
                        </h1>
                        <div class="flex items-center space-x-3 mt-2">
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($user->role === 'CLIENT') bg-green-100 text-green-800
                                @elseif($user->role === 'DELIVERER') bg-blue-100 text-blue-800
                                @elseif($user->role === 'COMMERCIAL') bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $user->role }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($user->status === 'ACTIVE') bg-green-100 text-green-800
                                @elseif($user->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $user->status }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Membre depuis {{ $user->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('supervisor.users.edit', $user) }}"
                           class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations de base -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informations Personnelles
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Nom Complet</label>
                            <p class="text-lg font-medium text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <div class="flex items-center space-x-2">
                                <p class="text-lg font-medium text-gray-900">{{ $user->email }}</p>
                                @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Vérifié
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Non vérifié
                                </span>
                                @endif
                            </div>
                            @if($user->email_verified_at)
                                <p class="text-sm text-gray-500">Vérifié le {{ $user->email_verified_at->format('d/m/Y à H:i') }}</p>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Téléphone</label>
                            <p class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $user->phone }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Délégation</label>
                            <p class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $user->delegation->name ?? 'Aucune' }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Membre Depuis</label>
                            <p class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $user->created_at->format('d/m/Y à H:i') }}
                            </p>
                            <p class="text-sm text-gray-500">Il y a {{ $user->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-600">Dernière Activité</label>
                            <p class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $user->updated_at->format('d/m/Y à H:i') }}
                            </p>
                            <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            @if(in_array($user->role, ['CLIENT', 'DELIVERER']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Statistiques
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-red-600 mb-2">{{ $stats['total_packages'] }}</div>
                            <div class="text-sm font-medium text-red-800">
                                @if($user->role === 'CLIENT') Colis Créés @else Colis Acceptés @endif
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['delivered_packages'] }}</div>
                            <div class="text-sm font-medium text-green-800">Colis Livrés</div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($stats['wallet_balance'], 2) }}</div>
                            <div class="text-sm font-medium text-blue-800">DT Portefeuille</div>
                        </div>
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-amber-600 mb-2">{{ $stats['total_complaints'] }}</div>
                            <div class="text-sm font-medium text-amber-800">Réclamations</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Activité récente -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Activité Récente</h3>
                @if($recentActivity->count() > 0)
                <div class="space-y-4">
                    @foreach($recentActivity as $activity)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-{{ $activity['color'] ?? 'gray' }}-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-{{ $activity['color'] ?? 'gray' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($activity['type'] === 'package')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                @elseif($activity['type'] === 'transaction')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</div>
                            <div class="text-sm text-gray-600">{{ $activity['description'] }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $activity['date']->diffForHumans() }}</div>
                        </div>
                        @if(isset($activity['url']))
                        <a href="{{ $activity['url'] }}" class="text-red-600 hover:text-red-800 text-sm">
                            Voir →
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500">Aucune activité récente</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statut et actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Actions Utilisateur
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Actions -->
                    <div class="space-y-3">
                        @if($user->status === 'ACTIVE')
                        <form method="POST" action="{{ route('supervisor.users.deactivate', $user) }}" class="w-full">
                            @csrf
                            <button type="submit" onclick="return confirm('Désactiver cet utilisateur?')"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-lg hover:from-yellow-600 hover:to-amber-600 transition-all text-sm font-medium flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Désactiver
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('supervisor.users.activate', $user) }}" class="w-full">
                            @csrf
                            <button type="submit" onclick="return confirm('Activer cet utilisateur?')"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:from-green-600 hover:to-emerald-600 transition-all text-sm font-medium flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Activer
                            </button>
                        </form>
                        @endif

                        <button onclick="showResetPasswordModal()"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all text-sm font-medium flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2a2 2 0 012 2M9 7a2 2 0 00-2 2m0 0a2 2 0 00-2 2m2-2a2 2 0 012 2m-2-2a2 2 0 00-2 2"></path>
                            </svg>
                            Réinitialiser Mot de Passe
                        </button>

                        <form method="POST" action="{{ route('supervisor.users.force.logout', $user) }}" class="w-full">
                            @csrf
                            <button type="submit" onclick="return confirm('Déconnecter cet utilisateur de toutes ses sessions?')"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all text-sm font-medium flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Forcer Déconnexion
                            </button>
                        </form>

                        @if(!in_array($user->role, ['SUPERVISOR']) && !$user->packages()->exists())
                        <div class="border-t border-gray-200 pt-3 mt-4">
                            <form method="POST" action="{{ route('supervisor.users.destroy', $user) }}" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('ATTENTION: Supprimer définitivement cet utilisateur? Cette action est irréversible.')"
                                        class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all text-sm font-medium flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Supprimer Utilisateur
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations wallet (pour clients et livreurs) -->
            @if(in_array($user->role, ['CLIENT', 'DELIVERER']) && $user->wallet)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Portefeuille
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-green-700">Balance Disponible</span>
                            <span class="text-2xl font-bold text-green-800">{{ number_format($user->wallet->balance, 2) }} DT</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-yellow-700">En Attente</span>
                            <span class="text-xl font-bold text-yellow-800">{{ number_format($user->wallet->pending_amount, 2) }} DT</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-red-700">Montant Gelé</span>
                            <span class="text-xl font-bold text-red-800">{{ number_format($user->wallet->frozen_amount, 2) }} DT</span>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total</span>
                            <span class="text-xl font-bold text-gray-900">
                                {{ number_format($user->wallet->balance + $user->wallet->pending_amount + $user->wallet->frozen_amount, 2) }} DT
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sessions actives -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Informations Connexion
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700">Dernière Activité</span>
                            </div>
                            <span class="text-sm text-gray-600">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700">Inscription</span>
                            </div>
                            <span class="text-sm text-gray-600">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de réinitialisation du mot de passe -->
<div id="resetPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Réinitialiser le Mot de Passe</h3>
            <form method="POST" action="{{ route('supervisor.users.reset.password', $user) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                    <input type="password" name="password" class="w-full border-gray-300 rounded-lg" required minlength="8">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-lg" required minlength="8">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideResetPasswordModal()"
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function hideResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideResetPasswordModal();
    }
});
</script>
@endsection