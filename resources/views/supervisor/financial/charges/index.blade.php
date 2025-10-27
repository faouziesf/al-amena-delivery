<x-layouts.supervisor-new>
    <x-slot name="title">Charges Fixes</x-slot>
    <x-slot name="subtitle">Gestion des charges récurrentes de l'entreprise</x-slot>

    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('supervisor.financial.charges.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Nouvelle Charge</span>
                </a>

                <a href="{{ route('supervisor.financial.charges.import.template') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Template CSV</span>
                </a>

                <button @click="$refs.importFile.click()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>Importer CSV</span>
                </button>
                
                <form x-ref="importForm" action="{{ route('supervisor.financial.charges.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" x-ref="importFile" name="file" accept=".csv" @change="$refs.importForm.submit()">
                </form>
            </div>

            <a href="{{ route('supervisor.financial.charges.export') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exporter</span>
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Total Charges Actives</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $charges->where('is_active', true)->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Équivalent Mensuel Total</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">
                    {{ number_format($charges->where('is_active', true)->sum('monthly_equivalent'), 3) }} DT
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Charges Mensuelles</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">
                    {{ $charges->where('is_active', true)->where('periodicity', 'MONTHLY')->count() }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-gray-600 text-sm">Charges Annuelles</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">
                    {{ $charges->where('is_active', true)->where('periodicity', 'YEARLY')->count() }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Nom de la charge..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Périodicité</label>
                    <select name="periodicity" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Toutes</option>
                        <option value="DAILY" {{ request('periodicity') === 'DAILY' ? 'selected' : '' }}>Journalière</option>
                        <option value="WEEKLY" {{ request('periodicity') === 'WEEKLY' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="MONTHLY" {{ request('periodicity') === 'MONTHLY' ? 'selected' : '' }}>Mensuelle</option>
                        <option value="YEARLY" {{ request('periodicity') === 'YEARLY' ? 'selected' : '' }}>Annuelle</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        Filtrer
                    </button>
                    <a href="{{ route('supervisor.financial.charges.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Charges Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Périodicité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Équiv. Mensuel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($charges as $charge)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $charge->name }}</div>
                                    @if($charge->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($charge->description, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ number_format($charge->amount, 3) }} DT</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($charge->periodicity === 'DAILY') bg-purple-100 text-purple-800
                                @elseif($charge->periodicity === 'WEEKLY') bg-blue-100 text-blue-800
                                @elseif($charge->periodicity === 'MONTHLY') bg-green-100 text-green-800
                                @else bg-orange-100 text-orange-800
                                @endif">
                                @if($charge->periodicity === 'DAILY') Journalière
                                @elseif($charge->periodicity === 'WEEKLY') Hebdomadaire
                                @elseif($charge->periodicity === 'MONTHLY') Mensuelle
                                @else Annuelle
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-blue-600">{{ number_format($charge->monthly_equivalent, 3) }} DT</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($charge->is_active)
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Actif
                            </span>
                            @else
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Inactif
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('supervisor.financial.charges.show', $charge) }}" 
                               class="text-blue-600 hover:text-blue-900">Voir</a>
                            <a href="{{ route('supervisor.financial.charges.edit', $charge) }}" 
                               class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                            <form action="{{ route('supervisor.financial.charges.destroy', $charge) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette charge ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-lg font-medium">Aucune charge trouvée</p>
                            <p class="text-sm mt-2">Commencez par créer votre première charge fixe</p>
                            <a href="{{ route('supervisor.financial.charges.create') }}" 
                               class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                Créer une charge
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($charges->hasPages())
        <div class="bg-white rounded-xl shadow px-6 py-4">
            {{ $charges->links() }}
        </div>
        @endif
    </div>
</x-layouts.supervisor-new>
