<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service de gestion des notifications push via Firebase Cloud Messaging
 */
class PushNotificationService
{
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    protected $serverKey;

    public function __construct()
    {
        // Clé serveur Firebase depuis .env
        $this->serverKey = env('FCM_SERVER_KEY');
    }

    /**
     * Envoyer une notification push à un utilisateur
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = [])
    {
        $user = User::find($userId);
        
        if (!$user || !$user->fcm_token) {
            Log::warning("Push notification: User #{$userId} has no FCM token");
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    /**
     * Envoyer une notification push à plusieurs utilisateurs
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = [])
    {
        $users = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->get();

        $tokens = $users->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            Log::warning("Push notification: No FCM tokens found for users");
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification push à un token FCM
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        if (!$this->serverKey) {
            Log::error("Push notification: FCM_SERVER_KEY not configured");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'to' => $token,
                'priority' => 'high',
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                Log::info("Push notification sent successfully", [
                    'title' => $title,
                    'token' => substr($token, 0, 20) . '...'
                ]);
                return true;
            } else {
                Log::error("Push notification failed", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Push notification exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification push à plusieurs tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        if (!$this->serverKey) {
            Log::error("Push notification: FCM_SERVER_KEY not configured");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'registration_ids' => $tokens,
                'priority' => 'high',
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                Log::info("Push notification sent to multiple tokens", [
                    'title' => $title,
                    'count' => count($tokens)
                ]);
                return true;
            } else {
                Log::error("Push notification failed", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Push notification exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification push à tous les utilisateurs d'un rôle
     */
    public function sendToRole(string $role, string $title, string $body, array $data = [])
    {
        $users = User::where('role', $role)
            ->where('status', 'ACTIVE')
            ->whereNotNull('fcm_token')
            ->get();

        $tokens = $users->pluck('fcm_token')->toArray();

        if (empty($tokens)) {
            Log::warning("Push notification: No FCM tokens for role {$role}");
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification push basée sur un type de notification
     */
    public function sendFromNotification($notification)
    {
        if (!$notification->user_id) {
            return false;
        }

        $data = [
            'notification_id' => (string) $notification->id,
            'type' => $notification->type,
            'priority' => $notification->priority,
        ];

        // Ajouter les données supplémentaires si présentes
        if ($notification->data) {
            $data = array_merge($data, is_array($notification->data) ? $notification->data : json_decode($notification->data, true));
        }

        return $this->sendToUser(
            $notification->user_id,
            $notification->title,
            $notification->message,
            $data
        );
    }

    /**
     * Mettre à jour le token FCM d'un utilisateur
     */
    public function updateUserToken(int $userId, string $fcmToken)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return false;
            }

            $user->update(['fcm_token' => $fcmToken]);
            
            Log::info("FCM token updated for user #{$userId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error updating FCM token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer le token FCM d'un utilisateur (déconnexion)
     */
    public function removeUserToken(int $userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return false;
            }

            $user->update(['fcm_token' => null]);
            
            Log::info("FCM token removed for user #{$userId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error removing FCM token: " . $e->getMessage());
            return false;
        }
    }
}
