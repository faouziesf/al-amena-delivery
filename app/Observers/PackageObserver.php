<?php

namespace App\Observers;

use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\ActionLog;
use App\Services\ActionLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class PackageObserver
{
    protected $actionLogService;
    protected $notificationService;

    public function __construct(ActionLogService $actionLogService, NotificationService $notificationService)
    {
        $this->actionLogService = $actionLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Package "created" event.
     */
    public function created(Package $package): void
    {
        // Créer historique de statut initial
        $this->logStatusHistory($package, null, $package->status, 'Colis créé');

        // Logger l'action avec le service
        $this->actionLogService->logCreated('Package', $package->id, [
            'package_code' => $package->package_code,
            'sender_id' => $package->sender_id,
            'status' => $package->status
        ]);

        // Notifier le client que son colis a été créé
        if ($package->sender_id) {
            $this->notificationService->create(
                $package->sender_id,
                'PACKAGE_CREATED',
                'Colis Créé',
                "Votre colis {$package->package_code} a été créé avec succès",
                'NORMAL',
                ['package_id' => $package->id, 'package_code' => $package->package_code]
            );
        }
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        // Vérifier si le statut a changé
        if ($package->isDirty('status')) {
            $oldStatus = $package->getOriginal('status');
            $newStatus = $package->status;
            
            $this->logStatusHistory($package, $oldStatus, $newStatus, $this->getStatusChangeDescription($oldStatus, $newStatus));
            
            // Logger avec le service
            $this->actionLogService->logStatusChanged('Package', $package->id, $oldStatus, $newStatus);
            
            // Notifier le client du changement de statut
            if ($package->sender_id) {
                $this->notificationService->notifyPackageStatusChanged(
                    $package->id,
                    $oldStatus,
                    $newStatus,
                    $package->sender_id
                );
            }
        }

        // Vérifier si le livreur a changé
        if ($package->isDirty('assigned_deliverer_id')) {
            $oldDelivererId = $package->getOriginal('assigned_deliverer_id');
            $newDelivererId = $package->assigned_deliverer_id;
            
            $description = 'Livreur ';
            if ($oldDelivererId && $newDelivererId) {
                $description .= 'changé';
            } elseif ($newDelivererId) {
                $description .= 'assigné';
            } else {
                $description .= 'désassigné';
            }

            $this->logStatusHistory($package, $package->status, $package->status, $description);
            
            // Logger l'assignation
            $this->actionLogService->logAssignment('Package', $package->id, $oldDelivererId, $newDelivererId);
            
            // Notifier le nouveau livreur
            if ($newDelivererId) {
                $this->notificationService->notifyPackageAssigned($package->id, $newDelivererId);
            }
            
            // Notifier l'ancien livreur si désassigné
            if ($oldDelivererId && !$newDelivererId) {
                $this->notificationService->create(
                    $oldDelivererId,
                    'PACKAGE_UNASSIGNED',
                    'Colis Désassigné',
                    "Le colis {$package->package_code} vous a été retiré",
                    'NORMAL',
                    ['package_id' => $package->id]
                );
            }
        }

        // Logger l'action avec ancien/nouveau
        $dirty = $package->getDirty();
        if (!empty($dirty)) {
            $original = [];
            foreach (array_keys($dirty) as $key) {
                $original[$key] = $package->getOriginal($key);
            }

            $this->actionLogService->logUpdated('Package', $package->id, $original, $dirty);
        }
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Package $package): void
    {
        $this->actionLogService->logDeleted('Package', $package->id, [
            'package_code' => $package->package_code,
            'status' => $package->status
        ]);
    }

    /**
     * Enregistrer un changement dans l'historique de statut
     */
    protected function logStatusHistory(Package $package, ?string $oldStatus, string $newStatus, string $notes = null): void
    {
        $user = Auth::user();
        
        PackageStatusHistory::create([
            'package_id' => $package->id,
            'previous_status' => $oldStatus ?? $newStatus,  // Si null, utiliser new_status
            'new_status' => $newStatus,
            'changed_by' => $user?->id ?? 1,
            'changed_by_role' => $user?->role ?? 'SYSTEM',
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obtenir une description lisible du changement de statut
     */
    protected function getStatusChangeDescription(?string $oldStatus, string $newStatus): string
    {
        $descriptions = [
            'CREATED' => 'Colis créé',
            'AVAILABLE' => 'Disponible pour livraison',
            'ACCEPTED' => 'Accepté par le livreur',
            'PICKED_UP' => 'Collecté par le livreur',
            'AT_DEPOT' => 'Arrivé au dépôt',
            'OUT_FOR_DELIVERY' => 'En cours de livraison',
            'DELIVERED' => 'Livré au destinataire',
            'PAID' => 'Paiement finalisé',
            'RETURNED' => 'Retourné à l\'expéditeur',
            'REFUSED' => 'Refusé par le destinataire',
            'CANCELLED' => 'Annulé',
            'UNAVAILABLE' => 'Client indisponible',
        ];

        $oldDesc = $descriptions[$oldStatus] ?? $oldStatus;
        $newDesc = $descriptions[$newStatus] ?? $newStatus;

        return "Statut changé: {$oldDesc} → {$newDesc}";
    }
}
