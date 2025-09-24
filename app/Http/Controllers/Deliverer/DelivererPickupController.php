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
        $availablePickups = PickupRequest::with(['client', 'assignedDeliverer'])
            ->where('status', 'pending')
            ->whereDate('requested_pickup_date', '>=', Carbon::today())
            ->orderBy('requested_pickup_date')
            ->paginate(10);

        $myPickups = PickupRequest::with(['client'])
            ->where('assigned_deliverer_id', Auth::id())
            ->whereIn('status', ['assigned', 'picked_up'])
            ->orderBy('requested_pickup_date')
            ->paginate(10);

        return view('deliverer.pickups.index', compact('availablePickups', 'myPickups'));
    }

    public function assign(PickupRequest $pickupRequest)
    {
        if ($pickupRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Cette demande n\'est plus disponible']);
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

        DB::transaction(function () use ($pickupRequest) {
            $pickupRequest->update([
                'status' => 'picked_up',
                'picked_up_at' => now()
            ]);

            if ($pickupRequest->packages) {
                Package::whereIn('id', $pickupRequest->packages)
                    ->update(['status' => 'in_progress']);
            }
        });

        return redirect()->route('deliverer.pickups.index')
            ->with('success', 'Collecte complétée avec succès');
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
