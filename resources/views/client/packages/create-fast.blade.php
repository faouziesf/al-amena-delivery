@extends('layouts.client')

@section('title', 'Nouveau Colis')
@section('page-title', 'Création Rapide de Colis')
@section('page-description', 'Saisie optimisée pour rapidité')

@section('header-actions')
<div class="flex items-center space-x-3">
    <div class="text-sm text-gray-600 hidden md:block">
        <kbd class="px-2 py-1 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
        <kbd class="px-2 py-1 text-xs bg-gray-100 border rounded">Enter</kbd> = Créer
    </div>
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Liste
    </a>
</div>
@endsection

@section('content')
<div x-data="fastPackageCreate()" class="max-w-4xl mx-auto">
    
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    <!-- Header Compact avec Solde -->
    <div class="bg-gradient-to-r from-blue-600 to-emerald-600 rounded-lg p-4 text-white mb-4 flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div>
                <span class="text-sm opacity-90">Solde</span>
                <div class="text-xl font-bold">{{ number_format($user->wallet->balance, 3) }} DT</div>
            </div>
            <div class="text-sm">
                <div>Livraison: {{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</div>
                <div>Retour: {{ number_format($user->clientProfile->offer_return_price, 3) }} DT</div>
            </div>
        </div>
        <div class="text-right text-sm">
            <div>Colis créés aujourd'hui: <span class="font-bold">{{ $todayPackagesCount ?? 0 }}</span></div>
            <div class="text-xs opacity-75">Gain potentiel: {{ number_format(($todayRevenue ?? 0), 3) }} DT</div>
        </div>
    </div>

    <!-- Formulaire Ultra-Rapide -->
    <form action="{{ route('client.packages.store') }}" method="POST" @submit="submitForm" 
          @keydown.ctrl.enter="$event.target.closest('form').submit()"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @csrf
        <input type="hidden" name="continue_creating" value="1">
        
        <!-- Zone Fournisseur -->
        <div class="bg-blue-50 px-4 py-3 border-b">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">📦 Pickup</h3>
                @if(isset($supplierAddresses) && $supplierAddresses->count() > 0)
                <select @change="loadSupplier($event.target.value)" 
                        class="text-sm border-0 bg-transparent focus:ring-0 text-blue-700 font-medium">
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

        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Nom Fournisseur -->
            <div>
                <input type="text" name="supplier_name" required
                       value="{{ old('supplier_name', $lastSupplierData['name'] ?? '') }}"
                       x-model="form.supplier_name" x-ref="firstField"
                       placeholder="Nom fournisseur *"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('supplier_name') border-red-500 @enderror">
                @error('supplier_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Téléphone Fournisseur -->
            <div>
                <input type="tel" name="supplier_phone" required
                       value="{{ old('supplier_phone', $lastSupplierData['phone'] ?? '') }}"
                       x-model="form.supplier_phone"
                       placeholder="Téléphone fournisseur *"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('supplier_phone') border-red-500 @enderror">
                @error('supplier_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Délégation Pickup -->
            <div>
                <select name="pickup_delegation_id" required x-model="form.pickup_delegation_id" @change="calculateFees()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pickup_delegation_id') border-red-500 @enderror">
                    <option value="">Délégation pickup *</option>
                    @foreach($delegations as $delegation)
                        <option value="{{ $delegation->id }}" 
                                {{ old('pickup_delegation_id', $lastSupplierData['pickup_delegation_id'] ?? '') == $delegation->id ? 'selected' : '' }}>
                            {{ $delegation->name }}
                        </option>
                    @endforeach
                </select>
                @error('pickup_delegation_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Save Supplier -->
            <div class="flex items-center justify-center">
                <label class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" name="save_supplier_address" value="1" x-model="form.save_supplier"
                           class="mr-1 h-3 w-3 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    💾 Sauver
                </label>
            </div>
        </div>

        <!-- Adresse Pickup -->
        <div class="px-4 pb-4">
            <textarea name="pickup_address" required rows="2" x-model="form.pickup_address"
                      placeholder="Adresse pickup complète *"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pickup_address') border-red-500 @enderror">{{ old('pickup_address', $lastSupplierData['pickup_address'] ?? '') }}</textarea>
            @error('pickup_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Zone Destinataire -->
        <div class="bg-emerald-50 px-4 py-3 border-b border-t">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">🎯 Livraison</h3>
                @if(isset($clientAddresses) && $clientAddresses->count() > 0)
                <select @change="loadClient($event.target.value)"
                        class="text-sm border-0 bg-transparent focus:ring-0 text-emerald-700 font-medium">
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

        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Nom Client -->
            <div>
                <input type="text" name="recipient_name" required
                       value="{{ old('recipient_name') }}" x-model="form.recipient_name"
                       placeholder="Nom client *"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('recipient_name') border-red-500 @enderror">
                @error('recipient_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Téléphone Client -->
            <div>
                <input type="tel" name="recipient_phone" required
                       value="{{ old('recipient_phone') }}" x-model="form.recipient_phone"
                       placeholder="Téléphone client *"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('recipient_phone') border-red-500 @enderror">
                @error('recipient_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Délégation Destination -->
            <div>
                <select name="delegation_to" required x-model="form.delegation_to" @change="calculateFees()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('delegation_to') border-red-500 @enderror">
                    <option value="">Destination *</option>
                    @foreach($delegations as $delegation)
                        <option value="{{ $delegation->id }}" {{ old('delegation_to') == $delegation->id ? 'selected' : '' }}>
                            {{ $delegation->name }}
                        </option>
                    @endforeach
                </select>
                @error('delegation_to')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Save Client -->
            <div class="flex items-center justify-center">
                <label class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" name="save_client_address" value="1" x-model="form.save_client"
                           class="mr-1 h-3 w-3 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                    💾 Sauver
                </label>
            </div>
        </div>

        <!-- Adresse Client -->
        <div class="px-4 pb-4">
            <textarea name="recipient_address" required rows="2" x-model="form.recipient_address"
                      placeholder="Adresse livraison complète *"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('recipient_address') border-red-500 @enderror">{{ old('recipient_address') }}</textarea>
            @error('recipient_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Zone Colis -->
        <div class="bg-purple-50 px-4 py-3 border-b border-t">
            <h3 class="font-semibold text-gray-900">📋 Colis</h3>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Description -->
            <div>
                <input type="text" name="content_description" required
                       value="{{ old('content_description') }}" x-model="form.content_description"
                       placeholder="Description contenu *"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('content_description') border-red-500 @enderror">
                @error('content_description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- COD Amount -->
            <div>
                <div class="relative">
                    <input type="number" name="cod_amount" required
                           value="{{ old('cod_amount', '0') }}" x-model="form.cod_amount"
                           min="0" max="9999.999" step="0.001" @input="calculateFees()"
                           placeholder="Montant COD *"
                           class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('cod_amount') border-red-500 @enderror">
                    <span class="absolute right-2 top-2 text-xs text-gray-500">DT</span>
                </div>
                @error('cod_amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Options -->
            <div class="flex items-center space-x-4">
                <label class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" name="is_fragile" value="1" x-model="form.fragile"
                           class="mr-1 h-3 w-3 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                    🔸 Fragile
                </label>
                <label class="flex items-center text-xs text-gray-600">
                    <input type="checkbox" name="requires_signature" value="1" x-model="form.signature"
                           class="mr-1 h-3 w-3 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                    ✍️ Signature
                </label>
            </div>
        </div>

        <!-- Notes -->
        <div class="px-4 pb-4">
            <textarea name="notes" rows="2" x-model="form.notes"
                      placeholder="Notes (optionnel)"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('notes') }}</textarea>
        </div>

        <!-- Footer avec résumé et actions -->
        <div class="bg-gray-50 px-4 py-4 border-t flex items-center justify-between">
            
            <!-- Résumé rapide -->
            <div class="flex items-center space-x-6 text-sm" x-show="form.cod_amount > 0">
                <div>
                    <span class="text-gray-600">COD:</span> 
                    <span class="font-semibold" x-text="formatCurrency(form.cod_amount)"></span>
                </div>
                <div>
                    <span class="text-gray-600">Escrow:</span> 
                    <span class="font-semibold text-blue-600" x-text="formatCurrency(escrowAmount)"></span>
                </div>
                <div>
                    <span class="text-gray-600">Solde après:</span> 
                    <span class="font-semibold" :class="remainingBalance >= 0 ? 'text-green-600' : 'text-red-600'"
                          x-text="formatCurrency(remainingBalance)"></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="resetClient()" 
                        class="px-4 py-2 text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg transition-colors">
                    🔄 Nouveau client
                </button>
                
                <button type="submit" 
                        :disabled="!canCreate"
                        :class="canCreate ? 
                            'bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white' : 
                            'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-6 py-2 font-medium rounded-lg transition-all duration-200 transform hover:scale-105">
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
    <div class="mt-4 bg-blue-100 border border-blue-200 rounded-lg p-3">
        <div class="text-sm text-blue-800">
            <strong>⌨️ Raccourcis:</strong> 
            <kbd class="px-2 py-1 text-xs bg-white border rounded mx-1">Ctrl+Enter</kbd> Créer colis
            <kbd class="px-2 py-1 text-xs bg-white border rounded mx-1">Ctrl+R</kbd> Nouveau client
            <kbd class="px-2 py-1 text-xs bg-white border rounded mx-1">Tab</kbd> Champ suivant
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