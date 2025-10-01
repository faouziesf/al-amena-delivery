<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\Complaint;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Complaint::with(['package', 'client', 'assignedCommercial']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Par défaut, afficher seulement les réclamations en attente et en cours
            $query->whereIn('status', ['PENDING', 'IN_PROGRESS']);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('assigned_to_me') && $request->assigned_to_me) {
            $query->where('assigned_commercial_id', Auth::id());
        }

        if ($request->filled('unassigned') && $request->unassigned) {
            $query->whereNull('assigned_commercial_id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('package', function ($package) use ($search) {
                    $package->where('package_code', 'like', "%{$search}%");
                })
                ->orWhereHas('client', function ($client) use ($search) {
                    $client->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('complaint_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $complaints = $query->orderBy('priority', 'desc')
                           ->orderBy('created_at', 'asc')
                           ->paginate(20);

        $stats = $this->commercialService->getComplaintsSummary();
        
        // Ajouter stats spécifiques à ce commercial
        $myStats = [
            'assigned_to_me' => Complaint::where('assigned_commercial_id', Auth::id())
                                        ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                                        ->count(),
            'resolved_by_me_today' => Complaint::where('assigned_commercial_id', Auth::id())
                                             ->where('status', 'RESOLVED')
                                             ->whereDate('resolved_at', today())
                                             ->count(),
        ];

        return view('commercial.complaints.index', compact('complaints', 'stats', 'myStats'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load([
            'package.sender', 
            'package.assignedDeliverer',
            'package.delegationFrom', 
            'package.delegationTo',
            'package.statusHistory.changedBy',
            'package.codModifications.modifiedByCommercial',
            'client',
            'assignedCommercial'
        ]);

        return view('commercial.complaints.show', compact('complaint'));
    }

    public function assign(Complaint $complaint, Request $request)
    {
        if ($complaint->status !== 'PENDING') {
            return back()->withErrors(['error' => 'Cette réclamation ne peut plus être assignée.']);
        }

        try {
            // Auto-assigner au commercial connecté si pas spécifié
            $commercial = $request->filled('commercial_id') 
                ? User::findOrFail($request->commercial_id)
                : Auth::user();

            $complaint->assignTo($commercial);

            return back()->with('success', "Réclamation assignée à {$commercial->name}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()]);
        }
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        if (!$complaint->canBeResolved()) {
            return back()->withErrors(['error' => 'Cette réclamation ne peut plus être résolue.']);
        }

        $request->validate([
            'action' => 'required|in:simple_resolve,cod_change,reschedule,return_package,fourth_attempt',
            'resolution_notes' => 'required|string|max:1000',
            'new_cod_amount' => 'required_if:action,cod_change|nullable|numeric|min:0|max:9999.999',
            'reschedule_date' => 'required_if:action,reschedule|nullable|date|after:today',
            'emergency' => 'boolean'
        ]);

        try {
            switch ($request->action) {
                case 'simple_resolve':
                    $complaint->resolve($request->resolution_notes);
                    break;

                case 'cod_change':
                    if (!$complaint->package->canBeModified()) {
                        throw new \Exception('Le COD de ce colis ne peut plus être modifié.');
                    }
                    
                    // Modifier le COD
                    $this->commercialService->modifyCodAmount(
                        $complaint->package,
                        $request->new_cod_amount,
                        'Suite à réclamation client - ' . $complaint->complaint_code,
                        Auth::user(),
                        $complaint->id,
                        $request->boolean('emergency')
                    );

                    $complaint->resolve(
                        'COD modifié de ' . $complaint->package->cod_amount . ' à ' . $request->new_cod_amount . ' DT. ' . $request->resolution_notes,
                        [
                            'action_taken' => 'cod_modified',
                            'old_cod' => $complaint->package->cod_amount,
                            'new_cod' => $request->new_cod_amount,
                            'emergency' => $request->boolean('emergency')
                        ]
                    );
                    break;

                case 'reschedule':
                    // Reset delivery attempts
                    $complaint->package->update(['delivery_attempts' => 0]);
                    
                    $complaint->resolve(
                        'Livraison reprogrammée au ' . $request->reschedule_date . '. ' . $request->resolution_notes,
                        [
                            'action_taken' => 'rescheduled',
                            'reschedule_date' => $request->reschedule_date
                        ]
                    );
                    break;

                case 'return_package':
                    // Marquer le colis pour retour
                    $complaint->package->updateStatus('RETURNED', Auth::user(), 'Retour suite à réclamation client');
                    
                    $complaint->resolve(
                        'Colis marqué pour retour. ' . $request->resolution_notes,
                        ['action_taken' => 'returned']
                    );
                    break;

                case 'fourth_attempt':
                    // Programmer une 4ème tentative
                    $complaint->package->update(['delivery_attempts' => 3]);
                    
                    $complaint->resolve(
                        '4ème tentative programmée. ' . $request->resolution_notes,
                        ['action_taken' => 'fourth_attempt']
                    );
                    break;
            }

            // Auto-assigner si pas déjà fait
            if (!$complaint->assigned_commercial_id) {
                $complaint->assignTo(Auth::user());
            }

            return redirect()->route('commercial.complaints.index')
                ->with('success', 'Réclamation résolue avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du traitement: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, Complaint $complaint)
    {
        if (!$complaint->canBeResolved()) {
            return back()->withErrors(['error' => 'Cette réclamation ne peut plus être rejetée.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $complaint->reject($request->rejection_reason);

            // Auto-assigner si pas déjà fait
            if (!$complaint->assigned_commercial_id) {
                $complaint->assignTo(Auth::user());
            }

            return redirect()->route('commercial.complaints.index')
                ->with('success', 'Réclamation rejetée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du rejet: ' . $e->getMessage()]);
        }
    }

    public function modifyCod(Request $request, Package $package)
    {
        $request->validate([
            'new_cod_amount' => 'required|numeric|min:0|max:9999.999',
            'reason' => 'required|string|max:255',
            'emergency' => 'boolean',
            'complaint_id' => 'nullable|exists:complaints,id'
        ]);

        try {
            $modification = $this->commercialService->modifyCodAmount(
                $package,
                $request->new_cod_amount,
                $request->reason,
                Auth::user(),
                $request->complaint_id,
                $request->boolean('emergency')
            );

            $message = "COD modifié avec succès pour le colis {$package->package_code}. ";
            $message .= "Ancien montant: " . number_format($modification->old_amount, 3) . " DT, ";
            $message .= "Nouveau montant: " . number_format($modification->new_amount, 3) . " DT";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'complaint_ids' => 'required|array|min:1',
            'complaint_ids.*' => 'exists:complaints,id',
            'commercial_id' => 'nullable|exists:users,id',
        ]);

        try {
            $commercial = $request->filled('commercial_id') 
                ? User::findOrFail($request->commercial_id)
                : Auth::user();

            $assignedCount = 0;
            $complaints = Complaint::whereIn('id', $request->complaint_ids)
                                 ->where('status', 'PENDING')
                                 ->get();

            foreach ($complaints as $complaint) {
                $complaint->assignTo($commercial);
                $assignedCount++;
            }

            return back()->with('success', "{$assignedCount} réclamations assignées à {$commercial->name}.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation groupée: ' . $e->getMessage()]);
        }
    }

    public function markAsUrgent(Complaint $complaint, Request $request)
    {
        try {
            $complaint->update(['priority' => 'URGENT']);

            // Créer une notification urgente
            $this->notificationService->notifyComplaintCreated($complaint);

            return back()->with('success', 'Réclamation marquée comme urgente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    // ==================== API ENDPOINTS ====================

    public function apiStats()
    {
        $stats = $this->commercialService->getComplaintsSummary();
        
        return response()->json(array_merge($stats, [
            'assigned_to_me' => Complaint::where('assigned_commercial_id', Auth::id())
                                        ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                                        ->count(),
            'resolved_by_me_today' => Complaint::where('assigned_commercial_id', Auth::id())
                                             ->where('status', 'RESOLVED')
                                             ->whereDate('resolved_at', today())
                                             ->count(),
        ]));
    }

    public function apiPending()
    {
        $complaints = Complaint::with(['package', 'client'])
                              ->pending()
                              ->orderBy('priority', 'desc')
                              ->orderBy('created_at', 'asc')
                              ->limit(10)
                              ->get()
                              ->map(function ($complaint) {
                                  return [
                                      'id' => $complaint->id,
                                      'complaint_code' => $complaint->complaint_code,
                                      'type' => $complaint->type_display,
                                      'priority' => $complaint->priority,
                                      'priority_color' => $complaint->priority_color,
                                      'package_code' => $complaint->package->package_code,
                                      'client_name' => $complaint->client->name,
                                      'created_at' => $complaint->created_at->diffForHumans(),
                                      'show_url' => route('commercial.complaints.show', $complaint->id),
                                  ];
                              });

        return response()->json($complaints);
    }

    public function apiRecentActivity()
    {
        $recentActivity = Complaint::with(['package', 'client', 'assignedCommercial'])
                                  ->where('updated_at', '>=', now()->subHours(24))
                                  ->orderBy('updated_at', 'desc')
                                  ->limit(20)
                                  ->get()
                                  ->map(function ($complaint) {
                                      return [
                                          'id' => $complaint->id,
                                          'complaint_code' => $complaint->complaint_code,
                                          'type' => $complaint->type_display,
                                          'status' => $complaint->status_display,
                                          'package_code' => $complaint->package->package_code,
                                          'client_name' => $complaint->client->name,
                                          'assigned_to' => $complaint->assignedCommercial->name ?? 'Non assignée',
                                          'updated_at' => $complaint->updated_at->diffForHumans(),
                                      ];
                                  });

        return response()->json($recentActivity);
    }
}