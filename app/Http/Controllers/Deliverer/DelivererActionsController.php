<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PickupRequest;
use App\Models\ReturnPackage;
use App\Models\WithdrawalRequest;
use App\Models\PackageStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur Actions Livreur
 * 
 * Gère toutes les actions du livreur:
 * - Marquer comme ramassé (pickup)
 * - Marquer comme livré (deliver)
 * - Marquer comme indisponible (unavailable)
 * - Capture de signature
 * - Livraison directe après pickup
 */
class DelivererActionsController extends Controller
{
    /**
     * MARQUER COMME RAMASSÉ (Pickup)
     */
    public function markPickup(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non assigné à vous'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $package->update([
                'status' => 'PICKED_UP',
                'picked_up_at' => now()
            ]);

            // Historique
            PackageStatusHistory::create([
                'package_id' => $package->id,
                'status' => 'PICKED_UP',
                'changed_by' => $user->id,
                'notes' => 'Colis ramassé par le livreur'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Colis marqué comme ramassé',
                'package' => $package->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur pickup: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du ramassage'
            ], 500);
        }
    }

    /**
     * MARQUER COMME LIVRÉ (Deliver)
     */
    public function markDelivered(Request $request, Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non assigné à vous'
            ], 403);
        }

        $request->validate([
            'signature_data' => 'nullable|string',
            'delivery_notes' => 'nullable|string|max:500'
        ]);

        // Vérifier si signature obligatoire
        $isSpecial = $package->return_package_id || $package->payment_withdrawal_id;
        $requiresSignature = $package->requires_signature || $isSpecial;

        if ($requiresSignature && !$request->signature_data) {
            return response()->json([
                'success' => false,
                'message' => 'Signature obligatoire pour ce type de colis'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Sauvegarder la signature si fournie
            $signaturePath = null;
            if ($request->signature_data) {
                $signaturePath = $this->storeSignatureFile($request->signature_data, $package->id);
            }

            $package->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'signature_path' => $signaturePath,
                'delivery_notes' => $request->delivery_notes
            ]);

            // Historique
            PackageStatusHistory::create([
                'package_id' => $package->id,
                'status' => 'DELIVERED',
                'changed_by' => $user->id,
                'notes' => $request->delivery_notes ?? 'Colis livré avec succès'
            ]);

            // Si c'est un retour, mettre à jour le ReturnPackage
            if ($package->return_package_id) {
                $returnPackage = ReturnPackage::find($package->return_package_id);
                if ($returnPackage) {
                    $returnPackage->markAsDelivered();
                }
            }

            // Si c'est un paiement, mettre à jour le WithdrawalRequest
            if ($package->payment_withdrawal_id) {
                $withdrawal = WithdrawalRequest::find($package->payment_withdrawal_id);
                if ($withdrawal) {
                    $withdrawal->update([
                        'status' => 'DELIVERED',
                        'delivered_at' => now(),
                        'delivery_proof' => ['signature' => $signaturePath]
                    ]);
                }
            }

            // Gérer le COD si applicable
            if ($package->cod_amount > 0 && !$isSpecial) {
                $this->handleCodCollection($package, $user);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Colis livré avec succès',
                'package' => $package->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur delivery: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison'
            ], 500);
        }
    }

    /**
     * MARQUER COMME INDISPONIBLE (Unavailable)
     */
    public function markUnavailable(Request $request, Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non assigné à vous'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $package->update([
                'status' => 'UNAVAILABLE',
                'unavailable_attempts' => ($package->unavailable_attempts ?? 0) + 1,
                'unavailable_reason' => $request->reason
            ]);

            // Historique
            PackageStatusHistory::create([
                'package_id' => $package->id,
                'status' => 'UNAVAILABLE',
                'changed_by' => $user->id,
                'notes' => 'Destinataire indisponible: ' . $request->reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Colis marqué comme indisponible',
                'package' => $package->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur unavailable: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement'
            ], 500);
        }
    }

    /**
     * CAPTURE DE SIGNATURE
     */
    public function signatureCapture(Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return redirect()->route('deliverer.tournee')
                ->with('error', 'Colis non assigné à vous');
        }

        $isSpecial = $package->return_package_id || $package->payment_withdrawal_id;
        $requiresSignature = $package->requires_signature || $isSpecial;

        return view('deliverer.signature-capture', compact('package', 'requiresSignature', 'isSpecial'));
    }

    /**
     * SAUVEGARDER LA SIGNATURE (API)
     */
    public function saveSignature(Request $request, Package $package)
    {
        $user = Auth::user();

        if ($package->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non assigné à vous'
            ], 403);
        }

        $request->validate([
            'signature_data' => 'required|string'
        ]);

        try {
            $signaturePath = $this->storeSignatureFile($request->signature_data, $package->id);

            $package->update([
                'signature_path' => $signaturePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature enregistrée',
                'signature_path' => $signaturePath
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur signature: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la signature'
            ], 500);
        }
    }

    /**
     * MARQUER PICKUP COMME COLLECTÉ + LIVRAISON DIRECTE
     */
    public function markPickupCollected(Request $request, $pickupId)
    {
        $user = Auth::user();
        $pickup = PickupRequest::findOrFail($pickupId);

        if ($pickup->assigned_deliverer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pickup non assigné à vous'
            ], 403);
        }

        $request->validate([
            'scanned_packages' => 'required|array|min:1',
            'scanned_packages.*' => 'string'
        ]);

        DB::beginTransaction();
        try {
            // Marquer le pickup comme collecté
            $pickup->update([
                'status' => 'collected',
                'collected_at' => now(),
                'collected_by' => $user->id
            ]);

            $directDeliveries = [];
            $delivererGouvernorats = $user->deliverer_gouvernorats ?? [];

            // Traiter chaque colis scanné
            foreach ($request->scanned_packages as $packageCode) {
                $package = Package::where('package_code', $packageCode)->first();
                
                if (!$package) {
                    continue;
                }

                // Marquer comme ramassé
                $package->update([
                    'status' => 'PICKED_UP',
                    'picked_up_at' => now(),
                    'pickup_request_id' => $pickup->id
                ]);

                // LOGIQUE LIVRAISON DIRECTE ⚡
                // Si la destination est dans la zone du livreur, créer une tâche de livraison directe
                if (!empty($delivererGouvernorats) && $package->delegationTo) {
                    $destinationGouvernorate = $package->delegationTo->governorate;
                    
                    if (in_array($destinationGouvernorate, $delivererGouvernorats)) {
                        // Assigner directement au livreur
                        $package->update([
                            'assigned_deliverer_id' => $user->id,
                            'assigned_at' => now(),
                            'is_direct_delivery' => true // Flag spécial
                        ]);

                        $directDeliveries[] = [
                            'package_id' => $package->id,
                            'package_code' => $package->package_code,
                            'recipient_name' => $package->recipient_data['name'] ?? 'N/A',
                            'destination' => $package->delegationTo->name
                        ];

                        // Historique
                        PackageStatusHistory::create([
                            'package_id' => $package->id,
                            'status' => 'ASSIGNED_DIRECT_DELIVERY',
                            'changed_by' => $user->id,
                            'notes' => 'Livraison directe après pickup - Destination dans la zone du livreur'
                        ]);
                    }
                }
            }

            DB::commit();

            $message = 'Pickup collecté avec succès';
            if (count($directDeliveries) > 0) {
                $message .= '. ' . count($directDeliveries) . ' colis ajouté(s) à votre Run Sheet pour livraison directe ⚡';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'direct_deliveries' => $directDeliveries,
                'total_packages' => count($request->scanned_packages),
                'direct_delivery_count' => count($directDeliveries)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur pickup collection: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la collecte'
            ], 500);
        }
    }

    /**
     * HELPERS PRIVÉS
     */

    /**
     * Sauvegarder la signature en base64 dans le storage
     */
    private function storeSignatureFile($signatureData, $packageId)
    {
        // Décoder base64
        $image = str_replace('data:image/png;base64,', '', $signatureData);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        // Générer nom de fichier
        $filename = 'signatures/package_' . $packageId . '_' . time() . '.png';

        // Sauvegarder
        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }

    /**
     * Gérer la collecte du COD
     */
    private function handleCodCollection($package, $user)
    {
        // Ajouter le montant COD au wallet du livreur INSTANTANÉMENT
        $wallet = \App\Models\UserWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'pending_amount' => 0]
        );

        // Créditer le wallet immédiatement
        $wallet->increment('balance', $package->cod_amount);

        // Logger la transaction comme COMPLETED
        \App\Models\FinancialTransaction::create([
            'user_id' => $user->id,
            'type' => 'COD_COLLECTED',
            'amount' => $package->cod_amount,
            'description' => 'COD collecté - Colis ' . $package->package_code,
            'reference' => 'COD_' . $package->id,
            'status' => 'COMPLETED'
        ]);
    }
}
