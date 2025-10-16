@extends('layouts.client')

@section('title', 'Mes Tickets de Support')

@section('content')
<div class="min-h-screen bg-gray-50 -mx-4 -my-4 lg:-mx-6 lg:-my-6 px-4 py-6 lg:px-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header avec statistiques -->
        <div class="mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Mes Tickets de Support</h1>
                    <p class="text-sm text-gray-600 mt-1">Gérez vos demandes d'assistance</p>
                </div>
                <a href="{{ route('client.tickets.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nouveau Ticket
                </a>
            </div>

            <!-- Statistiques rapides -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
                @php
                    $stats = [
                        'total' => $tickets->total(),
                        'open' => $tickets->where('status', 'OPEN')->count(),
                        'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                        'urgent' => $tickets->where('status', 'URGENT')->count()
                    ];
                @endphp
                <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-600">Total</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-600">Ouverts</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['open'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-600">En cours</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-600">Urgents</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $stats['urgent'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white p-3 sm:p-4 rounded-xl shadow-sm border mb-4 sm:mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-0">
                    <label for="search" class="sr-only">Rechercher</label>
                    <input type="text" name="search" id="search"
                           value="{{ request('search') }}"
                           placeholder="Rechercher par numéro, sujet..."
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="min-w-0">
                    <select name="status" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>Ouvert</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Résolu</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Fermé</option>
                        <option value="URGENT" {{ request('status') === 'URGENT' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="min-w-0">
                    <select name="type" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les types</option>
                        <option value="COMPLAINT" {{ request('type') === 'COMPLAINT' ? 'selected' : '' }}>Réclamation</option>
                        <option value="QUESTION" {{ request('type') === 'QUESTION' ? 'selected' : '' }}>Question</option>
                        <option value="SUPPORT" {{ request('type') === 'SUPPORT' ? 'selected' : '' }}>Support</option>
                        <option value="OTHER" {{ request('type') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <button type="submit"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Filtrer
                </button>

                @if(request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('client.tickets.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        <!-- Liste des tickets -->
        <div class="bg-white rounded-xl shadow-sm border">
            @if($tickets->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <div class="p-3 sm:p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h3 class="text-base font-semibold text-gray-900">
                                            <a href="{{ route('client.tickets.show', $ticket) }}"
                                               class="hover:text-blue-600 transition-colors">
                                                #{{ $ticket->ticket_number }}
                                            </a>
                                        </h3>

                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->status_color }}">
                                            {{ $ticket->status_display }}
                                        </span>

                                        @if($ticket->priority === 'URGENT')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100">
                                                🚨 Urgent
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {{ $ticket->type_display }}
                                        </span>
                                    </div>

                                    <p class="text-sm sm:text-base text-gray-900 font-medium mb-1">{{ $ticket->subject }}</p>
                                    <p class="text-gray-600 text-xs sm:text-sm line-clamp-2">{{ Str::limit($ticket->description, 150) }}</p>

                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2 text-xs sm:text-sm text-gray-500">
                                        <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                                        @if($ticket->assignedTo)
                                            <span>Assigné à {{ $ticket->assignedTo->name }}</span>
                                        @endif
                                        @if($ticket->complaint)
                                            <span class="text-orange-600">📋 Réclamation #{{ $ticket->complaint->id }}</span>
                                        @endif
                                        @if($ticket->package)
                                            <span class="text-blue-600">📦 {{ $ticket->package->package_code }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    @php
                                        $unreadCount = $ticket->messages()
                                            ->where('sender_type', '!=', 'CLIENT')
                                            ->whereNull('read_at')
                                            ->count();
                                    @endphp

                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }} nouveau(x)
                                        </span>
                                    @endif

                                    <a href="{{ route('client.tickets.show', $ticket) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        Voir →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun ticket trouvé</h3>
                    <p class="text-gray-600 mb-6">Vous n'avez pas encore créé de tickets de support.</p>
                    <a href="{{ route('client.tickets.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer mon premier ticket
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
@endpush