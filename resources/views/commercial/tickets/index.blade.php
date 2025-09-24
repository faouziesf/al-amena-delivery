@extends('layouts.commercial')

@section('title', 'Gestion des Tickets')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec statistiques -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des Tickets</h1>
                    <p class="text-gray-600 mt-1">Traitez et répondez aux demandes clients</p>
                </div>
                <a href="{{ route('commercial.tickets.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Créer un Ticket
                </a>
            </div>

            <!-- Statistiques Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Ouverts</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['open'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Urgents</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['urgent'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Mes tickets</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['my_tickets'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Non assignés</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['unassigned'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Attention</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['needs_attention'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres avancés -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text" name="search" id="search"
                           value="{{ request('search') }}"
                           placeholder="Numéro, sujet, client..."
                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" id="status" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>Ouvert</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Résolu</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Fermé</option>
                        <option value="URGENT" {{ request('status') === 'URGENT' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                    <select name="priority" id="priority" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Toutes priorités</option>
                        <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>Faible</option>
                        <option value="NORMAL" {{ request('priority') === 'NORMAL' ? 'selected' : '' }}>Normale</option>
                        <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>Élevée</option>
                        <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les types</option>
                        <option value="COMPLAINT" {{ request('type') === 'COMPLAINT' ? 'selected' : '' }}>Réclamation</option>
                        <option value="QUESTION" {{ request('type') === 'QUESTION' ? 'selected' : '' }}>Question</option>
                        <option value="SUPPORT" {{ request('type') === 'SUPPORT' ? 'selected' : '' }}>Support</option>
                        <option value="OTHER" {{ request('type') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigné à</label>
                    <select name="assigned_to" id="assigned_to" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous</option>
                        <option value="me" {{ request('assigned_to') === 'me' ? 'selected' : '' }}>Mes tickets</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Non assignés</option>
                        @foreach($commercials as $commercial)
                            <option value="{{ $commercial->id }}" {{ request('assigned_to') == $commercial->id ? 'selected' : '' }}>
                                {{ $commercial->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'status', 'priority', 'type', 'assigned_to']))
                        <a href="{{ route('commercial.tickets.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Actions rapides -->
        <div class="flex space-x-4 mb-6">
            <a href="{{ route('commercial.tickets.export') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exporter CSV
            </a>

            <button onclick="toggleBulkActions()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Actions groupées
            </button>
        </div>

        <!-- Liste des tickets -->
        <div class="bg-white rounded-lg shadow-sm border">
            @if($tickets->count() > 0)
                <!-- Bulk actions bar (hidden by default) -->
                <div id="bulk-actions" class="hidden bg-blue-50 px-6 py-4 border-b">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-blue-800" id="selected-count">0 sélectionné(s)</span>
                        </div>
                        <div class="flex space-x-2">
                            <select id="bulk-assign" class="border-gray-300 rounded text-sm">
                                <option value="">Assigner à...</option>
                                @foreach($commercials as $commercial)
                                    <option value="{{ $commercial->id }}">{{ $commercial->name }}</option>
                                @endforeach
                            </select>
                            <button onclick="bulkAssign()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Assigner
                            </button>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <input type="checkbox" class="ticket-checkbox hidden rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       value="{{ $ticket->id }}">

                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('commercial.tickets.show', $ticket) }}" class="hover:text-blue-600 transition-colors">
                                                #{{ $ticket->ticket_number }}
                                            </a>
                                        </h3>

                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->status_color }}">
                                            {{ $ticket->status_display }}
                                        </span>

                                        @if($ticket->priority === 'URGENT' || $ticket->status === 'URGENT')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100">
                                                🚨 Urgent
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            {{ $ticket->type_display }}
                                        </span>
                                    </div>

                                    <p class="text-gray-900 font-medium mb-1">{{ $ticket->subject }}</p>
                                    <p class="text-gray-600 text-sm line-clamp-2">{{ Str::limit($ticket->description, 150) }}</p>

                                    <div class="flex items-center space-x-6 mt-3 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $ticket->client->name }}
                                        </div>
                                        <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                                        @if($ticket->assignedTo)
                                            <span class="text-blue-600">Assigné à {{ $ticket->assignedTo->name }}</span>
                                        @else
                                            <span class="text-red-600">Non assigné</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    @php
                                        $unreadCount = $ticket->messages()
                                            ->where('sender_type', 'CLIENT')
                                            ->whereNull('read_at')
                                            ->count();
                                    @endphp

                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }} nouveau(x)
                                        </span>
                                    @endif

                                    <div class="flex space-x-2">
                                        @if(!$ticket->assignedTo)
                                            <button onclick="assignToMe('{{ $ticket->id }}')"
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                M'assigner
                                            </button>
                                        @endif
                                        <a href="{{ route('commercial.tickets.show', $ticket) }}"
                                           class="text-green-600 hover:text-green-800 font-medium">
                                            Traiter →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun ticket trouvé</h3>
                    <p class="text-gray-600 mb-6">Aucun ticket ne correspond aux critères de recherche.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleBulkActions() {
    const bulkActions = document.getElementById('bulk-actions');
    const checkboxes = document.querySelectorAll('.ticket-checkbox');

    if (bulkActions.classList.contains('hidden')) {
        bulkActions.classList.remove('hidden');
        checkboxes.forEach(cb => cb.classList.remove('hidden'));
    } else {
        bulkActions.classList.add('hidden');
        checkboxes.forEach(cb => {
            cb.classList.add('hidden');
            cb.checked = false;
        });
        document.getElementById('select-all').checked = false;
        updateSelectedCount();
    }
}

function assignToMe(ticketId) {
    fetch(`/commercial/tickets/${ticketId}/assign`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            assigned_to_id: {{ auth()->id() }}
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('ticket-checkbox')) {
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.ticket-checkbox:checked').length;
    document.getElementById('selected-count').textContent = `${selected} sélectionné(s)`;
}

function bulkAssign() {
    const selected = Array.from(document.querySelectorAll('.ticket-checkbox:checked')).map(cb => cb.value);
    const assignTo = document.getElementById('bulk-assign').value;

    if (selected.length === 0 || !assignTo) {
        alert('Veuillez sélectionner des tickets et choisir un commercial');
        return;
    }

    // Implementation for bulk assignment
    console.log('Bulk assign:', selected, 'to:', assignTo);
}
</script>

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
@endsection