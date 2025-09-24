@extends('layouts.supervisor')

@section('title', 'Rapport de Performance - Tickets')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rapport de Performance des Tickets</h1>
                    <p class="text-gray-600 mt-1">Analyse détaillée des métriques et KPI du support client</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('supervisor.tickets.overview') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        ← Vue d'ensemble
                    </a>
                    <button onclick="window.print()"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimer le rapport
                    </button>
                </div>
            </div>

            <!-- Période du rapport -->
            <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h3 class="font-semibold text-gray-900">Période d'analyse:</h3>
                        <span class="text-gray-600">{{ now()->subDays(30)->format('d/m/Y') }} - {{ now()->format('d/m/Y') }}</span>
                        <span class="text-sm text-gray-500">(30 derniers jours)</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Généré le {{ now()->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>
        </div>

        @php
            // Calculs pour le rapport de performance
            $last30Days = \App\Models\Ticket::where('created_at', '>=', now()->subDays(30))->get();
            $previous30Days = \App\Models\Ticket::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->get();

            // KPIs principaux
            $kpis = [
                'total_tickets' => $last30Days->count(),
                'resolved_tickets' => $last30Days->whereNotNull('resolved_at')->count(),
                'avg_response_time' => $last30Days->filter(fn($t) => $t->first_response_at)->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at)),
                'avg_resolution_time' => $last30Days->filter(fn($t) => $t->resolved_at)->avg(fn($t) => $t->created_at->diffInHours($t->resolved_at)),
                'sla_compliance' => $last30Days->filter(fn($t) => $t->first_response_at && $t->created_at->diffInHours($t->first_response_at) <= 24)->count(),
                'customer_satisfaction' => 87 // Placeholder - à remplacer par vraie métrique
            ];

            // Comparaison avec période précédente
            $previousKpis = [
                'total_tickets' => $previous30Days->count(),
                'resolved_tickets' => $previous30Days->whereNotNull('resolved_at')->count(),
                'avg_response_time' => $previous30Days->filter(fn($t) => $t->first_response_at)->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at))
            ];

            // Performance par commercial
            $commercialPerformance = \App\Models\User::where('role', 'COMMERCIAL')->get()->map(function($commercial) use ($last30Days) {
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

            // Analyse par type et catégorie
            $typeAnalysis = $last30Days->groupBy('type')->map(function($tickets, $type) {
                return [
                    'count' => $tickets->count(),
                    'avg_response_time' => $tickets->filter(fn($t) => $t->first_response_at)->avg(fn($t) => $t->created_at->diffInHours($t->first_response_at)),
                    'resolution_rate' => $tickets->whereNotNull('resolved_at')->count() / $tickets->count() * 100
                ];
            });

            // Tendances quotidiennes
            $dailyTrends = collect(range(0, 29))->map(function($days) {
                $date = now()->subDays($days);
                return [
                    'date' => $date->format('d/m'),
                    'created' => \App\Models\Ticket::whereDate('created_at', $date)->count(),
                    'resolved' => \App\Models\Ticket::whereDate('resolved_at', $date)->count(),
                    'first_responses' => \App\Models\Ticket::whereDate('first_response_at', $date)->count()
                ];
            })->reverse();
        @endphp

        <!-- KPIs principaux -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <!-- Total tickets -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $kpis['total_tickets'] }}</p>
                        @php $totalChange = $previousKpis['total_tickets'] > 0 ? (($kpis['total_tickets'] - $previousKpis['total_tickets']) / $previousKpis['total_tickets']) * 100 : 0; @endphp
                        <p class="text-xs {{ $totalChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $totalChange >= 0 ? '+' : '' }}{{ round($totalChange, 1) }}% vs période précédente
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tickets résolus -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tickets Résolus</p>
                        <p class="text-2xl font-bold text-green-600">{{ $kpis['resolved_tickets'] }}</p>
                        @php $resolvedRate = $kpis['total_tickets'] > 0 ? round(($kpis['resolved_tickets'] / $kpis['total_tickets']) * 100, 1) : 0; @endphp
                        <p class="text-xs text-gray-500">{{ $resolvedRate }}% du total</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Temps de réponse moyen -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Temps Réponse Moy.</p>
                        <p class="text-2xl font-bold {{ $kpis['avg_response_time'] > 24 ? 'text-red-600' : ($kpis['avg_response_time'] > 12 ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ round($kpis['avg_response_time'] ?? 0, 1) }}h
                        </p>
                        @php $responseTimeChange = $previousKpis['avg_response_time'] > 0 ? (($kpis['avg_response_time'] - $previousKpis['avg_response_time']) / $previousKpis['avg_response_time']) * 100 : 0; @endphp
                        <p class="text-xs {{ $responseTimeChange <= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $responseTimeChange <= 0 ? '' : '+' }}{{ round($responseTimeChange, 1) }}% vs précédente
                        </p>
                    </div>
                    <div class="p-3 {{ $kpis['avg_response_time'] > 24 ? 'bg-red-100' : ($kpis['avg_response_time'] > 12 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                        <svg class="w-6 h-6 {{ $kpis['avg_response_time'] > 24 ? 'text-red-600' : ($kpis['avg_response_time'] > 12 ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Temps de résolution moyen -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Temps Résolution Moy.</p>
                        <p class="text-2xl font-bold text-blue-600">{{ round($kpis['avg_resolution_time'] ?? 0, 1) }}h</p>
                        <p class="text-xs text-gray-500">Objectif: &lt; 48h</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Respect SLA -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Respect SLA</p>
                        @php $slaRate = $kpis['total_tickets'] > 0 ? round(($kpis['sla_compliance'] / $kpis['total_tickets']) * 100, 1) : 0; @endphp
                        <p class="text-2xl font-bold {{ $slaRate < 80 ? 'text-red-600' : ($slaRate < 90 ? 'text-yellow-600' : 'text-green-600') }}">{{ $slaRate }}%</p>
                        <p class="text-xs text-gray-500">{{ $kpis['sla_compliance'] }}/{{ $kpis['total_tickets'] }} dans les 24h</p>
                    </div>
                    <div class="p-3 {{ $slaRate < 80 ? 'bg-red-100' : ($slaRate < 90 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                        <svg class="w-6 h-6 {{ $slaRate < 80 ? 'text-red-600' : ($slaRate < 90 ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Satisfaction client -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Satisfaction Client</p>
                        <p class="text-2xl font-bold text-green-600">{{ $kpis['customer_satisfaction'] }}%</p>
                        <p class="text-xs text-green-600">+2% vs précédente</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance par commercial -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">👥 Performance par Commercial</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets Traités</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets Résolus</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Taux de Résolution</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Temps Réponse Moy.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Respect SLA</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($commercialPerformance as $performance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
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
                                    <div class="text-sm font-medium text-green-600">{{ $performance['tickets_resolved'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $performance['resolution_rate'] >= 80 ? 'bg-green-100 text-green-800' :
                                           ($performance['resolution_rate'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ round($performance['resolution_rate'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-medium {{ $performance['avg_response_time'] > 24 ? 'text-red-600' : ($performance['avg_response_time'] > 12 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ round($performance['avg_response_time'] ?? 0, 1) }}h
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php $commercialSlaRate = $performance['tickets_handled'] > 0 ? round(($performance['sla_compliance'] / $performance['tickets_handled']) * 100, 1) : 0; @endphp
                                    <div class="text-sm font-medium {{ $commercialSlaRate >= 90 ? 'text-green-600' : ($commercialSlaRate >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $commercialSlaRate }}%
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
                                        $gradeColor = $score >= 80 ? 'green' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $gradeColor }}-100 text-{{ $gradeColor }}-800">
                                        {{ $grade }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Analyse par type -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">📊 Analyse par Type de Ticket</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $typeLabels = [
                        'COMPLAINT' => ['label' => 'Réclamations', 'icon' => '📋', 'color' => 'red'],
                        'QUESTION' => ['label' => 'Questions', 'icon' => '❓', 'color' => 'blue'],
                        'SUPPORT' => ['label' => 'Support technique', 'icon' => '🛠️', 'color' => 'purple'],
                        'OTHER' => ['label' => 'Autres', 'icon' => '📝', 'color' => 'gray']
                    ];
                @endphp

                @foreach($typeAnalysis as $type => $analysis)
                    @php $config = $typeLabels[$type] ?? ['label' => $type, 'icon' => '📝', 'color' => 'gray']; @endphp
                    <div class="p-4 border-l-4 border-{{ $config['color'] }}-500 bg-{{ $config['color'] }}-50">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="text-xl">{{ $config['icon'] }}</span>
                            <h4 class="font-semibold text-gray-900">{{ $config['label'] }}</h4>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Volume:</span>
                                <span class="font-medium">{{ $analysis['count'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Temps réponse moy.:</span>
                                <span class="font-medium">{{ round($analysis['avg_response_time'] ?? 0, 1) }}h</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taux résolution:</span>
                                <span class="font-medium">{{ round($analysis['resolution_rate'], 1) }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tendances quotidiennes -->
        <div class="bg-white p-6 rounded-lg shadow-sm border mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">📈 Tendances Quotidiennes (30 derniers jours)</h3>
            <div class="space-y-4">
                <!-- Graphique simple en ASCII/HTML -->
                <div class="grid grid-cols-1 gap-4">
                    <div class="overflow-x-auto">
                        <div class="flex items-end justify-between h-40 min-w-full space-x-1" style="min-width: 900px;">
                            @foreach($dailyTrends as $day)
                                @php
                                    $maxValue = $dailyTrends->max('created');
                                    $createdHeight = $maxValue > 0 ? ($day['created'] / $maxValue) * 100 : 0;
                                    $resolvedHeight = $maxValue > 0 ? ($day['resolved'] / $maxValue) * 100 : 0;
                                @endphp
                                <div class="flex flex-col items-center space-y-1 min-w-0">
                                    <div class="flex items-end space-x-1 h-32">
                                        <div class="bg-blue-500 rounded-t" style="height: {{ $createdHeight }}%; width: 8px;"
                                             title="Créés: {{ $day['created'] }}"></div>
                                        <div class="bg-green-500 rounded-t" style="height: {{ $resolvedHeight }}%; width: 8px;"
                                             title="Résolus: {{ $day['resolved'] }}"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 transform -rotate-45 origin-left">{{ $day['date'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-center space-x-6 text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                            <span>Tickets créés</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            <span>Tickets résolus</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">💡 Recommandations d'Amélioration</h3>
            <div class="space-y-4">
                @php
                    $recommendations = [];

                    if ($kpis['avg_response_time'] > 24) {
                        $recommendations[] = [
                            'type' => 'critical',
                            'title' => 'Temps de réponse critique',
                            'description' => 'Le temps de réponse moyen dépasse 24h. Considérez augmenter l\'équipe support ou améliorer les processus.',
                            'action' => 'Recruter un commercial supplémentaire ou revoir la distribution des tickets'
                        ];
                    }

                    if ($slaRate < 80) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Respect SLA insuffisant',
                            'description' => 'Moins de 80% des tickets respectent le SLA de 24h.',
                            'action' => 'Mettre en place des alertes automatiques et prioriser les nouveaux tickets'
                        ];
                    }

                    if ($resolvedRate < 70) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'title' => 'Taux de résolution faible',
                            'description' => 'Le taux de résolution est inférieur à 70%.',
                            'action' => 'Formation de l\'équipe sur les techniques de résolution de problèmes'
                        ];
                    }

                    if (empty($recommendations)) {
                        $recommendations[] = [
                            'type' => 'success',
                            'title' => 'Performance excellente',
                            'description' => 'Tous les indicateurs sont dans les objectifs. Continuez sur cette voie !',
                            'action' => 'Maintenir le niveau de service actuel'
                        ];
                    }
                @endphp

                @foreach($recommendations as $recommendation)
                    <div class="flex items-start space-x-4 p-4 rounded-lg {{
                        $recommendation['type'] === 'critical' ? 'bg-red-50 border border-red-200' :
                        ($recommendation['type'] === 'warning' ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200')
                    }}">
                        <div class="{{
                            $recommendation['type'] === 'critical' ? 'text-red-600' :
                            ($recommendation['type'] === 'warning' ? 'text-yellow-600' : 'text-green-600')
                        }}">
                            @if($recommendation['type'] === 'critical')
                                🚨
                            @elseif($recommendation['type'] === 'warning')
                                ⚠️
                            @else
                                ✅
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold {{
                                $recommendation['type'] === 'critical' ? 'text-red-800' :
                                ($recommendation['type'] === 'warning' ? 'text-yellow-800' : 'text-green-800')
                            }}">
                                {{ $recommendation['title'] }}
                            </h4>
                            <p class="text-gray-700 mt-1">{{ $recommendation['description'] }}</p>
                            <p class="text-sm font-medium {{
                                $recommendation['type'] === 'critical' ? 'text-red-700' :
                                ($recommendation['type'] === 'warning' ? 'text-yellow-700' : 'text-green-700')
                            }} mt-2">
                                <strong>Action recommandée:</strong> {{ $recommendation['action'] }}
                            </p>
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
    .no-print, nav, .flex.justify-between.items-center {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .bg-gray-50 {
        background: white !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Fonction pour exporter le rapport
function exportReport(format) {
    if (format === 'print') {
        window.print();
    }
    // Ici on pourrait ajouter d'autres formats d'export (PDF, Excel, etc.)
}
</script>
@endpush