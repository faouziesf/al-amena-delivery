<x-layouts.supervisor-new>
    <x-slot name="title">Nouveau Véhicule</x-slot>
    <x-slot name="subtitle">Ajouter un véhicule à la flotte</x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('supervisor.vehicles.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informations Générales -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Générales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom du Véhicule <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="registration_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Immatriculation <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('registration_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-2">
                            Prix d'Achat (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" step="0.001" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('purchase_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="max_depreciation_km" class="block text-sm font-medium text-gray-700 mb-2">
                            KM Max Amortissement <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="max_depreciation_km" name="max_depreciation_km" value="{{ old('max_depreciation_km', 300000) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('max_depreciation_km')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="current_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Kilométrage Actuel <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="current_km" name="current_km" value="{{ old('current_km', 0) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('current_km')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Maintenance Vidange -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance - Vidange</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="oil_change_cost" class="block text-sm font-medium text-gray-700 mb-2">
                            Coût Vidange (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="oil_change_cost" name="oil_change_cost" value="{{ old('oil_change_cost', 50) }}" step="0.001" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="oil_change_interval_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Intervalle (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="oil_change_interval_km" name="oil_change_interval_km" value="{{ old('oil_change_interval_km', 10000) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="last_oil_change_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Dernière Vidange (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="last_oil_change_km" name="last_oil_change_km" value="{{ old('last_oil_change_km', 0) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Maintenance Bougies -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance - Bougies</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="spark_plug_cost" class="block text-sm font-medium text-gray-700 mb-2">
                            Coût Bougies (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="spark_plug_cost" name="spark_plug_cost" value="{{ old('spark_plug_cost', 80) }}" step="0.001" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="spark_plug_interval_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Intervalle (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="spark_plug_interval_km" name="spark_plug_interval_km" value="{{ old('spark_plug_interval_km', 30000) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="last_spark_plug_change_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Dernier Changement (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="last_spark_plug_change_km" name="last_spark_plug_change_km" value="{{ old('last_spark_plug_change_km', 0) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Maintenance Pneus -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Maintenance - Pneus</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="tire_unit_cost" class="block text-sm font-medium text-gray-700 mb-2">
                            Coût Unitaire Pneu (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="tire_unit_cost" name="tire_unit_cost" value="{{ old('tire_unit_cost', 120) }}" step="0.001" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="tire_change_interval_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Intervalle (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="tire_change_interval_km" name="tire_change_interval_km" value="{{ old('tire_change_interval_km', 50000) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="last_tire_change_km" class="block text-sm font-medium text-gray-700 mb-2">
                            Dernier Changement (KM) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="last_tire_change_km" name="last_tire_change_km" value="{{ old('last_tire_change_km', 0) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Carburant -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Carburant</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fuel_price_per_liter" class="block text-sm font-medium text-gray-700 mb-2">
                            Prix/Litre (DT) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="fuel_price_per_liter" name="fuel_price_per_liter" value="{{ old('fuel_price_per_liter', 2.15) }}" step="0.001" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="average_fuel_consumption" class="block text-sm font-medium text-gray-700 mb-2">
                            Consommation Moyenne (L/100km)
                        </label>
                        <input type="number" id="average_fuel_consumption" name="average_fuel_consumption" value="{{ old('average_fuel_consumption', 7) }}" step="0.1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('supervisor.vehicles.index') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Créer le Véhicule
                </button>
            </div>
        </form>
    </div>
</x-layouts.supervisor-new>
