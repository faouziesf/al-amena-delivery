<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    /**
     * Page de recherche intelligente
     */
    public function index()
    {
        return view('supervisor.search.index');
    }

    /**
     * Effectue une recherche globale
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, packages, users, tickets

        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Veuillez saisir au moins 2 caractères.',
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'packages') {
            $results['packages'] = $this->searchPackages($query);
        }

        if ($type === 'all' || $type === 'users') {
            $results['users'] = $this->searchUsers($query);
        }

        if ($type === 'all' || $type === 'tickets') {
            $results['tickets'] = $this->searchTickets($query);
        }

        $totalResults = collect($results)->sum(fn($r) => count($r));

        return response()->json([
            'query' => $query,
            'total_results' => $totalResults,
            'results' => $results,
        ]);
    }

    /**
     * Vue de recherche avec résultats paginés
     */
    public function results(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');

        if (strlen($query) < 2) {
            return redirect()->route('supervisor.search.index')
                ->with('error', 'Veuillez saisir au moins 2 caractères.');
        }

        $packages = $type === 'all' || $type === 'packages' 
            ? $this->searchPackagesPaginated($query) 
            : null;

        $users = $type === 'all' || $type === 'users' 
            ? $this->searchUsersPaginated($query) 
            : null;

        $tickets = $type === 'all' || $type === 'tickets' 
            ? $this->searchTicketsPaginated($query) 
            : null;

        return view('supervisor.search.results', compact('query', 'type', 'packages', 'users', 'tickets'));
    }

    /**
     * Recherche dans les packages
     */
    private function searchPackages($query, $limit = 10)
    {
        return Package::where(function($q) use ($query) {
            $q->where('tracking_number', 'LIKE', "%{$query}%")
              ->orWhere('package_code', 'LIKE', "%{$query}%")
              ->orWhere('recipient_name', 'LIKE', "%{$query}%")
              ->orWhere('recipient_phone', 'LIKE', "%{$query}%")
              ->orWhere('recipient_address', 'LIKE', "%{$query}%")
              ->orWhere('sender_phone', 'LIKE', "%{$query}%");
        })
        ->with(['sender:id,name,email', 'assignedDeliverer:id,name'])
        ->select('id', 'tracking_number', 'package_code', 'recipient_name', 'recipient_phone', 'status', 'sender_id', 'assigned_deliverer_id')
        ->limit($limit)
        ->get()
        ->map(function($package) {
            return [
                'id' => $package->id,
                'type' => 'package',
                'title' => $package->tracking_number . ' - ' . $package->recipient_name,
                'subtitle' => 'Statut: ' . $package->status,
                'description' => 'Expéditeur: ' . ($package->sender->name ?? 'N/A'),
                'url' => route('supervisor.packages.show', $package->id),
                'data' => [
                    'tracking' => $package->tracking_number,
                    'code' => $package->package_code,
                    'status' => $package->status,
                    'recipient' => $package->recipient_name,
                    'phone' => $package->recipient_phone,
                ],
            ];
        })
        ->toArray();
    }

    /**
     * Recherche dans les packages (paginée)
     */
    private function searchPackagesPaginated($query)
    {
        return Package::where(function($q) use ($query) {
            $q->where('tracking_number', 'LIKE', "%{$query}%")
              ->orWhere('package_code', 'LIKE', "%{$query}%")
              ->orWhere('recipient_name', 'LIKE', "%{$query}%")
              ->orWhere('recipient_phone', 'LIKE', "%{$query}%")
              ->orWhere('recipient_address', 'LIKE', "%{$query}%")
              ->orWhere('sender_phone', 'LIKE', "%{$query}%");
        })
        ->with(['sender:id,name,email', 'assignedDeliverer:id,name'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    }

    /**
     * Recherche dans les utilisateurs
     */
    private function searchUsers($query, $limit = 10)
    {
        return User::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
        })
        ->select('id', 'name', 'email', 'phone', 'role', 'account_status')
        ->limit($limit)
        ->get()
        ->map(function($user) {
            return [
                'id' => $user->id,
                'type' => 'user',
                'title' => $user->name,
                'subtitle' => $user->role . ' - ' . $user->account_status,
                'description' => $user->email . ' | ' . ($user->phone ?? 'N/A'),
                'url' => route('supervisor.users.show', $user->id),
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $user->account_status,
                ],
            ];
        })
        ->toArray();
    }

    /**
     * Recherche dans les utilisateurs (paginée)
     */
    private function searchUsersPaginated($query)
    {
        return User::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    }

    /**
     * Recherche dans les tickets
     */
    private function searchTickets($query, $limit = 10)
    {
        return Ticket::where(function($q) use ($query) {
            $q->where('ticket_number', 'LIKE', "%{$query}%")
              ->orWhere('subject', 'LIKE', "%{$query}%")
              ->orWhere('message', 'LIKE', "%{$query}%");
        })
        ->with(['client:id,name,email'])
        ->select('id', 'ticket_number', 'subject', 'status', 'priority', 'client_id')
        ->limit($limit)
        ->get()
        ->map(function($ticket) {
            return [
                'id' => $ticket->id,
                'type' => 'ticket',
                'title' => $ticket->ticket_number . ' - ' . $ticket->subject,
                'subtitle' => 'Statut: ' . $ticket->status . ' | Priorité: ' . $ticket->priority,
                'description' => 'Client: ' . ($ticket->client->name ?? 'N/A'),
                'url' => route('supervisor.tickets.show', $ticket->id),
                'data' => [
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                ],
            ];
        })
        ->toArray();
    }

    /**
     * Recherche dans les tickets (paginée)
     */
    private function searchTicketsPaginated($query)
    {
        return Ticket::where(function($q) use ($query) {
            $q->where('ticket_number', 'LIKE', "%{$query}%")
              ->orWhere('subject', 'LIKE', "%{$query}%")
              ->orWhere('message', 'LIKE', "%{$query}%");
        })
        ->with(['client:id,name,email'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    }

    /**
     * Suggestions de recherche (autocomplétion)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // Suggestions de packages (5 max)
        $packages = Package::where(function($q) use ($query) {
            $q->where('tracking_number', 'LIKE', "%{$query}%")
              ->orWhere('package_code', 'LIKE', "%{$query}%")
              ->orWhere('recipient_name', 'LIKE', "%{$query}%");
        })
        ->select('id', 'tracking_number', 'recipient_name')
        ->limit(5)
        ->get();

        foreach ($packages as $package) {
            $suggestions[] = [
                'type' => 'package',
                'label' => $package->tracking_number . ' - ' . $package->recipient_name,
                'value' => $package->tracking_number,
                'url' => route('supervisor.packages.show', $package->id),
            ];
        }

        // Suggestions d'utilisateurs (5 max)
        $users = User::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%");
        })
        ->select('id', 'name', 'email', 'role')
        ->limit(5)
        ->get();

        foreach ($users as $user) {
            $suggestions[] = [
                'type' => 'user',
                'label' => $user->name . ' (' . $user->role . ')',
                'value' => $user->name,
                'url' => route('supervisor.users.show', $user->id),
            ];
        }

        return response()->json($suggestions);
    }

    /**
     * Recherche avancée avec filtres
     */
    public function advanced(Request $request)
    {
        $filters = $request->all();
        $type = $request->get('search_type', 'packages');

        $results = match($type) {
            'packages' => $this->advancedSearchPackages($filters),
            'users' => $this->advancedSearchUsers($filters),
            'tickets' => $this->advancedSearchTickets($filters),
            default => null,
        };

        return view('supervisor.search.advanced', compact('results', 'filters', 'type'));
    }

    /**
     * Recherche avancée de packages
     */
    private function advancedSearchPackages($filters)
    {
        $query = Package::query()->with(['sender', 'assignedDeliverer']);

        if (!empty($filters['tracking'])) {
            $query->where('tracking_number', 'LIKE', "%{$filters['tracking']}%");
        }

        if (!empty($filters['recipient_name'])) {
            $query->where('recipient_name', 'LIKE', "%{$filters['recipient_name']}%");
        }

        if (!empty($filters['recipient_phone'])) {
            $query->where('recipient_phone', 'LIKE', "%{$filters['recipient_phone']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sender_id'])) {
            $query->where('sender_id', $filters['sender_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Recherche avancée d'utilisateurs
     */
    private function advancedSearchUsers($filters)
    {
        $query = User::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'LIKE', "%{$filters['email']}%");
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'LIKE', "%{$filters['phone']}%");
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['account_status'])) {
            $query->where('account_status', $filters['account_status']);
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Recherche avancée de tickets
     */
    private function advancedSearchTickets($filters)
    {
        $query = Ticket::query()->with('client');

        if (!empty($filters['ticket_number'])) {
            $query->where('ticket_number', 'LIKE', "%{$filters['ticket_number']}%");
        }

        if (!empty($filters['subject'])) {
            $query->where('subject', 'LIKE', "%{$filters['subject']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
}
