@extends('layouts.client')

@section('title', 'Créer un Colis')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Créer un Nouveau Colis</h1>
            <p class="mt-1 text-sm text-purple-600">
                Remplissez les informations pour créer votre colis
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.packages') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Retour
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if(!$user->isActive() || !$user->clientProfile)
        <!-- Alerte compte non actif -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-xl mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-800">
                        <strong>Compte en attente de validation.</strong>
                        Votre compte doit être validé par notre équipe avant de pouvoir créer des colis.
                        Veuillez patienter ou contacter le support.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('client.packages.store') }}" 
          method="POST" 
          x-data="packageForm()" 
          @submit="onSubmit"
          class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Formulaire principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Informations Expéditeur (pré-remplies) -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Informations Expéditeur</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" 
                                   value="{{ $user->name }}" 
                                   disabled
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm bg-gray-50 text-gray-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" 
                                   value="{{ $user->phone }}" 
                                   disabled
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm bg-gray-50 text-gray-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Adresse</label>
                            <textarea disabled 
                                      rows="2"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm bg-gray-50 text-gray-500">{{ $user->address }}</textarea>
                        </div>
                    </div>

                    <!-- Délégation de départ -->
                    <div class="mt-6">
                        <label for="delegation_from" class="block text-sm font-medium text-gray-700">
                            Délégation de départ <span class="text-red-500">*</span>
                        </label>
                        <select name="delegation_from" 
                                id="delegation_from" 
                                x-model="form.delegation_from"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sélectionnez une délégation</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}">{{ $delegation->full_name }}</option>
                            @endforeach
                        </select>
                        @error('delegation_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Informations Destinataire -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Informations Destinataire</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="recipient_name" class="block text-sm font-medium text-gray-700">
                                Nom complet <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="recipient_name" 
                                   id="recipient_name" 
                                   x-model="form.recipient_name"
                                   value="{{ old('recipient_name') }}"
                                   required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            @error('recipient_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="recipient_phone" class="block text-sm font-medium text-gray-700">
                                Téléphone <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="recipient_phone" 
                                   id="recipient_phone" 
                                   x-model="form.recipient_phone"
                                   value="{{ old('recipient_phone') }}"
                                   required
                                   placeholder="Ex: +216 12 345 678"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            @error('recipient_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Délégation de destination -->
                        <div class="sm:col-span-2">
                            <label for="delegation_to" class="block text-sm font-medium text-gray-700">
                                Délégation de destination <span class="text-red-500">*</span>
                            </label>
                            <select name="delegation_to" 
                                    id="delegation_to" 
                                    x-model="form.delegation_to"
                                    required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Sélectionnez une délégation</option>
                                @foreach($delegations as $delegation)
                                    <option value="{{ $delegation->id }}">{{ $delegation->full_name }}</option>
                                @endforeach
                            </select>
                            @error('delegation_to')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="recipient_address" class="block text-sm font-medium text-gray-700">
                                Adresse complète <span class="text-red-500">*</span>
                            </label>
                            <textarea name="recipient_address" 
                                      id="recipient_address" 
                                      rows="3"
                                      x-model="form.recipient_address"
                                      required
                                      placeholder="Adresse complète avec détails (rue, numéro, étage, etc.)"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">{{ old('recipient_address') }}</textarea>
                            @error('recipient_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Détails du Colis -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Détails du Colis</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="content_description" class="block text-sm font-medium text-gray-700">
                                Description du contenu <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="content_description" 
                                   id="content_description" 
                                   x-model="form.content_description"
                                   value="{{ old('content_description') }}"
                                   required
                                   placeholder="Ex: Vêtements, Électronique, Documents..."
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            @error('content_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cod_amount" class="block text-sm font-medium text-gray-700">
                                Montant COD (DT) <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input type="number" 
                                       name="cod_amount" 
                                       id="cod_amount" 
                                       x-model="form.cod_amount"
                                       value="{{ old('cod_amount', 0) }}"
                                       step="0.001"
                                       min="0"
                                       max="9999.999"
                                       required
                                       @input="calculateCosts"
                                       class="block w-full px-3 py-2 pr-12 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">DT</span>
                                </div>
                            </div>
                            @error('cod_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Commentaires (optionnel)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3"
                                      x-model="form.notes"
                                      placeholder="Instructions spéciales, horaires préférés, etc."
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Résumé et Tarification -->
            <div class="space-y-6">
                
                <!-- Wallet Summary -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Mon Wallet</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Solde disponible</span>
                            <span class="font-semibold text-purple-900" x-text="formatMoney({{ $user->wallet_balance }})"></span>
                        </div>
                        
                        @if($user->wallet_pending > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">En attente</span>
                                <span class="font-medium text-orange-600">{{ number_format($user->wallet_pending, 3) }} DT</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Calcul des Coûts -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Calcul des Coûts</h3>
                    
                    <div class="space-y-4">
                        <!-- Tarifs client -->
                        <div class="bg-purple-50 p-4 rounded-xl">
                            <h4 class="font-medium text-purple-900 mb-2">Vos Tarifs</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-purple-700">Frais livraison:</span>
                                    <span class="font-medium">{{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-purple-700">Frais retour:</span>
                                    <span class="font-medium">{{ number_format($user->clientProfile->offer_return_price, 3) }} DT</span>
                                </div>
                            </div>
                        </div>

                        <!-- Calcul dynamique -->
                        <div x-show="form.cod_amount >= 0" class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-purple-100">
                                <span class="text-sm text-gray-600">Montant COD</span>
                                <span class="font-medium" x-text="formatMoney(form.cod_amount)"></span>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-purple-100">
                                <span class="text-sm text-gray-600">Déduction wallet</span>
                                <span class="font-medium text-red-600" x-text="'-' + formatMoney(calculations.escrowAmount)"></span>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-purple-100">
                                <span class="text-sm text-gray-600">Si livré, vous recevez</span>
                                <span class="font-medium text-green-600" x-text="'+' + formatMoney(calculations.deliveryCredit)"></span>
                            </div>

                            <!-- Warning si solde insuffisant -->
                            <div x-show="calculations.escrowAmount > {{ $user->wallet_balance }}" 
                                 class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-800">
                                            <strong>Solde insuffisant</strong><br>
                                            Il vous manque <span x-text="formatMoney(calculations.escrowAmount - {{ $user->wallet_balance }})"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button type="submit" 
                            :disabled="!canSubmit || loading"
                            :class="{ 'opacity-50 cursor-not-allowed': !canSubmit || loading }"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">Créer le Colis</span>
                        <span x-show="loading">Création en cours...</span>
                    </button>
                    
                    <a href="{{ route('client.packages') }}" 
                       class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function packageForm() {
        return {
            loading: false,
            form: {
                delegation_from: '{{ old("delegation_from") }}',
                delegation_to: '{{ old("delegation_to") }}',
                recipient_name: '{{ old("recipient_name") }}',
                recipient_phone: '{{ old("recipient_phone") }}',
                recipient_address: '{{ old("recipient_address") }}',
                content_description: '{{ old("content_description") }}',
                cod_amount: parseFloat('{{ old("cod_amount", 0) }}'),
                notes: '{{ old("notes") }}'
            },
            
            calculations: {
                escrowAmount: 0,
                deliveryCredit: 0
            },
            
            get canSubmit() {
                return !this.loading && 
                       this.form.delegation_from && 
                       this.form.delegation_to && 
                       this.form.recipient_name && 
                       this.form.recipient_phone && 
                       this.form.recipient_address && 
                       this.form.content_description && 
                       this.form.cod_amount >= 0 &&
                       this.calculations.escrowAmount <= {{ $user->wallet_balance }};
            },
            
            calculateCosts() {
                const codAmount = parseFloat(this.form.cod_amount) || 0;
                const deliveryFee = {{ $user->clientProfile->offer_delivery_price }};
                const returnFee = {{ $user->clientProfile->offer_return_price }};
                
                if (codAmount >= deliveryFee) {
                    // Cas 1: COD >= Frais livraison
                    this.calculations.escrowAmount = returnFee;
                    this.calculations.deliveryCredit = (codAmount + returnFee) - deliveryFee;
                } else {
                    // Cas 2: COD < Frais livraison
                    this.calculations.escrowAmount = deliveryFee;
                    this.calculations.deliveryCredit = codAmount;
                }
            },
            
            formatMoney(amount) {
                return new Intl.NumberFormat('fr-TN', {
                    minimumFractionDigits: 3,
                    maximumFractionDigits: 3
                }).format(amount || 0) + ' DT';
            },
            
            onSubmit() {
                this.loading = true;
            },
            
            init() {
                this.calculateCosts();
                this.$watch('form.cod_amount', () => this.calculateCosts());
            }
        }
    }
</script>
@endpush