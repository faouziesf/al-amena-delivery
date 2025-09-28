<!-- Modal de mise à jour du statut -->
<div id="updateStatusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <!-- Header du modal -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Changer le statut
                    </h3>
                    <button onclick="closeModal('updateStatusModal')" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('commercial.packages.update.status', $package) }}" method="POST" class="p-6">
                @csrf

                <!-- Statut actuel -->
                <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-purple-800 font-medium">Statut actuel :</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            @if($package->status === 'CREATED') bg-slate-100 text-slate-800
                            @elseif($package->status === 'AVAILABLE') bg-blue-100 text-blue-800
                            @elseif($package->status === 'ACCEPTED') bg-yellow-100 text-yellow-800
                            @elseif($package->status === 'PICKED_UP') bg-orange-100 text-orange-800
                            @elseif($package->status === 'DELIVERED') bg-green-100 text-green-800
                            @elseif($package->status === 'RETURNED') bg-red-100 text-red-800
                            @elseif($package->status === 'CANCELLED') bg-slate-100 text-slate-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            {{ $package->status }}
                        </span>
                    </div>
                </div>

                <!-- Nouveau statut -->
                <div class="mb-6">
                    <label for="new_status" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau statut *
                    </label>
                    <select name="new_status" id="new_status" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                        <option value="">-- Sélectionner un nouveau statut --</option>
                        <option value="AVAILABLE" @if($package->status === 'AVAILABLE') disabled @endif>
                            AVAILABLE - Disponible pour les livreurs
                        </option>
                        <option value="ACCEPTED" @if($package->status === 'ACCEPTED') disabled @endif>
                            ACCEPTED - Accepté par un livreur
                        </option>
                        <option value="PICKED_UP" @if($package->status === 'PICKED_UP') disabled @endif>
                            PICKED_UP - Collecté par le livreur
                        </option>
                        <option value="DELIVERED" @if($package->status === 'DELIVERED') disabled @endif>
                            DELIVERED - Livré au destinataire
                        </option>
                        <option value="RETURNED" @if($package->status === 'RETURNED') disabled @endif>
                            RETURNED - Retourné à l'expéditeur
                        </option>
                        <option value="CANCELLED" @if($package->status === 'CANCELLED') disabled @endif>
                            CANCELLED - Annulé
                        </option>
                    </select>
                </div>

                <!-- Assignation de livreur (conditionnel) -->
                <div class="mb-6" id="deliverer_assignment_field" style="display: none;">
                    <label for="status_deliverer_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Assigner à un livreur *
                    </label>
                    <select name="deliverer_id" id="status_deliverer_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white">
                        <option value="">-- Choisir un livreur --</option>
                        <!-- Les livreurs seront chargés via JavaScript -->
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="status_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (optionnel)
                    </label>
                    <textarea name="notes" id="status_notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                        placeholder="Ajouter des notes sur ce changement de statut..."></textarea>
                </div>

                <!-- Alerte pour les changements critiques -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg" id="critical_change_warning" style="display: none;">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.963-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <p class="text-yellow-800 font-medium mb-1">Attention !</p>
                            <p class="text-yellow-700" id="warning_message"></p>
                        </div>
                    </div>
                </div>

                <!-- Résumé du changement -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Résumé du changement :</h4>
                    <div class="flex items-center justify-center space-x-4">
                        <div class="text-center">
                            <div class="text-xs text-gray-500 mb-1">Statut actuel</div>
                            <span class="inline-block px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm font-medium">
                                {{ $package->status }}
                            </span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                        <div class="text-center">
                            <div class="text-xs text-gray-500 mb-1">Nouveau statut</div>
                            <span class="inline-block px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm font-medium" id="new_status_preview">
                                Non sélectionné
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeModal('updateStatusModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Changer le statut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newStatusSelect = document.getElementById('new_status');
    const delivererField = document.getElementById('deliverer_assignment_field');
    const delivererSelect = document.getElementById('status_deliverer_id');
    const warningDiv = document.getElementById('critical_change_warning');
    const warningMessage = document.getElementById('warning_message');
    const newStatusPreview = document.getElementById('new_status_preview');

    const currentStatus = '{{ $package->status }}';

    // Charger les livreurs disponibles
    fetch('/commercial/api/deliverers/available')
        .then(response => response.json())
        .then(data => {
            delivererSelect.innerHTML = '<option value="">-- Choisir un livreur --</option>';
            data.forEach(deliverer => {
                const option = document.createElement('option');
                option.value = deliverer.id;
                option.textContent = `${deliverer.name} (${deliverer.phone || 'N/A'})`;
                delivererSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des livreurs:', error);
        });

    // Gestion du changement de statut
    newStatusSelect.addEventListener('change', function() {
        const newStatus = this.value;
        newStatusPreview.textContent = newStatus || 'Non sélectionné';

        // Afficher le champ livreur si nécessaire
        if (newStatus === 'ACCEPTED') {
            delivererField.style.display = 'block';
            delivererSelect.setAttribute('required', 'required');
        } else {
            delivererField.style.display = 'none';
            delivererSelect.removeAttribute('required');
        }

        // Afficher les avertissements
        warningDiv.style.display = 'none';

        if (newStatus === 'DELIVERED' && !['PICKED_UP'].includes(currentStatus)) {
            warningDiv.style.display = 'block';
            warningMessage.textContent = 'Vous marquez ce colis comme livré sans qu\'il ait été collecté. Assurez-vous que c\'est correct.';
        } else if (newStatus === 'CANCELLED' && ['DELIVERED', 'RETURNED'].includes(currentStatus)) {
            warningDiv.style.display = 'block';
            warningMessage.textContent = 'Vous annulez un colis qui a déjà été traité. Cette action peut avoir des implications financières.';
        } else if (newStatus === 'RETURNED' && currentStatus === 'DELIVERED') {
            warningDiv.style.display = 'block';
            warningMessage.textContent = 'Vous retournez un colis marqué comme livré. Assurez-vous que cette action est justifiée.';
        }
    });
});
</script>