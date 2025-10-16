@extends('layouts.client')

@section('title', 'Modifier un Compte Bancaire')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between flex-col sm:flex-row">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl font-bold text-gray-900">✏️ Modifier un Compte Bancaire</h1>
                <p class="text-gray-600 mt-2">Modifiez les informations de votre compte</p>
            </div>
            <a href="{{ route('client.bank-accounts.index') }}"
               class="inline-flex items-center px-4 sm:px-5 lg:px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-2xl transition-all transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-6 h-6 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Messages d'erreur -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl">
            <div class="flex flex-col sm:flex-row">
                <svg class="w-6 h-6 sm:w-5 sm:h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="font-medium">Erreurs de validation:</h4>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 transition-all duration-300 hover:-translate-y-1">
        <form action="{{ route('client.bank-accounts.update', $bankAccount) }}" method="POST" class="p-4 sm:p-5 lg:p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Section informations bancaires -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations du compte</h3>

                    <div class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-2 sm:gap-3 lg:gap-4 lg:gap-6">
                        <div class="md:col-span-2">
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom de la banque <span class="text-red-500">*</span>
                            </label>
                            <select id="bank_name" name="bank_name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 min-h-[44px]">
                                <option value="">Sélectionner une banque</option>

                                <!-- Banques commerciales publiques -->
                                <optgroup label="🏛️ Banques Publiques">
                                    <option value="Banque de Tunisie" {{ old('bank_name', $bankAccount->bank_name) == 'Banque de Tunisie' ? 'selected' : '' }}>Banque de Tunisie (BT)</option>
                                    <option value="Banque Nationale Agricole" {{ old('bank_name', $bankAccount->bank_name) == 'Banque Nationale Agricole' ? 'selected' : '' }}>Banque Nationale Agricole (BNA)</option>
                                    <option value="Société Tunisienne de Banque" {{ old('bank_name', $bankAccount->bank_name) == 'Société Tunisienne de Banque' ? 'selected' : '' }}>Société Tunisienne de Banque (STB)</option>
                                </optgroup>

                                <!-- Banques privées -->
                                <optgroup label="🏦 Banques Privées">
                                    <option value="Amen Bank" {{ old('bank_name', $bankAccount->bank_name) == 'Amen Bank' ? 'selected' : '' }}>Amen Bank</option>
                                    <option value="Arab Tunisian Bank" {{ old('bank_name', $bankAccount->bank_name) == 'Arab Tunisian Bank' ? 'selected' : '' }}>Arab Tunisian Bank (ATB)</option>
                                    <option value="Attijari Bank" {{ old('bank_name', $bankAccount->bank_name) == 'Attijari Bank' ? 'selected' : '' }}>Attijari Bank</option>
                                    <option value="Banque Internationale Arabe de Tunisie" {{ old('bank_name', $bankAccount->bank_name) == 'Banque Internationale Arabe de Tunisie' ? 'selected' : '' }}>Banque Internationale Arabe de Tunisie (BIAT)</option>
                                    <option value="Banque de l'Habitat" {{ old('bank_name', $bankAccount->bank_name) == 'Banque de l\'Habitat' ? 'selected' : '' }}>Banque de l'Habitat (BH Bank)</option>
                                    <option value="Union Bancaire pour le Commerce et l'Industrie" {{ old('bank_name', $bankAccount->bank_name) == 'Union Bancaire pour le Commerce et l\'Industrie' ? 'selected' : '' }}>Union Bancaire pour le Commerce et l'Industrie (UBCI)</option>
                                    <option value="Union Internationale de Banques" {{ old('bank_name', $bankAccount->bank_name) == 'Union Internationale de Banques' ? 'selected' : '' }}>Union Internationale de Banques (UIB)</option>
                                    <option value="Banque Zitouna" {{ old('bank_name', $bankAccount->bank_name) == 'Banque Zitouna' ? 'selected' : '' }}>Banque Zitouna</option>
                                </optgroup>

                                <!-- Banques étrangères -->
                                <optgroup label="🌍 Banques Étrangères">
                                    <option value="Qatar National Bank Tunisia" {{ old('bank_name', $bankAccount->bank_name) == 'Qatar National Bank Tunisia' ? 'selected' : '' }}>Qatar National Bank Tunisia (QNB)</option>
                                </optgroup>

                                <option value="Autre" {{ old('bank_name', $bankAccount->bank_name) == 'Autre' ? 'selected' : '' }}>🏦 Autre banque</option>
                            </select>
                            @error('bank_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom du titulaire <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="account_holder_name" name="account_holder_name" required
                                   value="{{ old('account_holder_name', $bankAccount->account_holder_name) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Nom complet du titulaire du compte">
                            @error('account_holder_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">
                                IBAN <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="iban" name="iban" required
                                   value="{{ old('iban', $bankAccount->iban) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="TN5901000123456789012345"
                                   maxlength="24">
                            <p class="mt-1 text-sm sm:text-xs text-gray-500">
                                Format: TN suivi de 22 chiffres
                            </p>
                            @error('iban')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Options</h3>

                    <div class="flex items-center flex-col sm:flex-row">
                        <input type="checkbox" id="is_default" name="is_default" value="1"
                               {{ old('is_default', $bankAccount->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Définir comme compte par défaut
                        </label>
                    </div>
                    <p class="mt-1 text-sm sm:text-xs text-gray-500">
                        Le compte par défaut sera pré-sélectionné lors des demandes de retrait
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t flex-col sm:flex-row">
                <a href="{{ route('client.bank-accounts.index') }}"
                   class="px-4 sm:px-5 lg:px-6 py-3 border border-gray-300 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="px-4 sm:px-5 lg:px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
