@extends('layouts.depot-manager')

@section('title', 'Modifier Livreur - ' . $deliverer->name)
@section('page-title', 'Modifier le Livreur')
@section('page-description', 'Modification des informations et paramètres')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('depot-manager.deliverers.show', $deliverer) }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier {{ $deliverer->name }}</h1>
            <p class="text-gray-600">Livreur ID: {{ $deliverer->id }} • {{ $deliverer->assigned_delegation }}</p>
        </div>
    </div>

    <!-- Formulaire -->
    <form method="POST" action="{{ route('depot-manager.deliverers.update', $deliverer) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations personnelles</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom complet -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $deliverer->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('name') border-red-500 @enderror"
                           placeholder="Nom et prénom du livreur">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $deliverer->email) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('email') border-red-500 @enderror"
                           placeholder="email@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $deliverer->phone) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('phone') border-red-500 @enderror"
                           placeholder="+216 XX XXX XXX">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau mot de passe
                    </label>
                    <input type="password" name="password" id="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror"
                           placeholder="Laisser vide pour ne pas changer">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Laisser vide pour conserver le mot de passe actuel</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Affectation géographique</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Gouvernorat -->
                <div>
                    <label for="assigned_delegation" class="block text-sm font-medium text-gray-700 mb-2">
                        Gouvernorat d'affectation <span class="text-red-500">*</span>
                    </label>
                    <select name="assigned_delegation" id="assigned_delegation" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('assigned_delegation') border-red-500 @enderror">
                        <option value="">Sélectionner un gouvernorat</option>
                        @foreach(auth()->user()->assigned_gouvernorats_array as $gov)
                            <option value="{{ $gov }}" {{ old('assigned_delegation', $deliverer->assigned_delegation) == $gov ? 'selected' : '' }}>
                                {{ $gov }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_delegation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type de livreur -->
                <div>
                    <label for="deliverer_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type de livreur
                    </label>
                    <select name="deliverer_type" id="deliverer_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="NORMAL" {{ old('deliverer_type', $deliverer->deliverer_type ?? 'NORMAL') == 'NORMAL' ? 'selected' : '' }}>Normal</option>
                        <option value="TRANSIT" {{ old('deliverer_type', $deliverer->deliverer_type) == 'TRANSIT' ? 'selected' : '' }}>Transit</option>
                        <option value="EXPRESS" {{ old('deliverer_type', $deliverer->deliverer_type) == 'EXPRESS' ? 'selected' : '' }}>Express</option>
                    </select>
                </div>
            </div>

            <!-- Adresse -->
            <div class="mt-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse complète
                </label>
                <textarea name="address" id="address" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('address') border-red-500 @enderror"
                          placeholder="Adresse complète du livreur">{{ old('address', $deliverer->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Paramètres du compte</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Statut du compte -->
                <div>
                    <label for="account_status" class="block text-sm font-medium text-gray-700 mb-2">
                        Statut du compte
                    </label>
                    <select name="account_status" id="account_status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="ACTIVE" {{ old('account_status', $deliverer->account_status) == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                        <option value="PENDING" {{ old('account_status', $deliverer->account_status) == 'PENDING' ? 'selected' : '' }}>En attente</option>
                        <option value="SUSPENDED" {{ old('account_status', $deliverer->account_status) == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>

                <!-- Vérification -->
                <div class="flex items-center">
                    <input type="checkbox" name="verified" id="verified" value="1"
                           {{ old('verified', $deliverer->verified_at ? true : false) ? 'checked' : '' }}
                           class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                    <label for="verified" class="ml-2 text-sm text-gray-700">
                        Compte vérifié
                    </label>
                </div>
            </div>

            <!-- Informations du compte -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date de création
                    </label>
                    <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">
                        {{ $deliverer->created_at ? $deliverer->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dernière connexion
                    </label>
                    <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">
                        {{ $deliverer->last_activity ? $deliverer->last_activity->diffForHumans() : 'Jamais connecté' }}
                    </p>
                </div>
            </div>

            <!-- Notes internes -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes internes
                </label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                          placeholder="Notes internes sur ce livreur (visible uniquement par les administrateurs)">{{ old('notes', $deliverer->notes ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Ces notes ne sont pas visibles par le livreur</p>
            </div>
        </div>

        <!-- Actions avancées -->
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-6">Actions avancées</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button type="button" onclick="reassignAllPackages()"
                        class="flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Réassigner tous les colis
                </button>

                <button type="button" onclick="resetPassword()"
                        class="flex items-center justify-center px-4 py-3 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Générer nouveau mot de passe
                </button>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('depot-manager.deliverers.show', $deliverer) }}"
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                Sauvegarder les modifications
            </button>
        </div>
    </form>

</div>

<script>
function reassignAllPackages() {
    if (confirm('Voulez-vous réassigner tous les colis de ce livreur à d\'autres livreurs ?')) {
        fetch(`/depot-manager/deliverers/{{ $deliverer->id }}/reassign-packages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`${data.reassigned_count} colis ont été réassignés avec succès !`);
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la réassignation');
        });
    }
}

function resetPassword() {
    if (confirm('Voulez-vous générer un nouveau mot de passe pour ce livreur ?')) {
        const newPassword = Math.random().toString(36).slice(-8);
        document.getElementById('password').value = newPassword;
        alert(`Nouveau mot de passe généré : ${newPassword}\nN'oubliez pas de le communiquer au livreur !`);
    }
}
</script>

@endsection