@extends('layouts.supervisor')

@section('title', 'Gestion des Tickets')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec actions -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des Tickets</h1>
                    <p class="text-gray-600 mt-1">Vue d'ensemble et gestion superviseur des tickets</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('supervisor.tickets.overview') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Vue d'ensemble
                    </a>
                    <a href="{{ route('supervisor.tickets.performance-report') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Rapport de performance
                    </a>
                </div>
            </div>

            <!-- Statistiques détaillées -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
                @php
                    $stats = [
                        'total' => $tickets->total(),
                        'urgent' => $tickets->where('status', 'URGENT')->count(),
                        'open' => $tickets->where('status', 'OPEN')->count(),
                        'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                        'resolved' => $tickets->where('status', 'RESOLVED')->count(),
                        'closed' => $tickets->where('status', 'CLOSED')->count()
                    ];

                    $commercialStats = \App\Models\User::where('role', 'COMMERCIAL')
                        ->withCount(['assignedTickets' => function($q) {
                            $q->whereIn('status', ['OPEN', 'IN_PROGRESS', 'URGENT']);
                        }])->get();
                @endphp

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
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Urgents</p>
                            <p class="text-2xl font-bold text-red-700">{{ $stats['urgent'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Nouveaux</p>
                            <p class="text-2xl font-bold text-yellow-700">{{ $stats['open'] }}</p>
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
                            <p class="text-2xl font-bold text-orange-700">{{ $stats['in_progress'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Résolus</p>
                            <p class="text-2xl font-bold text-green-700">{{ $stats['resolved'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Fermés</p>
                            <p class="text-2xl font-bold text-gray-700">{{ $stats['closed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance commerciaux -->
            <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Charge de travail des commerciaux</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($commercialStats as $commercial)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">{{ strtoupper(substr($commercial->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $commercial->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $commercial->assigned_tickets_count }} tickets actifs</p>
                                </div>
                            </div>
                            @if($commercial->assigned_tickets_count > 10)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100">
                                    Surchargé
                                </span>
                            @elseif($commercial->assigned_tickets_count > 5)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-yellow-800 bg-yellow-100">
                                    Chargé
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-green-800 bg-green-100">
                                    Disponible
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Filtres avancés -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                    <input type="text" name="search" id="search"
                           value="{{ request('search') }}"
                           placeholder="Numéro, sujet, client..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" id="status" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="URGENT" {{ request('status') === 'URGENT' ? 'selected' : '' }}>🚨 Urgent</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>Ouvert</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>En cours</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Résolu</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Fermé</option>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les types</option>
                        <option value="COMPLAINT" {{ request('type') === 'COMPLAINT' ? 'selected' : '' }}>Réclamation</option>
                        <option value="QUESTION" {{ request('type') === 'QUESTION' ? 'selected' : '' }}>Question</option>
                        <option value="SUPPORT" {{ request('type') === 'SUPPORT' ? 'selected' : '' }}>Support</option>
                        <option value="OTHER" {{ request('type') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigné à</label>
                    <select name="assigned_to" id="assigned_to" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous les commerciaux</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Non assigné</option>
                        @foreach(\App\Models\User::where('role', 'COMMERCIAL')->get() as $commercial)
                            <option value="{{ $commercial->id }}" {{ request('assigned_to') == $commercial->id ? 'selected' : '' }}>
                                {{ $commercial->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                    <select name="priority" id="priority" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les priorités</option>
                        <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>Urgente</option>
                        <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>Élevée</option>
                        <option value="NORMAL" {{ request('priority') === 'NORMAL' ? 'selected' : '' }}>Normale</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'status', 'type', 'assigned_to', 'priority']))
                        <a href="{{ route('supervisor.tickets.index') }}"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Actions en lot -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
            <form method="POST" action="{{ route('supervisor.tickets.bulk-reassign') }}" id="bulkForm">
                @csrf
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Sélectionner tout</span>
                        </label>
                        <span id="selectedCount" class="text-sm text-gray-500">0 ticket(s) sélectionné(s)</span>
                    </div>

                    <div class="flex items-center space-x-3">
                        <select name="new_assignee" class="text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Réassigner à...</option>
                            @foreach(\App\Models\User::where('role', 'COMMERCIAL')->get() as $commercial)
                                <option value="{{ $commercial->id }}">{{ $commercial->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" id="bulkAssign" disabled
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded font-medium text-sm transition-colors">
                            Réassigner
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Liste des tickets -->
        <div class="bg-white rounded-lg shadow-sm border">
            @if($tickets->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <div class="p-6 hover:bg-gray-50 transition-colors {{ $ticket->status === 'URGENT' ? 'bg-red-50 border-l-4 border-red-500' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <input type="checkbox" name="selected_tickets[]" value="{{ $ticket->id }}"
                                           class="ticket-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                                   class="hover:text-blue-600 transition-colors">
                                                    #{{ $ticket->ticket_number }}
                                                </a>
                                            </h3>

                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->status_color }}">
                                                {{ $ticket->status_display }}
                                            </span>

                                            @if($ticket->priority === 'URGENT')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100">
                                                    🚨 URGENT
                                                </span>
                                            @endif

                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                                {{ $ticket->type_display }}
                                            </span>
                                        </div>

                                        <p class="text-gray-900 font-medium mb-1">{{ $ticket->subject }}</p>
                                        <p class="text-gray-600 text-sm line-clamp-2">{{ Str::limit($ticket->description, 150) }}</p>

                                        <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                                            <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                                            <span class="text-blue-600">👤 {{ $ticket->client->first_name }} {{ $ticket->client->last_name }}</span>
                                            @if($ticket->assignedTo)
                                                <span class="text-green-600">👨‍💼 {{ $ticket->assignedTo->name }}</span>
                                            @else
                                                <span class="text-red-600">⚠️ Non assigné</span>
                                            @endif
                                            @if($ticket->complaint)
                                                <span class="text-orange-600">📋 Réclamation #{{ $ticket->complaint->id }}</span>
                                            @endif
                                            @if($ticket->package)
                                                <span class="text-purple-600">📦 {{ $ticket->package->package_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    @php
                                        $unreadCount = $ticket->messages()
                                            ->whereNull('read_at')
                                            ->count();
                                        $responseTime = $ticket->first_response_at
                                            ? $ticket->created_at->diffInHours($ticket->first_response_at)
                                            : $ticket->created_at->diffInHours(now());
                                    @endphp

                                    @if($unreadCount > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $unreadCount }} nouveau(x)
                                        </span>
                                    @endif

                                    @if(!$ticket->first_response_at && $responseTime > 24)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ⏰ {{ $responseTime }}h sans réponse
                                        </span>
                                    @endif

                                    <!-- Actions rapides -->
                                    @if($ticket->status !== 'CLOSED')
                                        <div class="flex items-center space-x-2">
                                            @if($ticket->status !== 'URGENT')
                                                <form method="POST" action="{{ route('supervisor.tickets.escalate', $ticket) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                        🚨 Escalader
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('supervisor.tickets.force-close', $ticket) }}" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Fermer définitivement ce ticket ?')"
                                                        class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                                    🔒 Fermer
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    <a href="{{ route('supervisor.tickets.show', $ticket) }}"
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
                    <p class="text-gray-600">Aucun ticket ne correspond aux critères de recherche.</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const ticketCheckboxes = document.querySelectorAll('.ticket-checkbox');
    const selectedCountEl = document.getElementById('selectedCount');
    const bulkAssignBtn = document.getElementById('bulkAssign');

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
        selectedCountEl.textContent = `${checkedCount} ticket(s) sélectionné(s)`;
        bulkAssignBtn.disabled = checkedCount === 0;

        if (checkedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCount === ticketCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        ticketCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        updateSelectedCount();
    });

    ticketCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Confirmation for bulk actions
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.ticket-checkbox:checked').length;
        const assignee = this.querySelector('[name="new_assignee"]').selectedOptions[0].textContent;

        if (checkedCount === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un ticket.');
            return;
        }

        if (!confirm(`Réassigner ${checkedCount} ticket(s) à ${assignee} ?`)) {
            e.preventDefault();
        }
    });

    updateSelectedCount();
});
</script>
@endpush