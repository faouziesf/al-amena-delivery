@extends('layouts.client')

@section('title', 'Nouveau Colis')
@section('page-title', 'Créer un Nouveau Colis')
@section('page-description', 'Créez un envoi vers n\'importe quelle délégation')

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour à la liste
    </a>
</div>
@endsection

@section('content')
<div x-data="packageCreateData()" class="max-w-4xl mx-auto">
    
    <!-- Info Portefeuille -->
    <div class="bg-gradient-to-r from-blue-600 to-emerald-600 rounded-xl p-6 text-white mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold mb-1">Solde Portefeuille</h3>
                <p class="text-2xl font-bold">{{ number_format($user->wallet->balance, 3) }} DT</p>
                @if($user->wallet->pending_amount > 0)
                    <p class="text-sm text-blue-100">{{ number_format($user->wallet->pending_amount, 3) }} DT en attente</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-100">Frais de livraison</p>
                <p class="text-lg font-semibold">{{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</p>
                <p class="text-xs text-blue-100">Frais de retour: {{ number_format($user->clientProfile->offer_return_price, 3) }} DT</p>
            </div>
        </div>
    </div>

    <!-- Formulaire -->
    <form action="{{ route('client.packages.store') }}" method="POST" class="space-y-6" @submit="validateForm">
        @csrf
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- En-tête -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Informations de l'Envoi</h3>
                <p class="text-sm text-gray-600">Remplissez les détails de votre colis</p>
            </div>

            <div class="p-6 space-y-6">
                
                <!-- Itinéraire -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="delegation_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Délégation d'Origine *
                        </label>
                        <select name="delegation_from" id="delegation_from" required
                                x-model="formData.delegation_from" @change="calculateFees()"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('delegation_from') border-red-500 @enderror">
                            <option value="">Sélectionnez une délégation</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}" {{ old('delegation_from') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                    @if($delegation->zone)
                                        ({{ $delegation->zone }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('delegation_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delegation_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Délégation de Destination *
                        </label>
                        <select name="delegation_to" id="delegation_to" required
                                x-model="formData.delegation_to" @change="calculateFees()"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('delegation_to') border-red-500 @enderror">
                            <option value="">Sélectionnez une délégation</option>
                            @foreach($delegations as $delegation)
                                <option value="{{ $delegation->id }}" {{ old('delegation_to') == $delegation->id ? 'selected' : '' }}>
                                    {{ $delegation->name }}
                                    @if($delegation->zone)
                                        ({{ $delegation->zone }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('delegation_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Informations Destinataire -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Informations du Destinataire</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom Complet *
                            </label>
                            <input type="text" name="recipient_name" id="recipient_name" required
                                   value="{{ old('recipient_name') }}" x-model="formData.recipient_name"
                                   placeholder="Ex: Ahmed Ben Ali"
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('recipient_name') border-red-500 @enderror">
                            @error('recipient_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="recipient_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Numéro de Téléphone *
                            </label>
                            <input type="tel" name="recipient_phone" id="recipient_phone" required
                                   value="{{ old('recipient_phone') }}" x-model="formData.recipient_phone"
                                   placeholder="Ex: +216 12 345 678"
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('recipient_phone') border-red-500 @enderror">
                            @error('recipient_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="recipient_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse Complète *
                        </label>
                        <textarea name="recipient_address" id="recipient_address" required rows="3"
                                  x-model="formData.recipient_address"
                                  placeholder="Adresse complète avec tous les détails nécessaires..."
                                  class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('recipient_address') border-red-500 @enderror">{{ old('recipient_address') }}</textarea>
                        @error('recipient_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Détails du Colis -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">Détails du Colis</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="content_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description du Contenu *
                            </label>
                            <input type="text" name="content_description" id="content_description" required
                                   value="{{ old('content_description') }}" x-model="formData.content_description"
                                   placeholder="Ex: Vêtements, Électronique, Documents..."
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('content_description') border-red-500 @enderror">
                            @error('content_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cod_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Montant COD (Contre Remboursement) *
                            </label>
                            <div class="relative">
                                <input type="number" name="cod_amount" id="cod_amount" required
                                       value="{{ old('cod_amount', '0') }}" x-model="formData.cod_amount"
                                       min="0" max="9999.999" step="0.001" @input="calculateFees()"
                                       placeholder="0.000"
                                       class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cod_amount') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">DT</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Montant que le destinataire doit payer</p>
                            @error('cod_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes Spéciales (Optionnel)
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  x-model="formData.notes"
                                  placeholder="Instructions spéciales pour la livraison..."
                                  class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        <!-- Résumé des Frais -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="showFeesSummary">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé des Frais</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Montant COD:</span>
                    <span class="font-medium" x-text="formatCurrency(formData.cod_amount)"></span>
                </div>
                
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Frais de livraison:</span>
                    <span class="font-medium">{{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</span>
                </div>
                
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Frais de retour:</span>
                    <span class="font-medium">{{ number_format($user->clientProfile->offer_return_price, 3) }} DT</span>
                </div>
                
                <div class="flex justify-between items-center py-3 bg-blue-50 rounded-lg px-4" x-show="escrowAmount > 0">
                    <span class="font-medium text-blue-900">Montant à déduire de votre portefeuille:</span>
                    <span class="text-lg font-bold text-blue-900" x-text="formatCurrency(escrowAmount)"></span>
                </div>
                
                <div class="flex justify-between items-center py-3 rounded-lg px-4" 
                     :class="remainingBalance >= 0 ? 'bg-green-50' : 'bg-red-50'">
                    <span class="font-medium" :class="remainingBalance >= 0 ? 'text-green-900' : 'text-red-900'">
                        Solde restant après création:
                    </span>
                    <span class="text-lg font-bold" 
                          :class="remainingBalance >= 0 ? 'text-green-900' : 'text-red-900'"
                          x-text="formatCurrency(remainingBalance)"></span>
                </div>
                
                <div x-show="remainingBalance < 0" class="bg-red-100 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-red-700 font-medium">Solde insuffisant pour créer ce colis</span>
                    </div>
                    <p class="text-xs text-red-600 mt-1">Veuillez alimenter votre portefeuille ou réduire le montant COD</p>
                </div>
            </div>
        </div>

        <!-- Info mise à jour -->
        <div class="bg-blue-100 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Version simplifiée</p>
                    <p>Vous utilisez actuellement la version basique de création de colis. Pour accéder aux fonctionnalités avancées (données fournisseur, adresses sauvegardées, import CSV), contactez l'administrateur pour mettre à jour le système.</p>
                </div>
            </div>
        </div>

        <!-- Boutons d'Action -->
        <div class="flex items-center justify-between bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('client.packages.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                    Annuler
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                <button type="button" @click="resetForm()" 
                        class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réinitialiser
                </button>
                
                <button type="submit" 
                        :disabled="!canCreatePackage"
                        :class="canCreatePackage ? 
                            'bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white' : 
                            'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="inline-flex items-center px-8 py-3 font-medium rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Créer le Colis
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function packageCreateData() {
    return {
        formData: {
            delegation_from: '{{ old("delegation_from") }}',
            delegation_to: '{{ old("delegation_to") }}',
            recipient_name: '{{ old("recipient_name") }}',
            recipient_phone: '{{ old("recipient_phone") }}',
            recipient_address: '{{ old("recipient_address") }}',
            content_description: '{{ old("content_description") }}',
            cod_amount: {{ old('cod_amount', '0') }},
            notes: '{{ old("notes") }}'
        },
        
        walletBalance: {{ $user->wallet->balance }},
        deliveryFee: {{ $user->clientProfile->offer_delivery_price }},
        returnFee: {{ $user->clientProfile->offer_return_price }},
        
        get showFeesSummary() {
            return this.formData.cod_amount > 0 || 
                   this.formData.delegation_from || 
                   this.formData.delegation_to;
        },
        
        get escrowAmount() {
            const codAmount = parseFloat(this.formData.cod_amount) || 0;
            if (codAmount >= this.deliveryFee) {
                return this.returnFee;
            } else {
                return this.deliveryFee;
            }
        },
        
        get remainingBalance() {
            return this.walletBalance - this.escrowAmount;
        },
        
        get canCreatePackage() {
            return this.formData.delegation_from && 
                   this.formData.delegation_to && 
                   this.formData.delegation_from !== this.formData.delegation_to &&
                   this.formData.recipient_name && 
                   this.formData.recipient_phone && 
                   this.formData.recipient_address && 
                   this.formData.content_description && 
                   this.formData.cod_amount >= 0 &&
                   this.remainingBalance >= 0;
        },
        
        calculateFees() {
            // Fees are automatically calculated by getters
            console.log('Frais calculés:', {
                escrow: this.escrowAmount,
                remaining: this.remainingBalance
            });
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-TN', { 
                style: 'currency', 
                currency: 'TND',
                minimumFractionDigits: 3
            }).format(amount || 0);
        },
        
        resetForm() {
            this.formData = {
                delegation_from: '',
                delegation_to: '',
                recipient_name: '',
                recipient_phone: '',
                recipient_address: '',
                content_description: '',
                cod_amount: 0,
                notes: ''
            };
        },
        
        validateForm(event) {
            if (!this.canCreatePackage) {
                event.preventDefault();
                showToast('Veuillez corriger les erreurs du formulaire', 'error');
                return false;
            }
            
            if (this.remainingBalance < 0) {
                event.preventDefault();
                showToast('Solde insuffisant pour créer ce colis', 'error');
                return false;
            }
            
            return true;
        }
    }
}
</script>
@endpush
@endsection