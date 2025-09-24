@extends('layouts.client')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('client.tickets.index') }}" class="hover:text-gray-700">Tickets</a>
                <span>/</span>
                <span class="text-gray-900">#{{ $ticket->ticket_number }}</span>
            </nav>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $ticket->status_color }}">
                            {{ $ticket->status_display }}
                        </span>
                        <span class="text-sm text-gray-500">#{{ $ticket->ticket_number }}</span>
                        <span class="text-sm text-gray-500">{{ $ticket->type_display }}</span>
                        @if($ticket->priority !== 'NORMAL')
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->priority_color }} bg-opacity-10">
                                {{ $ticket->priority_display }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($ticket->canAddMessages())
                    <button onclick="scrollToReply()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Répondre
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Messages et réponses -->
            <div class="lg:col-span-2">
                <!-- Informations du ticket -->
                <div class="bg-white rounded-lg shadow-sm border mb-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Description initiale</h3>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>

                        @if($ticket->complaint)
                            <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-orange-800 font-medium">
                                        Réclamation associée #{{ $ticket->complaint->id }}
                                    </p>
                                </div>
                                <p class="text-orange-700 text-sm mt-1">
                                    Ce ticket a été créé automatiquement depuis votre réclamation
                                </p>
                            </div>
                        @endif

                        @if($ticket->package)
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <p class="text-blue-800 font-medium">
                                        Colis concerné: {{ $ticket->package->package_code }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 text-sm text-gray-600">
                        Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
                        @if($ticket->assignedTo)
                            • Assigné à {{ $ticket->assignedTo->name }}
                        @endif
                    </div>
                </div>

                <!-- Messages de conversation -->
                @if($ticket->messages->count() > 1)
                    <div class="bg-white rounded-lg shadow-sm border">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900">Conversation</h3>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @foreach($ticket->messages->skip(1) as $message)
                                <div class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-{{ $message->isFromClient() ? 'blue' : 'green' }}-100 flex items-center justify-center">
                                                @if($message->isFromClient())
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $message->sender_name }}
                                                </p>
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $message->isFromClient() ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $message->sender_type_display }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ $message->created_at->format('d/m/Y à H:i') }}
                                                </span>
                                            </div>

                                            <div class="prose prose-sm max-w-none text-gray-700 mb-3">
                                                {!! nl2br(e($message->message)) !!}
                                            </div>

                                            @if($message->hasAttachments())
                                                <div class="space-y-2">
                                                    <p class="text-sm font-medium text-gray-700">Pièces jointes :</p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                        @foreach($message->formatted_attachments as $attachment)
                                                            <a href="{{ $attachment['url'] }}" target="_blank"
                                                               class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                                </svg>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment['name'] }}</p>
                                                                    @if(isset($attachment['size']))
                                                                        <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 1) }} KB</p>
                                                                    @endif
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Formulaire de réponse -->
                @if($ticket->canAddMessages())
                    <div id="reply-form" class="bg-white rounded-lg shadow-sm border mt-6">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900">Ajouter une réponse</h3>
                        </div>

                        <form method="POST" action="{{ route('client.tickets.add.message', $ticket) }}" enctype="multipart/form-data" class="p-6">
                            @csrf

                            <div class="mb-4">
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Votre message <span class="text-red-500">*</span>
                                </label>
                                <textarea name="message" id="message" rows="4" required
                                          placeholder="Tapez votre réponse ici..."
                                          class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-300 @enderror">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="reply_attachments" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pièces jointes (optionnel)
                                </label>
                                <input type="file" name="attachments[]" id="reply_attachments" multiple
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                       class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">
                                    Formats acceptés: JPG, PNG, PDF, DOC, DOCX (max 10MB par fichier)
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button" onclick="markAsResolved()"
                                        class="text-green-600 hover:text-green-800 font-medium">
                                    ✓ Marquer comme résolu
                                </button>

                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Envoyer la réponse
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-100 rounded-lg p-6 text-center mt-6">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m1-5V9a2 2 0 112 0v3M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Ticket fermé</h3>
                        <p class="text-gray-600">
                            Ce ticket est fermé et ne peut plus recevoir de nouveaux messages.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Sidebar d'informations -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations du ticket</h3>

                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Statut</dt>
                            <dd class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->status_color }}">
                                    {{ $ticket->status_display }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->type_display }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priorité</dt>
                            <dd class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ticket->priority_color }} bg-opacity-10">
                                    {{ $ticket->priority_display }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>

                        @if($ticket->assignedTo)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigné à</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->assignedTo->name }}</dd>
                            </div>
                        @endif

                        @if($ticket->first_response_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Première réponse</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->first_response_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        @endif

                        @if($ticket->resolved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Résolu le</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dernière activité</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $ticket->last_activity_at ? $ticket->last_activity_at->format('d/m/Y à H:i') : 'Aucune' }}
                            </dd>
                        </div>
                    </dl>

                    @if($ticket->canAddMessages())
                        <hr class="my-6">
                        <form method="POST" action="{{ route('client.tickets.mark.resolved', $ticket) }}"
                              onsubmit="return confirm('Êtes-vous sûr que ce ticket est résolu ?')">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                ✓ Marquer comme résolu
                            </button>
                        </form>
                    @endif

                    <hr class="my-6">

                    <div class="text-sm text-gray-600">
                        <h4 class="font-medium text-gray-900 mb-2">Besoin d'aide ?</h4>
                        <p class="mb-2">
                            Notre équipe support vous répondra dans les plus brefs délais selon la priorité de votre ticket.
                        </p>
                        <p>
                            📧 support@alamena.com<br>
                            📱 +216 70 123 456
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scrollToReply() {
    document.getElementById('reply-form').scrollIntoView({ behavior: 'smooth' });
    document.getElementById('message').focus();
}

function markAsResolved() {
    if (confirm('Marquer ce ticket comme résolu ? Cette action informera notre équipe que votre problème est résolu.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("client.tickets.mark.resolved", $ticket) }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection