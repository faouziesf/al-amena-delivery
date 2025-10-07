<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\Log;

class PackageAutoAssignmentService
{
    /**
     * Auto-assigner un colis ramassé (placeholder)
     */
    public function autoAssignPickedUpPackage(Package $package): void
    {
        Log::info("Auto-assignation appelée pour colis #{$package->id}");
        
        // TODO: Implémenter la logique d'auto-assignation si nécessaire
        // Pour l'instant, ne rien faire (le colis est déjà assigné manuellement)
    }
}
