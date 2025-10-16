@extends('layouts.client')

@section('title', 'Mon Profil')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- En-tête -->
    <div class="mb-3 sm:mb-2 sm:mb-3 md:mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-2 sm:mb-3 sm:mb-0">
                <h1 class="text-lg sm:text-xl md:text-xl sm:text-lg sm:text-xl font-bold text-gray-900 mb-2">Mon Profil</h1>
                <p class="text-gray-600 text-sm md:text-base">Gérez vos informations personnelles et professionnelles</p>
            </div>
            <a href="{{ route('client.profile.edit') }}"
               class="bg-purple-600 text-white px-4 md:px-4 py-2.5 md:py-3 rounded-lg hover:bg-purple-700 transition-colors text-sm md:text-base inline-flex items-center justify-center">
                <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-2 sm:gap-3 lg:gap-2 sm:gap-3 sm:gap-3 sm:gap-2 sm:gap-3">

        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-2 sm:space-y-3 lg:space-y-3 sm:space-y-2 sm:space-y-3">

            <!-- Informations personnelles -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3 border-b border-gray-200">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">Informations Personnelles</h3>
                </div>
                <div class="p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3 space-y-2 sm:space-y-3">
                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nom complet</label>
                            <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Téléphone</label>
                            <p class="text-gray-900">{{ $user->phone ?? 'Non renseigné' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Statut du compte</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $user->status_color }}">
                                {{ $user->status_display }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Adresse</label>
                        <p class="text-gray-900">{{ $user->address ?? 'Non renseigné' }}</p>
                    </div>
                </div>
            </div>

            <!-- Informations professionnelles -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3 border-b border-gray-200">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">Informations Professionnelles</h3>
                </div>
                <div class="p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3">
                    @if($user->clientProfile && $user->clientProfile->hasBusinessInfo())
                        <div class="space-y-2 sm:space-y-3">
                            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Nom de boutique/entreprise</label>
                                    <p class="text-gray-900 font-medium">{{ $user->clientProfile->shop_name ?? 'Non renseigné' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Secteur d'activité</label>
                                    <p class="text-gray-900">{{ $user->clientProfile->business_sector ?? 'Non renseigné' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Matricule fiscal</label>
                                    <p class="text-gray-900 font-mono">{{ $user->clientProfile->fiscal_number ?? 'Non renseigné' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Document d'identité</label>
                                    @if($user->clientProfile->identity_document)
                                        <a href="{{ route('client.profile.download-identity') }}"
                                           class="inline-flex items-center text-purple-600 hover:text-purple-700">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Télécharger
                                        </a>
                                    @else
                                        <p class="text-gray-500">Non fourni</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-2 sm:py-3 sm:py-3 sm:py-2 sm:py-3">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2 sm:mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="text-gray-500 mb-2 sm:mb-3">Aucune information professionnelle renseignée</p>
                            <a href="{{ route('client.profile.edit') }}"
                               class="text-purple-600 hover:text-purple-700 font-medium">
                                Compléter mon profil →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="space-y-2 sm:space-y-3 lg:space-y-3 sm:space-y-2 sm:space-y-3">

            <!-- Statut et dates -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Informations du compte</h3>
                <div class="space-y-2 sm:space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Membre depuis</label>
                        <p class="text-gray-900">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Date inconnue' }}</p>
                    </div>
                    @if($user->verified_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Compte vérifié le</label>
                        <p class="text-gray-900">{{ $user->verified_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @endif
                    @if($user->last_login)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Dernière connexion</label>
                        <p class="text-gray-900">{{ $user->last_login->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Offre tarifaire -->
            @if($user->clientProfile && $user->clientProfile->hasValidPricing())
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Votre Offre Tarifaire</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Livraison réussie</span>
                        <span class="font-bold text-green-600">{{ $user->clientProfile->formatted_delivery_price }} DT</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Retour expéditeur</span>
                        <span class="font-bold text-orange-600">{{ $user->clientProfile->formatted_return_price }} DT</span>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Seul le service commercial peut modifier ces tarifs.
                    </p>
                </div>
            </div>
            @endif

            <!-- Progression profil -->
            @if($user->clientProfile)
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-2.5 sm:p-3 md:p-3 sm:p-2.5 sm:p-3">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Complétude du profil</h3>
                <div class="mb-2 sm:mb-3">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">Progression</span>
                        <span class="font-medium">{{ $user->clientProfile->getCompletionPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full transition-all duration-300"
                             style="width: {{ $user->clientProfile->getCompletionPercentage() }}%"></div>
                    </div>
                </div>
                @if($user->clientProfile->getCompletionPercentage() < 100)
                <p class="text-sm text-gray-600 mb-3">
                    Complétez votre profil pour optimiser l'utilisation de la plateforme.
                </p>
                <a href="{{ route('client.profile.edit') }}"
                   class="w-full bg-purple-600 text-white text-center py-2.5 px-4 rounded-lg hover:bg-purple-700 transition-colors inline-block text-sm md:text-base">
                    Compléter
                </a>
                @else
                <div class="flex items-center text-green-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Profil complet</span>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
@endsection