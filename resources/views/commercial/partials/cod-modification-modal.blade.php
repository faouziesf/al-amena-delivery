<!-- Modal Modification COD -->
<div x-show="showCodModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4"
     style="display: none;"
     @click.self="showCodModal = false">

    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Modifier COD</h3>
            <button @click="showCodModal = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="modifyCod()">
            <div class="p-6 space-y-4">
                <!-- Code Colis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Code Colis <span class="text-red-500">*</span>
                    </label>
                    <input type="text" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Entrer le code du colis">
                    <p class="text-xs text-gray-500 mt-1">Le code du colis dont vous voulez modifier le COD</p>
                </div>

                <!-- Nouveau Montant COD -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nouveau Montant COD (DT) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.001" required min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="ex: 50.000">
                    <p class="text-xs text-gray-500 mt-1">Le nouveau montant à collecter lors de la livraison</p>
                </div>

                <!-- Raison -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Raison de la Modification <span class="text-red-500">*</span>
                    </label>
                    <select required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sélectionner une raison</option>
                        <option value="client_request">Demande client</option>
                        <option value="price_error">Erreur de prix initial</option>
                        <option value="product_change">Changement produit</option>
                        <option value="complaint_resolution">Résolution réclamation</option>
                        <option value="commercial_discount">Remise commerciale</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Notes supplémentaires
                    </label>
                    <textarea rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Détails supplémentaires sur la modification..."></textarea>
                </div>

                <!-- Urgence -->
                <div class="flex items-center">
                    <input type="checkbox" id="urgent-modification"
                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="urgent-modification" class="ml-2 text-sm text-gray-700">
                        Modification urgente
                    </label>
                    <div class="ml-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Priorité
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex space-x-3 p-6 border-t bg-gray-50 rounded-b-xl">
                <button type="submit"
                        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition-colors">
                    Modifier COD
                </button>
                <button type="button" @click="showCodModal = false"
                        class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function modifyCod() {
    showToast('Fonctionnalité de modification COD à implémenter', 'info');
    // TODO: Implement COD modification logic
}
</script>