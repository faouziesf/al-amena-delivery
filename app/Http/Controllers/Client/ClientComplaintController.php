<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\User;
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
     * Traiter une nouvelle réclamation SELON SPÉCIFICATIONS COMPLÈTES
     */
    public function store(Request $request, Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        // VÉRIFICATIONS CRITIQUES : Délai réclamation et statut
        if (in_array($package->status, ['PAID', 'DELIVERED_PAID'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Réclamation impossible sur un colis déjà payé et verrouillé.'
                ], 400);
            }
            return back()->with('error', 'Réclamation impossible sur un colis déjà payé et verrouillé.');
        }

        // Vérifier le délai de réclamation (jusqu'à fin du jour suivant)
        if ($package->status === 'DELIVERED') {
            $deliveredAt = $package->statusHistory()
                ->where('status', 'DELIVERED')
                ->first()?->created_at;

            if ($deliveredAt) {
                $deadlineDate = $deliveredAt->copy()->addDay()->endOfDay();
                if (now()->isAfter($deadlineDate)) {
                    $message = 'Délai de réclamation dépassé. Les réclamations doivent être faites avant la fin du jour suivant la livraison.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 400);
                    }
                    return back()->with('error', $message);
                }
            }
        }

        $validated = $request->validate([
            'type' => 'required|string|in:CHANGE_COD,DELIVERY_DELAY,REQUEST_RETURN,RETURN_DELAY,RESCHEDULE_TODAY,FOURTH_ATTEMPT,CUSTOM',
            'description' => 'required|string|max:1000',
            'urgent' => 'boolean',
            'new_cod_amount' => 'nullable|numeric|min:0|max:9999.999|required_if:type,CHANGE_COD',
            'preferred_date' => 'nullable|date|after:today|before:' . now()->addDays(8)->format('Y-m-d') . '|required_if:type,RESCHEDULE_TODAY,FOURTH_ATTEMPT'
        ], [
            'new_cod_amount.required_if' => 'Le nouveau montant COD est obligatoire pour un changement de prix.',
            'preferred_date.required_if' => 'La date est obligatoire pour un report ou une 4ème tentative.',
            'preferred_date.before' => 'La date ne peut pas dépasser 7 jours.',
        ]);

        try {
            DB::beginTransaction();

            $complaint = Complaint::create([
                'package_id' => $package->id,
                'client_id' => Auth::id(),
                'type' => $validated['type'],
                'description' => $validated['description'],
                'status' => 'PENDING',
                'urgent' => $validated['urgent'] ?? false,
                'metadata' => [
                    'new_cod_amount' => $validated['new_cod_amount'] ?? null,
                    'preferred_date' => $validated['preferred_date'] ?? null,
                    'current_cod_amount' => $package->cod_amount,
                    'package_status_at_complaint' => $package->status
                ]
            ]);

            // TRAITEMENT AUTOMATIQUE selon le type
            $this->processComplaintByType($complaint, $package, $validated);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Réclamation créée avec succès',
                    'complaint_id' => $complaint->id
                ]);
            }

            return redirect()->route('client.complaints.show', $complaint)
                ->with('success', "Réclamation #{$complaint->id} créée avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Traitement automatique selon le type de réclamation
     */
    private function processComplaintByType($complaint, $package, $validated)
    {
        switch ($complaint->type) {
            case 'CHANGE_COD':
                // Marquer que le commercial doit modifier le COD
                $complaint->update([
                    'needs_commercial_action' => true,
                    'required_action' => 'MODIFY_COD',
                    'action_data' => [
                        'new_amount' => $validated['new_cod_amount'],
                        'old_amount' => $package->cod_amount
                    ]
                ]);
                break;

            case 'RESCHEDULE_TODAY':
                // Programmer le report
                $complaint->update([
                    'needs_commercial_action' => true,
                    'required_action' => 'RESCHEDULE_DELIVERY',
                    'action_data' => [
                        'new_date' => $validated['preferred_date']
                    ]
                ]);
                break;

            case 'FOURTH_ATTEMPT':
                // Programmer 4ème tentative
                $complaint->update([
                    'needs_commercial_action' => true,
                    'required_action' => 'FOURTH_ATTEMPT',
                    'action_data' => [
                        'attempt_date' => $validated['preferred_date']
                    ]
                ]);
                break;

            case 'REQUEST_RETURN':
                // Demande de retour anticipé
                $complaint->update([
                    'needs_commercial_action' => true,
                    'required_action' => 'FORCE_RETURN'
                ]);
                break;

            default:
                // Autres types : attente traitement commercial
                $complaint->update([
                    'needs_commercial_action' => true,
                    'required_action' => 'REVIEW'
                ]);
        }

        // Créer notification pour le commercial
        $this->createCommercialNotification($complaint, $package);
    }

    /**
     * Créer notification pour le commercial
     */
    private function createCommercialNotification($complaint, $package)
    {
        // Chercher un commercial disponible (logique simple pour MVP)
        $commercial = User::where('role', 'COMMERCIAL')
                         ->where('account_status', 'ACTIVE')
                         ->first();

        if ($commercial) {
            $commercial->notifications()->create([
                'type' => 'NEW_COMPLAINT',
                'title' => 'Nouvelle réclamation' . ($complaint->urgent ? ' URGENTE' : ''),
                'message' => "Réclamation #{$complaint->id} sur colis #{$package->package_code}",
                'data' => [
                    'complaint_id' => $complaint->id,
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'type' => $complaint->type,
                    'urgent' => $complaint->urgent
                ]
            ]);

            // Assigner la réclamation au commercial
            $complaint->update(['assigned_commercial_id' => $commercial->id]);
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
     * Rouvrir une réclamation
     */
    public function reopen(Complaint $complaint, Request $request)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        if (!in_array($complaint->status, ['RESOLVED', 'CLOSED'])) {
            return back()->with('error', 'Cette réclamation ne peut pas être rouverte.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $complaint->update([
            'status' => 'IN_PROGRESS',
            'reopen_reason' => $validated['reason'],
            'reopened_at' => now()
        ]);

        // Notifier le commercial
        if ($complaint->assigned_commercial_id) {
            $complaint->assignedCommercial->notifications()->create([
                'type' => 'COMPLAINT_REOPENED',
                'title' => 'Réclamation rouverte',
                'message' => "La réclamation #{$complaint->id} a été rouverte par le client",
                'data' => [
                    'complaint_id' => $complaint->id,
                    'reason' => $validated['reason']
                ]
            ]);
        }

        return back()->with('success', 'Réclamation rouverte avec succès.');
    }

    /**
     * Marquer comme résolu côté client
     */
    public function markResolved(Complaint $complaint, Request $request)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        if ($complaint->status !== 'IN_PROGRESS') {
            return back()->with('error', 'Cette réclamation ne peut pas être marquée comme résolue.');
        }

        $validated = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $complaint->update([
            'status' => 'RESOLVED',
            'satisfaction_rating' => $validated['satisfaction_rating'],
            'client_feedback' => $validated['feedback'],
            'resolved_at' => now(),
            'resolved_by_client' => true
        ]);

        return back()->with('success', 'Réclamation marquée comme résolue. Merci pour votre retour!');
    }

    /**
     * Timeline de la réclamation
     */
    public function showTimeline(Complaint $complaint)
    {
        if ($complaint->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette réclamation.');
        }

        $complaint->load([
            'package',
            'responses.author',
            'statusHistory'
        ]);

        return view('client.complaints.timeline', compact('complaint'));
    }

    /**
     * Méthodes pour traitement rapide des réclamations types
     */
    public function requestCodChange(Request $request, Package $package)
    {
        return $this->store($request->merge(['type' => 'cod_change']), $package);
    }

    public function requestReturn(Request $request, Package $package)
    {
        return $this->store($request->merge(['type' => 'request_return']), $package);
    }

    public function requestReschedule(Request $request, Package $package)
    {
        return $this->store($request->merge(['type' => 'reschedule_today']), $package);
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
    private function createResponseNotification($complaint, $response)
    {
        if (!$complaint->assigned_commercial_id) return;

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