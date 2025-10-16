<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\Manifest;
use App\Models\ClientPickupAddress;
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

        // Récupérer les manifestes existants avec les status badges
        $existingManifests = Manifest::where('sender_id', $user->id)
            ->with(['pickupAddress', 'pickupRequest'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($manifest) {
                return [
                    'id' => $manifest->id,
                    'manifest_number' => $manifest->manifest_number,
                    'pickup_address_name' => $manifest->pickup_address_name,
                    'pickup_phone' => $manifest->pickup_phone,
                    'total_packages' => $manifest->total_packages,
                    'total_cod_amount' => $manifest->total_cod_amount,
                    'total_weight' => $manifest->total_weight,
                    'generated_at' => $manifest->generated_at,
                    'status_badge' => $manifest->status_badge,
                    'pickup_request_id' => $manifest->pickup_request_id
                ];
            });

        // Récupérer les colis disponibles pour manifeste (CREATED et AVAILABLE sans manifeste)
        $assignedPackageIds = Manifest::where('sender_id', $user->id)
            ->get()
            ->flatMap(function ($manifest) {
                return $manifest->package_ids ?? [];
            })->toArray();

        $availablePackages = Package::where('sender_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->whereNotIn('id', $assignedPackageIds)
            ->with(['pickupAddress'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les adresses de pickup du client
        $clientPickupAddresses = ClientPickupAddress::where('client_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('client.manifests.index', compact('availablePackages', 'existingManifests', 'clientPickupAddresses'));
    }

    /**
     * Créer un nouveau manifeste
     */
    public function create()
    {
        $user = Auth::user();

        // Récupérer les IDs des colis déjà dans des manifestes
        $assignedPackageIds = Manifest::where('sender_id', $user->id)
            ->get()
            ->flatMap(function ($manifest) {
                return $manifest->package_ids ?? [];
            })
            ->toArray();

        // Récupérer les colis disponibles pour manifeste
        $availablePackages = Package::where('sender_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->whereNotIn('id', $assignedPackageIds)
            ->with(['pickupAddress', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les adresses de pickup du client
        $clientPickupAddresses = ClientPickupAddress::where('client_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('client.manifests.create', compact('availablePackages', 'clientPickupAddresses'));
    }

    /**
     * Générer le manifeste PDF
     */
    public function generate(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'exists:packages,id',
            'pickup_address_id' => 'required|exists:client_pickup_addresses,id',
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier que tous les colis appartiennent au client et qu'ils ne sont pas déjà dans un manifeste
            $assignedPackageIds = Manifest::where('sender_id', $user->id)
                ->get()
                ->flatMap(function ($manifest) {
                    return $manifest->package_ids ?? [];
                })
                ->toArray();

            $packages = Package::whereIn('id', $request->package_ids)
                ->where('sender_id', $user->id)
                ->whereIn('status', ['AVAILABLE', 'CREATED'])
                ->whereNotIn('id', $assignedPackageIds)
                ->with(['pickupAddress'])
                ->get();

            if ($packages->count() !== count($request->package_ids)) {
                DB::rollBack();
                return back()->withErrors(['package_ids' => 'Certains colis sélectionnés ne sont pas valides ou sont déjà dans un manifeste.']);
            }

            // Récupérer l'adresse de pickup et vérifier qu'elle appartient au client
            $pickupAddress = ClientPickupAddress::where('id', $request->pickup_address_id)
                ->where('client_id', $user->id)
                ->first();

            if (!$pickupAddress) {
                DB::rollBack();
                return back()->withErrors(['pickup_address_id' => 'Adresse de pickup invalide.']);
            }

            // Créer le manifeste temporaire pour générer le numéro
            $tempManifest = new Manifest();
            $manifestNumber = $tempManifest->generateManifestNumber();

            // Calculer les totaux
            $totalCodAmount = $packages->sum('cod_amount');
            $totalWeight = $packages->sum('package_weight');

            // Créer la demande de pickup
            $pickupRequest = PickupRequest::create([
                'client_id' => $user->id,
                'pickup_address' => $pickupAddress->address,
                'pickup_contact_name' => $pickupAddress->contact_name,
                'pickup_phone' => $pickupAddress->phone,
                'pickup_notes' => 'Collecte pour manifeste ' . $manifestNumber,
                'delegation_from' => $pickupAddress->delegation ?? 'Non spécifié',
                'requested_pickup_date' => now()->addDay(), // Demain par défaut
                'status' => 'pending'
            ]);

            // Créer le manifeste
            $manifest = Manifest::create([
                'manifest_number' => $manifestNumber,
                'sender_id' => $user->id,
                'package_ids' => $packages->pluck('id')->toArray(),
                'pickup_address_id' => $pickupAddress->id,
                'pickup_address_name' => $pickupAddress->name,
                'pickup_phone' => $pickupAddress->phone,
                'total_packages' => $packages->count(),
                'total_cod_amount' => $totalCodAmount,
                'total_weight' => $totalWeight,
                'pickup_request_id' => $pickupRequest->id,
                'status' => Manifest::STATUS_EN_PREPARATION,
                'generated_at' => now()
            ]);

            // LOGIQUE MÉTIER: Mettre à jour le statut de TOUS les colis du manifeste à AVAILABLE
            foreach ($packages as $package) {
                if ($package->status === 'CREATED' || $package->status === 'AVAILABLE') {
                    $package->status = 'AVAILABLE';
                    $package->save();
                }
            }

            DB::commit();

            return redirect()->route('client.manifests.index')
                ->with('success', "Manifeste {$manifestNumber} créé avec succès. Une demande de collecte a été générée automatiquement.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création du manifeste: ' . $e->getMessage()]);
        }
    }

    /**
     * Générer le PDF du manifeste
     */
    public function downloadPdf($manifestId)
    {
        $user = Auth::user();
        $manifest = Manifest::where('id', $manifestId)
            ->where('sender_id', $user->id)
            ->with(['pickupAddress', 'pickupRequest'])
            ->firstOrFail();

        $packages = Package::whereIn('id', $manifest->package_ids)->get();

        // Préparer les données pour le PDF
        $manifestData = [
            'manifest_number' => $manifest->manifest_number,
            'client' => $user,
            'packages' => $packages,
            'pickup_info' => [
                'address' => $manifest->pickup_address_name,
                'phone' => $manifest->pickup_phone,
                'date' => $manifest->generated_at,
            ],
            'generated_at' => $manifest->generated_at,
            'total_packages' => $manifest->total_packages,
            'total_weight' => $manifest->total_weight,
            'total_cod' => $manifest->total_cod_amount,
            'notes' => $manifest->pickupRequest->pickup_notes ?? null,
        ];

        // Générer le PDF
        $pdf = PDF::loadView('client.manifests.pdf', $manifestData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('manifeste-' . $manifest->manifest_number . '.pdf');
    }

    /**
     * Afficher la page d'impression du manifeste
     */
    public function printView($manifestId)
    {
        $user = Auth::user();
        $manifest = Manifest::where('id', $manifestId)
            ->where('sender_id', $user->id)
            ->with(['pickupAddress', 'pickupRequest'])
            ->firstOrFail();

        $packages = Package::whereIn('id', $manifest->package_ids)->get();

        // Préparer les données pour la vue d'impression
        $manifestData = [
            'manifest' => $manifest,
            'manifest_number' => $manifest->manifest_number,
            'client' => $user,
            'packages' => $packages,
            'pickup_info' => [
                'address' => $manifest->pickup_address_name,
                'phone' => $manifest->pickup_phone,
                'date' => $manifest->generated_at,
            ],
            'generated_at' => $manifest->generated_at,
            'total_packages' => $manifest->total_packages,
            'total_weight' => $manifest->total_weight,
            'total_cod' => $manifest->total_cod_amount,
            'notes' => $manifest->pickupRequest->pickup_notes ?? null,
        ];

        return view('client.manifests.print', $manifestData);
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
            ->where('sender_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupAddress'])
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
                    'pickup_address' => $package->pickupAddress?->address ?? 'Non définie',
                ];
            }),
            'summary' => [
                'total_packages' => $packages->count(),
                'total_weight' => $packages->sum('package_weight'),
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
        //     'sender_id' => $manifestData['client']->id,
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

        $packages = Package::where('sender_id', $user->id)
            ->whereIn('status', ['AVAILABLE', 'CREATED'])
            ->with(['pickupAddress'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $packages->groupBy(function ($package) {
            if ($package->pickupAddress) {
                return $package->pickupAddress->address . ' | ' . $package->pickupAddress->phone;
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
                    'total_weight' => $packages->sum('package_weight'),
                    'total_value' => $packages->sum('declared_value'),
                ];
            })->values()
        ]);
    }

    /**
     * Afficher les détails d'un manifeste
     */
    public function show($manifestId)
    {
        $user = Auth::user();
        $manifest = Manifest::where('id', $manifestId)
            ->where('sender_id', $user->id)
            ->with(['pickupAddress', 'pickupRequest'])
            ->firstOrFail();

        $packages = Package::whereIn('id', $manifest->package_ids ?? [])
            ->with(['delegationTo', 'assignedDeliverer'])
            ->get();

        // Transformer le manifeste pour Vue.js
        $manifestData = [
            'id' => $manifest->id,
            'manifest_number' => $manifest->manifest_number,
            'pickup_address_id' => $manifest->pickup_address_id,
            'pickup_address_name' => $manifest->pickup_address_name,
            'pickup_phone' => $manifest->pickup_phone,
            'total_packages' => $manifest->total_packages,
            'total_cod_amount' => $manifest->total_cod_amount,
            'total_weight' => $manifest->total_weight,
            'generated_at' => $manifest->generated_at,
            'status_badge' => $manifest->status_badge,
            'pickup_request_id' => $manifest->pickup_request_id
        ];

        // Mettre à jour le statut du manifeste avant affichage
        $manifest->updateStatus();
        $manifest = $manifest->fresh(); // Recharger depuis la DB

        return view('client.manifests.show', compact('manifest', 'packages', 'manifestData'));
    }

    /**
     * Supprimer un manifeste
     */
    public function destroy($manifestId)
    {
        try {
            $user = Auth::user();
            $manifest = Manifest::where('id', $manifestId)
                ->where('sender_id', $user->id)
                ->firstOrFail();

            // Vérifier si le manifeste peut être supprimé
            if (!$manifest->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce manifeste ne peut pas être supprimé. Il contient des colis déjà ramassés ou livrés.'
                ], 400);
            }

            DB::beginTransaction();

            // Remettre les colis à l'état READY
            $packages = Package::whereIn('id', $manifest->package_ids ?? [])->get();
            foreach ($packages as $package) {
                $package->status = 'READY';
                $package->manifest_id = null;
                $package->save();
            }

            // Supprimer le manifeste
            $manifest->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Manifeste supprimé avec succès.',
                'redirect' => route('client.manifests.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer un colis d'un manifeste
     */
    public function removePackage(Request $request, $manifestId)
    {
        $user = Auth::user();
        $manifest = Manifest::where('id', $manifestId)
            ->where('sender_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'package_id' => 'required|integer|exists:packages,id'
        ]);

        $packageId = $request->package_id;

        if (!$manifest->canRemovePackage($packageId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis ne peut pas être retiré du manifeste (il a peut-être déjà été ramassé).'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $removed = $manifest->removePackage($packageId);

            if ($removed) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Colis retiré du manifeste avec succès.',
                    'new_total' => $manifest->total_packages
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de retirer ce colis du manifeste.'
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API pour obtenir les colis éligibles pour les manifestes
     */
    public function getAvailablePackages(Request $request)
    {
        $user = Auth::user();
        $pickupAddressId = $request->input('pickup_address_id');

        // Récupérer les IDs des colis déjà dans des manifestes
        $assignedPackageIds = Manifest::where('sender_id', $user->id)
            ->get()
            ->flatMap(function ($manifest) {
                return $manifest->package_ids ?? [];
            })
            ->toArray();

        $query = Package::where('sender_id', $user->id)
            ->whereIn('status', ['CREATED', 'AVAILABLE'])
            ->whereNotIn('id', $assignedPackageIds)
            ->with(['pickupAddress', 'delegationTo'])
            ->orderBy('created_at', 'desc');

        // Filtrer par adresse de pickup si spécifiée
        if ($pickupAddressId) {
            $query->where('pickup_address_id', $pickupAddressId);
        }

        $packages = $query->get();

        return response()->json([
            'success' => true,
            'packages' => $packages->map(function($package) {
                return [
                    'id' => $package->id,
                    'package_code' => $package->package_code,
                    'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                    'recipient_phone' => $package->recipient_data['phone'] ?? 'N/A',
                    'recipient_address' => $package->recipient_data['address'] ?? 'N/A',
                    'cod_amount' => $package->cod_amount ?? 0,
                    'content_description' => $package->content_description,
                    'status' => $package->status,
                    'pickup_address' => $package->pickupAddress ? [
                        'id' => $package->pickupAddress->id,
                        'name' => $package->pickupAddress->name,
                        'address' => $package->pickupAddress->address,
                        'phone' => $package->pickupAddress->phone
                    ] : null,
                    'delegation_to' => optional($package->delegationTo)->name ?? 'N/A'
                ];
            })
        ]);
    }

    /**
     * Ajouter un colis à un manifeste existant
     */
    public function addPackage(Request $request, $manifestId)
    {
        $request->validate([
            'package_id' => 'required|integer|exists:packages,id'
        ]);

        $user = Auth::user();
        $manifest = Manifest::where('id', $manifestId)
            ->where('sender_id', $user->id)
            ->firstOrFail();

        $packageId = (int) $request->input('package_id');

        // Vérifier que le colis appartient au client
        $package = Package::where('id', $packageId)
            ->where('sender_id', $user->id)
            ->whereIn('status', ['CREATED', 'AVAILABLE'])
            ->firstOrFail();

        // Vérifier que le colis a la même adresse de pickup que le manifeste
        if ($package->pickup_address_id && $manifest->pickup_address_id &&
            $package->pickup_address_id !== $manifest->pickup_address_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis ne peut pas être ajouté car il n\'a pas la même adresse de pickup que le manifeste.'
            ], 400);
        }

        // Vérifier si le manifeste peut être modifié (pas encore collecté)
        $manifestPackages = Package::whereIn('id', $manifest->package_ids ?? [])->get();
        $hasPickedUpPackages = $manifestPackages->where('status', 'PICKED_UP')->count() > 0;

        if ($hasPickedUpPackages) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'ajouter des colis à un manifeste qui contient des colis déjà collectés.'
            ], 400);
        }

        // Vérifier que le colis n'est pas déjà dans un manifeste
        $existingManifests = Manifest::where('sender_id', $user->id)->get();
        $isInManifest = false;

        foreach ($existingManifests as $existingManifest) {
            $packageIds = $existingManifest->package_ids ?? [];
            if (in_array($packageId, $packageIds)) {
                $isInManifest = true;
                break;
            }
        }

        if ($isInManifest) {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis est déjà dans un manifeste.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Ajouter le colis au manifeste
            $currentPackageIds = $manifest->package_ids ?? [];
            $currentPackageIds[] = $packageId;

            // Recalculer les totaux
            $allPackages = Package::whereIn('id', $currentPackageIds)->get();

            $manifest->update([
                'package_ids' => $currentPackageIds,
                'total_packages' => $allPackages->count(),
                'total_cod_amount' => $allPackages->sum('cod_amount'),
                'total_weight' => $allPackages->sum('package_weight'),
            ]);

            // Recharger le modèle pour s'assurer des bonnes valeurs
            $manifest = $manifest->fresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Colis ajouté au manifeste avec succès.',
                'manifest' => [
                    'total_packages' => $manifest->total_packages,
                    'total_cod_amount' => $manifest->total_cod_amount,
                    'total_weight' => $manifest->total_weight,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'ajout du colis au manifeste', [
                'manifest_id' => $manifestId,
                'package_id' => $packageId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du colis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les détails d'un manifeste avec ses colis
     */
    public function getDetails($manifestId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            $manifest = Manifest::where('id', $manifestId)
                ->where('sender_id', $user->id)
                ->with(['pickupAddress', 'pickupRequest'])
                ->firstOrFail();

            $packages = Package::whereIn('id', $manifest->package_ids ?? [])
                ->with(['delegationTo'])
                ->get();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'manifest' => [
                'id' => $manifest->id,
                'manifest_number' => $manifest->manifest_number,
                'generated_at' => $manifest->generated_at,
                'pickup_address_name' => $manifest->pickup_address_name,
                'pickup_phone' => $manifest->pickup_phone,
                'total_packages' => $manifest->total_packages,
                'total_cod_amount' => $manifest->total_cod_amount,
                'total_weight' => $manifest->total_weight,
                'status_badge' => $manifest->status_badge,
                'can_modify' => $packages->where('status', 'PICKED_UP')->count() === 0
            ],
            'packages' => $packages->map(function($package) {
                return [
                    'id' => $package->id,
                    'package_code' => $package->package_code,
                    'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                    'recipient_phone' => $package->recipient_data['phone'] ?? 'N/A',
                    'recipient_address' => $package->recipient_data['address'] ?? 'N/A',
                    'cod_amount' => $package->cod_amount ?? 0,
                    'content_description' => $package->content_description,
                    'status' => $package->status,
                    'delegation_to' => optional($package->delegationTo)->name ?? 'N/A',
                    'can_remove' => $package->status !== 'PICKED_UP'
                ];
            })
        ]);
    }
}