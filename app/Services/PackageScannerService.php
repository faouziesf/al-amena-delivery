<?php

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PackageScannerService
{
    /**
     * Scanner et traiter un code
     */
    public function scanCode(string $code): array
    {
        $package = $this->findPackageByCode($code);
        
        if (!$package) {
            return [
                'success' => false,
                'message' => "❌ Aucun colis trouvé : {$code}",
                'suggestions' => $this->getCodeSuggestions($code)
            ];
        }

        // Log du scan
        Log::info('Package scanned', [
            'code' => $code,
            'package_id' => $package->id,
            'deliverer_id' => Auth::id(),
            'package_status' => $package->status
        ]);

        return $this->determineAction($package);
    }

    /**
     * Recherche package par code (tous formats)
     */
    private function findPackageByCode(string $code): ?Package
    {
        $cleanCode = strtoupper(trim($code));
        
        // 1. URL tracking (QR)
        if (preg_match('/\/track\/(.+)$/', $code, $matches)) {
            $package = Package::where('package_code', $matches[1])->first();
            if ($package) return $package;
        }
        
        // 2. Code exact
        $package = Package::where('package_code', $cleanCode)->first();
        if ($package) return $package;
        
        // 3. Avec préfixe PKG_
        if (!str_starts_with($cleanCode, 'PKG_')) {
            $package = Package::where('package_code', 'PKG_' . $cleanCode)->first();
            if ($package) return $package;
        }
        
        // 4. Sans préfixe PKG_
        if (str_starts_with($cleanCode, 'PKG_')) {
            $package = Package::where('package_code', 'LIKE', '%' . substr($cleanCode, 4) . '%')->first();
            if ($package) return $package;
        }
        
        return null;
    }

    /**
     * Déterminer l'action selon le statut
     */
    private function determineAction(Package $package): array
    {
        $delivererId = Auth::id();
        
        // Colis disponible
        if ($package->status === 'AVAILABLE') {
            return [
                'success' => true,
                'message' => "✅ Colis disponible pour acceptation",
                'action' => 'accept',
                'redirect' => route('deliverer.packages.show', $package),
                'package' => $this->formatPackage($package)
            ];
        }

        // Colis assigné à ce livreur
        if ($package->assigned_deliverer_id === $delivererId) {
            return match($package->status) {
                'OUT_FOR_DELIVERY' => [
                    'success' => true,
                    'message' => "📦 Prêt pour collecte",
                    'action' => 'pickup',
                    'package' => $this->formatPackage($package),
                    'pickup_info' => $this->getPickupInfo($package)
                ],
                'PICKED_UP', 'UNAVAILABLE' => [
                    'success' => true,
                    'message' => "🚚 Prêt pour livraison",
                    'action' => 'deliver',
                    'package' => $this->formatPackage($package),
                    'cod_warning' => "💰 COD EXACT requis: {$package->cod_amount} DT",
                    'is_urgent' => $package->delivery_attempts >= 3
                ],
                'VERIFIED' => [
                    'success' => true,
                    'message' => "↩️ À retourner à l'expéditeur",
                    'action' => 'return',
                    'package' => $this->formatPackage($package)
                ],
                'DELIVERED' => [
                    'success' => true,
                    'message' => "✅ Déjà livré",
                    'package' => $this->formatPackage($package)
                ],
                default => [
                    'success' => true,
                    'message' => "Statut: {$package->status}",
                    'package' => $this->formatPackage($package)
                ]
            };
        }

        // Assigné à un autre livreur
        if ($package->assigned_deliverer_id) {
            $otherDeliverer = User::find($package->assigned_deliverer_id);

            // Permettre la réassignation pour les colis "in_progress"
            if (in_array($package->status, ['PICKED_UP', 'UNAVAILABLE'])) {
                return [
                    'success' => true,
                    'message' => "🔄 Colis en transit - Réassignation possible",
                    'action' => 'reassign',
                    'current_deliverer' => $otherDeliverer->name ?? 'Inconnu',
                    'package' => $this->formatPackage($package),
                    'can_reassign' => true,
                    'warning' => "Ce colis est actuellement assigné à {$otherDeliverer->name}. Voulez-vous le réassigner ?"
                ];
            }

            // Pour les autres statuts, pas de réassignation
            return [
                'success' => false,
                'message' => "🔒 Assigné à un autre livreur",
                'assigned_to' => $otherDeliverer->name ?? 'Inconnu',
                'package' => $this->formatPackage($package)
            ];
        }

        return [
            'success' => false,
            'message' => "❓ Statut non géré: {$package->status}"
        ];
    }

    /**
     * Formater package pour réponse
     */
    private function formatPackage(Package $package): array
    {
        return [
            'id' => $package->id,
            'code' => $package->package_code,
            'status' => $package->status,
            'cod_amount' => $package->cod_amount,
            'formatted_cod' => number_format($package->cod_amount, 3) . ' DT',
            'content_description' => $package->content_description,
            'delegation_from' => $package->delegationFrom->name ?? 'N/A',
            'delegation_to' => $package->delegationTo->name ?? 'N/A',
            'delivery_attempts' => $package->delivery_attempts ?? 0
        ];
    }

    /**
     * Info pickup
     */
    private function getPickupInfo(Package $package): array
    {
        return [
            'name' => $package->sender_data['name'] ?? 'N/A',
            'phone' => $package->sender_data['phone'] ?? 'N/A',
            'address' => $package->pickup_address ?? $package->sender_data['address'] ?? 'N/A',
            'delegation' => $package->delegationFrom->name ?? 'N/A'
        ];
    }

    /**
     * Suggestions codes similaires
     */
    private function getCodeSuggestions(string $code): array
    {
        $cleanCode = strtoupper(trim($code));
        
        if (strlen($cleanCode) >= 6) {
            $partial = substr($cleanCode, -8);
            return Package::where('package_code', 'LIKE', '%' . $partial . '%')
                         ->limit(3)
                         ->pluck('package_code')
                         ->toArray();
        }
        
        return [];
    }

    /**
     * Réassigner un colis à un nouveau livreur
     */
    public function reassignPackage(Package $package, int $newDelivererId, string $reason = null): array
    {
        // Vérifications de sécurité
        if (!in_array($package->status, ['PICKED_UP', 'UNAVAILABLE'])) {
            return [
                'success' => false,
                'message' => 'Ce colis ne peut pas être réassigné dans son état actuel'
            ];
        }

        $newDeliverer = User::find($newDelivererId);
        if (!$newDeliverer || $newDeliverer->role !== 'DELIVERER') {
            return [
                'success' => false,
                'message' => 'Livreur non valide'
            ];
        }

        $previousDeliverer = User::find($package->assigned_deliverer_id);

        try {
            \DB::transaction(function () use ($package, $newDelivererId, $reason, $previousDeliverer, $newDeliverer) {
                // Mettre à jour le colis
                $package->update([
                    'assigned_deliverer_id' => $newDelivererId,
                    'reassigned_at' => now(),
                    'reassignment_reason' => $reason
                ]);

                // Log de l'action
                \Log::info('Package reassigned', [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'from_deliverer_id' => $previousDeliverer->id ?? null,
                    'from_deliverer_name' => $previousDeliverer->name ?? 'Inconnu',
                    'to_deliverer_id' => $newDelivererId,
                    'to_deliverer_name' => $newDeliverer->name,
                    'reason' => $reason,
                    'status' => $package->status,
                    'reassigned_by' => \Auth::id()
                ]);

                // Créer des notifications
                if (class_exists(\App\Models\Notification::class)) {
                    // Notification au nouveau livreur
                    \App\Models\Notification::create([
                        'user_id' => $newDelivererId,
                        'type' => 'PACKAGE_REASSIGNED_TO',
                        'title' => 'Colis réassigné à vous',
                        'message' => "Le colis #{$package->package_code} vous a été réassigné",
                        'priority' => 'HIGH',
                        'data' => [
                            'package_id' => $package->id,
                            'previous_deliverer' => $previousDeliverer->name ?? 'Inconnu',
                            'reason' => $reason
                        ]
                    ]);

                    // Notification à l'ancien livreur (si existe)
                    if ($previousDeliverer) {
                        \App\Models\Notification::create([
                            'user_id' => $previousDeliverer->id,
                            'type' => 'PACKAGE_REASSIGNED_FROM',
                            'title' => 'Colis réassigné',
                            'message' => "Le colis #{$package->package_code} a été réassigné à {$newDeliverer->name}",
                            'priority' => 'NORMAL',
                            'data' => [
                                'package_id' => $package->id,
                                'new_deliverer' => $newDeliverer->name,
                                'reason' => $reason
                            ]
                        ]);
                    }

                    // Notification au client
                    \App\Models\Notification::create([
                        'user_id' => $package->sender_id,
                        'type' => 'PACKAGE_DELIVERER_CHANGED',
                        'title' => 'Changement de livreur',
                        'message' => "Votre colis #{$package->package_code} a été confié à un nouveau livreur",
                        'priority' => 'NORMAL',
                        'data' => [
                            'package_id' => $package->id,
                            'new_deliverer' => $newDeliverer->name,
                            'reason' => $reason ?? 'Optimisation des livraisons'
                        ]
                    ]);
                }
            });

            return [
                'success' => true,
                'message' => "Colis réassigné avec succès à {$newDeliverer->name}",
                'package' => $this->formatPackage($package->fresh()),
                'new_deliverer' => [
                    'id' => $newDeliverer->id,
                    'name' => $newDeliverer->name
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Package reassignment failed', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la réassignation'
            ];
        }
    }
}