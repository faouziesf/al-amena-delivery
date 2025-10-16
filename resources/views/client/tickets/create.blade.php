@extends('layouts.client')

@section('title', 'Nouveau Ticket de Support')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header Modern -->
    <div class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900">Nouveau Ticket de Support</h1>
                        <p class="text-xs sm:text-sm text-slate-600">Obtenez l'aide dont vous avez besoin</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center space-x-3">
                    <div class="text-sm text-slate-500">
                        <span class="font-medium">Réponse moyenne :</span> <span class="text-green-600 font-semibold">24h</span>
                    </div>
                    <a href="{{ route('client.tickets.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-4">

            <!-- Formulaire principal - 3 colonnes -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                    @if($complaint ?? false)
                        <!-- Alert réclamation -->
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 sm:py-3 text-white">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold">Ticket depuis une réclamation</h3>
                                    <p class="text-sm text-orange-100">Réclamation #{{ $complaint->id }} - Les informations seront pré-remplies</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data" class="p-3 sm:p-4 space-y-3 sm:space-y-4" id="ticketForm">
                        @csrf

                        @if($complaint ?? false)
                            <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                        @endif

                        <!-- Étape 1: Type de demande -->
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">Type de demande</h2>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <label class="ticket-type-option {{ old('type', $complaint ?? false ? 'COMPLAINT' : '') === 'COMPLAINT' ? 'selected' : '' }}" data-value="COMPLAINT">
                                    <input type="radio" name="type" value="COMPLAINT" class="hidden" {{ old('type', $complaint ?? false ? 'COMPLAINT' : '') === 'COMPLAINT' ? 'checked' : '' }}>
                                    <div class="text-center p-2.5 rounded-lg border-2 transition-all duration-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50">
                                        <div class="text-2xl mb-1">📋</div>
                                        <div class="font-semibold text-slate-900">Réclamation</div>
                                        <div class="text-sm text-slate-500 mt-1">Problème avec un colis</div>
                                    </div>
                                </label>

                                <label class="ticket-type-option {{ old('type') === 'QUESTION' ? 'selected' : '' }}" data-value="QUESTION">
                                    <input type="radio" name="type" value="QUESTION" class="hidden" {{ old('type') === 'QUESTION' ? 'checked' : '' }}>
                                    <div class="text-center p-2.5 rounded-lg border-2 transition-all duration-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50">
                                        <div class="text-2xl mb-1">❓</div>
                                        <div class="font-semibold text-slate-900">Question</div>
                                        <div class="text-sm text-slate-500 mt-1">Information générale</div>
                                    </div>
                                </label>

                                <label class="ticket-type-option {{ old('type') === 'SUPPORT' ? 'selected' : '' }}" data-value="SUPPORT">
                                    <input type="radio" name="type" value="SUPPORT" class="hidden" {{ old('type') === 'SUPPORT' ? 'checked' : '' }}>
                                    <div class="text-center p-2.5 rounded-lg border-2 transition-all duration-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50">
                                        <div class="text-2xl mb-1">🛠️</div>
                                        <div class="font-semibold text-slate-900">Support</div>
                                        <div class="text-sm text-slate-500 mt-1">Aide technique</div>
                                    </div>
                                </label>

                                <label class="ticket-type-option {{ old('type') === 'OTHER' ? 'selected' : '' }}" data-value="OTHER">
                                    <input type="radio" name="type" value="OTHER" class="hidden" {{ old('type') === 'OTHER' ? 'checked' : '' }}>
                                    <div class="text-center p-2.5 rounded-lg border-2 transition-all duration-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50">
                                        <div class="text-2xl mb-1">📝</div>
                                        <div class="font-semibold text-slate-900">Autre</div>
                                        <div class="text-sm text-slate-500 mt-1">Autre sujet</div>
                                    </div>
                                </label>
                            </div>
                            @error('type')
                                <p class="text-sm text-red-600 flex items-center mt-2">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Séparateur -->
                        <div class="border-t border-slate-200"></div>

                        <!-- Étape 2: Informations -->
                        <div class="space-y-3 sm:space-y-2 sm:space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                                <h2 class="text-xl font-bold text-slate-900">Détails de votre demande</h2>
                            </div>

                            <!-- Sujet -->
                            <div>
                                <label for="subject" class="block text-sm font-semibold text-slate-700 mb-3">
                                    Sujet de votre demande *
                                </label>
                                <input type="text" name="subject" id="subject" required
                                       value="{{ old('subject', $complaint ?? false ? 'Réclamation - ' . ($complaint->type ?? '') : '') }}"
                                       placeholder="Ex: Problème de livraison, Question sur les frais..."
                                       class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('subject') border-red-300 @enderror">
                                @error('subject')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-slate-700 mb-3">
                                    Description détaillée *
                                </label>
                                <textarea name="description" id="description" rows="6" required
                                          placeholder="Décrivez votre problème ou question en détail. Plus vous fournissez d'informations, plus nous pourrons vous aider efficacement..."
                                          class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none @error('description') border-red-300 @enderror">{{ old('description', $complaint->description ?? '') }}</textarea>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <div class="mt-2 text-sm text-slate-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Caractères: <span id="charCount">0</span>
                                </div>
                            </div>

                            <!-- Colis concerné -->
                            <div>
                                <label for="package_code" class="block text-sm font-semibold text-slate-700 mb-3">
                                    Colis concerné (optionnel)
                                </label>
                                <div class="relative">
                                    <input type="text" name="package_code" id="package_code"
                                           placeholder="Saisissez le code du colis (ex: PKG0200001)"
                                           class="w-full pl-12 pr-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">
                                    Si votre demande concerne un colis spécifique, indiquez son code ici
                                </p>
                            </div>

                            <!-- Pièces jointes -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">
                                    Pièces jointes (optionnel)
                                </label>
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-2.5 sm:p-3 sm:p-3 sm:p-2.5 sm:p-3 text-center hover:border-slate-400 transition-colors duration-200" id="dropZone">
                                    <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt" class="hidden">
                                    <label for="attachments" class="cursor-pointer">
                                        <svg class="w-12 h-12 mx-auto mb-2 sm:mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-slate-700 mb-2">Ajoutez vos fichiers</h3>
                                        <p class="text-slate-500 mb-2 sm:mb-3">Glissez-déposez ou cliquez pour sélectionner</p>
                                        <div class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Choisir les fichiers
                                        </div>
                                    </label>
                                    <p class="text-xs text-slate-400 mt-4">
                                        Formats: JPG, PNG, PDF, DOC, DOCX, TXT • Max 5MB par fichier
                                    </p>
                                </div>

                                <!-- Preview des fichiers -->
                                <div id="filePreview" class="hidden mt-4 space-y-2"></div>

                                @error('attachments.*')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Séparateur -->
                        <div class="border-t border-slate-200"></div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-500">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Auto-sauvegarde activée
                                </span>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="button" onclick="saveDraft()"
                                        class="inline-flex items-center px-3 sm:px-4 py-2 border-2 border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-semibold transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    Brouillon
                                </button>

                                <button type="submit" id="submitBtn"
                                        class="inline-flex items-center px-4 sm:px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-bold transition-all duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Créer le ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar d'aide et information -->
            <div class="lg:col-span-1">
                <div class="space-y-3 sm:space-y-2 sm:space-y-3">

                    <!-- Aide et conseils -->
                    <div class="bg-white rounded-lg shadow-md border border-slate-200 p-3 sm:p-2.5 sm:p-3 sticky top-6">
                        <div class="flex items-center space-x-3 mb-3 sm:mb-2 sm:mb-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Conseils utiles</h3>
                        </div>

                        <div class="space-y-2 sm:space-y-3 text-sm">
                            <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-green-800 mb-1">Titre explicite</p>
                                    <p class="text-green-700">Un sujet précis aide notre équipe à traiter votre demande plus rapidement</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-blue-800 mb-1">Description complète</p>
                                    <p class="text-blue-700">Plus vous détaillez votre problème, mieux nous pourrons vous aider</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                <div class="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-orange-800 mb-1">Pièces jointes</p>
                                    <p class="text-orange-700">Ajoutez des captures d'écran ou documents pour clarifier votre demande</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Temps de réponse -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg shadow-md p-3 sm:p-2.5 sm:p-3 text-white">
                        <div class="flex items-center space-x-3 mb-2 sm:mb-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-bold">Temps de réponse</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="bg-white bg-opacity-10 rounded-lg p-3">
                                <div class="text-xl font-bold">2-4h</div>
                                <div class="text-xs text-blue-100">Questions simples</div>
                            </div>
                            <div class="bg-white bg-opacity-10 rounded-lg p-3">
                                <div class="text-xl font-bold">24-48h</div>
                                <div class="text-xs text-blue-100">Problèmes complexes</div>
                            </div>
                        </div>
                    </div>

                    <!-- Moyens de contact alternatifs -->
                    <div class="bg-white rounded-lg shadow-md border border-slate-200 p-3 sm:p-2.5 sm:p-3">
                        <div class="flex items-center space-x-3 mb-2 sm:mb-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <h4 class="font-bold text-slate-900">Autres contacts</h4>
                        </div>

                        <div class="space-y-3">
                            <a href="mailto:support@alamena.com" class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                                <div class="w-5 h-5 bg-slate-200 rounded-lg flex items-center justify-center group-hover:bg-slate-300">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-800">Email</div>
                                    <div class="text-sm text-slate-600">support@alamena.com</div>
                                </div>
                            </a>

                            <a href="tel:+21670123456" class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                                <div class="w-5 h-5 bg-slate-200 rounded-lg flex items-center justify-center group-hover:bg-slate-300">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-800">Téléphone</div>
                                    <div class="text-sm text-slate-600">+216 70 123 456</div>
                                </div>
                            </a>
                        </div>

                        <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-semibold text-yellow-800">Horaires</span>
                            </div>
                            <p class="text-sm text-yellow-700 mt-1">Lundi - Vendredi : 8h00 - 18h00</p>
                        </div>
                    </div>

                    <!-- Statistiques de satisfaction -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-3 sm:p-2.5 sm:p-3 text-white">
                        <h4 class="font-bold mb-2 sm:mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Notre performance
                        </h4>
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div>
                                <div class="text-lg sm:text-xl font-bold">98%</div>
                                <div class="text-xs text-green-100">Satisfaction clients</div>
                            </div>
                            <div>
                                <div class="text-lg sm:text-xl font-bold">4.8/5</div>
                                <div class="text-xs text-green-100">Note moyenne</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des types de tickets
    const typeOptions = document.querySelectorAll('.ticket-type-option');
    const typeInputs = document.querySelectorAll('input[name="type"]');

    typeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Supprimer la sélection précédente
            typeOptions.forEach(opt => opt.classList.remove('selected'));
            typeInputs.forEach(input => input.checked = false);

            // Sélectionner le nouveau type
            this.classList.add('selected');
            const input = this.querySelector('input[type="radio"]');
            if (input) {
                input.checked = true;
            }

            // Mettre à jour l'apparence
            updateTypeSelection();
        });
    });

    function updateTypeSelection() {
        typeOptions.forEach(option => {
            const div = option.querySelector('div');
            if (option.classList.contains('selected')) {
                div.classList.remove('border-slate-200', 'hover:border-blue-300', 'hover:bg-blue-50');
                div.classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
            } else {
                div.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-200');
                div.classList.add('border-slate-200', 'hover:border-blue-300', 'hover:bg-blue-50');
            }
        });
    }

    // Compteur de caractères pour la description
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    descriptionField.addEventListener('input', function() {
        charCount.textContent = this.value.length;

        // Changement de couleur selon la longueur
        if (this.value.length < 50) {
            charCount.className = 'text-red-500 font-semibold';
        } else if (this.value.length < 100) {
            charCount.className = 'text-orange-500 font-semibold';
        } else {
            charCount.className = 'text-green-500 font-semibold';
        }
    });

    // Gestion des fichiers avec drag & drop
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('attachments');
    const filePreview = document.getElementById('filePreview');

    // Prévenir les comportements par défaut
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Styles pour le drag & drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
        dropZone.classList.remove('border-slate-300');
    }

    function unhighlight() {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        dropZone.classList.add('border-slate-300');
    }

    // Gérer le drop
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFileSelect();
    }

    // Gérer la sélection de fichier
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        const files = fileInput.files;
        filePreview.innerHTML = '';

        if (files.length > 0) {
            filePreview.classList.remove('hidden');

            Array.from(files).forEach((file, index) => {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileElement = document.createElement('div');
                fileElement.className = 'flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg';

                const fileIcon = getFileIcon(file.type);

                fileElement.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="text-lg sm:text-xl">${fileIcon}</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">${file.name}</p>
                            <p class="text-xs text-slate-500">${fileSize} MB</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;

                filePreview.appendChild(fileElement);
            });
        } else {
            filePreview.classList.add('hidden');
        }
    }

    function getFileIcon(type) {
        if (type.includes('image')) return '🖼️';
        if (type.includes('pdf')) return '📄';
        if (type.includes('word')) return '📝';
        if (type.includes('text')) return '📃';
        return '📎';
    }

    // Auto-save draft
    function saveToLocalStorage() {
        const formData = {
            type: document.querySelector('input[name="type"]:checked')?.value || '',
            subject: document.getElementById('subject').value,
            description: document.getElementById('description').value,
            package_code: document.getElementById('package_code').value,
            timestamp: Date.now()
        };

        if (formData.subject || formData.description) {
            localStorage.setItem('ticket_draft', JSON.stringify(formData));
        }
    }

    // Sauvegarde auto toutes les 30 secondes
    setInterval(saveToLocalStorage, 30000);

    // Sauvegarde sur les changements importants
    ['subject', 'description'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field.addEventListener('blur', saveToLocalStorage);
    });

    // Fonction brouillon
    window.saveDraft = function() {
        saveToLocalStorage();

        // Animation de confirmation
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Sauvegardé
        `;
        btn.classList.add('bg-green-50', 'border-green-300', 'text-green-700');

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('bg-green-50', 'border-green-300', 'text-green-700');
        }, 2000);
    }

    // Supprimer un fichier
    window.removeFile = function(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;

        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }

        fileInput.files = dt.files;
        handleFileSelect();
    }

    // Charger le brouillon au chargement de la page
    const draft = localStorage.getItem('ticket_draft');
    if (draft) {
        const data = JSON.parse(draft);
        const timeDiff = Date.now() - data.timestamp;
        const hoursDiff = timeDiff / (1000 * 60 * 60);

        if (hoursDiff < 24 && (data.subject || data.description)) {
            const restoreDraft = confirm('Un brouillon récent a été trouvé. Voulez-vous le restaurer ?');
            if (restoreDraft) {
                if (data.type) {
                    const typeOption = document.querySelector(`[data-value="${data.type}"]`);
                    if (typeOption) {
                        typeOption.click();
                    }
                }
                if (data.subject) document.getElementById('subject').value = data.subject;
                if (data.description) {
                    document.getElementById('description').value = data.description;
                    descriptionField.dispatchEvent(new Event('input'));
                }
                if (data.package_code) document.getElementById('package_code').value = data.package_code;
            }
        }
    }

    // Validation en temps réel
    const form = document.getElementById('ticketForm');
    const submitBtn = document.getElementById('submitBtn');

    function validateForm() {
        const type = document.querySelector('input[name="type"]:checked');
        const subject = document.getElementById('subject').value.trim();
        const description = document.getElementById('description').value.trim();

        const isValid = type && subject.length > 0 && description.length >= 10;

        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        return isValid;
    }

    // Validation en temps réel
    ['input', 'change'].forEach(eventType => {
        form.addEventListener(eventType, validateForm);
    });

    // Validation initiale
    validateForm();

    // Soumission du formulaire
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }

        // Animation de soumission
        submitBtn.innerHTML = `
            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Création en cours...
        `;
        submitBtn.disabled = true;

        // Supprimer le brouillon après soumission
        localStorage.removeItem('ticket_draft');
    });
});
</script>

<style>
.ticket-type-option.selected div {
    @apply border-blue-500 bg-blue-50 ring-2 ring-blue-200;
}

.ticket-type-option:not(.selected) div:hover {
    @apply border-blue-300 bg-blue-50;
}

#dropZone.drag-over {
    @apply border-blue-400 bg-blue-50;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#filePreview > div {
    animation: fadeInUp 0.3s ease-out;
}

.form-step {
    opacity: 0.6;
    transition: opacity 0.2s;
}

.form-step.active {
    opacity: 1;
}

/* Animation pour les boutons */
button {
    transition: all 0.2s ease;
}

button:hover {
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}
</style>
@endsection