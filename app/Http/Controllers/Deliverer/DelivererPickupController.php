<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class DelivererPickupController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $deliverer = Auth::user();

        // Vérifier si le livreur peut voir les pickup requests
        if (!$deliverer->canViewPickupRequests()) {
            return view('deliverer.pickups.index', [
                'availablePickups' => collect(),
                'myPickups' => collect(),
                'error' => 'Votre type de compte ne permet pas de voir les demandes de collecte.',
                'deliverer_type' => $deliverer->deliverer_type
            ]);
        }

        // Logique selon le type de livreur
        if ($deliverer->isJokerDeliverer()) {
            // Les livreurs JOKER voient tous les pickups
            $availablePickups = PickupRequest::with(['client', 'assignedDeliverer'])
                ->where('status', 'pending')
                ->whereDate('requested_pickup_date', '>=', Carbon::today())
                ->orderBy('requested_pickup_date')
                ->paginate(10);
        } elseif ($deliverer->isDelegationDeliverer()) {
            // Vérifier si le livreur a une délégation assignée
            if (!$deliverer->hasAssignedDelegation()) {
                return view('deliverer.pickups.index', [
                    'availablePickups' => collect(),
                    'myPickups' => collect(),
                    'error' => 'Aucune délégation assignée. Contactez votre superviseur.',
                    'deliverer_type' => $deliverer->deliverer_type
                ]);
            }

            // Filtrer les pickups disponibles par délégation du livreur
            $availablePickups = PickupRequest::with(['client', 'assignedDeliverer'])
                ->where('status', 'pending')
                ->where('delegation', $deliverer->assigned_delegation)
                ->whereDate('requested_pickup_date', '>=', Carbon::today())
                ->orderBy('requested_pickup_date')
                ->paginate(10);
        } else {
            // Fallback pour autres types
            $availablePickups = collect();
        }

        $myPickups = PickupRequest::with(['client'])
            ->where('assigned_deliverer_id', Auth::id())
            ->whereIn('status', ['assigned', 'picked_up'])
            ->orderBy('requested_pickup_date')
            ->paginate(10);

        return view('deliverer.pickups.index', compact('availablePickups', 'myPickups'));
    }

    public function assign(PickupRequest $pickupRequest)
    {
        $deliverer = Auth::user();

        if ($pickupRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cette demande n\'est plus disponible']);
        }

        // Vérifier que le livreur peut voir les pickup requests
        if (!$deliverer->canViewPickupRequests()) {
            return back()->withErrors(['type' => 'Votre type de compte ne permet pas d\'assigner des demandes de collecte.']);
        }

        // Vérifications selon le type de livreur
        if ($deliverer->isDelegationDeliverer()) {
            // Vérifier que le livreur a une délégation assignée
            if (!$deliverer->hasAssignedDelegation()) {
                return back()->withErrors(['delegation' => 'Aucune délégation assignée. Contactez votre superviseur.']);
            }

            // Vérifier que le pickup est dans la délégation du livreur
            if ($pickupRequest->delegation !== $deliverer->assigned_delegation) {
                return back()->withErrors(['delegation' => 'Ce pickup n\'est pas dans votre délégation.']);
            }
        } elseif ($deliverer->isJokerDeliverer()) {
            // Les livreurs JOKER peuvent s'assigner n'importe quel pickup
            // Pas de vérification de délégation
        }

        DB::transaction(function () use ($pickupRequest) {
            $pickupRequest->update([
                'assigned_deliverer_id' => Auth::id(),
                'assigned_at' => now(),
                'status' => 'assigned'
            ]);

            if ($pickupRequest->packages) {
                Package::whereIn('id', $pickupRequest->packages)
                    ->update(['assigned_deliverer_id' => Auth::id()]);
            }
        });

        return redirect()->route('deliverer.pickups.show', $pickupRequest)
            ->with('success', 'Demande de collecte assignée avec succès');
    }

    public function show(PickupRequest $pickupRequest)
    {
        $this->authorize('view', $pickupRequest);

        // Load related models
        $pickupRequest->load(['client']);

        $packages = Package::whereIn('id', $pickupRequest->packages ?? [])->get();

        return view('deliverer.pickups.show', compact('pickupRequest', 'packages'));
    }

    public function complete(PickupRequest $pickupRequest)
    {
        $this->authorize('update', $pickupRequest);

        if ($pickupRequest->status !== 'assigned') {
            return back()->withErrors(['status' => 'Cette demande ne peut pas être complétée']);
        }

        try {
            // Utiliser la méthode du modèle pour marquer comme collecté
            $pickupRequest = $pickupRequest->markAsPickedUp(Auth::id());
        } catch (\Exception $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        $packagesCount = is_array($pickupRequest->packages) ? count($pickupRequest->packages) : 0;

        return redirect()->route('deliverer.pickups.index')
            ->with('success', "Collecte complétée avec succès. {$packagesCount} colis marqués comme collectés.");
    }

    public function scan()
    {
        return view('deliverer.pickups.scan');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrCode = $request->qr_code;

        $pickupRequest = PickupRequest::where('id', $qrCode)
            ->orWhere(function($query) use ($qrCode) {
                $query->whereJsonContains('packages', (int)$qrCode);
            })
            ->first();

        if (!$pickupRequest) {
            return response()->json(['success' => false, 'message' => 'Demande de collecte non trouvée']);
        }

        if ($pickupRequest->assigned_deliverer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cette demande n\'est pas assignée à vous']);
        }

        if ($pickupRequest->status !== 'assigned') {
            return response()->json(['success' => false, 'message' => 'Cette demande n\'est pas prête pour la collecte']);
        }

        return response()->json([
            'success' => true,
            'pickup_request' => $pickupRequest,
            'redirect_url' => route('deliverer.pickups.show', $pickupRequest)
        ]);
    }

    public function apiStats()
    {
        $stats = [
            'available_pickups' => PickupRequest::where('status', 'pending')
                ->whereDate('requested_pickup_date', '>=', Carbon::today())
                ->count(),
            'my_assigned' => PickupRequest::where('assigned_deliverer_id', Auth::id())
                ->where('status', 'assigned')
                ->count(),
            'completed_today' => PickupRequest::where('assigned_deliverer_id', Auth::id())
                ->where('status', 'picked_up')
                ->whereDate('picked_up_at', Carbon::today())
                ->count(),
            'pending_today' => PickupRequest::where('assigned_deliverer_id', Auth::id())
                ->where('status', 'assigned')
                ->whereDate('requested_pickup_date', Carbon::today())
                ->count()
        ];

        return response()->json($stats);
    }
}
