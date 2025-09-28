<!-- Modal d'assignation de livreur -->
<div id="assignDelivererModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <!-- Header du modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $package->assignedDeliverer ? 'Réassigner le livreur' : 'Assigner un livreur' }}
                    </h3>
                    <button onclick="closeModal('assignDelivererModal')" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('commercial.packages.assign.deliverer', $package) }}" method="POST" class="p-6">
                @csrf

                <!-- Information actuelle -->
                @if($package->assignedDeliverer)
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-yellow-800">
                            <strong>Livreur actuel :</strong> {{ $package->assignedDeliverer->first_name ?? $package->assignedDeliverer->name }} {{ $package->assignedDeliverer->last_name ?? '' }}
                        </span>
                    </div>
                </div>
                @endif

                <!-- Sélection du livreur -->
                <div class="mb-6">
                    <label for="deliverer_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner un livreur *
                    </label>
                    <div class="relative">
                        <select name="deliverer_id" id="deliverer_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                            <option value="">-- Choisir un livreur --</option>
                            <!-- Les livreurs seront chargés via JavaScript -->
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="assignment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (optionnel)
                    </label>
                    <textarea name="notes" id="assignment_notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        placeholder="Ajouter des instructions spéciales pour le livreur..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeModal('assignDelivererModal')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $package->assignedDeliverer ? 'Réassigner' : 'Assigner' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Charger les livreurs disponibles
document.addEventListener('DOMContentLoaded', function() {
    fetch('/commercial/api/deliverers/available')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('deliverer_id');
            select.innerHTML = '<option value="">-- Choisir un livreur --</option>';

            data.forEach(deliverer => {
                const option = document.createElement('option');
                option.value = deliverer.id;
                option.textContent = `${deliverer.name} (${deliverer.phone || 'N/A'})`;

                // Marquer le livreur actuel comme sélectionné
                @if($package->assignedDeliverer)
                if (deliverer.id === {{ $package->assignedDeliverer->id }}) {
                    option.selected = true;
                }
                @endif

                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement des livreurs:', error);
            showToast('Erreur lors du chargement des livreurs', 'error');
        });
});
</script>