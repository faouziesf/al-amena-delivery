<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service de gestion des notifications système
 */
class NotificationService
{
    /**
     * Créer une notification pour un utilisateur
     */
    public function create(int $userId, string $type, string $title, string $message, string $priority = 'NORMAL', array $data = [])
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'priority' => $priority,
                'data' => $data,
                'read_at' => null,
            ]);
        } catch (\Exception $e) {
            \Log::error("Erreur création notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Créer des notifications pour plusieurs utilisateurs
     */
    public function createForUsers(array $userIds, string $type, string $title, string $message, string $priority = 'NORMAL', array $data = [])
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notification = $this->create($userId, $type, $title, $message, $priority, $data);
            if ($notification) {
                $notifications[] = $notification;
            }
        }
        
        return $notifications;
    }

    /**
     * Envoyer une notification simple
     */
    public function sendNotification($user, $message, $type = 'INFO')
    {
        if (is_int($user)) {
            $userId = $user;
        } elseif ($user instanceof User) {
            $userId = $user->id;
        } else {
            return false;
        }

        return $this->create($userId, $type, 'Notification', $message);
    }

    /**
     * Notifier un changement de statut de package
     */
    public function notifyPackageStatusChanged(int $packageId, string $oldStatus, string $newStatus, int $clientId)
    {
        return $this->create(
            $clientId,
            'PACKAGE_STATUS',
            'Changement de Statut',
            "Votre colis #{$packageId} est passé de {$oldStatus} à {$newStatus}",
            'NORMAL',
            [
                'package_id' => $packageId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]
        );
    }

    /**
     * Notifier un nouveau colis assigné à un livreur
     */
    public function notifyPackageAssigned(int $packageId, int $delivererId)
    {
        return $this->create(
            $delivererId,
            'PACKAGE_ASSIGNED',
            'Nouveau Colis Assigné',
            "Un nouveau colis #{$packageId} vous a été assigné",
            'HIGH',
            ['package_id' => $packageId]
        );
    }

    /**
     * Notifier un client d'un nouveau ticket
     */
    public function notifyNewTicket(int $ticketId, int $clientId)
    {
        return $this->create(
            $clientId,
            'TICKET_CREATED',
            'Ticket Créé',
            "Votre ticket #{$ticketId} a été créé et sera traité sous peu",
            'NORMAL',
            ['ticket_id' => $ticketId]
        );
    }

    /**
     * Notifier un commercial d'une nouvelle réclamation
     */
    public function notifyCommercialOfNewComplaint($complaint)
    {
        // Récupérer tous les commerciaux
        $commercials = User::where('role', 'COMMERCIAL')->where('status', 'ACTIVE')->pluck('id')->toArray();
        
        return $this->createForUsers(
            $commercials,
            'COMPLAINT_CREATED',
            'Nouvelle Réclamation',
            "Une nouvelle réclamation a été créée",
            'HIGH',
            ['complaint_id' => $complaint->id ?? null]
        );
    }

    /**
     * Notifier un client de la mise à jour de sa réclamation
     */
    public function notifyClientOfComplaintUpdate($complaint)
    {
        if (!isset($complaint->client_id)) {
            return false;
        }

        return $this->create(
            $complaint->client_id,
            'COMPLAINT_UPDATED',
            'Réclamation Mise à Jour',
            "Votre réclamation a été mise à jour",
            'NORMAL',
            ['complaint_id' => $complaint->id ?? null]
        );
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return 0;
        }

        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Obtenir le nombre de notifications urgentes non lues
     */
    public function getUrgentCount($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return 0;
        }

        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->whereIn('priority', ['HIGH', 'URGENT'])
            ->count();
    }

    /**
     * Récupérer les notifications d'un utilisateur
     */
    public function getUserNotifications($userId, $limit = 10): Collection
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Récupérer les notifications non lues d'un utilisateur
     */
    public function getUnreadNotifications($userId, $limit = 10): Collection
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($notificationId)
    {
        try {
            $notification = Notification::find($notificationId);
            
            if ($notification && !$notification->read_at) {
                $notification->update(['read_at' => now()]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error("Erreur marquage notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($userId)
    {
        try {
            Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Erreur marquage notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer une notification
     */
    public function delete($notificationId)
    {
        try {
            $notification = Notification::find($notificationId);
            
            if ($notification) {
                $notification->delete();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error("Erreur suppression notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Méthodes héritées (compatibilité)
     */
    public function sendEmail($user, $subject, $message)
    {
        // TODO: Implémenter l'envoi d'emails via Mailtrap ou autre
        return true;
    }

    public function sendSms($phone, $message)
    {
        // TODO: Implémenter l'envoi de SMS
        return true;
    }
}