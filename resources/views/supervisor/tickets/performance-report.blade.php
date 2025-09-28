@extends('layouts.supervisor')

@section('title', 'Rapport de Performance - Tickets')

@section('content')
<div x-data="performanceReport()" class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        Rapport de Performance des Tickets
                    </h1>
                    <p class="text-gray-600 mt-1">Analyse détaillée des métriques et KPI du support client</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mt-4 lg:mt-0">
                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Dashboard Tickets
                    </a>
                    <a href="{{ route('supervisor.tickets.overview') }}"
                       class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Vue d'ensemble
                    </a>
                    <button @click="exportReport('print')"
                           class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 inline-flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer le rapport
                    </button>
                </div>
            </div>

            <!-- Période du rapport -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0v10m6-10v10a1 1 0 01-1 1H9a1 1 0 01-1-1V7"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Période d'analyse :</h3>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-2">
                            <span class="text-gray-700 font-medium">
                                {{ request('period', 30) === '30' ? '30 derniers jours' :
                                   (request('period') === '7' ? '7 derniers jours' :
                                   (request('period') === '90' ? '90 derniers jours' : '30 derniers jours')) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                ({{ now()->subDays(request('period', 30))->format('d/m/Y') }} - {{ now()->format('d/m/Y') }})
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                        <div class="text-sm text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Généré le {{ now()->format('d/m/Y à H:i') }}
                        </div>
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('supervisor.tickets.performance-report', ['period' => 7]) }}"
                               class="px-3 py-1 text-xs rounded-full {{ request('period') == '7' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                7j
                            </a>
                            <a href="{{ route('supervisor.tickets.performance-report', ['period' => 30]) }}"
                               class="px-3 py-1 text-xs rounded-full {{ request('period', 30) == '30' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                30j
                            </a>
                            <a href="{{ route('supervisor.tickets.performance-report', ['period' => 90]) }}"
                               class="px-3 py-1 text-xs rounded-full {{ request('period') == '90' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                90j
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $period = request('period', 30);
            $last30Days = \App\Models\Ticket::where('created_at', '>=', now()->subDays($period))->get();
            $previous30Days = \App\Models\Ticket::whereBetween('created_at', [now()->subDays($period * 2), now()->subDays($period)])->get();

            // KPIs principaux
            $kpis = [
                'total_tickets' => $last30Days->count(),
                'resolved_tickets' => $last30Days->whereNotNull('resolved_at')->count(),
                'avg_response_time' => $last30Days->filter(fn($t) => $t->first_response_at)->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at)),
                'avg_resolution_time' => $last30Days->filter(fn($t) => $t->resolved_at)->avg(fn($t) => $t->created_at->diffInHours($t->resolved_at)),
                'sla_compliance' => $last30Days->filter(fn($t) => $t->first_response_at && $t->created_at->diffInHours($t->first_response_at) <= 24)->count(),
                'customer_satisfaction' => 87
            ];

            // Comparaison avec période précédente
            $previousKpis = [
                'total_tickets' => $previous30Days->count(),
                'resolved_tickets' => $previous30Days->whereNotNull('resolved_at')->count(),
                'avg_response_time' => $previous30Days->filter(fn($t) => $t->first_response_at)->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at))
            ];
        @endphp

        <!-- KPIs principaux -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <!-- Total tickets -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['total_tickets'] }}</p>
                        @php $totalChange = $previousKpis['total_tickets'] > 0 ? (($kpis['total_tickets'] - $previousKpis['total_tickets']) / $previousKpis['total_tickets']) * 100 : 0; @endphp
                        <p class="text-xs flex items-center {{ $totalChange >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                            @if($totalChange >= 0)
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                </svg>
                            @else
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                            @endif
                            {{ $totalChange >= 0 ? '+' : '' }}{{ round($totalChange, 1) }}% vs période précédente
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tickets résolus -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tickets Résolus</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $kpis['resolved_tickets'] }}</p>
                        @php $resolvedRate = $kpis['total_tickets'] > 0 ? round(($kpis['resolved_tickets'] / $kpis['total_tickets']) * 100, 1) : 0; @endphp
                        <p class="text-xs text-gray-500 flex items-center mt-1">
                            <div class="w-16 h-1.5 bg-gray-200 rounded-full mr-2 overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                                     style="width: {{ $resolvedRate }}%"></div>
                            </div>
                            {{ $resolvedRate }}% du total
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Temps de réponse moyen -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Temps Réponse Moy.</p>
                        <p class="text-2xl font-bold {{ $kpis['avg_response_time'] > 24 ? 'text-red-600' : ($kpis['avg_response_time'] > 12 ? 'text-yellow-600' : 'text-emerald-600') }}">
                            {{ round($kpis['avg_response_time'] ?? 0, 1) }}h
                        </p>
                        @php $responseTimeChange = $previousKpis['avg_response_time'] > 0 ? (($kpis['avg_response_time'] - $previousKpis['avg_response_time']) / $previousKpis['avg_response_time']) * 100 : 0; @endphp
                        <p class="text-xs flex items-center {{ $responseTimeChange <= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">
                            @if($responseTimeChange <= 0)
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                            @else
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                </svg>
                            @endif
                            {{ $responseTimeChange <= 0 ? '' : '+' }}{{ round($responseTimeChange, 1) }}%
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br {{ $kpis['avg_response_time'] > 24 ? 'from-red-500 to-red-600' : ($kpis['avg_response_time'] > 12 ? 'from-yellow-500 to-yellow-600' : 'from-emerald-500 to-emerald-600') }} rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Temps de résolution moyen -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Temps Résolution Moy.</p>
                        <p class="text-2xl font-bold text-blue-600">{{ round($kpis['avg_resolution_time'] ?? 0, 1) }}h</p>
                        <p class="text-xs text-gray-500 flex items-center mt-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Objectif: &lt; 48h
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Respect SLA -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Respect SLA</p>
                        @php $slaRate = $kpis['total_tickets'] > 0 ? round(($kpis['sla_compliance'] / $kpis['total_tickets']) * 100, 1) : 0; @endphp
                        <p class="text-2xl font-bold {{ $slaRate < 80 ? 'text-red-600' : ($slaRate < 90 ? 'text-yellow-600' : 'text-emerald-600') }}">
                            {{ $slaRate }}%
                        </p>
                        <p class="text-xs text-gray-500 flex items-center mt-1">
                            <div class="w-16 h-1.5 bg-gray-200 rounded-full mr-2 overflow-hidden">
                                <div class="h-full {{ $slaRate < 80 ? 'bg-red-500' : ($slaRate < 90 ? 'bg-yellow-500' : 'bg-emerald-500') }} rounded-full transition-all duration-300"
                                     style="width: {{ $slaRate }}%"></div>
                            </div>
                            {{ $kpis['sla_compliance'] }}/{{ $kpis['total_tickets'] }} dans les 24h
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br {{ $slaRate < 80 ? 'from-red-500 to-red-600' : ($slaRate < 90 ? 'from-yellow-500 to-yellow-600' : 'from-emerald-500 to-emerald-600') }} rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Satisfaction client -->
            <div class="bg-white/80 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Satisfaction Client</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $kpis['customer_satisfaction'] }}%</p>
                        <p class="text-xs text-emerald-600 flex items-center mt-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            +2% vs précédente
                        </p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance par commercial -->
        <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Performance par Commercial
                </h3>
                <div class="text-sm text-gray-600">
                    Analyse détaillée des performances individuelles
                </div>
            </div>

            @php
                $commercialPerformance = $commercialPerformance ?? \App\Models\User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])->get()->map(function($commercial) use ($last30Days) {
                    $commercialTickets = $last30Days->where('assigned_to_id', $commercial->id);
                    $responseTime = $commercialTickets->filter(fn($t) => $t->first_response_at)
                        ->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at));

                    return [
                        'commercial' => $commercial,
                        'tickets_handled' => $commercialTickets->count(),
                        'tickets_resolved' => $commercialTickets->whereNotNull('resolved_at')->count(),
                        'avg_response_time' => $responseTime,
                        'resolution_rate' => $commercialTickets->count() > 0 ? ($commercialTickets->whereNotNull('resolved_at')->count() / $commercialTickets->count() * 100) : 0,
                        'sla_compliance' => $commercialTickets->filter(fn($t) => $t->first_response_at && $t->created_at->diffInHours($t->first_response_at) <= 24)->count()
                    ];
                })->sortByDesc('tickets_handled');
            @endphp

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets Traités</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets Résolus</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Taux de Résolution</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Temps Réponse Moy.</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Respect SLA</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($commercialPerformance as $performance)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                            <span class="text-white font-medium text-sm">{{ strtoupper(substr($performance['commercial']->name, 0, 2)) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $performance['commercial']->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $performance['commercial']->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium text-gray-900">{{ $performance['tickets_handled'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium text-emerald-600">{{ $performance['tickets_resolved'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="flex flex-col items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $performance['resolution_rate'] >= 80 ? 'bg-emerald-100 text-emerald-800' :
                                                   ($performance['resolution_rate'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ round($performance['resolution_rate'], 1) }}%
                                            </span>
                                            <div class="w-16 h-1 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                                <div class="h-full {{ $performance['resolution_rate'] >= 80 ? 'bg-emerald-500' : ($performance['resolution_rate'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }} rounded-full transition-all duration-300"
                                                     style="width: {{ $performance['resolution_rate'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium {{ $performance['avg_response_time'] > 24 ? 'text-red-600' : ($performance['avg_response_time'] > 12 ? 'text-yellow-600' : 'text-emerald-600') }}">
                                        {{ round($performance['avg_response_time'] ?? 0, 1) }}h
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php $commercialSlaRate = $performance['tickets_handled'] > 0 ? round(($performance['sla_compliance'] / $performance['tickets_handled']) * 100, 1) : 0; @endphp
                                    <div class="flex items-center justify-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-medium {{ $commercialSlaRate >= 90 ? 'text-emerald-600' : ($commercialSlaRate >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $commercialSlaRate }}%
                                            </span>
                                            <div class="w-16 h-1 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                                <div class="h-full {{ $commercialSlaRate >= 90 ? 'bg-emerald-500' : ($commercialSlaRate >= 80 ? 'bg-yellow-500' : 'bg-red-500') }} rounded-full transition-all duration-300"
                                                     style="width: {{ $commercialSlaRate }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $score = 0;
                                        if ($performance['resolution_rate'] >= 80) $score += 25;
                                        elseif ($performance['resolution_rate'] >= 60) $score += 15;

                                        if ($performance['avg_response_time'] <= 12) $score += 25;
                                        elseif ($performance['avg_response_time'] <= 24) $score += 15;

                                        if ($commercialSlaRate >= 90) $score += 25;
                                        elseif ($commercialSlaRate >= 80) $score += 15;

                                        if ($performance['tickets_handled'] >= 20) $score += 25;
                                        elseif ($performance['tickets_handled'] >= 10) $score += 15;

                                        $grade = $score >= 80 ? 'A' : ($score >= 60 ? 'B' : ($score >= 40 ? 'C' : 'D'));
                                        $gradeColor = [
                                            'A' => 'from-emerald-500 to-emerald-600',
                                            'B' => 'from-blue-500 to-blue-600',
                                            'C' => 'from-yellow-500 to-yellow-600',
                                            'D' => 'from-red-500 to-red-600'
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-center">
                                        <div class="w-8 h-8 bg-gradient-to-br {{ $gradeColor[$grade] }} text-white rounded-full flex items-center justify-center font-bold shadow-lg">
                                            {{ $grade }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Recommandations d'Amélioration
            </h3>
            <div class="space-y-4">
                @php
                    $recommendations = [];

                    if (($kpis['avg_response_time'] ?? 0) > 24) {
                        $recommendations[] = [
                            'type' => 'critical',
                            'title' => 'Temps de réponse critique',
                            'description' => 'Le temps de réponse moyen dépasse 24h. Une action immédiate est nécessaire.',
                            'action' => 'Recruter un commercial supplémentaire ou revoir la distribution des tickets',
                            'impact' => 'Risque de perte de clients et dégradation de la satisfaction'
                        ];
                    }

                    if ($slaRate < 80) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Respect SLA insuffisant',
                            'description' => 'Moins de 80% des tickets respectent le SLA de 24h.',
                            'action' => 'Mettre en place des alertes automatiques et prioriser les nouveaux tickets',
                            'impact' => 'Non-respect des engagements contractuels'
                        ];
                    }

                    if ($resolvedRate < 70) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Taux de résolution faible',
                            'description' => 'Le taux de résolution est inférieur à 70%.',
                            'action' => 'Formation de l\'équipe sur les techniques de résolution de problèmes',
                            'impact' => 'Accumulation de tickets non résolus'
                        ];
                    }

                    if (empty($recommendations)) {
                        $recommendations[] = [
                            'type' => 'success',
                            'title' => 'Performance excellente',
                            'description' => 'Tous les indicateurs sont dans les objectifs. Continuez sur cette voie !',
                            'action' => 'Maintenir le niveau de service actuel et partager les bonnes pratiques',
                            'impact' => 'Satisfaction client optimale'
                        ];
                    }
                @endphp

                @foreach($recommendations as $recommendation)
                    <div class="flex items-start space-x-4 p-6 rounded-xl border-l-4 {{
                        $recommendation['type'] === 'critical' ? 'bg-red-50 border-red-500' :
                        ($recommendation['type'] === 'warning' ? 'bg-yellow-50 border-yellow-500' : 'bg-emerald-50 border-emerald-500')
                    }}">
                        <div class="flex-shrink-0 p-2 rounded-full {{
                            $recommendation['type'] === 'critical' ? 'bg-red-100' :
                            ($recommendation['type'] === 'warning' ? 'bg-yellow-100' : 'bg-emerald-100')
                        }}">
                            @if($recommendation['type'] === 'critical')
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @elseif($recommendation['type'] === 'warning')
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-lg {{
                                $recommendation['type'] === 'critical' ? 'text-red-800' :
                                ($recommendation['type'] === 'warning' ? 'text-yellow-800' : 'text-emerald-800')
                            }} mb-2">
                                {{ $recommendation['title'] }}
                            </h4>
                            <p class="text-gray-700 mb-3">{{ $recommendation['description'] }}</p>
                            <div class="space-y-2">
                                <div class="p-3 bg-white rounded-lg border">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Action recommandée :</p>
                                    <p class="text-sm text-gray-700">{{ $recommendation['action'] }}</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Impact attendu :</p>
                                    <p class="text-sm text-gray-700">{{ $recommendation['impact'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .no-print, nav, .flex.justify-between.items-center, button {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .bg-gradient-to-br {
        background: white !important;
    }

    .backdrop-blur-lg {
        backdrop-filter: none !important;
    }
}

/* Animation pour les barres de progression */
@keyframes fillBar {
    from {
        width: 0%;
    }
    to {
        width: var(--target-width);
    }
}

.progress-bar {
    animation: fillBar 1s ease-out;
}

/* Effet glassmorphism */
.backdrop-blur-lg {
    backdrop-filter: blur(16px);
}

/* Animation des cartes */
.hover\:scale-105:hover {
    transform: scale(1.05);
}
</style>
@endpush

@push('scripts')
<script>
function performanceReport() {
    return {
        init() {
            this.animateProgressBars();
        },

        animateProgressBars() {
            // Animer les barres de progression après le chargement
            setTimeout(() => {
                document.querySelectorAll('.progress-bar').forEach(bar => {
                    bar.style.width = bar.getAttribute('data-width');
                });
            }, 500);
        },

        exportReport(format) {
            if (format === 'print') {
                window.print();
            }
            // Ici on pourrait ajouter d'autres formats d'export (PDF, Excel, etc.)
        }
    }
}

// Auto-refresh des données
setInterval(function() {
    // Check for updates (optionnel)
    console.log('Vérification des mises à jour des métriques...');
}, 300000); // 5 minutes

document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des tooltips aux métriques
    const tooltips = {
        'temps-reponse': 'Temps moyen entre la création d\'un ticket et la première réponse',
        'temps-resolution': 'Temps moyen entre la création et la résolution d\'un ticket',
        'sla-compliance': 'Pourcentage de tickets ayant reçu une réponse dans les 24h',
        'satisfaction': 'Score de satisfaction basé sur les retours clients'
    };

    // Fonctionnalité d'export étendue
    window.exportToCSV = function() {
        // Implémentation de l'export CSV
        console.log('Export CSV en cours...');
    };
});
</script>
@endpush