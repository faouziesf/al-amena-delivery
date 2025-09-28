<!-- Modal Création Client -->
<div x-show="showCreateClientModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4"
     style="display: none;"
     @click.self="showCreateClientModal = false">

    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900">Nouveau Client</h3>
            <button @click="showCreateClientModal = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="createClient()">
            <div class="p-6 space-y-6">
                <!-- Informations de Base -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Informations de Base</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Nom complet">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="email@exemple.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Téléphone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="+216 XX XXX XXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nom Boutique
                            </label>
                            <input type="text"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Nom de la boutique (optionnel)">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Adresse <span class="text-red-500">*</span>
                        </label>
                        <input type="text" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Adresse complète">
                    </div>
                </div>

                <!-- Authentification -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Authentification</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <input type="password" required minlength="6"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Minimum 6 caractères">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmer <span class="text-red-500">*</span>
                            </label>
                            <input type="password" required minlength="6"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Confirmer le mot de passe">
                        </div>
                    </div>
                </div>

                <!-- Tarification -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Tarification</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prix Livraison (DT) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.001" required min="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="ex: 7.000">
                            <p class="text-xs text-gray-500 mt-1">Montant facturé en cas de livraison réussie</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prix Retour (DT) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.001" required min="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="ex: 3.000">
                            <p class="text-xs text-gray-500 mt-1">Montant facturé en cas de retour</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex space-x-3 p-6 border-t bg-gray-50 rounded-b-xl">
                <button type="submit"
                        class="flex-1 bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 transition-colors">
                    Créer Client
                </button>
                <button type="button" @click="showCreateClientModal = false"
                        class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function createClient() {
    showToast('Fonctionnalité de création client à implémenter', 'info');
    // TODO: Implement client creation logic
}
</script>