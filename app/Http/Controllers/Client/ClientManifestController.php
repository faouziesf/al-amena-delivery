<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientManifestController extends Controller
{
    /**
     * Afficher la liste des manifestes ou l'interface de création
     */
    public function index()
    {
        $user = Auth::user();

        // Récupérer les colis disponibles pour manifeste (AVAILABLE et CREATED)
        $availablePackages = Package::where('client_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupRequest'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Grouper les colis par adresse de pickup
        $packagesByPickup = $availablePackages->groupBy(function ($package) {
            if ($package->pickupRequest) {
                return $package->pickupRequest->pickup_address . ' | ' . $package->pickupRequest->pickup_phone;
            }
            return 'Adresse non définie';
        });

        // Récupérer les manifestes existants (utiliser une table ou marquer les packages)
        $existingManifests = $this->getExistingManifests($user->id);

        return view('client.manifests.index', compact('packagesByPickup', 'existingManifests'));
    }

    /**
     * Créer un nouveau manifeste
     */
    public function create()
    {
        $user = Auth::user();

        // Récupérer les colis disponibles pour manifeste
        $availablePackages = Package::where('client_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupRequest'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Grouper par adresse de pickup
        $packagesByPickup = $availablePackages->groupBy(function ($package) {
            if ($package->pickupRequest) {
                return $package->pickupRequest->pickup_address . ' | ' . $package->pickupRequest->pickup_phone;
            }
            return 'Adresse non définie';
        });

        return view('client.manifests.create', compact('packagesByPickup'));
    }

    /**
     * Générer le manifeste PDF
     */
    public function generate(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'pickup_address' => 'required|string|max:255',
            'pickup_contact' => 'required|string|max:100',
            'pickup_phone' => 'required|string|max:20',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Vérifier que tous les colis appartiennent au client
        $packages = Package::whereIn('id', $request->package_ids)
            ->where('client_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupRequest'])
            ->get();

        if ($packages->count() !== count($request->package_ids)) {
            return back()->withErrors(['package_ids' => 'Certains colis sélectionnés ne sont pas valides.']);
        }

        // Générer un numéro de manifeste unique
        $manifestNumber = 'MAN-' . strtoupper(substr($user->name, 0, 3)) . '-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        // Marquer les colis comme "EN_MANIFESTE" (optionnel - créer ce statut si nécessaire)
        // Package::whereIn('id', $request->package_ids)->update(['manifest_number' => $manifestNumber]);

        // Préparer les données pour le PDF
        $manifestData = [
            'manifest_number' => $manifestNumber,
            'client' => $user,
            'packages' => $packages,
            'pickup_info' => [
                'address' => $request->pickup_address,
                'contact' => $request->pickup_contact,
                'phone' => $request->pickup_phone,
                'date' => $request->delivery_date ? Carbon::parse($request->delivery_date) : null,
            ],
            'notes' => $request->notes,
            'generated_at' => now(),
            'total_packages' => $packages->count(),
            'total_weight' => $packages->sum('weight'),
            'total_value' => $packages->sum('declared_value'),
            'total_cod' => $packages->where('cod_amount', '>', 0)->sum('cod_amount'),
        ];

        // Générer le PDF
        $pdf = PDF::loadView('client.manifests.pdf', $manifestData);
        $pdf->setPaper('A4', 'portrait');

        // Sauvegarder les informations du manifeste (optionnel)
        $this->saveManifestRecord($manifestData, $request->package_ids);

        return $pdf->download('manifeste-' . $manifestNumber . '.pdf');
    }

    /**
     * Afficher l'aperçu d'un manifeste
     */
    public function preview(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
        ]);

        $user = Auth::user();

        $packages = Package::whereIn('id', $request->package_ids)
            ->where('client_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupRequest'])
            ->get();

        return response()->json([
            'success' => true,
            'packages' => $packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'tracking_number' => $package->tracking_number,
                    'recipient_name' => $package->recipient_name,
                    'recipient_address' => $package->recipient_address,
                    'recipient_phone' => $package->recipient_phone,
                    'weight' => $package->weight,
                    'declared_value' => $package->declared_value,
                    'cod_amount' => $package->cod_amount,
                    'description' => $package->description,
                    'pickup_address' => $package->pickupRequest?->pickup_address ?? 'Non définie',
                ];
            }),
            'summary' => [
                'total_packages' => $packages->count(),
                'total_weight' => $packages->sum('weight'),
                'total_value' => $packages->sum('declared_value'),
                'total_cod' => $packages->where('cod_amount', '>', 0)->sum('cod_amount'),
            ]
        ]);
    }

    /**
     * Récupérer les manifestes existants (simulation)
     */
    private function getExistingManifests($clientId)
    {
        // Pour l'instant, simulation - vous pouvez créer une table manifests plus tard
        return collect([]);
    }

    /**
     * Sauvegarder les informations du manifeste (optionnel)
     */
    private function saveManifestRecord($manifestData, $packageIds)
    {
        // Optionnel : créer une table manifests pour sauvegarder l'historique
        // DB::table('manifests')->insert([
        //     'manifest_number' => $manifestData['manifest_number'],
        //     'client_id' => $manifestData['client']->id,
        //     'package_ids' => json_encode($packageIds),
        //     'pickup_address' => $manifestData['pickup_info']['address'],
        //     'total_packages' => $manifestData['total_packages'],
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }

    /**
     * API pour récupérer les colis par adresse de pickup
     */
    public function getPackagesByPickup(Request $request)
    {
        $user = Auth::user();

        $packages = Package::where('client_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupRequest'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $packages->groupBy(function ($package) {
            if ($package->pickupRequest) {
                return $package->pickupRequest->pickup_address . ' | ' . $package->pickupRequest->pickup_phone;
            }
            return 'Adresse non définie';
        });

        return response()->json([
            'success' => true,
            'groups' => $grouped->map(function ($packages, $key) {
                return [
                    'key' => $key,
                    'address' => explode(' | ', $key)[0] ?? $key,
                    'phone' => explode(' | ', $key)[1] ?? '',
                    'packages' => $packages->map(function ($package) {
                        return [
                            'id' => $package->id,
                            'tracking_number' => $package->tracking_number,
                            'recipient_name' => $package->recipient_name,
                            'recipient_address' => $package->recipient_address,
                            'weight' => $package->weight . ' kg',
                            'declared_value' => number_format($package->declared_value, 2) . ' TND',
                            'cod_amount' => $package->cod_amount > 0 ? number_format($package->cod_amount, 2) . ' TND' : 'Aucun',
                            'description' => $package->description,
                        ];
                    }),
                    'count' => $packages->count(),
                    'total_weight' => $packages->sum('weight'),
                    'total_value' => $packages->sum('declared_value'),
                ];
            })->values()
        ]);
    }
}