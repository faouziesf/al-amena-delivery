<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommercialTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:COMMERCIAL,SUPERVISOR');
    }

    /**
     * Dashboard des tickets pour commercial
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['client', 'messages' => function($q) {
                          $q->orderBy('created_at', 'desc')->limit(1);
                      }])
                      ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to_id', Auth::id());
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to_id');
            } else {
                $query->where('assigned_to_id', $request->assigned_to);
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

        $tickets = $query->paginate(15);

        // Statistiques pour le dashboard
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'in_progress' => Ticket::inProgress()->count(),
            'urgent' => Ticket::urgent()->count(),
            'my_tickets' => Ticket::assignedTo(Auth::id())->count(),
            'unassigned' => Ticket::whereNull('assigned_to_id')->count(),
            'needs_attention' => Ticket::needsAttention()->count()
        ];

        // Liste des commerciaux pour le filtre
        $commercials = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                          ->select('id', 'name')
                          ->orderBy('name')
                          ->get();

        return view('commercial.tickets.index', compact('tickets', 'stats', 'commercials'));
    }

    /**
     * Afficher un ticket avec tous ses messages
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

        // Marquer les messages non lus du client comme lus
        $ticket->messages()
               ->where('sender_type', 'CLIENT')
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        return view('commercial.tickets.show', compact('ticket'));
    }

    /**
     * Assigner un ticket à un commercial
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assigned_to_id' => 'required|exists:users,id'
        ]);

        $commercial = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                         ->findOrFail($validated['assigned_to_id']);

        $ticket->assignTo($commercial->id);

        // Message interne d'assignation
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => Auth::user()->role,
            'message' => "Ticket assigné à {$commercial->name}.",
            'is_internal' => true
        ]);

        return back()->with('success', "Ticket assigné à {$commercial->name} avec succès.");
    }

    /**
     * Changer le statut d'un ticket
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:OPEN,IN_PROGRESS,RESOLVED,CLOSED,URGENT',
            'reason' => 'nullable|string|max:500'
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        // Logique spéciale selon le nouveau statut
        switch ($newStatus) {
            case 'URGENT':
                $ticket->markAsUrgent($validated['reason'] ?? 'Marqué urgent par ' . Auth::user()->name);
                break;

            case 'RESOLVED':
                $ticket->markAsResolved(Auth::id());
                break;

            case 'CLOSED':
                $ticket->close(Auth::id());
                break;

            default:
                $ticket->update(['status' => $newStatus]);
                break;
        }

        // Message interne du changement de statut
        $statusMessage = "Statut changé de {$ticket->getOriginal('status')} vers {$newStatus}";
        if ($validated['reason']) {
            $statusMessage .= "\nRaison: " . $validated['reason'];
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => Auth::user()->role,
            'message' => $statusMessage,
            'is_internal' => true
        ]);

        return back()->with('success', "Statut du ticket mis à jour vers {$ticket->status_display}.");
    }

    /**
     * Ajouter un message/réponse au ticket
     */
    public function addMessage(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        // Vérifier si on peut encore ajouter des messages
        if (!$ticket->canAddMessages() && !$validated['is_internal']) {
            return back()->with('error', 'Ce ticket est fermé. Seuls les messages internes sont autorisés.');
        }

        $messageData = [
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => Auth::user()->role,
            'message' => $validated['message'],
            'is_internal' => $validated['is_internal'] ?? false
        ];

        // Gérer les pièces jointes
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => Storage::url($path),
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize()
                ];
            }
        }

        if (!empty($attachments)) {
            $messageData['attachments'] = $attachments;
        }

        TicketMessage::create($messageData);

        // Assigner automatiquement le ticket au commercial s'il n'est pas assigné
        if (!$ticket->assigned_to_id && !$validated['is_internal']) {
            $ticket->assignTo(Auth::id());
        }

        $messageType = $validated['is_internal'] ? 'Message interne' : 'Réponse';
        return back()->with('success', "{$messageType} ajouté avec succès.");
    }

    /**
     * Créer un ticket au nom d'un client
     */
    public function create(Request $request)
    {
        $client = null;
        if ($request->filled('client_id')) {
            $client = User::where('role', 'CLIENT')->findOrFail($request->client_id);
        }

        $clients = User::where('role', 'CLIENT')
                      ->orderBy('name')
                      ->get(['id', 'name', 'email']);

        return view('commercial.tickets.create', compact('client', 'clients'));
    }

    /**
     * Enregistrer un ticket créé par le commercial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'type' => 'required|in:COMPLAINT,QUESTION,SUPPORT,OTHER',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'package_id' => 'nullable|exists:packages,id'
        ]);

        // Vérifier que le client existe et a le bon rôle
        $client = User::where('role', 'CLIENT')->findOrFail($validated['client_id']);

        // Créer le ticket
        $ticket = Ticket::create($validated);

        // Assigner automatiquement au commercial qui crée
        $ticket->assignTo(Auth::id());

        // Message initial du commercial
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => Auth::user()->role,
            'message' => "Ticket créé par le commercial au nom du client.\n\n" . $validated['description'],
            'is_internal' => true
        ]);

        return redirect()->route('commercial.tickets.show', $ticket)
                        ->with('success', 'Ticket créé avec succès au nom du client.');
    }

    /**
     * Recherche rapide de tickets
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $tickets = Ticket::where('ticket_number', 'like', "%{$query}%")
                        ->orWhere('subject', 'like', "%{$query}%")
                        ->orWhereHas('client', function($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%")
                              ->orWhere('email', 'like', "%{$query}%");
                        })
                        ->with('client:id,name,email')
                        ->limit(10)
                        ->get(['id', 'ticket_number', 'subject', 'status', 'client_id']);

        return response()->json($tickets);
    }

    /**
     * Export des tickets (pour rapport)
     */
    public function export(Request $request)
    {
        $query = Ticket::with(['client', 'assignedTo']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to_id', Auth::id());
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to_id');
            }
        }

        $tickets = $query->get();

        $csvData = [];
        $csvData[] = ['Numéro', 'Client', 'Sujet', 'Type', 'Statut', 'Priorité', 'Assigné à', 'Créé le', 'Dernière activité'];

        foreach ($tickets as $ticket) {
            $csvData[] = [
                $ticket->ticket_number,
                $ticket->client->name,
                $ticket->subject,
                $ticket->type_display,
                $ticket->status_display,
                $ticket->priority_display,
                $ticket->assignedTo ? $ticket->assignedTo->name : 'Non assigné',
                $ticket->created_at->format('d/m/Y H:i'),
                $ticket->last_activity_at ? $ticket->last_activity_at->format('d/m/Y H:i') : ''
            ];
        }

        $filename = 'tickets_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        foreach ($csvData as $row) {
            fputcsv($handle, $row, ';');
        }

        fclose($handle);
        exit;
    }
}