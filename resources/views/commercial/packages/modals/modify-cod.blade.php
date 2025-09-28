<!-- Modal de modification du COD -->
<div id="modifyCodModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <!-- Header du modal -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        Modifier le montant COD
                    </h3>
                    <button onclick="closeModal('modifyCodModal')" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('commercial.packages.modify.cod', $package) }}" method="POST" class="p-6">
                @csrf

                <!-- Montant actuel -->
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-amber-800 font-medium">Montant COD actuel :</span>
                        <span class="text-lg font-bold text-amber-900">{{ number_format($package->cod_amount, 2) }} TND</span>
                    </div>
                </div>

                <!-- Nouveau montant -->
                <div class="mb-6">
                    <label for="new_cod_amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau montant COD (TND) *
                    </label>
                    <div class="relative">
                        <input type="number" name="new_cod_amount" id="new_cod_amount"
                            step="0.01" min="0" max="9999.99" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent pr-12"
                            placeholder="0.00"
                            value="{{ number_format($package->cod_amount, 2, '.', '') }}">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 text-sm">TND</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Montant entre 0 et 9999.99 TND</p>
                </div>

                <!-- Raison de la modification -->
                <div class="mb-6">
                    <label for="cod_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Raison de la modification *
                    </label>
                    <select name="reason" id="cod_reason" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                        <option value="">-- Sélectionner une raison --</option>
                        <option value="DEMANDE_CLIENT">Demande du client</option>
                        <option value="ERREUR_SAISIE">Erreur de saisie initiale</option>
                        <option value="MODIFICATION_COMMANDE">Modification de commande</option>
                        <option value="AJUSTEMENT_PRIX">Ajustement de prix</option>
                        <option value="PROMOTION">Application d'une promotion</option>
                        <option value="AUTRE">Autre raison</option>
                    </select>
                </div>

                <!-- Raison personnalisée -->
                <div class="mb-6" id="custom_reason_field" style="display: none;">
                    <label for="custom_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Précisez la raison
                    </label>
                    <textarea name="custom_reason" id="custom_reason" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                        placeholder="Expliquez la raison de cette modification..."></textarea>
                </div>

                <!-- Modification d'urgence -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="emergency" value="1"
                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">
                            Modification d'urgence
                            <span class="text-red-600 font-medium">(prioritaire)</span>
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">Cochez si cette modification est urgente et doit être traitée immédiatement</p>
                </div>

                <!-- Aperçu des modifications -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Aperçu des modifications :</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-gray-500">Ancien montant</div>
                            <div class="text-lg font-semibold text-gray-800" id="old_amount_display">{{ number_format($package->cod_amount, 2) }} TND</div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500">Nouveau montant</div>
                            <div class="text-lg font-semibold text-amber-600" id="new_amount_display">{{ number_format($package->cod_amount, 2) }} TND</div>
                        </div>
                    </div>
                    <div class="text-center mt-2 pt-2 border-t border-gray-200">
                        <div class="text-xs text-gray-500">Différence</div>
                        <div class="text-sm font-semibold" id="difference_display">0.00 TND</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeModal('modifyCodModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Modifier le COD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newAmountInput = document.getElementById('new_cod_amount');
    const reasonSelect = document.getElementById('cod_reason');
    const customReasonField = document.getElementById('custom_reason_field');
    const oldAmountDisplay = document.getElementById('old_amount_display');
    const newAmountDisplay = document.getElementById('new_amount_display');
    const differenceDisplay = document.getElementById('difference_display');

    const originalAmount = {{ $package->cod_amount }};

    // Gestion du champ raison personnalisée
    reasonSelect.addEventListener('change', function() {
        if (this.value === 'AUTRE') {
            customReasonField.style.display = 'block';
            document.getElementById('custom_reason').setAttribute('required', 'required');
        } else {
            customReasonField.style.display = 'none';
            document.getElementById('custom_reason').removeAttribute('required');
        }
    });

    // Mise à jour de l'aperçu en temps réel
    newAmountInput.addEventListener('input', function() {
        const newAmount = parseFloat(this.value) || 0;
        const difference = newAmount - originalAmount;

        newAmountDisplay.textContent = newAmount.toFixed(2) + ' TND';

        const differenceText = (difference >= 0 ? '+' : '') + difference.toFixed(2) + ' TND';
        differenceDisplay.textContent = differenceText;
        differenceDisplay.className = `text-sm font-semibold ${difference >= 0 ? 'text-green-600' : 'text-red-600'}`;
    });
});
</script>