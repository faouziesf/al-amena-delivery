<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SimpleDelivererController extends Controller
{
    /**
     * Dashboard simplifié - Vue principale "Ma Tournée"
     */
    public function dashboard()
    {
        return view('deliverer.simple-dashboard');
    }

    /**
     * Interface scanner simplifiée
     */
    public function scanner()
    {
        return view('deliverer.simple-scanner');
    }

    /**
     * Traitement du scan QR
     */
    public function processScan(Request $request)
    {
        return $this->scanQR($request);
    }

    /**
     * Marquer un colis comme collecté
     */
    public function markPickup(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'PICKED_UP',
            'picked_up_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis collecté']);
    }

    /**
     * Marquer un colis comme livré
     */
    public function markDelivered(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'DELIVERED',
            'delivered_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis livré']);
    }

    /**
     * Marquer un colis comme indisponible
     */
    public function markUnavailable(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'UNAVAILABLE',
            'unavailable_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis marqué indisponible']);
    }

    /**
     * Marquer un colis comme annulé
     */
    public function markCancelled(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $package->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Colis annulé']);
    }

    /**
     * Sauvegarder la signature
     */
    public function saveSignature(Request $request, Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Colis non assigné'], 403);
        }

        $request->validate([
            'signature' => 'required|string'
        ]);

        try {
            $signatureData = $request->signature;
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    throw new \Exception('Type de signature invalide');
                }

                $signatureData = str_replace(' ', '+', $signatureData);
                $signatureData = base64_decode($signatureData);

                if ($signatureData === false) {
                    throw new \Exception('Impossible de décoder la signature');
                }

                $signaturePath = 'signatures/' . $package->tracking_number . '_' . time() . '.' . $type;
                Storage::disk('public')->put($signaturePath, $signatureData);

                $package->update([
                    'delivery_signature' => $signaturePath,
                    'status' => 'DELIVERED',
                    'delivered_at' => now()
                ]);

                return response()->json(['success' => true, 'message' => 'Signature sauvegardée']);
            }

            throw new \Exception('Format de signature invalide');

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Récupérer les collectes du jour
     */
    public function apiPickups()
    {
        $user = Auth::user();

        $pickups = PickupRequest::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['ASSIGNED', 'ACCEPTED'])
            ->with(['packages' => function($query) {
                $query->whereIn('status', ['AVAILABLE', 'CREATED']);
            }])
            ->get()
            ->map(function($pickup) {
                return [
                    'id' => $pickup->id,
                    'pickup_address' => $pickup->pickup_address,
                    'pickup_contact' => $pickup->pickup_contact,
                    'pickup_phone' => $pickup->pickup_phone,
                    'packages_count' => $pickup->packages->count(),
                    'status' => $pickup->status,
                    'type' => 'pickup'
                ];
            });

        return response()->json($pickups);
    }

    /**
     * API: Récupérer les livraisons du jour
     */
    public function apiDeliveries()
    {
        $user = Auth::user();

        $deliveries = Package::where('assigned_deliverer_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
            ->select([
                'id', 'tracking_number', 'recipient_name', 'recipient_address',
                'recipient_phone', 'cod_amount', 'status'
            ])
            ->get()
            ->map(function($package) {
                return [
                    'id' => $package->id,
                    'tracking_number' => $package->tracking_number,
                    'recipient_name' => $package->recipient_name,
                    'recipient_address' => $package->recipient_address,
                    'recipient_phone' => $package->recipient_phone,
                    'cod_amount' => $package->cod_amount,
                    'status' => $package->status,
                    'type' => 'delivery'
                ];
            });

        return response()->json($deliveries);
    }

    /**
     * API: Compléter une collecte
     */
    public function completePickup(Request $request, $pickupId)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $pickup = PickupRequest::where('id', $pickupId)
                ->where('assigned_deliverer_id', $user->id)
                ->firstOrFail();

            // Marquer la collecte comme terminée
            $pickup->update([
                'status' => 'PICKED_UP',
                'picked_up_at' => now(),
                'pickup_signature' => $request->signature ?? null
            ]);

            // Marquer tous les colis comme collectés
            Package::where('pickup_request_id', $pickup->id)
                ->whereIn('status', ['AVAILABLE', 'CREATED'])
                ->update([
                    'status' => 'PICKED_UP',
                    'picked_up_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Collecte terminée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la collecte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Mettre à jour le statut d'une livraison
     */
    public function updateDelivery(Request $request, $packageId)
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:DELIVERED,UNAVAILABLE,CANCELLED,RETURNED'
        ]);

        try {
            $package = Package::where('id', $packageId)
                ->where('assigned_deliverer_id', $user->id)
                ->firstOrFail();

            $updateData = [
                'status' => $request->status,
                'updated_at' => now()
            ];

            // Selon le statut, ajouter les champs appropriés
            switch ($request->status) {
                case 'DELIVERED':
                    $updateData['delivered_at'] = now();
                    break;
                case 'UNAVAILABLE':
                    $updateData['unavailable_at'] = now();
                    break;
                case 'CANCELLED':
                    $updateData['cancelled_at'] = now();
                    break;
                case 'RETURNED':
                    $updateData['returned_at'] = now();
                    break;
            }

            $package->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Compléter une livraison avec signature
     */
    public function completeDelivery(Request $request, $packageId)
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:DELIVERED',
            'signature' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $package = Package::where('id', $packageId)
                ->where('assigned_deliverer_id', $user->id)
                ->firstOrFail();

            // Sauvegarder la signature si fournie
            $signaturePath = null;
            if ($request->signature) {
                $signatureData = $request->signature;
                // Extraire les données base64
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                    $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new \Exception('Type de signature invalide');
                    }

                    $signatureData = str_replace(' ', '+', $signatureData);
                    $signatureData = base64_decode($signatureData);

                    if ($signatureData === false) {
                        throw new \Exception('Impossible de décoder la signature');
                    }

                    $signaturePath = 'signatures/' . $package->tracking_number . '_' . time() . '.' . $type;
                    Storage::disk('public')->put($signaturePath, $signatureData);
                }
            }

            // Mettre à jour le package
            $package->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'delivery_signature' => $signaturePath
            ]);

            // Ajouter le COD au wallet du livreur si applicable
            if ($package->cod_amount > 0) {
                $wallet = UserWallet::firstOrCreate(['user_id' => $user->id]);
                $wallet->increment('balance', $package->cod_amount);

                // Enregistrer la transaction
                $wallet->transactions()->create([
                    'type' => 'COD_COLLECTION',
                    'amount' => $package->cod_amount,
                    'description' => "COD collecté - Colis #{$package->tracking_number}",
                    'package_id' => $package->id,
                    'status' => 'COMPLETED'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison confirmée avec succès',
                'cod_collected' => $package->cod_amount
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Scanner un QR code
     */
    public function scanQR(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $user = Auth::user();
        $qrCode = $request->qr_code;

        try {
            // Rechercher le colis par tracking number
            $package = Package::where('tracking_number', $qrCode)
                ->where('assigned_deliverer_id', $user->id)
                ->first();

            if ($package) {
                return response()->json([
                    'success' => true,
                    'type' => 'package',
                    'data' => [
                        'id' => $package->id,
                        'tracking_number' => $package->tracking_number,
                        'recipient_name' => $package->recipient_name,
                        'recipient_address' => $package->recipient_address,
                        'cod_amount' => $package->cod_amount,
                        'status' => $package->status
                    ]
                ]);
            }

            // Rechercher une collecte
            $pickup = PickupRequest::where('pickup_code', $qrCode)
                ->where('assigned_deliverer_id', $user->id)
                ->first();

            if ($pickup) {
                return response()->json([
                    'success' => true,
                    'type' => 'pickup',
                    'data' => [
                        'id' => $pickup->id,
                        'pickup_address' => $pickup->pickup_address,
                        'pickup_contact' => $pickup->pickup_contact,
                        'packages_count' => $pickup->packages()->count(),
                        'status' => $pickup->status
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'QR code non trouvé ou non assigné à vous'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtenir le solde wallet
     */
    public function apiWalletBalance()
    {
        $user = Auth::user();
        $wallet = UserWallet::where('user_id', $user->id)->first();

        return response()->json([
            'balance' => $wallet ? $wallet->balance : 0,
            'available_balance' => $wallet ? $wallet->available_balance : 0,
            'pending_amount' => $wallet ? $wallet->pending_amount : 0
        ]);
    }
}