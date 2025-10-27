<x-layouts.supervisor-new>
    <x-slot name="title">Détails Charge Fixe</x-slot>
    <x-slot name="subtitle">{{ $charge->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('supervisor.financial.charges.edit', $charge) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                Modifier
            </a>
            <form action="{{ route('supervisor.financial.charges.destroy', $charge) }}" method="POST" class="inline"
                  onsubmit="return confirm('Confirmer la suppression ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Supprimer
                </button>
            </form>
        </div>

        <!-- Informations Principales -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600">Nom</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $charge->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Statut</p>
                    @if($charge->is_active)
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">Actif</span>
                    @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm font-semibold rounded-full">Inactif</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600">Montant</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($charge->amount, 3) }} DT</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Périodicité</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if($charge->periodicity === 'DAILY') Journalière
                        @elseif($charge->periodicity === 'WEEKLY') Hebdomadaire
                        @elseif($charge->periodicity === 'MONTHLY') Mensuelle
                        @else Annuelle @endif
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600">Équivalent Mensuel</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($charge->monthly_equivalent, 3) }} DT</p>
                </div>
                @if($charge->description)
                <div class="col-span-2">
                    <p class="text-sm text-gray-600">Description</p>
                    <p class="text-gray-900">{{ $charge->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Calculs -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Calculs Prévisionnels</h3>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-600">Ce Mois</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($charge->monthly_equivalent, 3) }} DT</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-600">Trimestre</p>
                    <p class="text-2xl font-bold text-green-900">{{ number_format($charge->monthly_equivalent * 3, 3) }} DT</p>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <p class="text-sm text-purple-600">Année</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($charge->monthly_equivalent * 12, 3) }} DT</p>
                </div>
            </div>
        </div>

        <!-- Métadonnées -->
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Système</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Créé par</p>
                    <p class="text-gray-900 font-medium">{{ $charge->creator?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Créé le</p>
                    <p class="text-gray-900 font-medium">{{ $charge->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($charge->updated_at != $charge->created_at)
                <div>
                    <p class="text-gray-600">Dernière modification</p>
                    <p class="text-gray-900 font-medium">{{ $charge->updated_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.supervisor-new>
