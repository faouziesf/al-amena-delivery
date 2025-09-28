<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\ClientPickupAddress;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientPickupRequestController extends Controller
{
    public function index()
    {
        $pickupRequests = PickupRequest::where('client_id', Auth::id())
            ->with(['assignedDeliverer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.pickup-requests.index', compact('pickupRequests'));
    }

    public function create()
    {
        $savedAddresses = ClientPickupAddress::forClient(Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('client.pickup-requests.create', compact('savedAddresses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'address_type' => 'required|in:saved,custom',
            'saved_address_id' => 'required_if:address_type,saved|exists:client_pickup_addresses,id',
            'pickup_address' => 'nullable|string|max:500|required_if:address_type,custom',
            'pickup_phone' => 'nullable|string|max:20',
            'pickup_contact_name' => 'nullable|string|max:255',
            'delegation_from' => 'nullable|string|max:255|required_if:address_type,custom',
            'pickup_notes' => 'nullable|string|max:1000',
            'requested_pickup_date' => 'required|date|after:now'
        ]);

        // Initialiser les variables
        $address = '';
        $phone = $request->pickup_phone ?? '';
        $contactName = $request->pickup_contact_name ?? '';
        $delegationFrom = '';
        $selectedPickupAddress = null;

        if ($request->address_type === 'saved') {
            $selectedPickupAddress = ClientPickupAddress::forClient(Auth::id())
                ->findOrFail($request->saved_address_id);

            // Utiliser les données de l'adresse sauvegardée
            $address = $selectedPickupAddress->address;
            $phone = $phone ?: $selectedPickupAddress->phone;
            $contactName = $contactName ?: $selectedPickupAddress->contact_name;
            $delegationFrom = $selectedPickupAddress->gouvernorat ?: 'N/A';
        } else {
            // Utiliser les données du formulaire pour une adresse personnalisée
            $address = $request->pickup_address ?? '';
            $delegationFrom = $request->delegation_from ?? 'N/A';
        }

        // Gestion spéciale si delegation_from est l'indicateur du frontend
        if ($delegationFrom === 'saved_address' && $request->address_type === 'saved') {
            $delegationFrom = $selectedPickupAddress->gouvernorat ?? 'Non spécifié';
        }

        // S'assurer que delegation_from n'est jamais vide ou null
        if (empty($delegationFrom) || $delegationFrom === '' || is_null($delegationFrom)) {
            $delegationFrom = 'Non spécifié';
        }

        $pickupRequest = PickupRequest::create([
            'client_id' => Auth::id(),
            'pickup_address' => $address,
            'pickup_phone' => $phone,
            'pickup_contact_name' => $contactName,
            'pickup_notes' => $request->pickup_notes,
            'delegation_from' => $delegationFrom,
            'requested_pickup_date' => $request->requested_pickup_date,
            'status' => 'pending'
        ]);

        return redirect()->route('client.pickup-requests.index')
            ->with('success', 'Demande de collecte créée avec succès');
    }

    public function show(PickupRequest $pickupRequest)
    {
        $this->authorize('view', $pickupRequest);

        return view('client.pickup-requests.show', compact('pickupRequest'));
    }

    public function cancel(PickupRequest $pickupRequest)
    {
        $this->authorize('update', $pickupRequest);

        if (!in_array($pickupRequest->status, ['pending', 'assigned'])) {
            return back()->withErrors(['status' => 'Cette demande ne peut plus être annulée']);
        }

        $pickupRequest->update(['status' => 'cancelled']);

        return redirect()->route('client.pickup-requests.index')
            ->with('success', 'Demande de collecte annulée avec succès');
    }

    /**
     * API - Statistiques des pickup requests
     */
    public function apiStats()
    {
        $userId = Auth::id();

        $pickupRequests = PickupRequest::where('client_id', $userId);

        return response()->json([
            'total' => $pickupRequests->count(),
            'pending' => $pickupRequests->where('status', 'pending')->count(),
            'assigned' => $pickupRequests->where('status', 'assigned')->count(),
            'picked_up' => $pickupRequests->where('status', 'picked_up')->count(),
            'cancelled' => $pickupRequests->where('status', 'cancelled')->count(),
            'this_month' => $pickupRequests->whereMonth('created_at', now()->month)->count(),
            'this_week' => $pickupRequests->where('created_at', '>=', now()->startOfWeek())->count(),
        ]);
    }

    /**
     * API - Pickup requests récentes
     */
    public function apiRecent($limit = 5)
    {
        $pickupRequests = PickupRequest::where('client_id', Auth::id())
            ->with(['assignedDeliverer:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($pickup) {
                return [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'status_display' => $this->getStatusDisplay($pickup->status),
                    'status_color' => $this->getStatusColor($pickup->status),
                    'packages_count' => 0, // Pas de colis associés directement
                    'requested_pickup_date' => $pickup->requested_pickup_date?->format('d/m/Y H:i'),
                    'created_at' => $pickup->created_at->diffForHumans(),
                    'deliverer_name' => $pickup->assignedDeliverer?->name,
                    'pickup_address_name' => $pickup->pickup_address,
                    'show_url' => route('client.pickup-requests.show', $pickup)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pickupRequests
        ]);
    }

    /**
     * Obtenir l'affichage du statut
     */
    private function getStatusDisplay($status)
    {
        return match($status) {
            'pending' => 'En attente',
            'assigned' => 'Assignée',
            'picked_up' => 'Collectée',
            'cancelled' => 'Annulée',
            default => $status
        };
    }

    /**
     * Obtenir la couleur du statut
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'picked_up' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

}
