@props([
    'placeholder' => 'PKG_1234...',
    'autofocus' => true,
    'showHistory' => true
])

<div x-show="activeMode === 'manual'" class="space-y-4">
    <!-- Code Input -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Code du colis</label>
        <div class="relative">
            <input type="text"
                   x-ref="manualInput"
                   x-model="manualCode"
                   @keydown.enter="searchCode()"
                   @input="validateCode()"
                   class="w-full px-4 py-3 border rounded-xl focus:ring-2 text-lg font-mono uppercase transition-colors"
                   :class="{
                        'border-gray-300 focus:border-blue-500 focus:ring-blue-200': !manualCode,
                        'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-200': codeValid,
                        'border-red-500 focus:border-red-500 focus:ring-red-200': manualCode && !codeValid
                   }"
                   placeholder="{{ $placeholder }}"
                   {{ $autofocus ? 'autofocus' : '' }}>

            <!-- Validation Icons -->
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <!-- Valid Icon -->
                <svg x-show="codeValid"
                     class="h-5 w-5 text-emerald-500"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>

                <!-- Invalid Icon -->
                <svg x-show="manualCode && !codeValid"
                     class="h-5 w-5 text-red-500"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <!-- Code Validation Feedback -->
        <div x-show="manualCode && !codeValid" class="mt-1 text-sm text-red-600">
            Code invalide. Le code doit contenir au moins 6 caractères.
        </div>

        <div x-show="codeValid" class="mt-1 text-sm text-emerald-600">
            Code valide
        </div>
    </div>

    <!-- Search Button -->
    <button @click="searchCode()"
            :disabled="!codeValid || searching"
            class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
        <span x-show="!searching" class="flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Rechercher</span>
        </span>
        <span x-show="searching" class="flex items-center justify-center space-x-2">
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Recherche...</span>
        </span>
    </button>

    <!-- Quick Actions -->
    <div class="flex space-x-2">
        <button @click="manualCode = ''; validateCode();"
                class="flex-1 bg-gray-100 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Effacer
        </button>

        <button @click="$refs.manualInput.focus()"
                class="flex-1 bg-gray-100 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            Focus
        </button>
    </div>

    <!-- Code Format Help -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div class="flex items-start space-x-2">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-900">Formats de codes acceptés</p>
                <ul class="text-xs text-blue-700 mt-1 space-y-1">
                    <li>• Code colis: PKG_12345678</li>
                    <li>• Code-barres standard</li>
                    <li>• QR codes avec URL de tracking</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Code Suggestions (based on recent patterns) -->
    <div x-show="manualCode.length >= 3 && !codeValid" class="space-y-2">
        <h5 class="text-sm font-medium text-gray-700">Suggestions</h5>
        <template x-for="suggestion in getCodeSuggestions()" :key="suggestion">
            <button @click="manualCode = suggestion; validateCode();"
                    class="w-full text-left px-3 py-2 text-sm bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg transition-colors">
                <span class="font-mono" x-text="suggestion"></span>
            </button>
        </template>
    </div>

    @if ($showHistory)
    <!-- Recent Codes History -->
    <div x-show="recentCodes.length > 0" class="space-y-2">
        <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium text-gray-700">Codes récents</h5>
            <button @click="clearRecentCodes()"
                    class="text-xs text-red-600 hover:text-red-800">
                Effacer l'historique
            </button>
        </div>

        <div class="space-y-1 max-h-32 overflow-y-auto">
            <template x-for="item in recentCodes.slice(0, 5)" :key="item.value">
                <div class="flex items-center space-x-2 p-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <button @click="useRecentCode(item.value)"
                            class="flex-1 text-left">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-sm text-gray-900" x-text="item.value"></span>
                            <span class="text-xs text-gray-500" x-text="formatTime(item.timestamp)"></span>
                        </div>
                    </button>

                    <button @click="removeRecentCode(item.value)"
                            class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- Keyboard Shortcuts Help -->
    <div class="text-xs text-gray-500 bg-gray-50 rounded-lg p-2">
        <div class="flex items-center space-x-4">
            <span class="flex items-center space-x-1">
                <kbd class="px-1 py-0.5 bg-gray-200 rounded text-xs">Enter</kbd>
                <span>Rechercher</span>
            </span>
            <span class="flex items-center space-x-1">
                <kbd class="px-1 py-0.5 bg-gray-200 rounded text-xs">Esc</kbd>
                <span>Fermer</span>
            </span>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonctions additionnelles pour code-input
window.codeInputHelpers = {
    getCodeSuggestions(partialCode) {
        if (!partialCode || partialCode.length < 3) return [];

        const suggestions = [];
        const upper = partialCode.toUpperCase();

        // Suggestion basée sur le pattern PKG_
        if (upper.startsWith('PKG') && !upper.includes('_')) {
            suggestions.push(`PKG_${upper.substring(3).padEnd(8, '0')}`);
        }

        // Suggestion basée sur les codes récents
        const recentCodes = JSON.parse(localStorage.getItem('scanner_recent_codes') || '[]');
        const matching = recentCodes
            .filter(item => item.value.toUpperCase().startsWith(upper))
            .slice(0, 3)
            .map(item => item.value);

        suggestions.push(...matching);

        return [...new Set(suggestions)]; // Unique values
    },

    clearRecentCodes() {
        localStorage.removeItem('scanner_recent_codes');
        this.recentCodes = [];
    },

    removeRecentCode(code) {
        this.recentCodes = this.recentCodes.filter(item => item.value !== code);
        localStorage.setItem('scanner_recent_codes', JSON.stringify(this.recentCodes));
    }
};

// Extend the main scanner component with code input helpers
document.addEventListener('alpine:init', () => {
    Alpine.data('delivererQRScanner', (options) => ({
        ...Alpine.$data.delivererQRScanner?.(options) || {},

        getCodeSuggestions() {
            return window.codeInputHelpers.getCodeSuggestions(this.manualCode);
        },

        clearRecentCodes() {
            window.codeInputHelpers.clearRecentCodes.call(this);
        },

        removeRecentCode(code) {
            window.codeInputHelpers.removeRecentCode.call(this, code);
        }
    }));
});
</script>
@endpush