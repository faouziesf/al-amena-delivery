<!-- Add Advance Modal -->
<div x-show="showAddModal" x-transition
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.away="showAddModal = false"
             class="relative bg-white rounded-xl shadow-xl max-w-md w-full">

            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Ajouter une Avance
                </h3>
                <button @click="showAddModal = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Client Info -->
                <div x-show="selectedClient" class="mb-4 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center">
                            <span class="text-white font-medium text-sm" x-text="selectedClient?.name?.charAt(0)"></span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900" x-text="selectedClient?.name"></div>
                            <div class="text-sm text-gray-600" x-text="selectedClient?.email"></div>
                            <div class="text-sm font-medium text-emerald-600">
                                Avance actuelle: <span x-text="formatCurrency(selectedClient?.wallet?.advance_balance || 0)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Form -->
                <form @submit.prevent="submitAddAdvance()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Montant à ajouter (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               x-model="addAdvanceForm.amount"
                               step="0.001"
                               min="0.001"
                               max="1000"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ex: 50.000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea x-model="addAdvanceForm.description"
                                  rows="3"
                                  placeholder="Motif de l'avance accordée..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="submit"
                                :disabled="!addAdvanceForm.amount || addAdvanceForm.loading"
                                class="flex-1 bg-emerald-600 text-white py-2 px-4 rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!addAdvanceForm.loading">Confirmer l'Avance</span>
                            <span x-show="addAdvanceForm.loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement...
                            </span>
                        </button>
                        <button type="button"
                                @click="showAddModal = false"
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

window.clientAdvancesAppExtensions.addAdvance = {
    addAdvanceForm: {
        amount: '',
        description: '',
        loading: false
    },

    async submitAddAdvance() {
        if (!this.selectedClient || !this.addAdvanceForm.amount) {
            showToast('Veuillez remplir tous les champs requis', 'error');
            return;
        }

        this.addAdvanceForm.loading = true;

        try {
            const response = await fetch(`/commercial/client-advances/${this.selectedClient.id}/add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: parseFloat(this.addAdvanceForm.amount),
                    description: this.addAdvanceForm.description
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showToast(data.message, 'success');

                // Update client data
                const clientIndex = this.clients.findIndex(c => c.id === this.selectedClient.id);
                if (clientIndex !== -1) {
                    this.clients[clientIndex].wallet.advance_balance = data.advance_balance;
                }

                // Reset form and close modal
                this.addAdvanceForm = { amount: '', description: '', loading: false };
                this.showAddModal = false;
                this.selectedClient = null;

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
            this.addAdvanceForm.loading = false;
        }
    }
};
</script>