<x-layouts.supervisor-new>
    <x-slot name="title">Gestion des Véhicules</x-slot>
    <x-slot name="subtitle">Suivi des véhicules et coûts d'exploitation</x-slot>

    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('supervisor.vehicles.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Nouveau Véhicule</span>
            </a>

            <a href="{{ route('supervisor.vehicles.alerts') }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Alertes Maintenance</span>
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Véhicules</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $vehicles->total() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Coût Moyen /km</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">
                    {{ number_format($vehicles->avg('total_cost_per_km') ?? 0, 3) }} DT
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">KM Total</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">
                    {{ number_format($vehicles->sum('current_km')) }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Alertes Actives</p>
                <p class="text-2xl font-bold text-red-600 mt-2" x-data x-init="
                    fetch('/supervisor/api/vehicles/alerts-count')
                        .then(r => r.json())
                        .then(data => $el.textContent = data.count || 0)
                ">0</p>
            </div>
        </div>

        <!-- Vehicles List -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($vehicles as $vehicle)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $vehicle->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $vehicle->registration_number }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($vehicle->alerts_count > 0)
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                {{ $vehicle->alerts_count }} alerte(s)
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Kilométrage</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($vehicle->current_km) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">Coût/km</p>
                            <p class="text-lg font-bold text-blue-600">{{ number_format($vehicle->total_cost_per_km, 3) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600">KM Moyen/J</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($vehicle->average_daily_km ?? 0) }}</p>
                        </div>
                    </div>

                    <!-- Maintenance Indicators -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Prochaine vidange:</span>
                            <span class="font-semibold {{ $vehicle->km_until_oil_change < 500 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($vehicle->km_until_oil_change) }} km
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Bougies:</span>
                            <span class="font-semibold {{ $vehicle->km_until_spark_plug_change < 1000 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ number_format($vehicle->km_until_spark_plug_change) }} km
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('supervisor.vehicles.show', $vehicle) }}" 
                           class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition">
                            Détails
                        </a>
                        <a href="{{ route('supervisor.vehicles.readings.create', $vehicle) }}" 
                           class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition">
                            + Relevé
                        </a>
                        <a href="{{ route('supervisor.vehicles.edit', $vehicle) }}" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm transition">
                            Modifier
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 bg-white rounded-xl shadow p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun véhicule</h3>
                <p class="text-gray-600 mb-6">Commencez par ajouter votre premier véhicule</p>
                <a href="{{ route('supervisor.vehicles.create') }}" 
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                    Ajouter un véhicule
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($vehicles->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
