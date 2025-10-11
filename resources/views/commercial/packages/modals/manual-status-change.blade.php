<!-- Modal de changement manuel de statut (avec raison obligatoire) -->
<div id="manualStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
            <!-- Header du modal -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Changement Manuel de Statut
                    </h3>
                    <button onclick="closeModal('manualStatusModal')" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-purple-100 text-sm mt-2">Intervention manuelle avec justification obligatoire</p>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('commercial.packages.change.status', $package) }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <!-- Statut actuel -->
                <div class="mb-6 p-4 bg-purple-50 border-2 border-purple-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-purple-800 font-medium">Statut actuel :</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            @if($package->status === 'CREATED') bg-slate-100 text-slate-800
                            @elseif($package->status === 'AVAILABLE') bg-blue-100 text-blue-800
                            @elseif($package->status === 'AT_DEPOT') bg-cyan-100 text-cyan-800
                            @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                            @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                            @elseif($package->status === 'UNAVAILABLE') bg-yellow-100 text-yellow-800
                            @elseif($package->status === 'REFUSED') bg-red-100 text-red-800
                            @elseif($package->status === 'AWAITING_RETURN') bg-orange-100 text-orange-800
                            @elseif($package->status === 'RETURN_IN_PROGRESS') bg-amber-100 text-amber-800
                            @elseif($package->status === 'RETURNED_TO_CLIENT') bg-rose-100 text-rose-800
                            @elseif($package->status === 'RETURN_CONFIRMED') bg-slate-100 text-slate-800
                            @elseif($package->status === 'RETURN_ISSUE') bg-red-100 text-red-800
                            @elseif($package->status === 'PAID') bg-emerald-100 text-emerald-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3"/>
                            </svg>
                            {{ $package->status }}
                        </span>
                    </div>
                </div>

                <!-- Nouveau statut -->
                <div class="mb-6">
                    <label for="manual_new_status" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau statut * <span class="text-red-500 text-xs">(requis)</span>
                    </label>
                    <select name="new_status" id="manual_new_status" required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white transition-all">
                        <option value="">-- Sélectionner un nouveau statut --</option>
                        <option value="CREATED" @if($package->status === 'CREATED') disabled class="bg-gray-100" @endif>CREATED - Colis créé</option>
                        <option value="AVAILABLE" @if($package->status === 'AVAILABLE') disabled class="bg-gray-100" @endif>AVAILABLE - Disponible pour livraison</option>
                        <option value="AT_DEPOT" @if($package->status === 'AT_DEPOT') disabled class="bg-gray-100" @endif>AT_DEPOT - Au dépôt</option>
                        <option value="PICKED_UP" @if($package->status === 'PICKED_UP') disabled class="bg-gray-100" @endif>PICKED_UP - Collecté</option>
                        <option value="DELIVERED" @if($package->status === 'DELIVERED') disabled class="bg-gray-100" @endif>DELIVERED - Livré</option>
                        <option value="UNAVAILABLE" @if($package->status === 'UNAVAILABLE') disabled class="bg-gray-100" @endif>UNAVAILABLE - Destinataire indisponible</option>
                        <option value="REFUSED" @if($package->status === 'REFUSED') disabled class="bg-gray-100" @endif>REFUSED - Refusé</option>
                        <option value="AWAITING_RETURN" @if($package->status === 'AWAITING_RETURN') disabled class="bg-gray-100" @endif>AWAITING_RETURN - En attente de retour</option>
                        <option value="RETURN_IN_PROGRESS" @if($package->status === 'RETURN_IN_PROGRESS') disabled class="bg-gray-100" @endif>RETURN_IN_PROGRESS - Retour en cours</option>
                        <option value="RETURNED_TO_CLIENT" @if($package->status === 'RETURNED_TO_CLIENT') disabled class="bg-gray-100" @endif>RETURNED_TO_CLIENT - Retourné au client</option>
                        <option value="RETURN_CONFIRMED" @if($package->status === 'RETURN_CONFIRMED') disabled class="bg-gray-100" @endif>RETURN_CONFIRMED - Retour confirmé</option>
                        <option value="RETURN_ISSUE" @if($package->status === 'RETURN_ISSUE') disabled class="bg-gray-100" @endif>RETURN_ISSUE - Problème de retour</option>
                        <option value="PAID" @if($package->status === 'PAID') disabled class="bg-gray-100" @endif>PAID - Payé</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Les statuts grisés sont indisponibles (statut actuel ou transitions invalides)</p>
                </div>

                <!-- Raison du changement (OBLIGATOIRE) -->
                <div class="mb-6">
                    <label for="change_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Raison du changement * <span class="text-red-500 text-xs">(obligatoire, max 500 caractères)</span>
                    </label>
                    <textarea name="change_reason" id="change_reason" rows="4" required maxlength="500"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none transition-all"
                        placeholder="Expliquez pourquoi vous changez manuellement ce statut...&#10;&#10;Exemples:&#10;- Erreur de scan lors de la livraison&#10;- Problème signalé par le client&#10;- Correction suite à réclamation&#10;- Instruction spéciale du superviseur"></textarea>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-xs text-gray-500">Cette raison sera enregistrée dans les logs</p>
                        <p class="text-xs text-gray-400" id="char_count">0/500</p>
                    </div>
                </div>

                <!-- Alerte pour les changements critiques -->
                <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-lg" id="manual_critical_warning" style="display: none;">
                    <div class="flex">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <p class="text-red-800 font-bold mb-1">⚠️ ATTENTION - Changement Critique !</p>
                            <p class="text-red-700 font-medium" id="manual_warning_message"></p>
                        </div>
                    </div>
                </div>

                <!-- Résumé du changement -->
                <div class="mb-6 p-4 bg-gradient-to-r from-gray-50 to-purple-50 border-2 border-purple-200 rounded-lg">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Résumé de la modification
                    </h4>
                    <div class="flex items-center justify-center space-x-4">
                        <div class="text-center flex-1">
                            <div class="text-xs text-gray-500 mb-1 font-medium">De</div>
                            <span class="inline-block px-3 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-bold border-2 border-gray-300">
                                {{ $package->status }}
                            </span>
                        </div>
                        <svg class="w-6 h-6 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        <div class="text-center flex-1">
                            <div class="text-xs text-gray-500 mb-1 font-medium">Vers</div>
                            <span class="inline-block px-3 py-2 bg-purple-100 text-purple-800 rounded-lg text-sm font-bold border-2 border-purple-300" id="manual_new_status_preview">
                                Non sélectionné
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t-2 border-gray-200">
                    <button type="button" onclick="closeModal('manualStatusModal')"
                        class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                        Annuler
                    </button>
                    <button type="submit" id="manual_submit_btn"
                        class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all font-bold flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirmer le Changement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const manualNewStatusSelect = document.getElementById('manual_new_status');
    const manualWarningDiv = document.getElementById('manual_critical_warning');
    const manualWarningMessage = document.getElementById('manual_warning_message');
    const manualNewStatusPreview = document.getElementById('manual_new_status_preview');
    const changeReasonTextarea = document.getElementById('change_reason');
    const charCountSpan = document.getElementById('char_count');
    const submitBtn = document.getElementById('manual_submit_btn');

    const currentStatus = '{{ $package->status }}';

    // Compteur de caractères
    changeReasonTextarea.addEventListener('input', function() {
        const count = this.value.length;
        charCountSpan.textContent = `${count}/500`;

        if (count > 450) {
            charCountSpan.classList.add('text-red-500', 'font-bold');
        } else {
            charCountSpan.classList.remove('text-red-500', 'font-bold');
        }
    });

    // Gestion du changement de statut
    manualNewStatusSelect.addEventListener('change', function() {
        const newStatus = this.value;
        manualNewStatusPreview.textContent = newStatus || 'Non sélectionné';

        // Réinitialiser l'avertissement
        manualWarningDiv.style.display = 'none';
        submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        submitBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');

        // Vérifier les transitions critiques
        if (newStatus === 'PAID' && currentStatus !== 'DELIVERED') {
            manualWarningDiv.style.display = 'block';
            manualWarningMessage.textContent = 'Vous marquez ce colis comme PAYÉ alors qu\'il n\'est pas DELIVERED. Cette action peut avoir des conséquences financières graves !';
            submitBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
            submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else if (newStatus === 'DELIVERED' && !['AT_DEPOT', 'PICKED_UP', 'UNAVAILABLE'].includes(currentStatus)) {
            manualWarningDiv.style.display = 'block';
            manualWarningMessage.textContent = 'Vous marquez ce colis comme LIVRÉ sans qu\'il ait suivi le workflow normal. Vérifiez bien cette action !';
        } else if (newStatus === 'RETURN_CONFIRMED' && currentStatus !== 'RETURNED_TO_CLIENT') {
            manualWarningDiv.style.display = 'block';
            manualWarningMessage.textContent = 'Vous confirmez un retour sans que le colis soit retourné au client. Assurez-vous que c\'est justifié !';
        } else if (currentStatus === 'PAID') {
            manualWarningDiv.style.display = 'block';
            manualWarningMessage.textContent = 'DANGER : Vous modifiez un colis déjà PAYÉ. Cette action est bloquée par le système pour des raisons de sécurité financière.';
            submitBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
            submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else if (newStatus === 'AT_DEPOT' && currentStatus === 'AWAITING_RETURN') {
            // Cas spécial: 4ème tentative via changement manuel
            manualWarningDiv.style.display = 'block';
            manualWarningMessage.textContent = 'Vous relancez une tentative de livraison. Utilisez plutôt le bouton "Lancer 4ème Tentative" pour cette action.';
            manualWarningDiv.classList.remove('bg-red-50', 'border-red-200');
            manualWarningDiv.classList.add('bg-blue-50', 'border-blue-200');
            manualWarningMessage.classList.remove('text-red-700');
            manualWarningMessage.classList.add('text-blue-700');
        }
    });
});

// Fonction pour ouvrir le modal (appelée depuis la page principale)
function openManualStatusModal() {
    document.getElementById('manualStatusModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
</script>
