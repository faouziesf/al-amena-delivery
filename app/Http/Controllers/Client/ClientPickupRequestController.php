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

        // PACKAGES DISPONIBLES : Seulement statut AVAILABLE (créés et prêts)
        $availablePackages = Package::where('sender_id', Auth::id())
            ->where('status', 'AVAILABLE')
            ->whereNull('pickup_request_id')
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->get();

        // PICKUP BROUILLONS : Grouper par adresse de pickup
        $draftPickups = $this->getDraftPickupsByAddress();

        return view('client.pickup-requests.create', compact(
            'savedAddresses',
            'availablePackages',
            'draftPickups'
        ));
    }

    /**
     * Obtenir les brouillons de pickup groupés par adresse
     */
    private function getDraftPickupsByAddress()
    {
        $availablePackages = Package::where('sender_id', Auth::id())
            ->where('status', 'AVAILABLE')
            ->whereNull('pickup_request_id')
            ->get();

        // Grouper par adresse de pickup
        $grouped = $availablePackages->groupBy(function ($package) {
            return $package->pickup_address_id ?: 'no_address';
        });

        $draftPickups = [];

        foreach ($grouped as $addressId => $packages) {
            if ($addressId === 'no_address') continue;

            $pickupAddress = $packages->first()->pickupAddress;
            if (!$pickupAddress) continue;

            $draftPickups[] = [
                'pickup_address' => $pickupAddress,
                'packages' => $packages,
                'total_packages' => $packages->count(),
                'total_cod' => $packages->sum('cod_amount')
            ];
        }

        return collect($draftPickups);
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
            'requested_pickup_date' => 'required|date|after:now',
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id'
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

        // Log de debug (à retirer en production)
        \Log::info('Pickup Request Debug', [
            'address_type' => $request->address_type,
            'delegation_from_final' => $delegationFrom,
            'delegation_from_request' => $request->delegation_from,
            'pickup_address_id' => $request->saved_address_id ?? null
        ]);

        $packageIds = Package::whereIn('id', $request->package_ids)
            ->where('sender_id', Auth::id())
            ->whereIn('status', ['CREATED', 'AVAILABLE'])
            ->whereNull('pickup_request_id')
            ->pluck('id')
            ->toArray();

        if (empty($packageIds)) {
            return back()->withErrors(['package_ids' => 'Aucun colis valide sélectionné']);
        }

        DB::transaction(function () use ($request, $address, $phone, $contactName, $delegationFrom, $packageIds) {
            $pickupRequest = PickupRequest::create([
                'client_id' => Auth::id(),
                'pickup_address' => $address,
                'pickup_phone' => $phone,
                'pickup_contact_name' => $contactName,
                'pickup_notes' => $request->pickup_notes,
                'delegation_from' => $delegationFrom,
                'requested_pickup_date' => $request->requested_pickup_date,
                'packages' => $packageIds,
                'status' => 'pending'
            ]);

            Package::whereIn('id', $packageIds)->update([
                'pickup_request_id' => $pickupRequest->id
            ]);
        });

        return redirect()->route('client.pickup-requests.index')
            ->with('success', 'Demande de collecte créée avec succès');
    }

    public function show(PickupRequest $pickupRequest)
    {
        $this->authorize('view', $pickupRequest);

        $packages = Package::whereIn('id', $pickupRequest->packages ?? [])->get();

        return view('client.pickup-requests.show', compact('pickupRequest', 'packages'));
    }

    public function cancel(PickupRequest $pickupRequest)
    {
        $this->authorize('update', $pickupRequest);

        if (!in_array($pickupRequest->status, ['pending', 'assigned'])) {
            return back()->withErrors(['status' => 'Cette demande ne peut plus être annulée']);
        }

        DB::transaction(function () use ($pickupRequest) {
            $pickupRequest->update(['status' => 'cancelled']);

            Package::whereIn('id', $pickupRequest->packages ?? [])
                ->update(['pickup_request_id' => null]);
        });

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
                    'packages_count' => is_array($pickup->packages) ? count($pickup->packages) : 0,
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

    /**
     * Gérer un brouillon de pickup (ajouter/retirer des colis)
     */
    public function manageDraft(Request $request)
    {
        $request->validate([
            'pickup_address_id' => 'required|exists:client_pickup_addresses,id',
            'action' => 'required|in:add,remove',
            'package_ids' => 'required|array',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $pickupAddress = ClientPickupAddress::forClient(Auth::id())
            ->findOrFail($request->pickup_address_id);

        $packages = Package::whereIn('id', $request->package_ids)
            ->where('sender_id', Auth::id())
            ->where('status', 'AVAILABLE')
            ->get();

        if ($request->action === 'add') {
            // Ajouter les colis au brouillon (assigner l'adresse de pickup)
            Package::whereIn('id', $packages->pluck('id'))
                ->whereNull('pickup_request_id')
                ->update(['pickup_address_id' => $pickupAddress->id]);

            $message = 'Colis ajoutés au brouillon avec succès';
        } else {
            // Retirer les colis du brouillon (supprimer l'adresse de pickup)
            Package::whereIn('id', $packages->pluck('id'))
                ->where('pickup_address_id', $pickupAddress->id)
                ->whereNull('pickup_request_id')
                ->update(['pickup_address_id' => null]);

            $message = 'Colis retirés du brouillon avec succès';
        }

        return back()->with('success', $message);
    }

    /**
     * Créer une demande de pickup à partir d'un brouillon
     */
    public function createFromDraft(Request $request)
    {
        $request->validate([
            'pickup_address_id' => 'required|exists:client_pickup_addresses,id',
            'pickup_notes' => 'nullable|string|max:1000',
            'requested_pickup_date' => 'required|date|after:now'
        ]);

        $pickupAddress = ClientPickupAddress::forClient(Auth::id())
            ->findOrFail($request->pickup_address_id);

        // Récupérer tous les colis du brouillon pour cette adresse
        $packageIds = Package::where('sender_id', Auth::id())
            ->where('status', 'AVAILABLE')
            ->where('pickup_address_id', $pickupAddress->id)
            ->whereNull('pickup_request_id')
            ->pluck('id')
            ->toArray();

        if (empty($packageIds)) {
            return back()->withErrors(['pickup_address_id' => 'Aucun colis disponible dans ce brouillon']);
        }

        DB::transaction(function () use ($request, $pickupAddress, $packageIds) {
            $pickupRequest = PickupRequest::create([
                'client_id' => Auth::id(),
                'pickup_address' => $pickupAddress->address,
                'pickup_phone' => $pickupAddress->phone,
                'pickup_contact_name' => $pickupAddress->contact_name,
                'pickup_notes' => $request->pickup_notes,
                'delegation_from' => $pickupAddress->gouvernorat,
                'requested_pickup_date' => $request->requested_pickup_date,
                'packages' => $packageIds,
                'status' => 'pending'
            ]);

            // Assigner les colis à la demande de pickup
            Package::whereIn('id', $packageIds)->update([
                'pickup_request_id' => $pickupRequest->id
            ]);
        });

        return redirect()->route('client.pickup-requests.index')
            ->with('success', 'Demande de collecte créée à partir du brouillon avec succès');
    }

    /**
     * API - Récupérer les colis disponibles pour un brouillon
     */
    public function apiAvailablePackages(Request $request)
    {
        $pickupAddressId = $request->get('pickup_address_id');

        $query = Package::where('sender_id', Auth::id())
            ->where('status', 'AVAILABLE')
            ->whereNull('pickup_request_id')
            ->with(['delegationFrom', 'delegationTo']);

        if ($pickupAddressId) {
            // Colis déjà dans le brouillon
            $inDraft = $query->clone()->where('pickup_address_id', $pickupAddressId)->get();

            // Colis disponibles (sans adresse de pickup ou avec une autre adresse)
            $available = $query->clone()
                ->where(function($q) use ($pickupAddressId) {
                    $q->whereNull('pickup_address_id')
                      ->orWhere('pickup_address_id', '!=', $pickupAddressId);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'in_draft' => $inDraft->map(function($package) {
                        return [
                            'id' => $package->id,
                            'code' => $package->code,
                            'receiver_name' => $package->receiver_name,
                            'receiver_address' => $package->receiver_address,
                            'cod_amount' => $package->cod_amount,
                            'delegation_to' => $package->delegationTo?->name,
                            'created_at' => $package->created_at->format('d/m/Y H:i')
                        ];
                    }),
                    'available' => $available->map(function($package) {
                        return [
                            'id' => $package->id,
                            'code' => $package->code,
                            'receiver_name' => $package->receiver_name,
                            'receiver_address' => $package->receiver_address,
                            'cod_amount' => $package->cod_amount,
                            'delegation_to' => $package->delegationTo?->name,
                            'created_at' => $package->created_at->format('d/m/Y H:i')
                        ];
                    })
                ]
            ]);
        }

        // Tous les colis disponibles
        $packages = $query->get();

        return response()->json([
            'success' => true,
            'data' => $packages->map(function($package) {
                return [
                    'id' => $package->id,
                    'code' => $package->code,
                    'receiver_name' => $package->receiver_name,
                    'receiver_address' => $package->receiver_address,
                    'cod_amount' => $package->cod_amount,
                    'delegation_to' => $package->delegationTo?->name,
                    'pickup_address_id' => $package->pickup_address_id,
                    'created_at' => $package->created_at->format('d/m/Y H:i')
                ];
            })
        ]);
    }
}
