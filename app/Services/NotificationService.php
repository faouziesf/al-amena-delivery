<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Models\Package;

class NotificationService
{
    // ==================== CRÉATION DE NOTIFICATIONS ====================

    public function notifyComplaintCreated(Complaint $complaint)
    {
        return Notification::createComplaintNotification($complaint);
    }

    public function notifyWithdrawalRequested(WithdrawalRequest $withdrawal)
    {
        return Notification::createWithdrawalNotification($withdrawal);
    }

    public function notifyHighWalletBalance(User $deliverer)
    {
        return Notification::createHighWalletNotification($deliverer);
    }

    public function notifyPackageBlocked(Package $package, $reason = 'Colis bloqué depuis plus de 3 jours')
    {
        $commercials = User::where('role', 'COMMERCIAL')
                          ->where('account_status', 'ACTIVE')
                          ->pluck('id')
                          ->toArray();

        $notifications = [];
        foreach ($commercials as $commercialId) {
            $notifications[] = Notification::create([
                'user_id' => $commercialId,
                'type' => 'PACKAGE_BLOCKED',
                'title' => 'Colis bloqué',
                'message' => "Le colis {$package->package_code} est bloqué - {$reason}",
                'priority' => 'HIGH',
                'action_url' => route('commercial.packages.show', $package->id),
                'related_type' => 'App\Models\Package',
                'related_id' => $package->id,
                'data' => [
                    'package_id' => $package->id,
                    'package_code' => $package->package_code,
                    'status' => $package->status,
                    'days_blocked' => now()->diffInDays($package->created_at),
                    'reason' => $reason
                ]
            ]);
        }

        return $notifications;
    }

