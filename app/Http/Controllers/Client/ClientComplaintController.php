<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientComplaintController extends Controller
{
    /**
     * Liste des réclamations du client
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $complaints = $user->complaints()
            ->with(['package', 'assignedCommercial'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.complaints.index', compact('complaints'));
    }

    /**
     * Formulaire de création de réclamation
     */
    public function create(Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        // Vérifier qu'il n'y a pas déjà une réclamation en cours
        $existingComplaint = $package->complaints()
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->first();

        if ($existingComplaint) {
            return redirect()->route('client.complaints.show', $existingComplaint)
                ->with('info', 'Une réclamation est déjà en cours pour ce colis.');
        }

        return view('client.complaints.create', compact('package'));
    }

    /**
     * Enregistrement d'une nouvelle réclamation
     */
    public function store(Package $package, Request $request)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        // Vérifier qu'il n'y a pas déjà une réclamation en cours
        $existingComplaint = $package->complaints()
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->first();

        if ($existingComplaint) {
            return redirect()->route('client.complaints.show', $existingComplaint)
                ->with('info', 'Une réclamation est déjà en cours pour ce colis.');
        }

        $validated = $request->validate([
            'type' => 'required|in:DELIVERY_DELAY,PACKAGE_DAMAGED,PACKAGE_LOST,WRONG_ADDRESS,DELIVERY_REFUSED,COD_ISSUE,OTHER',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'description' => 'required|string|max:1000',
            'expected_resolution' => 'nullable|string|max:500',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120' // 5MB max
        ]);

        try {
            DB::beginTransaction();

            $complaint = Complaint::create([
                'package_id' => $package->id,
                'client_id' => Auth::id(),
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'description' => $validated['description'],
                'expected_resolution' => $validated['expected_resolution'],
                'status' => 'PENDING'
            ]);

            // Traiter les pièces jointes si présentes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('complaints/' . $complaint->id, 'public');
                    
                    $complaint->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);
                }
            }

            // Créer une notification pour les commerciaux
            $this->createComplaintNotification($complaint);

            DB::commit();

            return redirect()->route('client.complaints.show', $complaint)
                ->with('success', "Réclamation #{$complaint->id} créée avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Détails d'une réclamation
     */
    public function show(Complaint $complaint)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        $complaint->load([
            'package.delegationFrom',
            'package.delegationTo',
            'assignedCommercial',
            'attachments',
            'responses.author'
        ]);

        return view('client.complaints.show', compact('complaint'));
    }

    /**
     * Répondre à une réclamation
     */
    public function respond(Complaint $complaint, Request $request)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        if ($complaint->status === 'RESOLVED') {
            return back()->with('error', 'Cette réclamation est déjà résolue.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $response = $complaint->responses()->create([
                'author_id' => Auth::id(),
                'author_type' => 'CLIENT',
                'message' => $validated['message']
            ]);

            // Traiter les pièces jointes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('complaints/' . $complaint->id . '/responses', 'public');
                    
                    $response->attachments()->create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);
                }
            }

            // Mettre à jour le statut si nécessaire
            if ($complaint->status === 'PENDING') {
                $complaint->update(['status' => 'IN_PROGRESS']);
            }

            // Notifier le commercial assigné
            if ($complaint->assigned_commercial_id) {
                $this->createResponseNotification($complaint, $response);
            }

            DB::commit();

            return back()->with('success', 'Votre réponse a été ajoutée avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout de la réponse: ' . $e->getMessage());
        }
    }

    /**
     * Fermer une réclamation (satisfaction client)
     */
    public function close(Complaint $complaint, Request $request)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        if (!in_array($complaint->status, ['IN_PROGRESS', 'RESOLVED'])) {
            return back()->with('error', 'Cette réclamation ne peut pas être fermée.');
        }

        $validated = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $complaint->update([
            'status' => 'CLOSED',
            'satisfaction_rating' => $validated['satisfaction_rating'],
            'client_feedback' => $validated['feedback'],
            'closed_at' => now()
        ]);

        return redirect()->route('client.complaints.index')
            ->with('success', 'Réclamation fermée avec succès. Merci pour votre retour!');
    }

    /**
     * API - Statistiques des réclamations
     */
    public function apiStats()
    {
        $user = Auth::user();

        $stats = [
            'total' => $user->complaints()->count(),
            'pending' => $user->complaints()->where('status', 'PENDING')->count(),
            'in_progress' => $user->complaints()->where('status', 'IN_PROGRESS')->count(),
            'resolved' => $user->complaints()->where('status', 'RESOLVED')->count(),
            'closed' => $user->complaints()->where('status', 'CLOSED')->count(),
            'average_rating' => $user->complaints()
                ->whereNotNull('satisfaction_rating')
                ->avg('satisfaction_rating')
        ];

        return response()->json($stats);
    }

    /**
     * Méthodes privées
     */
    private function createComplaintNotification($complaint)
    {
        // Créer une notification pour tous les commerciaux
        $commercials = \App\Models\User::where('role', 'COMMERCIAL')
            ->where('account_status', 'ACTIVE')
            ->get();

        foreach ($commercials as $commercial) {
            \App\Models\Notification::create([
                'user_id' => $commercial->id,
                'type' => 'NEW_COMPLAINT',
                'title' => 'Nouvelle réclamation',
                'message' => "Nouvelle réclamation #{$complaint->id} pour le colis #{$complaint->package->package_code}",
                'priority' => $complaint->priority === 'URGENT' ? 'HIGH' : 'NORMAL',
                'data' => [
                    'complaint_id' => $complaint->id,
                    'package_code' => $complaint->package->package_code,
                    'client_name' => $complaint->client->name
                ]
            ]);
        }
    }

    private function createResponseNotification($complaint, $response)
    {
        \App\Models\Notification::create([
            'user_id' => $complaint->assigned_commercial_id,
            'type' => 'COMPLAINT_RESPONSE',
            'title' => 'Réponse client à réclamation',
            'message' => "Le client a répondu à la réclamation #{$complaint->id}",
            'priority' => 'NORMAL',
            'data' => [
                'complaint_id' => $complaint->id,
                'response_id' => $response->id,
                'client_name' => $complaint->client->name
            ]
        ]);
    }
}