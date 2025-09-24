@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('supervisor.users.show', $user) }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Modifier l'Utilisateur</h1>
                <p class="text-gray-600">{{ $user->name }} ({{ $user->role }})</p>
                <div class="flex items-center mt-1 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        @if($user->status === 'ACTIVE') bg-green-100 text-green-800
                        @elseif($user->status === 'PENDING') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ $user->status }}
                    </span>
                    <span class="mx-2 text-gray-400">•</span>
                    <span class="text-gray-600">Créé le {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">

        <!-- Formulaire principal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Modifier les Informations</h2>
                <p class="text-sm text-gray-600">Mettez à jour les informations de l'utilisateur</p>
            </div>

            <form method="POST" action="{{ route('supervisor.users.update', $user) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Informations personnelles -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations Personnelles</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom Complet *
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($user->email_verified_at)
                                <div class="mt-2 flex items-center text-sm text-green-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Email vérifié le {{ $user->email_verified_at->format('d/m/Y') }}
                                </div>
                            @else
                                <div class="mt-2 flex items-center text-sm text-amber-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Email non vérifié
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Téléphone *
                            </label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required
                                   placeholder="+216 XX XXX XXX"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Membre Depuis
                            </label>
                            <div class="w-full px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-300 rounded-lg text-gray-700 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $user->created_at->format('d/m/Y à H:i') }}
                                <span class="ml-2 text-sm text-gray-500">({{ $user->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuration du compte -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration du Compte</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                Rôle *
                            </label>
                            <select name="role" id="role" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('role') border-red-500 @enderror">
                                <option value="CLIENT" {{ old('role', $user->role) === 'CLIENT' ? 'selected' : '' }}>
                                    👤 Client
                                </option>
                                <option value="DELIVERER" {{ old('role', $user->role) === 'DELIVERER' ? 'selected' : '' }}>
                                    🚚 Livreur
                                </option>
                                <option value="COMMERCIAL" {{ old('role', $user->role) === 'COMMERCIAL' ? 'selected' : '' }}>
                                    💼 Commercial
                                </option>
                                <option value="SUPERVISOR" {{ old('role', $user->role) === 'SUPERVISOR' ? 'selected' : '' }}>
                                    👨‍💼 Superviseur
                                </option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($user->role !== old('role', $user->role))
                                <div class="mt-2 flex items-center text-sm text-amber-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Attention: Changer le rôle peut affecter les permissions
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Statut *
                            </label>
                            <select name="status" id="status" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('status') border-red-500 @enderror">
                                <option value="ACTIVE" {{ old('status', $user->status) === 'ACTIVE' ? 'selected' : '' }}>
                                    ✅ Actif
                                </option>
                                <option value="PENDING" {{ old('status', $user->status) === 'PENDING' ? 'selected' : '' }}>
                                    ⏳ En attente
                                </option>
                                <option value="SUSPENDED" {{ old('status', $user->status) === 'SUSPENDED' ? 'selected' : '' }}>
                                    ❌ Suspendu
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(isset($delegations) && $delegations->count() > 0)
                        <div class="md:col-span-2">
                            <label for="delegation_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Délégation
                            </label>
                            <select name="delegation_id" id="delegation_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('delegation_id') border-red-500 @enderror">
                                <option value="">Aucune délégation</option>
                                @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}" {{ old('delegation_id', $user->delegation_id) == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('delegation_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Requis pour certains rôles</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informations wallet (si applicable) -->
                @if(in_array($user->role, ['CLIENT', 'DELIVERER']) && $user->wallet)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations Portefeuille</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-green-700">Balance</label>
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-green-900">
                                {{ number_format($user->wallet->balance, 3) }} DT
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-yellow-700">En Attente</label>
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-yellow-900">
                                {{ number_format($user->wallet->pending_amount, 3) }} DT
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-red-700">Gelé</label>
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                </svg>
                            </div>
                            <div class="text-2xl font-bold text-red-900">
                                {{ number_format($user->wallet->frozen_amount, 3) }} DT
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Statistiques utilisateur -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Statistiques de l'Utilisateur
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if($user->role === 'CLIENT')
                        <div class="bg-white rounded-lg p-4 text-center border border-blue-200">
                            <div class="text-2xl font-bold text-blue-600">{{ $user->packages()->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Colis créés</div>
                        </div>
                        @elseif($user->role === 'DELIVERER')
                        <div class="bg-white rounded-lg p-4 text-center border border-green-200">
                            <div class="text-2xl font-bold text-green-600">{{ $user->assignedPackages()->where('status', 'DELIVERED')->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Colis livrés</div>
                        </div>
                        @endif

                        <div class="bg-white rounded-lg p-4 text-center border border-red-200">
                            <div class="text-2xl font-bold text-red-600">{{ $user->complaints()->count() }}</div>
                            <div class="text-sm text-gray-600 mt-1">Réclamations</div>
                        </div>

                        <div class="bg-white rounded-lg p-4 text-center border border-purple-200">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $user->created_at->diffInDays(now()) }}
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Jours d'ancienneté</div>
                        </div>

                        <div class="bg-white rounded-lg p-4 text-center border border-orange-200">
                            <div class="text-2xl font-bold text-orange-600">
                                {{ $user->updated_at->format('d/m/Y') === now()->format('d/m/Y') ? 'Aujourd\'hui' : $user->updated_at->diffForHumans() }}
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Dernière modification</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('supervisor.users.show', $user) }}"
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Annuler
                        </a>
                        <a href="{{ route('supervisor.users.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 underline">
                            Retour à la liste
                        </a>
                    </div>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all flex items-center shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mettre à Jour
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide contextuelle -->
        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-lg font-medium text-blue-900 mb-3">Notes de modification</p>
                    <div class="space-y-2 text-sm text-blue-800">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Les wallets sont créés automatiquement pour les clients et livreurs</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Changer le rôle peut affecter l'accès aux fonctionnalités</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293a1 1 0 00-1.414-1.414L9 11.586 7.707 10.293a1 1 0 00-1.414 1.414L9 14.414l3.293-3.707a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Suspendre un utilisateur bloque son accès immédiatement</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Les modifications sont enregistrées dans l'historique</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone');
    const roleSelect = document.getElementById('role');
    const delegationSelect = document.getElementById('delegation_id');

    // Format du téléphone
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0 && !value.startsWith('216')) {
            if (value.length === 8) {
                value = '216' + value;
            }
        }
        if (value.length > 0) {
            value = '+' + value;
        }
        this.value = value;
    });

    // Validation de la délégation selon le rôle
    function validateDelegation() {
        const role = roleSelect.value;
        const delegation = delegationSelect.value;
        const delegationContainer = delegationSelect.closest('div');

        // Supprimer les messages d'erreur précédents
        const existingError = delegationContainer.querySelector('.delegation-error');
        if (existingError) {
            existingError.remove();
        }

        if ((role === 'CLIENT' || role === 'DELIVERER') && !delegation) {
            const error = document.createElement('p');
            error.className = 'mt-1 text-sm text-red-600 delegation-error flex items-center';
            error.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Une délégation est requise pour ce rôle
            `;
            delegationContainer.appendChild(error);
            return false;
        }
        return true;
    }

    if (roleSelect && delegationSelect) {
        roleSelect.addEventListener('change', validateDelegation);
        delegationSelect.addEventListener('change', validateDelegation);
    }

    // Animation de soumission
    form.addEventListener('submit', function(e) {
        if (delegationSelect && !validateDelegation()) {
            e.preventDefault();
            return false;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Mise à jour...';

        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Mettre à Jour';
        }, 2000);
    });
});
</script>
@endsection