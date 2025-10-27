<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreatePackagesRequest;
use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiPackageController extends Controller
{
    /**
     * Créer un ou plusieurs colis
     */
    public function store(CreatePackagesRequest $request)
    {
        $validated = $request->validated();
        $createdPackages = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($validated['packages'] as $packageData) {
                // Générer le code unique du colis
                $packageCode = $this->generateUniquePackageCode();
                
                // Créer le colis
                $package = Package::create([
                    'package_code' => $packageCode,
                    'sender_id' => auth()->id(),
                    'pickup_address_id' => $packageData['pickup_address_id'],
                    'recipient_name' => $packageData['recipient_name'],
                    'recipient_phone' => $packageData['recipient_phone'],
                    'recipient_phone_2' => $packageData['recipient_phone_2'] ?? null,
                    'recipient_gouvernorat' => $packageData['recipient_gouvernorat'],
                    'recipient_delegation' => $packageData['recipient_delegation'],
                    'recipient_address' => $packageData['recipient_address'],
                    'package_content' => $packageData['package_content'],
                    'package_price' => $packageData['package_price'],
                    'delivery_type' => $packageData['delivery_type'],
                    'payment_type' => $packageData['payment_type'],
                    'cod_amount' => $packageData['payment_type'] === 'COD' ? ($packageData['cod_amount'] ?? $packageData['package_price']) : null,
                    'is_fragile' => $packageData['is_fragile'] ?? false,
                    'is_exchange' => $packageData['is_exchange'] ?? false,
                    'comment' => $packageData['comment'] ?? null,
                    'external_reference' => $packageData['external_reference'] ?? null,
                    'status' => 'CREATED',
                    'created_via' => 'API',
                ]);
                
                // Créer l'historique
                PackageHistory::create([
                    'package_id' => $package->id,
                    'status' => 'CREATED',
                    'changed_by' => auth()->id(),
                    'note' => 'Colis créé via API',
                ]);
                
                $createdPackages[] = [
                    'id' => $package->id,
                    'tracking_number' => $package->package_code,
                    'status' => $package->status,
                    'recipient_name' => $package->recipient_name,
                    'created_at' => $package->created_at->toIso8601String(),
                ];
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($createdPackages) . ' colis créé' . (count($createdPackages) > 1 ? 's' : '') . ' avec succès',
                'data' => [
                    'created_count' => count($createdPackages),
                    'packages' => $createdPackages,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création des colis',
                'error_code' => 'CREATION_FAILED',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lister les colis avec filtres et pagination
     */
    public function index(Request $request)
    {
        $query = Package::where('sender_id', auth()->id())
            ->with(['deliverer:id,name,phone', 'pickupAddress:id,name,address,gouvernorat,delegation']);
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par tracking number
        if ($request->filled('tracking_number')) {
            $query->where('package_code', $request->tracking_number);
        }
        
        // Filtre par plage de dates
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filtre par gouvernorat
        if ($request->filled('gouvernorat')) {
            $query->where('recipient_gouvernorat', $request->gouvernorat);
        }
        
        // Filtre par délégation
        if ($request->filled('delegation')) {
            $query->where('recipient_delegation', $request->delegation);
        }
        
        // Filtre par type de paiement
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // Tri
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        if (in_array($sort, ['created_at', 'status', 'package_price'])) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Pagination
        $perPage = min(max((int)$request->input('per_page', 50), 10), 100);
        
        $packages = $query->paginate($perPage);
        
        $data = [
            'packages' => $packages->map(function ($package) {
                return $this->formatPackageForApi($package);
            }),
            'meta' => [
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'per_page' => $packages->perPage(),
                'total' => $packages->total(),
                'from' => $packages->firstItem(),
                'to' => $packages->lastItem(),
            ],
            'links' => [
                'first' => $packages->url(1),
                'last' => $packages->url($packages->lastPage()),
                'prev' => $packages->previousPageUrl(),
                'next' => $packages->nextPageUrl(),
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Afficher les détails d'un colis
     */
    public function show($trackingNumber)
    {
        $package = Package::where('package_code', $trackingNumber)
            ->where('sender_id', auth()->id())
            ->with(['deliverer:id,name,phone', 'pickupAddress'])
            ->first();
        
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non trouvé',
                'error_code' => 'PACKAGE_NOT_FOUND'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'package' => $this->formatPackageForApi($package, true)
            ]
        ]);
    }

    /**
     * Générer les étiquettes PDF pour plusieurs colis
     */
    public function generateLabels(Request $request)
    {
        $request->validate([
            'tracking_numbers' => 'required|array|min:1|max:100',
            'tracking_numbers.*' => 'required|string',
        ]);
        
        $packages = Package::whereIn('package_code', $request->tracking_numbers)
            ->where('sender_id', auth()->id())
            ->get();
        
        if ($packages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun colis trouvé',
                'error_code' => 'NO_PACKAGES_FOUND'
            ], 404);
        }
        
        try {
            // Générer le PDF avec toutes les étiquettes
            $pdf = \PDF::loadView('pdf.package-labels', ['packages' => $packages]);
            
            return $pdf->download('etiquettes-' . now()->format('Y-m-d-His') . '.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des étiquettes',
                'error_code' => 'LABEL_GENERATION_FAILED',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Formater un colis pour l'API
     */
    private function formatPackageForApi($package, $includeFullDetails = false)
    {
        $formatted = [
            'id' => $package->id,
            'tracking_number' => $package->package_code,
            'status' => $package->status,
            'recipient_name' => $package->recipient_name,
            'recipient_phone' => $package->recipient_phone,
            'recipient_gouvernorat' => $package->recipient_gouvernorat,
            'recipient_delegation' => $package->recipient_delegation,
            'recipient_address' => $package->recipient_address,
            'package_content' => $package->package_content,
            'package_price' => (float)$package->package_price,
            'cod_amount' => $package->cod_amount ? (float)$package->cod_amount : null,
            'delivery_type' => $package->delivery_type,
            'payment_type' => $package->payment_type,
            'is_fragile' => (bool)$package->is_fragile,
            'is_exchange' => (bool)$package->is_exchange,
            'created_at' => $package->created_at->toIso8601String(),
            'delivered_at' => $package->delivered_at ? $package->delivered_at->toIso8601String() : null,
        ];
        
        // Ajouter le livreur si assigné
        if ($package->deliverer) {
            $formatted['deliverer_name'] = $package->deliverer->name;
        }
        
        // Ajouter les détails complets si demandé
        if ($includeFullDetails) {
            $formatted['recipient_phone_2'] = $package->recipient_phone_2;
            $formatted['comment'] = $package->comment;
            $formatted['external_reference'] = $package->external_reference;
            
            if ($package->deliverer) {
                $formatted['deliverer'] = [
                    'id' => $package->deliverer->id,
                    'name' => $package->deliverer->name,
                    'phone' => $package->deliverer->phone,
                ];
            }
            
            if ($package->pickupAddress) {
                $formatted['pickup_address'] = [
                    'id' => $package->pickupAddress->id,
                    'name' => $package->pickupAddress->name,
                    'address' => $package->pickupAddress->address,
                    'gouvernorat' => $package->pickupAddress->gouvernorat,
                    'delegation' => $package->pickupAddress->delegation,
                ];
            }
            
            // Ajouter l'historique
            $formatted['history'] = $package->history()->orderBy('created_at')->get()->map(function ($history) {
                return [
                    'status' => $history->status,
                    'date' => $history->created_at->toIso8601String(),
                    'changed_by' => $history->changedBy ? $history->changedBy->name : 'Système',
                    'note' => $history->note,
                ];
            });
        } else {
            // Ajouter un historique simplifié
            $formatted['history'] = $package->history()->orderBy('created_at')->get()->map(function ($history) {
                return [
                    'status' => $history->status,
                    'date' => $history->created_at->toIso8601String(),
                    'note' => $history->note,
                ];
            });
        }
        
        return $formatted;
    }

    /**
     * Générer un code de colis unique
     */
    private function generateUniquePackageCode()
    {
        do {
            $code = 'PKG_' . strtoupper(Str::random(6)) . '_' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Package::where('package_code', $code)->exists());
        
        return $code;
    }
}
