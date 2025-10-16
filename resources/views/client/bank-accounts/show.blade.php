@extends('layouts.client')

@section('title', 'Détails du Compte Bancaire')

@section('content')
<div class="max-w-7xl mx-auto sm:py-5 lg:py-3 sm:py-2 sm:py-3">
    <!-- En-tête -->
    <div class="mb-2 sm:mb-3 sm:mb-3 sm:mb-2 sm:mb-3">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between flex-col sm:flex-row">
            <div class="mb-2 sm:mb-3 lg:mb-0">
                <h1 class="text-xl sm:text-lg sm:text-xl font-bold text-gray-900">🏦 Détails du Compte</h1>
                <p class="text-gray-600 mt-2">{{ $bankAccount->bank_name }}</p>
            </div>
            <div class="flex space-x-3 flex-col sm:flex-row">
                <a href="{{ route('client.bank-accounts.edit', $bankAccount) }}"
                   class="inline-flex items-center px-4 sm:px-5 lg:px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white rounded-lg transition-all transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('client.bank-accounts.index') }}"
                   class="inline-flex items-center px-4 sm:px-5 lg:px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Badge par défaut -->
    @if($bankAccount->is_default)
        <div class="mb-3 sm:mb-2 sm:mb-3">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800 transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
                <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Compte par défaut
            </span>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-2 sm:gap-3 lg:gap-2 sm:gap-3 lg:gap-3 sm:gap-2 sm:gap-3">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 transition-all duration-300 hover:-translate-y-1">
                <h2 class="text-xl font-bold text-gray-900 mb-3 sm:mb-2 sm:mb-3">Informations du compte</h2>

                <div class="space-y-3 sm:space-y-2 sm:space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Banque</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $bankAccount->bank_name }}</p>
                    </div>

                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Titulaire du compte</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $bankAccount->account_holder_name }}</p>
                    </div>

                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">IBAN</label>
                        <p class="text-lg font-mono text-gray-900 bg-gray-50 p-3 rounded-lg">
                            {{ $bankAccount->formatted_iban }}
                        </p>
                    </div>

                    @if($bankAccount->last_used_at)
                        <div class="border-t pt-6">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Dernière utilisation</label>
                            <p class="text-lg text-gray-900">
                                {{ $bankAccount->last_used_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    @endif

                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Date d'ajout</label>
                        <p class="text-lg text-gray-900">
                            {{ $bankAccount->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-2.5 sm:p-3 sm:p-5 lg:p-3 sm:p-2.5 sm:p-3 transition-all duration-300 hover:-translate-y-1">
                <h2 class="text-xl font-bold text-gray-900 mb-3 sm:mb-2 sm:mb-3">Actions</h2>

                <div class="space-y-3">
                    @if(!$bankAccount->is_default)
                        <form action="{{ route('client.bank-accounts.set-default', $bankAccount) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 hover:bg-green-700 text-white rounded-lg transition-colors flex-col sm:flex-row">
                                <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Définir par défaut
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('client.bank-accounts.edit', $bankAccount) }}" 
                       class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex-col sm:flex-row">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>

                    @if($bankAccount->canBeDeleted())
                        <form action="{{ route('client.bank-accounts.destroy', $bankAccount) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte bancaire ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex-col sm:flex-row">
                                <svg class="w-5 h-5 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    @else
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 text-center">
                                ⚠️ Ce compte ne peut pas être supprimé
                            </p>
                            <p class="text-sm sm:text-xs text-gray-500 mt-1 text-center">
                                (Compte par défaut ou retraits en cours)
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-2.5 sm:p-3">
                <div class="flex flex-col sm:flex-row">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">Information</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            Ce compte bancaire est utilisé pour les demandes de retrait par virement bancaire.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
