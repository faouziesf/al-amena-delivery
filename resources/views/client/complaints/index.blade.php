@extends('layouts.client')

@section('title', 'Mes Réclamations')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- Header --}}
    <div class="mb-6 lg:mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    ⚠️ Mes Réclamations
                </h1>
                <p class="text-gray-600 text-sm sm:text-base">
                    Gérez vos réclamations concernant vos colis
                </p>
            </div>
            
            <a href="{{ route('client.packages.index') }}" 
               class="inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle Réclamation
            </a>
        </div>
    </div>

    @if($complaints->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune réclamation</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Vous n'avez aucune réclamation en cours. Si vous rencontrez un problème avec un colis, vous pouvez créer une réclamation.
            </p>
            <a href="{{ route('client.packages.index') }}" 
               class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Voir mes colis
            </a>
        </div>
    @else
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            @php
                $total = $complaints->total();
                $open = $complaints->where('status', 'OPEN')->count();
                $inProgress = $complaints->where('status', 'IN_PROGRESS')->count();
                $resolved = $complaints->where('status', 'RESOLVED')->count();
            @endphp
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm text-gray-600">Total</span>
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $total }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm text-blue-600">Ouvertes</span>
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $open }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm text-amber-600">En cours</span>
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-amber-600">{{ $inProgress }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm text-green-600">Résolues</span>
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $resolved }}</p>
            </div>
        </div>

        {{-- Mobile: Cards List --}}
        <div class="lg:hidden space-y-4">
            @foreach($complaints as $complaint)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium text-gray-500">
                                    #{{ $complaint->id }}
                                </span>
                                @php
                                    $statusConfig = match($complaint->status) {
                                        'OPEN' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Ouverte'],
                                        'IN_PROGRESS' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'En cours'],
                                        'RESOLVED' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Résolue'],
                                        'CLOSED' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Fermée'],
                                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $complaint->status],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>
                            <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">
                                {{ $complaint->subject }}
                            </h3>
                            @if($complaint->package)
                                <p class="text-xs text-gray-600">
                                    Colis: <span class="font-medium">{{ $complaint->package->package_code }}</span>
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                        {{ $complaint->description }}
                    </p>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-500">
                            {{ $complaint->created_at->diffForHumans() }}
                        </span>
                        <a href="{{ route('client.complaints.show', $complaint) }}" 
                           class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            Voir détails
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Desktop: Table --}}
        <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Réclamation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Colis
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Commercial
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($complaints as $complaint)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $complaint->subject }}
                                        </div>
                                        <div class="text-sm text-gray-500 line-clamp-1">
                                            {{ $complaint->description }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($complaint->package)
                                    <a href="{{ route('client.packages.show', $complaint->package) }}" 
                                       class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                        {{ $complaint->package->package_code }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = match($complaint->status) {
                                        'OPEN' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Ouverte'],
                                        'IN_PROGRESS' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'En cours'],
                                        'RESOLVED' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Résolue'],
                                        'CLOSED' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Fermée'],
                                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $complaint->status],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($complaint->assignedCommercial)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-indigo-600">
                                                    {{ strtoupper(substr($complaint->assignedCommercial->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $complaint->assignedCommercial->name }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $complaint->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $complaint->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('client.complaints.show', $complaint) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Détails
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($complaints->hasPages())
            <div class="mt-6">
                {{ $complaints->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
