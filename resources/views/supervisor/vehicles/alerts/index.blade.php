<x-layouts.supervisor-new>
    <x-slot name="title">Alertes Maintenance Véhicules</x-slot>
    <x-slot name="subtitle">Suivi des maintenances à effectuer</x-slot>

    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Alertes</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $alerts->total() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Critiques</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $alerts->where('severity', 'CRITICAL')->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Avertissements</p>
                <p class="text-3xl font-bold text-orange-600 mt-2">{{ $alerts->where('severity', 'WARNING')->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Non Lues</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $alerts->where('is_read', false)->count() }}</p>
            </div>
        </div>

        <!-- Alerts List -->
        <div class="space-y-4">
            @forelse($alerts as $alert)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4 flex-1">
                        <div class="w-12 h-12 {{ $alert->severity === 'CRITICAL' ? 'bg-red-100' : ($alert->severity === 'WARNING' ? 'bg-orange-100' : 'bg-blue-100') }} rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 {{ $alert->severity === 'CRITICAL' ? 'text-red-600' : ($alert->severity === 'WARNING' ? 'text-orange-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $alert->severity === 'CRITICAL' ? 'bg-red-100 text-red-800' : ($alert->severity === 'WARNING' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $alert->severity }}
                                </span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">
                                    {{ $alert->alert_type === 'oil' ? 'Vidange' : ($alert->alert_type === 'spark_plug' ? 'Bougies' : 'Pneus') }}
                                </span>
                            </div>

                            <h4 class="font-semibold text-gray-900">{{ $alert->vehicle->name }} - {{ $alert->vehicle->registration_number }}</h4>
                            <p class="text-gray-600 mt-1">{{ $alert->message }}</p>

                            <div class="mt-3 flex items-center space-x-4 text-sm text-gray-600">
                                <span>KM Actuel: {{ number_format($alert->current_km) }}</span>
                                <span>Prochaine: {{ number_format($alert->next_maintenance_km) }}</span>
                                <span>{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ml-4 flex items-center space-x-2">
                        @if(!$alert->is_read)
                        <form action="{{ route('supervisor.vehicles.alerts.mark-read', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">
                                Marquer Lu
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('supervisor.vehicles.show', $alert->vehicle) }}" 
                           class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                            Voir Véhicule
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">✅ Aucune Alerte</h3>
                <p class="text-gray-600">Tous les véhicules sont à jour pour la maintenance</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($alerts->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $alerts->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
