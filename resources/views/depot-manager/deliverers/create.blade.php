@extends('layouts.depot-manager')

@section('title', 'Ajouter un Livreur')
@section('page-title', 'Ajouter un Livreur')
@section('page-description', 'Créer un nouveau compte livreur dans vos gouvernorats')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec retour -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('depot-manager.deliverers.index') }}"
           class="inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-orange-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ajouter un Livreur</h1>
            <p class="text-gray-600">Nouveau compte livreur pour vos gouvernorats</p>
        </div>
    </div>

    <!-- Formulaire -->
    <form method="POST" action="{{ route('depot-manager.deliverers.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations personnelles</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom complet -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
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
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('phone') border-red-500 @enderror"
                           placeholder="+216 XX XXX XXX">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror"
                           placeholder="Minimum 8 caractères">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                            <option value="{{ $gov }}" {{ old('assigned_delegation') == $gov ? 'selected' : '' }}>
                                {{ $gov }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_delegation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Le livreur sera affecté à ce gouvernorat</p>
                </div>

                <!-- Type de livreur -->
                <div>
                    <label for="deliverer_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type de livreur
                    </label>
                    <select name="deliverer_type" id="deliverer_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="NORMAL" {{ old('deliverer_type', 'NORMAL') == 'NORMAL' ? 'selected' : '' }}>Normal</option>
                        <option value="TRANSIT" {{ old('deliverer_type') == 'TRANSIT' ? 'selected' : '' }}>Transit</option>
                        <option value="EXPRESS" {{ old('deliverer_type') == 'EXPRESS' ? 'selected' : '' }}>Express</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Type de service que propose ce livreur</p>
                </div>
            </div>

            <!-- Adresse -->
            <div class="mt-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse complète
                </label>
                <textarea name="address" id="address" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('address') border-red-500 @enderror"
                          placeholder="Adresse complète du livreur">{{ old('address') }}</textarea>
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
                        <option value="ACTIVE" {{ old('account_status', 'ACTIVE') == 'ACTIVE' ? 'selected' : '' }}>Actif</option>
                        <option value="PENDING" {{ old('account_status') == 'PENDING' ? 'selected' : '' }}>En attente</option>
                        <option value="SUSPENDED" {{ old('account_status') == 'SUSPENDED' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>

                <!-- Vérification -->
                <div class="flex items-center">
                    <input type="checkbox" name="verified" id="verified" value="1" {{ old('verified') ? 'checked' : '' }}
                           class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                    <label for="verified" class="ml-2 text-sm text-gray-700">
                        Compte vérifié immédiatement
                    </label>
                </div>
            </div>

            <!-- Notes internes -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes internes
                </label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                          placeholder="Notes internes sur ce livreur (visible uniquement par les administrateurs)">{{ old('notes') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Ces notes ne sont pas visibles par le livreur</p>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('depot-manager.deliverers.index') }}"
               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                Créer le Livreur
            </button>
        </div>
    </form>

</div>
@endsection