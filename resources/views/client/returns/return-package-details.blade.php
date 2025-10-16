@extends('layouts.client')

@section('title', 'Détails du Colis Retour')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-3 sm:mb-2 sm:mb-3 text-sm">
        <ol class="flex items-center space-x-2 text-gray-600">
            <li><a href="{{ route('client.dashboard') }}" class="hover:text-blue-600">Tableau de bord</a></li>
            <li>/</li>
            <li><a href="{{ route('client.packages.index') }}" class="hover:text-blue-600">Mes colis</a></li>
            <li>/</li>
            <li><a href="{{ route('client.packages.show', $originalPackage->id) }}" class="hover:text-blue-600">{{ $originalPackage->package_code }}</a></li>
            <li>/</li>
            <li class="text-gray-900 font-medium">Colis Retour</li>
        </ol>
    </nav>

    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900">📦 Colis Retour</h1>
                <p class="text-gray-600 mt-1">Détails du retour pour le colis {{ $originalPackage->package_code }}</p>
            </div>
            <div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                    RETOUR
                </span>
            </div>
        </div>
    </div>

    <!-- Lien vers le colis original -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Ce colis retour est associé au colis original 
                    <a href="{{ route('client.packages.show', $originalPackage->id) }}" 
                       class="font-medium underline hover:text-blue-600">
                        {{ $originalPackage->package_code }}
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-2 sm:gap-3">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-2 sm:space-y-3">
            <!-- Informations du colis retour -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Informations du Colis Retour</h2>
                <dl class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Code du retour</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">
                            {{ $returnPackage->return_code ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $returnPackage->created_at ? $returnPackage->created_at->format('d/m/Y à H:i') : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $returnPackage->status ?? 'EN_COURS' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Raison du retour</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $returnPackage->return_reason ?? $originalPackage->return_reason ?? 'Non spécifiée' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informations du colis original -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Colis Original</h2>
                <dl class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Code colis</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">
                            {{ $originalPackage->package_code }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Destinataire original</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $originalPackage->recipient_data['name'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $originalPackage->recipient_data['phone'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Montant COD</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ number_format($originalPackage->cod_amount, 3) }} DT
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Informations de retour -->
            @if($returnPackage->sender_data)
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Adresse de Retour</h2>
                <dl class="grid grid-cols-1 gap-2 sm:gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $returnPackage->sender_data['name'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $returnPackage->sender_data['phone'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $returnPackage->sender_data['address'] ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-3 sm:space-y-2 sm:space-y-3">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('client.packages.show', $originalPackage->id) }}" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                        Voir le Colis Original
                    </a>
                    
                    @if($originalPackage->status === 'RETURNED')
                        <a href="{{ route('client.returns.show', $originalPackage->id) }}" 
                           class="block w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                            Gérer le Retour
                        </a>
                    @endif
                    
                    <a href="{{ route('client.packages.index') }}" 
                       class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                        ← Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="bg-gray-50 rounded-lg p-2.5 sm:p-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">ℹ️ Information</h3>
                <p class="text-xs text-gray-600">
                    Ce colis retour a été créé automatiquement pour gérer le retour du colis original vers votre adresse.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
