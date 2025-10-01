@extends('layouts.client')

@section('title', 'Mes Comptes Bancaires')

@section('content')
<div x-data="bankAccountsApp()" class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-3xl font-bold text-gray-900">💳 Mes Comptes Bancaires</h1>
                <p class="text-gray-600 mt-2">Gérez vos comptes bancaires pour les retraits</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="openCreateModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter un compte bancaire
                </button>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Liste des comptes bancaires -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($bankAccounts as $account)
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
                <!-- Header avec status -->
                <div class="p-6 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            @if($account->is_default)
                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ⭐ Par défaut
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2">
                            <!-- Actions dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">

                                    @if(!$account->is_default)
                                        <form action="{{ route('client.bank-accounts.set-default', $account) }}" method="POST" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Définir par défaut
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('client.bank-accounts.edit', $account) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifier
                                    </a>

                                    @if($bankAccounts->count() > 1)
                                        <form action="{{ route('client.bank-accounts.destroy', $account) }}" method="POST"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte bancaire ?')" class="block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du compte -->
                    <div class="space-y-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $account->bank_name }}</h3>
                            <p class="text-sm text-gray-600">{{ $account->account_holder_name }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">IBAN</span>
                                <button @click="toggleIban({{ $account->id }})" class="text-xs text-purple-600 hover:text-purple-800">
                                    <span x-show="!showFullIban[{{ $account->id }}]">Afficher</span>
                                    <span x-show="showFullIban[{{ $account->id }}]">Masquer</span>
                                </button>
                            </div>
                            <div class="mt-1 font-mono text-sm">
                                <span x-show="!showFullIban[{{ $account->id }}]" class="text-gray-900">{{ $account->masked_iban }}</span>
                                <span x-show="showFullIban[{{ $account->id }}]" class="text-gray-900">{{ chunk_split($account->iban, 4, ' ') }}</span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            @if($account->last_used_at)
                                Dernière utilisation: {{ $account->last_used_at->format('d/m/Y H:i') }}
                            @else
                                Jamais utilisé
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun compte bancaire</h3>
                    <p class="text-gray-500 mb-6">Ajoutez votre premier compte bancaire pour effectuer des retraits</p>
                    <button @click="openCreateModal()"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter un compte bancaire
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal d'ajout de compte bancaire -->
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">

        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-screen overflow-y-auto"
             @click.away="closeCreateModal()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">

            <form action="{{ route('client.bank-accounts.store') }}" method="POST" class="p-6">
                @csrf

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Ajouter un compte bancaire</h3>
                    <button type="button" @click="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la banque</label>
                        <select name="bank_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du titulaire</label>
                        <input type="text" name="account_holder_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Nom complet du titulaire du compte">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">IBAN</label>
                        <input type="text" name="iban" required
                               x-model="newIban"
                               @input="validateIban()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="TN5901000123456789012345"
                               maxlength="24">
                        <div x-show="ibanValidation.message" class="mt-2 text-sm"
                             :class="ibanValidation.valid ? 'text-green-600' : 'text-red-600'"
                             x-text="ibanValidation.message"></div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default"
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Définir comme compte par défaut
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="closeCreateModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            :disabled="!ibanValidation.valid"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Ajouter le compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bankAccountsApp() {
    return {
        showCreateModal: false,
        showFullIban: {},
        newIban: '',
        ibanValidation: { valid: false, message: '' },

        openCreateModal() {
            this.showCreateModal = true;
            this.newIban = '';
            this.ibanValidation = { valid: false, message: '' };
        },

        closeCreateModal() {
            this.showCreateModal = false;
        },

        toggleIban(accountId) {
            this.showFullIban[accountId] = !this.showFullIban[accountId];
        },

        async validateIban() {
            if (!this.newIban) {
                this.ibanValidation = { valid: false, message: '' };
                return;
            }

            try {
                const response = await fetch('{{ route("client.bank-accounts.validate-iban") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ iban: this.newIban })
                });

                const data = await response.json();
                this.ibanValidation = {
                    valid: data.valid,
                    message: data.message
                };

            } catch (error) {
                console.error('Erreur lors de la validation IBAN:', error);
                this.ibanValidation = { valid: false, message: 'Erreur de validation' };
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection