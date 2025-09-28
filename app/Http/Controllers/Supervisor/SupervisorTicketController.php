<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorTicketController extends Controller
{
    public function __construct()
    {
        // Middleware handling is done in routes or through middleware groups
    }

    /**
     * Dashboard superviseur avec focus sur les tickets urgents
     */
    public function index(Request $request)
    {
        // Tickets urgents (priorité principale du superviseur)
        $urgentTickets = Ticket::urgent()
                              ->with(['client', 'assignedTo', 'messages' => function($q) {
                                  $q->orderBy('created_at', 'desc')->limit(1);
                              }])
                              ->orderBy('created_at', 'desc')
                              ->get();

        // Tickets nécessitant attention (non traités depuis 2+ jours)
        $needsAttentionTickets = Ticket::needsAttention()
                                      ->with(['client', 'assignedTo'])
                                      ->limit(10)
                                      ->get();

        // Statistiques avancées du système
        $stats = $this->getAdvancedStats();

        // Performance des commerciaux
        $commercialPerformance = $this->getCommercialPerformance();

        return view('supervisor.tickets.index', compact(
            'urgentTickets',
            'needsAttentionTickets',
            'stats',
            'commercialPerformance'
        ));
    }

    /**
     * Vue d'ensemble complète de tous les tickets
     */
    public function overview(Request $request)
    {
        $query = Ticket::with(['client', 'assignedTo', 'messages' => function($q) {
                          $q->orderBy('created_at', 'desc')->limit(1);
                      }])
                      ->orderBy('created_at', 'desc');

        // Filtres superviseur (plus étendus)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to_id');
            } else {
                $query->where('assigned_to_id', $request->assigned_to);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('response_time')) {
            // Filtrer par temps de première réponse
            switch ($request->response_time) {
                case 'no_response':
                    $query->whereNull('first_response_at');
                    break;
                case 'fast': // < 2h
                    $query->whereNotNull('first_response_at')
                          ->whereRaw('(julianday(first_response_at) - julianday(created_at)) * 24 < 2');
                    break;
                case 'slow': // > 24h
                    $query->whereNotNull('first_response_at')
                          ->whereRaw('(julianday(first_response_at) - julianday(created_at)) * 24 > 24');
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(20);

        // Liste des commerciaux pour filtres
        $commercials = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                          ->orderBy('name')
                          ->get(['id', 'name']);

        return view('supervisor.tickets.overview', compact('tickets', 'commercials'));
    }

    /**
     * Afficher un ticket (vue superviseur avec messages internes)
     */
    public function show(Ticket $ticket)
    {
        $ticket->load([
            'client',
            'complaint',
            'package',
            'assignedTo',
            'messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
            'messages.sender'
        ]);

        return view('supervisor.tickets.show', compact('ticket'));
    }

    /**
     * Escalader un ticket (marquer comme urgent)
     */
    public function escalate(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'reassign_to' => 'nullable|exists:users,id'
        ]);

        $reason = "Escaladé par superviseur: " . $validated['reason'];
        $ticket->markAsUrgent($reason);

        // Réassigner si demandé
        if ($validated['reassign_to']) {
            $newCommercial = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                                ->findOrFail($validated['reassign_to']);
            $ticket->assignTo($newCommercial->id);
        }

        // Message interne de l'escalade
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'SUPERVISOR',
            'message' => "🚨 TICKET ESCALADÉ EN URGENCE\n\nRaison: {$validated['reason']}\n\nAction requise immédiate.",
            'is_internal' => true
        ]);

        return back()->with('success', 'Ticket escaladé en urgence avec succès.');
    }

    /**
     * Réassigner en masse des tickets
     */
    public function bulkReassign(Request $request)
    {
        $validated = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'assigned_to_id' => 'required|exists:users,id'
        ]);

        $commercial = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                         ->findOrFail($validated['assigned_to_id']);

        $tickets = Ticket::whereIn('id', $validated['ticket_ids'])->get();

        foreach ($tickets as $ticket) {
            $ticket->assignTo($commercial->id);

            // Message interne pour chaque ticket
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => Auth::id(),
                'sender_type' => 'SUPERVISOR',
                'message' => "Réassigné en masse à {$commercial->name} par le superviseur.",
                'is_internal' => true
            ]);
        }

        $count = count($tickets);
        return back()->with('success', "{$count} ticket(s) réassigné(s) à {$commercial->name} avec succès.");
    }

    /**
     * Rapport de performance détaillé
     */
    public function performanceReport(Request $request)
    {
        $period = $request->get('period', '30'); // jours
        $startDate = now()->subDays($period);

        // Performance par commercial
        $commercialStats = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                              ->withCount([
                                  'assignedTickets as total_assigned',
                                  'assignedTickets as resolved_count' => function($q) use ($startDate) {
                                      $q->where('status', 'RESOLVED')
                                        ->where('resolved_at', '>=', $startDate);
                                  },
                                  'assignedTickets as closed_count' => function($q) use ($startDate) {
                                      $q->where('status', 'CLOSED')
                                        ->where('closed_at', '>=', $startDate);
                                  }
                              ])
                              ->get();

        // Temps de réponse moyens
        $responseTimeStats = Ticket::selectRaw('
                COALESCE(assigned_to_id, 0) as commercial_id,
                AVG((julianday(first_response_at) - julianday(created_at)) * 24) as avg_response_time,
                COUNT(*) as ticket_count
            ')
            ->whereNotNull('first_response_at')
            ->where('created_at', '>=', $startDate)
            ->groupBy('assigned_to_id')
            ->get();

        // Satisfaction client (basée sur résolution rapide)
        $satisfactionStats = Ticket::selectRaw('
                COALESCE(assigned_to_id, 0) as commercial_id,
                AVG(CASE WHEN (julianday(resolved_at) - julianday(created_at)) <= 1 THEN 100 ELSE 50 END) as satisfaction_score
            ')
            ->where('status', 'RESOLVED')
            ->where('created_at', '>=', $startDate)
            ->groupBy('assigned_to_id')
            ->get();

        return view('supervisor.tickets.performance-report', compact(
            'commercialStats',
            'responseTimeStats',
            'satisfactionStats',
            'period'
        ));
    }

    /**
     * Statistiques avancées du système
     */
    private function getAdvancedStats()
    {
        return [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::open()->count(),
            'in_progress' => Ticket::inProgress()->count(),
            'urgent_tickets' => Ticket::urgent()->count(),
            'resolved_today' => Ticket::where('status', 'RESOLVED')
                                     ->whereDate('resolved_at', today())
                                     ->count(),
            'unassigned' => Ticket::whereNull('assigned_to_id')->count(),
            'needs_attention' => Ticket::needsAttention()->count(),
            'avg_resolution_time' => Ticket::whereNotNull('resolved_at')
                                           ->selectRaw('AVG((julianday(resolved_at) - julianday(created_at)) * 24) as avg_time')
                                           ->value('avg_time'),
            'response_rate' => Ticket::whereNotNull('first_response_at')->count() * 100 / max(1, Ticket::count()),
        ];
    }

    /**
     * Performance des commerciaux
     */
    private function getCommercialPerformance()
    {
        return User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                   ->withCount([
                       'assignedTickets as active_tickets' => function($q) {
                           $q->whereIn('status', ['OPEN', 'IN_PROGRESS']);
                       },
                       'assignedTickets as resolved_this_month' => function($q) {
                           $q->where('status', 'RESOLVED')
                             ->whereMonth('resolved_at', now()->month);
                       }
                   ])
                   ->get();
    }

    /**
     * Forcer la fermeture d'un ticket (pouvoir superviseur)
     */
    public function forceClose(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $ticket->close(Auth::id());

        // Message interne de fermeture forcée
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'SUPERVISOR',
            'message' => "🔒 TICKET FERMÉ PAR LE SUPERVISEUR\n\nRaison: {$validated['reason']}\n\nCette action ne peut pas être annulée.",
            'is_internal' => true
        ]);

        return back()->with('success', 'Ticket fermé définitivement par décision superviseur.');
    }
}