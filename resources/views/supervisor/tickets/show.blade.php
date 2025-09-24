@extends('layouts.supervisor')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - Supervision')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec navigation -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('supervisor.tickets.index') }}" class="hover:text-gray-700">Tickets</a>
                <span>/</span>
                <span class="text-gray-900">#{{ $ticket->ticket_number }}</span>
            </nav>

            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">Ticket #{{ $ticket->ticket_number }}</h1>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $ticket->status_color }}">
                            {{ $ticket->status_display }}
                        </span>
                        @if($ticket->priority === 'URGENT')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full text-red-800 bg-red-100">
                                🚨 URGENT
                            </span>
                        @endif
                        <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                            {{ $ticket->type_display }}
                        </span>
                    </div>
                    <p class="text-gray-600">{{ $ticket->subject }}</p>
                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                        <span>Créé {{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                        @if($ticket->complaint)
                            <span class="text-orange-600">📋 Réclamation #{{ $ticket->complaint->id }}</span>
                        @endif
                        @if($ticket->package)
                            <span class="text-blue-600">📦 {{ $ticket->package->package_code }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @if($ticket->status !== 'CLOSED')
                        <!-- Actions superviseur -->
                        <div class="flex items-center space-x-2">
                            @if($ticket->status !== 'URGENT')
                                <form method="POST" action="{{ route('supervisor.tickets.escalate', $ticket) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                        🚨 Escalader
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('supervisor.tickets.force-close', $ticket) }}" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Fermer définitivement ce ticket ? Cette action est irréversible.')"
                                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                    🔒 Fermer définitivement
                                </button>
                            </form>
                        </div>
                    @endif

                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="text-gray-600 hover:text-gray-800 font-medium">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Messages et conversation principale -->
            <div class="lg:col-span-2">
                <!-- Métriques de performance -->
                @php
                    $responseTime = $ticket->first_response_at
                        ? $ticket->created_at->diffInHours($ticket->first_response_at)
                        : null;
                    $resolutionTime = $ticket->resolved_at
                        ? $ticket->created_at->diffInHours($ticket->resolved_at)
                        : null;
                    $messagesCount = $ticket->messages()->count();
                    $clientMessages = $ticket->messages()->where('sender_type', 'CLIENT')->count();
                    $supportMessages = $ticket->messages()->where('sender_type', '!=', 'CLIENT')->count();
                @endphp

                <div class="bg-white rounded-lg shadow-sm border mb-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">📊 Métriques de performance</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ $responseTime ? $responseTime . 'h' : 'En attente' }}
                                </div>
                                <div class="text-sm text-gray-600">Temps de première réponse</div>
                                @if($responseTime && $responseTime > 24)
                                    <div class="text-xs text-red-600 font-medium">⚠️ SLA dépassé</div>
                                @endif
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ $resolutionTime ? $resolutionTime . 'h' : 'En cours' }}
                                </div>
                                <div class="text-sm text-gray-600">Temps de résolution</div>
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $messagesCount }}</div>
                                <div class="text-sm text-gray-600">Messages échangés</div>
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">
                                    {{ $clientMessages }}/{{ $supportMessages }}
                                </div>
                                <div class="text-sm text-gray-600">Client/Support</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description initiale -->
                <div class="bg-white rounded-lg shadow-sm border mb-6">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Description initiale</h3>
                            <span class="text-sm text-gray-500">
                                {{ $ticket->client->first_name }} {{ $ticket->client->last_name }}
                                • {{ $ticket->created_at->format('d/m/Y à H:i') }}
                            </span>
                        </div>
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Timeline complète des messages -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">💬 Timeline complète</h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($ticket->messages()->latest()->get() as $message)
                            <div class="p-6 {{
                                $message->sender_type === 'CLIENT' ? 'bg-blue-50' :
                                ($message->is_internal ? 'bg-yellow-50' : 'bg-green-50')
                            }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center space-x-3">
                                        @if($message->sender_type === 'CLIENT')
                                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->first_name ?? 'C', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-900">
                                                    {{ $message->sender->first_name }} {{ $message->sender->last_name }}
                                                </span>
                                                <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded ml-2">Client</span>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->name ?? 'S', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-900">{{ $message->sender->name ?? 'Support' }}</span>
                                                @if($message->is_internal)
                                                    <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded ml-2">Note interne</span>
                                                @else
                                                    <span class="text-xs text-green-700 bg-green-100 px-2 py-1 rounded ml-2">Support</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right text-sm text-gray-500">
                                        <div>{{ $message->created_at->format('d/m/Y à H:i') }}</div>
                                        @if($message->read_at)
                                            <div class="text-xs text-green-600">✓ Lu {{ $message->read_at->format('H:i') }}</div>
                                        @else
                                            <div class="text-xs text-red-600">Non lu</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="prose max-w-none text-gray-700">
                                    {!! nl2br(e($message->message)) !!}
                                </div>

                                @if($message->attachments)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($message->attachments as $attachment)
                                            <a href="{{ Storage::url($attachment['path']) }}"
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                                📎 {{ $attachment['name'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Analytics du message -->
                                <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            @if($message->sender_type !== 'CLIENT')
                                                Temps de réponse: {{ $message->created_at->diffInMinutes($ticket->last_activity_at) }}min
                                            @endif
                                        </div>
                                        <div>
                                            Message #{{ $loop->total - $loop->index }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">
                                Aucun message dans cette conversation
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar avec informations complètes -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Assignation et réassignation -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">👨‍💼 Gestion</h3>

                        <form method="POST" action="{{ route('supervisor.tickets.bulk-reassign') }}">
                            @csrf
                            <input type="hidden" name="selected_tickets[]" value="{{ $ticket->id }}">

                            <div class="mb-4">
                                <label for="new_assignee" class="block text-sm font-medium text-gray-700 mb-2">
                                    Réassigner à
                                </label>
                                <select name="new_assignee" id="new_assignee"
                                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Sélectionner un commercial</option>
                                    @foreach(\App\Models\User::where('role', 'COMMERCIAL')->get() as $commercial)
                                        <option value="{{ $commercial->id }}"
                                                {{ $ticket->assigned_to_id == $commercial->id ? 'selected' : '' }}>
                                            {{ $commercial->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Réassigner
                            </button>
                        </form>

                        <!-- Charge de travail du commercial assigné -->
                        @if($ticket->assignedTo)
                            @php
                                $workload = $ticket->assignedTo->assignedTickets()
                                    ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'URGENT'])
                                    ->count();
                            @endphp
                            <div class="mt-4 p-3 bg-gray-50 rounded">
                                <div class="text-sm font-medium text-gray-700">Charge actuelle</div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-lg font-bold">{{ $workload }} tickets</span>
                                    @if($workload > 10)
                                        <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">Surchargé</span>
                                    @elseif($workload > 5)
                                        <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded">Chargé</span>
                                    @else
                                        <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">Disponible</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Informations détaillées du ticket -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Détails</h3>

                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="font-medium text-gray-600">Client</dt>
                                <dd class="text-gray-900">
                                    <a href="{{ route('commercial.clients.show', $ticket->client) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $ticket->client->first_name }} {{ $ticket->client->last_name }}
                                    </a>
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-600">Email</dt>
                                <dd class="text-gray-900">{{ $ticket->client->email }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-600">Téléphone</dt>
                                <dd class="text-gray-900">{{ $ticket->client->phone ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-600">Priorité</dt>
                                <dd>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $ticket->priority === 'URGENT' ? 'text-red-800 bg-red-100' :
                                           ($ticket->priority === 'HIGH' ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100') }}">
                                        {{ $ticket->priority_display }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-600">Assigné à</dt>
                                <dd class="text-gray-900">{{ $ticket->assignedTo->name ?? 'Non assigné' }}</dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-600">Créé le</dt>
                                <dd class="text-gray-900">{{ $ticket->created_at->format('d/m/Y à H:i') }}</dd>
                            </div>

                            @if($ticket->first_response_at)
                                <div>
                                    <dt class="font-medium text-gray-600">Première réponse</dt>
                                    <dd class="text-gray-900">{{ $ticket->first_response_at->format('d/m/Y à H:i') }}</dd>
                                </div>
                            @endif

                            @if($ticket->resolved_at)
                                <div>
                                    <dt class="font-medium text-gray-600">Résolu le</dt>
                                    <dd class="text-gray-900">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="font-medium text-gray-600">Dernière activité</dt>
                                <dd class="text-gray-900">{{ $ticket->last_activity_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($ticket->complaint)
                        <!-- Réclamation liée -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Réclamation</h3>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Réclamation:</span>
                                    <span class="text-gray-900">#{{ $ticket->complaint->id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Type:</span>
                                    <span class="text-gray-900">{{ $ticket->complaint->type }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Statut:</span>
                                    <span class="text-gray-900">{{ $ticket->complaint->status }}</span>
                                </div>

                                @if($ticket->complaint->package)
                                    <div>
                                        <span class="font-medium text-gray-600">Colis:</span>
                                        <span class="text-gray-900">{{ $ticket->complaint->package->package_code }}</span>
                                    </div>
                                @endif

                                <a href="{{ route('commercial.complaints.show', $ticket->complaint) }}"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                    Voir la réclamation →
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($ticket->package)
                        <!-- Colis lié -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Colis</h3>

                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Code:</span>
                                    <span class="text-gray-900">{{ $ticket->package->package_code }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Statut:</span>
                                    <span class="text-gray-900">{{ $ticket->package->status }}</span>
                                </div>
                                @if($ticket->package->deliverer)
                                    <div>
                                        <span class="font-medium text-gray-600">Livreur:</span>
                                        <span class="text-gray-900">{{ $ticket->package->deliverer->name }}</span>
                                    </div>
                                @endif

                                <a href="{{ route('commercial.packages.show', $ticket->package) }}"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                    Voir le colis →
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Statistiques client avancées -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Profil client</h3>

                        @php
                            $clientStats = [
                                'tickets' => $ticket->client->tickets()->count(),
                                'urgentTickets' => $ticket->client->tickets()->where('status', 'URGENT')->count(),
                                'packages' => $ticket->client->packages()->count(),
                                'complaints' => $ticket->client->complaints()->count(),
                                'avgResponseTime' => $ticket->client->tickets()
                                    ->whereNotNull('first_response_at')
                                    ->get()
                                    ->avg(function($t) {
                                        return $t->created_at->diffInHours($t->first_response_at);
                                    })
                            ];
                        @endphp

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Tickets total</dt>
                                <dd class="font-medium text-gray-900">{{ $clientStats['tickets'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Tickets urgents</dt>
                                <dd class="font-medium text-red-600">{{ $clientStats['urgentTickets'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Colis envoyés</dt>
                                <dd class="font-medium text-gray-900">{{ $clientStats['packages'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Réclamations</dt>
                                <dd class="font-medium text-orange-600">{{ $clientStats['complaints'] }}</dd>
                            </div>
                            @if($clientStats['avgResponseTime'])
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Temps réponse moy.</dt>
                                    <dd class="font-medium text-gray-900">{{ round($clientStats['avgResponseTime'], 1) }}h</dd>
                                </div>
                            @endif
                        </dl>

                        <!-- Évaluation du risque client -->
                        @php
                            $riskScore = 0;
                            if ($clientStats['urgentTickets'] > 2) $riskScore += 3;
                            if ($clientStats['complaints'] > 5) $riskScore += 2;
                            if ($clientStats['tickets'] > 10) $riskScore += 1;

                            $riskLevel = $riskScore >= 4 ? 'high' : ($riskScore >= 2 ? 'medium' : 'low');
                        @endphp

                        <div class="mt-4 p-3 rounded {{
                            $riskLevel === 'high' ? 'bg-red-50 border border-red-200' :
                            ($riskLevel === 'medium' ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200')
                        }}">
                            <div class="text-sm font-medium {{
                                $riskLevel === 'high' ? 'text-red-800' :
                                ($riskLevel === 'medium' ? 'text-yellow-800' : 'text-green-800')
                            }}">
                                Profil client:
                                {{ $riskLevel === 'high' ? '⚠️ À risque' : ($riskLevel === 'medium' ? '⚡ Attention' : '✅ Normal') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-scroll to bottom of conversation on load
document.addEventListener('DOMContentLoaded', function() {
    const conversation = document.querySelector('.divide-y.divide-gray-200');
    if (conversation && conversation.children.length > 0) {
        conversation.lastElementChild.scrollIntoView({ behavior: 'smooth' });
    }
});

// Confirmation for critical actions
document.querySelectorAll('form[action*="force-close"], form[action*="escalate"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const action = form.action.includes('force-close') ? 'fermer définitivement' : 'escalader';
        const warning = action === 'fermer définitivement' ?
            'Cette action est irréversible et fermera le ticket définitivement.' :
            'Le ticket sera marqué comme urgent et nécessitera une attention immédiate.';

        if (!confirm(`Voulez-vous vraiment ${action} ce ticket ?\n\n${warning}`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush