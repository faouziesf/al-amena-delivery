<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:CLIENT');
    }

    /**
     * Liste des tickets du client
     */
    public function index(Request $request)
    {
        $query = Ticket::where('client_id', Auth::id())
                      ->with(['messages' => function($q) {
                          $q->orderBy('created_at', 'desc')->limit(1);
                      }])
                      ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(10);

        return view('client.tickets.index', compact('tickets'));
    }

    /**
     * Formulaire de création de ticket
     */
    public function create(Request $request)
    {
        $complaint = null;
        if ($request->filled('complaint_id')) {
            $complaint = Complaint::where('client_id', Auth::id())
                                 ->findOrFail($request->complaint_id);
        }

        return view('client.tickets.create', compact('complaint'));
    }

    /**
     * Créer un nouveau ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:COMPLAINT,QUESTION,SUPPORT,OTHER',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'complaint_id' => 'nullable|exists:complaints,id',
            'package_id' => 'nullable|exists:packages,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        $validated['client_id'] = Auth::id();

        // Créer le ticket
        $ticket = Ticket::create($validated);

        // Message initial du client
        $messageData = [
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'CLIENT',
            'message' => $validated['description'],
            'is_internal' => false
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

        return redirect()->route('client.tickets.show', $ticket)
                        ->with('success', 'Ticket créé avec succès. Un commercial vous répondra bientôt.');
    }

    /**
     * Afficher un ticket et ses messages
     */
    public function show(Ticket $ticket)
    {
        // Vérifier que le ticket appartient au client
        if ($ticket->client_id !== Auth::id()) {
            abort(403);
        }

        $ticket->load([
            'messages' => function($q) {
                $q->where('is_internal', false)->orderBy('created_at', 'asc');
            },
            'messages.sender',
            'complaint',
            'package',
            'assignedTo'
        ]);

        // Marquer les messages non lus du commercial comme lus
        $ticket->messages()
               ->where('sender_type', '!=', 'CLIENT')
               ->whereNull('read_at')
               ->update(['read_at' => now()]);

        return view('client.tickets.show', compact('ticket'));
    }

    /**
     * Ajouter un message à un ticket
     */
    public function addMessage(Request $request, Ticket $ticket)
    {
        // Vérifier que le ticket appartient au client
        if ($ticket->client_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que le ticket accepte encore des messages
        if (!$ticket->canAddMessages()) {
            return back()->with('error', 'Ce ticket est fermé. Vous ne pouvez plus ajouter de messages.');
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        $messageData = [
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'CLIENT',
            'message' => $validated['message'],
            'is_internal' => false
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

        // Remettre le ticket en "IN_PROGRESS" s'il était résolu
        if ($ticket->isResolved()) {
            $ticket->update(['status' => 'IN_PROGRESS']);
        }

        return back()->with('success', 'Message ajouté avec succès.');
    }

    /**
     * Marquer un ticket comme résolu par le client
     */
    public function markResolved(Ticket $ticket)
    {
        // Vérifier que le ticket appartient au client
        if ($ticket->client_id !== Auth::id()) {
            abort(403);
        }

        if ($ticket->isResolved() || $ticket->isClosed()) {
            return back()->with('error', 'Ce ticket est déjà résolu ou fermé.');
        }

        $ticket->markAsResolved(Auth::id());

        return back()->with('success', 'Ticket marqué comme résolu. Merci de votre retour.');
    }

    /**
     * Créer un ticket depuis une réclamation
     */
    public function createFromComplaint(Complaint $complaint)
    {
        // Vérifier que la réclamation appartient au client
        if ($complaint->client_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier qu'il n'y a pas déjà un ticket pour cette réclamation
        if ($complaint->ticket()->exists()) {
            return redirect()->route('client.tickets.show', $complaint->ticket)
                           ->with('info', 'Un ticket existe déjà pour cette réclamation.');
        }

        // Créer le ticket
        $ticket = Ticket::create([
            'type' => 'COMPLAINT',
            'subject' => 'Réclamation #' . $complaint->id . ' - ' . $complaint->type_display,
            'description' => $complaint->description,
            'priority' => $complaint->is_urgent ? 'URGENT' : 'NORMAL',
            'client_id' => Auth::id(),
            'complaint_id' => $complaint->id,
            'package_id' => $complaint->package_id
        ]);

        // Message initial basé sur la réclamation
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'CLIENT',
            'message' => "Réclamation automatiquement convertie en ticket.\n\nType: {$complaint->type_display}\nDescription: {$complaint->description}",
            'is_internal' => false
        ]);

        return redirect()->route('client.tickets.show', $ticket)
                        ->with('success', 'Ticket créé depuis votre réclamation. Un commercial va traiter votre demande.');
    }
}