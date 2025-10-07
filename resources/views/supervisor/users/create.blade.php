@extends('layouts.supervisor')

@section('content')
<div x-data="userCreationWizard()" class="container mx-auto px-4 py-6">
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
                <p class="text-gray-600">Assistant de création par étapes selon le type de compte</p>
            </div>
        </div>
    </div>

    <!-- Affichage des erreurs -->
    @if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Erreurs de validation</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Indicateur d'étapes -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Étape 1: Type de compte -->
                <div class="flex items-center">
                    <div :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                         class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                        1
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-500'">
                        Type de compte
                    </span>
                </div>
                
                <!-- Flèche -->
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                
                <!-- Étape 2: Informations -->
                <div class="flex items-center">
                    <div :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                         class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                        2
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-500'">
                        Informations
                    </span>
                </div>
                
                <!-- Flèche -->
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                
                <!-- Étape 3: Configuration -->
                <div class="flex items-center">
                    <div :class="currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                         class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                        3
                    </div>
                    <span class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-500'">
                        Configuration
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <!-- Carte principale -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- ÉTAPE 1: SÉLECTION DU TYPE DE COMPTE -->
            <div x-show="currentStep === 1" x-transition class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Quel type de compte voulez-vous créer ?</h2>
                    <p class="text-gray-600">Choisissez le rôle approprié pour configurer les options nécessaires</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Client -->
                    <div @click="selectAccountType('CLIENT')" 
                         :class="form.role === 'CLIENT' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                         class="cursor-pointer border-2 border-gray-200 rounded-xl p-6 transition-all">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">👤</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Client</h3>
                            <p class="text-sm text-gray-600 mb-4">Expéditeur de colis avec portefeuille</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>• Création de colis</div>
                                <div>• Gestion du portefeuille</div>
                                <div>• Suivi des livraisons</div>
                                <div>• Demandes de recharge</div>
                            </div>
                        </div>
                    </div>

                    <!-- Livreur -->
                    <div @click="selectAccountType('DELIVERER')" 
                         :class="form.role === 'DELIVERER' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                         class="cursor-pointer border-2 border-gray-200 rounded-xl p-6 transition-all">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🚚</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Livreur</h3>
                            <p class="text-sm text-gray-600 mb-4">Agent de livraison mobile</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>• Acceptation de colis</div>
                                <div>• Scan QR codes</div>
                                <div>• Gestion des livraisons</div>
                                <div>• Portefeuille livreur</div>
                            </div>
                        </div>
                    </div>

                    <!-- Commercial -->
                    <div @click="selectAccountType('COMMERCIAL')" 
                         :class="form.role === 'COMMERCIAL' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                         class="cursor-pointer border-2 border-gray-200 rounded-xl p-6 transition-all">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">💼</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Commercial</h3>
                            <p class="text-sm text-gray-600 mb-4">Gestionnaire clients et livreurs</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>• Validation des comptes</div>
                                <div>• Gestion des recharges</div>
                                <div>• Support client</div>
                                <div>• Traitement réclamations</div>
                            </div>
                        </div>
                    </div>

                    <!-- Chef Dépôt -->
                    <div @click="selectAccountType('DEPOT_MANAGER')" 
                         :class="form.role === 'DEPOT_MANAGER' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                         class="cursor-pointer border-2 border-gray-200 rounded-xl p-6 transition-all">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">🏢</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Chef Dépôt</h3>
                            <p class="text-sm text-gray-600 mb-4">Responsable régional</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>• Gestion par gouvernorat</div>
                                <div>• Supervision livreurs</div>
                                <div>• Rapports régionaux</div>
                                <div>• Coordination locale</div>
                            </div>
                        </div>
                    </div>

                    <!-- Superviseur -->
                    <div @click="selectAccountType('SUPERVISOR')" 
                         :class="form.role === 'SUPERVISOR' ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'"
                         class="cursor-pointer border-2 border-gray-200 rounded-xl p-6 transition-all">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl">👨‍💼</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Superviseur</h3>
                            <p class="text-sm text-gray-600 mb-4">Accès administrateur complet</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>• Gestion complète système</div>
                                <div>• Création utilisateurs</div>
                                <div>• Rapports globaux</div>
                                <div>• Configuration système</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center mt-8">
                    <button @click="nextStep()" :disabled="!form.role"
                            :class="form.role ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-8 py-3 text-white rounded-lg transition-colors flex items-center">
                        Continuer
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 2: INFORMATIONS PERSONNELLES -->
            <div x-show="currentStep === 2" x-transition class="p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Informations Personnelles</h2>
                    <p class="text-gray-600">Saisissez les coordonnées de base de l'utilisateur</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            👤 Nom Complet *
                        </label>
                        <input x-model="form.name" type="text" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ✉️ Email *
                        </label>
                        <input x-model="form.email" type="email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            📱 Téléphone *
                        </label>
                        <input x-model="form.phone" type="tel" required placeholder="+216 XX XXX XXX"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            🔐 Mot de Passe *
                        </label>
                        <div class="relative">
                            <input x-model="form.password" :type="showPassword ? 'text' : 'password'" required minlength="8"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors pr-10">
                            <button @click="showPassword = !showPassword" type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Minimum 8 caractères</p>
                    </div>
                </div>

                <!-- Champs spécifiques selon le rôle -->
                <div x-show="form.role === 'CLIENT'" class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                    <h3 class="text-lg font-medium text-green-800 mb-4">👤 Configuration Client</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">🏪 Nom de la boutique</label>
                            <input x-model="form.shop_name" type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">📍 Adresse complète *</label>
                            <textarea x-model="form.address" rows="2" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">💰 Frais de livraison (DT) *</label>
                            <input x-model="form.delivery_price" type="number" step="0.001" min="0.001" max="999.999" required
                                   placeholder="Ex: 7.500"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Prix facturé pour chaque livraison réussie</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">📦 Frais de retour (DT) *</label>
                            <input x-model="form.return_price" type="number" step="0.001" min="0" max="999.999" required
                                   placeholder="Ex: 5.000 ou 0.000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Prix facturé en cas de retour (0.000 = gratuit)</p>
                        </div>
                    </div>
                </div>

                <div x-show="form.role === 'DELIVERER'" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-medium text-blue-800 mb-4">🚚 Configuration Livreur</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">🚗 Type de véhicule</label>
                            <select x-model="form.vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Sélectionner</option>
                                <option value="MOTO">🏍️ Moto</option>
                                <option value="VOITURE">🚗 Voiture</option>
                                <option value="CAMIONNETTE">🚐 Camionnette</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">🆔 Numéro d'immatriculation</label>
                            <input x-model="form.vehicle_registration" type="text" 
                                   placeholder="Ex: 123 TUN 456"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">🚛 Type de livreur *</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors"
                                       :class="form.is_transit_deliverer === false ? 'bg-blue-100 border-blue-500' : ''">
                                    <input type="radio" x-model="form.is_transit_deliverer" :value="false" name="deliverer_type" required
                                           class="text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">🏠 Livreur Local</div>
                                        <div class="text-xs text-gray-500">Livre dans sa délégation uniquement</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors"
                                       :class="form.is_transit_deliverer === true ? 'bg-blue-100 border-blue-500' : ''">
                                    <input type="radio" x-model="form.is_transit_deliverer" :value="true" name="deliverer_type" required
                                           class="text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">🚛 Livreur Transit</div>
                                        <div class="text-xs text-gray-500">Transporte entre délégations</div>
                                    </div>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <strong>Local:</strong> Accepte et livre les colis dans sa délégation<br>
                                <strong>Transit:</strong> Transporte les colis d'une délégation à une autre
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button @click="previousStep()" type="button"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Précédent
                    </button>
                    <button @click="nextStep()" type="button"
                            :disabled="!canProceedToStep3()"
                            :class="canProceedToStep3() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-8 py-3 text-white rounded-lg transition-colors flex items-center">
                        Continuer
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ÉTAPE 3: CONFIGURATION FINALE -->
            <div x-show="currentStep === 3" x-transition class="p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Configuration Finale</h2>
                    <p class="text-gray-600">Paramètres spécifiques au rôle sélectionné</p>
                </div>

                <form method="POST" action="{{ route('supervisor.users.store') }}" @submit="submitForm">
                    @csrf
                    
                    <!-- Champs cachés pour les données du formulaire -->
                    <input type="hidden" name="role" :value="form.role">
                    <input type="hidden" name="name" :value="form.name">
                    <input type="hidden" name="email" :value="form.email">
                    <input type="hidden" name="phone" :value="form.phone">
                    <input type="hidden" name="password" :value="form.password">
                    <input type="hidden" name="shop_name" :value="form.shop_name">
                    <input type="hidden" name="address" :value="form.address">
                    <input type="hidden" name="delivery_price" :value="form.delivery_price">
                    <input type="hidden" name="return_price" :value="form.return_price">
                    <input type="hidden" name="vehicle_type" :value="form.vehicle_type">
                    <input type="hidden" name="vehicle_registration" :value="form.vehicle_registration">
                    <input type="hidden" name="is_transit_deliverer" :value="form.is_transit_deliverer ? '1' : '0'">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ⚡ Statut Initial *
                            </label>
                            <select x-model="form.account_status" name="account_status" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="ACTIVE">✅ Actif (peut se connecter immédiatement)</option>
                                <option value="PENDING">⏳ En attente (nécessite validation)</option>
                            </select>
                        </div>

                        <div x-show="needsDelegation()">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📍 Délégation <span x-show="isDelegationRequired()">*</span>
                            </label>
                            <select x-model="form.delegation_id" name="delegation_id" 
                                    :required="isDelegationRequired()"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                <option value="">Sélectionner une délégation</option>
                                @if(isset($delegations))
                                    @foreach($delegations as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="text-xs text-gray-500 mt-1" x-show="form.role === 'CLIENT'">Délégation principale du client</p>
                            <p class="text-xs text-gray-500 mt-1" x-show="form.role === 'DELIVERER' && form.is_transit_deliverer === false">Délégation de livraison du livreur local</p>
                            <p class="text-xs text-gray-500 mt-1" x-show="form.role === 'DELIVERER' && form.is_transit_deliverer === true">Les livreurs transit n'ont pas de délégation fixe</p>
                        </div>
                    </div>

                    <!-- Configuration spéciale pour Chef Dépôt -->
                    <div x-show="form.role === 'DEPOT_MANAGER'" class="mt-6 p-4 bg-orange-50 rounded-lg border border-orange-200">
                        <h3 class="text-lg font-medium text-orange-800 mb-4">
                            🏢 Gouvernorats assignés *
                            <span class="text-xs font-normal text-orange-600">(Sélectionnez au moins 1)</span>
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 bg-white rounded border border-orange-200">
                            @if(isset($gouvernorats))
                                @foreach($gouvernorats as $key => $name)
                                <label class="flex items-center p-2 hover:bg-orange-100 rounded cursor-pointer transition-colors">
                                    <input type="checkbox" name="assigned_gouvernorats[]" value="{{ $key }}"
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $name }}</span>
                                </label>
                                @endforeach
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-orange-600">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Le chef dépôt sera responsable des livreurs dans les gouvernorats sélectionnés
                        </p>
                    </div>

                    <!-- Résumé de création -->
                    <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-medium text-blue-800 mb-4">📋 Résumé de création</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><strong>Rôle:</strong> <span x-text="getRoleDisplay()"></span></div>
                            <div><strong>Nom:</strong> <span x-text="form.name"></span></div>
                            <div><strong>Email:</strong> <span x-text="form.email"></span></div>
                            <div><strong>Téléphone:</strong> <span x-text="form.phone"></span></div>
                            <div><strong>Statut:</strong> <span x-text="form.account_status === 'ACTIVE' ? '✅ Actif' : '⏳ En attente'"></span></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button @click="previousStep()" type="button"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Précédent
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                                :class="isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                                class="px-8 py-3 text-white rounded-lg transition-colors flex items-center">
                            <svg x-show="!isSubmitting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <svg x-show="isSubmitting" class="animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span x-text="isSubmitting ? 'Création...' : 'Créer l\'Utilisateur'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function userCreationWizard() {
    return {
        currentStep: 1,
        showPassword: false,
        isSubmitting: false,
        
        form: {
            role: '',
            name: '',
            email: '',
            phone: '',
            password: '',
            shop_name: '',
            address: '',
            delivery_price: '',
            return_price: '',
            vehicle_type: '',
            vehicle_registration: '',
            is_transit_deliverer: null,
            account_status: 'ACTIVE',
            delegation_id: ''
        },

        selectAccountType(role) {
            this.form.role = role;
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

        canProceedToStep3() {
            return this.form.name && 
                   this.form.email && 
                   this.form.phone && 
                   this.form.password &&
                   this.form.password.length >= 8 &&
                   this.validateRoleSpecificFields();
        },

        validateRoleSpecificFields() {
            if (this.form.role === 'CLIENT') {
                return this.form.address && 
                       this.form.delivery_price && 
                       this.form.delivery_price > 0 &&
                       this.form.return_price !== '' && 
                       this.form.return_price >= 0;
            }
            if (this.form.role === 'DELIVERER') {
                return this.form.is_transit_deliverer !== null;
            }
            return true;
        },

        needsDelegation() {
            // Afficher le champ délégation pour clients et livreurs
            if (this.form.role === 'CLIENT') return true;
            if (this.form.role === 'DELIVERER') return true;
            return false;
        },

        isDelegationRequired() {
            // Délégation requise pour les clients
            if (this.form.role === 'CLIENT') return true;
            // Délégation requise pour les livreurs locaux seulement (pas pour TRANSIT)
            if (this.form.role === 'DELIVERER' && this.form.is_transit_deliverer === false) return true;
            return false;
        },

        getRoleDisplay() {
            const roles = {
                'CLIENT': '👤 Client',
                'DELIVERER': '🚚 Livreur',
                'COMMERCIAL': '💼 Commercial',
                'DEPOT_MANAGER': '🏢 Chef Dépôt',
                'SUPERVISOR': '👨‍💼 Superviseur'
            };
            return roles[this.form.role] || this.form.role;
        },

        submitForm(event) {
            // Validation spéciale pour DEPOT_MANAGER
            if (this.form.role === 'DEPOT_MANAGER') {
                const checkboxes = document.querySelectorAll('input[name="assigned_gouvernorats[]"]:checked');
                if (checkboxes.length === 0) {
                    event.preventDefault();
                    alert('⚠️ Veuillez sélectionner au moins un gouvernorat pour le chef dépôt');
                    this.isSubmitting = false;
                    return false;
                }
            }
            this.isSubmitting = true;
            // Le formulaire sera soumis normalement
        },

        init() {
            // Format du téléphone
            this.$watch('form.phone', (value) => {
                if (value) {
                    let cleaned = value.replace(/\D/g, '');
                    if (cleaned.length > 0 && !cleaned.startsWith('216')) {
                        if (cleaned.length === 8) {
                            cleaned = '216' + cleaned;
                        }
                    }
                    if (cleaned.length > 0) {
                        this.form.phone = '+' + cleaned;
                    }
                }
            });
        }
    }
}
</script>
@endsection