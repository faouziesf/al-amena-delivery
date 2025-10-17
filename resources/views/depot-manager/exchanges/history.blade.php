@extends('layouts.depot-manager')

@section('title', 'Historique des Échanges Traités')

@section('page-title', '📜 Historique des Échanges')

@section('content')
<div class="p-6 space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-gray-600">Consultez les colis échanges déjà traités</p>
        </div>
        <a href="{{ route('depot-manager.exchanges.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux échanges
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Total des échanges traités</p>
                <p class="text-5xl font-bold mt-2">{{ $processedExchanges->total() }}</p>
            </div>
            <div class="bg-white bg-opacity-20 rounded-full p-5">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Liste des Échanges Traités -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-green-100">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Échanges Traités ({{ $processedExchanges->total() }})
            </h3>
        </div>
        
        @if($processedExchanges->isEmpty())
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h4 class="text-xl font-semibold text-gray-600 mb-2">Aucun échange traité</h4>
            <p class="text-gray-500">Les échanges traités apparaîtront ici</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Colis Original</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code Retour</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Livré le</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Traité par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut Retour</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($processedExchanges as $package)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    ✓
                                </span>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $package->package_code }}</div>
                                    <div class="text-sm text-gray-500">{{ $package->recipient_data['name'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($package->returnPackage)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                    ↩
                                </span>
                                <span class="font-medium text-gray-900">{{ $package->returnPackage->return_package_code }}</span>
                            </div>
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $package->sender->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $package->sender_data['phone'] ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $package->delivered_at ? $package->delivered_at->format('d/m/Y') : 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $package->delivered_at ? $package->delivered_at->format('H:i') : '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($package->returnPackage)
                            <div class="text-sm font-medium text-gray-900">{{ $package->returnPackage->depot_manager_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $package->returnPackage->created_at ? $package->returnPackage->created_at->format('d/m/Y H:i') : '' }}</div>
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($package->returnPackage)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                @if($package->returnPackage->status === 'AT_DEPOT') bg-blue-100 text-blue-700
                                @elseif($package->returnPackage->status === 'ASSIGNED') bg-purple-100 text-purple-700
                                @elseif($package->returnPackage->status === 'DELIVERED') bg-green-100 text-green-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                {{ $package->returnPackage->status }}
                            </span>
                            @else
                            <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($package->returnPackage)
                            <a href="{{ route('depot-manager.exchanges.print', $package->returnPackage) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Imprimer
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        @if($processedExchanges->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $processedExchanges->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
