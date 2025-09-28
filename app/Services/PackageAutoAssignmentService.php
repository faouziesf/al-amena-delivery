<?php

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PackageAutoAssignmentService
{
    /**
     * Auto-assigner un colis PICKED_UP à un livreur de la même délégation
     */
    public function autoAssignPickedUpPackage(Package $package)
    {
        // Vérifier que le colis peut être auto-assigné
        if (!$this->canAutoAssign($package)) {
            return false;
        }

        // Trouver un livreur disponible dans la même délégation
        $deliverer = $this->findAvailableDelivererInDelegation($package->delegation_to);

        if (!$deliverer) {
            Log::info("Aucun livreur disponible trouvé pour la délégation: {$package->delegation_to}");
            return false;
        }

        // Assigner le colis au livreur
        $package->update([
            'assigned_deliverer_id' => $deliverer->id,
            'status' => 'ACCEPTED', // Statut "en cours de livraison"
            'accepted_at' => now()
        ]);

        Log::info("Colis #{$package->tracking_number} auto-assigné au livreur {$deliverer->name} (délégation: {$package->delegation_to})");

        return true;
    }

    /**
     * Vérifier si un colis peut être auto-assigné
     */
    private function canAutoAssign(Package $package)
    {
        return $package->status === 'PICKED_UP' &&
               !empty($package->delegation_to) &&
               empty($package->assigned_deliverer_id);
    }

    /**
     * Trouver un livreur disponible dans une délégation
     */
    private function findAvailableDelivererInDelegation($delegation)
    {
        return User::where('role', 'DELIVERER')
                   ->where('assigned_delegation', $delegation)
                   ->where('account_status', 'ACTIVE')
                   ->first(); // Pour l'instant, on prend le premier disponible
                   // TODO: Implémenter une logique plus sophistiquée (charge de travail, etc.)
    }

    /**
     * Auto-assigner tous les colis PICKED_UP sans livreur
     */
    public function autoAssignAllPickedUpPackages()
    {
        $packages = Package::where('status', 'PICKED_UP')
                          ->whereNull('assigned_deliverer_id')
                          ->whereNotNull('delegation_to')
                          ->get();

        $assignedCount = 0;

        foreach ($packages as $package) {
            if ($this->autoAssignPickedUpPackage($package)) {
                $assignedCount++;
            }
        }

        Log::info("Auto-assignation terminée: {$assignedCount} colis assignés sur " . $packages->count());

        return $assignedCount;
    }

    /**
     * Auto-assigner les colis d'une délégation spécifique
     */
    public function autoAssignPackagesForDelegation($delegation)
    {
        $packages = Package::where('status', 'PICKED_UP')
                          ->where('delegation_to', $delegation)
                          ->whereNull('assigned_deliverer_id')
                          ->get();

        $assignedCount = 0;

        foreach ($packages as $package) {
            if ($this->autoAssignPickedUpPackage($package)) {
                $assignedCount++;
            }
        }

        return $assignedCount;
    }
}