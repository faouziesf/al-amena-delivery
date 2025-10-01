@extends('layouts.client')

@section('title', 'Demande de Retrait')

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- En-tête -->
    <div class="mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Demande de Retrait</h1>
                <p class="text-gray-600 text-sm md:text-base">Retirez des fonds de votre portefeuille</p>
            </div>
            <a href="{{ route('client.wallet.index') }}"
               class="inline-flex items-center text-purple-600 hover:text-purple-800 transition-colors text-sm md:text-base">
                <svg class="w-4 h-4 md:w-6 md:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

        <!-- Formulaire principal -->
        <div class="lg:col-span-2">
            <form action="{{ route('client.wallet.store.withdrawal') }}" method="POST"
                  class="space-y-8" x-data="withdrawalForm()">
                @csrf

                <!-- Informations de base -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-4 md:mb-6">Montant à retirer</h3>

                    <div class="space-y-4 md:space-y-6">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Montant (DT) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number"
                                       id="amount"
                                       name="amount"
                                       step="0.001"
                                       min="1"
                                       max="{{ $availableBalance }}"
                                       value="{{ old('amount') }}"
                                       x-model="amount"
                                       @input="calculateFees()"
                                       required
                                       class="w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base @error('amount') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">DT</span>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:justify-between text-xs md:text-sm mt-2 space-y-1 sm:space-y-0">
                                <span class="text-gray-500">Minimum: 1.000 DT</span>
                                <span class="text-gray-500">Maximum: {{ number_format($availableBalance, 3) }} DT</span>
                            </div>
                            @error('amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Motif du retrait
                            </label>
                            <textarea id="reason"
                                      name="reason"
                                      rows="3"
                                      placeholder="Optionnel - indiquez le motif de votre retrait"
                                      class="w-full px-3 md:px-4 py-2 md:py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Méthode de retrait -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Méthode de retrait</h3>

                    <div class="space-y-4">
                        <!-- Virement bancaire -->
                        <label class="block">
                            <div class="flex items-center">
                                <input type="radio"
                                       x-model="method"
                                       value="BANK_TRANSFER"
                                       name="preferred_method"
                                       class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">Virement bancaire</p>
                                            <p class="text-sm text-gray-600">Délai: 24-48h ouvrables</p>
                                        </div>
                                        <div class="text-green-600 font-medium">
                                            Gratuit
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Livraison espèces -->
                        <label class="block">
                            <div class="flex items-center">
                                <input type="radio"
                                       x-model="method"
                                       value="CASH_DELIVERY"
                                       name="preferred_method"
                                       class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">Livraison en espèces</p>
                                            <p class="text-sm text-gray-600">Délai: 2-4h</p>
                                        </div>
                                        <div class="text-orange-600 font-medium">
                                            <span x-text="deliveryFee"></span> DT
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Configuration virement bancaire -->
                <div x-show="method === 'BANK_TRANSFER'"
                     x-transition
                     class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations bancaires</h3>

                    @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                        <!-- Comptes existants -->
                        <div class="space-y-4 mb-6">
                            <label class="block text-sm font-medium text-gray-700">Choisir un compte enregistré</label>

                            @foreach($bankAccounts as $account)
                                <label class="block">
                                    <div class="flex items-center p-4 border rounded-xl hover:bg-gray-50 cursor-pointer"
                                         :class="selectedBankAccount === {{ $account->id }} ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                        <input type="radio"
                                               x-model="selectedBankAccount"
                                               value="{{ $account->id }}"
                                               name="bank_account_id"
                                               class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $account->bank_name }}</p>
                                                    <p class="text-sm text-gray-600">{{ $account->account_holder_name }}</p>
                                                    <p class="text-sm text-gray-500 font-mono">{{ $account->masked_iban }}</p>
                                                </div>
                                                @if($account->is_default)
                                                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                                                        Par défaut
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach

                            <!-- Option nouveau compte -->
                            <label class="block">
                                <div class="flex items-center p-4 border rounded-xl hover:bg-gray-50 cursor-pointer"
                                     :class="selectedBankAccount === 'new' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio"
                                           x-model="selectedBankAccount"
                                           value="new"
                                           class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                    <div class="ml-4 flex-1">
                                        <p class="font-medium text-gray-900">+ Nouveau compte bancaire</p>
                                        <p class="text-sm text-gray-600">Ajouter un nouveau compte</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endif

                    <!-- Formulaire nouveau compte -->
                    <div x-show="selectedBankAccount === 'new' || {{ (isset($bankAccounts) && $bankAccounts->count() === 0) ? 'true' : 'false' }}"
                         x-transition
                         class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom de la banque <span class="text-red-500">*</span>
                                </label>
                                <select id="bank_name"
                                        name="bank_name"
                                        x-model="newBankAccount.bank_name"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Sélectionner une banque</option>

                                    <!-- Banques commerciales publiques -->
                                    <optgroup label="🏛️ Banques Publiques">
                                        <option value="Banque de Tunisie">Banque de Tunisie (BT)</option>
                                        <option value="Banque Nationale Agricole">Banque Nationale Agricole (BNA)</option>
                                        <option value="Société Tunisienne de Banque">Société Tunisienne de Banque (STB)</option>
                                    </optgroup>

                                    <!-- Banques privées -->
                                    <optgroup label="🏦 Banques Privées">
                                        <option value="Amen Bank">Amen Bank</option>
                                        <option value="Arab Tunisian Bank">Arab Tunisian Bank (ATB)</option>
                                        <option value="Attijari Bank">Attijari Bank</option>
                                        <option value="Banque Internationale Arabe de Tunisie">Banque Internationale Arabe de Tunisie (BIAT)</option>
                                        <option value="Banque de l'Habitat">Banque de l'Habitat (BH Bank)</option>
                                        <option value="Union Bancaire pour le Commerce et l'Industrie">Union Bancaire pour le Commerce et l'Industrie (UBCI)</option>
                                        <option value="Union Internationale de Banques">Union Internationale de Banques (UIB)</option>
                                        <option value="Banque Zitouna">Banque Zitouna</option>
                                        <option value="Tunisie Leasing Bank">Tunisie Leasing Bank (TLB)</option>
                                        <option value="Banque Franco-Tunisienne">Banque Franco-Tunisienne (BFT)</option>
                                        <option value="North Africa International Bank">North Africa International Bank (NAIB)</option>
                                        <option value="Citybank Tunisia">Citybank Tunisia</option>
                                        <option value="Al Baraka Bank Tunisia">Al Baraka Bank Tunisia</option>
                                        <option value="Banque Tuniso-Libyenne">Banque Tuniso-Libyenne (BTL)</option>
                                        <option value="Banque Tuniso-Koweïtienne">Banque Tuniso-Koweïtienne (BTK)</option>
                                        <option value="Wifak International Bank">Wifak International Bank</option>
                                        <option value="Banque de Coopération du Maghreb Arabe">Banque de Coopération du Maghreb Arabe (BCMA)</option>
                                    </optgroup>

                                    <!-- Banques étrangères -->
                                    <optgroup label="🌍 Banques Étrangères">
                                        <option value="Qatar National Bank Tunisia">Qatar National Bank Tunisia (QNB)</option>
                                        <option value="Crédit du Maroc Tunisia">Crédit du Maroc Tunisia</option>
                                        <option value="First National Bank Tunisia">First National Bank Tunisia (FNB)</option>
                                    </optgroup>

                                    <!-- Institutions spécialisées -->
                                    <optgroup label="🏢 Institutions Spécialisées">
                                        <option value="Banque Tunisienne de Solidarité">Banque Tunisienne de Solidarité (BTS)</option>
                                        <option value="Tunisian Foreign Investment Bank">Tunisian Foreign Investment Bank (TFIB)</option>
                                        <option value="Banque d'Affaires de Tunisie">Banque d'Affaires de Tunisie (BAT)</option>
                                    </optgroup>

                                    <option value="Autre">🏦 Autre banque</option>
                                </select>
                            </div>

                            <div>
                                <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom du titulaire <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="account_holder_name"
                                       name="account_holder_name"
                                       x-model="newBankAccount.account_holder_name"
                                       placeholder="Nom complet du titulaire du compte"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">
                                IBAN <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="iban"
                                   name="iban"
                                   x-model="newBankAccount.iban"
                                   @input="validateIban()"
                                   placeholder="TN59 XXXX XXXX XXXX XXXX XXXX XX"
                                   maxlength="29"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono">
                            <div x-show="ibanValidation.message" class="mt-2">
                                <p :class="ibanValidation.valid ? 'text-green-600' : 'text-red-600'"
                                   class="text-sm"
                                   x-text="ibanValidation.message"></p>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       x-model="newBankAccount.save_account"
                                       name="save_account"
                                       class="w-4 h-4 text-purple-600 focus:ring-purple-500 rounded">
                                <span class="ml-3 text-sm text-gray-700">Enregistrer ce compte pour les prochains retraits</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Configuration livraison espèces -->
                <div x-show="method === 'CASH_DELIVERY'"
                     x-transition
                     class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Adresse de livraison</h3>

                    @if(isset($pickupAddresses) && $pickupAddresses->count() > 0)
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Choisir une adresse enregistrée</label>

                            @foreach($pickupAddresses as $address)
                                <label class="block">
                                    <div class="flex items-center p-4 border rounded-xl hover:bg-gray-50 cursor-pointer"
                                         :class="selectedDeliveryAddress === {{ $address->id }} ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">
                                        <input type="radio"
                                               x-model="selectedDeliveryAddress"
                                               value="{{ $address->id }}"
                                               name="delivery_address_id"
                                               class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $address->label ?? 'Adresse de pickup' }}</p>
                                                    <p class="text-sm text-gray-600">{{ $address->contact_name }}</p>
                                                    <p class="text-sm text-gray-500">{{ $address->address }}, {{ $address->delegation }}</p>
                                                    <p class="text-sm text-gray-500">Tél: {{ $address->phone }}</p>
                                                </div>
                                                @if($address->is_default)
                                                    <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                                        Par défaut
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-gray-500 mb-4">Aucune adresse de pickup enregistrée</p>
                            <a href="{{ route('client.pickup-addresses.create') }}"
                               class="text-orange-600 hover:text-orange-700 font-medium">
                                Ajouter une adresse de pickup →
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Résumé et validation -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Résumé de la demande</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Montant demandé</span>
                            <span class="font-medium" x-text="formatAmount(amount) + ' DT'">0.000 DT</span>
                        </div>

                        <div x-show="method === 'CASH_DELIVERY'" class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Frais de livraison</span>
                            <span class="font-medium text-orange-600" x-text="deliveryFee + ' DT'">2.000 DT</span>
                        </div>

                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Montant à recevoir</span>
                                <span class="text-xl font-bold text-green-600" x-text="formatAmount(getFinalAmount()) + ' DT'">0.000 DT</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit"
                                :disabled="!isFormValid()"
                                :class="isFormValid()
                                       ? 'bg-purple-600 hover:bg-purple-700 text-white'
                                       : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                class="w-full py-3 px-6 rounded-xl font-medium transition-colors">
                            Confirmer la demande de retrait
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

            <!-- Solde disponible -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Votre portefeuille</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Solde total</span>
                        <span class="font-medium">{{ number_format($user->wallet->balance, 3) }} DT</span>
                    </div>
                    @if(($user->wallet->frozen_amount ?? 0) > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Montant gelé</span>
                        <span class="font-medium text-orange-600">{{ number_format($user->wallet->frozen_amount, 3) }} DT</span>
                    </div>
                    @endif
                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900">Disponible pour retrait</span>
                            <span class="font-bold text-green-600">{{ number_format($availableBalance, 3) }} DT</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informations
                </h3>
                <div class="space-y-3 text-sm text-blue-800">
                    <p><strong>Virement bancaire :</strong> Gratuit, 24-48h ouvrables</p>
                    <p><strong>Livraison espèces :</strong> 2.000 DT de frais, 2-4h</p>
                    <p><strong>Horaires :</strong> Demandes traitées de 8h à 18h</p>
                    <p><strong>Montant minimum :</strong> 1.000 DT</p>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
function withdrawalForm() {
    return {
        amount: '',
        method: '',
        selectedBankAccount: @json(isset($bankAccounts) && $bankAccounts->where('is_default', true)->first() ? $bankAccounts->where('is_default', true)->first()->id : (isset($bankAccounts) && $bankAccounts->first() ? $bankAccounts->first()->id : 'new')),
        selectedDeliveryAddress: @json(isset($pickupAddresses) && $pickupAddresses->where('is_default', true)->first() ? $pickupAddresses->where('is_default', true)->first()->id : (isset($pickupAddresses) && $pickupAddresses->first() ? $pickupAddresses->first()->id : null)),
        deliveryFee: '2.000',

        newBankAccount: {
            bank_name: '',
            account_holder_name: '',
            iban: '',
            save_account: true
        },

        ibanValidation: {
            valid: false,
            message: ''
        },

        formatAmount(amount) {
            return parseFloat(amount || 0).toFixed(3);
        },

        getFinalAmount() {
            const baseAmount = parseFloat(this.amount || 0);
            if (this.method === 'CASH_DELIVERY') {
                return Math.max(0, baseAmount - parseFloat(this.deliveryFee));
            }
            return baseAmount;
        },

        calculateFees() {
            // Logique de calcul des frais si nécessaire
        },

        async validateIban() {
            const iban = this.newBankAccount.iban.replace(/\s/g, '').toUpperCase();

            if (!iban) {
                this.ibanValidation = { valid: false, message: '' };
                return;
            }

            // Validation basique côté client
            if (!/^TN\d{22}$/.test(iban)) {
                this.ibanValidation = { valid: false, message: 'Format invalide. Attendu: TN + 22 chiffres' };
                return;
            }

            this.ibanValidation = { valid: true, message: 'Format IBAN valide' };

            // Format avec espaces pour affichage
            this.newBankAccount.iban = iban.replace(/(.{4})/g, '$1 ').trim();
        },

        isFormValid() {
            if (!this.amount || parseFloat(this.amount) <= 0) return false;
            if (!this.method) return false;

            if (this.method === 'BANK_TRANSFER') {
                if (this.selectedBankAccount === 'new') {
                    return this.newBankAccount.bank_name &&
                           this.newBankAccount.account_holder_name &&
                           this.newBankAccount.iban &&
                           this.ibanValidation.valid;
                }
                return this.selectedBankAccount;
            }

            if (this.method === 'CASH_DELIVERY') {
                return this.selectedDeliveryAddress;
            }

            return false;
        },

        init() {
            // Initialisation
            @if(!isset($bankAccounts) || $bankAccounts->count() === 0)
                this.selectedBankAccount = 'new';
            @endif
        }
    }
}
</script>
@endpush
@endsection