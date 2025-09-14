@extends('layouts.client')

@section('title', 'Demander un Retrait')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-purple-900">Demander un Retrait</h1>
            <p class="mt-1 text-sm text-purple-600">
                Retirez des fonds de votre wallet
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('client.wallet') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
                Retour au Wallet
            </a>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('client.wallet.store-withdrawal') }}" 
          method="POST" 
          x-data="withdrawalForm()" 
          @submit="onSubmit"
          class="max-w-3xl mx-auto space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Formulaire principal -->
            <div class="lg:col-span-2">
                
                <!-- Informations sur le Wallet -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <div class="h-8 w-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Votre Wallet</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-purple-50 p-4 rounded-xl">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-purple-700">Solde disponible</span>
                                <span class="text-xl font-bold text-purple-900">{{ number_format($user->wallet_balance, 3) }} DT</span>
                            </div>
                        </div>
                        
                        @if($user->wallet_pending > 0)
                            <div class="bg-orange-50 p-4 rounded-xl">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-orange-700">En attente</span>
                                    <span class="text-lg font-semibold text-orange-600">{{ number_format($user->wallet_pending, 3) }} DT</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulaire de retrait -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-purple-900">Détails du Retrait</h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Montant à retirer -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                Montant à retirer (DT) <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       x-model="form.amount"
                                       value="{{ old('amount') }}"
                                       step="0.001"
                                       min="1"
                                       max="{{ $user->wallet_balance }}"
                                       required
                                       @input="validateAmount"
                                       class="block w-full px-3 py-3 pr-16 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 text-lg font-medium">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-medium">DT</span>
                                </div>
                            </div>
                            
                            <!-- Validation en temps réel -->
                            <div class="mt-2 space-y-1">
                                <div x-show="form.amount > {{ $user->wallet_balance }}" class="text-red-600 text-sm">
                                    ❌ Le montant dépasse votre solde disponible
                                </div>
                                <div x-show="form.amount > 0 && form.amount <= {{ $user->wallet_balance }}" class="text-green-600 text-sm">
                                    ✅ Montant valide
                                </div>
                                <div class="text-gray-500 text-sm">
                                    Montant maximum: {{ number_format($user->wallet_balance, 3) }} DT
                                </div>
                            </div>
                            
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Montants rapides -->
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" 
                                        @click="setAmount({{ min(10, $user->wallet_balance) }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-purple-300 text-xs font-medium rounded-lg text-purple-700 bg-purple-50 hover:bg-purple-100">
                                    10 DT
                                </button>
                                <button type="button" 
                                        @click="setAmount({{ min(50, $user->wallet_balance) }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-purple-300 text-xs font-medium rounded-lg text-purple-700 bg-purple-50 hover:bg-purple-100">
                                    50 DT
                                </button>
                                <button type="button" 
                                        @click="setAmount({{ min(100, $user->wallet_balance) }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-purple-300 text-xs font-medium rounded-lg text-purple-700 bg-purple-50 hover:bg-purple-100">
                                    100 DT
                                </button>
                                <button type="button" 
                                        @click="setAmount({{ $user->wallet_balance }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-orange-300 text-xs font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100">
                                    Tout retirer ({{ number_format($user->wallet_balance, 3) }} DT)
                                </button>
                            </div>
                        </div>

                        <!-- Méthode de retrait -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Méthode de retrait <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="space-y-4">
                                <!-- Virement bancaire -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.method === 'BANK_TRANSFER' }">
                                        <input type="radio" 
                                               name="method" 
                                               value="BANK_TRANSFER" 
                                               x-model="form.method"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Virement bancaire</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Délai: 24-48h ouvrables</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Livraison en espèces -->
                                <div class="relative">
                                    <label class="flex items-center p-4 border border-gray-300 rounded-xl hover:border-purple-500 cursor-pointer transition-colors"
                                           :class="{ 'border-purple-500 bg-purple-50': form.method === 'CASH_DELIVERY' }">
                                        <input type="radio" 
                                               name="method" 
                                               value="CASH_DELIVERY" 
                                               x-model="form.method"
                                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.5 0H3c.621 0 1.125.504 1.125 1.125v.375M3.75 15h-.75v.75c0 .621.504 1.125 1.125 1.125h.75m0-1.5v.375c0 .621.504 1.125 1.125 1.125H6.75m-3 0H4.5c-.621 0-1.125-.504-1.125-1.125V15m0 0h-.75" />
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">Livraison en espèces</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Livré par notre équipe à votre adresse</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            @error('method')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Détails bancaires (si virement sélectionné) -->
                        <div x-show="form.method === 'BANK_TRANSFER'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95">
                            
                            <div class="border border-blue-200 rounded-xl p-4 bg-blue-50">
                                <h4 class="text-sm font-medium text-blue-900 mb-4">Informations Bancaires</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="bank_iban" class="block text-sm font-medium text-gray-700">
                                            IBAN / RIB <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="bank_iban" 
                                               id="bank_iban" 
                                               x-model="form.bank_iban"
                                               value="{{ old('bank_iban') }}"
                                               :required="form.method === 'BANK_TRANSFER'"
                                               placeholder="TN59 1000 6035 1835 9847 8831"
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        @error('bank_iban')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="bank_name" class="block text-sm font-medium text-gray-700">
                                            Nom de la banque <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="bank_name" 
                                               id="bank_name" 
                                               x-model="form.bank_name"
                                               value="{{ old('bank_name') }}"
                                               :required="form.method === 'BANK_TRANSFER'"
                                               placeholder="Ex: BIAT, STB, BNA..."
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        @error('bank_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="bank_beneficiary" class="block text-sm font-medium text-gray-700">
                                            Nom du bénéficiaire <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="bank_beneficiary" 
                                               id="bank_beneficiary" 
                                               x-model="form.bank_beneficiary"
                                               value="{{ old('bank_beneficiary', $user->name) }}"
                                               :required="form.method === 'BANK_TRANSFER'"
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        @error('bank_beneficiary')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations de livraison (si livraison espèces sélectionnée) -->
                        <div x-show="form.method === 'CASH_DELIVERY'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95">
                            
                            <div class="border border-green-200 rounded-xl p-4 bg-green-50">
                                <h4 class="text-sm font-medium text-green-900 mb-3">Adresse de Livraison</h4>
                                
                                <div class="space-y-3">
                                    <div class="bg-white p-3 rounded-lg border border-green-200">
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $user->phone }}</p>
                                        <p class="text-sm text-gray-600">{{ $user->address }}</p>
                                    </div>
                                    
                                    <p class="text-xs text-green-700">
                                        ℹ️ La livraison s'effectuera à l'adresse de votre compte. 
                                        Délai: 24-48h selon disponibilité de notre équipe.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Récapitulatif -->
            <div class="space-y-6">
                
                <!-- Récapitulatif -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Récapitulatif</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-purple-100">
                            <span class="text-sm text-gray-600">Montant demandé</span>
                            <span class="font-semibold text-purple-900" x-text="formatMoney(form.amount)"></span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-purple-100">
                            <span class="text-sm text-gray-600">Frais de traitement</span>
                            <span class="font-medium text-gray-700">0.000 DT</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium text-gray-900">Montant net à recevoir</span>
                            <span class="text-lg font-bold text-green-600" x-text="formatMoney(form.amount)"></span>
                        </div>
                        
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-purple-700">Solde après retrait:</span>
                                <span class="font-semibold text-purple-900" x-text="formatMoney({{ $user->wallet_balance }} - form.amount)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conditions -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">Conditions</h3>
                    
                    <div class="space-y-3 text-sm text-gray-600">
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Aucuns frais de traitement</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Virement: 24-48h ouvrables</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-purple-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Livraison espèces: selon disponibilité</span>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="h-4 w-4 text-yellow-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Montant minimum: 1.000 DT</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <button type="submit" 
                            :disabled="!canSubmit || loading"
                            :class="{ 'opacity-50 cursor-not-allowed': !canSubmit || loading }"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">Confirmer la Demande</span>
                        <span x-show="loading">Traitement en cours...</span>
                    </button>
                    
                    <a href="{{ route('client.wallet') }}" 
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
    function withdrawalForm() {
        return {
            loading: false,
            form: {
                amount: parseFloat('{{ old("amount", 0) }}') || 0,
                method: '{{ old("method", "") }}',
                bank_iban: '{{ old("bank_iban") }}',
                bank_name: '{{ old("bank_name") }}',
                bank_beneficiary: '{{ old("bank_beneficiary", $user->name) }}'
            },
            
            get canSubmit() {
                const validAmount = this.form.amount > 0 && this.form.amount <= {{ $user->wallet_balance }};
                const methodSelected = this.form.method !== '';
                
                if (this.form.method === 'BANK_TRANSFER') {
                    return validAmount && methodSelected && 
                           this.form.bank_iban && 
                           this.form.bank_name && 
                           this.form.bank_beneficiary && 
                           !this.loading;
                }
                
                return validAmount && methodSelected && !this.loading;
            },
            
            validateAmount() {
                const amount = parseFloat(this.form.amount);
                if (isNaN(amount) || amount <= 0) {
                    this.form.amount = 0;
                } else if (amount > {{ $user->wallet_balance }}) {
                    this.form.amount = {{ $user->wallet_balance }};
                }
            },
            
            setAmount(amount) {
                this.form.amount = Math.min(amount, {{ $user->wallet_balance }});
            },
            
            formatMoney(amount) {
                return new Intl.NumberFormat('fr-TN', {
                    minimumFractionDigits: 3,
                    maximumFractionDigits: 3
                }).format(amount || 0) + ' DT';
            },
            
            onSubmit() {
                this.loading = true;
            }
        }
    }
</script>
@endpush