    public function notifySystemAlert($message, $priority = 'NORMAL', array $userIds = [])
    {
        if (empty($userIds)) {
            // Notifier tous les commerciaux et superviseurs
            $userIds = User::whereIn('role', ['COMMERCIAL', 'SUPERVISOR'])
                          ->where('account_status', 'ACTIVE')
                          ->pluck('id')
                          ->toArray();
        }

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'type' => 'SYSTEM_ALERT',
                'title' => 'Alerte système',
                'message' => $message,
                'priority' => $priority,
                'data' => [
                    'alert_time' => now()->toISOString(),
                    'system_alert' => true
                ]
            ]);
        }

        return $notifications;
    }

    // ==================== GESTION DES NOTIFICATIONS ====================

    public function getUserNotifications(User $user, $unreadOnly = false, $limit = 50)
    {
        $query = $user->notifications()
                     ->notExpired()
                     ->orderBy('priority', 'desc')
                     ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    public function getUnreadCount(User $user)
    {
        return $user->notifications()
                   ->unread()
                   ->notExpired()
                   ->count();
    }

    public function getUrgentCount(User $user)
    {
        return $user->notifications()
                   ->unread()
                   ->urgent()
                   ->notExpired()
                   ->count();
    }

    public function markAsRead(User $user, $notificationId = null)
    {
        if ($notificationId) {
            // Marquer une notification spécifique
            $notification = $user->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                return 1;
            }
            return 0;
        } else {
            // Marquer toutes les notifications non lues
            return $user->notifications()
                       ->unread()
                       ->update([
                           'read' => true,
                           'read_at' => now()
                       ]);
        }
    }

    public function markAllAsRead(User $user)
    {
        return $user->notifications()
                   ->unread()
                   ->update([
                       'read' => true,
                       'read_at' => now()
                   ]);
    }

    public function deleteNotification(User $user, $notificationId)
    {
        return $user->notifications()
                   ->where('id', $notificationId)
                   ->delete();
    }

    public function deleteReadNotifications(User $user, $olderThanDays = 30)
    {
        return $user->notifications()
                   ->read()
                   ->where('read_at', '<', now()->subDays($olderThanDays))
                   ->delete();
    }

    // ==================== STATISTIQUES ET MONITORING ====================

    public function getNotificationStats(User $user)
    {
        $notifications = $user->notifications()->notExpired();

        return [
            'total' => $notifications->count(),
            'unread' => $notifications->clone()->unread()->count(),
            'urgent' => $notifications->clone()->urgent()->count(),
            'by_type' => $notifications->clone()
                                     ->selectRaw('type, COUNT(*) as count')
                                     ->groupBy('type')
                                     ->pluck('count', 'type')
                                     ->toArray(),
            'today' => $notifications->clone()->whereDate('created_at', today())->count(),
            'this_week' => $notifications->clone()->where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    public function getSystemNotificationStats()
    {
        return [
            'total_sent_today' => Notification::whereDate('created_at', today())->count(),
            'urgent_pending' => Notification::urgent()->unread()->count(),
            'expired_cleaned' => $this->cleanupExpiredNotifications(),
            'by_type_today' => Notification::whereDate('created_at', today())
                                          ->selectRaw('type, COUNT(*) as count')
                                          ->groupBy('type')
                                          ->pluck('count', 'type')
                                          ->toArray(),
        ];
    }

    // ==================== MAINTENANCE ====================

    public function cleanupExpiredNotifications()
    {
        return Notification::expired()->delete();
    }

    public function cleanupOldReadNotifications($olderThanDays = 30)
    {
        return Notification::read()
                          ->where('read_at', '<', now()->subDays($olderThanDays))
                          ->delete();
    }

    // ==================== NOTIFICATIONS AUTOMATIQUES ====================

    public function checkAndNotifyHighWalletBalances($threshold = 100)
    {
        $deliverers = User::join('user_wallets', 'users.id', '=', 'user_wallets.user_id')
                         ->where('users.role', 'DELIVERER')
                         ->where('users.account_status', 'ACTIVE')
                         ->where('user_wallets.balance', '>', $threshold)
                         ->get(['users.*']);

        $notifications = [];
        foreach ($deliverers as $deliverer) {
            // Vérifier si pas déjà notifié dans les dernières 4 heures
            $recentNotification = Notification::where('type', 'WALLET_HIGH_BALANCE')
                                            ->where('related_type', 'App\Models\User')
                                            ->where('related_id', $deliverer->id)
                                            ->where('created_at', '>', now()->subHours(4))
                                            ->exists();

            if (!$recentNotification) {
                $notifications = array_merge($notifications, $this->notifyHighWalletBalance($deliverer));
            }
        }

        return $notifications;
    }

    public function checkAndNotifyBlockedPackages($daysSinceCreation = 3)
    {
        $blockedPackages = Package::inProgress()
                                 ->where('created_at', '<', now()->subDays($daysSinceCreation))
                                 ->get();

        $notifications = [];
        foreach ($blockedPackages as $package) {
            // Vérifier si pas déjà notifié dans les dernières 24 heures
            $recentNotification = Notification::where('type', 'PACKAGE_BLOCKED')
                                            ->where('related_type', 'App\Models\Package')
                                            ->where('related_id', $package->id)
                                            ->where('created_at', '>', now()->subHours(24))
                                            ->exists();

            if (!$recentNotification) {
                $daysSince = now()->diffInDays($package->created_at);
                $notifications = array_merge(
                    $notifications, 
                    $this->notifyPackageBlocked($package, "Bloqué depuis {$daysSince} jours")
                );
            }
        }

        return $notifications;
    }

    // ==================== API HELPER ====================

    public function formatNotificationForApi(Notification $notification)
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'type_display' => $notification->type_display,
            'title' => $notification->title,
            'message' => $notification->message,
            'priority' => $notification->priority,
            'priority_display' => $notification->priority_display,
            'priority_color' => $notification->priority_color,
            'read' => $notification->read,
            'action_url' => $notification->action_url,
            'has_action' => $notification->hasAction(),
            'created_at' => $notification->created_at->toISOString(),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'data' => $notification->data,
        ];
    }
}