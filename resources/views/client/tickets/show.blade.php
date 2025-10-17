<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ticket->subject }} - Ticket #{{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }
        
        /* Scrollbar personnalisée */
        .messages-container::-webkit-scrollbar { width: 8px; }
        .messages-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .messages-container::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
        .messages-container::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
        
        /* Animation entrée message */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-item { animation: slideIn 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-50">
    
    <div class="h-screen flex flex-col" x-data="ticketChat()">
        
        <!-- HEADER FIXE -->
        <header class="bg-white border-b border-gray-200 shadow-sm flex-shrink-0 z-20">
            <div class="px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    
                    <!-- Info Ticket -->
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <a href="{{ route('client.tickets.index') }}" 
                           class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg sm:text-xl font-black text-gray-900 truncate">
                                {{ $ticket->subject }}
                            </h1>
                            <div class="flex items-center gap-2 text-sm mt-1">
                                <span class="text-gray-500 font-medium">#{{ $ticket->ticket_number }}</span>
                                <span class="text-gray-300">•</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                    @if($ticket->status === 'OPEN') bg-green-100 text-green-700
                                    @elseif($ticket->status === 'IN_PROGRESS') bg-blue-100 text-blue-700
                                    @elseif($ticket->status === 'RESOLVED') bg-purple-100 text-purple-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    @if($ticket->status === 'OPEN') 🟢 OUVERT
                                    @elseif($ticket->status === 'IN_PROGRESS') 🔵 EN COURS
                                    @elseif($ticket->status === 'RESOLVED') 🟣 RÉSOLU
                                    @else ⚪ {{ $ticket->status }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions Header -->
                    <div class="flex items-center gap-2">
                        @if($ticket->package)
                        <a href="{{ route('client.packages.show', $ticket->package) }}" 
                           class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Voir le colis</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- ZONE MESSAGES SCROLLABLE -->
        <div class="flex-1 overflow-y-auto messages-container bg-gradient-to-b from-gray-50 to-gray-100" 
             x-ref="messagesContainer">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-6">
                
                <!-- Message Initial du Ticket -->
                <div class="message-item">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($ticket->client->name ?? 'CL', 0, 2)) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-bold text-gray-900">{{ $ticket->client->name ?? 'Client' }}</span>
                                <span class="text-sm text-gray-500">{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                    Vous
                                </span>
                            </div>
                            <div class="bg-white rounded-2xl rounded-tl-none shadow-sm border border-gray-200 p-4">
                                <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</p>
                                
                                @if($ticket->package_code)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span class="text-gray-600">Colis:</span>
                                        <span class="font-mono font-bold text-indigo-600">{{ $ticket->package_code }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages/Réponses -->
                @foreach($ticket->messages as $message)
                <div class="message-item">
                    <div class="flex gap-3 {{ $message->is_admin_reply ? '' : 'flex-row-reverse' }}">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold
                                {{ $message->is_admin_reply ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 'bg-gradient-to-br from-indigo-500 to-purple-600' }}">
                                @if($message->is_admin_reply)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                @else
                                    {{ strtoupper(substr($ticket->client->name ?? 'CL', 0, 2)) }}
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 {{ $message->is_admin_reply ? '' : 'flex flex-col items-end' }}">
                            <div class="flex items-center gap-2 mb-2 {{ $message->is_admin_reply ? '' : 'flex-row-reverse' }}">
                                <span class="font-bold text-gray-900">
                                    {{ $message->is_admin_reply ? 'Support Al-Amena' : ($ticket->client->name ?? 'Client') }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $message->created_at->format('d/m/Y à H:i') }}</span>
                                @if(!$message->is_admin_reply)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                    Vous
                                </span>
                                @endif
                            </div>
                            <div class="rounded-2xl shadow-sm border p-4 max-w-2xl
                                {{ $message->is_admin_reply ? 'bg-green-50 border-green-200 rounded-tl-none' : 'bg-white border-gray-200 rounded-tr-none' }}">
                                <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($ticket->messages->isEmpty() && $ticket->status !== 'RESOLVED')
                <!-- Empty state -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">En attente de réponse du support</p>
                    <p class="text-sm text-gray-400 mt-1">Vous recevrez une notification dès qu'on vous répond</p>
                </div>
                @endif

            </div>
        </div>

        <!-- FOOTER FIXE - Zone d'écriture -->
        @if($ticket->status !== 'RESOLVED')
        <footer class="bg-white border-t border-gray-200 shadow-lg flex-shrink-0">
            <div class="max-w-4xl mx-auto px-3 sm:px-4 py-2">
                <form action="{{ route('client.tickets.reply', $ticket) }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      @submit="scrollToBottom()"
                      class="space-y-2">
                    @csrf
                    
                    <!-- Zone de texte -->
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <textarea name="message" 
                                      required
                                      rows="2"
                                      x-model="newMessage"
                                      placeholder="Écrivez votre message..."
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none transition-all"
                                      @keydown.ctrl.enter="$el.form.submit()"></textarea>
                        </div>
                        
                        <div class="flex gap-1">
                            <!-- Bouton Pièce jointe -->
                            <button type="button" 
                                    @click="$refs.fileInput.click()"
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </button>
                            <input type="file" 
                                   name="attachments[]" 
                                   multiple 
                                   x-ref="fileInput"
                                   @change="handleFiles($event)"
                                   class="hidden">
                            
                            <!-- Bouton Envoyer -->
                            <button type="submit" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-md hover:shadow-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Aperçu fichiers -->
                    <div x-show="files.length > 0" class="flex flex-wrap gap-1.5">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-100 rounded text-xs">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-gray-700 font-medium" x-text="file.name"></span>
                                <button type="button" 
                                        @click="removeFile(index)"
                                        class="text-red-500 hover:text-red-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Info raccourci - version compacte -->
                    <div class="flex items-center justify-end text-xs text-gray-400">
                        <span><span x-text="newMessage.length"></span> • Ctrl+↵</span>
                    </div>
                </form>
            </div>
        </footer>
        @else
        <!-- Ticket résolu -->
        <footer class="bg-gradient-to-r from-purple-100 to-indigo-100 border-t border-purple-200 flex-shrink-0">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 text-center">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-lg font-bold text-purple-900">Ticket résolu</span>
                </div>
                <p class="text-sm text-purple-700">Ce ticket a été résolu. Vous ne pouvez plus envoyer de messages.</p>
                <a href="{{ route('client.tickets.index') }}" 
                   class="inline-flex items-center gap-2 mt-4 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold transition-colors">
                    Retour aux tickets
                </a>
            </div>
        </footer>
        @endif

    </div>

    <script>
    function ticketChat() {
        return {
            newMessage: '',
            files: [],
            
            init() {
                this.scrollToBottom();
            },
            
            scrollToBottom() {
                this.$nextTick(() => {
                    const container = this.$refs.messagesContainer;
                    container.scrollTop = container.scrollHeight;
                });
            },
            
            handleFiles(event) {
                const newFiles = Array.from(event.target.files);
                this.files = [...this.files, ...newFiles];
            },
            
            removeFile(index) {
                this.files.splice(index, 1);
                this.$refs.fileInput.value = '';
            }
        }
    }
    </script>
</body>
</html>
