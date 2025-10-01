<!-- Quick Add Advance Modal -->
<div x-show="showQuickAddModal" x-transition
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.away="showQuickAddModal = false"
             class="relative bg-white rounded-xl shadow-xl max-w-lg w-full">

            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Ajouter une Avance Rapide
                </h3>
                <button @click="showQuickAddModal = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <form @submit.prevent="submitQuickAdd()" class="space-y-6">
                    <!-- Client Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rechercher un client <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text"
                                   x-model="quickAddForm.clientSearch"
                                   @input="searchClientsForQuickAdd()"
                                   placeholder="Tapez le nom, email ou téléphone du client..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">

                            <!-- Search Results -->
                            <div x-show="quickAddForm.searchResults.length > 0 && quickAddForm.clientSearch.length > 1"
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="client in quickAddForm.searchResults" :key="client.id">
                                    <div @click="selectClientForQuickAdd(client)"
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center">
                                                <span class="text-white font-medium text-xs" x-text="client.name.charAt(0)"></span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900" x-text="client.name"></div>
                                                <div class="text-sm text-gray-500" x-text="client.email"></div>
                                                <div class="text-xs text-emerald-600">
                                                    Avance actuelle: <span x-text="formatCurrency(client.advance_balance)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Client Display -->
                    <div x-show="quickAddForm.selectedClient" class="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center">
                                    <span class="text-white font-medium text-sm" x-text="quickAddForm.selectedClient?.name?.charAt(0)"></span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900" x-text="quickAddForm.selectedClient?.name"></div>
                                    <div class="text-sm text-gray-600" x-text="quickAddForm.selectedClient?.email"></div>
                                    <div class="text-sm font-medium text-emerald-600">
                                        Avance actuelle: <span x-text="formatCurrency(quickAddForm.selectedClient?.advance_balance || 0)"></span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="clearSelectedClient()"
                                    class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Amount and Description -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Montant (DT) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   x-model="quickAddForm.amount"
                                   step="0.001"
                                   min="0.001"
                                   max="1000"
                                   required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                   placeholder="Ex: 50.000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Montants prédéfinis
                            </label>
                            <div class="flex space-x-2">
                                <button type="button" @click="quickAddForm.amount = '25.000'"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                    25 DT
                                </button>
                                <button type="button" @click="quickAddForm.amount = '50.000'"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                    50 DT
                                </button>
                                <button type="button" @click="quickAddForm.amount = '100.000'"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                    100 DT
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea x-model="quickAddForm.description"
                                  rows="3"
                                  placeholder="Motif de l'avance accordée..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="submit"
                                :disabled="!quickAddForm.selectedClient || !quickAddForm.amount || quickAddForm.loading"
                                class="flex-1 bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!quickAddForm.loading">Ajouter l'Avance</span>
                            <span x-show="quickAddForm.loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement...
                            </span>
                        </button>
                        <button type="button"
                                @click="showQuickAddModal = false"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Add to the clientAdvancesApp function
if (typeof window.clientAdvancesAppExtensions === 'undefined') {
    window.clientAdvancesAppExtensions = {};
}

window.clientAdvancesAppExtensions.quickAdd = {
    quickAddForm: {
        clientSearch: '',
        selectedClient: null,
        searchResults: [],
        amount: '',
        description: '',
        loading: false,
        searchTimeout: null
    },

    async searchClientsForQuickAdd() {
        const query = this.quickAddForm.clientSearch.trim();

        if (query.length < 2) {
            this.quickAddForm.searchResults = [];
            return;
        }

        // Debounce the search
        clearTimeout(this.quickAddForm.searchTimeout);
        this.quickAddForm.searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/commercial/client-advances/api/search-clients?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                this.quickAddForm.searchResults = data.clients || [];
            } catch (error) {
                console.error('Erreur recherche clients:', error);
                this.quickAddForm.searchResults = [];
            }
        }, 300);
    },

    selectClientForQuickAdd(client) {
        this.quickAddForm.selectedClient = client;
        this.quickAddForm.clientSearch = client.name;
        this.quickAddForm.searchResults = [];
    },

    clearSelectedClient() {
        this.quickAddForm.selectedClient = null;
        this.quickAddForm.clientSearch = '';
        this.quickAddForm.searchResults = [];
    },

    async submitQuickAdd() {
        if (!this.quickAddForm.selectedClient || !this.quickAddForm.amount) {
            showToast('Veuillez sélectionner un client et saisir un montant', 'error');
            return;
        }

        this.quickAddForm.loading = true;

        try {
            const response = await fetch(`/commercial/client-advances/${this.quickAddForm.selectedClient.id}/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: parseFloat(this.quickAddForm.amount),
                    description: this.quickAddForm.description || `Avance accordée via ajout rapide`
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showToast(data.message, 'success');

                // Update client data if in current list
                const clientIndex = this.clients.findIndex(c => c.id === this.quickAddForm.selectedClient.id);
                if (clientIndex !== -1) {
                    this.clients[clientIndex].wallet.advance_balance = data.advance_balance;
                }

                // Reset form and close modal
                this.quickAddForm = {
                    clientSearch: '',
                    selectedClient: null,
                    searchResults: [],
                    amount: '',
                    description: '',
                    loading: false,
                    searchTimeout: null
                };
                this.showQuickAddModal = false;

                // Refresh stats
                await this.loadStats();
                this.applyFilters();

            } else {
                showToast(data.message || 'Erreur lors de l\'ajout de l\'avance', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur de connexion', 'error');
        } finally {
            this.quickAddForm.loading = false;
        }
    }
};
</script>