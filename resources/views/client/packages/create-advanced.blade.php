@extends('layouts.client')

@section('title', 'Nouveau Colis')
@section('page-title', 'Création Rapide de Colis')
@section('page-description', 'Saisie optimisée pour rapidité')

@section('header-actions')
<div class="flex items-center space-x-2">
    <div class="text-xs text-gray-400 hidden md:block">
        <kbd class="px-1.5 py-0.5 text-xs bg-gray-50 border border-gray-200 rounded">Ctrl</kbd> + 
        <kbd class="px-1.5 py-0.5 text-xs bg-gray-50 border border-gray-200 rounded">Enter</kbd> = Créer
    </div>
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-500 text-sm font-medium rounded-lg transition-colors duration-200">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Liste
    </a>
</div>
@endsection

@section('content')
<div x-data="fastPackageCreate()" class="max-w-4xl mx-auto font-sans">
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-50 border-l-2 border-green-400 text-green-600 p-2 mb-3 rounded-r-lg">
        <div class="flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    <!-- Header Compact avec Solde -->
    <div class="bg-gradient-to-r from-indigo-500 to-teal-500 rounded-lg p-3 text-white mb-3 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <span class="text-xs opacity-80">Solde</span>
                    <div class="text-lg font-semibold">{{ number_format($user->wallet->balance, 3) }} DT</div>
                </div>
                <div class="text-xs">
                    <div>Livraison: {{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</div>
                    <div>Retour: {{ number_format($user->clientProfile->offer_return_price, 3) }} DT</div>
                </div>
            </div>
            <div class="text-right text-xs">
                <div>Colis créés aujourd'hui: <span class="font-semibold">{{ $todayPackagesCount ?? 0 }}</span></div>
                <div class="opacity-70">Gain potentiel: {{ number_format(($todayRevenue ?? 0), 3) }} DT</div>
            </div>
        </div>
    </div>

    <!-- Formulaire Ultra-Rapide -->
    <form action="{{ route('client.packages.store') }}" method="POST" @submit="submitForm" 
          @keydown.ctrl.enter="$event.target.closest('form').submit()"
          class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        <input type="hidden" name="continue_creating" value="1">

        <!-- Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-3">
            <!-- Pickup Section (Left) -->
            <div class="space-y-3">
                <div class="bg-indigo-50 px-3 py-2 rounded-t-md">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-indigo-800 text-sm">📦 Pickup</h3>
                        @if(isset($supplierAddresses) && $supplierAddresses->count() > 0)
                        <select @change="loadSupplier($event.target.value)" 
                                class="text-xs bg-transparent focus:ring-0 text-indigo-600 font-medium">
                            <option value="">🔄 Adresses sauvées</option>
                            @foreach($supplierAddresses as $addr)
                                <option value="{{ $addr->id }}" 
                                        data-name="{{ $addr->name }}" 
                                        data-phone="{{ $addr->phone }}" 
                                        data-address="{{ $addr->address }}" 
                                        data-delegation="{{ $addr->delegation_id }}">
                                    {{ $addr->label ?: $addr->name }} ({{ $addr->delegation->name }})
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <!-- Nom Fournisseur -->
                    <div>
                        <input type="text" name="supplier_name" required
                               value="{{ old('supplier_name', $lastSupplierData['name'] ?? '') }}"
                               x-model="form.supplier_name" x-ref="firstField"
                               placeholder="Nom fournisseur *"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-colors duration-200 @error('supplier_name') border-red-400 @enderror">
                        @error('supplier_name')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Téléphone Fournisseur -->
                    <div>
                        <input type="tel" name="supplier_phone" required
                               value="{{ old('supplier_phone', $lastSupplierData['phone'] ?? '') }}"
                               x-model="form.supplier_phone"
                               placeholder="Téléphone fournisseur *"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-colors duration-200 @error('supplier_phone') border-red-400 @enderror">
                        @error('supplier_phone')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Délégation Pickup -->
                    <div>
                        <select name="pickup_delegation_id" required x-model="form.pickup_delegation_id" @change="calculateFees()"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-colors duration-200 @error('pickup_delegation_id') border-red-400 @enderror">
                            <option value="">Délégation pickup *</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}" 
                                        {{ old('pickup_delegation_id', $lastSupplierData['pickup_delegation_id'] ?? '') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pickup_delegation_id')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Save Supplier -->
                    <div class="flex items-center">
                        <label class="flex items-center text-xs text-gray-500">
                            <input type="checkbox" name="save_supplier_address" value="1" x-model="form.save_supplier"
                                   class="mr-1 h-3 w-3 text-indigo-500 focus:ring-indigo-400 border-gray-200 rounded">
                            💾 Sauver
                        </label>
                    </div>
                </div>

                <!-- Adresse Pickup -->
                <div>
                    <textarea name="pickup_address" required rows="3" x-model="form.pickup_address"
                              placeholder="Adresse pickup complète *"
                              class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-colors duration-200 @error('pickup_address') border-red-400 @enderror">{{ old('pickup_address', $lastSupplierData['pickup_address'] ?? '') }}</textarea>
                    @error('pickup_address')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Delivery Section (Right) -->
            <div class="space-y-3">
                <div class="bg-teal-50 px-3 py-2 rounded-t-md">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-teal-800 text-sm">🎯 Livraison</h3>
                        @if(isset($clientAddresses) && $clientAddresses->count() > 0)
                        <select @change="loadClient($event.target.value)"
                                class="text-xs bg-transparent focus:ring-0 text-teal-600 font-medium">
                            <option value="">🔄 Clients habituels</option>
                            @foreach($clientAddresses as $addr)
                                <option value="{{ $addr->id }}" 
                                        data-name="{{ $addr->name }}" 
                                        data-phone="{{ $addr->phone }}" 
                                        data-address="{{ $addr->address }}" 
                                        data-delegation="{{ $addr->delegation_id }}">
                                    {{ $addr->label ?: $addr->name }} ({{ $addr->delegation->name }})
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <!-- Nom Client -->
                    <div>
                        <input type="text" name="recipient_name" required
                               value="{{ old('recipient_name') }}" x-model="form.recipient_name"
                               placeholder="Nom client *"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-400 focus:border-teal-400 transition-colors duration-200 @error('recipient_name') border-red-400 @enderror">
                        @error('recipient_name')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Téléphone Client -->
                    <div>
                        <input type="tel" name="recipient_phone" required
                               value="{{ old('recipient_phone') }}" x-model="form.recipient_phone"
                               placeholder="Téléphone client *"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-400 focus:border-teal-400 transition-colors duration-200 @error('recipient_phone') border-red-400 @enderror">
                        @error('recipient_phone')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Délégation Destination -->
                    <div>
                        <select name="delegation_to" required x-model="form.delegation_to" @change="calculateFees()"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-400 focus:border-teal-400 transition-colors duration-200 @error('delegation_to') border-red-400 @enderror">
                            <option value="">Destination *</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}" {{ old('delegation_to') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('delegation_to')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                    </div>

                    <!-- Save Client -->
                    <div class="flex items-center">
                        <label class="flex items-center text-xs text-gray-500">
                            <input type="checkbox" name="save_client_address" value="1" x-model="form.save_client"
                                   class="mr-1 h-3 w-3 text-teal-500 focus:ring-teal-400 border-gray-200 rounded">
                            💾 Sauver
                        </label>
                    </div>
                </div>

                <!-- Adresse Client -->
                <div>
                    <textarea name="recipient_address" required rows="3" x-model="form.recipient_address"
                              placeholder="Adresse livraison complète *"
                              class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-400 focus:border-teal-400 transition-colors duration-200 @error('recipient_address') border-red-400 @enderror">{{ old('recipient_address') }}</textarea>
                    @error('recipient_address')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Zone Colis -->
        <div class="bg-purple-50 px-3 py-2 border-t border-gray-100">
            <h3 class="font-medium text-purple-800 text-sm">📋 Colis</h3>
        </div>

        <div class="p-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
            <!-- Description -->
            <div>
                <input type="text" name="content_description" required
                       value="{{ old('content_description') }}" x-model="form.content_description"
                       placeholder="Description contenu *"
                       class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-colors duration-200 @error('content_description') border-red-400 @enderror">
                @error('content_description')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
            </div>

            <!-- COD Amount -->
            <div>
                <div class="relative">
                    <input type="number" name="cod_amount" required
                           value="{{ old('cod_amount', '0') }}" x-model="form.cod_amount"
                           min="0" max="9999.999" step="0.001" @input="calculateFees()"
                           placeholder="Montant COD *"
                           class="w-full px-2 py-1.5 pr-7 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-colors duration-200 @error('cod_amount') border-red-400 @enderror">
                    <span class="absolute right-2 top-1.5 text-xs text-gray-400">DT</span>
                </div>
                @error('cod_amount')<p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>@enderror
            </div>

            <!-- Options -->
            <div class="flex items-center space-x-3">
                <label class="flex items-center text-xs text-gray-500">
                    <input type="checkbox" name="is_fragile" value="1" x-model="form.fragile"
                           class="mr-1 h-3 w-3 text-orange-500 focus:ring-orange-400 border-gray-200 rounded">
                    🔸 Fragile
                </label>
                <label class="flex items-center text-xs text-gray-500">
                    <input type="checkbox" name="requires_signature" value="1" x-model="form.signature"
                           class="mr-1 h-3 w-3 text-orange-500 focus:ring-orange-400 border-gray-200 rounded">
                    ✍️ Signature
                </label>
            </div>
        </div>

        <!-- Notes -->
        <div class="px-3 pb-3">
            <textarea name="notes" rows="2" x-model="form.notes"
                      placeholder="Notes (optionnel)"
                      class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-colors duration-200"></textarea>
        </div>

        <!-- Footer avec résumé et actions -->
        <div class="bg-gray-50 px-3 py-2 border-t border-gray-100 flex items-center justify-between">
            <!-- Résumé rapide -->
            <div class="flex items-center space-x-4 text-xs" x-show="form.cod_amount > 0">
                <div>
                    <span class="text-gray-500">COD:</span> 
                    <span class="font-medium" x-text="formatCurrency(form.cod_amount)"></span>
                </div>
                <div>
                    <span class="text-gray-500">Escrow:</span> 
                    <span class="font-medium text-indigo-500" x-text="formatCurrency(escrowAmount)"></span>
                </div>
                <div>
                    <span class="text-gray-500">Solde après:</span> 
                    <span class="font-medium" :class="remainingBalance >= 0 ? 'text-green-500' : 'text-red-500'"
                          x-text="formatCurrency(remainingBalance)"></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2">
                <button type="button" @click="resetClient()" 
                        class="px-3 py-1.5 text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-lg transition-all duration-200 hover:scale-105">
                    🔄 Nouveau client
                </button>
                
                <button type="submit" 
                        :disabled="!canCreate"
                        :class="canCreate ? 
                            'bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white' : 
                            'bg-gray-200 text-gray-400 cursor-not-allowed'"
                        class="px-4 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105 shadow-sm">
                    <span x-show="!submitting">🚀 Créer & Continuer</span>
                    <span x-show="submitting">⏳ Création...</span>
                </button>
            </div>
        </div>

        <!-- Champs cachés pour les options avancées -->
        <input type="hidden" name="package_weight" x-model="form.package_weight">
        <input type="hidden" name="package_value" x-model="form.package_value">
        <input type="hidden" name="package_length" x-model="form.package_length">
        <input type="hidden" name="package_width" x-model="form.package_width">
        <input type="hidden" name="package_height" x-model="form.package_height">
        <input type="hidden" name="special_instructions" x-model="form.special_instructions">
        <input type="hidden" name="supplier_address_label" x-model="form.supplier_label">
        <input type="hidden" name="client_address_label" x-model="form.client_label">
    </form>

    <!-- Raccourcis clavier -->
    <div class="mt-3 bg-indigo-50 border border-indigo-100 rounded-lg p-2">
        <div class="text-xs text-indigo-700">
            <strong>⌨️ Raccourcis:</strong> 
            <kbd class="px-1.5 py-0.5 text-xs bg-white border border-gray-200 rounded mx-0.5">Ctrl+Enter</kbd> Créer
            <kbd class="px-1.5 py-0.5 text-xs bg-white border border-gray-200 rounded mx-0.5">Ctrl+R</kbd> Nouveau client
            <kbd class="px-1.5 py-0.5 text-xs bg-white border border-gray-200 rounded mx-0.5">Tab</kbd> Champ suivant
        </div>
    </div>
</div>

@push('scripts')
<script>
function fastPackageCreate() {
    return {
        form: {
            supplier_name: '{{ old("supplier_name", $lastSupplierData["name"] ?? "") }}',
            supplier_phone: '{{ old("supplier_phone", $lastSupplierData["phone"] ?? "") }}',
            pickup_delegation_id: '{{ old("pickup_delegation_id", $lastSupplierData["pickup_delegation_id"] ?? "") }}',
            pickup_address: `{{ old("pickup_address", $lastSupplierData["pickup_address"] ?? "") }}`,
            save_supplier: false,
            supplier_label: '',
            
            delegation_to: '{{ old("delegation_to") }}',
            recipient_name: '{{ old("recipient_name") }}',
            recipient_phone: '{{ old("recipient_phone") }}',
            recipient_address: `{{ old("recipient_address") }}`,
            save_client: false,
            client_label: '',
            
            content_description: '{{ old("content_description") }}',
            cod_amount: {{ old('cod_amount', '0') }},
            notes: `{{ old("notes") }}`,
            fragile: false,
            signature: false,
            
            // Champs cachés
            package_weight: 0,
            package_value: 0,
            package_length: 0,
            package_width: 0,
            package_height: 0,
            special_instructions: ''
        },
        
        submitting: false,
        walletBalance: {{ $user->wallet->balance }},
        deliveryFee: {{ $user->clientProfile->offer_delivery_price }},
        returnFee: {{ $user->clientProfile->offer_return_price }},
        
        get escrowAmount() {
            const cod = parseFloat(this.form.cod_amount) || 0;
            return cod >= this.deliveryFee ? this.returnFee : this.deliveryFee;
        },
        
        get remainingBalance() {
            return this.walletBalance - this.escrowAmount;
        },
        
        get canCreate() {
            return this.form.supplier_name && this.form.supplier_phone && 
                   this.form.pickup_delegation_id && this.form.pickup_address && 
                   this.form.delegation_to && this.form.pickup_delegation_id !== this.form.delegation_to &&
                   this.form.recipient_name && this.form.recipient_phone && 
                   this.form.recipient_address && this.form.content_description && 
                   this.form.cod_amount >= 0 && this.remainingBalance >= 0 && !this.submitting;
        },
        
        calculateFees() {
            // Auto-calculé via getters
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-TN', { 
                style: 'currency', 
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount || 0);
        },
        
        loadSupplier(id) {
            if (!id) return;
            const option = event.target.selectedOptions[0];
            this.form.supplier_name = option.dataset.name;
            this.form.supplier_phone = option.dataset.phone;
            this.form.pickup_address = option.dataset.address;
            this.form.pickup_delegation_id = option.dataset.delegation;
            
            // Auto-focus sur destinataire
            setTimeout(() => {
                document.querySelector('input[name="recipient_name"]')?.focus();
            }, 100);
        },
        
        loadClient(id) {
            if (!id) return;
            const option = event.target.selectedOptions[0];
            this.form.recipient_name = option.dataset.name;
            this.form.recipient_phone = option.dataset.phone;
            this.form.recipient_address = option.dataset.address;
            this.form.delegation_to = option.dataset.delegation;
            
            // Auto-focus sur description
            setTimeout(() => {
                document.querySelector('input[name="content_description"]')?.focus();
            }, 100);
        },
        
        resetClient() {
            this.form.delegation_to = '';
            this.form.recipient_name = '';
            this.form.recipient_phone = '';
            this.form.recipient_address = '';
            this.form.save_client = false;
            this.form.client_label = '';
            this.form.content_description = '';
            this.form.cod_amount = 0;
            this.form.notes = '';
            this.form.fragile = false;
            this.form.signature = false;
            
            // Focus sur nom client
            setTimeout(() => {
                document.querySelector('input[name="recipient_name"]')?.focus();
            }, 100);
        },
        
        submitForm(event) {
            if (!this.canCreate) {
                event.preventDefault();
                alert('Veuillez corriger les erreurs du formulaire');
                return false;
            }
            
            if (this.remainingBalance < 0) {
                event.preventDefault();
                alert('Solde insuffisant pour créer ce colis');
                return false;
            }
            
            this.submitting = true;
            return true;
        },
        
        init() {
            // Auto-focus si fournisseur pré-rempli
            if (this.form.supplier_name) {
                setTimeout(() => {
                    document.querySelector('input[name="recipient_name"]')?.focus();
                }, 300);
            } else {
                setTimeout(() => {
                    this.$refs.firstField?.focus();
                }, 300);
            }
            
            // Raccourcis clavier
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    this.resetClient();
                }
            });
        }
    }
}
</script>
@endpush
@endsection