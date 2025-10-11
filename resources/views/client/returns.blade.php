@extends('layouts.client')

@section('title', 'Mes Retours')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900">📦 Mes Colis Retournés</h1>
            <p class="text-gray-600 mt-1">Gérez vos colis retournés et confirmez leur réception</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">En Attente de Confirmation</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $packagesAwaitingConfirmation->count() }}</p>
                    </div>
                    <div class="text-3xl">⏳</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Retours Confirmés</p>
                        <p class="text-2xl font-bold text-green-600">{{ $confirmedReturns->count() }}</p>
                    </div>
                    <div class="text-3xl">✅</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Problèmes Signalés</p>
                        <p class="text-2xl font-bold text-red-600">{{ $issueReturns->count() }}</p>
                    </div>
                    <div class="text-3xl">⚠️</div>
                </div>
            </div>
        </div>

        <!-- Colis en attente de confirmation (IMPORTANT) -->
        @if($packagesAwaitingConfirmation->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 border-2 border-orange-400">
            <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    ⏳ Colis à Confirmer ({{ $packagesAwaitingConfirmation->count() }})
                </h2>
                <p class="text-orange-100 text-sm mt-1">Vous avez 48h pour confirmer ou signaler un problème</p>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    @foreach($packagesAwaitingConfirmation as $package)
                    <div class="bg-orange-50 border-2 border-orange-200 rounded-xl p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="font-mono font-bold text-orange-900 text-lg">{{ $package->package_code }}</span>
                                    <span class="px-2 py-1 bg-orange-200 text-orange-800 text-xs font-semibold rounded-full">
                                        {{ $package->status }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <p><strong>Destinataire:</strong> {{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                                    <p><strong>COD:</strong> {{ number_format($package->cod_amount, 2) }} TND</p>
                                    @if($package->return_reason)
                                    <p class="mt-2"><strong>Raison du retour:</strong> {{ $package->return_reason }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-orange-700">
                                        Retourné {{ $package->returned_to_client_at->diffForHumans() }}
                                        @if($package->returned_to_client_at->addHours(48) > now())
                                            - <strong>{{ $package->returned_to_client_at->addHours(48)->diffForHumans() }} restants</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-4">
                                <form action="{{ route('client.returns.confirm', $package) }}" method="POST"
                                      onsubmit="return confirm('Confirmer la réception de ce colis ?');">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm whitespace-nowrap">
                                        ✅ Confirmer Réception
                                    </button>
                                </form>
                                <button onclick="openIssueModal({{ $package->id }}, '{{ $package->package_code }}')"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold text-sm whitespace-nowrap">
                                    ⚠️ Signaler Problème
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Retours confirmés -->
        @if($confirmedReturns->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-green-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">✅ Retours Confirmés ({{ $confirmedReturns->count() }})</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($confirmedReturns as $package)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-green-900">{{ $package->package_code }}</span>
                            <p class="text-sm text-gray-600">{{ $package->recipient_data['name'] ?? 'N/A' }} - {{ number_format($package->cod_amount, 2) }} TND</p>
                            <p class="text-xs text-gray-500 mt-1">Confirmé {{ $package->updated_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-3xl">✅</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Problèmes signalés -->
        @if($issueReturns->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 border-2 border-red-400">
            <div class="bg-red-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">⚠️ Problèmes Signalés ({{ $issueReturns->count() }})</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($issueReturns as $package)
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <span class="font-mono font-bold text-red-900 text-lg">{{ $package->package_code }}</span>
                                <p class="text-sm text-gray-600 mt-1">{{ $package->recipient_data['name'] ?? 'N/A' }}</p>
                                @if($package->complaints->count() > 0)
                                <div class="mt-2 bg-red-100 border border-red-300 rounded p-2">
                                    <p class="text-sm font-semibold text-red-900">Problème signalé:</p>
                                    <p class="text-sm text-red-800">{{ $package->complaints->last()->description }}</p>
                                    <p class="text-xs text-red-600 mt-1">Statut: {{ $package->complaints->last()->status }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($packagesAwaitingConfirmation->count() === 0 && $confirmedReturns->count() === 0 && $issueReturns->count() === 0)
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Aucun colis retourné</h3>
            <p class="text-gray-600">Vous n'avez aucun colis en cours de retour</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal Signaler Problème -->
<div id="issue-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="bg-red-600 px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">⚠️ Signaler un Problème</h3>
            <p class="text-red-100 text-sm mt-1">Décrivez le problème rencontré</p>
        </div>

        <form id="issue-form" method="POST">
            @csrf
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Colis concerné:</p>
                    <p id="modal-package-code" class="font-mono font-bold text-lg text-gray-900"></p>
                </div>

                <div class="mb-6">
                    <label for="issue_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description du problème * <span class="text-red-500">(max 1000 caractères)</span>
                    </label>
                    <textarea name="issue_description" id="issue_description" rows="6" required maxlength="1000"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                        placeholder="Exemple: Colis endommagé, contenu manquant, mauvais article..."></textarea>
                    <p class="text-xs text-gray-500 mt-1" id="char-counter">0/1000 caractères</p>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="closeIssueModal()"
                        class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
                        Signaler le Problème
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openIssueModal(packageId, packageCode) {
    const modal = document.getElementById('issue-modal');
    const form = document.getElementById('issue-form');
    const codeDisplay = document.getElementById('modal-package-code');

    form.action = `/client/returns/${packageId}/report-issue`;
    codeDisplay.textContent = packageCode;
    modal.classList.remove('hidden');
}

function closeIssueModal() {
    document.getElementById('issue-modal').classList.add('hidden');
    document.getElementById('issue_description').value = '';
}

// Compteur de caractères
document.getElementById('issue_description').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('char-counter').textContent = `${count}/1000 caractères`;
});
</script>
@endsection
