<?php

namespace App\Services;

use App\Models\User;
use App\Models\Package;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service temporaire pour maintenir la compatibilité
 * TODO: Refactoriser et déplacer la logique vers les contrôleurs appropriés
 */
class CommercialService
{
    /**
     * Méthodes temporaires pour éviter les erreurs
     * Ces méthodes doivent être remplacées par du code direct dans les contrôleurs
     */

    public function getComplaintsSummary()
    {
        return [
            'total' => 0,
            'pending' => 0,
            'resolved' => 0,
            'urgent' => 0
        ];
    }

    public function processWithdrawalRequest($withdrawal, $action, $data, $commercial)
    {
        throw new \Exception('Cette méthode doit être refactorisée dans le contrôleur');
    }

    public function assignWithdrawalToDeliverer($withdrawal, $deliverer)
    {
        throw new \Exception('Cette méthode doit être refactorisée dans le contrôleur');
    }

    public function emptyDelivererWallet($deliverer, $commercial, $physicalAmount = null)
    {
        throw new \Exception('Cette méthode doit être refactorisée dans le contrôleur');
    }

    public function modifyCodAmount($package, $newAmount, $reason, $commercial, $complaint = null, $emergency = false)
    {
        throw new \Exception('Cette méthode doit être refactorisée dans le contrôleur');
    }
}