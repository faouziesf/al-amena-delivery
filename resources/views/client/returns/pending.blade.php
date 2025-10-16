@extends('layouts.client')

@section('title', 'Retours à Traiter')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- En-tête -->
    <div class="mb-3 sm:mb-2 sm:mb-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl sm:text-lg sm:text-xl font-bold text-gray-900">📦 Retours à Traiter</h1>
                <p class="text-gray-600 mt-1">Gérez vos colis retournés</p>
            </div>
            <div class="text-right">
                <div class="text-xl sm:text-lg sm:text-xl font-bold text-orange-600">{{ $returnedPackages->total() }}</div>
                <div class="text-sm text-gray-600">Retour(s) en attente</div>
            </div>
        </div>
    </div>

    <!-- Message d'information -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Information importante :</strong> Si vous ne signalez aucun problème dans les <strong>48 heures</strong>, 
                    le retour sera automatiquement confirmé et le colis sera considéré comme reçu.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-2.5 sm:p-3 mb-3 sm:mb-2 sm:mb-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($returnedPackages->isEmpty())
        <!-- Aucun retour -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="text-6xl mb-2 sm:mb-3">✅</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun retour en attente</h3>
            <p class="text-gray-600">Vous n'avez actuellement aucun colis retourné à traiter.</p>
            <a href="{{ route('client.packages.index') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-700 font-medium">
                → Voir tous mes colis
            </a>
        </div>
    @else
        <!-- Liste des retours -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code Colis
                            </th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Destinataire
                            </th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date de Retour
                            </th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Raison
                            </th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Temps Restant
                            </th>
                            <th class="px-3 sm:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($returnedPackages as $package)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 sm:py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $package->package_code }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 sm:py-3">
                                    <div class="text-sm text-gray-900">
                                        {{ $package->recipient_data['name'] ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $package->recipient_data['phone'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 sm:py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $package->returned_to_client_at ? $package->returned_to_client_at->format('d/m/Y') : 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $package->returned_to_client_at ? $package->returned_to_client_at->format('H:i') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 sm:py-3">
                                    <div class="text-sm text-gray-900">
                                        {{ $package->return_reason ?? 'Non spécifiée' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 sm:py-3 whitespace-nowrap">
                                    @if(isset($package->hours_remaining))
                                        @if($package->hours_remaining > 24)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ round($package->hours_remaining) }}h restantes
                                            </span>
                                        @elseif($package->hours_remaining > 6)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ round($package->hours_remaining) }}h restantes
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ round($package->hours_remaining) }}h restantes
                                            </span>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">
                                            Confirmation auto: {{ $package->auto_confirm_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 sm:py-3 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-col space-y-2">
                                        <!-- Bouton Valider la Réception -->
                                        <form action="{{ route('client.returns.validate-reception', $package->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Confirmez-vous avoir bien reçu ce colis retourné ?');">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Valider la Réception
                                            </button>
                                        </form>
                                        
                                        <!-- Bouton Réclamer un Problème -->
                                        <form action="{{ route('client.returns.report-problem', $package->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir signaler un problème avec ce colis ? Un ticket de réclamation sera créé.');">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                Réclamer un Problème
                                            </button>
                                        </form>
                                        
                                        <!-- Lien Détails -->
                                        <a href="{{ route('client.returns.show', $package->id) }}" 
                                           class="text-center text-blue-600 hover:text-blue-900 text-sm font-medium">
                                            Voir les détails →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($returnedPackages->hasPages())
                <div class="px-4 py-2 sm:py-3 border-t border-gray-200">
                    {{ $returnedPackages->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
