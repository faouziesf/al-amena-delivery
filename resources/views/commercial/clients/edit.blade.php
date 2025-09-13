@extends('layouts.commercial')

@section('title', 'Modifier Client - ' . $client->name)
@section('page-title', 'Modifier le Client')
@section('page-description', 'Modifiez les informations et la tarification du client')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('commercial.clients.show', $client) }}" 
       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour au profil
    </a>
    
    <a href="{{ route('commercial.clients.index') }}" 
       class="px-4 py-2 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        Liste des clients
    </a>
</div>
@endsection

@section('content')
<div x-data="editClientApp()" class="max-w-4xl mx-auto">
    
    <!-- Client Info Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg text-white p-6 mb-8">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ substr($client->name, 0, 2) }}</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold">{{ $client->name }}</h1>
                <p class="text-purple-100">Client #{{ $client->id }} - {{ $client->email }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                        {{ $client->account_status === 'ACTIVE' ? 'bg-green-500 text-white' : 
                           ($client->account_status === 'PENDING' ? 'bg-orange-500 text-white' : 'bg-red-500 text-white') }}">
                        {{ $client->account_status === 'ACTIVE' ? 'Actif' : 
                           ($client->account_status === 'PENDING' ? 'En attente' : 'Suspendu') }}
                    </span>
                    <span class="text-purple-200 text-sm">
                        Créé le {{ $client->created_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form @submit.prevent="updateClient" class="space-y-8">
        
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Informations de Base</h3>
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
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <input x-model="form.email" type="email" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="email@example.com">
                    <p class="text-xs text-gray-500 mt-1">L'email ne peut pas être dupliqué</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro de téléphone <span class="text-red-500">*</span>
                    </label>
                    <input x-model="form.phone" type="tel" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="+216 XX XXX XXX">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de la boutique/entreprise
                    </label>
                    <input x-model="form.shop_name" type="text"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Nom commercial">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse complète <span class="text-red-500">*</span>
                </label>
                <textarea x-model="form.address" rows="3" required
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                          placeholder="Adresse complète de l'expéditeur"></textarea>
            </div>
        </div>

        <!-- Password Update -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Changement de Mot de Passe</h3>
                <p class="text-sm text-gray-600">Laissez vide pour conserver le mot de passe actuel</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <input x-model="form.new_password" :type="showPassword ? 'text' : 'password'" minlength="6"
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
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères si rempli</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input x-model="form.new_password_confirmation" type="password" minlength="6"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500">
                    <p x-show="form.new_password && form.new_password_confirmation && form.new_password !== form.new_password_confirmation" 
                       class="text-xs text-red-600 mt-1">Les mots de passe ne correspondent pas</p>
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Informations Professionnelles</h3>
                <p class="text-sm text-gray-600">Détails commerciaux et légaux</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Matricule fiscal</label>
                    <input x-model="form.fiscal_number" type="text"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Numéro d'identification fiscale">
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
            </div>
        </div>

        <!-- Pricing Configuration -->
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Configuration Tarifaire</h3>
                <p class="text-sm text-gray-600">Modifiez les prix personnalisés pour ce client</p>
            </div>

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
                        </div>

                        <div class="bg-green-100 p-3 rounded-lg">
                            <p class="text-sm text-green-700">
                                <span class="font-medium">Actuel :</span> {{ number_format($client->clientProfile->offer_delivery_price ?? 0, 3) }} DT
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
                        </div>

                        <div class="bg-red-100 p-3 rounded-lg">
                            <p class="text-sm text-red-700">
                                <span class="font-medium">Actuel :</span> {{ number_format($client->clientProfile->offer_return_price ?? 0, 3) }} DT
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Summary -->
            <div class="mt-8 bg-purple-50 border border-purple-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-purple-800 mb-4">Résumé des Modifications</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg border border-purple-200">
                        <div class="text-sm text-gray-600">Nouveau prix livraison</div>
                        <div class="text-2xl font-bold text-green-600" x-text="(form.delivery_price || 0).toFixed(3) + ' DT'"></div>
                        <div class="text-xs text-gray-500">
                            Ancien: {{ number_format($client->clientProfile->offer_delivery_price ?? 0, 3) }} DT
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-purple-200">
                        <div class="text-sm text-gray-600">Nouveau prix retour</div>
                        <div class="text-2xl font-bold text-red-600" x-text="(form.return_price || 0).toFixed(3) + ' DT'"></div>
                        <div class="text-xs text-gray-500">
                            Ancien: {{ number_format($client->clientProfile->offer_return_price ?? 0, 3) }} DT
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Status Management (Commercial/Supervisor only) -->
        @if(auth()->user()->canManageClients())
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Gestion du Compte</h3>
                <p class="text-sm text-gray-600">Actions administratives sur le compte client</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($client->account_status === 'PENDING')
                    <button type="button" @click="validateAccount()" 
                            class="flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Valider le Compte
                    </button>
                @endif

                @if($client->account_status === 'ACTIVE')
                    <button type="button" @click="suspendAccount()" 
                            class="flex items-center justify-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636"/>
                        </svg>
                        Suspendre le Compte
                    </button>
                @endif

                @if($client->account_status === 'SUSPENDED')
                    <button type="button" @click="reactivateAccount()" 
                            class="flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réactiver le Compte
                    </button>
                @endif
            </div>
        </div>
        @endif

        <!-- Error Display -->
        <div x-show="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
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

        <!-- Form Actions -->
        <div class="flex justify-between items-center pt-6">
            <div class="flex space-x-3">
                <a href="{{ route('commercial.clients.show', $client) }}" 
                   class="px-6 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler les modifications
                </a>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" @click="resetForm()" 
                        class="px-6 py-3 text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors">
                    Réinitialiser
                </button>
                
                <button type="submit" :disabled="loading || !canSubmit()"
                        class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span x-show="!loading" class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer les Modifications
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mise à jour...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function editClientApp() {
    return {
        showPassword: false,
        loading: false,
        errors: [],
        originalForm: {},
        
        form: {
            name: '{{ $client->name }}',
            email: '{{ $client->email }}',
            phone: '{{ $client->phone }}',
            address: '{{ $client->address }}',
            shop_name: '{{ $client->clientProfile->shop_name ?? '' }}',
            fiscal_number: '{{ $client->clientProfile->fiscal_number ?? '' }}',
            business_sector: '{{ $client->clientProfile->business_sector ?? '' }}',
            identity_document: '{{ $client->clientProfile->identity_document ?? '' }}',
            delivery_price: {{ $client->clientProfile->offer_delivery_price ?? 0 }},
            return_price: {{ $client->clientProfile->offer_return_price ?? 0 }},
            new_password: '',
            new_password_confirmation: ''
        },

        init() {
            // Save original form data for reset
            this.originalForm = { ...this.form };
        },

        canSubmit() {
            // Check if passwords match when provided
            if (this.form.new_password && this.form.new_password !== this.form.new_password_confirmation) {
                return false;
            }
            
            // Check required fields
            return this.form.name && 
                   this.form.email && 
                   this.form.phone && 
                   this.form.address && 
                   this.form.delivery_price > 0 && 
                   this.form.return_price > 0;
        },

        resetForm() {
            this.form = { ...this.originalForm };
            this.errors = [];
        },

        async updateClient() {
            if (!this.canSubmit()) {
                showToast('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }

            this.loading = true;
            this.errors = [];

            try {
                const response = await fetch(`/commercial/clients/{{ $client->id }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (response.ok) {
                    showToast('Client mis à jour avec succès !', 'success');
                    
                    // Update original form data
                    this.originalForm = { ...this.form };
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = `/commercial/clients/{{ $client->id }}`;
                    }, 1500);
                } else {
                    if (result.errors) {
                        this.errors = Object.values(result.errors).flat();
                    } else {
                        this.errors = [result.message || 'Erreur lors de la mise à jour du client'];
                    }
                    
                    // Scroll to errors
                    document.querySelector('[x-show="errors.length > 0"]')?.scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                }
            } catch (error) {
                this.errors = ['Erreur de connexion. Veuillez réessayer.'];
                console.error('Erreur mise à jour client:', error);
            } finally {
                this.loading = false;
            }
        },

        async validateAccount() {
            if (!confirm('Êtes-vous sûr de vouloir valider ce compte client ?')) {
                return;
            }

            try {
                const response = await fetch(`/commercial/clients/{{ $client->id }}/validate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notes: 'Compte validé depuis la page d\'édition'
                    })
                });

                if (response.ok) {
                    showToast('Compte client validé avec succès', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la validation', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        },

        async suspendAccount() {
            const reason = prompt('Motif de suspension:');
            if (!reason) return;

            try {
                const response = await fetch(`/commercial/clients/{{ $client->id }}/suspend`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ reason })
                });

                if (response.ok) {
                    showToast('Compte client suspendu avec succès', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la suspension', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        },

        async reactivateAccount() {
            if (!confirm('Êtes-vous sûr de vouloir réactiver ce compte client ?')) {
                return;
            }

            try {
                const response = await fetch(`/commercial/clients/{{ $client->id }}/validate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        notes: 'Compte réactivé depuis la page d\'édition'
                    })
                });

                if (response.ok) {
                    showToast('Compte client réactivé avec succès', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const data = await response.json();
                    showToast(data.message || 'Erreur lors de la réactivation', 'error');
                }
            } catch (error) {
                showToast('Erreur de connexion', 'error');
            }
        }
    }
}
</script>
@endpush