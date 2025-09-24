@extends('layouts.supervisor')

@section('title', 'Vue d\'ensemble des Tickets')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Vue d'ensemble des Tickets</h1>
                    <p class="text-gray-600 mt-1">Tableau de bord superviseur avec métriques et alertes</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('supervisor.tickets.index') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        ← Retour à la liste
                    </a>
                    <a href="{{ route('supervisor.tickets.performance-report') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        📊 Rapport détaillé
                    </a>
                </div>
            </div>
        </div>

        @php
            // Statistiques en temps réel
            $stats = [
                'total' => $tickets->count(),
                'urgent' => $tickets->where('status', 'URGENT')->count(),
                'open' => $tickets->where('status', 'OPEN')->count(),
                'in_progress' => $tickets->where('status', 'IN_PROGRESS')->count(),
                'resolved' => $tickets->where('status', 'RESOLVED')->count(),
                'closed' => $tickets->where('status', 'CLOSED')->count(),
                'unassigned' => $tickets->whereNull('assigned_to_id')->count()
            ];

            // SLA et performance
            $slaBreaches = $tickets->filter(function($ticket) {
                return !$ticket->first_response_at &&
                       $ticket->created_at->diffInHours(now()) > 24;
            });

            $avgResponseTime = $tickets->filter(function($ticket) {
                return $ticket->first_response_at;
            })->avg(function($ticket) {
                return $ticket->created_at->diffInHours($ticket->first_response_at);
            });

            // Tickets par commercial
            $commercialPerformance = \App\Models\User::where('role', 'COMMERCIAL')
                ->withCount([
                    'assignedTickets as total_assigned',
                    'assignedTickets as active_tickets' => function($q) {
                        $q->whereIn('status', ['OPEN', 'IN_PROGRESS', 'URGENT']);
                    },
                    'assignedTickets as urgent_tickets' => function($q) {
                        $q->where('status', 'URGENT');
                    }
                ])
                ->get();

            // Tendances (derniers 7 jours)
            $weeklyTrend = collect(range(0, 6))->map(function($days) {
                $date = now()->subDays($days);
                return [
                    'date' => $date->format('d/m'),
                    'created' => \App\Models\Ticket::whereDate('created_at', $date)->count(),
                    'resolved' => \App\Models\Ticket::whereDate('resolved_at', $date)->count()
                ];
            })->reverse();

            // Types de tickets les plus fréquents
            $ticketsByType = $tickets->groupBy('type')->map->count()->sortDesc();

            // Clients avec le plus de tickets
            $topClients = $tickets->groupBy('client_id')
                ->map(function($clientTickets) {
                    $client = $clientTickets->first()->client;
                    return [
                        'client' => $client,
                        'count' => $clientTickets->count(),
                        'urgent' => $clientTickets->where('status', 'URGENT')->count(),
                    ];
                })
                ->sortByDesc('count')
                ->take(5);
        @endphp

        <!-- Alertes critiques -->
        @if($slaBreaches->count() > 0 || $stats['urgent'] > 0 || $stats['unassigned'] > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.168 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-red-800">⚠️ Alertes critiques</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($slaBreaches->count() > 0)
                        <div class="bg-red-100 p-4 rounded">
                            <div class="text-red-800 font-semibold">{{ $slaBreaches->count() }} SLA dépassés</div>
                            <div class="text-red-600 text-sm">Tickets sans réponse > 24h</div>
                            <a href="{{ route('supervisor.tickets.index', ['sla_breach' => 1]) }}"
                               class="text-red-800 text-sm font-medium hover:underline">Voir →</a>
                        </div>
                    @endif

                    @if($stats['urgent'] > 0)
                        <div class="bg-red-100 p-4 rounded">
                            <div class="text-red-800 font-semibold">{{ $stats['urgent'] }} tickets urgents</div>
                            <div class="text-red-600 text-sm">Nécessitent attention immédiate</div>
                            <a href="{{ route('supervisor.tickets.index', ['status' => 'URGENT']) }}"
                               class="text-red-800 text-sm font-medium hover:underline">Voir →</a>
                        </div>
                    @endif

                    @if($stats['unassigned'] > 0)
                        <div class="bg-red-100 p-4 rounded">
                            <div class="text-red-800 font-semibold">{{ $stats['unassigned'] }} non assignés</div>
                            <div class="text-red-600 text-sm">Tickets sans commercial</div>
                            <a href="{{ route('supervisor.tickets.index', ['assigned_to' => 'unassigned']) }}"
                               class="text-red-800 text-sm font-medium hover:underline">Voir →</a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Métriques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Tous statuts confondus</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Temps Réponse Moy.</p>
                        <p class="text-3xl font-bold {{ $avgResponseTime > 24 ? 'text-red-600' : ($avgResponseTime > 12 ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ round($avgResponseTime ?? 0, 1) }}h
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Objectif: &lt; 12h</p>
                    </div>
                    <div class="p-3 {{ $avgResponseTime > 24 ? 'bg-red-100' : ($avgResponseTime > 12 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                        <svg class="w-8 h-8 {{ $avgResponseTime > 24 ? 'text-red-600' : ($avgResponseTime > 12 ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Taux de Résolution</p>
                        @php
                            $resolutionRate = $stats['total'] > 0 ? round(($stats['resolved'] + $stats['closed']) / $stats['total'] * 100, 1) : 0;
                        @endphp
                        <p class="text-3xl font-bold {{ $resolutionRate < 60 ? 'text-red-600' : ($resolutionRate < 80 ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ $resolutionRate }}%
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stats['resolved'] + $stats['closed'] }}/{{ $stats['total'] }} résolus</p>
                    </div>
                    <div class="p-3 {{ $resolutionRate < 60 ? 'bg-red-100' : ($resolutionRate < 80 ? 'bg-yellow-100' : 'bg-green-100') }} rounded-full">
                        <svg class="w-8 h-8 {{ $resolutionRate < 60 ? 'text-red-600' : ($resolutionRate < 80 ? 'text-yellow-600' : 'text-green-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Satisfaction Client</p>
                        <p class="text-3xl font-bold text-green-600">87%</p>
                        <p class="text-xs text-gray-500 mt-1">Basé sur retours clients</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Répartition par statut -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Répartition par Statut</h3>
                <div class="space-y-4">
                    @php
                        $statusConfig = [
                            'URGENT' => ['color' => 'red', 'icon' => '🚨', 'label' => 'Urgent'],
                            'OPEN' => ['color' => 'yellow', 'icon' => '📂', 'label' => 'Ouvert'],
                            'IN_PROGRESS' => ['color' => 'blue', 'icon' => '⚡', 'label' => 'En cours'],
                            'RESOLVED' => ['color' => 'green', 'icon' => '✅', 'label' => 'Résolu'],
                            'CLOSED' => ['color' => 'gray', 'icon' => '🔒', 'label' => 'Fermé']
                        ];
                    @endphp

                    @foreach($statusConfig as $status => $config)
                        @php $count = $stats[strtolower($status)] ?? 0; @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span>{{ $config['icon'] }}</span>
                                <span class="font-medium text-gray-900">{{ $config['label'] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $config['color'] }}-500 h-2 rounded-full"
                                         style="width: {{ $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="font-bold text-gray-900 w-8 text-right">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Performance par commercial -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Performance Commerciaux</h3>
                <div class="space-y-4">
                    @foreach($commercialPerformance as $commercial)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">{{ strtoupper(substr($commercial->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $commercial->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $commercial->active_tickets }} actifs • {{ $commercial->total_assigned }} total</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($commercial->urgent_tickets > 0)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full text-red-800 bg-red-100">
                                        🚨 {{ $commercial->urgent_tickets }}
                                    </span>
                                @endif
                                @if($commercial->active_tickets > 10)
                                    <div class="text-xs text-red-600 font-medium">Surchargé</div>
                                @elseif($commercial->active_tickets > 5)
                                    <div class="text-xs text-yellow-600 font-medium">Chargé</div>
                                @else
                                    <div class="text-xs text-green-600 font-medium">Disponible</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Tendance hebdomadaire -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Tendance (7 derniers jours)</h3>
                <div class="space-y-3">
                    @foreach($weeklyTrend as $day)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">{{ $day['date'] }}</span>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-blue-600">Créés:</span>
                                    <span class="font-medium text-blue-600">{{ $day['created'] }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-green-600">Résolus:</span>
                                    <span class="font-medium text-green-600">{{ $day['resolved'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Types de tickets -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Types de Tickets</h3>
                <div class="space-y-3">
                    @php
                        $typeIcons = [
                            'COMPLAINT' => '📋',
                            'QUESTION' => '❓',
                            'SUPPORT' => '🛠️',
                            'OTHER' => '📝'
                        ];
                        $typeLabels = [
                            'COMPLAINT' => 'Réclamations',
                            'QUESTION' => 'Questions',
                            'SUPPORT' => 'Support technique',
                            'OTHER' => 'Autres'
                        ];
                    @endphp

                    @foreach($ticketsByType as $type => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span>{{ $typeIcons[$type] ?? '📝' }}</span>
                                <span class="font-medium text-gray-900">{{ $typeLabels[$type] ?? $type }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full"
                                         style="width: {{ ($count / $stats['total']) * 100 }}%"></div>
                                </div>
                                <span class="font-bold text-gray-900 w-8 text-right">{{ $count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top clients -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Clients avec le plus de tickets</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Tickets</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urgents</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($topClients as $clientData)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">
                                        {{ $clientData['client']->first_name }} {{ $clientData['client']->last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $clientData['count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($clientData['urgent'] > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            🚨 {{ $clientData['urgent'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $clientData['client']->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('commercial.clients.show', $clientData['client']) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        Voir profil →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh every 5 minutes
setInterval(function() {
    window.location.reload();
}, 300000);

// Real-time updates notification
function checkForUpdates() {
    // Cette fonction pourrait être connectée à des WebSockets pour des mises à jour en temps réel
    console.log('Checking for ticket updates...');
}

setInterval(checkForUpdates, 30000); // Check every 30 seconds
</script>
@endpush