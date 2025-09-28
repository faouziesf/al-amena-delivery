@extends('layouts.client')

@section('title', "Ticket #{$ticket->id} - {$ticket->subject}")

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header compact -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl flex items-center justify-center text-white font-bold">
                        #
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">{{ $ticket->subject }}</h1>
                        <div class="flex items-center space-x-3 text-sm text-slate-500">
                            <span>Ticket #{{ $ticket->id }}</span>
                            <span>•</span>
                            <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Bouton détails -->
                    <button onclick="toggleTicketDetails()"
                            class="flex items-center space-x-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors duration-200">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">Détails</span>
                    </button>

                    <!-- Status compact -->
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($ticket->status === 'OPEN') bg-green-100 text-green-800 border border-green-200
                        @elseif($ticket->status === 'IN_PROGRESS') bg-blue-100 text-blue-800 border border-blue-200
                        @elseif($ticket->status === 'RESOLVED') bg-purple-100 text-purple-800 border border-purple-200
                        @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                        {{ $ticket->status_display }}
                    </div>

                    <!-- Navigation rapide -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('client.tickets.index') }}"
                           class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                           title="Retour aux tickets">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        @if($ticket->package)
                            <a href="{{ route('client.packages.show', $ticket->package) }}"
                               class="p-2 text-blue-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                               title="Voir le colis {{ $ticket->package->package_code }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container principal plein écran -->
    <div class="h-[calc(100vh-80px)] flex flex-col">

        <!-- Messages Area - Plein écran -->
        <div class="flex-1 overflow-y-auto bg-gradient-to-b from-slate-50/30 to-white px-6 py-6" id="messagesContainer">
            <div class="max-w-4xl mx-auto space-y-4">
                @forelse($ticket->messages as $message)
                    <x-chat-message :message="$message" :isOwn="$message->isFromClient()" />
                @empty
                    <div class="flex items-center justify-center h-64 text-slate-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <h3 class="text-lg font-semibold mb-1">Conversation vide</h3>
                            <p class="text-sm">Commencez par écrire votre premier message</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Reply Form - Fixée en bas -->
        @if($ticket->canClientAddMessages())
        <div class="border-t border-slate-200 bg-white px-6 py-4">
            <div class="max-w-4xl mx-auto">
                <form action="{{ route('client.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="replyForm">
                    @csrf

                    <!-- Message Input -->
                    <div class="relative">
                        <textarea name="message" id="message"
                                  rows="2"
                                  placeholder="Tapez votre message ici... (Ctrl+Entrée pour envoyer)"
                                  class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition-all duration-200 bg-white shadow-sm">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions Bar -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- File Upload -->
                            <label class="flex items-center space-x-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg cursor-pointer transition-colors duration-200">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-sm text-slate-700">Joindre</span>
                                <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt" class="hidden" onchange="updateFilePreview(this)">
                            </label>

                            <!-- File Preview -->
                            <div id="filePreview" class="hidden text-sm text-slate-600 bg-slate-50 px-3 py-2 rounded-lg"></div>
                        </div>

                        <!-- Send Button -->
                        <button type="submit"
                                class="flex items-center space-x-2 px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Envoyer</span>
                        </button>
                    </div>

                    @error('attachments.*')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>
        @else
        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="text-slate-600">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="font-medium">Ce ticket est fermé</p>
                    <p class="text-sm text-slate-500">Vous ne pouvez plus ajouter de messages</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Popup des détails -->
    <div id="ticketDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 transition-opacity duration-300">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="modalContent">

                <!-- Header du modal -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6 text-white rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">Détails du ticket</h2>
                        <button onclick="toggleTicketDetails()" class="text-white hover:text-blue-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Informations principales -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="text-sm text-slate-600 mb-1">Statut</div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($ticket->status === 'OPEN') bg-green-100 text-green-800
                                @elseif($ticket->status === 'IN_PROGRESS') bg-blue-100 text-blue-800
                                @elseif($ticket->status === 'RESOLVED') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $ticket->status_display }}
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="text-sm text-slate-600 mb-1">Priorité</div>
                            <div class="text-sm font-semibold {{ $ticket->priority === 'HIGH' ? 'text-red-600' : 'text-slate-900' }}">
                                {{ $ticket->priority === 'HIGH' ? 'Élevée' : 'Normale' }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($ticket->description)
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 mb-3">Description initiale</h3>
                            <div class="bg-slate-50 rounded-lg p-4 text-slate-700 leading-relaxed">
                                {!! nl2br(e($ticket->description)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Informations sur le colis -->
                    @if($ticket->is_complaint && $ticket->package)
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 mb-3">Colis concerné</h3>
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <div class="flex items-center space-x-2 text-orange-700 mb-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium">Réclamation</span>
                                </div>
                                <div class="text-orange-800 font-semibold">{{ $ticket->package->package_code }}</div>
                                <a href="{{ route('client.packages.show', $ticket->package) }}"
                                   class="inline-flex items-center mt-2 text-sm text-orange-600 hover:text-orange-800 transition-colors">
                                    Voir les détails du colis
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Historique -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-3">Historique</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-slate-100">
                                <span class="text-slate-600">Ticket créé</span>
                                <span class="font-medium">{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            @if($ticket->resolved_at)
                                <div class="flex justify-between py-2 border-b border-slate-100">
                                    <span class="text-slate-600">Résolu le</span>
                                    <span class="font-medium text-green-600">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between py-2">
                                <span class="text-slate-600">Messages échangés</span>
                                <span class="font-medium">{{ $ticket->messages->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($ticket->status === 'RESOLVED' && $ticket->status !== 'CLOSED')
                        <div class="border-t border-slate-200 pt-4">
                            <form action="{{ route('client.tickets.mark-resolved', $ticket) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition-colors duration-200">
                                    ✅ Marquer comme résolu définitivement
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages
function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

// File preview
function updateFilePreview(input) {
    const preview = document.getElementById('filePreview');
    if (input.files.length > 0) {
        preview.classList.remove('hidden');
        let fileNames = [];
        for (let i = 0; i < Math.min(input.files.length, 2); i++) {
            fileNames.push(input.files[i].name);
        }
        if (input.files.length > 2) {
            preview.innerHTML = `${fileNames.join(', ')} + ${input.files.length - 2} autres`;
        } else {
            preview.innerHTML = fileNames.join(', ');
        }
    } else {
        preview.classList.add('hidden');
    }
}

// Form validation
function validateForm(e) {
    const message = document.getElementById('message').value.trim();
    const files = document.getElementById('attachments').files;

    if (!message && files.length === 0) {
        e.preventDefault();

        // Modern alert style
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300';
        alertDiv.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                </svg>
                <span>Veuillez écrire un message ou joindre un fichier</span>
            </div>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 4000);

        return false;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();

    // Add form validation
    const form = document.getElementById('replyForm');
    if (form) {
        form.addEventListener('submit', validateForm);
    }

    // Add smooth scrolling for new messages
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                scrollToBottom();
            }
        });
    });

    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        observer.observe(messagesContainer, {
            childList: true,
            subtree: true
        });
    }

    // Add enter key shortcut (Ctrl+Enter to send)
    const messageTextarea = document.getElementById('message');
    if (messageTextarea) {
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                form.submit();
            }
        });
    }
});

// Add typing indicator animation
function addTypingAnimation() {
    const textarea = document.getElementById('message');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
}

// Initialize typing animation
document.addEventListener('DOMContentLoaded', addTypingAnimation);

// Toggle ticket details modal
function toggleTicketDetails() {
    const modal = document.getElementById('ticketDetailsModal');
    const modalContent = document.getElementById('modalContent');

    if (modal.classList.contains('hidden')) {
        // Show modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    } else {
        // Hide modal
        modal.classList.add('opacity-0');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('ticketDetailsModal');
    if (e.target === modal) {
        toggleTicketDetails();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('ticketDetailsModal');
        if (!modal.classList.contains('hidden')) {
            toggleTicketDetails();
        }
    }
});
</script>
@endsection