<!-- Remove Advance Modal -->
<div x-show="showRemoveModal" x-transition
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div @click.away="showRemoveModal = false"
             class="relative bg-white rounded-xl shadow-xl max-w-md w-full">

            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Retirer une Avance
                </h3>
                <button @click="showRemoveModal = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Client Info -->
                <div x-show="selectedClient" class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center">
                            <span class="text-white font-medium text-sm" x-text="selectedClient?.name?.charAt(0)"></span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900" x-text="selectedClient?.name"></div>
                            <div class="text-sm text-gray-600" x-text="selectedClient?.email"></div>
                            <div class="text-sm font-medium text-red-600">
                                Avance disponible: <span x-text="formatCurrency(selectedClient?.wallet?.advance_balance || 0)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Notice -->
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <strong>Attention:</strong> Cette action retirera définitivement l'avance du compte client.
                        </div>
                    </div>
                </div>

                <!-- Remove Form -->
                <form @submit.prevent="submitRemoveAdvance()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Montant à retirer (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               x-model="removeAdvanceForm.amount"
                               step="0.001"
                               min="0.001"
                               :max="selectedClient?.wallet?.advance_balance || 0"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Ex: 25.000">
                        <div class="text-xs text-gray-500 mt-1">
                            Maximum: <span x-text="formatCurrency(selectedClient?.wallet?.advance_balance || 0)"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Motif du retrait <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="removeAdvanceForm.description"
                                  rows="3"
                                  required
                                  placeholder="Motif du retrait de l'avance..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="submit"
                                :disabled="!removeAdvanceForm.amount || !removeAdvanceForm.description || removeAdvanceForm.loading"
                                class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!removeAdvanceForm.loading">Confirmer le Retrait</span>
                            <span x-show="removeAdvanceForm.loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Traitement...
                            </span>
                        </button>
                        <button type="button"
                                @click="showRemoveModal = false"
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

window.clientAdvancesAppExtensions.removeAdvance = {
    removeAdvanceForm: {
        amount: '',
        description: '',
        loading: false
    },

    async submitRemoveAdvance() {
        if (!this.selectedClient || !this.removeAdvanceForm.amount || !this.removeAdvanceForm.description) {
            showToast('Veuillez remplir tous les champs requis', 'error');
            return;
        }

        const amount = parseFloat(this.removeAdvanceForm.amount);
        const availableAdvance = this.selectedClient.wallet?.advance_balance || 0;

        if (amount > availableAdvance) {
            showToast(`Le montant ne peut pas dépasser l'avance disponible (${this.formatCurrency(availableAdvance)})`, 'error');
            return;
        }

        this.removeAdvanceForm.loading = true;

        try {
            const response = await fetch(`/commercial/client-advances/${this.selectedClient.id}/remove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: amount,
                    description: this.removeAdvanceForm.description
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
                this.removeAdvanceForm = { amount: '', description: '', loading: false };
                this.showRemoveModal = false;
                this.selectedClient = null;

                // Refresh stats
                await this.loadStats();
                this.applyFilters();

            } else {
                showToast(data.message || 'Erreur lors du retrait de l\'avance', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur de connexion', 'error');
        } finally {
            this.removeAdvanceForm.loading = false;
        }
    }
};
</script>