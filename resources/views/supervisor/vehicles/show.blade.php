<x-layouts.supervisor-new>
    <x-slot name="title">{{ $vehicle->name }}</x-slot>
    <x-slot name="subtitle">{{ $vehicle->registration_number }}</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('supervisor.vehicles.readings.create', $vehicle) }}" 
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                + Nouveau Relevé
            </a>
            <a href="{{ route('supervisor.vehicles.edit', $vehicle) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Modifier
            </a>
        </div>

        <!-- Stats Principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Kilométrage</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($vehicle->current_km) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Coût Total /km</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($vehicle->total_cost_per_km, 3) }} DT</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">KM Moyen/Jour</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($vehicle->average_daily_km ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Alertes</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $vehicle->unread_alerts_count ?? 0 }}</p>
            </div>
        </div>

        <!-- Coûts Détaillés -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails Coûts /km</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600">Amortissement</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($vehicle->depreciation_cost_per_km, 3) }}</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-600">Vidange</p>
                    <p class="text-lg font-bold text-blue-900">{{ number_format($vehicle->oil_change_cost_per_km, 3) }}</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-600">Bougies</p>
                    <p class="text-lg font-bold text-green-900">{{ number_format($vehicle->spark_plug_cost_per_km, 3) }}</p>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <p class="text-xs text-purple-600">Pneus</p>
                    <p class="text-lg font-bold text-purple-900">{{ number_format($vehicle->tire_cost_per_km, 3) }}</p>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <p class="text-xs text-orange-600">Carburant</p>
                    <p class="text-lg font-bold text-orange-900">{{ number_format($vehicle->fuel_cost_per_km, 3) }}</p>
                </div>
            </div>
        </div>

        <!-- Maintenance -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">État Maintenance</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Vidange</p>
                        <p class="text-sm text-gray-600">Dernière: {{ number_format($vehicle->last_oil_change_km) }} km</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Dans</p>
                        <p class="text-xl font-bold {{ $vehicle->km_until_oil_change < 500 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($vehicle->km_until_oil_change) }} km
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Bougies</p>
                        <p class="text-sm text-gray-600">Dernier: {{ number_format($vehicle->last_spark_plug_change_km) }} km</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Dans</p>
                        <p class="text-xl font-bold {{ $vehicle->km_until_spark_plug_change < 1000 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ number_format($vehicle->km_until_spark_plug_change) }} km
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Pneus</p>
                        <p class="text-sm text-gray-600">Dernier: {{ number_format($vehicle->last_tire_change_km) }} km</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Dans</p>
                        <p class="text-xl font-bold text-green-600">
                            {{ number_format($vehicle->km_until_tire_change) }} km
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relevés Récents -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Relevés Récents</h3>
            @if($vehicle->mileageReadings && $vehicle->mileageReadings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kilométrage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">KM Parcourus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">KM/Jour</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($vehicle->mileageReadings->take(10) as $reading)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reading->reading_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">{{ number_format($reading->mileage) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ number_format($reading->km_since_last ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reading->working_days_since_last ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ number_format($reading->average_daily_km ?? 0, 1) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-8">Aucun relevé enregistré</p>
            @endif
        </div>
    </div>
</x-layouts.supervisor-new>
