@extends('layouts.client')

@section('title', 'Mes Tickets')

@section('content')
<div class="max-w-6xl mx-auto py-4 sm:py-6 px-4 sm:px-6" x-data="ticketsPage()">
    
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mb-1">💬 Mes Tickets</h1>
                <p class="text-gray-600">Support et assistance</p>
            </div>
            <a href="{{ route('client.tickets.create') }}" 
               class="inline-flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="hidden sm:inline">Nouveau Ticket</span>
                <span class="sm:hidden">Nouveau</span>
            </a>
        </div>

        <!-- Stats Cards -->
        @php
            $stats = [
                'total' => $tickets->total(),
                'open' => $tickets->where('status', 'OPEN')->count(),
                'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                'resolved' => $tickets->where('status', 'RESOLVED')->count()
            ];
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-4 shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium opacity-90">Total</span>
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-3xl font-black">{{ $stats['total'] }}</div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-4 shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium opacity-90">Ouverts</span>
                    <span class="text-2xl">🟢</span>
                </div>
                <div class="text-3xl font-black">{{ $stats['open'] }}</div>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-4 shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium opacity-90">En cours</span>
                    <span class="text-2xl">🔵</span>
                </div>
                <div class="text-3xl font-black">{{ $stats['in_progress'] }}</div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-4 shadow-lg text-white transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium opacity-90">Résolus</span>
                    <span class="text-2xl">✅</span>
                </div>
                <div class="text-3xl font-black">{{ $stats['resolved'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="🔍 Rechercher par numéro ou sujet..."
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>

            <select name="status" 
                    class="px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                <option value="">Tous les statuts</option>
                <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>🟢 Ouvert</option>
                <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>🔵 En cours</option>
                <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>✅ Résolu</option>
                <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>⚪ Fermé</option>
            </select>

            <button type="submit" 
                    class="px-6 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold transition-colors">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('client.tickets.index') }}" 
               class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold transition-colors text-center">
                ✕
            </a>
            @endif
        </form>
    </div>

    <!-- Liste des Tickets -->
    @if($tickets->count() > 0)
    <div class="space-y-4">
        @foreach($tickets as $ticket)
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
            <a href="{{ route('client.tickets.show', $ticket) }}" class="block p-5">
                <div class="flex items-start justify-between gap-4">
                    <!-- Info Principale -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="font-mono font-bold text-gray-900">#{{ $ticket->ticket_number }}</span>
                            
                            @if($ticket->status === 'OPEN')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                🟢 OUVERT
                            </span>
                            @elseif($ticket->status === 'IN_PROGRESS')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                🔵 EN COURS
                            </span>
                            @elseif($ticket->status === 'RESOLVED')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                                ✅ RÉSOLU
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                ⚪ {{ strtoupper($ticket->status) }}
                            </span>
                            @endif

                            @if($ticket->type === 'COMPLAINT')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                📋 Réclamation
                            </span>
                            @elseif($ticket->type === 'QUESTION')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                ❓ Question
                            </span>
                            @elseif($ticket->type === 'SUPPORT')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">
                                🛠️ Support
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                📝 Autre
                            </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-2 hover:text-indigo-600 transition-colors">
                            {{ $ticket->subject }}
                        </h3>

                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $ticket->description }}</p>

                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>

                            @if($ticket->package)
                            <span class="flex items-center gap-1 text-indigo-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                {{ $ticket->package->package_code }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Badge Non Lu + Flèche -->
                    <div class="flex items-center gap-3">
                        @php
                            $unreadCount = $ticket->messages()
                                ->where('sender_type', '!=', 'CLIENT')
                                ->whereNull('read_at')
                                ->count();
                        @endphp

                        @if($unreadCount > 0)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-500 text-white shadow-lg animate-pulse">
                            {{ $unreadCount }} new
                        </span>
                        @endif

                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center">
        <div class="relative inline-block mb-6">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur-2xl opacity-20"></div>
            <div class="relative w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-2">Aucun ticket</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Vous n'avez pas encore créé de ticket de support. Besoin d'aide ? Créez votre premier ticket !
        </p>
        <a href="{{ route('client.tickets.create') }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Créer mon premier ticket
        </a>
    </div>
    @endif

</div>

<script>
function ticketsPage() {
    return {
        //
    }
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
