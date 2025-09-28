@extends('layouts.supervisor')

@section('title', 'Dashboard Tickets - Superviseur')

@section('content')
<div x-data="ticketDashboard()" class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header avec actions -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Dashboard Tickets Superviseur
                    </h1>
                    <p class="text-gray-600 mt-1">Vue d'ensemble et contrôle avancé des tickets urgents</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mt-4 lg:mt-0">
                    <a href="{{ route('supervisor.tickets.overview') }}"
                       class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Vue d'ensemble complète
                    </a>
                    <a href="{{ route('supervisor.tickets.performance-report') }}"
                       class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Rapport de performance
                    </a>
                </div>
            </div>

            <!-- Statistiques détaillées avec animations -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4 mb-8">
                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Ouverts</p>
                            <p class="text-2xl font-bold text-green-700">{{ $stats['open_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">En cours</p>
                            <p class="text-2xl font-bold text-orange-700">{{ $stats['in_progress'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Urgents</p>
                            <p class="text-2xl font-bold text-red-700">{{ $stats['urgent_tickets'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Résolus aujourd'hui</p>
                            <p class="text-2xl font-bold text-emerald-700">{{ $stats['resolved_today'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 21l1.364-1.364M5.636 5.636L3 3l1.364 1.364"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Non assignés</p>
                            <p class="text-2xl font-bold text-gray-700">{{ $stats['unassigned'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Besoin d'attention</p>
                            <p class="text-2xl font-bold text-yellow-700">{{ $stats['needs_attention'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center">
                        <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Taux de réponse</p>
                            <p class="text-2xl font-bold text-indigo-700">{{ number_format($stats['response_rate'] ?? 0, 1) }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance commerciaux -->
            <div class="bg-white/80 backdrop-blur-lg p-8 rounded-2xl shadow-lg border border-white/20 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Charge de travail des commerciaux
                    </h3>
                    <span class="text-sm text-gray-600">Tickets actifs par commercial</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($commercialPerformance as $commercial)
                        <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold">{{ strtoupper(substr($commercial->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $commercial->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $commercial->active_tickets_count }} tickets actifs</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $commercial->resolved_this_month_count }} résolus ce mois</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    @if($commercial->active_tickets_count > 15)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100 mb-1">
                                            Surchargé
                                        </span>
                                    @elseif($commercial->active_tickets_count > 8)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-yellow-800 bg-yellow-100 mb-1">
                                            Chargé
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-green-800 bg-green-100 mb-1">
                                            Disponible
                                        </span>
                                    @endif
                                    <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full"
                                             style="width: {{ min(100, ($commercial->active_tickets_count / 20) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tickets urgents -->
        @if($urgentTickets->count() > 0)
        <div class="mb-8">
            <div class="bg-gradient-to-r from-red-500 to-orange-500 p-1 rounded-2xl shadow-lg">
                <div class="bg-white rounded-xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-red-600 flex items-center">
                            <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            🚨 Tickets Urgents - Action Immédiate Requise
                        </h2>
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-semibold">
                            {{ $urgentTickets->count() }} ticket(s)
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($urgentTickets as $ticket)
                            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-3">
                                            <h3 class="text-xl font-bold text-red-800">
                                                <a href="{{ route('supervisor.tickets.show', $ticket) }}" class="hover:text-red-600 transition-colors">
                                                    #{{ $ticket->ticket_number }}
                                                </a>
                                            </h3>
                                            <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold animate-pulse">
                                                🚨 URGENT
                                            </span>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm">
                                                {{ $ticket->type_display ?? 'GÉNÉRAL' }}
                                            </span>
                                        </div>

                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h4>
                                        <p class="text-gray-700 mb-4 line-clamp-2">{{ Str::limit($ticket->description, 200) }}</p>

                                        <div class="flex items-center space-x-6 text-sm">
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
                                                    Assigné à {{ $ticket->assignedTo->name }}
                                                </div>
                                            @else
                                                <div class="flex items-center text-red-600">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    ⚠️ Non assigné
                                                </div>
                                            @endif
                                            <div class="flex items-center text-gray-600">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $ticket->created_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        @if($ticket->messages && $ticket->messages->count() > 0)
                                            <div class="mt-4 p-3 bg-white rounded-lg border">
                                                <p class="text-sm text-gray-600 mb-1">Dernier message :</p>
                                                <p class="text-sm text-gray-900">{{ Str::limit($ticket->messages->first()->message, 100) }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-col space-y-2 ml-6">
                                        <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors text-center">
                                            Traiter maintenant
                                        </a>

                                        @if(!$ticket->assignedTo)
                                            <button @click="assignTicket({{ $ticket->id }})"
                                                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                                Assigner rapidement
                                            </button>
                                        @endif

                                        <form method="POST" action="{{ route('supervisor.tickets.force-close', $ticket) }}" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Fermer définitivement ce ticket urgent ?')"
                                                    class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                                                Fermer force
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tickets nécessitant attention -->
        @if($needsAttentionTickets->count() > 0)
        <div class="mb-8">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-400 p-1 rounded-2xl shadow-lg">
                <div class="bg-white rounded-xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-yellow-600 flex items-center">
                            <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ⚠️ Tickets nécessitant attention
                        </h2>
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-semibold">
                            {{ $needsAttentionTickets->count() }} ticket(s)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($needsAttentionTickets as $ticket)
                            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300">
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-yellow-800">
                                        <a href="{{ route('supervisor.tickets.show', $ticket) }}" class="hover:text-yellow-600 transition-colors">
                                            #{{ $ticket->ticket_number }}
                                        </a>
                                    </h3>
                                    <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <h4 class="font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h4>
                                <p class="text-gray-700 text-sm mb-4 line-clamp-2">{{ Str::limit($ticket->description, 120) }}</p>

                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-blue-600">👤 {{ $ticket->client->name ?? 'Client inconnu' }}</span>
                                        @if($ticket->assignedTo)
                                            <span class="text-green-600">👨‍💼 {{ $ticket->assignedTo->name }}</span>
                                        @else
                                            <span class="text-red-600">⚠️ Non assigné</span>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                           class="text-yellow-600 hover:text-yellow-800 font-medium">
                                            Voir →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Vue d'ensemble</h3>
                    <p class="text-gray-600 text-sm mb-4">Tous les tickets avec filtres avancés</p>
                    <a href="{{ route('supervisor.tickets.overview') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Accéder
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rapports détaillés</h3>
                    <p class="text-gray-600 text-sm mb-4">Performance et statistiques avancées</p>
                    <a href="{{ route('supervisor.tickets.performance-report') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Voir rapports
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Gestion équipes</h3>
                    <p class="text-gray-600 text-sm mb-4">Réassignation et charge de travail</p>
                    <button @click="showBulkAssignModal = true"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Réassigner en lot
                    </button>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Temps réel</h3>
                    <p class="text-gray-600 text-sm mb-4">Actualisation automatique des données</p>
                    <button @click="refreshData()"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                        Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'assignation rapide -->
<div x-show="showQuickAssignModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Assignation rapide</h3>
        <form method="POST" action="{{ route('supervisor.tickets.bulk-reassign') }}">
            @csrf
            <input type="hidden" name="ticket_ids[]" :value="selectedTicketId">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Commercial</label>
                <select name="assigned_to_id" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    @foreach($commercialPerformance as $commercial)
                        <option value="{{ $commercial->id }}">
                            {{ $commercial->name }} ({{ $commercial->active_tickets_count }} tickets)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    Assigner
                </button>
                <button type="button" @click="showQuickAssignModal = false" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium">
                    Annuler
                </button>
            </div>
        </form>
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

[x-cloak] { display: none !important; }

/* Animations personnalisées */
@keyframes slideIn {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slideIn 0.5s ease-out;
}

/* Gradients personnalisés */
.glass-effect {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
@endpush

@push('scripts')
<script>
function ticketDashboard() {
    return {
        showQuickAssignModal: false,
        showBulkAssignModal: false,
        selectedTicketId: null,
        autoRefresh: true,
        refreshInterval: null,

        init() {
            if (this.autoRefresh) {
                this.startAutoRefresh();
            }
        },

        assignTicket(ticketId) {
            this.selectedTicketId = ticketId;
            this.showQuickAssignModal = true;
        },

        refreshData() {
            location.reload();
        },

        startAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                // Actualisation discrète des statistiques via AJAX
                fetch('{{ route("supervisor.tickets.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(() => {
                    // Optionnel : mise à jour des données sans recharger
                    console.log('Données actualisées');
                });
            }, 30000); // 30 secondes
        },

        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }
}

// Notifications temps réel (optionnel avec WebSockets)
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter ici la logique de notifications push si nécessaire
});
</script>
@endpush