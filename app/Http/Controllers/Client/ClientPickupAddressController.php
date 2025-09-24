<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientPickupAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPickupAddressController extends Controller
{
    public function index()
    {
        $addresses = ClientPickupAddress::forClient(Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.pickup-addresses.index', compact('addresses'));
    }

    public function create()
    {
        $gouvernorats = config('tunisia.gouvernorats');
        $delegationsData = config('tunisia.delegations');

        return view('client.pickup-addresses.create', compact('gouvernorats', 'delegationsData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'gouvernorat' => 'required|string|max:255',
            'delegation' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_default' => 'boolean'
        ]);

        if ($request->is_default) {
            ClientPickupAddress::forClient(Auth::id())->update(['is_default' => false]);
        }

        ClientPickupAddress::create([
            'client_id' => Auth::id(),
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'tel2' => $request->tel2,
            'contact_name' => $request->contact_name,
            'gouvernorat' => $request->gouvernorat,
            'delegation' => $request->delegation,
            'notes' => $request->notes,
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('client.pickup-addresses.index')
            ->with('success', 'Adresse de collecte ajoutée avec succès');
    }

    public function edit(ClientPickupAddress $pickupAddress)
    {
        $this->authorize('update', $pickupAddress);

        $gouvernorats = config('tunisia.gouvernorats');
        $delegationsData = config('tunisia.delegations');

        return view('client.pickup-addresses.edit', compact('pickupAddress', 'gouvernorats', 'delegationsData'));
    }

    public function update(Request $request, ClientPickupAddress $pickupAddress)
    {
        $this->authorize('update', $pickupAddress);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'gouvernorat' => 'required|string|max:255',
            'delegation' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_default' => 'boolean'
        ]);

        if ($request->is_default && !$pickupAddress->is_default) {
            ClientPickupAddress::forClient(Auth::id())->update(['is_default' => false]);
        }

        $pickupAddress->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'tel2' => $request->tel2,
            'contact_name' => $request->contact_name,
            'gouvernorat' => $request->gouvernorat,
            'delegation' => $request->delegation,
            'notes' => $request->notes,
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('client.pickup-addresses.index')
            ->with('success', 'Adresse de collecte modifiée avec succès');
    }

    public function destroy(ClientPickupAddress $pickupAddress)
    {
        $this->authorize('delete', $pickupAddress);

        $pickupAddress->delete();

        return redirect()->route('client.pickup-addresses.index')
            ->with('success', 'Adresse de collecte supprimée avec succès');
    }

    public function setDefault(ClientPickupAddress $pickupAddress)
    {
        $this->authorize('update', $pickupAddress);

        ClientPickupAddress::forClient(Auth::id())->update(['is_default' => false]);
        $pickupAddress->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }
}
