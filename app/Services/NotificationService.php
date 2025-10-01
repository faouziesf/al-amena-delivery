<?php

namespace App\Services;

/**
 * Service temporaire pour éviter les erreurs de dépendance
 * TODO: Implémenter les fonctionnalités de notification appropriées
 */
class NotificationService
{
    /**
     * Méthodes temporaires pour maintenir la compatibilité
     */

    public function sendNotification($user, $message, $type = 'info')
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter l'envoi de notifications
        return true;
    }

    public function sendEmail($user, $subject, $message)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter l'envoi d'emails
        return true;
    }

    public function sendSms($phone, $message)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter l'envoi de SMS
        return true;
    }

    public function notifyCommercialOfNewComplaint($complaint)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter la notification des commerciaux
        return true;
    }

    public function notifyClientOfComplaintUpdate($complaint)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter la notification des clients
        return true;
    }

    public function getUnreadCount($userId = null)
    {
        // Implémentation temporaire - retourne 0
        // TODO: Implémenter le comptage des notifications non lues
        return 0;
    }

    public function getUserNotifications($userId, $limit = 10)
    {
        // Implémentation temporaire - retourne une collection vide
        // TODO: Implémenter la récupération des notifications utilisateur
        return collect([]);
    }

    public function markAsRead($notificationId)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter le marquage comme lu
        return true;
    }

    public function markAllAsRead($userId)
    {
        // Implémentation temporaire - ne fait rien
        // TODO: Implémenter le marquage global comme lu
        return true;
    }

    public function getUrgentCount($userId = null)
    {
        // Implémentation temporaire - retourne 0
        // TODO: Implémenter le comptage des notifications urgentes
        return 0;
    }
}