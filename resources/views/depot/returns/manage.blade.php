@extends('layouts.depot-manager')

@section('title', 'Gestion Colis Retours')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📦 Gestion des Colis Retours</h1>
                    <p class="text-gray-600 mt-1">Liste de tous les colis retours créés</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('depot.returns.dashboard') }}" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold transition-all shadow-lg">
                        + Nouveau Scan
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Retours</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $returnPackages->total() }}</p>
                    </div>
                    <div class="text-3xl">📦</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Au Dépôt</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $returnPackages->where('status', 'AT_DEPOT')->count() }}</p>
                    </div>
                    <div class="text-3xl">🏭</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Non Imprimés</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $returnPackages->where('printed_at', null)->count() }}</p>
                    </div>
                    <div class="text-3xl">🖨️</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Livrés</p>
                        <p class="text-2xl font-bold text-green-600">{{ $returnPackages->where('status', 'DELIVERED')->count() }}</p>
                    </div>
                    <div class="text-3xl">✅</div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code Retour</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colis Original</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imprimé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($returnPackages as $returnPkg)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-orange-900">{{ $returnPkg->return_package_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono text-sm text-gray-600">{{ $returnPkg->originalPackage->package_code ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $returnPkg->recipient_info['name'] ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $returnPkg->recipient_info['phone'] ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($returnPkg->status === 'AT_DEPOT') bg-blue-100 text-blue-800
                                    @elseif($returnPkg->status === 'DELIVERED') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $returnPkg->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($returnPkg->printed_at)
                                    <span class="text-green-600">✅ {{ $returnPkg->printed_at->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="text-orange-600">❌ Non</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $returnPkg->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('depot.returns.show', $returnPkg) }}" class="text-blue-600 hover:text-blue-900">Détails</a>
                                    @if(!$returnPkg->printed_at)
                                    <a href="{{ route('depot.returns.print', $returnPkg) }}" target="_blank" class="text-orange-600 hover:text-orange-900">Imprimer</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <div class="text-4xl mb-2">📭</div>
                                <p>Aucun colis retour créé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($returnPackages->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $returnPackages->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
