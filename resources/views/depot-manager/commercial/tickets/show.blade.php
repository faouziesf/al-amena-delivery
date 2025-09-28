@extends('layouts.depot-manager')

@section('title', "Support Ticket #{$ticket->id}")

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-green-50">
    <!-- Header compact pour commercial -->
    <div class="bg-white shadow-lg border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-600 to-emerald-700 rounded-xl flex items-center justify-center text-white font-bold">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">{{ $ticket->subject }}</h1>
                        <div class="flex items-center space-x-3 text-sm text-slate-500">
                            <span>Ticket #{{ $ticket->id }}</span>
                            <span>•</span>
                            <span>{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                            @if($ticket->client)
                                <span>•</span>
                                <span>{{ $ticket->client->first_name }} {{ $ticket->client->last_name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Actions rapides -->
                    @if(!$ticket->isResolved() && !$ticket->isClosed())
                        <form action="{{ route('depot-manager.commercial.tickets.update-status', $ticket) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="RESOLVED">
                            <button type="submit" class="flex items-center space-x-1 px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Résoudre</span>
                            </button>
                        </form>
                    @endif

                    <!-- Bouton d'appel client -->
                    @if($ticket->client && $ticket->client->phone)
                    <button onclick="contactTicketClient('{{ $ticket->client->phone }}')"
                            class="flex items-center space-x-2 px-4 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-xl transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Appeler</span>
                    </button>
                    @endif

                    <!-- Bouton détails client -->
                    <button onclick="toggleTicketDetails()"
                            class="flex items-center space-x-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors duration-200">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">Client & Détails</span>
                    </button>

                    <!-- Status compact -->
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($ticket->status === 'OPEN') bg-green-100 text-green-800 border border-green-200
                        @elseif($ticket->status === 'IN_PROGRESS') bg-blue-100 text-blue-800 border border-blue-200
                        @elseif($ticket->status === 'RESOLVED') bg-purple-100 text-purple-800 border border-purple-200
                        @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                        {{ $ticket->status_display }}
                        @if($ticket->priority === 'HIGH')
                            <span class="ml-2 text-red-600">🔥</span>
                        @endif
                    </div>

                    <!-- Navigation rapide -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('depot-manager.commercial.tickets.index') }}"
                           class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                           title="Retour aux tickets">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        @if($ticket->package)
                            <a href="{{ route('commercial.packages.show', $ticket->package) }}"
                               class="p-2 text-green-400 hover:text-green-600 rounded-lg hover:bg-green-50 transition-colors"
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
        <div class="flex-1 overflow-y-auto bg-gradient-to-b from-green-50/20 to-white px-6 py-6" id="messagesContainer">
            <div class="max-w-5xl mx-auto space-y-4">
                @forelse($ticket->messages as $message)
                    <x-chat-message :message="$message" :isOwn="!$message->isFromClient()" />
                @empty
                    <div class="flex items-center justify-center h-64 text-slate-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <h3 class="text-lg font-semibold mb-1">Conversation vide</h3>
                            <p class="text-sm">Soyez le premier à répondre à ce client</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Reply Form - Fixée en bas -->
        @if($ticket->canCommercialAddMessages())
        <div class="border-t border-slate-200 bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4">
            <div class="max-w-5xl mx-auto">
                <form action="{{ route('depot-manager.commercial.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="replyForm">
                    @csrf

                    <!-- Message Input -->
                    <div class="relative">
                        <textarea name="message" id="message"
                                  rows="3"
                                  placeholder="Rédigez votre réponse professionnelle... (Ctrl+Entrée pour envoyer)"
                                  class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none transition-all duration-200 bg-white shadow-sm">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions Bar -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <!-- Type de réponse -->
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-2 text-sm">
                                    <input type="radio" name="is_internal" value="0" checked class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="font-medium text-slate-700">Réponse publique</span>
                                </label>
                                <label class="flex items-center space-x-2 text-sm">
                                    <input type="radio" name="is_internal" value="1" class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                                    <span class="font-medium text-amber-700">Note interne</span>
                                </label>
                            </div>

                            <!-- File Upload -->
                            <label class="flex items-center space-x-2 px-3 py-2 bg-white hover:bg-slate-50 rounded-lg cursor-pointer transition-colors duration-200 border border-slate-200 shadow-sm">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-sm font-medium text-slate-700">Joindre</span>
                                <input type="file" name="attachments[]" id="attachments" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt" class="hidden" onchange="updateFilePreview(this)">
                            </label>

                            <!-- File Preview -->
                            <div id="filePreview" class="hidden text-sm text-slate-600 bg-slate-50 px-3 py-2 rounded-lg"></div>
                        </div>

                        <!-- Send Button -->
                        <button type="submit"
                                class="flex items-center space-x-2 px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Répondre</span>
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
            <div class="max-w-5xl mx-auto">
                <div class="text-slate-600">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="font-medium">Ticket fermé</p>
                    <p class="text-sm text-slate-500">Plus de réponses possibles</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Popup des détails commercial -->
    <div id="ticketDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 transition-opacity duration-300">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="modalContent">

                <!-- Header du modal commercial -->
                <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-6 text-white rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <h2 class="text-xl font-bold">Informations Client & Ticket</h2>
                        </div>
                        <button onclick="toggleTicketDetails()" class="text-white hover:text-green-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- Informations Client -->
                        <div class="space-y-6">
                            <div class="bg-blue-50 rounded-2xl p-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    Informations Client
                                </h3>

                                <div class="space-y-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                            {{ strtoupper(substr($ticket->client->first_name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-xl text-slate-900">{{ $ticket->client->first_name }} {{ $ticket->client->last_name }}</p>
                                            <p class="text-slate-600">{{ $ticket->client->email }}</p>
                                            @if($ticket->client->phone)
                                                <p class="text-slate-600 flex items-center mt-1">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                                    </svg>
                                                    {{ $ticket->client->phone }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions client -->
                                    <div class="pt-4 border-t border-blue-200">
                                        <div class="grid grid-cols-2 gap-3">
                                            <a href="mailto:{{ $ticket->client->email }}" class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                                </svg>
                                                Email
                                            </a>
                                            @if($ticket->client->phone)
                                                <a href="tel:{{ $ticket->client->phone }}" class="flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                                    </svg>
                                                    Appeler
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions administratives -->
                            <div class="bg-slate-50 rounded-2xl p-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Actions rapides</h3>
                                <div class="space-y-3">
                                    @if(!$ticket->isResolved() && !$ticket->isClosed())
                                        <form action="{{ route('depot-manager.commercial.tickets.update-status', $ticket) }}" method="POST" class="inline w-full">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="RESOLVED">
                                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition-colors duration-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Marquer comme résolu
                                            </button>
                                        </form>
                                    @endif

                                    @if($ticket->priority !== 'HIGH')
                                        <form action="{{ route('depot-manager.commercial.tickets.update-priority', $ticket) }}" method="POST" class="inline w-full">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="priority" value="HIGH">
                                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors duration-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                                                </svg>
                                                Marquer urgent
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Détails du Ticket -->
                        <div class="space-y-6">
                            <!-- Informations principales -->
                            <div class="bg-green-50 rounded-2xl p-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">Détails du ticket</h3>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="bg-white rounded-lg p-3">
                                        <div class="text-sm text-slate-600 mb-1">Statut</div>
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($ticket->status === 'OPEN') bg-green-100 text-green-800
                                            @elseif($ticket->status === 'IN_PROGRESS') bg-blue-100 text-blue-800
                                            @elseif($ticket->status === 'RESOLVED') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $ticket->status_display }}
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg p-3">
                                        <div class="text-sm text-slate-600 mb-1">Priorité</div>
                                        <div class="text-sm font-semibold {{ $ticket->priority === 'HIGH' ? 'text-red-600' : 'text-slate-900' }} flex items-center">
                                            @if($ticket->priority === 'HIGH')
                                                🔥 Élevée
                                            @else
                                                ✅ Normale
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between py-2 border-b border-green-100">
                                        <span class="text-slate-600 font-medium">Type</span>
                                        <span class="font-semibold text-slate-900">{{ $ticket->type_display }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-green-100">
                                        <span class="text-slate-600 font-medium">Messages</span>
                                        <span class="font-bold text-slate-900">{{ $ticket->messages->count() }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-slate-600 font-medium">Créé le</span>
                                        <span class="font-medium text-slate-900">{{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($ticket->description)
                                <div class="bg-slate-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-bold text-slate-900 mb-3">Description initiale</h3>
                                    <div class="bg-white rounded-lg p-4 text-slate-700 leading-relaxed">
                                        {!! nl2br(e($ticket->description)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Informations sur le colis -->
                            @if($ticket->is_complaint && $ticket->package)
                                <div class="bg-orange-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-bold text-slate-900 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                                        </svg>
                                        Réclamation
                                    </h3>
                                    <div class="bg-white rounded-lg p-4">
                                        <div class="text-orange-800 font-bold text-lg mb-2">{{ $ticket->package->package_code }}</div>
                                        <a href="{{ route('commercial.packages.show', $ticket->package) }}"
                                           class="inline-flex items-center text-sm text-orange-600 hover:text-orange-800 transition-colors font-medium">
                                            Voir tous les détails du colis
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
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

// File preview for commercial interface
function updateFilePreview(input) {
    const preview = document.getElementById('filePreview');
    if (input.files.length > 0) {
        preview.classList.remove('hidden');
        let fileNames = [];
        for (let i = 0; i < Math.min(input.files.length, 2); i++) {
            fileNames.push(input.files[i].name);
        }
        if (input.files.length > 2) {
            preview.innerHTML = `<span class="font-medium">${fileNames.join(', ')} + ${input.files.length - 2} autres</span>`;
        } else {
            preview.innerHTML = `<span class="font-medium">${fileNames.join(', ')}</span>`;
        }
    } else {
        preview.classList.add('hidden');
    }
}

// Enhanced form validation for commercial
function validateCommercialForm(e) {
    const message = document.getElementById('message').value.trim();
    const files = document.getElementById('attachments').files;
    const isInternal = document.querySelector('input[name="is_internal"]:checked').value;

    if (!message && files.length === 0) {
        e.preventDefault();

        // Professional notification
        showNotification('Veuillez rédiger un message ou joindre un document', 'warning');
        return false;
    }

    // Show sending notification
    if (message || files.length > 0) {
        const responseType = isInternal === '1' ? 'note interne' : 'réponse publique';
        showNotification(`Envoi de la ${responseType} en cours...`, 'info');
    }
}

// Professional notification system
function showNotification(message, type = 'info') {
    const colors = {
        'info': 'bg-blue-500 border-blue-600',
        'success': 'bg-green-500 border-green-600',
        'warning': 'bg-amber-500 border-amber-600',
        'error': 'bg-red-500 border-red-600'
    };

    const icons = {
        'info': '📋',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌'
    };

    const notification = document.createElement('div');
    notification.className = `fixed top-6 right-6 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-xl border-2 z-50 transform transition-all duration-500 translate-x-full opacity-0`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <span class="text-lg">${icons[type]}</span>
            <div class="font-medium">${message}</div>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Animate out
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

// Initialize commercial interface
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();

    // Form validation
    const form = document.getElementById('replyForm');
    if (form) {
        form.addEventListener('submit', validateCommercialForm);
    }

    // Auto-scroll for new messages
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                setTimeout(scrollToBottom, 100);
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

    // Professional keyboard shortcuts
    const messageTextarea = document.getElementById('message');
    if (messageTextarea) {
        // Ctrl+Enter to send
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });

        // Auto-resize textarea
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });
    }

    // Enhanced radio button styling
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const label = this.closest('label');
            const allLabels = document.querySelectorAll('label');

            allLabels.forEach(l => l.classList.remove('bg-slate-100', 'border-slate-300'));

            if (this.checked) {
                label.classList.add('bg-slate-100', 'border-slate-300', 'rounded-lg', 'px-3', 'py-2', 'border');
            }
        });
    });

    // Professional loading states
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton && form) {
        form.addEventListener('submit', function() {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Envoi en cours...</span>
            `;
        });
    }

    // Initialize commercial modal functionality
    initializeCommercialModal();
});

// Toggle ticket details modal for commercial
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

// Initialize commercial-specific modal features
function initializeCommercialModal() {
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

    // Contact ticket client function
    window.contactTicketClient = function(phone) {
        if (phone) {
            window.location.href = `tel:${phone}`;
        } else {
            alert('Numéro de téléphone du client non disponible');
        }
    };
}
</script>
@endsection