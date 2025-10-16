@extends('layouts.client')

@section('title', 'Détails du Retour - ' . $package->package_code)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-3 sm:mb-2 sm:mb-3 text-sm">
        <ol class="flex items-center space-x-2 text-gray-600">
            <li><a href="{{ route('client.dashboard') }}" class="hover:text-blue-600">Tableau de bord</a></li>
            <li>/</li>
            <li><a href="{{ route('client.returns.pending') }}" class="hover:text-blue-600">Retours à traiter</a></li>
            <li>/</li>
            <li class="text-gray-900 font-medium">{{ $package->package_code }}</li>
        </ol>
    </nav>

    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900">📦 {{ $package->package_code }}</h1>
                <p class="text-gray-600 mt-1">Détails du colis retourné</p>
            </div>
            <div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                    RETOURNÉ
                </span>
            </div>
        </div>
    </div>

    <!-- Alerte temps restant -->
    @if($package->status === 'RETURNED' && isset($package->hours_remaining))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Temps restant :</strong> {{ round($package->hours_remaining) }} heures
                        <br>
                        <span class="text-xs">Le retour sera automatiquement confirmé le {{ $package->auto_confirm_at->format('d/m/Y à H:i') }}</span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-2 sm:gap-3">
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-2 sm:space-y-3">
            <!-- Informations du retour -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Informations du Retour</h2>
                <dl class="grid grid-cols-1 gap-2 sm:gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date de retour</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->returned_to_client_at ? $package->returned_to_client_at->format('d/m/Y à H:i') : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Raison du retour</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->return_reason ?? 'Non spécifiée' }}
                        </dd>
                    </div>
                    @if($package->auto_return_reason)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Raison automatique</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $package->auto_return_reason }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Informations du colis -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Informations du Colis</h2>
                <dl class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Destinataire</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->recipient_data['name'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->recipient_data['phone'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->recipient_data['address'] ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Délégation</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $package->delegationTo->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Montant COD</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ number_format($package->cod_amount, 3) }} DT
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Historique -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Historique du Statut</h2>
                <div class="flow-root">
                    <ul class="-mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3">
                        @foreach($package->statusHistory as $index => $history)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">{{ $history->status }}</p>
                                                @if($history->notes)
                                                    <p class="text-sm text-gray-500">{{ $history->notes }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $history->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-3 sm:space-y-2 sm:space-y-3">
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Actions</h2>
                <div class="space-y-3">
                    @if($package->status === 'RETURNED')
                        <!-- Bouton Valider la Réception -->
                        <form action="{{ route('client.returns.validate-reception', $package->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Confirmez-vous avoir bien reçu ce colis retourné ?');">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                ✅ Valider la Réception
                            </button>
                        </form>
                        <p class="text-xs text-gray-500 text-center">
                            Confirmez que vous avez bien reçu le colis retourné
                        </p>
                        
                        <div class="border-t border-gray-200 my-4"></div>
                        
                        <!-- Bouton Réclamer un Problème -->
                        <form action="{{ route('client.returns.report-problem', $package->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir signaler un problème avec ce colis ? Un ticket de réclamation sera créé et notre équipe vous contactera.');">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                ⚠️ Réclamer un Problème
                            </button>
                        </form>
                        <p class="text-xs text-gray-500 text-center">
                            Signalez si le colis n'a pas été retourné ou s'il y a un problème
                        </p>
                    @endif
                    
                    <a href="{{ route('client.returns.pending') }}" 
                       class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                        ← Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Réclamations associées -->
            @if($package->complaints->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-2.5 sm:p-3">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-3">Réclamations</h2>
                    <div class="space-y-3">
                        @foreach($package->complaints as $complaint)
                            <div class="border-l-4 border-blue-500 pl-3 py-2">
                                <div class="text-sm font-medium text-gray-900">{{ $complaint->type }}</div>
                                <div class="text-xs text-gray-500">{{ $complaint->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs">
                                    <span class="px-2 py-1 rounded-full {{ $complaint->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $complaint->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
