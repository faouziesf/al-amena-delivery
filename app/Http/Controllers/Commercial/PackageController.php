<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CommercialService;
use App\Models\Package;
use App\Models\User;
use App\Models\Delegation;
use App\Models\CodModification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    protected $commercialService;

    public function __construct(CommercialService $commercialService)
    {
        $this->commercialService = $commercialService;
    }

    public function index(Request $request)
    {
        $query = Package::with(['sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        if ($request->filled('deliverer_id')) {
            $query->where('assigned_deliverer_id', $request->deliverer_id);
        }

        if ($request->filled('delegation_from')) {
            $query->where('delegation_from', $request->delegation_from);
        }

        if ($request->filled('delegation_to')) {
            $query->where('delegation_to', $request->delegation_to);
        }

        if ($request->filled('cod_min')) {
            $query->where('cod_amount', '>=', $request->cod_min);
        }

        if ($request->filled('cod_max')) {
            $query->where('cod_amount', '<=', $request->cod_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Suppression du filtre réclamations - les livreurs n'ont pas accès aux réclamations

        if ($request->filled('blocked_only') && $request->blocked_only) {
            $query->inProgress()->where('created_at', '<', now()->subDays(3));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('package_code', 'like', "%{$search}%")
                  ->orWhereJsonContains('recipient_data->name', $search)
                  ->orWhereJsonContains('recipient_data->phone', $search)
                  ->orWhereHas('sender', function ($sender) use ($search) {
                      $sender->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(30);

        // Statistiques (sans réclamations - les livreurs n'y ont pas accès)
        $stats = [
            'total' => Package::count(),
            'created_today' => Package::whereDate('created_at', today())->count(),
            'in_progress' => Package::inProgress()->count(),
            'delivered_today' => Package::delivered()->whereDate('updated_at', today())->count(),
            'blocked' => Package::inProgress()->where('created_at', '<', now()->subDays(3))->count(),
            'total_cod_today' => Package::whereDate('created_at', today())->sum('cod_amount'),
        ];

        // Données pour les filtres
        $delegations = Delegation::active()->orderBy('name')->get();
        $clients = User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->orderBy('name')->get();

        return view('commercial.packages.index', compact('packages', 'stats', 'delegations', 'clients'));
    }

    public function show(Package $package)
    {
        $package->load([
            'sender.clientProfile',
            'assignedDeliverer',
            'delegationFrom',
            'delegationTo',
            'statusHistory.changedBy',
            'codModifications.modifiedByCommercial'
        ]);

        return view('commercial.packages.show', compact('package'));
    }

    public function updateStatus(Request $request, Package $package)
    {
        $request->validate([
            'new_status' => 'required|in:AVAILABLE,ACCEPTED,PICKED_UP,DELIVERED,RETURNED,CANCELLED',
            'notes' => 'nullable|string|max:500',
            'deliverer_id' => 'required_if:new_status,ACCEPTED|nullable|exists:users,id',
        ]);

        try {
            // Si on assigne à un livreur
            if ($request->filled('deliverer_id') && $request->new_status === 'ACCEPTED') {
                $deliverer = User::where('role', 'DELIVERER')
                               ->where('account_status', 'ACTIVE')
                               ->findOrFail($request->deliverer_id);
                
                $package->update([
                    'assigned_deliverer_id' => $deliverer->id,
                    'assigned_at' => now()
                ]);
            }

            $package->updateStatus($request->new_status, Auth::user(), $request->notes, [
                'changed_by_commercial' => true,
                'manual_update' => true
            ]);

            return back()->with('success', 
                "Statut du colis mis à jour vers: {$request->new_status}"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    public function assignDeliverer(Request $request, Package $package)
    {
        $request->validate([
            'deliverer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $deliverer = User::where('role', 'DELIVERER')
                           ->where('account_status', 'ACTIVE')
                           ->findOrFail($request->deliverer_id);

            $package->update([
                'assigned_deliverer_id' => $deliverer->id,
                'assigned_at' => now(),
                'status' => 'ACCEPTED'
            ]);

            $package->updateStatus('ACCEPTED', Auth::user(), $request->notes ?? "Assigné à {$deliverer->name}", [
                'assigned_deliverer' => $deliverer->name,
                'assigned_by_commercial' => true
            ]);

            return back()->with('success', 
                "Colis assigné à {$deliverer->name} avec succès."
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()]);
        }
    }

    public function modifyCod(Request $request, Package $package)
    {
        $request->validate([
            'new_cod_amount' => 'required|numeric|min:0|max:9999.999',
            'reason' => 'required|string|max:255',
            'emergency' => 'boolean',
        ]);

        try {
            $modification = $this->commercialService->modifyCodAmount(
                $package,
                $request->new_cod_amount,
                $request->reason,
                Auth::user(),
                null,
                $request->boolean('emergency')
            );

            return back()->with('success', 
                "COD modifié: " . number_format($modification->old_amount, 3) . 
                " → " . number_format($modification->new_amount, 3) . " DT"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'new_status' => 'required|in:AVAILABLE,CANCELLED,RETURNED',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $updatedCount = 0;
            $packages = Package::whereIn('id', $request->package_ids)->get();

            foreach ($packages as $package) {
                try {
                    $package->updateStatus($request->new_status, Auth::user(), $request->notes, [
                        'bulk_update' => true,
                        'updated_count' => count($request->package_ids)
                    ]);
                    $updatedCount++;
                } catch (\Exception $e) {
                    // Log individual errors but continue
                    \Log::warning("Erreur mise à jour colis {$package->package_code}: {$e->getMessage()}");
                }
            }

            return back()->with('success', 
                "{$updatedCount} colis mis à jour vers le statut: {$request->new_status}"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour groupée: ' . $e->getMessage()]);
        }
    }

    public function bulkAssignDeliverer(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'deliverer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $deliverer = User::where('role', 'DELIVERER')
                           ->where('account_status', 'ACTIVE')
                           ->findOrFail($request->deliverer_id);

            $assignedCount = 0;
            $packages = Package::whereIn('id', $request->package_ids)
                             ->whereIn('status', ['CREATED', 'AVAILABLE'])
                             ->get();

            foreach ($packages as $package) {
                try {
                    $package->update([
                        'assigned_deliverer_id' => $deliverer->id,
                        'assigned_at' => now(),
                        'status' => 'ACCEPTED'
                    ]);

                    $package->updateStatus('ACCEPTED', Auth::user(), 
                        $request->notes ?? "Assignation groupée à {$deliverer->name}", 
                        [
                            'bulk_assignment' => true,
                            'assigned_deliverer' => $deliverer->name
                        ]
                    );

                    $assignedCount++;
                } catch (\Exception $e) {
                    \Log::warning("Erreur assignation colis {$package->package_code}: {$e->getMessage()}");
                }
            }

            return back()->with('success', 
                "{$assignedCount} colis assignés à {$deliverer->name} avec succès."
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation groupée: ' . $e->getMessage()]);
        }
    }

    public function resetDeliveryAttempts(Package $package, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $package->update(['delivery_attempts' => 0]);
            
            $package->updateStatus($package->status, Auth::user(), 
                'Tentatives de livraison remises à zéro. ' . ($request->notes ?? ''), 
                [
                    'attempts_reset' => true,
                    'reset_by_commercial' => true
                ]
            );

            return back()->with('success', 'Tentatives de livraison remises à zéro.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la remise à zéro: ' . $e->getMessage()]);
        }
    }

    public function codHistory(Package $package)
    {
        $modifications = $package->codModifications()
                               ->with('modifiedByCommercial')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            'current_cod' => $package->cod_amount,
            'modifications' => $modifications->map(function ($mod) {
                return [
                    'id' => $mod->id,
                    'old_amount' => number_format($mod->old_amount, 3),
                    'new_amount' => number_format($mod->new_amount, 3),
                    'difference' => $mod->formatted_change,
                    'reason' => $mod->reason,
                    'modified_by' => $mod->modifiedByCommercial->name,
                    'emergency' => $mod->emergency_modification,
                    'created_at' => $mod->created_at->format('d/m/Y H:i'),
                    'created_at_human' => $mod->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    public function generateRunSheet(Request $request)
    {
        $request->validate([
            'delegation_id' => 'required|exists:delegations,id',
            'deliverer_id' => 'nullable|exists:users,id',
            'date' => 'nullable|date',
        ]);

        try {
            $delegation = Delegation::findOrFail($request->delegation_id);
            $date = $request->date ?? today();

            $query = Package::with(['sender', 'assignedDeliverer'])
                          ->where('delegation_to', $delegation->id)
                          ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                          ->whereDate('created_at', $date);

            if ($request->filled('deliverer_id')) {
                $query->where('assigned_deliverer_id', $request->deliverer_id);
            }

            $packages = $query->orderBy('created_at', 'asc')->get();

            // TODO: Générer PDF avec DomPDF
            // $pdf = PDF::loadView('commercial.packages.run_sheet', compact('packages', 'delegation', 'date'));
            // return $pdf->stream("feuille_route_{$delegation->name}_{$date}.pdf");

            // Pour l'instant, retourner une vue
            return view('commercial.packages.run_sheet', compact('packages', 'delegation', 'date'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la génération: ' . $e->getMessage()]);
        }
    }

    // ==================== API ENDPOINTS ====================

    public function apiSearch(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 3) {
            return response()->json([]);
        }

        $packages = Package::where('package_code', 'like', "%{$search}%")
                          ->orWhereJsonContains('recipient_data->name', $search)
                          ->orWhereJsonContains('recipient_data->phone', $search)
                          ->with(['sender', 'assignedDeliverer'])
                          ->limit(10)
                          ->get()
                          ->map(function ($package) {
                              return [
                                  'id' => $package->id,
                                  'package_code' => $package->package_code,
                                  'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                                  'sender_name' => $package->sender->name,
                                  'cod_amount' => number_format($package->cod_amount, 3),
                                  'status' => $package->status,
                                  'assigned_deliverer' => $package->assignedDeliverer->name ?? 'Non assigné',
                                  'show_url' => route('commercial.packages.show', $package->id),
                              ];
                          });

        return response()->json($packages);
    }

    public function apiStats()
    {
        $stats = [
            'total' => Package::count(),
            'created_today' => Package::whereDate('created_at', today())->count(),
            'in_progress' => Package::inProgress()->count(),
            'delivered_today' => Package::delivered()->whereDate('updated_at', today())->count(),
            'blocked' => Package::inProgress()->where('created_at', '<', now()->subDays(3))->count(),
            'with_complaints' => Package::withPendingComplaints()->count(),
            'by_status' => Package::selectRaw('status, COUNT(*) as count')
                                 ->groupBy('status')
                                 ->pluck('count', 'status')
                                 ->toArray(),
            'total_cod_amount' => Package::sum('cod_amount'),
            'average_cod' => Package::where('cod_amount', '>', 0)->avg('cod_amount'),
        ];

        return response()->json($stats);
    }

    public function apiBlockedPackages()
    {
        $packages = Package::with(['sender', 'assignedDeliverer'])
                          ->inProgress()
                          ->where('created_at', '<', now()->subDays(3))
                          ->orderBy('created_at', 'asc')
                          ->limit(20)
                          ->get()
                          ->map(function ($package) {
                              return [
                                  'id' => $package->id,
                                  'package_code' => $package->package_code,
                                  'sender_name' => $package->sender->name,
                                  'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                                  'status' => $package->status,
                                  'days_blocked' => now()->diffInDays($package->created_at),
                                  'assigned_deliverer' => $package->assignedDeliverer->name ?? 'Non assigné',
                                  'cod_amount' => number_format($package->cod_amount, 3),
                                  'show_url' => route('commercial.packages.show', $package->id),
                              ];
                          });

        return response()->json($packages);
    }

    public function apiByDelegation(Request $request)
    {
        $delegationId = $request->input('delegation_id');
        
        if (!$delegationId) {
            return response()->json([]);
        }

        $packages = Package::with(['sender', 'assignedDeliverer'])
                          ->where('delegation_to', $delegationId)
                          ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                          ->orderBy('created_at', 'desc')
                          ->limit(50)
                          ->get()
                          ->map(function ($package) {
                              return [
                                  'id' => $package->id,
                                  'package_code' => $package->package_code,
                                  'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                                  'recipient_phone' => $package->recipient_data['phone'] ?? 'N/A',
                                  'cod_amount' => number_format($package->cod_amount, 3),
                                  'status' => $package->status,
                                  'assigned_deliverer' => $package->assignedDeliverer->name ?? 'Non assigné',
                                  'created_at' => $package->created_at->format('d/m H:i'),
                              ];
                          });

        return response()->json($packages);
    }
}