@extends('layouts.client')

@section('title', 'Import CSV')
@section('page-title', 'Import de Colis en Masse')
@section('page-description', 'Importez plusieurs colis simultanément via un fichier CSV')

@section('header-actions')
<div class="flex items-center space-x-3 flex-col sm:flex-row">
    <a href="{{ route('client.packages.import.template') }}" 
       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Télécharger Template
    </a>
    <a href="{{ route('client.packages.index') }}" 
       class="inline-flex items-center px-4 sm:px-5 py-2.5 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-2xl transition-colors transform hover:scale-105 active:scale-95 transition-all duration-200 flex-col sm:flex-row">
        <svg class="w-5 h-5 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="csvImportForm()">
    
    <!-- Informations importantes -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6">
        <div class="flex items-start space-x-3 flex-col sm:flex-row">
            <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1 flex-col sm:flex-row">
                <h3 class="font-semibold text-blue-900 mb-2">📋 Instructions d'import</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Utilisez le template CSV fourni pour structurer vos données</li>
                    <li>• Maximum 500 colis par import</li>
                    <li>• Taille de fichier maximale : 5 MB</li>
                    <li>• Formats acceptés : CSV, TXT</li>
                    <li>• Vérifiez que vous avez suffisamment de solde avant l'import</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Solde actuel -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6">
        <div class="flex items-center justify-between flex-col sm:flex-row">
            <div>
                <span class="text-sm text-emerald-700">Solde disponible</span>
                <div class="text-2xl font-bold text-emerald-900">{{ number_format($user->wallet->balance, 3) }} DT</div>
            </div>
            <div class="text-sm text-emerald-700">
                <div>Frais livraison: {{ number_format($user->clientProfile->offer_delivery_price, 3) }} DT</div>
                <div>Frais retour: {{ number_format($user->clientProfile->offer_return_price, 3) }} DT</div>
            </div>
        </div>
    </div>

    <!-- Formulaire d'import -->
    <form action="{{ route('client.packages.import.process') }}" method="POST" enctype="multipart/form-data" 
          @submit="submitForm" class="bg-white rounded-2xl shadow-md hover:shadow-xl border transition-all duration-300 hover:-translate-y-1">
        @csrf
        
        <!-- Header -->
        <div class="bg-gray-50 px-4 sm:px-5 lg:px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">📁 Sélectionner le fichier CSV</h2>
        </div>

        <!-- Corps du formulaire -->
        <div class="p-4 sm:p-5 lg:p-6 space-y-6">
            
            <!-- Upload de fichier -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fichier CSV <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-4 sm:p-5 lg:p-6 text-center hover:border-blue-400 transition-colors"
                     @dragover.prevent="dragover = true"
                     @dragleave.prevent="dragover = false"
                     @drop.prevent="handleFileDrop($event)"
                     :class="dragover ? 'border-blue-400 bg-blue-50' : ''">
                    
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="hidden min-h-[44px]" x-ref="fileInput" @change="handleFileSelect($event)">
                    
                    <div x-show="!selectedFile">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="mt-4">
                            <button type="button" @click="$refs.fileInput.click()" 
                                    class="text-blue-600 hover:text-blue-500 font-medium">
                                Cliquez pour sélectionner
                            </button>
                            <span class="text-gray-500">ou glissez-déposez votre fichier</span>
                        </div>
                        <p class="text-sm sm:text-xs text-gray-500 mt-2">CSV, TXT jusqu'à 5MB</p>
                    </div>
                    
                    <div x-show="selectedFile" class="text-left">
                        <div class="flex items-center space-x-3 flex-col sm:flex-row">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900" x-text="selectedFile?.name"></p>
                                <p class="text-sm text-gray-500" x-text="formatFileSize(selectedFile?.size)"></p>
                            </div>
                            <button type="button" @click="clearFile()" 
                                    class="text-red-600 hover:text-red-800">
                                <svg class="w-6 h-6 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @error('csv_file')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options du CSV -->
            <div class="grid grid-cols-1 md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Délimiteur</label>
                    <select name="delimiter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
                        <option value="semicolon" selected>Point-virgule (;)</option>
                        <option value="comma">Virgule (,)</option>
                        <option value="tab">Tabulation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encodage</label>
                    <select name="encoding" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
                        <option value="UTF-8" selected>UTF-8</option>
                        <option value="ISO-8859-1">ISO-8859-1</option>
                    </select>
                </div>

                <div class="flex items-center pt-8 flex-col sm:flex-row">
                    <label class="flex items-center flex-col sm:flex-row">
                        <input type="checkbox" name="has_header" value="1" checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded min-h-[44px]">
                        <span class="ml-2 text-sm text-gray-700">Première ligne = en-têtes</span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 sm:px-5 lg:px-6 py-4 border-t flex items-center justify-between flex-col sm:flex-row">
            <div class="text-sm text-gray-500">
                <span x-show="selectedFile">
                    Fichier sélectionné: <span class="font-medium" x-text="selectedFile?.name"></span>
                </span>
            </div>
            
            <div class="flex items-center space-x-3 flex-col sm:flex-row">
                <button type="button" @click="clearFile()" 
                        class="px-4 sm:px-5 py-2.5 sm:py-3 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-all duration-300 hover:-translate-y-1">
                    Annuler
                </button>
                <button type="submit" :disabled="!selectedFile || submitting"
                        :class="selectedFile && !submitting ? 
                            'bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:bg-blue-700 text-white' : 
                            'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-4 sm:px-5 lg:px-6 py-2 text-sm font-medium rounded-md transition-colors">
                    <span x-show="!submitting">🚀 Lancer l'import</span>
                    <span x-show="submitting" class="flex items-center flex-col sm:flex-row">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Import en cours...
                    </span>
                </button>
            </div>
        </div>
    </form>

    <!-- Historique des imports récents -->
    @if($recentImports->count() > 0)
    <div class="mt-8 bg-white rounded-2xl shadow-md hover:shadow-xl border transition-all duration-300 hover:-translate-y-1">
        <div class="bg-gray-50 px-4 sm:px-5 lg:px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">📈 Historique des imports</h2>
        </div>
        
        <div class="divide-y divide-gray-200">
            @foreach($recentImports as $import)
            <div class="p-4 sm:p-5 lg:p-6 flex items-center justify-between flex-col sm:flex-row">
                <div class="flex items-center space-x-4 flex-col sm:flex-row">
                    <div class="flex-shrink-0 flex-col sm:flex-row">
                        <span class="inline-block px-3 py-1 text-sm sm:text-xs font-medium rounded-full {{ $import->getStatusColorAttribute() }}">
                            {{ $import->getStatusDisplayAttribute() }}
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $import->filename }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $import->batch_code }} • 
                            {{ $import->successful_rows }}/{{ $import->total_rows }} réussis •
                            {{ $import->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3 flex-col sm:flex-row">
                    @if($import->isCompleted())
                        <span class="text-sm text-green-600 font-medium">{{ $import->getSuccessRateAttribute() }}% réussi</span>
                    @endif
                    
                    <a href="{{ route('client.packages.import.status', $import->id) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Voir détails
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function csvImportForm() {
    return {
        selectedFile: null,
        dragover: false,
        submitting: false,
        
        handleFileDrop(e) {
            this.dragover = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.selectFile(files[0]);
            }
        },
        
        handleFileSelect(e) {
            if (e.target.files.length > 0) {
                this.selectFile(e.target.files[0]);
            }
        },
        
        selectFile(file) {
            // Vérifier l'extension
            const allowedExtensions = ['.csv', '.txt'];
            const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
            
            if (!allowedExtensions.includes(fileExtension)) {
                alert('Format de fichier non supporté. Utilisez un fichier CSV ou TXT.');
                return;
            }
            
            // Vérifier la taille (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Taille maximale: 5MB.');
                return;
            }
            
            this.selectedFile = file;
        },
        
        clearFile() {
            this.selectedFile = null;
            this.$refs.fileInput.value = '';
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '';
            const sizes = ['octets', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
        },
        
        submitForm(e) {
            if (!this.selectedFile) {
                e.preventDefault();
                alert('Veuillez sélectionner un fichier CSV');
                return;
            }
            
            this.submitting = true;
        }
    }
}
</script>
@endpush
@endsection