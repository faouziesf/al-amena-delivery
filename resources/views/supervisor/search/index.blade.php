<x-layouts.supervisor-new>
    <x-slot name="title">Recherche Intelligente</x-slot>
    <x-slot name="subtitle">Recherchez dans tous les colis, utilisateurs et tickets</x-slot>

    <div x-data="{
        query: '{{ request('q') }}',
        type: '{{ request('type', 'all') }}',
        results: [],
        loading: false,
        suggestions: [],
        
        async search() {
            if (this.query.length < 2) return;
            
            this.loading = true;
            try {
                const response = await fetch('/supervisor/search/api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        q: this.query,
                        type: this.type
                    })
                });
                
                const data = await response.json();
                this.results = data.results || [];
            } catch (error) {
                console.error('Erreur recherche:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async getSuggestions() {
            if (this.query.length < 2) {
                this.suggestions = [];
                return;
            }
            
            const response = await fetch(`/supervisor/search/suggestions?q=${this.query}`);
            const data = await response.json();
            this.suggestions = data.suggestions || [];
        }
    }" class="space-y-6">

        <!-- Search Bar -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="relative">
                <div class="flex items-center space-x-4">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="query"
                               @input.debounce.300ms="getSuggestions()"
                               @keyup.enter="search()"
                               placeholder="Rechercher un colis, utilisateur, ticket..." 
                               class="w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        
                        <!-- Suggestions -->
                        <div x-show="suggestions.length > 0" 
                             x-cloak
                             class="absolute z-50 w-full mt-2 bg-white rounded-lg shadow-xl border border-gray-200 max-h-80 overflow-y-auto">
                            <template x-for="suggestion in suggestions" :key="suggestion.id">
                                <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer" 
                                     @click="query = suggestion.text; suggestions = []; search()">
                                    <p class="font-medium text-gray-900" x-text="suggestion.text"></p>
                                    <p class="text-sm text-gray-600" x-text="suggestion.type"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <select x-model="type" 
                            class="px-4 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">Tout</option>
                        <option value="packages">Colis</option>
                        <option value="users">Utilisateurs</option>
                        <option value="tickets">Tickets</option>
                    </select>

                    <button @click="search()" 
                            class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center space-x-2">
                        <span>Rechercher</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>

                <p class="mt-3 text-sm text-gray-600">
                    💡 <strong>Astuce:</strong> Recherchez par tracking, nom, téléphone, email, adresse...
                </p>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="text-center py-12">
            <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-gray-600">Recherche en cours...</p>
        </div>

        <!-- Results -->
        <div x-show="!loading && results.length > 0" x-cloak class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <span x-text="results.length"></span> résultat(s) trouvé(s)
                </h3>
            </div>

            <template x-for="result in results" :key="result.id">
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="{
                                          'bg-blue-100 text-blue-800': result.type === 'package',
                                          'bg-green-100 text-green-800': result.type === 'user',
                                          'bg-purple-100 text-purple-800': result.type === 'ticket'
                                      }"
                                      x-text="result.type.toUpperCase()"></span>
                                <h4 class="font-semibold text-gray-900" x-text="result.title"></h4>
                            </div>
                            <p class="text-gray-600 text-sm" x-text="result.description"></p>
                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                <span x-text="result.meta"></span>
                            </div>
                        </div>
                        <a :href="result.url" 
                           class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                            Voir Détails
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- No Results -->
        <div x-show="!loading && query.length > 0 && results.length === 0" x-cloak class="text-center py-12 bg-white rounded-xl shadow">
            <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun résultat</h3>
            <p class="text-gray-600">Essayez avec d'autres mots-clés ou filtres</p>
        </div>

        <!-- Empty State -->
        <div x-show="query.length === 0" class="text-center py-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
            <svg class="w-24 h-24 mx-auto text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Recherche Intelligente</h3>
            <p class="text-gray-600 max-w-md mx-auto">
                Recherchez instantanément parmi tous vos colis, utilisateurs et tickets. 
                Entrez au moins 2 caractères pour commencer.
            </p>
        </div>
    </div>
</x-layouts.supervisor-new>
