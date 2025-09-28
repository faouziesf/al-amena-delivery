@extends('layouts.deliverer')

@section('title', 'Mes Colis')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">📦 Mes Colis</h1>
        <p class="text-gray-500 mt-1">Vue d'ensemble de votre activité de colis.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
        @php
            $statCards = [
                ['label' => 'Pickups Disponibles', 'stat' => $stats['available_pickups'] ?? 0, 'route' => 'deliverer.packages.available'],
                ['label' => 'Mes Pickups', 'stat' => $stats['my_pickups'] ?? 0, 'route' => 'deliverer.packages.my-pickups'],
                ['label' => 'Livraisons en Cours', 'stat' => $stats['deliveries'] ?? 0, 'route' => 'deliverer.packages.deliveries'],
                ['label' => 'Retours à Traiter', 'stat' => $stats['returns'] ?? 0, 'route' => 'deliverer.packages.returns']
            ];
        @endphp

        @foreach ($statCards as $card)
        <a href="{{ route($card['route']) }}" class="group">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-purple-100 hover:shadow-lg hover:border-purple-200 transition-all duration-300 h-full">
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <p class="text-3xl font-bold text-purple-800 mt-1">{{ $card['stat'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Colis urgents -->
    @if($urgentPackages && $urgentPackages->count() > 0)
    <div class="bg-red-50 border border-red-200 rounded-2xl mb-8">
        <div class="px-6 py-4">
            <h2 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Colis Urgents (3ème tentative)
            </h2>
        </div>
        <div class="p-4 space-y-3">
            @foreach($urgentPackages as $package)
            <div class="flex items-center justify-between p-4 bg-white border border-red-100 rounded-lg">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-medium text-gray-800">{{ $package->package_code }}</span>
                        <span class="bg-red-600 text-white text-xs px-2 py-1 rounded-full font-semibold">URGENT</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $package->recipient_data['name'] ?? 'N/A' }} • {{ $package->delegationTo->name ?? 'N/A' }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                     <p class="text-sm font-semibold text-red-700">
                        COD: {{ number_format($package->cod_amount, 3) }} DT
                    </p>
                    <a href="{{ route('deliverer.packages.show', $package) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                        Traiter
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Activité récente -->
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100">
        <div class="px-6 py-4 border-b border-purple-100">
            <h2 class="text-lg font-semibold text-gray-800">Activité Récente des Colis</h2>
        </div>
        <div class="p-4">
            @if($recentActivity && $recentActivity->count() > 0)
                <div class="space-y-2">
                    @foreach($recentActivity as $activity)
                    <div class="flex items-center gap-4 p-3 hover:bg-slate-50 rounded-lg">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg
                            @if($activity->status === 'DELIVERED') bg-green-100 text-green-600
                            @elseif($activity->status === 'PICKED_UP') bg-blue-100 text-blue-600
                            @elseif($activity->status === 'RETURNED') bg-red-100 text-red-600
                            @else bg-gray-100 text-gray-600
                            @endif">
                            @if($activity->status === 'DELIVERED') ✅
                            @elseif($activity->status === 'PICKED_UP') 📦
                            @elseif($activity->status === 'RETURNED') ↩️
                            @else 📋 @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $activity->package_code }}</p>
                            <p class="text-sm text-gray-500">
                                @if($activity->status === 'DELIVERED') Livré • {{ number_format($activity->cod_amount, 3) }} DT
                                @elseif($activity->status === 'PICKED_UP') Collecté chez l'expéditeur
                                @elseif($activity->status === 'RETURNED') Retourné à l'expéditeur
                                @else {{ $activity->status }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">{{ $activity->updated_at ? $activity->updated_at->diffForHumans() : 'Date inconnue' }}</p>
                            <a href="{{ route('deliverer.packages.show', $activity) }}" class="text-xs text-purple-600 hover:text-purple-800 font-semibold">
                                Voir détails →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path></svg>
                    <p class="text-gray-500 font-medium">Aucune activité récente à afficher.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection