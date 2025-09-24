@extends('layouts.supervisor')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('supervisor.users.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Créer un Nouvel Utilisateur</h1>
                <p class="text-gray-600">Ajouter un nouvel utilisateur au système</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Carte principale -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Informations Utilisateur</h2>
                <p class="text-sm text-gray-600">Remplissez tous les champs requis</p>
            </div>

            <form method="POST" action="{{ route('supervisor.users.store') }}" class="p-6 space-y-6">
                @csrf

                <!-- Informations personnelles -->
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations Personnelles</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom Complet *
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Téléphone *
                            </label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                   placeholder="+216 XX XXX XXX"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Mot de Passe *
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required minlength="8"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('password') border-red-500 @enderror">
                                <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Minimum 8 caractères</p>
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
                                <option value="">Sélectionner un rôle</option>
                                <option value="CLIENT" {{ old('role') == 'CLIENT' ? 'selected' : '' }}>
                                    👤 Client
                                </option>
                                <option value="DELIVERER" {{ old('role') == 'DELIVERER' ? 'selected' : '' }}>
                                    🚚 Livreur
                                </option>
                                <option value="COMMERCIAL" {{ old('role') == 'COMMERCIAL' ? 'selected' : '' }}>
                                    💼 Commercial
                                </option>
                                <option value="SUPERVISOR" {{ old('role') == 'SUPERVISOR' ? 'selected' : '' }}>
                                    👨‍💼 Superviseur
                                </option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Statut Initial *
                            </label>
                            <select name="status" id="status" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors @error('status') border-red-500 @enderror">
                                <option value="">Sélectionner un statut</option>
                                <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>
                                    ✅ Actif
                                </option>
                                <option value="PENDING" {{ old('status') == 'PENDING' ? 'selected' : '' }}>
                                    ⏳ En attente
                                </option>
                                <option value="SUSPENDED" {{ old('status') == 'SUSPENDED' ? 'selected' : '' }}>
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
                                <option value="{{ $delegation->id }}" {{ old('delegation_id') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('delegation_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Optionnel - Requis pour certains rôles</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informations sur les rôles -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Descriptions des Rôles</h4>
                    <div class="space-y-2 text-sm text-blue-800">
                        <div><strong>👤 Client:</strong> Peut créer des colis et gérer son portefeuille</div>
                        <div><strong>🚚 Livreur:</strong> Accepte et livre les colis, scanne les QR codes</div>
                        <div><strong>💼 Commercial:</strong> Traite les demandes de recharge et réclamations</div>
                        <div><strong>👨‍💼 Superviseur:</strong> Accès complet au système et surveillance</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('supervisor.users.index') }}"
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all flex items-center shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer l'Utilisateur
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
                    <p class="text-lg font-medium text-blue-900 mb-3">Notes importantes</p>
                    <div class="space-y-2 text-sm text-blue-800">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Les clients et livreurs auront automatiquement un portefeuille créé</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>L'utilisateur recevra ses identifiants par email</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Le mot de passe peut être réinitialisé plus tard</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Les utilisateurs "En attente" ne peuvent pas se connecter</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone');
    const roleSelect = document.getElementById('role');
    const statusSelect = document.getElementById('status');

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

    // Auto-sélection du statut actif
    roleSelect.addEventListener('change', function() {
        if (this.value && statusSelect.value === '') {
            statusSelect.value = 'ACTIVE';
        }
    });

    // Validation avant soumission
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Création...';

        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>Créer l\'Utilisateur';
        }, 2000);
    });
});
</script>
@endsection