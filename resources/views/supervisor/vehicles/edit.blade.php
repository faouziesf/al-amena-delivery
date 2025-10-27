<x-layouts.supervisor-new>
    <x-slot name="title">Modifier Véhicule</x-slot>
    <x-slot name="subtitle">{{ $vehicle->name }} ({{ $vehicle->registration_number }})</x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('supervisor.vehicles.update', $vehicle) }}" method="POST" class="bg-white rounded-xl shadow p-8">
            @csrf
            @method('PUT')

            <!-- Informations Générales -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Générales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du Véhicule *</label>
                        <input type="text" name="name" value="{{ old('name', $vehicle->name) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Matricule *</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Prix et Amortissement -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Prix et Amortissement</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prix d'Achat (DT) *</label>
                        <input type="number" step="0.001" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">KM Max Amortissement *</label>
                        <input type="number" name="max_depreciation_km" value="{{ old('max_depreciation_km', $vehicle->max_depreciation_km) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Vidange -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vidange</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coût Vidange (DT) *</label>
                        <input type="number" step="0.001" name="oil_change_cost" value="{{ old('oil_change_cost', $vehicle->oil_change_cost) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Intervalle (KM) *</label>
                        <input type="number" name="oil_change_interval_km" value="{{ old('oil_change_interval_km', $vehicle->oil_change_interval_km) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Prix Carburant -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Carburant</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix par Litre (DT) *</label>
                    <input type="number" step="0.001" name="fuel_price_per_liter" value="{{ old('fuel_price_per_liter', $vehicle->fuel_price_per_liter) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('supervisor.vehicles.show', $vehicle) }}" 
                   class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Mettre à Jour
                </button>
            </div>
        </form>
    </div>
</x-layouts.supervisor-new>
