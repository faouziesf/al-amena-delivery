@extends('layouts.client')

@section('title', 'Nouveau Ticket')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-6" x-data="ticketForm()">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mb-1">✉️ Nouveau Ticket</h1>
                <p class="text-gray-600">Contactez notre équipe de support</p>
            </div>
            <a href="{{ route('client.tickets.index') }}" 
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Messages Flash -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm animate-pulse">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-800 font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formulaire Principal -->
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @if(isset($complaint))
                <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                @endif

                <!-- Card Principale -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                    
                    @if(isset($complaint))
                    <!-- Alert Réclamation -->
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                <span class="text-2xl">⚠️</span>
                            </div>
                            <div>
                                <h3 class="font-bold">Ticket depuis réclamation</h3>
                                <p class="text-sm text-white/90">Réclamation #{{ $complaint->id }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 space-y-6">
                        
                        <!-- Type de Ticket -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-4">
                                Type de demande *
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @foreach([
                                    ['value' => 'COMPLAINT', 'icon' => '📋', 'label' => 'Réclamation', 'desc' => 'Problème colis'],
                                    ['value' => 'QUESTION', 'icon' => '❓', 'label' => 'Question', 'desc' => 'Information'],
                                    ['value' => 'SUPPORT', 'icon' => '🛠️', 'label' => 'Support', 'desc' => 'Aide technique'],
                                    ['value' => 'OTHER', 'icon' => '📝', 'label' => 'Autre', 'desc' => 'Autre sujet']
                                ] as $type)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $type['value'] }}" 
                                           class="peer sr-only" 
                                           {{ old('type', isset($complaint) ? 'COMPLAINT' : '') === $type['value'] ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-gray-200 rounded-xl text-center transition-all
                                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md
                                                hover:border-indigo-300 hover:bg-indigo-50/50">
                                        <div class="text-3xl mb-2">{{ $type['icon'] }}</div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $type['label'] }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $type['desc'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sujet -->
                        <div>
                            <label for="subject" class="block text-sm font-bold text-gray-900 mb-2">
                                Sujet *
                            </label>
                            <input type="text" 
                                   name="subject" 
                                   id="subject" 
                                   required
                                   value="{{ old('subject', isset($complaint) ? 'Réclamation - ' . ($complaint->type ?? '') : '') }}"
                                   placeholder="Ex: Problème de livraison, Question sur les frais..."
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('subject') border-red-300 @enderror">
                            @error('subject')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-900 mb-2">
                                Description *
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="6" 
                                      required
                                      x-model="description"
                                      placeholder="Décrivez votre problème ou question en détail..."
                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none @error('description') border-red-300 @enderror">{{ old('description', $complaint->description ?? '') }}</textarea>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm text-gray-500">
                                    <span x-text="description.length"></span> caractères
                                </p>
                                <p class="text-sm" :class="description.length < 50 ? 'text-red-500' : description.length < 100 ? 'text-orange-500' : 'text-green-500'">
                                    <span x-show="description.length < 50">Trop court</span>
                                    <span x-show="description.length >= 50 && description.length < 100">Bien</span>
                                    <span x-show="description.length >= 100">Parfait ✓</span>
                                </p>
                            </div>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Code Colis -->
                        <div>
                            <label for="package_code" class="block text-sm font-bold text-gray-900 mb-2">
                                Code colis (optionnel)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       name="package_code" 
                                       id="package_code"
                                       placeholder="PKG_XXXXXX"
                                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Si votre demande concerne un colis spécifique</p>
                        </div>

                        <!-- Pièces jointes -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                Pièces jointes (optionnel)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition-colors cursor-pointer"
                                 @click="$refs.fileInput.click()">
                                <input type="file" 
                                       name="attachments[]" 
                                       multiple 
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                       x-ref="fileInput"
                                       @change="handleFiles($event)"
                                       class="hidden">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-700 mb-1">Cliquez pour ajouter des fichiers</p>
                                <p class="text-xs text-gray-500">JPG, PNG, PDF, DOC • Max 5MB</p>
                            </div>
                            
                            <!-- Aperçu fichiers -->
                            <div x-show="files.length > 0" class="mt-4 space-y-2">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900" x-text="file.name"></p>
                                                <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Actions -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                        <a href="{{ route('client.tickets.index') }}" 
                           class="sm:hidden inline-flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-gray-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Retour
                        </a>
                        
                        <button type="submit" 
                                class="ml-auto inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Créer le ticket
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <!-- Sidebar Aide -->
        <div class="lg:col-span-1 space-y-4">
            
            <!-- Conseils -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-lg">Conseils</h3>
                </div>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-300">✓</span>
                        <span>Soyez précis dans votre description</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-300">✓</span>
                        <span>Ajoutez des captures d'écran si possible</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-300">✓</span>
                        <span>Indiquez le code du colis concerné</span>
                    </li>
                </ul>
            </div>

            <!-- Temps de Réponse -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-lg text-gray-900">Délai</h3>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-2xl font-black text-green-600">2-4h</div>
                        <div class="text-xs text-green-700">Questions simples</div>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg border border-orange-200">
                        <div class="text-2xl font-black text-orange-600">24-48h</div>
                        <div class="text-xs text-orange-700">Problèmes complexes</div>
                    </div>
                </div>
            </div>

            <!-- Contact Rapide -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-lg text-gray-900">Contact</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <a href="tel:+21670123456" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span>📞</span>
                        <span class="font-medium text-gray-900">+216 70 123 456</span>
                    </a>
                    <a href="mailto:support@alamena.com" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span>✉️</span>
                        <span class="font-medium text-gray-900">support@alamena.com</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function ticketForm() {
    return {
        description: '{{ old('description', $complaint->description ?? '') }}',
        files: [],
        
        handleFiles(event) {
            const newFiles = Array.from(event.target.files);
            this.files = [...this.files, ...newFiles];
        },
        
        removeFile(index) {
            this.files.splice(index, 1);
            this.$refs.fileInput.value = '';
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    }
}
</script>
@endsection
