@extends('layouts.commercial')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec navigation -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('commercial.tickets.index') }}" class="hover:text-gray-700">Tickets</a>
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
                        <!-- Actions rapides -->
                        <div class="flex items-center space-x-2">
                            @if($ticket->status === 'OPEN')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="IN_PROGRESS">
                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                        ⚡ Prendre en charge
                                    </button>
                                </form>
                            @endif

                            @if(in_array($ticket->status, ['OPEN', 'IN_PROGRESS']))
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="URGENT">
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                        🚨 Marquer urgent
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="RESOLVED">
                                    <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                        ✅ Résoudre
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    <a href="{{ route('commercial.tickets.index') }}"
                       class="text-gray-600 hover:text-gray-800 font-medium">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Messages et conversation principale -->
            <div class="lg:col-span-2">
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

                <!-- Conversation -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Conversation</h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($ticket->messages()->latest()->get() as $message)
                            <div class="p-6 {{ $message->sender_type === 'CLIENT' ? 'bg-blue-50' : ($message->is_internal ? 'bg-yellow-50' : 'bg-gray-50') }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center space-x-2">
                                        @if($message->sender_type === 'CLIENT')
                                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->first_name ?? 'C', 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900">
                                                {{ $message->sender->first_name }} {{ $message->sender->last_name }}
                                            </span>
                                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">Client</span>
                                        @else
                                            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->name ?? 'S', 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="font-medium text-gray-900">{{ $message->sender->name ?? 'Support' }}</span>
                                            @if($message->is_internal)
                                                <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded">Note interne</span>
                                            @else
                                                <span class="text-xs text-green-700 bg-green-100 px-2 py-1 rounded">Support</span>
                                            @endif
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $message->created_at->format('d/m/Y à H:i') }}</span>
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
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">
                                Aucun message dans cette conversation
                            </div>
                        @endforelse
                    </div>

                    @if($ticket->status !== 'CLOSED')
                        <!-- Formulaire de réponse -->
                        <div class="p-6 border-t bg-gray-50">
                            <form method="POST" action="{{ route('commercial.tickets.add-message', $ticket) }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-4">
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                        Votre réponse
                                    </label>
                                    <textarea name="message" id="message" rows="4" required
                                              placeholder="Tapez votre réponse au client..."
                                              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-300 @enderror"></textarea>
                                    @error('message')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_internal" value="1"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            Note interne (non visible par le client)
                                        </span>
                                    </label>
                                </div>

                                <div class="mb-4">
                                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                                        Pièces jointes (optionnel)
                                    </label>
                                    <input type="file" name="attachments[]" id="attachments" multiple
                                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                           class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Formats acceptés: JPG, PNG, PDF, DOC, DOCX (max 10MB par fichier)
                                    </p>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-4">
                                        @if($ticket->status === 'OPEN')
                                            <label class="flex items-center">
                                                <input type="checkbox" name="take_charge" value="1" checked
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">
                                                    Prendre en charge le ticket
                                                </span>
                                            </label>
                                        @endif
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <button type="submit" name="action" value="reply"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                            💬 Envoyer la réponse
                                        </button>

                                        @if(in_array($ticket->status, ['OPEN', 'IN_PROGRESS']))
                                            <button type="submit" name="action" value="resolve"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                                ✅ Répondre et résoudre
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="p-6 border-t bg-gray-100">
                            <p class="text-center text-gray-600 font-medium">
                                🔒 Ce ticket est fermé - Plus de réponses possibles
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar avec informations -->
            <div class="lg:col-span-1">
                <div class="space-y-6">
                    <!-- Informations du ticket -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Informations</h3>

                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Client</dt>
                                <dd class="text-sm text-gray-900">
                                    <a href="{{ route('commercial.clients.show', $ticket->client) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        {{ $ticket->client->first_name }} {{ $ticket->client->last_name }}
                                    </a>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $ticket->client->email }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Téléphone</dt>
                                <dd class="text-sm text-gray-900">{{ $ticket->client->phone ?? 'Non renseigné' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Priorité</dt>
                                <dd class="text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $ticket->priority === 'URGENT' ? 'text-red-800 bg-red-100' :
                                           ($ticket->priority === 'HIGH' ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100') }}">
                                        {{ $ticket->priority_display }}
                                    </span>
                                </dd>
                            </div>

                            @if($ticket->assignedTo)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Assigné à</dt>
                                    <dd class="text-sm text-gray-900">{{ $ticket->assignedTo->name }}</dd>
                                </div>
                            @endif

                            @if($ticket->first_response_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Première réponse</dt>
                                    <dd class="text-sm text-gray-900">{{ $ticket->first_response_at->format('d/m/Y à H:i') }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Dernière activité</dt>
                                <dd class="text-sm text-gray-900">{{ $ticket->last_activity_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($ticket->complaint)
                        <!-- Réclamation liée -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Réclamation liée</h3>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600">Réclamation #{{ $ticket->complaint->id }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $ticket->complaint->type }}</p>
                                </div>

                                @if($ticket->complaint->package)
                                    <div>
                                        <p class="text-sm text-gray-600">Colis</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $ticket->complaint->package->package_code }}</p>
                                    </div>
                                @endif

                                <a href="{{ route('commercial.complaints.show', $ticket->complaint) }}"
                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                    Voir la réclamation →
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($ticket->package)
                        <!-- Colis lié -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Colis lié</h3>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600">Code colis</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $ticket->package->package_code }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">Statut</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $ticket->package->status }}</p>
                                </div>

                                <a href="{{ route('commercial.packages.show', $ticket->package) }}"
                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                    Voir le colis →
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Actions administratives -->
                    @if($ticket->status !== 'CLOSED')
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Actions</h3>

                            <div class="space-y-3">
                                <!-- Assignation -->
                                <form method="POST" action="{{ route('commercial.tickets.assign', $ticket) }}">
                                    @csrf
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                                        Réassigner à
                                    </label>
                                    <select name="assigned_to" id="assigned_to"
                                            class="w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Non assigné</option>
                                        @foreach(\App\Models\User::where('role', 'COMMERCIAL')->get() as $commercial)
                                            <option value="{{ $commercial->id }}" {{ $ticket->assigned_to_id == $commercial->id ? 'selected' : '' }}>
                                                {{ $commercial->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="mt-2 w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm font-medium">
                                        Assigner
                                    </button>
                                </form>

                                <!-- Changement de statut -->
                                @if(in_array($ticket->status, ['OPEN', 'IN_PROGRESS']))
                                    <div class="pt-3 border-t">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Changer le statut</p>
                                        <div class="grid grid-cols-1 gap-2">
                                            @if($ticket->status !== 'URGENT')
                                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="URGENT">
                                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium">
                                                        🚨 Marquer urgent
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="RESOLVED">
                                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium">
                                                    ✅ Marquer résolu
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Statistiques du client -->
                    <div class="bg-white rounded-lg shadow-sm border p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Client</h3>

                        @php
                            $clientStats = [
                                'tickets' => $ticket->client->tickets()->count(),
                                'packages' => $ticket->client->packages()->count(),
                                'complaints' => $ticket->client->complaints()->count()
                            ];
                        @endphp

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Tickets créés</dt>
                                <dd class="font-medium text-gray-900">{{ $clientStats['tickets'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Colis envoyés</dt>
                                <dd class="font-medium text-gray-900">{{ $clientStats['packages'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Réclamations</dt>
                                <dd class="font-medium text-gray-900">{{ $clientStats['complaints'] }}</dd>
                            </div>
                        </dl>
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

// Confirmation for status changes
document.querySelectorAll('form[action*="update-status"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const status = form.querySelector('input[name="status"]').value;
        const confirmMessages = {
            'URGENT': 'Marquer ce ticket comme urgent ? Il sera visible dans l\'interface superviseur.',
            'RESOLVED': 'Marquer ce ticket comme résolu ? Le client pourra encore répondre mais ne pourra plus créer de nouveaux messages.',
            'CLOSED': 'Fermer définitivement ce ticket ? Cette action est irréversible.'
        };

        if (confirmMessages[status] && !confirm(confirmMessages[status])) {
            e.preventDefault();
        }
    });
});
</script>
@endpush