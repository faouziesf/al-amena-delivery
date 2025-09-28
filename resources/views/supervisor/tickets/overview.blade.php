@extends('layouts.supervisor')

@section('title', 'Vue d\'ensemble des Tickets')

@section('content')
<div x-data="ticketOverview()" class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-cyan-600 bg-clip-text text-transparent">
                        Vue d'ensemble des Tickets
                    </h1>
                    <p class="text-gray-600 mt-1">Analyse complète et filtrage avancé des tickets</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mt-4 lg:mt-0">
                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour Dashboard
                    </a>
                    <a href="{{ route('supervisor.tickets.performance-report') }}"
                       class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Rapport performance
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres avancés -->
        <div class="bg-white/90 backdrop-blur-lg p-8 rounded-2xl shadow-lg border border-white/20 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                </svg>
                Filtres avancés
            </h3>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                    <input type="text" name="search" id="search"
                           value="{{ request('search') }}"
                           placeholder="Numéro, sujet, client..."
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" id="status" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">Tous les statuts</option>
                        <option value="URGENT" {{ request('status') === 'URGENT' ? 'selected' : '' }} class="text-red-600 font-semibold">🚨 Urgent</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>📂 Ouvert</option>
                        <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>⚡ En cours</option>
                        <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>✅ Résolu</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>🔒 Fermé</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priorité</label>
                    <select name="priority" id="priority" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">Toutes priorités</option>
                        <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>🔴 Urgente</option>
                        <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>🟡 Élevée</option>
                        <option value="NORMAL" {{ request('priority') === 'NORMAL' ? 'selected' : '' }}>🟢 Normale</option>
                    </select>
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigné à</label>
                    <select name="assigned_to" id="assigned_to" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">Tous commerciaux</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }} class="text-red-600">⚠️ Non assigné</option>
                        @foreach($commercials as $commercial)
                            <option value="{{ $commercial->id }}" {{ request('assigned_to') == $commercial->id ? 'selected' : '' }}>
                                {{ $commercial->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                    <input type="date" name="date_from" id="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-700 hover:to-cyan-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'status', 'priority', 'assigned_to', 'date_from', 'date_to']))
                        <a href="{{ route('supervisor.tickets.overview') }}"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-3 rounded-lg font-medium transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
            @php
                $stats = [
                    'total' => $tickets->total(),
                    'urgent' => $tickets->where('priority', 'URGENT')->count(),
                    'open' => $tickets->where('status', 'OPEN')->count(),
                    'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                    'resolved' => $tickets->where('status', 'RESOLVED')->count(),
                    'unassigned' => $tickets->whereNull('assigned_to_id')->count()
                ];
            @endphp

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600">Total</div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['urgent'] }}</div>
                    <div class="text-sm text-gray-600">🚨 Urgents</div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['open'] }}</div>
                    <div class="text-sm text-gray-600">📂 Ouverts</div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</div>
                    <div class="text-sm text-gray-600">⚡ En cours</div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['resolved'] }}</div>
                    <div class="text-sm text-gray-600">✅ Résolus</div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-4 rounded-xl shadow-lg border border-white/20">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ $stats['unassigned'] }}</div>
                    <div class="text-sm text-gray-600">⚠️ Non assignés</div>
                </div>
            </div>
        </div>

        <!-- Actions en lot -->
        <div class="bg-white/90 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 mb-8">
            <form method="POST" action="{{ route('supervisor.tickets.bulk-reassign') }}" id="bulkForm">
                @csrf
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Sélectionner tout</span>
                        </label>
                        <span id="selectedCount" class="text-sm text-gray-500">0 ticket(s) sélectionné(s)</span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                        <select name="assigned_to_id" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Réassigner à...</option>
                            @foreach($commercials as $commercial)
                                <option value="{{ $commercial->id }}">{{ $commercial->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" id="bulkAssign" disabled
                                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Réassigner sélection
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Liste des tickets -->
        <div class="bg-white/90 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($tickets->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($tickets as $ticket)
                        <div class="p-6 hover:bg-gray-50/50 transition-colors {{ $ticket->priority === 'URGENT' ? 'bg-red-50/30 border-l-4 border-red-500' : '' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4">
                                    <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}"
                                           class="ticket-checkbox mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-3">
                                            <h3 class="text-lg font-bold text-gray-900">
                                                <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                                   class="hover:text-indigo-600 transition-colors">
                                                    #{{ $ticket->ticket_number }}
                                                </a>
                                            </h3>

                                            @php
                                                $statusConfig = [
                                                    'URGENT' => 'bg-red-100 text-red-800',
                                                    'OPEN' => 'bg-yellow-100 text-yellow-800',
                                                    'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                                    'RESOLVED' => 'bg-green-100 text-green-800',
                                                    'CLOSED' => 'bg-gray-100 text-gray-800'
                                                ];

                                                $priorityConfig = [
                                                    'URGENT' => 'bg-red-100 text-red-800',
                                                    'HIGH' => 'bg-orange-100 text-orange-800',
                                                    'NORMAL' => 'bg-green-100 text-green-800'
                                                ];
                                            @endphp

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusConfig[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $ticket->status }}
                                            </span>

                                            @if($ticket->priority && $ticket->priority !== 'NORMAL')
                                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $priorityConfig[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                                    @if($ticket->priority === 'URGENT') 🚨 @endif
                                                    {{ $ticket->priority }}
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h4>
                                        <p class="text-gray-700 mb-4 line-clamp-2">{{ Str::limit($ticket->description, 200) }}</p>

                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            <div class="flex items-center text-blue-600">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                {{ $ticket->client->name ?? 'Client inconnu' }}
                                            </div>

                                            @if($ticket->assignedTo)
                                                <div class="flex items-center text-green-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $ticket->assignedTo->name }}
                                                </div>
                                            @else
                                                <div class="flex items-center text-red-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Non assigné
                                                </div>
                                            @endif

                                            <div class="flex items-center text-gray-600">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $ticket->created_at->diffForHumans() }}
                                            </div>

                                            @if($ticket->complaint)
                                                <div class="flex items-center text-orange-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Réclamation #{{ $ticket->complaint->id }}
                                                </div>
                                            @endif

                                            @if($ticket->package)
                                                <div class="flex items-center text-purple-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                                                    </svg>
                                                    {{ $ticket->package->package_code }}
                                                </div>
                                            @endif
                                        </div>

                                        @if($ticket->messages && $ticket->messages->count() > 0)
                                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border">
                                                <p class="text-sm text-gray-600 mb-1">Dernier message :</p>
                                                <p class="text-sm text-gray-900">{{ Str::limit($ticket->messages->first()->message ?? '', 100) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col space-y-2 ml-6">
                                    <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors text-center">
                                        Voir détails
                                    </a>

                                    @if($ticket->priority !== 'URGENT')
                                        <form method="POST" action="{{ route('supervisor.tickets.escalate', $ticket) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="reason" value="Escalade depuis vue d'ensemble">
                                            <button type="submit"
                                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                                🚨 Escalader
                                            </button>
                                        </form>
                                    @endif

                                    @if($ticket->status !== 'CLOSED')
                                        <form method="POST" action="{{ route('supervisor.tickets.force-close', $ticket) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="reason" value="Fermeture depuis vue d'ensemble">
                                            <button type="submit" onclick="return confirm('Fermer définitivement ce ticket ?')"
                                                    class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                                🔒 Fermer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t bg-gray-50/50">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun ticket trouvé</h3>
                    <p class="text-gray-600">Aucun ticket ne correspond aux critères de recherche sélectionnés.</p>
                    <a href="{{ route('supervisor.tickets.overview') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Réinitialiser les filtres
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

/* Animation des cartes */
.hover\:scale-105:hover {
    --tw-scale-x: 1.05;
    --tw-scale-y: 1.05;
    transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
}

/* Effet glassmorphism */
.backdrop-blur-lg {
    backdrop-filter: blur(16px);
}
</style>
@endpush

@push('scripts')
<script>
function ticketOverview() {
    return {
        selectedTickets: [],

        init() {
            this.initBulkActions();
        },

        initBulkActions() {
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
                const assignee = this.querySelector('[name="assigned_to_id"]').selectedOptions[0].textContent;

                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un ticket.');
                    return;
                }

                if (!this.querySelector('[name="assigned_to_id"]').value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un commercial.');
                    return;
                }

                if (!confirm(`Réassigner ${checkedCount} ticket(s) à ${assignee} ?`)) {
                    e.preventDefault();
                }
            });

            updateSelectedCount();
        }
    }
}

// Auto-refresh every 2 minutes for real-time updates
setInterval(function() {
    // Refresh silently in background to check for new urgent tickets
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        if (response.ok) {
            console.log('Données actualisées en arrière-plan');
        }
    });
}, 120000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+A to select all tickets
    if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        document.getElementById('selectAll').click();
    }

    // Escape to clear selection
    if (e.key === 'Escape') {
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.ticket-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectedCount').textContent = '0 ticket(s) sélectionné(s)';
        document.getElementById('bulkAssign').disabled = true;
    }
});
</script>
@endpush