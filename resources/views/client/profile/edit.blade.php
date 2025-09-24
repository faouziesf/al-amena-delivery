@extends('layouts.client')

@section('title', 'Modifier mon Profil')

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Modifier mon Profil</h1>
                <p class="text-gray-600">Mettez à jour vos informations personnelles et professionnelles</p>
            </div>
            <a href="{{ route('client.profile.index') }}"
               class="text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <form action="{{ route('client.profile.update') }}" method="POST" enctype="multipart/form-data"
          class="space-y-8" x-data="profileForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Formulaire principal -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Informations personnelles -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informations Personnelles</h3>
                        <p class="text-sm text-gray-600 mt-1">Ces informations sont obligatoires pour votre compte</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom complet <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Téléphone <span class="text-red-500">*</span>
                                </label>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       required
                                       placeholder="Ex: 20123456"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Adresse complète <span class="text-red-500">*</span>
                            </label>
                            <textarea id="address"
                                      name="address"
                                      rows="3"
                                      required
                                      placeholder="Votre adresse complète avec ville et code postal"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address') border-red-500 @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informations professionnelles -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informations Professionnelles</h3>
                        <p class="text-sm text-gray-600 mt-1">Optionnelles mais recommandées pour un meilleur service</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom de boutique/entreprise
                                </label>
                                <input type="text"
                                       id="shop_name"
                                       name="shop_name"
                                       value="{{ old('shop_name', optional($user->clientProfile)->shop_name) }}"
                                       placeholder="Ex: Boutique Mode Tunis"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('shop_name') border-red-500 @enderror">
                                @error('shop_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="business_sector" class="block text-sm font-medium text-gray-700 mb-2">
                                    Secteur d'activité
                                </label>
                                <input type="text"
                                       id="business_sector"
                                       name="business_sector"
                                       value="{{ old('business_sector', optional($user->clientProfile)->business_sector) }}"
                                       placeholder="Ex: Mode et vêtements"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('business_sector') border-red-500 @enderror">
                                @error('business_sector')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="fiscal_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Matricule fiscal
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="fiscal_number"
                                       name="fiscal_number"
                                       value="{{ old('fiscal_number', optional($user->clientProfile)->fiscal_number) }}"
                                       placeholder="1234567ABC123"
                                       maxlength="13"
                                       x-model="fiscalNumber"
                                       @input="validateFiscalNumber()"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono @error('fiscal_number') border-red-500 @enderror">
                                <div x-show="fiscalValidation.show"
                                     class="absolute right-3 top-3"
                                     x-transition>
                                    <svg x-show="fiscalValidation.valid"
                                         class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="!fiscalValidation.valid"
                                         class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Format: 7 chiffres + 3 lettres + 3 chiffres (ex: 1234567ABC123)</p>
                            <p x-show="fiscalValidation.message"
                               x-text="fiscalValidation.message"
                               :class="fiscalValidation.valid ? 'text-green-600' : 'text-red-600'"
                               class="text-sm mt-1"></p>
                            @error('fiscal_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="identity_document" class="block text-sm font-medium text-gray-700 mb-2">
                                Document d'identité
                            </label>
                            <div class="space-y-4">
                                @if(optional($user->clientProfile)->identity_document)
                                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-200">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div>
                                                <p class="font-medium text-green-800">Document déjà fourni</p>
                                                <p class="text-sm text-green-600">Vous pouvez télécharger ou remplacer votre document</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('client.profile.download-identity') }}"
                                               class="text-green-600 hover:text-green-700 p-2 rounded-lg hover:bg-green-100 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            <button type="button"
                                                    @click="deleteDocument()"
                                                    class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-100 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-purple-400 transition-colors">
                                    <input type="file"
                                           id="identity_document"
                                           name="identity_document"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           @change="handleFileUpload($event)"
                                           class="hidden">
                                    <label for="identity_document" class="cursor-pointer block text-center">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-gray-600 font-medium">{{ optional($user->clientProfile)->identity_document ? 'Remplacer le document' : 'Télécharger un document' }}</p>
                                        <p class="text-sm text-gray-500 mt-1">PDF, JPG, PNG jusqu'à 5MB</p>
                                    </label>
                                </div>

                                <div x-show="uploadProgress.show"
                                     x-transition
                                     class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span x-text="uploadProgress.fileName" class="text-blue-800 font-medium"></span>
                                    </div>
                                    <p class="text-sm text-blue-600 mt-1">Fichier sélectionné - sera téléchargé lors de la sauvegarde</p>
                                </div>
                            </div>
                            @error('identity_document')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Actions -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sauvegarder les modifications</h3>
                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full bg-purple-600 text-white py-3 px-4 rounded-xl hover:bg-purple-700 transition-colors font-medium">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer
                        </button>
                        <a href="{{ route('client.profile.index') }}"
                           class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-xl hover:bg-gray-200 transition-colors font-medium text-center inline-block">
                            Annuler
                        </a>
                    </div>
                </div>

                <!-- Aide -->
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aide
                    </h3>
                    <div class="space-y-3 text-sm text-blue-800">
                        <p><strong>Informations personnelles :</strong> Obligatoires pour votre compte</p>
                        <p><strong>Informations professionnelles :</strong> Optionnelles mais recommandées</p>
                        <p><strong>Document d'identité :</strong> CIN, passeport ou registre de commerce</p>
                        <p><strong>Matricule fiscal :</strong> Utile pour les entreprises</p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function profileForm() {
    return {
        fiscalNumber: '{{ old('fiscal_number', optional($user->clientProfile)->fiscal_number) }}',
        fiscalValidation: {
            show: false,
            valid: false,
            message: ''
        },
        uploadProgress: {
            show: false,
            fileName: ''
        },

        validateFiscalNumber() {
            if (!this.fiscalNumber) {
                this.fiscalValidation.show = false;
                return;
            }

            const pattern = /^[0-9]{7}[A-Z]{3}[0-9]{3}$/;
            this.fiscalValidation.valid = pattern.test(this.fiscalNumber);
            this.fiscalValidation.show = true;
            this.fiscalValidation.message = this.fiscalValidation.valid
                ? 'Format valide'
                : 'Format invalide (ex: 1234567ABC123)';
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.uploadProgress.show = true;
                this.uploadProgress.fileName = file.name;
            } else {
                this.uploadProgress.show = false;
            }
        },

        async deleteDocument() {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
                return;
            }

            try {
                const response = await fetch('{{ route('client.profile.delete-identity') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            } catch (error) {
                alert('Erreur lors de la suppression du document');
            }
        },

        init() {
            if (this.fiscalNumber) {
                this.validateFiscalNumber();
            }
        }
    }
}
</script>
@endpush
@endsection