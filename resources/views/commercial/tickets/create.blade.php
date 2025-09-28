@extends('layouts.commercial')

@section('title', 'Créer un Ticket')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Créer un Ticket</h1>
                    <p class="mt-2 text-gray-600">Créer un nouveau ticket de support pour un client</p>
                </div>
                <a href="{{ route('commercial.tickets.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Retour à la liste
                </a>
            </div>
        </div>

        <!-- Formulaire de création -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="{{ route('commercial.tickets.store') }}" class="space-y-6">
                @csrf

                <!-- Client Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Client *
                        </label>
                        <select name="client_id" id="client_id" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner un client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} - {{ $client->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type de ticket *
                        </label>
                        <select name="type" id="type" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir un type</option>
                            <option value="COMPLAINT" {{ old('type') == 'COMPLAINT' ? 'selected' : '' }}>Réclamation</option>
                            <option value="QUESTION" {{ old('type') == 'QUESTION' ? 'selected' : '' }}>Question</option>
                            <option value="SUPPORT" {{ old('type') == 'SUPPORT' ? 'selected' : '' }}>Support technique</option>
                            <option value="OTHER" {{ old('type') == 'OTHER' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Priority and Package -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priorité *
                        </label>
                        <select name="priority" id="priority" required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="NORMAL" {{ old('priority') == 'NORMAL' ? 'selected' : '' }}>Normale</option>
                            <option value="HIGH" {{ old('priority') == 'HIGH' ? 'selected' : '' }}>Élevée</option>
                            <option value="URGENT" {{ old('priority') == 'URGENT' ? 'selected' : '' }}>Urgente</option>
                            <option value="LOW" {{ old('priority') == 'LOW' ? 'selected' : '' }}>Faible</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="package_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Code colis (optionnel)
                        </label>
                        <input type="text" name="package_code" id="package_code"
                               value="{{ old('package_code') }}"
                               placeholder="PKG_XXXXXXXX"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        @error('package_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Si le ticket concerne un colis spécifique</p>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        Sujet *
                    </label>
                    <input type="text" name="subject" id="subject" required
                           value="{{ old('subject') }}"
                           placeholder="Résumé du problème..."
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description détaillée *
                    </label>
                    <textarea name="description" id="description" rows="6" required
                              placeholder="Décrivez le problème en détail..."
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assignment -->
                <div>
                    <label for="assigned_to_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Assigner à (optionnel)
                    </label>
                    <select name="assigned_to_id" id="assigned_to_id"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Auto-assignation</option>
                        @foreach($commercials as $commercial)
                            <option value="{{ $commercial->id }}" {{ old('assigned_to_id') == $commercial->id ? 'selected' : '' }}>
                                {{ $commercial->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Laisser vide pour s'auto-assigner le ticket</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <span class="text-red-500">*</span> Champs obligatoires
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('commercial.tickets.index') }}"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                            Annuler
                        </a>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Créer le ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Auto-search for package -->
        @if(request('package_code'))
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Colis pré-sélectionné
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            Le code colis {{ request('package_code') }} a été automatiquement rempli.
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Auto-complétion des champs basée sur le client sélectionné
document.getElementById('client_id').addEventListener('change', function() {
    const clientId = this.value;
    if (clientId) {
        // Vous pouvez ajouter ici une requête AJAX pour récupérer les infos du client
        // et pré-remplir certains champs
    }
});

// Auto-complétion du package
document.getElementById('package_code').addEventListener('blur', function() {
    const packageCode = this.value;
    if (packageCode && packageCode.length > 3) {
        // Vous pouvez ajouter ici une requête AJAX pour vérifier le colis
        // et pré-remplir le client si trouvé
    }
});
</script>
@endsection