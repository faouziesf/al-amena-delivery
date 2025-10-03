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

                <!-- Type de livreur (lecture seule) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type de livreur
                    </label>
                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-600">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $deliverer->deliverer_type === 'DELEGATION' ? 'Délégation fixe' : $deliverer->deliverer_type }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Le type de livreur ne peut pas être modifié par le chef dépôt</p>
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
                        Statut du compte <span class="text-red-500">*</span>
                    </label>
                    <select name="account_status" id="account_status" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('account_status') border-red-500 @enderror">
                        <option value="ACTIVE" {{ old('account_status', $deliverer->account_status) == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                        <option value="SUSPENDED" {{ old('account_status', $deliverer->account_status) == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                    @error('account_status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informations de création -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Date de création
                    </label>
                    <p class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">
                        {{ $deliverer->created_at ? $deliverer->created_at->format('d/m/Y H:i') : 'N/A' }}
                    </p>
                </div>
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


@endsection