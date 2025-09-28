@extends('layouts.commercial')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Header moderne avec gradient -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('commercial.tickets.index') }}"
                       class="text-white/80 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="text-2xl font-bold text-white">{{ $ticket->ticket_number }}</h1>
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full
                                {{ $ticket->status === 'OPEN' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $ticket->status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $ticket->status === 'RESOLVED' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $ticket->status === 'CLOSED' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $ticket->status === 'URGENT' ? 'bg-red-100 text-red-800 animate-pulse' : '' }}">
                                @if($ticket->status === 'URGENT')
                                    🚨 {{ $ticket->status_display }}
                                @else
                                    {{ $ticket->status_display }}
                                @endif
                            </span>
                            @if($ticket->priority === 'HIGH' || $ticket->priority === 'URGENT')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-500 text-white animate-bounce">
                                    ⚡ {{ $ticket->priority_display }}
                                </span>
                            @endif
                        </div>
                        <p class="text-white/90 mt-1">{{ $ticket->subject }}</p>
                        <div class="flex items-center space-x-4 mt-2 text-white/70 text-sm">
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>{{ $ticket->client->name ?? 'Client inconnu' }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            @if($ticket->package)
                                <div class="flex items-center space-x-1 text-yellow-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span>{{ $ticket->package->package_code }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="flex items-center space-x-3">
                    @if($ticket->status !== 'CLOSED')
                        <div class="flex items-center space-x-2">
                            @if($ticket->status === 'OPEN')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="IN_PROGRESS">
                                    <button type="submit"
                                            class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white/30">
                                        ▶️ Prendre en charge
                                    </button>
                                </form>
                            @endif

                            @if($ticket->status === 'IN_PROGRESS')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="RESOLVED">
                                    <button type="submit"
                                            class="bg-green-500/80 hover:bg-green-500 text-white px-4 py-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                                        ✅ Résoudre
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Zone de conversation principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description initiale -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-white p-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $ticket->client->name ?? 'Client' }}</h3>
                                <p class="text-sm text-gray-500">{{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div class="flex-1"></div>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $ticket->type_display }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="prose prose-gray max-w-none">
                            <p class="text-gray-700 leading-relaxed">{{ $ticket->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Messages de conversation -->
                <div class="space-y-4">
                    @forelse($ticket->messages as $message)
                        <div class="flex {{ $message->sender_type === 'CLIENT' ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="flex items-end space-x-2 {{ $message->sender_type === 'CLIENT' ? '' : 'flex-row-reverse space-x-reverse' }}">
                                    <!-- Avatar -->
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 {{ $message->sender_type === 'CLIENT' ? 'bg-gradient-to-r from-blue-500 to-indigo-600' : 'bg-gradient-to-r from-purple-500 to-pink-600' }} flex items-center justify-center">
                                        @if($message->sender_type === 'CLIENT')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-6m-2-5.5V9a2 2 0 012-2h2a2 2 0 012 2v6.5"/>
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Bulle de message -->
                                    <div class="relative">
                                        <div class="px-4 py-3 rounded-2xl shadow-md {{ $message->sender_type === 'CLIENT' ? 'bg-white border border-gray-200' : 'bg-gradient-to-r from-purple-500 to-pink-600 text-white' }}">
                                            <p class="text-sm leading-relaxed">{{ $message->message }}</p>

                                            @if($message->attachments && count($message->attachments) > 0)
                                                <div class="mt-3 space-y-2">
                                                    @foreach($message->attachments as $attachment)
                                                        <div class="flex items-center space-x-2 p-2 {{ $message->sender_type === 'CLIENT' ? 'bg-gray-50' : 'bg-white/20' }} rounded-lg">
                                                            <svg class="w-4 h-4 {{ $message->sender_type === 'CLIENT' ? 'text-gray-500' : 'text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                            </svg>
                                                            <a href="{{ $attachment['url'] }}" target="_blank"
                                                               class="text-xs {{ $message->sender_type === 'CLIENT' ? 'text-gray-700 hover:text-blue-600' : 'text-white hover:text-gray-200' }} truncate">
                                                                {{ $attachment['name'] }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Timestamp -->
                                        <div class="mt-1 px-2">
                                            <span class="text-xs text-gray-500">
                                                {{ $message->created_at->format('d/m H:i') }}
                                                @if($message->is_internal)
                                                    <span class="text-orange-500">• Interne</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-gray-500">Aucun message pour le moment</p>
                            <p class="text-sm text-gray-400">Soyez le premier à répondre au client</p>
                        </div>
                    @endforelse
                </div>

                <!-- Formulaire de réponse moderne -->
                @if($ticket->status !== 'CLOSED')
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-gray-200">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-6m-2-5.5V9a2 2 0 012-2h2a2 2 0 012 2v6.5"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-900">Votre réponse</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('commercial.tickets.add-message', $ticket) }}" enctype="multipart/form-data" class="p-6">
                            @csrf

                            <!-- Zone de texte moderne -->
                            <div class="mb-4">
                                <textarea name="message" rows="4" required
                                          placeholder="Tapez votre réponse au client..."
                                          class="w-full rounded-xl border-gray-200 focus:border-purple-500 focus:ring-purple-500 resize-none transition-all duration-200"
                                          style="font-size: 16px;"></textarea>
                            </div>

                            <!-- Options avancées -->
                            <div class="mb-4 space-y-4">
                                <!-- Pièces jointes -->
                                <div x-data="{ showAttachments: false }">
                                    <button type="button" @click="showAttachments = !showAttachments"
                                            class="flex items-center space-x-2 text-sm text-gray-600 hover:text-purple-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <span>Joindre des fichiers</span>
                                    </button>

                                    <div x-show="showAttachments" x-transition class="mt-3">
                                        <input type="file" name="attachments[]" multiple
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                    </div>
                                </div>

                                <!-- Message interne -->
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="is_internal" value="1"
                                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm text-gray-600">
                                        <span class="text-orange-600">🔒</span>
                                        Message interne (invisible pour le client)
                                    </span>
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 text-sm text-gray-500">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Ctrl+Entrée pour envoyer</span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <button type="submit" name="action" value="reply"
                                            class="bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        Envoyer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-2xl p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Ticket fermé</h3>
                        <p class="text-gray-500">Ce ticket a été résolu et fermé. Aucun nouveau message ne peut être ajouté.</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar avec informations -->
            <div class="space-y-6">
                <!-- Informations du ticket -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informations
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Statut</span>
                            <span class="text-sm font-semibold {{ $ticket->status_color }}">
                                {{ $ticket->status_display }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Priorité</span>
                            <span class="text-sm font-semibold {{ $ticket->priority_color }}">
                                {{ $ticket->priority_display }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Type</span>
                            <span class="text-sm text-gray-900">{{ $ticket->type_display }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Assigné à</span>
                            <span class="text-sm text-gray-900">
                                {{ $ticket->assignedTo->name ?? 'Non assigné' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm font-medium text-gray-600">Créé le</span>
                            <span class="text-sm text-gray-900">{{ $ticket->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                @if($ticket->status !== 'CLOSED')
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Actions rapides
                        </h3>

                        <div class="space-y-3">
                            @if($ticket->status === 'OPEN')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="IN_PROGRESS">
                                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition-colors">
                                        ▶️ Prendre en charge
                                    </button>
                                </form>
                            @endif

                            @if($ticket->status === 'IN_PROGRESS')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="RESOLVED">
                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition-colors">
                                        ✅ Marquer comme résolu
                                    </button>
                                </form>
                            @endif

                            @if(!in_array($ticket->status, ['URGENT']) && $ticket->priority !== 'URGENT')
                                <form method="POST" action="{{ route('commercial.tickets.update-status', $ticket) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="URGENT">
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg transition-colors">
                                        🚨 Marquer urgent
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Client info -->
                @if($ticket->client)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Client
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $ticket->client->name }}</p>
                                @if($ticket->client->phone)
                                    <p class="text-sm text-gray-600">{{ $ticket->client->phone }}</p>
                                @endif
                                @if($ticket->client->email)
                                    <p class="text-sm text-gray-600">{{ $ticket->client->email }}</p>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-gray-200">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                                    Voir le profil complet →
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        const form = document.querySelector('form[action*="messages"]');
        if (form) {
            form.submit();
        }
    }
});

// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.querySelector('.space-y-4');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
@endsection