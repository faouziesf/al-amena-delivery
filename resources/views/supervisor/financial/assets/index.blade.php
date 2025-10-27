<x-layouts.supervisor-new>
    <x-slot name="title">Actifs Amortissables</x-slot>
    <x-slot name="subtitle">Gestion des équipements et actifs de l'entreprise</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex justify-between items-center">
            <div></div>
            <a href="{{ route('supervisor.financial.assets.create') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                + Nouvel Actif
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Actifs</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $assets->total() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Valeur Totale</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">0 DT</p>
                <p class="text-xs text-gray-500 mt-1">À implémenter</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Coût Mensuel</p>
                <p class="text-3xl font-bold text-green-600 mt-2">0 DT</p>
                <p class="text-xs text-gray-500 mt-1">À implémenter</p>
            </div>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-xl shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Liste des Actifs</h3>
                
                @if($assets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($assets as $asset)
                            <tr>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $asset->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $asset->description }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $asset->category ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    @if($asset->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Actif</span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Inactif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('supervisor.financial.assets.show', $asset) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $assets->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Aucun actif enregistré</p>
                    <a href="{{ route('supervisor.financial.assets.create') }}" 
                       class="mt-4 inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Créer le Premier Actif
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.supervisor-new>
