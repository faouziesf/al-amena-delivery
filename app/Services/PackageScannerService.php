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
                'ACCEPTED' => [
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
            return [
                'success' => false,
                'message' => "🔒 Assigné à un autre livreur",
                'assigned_to' => $otherDeliverer->name ?? 'Inconnu'
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
}