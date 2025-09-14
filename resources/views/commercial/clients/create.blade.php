@extends('layouts.commercial')

@section('title', 'Nouveau Client')
@section('page-title', 'Créer un Nouveau Client')
@section('page-description', 'Ajoutez un nouveau client avec tarification personnalisée')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.clients.index') }}" 
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>
</div>
@endsection

@section('content')
<div x-data="createClientApp()" class="max-w-4xl mx-auto">
    <!-- Progress Steps -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol class="flex items-center">
                <li class="relative pr-8 sm:pr-20">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="h-0.5 w-full bg-gray-200"></div>
                    </div>
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full"
                         :class="currentStep >= 1 ? 'bg-purple-600' : 'bg-gray-300'">
                        <span class="text-sm font-medium text-white">1</span>
                    </div>
                    <span class="mt-2 block text-sm font-medium text-gray-900">Informations</span>
                </li>

                <li class="relative pr-8 sm:pr-20">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="h-0.5 w-full bg-gray-200"></div>
                    </div>
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full"
                         :class="currentStep >= 2 ? 'bg-purple-600' : 'bg-gray-300'">
                        <span class="text-sm font-medium text-white">2</span>
                    </div>
                    <span class="mt-2 block text-sm font-medium text-gray-900">Professionnel</span>
                </li>

                <li class="relative">
                    <div class="relative flex h-8 w-8 items-center justify-center rounded-full"
                         :class="currentStep >= 3 ? 'bg-purple-600' : 'bg-gray-300'">
                        <span class="text-sm font-medium text-white">3</span>
                    </div>
                    <span class="mt-2 block text-sm font-medium text-gray-900">Tarification</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Main Form -->
    <form @submit.prevent="submitForm" class="space-y-8">
        <!-- Step 1: Basic Information -->
        <div x-show="currentStep === 1" x-transition class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Informations de Base</h3>
                <p class="text-sm text-gray-600">Coordonnées principales du client</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input x-model="form.name" type="text" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Nom et prénom du client">
                    <p class="text-xs text-gray-500 mt-1">Nom qui apparaîtra sur les documents</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <input x-model="form.email" type="email" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="email@example.com">
                    <p class="text-xs text-gray-500 mt-1">Email de connexion unique</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro de téléphone <span class="text-red-500">*</span>
                    </label>
                    <input x-model="form.phone" type="tel" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="+216 XX XXX XXX">
                    <p class="text-xs text-gray-500 mt-1">Numéro principal de contact</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de la boutique/entreprise
                    </label>
                    <input x-model="form.shop_name" type="text"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Nom commercial">
                    <p class="text-xs text-gray-500 mt-1">Optionnel - nom affiché publiquement</p>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse complète <span class="text-red-500">*</span>
                </label>
                <textarea x-model="form.address" rows="3" required
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                          placeholder="Adresse complète de l'expéditeur"></textarea>
                <p class="text-xs text-gray-500 mt-1">Adresse qui apparaîtra sur les étiquettes de colis</p>
            </div>

            <!-- Password Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-900 mb-4">Mot de passe de connexion</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input x-model="form.password" :type="showPassword ? 'text' : 'password'" required minlength="6"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-10 focus:ring-purple-500 focus:border-purple-500">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.465 8.465m1.413 1.413L18.707 18.707"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input x-model="form.password_confirmation" type="password" required minlength="6"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500">
                        <p x-show="form.password && form.password_confirmation && form.password !== form.password_confirmation" 
                           class="text-xs text-red-600 mt-1">Les mots de passe ne correspondent pas</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="button" @click="nextStep()" 
                        :disabled="!canProceedStep1()"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Suivant
                    <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Professional Information -->
        <div x-show="currentStep === 2" x-transition class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Informations Professionnelles</h3>
                <p class="text-sm text-gray-600">Détails commerciaux et légaux (optionnels mais recommandés)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Matricule fiscal</label>
                    <input x-model="form.fiscal_number" type="text"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Numéro d'identification fiscale">
                    <p class="text-xs text-gray-500 mt-1">Pour les entreprises enregistrées</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secteur d'activité</label>
                    <select x-model="form.business_sector" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Sélectionnez un secteur</option>
                        <option value="Commerce">Commerce de détail</option>
                        <option value="Textile">Textile & Vêtements</option>
                        <option value="Electronique">Électronique</option>
                        <option value="Alimentaire">Alimentaire</option>
                        <option value="Artisanat">Artisanat</option>
                        <option value="Services">Services</option>
                        <option value="Cosmétique">Cosmétique & Beauté</option>
                        <option value="Mobilier">Mobilier & Décoration</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Document d'identité</label>
                <input x-model="form.identity_document" type="text"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                       placeholder="CIN, Passeport, Registre de commerce...">
                <p class="text-xs text-gray-500 mt-1">Type et numéro du document d'identité</p>
            </div>

            <!-- Additional Notes -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes internes</label>
                <textarea x-model="form.internal_notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                          placeholder="Notes visibles uniquement par l'équipe commerciale..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Ces informations ne seront pas visibles par le client</p>
            </div>

            <div class="flex justify-between mt-8">
                <button type="button" @click="previousStep()" 
                        class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Précédent
                </button>
                
                <button type="button" @click="nextStep()" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Suivant
                    <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Pricing -->
        <div x-show="currentStep === 3" x-transition class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Configuration Tarifaire</h3>
                <p class="text-sm text-gray-600">Définissez les prix personnalisés pour ce client</p>
            </div>

            <!-- Pricing Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="border-2 border-green-200 rounded-lg p-6 bg-green-50">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="text-lg font-semibold text-green-800">Frais de Livraison</h4>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-green-700 mb-2">
                                Prix livraison réussie (DT) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input x-model.number="form.delivery_price" type="number" step="0.001" required min="0" max="999.999"
                                       class="w-full border border-green-300 rounded-lg px-4 py-3 pr-12 focus:ring-green-500 focus:border-green-500">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-green-600 font-medium">DT</span>
                            </div>
                            <p class="text-xs text-green-600 mt-1">Montant facturé quand le colis est livré avec succès</p>
                        </div>

                        <div class="bg-green-100 p-3 rounded-lg">
                            <p class="text-sm text-green-700">
                                <span class="font-medium">Scénario :</span> Le client paie ce montant pour chaque colis livré au destinataire.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border-2 border-red-200 rounded-lg p-6 bg-red-50">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.936-.833-2.707 0L3.107 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <h4 class="text-lg font-semibold text-red-800">Frais de Retour</h4>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-red-700 mb-2">
                                Prix retour échec (DT) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input x-model.number="form.return_price" type="number" step="0.001" required min="0" max="999.999"
                                       class="w-full border border-red-300 rounded-lg px-4 py-3 pr-12 focus:ring-red-500 focus:border-red-500">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-red-600 font-medium">DT</span>
                            </div>
                            <p class="text-xs text-red-600 mt-1">Montant facturé quand le colis est retourné (peut être 0 pour un retour gratuit)</p>
                        </div>

                        <div class="bg-red-100 p-3 rounded-lg">
                            <p class="text-sm text-red-700">
                                <span class="font-medium">Scénario :</span> Le client paie ce montant quand le destinataire refuse le colis.
                                <br>
                                <span class="font-medium">Note :</span> Vous pouvez définir 0 DT pour offrir un retour gratuit à ce client.
                            </p>
                        </div>

                        <!-- Additional info for 0 return price -->
                        <div x-show="form.return_price == 0" class="mt-3 bg-yellow-100 border border-yellow-300 p-3 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-yellow-700">
                                    <span class="font-medium">Retour gratuit activé :</span> 
                                    Ce client ne sera pas facturé pour les colis retournés. 
                                    Cette configuration peut impacter votre rentabilité.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Summary -->
            <div class="mt-8 bg-purple-50 border border-purple-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-purple-800 mb-4">Résumé de la Configuration</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg border border-purple-200">
                        <div class="text-sm text-gray-600">Livraison réussie</div>
                        <div class="text-2xl font-bold text-green-600" x-text="(form.delivery_price || 0).toFixed(3) + ' DT'"></div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-purple-200">
                        <div class="text-sm text-gray-600">Retour échec</div>
                        <div class="text-2xl font-bold text-red-600" x-text="(form.return_price || 0).toFixed(3) + ' DT'"></div>
                        <div x-show="form.return_price == 0" class="text-xs text-yellow-600 font-medium mt-1">
                            ✓ Retour gratuit
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-purple-100 rounded-lg">
                    <p class="text-sm text-purple-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">Note :</span> Ces tarifs pourront être modifiés ultérieurement depuis la fiche client.
                    </p>
                </div>
            </div>

            <!-- Error Display -->
            <div x-show="errors.length > 0" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erreurs de validation :</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <template x-for="error in errors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8">
                <button type="button" @click="previousStep()" 
                        class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Précédent
                </button>
                
                <div class="flex space-x-3">
                    <button type="button" @click="saveDraft()" 
                            class="px-6 py-3 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
                        Sauvegarder brouillon
                    </button>
                    
                    <button type="submit" :disabled="loading || !canSubmit()"
                            class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!loading" class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Créer le Client
                        </span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Création en cours...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function createClientApp() {
    return {
        currentStep: 1,
        showPassword: false,
        loading: false,
        errors: [],
        
        form: {
            name: '',
            email: '',
            phone: '',
            address: '',
            shop_name: '',
            fiscal_number: '',
            business_sector: '',
            identity_document: '',
            internal_notes: '',
            delivery_price: '',
            return_price: '',
            password: '',
            password_confirmation: ''
        },

        init() {
            // Check for duplicate parameter
            const urlParams = new URLSearchParams(window.location.search);
            const duplicateId = urlParams.get('duplicate');
            if (duplicateId) {
                this.loadClientForDuplication(duplicateId);
            }
        },

        async loadClientForDuplication(clientId) {
            try {
                const response = await fetch(`/commercial/clients/${clientId}`);
                if (response.ok) {
                    const clientData = await response.json();
                    // Copy relevant fields, excluding sensitive data
                    this.form.shop_name = clientData.shop_name || '';
                    this.form.business_sector = clientData.business_sector || '';
                    this.form.delivery_price = clientData.delivery_price || '';
                    this.form.return_price = clientData.return_price || '';
                    showToast('Données copiées depuis le client existant', 'info');
                }
            } catch (error) {
                console.error('Erreur lors du chargement pour duplication:', error);
            }
        },

        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        canProceedStep1() {
            return this.form.name && 
                   this.form.email && 
                   this.form.phone && 
                   this.form.address && 
                   this.form.password && 
                   this.form.password_confirmation &&
                   this.form.password === this.form.password_confirmation &&
                   this.form.password.length >= 6;
        },

        canSubmit() {
            return this.canProceedStep1() && 
                   this.form.delivery_price > 0 && 
                   this.form.return_price >= 0;  // CORRIGÉ: Permet maintenant 0 pour le retour
        },

        async saveDraft() {
            // TODO: Implement draft saving functionality
            showToast('Fonctionnalité de brouillon à implémenter', 'info');
        },

        async submitForm() {
            if (!this.canSubmit()) {
                showToast('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            this.loading = true;
            this.errors = [];

            try {
                const response = await fetch('/commercial/clients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (response.ok) {
                    let successMessage = `Client ${this.form.name} créé avec succès !`;
                    if (this.form.return_price == 0) {
                        successMessage += ' (Retour gratuit configuré)';
                    }
                    showToast(successMessage, 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = `/commercial/clients/${result.client.id}`;
                    }, 1500);
                } else {
                    if (result.errors) {
                        this.errors = Object.values(result.errors).flat();
                    } else {
                        this.errors = [result.message || 'Erreur lors de la création du client'];
                    }
                    
                    // Scroll to errors
                    document.querySelector('[x-show="errors.length > 0"]')?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                }
            } catch (error) {
                this.errors = ['Erreur de connexion. Veuillez réessayer.'];
                console.error('Erreur création client:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush