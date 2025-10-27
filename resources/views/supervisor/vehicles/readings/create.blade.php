<x-layouts.supervisor-new>
    <x-slot name="title">Nouveau Relevé Kilométrique</x-slot>
    <x-slot name="subtitle">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow p-8">
            <form action="{{ route('supervisor.vehicles.readings.store', $vehicle) }}" method="POST">
                @csrf

                <!-- Info Véhicule -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-700">Véhicule: <span class="font-semibold">{{ $vehicle->name }}</span></p>
                    <p class="text-sm text-gray-700">KM Actuel: <span class="font-semibold">{{ number_format($vehicle->current_km) }} km</span></p>
                </div>

                <!-- Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date du Relevé *</label>
                    <input type="date" name="reading_date" value="{{ old('reading_date', now()->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Kilométrage -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau Kilométrage *</label>
                    <input type="number" name="mileage" min="{{ $vehicle->current_km }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Exemple: {{ $vehicle->current_km + 100 }}">
                    <p class="mt-1 text-sm text-gray-500">Doit être supérieur à {{ number_format($vehicle->current_km) }} km</p>
                </div>

                <!-- Litres Carburant -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Litres de Carburant Consommés</label>
                    <input type="number" step="0.01" name="fuel_liters" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Exemple: 45.5">
                    <p class="mt-1 text-sm text-gray-500">Optionnel - Pour calculer la consommation</p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Observations, remarques..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('supervisor.vehicles.show', $vehicle) }}" 
                       class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                        Enregistrer Relevé
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.supervisor-new>
