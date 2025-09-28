@extends('layouts.supervisor')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - Supervision')

@section('content')
<div x-data="ticketDetails()" class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec navigation -->
        <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                <a href="{{ route('supervisor.tickets.index') }}" class="hover:text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Tickets
                </a>
                <span>/</span>
                <a href="{{ route('supervisor.tickets.overview') }}" class="hover:text-gray-700">Vue d'ensemble</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">#{{ $ticket->ticket_number }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-700 to-blue-600 bg-clip-text text-transparent">
                            Ticket #{{ $ticket->ticket_number }}
                        </h1>

                        @php
                            $statusConfig = [
                                'URGENT' => 'bg-red-100 text-red-800',
                                'OPEN' => 'bg-yellow-100 text-yellow-800',
                                'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                'RESOLVED' => 'bg-green-100 text-green-800',
                                'CLOSED' => 'bg-gray-100 text-gray-800'
                            ];
                        @endphp

                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusConfig[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $ticket->status }}
                        </span>

                        @if($ticket->priority === 'URGENT')
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full text-red-800 bg-red-100 animate-pulse">
                                🚨 URGENT
                            </span>
                        @endif

                        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                            {{ $ticket->type ?? 'GÉNÉRAL' }}
                        </span>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h2>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0V3"></path>
                            </svg>
                            Créé {{ $ticket->created_at->format('d/m/Y à H:i') }}
                        </div>

                        <div class="flex items-center">
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
                                Assigné à {{ $ticket->assignedTo->name }}
                            </div>
                        @else
                            <div class="flex items-center text-red-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Non assigné
                            </div>
                        @endif

                        @if($ticket->complaint)
                            <div class="flex items-center text-orange-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Réclamation #{{ $ticket->complaint->id }}
                            </div>
                        @endif

                        @if($ticket->package)
                            <div class="flex items-center text-blue-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                                </svg>
                                {{ $ticket->package->package_code }}
                            </div>
                        @endif

                        <div class="flex items-center text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $ticket->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mt-6 lg:mt-0">
                    @if($ticket->status !== 'CLOSED')
                        @if($ticket->priority !== 'URGENT')
                            <form method="POST" action="{{ route('supervisor.tickets.escalate', $ticket) }}" class="inline">
                                @csrf
                                <input type="hidden" name="reason" value="Escalade depuis vue détaillée par superviseur">
                                <button type="submit"
                                        class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    🚨 Escalader en urgent
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('supervisor.tickets.force-close', $ticket) }}" class="inline">
                            @csrf
                            <input type="hidden" name="reason" value="Fermeture forcée depuis vue détaillée par superviseur">
                            <button type="submit" onclick="return confirm('Fermer définitivement ce ticket ? Cette action est irréversible.')"
                                    class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                🔒 Fermer définitivement
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Messages et conversation principale -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Métriques de performance -->
                @php
                    $responseTime = $ticket->first_response_at
                        ? $ticket->created_at->diffInHours($ticket->first_response_at)
                        : ($ticket->created_at->diffInHours(now()));

                    $resolutionTime = $ticket->resolved_at
                        ? $ticket->created_at->diffInHours($ticket->resolved_at)
                        : null;

                    $messagesCount = $ticket->messages ? $ticket->messages->count() : 0;
                    $hasFirstResponse = $ticket->first_response_at !== null;
                @endphp

                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Métriques de performance
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $hasFirstResponse ? ($responseTime <= 2 ? 'text-green-600' : ($responseTime <= 12 ? 'text-yellow-600' : 'text-red-600')) : 'text-red-600' }}">
                                    {{ $hasFirstResponse ? round($responseTime, 1) . 'h' : 'Aucune' }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Temps de première réponse</div>
                                @if(!$hasFirstResponse)
                                    <div class="text-xs text-red-600 font-medium mt-1">⚠️ Aucune réponse</div>
                                @elseif($responseTime > 24)
                                    <div class="text-xs text-red-600 font-medium mt-1">⚠️ SLA dépassé</div>
                                @elseif($responseTime > 12)
                                    <div class="text-xs text-yellow-600 font-medium mt-1">⚡ Objectif manqué</div>
                                @else
                                    <div class="text-xs text-green-600 font-medium mt-1">✅ Dans les temps</div>
                                @endif
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $resolutionTime ? ($resolutionTime <= 48 ? 'text-green-600' : ($resolutionTime <= 72 ? 'text-yellow-600' : 'text-red-600')) : 'text-blue-600' }}">
                                    {{ $resolutionTime ? round($resolutionTime, 1) . 'h' : 'En cours' }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Temps de résolution</div>
                                @if($resolutionTime && $resolutionTime > 72)
                                    <div class="text-xs text-red-600 font-medium mt-1">⚠️ Très long</div>
                                @endif
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $messagesCount }}</div>
                                <div class="text-sm text-gray-600 mt-1">Messages échangés</div>
                                @if($messagesCount > 10)
                                    <div class="text-xs text-orange-600 font-medium mt-1">📈 Nombreux échanges</div>
                                @elseif($messagesCount === 0)
                                    <div class="text-xs text-red-600 font-medium mt-1">📭 Aucun échange</div>
                                @endif
                            </div>

                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600">
                                    {{ $ticket->created_at->diffInDays(now()) }}
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Jours d'ouverture</div>
                                @if($ticket->created_at->diffInDays(now()) > 7)
                                    <div class="text-xs text-red-600 font-medium mt-1">⏰ Ancien ticket</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description initiale -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Description initiale du problème
                            </h3>
                            <div class="text-sm text-gray-500 flex items-center">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-white text-xs font-medium">{{ strtoupper(substr($ticket->client->name ?? 'C', 0, 1)) }}</span>
                                </div>
                                {{ $ticket->client->name ?? 'Client' }} • {{ $ticket->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                </div>

                <!-- Timeline des messages -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
                            </svg>
                            Timeline de la conversation
                        </h3>
                        @if($messagesCount > 0)
                            <p class="text-sm text-gray-600 mt-1">{{ $messagesCount }} message(s) échangé(s)</p>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($ticket->messages as $message)
                            <div class="p-6 {{ $message->sender_type === 'CLIENT' ? 'bg-blue-50/50' : ($message->is_internal ? 'bg-yellow-50/50' : 'bg-green-50/50') }}">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($message->sender_type === 'CLIENT')
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->name ?? 'C', 0, 1)) }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                                                <span class="text-white text-sm font-medium">
                                                    {{ strtoupper(substr($message->sender->name ?? 'S', 0, 1)) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-900">{{ $message->sender->name ?? 'Utilisateur' }}</span>
                                                @if($message->sender_type === 'CLIENT')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Client</span>
                                                @elseif($message->is_internal)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Note interne</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Support</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $message->created_at->format('d/m/Y à H:i') }}
                                                @if($message->read_at)
                                                    <span class="text-green-600 ml-1">✓ Lu</span>
                                                @else
                                                    <span class="text-orange-600 ml-1">Non lu</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="prose prose-sm max-w-none text-gray-700">
                                            {!! nl2br(e($message->message)) !!}
                                        </div>

                                        @if(isset($message->attachments) && count($message->attachments) > 0)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach($message->attachments as $attachment)
                                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank"
                                                       class="inline-flex items-center px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                        </svg>
                                                        {{ $attachment['name'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun message</h3>
                                <p class="text-gray-600">Aucun message n'a encore été échangé dans ce ticket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar avec informations complètes -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Assignation et réassignation -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Gestion du ticket
                    </h3>

                    <form method="POST" action="{{ route('supervisor.tickets.bulk-reassign') }}">
                        @csrf
                        <input type="hidden" name="ticket_ids[]" value="{{ $ticket->id }}">

                        <div class="mb-4">
                            <label for="assigned_to_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Réassigner à un commercial
                            </label>
                            <select name="assigned_to_id" id="assigned_to_id"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Sélectionner un commercial</option>
                                @foreach(\App\Models\User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])->get() as $commercial)
                                    <option value="{{ $commercial->id }}"
                                            {{ $ticket->assigned_to_id == $commercial->id ? 'selected' : '' }}>
                                        {{ $commercial->name }}
                                        @php
                                            $workload = \App\Models\Ticket::where('assigned_to_id', $commercial->id)
                                                ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'URGENT'])
                                                ->count();
                                        @endphp
                                        ({{ $workload }} tickets)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                            Réassigner le ticket
                        </button>
                    </form>

                    <!-- Charge de travail du commercial assigné -->
                    @if($ticket->assignedTo)
                        @php
                            $workload = \App\Models\Ticket::where('assigned_to_id', $ticket->assigned_to_id)
                                ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'URGENT'])
                                ->count();
                        @endphp
                        <div class="mt-4 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                            <div class="text-sm font-medium text-gray-700 mb-2">Charge actuelle de {{ $ticket->assignedTo->name }}</div>
                            <div class="flex items-center justify-between">
                                <span class="text-xl font-bold text-gray-900">{{ $workload }} tickets actifs</span>
                                @if($workload > 15)
                                    <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full font-semibold">Surchargé</span>
                                @elseif($workload > 8)
                                    <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full font-semibold">Chargé</span>
                                @else
                                    <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full font-semibold">Disponible</span>
                                @endif
                            </div>
                            <div class="mt-2 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full transition-all duration-300"
                                     style="width: {{ min(100, ($workload / 20) * 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Informations détaillées du ticket -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Informations détaillées
                    </h3>

                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-600">Client</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                {{ $ticket->client->name ?? 'Client inconnu' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Email</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                <a href="mailto:{{ $ticket->client->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $ticket->client->email }}
                                </a>
                            </dd>
                        </div>

                        @if($ticket->client->phone)
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Téléphone</dt>
                                <dd class="text-sm text-gray-900 mt-1">
                                    <a href="tel:{{ $ticket->client->phone }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $ticket->client->phone }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Priorité</dt>
                            <dd class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $ticket->priority === 'URGENT' ? 'text-red-800 bg-red-100' :
                                       ($ticket->priority === 'HIGH' ? 'text-yellow-800 bg-yellow-100' : 'text-green-800 bg-green-100') }}">
                                    {{ $ticket->priority ?? 'NORMAL' }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Type</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $ticket->type ?? 'GÉNÉRAL' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Assigné à</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                {{ $ticket->assignedTo->name ?? 'Non assigné' }}
                                @if(!$ticket->assignedTo)
                                    <span class="text-red-600 ml-1">⚠️</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Créé le</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $ticket->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>

                        @if($ticket->first_response_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Première réponse</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $ticket->first_response_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        @endif

                        @if($ticket->resolved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Résolu le</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-600">Dernière mise à jour</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $ticket->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Statistiques client avancées -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profil du client
                    </h3>

                    @php
                        $clientStats = [
                            'tickets' => \App\Models\Ticket::where('client_id', $ticket->client_id)->count(),
                            'urgentTickets' => \App\Models\Ticket::where('client_id', $ticket->client_id)->where('priority', 'URGENT')->count(),
                            'resolvedTickets' => \App\Models\Ticket::where('client_id', $ticket->client_id)->where('status', 'RESOLVED')->count(),
                        ];
                    @endphp

                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Total tickets</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $clientStats['tickets'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Tickets urgents</dt>
                            <dd class="text-sm font-medium text-red-600">{{ $clientStats['urgentTickets'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Tickets résolus</dt>
                            <dd class="text-sm font-medium text-green-600">{{ $clientStats['resolvedTickets'] }}</dd>
                        </div>
                        @if($clientStats['tickets'] > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Taux de résolution</dt>
                                <dd class="text-sm font-medium text-gray-900">
                                    {{ round(($clientStats['resolvedTickets'] / $clientStats['tickets']) * 100, 1) }}%
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <!-- Évaluation du risque client -->
                    @php
                        $riskScore = 0;
                        if ($clientStats['urgentTickets'] > 2) $riskScore += 3;
                        if ($clientStats['tickets'] > 10) $riskScore += 2;
                        if ($clientStats['tickets'] > 0 && ($clientStats['resolvedTickets'] / $clientStats['tickets']) < 0.5) $riskScore += 1;

                        $riskLevel = $riskScore >= 4 ? 'high' : ($riskScore >= 2 ? 'medium' : 'low');
                    @endphp

                    <div class="mt-4 p-3 rounded-lg {{
                        $riskLevel === 'high' ? 'bg-red-50 border border-red-200' :
                        ($riskLevel === 'medium' ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200')
                    }}">
                        <div class="text-sm font-medium flex items-center {{
                            $riskLevel === 'high' ? 'text-red-800' :
                            ($riskLevel === 'medium' ? 'text-yellow-800' : 'text-green-800')
                        }}">
                            @if($riskLevel === 'high')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                ⚠️ Client à risque
                            @elseif($riskLevel === 'medium')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ⚡ Surveillance recommandée
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ✅ Profil normal
                            @endif
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            Basé sur l'historique des tickets
                        </div>
                    </div>
                </div>

                @if($ticket->complaint)
                    <!-- Réclamation liée -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Réclamation associée
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-600">ID:</span>
                                <span class="text-sm text-gray-900 ml-2">#{{ $ticket->complaint->id }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Type:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $ticket->complaint->type ?? 'Non spécifié' }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Statut:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $ticket->complaint->status ?? 'Non spécifié' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($ticket->package)
                    <!-- Colis lié -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                            </svg>
                            Colis associé
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Code:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $ticket->package->package_code }}</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Statut:</span>
                                <span class="text-sm text-gray-900 ml-2">{{ $ticket->package->status }}</span>
                            </div>
                            @if($ticket->package->assigned_deliverer_id)
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Livreur:</span>
                                    <span class="text-sm text-gray-900 ml-2">{{ $ticket->package->assignedDeliverer->name ?? 'Non défini' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 0.5rem;
}

/* Animation du hover sur les boutons */
.transform.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Effet glassmorphism */
.backdrop-blur-lg {
    backdrop-filter: blur(16px);
}

/* Animation des messages */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.divide-y > div {
    animation: slideIn 0.3s ease-out;
}
</style>
@endpush

@push('scripts')
<script>
function ticketDetails() {
    return {
        init() {
            this.scrollToLatestMessage();
        },

        scrollToLatestMessage() {
            // Auto-scroll to latest message on page load
            setTimeout(() => {
                const messages = document.querySelectorAll('.divide-y.divide-gray-100 > div');
                if (messages.length > 0) {
                    messages[messages.length - 1].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }, 100);
        }
    }
}

// Confirmation for critical actions
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[action*="force-close"], form[action*="escalate"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = form.action.includes('force-close') ? 'fermer définitivement' : 'escalader en urgent';
            const warning = action === 'fermer définitivement' ?
                'Cette action est irréversible et fermera le ticket définitivement. Le client ne pourra plus répondre.' :
                'Le ticket sera marqué comme urgent et nécessitera une attention immédiate de l\'équipe support.';

            const confirmMessage = `Voulez-vous vraiment ${action} ce ticket ?\n\n${warning}`;

            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });

    // Real-time updates check
    setInterval(function() {
        // Check for new messages or status updates
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                console.log('Vérification des mises à jour...');
                // Could implement real-time updates here
            }
        });
    }, 30000); // Check every 30 seconds
});
</script>
@endpush